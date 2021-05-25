<?php

class FormValidator
{
	private $max_len;
	private $min_len;

	public function __construct(){

	}

	private function field_required($data){
		if(isset($data) && strlen($data) > 0){
			return true;
		}else{
			return false;
		}
	}

	private function field_max_length($data, $len){
		if(strlen($data) <= $len){
			return true;
		}else{
			return false;
		}
	}

	private function field_min_length($data, $len){
		if(strlen($data) <= $len){
			return true;
		}else{
			return false;
		}
	}

	private function field_email($data){
		if(filter_var($data, FILTER_VALIDATE_EMAIL)){
			return true;
		}else{
			return false;
		}
	}

	private function field_min($data, $value){
		if($data >= $value){
			return true;
		}else{
			return false;
		}
	}

	private function field_max($data, $value){
		if($data <= $value){
			return true;
		}else{
			return false;
		}
	}

	public function validation($data, $valid_options, $valid_msg){		
		if(!empty($valid_options)){
			$error_msg = [];
			foreach ($valid_options as $key => $value) {
				if($key == 'required'){
					if(!$this->field_required($data)){
						$error_msg[] = $valid_msg[$key];
					}
				}elseif($key == 'max-len'){
					if(!$this->field_max_length($data, $value)){
						$error_msg[] = $valid_msg[$key];
					}
				}elseif($key == 'min-len'){
					if(!$this->field_min_length($data, $value)){
						$error_msg[] = $valid_msg[$key];
					}
				}elseif($key == 'email'){
					if(!empty($data) && !$this->field_email($data)){
						$error_msg[] = $valid_msg[$key];
					}
				}elseif($key == 'min'){
					if(!$this->field_min($data, $value)){
						$error_msg[] = $valid_msg[$key];
					}
				}elseif($key == 'max'){
					if(!$this->field_max($data, $value)){
						$error_msg[] = $valid_msg[$key];
					}
				}
			}

			if(!empty($error_msg)){
				return[
					'result' => false,
					'msg' => $error_msg,
				];
			}else{
				return[
					'result' => true,
					'msg' => 'Request data is valid.',
				];
			}
		}else{
			return[
				'result' => true,
				'msg' => 'Validation option is empty.',
			];
		}
	}

	public function valid($post_data, $valid_field){
		$data = new stdClass();
		$errors = [];

		if (is_array($post_data)) {
			$count = 0;
			unset($post_data['submit']);
			foreach ($post_data as $key=>$val) {
				$update_val = is_array($val) ? $val : trim($val);
				$type = (isset($valid_field[$key]['type'])) ? $valid_field[$key]['type'] : '';

				switch ($type) {
					case 'json':
						$update_val = json_encode($update_val, JSON_NUMERIC_CHECK);
						break;
					case 'array':
						$update_val = implode(',', $update_val);
						break;
					default:						
						break;
				}

				if(isset($valid_field[$key]) && isset($valid_field[$key]['validation'])){
					$valid = $this->validation($update_val, $valid_field[$key]['validation'], $valid_field[$key]['validation_msg']);
					if(!$valid['result']){
						$errors[$key] = $valid['msg'];
					}
				}
				$data->$key = $update_val;
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $data,
				MSG_ERROR_DATA => $errors
			];
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $data,
			MSG_ERROR_DATA => $errors
		];
	}
}