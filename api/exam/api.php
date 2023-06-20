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

$accessKey = "d667e7d739302f7c12ce471a4086fc83";

function 同步基础结构()   {
    global $accessKey;
    $api = "https://www.xuekubao.com/index.php?s=Index&m=Api&a=subjectEditionApi&accessKey=".$accessKey;
    $data = file_get_contents($api);
    $data = json_decode($data,true);
    if($data['errorCode']==0) {
        $dataX = $data['data'];
        foreach($dataX as $学段INFOR)                       {
            $Element = [];
            $Element['学段']        = $学段INFOR['name'];
            $Element['学段代码']    = $学段INFOR['code'];
            foreach($学段INFOR['child'] as $年级INFOR)   {
                $Element['年级']        = $年级INFOR['name'];
                $Element['年级代码']    = $年级INFOR['code'];
                foreach($年级INFOR['child'] as $学科INFOR)   {
                    $Element['学科']        = $学科INFOR['name'];
                    $Element['学科代码']    = $学科INFOR['code'];
                    foreach($学科INFOR['child'] as $教材INFOR)   {
                        $Element['id']          = $教材INFOR['id'];
                        $Element['教材代码']     = $教材INFOR['code'];
                        $Element['教材']         = $教材INFOR['name'];
                        InsertOrUpdateTableByArray('data_exam_structure', $Element, $primarykey="学段,年级,学科,教材", $Debug=0, $InsertMode='InsertOrUpdate');
                    }
                }
            }

        }
    }
}

//同步题目类型();
function 同步题目类型()   {
    global $accessKey;
    $api = "https://www.xuekubao.com/index.php?s=Index&m=Api&a=getOtherBasic&accessKey=".$accessKey;
    $data = file_get_contents($api);
    $data = json_decode($data,true);
    if($data['errorCode']==0) {
        $qtypes = $data['qtypes'];
        foreach($qtypes as $Line)                       {
            $Element = [];
            $Element['id']          = $Line['id'];
            $Element['学段']        = $Line['pharseId'];
            $Element['学科']        = $Line['subjectId'];
            $Element['类型']        = $Line['typeName'];
            InsertOrUpdateTableByArray('data_exam_question_types', $Element, $primarykey="学段,学科,类型", $Debug=0, $InsertMode='InsertOrUpdate');

        }
    }
}


//同步章节数据($pharseId=1,$subjectId=2,$editionId=3);
function 同步章节数据($pharseId=1,$subjectId=2,$editionId=3)   {
    global $accessKey;
    $api = "https://www.xuekubao.com/index.php?s=Index&m=Api&a=chapterApi&pharseId=$pharseId&subjectId=$subjectId&editionId=$editionId&accessKey=".$accessKey;
    $RS = [];
    $data = file_get_contents($api);
    $data = json_decode($data,true);
    if($data['errorCode']==0) {
        $data = $data['data'];
        foreach($data as $Line)                       {
            $Element = [];
            $Element['id']          = $Line['id'];
            $Element['名称']        = $Line['name'];
            $Element['父ID']        = $Line['pid'];
            $Element['学科ID']        = $Line['subjectId'];
            $Element['学段ID']        = $Line['pharseId'];
            $Element['教材ID']        = $Line['editionId'];
            $Element['年级ID']        = $Line['gradeId'];
            $Element['排序ID']        = $Line['sort'];
            $Element['旧ID']          = $Line['oldId'];
            $Element['唯一码']        = $Line['unique_code'];
            $Element['层级']          = $Line['level'];
            InsertOrUpdateTableByArray('data_exam_charpter', $Element, $primarykey="id", $Debug=0, $InsertMode='InsertOrUpdate');
            $RS[] = $Line;
        }
    }
    else {
        print_R($data);
    }
    return $RS;
}


$sql = "select * from data_exam_structure where 章节状态='' limit 3";
$rs = $db->Execute($sql);
$rs_a = $rs->GetArray();
foreach($rs_a as $Line) {
    if($Line['章节状态']!='同步完成')  {
        $同步章节数据 = 同步章节数据($Line['学段ID'],$Line['学科ID'],$Line['教材ID']);
        if(sizeof($同步章节数据)>0)    {
            $sql = "update data_exam_structure set 章节状态='同步完成' where id='".$Line['id']."'";
            $db->Execute($sql);
            print_R("本次同步记录数:".sizeof($同步章节数据));
        }
        else {
            $sql = "update data_exam_structure set 章节状态='同步失败' where id='".$Line['id']."'";
            $db->Execute($sql);
        }
    }
}
//print "<meta http-equiv=\"refresh\" content=\"".rand(5,15)."\"; url=?\">";


?>