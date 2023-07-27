<?php
header("Content-Type: application/json"); 
$TIME_BEGIN = time();
require_once('cors.php');
require_once('include.inc.php');
require_once('data_enginee_function.php');

//print "TIME EXCEUTE 0:".(time()-$TIME_BEGIN)."<BR>\n";
//$FormId = 16;
//$Step = 1;

global $GLOBAL_EXEC_KEY_SQL;
$GLOBAL_EXEC_KEY_SQL = [];
$AdditionalPermissionsSQL = "";

//Get Form Flow Setting
if($FlowId!="")  {
	$sql    = "select * from form_formflow where id='$FlowId'";
}
else {
	$sql    = "select * from form_formflow where FormId='$FormId' and Step='$Step'";
}
$rs         = $db->CacheExecute(10, $sql);
$FromInfo   = $rs->fields;
$FormId  	= $FromInfo['FormId'];
$FlowId  	= $FromInfo['id'];
$FlowName  	= $FromInfo['FlowName'];
$Step  		= $FromInfo['Step'];
$Setting  	= $FromInfo['Setting'];
$FaceTo  	= $FromInfo['FaceTo'];

//Except CSRS
global $ExceptCsrf;
$ExceptCsrf[] = "/apps/apps_19.php";
$ExceptCsrf[] = "/apps/apps_19.php";

if($FaceTo=="AuthUser")         {
    //Check User Login or Not
    CheckAuthUserLoginStatus();
    CheckAuthUserRoleHaveMenu($FlowId);
    CheckCsrsToken();
}
if($FaceTo=="Student")         {
    //Check User Login or Not
    CheckAuthUserLoginStatus();
    //CheckAuthUserRoleHaveMenu($FlowId);
    CheckCsrsToken();
}
//print "TIME EXCEUTE 1:".(time()-$TIME_BEGIN)."<BR>\n";
$rowHeight = 38;
$sqlList = [];

global $SettingMap;
$SettingMap = unserialize(base64_decode($Setting));
$Actions_In_List_Row_Array = explode(',',$SettingMap['Actions_In_List_Row']);
$Actions_In_List_Header_Array = explode(',',$SettingMap['Actions_In_List_Header']);
//print_R($SettingMap);exit;
//print "TIME EXCEUTE 2:".(time()-$TIME_BEGIN)."<BR>\n";

//Get Table Infor
$sql        = "select * from form_formname where id='$FormId'";
$rs         = $db->CacheExecute(180, $sql);
$FromInfo   = $rs->fields;
$TableName  = $FromInfo['TableName'];
global $FormName;
$FormName   = $FromInfo['ShortName'];

//EnablePluginsForIndividual
if($SettingMap['EnablePluginsForIndividual']=="Enable" && $TableName!="" && $Step>0 && is_file("../plugins/plugin_".$TableName."_".$Step.".php"))    {
    require_once("../plugins/plugin_".$TableName."_".$Step.".php");
}

//Get form_formfield_showtype
$sql        = "select * from form_formfield_showtype";
$rs         = $db->CacheExecute(180, $sql);
$AllShowTypes   = $rs->GetArray();
$AllShowTypesArray = [];
foreach($AllShowTypes as $Item)  {
    $AllShowTypesArray[$Item['Name']] = $Item;
}
//print "TIME EXCEUTE 3:".(time()-$TIME_BEGIN)."<BR>\n";

//Get All Fields
$sql        = "select * from form_formfield where FormId='$FormId' and IsEnable='1' order by SortNumber asc, id asc";
$rs         = $db->Execute($sql);
$AllFieldsFromTable   = $rs->GetArray();
$AllFieldsMap = [];
foreach($AllFieldsFromTable as $Item)  {
    $AllFieldsMap[$Item['FieldName']] = $Item;
    $LocaleFieldArray[$Item['EnglishName']] = $Item['FieldName'];
    $LocaleFieldArray[$Item['ChineseName']] = $Item['FieldName'];
}
//print "TIME EXCEUTE 4:".(time()-$TIME_BEGIN)."<BR>\n";

$MetaColumnNames    = GLOBAL_MetaColumnNames($TableName); 
$UniqueKey          = $MetaColumnNames[1];

//Extra Role
$AddSql = " where 1=1 ";
require_once('data_enginee_filter_role.php');

//print "TIME EXCEUTE 6:".(time()-$TIME_BEGIN)."<BR>\n";

global $InsertOrUpdateFieldArrayForSql;
$InsertOrUpdateFieldArrayForSql['ADD']  = [];
$InsertOrUpdateFieldArrayForSql['EDIT'] = [];

$defaultValuesAdd  = [];
$defaultValuesEdit = [];


$allFieldsAdd   = getAllFields($AllFieldsFromTable, $AllShowTypesArray, 'ADD', true, $SettingMap);
foreach($allFieldsAdd as $ModeName=>$allFieldItem) {
    foreach($allFieldItem as $ITEM) {
        $defaultValuesAdd[$ITEM['name']] = $ITEM['value'];
        if($ITEM['code']!="") {
            $defaultValuesAdd[$ITEM['code']] = $ITEM['value'];
        }
    }
}

$allFieldsEdit  = getAllFields($AllFieldsFromTable, $AllShowTypesArray, 'EDIT', true, $SettingMap);
foreach($allFieldsEdit as $ModeName=>$allFieldItem) {
    foreach($allFieldItem as $ITEM) {
        $defaultValuesEdit[$ITEM['name']] = $ITEM['value'];
    }
}

$allFieldsView  = getAllFields($AllFieldsFromTable, $AllShowTypesArray, 'VIEW', true, $SettingMap);
foreach($allFieldsView as $ModeName=>$allFieldItem) {
    foreach($allFieldItem as $ITEM) {
        $allFieldsView[$ITEM['name']] = $ITEM['value'];
    }
}

//Import Page Structure
$Import_Rule_Method = [];
$Import_Rule_Method[] = ['value'=>"BothInsertAndUpdate", 'label'=>__("BothInsertAndUpdate")];
$Import_Rule_Method[] = ['value'=>"OnlyUpdate", 'label'=>__("OnlyUpdate")];
$Import_Rule_Method[] = ['value'=>"OnlyInsert", 'label'=>__("OnlyInsert")];
$allFieldsImport['Default'][] = ['name' => "Import_Rule_Method", 'show'=>true, 'type'=>'select', 'options'=>$Import_Rule_Method, 'label' => __("Step1_Choose_Import_Rule"), 'value' => $Import_Rule_Method[0]['value'], 'placeholder' => "", 'helptext' => __(""), 'rules' => ['required' => false, 'disabled' => false, 'xs'=>12, 'sm'=>12]];

$Import_Fields          = [];
$Import_Fields_Default  = [];
foreach($AllFieldsFromTable as $Item)  {
    if($SettingMap["FieldImport_".$Item['FieldName']]=="true" || $SettingMap["FieldImport_".$Item['FieldName']]=="1")   {
        $Import_Fields[]            = ['value'=>$Item['FieldName'], 'label'=>$Item['ChineseName']];
        $Import_Fields_Default[]    = $Item['FieldName'];
    }
}
$allFieldsImport['Default'][] = ['name' => "Import_Fields", 'show'=>true, 'type'=>'checkbox', 'options'=>$Import_Fields, 'label' => __("Step2_Choose_Import_Fields"), 'value' => join(',', $Import_Fields_Default), 'placeholder' => "", 'helptext' => __(""), 'rules' => ['required' => true, 'disabled' => false, 'xs'=>12, 'sm'=>12, 'row'=>true]];

$TEMPARRAY                      = [];
$TEMPARRAY['TableName']         = $TableName;
$TEMPARRAY['Action']            = "export_template";
$TEMPARRAY['FormId']            = $FormId;
$TEMPARRAY['FlowId']            = $FlowId;
$TEMPARRAY['FileName']          = $FormName;
$TEMPARRAY['Time']              = time();
$DATATEMP                       = EncryptID(serialize($TEMPARRAY));
$URLTEMP                        = "data_export.php?DATA=".$DATATEMP;
$allFieldsImport['Default'][] = ['name' => "Import_Template", 'show'=>true, 'FieldTypeArray'=>[], 'type'=>'buttonurl', 'label' => __("Import_Template_File"), 'value' => $URLTEMP, 'placeholder' => "", 'helptext' => __(""), 'rules' => ['required' => false,'xs'=>12, 'sm'=>12, 'disabled' => false] ];

$allFieldsImport['Default'][] = ['name' => "Import_File", 'show'=>true, 'FieldTypeArray'=>[], 'type'=>'xlsx', 'label' => __("Step3_Upload_Excel_File"), 'value' => "", 'placeholder' => "", 'helptext' => __(""), 'rules' => ['required' => true,'xs'=>12, 'sm'=>12, 'disabled' => false] ];


foreach($allFieldsImport as $ModeName=>$allFieldItem) {
    foreach($allFieldItem as $ITEM) {
        $defaultValuesImport[$ITEM['name']] = $ITEM['value'];
    }
}

$allFieldsExport        = [];
foreach($allFieldsView as $ModeName=>$allFieldItem) {
    foreach($allFieldItem as $ITEM) {
        if($SettingMap["FieldExport_".$ITEM['name']]=="true" || $SettingMap["FieldExport_".$ITEM['name']]=="1" || $SettingMap["FieldExport_".$ITEM['code']]=="true" || $SettingMap["FieldExport_".$ITEM['code']]=="1")   {
            $allFieldsExport[$ModeName][] = $ITEM;
        }
    }
}

//print "TIME EXCEUTE 7:".(time()-$TIME_BEGIN)."<BR>\n";
//UpdateOtherTableFieldAfterFormSubmit($id);

if($_GET['action']=="option_multi_approval")  {
    $option_multi_approval = option_multi_approval_exection($_POST['selectedRows'], $_POST['multiReviewInputValue'], $Reminder=1, $UpdateOtherTableField=1);
    print $option_multi_approval;
    exit;
}

if($_GET['action']=="option_multi_refuse")  {
    $option_multi_refuse = option_multi_refuse_exection($_POST['selectedRows'], $_POST['multiReviewInputValue'], $Reminder=1, $UpdateOtherTableField=1);
    print $option_multi_refuse;
    exit;
}

if($_GET['action']=="option_multi_cancel")  {
    $option_multi_cancel = option_multi_cancel_exection($_POST['selectedRows'], $_POST['multiReviewInputValue'], $Reminder=1, $UpdateOtherTableField=1);
    print $option_multi_cancel;
    exit;
}

if( $_GET['action']=="import_default_data" && in_array('Import',$Actions_In_List_Header_Array) && $TableName!="")  {
    
    //Filter data when do add save operation
    require_once('data_enginee_filter_post.php');    
    $MetaColumnNames    = GLOBAL_MetaColumnNames($TableName);
    
    $filePath = $_FILES['Import_File']['tmp_name']['0'];
    if(!is_file($filePath))  {
        $RS             = [];
        $RS['status']   = "ERROR";
        $RS['msg']      = __("Upload File Not Exist");
        $RS['data']     = $data;
        print json_encode($RS);
        exit;
    }

    //Read Data From Excel
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    $data = [];
    for ($row = 1; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
            $rowData[] = trim($cellValue);
        }
        $data[] = $rowData;
    }
    $Header         = $data[0];
    $FieldToIndex   = array_flip($Header);

    //Import Parse Data
    $Import_Fields_Unique_1 = $SettingMap['Import_Fields_Unique_1'];
    $Import_Fields_Unique_2 = $SettingMap['Import_Fields_Unique_2'];
    $Import_Fields_Unique_3 = $SettingMap['Import_Fields_Unique_3'];
    $ImportUniqueFields = [];
    if($Import_Fields_Unique_1!="Disabled" && $Import_Fields_Unique_1!="" && $Import_Fields_Unique_1!="id")  {
        $ImportUniqueFields[] = $Import_Fields_Unique_1;
    }
    if($Import_Fields_Unique_2!="Disabled" && $Import_Fields_Unique_2!="" && $Import_Fields_Unique_2!="id")  {
        $ImportUniqueFields[] = $Import_Fields_Unique_2;
    }
    if($Import_Fields_Unique_3!="Disabled" && $Import_Fields_Unique_3!="" && $Import_Fields_Unique_3!="id")  {
        $ImportUniqueFields[] = $Import_Fields_Unique_3;
    }
    if(sizeof($ImportUniqueFields)==0)   {
        $RS             = [];
        $RS['status']   = "OK";
        $RS['msg']      = __("Import Unique Fields Not Config");
        print json_encode($RS);
        exit;
    }
    //Body Data
    $Import_Fields_Array = explode(',',$_POST['Import_Fields']);
    for ($row = 1; $row < sizeof($data); $row++) {
        $Element        = [];
        $IsExecutionSQL = 0;
        for ($column = 0; $column < sizeof($Header); $column++)         {
            $FieldName  = $LocaleFieldArray[$Header[$column]];
            if( in_array($FieldName, $MetaColumnNames) && in_array($FieldName,$Import_Fields_Array))  {
                $Element[$FieldName] = trim($data[$row][$column]);
                if($Element[$FieldName]!="")   {
                    $IsExecutionSQL = 1;
                }
            }
        }
        if(sizeof(array_keys($Element))<=sizeof($ImportUniqueFields)) {
            $RS             = [];
            $RS['status']   = "ERROR";
            $RS['msg']      = __("Import Fields Is Too Less");
            $RS['_GET']     = $_GET;
            $RS['_POST']    = $_POST;
            $RS['_FILES']   = $_FILES;
            $RS['sql']      = $sqlList;
            print json_encode($RS);
            exit;
        }
        if($IsExecutionSQL)    {
            //functionNameIndividual
            $functionNameIndividual = "plugin_".$TableName."_".$Step."_import_default_data_before_submit";
            if(function_exists($functionNameIndividual))  {
                $Element = $functionNameIndividual($Element);
            }
            
            $Import_Rule_Method = $_POST['Import_Rule_Method'];
            switch($Import_Rule_Method) {
                case 'BothInsertAndUpdate':
                    [$rs,$sql] = InsertOrUpdateTableByArray($TableName,$Element,join(',',$ImportUniqueFields),0,'InsertOrUpdate');
                    $sqlList[] = $sql;
                    break;
                case 'OnlyUpdate':
                    [$rs,$sql] = InsertOrUpdateTableByArray($TableName,$Element,join(',',$ImportUniqueFields),0,'Update');
                    $sqlList[] = $sql;
                    break;
                case 'OnlyInsert':
                    [$rs,$sql] = InsertOrUpdateTableByArray($TableName,$Element,join(',',$ImportUniqueFields),0,'Insert');
                    $sqlList[] = $sql;
                    break;
            }
            if($rs->EOF) {
            }
        }
        else {
            //Empty Row
        }
    }

    //functionNameIndividual
    $functionNameIndividual = "plugin_".$TableName."_".$Step."_import_default_data_after_submit";
    if(function_exists($functionNameIndividual))  {
        $functionNameIndividual();
    }

    if(1)   {
        $RS             = [];
        $RS['status']   = "OK";
        $RS['msg']      = __("Import Data Success");
        $RS['_GET']     = $_GET;
        $RS['_POST']    = $_POST;
        $RS['_FILES']   = $_FILES;
        $RS['sql']      = $sqlList;
        $RS['counter']  = sizeof($data);
        print json_encode($RS);
        exit;
    }

}

