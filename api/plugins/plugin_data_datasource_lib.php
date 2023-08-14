<?php
/*
* Infrastructure: Chives React Admin
* Author: Chives Network
* Email: reactchives@gmail.com
* Copyright (c) 2023
* License: GPL V3 or Commercial license
*/
header("Content-Type: application/json");
require_once('../cors.php');
require_once('../include.inc.php');

$sql    = "select count(*) AS NUM from data_datasyncedrules where 数据同步周期='每天' and 计划时间='".date('Y-m-d')."'";
$rs     = $db->Execute($sql);
if($rs->fields['NUM']==0) {
    $sql = "update data_datasyncedrules set 执行状态='未执行', 计划时间='".date('Y-m-d')."' where 数据同步周期='每天'";
    $db->Execute($sql);
    print $sql."<BR>";
}
//$sql = "update data_datasyncedrules set 执行状态='未执行', 计划时间='".date('Y-m-d')."' where 数据同步周期='每天'";$db->Execute($sql);
$id     = intval($_GET['id']);
if($id>0) {
    $sql    = "select * from data_datasyncedrules where id='$id'";
}
else {
    $sql    = "select * from data_datasyncedrules where 数据同步周期='每天' and 执行状态='未执行' and 数据源!='' and 远程数据表!='' ";
}
$rs     = $db->Execute($sql);
$rs_a   = $rs->GetArray();
foreach($rs_a as $LineX) {
    $FormId             = $LineX['FormId'];
    $TableName          = $LineX['TableName'];
    $数据源             = $LineX['数据源'];
    $远程数据表         = $LineX['远程数据表'];
    $远程数据表主键     = $LineX['远程数据表主键'];
    $数据同步方式       = $LineX['数据同步方式'];
    $数据同步周期       = $LineX['数据同步周期'];
    $执行状态           = $LineX['执行状态'];
    $执行时间           = $LineX['执行时间'];
    $远程数据库信息      = returntablefield("data_datasource","id",$数据源,"数据库主机,数据库用户名,数据库密码,数据库名称,连接池名称");
    if($远程数据库信息['数据库用户名']!="")    {
        $db_remote = NewADOConnection($DB_TYPE='mysqli');
        $db_remote->connect($远程数据库信息['数据库主机'], $远程数据库信息['数据库用户名'], DecryptID($远程数据库信息['数据库密码']), $远程数据库信息['数据库名称']);
        $db_remote->Execute("Set names utf8;");
        if($db_remote->database==$远程数据库信息['数据库名称']) {
            $MetaColumnNamesTemp    = $db_remote->MetaColumnNames($远程数据表);
            $远程数据表结构          = array_values($MetaColumnNamesTemp);
            if(is_array($远程数据表结构) && $远程数据表结构[0]!="")     {

                //得到当前数据中心表的结构
                $sql            = "select * from form_formfield where FormId='$FormId' and IsEnable='1' order by SortNumber asc";
                $rs_fields      = $db->CacheExecute(180,$sql);
                $rs_a_fields    = $rs_fields->GetArray();
                
                //开始做远程数据表同步到本数据中心的操作
                if($数据同步方式=="全量同步")  {
                    $学校十位代码              = returntablefield("ods_zzxxgkjcsj","id",1,"XXDM")['XXDM'];
                    $数据字典同步异常数据       = [];
                    $数据字典同步异常数据Detail = [];
                    $新编号 = 0;
                    //第一步:清空本地表
                    $sql = "truncate table $TableName";
                    $db->Execute($sql);
                    //第二步:从远程数据表获得数据
                    $BatchSqlBody   = [];
                    $sql            = "select * from $远程数据表";
                    $rs_remote      = $db_remote->Execute($sql);
                    $rs_a_remote    = $rs_remote->GetArray();
                    $NewRecord      = [];
                    foreach($rs_a_remote as $Line)  {
                        foreach($rs_a_fields as $Item) {
                            $FieldsSeting           = json_decode($Item['Setting'], true);
                            $RemoteRelativeField    = $FieldsSeting['RemoteRelativeField'];    
                            $LocalFieldExtraFilter    = $FieldsSeting['LocalFieldExtraFilter'];                          
                            //第三步:同步远程数据表数据,并且进行数据清洗. 这一步要先做,然后才能在本地默认值处理时,可以做本地的数据表关联
                            if($RemoteRelativeField!="" && $RemoteRelativeField!="None" && in_array($RemoteRelativeField, $远程数据表结构))  {
                                $FieledShowTypeArray = explode(':',$FieldsSeting['ShowType']);
                                switch($FieledShowTypeArray[0]) {
                                    case 'Input':
                                        $NewRecord[$FieldsSeting['FieldName']] = $Line[$RemoteRelativeField];
                                        break;
                                    case '中职标准':
                                        //数据字典的名称转代码
                                        $ADDTYPE        = returntablefield("form_formfield_showtype","Name",$FieldsSeting['ShowType'],"`ADD`")['ADD'];
                                        $ADDTYPE_ARRAY  = explode(':',$ADDTYPE);
                                        if(sizeof($ADDTYPE_ARRAY)==7 && $ADDTYPE_ARRAY[1]=="form_formdict") {
                                            $Temp_MetaColumnNames = GLOBAL_MetaColumnNames($ADDTYPE_ARRAY[1]);
                                            $sql        = "select ".$Temp_MetaColumnNames[$ADDTYPE_ARRAY[2]]." AS CODE,".$Temp_MetaColumnNames[$ADDTYPE_ARRAY[3]]." AS NAME, OtherPossibleValues from ".$ADDTYPE_ARRAY[1]." where ".$ADDTYPE_ARRAY[5]."='".$ADDTYPE_ARRAY[6]."'";
                                            $rs_temp    = $db->CacheExecute(180,$sql);
                                            $rs_a_temp  = $rs_temp->GetArray();
                                            $数据字典合集 = [];
                                            foreach($rs_a_temp as $LineTemp) {
                                                $OtherPossibleValuesArray = explode(',',$OtherPossibleValues);
                                                foreach($OtherPossibleValues as $TEMP) {
                                                    $数据字典合集[$TEMP] = $LineTemp['NAME'];
                                                }
                                                $数据字典合集[$LineTemp['CODE']] = $LineTemp['CODE'];
                                                $数据字典合集[$LineTemp['NAME']] = $LineTemp['CODE'];
                                            }
                                            if($ADDTYPE_ARRAY[6]=="XQDM（学期代码）")  {
                                                if(strpos($Line[$RemoteRelativeField],"第一学期")) {
                                                    $Line[$RemoteRelativeField] = "秋季学期";
                                                }
                                                else if(strpos($Line[$RemoteRelativeField],"第二学期")) {
                                                    $Line[$RemoteRelativeField] = "春季学期";
                                                }
                                            }
                                            else if($ADDTYPE_ARRAY[6]=="XQDM（学期代码）")  {
                                                if(strpos($Line[$RemoteRelativeField],"第一学期")) {
                                                    $Line[$RemoteRelativeField] = "秋季学期";
                                                }
                                                else if(strpos($Line[$RemoteRelativeField],"第二学期")) {
                                                    $Line[$RemoteRelativeField] = "春季学期";
                                                }
                                            }
                                            if($数据字典合集[$Line[$RemoteRelativeField]]!="")  {
                                                $NewRecord[$FieldsSeting['FieldName']] = $数据字典合集[$Line[$RemoteRelativeField]];
                                            }
                                            elseif(1) {
                                                //记录数据字典同步时,是哪一个记录出的问题
                                                $数据字典合集HTML = "";
                                                foreach($数据字典合集 AS $KEYTEMP=>$VALUETEMP) {
                                                    $数据字典合集HTML .= "$KEYTEMP => $VALUETEMP<BR>";
                                                }
                                                $数据字典同步异常数据Detail[] = ["远程数据记录的值"=>$Line[$RemoteRelativeField], "本地数据字典"=>$ADDTYPE_ARRAY[6],"数据字典合集"=>$数据字典合集HTML];
                                            }
                                            else {
                                                $数据字典同步异常数据[$FieldsSeting['FieldName']][$RemoteRelativeField][$Line[$RemoteRelativeField]] += 1;
                                            }
                                        }
                                        break;
                                    
                                }
                            }
                            //第四步:不使用远程数据表使用,而是使用本地数据的配置来产生一个默认值
                            else if($RemoteRelativeField=="Default")  {
                                switch($FieldsSeting['ShowType']) {
                                    case 'Hidden:Createandupdatetime':
                                        if($FieldsSeting['DateTimeFormat']!="") {
                                            $NewRecord[$FieldsSeting['FieldName']] = date($FieldsSeting['DateTimeFormat']);    
                                        }
                                        else {
                                            $NewRecord[$FieldsSeting['FieldName']] = date("Y-m-d H:i:s");  
                                        }
                                        break;
                                    case '32位全局唯一编码字符串':
                                        $唯一编码前缀   = $学校十位代码.$FieldsSeting['Placeholder'];
                                        $剩余位数       = 32-strlen($唯一编码前缀);
                                        $新编号         += 1;
                                        $补齐0数量      = $剩余位数-strlen($新编号);
                                        while($补齐0数量>0) {
                                            $唯一编码前缀 .= "0";
                                            $补齐0数量 --;
                                        }
                                        $NewRecord[$FieldsSeting['FieldName']] = $唯一编码前缀.$新编号;
                                        break;
                                    default:
                                        $ADDTYPE        = returntablefield("form_formfield_showtype","Name",$FieldsSeting['ShowType'],"`ADD`")['ADD'];
                                        $ADDTYPE_ARRAY  = explode(':',$ADDTYPE);
                                        if(sizeof($ADDTYPE_ARRAY)==4) {
                                            $Temp_MetaColumnNames = GLOBAL_MetaColumnNames($ADDTYPE_ARRAY[1]);
                                            $sql        = "select ".$Temp_MetaColumnNames[$ADDTYPE_ARRAY[2]]." AS NAME from ".$ADDTYPE_ARRAY[1]."";
                                            $rs_temp    = $db->CacheExecute(180, $sql);
                                            $NewRecord[$FieldsSeting['FieldName']] = $rs_temp->fields['NAME'];
                                            //print_R($NewRecord);;exit;
                                        }
                                        else if($ADDTYPE_ARRAY[1]=="form_formdict" && sizeof($ADDTYPE_ARRAY)==7) {
                                            //$Temp_MetaColumnNames = GLOBAL_MetaColumnNames($ADDTYPE_ARRAY[1]);
                                            //$sql        = "select ".$Temp_MetaColumnNames[$ADDTYPE_ARRAY[2]]." AS NAME from ".$ADDTYPE_ARRAY[1]."";
                                            //$rs_temp    = $db->CacheExecute(180, $sql);
                                            //$NewRecord[$FieldsSeting['FieldName']] = $rs_temp->fields['NAME'];
                                            print_R($NewRecord);;exit;
                                        }
                                        else {
                                            print "<BR>出现异常,需要处理.";
                                            print_R($FieldsSeting['ShowType']);
                                            print_R($ADDTYPE_ARRAY);
                                        }
                                        break;
                                }
                            }
                            //第五步:LocalFieldExtraFilter 必须要先过滤有效的值,才能在这一步拿到值
                            else if($RemoteRelativeField=="LocalFieldExtraFilter"&&$LocalFieldExtraFilter!="")  {
                                $LocalFieldExtraFilterArray = explode(":",$LocalFieldExtraFilter);
                                $Temp_MetaColumnNames = GLOBAL_MetaColumnNames($LocalFieldExtraFilterArray[1]);
                                if(in_array($LocalFieldExtraFilterArray[2],$Temp_MetaColumnNames) && in_array($LocalFieldExtraFilterArray[3],$Temp_MetaColumnNames) && $LocalFieldExtraFilterArray[0]!="" && $NewRecord[$LocalFieldExtraFilterArray[0]]!="") {
                                    $NewRecord[$FieldsSeting['FieldName']] = returntablefield($LocalFieldExtraFilterArray[1],$LocalFieldExtraFilterArray[2],$NewRecord[$LocalFieldExtraFilterArray[0]],$LocalFieldExtraFilterArray[3])[$LocalFieldExtraFilterArray[3]];
                                    //print_R($NewRecord[$LocalFieldExtraFilterArray[0]]);
                                    //print_R($Temp_MetaColumnNames);;exit;
                                }
                                elseif($LocalFieldExtraFilterArray[0]!="" && $NewRecord[$LocalFieldExtraFilterArray[0]]!="") {
                                    //在本地数据表的数据过滤中,没有命中
                                    print "在本地数据表的数据过滤中,没有命中";
                                    print_R($LocalFieldExtraFilterArray);
                                }
                                else {
                                    //为空,无需过滤
                                }
                            }
                            //print_R($FieldsSeting);//exit;
                        }
                        if(sizeof(array_keys($NewRecord))>0) {
                            $BatchSqlBody[] = "('".join("','",array_values($NewRecord))."')";
                        }
                    }
                    $所有异常数据                           = [];
                    $所有异常数据['数据源']                 = $远程数据库信息['连接池名称'];
                    $所有异常数据['远程数据表']             = $远程数据表;
                    $所有异常数据['数据字典同步异常数据']   = $数据字典同步异常数据;
                    $所有异常数据['同步异常数据解决方法']   = "把异常数据的数据字典重新跟标准库的数据字典做一下关联.";
                    $数据字典同步异常数据Array  = [];
                    foreach($数据字典同步异常数据 as $本地字段=>$详细信息) {
                        foreach($详细信息 as $远程字段=>$字典信息) {
                            foreach($字典信息 as $字典名称=>$字典数量) {
                                $FieldElement = [];
                                $FieldElement['数据源']     = $远程数据库信息['连接池名称'];
                                $FieldElement['本地字段']   = $本地字段;
                                $FieldElement['远程数据表'] = $远程数据表;
                                $FieldElement['远程字段']   = $远程字段;
                                $FieldElement['字典名称']   = $字典名称;
                                $FieldElement['异常记录数量'] = $字典数量;
                                $数据字典同步异常数据Array[] = $FieldElement;
                            }
                        }
                    }
                    $所有异常数据['数据字典同步异常数据Array']   = $数据字典同步异常数据Array;
                    //每次插入的记录数
                    $batchSize = 50;
                    //将SQL语句拆分成批次进行插入
                    if(count($BatchSqlBody)>0)  {
                        for ($i = 0; $i < count($BatchSqlBody); $i += $batchSize) {
                            $batch = array_slice($BatchSqlBody, $i, $batchSize);
                            $insertSql = "INSERT INTO $TableName (".join(',',array_keys($NewRecord)).") VALUES " . implode(', ', $batch);
                            $rs_temp = $db->Execute($insertSql);
                            if(!$rs_temp->EOF)  {
                                print $insertSql;
                            }
                            else {
                                //print " $i 插入成功";
                            }
                        }
                        $sql = "update data_datasyncedrules set 执行时间='".date('Y-m-d H:i:s')."',执行状态='已完成',执行明细='".json_encode($所有异常数据)."',异常数据='".json_encode($数据字典同步异常数据Array)."' where id='".$LineX['id']."'";
                        $db->Execute($sql);
                        //print $sql."<BR>";
                        print "同步数据:".count($BatchSqlBody)."条 本地数据表:$TableName <BR>";
                        //print_R($数据字典同步异常数据Array);
                        print RSA2HTML($数据字典同步异常数据Array,$width='100%');     
                        print RSA2HTML($数据字典同步异常数据Detail,$width='100%');
                        //显示最近100条记录
                        $sql    = "select * from $TableName order by id desc limit 100";
                        $rsX    = $db->Execute($sql);
                        $rsX_a  = $rsX->GetArray();  
                        print "<BR>过滤以后数据,只显示最近100条数据:";
                        print RSA2HTML($rsX_a,$width='100%');
                    }
                    else {
                        $sql = "update data_datasyncedrules set 执行时间='".date('Y-m-d H:i:s')."',执行状态='已执行,但没有获取任何数据',执行明细='".json_encode($所有异常数据)."' where id='".$LineX['id']."'";
                        $db->Execute($sql);
                        //print $sql."<BR>";
                        //print_R($所有异常数据);
                        print "已执行,但没有获取任何数据";
                    }
                    //print_R($所有异常数据);
                }
                exit;
            }
        }
    }
    //每次只执行一次计划任务
    exit;
}


?>