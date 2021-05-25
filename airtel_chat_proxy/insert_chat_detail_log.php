<?php
// error_reporting(1);
// ini_set('display_errors', 0);

define('APPPATH', dirname(__FILE__).'/../');
include_once(APPPATH . 'config/constant.php');
include_once(APPPATH . 'config/common.php');
include_once('conf.php');
include_once('request_cc_service.php');

$dbprefix = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
$site_key = isset($_REQUEST['site_key']) ? trim($_REQUEST['site_key']) : '';
$page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
if (strlen($dbprefix) != 2) 
	$dbprefix = 'AA';

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

//$conn = new DBManager($db);
$response = new stdClass();
$post_data = $_REQUEST; // post data
/**
 * no need to use user, site_key & page_id of post_data
 * that's why re,ove this data
 */
unset($post_data['user']); // unset user from post data
unset($post_data['site_key']); // unset site_key from post data
unset($post_data['page_id']); // unset page_id from post data

$request_str = '';
$count = 0;
foreach ($post_data as $key => $item) {
	if($key=='callid'){
		$item = explode('-', $item);
		$item = $item[0];
	}

	$and_symbol = ($count > 0) ? '&' : '';
	$request_str .= $and_symbol.$key.'='.urlencode($item);
	$count++;
}
$response = do_cc_request('method=insertChatDetailLog&'.$request_str);
// var_dump($request_str);
// var_dump($response);
// die();

if(!empty($response)){
	if (substr($response, 0, 3) == "200") {
        $response = substr($response, 4);
        die($response);
	}
}else{
	die(json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'There are a problem of service request of inser chat detail log.'
	]));
}