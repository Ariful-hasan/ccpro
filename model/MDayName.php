<?php

class MDayName extends Model
{
	private $valid_field = [
		'name' => [
			'len' => 35,
			'validation' => [
				'required' => true, 
				'max-len' => 35
			],
			'validation_msg' => [
				'required' => "Name is required!", 
				'max-len' => "Please enter no more than 35 characters!",
			],
			'special_char_check' => true
		],
		'type' => [
			'len' => 1,
			'validation' => [
				'required' => true, 
				'max-len' => 1
			],
			'validation_msg' => [
				'required' => "Type is required!", 
				'max-len' => "Please enter no more than 1 character!",
			],
			'special_char_check' => true
		],
		'status' => [
			'len' => 1,
			'validation' => [
				'required' => true, 
				'max-len' => 1
			],
			'validation_msg' => [
				'required' => "Status is required!", 
				'max-len' => "Please enter no more than 1 character!",
			],
			'special_char_check' => true
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function numDayNames($id='', $type = '', $name='', $status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM fc_special_day ";
		if (!empty($id)) $cond .= "id=?";
		if (!empty($type)) $cond .= "type=?";
		if (!empty($name)) $cond .= $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($status)) $cond .= $this->getAndCondition($cond, "status=?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "10"]; }
		if(!empty($type)){  $condParams[] = ["paramType" => "s", "paramValue" => $type, "paramLength" => "1"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramLength" => "35", "paramRawValue" => $name]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getDayNames($id='', $type='', $name='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM fc_special_day ";
		if (!empty($id)){ 
			$cond .= "id=?";
		}
		if (!empty($type)){ 
			$cond .= "type=?";
		}		
		if (!empty($name)){
			$cond .= $this->getAndCondition($cond, "name LIKE ?");
		}
		if (!empty($status)){ 
			$cond .= $this->getAndCondition($cond, "status=?");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "10"]; }
		if(!empty($type)){  $condParams[] = ["paramType" => "s", "paramValue" => $type, "paramLength" => "1"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramLength" => "35", "paramRawValue" => $name]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	public function updateDayNameStatus($id, $status='')
	{		
		$login_user = UserAuth::getCurrentUser();
		$sql = "UPDATE fc_special_day SET status='$status' WHERE id='$id'";		
		if ($this->getDB()->query($sql)) {
			$ltxt = $status==MSG_YES ? 'Inactive to Active' : 'Active to Inactive';
			$this->addToAuditLog('FC Day Name', 'F', "User=$login_user", "Status=".$ltxt);
			return true;
		}
		return false;
	}

	private function validate($post_data)
	{
		$day_name = new stdClass();
		$validator = new FormValidator();

		if (is_array($post_data)) {
			$count = 0;
			unset($post_data['submit']);
			foreach ($post_data as $key=>$val) {
				if(isset($this->valid_field[$key]['type']) && isset($this->valid_field[$key]['type']) == 'json')
					$val = json_encode($val, JSON_NUMERIC_CHECK);

				if(isset($this->valid_field[$key]) && isset($this->valid_field[$key]['validation'])){
					$valid = $validator->validation(trim($val), $this->valid_field[$key]['validation'], $this->valid_field[$key]['validation_msg']);
					if(!$valid['result']){
						$errors[$key] = $valid['msg'];
					}
				}
				$day_name->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $day_name,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		if(empty($day_name->id)){
			$day_name->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $day_name,
		];
	}

	public function saveData($post_data){
		$validate = $this->validate($post_data);

		if(!$validate[MSG_RESULT]){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_ERROR_DATA => $validate[MSG_ERROR_DATA],
				MSG_MSG => 'You have some form errors. Please check below!'
			];
		}

		$login_user = UserAuth::getCurrentUser();
		$sql = '';
		$log_txt = '';
		$is_insert = false;

		if(!empty($validate[MSG_DATA])){
			$count = 0;			
			foreach ((array)$validate[MSG_DATA] as $key => $value) {
				$fields[$count] = $key;
				$values[$count] = '?';
				$condParams[$count] = [
					"paramType" => "s", 
					"paramValue" => $value,
					'specialCharCheck' => (isset($this->valid_field[$key]['special_char_check']) ? $this->valid_field[$key]['special_char_check'] : false)				
				];	

				if(isset($this->valid_field[$key]['len'])){
					$condParams[$count]['paramLength'] = $this->valid_field[$key]['len'];
				}

				$log_txt .= ucfirst($key)."='$value'; ";
				$count++;
			}
			$sql = "INSERT INTO fc_special_day(".implode(",", $fields).") VALUES(".implode(",", $values).")";
			// echo $sql;
			// pr($fields);
			// pr($values);
			// pr($condParams);
			// die();

			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			if($validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
			}
		}

		if ($is_insert) {
			$this->addToAuditLog('Forecast Day Name', 'F', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Name has been submitted successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Name has not been submitted successfully.'
			];
		}
	}

	public function updateData($post_data, $old_data){
		$validate = $this->validate($post_data);
		
		if(!$validate[MSG_RESULT]){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_ERROR_DATA => $validate[MSG_ERROR_DATA],
				MSG_MSG => 'You have some form errors. Please check below!'
			];
		}

		$login_user = UserAuth::getCurrentUser();
		$sql = '';
		$log_txt = '';
		$is_update = false;
		$isNotifyNeeded = false;

		if(!empty($validate[MSG_DATA])){
			$count = 0;
			foreach ((array)$validate[MSG_DATA] as $key => $value) {
				if ($value != $old_data->$key) {
					$field_values[$count] = $key."= ? ";				
					$condParams[$count] = [
						"paramType" => "s", 
						"paramValue" => $value,
						'specialCharCheck' => (isset($this->valid_field[$key]['special_char_check']) ? $this->valid_field[$key]['special_char_check'] : false)				
					];	

					if(isset($this->valid_field[$key]['len'])){
						$condParams[$count]['paramLength'] = $this->valid_field[$key]['len'];
					}

					$log_txt = $this->addAuditText($log_txt, ucfirst($key)."=".$old_data->$key." to ".$value);
					$count++;
				}
			}
			$sql = "UPDATE fc_special_day SET ".implode(',', $field_values)." WHERE id= ? ";
			$condParams[] = ["paramType" => "s", "paramValue" => $old_data->id];
			// echo $sql;
			// pr($field_values);
			// pr($condParams);
			// pr($log_txt);

			$validate = $this->getDB()->validateQueryParams($condParams);
			// dd($validate); 
			if($validate['result'] == true){
				$is_update = $this->getDB()->executeInsertQuery($sql, $validate['paramTypes'], $validate['bindParams']);
			}
		}

		if ($is_update) {
			$this->addToAuditLog('Day Name Update', 'F', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Name has been updated successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Name has not been updated successfully.'
			];
		}
	}

	public function checkUniqueDayName($name, $id){
		$cond = '';
		$sql = "SELECT count(name) as name_e FROM fc_special_day ";
		if (!empty($id)){ 
			$cond .= "id!=?";
		}		
		if (!empty($name)){
			$cond .= $this->getAndCondition($cond, "name=?");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "10"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => $name, "paramLength" => "35", "paramRawValue" => $name]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		// var_dump($result[0]->name_e);

		return empty($result[0]->name_e) ? 'true':'false';
	}

	public function getNameList($id='', $type='', $name='', $status=''){
		$data = $this->getDayNames($id, $type, $name, $status);
		$tmp_data = [];
		
		foreach ($data as $key => $item) {
			$tmp_data[$item->type][$item->id] = $item->name;
		}

		return $tmp_data;
	}
}

?>