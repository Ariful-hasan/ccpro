<?php

class Report extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	}
	
	function actionChannelday()
	{
		include('lib/DateHelper.php');
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$dateinfo = DateHelper::get_input_time_details();
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		$data['rday_date'] = $repLastDate;
		$data['repDayMsg'] = UserAuth::getRepErrMsg();
		
		$data['day'] = $dateinfo->sdate;
		$data['pageTitle'] = 'Channel Graph by Hour';
		$data['side_menu_index'] = 'reports';
		$data['request'] = $this->getRequest();
		$data['tid'] = $tid;
		$data['dateinfo'] = $dateinfo;
		$data['trunk_options'] = $setting_model->getTrunkOptions();
		$this->getTemplate()->display('channel_chart', $data);
	}

	function actionChannelmonth()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data['month'] = date("m");
		$data['year'] = date("Y");
		$data['pageTitle'] = 'Channel Graph by Day';
		$data['side_menu_index'] = 'reports';
		$data['request'] = $this->getRequest();
		$data['tid'] = $tid;
		
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Ym', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		$data['rday_date'] = $repLastDate;
		$data['repDayMsg'] = UserAuth::getRepErrMsg();
		
		$data['trunk_options'] = $setting_model->getTrunkOptions();
		$this->getTemplate()->display('channel_chart', $data);
	}

	function actionFulldayskill()
	{
		/* include('model/MReport.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		//echo 'a';
		$dateinfo = DateHelper::get_input_time_details();
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getSkillLogPerDay($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (is_array($data['logs'])) {
			include('model/MSetting.php');
			$setting_model = new MSetting();
			$sl_method = $setting_model->getSetting('sl_method');;
			$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		}
		
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Full Day Skill Report : ' . DateHelper::get_date_title($dateinfo);
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_log_full_day', $data);
		 */
		
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$sl_method = $setting_model->getSetting('sl_method');;
		$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		 
		$data['pageTitle'] = 'Skill Report - Daily';
		$data['dataUrl'] = $this->url('task=get-report-data&act=fulldayskill');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_log_full_day', $data);
	}

	function actionFulldayservicelevel()
	{
		/* include('model/MReport.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		//echo 'a';
		
		$dateinfo = DateHelper::get_input_time_details();
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getSkillLogPerDay($dateinfo);	
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;		
		
		if (is_array($data['logs'])) {
			include('model/MSetting.php');
			$setting_model = new MSetting();
			$sl_method = $setting_model->getSetting('sl_method');;
			$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		}
		
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Daily Service Level Monitoring : ' . DateHelper::get_date_title($dateinfo);
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_service_level', $data);
		 */
	    include('model/MSetting.php');
	    $setting_model = new MSetting();
	    $sl_method = $setting_model->getSetting('sl_method');;
	    $data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
	    
		$data['pageTitle'] = 'Daily Service Level Monitoring';
		$data['dataUrl'] = $this->url('task=get-report-data&act=fulldayservicelevel');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_service_level', $data);
	}

	function actionSkillspectrumOld() {
		include('model/MReport.php');
		include('model/MSkill.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$skill_model = new MSkill();

        $day = !empty($_POST['sdate']) ? generic_date_format($_POST['sdate']) : date('Y-m-d');
        $dateinfo = DateHelper::get_input_time_details(false, $day);   ////// New DateInfo ////////

		//$dateinfo = DateHelper::get_input_time_details();     /////// Old DateInfo
		$skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
		$skills = $skill_model->getSkillOptions();
		if (empty($skills)) {
			$skills = array();
		}
		$data['skills'] = array_merge(array('' => 'All'), $skills);
		/*
		if (empty($skill) && is_array($data['skills'])) {
			$skill = key($data['skills']);
		}
		*/
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getSpectrumLogBySkill($skill, $dateinfo);
			//GPrint($dateinfo);die;
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['skill'] = $skill;
		$data['pageTitle'] = 'Spectrum Report of Skill : ' . DateHelper::get_date_title($dateinfo);
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_log_spectrum', $data);
	}


    function actionSkillspectrum() {
        include('model/MReport.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $skill_model = new MSkill();

        $dateInfo = new stdClass();
        $dateInfo->sdate = !empty($_REQUEST['sdate']) ? generic_date_format_from_report_datetime($_REQUEST['sdate']) : date('Y-m-d');
        $dateInfo->edate = !empty($_REQUEST['edate']) ? generic_date_format_from_report_datetime($_REQUEST['edate']) : date('Y-m-d');
        $dateInfo->stime = !empty($_REQUEST['sdate']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT . " H:i", $_REQUEST['sdate']), 'H') : '00:00';
        $dateInfo->etime = !empty($_REQUEST['edate']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT . " H:i", $_REQUEST['edate']), 'H') :'23:59';

        $skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
        $skills = $skill_model->getSkillOptions();
        if (empty($skills)) {
            $skills = array();
        }
        $data['skills'] = array_merge(array('' => 'All'), $skills);

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateInfo->sdate) && $dateInfo->sdate < $repLastDate){
            $errMsg = UserAuth::getRepErrMsg();
            $errType = 1;
            $data['logs'] = "";

        }else {
            $data['logs'] = $report_model->getSpectrumLogBySkill($skill, $dateInfo);
        }
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['dateinfo'] = $dateInfo;
        $data['from'] = $_POST['sdate'] ? $_POST['sdate'] : date(REPORT_DATE_FORMAT . " 00:00");
        $data['to'] = $_POST['edate'] ? $_POST['edate'] :  date(REPORT_DATE_FORMAT . " 23:59");

        $data['request'] = $this->getRequest();
        $data['skill'] = $skill;
        $data['pageTitle'] = 'Spectrum Report of Skill : ';
        $data['reportHeader'] = true;
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_skill_log_spectrum_new', $data);
    }

	function actionAnsskilldelayspectrum()
	{
		/* include('model/MReport.php');
		include('model/MSkill.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$skill_model = new MSkill();
		//echo 'a';
		$dateinfo = DateHelper::get_input_time_details();
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getServiceAnsDelaySpectrum('', $dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;

		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['pageTitle'] = 'Delay Spectrum of Answered Skill : ' . DateHelper::get_date_title($dateinfo);
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_delay_spectrum', $data);
		 */
		
		$data['pageTitle'] = 'Delay Spectrum of Answered Skill';
		$data['dataUrl'] = $this->url('task=get-report-data&act=ansskilldelayspectrum');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_delay_spectrum', $data);
	}

	function actionAbdnskilldelayspectrum()
	{
		/* include('model/MReport.php');
		include('lib/DateHelper.php');
		include('model/MSkill.php');
		$report_model = new MReport();
		$skill_model = new MSkill();

		$dateinfo = DateHelper::get_input_time_details();
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getServiceAbdnDelaySpectrum('', $dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['pageTitle'] = 'Delay Spectrum of Abandoned Skill : ' . DateHelper::get_date_title($dateinfo);
		
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_delay_spectrum', $data);
		 */
		
		$data['pageTitle'] = 'Delay Spectrum of Abandoned Skill';
		$data['dataUrl'] = $this->url('task=get-report-data&act=abdnskilldelayspectrum');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_delay_spectrum', $data);
	}

	function actionSkilllogbyinterval()
	{
		/* include('model/MReport.php');
		include('model/MSkill.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$skill_model = new MSkill();

		$dateinfo = DateHelper::get_input_time_details();
		$skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';

		$data['start_tstamp'] = strtotime($dateinfo->sdate." 00:00:00");
		$data['end_tstamp'] = $dateinfo->sdate == date('Y-m-d') ? strtotime($dateinfo->sdate." " . date("H:i:s")) : strtotime($dateinfo->sdate." 23:59:59");

		$data['skills'] = $skill_model->getSkillOptions();
		if (is_array($data['skills'])) {
			$data['skills'] = array_merge(array(''=>'All Skill'), $data['skills']);
		}

		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$data['logs'] = "";
		}else {
			$data['logs'] = $report_model->getSkillLogByInterval($dateinfo, $skill);	
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;		
		
		if (is_array($data['logs'])) {
			include('model/MSetting.php');
			$setting_model = new MSetting();
			$sl_method = $setting_model->getSetting('sl_method');;
			$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		}
		
		$data['dateinfo'] = $dateinfo;
		$data['skill'] = $skill;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill Report by Interval : ' . DateHelper::get_date_title($dateinfo);
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_log_interval', $data);
		 */
	    include('model/MSkill.php');
	    $skill_model = new MSkill();
	    $data['skills'] = $skill_model->getSkillOptions();
	    if (is_array($data['skills'])) {
	        $data['skills'] = array_merge(array('*'=>'All Skill'), $data['skills']);
	    }
	    
	    include('model/MSetting.php');
	    $setting_model = new MSetting();
	    $sl_method = $setting_model->getSetting('sl_method');;
	    $data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
	    
		$data['pageTitle'] = 'Skill Report by Interval';
		$data['dataUrl'] = $this->url('task=get-report-data&act=skilllogbyinterval');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_log_interval', $data);
	}

	function actionAgentperfinbound()
	{
		/* include('model/MReport.php');
		include('model/MAgent.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$agent_model = new MAgent();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");

		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numAgentPerfInbound($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (!isset($_REQUEST['download'])) $data['logs'] = $pagination->num_records > 0 ? 
			$report_model->getAgentPerfInbound($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['report_model'] = $report_model;
		$data['request'] = $this->getRequest();
		$data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
		$data['pageTitle'] = 'Agent Performance Report - Inbound';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_performance_inbound', $data); */
		
		include('model/MSetting.php');
		$setting_model = new MSetting();
		            
		$data['aux_messages'] = $setting_model->getBusyMessages('Y');
		$data['pageTitle'] = 'Agent Performance Report - Inbound';
		$data['dataUrl'] = $this->url('task=get-report-data&act=agentperfinbound');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_performance_inbound', $data);
	}

	function actionAgentcall()
	{
		include('model/MReport.php');
		include('model/MAgent.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		include('conf.extras.php');
		$report_model = new MReport();
		$agent_model = new MAgent();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");
		
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numAgentCallSourceLog($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;		

		$data['logs'] = null;
		if (!isset($_REQUEST['download'])) {
			$data['logs'] = $pagination->num_records > 0 ? 
				$report_model->getAgentCallSourceLog($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		} else {
			$data['report_model'] = $report_model;
		}
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;

		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['country_prefix'] = $extra->report_country_prefix;
		$data['skills'] = $skill_model->getSkillOptions();
		$data['agents'] = $agent_model->getAgentNames();
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Agent Call - Inbound';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_call', $data);
	}

	function actionSkillsource()
	{
		include('model/MReport.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		include('conf.extras.php');
		$report_model = new MReport();
		$skill_model = new MSkill();
		$pagination = new Pagination();
		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");
		
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numSkillSourceLog($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;		
		
		$data['logs'] = null;
		if (!isset($_REQUEST['download'])) {
			$data['logs'] = $pagination->num_records > 0 ? 
				$report_model->getSkillSourceLog($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		} else {
			$data['report_model'] = $report_model;
		}
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;

		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['country_prefix'] = $extra->report_country_prefix;
		$data['skills'] = $skill_model->getAllSkillOptions('', 'array');
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Skill Report - Inbound';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_source', $data);
	}

	function actionAgenttime()
	{
		include('model/MReport.php');
		include('model/MSetting.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		include('conf.extras.php');

		$report_model = new MReport();
		$setting_model = new MSetting();
		$pagination = new Pagination();
		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");
		
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numAgentTime($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;		

		$data['logs'] = null;
		if (!isset($_REQUEST['download'])) {
			$data['logs'] = $pagination->num_records > 0 ? 
				$report_model->getAgentTime($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		} else {
			$data['report_model'] = $report_model;
		}
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;

		$data['aux_messages'] = $setting_model->getBusyMessages('Y');
		$data['country_prefix'] = $extra->report_country_prefix;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Agent Time';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_time', $data);
	}

	function actionAgentcdrsummary()
	{
		/* include('model/MReport.php');
		include('model/MAgent.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$agent_model = new MAgent();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");

		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numAgentPerfInbound($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (!isset($_REQUEST['download'])) $data['logs'] = $pagination->num_records > 0 ? 
			$report_model->getAgentPerfInbound($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['report_model'] = $report_model;
		$data['request'] = $this->getRequest();
		$data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
		$data['pageTitle'] = 'Agent CDR Summary';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_cdr_summary', $data);
		 */
		
		$data['pageTitle'] = 'Agent CDR Summary';
		$data['dataUrl'] = $this->url('task=get-report-data&act=agentcdrsummary');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_cdr_summary', $data);
	}

	function actionAgentperfoutboundmanual()
	{
		/* include('model/MReport.php');
		include('model/MAgent.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$agent_model = new MAgent();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");

		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)){
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
		}
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
			$errMsg = UserAuth::getRepErrMsg();
			$errType = 1;
			$pagination->num_records = 0;
		}else {
			$pagination->num_records = $report_model->numAgentPerfOutBoundManual($dateinfo);
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (isset($_REQUEST['download'])) $data['report_model'] = $report_model;
		else $data['logs'] = $pagination->num_records > 0 ? 
			$report_model->getAgentPerfOutBoundManual($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
		$data['pageTitle'] = 'Agent Performance Report - Outbound';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_performance_outbound', $data);
		 */
		
		$data['pageTitle'] = 'Agent Performance Report - Outbound';
		$data['dataUrl'] = $this->url('task=get-report-data&act=agentperfoutboundmanual');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_performance_outbound', $data);
	}
/*
	function actionAgentperfoutboundautodial()
	{
		include('model/MReport.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
				"&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime&page=");

		$pagination->num_records = $report_model->numAgentPerfOutBoundAutoDial($dateinfo);
		$data['logs'] = $pagination->num_records > 0 ? 
			$report_model->getAgentPerfOutBoundAutoDial($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Agent Performance Report - Outbound Auto Dial';
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_performance_outbound', $data);
	}
*/
	function actionAgentinboundlog()
	{
		include('model/MReport.php');
		include('lib/DateHelper.php');
		include('model/MAgent.php');

		$agent_model = new MAgent();
		$report_model = new MReport();

		$option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
		

		if ($option == 'daily') {
			$dateinfo = DateHelper::get_input_time_details();
			//$data['pageTitle'] = 'Agent Log : ' . date("M d, Y", $dateinfo->ststamp);
			$data['pageTitle'] = 'Agent Log';
		} else {
			$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date('y-m');
			$m = substr($month, 3, 2);
			$y = '20' . substr($month, 0, 2);
			if (!checkdate($m, '01', $y)) {
				$month = date('y-m');
			}
			$dateinfo = DateHelper::get_input_time_details(false, $y.'-'.$m.'-'.'01', $y.'-'.$m.'-'.'31', '00:00', '23:59');
			//$data['pageTitle'] = 'Agent Log : ' . date("M Y", $dateinfo->ststamp);
			$data['pageTitle'] = 'Agent Log';
		}
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=reportagentinboundlog&option='.$option);
		$data['option'] = $option;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_monthly_inbound_log', $data);
	}

	function actionDidinboundlog()
	{
		include('model/MReport.php');
		include('lib/DateHelper.php');
		include('model/MAgent.php');

		$agent_model = new MAgent();
		$report_model = new MReport();

		$option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
		

		if ($option == 'daily') {
			$dateinfo = DateHelper::get_input_time_details();
			//$data['pageTitle'] = 'Agent Log : ' . date("M d, Y", $dateinfo->ststamp);
			$data['pageTitle'] = 'Daily DID Report';
		} else {
			$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date('y-m');
			$m = substr($month, 3, 2);
			$y = '20' . substr($month, 0, 2);
			if (!checkdate($m, '01', $y)) {
				$month = date('y-m');
			}
			$dateinfo = DateHelper::get_input_time_details(false, $y.'-'.$m.'-'.'01', $y.'-'.$m.'-'.'31', '00:00', '23:59');
			//$data['pageTitle'] = 'Agent Log : ' . date("M Y", $dateinfo->ststamp);
			$data['pageTitle'] = 'DID Report';
		}
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=didinboundlog&option='.$option);
		$data['option'] = $option;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_did_monthly_inbound_log', $data);
	}
		
	function actionDidinboundlog2()
        {
                include('model/MReport.php');
                include('lib/DateHelper.php');
                include('model/MAgent.php');

                $agent_model = new MAgent();
                $report_model = new MReport();

                $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';

                $dateinfo = DateHelper::get_input_time_details();
                $data['pageTitle'] = 'DID Report';

                $data['dataUrl'] = $this->url('task=get-report-data&act=didinboundlog2');
                $data['option'] = $option;
                $data['side_menu_index'] = 'reports';
                $data['reportHeader'] = true;
                $this->getTemplate()->display('report_did_inbound_log', $data);
        }
        
        
        function actionCampaignRecordStatus()
        {
                //include('model/MReport.php');
                //include('lib/DateHelper.php');
                //include('model/MAgent.php');
                //$agent_model = new MAgent();
                //$report_model = new MReport();
                //$dateinfo = DateHelper::get_input_time_details();
                $data['pageTitle'] = 'Campaign Record Status';
                $data['dataUrl'] = $this->url('task=get-report-data&act=campaign-record-status');
                //$data['option'] = $option;
                $data['side_menu_index'] = 'campaign';
                $data['reportHeader'] = true;
                $this->getTemplate()->display('report_campaign_record_status', $data);
        }

	function actionAgentinboundlog_bk()
	{
	    include('model/MReport.php');
	    include('lib/Pagination.php');
	    include('lib/DateHelper.php');
	    include('model/MAgent.php');
	
	    $agent_model = new MAgent();
	    $report_model = new MReport();
	    $pagination = new Pagination();
	
	    $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
	
	
	    if ($option == 'daily') {
	        $dateinfo = DateHelper::get_input_time_details();
	        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
	            "&option=daily&sdate=$dateinfo->sdate&edate=&stime=&etime=&page=");
	        $data['pageTitle'] = 'Agent Log : ' . date("M d, Y", $dateinfo->ststamp);
	        $data['smi_selection'] = 'report_agentinboundlog_daily';
	    } else {
	        $month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date('y-m');
	        $m = substr($month, 3, 2);
	        $y = '20' . substr($month, 0, 2);
	        if (!checkdate($m, '01', $y)) {
	            $month = date('y-m');
	        }
	        $dateinfo = DateHelper::get_input_time_details(false, $y.'-'.$m.'-'.'01', $y.'-'.$m.'-'.'31', '00:00', '23:59');
	        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
	            "&option=monthly&month=$month&page=");
	        $data['pageTitle'] = 'Agent Log : ' . date("M Y", $dateinfo->ststamp);
	        $data['smi_selection'] = 'report_agentinboundlog_monthly';
	    }
	
	    //print_r($dateinfo);
	
	    $data['dataUrl'] = $this->url('task=get-report-data&act=reportagentinboundlog&option='.$option);
	
	    $errMsg = '';
	    $errType = 1;
	    $reportDays = UserAuth::getReportDays();
	    $repLastDate = "";
	    if (!empty($reportDays)){
	        $toDate = date("Y-m-d");
	        if ($option == 'daily') {
	            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
	        }else {
	            $repLastDate = date('Y-m-01', strtotime($toDate. ' - '.$reportDays.' days'));
	        }
	    }
	    if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
	        $errMsg = UserAuth::getRepErrMsg();
	        $errType = 1;
	        $pagination->num_records = 0;
	    }else {
	        $pagination->num_records = $report_model->numAgentLogInbound($dateinfo);
	    }
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	
	    if (!isset($_REQUEST['download'])) $data['logs'] = $pagination->num_records > 0 ?
	    $report_model->getAgentInboundLog($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
	    //print_r($dateinfo);
	    //$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;
	    $pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
	    $data['pagination'] = $pagination;
	    $data['dateinfo'] = $dateinfo;
	    $data['option'] = $option;
	    $data['request'] = $this->getRequest();
	    $data['report_model'] = $report_model;
	    $data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
	    $data['side_menu_index'] = 'reports';
	    $this->getTemplate()->display('report_agent_monthly_inbound_log', $data);
	}

	function actionAgentsessionlog()
	{
		include('model/MReport.php');
		include('lib/DateHelper.php');
		include('model/MAgent.php');

		$agent_model = new MAgent();
		$report_model = new MReport();

		$dateinfo = DateHelper::get_input_time_details();
		$data['pageTitle'] = 'Agent Session Log : ' . date("M d, Y", $dateinfo->ststamp);

		$data['dataUrl'] = $this->url('task=get-report-data&act=reportsessionlog');
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_session_log', $data);
	}

	function actionAgentsessiondetails()
	{
		include('model/MReport.php');
		include('model/MSetting.php');
		include('lib/DateHelper.php');
		include('lib/AgentSessionHelper.php');
		$report_model = new MReport();
		$setting_model = new MSetting();
		$dateinfo = DateHelper::get_input_time_details();
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';

		$data['aux_messages'] = $setting_model->getBusyMessages('', 0, 0, true);
		
		if (!isset($_REQUEST['download'])) {
			$data['logs'] = $report_model->getAgentSessionLog($agentid, $dateinfo->sdate . ' 00:00:00');
			
			$endtime = '23:59:59';
			if ($dateinfo->sdate == date('Y-m-d')) {
				$endtime = date("H:i:s");
			}

			$stime = strtotime($dateinfo->sdate . ' 00:00:00');
			$etime = strtotime($dateinfo->sdate . ' ' . $endtime);
			$bars = AgentSessionHelper::getTimeLines($data['logs'], $stime, $etime);
			//var_dump($bars);
			$data['session_chart'] = AgentSessionHelper::getSessionChart($bars, $stime, $etime, $data['aux_messages']);
		} else {
			$data['report_model'] = $report_model;
		}

		$data['pageTitle'] = "Agent[$agentid] Session Details : " . date("M d, Y", $dateinfo->ststamp);
		$data['side_menu_index'] = 'reports';
		$data['dateinfo'] = $dateinfo;
		$data['agentid'] = $agentid;
		$data['request'] = $this->getRequest();
		$this->getTemplate()->display('report_agent_session_details', $data);
	}

	function actionAgentmonthlysession()
	{
		include('model/MReport.php');
		include('lib/DateHelper.php');

		$report_model = new MReport();
		$dateinfo = DateHelper::get_input_time_details();
		$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		$option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : '';
		if ($option != 'monthly') {
			$option = 'daily';
		}
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=reportagentmonthlylog&option='.$option.'&agentid='. $agentid . '&sdate='.$dateinfo->sdate . '&edate=' . $dateinfo->edate );
		
		$data['pageTitle'] = "Agent[$agentid] Session Details : " . date("M, Y", $dateinfo->ststamp);
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_session_log_monthly', $data);
	}

	function actionAgentfulldaylog()
	{
	    include('model/MSetting.php');
	    $setting_model = new MSetting();
	    
	    $data['aux_messages'] = $setting_model->getBusyMessages('Y');
		$data['pageTitle'] = 'Full Day Agent Report';
		$data['dataUrl'] = $this->url('task=get-report-data&act=agentfulldaylog');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_full_day_inbound_log', $data);
	}
	
	function actionAgentSessionSummary()
	{
	    include('model/MSetting.php');
	    $setting_model = new MSetting();
	    
	    $data['aux_messages'] = $setting_model->getBusyMessages('Y');
	    $data['pageTitle'] = 'Agent Session Summary';
	    $data['dataUrl'] = $this->url('task=get-report-data&act=agent-session-summary');
	    $data['reportHeader'] = true;
	    $data['side_menu_index'] = 'reports';
	    $this->getTemplate()->display('report_agent_session_summary', $data);
	}
	
	function actionDidCallSummary()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();
		 
		$data['aux_messages'] = $setting_model->getBusyMessages('Y');
		$data['pageTitle'] = 'DID Call Summary';
		$data['dataUrl'] = $this->url('task=get-report-data&act=did-call-summary');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_did_call_summary', $data);
	}
	
	function actionSkillReport()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		
		include('model/MLanguage.php');
		$language_model = new MLanguage();
		$langs = $language_model->getActiveLanguageListArray();
		$data['lang_options'] = array_merge(array("Select"), $langs);
		
		$data['pageTitle'] = 'Skill Report';
		$data['dataUrl'] = $this->url('task=get-report-data&act=skill-call-report');
		
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$sl_method = $setting_model->getSetting('sl_method');
		$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_call_report', $data);
	}


	function actionSkillReportDaily()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		
		include('model/MLanguage.php');
		$language_model = new MLanguage();
		$langs = $language_model->getActiveLanguageListArray();
		$data['lang_options'] = array_merge(array("Select"), $langs);
		
		$data['pageTitle'] = 'Skill Report - Daily';
		$data['dataUrl'] = $this->url('task=get-report-data&act=skill-call-report-daily');
		
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$sl_method = $setting_model->getSetting('sl_method');
		$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_call_report_daily', $data);
	}
	
	function actionSkillCallSummary()
	{
		include('model/MSkill.php');
		$skill_model = new MSkill();
			
		$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		
		include('model/MLanguage.php');
                $language_model = new MLanguage();
                $langs = $language_model->getActiveLanguageListArray();
                
                //print_r($data['lang_options']);
                $data['lang_options'] = array_merge(array("Select"), $langs);
		
		//$data['lang_options'] = array('0'=>'Select', 'EN'=>'English', 'BN'=>'Bangla');
		
		$data['pageTitle'] = 'Skill Report - Interval';
		$data['dataUrl'] = $this->url('task=get-report-data&act=skill-call-summary');
		
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$sl_method = $setting_model->getSetting('sl_method');
		$data['sl_method'] = $sl_method->value == 'B' ? 'B' : 'A';
		
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_skill_call_summary', $data);
	}


	function actionIvrServiceSummary()
	{
		$data['pageTitle'] = 'IVR Service Summary';
		$data['dataUrl'] = $this->url('task=get-report-data&act=ivr-service-summary');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_ivr_service_summary_2', $data);
	}

	function actionAgentSkillReport()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$data['pageTitle'] = 'Skill Report of Agent';
		$data['dataUrl'] = $this->url('task=get-report-data&act=agent-skill-report');
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('report_agent_skill_report', $data);
	}
	
	function actionDbuserpanel()
	{
		$data['pageTitle'] = 'Dashboard Panel';
		$this->getTemplate()->display('dashboard_panel', $data);
	}
	
	function actionDashboard()
	{
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MAgent.php');
		$skill_model = new MSkill();
		$setting_model = new MSetting();
		$agent_model = new MAgent();

		if (isset($_GET['expand'])) {
			if ($_GET['expand'] == 'Y' || $_GET['expand'] == 'N') {
				//$_COOKIE['expanded_db'] = $_GET['expand'];
				setcookie("expanded_db", $_GET['expand'], time()+86400);
				$_COOKIE['expanded_db'] = $_GET['expand'];
			}
		}

		$data['seats'] = $setting_model->getSeats('Y');
		$data['aux_messages'] = $setting_model->getBusyMessages();
		
		$is_expanded_db = isset($_COOKIE['expanded_db']) && $_COOKIE['expanded_db'] == 'Y' ? 'Y' : 'N';
		if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) $is_expanded_db = 'Y';
		
		if ($is_expanded_db == 'Y') {
			$data['skills'] = $skill_model->getAllSkillOptions('Y');
			$data['agents'] = $agent_model->getAgentNames();
		} else {
			$data['skills'] = $agent_model->getAssignedSkills(UserAuth::getCurrentUser());
			$data['agents'] = $agent_model->getSupervisedAgents(UserAuth::getCurrentUser());
		}
		
		$data['pageTitle'] = 'gPlex Contact Center Dashboard';
		$data['is_expanded_db'] = $is_expanded_db;
		$data['request'] = $this->getRequest();
		$setting_model->addToAuditLog('Dashboard', 'V', "", "");
		$this->getTemplate()->display_only('dashboard-http', $data);
	}
	
	function actionDashboardws()
	{
		$this->actionDashboardnew();
		return;
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MAgent.php');
		include('model/MLanguage.php');
		$skill_model = new MSkill();
		$setting_model = new MSetting();
		$agent_model = new MAgent();
		$language_model = new MLanguage();

		if (isset($_GET['expand'])) {
			if ($_GET['expand'] == 'Y' || $_GET['expand'] == 'N') {
				//$_COOKIE['expanded_db'] = $_GET['expand'];
				setcookie("expanded_db", $_GET['expand'], time()+86400);
				$_COOKIE['expanded_db'] = $_GET['expand'];
			}
		}

		$data['seats'] = $setting_model->getSeats('Y', 0, 0, '', '', '', true);
		$data['aux_messages'] = $setting_model->getBusyMessages();
		
		$is_expanded_db = isset($_COOKIE['expanded_db']) && $_COOKIE['expanded_db'] == 'Y' ? 'Y' : 'N';
		if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) $is_expanded_db = 'Y';
		
		if ($is_expanded_db == 'Y') {
			$data['skills'] = $skill_model->getAllSkillOptions('Y');
			$data['agents'] = $agent_model->getAgentNames();
		} else {
			$data['skills'] = $agent_model->getAssignedSkills(UserAuth::getCurrentUser());
			$data['agents'] = $agent_model->getSupervisedAgents(UserAuth::getCurrentUser());
		}
		$data['languages'] = $language_model->getActiveLanguageList();
		
		$data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
		$data['agent_skills'] = $skill_model->getAgentSkills();
		//print_r($data['agent_skills']);
		$data['pageTitle'] = 'gPlex Contact Center Dashboard';
		$data['is_expanded_db'] = $is_expanded_db;
		$data['request'] = $this->getRequest();
		$setting_model->addToAuditLog('Dashboard', 'V', "", "");
		$this->getTemplate()->display_only('dashboard', $data);
	}
	
	function actionDashboardnew()
	{
		include('model/MSetting.php');
                include('model/MSkill.php');
                include('model/MAgent.php');
                include('model/MLanguage.php');
                $skill_model = new MSkill();
                $setting_model = new MSetting();
                $agent_model = new MAgent();
                $language_model = new MLanguage();

                if (isset($_GET['expand'])) {
                        if ($_GET['expand'] == 'Y' || $_GET['expand'] == 'N') {
                                //$_COOKIE['expanded_db'] = $_GET['expand'];
                                setcookie("expanded_db", $_GET['expand'], time()+86400);
                                $_COOKIE['expanded_db'] = $_GET['expand'];
                        }
                }

                $data['seats'] = $setting_model->getSeats('Y', 0, 0, '', '', '', true);
                $data['aux_messages'] = $setting_model->getBusyMessages();
                $data['CCSettings'] = $setting_model->getCCSettings();

                $is_expanded_db = isset($_COOKIE['expanded_db']) && $_COOKIE['expanded_db'] == 'Y' ? 'Y' : 'N';
                if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) $is_expanded_db = 'Y';

                if ($is_expanded_db == 'Y') {
                        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
                        $data['agents'] = $agent_model->getAgentNames();
                } else {
                        $data['skills'] = $agent_model->getAssignedSkillsShort(UserAuth::getCurrentUser());
                        $data['agents'] = $agent_model->getSupervisedAgents(UserAuth::getCurrentUser());
                }
                $data['languages'] = $language_model->getActiveLanguageList();

                $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
                $data['agent_skills'] = $skill_model->getAgentSkills();
                //print_r($data['agent_skills']);
                $data['pageTitle'] = 'gPlex Contact Center Dashboard';
                $data['is_expanded_db'] = $is_expanded_db;
                $data['request'] = $this->getRequest();
                $setting_model->addToAuditLog('Dashboard', 'V', "", "");
                //$this->getTemplate()->display_only('dashboard-ws-new', $data);

                $this->getTemplate()->display_only('dashboard-ws-new-2', $data);

		/*
		include('model/MSetting.php');
		include('model/MSkill.php');
		include('model/MAgent.php');
		include('model/MLanguage.php');
		$skill_model = new MSkill();
		$setting_model = new MSetting();
		$agent_model = new MAgent();
		$language_model = new MLanguage();

		if (isset($_GET['expand'])) {
			if ($_GET['expand'] == 'Y' || $_GET['expand'] == 'N') {
				//$_COOKIE['expanded_db'] = $_GET['expand'];
				setcookie("expanded_db", $_GET['expand'], time()+86400);
				$_COOKIE['expanded_db'] = $_GET['expand'];
			}
		}

		$data['seats'] = $setting_model->getSeats('Y', 0, 0, '', '', '', true);
		$data['aux_messages'] = $setting_model->getBusyMessages();
		
		$is_expanded_db = isset($_COOKIE['expanded_db']) && $_COOKIE['expanded_db'] == 'Y' ? 'Y' : 'N';
		if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) $is_expanded_db = 'Y';
		
		if ($is_expanded_db == 'Y') {
			$data['skills'] = $skill_model->getAllSkillOptions('Y');
			$data['agents'] = $agent_model->getAgentNames();
		} else {
			$data['skills'] = $agent_model->getAssignedSkills(UserAuth::getCurrentUser());
			$data['agents'] = $agent_model->getSupervisedAgents(UserAuth::getCurrentUser());
		}
		$data['languages'] = $language_model->getActiveLanguageList();
		
		$data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
		$data['agent_skills'] = $skill_model->getAgentSkills();
		//print_r($data['agent_skills']);
		$data['pageTitle'] = 'gPlex Contact Center Dashboard';
		$data['is_expanded_db'] = $is_expanded_db;
		$data['request'] = $this->getRequest();
		$setting_model->addToAuditLog('Dashboard', 'V', "", "");
		$this->getTemplate()->display_only('dashboard-ws-new', $data);
		*/
	}

	public function actionTabularDashboard()
	{
		AddModel('MSetting');
        AddModel('MSkill');
        AddModel('MAgent');
        AddModel('MSeat');
        include('conf.extras.php');

        $skill_model = new MSkill();
        $setting_model = new MSetting();
        $agent_model = new MAgent();
        $seat_model = new MSeat();
        //$language_model = new MLanguage();


        if (isset($_GET['expand']) && ($_GET['expand'] == 'Y' || $_GET['expand'] == 'N')) {
            setcookie("expanded_db", $_GET['expand'], time()+86400);
            $_COOKIE['expanded_db'] = $_GET['expand'];
        }


        $data['seats'] = $seat_model->getSeatIdLabelAsKeyValue();
        $data['aux_messages'] = $setting_model->getBusyMessages();
        $data['aux_messages'][] = $this->getExtraAuxCodeHavingId25();
        $data['CCSettings'] = $setting_model->getCCSettings();

        $is_expanded_db = isset($_COOKIE['expanded_db']) && $_COOKIE['expanded_db'] == 'Y' ? 'Y' : 'N';
        if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) {
            $is_expanded_db = 'Y';
        }

        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $data['agents'] = $agent_model->getAgentsWithSupervisorName('',false);

        $data['agents'] = $this->formatAgentsForDashboard($data['agents']);


        $data['highlight_row_threshold'] = !empty($extra->highlight_dashboard_row_after) ? $extra->highlight_dashboard_row_after : 120;

        $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
        $data['agent_skills'] = $skill_model->getAgentSkills();

        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['is_expanded_db'] = $is_expanded_db;
        $data['request'] = $this->getRequest();
        $setting_model->addToAuditLog('Tabular Dashboard', 'V', "", "");

        $this->getTemplate()->display_only('tabular_dashboard', $data);

	}


    public function actionTabularDashboardSkill()
    {
        AddModel('MSkill');
        include ('conf.extras.php');
        include ('config/constant.php');

        $skill_model = new MSkill();

        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['request'] = $this->getRequest();
        $data['service_level_calculation_method'] = $extra->sl_method;
        $data['show_sl_formula'] = defined('SHOW_SL_FORMULA_IN_REPORT') ? constant('SHOW_SL_FORMULA_IN_REPORT') : false;
        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $this->getTemplate()->display_only('tabular_dashboard_skill_info', $data);
    }

    public function actionCurrentDaySkillSummary()
    {
        AddModel('MReportNew');
        $report_model = new MReportNew();

        $response = $report_model->getCurrentDaySkillSummary();
        die(json_encode($response));
    }

	private function formatAgentsForDashboard($agents = [])
    {
        $result = [];
        if (empty($agents)){
            return $result;
        }

        foreach ($agents as $agent){
            if (empty($result[$agent->agent_id])){
                $result[$agent->agent_id] = $agent;
            }
            if (empty( $result[$agent->agent_id]->skill_set)){
                $result[$agent->agent_id]->skill_set = [];
                $result[$agent->agent_id]->skill_names = '';
            }
            $result[$agent->agent_id]->skill_set[] = $agent->skill_id;
            $result[$agent->agent_id]->skill_names .= !empty($result[$agent->agent_id]->skill_names) ? ", " : "";
            $result[$agent->agent_id]->skill_names  .= $agent->skill_name;
        }

        return $result;
    }



	function numOfSkillSourcePages($dateinfo)
	{
		if (empty($dateinfo->sdate) || empty($dateinfo->edate)) return 1;
		$start = strtotime($dateinfo->sdate);
		$end = strtotime($dateinfo->edate);
		//echo $start . '--' . $end;
		$num_pages =ceil(abs($end - $start) / 86400);
		return $num_pages+1;
	}

    public function actionDashboard777()
    {
        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MSetting');
        AddModel('MSeat');

        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $setting_model = new MSetting();
        $seat_model = new MSeat();

        $agents_with_skillSet = [];

       // $agents = $agent_model->getAgentsOf777Category();
        $agents = $agent_model->getAgentSkill();

        foreach ($agents as $agent){
            if (empty($agents_with_skillSet[$agent->agent_id])){
                $agents_with_skillSet[$agent->agent_id] = new stdClass();
                $agents_with_skillSet[$agent->agent_id]->agent_id = $agent->agent_id;
                $agents_with_skillSet[$agent->agent_id]->skillSet = [];
            }
            $agents_with_skillSet[$agent->agent_id]->skillSet[] =  $agent->skill_id;
        }

        $aux_messages = $setting_model->getBusyMessages();
        $aux_messages[] = $this->getExtraAuxCodeHavingId25();

        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['request'] = $this->getRequest();
        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $data['active_seats'] = $seat_model->getSeatIdLabelAsKeyValue();
        $data['CCSettings'] = $setting_model->getCCSettings();
        $data['aux_messages'] = $aux_messages;
        $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
        $data['agents_of_777_category'] = $agents_with_skillSet;
        $this->getTemplate()->display_only('grid_dashboard_777', $data);
    }

    public function actionDashboardExcept777()
    {
        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MSetting');
        AddModel('MSeat');

        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $setting_model = new MSetting();
        $seat_model = new MSeat();

        $agents_with_skillSet = [];

        //$agents = $agent_model->getAgentsExcept777Category();
        $agents = $agent_model->getAgentSkill();

        foreach ($agents as $agent){
            if (empty($agents_with_skillSet[$agent->agent_id])){
                $agents_with_skillSet[$agent->agent_id] = new stdClass();
                $agents_with_skillSet[$agent->agent_id]->agent_id = $agent->agent_id;
                $agents_with_skillSet[$agent->agent_id]->skillSet = [];
            }
            $agents_with_skillSet[$agent->agent_id]->skillSet[] =  $agent->skill_id;
        }

        $aux_messages = $setting_model->getBusyMessages();
        $aux_messages[] = $this->getExtraAuxCodeHavingId25();

        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['request'] = $this->getRequest();
        $data['CCSettings'] = $setting_model->getCCSettings();
        $data['aux_messages'] = $aux_messages;
        $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $data['active_seats'] = $seat_model->getSeatIdLabelAsKeyValue();
        $data['agents_except_777_category'] = $agents_with_skillSet;
        $this->getTemplate()->display_only('grid_dashboard_except_777', $data);
    }

    protected function getExtraAuxCodeHavingId25(){
        $aux_code25 = new stdClass();
        $aux_code25->aux_code = "25";
        $aux_code25->aux_type = "I";
        $aux_code25->message = "Just Logged In, Not Ready Yet";
        $aux_code25->active = "Y";

        return $aux_code25;
    }

    public function actionWallboard()
    {

        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MSetting');
        AddModel('MSeat');
        AddModel('MReportNew');

        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $setting_model = new MSetting();
        $seat_model = new MSeat();

        $agents_with_skillSet = [];


        $agents = $agent_model->getAgentSkill();

        foreach ($agents as $agent){
            if (empty($agents_with_skillSet[$agent->agent_id])){
                $agents_with_skillSet[$agent->agent_id] = new stdClass();
                $agents_with_skillSet[$agent->agent_id]->agent_id = $agent->agent_id;
                $agents_with_skillSet[$agent->agent_id]->skillSet = [];
            }
            $agents_with_skillSet[$agent->agent_id]->skillSet[] =  $agent->skill_id;
        }

        $aux_messages = $setting_model->getBusyMessages();
        $aux_messages[] = $this->getExtraAuxCodeHavingId25();

        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['request'] = $this->getRequest();
        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $data['active_seats'] = $seat_model->getSeatIdLabelAsKeyValue();
        $data['CCSettings'] = $setting_model->getCCSettings();
        $data['aux_messages'] = $aux_messages;
        $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
        $data['agents'] = $agents_with_skillSet;
        $data['voice_skills'] = $skill_model->getVoiceSkillIDs();
        $this->getTemplate()->display_only('combine_dashboard', $data);

    }

    public function actionGetDailySnapShotFotTheDay()
    {
        AddModel('MSkill');

        $skill_model = new MSkill();

        $response = $skill_model->getDailySnapShotData();
        $response = is_array($response) ? array_shift($response) : [];
        $response = array_map(function ($property){
            return round($property,0);
        }, (array)$response);

        die(json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR));
    }
}
