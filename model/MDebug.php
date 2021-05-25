<?php

class MDebug extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function numDebugLog($dateinfo)
	{
		$cond = $this->getDateCond($dateinfo);

		$sql = "SELECT COUNT(tstamp) AS numrows FROM debug_log ";
		if (!empty($cond)) $sql .= "WHERE $cond";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getDebugLog($dateinfo, $offset, $limit)
	{
		$cond = $this->getDateCond($dateinfo);

		$sql = "SELECT * FROM debug_log ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getDateCond($dateinfo)
	{
		$cond = '';
		if (!empty($dateinfo->sdate) && !empty($dateinfo->edate)) {
			$cond = "tstamp BETWEEN '$dateinfo->sdate 00:00:00' AND '$dateinfo->edate 23:59:59'";
		} else if (!empty($dateinfo->sdate)) {
			$cond = "tstamp BETWEEN '$dateinfo->sdate 00:00:00' AND '$dateinfo->sdate 23:59:59'";
		} else if (!empty($dateinfo->edate)) {
			$cond = "tstamp BETWEEN '$dateinfo->edate 00:00:00' AND '$dateinfo->edate 23:59:59'";
		}
		return $cond;
	}
	
}

?>