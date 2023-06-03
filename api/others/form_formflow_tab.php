<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$FlowID = 11;

$Tab['fieldtype'] = ['group'=>'FormFlow','value'=>'fieldtype','label'=>'Field Type','icon'=>'mdi:database-outline','backEndApi'=>'form_formflow_1.php','action'=>'edit_default','id'=>$FlowID];
$Tab['interfaceparameters'] = ['group'=>'FormFlow','value'=>'interfaceparameters','label'=>'Interface','icon'=>'mdi:settings','backEndApi'=>'form_formflow_2.php','action'=>'edit_default','id'=>$FlowID];
$Tab['bottombutton'] = ['group'=>'FormFlow','value'=>'bottombutton','label'=>'Bottom Button','icon'=>'mdi:border-bottom','backEndApi'=>'form_formflow_3.php','action'=>'edit_default','id'=>$FlowID];
$Tab['additionalpermissions'] = ['group'=>'FormFlow','value'=>'additionalpermissions','label'=>'Additional Permissions','icon'=>'mdi:lock-open-outline','backEndApi'=>'form_formflow_4.php','action'=>'edit_default','id'=>$FlowID];
$Tab['notification'] = ['group'=>'FormFlow','value'=>'notification','label'=>'Notification','icon'=>'mdi:bell-outline','backEndApi'=>'form_formflow_5.php','action'=>'edit_default','id'=>$FlowID];

print json_encode($Tab);
?>