<?php

class MEmailTemplate extends Model
{
	function __construct() {
		parent::__construct();
	}

	function addAutoReplyTemplate($skillid)
	{
		$sql = "INSERT INTO email_auto_reply_templates SET skill_id='$skillid', mail_body='', status='N'";
		return $this->getDB()->query($sql);
	}
	
	function getAutoReplyTemplateBySkill($skillid)
	{
		if (empty($skillid)) return null;
		
		$sql = "SELECT * FROM email_auto_reply_templates WHERE skill_id='$skillid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function updateAutoReplyTemplate($oldtemplate, $template, $skillinfo)
	{
		if (empty($oldtemplate->skill_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		if ($template->mail_body != $oldtemplate->mail_body) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "mail_body='$template->mail_body'";
			$ltext = $this->addAuditText($ltext, "Body changed");
		}
		if ($template->status != $oldtemplate->status) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "status='$template->status'";
			$ltext = $this->addAuditText($ltext, "Type=$oldtemplate->status to $template->status");
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE email_auto_reply_templates SET $changed_fields WHERE skill_id='$oldtemplate->skill_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$this->addToAuditLog('Email Auto Reply Template', 'U', "Skill=".$skillinfo->skill_name, $ltext);
		}
		
		return $is_update;
	}
	
	function numTemplates()
	{
		$cond = '';
		$sql = "SELECT COUNT(tstamp) AS numrows FROM email_templates ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getTemplates($offset=0, $limit=0)
	{
		$sql = "SELECT tstamp, et.title, dc.status, et.disposition_id, dc.title AS dc_title FROM email_templates AS et LEFT JOIN ".
			"email_disposition_code AS dc ON dc.disposition_id=et.disposition_id ORDER BY et.title ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getTemplateById($id)
	{
		$sql = "SELECT * FROM email_templates WHERE tstamp='$id'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addTemplate($template)
	{
		if (empty($template->title)) return false;
		
		$id = time();
		$sql = "INSERT INTO email_templates SET tstamp='$id', ".
			"title='$template->title', ".
			"mail_body='$template->mail_body', ".
			"disposition_id='$template->disposition_id', ".
			"status='$template->status'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Email Template', 'A', "ID=".$id, "Title=$template->title");
			return true;
		}
		return false;
	}
	
	function updateTemplate($oldtemplate, $template)
	{
		if (empty($oldtemplate->tstamp)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		if ($template->title != $oldtemplate->title) {
			$changed_fields .= "Title='$template->title'";
			$ltext = "Title=$oldtemplate->title to $template->title";
		}
		if ($template->mail_body != $oldtemplate->mail_body) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "mail_body='$template->mail_body'";
			$ltext = $this->addAuditText($ltext, "Body changed");
		}
		if ($template->disposition_id != $oldtemplate->disposition_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "disposition_id='$template->disposition_id'";
			$ltext = $this->addAuditText($ltext, "Disposition changed");
		}
		if ($template->status != $oldtemplate->status) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "status='$template->status'";
			$ltext = $this->addAuditText($ltext, "Type=$oldtemplate->status to $template->status");
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE email_templates SET $changed_fields WHERE tstamp='$oldtemplate->tstamp'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$this->addToAuditLog('Email Template', 'U', "Title=".$oldtemplate->title, $ltext);
		}
		
		return $is_update;
	}
	
	function deleteTemplate($tstamp)
	{
		$mailinfo = $this->getTemplateById($tstamp); 
		if (!empty($mailinfo)) {
			$sql = "DELETE FROM email_templates WHERE tstamp='$tstamp' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Email Template', 'D', "ID=$tstamp", "Title=".$mailinfo->title);
				return true;
			}
		}
		return false;
	}
	
	function getTemplateOptions($did, $status='')
	{
        $did = str_replace(array("'",'"'), '', $did);
		$options = array();
		$cond = '';
		$sql = "SELECT tstamp, title, mail_body FROM email_templates ";
		if (!empty($did)) $cond .= "(disposition_id='$did' OR disposition_id='')";
		else $cond .= "disposition_id=''";
		if (!empty($status)) {
			$cond .= ' AND ';
			$cond .= "status='$status' ";
		}
		$sql .= "WHERE $cond ";
		$sql .= 'ORDER BY title';
		$result = $this->getDB()->query($sql);
		/*if (is_array($result)) {
			foreach ($result as $template) {
				$options[$template->tstamp] = $template->title;
			}
		}*/
		//var_dump($options);
		return $result;
	}
	
	function getChatTemplateOptions($sid='', $status='')
	{
		$options = array();
		$cond = '';
		$sql = "SELECT tstamp, title, message FROM chat_msg_templates ";
		if(!empty($sid)) {
			$cond .= "skill_id='$sid' ";
		}
		
		if (!empty($status)) {
			if(!empty($cond)) $cond .=" AND ";
			$cond .= "status='$status' ";
		}
		$sql .= "WHERE $cond ";
		$sql .= 'ORDER BY title';
		$result = $this->getDB()->query($sql);
		/*if (is_array($result)) {
			foreach ($result as $template) {
				$options[$template->tstamp] = $template->title;
			}
		}*/
		//var_dump($options);
		return $result;
	}

	function getAll()
	{
	    return $this->getDB()->query("SELECT * FROM email_templates ORDER BY title");
	}

	function getAllActive()
	{
		return $this->getDB()->query("SELECT * FROM email_templates WHERE status = 'Y' ORDER BY title");
	}
	
	function getChatTemplateById($id)
	{
		$sql = "SELECT * FROM chat_msg_templates WHERE tstamp='$id'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function numSmsTemplates()
	{
		$cond = '';
		$sql = "SELECT COUNT(tstamp) AS numrows FROM sms_templates ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getSmsTemplates($offset=0, $limit=0, $active='')
	{
		$sql = "SELECT * FROM sms_templates ";
		if (!empty($active)){
			$sql .= " WHERE status='$active' ";
		}
		$sql .= " ORDER BY title ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getSmsTemplateById($id)
	{
		$sql = "SELECT * FROM sms_templates WHERE tstamp='$id'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addSmsTemplate($template)
	{
		if (empty($template->template_id) || empty($template->title)) return false;

		$id = time();
		$sql = "INSERT INTO sms_templates SET template_id='{$template->template_id}', tstamp='$id', ".
			"title='$template->title', ".
			"sms_body='$template->sms_body', ".
			"type='$template->type', ".
			"status='$template->status' ";

		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('SMS Template', 'A', "ID=".$id, "Title=$template->title");
			return true;
		}
		return false;
	}

	function updateSmsTemplate($oldtemplate, $template)
	{
		if (empty($oldtemplate->template_id) || empty($oldtemplate->tstamp)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';

		if ($template->template_id != $oldtemplate->template_id) {
			$changed_fields .= "template_id='$template->template_id'";
			$ltext = "template_id=$oldtemplate->template_id to $template->template_id";
		}

		if ($template->title != $oldtemplate->title) {
			$changed_fields .= "Title='$template->title'";
			$ltext = "Title=$oldtemplate->title to $template->title";
		}
		if ($template->sms_body != $oldtemplate->sms_body) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "sms_body='$template->sms_body'";
			$ltext = $this->addAuditText($ltext, "Body changed");
		}

        if ($template->type != $oldtemplate->type) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "type='$template->type'";
            $ltext = $this->addAuditText($ltext, "Type=$oldtemplate->type to $template->type");
        }

		if ($template->status != $oldtemplate->status) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "status='$template->status'";
			$ltext = $this->addAuditText($ltext, "Type=$oldtemplate->status to $template->status");
		}

		if (!empty($changed_fields)) {
			$sql = "UPDATE sms_templates SET $changed_fields WHERE tstamp='$oldtemplate->tstamp'";
			$is_update = $this->getDB()->query($sql);
		}

		if ($is_update) {
			$this->addToAuditLog('SMS Template', 'U', "Title=".$oldtemplate->title, $ltext);
		}

		return $is_update;
	}

	function deleteSmsTemplate($tstamp)
	{
		if (!empty($tstamp)) {
			$sql = "DELETE FROM sms_templates WHERE tstamp='$tstamp' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('SMS Template', 'D', "ID=$tstamp", "Title=".$mailinfo->title);
				return true;
			}
		}
		return false;
	}

    function numSmsSended($dateinfo, $destNum='',$skill_id='',$agent_id='')
    {
        $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        $cond = '';
        $sql = "SELECT COUNT(tstamp) AS numrows FROM inbound_sms_log ";
        if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
        if (!empty($destNum)) $cond = $this->getAndCondition($cond, "dest_number LIKE '%$destNum%'");
        if (!empty($skill_id) && $skill_id != "*") $cond = $this->getAndCondition($cond, "skill_id='{$skill_id}' ");
        if (!empty($agent_id) && $agent_id != "*") $cond = $this->getAndCondition($cond, "agent_id='{$agent_id}'");
        if (!empty($cond)) $sql .= "WHERE $cond ";


        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    function getSmsSended($dateinfo, $destNum='', $skill_id='', $agent_id='', $offset=0, $limit=0)
    {
        $cond = "";
        $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        //$sql = "SELECT * FROM inbound_sms_log INNER JOIN agent_inbound_log ON inbound_sms_log.callid = agent_inbound_log.callid ";
        $sql = "SELECT * FROM inbound_sms_log ";
        if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
        if (!empty($destNum)) $cond = $this->getAndCondition($cond, "dest_number LIKE '%$destNum%'");
        if (!empty($skill_id) && $skill_id != "*") $cond = $this->getAndCondition($cond, "skill_id='{$skill_id}' ");
        if (!empty($agent_id) && $agent_id != "*") $cond = $this->getAndCondition($cond, "agent_id='{$agent_id}'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= " ORDER BY tstamp DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getNewEmailTemplate($did){
	    $sql = "SELECT tstamp, title FROM email_templates WHERE";
	    $sql .= !empty($did) ? " disposition_id='$did' AND  " : "";
	    $sql .= " `status`='Y' ORDER BY title";
        return $this->getDB()->query($sql);
    }
    function getNewEmailTemplateById($id){
	    $response = "";
	    $sql = "SELECT mail_body FROM email_templates WHERE tstamp = '$id'";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            $response = base64_decode($result[0]->mail_body);
        }
        return $response;
    }
}