//编辑页面时的启用字段列表
if( $_GET['action']=="add_default_data" && in_array('Add',$Actions_In_List_Header_Array) && $TableName!="")  {
    
    //Filter data when do add save operation
    require_once('data_enginee_filter_post.php');
    $MetaColumns    = $db->MetaColumns($TableName);
    $MetaColumns    = array_values($MetaColumns);
    $MetaColumnsInDb = [];
    foreach($MetaColumns as $Item)  {
        $MetaColumnsInDb[$Item->name]       = $Item->type;
    }
    $MetaColumnNames    = GLOBAL_MetaColumnNames($TableName);
    
    //functionNameIndividual
    $functionNameIndividual = "plugin_".$TableName."_".$Step."_add_default_data_before_submit";
    if(function_exists($functionNameIndividual))  {
        $functionNameIndividual();
    }

    $FieldsArray        = [];
    $IsExecutionSQL     = 0;
    foreach($AllFieldsFromTable as $Item)  {
        if($_POST[$Item['FieldName']]!="") {            
            $IsExecutionSQL = 1;
        }
        // Give a default value for date and number
        $FieldType = $MetaColumnsInDb[$Item['FieldName']];
        if($_POST[$Item['FieldName']]=="") {    
            switch($FieldType)  {
                case 'int':
                    $_POST[$Item['FieldName']] = 0;
                    break;
                case 'date':
                    $_POST[$Item['FieldName']] = "1971-01-01";
                    break;
                case 'datetime':
                    $_POST[$Item['FieldName']] = "1971-01-01 00:00:00";
                    break;
            }
            $CurrentFieldType = $AllShowTypesArray[$AllFieldsMap[$Item['FieldName']]['ShowType']]['ADD'];
            switch($CurrentFieldType) {
                case 'autoincrement':
                    $sql = "select max(id) as NUM from $TableName";
                    $rs  = $db->Execute($sql);
                    $NUM = intval($rs->fields['NUM']);
                    $NUM += 1;
                    $FROM = 100000;
                    $NUM += $FROM;
                    $_POST[$Item['FieldName']] = $NUM;
                    break;
                case 'autoincrementdate':
                    $sql = "select max(id) as NUM from $TableName";
                    $rs  = $db->Execute($sql);
                    $NUM = $rs->fields['NUM'];
                    $NUM += 1;
                    $FROM = date('Ymd');
                    if(strlen($NUM)==1) {
                        $NUM = $FROM."000".$NUM;
                    }
                    else if(strlen($NUM)==2) {
                        $NUM = $FROM."00".$NUM;
                    }
                    else if(strlen($NUM)==3) {
                        $NUM = $FROM."0".$NUM;
                    }
                    $_POST[$Item['FieldName']] = $NUM;
                    break;
                case 'avatar':
                    if(is_array($_FILES[$Item['FieldName']]))    {
                        ImageUploadToDisk($Item['FieldName']);
                        $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                    }
                    elseif(strpos($_POST[$Item['FieldName']], "data_image.php?")!==false)  {
                        //Delete this Key from FieldsArray
                        $FieldsArray = array_diff_key($FieldsArray,[$Item['FieldName']=>""]);
                    }
                    break;
                case 'files':
                    if(is_array($_FILES[$Item['FieldName']]))    {
                        FilesUploadToDisk($Item['FieldName']);
                        $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                    }
                    elseif(strpos($_POST[$Item['FieldName']], "data_image.php?")!==false)  {
                        //Delete this Key from FieldsArray
                        $FieldsArray = array_diff_key($FieldsArray,[$Item['FieldName']=>""]);
                    }
                    break;
                case 'file':
                    if(is_array($_FILES[$Item['FieldName']]))    {
                        FilesUploadToDisk($Item['FieldName']);
                        $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                    }
                    elseif(strpos($_POST[$Item['FieldName']], "data_image.php?")!==false)  {
                        //Delete this Key from FieldsArray
                        $FieldsArray = array_diff_key($FieldsArray,[$Item['FieldName']=>""]);
                    }
                    break;
                case 'xlsx':
                    if(is_array($_FILES[$Item['FieldName']]))    {
                        FilesUploadToDisk($Item['FieldName']);
                        $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                    }
                    elseif(strpos($_POST[$Item['FieldName']], "data_image.php?")!==false)  {
                        //Delete this Key from FieldsArray
                        $FieldsArray = array_diff_key($FieldsArray,[$Item['FieldName']=>""]);
                    }
                    break;
            }
        }
        $FieldsArray[$Item['FieldName']]       = addslashes($_POST[$Item['FieldName']]);
    }
    if($IsExecutionSQL)   {
        global $InsertOrUpdateFieldArrayForSql; //Define in data_enginee_function.php
        foreach($InsertOrUpdateFieldArrayForSql['ADD'] as $FieldName=>$FieldValue)  {
            if($FieldValue!="")   {
                $FieldsArray[$FieldName]       = $FieldValue;
            }
        }

        //Split Multi Records
        $Add_Page_Split_Multi_Records_Value_Array = [];
        $Add_Page_Split_Multi_Records = $SettingMap['AddPageSplitMultiRecords'];
        if($Add_Page_Split_Multi_Records!="" && $Add_Page_Split_Multi_Records!="None" && in_array($Add_Page_Split_Multi_Records,$MetaColumnNames) )      {
            $Add_Page_Split_Multi_Records_Value_Array = explode(',', $FieldsArray[$Add_Page_Split_Multi_Records]);
        }
        else {
            //Default a Value for Not Need To Split
            $Add_Page_Split_Multi_Records = "id";
            $Add_Page_Split_Multi_Records_Value_Array = [NULL];
        }
        //Begin to Split Multi Records
        foreach($Add_Page_Split_Multi_Records_Value_Array as $Add_Page_Split_Multi_Records_Value)    {
            $FieldsArray[$Add_Page_Split_Multi_Records] = $Add_Page_Split_Multi_Records_Value;
            //Syncing To Other Fields
            if($Add_Page_Split_Multi_Records=="学号" || $Add_Page_Split_Multi_Records=="学生学号") {
                $sql     = "select * from data_student where 学号 = '".ForSqlInjection($Add_Page_Split_Multi_Records_Value)."'";
                $rsf     = $db->Execute($sql);
                in_array("系部",$MetaColumnNames) ? $FieldsArray['系部'] = $rsf->fields['系部'] : '';
                in_array("专业",$MetaColumnNames) ? $FieldsArray['专业'] = $rsf->fields['专业'] : '';
                in_array("班级",$MetaColumnNames) ? $FieldsArray['班级'] = $rsf->fields['班级'] : '';
                in_array("姓名",$MetaColumnNames) ? $FieldsArray['姓名'] = $rsf->fields['姓名'] : '';
                in_array("学生班级",$MetaColumnNames) ? $FieldsArray['学生班级'] = $rsf->fields['学生班级'] : '';
                in_array("学生姓名",$MetaColumnNames) ? $FieldsArray['学生姓名'] = $rsf->fields['学生姓名'] : '';
                in_array("身份证号",$MetaColumnNames) ? $FieldsArray['身份证号'] = $rsf->fields['身份证号'] : '';
                in_array("出生日期",$MetaColumnNames) ? $FieldsArray['出生日期'] = $rsf->fields['出生日期'] : '';
                in_array("性别",$MetaColumnNames) ? $FieldsArray['性别'] = $rsf->fields['性别'] : '';
                in_array("座号",$MetaColumnNames) ? $FieldsArray['座号'] = $rsf->fields['座号'] : '';
                in_array("学生宿舍",$MetaColumnNames) ? $FieldsArray['学生宿舍'] = $rsf->fields['学生宿舍'] : '';
                in_array("学生状态",$MetaColumnNames) ? $FieldsArray['学生状态'] = $rsf->fields['学生状态'] : '';
                in_array("学生手机",$MetaColumnNames) ? $FieldsArray['学生手机'] = $rsf->fields['学生手机'] : '';
            }
            //Unique Fields
            $SQL_Unique_Fields = ['1=1'];
            if($SettingMap['Unique_Fields_1']!="" && $SettingMap['Unique_Fields_1']!="None" && in_array($SettingMap['Unique_Fields_1'],$MetaColumnNames) ) {
                $SQL_Unique_Fields[] = $SettingMap['Unique_Fields_1']." = '".$FieldsArray[$SettingMap['Unique_Fields_1']]."' ";
            }
            if($SettingMap['Unique_Fields_2']!="" && $SettingMap['Unique_Fields_2']!="None" && in_array($SettingMap['Unique_Fields_2'],$MetaColumnNames) ) {
                $SQL_Unique_Fields[] = $SettingMap['Unique_Fields_2']." = '".$FieldsArray[$SettingMap['Unique_Fields_2']]."' ";
            }
            if($SettingMap['Unique_Fields_3']!="" && $SettingMap['Unique_Fields_3']!="None" && in_array($SettingMap['Unique_Fields_3'],$MetaColumnNames) ) {
                $SQL_Unique_Fields[] = $SettingMap['Unique_Fields_3']." = '".$FieldsArray[$SettingMap['Unique_Fields_3']]."' ";
            }
            if(sizeof($SQL_Unique_Fields)>1) {
                $sql    = "select COUNT(*) AS NUM from $TableName where ".join(" and ", $SQL_Unique_Fields)."";
                $rsTemp = $db->Execute($sql);
                if($rsTemp->fields['NUM']>=1) {
                    $RS = [];
                    $RS['status'] = "ERROR";
                    $RS['msg'] = $SettingMap['Unique_Fields_Repeat_Text']?$SettingMap['Unique_Fields_Repeat_Text']:__('Unique_Fields_Repeat_Text');
                    $RS['sql'] = $sql;
                    $RS['_GET'] = $_GET;
                    $RS['_POST'] = $_POST;
                    print json_encode($RS);
                    exit;
                }
            }

            //Execute Insert SQL
            $KEYS			= array_keys($FieldsArray);
            $VALUES			= array_values($FieldsArray);
            $sql	        = "insert into $TableName(`".join('`,`',$KEYS)."`) values('".join("','",$VALUES)."')";
            $rs             = $db->Execute($sql);
        }
        if($rs->EOF) {
            $NewId = $db->Insert_ID();
            UpdateOtherTableFieldAfterFormSubmit($NewId);
            $Msg_Reminder_Object_From_Add_Or_Edit_Result = Msg_Reminder_Object_From_Add_Or_Edit($TableName, $NewId);
            $RS['status'] = "OK";
            $RS['msg'] = $SettingMap['Tip_When_Add_Success'];
            $RS['Msg_Reminder_Object_From_Add_Or_Edit_Result'] = $Msg_Reminder_Object_From_Add_Or_Edit_Result;
            if($SettingMap['Debug_Sql_Show_On_Api']=="Yes")  {
                $RS['sql'] = $sql;  
                global $GLOBAL_EXEC_KEY_SQL;
                $RS['GLOBAL_EXEC_KEY_SQL'] = $GLOBAL_EXEC_KEY_SQL;              
            }
            
            //Relative Child Table Support
            $Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
            $Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
            $Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
            if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
                $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
                $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
                $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
                $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
                $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
                if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) &&strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')!==false) {
                    //Get All Fields
                    $db->BeginTrans();
                    $MultiSql                   = [];
                    $sql                        = "delete from $ChildTableName where $Relative_Child_Table_Parent_Field_Name = '".$FieldsArray[$Relative_Child_Table_Parent_Field_Name]."';";
                    $db->Execute($sql);
                    $MultiSql[]                 = $sql;
                    $sql                        = "select * from form_formfield where FormId='$ChildFormId' and IsEnable='1' order by SortNumber asc, id asc";
                    $rs                         = $db->Execute($sql);
                    $ChildAllFieldsFromTable    = $rs->GetArray();
                    $ChildAllFieldsMap          = [];
                    $ChildItemCounter           = $_POST['ChildItemCounter'];
                    for($X=0;$X<$ChildItemCounter;$X++)                    {
                        $ChildElement = [];
                        foreach($ChildAllFieldsFromTable as $Item)  {
                            $ChildFieldName = $Item['FieldName'];
                            switch($Item['ShowType']) {
                                case 'Hidden:Createtime':
                                    $ChildElement[$ChildFieldName] = date('Y-m-d H:i:s');
                                    break;
                                case 'Hidden:CurrentUserIdAdd':
                                case 'Hidden:CurrentUserIdAddEdit':
                                    $ChildElement[$ChildFieldName] = $GLOBAL_USER->USER_ID;
                                    break;
                                default:
                                    $ChildElement[$ChildFieldName] = ForSqlInjection($_POST['ChildTable____'.$X.'____'.$ChildFieldName]);
                                    break;
                            }                            
                        }
                        $deleteChildTableItemArray = explode(',',$_POST['deleteChildTableItemArray']);
                        if(!in_array($X, $deleteChildTableItemArray)) {
                            $ChildElement[$Relative_Child_Table_Parent_Field_Name] = $FieldsArray[$Relative_Child_Table_Parent_Field_Name];
                            $ChildKeys      = array_keys($ChildElement);
                            $ChildValues    = array_values($ChildElement);
                            $sql            = "insert into $ChildTableName (".join(',',$ChildKeys).") values('".join("','",$ChildValues)."');";
                            $db->Execute($sql);
                            $MultiSql[]     = $sql;
                        }
                    }
                    $db->CommitTrans();
                    $RS['MultiSql'] = $MultiSql;
                }
            }
            
            //functionNameIndividual
            $functionNameIndividual = "plugin_".$TableName."_".$Step."_add_default_data_after_submit";
            if(function_exists($functionNameIndividual))  {
                $functionNameIndividual($NewId);
            }
            //SystemLogRecord
            if(in_array($SettingMap['OperationLogGrade'],["AddEditAndDeleteOperation","AllOperation"]))  {
                $sql    = "select * from $TableName where ".$MetaColumnNames[0]." = '$NewId'";
                $Record = $db->Execute($sql); 
                SystemLogRecord("add_default_data", '', json_encode($Record->fields));
            }
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
    else {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("No POST Infor");
        $RS['sql'] = $sql;
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }
}

