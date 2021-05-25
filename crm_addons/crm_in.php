<?php

class Crm_in extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()	{
		include('model/MCrmIn.php');
		include('model/MSkillCrmTemplate.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$crm_model = new MCrmIn();
		$crm_dp_model = new MSkillCrmTemplate();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details(true);
		$account_id = isset($_REQUEST['account_id']) ? trim($_REQUEST['account_id']) : "";
		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : "";

		$data['pageTitle'] = 'CRM-Inbound Record(s)';

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&account_id=$account_id&dcode=$dcode");

		$pagination->num_records = $crm_model->numCrmRecords($account_id, $dcode, $dateinfo);

		if (isset($_REQUEST['download']) && $pagination->num_records>0) {
			$this->downloadCRM($crm_model, $data['pageTitle'],  $this->getTemplate(), $account_id, $dcode);
			exit;
		}

		$data['records'] = $pagination->num_records > 0 ? 
			$crm_model->getCrmRecords($account_id, $dcode, $pagination->getOffset(), $pagination->rows_per_page, $dateinfo) : null;
		$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;

		$data['dateinfo'] = $dateinfo;
		$data['pagination'] = $pagination;
		$data['account_id'] = $account_id;
		$data['dcode'] = $dcode;
		$data['request'] = $this->getRequest();
		$data['dp_options'] = $crm_dp_model->getDispositionSelectOptions();

		$data['topMenuItems'] = array(array('href'=>"task=crm_in&act=upload", 'img'=>'add.png', 'label'=>'Upload records', 'class'=>'upload-rec'));
		
		$this->getTemplate()->display('crm_in', $data);
	}

