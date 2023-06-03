<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$TableName          = "data_testform";
$MetaColumnNames    = $db->MetaColumnNames($TableName);
$MetaColumnNames    = array_values($MetaColumnNames);
//print_R($MetaColumnNames);


$FieldTypeShow = [
                'FieldTypeFollowByFormSetting'=>'正常启用且沿用表单中设计的类型',
                'List_Use_AddEditView_NotUse'=>'列表视图中显示但新建和编辑查看视图中不启用',
                'ListView_Use_AddEdit_NotUse'=>'列表查看视图中显示但新建和编辑视图中不启用',
                'View_Use_ListAddEdit_NotUse'=>'列表新建编辑视图中不显示但查看视图中启用',
                'ListAddView_Use_Edit_Readonly'=>'列表视图中显示但编辑视图中显示为只读',
                'ListView_Use_AddEdit_Readonly'=>'列表视图中显示但新建和编辑视图中显示为只读',
                'ListAddEdit_Use_View_NotUse'=>'禁用查看其它沿用原值',
                'Disabled'=>'禁用',
                'HiddenUserID'=>'隐藏用户名',
                'HiddenUsername'=>'隐藏姓名',
                'HiddenDeptID'=>'隐藏部门ID',
                'HiddenDeptName'=>'隐藏部门名称',
                'HiddenStudentCode'=>'隐藏学生学号',
                'HiddenStudentName'=>'隐藏学生姓名',
                'HiddenStudentClassID'=>'隐藏学生班级',
                'HiddenStudentSeatID'=>'隐藏学生座号',
                'HiddenStudentGenderID'=>'隐藏学生性别',
                'HiddenStudentNianJi'=>'隐藏学生年级',
                'HiddenStudentZhuanYe'=>'隐藏学生专业',
                'HiddenStudentXi'=>'隐藏学生系部',
                ];

$FormFieldSelectOptions = [];
$FormFieldSelectOptions[] = ['value'=>'FieldTypeFollowByFormSetting', 'label'=>__('FieldTypeFollowByFormSetting')];
$FormFieldSelectOptions[] = ['value'=>'List_Use_AddEditView_NotUse', 'label'=>__('List_Use_AddEditView_NotUse')];
$FormFieldSelectOptions[] = ['value'=>'ListView_Use_AddEdit_NotUse', 'label'=>__('ListView_Use_AddEdit_NotUse')];
$FormFieldSelectOptions[] = ['value'=>'View_Use_ListAddEdit_NotUse', 'label'=>__('View_Use_ListAddEdit_NotUse')];
$FormFieldSelectOptions[] = ['value'=>'ListAddView_Use_Edit_Readonly', 'label'=>__('ListAddView_Use_Edit_Readonly')];
$FormFieldSelectOptions[] = ['value'=>'ListView_Use_AddEdit_Readonly', 'label'=>__('ListView_Use_AddEdit_Readonly')];
$FormFieldSelectOptions[] = ['value'=>'ListAddEdit_Use_View_NotUse', 'label'=>__('ListAddEdit_Use_View_NotUse')];
$FormFieldSelectOptions[] = ['value'=>'HiddenUserID', 'label'=>__('HiddenUserID')];
$FormFieldSelectOptions[] = ['value'=>'HiddenUsername', 'label'=>__('HiddenUsername')];
$FormFieldSelectOptions[] = ['value'=>'HiddenDeptID', 'label'=>__('HiddenDeptID')];
$FormFieldSelectOptions[] = ['value'=>'HiddenDeptName', 'label'=>__('HiddenDeptName')];

$YesOrNotOptions = [];
$YesOrNotOptions[] = ['value'=>'Yes', 'label'=>__('Yes')];
$YesOrNotOptions[] = ['value'=>'No', 'label'=>__('No')];

$FormID     = ParamsFilter($_REQUEST['formid']);
$Step       = ParamsFilter($_REQUEST['step']);


