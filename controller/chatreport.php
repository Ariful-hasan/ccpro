<?php

class Chatreport extends Controller
{
	function __construct() {
		parent::__construct();
		
		$licenseInfo = UserAuth::getLicenseSettings();
		if ($licenseInfo->chat_module == 'N') {
			header("Location: ./index.php");
			exit;
		}
	}

	function init()
	{
	    include('model/MSkillCrmTemplate.php');
	    $template_model = new MSkillCrmTemplate();
	    $data['pageTitle'] = 'Chat Disposition Log';
	    $data['side_menu_index'] = 'chat';
	    $data['smi_selection'] = 'chatreport_';
	    $data['dp_options'] = $template_model->getDisposChatSelectOptions(true);
	    $data['dataUrl'] = $this->url('task=get-chat-data&act=chatreport');
	    $this->getTemplate()->display('chat_report_log', $data);
	}
	
	function actionRecordcount()
	{
	    include('model/MSkillCrmTemplate.php');
	    $template_model = new MSkillCrmTemplate();
	    $data['pageTitle'] = 'Chat Disposition';
	    $data['side_menu_index'] = 'chat';
	    $data['smi_selection'] = 'chatreport_recordcount';
	    $data['dp_options'] = $template_model->getDisposChatSelectOptions(true);
	    $data['dataUrl'] = $this->url('task=get-chat-data&act=chat-record-count');
	    $this->getTemplate()->display('chat_report_record_count', $data);
	}
	
	function actionAvgcount()
	{
	    include('model/MSkillCrmTemplate.php');
	    $template_model = new MSkillCrmTemplate();
	    $data['pageTitle'] = 'Chat Time Track';
	    $data['side_menu_index'] = 'chat';
	    $data['smi_selection'] = 'chatreport_avgcount';
	    $data['dataUrl'] = $this->url('task=get-chat-data&act=chat-avg-count');
	    $this->getTemplate()->display('chat_report_time_track', $data);
	}
	
	function actionRating()
	{
	    include('model/MChatTemplate.php');
	    $template_model = new MChatTemplate();
	    $data['pageTitle'] = 'Chat Ratings';
	    $data['side_menu_index'] = 'chat';
	    $data['smi_selection'] = 'chatreport_rating';
	    $data['dp_options'] = $template_model->getAgentsChatSkill(true);
	    $data['dataUrl'] = $this->url('task=get-chat-data&act=chat-agent-rating');
	    $this->getTemplate()->display('chat_report_rating', $data);
	}
}
