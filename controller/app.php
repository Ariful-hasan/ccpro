<?php

class App extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MMobileApp.php');
		$app_model = new MMobileApp();
		
		$data['app_requests'] = $app_model->getAvailableRequests();
		$data['fields'] = $app_model->getRequestFields();
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'App Configuration';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'app_';
		
		$this->getTemplate()->display('app_configuration_home', $data);
	}
	
	function actionNewRequest()
	{
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$csection = null;
		$fields = null;
		if (!empty($sid)) {
			include('model/MMobileApp.php');
			$app_model = new MMobileApp();
			$csection = $app_model->getRequestById($sid);
			$fields = $app_model->getFieldsByRequest($sid);
		}
		$data['sid'] = $sid;
		$data['csection'] = $csection;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure App Request';
		$this->getTemplate()->display_popup('app_request_configuration', $data);
	}
	
	function actionSaveRequest()
	{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MMobileApp.php');
		$app_model = new MMobileApp();
		$app_model->saveRequest($data);
		echo '';
	}
	
	function actionRequestValues()
	{
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$csection = null;
		$fields = null;
		include('model/MMobileApp.php');
		$app_model = new MMobileApp();
	
		if (!empty($sid)) {
			$csection = $app_model->getRequestById($sid);
			$sec_title = $csection->title;
		} else {
			exit;
		}
		
		$fields = $app_model->getValuesByRequest($sid);
	
		$data['sid'] = $sid;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Save Values of Request - ' . $sec_title;
		$this->getTemplate()->display_popup('app_request_values', $data);
	}
	
	function actionSaveSetValues()
	{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MMobileApp.php');
		$app_model = new MMobileApp();
		$app_model->saveSetValues($data);
		echo '';
	}
	
	
	
	
	function actionSaveTemplate()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : '';
		
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		//var_dump($data);
		//exit;
		$template_model->saveTemplate($tid, $data);
		
		echo '';
	}
	
	function actionDelSection()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_POST['sid']) ? $_POST['sid'] : '';
		
		if (!empty($sid)) {
			include('model/MSkillCrmTemplate.php');
			$template_model = new MSkillCrmTemplate();
			$template_model->deleteSection($tid, $sid);
		}
		echo '';
	}
	
	
	function actionSaveFilter()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		$template_model->saveFilter($tid, $data);
		echo '';
	}
	
	function actionDeleteFilter()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		$template_model->deleteFilter($tid, $data);
		echo '';
	}
	
	
	function actionSaveDefinedSection()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		$type = isset($data['stype']) ? $data['stype'] : '';
		
		if (!empty($data) & ($type=='D' || $type=='T' || $type=='I')) {

			include('model/MSkillCrmTemplate.php');
			$template_model = new MSkillCrmTemplate();
			
			$id = isset($data['id']) ? $data['id'] : '';
			
			$err = '';
			
//			if (empty($id)) {
			$exist_sec = $template_model->getExistingSection($tid, $type);
			if (!empty($exist_sec)) {
				if (empty($id) || ($id != $exist_sec->section_id)) {
					if ($type == 'D') $txt = 'Disposition';
					else if ($type == 'I') $txt = 'IVR Services';
					else $txt = 'TPIN';
					exit($txt.' section already exist !!');
				}
			}
//			}
			
			$template_model->saveDefinedSection($tid, $data);
			exit;
		}
		
		echo 'Failed to save section';
	}

	function actionNewDefinedSection()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$csection = null;

		if (!empty($sid)) {
			include('model/MSkillCrmTemplate.php');
			$template_model = new MSkillCrmTemplate();
			$csection = $template_model->getSectionById($tid, $sid);
		}
		$data['tid'] = $tid;
		$data['sid'] = $sid;
		$data['csection'] = $csection;

		$data['pageTitle'] = 'Configure Section';
		$this->getTemplate()->display_popup('skill_crm_template_section_fixed', $data);
	}
	
	function actionSectionFilters()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$csection = null;
		$fields = null;
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		if (!empty($sid)) {
			$csection = $template_model->getSectionById($tid, $sid);
			$sec_title = $csection->section_title;
		} else {
			//exit;
			$template = $template_model->getTemplateById($tid);
			$data['search_api'] = $template->api;
			$sec_title = 'Search';
		}
		$fields = $template_model->getFiltersBySection($tid, $sid);
		
		$data['tid'] = $tid;
		$data['sid'] = $sid;
		$data['csection'] = $csection;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Search Option of Section - ' . $sec_title;
		$this->getTemplate()->display_popup('skill_crm_template_section_filters', $data);
	}
	
	
	function actionDetails()
	{
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';

		$data['tid'] = $tid;
		$data['sections'] = $template_model->getSections($tid);
		$data['fields'] = $template_model->getFields($tid);
		$data['template_details'] = $template_model->getTemplateById($tid);
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill Template Design';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'stemplate_';
		
		$this->getTemplate()->display('skill_crm_template_design', $data);
	}

	function actionAddTemplate()
	{
		$this->saveTemplate();
	}

	function actionUpdateTemplate()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveTemplate($tid);
	}

	function actionDel()
	{
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();

		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($template_model->deleteTemplate($tid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>false, 'msg'=>'Template Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>true, 'msg'=>'Failed to Delete Template', 'redirectUri'=>$url));
		}
	}

	function saveTemplate($tid='')
	{
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$template = $this->getSubmittedTemplate();
			$errMsg = $this->getTemplateValidationMsg($template);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tid)) {
					if ($template_model->addTemplate($template)) {
						$errMsg = 'Template added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add template !!';
					}
				} else {
					$oldtemplate = $this->getInitialTemplate($tid, $template_model);
					if ($template_model->updateTemplate($oldtemplate, $template)) {
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
			$template = $this->getInitialTemplate($tid, $template_model);
			if (empty($template)) {
				exit;
			}
		}
		
		$data['template'] = $template;
		$data['tid'] = $tid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		if (empty($tid)) {
			$data['pageTitle'] = 'Add New Template';
		} else {
			$data['pageTitle'] = 'Update Template';
			$data['topMenuItems'] = array(array('href'=>'task=stemplate&act=details&tid='.$tid, 'img'=>'icon/cog.png', 'label'=>'Design Template'));
		}
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'stemplate_';
		$this->getTemplate()->display('skill_crm_template_form', $data);
	}
	
	function getInitialTemplate($tid, $template_model)
	{
		$template = null;

		if (empty($tid)) {
			$template->title = '';
			//$template->api = '';
		} else {
			$template = $template_model->getTemplateById($tid);
		}
		return $template;
	}

	function getSubmittedTemplate()
	{
		$posts = $this->getRequest()->getPost();
		$template = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$template->$key = trim($val);
			}
		}

		return $template;
	}

	function getTemplateValidationMsg($template)
	{
		if (empty($template->title)) return "Provide template title";
		if (!preg_match("/^[0-9a-zA-Z_ ]{1,20}$/", $template->title)) return "Provide valid title";

		return '';
	}
	
}
