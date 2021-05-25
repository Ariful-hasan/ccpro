<?php

//error_reporting(7);
//if (!session_id()) session_start();

$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
header("Location: ./index.php?task=email&act=details&tid=$tid");
exit;

/*
include_once('./lib/UserAuth.php');
include_once('./lib/TemplateManager.php');
include_once('./lib/DBManager.php');
include_once('./model/Model.php');

$userAuth = new UserAuth();

$user = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
$pass = isset($_REQUEST['pass']) ? trim($_REQUEST['pass']) : '';
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';


$user_len = strlen($user);
if ($user_len <= 4) {

	include('model/MAgent.php');
	$agent_model = new MAgent();

	$valid_user = $agent_model->getValidAgent($user, $pass);

	if (!empty($valid_user)) {
		UserAuth::login($user, $valid_user->usertype);
		//$template->redirect("./index.php?task=email&act=details&tid=$tid");
		header("Location: ./index.php?task=email&act=details&tid=$tid");
		exit;
	}
}
	//var_dump($user_len);
exit;
*/
?>