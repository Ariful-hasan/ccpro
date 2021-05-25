<?php
define('DEBUG', true);

define('BASEPATH', dirname(__FILE__)."/");
if (DEBUG) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ERROR);
} else {
	error_reporting(0);
}

if (!session_id()) session_start();

//var_dump($_SESSION);
//session_destroy();
//die();
/*
$ip = $_SERVER['REMOTE_ADDR'];

if($ip!='69.88.13.17' && $ip!='192.168.10.62' && $ip!='58.65.224.5') {
	header("HTTP/1.0 401 Unauthorized");
	exit;
}
*/

include_once(BASEPATH . 'conf.php');
include_once(BASEPATH . 'conf.sms.php');
include_once(BASEPATH . 'config/constant.php');
include_once(BASEPATH . 'config/common.php');
include_once(BASEPATH . 'config/Helper.php');
include_once(BASEPATH . 'lib/UserAuth.php');
include_once(BASEPATH . 'lib/TemplateManager.php');
include_once(BASEPATH . 'lib/DBManager.php');
include_once(BASEPATH . 'helper/helper_cc.php');
include(BASEPATH . "controller/controller.php");
include(BASEPATH . "model/Model.php");

$userAuth = new UserAuth();

$task = isset($_GET['task']) ? trim($_GET['task']) : '';
$action = isset($_GET['act']) ? trim($_GET['act']) : '';
if(!$userAuth->isLoggedIn()) {

    // include(BASEPATH . 'conf.php');
        
    if ($db->cctype > 0) {         
    	if(!empty($db->db_suffix) && strlen($db->db_suffix)==2)       
        	UserAuth::setDBSuffix($db->db_suffix);
        else
        	UserAuth::setDBSuffix('AA');
        UserAuth::setAccountName('ccpro');                
    } elseif (isset($_REQUEST['_acc'])) {
		UserAuth::setDBSuffix('');
		UserAuth::setAccountName('');
		
		include(BASEPATH . 'model/MAccount.php');
		$acc_model = new MAccount();
		$_acc = trim($_REQUEST['_acc']);
		$_acc = explode("/", $_acc);
		//var_dump($_acc);exit;
		$_account = $acc_model->getAccountFromUrl($_acc[0]);
		if (!empty($_account)) {
			UserAuth::setDBSuffix($_account->db_suffix);
			UserAuth::setAccountName($_account->device_display_name);
		}
		header("Location: ".$_SERVER['SCRIPT_NAME']);
		die();
	}
	
	$dbsuffix = UserAuth::getDBSuffix();
	
	if (empty($dbsuffix)) {
		include_once('conf.extras.php');
		$template = new TemplateManager('login', 'index', $extra);
		Model::setTemplate($template);
		$template->display_404_error(array('isANF'=>true,'pageTitle'=>'Account not found !!', 'isError'=>true, 'msg'=>'Account not found !!'));
	}
	
	if ($userAuth->isPageLoggedIn()) {
		if ($task != 'logout') {
			$user = $userAuth->getCurrentUser();
			if ($user == 'dashboard') {
				$task = 'report';
				if ($action != 'dashboard') {
					$action = 'dbuserpanel';
				}
			} else if ($user == 'linkstatus') {
				$task = 'linkstatus';
			} else {
				$task = 'login';
			}
		}
	} else {
		if (!isAccessablePage($task, $action)) {
			$task = 'login';
		}
	}
} else {
	/*
	if ($userAuth->hasRole('dashboard')) {
		if ($task != 'logout' && $task != 'agent') {
			$task = 'dashboard';
		}
	}
	*/
	if ($task == 'login') {
		$default_page = getDefaultPage();
		$task = $default_page[0];
		$action = $default_page[1];
	}
	if ($task != 'logout' && UserAuth::isPasswordChangeRequired()) {
		$task = 'password';
	}
}

if (empty($task)) $task = 'agents';

