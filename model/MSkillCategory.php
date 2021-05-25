<?php

class MSkillCategory extends Model
{
	public function __construct() {
		parent::__construct();
	}

	public function numSkillCategories($id = '', $name='', $status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM skill_category ";
		if (!empty($id)) $cond .= "id= ?";
		if (!empty($name)) $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($status)) $cond = $this->getAndCondition($cond, "status=?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "3"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getSkillCategories($id='', $name='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM skill_category ";
		if (!empty($id)){ 
			$cond .= "id= ?";
		}		
		if (!empty($name)){
			$cond = $this->getAndCondition($cond, "name LIKE ?");
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "status=?");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY cat_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "3"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);

		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	function getSkillCategoriesNameArray(){
		$returnArray=array();
		$sql = "SELECT cat_id, name FROM skill_category ";		
        $sql .= " ORDER BY cat_id ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->cat_id]=$data->name;
			}
		}
		return $returnArray;
	}
}

?>