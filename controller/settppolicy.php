<?php

class Settppolicy extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MIvr.php');

		$setting_model = new MSetting();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();

		$skill_options = $skill_model->getSkillOptions();
		$ivr_options = $ivr_model->getIvrOptions();
		$data['priority_policy'] = $setting_model->getPrioritySettings('', $skill_options, $ivr_options);
		$data['pageTitle'] = 'Overrule CTI Settings by Priority';
		$data['request'] = $this->getRequest();
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_priority_policy', $data);
	}

	function actionUpdate()
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MIvr.php');

		$setting_model = new MSetting();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$data['pageTitle'] = 'Overrule CTI Settings by Priority';

		$plevel = isset($_REQUEST['plevel']) ? trim($_REQUEST['plevel']) : '';

		$skill_options = $skill_model->getSkillOptions();
		$ivr_options = $ivr_model->getIvrOptions();

		$request = $this->getRequest();
		$errMsg = '';
		if ($request->isPost()) {

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			$cti_action = isset($_POST['cti_action']) ? $_POST['cti_action'] : '';
//var_dump($plevel);exit;
			if (strlen($cti_action)==2 && ($plevel=='L' || $plevel=='H')) {

				if ($cti_action=='SQ') {
					$param = isset($_POST['service_name']) ? $_POST['service_name'] : '';
				} else if($cti_action=='IV') {
					$param = isset($_POST['ivr_name']) ? $_POST['ivr_name'] : '';
				} else {
					$param = '';
				}
				
				if (($cti_action=='SQ'||$cti_action=='IV') && empty($param)) {
					$this->getTemplate()->display('msg', array('pageTitle'=>$data['pageTitle'], 'isError'=>true, 'msg'=>'Failed to Update Setting', 'redirectUri'=>$url));
				} else {
					
					$status = $setting_model->updatePrioritySetting($plevel, $cti_action, $param);
					
					if($status) {

						
						/*
							Logging...
						*/
						$log_text = '';
						
						$old_cti_action = isset($_POST['old_cti_action']) ? $_POST['old_cti_action'] : '';
						$old_value = isset($_POST['old_value']) ? $_POST['old_value'] : '';
						
						if ($plevel == 'L') $identity = "Priority Low";
						else $identity = "Priority High";
						
						if ($old_cti_action!=$cti_action) {
							$ct_act_opts = array('SQ'=>'Skill', 'IV'=>'IVR', 'BL'=>'Call Block');
							$old_action_value = isset($ct_act_opts[$old_cti_action]) ? $ct_act_opts[$old_cti_action] : $old_cti_action;
							$new_action_value = isset($ct_act_opts[$cti_action]) ? $ct_act_opts[$cti_action] : $cti_action;
							
							$log_text = $setting_model->addAuditText($log_text, "CTI Action=$old_action_value to $new_action_value");
						}
						
						if ($old_value != $param) {
							$new_param_value = '';
							$old_param_value = '';
							if($old_cti_action=='SQ') $old_param_value = isset($skill_options[$old_value]) ? $skill_options[$old_value] : $old_value;
							else if($old_cti_action=='IV') $old_param_value = isset($ivr_options[$old_value]) ? $ivr_options[$old_value] : $old_value;
							else $old_param_value = '';

							if($cti_action=='SQ') $new_param_value = isset($skill_options[$param]) ? $skill_options[$param] : $param;
							else if($cti_action=='IV') $new_param_value = isset($ivr_options[$param]) ? $ivr_options[$param] : $param;
							else $new_param_value = '';
							
							if (!empty($new_param_value)) $log_text = $setting_model->addAuditText($log_text, "Skill/IVR=$old_param_value to $new_param_value");
						}
						$setting_model->addToAuditLog('Priority Policy', 'U', $identity, $log_text);
						/*
							-end logging-
						*/
						
						$this->getTemplate()->display('msg', array('pageTitle'=>$data['pageTitle'], 'isError'=>false, 'msg'=>'Priority Setting Updated Successfully', 'redirectUri'=>$url));
					} else {
						$this->getTemplate()->display('msg', array('pageTitle'=>$data['pageTitle'], 'isError'=>true, 'msg'=>'Failed to Update Setting', 'redirectUri'=>$url));
					}
				}
			
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>$data['pageTitle'], 'isError'=>true, 'msg'=>'Failed to Update Setting', 'redirectUri'=>$url));
			}


		}

		if ($plevel=='low') {
			$precord = $setting_model->getPrioritySettingsByLevel('L');
			$cti_action_options = array(''=>'Select','SQ'=>'Skill', 'IV'=>'IVR', 'BL'=>'Call Block');
		} else { //high
			$cti_action_options = array(''=>'Select','SQ'=>'Skill', 'IV'=>'IVR');
			$precord = $setting_model->getPrioritySettingsByLevel('H');
		}

		$data['skill_options'] = $skill_options;
		$data['ivr_options'] = $ivr_options;
		$data['cti_action_options'] = $cti_action_options;
		$data['precord'] = $precord;
		//var_dump($precord);
		$data['request'] = $request;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settppolicy_';
		$this->getTemplate()->display('settings_ppolicy_form', $data);
	}

