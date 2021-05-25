<?php

class Agents extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionAgentList();
	}
	
	function actionAgentList()
	{
		$utype = UserAuth::getRoleID();
		if($utype == 'A' || $utype == 'P' || $utype == 'G')
			$this->getTemplate()->redirect('./index.php?task=agent');

	    $this->setPageTitle('Agent List');
		if($utype=='R'){
			$this->setTopMenu('task=agents&act=add','fa fa-user', 'Add New Agent', 'By clicking this button the System Admin may register a new agent.');
			$this->setTopMenu('task=agents&act=add-session','fa fa-user', 'Add Agent Session', 'Add Agent Session');
		}
		$this->setGridDataUrl($this->url('task=get-home-data&act=agents'));
		$this->setViewData('userColumn', "Agent ID");
		$this->display("agents2");
	    /*
		$data['pageTitle'] = 'Agent List';
		$data['topMenuItems'] = array(array('href'=>'task=agents&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Agent', 'title'=>'By clicking this button the System Admin may register a new agent.'),array('href'=>'task=agents&act=add-session', 'img'=>'fa fa-user', 'label'=>'Add Agent Session', 'title'=>'Add Agent Session'));
		$data['dataUrl'] = $this->url('task=get-home-data&act=agents');
		$data['userColumn'] = "Agent ID";
		$this->getTemplate()->display('agents2', $data);
		*/
	}
	
	function actionAgentsgrid2data()
	{
		include('model/MAgent.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$agent_model = new MAgent();
		$skill_model = new MSkill();
		$pagination = new Pagination();

		$utype = 'A';
		
		//$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&utype=$utype");
		$pagination->num_records = $agent_model->numAgents($utype);
		$agents = $pagination->num_records > 0 ?
		$agent_model->getAgents($utype, '', $pagination->getOffset(), $pagination->rows_per_page) : null;
	
		$skill_options = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module, '', 'array');
		
		
		$responce = new stdClass ();
		$responce->records = is_array($agents) ? count($agents) : 0;
		$responce->page = $pagination->current_page;
		$responce->total =$pagination->getTotalPageCount();	
		
		$result=&$agents;
		if(count($result)>0){
			foreach ( $result as &$data ) {
				$data->web_password_priv=$data->web_password_priv=="Y"?'<a href="#">Yes</a>':'<a class="text-danger"  href="#">No</a>';
				$data->active=$data->active=="Y"?'<a href="#">Active</a>':'<a class="text-danger" href="#">Inactive</a>';
				$skills = $agent_model->getAgentSkill($data->agent_id);
				$num_skills = 0;
				$dtstr="";
				if (is_array($skills)) {
					foreach ($skills as $skill) {
						if ($num_skills > 0) $dtstr.= ', ';
						$dtstr.= isset($skill_options[$skill->skill_id]) ? $skill_options[$skill->skill_id] : $skill->skill_id;
						$dtstr.=' ('.$skill->priority.')';
						$num_skills++;
					}
				} else {
					$dtstr='No Skill';
				}
				$data->skill=$dtstr;
			}
		}
		$responce->rowdata = $result;
		die(json_encode($responce));
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
			if ($agent_model->updateAgentTBankStatus($agentid, $status, 'A', $agent_title)) {
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
		$utype = 'A';
		$this->saveAgent('', $utype);
	}

	function actionAddSession()
	{
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MAgent.php');
        $agent_model = new MAgent();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;

        $agent_session = $this->get_default_agent_session();

        if ($request->isPost()) {
            $agent_session->agent_id = $request->getRequest('agent_id');
            $agent_session->shift_code = $request->getRequest('shift_code');
            $agent_session->is_regular_shift = $request->getRequest('is_regular_shift');
            $agent_session->sdate = $request->getRequest('sdate');
            $agent_session->shift_start = $request->getRequest('shift_start');

            $errMsg = $this->validate_agent_session($request->_posts);

            if (empty($errMsg)) {
                $start_time = $agent_session->shift_start == "N" ? date('H:i') : $agent_session->shift_start;

                if($agent_model->save_agent_session($agent_session->agent_id,$agent_session->shift_code,$agent_session->sdate,$start_time,$agent_session->is_regular_shift)){
                    $errMsg = "Agent Session Successfully Added";
                    $errType = 0;
                } else{
                    $errMsg = "Failed to Add Agent Session";
                    $errType = 1;
                }
            }
        }

        $data['shifts'] = $agent_model->get_shifts();
        $data['agent_session'] = $agent_session;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['topMenuItems'] = array(array('href'=>'task=agents', 'img'=>'fa fa-user', 'label'=>'Agent List', 'title'=>'Agent List.'));
        $data['pageTitle'] = empty($agentid) ? 'Add New Agent Session' : 'Update Agent Session';
        $data['smi_selection'] = 'agents_';
        $this->getTemplate()->display('agent_session_form', $data);
	}

	private function get_default_agent_session()
    {
	    $agent_session = new stdClass();
	    $agent_session->sdate = date("Y-m-d");
	    $agent_session->is_regular_shift = "Y";
	    $agent_session->is_regular_shift = "Y";
	    return $agent_session;
    }

    private function validate_agent_session($post=[])
    {
        include_once('model/MAgent.php');
        include_once('model/MReportNew.php');
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $shifts = $report_model->get_shift_key_value();

        $supervised_agents = $agent_model->getSupervisedAgents(UserAuth::getCurrentUser());
        $supervised_agent_list = [];
        foreach ($supervised_agents as $agent)
        {
            $supervised_agent_list[$agent->agent_id] = $agent->nick;
        }


        $error_message = "";
        if ($post['agent_id'] == ""){
            $error_message .= "Agent ID is required\n";
        }
        if (strlen($post['agent_id']) != 4 || !ctype_digit($post['agent_id'])){
            $error_message .= "Invalid agent ID\n";
        }
        if (UserAuth::getRoleID() != "R" && !array_key_exists($post['agent_id'], $supervised_agent_list)){
            $error_message .= "You do not have permission to add session for selected agent.\n";
        }
        if (!array_key_exists($post['shift_code'], $shifts)){
            $error_message .= "Invalid shift\n";
        }
        if (empty($post['shift_code'])){
            $error_message .= "Shift is required\n";
        }
        if (empty($post['is_regular_shift'])){
            $error_message .= "Shift Type is required\n";
        }
        if (!in_array($post['is_regular_shift'], array("Y","N")) || !ctype_upper($post['is_regular_shift']) || strlen($post['is_regular_shift']) != 1){
            $error_message .= "Invalid shift type\n";
        }
        if (empty($post['sdate']))
        {
            $error_message .= "Date is required\n";
        }
        if (strlen($post['sdate']) != 10)
        {
            $error_message .= "Invalid date\n";
        }
        if (strtotime($post['sdate']) > time())
        {
            $error_message .= "Invalid date range\n";
        }
        if (empty($post['shift_start']))
        {
            $error_message .= "Shift start time is required\n";
        }
        if ((strtoupper($post['shift_start']) == "N" && strlen($post['shift_start']) != 1) || (strlen($post['shift_start']) > 5))
        {
            $error_message .= "Invalid shift start time.\n";
        }

        if (empty($error_message))
        {
            $error_message .= $agent_model->agent_session_exists($post['agent_id'],$post['shift_code'],$post['sdate']) ? "Agent session exists" : "";
        }

        return $error_message;
    }

	function actionUpdate()
	{
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$this->saveAgent($agentid, 'A');
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
	
	function saveAgent($agentid='', $usertype='')
	{
		include('model/MAgent.php');
		include('model/MPassword.php');
		include('lib/FileManager.php');
		include_once('model/MRole.php');
		AddModel("MLanguage");
		AddModel("MReportPartition");
		$agent_model = new MAgent();
		$partition_model = new MReportPartition();
		$role_model = new MRole();

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
		
		$languages=MLanguage::getActiveLanguageList('A');
		
		if ($request->isPost()) {
			$agent = $this->getSubmittedAgent($agentid);
			$errMsg = $this->getValidationMsg($agent, $agentid, $passRuleObj);
			if (empty($usertype)) $usertype = $agent->usertype;
			if (empty($agent->language_1) && empty($agent->language_2) && empty($agent->language_3)) {
				$agent->language_1 = 'EN';
			}
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
							//$errMsg = $agent_title . ' added successfully !!';
							$pic_agent_id = $agent->agent_id;
							$is_success = true;
						} else {
							$errMsg = 'Failed to add '.$agent_title.' !!';
						}
					} else {
						$pic_agent_id = $agentid;
						$oldagent = $this->getInitialAgent($agentid, 'A', $agent_model);
						if(UserAuth::getRoleID()!="R"){
							$agent->usertype=$oldagent->usertype;
						}
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
							$target_path = 'agents_picture/';
							if(!is_dir($target_path)){
								mkdir($target_path,0755,true);
							}	
							$target_path.=$pic_agent_id . '.' . $extention;
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
		if(UserAuth::getRoleID()!="R" && $agent->usertype=="S"){
			$this->getTemplate()->redirect('./index.php?task=agents');
		}
		$data['passRules'] = $passRuleObj;
		$data['agent'] = $agent;
		$data['partitions'] = $partition_model->getPartitionsKeyValue('SQ');
		$data['agentid'] = $agentid;
		$data['languages'] = &$languages;
		$data['request'] = $this->getRequest();
		$data['supervisors'] = $agent_model->getAgentNames("S");
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$agent_title = $usertype == 'S' ? 'Supervisor' : 'Agent';
		$data['pageTitle'] = empty($agentid) ? 'Add New '.$agent_title : 'Update '.$agent_title.' : ' . $agentid;
		$data['smi_selection'] = 'agents_';
		$data['role_list'] = $role_model->getRoles();
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
		$agent = null;

		if (empty($agentid)) {
			$agent = new stdClass();
			$agent->nick = "";
			$agent->agent_id = "";
			$agent->altid = "";
			$agent->password = "";
			$agent->web_password = "";
			$agent->web_password_priv = 'N';
			//$agent->agentskills = '';
			$agent->usertype = $usertype;
			$agent->name = '';
			$agent->partition_id = '';
			$agent->telephone = '';
			$agent->email = '';
			$agent->did = '';
			$agent->language_1 = 'EN';
			$agent->language_2 = '';
			$agent->language_3 = ''; 
			$agent->bday = date("d");
			$agent->bmonth = date("m");
			$agent->birth_day = $agent->bmonth . $agent->bday;
			$agent->active = "Y";
			$agent->max_chat_session = "0";
			$agent->chat_session_limit_with_call = "0";
			$agent->supervisor_id = "";
			$agent->screen_logger = "Y";
			$agent->ob_call = "N";
		} else {
			$agent = $agent_model->getAgentById($agentid);
			//var_dump($agent);
			if (!empty($agent)) {
				//if ($usertype != $agent->usertype) return null;
				$agent->bmonth = substr($agent->birth_day, 0, 2);
				$agent->bday = substr($agent->birth_day, 2, 2);
				//$skills = $agent_model->getAgentSkill($agentid);
				//$agent->agentskills = $this->toSkillString($skills);
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
		//GPrint($posts);die;
		//$agent->agentskills = isset($agent->agentskills) ? rtrim($agent->agentskills, ',') : '';
		// if ($agent->usertype != 'S') $agent->usertype = "A";
		//$agent->usertype = "A";
		$agent->birth_day = $agent->bmonth . $agent->bday;
		if ($agent->active != 'N') $agent->active = "Y";
		if (isset($agent->web_password)) $agent->web_password_priv = 'Y';
		if (!empty($agentid)) $agent->agent_id = $agentid;
		//var_dump($agent);
		
		return $agent;
	}

	function getValidationMsg($agent, $agentid='', $passRuleObj=null)
	{
		$err = '';

		$agent_title = $agent->usertype == 'S' ? 'Supervisor' : 'Agent';
		//if (empty($agent->nick)) $err = "Provide Agent Name";
		if (isset($agent->web_password) && !empty($agent->web_password)) {
			if ($agent->web_password != $agent->web_password_re) $err = "Password Mismatched";
		}
		
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
			if (empty($agent->agent_id)) $err = $agent_title." ID is required!";
		}
		if (empty($agent->name)) $err = "Name is required!";
		if (empty($agent->role_id)) $err = "Role ID is required!";
		if (empty($agent->screen_logger)) $err = "Screen Logger is required";
		if (! in_array($agent->screen_logger, ['N','Y'] )) $err = "Invalid screen logger option";
		if (empty($agent->ob_call)) $err = "Outbound call permission is required";
        if (! in_array($agent->ob_call, ['N','Y'] )) $err = "Invalid outbound call option";
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
		$this->getTemplate()->display_popup('reset-login-pin', $data,true);
	}
	function actionResetPassword() {
		$data['resetted'] = false;
		$data['agentId'] = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$data['uType'] = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		$data['pageTitle'] = "Reset ".$data['uType']." Password";
		$request = $this->getRequest();
		$data['errMsg'] = "";
		$data['errType'] = 1;
		$data['curPass'] = "";
		$data['agentName'] = "";
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$agentInfo = $agent_model->isAgentExist($data['agentId']);
		if ($agentInfo && !empty($agentInfo)){
			$data['agentName'] = $agentInfo->nick;
		}else {
			$data['errType'] = 1;
			$data['errMsg'] = "Invalid ".$data['uType'].".";
		}
		if ($request->isPost()) {			
			$agentid = $request->getPost('agentid');
			$agentPass = $agent_model->resetAgentPassword($agentid);
			if ($agentPass && !empty($agentPass)){
				$data['agentName'] = $agentInfo->nick;
				$data['resetted'] = true;
				$data['errType'] = 0;
				$data['errMsg'] = $data['uType']." password has been reset successfully.";
				$data['curPass'] = $agentPass;
			}else {
				$data['errType'] = 1;
				$data['errMsg'] = $data['uType']." password reset failed.";
			}
		}
		$this->getTemplate()->display_popup('password_reset', $data,true);
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

	function actionShiftProfileList(){
        $data['pageTitle'] = 'Shift Profile List';
        $data['topMenuItems'] = array(array('href'=>'task=agents&act=add-shift-profile', 'img'=>'fa fa-user', 'label'=>'Add New Shift Profile', 'title'=>'By clicking this button the System Admin may add a shift profile.'));
        $data['dataUrl'] = $this->url('task=get-home-data&act=shiftprofile');
        $data['userColumn'] = "Agent ID";
        $data['overlap'] = array("*"=>"All","1"=>"True","0"=>"False");
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'agent_shift_profile';
        $this->getTemplate()->display('shiftprofile', $data);
    }

    function actionAddShiftProfile()
    {
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MAgent.php');
        $agent_model = new MAgent();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;

        $shift_profile = $this->get_default_shift_profile();

        if ($request->isPost()) {

            $shift_profile->shift_code = strtoupper($request->getRequest('shift_code'));
            $shift_profile->label = $request->getRequest('label');
            $shift_profile->start_time = $request->getRequest('start_time');
            $shift_profile->end_time = $request->getRequest('end_time');
            $shift_profile->early_login_cutoff_time = $request->getRequest('early_login_cutoff_time');
            $shift_profile->late_login_cutoff_time = $request->getRequest('late_login_cutoff_time');
            $shift_profile->tardy_cutoff_sec = $request->getRequest('tardy_cutoff_sec');
            $shift_profile->early_leave_cutoff_sec = $request->getRequest('early_leave_cutoff_sec');
            //$shift_profile->day_overlap = $request->getRequest('day_overlap');


            $errMsg = $this->validate_shift_profile($request->getPost());

            if (empty($errMsg)) {

                //$shift_profile->shift_duration = ( $shift_profile->end_time - $shift_profile->start_time )*3600;
                if($this->saveShiftProfile($shift_profile)){
                    $errMsg = "Shift Profile Successfully Added";
                    $errType = 0;
                    $url = $this->url("task=agents&act=shift-profile-list");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else{
                    $errMsg = "Failed to Add Shift Profile";
                    $errType = 1;
                }
            }
        }

        $data['shift_profile'] = $shift_profile;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = false;
        $data['topMenuItems'] = array(array('href'=>'task=agents&act=shift-profile-list', 'img'=>'fa fa-bar', 'label'=>'Shift Profile List', 'title'=>'Shift Profile List.'));
        $data['pageTitle'] = empty($agentid) ? 'Add New Shift Profile' : 'Update Shift Profile';
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'agent_shift_profile';
        $this->getTemplate()->display('shift_profile_form', $data);
    }

    function saveShiftProfile($shift_profile, $isUpdate=false){
        $agent_model = new MAgent();
        if ($isUpdate){
            $shift_code = strtoupper($shift_profile->shift_code);
            if (!$agent_model->deleteShiftProfile($shift_code)){
                 return false;
            }
        }
        $response = false;
	    if ($shift_profile->start_time > $shift_profile->end_time){

            //$hour = 0;
            $minute = 0;
            $star_time_arr = explode(':',$shift_profile->start_time);
            $late_login_cutoff_arr = explode(":",$shift_profile->late_login_cutoff_time);
            //$end_time_arr = explode(":",$shift_profile->end_time);

            $to_time = strtotime($shift_profile->start_time);
            $from_time = strtotime("23:59");
            $minute += abs($to_time - $from_time) / 60 + 1;

            $to_time= strtotime("00:00");
            $from_time= strtotime($shift_profile->end_time);
            $minute += abs($to_time - $from_time) / 60;
            $duration = $minute*60;

            $profileobj_0 = new stdClass();
            $profileobj_0->shift_code = $shift_profile->shift_code;
            $profileobj_0->label = $shift_profile->label;
            $profileobj_0->start_time = $shift_profile->start_time;
            $profileobj_0->end_time = "23:59";
            //$profileobj_0->shift_duration = $shift_profile->shift_duration;
            $profileobj_0->early_login_cutoff_time = $shift_profile->early_login_cutoff_time;
            $profileobj_0->late_login_cutoff_time = $shift_profile->late_login_cutoff_time;
            $profileobj_0->tardy_cutoff_sec = $shift_profile->tardy_cutoff_sec;
            $profileobj_0->early_leave_cutoff_sec = $shift_profile->early_leave_cutoff_sec;
            $profileobj_0->day_overlap = "0";

            $profileobj_1 = new stdClass();
            $profileobj_1->shift_code = $shift_profile->shift_code;
            $profileobj_1->label = "*";
            $profileobj_1->start_time = "00:00";
            $profileobj_1->end_time = $shift_profile->end_time;
            //$profileobj_1->shift_duration = $shift_profile->shift_duration;
            $profileobj_1->early_login_cutoff_time = "00:00";
            $profileobj_1->late_login_cutoff_time = $shift_profile->late_login_cutoff_time;
            $profileobj_1->tardy_cutoff_sec = $shift_profile->tardy_cutoff_sec;
            $profileobj_1->early_leave_cutoff_sec = $shift_profile->early_leave_cutoff_sec;
            $profileobj_1->day_overlap = "1";

            if ($star_time_arr[0] == $late_login_cutoff_arr[0]){
                $profileobj_1->late_login_cutoff_time = "00:00";
            }
            elseif ($star_time_arr[0] > $late_login_cutoff_arr[0]){
                $profileobj_0->late_login_cutoff_time = "23:59";
            }

            $profileobj_0->shift_duration = $duration;
            $profileobj_1->shift_duration = $duration;

            $response = $agent_model->save_shift_profile($profileobj_0);
            $response = $agent_model->save_shift_profile($profileobj_1);
        }else {
            $duration = abs(strtotime($shift_profile->end_time) - strtotime($shift_profile->start_time));
            $shift_profile->shift_duration = $duration;
            $shift_profile->day_overlap = "0";
            $response = $agent_model->save_shift_profile($shift_profile);
        }
        return $response;
    }

    function actionUpdateShiftProfile(){
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MAgent.php');
        $agent_model = new MAgent();

        $request = $this->getRequest();
        $shift_code = $request->getRequest('scode');
        $day_overlap = $request->getRequest('doverlap');
        $errMsg = '';
        $errType = 1;

        $shift_profile = $agent_model->getShiftProfileById($shift_code,$day_overlap);
        $limit_count = count($shift_profile);
        /*$shift_profile->shift_code = $shift_code;
        $shift_profile->day_overlap = $day_overlap;*/
        $start_time ="00:00";
        $end_time ="23:59";
        $early_login_cutoff_time ="00:00";
        $late_login_cutoff_time ="23:59";
        $label = "*";
        $obj = new stdClass();
        if (!empty($shift_profile)){
            foreach ($shift_profile as $key){
                if (strtotime($start_time) < strtotime($key->start_time)){
                    $start_time = $key->start_time;
                }
                if (strtotime($end_time) > strtotime($key->end_time)){
                    $end_time = $key->end_time;
                }
                if (strtotime($early_login_cutoff_time) < strtotime($key->early_login_cutoff_time)){
                    $early_login_cutoff_time = $key->early_login_cutoff_time;
                }
                if ( ($key->late_login_cutoff_time != "00:00") && (strtotime($late_login_cutoff_time) > strtotime($key->late_login_cutoff_time))){
                    $late_login_cutoff_time = $key->late_login_cutoff_time;
                }
                if ($key->label != "*"){
                    $label = $key->label;
                }
            }

            foreach ($shift_profile as $key){
                if ($day_overlap == $key->day_overlap){
                    $obj->shift_code = $key->shift_code;
                    $obj->label = $label;
                    $obj->start_time = $start_time;
                    $obj->end_time = $end_time;
                    $obj->early_login_cutoff_time = $early_login_cutoff_time;
                    $obj->late_login_cutoff_time = $late_login_cutoff_time;
                    $obj->tardy_cutoff_sec = $key->tardy_cutoff_sec;
                    $obj->early_leave_cutoff_sec = $key->early_leave_cutoff_sec;
                    $obj->day_overlap = $key->day_overlap;
                }
            }

        }
        //GPrint($late_login_cutoff_time);
        //GPrint($obj);die;
        if ($request->isPost()) {

            $obj->label = $request->getRequest('label');
            $obj->start_time = $request->getRequest('start_time');
            $obj->end_time = $request->getRequest('end_time');
            $obj->early_login_cutoff_time = $request->getRequest('early_login_cutoff_time');
            $obj->late_login_cutoff_time = $request->getRequest('late_login_cutoff_time');
            $obj->tardy_cutoff_sec = $request->getRequest('tardy_cutoff_sec');
            $obj->early_leave_cutoff_sec = $request->getRequest('early_leave_cutoff_sec');

            $errMsg = $this->validate_shift_profile($request->getPost(),true);

            if (empty($errMsg)) {
                $obj->shift_code = $shift_code;
                $obj->day_overlap = "";

                if($this->saveShiftProfile($obj, true)){
                    $errMsg = "Shift Profile Updated Successfully";
                    $errType = 0;
                    $url = $this->url("task=agents&act=shift-profile-list");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else{
                    $errMsg = "Failed to UPdate Shift Profile";
                    $errType = 1;
                }
            }
        }

        $data['shift_profile'] = $obj;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = true;
        $data['topMenuItems'] = array(array('href'=>'task=agents&act=shift-profile-list', 'img'=>'fa fa-bar', 'label'=>'Shift Profile List', 'title'=>'Shift Profile List.'));
        $data['pageTitle'] = 'Update Shift Profile';
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'agent_shift_profile';
        $this->getTemplate()->display('shift_profile_form', $data);
    }

    private function get_default_shift_profile()
    {
        $shift_profile = new stdClass();
        $shift_profile->shift_code = "";
        $shift_profile->label = "";
        $shift_profile->start_time = "";
        $shift_profile->end_time = "";
        $shift_profile->shift_duration = "";
        $shift_profile->early_login_cutoff_time = "";
        $shift_profile->late_login_cutoff_time = "";
        $shift_profile->tardy_cutoff_sec = "";
        $shift_profile->early_leave_cutoff_sec = "";
        $shift_profile->day_overlap = "";
        return $shift_profile;
    }

    private function validate_shift_profile($post=[],$isUpdate=false)
    {
        //include('model/MAgent.php');
        $agent_model = new MAgent();
        $error_message = "";

        if (!$isUpdate && $post['shift_code'] == ""){
            $error_message .= "Shift Code is required\n";
        }
        if (!$isUpdate && strlen($post['shift_code']) != 4){
            $error_message .= "Invalid Shift Code\n";
        }

        if (empty($post['label'])){
            $error_message .= "Label is required\n";
        }
        if (strlen($post['label']) > 20){
            $error_message .= "Label is Invalid\n";
        }
        if (empty($post['start_time'])){
            $error_message .= "Start Time is required\n";
        }
        if (strlen($post['start_time']) > 5){
            $error_message .= "Invalid Start Time\n";
        }
        if (empty($post['end_time'])){
            $error_message .= "End Time is required\n";
        }
        if (strlen($post['end_time']) > 5){
            $error_message .= "Invalid End Time\n";
        }
        /*if (!$isUpdate && !isset($post['day_overlap'])){
            $error_message .= "Day Overlap is required\n";
        }
        if (!$isUpdate && (strlen($post['day_overlap']) > 1 || !in_array($post['day_overlap'], array('1','0')))){
            $error_message .= "Invalid Day Overlap\n";
        }*/

        if (empty($post['early_login_cutoff_time']))
        {
            $error_message .= "Early Login Cutoff Time is required\n";
        }
        if (strlen($post['early_login_cutoff_time']) > 5)
        {
            $error_message .= "Invalid Early Login Cutoff Time\n";
        }

        if (empty($post['late_login_cutoff_time']))
        {
            $error_message .= "Late Login Cutoff Time is required\n";
        }
        if ((strlen($post['late_login_cutoff_time']) > 5))
        {
            $error_message .= "Invalid Late Login Cutoff Time.\n";
        }

        if (!isset($post['tardy_cutoff_sec']))
        {
            $error_message .= "Tardy Cutoff Seconds is required\n";
        }
        if ((strlen($post['tardy_cutoff_sec']) > 4))
        {
            $error_message .= "Invalid Tardy Cutoff Seconds.\n";
        }

        if (!isset($post['early_leave_cutoff_sec']))
        {
            $error_message .= "Early Leave Cutoff Seconds is required\n";
        }
        if ((strlen($post['early_leave_cutoff_sec']) > 4))
        {
            $error_message .= "Invalid Early Leave Cutoff Seconds.\n";
        }


        if (empty($error_message) && !$isUpdate)
        {
            $error_message .= $agent_model->shift_profile_exists($post['shift_code']) ? "Shift Profile exists" : "";
        }

        return $error_message;
    }

    function actionAgentSkill()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';		
		
		if ('admin' != UserAuth::getRole()) {
			$isAllowed = $agent_model->isAllowedAgent(UserAuth::getCurrentUser(), $agentid);
			if (!$isAllowed) exit;
		}
		$agentdetails = $agent_model->getAgentById($agentid);
		if (empty($agentdetails)) exit;

		$agent_skills0 = '';
		$agent_skills1 = '';
		$agent_skills2 = '';
        $agent_skills3 = '';
		$agent_skills4 = '';
		$agent_skills5 = '';
		$agent_skills6 = '';
		$agent_skills7 = '';
		$agent_skills8 = '';
		$agent_skills9 = '';
		$agent_skills10 = '';


		$data['agent_skills'] = $agent_model->getAgentSkill($agentid);
		
		$request = $this->getRequest();
		$isUpdate = false;
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$skills = $this->getSubmittedSkills();
			
			$agent_skills0 = $skills->agent_skills0;
			$agent_skills1 = $skills->agent_skills1;
			$agent_skills2 = $skills->agent_skills2;
			$agent_skills3 = $skills->agent_skills3;
			$agent_skills4 = $skills->agent_skills4;
			$agent_skills5 = $skills->agent_skills5;
			$agent_skills6 = $skills->agent_skills6;
			$agent_skills7 = $skills->agent_skills7;
			$agent_skills8 = $skills->agent_skills8;
			$agent_skills9 = $skills->agent_skills9;
			$agent_skills10 = $skills->agent_skills10;

			$skills0 = $this->toSkillArray($agent_skills0);
			$skills1 = $this->toSkillArray($agent_skills1);
			$skills2 = $this->toSkillArray($agent_skills2);
			$skills3 = $this->toSkillArray($agent_skills3);
			$skills4 = $this->toSkillArray($agent_skills4);
			$skills5 = $this->toSkillArray($agent_skills5);
			$skills6 = $this->toSkillArray($agent_skills6);
			$skills7 = $this->toSkillArray($agent_skills7);
			$skills8 = $this->toSkillArray($agent_skills8);
			$skills9 = $this->toSkillArray($agent_skills9);
			$skills10 = $this->toSkillArray($agent_skills10);

			if ($agent_model->addAgentSkills($agentid, 0, $skills0, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 1, $skills1, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 2, $skills2, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 3, $skills3, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 4, $skills4, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 5, $skills5, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 6, $skills6, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 7, $skills7, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 8, $skills8, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 9, $skills9, $agentdetails)) $isUpdate = true;
			if ($agent_model->addAgentSkills($agentid, 1, $skills10, $agentdetails)) $isUpdate = true;
			if ($agent_model->removeAgentSkills($agentid, $skills0, $skills1, $skills2, $skills3,$skills4, $skills5, $skills6, $skills7, $skills8, $skills9, $skills10, $agentdetails)) $isUpdate = true;
			
			// GPrint($skills0);
			// GPrint($skills1);
			// GPrint($skills2);
			// GPrint($skills3);
			// GPrint($skills4);
			// GPrint($skills5);
			// GPrint($skills6);
			// GPrint($skills7);
			// GPrint($skills8);
			// GPrint($skills9);
			// GPrint($skills10);
			// GPrint(array_merge($skills0,$skills1,$skills2,$skills3,$skills4,$skills5,$skills6,$skills7,$skills8,$skills9,$skills10));
			// die();
			// $all_skills = array_merge($skills0,$skills1,$skills2,$skills3,$skills4,$skills5,$skills6,$skills7,$skills8,$skills9,skills10);

			if ($isUpdate) {
				// foreach ($all_skills as $key => $skill_id) {
				// 	$skill_model->notifySkillAgentUpdate($skill_id);
				// }
				
				$errType = 0;
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=agent-skill&agentid=$agentid");
				$data['pageTitle'] = 'Skill List for agent :: '  .$agentdetails->name;
				$data['message'] = 'Skill list have been updated successfully !!';
				$data['message'] .= '<br /><br /> <a class="btn" href="'.$url.'"><img src="image/arrow_refresh_small.png" class="bottom" border="0" width="16" height="16" /> Reload Skill List</a>';
				$data['msgType'] = 'success';
				$this->getTemplate()->display_popup('popup_message', $data);
			} else {
				$errMsg = 'No change found!!';
			}
		} else {
			// GPrint($data['agent_skills']);

			if (is_array($data['agent_skills'])) {
				foreach ($data['agent_skills'] as $sag) {
					if ($sag->priority == 1) {
						if($sag->qtype == 'P'){
							if (!empty($agent_skills10)) $agent_skills10 .= ',';
                        	$agent_skills10 .= $sag->skill_id;
						}else{
							if (!empty($agent_skills1)) $agent_skills1 .= ',';
							$agent_skills1 .= $sag->skill_id;
						}
					} else if ($sag->priority == 2) {
						if (!empty($agent_skills2)) $agent_skills2 .= ',';
						$agent_skills2 .= $sag->skill_id;
					} else if ($sag->priority == 0) {
						if (!empty($agent_skills0)) $agent_skills0 .= ',';
						$agent_skills0 .= $sag->skill_id;
					} else if ($sag->priority == 3) {
                        if (!empty($agent_skills3)) $agent_skills3 .= ',';
                        $agent_skills3 .= $sag->skill_id;
					}else if ($sag->priority == 4) {
                        if (!empty($agent_skills4)) $agent_skills4 .= ',';
                        $agent_skills4 .= $sag->skill_id;
					}else if ($sag->priority == 5) {
                        if (!empty($agent_skills5)) $agent_skills5 .= ',';
                        $agent_skills5 .= $sag->skill_id;
					}else if ($sag->priority == 6) {
                        if (!empty($agent_skills6)) $agent_skills6 .= ',';
                        $agent_skills6 .= $sag->skill_id;
					}else if ($sag->priority == 7) {
                        if (!empty($agent_skills7)) $agent_skills7 .= ',';
                        $agent_skills7 .= $sag->skill_id;
					}else if ($sag->priority == 8) {
                        if (!empty($agent_skills8)) $agent_skills8 .= ',';
                        $agent_skills8 .= $sag->skill_id;
					}else {
						if (!empty($agent_skills9)) $agent_skills9 .= ',';
							$agent_skills9 .= $sag->skill_id;
					}
				}
			}
		}


		$data['skill_options'] = $skill_model->getSkillNames();
		$data['request'] = $request;
		$data['agentid'] = $agentid;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		// $data['predictive_dial_skill'] = strtoupper($skilldetails->qtype) == "P";
		$data['agent_skills0'] = $agent_skills0;
		$data['agent_skills1'] = $agent_skills1;
		$data['agent_skills2'] = $agent_skills2;
		$data['agent_skills3'] = $agent_skills3;
		$data['agent_skills4'] = $agent_skills4;
		$data['agent_skills5'] = $agent_skills5;
		$data['agent_skills6'] = $agent_skills6;
		$data['agent_skills7'] = $agent_skills7;
		$data['agent_skills8'] = $agent_skills8;
		$data['agent_skills9'] = $agent_skills9;
		$data['agent_skills10'] = $agent_skills10;
		$data['pageTitle'] = 'Skill List for agent :: '  .$agentdetails->name;
		$this->getTemplate()->display_popup('agent_skill_select', $data);
	}

	function getSubmittedSkills()
	{
		$posts = $this->getRequest()->getPost();
		
		$skill = new stdClass();

		$skill->agent_skills0 = isset($posts['agent_skills0']) ?  trim($posts['agent_skills0']) : '';		
		$skill->agent_skills1 = isset($posts['agent_skills1']) ?  trim($posts['agent_skills1']) : '';
		$skill->agent_skills2 = isset($posts['agent_skills2']) ?  trim($posts['agent_skills2']) : '';
		$skill->agent_skills3 = isset($posts['agent_skills3']) ?  trim($posts['agent_skills3']) : '';
		$skill->agent_skills4 = isset($posts['agent_skills4']) ?  trim($posts['agent_skills4']) : '';
		$skill->agent_skills5 = isset($posts['agent_skills5']) ?  trim($posts['agent_skills5']) : '';
		$skill->agent_skills6 = isset($posts['agent_skills6']) ?  trim($posts['agent_skills6']) : '';
		$skill->agent_skills7 = isset($posts['agent_skills7']) ?  trim($posts['agent_skills7']) : '';
		$skill->agent_skills8 = isset($posts['agent_skills8']) ?  trim($posts['agent_skills8']) : '';
		$skill->agent_skills9 = isset($posts['agent_skills9']) ?  trim($posts['agent_skills9']) : '';
		$skill->agent_skills10 = isset($posts['agent_skills10']) ?  trim($posts['agent_skills10']) : '';

		$skill->agent_skills0 = rtrim($skill->agent_skills0, ',');		
		$skill->agent_skills1 = rtrim($skill->agent_skills1, ',');
		$skill->agent_skills2 = rtrim($skill->agent_skills2, ',');
		$skill->agent_skills3 = rtrim($skill->agent_skills3, ',');
		$skill->agent_skills4 = rtrim($skill->agent_skills4, ',');
		$skill->agent_skills5 = rtrim($skill->agent_skills5, ',');
		$skill->agent_skills6 = rtrim($skill->agent_skills6, ',');
		$skill->agent_skills7 = rtrim($skill->agent_skills7, ',');
		$skill->agent_skills8 = rtrim($skill->agent_skills8, ',');
		$skill->agent_skills9 = rtrim($skill->agent_skills9, ',');
		$skill->agent_skills10 = rtrim($skill->agent_skills10, ',');

		return $skill;
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
}
