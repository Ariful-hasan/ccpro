<?php

class Skills extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MSkill.php');
		//include('lib/Pagination.php');
		$skill_model = new MSkill(); */
		/*
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $skill_model->numSkills();
		$data['skills'] = $pagination->num_records > 0 ? 
			$skill_model->getSkills('', $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['skills']) ? count($data['skills']) : 0;
		$data['pagination'] = $pagination;
		*/
		/* if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', '', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), '', 0, 100);
		} */
		//$data['skills_out'] = $skill_model->getOutSkills('', 0, 100);

		/* $data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill List';
		$this->getTemplate()->display('skills', $data); */
		
		$data['pageTitle'] = 'Skill List';
		$data['dataUrl'] = $this->url('task=get-home-data&act=skills');
		$this->getTemplate()->display('skills', $data);
	}

	function actionAgent()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		if ('admin' != UserAuth::getRole()) {
			$isAllowed = $skill_model->isAllowedSkill(UserAuth::getCurrentUser(), $skillid);
			if (!$isAllowed) exit;
		}
		$skilldetails = $skill_model->getSkillById($skillid);
		if (empty($skilldetails)) exit;

		$skill_agents0 = '';
		$skill_agents1 = '';
		$skill_agents2 = '';
        $skill_agents3 = '';
		$skill_agents4 = '';
		$skill_agents5 = '';
		$skill_agents6 = '';
		$skill_agents7 = '';
		$skill_agents8 = '';
		$skill_agents9 = '';


		$data['skill_agents'] = $agent_model->getAgentSkill('', $skillid);
		
		$request = $this->getRequest();
		$isUpdate = false;
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$agents = $this->getSubmittedAgents();
			
			$skill_agents0 = $agents->skill_agents0;
			$skill_agents1 = $agents->skill_agents1;
			$skill_agents2 = $agents->skill_agents2;
			$skill_agents3 = $agents->skill_agents3;
			$skill_agents4 = $agents->skill_agents4;
			$skill_agents5 = $agents->skill_agents5;
			$skill_agents6 = $agents->skill_agents6;
			$skill_agents7 = $agents->skill_agents7;
			$skill_agents8 = $agents->skill_agents8;
			$skill_agents9 = $agents->skill_agents9;

			$agents0 = $this->toAgentArray($skill_agents0);
			$agents1 = $this->toAgentArray($skill_agents1);
			$agents2 = $this->toAgentArray($skill_agents2);
			$agents3 = $this->toAgentArray($skill_agents3);
			$agents4 = $this->toAgentArray($skill_agents4);
			$agents5 = $this->toAgentArray($skill_agents5);
			$agents6 = $this->toAgentArray($skill_agents6);
			$agents7 = $this->toAgentArray($skill_agents7);
			$agents8 = $this->toAgentArray($skill_agents8);
			$agents9 = $this->toAgentArray($skill_agents9);

			if ($skill_model->addSkillAgents($skillid, 0, $agents0, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 1, $agents1, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 2, $agents2, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 3, $agents3, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 4, $agents4, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 5, $agents5, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 6, $agents6, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 7, $agents7, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 8, $agents8, $skilldetails)) $isUpdate = true;
			if ($skill_model->addSkillAgents($skillid, 9, $agents9, $skilldetails)) $isUpdate = true;
			if ($skill_model->removeSkillAgents($skillid, $agents0, $agents1, $agents2, $agents3,$agents4, $agents5,
                $agents6, $agents7, $agents8, $agents9, $skilldetails)) $isUpdate = true;
			
			if ($isUpdate) {
			
				$skill_model->notifySkillAgentUpdate($skilldetails->skill_id);
				//$errMsg = 'Agent list updated successfully!!';
				$errType = 0;
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=agent&skillid=$skillid");
				//$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				$data['pageTitle'] = 'Agent List for skill :: '  .$skilldetails->skill_name;
				$data['message'] = 'Agent list updated successfully !!';
				$data['message'] .= '<br /><br /> <a class="btn" href="'.$url.'"><img src="image/arrow_refresh_small.png" class="bottom" border="0" width="16" height="16" /> Reload Agent List</a>';
				$data['msgType'] = 'success';
				$this->getTemplate()->display_popup('popup_message', $data);
			} else {
				$errMsg = 'No change found!!';
			}
		} else {
			if (is_array($data['skill_agents'])) {
				foreach ($data['skill_agents'] as $sag) {
					if ($sag->priority == 1) {
						if (!empty($skill_agents1)) $skill_agents1 .= ',';
						$skill_agents1 .= $sag->agent_id;
					} else if ($sag->priority == 2) {
						if (!empty($skill_agents2)) $skill_agents2 .= ',';
						$skill_agents2 .= $sag->agent_id;
					} else if ($sag->priority == 0) {
						if (!empty($skill_agents0)) $skill_agents0 .= ',';
						$skill_agents0 .= $sag->agent_id;
					} else if ($sag->priority == 3) {
                        if (!empty($skill_agents3)) $skill_agents3 .= ',';
                        $skill_agents3 .= $sag->agent_id;
					}else if ($sag->priority == 4) {
                        if (!empty($skill_agents4)) $skill_agents4 .= ',';
                        $skill_agents4 .= $sag->agent_id;
					}else if ($sag->priority == 5) {
                        if (!empty($skill_agents5)) $skill_agents5 .= ',';
                        $skill_agents5 .= $sag->agent_id;
					}else if ($sag->priority == 6) {
                        if (!empty($skill_agents6)) $skill_agents6 .= ',';
                        $skill_agents6 .= $sag->agent_id;
					}else if ($sag->priority == 7) {
                        if (!empty($skill_agents7)) $skill_agents7 .= ',';
                        $skill_agents7 .= $sag->agent_id;
					}else if ($sag->priority == 8) {
                        if (!empty($skill_agents8)) $skill_agents8 .= ',';
                        $skill_agents8 .= $sag->agent_id;
					} else {
						if (!empty($skill_agents9)) $skill_agents9 .= ',';
						$skill_agents9 .= $sag->agent_id;
					}
				}
			}
		}


		$data['agent_options'] = $agent_model->getAgentNames('', 'Y');
		$data['request'] = $request;
		$data['skillid'] = $skillid;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['predictive_dial_skill'] = strtoupper($skilldetails->qtype) == "P";
		$data['skill_agents0'] = $skill_agents0;
		$data['skill_agents1'] = $skill_agents1;
		$data['skill_agents2'] = $skill_agents2;
		$data['skill_agents3'] = $skill_agents3;
		$data['skill_agents4'] = $skill_agents4;
		$data['skill_agents5'] = $skill_agents5;
		$data['skill_agents6'] = $skill_agents6;
		$data['skill_agents7'] = $skill_agents7;
		$data['skill_agents8'] = $skill_agents8;
		$data['skill_agents9'] = $skill_agents9;
		$data['pageTitle'] = 'Agent List for skill :: '  .$skilldetails->skill_name;
		$this->getTemplate()->display_popup('skill_agent_select', $data);
	}
	
	function toAgentArray($str_service)
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
	
	function getSubmittedAgents()
	{
		$posts = $this->getRequest()->getPost();
		
		$agent = new stdClass();

		$agent->skill_agents0 = isset($posts['skill_agents0']) ?  trim($posts['skill_agents0']) : '';		
		$agent->skill_agents1 = isset($posts['skill_agents1']) ?  trim($posts['skill_agents1']) : '';
		$agent->skill_agents2 = isset($posts['skill_agents2']) ?  trim($posts['skill_agents2']) : '';
		$agent->skill_agents3 = isset($posts['skill_agents3']) ?  trim($posts['skill_agents3']) : '';
		$agent->skill_agents4 = isset($posts['skill_agents4']) ?  trim($posts['skill_agents4']) : '';
		$agent->skill_agents5 = isset($posts['skill_agents5']) ?  trim($posts['skill_agents5']) : '';
		$agent->skill_agents6 = isset($posts['skill_agents6']) ?  trim($posts['skill_agents6']) : '';
		$agent->skill_agents7 = isset($posts['skill_agents7']) ?  trim($posts['skill_agents7']) : '';
		$agent->skill_agents8 = isset($posts['skill_agents8']) ?  trim($posts['skill_agents8']) : '';
		$agent->skill_agents9 = isset($posts['skill_agents9']) ?  trim($posts['skill_agents9']) : '';

		$agent->skill_agents0 = rtrim($agent->skill_agents0, ',');		
		$agent->skill_agents1 = rtrim($agent->skill_agents1, ',');
		$agent->skill_agents2 = rtrim($agent->skill_agents2, ',');
		$agent->skill_agents3 = rtrim($agent->skill_agents3, ',');
		$agent->skill_agents4 = rtrim($agent->skill_agents4, ',');
		$agent->skill_agents5 = rtrim($agent->skill_agents5, ',');
		$agent->skill_agents6 = rtrim($agent->skill_agents6, ',');
		$agent->skill_agents7 = rtrim($agent->skill_agents7, ',');
		$agent->skill_agents8 = rtrim($agent->skill_agents8, ',');
		$agent->skill_agents9 = rtrim($agent->skill_agents9, ',');

		return $agent;
	}
	
	function actionActivate()
	{
		$skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		$status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		
		if ($status == 'Y' || $status == 'N') {
			include('model/MSkill.php');
			$skill_model = new MSkill();
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&page=$pageNum");
			if ($skill_model->updateSkillStatus($skillid, $type, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Skill', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Skill', 'isError'=>true, 'msg'=>'Failed to Update Status', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionUpdate()
	{
		$skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
		$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		if ($type == 'out') {
			$this->saveOutSkill($skillid);
		} else {
			$this->saveSkill($skillid);
		}
	}

	function saveOutSkill($skillid='')
	{
		include('model/MSkill.php');
		include('model/MMusic.php');
		include('lib/FileManager.php');
		$skill_model = new MSkill();

		$data['yes_no_options'] = array('Y'=>'Enable', 'N'=>'Disable');

		$request = $this->getRequest();
		$errMsg = '';
		if ($request->isPost()) {

			$skill = $this->getSubmittedOutSkill($skillid);
			$errMsg = $this->getOutValidationMsg($skill, $skillid);

			if (empty($errMsg)) {
				$oldskill = $this->getInitialOutSkill($skillid, $skill_model);
				if (!empty($oldskill)) {

					$is_success = false;
					$is_welcome_en_file_uploaded = false;
					$is_welcome_bn_file_uploaded = false;
					$is_announcement_en_file_uploaded = false;
					$is_announcement_bn_file_uploaded = false;
					$audit_text = '';

					$resp = FileManager::check_file_for_upload('welcome_file_en', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_welcome_en_file_uploaded = true;
					}

					$resp = FileManager::check_file_for_upload('welcome_file_bn', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_welcome_bn_file_uploaded = true;
					}

					$resp = FileManager::check_file_for_upload('announcement_file_en', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_announcement_en_file_uploaded = true;
					}
					
					$resp = FileManager::check_file_for_upload('announcement_file_bn', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_announcement_bn_file_uploaded = true;
					}


					if (empty($errMsg)) {

						if ($is_welcome_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/E/fl_welcome.wav';
							if (FileManager::save_uploaded_file('welcome_file_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Welcome voice(ENG);';
							}
						}
						if ($is_welcome_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/B/fl_welcome.wav';
							if (FileManager::save_uploaded_file('welcome_file_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Welcome voice(BAN);';
							}
						}
						if ($is_announcement_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/E/fl_unattended.wav';
							if (FileManager::save_uploaded_file('announcement_file_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Announcement(ENG);';
							}
						}
						if ($is_announcement_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/B/fl_unattended.wav';
							if (FileManager::save_uploaded_file('announcement_file_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Announcement(BAN);';
							}
						}

						if ($skill_model->updateOutSkill($oldskill, $skill, $audit_text)) {
							$is_success = true;
						}
					
						if ($is_success) {
							$errMsg = 'Skill updated successfully !!';
						} else {
							$errMsg = 'Failed to update Skill !!';
						}
						
						if ($is_success) {
							$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
							$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
								CONTENT=\"2;URL=$url\">";
						}
					}

				} else {
					$errMsg = 'Invalid Skill !!';
				}
			}

		} else {
			$skill = $this->getInitialOutSkill($skillid, $skill_model);
		}

		$data['skillid'] = $skillid;
		$data['skill'] = $skill;
		$data['request'] = $this->getRequest();
		//$data['skill_options'] = $skill_model->getSkills();
		$data['errMsg'] = $errMsg;
		$data['pageTitle'] = 'Update Outbound Skill : ' . $skill->skill_name;
		$this->getTemplate()->display('skill_out_form', $data);
	}

	function saveSkill($skillid='')
	{
		include('model/MSkill.php');
		include('model/MMusic.php');
		include('lib/FileManager.php');
		include('model/MSkillCrmTemplate.php');
		include('model/MEmail.php');		
		include('conf.extras.php');
		
		$music_model = new MMusic();
		$skill_model = new MSkill();
		$email_model = new MEmail();
		$template_model = new MSkillCrmTemplate();

		$oldskill = $this->getInitialSkill($skillid, $skill_model);
		$oldChatEmail = $this->getInitialEmailSig($skillid, $email_model);
		$isChatSkill = false;
		$oldChatEmailSkillId = !empty($oldChatEmail->chat_skill_id) ? $oldChatEmail->chat_skill_id : "";
		
		if ($oldskill->qtype=='E') {
			$this->saveEmailSkill($skillid, $oldskill, $skill_model);
		}elseif ($oldskill->qtype=='C') {
			$isChatSkill = true;
		}

		//$data['music_directory_options'] = $music_model->getMusicFolders();
		$data['acd_mode_options'] = array('L'=>'Longest Idle', 'A'=>'Random', 'R'=>'Round Robin');
		$data['yes_no_options'] = array('Y'=>'Enable', 'N'=>'Disable');
		//$data['unattended_calls_options'] = array('HU'=>'Hang Up', 'AH'=>'Announce and Hang Up');
		$data['record_all_calls_options'] = array('Y'=>'Enable', 'N'=>'Disable');
		$data['queue_position_options'] = array('Disable'=>'Disable', 'QPosition'=>'Queue Position', 'Simple'=>'Simple');
		$data['callback_options'] = array(''=>'No Callback', 'M'=>'Manual Callback');
		$data['skill_qtype'] = $oldskill->qtype;


		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {

			$skill = $this->getSubmittedSkill($skillid);
			$errMsg = $this->getValidationMsg($skill, $skillid);
			
			if ($isChatSkill){
			    $oldChatEmail->from_name = $skill->from_name;
			    $oldChatEmail->from_email = $skill->from_email;
    			$errMsg2 = $this->getEmailValidationMsg($skill);			
    			if (empty($errMsg)){
    			    $errMsg = $errMsg2;
    			}
			}

			if (empty($errMsg)) {
				
				if (!empty($oldskill)) {

					$is_success = false;
					$is_welcome_en_file_uploaded = false;
					$is_welcome_bn_file_uploaded = false;
					$is_announcement_en_file_uploaded = false;
					$is_announcement_bn_file_uploaded = false;
					$audit_text = '';

					/*$resp = FileManager::check_file_for_upload('welcome_file_en', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_welcome_en_file_uploaded = true;
					}

					$resp = FileManager::check_file_for_upload('welcome_file_bn', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_welcome_bn_file_uploaded = true;
					}

					$resp = FileManager::check_file_for_upload('announcement_file_en', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_announcement_en_file_uploaded = true;
					}

					$resp = FileManager::check_file_for_upload('announcement_file_bn', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a wav file';
					} else if ($resp == FILE_UPLOADED) {
						$is_announcement_bn_file_uploaded = true;
					}
					*/
					if (empty($errMsg)) {
						/*
						if ($is_welcome_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/E/fl_welcome.wav';
							if (FileManager::save_uploaded_file('welcome_file_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Welcome voice(ENG);';
							}
							//echo $target_path . "\nSUC=" . $is_success;
						}
						if ($is_welcome_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/B/fl_welcome.wav';
							if (FileManager::save_uploaded_file('welcome_file_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Welcome voice(BAN);';
							}
						}
						if ($is_announcement_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/E/fl_unattended.wav';
							if (FileManager::save_uploaded_file('announcement_file_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Announcement voice(ENG);';
							}
						}
						if ($is_announcement_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/B/fl_unattended.wav';
							if (FileManager::save_uploaded_file('announcement_file_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Announcement voice(BAN);';
							}
						}
						
						
						//for audit purpose
						*/
						$mu_options[''] = 'RingBackTone';
						if (isset($data['music_directory_options']) && is_array($data['music_directory_options'])) {
							foreach ($data['music_directory_options'] as $music_dir) {
								$mu_options[$music_dir->fl_id] = $music_dir->name;
							}
						}
						$value_options = array(
							'music_directory' => $mu_options,
							'welcome_voice' => $data['yes_no_options'],
							'acd_mode' => $data['acd_mode_options'],
							'popup_url' => '',
							'record_all_calls' => $data['yes_no_options'],
							'callback_type' => $data['callback_options'],
							'caller_priority' => $data['yes_no_options'],
							'TTS' => $data['yes_no_options'],
							//'unattended_calls_action' => $data['unattended_calls_options'],
							'auto_answer' => $data['yes_no_options']
						);
						/*
						$isTTSUpdate = false;						
						if ($extra->voice_synth_module) {
							if ($this->saveSkillTTSValues($skillid, $skill)) {
								$is_success = true;
								$isTTSUpdate = true;
							}
							
							if (($oldskill->TTS != $skill->TTS || $isTTSUpdate) && $skill->TTS == 'Y') {
								$this->reloadTTS($skillid);
							}
						}
						*/
						$isTTSUpdate=false;
						if ($skill_model->updateSkill($oldskill, $skill, $audit_text, $value_options, false, $isTTSUpdate)) {
							$is_success = true;
						}
						
						if ($email_model->updateChatSkillEmail($skillid, $skill->from_name, $skill->from_email, $oldChatEmailSkillId)){
						    $is_success = true;
						}						
						
						if ($is_success) {
							$errType = 0;
							$errMsg = 'Skill updated successfully !!';
						} else {
							$errMsg = 'No change found !!';
						}
					
						if ($is_success) {
							$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
							$data['metaText'] = "<META HTTP-EQUIV=\"refresh\"
								CONTENT=\"2;URL=$url\">";
						}
					}

				} else {
					$errMsg = 'Invalid Skill !!';
				}
			}

		} else {
			$skill = $this->getInitialSkill($skillid, $skill_model);
		}

		$data['skillid'] = $skillid;
		$data['skill'] = $skill;
		$data['chat_email'] = $oldChatEmail;
		$data['request'] = $this->getRequest();
		//$data['skill_options'] = $skill_model->getSkills();
		$data['template_opt'] = $template_model->getTemplates();
		$data['tts_support'] = $extra->voice_synth_module;
		if ($extra->voice_synth_module) $data['ttstexts'] = $this->getSkillTTSValues($skillid);
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update Skill : ' . $skill->skill_name;
		$data['smi_selection'] = 'skills_';
		$data['topMenuItems'] = array(				
				array('href'=>'task=skills&act=upload-welcome-voice2&skill='.$skillid,  'img'=>'fa fa-play', 'color'=>'success', 'label'=>'Welcome Voice', 'class'=>"lightboxWIFR",'dataattr'=>array('w'=>'900px','h'=>'auto')),
				array('href'=>'task=skills&act=upload-hold-voice&skill='.$skillid,  'img'=>'fa fa-play', 'color'=>'success','label'=>'Music on Hold', 'class'=>"lightboxWIFR",'dataattr'=>array('w'=>'900px','h'=>'auto')),
				array('href'=>'task=skills&act=update&skillid='.$skillid,  'img'=>'fa fa-gear','color'=>'success', 'label'=>'Properties', 'class'=>"","property"=>array("disabled"=>"disabled")),
				array('href'=>'task=moh-filler&act=queue-timeout&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Queue Timeout','class'=>""),
				array('href'=>'task=moh-filler&act=config&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Filler','class'=>"")
		);
		$this->getTemplate()->display('skill_form', $data);
	}
	
	function reloadTTS($skillid)
	{
		file_put_contents('/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp', 'Q:'.$skillid."\n", FILE_APPEND);
		//file_put_contents('E:/temp/tts.tmp', 'Q:'.$skillid."\n", FILE_APPEND);
	}
	
	function getSkillTTSValues($skillid)
	{
		$response = null;
		if (file_exists($this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/TTS.txt')) {
			$response = unserialize(file_get_contents($this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/TTS.txt'));
		}
		
		if (!is_array($response)) {
			$response = array(
				'tts_fl_welcome' => '',
				'tts_fl_unattended' => ''
			);

			$this->setSkillTTSValues($skillid, (object) $response);
			
			$response = array(
				'fl_welcome' => '',
				'fl_unattended' => ''
			);
		}
		return $response;
	}
	
	function saveSKillTTSValues($skillid, $skill)
	{
		$oldvalues = $this->getSkillTTSValues($skillid);
		
		if ($oldvalues['fl_welcome'] != $skill->tts_fl_welcome || $oldvalues['fl_unattended'] != $skill->tts_fl_unattended) {
			$this->setSkillTTSValues($skillid, $skill);
			return true;
		}
		return false;
	}
	
	function setSkillTTSValues($skillid, $skill)
	{
		$response = array(
			'fl_welcome' => $skill->tts_fl_welcome,
			'fl_unattended' => $skill->tts_fl_unattended
		);
		file_put_contents($this->getTemplate()->file_upload_path . 'Q/' . $skillid . '/TTS.txt', serialize($response));
	}

	function saveEmailSkill($skillid, $oldskill, $skill_model)
	{
		if ($oldskill->qtype != 'E') {
			exit;
		} else {
			$oldskill->emails = $skill_model->getEmails($skillid, 'string');
		}

		$data['acd_mode_options'] = array('L'=>'Longest Idle', 'A'=>'Random', 'R'=>'Round Robin');
		$data['yes_no_options'] = array('Y'=>'Enable', 'N'=>'Disable');

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {

			$skill = $this->getSubmittedEmailSkill($skillid);
			$errMsg = $this->getValidationMsg($skill, $skillid);
			if (empty($errMsg)) {
				$duplicate = $skill_model->getDuplicateSkillEmail($oldskill, $skill);
				if (!empty($duplicate)) $errMsg = 'Email ' . $duplicate . ' already exist !!';
			}
			if (empty($errMsg)) {
				
				if (!empty($oldskill)) {

					$is_success = false;

					$value_options = array(
						'acd_mode' => $data['acd_mode_options'],
						'popup_url' => ''
					);
					if ($skill_model->updateEmailSkill($oldskill, $skill, $value_options)) {
						$is_success = true;
					}
						
					if ($is_success) {
						$errMsg = 'Skill updated successfully !!';
					} else {
						$errMsg = 'No change found !!';
					}
					
					if ($is_success) {
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
							CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'Invalid Skill !!';
				}
			}

		} else {
			$skill = $oldskill;
		}

		$data['skillid'] = $skillid;
		$data['skill'] = $skill;
		$data['request'] = $this->getRequest();
		//$data['skill_options'] = $skill_model->getSkills();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update Skill : ' . $skill->skill_name;
		$data['smi_selection'] = 'skills_';
		$this->getTemplate()->display('skill_email_form', $data);
	}
	
	function getInitialOutSkill($skillid, $skill_model)
	{
		$skill = null;

		$skill = $skill_model->getSkillById($skillid, 'out');
		if (empty($skill)) {
			exit;
		} else {
			$file_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid;
			if (!file_exists($file_path)) {
					mkdir($file_path, 0755, true);//mkdir($file_path, 0700, true); for PHP >= 5
			}

			$language_path = $file_path . '/E';
			if (!file_exists($language_path)) {
					mkdir($language_path, 0755, true);
			}

			$language_path = $file_path . '/B';
			if (!file_exists($language_path)) {
					mkdir($language_path, 0755, true);
			}
			
			//$skill->off_hour_announcement_file = file_exists($file_path.'/fl_unattended.wav') ? 'Y' : 'N';
		}
		return $skill;
	}

	function getInitialSkill($skillid, $skill_model)
	{
		$skill = null;

		$skill = $skill_model->getSkillById($skillid);
		if (empty($skill)) {
			exit;
		} else {
			$file_path = $this->getTemplate()->file_upload_path . 'Q/' . $skillid;
			if (!file_exists($file_path)) {
					mkdir($file_path, 0755, true);//mkdir($file_path, 0700, true); for PHP >= 5
			}

			$language_path = $file_path . '/E';
			if (!file_exists($language_path)) {
					mkdir($language_path, 0755, true);
			}

			$language_path = $file_path . '/B';
			if (!file_exists($language_path)) {
					mkdir($language_path, 0755, true);
			}
			//$skill->off_hour_announcement_file = file_exists($file_path.'/fl_unattended.wav') ? 'Y' : 'N';
		}
		return $skill;
	}
	
	function getInitialEmailSig($skillid, $email_model)
	{
		$skill = new stdClass();
		$skill->from_name = "";
		$skill->from_email = "";
		$skill->chat_skill_id = "";

		$emailData = $email_model->getSkillEmail($skillid);
		if (!empty($emailData)) {
		    $skill->chat_skill_id = $emailData->skill_id;
		    $skill->from_name = $emailData->from_name;
		    $skill->from_email = $emailData->from_email;
		}
		return $skill;
	}

	function getSubmittedSkill($skillid)
	{
		$posts = $this->getRequest()->getPost();
		$skill = new stdClass();

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$skill->$key = trim($val);
			}
		}
		if ($skill->sel_find_last_agent == 0) $skill->find_last_agent = 0;
		if (empty($skill->max_calls_in_queue)) $skill->max_calls_in_queue = 0;
		if (empty($skill->max_wait_time)) $skill->max_wait_time = 0;
		if (empty($skill->agent_ring_timeout)) $skill->agent_ring_timeout = 0;
		if (empty($skill->delay_between_calls)) $skill->delay_between_calls = 0;
		if (empty($skill->queue_position_time)) $skill->queue_position_time = 0;
		$skill->skill_id = $skillid;
		return $skill;
	}

	function getSubmittedEmailSkill($skillid)
	{
		$posts = $this->getRequest()->getPost();
		$skill = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$skill->$key = trim($val);
			}
		}
		$cti->emails = isset($cti->emails) ? rtrim($cti->emails, ',') : '';
		if ($skill->sel_find_last_agent == 0) $skill->find_last_agent = 0;
		if (empty($skill->max_calls_in_queue)) $skill->max_calls_in_queue = 0;
		if (empty($skill->agent_ring_timeout)) $skill->agent_ring_timeout = 0;
		if (empty($skill->delay_between_calls)) $skill->delay_between_calls = 0;
		$skill->skill_id = $skillid;
		return $skill;
	}

	function getSubmittedOutSkill($skillid)
	{
		$posts = $this->getRequest()->getPost();
		$skill = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$skill->$key = trim($val);
			}
		}
		if (empty($skill->max_out_calls_per_agent)) $skill->max_out_calls_per_agent = 0;
		if (empty($skill->service_level)) $skill->service_level = 0;
		//if (empty($skill->auto_answer)) $skill->auto_answer = 0;
		if (empty($skill->anouncement_timeouot)) $skill->anouncement_timeouot = 0;
		$skill->skill_id = $skillid;
		return $skill;
	}

	function getValidationMsg($skill, $skillid='')
	{
		$err = '';
		if (empty($skill->acd_mode)) $err = "Select Call Routing";
		if (empty($skill->skill_name)) $err = "Provide Skill Name";		
		return $err;
	}

	function getOutValidationMsg($skill, $skillid='')
	{
		$err = '';
		if (empty($skill->skill_name)) $err = "Provide Skill Name";		
		return $err;
	}
	
	function getEmailValidationMsg($service)
	{
	    //if (empty($service->from_email)) return "Provide email address";
	    //if (empty($service->from_name)) return "Provide email recipient name";
	    if (!empty($service->from_email) && !filter_var($service->from_email, FILTER_VALIDATE_EMAIL)) return "Provide valid email address";
	    if (!empty($service->from_name) && !preg_match("/^[0-9a-zA-Z_@ .]{1,50}$/", $service->from_name)) return "Provide valid email recipient name";
	
	    return '';
	}

	function isValidURL($url)
	{
		 return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	} 	

	function findexts ($filename)
	{
		$filename = strtolower($filename) ;
		$exts = explode(".", $filename) ;
		$n = count($exts)-1;
		$exts = $exts[$n];
		return $exts;
	}
	
	function actionUploadWelcomeVoice(){
	
		$skill_id=$this->getRequest()->getGet('skill');
		$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
		AddModel("MMOHFiller");
		AddModel("MLanguage");
		$mh=new MMOHFiller();
		$data=array();
		$data['fullload']=true;
		$data['skill']=$skill_id;
		$data['pageTitle'] = 'Upload Welcome Voice';
		$data['buttonTitle'] = 'Welcome Voice';
		//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
		AddHiddenFields("skill", $skill_id);
		$maxFileSize = $this->getTemplate()->maxFileSize;
		$isError = false;
		$isUploadable = false;
		$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/";
		$fileExt = $this->getTemplate()->voice_store_file_extension;
		$filename = "welcome";
		$filenameWithExt = $filename.".".$fileExt;
		
		if($this->getRequest()->isPost()){
			
			if ($_FILES["uploadfile"]["error"] != UPLOAD_ERR_NO_FILE){
				if($_FILES["uploadfile"]["error"] ==0 && ($_FILES["uploadfile"]["size"] > 0)){
					$value = $_FILES["uploadfile"]["name"];
					$target_file = basename($value); 
					$fileExt = explode(".", $target_file);
					$fileExt = strtolower(end($fileExt));
					$fileBaseName = basename($value,".".$fileExt);
					
					$fileProp = exec('file -b '. $_FILES["uploadfile"]["tmp_name"]);
					
					$check16Bit = strpos($fileProp, "16 bit");
					$checkMono = strpos($fileProp, "mono");
					$checkHz = strpos($fileProp, "8000 Hz");
					
					if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) && ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
					// if( true ){ 
					
						// size in bytes, 1000000 bytes = 1 MB
						if ($_FILES["uploadfile"]["size"] <= $maxFileSize) {
							$fileupload['tmp_name'] = $_FILES["uploadfile"]["tmp_name"];
							$fileupload['name'] = $_FILES["uploadfile"]["name"];
							$isUploadable = true;
						}else{
							$isError = true;
							$sizeInMB = $maxFileSize / 1000000;
							AddError("Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB");
						}
					}else{
						$isError = true;
						AddError("Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
					}
				}
			}
			else {
				$isError = true;
				// AddError("[$lan][$key] No file selected");
			}
			
			if(!$isError){ 
				if (!empty($fileupload)){ 
					$fl=$target_path;
					if(!is_dir($fl)){
						mkdir($fl,0755,true);
					}
					if(file_exists($fl."welcome.".$fileExt)){
						unlink($fl."welcome.".$fileExt);
						unlink($fl."welcome.alaw");
						unlink($fl."welcome.ulaw");
						unlink($fl."welcome.txt");
					} 
					if(move_uploaded_file($fileupload['tmp_name'], $fl."welcome.".$fileExt)){
						// create to auto genarated file
						$autoGenFileName = $fl."welcome"; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$fileupload['name']);
						fclose($file);
						$isError = false;
					}else{
						$isError =true;
					}
				}
				if(!$isError){
					$mh->notifyIVRVoiceFileUpdate($skill_id, 'welcome');
					AddInfo("File uploaded successfully");
					$this->getTemplate()->display_popup_msg($data);
				}
			}
		} 
		$fle=$target_path."welcome.".$this->getTemplate()->voice_store_file_extension; 
		$data['fileInfo'] = [
			"file_name" => "welcome",
			"file_path" => site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&fileName=welcome"),
			"file_dir" => $target_path."/",
			"file_delete_url" => file_exists($fle) ? $this->url("task=confirm-response&act=delete-ivr-welcome-file&ivrtype=skill&pro=da&lan=EN&ivrid=".$skill_id) : ""
		];
		$this->getTemplate()->display_popup('mod-upload-welcome', $data);
	
	
	}
	
	/*
	 * Temporarily done for welcome without language 
	 */	
	
	function actionUploadWelcomeVoice2(){
	
		$skill_id=$this->getRequest()->getGet('skill');
		$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
		AddModel("MMOHFiller");
		AddModel("MLanguage");
		$mh=new MMOHFiller();
		$data=array();
		$data['fullload']=true;
		$data['skill']=$skill_id;
		$data['pageTitle'] = 'Upload Welcome Voice';
		$data['buttonTitle'] = 'Welcome Voice';
		//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
		AddHiddenFields("skill", $skill_id);
		$maxFileSize = $this->getTemplate()->maxFileSize;
		$isError=false;
		$isUploadable = false;
		$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/";

		$fileExt = $this->getTemplate()->voice_store_file_extension;
		$filename = "welcome";
		$filenameWithExt = $filename.".".$fileExt;

		if($this->getRequest()->isPost()){
			
			if ($_FILES["uploadfile"]["error"] != UPLOAD_ERR_NO_FILE){
				if($_FILES["uploadfile"]["error"] ==0 && ($_FILES["uploadfile"]["size"] > 0)){
					$value = $_FILES["uploadfile"]["name"];
					$target_file = basename($value); 
					$fileExt = explode(".", $target_file);
					$fileExt = strtolower(end($fileExt));
					$fileBaseName = basename($value,".".$fileExt);
					
					$fileProp = exec('file -b '. $_FILES["uploadfile"]["tmp_name"]);
					
					$check16Bit = strpos($fileProp, "16 bit");
					$checkMono = strpos($fileProp, "mono");
					$checkHz = strpos($fileProp, "8000 Hz");
					
					if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) && ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
					// if( true ){ 
					
						// size in bytes, 1000000 bytes = 1 MB
						if ($_FILES["uploadfile"]["size"] <= $maxFileSize) {
							$fileupload['tmp_name'] = $_FILES["uploadfile"]["tmp_name"];
							$fileupload['name'] = $_FILES["uploadfile"]["name"];
							$isUploadable = true;
						}else{
							$isError = true;
							$sizeInMB = $maxFileSize / 1000000;
							AddError("Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB");
						}
					}else{
						$isError = true;
						AddError("Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
					}
				}
			}
			else {
				$isError = true;
				// AddError("[$lan][$key] No file selected");
			}
			
			if(!$isError){ 
				if (!empty($fileupload)){ 
					$fl=$target_path;
					if(!is_dir($fl)){
						mkdir($fl,0755,true);
					}
					if(file_exists($fl."welcome.".$fileExt)){
						unlink($fl."welcome.".$fileExt);
						unlink($fl."welcome.alaw");
						unlink($fl."welcome.ulaw");
						unlink($fl."welcome.txt");
					} 
					if(move_uploaded_file($fileupload['tmp_name'], $fl."welcome.".$fileExt)){
						// create to auto genarated file
						$autoGenFileName = $fl."welcome"; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$fileupload['name']);
						fclose($file);
						$isError = false;
					}else{
						$isError =true;
					}
				}
				if(!$isError){
					$mh->notifyIVRVoiceFileUpdate($skill_id, 'welcome');
					AddInfo("File uploaded successfully");
					$this->getTemplate()->display_popup_msg($data);
				}
			}
		} 
		$fle=$target_path."welcome.".$this->getTemplate()->voice_store_file_extension; 
		$data['fileInfo'] = [
			"file_name" => "welcome",
			"file_path" => site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&fileName=welcome"),
			"file_dir" => $target_path."/",
			"file_delete_url" => file_exists($fle) ? $this->url("task=confirm-response&act=delete-ivr-welcome-file&ivrtype=skill&pro=da&lan=EN&ivrid=".$skill_id) : ""
		];
		$this->getTemplate()->display_popup('mod-upload-welcome', $data);
	
	}
	
	function actionUploadHoldVoice(){		
		$skill_id=$this->getRequest()->getGet('skill');
		AddModel("MSkill");
		$skill_obj=new MSkill();
		$skill=$skill_obj->getSkillById($skill_id);
		
		$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
		AddModel("MMOHFiller");
		AddModel("MLanguage");
		$mh=new MMOHFiller();
		$data=array();
		$data['fullload']=true;		
		$data['skill']=$skill_id;
		$data['pageTitle'] = 'Upload Music on Hold for skill::'.$skill->skill_name;
		//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);	
		AddHiddenFields("skill", $skill_id);
		$isError=false;		
		$counter=0;
		
		if($this->getRequest()->isPost()){ 
			if($this->UploadMusicOnHoldFile($skill_id, 0, $_FILES["moh1"])){
				$counter++;
				$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh0');
			}
			if($this->UploadMusicOnHoldFile($skill_id, 1, $_FILES["moh2"])){
				$counter++;
				$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh1');
			}
			if($this->UploadMusicOnHoldFile($skill_id, 2, $_FILES["moh3"])){
				$counter++;
				$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh2');
			}
			//if ($counter>0) {
				//$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh');
			//}
			AddInfo($counter." file uploaded");
		}
		$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/MOH/";
		$supportExtArray = $this->getTemplate()->voice_file_supported_extn; 
		if (!empty($supportExtArray) && count($supportExtArray) > 0){
			$isTypeFmoh1 = true;
			$isTypeFmoh2 = true;
			$isTypeFmoh3 = true; 
			foreach ($supportExtArray as $extType){
				if($isTypeFmoh1 && file_exists($target_path."moh0.".$extType)){
					$data['f_moh1'] = site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh0");
					$data['f_moh1_filename']= "moh0.".$extType;
					$isTypeFmoh1 = false;
				}
				if($isTypeFmoh2 && file_exists($target_path."moh1.".$extType)){
					$data['f_moh2']= site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh1");
					$data['f_moh2_filename']= "moh1.".$extType;
					$isTypeFmoh2 = false;
				}
				if($isTypeFmoh3 && file_exists($target_path."moh2.".$extType)){
					$data['f_moh3']= site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh2");
					$data['f_moh3_filename']= "moh2.".$extType;
					$isTypeFmoh3 = false;
				}
			}
		}else{
			if(file_exists($target_path."moh0.".$this->getTemplate()->voice_store_file_extension)){
				$data['f_moh1']= site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh0");
			}
			if(file_exists($target_path."moh1.".$this->getTemplate()->voice_store_file_extension)){
				$data['f_moh2']= site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh1");
			}
			if(file_exists($target_path."moh2.".$this->getTemplate()->voice_store_file_extension)){
				$data['f_moh3']= site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$skill_id}&lang=MOH&fileName=moh2");
			}
		}
		
		$this->getTemplate()->display_popup('mod-upload-hold-voice', $data);
		
		
	}
	private function UploadMusicOnHoldFile($skill_id,$filenumber,$file){ 
		$maxFileSize = $this->getTemplate()->maxFileSize;
		$filename="moh{$filenumber}.".$this->getTemplate()->voice_store_file_extension;
		$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/MOH/";
		if(!is_dir($target_path)){
			mkdir($target_path,0755,true);
		}
		//AddError($file["error"] . " $target_path $filename");		
		if($file["error"]==0){
			$target_file = basename($file["name"]);
			$fileExt = pathinfo($target_file,PATHINFO_EXTENSION);
			$filename = "moh{$filenumber}";
			$filenameWithExt = $filename.".".$fileExt;

			$fileProp = exec('file -b '. $file["tmp_name"]);
							
			$check16Bit = strpos($fileProp, "16 bit");
			$checkMono = strpos($fileProp, "mono");
			$checkHz = strpos($fileProp, "8000 Hz");

			if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) &&  ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){
			// if(true){	
				if ($file["size"] <= $maxFileSize) {
					if(file_exists($target_path.$filenameWithExt)){
						unlink($target_path.$filenameWithExt);
						unlink($target_path.$filename.".ulaw");
						unlink($target_path.$filename.".alaw");
						unlink($target_path.$filename.".txt");	
					}
					if(!move_uploaded_file($file["tmp_name"],$target_path.$filenameWithExt)){ 
						AddError(" File $filenumber: Sorry, audio file is not uploaded.");
					}else {

						// create to auto genarated file
						$autoGenFileName = $target_path.$filename; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$filenameWithExt);
						fclose($file);
						return  true;
					}
					
				}else{					
					AddError(" File $filenumber: Sorry, audio file is too large.");
				}
			}else{
				AddError("File $filenumber: Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
			}
			
		}
		return false;		
		
	}
}
