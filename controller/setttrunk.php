<?php

class Setttrunk extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $data['pageTitle'] = 'Trunk List';
		$data['side_menu_index'] = 'settings';
		$data['dataUrl'] = $this->url('task=get-tools-data&act=setttrunkinit');
		$this->getTemplate()->display('settings_trunks', $data);
	}
	
	
	function actionMultiCcRoute()
	{
	    $data['pageTitle'] = 'Trunk List';
	    $data['side_menu_index'] = 'settings';
	    $data['dataUrl'] = $this->url('task=get-tools-data&act=setttrunkinit');
	    $this->getTemplate()->display('settings_multi_cc_route', $data);
	}

	function actionActivate()
	{
		$trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		
		if ($status == 'Y' || $status == 'N') {
			include('model/MSetting.php');
			$setting_model = new MSetting();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			if ($setting_model->updateTrunkStatus($trunkid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Trunk', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Trunk', 'isError'=>true, 'msg'=>'Failed to Update Status', 'redirectUri'=>$url));
			}
		}
	}

	function actionAdd()
	{
		$this->saveTrunk();
	}

	function actionUpdate()
	{
		$trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';
		if (!empty($trunkid)) $this->saveTrunk($trunkid);
	}

	function actionDel()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();
		
		$trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		
		if ($setting_model->deleteTrunk($trunkid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Trunk', 'isError'=>false, 'msg'=>'Trunk Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Trunk', 'isError'=>true, 'msg'=>'Failed to Delete Trunk', 'redirectUri'=>$url));
		}
		
	}
	
	function actionAddcli()
	{
		$trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';
		$this->saveCLI($trunkid);
	}
	
	function actionDelcli()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		list($trunkid, $skillid, $dialprefix) = explode('_', $id);
		if ($setting_model->deleteTrunkCLI($trunkid, $skillid, $dialprefix)) {
			echo "Y";
		} else {
			echo "N";
		}
		exit;
	}
	
	function saveCLI($trunkid='')
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		$setting_model = new MSetting();
		$skill_model = new MSkill();
		$errMsg = '';
		$errType = 1;
		$trunk = $setting_model->getTrunkById($trunkid);
		
		if (empty($trunk)) {
			exit;
		}
		$fixed_prefix = $trunk->dial_prefix;
		//var_dump($fixed_prefix);
		
		$data['prefix_len'] = strlen($fixed_prefix);
		$data['fixed_prefix'] = $fixed_prefix;

		//$skill_options = array_merge(array('' => 'Select'), $skill_model->getOutSkillOptions('Y'));
		$skill_options = $skill_model->getOutSkillOptions();
		//$skill_options[''] = 'Select';
		//var_dump($skill_options);
		$skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
		$skillname = isset($skill_options[$skill]) ? $skill_options[$skill] : '';
		
		$data['pageTitle'] = empty($data['old_skill']) ? 'Add New CLI :: ' . $trunk->trunk_name : 'Update CLI :: ' . $trunk->trunk_name;
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$trunkCLI = $this->getSubmittedTrunkCLI();
			$errMsg = $this->getCLIValidationMsg($trunkid, $trunk->dial_prefix, $trunkCLI, $setting_model);

			$data['skill'] = $trunkCLI->skill;
			$data['dial_prefix'] = $trunkCLI->dial_prefix;
			$data['cli'] = $trunkCLI->cli;
			$data['old_skill'] = $trunkCLI->old_skill;
			$data['old_dial_prefix'] = $trunkCLI->old_dial_prefix;
			$data['old_cli'] = $trunkCLI->old_cli;
			
			if (empty($errMsg)) {

				$is_success = false;
				$trunkCLI->dial_prefix = $fixed_prefix . $trunkCLI->dial_prefix;
				if (empty($trunkCLI->old_skill)) {
					if ($setting_model->addCLINode($trunkid, $trunkCLI, $trunk->trunk_name, $skillname)) {
						$errMsg = 'CLI added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add CLI !!';
					}
				} else {

					if ($setting_model->updateCLINode($trunkid, $trunkCLI, $trunk->trunk_name, $skillname)) {
						$errMsg = 'CLI updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
					
				if ($is_success) {
				
					$data['message'] = $errMsg;
					$data['msgType'] = 'success';
					$data['refreshParent'] = true;
					
					$this->getTemplate()->display_popup('popup_message', $data);
					//$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					//$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
						//CONTENT=\"2;URL=$url\">";
				}
			}
		} else {
			//$cli = $this->getInitialCli($trunkid, $setting_model);
			$trunkCLI = null;
			$data['skill'] = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
			$data['dial_prefix'] = isset($_REQUEST['dial_prefix']) ? trim($_REQUEST['dial_prefix']) : '';
			
			if (!empty($data['skill'])) {
				
				$trunkCLI = $setting_model->getTrunkCLINode($trunkid, $data['skill'], $data['dial_prefix']);
			}
			
			if (empty($trunkCLI) && !empty($data['skill'])) exit;
			
			$data['cli'] = empty($trunkCLI) ? '' : $trunkCLI->cli;
			$data['old_skill'] = $data['skill'];
			$data['old_dial_prefix'] = $data['dial_prefix'];
			$data['old_cli'] = $data['cli'];
			$data['dial_prefix'] = empty($data['dial_prefix']) ? '' : substr($data['dial_prefix'], $data['prefix_len']);
		}
		
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['skill_options'] = $skill_options;
		$data['trunkid'] = $trunkid;
		//if (!isset($data['is_root'])) $data['is_root'] = false;
		$this->getTemplate()->display_popup('settings_trunk_cli', $data);
	}
	
	function saveTrunk($trunkid='')
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		$setting_model = new MSetting();
		$skill_model = new MSkill();
		
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$trunk = $this->getSubmittedTrunk($trunkid);
			
			$errMsg = $this->getValidationMsg($trunk, $trunkid);
			if (empty($errMsg)) {
				$trunk_id = empty($trunkid) ? $setting_model->getEmptyTrunkId() : $trunkid;

				if (!empty($trunk_id)) {
					
					$is_success = false;

					if (empty($trunkid)) {
						if ($setting_model->addTrunk($trunk_id, $trunk)) {
							$errMsg = 'Trunk added successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'Trunk name already exist !!';
						}
					} else {
						$oldtrunk = $this->getInitialTrunk($trunkid, $setting_model);
						if ($setting_model->updateTrunk($oldtrunk, $trunk)) {
							$errMsg = 'Trunk updated successfully !!';
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

				} else {
					$errMsg = 'Maximum number of trunks already exist !!';
				}
			}
			
		} else {
			$trunk = $this->getInitialTrunk($trunkid, $setting_model);
			if (empty($trunk)) {
				exit;
			}
		}
		
		$trunk->numbers = $setting_model->getTrunkCLIs($trunkid);
		$data['dataUrl'] = $this->url('task=get-tools-data&act=setttrunkcli&trunkid='.$trunkid);
		$data['trunk'] = $trunk;
		$data['trunkid'] = $trunkid;
		$data['skills'] = $skill_model->getOutSkillOptions();
		//var_dump($data['skills']);
		$data['request'] = $this->getRequest();
		$data['trunk_type_options'] = array('SIP'=>'SIP', 'SS7'=>'SS7');
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($trunkid) ? 'Add New Trunk' : 'Update Trunk : ' . $trunk->trunk_name;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'setttrunk_';
		$this->getTemplate()->display('settings_trunk_form', $data);
	}
	
	function getInitialTrunk($trunkid, $setting_model)
	{
		$trunk = null;

		if (empty($trunkid)) {
			$trunk->trunkid = "";
			$trunk->label = "";
			$trunk->prefix_len = "0";
			$trunk->dial_in_prefix = "";
			$trunk->dial_out_prefix = '';
			$trunk->ip = '';
			$trunk->ch_capacity = "0";
			$trunk->priority = '';
			$trunk->prefix_len = '0';			
			$trunk->rewrite_cli = '';
			$trunk->min_number_len = '';
			$trunk->max_number_len = '';
			$trunk->do_register = '0';
			//$trunk->numbers = "";
		} else {
			$trunk = $setting_model->getTrunkById($trunkid);
		}
		return $trunk;
	}

	function getSubmittedTrunkCLI()
	{
		$posts = $this->getRequest()->getPost();
		$trunk = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$trunk->$key = trim($val);
			}
		}
		return $trunk;
	}

	function getSubmittedTrunk($trunkid)
	{
		$posts = $this->getRequest()->getPost();
		$trunk = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$trunk->$key = trim($val);
			}
		}
		//$trunk->numbers = isset($trunk->numbers) ? rtrim($trunk->numbers, ',') : '';
		//$trunk->numbers_old = isset($trunk->numbers_old) ? rtrim($trunk->numbers_old, ',') : '';
		if (!empty($trunkid)) $trunk->trunkid = $trunkid;
		//var_dump($agent);
		return $trunk;
	}

	function getCLIValidationMsg($trunkid, $trunk_prefix, $trunkCLI, $setting_model)
	{
		$err = '';

		$new_dial_prefix = $trunk_prefix . $trunkCLI->dial_prefix;
		
		$tprefix_len = strlen($trunk_prefix);

		if (strlen($trunkCLI->cli) < 4) $err = 'Minimum length of CLI should be 4';		
		if (strlen($trunkCLI->skill) <= 0) $err = 'Select skill';
		if (empty($new_dial_prefix) && !empty($trunk_prefix)) $err = 'Provide dial out prefix';
		if (empty($trunkCLI->cli)) $err = 'Provide CLI';
		else if (preg_match('/^[0-9]+$/', $trunkCLI->cli) <= 0) $err = 'Invalid CLI provided';
		if (empty($trunkid)) $err = 'Invalid trunk';

		if ($trunkCLI->cli == $trunkCLI->old_cli && $new_dial_prefix == $trunkCLI->old_dial_prefix && $trunkCLI->skill == $trunkCLI->old_skill) {
			$err = 'No change found';
		}
		
		if (empty($err)) {
			if (substr($new_dial_prefix, 0, $tprefix_len) != $trunk_prefix) $err = 'Invalid dial out prefix';
		}
		
		/*
		if (empty($err) && $trunkCLI->cli != $trunkCLI->old_cli) {
			$isProvisioned = $setting_model->isCLIExistInList($trunkid, $trunkCLI->cli);
			if (!$isProvisioned) $err = 'CLI not provisioned yet';
		}
		*/
		
		if (empty($err) && $new_dial_prefix != $trunkCLI->old_dial_prefix || $trunkCLI->skill != $trunkCLI->old_skill) {
			$node = $setting_model->getTrunkCLINode($trunkid, $trunkCLI->skill, $new_dial_prefix);
			if (!empty($node)) $err = 'Skill & dial out prefix already exist';
		}
		
		return $err;
	}
	
	function getValidationMsg($trunk, $trunkid='')
	{
		$err = '';
		$prefixLength = 0;

		//if (empty($trunk->trunk_name)) $err = "Provide Trunk Name";
		/*
		if (strlen($trunk->numbers) > 0) {
			$num_matches = preg_match('/^[0-9,\s]+$/', $trunk->numbers);
			if ($num_matches <= 0) $err = 'Invalid Numbers Provided';
		}
		*/
		if (strlen($trunk->label) > 0) {
			$num_matches = preg_match('/^[0-9a-zA-Z_\s]+$/', $trunk->label);
			if ($num_matches <= 0) $err = 'Invalid Label Provided';
		}
		if (strlen($trunk->ch_capacity) > 0) {
			$num_matches = preg_match('/^[0-9]{1,3}$/', $trunk->ch_capacity);
			if ($num_matches <= 0) $err = 'Invalid Channel Capacity Provided';
		}
		
		if (strlen($trunk->priority) > 0) {
			$num_matches = preg_match('/^[0-9]{1,1}$/', $trunk->priority);
			if ($num_matches <= 0) $err = 'Invalid Priority Provided';
		}
		if (strlen($trunk->prefix_len) > 0) {
			$prefixLength = $trunk->prefix_len;
			$num_matches = preg_match('/^[0-9]{1,1}$/', $trunk->prefix_len);
			if ($num_matches <= 0) $err = 'Invalid Prefix Length Provided';
		}
		
		if (strlen($trunk->dial_in_prefix) > 0) {
			$num_matches = preg_match('/^[0-9\*#]{1,8}$/', $trunk->dial_in_prefix);
			if ($num_matches <= 0) {
				$err = 'Invalid Dial in Prefix Provided';
			}elseif ($prefixLength > 0 && strlen($trunk->dial_in_prefix) > $prefixLength) {
				$err = 'Dial in Prefix length must be equal or smaller than Prefix Length';
			}
		}
		if (strlen($trunk->dial_out_prefix) > 0) {
			$num_matches = preg_match('/^[0-9\*#]{1,8}$/', $trunk->dial_out_prefix);
			if ($num_matches <= 0) $err = 'Invalid Dial out Prefix Provided';
		}
		//if (empty($user->role)) $err = "Provide User Role";
		return $err;
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

}
