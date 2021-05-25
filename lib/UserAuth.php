<?php

class UserAuth
{
	static function login($guser, $role,$ocktoken='',$validuser=null)
	{
		global $db;
		//hosted report
		$_SESSION[$db->db_suffix.'sesGCCUser'] = $guser;
		$_SESSION[$db->db_suffix.'sesGCCAltUser'] = !empty($validuser->altid)?$validuser->altid:"";
		$_SESSION[$db->db_suffix.'sesGCCUserNick'] = !empty($validuser->nick)?$validuser->nick:"";
		$_SESSION[$db->db_suffix.'sesGCCUserFullName'] = !empty($validuser->name)?$validuser->name:"";
		$_SESSION[$db->db_suffix.'sesGCCDBRoleId'] = !empty($validuser->role_id)?$validuser->role_id:"";
		$_SESSION[$db->db_suffix.'sesGCCRole'] = $role;
		$_SESSION[$db->db_suffix.'sesGCCPasswordExpired'] = false;
		
		$_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess'] = false;
		$_SESSION[$db->db_suffix.'sesGCCExpiredTime'] = 0;
		$_SESSION[$db->db_suffix.'sesOCXToken']=$ocktoken;
		$_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'] = 0;
		$_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'] = 0;
		$_SESSION[$db->db_suffix.'sesAccount_id']=$validuser->account_id;
		//$_SESSION[$db->db_suffix.'sesDBSuffix']=$validuser->db_prefix;
		$_SESSION[$db->db_suffix.'sesGCCAllowedAgents'] = empty($validuser->allowed_agents) ? array() : $validuser->allowed_agents;
		$_SESSION[$db->db_suffix.'sesGCCAllowedSkills'] = empty($validuser->allowed_skills) ? array() : $validuser->allowed_skills;
		$_SESSION[$db->db_suffix.'sesGCCMenuInfo'] = empty($validuser->menu_info) ? array() : $validuser->menu_info;
	}
	
	static function getAllowedAgents($mode='')
	{
		global $db;

	    $return = isset($_SESSION[$db->db_suffix.'sesGCCAllowedAgents']) ? $_SESSION[$db->db_suffix.'sesGCCAllowedAgents'] : array();
	    if ($mode == 'sql') {
	        $agents = isset($_SESSION[$db->db_suffix.'sesGCCAllowedAgents']) ? $_SESSION[$db->db_suffix.'sesGCCAllowedAgents'] : array();
	        $return = '';
	        if (is_array($agents)) {
	            $return = "'" . implode("','", $agents) . "'";
	        }
	        return $return;
	    }
	    return isset($_SESSION[$db->db_suffix.'sesGCCAllowedAgents']) ? $_SESSION[$db->db_suffix.'sesGCCAllowedAgents'] : array();
	}
	
	static function setDBSuffix($suffix)
	{
		global $db;
		$_SESSION[$db->db_suffix.'sesDBSuffix']=$suffix;
	}
	
	static function getDBSuffix()
	{
		global $db;
		$msg = '';
		if (isset($_SESSION[$db->db_suffix.'sesDBSuffix'])) {
			return $_SESSION[$db->db_suffix.'sesDBSuffix'];
		}
		return '';
	}
	
	static function getConfExtraFile()
	{
		global $db;
	    $db_suffix = UserAuth::getDBSuffix();
	    if (file_exists('conf.extras.'.$db_suffix.'.php')) {
	        return 'conf.extras.'.$db_suffix.'.php';
	    }
	    
	    return 'conf.extras.php';
	}
	
	static function setAccountName($name)
	{
		global $db;
		$_SESSION[$db->db_suffix.'sesAccName']=$name;
	}
	
	static function getAccountName()
	{
		global $db;
		$msg = '';
		if (isset($_SESSION[$db->db_suffix.'sesAccName'])) {
			return $_SESSION[$db->db_suffix.'sesAccName'];
		}
		return '';
	}
	
	static function getAccount_id()
	{
		global $db;
		$msg = '';
		if (isset($_SESSION[$db->db_suffix.'sesAccount_id'])) {
			return $_SESSION[$db->db_suffix.'sesAccount_id'];
		}
		return '';
	}
	
