<?php

class MEmail extends Model
{
    private $_new_disposition_id = '';
    function __construct() {
        parent::__construct();
    }

    function getAttachments($tid, $sl, $time_stamp=false)
    {
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

    function getAttachmentDetails($tid, $sl, $part)
    {
        $sql = "SELECT * FROM email_attachments WHERE ticket_id='$tid' AND mail_sl='$sl' AND part_position='$part' LIMIT 1";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            $sql = "SELECT * FROM email_messages WHERE ticket_id='$tid' AND mail_sl='$sl' LIMIT 1";
            $result2 = $this->getDB()->query($sql);

            if (is_array($result2)) {
                $result[0]->tstamp = $result2[0]->tstamp;

                return $result[0];
            }
        }

        return null;
    }

    function getEmailMessage($ticket_id){
        $sql = "select e.ticket_id, e.tstamp, e.mail_body from ";
        $sql .= " (select ticket_id , max(tstamp) as tmstamp from email_messages where ticket_id in('".implode("','",$ticket_id)."') group by ticket_id) ";
        $sql .= " as x inner join email_messages as e on e.ticket_id = x.ticket_id and e.tstamp = x.tmstamp";
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

    function numETicket($agentid, $assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for, $customer_id,$subject,$phone, $skill, $mail_to, $fetch_inbox_info, $ticket_id_arr)
    {
        $sql = "SELECT COUNT(eti.ticket_id) AS numrows FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }

        //$sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id = eti.category_id";
        $fetch_box_email = !empty($fetch_inbox_info['username']) ? $fetch_inbox_info['username'] : "";
        $cond = $this->getEmailCondition($assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for, $customer_id, $agentid,$subject,$phone, $skill, $mail_to, $fetch_box_email, $ticket_id_arr);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //GPrint($sql);die;
        $result = $this->getDB()->queryOnUpdateDB($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getETicket($agentid, $assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $offset=0, $limit=0, $tfield, $newMailTime='0', $updateinfo=null,$source=null, $account_id=null, $last_name=null, $sender_name=null,$ticket_category_id=null,$maker,$created_for,$customer_id,$subject,$phone, $skill, $mail_to, $fetch_inbox_info, $ticket_id_arr)
    {
        $sql = "SELECT eti.*, skl.skill_name, tc.title, ag.name FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= "LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";
        $sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id ";

        //$sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.created_by "; ///old
        $sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.agent_id "; ///new

        $fetch_box_email = !empty($fetch_inbox_info['username']) ? $fetch_inbox_info['username'] : "";
        $cond = $this->getEmailCondition($assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo,$source,$account_id,$last_name, $sender_name,$ticket_category_id,$maker,$created_for,$customer_id, $agentid,$subject,$phone, $skill,$mail_to, $fetch_box_email, $ticket_id_arr);
        //if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;die;
        $this->getDB()->setCharset("utf8");
        return $this->getDB()->queryOnUpdateDB($sql);
    }

    function getEmailCondition($assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null, $source, $account_id, $last_name,$sender_name,$ticket_category_id,$maker = null, $created_for = null,$customer_id=null, $agentid=null,$subject,$phone, $skill, $mail_to, $fetch_box_email, $ticket_id_arr)	{
        $cond = '';
        $timeField = "create_time";
        if (!empty($ticketid)) $cond .= "eti.ticket_id='$ticketid'";

        /*if (!empty($agentid)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " ( eti.assigned_to='$agentid' OR eti.agent_id='$agentid' OR eti.agent_id_2='$agentid')";
        }*/

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

        if (!empty($subject)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.subject_db LIKE '%$subject%'";
        }

        if (!empty($phone)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.phone LIKE '%$phone%'";
        }
        if (!empty($assignedId)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.assigned_to='$assignedId'";
        }

        if (!empty($email)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.created_for LIKE '$email%'";
        }

        //$cond .= !empty($created_for) ? "eti.created_for LIKE'%".$created_for."%'" : "";
        if (!empty($ticket_id_arr)) {
            $cond .= "eti.ticket_id IN ('".implode("','", $ticket_id_arr)."')";
        }elseif (empty($ticket_id_arr) && !empty($created_for)){
            $cond .= "eti.created_for = '' ";
        }

        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.status='$status'";
        }

        if (!empty($source)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.source='$source'";
        }

        //$account_id, $last_name
        if (!empty($account_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.account_id='$account_id'";
        }
        if (!empty($last_name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "(eti.last_name LIKE '%$last_name%'  OR eti.first_name LIKE '%$last_name%')";
        }
        if (!empty($ticket_category_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.category_id='$ticket_category_id'";
        }
        if (!empty($customer_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.customer_id='$customer_id'";
        }
        if (!empty($skill)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.skill_id='$skill'";
        }
        if (!empty($mail_to)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.mail_to LIKE '%$mail_to%'";
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
            if ($tfield == "update" && $newMailTime == "24") {
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

        if (!empty($fetch_box_email)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $fetch_box_email=="is_priority" ? " eti.is_priority='Y'" : " eti.fetch_box_email='$fetch_box_email' ";
        }
        return $cond;
    }

    function getETicketById($ticketid)
    {
        if (empty($ticketid)) return null;
        //$sql = "SELECT eti.*, skl.skill_name FROM e_ticket_info AS eti LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id WHERE ticket_id='$ticketid' LIMIT 1";
        $sql = "SELECT eti.*, skl.skill_name,tc.title FROM e_ticket_info AS eti LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id WHERE ticket_id='$ticketid' LIMIT 1";
        //$result = $this->getDB()->query($sql);
        $this->getDB()->setCharset("utf8");
        $result = $this->getDB()->queryOnUpdateDB($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getSkillEmailCountByDay($skillid, $dateinfo, $offset=0, $limit=0)
    {
        $date_attributes = DateHelper::get_date_attributes('create_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (empty($date_attributes->yy)) return array('result'=>null, 'numrows'=>0);

        $sql = "SELECT skill_id, FROM_UNIXTIME(create_time, '%Y-%m-%d') AS cdate, COUNT(ticket_id) AS num_tickets, SUM(IF(status='S', 1, 0)) AS num_served FROM e_ticket_info ";
        if (!empty($skillid)) {
            $sql .= "WHERE skill_id='$skillid' ";
            if (!empty($date_attributes->condition)) $sql .= "AND $date_attributes->condition ";
        } else {
            if (!empty($date_attributes->condition)) $sql .= "WHERE $date_attributes->condition ";
        }
        $sql .= "GROUP BY cdate, skill_id ORDER BY cdate ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;
        $result = $this->getDB()->query($sql);
        $numrows = $this->getDB()->query("SELECT FOUND_ROWS() AS numrows");
        return array('result'=>$result, 'numrows'=>$numrows[0]->numrows);
    }

    function isAllowedToReadTicket($ticketid, $agentid)
    {
        $sql = "SELECT s.agent_id FROM agent_skill s, e_ticket_info t WHERE t.ticket_id='$ticketid' AND s.skill_id=t.skill_id AND s.agent_id='$agentid'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) return true;

        return false;
    }

    function isAllowedToChangeTicket($ticketid, $agentid, $role, $callid=null)
    {
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

            //$sql = "SELECT agent_id FROM calls_in WHERE callid='$ticketid' AND agent_id='$agentid'";
            $sql = "SELECT agent_id FROM agents WHERE agent_id='$agentid' AND callid='$ticketid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT DISTINCT agent_id FROM email_messages WHERE ticket_id='$ticketid' AND agent_id='$agentid'";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;

            $sql = "SELECT assigned_to FROM e_ticket_info WHERE ticket_id='$ticketid' AND assigned_to='$agentid' ";
            //$sql .= " AND (assigned_to='$agentid' OR agent_id='$agentid' OR agent_id_2='$agentid')"; /////////update at 08/08/18
            $result = $this->getDB()->query($sql);
            if (is_array($result)) return true;
        }

        return false;
    }

    function addETicketActivityOld($ticketid, $user, $activity, $activity_details='')
    {
        $tstamp = '';
        if (empty($ticketid) || empty($user)) return false;
        $sql = "SELECT * FROM e_ticket_activity WHERE ticket_id='$ticketid' AND agent_id='$user' ORDER BY activity_time DESC LIMIT 1";
        $result = $this->getDB()->query($sql);
        if (is_array($result) && $result[0]->activity == 'V' && time()-$result[0]->activity_time <= 600) $tstamp = $result[0]->activity_time;

        $tstamp2 = time();
        if (!empty($tstamp)) {
            $sql = "UPDATE e_ticket_activity SET activity='$activity', activity_details='$activity_details', activity_time='$tstamp2' ".
                "WHERE ticket_id='$ticketid' AND agent_id='$user' AND activity_time='$tstamp' LIMIT 1";
        } else {
            $sql = "INSERT INTO e_ticket_activity SET ticket_id='$ticketid', agent_id='$user', activity='$activity', ".
                "activity_details='$activity_details', activity_time='$tstamp2'";
        }
        $result = $this->getDB()->query($sql);
        //$this->saveFailedQueryResult($ticketid, $sql, $result, "addETicketActivity");
        return $result;
    }

    function getTicketStatusLabel( $status='O' )
    {
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
                //'H' => 'Hold',
                'R' => 'Re-Schedulr',
                'K' => 'Park',
            );
        }else{
            return array(
                'O' => 'New',
                'P' => 'Pending',
                'C' => 'Pending - Client',
                'S' => 'Served',
                'E' => 'Closed',
                'R' => 'Re-Schedulr',
                'K' => 'Park',
            );
        }
    }

    function getChangableTicketStatus($status='')
    {
        $status_changable = array(
            'O' => array('P', 'C', 'S', 'E',  'K', 'R','O'),
            'P' => array('C', 'S', 'E', 'R', 'K', 'R'),
            'C' => array('P', 'S', 'E', 'R', 'K', 'R'),
            'S' => array('P', 'C', 'E', 'K', 'R'),
            'E' => array(),
            //'H' => array('P', 'C', 'S', 'E', 'H', 'K', 'R'),
            'R' => array('P', 'C', 'S', 'E',  'K', 'R'),
            'K' => array('P', 'C', 'S', 'E',  'K', 'R'),
        );
        //if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) $status_changable['E'] = array('P');
        $status_array = isset ($status_changable[$status]) ? $status_changable[$status] : array();
        return $status_array;
    }

    function getTicketEmails($ticketid='', $sl='') {
        $cond = '';
        $sql = "SELECT * FROM email_messages ";
        //$sql = "SELECT email_messages.*, agents.nick FROM email_messages  LEFT JOIN agents ON agents.agent_id = email_messages.agent_id ";   //////ONE BANK//////
        if (!empty($ticketid)) $cond .= "ticket_id='$ticketid'";
        if (!empty($sl)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "mail_sl='$sl'";
        }
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY mail_sl DESC ";
        return $this->getDB()->query($sql);
    }



    function addAllowedEmail($service)
    {
        if (empty($service->email)) return false;

        $sql = "INSERT INTO email_cc_bcc_list SET ".
            "skill_id='$service->skill_id', ".
            "email='$service->email', ".
            "name='$service->name', ".
            "status='$service->status'";

        if ($this->getDB()->query($sql)) {
            $this->addToAuditLog('CC/BCC Email', 'A', "Email=".$service->email, "Name=$service->name");
            return true;
        }
        return false;
    }

    function updateAllowedEmail($oldservice, $service)
    {
        if (empty($oldservice->email)) return false;
        $is_update = false;
        $changed_fields = '';
        $ltext = '';
        if ($service->name != $oldservice->name) {
            $changed_fields .= "name='$service->name'";
            $ltext = $this->addAuditText($ltext, "Name=$oldservice->name to $service->name");
        }
        if ($service->email != $oldservice->email) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "email='$service->email'";
            $ltext = $this->addAuditText($ltext, "Email=$oldservice->email to $service->email");
        }
        if ($service->status != $oldservice->status) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "status='$service->status'";
            $ltext = $this->addAuditText($ltext, "Status=$oldservice->status to $service->status");
        }
        if ($service->skill_id != $oldservice->skill_id) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "skill_id='$service->skill_id'";
            $ltext = $this->addAuditText($ltext, "Skill Changed");
        }

        if (!empty($changed_fields)) {
            $sql = "UPDATE email_cc_bcc_list SET $changed_fields WHERE email='$oldservice->email'";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            $this->addToAuditLog('CC/BCC Email', 'U', "Email=".$oldservice->email, $ltext);
        }

        return $is_update;
    }

    function deleteAllowedEmail($email)
    {
        $einfo = $this->getAllowedEmail($email);
        if (!empty($einfo)) {
            $sql = "DELETE FROM email_cc_bcc_list WHERE email='$email' LIMIT 1";
            if ($this->getDB()->query($sql)) {
                $this->addToAuditLog('CC/BCC Email', 'D', "Email=$email", "");
                return true;
            }
        }
        return false;
    }

    function numAllowedEmails()
    {
        $cond = '';
        $sql = "SELECT COUNT(email) AS numrows FROM email_cc_bcc_list ";
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    function getAllowedEmails($offset=0, $limit=0)
    {
        $sql = "SELECT * FROM email_cc_bcc_list ORDER BY name ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getAllowedEmail($email)
    {
        $sql = "SELECT * FROM email_cc_bcc_list WHERE email='$email'";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function skillEmailProperties($skill_id)
    {
        $properties = array('auto_reply'=>'N', 'signature'=>'N');

        $sql = "SELECT status FROM email_signature WHERE skill_id='$skill_id'";
        $result = $this->getDB()->query($sql);
        if(is_array($result)) {
            $properties['signature'] = $result[0]->status;
        }

        $sql = "SELECT status FROM email_auto_reply_templates WHERE skill_id='$skill_id'";
        $result = $this->getDB()->query($sql);
        if(is_array($result)) {
            $properties['auto_reply'] = $result[0]->status;
        }

        return (object) $properties;
    }

    function rebuildDispositionTree($parent, $left)
    {
        $right = $left+1;
        $sql = "SELECT disposition_id FROM email_disposition_code WHERE parent_id='$parent'";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            foreach ($result as $row) {
                $right = $this->rebuildDispositionTree($row->disposition_id, $right);
            }
        }

        $sql = "UPDATE email_disposition_code SET lft='$left', rgt='$right' WHERE disposition_id='$parent'";
        $this->getDB()->query($sql);
        return $right+1;
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
                //$path = '';
                foreach ($result as $row) {
                    //if (!empty($path)) $path .= ' -> ';
                    //$path .= $row->title;
                    //array_unshift($path, array($row->disposition_id, $row->title));
                    $path[] = array($row->disposition_id, $row->title);
                }
                //return $path;
            }
            $path[] = array($child, $result_d[0]->title);
        }

        return $path;
    }

    function getDispositionPath($child)
    {
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

    function copyDisposition($disp_from, $disp_to, $to_skill_id)
    {
        if (empty($disp_from) || empty($disp_to) || empty($to_skill_id)) return false;

        $disp_from_details = $this->getDispositionById($disp_from);

        if (!empty($disp_from_details)) {

            $service = new stdClass();
            $service->parent_id = $disp_to;
            $service->title = $disp_from_details->title;

            if ($this->addService($to_skill_id, $service)) {
                //$disp_to_details = $this->getDispositionById($disp_from);
                $id = time();
                $new_disp_id = $this->_new_disposition_id;

                if (!empty($new_disp_id)) {
                    $sql = "SELECT title, mail_body, status FROM email_templates WHERE ".
                        "disposition_id='$disp_from'";
                    $result = $this->getDB()->query($sql);
                    if (is_array($result)) {
                        foreach ($result as $row) {
                            $sql = "INSERT INTO email_templates SET tstamp='$id', ".
                                "title='$row->title', ".
                                "mail_body='$row->mail_body', ".
                                "disposition_id='$new_disp_id', ".
                                "status='$row->status'";
                            $this->getDB()->query($sql);
                            $id++;
                        }
                    }
                }

                return true;
            }


        }

        return false;
    }

    function copyDisposition2Skill($disp_from, $to_skill_id)
    {
        if (empty($disp_from) || empty($to_skill_id)) return false;

        $copySuccess = false;
        $disposList = array();
        $arraySl = -1;
        $disposFrom = $disp_from;
        while (!empty($disposFrom)) {
            $arraySl++;
            $disposDetails = $this->getDispositionById($disposFrom);
            $disposList[$arraySl]['skill_id'] = $disposDetails->skill_id;
            $disposList[$arraySl]['parent_id'] = $disposDetails->parent_id;
            $disposList[$arraySl]['disposition_id'] = $disposDetails->disposition_id;
            $disposList[$arraySl]['title'] = $disposDetails->title;
            $disposList[$arraySl]['lft'] = $disposDetails->lft;
            $disposList[$arraySl]['rgt'] = $disposDetails->rgt;
            $disposFrom = $disposDetails->parent_id;
        }
        $deCount = $arraySl;
        $lastDisposId = "";
        while ($deCount >= 0 && $disposId = $this->getDisposBySkillId($to_skill_id, $disposList[$deCount]['title'], $lastDisposId)) {
            $lastDisposId = $disposId;
            $deCount--;
        }
        if ($deCount >= 0){
            for ($mn = $deCount; $mn >= 0; $mn--){
                $disposData = new stdClass();
                $disposData->parent_id = $lastDisposId;
                $disposData->title = $disposList[$mn]['title'];
                $lastDisposId = $this->addDispos2Email($to_skill_id, $disposData);
                $id = time();
                if (!empty($lastDisposId)) {
                    $copySuccess = true;
                    $sql = "SELECT title, mail_body, status FROM email_templates WHERE ".
                        "disposition_id='".$disposList[$mn]['disposition_id']."'";
                    $result = $this->getDB()->query($sql);
                    if (is_array($result)) {
                        foreach ($result as $row) {
                            $sql = "INSERT INTO email_templates SET tstamp='$id', ".
                                "title='$row->title', ".
                                "mail_body='$row->mail_body', ".
                                "disposition_id='$lastDisposId', ".
                                "status='$row->status'";
                            $this->getDB()->query($sql);
                            $id++;
                        }
                    }
                }
            }
        }
        return $copySuccess;
    }

    function copyChatDisposition($disp_from, $disp_to, $to_skill_id)
    {
        if (empty($disp_from) || empty($disp_to) || empty($to_skill_id)) return false;

        $disp_from_details = $this->getDispositionById($disp_from);

        if (!empty($disp_from_details)) {
            $service = new stdClass();
            $service->parent_id = $disp_to;
            $service->title = $disp_from_details->title;

            if ($this->addService($to_skill_id, $service)) {
                $id = time();
                $new_disp_id = $this->_new_disposition_id;
                return true;
            }
        }
        return false;
    }

    function copyChatDispos2Skill($disp_from, $to_skill_id)
    {
        if (empty($disp_from) || empty($to_skill_id)) return false;

        $copySuccess = false;
        $disposList = array();
        $arraySl = -1;
        $disposFrom = $disp_from;
        while (!empty($disposFrom)) {
            $arraySl++;
            $disposDetails = $this->getDispositionById($disposFrom);
            $disposList[$arraySl]['skill_id'] = $disposDetails->skill_id;
            $disposList[$arraySl]['parent_id'] = $disposDetails->parent_id;
            $disposList[$arraySl]['disposition_id'] = $disposDetails->disposition_id;
            $disposList[$arraySl]['title'] = $disposDetails->title;
            $disposList[$arraySl]['lft'] = $disposDetails->lft;
            $disposList[$arraySl]['rgt'] = $disposDetails->rgt;
            $disposFrom = $disposDetails->parent_id;
        }
        $deCount = $arraySl;
        $lastDisposId = "";
        while ($deCount >= 0 && $disposId = $this->getDisposBySkillId($to_skill_id, $disposList[$deCount]['title'], $lastDisposId)) {
            $lastDisposId = $disposId;
            $deCount--;
        }
        if ($deCount >= 0){
            for ($mn = $deCount; $mn >= 0; $mn--){
                $disposData = new stdClass();
                $disposData->parent_id = $lastDisposId;
                $disposData->title = $disposList[$mn]['title'];
                $lastDisposId = $this->addDispos2Email($to_skill_id, $disposData);
            }
        }
        return $copySuccess;
    }

    function getDisposBySkillId($sid, $title='', $parentId='')
    {
        if (empty($sid)) return false;
        $sql = "SELECT disposition_id FROM email_disposition_code WHERE skill_id='$sid' ";
        if (!empty($title)) $sql .=  "AND title='$title' ";
        /*if ($parentId == ''){
            $sql .=  "AND parent_id='' ";
        }else {
            $sql .=  "AND parent_id<>'' ";
        }*/
        $sql .=  "AND parent_id='$parentId' ";
        $sql .= "LIMIT 1";//echo $sql;
        $result = $this->getDB()->query($sql);
        if (is_array($result) && !empty($result[0]->disposition_id)) return $result[0]->disposition_id;
        return false;
    }

    function addDispos2Email($sid, $dataObj)
    {
        //if (empty($service->disposition_id)) return false;
        $did = $this->getNextId('disposition_id', 'email_disposition_code', 'AAAA', 'ZZZZ');
        $this->_new_disposition_id = $did;
        $sql = "INSERT INTO email_disposition_code SET ".
            "skill_id='$sid', ".
            "parent_id='$dataObj->parent_id', ".
            "disposition_id='$did', ".
            "title='$dataObj->title'";

        if ($this->getDB()->query($sql)) {
            $this->rebuildDispositionTree('', 0);
            $this->addToAuditLog('Email Disposition', 'A', "Disposition Code=".$did, "Title=$dataObj->title");
            return $did;
        }
        return null;
    }
    /*
    function getSkillDispositions($sid)
    {
        $sql = "SELECT * FROM email_disposition_code ";
        if (!empty($sid)) $sql .= "WHERE skill_id='$sid' ";
        $sql .= "ORDER BY lft ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }
    */

    function getDispositionChildrenOptions($skillid='', $root='')
    {
        $options = array();
        $cond = "";
        //$sql = "SELECT disposition_id, title FROM email_disposition_code WHERE parent_id='$root' AND status='Y' ";
        $sql = "SELECT disposition_id, title FROM email_disposition_code WHERE ";
        if (!empty($root))$cond = $this->getAndCondition($cond, " parent_id='$root'");
        if (!empty($skillid))$cond = $this->getAndCondition($cond, " skill_id='$skillid'");
        $cond = $this->getAndCondition($cond, " status='Y'");

        $sql .= $cond." ORDER BY title ASC";
        $result = $this->getDB()->query($sql);
        //echo $sql;die;

        if (is_array($result)) {
            foreach ($result as $row) {
                $options[$row->disposition_id] = $row->title;
            }
        }

        return $options;
    }

    function getDispositionTreeOptions($skillid='', $root='')
    {
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

    function numDispositions($sid)
    {
        $sql = "SELECT COUNT(disposition_id) AS numrows FROM email_disposition_code ";
        if (!empty($sid)) $sql .= "WHERE skill_id='$sid'";
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    function getDispositions($sid, $offset=0, $limit=0)
    {
        $sql = "SELECT * FROM email_disposition_code ";
        if (!empty($sid)) $sql .= "WHERE skill_id='$sid' ";
        $sql .= "ORDER BY lft ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getDispositionById($disid, $sid='')
    {
        $sql = "SELECT * FROM email_disposition_code WHERE disposition_id='$disid' ";
        if (!empty($sid) && ($disid != MARK_AS_CLOSED)) $sql .=  "AND skill_id='$sid' ";
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) return $return[0];
        return null;
    }

    function updateSignature($oldsignature, $signature, $skillinfo)
    {
        if (empty($oldsignature->skill_id)) return false;
        $is_update = false;
        $changed_fields = '';
        $ltext = '';

        if ($signature->signature_text != $oldsignature->signature_text) {
            $changed_fields .= "signature_text='$signature->signature_text'";
            $ltext = $this->addAuditText($ltext, "Signature Changed");
        }
        if ($signature->status != $oldsignature->status) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "status='$signature->status'";
            $ltext = $this->addAuditText($ltext, "Status=$oldsignature->status to $signature->status");
        }

        if (!empty($changed_fields)) {
            $sql = "UPDATE email_signature SET $changed_fields WHERE skill_id='$oldsignature->skill_id'";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            $this->addToAuditLog('Email Signature', 'U', "Skill=".$skillinfo->skill_name, $ltext);
        }

        return $is_update;
    }

    function updateService($oldservice, $service)
    {
        if (empty($oldservice->disposition_id)) return false;
        $is_update = false;
        $changed_fields = '';
        $ltext = '';
        if ($service->title != $oldservice->title) {
            $changed_fields .= "title='$service->title'";
            $ltext = $this->addAuditText($ltext, "Title=$oldservice->title to $service->title");
        }
        if ($service->disposition_type != $oldservice->disposition_type) {
            $changed_fields .= !empty($changed_fields) ? " ," : $changed_fields;
            $changed_fields .= "disposition_type='$service->disposition_type'";
            $ltext = $this->addAuditText($ltext, "Type=$oldservice->disposition_type to $service->disposition_type");
        }
        /*
        if ($service->parent_id != $oldservice->parent_id) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "parent_id='$service->parent_id'";
            $ltext = $this->addAuditText($ltext, "Parent changed");
        }
        */

        if (!empty($changed_fields)) {
            $sql = "UPDATE email_disposition_code SET $changed_fields WHERE disposition_id='$oldservice->disposition_id'";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            //if ($service->parent_id != $oldservice->parent_id) $this->rebuildDispositionTree('', 0);
            $this->addToAuditLog('Email Disposition', 'U', "Disposition Code=".$oldservice->disposition_id, $ltext);
        }

        return $is_update;
    }

    function addService($sid, $service)
    {
        //if (empty($service->disposition_id)) return false;
        $did = $this->getNextId('disposition_id', 'email_disposition_code', 'AAAA', 'ZZZZ');
        $this->_new_disposition_id = $did;
        $sql = "INSERT INTO email_disposition_code SET ".
            "skill_id='$sid', ".
            "parent_id='$service->parent_id', ".
            "disposition_id='$did', ".
            "disposition_type='$service->disposition_type', ".
            "title='$service->title', ".
            "status='Y' ";

        if ($this->getDB()->query($sql)) {
            $this->rebuildDispositionTree('', 0);
            $this->addToAuditLog('Email Disposition', 'A', "Disposition Code=".$did, "Title=$service->title");
            return true;
        }
        return false;
    }

    function deleteDispositionId($dcode, $sid)
    {
        $dcodeinfo = $this->getDispositionById($dcode, $sid);
        if (!empty($dcodeinfo)) {
            $sql = "DELETE FROM email_disposition_code WHERE disposition_id='$dcode' AND skill_id='$sid' LIMIT 1";
            if ($this->getDB()->query($sql)) {
                $this->addToAuditLog('Email Disposition', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->title);
                return true;
            }
        }
        return false;
    }

    function getDispositionOptions($sid='')
    {
        $options = array();
        $sql = "SELECT disposition_id, title FROM email_disposition_code ";
        if (!empty($sid)) $sql .= "WHERE skill_id='$sid' ";
        $sql .= 'ORDER BY title';
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $skill) {
                $options[$skill->disposition_id] = $skill->title;
            }
        }
        //var_dump($options);
        return $options;
    }

    function getNextId($fld_id, $tbl_name, $min_id, $max_id)
    {
        $id = '';
        $sql = "SELECT MAX($fld_id) AS max_id FROM $tbl_name";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            if (!empty($result[0]->max_id)) $id = $result[0]->max_id;
        }
        if (empty($id)) return $min_id;
        if ($id == $max_id) return '';
        $id++;
        return $id;
    }

    function getSkillAgents($skill_id='', $agent_id='')
    {
        $cond = '';
        $sql = "SELECT s.agent_id, a.nick FROM agent_skill AS s LEFT JOIN agents AS a ON a.agent_id=s.agent_id";
        if (!empty($agent_id)) $cond = $this->getAndCondition($cond, "s.agent_id='$agent_id'");
        if (!empty($skill_id)) $cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
        if (!empty($cond)) $sql .= " WHERE $cond";
        $sql .= " ORDER BY s.agent_id ";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    function getSkillEmail($skillid)
    {
        $sql = "SELECT * FROM email_signature ";
        if (!empty($skillid)) $sql .= "WHERE skill_id='$skillid' ";
        $sql .= "LIMIT 1";
        //echo $sql;
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getEmailSignature($skillid)
    {
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

    function assignAgent($ticketid, $agentid, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE e_ticket_info SET assigned_to='$agentid', status_updated_by='$user', last_update_time='$now', rs_tr='Y', rs_tr_create_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'A', $agentid);
            return true;
        }
        return false;
    }

    function setEmailDisposition($ticketid, $disposition_id, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE e_ticket_info SET status_updated_by='$user', disposition_id='$disposition_id', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'D', $disposition_id);
            return true;
        }
        return false;
    }

    function updateCategory($ticketid, $category_tid, $user){
        if (empty($ticketid) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE e_ticket_info SET category_id='$category_tid', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            //$this->addETicketActivity($ticketid, $user, 'A', $agentid);
            return true;
        }
        return false;
    }


    function updateTicketStatus( $tid='', $status='', $user, $email=null, $role_id=null, $ticketinfo=null, $reschedule_date_time=null, $callid=null, $isSend=false){

        if (empty($tid) || empty($status)) return false;
        $customer_id = "";
        if (!empty($email)){
            $customer_id =  $this->is_customer_exists($email);
            $customer_id = !empty($customer_id) ? $customer_id : $this->get_customer_id();
        }
        $now = time();

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
        //$this->addETicketActivity($tid, $user, 'S', $status); //// added into updateLogEmailSession

        $sql = "UPDATE e_ticket_info SET ";
        $sql .= " status_updated_by='$user', status='$status', ";
        $sql .= !empty($isSend) ? " num_mails=num_mails+1, typing='', `current_user`='', " : "";
        $sql .= "last_update_time='$now', customer_id='$customer_id' ";
        $sql .= !empty($ticketinfo)?$this->updateETicketInfoSql($ticketinfo, $role_id, $user, $status):'';
        $sql .= $status=='R'? ", rs_tr='Y' ,reschedule_time= UNIX_TIMESTAMP('$reschedule_date_time'), rs_tr_create_time='$now'" : "";
        $sql .= !empty($callid)? " ,acd_agent='$user', acd_status='$status' ":"";
        $sql .= " WHERE ticket_id='$tid'";
        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function createNewEmail($agent_id, $skill_name, $skill_email, $name, $email, $cc, $bcc, $did, $subject, $mail_body, $status, $skill_id, $ccmails, $att_files)
    {
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            $i++;
        }
        if (empty($id)) return false;

        $email_cc = !empty($cc) ? implode(",", $cc) :'';
        $email_bcc = !empty($bcc) ? implode(",", $bcc) : '';

        $has_attachment = 'N';
        if (is_array($att_files['error'])) {
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }
        }

        $now = time();
        $email_fetch_inbox_name = self::getEmailFetchInboxName($skill_email);
        $email_fetch_inbox_name = !empty($email_fetch_inbox_name) ? $email_fetch_inbox_name : $skill_name;

        $subject = '['. $id .'] ' . $subject;
        $subject = nl2br(base64_encode($subject));
        $mail_body = nl2br(base64_encode($mail_body));
        $full_name = explode(' ',$name);
        $first_name = reset($full_name);
        $last_name = end($full_name);
        $customer_id =  $this->is_customer_exists($email);
        $customer_id = !empty($customer_id) ? $customer_id : $this->get_customer_id();
        $phone = $this->get_phone_number_from_email($subject);
        $phone = !empty($phone) ? $phone : $this->get_phone_number_from_email($mail_body);
        $created_for_email = explode(",", $email);
        $session_id = $id.'001';
        $in_kpi = !empty($status) && ($status=="S" || $status=="E") ? "Y" : "N";
        $close_time = !empty($status) && ($status=="S" || $status=="E") ? $now : "0";
        $sql = "INSERT INTO e_ticket_info SET ticket_id='$id', skill_id='$skill_id', subject='$subject', status='$status', ".
            "mail_to='$skill_email', created_by='$agent_id', created_for='$created_for_email[0]', assigned_to='', status_updated_by='$agent_id', ".
            "num_mails='1', disposition_id='$did', last_update_time='$now', create_time='$now', ".
            "first_name='$first_name', last_name='$last_name', phone='$phone', customer_id='$customer_id', agent_id='$agent_id', ".
            " in_kpi='$in_kpi', first_open_time='$now', closed_time='$close_time', session_id='$session_id', fetch_box_name='$email_fetch_inbox_name', fetch_box_email='$skill_email'";
        $is_update = $this->getDB()->query($sql);

        if ($is_update) {
            $from = empty($name) ? $created_for_email[0] : "$name <$created_for_email[0]>";
            $sql = "INSERT INTO email_messages SET ticket_id='$id', mail_sl='001', original_mail_id='', ".
                "subject='$subject', from_name='$name', from_email='$skill_email', mail_from='$from', ".
                "mail_to='$email', mail_cc='$email_cc', mail_bcc='$email_bcc', agent_id='$agent_id',  ".
                "reference='', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N', ".
                "phone='$phone', email_status='$status', email_did='$did', session_id='$session_id'";
            $this->getDB()->query($sql);

            //first decide who will receive the mail
            $sql = "INSERT INTO emails_out SET ".
                "ticket_id='$id', ".
                "mail_sl='001', ".
                "subject='$subject', ".
                "from_name='$email_fetch_inbox_name', ".
                "from_email='$skill_email', ".
                "to_email='$email', ".
                "to_name='$name', ".
                "cc='$email_cc', ".
                "bcc='$email_bcc', ".
                "body='$mail_body', ".
                "has_attachment='$has_attachment', ".
                "is_forward='N', ".
                "tstamp='".$now."'";
            $this->getDB()->query($sql);

            $this->addETicketActivity($id, $agent_id, 'M', '001');
            $this->addETicketActivity($id, $agent_id, 'S', $status);
            if (!empty($did)) {
                $this->addETicketActivity($id, $agent_id, 'D', $did);
            }
            if ($has_attachment=='Y'){
                $this->saveAttachment($has_attachment, $att_files, $id, "001", $now);
            }
            /// Log Email Session /////
            $this->generateLogEmailSession($id);
        }

        return $is_update;
    }

    function random_digits($num_digits)
    {
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
    }

    function get_phone_number_from_email($text){
        if (strlen($text) > 0){
            $text = strip_tags($text);
            //$text = str_replace("\xc2\xa0",' ',$text);
            preg_match_all('/ [0-9]{5}[\-][0-9]{6}|[\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6}|[0-9]{11}|[\+][0-9]{13}|[\+][0-9]{7}[\-][0-9]{6}|[\+][0-9]{3}[\-][0-9]{8} /', $text, $matches);
            //preg_match_all('/[0-9]{11}/', $text, $matches);
            //preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
            //echo 'number</br>';GPrint($matches);echo '</br>';
            $matches = !empty($matches[0]) ? $matches[0][0] : '';
            return $matches;
        }
        return '';
    }

    /*function getInboxEmail(){
        $response = [];
        $sql = "SELECT username FROM email_fetch_inboxes";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            foreach ($result as $key){
                $response[] = $key->username;
            }
        }
        return $response;
    }*/
    /*function getNewTicketId(){
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            else $id = '';
            $i++;
        }
        return $id;
    }*/

    function getEmailSerialNo($email_id){
        $sql = "Select count(ticket_id) as numEmail FROM email_messages WHERE ticket_id='$email_id'";
        $result = $this->getDB()->query($sql);
        $sl = $result[0]->numEmail + 1;
        return sprintf("%03d", $sl);
    }

    private function getEmailMessagesFields(){
        $obj = new stdClass();
        $obj->ticket_id=''; $obj->original_mail_id=''; $obj->mail_sl=''; $obj->subject=''; $obj->from_name=''; $obj->from_email='';
        $obj->mail_from=''; $obj->mail_to=''; $obj->mail_cc=''; $obj->mail_bcc=''; $obj->reference=''; $obj->mail_body='';
        $obj->has_attachment=''; $obj->is_forward=''; $obj->agent_id=''; $obj->status=''; $obj->tstamp=''; $obj->send_time='';
        $obj->answer_time=''; $obj->stop_time=''; $obj->duration=''; $obj->phone=''; $obj->email_status=''; $obj->email_did='';
        $obj->acd_agent=''; $obj->acd_status=''; $obj->single_reply=''; $obj->session_id='';
        return $obj;
    }
    private function getEmailsOutFields(){
        $obj = new stdClass();
        $obj->ticket_id='';$obj->mail_sl='';$obj->from_email='';$obj->from_name='';$obj->to_email='';$obj->to_name='';$obj->cc='';
        $obj->bcc='';$obj->subject='';$obj->body='';$obj->has_attachment='';$obj->is_forward='';$obj->tstamp='';$obj->single_reply='';
        return $obj;
    }
    function setPropertyKeyValue($table_object, $data_object){
        $str = "";
        if (!empty($data_object)){
            $data_array = (array)$data_object;
            foreach ($table_object as $property => $vl){
                if (array_key_exists($property, $data_array)){
                    $str .= $property."='".$data_array[$property]."', ";
                }
            }
            $str = rtrim(trim($str),",");
        }
        return $str;
    }
    function saveEmailMessage($post_data){
        if(empty($post_data->ticket_id) || empty($post_data->mail_sl))
            return false;

        $value = $this->setPropertyKeyValue($this->getEmailMessagesFields(), $post_data);
        if (!empty($value)){
            $sql = "INSERT INTO email_messages SET ".$value;
            return $this->getDB()->query($sql);
        }
        return false;
    }
    function saveEmailsOut($post_data){
        $post_data->to_email = $post_data->mail_to;
        $post_data->cc = $post_data->mail_cc;
        $post_data->bcc = $post_data->mail_bcc;
        $post_data->body = $post_data->mail_body;
        $value = $this->setPropertyKeyValue($this->getEmailsOutFields(), $post_data);
        if (!empty($value)){
            $sql = "INSERT INTO emails_out SET ".$value;
            return $this->getDB()->query($sql);
        }
        return false;
    }

    function addEmailMsg($post_data, $file, $attachment_save_path='', $emailInfo=null){
        $sl = $this->getEmailSerialNo($post_data->ticket_id);
        $post_data->mail_sl = $sl;
        $has_attachment = 'N';
        $activity_type = $post_data->is_forward=="Y" ? "F" : "M";
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
            if ($post_data->has_attachment == "Y"){
                $this->saveAttachmentById($post_data, $file, $attachment_save_path, $has_att_files_no_error);
            }
            $this->addETicketActivity($post_data->ticket_id, $post_data->agent_id, $activity_type, $post_data->mail_sl);
            return $post_data;
        }
        return false;
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
    function saveAttachmentById($post_data, $file, $attachment_save_path, $has_att_files_no_error){
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
        $response = false;
        if ($has_att_files_no_error) {
            foreach ($file['error'] as $err) {
                if ($err == 0) {
                    $fname = $file["name"][$i];
                    $fsubtype = strtoupper(end((explode(".", $fname))));
                    $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                    $attachment_i = $i + 1;
                    move_uploaded_file($file["tmp_name"][$i], $dir . $fname);
                    $fname = $this->getDB()->escapeString($fname);
                    $sql = "INSERT INTO email_attachments SET ticket_id='$post_data->ticket_id', mail_sl='$post_data->mail_sl', attach_type='$ftype', attach_subtype='$fsubtype', ";
                    $sql .= "part_position='$attachment_i', file_name='$fname'";
                    $response = $this->getDB()->query($sql);
                }
                $i++;
            }
        }

        if (!empty($post_data->forwarded_attachment_hidden)){
            $attachment_i = $attachment_i==0 ? 1 : $attachment_i;
            $response = $this->saveForwardedAttachment($post_data->forwarded_attachment_hidden, $attachment_i, $post_data->mail_sl, $attachment_save_path, $dir);
        }
        return $response;
    }

//    function addEmailMsgOld( $emailInfo, $msgBody, $att_files, $forward, $cc, $bcc, $ccmails, $agentid, $email_status, $email_did=null, $role_id, $callid, $specific_email_sl, $forwarded_attachment_file=null, $is_reply_all=null){
//        if (empty($emailInfo->ticket_id)) return false;
//        //GPrint($emailInfo);die;
//        $phone_number = $this->get_phone_number_from_email($msgBody);
//        $allowed_mails = array();
//        if (is_array($ccmails)) {
//            foreach ($ccmails as $ccmail) $allowed_mails[] = $ccmail->email;
//        }
//
//        $sql = "Select count(ticket_id) as numEmail FROM email_messages WHERE ticket_id='$emailInfo->ticket_id'";
//        $result = $this->getDB()->query($sql);
//        $sl = $result[0]->numEmail + 1;
//        $has_attachment = 'N';
//        $is_forward = 'N';
//        $has_att_files_no_error = true;
//        if (is_array($att_files['error'])) {
//            foreach ($att_files['error'] as $err) {
//                if ($err == 0) {
//                    $has_attachment = 'Y';
//                }
//            }
//        }
//        if (!empty($forwarded_attachment_file)){
//            if (is_array($att_files['error']) && $has_attachment == 'N'){
//                $has_att_files_no_error = false;
//            }
//            $has_attachment = 'Y';
//        }
//
//        $sl = sprintf("%03d", $sl);
//        $now = time();
//
//        $secondsPerMinute = 60;
//        $kpi_accepted_mins = IN_KPI_TIME; /// 24 hours = 1440 mins;
//        $open_duration = ($now - $emailInfo->first_open_time);
//
//        $email_to = $emailInfo->created_for;
//        $email_cc = '';
//        $email_bcc = '';
//        $activity_type = 'M';
//
//        if (!empty($forward)) {
//            //if (in_array($forward, $allowed_mails)) {
//            $email_to = $forward;
//            $is_forward = 'Y';
//            $activity_type = 'F';
//            //}
//        }
//        if (!empty($is_reply_all) && $is_reply_all=="Y"){
//            $temp = !empty($emailInfo->mail_to) ? explode(",",$emailInfo->mail_to) : '';
//            $temp[] = $emailInfo->created_for;
//            if (!empty($emailInfo->fetch_box_email)){
//                $idx = in_array($emailInfo->fetch_box_email, $temp) ? array_search($emailInfo->fetch_box_email, $temp) : '';
//                if ($idx !== FALSE){
//                    unset($temp[$idx]);
//                }
//                $temp = !empty($temp) ? implode(",",$temp) : '';
//                $email_to = rtrim(trim($temp),',');
//            }
//        }
//
//        if (!empty($cc)) {
//            foreach ($cc as $_cc) {
//                if (!empty($email_cc)) $email_cc .= ',';
//                $email_cc .= $_cc;
//            }
//        }
//
//        if (!empty($bcc)) {
//            foreach ($bcc as $_bcc) {
//                if (!empty($email_bcc)) $email_bcc .= ',';
//                $email_bcc .= $_bcc;
//            }
//        }
//
//        $email_fetch_inbox_name = !empty($emailInfo->fetch_box_name) ? $emailInfo->fetch_box_name : self::getEmailFetchInboxName($emailInfo->mail_to);
//        $msgBody = base64_encode($msgBody);
//        $sql = "INSERT INTO email_messages SET ";
//        $sql .="ticket_id='$emailInfo->ticket_id', ";
//        $sql .="mail_sl='$sl', ";
//        $sql .="subject='$emailInfo->subject', ";
//        //$sql .="from_name='$emailInfo->skill_name', ";
//        $sql .="from_name='$email_fetch_inbox_name', ";
//
////			$sql .="from_email='$emailInfo->mail_to', ";
////  		$sql .="mail_from='$emailInfo->mail_to', ";
//        $sql .="from_email='$emailInfo->fetch_box_email', ";
//        $sql .="mail_from='$emailInfo->fetch_box_email', ";
//
//        $sql .="mail_to='$email_to', ";
//        $sql .="mail_cc='$email_cc', ";
//        $sql .="mail_bcc='$email_bcc', ";
//        $sql .="mail_body='$msgBody', ";
//        $sql .="status='O', ";
//        $sql .="tstamp='".$now."', ";
//        $sql .="has_attachment='$has_attachment', ";
//        $sql .="is_forward='$is_forward', ";
//        $sql .="agent_id='$agentid', ";
//        $sql .="reference='', "; //updated by none2
//        $sql .="phone='$phone_number', ";
//        $sql .="single_reply='$specific_email_sl', ";
//        $sql .=!empty($email_status) ? "email_status='$email_status', " : "email_status='$emailInfo->email_status', ";
//        $sql .=!empty($email_did) ? " email_did='$email_did', " : " email_did='$emailInfo->email_did', ";
//        $sql .=!empty($callid) ? " acd_agent='$agentid', " : " ";
//        if (!empty($callid)){
//            $sql .=!empty($email_status) ? " acd_status='$email_status'," : " acd_status='$emailInfo->email_status',";
//        }
//        $sql = rtrim(trim($sql),',');
//        if ($this->getDB()->query($sql)) {
//
//            $sql = "INSERT INTO emails_out SET ";
//            $sql .= "ticket_id='$emailInfo->ticket_id', ";
//            $sql .= "mail_sl='$sl', ";
//            $sql .= "subject='$emailInfo->subject', ";
////			$sql .= "from_name='$emailInfo->skill_name', ";
//            $sql .= "from_name='$email_fetch_inbox_name', ";
////			$sql .= "from_email='$emailInfo->mail_to', ";
//            $sql .= "from_email='$emailInfo->fetch_box_email', ";
//            $sql .= "to_email='$email_to', ";
//            $sql .= "cc='$email_cc', ";
//            $sql .= "bcc='$email_bcc', ";
//            $sql .= "body='$msgBody', ";
//            $sql .= "has_attachment='$has_attachment', ";
//            $sql .= "is_forward='$is_forward', ";
//            $sql .= "tstamp='".$now."', ";
//            $sql .= "single_reply='$specific_email_sl'";
//            $this->getDB()->query($sql);
//
//            $sql = "UPDATE e_ticket_info SET  ";
//            //$sql .= ($isAutoReply && $sl == "002") ? "num_mails = 3, " : "num_mails = num_mails+1, ";
//            $sql .= "num_mails = num_mails+1, ";
//            $sql .= "last_update_time = '".time()."', ";
//            $sql .= "status_updated_by = '$agentid', ";
//            //////New///
//            $sql .= " typing='', ";
//            $sql .= " `current_user`='' ";
//            $sql .= $this->updateETicketInfoSql( $emailInfo, $role_id, $agentid, $email_status);
//
//            //////New///
//            $sql .= "WHERE ticket_id='$emailInfo->ticket_id'";
//            $is_eticket_update = $this->getDB()->query($sql);
//
//
//            $i = 0;
//            $attachment_save_path = '';
//            if ($has_attachment == 'Y') {
//                include('conf.email.php');
//                //include('model/MCcSettings.php');
//                $ccsettings_model = new MCcSettings();
//                $email_settings = $ccsettings_model->getEmailSettings();
//                if (!empty($email_settings)){
//                    foreach ($email_settings as $itm){
//                        if ($itm->item == "attachment_save_path"){
//                            $attachment_save_path = base64_decode($itm->value);
//                        }
//                    }
//                }
//
//                $yy = date("y", $now);
//                $mm = date("m", $now);
//                $dd = date("d", $now);
//                $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailInfo->ticket_id . '/' . $sl . '/';
//                if (!is_dir($dir)) mkdir($dir, 0750, true);
//                $attachment_i = 0;
//                if ($has_att_files_no_error){
//                    foreach ($att_files['error'] as $err) {
//                        if ($err == 0) {
//                            //$has_attachment = 'Y';
//                            $fname = $att_files["name"][$i];
//                            $fsubtype = strtoupper( end((explode(".", $fname))) );
//                            $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
//                            $attachment_i = $i+1;
//                            $fname = $this->getDB()->escapeString($fname);
//                            move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);
//                            $sql = "INSERT INTO email_attachments SET ticket_id='$emailInfo->ticket_id', mail_sl='$sl', attach_type='$ftype', attach_subtype='$fsubtype', ".
//                                "part_position='$attachment_i', file_name='$fname'";
//                            $this->getDB()->query($sql);
//                        }
//                        $i++;
//                    }
//                }
//
//                if (!empty($forwarded_attachment_file)){
//                    $attachment_i = $attachment_i==0 ? 1 : $attachment_i;
//                    $sql = $this->getForwardedAttachmentFileDetails($forwarded_attachment_file, $attachment_i, $sl);
//                    if (!empty($sql)){
//                        foreach ($forwarded_attachment_file as $item){
//                            $old_file = $attachment_save_path.'/'.$item;
//                            $name = explode("/", $item);
//                            $new_fname = end($name);
//                            $rnd = copy($old_file, $dir.$new_fname);
//                        }
//                        $this->getDB()->query($sql);
//                    }
//                }
//            }
//            $this->addETicketActivity($emailInfo->ticket_id, $agentid, $activity_type, $sl);
//            return true;
//        }
//        return false;
//    }



//    function addEmailMsgMulti( $emailInfo, $msgBody, $att_files, $forward, $cc, $bcc, $ccmails, $agentid, $email_status, $email_did=null, $role_id, $callid, $specific_email_sl, $forwarded_attachment_file=null, $is_reply_all=null, $session_id=null ){
//        if (empty($emailInfo->ticket_id)) return false;
//        $phone_number = $this->get_phone_number_from_email($msgBody);
//        $allowed_mails = array();
//        if (is_array($ccmails)) {
//            foreach ($ccmails as $ccmail) $allowed_mails[] = $ccmail->email;
//        }
//        $sql = "Select count(ticket_id) as numEmail FROM email_messages WHERE ticket_id='$emailInfo->ticket_id'";
//        $result = $this->getDB()->query($sql);
//        $sl = $result[0]->numEmail + 1;
//
//        $has_attachment = 'N';
//        $is_forward = 'N';
//        $has_att_files_no_error = true;
//        if (is_array($att_files['error'])) {
//            foreach ($att_files['error'] as $err) {
//                if ($err == 0) {
//                    $has_attachment = 'Y';
//                }
//            }
//        }
//        if (!empty($forwarded_attachment_file)){
//            if (is_array($att_files['error']) && $has_attachment == 'N'){
//                $has_att_files_no_error = false;
//            }
//            $has_attachment = 'Y';
//        }
//
//        $sl = sprintf("%03d", $sl);
//        $now = time();
//
//        $secondsPerMinute = 60;
//        $kpi_accepted_mins = IN_KPI_TIME; /// 24 hours = 1440 mins;
//        $open_duration = ($now - $emailInfo->first_open_time);
//
//        $email_to = $emailInfo->created_for;
//        $email_cc = '';
//        $email_bcc = '';
//        $activity_type = 'M';
//
//        if (!empty($forward)) {
//            $email_to = $forward;
//            $is_forward = 'Y';
//            $activity_type = 'F';
//        }
//        if (!empty($is_reply_all) && $is_reply_all=="Y"){
//            $temp = !empty($emailInfo->mail_to) ? explode(",",$emailInfo->mail_to) : '';
//            $temp[] = $emailInfo->created_for;
//            if (!empty($emailInfo->fetch_box_email)){
//                $idx = in_array($emailInfo->fetch_box_email, $temp) ? array_search($emailInfo->fetch_box_email, $temp) : '';
//                if ($idx !== FALSE){
//                    unset($temp[$idx]);
//                }
//                $temp = !empty($temp) ? implode(",",$temp) : '';
//                $email_to = rtrim(trim($temp),',');
//            }
//        }
//
//        if (!empty($cc)) {
//            foreach ($cc as $_cc) {
//                if (!empty($email_cc)) $email_cc .= ',';
//                $email_cc .= $_cc;
//            }
//        }
//
//        if (!empty($bcc)) {
//            foreach ($bcc as $_bcc) {
//                if (!empty($email_bcc)) $email_bcc .= ',';
//                $email_bcc .= $_bcc;
//            }
//        }
//        $email_fetch_inbox_name = !empty($emailInfo->fetch_box_name) ? $emailInfo->fetch_box_name : self::getEmailFetchInboxName($emailInfo->mail_to);
//        $msgBody = base64_encode($msgBody);
//        $sql = "INSERT INTO email_messages SET ";
//        $sql .="ticket_id='$emailInfo->ticket_id', ";
//        $sql .="mail_sl='$sl', ";
//        $sql .="subject='$emailInfo->subject', ";
//        $sql .="from_name='$email_fetch_inbox_name', ";
//        $sql .="from_email='$emailInfo->fetch_box_email', ";
//        $sql .="mail_from='$emailInfo->fetch_box_email', ";
//        $sql .="mail_to='$email_to', ";
//        $sql .="mail_cc='$email_cc', ";
//        $sql .="mail_bcc='$email_bcc', ";
//        $sql .="mail_body='$msgBody', ";
////        $sql .="status='O', ";
//        $sql .="status='', ";
//        $sql .="tstamp='".$now."', ";
//        $sql .="has_attachment='$has_attachment', ";
//        $sql .="is_forward='$is_forward', ";
//        $sql .="agent_id='$agentid', ";
//        $sql .="reference='', "; //updated by none2
//        $sql .="phone='$phone_number', ";
//        $sql .="single_reply='$specific_email_sl', ";
//        $sql .="session_id='$session_id', ";
//        //$sql .="email_status='$email_status', " ;
//        $sql .=!empty($email_status) ? "email_status='$email_status', " : "email_status='$emailInfo->email_status', ";
//        $sql .=!empty($email_did) ? " email_did='$email_did', " : " email_did='$emailInfo->email_did', ";
//        $sql .=!empty($callid) ? " acd_agent='$agentid', " : " ";
//        if (!empty($callid)){
//            $sql .=!empty($email_status) ? " acd_status='$email_status'," : " acd_status='$emailInfo->email_status',";
//        }
//        $sql = rtrim(trim($sql),',');
//        if ($this->getDB()->query($sql)) {
//            $sql = "INSERT INTO emails_out SET ";
//            $sql .= "ticket_id='$emailInfo->ticket_id', ";
//            $sql .= "mail_sl='$sl', ";
//            $sql .= "subject='$emailInfo->subject', ";
//            $sql .= "from_name='$email_fetch_inbox_name', ";
//            $sql .= "from_email='$emailInfo->fetch_box_email', ";
//            $sql .= "to_email='$email_to', ";
//            $sql .= "cc='$email_cc', ";
//            $sql .= "bcc='$email_bcc', ";
//            $sql .= "body='$msgBody', ";
//            $sql .= "has_attachment='$has_attachment', ";
//            $sql .= "is_forward='$is_forward', ";
//            $sql .= "tstamp='".$now."', ";
//            $sql .= "single_reply='$specific_email_sl'";
//            $this->getDB()->query($sql);
//
//            $sql = "UPDATE e_ticket_info SET  ";
//            $sql .= "num_mails = num_mails+1, ";
//            $sql .= "last_update_time = '".time()."', ";
//            $sql .= "status_updated_by = '$agentid', ";
//            //////New///
//            $sql .= " typing='', ";
//            $sql .= " `current_user`='' ";
//            $sql .= $this->updateETicketInfoSql( $emailInfo, $role_id, $agentid, $email_status);
//            //////New///
//            $sql .= "WHERE ticket_id='$emailInfo->ticket_id'";
//            $is_eticket_update = $this->getDB()->query($sql);
//            $i = 0;
//            $attachment_save_path = '';
//            if ($has_attachment == 'Y') {
//                include('conf.email.php');
//                $ccsettings_model = new MCcSettings();
//                $email_settings = $ccsettings_model->getEmailSettings();
//                if (!empty($email_settings)){
//                    foreach ($email_settings as $itm){
//                        if ($itm->item == "attachment_save_path"){
//                            $attachment_save_path = base64_decode($itm->value);
//                        }
//                    }
//                }
//
//                $yy = date("y", $now);
//                $mm = date("m", $now);
//                $dd = date("d", $now);
//                $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailInfo->ticket_id . '/' . $sl . '/';
//                if (!is_dir($dir)) mkdir($dir, 0750, true);
//                $attachment_i = 0;
//                if ($has_att_files_no_error){
//                    foreach ($att_files['error'] as $err) {
//                        if ($err == 0) {
//                            $fname = $att_files["name"][$i];
//                            $fsubtype = strtoupper( end((explode(".", $fname))) );
//                            $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
//                            $attachment_i = $i+1;
//
//                            move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);
//                            $sql = "INSERT INTO email_attachments SET ticket_id='$emailInfo->ticket_id', mail_sl='$sl', attach_type='$ftype', attach_subtype='$fsubtype', ".
//                                "part_position='$attachment_i', file_name='$fname'";
//                            $this->getDB()->query($sql);
//                        }
//                        $i++;
//                    }
//                }
//
//                if (!empty($forwarded_attachment_file)){
//                    $attachment_i = $attachment_i==0 ? 1 : $attachment_i;
//                    $sql = $this->getForwardedAttachmentFileDetails($forwarded_attachment_file, $attachment_i, $sl);
//                    if (!empty($sql)){
//                        foreach ($forwarded_attachment_file as $item){
//                            $old_file = $attachment_save_path.'/'.$item;
//                            $name = explode("/", $item);
//                            $new_fname = end($name);
//                            $rnd = copy($old_file, $dir.$new_fname);
//                        }
//                        $this->getDB()->query($sql);
//                    }
//                }
//            }
//
//            $this->addETicketActivity($emailInfo->ticket_id, $agentid, $activity_type, $sl);
//            return true;
//        }
//        return false;
//    }

    function getChatdispositionChildrenOptions($skillid='', $root='')
    {
        $options = array();
        $sql = "SELECT disposition_id, title FROM skill_disposition_code WHERE parent_id='$root' ";
        $sql .= " AND status = 'Y'";
        if (!empty($skillid)) $sql .= "AND skill_id='$skillid' ";
        $sql .= "ORDER BY title ASC";
        $result = $this->getDB()->query($sql);
        //echo $sql;
        if (is_array($result)) {
            foreach ($result as $row) {
                $options[$row->disposition_id] = $row->title;
            }
        }

        return $options;
    }

    function getEmailBySkillId($skill_id='')
    {
        $sql = "SELECT * FROM email_cc_bcc_list WHERE skill_id='' OR skill_id='$skill_id' ORDER BY name ";
        return $this->getDB()->query($sql);
    }

    function getChatTemplateBySkillId($sid)
    {
        $sql = "SELECT * FROM chat_msg_templates WHERE skill_id IN ('$sid') ORDER BY title ";
        return $this->getDB()->query($sql);
    }

    function getDispositionByIds($dids) {
        $sql = "SELECT * FROM skill_disposition_code WHERE disposition_id IN ('$dids') ";
        return $this->getDB()->query($sql);
    }

    function addDispositionModel($callid, $did, $tstamp, $agent_id, $note, $email, $contact, $web_site_url, $user_arival_duration, $service_id="")
    {
        log_text(array($callid,"Enter save disposition model"));
        $sql = "SELECT * FROM chat_detail_log WHERE callid='".$callid."'";
        $result = $this->getDB()->query($sql);
        if(is_array($result)==0) {
            $sql = "INSERT INTO chat_detail_log SET ".
                "callid='".$callid."', ".
                "disposition_id='".$did."', ".
                "tstamp='".$tstamp."', ".
                "agent_id='".$agent_id."', ".
                "note='".$this->getDB()->escapeString($note)."', ".
                "email='".$email."', ".
                "contact_number='".$contact."', ".
                "service_id='".$service_id."', ".
                "url='".$this->getDB()->escapeString($web_site_url)."', ".
                "url_duration='".$user_arival_duration."'";
        } else {
            $sql = "UPDATE chat_detail_log SET ".
                "disposition_id='".$did."', ".
                "tstamp='".$tstamp."', ".
                "agent_id='".$agent_id."', ".
                "note='".$this->getDB()->escapeString($note)."', ".
                "email='".$email."', ".
                "contact_number='".$contact."', ".
                "service_id='".$service_id."', ".
                "url='".$this->getDB()->escapeString($web_site_url)."', ".
                "url_duration='".$user_arival_duration."' ".
                " WHERE callid='".$callid."'";
        }
        log_text(array($callid,"After save disposition model",$sql));
        return $this->getDB()->query($sql);
    }

    function updateChatSkillEmail($skillid, $name, $email, $old_skillid='')
    {
        if(empty($old_skillid)) {
            $sql = "INSERT INTO email_signature SET ".
                "skill_id='".$skillid."', ".
                "from_email='".$email."', ".
                "from_name='".$name."', ".
                "status='Y'";
        } else {
            $sql = "UPDATE email_signature SET ".
                "from_email='".$email."', ".
                "from_name='".$name."', ".
                "status='Y' ".
                "WHERE skill_id='".$skillid."' LIMIT 1";
        }

        return $this->getDB()->query($sql);
    }

    function getEmailDispositionByTicketId($ticket_id){
        $response = null;
        $sql  = "SELECT title FROM skill_crm_disposition_code  ";
        $sql .= " LEFT JOIN e_ticket_info ON e_ticket_info.disposition_id = skill_crm_disposition_code.disposition_id ";
        $sql .= " WHERE e_ticket_info.ticket_id = '$ticket_id' ";
        $result =  $this->getDB()->query($sql);
        if (is_array($result)) {
            $response = $result[0]->title;
        }
        return $response;
    }

    public function ticket_category_exists($title)
    {
        $query = "SELECT COUNT(*) AS total_category FROM ticket_category WHERE title='{$title}'";
        $result =  $this->getDB()->query($query);
        return $result[0]->total_category > 0 ? TRUE : FALSE;
    }

    public function addTicketCategory($title = '', $status = 'N')
    {
        $id = $this->GetNewIncId("category_id", "AA", "ticket_category");
        if (empty($id) || empty($title)) return FALSE;

        $query = "INSERT INTO ticket_category (category_id,title,status) VALUES('{$id}','{$title}','{$status}')";
        //return $this->getDB()->query($query);
        if ($this->getDB()->query($query)) {
            //$ltxt = $status=='A' ? 'Inactive to Active' : 'Active to Inactive';
            $this->addToAuditLog("ticket category", 'A', 'category_id' , "category_id=".$id);
            return true;
        }
        return false;
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

    function updateTicketCategoryStatus( $id,$status='')
    {
        if (!empty($id) && ($status=='A' || $status=='I')) {
            //$sql = "UPDATE agents SET active='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
            $sql = "UPDATE ticket_category SET `status`='$status' WHERE category_id='$id'";
            if ($this->getDB()->query($sql)) {
                $ltxt = $status=='A' ? 'Inactive to Active' : 'Active to Inactive';
                $this->addToAuditLog("ticket category", 'U', $id , "Status=".$ltxt);
                return true;
            }
        }
        return false;
    }

    function getTicketCategoryByID($category_id = null){
        if (!empty($category_id)){
            $sql = "SELECT * FROM ticket_category WHERE category_id = '$category_id' ";
            $sql .= "LIMIT 1";
            $return = $this->getDB()->query($sql);
            if (is_array($return)) return $return[0];
            //return null;
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
        //GPrint($category_id);die;
        return false;
    }

    static function addDomain($skill_id=null, $domain=null){
        if (!empty($skill_id) && !empty($domain)){
            $query = "INSERT INTO email_domain2skill (skill_id, domain) VALUES('{$skill_id}','{$domain}')";
            //die($query);
            $obj = new static();
            if ($obj->getDB()->query($query)) {
                $obj->addToAuditLog("add skill_domain", 'A', 'skill_domain' , "skill_id=".$skill_id);
                return true;
            }
        }
        return false;
    }
    static function updateDoman($data ,$skill_id, $domain){
        if (!empty($data) && !empty($skill_id) && !empty($domain)){
            $obj = new static();
            $query = "UPDATE email_domain2skill SET domain='{$data}' WHERE domain = '{$domain}' AND skill_id = '{$skill_id}'";
            if ($obj->getDB()->query($query)) {
                $obj->addToAuditLog("add skill_domain", 'A', 'skill_domain' , "skill_id=".$skill_id);
                return true;
            }
        }
        return false;
    }

    static function isDomainExists($skill_id, $domain){
        $obj = new static();
        $query = "SELECT COUNT(*) AS total_domain FROM email_domain2skill WHERE domain='{$domain}'";
        $result =  $obj->getDB()->query($query);
        return $result[0]->total_domain > 0 ? TRUE : FALSE;
    }

    function numSkillDomain($skill_id){
        $cond = "";
        if (!empty($skill_id)) {
            $cond .= " skill_id='$skill_id'";
        }
        /*if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "status='$status'";
        }*/

        $sql = "SELECT COUNT(*) AS numrows FROM email_domain2skill ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getSkillDomain($skill_id, $limit=0, $offset=0){
        $cond = "";
        if (!empty($skill_id)) {
            $cond .= " skill_id='$skill_id'";
        }

        $sql = "SELECT * FROM email_domain2skill ";
        if (!empty($cond)) $sql .= "WHERE $cond ";

        $sql .= "ORDER BY domain ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->query($sql);
    }

    static function deleteSkillDomain($skill_id, $domain){
        if (!empty($skill_id) && !empty($domain)){
            $obj = new static();
            $sql = "DELETE FROM email_domain2skill WHERE domain = '{$domain}' AND skill_id = '{$skill_id}' LIMIT 1";
            if ($obj->getDB()->query($sql)) {
                $obj->addToAuditLog('Skill Domain', 'D', "Skill ID=$skill_id", "Domain=".$domain);
                return true;
            }
        }
        return false;
    }

    static function getSkillDomainById($skill_id, $domain){
        if (!empty($skill_id) && !empty($domain)){
            $obj = new static();
            $sql = "SELECT * FROM email_domain2skill WHERE domain = '{$domain }' AND skill_id = '{$skill_id}'";
            //echo $sql;die;
            return $obj->getDB()->query($sql);
        }
        return false;
    }

    static function numSkillKeyword($skill_id){
        $cond = "";
        if (!empty($skill_id)) {
            $cond .= " skill_id='$skill_id'";
        }
        $obj = new static();
        $sql = "SELECT COUNT(*) AS numrows FROM email_routing ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $obj->getDB()->query($sql);

        if($obj->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    static function getSkillKeyword($skill_id, $limit=0, $offset=0){
        $cond = "";
        if (!empty($skill_id)) {
            $cond .= " skill_id='$skill_id'";
        }

        $sql = "SELECT * FROM email_routing ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY keyword ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        $obj = new static();
        return $obj->getDB()->query($sql);
    }

    static function isKeywordExists($keyword){
        $obj = new static();
        $query = "SELECT COUNT(*) AS total_keyword FROM email_routing WHERE keyword='{$keyword}'";
        $result =  $obj->getDB()->query($query);
        return $result[0]->total_keyword > 0 ? TRUE : FALSE;
    }

    static function addKeyword($skill_id=null, $keyword=null){
        if (!empty($skill_id) && !empty($keyword)){
            $query = "INSERT INTO email_routing (skill_id, keyword) VALUES('{$skill_id}','{$keyword}')";
            //die($query);
            $obj = new static();
            if ($obj->getDB()->query($query)) {
                $obj->addToAuditLog("add skill_keyword", 'A', 'skill_keyword' , "keyword=".$keyword);
                return true;
            }
        }
        return false;
    }

    static function getSkillKeywordById($skill_id, $keyword){
        if (!empty($skill_id) && !empty($keyword)){
            $obj = new static();
            $sql = "SELECT * FROM email_routing WHERE keyword = '{$keyword}' AND skill_id = '{$skill_id}'";
            //echo $sql;die;
            return $obj->getDB()->query($sql);
        }
        return false;
    }

    static function updateKeyword($data ,$skill_id, $keyword){
        if (!empty($data) && !empty($skill_id) && !empty($keyword)){
            $obj = new static();
            $query = "UPDATE email_routing SET keyword='{$data}' WHERE keyword = '{$keyword}' AND skill_id = '{$skill_id}'";
            if ($obj->getDB()->query($query)) {
                $obj->addToAuditLog("add skill_keyword", 'A', 'skill_keyword' , "keyword=".$data);
                return true;
            }
        }
        return false;
    }

    static function deleteSkillKeyword($skill_id, $keyword){
        if (!empty($skill_id) && !empty($keyword)){
            $obj = new static();
            $sql = "DELETE FROM email_routing WHERE keyword = '{$keyword}' AND skill_id = '{$skill_id}' LIMIT 1";
            if ($obj->getDB()->query($sql)) {
                $obj->addToAuditLog('Skill Keyword', 'D', "Skill ID=$skill_id", "Keyword=".$keyword);
                return true;
            }
        }
        return false;
    }

    function getLastTicketUpdater($ticket_ids){
        $sql = "SELECT em.ticket_id, em.agent_id, ag.nick FROM email_messages em ";
        $sql .= " LEFT JOIN agents AS ag ON ag.agent_id = em.agent_id ";
        $sql .= " INNER JOIN ";
        $sql .= " (SELECT ticket_id, MAX(mail_sl) AS max_sl FROM email_messages WHERE ticket_id IN('".implode("','",$ticket_ids)."') GROUP BY ticket_id) ";
        $sql .= "  group_em ON em.ticket_id = group_em.ticket_id AND em.mail_sl = group_em.max_sl ";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
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
    function getAllEmailAttachments() {
        $sql = "SELECT eti.ticket_id, eti.customer_id, eti.`subject`, eat.* FROM e_ticket_info AS eti ";
        $sql .= " LEFT JOIN email_attachments AS eat ON eat.ticket_id = eti.ticket_id ";
        $sql .= " WHERE eti.customer_id != '' AND eat.ticket_id !='' ";
        $sql .= " ORDER BY eti.last_update_time ASC ";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    /*function getAllCustomerDetails(){
        $sql = "SELECT * FROM email_address_book";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }*/

    function numCustomer( $name, $email, $customer_id)
    {
        $sql = "SELECT COUNT(customer_id) AS numrows from email_address_book ";
        $cond = $this->getCustomerCondition( $name, $email, $customer_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        //GPrint($sql);die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getCustomerCondition($name, $email, $customer_id)	{
        $cond = '';
        if (!empty($customer_id)) $cond .= "customer_id='$customer_id'";
        if (!empty($name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "name LIKE'%$name%'";
        }
        if (!empty($email)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "email='$email'";
        }
        return $cond;
    }

    function getCustomer($name, $email, $customer_id, $offset=0, $limit=0)
    {
        $sql = "SELECT * FROM email_address_book ";
//        $sql .= "LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";
//        $sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id ";
//        $sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.created_by ";
        $cond = $this->getCustomerCondition($name, $email, $customer_id);
        //if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //$sql .= "ORDER BY last_update_time DESC ";
        //echo $sql;
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        return $this->getDB()->query($sql);
    }

    function numAttachmentByCustomerID( $cid, $ticket_id, $file_name, $subject)
    {
        $sql = "SELECT COUNT(eti.ticket_id) AS numrows FROM e_ticket_info AS eti ";
        $sql .= "LEFT JOIN email_attachments AS eat ON eat.ticket_id = eti.ticket_id ";
        $cond = $this->getCustomerAttachmentCondition($cid, $ticket_id, $file_name, $subject);

        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        //GPrint($sql);die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getAttachmentByCustomerID($cid, $ticket_id, $file_name, $subject, $offset=0, $limit=0) {
        $sql = "SELECT eti.ticket_id, eti.customer_id, eti.`subject`, eat.* FROM e_ticket_info AS eti ";
        $sql .= " LEFT JOIN email_attachments AS eat ON eat.ticket_id = eti.ticket_id ";
        $cond = $this->getCustomerAttachmentCondition($cid, $ticket_id, $file_name, $subject);

        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= " ORDER BY eti.last_update_time ASC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }

    function getCustomerAttachmentCondition($cid, $ticket_id, $file_name, $subject) {
        $cond = '';
        if (!empty($cid)) $cond .= "eti.customer_id='$cid'";
        if (!empty($ticket_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eat.ticket_id='$ticket_id'";
        }else {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eat.ticket_id !=''";
        }
        if (!empty($file_name)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eat.file_name LIKE '%$file_name%'";
        }
        if (!empty($subject)) {
            $subject = base64_encode($subject);
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eat.file_name LIKE '%$subject%'";
        }
        return $cond;
    }

    function isAutoReplyEnable($skill_id){
        $sql = "SELECT `status` FROM email_auto_reply_templates WHERE skill_id='$skill_id' AND `status`='Y'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0];
        }
        return null;
    }

    function updateFirstOpenTimeById($tid) {
        $sql = "SELECT create_time, first_open_time FROM e_ticket_info WHERE ticket_id ='$tid'";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        $now = time();
        $secondsPerMinute = 60;
        if (is_array($result) && $result[0]->first_open_time == 0) {
            $wating_duration = ($now - $result[0]->create_time);
            $sql = "UPDATE e_ticket_info SET waiting_duration='$wating_duration', first_open_time ='$now' WHERE ticket_id='$tid'";
            //dd($sql);
            return $this->getDB()->query($sql);
        }
        return '';
    }

    function updateETicketInfoSql($emailInfo, $role_id, $agentid, $email_status){
        $now = time();
        $secondsPerMinute = 60;
        $kpi_accepted_mins = IN_KPI_TIME;//720;  ////720 mins
        $open_duration = !empty($emailInfo) ? $now - $emailInfo->first_open_time : 0;
        $sql = '';
        if ($role_id=='A') {
            if (!empty($emailInfo->agent_id)) {
                $sql .= $emailInfo->agent_id!=$agentid ? ", agent_id_2='$agentid' " : "";
            } else {
                $sql .= ", agent_id='$agentid' ";
            }
        } else {
            if (!empty($emailInfo->agent_id)) {
                $sql .= $emailInfo->agent_id!=$agentid ? ", agent_id_2='$agentid' " : "";
            } else {
                $sql .= ", agent_id='$agentid' ";
            }
        }
        if ($emailInfo->status != "S"){
            $sql .= $email_status=='E'? ", closed_time='$now' " : "";
            $sql .= $email_status=='E' && $kpi_accepted_mins>=$open_duration? ", in_kpi='Y' " : "";
            $sql .= $email_status=='E'? ", open_duration='$open_duration' " : "";
        }
        if ($emailInfo->status != "E"){
            $sql .= $email_status=='S'? ", closed_time='$now' " : "";
            $sql .= $email_status=='S' && $kpi_accepted_mins>=$open_duration? ", in_kpi='Y' " : "";
            $sql .= $email_status=='S'? ", open_duration='$open_duration' " : "";
        }
        //dd($sql);
        return $sql;
    }

    function getEmailDispositionName($disp_codes){
        $res = array();
        $sql = "SELECT disposition_id, title FROM email_disposition_code WHERE disposition_id in ('".implode("','", $disp_codes)."')";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            foreach ( $result as $item)$res[$item->disposition_id] = $item->title;
            return $res;
        }
        return null;
    }

    function numETicketNew($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null){
        $sql = "SELECT COUNT(eti.ticket_id) AS numrows FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }

        //$sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id = eti.category_id";
//        dd($skill_id);
        $cond = $this->getEmailConditionNew($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        //echo $sql;die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function getETicketNew($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null, $offset=0, $limit=0){
        $sql = "SELECT eti.*, skl.skill_name, tc.title, ag.name FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= "LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";
        $sql .= "LEFT JOIN ticket_category AS tc ON tc.category_id=eti.category_id ";

        //$sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.created_by "; ///old
        $sql .= "LEFT JOIN agents AS ag ON ag.agent_id=eti.agent_id "; ///new

        $cond = $this->getEmailConditionNew($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id);
        //if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->query($sql);
    }
    function getEmailConditionNew( $disposition_id, $status, $agentid, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null, $first_open_date_info, $close_date_info  ,$customer_id, $waiting_duration, $in_kpi, $subject, $isAgent=false, $skill_id=null)	{
        $cond = '';
        $timeField = "create_time";
        $create_date_attr = "";
        $last_date_attr = "";
        $open_date_attr = "";
        $close_date_attr = "";

        if (!empty($disposition_id)) {
            $cond .= "eti.disposition_id='$disposition_id'";
        }
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.status='$status'";
        }
        if (!empty($skill_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.skill_id='$skill_id'";
        }
        //dd($skill_id);
        if (isset($dateinfo->sdate) && !empty($dateinfo->sdate)){
            $create_date_attr = DateHelper::get_date_attributes('create_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        }
        if (isset($updateinfo->sdate) && !empty($updateinfo->sdate)){
            $last_date_attr = DateHelper::get_date_attributes("last_update_time", $updateinfo->sdate, $updateinfo->edate, $updateinfo->stime, $updateinfo->etime);
        }
        if (!empty($create_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $create_date_attr->condition;
        }
        if (!empty($last_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $last_date_attr->condition;
        }

        if (!empty($customer_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.customer_id='$customer_id'";
        }
        if (!empty($agentid)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "( eti.agent_id='$agentid' OR eti.agent_id_2='$agentid' )";
        }
        if (!empty($waiting_duration)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.waiting_duration='$waiting_duration'";
        }
        if (!empty($in_kpi)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.in_kpi='$in_kpi'";
        }
        if (!empty($subject)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.subject_db LIKE '%$subject%'";
        }

        if (isset($first_open_date_info->sdate) && !empty($first_open_date_info->sdate)){
            $open_date_attr = DateHelper::get_date_attributes("first_open_time", $first_open_date_info->sdate, $first_open_date_info->edate, $first_open_date_info->stime, $first_open_date_info->etime);
        }
        if (isset($close_date_info->sdate) && !empty($close_date_info->sdate)){
            $close_date_attr = DateHelper::get_date_attributes("closed_time", $close_date_info->sdate, $close_date_info->edate, $close_date_info->stime, $close_date_info->etime);
        }

        if (!empty($open_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $open_date_attr->condition;
        }
        if (!empty($close_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $close_date_attr->condition;
        }
        return $cond;
    }


    function isDistributed($tid){
        $sql = "SELECT email_id, acd_status, status FROM emails_in WHERE email_id = '$tid'";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (is_array($result)){
            return $result[0];
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
        $sql .= "email='$emailInfo->created_for', `subject`='$emailInfo->subject', skill_id='$emailInfo->skill_id', `language`='EN', status='$status', agent_id='$emailInfo->assigned_to'";
        return $this->getDB()->query($sql);
    }

    function deleteEmailDistribution($tid){
        $sql = "DELETE FROM emails_in WHERE email_id='$tid' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    function updateETicketTyping($ticket_id, $isEdit=false){
        $typing = $isEdit?'Y':'';
        $sql = "UPDATE e_ticket_info SET  typing='$typing' WHERE ticket_id='$ticket_id' LIMIT 1";
        return $this->getDB()->query($sql);
    }
    function isTyping($ticket_id){
        $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$ticket_id' AND typing='Y'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0];
        }
        return null;
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
    function emptyCurrentUser($ticket_id){
        $sql = "UPDATE e_ticket_info SET `current_user`='' WHERE ticket_id='$ticket_id'";
        return $this->getDB()->query($sql);
    }
    function getEmailSkill($agent_id=null){
        if (!empty($agent_id)){
            $sql = "SELECT sk.skill_id, sk.skill_name FROM skill AS sk ";
            $sql .= " LEFT JOIN agent_skill AS ak ON ak.skill_id=sk.skill_id ";
            $sql .= " WHERE ak.agent_id='$agent_id' AND sk.qtype='E' ";
        } else {
            $sql = "SELECT skill_id,skill_name FROM skill  WHERE qtype='E' ";
        }
//        dd($sql);
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result;
        }
        return null;
    }

    function setEmailSkill($ticketid, $skill_id, $user){
        if (empty($ticketid) || empty($skill_id) || empty($user)) return false;
        $now = time();
        $sql = "UPDATE e_ticket_info SET status_updated_by='$user', skill_id='$skill_id', last_update_time='$now', rs_tr='Y', rs_tr_create_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
        if ($this->getDB()->query($sql)) {
            $this->addETicketActivity($ticketid, $user, 'K', $skill_id);
            return true;
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
                    $sender_name = $eTicketInfo->first_name.' '.$eTicketInfo->last_name;
                    $sql = "INSERT INTO emails_in SET email_id= '{$ticketid}', tstamp = UNIX_TIMESTAMP('$now'), ";
                    $sql .= " sender_name='{$sender_name}', phone='', email='{$eTicketInfo->created_for}', `subject`='{$eTicketInfo->subject}', skill_id='{$skill}', `language`='EN', status='T', agent_id='' ";
                }
            }
            return $this->getDB()->query($sql);
        }
        return null;
    }

    function getDBIPSettings() {
        $sql = "SELECT db_ip FROM settings";
        $rsult = $this->getDB()->query($sql);
        //GPrint('callesddddd');
        return !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";
    }
    function updateETicketInfo($agent_id, $ticket_id) {
        $now = time();
        if (!empty($agent_id) && !empty($ticket_id)){
            //$conn = db_con();
            $sql = "SELECT assigned_to,agent_id FROM e_ticket_info WHERE ticket_id='$ticket_id'";
            $result = $this->getDB()->query($sql);
            if (empty($result[0]->agent_id) || (!empty($result[0]->agent_id) && $result[0]->agent_id==$agent_id)){
                $sql = "UPDATE e_ticket_info SET last_update_time='$now', agent_id='$agent_id', typing='Y', `current_user`='$agent_id' WHERE ticket_id='$ticket_id'";
            }else {
                $sql = "UPDATE e_ticket_info SET last_update_time='$now', agent_id_2='$agent_id', typing='Y', `current_user`='$agent_id' WHERE ticket_id='$ticket_id'";
            }
            $result = $this->getDB()->query($sql);
            //$this->saveFailedQueryResult($ticket_id, $sql, $result, "updateETicketInfo");
            return $result;
        }
    }
    function updateSkipStatusOfEticket($agent, $ticket_id) {
        //$conn = db_con();
        $sql = "UPDATE e_ticket_info SET acd_agent='$agent', acd_status='Z', typing='', `current_user`='' WHERE ticket_id='$ticket_id'";
        return $this->getDB()->query($sql);
    }

    function getEmailAddressBook(){
        $sql = "SELECT * FROM email_address_book";
        $this->getDB()->setCharset("utf8");
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    static function getTotalNumberOfEmailAddress(){
        $sql = "SELECT COUNT(email)as total_num from email_address_book";
        $result = (new self())->getDB()->query($sql);
        return $result[0]->total_num ?? 0;
    }

    function getEmailAgents($user_id=null){
        $email_skills = $this->getEmailSkill($user_id);
        $skills = array();
        if (!empty($email_skills)){
            foreach ($email_skills as $key)
                $skills[] = $key->skill_id;
            if (!empty($skills)){
                $imp = implode("','",$skills);
                $sql = "SELECT ag.agent_id, ag.nick, ag.`name`,ags.skill_id FROM agents AS ag LEFT JOIN agent_skill AS ags ON ags.agent_id = ag.agent_id WHERE ags.skill_id IN ('$imp')";
                $result = $this->getDB()->query($sql);
                if (is_array($result)) {
                    foreach ($result as $item)
                        $agent_list[$item->agent_id] = $item->name;
                    return $agent_list;
                }
            }
        }
        return null;
    }

    function saveAttachment($has_attachment="N", $att_files, $ticket_id, $sl, $time){
        $now = $time;
        $i = 0;

        if ($has_attachment == 'Y') {
            include('conf.email.php');
            //include('model/MCcSettings.php');
            $ccsettings_model = new MCcSettings();
            $email_settings = $ccsettings_model->getEmailSettings();
//            $attachment_save_path = !empty($email_settings->attachment_save_path) ? base64_decode($email_settings->attachment_save_path) : "";
            $attachment_save_path = '';
            if (!empty($email_settings)){
                foreach ($email_settings as $itm){
                    if ($itm->item == "attachment_save_path"){
                        $attachment_save_path = base64_decode($itm->value);
                    }
                }
            }

            $yy = date("y", $now);
            $mm = date("m", $now);
            $dd = date("d", $now);
            $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $ticket_id . '/' . $sl . '/';
            if (!is_dir($dir)) mkdir($dir, 0750, true);
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    //$has_attachment = 'Y';
                    $fname = $att_files["name"][$i];
                    $fsubtype = strtoupper( end((explode(".", $fname))) );
                    $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                    $attachment_i = $i+1;

                    move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);
                    $fname = $this->getDB()->escapeString($fname);
                    $sql = "INSERT INTO email_attachments SET ticket_id='$ticket_id', mail_sl='$sl', attach_type='$ftype', attach_subtype='$fsubtype', ".
                        "part_position='$attachment_i', file_name='$fname'";
                    $this->getDB()->query($sql);
                }
                $i++;
            }
        }
    }

    static function getEmailFetchInboxName($mail_to){
//        $sql = "SELECT `name` FROM email_fetch_inboxes WHERE username='$mail_to' AND `status`='A'";
//        $_this = new static();
//        $result = $_this->getDB()->query($sql);
//        return !empty($result) ? $result[0]->name : '';

        $_this = new static();
        if (!empty(!empty($mail_to))){
            $mail_to = explode(",", $mail_to);
            $partial_sql = "";
            foreach ($mail_to as $email){
                $partial_sql .= !empty($partial_sql) ? ' OR ' : '';
                $partial_sql .= "username='".$email."'";
            }
            $sql = "SELECT `name` FROM  email_fetch_inboxes WHERE $partial_sql";
            $result = $_this->getDB()->query($sql);
            return !empty($result) ? $result[0]->name : '';
        }

    }

    function getEmailAgentList($agent_ids){
        $sql = "SELECT agent_id,nick,`name` FROM agents WHERE agent_id IN ('".implode("','",$agent_ids)."')";
        $result = $this->getDB()->query($sql);
        $response = '';
        if (!empty($result)){
            foreach ($result as $key){
                $response[$key->agent_id] = $key->name;
            }
        }
        return $response;
    }

    function getLastEmail($ticket_id, $mail_sl=null){
        if ($mail_sl){
            $sql = "SELECT from_email,mail_from,mail_body,tstamp FROM email_messages WHERE ticket_id='$ticket_id' AND mail_sl='$mail_sl'";
        }else {
            $sql = "SELECT from_email,mail_from,mail_body,tstamp FROM email_messages WHERE ticket_id='$ticket_id' ORDER BY mail_sl DESC LIMIT 1";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return $result[0];
        }
        return null;
    }

    function getForwardedAttachmentFileDetails($data, $attachment_no, $next_mail_sl){
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
                return $sql;
            }
        }
        return null;
    }

    function addLogEmailActivity($ticket_id, $pull_time, $status, $updated_by, $call_id, $is_skip, $close_time, $skip_time, $call_land_time, $current_user, $skill_transfer=null, $agent_transfer=null, $disposition=null, $transfer_time_except_pull='0'){
        $sql = "INSERT INTO log_email_activity SET ";
        $sql .= " ticket_id='$ticket_id', pull_time='$pull_time', status='$status', updated_by='$updated_by', call_id='$call_id', is_skip='$is_skip', close_time='$close_time', ";
        $sql .= " skip_time='$skip_time', call_land_time='$call_land_time', ";
        $sql .= " disposition='$disposition', skill_transfer='$skill_transfer', agent_transfer='$agent_transfer', `current_user`='$current_user', transfer_time_except_pull='$transfer_time_except_pull'";
        return $this->getDB()->query($sql);
    }

    function isPull($ticket_id, $current_user){
        $sql = "SELECT * FROM log_email_activity WHERE ticket_id = '$ticket_id' AND `current_user`='$current_user'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            return true;
        }
        return false;
    }

    function getFetchInboxDetailsByUserName($username=null) {
        $sql = "SELECT * FROM email_fetch_inboxes ";
        $sql .= !empty($username) ? " WHERE username='$username'" : "";
        return $this->getDB()->query($sql);
    }

    function updateLogEmailSession($ticket_id, $status, $agentid, $post_data=null){
        $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (is_array($result)) {
            $sql = "SELECT * FROM log_email_session WHERE session_id='".$result[0]->session_id."' AND ticket_id ='$ticket_id' ";
            $session_result = $this->getDB()->queryOnUpdateDB($sql);

            // inkpi updated for skill-wise, fetchbox-wise, global
            $in_kpi_time = 0;
            $skill_kpi_info = (new self())::getInKpiBySkillId($session_result[0]->skill_id);
            if ($skill_kpi_info->in_kpi) {
                $in_kpi_time = $skill_kpi_info->in_kpi;
            } else {
                $fetch_info = $this->getFetchInboxDetailsByUserName($result[0]->fetch_box_email);
                $in_kpi_time = !empty($fetch_info[0]->in_kpi_time) ? $fetch_info[0]->in_kpi_time : 0;
            }
            $in_kpi_time = !empty($in_kpi_time) ? $in_kpi_time : IN_KPI_TIME;

            $now = time();
            if (is_array($session_result) && ( $session_result[0]->status =="S" || $session_result[0]->status =="E" )){

                $last_3_digit = substr($result[0]->session_id, -3);
                $last_3_digit = (int)$last_3_digit;
                $last_3_digit = sprintf("%03d", $last_3_digit+1);
                $new_session_id = $ticket_id.$last_3_digit;
                $reopen_count = !empty($result[0]->reopen_count) ? $result[0]->reopen_count : 0 ;
                $reopen_count = $reopen_count+1;
                $create_time = $now;
                $waiting_duration = !empty($session_result[0]->create_time) ? $now - $session_result[0]->create_time : 0;

                $sql = "INSERT INTO log_email_session SET  ticket_id='$ticket_id', session_id='".$new_session_id."', ";
                $sql .= $status=='R'? " rs_tr='".$result[0]->rs_tr."', reschedule_time='".$result[0]->reschedule_time."', rs_tr_create_time='".$result[0]->rs_tr_create_time."', " : "";
                if (!empty($status) && ($status=='S' || $status=='E')){
                    $open_duration = $now - $now;
                    $in_kpi = $in_kpi_time >= ($now - $create_time) ? 'Y' : 'N';
                    $sql .= " create_time='$create_time', first_open_time='$now', waiting_duration='$waiting_duration',skill_id='".$result[0]->skill_id."', agent_1='$agentid', close_time='$now', open_duration='$open_duration', in_kpi='$in_kpi', status='$status', last_update_time='$now', status_updated_by='$agentid', disposition_id='".$result[0]->disposition_id."', reopen_num='$reopen_count', first_seen_by='$agentid', ";
                }else {
                    $sql .= " create_time='$create_time', first_open_time='$now', waiting_duration='$waiting_duration',skill_id='".$result[0]->skill_id."', agent_1='$agentid', status='$status', last_update_time='$now', status_updated_by='$agentid', disposition_id='".$result[0]->disposition_id."', reopen_num='$reopen_count', first_seen_by='$agentid', ";
                }
                $sql .= !empty($post_data->customer_phone_number) ? " phone='$post_data->customer_phone_number' " : "";
                //$sql = rtrim(trim($sql),',');
                $rnd = $this->getDB()->query($sql);
                if ($rnd){
                    $this->addETicketActivity($ticket_id, $agentid, 'S', $status);
                }

                $sql = "UPDATE e_ticket_info SET reopen_count='$reopen_count', session_id='".$new_session_id."' WHERE ticket_id='$ticket_id'";
                $rnd_11 = $this->getDB()->query($sql);
                $this->UpdateEmailMessageForSession($result[0], $new_session_id, $session_result[0]->create_time); //20/08/19
                return $rnd_11;

            } elseif(is_array($session_result)) {
                $sql = "UPDATE log_email_session SET agent_1='$agentid', `status`='$status', last_update_time='$now', status_updated_by='$agentid', disposition_id='".$result[0]->disposition_id."', ";
                $sql .= $status=='R'? " rs_tr='".$result[0]->rs_tr."', reschedule_time='".$result[0]->reschedule_time."', rs_tr_create_time='".$result[0]->rs_tr_create_time."', " : "";
                if (!empty($status) && ($status=='S' || $status=='E')){
                    $open_duration = !empty($session_result[0]->first_open_time) ? $now - $session_result[0]->first_open_time : 0;
                    $in_kpi = $in_kpi_time >= ($now - $session_result[0]->create_time) ? 'Y' : 'N';
                    $sql .= "  close_time='$now', open_duration='$open_duration', in_kpi='$in_kpi',  ";
                }
                $sql .= !empty($post_data->customer_phone_number) ? " phone='$post_data->customer_phone_number' " : "";
                $sql = rtrim(trim($sql),',');
                $sql .= " WHERE ticket_id='$ticket_id' AND session_id='".$result[0]->session_id."'";
                $rnd = $this->getDB()->query($sql);
                if ($rnd){
                    $this->addETicketActivity($ticket_id, $agentid, 'S', $status);
                }
                $this->UpdateEmailMessageForSession($result[0], null, $session_result[0]->create_time); //20/08/19
                return $rnd;
            }
        }
        return null;
    }

    function generate_session_id($ticket_id){
        $sql = "SELECT COUNT(*) AS numrows FROM log_email_session WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            $total_session = $result[0]->numrows;
            $total_session = (int)$total_session;
            $last_3_digit = sprintf("%03d", $total_session+1);
            return $ticket_id.$last_3_digit;
        }
        return $ticket_id.'001';
    }


    function updateFirstOpenTimeForLogEmailSession($eticket_obj, $agent_id) {
        if (!empty($eticket_obj)) {
            $sql = "SELECT * FROM log_email_session WHERE ticket_id ='".$eticket_obj->ticket_id."' AND session_id='".$eticket_obj->session_id."'";
            $result = $this->getDB()->queryOnUpdateDB($sql);
            if (is_array($result) && empty($result[0]->first_open_time) && empty($result[0]->close_time)) {
                $now = time();
                $waiting_duration = $now -  $result[0]->create_time;
                $sql = "UPDATE log_email_session SET first_open_time='$now', waiting_duration='$waiting_duration', first_seen_by='$agent_id' WHERE ticket_id='".$eticket_obj->ticket_id."' AND session_id='".$eticket_obj->session_id."'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function updateLogEmailSessionDisposition($ticket_id){
        $sql = "SELECT session_id, last_update_time, status_updated_by, disposition_id  FROM e_ticket_info WHERE ticket_id='$ticket_id' ";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (!empty($result)){
            $sql = "SELECT * FROM log_email_session WHERE ticket_id ='$ticket_id' AND session_id='".$result[0]->session_id."'";
            $session_result = $this->getDB()->queryOnUpdateDB($sql);
            if (!empty($session_result)){
                $sql = "UPDATE log_email_session SET last_update_time='".$result[0]->last_update_time."', status_updated_by='".$result[0]->status_updated_by."', disposition_id='".$result[0]->disposition_id."', ";
                //$sql .= $this->setLogEmailSessionAgent($session_result, $result[0]->status_updated_by);
                $sql = rtrim(trim($sql),',');
                $sql .= " WHERE ticket_id='$ticket_id' AND session_id='".$result[0]->session_id."'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function setLogEmailSessionAgent($session_result, $agentid) {
        $is_agent_exists = false;
        $sql = "";
        if (empty($session_result[0]->agent_1)){
            $sql =" agent_1='$agentid', ";
            $is_agent_exists = true;
        }else {
            if ($session_result[0]->agent_1==$agentid)
                $is_agent_exists = true;
        }
        if (!$is_agent_exists){
            if (empty($session_result[0]->agent_2)){
                $sql =" agent_2='$agentid', ";
                $is_agent_exists = true;
            }else {
                if ($session_result[0]->agent_2==$agentid)
                    $is_agent_exists = true;
            }
        }
        if (!$is_agent_exists) {
            if (empty($session_result[0]->agent_3)){
                $sql =" agent_3='$agentid', ";
            }
        }
        return $sql;
    }

    function updateLogEmailSessionTransfer($ticket_id, $skill_transfer=null, $agent_transfer=null){
        $sql = "SELECT *  FROM e_ticket_info WHERE ticket_id='$ticket_id' ";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (!empty($result)){
            $sql = "SELECT * FROM log_email_session WHERE ticket_id ='$ticket_id' AND session_id='".$result[0]->session_id."'";
            $session_result = $this->getDB()->queryOnUpdateDB($sql);
            if (!empty($session_result)){
                $sql = "UPDATE log_email_session SET last_update_time='".$result[0]->last_update_time."', status_updated_by='".$result[0]->status_updated_by."', disposition_id='".$result[0]->disposition_id."', rs_tr='".$result[0]->rs_tr."', reschedule_time='".$result[0]->reschedule_time."', rs_tr_create_time='".$result[0]->rs_tr_create_time."', ";
                $sql .= !empty($skill_transfer) ? " skill_id='".$skill_transfer."', " : "";
                $sql .= !empty($agent_transfer) ? " assigned_to='".$agent_transfer."', " : "";
                //$sql .= $this->setLogEmailSessionAgent($session_result, $result[0]->status_updated_by);
                $sql = rtrim(trim($sql),',');
                $sql .= " WHERE ticket_id='$ticket_id' AND session_id='".$result[0]->session_id."'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function generateLogEmailSession($ticket_id){
        $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (!empty($result)){
            $sql = "INSERT INTO log_email_session SET  ticket_id='$ticket_id', session_id='".$result[0]->session_id."', create_time='".$result[0]->create_time."', first_open_time='".$result[0]->first_open_time."', ";
            $sql .= " waiting_duration='".$result[0]->waiting_duration."', skill_id='".$result[0]->skill_id."', agent_1='".$result[0]->status_updated_by."', close_time='".$result[0]->closed_time."', ";
            $sql .= " open_duration='".$result[0]->open_duration."', in_kpi='".$result[0]->in_kpi."', status='".$result[0]->status."', reopen_num='".$result[0]->reopen_count."', last_update_time='".$result[0]->last_update_time."', ";
            $sql .= " status_updated_by='".$result[0]->status_updated_by."', disposition_id='".$result[0]->disposition_id."', first_seen_by='".$result[0]->status_updated_by."' ";
            //$sql .= " rs_tr='".$result[0]->rs_tr."', reschedule_time='".$result[0]->reschedule_time."', rs_tr_create_time='".$result[0]->rs_tr_create_time."',";
            return $this->getDB()->query($sql);
        }
        return null;
    }

    function check_empty_log_email_session($eTicket_info, $agent_id){
        $again_check_query = "SELECT session_id FROM log_email_session WHERE ticket_id='".$eTicket_info->ticket_id."' ";
        if (empty($eTicket_info->session_id)) {
            $session_id = $eTicket_info->ticket_id.'001';
            if ($eTicket_info->status=='S' || $eTicket_info->status=='E'){
                $create_time = $eTicket_info->last_update_time;
                $first_open_time = $eTicket_info->last_update_time;
            }else {
                $create_time = $eTicket_info->create_time;
                $first_open_time = $eTicket_info->first_open_time;
            }
            $waiting_duration = $first_open_time - $create_time;

            $sql = "INSERT INTO log_email_session SET  ticket_id='".$eTicket_info->ticket_id."', session_id='$session_id', create_time='$create_time', first_open_time='$first_open_time', ";
            $sql .= "  waiting_duration='".$waiting_duration."', skill_id='".$eTicket_info->skill_id."', first_seen_by='".$agent_id."' ";
            $sql = trim($sql);
            $result = $this->getDB()->queryOnUpdateDB($again_check_query);
            if (!is_array($result) && empty($result)){
                if ($this->getDB()->query($sql)){
                    $sql = "UPDATE e_ticket_info SET session_id='$session_id' WHERE ticket_id='".$eTicket_info->ticket_id."' ";
                    return $this->getDB()->query($sql);
                }
            }
        }
        return null;
    }


    function numEmailReport($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null){
        $sql = "SELECT COUNT(les.ticket_id) as numrows from log_email_session AS les ";
        $sql .=" JOIN e_ticket_info AS eti ON eti.ticket_id = les.ticket_id ";
        if (!empty($agentid)){
            //$sql .= "JOIN agent_skill AS ags ON ags.skill_id=les.skill_id AND ags.agent_id='$agentid' ";
        }

        $cond = $this->getEmailReportCondition($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //$sql .= "  AND !( les.close_time > 0 AND les.first_open_time = 0) "; //// updated for mark as closed.
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getEmailReport($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null, $offset=0, $limit=0){
        $sql = "SELECT les.*, skl.skill_name,  eti.subject,eti.created_for, eti.mail_to, eti.customer_id FROM log_email_session AS les ";
        $sql .=" JOIN e_ticket_info AS eti ON eti.ticket_id = les.ticket_id ";
        if (!empty($agentid)){
            //$sql .= "JOIN agent_skill AS ags ON ags.skill_id=les.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= "LEFT JOIN skill AS skl ON skl.skill_id=les.skill_id ";
        $cond = $this->getEmailReportCondition($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //$sql .= "  AND !( les.close_time > 0 AND les.first_open_time = 0) "; //// updated for mark as closed.
        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->queryOnUpdateDB($sql);
    }

    function getEmailReportCondition( $disposition_id, $status, $agentid, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null, $first_open_date_info, $close_date_info  ,$customer_id, $waiting_duration, $in_kpi, $subject, $isAgent=false, $skill_id=null)	{
        $cond = '';
        $timeField = "create_time";
        $create_date_attr = "";
        $last_date_attr = "";
        $open_date_attr = "";
        $close_date_attr = "";
        //dd($close_date_info);
        if (!empty($disposition_id)) {
            $cond .= "les.disposition_id='$disposition_id'";
        }
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "les.status='$status'";
        }
        if (!empty($skill_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "les.skill_id='$skill_id'";
        }
        //dd($skill_id);
        if (isset($dateinfo->sdate) && !empty($dateinfo->sdate)){
            $create_date_attr = DateHelper::get_date_attributes('les.create_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        }
        if (isset($updateinfo->sdate) && !empty($updateinfo->sdate)){
            $last_date_attr = DateHelper::get_date_attributes("les.last_update_time", $updateinfo->sdate, $updateinfo->edate, $updateinfo->stime, $updateinfo->etime);
        }
        if (!empty($create_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $create_date_attr->condition;
        }
        if (!empty($last_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $last_date_attr->condition;
        }

        if (!empty($customer_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.customer_id='$customer_id'";
        }
        if (!empty($agentid)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "( les.agent_1='$agentid' OR les.agent_2='$agentid' )";
        }
        if (!empty($waiting_duration)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "les.waiting_duration='$waiting_duration'";
        }
        if (!empty($in_kpi)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "les.in_kpi='$in_kpi'";
        }
        if (!empty($subject)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "eti.subject_db LIKE '%$subject%'";
        }

        if (isset($first_open_date_info->sdate) && !empty($first_open_date_info->sdate)){
            $open_date_attr = DateHelper::get_date_attributes("les.first_open_time", $first_open_date_info->sdate, $first_open_date_info->edate, $first_open_date_info->stime, $first_open_date_info->etime);
        }
        if (isset($close_date_info->sdate) && !empty($close_date_info->sdate)){
            $close_date_attr = DateHelper::get_date_attributes("les.close_time", $close_date_info->sdate, $close_date_info->edate, $close_date_info->stime, $close_date_info->etime);
        }

        if (!empty($open_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $open_date_attr->condition;
        }
        if (!empty($close_date_attr)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= $close_date_attr->condition;
        }
        return $cond;
    }

    function numEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id){
        $cond = '';
        $sql = "SELECT COUNT(*) as numrows FROM e_ticket_activity ";
        $cond = $this->getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id);
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        //echo $sql;die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function getEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id, $offset=0, $limit=0){
        $sql = "SELECT * FROM e_ticket_activity ";
        $cond = $this->getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY activity_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->queryOnUpdateDB($sql);
    }

    function getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id){
        $cond = '';
        if (isset($dateTimeInfo->sdate) && !empty($dateTimeInfo->sdate)){
            $activity_time = DateHelper::get_date_attributes('activity_time', $dateTimeInfo->sdate, $dateTimeInfo->edate, $dateTimeInfo->stime, $dateTimeInfo->etime);
            if (!empty($activity_time)){
                $cond .= $activity_time->condition;
            }
        }
        if (!empty($ticket_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "ticket_id='$ticket_id' ";
        }
        if (!empty($agentId)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "agent_id IN ('$agentId') ";
        }

        if (!empty($cond)) $cond .= " AND ";
        $cond .= " activity='S' ";
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " activity_details='$status' ";
        }
        return rtrim(trim($cond), ',');
    }

    function isNewEmailArrive($ticket_id){
        $sql = "SELECT COUNT(*) AS numrows FROM email_messages WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function eTicketInterFareUpdate($ticket_id, $latest_email_details, $status){
        $new_status = '';
        if ($status=="S" || $status=="E"){
            $new_status = "`status`='O', ";
            $this->addETicketActivity($ticket_id, 'STM', 'S','O');
        }
        if (!empty($latest_email_details)){
            $sql = "UPDATE e_ticket_info SET $new_status ib_tstamp='".$latest_email_details->tstamp."', ib_user='".$latest_email_details->from_email."' WHERE ticket_id='$ticket_id'";
        } else {
            $sql = "UPDATE e_ticket_info SET interfare_tstamp='0', interfare_user='' WHERE ticket_id='$ticket_id'";
        }
        $this->getDB()->query($sql);
        if (!empty($latest_email_details) && ($status=="S" || $status=="E")){
            $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$ticket_id'";
            $result = $this->getDB()->query($sql);
            if (!empty($result)){
                $last_3_digit = substr($result[0]->session_id, -3);
                $last_3_digit = (int)$last_3_digit;
                $last_3_digit = sprintf("%03d", $last_3_digit+1);
                $new_session_id = $ticket_id.$last_3_digit;
                $reopen_count = !empty($result[0]->reopen_count) ? $result[0]->reopen_count : 0 ;
                $reopen_count = $reopen_count+1;

                $sql = "INSERT INTO log_email_session SET  ticket_id='$ticket_id', session_id='".$new_session_id."', ";
                $sql .= " create_time='".$latest_email_details->tstamp."', skill_id='".$result[0]->skill_id."', status='O', last_update_time='".$latest_email_details->tstamp."', status_updated_by='".$latest_email_details->from_email."', disposition_id='".$result[0]->disposition_id."', reopen_num='$reopen_count', ib='Y' ";
                $this->getDB()->query($sql);

                $sql = "UPDATE e_ticket_info SET reopen_count='$reopen_count', session_id='".$new_session_id."' WHERE ticket_id='$ticket_id'";
                return $this->getDB()->query($sql);
            }
        }
        return null;
    }

    function getTicketEmailsBySessiionId($ticketid='', $sl='', $session_id=null) {
        $cond = '';
        $sql = "SELECT * FROM email_messages ";
        //$sql = "SELECT email_messages.*, agents.nick FROM email_messages  LEFT JOIN agents ON agents.agent_id = email_messages.agent_id ";   //////ONE BANK//////
        if (!empty($ticketid)) $cond .= "ticket_id='$ticketid'";
        if (!empty($sl)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " mail_sl='$sl'";
        }
        if (!empty($session_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " session_id='$session_id'";
        }
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY mail_sl DESC ";
        return $this->getDB()->query($sql);
    }

    function getSessionEmail($ticket_id, $sdate, $edate){
        $sql = "SELECT * FROM email_messages ";
        $sql .= " WHERE ticket_id='{$ticket_id}' AND tstamp BETWEEN '{$sdate}' AND '{$edate}'";
        $sql .= " ORDER BY mail_sl DESC ";
        return $this->getDB()->query($sql);
    }

    function setMarkAsClosedForLogSession($ticket_ids, $agentid){
        $now = time();
        $response = false;
        $string_id = "('".implode("','", $ticket_ids)."')";
        $sql = "SELECT ticket_id,session_id,create_time,first_open_time,waiting_duration,agent_1, agent_2, agent_3,close_time,open_duration FROM log_email_session WHERE close_time='' AND ticket_id IN $string_id";
        $log_result = $this->getDB()->query($sql);
        if ($log_result){
            $executed_ticket_id = [];
            foreach ($log_result as $key){
                $sql = "UPDATE log_email_session SET `status`='E', last_update_time='$now', status_updated_by='$agentid', disposition_id='".MARK_AS_CLOSED."', ";
                $session_result[] = $key;
                $open_duration = !empty($key->first_open_time) ? $now - $key->first_open_time : 0;
//                $open_duration = $now - $key->create_time;
//                $in_kpi = !empty($key->first_open_time) && (IN_KPI_TIME >= $open_duration) ? 'Y' : 'N';
                $in_kpi = $now - $key->create_time <= IN_KPI_TIME ? 'Y' : 'N';
                $sql .= "  close_time='$now', open_duration='$open_duration', in_kpi='$in_kpi',  ";
                $sql .= $this->setLogEmailSessionAgent( $session_result, $agentid);

                $sql = rtrim(trim($sql),',');
                $sql .= " WHERE ticket_id='".$key->ticket_id."' AND session_id='".$key->session_id."'";
                $this->getDB()->query($sql);

                if (!in_array($key->ticket_id, $executed_ticket_id))
                $executed_ticket_id[] = $key->ticket_id;
//                $sql = "UPDATE e_ticket_info SET `status`='E', status_updated_by='$agentid', disposition_id='".MARK_AS_CLOSED."', last_update_time='$now' WHERE ticket_id='".$key->ticket_id."' ";
//                $response = $this->getDB()->query($sql);
//                $this->addETicketActivity($key->ticket_id,$agentid,'D',MARK_AS_CLOSED);
//                $this->addETicketActivity($key->ticket_id,$agentid,'S','E');
            }
            if (!empty($executed_ticket_id)){ ///   New for Multi
                foreach ($executed_ticket_id as $key => $tid){
                    $sql = "UPDATE e_ticket_info SET `status`='E', status_updated_by='$agentid', disposition_id='".MARK_AS_CLOSED."', last_update_time='$now' WHERE ticket_id='".$tid."' ";
                    $response = $this->getDB()->query($sql);
                    $this->addETicketActivity($tid,$agentid,'D',MARK_AS_CLOSED);
                    $this->addETicketActivity($tid,$agentid,'S','E');
                }
            }
        }
        return $response;
    }

    function getIncomingEmailCountById($ticket_ids){
        $id_string = '';
        $response = [];
        if (is_array($ticket_ids))
            $id_string = "'".implode("','", $ticket_ids)."'";
        elseif (!empty($ticket_ids) && !is_array($ticket_ids))
            $id_string = "'".$ticket_ids."'";
        if (!empty($id_string)){
            $sql = "SELECT ticket_id,COUNT(*) AS total FROM email_messages WHERE ticket_id IN($id_string) AND agent_id='' GROUP BY ticket_id";
            $result = $this->getDB()->query($sql);
            if(!empty($result)) {
                foreach ($result as $key){
                    $response[$key->ticket_id] = $key->total;
                }
            }
        }
        return $response;
    }
    
function getTopMenuEmailCount($email_arr, $date_info){
    $date_str = !empty($date_info['sdate']) ? " last_update_time BETWEEN '".strtotime($date_info['sdate'])."' AND " : "";
    $date_str .= !empty($date_str) && !empty($date_info['edate']) ? "'".time()."' " : "";

    $sql = "SELECT COUNT(*) as total, fetch_box_email FROM e_ticket_info ";
    $sql .= " WHERE fetch_box_email IN ('".implode("','",$email_arr)."') AND `status`='O' AND $date_str ";
    $sql .= " GROUP BY fetch_box_email ";
    //GPrint($sql);die;
    $data = $this->getDB()->query($sql);
    $result = [];
    if (!empty($data)){
        foreach ($data as $key){
            $result[$key->fetch_box_email] = $key->total;
        }
    }
    return $result;
}

function changeDispositionStatus($dispositon_id, $staus){
    $dids[] = $dispositon_id;
    $sql = "SELECT lft,rgt FROM email_disposition_code WHERE disposition_id='$dispositon_id'";
    $result = $this->getDB()->query($sql);
    if (is_array($result)){
        $sql = "SELECT disposition_id FROM email_disposition_code WHERE lft > ".$result[0]->lft." AND rgt < ".$result[0]->rgt;
        $child_result = $this->getDB()->query($sql);
        if (!empty($child_result)){
            foreach ($child_result as $key)
                $dids []= $key->disposition_id;
        }
    }

    $sql = "UPDATE email_disposition_code SET `status`='$staus' WHERE disposition_id IN ('".implode("','", $dids)."')";
    return $this->getDB()->query($sql);
}

    function setShowStatus($ticket_id, $session_id=null){
        $sql = "SELECT DISTINCT `status` FROM log_email_session WHERE ticket_id='$ticket_id'";
        $result = $this->getDB()->query($sql);
        $response_status = "";
        $email_current_status = [];
        if (is_array($result)) {
            foreach ($result as $item){
                $email_current_status[] = $item->status;
            }
        }
        if (!empty($email_current_status)){
            if (in_array('N',$email_current_status) || in_array('O',$email_current_status)) $response_status = "O";
            elseif (in_array('P',$email_current_status)) $response_status = "P";
            elseif (in_array('C',$email_current_status)) $response_status = "C";
            elseif (in_array('R',$email_current_status)) $response_status = "R";
            elseif (in_array('K',$email_current_status)) $response_status = "K";
            elseif (in_array('S',$email_current_status)) $response_status = "S";
            elseif (in_array('E',$email_current_status)) $response_status = "E";
        }
        if (!empty($response_status)){
            $sql = "UPDATE e_ticket_info SET status='$response_status' WHERE ticket_id='$ticket_id'";
            return $this->getDB()->query($sql);
        }
        return null;
    }
    function setEmailMessageStatus($ticketid, $mail_sl, $status){
        $sql = "UPDATE email_messages SET email_status='$status' WHERE ticket_id='$ticketid' AND mail_sl='$mail_sl' LIMIT 1";
        return $this->getDB()->query($sql);
    }
    function getEmailMessageDetails($ticket_id, $mail_sl, $session_id=null) {
        $select = "ticket_id, mail_sl, from_name, from_email, mail_from, status, tstamp, session_id";
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

    function getSessionId($ticket_id, $status=null){
        $sql = "SELECT MAX(session_id) as session_id FROM log_email_session WHERE ticket_id='$ticket_id' ";
        $sql .= !empty($status) ? " AND `status`='$status'" : "";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        if (!empty($result))
            return $result[0]->session_id;
        return null;
    }

    function getEmailIdByEmailAddress($e_address, $arrivalDateInfo, $lastUpdateDateInfo){
        $response = [];
        $dateTimeInfo = !empty($lastUpdateDateInfo->sdate) ? $lastUpdateDateInfo : $arrivalDateInfo;
        $sql = "SELECT DISTINCT(ticket_id) FROM email_messages WHERE tstamp BETWEEN '".$dateTimeInfo->ststamp."' AND '".$dateTimeInfo->etstamp."' ";
        $sql .= " AND from_email LIKE '%$e_address%' ORDER BY tstamp DESC";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            foreach ($result as $key){
                $response[] = $key->ticket_id;
            }
        }
        return $response;
    }

    function saveFailedQueryResult($ticket_id, $query, $result, $file_name){
        //$filename = "/usr/local/ccpro/email/log/temp/".strtoupper($file_name).".log";
        $filename = !empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']!="192.168.10.60" ? "/usr/local/ccpro/email/log/temp/".strtoupper($file_name).".log" : "";
        $file = !empty($filename) ? fopen( $filename, "a+" ) : false ;
        if( $file == true) {
            $obj = new stdClass();
            $obj->ticket_id="$ticket_id";
            $obj->query="$query";
            $obj->result="$result";
            $obj->date=date("Y-m-d H:i:s", time());
            fwrite($file, "\n".json_encode($obj)."\n\n\n" );
            fclose($file);
        }
        return null;
    }

    function addETicketActivity($ticketid, $user, $activity, $activity_details=''){
        if (empty($ticketid) || empty($user)) return false;
        $isActivityAdded = true;

        if ($activity=="V") {
            $sql = "SELECT * FROM e_ticket_activity WHERE ticket_id='$ticketid' AND agent_id='$user' AND activity='V' ORDER BY activity_time DESC LIMIT 1";
            $result = $this->getDB()->query($sql);
            if (is_array($result) && time()-$result[0]->activity_time < 600) {
                $isActivityAdded = false;
            }
        }
        if ($isActivityAdded){
            $sql = "INSERT INTO e_ticket_activity SET ticket_id='$ticketid', agent_id='$user', activity='$activity', ".
                "activity_details='$activity_details', activity_time='".time()."'";

            $result = $this->getDB()->query($sql);
            //$this->saveFailedQueryResult($ticketid, $sql, $result, "addETicketActivity");
        }
        return null;
    }

    /*
     * Update Email-Message by session_id
     * */
    function UpdateEmailMessageForSession($email_info, $session_id=null, $create_time){
        if (!empty($email_info)) {
            $session_id = !empty($session_id) ? $session_id : $email_info->session_id;
            $now = time();
            $mail_sl = sprintf("%03d", $email_info->num_mails);
            $sql = "UPDATE email_messages SET session_id='$session_id' ";
            $sql .= " WHERE tstamp between '{$create_time}' AND '{$now}' AND ticket_id='$email_info->ticket_id' AND mail_sl='$mail_sl' ";
            return $this->getDB()->query($sql);
        }
        return false;
    }

    /*
     * Priority top menu email count
     */
    function getPriorityTopMenuEmailCount($skills, $dateInfo){
        if (empty($dateInfo) || ($dateInfo['sdate'] > $dateInfo['edate']) || empty($skills))
            return null;

        $sdate = strtotime($dateInfo['sdate']);
        $edate = time();

        $sql = "SELECT COUNT(ticket_id) as total_priority_email FROM e_ticket_info ";
        $sql .= " WHERE last_update_time BETWEEN '$sdate' AND  '$edate' ";
        $sql .= " AND skill_id IN ('".implode("','", $skills)."') AND `status`='O' AND is_priority='Y'";
        $result = $this->getDB()->query($sql);
        if (is_array($result))
            return $result[0]->total_priority_email;
        return 0;
    }

    /*
     * skill in kpi time
     */
    static function getInKpiBySkillId($skill_id){
        $sql = "SELECT * FROM email_skill_inkpi WHERE skill_id='$skill_id'";
        $result = (new self())->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    static function updateInkpiBySkillId($inkpi, $skill_id, $is_update){
        if ($is_update){
            $sql = "UPDATE email_skill_inkpi SET in_kpi='{$inkpi}' WHERE skill_id='$skill_id'";
        } else {
            $sql = "INSERT INTO email_skill_inkpi SET skill_id='$skill_id', in_kpi='{$inkpi}'";
        }
        return (new self())->getDB()->query($sql);
    }

    static function getAllSkillInkpi(){
        $sql = "SELECT * FROM email_skill_inkpi";
        $result = (new self())->getDB()->query($sql);
        if (is_array($result)){
            return array_column($result, 'in_kpi', 'skill_id');
        }
        return null;
    }

    public function getLastSessionById($ticket_id, $status=null) {
        $sql = "SELECT session_id, create_time FROM log_email_session WHERE ticket_id='{$ticket_id}' ";
        $sql .= !empty($status) ? " AND `status`='$status'" : "";
        $sql .= " ORDER BY session_id DESC LIMIT 1";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        return !empty($result) ? array_shift($result) : null;
    }

    /*
     * get email-messages
     * last send email time
     * time_diff in seconds
     */
    public function isEmailSendValid ($ticket_id, $time_diff=60) {
        if (empty($ticket_id))
            return false;

        $sql = "SELECT ticket_id,tstamp,`status` FROM email_messages WHERE ticket_id='$ticket_id' ORDER BY tstamp DESC LIMIT 1";
        $result = $this->getDB()->queryOnUpdateDB($sql);
        $result = !empty($result) ? array_shift($result) : null;

        if (empty($result)) {
            return true;
        } elseif (!empty($result) && ( ($result->status == 'O' && time() - $result->tstamp >=$time_diff) || ($result->status == 'N') )) {
            return true;
        }
        return false;
    }

    function test ($sql) {
        return $this->getDB()->query($sql);
    }

}

?>