if( $_GET['action']=="edit_default_data" && in_array('Edit',$Actions_In_List_Row_Array) && $_GET['id']!="" && $TableName!="")  {
    if($TableName=="data_user" && $SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']=="email") {
        $EMAIL  = $GLOBAL_USER->email;
        $id     = returntablefield($TableName,"EMAIL",$EMAIL,"id")["id"];
    }
    else if($TableName=="data_user" && $SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']=="USER_ID") {
        $USER_ID  = $GLOBAL_USER->USER_ID;
        $id     = returntablefield($TableName,"USER_ID",$USER_ID,"id")["id"];
    }
    else if($SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']!="") {
        $id     = intval($SettingMap['Init_Action_FilterValue']);
    }
    else {        
        $id     = intval(DecryptID($_GET['id']));
    }
    if($id==0)   {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("Error Id Value");
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }
    $MetaColumnNames    = GLOBAL_MetaColumnNames($TableName);
    $FieldsArray        = [];
    $FieldsArray['id']  = $id;
    $IsExecutionSQL     = 0;
    $IsExecutionSQLChildTable     = 0;
    //Filter data when do edit save operation
    require_once('data_enginee_filter_post.php');

    //functionNameIndividual
    $functionNameIndividual = "plugin_".$TableName."_".$Step."_edit_default_data_before_submit";
    if(function_exists($functionNameIndividual))  {
        $functionNameIndividual($id);
    }

    global $InsertOrUpdateFieldArrayForSql; //Define in data_enginee_function.php
    //print_R($InsertOrUpdateFieldArrayForSql);exit;
    foreach($InsertOrUpdateFieldArrayForSql['EDIT'] as $FieldName=>$FieldValue)  {
        if($FieldValue=="")   {
            $FieldsArray[$FieldName]       = addslashes($_POST[$FieldName]);
        }
        else {
            $FieldsArray[$FieldName]       = $FieldValue;
        }
        if($_POST[$FieldName]!="") {
            $IsExecutionSQL = 1;
        }        
        if($_POST['ChildItemCounter']>0) {
            $IsExecutionSQLChildTable = 1;
        }
    }
    //Check Permission For This Record
    //LimitEditAndDelete
    $sql            = "select * from $TableName where ".$MetaColumnNames[0]." = '$id'";
    $RecordOriginal = $db->Execute($sql); 
    if($SettingMap['LimitEditAndDelete_Edit_Field_One']!="" && $SettingMap['LimitEditAndDelete_Edit_Field_One']!="None" && in_array($SettingMap['LimitEditAndDelete_Edit_Field_One'], $MetaColumnNames)) {
        $LimitEditAndDelete_Edit_Value_One_Array = explode(',',$SettingMap['LimitEditAndDelete_Edit_Value_One']);
        if(in_array($RecordOriginal->fields[$SettingMap['LimitEditAndDelete_Edit_Field_One']],$LimitEditAndDelete_Edit_Value_One_Array)) {
            $RS = [];
            $RS['status'] = "ERROR";
            $RS['msg'] = __("LimitEditAndDelete");
            $RS['_GET'] = $_GET;
            $RS['_POST'] = $_POST;
            print json_encode($RS);
            exit;
        }
    }
    if($SettingMap['LimitEditAndDelete_Edit_Field_Two']!="" && $SettingMap['LimitEditAndDelete_Edit_Field_Two']!="None" && in_array($SettingMap['LimitEditAndDelete_Edit_Field_Two'], $MetaColumnNames)) {
        $LimitEditAndDelete_Edit_Value_Two_Array = explode(',',$SettingMap['LimitEditAndDelete_Edit_Value_Two']);
        if(in_array($RecordOriginal->fields[$SettingMap['LimitEditAndDelete_Edit_Field_Two']],$LimitEditAndDelete_Edit_Value_Two_Array)) {
            $RS = [];
            $RS['status'] = "ERROR";
            $RS['msg'] = __("LimitEditAndDelete");
            $RS['_GET'] = $_GET;
            $RS['_POST'] = $_POST;
            print json_encode($RS);
            exit;
        }
    }

    foreach($AllFieldsFromTable as $Item)  {
        $CurrentFieldType = $AllShowTypesArray[$AllFieldsMap[$Item['FieldName']]['ShowType']]['EDIT'];
        switch($CurrentFieldType) {
            case 'avatar':
                if(is_array($_FILES[$Item['FieldName']]))    {
                    ImageUploadToDisk($Item['FieldName']);
                    $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                }
                elseif(strpos($_POST[$Item['FieldName']], "data_image.php?")!==false)  {
                    //Delete this Key from FieldsArray
                    $FieldsArray = array_diff_key($FieldsArray,[$Item['FieldName']=>""]);
                }
                break;
            case 'files':
                if(is_array($_FILES[$Item['FieldName']]))    {
                    FilesUploadToDisk($Item['FieldName']);
                    $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                }
                if(is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))  {
                    $OriginalValue = $RecordOriginal->fields[$Item['FieldName']];
                    $FieldsArray[$Item['FieldName']]    = AttachValueMinusOneFile($OriginalValue, $_POST[$Item['FieldName']."_OriginalFieldValue"], $FieldsArray[$Item['FieldName']]);
                }
                if(!is_array($_FILES[$Item['FieldName']]) && !is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))    {
                    $FieldsArray[$Item['FieldName']]    = "";
                }
                break;
            case 'file':
                if(is_array($_FILES[$Item['FieldName']]))    {
                    FilesUploadToDisk($Item['FieldName']);
                    $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                }
                if(is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))  {
                    $OriginalValue = $RecordOriginal->fields[$Item['FieldName']];
                    $FieldsArray[$Item['FieldName']]    = AttachValueMinusOneFile($OriginalValue, $_POST[$Item['FieldName']."_OriginalFieldValue"], $FieldsArray[$Item['FieldName']]);
                }
                if(!is_array($_FILES[$Item['FieldName']]) && !is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))    {
                    $FieldsArray[$Item['FieldName']]    = "";
                }
                break;
            case 'xlsx':
                if(is_array($_FILES[$Item['FieldName']]))    {
                    FilesUploadToDisk($Item['FieldName']);
                    $FieldsArray[$Item['FieldName']]    = addslashes($_POST[$Item['FieldName']]);
                }
                if(is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))  {
                    $OriginalValue = $RecordOriginal->fields[$Item['FieldName']];
                    $FieldsArray[$Item['FieldName']]    = AttachValueMinusOneFile($OriginalValue, $_POST[$Item['FieldName']."_OriginalFieldValue"], $FieldsArray[$Item['FieldName']]);
                }
                if(!is_array($_FILES[$Item['FieldName']]) && !is_array($_POST[$Item['FieldName']."_OriginalFieldValue"]))    {
                    $FieldsArray[$Item['FieldName']]    = "";
                }
                break;
        }
    }

    if($IsExecutionSQL || $IsExecutionSQLChildTable)   {
        [$Record,$sql]  = InsertOrUpdateTableByArray($TableName, $FieldsArray, 'id', 0, "Update");
        if($Record->EOF) {
            UpdateOtherTableFieldAfterFormSubmit($FieldsArray['id']);
            $Msg_Reminder_Object_From_Add_Or_Edit_Result = Msg_Reminder_Object_From_Add_Or_Edit($TableName, $FieldsArray['id']);
            $RS['status'] = "OK";
            $RS['msg'] = $SettingMap['Tip_When_Edit_Success'];
            $RS['Msg_Reminder_Object_From_Add_Or_Edit_Result'] = $Msg_Reminder_Object_From_Add_Or_Edit_Result;
            if($SettingMap['Debug_Sql_Show_On_Api']=="Yes")  {
                global $GLOBAL_EXEC_KEY_SQL;
                $RS['sql'] = $sql;  
                $RS['GLOBAL_EXEC_KEY_SQL'] = $GLOBAL_EXEC_KEY_SQL;              
            }
            $RS['sql'] = $sql;  
            $RS['_POST'] = $_POST; 
            $RS['_FILES'] = $_FILES;  
            //Batch_Approval
            $Batch_Approval_Status_Field    = $SettingMap['Batch_Approval_Status_Field'];
            $Batch_Approval_Status_Value    = $SettingMap['Batch_Approval_Status_Value'];
            if($Batch_Approval_Status_Value!="" && $_POST[$Batch_Approval_Status_Field]==$Batch_Approval_Status_Value)  {
                option_multi_approval_exection($FieldsArray['id'], $multiReviewInputValue='', $Reminder=0, $UpdateOtherTableField=0);
            }
            //Batch_Cancel
            $Batch_Cancel_Status_Field    = $SettingMap['Batch_Cancel_Status_Field'];
            $Batch_Cancel_Status_Value    = $SettingMap['Batch_Cancel_Status_Value'];
            if($Batch_Cancel_Status_Value!="" && $_POST[$Batch_Cancel_Status_Field]==$Batch_Cancel_Status_Value)  {
                option_multi_cancel_exection($FieldsArray['id'], $multiReviewInputValue='', $Reminder=0, $UpdateOtherTableField=0);
            }
            //Batch_Refuse
            $Batch_Refuse_Status_Field    = $SettingMap['Batch_Refuse_Status_Field'];
            $Batch_Refuse_Status_Value    = $SettingMap['Batch_Refuse_Status_Value'];
            if($Batch_Refuse_Status_Value!="" && $_POST[$Batch_Refuse_Status_Field]==$Batch_Refuse_Status_Value)  {
                option_multi_refuse_exection($FieldsArray['id'], $multiReviewInputValue='', $Reminder=0, $UpdateOtherTableField=0);
            }            
            //Relative Child Table Support
            $Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
            $Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
            $Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
            if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
                $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
                $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
                $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
                $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
                $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
                if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) &&strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')!==false) {
                    //Get All Fields
                    $readonlyIdArray            = explode(',',ForSqlInjection($_POST['readonlyIdArray']));
                    $db->BeginTrans();
                    $MultiSql                   = [];
                    $sql                        = "delete from $ChildTableName where $Relative_Child_Table_Parent_Field_Name = '".$RecordOriginal->fields[$Relative_Child_Table_Parent_Field_Name]."' and id not in ('".join("','",$readonlyIdArray)."');";
                    $db->Execute($sql);
                    $MultiSql[]                 = $sql;
                    $sql                        = "select * from form_formfield where FormId='$ChildFormId' and IsEnable='1' order by SortNumber asc, id asc";
                    $rs                         = $db->Execute($sql);
                    $ChildAllFieldsFromTable    = $rs->GetArray();
                    $ChildAllFieldsMap          = [];
                    $ChildItemCounter           = $_POST['ChildItemCounter'];
                    for($X=0;$X<$ChildItemCounter;$X++)                    {
                        $ChildElement = [];
                        foreach($ChildAllFieldsFromTable as $Item)  {
                            $ChildFieldName = $Item['FieldName'];
                            switch($Item['ShowType']) {
                                case 'Hidden:Createtime':
                                    $ChildElement[$ChildFieldName] = date('Y-m-d H:i:s');
                                    break;
                                case 'Hidden:CurrentUserIdAdd':
                                case 'Hidden:CurrentUserIdAddEdit':
                                    $ChildElement[$ChildFieldName] = $GLOBAL_USER->USER_ID;
                                    break;
                                default:
                                    $ChildElement[$ChildFieldName] = ForSqlInjection($_POST['ChildTable____'.$X.'____'.$ChildFieldName]);
                                    break;
                            }                            
                        }
                        $deleteChildTableItemArray = explode(',',$_POST['deleteChildTableItemArray']);
                        if(!in_array($X, $deleteChildTableItemArray)) {
                            $ChildElement[$Relative_Child_Table_Parent_Field_Name] = $RecordOriginal->fields[$Relative_Child_Table_Parent_Field_Name];
                            $ChildKeys      = array_keys($ChildElement);
                            $ChildValues    = array_values($ChildElement);
                            $sql            = "insert into $ChildTableName (".join(',',$ChildKeys).") values('".join("','",$ChildValues)."');";
                            $db->Execute($sql);
                            $MultiSql[]     = $sql;
                        }
                    }
                    $db->CommitTrans();
                    $RS['MultiSql'] = $MultiSql;
                }
            }
            
            //functionNameIndividual
            $functionNameIndividual = "plugin_".$TableName."_".$Step."_edit_default_data_after_submit";
            if(function_exists($functionNameIndividual))  {
                $functionNameIndividual($id);
            }
            //SystemLogRecord
            if(in_array($SettingMap['OperationLogGrade'],["EditAndDeleteOperation","AddEditAndDeleteOperation","AllOperation"]))  {
                $sql            = "select * from $TableName where ".$MetaColumnNames[0]." = '$id'";
                $Record         = $db->Execute($sql); 
                SystemLogRecord("edit_default_data", json_encode($RecordOriginal->fields), json_encode($Record->fields));
            }
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
    else {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("No POST Infor");
        $RS['sql'] = $sql;
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        $RS['IsExecutionSQL'] = $IsExecutionSQL;
        $RS['IsExecutionSQLChildTable'] = $IsExecutionSQLChildTable;
        print json_encode($RS);
        exit;
    }
}

if( $_GET['action']=="edit_default_configsetting_data" && $SettingMap['Init_Action_Value']=="edit_default_configsetting" && $_GET['id']!="")  {
    $id = DecryptID($_GET['id']);
    $ConfigSetting = base64_encode(serialize($_POST));
    $sql = "update form_formflow set ConfigSetting='$ConfigSetting' where id='$id'";
    $db->Execute($sql);
    $RS = [];
    $RS['status'] = "OK";
    $RS['msg'] = __("Update Success");
    $RS['sql'] = $sql;
    $RS['_GET'] = $_GET;
    $RS['_POST'] = $_POST;
    print json_encode($RS);
    exit;
}

