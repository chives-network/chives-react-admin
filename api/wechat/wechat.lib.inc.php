<?php
header("Content-Type: application/json"); 
require_once('../cors.php');
require_once('../include.inc.php');

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);

$_REQUEST['JSON'] = 1; 
global $微信小程序_全局编号;
global $微信小程序_英文标识;
global $HTTP_HOST;


global $掌上校园接口访问状态;
global $SYSTEM_FORCE_TO_BIND_USER_STATUS;
global $SYSTEM_CACHE_SECOND_TDFORMICAMPUS;
$学校ID 	= $_REQUEST['LOGIN_SCHOOL_ID'];

$SYSTEM_CACHE_SECOND_TDFORMICAMPUS = 10;

$sql 		= "select * from data_miniprogram where 是否启用='是' and id='$微信小程序_全局编号'";
$rs 		= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
$rs_a 		= $rs->GetArray();
$小程序名称 = $rs_a[0]['小程序名称'];
$英文标识 	= $rs_a[0]['英文标识'];
$基础构架 	= $rs_a[0]['基础构架'];
$业务表单 	= $rs_a[0]['业务表单'];
$角色列表 	= $rs_a[0]['角色列表'];
$表单列表 	= $基础构架.$业务表单;
$表单列表 	= explode(',',$表单列表);
$缓存周期	= $rs_a[0]['缓存周期'];
$开放对像	= $rs_a[0]['开放对像'];
switch($开放对像)				{
	case "关联用户名或学号以后才能使用":
		$SYSTEM_FORCE_TO_BIND_USER_STATUS = 1;
		break;
	case "等需要时再来关联用户名或学号":
		$SYSTEM_FORCE_TO_BIND_USER_STATUS = 2;
		break;
	case "任何一个模块都不需要关联用户名":
		$SYSTEM_FORCE_TO_BIND_USER_STATUS = 3;
		break;
}
$小程序首页面介绍	= $rs_a[0]['小程序首页面介绍'];
$小程序认证页介绍	= $rs_a[0]['小程序认证页介绍'];
$用户使用协议		= $rs_a[0]['用户使用协议'];
$使用前说明			= $rs_a[0]['使用前说明'];
$服务内容			= $rs_a[0]['服务内容'];


