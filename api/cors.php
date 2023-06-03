<?php
// 设置允许其他域名访问
$allowedOrigins = ['http://localhost:3000','http://react.admin.chives'];
$HTTP_ORIGIN    = $_SERVER['HTTP_ORIGIN'];
if (in_array($HTTP_ORIGIN, $allowedOrigins)) {
    header("Access-Control-Allow-Origin:" . $HTTP_ORIGIN);
}
// 设置允许的响应类型 
header('Access-Control-Allow-Methods:GET, POST');
// 设置允许的响应头 
header("Access-Control-Allow-Headers: Content-Type, Authorization, Csrf_token");
// 设置编码
header("Content-type: text/html; charset=utf-8");

?>