$action = empty($action) ? 'init' : $action;
// echo $task;die();
if (!isAccessablePage($task, $action)) {
	if (!$userAuth->isPageLoggedIn() && !UserAuth::isPasswordChangeRequired()) {
	$default_page = getDefaultPage();
	$task = $default_page[0];
	$action = $default_page[1];
	}
}

include_once('conf.extras.php');

$template = new TemplateManager($task, $action, $extra);
Model::setTemplate($template);
if (file_exists("controller/$task".".php") &&  include("controller/$task".".php")) {

	$className = processRoutePath($task);
	$ins = new $className();
	$_action_name = $action == 'init' ? 'init' : 'action' . processRoutePath($action);
	////echo $action;
	//echo $task;
	if (!method_exists($ins, $_action_name)) {
		$_action_name = 'init';
		$action = 'init';
	}
	$template->_request_action = $action;
	$ins->getRequest()->setActionName($action);
	$ins->getRequest()->setControllerName($task);
	$ins->setTemplate($template);
	$ins->$_action_name();
	//echo 'deb1';	
} else {
	$template->display('msg', array('pageTitle'=>'Page not found !!', 'isError'=>true, 'msg'=>'Page not found !!'));
}

function getDefaultPage()
{
	$_role = UserAuth::getRole();
	$defaults = array(
		'agent' => array('agent', 'init'),
		'dashboard' => array('agent', 'init'),
		'supervisor' => array('agents', 'init'),
		'admin' => array('agents', 'init'),
		'report' => array('agents', 'init'),
		'report_digicon' => array('agents', 'init')
	);
	return isset($defaults[$_role]) ? $defaults[$_role] : array('login', 'init');
}