if( ( ($_GET['action']=="edit_default"&&in_array('Edit',$Actions_In_List_Row_Array))  ) && $_GET['id']!="")  {
    if($TableName=="data_user" && $SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']=="email") {
        $EMAIL  = $GLOBAL_USER->email;
        $id     = returntablefield("data_user","EMAIL",$EMAIL,"id")["id"];
    } 
    else if($TableName=="data_user" && $SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']=="USER_ID") {
        $USER_ID  = $GLOBAL_USER->USER_ID;
        $id     = returntablefield($TableName,"USER_ID",$USER_ID,"id")["id"];
    }   
    else if($SettingMap['Init_Action_Value']=="edit_default" && $SettingMap['Init_Action_FilterValue']!="") {
        $id     = intval($SettingMap['Init_Action_FilterValue']);
    }
    else {
        $id     = intval(DecryptID($_GET['id']));
    }    
    if($id==0)   {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("Error Id Value");
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }

    //functionNameIndividual
    $functionNameIndividual = "plugin_".$TableName."_".$Step."_edit_default";
    if(function_exists($functionNameIndividual))  {
        $functionNameIndividual($id);
    }

    //Get Row Data
    $sql    = "select * from `$TableName` where id = '$id'";
    $rsf    = $db->Execute($sql);
    $data   = $rsf->fields;

    foreach($AllFieldsFromTable as $Item)  {
        $CurrentFieldType = $AllShowTypesArray[$AllFieldsMap[$Item['FieldName']]['ShowType']]['EDIT'];
        switch($CurrentFieldType) {
            case 'avatar':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'avatar',$data[$Item['FieldName']]);
                break;
            case 'files':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'files',$data[$Item['FieldName']]);
                break;
            case 'file':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'file',$data[$Item['FieldName']]);
                break;
            case 'xlsx':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'xlsx',$data[$Item['FieldName']]);
                break;
        }
    }

    $RS = [];
    $RS['status'] = "OK";
    $RS['data'] = $data;
    $RS['sql'] = $sql;
    $RS['msg'] = __("Get Data Success");
    if($_GET['IsGetStructureFromEditDefault']==1)  {
        $edit_default['allFields']      = $allFieldsEdit;
        $edit_default['allFieldsMode']  = [['value'=>"Default", 'label'=>__("")]];
        $edit_default['defaultValues']  = $defaultValuesEdit;
        $edit_default['dialogContentHeight']  = "90%";
        $edit_default['submitaction']   = "edit_default_data";
        $edit_default['submittext']     = __("Submit");
        $edit_default['componentsize']  = "small";
        $edit_default['canceltext']     = "";
        $edit_default['titletext']      = "";
        $edit_default['titlememo']      = "";
        $edit_default['tablewidth']     = 650;
    }
    //Relative Child Table Support
    $Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
    $Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
    $Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
    if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
        $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
        $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
        $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
        $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
        $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
        if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) ) {
            //Get All Fields
            $sql        = "select * from $ChildTableName where $Relative_Child_Table_Parent_Field_Name = '".$data[$Relative_Child_Table_Parent_Field_Name]."';";
            $rs         = $db->Execute($sql);
            $rs_a       = $rs->GetArray();
            $readonlyIdArray            = [];
            $deleteChildTableItemArray  = [];
            $RS['childtable']['sql']    = $sql;
            $RS['childtable']['data']   = $rs_a;
            $RS['childtable']['ChildItemCounter'] = sizeof($rs_a);
            for($X=0;$X<sizeof($rs_a);$X++) {
                $Line = $rs_a[$X];
                foreach($Line AS $LineKey=>$LineValue) {
                    $data['ChildTable____'.$X.'____'.$LineKey] = $LineValue;
                }
                //LimitEditAndDelete
                if($ChildSettingMap['LimitEditAndDelete_Edit_Field_One']!="" && $ChildSettingMap['LimitEditAndDelete_Edit_Field_One']!="None" && in_array($ChildSettingMap['LimitEditAndDelete_Edit_Field_One'], $ChildMetaColumnNames)) {
                    $LimitEditAndDelete_Edit_Value_One_Array = explode(',',$ChildSettingMap['LimitEditAndDelete_Edit_Value_One']);
                    if(in_array($Line[$ChildSettingMap['LimitEditAndDelete_Edit_Field_One']],$LimitEditAndDelete_Edit_Value_One_Array)) {
                        $readonlyIdArray[] = $Line['id'];
                        $deleteChildTableItemArray[] = $X;
                    }
                }
            }
            $RS['childtable']['readonlyIdArray']                = $readonlyIdArray;
            $RS['childtable']['deleteChildTableItemArray']      = $deleteChildTableItemArray;
            $RS['data']  = $data;
        }
    }
    $RS['edit_default'] = $edit_default;
    print json_encode($RS);
    exit;  
}

if( ( ($_GET['action']=="view_default"&&in_array('View',$Actions_In_List_Row_Array))  ) && $_GET['id']!="")  {
    $id     = intval(DecryptID($_GET['id']));    
    if($id==0)   {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("Error Id Value");
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }

    //functionNameIndividual
    $functionNameIndividual = "plugin_".$TableName."_".$Step."_view_default";
    if(function_exists($functionNameIndividual))  {
        $functionNameIndividual($id);
    }

    $sql    = "select * from `$TableName` where id = '$id'";
    $rsf    = $db->Execute($sql);
    $data   = $rsf->fields;

    foreach($AllFieldsFromTable as $Item)  {
        $CurrentFieldType = $AllShowTypesArray[$AllFieldsMap[$Item['FieldName']]['ShowType']]['EDIT'];
        switch($CurrentFieldType) {
            case 'avatar':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'avatar',$data[$Item['FieldName']]);
                break;
            case 'files':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'files',$data[$Item['FieldName']]);
                break;
            case 'file':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'file',$data[$Item['FieldName']]);
                break;
            case 'xlsx':
                $data[$Item['FieldName']] = AttachFieldValueToUrl($TableName,$id,$Item['FieldName'],'xlsx',$data[$Item['FieldName']]);
                break;
        }
    }

    $RS = [];
    $RS['status'] = "OK";
    $RS['data'] = $data;
    $RS['sql'] = $sql;
    $RS['msg'] = __("Get Data Success");
    $view_default = [];
    if($_GET['IsGetStructureFromEditDefault']==1)  {
        $view_default['allFields']      = $allFieldsView;
        $view_default['allFieldsMode']  = [['value'=>"Default", 'label'=>__("")]];
        $view_default['defaultValues']  = $defaultValuesEdit;
        $view_default['dialogContentHeight']  = "90%";
        $view_default['componentsize']  = "small";
        $view_default['canceltext']     = "";
        $view_default['titletext']      = "";
        $view_default['titlememo']      = "";
        $view_default['tablewidth']     = 650;
    }
    $RS['_SERVER'] = $_SERVER;
    $RS['view_default'] = $view_default;

    //Filter Data For View
    foreach($allFieldsView as $ModeName=>$allFieldItem) {
        foreach($allFieldItem as $ITEM) {
            $FieldName              = $ITEM['name'];
            $CurrentFieldTypeArray  = $ITEM['FieldTypeArray'];
            switch($CurrentFieldTypeArray[0])   {
                case 'radiogroup':
                case 'radiogroupcolor':
                case 'tablefilter':
                case 'tablefiltercolor':
                case 'autocomplete':
                    //print_R($CurrentFieldTypeArray);
                    $TableNameTemp      = $CurrentFieldTypeArray[1];
                    $KeyField           = $CurrentFieldTypeArray[2];
                    $ValueField         = $CurrentFieldTypeArray[3];
                    $DefaultValue       = $CurrentFieldTypeArray[4];
                    $WhereField         = ForSqlInjection($CurrentFieldTypeArray[5]);
                    $WhereValue         = ForSqlInjection($CurrentFieldTypeArray[6]);
                    $MetaColumnNamesTemp    = GLOBAL_MetaColumnNames($TableNameTemp);               
                    if($WhereField!="" && $WhereValue!="" && $MetaColumnNamesTemp[$KeyField]!="" && $RS['data'][$FieldName]!="") {
                        $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where $WhereField = '".$WhereValue."' and `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($RS['data'][$FieldName])."' ;";
                        $rs = $db->CacheExecute(10, $sql) or print($sql);
                        $RS['data'][$FieldName] = $rs->fields['label'];
                    }
                    elseif($MetaColumnNamesTemp[$KeyField]!="" && $RS['data'][$FieldName]!="")    {
                        $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($RS['data'][$FieldName])."' ;";
                        $rs = $db->CacheExecute(10, $sql) or print($sql);
                        $RS['data'][$FieldName] = $rs->fields['label'];
                    }    
                    break;
                case 'autocompletemulti':
                    //print_R($CurrentFieldTypeArray);
                    $TableNameTemp      = $CurrentFieldTypeArray[1];
                    $KeyField           = $CurrentFieldTypeArray[2];
                    $ValueField         = $CurrentFieldTypeArray[3];
                    $DefaultValue       = $CurrentFieldTypeArray[4];
                    $WhereField         = ForSqlInjection($CurrentFieldTypeArray[5]);
                    $WhereValue         = ForSqlInjection($CurrentFieldTypeArray[6]);
                    $MetaColumnNamesTemp    = GLOBAL_MetaColumnNames($TableNameTemp);           
                    $MultiValueArray        = explode(',',$RS['data'][$FieldName]);
                    $MultiValueRS           = [];
                    foreach($MultiValueArray as $MultiValue) {
                        if($WhereField!="" && $WhereValue!="" && $MetaColumnNamesTemp[$KeyField]!="" && $MultiValue!="") {
                            $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where $WhereField = '".$WhereValue."' and `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($MultiValue)."' ;";
                            $rs = $db->CacheExecute(10, $sql) or print($sql);
                            $MultiValueRS[] = $rs->fields['label'];
                        }
                        elseif($MetaColumnNamesTemp[$KeyField]!="" && $MultiValue!="")    {
                            $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($MultiValue)."' ;";
                            $rs = $db->CacheExecute(10, $sql) or print($sql);
                            $MultiValueRS[] = $rs->fields['label'];
                        }
                    }
                    $RS['data'][$FieldName] = join(',',$MultiValueRS);
                    break;
                default:
                    break;
            }
        }
    }

    //Convert data to Table
    $ApprovalNodeFieldsArray = explode(',',$SettingMap['ApprovalNodeFields']);
    $ApprovalNodeFieldsHidden = [];
    $ApprovalNodeFieldsStatus = [];
    foreach($ApprovalNodeFieldsArray as $TempField) {
        $ApprovalNodeFieldsHidden[] = $TempField."审核状态";
        //$ApprovalNodeFieldsHidden[] = $TempField."申请时间";
        //$ApprovalNodeFieldsHidden[] = $TempField."申请人";
        $ApprovalNodeFieldsHidden[] = $TempField."审核时间";
        $ApprovalNodeFieldsHidden[] = $TempField."审核人";
        $ApprovalNodeFieldsHidden[] = $TempField."审核意见";
        $ApprovalNodeFieldsStatus[$TempField."审核状态"] = $TempField."审核状态";
    }
    $ApprovalNodeFieldsStatus = array_keys($ApprovalNodeFieldsStatus);
    $NewTableRowData    = [];
    $FieldNameArray     = $allFieldsView['Default'];
    for($X=0;$X<sizeof($FieldNameArray);$X=$X+2)        {
        $FieldName1 = $FieldNameArray[$X]['name'];
        if($FieldNameArray[$X]['type']=="autocomplete" && $FieldNameArray[$X]['code']!="") {
            $FieldName1 = $FieldNameArray[$X]['code'];
        }
        $FieldName2 = $FieldNameArray[$X+1]['name'];
        if($FieldNameArray[$X+1]['type']=="autocomplete" && $FieldNameArray[$X+1]['code']!="") {
            $FieldName2 = $FieldNameArray[$X+1]['code'];
        }
        $RowData = [];
        if(!in_array($FieldName1,$ApprovalNodeFieldsHidden) && $FieldName1!="") {
            $RowData[0]['Name']     = $FieldName1;
            $RowData[0]['Value']    = $RS['data'][$FieldName1];
            $RowData[0]['FieldArray']   = $FieldNameArray[$X];
        }
        if(!in_array($FieldName2,$ApprovalNodeFieldsHidden) && $FieldName2!="") {
            $RowData[1]['Name']     = $FieldName2;
            $RowData[1]['Value']    = $RS['data'][$FieldName2];
            $RowData[1]['FieldArray']   = $FieldNameArray[$X+1];
        }
        if(sizeof($RowData)>0) {
            $NewTableRowData[] = $RowData;
        }
    }
    $RS['newTableRowData']          = $NewTableRowData;
    $RS['ApprovalNodes']['Nodes']   = $ApprovalNodeFieldsArray[0]!=""?$ApprovalNodeFieldsArray:[];
    $RS['ApprovalNodes']['Fields']  = ['审核结点','审核状态','审核时间','审核人','审核意见'];

    $RS['print']['text']            = __("Print");

    //Relative Child Table Support
    $Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
    $Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
    $Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
    if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
        $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
        $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
        $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
        $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
        $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
        if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) ) {
            //Get All Fields
            $sql        = "select * from $ChildTableName where $Relative_Child_Table_Parent_Field_Name = '".$data[$Relative_Child_Table_Parent_Field_Name]."';";
            $rs         = $db->Execute($sql);
            $rs_a       = $rs->GetArray();
            $RS['childtable']['sql']    = $sql;
            $RS['childtable']['data']   = $rs_a;
            $RS['childtable']['ChildItemCounter'] = sizeof($rs_a);

            //Get All Fields
            $sql                        = "select * from form_formfield where FormId='$ChildFormId' and IsEnable='1' order by SortNumber asc, id asc";
            $rs                         = $db->Execute($sql);
            $ChildAllFieldsFromTable    = $rs->GetArray();
            $allFieldsView   = getAllFields($ChildAllFieldsFromTable, $AllShowTypesArray, 'VIEW', true, $ChildSettingMap);
            foreach($allFieldsView as $ModeName=>$allFieldItem) {
                $allFieldItemIndex = 0;
                foreach($allFieldItem as $ITEM) {
                    //if(strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')===false) {
                        //$allFieldsView[$ModeName][$allFieldItemIndex]['rules']['disabled'] = true;
                    //}
                    //$allFieldItemIndex ++;
                }
            }
            $RS['childtable']['allFields']  = $allFieldsView;
            
        }
    }

    if(in_array($SettingMap['MobileEndShowType'],["NewsTemplate1","NotificationTemplate1","NotificationTemplate2"]))           {
        //News Template
        $RS['MobileEnd']['MobileEndNewsTitle']                = $data[$SettingMap['MobileEndNewsTitle']];
        $RS['MobileEnd']['MobileEndNewsGroup']                = $data[$SettingMap['MobileEndNewsGroup']];
        $RS['MobileEnd']['MobileEndNewsContent']              = $data[$SettingMap['MobileEndNewsContent']];
        $RS['MobileEnd']['MobileEndNewsReadCounter']          = $data[$SettingMap['MobileEndNewsReadCounter']];
        $RS['MobileEnd']['MobileEndNewsReadUsers']            = $data[$SettingMap['MobileEndNewsReadUsers']];
        $RS['MobileEnd']['MobileEndNewsCreator']              = returntablefield("data_user","USER_ID",$data[$SettingMap['MobileEndNewsCreator']],"USER_NAME")["USER_NAME"];
        $RS['MobileEnd']['MobileEndNewsCreateTime']           = $data[$SettingMap['MobileEndNewsCreateTime']];
        //if($data[$SettingMap['MobileEndNewsLeftImage']]=="") {
        //    $data[$SettingMap['MobileEndNewsLeftImage']] = "/images/wechat/logo_icampus.png";
        //}
        $RS['MobileEnd']['MobileEndNewsLeftImage']            = $data[$SettingMap['MobileEndNewsLeftImage']];
    }

    print json_encode($RS);
    exit;  
}

if($_GET['action']=="updateone")  {
    $id     = intval(DecryptID($_POST['id']));
    $field  = ParamsFilter($_POST['field']);
    $value  = ParamsFilter($_POST['value']);
    $primary_key = $MetaColumnNames[0];
    //Check Field Valid
    if($id>0&&$field!=""&&in_array($field,$MetaColumnNames)&&$primary_key!=$field&&($SettingMap['FieldEditable_'.$field]=='true' || $SettingMap['FieldEditable_'.$field]=='1')) {
        $sql    = "update $TableName set $field = '$value' where $primary_key = '$id'";
        $db->Execute($sql);
        //functionNameIndividual
        $functionNameIndividual = "plugin_".$TableName."_".$Step."_updateone";
        if(function_exists($functionNameIndividual))  {
            $functionNameIndividual($id);
        }        
        //SystemLogRecord
        if(in_array($SettingMap['OperationLogGrade'],["EditAndDeleteOperation","AddEditAndDeleteOperation","AllOperation"]))  {
            SystemLogRecord("updateone", '', json_encode([$sql]));
        }
        $RS = [];
        $RS['status'] = "OK";
        $RS['msg'] = __("Update Success");
        print json_encode($RS);
        exit;
    }
    else {
        $RS = [];
        $RS['status'] = "ERROR";
        $RS['msg'] = __("Params Error");
        $RS['_GET'] = $_GET;
        $RS['_POST'] = $_POST;
        print json_encode($RS);
        exit;
    }    
}

