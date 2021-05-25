<?php

class MAccount extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getAccountFromUrl($name)
	{
		$sql = "SELECT db_suffix, account_id, device_display_name FROM cc_master.account WHERE device_display_name='$name' LIMIT 1";
		$result = $this->getDB()->query($sql);

		if ($this->getDB()->getNumRows() == 1) {
			return $result[0];
		}

		return null;
	}

}

?>