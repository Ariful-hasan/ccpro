<?php

class MSetting extends Model
{
	var $codeGenerationCounter=0;
	var $codeGenerationFailedCounter=0;
	function __construct() {
		parent::__construct();
	}

	function sip_notify($seat_id, $msg)
	{
	        /*
	        $dbsuffix = UserAuth::getDBSuffix();
	        $sql = "INSERT INTO cc_master.sync_web_uploads SET db_suffix='$dbsuffix', update_type='SIP_NOTIFY', update_id='$seat_id', upload_path='$msg',update_time=NOW()";
	        return $this->getDB()->query($sql);
	        */
	        include_once('lib/DBNotify.php');
                return DBNotify::NotifySIP(UserAuth::getDBSuffix(), $seat_id, $msg, $this->getDB());
	}
	
	function getValidPage($user, $pass)
	{
		$sql = "SELECT page FROM page_access WHERE page='$user' AND password='$pass'";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {
			return $result[0];
		}

		return null;
	}
	
	function getPageAccessByPage($page)
	{
		$sql = "SELECT page FROM page_access WHERE page='$page'";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {
			return $result[0];
		}

		return null;
	}
	
	function resetPageAccessPassword($pageid, $pass)
	{
		$pass = addslashes($pass);
		$sql = "UPDATE page_access SET password='$pass' WHERE page='$pageid'";
		return $this->getDB()->query($sql);
	}

        function updateActiveServer($opt)
        {
                if (in_array($opt, array('P', 'D'))) {
                        $sql = "update settings set active_sip_srv='$opt'";
                        return $this->getDB()->query($sql);
                }
                return false;
        }
        	
	function getCCSettings()
	{
	        $sql = "SELECT * FROM settings LIMIT 1";
	        $result = $this->getDB()->query($sql);
	        return is_array($result) ? $result[0] : null;
	}
	
	function getSetting($field='')
	{
	        if ($field == 'sl_method') {
	                $obj = new stdClass();
	                include(UserAuth::getConfExtraFile());
	                $obj->value = $extra->sl_method;
	                return $obj;
	        }
	        
		$cond = '';
		$sql = "SELECT item, value FROM settings ";
		if (!empty($field)) $cond = "item='$field'";
		if (!empty($cond)) $sql .= "WHERE $cond";

		$result = $this->getDB()->query($sql);
		if (!empty($field) && is_array($result)) return $result[0];
		return $result;
	}

	function setSetting($field, $value)
	{
		if (empty($field) || empty($value)) return false;

		$sql = "UPDATE settings SET value='$value' WHERE item='$field'";
		return $this->getDB()->query($sql);
	}
	
	function setReportDay($field, $value)
	{
		if (empty($field)) return false;
		if (empty($value)) $value = 0;
	
		$sql = "INSERT INTO settings (item, value) VALUES('$field', '$value') ";
		$sql .= " ON DUPLICATE KEY UPDATE value = VALUES(value)";
		//$sql = "UPDATE settings SET value='$value' WHERE item='$field'";
		return $this->getDB()->query($sql);
	}
	
	function getPrioritySettings($level, $skill_options, $ivr_options)
	{
		$sql = "SELECT * FROM priority_settings";
		$result = $this->getDB()->query($sql);
		$priority = null;
		if (is_array($result)) {
			foreach ($result as $precord) {
				if ($precord->priority_level == 'H' || $precord->priority_level == 'L') {
					$obj_var = $precord->priority_level == 'L' ? 'low' : 'high';
					$priority->$obj_var->cti_action = $precord->cti_action;
					if ($precord->cti_action == 'SQ')
						$priority->$obj_var->value = isset($skill_options[$precord->param]) ? $skill_options[$precord->param] : '';
					elseif ($precord->cti_action == 'IV')
						$priority->$obj_var->value = isset($ivr_options[$precord->param]) ? $ivr_options[$precord->param] : '';
					else
						$priority->$obj_var->value = '-';
				}
			}
		} else {
			$priority->high->value = '-';
			$priority->low->value = '-';
			$priority->high->cti_action = '';
			$priority->low->cti_action = '';
		}
		
		return $priority;
	}