$MainImageList	= array($HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020_banner/xayuhengshi_zhaosheng.png",$HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020_banner/xayuhengshi_campus01.png",$HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020_banner/xayuhengshi_campus02.png",$HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020_banner/xayuhengshi_campus03.png",$HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020_banner/xayuhengshi_campus04.png");//三张滚动截图


if($_GET['action']=='update')							{
	$RESULT = [];
	$RESULT['status']			    = "OK";
    $RESULT['version']			    = date("Ymd_His",time()+$缓存周期);
	$RESULT['学校ID']			    = $学校ID;
	$RESULT['小程序名称']		    = $小程序名称;
	$RESULT['APP_SELF_NAME']	    = $授权方昵称;
	$RESULT['小程序首页面介绍']	    = $小程序首页面介绍;
	$RESULT['小程序认证页介绍']	    = $小程序认证页介绍;
	$RESULT['小程序使用协议']	    = $用户使用协议;
	$RESULT['小程序服务内容']	    = $服务内容;
	$RESULT['小程序使用前说明']	    = $使用前说明;
	$RESULT['小程序LOGO']		    = "/general/EDU/Interface/TDFORMICAMPUS/images/theme/logo.png";	
	print_R(json_encode($RESULT));
    exit;
}

$SYSTEM_IS_CLOUD 	= $_REQUEST['SYSTEM_IS_CLOUD'];
if($_GET['action']=='maindata'&&$SYSTEM_IS_CLOUD==1)															{
	
	//print_R($角色权限);	
	把来自掌上校园的请求转化为本地的SESSION($SCHOOLID);	
	$SYSTEM_APPSTORE_ID = $_REQUEST['SYSTEM_APPSTORE_ID'];
	
	得到当前学校下面所有的菜单信息_云端部署();
	$得到用户所属的表单ID列表X 	= $_SESSION['得到用户所属的表单ID列表X'];
	$得到用户所属的表单ID列表X	= array_keys($得到用户所属的表单ID列表X);
	//print_R($得到用户所属的表单ID列表X);
	
	$菜单分组MAP 			= array();
	$sql 					= "select 名称 from form_formdict where 字典标识 = 'wechat_project_掌上校园分组' order by 排序号 asc";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	$表单IDLIST				= array();
	for($i=0;$i<sizeof($rs_a);$i++)			{
		$名称				= $rs_a[$i]['名称'];
		$菜单分组MAP[]		= $名称;
	}
	$菜单分组MAP 			= array_flip($菜单分组MAP);
	//print_R($菜单分组MAP);
	
	$表单MAP 				= array();
	$sql 					= "select id,英文标识,中文名称 from form_formname where 中文名称 in ('".join("','",$表单列表)."')";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	$表单IDLIST				= array();
	for($i=0;$i<sizeof($rs_a);$i++)			{
		$编号X				= $rs_a[$i]['id'];
		$英文标识X			= $rs_a[$i]['英文标识'];
		$中文名称X			= $rs_a[$i]['中文名称'];
		$表单MAP[$中文名称X]['id'] 		= $编号X;
		$表单MAP[$中文名称X]['英文标识'] 	= $英文标识X;
		$表单MAP[$编号X]['英文标识'] 		= $英文标识X;
		$表单IDLIST[]						= $编号X;
	}
	//形成一个学生菜单菜单的数组
	$形成一个学生菜单菜单的数组 	= array();
	$sql 							= "select * from data_wechat_project where 类型='学生' order by 排序号 asc";
	$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a							= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$访问地址					= $rs_a[$i]['访问地址'];
		$关联其它模块				= $rs_a[$i]['关联其它模块'];
		$形成一个学生菜单菜单的数组[$访问地址] = $关联其它模块;
		$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
	}
	$形成一个学生菜单菜单的数组KEYS = array_keys($形成一个学生菜单菜单的数组);
	//print_R($形成一个学生菜单菜单的数组KEYS);
	
	if($_SESSION['LOGIN_USER_TYPE']=="访客")										{
		$LOGIN_USER_EDUID 	= $_SESSION['LOGIN_USER_EDUID'];
		//形成一个访客菜单菜单的数组
		$访客类型数组 					= array("普通访客");
		//判断当前用户是否是访客管理员
		$单位名称ARRAY		= array();
		$SQL_ARRAY			= array();
		$组织信息			= returntablefield("data_fangke_organization","创建人",$LOGIN_USER_EDUID,"单位名称,学校ID");
		$单位名称	 		= $组织信息['单位名称'];
		$学校ID	 			= $组织信息['学校ID'];
		if($单位名称!="")				{
			$单位名称ARRAY[] = $单位名称;
		}
		$sql			= "select 单位名称 from data_fangke_organization where find_in_set('$LOGIN_USER_EDUID',全局管理人员手机号)";
		$rs	 			= $db->CacheExecute(30,$sql);
		$rs_a 			= $rs->GetArray();
		for($i=0;$i<sizeof($rs_a);$i++)							{
			$单位名称ARRAY[]	= $rs_a[$i]['单位名称'];
		}
		if($单位名称ARRAY[0]!="")								{
			$访客类型数组 				= array("普通访客","访客管理","访客管理员");		
		}
		else								{
			//判断是否是部门审核管理员
			$sql			= "select 部门名称,学校ID from data_yun_department where find_in_set('$LOGIN_USER_EDUID',管理人员手机号)";
			$rs	 			= $db->CacheExecute(30,$sql);
			$rs_a 			= $rs->GetArray();
			$MAP			= array();
			if(sizeof($rs_a)>0)			{
				$访客类型数组			= array("普通访客","访客管理");
			}
		}
		//形成访客菜单.
		$形成一个访客菜单菜单的数组 	= array();
		$sql 							= "select * from data_wechat_project where 类型 in ('".join("','",$访客类型数组)."') order by 排序号 asc";
		$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
		$rs_a							= $rs->GetArray();
		for($i=0;$i<sizeof($rs_a);$i++)												{
			$访问地址					= $rs_a[$i]['访问地址'];
			$关联其它模块				= $rs_a[$i]['关联其它模块'];
			$形成一个访客菜单菜单的数组[$访问地址] = $关联其它模块;
			$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
		}
		$形成一个访客菜单菜单的数组KEYS = array_keys($形成一个访客菜单菜单的数组);
		//print_R($sql);print_R($形成一个访客菜单菜单的数组KEYS);exit;
	}
	
	//输出流程部分菜单
	global $指定表单下面的流程ID,$指定表单下面的报表ID;
	global $权限表;
	$流程ID转表单ID 		= array();
	$指定表单下面的流程ID 	= array();
	$sql 					= "select id,表单ID,流程名称,步骤,参数设置 from form_formflow where 表单ID in ('".join("','",$表单IDLIST)."') order by 表单ID asc,步骤 asc";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$表单IDX				= $rs_a[$i]['表单ID'];
		$编号X					= $rs_a[$i]['id'];
		$步骤X					= $rs_a[$i]['步骤'];
		$参数设置				= unserialize($rs_a[$i]['参数设置']);
		$掌上校园_图标名称 		= $参数设置['掌上校园_图标名称'];
		$掌上校园_整体是否启用 	= $参数设置['掌上校园_整体是否启用'];
		$是否强制每个流程对应一个移动端菜单 	= $参数设置['是否强制每个流程对应一个移动端菜单'];
		$掌上校园_分组名称1 	= $参数设置['掌上校园_分组名称1'];
		$掌上校园_整个模块图标 	= $参数设置['掌上校园_整个模块图标'];
		$额外限制权限 			= $参数设置['额外限制权限'];
		if($额外限制权限=="不设置")			$额外限制权限 	= "全校";
		$掌上校园_把当前菜单加载到其它的掌上校园图标 		= $参数设置['掌上校园_把当前菜单加载到其它的掌上校园图标'];
		//强制关联用户时需要做用户的权限校验 未设置权限时,默认全部权限,设置权限时沿用设置时的值.
		//教职工菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限!="任何人"
			&&$额外限制权限!="学生"
			&&$额外限制权限!="新生"
			&&$额外限制权限!="毕业生"
			&&$额外限制权限!="家长"
			&&($掌上校园_把当前菜单加载到其它的掌上校园图标==""||$掌上校园_把当前菜单加载到其它的掌上校园图标=="不设置")
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="老师"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
			if($是否强制每个流程对应一个移动端菜单=="是")			{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			}
			else		{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX.".php";
			}
		}
		//学生菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="学生"
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="学生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个学生菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				if($形成一个学生菜单菜单的数组[$访问地址]!="")				{
					//学生端的部分,菜单进行叠加,原来的设计学生端直接进入到功能区,没有菜单列表
					$访问地址 														= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_student.php";
				}			
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
			}
		}
		//访客管理员
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="访客"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个访客菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				
				//暂时不启用数量,因为还没有解决,如果高效率的更新图标的值的方法.
				if($表单IDX=="1765"&&$步骤X==3&&1==0)			{
					$记录数量  	= 0;
					require_once("../TDFORMDATA/data_fangke_record_function.php");
					global $SYSTEM_ADD_SQL;
					data_fangke_record_访客管理SQL过滤();
					$sql 		= "select COUNT(*) AS NUM FROM data_fangke_record where 1=1 $SYSTEM_ADD_SQL";
					$rs 		= $db->Execute($sql);
					$记录数量 	= $rs->fields['NUM'];
					//print $SYSTEM_ADD_SQL;
					$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['记录数量']		= (INT)$记录数量;
					$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['记录数量sql']		= $sql;
				}
			}
		}
	}
	
	//输出报表部分菜单
	global $指定表单下面的流程ID,$指定表单下面的报表ID;
	global $权限表;
	$流程ID转表单ID 		= array();
	$指定表单下面的流程ID 	= array();
	$sql 					= "select id,表单ID,报表名称,步骤,参数设置 from form_formreport where 表单ID in ('".join("','",$表单IDLIST)."') order by 表单ID asc,步骤 asc";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$表单IDX				= $rs_a[$i]['表单ID'];
		$编号X					= $rs_a[$i]['id'];
		$步骤X					= $rs_a[$i]['步骤'];
		$参数设置				= unserialize($rs_a[$i]['参数设置']);
		$掌上校园_图标名称 		= $参数设置['掌上校园_图标名称'];
		$掌上校园_整体是否启用 	= $参数设置['掌上校园_整体是否启用'];
		$是否强制每个流程对应一个移动端菜单 	= $参数设置['是否强制每个流程对应一个移动端菜单'];
		$掌上校园_分组名称1 	= $参数设置['掌上校园_分组名称1'];
		$掌上校园_整个模块图标 	= $参数设置['掌上校园_整个模块图标'];
		$额外限制权限 			= $参数设置['额外限制权限'];
		if($额外限制权限=="不设置")			$额外限制权限 	= "全校";
		$掌上校园_把当前菜单加载到其它的掌上校园图标 		= $参数设置['掌上校园_把当前菜单加载到其它的掌上校园图标'];
		//强制关联用户时需要做用户的权限校验 未设置权限时,默认全部权限,设置权限时沿用设置时的值.
		//教职工菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限!="任何人"
			&&$额外限制权限!="学生"
			&&$额外限制权限!="家长"
			&&$额外限制权限!="毕业生"
			&&($掌上校园_把当前菜单加载到其它的掌上校园图标==""||$掌上校园_把当前菜单加载到其它的掌上校园图标=="不设置")
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="老师"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
			if($是否强制每个流程对应一个移动端菜单=="是")			{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= "report_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			}
			else		{
				//沿用流程部分的设置
				//$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX.".php";
			}
		}
		//学生菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="学生"
			&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="学生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "report_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个学生菜单菜单的数组KEYS)&&$是否强制每个流程对应一个移动端菜单=="是")									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
			}
		}
		//访客管理员
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="访客"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 																= "report_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个访客菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
			}
		}
	}
	//print_R($权限表);
	
	$COUNTER 	= 0;
	$分组KEYS 	= array_keys($权限表);
	//把[基础数据]这个值放到最下面-开始
	$分组KEYSNEW= array();
	$通知公告_是否启用 = 0;
	$基础数据_是否启用 = 0;	
	if(in_array("通知公告",$分组KEYS))		{
		$分组KEYSNEW[] 	= "通知公告";
	}
	for($i=0;$i<count($分组KEYS);$i++)			{
		$分组KEY 				= $分组KEYS[$i];
		if($分组KEY!="基础数据"&&$分组KEY!="通知公告")		{
			$分组KEYSNEW[] 		= $分组KEY;
		}
	}
	if(in_array("基础数据",$分组KEYS))		{
		$分组KEYSNEW[] 	= "基础数据";
	}
	$分组KEYS		= $分组KEYSNEW;
	//把[基础数据]这个值放到最下面-结束
	for($i=0;$i<count($分组KEYS);$i++)			{
		$分组KEY 				= $分组KEYS[$i];
		$当前分组下面的菜单 	= $权限表[$分组KEY];
		$当前分组下面的菜单KEYS = array_keys($当前分组下面的菜单);
		for($iR=0;$iR<count($当前分组下面的菜单KEYS);$iR++)			{
			$当前分组下面的菜单KEY 	= $当前分组下面的菜单KEYS[$iR];
			$表单英文标识			= $当前分组下面的菜单[$当前分组下面的菜单KEY]['表单英文标识'];
			$表单ID					= $当前分组下面的菜单[$当前分组下面的菜单KEY]['表单ID'];
			$图标					= $当前分组下面的菜单[$当前分组下面的菜单KEY]['图标'];
			$强制登录				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['强制登录'];
			$访问地址				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['访问地址'];
			$其它信息				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['其它信息'];
			$Element				= array();
			$Element['id']			= $COUNTER;
			$Element['name']		= $当前分组下面的菜单KEY;
			$Element['groupname']	= $分组KEY;
			$Element['url']			= "/general/EDU/Interface/TDFORMICAMPUS/".$访问地址;
			$Element['headerurl']	= $Element['url']."?showHeader=showHeader";
			if(is_file("images2020/".$图标))	{
				$Element['img'] 		= $HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020/".$图标;
			}
			else{
				$Element['img'] 		= $HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images/".$图标;
			}	
			$Element['type']		= "InterfaceInit";//InterfaceNews InterfaceNotify
			$Element['pathurl']		= $Element['type']."_".$COUNTER."_".$COUNTER;
			$Element['itemid']		= $COUNTER;
			$Element['number']		= (INT)$当前分组下面的菜单[$当前分组下面的菜单KEY]['记录数量'];//图标的右上角提示信息
			$Element['formid']		= $表单ID;			
			$Element['flowid']		= $当前分组下面的菜单[$当前分组下面的菜单KEY]['流程步骤'];
			$Element['forcelogin']	= $强制登录;//
			$Element['其它信息']	= $其它信息;//
			$Element['自定义小程序页面']	= $当前分组下面的菜单[$当前分组下面的菜单KEY]['掌上校园_自定义小程序页面'];//
			
			//通知公告的前面两个菜单项目,直接在微信小程序的代码中写死,需要在"校园"图标中显示出来.
			if($分组KEY=="通知公告"&&$iR>=2)				{
				$MainDataList[$分组KEY][]				= $Element;	
			}
			if($分组KEY!="通知公告")				{
				$MainDataList[$分组KEY][]				= $Element;	
			}
			$MainDataMap[]								= $Element;
			$COUNTER ++;
		}
	}
	
	//print_R($MainDataMap);	
	//菜单分组MAP
	
	$MainDataGroup 				= array_keys($MainDataList);;
	$MainDataGroupNew			= array();
	for($i=0;$i<sizeof($MainDataGroup);$i++)		{
		$MainDataGroupIndex		= $菜单分组MAP[$MainDataGroup[$i]];
		$MainDataGroupNew[$MainDataGroupIndex] = $MainDataGroup[$i];
	}
	ksort($MainDataGroupNew);
	$MainDataGroupNew			= array_values($MainDataGroupNew);
	
	$RESULT['MainDataList']		= $MainDataList;						//主要数据区
	$RESULT['MainDataMap']		= $MainDataMap;							//主要数据区
	$RESULT['MainDataGroup']	= $MainDataGroupNew;			//菜单分组
	$RESULT['MainImageList']	= $MainImageList;//三张滚动截图
	$RESULT['IsSearch']			= false;//是否显示搜索,目前需要关闭
	$RESULT['IsUserAvator']		= false;//是否显示用户头像
	$RESULT['用户类型']			= $_SESSION['LOGIN_USER_TYPE'];//是否显示用户头像
	$RESULT['LOGIN_USER_EDUID']	= $_SESSION['LOGIN_USER_EDUID'];//是否显示用户头像
	$RESULT['IndexLogo']		= array();
	$RESULT['SYSTEM_FORCE_TO_BIND_USER_STATUS']	= $SYSTEM_FORCE_TO_BIND_USER_STATUS;
	$RESULT['_REQUEST']			= $_REQUEST;
	$RESULT['_POST']			= $_POST;
	$RESULT['权限表']			= $权限表;
	$RESULT['表单IDLIST']		= $表单IDLIST;
	$RESULT['表单列表']			= $表单列表;
	$RESULT['_SESSION']			= $_SESSION;
	
	$返回数据 = $RESULT;
	$返回数据 = RSA_GBK_TO_UTF8($返回数据,"gbk","utf-8");
	$返回数据 = 掌上校园_输出数据($返回数据);
	print_R($返回数据);
	exit;
}


