<?php

class MIvrAPI extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numConnections()
	{
		$cond = '';
		$sql = "SELECT COUNT(conn_name) AS numrows FROM ivr_api ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getConnections($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM ivr_api ORDER BY conn_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getConnectionByName($cname)
	{
		$sql = "SELECT * FROM ivr_api WHERE conn_name='$cname'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addConnection($connection)
	{
		if (empty($connection->conn_name)) return false;
		
		$sql = "INSERT INTO ivr_api SET ".
			"conn_name='$connection->conn_name', ".
			"conn_method='$connection->conn_method', ".
			"url='$connection->url', ".
			"credential='$connection->credential', ".
			"pass_credential='$connection->pass_credential', ".
			"submit_method='$connection->submit_method', ".
			"submit_param='$connection->submit_param', ".
			"return_method='$connection->return_method', ".
			"return_param='$connection->return_param', ".
			"active='$connection->active'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('IVR API', 'A', "Connection=".$connection->conn_name, "Method=$connection->conn_method");
			return true;
		}
		return false;
	}
	
	function updateConnection($oldconnection, $connection, $value_options)
	{
		if (empty($oldconnection->conn_name)) return false;
		$is_update = false;
		$fields_array = array(
			'conn_method' => 'conn_method',
			'url' => 'url',
			'credential' => 'credential',
			'pass_credential' => 'pass_credential',
			'submit_method' => 'submit_method',
			'submit_param' => 'submit_param',
			'return_method' => 'return_method',
			'return_param' => 'return_param',
			'active' => 'active'
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldconnection, $connection, $fields_array);
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE ivr_api SET $changed_fields WHERE conn_name='$oldconnection->conn_name'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
				'conn_method' => 'Method',
				'url' => 'URL',
				'credential' => 'Credential',
				'pass_credential' => 'Pass Credential',
				'submit_method' => 'Submit Method',
				'submit_param' => 'Submit Param',
				'return_method' => 'Return Method',
				'return_param' => 'Return Param',
				'active' => 'Status'
			);

			$audit_text = $this->getAuditText($oldconnection, $connection, $fields_array, $field_names, $value_options);
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('IVR API', 'U', "Connection=".$oldconnection->conn_name, $audit_text);
		}

		return $is_update;
	}
	
	function deleteConnection($cname)
	{
		$apiinfo = $this->getConnectionByName($cname); 
		if (!empty($apiinfo)) {
			$sql = "DELETE FROM ivr_api WHERE conn_name='$cname' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('IVR API', 'D', "Connection=".$apiinfo->conn_name, "Method=$apiinfo->conn_method");
				return true;
			}
		}
		return false;
	}
	
	/*
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
	*/

}

?>