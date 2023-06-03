<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$TableName          = "data_testform";

$MetaColumnNames    = $db->MetaColumnNames($TableName);
$MetaColumnNames    = array_values($MetaColumnNames);
//print_R($MetaColumnNames);
$MetaColumnNamesOptions = [];
$MetaColumnNamesOptionsAll = [];
$MetaColumnNamesOptionsAll[] = ['value'=>"Disabled", 'label'=>"Disabled"];
foreach($MetaColumnNames AS $Item) {
    if($Item!="id")   {
        $MetaColumnNamesOptions[] = ['value'=>$Item, 'label'=>$Item];
    }
    $MetaColumnNamesOptionsAll[] = ['value'=>$Item, 'label'=>$Item];
}

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
    $FieldsArray['CreateTime']  = date("Y-m-d H:i:s");

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

    ##################################################################################################################################
    $allFields['Batch_Approval'][] = ['name' => "Divider1", 'show'=>true, 'type'=>"divider", 'label' => __("Divider"), 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_Status_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Approval_Status_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_Status_Value", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Approval_Status_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_DateTime_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Approval_DateTime_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Approval_DateTime_Format = [];
    $Batch_Approval_DateTime_Format[] = ['value'=>"DateTime", 'label'=>__("DateTime")];
    $Batch_Approval_DateTime_Format[] = ['value'=>"Date", 'label'=>__("Date")];
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_DateTime_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Approval_DateTime_Format, 'label' => __("Batch_Approval_DateTime_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_User_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Approval_User_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Approval_User_Format = [];
    $Batch_Approval_User_Format[] = ['value'=>"UserID", 'label'=>__("UserID")];
    $Batch_Approval_User_Format[] = ['value'=>"UserName", 'label'=>__("UserName")];
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_User_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Approval_User_Format, 'label' => __("Batch_Approval_User_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_Review_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Approval_Review_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Approval'][]  = ['name' => "Batch_Approval_Review_Opinion", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Approval_Review_Opinion"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_1", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_1"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_1", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_2", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_2"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_2", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_3", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_3"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_3", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_4", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_4"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_4", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_5", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_5"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_5", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Change_Field_When_Batch_Approval_6", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Approval_6"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Approval'][]  = ['name' => "Change_Into_Value_When_Batch_Approval_6", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Approval'][]  = ['name' => "Divider1", 'show'=>true, 'type'=>"divider", 'label' => __("Divider"), 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];

    ##################################################################################################################################
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_Status_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Refuse_Status_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_Status_Value", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Refuse_Status_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_DateTime_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Refuse_DateTime_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Refuse_DateTime_Format = [];
    $Batch_Refuse_DateTime_Format[] = ['value'=>"DateTime", 'label'=>__("DateTime")];
    $Batch_Refuse_DateTime_Format[] = ['value'=>"Date", 'label'=>__("Date")];
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_DateTime_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Refuse_DateTime_Format, 'label' => __("Batch_Refuse_DateTime_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_User_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Refuse_User_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Refuse_User_Format = [];
    $Batch_Refuse_User_Format[] = ['value'=>"UserID", 'label'=>__("UserID")];
    $Batch_Refuse_User_Format[] = ['value'=>"UserName", 'label'=>__("UserName")];
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_User_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Refuse_User_Format, 'label' => __("Batch_Refuse_User_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_Review_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Refuse_Review_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Refuse'][]  = ['name' => "Batch_Refuse_Review_Opinion", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Refuse_Review_Opinion"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_1", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_1"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_1", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_2", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_2"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_2", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_3", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_3"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_3", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_4", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_4"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_4", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_5", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_5"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_5", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Field_When_Batch_Refuse_6", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Refuse_6"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Refuse'][]  = ['name' => "Change_Into_Value_When_Batch_Refuse_6", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Refuse'][]  = ['name' => "Divider2", 'show'=>true, 'type'=>"divider", 'label' => __("Divider"), 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];

    ##################################################################################################################################
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_Status_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Cancel_Status_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_Status_Value", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Cancel_Status_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_DateTime_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Cancel_DateTime_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Cancel_DateTime_Format = [];
    $Batch_Cancel_DateTime_Format[] = ['value'=>"DateTime", 'label'=>__("DateTime")];
    $Batch_Cancel_DateTime_Format[] = ['value'=>"Date", 'label'=>__("Date")];
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_DateTime_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Cancel_DateTime_Format, 'label' => __("Batch_Cancel_DateTime_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_User_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Cancel_User_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $Batch_Cancel_User_Format = [];
    $Batch_Cancel_User_Format[] = ['value'=>"UserID", 'label'=>__("UserID")];
    $Batch_Cancel_User_Format[] = ['value'=>"UserName", 'label'=>__("UserName")];
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_User_Format", 'show'=>true, 'type'=>'radiogroup', 'options'=>$Batch_Cancel_User_Format, 'label' => __("Batch_Cancel_User_Format"), 'value' => "DateTime", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_Review_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Cancel_Review_Field"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>8]];
    $allFields['Batch_Cancel'][]  = ['name' => "Batch_Cancel_Review_Opinion", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Cancel_Review_Opinion"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_1", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_1"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_1", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_2", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_2"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_2", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_3", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_3"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_3", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_4", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_4"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_4", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_5", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_5"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_5", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Field_When_Batch_Cancel_6", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Change_Field_When_Batch_Cancel_6"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Batch_Cancel'][]  = ['name' => "Change_Into_Value_When_Batch_Cancel_6", 'show'=>true, 'type'=>"input", 'label' => __("Change_Into_Value"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['false' => true,'xs'=>12, 'sm'=>2, 'disabled' => false]];
    
    $allFields['Batch_Cancel'][]  = ['name' => "Divider3", 'show'=>true, 'type'=>"divider", 'label' => __("Divider"), 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];

    
    foreach($allFields as $ModeName=>$allFieldItem) {
        foreach($allFieldItem as $ITEM) {
            $defaultValues[$ITEM['name']] = $ITEM['value'];
        }
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
    $allFieldsMode[] = ['value'=>"Batch_Approval", 'label'=>__("Batch_Approval")];
    $allFieldsMode[] = ['value'=>"Batch_Refuse", 'label'=>__("Batch_Refuse")];
    $allFieldsMode[] = ['value'=>"Batch_Cancel", 'label'=>__("Batch_Cancel")];
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
