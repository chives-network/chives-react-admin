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

if($param1=="sys" && $param2=="login")  {

}
else if($param1=="sys" && $param2=="getOssInfo")  {
    $RS = [];
    $RS['msg'] = "操作成功";
    $RS['code'] = 200;
    $RS['data']['bucketURL'] = "http://localhost:9999/";
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="create")  {
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
    $RS = [];
    $RS['msg']  = "操作成功";
    $RS['code'] = 200;
    $RS['sql']  = $sql;
    print_R(json_encode($RS));
    exit;
}
else if($param1=="project" && $param2=="edit")  {
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
    print '{"code":200,"msg":"获取成功","count":2,"data":[{"id":"1675716114495315970","projectName":"大数据中心演示图","state":-1,"createTime":"2023-07-02 21:00:29","createUserId":null,"isDelete":null,"indexImage":"http://127.0.0.1:8083/oss/2023-07-04/836345724009582592.png","remarks":null},{"id":"1676366152024260610","projectName":"新项目结构图","state":-1,"createTime":"2023-07-04 16:03:30","createUserId":null,"isDelete":null,"indexImage":"http://127.0.0.1:8083/oss/2023-07-04/836350463531159552.png","remarks":null}]}';
    exit;
}
else if($param1=="project" && $param2=="publish")  {
    print '{"code":200,"data":{"id":"836359243467722753","fileName":"836359243467722752.png","fileSize":3927,"createTime":"2023-07-04 16:59:52","relativePath":"2023-07-04","virtualKey":"oss","fileurl":"http://127.0.0.1:8083/oss/2023-07-04/836359243467722752.png"}}';
    exit;
}
else if($param1=="project" && $param2=="save" && $param3=="data")  {
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
else if($param1=="project" && $param2=="upload")  {

}
else if($param1=="project" && $param2=="login")  {

}
else if($param1=="project" && $param2=="login")  {

}

?>