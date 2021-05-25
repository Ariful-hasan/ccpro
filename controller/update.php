<?php

class Update extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	}
	
	function actionAgentcall()
	{
		include('model/MReportUpdate.php');
		include('model/MReport.php');
		include('lib/DateHelper.php');
		$report_data_model = new MReport();
		$report_model = new MReportUpdate();

		$report_time = strtotime("-1 day");
		$report_day = date("Y-m-d", $report_time);
		//$report_day = '2012-03-18';
		$cur_year = date("y");
		$report_year = date("y");
		$report = 'AgentCallInbound';
		
		$start_tstamp = strtotime($report_day.' 00:00:00');
		$end_tstamp = strtotime($report_day.' 23:59:59');

		if ($cur_year != $report_year) {
			$isReportExist = $report_model->isTableExist($report, $report_year);
		} else {
			$isReportExist = $report_model->isReportExist($report, $start_tstamp, $end_tstamp);
		}
		
		
		if (!$isReportExist) {

			$agents = $report_model->getSessionAgents($start_tstamp, $end_tstamp);
			$logs_raw = $report_data_model->getAgentCallSourceLog($report_day, $report_day, '00:00', '23:59');
			$logs = array();
			if (is_array($logs_raw)) {
				foreach ($logs_raw as $log) {
					$key = $log->agent_id . '_' . $log->skill_id . '_' . substr($log->cdate, 5, 2) . '_' . substr($log->cdate, 8, 2) . '_' . $log->hour . '_' . sprintf("%02d", $log->minute);
					$logs[$key] = $log;
				}
			}
			$day = date("d", $start_tstamp);
			$month = date("m", $start_tstamp);

			foreach ($agents as $agentid) {

				$skills = $report_model->getAgentSkills($agentid);
				for ($tstamp=$start_tstamp; $tstamp<=$end_tstamp; $tstamp+=1800) {
					foreach ($skills as $skillid) {

						$min = date("i", $tstamp);
						$hour = date("H", $tstamp);
						$key = $agentid . '_' . $skillid . '_' . $month . '_' . $day . '_' . $hour . '_' . $min;
						$call = isset($logs[$key]) ? $logs[$key] : null;
						$report_model->addAgentCall($agentid, $skillid, $tstamp, $call);

					}
				}
			}
		}

		if ($cur_year != $report_year) {
			if (!$isReportExist) {
				$report_model->backupYearReport($report, $report_year);
			}
		}
	}


	function actionSkillreport()
	{
		include('model/MReportUpdate.php');
		include('model/MReport.php');
		include('lib/DateHelper.php');
		$report_data_model = new MReport();
		$report_model = new MReportUpdate();

		$report_time = strtotime("-1 day");
		$report_day = date("Y-m-d", $report_time);
		//$report_day = '2012-03-18';
		$cur_year = date("y");
		$report_year = date("y");
		$report = 'SkillReportInbound';
		
		$start_tstamp = strtotime($report_day.' 00:00:00');
		$end_tstamp = strtotime($report_day.' 23:59:59');

		if ($cur_year != $report_year) {
			$isReportExist = $report_model->isTableExist($report, $report_year);
		} else {
			$isReportExist = $report_model->isReportExist($report, $start_tstamp, $end_tstamp);
		}
		
		
		if (!$isReportExist) {

			$skills = $report_model->getInboundSkills();
			$logs_raw = $report_data_model->getSkillSourceLog($report_day, $report_day, '00:00', '23:59');
			$logs = array();
			if (is_array($logs_raw)) {
				foreach ($logs_raw as $log) {
					$key = $log->skill_id . '_' . substr($log->cdate, 5, 2) . '_' . substr($log->cdate, 8, 2) . '_' . $log->hour . '_' . sprintf("%02d", $log->minute);
					$logs[$key] = $log;
				}
			}
			$day = date("d", $start_tstamp);
			$month = date("m", $start_tstamp);

			foreach ($skills as $skillid) {

				for ($tstamp=$start_tstamp; $tstamp<=$end_tstamp; $tstamp+=1800) {

					$min = date("i", $tstamp);
					$hour = date("H", $tstamp);
					$key = $skillid . '_' . $month . '_' . $day . '_' . $hour . '_' . $min;
					$call = isset($logs[$key]) ? $logs[$key] : null;
					$report_model->addSkillReport($skillid, $tstamp, $call);
				}
			}
		}

		if ($cur_year != $report_year) {
			if (!$isReportExist) {
				$report_model->backupYearReport($report, $report_year);
			}
		}
	}

	function actionAgenttime()
	{
		include('model/MReportUpdate.php');
		include('model/MReport.php');
		include('lib/DateHelper.php');
		$report_data_model = new MReport();
		$report_model = new MReportUpdate();

		$report_time = strtotime("-1 day");
		$report_day = date("Y-m-d", $report_time);
		//$report_day = '2012-03-18';
		$cur_year = date("y");
		$report_year = date("y");
		$report = 'AgentTime';
		
		$start_tstamp = strtotime($report_day.' 00:00:00');
		$end_tstamp = strtotime($report_day.' 23:59:59');

		if ($cur_year != $report_year) {
			$isReportExist = $report_model->isTableExist($report, $report_year);
		} else {
			$isReportExist = $report_model->isReportExist($report, $start_tstamp, $end_tstamp);
		}
		
		
		if (!$isReportExist) {

			$agents = $report_model->getSessionAgents($start_tstamp, $end_tstamp);

			$_dateinfo = new stdClass;
			$_dateinfo->sdate = $_dateinfo->edate = date("Y-m-d", $start_tstamp);

			foreach ($agents as $agentid) {

				for ($tstamp=$start_tstamp; $tstamp<=$end_tstamp; $tstamp+=1800) {

					$_dateinfo->stime = date("H:i", $tstamp);
					$_dateinfo->etime = date("H:i", $tstamp+1799);
					$agentinfo = $report_data_model->getAgentSessionInfo($agentid, $_dateinfo);
					
					$report_model->addAgentTime($agentid, $tstamp, $agentinfo);
				}

			}
		}

		if ($cur_year != $report_year) {
			if (!$isReportExist) {
				$report_model->backupYearReport($report, $report_year);
			}
		}
	}

}
