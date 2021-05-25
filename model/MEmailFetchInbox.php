<?php

class MEmailFetchInbox extends Model
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
		'skill_id' => [
			'len' => 2,
			'validation' => [
				'required' => true, 
				'max-len' => 2
			],
			'validation_msg' => [
				'required' => "Skill is required!", 
				'max-len' => "Please enter no more than 2 characters!",
			],
			'special_char_check' => true
		],
		'skill_name' => [
			'len' => 25,
			'validation' => [
				'required' => true, 
				'max-len' => 25
			],
			'validation_msg' => [
				'required' => "Skill name is required!", 
				'max-len' => "Please enter no more than 25 characters!",
			],
			'special_char_check' => false
		],
		'fetch_method' => [
			'len' => 3,
			'validation' => [
				'required' => true, 
				'max-len' => 3
			],
			'validation_msg' => [
				'required' => "Fetch method is required!", 
				'max-len' => "Please enter no more than 3 characters!",
			],
			'special_char_check' => true
		],
		'host' => [
			'len' => 50,
			'validation' => [
				'required' => true, 
				'max-len' => 50
			],
			'validation_msg' => [
				'required' => "Host is required!", 
				'max-len' => "Please enter no more than 50 characters!",
			],
			'special_char_check' => false
		],
		'username' => [
			'len' => 35,
			'validation' => [
				'required' => true, 
				'max-len' => 35
			],
			'validation_msg' => [
				'required' => "Username is required!", 
				'max-len' => "Please enter no more than 35 characters!",
			],
			'special_char_check' => false
		],
		'password' => [
			'len' => 48,
			'validation' => [
				'required' => true, 
				'max-len' => 48
			],
			'validation_msg' => [
				'required' => "Password is required!", 
				'max-len' => "Please enter no more than 48 characters!",
			],
			'special_char_check' => false
		],
		'port' => [
			'len' => 5,
			'validation' => [
				'required' => true, 
				'max-len' => 5
			],
			'validation_msg' => [
				'required' => "Port is required!", 
				'max-len' => "Please enter no more than 5 characters!",
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
				'max-len' => "Please enter no more than 1 characters!",
			],
			'special_char_check' => true
		],
		'id' => [
			'len' => 10,
			'special_char_check' => true
		],
        'show_email_list' => [
            'len' => 1,
            'validation' => [
                'max-len' => 1
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 1 characters!",
            ],
            'special_char_check' => true
        ],
        'title' => [
            'len' => 30,
            'validation' => [
                'max-len' => 30
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 30 characters!",
            ],
            'special_char_check' => true
        ],
        'inbox_row_color' => [
            'len' => 20,
            'validation' => [
                'max-len' => 20
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 20 characters!",
            ],
            'special_char_check' => true
        ],'in_kpi_time' => [
            'len' => 10,
            'validation' => [
                'max-len' => 10
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 10 digits!",
            ],
            'special_char_check' => true
        ],
	];

	public function __construct() {
		parent::__construct();
	}

	public function numEmailFetchInboxes($id = '', $name='', $status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM email_fetch_inboxes ";
		if (!empty($id)) $cond .= "id= ?";
		if (!empty($name)) $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($status)) $cond = $this->getAndCondition($cond, "status=?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "d", "paramValue" => $id, "paramLength" => "11"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getEmailFetchInboxes($id='', $name='', $status='', $show_email_list='', $offset=0, $limit=0, $select_items='*')
	{
		$cond = '';
		$sql = "SELECT $select_items FROM email_fetch_inboxes ";
		if (!empty($id)){ 
			$cond .= "id= ?";
		}		
		if (!empty($name)){
			$cond = $this->getAndCondition($cond, "name LIKE ?");
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "status=?");
		}
		if (!empty($show_email_list)){
			$cond = $this->getAndCondition($cond, "show_email_list=?");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "d", "paramValue" => $id, "paramLength" => "11"]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $did]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		if(!empty($show_email_list)){  $condParams[] = ["paramType" => "s", "paramValue" => $show_email_list]; }

		$validate = $this->getDB()->validateQueryParams($condParams);
		//GPrint($validate);die;
		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	private function validate($post_data)
	{
		$ef_inbox = new stdClass();
		$validator = new FormValidator();

		$count = 0;
		unset($post_data['submit']);
		foreach ($this->valid_field as $key=>$val) {
			$ef_inbox->$key = trim($post_data[$key]);
			if(!empty($val) && isset($val['validation'])){
				$valid = $validator->validation(trim($post_data[$key]), $val['validation'], $val['validation_msg']);
				if(!$valid['result']){
					$errors[$key] = $valid['msg'];
				}
				unset($post_data[$key]);
			}
		}
		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $ef_inbox,
				MSG_ERROR_DATA => $errors
			];
		}

		// if validation is ok then update others data
		if (!empty($post_data)) {
			foreach ($post_data as $key=>$val) {
				$ef_inbox->$key = trim($val);
			}
		}
		$login_user = UserAuth::getCurrentUser();
		
		if(!empty($ef_inbox->id)){
			$ef_inbox->updated_by = $login_user;
			$ef_inbox->updated_at = date("Y-m-d H:i:s");
		}else{
			$ef_inbox->created_by = $login_user;
			$ef_inbox->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $ef_inbox,
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
			$sql = "INSERT INTO email_fetch_inboxes(".implode(",", $fields).") VALUES(".implode(",", $values).")";
			//echo $sql;
			// pr($fields);
			// pr($values);
			// pr($condParams);
			 //die();

			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			 //dd($validateQuery);
			if($validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
			}
		}

		if ($is_insert) {
			$this->addToAuditLog('Email fetch inbox add', 'MN', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'This item has been saved successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'This item has not been saved successfully.'
			];
		}
	}

	public function updateEmailFetchInboxStatus($id, $status='')
	{		
		$login_user = UserAuth::getCurrentUser();
		$sql = "UPDATE email_fetch_inboxes SET status='$status' WHERE id='$id'";		
		if ($this->getDB()->query($sql)) {
			$ltxt = $status==STATUS_ACTIVE ? 'Inactive to Active' : 'Active to Inactive';
			$this->addToAuditLog('Email fetch inbox status', 'PG', "User=$login_user", "Status=".$ltxt);
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
			$sql = "UPDATE email_fetch_inboxes SET ".implode(',', $field_values)." WHERE id= ? ";
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
			$this->addToAuditLog('Email fetch inbox Update', 'PG', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'This item has been updated successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'This item has not been updated successfully.'
			];
		}
	}
}

?>