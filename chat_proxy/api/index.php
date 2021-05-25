<?php

$ip = $_SERVER['REMOTE_ADDR'];

include('../conf.php');
include_once('../request_cc_service.php');

if (!isset($token_generate_ips) || !is_array($token_generate_ips)) {
	send_response(500, "Internal Server Error");
}

if ($token_validation && !in_array($ip, $token_generate_ips)) {
	send_response(401, "Unauthorized");
}

$pageId = isset($_GET["pageId"]) ? strtoupper($_GET["pageId"]) : "";
$user = "AA";

if (empty($pageId) || !preg_match('/^[a-z_\-\.0-9]{5,18}$/i', $pageId)) {
	send_response(400, "Bad Request");
}
$token = do_cc_request("user=$user&method=getToken&src=$pageId");
if (substr($token, 0, 3) == "200") {
	send_response(200, substr($token, 4));
} else {
	send_response(502, "Bad Gateway");
}

function send_response($code, $msg='')
{
	//$header_msg = $code == 200 ? ' OK' : empty($msg) ? '' : ' ' . $msg;
	//header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . $header_msg);
	echo $code;
	if (!empty($msg)) echo ' ' . $msg;
	exit;
}
