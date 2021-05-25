<?php

class MPredictiveDial extends Model
{
    public $hide_column = null;
    public $csv_load_file = null;
    public $csv_load_column = null;

	function __construct()
    {
		parent::__construct();
	}
	
	function doNotifyPD($sid, $act)
	{
	    if (strlen($sid) != 2) return true;
	    
	    $event = '';
	    if ($act == 'start') {
	        $event = 'pd_start';
	    } elseif ($act == 'stop') {
	        $event = 'pd_stop';
	    } else {
	        return true;
	    }
	    
	    $sql = "SELECT db_ip, db_port FROM settings LIMIT 1";
	    $result = $this->getDB()->query($sql);
	    //print_r($result);
	    if(is_array($result))
	    {
	        //echo '111';
	        $sql = "SELECT updatedb('" . $result[0]->db_ip . "', '" . $result[0]->db_port . "', CONCAT('WWW\r\nType: SIP_NOTIFY\r\nevent: $event\r\nevent_body: $sid\r\n')) INTO @a";
	        //echo $sql;
	        $this->getDB()->query($sql);
	        
	        if ($act == 'start') {
	            $etime = time() + 28800;
	            $sql = "UPDATE pd_profile SET status='A', start_time='".date("Y-m-d H:i:s")."', stop_time='".date("Y-m-d H:i:s", $etime)."' WHERE skill_id='$sid'";
	            $this->getDB()->query($sql);
	        } elseif ($act == 'stop') {
	            $sql = "UPDATE pd_profile SET status='N' WHERE skill_id='$sid'";
	            $this->getDB()->query($sql);
	        }
	    }
            return true;
	}
	
	function RestartLead($sid)
	{
	    if (strlen($sid) != 2) return true;
	    
	    $sql = "UPDATE leads SET dial_status='A', retry_count=0 WHERE skill_id='$sid'";
	    if ($this->getDB()->query($sql)) {
	        //$this->doNotifyPD($sid, 'start');
	    }
	    
	    return true;
	}
	

    public static function get_dial_status_options($key='')
    {
        $dial_statuses = [
            'A'=>'Active',
            'E'=>'End',
            'F'=>'Failed',
            'P'=>'Call In Progress',
            'S'=>'Served',
            'T'=>'Schedule (Time) Dial',
        ];
        return empty($key) ? $dial_statuses : (!empty($dial_statuses[$key]) ? $dial_statuses[$key] : $key );
	}

	
	function numDialNumbers($skill_id='',$number_1='', $group_by_dial_status = FALSE)
	{
		$sql = "SELECT dial_status, COUNT(*) AS total FROM leads ";
		$sql .= " WHERE skill_id='{$skill_id}' ";
		$sql .= !empty($number_1) ? " AND number_1={$number_1} " :"";
        $sql .= $group_by_dial_status ? " GROUP BY dial_status" : "";

        $result = $this->getDB()->query($sql);
		return $group_by_dial_status ? $result : $result[0]->total;
	}

    public function get_pd_profile($skill_id='')
    {
        if (strlen($skill_id) !== 2) return;
        $query = "SELECT * FROM pd_profile WHERE skill_id='{$skill_id}' LIMIT 1";
        return $this->getDB()->query($query);
	}
	
	function getDialNumbers($skill_id='',$number_1, $offset=0, $rowsPerPage=0, $orderBy='id', $orderType='ASC')
	{
		$sql = "SELECT * FROM leads ";
        $sql .= " WHERE skill_id='{$skill_id}' ";
        $sql .= !empty($number_1) ? " AND number_1={$number_1} " :"";
		$sql .= " ORDER BY $orderBy $orderType";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";

		return $this->getDB()->query($sql);
	}

	function downloadDialNumbers($skill_id='',$orderBy='number_1', $orderType='ASC')
	{
		$sql = "SELECT * FROM leads";
		if (!empty($skill_id))
        {
            $sql .= " WHERE skill_id='$skill_id' ";
        }
        return $this->getDB()->query($sql);
	}
	
