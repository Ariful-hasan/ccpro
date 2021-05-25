<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if( !session_id() ) @session_start();
define('APPPATH', dirname(__FILE__).'/../');

include_once(APPPATH . 'config/constant.php');
include_once(APPPATH . 'config/common.php');
include_once('conf.php');
include_once('request_cc_service.php');

// $db = new stdClass();
// include_once(APPPATH . 'conf.php');

$dbprefix = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
$site_key = isset($_REQUEST['site_key']) ? trim($_REQUEST['site_key']) : '';
$page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
if (strlen($dbprefix) != 2) 
	$dbprefix = 'AA';

// include_once(APPPATH . 'lib/DBManager.php');
// include_once(APPPATH . 'lib/UserAuth.php');
// include_once(APPPATH . 'lib/FormValidator.php');
// UserAuth::setDBSuffix($dbprefix);

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

// $conn = new DBManager($db);
$response = new stdClass();
$post_data = $_REQUEST; // post data
/**
 * no need to use user, site_key & page_id of post_data
 * that's why re,ove this data
 */
unset($post_data['user']); // unset user from post data
unset($post_data['site_key']); // unset site_key from post data
unset($post_data['page_id']); // unset page_id from post data

if(empty($post_data['code'])){
	die(json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'Please input OTP number.'
	]));
}
$isValidReq = false;
if(!empty($post_data['code']) && preg_match('/^[0-9]{6}$/i', $post_data['code'])){
	$isValidReq = true;
}
if (!$isValidReq) {
	die(json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'Please input valid OTP number'
	]));
}

// var_dump($_SESSION);
// die();
// if(trim($post_data['auth_code']) != $_SESSION['chat_auth_code']){
// 	die(json_encode([
// 		MSG_RESULT => false,
// 		MSG_TYPE => MSG_ERROR,
// 		MSG_MSG => 'Befor OTP generate, you have modified your data.'
// 	]));
// }

$request_str = '';
$count = 0;
foreach ($post_data as $key => $item) {
	$and_symbol = ($count > 0) ? '&' : '';
	$request_str .= $and_symbol.$key.'='.urlencode($item);
	$count++;
}
$response = do_cc_request('method=checkChatCode&'.$request_str);
// var_dump($request_str);
// var_dump($response);

if(!empty($response)){
	if (substr($response, 0, 3) == "200") {
        $response = substr($response, 4);
        die($response);
	}
}else{
	die(json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'There are a problem of service request of Send Chat Code'
	]));
}