	function getPrioritySettingsByLevel($level)
	{
		$sql = "SELECT * FROM priority_settings WHERE priority_level='$level'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function updatePrioritySetting($plevel='', $cti_action='', $param='')
	{
		if (empty($plevel)) return false;
		if (empty($cti_action)) return false;

		$sql = "UPDATE priority_settings SET cti_action='$cti_action', param='$param' WHERE priority_level='$plevel'";
		return $this->getDB()->query($sql);
	}
	
	function getPageAccess()
	{
		$sql = "SELECT page FROM page_access ORDER BY page";
		return $this->getDB()->query($sql);
	}
	
	function getTrunks()
	{
		$sql = "SELECT * FROM sip_trunk ORDER BY label";

		return $this->getDB()->query($sql);
	}
	
	function getMacros()
	{
	        $sql = "SELECT * FROM macro ORDER BY code";
                return $this->getDB()->query($sql);
	}
	
	function deleteTrunk($trunkid)
	{
		$trunk = $this->getTrunkById($trunkid);
		if (!empty($trunk)) {
			$sql = "DELETE FROM sip_trunk WHERE trunkid='$trunkid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM trunk_cli WHERE trunkid='$trunkid'";
				$this->getDB()->query($sql);
				$this->addToAuditLog('Trunk', 'D', "Trunk=".$trunk->trunk_name, '');
				return true;
			}
		}
		return false;
	}
	
