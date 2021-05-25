<?php

class Crm extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    include('model/MCrm.php');
	    include('model/MCampaign.php');
	    include('model/MCrmDisposition.php');
	    
	    $crm_model = new MCrm();
	    $campaign_model = new MCampaign();
	    $crm_dp_model = new MCrmDisposition();
	    
	    $data['pageTitle'] = 'CRM Record(s)';
	    $data['campaign_options'] = $campaign_model->getCampaignSelectOptions(true);
	    $data['lead_options'] = $campaign_model->getLeadSelectOptions(true);
	    $data['dp_options'] = $crm_dp_model->getDispositionsOpt(0, 0, true);
	    $dial_num = isset($_REQUEST['dial_num']) ? trim($_REQUEST['dial_num']) : "";
	    $camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
	    $leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
	    $disp_code = isset($_REQUEST['disp_code']) ? trim($_REQUEST['disp_code']) : "";
	    $num_records = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);
	    
	    if ($num_records > 0 && (!empty($dial_num) || !empty($camid) || !empty($leadid) || !empty($disp_code))) {
	        if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
	            $data['topMenuItems'][] = array('href'=>"task=lead&act=crmlead&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code", 'img'=>'fa fa-plus-square', 'label'=>'Add records to new lead', 'class'=>'add-lead');
	        }	    
	        $data['topMenuItems'][] = array('href'=>"task=crm&act=churn&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code", 'img'=>'fa fa-arrow-circle-o-left', 'label'=>'Churn records', 'class'=>'churn-record');
	    }
	    
	    $data['dataUrl'] = $this->url('task=get-home-data&act=crm_init');
	    $this->getTemplate()->display('crm', $data);
	    
	    /* 
		include('model/MCrm.php');
		include('model/MCampaign.php');
		include('model/MCrmDisposition.php');
		
		include('lib/Pagination.php');
		$crm_model = new MCrm();
		$campaign_model = new MCampaign();
		$crm_dp_model = new MCrmDisposition();
		$pagination = new Pagination();

		$dial_num = isset($_REQUEST['dial_num']) ? trim($_REQUEST['dial_num']) : "";
		$camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
		$leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
		$disp_code = isset($_REQUEST['disp_code']) ? trim($_REQUEST['disp_code']) : "";

		$data['pageTitle'] = 'CRM Record(s)';

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code");

		$pagination->num_records = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);

		$data['campaign_options'] = $campaign_model->getCampaignSelectOptions();
		$data['lead_options'] = $campaign_model->getLeadSelectOptions();
		$data['dp_options'] = $crm_dp_model->getDispositions();
		
		if (isset($_REQUEST['download']) && $pagination->num_records>0) {
			$audit_text = $this->getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $data['campaign_options'], $data['lead_options'], $data['dp_options']);
			$crm_model->addToAuditLog($data['pageTitle'], 'L', "", $audit_text);
			$this->downloadCRM($crm_model, $data['pageTitle'],  $this->getTemplate(), $dial_num, $camid, $leadid, $disp_code);
			exit;
		}

		$data['records'] = $pagination->num_records > 0 ? 
			$crm_model->getCrmRecords($dial_num, $camid, $leadid, $disp_code, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;

		if ($pagination->num_records > 0 && (!empty($dial_num) || !empty($camid) || !empty($leadid) || !empty($disp_code))) {
			$data['crm_churn_in_top'] = true;
		}
		
		$data['pagination'] = $pagination;
		$data['dial_num'] = $dial_num;
		$data['camid'] = $camid;
		$data['leadid'] = $leadid;
		$data['disp_code'] = $disp_code;
		$data['request'] = $this->getRequest();
		//$data['reportHeader'] = true;
		//$data['side_menu_index'] = 'reports';
		

		$data['topMenuItems'] = array();
		
		if (isset($data['crm_churn_in_top'])) {
		if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
			$data['topMenuItems'][] = array('href'=>"task=lead&act=crmlead&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code", 'img'=>'add.png', 'label'=>'Add records to new lead', 'class'=>'add-lead');
		}
		
		$data['topMenuItems'][] = array('href'=>"task=crm&act=churn&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code", 'img'=>'arrow_redo.png', 'label'=>'Churn records', 'class'=>'churn-record');
		}
		
		if (isset($_POST['search'])) {
			$audit_text = $this->getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $data['campaign_options'], $data['lead_options'], $data['dp_options']);
			$crm_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
		}
		
		$this->getTemplate()->display('crm', $data);
 */
	}
	
	function getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $campaign_options, $lead_options, $dp_options)
	{
		$audit_text = '';
			
		if (!empty($dial_num)) $audit_text .= "Dial number=$dial_num;";
		if (!empty($camid)) {
			$txt = isset($campaign_options[$camid]) ? $campaign_options[$camid] : $camid;
			$audit_text .= "Campaign=" . $txt . ";";
		}
		if (!empty($leadid)) {
			$txt = isset($lead_options[$leadid]) ? $lead_options[$leadid] : $leadid;
			$audit_text .= "Lead=" . $txt . ";";
		}
		
		if (!empty($disp_code)) {
			$txt = '';
			//$txt = isset($data['dp_options'][$disp_code]) ? $data['dp_options'][$disp_code] : $disp_code;
			if (is_array($dp_options)) foreach ($dp_options as $dp) { 
				if ($dp->disposition_id == $disp_code) {
				
					if ($dp->campaign_id == '0000') $txt = 'System';
					else if ($dp->campaign_id == '1111') $txt = 'General';
					else $txt = $dp->campaign_title;
					$txt = $txt . ' - ' . $dp->title;
				}
			}
			
			if (empty($txt)) $txt = $disp_code;
			$audit_text .= "Disposition=" . $txt . ";";
		}
		
		return $audit_text;
	}
	
	function actionRecordcount()
	{
		include('model/MCrm.php');
		include('model/MCampaign.php');
		include('lib/DateHelper.php');
		include('lib/Pagination.php');
		$crm_model = new MCrm();
		$campaign_model = new MCampaign();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();
		$camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
		$leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . 
			"&act=recordcount&camid=$camid&leadid=$leadid&sdate=$dateinfo->sdate&edate=$dateinfo->edate&".
			"stime=$dateinfo->stime&etime=$dateinfo->etime");
		
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
			$pagination->num_records = $crm_model->numCrmRecordCount($camid, $leadid, $dateinfo);	
		}
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		$data['records'] = $pagination->num_records > 0 ? 
			$crm_model->getCrmRecordCount($camid, $leadid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;

		$data['pagination'] = $pagination;
		$data['campaign_options'] = $campaign_model->getCampaignSelectOptions();
		$data['lead_options'] = $campaign_model->getLeadSelectOptions();

		if ($pagination->num_records > 0) {
			include('model/MCrmDisposition.php');
			$crm_dp_model = new MCrmDisposition();
			$data['dp_options'] = $crm_dp_model->getDispositions();
			$data['total_crm_records'] = $crm_model->getTotalCrmRecordCount($camid, $leadid, $dateinfo);
		}
		$data['camid'] = $camid;
		$data['leadid'] = $leadid;
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Call Disposition';
		$data['side_menu_index'] = 'reports';
		$this->getTemplate()->display('crm_report_record_count', $data);
	}


	function actionChurn()
	{
		include('model/MCrm.php');
		$crm_model = new MCrm();
		
		$dial_num = isset($_REQUEST['dial_num']) ? trim($_REQUEST['dial_num']) : "";
		$camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
		$leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
		$disp_code = isset($_REQUEST['disp_code']) ? trim($_REQUEST['disp_code']) : "";
		$num_selected_req = isset($_POST['num_selected']) ? trim($_POST['num_selected']) : 0;
		
		if (empty($dial_num) && empty($camid) && empty($leadid) && empty($disp_code)) exit;
		
		$data['num_selected'] = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);
		
		if ($data['num_selected'] == 0) exit;
		$data['pageTitle'] = 'Churn CRM Record(s)';
				
		if (isset($_POST['churn']) && $num_selected_req == $data['num_selected']) {
			if ($crm_model->churnCrmRecords($dial_num, $camid, $leadid, $disp_code, $num_selected_req)) {
				$data['message'] = 'Record(s) churned successfully !!';
				$data['msgType'] = 'success';
				
				include('model/MCrmDisposition.php');
				$crm_dp_model = new MCrmDisposition();
				include('model/MCampaign.php');
				$campaign_model = new MCampaign();
				
				$campaign_options = $campaign_model->getCampaignSelectOptions();
				$lead_options = $campaign_model->getLeadSelectOptions();
				$dp_options = $crm_dp_model->getDispositions();

				$audit_text = $this->getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $campaign_options, $lead_options, $dp_options);
				$crm_model->addToAuditLog('CRM Churn', 'U', "", $audit_text);
				
			} else {
				$data['message'] = 'Failed to churn record(s) !!';
				$data['msgType'] = 'error';
				//$data['refreshParent'] = false;
			}
			$this->getTemplate()->display_popup('popup_message', $data);			
			exit;
		}
		
		$data['dial_num'] = $dial_num;
		$data['camid'] = $camid;
		$data['leadid'] = $leadid;
		$data['disp_code'] = $disp_code;

		$data['request'] = $this->getRequest();
		
		$this->getTemplate()->display_popup('crm_churn', $data);
	}
	
	
	function downloadCRM($crm_model, $title, $template, $dial_num, $camid, $leadid, $disp_code)
	{
		//path also used in model function
		$file_name = $crm_model->getCdrCsvSavePath() . 'crm_records.csv';

		$is_success = $crm_model->prepareCRMFile($dial_num, $camid, $leadid, $disp_code, $file_name);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			$dl_helper->download_file('', "Campaign,Lead,Dial Number,First name,Last name,Dial attempt,Last disposition,Last dial time\n");
		}
	}

	function actionDetails()
	{
		include('model/MCrm.php');
		include('lib/Pagination.php');
		$crm_model = new MCrm();
		$pagination = new Pagination();
		
		$fields = array('record_id', 'dial_number', 'campaign_id', 'lead_id', 'title', 'first_name', 'middle_name', 'last_name', 'DOB', 'house_no',
			'street', 'landmarks', 'city', 'state', 'zip', 'country', 'home_phone', 'office_phone', 'mobile_phone', 'other_phone', 'fax',
			'email', 'dial_attempted', 'last_dial_time', 'last_disposition_id', 'last_callid');

		$sql_cond = '';
		$url_cond = '';
		
		foreach ($fields as $fld) {
			$val = isset($_REQUEST[$fld]) ? trim($_REQUEST[$fld]) : "";
			if (!empty($val)) {
				if (!empty($sql_cond)) $sql_cond .= " AND ";
				$sql_cond .= "$fld='$val'";
				$url_cond .= '&' . $fld . '=' . urlencode($val);
			}
		}
		
		if (empty($sql_cond)) {
			$num_records = 0;
		} else {
			$num_records = $crm_model->numCrmRecordsByCond($sql_cond);
		}

		
		if ($num_records <= 1) {
			$date['errMsg'] = '';
			if ($num_records == 1) {

				include('model/MCrmDisposition.php');
				$dp_model = new MCrmDisposition();
				
				$records = $crm_model->getCrmRecordDetailByCond($sql_cond, 0, 1);
				$data['profile'] = $records[0];
				
				$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=details" . $url_cond);
				$pagination->num_records = $crm_model->numCrmDispositions($data['profile']->record_id);
				$pagination->rows_per_page = 10;
				$data['records'] = $pagination->num_records > 0 ? 
					$crm_model->getCrmDispositions($data['profile']->record_id, $pagination->getOffset(), $pagination->rows_per_page) : null;
				$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;
				$data['dp_options'] = $dp_model->getDispositionOptions($data['profile']->campaign_id);
				$data['schedule_dial'] = $crm_model->getScheduleDial($data['profile']->campaign_id, $data['profile']->record_id);
				
				$data['pagination'] = $pagination;
				
				$crm_model->addToAuditLog('CRM Details', 'V', "", 'Profile='. $data['profile']->first_name . ' ' . $data['profile']->last_name);
				
			} else {
				$data['errMsg'] = 'No record found !!';
			}
			$data['pageTitle'] = "CRM Profile Details";
			$data['request'] = $this->getRequest();
			$data['reportHeader'] = true;
			
			$this->getTemplate()->display('crm_record', $data);
		} else {

			include('model/MCampaign.php');
			include('model/MCrmDisposition.php');

			$campaign_model = new MCampaign();
			$crm_dp_model = new MCrmDisposition();

			$data['pageTitle'] = 'CRM Record(s)';
			$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=details" . $url_cond);
			$pagination->num_records = $num_records;

			$data['records'] = $crm_model->getCrmRecordsByCond($sql_cond, $pagination->getOffset(), $pagination->rows_per_page);
			$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;

			$data['pagination'] = $pagination;
			$data['dial_num'] = '';
			$data['disp_code'] = '';
			$data['camid'] = '';
			$data['leadid'] = '';
			$data['request'] = $this->getRequest();
			$data['campaign_options'] = $campaign_model->getCampaignSelectOptions();
			$data['lead_options'] = $campaign_model->getLeadSelectOptions();
			$data['dp_options'] = $crm_dp_model->getDispositions();

			$data['is_popup'] = true;
			
			//$crm_model->addToAuditLog('CRM Record(s)', 'V', "", "");
			
			$this->getTemplate()->display_popup('crm', $data);
		}
		exit;
	}
	
	function actionDownloadcrmvoice()
	{
		include('model/MCrm.php');
		include('model/MCampaign.php');
		
		$crm_model = new MCrm();
		$campaign_model = new MCampaign();

		$dial_num = isset($_REQUEST['dial_num']) ? trim($_REQUEST['dial_num']) : "";
		$camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
		$leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
		$disp_code = isset($_REQUEST['disp_code']) ? trim($_REQUEST['disp_code']) : "";
		
		$num_records = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);
	
		if ($num_records > 0 && isset($_POST['fname1'])) {
			$rows = $crm_model->getCrmRecordsForVoice($dial_num, $camid, $leadid, $disp_code);
			//var_dump($rows);
			$voice_files = array();
			if (is_array($rows)) {
				foreach ($rows as $call) {
					$file_timestamp = substr($call->last_callid, 0, 10);
					$yyyy = date("Y", $file_timestamp);
					$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
					$sound_file_gsm = $this->getTemplate()->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $call->last_callid . ".gsm";
					//echo $sound_file_gsm;
					if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
						$newname = '';
						if (!empty($_POST['fname1'])) {
							if ($_POST['fname1'] == 'DT') {if (!empty($call->last_dial_time)) $newname .= date("Y_m_d_H_i_s", $call->last_dial_time) . '-';}
							else if ($_POST['fname1'] == 'CM') {if (!empty($call->cp_title)) $newname .= str_replace(array(' '), "_", $call->cp_title) . '-';}
							else if ($_POST['fname1'] == 'LD') {if (!empty($call->lp_title)) $newname .= str_replace(array(' '), "_", $call->lp_title) . '-';}
							else if ($_POST['fname1'] == 'DN') {if (!empty($call->dial_number)) $newname .= $call->dial_number . '-';}
						}
						if (!empty($_POST['fname2'])) {
							if ($_POST['fname2'] == 'DT') {if (!empty($call->last_dial_time)) $newname .= date("Y_m_d_H_i_s", $call->last_dial_time) . '-';}
							else if ($_POST['fname2'] == 'CM') {if (!empty($call->cp_title)) $newname .= str_replace(array(' '), "_", $call->cp_title) . '-';}
							else if ($_POST['fname2'] == 'LD') {if (!empty($call->lp_title)) $newname .= str_replace(array(' '), "_", $call->lp_title) . '-';}
							else if ($_POST['fname2'] == 'DN') {if (!empty($call->dial_number)) $newname .= $call->dial_number . '-';}
						}
						if (!empty($_POST['fname3'])) {
							if ($_POST['fname3'] == 'DT') {if (!empty($call->last_dial_time)) $newname .= date("Y_m_d_H_i_s", $call->last_dial_time) . '-';}
							else if ($_POST['fname3'] == 'CM') {if (!empty($call->cp_title)) $newname .= str_replace(array(' '), "_", $call->cp_title) . '-';}
							else if ($_POST['fname3'] == 'LD') {if (!empty($call->lp_title)) $newname .= str_replace(array(' '), "_", $call->lp_title) . '-';}
							else if ($_POST['fname3'] == 'DN'){if (!empty($call->dial_number))  $newname .= $call->dial_number . '-';}
						}
						if (!empty($_POST['fname4'])) {
							if ($_POST['fname4'] == 'DT') {if (!empty($call->last_dial_time)) $newname .= date("Y_m_d_H_i_s", $call->last_dial_time) . '-';}
							else if ($_POST['fname4'] == 'CM') {if (!empty($call->cp_title)) $newname .= str_replace(array(' '), "_", $call->cp_title) . '-';}
							else if ($_POST['fname4'] == 'LD') {if (!empty($call->lp_title)) $newname .= str_replace(array(' '), "_", $call->lp_title) . '-';}
							else if ($_POST['fname4'] == 'DN') {if (!empty($call->dial_number)) $newname .= $call->dial_number . '-';}
						}
						
						$newname .= $call->last_callid . '.gsm';
						$voice_files[] = array($sound_file_gsm, $newname);
					}
				}
			}

			if (count($voice_files) > 0) {
				$zip_file_path = '/tmp/crm-voices.zip';//$this->getTemplate()->voice_logger_path . 'crm-voices.zip';
				if (file_exists($zip_file_path)) unlink($zip_file_path);
				if ($this->create_zip($voice_files, $zip_file_path, true)) {



					$new_file_name = 'crm-voices.zip';
					$mtime = filemtime($zip_file_path);
					$size = intval(sprintf("%u", filesize($zip_file_path)));
		
					if (intval($size + 1) > $this->return_bytes(ini_get('memory_limit')) && intval($size * 1.5) <= 1073741824) { //Not higher than 1GB
						ini_set('memory_limit', intval($size * 1.5));
					}

					@apache_setenv('no-gzip', 1);
					@ini_set('zlib.output_compression', 0);
					// Maybe the client doesn't know what to do with the output so send a bunch of these headers:
					header("Content-type: application/force-download");
					header('Content-Type: application/octet-stream');
					if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE") != false) {
						header("Content-Disposition: attachment; filename=" . urlencode($new_file_name) . '; modification-date="' . date('r', $mtime) . '";');
					} else {
						header("Content-Disposition: attachment; filename=\"" . $new_file_name . '"; modification-date="' . date('r', $mtime) . '";');
					}
					// Set the length so the browser can set the download timers
					header("Content-Length: " . $size);
					// If it's a large file we don't want the script to timeout, so:
					//set_time_limit(300);
					// If it's a large file, readfile might not be able to do it in one go, so:
		
					$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
					if ($size > $chunksize) {
						$handle = fopen($zip_file_path, 'rb');
						$buffer = '';
						while (!feof($handle)) {
							$buffer = fread($handle, $chunksize);
							echo $buffer;
							ob_flush();
							flush();
						}
						fclose($handle);
					} else {
						readfile($zip_file_path);
					}
				}
				
				include('model/MCrmDisposition.php');
				$crm_dp_model = new MCrmDisposition();
				$campaign_options = $campaign_model->getCampaignSelectOptions();
				$lead_options = $campaign_model->getLeadSelectOptions();
				$dp_options = $crm_dp_model->getDispositions();

				$audit_text = $this->getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $campaign_options, $lead_options, $dp_options);
				$crm_model->addToAuditLog('CRM Voice Files', 'L', "", $audit_text);
				exit;
			} else {
				$data['errMsg'] = 'No voice file found !!';
			}
		}
		
		if ($num_records > 0) {
			$data['num_records'] = $num_records;
			$data['pageTitle'] = 'Download Voice File(s)';
			$data['request'] = $this->getRequest();

			$data['dial_num'] = $dial_num;
			$data['camid'] = $camid;
			$data['leadid'] = $leadid;
			$data['disp_code'] = $disp_code;
			$data['errType'] = 1;
			$this->getTemplate()->display_popup('crm_voice_download', $data);
		}
		
		
	}

	function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
	
	function create_zip($files = array(),$destination = '',$overwrite = false) {

		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
/*
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	*/
	//if we have good files...
	if(count($files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		$i = 0;
		foreach($files as $file) {
			$i++;
			$zip->addFile($file[0],$file[1]);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
	}
	
		
	function actionSaveprofile()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		
		if (isset($_POST['first_name']) & !empty($record_id)) {
			include('model/MCrm.php');
			$crm_model = new MCrm();
		
			$info = new stdClass();
			$info->first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : "";
			$info->last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : "";
			$info->middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : "";
			$info->title = isset($_POST['title']) ? trim($_POST['title']) : "";
			$info->DOB = isset($_POST['DOB']) ? trim($_POST['DOB']) : "";
			
			if ($crm_model->svaePersonalProfile($record_id, $info)) {
				$crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id", "Personal info updated;Name=$info->first_name $info->last_name;Middle Name=$info->middle_name;Title=$info->title;DOB=$info->DOB;");
				$msg = 1;
			}
		}

		echo $msg;
	}
	
	function actionDelschedule()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		$campaign_id = isset($_REQUEST['campaign_id']) ? trim($_REQUEST['campaign_id']) : "";
		if (isset($_POST['campaign_id']) & !empty($record_id)) {
			include('model/MCrm.php');
			$crm_model = new MCrm();
		
			$info = new stdClass();
			if ($crm_model->delScheduleDial($campaign_id, $record_id)) {
				$msg = 1;
				$crm_model->addToAuditLog('CRM Profile', 'D', "Record #=$record_id", "Callback schedule deleted;");
			}
		}

		echo $msg;
	}
	
	function actionSaveaddress()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		if (isset($_POST['house_no']) & !empty($record_id)) {
			include('model/MCrm.php');
			$crm_model = new MCrm();
		
			$info = new stdClass();
			$info->house_no = isset($_POST['house_no']) ? trim($_POST['house_no']) : "";
			$info->street = isset($_POST['street']) ? trim($_POST['street']) : "";
			$info->landmarks = isset($_POST['landmarks']) ? trim($_POST['landmarks']) : "";
			$info->city = isset($_POST['city']) ? trim($_POST['city']) : "";
			$info->state = isset($_POST['state']) ? trim($_POST['state']) : "";
			$info->zip = isset($_POST['zip']) ? trim($_POST['zip']) : "";
			$info->country = isset($_POST['country']) ? trim($_POST['country']) : "";
			
			if ($crm_model->svaePersonalAddress($record_id, $info)) {
				$crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id;Address updated", "House=$info->house_no;Street=$info->street;Landmarks=$info->landmarks;City=$info->city;State=$info->state;ZIP=$info->zip;Country=$info->country;");
				$msg = 1;
			}
		}

		echo $msg;
	}
	
	function actionSavecontact()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		if (isset($_POST['home_phone']) & !empty($record_id)) {
			include('model/MCrm.php');
			$crm_model = new MCrm();
		
			$info = new stdClass();
			$info->home_phone = isset($_POST['home_phone']) ? trim($_POST['home_phone']) : "";
			$info->office_phone = isset($_POST['office_phone']) ? trim($_POST['office_phone']) : "";
			$info->mobile_phone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : "";
			$info->other_phone = isset($_POST['other_phone']) ? trim($_POST['other_phone']) : "";
			$info->fax = isset($_POST['fax']) ? trim($_POST['fax']) : "";
			$info->email = isset($_POST['email']) ? trim($_POST['email']) : "";
			
			if ($crm_model->svaeContackDetails($record_id, $info)) {
				$crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id;Contact details updated", "Home Phone=$info->home_phone;Office Phone=$info->office_phone;Mobile Phone=$info->mobile_phone;Other Phone=$info->other_phone;Fax=$info->fax;Email=$info->email;");
				$msg = 1;
			}
		}

		echo $msg;
	}
	
	function actionSavedisposition()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		$agent_id = UserAuth::getCurrentUser();
		
		
		if (isset($_POST['disposition']) & !empty($record_id)) {
			include('model/MCrm.php');
			$crm_model = new MCrm();

			$records = $crm_model->getCrmRecordDetailByCond("record_id='$record_id'");
			
			if (is_array($records) && count($records) == 1) {
				
				$atext = '';
				$record = $records[0];
				$note = isset($_REQUEST['note']) ? trim($_REQUEST['note']) : "";
				$chk_callback = isset($_REQUEST['chk_callback']) ? trim($_REQUEST['chk_callback']) : 'N';

				if ($chk_callback == 'Y') {

					$days = isset($_REQUEST['days']) ? trim($_REQUEST['days']) : 0;
					$hrs = isset($_REQUEST['hrs']) ? trim($_REQUEST['hrs']) : 0;
					$min = isset($_REQUEST['min']) ? trim($_REQUEST['min']) : 0;
					$clk_date = isset($_REQUEST['clk_date']) ? trim($_REQUEST['clk_date']) : '';
					$clk_time = isset($_REQUEST['clk_time']) ? trim($_REQUEST['clk_time']) : '';
					$clktime = empty($clk_date) ? 0 : strtotime("$clk_date $clk_time");

					if (!empty($days) || !empty($hrs) || !empty($min) || !empty($clktime)) {
						if ($_POST['agent_opt'] != 'S') $agent_id = '';
						if ($crm_model->svaeScheduleDial($record->campaign_id, $record_id, $days, $hrs, $min, $clktime, $record->dial_number, $agent_id)) {
							$msg = 1;
							$atext .= "Callback schedule added;";
						}
					}
				}
				
				if ($crm_model->svaeDisposition($record->last_callid, $record_id, $_POST['disposition'], $note, $agent_id, $msg)) {
					$msg = 1;
					$atext .= "Disposition added;";
					if (!empty($note)) $atext .= "Note added;";
				}
				
				if ($msg == 1) {
					$crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id", $atext);
				}
				
			}
		}
		
		echo $msg;
	}
	
	function is_valid_data() {
		
	}
	
}