/*
	function pprefix()
	{
		include('model/MSetting.php');
		include('lib/Pagination.php');
		$setting_model = new MSetting();
		$pagination = new Pagination();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=pprefix");
		$pagination->num_records = $setting_model->numAgents($utype);
		$data['agents'] = $pagination->num_records > 0 ? 
			$agent_model->getAgents($utype, '', $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['agents']) ? count($data['agents']) : 0;
		$data['pagination'] = $pagination;

		$data['agent_model'] = $agent_model;
		$data['request'] = $this->getRequest();
		$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
		$data['pageTitle'] = $utype == 'S' ? 'Supervisor List' : 'Agent List';
		$data['usertype'] = $utype;
		$this->getTemplate()->display('agents', $data);
	}

*/





	function actionActivate()
	{
		$seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
		$status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		
		if ($status == 'Y' || $status == 'N') {
			include('model/MSetting.php');
			$setting_model = new MSetting();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&page=$pageNum");
			if ($setting_model->updateSeatStatus($seatid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>true, 'msg'=>'Failed to Update Status', 'redirectUri'=>$url));
			}
		}
	}
	

	function saveSeat($seatid='')
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$request = $this->getRequest();
		$errMsg = '';
		if ($request->isPost()) {

			$seat = $this->getSubmittedSeat($seatid);
			$errMsg = $this->getValidationMsg($seat, $seatid, $setting_model);

			if (empty($errMsg)) {
				$oldseat = $this->getInitialSeat($seatid, $setting_model);
				if (!empty($oldseat)) {
					$is_success = false;

					if ($setting_model->updateSeat($oldseat, $seat)) {
						$errMsg = 'Seat updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to update Seat !!';
					}
					
					if ($is_success) {
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'Invalid Seat !!';
				}
			}

		} else {
			$seat = $this->getInitialSeat($seatid, $setting_model);
		}

		$data['seatid'] = $seatid;
		$data['seat'] = $seat;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['pageTitle'] = 'Update Seat : ' . $seat->label;
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_seat_form', $data);
	}

	function getInitialSeat($seatid, $setting_model)
	{
		$seat = null;

		$seat = $setting_model->getSeatById($seatid);
		if (empty($seat)) {
			exit;
		}
		return $seat;
	}

	function getSubmittedSeat($seatid)
	{
		$posts = $this->getRequest()->getPost();
		$seat = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$seat->$key = trim($val);
			}
		}
		$seat->seat_id = $seatid;

		return $seat;
	}

	function getValidationMsg($seat, $seatid='', $setting_model)
	{
		$err = '';
		
		if (empty($seat->label)) $err = 'Label is Required';
		if( !empty($seat->ip) )	{
			$is_valid = $this->isValidateIP($seat->ip);
			if (!$is_valid)	$err = 'Invalid IP';
		}
		if (empty($err)) {
			if (empty($seat->ip) && $seat->active == 'Y') $err = 'To Activate Seat Please Provide IP';
		}
		if (empty($err) && !empty($seat->ip)) {
			$seat_by_ip = $setting_model->getSeatByIP($seat->ip);
			if (!empty($seat_by_ip)) {
				if ($seat_by_ip->seat_id != $seatid) $err = 'IP '.$seat->ip.' Already Exists';
			}
		}
		return $err;
	}

	function isValidateIP($ip)
	{
	   $return = true;
	   $tmp = explode(".", $ip);
	   if(count($tmp) < 4){
		  $return = false;
	   } else {
		  foreach($tmp AS $sub){
			 if($return != false){
				if(!eregi("^([0-9])", $sub)){
				   $return = false;
				} else {
				   $return = true;
				}
			 }
		  }
	   }
	   return $return;
	}

}
