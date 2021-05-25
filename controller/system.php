<?php

class System extends Controller
{
	function __construct() {
		parent::__construct();
	}
	
	function init()
	{
		$this->actionTempreport();
	}
	
	function actionLoadreport()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data['month'] = date("m");
		$data['year'] = date("Y");
		$data['pageTitle'] = 'Channel Graph by Day';
		$data['side_menu_index'] = 'reports';
		$data['request'] = $this->getRequest();
		$data['tid'] = $tid;
		$data['trunk_options'] = $setting_model->getTrunkOptions();
		$this->getTemplate()->display('channel_chart', $data);
	}
	
	function actionTempreport()
	{
		include('lib/DateHelper.php');
		include('model/MSystem.php');
		$setting_model = new MSystem();

		//$data['month'] = date("m");
		//$data['year'] = date("Y");
		
		$opt = isset($_REQUEST['opt']) ? trim($_REQUEST['opt']) : 'day';
		
		$dateinfo = DateHelper::get_input_time_details();
		
		if ($opt == 'month') {
			$data['month'] = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date("m");
			$data['year'] = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date("Y");
			
		} else {
			$data['day'] = $dateinfo->sdate;
		}
		
		$data['pageTitle'] = 'System Graph by Day';
		$data['side_menu_index'] = 'settings';
		$data['request'] = $this->getRequest();
		
		//$data['iid'] = $iid;
		$current_temperatures = array();
		$current_loads = array();
		$cts = $setting_model->getCurrentTemperatures();
		if (is_array($cts)) {
			foreach ($cts as $ct) {
				$current_temperatures[$ct->item_code] = $ct;
			}
		}
		
		$cts = $setting_model->getCurrentLoads();
		if (is_array($cts)) {
			foreach ($cts as $ct) {
				$current_loads[$ct->item_code] = $ct;
			}
		}

		
		$data['dateinfo'] = $dateinfo;
		$data['opt'] = $opt;
		$data['current_temperatures'] = $current_temperatures;
		$data['current_loads'] = $current_loads;
		if ($opt == 'month') {
			$data['topMenuItems'] = array(array('href'=>'task=system', 'img'=>'chart_line.png', 'label'=>'Day Report'));
		} else {
			$data['topMenuItems'] = array(array('href'=>'task=system&opt=month', 'img'=>'chart_line.png', 'label'=>'Month Report'));
		}
		$data['smi_selection'] = 'system_tempreport';
		$this->getTemplate()->display('system_temperature_chart', $data);
	}

	function actionTempdata()
	{
		include('model/MSystem.php');
		$report_model = new MSystem();

		$opt = isset($_REQUEST['opt']) ? trim($_REQUEST['opt']) : 'day';
		$month ='';
		$year = '';
		$day = '';

		if ($opt == 'month') {
			$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date("m");
			$year = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date("Y");
			$channel_usage1 = $report_model->getChannelUseByDay($year, $month, 'A');
			$channel_usage2 = $report_model->getChannelUseByDay($year, $month, 'B');
		} else {
			$day = isset($_REQUEST['day']) ? trim($_REQUEST['day']) : date("Y-m-d");
			$channel_usage1 = $report_model->getChannelUseByHour($day, 'A');
			$channel_usage2 = $report_model->getChannelUseByHour($day, 'B');
		}

		$trunkinfo1 = $report_model->getCurrentLogById('A');
		$trunkinfo2 = $report_model->getCurrentLogById('B');

		$trunk_name1 = empty($trunkinfo1) ? '' : $trunkinfo1->item;
		$trunk_name2 = empty($trunkinfo2) ? '' : $trunkinfo2->item;
		
		$this->drawDayChart($opt, $day, $month, $year, $trunk_name1, $trunk_name2, $channel_usage1, $channel_usage2, $report_model);
	}
	
	function actionLoaddata()
	{
		include('model/MSystem.php');
		$report_model = new MSystem();

		$opt = isset($_REQUEST['opt']) ? trim($_REQUEST['opt']) : 'day';
		$month ='';
		$year = '';
		$day = '';

		if ($opt == 'month') {
			$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date("m");
			$year = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date("Y");
			$channel_usage1 = $report_model->getChannelUseByDay($year, $month, 'C');
		} else {
			$day = isset($_REQUEST['day']) ? trim($_REQUEST['day']) : date("Y-m-d");
			$channel_usage1 = $report_model->getChannelUseByHour($day, 'C');
		}

		$trunkinfo1 = $report_model->getCurrentLogById('C');
		$trunk_name1 = empty($trunkinfo1) ? '' : $trunkinfo1->item;

		$this->drawLoadDayChart($opt, $day, $month, $year, $trunk_name1, $channel_usage1, $report_model);
	}
	
	function drawLoadDayChart($opt, $day, $month, $year, $trunk_name1, $channel_usage1, $report_model)
	{
		include('lib/GS_Chart.php');

		$values = array();
		$values2 = array();
		$labels = array();
		$usage1 = array();

		$max_value = 120;
		if ($opt == 'month') {

			if (strlen($month)==1) $month = "0$month";
			$cur_month = date("Ym");
			
			$provided_month = $year . $month;
			$numDayToPlot = $this->daysInMonth((int) $month, $year);
			$numX = $this->daysInMonth((int) $month, $year);
			$startI = 1;
			
			if ($cur_month==$provided_month) $maxX = date("d");
			else if ($provided_month>$cur_month) $maxX = 0;
			else $maxX = $numX;
		
			if (is_array($channel_usage1)) {
				foreach ($channel_usage1 as $cusage) {
					$usage1[$cusage->log_date] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
		
			for($i=$startI; $i<=$numX; $i++) {
				$_day = strlen($i)==1 ? "$year-$month-0$i" : "$year-$month-$i";
				$labels[] = "$i";
				$values2[] = 75;
				if ($i<=$maxX) {
					$values[] = isset($usage1[$_day]) ? (int) $usage1[$_day] : 0;
				}
			}
			
			$months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', 
			'06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October',
			'11' => 'November', '12' => 'December');
			
			$chart_title = "Load Report of $months[$month], $year";
			$chart_x_legend_title = "Day";
			$chart_y_legend_title = 'Max Load (%)';
			
		} else {
			$today = date("Y-m-d");
			$maxX = $day==$today ? date("H") : 23;
			if ($day>$today) $maxX = 0;
			$numX = 23;
			$startI = 0;

			if (is_array($channel_usage1)) {
				foreach ($channel_usage1 as $cusage) {
					$usage1[$cusage->log_hour] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
		
			if ($day==$today) {
				$temps = $report_model->getCurrentLoads();
				if (!isset($usage1[$maxX])) $usage1[$maxX] = 0;
				
				if (is_array($temps)) {
					foreach ($temps as $tm) {
						if ($tm->item_code == 'C') {
							if ($usage1[$maxX] < $tm->value) $usage1[$maxX] = $tm->value;
						}
					}
				}
			}
			
			for($i=$startI; $i<=$numX; $i++) {
				$hour = strlen($i)==1 ? "0$i" : $i;
				$labels[] = "$hour";
				$values2[] = 75;
				if($i<=$maxX) {
					$values[] = isset($usage1[$hour]) ? (int) $usage1[$hour] : 0;
				}
			}
			
			$chart_title = "Load Report of $day";
			$chart_x_legend_title = "Hour";
			$chart_y_legend_title = 'Load (%)';
		}

		$ystep_details = $this->calc_y_axis_values(0, $max_value, 6, 20);

		$chart = new GS_Chart($chart_title);
		$chart->set_x_axis_details($chart_x_legend_title, $labels);
		$chart->set_y_axis_details($chart_y_legend_title, $ystep_details[0], $ystep_details[1], $ystep_details[2]);
		$chart->add_line_dot_element($trunk_name1, $values);
		//$chart->add_line_dot_element('', $values2, '#DF3B69');
		echo $chart->toPrettyString();
		exit;

	}

	/*
	//$line_dot->tip = "Channel: #val#<br>Hour: #x_label#";
	//$line_dot->tip = "Channel: #val#<br>Hour: #x_label#";
	//$line_dot->dot-size = 6;
	*/
	
	function drawDayChart($opt, $day, $month, $year, $trunk_name1, $trunk_name2, $channel_usage1, $channel_usage2, $report_model)
	{
		include('lib/GS_Chart.php');

		$values = array();
		$values2 = array();
		$labels = array();
		$usage1 = array();
		$usage2 = array();

		$max_value =100;
		//echo $opt; exit;
		if ($opt == 'month') {

			if (strlen($month)==1) $month = "0$month";
			$cur_month = date("Ym");
			
			$provided_month = $year . $month;
			$numDayToPlot = $this->daysInMonth((int) $month, $year);
			$numX = $this->daysInMonth((int) $month, $year);
			$startI = 1;
			if ($cur_month==$provided_month) $maxX = date("d");
			else if ($provided_month>$cur_month) $maxX = 0;
			else $maxX = $numX;
		
			if (is_array($channel_usage1)) {
				foreach ($channel_usage1 as $cusage) {
					$usage1[$cusage->log_date] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
		
			if (is_array($channel_usage2)) {
				foreach ($channel_usage2 as $cusage) {
					$usage2[$cusage->log_date] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
		
			for($i=$startI; $i<=$numX; $i++) {
				$_day = strlen($i)==1 ? "$year-$month-0$i" : "$year-$month-$i";
				$labels[] = "$i";
				if ($i<=$maxX) {
					$values[] = isset($usage1[$_day]) ? (int) $usage1[$_day] : 0;
					$values2[] = isset($usage2[$_day]) ? (int) $usage2[$_day] : 0;
				}
			}
			
			$months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', 
			'06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October',
			'11' => 'November', '12' => 'December');
			
			$chart_title = "Temperature Report of $months[$month], $year";
			$chart_x_legend_title = "Day";
			$chart_y_legend_title = 'Max Temperature (C)';
			
		} else {
		
			$today = date("Y-m-d");
			$maxX = $day==$today ? date("H") : 23;
			if($day>$today) $maxX = 0;
			$numX = 23;
			$startI = 0;

			if (is_array($channel_usage1)) {
				foreach ($channel_usage1 as $cusage) {
					$usage1[$cusage->log_hour] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
		
			if (is_array($channel_usage2)) {
				foreach ($channel_usage2 as $cusage) {
					$usage2[$cusage->log_hour] = $cusage->value;
					if ($cusage->value>$max_value) $max_value = $cusage->value;
				}
			}
			
			if ($day==$today) {
				$temps = $report_model->getCurrentTemperatures();
				if (!isset($usage1[$maxX])) $usage1[$maxX] = 0;
				if (!isset($usage2[$maxX])) $usage2[$maxX] = 0;
				
				if (is_array($temps)) {
					foreach ($temps as $tm) {
						if ($tm->item_code == 'A') {
							if ($usage1[$maxX] < $tm->value) $usage1[$maxX] = $tm->value;
						} else if ($tm->item_code == 'B') {
							if ($usage2[$maxX] < $tm->value) $usage2[$maxX] = $tm->value;
						}
					}
				}
			}
		
			for($i=$startI; $i<=$numX; $i++) {
				$hour = strlen($i)==1 ? "0$i" : $i;
				$labels[] = "$hour";
				if($i<=$maxX) {
					$values[] = isset($usage1[$hour]) ? (int) $usage1[$hour] : 0;
					$values2[] = isset($usage2[$hour]) ? (int) $usage2[$hour] : 0;
				}
			}
			
			$chart_title = "Temperature Report of $day";
			$chart_x_legend_title = "Hour";
			$chart_y_legend_title = 'Temperature (C)';
		}
		
		$ystep_details = $this->calc_y_axis_values(0, $max_value, 5, 20);

		$chart = new GS_Chart($chart_title);
		$chart->set_x_axis_details($chart_x_legend_title, $labels);
		$chart->set_y_axis_details($chart_y_legend_title, $ystep_details[0], $ystep_details[1], $ystep_details[2]);
		$chart->add_line_dot_element($trunk_name1, $values, '#BB3939');
		//$chart->add_line_dot_element($trunk_name2, $values2);
		echo $chart->toPrettyString();
		exit;
	}
	
	function calc_y_axis_values($min, $max, $num_div, $plot_scale)
	{
		$max = ceil($max/$plot_scale);
		$max *= $plot_scale;
		$ysteps = $max/$num_div;
		
		return array($min, $max, $ysteps);
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
	
	function leapYear($year)
	{
		if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0)) return TRUE;
		return FALSE;
	}
	
}

?>