	static function getMessage()
	{
		global $db;
		$msg = '';
		if (isset($_SESSION[$db->db_suffix.'sesGCCMsg'])) {
			$msg = $_SESSION[$db->db_suffix.'sesGCCMsg'];
			unset($_SESSION[$db->db_suffix.'sesGCCMsg']);
		}
		return $msg;
	}
	
	static function setExpirationTime($isExpirationEnabled, $expiredOn)
	{
		global $db;
		$_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess'] = $isExpirationEnabled;
		$_SESSION[$db->db_suffix.'sesGCCExpiredTime'] = $expiredOn;
	}
	
	static function setForcePasswordOption($value=true)
	{
		global $db;
		$_SESSION[$db->db_suffix.'sesGCCPasswordExpired'] = $value;
	}
	
	static function showPassMessage($msgSet=false, $days='')
	{
		global $db;
		if ($msgSet && !empty($days)){
			$_SESSION[$db->db_suffix.'sesGCCPassExpDays'] = $days;
		}else {
			if (isset($_SESSION[$db->db_suffix.'sesGCCPassExpDays'])) unset($_SESSION[$db->db_suffix.'sesGCCPassExpDays']);
		}		
	}
	
	static function login_page($page)
	{
		global $db;
		//hosted report
		$_SESSION[$db->db_suffix.'sesGCCUser'] = $page;
		$_SESSION[$db->db_suffix.'sesGCCPage'] = $page;
	}
	
	static function isPageLoggedIn()
	{
		global $db;
		if (isset($_SESSION[$db->db_suffix.'sesGCCUser'])) {
			if (isset($_SESSION[$db->db_suffix.'sesGCCPage'])) return true;
		}
		
		return false;
	}
	
