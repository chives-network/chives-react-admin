<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$Tab[] = ['value'=>'account','label'=>'Account','icon'=>'mdi:account-outline'];
$Tab[] = ['value'=>'security','label'=>'Security','icon'=>'mdi:lock-open-outline'];
$Tab[] = ['value'=>'notifications','label'=>'Notifications','icon'=>'mdi:bell-outline'];

print json_encode($Tab);
?>