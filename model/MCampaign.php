<?php

class MCampaign extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getCampaignSelectOptions($star_represent_blank=false)
	{
	    if($star_represent_blank){
	        $ret = array('*'=>'Select');
	    }else{
	        $ret = array(''=>'Select');
	    }
	    
		$ret['0000'] = 'System';
		$ret['1111'] = 'General';
		$sql = "SELECT campaign_id, title FROM campaign_profile";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $res) {
				$ret[$res->campaign_id] = $res->title;
			}
		}
		return $ret;
	}

	function getLeadSelectOptions($star_represent_blank=false)
	{
	    if($star_represent_blank){
	        $ret = array('*'=>'Select');
	    }else{
	        $ret = array(''=>'Select');
	    }

		$sql = "SELECT lead_id, title FROM lead_profile";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $res) {
				$ret[$res->lead_id] = $res->title;
			}
		}
		return $ret;
	}
	
	function getLeadCount($title='')
	{
		$cond = "";
		$sql = "SELECT COUNT(lead_id) AS total FROM lead_profile ";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE '$title%'");
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->total) ? 0 : $result[0]->total;
		}

		return 0;
	}
	
	function getLeadsList($title='', $offset=0, $rowsPerPage=20)
	{
		$cond = "";
		$sql = "SELECT lead_id, title, reference, country_code, modify_date, number_count FROM lead_profile ";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE '$title%'");
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "ORDER BY lead_id LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function numLeadNumbers($leadid)
	{
		$sql = "SELECT COUNT(lead_id) AS total FROM leads WHERE lead_id='$leadid'";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->total) ? 0 : $result[0]->total;
		}

		return 0;
	}
	
	function getLeadNumbers($leadid, $offset=0, $rowsPerPage=0, $orderBy='dial_number', $orderType='ASC')
	{
		$sql = "SELECT * FROM leads WHERE lead_id='$leadid' ORDER BY $orderBy $orderType";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		return $this->getDB()->query($sql);
	}
	
	function getLeadById($lid)
	{
		$sql = "SELECT * FROM lead_profile WHERE lead_id='$lid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function deleteLeadNumbers($lid)
	{
		if (empty($lid) || !ctype_digit($lid)) return false;
		$sql = "DELETE FROM leads WHERE lead_id='$lid'";
		if ($this->getDB()->query($sql)) {
			$this->updateLeadCount($lid);
			$this->addToAuditLog('Lead Number', 'D', "Lead=$lid", "All Numbers");
			return true;
		}
		return false;
	}
	
	function deleteNumber($lid, $num)
	{
		$sql = "DELETE FROM leads WHERE lead_id='$lid' AND dial_number='$num' LIMIT 1";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$this->updateLeadCount($lid);
			$this->addToAuditLog('Lead Number', 'D', "Lead=$lid", "Number=$num");
			return true;
		}
		return false;
	}
	
	function updateLeadCount($lid)
	{
		$mdate = date("Y-m-d");
		$mdate_time = date("Y-m-d H:i:s");
		$num_count = $this->numLeadNumbers($lid);
		$sql = "UPDATE lead_profile SET modify_date='$mdate', modify_time='$mdate_time', number_count='$num_count' WHERE lead_id='$lid'";
		$this->getDB()->query($sql);
	}

	function addLead($lead, $ltext)
	{
		$lead_id = $this->getMaxLeadID();

		if (empty($lead_id) || !ctype_digit($lead_id)) { $lead_id ='100001'; }
		else if ( $lead_id == '999999') { return false; }
		else { $lead_id++; }

		$mdate = date("Y-m-d");
		$mdate_time = date("Y-m-d H:i:s");
		
		$sql = "INSERT INTO lead_profile SET lead_id='$lead_id', title='$lead->title', reference='$lead->reference', ".
			"country_code='$lead->country_code', modify_date='$mdate', modify_time='$mdate_time', ".
			"custom_label_1='$lead->custom_label_1',  custom_label_2='$lead->custom_label_2', ".
			"custom_label_3='$lead->custom_label_3',  custom_label_4='$lead->custom_label_4'";
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Lead', 'A', "Lead=$lead_id", $ltext);
			return $lead_id;
		}
		
		return false;
	}

	function getMaxLeadID()
	{
		$sql = "SELECT MAX(lead_id) AS numrows FROM lead_profile";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}
	
	function updateLead($lid, $lead, $ltext)
	{
		if (empty($lid)) return false;

		$mdate = date("Y-m-d");
		$sql = "UPDATE lead_profile SET title='$lead->title', reference='$lead->reference', ".
			"country_code='$lead->country_code', modify_date='$mdate', ".
			"custom_label_1='$lead->custom_label_1',  custom_label_2='$lead->custom_label_2', ".
			"custom_label_3='$lead->custom_label_3',  custom_label_4='$lead->custom_label_4'  WHERE lead_id='$lid'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Lead', 'U', "Lead=$lid", $ltext);
			return true;
		}
		return false;
	}

	function addLeadNumber($lead_id, $number)
	{
		if (!is_array($number)) return false;

		$dial_number = isset($number['dial_number']) ? trim($number['dial_number']) : '';
		
		if (!empty($dial_number)) {
		
			$dial_number_2 = isset($number['dial_number_2']) ? trim($number['dial_number_2']) : '';
			$dial_number_3 = isset($number['dial_number_3']) ? trim($number['dial_number_3']) : '';
			$dial_number_4 = isset($number['dial_number_4']) ? trim($number['dial_number_4']) : '';
			
			if (strlen($dial_number) == 10) {
				$dial_number = '1' . $dial_number;
			}
			
			if (strlen($dial_number_2) == 10) {
				$dial_number_1 = '1' . $dial_number_2;
			}
			
			if (strlen($dial_number_3) == 10) {
				$dial_number_3 = '1' . $dial_number_3;
			}
			
			if (strlen($dial_number_4) == 10) {
				$dial_number_4 = '1' . $dial_number_4;
			}
			
			$title = isset($number['title']) ? trim($number['title']) : '';
			$first_name = isset($number['first_name']) ? trim($number['first_name']) : '';
			$last_name = isset($number['last_name']) ? trim($number['last_name']) : '';
			$street = isset($number['street']) ? trim($number['street']) : '';
			$city = isset($number['city']) ? trim($number['city']) : '';
			$state = isset($number['state']) ? trim($number['state']) : '';
			$zip = isset($number['zip']) ? trim($number['zip']) : '';
			
			$custom_value_1 = isset($number['custom_value_1']) ? trim($number['custom_value_1']) : '';
			$custom_value_2 = isset($number['custom_value_2']) ? trim($number['custom_value_2']) : '';
			$custom_value_3 = isset($number['custom_value_3']) ? trim($number['custom_value_3']) : '';
			$custom_value_4 = isset($number['custom_value_4']) ? trim($number['custom_value_4']) : '';
			
			$agent_altid = isset($number['agent_altid']) ? trim($number['agent_altid']) : '';
			
			$cid = isset($number['customer_id']) && !empty($number['customer_id']) ? trim($number['customer_id']) : $this->getNewLeadCustomerId();
			//IGNORE

			//$sql = "SELECT customer_id FROM leads WHERE lead_id='$lead_id' AND (customer_id='$cid' OR dial_number='$dial_number') LIMIT 1";
			$sql = "SELECT customer_id FROM leads WHERE customer_id='$cid'  AND lead_id='$lead_id' LIMIT 1";
			$result = $this->getDB()->query($sql);
			
			if (!empty($cid)) {
				if (is_array($result)) {
					$sql = "UPDATE leads SET customer_id='$cid', dial_number_2='$dial_number_2', ".
						"dial_number_3='$dial_number_3', dial_number_4='$dial_number_4', title='$title', first_name='$first_name', ".
						"last_name='$last_name', street='$street', city='$city', state='$state', zip='$zip', ".
						"custom_value_1='$custom_value_1', custom_value_2='$custom_value_2', custom_value_3='$custom_value_3', ".
						"custom_value_4='$custom_value_4' WHERE lead_id='$lead_id' AND dial_number='$dial_number' LIMIT 1";
				} else {
					$sql = "INSERT INTO leads SET lead_id='$lead_id', customer_id='$cid', dial_number='$dial_number', dial_number_2='$dial_number_2', ".
						"dial_number_3='$dial_number_3', dial_number_4='$dial_number_4', title='$title', first_name='$first_name', ".
						"last_name='$last_name', street='$street', city='$city', state='$state', zip='$zip', ".
						"custom_value_1='$custom_value_1', custom_value_2='$custom_value_2', custom_value_3='$custom_value_3', ".
						"custom_value_4='$custom_value_4'";
				// echo $sql;
				}
				return $this->getDB()->query($sql);
			}
		}
		
		return false;
	}
	
	function getNewLeadCustomerId()
	{
		$cid = '';
		$i = 5;
		while ($cid == '' && $i>0) {
			$cid = substr(time(), 2) . mt_rand(10000000, 99999999);
			$sql = "SELECT customer_id FROM leads WHERE customer_id='$cid' LIMIT 1";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				$cid = '';
			}
			$i--;
		}
		
		return $cid;
	}
	
	function addLeadNumberFromCRM($lead_id, $dial_num, $camid, $leadid, $disp_code)
	{
		$sql = "INSERT INTO leads(lead_id, dial_number, title, first_name, last_name, street, city, state, zip) ".
			"SELECT '$lead_id', dial_number, title, first_name, last_name, street, city, state, zip FROM crm ";
		$cond = '';
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond";
		
		return $this->getDB()->query($sql);
	}

	function deleteLead($lid)
	{
		$leadinfo = $this->getLeadById($lid); 
		if (!empty($leadinfo)) {
			$sql = "DELETE FROM lead_profile WHERE lead_id='$lid'";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM leads WHERE lead_id='$lid'";
				$this->getDB()->query($sql);
				
				$num_numbers =  $this->getDB()->getAffectedRows();
				$this->addToAuditLog('Lead', 'D', "Lead=$leadinfo->title", "Numbers=$num_numbers");
				return true;
			}
		}
		return false;
	}


	function getCampaignCount($title='')
	{
		$cond = "";
		$sql = "SELECT COUNT(campaign_id) AS total FROM campaign_profile ";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE '$title%'");
		if (!empty($cond)) $sql .= " WHERE $cond";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->total) ? 0 : $result[0]->total;
		}

		return 0;
	}
	
	function getCampaignList($title='', $offset=0, $rowsPerPage=20)
	{
		$cond = "";
		/*$sql = "SELECT campaign_id, title, skill_id, dial_engine, retry_count, retry_interval_amd, retry_interval_drop, ".
			"retry_interval_busy, retry_interval_cancel, retry_interval_chanunavail, retry_interval_noanswer, retry_interval_congestion, max_out_bound_calls, max_pacing_ratio, ".
			"max_drop_ratio, status, end_time FROM campaign_profile ORDER BY campaign_id LIMIT $offset, $rowsPerPage";*/
		$sql = "SELECT * FROM campaign_profile ";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE '$title%'");
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "ORDER BY campaign_id LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getCampaignById($cid)
	{
		$sql = "SELECT * FROM campaign_profile WHERE campaign_id='$cid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function getCampaignBySkillId($skillId)
	{
		$sql = "SELECT * FROM campaign_profile WHERE skill_id='$skillId'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function getCampaignLeadOptions($cid)
	{
		$sql = "SELECT lp.lead_id, lp.title, cl.campaign_id, cl.priority, cl.weight, cl.run_hour, cl.status FROM lead_profile AS lp ".
			"LEFT JOIN campaign_lead AS cl ON campaign_id='$cid' AND lp.lead_id=cl.lead_id";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getCampaignLeads($cid)
	{
		$sql = "SELECT lp.lead_id, lp.title, cl.campaign_id, cl.priority, cl.weight, cl.run_hour, cl.status FROM campaign_lead AS cl ".
			"LEFT JOIN lead_profile AS lp ON cl.lead_id=lp.lead_id WHERE campaign_id='$cid'";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function updateCampaignLead($cid, $oldlead, $lead)
	{
		if (empty($cid)) return false;
		
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		if ($lead->priority != $oldlead->priority) {
			$changed_fields .= "priority='$lead->priority'";
			$ltext = "Priority=$oldlead->priority to $lead->priority";
		}
		
		if ($lead->weight != $oldlead->weight) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "weight='$lead->weight'";
			$ltext = $this->addAuditText($ltext, "Weight=$oldlead->weight to $lead->weight");
		}
		
		if ($lead->run_hour != $oldlead->run_hour) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "run_hour='$lead->run_hour'";
			$ltext = $this->addAuditText($ltext, "Run hour=$oldlead->run_hour to $lead->run_hour");
		}
		

		if (!empty($changed_fields)) {
			$sql = "UPDATE campaign_lead SET $changed_fields WHERE campaign_id='$cid' AND lead_id='$oldlead->lead_id'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {
			$this->addToAuditLog('Campaign Lead', 'U', "Campaign=".$oldlead->campaign_id.";Lead=$oldlead->lead_id", $ltext);
		}
		
		return $is_update;
	}
	
	function addCampaignLead($cid, $lead)
	{
		if (empty($cid) || empty($lead->lead_id)) return false;
		
		$old_campaign = $this->getCampaignById($cid);
		if (empty($old_campaign)) return false;
		
		$sql = "INSERT INTO campaign_lead SET campaign_id='$cid', ".
			"lead_id='$lead->lead_id', priority='$lead->priority', weight='$lead->weight', run_hour='$lead->run_hour'";
		
		if ($this->getDB()->query($sql)) {
			$retry_count = "3";
			$sql = "SELECT retry_count FROM campaign_profile WHERE campaign_id='$cid' LIMIT 1";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				$retry_count = $result[0]->retry_count;
			}
			$sql = "INSERT INTO campaign_dial_list (campaign_id, lead_id, customer_id, dial_number, dial_index, retry_count, disposition, next_dial_time) ".
				"SELECT '$cid', lead_id, customer_id, dial_number, 1, '$retry_count', '', NOW() FROM leads WHERE lead_id='$lead->lead_id'";
			$this->getDB()->query($sql);

			if ($old_campaign->status == 'A') {
				sleep(1);
				require_once('lib/DBNotify.php');
				DBNotify::NotifyCampaignUpdate(UserAuth::getDBSuffix(), $cid, 'CMP_START', $this->getDB());
			}
			
			$this->addToAuditLog('Campaign Lead', 'A', "Campaign=".$cid, "Lead=$lead->lead_id;Priority=$lead->priority;Weight=$lead->weight;Run hour=$lead->run_hour");
			return true;
		}
		return false;
	}
	
	function updateCampaign($cid, $campaign, $ltext)
	{
		if (empty($cid) || !ctype_digit($cid)) return false;

		$old_campaign = $this->getCampaignById($cid);
		
		if (empty($old_campaign)) return false;
		
		$sql = "UPDATE campaign_profile SET title='$campaign->title', skill_id='$campaign->skill_id', agent_id='$campaign->agent_id', ".
			"dial_engine='$campaign->dial_engine', retry_count='$campaign->retry_count', cli='$campaign->cli', ".
			"retry_interval_drop='$campaign->retry_interval_drop', retry_interval_vm='$campaign->retry_interval_vm', ".
			"retry_interval_noanswer='$campaign->retry_interval_noanswer', retry_interval_unreachable='$campaign->retry_interval_unreachable', ".
			//"retry_interval_cancel='$campaign->retry_interval_cancel', retry_interval_chanunavail='$campaign->retry_interval_chanunavail', ".
		        "max_call_per_agent='$campaign->max_call_per_agent', drop_call_action='$campaign->drop_call_action', timezone='$campaign->timezone', ".
			"max_out_bound_calls='$campaign->max_out_bound_calls', max_pacing_ratio='$campaign->max_pacing_ratio', ".
			"max_pacing_ratio='$campaign->max_pacing_ratio', max_drop_rate='$campaign->max_drop_rate', run_hour='$campaign->run_hour' WHERE campaign_id='$cid'";
		//echo $sql;
		$is_update = false;
		if ($this->getDB()->query($sql)) {
			$is_update = true;
			
			if ($old_campaign->max_pacing_ratio != $campaign->max_pacing_ratio) {
				require_once('lib/DBNotify.php');
				if (!empty($old_campaign->skill_id)) {
					DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $old_campaign->skill_id, $this->getDB());
				}
				if ($old_campaign->skill_id != $campaign->skill_id && !empty($campaign->skill_id)) {
					DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $campaign->skill_id, $this->getDB());
				}
			}
		}
		/*
		if (isset($campaign->leads_selected)) {
			$oldleads = $this->getCampaignLeadOptions($cid);
			if (is_array($oldleads)) {
				foreach ($oldleads as $old) {
					if (in_array($old->lead_id, $campaign->leads_selected)) {

						$p = 'priority' . $old->lead_id;
						$priority = $campaign->$p;
						$p = 'weight' . $old->lead_id;
						$weight = $campaign->$p;
						$runhour = '';
						for ($i=0;$i<=23;$i++) {
							$p = 'runhour' . $old->lead_id . $i;
							$runhour .= isset($campaign->$p) ? '1' : '0';
						}
							
						if (!empty($old->campaign_id)) {
							$sql = "UPDATE campaign_lead SET priority='$priority', weight='$weight', run_hour='$runhour' WHERE campaign_id='$cid' AND lead_id='$old->lead_id'";
							if ($this->getDB()->query($sql)) $is_update = true;
						} else {
							$sql = "INSERT INTO campaign_lead SET campaign_id='$cid', lead_id='$old->lead_id', priority='$priority', weight='$weight', run_hour='$runhour'";
							if ($this->getDB()->query($sql)) $is_update = true;
						}
					} else if (!empty($old->campaign_id)) {
						$sql = "DELETE FROM campaign_lead WHERE campaign_id='$cid' AND lead_id='$old->lead_id'";
						if ($this->getDB()->query($sql)) $is_update = true;
					}
				}
			}
			$ltext .= 'Lead(s)=' . $campaign->leads . ';';
				
		} else {
			$ltext .= 'Lead(s)=Empty;';
			$sql = "DELETE FROM campaign_lead WHERE campaign_id='$cid'";
			if ($this->getDB()->query($sql)) $is_update = true;
		}
		*/
		
		if ($is_update) {
			$this->addToAuditLog('Campaign', 'U', "Campaign=$cid", $ltext);
		}
		return $is_update;
	}
	
	function addCampaign($campaign, $ltext)
	{
		$campaign_id = $this->getMaxCampaignID();
		
		if (empty($campaign_id)) { $campaign_id ='100001'; }
		else if ( $campaign_id == '999999') { return false; }
		else { $campaign_id++; }
		
		$mdate = date("Y-m-d");
		
		$sql = "INSERT INTO campaign_profile SET campaign_id='$campaign_id', title='$campaign->title', skill_id='$campaign->skill_id', ".
			"agent_id='$campaign->agent_id', dial_engine='$campaign->dial_engine', retry_count='$campaign->retry_count', ".
			"max_call_per_agent='$campaign->max_call_per_agent', drop_call_action='$campaign->drop_call_action', cli='$campaign->cli', ".
			"retry_interval_drop='$campaign->retry_interval_drop', retry_interval_vm='$campaign->retry_interval_vm', timezone='$campaign->timezone', ".
			"retry_interval_noanswer='$campaign->retry_interval_noanswer', retry_interval_unreachable='$campaign->retry_interval_unreachable', ".
			//"retry_interval_cancel='$campaign->retry_interval_cancel', retry_interval_chanunavail='$campaign->retry_interval_chanunavail', ".
			"max_out_bound_calls='$campaign->max_out_bound_calls', max_pacing_ratio='$campaign->max_pacing_ratio', max_drop_rate='$campaign->max_drop_rate', run_hour='$campaign->run_hour'";
		//echo $sql;exit;
		if ($this->getDB()->query($sql)) {
			/*
			if (isset($campaign->leads_selected)) {
				foreach ($campaign->leads_selected as $ld) {
					//if (!empty($campaign->leads)) $campaign->leads .= ',';
					//$campaign->leads .= $ld;
					$p = 'priority' . $ld;
					$priority = $campaign->$p;
					$p = 'weight' . $ld;
					$weight = $campaign->$p;
					$runhour = '';
					for ($i=0;$i<=23;$i++) {
						$p = 'runhour' . $ld . $i;
						$runhour .= isset($campaign->$p) ? '1' : '0';
					}
					$sql = "INSERT INTO campaign_lead SET campaign_id='$campaign_id', lead_id='$ld', priority='$priority', weight='$weight', run_hour='$runhour'";
					$this->getDB()->query($sql);
				}
				$ltext .= 'Lead(s)=' . $campaign->leads . ';';
			}
			*/
			
			$this->addToAuditLog('Campaign', 'A', "Campaign=$campaign_id", $ltext);
			return $campaign_id;
		}
		
		return false;
	}

	function getMaxCampaignID()
	{
		$sql = "SELECT MAX(campaign_id) AS numrows FROM campaign_profile";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}
	
	function refreshCampaign($cid)
	{
		$campaigninfo = $this->getCampaignById($cid); 
		if (!empty($campaigninfo)) {
			$sql = "UPDATE campaign_profile SET refresh_lead='Y' WHERE campaign_id='$cid'";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Campaign', 'U', "Campaign=$campaigninfo->title", "Lead refreshed");
				return true;
			}
		}
		return false;
	}
	
	function deleteCampaign($cid)
	{
		if (empty($cid) || !ctype_digit($cid)) return false;
		
		$campaigninfo = $this->getCampaignById($cid); 
		if (!empty($campaigninfo)) {
			$sql = "DELETE FROM campaign_profile WHERE campaign_id='$cid'";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM campaign_lead WHERE campaign_id='$cid'";
				$this->getDB()->query($sql);
				
				$num_leads =  $this->getDB()->getAffectedRows();
				
				$sql = "DELETE FROM campaign_dial_list WHERE campaign_id='$cid'";
                                $this->getDB()->query($sql);

				$this->addToAuditLog('Campaign', 'D', "Campaign=$campaigninfo->title", "Lead(s)=$num_leads");
				return true;
			}
		}
		return false;
	}
	
	function deleteCampaignLead($cid, $lid)
	{
		$leadinfo = $this->getCampaignLeadById($cid, $lid); 
		if (!empty($leadinfo)) {
			$sql = "DELETE FROM campaign_lead WHERE campaign_id='$cid' AND lead_id='$lid'";
			if ($this->getDB()->query($sql)) {
				$num_leads =  $this->getDB()->getAffectedRows();
				$this->addToAuditLog('Campaign Lead', 'D', "Campaign=$cid;Lead=$leadinfo->title", "Lead(s)=$num_leads");
				$sql = "DELETE FROM campaign_dial_list WHERE campaign_id='$cid' AND lead_id='$lid'";
				$this->getDB()->query($sql);
				return true;
			}
		}
		return false;
	}
	
	function updateCampaignStatus($cid, $status)
	{
		$campaigninfo = $this->getCampaignById($cid); 
		if (!empty($campaigninfo)) {
			if ($status == 'A') {
				if ($campaigninfo->status == 'N' || $campaigninfo->status == 'C' || $campaigninfo->status == 'T' || $campaigninfo->status == 'E' || empty($campaigninfo->status)) {
					$etime = time() + 28800;

					$sql = "UPDATE campaign_profile SET status='A', start_time='".date("Y-m-d H:i:s")."', stop_time='".date("Y-m-d H:i:s", $etime)."' WHERE campaign_id='$cid'";
					if ($this->getDB()->query($sql)) {
						require_once('lib/DBNotify.php');
	                                        DBNotify::NotifyCampaignUpdate(UserAuth::getDBSuffix(), $cid, 'CMP_START', $this->getDB());

						$this->addToAuditLog('Campaign', 'U', "Campaign=$campaigninfo->title", "Status=Active");
						return true;
					}
				}
			} else {
				if ($campaigninfo->status == 'A') {
					$sql = "UPDATE campaign_profile SET status='N' WHERE campaign_id='$cid'";
					if ($this->getDB()->query($sql)) {
						require_once('lib/DBNotify.php');
						DBNotify::NotifyCampaignUpdate(UserAuth::getDBSuffix(), $cid, 'CMP_STOP', $this->getDB());
						
						$this->addToAuditLog('Campaign', 'U', "Campaign=$campaigninfo->title", "Status=Inactive");
						return true;
					}
				}
			}
		}
		return false;
	}
	
	function updateCampaignEndTime($cid, $etime, $campaigninfo)
	{
		//$tstamp = $etime * 60;
		
		$sql = "UPDATE campaign_profile SET end_time=$etime WHERE campaign_id='$cid'";
		if ($this->getDB()->query($sql)) {
			//if ($etime > 0) $txt = 'End time increase ' . $etime . ' minute';
			//else $txt = 'End time less ' . $etime . ' minute';
			$txt = 'End time update';
			$this->addToAuditLog('Campaign', 'U', "Campaign=$campaigninfo->title", $txt);
			return true;
		}
		return false;
	}
	
	function getCampaignLeadById($cid, $lid)
	{
		$sql = "SELECT cl.*, lp.title FROM campaign_lead AS cl LEFT JOIN lead_profile AS lp ON lp.lead_id=cl.lead_id WHERE cl.campaign_id='$cid' AND cl.lead_id='$lid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function updateCampaignLeadStatus($cid, $lid, $status)
	{
		$campaign_lead_info = $this->getCampaignLeadById($cid, $lid); 
		
		if (!empty($campaign_lead_info)) {
			if ($status == 'A') {
				if ($campaign_lead_info->status == 'N' || $campaign_lead_info->status == 'C' || $campaign_lead_info->status == 'T' || empty($campaign_lead_info->status)) {
					$sql = "UPDATE campaign_lead SET status='A' WHERE campaign_id='$cid' AND lead_id='$lid' LIMIT 1";
					if ($this->getDB()->query($sql)) {
						$sql = "UPDATE campaign_profile SET refresh_lead='Y' WHERE campaign_id='$cid'";
						$this->getDB()->query($sql);
						$this->addToAuditLog('Campaign Lead', 'U', "Campaign=$cid;Lead=$lid", "Status=Active");
						return true;
					}
				}
			} else {
				if ($campaign_lead_info->status == 'A') {
					$sql = "UPDATE campaign_lead SET status='N' WHERE campaign_id='$cid' AND lead_id='$lid' LIMIT 1";
					if ($this->getDB()->query($sql)) {
						$this->addToAuditLog('Campaign Lead', 'U', "Campaign=$cid;Lead=$lid", "Status=Inactive");
						return true;
					}
				}
			}
		}
		return false;
	}
}

?>