	function deleteMacro($code)
	{
		$macro = $this->getMacroByCode($code);
		if (!empty($macro)) {
			$sql = "DELETE FROM macro WHERE code='$code' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM macro_settings WHERE code='$code'";
				$this->getDB()->query($sql);
				$this->addToAuditLog('Macro', 'D', "Macro=".$macro->code . ' ' . $macro->title, '');
				return true;
			}
		}
		return false;
	}
	
	function deleteMacroJob($code, $cti_id)
	{
	        if (strlen($code) != 3 || !ctype_digit($code) || strlen($cti_id) != 2) return false;
	        $sql = "DELETE FROM macro_settings WHERE code='$code' AND cti_id='$cti_id'";
	        $this->getDB()->query($sql);
                $this->addToAuditLog('Macro CTI', 'D', "Macro CTI=".$code . ' ' . $cti_id, '');
	        return true;
	}
	
	function deleteTrunkCLI($trunkid, $skillid, $dialprefix)
	{
		$sql = "DELETE FROM trunk_cli WHERE trunkid='$trunkid' AND skill_id='$skillid' AND dial_prefix='$dialprefix'";
		return $this->getDB()->query($sql);
	}
	
	function getTrunkCLINode($trunkid, $skillid, $dial_prefix)
	{
		$sql = "SELECT * FROM trunk_cli WHERE trunkid='$trunkid' AND skill_id='$skillid' AND dial_prefix='$dial_prefix'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getMacroJob($macro_code, $cti)
	{
	        $sql = "SELECT * FROM macro_settings WHERE code='$macro_code' AND cti_id='$cti'";
                $result = $this->getDB()->query($sql);
                return is_array($result) ? $result[0] : null;
	}
	
	function isCLIExistInList($trunkid, $cli)
	{
		$len = strlen($cli);
		if ($len < 4) return false;
		
		$sql = "SELECT did FROM did_list WHERE trunkid='$trunkid' AND did_match=RIGHT($cli ,match_len)";
		//$sql = "SELECT trunkid FROM did_list WHERE trunkid='$trunkid' AND RIGHT(did, $len) = '$cli'";
		$result = $this->getDB()->query($sql);
		
		return empty($result) ? false : true;
	}

	function addMacroJob($macro_code, $node, $macroname)
	{
		if (empty($macro_code)) return false;
		$sql = "INSERT INTO macro_settings SET ".
			"code='$macro_code', ".
			"cti_id='$node->cti_id', ".
			"action_type='$node->action_type', ".
			"skill_id='$node->skill_id', ".
			"ivr_id='$node->ivr_id', ".
			"param='$node->param'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Macro Job', 'A', "Macro=".$macroname . " ($macro_code)", $ltxt);

			return true;
		}
		
		return false;
	}
	
	function updateMacroJob($macro_code, $clinode, $macroname)
	{
		if (empty($macro_code)) return false;
		
		$is_update = false;
		
		$field_array = array(
                        'skill_id' => 'skill_id',
                        'ivr_id' => 'ivr_id',
                        'action_type' => 'action_type',
			'param' => 'param'
                );

		$oldnode = new stdClass();
		$oldnode->skill_id = $clinode->old_skill;
		$oldnode->ivr_id = $clinode->old_ivr_id;
		$oldnode->action_type = $clinode->old_action_type;
		$oldnode->param = $clinode->old_param;
		
		$changed_fields = $this->getSqlOfChangedFields($oldnode, $clinode, $field_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE  SET $changed_fields WHERE code='$macro_code' AND cti_id='$clinode->cti'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
				'skill_id' => 'Skill',
				'ivr_id' => 'Ivr',
				'param' => 'Param',
				'action_type' => 'Action'
			);
			
			$audit_text = $this->getAuditText($oldnode, $clinode, $field_array, $field_names);
			$this->addToAuditLog('Macro Job', 'U', "Macro=".$macroname . " ($macro_code)", $audit_text);
		}
		
		return $is_update;
	}
	
	function addCLINode($trunkid, $node, $trunkname, $skillname)
	{
		if (empty($trunkid)) return false;
		$node->dial_prefix = trim($node->dial_prefix);
		$prefix_length = strlen($node->dial_prefix);
		$sql = "INSERT INTO trunk_cli SET ".
			"trunkid='$trunkid', ".
			"skill_id='$node->skill', ".
			"cli='$node->cli', ".
			"dial_prefix='$node->dial_prefix', ".
			"prefix_len='$prefix_length'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$ltxt = "skill=".$skillname.";Number=".$node->cli.";Dial prefix=".$node->dial_prefix;
			$this->addToAuditLog('Trunk CLI', 'A', "Trunk=".$trunkname, $ltxt);

			return true;
		}
		
		return false;
	}

	function updateCLINode($trunkid, $clinode, $trunkname, $skillname)
	{
		if (empty($trunkid)) return false;
		
		$is_update = false;

		$field_array = array(
			'skill_id' => 'skill',
			'cli' => 'cli',
			'dial_prefix' => 'dial_prefix'
		);
		
		$oldnode = new stdClass();
		$oldnode->skill_id = $clinode->old_skill;
		$oldnode->cli = $clinode->old_cli;
		$oldnode->dial_prefix = $clinode->old_dial_prefix;
		
		$changed_fields = $this->getSqlOfChangedFields($oldnode, $clinode, $field_array);

		if (!empty($changed_fields)) {
			$clinode->dial_prefix = trim($clinode->dial_prefix);
			$prefix_length = strlen($clinode->dial_prefix);
			$changed_fields .= ", prefix_len='$prefix_length'";
			$sql = "UPDATE trunk_cli SET $changed_fields WHERE trunkid='$trunkid' AND skill_id='$clinode->old_skill' AND dial_prefix='$clinode->old_dial_prefix'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
				'skill' => 'SKill',
				'cli' => 'Number',
				'dial_prefix' => 'Dial prefix'
			);
			
			$audit_text = $this->getAuditText($oldnode, $clinode, $field_array, $field_names);
			$this->addToAuditLog('Trunk CLI', 'U', "Name=".$trunkname, $audit_text);
		}
		
		return $is_update;
	}
	
	function getTrunkOptions()
	{
		$trunks = array();
		$sql = "SELECT trunkid, label FROM sip_trunk ORDER BY label";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$trunks[$row->trunkid] = $row->label;
			}
		}
		return $trunks;
	}

	function getTrunkById($id='')
	{
		if (empty($id)) return null;
		$sql = "SELECT * FROM sip_trunk WHERE trunkid='$id'";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			$trunk = $result[0];
			//$numbers = '';
			return $trunk;
		}
		
		return null;
	}
	
	function getMacroByCode($code='')
	{
	        if (strlen($code) != 3 || !ctype_digit($code)) return null;
	        $sql = "SELECT * FROM macro WHERE code='$code'";
	        $result = $this->getDB()->query($sql);
	        if (is_array($result)) {
	                return $result[0];
	        }
	        return null;
	}
	
	function getTrunkCLIs($id='')
	{
		if (empty($id)) return null;
		$numbers = array();
		$sql = "SELECT cli, skill_id, dial_prefix FROM trunk_cli WHERE trunkid='$id' ORDER BY cli";
		$result1 = $this->getDB()->query($sql);
		if (is_array($result1)) {
			foreach ($result1 as $trnk) {
				$numbers[] = $trnk;
			}
		}
		return $numbers;
	}
	
	function getMacroSettings($code='')
	{
	        if (strlen($code) != 3 || !ctype_digit($code)) return null;
	        $numbers = array();
                $sql = "SELECT code, cti_id, action_type, skill_id, ivr_id, param FROM macro_settings s WHERE code='$code' ORDER BY cti_id";
                $result1 = $this->getDB()->query($sql);
                if (is_array($result1)) {
                        foreach ($result1 as $trnk) {
                                $numbers[] = $trnk;
                        }
                }
                return $numbers;
	}
	
	function getEmptyTrunkId()
	{
		$id = '';
		$ids = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$sql = "SELECT trunkid FROM sip_trunk";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $trunk) {
				if (in_array($trunk->trunkid, $ids)) {
					$key = array_search($trunk->trunkid, $ids);
					unset($ids[$key]);
				}
			}
			reset($ids);
		}
		if (count($ids)>0) $id = current($ids);

		//var_dump($ids); echo $id; exit;
		return $id;
	}
	
	function addTrunk($trunkid, $trunk)
	{
		if (empty($trunkid)) return false;

		$sql = "INSERT INTO sip_trunk SET trunkid='$trunkid', label='$trunk->label', prefix_len='$trunk->prefix_len', priority='$trunk->priority', ".
			"ip='$trunk->ip', ch_capacity='$trunk->ch_capacity', dial_out_prefix='$trunk->dial_out_prefix', dial_in_prefix='$trunk->dial_in_prefix', ".
			"rewrite_cli='$trunk->rewrite_cli', min_number_len='$trunk->min_number_len', max_number_len='$trunk->max_number_len'";
		if ($this->getDB()->query($sql)) {
			/*
			$clis = explode(",", $trunk->numbers);
			if (is_array($clis)) {
				foreach ($clis as $cli) {
					$cli = trim($cli);
					if (!empty($cli)) {
						$sql = "INSERT INTO trunk_cli SET trunkid='$trunkid', cli='$cli'";
						$this->getDB()->query($sql);
					}
				}
			}
			*/
			return true;
		}
		return false;
	}
	
	function addMacro($macro)
	{
		if (strlen($macro->code) != 3 || !ctype_digit($macro->code)) return false;

		$sql = "INSERT INTO macro SET code='$macro->code', title='$macro->title'";
		if ($this->getDB()->query($sql)) {
			return true;
		}
		return false;
	}
	
	function updateTrunk($oldtrunk, $trunk)
	{
		if (empty($oldtrunk->trunkid)) return false;
		$is_update = false;

		/*
			'trunk_type' => 'trunk_type',
			'trunk_name' => 'trunk_name',
		*/
		
		$field_array = array(
			'label' => 'label',
			'prefix_len' => 'prefix_len',
			'ch_capacity' => 'ch_capacity',
			'dial_in_prefix' => 'dial_in_prefix',
			'dial_out_prefix' => 'dial_out_prefix',
			'priority' => 'priority',
			'rewrite_cli' => 'rewrite_cli',
			'min_number_len' => 'min_number_len',
			'max_number_len' => 'max_number_len',
			'do_register' => 'do_register'
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldtrunk, $trunk, $field_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE sip_trunk SET $changed_fields WHERE trunkid='$oldtrunk->trunkid'";
			$is_update = $this->getDB()->query($sql);
		}
		
		/*
		if ($oldtrunk->numbers != $trunk->numbers) {
			$sql = "DELETE FROM trunk_cli WHERE trunkid='$oldtrunk->trunkid'";
			$this->getDB()->query($sql);

			$clis = explode(",", $trunk->numbers);
			if (is_array($clis)) {
				foreach ($clis as $cli) {
					$cli = trim($cli);
					if (!empty($cli)) {
						$sql = "INSERT INTO trunk_cli SET trunkid='$oldtrunk->trunkid', cli='$cli'";
						$this->getDB()->query($sql);
					}
				}
			}

			$is_update = true;
		}
		*/
		
		if ($is_update) {
			if ($oldtrunk->dial_prefix != $trunk->dial_prefix) {
				$old_prefix_len = strlen($oldtrunk->dial_prefix) + 1;
				$sql = "UPDATE trunk_cli SET dial_prefix=CONCAT('$trunk->dial_prefix', SUBSTRING(dial_prefix, $old_prefix_len)), ".
					"prefix_len=LENGTH(dial_prefix) WHERE trunkid='$oldtrunk->trunkid'";
				$this->getDB()->query($sql);
			}
			
			$field_names = array(
				'label' => 'label',
				'prefix_len' => 'prefix_len',
				'ch_capacity' => 'ch_capacity',
				'dial_in_prefix' => 'dial_in_prefix',
				'dial_out_prefix' => 'dial_out_prefix',
				'priority' => 'priority',
				'rewrite_cli' => 'rewrite_cli',
				'min_number_len' => 'min_number_len',
				'max_number_len' => 'max_number_len',
				'do_register' => 'do_register'
			);

			$audit_text = $this->getAuditText($oldtrunk, $trunk, $field_array, $field_names);
			$this->addToAuditLog('Trunk', 'U', "Name=".$oldtrunk->label, $audit_text);
		}
		
		return $is_update;
	}

	function updateTrunkStatus($trunkid, $status='')
	{
		if (empty($trunkid)) return false;
		if ($status=='A' || $status=='I') {
			$sql = "UPDATE sip_trunk SET status='$status' WHERE trunkid='$trunkid'";
			if ($this->getDB()->query($sql)) {
				$trunkinfo = $this->getTrunkById($trunkid);
				$txt = $status=='Y' ? 'Status=Inactive to Active' : 'Status=Active to Inactive';
				$sid = "Name=$trunkinfo->label";
				$this->addToAuditLog('Trunk', 'U', $sid, $txt);
				return true;
			}
		}
		return false;
	}



	/**
		Functions needed for seat settings
	**/
	function numSeats($status='', $did='', $seat_id='', $label='', $not_show_all_seat=true)
	{
		$cond = '';
		$sql = "SELECT COUNT(seat_id) AS numrows FROM seat AS s ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($did)){
                    if (!empty($cond)) $cond .= " AND s.did='$did'";
                    else $cond .= "s.did='$did'";
                }
                if (!empty($seat_id)){
                    if (!empty($cond)) $cond .= " AND s.seat_id='$seat_id'";
                    else $cond .= "s.seat_id='$seat_id'";
                }
                if (!empty($label)){
                        if (!empty($cond)) $cond .= " AND s.label LIKE '$label%'";
                        else $cond .= "s.label LIKE '$label%'";
                }
                if (UserAuth::hasRole('supervisor') && $not_show_all_seat) {
                     $agents = UserAuth::getAllowedAgents('sql');   
                     if (!empty($cond)) $cond .= " AND ";
                     $cond .= "s.agent_id IN ($agents)";
                }
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		//var_dump($_SESSION);
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getSeats($status='', $offset=0, $limit=0, $did='', $seat_id='', $label='', $not_show_all_seat=true)
	{
		$cond = '';
		$sql = "SELECT s.seat_id,s.agent_id,s.label,s.did,s.device_type,s.device_mac,s.device_model,s.device_ip,s.active,a.code,allow_call_without_login
				FROM seat  as s LEFT JOIN seat_activation_code as a ON a.seat_id=s.seat_id ";


		if (!empty($status)) $cond = "s.active='$status'";
		if (!empty($did)){
		    if (!empty($cond)) $cond .= " AND s.did='$did'";
		    else $cond .= "s.did='$did'";
		}
		if (!empty($seat_id)){
		    if (!empty($cond)) $cond .= " AND s.seat_id='$seat_id'";
		    else $cond .= "s.seat_id='$seat_id'";
		}
		if (!empty($label)){
		        if (!empty($cond)) $cond .= " AND s.label LIKE '$label%'";
		        else $cond .= "s.label LIKE '$label%'";
		}
		/*
		if (UserAuth::hasRole('supervisor') && $not_show_all_seat) {
			 $agents = UserAuth::getAllowedAgents('sql');
			 if (!empty($cond)) $cond .= " AND ";
			 $cond .= "s.agent_id IN ($agents)";

		}
		*/
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= " ORDER BY s.seat_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function GetANewActivationCode(){
		$try=1;
		while ($try<=5){
			$code=rand('100000', '999999');
			$sql="SELECT count(*) as numrows from seat_activation_code WHERE code='$code'";
			$result=$this->getDB()->query($sql);	
			
			if ($this->getDB()->getNumRows() == 1) {
				if(empty($result[0]->numrows) ||$result[0]->numrows==0){
					return $code;
				}
			}
		}
		return false;
	}
	function DeleteActivationCodeBySeatId($seat_id){		
		$sql = "DELETE FROM seat_activation_code WHERE seat_id='$seat_id' LIMIT 1";
		if ($this->getDB()->query($sql)) {				
			return true;
		}	
		return false;
	}
	
	function GenerateActivationCode()
	{
		$this->codeGenerationCounter=0;
		$cond = '';
		$sql = "SELECT s.seat_id,s.label, s.device_type,s.device_mac,s.device_model,s.device_ip,s.active,a.code,a.seat_id as code_seat_id
				FROM seat  as s
				LEFT JOIN seat_activation_code as a ON a.seat_id=s.seat_id WHERE s.device_mac='' and  (a.code is null or a.code='')";	
		$result=$this->getDB()->query($sql);
		$reponse=new stdClass();
		$reponse->tried=0;
		$reponse->successfullCounter=0;
		$reponse->faildCounter=0;
		if($result){
			$reponse->tried=count($result);
			foreach ($result as $seat){
				$isSuccess = false;
				$code=$this->GetANewActivationCode();
				if($code){
					if(empty($seat->code_seat_id)){ // addnew
						$sql = "INSERT INTO seat_activation_code SET seat_id='{$seat->seat_id}', code='$code'";
						if ($this->getDB()->query($sql)) {							
							$reponse->successfullCounter++;
							$isSuccess = true;
						}else{
							$reponse->faildCounter++;
						}						
					}else{ // update
						$sql = "UPDATE seat_activation_code SET seat_id='{$seat->seat_id}', code='$code' WHERE seat_id='{$seat->code_seat_id}'";
						if ($this->getDB()->query($sql)) {
							$reponse->successfullCounter++;
							$isSuccess = true;
						}else{
							$reponse->faildCounter++;
						}
					}
					}else{
					$reponse->faildCounter++;
				}
				if ($isSuccess) {
				        $dpass = rand(10000000, 99999999);
				        $sql = "UPDATE seat SET device_pass='$dpass', active='Y' WHERE seat_id='{$seat->seat_id}' LIMIT 1";
				        $this->getDB()->query($sql);
				}
				
			}
			
		}	
		return $reponse;
		
	}

	function getSeatById($seatid)
	{
		$sql = "SELECT * FROM seat WHERE seat_id='$seatid'";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function getSeatByIP($ip)
	{
		if (empty($ip)) return null;
		$sql = "SELECT * FROM seat WHERE ip='$ip'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	
	function updateSeat($oldseat, $seat)
	{
		if (empty($oldseat->seat_id)) return false;
		$is_update = false;
		
		//if (empty($seat->device_ip)) $seat->active = 'N';

		$field_array = array(
			'label' => 'label',
			'did' => 'did',
			'forward_rule' => 'forward_rule',
			'forward_number' => 'forward_number',
			'device_ip' => 'device_ip',
			'device_mac' => 'device_mac', 
			'device_type' => 'device_type',
			'active' => 'active'
				
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldseat, $seat, $field_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE seat SET $changed_fields WHERE seat_id='$oldseat->seat_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
			'label' => 'Label',
			'ip' => 'IP',
			'active' => 'Status',
			'did' => 'DID',
			'forward_number' => 'Forward Number',
			'forward_rule' => 'Forward Rule'
			);
			$field_values = array(
				'active' => array('Y'=>'Active', 'N'=>'Inactive')
			);
			$audit_text = $this->getAuditText($oldseat, $seat, $field_array, $field_names, $field_values);
			$this->addToAuditLog('Seat', 'U', "ID=".$oldseat->seat_id.";Label=$oldseat->label", $audit_text);
		}
		
		return $is_update;
	}

	function updateSeatStatus($seatid, $status='')
	{
		if (empty($seatid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE seat SET active='$status' WHERE seat_id='$seatid'";
			if ($this->getDB()->query($sql)) {
				$seatinfo = $this->getSeatById($seatid);
				$txt = $status=='Y' ? 'Status=Inactive to Active' : 'Status=Active to Inactive';
				$sid = "ID=$seatid";
				$sid .= empty($seatinfo) ? "" : ";Label=$seatinfo->label";
				$this->addToAuditLog('Seat', 'U', $sid, $txt);
				return true;
			}
		}
		return false;
	}
	
	function updateSeatOutgoingControl($seatid, $status='')
	{
	        if (empty($seatid)) return false;
                if ($status=='Y' || $status=='N') {
                        $sql = "UPDATE seat SET allow_call_without_login='$status' WHERE seat_id='$seatid'";
                        if ($this->getDB()->query($sql)) {
                                $seatinfo = $this->getSeatById($seatid);
                                $txt = $status=='Y' ? 'Outgoing Control=Disable to Enable' : 'Outgoing Control=Enable to Disable';
                                $sid = "ID=$seatid";
                                $sid .= empty($seatinfo) ? "" : ";Label=$seatinfo->label";
                                $this->addToAuditLog('Seat', 'U', $sid, $txt);
                                return true;
                        }
                }
                return false;
	}

	/*
		backup
	*/
	function numBackups()
	{
		$sql = "SELECT COUNT(tstamp ) AS numrows FROM backup_log";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getBackups($offset=0, $limit=0)
	{
		$sql = "SELECT tstamp, dev_id, log FROM backup_log ";
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}



	/**
		Functions needed for AUX settings
	**/
	
	function getBusyMessagesForCallControl()
	{
	        $options = array();
                $sql = "SELECT aux_code,message FROM aux_message WHERE active='Y' AND aux_code < 20 ORDER BY aux_code";
                $result = $this->getDB()->query($sql);

                if (is_array($result)) {
                        foreach ($result as $ivr) {
                                $options[] = $ivr;
                        }
                }

                return $options;
	}
	
	function numBusyMessages($status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(aux_code) AS numrows FROM aux_message ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getBusyMessages($status='', $offset=0, $limit=0, $is_array_output=false)
	{
		$cond = '';
		$sql = "SELECT aux_code, aux_type, message, active FROM aux_message ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY aux_code ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		$result = $this->getDB()->query($sql);
		if ($is_array_output) {
			$aux_msgs = array();
			if (is_array($result)) {
				foreach($result as $val) {
					$aux_msgs[$val->aux_code] = $val->message;
				}
			}
			return $aux_msgs;
		}
		return $result;
	}

	function getBusyMessageById($aux_code)
	{
		$sql = "SELECT * FROM aux_message WHERE aux_code='$aux_code'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function updateBusyMessage($oldmsg, $msg)
	{
		if (empty($oldmsg->aux_code)) return false;
		$is_update = false;
		
		if (empty($msg->message)) $msg->active = 'N';
		
		$field_array = array(
			'message' => 'message',
			'aux_type' => 'aux_type',
			'active' => 'active'
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldmsg, $msg, $field_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE aux_message SET $changed_fields WHERE aux_code='$oldmsg->aux_code'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
			'message' => 'Message',
			'aux_type' => 'AUX type',
			'active' => 'Status'
			);
			$field_values = array(
				'aux_type' => array('O'=>'AUX-OUT', 'I'=>'AUX-IN'),
				'active' => array('Y'=>'Active', 'N'=>'Inactive')
			);
			$audit_text = $this->getAuditText($oldmsg, $msg, $field_array, $field_names, $field_values);
			$this->addToAuditLog('Busy Message', 'U', "Code=".$oldmsg->aux_code.";Message=$oldmsg->message", $audit_text);

		}
		
		return $is_update;
	}

	function updateBusyMessageStatus($bid, $status='')
	{
		if (empty($bid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE aux_message SET active='$status' WHERE aux_code='$bid'";
			if ($this->getDB()->query($sql)) {
				$bminfo = $this->getBusyMessageById($bid);
				$txt = $status=='Y' ? 'Status=Inactive to Active' : 'Status=Active to Inactive';
				$sid = "Code=$bid";
				$sid .= empty($bminfo) ? "" : ";Message=$bminfo->message";
				$this->addToAuditLog('Busy Message', 'U', $sid, $txt);
				return true;
			}
		}
		return false;
	}
	static function getInitialSeat($seatid, $setting_model)
	{
	    $seat = null;
	
	    $seat = $setting_model->getSeatById($seatid);
	    if (empty($seat)) {
	        exit;
	    }
	    return $seat;
	}

	function getCobrowseLinks()
	{
		$sql = "SELECT * FROM co_browser_link ";
		$result = $this->getDB()->query($sql);
		$list = [];
		if(!empty($result)){
			foreach($result as $val){
				$list[$val->site_prefix] = $val;
			}
		}
		return $list;
	}
	function saveCobrowseLog($postData){
		$session_id = $postData->getPost('session_id');
		$page_id = $postData->getPost('page_id');
		$initiator = $postData->getPost('initiator');
		$phone_number = $postData->getPost('phone_number');
		$agent_id = $postData->getPost('agent_id');
		$customer_name = $postData->getPost('customer_name');
		$web_chat_callid = $postData->getPost('web_chat_callid');

		$sql = "INSERT INTO co_browse_log SET start_time=NOW(), session_id='$session_id',
		page_id='$page_id' ,initiator='$initiator',phone_number='$phone_number',agent_id='$agent_id',
		customer_name='$customer_name', web_chat_callid = '$web_chat_callid', last_update_time = NOW()
		";
		return $this->getDB()->query($sql);
		
	}

}

?>