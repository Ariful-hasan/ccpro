<?php

class Calldetails extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		exit('Invalid Page');
	}

	/*
		agent log ....
	*/
	function actionAgent()
	{
		include('model/MReport.php');
		include('model/MAgent.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$agent_model = new MAgent();
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		/* Added By Sarwar*/
		$fullSkillData = isset($_REQUEST['fskill']) ? true : false;
		
		
		$errMsg = '';
		$calls = null;
		if (!empty($cid)) {
			$calls = $report_model->getAgentInboundLogByCallId($cid);			
			if (empty($calls)) $errMsg = 'No Record Found!';
		} else {
			$errMsg = 'Invalid Page!';
		}
		/* Added By Sarwar*/
		if($fullSkillData){
			$data['pageTitle'] = 'Call Details';
		}else{
			$data['pageTitle'] = 'Call Details - Agent';
		}

		$data['calls'] = $calls;
		$data['errMsg'] = $errMsg;
		$this->getTemplate()->display_popup('report_call_details_agent', $data);
	}
	

}
