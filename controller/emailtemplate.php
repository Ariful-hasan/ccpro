<?php

class Emailtemplate extends Controller
{
	function __construct() {
		parent::__construct();
		
		$licenseInfo = UserAuth::getLicenseSettings();
		if ($licenseInfo->email_module == 'N') {
			header("Location: ./index.php");
			exit;
		}
	}

	function init()
	{
		/* include('model/MEmailTemplate.php');
		include('lib/Pagination.php');
		$et_model = new MEmailTemplate();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $et_model->numTemplates();
		$data['emails'] = $pagination->num_records > 0 ? 
			$et_model->getTemplates($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['emails']) ? count($data['emails']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Email Templates';
		$data['side_menu_index'] = 'email';
		
		if ($pagination->num_records > 0) {
			include('model/MEmail.php');
			$data['email_model'] = new MEmail();
		} else {
			$data['email_model'] = null;
		}
		
		$data['topMenuItems'] = array(array('href'=>'task=emailtemplate&act=add', 'img'=>'fa-envelope-o', 'label'=>'Add New Template'));
		$this->getTemplate()->display('email_templates', $data);
		 */
		
        $data['topMenuItems'] = array(array('href'=>'task=emailtemplate&act=add', 'img'=>'fa fa-envelope-o', 'label'=>'Add New Template'));
		$data['side_menu_index'] = 'email';
		$data['pageTitle'] = 'Email Templates';
		$data['dataUrl'] = $this->url('task=get-email-data&act=emailtemplate');
		$this->getTemplate()->display('email_templates', $data);
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

	function actionDel()
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();

		$tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($et_model->deleteTemplate($tstamp)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>false, 'msg'=>' Template Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>true, 'msg'=>'Failed to Delete Template', 'redirectUri'=>$url));
		}
	}
	
	function actionMessage()
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		
		$tstamp = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data['service'] = "";
		$data['errMsg'] = "";
		$data['errType'] = 0;
		if (!empty($tstamp)){
			$data['service'] = $et_model->getTemplateById($tstamp);;
		}else {
			$data['errType'] = 1;
			$data['errMsg'] = "Email template not found!";
		}		
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Email Template Details';
		
		$this->getTemplate()->display_popup('email_template_details', $data);
	}
	
	function actionCopy()
	{
		$tstamp_code = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		include('model/MEmail.php');
		$email_model = new MEmail();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$tstamp_code = '';
			$service = $this->getSubmittedService($tstamp_code);
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tstamp_code)) {
					if ($et_model->addTemplate($service)) {
						$errMsg = 'Template added successfully !!';
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					} else {
						$errType = 1;
						$errMsg = 'Failed to add template !!';
					}
				}
			}			
		} else {
			$service = $this->getInitialService($tstamp_code, $et_model);
			if (empty($service)) {
				exit;
			}
		}
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = '';
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['iscopy'] = true;
		$data['pageTitle'] = 'Add New Template';
		//$data['dispositions'] = $email_model->getDispositionTreeOptions();
		$data['skill_id'] = $skill_model->getSkillField("skill_id", $service->disposition_id);
		$data['disposition_ids'] = $email_model->getDispositionPathArray($service->disposition_id);
		$data['dispositions0'] = $email_model->getDispositionChildrenOptions($data['skill_id'], '');
		$data['email_model'] = $email_model;
		
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'emailtemplate_';
		$this->getTemplate()->display('email_template_form', $data);
	}
	
	function saveService($tstamp_code='')
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		include('model/MEmail.php');
		$email_model = new MEmail();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($tstamp_code);
			$service->mail_body = !empty($service->mail_body) ? nl2br(base64_encode($service->mail_body)) : "";
			//var_dump($service);
			//exit;
			$errMsg = $this->getValidationMsg($service);

			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tstamp_code)) {
					if ($et_model->addTemplate($service)) {
						$errMsg = 'Template added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add template !!';
					}
				} else {
                    $oldtemplate = $this->getInitialService($tstamp_code, $et_model);
                    if ($et_model->updateTemplate($oldtemplate, $service)) {
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
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = $tstamp_code;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['iscopy'] = false;
		$data['pageTitle'] = empty($tstamp_code) ? 'Add New Template' : 'Update Template';
		//$data['dispositions'] = $email_model->getDispositionTreeOptions();
		$data['skill_id'] = $skill_model->getSkillField("skill_id", $service->disposition_id);
		$data['disposition_ids'] = $email_model->getDispositionPathArray($service->disposition_id);
		$data['dispositions0'] = $email_model->getDispositionChildrenOptions($data['skill_id'], '');
		$data['email_model'] = $email_model;
		
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'emailtemplate_';
		$this->getTemplate()->display('email_template_form', $data);
	}
	
	function getInitialService($tstamp_code, $et_model)
	{
		$service = null;

		if (empty($tstamp_code)) {
			$service = new stdClass();
			$service->tstamp = "";
			$service->title = "";
			$service->mail_body = "";
			$service->disposition_id = "";
			$service->status = "Y";
		} else {
			$service = $et_model->getTemplateById($tstamp_code);
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

		$did = '';
		for ($i=0;$i<=10; $i++) {
			$did1 = isset($_POST['disposition_id'.$i]) ? trim($_POST['disposition_id'.$i]) : '';
			if (!empty($did1)) $did = $did1;
				else break;
		}
		$service->disposition_id = $did;
		//echo $_POST['disposition_id0'];
		//echo $service->disposition_id;
		//exit();
		//if (!empty($tstamp_code)) $service->disposition_code = $tstamp_code;

		return $service;
	}

	function getValidationMsg($service)
	{
		//if (empty($service->disposition_code)) return "Provide disposition code";
		if (empty($service->title)) return "Provide template title";
		if (empty($service->mail_body)) return "Provide template text";
		//if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_code)) return "Provide valid disposition code";
		//if (!preg_match("/^[0-9a-zA-Z_ ]{1,40}$/", $service->service_title)) return "Provide valid service title";
		/*
		if ($service->disposition_code != $tstamp_code) {
			$existing_code = $ivr_model->getTemplateById($service->disposition_code);
			if (!empty($existing_code)) return "Disposition code $service->disposition_code already exist";
		}
		*/
		
		return '';
	}
	
}
