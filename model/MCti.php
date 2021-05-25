<?php

class MCti extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getCtiOptions()
        {
                $options = array();
                $sql = "SELECT cti_id, cti_name FROM cti ORDER BY cti_name ";
                $result = $this->getDB()->query($sql);
                if (is_array($result)) {
                        foreach ($result as $ivr) {
                                $options[$ivr->cti_id] = $ivr->cti_name;
                        }
                }
                return $options;
        }

	function numCtis($status='')
	{
		$sql = "SELECT COUNT(cti_id) AS numrows FROM cti ";
		if (!empty($status)) $sql .= "WHERE active='$status'";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCtis($status='', $offset=0, $limit=0)
	{
		$sql = "SELECT cti_id, cti_name, action_type, skill_id, ivr_id, ".
			"active, param FROM cti ";
		if (!empty($status)) $sql .= "WHERE active='$status' ";
		$sql .= "ORDER BY cti_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getCtiById($ctiid)
	{
		$sql = "SELECT * FROM cti WHERE cti_id='$ctiid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$cti = $result[0];
			$cti->did = $this->getDID($ctiid, 'string');
			$cti->label = $this->getLabel($ctiid, 'string');
			return $cti;
		}
		return null;
	}
	
	function getDID($ctiid='', $format='sql', $isEmptyDid=false)
	{
		if (empty($ctiid) && !$isEmptyDid) {
			return $format == 'string' ? '' : null;
		}
		
		if ($isEmptyDid) {
			$sql = "SELECT did FROM did LIMIT 100";
		} else {
			$sql = "SELECT did FROM did WHERE cti_id='$ctiid' LIMIT 100";
		}
		//echo $sql;		
		$result = $this->getDB()->query($sql);
		
		if ($format == 'string') {
			if (is_array($result)) {
				$str = '';
				foreach ($result as $ct) {
					if (!empty($str)) $str .= ', ';
					$str .= $ct->did;
				}
				return $str;
			} else {
				return '';
			}
		} else if ($format == 'array') {
			if (is_array($result)) {
				$dids = array();
				foreach ($result as $ct) {
					$dids[] = $ct->did;
				}
				return $dids;
			}
		}
		return $result;
	}

	function getLabel($ctiid='', $format='sql', $isEmptyDid=false)
	{
		if (empty($ctiid) && !$isEmptyDid) {
			return $format == 'string' ? '' : null;
		}
		
		if ($isEmptyDid) {
			$sql = "SELECT label FROM did LIMIT 100";
		} else {
			$sql = "SELECT label FROM did WHERE cti_id='$ctiid' LIMIT 100";
		}
		//echo $sql;		
		$result = $this->getDB()->query($sql);
		
		if ($format == 'string') {
			if (is_array($result)) {
				$str = '';
				foreach ($result as $ct) {
					if (!empty($str)) $str .= ', ';
					$str .= $ct->label;
				}
				return $str;
			} else {
				return '';
			}
		} else if ($format == 'array') {
			if (is_array($result)) {
				$labels = array();
				foreach ($result as $ct) {
					$labels[] = $ct->label;
				}
				return $labels;
			}
		}
		return $result;
	}

	function updateCtiPriorityStatus($ctiid, $priority='')
	{
		if (empty($ctiid)) return false;
		if ($priority=='Y' || $priority=='N') {
			$sql = "UPDATE cti SET is_priority='$priority' WHERE cti_id='$ctiid'";
			if ($this->getDB()->query($sql)) {
				
				$ctiinfo = $this->getCtiById($ctiid);
				$ltxt = $priority=='Y' ? 'Disable to Enable' : 'Enable to Disable';
				$cti_title = empty($ctiinfo) ? $ctiid : $ctiinfo->cti_name;
				$this->addToAuditLog('CTI', 'U', "CTI=$cti_title", "Priority=".$ltxt);
				return true;
			}
		}
		return false;
	}

	function updateCti($oldcti, $cti, $value_options)
	{
		if (empty($oldcti->cti_id)) return false;
		$is_update = false;
		$fields_array = array(
			'cti_name' => 'cti_name',
			'action_type' => 'action_type',
			'skill_id' => 'skill_id',
			'ivr_id' => 'ivr_id',
			'cti_type' => 'cti_type',
			'active' => 'active',
			'param' => 'param',
			//'cli_length' => 'cli_length',
			//'cli_padding_prefix' => 'cli_padding_prefix',
			'language' => 'language'
		);
		$changed_fields = $this->getSqlOfChangedFields($oldcti, $cti, $fields_array);
		// GPrint($changed_fields);
		// die();
		if (!empty($changed_fields)) {
			$sql = "UPDATE cti SET $changed_fields WHERE cti_id='$oldcti->cti_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($oldcti->did != $cti->did) {
			$sql = "DELETE FROM did WHERE cti_id='$oldcti->cti_id'";
			$this->getDB()->query($sql);

			$clis = explode(",", $cti->did);
			$labels = explode(",", $cti->label);
			if (is_array($clis)) {
				foreach ($clis as $key=>$cli) {
					$cli = trim($cli);
					$cli = str_replace(array("("," ", "-", ")"),"", $cli);
					$label = $labels[$key];
					if (!empty($cli)) {
						$sql = "INSERT INTO did SET cti_id='$oldcti->cti_id', did='$cli', label='$label' ";
						$this->getDB()->query($sql);
					}
				}
			}
			$is_update = true;
		}
		
		if ($is_update) {
			$field_names = array(
				'cti_name' => 'name',
				'action_type' => 'Action type',
				'skill_id' => 'Skill name',
				'ivr_id' => 'IVR name',
				'did' => 'DID',
				'cti_type' => 'Interface',
				'active' => 'Status',
				'label' => 'DID Label'
				//'cli_length' => 'CLI length',
				//'cli_padding_prefix' => 'CLI padding prefix'
			);
			$fields_array['did'] = 'did';
			$fields_array['label'] = 'label';
			$audit_text = $this->getAuditText($oldcti, $cti, $fields_array, $field_names, $value_options);
			$this->addToAuditLog('CTI', 'U', "CTI=".$oldcti->cti_name, $audit_text);
		}
		
		return $is_update;
	}
}

?>