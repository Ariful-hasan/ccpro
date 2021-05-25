<?php

class Conference extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $this->actionConference();
	}
	
	function actionConference()
	{
	    $data['pageTitle'] = 'Conference List';
	    $data['topMenuItems'] = array(array('href'=>'task=conference&act=add', 'img'=>'add.png', 'label'=>'Add Conference'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('conferences', $data);
	}
	
	function init2()
	{
		include('model/MConference.php');
		$conf_model = new MConference();
		$data['confs'] = $conf_model->getConferences();
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Conference List';
		
		if (UserAuth::hasRole('admin'))	$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=conference&act=add', 'img'=>'add.png', 'label'=>'Add Conference'));
		$this->getTemplate()->display('conferences', $data);
	}

	function actionSelectagent()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();
		
		$data['pageTitle'] = 'Agent(s)';
		
		$data['agent_options'] = $agent_model->getConferenceAgents();
		
		$this->getTemplate()->display_popup('conference_select_agent', $data);
	}
	
	function actionAdd()
	{
		$this->saveConference();
	}

	function actionUpdate()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$this->saveConference($cid);
	}

	function actionDel()
	{
		include('model/MConference.php');
		$conf_model = new MConference();

		$confid = isset($_REQUEST['confid']) ? trim($_REQUEST['confid']) : '';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		
		if ($conf_model->deleteConference($confid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Conference', 'isError'=>false, 'msg'=>'Conference Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Conference', 'isError'=>true, 'msg'=>'Failed to Delete Conference', 'redirectUri'=>$url));
		}
	}
	
	function saveConference($confid='')
	{
		include('model/MConference.php');
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$conf_model = new MConference();

		if (empty($confid)) {
			if ($conf_model->numConferences() >= 10) {
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				$this->getTemplate()->display('msg', array('pageTitle'=>'Add Conference', 'isError'=>true, 'msg'=>'Maximum number of conferences already exist!!', 'redirectUri'=>$url));
			}
		}
		
		$data['agent_options'] = $agent_model->getConferenceAgents();
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$conference = $this->getSubmittedConference($confid);
			$errMsg = $this->getValidationMsg($conference, $confid);

			if (empty($errMsg)) {
				
				//$existing_agents = empty($agentid) ? $agent_model->getAgentById($agent->agent_id) : null;
				//if (empty($existing_agents)) {
					
				$is_success = false;
				$agents_selected = $this->toArray($conference->agents);
				$exts_selected = $this->toArray($conference->ext_numbers);

				if (empty($confid)) {
					if ($conf_model->addConference($conference, $agents_selected, $exts_selected)) {
							$errMsg ='Conference added successfully !!';
							$is_success = true;
					} else {
						$errMsg = 'Failed to add conference !!';
					}
				} else {
					$oldconference = $this->getInitialConference($confid, $conf_model);
					$conference->bridge_number = $oldconference->bridge_number;
					//var_dump($oldconference);
					//exit;
					if (isset($_POST['start_conf'])) $task_type = 'S';
					else if (isset($_POST['stop_conf'])) $task_type = 'E';
					else $task_type = '';
					
					if ($conf_model->updateConference($oldconference, $conference, $agents_selected, $exts_selected, $task_type)) {
						$errMsg = 'Conference updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
							CONTENT=\"2;URL=$url\">";
				}

				//} else {
					//$errMsg = 'User ID already exist !!';
				//}
			}
			
		} else {
			$conference = $this->getInitialConference($confid, $conf_model);
			if (empty($conference)) {
				exit;
			}
		}
		
		$data['conference'] = $conference;
		$data['confid'] = $confid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($confid) ? 'Add Conference' : 'Update Conference';
		if (UserAuth::hasRole('admin'))	$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'conference_';
		$this->getTemplate()->display('conference_form', $data);
	}
	
	function getInitialConference($confid, $conf_model)
	{
		$conf = new stdClass();

		if (empty($confid)) {
			$conf->bridge_number = rand(100, 999);
			$conf->title = "";
			$conf->delay_ext_dial = "0";
			$conf->agents = "";
			$conf->ext_numbers = '';
			$conf->active = 'Y';
		} else {
			$conf = $conf_model->getConferenceById($confid);
			if (!empty($conf)) {
				$agents_added = $conf_model->getConferenceAgent($confid);
				$conf->agents = $this->toCSVString($agents_added, 'agent_id');
				
				$exts_added = $conf_model->getConferenceExtNumber($confid);
				$conf->ext_numbers = $this->toCSVString($exts_added, 'ext_number');
			}
		}
		return $conf;
	}

	function getSubmittedConference($confid)
	{
		$posts = $this->getRequest()->getPost();
		$conf = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$conf->$key = trim($val);
			}
		}

		if (!empty($confid)) $conf->conf_id = $confid;
		//var_dump($agent);
		return $conf;
	}

	function getValidationMsg($conference, $confid)
	{
		if (empty($confid)) {
			if (empty($conference->bridge_number)) return 'Conference number empty';
			if (!preg_match("/^[0-9]{3}$/", $conference->bridge_number)) return "Provide valid conference number";		
		}
		if (empty($conference->title)) return 'Title empty';
		if (!preg_match("/^[0-9A-Za-z_ ]{1,30}$/", $conference->title)) return "Provide valid title";
		
		return '';
	}
	
	function toArray($str_service)
	{
		$services = array();
		$_srvs = explode(",", $str_service);
		if (is_array($_srvs)) {
			foreach ($_srvs as $_srv) {
				$_srv = trim($_srv);
				if (!empty($_srv)) $services[] = $_srv;
			}
		}
		return $services;
	}

	function toCSVString($skills, $field)
	{
		$services = '';
		if (is_array($skills)) {
			foreach ($skills as $_srv) {
				$_srvid = $_srv->$field;
				if (!empty($services)) $services .= ',';
				$services .= $_srvid;
			}
		}
		return $services;
	}

}
