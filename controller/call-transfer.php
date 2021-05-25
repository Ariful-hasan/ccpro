<?php
class CallTransfer extends Controller
{
	function __construct()
    {
		parent::__construct();
	}
	
	function init()
	{
		$this->actionGetSettings();
	}

	function actionGetSettings()
	{
	    $this->setPageTitle("Call Transfer Permissions");
	    $this->setGridDataUrl($this->url('task=get-call-transfer-data&act=call-transfer-setting'));
	    $this->setSideMenuIndex('settings');
	    $this->setSelectedSideMenuIndex('call_trans_config');
        $this->display('call_transfer_settings');
	 /* $data['pageTitle'] = 'Call Transfer Settings';
        $data['dataUrl'] = $this->url('task=get-call-transfer-data&act=call-transfer-setting');
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'call_trans_config';
	    $this->getTemplate()->display('call_transfer_settings', $data);  */
    }
	
}