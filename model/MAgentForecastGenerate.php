<?php

class MAgentForecastGenerate extends Model
{
	private $valid_field = [		
		'sdate' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Date is required!",
			],
			'special_char_check' => true
		],
		'edate' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Date is required!",
			],
			'special_char_check' => true
		],
		'model' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Model is required!"
			],
			'special_char_check' => true
		],
		'type' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Type is required!"
			],
			'special_char_check' => true
		],
		'service_type' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Service Type is required!",
			],
			'special_char_check' => true
		],
		'skill_id' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Skill is required!"
			],
			'special_char_check' => false,
			'type'=> 'json'
		],
		'avg_call_duration' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Avg. Call Duration is required!"
			],
			'special_char_check' => false
		],
		'total_interval_length' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Total Interval Length is required!"
			],
			'special_char_check' => false
		],
		'agent_occupancy' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Agent Occupancy is required!"
			],
			'special_char_check' => false
		],
		'wait_time' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Wait Time is required!"
			],
			'special_char_check' => false
		],
		'service_level' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Service Level is required!"
			],
			'special_char_check' => false
		],
		'shrinkage' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Shrinkage is required!"
			],
			'special_char_check' => false
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function validate($post_data)
	{
		$fc_generate_data = new stdClass();
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
				$fc_generate_data->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $fc_generate_data,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		if(empty($fc_generate_data->id)){
			$fc_generate_data->generate_by = $login_user;
			$fc_generate_data->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $fc_generate_data,
		];
	}

	public function saveData($post_data){
		$login_user = UserAuth::getCurrentUser();
		$sql = '';
		$log_txt = '';
		$is_insert = false;

		if(!empty($post_data)){
			$count = 0;			
			foreach ((array)$post_data as $key => $value) {
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
			$sql = "INSERT INTO fc_agent_generate_log(".implode(",", $fields).") VALUES(".implode(",", $values).")";
			// echo $sql;
			// pr($fields);
			// pr($values);
			// pr($condParams);
			// die();

			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			if($validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
				$last_insert_id = $this->getDB()->getLastInsertId();
				$post_data->id = $last_insert_id;
			}
		}

		if ($is_insert) {
			$this->addToAuditLog('Forecast Generate Log', 'F', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $post_data,
				MSG_MSG => 'Log has been submitted successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $post_data,
				MSG_MSG => 'Log has not been submitted successfully.'
			];
		}
	}
}

?>