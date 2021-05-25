<?php

class MChatTemplate extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function numTemplates()
	{
		$cond = '';
		$sql = "SELECT COUNT(tstamp) AS numrows FROM chat_msg_templates ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getTemplates($offset=0, $limit=0)
	{
		$sql = "SELECT ct.*, sk.skill_name FROM chat_msg_templates AS ct ";
		$sql .= "LEFT JOIN skill AS sk ON sk.skill_id=ct.skill_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getTemplateById($id)
	{
		$sql = "SELECT * FROM chat_msg_templates WHERE tstamp='$id'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addTemplate($template)
	{
		if (empty($template->title)) return false;
		
		$id = time();
		$sql = "INSERT INTO chat_msg_templates SET tstamp='$id', ".
			"title='$template->title', ".
			"message='$template->message', ".
			"skill_id='$template->skill_id', ".
			"status='$template->status'";
		// echo $sql; 
			
		if ($this->getDB()->query($sql, false, "\\")) {
			$this->addToAuditLog('Chat Email Template', 'A', "ID=".$id, "Title=$template->title");
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
		if ($template->message != $oldtemplate->message) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "message='$template->message'";
			$ltext = $this->addAuditText($ltext, "Body changed");
		}
		if ($template->skill_id != $oldtemplate->skill_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "skill_id='$template->skill_id'";
			$ltext = $this->addAuditText($ltext, "Disposition changed");
		}
		if ($template->status != $oldtemplate->status) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "status='$template->status'";
			$ltext = $this->addAuditText($ltext, "Type=$oldtemplate->status to $template->status");
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE chat_msg_templates SET $changed_fields WHERE tstamp='$oldtemplate->tstamp'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$this->addToAuditLog('Chat Email Template', 'U', "Title=".$oldtemplate->title, $ltext);
		}
		
		return $is_update;
	}
	
	function deleteTemplate($tstamp)
	{
		$mailinfo = $this->getTemplateById($tstamp); 
		if (!empty($mailinfo)) {
			$sql = "DELETE FROM chat_msg_templates WHERE tstamp='$tstamp' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Chat Email Template', 'D', "ID=$tstamp", "Title=".$mailinfo->title);
				return true;
			}
		}
		return false;
	}
	
	function getTemplateOptions($did, $status='')
	{
		$options = array();
		$cond = '';
		$sql = "SELECT tstamp, title, message FROM chat_msg_templates ";
		if (!empty($did)) $cond .= "(skill_id='$did' OR skill_id='')";
		else $cond .= "skill_id=''";
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
	
	function getChatTheme(){
	    $options = array();
	    $options['blue'] = 'Blue';
	    $options['green'] = 'Green';
	    $options['orange'] = 'Orange';
	    $options['red'] = 'Red';
	    $options['yellow'] = 'Yellow';
	    $options['pink'] = 'Pink';
	    
	    return $options;
	}

	function numChatPages()
	{
	    $sql = "SELECT COUNT(page_id) AS numrows FROM chat_page ";
	    $result = $this->getDB()->query($sql);

	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getChatPages($offset=0, $limit=0)
	{
	    $sql = "SELECT cp.*, sk.skill_name FROM chat_page AS cp ";
	    $sql .= "LEFT JOIN skill AS sk ON sk.skill_id=cp.skill_id ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	
	    return $this->getDB()->query($sql);
	}
	
	function addChatPage($template)
	{
	    if (empty($template->page_id)) return false;

	    $sql = "INSERT INTO chat_page SET page_id='$template->page_id', ".
	        "www_domain='$template->www_domain', ".
	        "www_ip='$template->www_ip', ".
	        "skill_id='$template->skill_id', ".
	        "site_key='$template->site_key', ".
	        "active='$template->active', ".
	        "language='$template->language', ".
	        "max_session_per_agent='$template->max_session_per_agent', ".
	        "theme='$template->theme'";
	
	    if ($this->getDB()->query($sql)) {
	        $this->addToAuditLog('Chat Page', 'A', "ID=".$template->page_id, "Domain=$template->www_domain");
	        return true;
	    }
	    return false;
	}
	
	function updateChatPage($oldchatpage, $chatpage)
	{
	    if (empty($oldchatpage->page_id)) return false;
	    $is_update = false;
	    $changed_fields = '';
	    $ltext = '';
	
	    if ($chatpage->www_domain != $oldchatpage->www_domain) {
	        $changed_fields .= "www_domain='$chatpage->www_domain'";
	        $ltext = "Domain=$oldchatpage->www_domain to $chatpage->www_domain";
	    }
	    if ($chatpage->page_id != $oldchatpage->page_id) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "page_id='$chatpage->page_id'";
	        $ltext = $this->addAuditText($ltext, "Page ID=$oldchatpage->page_id to $chatpage->page_id");
	    }
	    if ($chatpage->www_ip != $oldchatpage->www_ip) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "www_ip='$chatpage->www_ip'";
	        $ltext = $this->addAuditText($ltext, "IP=$oldchatpage->www_ip to $chatpage->www_ip");
	    }
	    if ($chatpage->skill_id != $oldchatpage->skill_id) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "skill_id='$chatpage->skill_id'";
	        $ltext = $this->addAuditText($ltext, "Skill changed");
	    }
	    if ($chatpage->active != $oldchatpage->active) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "active='$chatpage->active'";
	        $ltext = $this->addAuditText($ltext, "Status=$oldchatpage->active to $chatpage->active");
	    }
	    if ($chatpage->site_key != $oldchatpage->site_key) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "site_key='$chatpage->site_key'";
	        $ltext = $this->addAuditText($ltext, "Site Key changed");
	    }
	    if ($chatpage->language != $oldchatpage->language) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "language='$chatpage->language'";
	        $ltext = $this->addAuditText($ltext, "Language changed");
	    }
	    if ($chatpage->max_session_per_agent != $oldchatpage->max_session_per_agent) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "max_session_per_agent='$chatpage->max_session_per_agent'";
	        $ltext = $this->addAuditText($ltext, "Max Session changed");
	    }
	    if ($chatpage->theme != $oldchatpage->theme) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "theme='$chatpage->theme'";
	        $ltext = $this->addAuditText($ltext, "Theme changed");
	    }
	    
	
	    if (!empty($changed_fields)) {
	        $sql = "UPDATE chat_page SET $changed_fields WHERE page_id='$oldchatpage->page_id'";
	        $is_update = $this->getDB()->query($sql);
	    }
	
	    if ($is_update) {
	        $this->addToAuditLog('Chat Page', 'U', "Page ID=".$oldchatpage->page_id, $ltext);
	    }
	
	    return $is_update;
	}
	
	function getChatPageById($pid, $cond='')
	{
	    if (empty($pid)) return null;
	    
	    $sql = "SELECT * FROM chat_page WHERE page_id='$pid'";
	    if (!empty($cond)) $sql .= " AND ".$cond;
	    $result = $this->getDB()->query($sql);
	    return is_array($result) ? $result[0] : null;
	}
	
	
	//Methods for char services
	function numChatServices($cond='')
	{
	    $sql = "SELECT COUNT(service_id) AS numrows FROM chat_service ";
	    if (!empty($cond)) $sql .= " WHERE ".$cond;
	    $result = $this->getDB()->query($sql);
	
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getChatServices($cond='', $offset=0, $limit=0)
	{
	    $sql = "SELECT cs.*, sk.skill_name FROM chat_service AS cs ";
	    $sql .= "LEFT JOIN skill AS sk ON sk.skill_id=cs.skill_id ";
	    if (!empty($cond)) $sql .= " WHERE ".$cond;
	    if ($limit > 0) $sql .= " LIMIT $offset, $limit";
	
	    return $this->getDB()->query($sql);
	}
	
	function addChatService($template)
	{
	    if (empty($template->page_id)) return false;
	    
	    $serviceID = '';
	    $sql = "SELECT MAX(service_id) AS maxid FROM chat_service ";
	    $result = $this->getDB()->query($sql);
	    
	    if($this->getDB()->getNumRows() == 1) {
	        $serviceID = empty($result[0]->maxid) ? '' : $result[0]->maxid;
	    }
	    if (empty($serviceID)){
	        $serviceID = 'AA';
	    }else {
	        $serviceID++;
	    }
	
	    $sql = "INSERT INTO chat_service SET page_id='$template->page_id', ".
	        "service_id='$serviceID', ".
	        "service_name='$template->service_name', ".
	        "skill_id='$template->skill_id', ".
	        "status='$template->status'";
	
	    if ($this->getDB()->query($sql)) {
	        $this->addToAuditLog('Chat Service', 'A', "ID=".$serviceID, "Page ID=$template->page_id");
	        return true;
	    }
	    return false;
	}
	
	function updateChatService($oldchatpage, $chatpage)
	{
	    if (empty($oldchatpage->page_id) || empty($oldchatpage->service_id)) return false;
	    $is_update = false;
	    $changed_fields = '';
	    $ltext = '';
	
	    if ($chatpage->service_name != $oldchatpage->service_name) {
	        $changed_fields .= "service_name='$chatpage->service_name'";
	        $ltext = "Service Name=$oldchatpage->service_name to $chatpage->service_name";
	    }
	    if ($chatpage->skill_id != $oldchatpage->skill_id) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "skill_id='$chatpage->skill_id'";
	        $ltext = $this->addAuditText($ltext, "Skill changed");
	    }
	    if ($chatpage->status != $oldchatpage->status) {
	        if (!empty($changed_fields)) $changed_fields .= ', ';
	        $changed_fields .= "status='$chatpage->status'";
	        $ltext = $this->addAuditText($ltext, "Status changed");
	    }	     
	
	    if (!empty($changed_fields)) {
	        $sql = "UPDATE chat_service SET $changed_fields WHERE service_id='$oldchatpage->service_id' AND page_id='$oldchatpage->page_id'";
	        $is_update = $this->getDB()->query($sql);
	    }
	
	    if ($is_update) {
	        $this->addToAuditLog('Chat Service', 'U', "Service ID=".$oldchatpage->service_id, $ltext);
	    }
	
	    return $is_update;
	}
	
	function deleteChatService($page_id, $service_id)
	{
	    if (empty($page_id) || empty($service_id)) return false;
	    
	    $sql = "DELETE FROM chat_service WHERE page_id='$page_id' AND service_id='$service_id' LIMIT 1";
	    if ($this->getDB()->query($sql)) {
	        $this->addToAuditLog('Chat Service', 'D', "Service id=$service_id", "Page id=".$page_id);
	        return true;
	    }
	    return false;
	}
	
	function getChatServiceById($pid, $cond='')
	{
	    if (empty($pid)) return null;
	     
	    $sql = "SELECT * FROM chat_service WHERE service_id='$pid'";
	    if (!empty($cond)) $sql .= " AND ".$cond;
	    $result = $this->getDB()->query($sql);
	    return is_array($result) ? $result[0] : null;
	}

	//Chat Disposition Methods
	function rebuildDispositionTree($parent, $left)
	{
	    $right = $left+1;
	    $sql = "SELECT disposition_id FROM skill_disposition_code WHERE parent_id='$parent'";
	    $result = $this->getDB()->query($sql);
	
	    if (is_array($result)) {
	        foreach ($result as $row) {
	            $right = $this->rebuildDispositionTree($row->disposition_id, $right);
	        }
	    }
	
	    $sql = "UPDATE skill_disposition_code SET lft='$left', rgt='$right' WHERE disposition_id='$parent'";
	    $this->getDB()->query($sql);
	    return $right+1;
	}
	
	function deleteDispositionId($dcode, $sid)
	{
	    $dcodeinfo = $this->getDispositionById($dcode, $sid);
	    if (!empty($dcodeinfo)) {
	        $sql = "DELETE FROM skill_disposition_code WHERE disposition_id='$dcode' AND skill_id='$sid' LIMIT 1";
	        if ($this->getDB()->query($sql)) {
	            $this->addToAuditLog('Chat Disposition', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->title);
	            return true;
	        }
	    }
	    return false;
	}
	
	function addService($sid, $service)
	{
	    //if (empty($service->disposition_id)) return false;
	    $did = $this->getNextId('disposition_id', 'skill_disposition_code', 'AAAA', 'ZZZZ');
	    $this->_new_disposition_id = $did;
	    $sql = "INSERT INTO skill_disposition_code SET ".
	        "skill_id='$sid', ".
	        "parent_id='$service->parent_id', ".
	        "disposition_id='$did', ".
            "disposition_type='$service->disposition_type', ".
	        "status='Y', ".
	        "title='$service->title'";
	
	    if ($this->getDB()->query($sql)) {
	        $this->rebuildDispositionTree('', 0);
	        $this->addToAuditLog('Chat Disposition', 'A', "Disposition Code=".$did, "Title=$service->title");
	        return true;
	    }
	    return false;
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
	
	    if (!empty($changed_fields)) {
	        $sql = "UPDATE skill_disposition_code SET $changed_fields WHERE disposition_id='$oldservice->disposition_id'";
	        $is_update = $this->getDB()->query($sql);
	    }

	    if ($is_update) {
	        $this->addToAuditLog('Chat Disposition', 'U', "Disposition Code=".$oldservice->disposition_id, $ltext);
	    }
	
	    return $is_update;
	}
	
	function getDispositionTreeOptions($skillid='', $root='')
	{
	    $left = 0;
	    $rgt = 0;
	
	    if (empty($root)) {
	        $left = 1;
	        $sql = 'SELECT MAX(rgt) AS max_rgt FROM skill_disposition_code';
	        if (!empty($skillid)) $sql .= " WHERE skill_id='$skillid'";
	        $result = $this->getDB()->query($sql);
	        if (is_array($result)) $rgt = $result[0]->max_rgt;
	    } else {
	        $sql = "SELECT lft, rgt FROM skill_disposition_code WHERE disposition_id='$root'";
	        if (!empty($skillid)) $sql .= " AND skill_id='$skillid'";
	        $result = $this->getDB()->query($sql);
	        if (is_array($result)) {
	            $left = $result[0]->lft;
	            $rgt = $result[0]->rgt;
	        }
	    }
	
	    $right = array();
	    $options = array();
	    $sql = "SELECT disposition_id, title, lft, rgt FROM skill_disposition_code WHERE ";
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
	
	function getDispositionById($disid, $sid='')
	{
	    $sql = "SELECT * FROM skill_disposition_code WHERE disposition_id='$disid' ";
	    if (!empty($sid)) $sql .=  "AND skill_id='$sid' ";
	    $sql .= "LIMIT 1";
	    $return = $this->getDB()->query($sql);
	    if (is_array($return)) return $return[0];
	    return null;
	}
	
	function numDispositions($sid)
	{
	    $sql = "SELECT COUNT(disposition_id) AS numrows FROM skill_disposition_code ";
	    if (!empty($sid)) $sql .= "WHERE skill_id='$sid'";
	    $result = $this->getDB()->query($sql);
	
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getDispositions($sid, $offset=0, $limit=0)
	{
	    $sql = "SELECT * FROM skill_disposition_code ";
	    if (!empty($sid)) $sql .= "WHERE skill_id='$sid' ";
	    $sql .= "ORDER BY lft ASC ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	
	    return $this->getDB()->query($sql);
	}
	
	function getDispositionPathArray($child)
	{
	    $path = array();
	    if (empty($child)) return $path;
	
	    $result_d = $this->getDB()->query("SELECT lft, rgt, title FROM skill_disposition_code WHERE disposition_id='$child'");
	    if (is_array($result_d)) {
	        $left = $result_d[0]->lft;
	        $rgt = $result_d[0]->rgt;
	        $sql = "SELECT disposition_id, title FROM skill_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
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
	            $lastDisposId = $this->addDispos2Skill($to_skill_id, $disposData);
	        }
	    }
	    return $copySuccess;
	}
	
	function getDisposBySkillId($sid, $title='', $parentId='')
	{
	    if (empty($sid)) return false;
	    $sql = "SELECT disposition_id FROM skill_disposition_code WHERE skill_id='$sid' ";
	    if (!empty($title)) $sql .=  "AND title='$title' ";

	    $sql .=  "AND parent_id='$parentId' ";
	    $sql .= "LIMIT 1";//echo $sql;
	    $result = $this->getDB()->query($sql);
	    if (is_array($result) && !empty($result[0]->disposition_id)) return $result[0]->disposition_id;
	    return false;
	}
	
	function addDispos2Skill($sid, $dataObj)
	{
	    //if (empty($service->disposition_id)) return false;
	    $did = $this->getNextId('disposition_id', 'skill_disposition_code', 'AAAA', 'ZZZZ');
	    $this->_new_disposition_id = $did;
	    $sql = "INSERT INTO skill_disposition_code SET ".
	        "skill_id='$sid', ".
	        "parent_id='$dataObj->parent_id', ".
	        "disposition_id='$did', ".
	        "title='$dataObj->title'";
	
	    if ($this->getDB()->query($sql)) {
	        $this->rebuildDispositionTree('', 0);
	        $this->addToAuditLog('Chat Disposition', 'A', "Disposition Code=".$did, "Title=$dataObj->title");
	        return $did;
	    }
	    return null;
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
	
	function getDispositionPath($child)
	{
	    if (empty($child)) return 'Not Selected';
	
	    $result = $this->getDB()->query("SELECT lft, rgt FROM skill_disposition_code WHERE disposition_id='$child'");
	    if (is_array($result)) {
	        $left = $result[0]->lft;
	        $rgt = $result[0]->rgt;
	        $sql = "SELECT title FROM skill_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
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
	
	//chat_detail_log methods
	function numChatLog($did, $dateinfo, $email = '', $contact_number = '')
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(callid) AS numrows FROM chat_detail_log ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) $cond = $this->getAndCondition($cond, "disposition_id='$did'");
		if (!empty($email)) $cond = $this->getAndCondition($cond, "email='$email'");
		if (!empty($contact_number)) $cond = $this->getAndCondition($cond, "contact_number='$contact_number'");

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getChatLogByCallId($callId)
	{
		$cond = '';
		$sql = "SELECT cl.*, dc.title, cs.service_name FROM chat_detail_log AS cl ".
			"LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ".
			"LEFT JOIN chat_service AS cs ON cs.service_id=cl.service_id  WHERE cl.callid='$callId' LIMIT 1";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	
	function getChatLog($did, $dateinfo, $offset=0, $limit=0, $email = null, $contact_number = null)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT cl.*, dc.title, cs.service_name FROM chat_detail_log AS cl ".
			"LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ".
			"LEFT JOIN chat_service AS cs ON cs.service_id=cl.service_id ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.disposition_id='$did'");
		if (!empty($email)) $cond = $this->getAndCondition($cond, "cl.email='$email'");
		if (!empty($contact_number)) $cond = $this->getAndCondition($cond, "cl.contact_number='$contact_number'");

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function numChatRecordCount($did, $dateinfo)
	{
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    $sql = "SELECT COUNT(DISTINCT cl.disposition_id) AS numrows FROM chat_detail_log AS cl LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ";
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	    if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.disposition_id='$did'");
	
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
	    //echo $sql;
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getChatRecordCount($did, $dateinfo, $offset=0, $limit=0)
	{
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    $sql = "SELECT dc.title, COUNT(callid) AS numrecords FROM chat_detail_log AS cl ".
	        "LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ";
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	    if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.disposition_id='$did'");
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $sql .= "GROUP BY cl.disposition_id ORDER BY cl.disposition_id ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	    //echo $sql;
	    return $this->getDB()->query($sql);
	}

	function getTotalCrmRecordCount($did, $dateinfo)
	{
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    $sql = "SELECT COUNT(callid) AS numrows FROM chat_detail_log AS cl LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ";
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	    if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.disposition_id='$did'");
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
	    //echo $sql;
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function numChatTimeAvg($dateinfo)
	{
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    $sql = "SELECT COUNT(DISTINCT cl.disposition_id) AS numrows FROM chat_detail_log AS cl LEFT JOIN skill_disposition_code AS dc ON dc.disposition_id=cl.disposition_id ";
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
	    //echo $sql;
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getChatTimeAvg($dateinfo, $offset=0, $limit=0)
	{
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    $sql = "SELECT url, COUNT(callid) AS numrecords, SUM(url_duration) AS tduration FROM chat_detail_log ";
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	     
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $sql .= "GROUP BY url ORDER BY url ASC ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	    //echo $sql;
	    return $this->getDB()->query($sql);
	}
	
	function addChatRating($callid, $rating=0)
	{
	    if (empty($callid) || empty($rating)) return false;
	    
	    $sql = "UPDATE cdrin_log SET agent_rating='$rating' WHERE callid='$callid'";
	
	    if ($this->getDB()->query($sql)) {
	        $this->addToAuditLog('Chat Rating', 'U', "callid='$callid'", "Rating='$rating'");
	        return true;
	    }
	    return false;
	}
	
	function getAgentsChatSkill($star_represent_blank=false){	    
	    $sql = "SELECT agent_id FROM agent_skill AS ask JOIN skill AS skl ON skl.skill_id=ask.skill_id WHERE skl.qtype='C'";
	    $result = $this->getDB()->query($sql);
	    if($star_represent_blank){
	        $ret = array('*'=>'Select');
	    }else{
	        $ret = array(''=>'Select');
	    }
	    if (is_array($result)) {
	        foreach ($result as $res) {
	            $ret[$res->agent_id] = $res->agent_id;
	        }
	    }
	    return $ret;
	}
	
	function getChatRatingData($dateinfo, $agentId=''){
	    $skillIds = "";
	    $sql = "SELECT skill_id FROM skill WHERE qtype='C'";
	    $result = $this->getDB()->query($sql);
	    if (is_array($result)) {
	        foreach ($result as $skills) {
	            if (!empty($skillIds)) $skillIds .= ", '".$skills->skill_id."'";
	            else $skillIds = "'".$skills->skill_id."'";
	        }
	    }
	    
	    $cond = '';
	    $date_attributes = DateHelper::get_date_attributes('cdl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
	    
	    if (!empty($skillIds)){
	        if (!empty($cond)){
	            $cond .= " AND skill_id IN ($skillIds) ";
	        }else {
	            $cond = " skill_id IN ($skillIds) ";
	        }
	    }
	    if(!empty($agentId)){
	        if (!empty($cond)){
	            $cond .= " AND agent_id='$agentId' ";
	        }else {
	            $cond = " agent_id='$agentId' ";
	        }
	    }	    
	    
	    $sql = "SELECT agent_id, COUNT(cdl.callid) AS numrecords, SUM(IF(agent_rating=5, agent_rating, 0)) AS very_good, ";
	    $sql .= "SUM(IF(agent_rating=4, agent_rating, 0)) AS good, SUM(IF(agent_rating=3, agent_rating, 0)) AS normal, ";
	    $sql .= "SUM(IF(agent_rating=2, agent_rating, 0)) AS bad, SUM(IF(agent_rating=1, agent_rating, 0)) AS very_bad ";
	    $sql .= "FROM cdrin_log AS cdl LEFT JOIN agent_inbound_log AS ail ON ail.callid=cdl.callid ";
	
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $sql .= "GROUP BY agent_id ORDER BY agent_id ASC";
	    //echo $sql;
	    return $this->getDB()->query($sql);
	}

    public function changeStatus($fields)
    {

        $sql = "UPDATE skill_disposition_code SET status='$fields->status' ";
        $sql .=" WHERE skill_id='$fields->skill_id' AND  lft >= {$fields->lft} AND rgt <= {$fields->rgt} ";

        return $this->getDB()->query($sql);
    }

}

?>