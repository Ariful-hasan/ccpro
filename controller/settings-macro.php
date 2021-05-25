<?php

class SettingsMacro extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $data['pageTitle'] = 'Macro Settings';
	    $data['side_menu_index'] = 'settings';
	    $data['topMenuItems'] = array(array('href'=>'task=settings-macro&act=add', 'img'=>'fa fa-user', 'label'=>'Add Macro Action'));
	    $data['dataUrl'] = $this->url('task=get-tools-data&act=sett-macro-init');
	    $this->getTemplate()->display('settings-macro', $data);
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
		$this->saveMacro();
	}

	function actionUpdate()
	{
		$macro_code = isset($_REQUEST['macro_code']) ? trim($_REQUEST['macro_code']) : '';
		if (!empty($macro_code)) $this->saveMacro($macro_code);
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
	
	function actionAddMacroJob()
	{
		$macro_code = isset($_REQUEST['macro_code']) ? trim($_REQUEST['macro_code']) : '';
		$this->saveMacroJob($macro_code);
	}
	
	function actionDeljob()
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
	
	function saveMacroJob($macro_code='', $cti='')
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('model/MCti.php');
		
		$setting_model = new MSetting();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$cti_model = new MCti();
		
		$errMsg = '';
		$errType = 1;
		$macro = $setting_model->getMacroByCode($macro_code);
		
		if (empty($macro)) {
			exit;
		}
		
		$skill_options = $skill_model->getSkillOptions();
		$ivr_options = $ivr_model->getIvrOptions();
		$cti_options = $cti_model->getCtiOptions();
		
		//$skill_options[''] = 'Select';
		//var_dump($skill_options);
		$skill_id = isset($_REQUEST['skill_id']) ? trim($_REQUEST['skill_id']) : '';
		$skillname = isset($skill_options[$skill_id]) ? $skill_options[$skill_id] : '';
		
		$data['pageTitle'] = empty($_REQUEST['cti']) ? 'Add New Action :: ' . $macro->title : 'Update Action :: ' . $macro->title;
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$macroJob = $this->getSubmittedMacroJob();
			$errMsg = $this->getMacroJobValidationMsg($macro_code, $macroJob, $setting_model);

			$data['skill_id'] = $macroJob->skill_id;
			$data['old_skill_id'] = $macroJob->old_skill_id;
			$data['ivr_id'] = $macroJob->ivr_id;
                        $data['old_ivr_id'] = $macroJob->old_ivr_id;
			$data['param'] = $macroJob->param;
                        $data['old_param'] = $macroJob->old_param;
			$data['action_type'] = $macroJob->action_type;
                        $data['old_action_type'] = $macroJob->old_action_type;

			if (empty($errMsg)) {

				$is_success = false;
				
				if (empty($macroJob->cti)) {
					if ($setting_model->addMacroJob($macro_code, $macroJob, $macro->title)) {
						$errMsg = 'Action added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add action !!';
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
			$macroJob = null;
			$data['cti'] = isset($_REQUEST['cti']) ? trim($_REQUEST['cti']) : '';
			if (!empty($data['cti'])) {
				$macroJob = $setting_model->getMacroJob($macro_code, $data['cti']);
			}
			
			if (empty($macroJob) && !empty($data['cti'])) exit;
			
			if (empty($macroJob)) {
				$data['cti_id'] = '';
				$data['skill_id'] = '';
				$data['ivr_id'] = '';
				$data['action_type'] = '';
				$data['param'] = '';
				$data['old_skill_id'] = '';
                                $data['old_ivr_id'] = '';
                                $data['old_action_type'] = '';
                                $data['old_param'] = '';
			} else {
				$data['cti_id'] = $macroJob->cti_id;
                                $data['skill_id'] = $macroJob->skill_id;
                                $data['ivr_id'] = $macroJob->ivr_id;
                                $data['action_type'] = $macroJob->action_type;
                                $data['param'] = $macroJob->param;
                                $data['old_skill_id'] = $macroJob->skill_id;
                                $data['old_ivr_id'] = $macroJob->ivr_id;
                                $data['old_action_type'] = $macroJob->action_type;
                                $data['old_param'] = $macroJob->param;
			}
		}
		
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['skill_options'] = $skill_options;
		$data['ivr_options'] = $ivr_options;
		$data['cti_options'] = $cti_options;
		$data['macro_code'] = $macro_code;
		//if (!isset($data['is_root'])) $data['is_root'] = false;
		$this->getTemplate()->display_popup('settings_macro_job', $data);
	}
	
	function saveMacro($macro_code='')
	{
		if (!empty($macro_code)) {
			if (strlen($macro_code) != 3 || !ctype_digit($macro_code)) {
				exit;
			}
		}
		include('model/MSetting.php');
		//include('model/MSkill.php');
		$setting_model = new MSetting();
		//$skill_model = new MSkill();
		
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$macro = $this->getSubmittedMacro($macro_code);
			
			$errMsg = $this->getValidationMsg($macro, $macro_code);
			if (empty($errMsg)) {
				//$trunk_id = empty($trunkid) ? $setting_model->getEmptyTrunkId() : $trunkid;

				if (!empty($macro->code)) {
					
					$is_success = false;

					if (empty($macro_code)) {
						$oldmacro = $this->getInitialMacro($macro->code, $setting_model);
						if (!empty($oldmacro)) {
							$errMsg = 'Macro code '.$macro->code.' already exist !!';
						} else {
							if ($setting_model->addMacro($macro)) {
								$errMsg = 'Macro added successfully !!';
								$is_success = true;
							} else {
								$errMsg = 'Failed to add macro !!';
							}
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
					$errMsg = 'Provide macro code !!';
				}
			}
			
		} else {
			$macro = $this->getInitialMacro($macro_code, $setting_model);
			if (empty($macro)) {
				exit;
			}
		}
		
		$macro->jobs = empty($macro_code) ? null : $setting_model->getMacroSettings($macro_code);
		$data['dataUrl'] = $this->url('task=get-tools-data&act=sett-macro-job&macro_code='.$macro_code);
		$data['macro'] = $macro;
		$data['macro_code'] = $macro_code;
		//$data['skills'] = $skill_model->getOutSkillOptions();
		//var_dump($data['skills']);
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($macro_code) ? 'Add New Macro' : 'Update Macro : ' . $macro->title;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settings-macro_';
		$this->getTemplate()->display('settings_macro_form', $data);
	}
	
	function getInitialMacro($macro_code, $setting_model)
	{
		$trunk = null;

		if (empty($macro_code)) {
			$trunk->code = "";
			$trunk->title = "";
		} else {
			$trunk = $setting_model->getMacroByCode($macro_code);
		}
		return $trunk;
	}

	function getSubmittedMacroJob()
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

	function getSubmittedMacro($macro_code)
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
		if (!empty($macro_code)) $trunk->macro_code = $macro_code;
		//var_dump($agent);
		return $trunk;
	}

	function getMacroJobValidationMsg($macro_code, $macroJob, $setting_model)
	{
		$err = '';

		
		if (preg_match('/^[0-9]{0,20}$/', $macroJob->param) <= 0) $err = 'Invalid Param provided';
		
		if (empty($macro_code)) $err = 'Invalid macro';

		return $err;
	}
	
	function getValidationMsg($macro, $macro_code='')
	{
		$err = '';
		$prefixLength = 0;

		if (strlen($macro->title) > 0) {
			$num_matches = preg_match('/^[0-9a-zA-Z_\s]+$/', $macro->title);
			if ($num_matches <= 0) $err = 'Invalid Label Provided';
		}
		
		$num_matches = preg_match('/^[0-9]{3,3}$/', $macro->code);
		if ($num_matches <= 0) {
			$err = empty($macro->code) ? 'Please Provide Code' : 'Invalid Code Provided';
		}

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
