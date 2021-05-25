<?php

class MChatLeaveMessage extends Model
{
	private $valid_field = [];

	public function __construct() {
		parent::__construct();
	}

	public function numLeaveMessages($field = '', $srcText='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM chat_leave_msg ";
		if (!empty($field) && !empty($srcText) && $field == 'id') $cond .= "id LIKE ?";
		if (!empty($field) && !empty($srcText) && $field == 'name') $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($field) && !empty($srcText) && $field == 'email') $cond = $this->getAndCondition($cond, "email LIKE ?");
		if (!empty($field) && !empty($srcText) && $field == 'number') $cond = $this->getAndCondition($cond, "number LIKE ?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if (!empty($field) && !empty($srcText) && $field == 'id'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}
		if (!empty($field) && !empty($srcText) && $field == 'name'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}	
		if (!empty($field) && !empty($srcText) && $field == 'email'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}
		if (!empty($field) && !empty($srcText) && $field == 'number'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}

		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getChatLeaveMessages($field = '', $srcText='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM chat_leave_msg ";		
		if (!empty($field) && !empty($srcText) && $field == 'id') $cond .= "id LIKE ?";
		if (!empty($field) && !empty($srcText) && $field == 'name') $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($field) && !empty($srcText) && $field == 'email') $cond = $this->getAndCondition($cond, "email LIKE ?");
		if (!empty($field) && !empty($srcText) && $field == 'number') $cond = $this->getAndCondition($cond, "number LIKE ?");		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if (!empty($field) && !empty($srcText) && $field == 'id'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}
		if (!empty($field) && !empty($srcText) && $field == 'name'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}	
		if (!empty($field) && !empty($srcText) && $field == 'email'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}
		if (!empty($field) && !empty($srcText) && $field == 'number'){  
			$condParams[] = ["paramType" => "s", "paramValue" => "%".$srcText."%", "paramRawValue" => $srcText]; 
		}
		
		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}
}

?>