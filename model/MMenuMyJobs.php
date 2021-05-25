<?php

class MMenuMyJobs extends Model
{
	
	function __construct() {
		parent::__construct();
	}
	
	function getMyJobs($agentid)
	{
		$sql = "SELECT SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, SUM(IF(status='C', 1, 0)) AS num_client_pendings FROM e_ticket_info ";
		$sql .= "WHERE assigned_to='$agentid'";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
}

?>