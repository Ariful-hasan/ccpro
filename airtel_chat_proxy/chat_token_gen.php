<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

define('APPPATH', dirname(__FILE__).'/');

include_once('conf.php');
include_once('request_cc_service.php');
include_once('log_file.php');
$page_id = 'ATWEBCHAT1';
$user = "AA";

// check valid ip series 
$ip_series = ['192.168.10','10.101.92', '192.168.37', '103.198.137', '157.230.33', '104.248.230','202.134.12', '118.67.220','58.65.224'];
$remote_address = $_SERVER['REMOTE_ADDR'];
//var_dump($remote_address);die();

$logData = [
	'remote_address: '.$remote_address,
	'PostData: '.json_encode($_REQUEST),
];
log_text($logData);

$ip_auth_flag = false;
foreach($ip_series as $ip){
	if(substr($remote_address, 0, strlen($ip)) == $ip){
		$ip_auth_flag = true;
		break;
	}
}

$response = new stdClass();
$post_data = $_REQUEST;

//add log into tex file
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
	$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}
$clientIp = @$_SERVER['HTTP_CLIENT_IP'];
$forwardIp = @$_SERVER['HTTP_X_FORWARDED_FOR'];
$remoteIp = $_SERVER['REMOTE_ADDR'];
if(filter_var($clientIp, FILTER_VALIDATE_IP)){
	$ip = $clientIp;
}elseif(filter_var($forwardIp, FILTER_VALIDATE_IP)){
	$ip = $forwardIp;
}else{
	$ip = $remoteIp;
}
$logData = [
	'ClientIp:'.$clientIp,
	'RemoteIp:'.$remoteIp,
	'ForwardIp:'.$forwardIp,
	'RealVisitorIp:'.$ip,
	'IpAuthFlag:'.$ip_auth_flag,
	'PostData:'.json_encode($post_data),
];
log_text($logData);


$error_msg = "";
//valid ip series
if(!$ip_auth_flag){
	die(json_encode([
		'result' => 401,
		'token' => '',
		'error' => "Unknown Host"
	]));
}
//valid customer name
if(!isset($post_data['cname']) || empty($post_data['cname'])){
	$error_msg = "Name is required!";
}elseif(strlen($post_data['cname']) > 25){
	$error_msg = "Name is not more than 25 characters!";
}
if(!empty($error_msg)){
	die(json_encode([
		'result' => 401,
		'token' => '',
		'error' => $error_msg
	]));
}

//valid custmer number
// var_dump(ctype_digit($post_data['cnumber']));
if(!isset($post_data['cnumber']) || empty($post_data['cnumber'])){
	$error_msg = "Contact Number is required!";
}elseif(strlen($post_data['cnumber']) < 10){
	$error_msg = "Contact Number is not less than 10 characters!";
}elseif(!ctype_digit($post_data['cnumber'])) {
	$error_msg = "Contact Number is not valid number!";
}
if(!empty($error_msg)){
	die(json_encode([
		'result' => 401,
		'token' => '',
		'error' => $error_msg
	]));
}

//valid customer verification
if(!isset($post_data['cverify']) || empty($post_data['cverify'])){
	$error_msg = "Customer Verification is required!";
}elseif(!in_array($post_data['cverify'], ['Y','N'])){
	$error_msg = "Customer Verification is not valid data!";
}
if(!empty($error_msg)){
	die(json_encode([
		'result' => 401,
		'token' => '',
		'error' => $error_msg
	]));
}

$regex_str = ['`', '\'','\\','"','~','!','#','$','^','&','%','*','(',')','{','}','<','>',',','?',';',':','|','+','=', '/'];
$post_data['cname'] = str_replace($regex_str, '', $post_data['cname']);

$post_data['page_id'] = $page_id;
$post_data['user'] = $user;
$post_data['ip'] = $remote_address;
$request_str = '';
$count = 0;
foreach ($post_data as $key => $item) {
	$and_symbol = ($count > 0) ? '&' : '';
	$request_str .= $and_symbol.$key.'='.urlencode($item);
	$count++;
}
//echo "sfsfsfsfsf";
$response = do_cc_request('method=createChatTokenSingleSign&'.$request_str);
//var_dump($request_str);
//var_dump($response);
//die();

if(!empty($response)){
	$response = trim($response);
	//var_dump(substr($response, 0, 3) == "200");  
	if (substr($response, 0, 3) == "200") {
        $response = substr($response, 4);
        
		die(json_encode([
			'result' => 200,
			'token' => $response,
			'error' => ''
		]));
	}
}else{
	die(json_encode([
		'result' => 500,
		'token' => '',
		'error' => 'Token has not been generated!'
	]));
}