if($_GET['action']=="delete_array")  {
    $selectedRows  = ForSqlInjection($_POST['selectedRows']);
    $selectedRows = explode(',',$selectedRows);
    $primary_key = $MetaColumnNames[0];
    foreach($selectedRows as $id) {
        $id     = intval(DecryptID($id));
        if($id>0)  {            
            //Check Permission For This Record
            //LimitEditAndDelete
            $sql            = "select * from $TableName where ".$MetaColumnNames[0]." = '$id'";
            $RecordOriginal = $db->Execute($sql);
            if($SettingMap['LimitEditAndDelete_Delete_Field_One']!="" && $SettingMap['LimitEditAndDelete_Delete_Field_One']!="None" && in_array($SettingMap['LimitEditAndDelete_Delete_Field_One'], $MetaColumnNames)) {
                $LimitEditAndDelete_Delete_Value_One_Array = explode(',',$SettingMap['LimitEditAndDelete_Delete_Value_One']);
                if(in_array($RecordOriginal->fields[$SettingMap['LimitEditAndDelete_Delete_Field_One']],$LimitEditAndDelete_Delete_Value_One_Array)) {
                    $RS = [];
                    $RS['status'] = "ERROR";
                    $RS['msg'] = __("Error Id Value");
                    $RS['_GET'] = $_GET;
                    $RS['_POST'] = $_POST;
                    print json_encode($RS);
                    exit;
                }
            }
            if($SettingMap['LimitEditAndDelete_Delete_Field_Two']!="" && $SettingMap['LimitEditAndDelete_Delete_Field_Two']!="None" && in_array($SettingMap['LimitEditAndDelete_Delete_Field_Two'], $MetaColumnNames)) {
                $LimitEditAndDelete_Delete_Value_Two_Array = explode(',',$SettingMap['LimitEditAndDelete_Delete_Value_Two']);
                if(in_array($RecordOriginal->fields[$SettingMap['LimitEditAndDelete_Delete_Field_Two']],$LimitEditAndDelete_Delete_Value_Two_Array)) {
                    $RS = [];
                    $RS['status'] = "ERROR";
                    $RS['msg'] = __("Error Id Value");
                    $RS['_GET'] = $_GET;
                    $RS['_POST'] = $_POST;
                    print json_encode($RS);
                    exit;
                }
            }
            if(in_array($SettingMap['OperationLogGrade'],["DeleteOperation","EditAndDeleteOperation","AddEditAndDeleteOperation","AllOperation"]))  {
                SystemLogRecord("delete_array", '', json_encode($RecordOriginal->fields));
            }

            $db->BeginTrans();
            $MultiSql   = [];
            $sql        = "delete from $TableName where $primary_key = '$id'";
            $db->Execute($sql);
            $MultiSql[] = $sql;
            //Relative Child Table Support
            $Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
            $Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
            $Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
            if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
                $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
                $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
                $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
                $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
                $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
                if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) &&strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')!==false) {
                    //Get All Fields
                    
                    $sql                    = "delete from $ChildTableName where $Relative_Child_Table_Parent_Field_Name = '".$RecordOriginal->fields[$Relative_Child_Table_Parent_Field_Name]."';";
                    $db->Execute($sql);
                    $MultiSql[]             = $sql;
                }
            }
            $db->CommitTrans();

            //functionNameIndividual
            $functionNameIndividual = "plugin_".$TableName."_".$Step."_delete_array";
            if(function_exists($functionNameIndividual))  {
                $functionNameIndividual($id);
            }
        }
    }
    $RS = [];
    $RS['status']   = "OK";
    $RS['MultiSql'] = $MultiSql;
    $RS['msg']      = __("Drop Item Success");
    print json_encode($RS);
    exit;
}

if($_GET['action']=="Reset_Password_123654")  {
    $selectedRows  = ForSqlInjection($_POST['selectedRows']);
    $selectedRows = explode(',',$selectedRows);
    $primary_key = $MetaColumnNames[0];
    foreach($selectedRows as $id) {
        $id     = intval(DecryptID($id));
        if($id>0)  {            
            if(1)  {
                $sql    = "select * from $TableName where $primary_key = '$id'";
                $rs     = $db->Execute($sql);
                SystemLogRecord("Reset_Password_123654", '', json_encode($rs->fields));
            }
            $密码       = password_make("123654");
            if(in_array("密码",$MetaColumnNames)) {
                $sql        = "update $TableName set 密码='$密码' where $primary_key = '$id'";
                $db->Execute($sql);
            }
            if(in_array("PASSWORD",$MetaColumnNames)) {
                $sql        = "update $TableName set `PASSWORD`='$密码' where $primary_key = '$id'";
                $db->Execute($sql);
            }
            //functionNameIndividual
            //$functionNameIndividual = "plugin_".$TableName."_".$Step."_delete_array";
            //if(function_exists($functionNameIndividual))  {
            //    $functionNameIndividual($id);
            //}
        }
    }
    $RS = [];
    $RS['status'] = "OK";
    $RS['sql'] = $sql;
    $RS['msg'] = __("Change Password Success");
    print json_encode($RS);
    exit;
}

if($_GET['action']=="Reset_Password_ID_Last6")  {
    $selectedRows  = ForSqlInjection($_POST['selectedRows']);
    $selectedRows = explode(',',$selectedRows);
    $primary_key = $MetaColumnNames[0];
    foreach($selectedRows as $id) {
        $id     = intval(DecryptID($id));
        if($id>0)  {            
            $sql    = "select * from $TableName where $primary_key = '$id'";
            $rs     = $db->Execute($sql);
            SystemLogRecord("Reset_Password_ID_Last6", '', json_encode($rs->fields));
            $身份证件号 = $rs->fields['身份证件号'];
            if(strlen($身份证件号)>6) {
                $身份证件号6 = substr($身份证件号,-6);
            }
            else {
                $身份证件号6 = "123654";
            }
            $密码       = password_make($身份证件号6);
            if(in_array("密码",$MetaColumnNames)) {
                $sql        = "update $TableName set 密码='$密码' where $primary_key = '$id'";
                $db->Execute($sql);
            }
            if(in_array("PASSWORD",$MetaColumnNames)) {
                $sql        = "update $TableName set `PASSWORD`='$密码' where $primary_key = '$id'";
                $db->Execute($sql);
            }
            //functionNameIndividual
            //$functionNameIndividual = "plugin_".$TableName."_".$Step."_delete_array";
            //if(function_exists($functionNameIndividual))  {
            //    $functionNameIndividual($id);
            //}
        }
    }
    $RS = [];
    $RS['status'] = "OK";
    $RS['sql'] = $sql;
    $RS['msg'] = __("Change Password Success");
    print json_encode($RS);
    exit;
}


//列表页面时的启用字段列表
$init_default_columns   = [];
$columnsactions         = [];
if(in_array('View',$Actions_In_List_Row_Array)) {
    $columnsactions[]   = ['action'=>'view_default','text'=>__('View'),'mdi'=>'mdi:eye-outline'];
}
if(in_array('Edit',$Actions_In_List_Row_Array)) {
    $columnsactions[]   = ['action'=>'edit_default','text'=>$SettingMap['Rename_List_Edit_Button'],'mdi'=>'mdi:pencil-outline'];
}
if(in_array('Delete',$Actions_In_List_Row_Array)) {
    $columnsactions[]   = ['action'=>'delete_array','text'=>$SettingMap['Rename_List_Delete_Button'],'mdi'=>'mdi:delete-outline','double_check'=>__('Do you want to delete this item?')];
}
$init_default_columns[] = ['flex' => 0.1, 'minWidth' => 120, 'sortable' => false, 'field' => "actions", 'headerName' => __("Actions"), 'show'=>true, 'type'=>'actions', 'actions' => $columnsactions];


$ApprovalNodeFieldsArray        = explode(',',$SettingMap['ApprovalNodeFields']);
$ApprovalNodeFieldsArrayFlip    = array_flip($ApprovalNodeFieldsArray);
if($SettingMap['ApprovalNodeTitle']!="")   {
    $RS['init_default']['ApprovalNodeFields']['AllNodes']       = $ApprovalNodeFieldsArray;
    $RS['init_default']['ApprovalNodeFields']['CurrentNode']    = $SettingMap['ApprovalNodeCurrentField'];
    $RS['init_default']['ApprovalNodeFields']['ActiveStep']     = $ApprovalNodeFieldsArrayFlip[$SettingMap['ApprovalNodeCurrentField']];
    $RS['init_default']['ApprovalNodeFields']['ApprovalNodeTitle']  = $SettingMap['ApprovalNodeTitle'];
}
else    {
    $RS['init_default']['ApprovalNodeFields']['AllNodes']       = [];
    $RS['init_default']['ApprovalNodeFields']['CurrentNode']    = "";
    $RS['init_default']['ApprovalNodeFields']['ActiveStep']     = 0;
    $RS['init_default']['ApprovalNodeFields']['ApprovalNodeTitle']  = "";
}



$ApprovalNodeFieldsArray = explode(',',$SettingMap['ApprovalNodeFields']);
$ApprovalNodeFieldsHidden = [];
$ApprovalNodeFieldsStatus = [];
foreach($ApprovalNodeFieldsArray as $TempField) {
    //$ApprovalNodeFieldsHidden[] = $TempField."审核状态";
    //$ApprovalNodeFieldsHidden[] = $TempField."申请时间";
    //$ApprovalNodeFieldsHidden[] = $TempField."申请人";
    $ApprovalNodeFieldsHidden[] = $TempField."审核时间";
    $ApprovalNodeFieldsHidden[] = $TempField."审核人";
    $ApprovalNodeFieldsHidden[] = $TempField."审核意见";
    $ApprovalNodeFieldsStatus[$TempField."审核状态"] = $TempField."审核状态";
}
$ApprovalNodeFieldsStatus = array_keys($ApprovalNodeFieldsStatus);
$searchField = [];
$groupField = [];
$FieldNameToType = [];
$UpdateFields = [];
foreach($AllFieldsFromTable as $Item)  {
    $FieldName      = $Item['FieldName'];    
    $EnglishName    = $Item['EnglishName'];
    $ShowType       = $Item['ShowType'];
    $IsSearch       = $Item['IsSearch'];
    $IsGroupFilter  = $Item['IsGroupFilter'];
    $ColumnWidth    = intval($Item['ColumnWidth']);
    $IsHiddenGroupFilter = $Item['IsHiddenGroupFilter'];
    $CurrentFieldType = $AllShowTypesArray[$ShowType]['LIST'];
    $CurrentFieldTypeArray = explode(':',$CurrentFieldType);
    $FieldNameToType[$FieldName] = $CurrentFieldType;
    //print $FieldName.":".$ShowType.":".$CurrentFieldType.'<BR>';
    if(in_array($FieldName,$ApprovalNodeFieldsHidden)) {
        continue;
    }

    global $GLOBAL_LANGUAGE;
    switch($GLOBAL_LANGUAGE) {
        case 'zhCN':
            $ShowTextName    = $Item['ChineseName'];
            break;
        case 'enUS':
            $ShowTextName    = $Item['EnglishName'];
            break;
        default:
            $ShowTextName    = $Item['EnglishName'];
            break;
    }
    
    $editable = false;
    if($SettingMap['FieldEditable_'.$FieldName]=='true' || $SettingMap['FieldEditable_'.$FieldName]=='1')   {
        $editable = true;
        $UpdateFields[] = $FieldName;
    }
    
    //Filter Field Type
    $FieldTypeInFlow = $SettingMap['FieldType_'.$FieldName];
    $FieldTypeInFlow_Map = [];
    switch($FieldTypeInFlow)   {
        case 'View_Use_ListAddEdit_NotUse':
        case 'Disable':
        case '':
            $CurrentFieldTypeArray[0] = "Disable";
            break;
    }
    //print $FieldName.":".$FieldTypeInFlow." ".$CurrentFieldTypeArray[0]."\n";

    switch($CurrentFieldTypeArray[0])   {
        case 'Disable':
        case '':
            break;
        case 'tablefilter':
        case 'tablefiltercolor':
        case 'autocomplete':
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'autocompletemulti':
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+200, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'radiogroup':
        case 'radiogroupcolor':
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'avatar':            
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'images':            
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'files':            
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'file':            
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        case 'xlsx':            
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$CurrentFieldTypeArray[0], 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
        default:
            $FieldType = "string";
            if(in_array($FieldName,$ApprovalNodeFieldsStatus))  {
                $FieldType = "approvalnode";
                $ColumnWidth = 265;
                $rowHeight = 45;
            }
            //print_R($FieldName);
            //print_R($ApprovalNodeFieldsStatus);
            $init_default_columns[] = ['flex' => 0.1, 'type'=>$FieldType, 'minWidth' => $ColumnWidth, 'maxWidth' => $ColumnWidth+100, 'field' => $FieldName, 'headerName' => $ShowTextName, 'show'=>true, 'renderCell' => NULL, 'editable'=>$editable];
            break;
    }
    if($IsSearch==1&&($SettingMap['FieldSearch_'.$FieldName]=='true'||$SettingMap['FieldSearch_'.$FieldName]=='1'))   {
        $searchField[] = ['label' => $ShowTextName, 'value' => $FieldName];
    }
    if($SettingMap['FieldGroup_'.$FieldName]=='true'||$SettingMap['FieldGroup_'.$FieldName]=='1')   { //$IsGroupFilter==1&&
        $groupField[] = $FieldName;
    }

}

if($SettingMap['Init_Action_Value']=="") {
    $SettingMap['Init_Action_Value'] = "init_default";
}
$RS['init_action']['action']        = $SettingMap['Init_Action_Value'];
$RS['init_action']['id']            = EncryptID($FlowId); //NOT USE THIS VALUE IN FRONT END

//Search Field
$RS['init_default']['searchFieldArray'] = $searchField;
$RS['init_default']['searchFieldText'] = __("Search Item");
if($_REQUEST['searchFieldName']=="") $_REQUEST['searchFieldName'] = $MetaColumnNames[1];
$RS['init_default']['searchFieldName'] = ForSqlInjection($_REQUEST['searchFieldName']);

$searchFieldName     = ForSqlInjection($_REQUEST['searchFieldName']);
$searchFieldValue    = ForSqlInjection($_REQUEST['searchFieldValue']);
if ($searchFieldName != "" && $searchFieldValue != "" && in_array($searchFieldName, $MetaColumnNames) ) {
    $AddSql .= " and ($searchFieldName like '%" . $searchFieldValue . "%')";
}
$RS['init_default']['searchFieldValue'] = ForSqlInjection($_REQUEST['searchFieldValue']);


