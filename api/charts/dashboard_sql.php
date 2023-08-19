<?php
header("Content-Type: application/json"); 
require_once('../cors.php');
require_once('../include.inc.php');

CheckAuthUserLoginStatus();

$payload    = file_get_contents('php://input');
$_POST      = json_decode($payload, true);

if($_POST['action']=="dbsource") {
    $sql    = "select 连接池名称 as label, id as value from data_datasource where 连接状态='正常' order by id desc";
    $rs     = $db->Execute($sql);
    $rs_a   = $rs->GetArray();
    for($i=0;$i<sizeof($rs_a );$i++) {
        $rs_a[$i]['value'] = EncryptID($rs_a[$i]['value']);
    }
    $RS = [];
    $RS['status']   = "OK";
    $RS['data']     = $rs_a;
    $RS['msg']      = "获取远程数据源成功";
    print json_encode($RS);
    exit; 
}

$id         = DecryptID($_POST['id']);
if($_POST['action']=="db"&&$id>0) {
    $sql            = "select * from data_datasource where id='$id' ";
    $rs             = $db->Execute($sql);
    $远程数据库信息  = $rs->fields;
    if($远程数据库信息['数据库用户名']!="")    {
        $db_remote = NewADOConnection($DB_TYPE='mysqli');
        $db_remote->connect($远程数据库信息['数据库主机'], $远程数据库信息['数据库用户名'], DecryptID($远程数据库信息['数据库密码']), $远程数据库信息['数据库名称']);
        $db_remote->Execute("Set names utf8;");
        if($db_remote->database==$远程数据库信息['数据库名称']) {
            $MetaTables = $db_remote->MetaTables();
            $RS = [];
            $RS['status']   = "OK";
            $RS['sql']      = $sql;
            $RS['data']     = $MetaTables;
            $RS['msg']      = "获取远程数据表列表成功";
            print json_encode($RS);
            exit;
        }
    }
    $RS = [];
    $RS['status']   = "ERROR";
    $RS['db']       = $rs_a;
    $RS['msg']      = "获取远程数据表列表失败";
    print json_encode($RS);
    exit; 
}

$id         = DecryptID($_POST['id']);
$table      = ForSqlInjection($_POST['table']);
if($_POST['action']=="table"&&$id>0&&$table!="") {
    $sql            = "select * from data_datasource where id='$id' ";
    $rs             = $db->Execute($sql);
    $远程数据库信息  = $rs->fields;
    if($远程数据库信息['数据库用户名']!="")    {
        $db_remote = NewADOConnection($DB_TYPE='mysqli');
        $db_remote->connect($远程数据库信息['数据库主机'], $远程数据库信息['数据库用户名'], DecryptID($远程数据库信息['数据库密码']), $远程数据库信息['数据库名称']);
        $db_remote->Execute("Set names utf8;");
        if($db_remote->database==$远程数据库信息['数据库名称']) {
            $MetaColumnNames    = $db_remote->MetaColumnNames($table);
            if(isset($MetaColumnNames)) {
                $MetaColumnNames    = array_values($MetaColumnNames);
                $RS = [];
                $RS['status']   = "OK";
                $RS['data']     = $MetaColumnNames;
                $RS['msg']      = "获取远程数据表结构成功";
                print json_encode($RS);
                exit;
            }
            else {
                exit;
            }
        }
    }
    $RS = [];
    $RS['status']   = "ERROR";
    $RS['db']       = $rs_a;
    $RS['msg']      = "获取远程数据表结构失败";
    print json_encode($RS);
    exit; 
}

?>