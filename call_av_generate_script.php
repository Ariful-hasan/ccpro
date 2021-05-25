<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//var_dump();
*/

if( (isset($_POST['callid']) && !empty($_POST['callid'])) && (isset($_POST['type']) && !empty($_POST['type'])) ){

        require('conf.extras.php');
        require('conf.php');

	$post_data = $_POST;
	$post_data['suffix'] = $db->db_suffix;

	$fields = '';
	foreach ($post_data as $key => $value) {
	    $fields .= $key . '=' . $value . '&';
	}
	rtrim($fields, '&');
	
	$http = 'http';
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
	    $http = 'https';
        }
	$url = $http . '://' . $extra->storage_srv_domain . '/generate_audio_video.php';
//var_dump($fields);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$server_output = curl_exec($ch);
	curl_close ($ch);
//var_dump($server_output);
	die($server_output);
}
$return['result'] = false;
$return['url'] = '';
die(json_encode($return));

?>