<?php

class MPassword extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getPasswordRules($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM password_settings ORDER BY rule_no ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function updatePasswordRule($ruleNo='', $status='', $value='') {
		if (empty($ruleNo) || (empty($status) && empty($value))) return false;
		$sql = "UPDATE password_settings SET ";
		if (!empty($status)) $sql .= "status = '$status' ";
		if (!empty($status) && !empty($value)) $sql .= ", value = '$value' ";
		elseif (!empty($value)) $sql .= "value = '$value' ";
		$sql .= "WHERE rule_no='$ruleNo'";
		if ($this->getDB()->query($sql)){
			$this->addToAuditLog('Password Rules', 'U', "", "Password Validation Rule $ruleNo Update");
			return true;
		}else {
			return false;
		}
	}

}

?>