<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 1/12/2019
 * Time: 5:12 PM
 */

class MCustomerJourney extends Model {
    public $_module_type = ["EM", "VC", "CT", "CB", "SM", "CO"];
    public $_module_type_data_table = ["EM"=>"email_messages", "VC"=>"skill_crm_disposition_log", "CT"=>"chat_detail_log", "CB"=>"", "SM"=>"", "CO"=>""];

    function __construct() {
        parent::__construct();
    }

    function isExist($journey_id, $sdate="", $edate=""){
        if (!empty($journey_id)){
            $sql = "SELECT * FROM log_customer_journey WHERE ";
            $sql .= !empty($sdate) && !empty($edate) ? " log_time BETWEEN '$sdate' AND '$edate' AND " : "";
            $sql .= " journey_id='$journey_id' ";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                return true;
            }
        }
        return false;
    }

    function addToCustomerJourney($customer_id=null, $module_type=null, $module_sub_type=null, $journey_id=null, $time=null){
        if (!empty($customer_id) && !empty($module_type) && !empty($journey_id)){
            $now = !empty($time) ? $time : time();
            $sql = "";
            if ($this->isExist($journey_id)){////// check only for email.
                //$sql = "UPDATE log_customer_journey SET log_time=FROM_UNIXTIME('$now') WHERE journey_id='$journey_id' LIMIT 1";
            } else {
                $sql = "INSERT INTO log_customer_journey SET customer_id='$customer_id', module_type='$module_type', module_sub_type='$module_sub_type', log_time=FROM_UNIXTIME('$now'), journey_id='$journey_id'";
            }
            return !empty($sql) ? $this->getDB()->query($sql) : null;
        }
        return null;
    }

    function getCustomerJourney($customer_id=null, $sdate=null, $edate=null){
        $from = !empty($sdate) ? date("Y-m-d H:i:s", strtotime($sdate)) : "";
        $to = !empty($edate) ? date("Y-m-d H:i:s", strtotime($edate)) : "";

        if (!empty($customer_id)){
            $sql = "SELECT * FROM log_customer_journey ";
            $sql .= " WHERE customer_id='$customer_id' ";
            $sql .= !empty($from) && !empty($to) && ($from<$to) ? " AND log_time BETWEEN '$from' AND '$to' " : "";
            $sql .= " ORDER BY log_time DESC LIMIT 10 ";
            return $this->getDB()->query($sql);
        }
        return null;
    }

    function getJourneyInfoByModuleType($module_arr) {
        $journey_data_table = $this->_module_type_data_table;
        if (!empty($module_arr)) {
            foreach ($journey_data_table as $key => $value) {
                //GPrint($key);
                foreach ($module_arr as $item => $ids){
                    if (($key == $item) && !empty($ids) && !empty($value)){
                        //GPrint($ids);
                        //$sql = ""
                    }
                }
            }
        }
    }

    function getCustomerJourneyByEmail($session_id){
        if (!empty($session_id)){
            $sql = "SELECT  title, les.ticket_id, les.create_time, les.close_time, les.`status`, eti.created_for, eti.subject, eti.fetch_box_email FROM log_email_session AS les ";
            $sql .= " LEFT JOIN email_disposition_code AS edc ON edc.disposition_id = les.disposition_id ";
            $sql .= " LEFT JOIN e_ticket_info AS eti ON eti.ticket_id = les.ticket_id ";
            $sql .= " WHERE les.session_id = '$session_id' ";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                return $result[0];
            }
            return "No disposition was set !";
        }
        return "Invalid Id !";
    }

    function getCustomerJourneyByChat($call_id){
        if ($call_id){
            $sql = "SELECT title,disposition_type, agn.`name`, cdl.callid, cdl.contact_number, lsi.call_start_time, (lsi.service_time-lsi.agent_hold_time) AS duration  ";
            $sql .= " FROM chat_detail_log AS cdl ";
            $sql .= " LEFT JOIN skill_disposition_code AS sdc ON sdc.disposition_id = cdl.disposition_id ";
            $sql .= " LEFT JOIN log_skill_inbound AS lsi ON lsi.callid=cdl.callid ";
            $sql .= " LEFT JOIN agents AS agn ON agn.agent_id=lsi.agent_id ";
            $sql .= " WHERE cdl.callid = '$call_id' ";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                return $result[0];
            }
            return "No disposition was set !";
        }
        return "Invalid Id !";
    }

    function getCustomerJourneyByVoiceAgent($call_id){
        if (!empty($call_id)){
            $timestamp = substr($call_id, 0, 10);
            $from = $timestamp - 3600;
            $to = $timestamp + 3600;

            $sql = "SELECT call_start_time, (service_time-agent_hold_time) AS duration, agent_id, disposition_count, ice_feedback, did, cli ";
            $sql .= " FROM log_skill_inbound AS lsi WHERE  ";
            $sql .= " call_start_time BETWEEN '".date("Y-m-d H:i:s", $from)."' AND '".date("Y-m-d H:i:s", $to)."' ";
            $sql .= " AND callid='$call_id' ";

            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                include_once('model/MAgent.php');
                $agent_model = new MAgent();
                $agent_arr = $agent_model->getAllAgents(array($result[0]->agent_id));
                $agent_name = !empty($agent_arr[0]->name) ? $agent_arr[0]->name : $result[0]->agent_id;


                $info['info'] = $result[0];
                $info['info']->name = $agent_name;
                if ($result[0]->disposition_count > 0) {
//                    $sql = "SELECT title, scdc.disposition_type FROM skill_crm_disposition_log AS scdl ";
//                    $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id = scdl.disposition_id ";
//                    $sql .= " WHERE tstamp BETWEEN '$from' AND '$to' AND  scdl.callid = '$call_id' ";
//                    $result = $this->getDB()->query($sql);
                    $result = $this->getDispostionForCall($call_id, $from, $to);
                    $info['disp'] = !empty($result) ? $result : "";
                }
                return $info;
            }
            return "No disposition was set !";
        }
        return "Invalid Id !";
    }

    function getCustomerJourneyByOutboundAgent($call_id){
        if (!empty($call_id)){
            $timestamp = substr($call_id, 0, 10);
            $from = $timestamp - 3600;
            $to = $timestamp + 3600;
            $sql = "SELECT start_time, agent_id, talk_time FROM log_agent_outbound_manual ";
            $sql .= " WHERE start_time BETWEEN '".date("Y-m-d H:i:s", $from)."' AND '".date("Y-m-d H:i:s", $to)."' AND callid='$call_id' ";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                include_once('model/MAgent.php');
                $agent_model = new MAgent();
                $agent_arr = $agent_model->getAllAgents(array($result[0]->agent_id));
                $agent_name = !empty($agent_arr[0]->name) ? $agent_arr[0]->name : $result[0]->agent_id;

                $info['info'] = $result[0];
                $info['info']->name = $agent_name;
                $disp = $this->getDispostionForCall($call_id, $from, $to);
                $info['disp'] = !empty($disp) ? $disp : "";
                return $info;
            }
            return "No disposition was set !";
        }
        return "Invalid Id !";
    }

    function getCustomerJourneyByVoiceAgentOld($call_id){
        if (!empty($call_id)){
            $sql = "SELECT cdr.did, title, disposition_type, agn.`name`, lsi.call_start_time, (lsi.service_time-lsi.agent_hold_time) AS duration, lsi.disposition_count, lsi.cli, lsi.ice_feedback ";
            $sql .= " FROM log_skill_inbound AS lsi ";
            $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id=lsi.disposition_id ";
            $sql .= " LEFT JOIN agents AS agn ON agn.agent_id=lsi.agent_id ";
            $sql .= " LEFT JOIN cdrin_log AS cdr ON cdr.callid=lsi.callid_cti ";
            $sql .= " WHERE lsi.callid='$call_id' ";

            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                $info['info'] = $result[0];
                if ($result[0]->disposition_count > 0) {
                    $sql = "SELECT title, scdc.disposition_type FROM skill_crm_disposition_log AS scdl ";
                    $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id = scdl.disposition_id ";
                    $sql .= " WHERE scdl.callid = '$call_id' ";
                    $result = $this->getDB()->query($sql);
                    $info['disp'] = !empty($result) ? $result : "";
                }
                return $info;
            }
            return "No disposition was set !";
        }
        return "Invalid Id !";
    }

    function getCustomerJourneyByVoiceIVR($call_id){
        $response = [];
        if (!empty($call_id)){
            $sql = "SELECT il.call_start_time, ivr_name, cl.cli, cl.did, cl.duration FROM ivr_log AS il ";
            $sql .= " LEFT JOIN cdrin_log AS cl ON cl.callid = il.callid_cti ";
            $sql .= " LEFT JOIN ivr AS ivr ON ivr.ivr_id = il.ivr_id ";
            $sql .= " WHERE il.callid_cti='$call_id' ";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                $response['info'] = $result[0];
                $sql = "SELECT branch,dtmf FROM log_ivr_journey WHERE callid_cti='$call_id' AND status_flag='S' ORDER BY enter_time DESC LIMIT 1 ";
                $result = $this->getDB()->query($sql);
                if (!empty($result)){
                    $brnch_dtmf = $result[0]->branch.$result[0]->dtmf;
                    $sql = "SELECT text, service_title FROM ivr_tree AS it ";
                    $sql .= " LEFT JOIN ivr_service_code AS isc ON isc.disposition_code = it.disposition_id ";
                    $sql .= " WHERE it.branch='$brnch_dtmf' ";
                    $result = $this->getDB()->query($sql);
                    if (!empty($result)){
                        $response['desp'] = $result[0];
                    }
                }
            }
            return $response;
        }
        return "Invalid Id !";
    }

    function getDispositionForChatAgent($callid){
        $sql = "SELECT title, customer_feedback, disposition_type FROM chat_detail_log AS cdl ";
        $sql .= " LEFT JOIN skill_disposition_code AS sdc ON sdc.disposition_id = cdl.disposition_id ";
        $sql .= " WHERE cdl.callid='$callid' ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            return $result[0];
        }
        return false;
    }
    function getDispositionForEmail($session_id) {
        $sql = "SELECT title FROM log_email_session AS les ";
        $sql .= " LEFT JOIN email_disposition_code AS edc ON edc.disposition_id = les.disposition_id ";
        $sql .= " WHERE les.session_id = '$session_id' ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            return $result[0];
        }
        return false;
    }

    function getVIVRForIO($session_id){
        $sql = "SELECT vl.*,ivr_name FROM vivr_log AS vl ";
        $sql .= " LEFT JOIN ivr ON ivr.ivr_id = vl.ivr_id ";
        $sql .= " WHERE session_id='$session_id' ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            return $result[0];
        }
        return false;
    }

    function getSMiningForSF($session_id){
        $response = [];
        $sql = "SELECT * FROM log_social_miner WHERE session_id='$session_id' ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            $response['info'] = $result[0];
            $sql = "SELECT enter_time,branch_title FROM log_social_m_journey WHERE session_id='$session_id'";
            $result = $this->getDB()->query($sql);
            if (!empty($result[0])){
                $response['disp'] = $result;
            }
        }
        return $response;
    }

    function getDispostionForCall($call_id, $sdate, $edate){
        if (!empty($call_id) && !empty($sdate) && $edate > $sdate){
            $sql = "SELECT title, scdc.disposition_type FROM skill_crm_disposition_log AS scdl ";
            $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id = scdl.disposition_id ";
            $sql .= " WHERE tstamp BETWEEN '$sdate' AND '$edate' AND  scdl.callid = '$call_id' ";
            return $this->getDB()->query($sql);
        }
        return null;
    }

    /*
     * used for customer journey activity log
     */
    function isActivityExists($callid){
        if (!empty($callid)){
            $sql = "SELECT customer_id FROM log_customer_journey_activity WHERE callid='$callid'";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                return true;
            }
        }
        return false;
    }

    /*
     * used to log customer journey activity
     */
    function addActivity($obj) {
        $sql = "INSERT INTO log_customer_journey_activity SET ";
        $sql .= " customer_id='$obj->customer_id', agent_id='$obj->agent_id', tstamp='$obj->tstamp', sdate='$obj->sdate', callid='$obj->callid', skill_id='$obj->skill_id' ";
        return $this->getDB()->query($sql);
    }

    /*
     * add journey
     * only for email
     */
    public function addCustomerJourneyForEmail ($customer_id, $session_object) {
        if (!empty($session_object) && !empty($session_object->create_time) && !empty($session_object->session_id)) {
            $currentTimestamp = date("Y-m-d H:i:s");
            if (!$this->isExist($session_object->session_id, date("Y-m-d H:i:s", $session_object->create_time), date("Y-m-d H:i:s"))) {
                $sql = "INSERT INTO log_customer_journey SET customer_id='$customer_id', module_type='EM', log_time='{$currentTimestamp}', journey_id='{$session_object->session_id}'";
                return $this->getDB()->query($sql);
            }
        }
        return false;
    }
}