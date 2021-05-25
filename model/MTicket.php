<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/2/2018
 * Time: 3:01 PM
 */

class MTicket extends Model
{
    private $_new_disposition_id = '';

    function __construct(){
        parent::__construct();
    }

    function getChangableTicketStatus($status=''){
        $status_changable = array(
            'O' => array('P', 'C', 'S', 'E'),
            'P' => array('C', 'S', 'E'),
            'C' => array('P', 'S', 'E'),
            'S' => array('E'),
            'E' => array(),
        );
        $status_array = isset ($status_changable[$status]) ? $status_changable[$status] : array();
        return $status_array;
    }

    function getTicketStatusLabel( $status='O' ) {
        $status_available = array(
            'O' => 'New',
            'P' => 'Pending',
            'C' => 'Pending - Client',
            'S' => 'Served',
            'E' => 'Closed',
//            'H' => 'Hold',
//            'K' => 'Park',
//            'R' => 'Re-schedule',
//            'Z' => 'Skip',
        );
        $staText = isset ($status_available[$status]) ? $status_available[$status] : "";
        return $staText;
    }

    function getTicketTemplateOptions($did, $status='') {
        $did = str_replace(array("'",'"'), '', $did);
        $options = array();
        $cond = '';
        $sql = "SELECT tstamp, title, ticket_body FROM ticket_templates ";
        if (!empty($did)) $cond .= "(disposition_id='$did' OR disposition_id='')";
        else $cond .= "disposition_id=''";
        if (!empty($status)) {
            $cond .= ' AND ';
            $cond .= "status='$status' ";
        }
        $sql .= "WHERE $cond ";
        $sql .= 'ORDER BY title';
        $result = $this->getDB()->query($sql);
        return $result;
    }
    private function random_digits($num_digits){
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
    }

    function createNewEmail($agent_id, $skill_id, $did, $ticket_body, $name, $account_id, $category_id, $status, $created_for, $source, $assigned_to='') {
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT ticket_id FROM t_ticket_info WHERE ticket_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            $i++;
        }

        if (empty($id)) return false;
        $now = time();

        $sql = "INSERT INTO t_ticket_info SET ticket_id='$id', created_by='$agent_id', created_for='$created_for', assigned_to='$assigned_to', ".
            "status_updated_by='$agent_id',  skill_id='$skill_id', category_id='$category_id', disposition_id='$did', ".
            "status='$status', num_tickets='1',  last_update_time='$now', create_time='$now', source='$source' `name`='$name', account_id='$account_id' ";
        $is_update = $this->getDB()->query($sql);

