<?php

define('MAX_BRANCH', 21);

class Ivrs extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();

		$data['ivrs'] = $ivr_model->getIvrs('', 0, 100);

		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'IVR List';
		$this->getTemplate()->display('ivrs', $data);
	}

	function actionConfig()
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('model/MIvrService.php');
		$ivr_model = new MIvr();
		$ivr_service_model = new MIvrService();
		$skill_model = new MSkill();

		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		$data['ivr_details'] = $ivr_model->getIVRDetails($ivrid);
		if (empty($data['ivr_details'])) {
			$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>$ivrid, 'branch'=>$ivrid, 'event'=>'AN', 'event_key'=>'', 'text'=>'IVR Root'));
			$data['ivr_details'] = $ivr_model->getIVRDetails($ivrid);
		}
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['sr_options'] = $ivr_service_model->getServiceOptions();
		$data['pageTitle'] = 'Update IVR :: ' . $ivr->ivr_name;
		$data['ivrid'] = $ivrid;
		$data['ivr'] = $ivr;
		$this->getTemplate()->display('ivr_config', $data);
	}

	function getEventName($event)
	{
		$events = array('AN'=>'Announcement', 'RV'=>'Read Value', 'GV'=>'Get Value', 'SV'=>'Set Value', 'IF'=>'Compare', 'DH'=>'Day Hour', 'PF'=>'Get DTMF', 'ID'=>'Get ID', 'GO'=>'Go', 'SQ'=>'Skill', 'SR'=>'Service Request', 'MC'=>'Module Call');
		$ename = isset($events[$event]) ? $events[$event] : $event;
		return $ename;
	}

	function getEventValueName($event)
	{
		$events = array('AN'=>'Text', 'RV'=>'Text', 'GV'=>'URL', 'SV'=>'Text', 'IF'=>'Text', 'DH'=>'Day Hour', 'PF'=>'Text', 'ID'=>'Text', 'GO'=>'Text', 'SQ'=>'Text', 'SR'=>'Text', 'MC'=>'Text');
		$ename = isset($events[$event]) ? $events[$event] : $event;
		return $ename;
	}
	
	function getSkillName($skill_model, $skillid)
	{
		$sq_options = $skill_model->getSkillOptions();
		$sname = isset($sq_options[$skillid]) ? $sq_options[$skillid] : $skillid;
		return $sname;
	}

	function getServiceName($ivr_service_model, $dcode)
	{
		$sq_options = $ivr_service_model->getServiceOptions();
		$sname = isset($sq_options[$dcode]) ? $sq_options[$dcode] : $dcode;
		return $sname;
	}
	
	function actionDeletenode()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();

		$menu = isset($_REQUEST['menu']) ? trim($_REQUEST['menu']) : '';

		$ivr_children = $ivr_model->getSubTreeByBranch($menu);
	
		if (is_array($ivr_children)) {
			$ivr_id = substr($menu, 0, 1);
			$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/';
			foreach ($ivr_children as $node) {
				if ($node->event=='PF') {
					if (!empty($node->event_key)) {
						$file = $delete_file_path . $node->event_key . '.wav';
						include('lib/FileManager.php');
						if (file_exists($file)) FileManager::unlink_file($file);
					}
				}
			}
		}

		$status = $ivr_model->removeBranch($menu);
	
		if ($status) {
			/*
			$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
			$login_id = UserAuth::getUserId();
			$updated_fields = array(array('IVR='.$ivrname.';', '-', '-'));
			ActivityLogManager::logInsert($login_id, "IVR", "D", $updated_fields, $db_manager);
			*/
			echo "{'success':true}";
		} else {
			echo "{'success':false}";
		}
	}
	
	function actionUpdatenode()
	{
		$this->updateNode('update');
	}
	
	function actionAddnode()
	{
		$this->updateNode('add');
	}

	function actionMovenode()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();
		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$from_node = '';
		$to_node = '';
		$errMsg = '';
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		
		if (isset($_POST['movenode'])) {
			$from_node = isset($_POST['from_node']) ? trim($_POST['from_node']) : '';
			$to_node = isset($_POST['to_node']) ? trim($_POST['to_node']) : '';
			$to_node = strtolower($to_node);
			
			$valid_dtmfs = array('.', '*', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
			
			if (strlen($to_node) < 2) $errMsg = 'Provide valid new node';
			if (empty($to_node)) $errMsg = 'Provide new node';
			if (empty($from_node)) $errMsg = 'Provide source node'; 
			
			if (empty($errMsg)) {
				$to_dtmf = substr($to_node, -1);
				if (!in_array($to_dtmf, $valid_dtmfs)) $errMsg = 'Provide valid new node';
			}
			
			if (empty($errMsg)) {
				if (substr($from_node, 0, 1) != $ivrid) $errMsg = 'Provide valid source node';
				if (substr($to_node, 0, 1) != $ivrid) $errMsg = 'Provide valid new node';
			}
			
			if (empty($errMsg)) {
				$from_tree = $ivr_model->getIVRDetailsByBranch($from_node);
				if (empty($from_tree)) $errMsg = 'Provide valid source node';
			}

			if (empty($errMsg)) {
				//$is_leaf_to_node = $ivr_model->isLeafNode($to_node);
				//if (!$is_leaf_to_node) $errMsg = 'Destination node should be a leaf node';
				$to_tree = $ivr_model->getIVRDetailsByBranch($to_node);
				if (!empty($to_tree)) $errMsg = 'New node already exist';
			}
			
			if (empty($errMsg)) {
				$to_tree = $ivr_model->getIVRDetailsByBranch(substr($to_node, 0, -1));
				if (empty($to_tree)) $errMsg = 'Provide valid new node';
			}
			
			if (empty($errMsg)) {
				$from_node_length = strlen($from_node);
				$to_node_length = strlen($to_node);
				if ($from_node_length < $to_node_length) {
					if (substr($to_node, 0, $from_node_length) == $from_node) {
						$errMsg = 'It is not possible to move these nodes';
					}
				}
			}
			if (empty($errMsg)) {
				if ($ivr_model->moveNode($from_node, $to_node, $ivr->ivr_name)) {
				
					$data['message'] = 'Node moved Successfully !!';
					$data['msgType'] = 'success';
					$data['refreshParent'] = true;
					$this->getTemplate()->display_popup('popup_message', $data);
					
				} else {
					$errMsg = 'Failed to move the node';
				}
			}
		}
		$data['request'] = $this->getRequest();
		$data['ivr'] = $ivr;
		$data['ivrid'] = $ivrid;
		$data['from_node'] = $from_node;
		$data['to_node'] = $to_node;
		$data['errMsg'] = $errMsg;
		
		$data['pageTitle'] = 'Move IVR Node :: ' . $ivr->ivr_name;
			
		$this->getTemplate()->display_popup('ivr_move_menu_item', $data);
	}
		
	function updateNode($type)
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('lib/FileManager.php');
		include('model/MIvrService.php');
		$ivr_service_model = new MIvrService();
		$ivr_model = new MIvr();
		$skill_model = new MSkill();

		$branch_id = isset($_REQUEST['menu']) ? trim($_REQUEST['menu']) : '';
		$pevent = isset($_REQUEST['pevent']) ? $_REQUEST['pevent'] : '';
		if ($type == 'update' && empty($pevent)) {
			$parent_details = $ivr_model->getParent($branch_id);
			if (!empty($parent_details)) $pevent = $parent_details->event;
		}
		
		if (isset($_POST['editivr'])) {
			$method = $type == 'update' ? 'upost' : 'apost';
			$data = $this->loadIVRNodeData($method, $branch_id, $ivr_model);


			//if (empty($data['errMsg'])) {
			//}
			
			if (empty($data['errMsg'])) {
				$file_uploaded = false;
				$en_file_uploaded = false;
				$bn_file_uploaded = false;
				$err = '';
				$audit_text = '';
				$ivr_menu = $ivr_model->getIVRDetailsByBranch($branch_id);
				//var_dump($branch_id);
				//var_dump($ivr_menu);
				//exit;
				if ($data['event'] == 'PF' || $data['event'] == 'AN' || $data['event'] == 'ID' || $data['event'] == 'GV') {
					
					if ($type == 'update') {
						if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') {
							$file_name = $ivr_menu->event_key;
						} else {
							$file_name = 'fl_' .  time();
						}
					} else {
						$file_name = 'fl_' .  time();
					}

					$event_key = $file_name;

					if($data['event'] == 'PF') {
						$srv_file_name = 'event_pf';
						$voice_name = 'Get DTMF';
					} elseif($data['event'] == 'AN') {
						$srv_file_name = 'event_an';
						$voice_name = 'Announcement';
					} elseif($data['event'] == 'ID') {
						$srv_file_name = 'event_id';
						$voice_name = 'Get ID';
					} else if($data['event'] == 'GV') {
						$srv_file_name = 'event_at';
						$voice_name = 'Get Value';
					}

					$srv_file_name_en = $srv_file_name . '_en';
					$srv_file_name_bn = $srv_file_name . '_bn';
					$err = '';
					
					if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
						$ivr_id = substr($branch_id, 0, 1);
						$extention = $this->findexts(basename( $_FILES[$srv_file_name_en]['name']));
						if ($extention == 'wav') {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/E/' . $file_name . '.' . $extention;
							if (FileManager::save_uploaded_file($srv_file_name_en, $target_path)) {
								$file_uploaded = true;
								$en_file_uploaded = true;
								$audit_text .= "$voice_name voice(ENG);";
							}
						} else {
							$err = 'Select only WAV file';
						}
					}
					
					if (empty($err)) {
						if (!empty($_FILES[$srv_file_name_bn]) && $_FILES[$srv_file_name_bn]["error"] <= 0) {
							$ivr_id = substr($branch_id, 0, 1);
							$extention = $this->findexts(basename( $_FILES[$srv_file_name_bn]['name']));
							if ($extention == 'wav') {
								$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/B/' . $file_name . '.' . $extention;
								if (FileManager::save_uploaded_file($srv_file_name_bn, $target_path)) {
									$file_uploaded = true;
									$bn_file_uploaded = true;
									$audit_text .= "$voice_name voice(BAN);";
								}
							} else {
								$err = 'Select only WAV file';
							}
						}
					}
					
					if (!$file_uploaded) $event_key = '';

				} else {
					//if ($data['event'] == 'RV') {
						//$event_key = '';
					//} else {
					if ($data['event'] != 'SV' && $data['event'] != 'IF') {
						$event_key_index = 'event_'.strtolower($data['event']);
						$event_key = $data[$event_key_index];
					} else {
						$event_key = '';
					}
					//}
				}
				
				if (empty($err)) {
					if ($type == 'update') {
						$new_branch = substr($branch_id, 0, -1) . $data['dtmf'];
						if ($data['event']=='DH') $data['text'] = str_replace(" ", "", $data['text']);
						$ivr_info_for_update = array('ivr_id'=>substr($branch_id, 0, 1), 'event'=>$data['event'], 'text'=>$data['text']);
						if (strlen($branch_id) > 1) $ivr_info_for_update['branch'] = $new_branch;

						if ($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'ID' && $data['event'] != 'GV' && $data['event'] != 'SV' && $data['event'] != 'IF') {
							$ivr_info_for_update['event_key'] = $event_key;
						} else {
							if ($file_uploaded == true) {
								$ivr_info_for_update['event_key'] = $event_key;
							} else {
								if(!empty($ivr_menu)) {
									if ($ivr_menu->event != $data['event']) $ivr_info_for_update['event_key'] = '';
								}
							}
						}
			
						//Delete Previous Files
						/*
						if (isset($ivr_info_for_update['event_key'])) {
							if (!empty($ivr_menu)) {
								if (!empty($ivr_menu->event_key)) {
									$ivr_id = substr($branch_id, 0, 1);
									$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/' . $ivr_menu->event_key . '.wav';
									if(file_exists($delete_file_path)) FileManager::unlink_file($delete_file_path);
								}
							}
						}
						*/
						if ($ivr_menu->event != $data['event']) {
							$ivr_id = substr($branch_id, 0, 1);
							if (!$en_file_uploaded) {
								$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/E/' . $ivr_menu->event_key . '.wav';
								if(file_exists($delete_file_path)) FileManager::unlink_file($delete_file_path);
							}
							if (!$bn_file_uploaded) {
								$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/B/' . $ivr_menu->event_key . '.wav';
								if(file_exists($delete_file_path)) FileManager::unlink_file($delete_file_path);
							}
						}
			
						$status = $ivr_model->updateIVRDetailsByBranch($branch_id, $ivr_info_for_update);
						//=======================log insertion===========================//
						//ActivityLogManager::logInsert($login_id, "IVR", "U", $data['updated_fields'], $db_manager);
						//===============================================================//
						if ($status && $new_branch != $branch_id) {
							$ivr_model->updateChildrenBranch($branch_id, $new_branch);
						}

						$data['pageTitle'] = 'Edit IVR Menu';
						$data['refreshParent'] = true;
						if ($status || $file_uploaded) {


							$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
							if ($data['old_event'] != $data['event']) $audit_text = $ivr_model->addAuditText($audit_text, 
								"Event=".$this->getEventName($data['old_event'])." to ".$this->getEventName($data['event']));
							$fld_txt = $this->getEventValueName($data['event']);
							if ($data['old_text'] != $data['text']) $audit_text = $ivr_model->addAuditText($audit_text, 
								"$fld_txt=".$data['old_text']." to ".$data['text']);
							if ($new_branch != $branch_id) {
								$old_dtmf = substr($branch_id, -1, 1);
								$new_dtmf = substr($new_branch, -1, 1);
								$audit_text = $ivr_model->addAuditText($audit_text, 
									"DTMF=".$old_dtmf." to ".$new_dtmf);
							}

							if ($data['event'] == 'SQ') {
								$audit_text = $ivr_model->addAuditText($audit_text, 
								"Skill=".$this->getSkillName($skill_model, $event_key));
							}
							$ivr_model->addToAuditLog('IVR', 'U', "IVR=".$ivrname, $audit_text);

							$data['message'] = 'Update Successfully !!';
							$data['msgType'] = 'success';
						} else {
							$data['message'] = 'Failed to Update Data !!';
							$data['msgType'] = 'error';
						}

					} else {
						$new_branch = $branch_id . $data['dtmf'];
						if ($data['event']=='DH') $data['text'] = str_replace(" ", "", $data['text']);
						$status = $ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
							'branch'=>$new_branch, 'event'=>$data['event'], 'event_key'=>$event_key, 'text'=>$data['text']));

						$data['pageTitle'] = 'Add IVR Branch';
						$data['refreshParent'] = true;

						if ($status || $file_uploaded) {
							if($status && $data['event']=='GV') {
								/*
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'s', 'event'=>'AN', 'event_key'=>'', 'text'=>'SUCCESS'));
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'f', 'event'=>'AN', 'event_key'=>'', 'text'=>'FAIL'));
								*/
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Get Value'));
							} else if($status && $data['event']=='DH') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'s', 'event'=>'AN', 'event_key'=>'', 'text'=>'On Time'));
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'f', 'event'=>'AN', 'event_key'=>'', 'text'=>'Off Time'));
							}
							 else if ($status && $data['event']=='MC') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Module Call'));
							} else if ($status && $data['event']=='RV') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Read Value'));
							} else if ($status && $data['event']=='SR') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Service Request'));
							} else if ($status && $data['event']=='SV') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Set Value'));
							} else if ($status && $data['event']=='IF') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Compare'));
							}


							$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
							$login_id = UserAuth::getCurrentUser();
							//$updated_fields = array(array('IVR='.$ivrname.'; Branch added;', '-', '-'));
							//ActivityLogManager::logInsert($login_id, "IVR", "A", $updated_fields, $db_manager);

							$icaption = $this->getEventValueName($data['event']);
							$ievent = $this->getEventName($data['event']);
							$ivalue = '';
							if ($data['event'] == 'SQ') {
								$ivalue = ';Skill='.$this->getSkillName($skill_model, $event_key);
							}
							$audit_text = "DTMF=".$data['dtmf'].";$audit_text"."$icaption=".$data['text'].";Event=$ievent"."$ivalue";
							$ivr_model->addToAuditLog('IVR', 'A', "IVR=".$ivrname, $audit_text);

							$data['message'] = 'Added Successfully !!';
							$data['msgType'] = 'success';
				
						} else {
							$data['message'] = 'Failed to add!!';
							$data['msgType'] = 'error';
						}
					}
			
					$this->getTemplate()->display_popup('popup_message', $data);
				} else {
					$data['errMsg'] = $err;
				}
			}

		} else {
			$method = $type == 'update' ? 'db' : 'default';
			$data = $this->loadIVRNodeData($method, $branch_id, $ivr_model);
		}

		$sq_options = $skill_model->getSkillOptions();
		$data['sq_options'] = array_merge(array(''=>'Select'), $sq_options);
		
		//$sr_options = 
		//var_dump($sr_options);
		$data['sr_options'] = $ivr_service_model->getServiceOptions();//array_merge(array(''=>'Select'), $sr_options);
		//var_dump($data['sr_options']);
		
		include('conf.extras.php');
		
		if ($extra->module_support) {
			include('ini_module_calls.php');
			$mc_options[''] = 'Select';
			if (is_array($module_calls)) {
				foreach ($module_calls as $mc) {
					$mc_options[$mc] = $mc;
				}
			}
			$data['mc_options'] = $mc_options;
		}

		$existing_dtmf_array = array();
		$exst_dtmf_prefix = $type == 'update' ? substr($branch_id, 0, -1) : $branch_id;
		$existing_dtmfs = $ivr_model->getIVRChildrenDetails($exst_dtmf_prefix, "RIGHT(branch, 1) AS dtmf");
		//var_dump($existing_dtmfs);
		if (is_array($existing_dtmfs)) {
			if ($type == 'update') {
				$dtmf_last_digit = substr($branch_id, -1);
			}
			foreach($existing_dtmfs as $temp_ivr) {
				if ($type == 'update' && $dtmf_last_digit == $temp_ivr->dtmf) continue;
				$existing_dtmf_array[$temp_ivr->dtmf] = $temp_ivr->dtmf;
			}
		}
		//var_dump($branch_id);
		$data['dtmf_options'] = array_diff_assoc(array('.'=>'.', '*'=>'*', '0'=>'0', '1'=>'1', '2'=>'2', '3'=>'3', 
			'4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', 'a'=>'a', 'b'=>'b', 'c'=>'c', 'd'=>'d', 'e'=>'e', 'f'=>'f'), $existing_dtmf_array);

		if (strlen($branch_id) == 1) {
			//$data['go_options'] = array("$branch_id"=>'Same Node');
			$data['go_options']['T_NODE'] = 'The Node';
			if ($data['event'] == 'GO') {
				$data['the_node'] = $data['event_go'];
			} else {
				$data['the_node'] = '';
			}
			$data['event_go']='T_NODE';
		} else {
			$prev_node = substr($branch_id, 0, -2);
			$root_node = substr($branch_id, 0, 1);
			$data['go_options'][$prev_node] = 'Previous Node';
			$data['go_options'][$root_node] = 'Root Node';
			$data['go_options']['T_NODE'] = 'The Node';
			if ($data['event'] == 'GO') {
				if ($data['event_go'] != $prev_node && $data['event_go'] != $root_node) {$data['the_node'] = $data['event_go'];$data['event_go']='T_NODE';}
				else {$data['the_node'] = $prev_node;}
			} else {
				$data['the_node'] = $prev_node;
			}
		}

		if ($type == 'update') {
			if(strlen($branch_id) <= MAX_BRANCH) $data['event_options']['AN'] = 'Announcement';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['GV'] = 'Get Value';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['DH'] = 'Day Hour';
			//if(strlen($branch_id) < MAX_BRANCH && $data['event']=='DH') $data['event_options']['DH'] = 'Day Hour';
			//$data['event_options']['DD'] = 'Direct Dial';
			//$data['event_options']['ED'] = 'External Dial';
			if(strlen($branch_id) <= MAX_BRANCH) $data['event_options']['PF'] = 'Get DTMF';
			if(strlen($branch_id) <= MAX_BRANCH) $data['event_options']['ID'] = 'Get ID';
//			if(strlen($branch_id) > 2) $data['event_options']['GO'] = 'Go';
			$data['event_options']['GO'] = 'Go';
			$data['event_options']['SQ'] = 'Skill';
			$data['event_options']['SR'] = 'Service Request';
			$data['event_options']['RV'] = 'Read Value';
			$data['event_options']['SV'] = 'Set Value';
			$data['event_options']['IF'] = 'Compare';
			if ($extra->module_support) $data['event_options']['MC'] = 'Module Call';

			if (strlen($branch_id) == 1) $data['is_root'] = true;
			$data['pageTitle'] = 'Update IVR Menu';
			if (isset($data['text']) && !empty($data['text'])) $data['pageTitle'] .= ' :: ' . $data['text'];
			$data['menu'] = $branch_id;
			$data['pevent'] = $pevent;
			$data['ivrname'] = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
			$data['is_edit'] = true;

		} else {
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['AN'] = 'Announcement';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['GV'] = 'Get Value';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['DH'] = 'Day Hour';
			//$data['event_options']['DD'] = 'Direct Dial';
			//$data['event_options']['ED'] = 'External Dial';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['PF'] = 'Get DTMF';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['ID'] = 'Get ID';
//			if(strlen($branch_id) > 1) $data['event_options']['GO'] = 'Go';
			$data['event_options']['GO'] = 'Go';
			$data['event_options']['SQ'] = 'Skill';
			$data['event_options']['SR'] = 'Service Request';
			$data['event_options']['RV'] = 'Read Value';
			$data['event_options']['SV'] = 'Set Value';
			$data['event_options']['IF'] = 'Comapare';
			if ($extra->module_support) $data['event_options']['MC'] = 'Module Call';

			$ivr_info = $ivr_model->getIVRDetailsByBranch($branch_id);
			$data['pageTitle'] = 'Add IVR Branch';
			if (!empty($ivr_info))
			if (!empty($ivr_info->text)) $data['pageTitle'] .= ' :: ' . $ivr_info->text;
			$data['menu'] = $branch_id;
			$data['pevent'] = $pevent;

			$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
			$data['ivrname'] = $ivrname;
			$data['is_edit'] = false;
		}
		$data['request'] = $this->getRequest();
		if (!isset($data['is_root'])) $data['is_root'] = false;
		$data['save_type'] = $type;
		$this->getTemplate()->display_popup('ivr_config_menu_item', $data);
	}
	
	
	function loadIVRNodeData($method='default', $branch_id, $ivr_model)
	{
		$data = array();
		$data['errMsg'] = '';
		
		if ($method == 'default') {
			$data['dtmf'] = '0';
			//$data['event'] = 'SQ';
			$data['event'] = 'PF';
			$data['text'] = '';
			//$data['event_sq'] = '';
		} else if ($method == 'db') {
			$ivr_menu = $ivr_model->getIVRDetailsByBranch($branch_id);
			if ($ivr_menu == null) {
				$data['dtmf'] = 0;
				$data['event'] = 'SQ';
				$data['text'] = '';
				$data['event_sq'] = '';
			} else {
				$data['dtmf'] = substr($branch_id, -1);
				$data['event'] = $ivr_menu->event;
				$data['text'] = $ivr_menu->text;
				$event_name = strtolower($data['event']);
				$data['event_'.$event_name] = $ivr_menu->event_key;
			}
		} else if ($method == 'apost') {
			
			$data['dtmf'] = $_POST['dtmf'];
			$data['event'] = $_POST['event'];
			$data['text'] = $_POST['text'];

			if($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'ID' && $data['event'] != 'GV' && $data['event'] != 'SV' && $data['event'] != 'IF') {
				$event_name = 'event_'.strtolower($data['event']);
				$data[$event_name] = $_POST[$event_name];
			}

			if ($data['event'] == 'GO') {
				if ($data['event_go'] == 'T_NODE') {
					$data['event_go'] = trim($_POST['the_node']);
				}
				//if ($branch_id == ($data['event_go'] . $data['dtmf'])) {
					//$data['errMsg'] = 'Can not go to the same node';
				//} else {
					$existing_branch = $ivr_model->getIVRDetailsByBranch($data['event_go']);
					if (empty($existing_branch)) $data['errMsg'] = 'Invalid node value provided';
					else $data['event_go'] = $existing_branch->branch;
				//}
			}
			
			if (empty($data['errMsg'])) {
				$ivr_at_dtmf = $ivr_model->getIVRDetailsByBranch($branch_id.$data['dtmf']);
				if (!empty($ivr_at_dtmf)) {
					$data['errMsg'] = 'Item exists for this DTMF position, delete that item first';
				} else {
					if(strlen($branch_id) > MAX_BRANCH) $data['errMsg'] = 'Maximum depth reaches, it is not possible to add more branch';
				}
			}
			
		
			//if(empty($data['text'])) $data['errMsg'] = 'Text is Required!!';
		} else if ($method == 'upost') {
			$data['dtmf'] = isset($_POST['dtmf']) ? $_POST['dtmf'] : '';
			$data['event'] = $_POST['event'];
			$data['text'] = $_POST['text'];
			$data['old_event'] = $_POST['old_event'];
			$data['old_text'] = $_POST['old_text'];
			
			/*
			$event_options = array();
			$event_options['AN'] = 'Announcement';
			$event_options['GV'] = 'Get Value';
			$event_options['DH'] = 'Day Hour';
			$event_options['DD'] = 'Direct Dial';
			$event_options['ED'] = 'External Dial';
			$event_options['PF'] = 'Get DTMF';
			$event_options['ID'] = 'Get ID';
			$event_options['GO'] = 'Go';
			$event_options['SQ'] = 'Service Queue';
			
			$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
			
			$data['updated_fields'] = array();
			$data['updated_fields'][] = array("IVR", $ivrname, '');
			$data['updated_fields'][] = array("Event",$event_options[$data['old_event']],$event_options[$data['event']]);
			if($data['event']=='GV') $fld_txt = "URL";
			else if($data['event']=='DH') $fld_txt = "Day Houre";
			else $fld_txt = "Text";
			
			$data['updated_fields'][] = array($fld_txt, $data['old_text'], $data['text']);
			
			//if($data['old_event']==$data['event'])
			
			//else
			//$data['updated_fields'][] = array($fld_txt, $data['text'], "");
			//========================================================================//
			*/
			
			
			if ($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'ID' && $data['event'] != 'GV' && $data['event'] != 'SV' && $data['event'] != 'IF') {
				
				$event_name = 'event_'.strtolower($data['event']);
				$old_event_name = 'old_event_'.strtolower($data['event']);
				$data[$event_name] = $_POST[$event_name];
				$data[$old_event_name] = $_POST[$old_event_name];
				
				//=========log data========//
				/*
				if($data['old_event']==$data['event']) {
					$data['updated_fields'][] = array($event_options[$data['event']],$data[$old_event_name],$data[$event_name]);
				} else {
					$data['updated_fields'][] = array($event_options[$data['event']],$data[$event_name],"");
				}
				*/
				//=========================//
			}

			if ($data['event'] == 'GO') {
				if ($data['event_go'] == 'T_NODE') {
					$data['event_go'] = trim($_POST['the_node']);
				}
				if ($branch_id == $data['event_go']) {
					$data['errMsg'] = 'Can not go to the same node';
				} else {
					$existing_branch = $ivr_model->getIVRDetailsByBranch($data['event_go']);
					if (empty($existing_branch)) $data['errMsg'] = 'Invalid node value provided';
					else $data['event_go'] = $existing_branch->branch;
				}
			}
			
			if (empty($data['errMsg'])) {
				$new_branch = substr($branch_id, 0, -1) . $data['dtmf'];
				//echo $data['dtmf'];
				if ($new_branch != $branch_id) {
					$ivr_at_dtmf = $ivr_model->getIVRDetailsByBranch(substr($branch_id, 0, -1).$data['dtmf']);
					if (!empty($ivr_at_dtmf)) {
						$data['errMsg'] = 'Item exists for this DTMF position, delete that item first';
					} else {
						if(strlen($branch_id) > MAX_BRANCH) $data['errMsg'] = 'Maximum depth reaches, it is not possible to add more branch';
					}
				}
			}

		}
		return $data;
	}
	
	function loadIVRRootData($method='db', $branch_id, $ivr_model)
	{
		$data = array();
		
		if($method == 'db') {
			$ivr_menu = $ivr_model->getIVRDetailsByBranch($branch_id);

			if($ivr_menu == null) {
				$data['event'] = 'PF';
				$data['text'] = '';
				$data['event_sq'] = '';
			} else {
				$data['event'] = $ivr_menu->event;
				$data['text'] = $ivr_menu->text;
				$event_name = strtolower($data['event']);
				$data['event_'.$event_name] = $ivr_menu->event_key;
			}

			$ivr_id = substr($branch_id, 0, 1);
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id;
			if(!file_exists($target_path)) {
				mkdir($target_path, 0700);
			}
			
		} else if($method == 'post') {
			$data['event'] = $_POST['event'];
			$data['text'] = $_POST['text'];

			if($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'ID' && $data['event'] != 'GV' && $data['event'] != 'SV' && $data['event'] != 'IF') {
				$event_name = 'event_'.strtolower($data['event']);
				$data[$event_name] = $_POST[$event_name];
			}
			
			//if(empty($data['text'])) $data['errMsg'] = 'Text is Required!!';
			$data['errMsg'] = '';
		}
		return $data;
	}
	
	function actionUpdate()
	{
		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$this->saveIvr($ivrid);
	}

	function saveIvr($ivrid='')
	{
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('lib/FileManager.php');
		$ivr_model = new MIvr();
		$skill_model = new MSkill();

		$sq_options = $skill_model->getSkillOptions();
		$data['sq_options'] = array_merge(array(''=>'Select'), $sq_options);
		$data['event_options'] = array('HU'=>'Hang Up','SQ'=>'Skill','PF'=>'Announcement');

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {

			$ivr = $this->getSubmittedIvr($ivrid);
			$ivr->dayhour = str_replace(" ", "", $ivr->dayhour);
			$errMsg = $this->getValidationMsg($ivr, $ivrid);

			if (empty($errMsg)) {
				$oldivr = $this->getInitialIvr($ivrid, $ivr_model);
				if (!empty($oldivr)) {
					$is_success = false;

					$extention = "";
					//$old_file_exist = false;
					//$old_file_name = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/timeout_action.wav';
					//$old_file_exist = file_exists($old_file_name);

					$is_language_select_file_uploaded = false;
					$is_off_hour_file_uploaded = false;
					$is_welcome_file_uploaded = false;
					$is_invalid_key_en_file_uploaded = false;
					$is_invalid_key_bn_file_uploaded = false;
					$is_goodbye_en_file_uploaded = false;
					$is_goodbye_bn_file_uploaded = false;
					$is_pf_en_file_uploaded = false;
					$is_pf_bn_file_uploaded = false;
					$audit_text = '';
					

					if ($ivr->timeout_action == 'PF') {
						if (!empty($_FILES['event_pf_en']) && $_FILES['event_pf_en']["error"] == 0) {
							$extention = $this->findexts(basename( $_FILES['event_pf_en']['name']));
							if ($extention !="wav") {
								$errMsg = 'Please select a wav file';
							} else {
								$is_pf_en_file_uploaded = true;
							}
						}/* else {
							if(!$old_file_exist) $errMsg = 'Please select a wav file';
						} */
						if (!empty($_FILES['event_pf_bn']) && $_FILES['event_pf_bn']["error"] == 0) {
							$extention = $this->findexts(basename( $_FILES['event_pf_bn']['name']));
							if ($extention !="wav") {
								$errMsg = 'Please select a wav file';
							} else {
								$is_pf_bn_file_uploaded = true;
							}
						}
					}

					if (!empty($_FILES['off_hour']) && $_FILES['off_hour']["error"] == 0) {
						$extention = $this->findexts(basename($_FILES['off_hour']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_off_hour_file_uploaded = true;
						}
					}

					if ($ivr->welcome_voice == 'Y') {
						if (!empty($_FILES['welcome']) && $_FILES['welcome']["error"] == 0) {
							$extention = $this->findexts(basename($_FILES['welcome']['name']));
							if ($extention !="wav") {
								$errMsg = 'Please select a wav file';
							} else {
								$is_welcome_file_uploaded = true;
							}
						}
					}

					if (!empty($_FILES['language_select']) && $_FILES['language_select']["error"] == 0) {
						$extention = $this->findexts(basename($_FILES['language_select']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_language_select_file_uploaded = true;
						}
					}
					
					if (!empty($_FILES['invalid_key_en']) && $_FILES['invalid_key_en']["error"] == 0) {
						$extention = $this->findexts(basename( $_FILES['invalid_key_en']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_invalid_key_en_file_uploaded = true;
						}
					}

					if (!empty($_FILES['invalid_key_bn']) && $_FILES['invalid_key_bn']["error"] == 0) {
						$extention = $this->findexts(basename( $_FILES['invalid_key_bn']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_invalid_key_bn_file_uploaded = true;
						}
					}


					if (!empty($_FILES['goodbye_en']) && $_FILES['goodbye_en']["error"] == 0) {
						$extention = $this->findexts(basename( $_FILES['goodbye_en']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_goodbye_en_file_uploaded = true;
						}
					}

					if (!empty($_FILES['goodbye_bn']) && $_FILES['goodbye_bn']["error"] == 0) {
						$extention = $this->findexts(basename( $_FILES['goodbye_bn']['name']));
						if ($extention !="wav") {
							$errMsg = 'Please select a wav file';
						} else {
							$is_goodbye_bn_file_uploaded = true;
						}
					}


					//if ($ivr->timeout_action=='PF' && $extention=='wav' && $errMsg=="") {
					if ($errMsg == "") {
						if ($is_pf_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/E/timeout_action.wav';
							if (FileManager::save_uploaded_file('event_pf_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Timeout voice(ENG);';
							}
						}
						if ($is_pf_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/B/timeout_action.wav';
							if (FileManager::save_uploaded_file('event_pf_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Timeout voice(BAN);';
							}
						}
						if ($is_off_hour_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/off_hour.wav';
							if (FileManager::save_uploaded_file('off_hour', $target_path)) {
								$is_success = true;
								$audit_text .= 'Off hour voice;';
							}
						}

						if ($is_welcome_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/welcome.wav';
							if (FileManager::save_uploaded_file('welcome', $target_path)) {
								$is_success = true;
								$audit_text .= 'Welcome voice;';
							}
						}
						if ($is_language_select_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/language_select.wav';
							if (FileManager::save_uploaded_file('language_select', $target_path)) {
								$is_success = true;
								$audit_text .= 'Language voice;';
							}
						}
						if ($is_invalid_key_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/E/invalid_key.wav';
							if (FileManager::save_uploaded_file('invalid_key_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Invalid key voice(ENG);';
							}
						}
						if ($is_invalid_key_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/B/invalid_key.wav';
							if (FileManager::save_uploaded_file('invalid_key_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Invalid key voice(BAN);';
							}
						}
						if ($is_goodbye_en_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/E/goodbye.wav';
							if (FileManager::save_uploaded_file('goodbye_en', $target_path)) {
								$is_success = true;
								$audit_text .= 'Good bye voice(ENG);';
							}
						}
						if ($is_goodbye_bn_file_uploaded) {
							$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/B/goodbye.wav';
							if (FileManager::save_uploaded_file('goodbye_bn', $target_path)) {
								$is_success = true;
								$audit_text .= 'Good bye voice(BAN);';
							}
						}
					}


					if ($errMsg=="") {
						$value_options = array(
							'timeout_action' => $data['event_options'],
							'debug_mode' => array('Y'=>'Enable', 'N'=>'Disable'),
							'language' => array(''=>'1: None, 2: None', 'E'=>'1: English, 2: None', 'B'=>'1: Bangla, 2: None', 'EB'=>'1: English, 2: Bangla', 'BE'=>'1: Bangla, 2: English'),
							'value' => $data['sq_options']
						);
						$isUpdate1 = $ivr_model->updateIVRByID($oldivr, $ivr, $audit_text, $value_options);
						$is_success = $is_success ? $is_success : $isUpdate1;
					}

					if ($is_success) {
						$errMsg = 'IVR Updated Successfully !!';
					} else {
						if (empty($errMsg)) $errMsg = 'No Change Found !!';
					}
					
					if ($is_success) {
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
							CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'Invalid IVR !!';
				}
			}

		} else {
			$ivr = $this->getInitialIvr($ivrid, $ivr_model);
		}

		$data['ivrid'] = $ivrid;
		$data['ivr'] = $ivr;
		$data['request'] = $this->getRequest();
		//$data['skill_options'] = $skill_model->getSkills();
		$data['language_options'] = array('E' => 'English', 'B' => 'Bangla');
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update IVR : ' . $ivr->ivr_name;
		$this->getTemplate()->display('ivr_form', $data);
	}

	function getInitialIvr($ivrid, $ivr_model)
	{
		$ivr = null;

		$ivr = $ivr_model->getIvrById($ivrid);
		
		if (empty($ivr)) {
			exit;
		} else {
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid;
			if (!file_exists($target_path)) {
				mkdir($target_path, 0700);
			}
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/E';
			if (!file_exists($target_path)) {
				mkdir($target_path, 0700);
			}
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/B';
			if (!file_exists($target_path)) {
				mkdir($target_path, 0700);
			}
		}
		return $ivr;
	}

	function getSubmittedIvr($ivrid)
	{
		$posts = $this->getRequest()->getPost();
		$ivr = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$ivr->$key = trim($val);
			}
		}
		$ivr->ivr_id = $ivrid;
		$ivr->language = $ivr->language1 . $ivr->language2;
		return $ivr;
	}

	function getValidationMsg($ivr, $ivrid='')
	{
		$err = '';
		if (empty($ivr->ivr_name)) $err = "Provide IVR Name";		
		return $err;
	}

	function getOutValidationMsg($skill, $skillid='')
	{
		$err = '';
		if (empty($skill->skill_name)) $err = "Provide Skill Name";		
		return $err;
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
}
