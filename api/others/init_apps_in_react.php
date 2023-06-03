<?php
header("Content-Type: application/json"); 
require_once('../cors.php');
require_once('../include.inc.php');

for($i=200;$i<=400;$i++)   {
	$Content = '
/*
* Infrastructure: Chives React Admin
* Author: Chives Network
* Email: reactchives@gmail.com
* Copyright (c) 2023
* License: GPL V3 or Commercial license
*/
import UserList from "../Enginee/index"

const AppChat = () => {
    // ** States
    const backEndApi = "apps/apps_'.$i.'.php"
    
  return (
    <UserList backEndApi={backEndApi} externalId=\'\'/>
    )
}

export default AppChat
';
	$FilePath = "../../src/pages/apps/".$i.".tsx";
	$rs = file_put_contents($FilePath,$Content);
}
?>