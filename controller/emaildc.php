<?php

class Emaildc extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MEmail.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$dc_model = new MEmail();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		
		$templateinfo = $skill_model->getSkillById($sid);
		
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . '&sid=' . $sid);
		
		$pagination->num_records = $dc_model->numDispositions($sid);
		$data['dispositions'] = $pagination->num_records > 0 ? 
			$dc_model->getDispositions($sid, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['dispositions']) ? count($data['dispositions']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['sid'] = $sid;
		$data['pageTitle'] = 'Email Disposition Code';
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
		
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'email_skills';		
		
		$data['disposition_options'] = $pagination->num_records > 0 ? $dc_model->getDispositionTreeOptions() : null;
		$data['dc_model'] = $dc_model;
		$this->getTemplate()->display('email_dispositions', $data); */
		
		
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';		
		$templateinfo = $skill_model->getSkillById($sid);		
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();
		
		$data['topMenuItems'] = array(
			array('href'=>'task=skills', 'img'=>'fa fa-tasks', 'label'=>'List Skill(s)'),
			array('href'=>'task=emaildc&act=add&sid='.$sid, 'img'=>'fa fa-plus-square', 'label'=>'Add New Disposition Code')
		);
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'email_skills';
		$data['sid'] = $sid;
		$data['pageTitle'] = 'Email Disposition Code';
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
		$data['dataUrl'] = $this->url('task=get-email-data&act=emaildsinit&sid='.$sid);
		$this->getTemplate()->display('email_dispositions', $data);
	}

	function actionRebuild()
	{
	
		include('model/MEmail.php');
		$dc_model = new MEmail();
		$dc_model->rebuildDispositionTree('', 0);
		exit;
	}
	
	function actionAdd()
	{
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$this->saveService($sid);
	}

	function actionUpdate()
	{
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$this->saveService($sid, $did);
	}

	function actionDel()
	{
		include('model/MEmail.php');
		$dc_model = new MEmail();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&sid=$sid&page=".$cur_page);
		
		if ($dc_model->deleteDispositionId($dcode, $sid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>false, 'msg'=>'Disposition Code Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>true, 'msg'=>'Failed to Delete  Disposition Code', 'redirectUri'=>$url));
		}
	}
	
	function saveService($sid, $dis_code='')
	{
		include('model/MEmail.php');
		$dc_model = new MEmail();
		include('model/MSkill.php');
		$skill_model = new MSkill();

		$templateinfo = $skill_model->getSkillById($sid);
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit;
		
		$request = $this->getRequest();
		$groups = null;
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService();
			
			//var_dump($service);
			//exit;
			//$groups = $dc_model->getDispositionGroupOptions($tid);
			$errMsg = $this->getValidationMsg($service, $sid, $dis_code, $dc_model);
			
			if (empty($errMsg)) {
				$is_success = false;
				
				/*
				$dis_group_id = '';
				if (!empty($service->group_id)) {
					$dis_group_id = $service->group_id;
				} else if (!empty($service->group_title)) {
					//if (in_array($service->group_title, $groups, true)) return "Disposition group $service->group_title already exist";
					$dis_group_id = $dc_model->addDispositionGroup($tid, $service->group_title);
				}
				
				$service->group_id = $dis_group_id;
				*/
				
				if (empty($dis_code)) {
					if ($dc_model->addService($sid, $service)) {
						$errMsg = 'Disposition code added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add disposition code !!';
					}
				} else {
					$oldservice = $this->getInitialService($sid, $dis_code, $dc_model);
					if ($dc_model->updateService($oldservice, $service)) {
						$errMsg = 'Disposition code updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&sid=$sid");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($sid, $dis_code, $dc_model);
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['dis_code'] = $dis_code;
		$data['sid'] = $sid;
		//$data['groups'] = empty($groups) ? $dc_model->getDispositionGroupOptions($tid) : $groups;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['disposition_type'] = get_disposition_type();

		if (empty($dis_code)) {
			$data['pageTitle'] = 'Add New Disposition Code';
			$data['dispositions'] = $dc_model->getDispositionTreeOptions($sid);
		} else {
			$data['pageTitle'] = 'Update Disposition Code'.' : ' . $dis_code;
			$data['dc_parent_path'] = $dc_model->getDispositionPath($dis_code);
		}
		
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;

		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'email_skills';
		$this->getTemplate()->display('email_disposition_form', $data);
	}
	
	function getInitialService($sid, $dis_code, $dc_model)
	{
		$service = new stdClass();

		if (empty($dis_code)) {
            $service->disposition_id = '';
            $service->disposition_type = '';
            $service->title = '';
            $service->parent_id = '';
		} else {
			$service = $dc_model->getDispositionById($dis_code, $sid);
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
		//if (!empty($dis_code)) $service->disposition_code = $dis_code;
		return $service;
	}

	function getValidationMsg($service, $sid, $dis_code='', $dc_model)
	{
		//if (empty($service->disposition_id)) return "Provide disposition code";
		if (empty($service->title)) return "Provide disposition title";
		if (empty($service->disposition_type)) return "Provide disposition type";
		//if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_id)) return "Provide valid disposition code";
		if (!preg_match("/^[0-9a-zA-Z_ ]{1,50}$/", $service->title)) return "Provide valid title";
		
		/*
		if ($service->disposition_id != $dis_code) {
			$existing_code = $dc_model->getDispositionById($service->disposition_id);
			if (!empty($existing_code)) return "Disposition code $service->disposition_id already exist";
		}
		
		
		if (!empty($service->group_id) && !empty($service->group_title)) {
			return "Please select only one option for disposition group";
		}
		
		if (!empty($service->group_id)) {
			if (!array_key_exists($service->group_id, $groups)) return "Invalid disposition group selected";
		} else if (!empty($service->group_title)) {
			if (in_array($service->group_title, $groups, true)) return "Disposition group $service->group_title already exist";
		}
		*/
		return '';
	}
	
}
