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
