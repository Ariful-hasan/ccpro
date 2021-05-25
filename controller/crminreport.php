<?php

class Crminreport extends Controller
{
	function __construct()
    {
		parent::__construct();
	}

	function init()
	{
	    include('model/MSkillCrmTemplate.php');
	    $template_model = new MSkillCrmTemplate();
	     
	    $data['template_options'] = $template_model->getTemplateSelectOptions(true);
		$data['dp_options'] = $template_model->getDispositionSelectOptions(true);
		$data['pageTitle'] = 'Skill CRM Log';
		$data['side_menu_index'] = 'reports';
		$data['dataUrl'] = $this->url('task=get-report-data&act=crmreport_init');
		$this->getTemplate()->display('crm_in_report_log', $data);
	}
	
	function actionRecordcount()
	{
		include('model/MCrmIn.php');
		include('model/MSkillCrmTemplate.php');
		include('lib/DateHelper.php');
		include('lib/Pagination.php');
		$crm_model = new MCrmIn();
		$template_model = new MSkillCrmTemplate();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();
		$tempid = isset($_REQUEST['tempid']) ? trim($_REQUEST['tempid']) : "";
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . 
			"&act=recordcount&tempid=$tempid&sdate=$dateinfo->sdate&edate=$dateinfo->edate&".
			"stime=$dateinfo->stime&etime=$dateinfo->etime");
		
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
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $crm_model->numCrmRecordCount($tempid, $dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (!isset($_REQUEST['download'])) $data['records'] = $pagination->num_records > 0 ? 
			$crm_model->getCrmRecordCount($tempid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		else $data['crm_model'] = $crm_model;
		$pagination->num_current_records = isset($data['records']) && is_array($data['records']) ? count($data['records']) : 0;

		$data['pagination'] = $pagination;
		$data['template_options'] = $template_model->getTemplateSelectOptions();

		if ($pagination->num_records > 0) {
			$data['dp_options'] = $template_model->getDispositionSelectOptions();
			$data['total_crm_records'] = $crm_model->getTotalCrmRecordCount($tempid, $dateinfo);
		}
		
		$data['tempid'] = $tempid;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill CRM Disposition';
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('crm_in_report_record_count', $data);
	}
	

}