//Group Filter
$RS['init_default']['filter'] = [];
foreach($groupField as $FieldName) {
    $sql    = "select $FieldName as name, $FieldName as value, count(*) AS num from $TableName where 1=1 $AdditionalPermissionsSQL group by $FieldName";
    $rs     = $db->CacheExecute(10, $sql) or print $sql;
    $rs_a   = $rs->GetArray();
    $ShowType   = $AllFieldsMap[$FieldName]['ShowType'];
    $FieldType  = $AllShowTypesArray[$ShowType]['LIST'];
    $FieldTypeArray = explode(":",$FieldType);    
    switch($FieldTypeArray[0]) {
        case 'tablefilter':
        case 'tablefiltercolor':
        case 'radiogroup':
        case 'radiogroupcolor':
            $TempTableName      = $FieldTypeArray[1];
            $TempKeyIndex       = $FieldTypeArray[2];
            $TempValueIndex     = $FieldTypeArray[3];
            if($TempKeyIndex!=$TempValueIndex)  {
                $TempColumnNames    = GLOBAL_MetaColumnNames($TempTableName);
                for($i=0;$i<sizeof($rs_a);$i++)  {
                    if($rs_a[$i]['value']!="")   {
                        $rs_a[$i]['name'] = returntablefield($TempTableName,$TempColumnNames[$TempKeyIndex],$rs_a[$i]['value'],$TempColumnNames[$TempValueIndex])[$TempColumnNames[$TempValueIndex]];
                    }
                    else {
                        $rs_a[$i]['name']   = __("NULL");
                        $rs_a[$i]['value']  = "NULL";
                    }
                }     
            }
            break;
    }
    for($i=0;$i<sizeof($rs_a);$i++)  {
        if($rs_a[$i]['value']=="")   {
            $rs_a[$i]['name']   = __("NULL");
            $rs_a[$i]['value']  = "NULL";
        }
    }
    $ALL_NUM = 0;
    foreach($rs_a as $Item) {
        $ALL_NUM += $Item['num'];
    }
    global $GLOBAL_LANGUAGE;
    switch($GLOBAL_LANGUAGE) {
        case 'zhCN':
            $ShowTextName    = $AllFieldsMap[$FieldName]['ChineseName'];
            break;
        case 'enUS':
            $ShowTextName    = $AllFieldsMap[$FieldName]['EnglishName'];
            break;
        default:
            $ShowTextName    = $AllFieldsMap[$FieldName]['EnglishName'];
            break;
    }
    array_unshift($rs_a,['name'=>__('All Data'), 'value'=>'All Data', 'num'=>$ALL_NUM]);
    if($_POST[$FieldName]!="") {
        $selected = ForSqlInjection($_POST[$FieldName]);
    }
    else {        
        $selected = "All Data";
    }
    $RS['init_default']['filter'][] = ['name' => $FieldName, 'text' => $ShowTextName, 'list' => $rs_a, 'selected' => $selected];
    //Sql Filter
    $SqlFilterValue = ForSqlInjection($_REQUEST[$FieldName]);
    if ($SqlFilterValue != "" && $SqlFilterValue != "NULL" && $SqlFilterValue != "All Data") {
        $AddSql .= " and (`$FieldName` = '" . $SqlFilterValue . "')";
    }
    else if ($SqlFilterValue == "NULL") {
        $AddSql .= " and (`$FieldName` = '')";
    }
    else {
        //Get All Data
    }
}

//print "TIME EXCEUTE 8:".(time()-$TIME_BEGIN)."<BR>\n";

$pageNumberArray = $SettingMap['pageNumberArray'];
if($pageNumberArray=="") {
    $pageNumberArray = [10,20,30,40,50,100,200,500];
}
$page       = intval($_REQUEST['page']);
$pageSize   = intval($_REQUEST['pageSize']);
if(!in_array($pageSize,$pageNumberArray))  {
	$pageSize = intval($SettingMap['Page_Number_In_List']);
}
$fromRecord = $page * $pageSize;


//print "TIME EXCEUTE 9:".(time()-$TIME_BEGIN)."<BR>\n";
if($FromInfo['TableName']!="")   {
    $RS['init_default']['searchtitle']  = $FromInfo['ShortName'];
}
else {
    $RS['init_default']['searchtitle']  = "Unknown Form";
}
$RS['init_default']['searchtitle']  = $SettingMap['List_Title_Name'];

$RS['init_default']['primarykey']   = $MetaColumnNames[0];


//print "TIME EXCEUTE 10:".(time()-$TIME_BEGIN)."<BR>\n";

if($_REQUEST['sortColumn']=="")   {    
    //order default
    $order_by_array = [];
    $Default_Order_Method_By_Field_One = $SettingMap['Default_Order_Method_By_Field_One'];
    $Default_Order_Method_By_Desc_One = $SettingMap['Default_Order_Method_By_Desc_One'];
    if(in_array($Default_Order_Method_By_Field_One, $MetaColumnNames))  {
        $order_by_array[] = "".$Default_Order_Method_By_Field_One." ".$Default_Order_Method_By_Desc_One;
    }
    $Default_Order_Method_By_Field_Two = $SettingMap['Default_Order_Method_By_Field_Two'];
    $Default_Order_Method_By_Desc_Two = $SettingMap['Default_Order_Method_By_Desc_Two'];
    if(in_array($Default_Order_Method_By_Field_Two, $MetaColumnNames))  {
        $order_by_array[] = "".$Default_Order_Method_By_Field_Two." ".$Default_Order_Method_By_Desc_Two;
    }
    $Default_Order_Method_By_Field_Three = $SettingMap['Default_Order_Method_By_Field_Three'];
    $Default_Order_Method_By_Desc_Three = $SettingMap['Default_Order_Method_By_Desc_Three'];
    if(in_array($Default_Order_Method_By_Field_Three, $MetaColumnNames))  {
        $order_by_array[] = "".$Default_Order_Method_By_Field_Three." ".$Default_Order_Method_By_Desc_Three;
    }
    if(sizeof($order_by_array)>0) {
        $orderby = "order by ".join(',',$order_by_array)."";
    }
}
else {
    if($_REQUEST['sortMethod']=="desc"&&in_array($_REQUEST['sortColumn'], $MetaColumnNames)) {
        $orderby = "order by `".$_REQUEST['sortColumn']."` desc";
    }
    elseif(in_array($_REQUEST['sortColumn'], $MetaColumnNames)) {
        $orderby = "order by `".$_REQUEST['sortColumn']."` asc";
    }
}

//print "TIME EXCEUTE 11:".(time()-$TIME_BEGIN)."<BR>\n";
//Extra_Priv_Filter_Field
Extra_Priv_Filter_Field_To_SQL();

//functionNameIndividual
$functionNameIndividual = "plugin_".$TableName."_".$Step."_init_default";
if(function_exists($functionNameIndividual))  {
    $functionNameIndividual($id);
}

$ForbiddenSelectRow = [];
$ForbiddenViewRow   = [];
$ForbiddenEditRow   = [];
$ForbiddenDeleteRow = [];
$ForbiddenSelectRowOriginal = [];
$ForbiddenViewRowOriginal   = [];
$ForbiddenEditRowOriginal   = [];
$ForbiddenDeleteRowOriginal = [];

//Get Total Records Number
$sql    = "select count(*) AS NUM from $TableName " . $AddSql . "";
$sqlList[] = $sql;
$rs     = $db->CacheExecute(10, $sql);
$RS['init_default']['total'] = intval($rs->fields['NUM']);

//Get All Data
$sql         = "select * from $TableName " . $AddSql . " $orderby limit $fromRecord,$pageSize";
$sqlList[]   = $sql;
//print $sql;
$NewRSA = [];
$rs     = $db->Execute($sql) or print $sql;
$rs_a   = $rs->GetArray();
$FieldDataColorValue = [];
$GetAllIDList = [];
$MobileEndData = [];
foreach ($rs_a as $Line) {
    $OriginalID         = $Line['id'];
    $GetAllIDList[]     = $Line['id'];
    $Line['id']         = EncryptID($Line['id']);
    $MobileEndItem                                      = [];
    //List Template
    $MobileEndItem['MobileEndFirstLine']                = $SettingMap['MobileEndFirstLine'];
    $MobileEndItem['MobileEndSecondLineLeft']           = $SettingMap['MobileEndSecondLineLeft'];
    $MobileEndItem['MobileEndSecondLineRight']          = $SettingMap['MobileEndSecondLineRight'];
    //News Template
    $MobileEndItem['MobileEndNewsTitle']                = $Line[$SettingMap['MobileEndNewsTitle']];
    $MobileEndItem['MobileEndNewsGroup']                = $Line[$SettingMap['MobileEndNewsGroup']];
    $MobileEndItem['MobileEndNewsContent']              = $Line[$SettingMap['MobileEndNewsContent']];
    $MobileEndItem['MobileEndNewsReadCounter']          = $Line[$SettingMap['MobileEndNewsReadCounter']];
    $MobileEndItem['MobileEndNewsReadUsers']            = $Line[$SettingMap['MobileEndNewsReadUsers']];
    $MobileEndItem['MobileEndNewsCreator']              = returntablefield("data_user","USER_ID",$Line[$SettingMap['MobileEndNewsCreator']],"USER_NAME")["USER_NAME"];;
    $MobileEndItem['MobileEndNewsCreateTime']           = $Line[$SettingMap['MobileEndNewsCreateTime']];
    if($Line[$SettingMap['MobileEndNewsLeftImage']]=="") {
        $Line[$SettingMap['MobileEndNewsLeftImage']] = "/images/wechat/logo_icampus_left.png";
    }
    $MobileEndItem['MobileEndNewsLeftImage']            = $Line[$SettingMap['MobileEndNewsLeftImage']];
    //Notification Template

    foreach($Line as $FieldName=>$FieldValue) {
        if($FieldValue=="1971-01-01" || $FieldValue=="1971-01-01 00:00:00" || $FieldValue=="1971-01")  {
            $Line[$FieldName] = "";
        }
        // filter data to show on the list page -- begin
        $CurrentFieldType = $FieldNameToType[$FieldName];
        $CurrentFieldTypeArray = explode(':',$CurrentFieldType);
        switch($CurrentFieldTypeArray[0])   {
            case 'radiogroup':
            case 'radiogroupcolor':
            case 'tablefilter':
            case 'tablefiltercolor':
            case 'autocomplete':
                $TableNameTemp      = $CurrentFieldTypeArray[1];
                $KeyField           = $CurrentFieldTypeArray[2];
                $ValueField         = $CurrentFieldTypeArray[3];
                $DefaultValue       = $CurrentFieldTypeArray[4];
                $WhereField         = ForSqlInjection($CurrentFieldTypeArray[5]);
                $WhereValue         = ForSqlInjection($CurrentFieldTypeArray[6]);
                $MetaColumnNamesTemp    = GLOBAL_MetaColumnNames($TableNameTemp);               
                if($WhereField!="" && $WhereValue!="" && $MetaColumnNamesTemp[$KeyField]!="" && $Line[$FieldName]!="") {
                    $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where $WhereField = '".$WhereValue."' and `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($Line[$FieldName])."' ;";
                    $rs = $db->CacheExecute(10, $sql) or print($sql);
                    $Line[$FieldName] = $rs->fields['label'];
                    if($Line[$FieldName]=="") $Line[$FieldName] = $WhereValue;
                    $FieldDataColorValue[$FieldName][$Line[$FieldName]] = "#";
                    //print "TIME EXCEUTE 12:".(time()-$TIME_BEGIN)." ".$Line[$FieldName]." $sql <BR>\n";
                }
                elseif($MetaColumnNamesTemp[$KeyField]!="" && $Line[$FieldName]!="")    {
                    $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($Line[$FieldName])."' ;";
                    $rs = $db->CacheExecute(10, $sql) or print($sql);
                    if($rs->fields['label']!="")  {
                        $Line[$FieldName] = $rs->fields['label'];
                    }
                    $FieldDataColorValue[$FieldName][$Line[$FieldName]] = "#";
                    //print "TIME EXCEUTE 13:".(time()-$TIME_BEGIN)." ".$Line[$FieldName]." $sql <BR>\n";
                }    
                break;
            case 'autocompletemulti':
                $TableNameTemp      = $CurrentFieldTypeArray[1];
                $KeyField           = $CurrentFieldTypeArray[2];
                $ValueField         = $CurrentFieldTypeArray[3];
                $DefaultValue       = $CurrentFieldTypeArray[4];
                $WhereField         = ForSqlInjection($CurrentFieldTypeArray[5]);
                $WhereValue         = ForSqlInjection($CurrentFieldTypeArray[6]);
                $MetaColumnNamesTemp    = GLOBAL_MetaColumnNames($TableNameTemp);           
                $MultiValueArray        = explode(',',$Line[$FieldName]);
                $MultiValueRS           = [];
                foreach($MultiValueArray as $MultiValue) {
                    if($WhereField!="" && $WhereValue!="" && $MetaColumnNamesTemp[$KeyField]!="" && $MultiValue!="") {
                        $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where $WhereField = '".$WhereValue."' and `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($MultiValue)."' ;";
                        $rs = $db->CacheExecute(10, $sql) or print($sql);
                        $MultiValueRS[] = $rs->fields['label'];
                    }
                    elseif($MetaColumnNamesTemp[$KeyField]!="" && $MultiValue!="")    {
                        $sql = "select `".$MetaColumnNamesTemp[$ValueField]."` as label from $TableNameTemp where `".$MetaColumnNamesTemp[$KeyField]."`='".ForSqlInjection($MultiValue)."' ;";
                        $rs = $db->CacheExecute(10, $sql) or print($sql);
                        $MultiValueRS[] = $rs->fields['label'];
                    }
                }
                $Line[$FieldName] = join(',',$MultiValueRS);
                $FieldDataColorValue[$FieldName][$Line[$FieldName]] = "#";
                //print "TIME EXCEUTE 13:".(time()-$TIME_BEGIN)."<BR>\n";
                break;
            case 'avatar':
                $Line[$FieldName] = AttachFieldValueToUrl($TableName,$OriginalID,$FieldName,'avatar',$Line[$FieldName]);
                break;
            case 'files':
                $Line[$FieldName] = AttachFieldValueToUrl($TableName,$OriginalID,$FieldName,'files',$Line[$FieldName]);
                break;
            case 'file':
                $Line[$FieldName] = AttachFieldValueToUrl($TableName,$OriginalID,$FieldName,'file',$Line[$FieldName]);
                break;
            case 'xlsx':
                $Line[$FieldName] = AttachFieldValueToUrl($TableName,$OriginalID,$FieldName,'xlsx',$Line[$FieldName]);
                break;
        }
        // filter data to show on the list page -- End
        // Mobile End Data Filter
        // List Template 1
        $MobileEndItem['MobileEndFirstLine']            = str_replace("[".$FieldName."]",$FieldValue,$MobileEndItem['MobileEndFirstLine']);
        $MobileEndItem['MobileEndSecondLineLeft']       = str_replace("[".$FieldName."]",$FieldValue,$MobileEndItem['MobileEndSecondLineLeft']);
        $MobileEndItem['MobileEndSecondLineRight']      = str_replace("[".$FieldName."]",$FieldValue,$MobileEndItem['MobileEndSecondLineRight']);
        $MobileEndItem['MobileEndSecondLineRightColor']  = $SettingMap['MobileEndSecondLineRightColor'];
        if($FieldName == $SettingMap['MobileEndWhenField1'] && $SettingMap['MobileEndWhenFieldIsEqual1'] == $FieldValue) {
            $MobileEndItem['MobileEndSecondLineRightColor'] = $SettingMap['MobileEndWhenFieldShowColor1'];
        }
        if($FieldName == $SettingMap['MobileEndWhenField2'] && $SettingMap['MobileEndWhenFieldIsEqual2'] == $FieldValue) {
            $MobileEndItem['MobileEndSecondLineRightColor'] = $SettingMap['MobileEndWhenFieldShowColor2'];
        }
        $MobileEndItem['MobileEndIconImage']        = "/images/wechatIcon/".$SettingMap['MobileEndIconImage'].".png";
        //print_R($SettingMap);exit;
    }
    
    //LimitEditAndDelete
    if($SettingMap['LimitEditAndDelete_Edit_Field_One']!="" && $SettingMap['LimitEditAndDelete_Edit_Field_One']!="None" && in_array($SettingMap['LimitEditAndDelete_Edit_Field_One'], $MetaColumnNames)) {
        $LimitEditAndDelete_Edit_Value_One_Array = explode(',',$SettingMap['LimitEditAndDelete_Edit_Value_One']);
        if(in_array($Line[$SettingMap['LimitEditAndDelete_Edit_Field_One']],$LimitEditAndDelete_Edit_Value_One_Array)) {
            $ForbiddenEditRow[$Line['id']] = $Line['id'];
            $ForbiddenSelectRow[$Line['id']] = $Line['id'];
            $ForbiddenEditRowOriginal[$OriginalID] = $OriginalID;
            $ForbiddenSelectRowOriginal[$OriginalID] = $OriginalID;
        }
    }
    if($SettingMap['LimitEditAndDelete_Edit_Field_Two']!="" && $SettingMap['LimitEditAndDelete_Edit_Field_Two']!="None" && in_array($SettingMap['LimitEditAndDelete_Edit_Field_Two'], $MetaColumnNames)) {
        $LimitEditAndDelete_Edit_Value_Two_Array = explode(',',$SettingMap['LimitEditAndDelete_Edit_Value_Two']);
        if(in_array($Line[$SettingMap['LimitEditAndDelete_Edit_Field_Two']],$LimitEditAndDelete_Edit_Value_Two_Array)) {
            $ForbiddenEditRow[$Line['id']] = $Line['id'];
            $ForbiddenSelectRow[$Line['id']] = $Line['id'];
            $ForbiddenEditRowOriginal[$OriginalID] = $OriginalID;
            $ForbiddenSelectRowOriginal[$OriginalID] = $OriginalID;
        }
    }
    if($SettingMap['LimitEditAndDelete_Delete_Field_One']!="" && $SettingMap['LimitEditAndDelete_Delete_Field_One']!="None" && in_array($SettingMap['LimitEditAndDelete_Delete_Field_One'], $MetaColumnNames)) {
        $LimitEditAndDelete_Delete_Value_One_Array = explode(',',$SettingMap['LimitEditAndDelete_Delete_Value_One']);
        if(in_array($Line[$SettingMap['LimitEditAndDelete_Delete_Field_One']],$LimitEditAndDelete_Delete_Value_One_Array)) {
            $ForbiddenDeleteRow[$Line['id']] = $Line['id'];
            $ForbiddenSelectRow[$Line['id']] = $Line['id'];
            $ForbiddenDeleteRowOriginal[$OriginalID] = $OriginalID;
            $ForbiddenSelectRowOriginal[$OriginalID] = $OriginalID;
        }
    }
    if($SettingMap['LimitEditAndDelete_Delete_Field_Two']!="" && $SettingMap['LimitEditAndDelete_Delete_Field_Two']!="None" && in_array($SettingMap['LimitEditAndDelete_Delete_Field_Two'], $MetaColumnNames)) {
        $LimitEditAndDelete_Delete_Value_Two_Array = explode(',',$SettingMap['LimitEditAndDelete_Delete_Value_Two']);
        if(in_array($Line[$SettingMap['LimitEditAndDelete_Delete_Field_Two']],$LimitEditAndDelete_Delete_Value_Two_Array)) {
            $ForbiddenDeleteRow[$Line['id']] = $Line['id'];
            $ForbiddenSelectRow[$Line['id']] = $Line['id'];
            $ForbiddenDeleteRowOriginal[$OriginalID] = $OriginalID;
            $ForbiddenSelectRowOriginal[$OriginalID] = $OriginalID;
        }
    }
    $NewRSA[] = $Line;
    if(in_array($Line['TableName'],['data_user','data_department','data_role','form_formfield'])) {
        $ForbiddenSelectRow[$Line['id']] = $Line['id'];
        //$ForbiddenViewRow[$Line['id']] = $Line['id'];
        //$ForbiddenEditRow[$Line['id']] = $Line['id'];
        $ForbiddenDeleteRow[$Line['id']] = $Line['id'];
        $ForbiddenDeleteRowOriginal[$OriginalID] = $OriginalID;
        $ForbiddenSelectRowOriginal[$OriginalID] = $OriginalID;
    }
    if($ForbiddenEditRow[$Line['id']]=="" && in_array('Edit',$Actions_In_List_Row_Array)) {
        $MobileEndItem['EditUrl']   = "?action=edit_default&pageid=$page&id=".$Line['id'];
    }
    if($ForbiddenDeleteRow[$Line['id']]=="" && in_array('Delete',$Actions_In_List_Row_Array)) {
        $MobileEndItem['DeleteUrl'] = "?action=delete_array&pageid=$page";
    }    
    if($ForbiddenViewRow[$Line['id']]=="" && in_array('View',$Actions_In_List_Row_Array)) {
        $MobileEndItem['ViewUrl']   = "?action=view_default&pageid=$page&id=".$Line['id'];
    }
    $MobileEndItem['PageId']    = $page;
    $MobileEndItem['Id']        = $Line['id'];
    $MobileEndItem['Template'] = "List";
    $MobileEndData[] = $MobileEndItem;

}

