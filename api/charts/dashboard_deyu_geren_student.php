<?php
header("Content-Type: application/json"); 
require_once('../cors.php');
require_once('../include.inc.php');

CheckAuthUserLoginStatus();

$optionsMenuItem = $_GET['optionsMenuItem'];
if($optionsMenuItem=="")  {
    $optionsMenuItem = "当前学期";
}

$学期 = returntablefield("data_xueqi","当前学期","是","学期名称")['学期名称'];

switch($optionsMenuItem) {
    case '最近一周':
        $whereSql = " and 积分时间 >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
        break;
    case '最近一月':
        $whereSql = " and 积分时间 >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case '当前学期':
        $whereSql = " and 学期='$学期'";
        break;
    case '所有学期':
        $whereSql = "";
        break;
}

$学号 = $GLOBAL_USER->学号;
$班级 = $GLOBAL_USER->班级;

$sql        = "select * from data_deyu_geren_gradeone";
$rs         = $db->CacheExecute(10,$sql);
$rs_a       = $rs->GetArray();
$图标和颜色 = [];
foreach($rs_a as $Line) {
    $图标和颜色[$Line['名称']]['颜色'] = $Line['颜色'];
    $图标和颜色[$Line['名称']]['图标'] = $Line['图标'];
}

//奖杯模块
$sql = "select SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' $whereSql ";
$rs = $db->CacheExecute(180,$sql);
$AnalyticsTrophy['Welcome']     = "您好,".$GLOBAL_USER->USER_NAME."!🥳";
$AnalyticsTrophy['SubTitle']    = "个人总积分";
$AnalyticsTrophy['TotalScore']  = $rs->fields['NUM'];
$AnalyticsTrophy['ViewButton']['name']  = "查看明细";
$AnalyticsTrophy['ViewButton']['url']   = "/apps/177";

//按一级指标统计积分
$sql = "select 一级指标 AS title, SUM(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' $whereSql group by 一级指标 order by 一级指标 asc";
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
$AnalyticsTransactionsCard['TopRightOptions'][]    = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];
$AnalyticsTransactionsCard['TopRightOptions'][]    = ['name'=>'最近一月','selected'=>$optionsMenuItem=='最近一月'?true:false];
$AnalyticsTransactionsCard['TopRightOptions'][]    = ['name'=>'当前学期','selected'=>$optionsMenuItem=='当前学期'?true:false];
$AnalyticsTransactionsCard['TopRightOptions'][]    = ['name'=>'所有学期','selected'=>$optionsMenuItem=='所有学期'?true:false];


//得到最新加分或是扣分的几条记录
$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' $whereSql and 积分分值>0 order by id desc limit 5";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
for($i=0;$i<sizeof($rs_a);$i++) {
    $rs_a[$i]['项目图标'] = "mdi:".$图标和颜色[$rs_a[$i]['一级指标']]['图标'];
    $rs_a[$i]['图标颜色'] = $图标和颜色[$rs_a[$i]['一级指标']]['颜色'];
}
$AnalyticsDepositWithdraw['加分']['Title']             = "加分";
$AnalyticsDepositWithdraw['加分']['TopRightButton']    = ['name'=>'查看所有','url'=>'/apps/177'];
$AnalyticsDepositWithdraw['加分']['data']              = $rs_a;

$sql = "select 一级指标,二级指标,积分项目,积分分值 from data_deyu_geren_record where 学号='$学号' $whereSql and 积分分值<0 order by id desc limit 5";
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
$sql    = "select 学号, 姓名, SUM(积分分值) AS 积分分值 from data_deyu_geren_record where 班级='$班级' $whereSql group by 学号 order by 积分分值 desc limit 5";
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
$AnalyticsSalesByCountries['TopRightOptions'][]    = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];
$AnalyticsSalesByCountries['TopRightOptions'][]    = ['name'=>'最近一月','selected'=>$optionsMenuItem=='最近一月'?true:false];
$AnalyticsSalesByCountries['TopRightOptions'][]    = ['name'=>'当前学期','selected'=>$optionsMenuItem=='当前学期'?true:false];
$AnalyticsSalesByCountries['TopRightOptions'][]    = ['name'=>'所有学期','selected'=>$optionsMenuItem=='所有学期'?true:false];


//ApexAreaChart
$sql = "select 积分时间,sum(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' $whereSql and 积分分值>0 group by 积分时间 order by 积分时间 asc";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
$输出数据 = [];
for($i=0;$i<sizeof($rs_a);$i++) {
    $输出数据[$rs_a[$i]['积分时间']] = $rs_a[$i]['NUM'];
}
$dataY = [];
$dataX = array_keys($输出数据);
$dataY[] = ["name"=>"班级总积分","data"=>array_values($输出数据)];

