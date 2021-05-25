<?php

if (UserAuth::isLoggedIn()) {

	if (isset($side_menu_index)) {
		$_side_menu_index = $side_menu_index;
	} else {
		$_side_menu_index = 'home';
	}

	$TopTitlePrefix = '';
	
	if ($_side_menu_index == 'settings') {
		
		$TopTitlePrefix = '<li><a href="' . $this->url('task=agents') . '"><i class="fa fa-home"></i> Home</a></li> <li><a href="' . $this->url('task=settings') . '">Tools</a></li>';
		
		if (UserAuth::hasRole('admin')) {
			$CurrentMenuPages = array(
				'tools' => array('Tools', 'th', '', '', ''),
				'agents_' => array('Home', 'home', 'agents', '', ''),
				'settings_music' => array('Music Directory', 'music', 'settings', 'music', ''),
				'settppolicy_' => array('Priority Policy', 'star', 'settppolicy', '', ''),
				'setttrunk_' => array('Trunk', 'tel', 'setttrunk', '', ''),
				'settbmsg_' => array('Busy Message', 'away', 'settbmsg', '', ''),
				'ivrdc_' => array('IVR Disposition Code', 'chart', 'ivrdc', '', ''),
				'ivrapi_' => array('IVR API Connection', 'cog', 'ivrapi', '', ''),
				'wallboard_' => array('Wallboard', 'app-list', 'wallboard', '', ''),
				'stemplate_' => array('Skill CRM Template', 'app-list', 'stemplate', '', ''),
				'settseats_' => array('Seats', 'conference', 'settseats', '', ''),
				'settaccess_' => array('Special Access', 'key', 'settaccess', '', ''),
				'rootaccess_' => array('Root Access', 'key', 'rootaccess', '', ''),
				'settbackup_' => array('Backup', 'compress', 'settbackup', '', ''),
				'settings_' => array('License', 'cog', 'settings', '', ''),
				'settslmethod_' => array('Service Level Calculation', 'calc', 'settslmethod', '', ''),
				'system_tempreport' => array('System Status', 'computer', 'system', 'tempreport', ''),
				'debuglog_' => array('Debug Log', 'log', 'debuglog', '', ''),
				'password_misslogin' => array('Login Attempts', 'log', 'password', 'misslogin', ''),
				'password_rules' => array('Password Validation Rules', 'log', 'password', 'rules', ''),
				'conference_' => array('Conference', 'conference', 'conference', '', ''),
			    'settings_setreportday' => array('Report Day Settings', 'cog', 'settings', 'setreportday', '')
			);
			$_menus = array('tools', 'settings_music', 'settppolicy_', 'setttrunk_', 'settbmsg_', 'ivrdc_', 'ivrapi_', 'wallboard_', 'stemplate_', 
				'settseats_', 'settaccess_', 'rootaccess_', 'settbackup_', 'settings_', 'settslmethod_', 'system_tempreport', 'debuglog_', 'password_misslogin', 'password_rules', 'conference_', 'settings_setreportday');
		} else {
			$_menus = array();
		}
	} else if ($_side_menu_index == 'campaign') {

		$TopTitlePrefix = '<li><a href="' . $this->url('task=agents') . '"><i class="fa fa-home"></i> Home</a></li><li><a href="' . $this->url('task=campaign') . '">Campaign</a></li>';
		
		$CurrentMenuPages = array(
			'campaign' => array('Campaign', 'th', '', '', ''),
			'agents_' => array('Home', 'home', 'agents', '', ''),
			'campaign_' => array('Campaign Management', 'dial', 'campaign', '', ''),
			'lead_' => array('Lead Management', 'user', 'lead', '', ''),
			'disposition_' => array('Disposition Code', 'user', 'disposition', '', '')
		);
		if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
			$_menus = array('campaign', 'campaign_', 'lead_', 'disposition_');
		} else {
			//UserAuth::hasRole('dashboard')
			$_menus = array();
		}
				
	} else if ($_side_menu_index == 'reports') {

		$TopTitlePrefix = '<li><a href="' . $this->url('task=agents') . '"><i class="fa fa-home"></i> Home</a></li><li><a href="' . $this->url('task=report&act=agentinboundlog&option=monthly') . '">Report</a></li>';
		$CurrentMenuPages = array(
			'chart' => array('Chart', 'module', '', '', ''),
			'cdr' => array('CDR', 'module', '', '', ''),
			'audit' => array('Audit', 'module', '', '', ''),
			'ivr' => array('IVR', 'module', '', '', ''),
			'campaign' => array('Campaign', 'module', '', '', ''),
			'agent' => array('Agent', 'module', '', '', ''),
			'statistics' => array('Statistics', 'module', '', '', ''),
			'performance' => array('Performance', 'module', '', '', ''),
			'source' => array('Source Report', 'module', '', '', ''),
			'agents_' => array('Home', 'home', 'agents', '', ''),
			'report_channelday' => array('Channel Report by Hour', 'chart-line', 'report', 'channelday', ''),
			'report_channelmonth' => array('Channel Report by Day', 'chart-line', 'report', 'channelmonth', ''),
			'cdr_skill' => array('Skill CDR', 'table', 'cdr', 'skill', ''),
			'cdr_inbound' => array('Line CDR', 'table', 'cdr', 'inbound', ''),
			'access_audit' => array('Activity Report', 'conference', 'access', 'audit', ''),
			'access_privilege' => array('Access Privilege', 'key', 'access', 'privilege', ''),
			'ivrreport_servicereq' => array('IVR Service Request', 'chart', 'ivrreport', 'servicereq', ''),
			'ivrreport_servicelog' => array('IVR Service Request Log', 'server-db', 'ivrreport', 'servicelog', ''),
			'ivrreport_service-summary' => array('IVR Service Summary', 'table', 'ivrreport', 'service-summary', ''),
			'crm_recordcount' => array('Call Disposition', 'dial', 'crm', 'recordcount', ''),
			'crminreport_' => array('Inbound Disposition  Log', 'server-db', 'crminreport', '', ''),
			'crminreport_recordcount' => array('Inbound Disposition', 'dial', 'crminreport', 'recordcount', ''),
			
			'report_agentinboundlog_monthly' => array('Report', 'report', 'report', 'agentinboundlog', 'option=monthly'),
			'report_agentinboundlog_daily' => array('Daily Agent Report', 'crm', 'report', 'agentinboundlog', 'option=daily'),
			'report_agentsessionlog' => array('Daily Agent Session Report', 'crm', 'report', 'agentsessionlog', ''),
			'cdr_agent_inbound' => array('Agent CDR', 'table', 'cdr', 'agent', 'type=inbound'),
			'access_login' => array('Login Activity Report', 'table', 'access', 'login', ''),
			'report_agentfulldaylog' => array('Full Day Agent Report', 'user', 'report', 'agentfulldaylog', ''),
			'report_fulldayservicelevel' => array('Daily Service Level Monitoring', 'table', 'report', 'fulldayservicelevel', ''),
			'report_skilllogbyinterval' => array('Skill Report by Interval', 'time', 'report', 'skilllogbyinterval', ''),
			'report_fulldayskill' => array('Full Day Skill Report', 'table', 'report', 'fulldayskill', ''),
			'report_skillspectrum' => array('Spectrum Report of Skill', 'time', 'report', 'skillspectrum', ''),
			'report_agentperfinbound' => array('Agent Perf - Inbound', 'crm', 'report', 'agentperfinbound', ''),
			'report_agentcdrsummary' => array('Agent CDR Summary', 'crm', 'report', 'agentcdrsummary', ''),
			'report_agentperfoutboundmanual' => array('Agent Perf - Outbound', 'crm', 'report', 'agentperfoutboundmanual', ''),
			'report_ansskilldelayspectrum' => array('Answer Delay Spectrum', 'table', 'report', 'ansskilldelayspectrum', ''),
			'report_abdnskilldelayspectrum' => array('Abandoned Delay Spectrum', 'table', 'report', 'abdnskilldelayspectrum', ''),
			'report_agentcall' => array('Agent Call - Inbound', 'crm', 'report', 'agentcall', ''),
			'report_skillsource' => array('Skill Report - Inbound', 'app-list', 'report', 'skillsource', ''),
			'report_agenttime' => array('Agent Time', 'away', 'report', 'agenttime', '')
		);
		if (UserAuth::hasRole('admin')) {
			$_menus = array('chart', 'report_channelday', 'report_channelmonth', 'cdr', 'cdr_skill', 'cdr_inbound', 'audit', 'access_audit', 'access_privilege', 
            'ivr', 'ivrreport_servicereq', 'ivrreport_servicelog', 'ivrreport_service-summary', 'campaign', 'crm_recordcount', 'crminreport_', 'crminreport_recordcount', 'agent', 
			'report_agentinboundlog_monthly', 'report_agentinboundlog_daily', 
			'report_agentsessionlog', 'cdr_agent_inbound', 'access_login', 'statistics', 'report_agentfulldaylog', 'report_fulldayservicelevel', 'report_skilllogbyinterval', 'report_fulldayskill', 
			'report_skillspectrum', 'performance', 'report_agentperfinbound', 'report_agentcdrsummary', 'report_agentperfoutboundmanual', 'report_ansskilldelayspectrum', 
			'report_abdnskilldelayspectrum', 'source', 'report_agentcall', 'report_skillsource', 'report_agenttime');
		} else if (UserAuth::hasRole('supervisor')) {
			$_menus = array('chart', 'report_channelday', 'report_channelmonth', 'cdr', 'cdr_skill', 'cdr_inbound', 
            'ivr', 'ivrreport_servicereq', 'ivrreport_servicelog', 'ivrreport_service-summary', 'campaign', 'crm_recordcount', 'crminreport_', 'crminreport_recordcount', 'agent', 
			'report_agentinboundlog_monthly', 'report_agentinboundlog_daily', 
			'report_agentsessionlog', 'cdr_agent_inbound', 'statistics', 'report_agentfulldaylog', 'report_fulldayservicelevel', 'report_skilllogbyinterval', 'report_fulldayskill', 
			'report_skillspectrum', 'performance', 'report_agentperfinbound', 'report_agentcdrsummary', 'report_agentperfoutboundmanual', 'report_ansskilldelayspectrum', 
			'report_abdnskilldelayspectrum', 'source', 'report_agentcall', 'report_skillsource', 'report_agenttime');
		} else {
			//UserAuth::hasRole('dashboard')
			$_menus = array();
		}
	} else if ($_side_menu_index == 'email') {
		$TopTitlePrefix = '<li><a href="' . $this->url('task=agents') . '"><i class="fa fa-home"></i> Home</a></li><li><a href="' . $this->url('task=email') . '">Email</a></li>';
		$CurrentMenuPages = array(
			'module' => array('Module', 'th-large ', '', '', ''),
			'agents_' => array('Home', 'home', 'agents', '', ''),
			'email_dashboard' => array('Dashboard', 'monitor', 'email', 'dashboard', ''),
			'email_init' => array('Email', 'email', 'email', '', ''),
			'email_init_myjob' => array('My Tickets', 'email', 'email', '', 'type=myjob'),
			'email_create' => array('Create Ticket', 'email', 'email', 'create', ''),
			'emailtemplate_' => array('Email Template', 'email', 'emailtemplate', '', ''),
			'email_skills' => array('Email Skill', 'email', 'email', 'skills', ''),
			'emailallowed_' => array('CC/BCC Email', 'email', 'emailallowed', '', ''),
			'report' => array('Report', 'module', '', '', ''),
			'emailreport_disposition' => array('Disposition', 'table', 'emailreport', 'disposition', ''),
			'emailreport_status' => array('Status', 'table', 'emailreport', 'status', ''),
			'emailemailreport_agentactivity' => array('Agent Activity', 'user', 'emailreport', 'agentactivity', '')

		);
		if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
			$_menus = array('module', 'email_dashboard', 'email_init', 'email_init_myjob', 'email_create', 'emailtemplate_', 'email_skills', 'emailallowed_', 'report', 'emailreport_disposition', 'emailreport_status', 'emailemailreport_agentactivity');
		} else if (UserAuth::hasRole('agent')) {
			$_menus = array('module', 'email_dashboard', 'email_init', 'email_init_myjob', 'email_create', 'emailtemplate_');
		} else {
			//UserAuth::hasRole('dashboard')
			$_menus = array();
		}

	} else {
		$TopTitlePrefix = '<li><a href="' . $this->url('task=agents') . '"><i class="fa fa-home"></i> Home</a></li>';
		$CurrentMenuPages = array(
			'home' => array('Home', 'th', '', '', ''),			
			'module' => array('Module', 'module', '', '', ''),
			'dialer' => array('Call Control', 'th', 'agent', 'dialer', ''),
			'agent_' => array('Profile', 'home', 'agent', '', ''),
			'agent_report' => array('Day Report', 'app-list', 'agent', 'day-report', ''),
			'agent_skillcdr' => array('CDR', 'app-list', 'agent', 'skill-cdr', ''),
			'agents_' => array('Agent', 'user', 'agents', '', ''),
			'supervisors_' => array('Supervisor', 'user', 'supervisors', '', 'utype=S'),
			'ctis_' => array('CTI', 'share-alt', 'ctis', '', ''),
			'skills_' => array('Skill', 'list', 'skills', '', ''),
			'ivrs_' => array('IVR', 'play-circle', 'ivrs', '', ''),
			'campaign_' => array('Campaign', 'tasks', 'campaign', '', ''),
			'crm_' => array('CRM', 'file-text', 'crm', '', ''),
			'crm_in_' => array('CRM-Inbound', 'file-text', 'crm_in', '', ''),
			'report_dashboard' => array('Dashboard', 'dashboard', '', '', '<a href="javascript:void(0);" onclick="popupWindow(\'' . $this->url('task=report&act=dashboard') . '\')">'),
			'knowledge_list' => array('Knowledge Base', 'table', 'knowledge', 'list', ''),
			'settings_' => array('Tools', 'cog', 'settings', '', ''),
			'conference_' => array('Conference', 'conference', 'conference', '', ''),
			'linkstatus_' => array('Link Status', 'link', 'linkstatus', '', ''),
			'report_agentinboundlog' => array('Report', 'bar-chart', 'report', 'agentinboundlog', 'option=monthly'),
			'email_' => array('Email', 'envelope-o', 'email', '', '')
		);
		if (UserAuth::hasRole('admin')) {
			$_menus = array('home', 'dialer','agents_', 'supervisors_', 'ctis_', 'skills_', 'ivrs_', 'campaign_', 'crm_', 'crm_in_', 'report_dashboard', 'knowledge_list', 'settings_');
			if ($this->ss7_link_status) {$_menus[] = 'linkstatus_';}
			$_menus[] = 'report_agentinboundlog';
			if ($this->email_module) {$_menus[] = 'module'; $_menus[] = 'email_';}
		} else if (UserAuth::hasRole('supervisor')) {
			$_menus = array('home', 'dialer','agents_', 'skills_', 'campaign_', 'crm_', 'crm_in_', 'report_dashboard', 'knowledge_list', 'conference_', 'report_agentinboundlog');
			if ($this->email_module) {$_menus[] = 'module'; $_menus[] = 'email_';}
		} else if (UserAuth::hasRole('agent')) {
			$_menus = array('home', 'dialer', 'agent_', 'knowledge_list', 'agent_report', 'agent_skillcdr', 'crm_');
			if ($this->email_module) {$_menus[] = 'module'; $_menus[] = 'email_';}
		} else {
			//UserAuth::hasRole('dashboard')
			$_menus = array();
		}
	}
}
?>