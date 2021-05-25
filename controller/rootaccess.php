<?php

class Rootaccess extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$utype = 'R';
		$data['num_records'] = $agent_model->numAgents($utype);
		$data['agents'] = $data['num_records'] > 0 ? 
			$agent_model->getAgents($utype, '', 0, 100) : null;
		$data['agent_model'] = $agent_model;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Root User List';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=rootaccess&act=add', 'img'=>'user_add.png', 'label'=>'Add New Root User'));
		$this->getTemplate()->display('root_users', $data);
	}

	function actionActivate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		//$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$agent_title = 'Root user';
		if ($status == 'Y' || $status == 'N') {
			include('model/MAgent.php');
			$agent_model = new MAgent();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			if ($agent_model->updateAgentStatus($agentid, $status, 'R', $agent_title)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}
	}

	function actionAdd()
	{
		$utype = 'R';
		$this->saveAgent('', $utype);
	}

	function actionUpdate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$this->saveAgent($agentid, 'R');
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
	
	function saveAgent($agentid='', $usertype='R')
	{
		include('model/MAgent.php');
		include('model/MPassword.php');
		include('lib/FileManager.php');
		$agent_model = new MAgent();
		
		
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
		
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		//$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module);
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$agent = $this->getSubmittedAgent($agentid);
			$errMsg = $this->getValidationMsg($agent, $agentid);
			$agent_title = 'Root user';

			if (empty($errMsg)) {
				$existing_agents = empty($agentid) ? $agent_model->getAgentById($agent->agent_id) : null;
				if (empty($existing_agents)) {
					$is_success = false;
					//$skills_selected = $this->toSkillArray($agent->agentskills);					
					$pic_agent_id = '';

					if (empty($agentid)) {
						if ($agent_model->addRootAgent($agent)) {
							$pic_agent_id = $agent->agent_id;
							//$errMsg = $agent_title . ' added successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'Failed to add '.$agent_title.' !!';
						}
					} else {
						$pic_agent_id = $agentid;
						$oldagent = $this->getInitialAgent($agentid, 'R', $agent_model);
						if ($agent_model->updateRootAgent($oldagent, $agent)) {
							//$errMsg = $agent_title.' updated successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'No change found !!';
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

		}
		
		$data['passRules'] = $passRuleObj;
		$data['agent'] = $agent;
		$data['agentid'] = $agentid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$agent_title = 'Root User';
		$data['pageTitle'] = empty($agentid) ? 'Add New '.$agent_title : 'Update '.$agent_title.' : ' . $agentid;
		$data['smi_selection'] = 'rootaccess_';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('agent_root_form', $data);
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
		$agent = null;

		if (empty($agentid)) {
			$agent->nick = "";
			$agent->agent_id = "";
			$agent->password = "";
			$agent->web_password = "";
			$agent->web_password_priv = 'N';
			$agent->from_time = '0000-00-00 00:00';
			$agent->access_duration = 0; //Minute
			$agent->name = '';
			$agent->telephone = '';
			$agent->email = '';
			$agent->active = "Y";
		} else {
			$agent = $agent_model->getAgentById($agentid);
			if (!empty($agent)) {
				if ($usertype != $agent->usertype) return null;
				$restrictions = $agent_model->getAgentAccessRestrictions($agentid);
				$agent->from_time = empty($restrictions) ? '0000-00-00 00:00' : substr($restrictions->start_time, 0, 16);
				if (!empty($restrictions)) {
					$agent->access_duration = (strtotime($restrictions->stop_time) - strtotime($restrictions->start_time))/60;
				} else {
					$agent->access_duration = 0;
				}
			} else {
				exit;
			}
		}
		return $agent;
	}

	function getSubmittedAgent($agentid)
	{
		$posts = $this->getRequest()->getPost();
		$agent = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$agent->$key = trim($val);
			}
		}
		$agent->usertype = "R";
		if ($agent->active != 'N') $agent->active = "Y";
		if (isset($agent->web_password)) $agent->web_password_priv = 'Y';
		if (!empty($agentid)) $agent->agent_id = $agentid;
		//var_dump($agent);
		return $agent;
	}

	function getValidationMsg($agent, $agentid='')
	{
		$err = '';

		$agent_title = 'Root user';
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
	

}
