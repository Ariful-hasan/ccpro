<?php

class MBlf_key extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function getTrunks()
	{
		$sql = "SELECT trunkid, label, trunk_name, trunk_type, ch_capacity, ch_running, dial_prefix, active FROM trunk ";
		$sql .= "ORDER BY trunk_name";

		return $this->getDB()->query($sql);
	}
	
	function deleteTrunk($trunkid)
	{
		$trunk = $this->getTrunkById($trunkid);
		if (!empty($trunk)) {
			$sql = "DELETE FROM trunk WHERE trunkid='$trunkid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM trunk_cli WHERE trunkid='$trunkid'";
				$this->getDB()->query($sql);
				$this->addToAuditLog('Trunk', 'D', "Trunk=".$trunk->trunk_name, '');
				return true;
			}
		}
		return false;
	}


	function addCLINode($trunkid, $node, $trunkname, $skillname)
	{
		if (empty($trunkid)) return false;
		$node->dial_prefix = trim($node->dial_prefix);
		$prefix_length = strlen($node->dial_prefix);
		$sql = "INSERT INTO trunk_cli SET ".
			"trunkid='$trunkid', ".
			"skill_id='$node->skill', ".
			"cli='$node->cli', ".
			"dial_prefix='$node->dial_prefix', ".
			"prefix_len='$prefix_length'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$ltxt = "skill=".$skillname.";Number=".$node->cli.";Dial prefix=".$node->dial_prefix;
			$this->addToAuditLog('Trunk CLI', 'A', "Trunk=".$trunkname, $ltxt);

			return true;
		}
		
		return false;
	}

	
	function addTrunk($trunkid, $trunk)
	{
		if (empty($trunkid)) return false;
		$trunk->dial_prefix = trim($trunk->dial_prefix);
		$prefix_length = strlen($trunk->dial_prefix);
		$sql = "INSERT INTO trunk SET trunkid='$trunkid', label='$trunk->label', trunk_type='$trunk->trunk_type', trunk_name='$trunk->trunk_name', ".
			"ip='$trunk->ip', ch_capacity='$trunk->ch_capacity', dial_prefix='$trunk->dial_prefix', prefix_len='$prefix_length'";
		if ($this->getDB()->query($sql)) {
			/*
			$clis = explode(",", $trunk->numbers);
			if (is_array($clis)) {
				foreach ($clis as $cli) {
					$cli = trim($cli);
					if (!empty($cli)) {
						$sql = "INSERT INTO trunk_cli SET trunkid='$trunkid', cli='$cli'";
						$this->getDB()->query($sql);
					}
				}
			}
			*/
			return true;
		}
		return false;
	}

	
	function getAllAgentArr(){
	    $agentArray = array();
	    $sql = "SELECT agent_id, nick FROM agents ORDER BY agent_id";
	    $result = $this->getDB()->query($sql);
	    if (is_array($result)) {
	        foreach ($result as $resultKey) {
	            $agentArray[$resultKey->agent_id] = $resultKey->nick;
	        }
	    }
	    return $agentArray;
	}

	function getAllSeatArr(){
	    $seatArray = array();
	    $sql = "SELECT seat_id, label FROM seat ORDER BY seat_id";
	    $result = $this->getDB()->query($sql);
	    if (is_array($result)) {
	        foreach ($result as $resultKey) {
	            $seatArray[$resultKey->seat_id] = $resultKey->label;
	        }
	    }
	    return $seatArray;
	}

	function getSeatById($seatid){
		$sql = "SELECT * FROM seat WHERE seat_id='$seatid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getBlfSettingByKey($seat, $unit, $bkey){
	    $sql = "SELECT * FROM blf_key WHERE seat_id='$seat' AND unit_id='$unit' AND key_id='$bkey' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getAllBlfKeys($seat, $unit){
	    $blfKeys = array();
	    $sql = "SELECT key_id FROM blf_key WHERE seat_id='$seat' AND unit_id='$unit'";
	    $result = $this->getDB()->query($sql);
	    if (is_array($result)) {
	        foreach ($result as $resultKey) {
	            $blfKeys[] = $resultKey->key_id;
			}
	    }
	    return $blfKeys;
	}
	
	function numBlfSettings($seat, $unit){
	    $totalRows = 0;
	    $sql = "SELECT count(key_id) AS numrows FROM blf_key WHERE seat_id='$seat' AND unit_id='$unit'";
	    $result = $this->getDB()->query($sql);//echo $sql;
	    $totalRows = is_array($result) ? $result[0]->numrows : 0;
	    return $totalRows;
	}
	
	function getBlfSettings($seat, $unit, $offset=0, $limit=0){
	    $sql = "SELECT * FROM blf_key WHERE seat_id='$seat' AND unit_id='$unit' ";
	    $sql .= "ORDER BY key_id ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	    
	    return $this->getDB()->query($sql);
	}
	
	function addBlfNewKey($seat, $unit, $service){
	    if (empty($seat) || empty($unit) || empty($service->key_id)){
	        return false;
	    }
	    $sql = "INSERT INTO blf_key SET seat_id='$seat', unit_id='$unit', key_id='$service->key_id', monitor='$service->monitor', label='$service->label', type='$service->type'";
	    if ($this->getDB()->query($sql)) {
	        return true;
	    }
	    return false;	    
	}
	
	function deleteBlfSettings($seat, $unit, $bkey){
	    if (!empty($seat) && !empty($unit) && !empty($bkey)) {
	        $sql = "DELETE FROM blf_key WHERE seat_id='$seat' AND unit_id='$unit' AND key_id='$bkey' LIMIT 1";
	        if ($this->getDB()->query($sql)) {
	            return true;
	        }
	    }
	    return false;
	}
	
	function updateBlfSettings($seat, $unit, $bkey, $oldService, $service)
	{
		if (empty($seat) || empty($unit) || empty($bkey)) return false;
		$is_update = false;

		$field_array = array(
			'label' => 'label',
			'key_id' => 'key_id',
			'monitor' => 'monitor',
			'type' => 'type'
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldService, $service, $field_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE blf_key SET $changed_fields WHERE seat_id='$seat' AND unit_id='$unit' AND key_id='$bkey' LIMIT 1";
			$is_update = $this->getDB()->query($sql);
		}
		
		return $is_update;
	}

}

?>