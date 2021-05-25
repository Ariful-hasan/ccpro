<?php

class MForecastDeviation extends Model
{
	private $valid_field = [
		'type' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Type is required!"
			],
			'special_char_check' => true
		],
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
		'skill_type' => [
			'validation' => [
				'required' => true
			],
			'validation_msg' => [
				'required' => "Skill Type is required!",
			],
			'special_char_check' => true
		],
		'skill_id' => [
			'validation' => [
				'required' => false
			],
			'validation_msg' => [
				'required' => "Skill is required!"
			],
			'special_char_check' => false,
			'type'=> 'json'
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function validate($post_data)
	{
		$fc_deviation_data = new stdClass();
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
				$fc_deviation_data->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $fc_deviation_data,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		if(empty($fc_deviation_data->id)){
			$fc_deviation_data->generate_by = $login_user;
			$fc_deviation_data->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $fc_deviation_data,
		];
	}
}

?>