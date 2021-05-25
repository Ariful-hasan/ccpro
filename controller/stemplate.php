<?php

class Stemplate extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $this->actionSTemplate();
	}
	
	function actionSTemplate()
	{
	    $data['pageTitle'] = 'Skill CRM Templates';
	    $data['topMenuItems'] = array(array('href'=>'task=stemplate&act=add-template', 'img'=>'fa fa-plus-square-o', 'label'=>'Add Template'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('skill_crm_templates', $data);
	}
	
	
	function init2()
	{
		include('model/MSkillCrmTemplate.php');
		include('lib/Pagination.php');
		$template_model = new MSkillCrmTemplate();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $template_model->numTemplates();
		$data['templates'] = $pagination->num_records > 0 ? 
			$template_model->getTemplates($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['templates']) ? count($data['templates']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill CRM Templates';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=stemplate&act=add-template', 'img'=>'add.png', 'label'=>'Add Template'));
		$this->getTemplate()->display('skill_crm_templates', $data);
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
	
	function actionSaveSection()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$tabid = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		$template_model->saveSection($tid, $tabid, $data);
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
	
	function actionSaveSetValues()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		$template_model->saveSetValues($tid, $data);
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
	
	function actionNewSection()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$tabid = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
		$tabSecId = isset($_REQUEST['tbsid']) ? trim($_REQUEST['tbsid']) : '';
		$csection = null;
		$fields = null;
		if (!empty($sid)) {
			include('model/MSkillCrmTemplate.php');
			$template_model = new MSkillCrmTemplate();
			$csection = $template_model->getSectionById($tid, $sid);
			$fields = $template_model->getFieldsBySection($tid, $sid);
		}
		$data['tid'] = $tid;
		$data['sid'] = $sid;
		$data['tabid'] = $tabid;
		$data['tab_sec_id'] = $tabSecId;
		$data['csection'] = $csection;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Section';
		$this->getTemplate()->display_popup('skill_crm_template_section', $data, false, false);
	}
	
	function actionSectionFilters()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$tabid = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
		$tabSecId = isset($_REQUEST['tbsid']) ? trim($_REQUEST['tbsid']) : '';
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
		$data['tabid'] = $tabid;
		$data['tab_sec_id'] = $tabSecId;
		$data['csection'] = $csection;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Search Option of Section - ' . $sec_title;
		$this->getTemplate()->display_popup('skill_crm_template_section_filters', $data);
	}
	
	function actionSectionValues()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$tabid = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
		$tabSecId = isset($_REQUEST['tbsid']) ? trim($_REQUEST['tbsid']) : '';
		$csection = null;
		$fields = null;
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
	
		if (!empty($sid)) {
			$csection = $template_model->getSectionById($tid, $sid);
			$sec_title = $csection->section_title;
		} else {
			//exit;
			//$template = $template_model->getTemplateById($tid);
			//$data['search_api'] = $template->api;
			$sec_title = 'Search';
		}
		$fields = $template_model->getValuesBySection($tid, $sid);
	
		$data['tid'] = $tid;
		$data['sid'] = $sid;
		$data['tabid'] = $tabid;
		$data['tab_sec_id'] = $tabSecId;
		//$data['csection'] = $csection;
		$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Save Values of Section - ' . $sec_title;
		$this->getTemplate()->display_popup('skill_crm_template_section_values', $data);
	}
	
	function actionSectionTabdata()
	{
	    include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$tabid = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
		$csection = null;

		$data['sections'] = $template_model->getSections($tid, $tabid);
		$data['fields'] = $template_model->getFields($tid);
		$data['template_details'] = $template_model->getTemplateById($tid);
		$data['request'] = $this->getRequest();

		if (!empty($sid)) {
			$csection = $template_model->getSectionById($tid, $sid);
			$sec_title = $csection->section_title;
		} else {
			$sec_title = 'Search';
		}
		//$fields = $template_model->getValuesBySection($tid, $sid);
	
		$data['tid'] = $tid;
		$data['sid'] = $sid;
		$data['tabid'] = $tabid;
		//$data['fields'] = $fields;
		$data['pageTitle'] = 'Configure Tab Information of Section - ' . $sec_title;
		$this->getTemplate()->display_popup_tab('skill_crm_template_tab_data', $data);
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
		$template = new stdClass();

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
