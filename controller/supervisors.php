<?php

class Supervisors extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MAgent.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$agent_model = new MAgent();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		if ($utype != 'S') $utype = 'A';
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&utype=$utype");
		$pagination->num_records = $agent_model->numAgents($utype);
		$data['agents'] = $pagination->num_records > 0 ? 
			$agent_model->getAgents($utype, '', $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['agents']) ? count($data['agents']) : 0;
		$data['pagination'] = $pagination;

		$data['agent_model'] = $agent_model;
		$data['request'] = $this->getRequest();
		$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module, '', 'array');
		$data['pageTitle'] = $utype == 'S' ? 'Supervisor List' : 'Agent List';
		$data['usertype'] = $utype;
		$data['topMenuItems'] = array(array('href'=>'task=supervisors&act=add&utype=S', 'img'=>'user_add.png', 'label'=>'Add New Supervisor'));
		$this->getTemplate()->display('agents', $data); */
		
		$data['pageTitle'] = 'Supervisor List';
		$data['topMenuItems'] = array(array('href'=>'task=supervisors&act=add&utype=S', 'img'=>'fa fa-user', 'label'=>'Add New Supervisor'));
		$data['dataUrl'] = $this->url('task=get-home-data&act=agents&utype=S');
		$data['userColumn'] = "Supervisor ID";
		$this->getTemplate()->display('agents2', $data);
	}

	function actionActivate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';
		if ($status == 'Y' || $status == 'N') {
			include('model/MAgent.php');
			$agent_model = new MAgent();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page."&utype=$utype");
			if ($agent_model->updateAgentStatus($agentid, $status, 'S', $agent_title)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}
	}

	function actionTelebank()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';
		if ($status == 'Y' || $status == 'N') {
			include('model/MAgent.php');
			$agent_model = new MAgent();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page."&utype=$utype");
			if ($agent_model->updateAgentTBankStatus($agentid, $status, 'S', $agent_title)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>false, 'msg'=>'TeleBanking Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Update TeleBanking Status Option', 'redirectUri'=>$url));
			}
		}
	}

	function actionAdd()
	{
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		if (empty($utype)) {
			$utype = isset($_REQUEST['usertype']) ? trim($_REQUEST['usertype']) : '';
		}
		//if ($utype != 'S') $utype = 'A';
		$utype = 'S';
		$this->saveAgent('', $utype);
	}

	function actionUpdate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$this->saveAgent($agentid, 'S');
	}

	function actionDelpic()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=update&agentid=$agentid");
		
		//$file_name = $this->getTemplate()->file_upload_path . 'Agents/' . $agentid . '.png';
		$file_name = 'agents_picture/' . $agentid . '.png';
		//if () {
		//}
		if (unlink($file_name)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Profile Picture', 'isError'=>false, 'msg'=>'Profile picture removed successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Profile Picture', 'isError'=>true, 'msg'=>'Failed to remove profile picture', 'redirectUri'=>$url));
		}
	}
	
	function actionDel()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();

		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		$utype = 'S';
		$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page."&utype=$utype");
		
		if ($agent_model->deleteAgent($agentid, $utype)) {
			//$file_name = $this->getTemplate()->file_upload_path . 'Agents/' . $agentid . '.png';
			$file_name = 'agents_picture/' . $agentid . '.png';
			if (file_exists($file_name)) {
				unlink($file_name);
			}
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete '.$agent_title, 'isError'=>false, 'msg'=>$agent_title.' Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Delete '.$agent_title, 'redirectUri'=>$url));
		}
	}
	
	function saveAgent($agentid='', $usertype='')
	{
		include('model/MAgent.php');
		include('lib/FileManager.php');
		include('model/MPassword.php');
		$agent_model = new MAgent();
		
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		//$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module);
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$data['maxlength'] = 15;
		$pass_model = new MPassword();
		$passRulesData = $pass_model->getPasswordRules(0, 7);
		$passRuleObj = new stdClass();
		if (count($passRulesData) > 0){
		    foreach ($passRulesData as $pRule) {
		        $objKey = $pRule->rule_key;
		        if ($pRule->status == 'Y') $passRuleObj->$objKey = $pRule->value;
		        else $passRuleObj->$objKey = "";
		        if ($pRule->status == 'Y' && $pRule->rule_key == 'max'){
		            $data['maxlength'] = $pRule->value;
		        }
		    }
		}
		if ($request->isPost()) {
			$agent = $this->getSubmittedAgent($agentid);
			$errMsg = $this->getValidationMsg($agent, $agentid);
			if (empty($usertype)) $usertype = $agent->usertype;
			$agent_title = $usertype == 'S' ? 'Supervisor' : 'Agent';
			
			$srv_file_name_en = 'agentImage';
			
			if (empty($errMsg)) {
			
				if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
					$extention = FileManager::findexts(basename( $_FILES[$srv_file_name_en]['name']));
					if ($extention != 'png') $errMsg = 'Only png file is allowed to upload';
				}
			}
			
			if (empty($errMsg)) {
				$existing_agents = empty($agentid) ? $agent_model->getAgentById($agent->agent_id) : null;
				if (empty($existing_agents)) {
					$is_success = false;
					//$skills_selected = $this->toSkillArray($agent->agentskills);					
					$pic_agent_id = '';

					if (empty($agentid)) {
						if ($agent_model->addAgent($agent)) {
							$pic_agent_id = $agent->agent_id;
							//$errMsg = $agent_title . ' added successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'Failed to add '.$agent_title.' !!';
						}
					} else {
						$pic_agent_id = $agentid;
						$oldagent = $this->getInitialAgent($agentid, 'S', $agent_model);
						if ($agent_model->updateAgent($oldagent, $agent)) {
							//$errMsg = $agent_title.' updated successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'No change found !!';
						}
					}
					
					if (!empty($pic_agent_id)) {
						if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
							//$target_path = $this->getTemplate()->file_upload_path . 'Agents/' . $pic_agent_id . '.' . $extention;
							$target_path = 'agents_picture/' . $pic_agent_id . '.' . $extention;
							if (move_uploaded_file($_FILES[$srv_file_name_en]['tmp_name'], $target_path)) $is_success = true;
						}
					}
					
					if ($is_success) {
						$errType = 0;
						if (empty($agentid)) {
							$errMsg = $agent_title . ' added successfully !!';
						} else {
							$errMsg = $agent_title.' updated successfully !!';
						}
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&utype=$usertype");
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
							CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'User ID already exist !!';
				}
			}
			
		} else {
			$agent = $this->getInitialAgent($agentid, $usertype, $agent_model);
			if (empty($agent)) {
				exit;
			}
			if (empty($usertype)) $usertype = $agent->usertype;
		}
		$data['passRules'] = $passRuleObj;
		$data['agent'] = $agent;
		$data['agentid'] = $agentid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$agent_title = $usertype == 'S' ? 'Supervisor' : 'Agent';
		$data['pageTitle'] = empty($agentid) ? 'Add New '.$agent_title : 'Update '.$agent_title.' : ' . $agentid;
		$data['smi_selection'] = 'supervisors_';
		$this->getTemplate()->display('agent_form', $data);
	}

	function actionCheck()
	{
		$posts = $this->getRequest()->getPost();
		$agentid = $posts['agent_id'];
		include('model/MAgent.php');
		$agent_model = new MAgent();
		
		$agent = $agent_model->getAgentById($agentid);
		if (!empty($agent)) {
			exit('false');
		}
		exit('true');
	}
		
	function getInitialAgent($agentid, $usertype, $agent_model)
	{
		$agent = new stdClass();

		if (empty($agentid)) {
			$agent->nick = "";
			$agent->agent_id = "";
			$agent->password = "";
			$agent->web_password = "";
			$agent->web_password_priv = 'N';
			$agent->agentskills = '';
			$agent->usertype = $usertype;
			$agent->name = '';
			$agent->telephone = '';
			$agent->email = '';
			$agent->bday = date("d");
			$agent->bmonth = date("m");
			$agent->birth_day = $agent->bmonth . $agent->bday;
			$agent->active = "Y";
		} else {
			$agent = $agent_model->getAgentById($agentid);
			if (!empty($agent)) {
				if ($usertype != $agent->usertype) return null;
				$skills = $agent_model->getAgentSkill($agentid);
				$agent->agentskills = $this->toSkillString($skills);
				$agent->bmonth = substr($agent->birth_day, 0, 2);
				$agent->bday = substr($agent->birth_day, 2, 2);
			} else {
				exit;
			}
		}
		return $agent;
	}

	function getSubmittedAgent($agentid)
	{
		$posts = $this->getRequest()->getPost();
		$agent = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$agent->$key = trim($val);
			}
		}
		$agent->agentskills = isset($agent->agentskills) ? rtrim($agent->agentskills, ',') : '';
		//if ($agent->usertype != 'S') $agent->usertype = "A";
		$agent->usertype = "S";
		$agent->birth_day = $agent->bmonth . $agent->bday;
		if ($agent->active != 'N') $agent->active = "Y";
		if (isset($agent->web_password)) $agent->web_password_priv = 'Y';
		if (!empty($agentid)) $agent->agent_id = $agentid;
		//var_dump($agent);
		return $agent;
	}

	function getValidationMsg($agent, $agentid='')
	{
		$err = '';

		$agent_title = $agent->usertype == 'S' ? 'Supervisor' : 'Agent';
		//if (empty($agent->nick)) $err = "Provide Agent Name";		
		if (isset($agent->web_password) && !empty($agent->web_password)) {
			if ($agent->web_password != $agent->web_password_re) $err = "Password Mismatched";
		}

		if (empty($agentid) || !empty($agent->password)) {
			if ($agent->password != $agent->password_re) $err = "Password Mismatched";
			if (empty($agent->password)) $err = "Provide ".$agent_title." Password";
		}			
		if (empty($agentid)) {
			if (empty($agent->agent_id)) $err = "Provide ".$agent_title." ID";
		}
		//if (empty($user->role)) $err = "Provide User Role";
		return $err;
	}
	
	function toSkillArray($str_service)
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

	function toSkillString($skills)
	{
		$services = '';
		if (is_array($skills)) {
			foreach ($skills as $_srv) {
				$_srvid = $_srv->skill_id;
				if (!empty($services)) $services .= ',';
				$services .= $_srvid;
			}
		}
		return $services;
	}

}
