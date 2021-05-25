<?php

class Smsreport extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionSmssummary();
	}
	
	function actionSmssummary()
	{
	    include('model/MSkill.php');
	    include('model/MAgent.php');
	    $skill_model = new MSkill();
	    $agent_model = new MAgent();

        $data['pageTitle'] = 'SMS Sending Report';
        $data['side_menu_index'] = 'reports';
        $data['skills'] = array_merge(array("*" => "All"),$skill_model->getSkillsNamesArray());
        $data['agents'] = array("*" => "All") + $agent_model->get_as_key_value();
        $data['dataUrl'] = $this->url('task=get-report-data&act=smssummary');
        $this->getTemplate()->display('report_sms_summary', $data);
	}

}
