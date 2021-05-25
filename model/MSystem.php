<?php

class MSystem extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getChannelUseByHour($day, $iid='')
	{
		$sql = "SELECT log_hour, value FROM system_log WHERE log_date='$day' AND item_code='$iid' GROUP BY log_hour";
		return $this->getDB()->query($sql);
	}

	function getChannelUseByDay($year, $month, $iid='')
	{
		$sdate = $year . '-' . $month . '-' . '01';
		$edate = $year . '-' . $month . '-' . '31';
		$sql = "SELECT log_date, MAX(value) AS value FROM system_log WHERE log_date BETWEEN '$sdate' AND '$edate' AND item_code='$iid' GROUP BY log_date";
		return $this->getDB()->query($sql);
	}
	
	function getCurrentTemperatures()
	{
		$sql = "SELECT * FROM system_profile WHERE item_code IN ('A', 'B')";
		return $this->getDB()->query($sql);
	}

	function getCurrentLoads()
	{
		$sql = "SELECT * FROM system_profile WHERE item_code='C'";
		return $this->getDB()->query($sql);
	}
	
	function getCurrentLogById($iid)
	{
		$sql = "SELECT * FROM system_profile WHERE item_code='$iid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
}

?>