//私有部署
if($_GET['action']=='maindata'&&$SYSTEM_IS_CLOUD==0)															{
	
	//print_R($角色权限);	
	把来自掌上校园的请求转化为本地的SESSION($SCHOOLID);	
	$SYSTEM_APPSTORE_ID = $_REQUEST['SYSTEM_APPSTORE_ID'];
	$得到用户所属的表单ID列表X = array();
	
	//第一步
	global $systemprivate_rsa;
	$LOGIN_USER_PRIV_OTHER	= $_SESSION['LOGIN_USER_PRIV_OTHER'];
	$LOGIN_USER_PRIV		= $_SESSION['LOGIN_USER_PRIV'];
	$LOGIN_USER_PRIV_STR_ARRAY = explode(',',$LOGIN_USER_PRIV.",".$LOGIN_USER_PRIV_OTHER);
	for($i=0;$i<sizeof($LOGIN_USER_PRIV_STR_ARRAY);$i++)		{
		//$LOGIN_USER_PRIV_TEXT_ARRAY[] = returntablefield("td_edu.systemprivate","ID",$LOGIN_USER_PRIV_STR_ARRAY[$i],"CONTENT");
		$LOGIN_USER_PRIV_TEXT_ARRAY[] = $systemprivate_rsa[$LOGIN_USER_PRIV_STR_ARRAY[$i]]['CONTENT'];
	}
	$LOGIN_USER_PRIV_TEXT  = join(',',$LOGIN_USER_PRIV_TEXT_ARRAY);
	$LOGIN_USER_PRIV_TEXT .= "...";	
	$_SESSION['LOGIN_USER_PRIV_TEXT'] = $LOGIN_USER_PRIV_TEXT;
	
	//第二步
	$sql	= "SELECT 业务表单,小程序名称 FROM data_miniprogram WHERE id='$SYSTEM_APPSTORE_ID'";
	$sql_2 	= $sql;
	$rs		= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a 	= $rs->GetArray();
	$基础设置 = $业务表单 = "";
	for($R=0;$R<sizeof($rs_a);$R++)				{
		$小程序名称	 					= $rs_a[$R]['小程序名称'];
		$业务表单MAP[$小程序名称]	 	= $rs_a[$R]['业务表单'];
		$业务表单						= $rs_a[$R]['业务表单'];
		$业务表单TEMP 					= explode(',',$rs_a[$R]['业务表单']);
		for($RR=0;$RR<sizeof($业务表单TEMP);$RR++)				{
			if($业务表单TEMP[$RR]!="")		{
				$业务表单TEMPARRAY[$业务表单TEMP[$RR]] = $小程序名称;
			}
		}
	}
	$业务表单ARRAY 	= explode(',',$业务表单);
	$所有表单ARRAY 	= explode(',',$业务表单);
	
	//print_R($业务表单ARRAY);exit;
	$sql 			= "select id,中文名称,英文标识 from form_formname where 中文名称 in ('".join("','",$所有表单ARRAY)."')";
	$rs				= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a 			= $rs->GetArray();
	$表单编号ARRAY 	= array();	
	for($R=0;$R<sizeof($rs_a);$R++)						{
		$表单编号	 						= $rs_a[$R]['id'];
		$英文标识	 						= $rs_a[$R]['英文标识'];
		$中文名称	 						= $rs_a[$R]['中文名称'];
		$表单编号ARRAY[] 					= $表单编号;
		$表单MAP[$表单编号] 				= $rs_a[$R];		
		//$表单MAP[$中文名称X]['id'] 		= $表单编号;
		//$表单MAP[$中文名称X]['英文标识'] 	= $英文标识X;
		$表单IDLIST[]						= $表单编号;
		if($业务表单TEMPARRAY[$中文名称]!="")		{
			$业务表单TEMPARRAY[$表单编号] = $业务表单TEMPARRAY[$中文名称];
		}
	}
	
	$sql 		= "select id,流程名称,表单ID,步骤,额外限制权限 from form_formflow where 表单ID in ('".join("','",$表单编号ARRAY)."') and 额外限制权限!='任何人' and 额外限制权限!='家长' order by 表单ID asc,步骤 asc";
	$rs			= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a 		= $rs->GetArray();
	//print_R($rs_a);
	$流程MAP 	= array();
	for($R=0;$R<sizeof($rs_a);$R++)				{
		$流程编号	 					= $rs_a[$R]['id'];
		$流程名称	 					= $rs_a[$R]['流程名称'];
		$表单ID	 						= $rs_a[$R]['表单ID'];
		$表单名称						= $表单MAP[$表单ID]['中文名称'];
		$英文标识						= $表单MAP[$表单ID]['英文标识'];
		$分类名称 						= $业务表单TEMPARRAY[$表单ID];
		$步骤	 						= $rs_a[$R]['步骤'];
		$额外限制权限					= $rs_a[$R]['额外限制权限'];
		$rs_a[$R]['URL']				= "data_".$英文标识."_".$表单ID."_".$步骤.".php";
		//权限值在用户的权限表里面.
		//print $表单名称."-".$流程名称."<BR>";
		if(
			( strpos($LOGIN_USER_PRIV_TEXT,"-".$流程名称.",")>0 || ($_SESSION['LOGIN_USER_EDUID']=="admin") )
		)			{
			$所有菜单MAP[$分类名称][$表单名称]['RSA'][]			= $rs_a[$R];
			$所有菜单MAP[$分类名称][$表单名称]['MenuArray'][]	= array("/general/EDU/Interface/TDFORMDATA/".$rs_a[$R]['URL'],$流程名称,$流程名称);;
			$所有菜单MAP[$分类名称][$表单名称]['URL'][]			= $rs_a[$R]['URL'];
			$所有菜单列表[]										= $rs_a[$R]['URL'];
			$得到用户所属的表单ID列表X[$表单ID]					= $表单ID;
			$得到用户所属的表单和流程流程[]						= $表单名称."-".$流程名称;
			$得到用户所属的表单和流程流程[]						= $流程编号;
		}
		//下面变量存储的是所有菜单的值,而不是部分
		$所有菜单二级_按分类[$分类名称][]					= $表单名称."-".$流程名称;
		if($额外限制权限=="不设置")							$额外限制权限 = "全校";
		$所有菜单二级_按角色[$额外限制权限][]				= $表单名称."-".$流程名称;
	}
	//追加两个必要的身份验证信息.
	//print_R($所有菜单MAP);print_R($所有菜单列表);print_R($所有菜单二级_按分类);exit;
	
	//print_R($所有菜单MAP);
	$_SESSION['所有菜单MAP'] 							= $所有菜单MAP;
	$_SESSION['所有菜单二级_按分类'] 					= $所有菜单二级_按分类;
	$_SESSION['所有菜单二级_按角色'] 					= $所有菜单二级_按角色;
	$_SESSION['所有菜单列表'] 							= $所有菜单列表;
	$_SESSION['小程序名称ARRAY'] 						= $小程序名称ARRAY;
	$_SESSION['LOGIN_SCHOOL_ID'] 						= $LOGIN_SCHOOL_ID;
	$_SESSION['sql_1'] 									= $sql_1;
	$_SESSION['sql_2'] 									= $sql_2;
	$_SESSION['当前用户最大权限集合'] 					= $权限列表ARRAY;
	$_SESSION['当前用户已分配角色'] 					= $LOGIN_USER_PRIV;
	$_SESSION['得到用户所属的表单ID列表X'] 				= $得到用户所属的表单ID列表X;
	$_SESSION['得到用户所属的表单和流程流程'] 			= $得到用户所属的表单和流程流程;
	$_SESSION['基础设置MAP'] 							= $基础设置MAP;
	$_SESSION['业务表单ARRAY'] 							= $业务表单ARRAY;
	$_SESSION['所有表单ARRAY'] 							= $所有表单ARRAY;
	$_SESSION['SYSTEM_APPSTORE_ID'] 					= $SYSTEM_APPSTORE_ID;
	
	
	$得到用户所属的表单ID列表X 	= $_SESSION['得到用户所属的表单ID列表X'];
	$得到用户所属的表单ID列表X	= array_keys($得到用户所属的表单ID列表X);
	//print_R($得到用户所属的表单ID列表X);
	
	$形成一个学生菜单菜单的数组 	= array();
	$sql 							= "select * from data_wechat_project order by 排序号 asc";
	$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a							= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$访问地址						= $rs_a[$i]['访问地址'];
		if($rs_a[$i]['排序号']>0)		{
			$菜单地址转排序号[$访问地址]	= $rs_a[$i]['排序号'];
		}
		else{
			$菜单地址转排序号[$访问地址]	= $rs_a[$i]['id'];
		}
	}
	
	//形成一个学生菜单菜单的数组
	$形成一个学生菜单菜单的数组 	= array();
	$sql 							= "select * from data_wechat_project where 类型='学生' order by 排序号 asc";
	$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a							= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$访问地址					= $rs_a[$i]['访问地址'];
		$关联其它模块				= $rs_a[$i]['关联其它模块'];
		$形成一个学生菜单菜单的数组[$访问地址] = $关联其它模块;
		//$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
	}
	$形成一个学生菜单菜单的数组KEYS = array_keys($形成一个学生菜单菜单的数组);
	//print_R($菜单地址转排序号);
	
	//形成一个毕业生菜单菜单的数组
	$形成一个毕业生菜单菜单的数组 	= array();
	$sql 							= "select * from data_wechat_project where 类型='毕业生' order by 排序号 asc";
	$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a							= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$访问地址					= $rs_a[$i]['访问地址'];
		$关联其它模块				= $rs_a[$i]['关联其它模块'];
		$形成一个毕业生菜单菜单的数组[$访问地址] = $关联其它模块;
		//$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
	}
	$形成一个毕业生菜单菜单的数组KEYS = array_keys($形成一个毕业生菜单菜单的数组);
	//print_R($形成一个毕业生菜单菜单的数组KEYS);
	
	//形成一个新生菜单菜单的数组
	$形成一个新生菜单菜单的数组 	= array();
	$sql 							= "select * from data_wechat_project where 类型='新生' order by 排序号 asc";
	$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a							= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$访问地址					= $rs_a[$i]['访问地址'];
		$关联其它模块				= $rs_a[$i]['关联其它模块'];
		$形成一个新生菜单菜单的数组[$访问地址] 	= $关联其它模块;
		//$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
	}
	$形成一个新生菜单菜单的数组KEYS = array_keys($形成一个新生菜单菜单的数组);
	//print_R($菜单地址转排序号);
	
	if($_SESSION['LOGIN_USER_TYPE']=="访客")										{
		$LOGIN_USER_EDUID 	= $_SESSION['LOGIN_USER_EDUID'];
		//形成一个访客菜单菜单的数组
		$访客类型数组 					= array("普通访客");
		//判断当前用户是否是访客管理员
		$单位名称ARRAY		= array();
		$SQL_ARRAY			= array();
		$组织信息			= returntablefield("data_fangke_organization","创建人",$LOGIN_USER_EDUID,"单位名称,学校ID");
		$单位名称	 		= $组织信息['单位名称'];
		$学校ID	 			= $组织信息['学校ID'];
		if($单位名称!="")				{
			$单位名称ARRAY[] = $单位名称;
		}
		$sql			= "select 单位名称 from data_fangke_organization where find_in_set('$LOGIN_USER_EDUID',全局管理人员手机号)";
		$rs	 			= $db->CacheExecute(30,$sql);
		$rs_a 			= $rs->GetArray();
		for($i=0;$i<sizeof($rs_a);$i++)							{
			$单位名称ARRAY[]	= $rs_a[$i]['单位名称'];
		}
		if($单位名称ARRAY[0]!="")								{
			$访客类型数组 				= array("普通访客","访客管理","访客管理员");		
		}
		else								{
			//判断是否是部门审核管理员
			$sql			= "select 部门名称,学校ID from data_yun_department where find_in_set('$LOGIN_USER_EDUID',管理人员手机号)";
			$rs	 			= $db->CacheExecute(30,$sql);
			$rs_a 			= $rs->GetArray();
			$MAP			= array();
			if(sizeof($rs_a)>0)			{
				$访客类型数组			= array("普通访客","访客管理");
			}
		}
		//形成访客菜单.
		$形成一个访客菜单菜单的数组 	= array();
		$sql 							= "select * from data_wechat_project where 类型 in ('".join("','",$访客类型数组)."') order by 排序号 asc";
		$rs								= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
		$rs_a							= $rs->GetArray();
		for($i=0;$i<sizeof($rs_a);$i++)												{
			$访问地址					= $rs_a[$i]['访问地址'];
			$关联其它模块				= $rs_a[$i]['关联其它模块'];
			$形成一个访客菜单菜单的数组[$访问地址] = $关联其它模块;
			//$菜单地址转排序号[$访问地址]			= $rs_a[$i]['排序号'];
		}
		$形成一个访客菜单菜单的数组KEYS = array_keys($形成一个访客菜单菜单的数组);
		//print_R($sql);print_R($形成一个访客菜单菜单的数组KEYS);exit;
	}
	
	//输出流程部分菜单
	global $指定表单下面的流程ID,$指定表单下面的报表ID;
	global $权限表;
	$流程ID转表单ID 		= array();
	$指定表单下面的流程ID 	= array();
	$sql 					= "select id,表单ID,流程名称,步骤,参数设置 from form_formflow where 表单ID in ('".join("','",$表单IDLIST)."') order by 表单ID asc,步骤 asc";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$表单IDX				= $rs_a[$i]['表单ID'];
		$编号X					= $rs_a[$i]['id'];
		$步骤X					= $rs_a[$i]['步骤'];
		$流程名称X				= $rs_a[$i]['流程名称'];
		$参数设置				= unserialize($rs_a[$i]['参数设置']);
		$掌上校园_图标名称 		= $参数设置['掌上校园_图标名称'];
		$掌上校园_整体是否启用 	= $参数设置['掌上校园_整体是否启用'];
		$是否强制每个流程对应一个移动端菜单 	= $参数设置['是否强制每个流程对应一个移动端菜单'];
		$掌上校园_分组名称1 	= $参数设置['掌上校园_分组名称1'];
		$掌上校园_整个模块图标 	= $参数设置['掌上校园_整个模块图标'];
		$额外限制权限 			= $参数设置['额外限制权限'];
		if($额外限制权限=="不设置")			$额外限制权限 	= "全校";
		$掌上校园_把当前菜单加载到其它的掌上校园图标 		= $参数设置['掌上校园_把当前菜单加载到其它的掌上校园图标'];
		//强制关联用户时需要做用户的权限校验 未设置权限时,默认全部权限,设置权限时沿用设置时的值.
		//教职工菜单输出.
		if(strpos($LOGIN_USER_PRIV_TEXT,"-".$流程名称X.",")<=0)			{
			//记录异常的权限部分
			$_SESSION['记录异常的权限部分'][$流程名称X] = "掌上校园_整体是否启用:".$掌上校园_整体是否启用." 掌上校园_图标名称:".$掌上校园_图标名称." 额外限制权限:".$额外限制权限." 掌上校园_把当前菜单加载到其它的掌上校园图标:".$掌上校园_把当前菜单加载到其它的掌上校园图标." LOGIN_USER_TYPE:".$_SESSION['LOGIN_USER_TYPE'];
		}
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限!="任何人"
			&&$额外限制权限!="学生"
			&&$额外限制权限!="家长"
			&&$额外限制权限!="毕业生"
			&&($掌上校园_把当前菜单加载到其它的掌上校园图标==""||$掌上校园_把当前菜单加载到其它的掌上校园图标=="不设置")
			&&( strpos($LOGIN_USER_PRIV_TEXT,"-".$流程名称X.",")>0 || ($_SESSION['LOGIN_USER_EDUID']=="admin") )
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="老师"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
			if($是否强制每个流程对应一个移动端菜单=="是")			{
				$访问地址															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= $访问地址;
			}
			else		{
				$访问地址															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX.".php";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= $访问地址;
			}
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']				= $菜单地址转排序号[$访问地址];
		}
		//学生菜单输出. 没有做判断,学生端的菜单全部显示出来.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="学生"
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="学生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			//print $访问地址;print_R($形成一个学生菜单菜单的数组KEYS);
			if(in_array($访问地址,$形成一个学生菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				if($形成一个学生菜单菜单的数组[$访问地址]!="")				{
					//学生端的部分,菜单进行叠加,原来的设计学生端直接进入到功能区,没有菜单列表
					$访问地址 														= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_student.php";
				}			
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
			}
		}
		//毕业生菜单输出. 没有做判断,学生端的菜单全部显示出来.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="毕业生"
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="毕业生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个毕业生菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				if($形成一个学生菜单菜单的数组[$访问地址]!="")				{
					//学生端的部分,菜单进行叠加,原来的设计学生端直接进入到功能区,没有菜单列表
					$访问地址 														= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_student.php";
				}			
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
			}
		}
		//新生菜单输出. 没有做判断,新生的菜单全部显示出来.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="新生"
			&&$_SESSION['LOGIN_USER_TYPE']=="新生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个新生菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				if($形成一个学生菜单菜单的数组[$访问地址]!="")				{
					//学生端的部分,菜单进行叠加,原来的设计学生端直接进入到功能区,没有菜单列表
					$访问地址 														= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_student.php";
				}			
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
			}
			else  	{
				$_SESSION['形成一个新生菜单菜单的数组KEYS'] = $形成一个新生菜单菜单的数组KEYS;
				$_SESSION['用于判断_访问地址'][] = $访问地址;
			}
		}
		
		//访客管理员
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			//&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="访客"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个访客菜单菜单的数组KEYS))									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
				
				//暂时不启用数量,因为还没有解决,如果高效率的更新图标的值的方法.
				if($表单IDX=="1765"&&$步骤X==3&&1==0)			{
					$记录数量  	= 0;
					require_once("../TDFORMDATA/data_fangke_record_function.php");
					global $SYSTEM_ADD_SQL;
					data_fangke_record_访客管理SQL过滤();
					$sql 		= "select COUNT(*) AS NUM FROM data_fangke_record where 1=1 $SYSTEM_ADD_SQL";
					$rs 		= $db->Execute($sql);
					$记录数量 	= $rs->fields['NUM'];
					//print $SYSTEM_ADD_SQL;
					$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['记录数量']		= (INT)$记录数量;
					$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['记录数量sql']		= $sql;
				}
			}
		}
	}
	
	//输出报表部分菜单
	global $指定表单下面的流程ID,$指定表单下面的报表ID;
	$流程ID转表单ID 		= array();
	$指定表单下面的流程ID 	= array();
	$sql 					= "select id,表单ID,报表名称,步骤,参数设置 from form_formreport where 表单ID in ('".join("','",$表单IDLIST)."') order by 表单ID asc,步骤 asc";
	$rs						= $db->CacheExecute($SYSTEM_CACHE_SECOND_TDFORMICAMPUS,$sql);
	$rs_a					= $rs->GetArray();
	for($i=0;$i<sizeof($rs_a);$i++)												{
		$表单IDX				= $rs_a[$i]['表单ID'];
		$编号X					= $rs_a[$i]['id'];
		$步骤X					= $rs_a[$i]['步骤'];
		$报表名称X				= $rs_a[$i]['报表名称'];
		$参数设置				= unserialize($rs_a[$i]['参数设置']);
		$掌上校园_图标名称 		= $参数设置['掌上校园_图标名称'];
		$掌上校园_整体是否启用 	= $参数设置['掌上校园_整体是否启用'];
		$是否强制每个流程对应一个移动端菜单 	= $参数设置['是否强制每个流程对应一个移动端菜单'];
		$掌上校园_分组名称1 	= $参数设置['掌上校园_分组名称1'];
		$掌上校园_整个模块图标 	= $参数设置['掌上校园_整个模块图标'];
		$额外限制权限 			= $参数设置['额外限制权限'];
		$移动端模式下面表格的栏目字段的值进行自动换行		= $参数设置['移动端模式下面表格的栏目字段的值进行自动换行'];
		if($额外限制权限=="不设置")			$额外限制权限 	= "全校";
		$掌上校园_把当前菜单加载到其它的掌上校园图标 		= $参数设置['掌上校园_把当前菜单加载到其它的掌上校园图标'];
		//强制关联用户时需要做用户的权限校验 未设置权限时,默认全部权限,设置权限时沿用设置时的值.
		//教职工菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限!="任何人"
			&&$额外限制权限!="学生"
			&&$额外限制权限!="家长"
			&&($掌上校园_把当前菜单加载到其它的掌上校园图标==""||$掌上校园_把当前菜单加载到其它的掌上校园图标=="不设置")
			&&( strpos($LOGIN_USER_PRIV_TEXT,"-".$报表名称X.",")>0 || ($_SESSION['LOGIN_USER_EDUID']=="admin") )
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="老师"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
			$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
			if($是否强制每个流程对应一个移动端菜单=="是")			{
				$访问地址															= "report_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
			}
			else		{
				//沿用流程部分的设置
				//$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址'] 		= "icampus_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX.".php";
			}
			if($移动端模式下面表格的栏目字段的值进行自动换行=="是")			{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['页面类型'] 		= "webpage";
			}
		}
		//学生菜单输出.
		if(	$掌上校园_整体是否启用=="是"
			&&$掌上校园_图标名称!=""
			&&$额外限制权限=="学生"
			&&in_array($表单IDX,$得到用户所属的表单ID列表X)
			&&( ($SYSTEM_FORCE_TO_BIND_USER_STATUS!=3) || ($SYSTEM_FORCE_TO_BIND_USER_STATUS==3&&$SYSTEM_APPSTORE_ID==2) )
			&&$_SESSION['LOGIN_USER_TYPE']=="学生"
		)			{
			$指定表单下面的流程ID[$表单IDX]['id'] 	= $编号X;
			$指定表单下面的流程ID[$表单IDX]['图标名称'] = $掌上校园_图标名称;
			$流程ID转表单ID['流程'][$编号X] 			= $表单IDX;
			$访问地址 															= "report_".$表单MAP[$表单IDX]['英文标识']."_".$表单IDX."_".$步骤X.".php";
			if(in_array($访问地址,$形成一个学生菜单菜单的数组KEYS)&&$是否强制每个流程对应一个移动端菜单=="是")									{
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程ID'][] 		= $编号X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单英文标识'] 	= $表单MAP[$表单IDX]['英文标识'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['表单ID'] 			= $表单IDX;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['流程步骤'] 		= $步骤X;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['图标'] 			= $掌上校园_整个模块图标;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['强制登录'] 		= 1;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['掌上校园_自定义小程序页面'] 		= $参数设置['掌上校园_自定义小程序页面'];
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['其它信息'] 		= $额外限制权限."-".$小程序信息['角色权限']."-1";
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['访问地址']		= $访问地址;
				$权限表[$掌上校园_分组名称1][$掌上校园_图标名称]['排序号']			= $菜单地址转排序号[$访问地址];
			}
		}
	}
	//print_R($权限表);
	
	$COUNTER 	= 0;
	$分组KEYS 	= array_keys($权限表);
	//把[基础数据]这个值放到最下面-开始
	$分组KEYSNEW= array();
	$通知公告_是否启用 = 0;
	$基础数据_是否启用 = 0;	
	if(in_array("通知公告",$分组KEYS))		{
		$分组KEYSNEW[] 	= "通知公告";
	}
	for($i=0;$i<count($分组KEYS);$i++)			{
		$分组KEY 				= $分组KEYS[$i];
		if($分组KEY!="基础数据"&&$分组KEY!="通知公告")		{
			$分组KEYSNEW[] 		= $分组KEY;
		}
	}
	if(in_array("基础数据",$分组KEYS))		{
		$分组KEYSNEW[] 	= "基础数据";
	}
	$分组KEYS		= $分组KEYSNEW;
	//把[基础数据]这个值放到最下面-结束
	for($i=0;$i<count($分组KEYS);$i++)			{
		$分组KEY 				= $分组KEYS[$i];
		$当前分组下面的菜单 	= $权限表[$分组KEY];
		$当前分组下面的菜单KEYS = array_keys($当前分组下面的菜单);
		//print_R($当前分组下面的菜单);
		for($iR=0;$iR<count($当前分组下面的菜单KEYS);$iR++)			{
			$当前分组下面的菜单KEY 	= $当前分组下面的菜单KEYS[$iR];
			$表单英文标识			= $当前分组下面的菜单[$当前分组下面的菜单KEY]['表单英文标识'];
			$表单ID					= $当前分组下面的菜单[$当前分组下面的菜单KEY]['表单ID'];
			$图标					= $当前分组下面的菜单[$当前分组下面的菜单KEY]['图标'];
			$强制登录				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['强制登录'];
			$访问地址				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['访问地址'];
			$其它信息				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['其它信息'];
			$页面类型				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['页面类型'];
			$Element				= array();
			$Element['id']			= $COUNTER;
			$Element['name']		= $当前分组下面的菜单KEY;
			$Element['groupname']	= $分组KEY;
			$Element['url']			= "/general/EDU/Interface/TDFORMICAMPUS/".$访问地址;
			$Element['headerurl']	= $Element['url']."?showHeader=showHeader";
			if(is_file("images2020/".$图标))	{
				$Element['img'] 		= $HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images2020/".$图标;
			}
			else{
				$Element['img'] 		= $HTTP_HOST."/general/EDU/Interface/TDFORMICAMPUS/images/".$图标;
			}	
			if($页面类型!="")			{
				$Element['type']		= $页面类型;
				$Element['url']			= "/general/EDU/Interface/TDFORMDATA/".$访问地址;
			}
			else	{
				$Element['type']		= "InterfaceInit";
			}
			//InterfaceNews InterfaceNotify
			$Element['pathurl']		= $Element['type']."_".$COUNTER."_".$COUNTER;
			$Element['itemid']		= $COUNTER;
			$Element['number']		= "0";//图标的右上角提示信息
			$Element['formid']		= $表单ID;
			$Element['flowid']		= $当前分组下面的菜单[$当前分组下面的菜单KEY]['流程步骤'];
			$Element['forcelogin']	= $强制登录;//图标的右上角提示信息
			$Element['其它信息']	= $其它信息;//图标的右上角提示信息
			$Element['自定义小程序页面']	= $当前分组下面的菜单[$当前分组下面的菜单KEY]['掌上校园_自定义小程序页面'];//
			
			//通知公告的前面两个菜单项目,直接在微信小程序的代码中写死,需要在"校园"图标中显示出来.
			$排序号 				= $当前分组下面的菜单[$当前分组下面的菜单KEY]['排序号'];
			if($分组KEY=="通知公告"&&$iR>=2)				{
				//$MainDataList[$分组KEY][$排序号]		= $Element;	
			}
			if($分组KEY!="通知公告")				{
				//$MainDataList[$分组KEY][$排序号]		= $Element;	
			}
			$MainDataMap[$排序号]	= $Element;
			$COUNTER ++;
		}
	}
	
	//print_R($菜单地址转排序号);
	//print_R($权限表);
	//重新排序
	ksort($MainDataMap);
	$COUNTER = 0;
	foreach($MainDataMap AS $MKEY=>$MVALUE)		{
		$MVALUE['id'] 		= $COUNTER;
		$MVALUE['itemid']	= $COUNTER;
		$MainDataMap_NEW[] 	= $MVALUE;
		$COUNTER++;
		$MainDataGroup[(STRING)$MVALUE['groupname']] 	= $MVALUE['groupname'];
	}
	$MainDataMap			= $MainDataMap_NEW;
	//重新排序 MainDataList
	$COUNTER = 0;
	
	foreach($MainDataMap AS $MKEY=>$MVALUE)								{
		$MainDataList_NEW[(STRING)$MVALUE['groupname']][] = $MVALUE;
	}
	
	//print_R($MainDataList);
	$MainDataList			= $MainDataList_NEW;
	//print_R($MainDataList_NEW);
	
	$RESULT['MainDataList']		= $MainDataList;						//主要数据区
	$RESULT['MainDataMap']		= $MainDataMap;							//主要数据区
	$RESULT['MainDataGroup']	= array_keys($MainDataGroup);			//菜单分组
	$RESULT['MainImageList']	= $MainImageList;//三张滚动截图
	$RESULT['IsSearch']			= false;//是否显示搜索,目前需要关闭
	$RESULT['IsUserAvator']		= false;//是否显示用户头像
	$RESULT['用户类型']			= $_SESSION['LOGIN_USER_TYPE'];//是否显示用户头像
	$RESULT['LOGIN_USER_EDUID']	= $_SESSION['LOGIN_USER_EDUID'];//是否显示用户头像
	//$RESULT['IndexLogo']		= array("/general/EDU/Interface/TDFORMICAMPUS/images/theme/logo.png","LOGO大小:360*100,建议PNG格式,显示格式:宽度不变,高度自适应");
	$RESULT['IndexLogo']		= array();
	$RESULT['SYSTEM_FORCE_TO_BIND_USER_STATUS']	= $SYSTEM_FORCE_TO_BIND_USER_STATUS;
	$RESULT['_REQUEST']			= $_REQUEST;
	$RESULT['_POST']			= $_POST;
	$RESULT['权限表']			= $权限表;
	$RESULT['表单IDLIST']		= $表单IDLIST;
	$RESULT['表单列表']			= $表单列表;
	$RESULT['_SESSION']			= $_SESSION;
	
	$返回数据 = $RESULT;
	$返回数据 = RSA_GBK_TO_UTF8($返回数据,"gbk","utf-8");
	$返回数据 = 掌上校园_输出数据($返回数据);
	print_R($返回数据);
	exit;
}

?>