// Add List Page Data Color Array
for($i=0;$i<sizeof($init_default_columns);$i++)    {
    $Item = $init_default_columns[$i];
    if($Item['type']=="radiogroupcolor" && is_array($FieldDataColorValue[$Item['field']]))   {
        $FieldItemAll = @array_keys(@$FieldDataColorValue[$Item['field']]);
        $Color = ArrayToColorStyle1($FieldItemAll);
        $init_default_columns[$i]['color'] = $Color;
        //print_R($init_default_columns[$i]);
    }
    elseif($Item['type']=="tablefiltercolor" && is_array($FieldDataColorValue[$Item['field']]))   {
        $FieldItemAll = @array_keys(@$FieldDataColorValue[$Item['field']]);
        $Color = ArrayToColorStyle2($FieldItemAll);
        $init_default_columns[$i]['color'] = $Color;
        //print_R($init_default_columns[$i]);
    }
}

$RS['init_default']['button_search']    = __("Search");
$RS['init_default']['button_add']       = $SettingMap['Rename_List_Add_Button'];
$RS['init_default']['button_import']    = $SettingMap['Rename_List_Import_Button']?$SettingMap['Rename_List_Import_Button']:__("Import");
$RS['init_default']['button_export']    = $SettingMap['Rename_List_Export_Button']?$SettingMap['Rename_List_Export_Button']:__("Export");
$RS['init_default']['columns']          = $init_default_columns;
$RS['init_default']['columnsactions']   = $columnsactions;

if($SettingMap['OperationLogGrade']=="AllOperation")  {
    SystemLogRecord("init_default", $BeforeRecord='', $AfterRecord='');
}

$RS['init_default']['data']                 = $NewRSA;
$RS['init_default']['MobileEndData']        = $MobileEndData;
$RS['init_default']['MobileEndShowType']    = $SettingMap['MobileEndShowType'];
$RS['init_default']['ForbiddenSelectRow']   = array_keys($ForbiddenSelectRow);
$RS['init_default']['ForbiddenViewRow']     = array_keys($ForbiddenViewRow);
$RS['init_default']['ForbiddenEditRow']     = array_keys($ForbiddenEditRow);
$RS['init_default']['ForbiddenDeleteRow']   = array_keys($ForbiddenDeleteRow);

$CSRF_DATA                          = [];
$CSRF_DATA['GetAllIDList']          = $GetAllIDList;
$CSRF_DATA['ForbiddenSelectRow']    = $ForbiddenSelectRowOriginal;
$CSRF_DATA['ForbiddenViewRow']      = $ForbiddenViewRowOriginal;
$CSRF_DATA['ForbiddenEditRow']      = $ForbiddenEditRowOriginal;
$CSRF_DATA['ForbiddenDeleteRow']    = $ForbiddenDeleteRowOriginal;
$CSRF_DATA['UpdateFields']          = $UpdateFields;
$CSRF_DATA['Actions_In_List_Row_Array'] = $Actions_In_List_Row_Array;
$CSRF_DATA['Bottom_Button_Actions_Array'] = explode(',',$SettingMap['Bottom_Button_Actions']);
$CSRF_DATA['Time']                  = time();
$RS['init_default']['CSRF_TOKEN']   = EncryptID(serialize($CSRF_DATA));
$RS['init_default']['CSRF_DATA']    = $CSRF_DATA;

$RS['init_default']['params']   = ['FormGroup' => '', 'role' => '', 'status' => '', 'q' => ''];

$RS['init_default']['rowdelete']    = [];
$RS['init_default']['rowdelete'][]  = ["text"=>$SettingMap['Tip_Title_When_Delete'],"action"=>"delete_array","title"=>$SettingMap['Tip_Title_When_Delete'],"content"=>$SettingMap['Tip_Content_When_Delete'],"memoname"=>"","inputmust"=>false,"inputmusttip"=>"","submit"=>$SettingMap['Tip_Button_When_Delete'],"cancel"=>__("Cancel")];