	static function logout()
	{
		global $db;
		if (isset($_SESSION[$db->db_suffix.'sesGCCUser'])) unset($_SESSION[$db->db_suffix.'sesGCCUser']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCRole'])) unset($_SESSION[$db->db_suffix.'sesGCCRole']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCPage'])) unset($_SESSION[$db->db_suffix.'sesGCCPage']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCPasswordExpired'])) unset($_SESSION[$db->db_suffix.'sesGCCPasswordExpired']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess'])) unset($_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCExpiredTime'])) unset($_SESSION[$db->db_suffix.'sesGCCExpiredTime']);
		if (isset($_SESSION[$db->db_suffix.'sesOCXToken'])) unset($_SESSION[$db->db_suffix.'sesOCXToken']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'])) unset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'])) unset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCAltUser'])) unset($_SESSION[$db->db_suffix.'sesGCCAltUser']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCUserNick'])) unset($_SESSION[$db->db_suffix.'sesGCCUserNick']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCUserFullName'])) unset($_SESSION[$db->db_suffix.'sesGCCUserFullName']);

		if (isset($_SESSION[$db->db_suffix.'sesGCCAllowedAgents'])) unset($_SESSION[$db->db_suffix.'sesGCCAllowedAgents']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCAllowedSkills'])) unset($_SESSION[$db->db_suffix.'sesGCCAllowedSkills']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCLincenseInfo'])) unset($_SESSION[$db->db_suffix.'sesGCCLincenseInfo']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCRepDay'])) unset($_SESSION[$db->db_suffix.'sesGCCRepDay']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCMenuInfo'])) unset($_SESSION[$db->db_suffix.'sesGCCMenuInfo']);
		if (isset($_SESSION[$db->db_suffix.'sesGCCDBRoleId'])) unset($_SESSION[$db->db_suffix.'sesGCCDBRoleId']);
		if (isset($_SESSION[$db->db_suffix.'priorityEmailSkill'])) unset($_SESSION[$db->db_suffix.'priorityEmailSkill']);
	}
	
	static function isPasswordChangeRequired()
	{
		global $db;
		if (isset($_SESSION[$db->db_suffix.'sesGCCPasswordExpired']) && $_SESSION[$db->db_suffix.'sesGCCPasswordExpired']) return true;
	}
	
	static function isLoggedIn()
	{
		global $db;
		/*
		if (UserAuth::isPasswordChangeRequired()) {
			header("Location: ./index.php?task=password");
			exit;
		}		
		*/
		//var_dump($_SESSION);
		if (isset($_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess']) && $_SESSION[$db->db_suffix.'sesGCCIsLimitedTimeAccess'] && $_SESSION[$db->db_suffix.'sesGCCExpiredTime'] < time()) {
			//var_dump($_SESSION);
			UserAuth::logout();
			$_SESSION[$db->db_suffix.'sesGCCMsg'] = 'Session Expired';
			header("Location: ./index.php?task=login");
			exit;
		}
		//echo date("Y-m-d H:i:s");
		if (isset($_SESSION[$db->db_suffix.'sesGCCUser'])) {
			if (isset($_SESSION[$db->db_suffix.'sesGCCRole'])) return true;
		}
		
		return false;
	}
	
	static function getCurrentUser()
	{
		global $db;
		$cur_user = isset($_SESSION[$db->db_suffix.'sesGCCUser']) ? $_SESSION[$db->db_suffix.'sesGCCUser'] : "";
		return $cur_user;
	}
	static function getCurrentAltUser()
	{
		global $db;
		$cur_user = isset($_SESSION[$db->db_suffix.'sesGCCAltUser']) ? $_SESSION[$db->db_suffix.'sesGCCAltUser'] : "";
		return $cur_user;
	}
	static function getCurrentRoolTitle()
	{
		global $db;
		$s=array(
			'A'=>'Agent',
			'R'=>'Root User',
			'S'=>'Supervisor',
			'P'=>'Report',
            'G'=>'Digicon Report',
		);
		
		$cur_user = isset($_SESSION[$db->db_suffix.'sesGCCRole']) ? $_SESSION[$db->db_suffix.'sesGCCRole'] : null;
		if($cur_user){
			return isset($s[$cur_user])?$s[$cur_user]:$cur_user;
		}
		return '';
	}
	
	static function hasRole($role)
	{
		global $db;
		$ret = false;
		if (!UserAuth::isLoggedIn()) return $ret;
		switch($role) {
			case 'admin':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='R' ? true : false;
				break;
			case 'supervisor':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='S' ? true : false;
				break;
			case 'dashboard':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='D' ? true : false;
				break;
			case 'agent':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='A' ? true : false;
				break;
			case 'report':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='P' ? true : false;
				break;
			case 'report_digicon':
				$ret = $_SESSION[$db->db_suffix.'sesGCCRole']=='G' ? true : false;
				break;
			default:
				$ret = false;
		}
		
		return $ret;
	}

	static function getRole()
	{
		global $db;
		$r = isset($_SESSION[$db->db_suffix.'sesGCCRole']) ? $_SESSION[$db->db_suffix.'sesGCCRole'] : '';
		if ($r == 'R') return 'admin';
		else if ($r == 'S') return 'supervisor';
		else if ($r == 'P') return 'report';
		else if ($r == 'G') return 'report_digicon';
		else if ($r == 'D') return 'dashboard';
		else if ($r == 'A') return 'agent';
		
		return '';
	}
	static function getRoleID()
	{
		global $db;
		return  isset($_SESSION[$db->db_suffix.'sesGCCRole']) ? $_SESSION[$db->db_suffix.'sesGCCRole'] : '';			
	}
	
	static function setReportDay($days=0)
	{
		global $db;
	    if (!empty($days)){
	        $_SESSION[$db->db_suffix.'sesGCCRepDay'] = $days;
	    }else {
	        $_SESSION[$db->db_suffix.'sesGCCRepDay'] = 0;
	    }
	}

	static function getReportDays()
	{
		global $db;
	    $days = 0;
	    /*if (isset($_SESSION[$db->db_suffix.'sesGCCRepDay']) && !empty($_SESSION[$db->db_suffix.'sesGCCRepDay'])){
	        $days = $_SESSION[$db->db_suffix.'sesGCCRepDay'];
	    }*/
	    return $days;
	}
	
	static function getRepErrMsg()
	{
		global $db;
		return "Report date range exceed!";
	}
	
	static function getOCXToken()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesOCXToken']) ? $_SESSION[$db->db_suffix.'sesOCXToken'] : "";
	}
	
	static function isWSLoggedIn()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn']) && $_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'] == 0 ? true : false;
	}
	
	static function isDBLoggedIn()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn']) && $_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'] == 0 ? true : false;
	}
	
	static function numWSLoggedIn()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn']) ? $_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'] : 0;
	}
	
	static function numDBLoggedIn()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn']) ? $_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'] : 0;
	}
	
	static function addWSLoggedIn()
	{
		global $db;
		if (isset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'])) $_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn']++;
	}
	
	static function addDBLoggedIn()
	{
		global $db;
		if (isset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'])) $_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn']++;
	}
	
	static function resetWSLoggedIn($ocxtoken)
	{
		global $db;
	    if (isset($_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'])) $_SESSION[$db->db_suffix.'sesGCCNumWSLoggedIn'] = 0;
	    if (isset($_SESSION[$db->db_suffix.'sesOCXToken'])) $_SESSION[$db->db_suffix.'sesOCXToken'] = $ocxtoken;
	}
	
	static function resetDBLoggedIn()
	{
		global $db;
	    if (isset($_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'])) $_SESSION[$db->db_suffix.'sesGCCNumDBLoggedIn'] = 0;
	}
	
	static function getUserNick()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCUserNick']) ? $_SESSION[$db->db_suffix.'sesGCCUserNick'] : "";
	}
	static function getUserID()
	{
		global $db;
		return isset($_SESSION[$db->db_suffix.'sesGCCUser']) ? $_SESSION[$db->db_suffix.'sesGCCUser'] : "";
	}
	
	static function setLicenseSettings($lObject)
	{
		global $db;
		if (isset ( $_SESSION[$db->db_suffix.'sesGCCLincenseInfo'] )) {
			unset ( $_SESSION[$db->db_suffix.'sesGCCLincenseInfo'] );
		}
		$_SESSION [$db->db_suffix.'sesGCCLincenseInfo'] = serialize ( $lObject );
	}
	
	static function getLicenseSettings()
	{
		global $db;
		if (isset ( $_SESSION[$db->db_suffix.'sesGCCLincenseInfo'] )) {
			return unserialize ( $_SESSION[$db->db_suffix.'sesGCCLincenseInfo'] );
		} else {
			return null;
		}
	}
	
	static function setCDRSearchParams($lObject)
	{
		global $db;
		if (isset ( $_SESSION[$db->db_suffix.'sesGCCSearchParams'] )) {
			unset ( $_SESSION[$db->db_suffix.'sesGCCSearchParams'] );
		}
		$_SESSION[$db->db_suffix.'sesGCCSearchParams'] = serialize ( $lObject );
	}
	
	static function getCDRSearchParams()
	{
		global $db;
		if (isset ( $_SESSION[$db->db_suffix.'sesGCCSearchParams'] )) {
			return unserialize ( $_SESSION[$db->db_suffix.'sesGCCSearchParams'] );
		} else {
			return null;
		}
	}

    static function setPartition($id, $label)
    {
    	global $db;
        $_SESSION[$db->db_suffix.'sesPartition'] = ['partition_id' => $id, 'name' => $label];
    }

    static function getPartition()
    {
    	global $db;
        return isset($_SESSION[$db->db_suffix.'sesPartition']) ? $_SESSION[$db->db_suffix.'sesPartition'] : [];
    }
    static function getCurrentUserMenu()
	{
		global $db;
		return  isset($_SESSION[$db->db_suffix.'sesGCCMenuInfo']) ? $_SESSION[$db->db_suffix.'sesGCCMenuInfo'] : '';			
	}
    static function getUserName()
    {
    	global $db;
        return isset($_SESSION[$db->db_suffix.'sesGCCUserFullName']) ? $_SESSION[$db->db_suffix.'sesGCCUserFullName'] : "";
    }
    static function isSetSesGCCPassExpDays()
    {
    	global $db;
        return isset($_SESSION[$db->db_suffix.'sesGCCPassExpDays']) ? true : false;
    }
    static function getSesGCCPassExpDays()
    {
    	global $db;
        return isset($_SESSION[$db->db_suffix.'sesGCCPassExpDays']) ? $_SESSION[$db->db_suffix.'sesGCCPassExpDays'] : '';
    }
    static function getSesGCCDBRoleId()
    {
    	global $db;
        return isset($_SESSION[$db->db_suffix.'sesGCCDBRoleId']) ? $_SESSION[$db->db_suffix.'sesGCCDBRoleId'] : '';
    }
}

?>