function isAccessablePage($controller, $action)
{
	$_role = UserAuth::getRole();
	if ($_role == 'admin') return true;

	$allowed_pages = array(
		'supervisor' => array(
			'agent_*',
			'agents_*',
			'skills_init',
			'skills_agent',
			// 'crm_*',
			'crm_in_*',
			'conference_*',
			// 'campaign_*',
			'lead_*',
			'disposition_*',
			'email_*',
			'emailtemplate_*',		    
			'emailartemplate_*',
			'emailallowed_*',
			'emailreport_*',
		    'chattemplate_*',
		    'chatreport_*',
			'emaildc_*',
			'emailsignature_*',
			// 'cdr_*',
			'calldetails_*',
			'autodialer_*',
            'email-confirm-response_*',
			// 'report_*',
			// 'ivrreport_*',
			// 'crminreport_*',
			'gdata_*',
			'password_*',
			'login_*',
			'logout_*',
			'get-home-data_skills',
			'get-home-data_agents',
			'get-report-data_*',
		    'get-ivr-report-data_*',
		    'get-chat-data_*',
			'http-response_*',
			'vm_*',
			'settseats_*',
			'get-tools-data_seats',
			'confirm-response_seats',
			'confirm-response_vm',
		    'confirm-response_chatTempDel',
			'get-agent-data_*',
            'get-email-data_*',
            'smstemplate_*',
			'smsreport_*',
			'settings_server',
			'confirm-response_agents',
			// 'report-new_*',
			// 'get-report-new-data_*',
			'report-partition_*',
			/*------------ Music on hold access for supervisor ---------*/
			'moh-filler_config',
			'skills_upload-hold-voice',
		    /*------------ Busy Message and Shift profile permission------*/
			'get-home-data_shiftprofile',
			'settbmsg_*',
			'get-tools-data_Bmsg',
            /*------------  Permission to  add disposition------*/
			'stemplate_init',
            'get-tools-data_STemplate',
			'scrmdc_*',
			'get-tools-data_SDisposition',
			'confirm-response_DelCode',
			'aht_*',
			'priority_agent-priorities',
			'priority_agent-priorities',
			'get-priority-data_agent-skills',
            'ticket_*',
            'get-ticket-data_*',
            'customer-journey_*',
            /*------------ SMS chat service permission ---------*/
            'sms_get-user-sms-by-session',
            'sms_send-sms-to-user',
            'sms_close-sms-session',
            'sms_send-sms-ping',
			'report_tabular-dashboard',
			'report_tabular-dashboard-skill',
			'dashboard_summary',
			'report_dashboardnew',
			'report_wallboard',
			'report_current-day-skill-summary',
			'report-new_*',
			'get-report-new-data_*'
		),
		'dashboard' => array(
			'agent_*',
			'report_dashboard',
			'password_*',
			'login_*',
			'logout_*',
			'http-response_*'
		),
		'agent' => array(
			'agent_*',
			//'crm_*',
			'crm_init',
			'crm_churn',
			'crm_details',
			'crm_downloadcrmvoice',
			'crm_saveprofile',
			'crm_saveaddress',
			'crm_savecontact',
			'crm_savedisposition',
			'crm_delschedule',
			'crm_in_details',
			'crm_in_tpin',
			'crm_in_search',
			'crm_in_verified',
			'crm_in_savedisposition',
			'crm_in_saveVarInCallSession',
			'crm_in_savecustinfo',
		    'crm_in_dispositionchildren',
			'crm_in_ticketSubmit',
            'crm_in_tab-details',
			'email_*',
			'emailtemplate_init',
			'emailtemplate_message',
			'password_init',
			'login_*',
			'logout_*',
			'get-agent-data_*',
		    'get-email-data_*',
		    'get-ivr-report-data_*',
			'http-response_*',
			'cdr_chat-log',
			'vm_*',
			'confirm-response_vm',
		    'ivrreport_*',
		    'get-report-new-data_reportagentstatus',
		    'get-report-new-data_agent-call-history',
		    'chattemplate_leave-messages',
		    'chattemplate_leave-message-grid-list',
            'emailreport_email-activity-report',
            'email-confirm-response_*',
            'ticket_*',
            'get-ticket-data_*',
            'customer-journey_*',
            /*------------ SMS chat service permission ---------*/
            'sms_get-user-sms-by-session',
            'sms_send-sms-to-user',
            'sms_close-sms-session',
            'sms_send-sms-ping',
            'sms_log-sms-from-js',
            /*------------ Unattended CDR ---------*/
            'unattended-cdr_*',
            'get-unattended-cdr-data_*'
		),
		'public' => array(
			'http-response_*'
		),
		'report' => array(
			'agent_*',
			'login_*',
			'logout_*',
			'report-new_*',
			'get-report-new-data_*',
			'report_dashboard-except777',
			'report_dashboard777',
			'aht_*',
			'password_*',
			'email_init',
			'email_email-report-new',
			'get-email-data_emailinit',
			'dashboard_summary',
			'report_tabular-dashboard',
			'report_tabular-dashboard-skill',
			'report_current-day-skill-summary',
			'email_dashboard',
			'smslogreport_*',
			'smsreport_*',
			'get-sms-log-data_*',
		),
		'report_digicon' => array(
			'agent_*',
			'login_*',
			'logout_*',
			'report-new_*',
			'get-report-new-data_*',
			'password_*',
		)			
	);

	$role_pages = isset($allowed_pages[$_role]) ? $allowed_pages[$_role] : array();	
	$role_pages=array_merge($role_pages,$allowed_pages['public']);
	if (is_array($role_pages)) {
		$_page = $controller . '_' . '*';
		if (in_array($_page, $role_pages)) return true;
		$_page = $controller . '_' . $action;
		if (in_array($_page, $role_pages)) return true;
	}
	
	return false;
}

function processRoutePath($path)
{
	$b = '';
	$paths = explode('-', $path);
	foreach ($paths as $p) {
		$b .= ucfirst($p);
	}
	
	return $b;
}

?>