	function deleteDialNumbers($skill_id='')
	{
		$sql = "DELETE FROM leads";
        if (!empty($skill_id))
        {
            $sql .= " WHERE skill_id='$skill_id' ";
        }
		if ($this->getDB()->query($sql))
		{
		    $this->addToAuditLog('Predictive Dial Number', 'D', "", "All Numbers");
			return true;
		}
		return false;
	}
	
	function deleteNumber($id, $num,$skill_id)
	{
	        if (!ctype_digit($id) || !ctype_digit($num) || !ctype_upper($skill_id) || strlen($skill_id) != 2) return false;
	        
		$sql = "DELETE FROM leads WHERE id='$id' AND number_1='{$num}' AND  skill_id='{$skill_id}' LIMIT 1";
		if ($this->getDB()->query($sql))
		{
			$this->addToAuditLog('PD', 'D', "Dial Number=$num", "Number=$num");
			return true;
		}

		return false;
	}

    /**
     * @description This function prepends a zero(0) at predictive dial number
     * @param $number
     * @return String
     */
    protected function prependZeroToPDNumber($number){
        return "0".$number;
    }

    /**
     * @description This function checks if the number needs to
     * prepend a zero if the length of number  is 10 and starts with 1.
     * @param $number
     * @return bool
     */
    protected function needsToPrependZeroToPDNumber($number){
        return ( strlen($number) == 10 && substr($number,0,1) == 1 );
    }

