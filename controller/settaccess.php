<?php

class Settaccess extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$data['special_access'] = $setting_model->getPageAccess();
		$data['num_current_records'] = is_array($data['special_access']) ? count($data['special_access']) : 0;

		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Special Access';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_special_access', $data);
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
		$errType = 1;
		$oldpage = $setting_model->getPageAccessByPage($pageid);
		if (empty($oldpage)) exit;

		if ($request->isPost()) {

			$pass1 = isset($_REQUEST['pass1']) ? trim($_REQUEST['pass1']) : '';
			$pass2 = isset($_REQUEST['pass2']) ? trim($_REQUEST['pass2']) : '';
			
			$errMsg = $this->getValidationMsg($pass1, $pass2);

			if (empty($errMsg)) {

				$is_success = false;

				if ($setting_model->resetPageAccessPassword($pageid, $pass1)) {
					$errMsg = 'Password updated successfully !!';
					$is_success = true;
				} else {
					$errMsg = 'Failed to update password !!';
				}
					
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}

			}

		}

		$data['pageid'] = $pageid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Reset Password : ' . $pageid;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settaccess_';
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
