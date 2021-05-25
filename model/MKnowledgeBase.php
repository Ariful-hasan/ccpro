<?php

//http://www.sitepoint.com/hierarchical-data-database-2/

class MKnowledgeBase extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numKnowledges($parentid='', $sid='')
	{
		$sql = "SELECT COUNT(kbase_id) AS numrows FROM knowledge_base WHERE parent_id='$parentid' ";
		if (!empty($sid)) $sql .= "AND skill_id='$sid'";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
	
		return 0;
	}
	
	function getKnowledges($parentid='', $sid='', $offset=0, $limit=0)
	{
		$sql = "SELECT kbase_id, title, description, lft, rgt FROM knowledge_base WHERE parent_id='$parentid' ";
		if (!empty($sid)) $sql .= "AND skill_id='$sid' ";
		$sql .= "ORDER BY lft ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getKBaseChildrenOptions($skillid='', $root='')
	{
		$options = array();
		$sql = "SELECT kbase_id, title FROM knowledge_base WHERE parent_id='$root' ";
		if (!empty($skillid)) $sql .= "AND skill_id='$skillid' ";
		$sql .= "ORDER BY lft ASC";
		$result = $this->getDB()->query($sql);
		//echo $sql;
	
		if (is_array($result)) {
			foreach ($result as $row) {
				$options[$row->kbase_id] = $row->title;
			}
		}
	
		return $options;
	}
	
	function getKBaseTreeOptions($skillid='', $root='')
	{
		$left = 0;
		$rgt = 0;
	
		if (empty($root)) {
			$left = 1;
			$sql = 'SELECT MAX(rgt) AS max_rgt FROM knowledge_base';
			if (!empty($skillid)) $sql .= " WHERE skill_id='$skillid'";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) $rgt = $result[0]->max_rgt;
		} else {
			$sql = "SELECT lft, rgt FROM knowledge_base WHERE kbase_id='$root'";
			if (!empty($skillid)) $sql .= " AND skill_id='$skillid'";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				$left = $result[0]->lft;
				$rgt = $result[0]->rgt;
			}
		}
	
		$right = array();
		$options = array();
		$sql = "SELECT kbase_id, title, lft, rgt FROM knowledge_base WHERE ";
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
	
				$options[$row->kbase_id] = str_repeat(' -> ',count($right)) . $row->title;
				$right[] = $row->rgt;
			}
		}
	
		return $options;
	}
	
	function getKnowledgeById($disid, $sid='')
	{
		$sql = "SELECT * FROM knowledge_base WHERE kbase_id='$disid' ";
		if (!empty($sid)) $sql .=  "AND skill_id='$sid' ";
		$sql .= "LIMIT 1";
		$return = $this->getDB()->query($sql);
		if (is_array($return)) {
			$return[0]->tags = $this->getKnowledgeTags($return[0]->kbase_id);
			return $return[0];
		}
		return null;
	}
	
	function getKnowledgeTags($kbase_id)
	{
		$tags = '';
		$sql = "SELECT tag_text FROM knowledge_base_tags WHERE kbase_id='$kbase_id'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				if (!empty($tags)) $tags .= ', ';
				$tags .= $row->tag_text;
			}
		}
		return $tags;
	}
	
	function addKnowledge($sid, $service)
	{
		//if (empty($service->disposition_id)) return false;
		$did = $this->getNextId('kbase_id', 'knowledge_base', 'AAAA', 'ZZZZ');
		$sql = "INSERT INTO knowledge_base SET ".
				"skill_id='$sid', ".
				"parent_id='$service->parent_id', ".
				"kbase_id='$did', ".
				"description='$service->description', ".
				"title='$service->title'";

		if ($this->getDB()->query($sql)) {
			$this->rebuildKnowledgeBaseTree('', 0);
			if (!empty($service->tags)) {
				$tags = explode(",", $service->tags);
				if (is_array($tags)) {
					foreach ($tags as $_tag) {
						$_tag = trim($_tag);
						if (!empty($_tag)) {
							$sql = "INSERT INTO knowledge_base_tags SET kbase_id='$did', tag_text='$_tag'";
							$this->getDB()->query($sql);
						}
					}
				}
			}
			$this->addToAuditLog('Knowledge Base', 'A', "ID=".$did, "Title=$service->title");
			return true;
		}
		return false;
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
	
	function getKnowledgePathArray($child)
	{
		$path = array();
		if (empty($child)) return $path;
	
		$result_d = $this->getDB()->query("SELECT lft, rgt, title FROM knowledge_base WHERE kbase_id='$child'");
		if (is_array($result_d)) {
			$left = $result_d[0]->lft;
			$rgt = $result_d[0]->rgt;
			$sql = "SELECT kbase_id, title FROM knowledge_base WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				foreach ($result as $row) {
					$path[] = array($row->kbase_id, $row->title);
				}
			}
			$path[] = array($child, $result_d[0]->title);
		}
	
		return $path;
	}
	
	function rebuildKnowledgeBaseTree($parent, $left)
	{
		$right = $left+1;   
		$sql = "SELECT kbase_id FROM knowledge_base WHERE parent_id='$parent'";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			foreach ($result as $row) {
				 $right = $this->rebuildKnowledgeBaseTree($row->kbase_id, $right);
			}
		}

		$sql = "UPDATE knowledge_base SET lft='$left', rgt='$right' WHERE kbase_id='$parent'";
		 $this->getDB()->query($sql);
		return $right+1;   
	}
	

