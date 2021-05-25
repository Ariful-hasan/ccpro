<?php

class Knowledge extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MKnowledgeBase.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$kb_model = new MKnowledgeBase();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		
		$templateinfo = $skill_model->getSkillById($sid);
		
		//if (empty($templateinfo) || $templateinfo->qtype != 'E') exit;
		if (empty($templateinfo)) exit;
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . '&sid=' . $sid);
		
		$pagination->num_records = $kb_model->numKnowledges('', $sid);
		$data['knowledges'] = $pagination->num_records > 0 ? 
			$kb_model->getKnowledges('', $sid, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['knowledges']) ? count($data['knowledges']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['sid'] = $sid;
		$data['pageTitle'] = 'Knowledge Base';
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
		
		//$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'knowledge_list';

		$data['topMenuItems'] = array(
			array('href'=>'task=skills', 'img'=>'icon/application_view_list.png', 'label'=>'List Skill(s)'),
			array('href'=>'task=knowledge&act=add&sid='.$sid, 'img'=>'add.png', 'label'=>'Add New Entry')
		);
		
		//$data['disposition_options'] = $pagination->num_records > 0 ? $kb_model->getDispositionTreeOptions() : null;
		$data['kb_model'] = $kb_model;
		$this->getTemplate()->display('knowledge_bases', $data);
	}
	
	function actionList()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', '', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), '', 0, 100);
		}
	
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Knowledge Base';
		$this->getTemplate()->display('knowledge_base_list', $data);
	}
	
	function actionKbchildren()
	{
		$kid = isset($_REQUEST['root']) ? trim($_REQUEST['root']) : '';
		$childs = array();
		if (!empty($kid)) {
			include('model/MKnowledgeBase.php');
			$kb_model = new MKnowledgeBase();
			
			$knowledge = $kb_model->getKnowledgeById($kid);
			
			if (!empty($knowledge)) {
				
				$children = $kb_model->getKnowledges($knowledge->kbase_id, $knowledge->skill_id);
				if (is_array($children)) {
					foreach ($children as $child) {
						//$details->childs[$child->kbase_id] = $child->title;
						$descendants = ($child->rgt - $child->lft - 1) / 2;
						$_child = new stdClass();
						$_child->text = $child->title;
						if ($descendants > 0) {
							$_child->text .= ' [' . $descendants . ']';
							$_child->hasChildren = true;
						}
						
						if (!empty($child->description)) $_child->text .= '<p>'.$child->description.'</p>';
						
						$_child->id = $child->kbase_id;
						$_child->class="file";
						$childs[] = $_child;
					}
				}
			}
		}
		echo json_encode($childs);
	}
	
	/*
	function actionKbasedetails()
	{
		$kid = isset($_REQUEST['kid']) ? trim($_REQUEST['kid']) : '';
		$details = new stdClass();
		if (!empty($kid)) {
			include('model/MKnowledgeBase.php');
			$kb_model = new MKnowledgeBase();
			
			$knowledge = $kb_model->getKnowledgeById($kid);
			
			if (!empty($knowledge)) {
				$details->title = $knowledge->title;
				$details->id = $knowledge->kbase_id;
				$details->desc = $knowledge->description;
				$details->childs = array();
				$children = $kb_model->getKnowledges($parentid, $knowledge->skill_id);
				if (is_array($children)) {
					foreach ($children as $child) {
						$details->childs[$child->kbase_id] = $child->title;
					}
				}
			}
		}
		
		echo json_encode($details);
	}
	
	function actionKbasechildren()
	{
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
		$options = array();
		if (!empty($did)) {
			include('model/MKnowledgeBase.php');
			$kb_model = new MKnowledgeBase();
				
			$options = $kb_model->getKBaseChildrenOptions('', $did);
		}
	
		echo json_encode($options);
	}
	*/
	
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
		include('model/MKnowledgeBase.php');
		$kb_model = new MKnowledgeBase();
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
			$errMsg = $this->getValidationMsg($service, $sid, $dis_code, $kb_model);
			
			if (empty($errMsg)) {
				$is_success = false;
				
				if (empty($dis_code)) {
					if ($kb_model->addKnowledge($sid, $service)) {
						$errMsg = 'Knowledge entry added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add knowledge entry !!';
					}
				} else {
					$oldservice = $this->getInitialService($sid, $dis_code, $kb_model);
					if ($kb_model->updateKnowledge($oldservice, $service)) {
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
			$service = $this->getInitialService($sid, $dis_code, $kb_model);
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
		
		if (empty($dis_code)) {
			$data['pageTitle'] = 'Add New Knowledge Entry';
			//$data['knowledges'] = $kb_model->getKBaseTreeOptions();
		} else {
			$data['pageTitle'] = 'Update Knowledge Entry'.' : ' . $dis_code;
			//$data['dc_parent_path'] = $kb_model->getDispositionPath($dis_code);
		}
		
		//var_dump();
		
		$kbase_id = isset($service->kbase_id) ? $service->kbase_id : '';
		$data['kbase_ids'] = $kb_model->getKnowledgePathArray($kbase_id);
		$data['kbase_id0'] = $kb_model->getKBaseChildrenOptions($sid);
		$data['kb_model'] = $kb_model;
		
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;

		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'knowledge_init';
		$this->getTemplate()->display('knowledge_entry_form', $data);
	}
	
	function getInitialService($sid, $dis_code, $kb_model)
	{
		$service = null;

		if (empty($dis_code)) {
			$service->kbase_id = '';
			$service->title = '';
			$service->description = '';
			$service->tags = '';
			$service->parent_id = '';
		} else {
			$service = $kb_model->getKnowledgeById($dis_code, $sid);
		}
		
		return $service;
	}

	function getSubmittedService()
	{
		$posts = $this->getRequest()->getPost();
		$service = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}
		
		$did = '';
		for ($i=0;$i<=10; $i++) {
			$did1 = isset($_POST['kbase_id'.$i]) ? trim($_POST['kbase_id'.$i]) : '';
			if (!empty($did1)) $did = $did1;
			else break;
		}
		$service->parent_id = $did;
		
		return $service;
	}

	function getValidationMsg($service, $sid, $dis_code='', $dc_model)
	{
		//if (empty($service->disposition_id)) return "Provide disposition code";
		if (empty($service->title)) return "Provide disposition title";
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