	function addDialNumber($number,$skill_id='')
	{
		if (!is_array($number)) return false;
		if (empty($skill_id)) return false;

		//$skill_id = isset($number['skill_id']) ? trim($number['skill_id']) : '';
		$agent_id = isset($number['agent_id']) ? trim($number['agent_id']) : '';
		$dial_number = isset($number['number_1']) ? trim($number['number_1']) : '';
		
		//if ($dial_number == '8801711062313')  {echo '$sql'; exit;}
		
		if (!empty($dial_number))
		{
		
			$dial_number_2 = isset($number['number_2']) ? trim($number['number_2']) : '';
			$dial_number_3 = isset($number['number_3']) ? trim($number['number_3']) : '';

			if ($this->needsToPrependZeroToPDNumber($dial_number)){
                $dial_number = $this->prependZeroToPDNumber($dial_number);
            }

            if ($this->needsToPrependZeroToPDNumber($dial_number_2)){
                $dial_number_2 = $this->prependZeroToPDNumber($dial_number_2);
            }

            if ($this->needsToPrependZeroToPDNumber($dial_number_3)){
                $dial_number_3 = $this->prependZeroToPDNumber($dial_number_3);
            }
			
			if (strlen($dial_number) == 10) {
				$dial_number = '1' . $dial_number;
			}
			
			if (strlen($dial_number_2) == 10) {
				$dial_number_1 = '1' . $dial_number_2;
			}
			
			if (strlen($dial_number_3) == 10) {
				$dial_number_3 = '1' . $dial_number_3;
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
			
			$cid = isset($number['customer_id']) && !empty($number['customer_id']) ? trim($number['customer_id']) : $this->getNewDialCustomerId($dial_number);
			//IGNORE

			//$sql = "SELECT customer_id FROM leads WHERE lead_id='$lead_id' AND (customer_id='$cid' OR dial_number='$dial_number') LIMIT 1";
			$sql = "SELECT customer_id, number_1 FROM leads WHERE (skill_id='$skill_id' AND number_1='$dial_number') OR customer_id='$cid' LIMIT 1";
			$result = $this->getDB()->query($sql);
			
			if (!empty($cid))
			{
				if (is_array($result))
				{
				    $cid = $result[0]->customer_id;
					$sql = "UPDATE leads SET agent_id='$agent_id', number_1='$dial_number', number_2='$dial_number_2', ".
						"number_3='$dial_number_3', title='$title', first_name='$first_name', ".
						"last_name='$last_name', street='$street', city='$city', state='$state', zip='$zip', ".
						"custom_value_1='$custom_value_1', custom_value_2='$custom_value_2', custom_value_3='$custom_value_3', ".
						"custom_value_4='$custom_value_4', agent_altid='$agent_altid' WHERE customer_id='$cid' LIMIT 1";
						//"skill_id='$skill_id' AND number_1='$dial_number' LIMIT 1";
                                        //echo $sql;exit;
				}
				else
				{
                    /*$max = $this->getDB()->queryOnUpdateDB("SELECT MAX(id) AS num_sort_id FROM leads WHERE skill_id='$skill_id'");
                    if (empty($max[0]->num_sort_id) || $max[0]->num_sort_id == null) {
                        $max[0]->num_sort_id = ord(substr($skill_id,0,1)) . ord(substr($skill_id,1,1)) . '000000';
                    }
                    $max = $max[0]->num_sort_id+1;*/

                    ////08-04-2020
                    list($timestamp , $microseconds) = explode('.', microtime(true));
                    //$max = $timestamp.$microseconds;
                    $max = bin2hex(random_bytes('7'));

					$sql = "INSERT INTO leads SET skill_id='$skill_id', agent_id='$agent_id', id='$max', customer_id='$cid', number_1='$dial_number', number_2='$dial_number_2', ".
						"number_3='$dial_number_3', title='$title', first_name='$first_name', ".
						"last_name='$last_name', street='$street', city='$city', state='$state', zip='$zip', ".
						"custom_value_1='$custom_value_1', custom_value_2='$custom_value_2', custom_value_3='$custom_value_3', ".
						"custom_value_4='$custom_value_4', agent_altid='$agent_altid'";
				}
			//if ($dial_number == '8801711062313')  {echo $sql; exit;}

				return $this->getDB()->query($sql);
			}
		}
		
		return false;
	}
	
	function getNewDialCustomerId($number)
	{
		$cid = '';
		$prefix = '';
		$i = 5;
		while ($cid == '' && $i>0)
        {
            if ($number) {
                $cid = $number;
                $prefix = $number;
                $number = '';
            }
			$cid = substr($prefix, 6) . mt_rand(1000, 9999);
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


    public static function addPredictiveProfile($profile)
    {
        $query = "SELECT COUNT(*) AS total_skill_profile FROM pd_profile WHERE skill_id='{$profile->skill_id}' ";
        $pd_model = new static();
        $result = $pd_model->getDB()->query($query);

        if (!empty($result[0]->total_skill_profile) && $result[0]->total_skill_profile > 0)
        {
            $query = "SELECT * FROM pd_profile WHERE skill_id='{$profile->skill_id}' LIMIT 1";
            //$pd_profile = $this->get_pd_profile($skill_id);
            $pd_profile = $pd_model->getDB()->query($query);
            $sql = "Update pd_profile SET dial_alt_number='{$profile->dial_alt_number}', ".
                "dial_engine='$profile->dial_engine', retry_count='$profile->retry_count', status='{$profile->status}', ".
                "max_call_per_agent='$profile->max_call_per_agent', drop_call_action='$profile->drop_call_action', fax_action='{$profile->fax_action}', ".
                "retry_interval_drop='$profile->retry_interval_drop', retry_interval_vm='$profile->retry_interval_vm', timezone='$profile->timezone', ".
                "retry_interval_noanswer='$profile->retry_interval_noanswer', retry_interval_unreachable='$profile->retry_interval_unreachable', ".
                "max_out_bound_calls='$profile->max_out_bound_calls', max_pacing_ratio='$profile->max_pacing_ratio', max_drop_rate='$profile->max_drop_rate', ".
                "run_hour='$profile->run_hour', param='$profile->param' WHERE skill_id='$profile->skill_id' ";
                if ($pd_model->getDB()->query($sql)) {
                    $pd_model->addToAuditLog('PD Profile', 'U', "PD Profile=$profile->skill_id", $profile);
                /*
                    if (is_array($pd_profile) && $pd_profile[0]->skill_id &&
                       ($profile->max_pacing_ratio != $pd_profile[0]->max_pacing_ratio || $profile->dial_engine != $pd_profile[0]->dial_engine ||
                       $profile->param != $pd_profile[0]->param)) {
                */
                    if (is_array($pd_profile) && $pd_profile[0]->skill_id) {
                       require_once('lib/DBNotify.php');
                       DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $profile->skill_id, $pd_model->getDB());
                    }
                    return 'U';
                }
        }
        else
        {
           $sql = "INSERT INTO pd_profile SET  skill_id='$profile->skill_id', dial_alt_number='{$profile->dial_alt_number}', ".
                "dial_engine='$profile->dial_engine', retry_count='$profile->retry_count', status='{$profile->status}', ".
                "max_call_per_agent='$profile->max_call_per_agent', drop_call_action='$profile->drop_call_action', fax_action='{$profile->fax_action}', ".
                "retry_interval_drop='$profile->retry_interval_drop', retry_interval_vm='$profile->retry_interval_vm', vm_action='{$profile->vm_action}', timezone='$profile->timezone', ".
                "retry_interval_noanswer='$profile->retry_interval_noanswer', retry_interval_unreachable='$profile->retry_interval_unreachable', ".
                "max_out_bound_calls='$profile->max_out_bound_calls', max_pacing_ratio='$profile->max_pacing_ratio', max_drop_rate='$profile->max_drop_rate', ".
                "run_hour='$profile->run_hour', param='$profile->param' ";
            if ($pd_model->getDB()->query($sql)) {
                $pd_model->addToAuditLog('PD Profile', 'A', "PD Profile=$profile->skill_id", $profile);
                require_once('lib/DBNotify.php');
                DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $profile->skill_id, $this->getDB());
                return TRUE;
            }
        }

        return FALSE;
    }

    function updatePDEndTime($sid, $etime, $pdinfo)
        {
                $sql = "UPDATE pd_profile SET stop_time='".date("Y-m-d H:i:s", $etime)."' WHERE skill_id='$sid' LIMIT 1";
                if ($this->getDB()->query($sql)) {
                    $txt = 'End time update';
                    $this->addToAuditLog('PD Profile', 'U', "SkillId=$sid", $txt);
                    require_once('lib/DBNotify.php');
                    DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $sid, $this->getDB());
                    return true;
                }
                return false;
        }

