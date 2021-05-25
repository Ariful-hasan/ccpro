<?php
define('OUTBOUND_SMS_CLI', "22274");
$smsConfObj = new stdClass();
$smsConfObj->sms_send_enable = false;
$smsConfObj->wsdl_url = "https://services.thecitybank.com:9773/cApps/services/CBLWebServices?wsdl";
$smsConfObj->data_param = array('sms_number' => 'mobileNumber', 'sms_text' => 'smsText');
?>