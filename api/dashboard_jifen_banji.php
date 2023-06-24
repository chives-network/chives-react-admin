<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

CheckAuthUserLoginStatus();

$学期 = returntablefield("data_xueqi","当前学期","是","学期名称")['学期名称'];

$USER_ID    = ForSqlInjection($GLOBAL_USER->USER_ID);

$sql        = "select * from data_deyu_geren_gradeone";
$rs         = $db->CacheExecute(10,$sql);
$rs_a       = $rs->GetArray();
$图标和颜色 = [];
foreach($rs_a as $Line) {
    $图标和颜色[$Line['名称']]['颜色'] = $Line['颜色'];
    $图标和颜色[$Line['名称']]['图标'] = $Line['图标'];
}

$sql        = "select 班级名称 from data_banji where 是否毕业='否' and (find_in_set('$USER_ID',实习班主任) or (班主任用户名='$USER_ID'))";
$rs         = $db->CacheExecute(10,$sql);
$rs_a       = $rs->GetArray();
$班级名称Array = [];
$TopRightOptions = [];
foreach($rs_a as $Line) {
    $班级名称Array[]    = ForSqlInjection($Line['班级名称']);
    $TopRightOptions[] = ['name'=>ForSqlInjection($Line['班级名称']), 'url'=>'/tab/apps_180','fieldname'=>'班级'];
}
if($_GET['className']!="")   {
    $班级 = ForSqlInjection($_GET['className']);
}
elseif($班级名称Array[0]!="") {
    $班级 = $班级名称Array[0];
}
else {
    $班级 = "计算机三班";
}
if(sizeof($TopRightOptions)==0)  {
    $TopRightOptions[] = ['name'=>ForSqlInjection($班级), 'url'=>'/tab/apps_180','fieldname'=>'班级'];
}

//奖杯模块
$sql = "select SUM(积分分值) AS NUM from data_deyu_geren_record where 班级='$班级' and 学期='$学期' ";
$rs = $db->CacheExecute(180,$sql);
$AnalyticsTrophy['Welcome']     = "您好,".$GLOBAL_USER->USER_NAME."!🥳";
$AnalyticsTrophy['SubTitle']    = $班级."总积分";
$AnalyticsTrophy['TotalScore']  = $rs->fields['NUM'];
$AnalyticsTrophy['ViewButton']['name']  = "查看明细";
$AnalyticsTrophy['ViewButton']['url']   = "/tab/apps_180";
$AnalyticsTrophy['TopRightOptions']     = $TopRightOptions;

//按一级指标统计积分
$sql = "select 一级指标 AS title, SUM(积分分值) AS NUM from data_deyu_geren_record where 班级='$班级' and 学期='$学期' group by 一级指标 order by 一级指标 asc";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
$Item = [];
$data = [];
$Index = 0;
foreach($rs_a as $Element)   {
    $data[] = ['title'=>$Element['title'],'stats'=>$Element['NUM'],'color'=>$图标和颜色[$Element['title']]['颜色'],'icon'=>"mdi:".$图标和颜色[$Element['title']]['图标']];
    $Index ++;
}
$AnalyticsTransactionsCard['Title']       = "德育量化";
$AnalyticsTransactionsCard['SubTitle']    = "按一级指标统计";
$AnalyticsTransactionsCard['data']        = $data;
$AnalyticsTransactionsCard['TopRightOptions']    = ['最近一周','最近一个月','整个学期'];
$AnalyticsTransactionsCard['TopRightOptions']    = [];


//得到最新加分或是扣分的几条记录
$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 班级='$班级' and 学期='$学期' and 积分分值>0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = "mdi:".$图标和颜色[$rs_a[$i]['一级指标']]['图标'];
    $rs_a[$i]['图标颜色'] = $图标和颜色[$rs_a[$i]['一级指标']]['颜色'];
}
$AnalyticsDepositWithdraw['加分']['Title']             = "加分";
$AnalyticsDepositWithdraw['加分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/tab/apps_180'];
$AnalyticsDepositWithdraw['加分']['data']              = $rs_a;

$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 班级='$班级' and 学期='$学期' and 积分分值<0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = "mdi:".$图标和颜色[$rs_a[$i]['一级指标']]['图标'];
    $rs_a[$i]['图标颜色'] = $图标和颜色[$rs_a[$i]['一级指标']]['颜色'];
}
$AnalyticsDepositWithdraw['扣分']['Title']             = "扣分";
$AnalyticsDepositWithdraw['扣分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/tab/apps_180'];
$AnalyticsDepositWithdraw['扣分']['data']              = $rs_a;


//本班积分排行 
$colorArray = ['primary','success','warning','info','info'];
$iconArray  = ['mdi:trending-up','mdi:account-outline','mdi:cellphone-link','mdi:currency-usd','mdi:currency-usd','mdi:currency-usd'];
$sql    = "select 学号, 姓名, SUM(积分分值) AS 积分分值 from data_deyu_geren_record where 班级='$班级' and 学期='$学期' group by 学号 order by 积分分值 desc limit 5";
$rs     = $db->CacheExecute(180,$sql);
$rs_a   = $rs->GetArray();
$Item   = [];
$Index  = 0;
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['图标颜色'] = $colorArray[$i];
}
$AnalyticsSalesByCountries['Title']       = "班级排行";
$AnalyticsSalesByCountries['SubTitle']    = "本班积分最高学生";
$AnalyticsSalesByCountries['data']        = $rs_a;
$AnalyticsSalesByCountries['TopRightOptions']    = ['最近一周','最近一个月','整个学期'];
$AnalyticsSalesByCountries['TopRightOptions']    = [];


$RS                                 = [];
$RS['defaultValue']                 = $班级;
$RS['AnalyticsTrophy']              = $AnalyticsTrophy;
$RS['AnalyticsTransactionsCard']    = $AnalyticsTransactionsCard;
$RS['AnalyticsDepositWithdraw']     = $AnalyticsDepositWithdraw;
$RS['AnalyticsSalesByCountries']    = $AnalyticsSalesByCountries;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;

print_R(json_encode($RS));















?>