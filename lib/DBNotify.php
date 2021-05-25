<?php

class DBNotify
{

	static function NotifyLogout($_db_suffix, $seat_id, $agent_id, $conn)
	{
    		$notify_txt = "WWW\r\nType: UPD_SEAT_LO\r\nseat_id: $seat_id\r\nagent_id: $agent_id\r\n";
    		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}

	static function NotifyCampaignUpdate($_db_suffix, $campaign_id, $action, $conn)
	{
	    $notify_txt = "WWW\r\nType: PD\r\nrecord_id: $campaign_id\r\naction: $action\r\n";
	    return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}
	
	function NotifyAgentUpdate($_db_suffix, $agent_id, $conn)
	{
	    $notify_txt = "WWW\r\nType: UPD_AGENT\r\nagent_id: $agent_id\r\n";
	    return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
        }
        
	function NotifySkillPropUpdate($_db_suffix, $skill_id, $conn)
	{
	    //exit;
	    
    		$notify_txt = "WWW\r\nType: UPD_SKILL\r\nskill_id: $skill_id\r\n";
    		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}

	function NotifySkillAgentUpdate($_db_suffix, $skill_id, $conn)
	{
		$notify_txt = "WWW\r\nType: UPD_SKILL_AG\r\nskill_id: $skill_id\r\n";
		//echo $_db_suffix; exit;
    		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}

	function NotifySkillUpdate($_db_suffix, $skill_id, $file, $conn)
	{
    		$notify_txt = "WWW\r\nType: UPD_SKILL_VOICE\r\nskill_id: $skill_id\r\nvoice: $file\r\n";
    		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}
	
	function NotifySkillMOHUpdate($_db_suffix, $skill_id, $conn)
	{
    		$notify_txt = "WWW\r\nType: UPD_SKILL_MOH\r\nskill_id: $skill_id\r\n";
    		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}

	function NotifyIVRUpdate($_db_suffix, $ivr_id, $file, $conn)
	{
		$notify_txt = "WWW\r\nType: UPD_IVR_VOICE\r\nivr_id: $ivr_id\r\nvoice: $file\r\n";
		return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	    	//notifyUpdate($_db_suffix, $notify_txt);
	}
	
	function NotifySIP($_db_suffix, $upd_id, $msg, $conn)
	{
	    $notify_txt = "WWW\r\nType: SIP_NOTIFY\r\nseat_id: $upd_id\r\nevent: $msg\r\n";	    
	    return self::notifyUpdate($_db_suffix, $notify_txt, $conn);
	}

	static function notifyUpdate($_db_suffix, $txt, $conn)
	{
	    $db_notification = null;
	    if ($conn->cctype>0) {
	        $sql = "SELECT db_ip  AS sip_srv_primary, db_port AS db_controller_port FROM settings LIMIT 1";
	    } else {
                $sql = "SELECT sip_srv_primary, db_controller_port FROM cc_master.account WHERE db_suffix='$_db_suffix' LIMIT 1";
            }
            //echo "\n";
            $result = $conn->query($sql);
            //print_r($result);
            if (is_array($result)) $db_notification = $result[0];
            if (!empty($db_notification->sip_srv_primary) && !empty($db_notification->db_controller_port)) {
                //echo "$db_notification->sip_srv_primary :: $db_notification->db_controller_port";
                if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
                {
                    //$errorcode = socket_last_error();
                    //$errormsg = socket_strerror($errorcode);
                    //die("Couldn't create socket: [$errorcode] $errormsg \n");
                    return false;
                }

                $txt .= "db_suffix: ". $_db_suffix . "\r\n";
                if ( ! socket_sendto($sock, $txt , strlen($txt) , 0 , $db_notification->sip_srv_primary , $db_notification->db_controller_port))
                {
                    //echo $txt;
                    //$errorcode = socket_last_error();
                    //$errormsg = socket_strerror($errorcode);
                    //die("Could not send data: [$errorcode] $errormsg \n");
                    return false;
                }
        
                //echo "Returned true";
                return true;
            }
            
            return false;
        }

}
