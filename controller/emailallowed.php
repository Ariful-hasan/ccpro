<?php

class Emailallowed extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MEmail.php');
		include('lib/Pagination.php');
		include('model/MSkill.php');
		$skill_model = new MSkill();
		$email_model = new MEmail();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $email_model->numAllowedEmails();
		$data['services'] = $pagination->num_records > 0 ? 
			$email_model->getAllowedEmails($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['services']) ? count($data['services']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'CC/BCC Emails';
		$data['side_menu_index'] = 'email';
		$data['topMenuItems'] = array(array('href'=>'task=emailallowed&act=add', 'img'=>'add.png', 'label'=>'Add New Email'));
		$data['skills'] = $skill_model->getEmailSkillOptions();
		$this->getTemplate()->display('allowed_emails', $data);
		 */
		
		$data['pageTitle'] = 'CC/BCC Emails';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=emailallowed&act=add', 'img'=>'fa fa-plus-square', 'label'=>'Add New Email'));
		$data['dataUrl'] = $this->url('task=get-email-data&act=emailallowedinit');
		$this->getTemplate()->display('allowed_emails', $data);
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$eid = isset($_REQUEST['eid']) ? trim($_REQUEST['eid']) : '';
		$this->saveService($eid);
	}

	function actionDel()
	{
		include('model/MEmail.php');
		$email_model = new MEmail();

		$eid = isset($_REQUEST['eid']) ? trim($_REQUEST['eid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($email_model->deleteAllowedEmail($eid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete CC/BCC Email', 'isError'=>false, 'msg'=>'CC/BCC Email Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete CC/BCC Email', 'isError'=>true, 'msg'=>'Failed to Delete CC/BCC Email', 'redirectUri'=>$url));
		}
	}
	
	function saveService($email='')
	{
		include('model/MEmail.php');
		$email_model = new MEmail();
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService();
			
			//var_dump($service);
			//exit;
			$errMsg = $this->getValidationMsg($service, $email, $email_model);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($email)) {
					if ($email_model->addAllowedEmail($service)) {
						$errMsg = 'Email added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add email !!';
					}
				} else {
					$oldservice = $this->getInitialService($email, $email_model);
					if ($email_model->updateAllowedEmail($oldservice, $service)) {
						$errMsg = 'Email updated successfully !!';
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
			$service = $this->getInitialService($email, $email_model);
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['email'] = $email;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['skills'] = $skill_model->getEmailSkillOptions();
		$data['pageTitle'] = empty($email) ? 'Add New Email' : 'Update Email'.' : ' . $email;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'emailallowed_';
		$this->getTemplate()->display('allowed_email_form', $data);
	}
	
	function getInitialService($email, $email_model)
	{
		$service = new stdClass();

		if (empty($email)) {
			$service->name = "";
			$service->email = "";
			$service->skill_id = "";
			$service->status = "Y";
		} else {
			$service = $email_model->getAllowedEmail($email);
		}
		return $service;
	}

	function getSubmittedService()
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

	function getValidationMsg($service, $email='', $email_model)
	{
		if (empty($service->email)) return "Provide email";
		if (empty($service->name)) return "Provide name";
		//if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_code)) return "Provide valid disposition code";
		if (!filter_var($service->email, FILTER_VALIDATE_EMAIL)) return "Provide valid email";
		if (!preg_match("/^[0-9a-zA-Z_ .]{1,40}$/", $service->name)) return "Provide valid name";
		
		if ($service->email != $email) {
			$existing_code = $email_model->getAllowedEmail($service->email);
			if (!empty($existing_code)) return "Email $service->email already exist";
		}
		
		return '';
	}
	
}
