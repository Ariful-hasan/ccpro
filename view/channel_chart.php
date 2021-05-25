<?php
$basepath= $this->getBasePath();
if(defined("BASEPATH")){
	$basepath= BASEPATH;	
}
require_once  $basepath.'lib/chart/highchart.php';
$nc=new HighChart();
$nc->SetChartType("column");
//$nc->SetParamMethod("ChartParam");
$nc->IsAjax=true;
 if (isset($day)){
	$nc->DataUrl=$this->url('task=gdata&act=channelday');
 }else{
 	$nc->DataUrl=$this->url('task=gdata&act=channelmonth');
 }
 if (!isset($day)){
	 $month_options = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May','06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October','11' => 'November', '12' => 'December');
	 $thisyear = date('Y');
	 $prevyear = date('Y') - 5;
	 $year_options =array();// array("*"=>"ALL");
	 foreach (range((date('Y')-5), $thisyear) as $tt){
	 	$year_options[$tt]=$tt;
	 }
 }
 if (is_array($trunk_options)) {
 	$trunk_options=array_merge(array("*"=>"ALL"),$trunk_options);
 }
 
if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<?php
$nc->searchform->AddSearhProperty("Trunk", "tid", "select", $trunk_options,$tid);
if (isset($day)){
	$nc->searchform->AddSearhProperty("Date", "day", "dateonly", "",$dateinfo->sdate);
}else{	
	$nc->searchform->AddSearhProperty("Month", "month", "select", $month_options,$month);
	$nc->searchform->AddSearhProperty("Year", "year", "select", $year_options,$year);
}

?>
<div class="box">
<?php echo $nc->ShowChart(); ?>
</div>
<?php /*?>
<script type="text/javascript">
	function ChartParam(param){	
		return param;
	}
</script>
<?php // */?>
