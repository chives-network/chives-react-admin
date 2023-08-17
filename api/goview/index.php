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

$param1 = ForSqlInjection($_GET['param1']);
$param2 = ForSqlInjection($_GET['param2']);
$param3 = ForSqlInjection($_GET['param3']);

$BackEndApi = "/api/goview/image";

if($param1=="sys" && $param2=="login")  {

}
else if($param1=="sys" && $param2=="getOssInfo")  {
    $RS = [];
    $RS['msg'] = "操作成功";
    $RS['code'] = 200;
    $RS['data']['bucketURL'] = "/api/goview/bucket/";
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="create")  {
    CheckAuthUserLoginStatus();
    $payload                    = file_get_contents('php://input');
    $_POST                      = json_decode($payload,true);
    $Element                    = [];
    $Element['indexImage']      = ForSqlInjection($_POST['indexImage']);
    $Element['projectName']     = ForSqlInjection($_POST['projectName']);
    $Element['remarks']         = ForSqlInjection($_POST['remarks']);
    $Element['createTime']      = date("Y-m-d H:i:s");
    [$rs,$sql] = InsertOrUpdateTableByArray($TableName="data_goview_project",$Element,"projectName,createTime",0,'Insert');
    if($rs->EOF) {
        $NewId = $db->Insert_ID();
        $sql = "select * from data_goview_project where id='$NewId'";
        $rsf = $db->Execute($sql);
        $RS = [];
        $RS['msg'] = "操作成功";
        $RS['code'] = 200;
        $RS['data'] = $rsf->fields;
        $RS['data']['id'] = EncryptID($RS['data']['id']);
        $RS['_SERVER']  = $_SERVER;
        $RS['_POST']  = $_POST;
        print_R(json_encode($RS));
        exit;
    }
    else {
        $RS = [];
        $RS['msg'] = "创建数据失败";
        $RS['code'] = 219;
        print_R(json_encode($RS));
        exit;
    }
}
else if($param1=="project" && $param2=="getData")  {
    $projectId      = intval(DecryptID($_GET['projectId']));
    $sql    = "select * from data_goview_project where id='$projectId'";
    $rs     = $db->Execute($sql);
    $rs->fields['state'] = intval($rs->fields['state']);
    $RS     = [];
    $RS['msg']      = "操作成功";
    $RS['code']     = 200;
    $RS['sql']      = $sql;
    $RS['_GET']     = $_GET;
    $RS['data']                 = $rs->fields;
    $RS['data']['id']           = EncryptID($RS['data']['id']);
    $RS['data']['content']      = base64_decode($rs->fields['content']);
    $RS['content']              = json_decode(base64_decode($rs->fields['content']),true);
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="delete")  {
    CheckAuthUserLoginStatus();
    $id     = DecryptID($_GET['ids']);
    $sql    = "delete from data_goview_project where id='$id'";
    $rs     = $db->Execute($sql);
    $RS     = [];
    $RS['msg']  = "操作成功";
    $RS['code'] = 200;
    $RS['id']   = $_GET['ids'];
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="copy")  {
    CheckAuthUserLoginStatus();
    $id     = DecryptID($_GET['ids']);
    $sql    = "INSERT INTO data_goview_project (`projectName`, `indexImage`, `remarks`, `isDelete`, `createUserId`, `createTime`, `content`) SELECT `projectName`, `indexImage`, `remarks`, `isDelete`, `createUserId`, `createTime`, `content` FROM data_goview_project WHERE id = '$id'";
    $rs     = $db->Execute($sql);
    $NewId  = $db->Insert_ID();
    global $FileStorageLocation;
    $FileStorageLocation = $FileStorageLocation."/GoView";
    if(is_file($FileStorageLocation."/".$id."_index_preview.png") && $NewId>0)  {
        copy($FileStorageLocation."/".$id."_index_preview.png", $FileStorageLocation."/".$NewId."_index_preview.png");
    }
    $RS     = [];
    $RS['msg']  = "操作成功";
    $RS['code'] = 200;
    $RS['id']   = $_GET['ids'];
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="edit")  {
    CheckAuthUserLoginStatus();
    $payload        = file_get_contents('php://input');
    $_POST          = json_decode($payload,true);
    $id             = intval(DecryptID($_POST['id']));
    $projectName    = ForSqlInjection($_POST['projectName']);
    $sql    = "update data_goview_project set projectName='$projectName' where id='$id'";
    $rs     = $db->Execute($sql);
    $RS     = [];
    $RS['msg'] = "操作成功";
    $RS['code'] = 200;
    $RS['sql'] = $sql;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="list")  {
    $page = intval($_GET['page']);
    if($page<1) $page = 1;
    $limit = intval($_GET['limit']);
    if($limit<6) $limit = 6;
    $from = ($page-1)*$limit;
    $projectId      = intval(DecryptID($_GET['projectId']));
    $sql    = "select id,projectName,state,indexImage,remarks,isDelete,createUserId,createTime from data_goview_project where projectName!='' and isDelete='-1' order by id desc limit ".$from.", ".$limit."";
    $rs     = $db->Execute($sql);
    $rs_a   = $rs->GetArray();
    $Counter = 0;
    foreach($rs_a as $Line) {
        $rs_a[$Counter]['id']           = EncryptID($Line['id']);
        $rs_a[$Counter]['indexImage']   = $BackEndApi."/".$rs_a[$Counter]['id'];
        $rs_a[$Counter]['state']        = intval($Line['state']);
        $rs_a[$Counter]['createUserId']        = intval($Line['createUserId']);
        $Counter ++;
    }
    $RS     = [];
    $RS['msg']      = "操作成功";
    $RS['code']     = 200;
    $RS['count']    = count($rs_a);
    $RS['data']     = $rs_a;
    $RS['sql']      = $sql;
    $RS['_GET']     = $_GET;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="myproject")  {
    CheckAuthUserLoginStatus();
    $page = intval($_GET['page']);
    if($page<1) $page = 1;
    $limit = intval($_GET['limit']);
    if($limit<6) $limit = 6;
    $from = ($page-1)*$limit;
    $projectId      = intval(DecryptID($_GET['projectId']));
    $sql    = "select id,projectName,state,indexImage,remarks,isDelete,createUserId,createTime from data_goview_project where projectName!='' and isDelete='-1' and createUserId='2' order by id desc limit ".$from.", ".$limit."";
    $rs     = $db->Execute($sql);
    $rs_a   = $rs->GetArray();
    $Counter = 0;
    foreach($rs_a as $Line) {
        $rs_a[$Counter]['id']           = EncryptID($Line['id']);
        $rs_a[$Counter]['indexImage']   = $BackEndApi."/".$rs_a[$Counter]['id'];
        $rs_a[$Counter]['state']        = intval($Line['state']);
        $rs_a[$Counter]['createUserId']        = intval($Line['createUserId']);
        $Counter ++;
    }
    $RS     = [];
    $RS['msg']      = "操作成功";
    $RS['code']     = 200;
    $RS['count']    = count($rs_a);
    $RS['data']     = $rs_a;
    $RS['sql']      = $sql;
    $RS['_GET']     = $_GET;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="listtemplate")  {
    $page = intval($_GET['page']);
    if($page<1) $page = 1;
    $limit = intval($_GET['limit']);
    if($limit<6) $limit = 6;
    $from = ($page-1)*$limit;
    $projectId      = intval(DecryptID($_GET['projectId']));
    $sql    = "select id,projectName,state,indexImage,remarks,isDelete,createUserId,createTime from data_goview_project where projectName!='' and isDelete='-1' and `state`='1' order by id desc limit ".$from.", ".$limit."";
    $rs     = $db->Execute($sql);
    $rs_a   = $rs->GetArray();
    $Counter = 0;
    foreach($rs_a as $Line) {
        $rs_a[$Counter]['id']           = EncryptID($Line['id']);
        $rs_a[$Counter]['indexImage']   = $BackEndApi."/".$rs_a[$Counter]['id'];
        $rs_a[$Counter]['state']        = intval($Line['state']);
        $rs_a[$Counter]['createUserId']        = intval($Line['createUserId']);
        $Counter ++;
    }
    $RS     = [];
    $RS['msg']      = "操作成功";
    $RS['code']     = 200;
    $RS['count']    = count($rs_a);
    $RS['data']     = $rs_a;
    $RS['sql']      = $sql;
    $RS['_GET']     = $_GET;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="publish")  {
    CheckAuthUserLoginStatus();
    $payload        = file_get_contents('php://input');
    $_POST          = json_decode($payload,true);
    $id             = intval(DecryptID($_POST['id']));
    $state          = intval($_POST['state']);
    $sql    = "update data_goview_project set state='$state' where id='$id'";
    $rs     = $db->Execute($sql);
    $RS     = [];
    $RS['msg'] = "操作成功";
    $RS['code'] = 200;
    $RS['sql'] = $sql;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="save" && $param3=="data")  {
    CheckAuthUserLoginStatus();
    $id             = intval(DecryptID($_POST['projectId']));
    $content        = base64_encode($_POST['content']);
    $sql    = "update data_goview_project set content='$content' where id='$id'";
    $rs     = $db->Execute($sql);
    if($rs->EOF) {
        $RS     = [];
        $RS['projectId'] = $id;
        $RS['msg'] = "操作成功";
        $RS['code'] = 200;
        $RS['_POST'] = $_POST;
        $RS['content'] = json_decode($_POST['content'],true);
        $RS['sql'] = $sql;
        print_R(json_encode($RS));
        exit;
    }
    else {
        $RS     = [];
        $RS['projectId'] = $id;
        $RS['msg'] = "保存失败";
        $RS['code'] = 200;
        $RS['_POST'] = $_POST;
        $RS['content'] = json_decode($_POST['content'],true);
        $RS['sql'] = $sql;
        print_R(json_encode($RS));
        exit;
    }
}
else if($param1=="image" && $param2!="")  {
    $ID = DecryptID($param2);
    global $FileStorageLocation;
    $FileStorageLocation = $FileStorageLocation."/GoView";
    $FilePath = $FileStorageLocation."/".$ID."_index_preview.png";
    if (!file_exists($FilePath))             {
        $FilePath = $FileStorageLocation."/0_index_preview.png";        
    }
    $imageType = exif_imagetype($FilePath);
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            header('Content-Type: image/jpeg');
            break;
        case IMAGETYPE_PNG:
            header('Content-Type: image/png');
            break;
        case IMAGETYPE_GIF:
            header('Content-Type: image/gif');
            break;
        default:
            header('Content-Type: image/png');
            break;
    }
    readfile($FilePath);
    exit;
}
else if($param1=="bucket" && $param2!="")  {
    $param2 = ForSqlInjection($param2);
    global $FileStorageLocation;
    $FileStorageLocation = $FileStorageLocation."/GoView";
    $FilePath = $FileStorageLocation."/".$param2;
    if (file_exists($FilePath))             {
        $imageType = exif_imagetype($FilePath);
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                header('Content-Type: image/jpeg');
                break;
            case IMAGETYPE_PNG:
                header('Content-Type: image/png');
                break;
            case IMAGETYPE_GIF:
                header('Content-Type: image/gif');
                break;
            default:
                header('Content-Type: image/png');
                break;
        }
        readfile($FilePath);
        exit;   
    }    
    exit;
}
else if($param1=="project" && $param2=="upload")  {
    CheckAuthUserLoginStatus();
    global $FileStorageLocation;
    $FileStorageLocation = $FileStorageLocation."/GoView";
    if(!is_dir($FileStorageLocation)) {
        mkdir($FileStorageLocation);
    }
    if(strpos($_FILES['object']['name'],"_index_preview.png")>0 && isset($_FILES) && isset($_FILES['object']) && is_file($_FILES['object']['tmp_name']) ) {
        $EncryptID  = str_replace("_index_preview.png","",$_FILES['object']['name']);
        $ID         = DecryptID($EncryptID);
        if($ID>0)    {
            $NewFilePath = $FileStorageLocation."/".$ID."_index_preview.png";
            //print $NewFilePath;
            copy($_FILES['object']['tmp_name'], $NewFilePath);
            $Element = [];
            $Element['bucketName'] = NULL;
            $Element['createTime'] = date("Y-m-d H:i:s");
            $Element['createUserId'] = 'admin';
            $Element['createUserName'] = 'admin';
            $Element['fileName']    = $ID."_index_preview.png";
            $Element['fileSize']    = filesize($ID."_index_preview.png");
            $Element['fileSuffix']  = "image/png";
            $Element['id']          = $ID;
            $Element['updateTime'] = NULL;
            $Element['updateUserId'] = NULL;
            $Element['updateUserName'] = NULL;
            print_R(json_encode($Element));
        }
        else {
            $Element = [];
            $Element['code'] = 218;
            $Element['msg'] = "ID值不合法";
            print_R(json_encode($Element));
        }
    }
    else if(strpos($_FILES['object']['name'],"_index_background.png")>0 && isset($_FILES) && isset($_FILES['object']) && is_file($_FILES['object']['tmp_name']) ) {
        $EncryptID  = str_replace("_index_background.png","",$_FILES['object']['name']);
        $ID         = DecryptID($EncryptID);
        if($ID>0)    {
            $NewFilePath = $FileStorageLocation."/".$ID."_index_background.png";
            //print $NewFilePath;
            copy($_FILES['object']['tmp_name'], $NewFilePath);
            $Element = [];
            $Element['bucketName'] = NULL;
            $Element['createTime'] = date("Y-m-d H:i:s");
            $Element['createUserId'] = 'admin';
            $Element['createUserName'] = 'admin';
            $Element['fileName']    = $ID."_index_background.png";
            $Element['fileSize']    = filesize($ID."_index_background.png");
            $Element['fileSuffix']  = "image/png";
            $Element['id']          = $ID;
            $Element['updateTime'] = NULL;
            $Element['updateUserId'] = NULL;
            $Element['updateUserName'] = NULL;
            $RS = [];
            $RS['code'] = 200;
            $RS['data'] = $Element;
            print_R(json_encode($RS));
        }
        else {
            $Element = [];
            $Element['code'] = 218;
            $Element['msg'] = "ID值不合法";
            print_R(json_encode($Element));
        }
    }
    else {
        $Element = [];
        $Element['code']    = 217;
        $Element['_FILES']  = $_FILES;
        $Element['msg']     = "UPLOAD时上传数据不合法";
        print_R(json_encode($Element));
    }
    exit;
}
else if($param1=="project" && $param2=="login")  {

}
else if($param1=="project" && $param2=="login")  {

}

?>