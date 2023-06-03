<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('cors.php');
require_once('include.inc.php');


/**
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 */
if($_GET['action']=="login")                {
    JWT::$leeway    = $NEXT_PUBLIC_JWT_EXPIRATION;
    $payload        = file_get_contents('php://input');
    $_POST          = json_decode($payload,true);
    $EMAIL          = ForSqlInjection($_POST['email']);
    $USER_ID        = ForSqlInjection($_POST['username']);
    $password       = ForSqlInjection($_POST['password']);
    $rememberMe     = ForSqlInjection($_POST['rememberMe']);
    if($EMAIL!="")   {
        $sql = "select * from data_user where EMAIL='$EMAIL'";
    }
    else {
        $sql = "select * from data_user where USER_ID='$USER_ID'";
    }
    $rs		= $db->Execute($sql);
	$UserInfo = $rs->fields;
    if($UserInfo['USER_ID']=="")  {  
        $sql    = "select * from data_student where 学号='$USER_ID'";
        $rs		= $db->Execute($sql);
        $StudentInfo = $rs->fields;
        if($StudentInfo['学号']=="")  {  
            $RS = [];
            $RS['status']   = "ERROR";
            $RS['email']    = __("USER NOT EXIST OR PASSWORD IS ERROR!");
            $RS['sql']      = $sql;
            $RS['_POST']    = $_POST;
            print json_encode($RS);
            exit;
        }
        $PASSWORD_IN_DB         = $StudentInfo['密码'];
        if($password!=""&&$PASSWORD_IN_DB!=""&&password_check($password,$PASSWORD_IN_DB))  {
            //Reform userData
            $userData = [];
            $userData['id']         = $StudentInfo['id'];
            $userData['USER_ID']    = $StudentInfo['学号'];
            $userData['USER_NAME']  = $StudentInfo['姓名'];
            $userData['学号']       = $StudentInfo['学号'];
            $userData['姓名']       = $StudentInfo['姓名'];
            $userData['班级']       = $StudentInfo['班级'];
            $userData['专业']       = $StudentInfo['专业'];
            $userData['系部']       = $StudentInfo['系部'];
            $userData['PRIV_NAME']  = "学生";
            $userData['avator']     = '/images/avatars/1.png';        
            $userData['username']   = $StudentInfo['学号'];
            $userData['role']       = "学生";
            $userData['type']       = "Student";
            $accessToken            = JWT::encode($userData, $NEXT_PUBLIC_JWT_SECRET, 'HS256');
            $RS['accessToken']      = $accessToken;
            $RS['userData']         = $userData;
            print_r(json_encode($RS));
            exit;
        }

        $RS = [];
        $RS['status']   = "ERROR";
        $RS['email']    = __("USER NOT EXIST OR PASSWORD IS ERROR!");
        $RS['sql']      = $sql;
        $RS['_POST']    = $_POST;
        print json_encode($RS);
        exit;
    }
    $PASSWORD_IN_DB         = $UserInfo['PASSWORD'];
    if($password!=""&&$PASSWORD_IN_DB!=""&&password_check($password,$PASSWORD_IN_DB))  {
        //Reform userData
        $userData = [];
        $userData['id']         = $UserInfo['id'];
        $userData['USER_ID']    = $UserInfo['USER_ID'];
        $userData['USER_NAME']  = $UserInfo['USER_NAME'];
        $userData['EMAIL']      = $UserInfo['EMAIL'];
        $userData['DEPT_ID']    = $UserInfo['DEPT_ID'];
        $userData['DEPT_NAME']  = returntablefield("data_department","id",$UserInfo['DEPT_ID'],"DEPT_NAME")['DEPT_NAME'];
        $userData['PRIV_NAME']  = returntablefield("data_role","id",$UserInfo['USER_PRIV'],"name")['name'];
        $userData['avator']     = '/images/avatars/1.png';        
        $userData['username']   = $UserInfo['USER_ID'];
        $userData['email']      = $UserInfo['EMAIL'];
        $userData['role']       = $userData['PRIV_NAME'];
        $userData['type']       = "User";
        $accessToken            = JWT::encode($userData, $NEXT_PUBLIC_JWT_SECRET, 'HS256');
        $RS['accessToken']      = $accessToken;
        $RS['userData']         = $userData;
        print_r(json_encode($RS));
        exit;
    }
    else {
        $RS = [];
        $RS['status']   = "ERROR";
        $RS['email']    = __("USER NOT EXIST OR PASSWORD IS ERROR!");
        $RS['sql']      = $sql;
        $RS['_POST']    = $_POST;
        print json_encode($RS);
        exit;
    }
}

if($_GET['action']=="register")                {
    JWT::$leeway    = $NEXT_PUBLIC_JWT_EXPIRATION;
    $accessToken    = JWT::encode([], $NEXT_PUBLIC_JWT_SECRET, 'HS256');
    $decoded        = JWT::decode($accessToken, new Key($NEXT_PUBLIC_JWT_SECRET, 'HS256'));
    $decoded_array  = (array) $decoded;
    print_r($jwt);
    exit;
}

if($_GET['action']=="refresh")                {
    $CheckAuthUserLoginStatus = CheckAuthUserLoginStatus();
    $RS['userData'] = $CheckAuthUserLoginStatus;
    $RS['accessToken'] = $_SERVER['HTTP_AUTHORIZATION'];
    print_r(json_encode($RS));
    exit;  
}

?>