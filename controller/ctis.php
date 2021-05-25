<?php

class Ctis extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/* include('model/MCti.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		$cti_model = new MCti();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();

		$data['ctis'] = $cti_model->getCtis();
		$data['request'] = $this->getRequest();
		$data['cti_model'] = $cti_model;
		$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		$data['pageTitle'] = 'CTI List';
		$this->getTemplate()->display('ctis', $data); */
		
		$data['pageTitle'] = 'CTI List';
		$data['dataUrl'] = $this->url('task=get-home-data&act=ctis');
		$this->getTemplate()->display('ctis', $data);
	}

	function actionPriority()
	{
		$ctiid = isset($_REQUEST['ctiid']) ? trim($_REQUEST['ctiid']) : '';
		$priority = isset($_REQUEST['priority']) ? trim($_REQUEST['priority']) : '';
		
		if ($priority == 'Y' || $priority == 'N') {
			include('model/MCti.php');
			$cti_model = new MCti();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			if ($cti_model->updateCtiPriorityStatus($ctiid, $priority)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update CTI', 'isError'=>false, 'msg'=>'Priority Option Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update CTI', 'isError'=>true, 'msg'=>'Failed to Update Priority Option', 'redirectUri'=>$url));
			}
		}
	}

	function actionUpdate()
	{
		$ctiid = isset($_REQUEST['ctiid']) ? trim($_REQUEST['ctiid']) : '';
		$this->saveCTI($ctiid);
	}

	function saveCTI($ctiid='')
	{
		include('model/MCti.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		AddModel("MLanguage");
		$cti_model = new MCti();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$is_node_valid = true;
		$data['action_type_options'] = array('IV'=>'IVR', 'SQ'=>'Skill', 'XF'=>'External Transfer', 'VM'=>'Voice Mail');
		$data['cti_type_options'] = array('SS7'=>'SS7','SIP'=>'SIP');
		$data['status_options'] = array('Y'=>'Enable', 'N'=>'Disable');
		$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		$data['language_option'] = array_merge(array(""=>"Select"),MLanguage::getActiveLanguageListArray());

		if ($request->isPost()) {
			$cti = $this->getSubmittedCti($ctiid);
			$errMsg = $this->getValidationMsg($cti, $ctiid);
			// GPrint($cti);
			if(!empty($cti->param)){
				$is_node_exists = $ivr_model->isIVRAndBranchExist($cti->ivr_id, $cti->param);
				if(!$is_node_exists && empty($errMsg)){
					$errMsg = 'Your node is not valid!';
					$is_node_valid = false;
				}
			}
			// GPrint($errMsg);
			
			if (empty($errMsg)) {
				$oldcti = $this->getInitialCti($ctiid, $cti_model);
				// GPrint($oldcti);
				// die();				
				if (!empty($oldcti)) {
					$is_success = false;
					$value_options = array(
						'action_type' => $data['action_type_options'],
						'skill_id' => $data['skill_options'],
						'ivr_id' => $data['ivr_options'],
						'did' => '',
						'cti_type' => $data['cti_type_options'],
						'active' => $data['status_options']
					);
					$cti->param = str_replace($cti->ivr_id, '', $cti->param);
					if ($cti_model->updateCti($oldcti, $cti, $value_options)) {
						$errMsg = 'CTI updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
					if ($is_success) {
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\"	CONTENT=\"2;URL=$url\">";
					}
				} else {
					$errMsg = 'Invalid CTI !!';
				}
			}
		} else {
			$cti = $this->getInitialCti($ctiid, $cti_model);
			if (empty($cti)) {
				exit;
			}
		}
		
		$data['cti'] = $cti;
		$data['ctiid'] = $ctiid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update CTI : ' . $cti->cti_name;
		$data['smi_selection'] = 'ctis_';
		$data['is_node_valid'] = $is_node_valid;
		$this->getTemplate()->display('cti_form', $data);
	}

	function getInitialCti($ctiid, $cti_model)
	{
		$cti = $cti_model->getCtiById($ctiid);
		if (empty($cti)) exit;
		return $cti;
	}

	function getSubmittedCti($ctiid)
	{
		$posts = $this->getRequest()->getPost();
		$cti = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$cti->$key = trim($val);
			}
		}
		$did = [];
		$label = [];
		if(!empty($posts['did_option'])){
			foreach ($posts['did_option'] as $key => $item) {
				if(!empty($item['did'])){
					$did[] = $item['did'];
					$label[] = $item['label'];
				}
			}
		}
		// $cti->did = isset($cti->did) ? rtrim($cti->did, ',') : '';
		$cti->did = !empty($did) ? implode(',', $did) : '';
		$cti->label = !empty($label) ? implode(',', $label) : '';
		if (!empty($ctiid)) $cti->cti_id = $ctiid;
		// GPrint($cti);
		// die();

		return $cti;
	}

	function getValidationMsg($cti, $ctiid='')
	{
		$err = '';

		//if (!preg_match("/^[0-9]{1,2}$/", $cti->cli_length)) return "Provide Valid CLI Length";
		//else if (strlen($cti->cli_length) == 0) $err = "Provide CTI Length";
		
		//if (!preg_match("/^[0-9]?$/", $cti->cli_padding_prefix)) return "Provide Valid CLI Padding Prefix";
		
		if (empty($cti->cti_name)) $err = "Provide CTI Name";
		if (strlen($cti->did) > 0) {
			//$num_matches = preg_match('/^[0-9,\*()-\s]+$/', $cti->did);
			$num_matches = preg_match("/^[0-9 ()-]+(,[0-9 ()-]+)*$/", $cti->did);
			//var_dump($num_matches);
			//echo "'$cti->did'";
			if ($num_matches <= 0) $err = 'Invalid DID Provided';
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