//MultiReview
$multireview = [];
$Bottom_Button_Actions_Array = explode(',',$SettingMap['Bottom_Button_Actions']);
$multireview['input']['placeholder'] = __("Review Opinion");
if(in_array('Delete',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Delete Selected"),"action"=>"delete_array","title"=>__("Delete multi items one time"),"content"=>__("Do you really want to delete this item? This operation will delete table and data in Database."),"memoname"=>"","inputmust"=>false,"inputmusttip"=>"","submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
if(in_array('Batch_Approval',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Multi Approval"),"action"=>"option_multi_approval","title"=>__("Approval multi items one time"),"content"=>__("Do you really want to approval multi items at this time?"),"memoname"=>$SettingMap['Batch_Approval_Review_Field'],"inputmust"=>$SettingMap['Batch_Approval_Review_Field']?true:false,"inputmusttip"=>__("Opinion must input"),"submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
if(in_array('Batch_Cancel',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Multi Cancel"),"action"=>"option_multi_cancel","title"=>__("Cancel multi items one time"),"content"=>__("Do you really want to cancel multi items at this time?"),"memoname"=>$SettingMap['Batch_Approval_Review_Field'],"inputmust"=>$SettingMap['Batch_Approval_Review_Field']?true:false,"inputmusttip"=>__("Opinion must input"),"submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
if(in_array('Batch_Reject',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Multi Refuse"),"action"=>"option_multi_refuse","title"=>__("Refuse multi items one time"),"content"=>__("Do you really want to approval multi items at this time?"),"memoname"=>$SettingMap['Batch_Approval_Review_Field'],"inputmust"=>$SettingMap['Batch_Approval_Review_Field']?true:false,"inputmusttip"=>__("Opinion must input"),"submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
if(in_array('Reset_Password_123654',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Reset_Password_123654"),"action"=>"Reset_Password_123654","title"=>__("Modify user passwords in batches"),"content"=>__("Modify the password of the selected record at one time to 123654"),"memoname"=>"","inputmust"=>false,"inputmusttip"=>"","submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
if(in_array('Reset_Password_ID_Last6',$Bottom_Button_Actions_Array))   {
    $multireview['multireview'][] = ["text"=>__("Reset_Password_ID_Last6"),"action"=>"Reset_Password_ID_Last6","title"=>__("Modify user passwords in batches"),"content"=>__("Modify the password of the selected record to the last six digits of the ID number, if no ID number is set, the password is 123654"),"memoname"=>"","inputmust"=>false,"inputmusttip"=>"","submit"=>__("Submit"),"cancel"=>__("Cancel")];
}
//$multireview['multireview'][] = ["text"=>"Multi Change Status","action"=>"option_multi_change_status","title"=>"option_multi_change_status Item","content"=>"Do you really to delete this item?Do you really to delete this item?","memoname"=>"审核意见3","inputmust"=>false,"inputmusttip"=>"","submit"=>"Submit","cancel"=>__("Cancel")];
$RS['init_default']['multireview'] = $multireview;
$RS['init_default']['checkboxSelection']  = is_array($multireview['multireview']) && count($multireview['multireview'])>0 ? true : false;

$RS['import_default']['allFields']        = $allFieldsImport;
$RS['import_default']['allFieldsMode']    = [['value'=>"Default", 'label'=>__("")]];
$RS['import_default']['defaultValues']    = $defaultValuesImport;
$RS['import_default']['dialogContentHeight']  = "90%";
$RS['import_default']['submitaction']     = "import_default_data";
$RS['import_default']['componentsize']    = "small";
$RS['import_default']['submittext']       = $SettingMap['Rename_Import_Submit_Button'];
$RS['import_default']['canceltext']       = __("Cancel");
$RS['import_default']['titletext']        = $SettingMap['Import_Title_Name'];
$RS['import_default']['titlememo']        = $SettingMap['Import_Subtitle_Name'];
$RS['import_default']['tablewidth']       = 650;
$RS['import_default']['submitloading']    = __("SubmitLoading");
$RS['import_default']['loading']          = __("Loading");
$RS['import_default']['ImportLoading']    = __("ImportLoading");


$TEMPARRAY                      = [];
$TEMPARRAY['TableName']         = $TableName;
$TEMPARRAY['Action']            = "export_data";
$TEMPARRAY['FormId']            = $FormId;
$TEMPARRAY['FlowId']            = $FlowId;
$TEMPARRAY['FileName']          = $FormName;
$TEMPARRAY['AddSql']            = $AddSql;
$TEMPARRAY['orderby']           = $orderby;
$TEMPARRAY['Time']              = time();
$DATATEMP                       = EncryptID(serialize($TEMPARRAY));
$exportUrl                      = "data_export.php?DATA=".$DATATEMP;
$RS['export_default']['allFields']        = $allFieldsExport;
$RS['export_default']['allFieldsMode']    = [['value'=>"Default", 'label'=>__("")]];
$RS['export_default']['defaultValues']    = [];
$RS['export_default']['dialogContentHeight']  = "90%";
$RS['export_default']['submitaction']     = "export_default_data";
$RS['export_default']['componentsize']    = "small";
$RS['export_default']['submittext']       = $SettingMap['Rename_Export_Submit_Button'];
$RS['export_default']['canceltext']       = __("Cancel");
$RS['export_default']['titletext']        = $SettingMap['Export_Title_Name'];
$RS['export_default']['titlememo']        = $SettingMap['Export_Subtitle_Name'];
$RS['export_default']['tablewidth']       = 650;
$RS['export_default']['submitloading']    = __("SubmitLoading");
$RS['export_default']['ExportLoading']    = __("ExportLoading");
$RS['export_default']['loading']          = __("Loading");
if(sizeof(array_keys($allFieldsExport))>0 && in_array('Export',$Actions_In_List_Header_Array)) {
    $RS['export_default']['exportUrl']        = $exportUrl;
}

$RS['add_default']['allFields']     = $allFieldsAdd;
$RS['add_default']['allFieldsMode']  = [['value'=>"Default", 'label'=>__("")]];
$RS['add_default']['defaultValues'] = $defaultValuesAdd;
$RS['add_default']['dialogContentHeight']  = "90%";
$RS['add_default']['submitaction']  = "add_default_data";
$RS['add_default']['componentsize'] = "medium";
$RS['add_default']['submittext']    = $SettingMap['Rename_Add_Submit_Button'];
$RS['add_default']['canceltext']    = __("Cancel");
$RS['add_default']['titletext']     = $SettingMap['Add_Title_Name'];
$RS['add_default']['titlememo']     = $SettingMap['Add_Subtitle_Name'];
$RS['add_default']['tablewidth']    = 650;
$RS['add_default']['submitloading'] = __("SubmitLoading");
$RS['add_default']['loading']       = __("Loading");

$RS['edit_default']['allFields']        = $allFieldsEdit;
$RS['edit_default']['allFieldsMode']    = [['value'=>"Default", 'label'=>__("")]];
$RS['edit_default']['defaultValues']    = $defaultValuesEdit;
$RS['edit_default']['dialogContentHeight']  = "90%";
$RS['edit_default']['submitaction']     = "edit_default_data";
$RS['edit_default']['componentsize']    = "small";
$RS['edit_default']['submittext']       = $SettingMap['Rename_Edit_Submit_Button'];
$RS['edit_default']['canceltext']       = __("Cancel");
$RS['edit_default']['titletext']        = $SettingMap['Edit_Title_Name'];
$RS['edit_default']['titlememo']        = $SettingMap['Edit_Subtitle_Name'];
$RS['edit_default']['tablewidth']       = 650;
$RS['edit_default']['submitloading']    = __("SubmitLoading");
$RS['edit_default']['loading']          = __("Loading");

$RS['view_default']               = $RS['add_default'];
$RS['view_default']['allFields']  = $allFieldsView;
$RS['view_default']['titletext']  = $SettingMap['View_Title_Name'];
$RS['view_default']['titlememo']  = $SettingMap['View_Subtitle_Name'];
$RS['view_default']['componentsize'] = "small";

//Relative Child Table Support
$Relative_Child_Table                   = $SettingMap['Relative_Child_Table'];
$Relative_Child_Table_Field_Name        = $SettingMap['Relative_Child_Table_Field_Name'];
$Relative_Child_Table_Parent_Field_Name = $SettingMap['Relative_Child_Table_Parent_Field_Name'];
if($Relative_Child_Table>0 && $Relative_Child_Table_Parent_Field_Name!="" && in_array($Relative_Child_Table_Parent_Field_Name,$MetaColumnNames)) {
    $ChildSettingMap = returntablefield("form_formflow",'id',$Relative_Child_Table,'Setting')['Setting'];
    $ChildSettingMap = unserialize(base64_decode($ChildSettingMap));
    $ChildFormId                = returntablefield("form_formflow",'id',$Relative_Child_Table,'FormId')['FormId'];
    $ChildTableName             = returntablefield("form_formname",'id',$ChildFormId,'TableName')['TableName'];
    $ChildMetaColumnNames       = GLOBAL_MetaColumnNames($ChildTableName); 
    if($Relative_Child_Table_Field_Name!="" && in_array($Relative_Child_Table_Field_Name, $ChildMetaColumnNames) ) {
        //Get All Fields
        $sql                        = "select * from form_formfield where FormId='$ChildFormId' and IsEnable='1' order by SortNumber asc, id asc";
        $rs                         = $db->Execute($sql);
        $ChildAllFieldsFromTable    = $rs->GetArray();
        $ChildAllFieldsMap = [];
        foreach($ChildAllFieldsFromTable as $Item)  {
            $ChildAllFieldsMap[$Item['FieldName']] = $Item;
            $ChildLocaleFieldArray[$Item['EnglishName']] = $Item['FieldName'];
            $ChildLocaleFieldArray[$Item['ChineseName']] = $Item['FieldName'];
        }
        $defaultValuesAddChild  = [];
        $defaultValuesEditChild = [];
        $allFieldsAdd   = getAllFields($ChildAllFieldsFromTable, $AllShowTypesArray, 'ADD', true, $ChildSettingMap);
        foreach($allFieldsAdd as $ModeName=>$allFieldItem) {
            foreach($allFieldItem as $ITEM) {
                $defaultValuesAddChild[$ITEM['name']] = $ITEM['value'];
                if($ITEM['code']!="") {
                    $defaultValuesAddChild[$ITEM['code']] = $ITEM['value'];
                }
            }
        }
        $RS['add_default']['childtable']['allFields']        = $allFieldsAdd;
        $RS['add_default']['childtable']['defaultValues']    = $defaultValuesAddChild;
        $RS['add_default']['childtable']['submittext']       = __("NewItem");
        $RS['add_default']['childtable']['Add']                = strpos($ChildSettingMap['Actions_In_List_Header'],'Add')===false?false:true;
        $RS['add_default']['childtable']['Edit']               = strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')===false?false:true;
        $RS['add_default']['childtable']['Delete']             = strpos($ChildSettingMap['Actions_In_List_Row'],'Delete')===false?false:true;
        
        $allFieldsEdit   = getAllFields($ChildAllFieldsFromTable, $AllShowTypesArray, 'EDIT', true, $ChildSettingMap);
        foreach($allFieldsEdit as $ModeName=>$allFieldItem) {
            $allFieldItemIndex = 0;
            foreach($allFieldItem as $ITEM) {
                $defaultValuesEditChild[$ITEM['name']] = $ITEM['value'];
                if($ITEM['code']!="") {
                    $defaultValuesEditChild[$ITEM['code']] = $ITEM['value'];
                }
                if(strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')===false) {
                    $allFieldsEdit[$ModeName][$allFieldItemIndex]['rules']['disabled'] = true;
                }
                $allFieldItemIndex ++;
            }
        }
        if(is_array($ChildSettingMap))   {
            foreach($ChildSettingMap as $ModeName=>$allFieldItem) {
                $defaultValuesEditChild[$ModeName] = $allFieldItem;
            }
        }
        $RS['edit_default']['childtable']['allFields']          = $allFieldsEdit;
        $RS['edit_default']['childtable']['defaultValues']      = $defaultValuesEditChild;
        $RS['edit_default']['childtable']['submittext']         = __("NewItem");
        $RS['edit_default']['childtable']['Add']                = strpos($ChildSettingMap['Actions_In_List_Header'],'Add')===false?false:true;
        $RS['edit_default']['childtable']['Edit']               = strpos($ChildSettingMap['Actions_In_List_Row'],'Edit')===false?false:true;
        $RS['edit_default']['childtable']['Delete']             = strpos($ChildSettingMap['Actions_In_List_Row'],'Delete')===false?false:true;

    }
}

$RS['init_default']['delete_dialog_title']      = $SettingMap['Tip_Title_When_Delete'];
$RS['init_default']['delete_dialog_content']    = $SettingMap['Tip_Content_When_Delete'];
$RS['init_default']['delete_dialog_button']     = $SettingMap['Tip_Button_When_Delete'];

$RS['init_default']['rowHeight']    = $rowHeight;
$RS['init_default']['dialogContentHeight']  = "90%";
$RS['init_default']['dialogMaxWidth']  = $SettingMap['Init_Action_AddEditWidth']?$SettingMap['Init_Action_AddEditWidth']:'md';// xl lg md sm xs 
$RS['init_default']['timeline']     = time();
$RS['init_default']['pageNumber']   = $pageSize;
$RS['init_default']['pageId']       = $page;
$RS['init_default']['pageNumberArray']  = $pageNumberArray;
if($SettingMap['Debug_Sql_Show_On_Api']=="Yes" || 1)  {
    $RS['init_default']['sql']                              = $sqlList;
    $RS['init_default']['ApprovalNodeFields']['DebugSql']   = $sqlList;
}
$RS['init_default']['ApprovalNodeFields']['Memo']           = $SettingMap['Init_Action_Memo'];


if($SettingMap['Init_Action_Value']=="edit_default_configsetting")   {
    //Get All Fields
    $sql                    = "select * from form_configsetting where FlowId='$FlowId' and IsEnable='1' order by SortNumber asc, id asc";
    $rs                     = $db->Execute($sql);
    $AllFieldsFromTable     = $rs->GetArray();
    $defaultValuesEdit      = [];
    $allFieldsEdit          = getAllFields($AllFieldsFromTable, $AllShowTypesArray, 'EDIT', $FilterFlowSetting=false, $SettingMap);
    foreach($allFieldsEdit as $ModeName=>$allFieldItem) {
        foreach($allFieldItem as $ITEM) {
            $defaultValuesEdit[$ITEM['name']] = $ITEM['value'];
        }
    }
    $ConfigSettingMap = returntablefield("form_formflow",'id',$FlowId,'ConfigSetting')['ConfigSetting'];
    $ConfigSettingMap = unserialize(base64_decode($ConfigSettingMap));
    if(is_array($ConfigSettingMap))   {
        foreach($ConfigSettingMap as $ModeName=>$allFieldItem) {
            $defaultValuesEdit[$ModeName] = $allFieldItem;
        }
    }
    //print_R($AllShowTypesArray);
    //print $sql;
    $RS['edit_default_configsetting']['allFields']        = $allFieldsEdit;
    $RS['edit_default_configsetting']['allFieldsMode']    = [['value'=>"Default", 'label'=>__("")]];
    $RS['edit_default_configsetting']['defaultValues']    = $defaultValuesEdit;
    $RS['edit_default_configsetting']['dialogContentHeight']  = "90%";
    $RS['edit_default_configsetting']['submitaction']     = "edit_default_configsetting_data";
    $RS['edit_default_configsetting']['componentsize']    = "small";
    $RS['edit_default_configsetting']['submittext']       = $SettingMap['Rename_Edit_Submit_Button'];
    $RS['edit_default_configsetting']['canceltext']       = __("Cancel");
    $RS['edit_default_configsetting']['titletext']        = $SettingMap['Edit_Title_Name'];
    $RS['edit_default_configsetting']['titlememo']        = $SettingMap['Edit_Subtitle_Name'];
    $RS['edit_default_configsetting']['tablewidth']       = 650;
    $RS['edit_default_configsetting']['submitloading']    = __("SubmitLoading");
    $RS['edit_default_configsetting']['loading']          = __("Loading");
}


if(sizeof($MetaColumnNames)>=5) {
    $pinnedColumnsLeft = [];
    $pinnedColumnsRight = [];
    if($SettingMap['Columns_Pinned_Left_Field_One']!="" && $SettingMap['Columns_Pinned_Left_Field_One']!="Disabled") {
        $pinnedColumnsLeft[$SettingMap['Columns_Pinned_Left_Field_One']] = $SettingMap['Columns_Pinned_Left_Field_One'];
    }
    if($SettingMap['Columns_Pinned_Left_Field_Two']!="" && $SettingMap['Columns_Pinned_Left_Field_Two']!="Disabled") {
        $pinnedColumnsLeft[$SettingMap['Columns_Pinned_Left_Field_Two']] = $SettingMap['Columns_Pinned_Left_Field_Two'];
    }
    if($SettingMap['Columns_Pinned_Left_Field_Three']!="" && $SettingMap['Columns_Pinned_Left_Field_Three']!="Disabled") {
        $pinnedColumnsLeft[$SettingMap['Columns_Pinned_Left_Field_Three']] = $SettingMap['Columns_Pinned_Left_Field_Three'];
    }
    if($SettingMap['Columns_Pinned_Left_Field_Four']!="" && $SettingMap['Columns_Pinned_Left_Field_Four']!="Disabled") {
        $pinnedColumnsLeft[$SettingMap['Columns_Pinned_Left_Field_Four']] = $SettingMap['Columns_Pinned_Left_Field_Four'];
    }
    if($SettingMap['Columns_Pinned_Right_Field_One']!="" && $SettingMap['Columns_Pinned_Right_Field_One']!="Disabled") {
        $pinnedColumnsRight[$SettingMap['Columns_Pinned_Right_Field_One']] = $SettingMap['Columns_Pinned_Right_Field_One'];
    }
    if($SettingMap['Columns_Pinned_Right_Field_Two']!="" && $SettingMap['Columns_Pinned_Right_Field_Two']!="Disabled") {
        $pinnedColumnsRight[$SettingMap['Columns_Pinned_Right_Field_Two']] = $SettingMap['Columns_Pinned_Right_Field_Two'];
    }
    if($SettingMap['Columns_Pinned_Right_Field_Three']!="" && $SettingMap['Columns_Pinned_Right_Field_Three']!="Disabled") {
        $pinnedColumnsRight[$SettingMap['Columns_Pinned_Right_Field_Three']] = $SettingMap['Columns_Pinned_Right_Field_Three'];
    }
    if($SettingMap['Columns_Pinned_Right_Field_Four']!="" && $SettingMap['Columns_Pinned_Right_Field_Four']!="Disabled") {
        $pinnedColumnsRight[$SettingMap['Columns_Pinned_Right_Field_Four']] = $SettingMap['Columns_Pinned_Right_Field_Four'];
    }
    $pinnedColumnsLeft  = array_keys($pinnedColumnsLeft);
    $pinnedColumnsRight = array_keys($pinnedColumnsRight);
    $pinnedColumns = ['left'=>$pinnedColumnsLeft,'right'=>$pinnedColumnsRight];
}
else {
    $pinnedColumns = ['left'=>[],'right'=>[]];
}
$RS['init_default']['pinnedColumns']  = $pinnedColumns;

$RS['init_default']['dataGridLanguageCode']  = $GLOBAL_LANGUAGE;

//Check Add Action In List Header
if(!in_array('Import',$Actions_In_List_Header_Array))  {
    $RS['import_default'] = [];
}
if(!in_array('Add',$Actions_In_List_Header_Array))  {
    $RS['add_default'] = [];
}
if(!in_array('Edit',$Actions_In_List_Row_Array))  {
    $RS['edit_default'] = [];
}

$RS['_GET']     = $_GET;
$RS['_POST']    = $_POST;
print_R(json_encode($RS, true));