	function downloadCRM($crm_model, $title, $template, $account_id, $dcode)
	{
		//path also used in model function
		$file_name = $crm_model->getCdrCsvSavePath() . 'crm_in_records.csv';
		$is_success = $crm_model->prepareCRMFile($account_id, $dcode, $file_name);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			$dl_helper->download_file('', "Account ID,Title,First Name,Middle Name,Last Name,DOB,House No.,Street,Landmarks,City,State,Zip,Country,Home Phone,Office Phone,Mobile Phone, Other Phone,Fax,Email,Priority Label,Last Callid,TPIN,Activation Date,Status\n");
		}
	}
	
	function actionUpload()
	{
		include('model/MCrmIn.php');
		$crm_model = new MCrmIn();

		$headings = array(
			'record_id'=>'Record ID',
			'account_id'=>'Account ID',
			'title'=>'Title',
			'first_name'=>'First Name',
			'middle_name'=>'Middle Name',
			'last_name'=>'Last Name',
			'DOB'=>'Date of Birth',
			'house_no'=>'House No.',
			'street'=>'Street',
			'landmarks'=>'Landmarks',
			'city'=>'City',
			'state'=>'State',
			'zip'=>'Zip',
			'country'=>'Country',
			'home_phone'=>'Home Phone',
			'office_phone'=>'Office Phone',
			'mobile_phone'=>'Mobile Phone',
			'other_phone'=>'Other Phone',
			'fax'=>'Fax',
			'email'=>'Email',
			'priority_label'=>'Priority Label',
			'TPIN'=>'TPIN',
			'status'=>'Status'
		);
		
		$data['heading'] = $this->getExcelHeading($headings);
		
		$request = $this->getRequest();
		$errMsg = '';
		if ($request->isPost()) {
			include('lib/FileManager.php');
			$errMsg = $this->getValidationMsg($data['heading']);
			
			if (empty($errMsg)) {
				$resp = FileManager::check_file_for_upload('number', 'csv');
				//var_dump($resp);
				if ($resp == FILE_EXT_INVALID) {
					$errMsg = 'Please select a CSV file';
				} else if ($resp == FILE_UPLOADED) {
					$is_number_file_uploaded = true;
					$file_type = 'csv';
				}
				//var_dump($is_number_file_uploaded);
				if ($is_number_file_uploaded) {
					$isUpdate = $this->processRecords($headings, $data['heading'], $file_type, $crm_model);
					//if (!is_array($numbers)) {
						//$errMsg = $numbers;
					//}
					if (strlen($isUpdate) == 0) {
						$data['message'] = 'Record(s) uploaded successfully !!';
						$data['msgType'] = 'success';
						$data['refreshParent'] = true;
					} else {
						$data['message'] = 'Failed to upload record(s) !!';
						$data['msgType'] = 'error';
						//$data['refreshParent'] = false;
					}
					$this->getTemplate()->display_popup('popup_message', $data);			
					exit;
				}
			}
			
			/*
			if (empty($errMsg) && $data['num_selected'] == $num_selected_req) {
				$isUpdate = false;
				
				$ltext = '';
				$ltext .= 'Title='.$lead->title . ';';
				$ltext .= 'Reference='.$lead->reference . ';';
				$ltext .= 'Country code='.$lead->country_code . ';';
				$lead_id = $campaign_model->addLead($lead, $ltext);
				if (!empty($lead_id)) {
					$isUpdate = true;
					$campaign_model->addLeadNumberFromCRM($lead_id, $dial_num, $camid, $leadid, $disp_code);
					$campaign_model->updateLeadCount($lead_id);
					$campaign_model->addToAuditLog('Lead', 'A', "Lead=$lead_id", "Numbers:$num_selected_req");
				}
				
				if ($isUpdate) {
					$data['message'] = 'Lead added successfully !!';
					$data['msgType'] = 'success';
				} else {
					$data['message'] = 'Failed to add lead !!';
					$data['msgType'] = 'error';
				}
				$this->getTemplate()->display_popup('popup_message', $data);			
				exit;
			}
			*/
		} else {
			//$lead = $this->getInitialLead($lid, $campaign_model);
		}
		
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['headings'] = $headings;
		$data['pageTitle'] = 'Upload CRM-Inbound Record(s)';
		$data['errType'] = 1;
		$this->getTemplate()->display_popup('crm_in_upload_form', $data);
	}
	
	function returnResponse($fp, $msg)
	{
		fclose($fp);
		return $msg;
	}
	
	function processRecords($headings_array, $heading, $num_file_type, $crm_model)
	{
		$number = array();
	
		$file = $_FILES['number']['tmp_name'];
		$fileName = $_FILES['number']['name'];
		//echo 'asd';
		$row = 1;
		//$discard = array("(", ")", "-", " ");
		$fp = fopen($file, "r");
		$db_cols = array();
		$count_num = 0;
		
		if ($num_file_type == 'csv') {
			while (($record = fgetcsv($fp, 5000, ",")) !== FALSE) {
				//var_dump($record); exit;
				$num = is_array($record) ? count($record) : 0;
				if ($row == 1) {
					//var_dump($heading);
					//var_dump($record);
					for ($c=0; $c < $num; $c++) {
						$hd = trim($record[$c]);
						foreach ($headings_array as $hkey => $hval) {
							if (!empty($hd) && $hd == $heading->{$hkey . '_heading'}) $db_cols[$hkey] = $c;
						}
					}
					
					if (count($db_cols) == 0) return $this->returnResponse($fp, "No matching data found !!");
				} else {
					
					if (count($db_cols) > 0 && is_array($record))	{
						
						$isEdit = $crm_model->addCRMRecord($db_cols, $record);
						if ($isEdit) $count_num++;
						//return $this->returnResponse($fp, 'Failed to add record');

					} else {
						return $this->returnResponse($fp, "Invalid excel format");
					}
				}
				$row++;
			}
		}
		//var_dump($num_file_type);
		
		if ($count_num > 0) {
			$crm_model->addToAuditLog('CRM-Inbound', 'A', "Numbers:$count_num", "");
			$hf = fopen("temp/heading_crm_in.txt", "w");
			fwrite($hf, serialize($heading));
			fclose($hf);
			
			return $this->returnResponse($fp, '');
		}
		
		fclose($fp);
		return 'Failed to add record';
	}
	
	function getValidationMsg($headings)
	{
		foreach ($headings as $val) {
			if (!empty($val)) return '';
		}
		
		return 'Please provide column mappings';
	}
	
	function getExcelHeading($headings)
	{
		$data = new stdClass();
		$hkey = '';
		foreach ($headings as $hkey=>$hlabel) {
			$data->{$hkey . '_heading'} = '';
		}
		
		$heading_file = "temp/heading_crm_in.txt";

		if (isset($_POST[$hkey . '_heading'])) {
			foreach ($headings as $hkey=>$hlabel) {
				$data->{$hkey . '_heading'} = isset($_POST[$hkey.'_heading']) ? trim($_POST[$hkey . '_heading']) : '';
			}
		} else if (file_exists($heading_file)) {
			$hf = fopen($heading_file, "r");
			$heading = fread($hf, filesize($heading_file));
			fclose($hf);
		
			//$rec_head = explode(",", $heading);
			$rec_head = unserialize($heading);

			if (!empty($rec_head)) {
				foreach ($headings as $hkey=>$hlabel) {
					$data->{$hkey . '_heading'} = isset($rec_head->{$hkey . '_heading'}) ? trim($rec_head->{$hkey . '_heading'}) : '';
				}
			}
		}
		//var_dump($data);
		return $data;
	}
	
	function actionDisposition()
	{
		include('model/MCrmIn.php');

		include('lib/Pagination.php');
		$crm_model = new MCrmIn();
		$pagination = new Pagination();

		$rid = isset($_REQUEST['rid']) ? trim($_REQUEST['rid']) : "";

		$record_details = $crm_model->getCRMRecord($rid);
		$data['pageTitle'] = 'CRM-Inbound Disposition(s)';
		
		if (!empty($record_details)) {
			$data['pageTitle'] = 'CRM-Inbound Disposition(s), Account Number - ' . $record_details->account_id;
			$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&rid=$rid");
			$pagination->num_records = $crm_model->numCrmDispositions($rid);
			$data['records'] = $pagination->num_records > 0 ? 
				$crm_model->getCrmDispositions($rid, $pagination->getOffset(), $pagination->rows_per_page) : null;
			$pagination->num_current_records = is_array($data['records']) ? count($data['records']) : 0;
			$data['pagination'] = $pagination;
			$data['request'] = $this->getRequest();
		} else {
			$data['records'] = null;
		}
		
		$this->getTemplate()->display_popup('crm_in_disposition_history', $data);
	}
	
	function actionDetails()
	{
		include('model/MCrmIn.php');
		include('lib/Pagination.php');
		$crm_model = new MCrmIn();
		$pagination = new Pagination();

		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$param = isset($_REQUEST['param']) ? trim($_REQUEST['param']) : '';
		$num_profile = 0;
		$display_disposition = false;
		$data['pageTitle'] = "Skill CRM Profile Details";
		$date['errMsg'] = '';

		include('crm_in_data.php');
		$crm_info = get_api_data($param);
/*
		echo "<pre>";
		print_r($crm_info);
		echo "</pre>";
		*/
		$data['errMsg'] = $crm_info->error;

		if (isset($crm_info->page_title) && !empty($crm_info->page_title)) {
			$data['pageTitle'] = $crm_info->page_title;
		}
		
		if (empty($data['errMsg'])) {

			if (!empty($crm_info->section)) $num_profile = 1;
			if ($num_profile == 1) {
				if (!empty($crm_info->crm_record_id) && !empty($crm_info->template_id) && !empty($crm_info->callid)) {
					$data['dispositioninfo'] = $crm_model->getCRMLogByCallID($crm_info->callid);
					if (!empty($data['dispositioninfo'])) {
						$display_disposition = true;
					}
				}

				$data['pagination'] = $pagination;
				$data['url_cond'] = 'param=' . $param;
				$data['crm_model'] = $crm_model;
				$data['crm_info'] = $crm_info;
				if ($display_disposition) {
					$data['group_options'] = $template_model->getDispositionGroupOptions($crm_info->template_id);
					$data['dp_options'] = $template_model->getDispositions($crm_info->template_id);
				}
				
				$accid = isset($crm_info->account_id) ? $crm_info->account_id : '';
				$crm_model->addToAuditLog($data['pageTitle'], 'V', "Account #=$accid", "CRM details");
				
				$data['request'] = $this->getRequest();
			} else {
				$data['errMsg'] = 'No profile found !!';
			}
		}
		$data['param'] = $param;
		$data['display_disposition'] = $display_disposition;
		$data['reportHeader'] = true;
		$this->getTemplate()->display('template_crm_record', $data);
	}
	
	function actionAddon()
	{
		include('model/MCrmIn.php');
		include('lib/Pagination.php');
		$crm_model = new MCrmIn();
		$pagination = new Pagination();
		
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$request = $this->getRequest();
		
		$callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
		$cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : '';
		$tempid = isset($_REQUEST['tempid']) ? trim($_REQUEST['tempid']) : '';
		
		$data['crm_info'] = $crm_model->getCRMRecord($cli);
		$display_disposition = empty($data['crm_info']) || empty($tempid) ? false : true;
		if ($display_disposition) {
			$data['group_options'] = $template_model->getDispositionGroupOptions($tempid);
			$data['dp_options'] = $template_model->getDispositions($tempid);
			
			if (is_array($data['dp_options'])) {
				$data['dispositioninfo'] = $crm_model->getCRMLogByCallID($callid);
				if (empty($data['dispositioninfo'])) {
					//$display_disposition = true;
					if ($crm_model->addDispositionLog($cli, $callid, UserAuth::getCurrentUser())) {
						$data['dispositioninfo'] = $crm_model->getCRMLogByCallID($callid);
					}
				}
				if (empty($data['dispositioninfo'])) {
					$display_disposition = false;
				}
			} else {
				$display_disposition = false;
			}
		}
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $request->getControllerName() . "&act=addon&".
			"cli=$cli&callid=$callid&tempid=" . $tempid);
		$pagination->rows_per_page = 10;
		$pagination->num_records = $crm_model->numCrmDispositions($cli);
		$records = $pagination->num_records > 0 ?
			$crm_model->getCrmDispositions($cli, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($records) ? count($records) : 0;
		
		$data['records'] = $records;
		$data['pagination'] = $pagination;
		$data['callid'] = $callid;
		$data['cli'] = $cli;
		$data['tempid'] = $tempid;
		$data['request'] = $request;
		$data['display_disposition'] = $display_disposition;
		$data['pageTitle'] = 'Disposition Information';
		$this->getTemplate()->display_only('skill_crm_disposition_addon', $data);
	}
	
	function actionAddtocrm()
	{
		include('model/MCrmIn.php');
		$crm_model = new MCrmIn();
		
		$callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
		$cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : '';
		$tempid = isset($_REQUEST['tempid']) ? trim($_REQUEST['tempid']) : '';
		
		$crm_info = $crm_model->getCRMRecord($cli);
		
		if (empty($crm_info)) {
			$crm_model->addCRMRecord(array('record_id'=>'record_id'), array('record_id'=>$cli));
		}
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=addon&callid=$callid&cli=$cli&tempid=$tempid");
		$this->getTemplate()->display('msg', array('pageTitle'=>'Skill CRM', 'isError'=>false, 'msg'=>'Record added successfully', 'redirectUri'=>$url));
	}
	
	function actionTpin()
	{
		$agent_id = isset($_POST['agentid']) ? trim($_POST['agentid']) : '';
		$callid = isset($_POST['callid']) ? trim($_POST['callid']) : '';
		$account_id = isset($_POST['accountid']) ? trim($_POST['accountid']) : '';

		include('model/MCrmIn.php');
		$crm_model = new MCrmIn();
		$crm_model->addToAuditLog('TPIN Generation', 'U', "Account #=$account_id", "Generate TPIN clicked");
		
		include('tpin_service.php');
		$status = tpin_generate($agent_id, $callid, $account_id);
		
		echo $status;
		exit;
	}

	function actionSearch()
	{
		$callid = isset($_POST['callid']) ? trim($_POST['callid']) : '';
		$account_id = isset($_POST['accountid']) ? trim($_POST['accountid']) : '';
		
		
		
		include('crm_in_data.php');
		$status = search_account($account_id, $callid);
		
		//if ($status == '200|OK') {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();
			$crm_model->addToAuditLog('Skill CRM', 'V', "Account #=$account_id", "Account searched");
		//}
		
		echo $status;
		exit;
	}
	
	function actionVerified()
	{
		$callid = isset($_POST['callid']) ? trim($_POST['callid']) : '';
		$account_id = isset($_POST['accountid']) ? trim($_POST['accountid']) : '';
		
		include('crm_in_data.php');
		$status = caller_verified($account_id, $callid, UserAuth::getCurrentUser());
		
		if ($status == '200|OK') {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();
			$crm_model->addToAuditLog('Skill CRM', 'U', "Account #=$account_id", "Account verified");
		}
		
		echo $status;
		exit;
	}
	
	function actionSavedisposition()
	{
		$msg = '-1';
		$record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
		$agent_id = UserAuth::getCurrentUser();
		
		
		if (isset($_POST['disposition']) & !empty($record_id)) {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();

			$callid = isset($_POST['callid']) ? trim($_POST['callid']) : "";
			$status = isset($_POST['status']) ? trim($_POST['status']) : "";
			//$records = $crm_model->getCrmRecordDetailByCond("record_id='$record_id'");
			
			//if (is_array($records) && count($records) == 1) {
			if (!empty($callid)) {
				
				//$record = $records[0];
				$note = isset($_POST['note']) ? trim($_POST['note']) : "";

				if ($crm_model->svaeDisposition($callid, $record_id, $_POST['disposition'], $note, $status, $agent_id, $msg)) {
					$msg = 1;
					$crm_model->addToAuditLog('Skill CRM', 'U', "", "Disposition updated");
				}
				
			}
		}

		echo $msg;
		exit;
	}
}
