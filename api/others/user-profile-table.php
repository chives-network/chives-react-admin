<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

$profile = '[
    {
       "id":1,
       "status":38,
       "leader":"Eileen",
       "name":"Website SEO",
       "date":"10 may 2021",
       "avatarColor":"success",
       "avatarGroup":[
          "/images/avatars/1.png",
          "/images/avatars/2.png",
          "/images/avatars/3.png",
          "/images/avatars/4.png"
       ]
    },
    {
       "id":2,
       "status":45,
       "leader":"Owen",
       "date":"03 Jan 2021",
       "name":"Social Banners",
       "avatar":"/images/icons/project-icons/social-label.png",
       "avatarGroup":[
          "/images/avatars/5.png",
          "/images/avatars/6.png"
       ]
    },
    {
       "id":3,
       "status":92,
       "leader":"Keith",
       "date":"12 Aug 2021",
       "name":"Logo Designs",
       "avatar":"/images/icons/project-icons/sketch-label.png",
       "avatarGroup":[
          "/images/avatars/7.png",
          "/images/avatars/8.png",
          "/images/avatars/1.png",
          "/images/avatars/2.png"
       ]
    },
    {
       "id":4,
       "status":56,
       "leader":"Merline",
       "date":"19 Apr 2021",
       "name":"IOS App Design",
       "avatar":"/images/icons/project-icons/sketch-label.png",
       "avatarGroup":[
          "/images/avatars/3.png",
          "/images/avatars/4.png",
          "/images/avatars/5.png",
          "/images/avatars/6.png"
       ]
    },
    {
       "id":5,
       "status":25,
       "leader":"Harmonia",
       "date":"08 Apr 2021",
       "name":"Figma Dashboards",
       "avatar":"/images/icons/project-icons/figma-label.png",
       "avatarGroup":[
          "/images/avatars/7.png",
          "/images/avatars/8.png",
          "/images/avatars/1.png"
       ]
    },
    {
       "id":6,
       "status":36,
       "leader":"Allyson",
       "date":"29 Sept 2021",
       "name":"Crypto Admin",
       "avatar":"/images/icons/project-icons/html-label.png",
       "avatarGroup":[
          "/images/avatars/2.png",
          "/images/avatars/3.png",
          "/images/avatars/4.png",
          "/images/avatars/5.png"
       ]
    },
    {
       "id":7,
       "status":72,
       "leader":"Georgie",
       "date":"20 Mar 2021",
       "name":"Create Website",
       "avatar":"/images/icons/project-icons/react-label.png",
       "avatarGroup":[
          "/images/avatars/6.png",
          "/images/avatars/7.png",
          "/images/avatars/8.png",
          "/images/avatars/1.png"
       ]
    },
    {
       "id":8,
       "status":89,
       "leader":"Fred",
       "date":"09 Feb 2021",
       "name":"App Design",
       "avatar":"/images/icons/project-icons/xd-label.png",
       "avatarGroup":[
          "/images/avatars/2.png",
          "/images/avatars/3.png",
          "/images/avatars/4.png",
          "/images/avatars/5.png"
       ]
    }
 ]';

$profile = json_decode($profile, true);
print_R(json_encode($profile));

?>