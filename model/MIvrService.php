<?php

class MIvrService extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numServices()
	{
		$cond = '';
		$sql = "SELECT COUNT(disposition_code) AS numrows FROM ivr_service_code ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getServices($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM ivr_service_code ORDER BY service_title ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getServiceByCode($dcode)
	{
		$sql = "SELECT * FROM ivr_service_code WHERE disposition_code='$dcode'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addService($service)
	{
		if (empty($service->disposition_code)) return false;
		
		$sql = "INSERT INTO ivr_service_code SET ".
			"disposition_code='$service->disposition_code', ".
			"service_title='$service->service_title', ".
			"service_type='$service->service_type'";
		$sql .= !empty($service->parent_id) ? ", parent_id='$service->parent_id' " : "";
		$sql .= !empty($service->report_category) ? ", report_category='$service->report_category' " : "";
		$sql .= !empty($service->report_type) ? ", report_type='$service->report_type' " : "";

		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('IVR Disposition Code', 'A', "Disposition Code=".$service->disposition_code, "Title=$service->service_title");
			return true;
		}
		return false;
	}
	
	function updateService($oldservice, $service)
	{
		if (empty($oldservice->disposition_code)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		if ($service->disposition_code != $oldservice->disposition_code) {
			$changed_fields .= "disposition_code='$service->disposition_code'";
			$ltext = "Disposition Code=$oldservice->disposition_code to $service->disposition_code";
		}
		if ($service->service_title != $oldservice->service_title) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "service_title='$service->service_title'";
			$ltext = $this->addAuditText($ltext, "Title=$oldservice->service_title to $service->service_title");
		}
		if ($service->service_type != $oldservice->service_type) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "service_type='$service->service_type'";
			$ltext = $this->addAuditText($ltext, "Type=$oldservice->service_type to $service->service_type");
		}
        if ($service->parent_id != $oldservice->parent_id) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "parent_id='$service->parent_id'";
            $ltext = $this->addAuditText($ltext, "Parent=$oldservice->parent_id to $service->parent_id");
        }
        if ($service->report_category != $oldservice->report_category) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "report_category='$service->report_category'";
            $ltext = $this->addAuditText($ltext, "Report_category=$oldservice->report_category to $service->report_category");
        }
        if ($service->report_type != $oldservice->report_type) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "report_type='$service->report_type'";
            $ltext = $this->addAuditText($ltext, "Report_type=$oldservice->report_type to $service->report_type");
        }
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE ivr_service_code SET $changed_fields WHERE disposition_code='$oldservice->disposition_code'";
			$is_update = $this->getDB()->query($sql);
		}
		

		if ($is_update) {

			$this->addToAuditLog('IVR Disposition Code', 'U', "Disposition Code=".$oldservice->disposition_code, $ltext);
		}
		
		return $is_update;
	}
	
	function deleteDispositionCode($dcode)
	{
		$dcodeinfo = $this->getServiceByCode($dcode); 
		if (!empty($dcodeinfo)) {
			$sql = "DELETE FROM ivr_service_code WHERE disposition_code='$dcode' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('IVR Disposition Code', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->service_title);
				return true;
			}
		}
		return false;
	}
	
	function getServiceOptions($type='', $isSetStar=false)
	{
	    if ($isSetStar){
	        $options = array('*'=>'Select');
	    }else {
		    $options = array(''=>'Select');
	    }
	    $options['ABDNCB'] = 'ABDNCB - Abandoned Call';
		$sql = "SELECT disposition_code, service_title FROM ivr_service_code ";
		if (!empty($type)) $sql .= "WHERE service_type='$type' ";
		$sql .= 'ORDER BY disposition_code';
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->disposition_code] = $skill->disposition_code . ' - ' . $skill->service_title;
			}
		}
		//var_dump($options);
		return $options;
	}

	function getAllDisposition($dis=null){
        $response = [];
        $sql = "SELECT disposition_code,service_title FROM ivr_service_code  ";
        $sql .= !empty($dis) ?  " WHERE disposition_code != '$dis' " : "";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $key) {
                $response[$key->disposition_code] =  $key->service_title;
            }
        }
        return $response;
    }

}

?>