<?php

class Scrmdg extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MSkillCrmTemplate.php');
		include('lib/Pagination.php');
		$dc_model = new MSkillCrmTemplate();
		$pagination = new Pagination();
		
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';		
		$templateinfo = $dc_model->getTemplateById($tid);
		
		if (empty($templateinfo)) exit;
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . '&tid=' . $tid);
		
		$pagination->num_records = $dc_model->numDispositionGroups($tid);
		$data['dispositions'] = $pagination->num_records > 0 ? 
			$dc_model->getDispositionGroups($tid, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['dispositions']) ? count($data['dispositions']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['tid'] = $tid;
		$data['pageTitle'] = 'Skill CRM Service Types';
		$data['pageTitle'] .= ' for Template - ' . $templateinfo->title;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'stemplate_';

		$data['topMenuItems'] = array(
			array('href'=>'task=stemplate', 'img'=>'fa fa-tasks', 'label'=>'List Template(s)'),
			array('href'=>'task=scrmdg&act=add&tid='.$tid, 'img'=>'fa fa-plus-square-o', 'label'=>'Add New Service Type')
		);
		
		$this->getTemplate()->display('skill_crm_disposition_groups', $data); */

		include('model/MSkillCrmTemplate.php');
		$dc_model = new MSkillCrmTemplate();
		
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';		
		$templateinfo = $dc_model->getTemplateById($tid);
		
		if (empty($templateinfo)) exit;
		$data['dataUrl'] = $this->url('task=get-tools-data&act=scrmdginit&tid='.$tid);
		$data['pageTitle'] = 'Skill CRM Service Types';
		$data['pageTitle'] .= ' for Template - ' . $templateinfo->title;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'stemplate_';
		
		$data['topMenuItems'] = array(
		    array('href'=>'task=stemplate', 'img'=>'fa fa-tasks', 'label'=>'List Template(s)'),
		    array('href'=>'task=scrmdg&act=add&tid='.$tid, 'img'=>'fa fa-plus-square-o', 'label'=>'Add New Service Type')
		);
		
		$this->getTemplate()->display('skill_crm_disposition_groups', $data);
	}

	function actionAdd()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveService($tid);
	}

	function actionUpdate()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';
		$this->saveService($tid, $gid);
	}
/*
	function actionDel()
	{
		include('model/MSkillCrmTemplate.php');
		$dc_model = new MSkillCrmTemplate();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&tid=$tid&page=".$cur_page);
		
		if ($dc_model->deleteDispositionId($dcode, $tid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>false, 'msg'=>'Disposition Code Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>true, 'msg'=>'Failed to Delete  Disposition Code', 'redirectUri'=>$url));
		}
	}
	*/
	
	function saveService($tid, $dis_code='')
	{
		include('model/MSkillCrmTemplate.php');
		$dc_model = new MSkillCrmTemplate();

		$templateinfo = $dc_model->getTemplateById($tid);
		if (empty($templateinfo)) exit;
		
		$request = $this->getRequest();
		$groups = null;
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService();			
			//var_dump($service);
			//exit;
			$errMsg = $this->getValidationMsg($service, $tid, $dis_code, $dc_model);
			
			if (empty($errMsg)) {
				$is_success = false;

				if (empty($dis_code)) {
					if ($dc_model->addDispositionGroup($tid, $service->title, $service->status_ticketing, $service->from_email_name, $service->to_email_name, $service->use_email_module)) {
						$errMsg = 'Service type added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add service type !!';
					}
				} else {
					$oldservice = $this->getInitialService($tid, $dis_code, $dc_model);
					if ($dc_model->updateDispositionGroup($oldservice, $service)) {
						$errMsg = 'Service type updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&tid=$tid");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($tid, $dis_code, $dc_model);
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['dis_code'] = $dis_code;
		$data['tid'] = $tid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($dis_code) ? 'Add New Service Type' : 'Update Service Type';
		$data['pageTitle'] .= ' for Template - ' . $templateinfo->title;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'stemplate_';
		$this->getTemplate()->display('skill_crm_disposition_group_form', $data);
	}
	
	function getInitialService($tid, $dis_code, $dc_model)
	{
		$service = new stdClass();

		if (empty($dis_code)) {
			$service->title = '';
			$service->service_type_id = '';
			$service->status_ticketing = 'N';
			$service->from_email_name = '';
			$service->to_email_name = '';
			$service->use_email_module = 'N';
		} else {
			$service = $dc_model->getDispositionGroupById($dis_code, $tid);
		}
		return $service;
	}

	function getSubmittedService()
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
			    $value = str_replace(array("\\", "\0", "\n", "\r", "'", '"', "\x1a"), '', $val);
				$service->$key = trim($value);
			}
		}

		//if (!empty($dis_code)) $service->disposition_code = $dis_code;

		return $service;
	}

	function getValidationMsg($service, $tid, $dis_code='', $dc_model)
	{
		if (empty($service->title)) return "Provide group title";
		if (!preg_match("/^[0-9a-zA-Z_ ]{1,40}$/", $service->title)) return "Provide valid title";
		
		if ($service->status_ticketing == 'Y'){
		    if (empty($service->from_email_name) || empty($service->to_email_name)){
		        return "From Email and To Email must not be empty for ticketing service";
		    }
		}
		
		$fromEmail = $service->from_email_name;
		if (!empty($fromEmail)){
		    $fromEmailArr = explode(",", $fromEmail);
		    if (count($fromEmailArr) > 1){
		        return "Multiple email address not allowed in from email address";
		    }else {
		        $startPos = strpos($fromEmail, "<");
		        $endPos = strpos($fromEmail, ">");
		        if ($startPos !== false && $endPos !== false) {
		            $startPos = $startPos + 1;
		            $email = substr($fromEmail, $startPos, ($endPos - $startPos));
		            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Provide valid to email address";
		        }
		    }
		}
		$toEmail = $service->to_email_name;
		if (!empty($toEmail)){
		    $toEmailArr = explode(",", $toEmail);
		    foreach ($toEmailArr as $email){
		        $startPos = strpos($email, "<");
		        $endPos = strpos($email, ">");
		        if ($startPos !== false && $endPos !== false) {
		            $startPos = $startPos + 1;
		            $email = substr($email, $startPos, ($endPos - $startPos));
		            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Provide valid to email address";
		        }else {
		            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Provide valid to email address";
		        }
		    }
		}
				
		return '';
	}
	
}