        if ($is_update) {
            $sql = "INSERT INTO ticket_messages SET ticket_id='$id', ticket_sl='001', from_name='$name', ticket_to='$created_for', ".
                "  ticket_body='$ticket_body', has_attachment='', agent_id='$agent_id',  from_name='$name', status='$status', tstamp='$now' ";
            $this->getDB()->query($sql);

            $sql = "INSERT INTO t_ticket_activity SET ticket_id='$id', agent_id='$agent_id', activity='M', ".
                "activity_details='001', activity_time='$now'";
            $this->getDB()->query($sql);

            if (!empty($did)) {
                $sql = "INSERT INTO t_ticket_activity SET ticket_id='$id', agent_id='$agent_id', activity='D', ".
                    "activity_details='$did', activity_time='$now'";
                $this->getDB()->query($sql);
            }
        }
        return $is_update;
    }


    function numETicket($agentid, $assignedId, $ticketid,  $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for){
        $sql = "SELECT COUNT(eti.ticket_id) AS numrows FROM t_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $cond = $this->getEmailCondition($assignedId, $ticketid,  $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        //GPrint($sql);die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getEmailCondition($assignedId, $ticketid,  $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null, $source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker=null, $created_for=null)	{
        $cond = '';
        $timeField = "create_time";
        if (!empty($ticketid)) $cond .= "eti.ticket_id='$ticketid'";

        if (!empty($sender_name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.created_by='$sender_name'";
        }

        if (!empty($maker)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.created_by='$maker'";
        }

        if (!empty($disposition_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.disposition_id='$disposition_id'";
        }

        if (!empty($assignedId)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.assigned_to='$assignedId'";
        }
        //$cond .= !empty($maker) ? "ag.name LIKE '%$maker%' " : "";
        $cond .= !empty($created_for) ? "eti.created_for = ".$created_for : "";

        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.status='$status'";
        }
        if (!empty($source)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.source='$source'";
        }
        if (!empty($account_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.account_id='$account_id'";
        }
        if (!empty($last_name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.name='$last_name'";
        }
        if (!empty($ticket_category_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.category_id='$ticket_category_id'";
        }
        //GPrint($tfield);die;
        if (!empty($tfield)) {
            if ($tfield == "update"){
                $timeField = "eti.last_update_time";
            }
        }

        $last_date_attr = new stdClass();
        $last_date_attr->condition = "";
        $date_attributes = DateHelper::get_date_attributes($timeField, $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (isset($updateinfo->sdate) && !empty($updateinfo->sdate)){
            $last_date_attr = DateHelper::get_date_attributes("last_update_time", $updateinfo->sdate, $updateinfo->edate, $updateinfo->stime, $updateinfo->etime);
        }

        if (!empty($date_attributes->condition)) {
            //$newMailTime = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
            if ($tfield == "update" && $newMailTime == "24"){
                $starttime = strtotime("-1 day");
                $endtime = time();
                $date_cond = "last_update_time BETWEEN $starttime AND $endtime";
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $date_cond;
            }else {
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $date_attributes->condition;
            }
        }

        if (!empty($last_date_attr->condition)) {
            if ($tfield == "update" && $newMailTime == "24"){
                ;
            }else {
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $last_date_attr->condition;
            }
        }
        return $cond;
    }

    function getETicket($agentid, $assignedId, $ticketid, $disposition_id, $status, $dateinfo, $offset=0, $limit=0, $tfield, $newMailTime='0', $updateinfo=null,$source=null, $account_id=null, $last_name=null, $sender_name=null,$ticket_category_id=null,$maker,$created_for){
        $sql = "SELECT eti.*, skl.skill_name, tc.title, ag.name AS agent_name FROM t_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= "LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";
        $sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id ";

        $sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.created_by ";

        $cond = $this->getEmailCondition($assignedId, $ticketid,  $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo,$source,$account_id,$last_name, $sender_name,$ticket_category_id,$maker,$created_for);
        //if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->query($sql);
    }

    function getLastTicketUpdater($ticket_ids){
        $sql = "SELECT em.ticket_id, em.agent_id, ag.nick FROM ticket_messages em ";
        $sql .= " LEFT JOIN agents AS ag ON ag.agent_id = em.agent_id ";
        $sql .= " INNER JOIN ";
        $sql .= " (SELECT ticket_id, MAX(ticket_sl) AS max_sl FROM ticket_messages WHERE ticket_id IN('".implode("','",$ticket_ids)."') GROUP BY ticket_id) ";
        $sql .= "  group_em ON em.ticket_id = group_em.ticket_id AND em.ticket_sl = group_em.max_sl ";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    function getEmailMessage($ticket_id){
        $sql = "select e.ticket_id, e.tstamp, e.ticket_body from ";
        $sql .= " (select ticket_id , max(tstamp) as tmstamp from ticket_messages where ticket_id in('".implode("','",$ticket_id)."') group by ticket_id) ";
        $sql .= " as x inner join ticket_messages as e on e.ticket_id = x.ticket_id and e.tstamp = x.tmstamp";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }
    function  getCreatedByNames($create_by){
        $sql = "SELECT agent_id,nick FROM agents WHERE agent_id IN ('".implode("','",$create_by)."')";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    function isAllowedToChangeTicket($ticketid, $agentid, $role) {
        if ($role == 'admin') return true;
        if ($role == 'supervisor') {
            return $this->isAllowedToReadTicket($ticketid, $agentid);
        } else {
            //$sql = "SELECT agent_id FROM calls_in WHERE callid='$ticketid' AND agent_id='$agentid'";
            $sql = "SELECT agent_id FROM agents WHERE agent_id='$agentid' AND callid='$ticketid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT DISTINCT agent_id FROM ticket_messages WHERE ticket_id='$ticketid' AND agent_id='$agentid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT assigned_to FROM t_ticket_info WHERE ticket_id='$ticketid' AND assigned_to='$agentid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;
        }

        return false;
    }

    function isAllowedToReadTicket($ticketid, $agentid){
        $sql = "SELECT s.agent_id FROM agent_skill s, t_ticket_info t WHERE t.ticket_id='$ticketid' AND s.skill_id=t.skill_id AND s.agent_id='$agentid'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) return true;

        return false;
    }
    function getETicketById($ticketid){
        if (empty($ticketid)) return null;
        $sql = "SELECT eti.*, skl.skill_name,tc.title FROM t_ticket_info AS eti LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id WHERE ticket_id='$ticketid' LIMIT 1";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function addTicketMsg( $emailInfo, $msgBody, $att_files, $agentid, $status ) {

        if (empty($emailInfo->ticket_id)) return false;

        $msgBody = nl2br($msgBody);
        $sql = "Select count(ticket_id) as numEmail FROM ticket_messages WHERE ticket_id='$emailInfo->ticket_id'";
        $result = $this->getDB()->query($sql);
        $sl = $result[0]->numEmail + 1;

        $has_attachment = 'N';
        if (is_array($att_files['error'])) {
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }
        }

        $sl = sprintf("%03d", $sl);
        $now = time();

        $email_to = $emailInfo->created_for;
        $activity_type = 'M';

        $msgBody = base64_encode($msgBody);
        $sql = "INSERT INTO ticket_messages SET ".
            "ticket_id='$emailInfo->ticket_id', ".
            "ticket_sl='$sl', ".
            "from_name='$emailInfo->skill_name', ".
            "ticket_body='$msgBody', ".
            "status='$status', ".
            "tstamp='".$now."', ".
            "has_attachment='$has_attachment', ".
            "agent_id='$agentid'";
        if ($this->getDB()->query($sql)) {
            $sql = "UPDATE t_ticket_info SET ".
                "num_tickets = num_tickets+1, ".
                "status_updated_by = '$agentid'".
                "last_update_time = '".time()."', ".
                "status = '$status' ".
                "WHERE ticket_id='$emailInfo->ticket_id'";
            $this->getDB()->query($sql);

            $i = 0;
            if ($has_attachment == 'Y') {
                include('conf.email.php');
                $yy = date("y", $now);
                $mm = date("m", $now);
                $dd = date("d", $now);
                $dir = $ticket_attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailInfo->ticket_id . '/' . $sl . '/';
                if (!is_dir($dir)) mkdir($dir, 0750, true);
                foreach ($att_files['error'] as $err) {
                    if ($err == 0) {

                        var_dump($att_files["name"][$i]);

                        $fname = $att_files["name"][$i];
                        $fsubtype = strtoupper( end((explode(".", $fname))) );
                        $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                        $attachment_i = $i+1;

                        move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);
                        echo $sql = "INSERT INTO ticket_attachments SET ticket_id='$emailInfo->ticket_id', ticket_sl='$sl', attach_type='$ftype', attach_subtype='$fsubtype', ".
                            "part_position='$attachment_i', file_name='$fname'";
                        $this->getDB()->query($sql);
                    }
                    $i++;
                }
            }
            $this->addETicketActivity($emailInfo->ticket_id, $agentid, $activity_type, $sl);
            return true;
        }
        return false;
    }

    function addETicketActivity($ticketid, $user, $activity, $activity_details='') {
        $tstamp = '';
        if (empty($ticketid) || empty($user)) return false;
        $sql = "SELECT * FROM t_ticket_activity WHERE ticket_id='$ticketid' AND agent_id='$user' ORDER BY activity_time DESC LIMIT 1";
        $result = $this->getDB()->query($sql);
        if (is_array($result) && $result[0]->activity == 'V' && time()-$result[0]->activity_time <= 1800) $tstamp = $result[0]->activity_time;

        $tstamp2 = time();
        if (!empty($tstamp)) {
            $sql = "UPDATE t_ticket_activity SET activity='$activity', activity_details='$activity_details', activity_time='$tstamp2' ".
                "WHERE ticket_id='$ticketid' AND agent_id='$user' AND activity_time='$tstamp' LIMIT 1";
        } else {
            $sql = "INSERT INTO t_ticket_activity SET ticket_id='$ticketid', agent_id='$user', activity='$activity', ".
                "activity_details='$activity_details', activity_time='$tstamp2'";
        }
        return $this->getDB()->query($sql);
    }

    function updateTicketStatus( $tid='', $status='', $user ){
        if (empty($tid) || empty($status)) return false;
        $now = time();
        $sql = "UPDATE t_ticket_info SET status='$status', status_updated_by='$user', last_update_time='$now' WHERE ticket_id='$tid'";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($tid, $user, 'S', $status);
            return true;
        }
        return false;
    }

    function getEmailDispositionByTicketId($ticket_id){
        $response = null;
        $sql  = "SELECT title FROM skill_crm_disposition_code  ";
        $sql .= " LEFT JOIN t_ticket_info ON t_ticket_info.disposition_id = skill_crm_disposition_code.disposition_id ";
        $sql .= " WHERE t_ticket_info.ticket_id = '$ticket_id' ";
        $result =  $this->getDB()->query($sql);
        if (is_array($result)) {
            $response = $result[0]->title;
        }
        return $response;
    }

    function getTicketEmails($ticketid='', $sl='') {
        $cond = '';
        //$sql = "SELECT * FROM email_messages ";
        $sql = "SELECT ticket_messages.*, agents.nick FROM ticket_messages  LEFT JOIN agents ON agents.agent_id = ticket_messages.agent_id ";   //////ONE BANK//////
        if (!empty($ticketid)) $cond .= " ticket_id='$ticketid' ";
        if (!empty($sl)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " ticket_sl='$sl' ";
        }
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $sql .= " ORDER BY ticket_sl DESC ";
        return $this->getDB()->query($sql);
    }

    function getDispositionById($disid, $sid=''){
        $sql = "SELECT * FROM email_disposition_code WHERE disposition_id='$disid' ";
        if (!empty($sid)) $sql .=  "AND skill_id='$sid' ";
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) return $return[0];
        return null;
    }
    function getTicketStatusOptions($showSelectOption=false)
    {
        if($showSelectOption){
            return array(
                '*' => 'Select',
                'O' => 'New',
                'P' => 'Pending',
                'C' => 'Pending - Client',
                'S' => 'Served',
                'E' => 'Closed',
            );
        }else{
            return array(
                'O' => 'New',
                'P' => 'Pending',
                'C' => 'Pending - Client',
                'S' => 'Served',
                'E' => 'Closed',
            );
        }
    }
    function getAttachments($tid, $sl){
        $sql = "SELECT * FROM ticket_attachments WHERE ticket_id='$tid' AND ticket_sl='$sl'";
        return $this->getDB()->query($sql);
    }

    function getAttachmentDetails($tid, $sl, $part) {
        $sql = "SELECT * FROM ticket_attachments WHERE ticket_id='$tid' AND ticket_sl='$sl' AND part_position='$part' LIMIT 1";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            $sql = "SELECT * FROM ticket_messages WHERE ticket_id='$tid' AND ticket_sl='$sl' LIMIT 1";
            $result2 = $this->getDB()->query($sql);

            if (is_array($result2)) {
                $result[0]->tstamp = $result2[0]->tstamp;

                return $result[0];
            }
        }

        return null;
    }

    function getSkillAgents($skill_id='', $agent_id='') {
        $cond = '';
        $sql = "SELECT s.agent_id, a.nick FROM agent_skill AS s LEFT JOIN agents AS a ON a.agent_id=s.agent_id";
        if (!empty($agent_id)) $cond = $this->getAndCondition($cond, "s.agent_id='$agent_id'");
        if (!empty($skill_id)) $cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
        if (!empty($cond)) $sql .= " WHERE $cond";
        $sql .= " ORDER BY s.agent_id ";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    function assignAgent($ticketid, $agentid, $user) {
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE t_ticket_info SET assigned_to='$agentid', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'A', $agentid);
            return true;
        }
        return false;
    }

    function setEmailDisposition($ticketid, $disposition_id, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE t_ticket_info SET disposition_id='$disposition_id', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'D', $disposition_id);
            return true;
        }
        return false;
    }

    function updateCategory($ticketid, $category_tid, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE t_ticket_info SET category_id='$category_tid', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'G', $category_tid);
            return true;
        }
        return false;
    }




    function getMyJobSummary($agentid, $source=null){
        $sql = "SELECT SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, SUM(IF(status='C', 1, 0)) AS num_client_pendings FROM t_ticket_info ";
        $sql .= "WHERE assigned_to='$agentid'";
        $sql .= !empty($source)?" AND source='$source'":"";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getRecentJobSummary($agentid, $skipAgent, $source=null){
        $starttime = strtotime("-1 day");
        $endtime = time();
        $date_cond = "last_update_time BETWEEN $starttime AND $endtime";
        $select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
            "SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";
        if ($skipAgent) {
            $sql = "SELECT $select_fields FROM t_ticket_info AS e WHERE $date_cond";
        } else {
            $sql = "SELECT $select_fields FROM t_ticket_info AS e JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='$agentid' ";
            $sql .= "WHERE $date_cond";
        }
        $sql .= !empty($source)?" AND source='$source'":"";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getJobSummary($agentid, $skipAgent, $source=null){
        $starttime = strtotime("-1 day");
        $endtime = time();
        $select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
            "SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";
        if ($skipAgent) {
            $sql = "SELECT $select_fields FROM t_ticket_info";
        } else {
            $sql = "SELECT $select_fields FROM t_ticket_info AS e JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='$agentid' ";
        }
        $sql .= !empty($source)?" WHERE source='$source'":"";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    public function numTicketCategory($title = '', $status = ''){
        $cond = "";
        if (!empty($title)) {
            $cond .= " title LIKE '$title%'";
        }
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "status='$status'";
        }
        $sql = "SELECT COUNT(*) AS numrows FROM ticket_category ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    public function getTicketCategory($title = '', $status = '',$limit=0, $offset=0){
        $cond = "";
        if (!empty($title)) {
            $cond .= " title LIKE '$title%'";
        }
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "status='$status'";
        }
        $sql = "SELECT * FROM ticket_category ";
        if (!empty($cond)) $sql .= "WHERE $cond ";

        $sql .= "ORDER BY title ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->query($sql);
    }


    function getTicketCategoryByID($category_id = null){
        if (!empty($category_id)){
            $sql = "SELECT * FROM ticket_category WHERE category_id = '$category_id' ";
            $sql .= "LIMIT 1";
            $return = $this->getDB()->query($sql);
            if (is_array($return)) return $return[0];
        }
        return null;
    }


    function updateTicketCategory($category_id=null, $title = '', $status = ''){
        if (!empty($category_id) && !empty($title) && ($status=='A' || $status=='I')) {
            $sql = "UPDATE ticket_category SET title = '$title', `status` = '$status' WHERE category_id = '$category_id'";
            if ($this->getDB()->query($sql)) {
                $this->addToAuditLog("ticket category", 'U', $category_id , "category_id=".$category_id);
                return true;
            }
        }
        return false;
    }

    public function ticket_category_exists($title){
        $query = "SELECT COUNT(*) AS total_category FROM ticket_category WHERE title='{$title}'";
        $result =  $this->getDB()->query($query);
        return $result[0]->total_category > 0 ? TRUE : FALSE;
    }

    public function addTicketCategory($title = '', $status = 'N') {
        $id = $this->GetNewIncId("category_id", "AA", "ticket_category");
        if (empty($id) || empty($title)) return FALSE;
        $query = "INSERT INTO ticket_category (category_id,title,status) VALUES('{$id}','{$title}','{$status}')";
        if ($this->getDB()->query($query)) {
            $this->addToAuditLog("ticket category", 'A', 'category_id' , "category_id=".$id);
            return true;
        }
        return false;
    }



}