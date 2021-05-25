<?php

class Emaildscopy extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MSkill.php');

		$data['sid'] = "";
		$data['copyText'] = isset($_REQUEST['cptxt']) ? trim(urldecode($_REQUEST['cptxt'])) : '';
		$data['disposId'] = isset($_REQUEST['did']) ? trim(urldecode($_REQUEST['did'])) : '';
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
		}

		$data['pagepart'] = 'skills';
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Copy Email Disposition Code with Template';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}

	function actionDispositions()
	{
		include('model/MEmail.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$dc_model = new MEmail();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		
		$sid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		$data['disposId'] = isset($_REQUEST['did']) ? trim(urldecode($_REQUEST['did'])) : '';
		$data['copyText'] = isset($_REQUEST['cptxt']) ? trim(urldecode($_REQUEST['cptxt'])) : '';
		$data['copyToTxt'] = '';
		$templateinfo = $skill_model->getSkillById($sid);
		
		if (!empty($templateinfo) && $templateinfo->qtype == 'E'){
			$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . '&act=dispositions');
			$pagination->base_link .= "&skillid=". $sid ."&did=". $data['disposId'] ."&cptxt=".urlencode($data['copyText']);
			$pagination->num_records = $dc_model->numDispositions($sid);
			$pagination->rows_per_page = 10;
			$data['dispositions'] = $pagination->num_records > 0 ? 
				$dc_model->getDispositions($sid, $pagination->getOffset(), $pagination->rows_per_page) : null;
			$pagination->num_current_records = is_array($data['dispositions']) ? count($data['dispositions']) : 0;
			$data['pagination'] = $pagination;
			$data['sid'] = $sid;
			$data['pageTitle'] = 'Copy Email Disposition Code with Template';
			$data['tabTitle'] = 'Disposition List';
			$data['tabTitle'] .= ' of Skill - ' . $templateinfo->skill_name;
			$data['copyToTxt'] = $templateinfo->skill_name;			
			$data['disposition_options'] = $pagination->num_records > 0 ? $dc_model->getDispositionTreeOptions() : null;
			$data['dc_model'] = $dc_model;
		}
		$data['request'] = $this->getRequest();
		$data['pagepart'] = 'dispositions';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}
	
	function actionConfirm()
	{		
		$data['sid'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$data['didTo'] = isset($_REQUEST['didto']) ? trim(urldecode($_REQUEST['didto'])) : '';
		$data['didFrom'] = isset($_REQUEST['didfrom']) ? trim(urldecode($_REQUEST['didfrom'])) : '';
		$data['copyFromTxt'] = isset($_REQUEST['cpfrom']) ? trim(urldecode($_REQUEST['cpfrom'])) : '';
		$data['copyToTxt'] = isset($_REQUEST['cp2txt']) ? trim(urldecode($_REQUEST['cp2txt'])) : '';
		$data['disposId'] = "";
		$data['copyText'] = "";
		
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Copy Email Disposition Code with Template';
		$data['pagepart'] = 'confirmation';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}
	
	function actionConfirm2Skill()
	{		
		$data['sid'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$data['didTo'] = '';
		$data['didFrom'] = isset($_REQUEST['didfrom']) ? trim(urldecode($_REQUEST['didfrom'])) : '';
		$data['copyFromTxt'] = isset($_REQUEST['cpfrom']) ? trim(urldecode($_REQUEST['cpfrom'])) : '';
		$data['copyToTxt'] = isset($_REQUEST['cp2txt']) ? trim(urldecode($_REQUEST['cp2txt'])) : '';
		$data['disposId'] = "";
		$data['copyText'] = "";
		
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Copy Email Disposition Code with Template';
		$data['pagepart'] = 'confirm2skill';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}
	
	function actionCopynow()
	{
		$data['sid'] = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		$data['didFrom'] = isset($_REQUEST['didfrom']) ? trim(urldecode($_REQUEST['didfrom'])) : '';
		$data['didTo'] = isset($_REQUEST['didto']) ? trim(urldecode($_REQUEST['didto'])) : '';
		$data['copyFromTxt'] = isset($_REQUEST['cpfrom']) ? trim(urldecode($_REQUEST['cpfrom'])) : '';
		$data['copyToTxt'] = isset($_REQUEST['cp2txt']) ? trim(urldecode($_REQUEST['cp2txt'])) : '';
		$data['disposId'] = "";
		$data['copyText'] = "";
		$errMsg = '';
		$errType = 1;
		
		include('model/MEmail.php');
		$dc_model = new MEmail();

		if ($dc_model->copyDisposition($data['didFrom'], $data['didTo'], $data['sid'])){
			$errType = 0;
			$errMsg = 'Email disposition code copy successful.';
		}else {
			$errType = 1;
			$errMsg = 'Failed to copy email disposition code!!';
		}
		$data['errType'] = $errType;
		$data['errMsg'] = $errMsg;		
		$data['pagepart'] = 'copy';
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Copy Email Disposition Code with Template';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}
	
	function actionCopytoskill()
	{
		$data['sid'] = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		$data['didFrom'] = isset($_REQUEST['didfrom']) ? trim(urldecode($_REQUEST['didfrom'])) : '';
		$data['copyFromTxt'] = isset($_REQUEST['cpfrom']) ? trim(urldecode($_REQUEST['cpfrom'])) : '';
		$data['copyToTxt'] = isset($_REQUEST['cp2txt']) ? trim(urldecode($_REQUEST['cp2txt'])) : '';
		$data['disposId'] = "";
		$data['copyText'] = "";
		$errMsg = '';
		$errType = 1;
		
		include('model/MEmail.php');
		$dc_model = new MEmail();

		if ($dc_model->copyDisposition2Skill($data['didFrom'], $data['sid'])){
			$errType = 0;
			$errMsg = 'Email disposition code copy successful.';
		}else {
			$errType = 1;
			$errMsg = 'Failed to copy email disposition code!!';
		}
		$data['errType'] = $errType;
		$data['errMsg'] = $errMsg;		
		$data['pagepart'] = 'copy';
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Copy Email Disposition Code with Template';
		$this->getTemplate()->display_popup('email_dispos_copy', $data);
	}
}
