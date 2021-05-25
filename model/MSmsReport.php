<?php
class MSmsReport extends Model
{
    function __construct() {
        parent::__construct();
    }

    function numSmsLog($date_info, $phone_number = null)
    {
        $start_time = date("Y-m-d H:i:s", $date_info->ststamp);
        $end_time = date("Y-m-d H:i:s", $date_info->etstamp);

        $sql = "SELECT COUNT(*) AS total_record FROM log_sms_detail lsd LEFT JOIN skill_crm_disposition_code dc ";
        $sql .= " ON lsd.disposition_id = dc.disposition_id";
        $sql .= " WHERE lsd.start_time BETWEEN '$start_time' and '$end_time' ";
        if ($phone_number != null) $sql .= " AND lsd.phone_number = '$phone_number' ";
        $result = $this->getDB()->query($sql);

        return (!empty($result)) ? $result[0]->total_record : 0;
    }

    function getSmsLog($date_info,  $phone_number = null, $offset = 0, $limit = 0)
    {
        $start_time = date("Y-m-d H:i:s", $date_info->ststamp);
        $end_time = date("Y-m-d H:i:s", $date_info->etstamp);

        $sql = "SELECT *,lsd.status as status_code FROM log_sms_detail lsd LEFT JOIN skill_crm_disposition_code dc ";
        $sql .= " ON lsd.disposition_id = dc.disposition_id";
        $sql .= " WHERE lsd.start_time BETWEEN '$start_time' and '$end_time' ";
//        $sql = "SELECT * FROM log_sms_detail WHERE ";
//        $sql .= " start_time BETWEEN '$start_time' and '$end_time'";
        if ($phone_number != null) $sql .= " AND lsd.phone_number = '$phone_number' ";
        if ($limit > 0) $sql .= " LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getSmsMesseges($session_id)
    {
        $sql = "SELECT * FROM log_sms_messages ";
        $sql .= " WHERE session_id = '$session_id' ";
        return $this->getDB()->query($sql);
    }

    function getSmsLogFromSession($session_id)
    {
        $sql = "SELECT * FROM log_sms_detail WHERE ";
        $sql .= " session_id = '$session_id' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    function logSmsOut($session_id, $phone_number, $sms1, $sms2, $did)
    {
        $id = $session_id . rand(1000000, 9999999);

        $sql = "INSERT INTO sms_out SET id ='$id', sms_to = '$phone_number', 
                sms_text = '$sms1', sms_text_2 = '$sms2', sms_from = '$did' ";
        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function logSmsMessage($reply_msg, $sms_detail_data, $agent_id)
    {
        $session_id = $sms_detail_data->session_id;
        $call_id = $sms_detail_data->callid;
        $phone_number = $sms_detail_data->phone_number;
        $message = $reply_msg;
        $sent_type = "P";
        $direction = "O";
        $msg_time = date("Y-m-d H:i:s");

        $sql = "INSERT INTO log_sms_messages SET session_id = '$session_id', msg_time = '$msg_time', callid = '$call_id',
                message = '$message', agent_id = '$agent_id', sent_type = '$sent_type', direction = '$direction',
                phone_number = '$phone_number', status = '' ";
        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function updateSmsDetail($session_id, $agent_id)
    {
        $current_time = date("Y-m-d H:i:s");

        $sql = "UPDATE log_sms_detail SET last_out_msg_time = '$current_time', last_update_time = '$current_time', 
                agent_id = '$agent_id' WHERE session_id = '$session_id' LIMIT 1";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function getTotalSms($from_date = null, $to_date = null)
    {
        $sql = "SELECT COUNT(*) as total_record FROM log_sms_detail";
        if ($from_date != null && $to_date != null) {
            $sql .= " WHERE start_time BETWEEN '$from_date' AND '$to_date'";
        }

        $result = $this->getDB()->query($sql);
        return (!empty($result)) ? $result[0]->total_record : 0;
    }

    function getPendingSms($from_date = null, $to_date = null)
    {
        $sql = "SELECT COUNT(*) as total_record FROM log_sms_detail WHERE";
        if($from_date != null && $to_date != null)
            $sql .= " start_time BETWEEN '$from_date' AND '$to_date' AND ";
        $sql .= " status != 'C'";

        $result = $this->getDB()->query($sql);
        return (!empty($result)) ? $result[0]->total_record : 0;
    }

    function getServedSms($from_date = null, $to_date = null)
    {
        $sql = "SELECT COUNT(*) as total_record FROM log_sms_detail WHERE";
        if ($from_date != null && $to_date != null)
            $sql .= " start_time BETWEEN '$from_date' AND '$to_date' AND ";
        $sql .= " status = 'C'";

        $result = $this->getDB()->query($sql);
        return (!empty($result)) ? $result[0]->total_record : 0;
    }

    function getSmsTemplate()
    {
        $sql = "SELECT * FROM sms_templates WHERE ";
        $sql .= " status = 'Y'";

        return $this->getDB()->query($sql);
    }

}