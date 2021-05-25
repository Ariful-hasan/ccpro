<?php

define('MAX_BRANCH', 31);

class Ivrs extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MIvr.php');
		$ivr_model = new MIvr();

		$data['ivrs'] = $ivr_model->getIvrs('', 0, 100);

		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'IVR List';
		$this->getTemplate()->display('ivrs', $data); */
		$data['topMenuItems'] = array(
			array('href'=>'task=ivrs&act=transfertoivr', 'img'=>'fa fa-exchange', 'label'=>'Transfer to IVR')
		);
		
		$data['pageTitle'] = 'IVR List';
		$data['dataUrl'] = $this->url('task=get-home-data&act=ivrs');
		$this->getTemplate()->display('ivrs', $data);
	}

	function actionTimeout()
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('model/MIvrService.php');
		include('conf.extras.php');
		
		$ivr_model = new MIvr();
		$ivr_service_model = new MIvrService();
		$skill_model = new MSkill();

		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		$data['ivr_details'] = $ivr_model->getIVRTimeoutDetails($ivrid);		
		
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['sr_options'] = $ivr_service_model->getServiceOptions();
		$data['pageTitle'] = 'Timeout IVR :: ' . $ivr->ivr_name;
		$data['ivrid'] = $ivrid;
		$data['ivr'] = $ivr;
		$data['topMenuItems'] = array(
			array('href'=>'task=ivrs&act=update&ivrid='.$ivrid, 'img'=>'icon/table.png', 'label'=>'View Properties'),
			array('href'=>'task=ivrs&act=config&ivrid='.$ivrid, 'img'=>'icon/table.png', 'label'=>'View Tree'),
			array('href'=>'task=ivrs&act=movenode&ivrid='.$ivrid, 'class'=>'conf-panel', 'img'=>'arrow_redo.png', 'label'=>'Move Node'),
			array('href'=>'task=ivrs&act=insertnode&ivrid='.$ivrid, 'class'=>'conf-panel', 'img'=>'add.png', 'label'=>'Insert above node')
		);
		if ($extra->voice_synth_module) {
			$data['topMenuItems'][] = array('href'=>'task=ivrs&act=ttsreload&rpage=config&ivrid='.$ivrid, 'img'=>'arrow_refresh_small.png', 'label'=>'TTS Reload');
		}
		
		$data['smi_selection'] = 'ivrs_';
		$this->getTemplate()->display('timeout_config', $data);
	}
	function actionConfig()
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('model/MIvrService.php');
		include('conf.extras.php');
	
		$ivr_model = new MIvr();
		$ivr_service_model = new MIvrService();
		$skill_model = new MSkill();
	
		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		$data['ivr_details'] = $ivr_model->getIVRDetails($ivrid);
		// GPrint(	$data['ivr_details']);die;
		if (empty($data['ivr_details'])) {
			$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>$ivrid, 'branch'=>$ivrid, 'event'=>'AN', 'event_key'=>'', 'text'=>'IVR Root'));
			$data['ivr_details'] = $ivr_model->getIVRDetails($ivrid);
		}
		if(!empty($data['ivr_details'])){
			
			$ivrBranches = array_column($data['ivr_details'], 'branch'); 
			$asrData = $ivr_model->getMultiIVRAsrData($ivrid, $ivrBranches);
			$processAsrData = [];
			array_walk($asrData, function($value) use (&$processAsrData){
				$indexKey =  $value->branch.$value->node;
				$processAsrData[$indexKey] = $value;
			});
			$data['asr_data'] = $processAsrData;
		}
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['sr_options'] = $ivr_service_model->getServiceOptions();
		$data['pageTitle'] = 'Update IVR :: ' . $ivr->ivr_name;
		$data['ivrid'] = $ivrid;
		$data['ivr'] = $ivr;
		$data['topMenuItems'] = array(
				array('href'=>'task=ivrs&act=update&ivrid='.$ivrid, 'img'=>'icon/table.png', 'label'=>'View Properties'),
				array('href'=>'task=ivrs&act=timeout&ivrid='.$ivrid, 'img'=>'icon/table.png', 'label'=>'Timeout'),
				array('href'=>'task=ivrs&act=movenode&ivrid='.$ivrid, 'class'=>'conf-panel', 'img'=>'arrow_redo.png', 'label'=>'Move Node'),
				array('href'=>'task=ivrs&act=insertnode&ivrid='.$ivrid, 'class'=>'conf-panel', 'img'=>'add.png', 'label'=>'Insert above node')
		);
		// if ($extra->voice_synth_module) {
		// 	$data['topMenuItems'][] = array('href'=>'task=ivrs&act=ttsreload&rpage=config&ivrid='.$ivrid, 'img'=>'arrow_refresh_small.png', 'label'=>'TTS Reload');
		// }
	
		$data['smi_selection'] = 'ivrs_';
		$this->getTemplate()->display('ivr_config', $data);
	}
	

	function getEventName($event)
	{
		$events = array('AN'=>'Announcement', 'RV'=>'Read Value', 'CA'=>'Call API', 'SV'=>'Set Value', 'IF'=>'Compare', 'DH'=>'Day Hour', 
			'PF'=>'Get DTMF', 'UI'=>'User Input', 'GO'=>'Go', 'SQ'=>'Skill', 'SR'=>'Service Request', 'SL'=>'Service Log', 'FP'=>'Set Footprint', 
			'AU'=>'Caller Authenticated', 'MC'=>'Module Call', 'DD' => 'Dial to Agent', 'CP' => 'Marked as Priority Caller');
		$ename = isset($events[$event]) ? $events[$event] : $event;
		return $ename;
	}

	function getEventValueName($event)
	{
		$events = array('AN'=>'Text', 'RV'=>'Text', 'CA'=>'URL', 'SV'=>'Text', 'IF'=>'Text', 'DH'=>'Day Hour', 'PF'=>'Text', 'UI'=>'Text', 'GO'=>'Text', 'SQ'=>'Text', 'SR'=>'Text', 'SL'=>'Text', 'FP'=>'Text', 'AU'=>'Text', 'MC'=>'Text');
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
	
	function actionDeleteonlynode()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();

		$menu = isset($_REQUEST['menu']) ? trim($_REQUEST['menu']) : '';

		$node = $ivr_model->getIVRDetailsByBranch($menu);
	
		if (!empty($node)) {
			$ivr_id = substr($menu, 0, 2);
			$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/';
			
			if ($node->event=='PF') {
				if (!empty($node->event_key)) {
					$file = $delete_file_path . $node->event_key . '.wav';
					include('lib/FileManager.php');
					if (file_exists($file)) FileManager::unlink_file($file);
				}
			}
		}
		
		$status = $ivr_model->removeBranchOnly($menu);
	
		$response = new stdClass();
		$response->success = false;
		if ($status) {
			$response->success = true;
		}
		echo json_encode($response);
		exit;
	}
	
	function actionDeletenode()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();

		$menu = isset($_REQUEST['menu']) ? trim($_REQUEST['menu']) : '';

		$ivr_children = $ivr_model->getSubTreeByBranch($menu);
	
		if (is_array($ivr_children)) {
			$ivr_id = substr($menu, 0, 2);
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
	
		$response = new stdClass();
		$response->success = false;
		if ($status) {
			$response->success = true;
		}
		echo json_encode($response);
		exit;
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
		$errType = 1;
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		
		if (isset($_POST['movenode'])) {
			$from_node = isset($_POST['from_node']) ? trim($_POST['from_node']) : '';
			$to_node = isset($_POST['to_node']) ? trim($_POST['to_node']) : '';
			//$to_node = strtolower($to_node);
			
			$valid_dtmfs = array('.', '*', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 's', 't');
			
			if (strlen($to_node) < 3) $errMsg = 'Provide valid new node';
			if (empty($to_node)) $errMsg = 'Provide new node';
			if (empty($from_node)) $errMsg = 'Provide source node'; 
			
			if (empty($errMsg)) {
				$to_dtmf = substr($to_node, -1);
				$to_dtmf = strtolower($to_dtmf);
				$to_node = substr($to_node, 0, -1) . $to_dtmf;
				if (!in_array($to_dtmf, $valid_dtmfs)) $errMsg = 'Provide valid new node';
			}
			
			if (empty($errMsg)) {
				if (substr($from_node, 0, 2) != $ivrid) $errMsg = 'Provide valid source node';
				if (substr($to_node, 0, 2) != $ivrid) $errMsg = 'Provide valid new node';
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
				$frm_len = strlen($from_node);
				$to_len = strlen($to_node);
				$old_len = $ivr_model->getMaxBranchLength($from_node);
				$new_len = $old_len - $frm_len + $to_len;
				if ($new_len > 31) $errMsg = 'Failed to move node as it will exceed MAX depth of the tree';
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
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Move IVR Node :: ' . $ivr->ivr_name;
			
		$this->getTemplate()->display_popup('ivr_move_menu_item', $data);
	}
	
	function actionInsertnode()
	{
		include('model/MIvr.php');
		$ivr_model = new MIvr();
		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';
		$from_node = '';
		$errMsg = '';
		$errType = 1;
		$ivr = $ivr_model->getIVRById($ivrid);
		if (empty($ivr)) exit;
		
		if (isset($_POST['insertnode'])) {
			$from_node = isset($_POST['from_node']) ? trim($_POST['from_node']) : '';
			
			if (strlen($from_node) < 3) $errMsg = 'Provide valid target node';
			if (empty($from_node)) $errMsg = 'Provide target node'; 
			
			if (empty($errMsg)) {
				if (substr($from_node, 0, 2) != $ivrid) $errMsg = 'Provide valid target node';
			}
			
			if (empty($errMsg)) {
				$from_tree = $ivr_model->getIVRDetailsByBranch($from_node);
				if (empty($from_tree)) $errMsg = 'Provide valid target node';
			}
			
			if (empty($errMsg)) {
				$old_len = $ivr_model->getMaxBranchLength($from_node);
				$new_len = $old_len + 1;
				/*
				$to_node = substr($from_node, 0, -1) . '.';
				$i = 1;
				$br_details = $ivr_model->getIVRDetailsByBranch($to_node);
				var_dump($br_details);
				while (!empty($br_details)) {
					$to_node = $to_node . '.';
					$i++;
					$br_details = $ivr_model->getIVRDetailsByBranch($to_node);
				}
				$old_len = $ivr_model->getMaxBranchLength($from_node);
				$new_len = $old_len + $i;
				*/
				if ($new_len > 31) $errMsg = 'Failed to insert node as it will exceed MAX depth of the tree';
			}

			if (empty($errMsg)) {
				//$to_node = substr($from_node, 0, -1) . '.';
				if ($ivr_model->insertBeforeNode($from_node, $ivr->ivr_name)) {
				//if (1) {
					$data['message'] = 'Node inserted uccessfully !!';
					$data['msgType'] = 'success';
					$data['refreshParent'] = true;
					$this->getTemplate()->display_popup('popup_message', $data);
					
				} else {
					$errMsg = 'Failed to insert above the node';
				}
			}
		}
		$data['request'] = $this->getRequest();
		$data['ivr'] = $ivr;
		$data['ivrid'] = $ivrid;
		$data['from_node'] = $from_node;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Insert above IVR Node :: ' . $ivr->ivr_name;
			
		$this->getTemplate()->display_popup('ivr_insert_above_menu_item', $data);
	}
	
	function updateNode($type)
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('lib/FileManager.php');
		include('model/MIvrService.php');
		include('conf.extras.php');
		AddModel("MLanguage");
		include("model/MIvrBranch.php");
		/* if(!empty($_POST['text'])){
			$_POST['text']=addslashes($_POST['text']);
		} */
		$ivr_service_model = new MIvrService();
		$ivr_model = new MIvr();
		$skill_model = new MSkill();
        $ivr_branch_model = new MIvrBranch();

		$branch_id = isset($_REQUEST['menu']) ? trim($_REQUEST['menu']) : '';
		$pevent = isset($_REQUEST['pevent']) ? $_REQUEST['pevent'] : '';
		if ($type == 'update' && empty($pevent)) {
			$parent_details = $ivr_model->getParent($branch_id);
			if (!empty($parent_details)) $pevent = $parent_details->event;
		}
		//$data['arg'] = "";
		
		if (isset($_POST['editivr'])) {
			$method = $type == 'update' ? 'upost' : 'apost';
			$data = $this->loadIVRNodeData($method, $branch_id, $ivr_model);
            //GPrint($method);
            //GPrint($data);die;

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
				if ($data['event'] == 'PF' || $data['event'] == 'AN' || $data['event'] == 'UI' || $data['event'] == 'CA') {
					
					if ($type == 'update') {
						if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') {
							$file_name = $ivr_menu->event_key;
						} else {
							$file_name = '';
						}
					} else {
						$file_name = '';
					}

					$event_key = $file_name;

					if($data['event'] == 'PF') {
						$srv_file_name = 'event_pf';
						$voice_name = 'Get DTMF';
					} elseif($data['event'] == 'AN') {
						$srv_file_name = 'event_an';
						$voice_name = 'Announcement';
					} elseif($data['event'] == 'UI') {
						$srv_file_name = 'event_ui';
						$voice_name = 'User Input';
					} else if($data['event'] == 'CA') {
						$srv_file_name = 'event_ca';
						$voice_name = 'Call API';
					}

					$srv_file_name_en = $srv_file_name . '_en';
					$srv_file_name_bn = $srv_file_name . '_bn';
					$err = '';
					
					if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
						$ivr_id = substr($branch_id, 0, 2);
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
							$ivr_id = substr($branch_id, 0, 2);
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
					
					if (!in_array($data['event'], ['PF', 'AN', 'UI']) && !$file_uploaded) $event_key = '';

				} else {
					//if ($data['event'] == 'RV') {
						//$event_key = '';
					//} else {
					if ($data['event'] != 'SV' && $data['event'] != 'SY' && $data['event'] != 'IF' && $data['event'] != 'AU' ) {
						$event_key_index = 'event_'.strtolower($data['event']);
						$event_key = $data[$event_key_index];
					}else {
						$event_key = '';
					}
					//}
				}
				
				if (empty($err)) {
					if ($type == 'update') {
						if (strlen($branch_id) <= 2) $new_branch = $branch_id;
						else $new_branch = substr($branch_id, 0, -1) . $data['dtmf'];
						if ($data['event']=='DH') $data['text'] = str_replace(" ", "", $data['text']);
						$ivr_info_for_update = array('ivr_id'=>substr($branch_id, 0, 2), 'event'=>$data['event'], 'text'=>$data['text']);
						if (strlen($branch_id) > 1) $ivr_info_for_update['branch'] = $new_branch;

						if ( $data['event'] != 'CA' && $data['event'] != 'SV' && $data['event'] != 'SY' && $data['event'] != 'IF') {
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
							$ivr_id = substr($branch_id, 0, 2);
							if (!$en_file_uploaded) {
								$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/E/' . $ivr_menu->event_key . '.wav';
								if(file_exists($delete_file_path)) FileManager::unlink_file($delete_file_path);
							}
							if (!$bn_file_uploaded) {
								$delete_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id . '/B/' . $ivr_menu->event_key . '.wav';
								if(file_exists($delete_file_path)) FileManager::unlink_file($delete_file_path);
							}
						}
			
						if (($ivr_info_for_update['event'] == 'PF' || $ivr_info_for_update['event'] == 'UI' || $ivr_info_for_update['event'] == 'AN')) {
							$oldtext = trim(preg_replace('/^\[.*\]/U', '', $ivr_menu->text));
							$newtext = trim(preg_replace('/^\[.*\]/U', '', $ivr_info_for_update['text']));
							if ($oldtext != $newtext) {
								$ivr_info_for_update['TTS_update'] = 'Y';
							}
						}
						if($data['old_arg']!=$data['arg']){
							$ivr_info_for_update['arg']=$data['arg'];
							$ivr_info_for_update['arg2']=$data['arg2'];
						}

                        $ivr_info_for_update['disposition_id'] = $data['disposition_id'];
                        $ivr_info_for_update['title_id'] = $data['title_id'];
                        $ivr_info_for_update['is_srv_log'] = $data['is_srv_log'];
                        $ivr_info_for_update['is_trace'] = $data['is_trace'];
                        $ivr_info_for_update['is_foot_print'] = $data['is_foot_print'];

						$status = $ivr_model->updateIVRDetailsByBranch($branch_id, $ivr_info_for_update);
						
						/*
						if ($status && $extra->voice_synth_module && isset($ivr_info_for_update['TTS_update'])) {
							//file_put_contents('/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp', 'IVR:'.$ivr_id."\n", FILE_APPEND);
							$this->reloadTTS(substr($branch_id, 0, 1));
						}
						*/
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

							if ($data['event'] == 'SQ' || $data['event'] == 'VM') {
								$audit_text = $ivr_model->addAuditText($audit_text, 
								"Skill=".$this->getSkillName($skill_model, $event_key));
							}
							$ivr_model->addToAuditLog('IVR', 'U', "IVR=".$ivrname, $audit_text);

							$data['message'] = 'Successfully updated !!';
							$data['msgType'] = 'success';
						} else {
							$data['message'] = 'No change found !!';
							$data['msgType'] = 'error';
						}

					} else {
						$new_branch = $branch_id . $data['dtmf'];
						if ($data['event']=='DH') $data['text'] = str_replace(" ", "", $data['text']);
						$status = $ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
							'branch'=>$new_branch, 'event'=>$data['event'], 'arg'=>$data['arg'], 'arg2'=>$data['arg2'], 'event_key'=>$event_key, 'text'=>$data['text']));

						$data['pageTitle'] = 'Add IVR Branch';
						$data['refreshParent'] = true;

						/*
						if ($status && $extra->voice_synth_module) {
							//file_put_contents('/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp', 'IVR:'.substr($branch_id, 0, 1)."\n", FILE_APPEND);
							$this->reloadTTS(substr($branch_id, 0, 1));
						}
						*/
						
						if ($status || $file_uploaded) {
							if($status && $data['event']=='GV') {
								/*
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'s', 'event'=>'AN', 'event_key'=>'', 'text'=>'SUCCESS'));
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 1), 
									'branch'=>$new_branch.'f', 'event'=>'AN', 'event_key'=>'', 'text'=>'FAIL'));
								*/
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Get Value'));
							} else if($status && $data['event']=='DH') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'s', 'event'=>'AN', 'event_key'=>'', 'text'=>'On Time'));
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'f', 'event'=>'AN', 'event_key'=>'', 'text'=>'Off Time'));
							}
							 else if ($status && $data['event']=='MC') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Module Call'));
							} else if ($status && $data['event']=='RV') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Read Value'));
							} else if ($status && $data['event']=='SR') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Service Request'));
							} else if ($status && $data['event']=='SL') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Service ReqLoguest'));
							} else if ($status && $data['event']=='FP') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Footprint'));
							} else if ($status && $data['event']=='AU') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Caller Authenticated'));
							} else if ($status && $data['event']=='CP') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Priority Caller Marking'));
							} else if ($status && $data['event']=='SV') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Set Value'));
							} else if ($status && $data['event']=='IF') {
								$ivr_model->addIVRDetailsByBranch(array('ivr_id'=>substr($branch_id, 0, 2), 
									'branch'=>$new_branch.'.', 'event'=>'AN', 'event_key'=>'', 'text'=>'After Compare'));
							}


							$ivrname = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
							$login_id = UserAuth::getCurrentUser();
							//$updated_fields = array(array('IVR='.$ivrname.'; Branch added;', '-', '-'));
							//ActivityLogManager::logInsert($login_id, "IVR", "A", $updated_fields, $db_manager);

							$icaption = $this->getEventValueName($data['event']);
							$ievent = $this->getEventName($data['event']);
							$ivalue = '';
							if ($data['event'] == 'SQ' || $data['event'] == 'VM') {
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
		$data['sr_options'] = $ivr_service_model->getServiceOptions('M');//array_merge(array(''=>'Select'), $sr_options);
		$data['sl_options'] = $ivr_service_model->getServiceOptions('A');
		$data['fp_options'] = $ivr_service_model->getServiceOptions('M');
		$data['branch_titles'] = ['*' => 'Select'] + $ivr_branch_model->allAsKeyValue();

		//var_dump($data['sr_options']);
		$data['mc_options']=array();
		
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
		$data['dtmf_options'] = array_diff_assoc(array('.'=>'.', '*'=>'*',  '#'=>'#', '0'=>'0', '1'=>'1', '2'=>'2', '3'=>'3',
			'4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', 'a'=>'a', 'b'=>'b', 'c'=>'c', 'd'=>'d', 'e'=>'e', 'f'=>'f', 'g'=> 'g', 'h'=>'h', 'i'=>'i', 'j'=>'j', 'k'=>'k', 'l'=>'l', 'm'=>'m', 'n'=>'n', 'o'=>'o', 'p'=> 'p', 'q'=>'q', 'r'=>'r', 's'=>'s', 't'=>'t', 'u'=>'u', 'v'=>'v', 'w'=>'w', 'x'=>'x', 'y'=> 'y', 'z'=>'z'), $existing_dtmf_array);

		if (strlen($branch_id) == 2) {
			//$data['go_options'] = array("$branch_id"=>'Same Node');
			$data['go_options']['T_NODE'] = 'The Node';
			if ($data['event'] == 'GO') {
				$data['the_node'] = $data['event_go'];
			} else {
				$data['the_node'] = '';
			}
			$data['event_go']='T_NODE';
		} else {
			$prev_node = substr($branch_id, 0, -2); //Confused -1 or -2
			$root_node = substr($branch_id, 0, 2);
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
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['CA'] = 'Call API';
			$data['event_options']['LN'] = 'Change Language';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['DH'] = 'Day Hour';
			$data['event_options']['DD'] = 'Dial to Agent';
			$data['event_options']['XF'] = 'External Transfer';
			//if(strlen($branch_id) < MAX_BRANCH && $data['event']=='DH') $data['event_options']['DH'] = 'Day Hour';
			//$data['event_options']['DD'] = 'Direct Dial';
			//$data['event_options']['ED'] = 'External Dial';
			if(strlen($branch_id) <= MAX_BRANCH) $data['event_options']['PF'] = 'Get DTMF';
			if(strlen($branch_id) <= MAX_BRANCH) $data['event_options']['UI'] = 'User Input';
//			if(strlen($branch_id) > 2) $data['event_options']['GO'] = 'Go';
			$data['event_options']['GO'] = 'Go';
			$data['event_options']['BY'] = 'Hangup';
			$data['event_options']['CP'] = 'Marked as Priority Caller';
			$data['event_options']['SQ'] = 'Skill';
			$data['event_options']['SR'] = 'Service Request';
			$data['event_options']['SL'] = 'Service Log';
			$data['event_options']['FP'] = 'Set Footprint';
			$data['event_options']['AU'] = 'Caller Authenticated';
			$data['event_options']['RV'] = 'Read Value';
			$data['event_options']['SV'] = 'Set Value';
			$data['event_options']['IF'] = 'Compare';
			if ($extra->module_support) $data['event_options']['MC'] = 'Module Call';
			$data['event_options']['VM'] = 'Voice Mail';
			$data['event_options']['VT'] = 'Voice Tap';

			//if ($extra->voice_synth_module) $data['event_options']['SY'] = 'Synth Voice';

			if (strlen($branch_id) == 2) $data['is_root'] = true;
			$data['pageTitle'] = 'Update IVR Menu';
			if (isset($data['text']) && !empty($data['text'])) $data['pageTitle'] .= ' :: ' . $data['text'];
			$data['menu'] = $branch_id;
			$data['pevent'] = $pevent;
			$data['ivrname'] = isset($_REQUEST['ivrname']) ? $_REQUEST['ivrname'] : '';
			$data['is_edit'] = true;

		} else {
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['AN'] = 'Announcement';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['CA'] = 'Call API';
			$data['event_options']['LN'] = 'Change Language';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['DH'] = 'Day Hour';
			$data['event_options']['DD'] = 'Dial to Agent';
			$data['event_options']['XF'] = 'External Transfer';
			//$data['event_options']['DD'] = 'Direct Dial';
			//$data['event_options']['ED'] = 'External Dial';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['PF'] = 'Get DTMF';
			if(strlen($branch_id) < MAX_BRANCH) $data['event_options']['UI'] = 'User Input';
//			if(strlen($branch_id) > 1) $data['event_options']['GO'] = 'Go';
			$data['event_options']['GO'] = 'Go';
			$data['event_options']['BY'] = 'Hangup';
			$data['event_options']['CP'] = 'Marked as Priority Caller';
			$data['event_options']['SQ'] = 'Skill';
			$data['event_options']['SR'] = 'Service Request';
			$data['event_options']['SL'] = 'Service Log';
			$data['event_options']['FP'] = 'Set Footprint';
			$data['event_options']['AU'] = 'Caller Authenticated';
			$data['event_options']['RV'] = 'Read Value';
			$data['event_options']['SV'] = 'Set Value';
			$data['event_options']['IF'] = 'Comapare';
			if ($extra->module_support) $data['event_options']['MC'] = 'Module Call';
			$data['event_options']['VM'] = 'Voice Mail';
            $data['event_options']['VT'] = 'Voice Tap';
			//if ($extra->voice_synth_module) $data['event_options']['SY'] = 'Synth Voice';

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
		$data['ivr_info'] = $ivr_model->getIVRDetailsByBranch($branch_id);;
		
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
				$data['arg'] = $ivr_menu->arg;
				$data['arg2'] = $ivr_menu->arg2;
				$data['text'] = $ivr_menu->text;
				$event_name = strtolower($data['event']);
				$data['event_'.$event_name] = $ivr_menu->event_key;
			}
		} else if ($method == 'apost') {
			
			$data['dtmf'] = $_POST['dtmf'];
			$data['event'] = $_POST['event'];
			$data['text'] = $_POST['text'];
			$data['arg'] = $_POST['arg'];
			$data['arg2'] = $_POST['arg2'];

			if($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'UI' && $data['event'] != 'CA' && $data['event'] != 'AU' && $data['event'] != 'SV' && $data['event'] != 'SY' && $data['event'] != 'IF') {
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
			$data['arg'] = $_POST['arg'];
			$data['arg2'] = $_POST['arg2'];
			$data['old_arg'] = $_POST['old_arg'];
			$data['disposition_id'] = $_POST['disposition_id'];
			$data['title_id'] = $_POST['title_id'];
			$data['is_srv_log'] = $_POST['log_service'] && $_POST['log_service'] == 'Y' ? 'Y' : '';
			$data['is_trace'] = $_POST['log_trace'] && $_POST['log_trace'] == 'Y' ? 'Y' : '';
			$data['is_foot_print'] = $_POST['log_footprint'] && $_POST['log_footprint'] == 'Y' ? 'Y' : '';

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
			
			//var_dump($data);
			if ($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'UI' && $data['event'] != 'CA' && $data['event'] != 'SV' && $data['event'] != 'SY' && $data['event'] != 'IF') {
				
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
				if (strlen($branch_id) <= 2) $new_branch = $branch_id;
				else $new_branch = substr($branch_id, 0, -1) . $data['dtmf'];
				
				//echo '$new_branch != $branch_id=' . $new_branch . ' ' . $branch_id;
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

			$ivr_id = substr($branch_id, 0, 2);
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_id;
			if(!file_exists($target_path)) {
				mkdir($target_path, 0755);
			}
			
		} else if($method == 'post') {
			$data['event'] = $_POST['event'];
			$data['text'] = $_POST['text'];

			if($data['event'] != 'PF' && $data['event'] != 'AN' && $data['event'] != 'UI' && $data['event'] != 'CA' && $data['event'] != 'SV' && $data['event'] != 'IF') {
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
		include('conf.extras.php');
		
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
			//$ivr->dayhour = str_replace(" ", "", $ivr->dayhour);
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
					

					if (false && $ivr->timeout_action == 'PF') {//skip. undefined property timeout_action
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
							//GPrint($target_path);
							if (false && FileManager::save_uploaded_file('welcome', $target_path)) {//skip
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
							'TTS' => array('Y'=>'Enable', 'N'=>'Disable'),
							'language' => array(''=>'1: None, 2: None', 'E'=>'1: English, 2: None', 'B'=>'1: Bangla, 2: None', 'EB'=>'1: English, 2: Bangla', 'BE'=>'1: Bangla, 2: English'),
							'value' => $data['sq_options']
						);

						$isTTSUpdate = false;						
						if ($extra->voice_synth_module) {
							if ($this->saveIVRTTSValues($ivrid, $ivr)) {
								$is_success = true;
								$isTTSUpdate = true;
							}
							
							/*
							if ($isTTSUpdate) {
								$value_options['TTS'] = (
							}
							*/
							// || $isTTSUpdate
							if (($oldivr->TTS != $ivr->TTS) && $ivr->TTS == 'Y') {
								//file_put_contents('/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp', 'IVR:'.$ivrid."\n", FILE_APPEND);
								//file_put_contents('E:/temp/tts.tmp', 'IVR:'.$ivrid."\n", FILE_APPEND);
								$this->reloadTTS($ivrid);
							}
						}
						$isUpdate1 = $ivr_model->updateIVRByID($oldivr, $ivr, $audit_text, $value_options, $extra->voice_synth_module, $isTTSUpdate);
						$is_success = $is_success ? $is_success : $isUpdate1;
						
					}

					if ($is_success) {
						$errMsg = 'IVR updated successfully !!';
					} else {
						if (empty($errMsg)) $errMsg = 'No change found !!';
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
		$data['tts_support'] = $extra->voice_synth_module;
		if ($extra->voice_synth_module) $data['ttstexts'] = $this->getIVRTTSValues($ivrid);
		$data['pageTitle'] = 'Update IVR : ' . $ivr->ivr_name;
		$data['topMenuItems'] = array(
			array('href'=>'task=ivrs&act=upload-welcome-voice&ivrid='.$ivrid,  'img'=>'fa fa-play', 'label'=>'Upload Welcome Voice', 'class'=>"lightboxWIFR",'dataattr'=>array('w'=>'600px','h'=>'450px')),
			array('href'=>'task=ivrs&act=config&ivrid='.$ivrid, 'img'=>'icon/chart_organisation.png', 'label'=>'View Tree')
		);
		if (false && $extra->voice_synth_module) {
			$data['topMenuItems'][] = array('href'=>'task=ivrs&act=ttsreload&rpage=update&ivrid='.$ivrid, 'img'=>'arrow_refresh_small.png', 'label'=>'TTS Reload');
		}
		$data['smi_selection'] = 'ivrs_';
		$this->getTemplate()->display('ivr_form', $data);
	}
	function actionUploadWelcomeVoice(){
	
		$ivrid=$this->getRequest()->getGet('ivrid');
		$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
		AddModel("MMOHFiller");
		AddModel("MLanguage");
		$mh=new MMOHFiller();
		$data=array();
		$data['fullload']=true;
		//$data['skill']=$ivr_id;
		$data['pageTitle'] = 'Upload Welcome Voice';
		$data['buttonTitle'] = 'Welcome Voice';
		//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
		AddHiddenFields("ivrid", $ivrid);
		$isError=false;
		//$target_path = $this->getTemplate()->file_upload_path . 'Q/' . $ivrid ."/";
		$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid ."/";
		$maxFileSize = $this->getTemplate()->maxFileSize;
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
					$mh->notifyIVRVoiceFileUpdate($ivrid, 'welcome');
					AddInfo("File uploaded successfully");
					$this->getTemplate()->display_popup_msg($data);
				}
			}
		} 
		$fle=$target_path."welcome.".$this->getTemplate()->voice_store_file_extension; 
		$data['fileInfo'] = [
			"file_name" => "welcome",
			"file_path" => site_url().$this->url("task=ivrs&act=get-voice-file&objType=IVR&objId={$ivrid}&fileName=welcome"),
			"file_dir" => $target_path."/",
			"file_delete_url" => file_exists($fle) ? $this->url("task=confirm-response&act=delete-ivr-welcome-file&pro=da&lan=EN&ivrid=".$ivrid) : ""
		];
		$this->getTemplate()->display_popup('mod-upload-welcome', $data);
	
	
	}

	function reloadTTS($ivrid)
	{
	    $path = '/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp';
	    if(is_dir($path)){
	        file_put_contents('/usr/local/gplexcc/regsrvr/engine/dashvar/tts.tmp', 'IVR:'.$ivrid."\n", FILE_APPEND);
	    }
	}
	
	function actionTtsreload()
	{
		include('model/MIvr.php');
		include('conf.extras.php');
		$ivr_model = new MIvr();

		$rpage = isset($_REQUEST['rpage']) ? trim($_REQUEST['rpage']) : '';
		$ivrid = isset($_REQUEST['ivrid']) ? trim($_REQUEST['ivrid']) : '';

		$ivr = $ivr_model->getIvrById($ivrid);
	
		if (empty($ivr) || !$extra->voice_synth_module) {
			exit;
		} else {
			$this->reloadTTS($ivrid);
		}
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=$rpage&ivrid=".$ivrid);
		$this->getTemplate()->display('msg', array('pageTitle'=>'Reload', 'isError'=>false, 'msg'=>'TTS reloaded successfully', 'redirectUri'=>$url));
		exit;
	}
	
	function getIVRTTSValues($ivrid)
	{
		$response = null;
		if (file_exists($this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/TTS.txt')) {
			$response = unserialize(file_get_contents($this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/TTS.txt'));
		}
		/*
		 else {
			mkdir($target_path, 0700);
		}
		*/
		
		if (!is_array($response)) {
			$response = array(
				'tts_off_hour' => '',
				'tts_welcome' => '',
				'tts_invalid_key' => '',
				'tts_language_select' => '',
				'tts_goodbye' => '',
				'tts_timeout_action' => ''
			);
			//file_put_contents($this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/TTS.txt', serialize($response));
			$this->setIVRTTSValues($ivrid, (object) $response);
			
			$response = array(
				'off_hour' => '',
				'welcome' => '',
				'invalid_key' => '',
				'language_select' => '',
				'goodbye' => '',
				'timeout_action' => ''
			);
		}
		return $response;
	}
	
	function saveIVRTTSValues($ivrid, $ivr)
	{
	    $oldvalues = $this->getIVRTTSValues($ivrid);
	    //$this->setIVRTTSValues($ivrid, $ivr);
	
	    /* if ($oldvalues['off_hour'] != $ivr->tts_off_hour || $oldvalues['welcome'] != $ivr->tts_welcome || $oldvalues['invalid_key'] != $ivr->tts_invalid_key ||
	        $oldvalues['language_select'] != $ivr->tts_language_select || $oldvalues['goodbye'] != $ivr->tts_goodbye ||
	        $oldvalues['timeout_action'] != $ivr->tts_timeout_action) {//skip
	            $this->setIVRTTSValues($ivrid, $ivr);
	            return true;
	        } */
	        return false;
	}
	
	function setIVRTTSValues($ivrid, $ivr)
	{
	   // GPrint($ivr);
	    /*
	      (
    [debug_mode] => N
    [TTS] => N
    [ivrid] => A
    [ivr_name] => IVR-A
    [dtmf_timeout] => 10
    [ivr_timeout] => 603
    [welcome_voice] => Y
    [editivr] => Update
    [ivr_id] => A
)
	     */
	    $response = array(
	        'off_hour' => $ivr->tts_off_hour,
	        'welcome' => $ivr->tts_welcome,
	        'invalid_key' => $ivr->tts_invalid_key,
	        'language_select' => $ivr->tts_language_select,
	        'goodbye' => $ivr->tts_goodbye,
	        'timeout_action' => $ivr->tts_timeout_action
	    );
		file_put_contents($this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/TTS.txt', serialize($response));
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
				mkdir($target_path, 0755,true);
			}
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/E';
			if (!file_exists($target_path)) {
				mkdir($target_path, 0755,true);
			}
			$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid . '/B';
			if (!file_exists($target_path)) {
				mkdir($target_path, 0755,true);
			}
		}
		return $ivr;
	}

	function getSubmittedIvr($ivrid)
	{
		$posts = $this->getRequest()->getPost();
		$ivr = new stdClass();

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$ivr->$key = trim($val);
			}
		}
		$ivr->ivr_id = $ivrid;
		//$ivr->language = $ivr->language1 . $ivr->language2;
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

	// function actionUploadVoiceFile(){	
		
	// 	$branch=$this->getRequest()->getGet('branch');	
		
	// 	AddModel("MLanguage");
	// 	AddModel("MIvr");
	// 	$ivrmodel=new MIvr();
	// 	$ivr_menu=$ivrmodel->getIVRDetailsByBranch($branch);
	// 	if(!$ivr_menu){
	// 		AddError("IVR Branch not found");
	// 	}
	// 	$data=array();
	// 	$data['fullload']=true;		
	// 	$data['pageTitle'] = 'Upload IVR Voice';
	// 	$data['buttonTitle'] = 'IVR Voice';
	// 	//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
	// 	AddHiddenFields("branch", $branch);
	// 	$isError=false;
	// 	$fileupload=array();
	// 	$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id ."/";
	// 	$isFileChanged = false;
	// 	if($this->getRequest()->isPost()){			
	// 		//$needtosaveeventkey=false;
	// 		//if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') {
	// 			//$file_name = $ivr_menu->event_key;
	// 		//} else {
	// 			$needtosaveeventkey=true;
	// 			$file_name = 'fl_' .  time();
	// 		//}
			
	// 		$isUploadable = false;
	// 		foreach ($_FILES["uploadfile"]['name'] as $lan=>$value){
	// 		    if ($_FILES["uploadfile"]["error"][$lan] != UPLOAD_ERR_NO_FILE){
    // 				if($_FILES["uploadfile"]["error"][$lan]==0){
    // 					$target_file = basename($value);
    // 					$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    // 					if(in_array($imageFileType, $this->getTemplate()->supported_extn)){ // need to add more file
    // 						if (true ||$_FILES["uploadfile"]["size"][$lan] > 500000) { // temporary skipped
    // 							$fileupload[$lan]=$_FILES["uploadfile"]["tmp_name"][$lan];
    // 							$isUploadable = true;
    // 						}else{
    // 						    $isError = true;
    // 							AddError("[$lan] Sorry, audio file is too large.");
    // 						}
    // 					}else{
    // 					    $isError = true;
    // 						AddError("[$lan] Invalid file type");    	
    // 					}
    // 				}
	// 		    }else {
	// 		        $isError = true;
	// 		        AddError("[$lan] No file selected");
	// 		    }
	// 		}
			
	// 		if ($isUploadable){
	// 		    $isError = false;
	// 		}
			
	// 		if(!$isError){
	// 			foreach ($fileupload as $key=>$vl){
	// 				$extension_type = pathinfo($target_file,PATHINFO_EXTENSION);
	// 				$fl=$target_path."{$key}/";
	// 				if(!is_dir($fl)){
	// 					mkdir($fl,0755,true);
	// 				}
	// 				if(file_exists($fl.$file_name.".$extension_type")){
	// 					unlink($fl.$file_name.".$extension_type");
	// 				}
	// 				if(!move_uploaded_file($vl, $fl.$file_name.".$extension_type")){
	// 					$isError =true;
	// 				} else {
	// 					$isFileChanged = true;
	// 				}
	// 			}
	// 			if ($isFileChanged) {
	// 				if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') {
	// 					$languages=MLanguage::getActiveLanguageList();
	// 					foreach ($languages as &$language){
	// 						$fle=$target_path."{$language->lang_key}/{$ivr_menu->event_key}.".$this->getTemplate()->store_file_extension;
	// 						$new_fle = $target_path."{$language->lang_key}/{$file_name}.".$this->getTemplate()->store_file_extension;
	// 						if (file_exists($new_fle)) {
	// 							if(file_exists($fle)){
	// 								unlink($fle);
	// 							}
	// 						} else {
	// 							if(file_exists($fle)){
	// 								$new_fle = $target_path."{$language->lang_key}/{$file_name}.".$this->getTemplate()->store_file_extension;
	// 								rename($fle, $new_fle);
	// 							}
	// 						}
	// 					}
	// 				}
	// 				if($needtosaveeventkey){
	// 					$ivrmodel->updateIVRDetailsByBranch($branch, array('event_key'=>$file_name));
	// 				}
	// 				AddModel("MMOHFiller");
	// 				$mh=new MMOHFiller();
	// 				$mh->notifyIVRVoiceFileUpdate($ivr_menu->ivr_id, '');
	// 			}
	// 			if(!$isError){
	// 				if($needtosaveeventkey){
	// 					$ivrmodel->updateIVRDetailsByBranch($branch, array('event_key'=>$file_name));
	// 				}
	// 				AddInfo("file Uploaded successfully");
	// 				$this->getTemplate()->display_popup_msg($data);
	// 			}
	// 		}
			
	// 	}
		
	// 	if ($isFileChanged) {
	// 		$ivr_menu=$ivrmodel->getIVRDetailsByBranch($branch);
	// 	}
		
	// 	$languages=MLanguage::getActiveLanguageList('A');
	// 	foreach ($languages as &$language){
	// 		$fle=$target_path."{$language->lang_key}/{$ivr_menu->event_key}.".$this->getTemplate()->store_file_extension;
	// 		if(!empty($ivr_menu->event_key)&& file_exists($fle)){
	// 			$language->file_delete_url=$this->url("task=confirm-response&act=delete-ivr-file&pro=da&lan={$language->lang_key}&branch=".$branch);
	// 		}else{
	// 			$language->file_delete_url="";
	// 		}
			
	// 	}
	// 	$data['languages']=&$languages;
	// 	$this->getTemplate()->display_popup('mod-upload-ivr-voice', $data);
	
	
	// }
	
	/**
	 * uploads tts file
	 */
	function ttsUpload($postData, $target_path){
		$ttsUsername = $this->getTemplate()->ttsUsername;
		$ttsPassword = $this->getTemplate()->ttsPassword;
		$ttsUrl = $this->getTemplate()->ttsUrl;

		$fileName = 'fl_'.time();
		$fl = $target_path."EN/";
		if(!is_dir($fl)){
			mkdir($fl,0755,true);
		}
		// create to auto genarated file
		$autoGenFileName = $fl.$fileName;
		$data = array(
			'text' => $postData['tts_text'],
			'user' => $ttsUsername,
			'password' => $ttsPassword
		);
		# Create a connection
		$url = $ttsUrl;
		$ch = curl_init($url);
		# Form data string
		$postString = http_build_query($data, '', '&');
		# Setting our options
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Get the response
		$response = curl_exec($ch);
		curl_close($ch);
		file_put_contents($autoGenFileName.".".$this->getTemplate()->voice_store_file_extension, $response);
		
	}

	public function actionAddIvrAsrWord(){
		AddModel("MIvr");
		$ivrmodel = new MIvr();
		$branch = $this->getRequest()->getGet('branch');
		$data = [];
		$data['fullload']=true;		
		$data['pageTitle'] = 'IVR ASR';
		$ivr_menu = $ivrmodel->getIVRDetailsByBranch($branch);
		$branchExceptNode = $ivr_menu->branch == $ivr_menu->ivr_id  ? $ivr_menu->branch : substr($ivr_menu->branch, 0, -1);
		$node = $ivr_menu->branch == $ivr_menu->ivr_id  ? '': substr($ivr_menu->branch, -1);

		$data['ivrAsrWordsData'] = $ivrmodel->getIVRAsrWords($ivr_menu->ivr_id, $branchExceptNode, $node);
		if($this->getRequest()->isPost()){
			$post = $_POST;
			// ivr asr word insert/update
			if( isset($post['asr_text']) && !empty($post['asr_text']) ){ 
				if(strlen($post['asr_text']) <= 255){
					$asrData = ['match_type' => '1', 'words' => $post['asr_text']];
					$result = $ivrmodel->insertOrUpdateIVRAsrWords($ivr_menu->ivr_id, $branchExceptNode, $node, $asrData);
					if($result['result'] == true){
						$msg = $result['is_new_record'] == true ? 'added' : 'updated';
						AddInfo("ASR text {$msg} successfully");
					}
				}else{
					AddError("ASR text max length 255 character!");
				}
				
			}else if( isset($post['asr_text']) && empty($post['asr_text']) && !empty($data['ivrAsrWordsData']->words) ){
				$isRemoved = $ivrmodel->removeIVRAsrWords($ivr_menu->ivr_id, $branchExceptNode, $node);
				if($isRemoved){
					AddInfo("ASR text remove successfully");
				}
			}
		}
		$this->getTemplate()->display_popup('ivr-asr', $data);
	}

	function actionUploadVoiceFile(){
		
		$branch=$this->getRequest()->getGet('branch');
		
		AddModel("MLanguage");
		AddModel("MIvr");
		$ivrmodel=new MIvr();
		$ivr_menu=$ivrmodel->getIVRDetailsByBranch($branch);
		
		if(!$ivr_menu){
			AddError("IVR Branch not found");
		}
		$data=array();
		$data['fullload']=true;		
		$data['pageTitle'] = 'Upload IVR Voice';
		$data['buttonTitle'] = 'IVR Voice';
		$data['multiFileUpload'] = false;
		if($ivr_menu->event == "PF"){
			$data['multiFileUpload'] = true;
		}
		$currentTime = time();	
		$file_name = 'fl_'.$currentTime;
		$arg2 = $file_name;
		if(!empty($ivr_menu->arg2)){ 
			$data['ivrMultiFiles'] = $ivrmodel->getIVRMultiFiles($ivr_menu->arg2); 
			$arg2 = $ivr_menu->arg2;
		}
		
		//$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
		AddHiddenFields("branch", $branch);
		$isError=false;
		$fileupload=array();
		$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id ."/";
		$isFileChanged = false;
		$ttsUpload = false;
		$data['hasTtsUpload'] = true;
		
		$doNotDelPath = $this->getTemplate()->file_upload_path . 'IVR/do_not_delete';
		if(!file_exists($doNotDelPath)){
			AddError("No mount file found");
			$this->getTemplate()->display_popup_msg($data);
		}
		
		$maxFileSize = $this->getTemplate()->maxFileSize;
		$hasMultiFiles = false;

		$languages = MLanguage::getActiveLanguageList('A'); 
		$isEventKeyUpdate = false;
		if($this->getRequest()->isPost()){
			$post = $_POST; 
			$needtosaveeventkey = false;
			
			$isUploadable = false;
			// is tts file upload
			if( (isset($post['tts']) && ($post['tts'] == "on")) && (isset($post['tts_text']) && !empty($post['tts_text'])) ){ 
				// upload tts file
				$fileupload = [];
				$this->ttsUpload($post, $target_path);
				$isUploadable = true;
				$ttsUpload = true;
				$ttsTxtFileName = 'tts_fl_'.$currentTime;
				$fileupload['EN']['name'][] = $file_name.".".$this->getTemplate()->voice_store_file_extension; 
				$this->removeIvrMultiFiles($ivr_menu, $languages);
			}

			$asrUpload = $this->uploadAsrFiles($_FILES['asr'], $ivr_menu, $file_name);
			$civrUpload = $this->uploadCivrFiles($_FILES['civr'], $ivr_menu, $file_name);
			if(($asrUpload == true || $civrUpload == true)){
				$isUploadable = true;
			}
			
			if($ttsUpload == false){
				foreach ($_FILES["uploadfile"]['name'] as $lan=>$lanValue){
					foreach($lanValue as $key => $value){
						if ($_FILES["uploadfile"]["error"][$lan][$key] != UPLOAD_ERR_NO_FILE){
							if($_FILES["uploadfile"]["error"][$lan][$key] ==0 && ($_FILES["uploadfile"]["size"][$lan][$key] > 0)){
								$target_file = basename($value); 
								$fileExt = explode(".", $target_file);
								$fileExt = strtolower(end($fileExt));
								$fileBaseName = basename($value,".".$fileExt);
								
								$fileProp = exec('file -b '. $_FILES["uploadfile"]["tmp_name"][$lan][$key]);
								
								$check16Bit = strpos($fileProp, "16 bit");
								$checkMono = strpos($fileProp, "mono");
								$checkHz = strpos($fileProp, "8000 Hz");
								
								if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) && ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
								// if( true ){ 
								
									// size in bytes, 1000000 bytes = 1 MB
									if ($_FILES["uploadfile"]["size"][$lan][$key] <= $maxFileSize) {
										$fileupload[$lan]['tmp_name'][$key] = $_FILES["uploadfile"]["tmp_name"][$lan][$key];
										$fileupload[$lan]['name'][$key] = $_FILES["uploadfile"]["name"][$lan][$key];
										$isUploadable = true;
										if($key > 0){
											$hasMultiFiles= true;
										}
									}else{
										$isError = true;
										$sizeInMB = $maxFileSize / 1000000;
										AddError("[$lan][$key] Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB");
									}
								}else{
									$isError = true;
									AddError("[$lan][$key] Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
								}
							}
						}
						else {
							$isError = true;
							// AddError("[$lan][$key] No file selected");
						}
					}
					
				}

			}

			
			if ($isUploadable){
			    $isError = false;
			}
			// echo "<pre>";
			//  print_r($fileupload); 
			//  die();
			if(empty($fileupload)){
				AddError("No IVR file selected");
			}
			if(!$isError){ 
				if(!empty($fileupload)){
					foreach ($fileupload as $key=>$vl){ 
						$extension_type = explode(".", $target_file);
						$extension_type = strtolower(end($extension_type));
						
						$fl=$target_path."{$key}/";
						if(!is_dir($fl)){
							mkdir($fl,0755,true);
						}
						// remove file
						$flnameKey = "";
						foreach($vl['name'] as $mkey => $mval){
							if(file_exists($fl.$file_name.".$extension_type")){
								unlink($fl.$file_name.".$extension_type");
								unlink($fl.$file_name.".ulaw");
								unlink($fl.$file_name.".alaw");
								unlink($fl.$file_name.".txt");
								@unlink($fl.$ttsTxtFileName.".txt");
							}
							
						}
						
						if($ttsUpload == true) {
							// create to auto genarated file
							$autoGenFileName = $fl.$file_name; 
							exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
							exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
							// create a file with real media file name
							$file = fopen($autoGenFileName.".txt","w");
							fwrite($file,$vl['name']['0']);
							fclose($file);
							$isFileChanged = true;
							// create a txt file with tts text
							$ttsFile = fopen($fl.$ttsTxtFileName.".txt","w");
							fwrite($ttsFile,$post['tts_text']);
							fclose($ttsFile); 
	
						}else if(empty($post['tts_text']) && $ttsUpload == false){
							$flnameKey = ""; 
							foreach($vl['name'] as $mkey => $mval){
								$upFileName = $file_name;
								if($mkey != 0){
									$upFileName = $file_name.$mkey;
								} 
	
								if(move_uploaded_file($vl['tmp_name'][$mkey], $fl.$upFileName.$flnameKey.".$extension_type")){ 
									// create to auto genarated file
									$autoGenFileName = $fl.$upFileName.$flnameKey; 
									exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
									exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
									// create a file with real media file name
									$file = fopen($autoGenFileName.".txt","w");
									fwrite($file,$mval);
									fclose($file);
									$isFileChanged = true;
								}
								
								
							}
							
						}else if(!empty($post['tts_text']) && $ttsUpload == false){ 
							AddError("Please remove tts file to upload a new file.");
							$this->getTemplate()->display_popup_msg($data); 
						} 
					}
				}
				
				if ($isFileChanged) { 
					$needtosaveeventkey = true;
					if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') { 
						$multiFileKeys = [];
						foreach ($languages as &$language){
							
							$fle = $target_path."{$language->lang_key}/{$ivr_menu->event_key}";
							$fle_with_ext = $fle.".".$this->getTemplate()->voice_store_file_extension;

							$new_fle = $target_path."{$language->lang_key}/{$file_name}";
							$new_fle_with_ext = $new_fle.".".$this->getTemplate()->voice_store_file_extension;

							$tts_fle = $target_path."{$language->lang_key}/tts_{$ivr_menu->event_key}";
							$new_tts_fle = $target_path."{$language->lang_key}/tts_{$file_name}";

							$this->renameOldFile($ivr_menu, 'ASR/'.$language->lang_key, $file_name);
							$this->renameOldFile($ivr_menu, 'CIVR/'.$language->lang_key, $file_name);
							

							if (file_exists($new_fle_with_ext) && file_exists($fle_with_ext)) {
								unlink($fle_with_ext);
								unlink($fle.".ulaw");
								unlink($fle.".alaw");
								unlink($fle.".txt");
								@unlink($tts_fle.".txt");
								
							}else if(file_exists($fle_with_ext)){ 
								rename($fle_with_ext, $new_fle_with_ext);
								rename($fle.".ulaw", $new_fle.".ulaw");
								rename($fle.".alaw", $new_fle.".alaw");
								rename($fle.".txt", $new_fle.".txt");
								@rename($tts_fle.".txt", $new_tts_fle.".txt");
								
							} 
							if(isset($fileupload[$language->lang_key]['name'] ) && !empty($fileupload[$language->lang_key]['name']) ){
								foreach($fileupload[$language->lang_key]['name'] as $multiKey => $multiValue){
									if($multiKey == '0') continue;
									$multiFileKeys[$multiKey] = $multiKey;
								}
							}
							
							
						}
						$fileList = $ivrmodel->getIvrMultiFileList($ivr_menu->arg2);
						foreach ($languages as &$language){
							foreach($multiFileKeys as $multiKey){
								if(isset($fileList[$multiKey])){
									$file = $fileList[$multiKey];
									$multi_fle = $target_path."{$language->lang_key}/{$file}";
									$multi_fle_with_ext = $multi_fle.".".$this->getTemplate()->voice_store_file_extension;

									$multi_new_fle = $target_path."{$language->lang_key}/{$file_name}".$multiKey;
									$multi_new_fle_with_ext = $multi_new_fle.".".$this->getTemplate()->voice_store_file_extension;

									$multi_tts_fle = $target_path."{$language->lang_key}/tts_{$ivr_menu->event_key}".$multiKey;
									$multi_new_tts_fle = $target_path."{$language->lang_key}/tts_{$file_name}".$multiKey;

									if (file_exists($multi_new_fle_with_ext) && file_exists($multi_fle_with_ext)) {
										unlink($multi_fle_with_ext);
										unlink($multi_fle.".ulaw");
										unlink($multi_fle.".alaw");
										unlink($multi_fle.".txt");
										@unlink($multi_tts_fle.".txt");
										
									}else if(file_exists($multi_fle_with_ext)){ 
										rename($multi_fle_with_ext, $multi_new_fle_with_ext);
										rename($multi_fle.".ulaw", $multi_new_fle.".ulaw");
										rename($multi_fle.".alaw", $multi_new_fle.".alaw");
										rename($multi_fle.".txt", $multi_new_fle.".txt");
										@rename($multi_tts_fle.".txt", $multi_new_tts_fle.".txt");
										
									} 

								}	

								
							}
						}
					}
					if($needtosaveeventkey){ 
						$ivrmodel->updateIVRDetailsByBranch($branch, array('event_key'=>$file_name));
						$isEventKeyUpdate = true;
						if(empty($post['tts_text']) && $ttsUpload == false && $hasMultiFiles == true){
							$ivrmodel->updateIVRMultiPrompt($arg2, $ivr_menu, $fileupload, $file_name); 
						}	
					}
					AddModel("MMOHFiller");
					$mh=new MMOHFiller();
					$mh->notifyIVRVoiceFileUpdate($ivr_menu->ivr_id, '');
				}
				if(!$isError){
					AddInfo("file Uploaded successfully");
					$this->getTemplate()->display_popup_msg($data);
				}
			}
			
		}
		
		if ($isFileChanged) { 
			$ivr_menu=$ivrmodel->getIVRDetailsByBranch($branch);
		}
		
		$multiFileSl = [];
		$multiFileFlag = 0;
		// Gprint($data['ivrMultiFiles']);
		// Gprint($languages);
	
		foreach ($languages as &$language){
			$fle = $target_path."{$language->lang_key}/{$ivr_menu->event_key}";
			$fle_with_ext = $fle.".".$this->getTemplate()->voice_store_file_extension; 

			$language->file_info[] = [
				'file_name' => $ivr_menu->event_key,
				'file_path' => site_url().$this->url("task=ivrs&act=get-voice-file&objType=IVR&objId={$ivr_menu->ivr_id}&lang={$language->lang_key}&fileName={$ivr_menu->event_key}"),
				'file_dir' => $target_path."{$language->lang_key}/",
				'file_sl' => "0",
				'file_delete_url' => !empty($ivr_menu->event_key)&& file_exists($fle_with_ext) ? $this->url("task=confirm-response&act=delete-ivr-file&pro=da&lan={$language->lang_key}&branch=".urlencode($branch)) : ""
			];

			$ivr_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id.'/';
			$asr_fle = $ivr_file_path."ASR/{$language->lang_key}/{$ivr_menu->event_key}";
			$asr_fle_with_ext = $asr_fle.".".$this->getTemplate()->voice_store_file_extension; 
			$language->asr_file_info[] = [
				'file_name' => $ivr_menu->event_key,
				'file_path' => site_url().$this->url("task=ivrs&act=get-voice-file&objType=IVR&objId={$ivr_menu->ivr_id}/ASR&lang={$language->lang_key}&fileName={$ivr_menu->event_key}"),
				'file_dir' => $ivr_file_path ."ASR/".$language->lang_key.'/',
				'file_sl' => "0",
				'file_delete_url' => !empty($ivr_menu->event_key)&& file_exists($asr_fle_with_ext) ? $this->url("task=confirm-response&act=delete-ivr-asr-file&pro=da&lan={$language->lang_key}&branch=".urlencode($branch)) : ""
			];

			$ivr_file_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id.'/';
			$civr_fle = $ivr_file_path."CIVR/{$language->lang_key}/{$ivr_menu->event_key}";
			$civr_fle_with_ext = $civr_fle.".".$this->getTemplate()->voice_store_file_extension; 
			$language->civr_file_info[] = [
				'file_name' => $ivr_menu->event_key,
				'file_path' => site_url().$this->url("task=ivrs&act=get-voice-file&objType=IVR&objId={$ivr_menu->ivr_id}/ASR&lang={$language->lang_key}&fileName={$ivr_menu->event_key}"),
				'file_dir' => $ivr_file_path ."CIVR/".$language->lang_key.'/',
				'file_sl' => "0",
				'file_delete_url' => !empty($ivr_menu->event_key)&& file_exists($civr_fle_with_ext) ? $this->url("task=confirm-response&act=delete-civr-file&pro=da&lan={$language->lang_key}&branch=".urlencode($branch)) : ""
			];

			$ttsFileExists = $target_path."EN/tts_".$ivr_menu->event_key.".txt";
			if(isset($data['ivrMultiFiles']) && !empty($data['ivrMultiFiles']) && !file_exists($ttsFileExists)){
				foreach($data['ivrMultiFiles'] as $fkey => $fval){
					$fle = $target_path."{$language->lang_key}/{$fval->file_id}";
					$fle_with_ext = $fle.".".$this->getTemplate()->voice_store_file_extension; 
					
					$fileId = $fval->file_id;
					$language->file_info[] = [
						'file_name' => $fval->file_id,
						'file_path' => site_url().$this->url("task=ivrs&act=get-voice-file&objType=IVR&objId={$ivr_menu->ivr_id}&lang={$language->lang_key}&fileName={$fval->file_id}"),
						'file_dir' => $target_path."{$language->lang_key}/",
						'file_sl' => $fval->sl,
						'file_delete_url' => !empty($fval->file_id)&& file_exists($fle_with_ext) ? $this->url("task=confirm-response&act=delete-ivr-file&pro=da&lan={$language->lang_key}&branch=".urlencode($branch)."&fileid={$fileId}") : ""
					];


				}

			}
		} 

		$ttsTxtFileName = "tts_".$languages[0]->file_info[0]['file_name'];
		$ttsTxtFileNameWithExt = $ttsTxtFileName.".txt";
		$ttsTxtFilePath = $languages[0]->file_info[0]['file_dir'].$ttsTxtFileNameWithExt;

		if(file_exists($ttsTxtFilePath)){
			unset($languages['1']);
		}
		$data['languages']=&$languages;
		// Gprint($multiFileSl);
		// Gprint($languages);
		$this->getTemplate()->display_popup('mod-upload-ivr-voice', $data);
	}

	public function renameOldFile($ivr_menu, $langKey, $filename){
		$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id ."/";

		$file = $target_path."{$langKey}/{$ivr_menu->event_key}";
		$file_with_ext = $file.".".$this->getTemplate()->voice_store_file_extension;

		$new_file = $target_path."{$langKey}/{$filename}";
		$new_file_with_ext = $new_file.".".$this->getTemplate()->voice_store_file_extension;

		if (file_exists($new_file_with_ext) && file_exists($file_with_ext)) {
			unlink($file_with_ext);
			unlink($file.".ulaw");
			unlink($file.".alaw");
			unlink($file.".txt");
			
		}else if(file_exists($file_with_ext)){ 
			rename($file_with_ext, $new_file_with_ext);
			rename($file.".ulaw", $new_file.".ulaw");
			rename($file.".alaw", $new_file.".alaw");
			rename($file.".txt", $new_file.".txt");
			
		} 
	}

	public function validateUploadFile($files){
		$fileupload = [];
		$maxFileSize = $this->getTemplate()->maxFileSize;
		foreach ($files['name'] as $lan=>$lanValue){
			foreach($lanValue as $key => $value){
				if ($files["error"][$lan][$key] != UPLOAD_ERR_NO_FILE){
					if($files["error"][$lan][$key] ==0 && ($files["size"][$lan][$key] > 0)){
						$target_file = basename($value); 
						$fileExt = explode(".", $target_file);
						$fileExt = strtolower(end($fileExt));
						
						$fileProp = exec('file -b '. $files["tmp_name"][$lan][$key]);
						
						$check16Bit = strpos($fileProp, "16 bit");
						$checkMono = strpos($fileProp, "mono");
						$checkHz = strpos($fileProp, "8000 Hz");
						
						$sizeInMB = $maxFileSize / 1000000;
						if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) && ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
						// if( true ){ 
						
							// size in bytes, 1000000 bytes = 1 MB
							if ($files["size"][$lan][$key] <= $maxFileSize) {
								$fileupload[$lan]['tmp_name'][$key] = $files["tmp_name"][$lan][$key];
								$fileupload[$lan]['name'][$key] = $files["name"][$lan][$key];
							}else{
								$fileupload[$lan]['error_msg'][$key] = "[$lan][$key] Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB";
							}
						}else{
							$fileupload[$lan]['error_msg'][$key] = "[$lan][$key] Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file";	
						}
					}
				}
				else {
					$fileupload[$lan]['error_msg'][$key] = "[$lan][$key] No file selected";
				}
			}
			
		}
		return $fileupload;
	}

	public function uploadAsrFiles($files, $ivr_menu, $file_name){
		$fileupload = [];
		$file_name = !empty($ivr_menu->event_key) ? $ivr_menu->event_key : $file_name;

		$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id ."/ASR/";
		$fileupload = $this->validateUploadFile($files);
		$isFileUploaded = false;
		foreach ($fileupload as $key => $vl){ 
			if(!isset($vl['error_msg']) || !empty($vl['error_msg'])){
				$fl = $target_path."{$key}/";
				if(!is_dir($fl)){
					mkdir($fl,0755,true);
				}

				foreach($vl['name'] as $mkey => $mval){
					$extension_type = explode(".", $mval);
					$extension_type = strtolower(end($extension_type));
					$upFileName = $file_name;
					if($mkey != 0){
						$upFileName = $file_name.$mkey;
					} 
					
					if(file_exists($fl.$upFileName.".$extension_type")){
						unlink($fl.$upFileName.".$extension_type");
						unlink($fl.$upFileName.".ulaw");
						unlink($fl.$upFileName.".alaw");
						unlink($fl.$upFileName.".txt");
					}

					if(move_uploaded_file($vl['tmp_name'][$mkey], $fl.$upFileName.".$extension_type")){
						// create to auto genarated file
						$autoGenFileName = $fl.$upFileName; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$mval);
						fclose($file);
						$isFileUploaded = true;
					}
					
				}
			}
			
		}
		return $isFileUploaded;
	}
	public function uploadCivrFiles($files, $ivr_menu, $file_name){
		$fileupload = [];
		$file_name = !empty($ivr_menu->event_key) ? $ivr_menu->event_key : $file_name;

		$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivr_menu->ivr_id ."/CIVR/";
		$fileupload = $this->validateUploadFile($files);
		$isFileUploaded = false;
		foreach ($fileupload as $key => $vl){ 
			if(!isset($vl['error_msg']) || !empty($vl['error_msg'])){
				$fl = $target_path."{$key}/";
				if(!is_dir($fl)){
					mkdir($fl,0755,true);
				}

				foreach($vl['name'] as $mkey => $mval){
					$extension_type = explode(".", $mval);
					$extension_type = strtolower(end($extension_type));
					$upFileName = $file_name;
					if($mkey != 0){
						$upFileName = $file_name.$mkey;
					} 
					
					if(file_exists($fl.$upFileName.".$extension_type")){
						unlink($fl.$upFileName.".$extension_type");
						unlink($fl.$upFileName.".ulaw");
						unlink($fl.$upFileName.".alaw");
						unlink($fl.$upFileName.".txt");
					}

					if(move_uploaded_file($vl['tmp_name'][$mkey], $fl.$upFileName.".$extension_type")){
						// create to auto genarated file
						$autoGenFileName = $fl.$upFileName; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$mval);
						fclose($file);
						$isFileUploaded = true;
					}
					
				}
			}
			
		}
		return $isFileUploaded;
	}

	public function removeIvrMultiFiles($ivrDetail, $languages){
		AddModel("MIvr");
		$ivrmodel=new MIvr();

		$targetPath = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrDetail->ivr_id . '/'; 
		if(!empty($ivrDetail->arg2)){
			$files = $ivrmodel->getIVRMultiFiles($ivrDetail->arg2);
			$bnFile = $targetPath."BN/{$ivrDetail->event_key}";
			$bnFileWithExt = $bnFile.".".$this->getTemplate()->voice_store_file_extension;
			if(file_exists($bnFileWithExt)){
				unlink($bnFileWithExt);
				@unlink($bnFile.".ulaw");
				@unlink($bnFile.".alaw");
				unlink($bnFile.".txt");
			}
			foreach($languages as $language){
				foreach($files as $file){
					$fle = $targetPath."{$language->lang_key}/{$file->file_id}";
					$fle_with_ext = $fle.".".$this->getTemplate()->voice_store_file_extension;

					if (file_exists($fle_with_ext)) {
						unlink($fle_with_ext);
						@unlink($fle.".ulaw");
						@unlink($fle.".alaw");
						unlink($fle.".txt");
						
					}
				}
				

			}
			$ivrmodel->removeIvrMultiFiles($ivrDetail->branch,$ivrDetail->arg2);
		}
	}
	
	function actionGetVoiceFile(){ 
		$objType = $this->getRequest()->getRequest('objType',null); 
		$objId = $this->getRequest()->getRequest('objId',null); 
		$lang = $this->getRequest()->getRequest('lang',null); 
		$fileName = $this->getRequest()->getRequest('fileName',null); 

		$fileNameWithExt = $this->getRequest()->getRequest('fileName',null).".".$this->getTemplate()->voice_store_file_extension; 
		$dst_file = $this->getTemplate()->file_upload_path.$objType.'/'.$objId."/". $fileName.".".$this->getTemplate()->voice_store_file_extension;
		if(!empty($lang)){
			$dst_file = $this->getTemplate()->file_upload_path.$objType.'/'.$objId."/".$lang."/". $fileName.".".$this->getTemplate()->voice_store_file_extension;
		}

		$mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
		if(file_exists($dst_file) && filesize($dst_file) > 0){
	
			header("Content-Description: File Transfer");
			header('Content-type: {$mime_type}');
			header('Content-length: ' . filesize($dst_file));
			header('Content-Transfer-Encoding: binary');
			header('Content-type: application/force-download');
			header('Content-Disposition: attachment; filename="' . $fileNameWithExt . '"');
			header("Expires: 0");
			header("Cache-Control: must-revalidate");
			header("Pragma: public");
			header("Content-Length: " . filesize($dst_file));
			
			ob_clean();
			flush();
			readfile($dst_file);


		} else {
			header("HTTP/1.0 404 Not Found");
		}

	}

	function actionTransfertoivr()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		$data['topMenuItems'] = array(
			array('href'=>'task=ivrs&act=add-transfer-ivr', 'img'=>'fa fa-plus-square-o', 'label'=>'Add Transfer to IVR'),
			array('href'=>'task=ivrs', 'img'=>'fa fa-list', 'label'=>'IVR List')
		);
	
		$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		$data['smi_selection'] = 'ivrs_';
		$data['pageTitle'] = 'Transfer to IVR';
		$data['dataUrl'] = $this->url('task=get-home-data&act=ivrtransfer');
		$this->getTemplate()->display('ivr_transfer', $data);
	}
	
	function actionAddTransferIvr()
	{
		$this->saveTransferIvr();
	}
	
	function actionEditTransferIvr()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveTransferIvr($tid);
	}
	
	function saveTransferIvr($tid='')
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		$ivr_model = new MIvr();		
		$skill_model = new MSkill();
	
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$ivrTData = $this->getSubmittedTIvrData();
			 
			if (empty($ivrTData->title)) {
				$errMsg = "Provide Title";
			}elseif (!empty($ivrTData->ivr_branch) && !$ivr_model->isIVRBranchExist($ivrTData->ivr_branch)) {
				$errMsg = "Provide Valid IVR Branch";
			}
			 
			if (empty($errMsg)) {
				$is_success = false;
                $has_record = false;
				    
				if(empty($tid)){
					$has_record = $ivr_model->hasTransferIvrBranchAdded($ivrTData->ivr_branch);
					if($has_record){
            			$ivr_model->removeExistingTransferToIVR($ivrTData->ivr_branch);
	                }
				}else{
					$has_record = $ivr_model->hasTransferIvrBranchAdded($tid);
					if($has_record){
            			$ivr_model->removeExistingTransferToIVR($tid);
	                }
				}			    
			    // GPrint($has_record);
                

                if(in_array("", $ivrTData->skill_id)){
                	$ivrTData->skill_id_transfer = "";
                    if ($ivr_model->saveTransferToIVR($ivrTData)) {
                        $ivr_model->addToAuditLog('Transfer to IVR', 'A', "Title #=$ivrTData->title", "Transfer to IVR Added");
                        $errMsg = 'Transfer to IVR added successfully.';
                        $is_success = true;
                    } else {
                        $errMsg = 'Failed to add Transfer to IVR !!';
                    }
                } else {
                    foreach ($ivrTData->skill_id as $skill_id){
                    	$ivrTData->skill_id_transfer = $skill_id;

                        if ($ivr_model->saveTransferToIVR($ivrTData)) {
                            $ivr_model->addToAuditLog('Transfer to IVR', 'A', "Title #=$ivrTData->title", "Transfer to IVR Added");
                            $errMsg = 'Transfer to IVR added successfully.';
                            $is_success = true;
                        } else {
                            $errMsg = 'Failed to add Transfer to IVR !!';
                        }                            
                    }
                }
	
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=".$this->getRequest()->getControllerName()."&act=transfertoivr");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
		} else {
			$ivrTData = $this->getInitialTIvrData($tid, $ivr_model);
			if (empty($ivrTData)) {
				exit;
			}
		}
	
		$selectArr = array(''=>'All Skill');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		$data['tIvrData'] = $ivrTData;
		$data['transferid'] = $tid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($tid) ? 'Add Transfer to IVR' : 'Update Transfer to IVR';
	
		$data['smi_selection'] = 'ivrs_';
		$this->getTemplate()->display('ivr_transfer_form', $data);
	}
	
	function getSubmittedTIvrData()
	{
		$posts = $this->getRequest()->getPost();
		$ivrTData = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
			    if(is_array($val)){
                    $ivrTData->$key = $val;
                }else{
                    $ivrTData->$key = trim($val);
                }
			}
		}
	
		return $ivrTData;
	}
	
	function getInitialTIvrData($tid, $ivr_model)
	{
		$ivrTData = null;
	
		if (empty($tid)) {
			$ivrTData = new stdClass();
			$ivrTData->skill_id = "";
			$ivrTData->title = "";
			$ivrTData->ivr_branch = "";
			$ivrTData->verified_call_only = "";
			$ivrTData->active = "";
		} else {
			$ivrTDataArr = $ivr_model->getIVRTranById($tid);
			$ivrTData = [];

			foreach ($ivrTDataArr as $key => $item) {
				if($key==0){
					$ivrTData = $item;
					$ivrTData->skill_id = [$item->skill_id];
				}else{
					$ivrTData->skill_id[] = $item->skill_id;
				}
			}
		}
		return $ivrTData;
	}
}
