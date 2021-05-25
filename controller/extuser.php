<?php

class Extuser extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionExtUserList();
	}
	
	function actionExtUserList()
	{
		$data['pageTitle'] = 'External User List';
		$data['topMenuItems'] = array(array('href'=>'task=extuser&act=add', 'img'=>'fa fa-user', 'label'=>'Add New External User', 'title'=>'By clicking this button the System Admin may register a new external user.'));
		$data['dataUrl'] = $this->url('task=get-home-data&act=ext');
		//$data['userColumn'] = "Agent ID";
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'extuser_';
		$this->getTemplate()->display('extuser', $data);
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
			if ($agent_model->updateAgentStatus($agentid, $status, 'A', $agent_title)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}
	}


	function actionAdd()
	{
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		if (empty($utype)) {
			$utype = isset($_REQUEST['usertype']) ? trim($_REQUEST['usertype']) : '';
		}
		$utype = 'A';
		$this->saveExtUser('', $utype);
	}

	function actionUpdate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$this->saveExtUser($agentid);
	}
	
	function actionDel()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();

		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
		$utype = 'A';
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
	
	function saveExtUser($login_id='')
	{
		include('model/MReportUser.php');
		include('model/MReportUserDid.php');
		include('model/MPassword.php');
		include('lib/FileManager.php');
		//AddModel("MLanguage");
		$ext_model = new MReportUser();
		$ext_did_model = new MReportUserDid();
		$did_list = $ext_model->getAllDid();
		//GPrint($did_list);die;
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		//$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module);
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$data['list_of_did'] = '';
		$list_of_did = '';
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
			//var_dump($_POST['did_options']);
			$extuser = $this->getSubmittedExtUser($login_id);
			//GPrint($extuser->did);die;
			$errMsg = $this->getValidationMsg($extuser, $login_id, $passRuleObj);
			$agent_title = 'External User';
			$srv_file_name_en = 'extUserImage';

			if (empty($errMsg)) {
				$existing_agents = empty($login_id) ? $ext_model->getExtUserByLoginId($extuser->login_id) : null;
				if (empty($existing_agents)) {
					$is_success = false;
					$pic_agent_id = '';

					if (empty($login_id)) {
						if ($ext_model->addExt($extuser)) {
							$errMsg = $agent_title . ' added successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'Failed to add '.$agent_title.' !!';
						}
					} else {
						$pic_agent_id = $login_id;
						$oldagent = $this->getInitialExtUser($login_id, $ext_model);
						if ($ext_model->updateExt($oldagent, $extuser)) {
							$errMsg = $agent_title.' updated successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'No change found !!';
						}
					}

					if ($is_success) {
						$errType = 0;
						if (empty($login_id)) {
							$errMsg = $agent_title . ' added successfully !!';
						} else {
							$errMsg = $agent_title.' updated successfully !!';
						}
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'User ID already exist !!';
				}
			}

		} else {
			$extuser = $this->getInitialExtUser($login_id, $ext_model);
			if (empty($extuser)) {
				exit;
			}
			//GPrint($extuser->did[0]);
			//if (empty($usertype)) $usertype = $agent->usertype;
		}
		/*if(UserAuth::getRoleID()!="R" && $extuser->usertype=="S"){
			$this->getTemplate()->redirect('./index.php?task=agents');
		}*/
		//$this->getTemplate()->redirect('./index.php?task=extuser');

		$data['passRules'] = $passRuleObj;
		$data['did_list'] = $did_list;
		$data['list_of_did'] = $list_of_did;
		$data['agent'] = $extuser;
		$data['agentid'] = $login_id;
		$data['languages'] = &$languages;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$agent_title = 'External User';
		//$agent_title = $usertype == 'S' ? 'Supervisor' : 'Agent';
		$data['pageTitle'] = empty($login_id) ? 'Add New '.$agent_title : 'Update '.$agent_title.' : ' . $login_id;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'extuser_';
		$this->getTemplate()->display('external_form', $data);
	}
	
	function actionCheck()
	{
		$posts = $this->getRequest()->getPost();
		$agentid = $posts['login_id'];
		include('model/MReportUser.php');
		$agent_model = new MReportUser();
		
		$agent = $agent_model->getExtUserByLoginId($agentid);
		if (!empty($agent)) {
			exit('false');
		}
		exit('true');
	}
	
	function getInitialExtUser($login_id, $agent_model)
	{
		$agent = null;

		if (empty($login_id)) {
			$agent = new stdClass();
			$agent->login_id = "";
			$agent->password = "";
			$agent->name = '';
			$agent->status = "A";
			$agent->did = "";
		} else {
			$agent = $agent_model->getExtUserByLoginId($login_id);
			//var_dump($agent);
			if (!empty($agent)) {
				$didList = $agent_model->getUserDid($agent->user_id);
				$did_list_array = array();
				$didString = "";
				if (is_array($didList)){
					foreach ($didList as $did){
						$did_list_array[] = $did->did;
						//$didString = !empty($didString) ? $didString.", ".$did->did : $did->did;
					}
				}
				//$agent->did = $didString;
				$agent->did = $did_list_array;
				
			} else {
				exit;
			}
		}
		return $agent;
	}

	function getSubmittedExtUser($login_id)
	{
		$posts = $this->getRequest()->getPost();
		$agent = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$agent->$key = trim($val);
			}
		}
		if (isset($_POST['did_options'])){
			$agent->did = $_POST['did_options'];
		}
		//GPrint($posts);die;
		//$agent->agentskills = isset($agent->agentskills) ? rtrim($agent->agentskills, ',') : '';
		//if ($agent->usertype != 'S') $agent->usertype = "A";
		//$agent->usertype = "A";
		//$agent->birth_day = $agent->bmonth . $agent->bday;
		if ($agent->status != 'L') $agent->status = "A";
		//if (isset($agent->web_password)) $agent->web_password_priv = 'Y';
		if (!empty($login_id)) $agent->login_id = $login_id;
		//var_dump($agent);
		
		return $agent;
	}

	function getValidationMsg($agent, $agentid='', $passRuleObj=null)
	{
		$err = '';

		$agent_title = 'External User';
		//$agent_title = $agent->usertype == 'S' ? 'Supervisor' : 'Agent';
		//if (empty($agent->nick)) $err = "Provide Agent Name";
		/*if (isset($agent->web_password) && !empty($agent->web_password)) {
			if ($agent->web_password != $agent->web_password_re) $err = "Password Mismatched";
		}*/
		
		if (empty($agentid) || !empty($agent->password)) {
			$allowChars = "";
			if (isset($passRuleObj->spe) && !empty($passRuleObj->spe)){
				$arrSpecial = str_split($passRuleObj->spe);
				foreach ($arrSpecial as $sinChar) {
					$allowChars .= "\\".$sinChar;
				}
			}
			
			if (empty($agent->password)){
				$err = "Provide ".$agent_title." Password";
			} elseif ($agent->password != $agent->password_re){
				$err = "Password Mismatched";
			} elseif (isset($passRuleObj->max) && !empty($passRuleObj->max) && strlen($agent->password) > $passRuleObj->max){
				$err = 'Password length must not be more than '.$passRuleObj->max.' characters';
			} elseif (isset($passRuleObj->min) && !empty($passRuleObj->min) && strlen($agent->password) < $passRuleObj->min){
				$err = 'Password must consist of at least '.$passRuleObj->min.' non-blank characters';
			} elseif (isset($passRuleObj->cha) && !empty($passRuleObj->cha) && !preg_match('/[a-zA-Z]{'.$passRuleObj->cha.',}/i', $agent->password)) {
				$err = 'Password field must contain at least '.$passRuleObj->cha.' character';
			} elseif (isset($passRuleObj->spe) && empty($passRuleObj->spe) && preg_match('/[^a-zA-Z0-9]/i', $agent->password)) {
				$err = 'Password field contain invalid character';
			} elseif (isset($passRuleObj->spe) && !empty($passRuleObj->spe) && preg_match('/[^a-zA-Z0-9'.$allowChars.']/i', $agent->password)) {
				$err = 'Password field contain invalid character';
			} elseif (isset($passRuleObj->upp) && !empty($passRuleObj->upp) && !preg_match('/(?:[A-Z].*){'.$passRuleObj->upp.',}/', $agent->password)){
				$err = 'Password contain at least '.$passRuleObj->upp.' uppercase character';
			} elseif (isset($passRuleObj->low) && !empty($passRuleObj->low) && !preg_match('/(?:[a-z].*){'.$passRuleObj->low.',}/', $agent->password)){
				$err = 'Password contain at least '.$passRuleObj->low.' lowercase Character';
			} elseif (isset($passRuleObj->num) && !empty($passRuleObj->num) && !preg_match('/(?:[0-9].*){'.$passRuleObj->num.',}/', $agent->password)){
				$err = 'Password contain at least '.$passRuleObj->num.' number';
			}
		}
		if (empty($agentid)) {
			if (empty($agent->login_id)) $err = "Provide ".$agent_title." ID";
		}
		if (preg_match('/[^A-Za-z0-9_]/', $agent->login_id)){
			$err = "Provide Valid Login ID";
		}
		//if (empty($user->role)) $err = "Provide User Role";
		return $err;
	}
	function actionResetLoginPinPassword(){
		$request = $this->getRequest();
		$data=array();
		$data['fullload']=true;
		$data['loadbv']=true;
		$data['pageTitle'] = 'New Login PIN Generation';
		//$data['buttonTitle'] = 'Generate';
		$agentid=$request->getGet('agentid');
		AddHiddenFields("agentid", $agentid);
		if($this->getRequest()->isPost()){		
			include('model/MAgent.php');
			$agent_model = new MAgent();			
				$generate="";
				$try=0;
				while ($try<=4){
					$generate=rand(10000,99999);
					if(!$agent_model->isLoginPINExist($generate)){
						break;
					}else{
						$generate="";
					}
					$try++;
				}
				if(!empty($generate)){
					if($agent_model->updateLoginPIN($agentid,$generate)){
						AddInfo("Login PIN successfully updated<br/> &nbsp; &nbsp;New Login PIN is : <strong>*$generate</strong>");
						$this->getTemplate()->display_popup_msg($data);
					}else{
						AddError("Try again");
					}
						
				}else{
					AddError("Try again");
				}
			
		}
		$this->getTemplate()->display_popup('reset-login-pin', $data);
	}
	function actionResetPassword() {
		$data['resetted'] = false;
		$data['agentId'] = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$data['loginid'] = isset($_REQUEST['loginid']) ? trim($_REQUEST['loginid']) : '';
		$data['pageTitle'] = "Reset External User Password";
		$request = $this->getRequest();
		$data['errMsg'] = "";
		$data['errType'] = 1;
		$data['curPass'] = "";
		$data['agentName'] = "";
		include('model/MReportUser.php');
		$agent_model = new MReportUser();
		$agentInfo = $agent_model->isAgentExist($data['agentId']);
		if ($agentInfo && !empty($agentInfo)){
			$data['agentName'] = $agentInfo->name;
		}else {
			$data['errType'] = 1;
			$data['errMsg'] = "Invalid External User";
		}
		if ($request->isPost()) {			
			$agentid = $request->getPost('agentid');
			$loginid = $request->getPost('loginid');
			$agentPass = $agent_model->resetAgentPassword($loginid);
			if ($agentPass && !empty($agentPass)){
				$data['agentName'] = $agentInfo->name;
				$data['loginid'] = $loginid;
				$data['resetted'] = true;
				$data['errType'] = 0;
				$data['errMsg'] = "External User password has been reset successfully.";
				$data['curPass'] = $agentPass;
			}else {
				$data['errType'] = 1;
				$data['errMsg'] = "External User password reset failed.";
			}
		}
		$this->getTemplate()->display_popup('ext_user_pass_reset', $data);
	}
	
	/*
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
	*/
	function actionTest(){
		$table=!empty($_REQUEST['tbl'])?$_REQUEST['tbl']:"seat";
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$sql = "SELECT * FROM $table limit 1";
		$result=$setting_model->getDB()->query($sql);
		ShowTableFromArray($result);
	}

}
