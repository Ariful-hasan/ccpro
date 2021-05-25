<?php

class MMusic extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getMusicFolders($id='')
	{
		$cond = '';
		$sql = "SELECT fl_id, name FROM music_folder ";
		if (!empty($id)) $cond = "fl_id='$id'";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY name ";
		$result = $this->getDB()->query($sql);
		if (!empty($id) && is_array($result)) return $result[0];
		return $result;
	}

	function updateFolder($flid='', $name='')
	{
		if (empty($flid) || empty($name)) return false;
		
		$sql = "UPDATE music_folder SET name='$name' WHERE fl_id='$flid'";
		return $this->getDB()->query($sql);
	}
}

?>