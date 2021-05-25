<?php

class MDaySetting extends Model
{
	private $valid_field = [		
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
		'day_id' => [
			'len' => 10,
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "Name is required!",
			],
			'special_char_check' => true
		],
		'mdate' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Date is required!"
			],
			'special_char_check' => true
		],
		'l_window' => [
			'validation' => [
				'required' => true,
				'max' => 9,
				'min' => 0,
			],
			'validation_msg' => [
				'required' => "Lower Window is required!",
				'max' => "Please enter a value less than or equal to 9!", 
				'min' => "Please enter a value greater than or equal to 0!", 
			],
			'special_char_check' => true
		],
		'u_window' => [
			'validation' => [
				'required' => true,
				'max' => 9,
				'min' => 0,
			],
			'validation_msg' => [
				'required' => "Upper Window is required!",
				'max' => "Please enter a value less than or equal to 9!", 
				'min' => "Please enter a value greater than or equal to 0!", 
			],
			'special_char_check' => true
		],
		'priority' => [
			'validation' => [
				'required' => true,
				'max' => 100,
				'min' => 1,
			],
			'validation_msg' => [
				'required' => "Lower Window is required!",
				'max' => "Please enter a value less than or equal to 100!", 
				'min' => "Please enter a value greater than or equal to 1!", 
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

	public function numDaySettings($id='', $type = '', $day_id='', $status='')
	{
		$cond = '';
		$join = '';
		$sql = "SELECT COUNT(fcds.id) AS numrows FROM fc_day_settings as fcds ";
		if (!empty($id)) $cond .= "fcds.id=?";
		if (!empty($type)) $cond .= "fcds.type=?";
		if (!empty($day_id)){ 
			$cond .= $this->getAndCondition($cond, "fcsd.name LIKE ?");
			$join = " INNER JOIN fc_special_day as fcsd ON fcsd.id=fcds.day_id ";
		}
		if (!empty($status)) $cond .= $this->getAndCondition($cond, "fcds.status=?");
		$sql .= $join;
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){ $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "10"]; }
		if(!empty($type)){ $condParams[] = ["paramType" => "s", "paramValue" => $type, "paramLength" => "1"]; }
		if(!empty($day_id)){ $condParams[] = ["paramType" => "s", "paramValue" => "%".$day_id."%", "paramLength" => "35", "paramRawValue" => $day_id]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams);
		// var_dump($sql);
		// var_dump($condParams);
		// var_dump($validate);
		// die();

		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getDaySettings($id='', $type='', $day_id='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$join = '';
		$sql = "SELECT * FROM fc_day_settings as fcds ";
		if (!empty($id)){ 
			$cond .= "fcds.id=?";
		}
		if (!empty($type)){ 
			$cond .= "fcds.type=?";
		}		
		if (!empty($day_id)){ 
			$cond .= $this->getAndCondition($cond, "fcsd.name LIKE ?");
			$join = " INNER JOIN fc_special_day as fcsd ON fcsd.id=fcds.day_id ";
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "fcds.status=?");
		}
		$sql .= $join;
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY fcds.id ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramLength" => "10"]; }
		if(!empty($type)){  $condParams[] = ["paramType" => "s", "paramValue" => $type, "paramLength" => "1"]; }
		if(!empty($day_id)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$day_id."%", "paramLength" => "35", "paramRawValue" => $day_id]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	public function updateDaySettingsStatus($id, $status='')
	{		
		$login_user = UserAuth::getCurrentUser();
		$sql = "UPDATE fc_day_settings SET status='$status' WHERE id='$id'";	
		
		if ($this->getDB()->query($sql)) {
			$ltxt = $status==MSG_YES ? 'Inactive to Active' : 'Active to Inactive';
			$this->addToAuditLog('FC Day Settings', 'F', "User=$login_user", "Status=".$ltxt);
			return true;
		}
		return false;
	}

	public function saveData($post_data){		
		$validate = $this->validate($post_data);
		$validate['data']->mdate = generic_date_format($validate['data']->mdate);

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
			$sql = "INSERT INTO fc_day_settings(".implode(",", $fields).") VALUES(".implode(",", $values).")";
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
			$this->addToAuditLog('Forecast Day Settings', 'F', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Day has been submitted successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Day has not been submitted successfully.'
			];
		}
	}

	private function validate($post_data)
	{
		$day_setting = new stdClass();
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
				$day_setting->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $day_setting,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		if(empty($day_setting->id)){
			$day_setting->created_by = $login_user;
			$day_setting->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $day_setting,
		];
	}

	public function updateData($post_data, $old_data){
		$validate = $this->validate($post_data);
		$validate['data']->mdate = generic_date_format($validate['data']->mdate);
		
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
			$sql = "UPDATE fc_day_settings SET ".implode(',', $field_values)." WHERE id= ? ";
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
			$this->addToAuditLog('Day Setting Update', 'F', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Day has been updated successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Day has not been updated successfully.'
			];
		}
	}

	public function getDayDataList($id='', $type='', $day_id='', $status=''){
		$data = $this->getDaySettings($id, $type, $day_id, $status);
		$tmp_data = [];
		
		foreach ($data as $key => $item) {
			$count = !isset($tmp_data[$item->type][$item->day_id]) ? 0 : count($tmp_data[$item->type][$item->day_id]);
			$tmp_data[$item->type][$item->day_id][$count]['mdate'] = $item->mdate;
			$tmp_data[$item->type][$item->day_id][$count]['l_window'] = $item->l_window;
			$tmp_data[$item->type][$item->day_id][$count]['u_window'] = $item->u_window;
			$tmp_data[$item->type][$item->day_id][$count]['priority'] = $item->priority;
		}

		return $tmp_data;
	}
}

?>