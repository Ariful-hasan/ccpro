<?php

class MSms extends Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function countRoute()
    {
        $response =  $this->getDB()->query("SELECT COUNT(*) AS total_record FROM sms_route");
        $response =  array_shift($response);

        return !empty($response->total_record) ?  $response->total_record : 0;
    }


    public function getRoute($offset = 0, $limit = 20)
    {
        return $this->getDB()->query("SELECT * FROM sms_route LIMIT {$limit} OFFSET {$offset} ");
    }


    public function countGateway()
    {
        $response =  $this->getDB()->query("SELECT COUNT(*) AS total_record FROM sms_gateway");

        return !empty($response[0]->total_record) ?  $response[0]->total_record : 0;
    }


    public function getGateway($offset = 0, $limit = 20)
    {
        return $this->getDB()->query("SELECT * FROM sms_gateway LIMIT {$limit} OFFSET {$offset} ");
    }


    public function saveRoute($prefix='', $status='A')
    {
        $gateway_id = $this->getRouteID();

        return $this->getDB()->query("INSERT INTO sms_route(prefix, gw_id, status) VALUES ('{$prefix}', '{$gateway_id}', '{$status}')");
    }


    public function updateRoute($gateway_id, $prefix='', $status='A')
    {
        return $this->getDB()->query("UPDATE sms_route SET prefix = '{$prefix}', status='{$status}' WHERE gw_id = '{$gateway_id}' LIMIT 1");
    }


    private function getRouteID($default = "AA")
    {
        $id = $this->getDB()->query("SELECT IFNULL(MAX(gw_id),0) as gw_id FROM sms_route LIMIT 1");

        if (!empty($id[0]->gw_id)) {
            $gateway_id = $id[0]->gw_id;

            return ++$gateway_id;
        }

        return $default;
    }


    public function saveGateway($gateway_id, $api = '', $status='A')
    {
        return $this->getDB()->query("INSERT INTO sms_gateway(gw_id, api, status) VALUES ( '{$gateway_id}', '{$api}', '{$status}')");
    }


    public function updateGateway($gateway_id, $api = '', $status='A')
    {
        return $this->getDB()->query("UPDATE sms_gateway SET api = '{$api}', status='{$status}' WHERE gw_id = '{$gateway_id}' LIMIT 1");
    }


    public function getGatewaysAsKeyValue($active_only=true)
    {
        $gateways = [];
        $sql = "SELECT * FROM  sms_route ";
        $sql .= $active_only ? " WHERE status = 'A' " : "";

        $data = $this->getDB()->query($sql);
        if (empty($data)) {
            return $gateways;
        }

        foreach ($data as $gateway) {
            $gateways[$gateway->gw_id] = $gateway->prefix;
        }

        return $gateways;
    }

    public function getApiByID($gateway_id)
    {
        if (empty($gateway_id)) {
            return null;
        }

        $response = $this->getDB()->query("SELECT * FROM sms_gateway WHERE gw_id = '{$gateway_id}' LIMIT 1");
        return !empty($response[0]) ? array_shift($response) : null;
    }

    public function getRouteByID($gateway_id)
    {
        if (empty($gateway_id)) {
            return null;
        }

        $response = $this->getDB()->query("SELECT * FROM sms_route WHERE gw_id = '{$gateway_id}' LIMIT 1");
        return !empty($response[0]) ? array_shift($response) : null;
    }

    public function deleteRoute($prefix = '')
    {
        $query = "DELETE FROM sms_route WHERE prefix = '{$prefix}' LIMIT 1 ";
        return empty($prefix) ? false : $this->getDB()->query($query);
    }

    public function deleteGateway($gateway_id='')
    {
        $query = "DELETE FROM sms_gateway WHERE gw_id='{$gateway_id}' LIMIT 1 ";
        return empty($gateway_id) ? false : $this->getDB()->query($query);
    }


    public function prepareURLByApiId($api_id, $method, $number, $message)
    {
        $url = '';
        $credentials = '';
        $response = $this->getDB()->query("SELECT * FROM ivr_api WHERE conn_name = '{$api_id}' LIMIT 1");
        if (empty($response[0])) {
            return $url;
        }

        $response = array_shift($response);

        if (!empty($response->credential)) {
            $credentials = str_replace(",", "&", $response->credential);
        }

        list($http_verb, $vars) = explode('(', $method);
        $vars = substr($vars, 0, - 1);
        $vars = explode(',', $vars);

        foreach ($vars as $val) {
            $val = trim($val);
            $param = str_replace("<number>", $number, $val);
            $param = str_replace("<message>", urlencode($message), $param);

            $credentials .= "&".$param;
        }

        return $response->url."?".$credentials;
    }

    /*============== For SMS Service from CRM ======================= */
    public function get_user_sms_by_session($phone)
    {
        $sql = "SELECT session_id, agent_id, msg_time,message,direction FROM log_sms_messages WHERE phone_number = '{$phone}' ORDER BY msg_time desc LIMIT 50";
        
//        return $this->getDB()->query($sql);
        //test
        return $this->getDB()->queryOnUpdateDB($sql);
    }

  	public function send_sms_to_user($session_id, $call_id, $sms, $phone, $agent_id, $did='')
    {
        $response = new stdClass();
        $response->status = false;
        $response->msg = "Something went wrong. Please try again.";

        $phone = substr($phone, -10);

        $sql = "SELECT phone_number, start_time, status FROM log_sms_detail WHERE session_id = '{$session_id}' limit 1";
        $result = $this->getDB()->query($sql);

        if (empty($result[0]->phone_number) || $result[0]->phone_number != $phone) {
            $response->msg = "Invalid target.";
            return $response;
        }

        if ($result[0]->status == "C") {
            $response->msg = "This message session has already been served.";
            return $response;
        }

        $stime = $result[0]->start_time;

        $sql = "SELECT status FROM log_sms_detail WHERE phone_number = '{$phone}' AND start_time = '{$stime}'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $sms_detail) {
                if ($sms_detail->status == "C") {
                    $response->msg = "This message session has already been served.";
                    return $response;
                }
            }
        }

        $sms_time = date("Y-m-d H:i:s");
        if (strpos($call_id, "-") !== false) {
            list($call_id, $garbage) = explode('-', $call_id);
        }

        $sql = "INSERT INTO log_sms_messages(session_id, msg_time, callid, message, agent_id, sent_type, direction, phone_number, status) ";
        $sql .= " VALUES('{$session_id}', '{$sms_time}', '{$call_id}', '{$sms}', '{$agent_id}', 'A', 'O', '{$phone}', 'I') ";
        $resp =  $this->getDB()->query($sql);

        $sql = "UPDATE log_sms_detail SET last_out_msg_time = '{$sms_time}', last_update_time='{$sms_time}' WHERE session_id = '{$session_id}' limit 1";
        $this->getDB()->query($sql);

        $sms_id = $this->generateRandomString();
        $sms = base64_encode($sms);
        $sms1 = $sms;
        $sms2 = "";

        if (strlen($sms) > 255) {
            $sms1 = substr($sms, 0, 255);
            $sms2 = substr($sms, 255);
        }

        $sql = "INSERT INTO sms_out(id, sms_to, sms_text, sms_text_2, sms_from) ";
        $sql .= " VALUES('{$sms_id}', '{$phone}', '{$sms1}', '{$sms2}', '{$did}')";

        $response->status = $this->getDB()->query($sql);
        $response->msg = $response->status ? "Sms sent successfully." : $response->status;
	return $response;
    }

    private function generateRandomString()
    {

        return time(). rand(1000000, 9999999);
    }

    public function sms_ping($msg, $msg_srv_port)
    {
        $sql = "SELECT db_ip FROM settings";
        $rsult = $this->getDB()->query($sql);
        $msg_srv_ip = !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";

        $len = strlen($msg);
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
        socket_close($sock);
        return true;
    }

    public function close_sms_session($msg, $port)
    {
        $sql = "SELECT db_ip FROM settings";
        $rsult = $this->getDB()->query($sql);
        $msg_srv_ip = !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";
        $msg_srv_port = $port;

        $len = strlen($msg);
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
        socket_close($sock);
        return true;
    }

    public function save_sms_service_disposition($session_id, $disposition='')
    {
        $agent_id = UserAuth::getUserID();
        $current_time = date("Y-m-d H:i:s");
        $status = "C";

        // ---------------------------- start already server msg status change ---------------------------------------------
        $sql = "SELECT phone_number, start_time, status FROM log_sms_detail WHERE session_id = '{$session_id}' limit 1";
        $result = $this->getDB()->query($sql);
        // var_dump($sql);
        // var_dump($result);

        if ($result[0]->status == "C") {
            $status = "A";
        }

        $stime = $result[0]->start_time;
        $phone = substr($result[0]->phone_number, -10);
        $sql = "SELECT status FROM log_sms_detail WHERE phone_number = '{$phone}' AND start_time = '{$stime}'";
        $result = $this->getDB()->query($sql);
        // var_dump($sql);
        // var_dump($result);
        
        if (is_array($result)) {
            foreach ($result as $sms_detail) {
                if ($sms_detail->status == "C") {
                    $status = "A";
                }
            }
        }
        // var_dump($status);
        // die("save_sms_service_disposition");     
        // ---------------------------- END already server msg status change ------------------------
		
        $sql = "UPDATE log_sms_detail SET disposition_id='{$disposition}', agent_id='{$agent_id}', last_update_time='{$current_time}', status='{$status}'  WHERE session_id = '{$session_id}' LIMIT 1";

																																																		 

        if ($this->getDB()->query($sql)) {
            $sql = "SELECT phone_number FROM log_sms_detail WHERE session_id = '{$session_id}' LIMIT 1";
            $response = $this->getDB()->query($sql);

            $should_send_ice = !empty($response[0]) && ($response[0]->last_out_msg_time != "0000-00-00 00:00:00") ? true : false;

            if ($should_send_ice) {

                $sms_id = $this->generateRandomString();
                include('config/constant.php');

                $did = defined('ICE_FEEDBACK_NUMBER') ? constant('ICE_FEEDBACK_NUMBER') : 28888;
                $sms = defined('ICE_FEEDBACK_MSG') ? constant('ICE_FEEDBACK_MSG') : "";
                $phone = $response[0]->phone_number;

                $sms = base64_encode($sms);
                $sms1 = $sms;
                $sms2 = "";

                if (strlen($sms) > 250) {
                    $sms1 = substr($sms, 0, 250);
                    $sms2 = substr($sms, 250);
                }

                $sql = "INSERT INTO sms_out(id, sms_to, sms_text, sms_text_2, sms_from) ";
                $sql .= " VALUES('{$sms_id}', '{$phone}', '{$sms1}', '{$sms2}', '{$did}')";

                return $this->getDB()->query($sql);
            }
        }

        return false;
    }

    public function get_sms_templates($active_only = true)
    {
        $sql = "SELECT tstamp, title, sms_body FROM sms_templates WHERE type = 'C' ORDER BY title";

        if ($active_only) {
            $sql = "SELECT tstamp, title, sms_body FROM sms_templates WHERE status = 'Y' AND type = 'C' ORDER BY title";
        }

        return $this->getDB()->query($sql);
    }
}
