<?php

class MCrmDisposition extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numDispositions()
	{
		$cond = '';
		$sql = "SELECT COUNT(disposition_id) AS numrows FROM disposition ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getDispositions($offset=0, $limit=0)
	{
		$sql = "SELECT d.campaign_id, d.disposition_id, d.title, c.title AS campaign_title FROM disposition AS d ".
			"LEFT JOIN campaign_profile AS c ON c.campaign_id=d.campaign_id ORDER BY disposition_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getDispositionsOpt($offset=0, $limit=0, $star_represent_blank=false)
	{
	    if($star_represent_blank){
	        $ret = array('*'=>'Select');
	    }else{
	        $ret = array(''=>'Select');
	    }
	    
	    $sql = "SELECT d.campaign_id, d.disposition_id, d.title, c.title AS campaign_title FROM disposition AS d ".
			"LEFT JOIN campaign_profile AS c ON c.campaign_id=d.campaign_id ORDER BY disposition_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
	    $result = $this->getDB()->query($sql);
	    if (is_array($result)) {
	        foreach ($result as $dp) {
	            if ($dp->campaign_id == '0000') $camtitle = 'System';
	            else if ($dp->campaign_id == '1111') $camtitle = 'General';
	            else $camtitle = $dp->campaign_title;

	            $ret[$dp->disposition_id] = $camtitle . ' - ' . $dp->title;
	        }
	    }
	    return $ret;
	}

	function getDispositionById($dcode)
	{
		$sql = "SELECT * FROM disposition WHERE disposition_id='$dcode'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getCampaignOptions()
	{
		$sql = "SELECT campaign_id, title FROM campaign_profile ORDER BY title";
		$result = $this->getDB()->query($sql);
		
		$return = array(''=>'Select','1111' => 'General');
		
		if (is_array($result)) {
			foreach ($result as $row) {
				$return[$row->campaign_id] = $row->title;
			}
		}
		
		return $return;
	}
	
	function getDispositionOptions($cmp_id)
	{
		$sql = "SELECT disposition_id, title FROM disposition WHERE campaign_id='0000' ORDER BY title";
		$result = $this->getDB()->query($sql);
		
		$return = array(''=>'Select');
		
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
		
		if ($cmp_id != '1111' && $cmp_id != '0000') {
			$sql = "SELECT disposition_id, title FROM disposition WHERE campaign_id='$cmp_id'";
			$result = $this->getDB()->query($sql);
			if (is_array($result)) {
				foreach ($result as $row) {
					$return[$row->disposition_id] = $row->title;
				}
			}
		}
		
		return $return;
	}

	function addDisposition($service, $campaign_options)
	{
		if (empty($service->campaign_id)) return false;
		
		$disposition_id = $this->getMaxDispositionID($service->campaign_id);
		
		if ($service->campaign_id == '1111') {
			if (empty($disposition_id)) { $disposition_id ='2001'; }
			else if ( $disposition_id == '9999') { return false; }
			else { $disposition_id++; }
		} else {
			if (empty($disposition_id)) { $disposition_id ='AAAA'; }
			else if ( $disposition_id == 'ZZZZ') { return false; }
			else { $disposition_id++; }
		}
		
		$sql = "INSERT INTO disposition SET campaign_id='$service->campaign_id', ".
			"disposition_id='$disposition_id', ".
			"title='$service->title'";
		
		if ($this->getDB()->query($sql)) {
			$cmp_name = isset($campaign_options[$service->campaign_id]) ? $campaign_options[$service->campaign_id] : $service->campaign_id;
			$this->addToAuditLog('Disposition Code', 'A', "Disposition Code=".$disposition_id, "Title=$service->title;Campaign=$cmp_name");
			return true;
		}
		return false;
	}
	
	function getMaxDispositionID($cm_id)
	{
		if ($cm_id == '1111') {
			$sql = "SELECT MAX(disposition_id) AS numrows FROM disposition WHERE campaign_id='$cm_id'";
		} else {
			$sql = "SELECT MAX(disposition_id) AS numrows FROM disposition WHERE campaign_id!='1111'";
		}
		
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}
	
	function updateDisposition($oldservice, $service)
	{
		if (empty($oldservice->disposition_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		/*
		if ($service->campaign_id != $oldservice->campaign_id) {
			$cmp_name = isset($campaign_options[$service->campaign_id]) ? $campaign_options[$service->campaign_id] : $service->campaign_id;
			$old_cmp_name = isset($campaign_options[$oldservice->campaign_id]) ? $campaign_options[$oldservice->campaign_id] : $oldservice->campaign_id;
			$changed_fields .= "campaign_id='$service->campaign_id'";
			$ltext = "Campaign=$old_cmp_name to $cmp_name";
		}
		*/
		/*
		if ($service->disposition_id != $oldservice->disposition_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "disposition_id='$service->disposition_id'";
			$ltext = $this->addAuditText($ltext, "Disposition Code=$oldservice->disposition_id to $service->disposition_id");
		}
		*/
		if ($service->title != $oldservice->title) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "title='$service->title'";
			$ltext = $this->addAuditText($ltext, "Title=$oldservice->title to $service->title");
		}
		

		if (!empty($changed_fields)) {
			$sql = "UPDATE disposition SET $changed_fields WHERE disposition_id='$oldservice->disposition_id'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {
			$this->addToAuditLog('Disposition Code', 'U', "Disposition Code=".$oldservice->disposition_id, $ltext);
		}
		
		return $is_update;
	}
	
	function deleteDispositionCode($dcode)
	{
		$dcodeinfo = $this->getDispositionById($dcode); 
		if (!empty($dcodeinfo)) {
			$sql = "DELETE FROM disposition WHERE disposition_id='$dcode' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Disposition Code', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->title);
				return true;
			}
		}
		return false;
	}
	
	function getServiceOptions()
	{
		$options = array(''=>'Select');
		$sql = "SELECT disposition_code, service_title FROM ivr_service_code ORDER BY disposition_code ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->disposition_code] = $skill->disposition_code . ' - ' . $skill->service_title;
			}
		}
		//var_dump($options);
		return $options;
	}

}

?>