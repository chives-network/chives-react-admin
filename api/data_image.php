<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

//$externalId = 16;

//CheckAuthUserLoginStatus();
$DATA = DecryptID($_GET['DATA']);
$DATA = unserialize($DATA);

$AttachName = $DATA['FieldName'];
$Type       = $DATA['Type'];
$TableName  = $DATA['TableName'];
$id         = $DATA['Id'];
$FilePath   = "";
$AttachValue = returntablefield($TableName,"id",$id,$AttachName)[$AttachName];
if($AttachValue!="")    {
    $AttachArray = explode("||",$AttachValue);
    $YM          = date('ym',$AttachArray[1]);
    $FileStorageLocation = $FileStorageLocation."/".$YM;
    $FilePath = $FileStorageLocation."/".$AttachArray[1].".".$AttachArray[0];
}
if (!file_exists($FilePath)) {
    $FilePath = "./images/avatars/2.png";
}

$imageType = exif_imagetype($FilePath);
switch ($imageType) {
    case IMAGETYPE_JPEG:
        header('Content-Type: image/jpeg');
        break;
    case IMAGETYPE_PNG:
        header('Content-Type: image/png');
        break;
    case IMAGETYPE_GIF:
        header('Content-Type: image/gif');
        break;
    default:
        header('Content-Type: image/png');
        break;
}
readfile($FilePath);
exit;


/*
if(is_file($FilePath))  {
        $filesize = filesize($FilePath);
		$file = fopen($FilePath,"r");
		header('Pragma: no-cache');
		header("Cache-control: private");
		header("Content-type: image/jpg");
		header("Content-Length: $filesize");
		header("Content-Disposition: attachment; filename=\"".urldecode($AttachArray[1])."\"");
		header("Content-Description: ".$_SERVER['SERVER_NAME']);
		echo fread($file,$filesize);
		fclose($file);
		exit;
    }
     */

?>