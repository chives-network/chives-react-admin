<?php

//FlowName: 模拟练习

function plugin_data_exam_paper_2_init_default()  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_add_default_data_before_submit()  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_add_default_data_after_submit($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
    /*
    $sql        = "select * from `$TableName` where id = '$id'";
    $rs         = $db->Execute($sql);
    $rs_a       = $rs->GetArray();
    foreach($rs_a as $Line)  {
        //
    }
    */
}

function plugin_data_exam_paper_2_edit_default($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code

    $edit_default_mode = [];
    $edit_default = [];
    $defaultValues = [];

    $sql            = "SELECT * FROM `data_exam_paper` where id='$id'";
    $rs             = $db->CacheExecute(180,$sql);
    $试卷信息       = $rs->fields;
    $试题抽取方式   = $试卷信息['试题抽取方式'];
    $题库分类       = $试卷信息['题库分类'];
    $单选题目数量   = $试卷信息['单选题目数量'];
    $多选题目数量   = $试卷信息['多选题目数量'];
    $判断题目数量   = $试卷信息['判断题目数量'];
    $题库抽取       = [];
    $题目序号列表 = [];
    if($试题抽取方式=="学生每次打开时随机抽取试题"&&$单选题目数量>0) {
        $sql        = "select * from data_exam_question where 题库分类='$题库分类' and 类型='单选'";
        $rs         = $db->Execute($sql);
        $rs_a       = $rs->GetArray();
        $NUM        = sizeof($rs_a);
        for($i=0;$i<$单选题目数量;$i++) {
            $Item = $rs_a[rand(0,$NUM-1)];
            $题库抽取['单选'][] = $Item;
            $题目序号列表[] = $Item['id'];
        }
    }
    if($试题抽取方式=="学生每次打开时随机抽取试题"&&$多选题目数量>0) {
        $sql        = "select * from data_exam_question where 题库分类='$题库分类' and 类型='多选'";
        $rs         = $db->Execute($sql);
        $rs_a       = $rs->GetArray();
        $NUM        = sizeof($rs_a);
        for($i=0;$i<$多选题目数量;$i++) {
            $Item = $rs_a[rand(0,$NUM-1)];
            $题库抽取['多选'][] = $Item;
            $题目序号列表[] = $Item['id'];
        }
    }
    if($试题抽取方式=="学生每次打开时随机抽取试题"&&$判断题目数量>0) {
        $sql        = "select * from data_exam_question where 题库分类='$题库分类' and 类型='判断'";
        $rs         = $db->Execute($sql);
        $rs_a       = $rs->GetArray();
        $NUM        = sizeof($rs_a);
        for($i=0;$i<$判断题目数量;$i++) {
            $Item = $rs_a[rand(0,$NUM-1)];
            $题库抽取['判断'][] = $Item;
            $题目序号列表[] = $Item['id'];
        }
    }
    $序号 = 1;
    foreach($题库抽取 AS $题目类型=>$对应题目) {
        $edit_default_mode[] = ['value'=>$题目类型, 'label'=>$题目类型];
        foreach($对应题目 AS $单个题目)     {
            $题目选项 = [];
            if($单个题目['A']!="")      {
                $题目选项[] = ['value'=>'A', 'label'=>$单个题目['A']];
            }
            if($单个题目['B']!="")      {
                $题目选项[] = ['value'=>'B', 'label'=>$单个题目['B']];
            }
            if($单个题目['C']!="")      {
                $题目选项[] = ['value'=>'C', 'label'=>$单个题目['C']];
            }
            if($单个题目['D']!="")      {
                $题目选项[] = ['value'=>'D', 'label'=>$单个题目['D']];
            }
            if($单个题目['E']!="")      {
                $题目选项[] = ['value'=>'E', 'label'=>$单个题目['E']];
            }
            if($单个题目['F']!="")      {
                $题目选项[] = ['value'=>'F', 'label'=>$单个题目['F']];
            }
            if($题目类型=="单选" || $题目类型=="判断")        {
                $edit_default[$题目类型][] = ['name' => "题目_".$单个题目['id'], 'show'=>true, 'type'=>'radiogroup', 'options'=>$题目选项, 'label' => $序号."、".$单个题目['题干'], 'value' => "A", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>12, 'row'=>false]];
                $defaultValues["题目_".$单个题目['id']] = "";
            }
            else if($题目类型=="多选") {
                $edit_default[$题目类型][] = ['name' => "题目_".$单个题目['id'], 'show'=>true, 'type'=>'checkbox', 'options'=>$题目选项, 'label' => $序号."、".$单个题目['题干'], 'value' => "A", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'row'=>false, 'xs'=>12, 'sm'=>12]];
                $defaultValues["题目_".$单个题目['id']] = "";
            }
            $序号 ++;
        }
    }
    $edit_default[$题目类型][] = ['name' => "题目序号列表", 'show'=>true, 'type'=>'hidden', 'label' => "题目序号列表", 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>12]];
    $defaultValues['题目序号列表'] = EncryptID(join(',',$题目序号列表));

    $RS['edit_default']['allFields']      = $edit_default;
    $RS['edit_default']['allFieldsMode']  = $edit_default_mode;
    $RS['edit_default']['defaultValues']  = $defaultValues;
    $RS['edit_default']['dialogContentHeight']  = "850px";
    $RS['edit_default']['submitaction']  = "edit_default_data";
    $RS['edit_default']['componentsize'] = "small";
    $RS['edit_default']['submittext']    = __("Submit");
    $RS['edit_default']['canceltext']    = __("Cancel");
    $RS['edit_default']['titletext']     = "开始您的练习";
    $RS['edit_default']['titlememo']     = "不限制时间,每次随机出题";
    $RS['edit_default']['tablewidth']    = 650;

    $RS['status']   = "OK";
    $RS['msg']      = "获得数据成功";
    $RS['forceuse'] = true; //强制使用当前结构数据来渲染表单
    $RS['data']     = $defaultValues;
    $RS['EnableFields']     = [];
    print_R(json_encode($RS, true));

    exit;
}

function plugin_data_exam_paper_2_edit_default_data_before_submit($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code

    $sql            = "SELECT * FROM `data_exam_paper` where id='$id'";
    $rs             = $db->CacheExecute(180,$sql);
    $试卷信息       = $rs->fields;
    $试题抽取方式   = $试卷信息['试题抽取方式'];
    $题库分类       = $试卷信息['题库分类'];
    $单选题目数量   = $试卷信息['单选题目数量'];
    $多选题目数量   = $试卷信息['多选题目数量'];
    $判断题目数量   = $试卷信息['判断题目数量'];

    $RS = [];
    $RS['status'] = "ERROR";
    $RS['msg'] = __("sql execution failed");
    $RS['试卷信息'] = $试卷信息;
    $RS['_GET'] = $_GET;
    $RS['_POST'] = $_POST;
    print json_encode($RS);

    exit;
}

function plugin_data_exam_paper_2_edit_default_data_after_submit($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_view_default($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_delete_array($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_updateone($id)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

function plugin_data_exam_paper_2_import_default_data_before_submit($Element)  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
    return $Element;
}

function plugin_data_exam_paper_2_import_default_data_after_submit()  {
    global $db;
    global $SettingMap;
    global $MetaColumnNames;
    global $GLOBAL_USER;
    global $TableName;
    //Here is your write code
}

?>