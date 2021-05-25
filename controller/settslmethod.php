<?php

class Settslmethod extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$dbSuffix = UserAuth::getDBSuffix();
		
		include(UserAuth::getConfExtraFile());
		
		
		$data['sl_method'] = $extra->sl_method;//$setting_model->getSetting('sl_method');
		
		$errMsg = '';
		$errType = 1;
		$request = $this->getRequest();
		if ($request->isPost()) {
			$sl_method = isset($_REQUEST['sl_method']) ? trim($_REQUEST['sl_method']) : '';
			if ($sl_method != $data['sl_method']->value) {
				$is_success = false;
				if ($setting_model->setSetting('sl_method', $sl_method)) {
					$data['sl_method']->value = $sl_method;
					$errMsg = 'Settings saved successfully !!';
					$is_success = true;
				} else {
					$errMsg = 'Failed to save settings !!';
				}
					
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
				}
			} else {
				$errMsg = 'No change found !!';
			}
		}
		//var_dump($data['sl_method']);
		//exit;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $request;
		$data['pageTitle'] = 'Service Level Calculation';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_sl', $data);
	}

	function actionUpdate()
	{
		$pageid = isset($_REQUEST['pageid']) ? trim($_REQUEST['pageid']) : '';
		$this->savePassword($pageid);
	}

	function savePassword($pageid='')
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$request = $this->getRequest();
		$errMsg = '';
		$oldpage = $setting_model->getPageAccessByPage($pageid);
		if (empty($oldpage)) exit;

		if ($request->isPost()) {

			$pass1 = isset($_REQUEST['pass1']) ? trim($_REQUEST['pass1']) : '';
			$pass2 = isset($_REQUEST['pass2']) ? trim($_REQUEST['pass2']) : '';
			
			$errMsg = $this->getValidationMsg($pass1, $pass2);

			if (empty($errMsg)) {

				$is_success = false;

				if ($setting_model->resetPageAccessPassword($pageid, $pass1)) {
					$errMsg = 'Password successfully updated !!';
					$is_success = true;
				} else {
					$errMsg = 'Failed to update password !!';
				}
					
				if ($is_success) {
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}

			}

		}

		$data['pageid'] = $pageid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['pageTitle'] = 'Reset Password : ' . $pageid;
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_special_access_form', $data);
	}

	function getValidationMsg($pass1, $pass2)
	{
		$err = '';
		$len = strlen($pass1);
		if ($len < 6) $err = 'Minimum length of new password should be 6';
		if (empty($pass1)) $err = 'New password required';
		if ($len > 12) $err = 'Maximum length of new password should be 126';

		if (empty($err) && $pass1 !== $pass2)	{
			$err = 'Password mismatched';
		}
		return $err;
	}

}
