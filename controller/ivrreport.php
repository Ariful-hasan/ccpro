<?php

class Ivrreport extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()	{
	}
	
	
	function actionServicelog()
	{
	    include('lib/DateHelper.php');
	    include('model/MIvrService.php');
	    AddModel('MIvr');
	    $ivr_model = new MIvrService();
	    $ivr = new MIvr();
	    
	    if (isset($_REQUEST['download'])){
	        include('model/MIvrServiceReport.php');
	        $report_model = new MIvrServiceReport();
	        $data['report_model'] = $report_model;
	    }
	    
	    $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
	    $clid = isset($_REQUEST['clid']) ? trim($_REQUEST['clid']) : '';
	    $alid = isset($_REQUEST['alid']) ? trim($_REQUEST['alid']) : '';
	    $dateinfo = DateHelper::get_input_time_details(true);
	    
	    $data['dateinfo'] = $dateinfo;
	    
	    //print_r($data['dateinfo']);
	    
	    $data['dcode'] = $dcode;
	    $data['clid'] = $clid;
	    $data['alid'] = $alid;
	    $data['ivrs'] = array_merge(['*'=>'All'], $ivr->getIvrOptions());
	    $data['dp_options'] = $ivr_model->getServiceOptions('', true);
	    $data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
	    $data['dataUrl'] = $this->url('task=get-ivr-report-data&act=servicelog');
	    $data['pageTitle'] = 'IVR Service Request Log';
	    $this->getTemplate()->display('report_ivr_service', $data);
	}

	function actionServiceSummary()
	{
	    include('model/MIvrService.php');
	    $ivr_model = new MIvrService();

	    $data['dp_options'] = $ivr_model->getServiceOptions('', true);
	    $data['dataUrl'] = $this->url('task=get-ivr-report-data&act=servicesummary');
	    $data['pageTitle'] = 'IVR Service Summary';
	    $data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_ivr_service_summary', $data);
	}
	
	function actionServicereq()
	{
	    /* include('model/MIvrService.php');
	    $ivr_model = new MIvrService();
	    
	    $data['pageTitle'] = 'IVR Service Request';
	    $data['dp_options'] = $ivr_model->getServiceOptions('', true);
		$data['side_menu_index'] = 'reports';
	    $data['dataUrl'] = $this->url('task=get-ivr-report-data&act=servicereq');
	    $this->getTemplate()->display('report_ivr_service_request', $data); */
	    
	    include('model/MIvrServiceReport.php');
		include('model/MIvrService.php');
		include('model/MAgent.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');

		$report_model = new MIvrServiceReport();
		$ivr_model = new MIvrService();
		$agent_model = new MAgent();
		$pagination = new Pagination();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$dateinfo = DateHelper::get_input_time_details(true);
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&dcode=$dcode&sdate=$dateinfo->sdate&edate=$dateinfo->edate&page=");
		$data['pageTitle'] = 'IVR Service Request';// . date("M d, Y", $dateinfo->ststamp);
		
		$current_agent = UserAuth::getCurrentUser();
		$skills = $agent_model->getAgentSkill($current_agent);
		
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numSurviceRequest($dateinfo, $dcode, $skills);
		}
		
		if (!isset($_REQUEST['download'])) $data['logs'] = $pagination->num_records > 0 ? 
			$report_model->getServiceRequest($dateinfo, $dcode, $pagination->getOffset(), $pagination->rows_per_page, $skills) : null;
		
		$pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['dcode'] = $dcode;
		$data['request'] = $this->getRequest();
		$data['dp_options'] = $ivr_model->getServiceOptions();
		//$data['report_model'] = $report_model;
		//$data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
		$data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
		$this->getTemplate()->display('report_ivr_service_request', $data);
	}
	
	function actionMarkservice()
	{
		include('model/MIvrServiceReport.php');
		include('lib/DateHelper.php');

		$report_model = new MIvrServiceReport();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		$dateinfo = DateHelper::get_input_time_details(true);
		
		if (isset($_POST['sreqs']) && is_array($_POST['sreqs'])) {
			$counter = 0;
			$counter = $report_model->markServices(UserAuth::getCurrentUser(), $_POST['sreqs']);
			$rurl = "index.php?task=ivrreport&act=servicereq&dcode=$dcode&sdate=$dateinfo->sdate&edate=$dateinfo->edate&page=$pageNum";
			if ($counter > 0) {
				$this->getTemplate()->display('msg', array('pageTitle'=>"IVR Service Request", 'isError'=>false, 'msg'=>$counter . ' request(s) added successfully', 'redirectUri'=>$rurl));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>"IVR Service Request", 'isError'=>true, 'msg'=>'Failed to add request', 'redirectUri'=>$rurl));
			}
		}
	}

	function actionIvrTraceLogReport()
	{
	    include('lib/DateHelper.php');	    
	    $dateinfo = DateHelper::get_input_time_details(true);
	    
	    $data['dateinfo'] = $dateinfo;
	    $data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
	    $data['dataUrl'] = $this->url('task=get-report-data&act=report-ivr-trace-log');
	    $data['pageTitle'] = 'Ivr Trace Log Report';
	    $this->getTemplate()->display('report_ivr_trace_log', $data);
	}

	function actionIvrTraceSummaryReport()
	{
	    include('lib/DateHelper.php');	    
	    include('model/MIvrService.php');
	    $ivr_model = new MIvrService();
	    $dateinfo = DateHelper::get_input_time_details(true);
	    
	    $data['dateinfo'] = $dateinfo;
	    $data['dp_options'] = $ivr_model->getServiceOptions('', true);
	    $data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
	    $data['dataUrl'] = $this->url('task=get-report-data&act=report-ivr-trace-summary');
	    $data['pageTitle'] = 'Ivr Trace Summary Report';
	    $this->getTemplate()->display('report_ivr_trace_summary', $data);
	}
	function actionIvrCallSummary()
	{
	    include('lib/DateHelper.php');
        include('model/MIvr.php');
        $ivr_model = new MIvr();
        $data['ivrNames'] = array("*"=>"All") + $ivr_model->getIvrOptions();
	    $dateinfo = DateHelper::get_input_time_details(true);
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list(array(REPORT_15_MIN_INV,REPORT_HALF_HOURLY));
	    
	    $data['dateinfo'] = $dateinfo;
	    $data['report_type'] = !empty($_REQUEST['report_type']) ? $_REQUEST['report_type'] : REPORT_HOURLY;
	    $data['ivr_id'] = !empty($_REQUEST['ivr_id']) ? $_REQUEST['ivr_id'] : '';
	    $data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
	    $data['dataUrl'] = $this->url('task=get-report-data&act=report-ivr-call-summary');
	    $data['pageTitle'] = 'Ivr Call Summary';
	    $this->getTemplate()->display('report_ivr_call_summary', $data);
	}
}
