<?php

class Smstemplate extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
        $data['topMenuItems'] = array(array('href'=>'task=smstemplate&act=add', 'img'=>'fa fa-envelope-o', 'label'=>'Add New Template'));
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'smstemplate';
		$data['pageTitle'] = 'SMS Templates';
		$data['dataUrl'] = $this->url('task=get-email-data&act=smstemplate');
		$this->getTemplate()->display('sms_templates', $data);
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveService($tid);
	}
	
	function saveService($tstamp_code='')
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($tstamp_code);
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tstamp_code)) {
					if ($et_model->addSmsTemplate($service)) {
						$errMsg = 'Template added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add template !!';
					}
				} else {
					$oldtemplate = $this->getInitialService($tstamp_code, $et_model);
					if ($et_model->updateSmsTemplate($oldtemplate, $service)) {
						$errMsg = 'Template updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($tstamp_code, $et_model);
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = $tstamp_code;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['iscopy'] = false;
		$data['pageTitle'] = empty($tstamp_code) ? 'Add New Template' : 'Update Template';

		$data['side_menu_index'] = 'home';
		$data['smi_selection'] = 'smstemplate_';
		$this->getTemplate()->display('sms_template_form', $data);
	}
	
	function getInitialService($tstamp_code, $et_model)
	{
		$service = null;

		if (empty($tstamp_code)) {
			$service = new stdClass();
			$service->template_id = "";
			$service->tstamp = "";
			$service->title = "";
			$service->sms_body = "";
			$service->type = "I";
			$service->status = "Y";
		} else {
			$service = $et_model->getSmsTemplateById($tstamp_code);
		}
		return $service;
	}

	function getSubmittedService($tstamp_code)
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}
		
		return $service;
	}

	function getValidationMsg($service)
	{
		if (empty($service->template_id)) return "Provide template ID";
		if (!preg_match("/^([a-z0-9]+-)*[a-z0-9]+$/i", $service->template_id) ) return "Alpha numeric and dash are allowed for ID";
		if (strlen($service->template_id) > 10 ) return "Maximum 10 character allowed for ID";
		if (empty($service->title)) return "Provide template title";
		if (empty($service->sms_body)) return "Provide template text";
		if (empty($service->type)) return "Provide SMS template type";
		if (!in_array($service->type, ['I','C'])) return "Invalid SMS type.";

		return '';
	}
	
}
