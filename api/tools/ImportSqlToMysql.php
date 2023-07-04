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

if($_SERVER['SERVER_NAME']=="data.dandian.net") {
    $File = file("../database/database.sql");
    $sqlContent = "";
    foreach($File as $Line) {
        if(substr($Line,0,2)!="--" && substr($Line,0,2)!="/*") {
            $sqlContent .= $Line;
        }
    }
    $sqlContent = str_replace("Windows NT 10.0; Win64;","",$sqlContent);
    $sqlContent = str_replace("#039;","#039；",$sqlContent);

    $queries = explode(';', $sqlContent);
    foreach ($queries as $query) {
        $query = trim($query);
        if($query!="" && $query!="")  {
            $query = str_replace("#039；","#039;",$query);
			$db->Execute($query) or print $query."<HR>";
            //print $query."<HR>";
        }

    }
    print "本次重新初始化数据库完成,下次执行一个小时以后. 当前浏览器页面不要关闭.";
	print "<meta http-equiv=\"refresh\" content=\"3600; url=\">";
}
else {
    print "没有任何任务执行.";
}



?>