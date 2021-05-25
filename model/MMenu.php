<?php

class MMenu extends Model
{
	private $valid_field = [
		'name' => [
			'len' => 25,
			'validation' => [
				'required' => true, 
				'max-len' => 25
			],
			'validation_msg' => [
				'required' => "Name is required!", 
				'max-len' => "Please enter no more than 25 characters!",
			],
			'special_char_check' => true
		],
		'id' => [
			'len' => 10,
			'special_char_check' => true
		],
		'menu_info' => [
			'len' => 100000,
			'special_char_check' => false
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function numMenus($id = '', $name='', $status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM menus ";
		if (!empty($id)) $cond .= "id LIKE ?";
		if (!empty($name)) $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($status)) $cond = $this->getAndCondition($cond, "status=?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$id."%", "paramLength" => "12", "paramRawValue" => $id]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getMenus($id='', $name='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM menus ";
		if (!empty($id)){ 
			$cond .= "id LIKE ?";
		}		
		if (!empty($name)){
			$cond = $this->getAndCondition($cond, "name LIKE ?");
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "status=?");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$id."%", "paramLength" => "12", "paramRawValue" => $id]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	private function validate($post_data)
	{
		$menu = new stdClass();
		$validator = new FormValidator();

		if (is_array($post_data)) {
			$count = 0;
			unset($post_data['submit']);
			foreach ($post_data as $key=>$val) {
				if(isset($this->valid_field[$key]) && isset($this->valid_field[$key]['validation'])){
					$valid = $validator->validation(trim($val), $this->valid_field[$key]['validation'], $this->valid_field[$key]['validation_msg']);
					if(!$valid['result']){
						$errors[$key] = $valid['msg'];
					}
				}
				$menu->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $menu,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		
		if(!empty($menu->id)){
			$menu->updated_by = $login_user;
			$menu->updated_at = date("Y-m-d H:i:s");
		}else{
			$menu->created_by = $login_user;
			$menu->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $menu,
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
			$sql = "INSERT INTO menus(".implode(",", $fields).") VALUES(".implode(",", $values).")";
			// echo $sql;
			// pr($fields);
			// pr($values);
			// pr($condParams);
			// die();

			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			// dd($validateQuery);
			if($validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
			}
		}

		if ($is_insert) {
			$this->addToAuditLog('Menu add', 'MN', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Menu has been submitted successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Menu has not been submitted successfully.'
			];
		}
	}

	public function updateMenuStatus($id, $status='')
	{		
		$login_user = UserAuth::getCurrentUser();
		$sql = "UPDATE menus SET status='$status' WHERE id='$id'";		
		if ($this->getDB()->query($sql)) {
			$ltxt = $status==STATUS_ACTIVE ? 'Inactive to Active' : 'Active to Inactive';
			$this->addToAuditLog('Menu', 'PG', "User=$login_user", "Status=".$ltxt);
			return true;
		}
		return false;
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

		//json data 
		$json_data = json_decode($validate[MSG_DATA]->menu_info);
		$validate[MSG_DATA]->menu_info = json_encode($json_data, JSON_NUMERIC_CHECK);

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
			$sql = "UPDATE menus SET ".implode(',', $field_values)." WHERE id= ? ";
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
			$this->addToAuditLog('Menu Update', 'PG', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Menu has been updated successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Menu has not been updated successfully.'
			];
		}
	}
}

?>