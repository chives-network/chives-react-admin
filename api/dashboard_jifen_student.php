<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

CheckAuthUserLoginStatus();

$学号 = $GLOBAL_USER->学号;
$班级 = $GLOBAL_USER->班级;
$学期 = returntablefield("data_xueqi","当前学期","是","学期名称")['学期名称'];

$sql        = "select * from data_deyu_geren_gradeone";
$rs         = $db->CacheExecute(10,$sql);
$rs_a       = $rs->GetArray();
$图标和颜色 = [];
foreach($rs_a as $Line) {
    $图标和颜色[$Line['名称']]['颜色'] = $Line['颜色'];
    $图标和颜色[$Line['名称']]['图标'] = $Line['图标'];
}

//奖杯模块
$sql = "select SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' and 学期='$学期' ";
$rs = $db->CacheExecute(180,$sql);
$AnalyticsTrophy['Welcome']     = "您好,".$GLOBAL_USER->USER_NAME."!🥳";
$AnalyticsTrophy['SubTitle']    = "个人总积分";
$AnalyticsTrophy['TotalScore']  = $rs->fields['NUM'];
$AnalyticsTrophy['ViewButton']['name']  = "查看明细";
$AnalyticsTrophy['ViewButton']['url']   = "/apps/177";

//按一级指标统计积分
$sql = "select 一级指标 AS title, SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' and 学期='$学期' group by 一级指标 order by 一级指标 asc";
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
$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' and 学期='$学期' and 积分分值>0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = "mdi:".$图标和颜色[$rs_a[$i]['一级指标']]['图标'];
    $rs_a[$i]['图标颜色'] = $图标和颜色[$rs_a[$i]['一级指标']]['颜色'];
}
$AnalyticsDepositWithdraw['加分']['Title']             = "加分";
$AnalyticsDepositWithdraw['加分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/apps/177'];
$AnalyticsDepositWithdraw['加分']['data']              = $rs_a;

$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' and 学期='$学期' and 积分分值<0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = "mdi:".$图标和颜色[$rs_a[$i]['一级指标']]['图标'];
    $rs_a[$i]['图标颜色'] = $图标和颜色[$rs_a[$i]['一级指标']]['颜色'];
}
$AnalyticsDepositWithdraw['扣分']['Title']             = "扣分";
$AnalyticsDepositWithdraw['扣分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/apps/177'];
$AnalyticsDepositWithdraw['扣分']['data']              = $rs_a;


//本班积分排行 
$colorArray = ['primary','success','warning','info','info'];
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





$RS = [];
$RS['AnalyticsTrophy']              = $AnalyticsTrophy;
$RS['AnalyticsTransactionsCard']    = $AnalyticsTransactionsCard;
$RS['AnalyticsDepositWithdraw']     = $AnalyticsDepositWithdraw;
$RS['AnalyticsSalesByCountries']    = $AnalyticsSalesByCountries;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;

print_R(json_encode($RS));















?>