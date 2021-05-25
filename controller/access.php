<?php

class Access extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionAudit();
	}
	
	function actionAudit()
	{
		include('model/MAgent.php');
		include('lib/DateHelper.php');
		$agent_model = new MAgent();
		$data['pageTitle'] = 'Activity Report';

		$dateinfo = DateHelper::get_input_time_details();
		$user = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		$data['dataUrl'] = $this->url('task=get-report-data&act=reportaudit');
		$selectArr = array('*'=>'Select');
		$data['agent_options'] = $selectArr + $agent_model->getAgentNames('', '', 0, 0, 'array');
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_audit', $data);
	}
	
	function actionLogin()
	{
		include('model/MReport.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();
		$user = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&user=$user&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime");

		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getLoginRecords($user, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;

		$pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=login&user='.$user);
		
		//$data['report_model'] = $report_model;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Login Activity Report';
		$data['reportHeader'] = true;
		$data['user'] = $user;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_login', $data);
	}

	function actionPrivilege()
	{
		$data['pageTitle'] = 'Access Privilege';
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('access_privilege', $data);
	}
	
	function downloadActivity($report_model, $title, $template, $agentid, $dateinfo)
	{
	    $file_name = $report_model->getCdrCsvSavePath() . 'agent_activity_report.csv';

		$is_success = $report_model->prepareActivityRptFile($agentid, $dateinfo, $file_name);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			$dl_helper->download_file('', "SL,Date time,User,Action,Section,IP,Log\n");
		}
	}
}