    public static function getPdProfileBySkillId($skill_id)
    {
       $pd = new static();
       $query = "SELECT * FROM pd_profile WHERE skill_id='{$skill_id}' ";
       $result = $pd->getDB()->query($query);
       return !empty($result[0]) ? $result[0] : (new static());
    }

    public function getLeadsColumn(){
        $sql = "DESC leads";
        $result = $this->getDB()->query($sql);
        $response = [];
        if (!empty($result)) {
            foreach ($result as $key){
                if (!in_array($key->Field, $this->hide_column)) {
                    $response[] = $key->Field;
                }
            }
        }
        return $response;
    }

    function saveLeadData($data, $header, $isDelete, $skill_id) {
        if (!empty($data)){

            if (!empty($isDelete) && !empty($skill_id)) {
                $sql = "DELETE FROM leads where skill_id='$skill_id'";
                $this->getDB()->query($sql);
            }

            $sql = "INSERT INTO leads (";
            $sql .= implode(", ", $header);
            $sql = rtrim($sql, ",").") VALUES (";
            foreach ($data as $item) {
                $sql .= "'".implode("','", $item)."'";
                $temp = rtrim(",'", $sql)."),(";
                $sql .= $temp;
            }
            $sql = rtrim($sql, ",(");
            return $this->getDB()->query($sql);
        }
        return false;
    }

    /*
     * insert csv
     */
    function csv_load(){
        $sql = "LOAD DATA INFILE '".$this->csv_load_file."' ";
        $sql .= "INTO TABLE leads FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' ";
        $sql .= "(".$this->csv_load_column.")";
        return $this->getDB()->query($sql);
    }

    /*
     * get skill wise
     * maximum id
     */
    public static function GET_MAX_ID($skill_id=null) {
        $sql = "SELECT MAX(id) AS num_sort_id FROM leads ";
        $sql .= !empty($skill_id) ? " WHERE skill_id='$skill_id' " : "";

        $obj = new self();
        $result = $obj->getDB()->queryOnUpdateDB($sql);
        return !empty($result) ? $result[0]->num_sort_id : null;
    }
}

