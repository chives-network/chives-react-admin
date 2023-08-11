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
require_once('../data_enginee_function.php');

$sql    = "select count(*) AS NUM from data_datasyncedrules where 数据同步周期='每天' and date_format(执行时间,'%Y-%m-%d')!='".date('Y-m-d')."'";
$rs     = $db->Execute($sql);
if($rs->fields['NUM']==0) {
    $sql = "update data_datasyncedrules set 执行状态='未执行' where 数据同步周期='每天'";
    $db->Execute($sql);
    print $sql."<BR>";
}

$sql    = "select * from data_datasyncedrules where 数据同步周期='每天' and 执行状态='未执行' and 数据源!='' and 远程数据表!='' ";
$rs     = $db->Execute($sql);
$rs_a   = $rs->GetArray();
foreach($rs_a as $Line) {
    $FormId             = $Line['FormId'];
    $TableName          = $Line['TableName'];
    $数据源             = $Line['数据源'];
    $远程数据表         = $Line['远程数据表'];
    $远程数据表主键     = $Line['远程数据表主键'];
    $数据同步方式       = $Line['数据同步方式'];
    $数据同步周期       = $Line['数据同步周期'];
    $执行状态           = $Line['执行状态'];
    $执行时间           = $Line['执行时间'];
    $远程数据库信息      = returntablefield("data_datasource","id",$数据源,"数据库主机,数据库用户名,数据库密码,数据库名称");
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
                $rs_fields      = $db->Execute($sql);
                $rs_a_fields    = $rs_fields->GetArray();
                
                //开始做远程数据表同步到本数据中心的操作
                if($数据同步方式=="全量同步")  {
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
                            if($RemoteRelativeField=="Default")  {
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
                                        $学校十位代码   = returntablefield("ods_zzxxgkjcsj","id",1,"XXDM")['XXDM'];
                                        $唯一编码前缀   = $学校十位代码.$FieldsSeting['Placeholder'];
                                        $剩余位数       = 32-strlen($唯一编码前缀);
                                        $新编号 += 1;
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
                                            $rs_temp    = $db->Execute($sql);
                                            $NewRecord[$FieldsSeting['FieldName']] = $rs_temp->fields['NAME'];
                                            //print_R($NewRecord);;exit;
                                        }
                                        else {
                                            print_R($FieldsSeting['ShowType']);
                                            print_R($ADDTYPE_ARRAY);
                                        }
                                        break;
                                }
                            }
                            else if(in_array($RemoteRelativeField,$远程数据表结构))  {
                                //进行数据清洗
                                $NewRecord[$FieldsSeting['FieldName']] = $Line[$RemoteRelativeField];
                            }
                            //print_R($FieldsSeting);//exit;
                        }
                        //exit;
                        $BatchSqlBody[] = "('".join("','",array_values($NewRecord))."')";
                    }
                    //每次插入的记录数
                    $batchSize = 50;
                    //将SQL语句拆分成批次进行插入
                    for ($i = 0; $i < count($BatchSqlBody); $i += $batchSize) {
                        $batch = array_slice($BatchSqlBody, $i, $batchSize);
                        $insertSql = "INSERT INTO $TableName (".join(',',array_keys($NewRecord)).") VALUES " . implode(', ', $batch);
                        $rs_temp = $db->Execute($insertSql);
                        if(!$rs_temp->EOF)  {
                            print $insertSql;
                        }
                        else {
                            print "插入成功";
                        }
                    }
                }
                exit;
            }
        }
    }
    //每次只执行一次计划任务
    
}


?>