<?php

class MMobileApp extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function getAvailableRequests()
	{
		$sql = "SELECT * FROM app_requests";
		return $this->getDB()->query($sql);
	}
	
	function getRequestById($sid)
	{
		$sql = "SELECT * FROM app_requests WHERE request_id='$sid' LIMIT 1";
		$return = $this->getDB()->query($sql);
		if (is_array($return)) return $return[0];
		return null;
	}
	
	function getRequestFields()
	{
		$sql = "SELECT * FROM app_request_fields ORDER BY request_id, sl";
		return $this->getDB()->query($sql);
	}
	
	function getFieldsByRequest($sid)
	{
		$sql = "SELECT * FROM app_request_fields WHERE request_id='$sid' ORDER BY sl";
		return $this->getDB()->query($sql);
	}
	
	function saveRequest($data)
	{
		if (empty($data)) return false;
		$id = $data['id'];
		if (!empty($id)) {
			
			$sql = "SELECT request_id FROM app_requests WHERE request_id='$id' LIMIT 1";
			$result = $this->getDB()->query($sql);
			$is_field_update_needed = true;
			if (is_array($result)) {
				$sql = "UPDATE app_requests SET title='$data[title]', response_type='$data[rtype]', api='$data[api]', ".
					"debug_mode='$data[dmode]' WHERE request_id='$id' LIMIT 1";
				$this->getDB()->query($sql);
				
				$sql = "DELETE FROM app_request_fields WHERE request_id='$id'";
				$this->getDB()->query($sql);
				
			} else {
				$sql = "INSERT INTO app_requests SET request_id='$id', title='$data[title]', response_type='$data[rtype]', ".
					"api='$data[api]', debug_mode='$data[dmode]', active='Y'";
				//exit;
				if (!$this->getDB()->query($sql)) {
					$is_field_update_needed = false;
				}
			}
			
			if ($is_field_update_needed) {
				$fields = isset($data['fields']) ? $data['fields'] : null;
				if (is_array($fields )) {
					$f_sl = 10;
					foreach ($fields  as $field) {
						$f_sl++;
						$sql = "INSERT INTO app_request_fields SET request_id='$id', sl='$f_sl', ".
								"field_label='$field[label]', field_key='$field[key]', field_mask='$field[mask]'";
						$this->getDB()->query($sql);
					}
				}
				return true;
			}
				
		}
	
		return false;
	}
	
	function getValuesByRequest($sid)
	{
		$sql = "SELECT * FROM app_request_values WHERE request_id='$sid'";
		return $this->getDB()->query($sql);
	}
	
	function saveSetValues($data)
	{
		if (empty($data)) return false;
		$id = $data['id'];
	
		$sql = "DELETE FROM app_request_values WHERE request_id='$id'";
		$this->getDB()->query($sql);
	
		$fields = isset($data['values']) ? $data['values'] : null;
		if (is_array($fields )) {
			foreach ($fields  as $field) {
				$sql = "INSERT INTO app_request_values SET request_id='$id', ".
						"name='$field[name]', api='$field[api]'";
				$this->getDB()->query($sql);
			}
		}
		return true;
	}
	
	
	
	
	function getDispositionPathArray($child)
	{
		$path = array();
		if (empty($child)) return $path;
	
		$result_d = $this->getDB()->query("SELECT lft, rgt, title FROM skill_crm_disposition_code WHERE disposition_id='$child'");
		if (is_array($result_d)) {
			$left = $result_d[0]->lft;
			$rgt = $result_d[0]->rgt;
			$sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
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
	
		$result = $this->getDB()->query("SELECT lft, rgt FROM skill_crm_disposition_code WHERE disposition_id='$child'");
		if (is_array($result)) {
			$left = $result[0]->lft;
			$rgt = $result[0]->rgt;
			$sql = "SELECT title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
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
	
	function getDispositionChildrenOptions($template_id='', $root='')
	{
		$options = array();
		$sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE parent_id='$root' ";
		if (!empty($template_id)) $sql .= "AND template_id='$template_id' ";
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
	
	function getCallSkill($callid)
	{
		$tstamp = substr($callid, 0, 10);
		$y = date("y", $tstamp);
		$table = $y == date("y") ? '' : '_' . $y;
		
		$sql = "SELECT popup_url, popup_type, skill_name FROM skill AS s LEFT JOIN skill_log$table AS l ON s.skill_id=l.skill_id WHERE l.callid='$callid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function getTemplateSelectOptions()
	{
		$ret = array(''=>'Select');
		$sql = "SELECT template_id, title FROM skill_crm_template";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $res) {
				$ret[$res->template_id] = $res->title;
			}
		}
		return $ret;
	}
	
	function getDispositionSelectOptions()
	{
		$ret = array(''=>'Select');
		$sql = "SELECT disposition_id, title FROM skill_crm_disposition_code";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $res) {
				$ret[$res->disposition_id] = $res->title;
			}
		}
		return $ret;
	}
	
	function numTemplates()
	{
		$sql = "SELECT COUNT(template_id) AS numrows FROM skill_crm_template ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getTemplates($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM skill_crm_template ORDER BY template_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getTemplateById($tid)
	{
		$sql = "SELECT * FROM skill_crm_template WHERE template_id='$tid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getMaxTemplateID()
	{
		$sql = "SELECT MAX(template_id) AS numrows FROM skill_crm_template";
		
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}

	
	
	
	function getFiltersBySection($tid, $sid)
	{
		$sql = "SELECT * FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$sid' ORDER BY sl";
		return $this->getDB()->query($sql);
	}
	
	
	function getNewScetionID($tid)
	{
		if (empty($tid)) return '';
		for ($i='A'; $i<='Z'; $i++) {
			$sql = "SELECT section_id FROM skill_crm_template_section WHERE template_id='$tid' AND section_id='$i'";
			$result = $this->getDB()->query($sql);
			if (empty($result)) return $i;
		}
		return '';
	}
	
	function getNewSectionSL($tid)
	{
		$sl = 11;
		$numsec = 0;
		$sql = "SELECT COUNT(section_id) AS numsec FROM skill_crm_template_section WHERE template_id='$tid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			$numsec = $result[0]->numsec;
			if (empty($numsec)) $numsec = 0;
		}
		
		return $sl+$numsec;
	}
	
	function getExistingSection($tid, $type)
	{
		$sql = "SELECT * FROM skill_crm_template_section WHERE template_id='$tid' AND section_type='$type' LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) return $result[0];
		return null;
	}
	
	function saveDefinedSection($tid, $data)
	{
		if (empty($tid)) return false;
		if (empty($data)) return false;
		$id = $data['id'];
		if (empty($id)) {
			$new_id = $this->getNewScetionID($tid);
			$new_sl = $this->getNewSectionSL($tid);
			$sql = "INSERT INTO skill_crm_template_section SET template_id='$tid', sl='$new_sl', section_id='$new_id', ".
					"section_title='$data[title]', section_type='$data[stype]', api='$data[api]', debug_mode='$data[dmode]', is_editable='N', active='Y'";
			if ($this->getDB()->query($sql)) {
				return true;
			}
			
		} else {
			$sql = "UPDATE skill_crm_template_section SET section_title='$data[title]', section_type='$data[stype]', api='$data[api]', debug_mode='$data[dmode]' WHERE template_id='$tid' AND section_id='$id'";
			if ($this->getDB()->query($sql)) {
				return true;
			}
		}
		
		return false;
	}
	
	function deleteSection($tid, $sid)
	{
		if (empty($tid)) return false;
		if (empty($sid)) return false;
		
		$sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid' AND section_id='$sid'";
		$this->getDB()->query($sql);
		
		$sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid' AND section_id='$sid'";
		$this->getDB()->query($sql);
		
		return true;
	}
	
	
	function deleteFilter($tid, $data)
	{
		if (empty($tid)) return false;
		if (empty($data)) return false;
		$id = $data['id'];
		if (!empty($id)) {
			$sql = "UPDATE skill_crm_template_section SET search_submit_label='', is_searchable='N' WHERE template_id='$tid' AND section_id='$id'";
			$this->getDB()->query($sql);
	
			$sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
			$this->getDB()->query($sql);
				
			return true;
		}
	
		return false;
	}
	
	function saveFilter($tid, $data)
	{
		if (empty($tid)) return false;
		if (empty($data)) return false;
		$id = $data['id'];
		if (!empty($id)) {
			$sql = "UPDATE skill_crm_template_section SET search_submit_label='$data[slabel]', is_searchable='Y' WHERE template_id='$tid' AND section_id='$id'";
			$this->getDB()->query($sql);
				
			$sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
			$this->getDB()->query($sql);
			
			$fields = isset($data['fields']) ? $data['fields'] : null;
			if (is_array($fields )) {
				$f_sl = 0;
				foreach ($fields  as $field) {
					$f_sl++;
					$sql = "INSERT INTO skill_crm_template_filters SET template_id='$tid', section_id='$id', sl='$f_sl', ".
							"field_label='$field[label]', field_key='$field[key]', field_type='$field[type]'";
					$this->getDB()->query($sql);
				}
			}
			return true;
		} else {
			$sql = "UPDATE skill_crm_template SET api='$data[api]' WHERE template_id='$tid'";
			$this->getDB()->query($sql);
			
			$sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
			$this->getDB()->query($sql);
				
			$fields = isset($data['fields']) ? $data['fields'] : null;
			if (is_array($fields )) {
				$f_sl = 0;
				foreach ($fields  as $field) {
					$f_sl++;
					$sql = "INSERT INTO skill_crm_template_filters SET template_id='$tid', section_id='$id', sl='$f_sl', ".
							"field_label='$field[label]', field_key='$field[key]', field_type='$field[type]'";
					$this->getDB()->query($sql);
				}
			}
			return true;
		}
	
		return false;
	}
	
	function saveTemplate($tid, $data)
	{
		if (is_array($data)) {
			$sl = 10;
			foreach ($data as $section) {
				$sl++;
				$sql = "UPDATE skill_crm_template_section SET sl='$sl' WHERE template_id='$tid' AND section_id='$section[id]'";
				$this->getDB()->query($sql);
			}
		}
		/*
		$sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid'";
		$this->getDB()->query($sql);
		
		$sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid'";
		$this->getDB()->query($sql);
		
		if (is_array($data)) {
			$sl = 10;
			foreach ($data as $section) {
				$sl++;
				$sql = "INSERT INTO skill_crm_template_section SET template_id='$tid', sl='$sl', section_id='$section[id]', ".
					"section_title='$section[title]', section_type='$section[stype]', api='$section[api]', is_editable='N', active='Y'";
				$this->getDB()->query($sql);
				
				$fields = isset($section['fields']) ? $section['fields'] : null;
				if (is_array($fields )) {
//				var_dump($fields);
					foreach ($fields  as $field) {
						$sql = "INSERT INTO skill_crm_template_fields SET template_id='$tid', section_id='$section[id]', ".
							"field_label='$field[label]', field_key='$field[key]'";
//echo $sql;
						$this->getDB()->query($sql);
					}
				}
				
			}
		}
		*/
		return true;
	}
	
	function addTemplate($template)
	{
		if (empty($template->title)) return false;
		
		$id = $this->getMaxTemplateID();
		
		if (empty($id)) { $id ='AAAA'; }
		else if ( $id == 'ZZZZ') { return false; }
		else { $id++; }
		
		$sql = "INSERT INTO skill_crm_template SET template_id='$id', ".
			"title='$template->title'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Skill Template', 'A', "Title=".$template->title, '');
			return true;
		}
		
		return false;
	}

	function updateTemplate($oldtemplate, $template)
	{
		if (empty($oldtemplate->template_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		if ($template->title != $oldtemplate->title) {
			$changed_fields .= "title='$template->title'";
			$ltext = $this->addAuditText($ltext, "Title=$oldtemplate->title to $template->title");
		}
/*
		if ($template->api != $oldtemplate->api) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "api='$template->api'";
			$ltext = $this->addAuditText($ltext, "API=$oldtemplate->api to $template->api");
		}
		*/

		if (!empty($changed_fields)) {
			$sql = "UPDATE skill_crm_template SET $changed_fields WHERE template_id='$oldtemplate->template_id'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {
			$this->addToAuditLog('Skill Template', 'U', $ltext, '');
		}
		
		return $is_update;
	}

	function deleteTemplate($tid)
	{
		/*
			Check skill for existing template
		*/
		$template_info = $this->getTemplateById($tid); 
		if (!empty($template_info)) {
			$sql = "DELETE FROM skill_crm_template WHERE template_id='$tid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid'";
				$this->getDB()->query($sql);
				$sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid'";
				$this->getDB()->query($sql);
				
				$this->addToAuditLog('Skill Template', 'D', "Template=$template_info->title", '');
				return true;
			}
		}
		return false;
	}
	
	function getMaxGroupID()
	{
		$sql = "SELECT MAX(group_id) AS numrows FROM skill_crm_disposition_group";
		
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}
	
	function addDispositionGroup($tid, $title)
	{
		if (empty($title)) return '';
		
		$id = $this->getMaxGroupID();
		
		if (empty($id)) { $id ='AA'; }
		else if ( $id == 'ZZ') { return ''; }
		else { $id++; }
		
		$sql = "INSERT INTO skill_crm_disposition_group SET ".
			"template_id='$tid', ".
			"group_id='$id', ".
			"title='$title'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Skill CRM Disposition Group', 'A', "Title=$title", "");
			return $id;
		}
		
		return '';
	}
	
	function getDispositionGroupOptions($tmp_id)
	{
		$return = array(''=>'Default');
		
		$sql = "SELECT group_id, title FROM skill_crm_disposition_group WHERE template_id='$tmp_id'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$return[$row->group_id] = $row->title;
			}
		}
		
		return $return;
	}

	function getDispositionOptions($tmp_id)
	{
		$return = array(''=>'Select');
		
		/*
		$sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE template_id='0000' ORDER BY title";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			foreach ($result as $row) {
				$return[$row->disposition_id] = $row->title;
			}
		}
		

		$sql = "SELECT disposition_id, title FROM disposition WHERE campaign_id='1111' ORDER BY title";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$return[$row->disposition_id] = $row->title;
			}
		}
		*/
		//if ($cmp_id != '1111' && $cmp_id != '0000') {
			$sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE template_id='$tmp_id'";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				foreach ($result as $row) {
					$return[$row->disposition_id] = $row->title;
				}
			}
		//}
		
		return $return;
	}

	function numDispositions($tid)
	{
		$sql = "SELECT COUNT(disposition_id) AS numrows FROM skill_crm_disposition_code ";
		if (!empty($tid)) $sql .= "WHERE template_id='$tid'";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function numDispositionGroups($tid)
	{
		$sql = "SELECT COUNT(group_id) AS numrows FROM skill_crm_disposition_group ";
		if (!empty($tid)) $sql .= "WHERE template_id='$tid'";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getDispositions($tid, $offset=0, $limit=0)
	{
		$sql = "SELECT * FROM skill_crm_disposition_code ";
		if (!empty($tid)) $sql .= "WHERE template_id='$tid' ";
		//$sql .= "ORDER BY title ";
		$sql .= "ORDER BY lft ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getDispositionGroups($tid, $offset=0, $limit=0)
	{
		$sql = "SELECT * FROM skill_crm_disposition_group ";
		if (!empty($tid)) $sql .= "WHERE template_id='$tid' ";
		$sql .= "ORDER BY title ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getDispositionById($disid, $tid='')
	{
		$sql = "SELECT * FROM skill_crm_disposition_code WHERE disposition_id='$disid' ";
		if (!empty($tid)) $sql .=  "AND template_id='$tid' ";
		$sql .= "LIMIT 1";
		$return = $this->getDB()->query($sql);
		if (is_array($return)) return $return[0];
		return null;
	}
	
	function getDispositionGroupById($gid, $tid='')
	{
		$sql = "SELECT * FROM skill_crm_disposition_group WHERE group_id='$gid' ";
		if (!empty($tid)) $sql .=  "AND template_id='$tid' ";
		$sql .= "LIMIT 1";
		$return = $this->getDB()->query($sql);
		if (is_array($return)) return $return[0];
		return null;
	}
	
	function updateService($oldservice, $service)
	{
		if (empty($oldservice->disposition_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		if ($service->disposition_id != $oldservice->disposition_id) {
			$changed_fields .= "disposition_id='$service->disposition_id'";
			$ltext = "Disposition Code=$oldservice->disposition_id to $service->disposition_id";
		}
		if ($service->title != $oldservice->title) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "title='$service->title'";
			$ltext = $this->addAuditText($ltext, "Title=$oldservice->title to $service->title");
		}
		
		if ($service->parent_id != $oldservice->parent_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "parent_id='$service->parent_id'";
			$ltext = $this->addAuditText($ltext, "Parent changed");
		}

		if (!empty($changed_fields)) {
			$sql = "UPDATE skill_crm_disposition_code SET $changed_fields WHERE disposition_id='$oldservice->disposition_id'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {
			$this->rebuildDispositionTree('', 0);
			$this->addToAuditLog('Skill CRM Disposition Code', 'U', "Disposition Code=".$oldservice->disposition_id, $ltext);
		}
		
		return $is_update;
	}
	
	function rebuildDispositionTree($parent, $left)
	{
		$right = $left+1;
		$sql = "SELECT disposition_id FROM skill_crm_disposition_code WHERE parent_id='$parent'";
		$result = $this->getDB()->query($sql);
	
		if (is_array($result)) {
			foreach ($result as $row) {
				$right = $this->rebuildDispositionTree($row->disposition_id, $right);
			}
		}
	
		$sql = "UPDATE skill_crm_disposition_code SET lft='$left', rgt='$right' WHERE disposition_id='$parent'";
		$this->getDB()->query($sql);
		return $right+1;
	}
	
	function updateDispositionGroup($oldservice, $service)
	{
		if (empty($oldservice->group_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		if ($service->title != $oldservice->title) {
			$changed_fields .= "title='$service->title'";
			$ltext = "Group title=$oldservice->title to $service->title";
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE skill_crm_disposition_group SET $changed_fields WHERE group_id='$oldservice->group_id'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {

			$this->addToAuditLog('Skill CRM Disposition Group', 'U', $ltext, "");
		}
		
		return $is_update;
	}
	
	function addService($tid, $service)
	{
		if (empty($service->disposition_id)) return false;
		
		$sql = "INSERT INTO skill_crm_disposition_code SET ".
			"template_id='$tid', ".
			"disposition_id='$service->disposition_id', ".
			"group_id='$service->group_id', ".
			"title='$service->title'";
		
		if ($this->getDB()->query($sql)) {
			$this->rebuildDispositionTree('', 0);
			$this->addToAuditLog('Skill CRM Disposition Code', 'A', "Disposition Code=".$service->disposition_id, "Title=$service->title");
			return true;
		}
		return false;
	}
	
	function deleteDispositionId($dcode, $tid)
	{
		$dcodeinfo = $this->getDispositionById($dcode, $tid); 
		if (!empty($dcodeinfo)) {
			$sql = "DELETE FROM skill_crm_disposition_code WHERE disposition_id='$dcode' AND template_id='$tid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Skill CRM Disposition Code', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->title);
				return true;
			}
		}
		return false;
	}
}

?>