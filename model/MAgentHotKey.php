<?php
class MAgentHotKey extends  Model{
	function  __construct(){
		parent::__construct();
	}
	
	function getAllRows($offset=0, $limit=0,$status='')
	{
		$options = array();
		$sql = "SELECT * FROM agent_hotkey ";
		if(!empty($status)){
			$sql.= " WHERE status='$status' ";
		}
		if ($limit > 0) $sql .= " LIMIT $offset, $limit";
		//echo $sql; die;
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result;
		}
		return array();
	}
	static function getAll($status='A')
	{	
		$thisobj=new self();
		return $thisobj->getAllRows(0,0,'A');
	}
	static function getAllAsObject()
	{
		 $result=self::getAll();
		 $return=new stdClass();
		 if(count($result)>0){
		 	foreach ($result as $hotkey ){
		 		$return->{$hotkey->action}=$hotkey->hot_key;
		 	}
		 }
		 return $return;
	}
	function numRows($status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(*) AS numrows FROM agent_hotkey ";
		if (!empty($status)) $cond = "active='$status'";
		$result = $this->getDB()->query($sql);
		if ($this->getDB()->getNumRows() == 1) {
		return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
	
		return 0;
	}
	/**
	 * @param String $action
	 * @return MAgentHotKey
	 */
	function getRowByKey($action)
	{
		$options = array();
		$sql = "SELECT * FROM agent_hotkey WHERE action='$action'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	function updateProperties($properties,$action)
	{
		if(count($properties)==0)return false;
		$hotkey = $this->getRowByKey($action);
		if (!empty($hotkey)) {
			$sql = "UPDATE agent_hotkey SET";
			foreach ($properties as $key=>$value){
				$sql.= " $key = '$value',";
			}
			$sql=rtrim($sql,",");
			$sql.=" WHERE action='$action' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Hotkey', 'U', implode(",", $properties),"");
				return true;
			}
		}else{
			AddError("Hotkey information invalid");
		}
		return false;
	}
	function updateStatus($action,$status)
	{
		$hotkey = $this->getRowByKey($action);
		if (!empty($hotkey)) {
			$sql = "UPDATE agent_hotkey SET status = '$status' WHERE action='$action' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Hotkey', 'U', "action=$action","");
				return true;
			}
		}
		return false;
	}
}