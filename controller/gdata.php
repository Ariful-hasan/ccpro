<?php
require_once 'BaseChartDataController.php';
class Gdata extends BaseChartDataController
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionChannelday();
	}

	function actionChannelday()
	{
		include('model/MReport.php');
		$report_model = new MReport();

		$day = $this->getMultiParam('day');
		$tid = $this->getMultiParam('tid');
		$trunkinfo = null;
		$channel_usage = $report_model->getChannelUseByDay($day, $tid);
		$trank_name = "";
		if (!empty($tid)) {
			include('model/MSetting.php');
			$setting_model = new MSetting();
			$trunkinfo = $setting_model->getTrunkById($tid);
		}
		$trunk_name = empty($trunkinfo) ? '' : $trunkinfo->trunk_name;
		$this->drawDayChart($day, $trunk_name, $channel_usage);
	}

	function actionChannelmonth()
	{
		include('model/MReport.php');
		$report_model = new MReport();

		$month = $this->getMultiParam('month', date("m"));
		$year = $this->getMultiParam('year',date("Y"));
		$tid = $this->getMultiParam('tid');
		//echo $month," ",$year," ",$tid;
		$trunkinfo = null;
		$channel_usage = $report_model->getChannelUseByMonth($year, $month, $tid);
		$trank_name = "";
		if (!empty($tid)) {
			include('model/MSetting.php');
			$setting_model = new MSetting();
			$trunkinfo = $setting_model->getTrunkById($tid);
		}
		$trunk_name = empty($trunkinfo) ? '' : $trunkinfo->trunk_name;
		$this->drawMonthChart($year, $month, $trunk_name, $channel_usage);
	}

	function drawMonthChart($year, $month, $trunk_name, $channel_usage)
	{
		include('lib/GS_Chart.php');
		$values = array();
		$labels = array();
		$usage = array();
		$max_channel = 0;
		if(strlen($month)==1) $month = "0$month";
		$cur_month = date("Ym");
		$provided_month = $year . $month;
		$numDayToPlot = $this->daysInMonth((int) $month, $year);
		$maxDay = $provided_month>$cur_month ? 0 : $numDayToPlot;
		$plot_scale = 25;

		if ($cur_month==$provided_month) $maxDay = date("d");

		if (is_array($channel_usage)) {
			foreach ($channel_usage as $cusage) {
				$usage[$cusage->sdate] = $cusage->ch_count;
				if($cusage->ch_count>$max_channel) $max_channel = $cusage->ch_count;
			}
		}

		for($i=1; $i<=$numDayToPlot; $i++) {
			$_day = strlen($i)==1 ? "$year-$month-0$i" : "$year-$month-$i";
			$labels[] = "$i";
			if($i<=$maxDay) $values[] = isset($usage[$_day]) ? (int) $usage[$_day] : 0;
		}

		if($max_channel>300) $plot_scale = 50;
		
		$max_channel = ceil($max_channel/$plot_scale);
		$ysteps = 5;
		$max_channel *= $plot_scale;
		if ($max_channel<=25) $max_channel = 25;

		if ($max_channel >= 100) {
			$ysteps = ceil(($max_channel + 1)/40);
			$max_channel = $ysteps * 40;
			$ysteps = $max_channel/4;
		} else {
			$ysteps = ceil(($max_channel + 1)/20);
			$max_channel = $ysteps * 20;
			$ysteps = $max_channel/4;
		}
		
		$months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', 
			'06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October',
			'11' => 'November', '12' => 'December');
		
		$txt_title = empty($trunk_name) ? "Channel Report for $months[$month], $year" : "Channel Report of $trunk_name for $months[$month], $year";
		
		$x = new OFC_Elements_Axis_X();
		$x->set_labels_from_array($labels);
		
		$y = new OFC_Elements_Axis_Y();
		$y->set_range(0, $max_channel, $ysteps);
		
		$legend_x = new OFC_Elements_Legend_X("Day");
		$legend_x->set_style("{color: #736AFF; font-size: 15px;}");

		$legend_y = new OFC_Elements_Legend_Y("Channels");
		$legend_y->set_style("{color: #736AFF; font-size: 12px;}");
		
		$line_dot = new OFC_Charts_Line();
		$chart = new GS_Chart($txt_title);

		$line_dot->set_values($values);

		$line_dot->set_key( "Max Channel Count", 12);
		$chart->set_x_axis($x);
		$chart->set_x_legend($legend_x);
		$chart->set_y_legend($legend_y);
		$chart->set_y_axis($y);

		$chart->add_element( $line_dot );
		//$line_dot->set_tooltip($a);
		if($this->is_highchart){
			$this->ConvertToHighChartResponse($chart);
		}else{
			echo $chart->toPrettyString();
			exit;
		}
	}

	function drawDayChart($day, $trunk_name, $channel_usage)
	{
		//$path = 'lib/';
		//set_include_path(get_include_path() . PATH_SEPARATOR . $path);
		//require_once('lib/OFC/OFC_Chart.php');
		include('lib/GS_Chart.php');

		$values = array();
		$labels = array();
		$usage = array();
		$max_channel = 0;
		$today = date("Y-m-d");
		$maxHr = $day==$today ? date("H") : 23;
		if($day>$today) $maxHr = 0;
		$plot_scale = 25;
		
		if (is_array($channel_usage)) {
			foreach ($channel_usage as $cusage) {
				$usage[$cusage->shour] = $cusage->ch_count;
				if ($cusage->ch_count>$max_channel) $max_channel = $cusage->ch_count;
			}
		}

		for($i=0; $i<=23; $i++) {
			$hour = strlen($i)==1 ? "0$i" : $i;
			$labels[] = "$hour";
			if($i<=$maxHr) $values[] = isset($usage[$hour]) ? (int) $usage[$hour] : 0;
		}
		
		if($max_channel>300) $plot_scale = 50;
		
		$max_channel = ceil($max_channel/$plot_scale);
		$ysteps = $plot_scale;
		$max_channel *= $plot_scale;
		if($max_channel<=25) $max_channel = 25;

		if ($max_channel >= 100) {
			$ysteps = ceil(($max_channel + 1)/40);
			$max_channel = $ysteps * 40;
			$ysteps = $max_channel/4;
		} else {
			$ysteps = ceil(($max_channel + 1)/20);
			$max_channel = $ysteps * 20;
			$ysteps = $max_channel/4;
		}

		$txt_title = "Channel Report of $day";
		if (!empty($trunk_name)) $txt_title .= " for $trunk_name";
		//$title = new OFC_Elements_Title( $txt_title );
		//$title->set_style("{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}");

		$x = new OFC_Elements_Axis_X();
		$x->set_labels_from_array($labels);

		$y = new OFC_Elements_Axis_Y();
		$y->set_range(0, $max_channel, $ysteps);

		$legend_x = new OFC_Elements_Legend_X("Hour (GMT)");
		$legend_x->set_style("{color: #736AFF; font-size: 15px;}");

		$legend_y = new OFC_Elements_Legend_Y("Channels");
		$legend_y->set_style("{color: #736AFF; font-size: 12px;}");


		$line_dot = new OFC_Charts_Line();
		$chart = new GS_Chart($txt_title);
		//$chart->set_title( $title );

		$line_dot->set_values($values);
		//$line_dot->tip = "Channel: #val#<br>Hour: #x_label#";
		//$line_dot->tip = "Channel: #val#<br>Hour: #x_label#";
		//$line_dot->dot-size = 6;
		//$line_dot->colour = "#a44a80";

		$line_dot->set_key( "Channel Count", 12 );
		$chart->set_x_axis($x);
		$chart->set_x_legend($legend_x);
		$chart->set_y_legend($legend_y);
		$chart->set_y_axis($y);

		$chart->add_element( $line_dot );
		if($this->is_highchart){
			$this->ConvertToHighChartResponse($chart);
		}else{
			echo $chart->toPrettyString();
			exit;
		}
		
		//$line_dot->set_tooltip($a);
		
	}

	function leapYear($year)
	{
		if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0)) return TRUE;
		return FALSE;
	}

	function daysInMonth($month = 0, $year = '')
	{
		$days_in_month    = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$d = array("Jan" => 31, "Feb" => 28, "Mar" => 31, "Apr" => 30, "May" => 31, "Jun" => 30, "Jul" => 31, "Aug" => 31, "Sept" => 30, "Oct" => 31, "Nov" => 30, "Dec" => 31);
		if(!is_numeric($year) || strlen($year) != 4) $year = date('Y');
		if($month == 2 || $month == 'Feb') {
			if ($this->leapYear($year)) return 29;
		}
		if(is_numeric($month)) {
			if($month < 1 || $month > 12) return 0;
			else return $days_in_month[$month - 1];
		} else {
			if(in_array($month, array_keys($d))) return $d[$month];
			else return 0;
		}
	}

}
