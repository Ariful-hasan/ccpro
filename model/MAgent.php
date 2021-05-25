<?php

class MAgent extends Model
{
	var $expireMsgDays = '';
	var $passExpireDays = '';
	var $loginAttempts = '';
	var $passRepeatNo = '';
	function __construct() {
		parent::__construct();
	}
	
	function notifyAgentUpdate($sync_type, $aid, $file='')
        {
		require_once('lib/DBNotify.php');
		DBNotify::NotifyAgentUpdate(UserAuth::getDBSuffix(), $aid, $this->getDB());
	}
	
	function getCCAccountInfo($db_suffix)
	{
		if ($this->getDB()->cctype > 0) {
			$sql = "SELECT ws_port, 'P' AS active_sip_srv, switch_ip AS sip_srv_primary, '' AS sip_srv_backup FROM settings LIMIT 1";
		} else {
			$sql = "SELECT ws_port, active_sip_srv, sip_srv_primary, sip_srv_backup FROM cc_master.account WHERE db_suffix='$db_suffix' LIMIT 1";
		}
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			//? $result[0] : null;
			$result[0]->ws_ip = $result[0]->active_sip_srv == 'P' ? $result[0]->sip_srv_primary : $result[0]->sip_srv_backup;
			if (!is_public_ip($result[0]->ws_ip)) {
                                //list($http_host, $http_port) = explode(":", $_SERVER['HTTP_HOST']);
                                list($http_host) = explode(":", $_SERVER['HTTP_HOST']);
                                $http_host_ip = gethostbyname($http_host);
                                if (is_public_ip($http_host_ip)) {
                                        $result[0]->ws_ip = $http_host_ip;
                                }
                        }
			return $result[0];
		}
		
