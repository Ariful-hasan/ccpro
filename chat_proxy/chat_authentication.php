<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if( !session_id() ) @session_start();

define('APPPATH', dirname(__FILE__).'/../');

include_once(APPPATH . 'config/constant.php');
include_once(APPPATH . 'config/common.php');
include_once(APPPATH . 'conf.email.php');

$db = new stdClass();
include_once(APPPATH . 'conf.php');

$dbprefix = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
$site_key = isset($_REQUEST['site_key']) ? trim($_REQUEST['site_key']) : '';
$page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
if (strlen($dbprefix) != 2) 
	$dbprefix = 'AA';

include_once(APPPATH . 'lib/DBManager.php');
include_once(APPPATH . 'lib/UserAuth.php');
include_once(APPPATH . 'lib/FormValidator.php');

UserAuth::setDBSuffix($dbprefix);

$isValidReq = false;
if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z_\-0-9]{5,32}$/i', $site_key)) {
	$isValidReq = true;
}
if (!$isValidReq) {
	die(json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'Your request is wrong.'
	]));
}

$conn = new DBManager($db);
$response = new stdClass();

$post_data = $_POST; // post data
/**
 * no need to use user, site_key & page_id of post_data
 * that's why re,ove this data
 */
unset($post_data['user']); // unset user from post data
unset($post_data['site_key']); // unset site_key from post data
unset($post_data['page_id']); // unset page_id from post data

$condParams = []; //mysql injection param
$insert_fields = []; //prepared insert field
$insert_values = []; //prepared insert value
$errors = [];
$count = 0;
$err_count = 0;
$auth_str_arr = [];

// form validation and sql insert data prepare
$db_field = [
	'cCustomerName' => [
		'title' => 'Name',
		'len' => 20,
		'validation' => [
			'required' => true, 
			'max-len' => 20,
		],
		'validation_msg' => [
			'required' => "Name is required!",
			'max-len' => "Please enter no more than 20 characters!",
		]
	],
	// 'cCustomerEmail' => [
	// 	'title' => 'Email',
	// 	'len' => 35,
	// 	'validation' => [
	// 		'required' => true, 
	// 		'email' => true,
	// 		'max-len' => 35, 
	// 	],
	// 	'validation_msg' => [
	// 		'required' => "Email is required!",
	// 		'email' => "Email is not valid!",
	// 		'max-len' => "Please enter no more than 35 characters!",
	// 	]
	// ],
	'cCustomerContactNumber' => [
		'title' => 'Contact Number',
		'len' => 11,
		'validation' => [
			'required' => true,
			'max-len' => 11
		],
		'validation_msg' => [
			'required' => "Contact Number is required!", 
			'max-len' => "Please enter no more than 11 characters!",
		]
	],
	'cCustomerSubject' => [
		'title' => 'Service',
		'len' => 2,
		'validation' => [
			'required' => true, 
			'max-len' => 2
		],
		'validation_msg' => [
			'required' => "Service is required!", 
			'max-len' => "Please enter no more than 2 characters!",
		]
	]
];


$validator = new FormValidator();
foreach ($post_data as $key => $value) {
	$valid = $validator->validation(trim($post_data[$key]), $db_field[$key]['validation'], $db_field[$key]['validation_msg']);
	if($valid['result']){
		$auth_str_arr[] = trim($post_data[$key]);
	}else{		
		$errors[$err_count]['field'] = $key;
		$errors[$err_count]['msg'] = $valid['msg'];
		$err_count++;
	}
}
if(!empty($errors)){
	die(json_encode([
		MSG_RESULT => false,
		MSG_DATA => $errors,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'You have some form errors. Please check below!'
	]));
}

$auth_str = implode('###', $auth_str_arr);
$auth_code = generate_base64_encode($auth_str, 3);
$_SESSION['chat_auth_code'] = $auth_code;

die(json_encode([
		MSG_RESULT => true,
		MSG_TYPE => MSG_SUCCESS,
		MSG_MSG => 'Data validation is OK',
		MSG_DATA => [
			'auth_code' => $auth_code
		]
	]));