$ApexAreaChart['Title']       = "班级学生积分之和";
$ApexAreaChart['SubTitle']    = "按天统计班级学生积分之和";
$ApexAreaChart['dataX']       = $dataX;
$ApexAreaChart['dataY']       = $dataY;
$ApexAreaChart['sql']       = $sql;
$ApexAreaChart['TopRightOptions'][]    = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];
$ApexAreaChart['TopRightOptions'][]    = ['name'=>'最近一月','selected'=>$optionsMenuItem=='最近一月'?true:false];
$ApexAreaChart['TopRightOptions'][]    = ['name'=>'当前学期','selected'=>$optionsMenuItem=='当前学期'?true:false];
$ApexAreaChart['TopRightOptions'][]    = ['name'=>'所有学期','selected'=>$optionsMenuItem=='所有学期'?true:false];



$ApexLineChart['Title']       = "班级学生积分之和";
$ApexLineChart['SubTitle']    = "按天统计班级学生积分之和";
$ApexLineChart['dataX']       = $dataX;
$ApexLineChart['dataY']       = $dataY;
$ApexLineChart['sql']       = $sql;
$ApexLineChart['TopRightOptions'][]    = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];
$ApexLineChart['TopRightOptions'][]    = ['name'=>'最近一月','selected'=>$optionsMenuItem=='最近一月'?true:false];
$ApexLineChart['TopRightOptions'][]    = ['name'=>'当前学期','selected'=>$optionsMenuItem=='当前学期'?true:false];
$ApexLineChart['TopRightOptions'][]    = ['name'=>'所有学期','selected'=>$optionsMenuItem=='所有学期'?true:false];




//AnalyticsWeeklyOverview
$sql = "select 积分时间,sum(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' $whereSql and 积分分值>0 group by 积分时间 order by 积分时间 desc limit 7";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
$输出数据 = [];
for($i=0;$i<sizeof($rs_a);$i++) {
    $输出数据[$rs_a[$i]['积分时间']] = $rs_a[$i]['NUM'];
}
ksort($输出数据);
$dataY = [];
$dataX = array_keys($输出数据);
$dataYItem = array_values($输出数据);
$dataY[] = ["name"=>"班级总积分","data"=>$dataYItem];

$AnalyticsWeeklyOverview['Title']         = "班级学生积分周报";
$AnalyticsWeeklyOverview['SubTitle']      = "最近一周班级学生积分之和";
$AnalyticsWeeklyOverview['dataX']         = $dataX;
$AnalyticsWeeklyOverview['dataY']         = $dataY;
$AnalyticsWeeklyOverview['sql']           = $sql;
$AnalyticsWeeklyOverview['TopRightOptions'][]       = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];

$AnalyticsWeeklyOverview['BottomText']['Left']      = array_sum($dataYItem);
$AnalyticsWeeklyOverview['BottomText']['Right']     = "最近一周总积分为".array_sum($dataYItem).", 比上周增加13%";

$AnalyticsWeeklyOverview['ViewButton']['name']  = "明细";
$AnalyticsWeeklyOverview['ViewButton']['url']   = "/tab/apps_180";


//AnalyticsPerformance
$sql = "select 一级指标,sum(积分分值) AS NUM from data_deyu_geren_record where 学号='$学号' $whereSql group by 一级指标 order by 一级指标 asc";
$rs = $db->CacheExecute(180,$sql);
$rs_a = $rs->GetArray();
$输出数据 = [];
for($i=0;$i<sizeof($rs_a);$i++) {
    $输出数据[$rs_a[$i]['一级指标']] = $rs_a[$i]['NUM'];
}
$dataY = [];
$dataX = array_keys($输出数据);
$dataY[] = ["name"=>"班级总积分","data"=>array_values($输出数据)];

$AnalyticsPerformance['Title']       = "按一级指标统计积分之和";
$AnalyticsPerformance['SubTitle']    = "按一级指标统计班级学生积分之和";
$AnalyticsPerformance['dataX']       = $dataX;
$AnalyticsPerformance['dataY']       = $dataY;
$AnalyticsPerformance['sql']       = $sql;
$AnalyticsPerformance['TopRightOptions'][]    = ['name'=>'最近一周','selected'=>$optionsMenuItem=='最近一周'?true:false];
$AnalyticsPerformance['TopRightOptions'][]    = ['name'=>'最近一月','selected'=>$optionsMenuItem=='最近一月'?true:false];
$AnalyticsPerformance['TopRightOptions'][]    = ['name'=>'当前学期','selected'=>$optionsMenuItem=='当前学期'?true:false];
$AnalyticsPerformance['TopRightOptions'][]    = ['name'=>'所有学期','selected'=>$optionsMenuItem=='所有学期'?true:false];


$RS = [];
$RS['AnalyticsTrophy']              = $AnalyticsTrophy;
$RS['AnalyticsTransactionsCard']    = $AnalyticsTransactionsCard;
$RS['AnalyticsDepositWithdraw']     = $AnalyticsDepositWithdraw;
$RS['AnalyticsSalesByCountries']    = $AnalyticsSalesByCountries;
$RS['ApexAreaChart']                = $ApexAreaChart;
$RS['ApexLineChart']                = $ApexLineChart;
$RS['AnalyticsWeeklyOverview']      = $AnalyticsWeeklyOverview;
$RS['AnalyticsPerformance']         = $AnalyticsPerformance;

$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;
$RS['AnalyticsTrophy'] = $AnalyticsTrophy;

print_R(json_encode($RS));















?>