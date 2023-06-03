<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$profile = '{
    "profileHeader":{
       "fullName":"Jiyun Wang - 2",
       "location":"Vancouver City",
       "joiningDate":"April 2022",
       "designation":"Software Enginee",
       "profileImg":"/images/avatars/1.png",
       "designationIcon":"mdi:invert-colors",
       "coverImg":"/images/pages/profile-banner.png"
    }
 }';

$profile = json_decode($profile, true);
print_R(json_encode($profile['profileHeader']));

?>