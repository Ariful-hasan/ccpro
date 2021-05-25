<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if( !session_id() ) @session_start();

define('APPPATH', dirname(__FILE__).'/../');

include_once(APPPATH . 'config/constant.php');
include_once(APPPATH . 'config/common.php');
$db = new stdClass();
include_once(APPPATH . 'conf.php');
include_once(APPPATH . 'lib/DBManager.php');
$conn = new DBManager($db);
$response = new stdClass();


$sql = "UPDATE opt_otc_number SET status='".STATUS_INACTIVE."' WHERE status='".STATUS_ACTIVE."' AND created_at < (NOW() - INTERVAL 3 MINUTE)";
$result = $conn->query($sql);
var_dump($result);
var_dump($sql);
die();