<?php
error_reporting(E_ALL);

$uri = 'http://';
if( isset($_SERVER['HTTPS'] ) ) {
	$uri = 'https://';
}

$uri = $uri . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];

//echo $uri;
//exit;
//$options = array('uri' => 'http://58.65.224.8/soap/server.php');
$options = array('uri' => $uri);

define('PATH_TO_ZF_LIBRARY',  'lib/');

set_include_path(
	implode(PATH_SEPARATOR, array(
		get_include_path(),
		PATH_TO_ZF_LIBRARY
	)));
require_once 'gPlex_CC_Service.php';
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();


if (isset($_GET['wsdl'])) {
	$wsdl = new Zend_Soap_AutoDiscover();
	$wsdl->setClass('gPlex_CC_Service');
	$wsdl->setUri($options['uri']);
	$wsdl->handle();
} else {
	ini_set("soap.wsdl_cache_enabled", "0");
	$server = new Zend_Soap_Server(null, $options);
	$server->setClass('gPlex_CC_Service');
	$server->setObject(new gPlex_CC_Service());
	$server->handle();
}
