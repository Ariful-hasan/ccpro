<?php

class MAppMapAddress extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numAddresses()
	{
		$cond = '';
		$sql = "SELECT COUNT(address_id) AS numrows FROM app_map_address ";
		//if (!empty($usertype)) $cond .= "usertype='$usertype'";
		//if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
		//if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getAddresses($offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM app_map_address ";
		//if (!empty($usertype)) $cond .= "usertype='$usertype'";
		//if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
		//if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY branch_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getAddressById($id)
	{
		$sql = "SELECT * FROM app_map_address WHERE address_id='$id'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getNewAddressId()
	{
		$id = '';
		
		$sql = "SELECT MAX(address_id) AS mid FROM app_map_address";
		$result = $this->getDB()->query($sql);
		
		if($this->getDB()->getNumRows() == 1) {
			$id = empty($result[0]->mid) ? '' : $result[0]->mid;
		}
		
		if (empty($id)) {
			$id = 'AAA';
		} else {
			if ($id == 'ZZZ') $id = '';
			else $id++;
		}
		
		return $id;
	}
	
	function addAddress($address)
	{
		$id = $this->getNewAddressId();
		
		if (empty($id)) return false;
		
		$branch_name = $this->getDB()->escapeString($address->branch_name);
		$br_address = $this->getDB()->escapeString($address->address);
		
		$sql = "INSERT INTO app_map_address SET ".
			"address_id='$id', ".
			"branch_name='$branch_name', ".
			"address='$br_address', ".
			"latitude='$address->latitude', ".
			"longitude='$address->longitude'";
		
		if ($this->getDB()->query($sql)) {
			$ltxt = "Latitude=".$address->latitude.";Longitude=".$address->longitude;
			$this->addToAuditLog('Map Address', 'A', "Branch name=".$branch_name, $ltxt);
			return true;
		}
		
		return false;
	}
	
	function updateAddress($oldaddress, $address)
	{
		if (empty($oldaddress->address_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		$branch_name = $this->getDB()->escapeString($address->branch_name);
		$br_address = $this->getDB()->escapeString($address->address);
		$old_branch_name = $this->getDB()->escapeString($oldaddress->branch_name);
		$old_br_address = $this->getDB()->escapeString($oldaddress->address);
		
		if ($address->branch_name != $oldaddress->branch_name) {
			$changed_fields .= "branch_name='$branch_name'";
			$ltext = "Branch name=$old_branch_name to $branch_name";
		}
		
		if ($address->address != $oldaddress->address) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "address='$br_address'";
			$ltext = $this->addAuditText($ltext, "Address=$old_br_address to $br_address");
		}
		
		if ($address->latitude != $oldaddress->latitude) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "latitude='$address->latitude'";
			$ltext = $this->addAuditText($ltext, "Latitude=$oldaddress->latitude to $address->latitude");
		}
		
		if ($address->longitude != $oldaddress->longitude) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "longitude='$address->longitude'";
			$ltext = $this->addAuditText($ltext, "Longitude=$oldaddress->longitude to $address->longitude");
		}
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE app_map_address SET $changed_fields WHERE address_id='$oldaddress->address_id'";
			$is_update = $this->getDB()->query($sql);
		}

		if ($is_update) {
			$this->addToAuditLog('Map Address', 'U', "Branch name=".$branch_name, $ltext);
		}
		
		return $is_update;
	}

	function deleteAddress($address_id)
	{
		$addressinfo = $this->getAddressById($address_id); 
		if (!empty($addressinfo)) {
			$sql = "DELETE FROM app_map_address WHERE address_id='$address_id' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$branch_name = $this->getDB()->escapeString($addressinfo->branch_name);
				$br_address = $this->getDB()->escapeString($addressinfo->address);
				
				$this->addToAuditLog('Map Address', 'D', "Branch name=$branch_name", "Address=".$br_address);
				return true;
			}
		}
		return false;
	}

}

?>