		return null;
	}
	
	/*
	function getValidAgent($user, $pass)
	{		
		$concatPass = $user . $pass;
		//$sql = "SELECT * FROM agents WHERE agent_id='$user' AND BINARY password=OLD_PASSWORD('$pass')";
		$concatPass = md5($concatPass);
		$sql = "SELECT * FROM agents WHERE agent_id='$user' AND (BINARY password=OLD_PASSWORD('$pass') OR password=SHA2(CONCAT(agent_id, '$concatPass'), 256))";
		//echo $sql;
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {

			$this->clearMissloginData($user);
			
			$sql = "SELECT value FROM settings WHERE item='report_day' LIMIT 1";
			$tresult = $this->getDB()->query($sql);
			$repDays = 0;
			if (isset($tresult[0]->value) && !empty($tresult[0]->value)){
			    $repDays = $tresult[0]->value;
			}
			UserAuth::setReportDay($repDays);
			
			return $result[0];
		}

		return null;
	}
*/
	function getValidAgent($user, $pass)
        {
        	$isAgentFound = false;
        	
                $concatPass = $user . $pass;
                //echo $concatPass . ' ';
                $concatPass = md5($concatPass);
                
                if (preg_match('/^[A-Za-z0-9_]{4,12}$/', $user)) {
                	$sql = "SELECT * FROM agents WHERE agent_id='$user' AND password=SHA2(CONCAT(agent_id, '$concatPass'), 256)";
                	$result = $this->getDB()->query($sql);
                	if ($this->getDB()->getNumRows() == 1) {
                		$isAgentFound = true;
                	} else {
                	
                		$concatPass = $user . $pass;
				$pass = $this->getDB()->escapeString($pass);
				
                		$sql = "SELECT * FROM agents WHERE agent_id='$user' AND (BINARY password=OLD_PASSWORD('$pass') OR password=SHA2('$concatPass', 256))";
                		$result = $this->getDB()->query($sql);
                		if ($this->getDB()->getNumRows() == 1) {
                			$isAgentFound = true;
                		} else {
					$sql = "SELECT * FROM agents WHERE agent_id='$user' AND password=SHA2(CONCAT(agent_id, '$concatPass'), 256)";
					$result = $this->getDB()->query($sql);
					if ($this->getDB()->getNumRows() == 1) {
						$isAgentFound = true;
					}
                		}
                		
                		//if ($this->getDB()->getNumRows() == 1) {
                		if ($isAgentFound) {
                			$isAgentFound = true;
                			$concatPass = md5($concatPass);
                			$sql = "UPDATE agents SET password=SHA2(CONCAT(agent_id, '$concatPass'), 256) WHERE agent_id='$user' LIMIT 1";
                			$result2 = $this->getDB()->query($sql);
                		}
                	}
                	
	                if ($isAgentFound) {
	                
	                        $this->clearMissloginData($user);
                        
        	                /*
                	        $sql = "SELECT value FROM settings WHERE item='report_day' LIMIT 1";
                	        $tresult2 = $this->getDB()->query($sql);
                	        $repDays = 0;
         	                if (isset($tresult2[0]->value) && !empty($tresult2[0]->value)){
                        	    $repDays = $tresult2[0]->value;
                        	}
                        	*/
	                        $repDays = 90;
        	                UserAuth::setReportDay($repDays);

        	                // role type menu read
        	                $sql = "SELECT * FROM menus WHERE role_id='".$result[0]->role_id."' AND status='".STATUS_ACTIVE."'";
                			$menu_info = $this->getDB()->query($sql);
                			$result[0]->menu_info = $menu_info[0]->menu_info;

                	        return $result[0];
			}

                }
                
                return null;
        }

	function GenerateToken($user)
	{		
		$hashcode=hash("crc32b",$user.rand(10,99).time());
		$sql = "UPDATE agents SET var1='$hashcode' WHERE agent_id='$user' LIMIT 1";
		if($this->getDB()->query($sql)){
			return $hashcode;
		}		
		return $hashcode;
	}
	
	function getPanelAccessTimeline($agentid)
	{
		$timeline = new stdClass();
		$timeline->stop_timestamp = 0;
		$timeline->is_check_required = false;
		$sql = "SELECT * FROM agents_access_restrictions WHERE agent_id='$agentid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			if ($result[0]->start_time != '0000-00-00 00:00:00' && $result[0]->stop_time != '0000-00-00 00:00:00') {
				$timeline->is_check_required = true;
				$time = time();
				$start_time = strtotime($result[0]->start_time);
				$stop_time = strtotime($result[0]->stop_time);
				if ($time >= $start_time && $time < $stop_time) {
					$timeline->stop_timestamp = $stop_time;
				}
			}
		}
		
		return $timeline;
	}
	
	function isAgentExist($user)
	{
		$sql = "SELECT agent_id,nick FROM agents WHERE agent_id='$user'";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {	
			$result = $this->getDB()->query($sql);
			//Password validation rules apply...
			$this->setPasswordRules();
			return $result[0];
		}

		return null;
	}
	function isLoginPINExist($loginpin)
	{
		$sql = "SELECT count(*) as total FROM agents WHERE login_pin='$loginpin'";
		$result = $this->getDB()->query($sql);
	
		if ($this->getDB()->getNumRows() == 1) {					
			return $result[0]->total>0;
		}	
		return false;
	}
	
	function getAgentAccessRestrictions($agentid)
	{
		$sql = "SELECT * FROM agents_access_restrictions WHERE agent_id='$agentid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		
		if ($this->getDB()->getNumRows() == 1) {
			return $result[0];
		}
		
		return null;
	}
	
	function setPasswordRules() {
		$sql = "SELECT rule_no, value, status FROM password_settings WHERE rule_no IN ('8', '9', '10', '11')";
		$passData = $this->getDB()->query($sql);
		if (count($passData) > 0){
			foreach ($passData as $rule) {
				if ($rule->status == 'Y'){
					if ($rule->rule_no == '8'){
						$this->passExpireDays = $rule->value;
					}
					if ($rule->rule_no == '9'){
						$this->expireMsgDays = $rule->value;
					}
					if ($rule->rule_no == '10'){
						$this->passRepeatNo = $rule->value;
					}
					if ($rule->rule_no == '11'){
						$this->loginAttempts = $rule->value;
					}
				}
			}
		}
	}
	
	function attemptsNumUnlock()
	{
		$sql = "SELECT rule_no, value, status FROM password_settings WHERE rule_no = '11'";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {	
			$result = $this->getDB()->query($sql);
			if (isset($result[0]->status) && $result[0]->status == 'Y'){
				return $result[0]->value;
			}			
		}
		return null;
	}
	
	function saveLoginAttempt($uid)
	{
		if (empty($uid)) return false;
		
		$sql = "INSERT INTO history_misslogin SET hit_date=NOW(), agent_id='$uid', ip='".$_SERVER['REMOTE_ADDR']."'";
		return $this->getDB()->query($sql);
	}
	
	function isAgentBlocked($uid){
		if (empty($uid) || empty($this->loginAttempts)) return false;
			
		$sql = "SELECT COUNT(*) AS totalMissed FROM history_misslogin WHERE agent_id = '$uid' AND hit_date >= TIMESTAMPADD(MINUTE,-30, NOW()) AND ip = '".$_SERVER['REMOTE_ADDR']."'";
		$result = $this->getDB()->query($sql);
		if ($this->getDB()->getNumRows() == 1 && $result[0]->totalMissed >= $this->loginAttempts) {
			return true;
		}else {
			return false;
		}
	}
	
	function clearMissloginData($uid){
		if (empty($uid)) return false;
		$randomNum = rand(1, 10);
		if ($randomNum == 5){
			$sql = "DELETE FROM history_misslogin WHERE agent_id = '$uid' AND hit_date < TIMESTAMPADD(MINUTE,-30, NOW())";
			return $this->getDB()->query($sql);
		}
		return false;
	}
	
	function clearMisslogin2Unlock($uid, $aip){
		if (empty($uid) || empty($aip)) return false;
		$sql = "DELETE FROM history_misslogin WHERE agent_id = '$uid' AND ip='$aip'";
		return $this->getDB()->query($sql);
	}
	
	function isAgentPassExist($user, $pass)
	{
		if (empty($user) || empty($pass) || empty($this->passRepeatNo)) return false;
		$concatPass = $user . $pass;
		$concatPass = md5($concatPass);
		$sql = "SELECT p.password FROM (SELECT password FROM history_password WHERE agent_id='$user' ORDER BY pass_date DESC LIMIT ".$this->passRepeatNo.") AS p WHERE (BINARY password=OLD_PASSWORD('$pass') OR password=SHA2(CONCAT(agent_id, '$concatPass'), 256))";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() > 0 && !empty($result[0]->password)) {
			return true;
		}else {
			return false;
		}
	}
	
	function resetAgentPassword($user)
	{
		if (empty($user)) return false;
		
		$password = "";
		$numberchar = "135798642";
		$capletter = "ABCDEFGHMNKPTRVX";
		$smallletter = "zypqwjstuabgh";
		$alt = time () % 2;
		for($i = 0; $i < 8; $i ++) {
			if ($alt == 1) {
				$password .= $capletter [(rand () % strlen ( $capletter ))];
				$alt = 2;
			} elseif($alt == 2) {
				$password .= $numberchar [(rand () % strlen ( $numberchar ))];
				$alt = 0;
			}else {
				$password .= $smallletter [(rand () % strlen ( $smallletter ))];
				$alt = 1;
			}
		}
		
		//$pass_md5 = md5($password);
		$concatPass = $user . $password;
		$pass_md5 = md5($concatPass);
		$sql = "UPDATE agents SET password = SHA2(CONCAT(agent_id, '$pass_md5'), 256) WHERE agent_id='$user'";
		if ($this->getDB()->query($sql)){
			$this->addToAuditLog('Password', 'U', "Agent=$user", "Password reset");
			return $password;
		}else {
			return false;
		}
	}
	
	function isPassChangeOver90Days($user, $pass)
	{
		if (empty($user)) return false;
		$sql = "SELECT pass_date FROM history_password WHERE agent_id='$user' ORDER BY pass_date DESC LIMIT 1";
		$result = $this->getDB()->query($sql);
		$numRows = count($result);
		if (empty($this->passExpireDays) && $numRows <= 0) return true;
		elseif (empty($this->passExpireDays)) return false;
		
		$passDate = $result[0]->pass_date;
		$passExpDays = (int)$this->passExpireDays;

		if ($numRows == 1 && !empty($passDate)) {
			if( strtotime($passDate) < strtotime('-'.$passExpDays.' day') ) {
				return true;
			}elseif (!empty($pass) && !empty($this->passRepeatNo) && !$this->isAgentPassExist($user, $pass)){
				return true;
			}			
		}else {
			return true;
		}
		return false;
	}
	
	function isPassChangeOver70Days($user, $pass)
	{
		if (empty($user) || empty($this->expireMsgDays)) return false;
		$sql = "SELECT pass_date FROM history_password WHERE agent_id='$user' ORDER BY pass_date DESC LIMIT 1";
		$result = $this->getDB()->query($sql);
		$numRows = $this->getDB()->getNumRows();
		$passDate = isset($result[0]->pass_date) ? $result[0]->pass_date : "";
		$passMsgDays = (int)$this->expireMsgDays;

		if ($numRows == 1 && !empty($passDate)) {
			if( strtotime($passDate) < strtotime('-'.$passMsgDays.' day') ) {
				return $passDate;
			}			
		}
		return null;
	}

	function numAgents($usertype='', $agent_id='', $did='', $status='', $nick_name='', $supervisor_id = '')
	{
		$cond = '';
		$sql = "SELECT COUNT(agent_id) AS numrows FROM agents ";
		if (!empty($agent_id)) $cond .= "agent_id= ?";
		if (!empty($did)) $cond = $this->getAndCondition($cond, "did LIKE ?");
		if (!empty($usertype)) $cond = $this->getAndCondition($cond, "usertype= ?"); 
		if (!empty($status)) $cond = $this->getAndCondition($cond, "active=?");
		
		$sound_nick = !empty($nick_name) ? explode(" ", $nick_name) : [];
		$sound_nick[0] = !empty($sound_nick[0]) ? preg_replace("/[^A-Za-z0-9]/", "", $sound_nick[0]) : "";
		$soundNick = !empty($sound_nick[0]) ? soundex($sound_nick[0]) : "";
		
		if (!empty($nick_name)){
			$nickCond = !empty($soundNick) ? "nick LIKE ? OR nick_sound= '$soundNick'" : "nick LIKE ?";
			$cond = $this->getAndCondition($cond, $nickCond);
		}
		if (!empty($supervisor_id)) $cond = $this->getAndCondition($cond, "supervisor_id= ?");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		// echo $sql;

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($agent_id)){  $condParams[] = ["paramType" => "s", "paramValue" => $agent_id, "paramLength" => "15"]; }
		if(!empty($did)){  $condParams[] = ["paramType" => "s", "paramValue" => '%'.$did."%", "paramRawValue" => $did]; }
		if(!empty($usertype)){  $condParams[] = ["paramType" => "s", "paramValue" => $usertype]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		if(!empty($nick_name)){ 
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$nick_name."%", "paramRawValue" => $nick_name];
		}
		if(!empty($supervisor_id)){  $condParams[] = ["paramType" => "s", "paramValue" => $supervisor_id, "paramLength" => "15"]; }
		$validate = $this->getDB()->validateQueryParams($condParams);

		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
		

	}
	
	function numMissLogins()
	{
		$cond = '';
		$sql = "SELECT COUNT(agent_id) AS numrows FROM history_misslogin WHERE  agent_id<>'' and hit_date>= TIMESTAMPADD( MINUTE,-30,  NOW() ) GROUP BY agent_id,ip";
		$result = $this->getDB()->query($sql);

		return $this->getDB()->getNumRows();
	}
	
	function getMissLogins($offset=0, $limit=0)
	{
		$sql = "SELECT  SQL_CALC_FOUND_ROWS  ag.name, ag.nick, hm.agent_id, hm.ip, count( hm.agent_id )  as tried ";
		$sql .= "FROM history_misslogin AS hm ";
		$sql .= "LEFT JOIN agents as ag ON ag.agent_id=hm.agent_id ";
		$sql .= "WHERE  hm.agent_id<>'' and hm.hit_date >= TIMESTAMPADD( MINUTE,-30, NOW() ) ";
		$sql .= "GROUP BY hm.agent_id,hm.ip ORDER BY tried DESC ";
		
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		return $this->getDB()->query($sql);
	}
	
	function getConferenceAgents()
	{
		$sql = "SELECT nick, agent_id, ip, busy_status, aux_status, active FROM agents WHERE active='Y' ORDER BY agent_id";
		return $this->getDB()->query($sql);
	}
	
	function getAgents($usertype='', $agent_id='', $did='', $status='', $offset=0, $limit=0, $nick_name='', $supervisor_id='')
	{
	
		$cond = '';
		$sql = "SELECT * FROM agents ";
		if (!empty($agent_id)){ 
			$cond .= "agent_id= ?";
		}
		
		if (!empty($did)){
			$cond = $this->getAndCondition($cond, "did LIKE ?");
		}	 
		
		if (!empty($usertype)) {
			$cond = $this->getAndCondition($cond, "usertype= ?");
		}
		else{
			$cond = $this->getAndCondition($cond, "usertype in ('A','S','P','G')");
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "active=?");
		}
		
		$sound_nick = !empty($nick_name) ? explode(" ", $nick_name) : [];
		$sound_nick[0] = !empty($sound_nick[0]) ? preg_replace("/[^A-Za-z0-9]/", "", $sound_nick[0]) : "";
		$soundNick = !empty($sound_nick[0]) ? soundex($sound_nick[0]) : "";
		
		if (!empty($nick_name)){ 
			$nickCond = !empty($soundNick) ? "nick LIKE ? OR nick_sound= '$soundNick'" : "nick LIKE ?";
			$cond = $this->getAndCondition($cond, $nickCond);
		}
		if (!empty($supervisor_id)){ 
			$cond = $this->getAndCondition($cond, "supervisor_id= ?");
		}
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY agent_id ";

		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		// var_dump($sql);
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($agent_id)){  $condParams[] = ["paramType" => "s", "paramValue" => $agent_id, "paramLength" => "15"]; }
		if(!empty($did)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$did."%", "paramRawValue" => $did]; }
		if(!empty($usertype)){  $condParams[] = ["paramType" => "s", "paramValue" => $usertype]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		if(!empty($nick_name)){ 
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$nick_name."%", "paramRawValue" => $nick_name];
		}
		if(!empty($supervisor_id)){  $condParams[] = ["paramType" => "s", "paramValue" => $supervisor_id, "paramLength" => "15"]; }
		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}

		return $result;
	
	}
	
	function getAgentNames($usertype='', $status='', $offset=0, $limit=0, $format='sql')
	{
		$cond = '';
		$sql = "SELECT agent_id, nick, name FROM agents ";
		if (!empty($usertype)) $cond .= "usertype='$usertype'";
		if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY agent_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		$result = $this->getDB()->query($sql);
		if ($format == 'array') {
			$names = array();
			if (is_array($result)) {
				foreach ($result as $row) {
					$names[$row->agent_id] = !empty($row->nick) ? $row->nick : $row->agent_id;
				}
			}
			return $names;
		}
		return $result;
	}

	function getAgentById($agentid)
	{
		$sql = "SELECT * FROM agents WHERE agent_id='$agentid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getSupervisedAgents($agentid)
	{
		$sql = "SELECT DISTINCT s.agent_id, a.nick FROM agent_skill s LEFT JOIN agents a ".
			"ON a.agent_id=s.agent_id WHERE skill_id IN (SELECT skill_id FROM agent_skill ".
			"WHERE agent_id='$agentid')";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getAssignedSkills($agentid)
	{
		$sql = "SELECT skill_id FROM agent_skill WHERE agent_id='$agentid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$skills = '';
			foreach ($result as $row) {
				if (!empty($skills)) $skills .= ",";
				$skills .= "'$row->skill_id'";
			}
			if (!empty($skills)) {

				$sql1 = "SELECT skill_id, skill_name, 'in' AS type FROM skill ";
				//$sql2 = "SELECT skill_id, skill_name, 'out' AS type FROM skill_out ";
				$cond = "skill_id IN ($skills) AND active='Y'";
				$sql1 .= "WHERE $cond ";
				//$sql2 .= "WHERE $cond ";
				//$sql = $sql1 . " UNION (" . $sql2 . ")";
				$sql = $sql1;
				//echo $sql;
				return $this->getDB()->query($sql);
			}
		}
		return null;
	}
	
	function getAssignedSkillsShort($agentid)
        {
                $sql = "SELECT skill_id FROM agent_skill WHERE agent_id='$agentid'";
                $result = $this->getDB()->query($sql);
                if (is_array($result)) {
                        $skills = '';
                        foreach ($result as $row) {
                                if (!empty($skills)) $skills .= ",";
                                $skills .= "'$row->skill_id'";
                        }
                        if (!empty($skills)) {

                                $sql1 = "SELECT skill_id, short_name, qtype, 'in' AS type FROM skill ";
                                //$sql2 = "SELECT skill_id, skill_name, 'out' AS type FROM skill_out ";
                                $cond = "skill_id IN ($skills) AND active='Y'";
                                $sql1 .= "WHERE $cond ";
                                //$sql2 .= "WHERE $cond ";
                                //$sql = $sql1 . " UNION (" . $sql2 . ")";
                                $sql = $sql1;
                                //echo $sql;
                                return $this->getDB()->query($sql);
                        }
                }
                return null;
        }

	function getAgentSkill($agent_id='', $skill_id='')
	{
		$cond = '';
		$sql = "SELECT ag.skill_id, ag.agent_id, ag.priority, s.skill_name, s.qtype FROM agent_skill as ag LEFT JOIN skill as s ON s.skill_id = ag.skill_id ";
		if (!empty($agent_id)) $cond = "ag.agent_id='$agent_id'";
		if (!empty($skill_id)) $cond = $this->getAndCondition($cond, "ag.skill_id='$skill_id'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY ag.priority, ag.agent_id ";
		
		return $this->getDB()->query($sql);
	}

	function addAgent($agent)
	{
		if (empty($agent->agent_id)) return false;
		
		//$pass_md5 = $agent->password;
		$login_pin = '';
		for ($i=0;$i<=5;$i++) {
			$login_pin = rand(10000, 99999);
			if ($this->isLoginPINExist($login_pin)) {
				$login_pin = '';
			} else {
				break;
			}
			$i++;
		}
		
		if (empty($login_pin)) return false;
		
		$concatPass = $agent->agent_id . $agent->password;
		$pass_md5 = md5($concatPass);
		$sound_nick = !empty($agent->nick) ? explode(" ", $agent->nick) : "";
		$sound_nick[0] = !empty($sound_nick[0]) ? preg_replace("/[^A-Za-z0-9]/", "", $sound_nick[0]) : "";
		$soundNick = !empty($sound_nick[0]) ? soundex($sound_nick[0]) : "";
		
		$sql = "INSERT INTO agents SET ".
			"agent_id='$agent->agent_id', ".
			"altid='$agent->altid', ".
			"nick='$agent->nick', ".
			"password=SHA2(CONCAT('$agent->agent_id', '$pass_md5'), 256), ".
			"usertype='$agent->usertype', ".
			"name='$agent->name', ".
			"telephone='$agent->telephone', ".
			"did='$agent->did', ".
			"email='$agent->email', ".
			"partition_id='$agent->partition_id', ".
			"birth_day='$agent->birth_day', ".
			"login_pin='$login_pin', ".
			"language_1='$agent->language_1', ".
			"language_2='$agent->language_2', ".
			"language_3='$agent->language_3', ".
			"nick_sound='$soundNick', ".
			"max_chat_session='$agent->max_chat_session', ".
			"chat_session_limit_with_call='$agent->chat_session_limit_with_call', ".
			"active='$agent->active', ".
			"role_id='$agent->role_id', ".
			"supervisor_id='$agent->supervisor_id', ".
			"screen_logger = '$agent->screen_logger', ".
			"ob_call = '$agent->ob_call' ";
		// echo $sql;exit;
		if ($this->getDB()->query($sql)) {
			//$this->addAgentSkill($agent->agent_id, $services);

			//$agent_title = $agent->usertype == 'S' ? 'Supervisor' : 'Agent';
			if ($agent->usertype == 'S') {
				$agent_title = 'Supervisor';
			} else if ($agent->usertype == 'D') {
				$agent_title = 'Dashboard User';
			} else if ($agent->usertype == 'P') {
				$agent_title = 'Report User';
			} else if ($agent->usertype == 'G') {
				$agent_title = 'Digicon Report User';
			} else {
				$agent_title = 'Agent';
			}
			$ltxt = $agent->active=='Y' ? 'Active' : 'Inactive';
			$ltxt = "Nick=".$agent->nick.";Pass=*;Status=".$ltxt;
			//$skill_text = $this->getLoggedServices($services, $skill_options);
			//if (!empty($skill_text)) $ltxt .= ";$skill_text";
            $this->synchronizeSeatWithAgent($agent->agent_id, $agent->screen_logger, $agent->ob_call);
			$this->addToAuditLog($agent_title, 'A', $agent_title . "=".$agent->agent_id, $ltxt);

			return true;
		}
		
		return false;
	}
	
	function addRootAgent($agent)
	{
		if (empty($agent->agent_id)) return false;
	
		//$pass_md5 = $agent->password;
		$login_pin = '';
        for ($i=0;$i<=5;$i++) {
                $login_pin = rand(10000, 99999);
                if ($this->isLoginPINExist($login_pin)) {
                        $login_pin = '';
                } else {
                        break;
                }
                $i++;
        }

        if (empty($login_pin)) return false;
        
        $concatPass = $agent->agent_id . $agent->password;
        $pass_md5 = md5($concatPass);

		$sql = "INSERT INTO agents SET ".
				"agent_id='$agent->agent_id', ".
				"nick='$agent->nick', ".
				"password=SHA2(CONCAT('$agent->agent_id', '$pass_md5'), 256), ".
				"usertype='R', ".
				"name='$agent->name', ".
				"telephone='$agent->telephone', ".
				"did='$agent->did', ".
				"email='$agent->email', ".
				"login_pin='$login_pin', ".
				"active='$agent->active'";
	
		if ($this->getDB()->query($sql)) {
			
			if ($agent->from_time == '0000-00-00 00:00') {
				$agent->start_time = '0000-00-00 00:00:00';
				$agent->stop_time = '0000-00-00 00:00:00';
			} else {
				$agent->start_time = $agent->from_time . ':00';
				$agent->stop_time = date("Y-m-d H:i:s", strtotime($agent->start_time) + $agent->access_duration*60);
			}
			
			$sql = "INSERT INTO agents_access_restrictions SET agent_id='$agent->agent_id', start_time='$agent->start_time', ".
				"stop_time='$agent->stop_time'";
			$this->getDB()->query($sql);
			
			$agent_title = 'Root User';
			$ltxt = $agent->active=='Y' ? 'Active' : 'Inactive';
			$ltxt = "Nick=".$agent->nick.";Pass=*;Status=".$ltxt;
			//$skill_text = $this->getLoggedServices($services, $skill_options);
			//if (!empty($skill_text)) $ltxt .= ";$skill_text";
			$this->addToAuditLog($agent_title, 'A', $agent_title . "=".$agent->agent_id, $ltxt);
	
			return true;
		}
	
		return false;
	}
	
	/*
	function getLoggedServices($services, $skill_options)
	{
		$ltext = '';

		$skills = array();
		if (is_array($skill_options)) {
			foreach ($skill_options as $skill) $skills[$skill->skill_id] = $skill->skill_name;
		}
		if (is_array($services)) {
			foreach ($services as $srv) {
				if (!empty($ltext)) $ltext .= ',';
				$ltext .= isset($skills[$srv]) ? $skills[$srv] : $srv;
			}
		}
		
		return empty($ltext) ? $ltext : 'Skills=' . $ltext;
	}
	*/
	
	function addAgentSkill($agent_id, $services)
	{
		$is_update = false;
		if (is_array($services)) {
			$priority = 0;
			foreach ($services as $srv) {
				$priority++;
				if ($priority <= 9) {
					$sql = "INSERT INTO agent_skill SET skill_id='$srv', agent_id='$agent_id', priority='$priority'";
					if ($this->getDB()->query($sql)) $is_update = true;
				}
			}
		}
		return $is_update;
	}

	function updateAgentStatus($agentid, $status='', $utype='A', $agent_title='')
	{
		if (empty($agentid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE agents SET active='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
			if ($this->getDB()->query($sql)) {

				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "Status=".$ltxt);
				return true;
			}
		}
		return false;
	}

	function updateLoginPIN($agentid, $newpin)
	{
		if (empty($agentid)) return false;		
		$sql = "UPDATE agents SET login_pin='$newpin' WHERE agent_id='$agentid'";
		if ($this->getDB()->query($sql)) {	
			
			$this->addToAuditLog("reset-login-pin", 'U', "agent_id=" . "agin=$agentid");
			return true;
		}
		
		return false;
	}
	

	function updateAgentTBankStatus($agentid, $status='', $utype='A', $agent_title='')
	{
		if (empty($agentid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE agents SET web_password_priv='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
			if ($this->getDB()->query($sql)) {

				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "TeleBank Status=".$ltxt);
				return true;
			}
		}
		return false;
	}
	
	function updateAgent($oldagent, $agent)
	{
		/*GPrint($oldagent);
		GPrint($agent);
		GPrint($_POST);
		die;*/
		if (empty($oldagent->agent_id)) return false;
		$is_update = false;
		$isNotifyNeeded = false;
		$changed_fields = '';
		$ltext = '';
		/*
		"name='$agent->name', ".
			"telephone='$agent->telephone', ".
			"email='$agent->email', ".
			*/

		$condParams = [];
		$result = [];
		if ($agent->nick != $oldagent->nick) {
			$sound_nick = !empty($agent->nick) ? explode(" ", $agent->nick) : "";
			$sound_nick[0] = !empty($sound_nick[0]) ? preg_replace("/[^A-Za-z0-9]/", "", $sound_nick[0]) : "";
			$soundNick = !empty($sound_nick[0]) ? soundex($sound_nick[0]) : "";
			
			$changed_fields .= "nick= ?, nick_sound='$soundNick'";
			$ltext = "Nick=$oldagent->nick to $agent->nick";
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->nick];
		}

		if ($agent->name != $oldagent->name) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "name= ? ";
			$ltext = $this->addAuditText($ltext, "Name=$oldagent->name to $agent->name");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->name, "paramLength" => 35];
		}

		if ($agent->birth_day != $oldagent->birth_day) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "birth_day= ?";
			$ltext = $this->addAuditText($ltext, "Birth day=$oldagent->birth_day to $agent->birth_day");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->birth_day];
		}

		if ($agent->telephone != $oldagent->telephone) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "telephone= ?";
			$ltext = $this->addAuditText($ltext, "Telephone=$oldagent->telephone to $agent->telephone");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->telephone];
		}
		
		if ($agent->did != $oldagent->did) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "did= ?";
			$ltext = $this->addAuditText($ltext, "DID=$oldagent->did to $agent->did");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->did];
        }

		if ($agent->email != $oldagent->email) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "email= ?";
			$ltext = $this->addAuditText($ltext, "Email=$oldagent->email to $agent->email");
			$condParams[] = ["paramType" => "s", "paramLength" => strlen($agent->email), "paramValue" => $agent->email];
		}

		if ($agent->partition_id != $oldagent->partition_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "partition_id= ?";
			$ltext = $this->addAuditText($ltext, "Partition ID=$oldagent->partition_id to $agent->partition_id");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->partition_id];
		}
		
		if ($agent->language_1 != $oldagent->language_1) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "language_1= ?";
			$ltext = $this->addAuditText($ltext, "Language 1=$oldagent->language_1 to $agent->language_1");
			$isNotifyNeeded = true;
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->language_1];
		}
		
		if ($agent->language_2 != $oldagent->language_2) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "language_2= ?";
			$ltext = $this->addAuditText($ltext, "Language 2=$oldagent->language_2 to $agent->language_2");
			$isNotifyNeeded = true;
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->language_2];
		}
		
		if ($agent->language_3 != $oldagent->language_3) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "language_3= ?";
			$ltext = $this->addAuditText($ltext, "Language 3=$oldagent->language_3 to $agent->language_3");
			$isNotifyNeeded = true;
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->language_3];
		}
		
		if ($agent->usertype != $oldagent->usertype) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "usertype= ?";
			$ltext = $this->addAuditText($ltext, "usertype=$oldagent->usertype to $agent->usertype");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->usertype];
		}

		if ($agent->altid != $oldagent->altid) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "altid= ?";
			$ltext = $this->addAuditText($ltext, "Alt. ID=$oldagent->altid to $agent->altid");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->altid];
		}
		
		if (!empty($agent->password)) {
			//$pass_md5 = $agent->password;
			$concatPass = $oldagent->agent_id . $agent->password;
			$pass_md5 = md5($concatPass);
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "password=SHA2(CONCAT(agent_id, '$pass_md5'), 256)";
			$ltext = $this->addAuditText($ltext, "Pass=*");
		}
		if (isset($agent->web_password) && !empty($agent->web_password)) {
			$pass_md5 = md5($agent->web_password);
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "web_password='$pass_md5'";
			$ltext = $this->addAuditText($ltext, "TeleBank Pass=*");
		}
		
		if ($agent->max_chat_session != $oldagent->max_chat_session) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "max_chat_session= ?";
			$ltext = $this->addAuditText($ltext, "max chat session=$oldagent->max_chat_session to $agent->max_chat_session");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->max_chat_session];
		}
		
		if ($agent->chat_session_limit_with_call != $oldagent->chat_session_limit_with_call) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "chat_session_limit_with_call= ?";
			$ltext = $this->addAuditText($ltext, "chat_session_limit_with_call=$oldagent->chat_session_limit_with_call to $agent->chat_session_limit_with_call");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->chat_session_limit_with_call];
		}

		if ($agent->role_id != $oldagent->role_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "role_id= ?";
			$ltext = $this->addAuditText($ltext, "role_id=$oldagent->role_id to $agent->role_id");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->role_id];
		}

		if ($agent->supervisor_id != $oldagent->supervisor_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "supervisor_id= ?";
			$ltext = $this->addAuditText($ltext, "supervisor_id=$oldagent->supervisor_id to $agent->supervisor_id");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->supervisor_id];
		}

		if ($agent->screen_logger != $oldagent->screen_logger) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "screen_logger= ?";
			$ltext = $this->addAuditText($ltext, "screen_logger=$oldagent->screen_logger to $agent->screen_logger");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->screen_logger];
		}


		if ($agent->ob_call != $oldagent->ob_call) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "ob_call= ?";
			$ltext = $this->addAuditText($ltext, "ob_call=$oldagent->ob_call to $agent->ob_call");
			$condParams[] = ["paramType" => "s", "paramValue" => $agent->ob_call];
		}

		// echo "<pre>";
		// Gprint($changed_fields);
		if (!empty($changed_fields)) {
			$sql = "UPDATE agents SET $changed_fields WHERE agent_id= ? ";
			$condParams[] = ["paramType" => "s", "paramValue" => $oldagent->agent_id];

			$validate = $this->getDB()->validateQueryParams($condParams); 
			// Gprint($validate);
			// Gprint($condParams);
			// Gprint($sql);
			// die();
			if($validate['result'] == true){
				$is_update = $this->getDB()->executeInsertQuery($sql, $validate['paramTypes'], $validate['bindParams']);
				// var_dump($is_update);
			}
		}
		/*
		if ($agent->agentskills != $oldagent->agentskills) {
			$sql = "DELETE FROM agent_skill WHERE agent_id='$oldagent->agent_id'";
			if ($this->getDB()->query($sql)) $is_update = true;
			if ($this->addAgentSkill($oldagent->agent_id, $services))  {
				$is_update = true;

				$skill_text = $this->getLoggedServices($services, $skill_options);
				if (!empty($skill_text)) $ltext = $this->addAuditText($ltext, $skill_text);
			}
		}
		*/

		if ($is_update) {
            $this->synchronizeSeatWithAgent($oldagent->agent_id, $agent->screen_logger, $agent->ob_call);
			//$agent_title = $oldagent->usertype == 'S' ? 'Supervisor' : 'Agent';
			if ($oldagent->usertype == 'S') {
				$agent_title = 'Supervisor';
			} else if ($oldagent->usertype == 'D') {
				$agent_title = 'Dashboard User';
			} else if ($oldagent->usertype == 'P') {
				$agent_title = 'Report User';
			} else if ($oldagent->usertype == 'G') {
				$agent_title = 'Digicon Report User';
			} else {
				$agent_title = 'Agent';
			}

			if ($isNotifyNeeded) {
				$this->notifyAgentUpdate('UPD_AGENT', $oldagent->agent_id);
			}
			
			$this->addToAuditLog($agent_title, 'U', $agent_title . "=".$oldagent->agent_id, $ltext);
		}
		
		return $is_update;
	}

	private function synchronizeSeatWithAgent($agent_id, $screen_logger, $outbound_call){
	    $this->getDB()->query("UPDATE seat SET screen_logger='{$screen_logger}', ob_call='{$outbound_call}' WHERE  agent_id='{$agent_id}' LIMIT 1");
    }

	function updateRootAgent($oldagent, $agent)
	{
		//echo 'asd';var_dump($oldagent);exit;
		if (empty($oldagent->agent_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		/*
			"name='$agent->name', ".
			"telephone='$agent->telephone', ".
			"email='$agent->email', ".
			*/
		if ($agent->nick != $oldagent->nick) {
			$changed_fields .= "nick='$agent->nick'";
			$ltext = "Nick=$oldagent->nick to $agent->nick";
		}
	
		if ($agent->name != $oldagent->name) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "name='$agent->name'";
			$ltext = $this->addAuditText($ltext, "Name=$oldagent->name to $agent->name");
		}
	
		if ($agent->telephone != $oldagent->telephone) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "telephone='$agent->telephone'";
			$ltext = $this->addAuditText($ltext, "Telephone=$oldagent->telephone to $agent->telephone");
		}

		if ($agent->did != $oldagent->did) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "did='$agent->did'";
			$ltext = $this->addAuditText($ltext, "DID=$oldagent->did to $agent->did");
		}
	
		if ($agent->email != $oldagent->email) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "email='$agent->email'";
			$ltext = $this->addAuditText($ltext, "Email=$oldagent->email to $agent->email");
		}
	
		if (!empty($agent->password)) {
		    //$pass_md5 = $agent->password;
		    $concatPass = $oldagent->agent_id . $agent->password;
		    $pass_md5 = md5($concatPass);
		    if (!empty($changed_fields)) $changed_fields .= ', ';
		    $changed_fields .= "password=SHA2(CONCAT(agent_id, '$pass_md5'), 256)";
		    $ltext = $this->addAuditText($ltext, "Pass=*");
		}
		if (isset($agent->web_password) && !empty($agent->web_password)) {
			$pass_md5 = md5($agent->web_password);
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "web_password='$pass_md5'";
			$ltext = $this->addAuditText($ltext, "TeleBank Pass=*");
		}
	
		if (!empty($changed_fields)) {
			$sql = "UPDATE agents SET $changed_fields WHERE agent_id='$oldagent->agent_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($agent->from_time == '0000-00-00 00:00') {
			$agent->start_time = '0000-00-00 00:00:00';
			$agent->stop_time = '0000-00-00 00:00:00';
		} else {
			$agent->start_time = $agent->from_time . ':00';
			$agent->stop_time = date("Y-m-d H:i:s", strtotime($agent->start_time) + $agent->access_duration*60);
		}
		
		$sql = "SELECT * FROM agents_access_restrictions WHERE agent_id='$agent->agent_id'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$sql = "UPDATE agents_access_restrictions SET start_time='$agent->start_time', stop_time='$agent->stop_time' WHERE ".
				"agent_id='$oldagent->agent_id'";
		} else {
			$sql = "INSERT INTO agents_access_restrictions SET agent_id='$oldagent->agent_id', start_time='$agent->start_time', ".
				"stop_time='$agent->stop_time'";
		}
		if ( $this->getDB()->query($sql) ) $is_update = true;
		/*
			if ($agent->agentskills != $oldagent->agentskills) {
			$sql = "DELETE FROM agent_skill WHERE agent_id='$oldagent->agent_id'";
			if ($this->getDB()->query($sql)) $is_update = true;
			if ($this->addAgentSkill($oldagent->agent_id, $services))  {
			$is_update = true;
	
			$skill_text = $this->getLoggedServices($services, $skill_options);
			if (!empty($skill_text)) $ltext = $this->addAuditText($ltext, $skill_text);
			}
			}
			*/
	
		if ($is_update) {
			//$agent_title = $oldagent->usertype == 'S' ? 'Supervisor' : 'Agent';
			$agent_title = 'Root user';
	
			$this->addToAuditLog($agent_title, 'U', $agent_title . "=".$oldagent->agent_id, $ltext);
		}
	
		return $is_update;
	}
	
	function deleteAgent($agentid, $usertype)
	{
		$agentinfo = $this->getAgentById($agentid); 
		if (!empty($agentinfo)) {
			$sql = "DELETE FROM agents WHERE agent_id='$agentid' AND usertype='$usertype'";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM agent_skill WHERE agent_id='$agentid'";
				$this->getDB()->query($sql);
				
				//$usertype = $agentinfo->usertype == 'S' ? 'Supervisor' : 'Agent';
				if ($agentinfo->usertype == 'S') {
					$usertype = 'Supervisor';
				} else if ($agentinfo->usertype == 'D') {
					$usertype = 'Dashboard User';
				} else {
					$usertype = 'Agent';
				}

				$this->addToAuditLog($usertype, 'D', $usertype . "=$agentid", "Name=".$agentinfo->nick);
				return true;
			}
		}
		return false;
	}

	function setAgentPassword($uid, $pass)
	{
		if (empty($uid) || empty($pass)) return false;
		
		$concatPass = $uid . $pass;
		$concatPass = md5($concatPass);
		$sql = "INSERT INTO history_password SET pass_date='".date("Y-m-d H:i:s")."', agent_id='$uid', password=SHA2(CONCAT('$uid', '$concatPass'), 256)";
		$this->getDB()->query($sql);
		$sql = "UPDATE agents SET password=SHA2(CONCAT(agent_id, '$concatPass'), 256) WHERE agent_id='$uid'";
		return $this->getDB()->query($sql);
	}

	function getLicenseInfo()
	{
		$licenseObj = new stdClass();
		$licenseObj->email_module = 'N';
		$licenseObj->chat_module = 'N';
		$licenseObj->vm_module = 'N';
		
		$sql = "SELECT vm_module, email_module, chat_module FROM settings LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (!empty($result) && is_array($result)) {
			$dataObject = $result[0];
			$licenseObj->email_module = $result[0]->email_module;
			$licenseObj->chat_module = $result[0]->chat_module;
			$licenseObj->vm_module = $result[0]->vm_module;
		}
		return $licenseObj;
	}

	public function get_as_key_value($id="")
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $agent_list["*"] = "All";
        $sql = "SELECT agent_id,nick, name FROM agents ";
        $sql .= !empty($partition_id) ? " WHERE partition_id = '{$partition_id}' " : "";
        if (!empty($id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND agent_id='{$id}' " : " WHERE agent_id='{$id}' ";
        }
        $sql .= "ORDER BY nick ";

        $agents = $this->getDB()->query($sql);
        if (empty($agents))
        {
            return [];
        }


        foreach ($agents as $agent)
        {
            // $agent_list["{$agent->agent_id}"] = $agent->nick;
            $agent_list["{$agent->agent_id}"] = $agent->agent_id.' - '.$agent->name;
        }

        return $agent_list;
    }

    public function get_as_key_value_for_ticket($id="")
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $agent_list["*"] = "All";
        $sql = "SELECT agent_id,nick,name FROM agents ";
        $sql .= !empty($partition_id) ? " WHERE partition_id = '{$partition_id}' " : "";
        if (!empty($id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND agent_id='{$id}' " : " WHERE agent_id='{$id}' ";
        }
        $sql .= "ORDER BY name ";

        $agents = $this->getDB()->query($sql);
        if (empty($agents))
        {
            return [];
        }


        foreach ($agents as $agent)
        {
            $agent_list["{$agent->agent_id}"] = $agent->name;
        }

        return $agent_list;
    }

    public function get_shifts($key='')
    {
        $sql = "SELECT * FROM shift_profile ";
        $sql .= !empty($key) ? " WHERE shift_code = '{$key}' AND day_overlap = 0 " : "WHERE day_overlap = 0 ";

        $shifts = $this->getDB()->query($sql);
        return empty($shifts) ? [] : $shifts;
    }

    public function agent_session_exists($agent_id, $shift_code,$date)
    {
        $query = "SELECT COUNT(*) AS total_agent_session FROM rt_agent_shift_summary WHERE sdate='{$date}' AND shift_code='{$shift_code}' AND agent_id='{$agent_id}' ";
        $result =  $this->getDB()->query($query);
        return $result[0]->total_agent_session > 0 ? TRUE : FALSE;
    }

    public function save_agent_session($agent_id, $shift_code,$date,$shift_start,$is_regular_shift)
    {
        $session_id = $agent_id.substr(time(), 1,9);
        $query = "INSERT INTO rt_agent_shift_summary (session_id,sdate,shift_code,agent_id,shift_start,is_regular_shift) VALUES('{$session_id}','{$date}','{$shift_code}','{$agent_id}','{$shift_start}','{$is_regular_shift}')";
        return $this->getDB()->query($query);
    }

    public function numShiftProfile($overlap){
        $table = 'shift_profile';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
        $sql .= $overlap!='*'?" WHERE day_overlap ='{$overlap}' ":"";

        $record = $this->getDB()->query($sql);
        return $record[0]->total_record;
    }
    public function getShiftProfile($overlap='',$offset=0,$limit=20){
        $table = 'shift_profile';
        $sql = "SELECT * FROM {$table}";
        $sql .= $overlap!='*'?" WHERE day_overlap ='{$overlap}' ":"";
        if ($limit > 0) $sql .= " ORDER BY shift_code LIMIT $limit OFFSET $offset";
        return $this->getDB()->query($sql);
    }

    public function shift_profile_exists($shift_code)
    {
        $query = "SELECT COUNT(*) AS total_shift_profile FROM shift_profile WHERE shift_code='{$shift_code}'";
        $result =  $this->getDB()->query($query);
        return $result[0]->total_shift_profile > 0 ? TRUE : FALSE;
    }

    public function save_shift_profile($shift_profile)
    {
        $query = "INSERT INTO shift_profile (shift_code,label,start_time,end_time,shift_duration,early_login_cutoff_time,late_login_cutoff_time,tardy_cutoff_sec,early_leave_cutoff_sec,day_overlap) VALUES('{$shift_profile->shift_code}','{$shift_profile->label}','{$shift_profile->start_time}','{$shift_profile->end_time}','$shift_profile->shift_duration}','{$shift_profile->early_login_cutoff_time}', '{$shift_profile->late_login_cutoff_time}', '{$shift_profile->tardy_cutoff_sec}', '{$shift_profile->early_leave_cutoff_sec}', '{$shift_profile->day_overlap}')";
        return $this->getDB()->query($query);
    }

    public function updateShiftProfile($profile)
    {
        $profile->day_overlap = !empty($profile->day_overlap)?$profile->day_overlap :"0";
        $query = "UPDATE shift_profile SET label='{$profile->label}', start_time = '{$profile->start_time}',end_time='{$profile->end_time}', shift_duration='{$profile->shift_duration}', early_login_cutoff_time='{$profile->early_login_cutoff_time}',late_login_cutoff_time='{$profile->late_login_cutoff_time}',tardy_cutoff_sec='{$profile->tardy_cutoff_sec}',early_leave_cutoff_sec='{$profile->early_leave_cutoff_sec}' WHERE shift_code='{$profile->shift_code}' AND day_overlap ='{$profile->day_overlap}' LIMIT 1";
        //var_dump($query);die;
        return $this->getDB()->query($query);
    }

    public function getShiftProfileById($shift_code,$over_lap="0")
    {
        //$query = "select * from shift_profile WHERE shift_code='{$shift_code}' AND day_overlap='{$over_lap}' LIMIT 1";
        $query = "select * from shift_profile WHERE shift_code='{$shift_code}'";

        return $this->getDB()->query($query);
        //$response =  $this->getDB()->query($query);
        //return $response[0];
    }

    public function deleteShiftProfile($shift_code){
        $query = "SELECT count(*) AS lmt FROM shift_profile WHERE shift_code = '{$shift_code}'";
        $data = $this->getDB()->query($query);
        $limit = $data[0]->lmt;
        if ($limit > 0){
            $sql = "DELETE FROM shift_profile WHERE shift_code='$shift_code' LIMIT $limit";
            return $this->getDB()->query($sql);
        }
        return false;
    }

    public function numCallQualityProfile()
    {
        $record = $this->getDB()->query("SELECT COUNT(*) AS total_record FROM rating_profile");
        return !empty($record[0]->total_record) ? $record[0]->total_record : 0;
    }

    public function getCallQualityProfile($offset=0,$limit=20)
    {
        return $this->getDB()->query("SELECT * FROM rating_profile ORDER BY rating_id ASC LIMIT $limit OFFSET $offset");
    }

    public function changeCallQualityProfileStatus($id=0,$status='')
    {
        return $this->getDB()->query("UPDATE rating_profile SET status='{$status}' WHERE rating_id='{$id}' LIMIT 1");
    }

    public function getActiveCallQualityProfiles()
    {
        return $this->getDB()->query("SELECT * FROM rating_profile WHERE status = 'Y' ");
    }
    public function getCallQualityProfileById($id)
    {
        return $this->getDB()->query("SELECT * FROM rating_profile WHERE rating_id = '{$id}' ");
    }

    public function addCallQualityProfile($label='',$status='N')
    {
        $id = $this->getCallRatingProfileID();
        if (empty($id) || empty($label)) return FALSE;

       $query = "INSERT INTO rating_profile (rating_id,label,status) VALUES('{$id}','{$label}','{$status}')";
       return $this->getDB()->query($query);
    }

    public function updateCallQualityProfile($id, $label='',$status='N')
    {
        if (empty($id) || empty($label)) return FALSE;

        $query = "UPDATE rating_profile SET label='{$label}',status='{$status}' WHERE rating_id='{$id}' LIMIT 1";
        return $this->getDB()->query($query);
    }

    private function getCallRatingProfileID()
    {
        $id = $this->getDB()->query("SELECT MAX(rating_id) as rating_profile_id FROM rating_profile");
        $id = $id[0]->rating_profile_id;
        if($id){
            $id++;
        }else{
            $id = 1;
        }
        return $id <= 9 ? $id : NULL;
    }


    public function AddCallQualityRating($callid,$call_time,$skill_id,$agent_id,$ratings)
    {
        $rate_time = date('Y-m-d H:i:s');
        $current_user = UserAuth::getUserID();

        $exist_check_sql = "SELECT COUNT(callid) AS service_quality_count FROM service_quality_rating WHERE callid='{$callid}' ";
        $response = $this->getDB()->query($exist_check_sql);
        $response = $response[0]->service_quality_count;

        $sql = $response > 0 ?  "UPDATE service_quality_rating " : "INSERT INTO service_quality_rating ";
        $sql .= " SET call_time='{$call_time}', agent_id='{$agent_id}', skill_id='{$skill_id}', rate_time='{$rate_time}', rated_by='{$current_user}' ";

        foreach ($ratings as $key => $rating)
        {
            $sql .= ", $key='{$rating}' ";
        }
        $sql .= $response > 0 ? " WHERE callid='{$callid}' LIMIT 1 " : " ,callid='{$callid}' ";

        return $this->getDB()->query($sql);
    }

    public function getServiceQualityRatingByCallID($callid)
    {
        $response = $this->getDB()->query("SELECT * FROM service_quality_rating WHERE callid='{$callid}' LIMIT 1");
        return !empty($response[0]) ? $response[0] : NULL;
    }

    public function getAllAgents($agent_id_array){
        $sql = "SELECT agent_id,name FROM agents WHERE agent_id IN ('".implode("','",$agent_id_array)."') ";
        return $this->getDB()->query($sql);
    }

    public function saveDisposition($call_id, $dispositions=[], $cli, $notes, $direction)
    {
        $response = new stdClass();
        $response->status = false;
        $response->message = 'Failed to Save Disposition.';

        $tstamp = time();
        $isSaved = false;
        $log_date = date("Y-m-d");
        $agent_id = UserAuth::getUserID();
        $dispositions = array_unique(array_filter($dispositions));
        $disposition_count = count($dispositions);

        if ( in_array($direction, ["OUT", "PD"])){
            return $this->saveOutboundDisposition($call_id, $cli, $direction, $dispositions, $notes);
        }

        $row = $this->getDB()->queryOnUpdateDB("SELECT * FROM log_skill_inbound WHERE callid= '{$call_id}'");

        if (!empty($row[0]->status) && $row[0]->status == 'A'){
            $response->message = "Disposition not allowed for an abandoned call.";
            return $response;
        }

        if (!empty($row[0]->disposition_id)){
            $response->message = "Already saved disposition for this call. Can't save anymore.";
            return $response;
        }

        $temp_disposition = $this->getDB()->queryOnUpdateDB("SELECT * FROM tmp_skill_disposition WHERE callid='{$call_id}' LIMIT 1");
        if (!empty($temp_disposition[0])){
            $response->message = "Already saved disposition for this call. Can't save anymore.";
            return $response;
        }

        $wrap_up_time = $this->getInboundWrapUpTime($call_id);

        if (empty($row)){
            $sql = "INSERT INTO tmp_skill_disposition(callid, agent_id, disposition_id, disposition_count) VALUES('{$call_id}', '{$agent_id}','{$dispositions[0]}', {$disposition_count}) ";
            $isSaved = $this->getDB()->query($sql);

            if ($isSaved){
	            $sql = "INSERT INTO skill_crm_disposition_log(callid,cli,disposition_id, tstamp,agent_id, wrap_up_time, note, log_date) VALUES";

	            foreach ($dispositions as $index => $disposition){
	                if ($disposition){
	                    $sql .= " ('{$call_id}', '{$cli}', '{$disposition}', '{$tstamp}', '{$agent_id}', '{$wrap_up_time}', '{$notes[$index]}', '{$log_date}'),";
	                }
	            }
                $isSaved =  $this->getDB()->query(rtrim($sql,','));
	        }
        }elseif(!empty($row[0]) && empty($row[0]->disposition_id)){
            $sql = "INSERT INTO skill_crm_disposition_log(callid,cli,disposition_id, tstamp,agent_id, wrap_up_time, note, log_date) VALUES";
            foreach ($dispositions as $index => $disposition){
                if ($disposition){
                    $sql .= " ('{$call_id}', '{$cli}', '{$disposition}', '{$tstamp}', '{$agent_id}', '{$wrap_up_time}', '{$notes[$index]}', '{$log_date}'),";
                }
            }
            $isSaved =  $this->getDB()->query(rtrim($sql,','));

            if ($isSaved){
	            $sql = "UPDATE log_skill_inbound SET disposition_id='{$dispositions[0]}', disposition_count='{$disposition_count}', ";
	            $sql .= " wrap_up_time = '{$wrap_up_time}' WHERE callid = '{$call_id}' LIMIT 1";
	            $this->getDB()->query($sql);
        	}
        }

        $response->status = $isSaved == true;
        $response->message = $isSaved ? "Disposition Saved Successfully." : "Failed to Save Disposition.";
        return $response;
    }

    protected function saveOutboundDisposition($call_id, $cli, $direction, $dispositions = [],  $notes = [])
    {
        $response = new stdClass();
        $response->status = false;
        $response->message = 'Failed to Save Disposition.';

        $tstamp = time();
        $log_date = date("Y-m-d");
        $agent_id = UserAuth::getUserID();
        $disposition_count = count($dispositions);

	$temp_disposition = $this->getDB()->queryOnUpdateDB("SELECT * FROM tmp_skill_disposition WHERE callid='{$call_id}' LIMIT 1");
        if (!empty($temp_disposition[0])){
            $response->message = "Already saved disposition for this call. Can't save anymore.";
            return $response;
        }

        $row = $this->getDB()->queryOnUpdateDB("SELECT * FROM log_agent_outbound_manual WHERE callid= '{$call_id}' limit 1");
        if (!empty($row[0]->disposition_id)){
            $response->message = "Already saved disposition for this call. Can't save anymore.";
            return $response;
        }

        

        $wrap_up_time = $direction == "PD" ? $this->getInboundWrapUpTime($call_id) : $this->getOutboundWrapUpTime($call_id);

        if (empty($row)){
            $sql = "INSERT INTO tmp_skill_disposition(callid, agent_id, disposition_id, disposition_count) VALUES('{$call_id}', '{$agent_id}','{$dispositions[0]}', {$disposition_count}) ";
            $isSaved = $this->getDB()->query($sql);

            if ($isSaved){
                $sql = "INSERT INTO skill_crm_disposition_log(callid,cli,disposition_id, tstamp,agent_id, wrap_up_time, note, log_date) VALUES";

                foreach ($dispositions as $index => $disposition){
                    if ($disposition){
                        $sql .= " ('{$call_id}', '{$cli}', '{$disposition}', '{$tstamp}', '{$agent_id}', '{$wrap_up_time}', '{$notes[$index]}', '{$log_date}'),";
                    }
                }
                $isSaved =  $this->getDB()->query(rtrim($sql,','));
            }
        }elseif(!empty($row[0]) && empty($row[0]->disposition_id)){
            $sql = "INSERT INTO skill_crm_disposition_log(callid,cli,disposition_id, tstamp,agent_id, wrap_up_time, note, log_date) VALUES";
            foreach ($dispositions as $index => $disposition){
                if ($disposition){
                    $sql .= " ('{$call_id}', '{$cli}', '{$disposition}', '{$tstamp}', '{$agent_id}', '{$wrap_up_time}', '{$notes[$index]}', '{$log_date}'),";
                }
            }
            $isSaved =  $this->getDB()->query(rtrim($sql,','));

            if ($isSaved){
                $sql = "UPDATE log_agent_outbound_manual SET disposition_id='{$dispositions[0]}', disposition_count='{$disposition_count}', ";
                $sql .= " wrap_up_time = '{$wrap_up_time}' WHERE callid = '{$call_id}' LIMIT 1";
                $this->getDB()->query($sql);
            }
        }

        $response->status = $isSaved == true;
        $response->message = $isSaved ? "Disposition Saved Successfully." : "Failed to Save Disposition.";
        return $response;
    }

    protected function getOutboundWrapUpTime($call_id)
    {
        $query = "SELECT IF(TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(tstamp), NOW())> skill.delay_between_calls, skill.delay_between_calls, ";
        $query .= " TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(tstamp), NOW())) AS wrap_up_time FROM agent_outbound_manual_log  INNER JOIN skill ON ";
        $query .= " agent_outbound_manual_log.skill_id = skill.skill_id WHERE agent_outbound_manual_log.callid ='{$call_id}' limit 1";

        $wrap_up = $this->getDB()->queryOnUpdateDB($query);

        return !empty($wrap_up[0]->wrap_up_time) ? $wrap_up[0]->wrap_up_time : 0;
    }

    protected function getInboundWrapUpTime($call_id)
    {
        $query = "SELECT IF(TIMESTAMPDIFF(SECOND, tstamp, NOW())> skill.delay_between_calls, skill.delay_between_calls, ";
        $query .= " TIMESTAMPDIFF(SECOND, tstamp, NOW())) AS wrap_up_time FROM log_skill_inbound  INNER JOIN skill ON ";
        $query .= " log_skill_inbound.skill_id = skill.skill_id WHERE log_skill_inbound.callid ='{$call_id}' limit 1";
        $wrap_up = $this->getDB()->queryOnUpdateDB($query);

        return !empty($wrap_up[0]->wrap_up_time) ? $wrap_up[0]->wrap_up_time : 0;
    }



    public function getAgentsWithSupervisorName($supervisor_id='', $only_agents = true)
    {
        $sql  = "SELECT agents.agent_id, agents.name, agents.nick, agents.supervisor_id , ";
        $sql .= " s.`name` as supervisor_name, s.nick as supervisor_nick, '' as skill, sq.skill_id AS skill_id,skill.skill_name, ";
        $sql .= " 'Not Ready' as status,  '' AS inbound_number, '' as outbound_number, 0 as time_in_state FROM agents ";
        $sql .= " INNER JOIN agent_skill as sq ON agents.agent_id = sq.agent_id ";
        $sql .= " INNER JOIN skill ON sq.skill_id = skill.skill_id ";
        $sql .= " LEFT JOIN agents as s ON agents.supervisor_id = s.agent_id  WHERE agents.agent_id != 'root' ";
        $sql .= $only_agents ? " AND agents.usertype = 'A' " : "";
        $sql .= !empty($supervisor_id) ? " AND agents.supervisor_id = {$supervisor_id} " : "";
        $sql .= " ORDER BY agent_id, nick";

        return $this->getDB()->query($sql);

    }

    public function getPdSkillEngine()
    {
        $agent_id = UserAuth::getCurrentUser();
        return $this->getDB()->query("SELECT skill_id, dial_engine FROM pd_profile WHERE skill_id IN (SELECT skill_id FROM agent_skill where agent_id='{$agent_id}')");
    }

    public function getAgentDetails($agent_id = '')
    {
        $formated_response = new stdClass();
        $formated_response->skills = [];
        $query  = "SELECT ag.agent_id,sk.skill_name,ag.language_1, ag.language_2,ag.language_3, ag.nick, su.agent_id AS supervisor_id, ";
        $query .= " su.nick AS supervisor_name FROM agents ag inner join agent_skill aq ON ag.agent_id = aq.agent_id inner join skill sk ";
        $query .= " ON aq.skill_id = sk.skill_id LEFT JOIN agents AS su ON  ag.supervisor_id = su.agent_id WHERE ag.agent_id = '{$agent_id}'";

        $response = $this->getDB()->query($query);

        if (empty($response)){
            return [];
        }

        foreach ($response as $row){
            $formated_response->agent_id = $row->agent_id;
            $formated_response->name = $row->name;
            $formated_response->nick = $row->nick;
            $formated_response->supervisor_id = $row->supervisor_id;
            $formated_response->supervisor_name = $row->supervisor_name;
            $formated_response->skills[] = $row->skill_name;
            $formated_response->language = [$row->lagnuage_1, $row->language_2, $row->language_3];
        }

        return $formated_response;
    }

    function getAgentNameById($agent_id){
        $sql = "SELECT nick FROM agents WHERE agent_id='$agent_id'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0];
        }
        return null;
    }

    public function getAgentsSessionId()
    {
        $agent_id = UserAuth::getCurrentUser();
        $response = $this->getDB()->query("SELECT session_id FROM agents WHERE agent_id='{$agent_id}' LIMIT 1");

        return !empty($response) ? $response[0]->session_id : null;
    }
    /*
     * Only for agent session summary in dialer
     */
    public function getAgentSessionSummary()
    {
        $session_id = $this->getAgentsSessionId();
        $sql = "SELECT agent_id,first_login,shift_code,login_count,staff_time,wrap_up_time,total_break_time,total_aux_in_time,total_aux_out_time FROM rt_agent_shift_summary WHERE session_id='{$session_id}' ORDER BY sdate DESC LIMIT 1";
        return $this->getDB()->query($sql);
    }

    public function getAgentNameKeyValue($id="")
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $agent_list["*"] = "All";
        $sql = "SELECT ag.agent_id, ag.name FROM skill s 
                INNER JOIN agent_skill ags ON s.skill_id = ags.skill_id
                INNER JOIN agents ag ON ags.agent_id = ag.agent_id
                WHERE s.qtype = 'C' ";
        $sql .= !empty($partition_id) ? " AND WHERE ag.partition_id = '{$partition_id}' " : "";
        $agents = $this->getDB()->query($sql);

        if (empty($agents)) {
            return [];
        }
        foreach ($agents as $agent) {
            $agent_list["{$agent->agent_id}"] = $agent->name;
        }
        return $agent_list;
    }

    /**
     * @return array
     */
    public function getAgentSkillWithNameAsKeyValue()
    {
        $skills = [];
        $agent_id = UserAuth::getCurrentUser();

        $query = "SELECT ags.skill_id, s.skill_name FROM agent_skill AS ags ";
        $query .= " INNER JOIN skill AS s ON ags.skill_id = s.skill_id ";
        $query .= " WHERE ags.agent_id = '{$agent_id}' AND s.active='Y' and s.qtype IN('V','P') ";

        $response = $this->getDB()->query($query);

        if (!empty($response)){
            foreach ($response as $skill_id => $skill_name){
                $skills[$skill_id] = $skill_name;
            }
        }

        return $skills;
    }

    public function getAgentsFullName()
    {
        $sql = "SELECT agent_id, nick, name FROM agents ";
        $sql .= "ORDER BY agent_id ";

        $result = $this->getDB()->query($sql);

        $names = array();
        if (is_array($result)) {
            foreach ($result as $row) {
                $names[$row->agent_id] = !empty($row->name) ? $row->name : $row->agent_id;
            }
        }
        return $names;
    }

    function getAgentsFromRole($role_id, $offset = 0, $limit = 0)
    {
        $sql = "SELECT * FROM agents WHERE role_id = '$role_id' ";

        $sql .= "ORDER BY agent_id ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);
        return $result;
    }

    function getAgentsFromName($name, $offset = 0, $limit = 0)
    {
        $sql = "SELECT * FROM agents WHERE `name` LIKE '%$name%' ";

        $sql .= "ORDER BY agent_id ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);
        return $result;
    }

    function addAgentSkills($agentid, $priority, $skills, $agent_details) {
		$is_update = false;
		$count = 0;

		if (is_array($skills)) {
			foreach ($skills as $skill) {
				$skill_id = trim($skill);
				$sql = "INSERT INTO agent_skill SET skill_id='$skill_id', agent_id='$agentid', priority='$priority' ON DUPLICATE KEY UPDATE priority='$priority'";
				// GPrint($sql);

				if ($this->getDB()->query($sql)) {
					$is_update = true;
					$count++;
				}
				// GPrint($is_update);
				// GPrint($count);
			}
		}

		if ($count > 0) {
			$audit_text = $count == 1 ? $count . ' skill ' : $count . ' skills ';
			$audit_text .= ' added in the agent';
			$this->addToAuditLog('Agent', 'A', "Agent=".$agent_details->name, $audit_text);
		}
		return $is_update;
	}

	function removeAgentSkills($agentid, $skills0, $skills1, $skills2, $skills3,$skills4, $skills5, $skills6, $skills7,$skills8, $skills9, $skills10, $agentdetails)
	{
		$del_cond = "";
		$del_cond0 = "";
		$del_cond1 = "";
		$del_cond2 = "";
        $del_cond3 = "";
		$del_cond4 = "";
		$del_cond5 = "";
		$del_cond6 = "";
		$del_cond7 = "";
		$del_cond8 = "";
		$del_cond9 = "";
		$del_cond10 = "";

		if (count($skills0) > 0) { $del_cond0 .= implode("','", $skills0); }
		if (count($skills1) > 0) { $del_cond1 .= implode("','", $skills1); }
		if (count($skills2) > 0) $del_cond2 .= implode("','", $skills2);
		if (count($skills3) > 0) $del_cond3 .= implode("','", $skills3);
		if (count($skills4) > 0) $del_cond4 .= implode("','", $skills4);
		if (count($skills5) > 0) $del_cond5 .= implode("','", $skills5);
		if (count($skills6) > 0) $del_cond6 .= implode("','", $skills6);
		if (count($skills7) > 0) $del_cond7 .= implode("','", $skills7);
		if (count($skills8) > 0) $del_cond8 .= implode("','", $skills8);
		if (count($skills9) > 0) $del_cond9 .= implode("','", $skills9);
		if (count($skills10) > 0) $del_cond10 .= implode("','", $skills10);

		if (!empty($del_cond1)) $del_cond .= "'" . $del_cond1 . "'";
		if (!empty($del_cond2)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond2 . "'";
		}
		if (!empty($del_cond3)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond3 . "'";
		}
		if (!empty($del_cond4)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond4 . "'";
		}
		if (!empty($del_cond5)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond5 . "'";
		}
		if (!empty($del_cond6)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond6 . "'";
		}
		if (!empty($del_cond7)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond7 . "'";
		}
		if (!empty($del_cond8)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond8 . "'";
		}
		if (!empty($del_cond9)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond9 . "'";
		}
		if (!empty($del_cond0)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond0 . "'";
		}
		if (!empty($del_cond10)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond10 . "'";
		}
		
		if (empty($del_cond)) $del_cond = "''";
		
		$sql = "DELETE FROM agent_skill WHERE agent_id='$agentid' AND skill_id NOT IN ($del_cond)";
		$resp = $this->getDB()->query($sql);
		if ($resp) {
			$num = $this->getDB()->getAffectedRows();
			$audit_text = $num == 1 ? $num . ' skill ' : $num . ' skills ';
			$audit_text .= ' removed from agent';
			$this->addToAuditLog('Agent', 'D', "Agent=".$agentdetails->name, $audit_text);
		}
		return $resp;
	}

    public function get_lms_info($cli)
    {
        $sql = "SELECT subs_type AS Connection, hvc_segment AS 'HVC Segment', ";
        $sql .= " IF(priority='', 3, priority)  AS Priority, ";
        $sql .= " category AS Category, subs_account_type AS 'Subscriber type'  ";
        $sql .= " FROM robi_lms_category WHERE cli = '{$cli}' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    public function get_last_month_voice_disposition($cli)
    {
        $sql = "SELECT 'Voice Call' AS module, unix_timestamp(lsi.tstamp) AS tstamp, lsi.agent_id, lsi.disposition_id, scdc.title ";
        $sql .= " FROM log_skill_inbound  lsi ";
        $sql .= " INNER JOIN log_customer_journey lcj ON lcj.journey_id = lsi.callid ";
        $sql .= " INNER JOIN skill_crm_disposition_code scdc ON lsi.disposition_id = scdc.disposition_id ";
        $sql .= " WHERE lcj.customer_id = '{$cli}' AND lcj.module_type IN('AC', 'PD') AND lsi.disposition_id != '' ";
        $sql .= " ORDER BY tstamp DESC LIMIT 5 ";
        return $this->getDB()->query($sql);
    }

    public function get_last_month_email_disposition($cli)
    {
        $sql = "SELECT 'Email' AS module, les.create_time as tstamp, les.agent_1 AS agent_id, les.disposition_id, ";
        $sql .= " edc.title FROM log_email_session les ";
        $sql .= " INNER JOIN log_customer_journey lcj ON lcj.journey_id = les.session_id ";
        $sql .= " INNER JOIN email_disposition_code edc ON cdl.disposition_id = edc.disposition_id ";
        $sql .= " WHERE lcj.customer_id = '{$cli}' AND lcj.module_type = 'EM' AND les.disposition_id != '' ";
        $sql .= " ORDER BY tstamp DESC LIMIT 5";
        return $this->getDB()->query($sql);
    }

    function isAllowedAgent($spid, $agentid)
	{
		$resp = false;
		$sql = "SELECT agent_id FROM agents WHERE supervisor_id='$spid' AND agent_id='$agentid' ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$resp = true;
		}
		return $resp;
	}
		
    function checkVerifiedUser($call_id)
    {
        $resp = false;
        $sql = "SELECT * FROM chat_detail_log WHERE callid='" . $call_id . "' ";
        //var_dump($sql);
        $result = $this->getDB()->queryOnUpdateDB($sql);
        //var_dump($result);
        if (is_array($result)) {
            return $result[0]->verify_user;
        }
        return $resp;
    }
	function setAgentFirstResponseTime($call_id, $agent_first_response_time = null)
    {
        if ($agent_first_response_time == null) {
            $current_datetime = date('Y-m-d H:i:s');
        } else {
            $current_datetime = $agent_first_response_time;
        }
        $sql = "UPDATE chat_detail_log set agent_first_response = '$current_datetime' WHERE callid= '$call_id'";
		//echo $sql;
		
		$agent_id = UserAuth::getCurrentUser();
        $text = ["Date:" => date("Y-m-d H:i:s"), "Call_id:" => $call_id, "Agent_id:" => $agent_id, "SQL:" => $sql];
        log_text($text);
		
        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }
    public function getAllAgentsAndSupervisor($agent_id_array)
    {
        $sql = "SELECT agent_id,name FROM agents WHERE (usertype='A' OR usertype='S')";
        if(!in_array('*', $agent_id_array)) 
        	$sql .= " AND agent_id IN ('".implode("','",$agent_id_array)."') ";
        
        return $this->getDB()->query($sql);
    }

    public function logChatCoBrowse($params)
    {
        $logStartTime = $params['logStartTime'];
        $agentId = $params['agentId'];
        $customerName = $params['customerName'];
        $customerNumber = $params['customerNumber'];
        $callId = $params['callId'];
        $requestType = $params['requestType'];
        $customerUrl = $params['customerUrl'];
        $agentUrl = $params['agentUrl'];

        $table = "chat_co_browse_log";
        $columns = " log_start_time = '{$logStartTime}', ";
        $columns .= " agent_id = '{$agentId}', ";
        $columns .= " customer_name = '{$customerName}', ";
        $columns .= " customer_number = '{$customerNumber}', ";
        $columns .= " callid = '{$callId}', ";
        $columns .= " request_type = '{$requestType}', ";
        $columns .= " customer_url = '{$customerUrl}', ";
        $columns .= " agent_url = '{$agentUrl}' ";

        $sql = "INSERT INTO {$table} SET {$columns}";
        $result = $this->getDB()->query($sql);

        return $result;
    }
}

