<?php
class MEmailNew extends Model{

    private $_new_disposition_id = '';
    function __construct() {
        parent::__construct();
    }

    function numETicket($search_boj, $dateinfo, $pullinfo, $newMailTime='0' ){
        $sql = "SELECT COUNT(em.ticket_id) AS numrows FROM email_messages AS em ";
        $sql .= " JOIN e_ticket_info AS eti ON eti.ticket_id=em.ticket_id ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $cond = $this->getEmailCondition($search_boj, $dateinfo, $pullinfo, $newMailTime);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //GPrint($sql);die;
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getETicket( $search_boj, $dateinfo, $pullinfo, $newMailTime='0', $offset=0, $limit=0){
        $sql = "SELECT em.*, eti.skill_id, eti.account_id, eti.first_name, eti.last_name, eti.customer_id FROM email_messages AS em  ";
        $sql .= " JOIN e_ticket_info AS eti ON eti.ticket_id = em.ticket_id ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $cond = $this->getEmailCondition($search_boj, $dateinfo, $pullinfo, $newMailTime);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY em.tstamp DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }

    function getEmailCondition($search_boj, $dateinfo, $pullinfo, $newMailTime)	{
        $cond = '';

        if (!empty($search_boj->ticket_id)) $cond .= "eti.ticket_id='$search_boj->ticket_id'";
        if (!empty($search_boj->subject)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.subject_db LIKE '%$search_boj->subject%'";
        }
        if (!empty($search_boj->from_email)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.from_email LIKE '%$search_boj->from_email%'";
        }
        if (!empty($search_boj->mail_to)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.mail_to LIKE '%$search_boj->mail_to%'";
        }

        if (!empty($dateinfo->ststamp) && ($dateinfo->ststamp < $dateinfo->etstamp)){
            if ( $newMailTime == "24") {
                $starttime = strtotime("-1 day");
                $endtime = time();
                $date_cond = " em.tstamp BETWEEN '$starttime' AND '$endtime' ";
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $date_cond;
            }else {
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= " em.tstamp BETWEEN '".$dateinfo->ststamp."' AND '".$dateinfo->etstamp."' ";
            }
        }

        if (!empty($search_boj->phone)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.phone LIKE '%$search_boj->phone%'";
        }
        if (!empty($search_boj->status)) {
            $status = $search_boj->status=='O'?'':$search_boj->status;
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.email_status='$status'";
        }
        if (!empty($search_boj->did)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.email_did='$search_boj->did'";
        }
        if (!empty($search_boj->assigned_to)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "em.assigned_to='$search_boj->assigned_to'";
        }
        if (!empty($pullinfo->ststamp) && ($pullinfo->ststamp < $pullinfo->etstamp)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " em.last_pull_time BETWEEN '".$pullinfo->ststamp."' AND '".$pullinfo->etstamp."' ";
        }



        if (!empty($search_boj->skill)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.skill_id='$search_boj->skill'";
        }
        if (!empty($search_boj->account_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.account_id='$search_boj->account_id'";
        }
        if (!empty($search_boj->last_name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "(eti.last_name LIKE '%$search_boj->last_name%'  OR eti.first_name LIKE '%$search_boj->last_name%')";
        }
        if (!empty($search_boj->customer_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.customer_id='$search_boj->customer_id'";
        }
        if (!empty($search_boj->fetch_box_email)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " eti.fetch_box_email='$search_boj->fetch_box_email' ";
        }
        return $cond;
    }

    function getDispositionTreeOptions($skillid='', $root=''){
        $left = 0;
        $rgt = 0;

        if (empty($root)) {
            $left = 1;
            $sql = 'SELECT MAX(rgt) AS max_rgt FROM email_disposition_code';
            if (!empty($skillid)) $sql .= " WHERE skill_id='$skillid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) $rgt = $result[0]->max_rgt;
        } else {
            $sql = "SELECT lft, rgt FROM email_disposition_code WHERE disposition_id='$root'";
            if (!empty($skillid)) $sql .= " AND skill_id='$skillid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) {
                $left = $result[0]->lft;
                $rgt = $result[0]->rgt;
            }
        }

        $right = array();
        $options = array();
        $sql = "SELECT disposition_id, title, lft, rgt FROM email_disposition_code WHERE ";
        if (!empty($skillid)) $sql .= "skill_id='$skillid' AND ";
        $sql .= "lft BETWEEN '$left' AND '$rgt' ORDER BY lft ASC";
        $result = $this->getDB()->query($sql);
        //echo $sql;
        if (is_array($result)) {
            foreach ($result as $row) {
                if (count($right) > 0) {
                    while ($right[count($right)-1]<$row->rgt) {
                        array_pop($right);
                        if (count($right) == 0) break;
                    }
                }

                $options[$row->disposition_id] = str_repeat(' -> ',count($right)) . $row->title;
                $right[] = $row->rgt;
            }
        }
        return $options;
    }

    function getTicketStatusLabel( $status='O' ){
        $status_available = array(
            'O' => 'New',
            'P' => 'Pending',
            'C' => 'Pending - Client',
            'S' => 'Served',
            'E' => 'Closed',
            'H' => 'Hold',
            'K' => 'Park',
            'R' => 'Re-schedule',
            'Z' => 'Skip',
        );
        $staText = isset ($status_available[$status]) ? $status_available[$status] : "";
        return $staText;
    }

    function getAllSkillName($skill_array=null){
        $response = [];
        if (!empty($skill_array)){
            $sql = "SELECT skill_id,skill_name FROM skill WHERE skill_id IN ('".implode("','", $skill_array)."') ";
            $response = $this->getDB()->query($sql);
        }
        return $response;
    }

    function getTopMenuEmailCount($email_arr, $date_info){
        $from = !empty($date_info['sdate']) ? strtotime($date_info['sdate']) : "";
        $now = time();

        $sql = "SELECT COUNT(*) AS total, eti.fetch_box_email FROM email_messages AS em ";
        $sql .= " JOIN e_ticket_info AS eti ON eti.ticket_id = em.ticket_id ";
        $sql .= " WHERE em.tstamp BETWEEN '$from' AND '$now' AND em.email_status='' ";
        $sql .= " GROUP BY eti.fetch_box_email";

        $data = $this->getDB()->query($sql);
        $result = [];
        if (!empty($data)){
            foreach ($data as $key){
                $result[$key->fetch_box_email] = $key->total;
            }
        }
        return $result;
    }

    function isAllowedToChangeTicket($ticketid, $agentid, $role, $callid=null){
        if ($role == 'admin') return true;
        if ($role == 'supervisor') {
            return $this->isAllowedToReadTicket($ticketid, $agentid);
        } else {
            ///////06-08-18////////
            $sql = "SELECT eti.ticket_id FROM e_ticket_info AS eti LEFT JOIN agent_skill AS ags ON ags.skill_id = eti.skill_id ";
            $sql .= " WHERE eti.ticket_id = '$ticketid' AND ags.agent_id='$agentid' ";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;
            ///////06-08-18////////

            $sql = "SELECT agent_id FROM agents WHERE agent_id='$agentid' AND callid='$ticketid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT DISTINCT agent_id FROM email_messages WHERE ticket_id='$ticketid' AND agent_id='$agentid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT assigned_to FROM email_messages WHERE ticket_id='$ticketid' AND assigned_to='$agentid' ";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;
        }
        return false;
    }

    function isAllowedToReadTicket($ticketid, $agentid){
        $sql = "SELECT s.agent_id FROM agent_skill s, e_ticket_info t WHERE t.ticket_id='$ticketid' AND s.skill_id=t.skill_id AND s.agent_id='$agentid'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) return true;
        return false;
    }

    function getETicketById($ticketid, $mail_sl=null){
        if (empty($ticketid)) return null;
        $sql = "SELECT em.*,  skl.skill_name FROM email_messages AS em  ";
        //$sql .= " JOIN e_ticket_info AS eti ON eti.ticket_id=em.ticket_id ";
        $sql .= " JOIN skill AS skl ON skl.skill_id=em.skill_id ";
        $sql .= " WHERE em.ticket_id='$ticketid' ";
        $sql .= !empty($mail_sl) ? " AND em.mail_sl='".sprintf("%03d", $mail_sl)."' " : "";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getEmailAddressBook(){
        $sql = "SELECT * FROM email_address_book";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    function getTicketStatusOptions($showSelectOption=false, $hide_options=array()){
        if($showSelectOption){
            return array(
                '*' => 'Select',
                'O' => 'New',
                'P' => 'Pending',
                'C' => 'Pending - Client',
                'S' => 'Served',
                'E' => 'Closed',
                //'H' => 'Hold',
                'R' => 'Re-Schedulr',
                'K' => 'Park',
            );
        }else{
            $status =  array(
                'O' => 'New',
                'P' => 'Pending',
                'C' => 'Pending - Client',
                'S' => 'Served',
                'E' => 'Closed',
                'R' => 'Re-Schedulr',
                'K' => 'Park',
            );
        }
        if (!empty($hide_options)){
            foreach ($hide_options as $key => $key_val){
                unset($status[$key_val]);
            }
        }
        return $status;
    }

    function isDistributed($tid){
        $sql = "SELECT email_id, acd_status, status FROM emails_in WHERE email_id = '$tid'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0];
        }
        return null;
    }

//    function getTicketEmails($ticketid='', $sl='') {
//        $cond = '';
//        $sql = "SELECT * FROM email_messages ";
//        //$sql = "SELECT email_messages.*, agents.nick FROM email_messages  LEFT JOIN agents ON agents.agent_id = email_messages.agent_id ";   //////ONE BANK//////
//        if (!empty($ticketid)) $cond .= "ticket_id='$ticketid'";
//        if (!empty($sl)) {
//            if (!empty($cond)) $cond .= ' AND ';
//            $cond .= "mail_sl='$sl'";
//        }
//        if (!empty($cond)) $sql .= "WHERE $cond ";
//        $sql .= "ORDER BY mail_sl DESC ";
//        return $this->getDB()->query($sql);
//    }

    function getDispositionById($disid, $sid=''){
        $sql = "SELECT * FROM email_disposition_code WHERE disposition_id='$disid' ";
        if (!empty($sid) && ($disid != MARK_AS_CLOSED)) $sql .=  "AND skill_id='$sid' ";
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) return $return[0];
        return null;
    }

    function getEmailSignature($skillid){
        if (empty($skillid)) return null;

        $sql = "SELECT * FROM email_signature WHERE skill_id='$skillid' LIMIT 1";
        $result = $this->getDB()->query($sql);
        if (empty($result)) {
            $sql1 = "INSERT INTO email_signature SET skill_id='$skillid'";
            $this->getDB()->query($sql1);
            $result = $this->getDB()->query($sql);
        }

        return is_array($result) ? $result[0] : null;
    }

    function addETicketActivity($ticketid, $mail_sl, $user, $activity, $activity_details=''){
        if (empty($ticketid) || empty($user)) return false;
        $isActivityAdded = true;

        if ($activity=="V") {
            $sql = "SELECT * FROM e_ticket_activity WHERE ticket_id='$ticketid' AND mail_sl='$mail_sl' AND agent_id='$user' AND activity='V' ORDER BY activity_time DESC LIMIT 1";
            $result = $this->getDB()->query($sql);
            if (is_array($result) && time()-$result[0]->activity_time < 600) {
                $isActivityAdded = false;
            }
        }
        if ($isActivityAdded){
            $sql = "INSERT INTO e_ticket_activity SET ticket_id='$ticketid', mail_sl='$mail_sl', agent_id='$user', activity='$activity', ".
                "activity_details='$activity_details', activity_time='".time()."'";
            return $this->getDB()->query($sql);
        }
        return null;
    }

    function getDispositionPath($child){
        if (empty($child)) return 'Not Selected';
        $result = $this->getDB()->query("SELECT lft, rgt FROM email_disposition_code WHERE disposition_id='$child'");
        if (is_array($result)) {
            $left = $result[0]->lft;
            $rgt = $result[0]->rgt;
            $sql = "SELECT title FROM email_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
            $result = $this->getDB()->query($sql);
            $path = '';
            if (is_array($result)) {
                foreach ($result as $row) {
                    if (!empty($path)) $path .= ' -> ';
                    $path .= $row->title;
                }
                return $path;
            }
            return $path;
        }
        return 'Invalid Parent';
    }

    function getDispositionPathArray($child){
        $path = array();
        if (empty($child)) return $path;
        $result_d = $this->getDB()->query("SELECT lft, rgt, title FROM email_disposition_code WHERE disposition_id='$child'");
        if (is_array($result_d)) {
            $left = $result_d[0]->lft;
            $rgt = $result_d[0]->rgt;
            $sql = "SELECT disposition_id, title FROM email_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY title ASC";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) {
                foreach ($result as $row) {
                    $path[] = array($row->disposition_id, $row->title);
                }
            }
            $path[] = array($child, $result_d[0]->title);
        }
        return $path;
    }

    function getDBIPSettings() {
        $sql = "SELECT db_ip FROM settings";
        $rsult = $this->getDB()->query($sql);
        return !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";
    }

    function updateSkipCloseOfEticket($agent, $ticket_id, $mail_sl, $event) {
        $sql = "";
        $now = time();
        if ($event == 'skip') {
            $sql = "UPDATE email_messages SET acd_agent='$agent', acd_status='Z', `current_user`='' WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl'";
            return $this->getDB()->query($sql);
        } elseif ($event == 'close') {
            return $this->emptyCurrentUser($ticket_id, $mail_sl);
        }
        return null;
    }

    function emptyCurrentUser($ticket_id, $mail_sl){
        $sql = "UPDATE email_messages SET `current_user`='' WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl'";
        return $this->getDB()->query($sql);
    }

    function deleteEmailDistribution($tid){
        $sql = "DELETE FROM emails_in WHERE email_id='$tid' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    function getEmailMessageDetails($ticket_id, $mail_sl, $session_id=null) {
        if (empty($session_id)){
            $sql = "SELECT * FROM email_messages WHERE ticket_id='$ticket_id'AND mail_sl='$mail_sl' ";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return $result[0];
        }
        $sql = "SELECT * FROM email_messages WHERE ticket_id='$ticket_id'AND mail_sl='$mail_sl' AND session_id='$session_id'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) return $result[0];

        return null;
    }

    function updatePullInfo($agent_id, $ticket_id, $mail_sl) {
        $now = time();
        if (!empty($agent_id) && !empty($ticket_id) && !empty($mail_sl)) {
            $sql = "UPDATE email_messages SET last_pull_time='$now', `current_user`='$agent_id' WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl'";
            return $this->getDB()->query($sql);
        }
    }

    function isNewEmailArrive($ticket_id){
        $sql = "SELECT COUNT(*) AS numrows FROM email_messages WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return null;
    }

    function getTotalEmailById($ticket_id){
        $sql = "SELECT COUNT(*) as numrows FROM email_messages WHERE ticket_id = '$ticket_id'";
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? null : $result[0]->numrows;
        }
        return null;
    }

    function setEmailSkill($ticketid, $mail_sl, $skill_id, $user){
        if (empty($ticketid) || empty($skill_id) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE email_messages SET agent_id='$user', skill_id='$skill_id', rs_tr='Y', rs_tr_create_time='$now' WHERE ticket_id='$ticketid' AND mail_sl='$mail_sl' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $mail_sl, $user, 'K', $skill_id);
            return true;
        }
        return false;
    }

    function setLogEmailSessionAgent($session_result, $agentid) {
        $is_agent_exists = false;
        $sql = "";
        if (empty($session_result->agent_1)){
            $sql =" agent_1='$agentid', ";
        }else {
            if ($session_result->agent_1==$agentid)
                $is_agent_exists = true;
        }
        if (!$is_agent_exists){
            if (empty($session_result->agent_2)){
                $sql =" agent_2='$agentid', ";
            }else {
                if ($session_result->agent_2==$agentid)
                    $is_agent_exists = true;
            }
        }
        if (!$is_agent_exists) {
            if (empty($session_result->agent_3)){
                $sql =" agent_3='$agentid', ";
            }
        }
        return $sql;
    }

    function updateLogEmailSessionTransfer($ticketInfo, $agent_id, $skill_transfer=null, $agent_transfer=null){
        $now = time();
        $sql = "SELECT * FROM log_email_session WHERE ticket_id ='".$ticketInfo->ticket_id."' AND session_id='".$ticketInfo->session_id."'";
        $result = $this->getDB()->query($sql);
        if (!empty($result)) {
            $sql = "UPDATE log_email_session SET  ";
            $sql .= !empty($skill_transfer) ? " skill_id='$skill_transfer', " : "";
            $sql .= $this->setLogEmailSessionAgent($result, $agent_id);
            $sql .= " last_update_time='$now', status_updated_by='".$agent_id."',  ";
            $sql .=" rs_tr='Y', rs_tr_create_time='$now', ";
            $sql .= !empty($agent_transfer) ? " assigned_to='".$agent_transfer."', " : "";

            $sql = rtrim(trim($sql),',');
            $sql .= " WHERE ticket_id='".$ticketInfo->ticket_id."' AND session_id='".$ticketInfo->session_id."'";
            return $this->getDB()->query($sql);
        }
        return false;
    }

    function setDistributionSkill($ticketid, $skill=null, $agent_id=null, $set_cond, $eTicketInfo=null){
        $str = "";
        $sql = "SELECT * FROM emails_in WHERE email_id='{$ticketid}'";
        $exist_result = $this->getDB()->query($sql);
        if (!empty($set_cond)){
            if ($set_cond == 'S' && !empty($skill)) $str = "skill_id='$skill', status='T'"; ////   t=Transfered
            if ($set_cond == 'A' && !empty($agent_id)) $str = "agent_id='$agent_id'";
            if (!empty($exist_result)) {
                $sql = "UPDATE emails_in SET $str WHERE email_id='$ticketid'";
            } else {
                if (!empty($eTicketInfo)){
                    $now = time();
                    $sender_name = $eTicketInfo->from_name;
                    $sql = "INSERT INTO emails_in SET email_id= '{$ticketid}', tstamp = UNIX_TIMESTAMP('$now'), ";
                    $sql .= " sender_name='{$sender_name}', phone='', email='{$eTicketInfo->from_email}', `subject`='{$eTicketInfo->subject}', skill_id='{$skill}', `language`='EN', status='T', agent_id='' ";
                }
            }
            return $this->getDB()->query($sql);
        }
        return null;
    }

    function getEmailSkill($agent_id=null){
        if (!empty($agent_id)){
            $sql = "SELECT sk.skill_id, sk.skill_name FROM skill AS sk ";
            $sql .= " LEFT JOIN agent_skill AS ak ON ak.skill_id=sk.skill_id ";
            $sql .= " WHERE ak.agent_id='$agent_id' AND sk.qtype='E' ";
        } else {
            $sql = "SELECT skill_id,skill_name FROM skill  WHERE qtype='E' ";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result;
        }
        return null;
    }

    function updateRescheduleDistribution($tid, $datetime){
        $sql = "UPDATE emails_in SET tstamp = UNIX_TIMESTAMP('$datetime'), `status` = 'R' WHERE email_id = '$tid'";
        return $this->getDB()->query($sql);
    }
    function addRescheduleDistribution($tid, $datetime, $emailInfo, $status=null){
        $status = !empty($status) ? $status : "N";
        $sql = "INSERT INTO emails_in SET email_id= '$tid', tstamp = UNIX_TIMESTAMP('$datetime'), ";;
        $sql .= "email='$emailInfo->from_email', `subject`='$emailInfo->subject', skill_id='$emailInfo->skill_id', `language`='EN', status='$status', agent_id='$emailInfo->assigned_to'";
        return $this->getDB()->query($sql);
    }

    function is_customer_exists($email){
        $email = explode(",", $email);
        $sql = "SELECT customer_id FROM email_address_book WHERE email = '$email[0]'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0]->customer_id;
        }
        return '';
    }
    function get_customer_id(){
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT customer_id FROM email_address_book WHERE customer_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) return $id;
            $i++;
        }
        return $id;
    }
    function updateTicketStatus( $tid='', $mail_sl='', $status='', $user, $email=null, $ticketinfo=null, $reschedule_date_time=null, $callid=null, $eTicketInfo=null ){

        if (empty($tid) || empty($mail_sl) || empty($status)) return false;
        $customer_id = "";
        $now = time();
        if (!empty($email)){
            $customer_id =  $this->is_customer_exists($email);
            $customer_id = !empty($customer_id) ? $customer_id : $this->get_customer_id();
        }

        $distributed_res = $this->isDistributed($tid);
        if ($status == 'R') {
            if (!empty($distributed_res)) {
                $this->updateRescheduleDistribution($tid, $reschedule_date_time);
            } else {
                $this->addRescheduleDistribution($tid, $reschedule_date_time, $ticketinfo, $status);
            }
        } elseif (!empty($distributed_res->status) && $distributed_res->status == 'R') {
            $this->deleteEmailDistribution($tid);
        }

        $this->addETicketActivity($tid, $mail_sl, $user, 'S', $status);
        $sql = "UPDATE email_messages SET ";
        $sql .= "agent_id='$user', email_status='$status', ";
        $sql .= !empty($callid)? " acd_agent='$user', acd_status='$status', ":"";
        $sql .= $status=='R'? " rs_tr='Y', reschedule_time= UNIX_TIMESTAMP('$reschedule_date_time'), rs_tr_create_time='$now', " : "";
        $sql .= " customer_id='$customer_id' WHERE ";
        $sql .= $status!='R' ? " session_id='".$eTicketInfo->session_id."'" : " ticket_id='$tid' AND mail_sl='$mail_sl'";
        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    /*function updateEmailParent($parent_sl, $post_data){
        $sql = "UPDATE email_messages SET parent_sl='$parent_sl' WHERE ticket_id='".$post_data->ticket_id."' AND mail_sl='".$post_data->mail_sl."' LIMIT 1";
        return $this->getDB()->query($sql);
    }*/
    function updateInboundEmailStatus($emailInfo, $replied_status, $isStatusUpdate=false){
        $sql = "UPDATE email_messages SET ";
        $sql .= $isStatusUpdate ? " email_status='".$replied_status."', " : "";
        $sql .= " `current_user`='' WHERE ticket_id='".$emailInfo->ticket_id."' AND mail_sl='".$emailInfo->mail_sl."' LIMIT 1 ";
        return $this->getDB()->query($sql);
    }
    function getSessionId($ticket_id){
        $sql = "SELECT COUNT(*) AS total FROM log_email_session WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->query($sql);
        $session_id = !empty($result[0]->total) ? $result[0]->total+1 : 1;
        $session_id = $ticket_id.sprintf("%03d", $session_id);
        return $session_id;
    }

    function send_Udp_msg($msg, $port){

        $sql = "SELECT db_ip FROM settings";
        $rsult = $this->getDB()->query($sql);
        $msg_srv_ip = !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";
        $msg_srv_port = $port;

        $len = strlen($msg);
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
        socket_close($sock);
    }

    function getSkillAgents($skill_id='', $agent_id=''){
        $cond = '';
        $sql = "SELECT s.agent_id, a.nick FROM agent_skill AS s LEFT JOIN agents AS a ON a.agent_id=s.agent_id";
        if (!empty($agent_id)) $cond = $this->getAndCondition($cond, "s.agent_id='$agent_id'");
        if (!empty($skill_id)) $cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
        if (!empty($cond)) $sql .= " WHERE $cond";
        $sql .= " ORDER BY s.agent_id ";
        return $this->getDB()->query($sql);
    }

    function assignAgent($ticketid, $mail_sl, $agentid, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE email_messages SET agent_id='$user', assigned_to='$agentid', rs_tr='Y', rs_tr_create_time='$now' WHERE ticket_id='$ticketid' AND mail_sl='$mail_sl' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $mail_sl, $user, 'A', $agentid);
            return true;
        }
        return false;
    }

    function setEmailDisposition($ticketid, $mail_sl, $disposition_id, $user){
        if (empty($ticketid) || empty($mail_sl) || empty($user)) return false;
        $sql = "UPDATE email_messages SET agent_id='$user', email_did='$disposition_id' WHERE ticket_id='$ticketid' AND mail_sl='$mail_sl' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $mail_sl, $user, 'D', $disposition_id);
            return true;
        }
        return false;
    }

    function updateLogEmailSessionDisposition($ticket_id, $mail_sl, $disposition_id, $user){
        $sql = "SELECT * FROM email_messages WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl' ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            $sql = "SELECT * FROM log_email_session WHERE ticket_id ='$ticket_id' AND session_id='".$result[0]->session_id."'";
            $session_result = $this->getDB()->query($sql);
            if (!empty($session_result)){
                $sql = "UPDATE log_email_session SET ";
                $sql .= $this->setLogEmailSessionAgent($session_result, $user);
                $sql .= " last_update_time='".time()."', status_updated_by='$user', disposition_id='$disposition_id' ";
                $sql = rtrim(trim($sql),',');
                $sql .= " WHERE ticket_id='$ticket_id' AND session_id='".$result[0]->session_id."'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function getDispositionChildrenOptions($skillid='', $root=''){
        $options = [];
        $sql = "SELECT disposition_id, title FROM email_disposition_code WHERE parent_id='$root' AND status='Y' ";
        if (!empty($skillid)) $sql .= "AND skill_id='$skillid' ";
        $sql .= "ORDER BY title ASC";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $row) {
                $options[$row->disposition_id] = $row->title;
            }
        }
        return $options;
    }

    function updateFirstOpenTimeById($ticket_id, $mail_sl, $user) {
        $sql = "UPDATE email_messages SET first_open_time='".time()."', first_seen_by='$user' WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl'";
        return $this->getDB()->query($sql);
    }

    function updateFirstOpenTimeForLogEmailSession($eticket_obj, $agent_id) {
        if (!empty($eticket_obj)) {
            $sql = "SELECT create_time, first_open_time, waiting_duration, close_time, first_seen_by FROM log_email_session WHERE ticket_id='".$eticket_obj->ticket_id."' AND session_id='".$eticket_obj->session_id."' ";
            $result = $this->getDB()->query($sql);
            if (is_array($result) && empty($result[0]->first_open_time) && empty($result[0]->first_seen_by)) {
                $now = time();
                $waiting_duration = $now -  $result[0]->create_time;
                $sql = "UPDATE log_email_session SET first_open_time='$now', waiting_duration='$waiting_duration', first_seen_by='$agent_id' WHERE ticket_id='".$eticket_obj->ticket_id."' AND session_id='".$eticket_obj->session_id."'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function getAttachments($tid, $sl, $time_stamp=false){
        if ($time_stamp){
            $sql = "SELECT * FROM email_attachments WHERE ticket_id='$tid' AND mail_sl='$sl' ORDER BY part_position ASC";
            $result['data'] = $this->getDB()->query($sql);
            if (!empty($result['data'])){
                $sql = "SELECT tstamp FROM email_messages WHERE ticket_id='$tid' AND mail_sl='$sl' LIMIT 1";
                $result2 = $this->getDB()->query($sql);
                if (is_array($result2)) {
                    $result['tstamp'] = $result2[0]->tstamp;
                    return $result;
                }
            }
            return null;
        }
        $sql = "SELECT * FROM email_attachments WHERE ticket_id='$tid' AND mail_sl='$sl' ORDER BY part_position ASC";
        return $this->getDB()->query($sql);
    }

    function saveEmailMessage($post_data){
        if(empty($post_data->ticket_id) || empty($post_data->mail_sl))
            return false;

        $sql = "INSERT INTO email_messages SET ";
        $sql .="ticket_id='$post_data->ticket_id', mail_sl='$post_data->mail_sl', subject='$post_data->subject', ";
        $sql .="from_name='$post_data->from_name', from_email='$post_data->from_email', mail_from='$post_data->mail_from', ";
        $sql .="mail_to='$post_data->mail_to', mail_cc='$post_data->mail_cc', mail_bcc='$post_data->mail_bcc', ";
        $sql .="mail_body='$post_data->mail_body', status='$post_data->status', skill_id='".$post_data->skill_id."', tstamp='".$post_data->tstamp."', ";
        $sql .="has_attachment='$post_data->has_attachment', is_forward='$post_data->is_forward', agent_id='$post_data->agent_id', ";
        $sql .="phone='$post_data->phone', email_status='$post_data->email_status', email_did='$post_data->email_did', ";
        $sql .="acd_agent='$post_data->acd_agent', acd_status='$post_data->acd_status', `current_user`='', ";
        $sql .="rs_tr='$post_data->rs_tr', ";
        $sql .=!empty($post_data->reschedule_time) ? "reschedule_time=UNIX_TIMESTAMP('$post_data->reschedule_time'), " : "";
        $sql .=!empty($post_data->rs_tr_create_time) ? "rs_tr_create_time='$post_data->rs_tr_create_time', " : "";
        $sql .="fetch_box_email='$post_data->fetch_box_email', ";
        $sql .="fetch_box_name='$post_data->fetch_box_name' ";
        $sql = rtrim(trim($sql),',');
        return $this->getDB()->query($sql);
    }

    function saveEmailsOut($post_data){
        if(empty($post_data->ticket_id) || empty($post_data->mail_sl))
            return false;

        $sql = "INSERT INTO emails_out SET ";
        $sql .= "ticket_id='$post_data->ticket_id', ";
        $sql .= "mail_sl='$post_data->mail_sl', ";
        $sql .= "subject='$post_data->subject', ";
        $sql .= "from_name='$post_data->from_name', ";
        $sql .= "from_email='$post_data->from_email', ";
        $sql .= "to_email='$post_data->mail_to', ";
        $sql .= "cc='$post_data->mail_cc', ";
        $sql .= "bcc='$post_data->mail_bcc', ";
        $sql .= "body='$post_data->mail_body', ";
        $sql .= "has_attachment='$post_data->has_attachment', ";
        $sql .= "is_forward='$post_data->is_forward', ";
        $sql .= "tstamp='".$post_data->tstamp."' ";
        $sql = rtrim(trim($sql),',');
        return $this->getDB()->query($sql);
    }

    function updateETicketInfo($post_data){
        if(empty($post_data->ticket_id) || empty($post_data->mail_sl))
            return false;

        $sql = "UPDATE e_ticket_info SET  ";
        $sql .= "num_mails = num_mails+1 ";
        $sql .= "WHERE ticket_id='$post_data->ticket_id'";
        return $this->getDB()->query($sql);
    }

    function saveForwardedAttachment($data, $attachment_no, $next_mail_sl, $attachment_save_path, $__dir){
        $file_names = [];
        $mail_sl = '';
        $tid = '';
        foreach ($data as $key){
            $str = explode("/",$key);
            $file_names[] = end($str);
            $mail_sl = $str[3];
            $tid = $str[2];
        }
        if (!empty($tid) && !empty($mail_sl) && !empty($file_names)){
            $sql = "SELECT * FROM email_attachments WHERE ticket_id='$tid' AND mail_sl='$mail_sl' ";
            $sql .=" AND file_name IN ('".implode("','", $file_names)."')";
            $result = $this->getDB()->query($sql);
            if (is_array($result)){
                $sql = "INSERT INTO email_attachments (ticket_id, mail_sl, attach_type, attach_subtype, part_position, file_name) VALUES ";
                foreach ($result as $item){
                    $sql .= " ( '$tid', '$next_mail_sl', '$item->attach_type', '$item->attach_subtype', '$attachment_no', '$item->file_name' ),";
                    $attachment_no++;
                }
                $sql = rtrim(trim($sql),",");

                foreach ($data as $item){
                    $old_file = $attachment_save_path.'/'.$item;
                    $name = explode("/", $item);
                    $new_fname = end($name);
                    copy($old_file, $__dir.$new_fname);
                }
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function saveAttachment($post_data, $file, $attachment_save_path, $has_att_files_no_error){
        if(empty($post_data->ticket_id) || empty($post_data->mail_sl) || empty($attachment_save_path))
            return false;

        $yy = date("y", $post_data->tstamp);
        $mm = date("m", $post_data->tstamp);
        $dd = date("d", $post_data->tstamp);
        $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $post_data->ticket_id . '/' . $post_data->mail_sl . '/';

        if (!is_dir($dir))
            mkdir($dir, 0750, true);

        $attachment_i = 0;
        $i = 0;
        if ($has_att_files_no_error) {
            foreach ($file['error'] as $err) {
                if ($err == 0) {
                    $fname = $file["name"][$i];
                    $fsubtype = strtoupper(end((explode(".", $fname))));
                    $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                    $attachment_i = $i + 1;
                    $fname = $this->getDB()->escapeString($fname);
                    move_uploaded_file($file["tmp_name"][$i], $dir . $fname);
                    $sql = "INSERT INTO email_attachments SET ticket_id='$post_data->ticket_id', mail_sl='$post_data->mail_sl', attach_type='$ftype', attach_subtype='$fsubtype', ";
                    $sql .= "part_position='$attachment_i', file_name='$fname'";
                    $this->getDB()->query($sql);
                }
                $i++;
            }
        }

        if (!empty($post_data->forwarded_attachment_hidden)){
            $attachment_i = $attachment_i==0 ? 1 : $attachment_i;
            $this->saveForwardedAttachment($post_data->forwarded_attachment_hidden, $attachment_i, $post_data->mail_sl, $attachment_save_path, $dir);
        }
    }

    function getEmailSerailNo($email_id){
        $sql = "Select count(ticket_id) as numEmail FROM email_messages WHERE ticket_id='$email_id'";
        $result = $this->getDB()->query($sql);
        $sl = $result[0]->numEmail + 1;
        return sprintf("%03d", $sl);
    }
    function addEmailMsg($post_data, $file, $attachment_save_path=''){
        $sl = $this->getEmailSerailNo($post_data->ticket_id);
        $post_data->mail_sl = $sl;

        $has_attachment = 'N';
        $activity_type = $post_data->is_forward=="Y" ? "F" : 'M';
        $has_att_files_no_error = true;

        if (is_array($file['error'])) {
            foreach ($file['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }
        }
        if (!empty($post_data->forwarded_attachment_hidden)) {
            if (is_array($file['error']) && $has_attachment == 'N'){
                $has_att_files_no_error = false;
            }
            $has_attachment = 'Y';
        }
        $post_data->has_attachment = $has_attachment;

        if ($this->saveEmailMessage($post_data)){
            $this->saveEmailsOut($post_data);
            $this->updateETicketInfo($post_data);

            $this->addETicketActivity($post_data->ticket_id, $post_data->mail_sl, $post_data->agent_id, 'S', $post_data->email_status);
            if ($post_data->email_did){
                $this->addETicketActivity($post_data->ticket_id, $post_data->mail_sl, $post_data->agent_id, 'D', $post_data->email_did);
            }
            if ($post_data->has_attachment == "Y"){
                $this->saveAttachment($post_data, $file, $attachment_save_path, $has_att_files_no_error);
            }
            $this->addETicketActivity($post_data->ticket_id, $post_data->agent_id, $activity_type, $post_data->mail_sl);
            return $sl;
        }
        return false;
    }
    function getSessionDataById($session_id=null){
        if (empty($session_id)) return false;
        $sql = "SELECT * FROM log_email_session WHERE session_id='$session_id'";
        $result  = $this->getDB()->query($sql);
        return !empty($result) ? $result[0] : false;
    }
    function updateEmailSession($emailInfo, $post_data){
        $session_data = $this->getSessionDataById($emailInfo->session_id);

        $sql = "UPDATE log_email_session SET ";
        $sql .= $this->setLogEmailSessionAgent($session_data, $post_data->agent_id);
        //$sql .= "agent_1='".$post_data->agent_id."', ";
        if ($post_data->email_status=='S' || $post_data->email_status=='E'){
            $open_duration = $post_data->tstamp - $emailInfo->first_open_time;
            $in_kpi =  $post_data->tstamp - $emailInfo->tstamp <= IN_KPI_TIME ? 'Y' : 'N';
            $sql .= "  close_time='".$post_data->tstamp."', open_duration='$open_duration', in_kpi='$in_kpi',  ";
        }
        $sql .= " `status`='".$post_data->email_status."', last_update_time='".$post_data->tstamp."', status_updated_by='".$post_data->agent_id."', ";
        $sql .= $post_data->email_status=='R'? " rs_tr='".$post_data->rs_tr."', reschedule_time='".$post_data->reschedule_time."', rs_tr_create_time='".$post_data->rs_tr_create_time."', " : "";
        $sql = rtrim(trim($sql),',');
        $sql .= " WHERE ticket_id='".$emailInfo->ticket_id."' AND session_id='".$emailInfo->session_id."'";
        if ($this->getDB()->query($sql)){
            return $this->updateEmailMsg($post_data->ticket_id, $post_data->mail_sl, $emailInfo->session_id);
        }
    }
    function crateLogEmailSession($emailInfo, $post_data){
        $new_session_id = $this->getSessionId($post_data->ticket_id);
        $_waiting_duration =  $post_data->tstamp - $emailInfo->tstamp;
        $_in_kpi = $post_data->tstamp - $emailInfo->tstamp <= IN_KPI_TIME ? "Y" :"N";
        //$this->getSessionDataById($post_data->ticket_id."001");

        $sql = "INSERT INTO log_email_session SET ";
        $sql .= " ticket_id='".$post_data->ticket_id."', session_id='".$new_session_id."', create_time='".$emailInfo->tstamp."',first_open_time='".$post_data->tstamp."',waiting_duration='$_waiting_duration',skill_id='".$post_data->skill_id."', ";
        $sql .= " agent_1='".$post_data->agent_id."', status='".$post_data->email_status."', ";
        if ($post_data->email_status == "S" || $post_data->email_status == "E"){
            $sql .= " close_time='".$post_data->tstamp."',open_duration='0',in_kpi='$_in_kpi', ";
        }
        $sql .= " last_update_time='".$post_data->tstamp."',status_updated_by='".$post_data->agent_id."',disposition_id='".$post_data->email_did."', ";
        if ($post_data->email_status == "R") {  ///// Reschedule may be turned off form reply option
            $sql .= " rs_tr='".$post_data->rs_tr."',reschedule_time='".$post_data->reschedule_time."',rs_tr_create_time='".$post_data->rs_tr_create_time."', ";
        }
        $sql .= " first_seen_by='".$post_data->agent_id."' ";
        if ($this->getDB()->query($sql)){
            /*$sql = "update email_messages SET session_id='$new_session_id' WHERE ticket_id='".$post_data->ticket_id."' AND mail_sl='".$post_data->mail_sl."' LIMIT 1";
            return $this->getDB()->query($sql);*/
            return $this->updateEmailMsg($post_data->ticket_id, $post_data->mail_sl, $new_session_id);
        }
        return false;
    }
    function updateEmailMsg($email_id, $email_sl, $new_session_id){
        $sql = "update email_messages SET session_id='$new_session_id' WHERE ticket_id='$email_id' AND mail_sl='$email_sl' LIMIT 1";
        return $this->getDB()->query($sql);
    }
    function updateLogEmailSession($emailInfo, $post_data){
        if (!empty($emailInfo)){
            if ($emailInfo->status == "N") {     ////INBOUND'S REPLY
                if (empty($emailInfo->email_status)) {
                    $this->updateEmailSession($emailInfo, $post_data);
                    return $this->updateInboundEmailStatus($emailInfo, $post_data->email_status, true);
                } else {
                    $this->crateLogEmailSession($emailInfo, $post_data);
                    return $this->updateInboundEmailStatus($emailInfo, $post_data->email_status);
                }
            } else {    /////OUTBOUND'S REPLY
                $this->crateLogEmailSession($emailInfo, $post_data);
                return $this->updateInboundEmailStatus($emailInfo, $post_data->email_status);
            }
        }
        return false;
    }

    function test() {
        //$fname = $this->getDB()->escapeString("the-cup's tea-2360104_1920.png");
        $fname = "the-cup's tea-2360104_1920.png";
        $sql = "INSERT INTO email_attachments SET ticket_id='9999999', mail_sl='999', attach_type='0', attach_subtype='0', part_position='1', file_name='$fname'";
        return $this->getDB()->query($sql);
    }

}