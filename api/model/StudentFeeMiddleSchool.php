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
require_once('StudentFeeMiddleSchool.lib.php');


$StudentFeeMiddleSchool = new StudentFeeMiddleSchool();
$学生信息 = $StudentFeeMiddleSchool->学生信息('411324198307194251');
//print_R($学生信息);

$学生缴费标准 = $StudentFeeMiddleSchool->学生缴费标准($学生信息);
print_R($学生缴费标准);


?>