/*
	
	
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
	
	
	
	
	*/


	
	
	
	function updateKnowledge($oldservice, $service)
	{
		if (empty($oldservice->kbase_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		if ($service->title != $oldservice->title) {
			$changed_fields .= "title='$service->title'";
			$ltext = "Title=$oldtemplate->title to $template->title";
		}
		if ($service->description != $oldservice->description) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "description='$service->description'";
			$ltext = $this->addAuditText($ltext, "Text changed");
		}
		
		if ($service->parent_id != $oldservice->parent_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "parent_id='$service->parent_id'";
			$ltext = $this->addAuditText($ltext, "Parent changed");
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE knowledge_base SET $changed_fields WHERE kbase_id='$oldservice->kbase_id'";
			$is_update = $this->getDB()->query($sql);
			if ($is_update && $service->parent_id != $oldservice->parent_id) {
				$this->rebuildKnowledgeBaseTree('', 0);
			}
		}
		
		if ($service->tags != $oldservice->tags) {
			$sql = "DELETE FROM knowledge_base_tags WHERE kbase_id='$oldservice->kbase_id'";
			$this->getDB()->query($sql);
			if (!empty($service->tags)) {
				$tags = explode(",", $service->tags);
				if (is_array($tags)) {
					foreach ($tags as $_tag) {
						$_tag = trim($_tag);
						if (!empty($_tag)) {
							$sql = "INSERT INTO knowledge_base_tags SET kbase_id='$oldservice->kbase_id', tag_text='$_tag'";
							$this->getDB()->query($sql);
						}
					}
				}
			}
			$ltext = $this->addAuditText($ltext, "Tags changed");
		}
		
		if ($is_update) {
			$this->addToAuditLog('Knowledge Base', 'U', "Title=".$oldservice->title, $ltext);
		}
		
		return $is_update;
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
	
	
	
	function setEmailDisposition($ticketid, $disposition_id, $user)
	{
		if (empty($ticketid) || empty($user)) return false;
		$now = time();
		//addETicketActivity($ticketid, $user, $activity, $activity_details='')
		$sql = "UPDATE e_ticket_info SET disposition_id='$disposition_id', last_update_time='$now' WHERE ticket_id='$ticketid' LIMIT 1";
		if ($this->getDB()->query($sql)) {
			$this->addETicketActivity($ticketid, $user, 'D', $disposition_id);
			return true;
		}
		return false;
	}
	
	function random_digits($num_digits)
	{
		if ($num_digits <= 0) {
			return '';
		}
		return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
	}

}

?>