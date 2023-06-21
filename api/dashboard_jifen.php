<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

//CheckAuthUserLoginStatus();

$学号 = "20230101";
$学期 = "2022-2023-第二学期";

//奖杯模块
$sql = "select SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' and 学期='$学期' ";
$rs = $db->CacheExecute(180,$sql);
$AnalyticsTrophy['Welcome']     = "您好,".$GLOBAL_USER->USER_NAME."XXXXXXX!🥳";
$AnalyticsTrophy['SubTitle']    = "个人总积分";
$AnalyticsTrophy['TotalScore']  = $rs->fields['NUM'];
$AnalyticsTrophy['ViewButton']['name']  = "查看明细";
$AnalyticsTrophy['ViewButton']['url']   = "/apps/177";

//按一级指标统计积分
$colorArray = ['primary','success','warning','info','info'];
$iconArray = ['mdi:trending-up','mdi:account-outline','mdi:cellphone-link','mdi:currency-usd','mdi:currency-usd','mdi:currency-usd'];
$sql = "select 一级指标 AS title, SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' and 学期='$学期' group by 一级指标 order by 一级指标 asc";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
$Item = [];
$Index = 0;
foreach($rs_a as $Element)   {
    $data[] = ['title'=>$Element['title'],'stats'=>$Element['NUM'],'color'=>$colorArray[$Index],'icon'=>$iconArray[$Index]];
    $Index ++;
}
$AnalyticsTransactionsCard['Title']       = "德育量化";
$AnalyticsTransactionsCard['SubTitle']    = "按一级指标统计";
$AnalyticsTransactionsCard['data']        = $data;
$AnalyticsTransactionsCard['TopRightButton'][]    = ['name'=>'最近一周','url'=>'/apps/177'];
$AnalyticsTransactionsCard['TopRightButton'][]    = ['name'=>'最近一月','url'=>'/apps/177'];
$AnalyticsTransactionsCard['TopRightButton'][]    = ['name'=>'当前学期','url'=>'/apps/177'];


//得到最新加分或是扣分的几条记录
$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' and 学期='$学期' and 积分分值>0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = $iconArray[$i];
    $rs_a[$i]['图标颜色'] = $colorArray[$i];
}
$AnalyticsDepositWithdraw['加分']['Title']             = "加分";
$AnalyticsDepositWithdraw['加分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/apps/177'];
$AnalyticsDepositWithdraw['加分']['data']              = $rs_a;

$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' and 学期='$学期' and 积分分值<0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = $iconArray[$i];
    $rs_a[$i]['图标颜色'] = $colorArray[$i];
}
$AnalyticsDepositWithdraw['扣分']['Title']             = "扣分";
$AnalyticsDepositWithdraw['扣分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/apps/177'];
$AnalyticsDepositWithdraw['扣分']['data']              = $rs_a;



$RS = [];
$RS['AnalyticsTrophy']              = $AnalyticsTrophy;
$RS['AnalyticsTransactionsCard']    = $AnalyticsTransactionsCard;
$RS['AnalyticsDepositWithdraw']     = $AnalyticsDepositWithdraw;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;

print_R(json_encode($RS));















?>