if($_GET['action']=="edit_default_data"&&$FormID!=""&&$Step!="")     {
    $sql    = "select * from form_formflow where FormID = '$FormID' and Step = '$Step'";
    $rs     = $db->Execute($sql);
    $FlowName   = $rs->fields['FlowName'];
    $Setting    = $rs->fields['Setting'];
    $SettingMap = unserialize(base64_decode($Setting));
    $FlowName   = $rs->fields['FlowName'];
    foreach($_POST as $value => $label)  {
        $SettingMap[$value] = $label;
    }
    $FieldsArray = [];
    $FieldsArray['FlowName']    = $SettingMap['FlowName'];
    $FieldsArray['FormID']      = $FormID;
    $FieldsArray['Step']        = $Step;
    $FieldsArray['Setting']     = base64_encode(serialize($SettingMap));
    $FieldsArray['Creator']     = "admin";
    $FieldsArray['CreateTime'] = date("Y-m-d H:i:s");

    [$rs,$sql] = InsertOrUpdateTableByArray("form_formflow",$FieldsArray,'FormID,Step',0);
    if($rs->EOF) {
        $RS['status'] = "OK";
        $RS['msg'] = __("Submit Success");
        print json_encode($RS);
        exit;  
    }
    else {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("sql execution failed");
        $RS['sql'] = $sql;
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }
}

if($_GET['action']=="edit_default")         {
    $allFields = [];
    for($i=1;$i<sizeof($MetaColumnNames);$i++)   {
        $FieldName = $MetaColumnNames[$i];
        //$allFields['Default'][] = ['FieldName'=>$FieldName,'FieldType'=>"FieldTypeFollowByFormSetting",'FieldGoup'=>"No",'FieldSearch'=>"No",'FieldImport'=>"No"];
        $defaultValues["FieldType_".$FieldName] = $FormFieldSelectOptions[0]['value'];
        $allFields['Default'][] = ['name' => "FieldType_".$FieldName, 'show'=>true, 'type'=>'select', 'options'=>$FormFieldSelectOptions, 'label' => $FieldName, 'value' => $FormFieldSelectOptions[0]['value'], 'placeholder' => $FieldName, 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>6]];
        $defaultValues["FieldGroup_".$FieldName] = $FormGroup[1]['value'];
        $allFields['Default'][] = ['name' => "FieldGroup_".$FieldName, 'show'=>true, 'type'=>'Switch', 'options'=>$FormGroup, 'label' => "Field Group", 'value' => $FormGroup[0]['value'], 'placeholder' => $FieldName, 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>4, 'sm'=>2]];
        $defaultValues["FieldSearch_".$FieldName] = $FormGroup[1]['value'];
        $allFields['Default'][] = ['name' => "FieldSearch_".$FieldName, 'show'=>true, 'type'=>'Switch', 'options'=>$FormGroup, 'label' => "Field Search", 'value' => $FormGroup[0]['value'], 'placeholder' => $FieldName, 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>4, 'sm'=>2]];
        $defaultValues["FieldImport_".$FieldName] = $FormGroup[1]['value'];
        $allFields['Default'][] = ['name' => "FieldImport_".$FieldName, 'show'=>true, 'type'=>'Switch', 'options'=>$FormGroup, 'label' => "Field Import", 'value' => $FormGroup[0]['value'], 'placeholder' => $FieldName, 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>4, 'sm'=>2]];
    }
    if($FormID!=""&&$Step!="")   {
        $sql    = "select * from form_formflow where FormID = '$FormID' and Step = '$Step'";
        $rs     = $db->Execute($sql);
        $FlowName   = $rs->fields['FlowName'];
        $Setting    = $rs->fields['Setting'];
        $SettingMap = unserialize(base64_decode($Setting));
        $FlowName   = $rs->fields['FlowName'];
        if(is_array($SettingMap))   {
            foreach($SettingMap as $value => $label)  {
                $defaultValues[$value] = $label;
            }
        }
    }    
    $edit_default['allFields']      = $allFields;
    $allFieldsMode[] = ['value'=>"Default", 'label'=>__("")];
    $edit_default['allFieldsMode']  = $allFieldsMode;
    $edit_default['defaultValues']  = $defaultValues;
    $edit_default['dialogContentHeight']  = "90%";
    $edit_default['componentsize']  = "small";
    $edit_default['submitaction']   = "edit_default_data";
    $edit_default['submittext']     = __("Submit");
    $edit_default['canceltext']     = "";
    $edit_default['tablewidth']     = 550;

    $RS['edit_default'] = $edit_default;
    $RS['status'] = "OK";
    $RS['data'] = $defaultValues;
    $RS['sql'] = $sql;
    $RS['msg'] = __("Get Data Success");
    print_R(json_encode($RS));
}
