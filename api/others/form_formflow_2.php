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

    $allFields['Tip_In_Interface'][] = ['name' => "List_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("List_Title_Name"), 'value' => "List ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Add_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("Add_Title_Name"), 'value' => "Add ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Edit_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("Edit_Title_Name"), 'value' => "Edit ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "View_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("View_Title_Name"), 'value' => "View ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Import_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("Import_Title_Name"), 'value' => "Import ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Export_Title_Name", 'show'=>true, 'type'=>"input", 'label' => __("Export_Title_Name"), 'value' => "Export ".$TableName, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $allFields['Tip_In_Interface'][] = ['name' => "Rename_Add_Submit_Button", 'show'=>true, 'type'=>"input", 'label' => __("Rename_Add_Submit_Button"), 'value' => "Submit", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>3, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Rename_Edit_Submit_Button", 'show'=>true, 'type'=>"input", 'label' => __("Rename_Edit_Submit_Button"), 'value' => "Submit", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>3, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Rename_List_Edit_Button", 'show'=>true, 'type'=>"input", 'label' => __("Rename_List_Edit_Button"), 'value' => "Edit", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>3, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Rename_List_Delete_Button", 'show'=>true, 'type'=>"input", 'label' => __("Rename_List_Delete_Button"), 'value' => "Delete", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>3, 'disabled' => false]];
    
    $allFields['Tip_In_Interface'][] = ['name' => "Tip_When_Add_Success", 'show'=>true, 'type'=>"input", 'label' => __("Tip_When_Add_Success"), 'value' => "Add Data Success!", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Tip_In_Interface'][] = ['name' => "Tip_When_Edit_Success", 'show'=>true, 'type'=>"input", 'label' => __("Tip_When_Edit_Success"), 'value' => "Edit Data Success!", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    
    $Rules_When_Import = [];
    $Rules_When_Import[] = ['value'=>"Import_And_Export", 'label'=>__("Import_And_Export")];
    $Rules_When_Import[] = ['value'=>"Only_Import", 'label'=>__("Only_Import")];
    $Rules_When_Import[] = ['value'=>"Only_Export", 'label'=>__("Only_Export")];
    $allFields['Tip_In_Interface'][] = ['name' => "Rules_When_Import", 'show'=>true, 'type'=>'select', 'options'=>$Rules_When_Import, 'label' => __("Rules_When_Import"), 'value' => $Rules_When_Import[0]['value'], 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $Actions_In_List_Header = [];
    $Actions_In_List_Header[] = ['value'=>"Add", 'label'=>__("Add")];
    $Actions_In_List_Header[] = ['value'=>"Export", 'label'=>__("Export")];
    $Actions_In_List_Header[] = ['value'=>"Import", 'label'=>__("Import")];
    $allFields['Tip_In_Interface'][] = ['name' => "Actions_In_List_Header", 'show'=>true, 'type'=>'checkbox', 'options'=>$Actions_In_List_Header, 'label' => __("Actions_In_List_Header"), 'value' => "Add,Import,Export", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $Actions_In_List_Row = [];
    $Actions_In_List_Row[] = ['value'=>"Edit", 'label'=>__("Edit")];
    $Actions_In_List_Row[] = ['value'=>"Delete", 'label'=>__("Delete")];
    $Actions_In_List_Row[] = ['value'=>"View", 'label'=>__("View")];
    $allFields['Tip_In_Interface'][] = ['name' => "Actions_In_List_Row", 'show'=>true, 'type'=>'checkbox', 'options'=>$Actions_In_List_Row, 'label' => __("Actions_In_List_Row"), 'value' => "Edit,Delete,View", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    
    $Page_Number_In_List = [];
    $Page_Number_In_List[] = ['value'=>10, 'label'=>10];
    $Page_Number_In_List[] = ['value'=>20, 'label'=>20];
    $Page_Number_In_List[] = ['value'=>30, 'label'=>30];
    $Page_Number_In_List[] = ['value'=>40, 'label'=>40];
    $Page_Number_In_List[] = ['value'=>50, 'label'=>50];
    $Page_Number_In_List[] = ['value'=>100, 'label'=>100];
    $Page_Number_In_List[] = ['value'=>200, 'label'=>200];
    $Page_Number_In_List[] = ['value'=>500, 'label'=>500];
    $allFields['Tip_In_Interface'][] = ['name' => "Page_Number_In_List", 'show'=>true, 'type'=>'select', 'options'=>$Page_Number_In_List, 'label' => __("Page_Number_In_List"), 'value' => 10, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];

    $Bottom_Button_Actions = [];
    $Bottom_Button_Actions[] = ['value'=>"Edit", 'label'=>__("Edit")];
    $Bottom_Button_Actions[] = ['value'=>"Delete", 'label'=>__("Delete")];
    $Bottom_Button_Actions[] = ['value'=>"Batch_Approval", 'label'=>__("Batch_Approval")];
    $Bottom_Button_Actions[] = ['value'=>"Batch_Reject", 'label'=>__("Batch_Reject")];
    $Bottom_Button_Actions[] = ['value'=>"Batch_Cancel", 'label'=>__("Batch_Cancel")];
    $Bottom_Button_Actions[] = ['value'=>"Reset_Password_123654", 'label'=>__("Reset_Password_123654")];
    $Bottom_Button_Actions[] = ['value'=>"Reset_Password_ID_Last6", 'label'=>__("Reset_Password_ID_Last6")];
    $Bottom_Button_Actions[] = ['value'=>"Batch_Setting_One", 'label'=>__("Batch_Setting_One")];
    $Bottom_Button_Actions[] = ['value'=>"Batch_Setting_Two", 'label'=>__("Batch_Setting_Two")];
    $allFields['Setting_Buttons'][] = ['name' => "Bottom_Button_Actions", 'show'=>true, 'type'=>'checkbox', 'options'=>$Bottom_Button_Actions, 'label' => __("Bottom_Button_Actions"), 'value' => "Edit,Delete", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>12]];

    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_One_Name", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Setting_One_Name"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_One_Change_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Setting_One_Change_Field"), 'value' => '', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_One_Additional_Display_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Setting_One_Additional_Display_Field"), 'value' => 10, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];

    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_Two_Name", 'show'=>true, 'type'=>"input", 'label' => __("Batch_Setting_Two_Name"), 'value' => "", 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false,'xs'=>12, 'sm'=>4, 'disabled' => false]];
    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_Two_Change_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Setting_Two_Change_Field"), 'value' => '', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Setting_Buttons'][] = ['name' => "Batch_Setting_Two_Additional_Display_Field", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Batch_Setting_Two_Additional_Display_Field"), 'value' => 10, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];

    $allFields['Setting_Buttons'][] = ['name' => "Which_Field_Store_Password_When_Enable_Change_Password", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptions, 'label' => __("Which_Field_Store_Password_When_Enable_Change_Password"), 'value' => 10, 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];
    
    $Default_Order_Method_By_Desc = [];
    $Default_Order_Method_By_Desc[] = ['value'=>"Desc", 'label'=>__("Desc")];
    $Default_Order_Method_By_Desc[] = ['value'=>"Asc", 'label'=>__("Asc")];
    
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Field_One", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptionsAll, 'label' => __("Default_Order_Method_By_Field_One"), 'value' => $MetaColumnNamesOptionsAll[1]['value'], 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Desc_One", 'show'=>true, 'type'=>'select', 'options'=>$Default_Order_Method_By_Desc, 'label' => __("Desc_Or_Asc_One"), 'value' => 'Desc', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>2]];
    
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Field_Two", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptionsAll, 'label' => __("Default_Order_Method_By_Field_Two"), 'value' => $MetaColumnNamesOptionsAll[0]['value'], 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Desc_Two", 'show'=>true, 'type'=>'select', 'options'=>$Default_Order_Method_By_Desc, 'label' => __("Desc_Or_Asc_Two"), 'value' => 'Desc', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>2]];
    
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Field_Three", 'show'=>true, 'type'=>'select', 'options'=>$MetaColumnNamesOptionsAll, 'label' => __("Default_Order_Method_By_Field_Three"), 'value' => $MetaColumnNamesOptionsAll[0]['value'], 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>4]];
    $allFields['Page_Sort'][] = ['name' => "Default_Order_Method_By_Desc_Three", 'show'=>true, 'type'=>'select', 'options'=>$Default_Order_Method_By_Desc, 'label' => __("Desc_Or_Asc_Three"), 'value' => 'Desc', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>2]];

    $allFields['Page_Sort'][] = ['name' => "Debug_Sql_Show_On_Api", 'show'=>true, 'type'=>'radiogroup', 'options'=>$YesOrNotOptions, 'label' => __("Debug_Sql_Show_On_Api"), 'value' => 'No', 'placeholder' => "", 'helptext' => "", 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>6]];
    
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
    $allFieldsMode[] = ['value'=>"Tip_In_Interface", 'label'=>__("Tip_In_Interface")];
    $allFieldsMode[] = ['value'=>"Setting_Buttons", 'label'=>__("Setting_Buttons")];
    $allFieldsMode[] = ['value'=>"Page_Sort", 'label'=>__("Page_Sort")];
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
