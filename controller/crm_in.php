<?php

class Crm_in extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()	{
		/* include('model/MCrmIn.php');
		include('model/MSkillCrmTemplate.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$crm_model = new MCrmIn();
		$crm_dp_model = new MSkillCrmTemplate();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details(true);
		$account_id = isset($_REQUEST['account_id']) ? trim($_REQUEST['account_id']) : "";
		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : "";

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
		$data['dp_options'] = $crm_dp_model->getDispositionSelectOptions(); */

	    include('model/MSkillCrmTemplate.php');
	    $crm_dp_model = new MSkillCrmTemplate();
	    
	    $data['request'] = $this->getRequest();
	    $data['dp_options'] = $crm_dp_model->getDispositionSelectOptions(true);
	    $data['dataUrl'] = $this->url('task=get-home-data&act=crm_ininit');
		$data['pageTitle'] = 'CRM-Inbound Record(s)';
		$data['topMenuItems'] = array(array('href'=>"task=crm_in&act=upload", 'img'=>'fa fa-upload', 'label'=>'Upload records', 'class'=>'lightboxWIF'), array('href'=>"task=crm_in&act=record-details", 'img'=>'fa fa-table', 'label'=>'CRM-Inbound Record Details'));
		$this->getTemplate()->display('crm_in', $data);
	}

	function actionDispositionchildren()
	{
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
		$options = array();
		if (!empty($did)) {
			include('model/MSkillCrmTemplate.php');
			$dc_model = new MSkillCrmTemplate();
	
			$options = $dc_model->getDispositionChildrenOptions('', $did);
		}
	
		echo json_encode($options);
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
	exit();
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
//exit("1");
		include('model/MCrmIn.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
        include('model/MEmail.php');
		$crm_model = new MCrmIn();
        $skill_model = new MSkill();
		$pagination = new Pagination();
		$email_model = new MEmail();

        $data['ticket_category'] = array();
        $ticket_category = $email_model->getTicketCategory('','A');
        if (!empty($ticket_category)){
            foreach ($ticket_category as $item){
                $data['ticket_category'][$item->category_id] = $item->title;
            }
        }

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
        $skills = $skill_model->getSkillsNamesArray("P");

        $data['errMsg'] = $crm_info->error;
        $data['callbacks'] = $crm_model->getAutoDialNumbers($crm_info->caller_id);
        $data['predictive_dial'] = in_array($crm_info->skill_name,$skills) ? TRUE : FALSE;

		if (isset($crm_info->page_title) && !empty($crm_info->page_title)) {
			$data['pageTitle'] = $crm_info->page_title;
		}
		
		if (empty($data['errMsg'])) {

			if (!empty($crm_info->section)) $num_profile = 1;
			if ($num_profile == 1) {
				$_crmRecordId = !empty($crm_info->crm_record_id) ? $crm_info->crm_record_id : '';
				$_crmRecordId = empty($_crmRecordId) && !empty($crm_info->data_record_id) ? $crm_info->data_record_id : $_crmRecordId;
				if (!empty($crm_info->template_id) && !empty($crm_info->callid)) {
					$data['dispositioninfo'] = $crm_model->getCRMLogByCallID($crm_info->callid, $_crmRecordId, $crm_info->agent_id, $crm_info->caller_auth_by);
					//var_dump($data['dispositioninfo']);exit;
					if (!empty($data['dispositioninfo'])) {
						$display_disposition = true;
					}
				}

				$data['pagination'] = $pagination;
				$data['url_cond'] = '&param=' . $param;
				$data['crm_model'] = $crm_model;
				$data['crm_info'] = $crm_info;
				if ($display_disposition) {
					$data['group_options'] = $template_model->getServiceTypeOptions($crm_info->template_id);
					$data['dp_options'] = $template_model->getDispositions($crm_info->template_id);
				}
				//GPrint($data['dp_options']);die;
				$accid = isset($crm_info->account_id) ? $crm_info->account_id : '';
				$crm_model->addToAuditLog($data['pageTitle'], 'V', "Account #=$accid", "CRM details");
				
				$data['request'] = $this->getRequest();
			} else {
				$data['errMsg'] = 'No profile found !!';
			}
		}
		if ($display_disposition) {
			$data['disposition_ids'] = $template_model->getDispositionPathArray($data['dispositioninfo']->disposition_id);
		} else {
			$data['disposition_ids'] = $template_model->getDispositionPathArray('');
		}
		
		$data['param'] = $param;
		
		if (!isset($crm_info->template_id)) {
			$param = unserialize(base64_decode($param));
			$template_id = isset($param['template_id']) ? $param['template_id'] : '';
		} else {
			$template_id = $crm_info->template_id;
		}
		
		$data['InCallSessionVars'] = $crm_model->getCallSessionVars($crm_info->callid);
		//print_r($data['InCallSessionVars']);
		$data['search_filters'] = $template_model->getFiltersBySection($template_id, '');
		//$data['crm_template'] = $template_model->getTemplateById($crm_info->template_id);
		$data['dc_model'] = $template_model;
        //var_dump($data['search_filters']);die;
		$data['display_disposition'] = $display_disposition;
		$data['reportHeader'] = true;

        $data['errMsg'] ? $this->getTemplate()->display_only('blank', []) :  $this->getTemplate()->display_crm('template_crm_record', $data);
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
		$data = isset($_POST['data']) ? $_POST['data'] : null;
	
		//var_dump($data);
                //echo "404|Not found|$callid|E";
		//exit;
		include('crm_in_data.php');
		$account_id = search_account($data, $callid);
	
		//if ($status == '200|OK') {
		if (!empty($account_id)) {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();
			$skill_crm_log = $crm_model->getCRMLogByCallID($callid);
            if (!empty($skill_crm_log->caller_auth_by)){
                $crm_model->updateSkillCrmLog($callid, '', $skill_crm_log->disposition_id, $skill_crm_log->note, '', $skill_crm_log->agent_id);
            }
			$crm_model->addToAuditLog('Skill CRM', 'V', "Account #=$account_id", "Account searched");
			$status = '200|OK';
		} else {
			$status = '404|No record found';
		}
	
		echo $status;
		exit;
	}
	
	function actionGet_section_data()
	{
		$request_params = array();
		if (isset($_POST)) {
			foreach ($_POST as $key => $val) {
				$request_params[$key] = $val;
			}
		} 
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		include('crm_in_data.php');
		
		$csection = $template_model->getSectionById($_POST['template_id'], $_POST['section_id']);
		//$fields = $template_model->getFieldsBySection($_POST['template_id'], $_POST['section_id']);
		
		$response_data = get_api_response($_POST['callid'], $csection, $request_params);
		
		if ($csection->section_type == 'G') {
			$i = 0;
			$columnCount = 0;
			$tabColumn = array();
			//$response_data = array_merge(array('column'=>))
			echo '<table class="report_table table">';
			foreach ($response_data as $rowid=>$grid_row) {
			    $columnCount = 0;
			    if ($rowid === "tab_id"){
			        if (is_array($grid_row)) {
			            foreach ($grid_row as $colKey=>$colVal) {
			                $tabColumn[$colKey] = $colVal;
			            }
			        }
			        continue;
			    }
				if ($i>0) {
					$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
				} else {
					$_class = 'report_row_head';
				}
				echo '<tr class="'.$_class.'">';
				if (is_array($grid_row)) {
					foreach ($grid_row as $colKey=>$colVal) {
					    if (!empty($tabColumn[$colKey])){
					        $tabUniqueId = $this->uniqueIdGenerate($tabColumn[$colKey]);
					        echo "<td class='cntr'><span class='contact-val'><a href='javascript:void(0)' class='add-contact tab-url' rel='".$tabColumn[$colKey]."' unique_id='".$tabUniqueId."' onclick='addTabContent(this);'>".$colVal."</a></span></td>";
					    }else {
					        echo '<td class="cntr">'.$colVal.'</td>';
					    }
						$columnCount++;
					}
				}
				echo '</tr>';
				$i++;
			}
			if ($i == 1){
			    echo '<tr class="report_row">';
			    echo '<td colspan="'.$columnCount.'" style="color:#BA6868; text-align: center;">No record found</td>';
			    echo '</tr>';
			}
			echo '</table>';
		}
		//var_dump($response_data);
		/*
		echo '<table class="report_table">
<tbody><tr class="report_row_head"><td class="cntr">Amount</td><td class="cntr">T. Type</td><td class="cntr">Narration</td><td class="cntr">Transaction
 Date</td></tr><tr class="report_row"><td class="cntr">'.rand(1, 9999).'.'.rand(10, 99).'</td><td class="cntr">D</td><td class="cntr">Pay to Mr. Salmon Ta</td><td class="cntr">2013-11-20
</td></tr><tr class="report_row_alt"><td class="cntr">12370.12</td><td class="cntr">C</td><td class="cntr">Deposit Chq # 23423</td><td class="cntr">2013-11-20
</td></tr></tbody></table>';
*/
		
		exit;
	}
	
	function uniqueIdGenerate($idPrefix=''){
	    $globalTime = mt_rand(101, 9999);
	    $myUniqueId = $idPrefix.'_'.$globalTime;
	
	    return $myUniqueId;
	}
	
	function actionVerified()
	{
		$callid = isset($_POST['callid']) ? trim($_POST['callid']) : '';
		$account_id = isset($_POST['accountid']) ? trim($_POST['accountid']) : '';
		$crm_record_id = isset($_POST['crm_record_id']) ? trim($_POST['crm_record_id']) : '';
		$caller_id = isset($_POST['caller']) ? trim($_POST['caller']) : '';
		$all_acc_ids = isset($_POST['all_ids']) ? trim($_POST['all_ids']) : '';
		
		include('crm_in_data.php');
		$status = caller_verified($account_id, $callid, UserAuth::getCurrentUser(), $crm_record_id, $caller_id, $all_acc_ids);
		
		if ($status == '200|OK') {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();
			$crm_model->addToAuditLog('Skill CRM', 'U', "Account #=$account_id", "Account verified");
		}
		
		echo $status;
		exit;
	}
	
	function actionSaveVarInCallSession()
	{
	    $callid = isset($_POST['callid']) ? trim($_POST['callid']) : "";
	    $var_name = isset($_POST['var_name']) ? trim($_POST['var_name']) : "";
	    $var_value = isset($_POST['var_value']) ? trim($_POST['var_value']) : "";
	    
	    if (!empty($callid) && !empty($var_name)) {
	        include('model/MCrmIn.php');
	        $crm_model = new MCrmIn();
	        $crm_model->svaeCallSessionVar($callid, $var_name, $var_value);
	    }
	    
	    echo "200 OK";
	}

    function actionSavedisposition()
    {
        $res = new stdClass();
        $res->message = "Failed to save";
        $res->type = FALSE;
        $msg = '-1';
        $record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
        $callid = isset($_POST['callid']) ? trim($_POST['callid']) : "";
        $agent_for_lead = $_POST['agent_id'] == "M" ? UserAuth::getCurrentUser() : "";
        $agent_id = UserAuth::getCurrentUser();
        $number = isset($_POST['number']) ? trim($_POST['number']) : "";
        $skill = isset($_POST['skill_id']) ? trim($_POST['skill_id']) : "";
        $dial_time = isset($_POST['dial_time']) ? trim($_POST['dial_time']) : "";
        $save_callback = isset($_POST['save_callback_request']) ? trim($_POST['save_callback_request']) : "";
        $open_crm_ticket = isset($_POST['open_crm_ticket']) ? trim($_POST['open_crm_ticket']) : "N";
        $disposition = isset($_POST['disposition']) ? $_POST['disposition'] : "";
        $note = isset($_POST['note']) ? $_POST['note'] : "";
        $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : "";
        $alternative_disposition = isset($_POST['alternative_disposition']) ? $_POST['alternative_disposition'] : "";
        $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : "";
        $account_id = isset($_POST['account_id']) ? $_POST['account_id'] : "";
        $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : "";

        $served_account = isset($_POST['served_account']) ? $_POST['served_account'] : "";

        if ((empty($disposition) && empty($alternative_disposition)) || empty($callid)) {
            echo $msg; exit();
        }

        if (strtoupper($open_crm_ticket) == "Y" && (empty($last_name) || empty($account_id) || empty($category_id)))
        {
            echo $msg; exit();
        }

        include('model/MCrmIn.php');
        $crm_model = new MCrmIn();

        $status = isset($_POST['status']) ? trim($_POST['status']) : "";
        $callid_a = explode('-', $callid);
        $callid = $callid_a[0];

        if (empty($disposition) && !empty($alternative_disposition))
        {
            $response = $crm_model->getDispositionIdByAlternativeDisposition($template_id, $alternative_disposition);
            if (empty($response))
            {
                $res->message = "No disposition found against {$alternative_disposition}";
                echo json_encode($res); exit();
            }
            $disposition = $response;
        }


        if ($crm_model->svaeDisposition($callid, $record_id, $disposition, $note, $status, $agent_id, $msg, $served_account)) {
            $call_back_save = 1;
            $msg = 1;
            if ($save_callback == "Y")
            {
                $call_back_save = $this->actionSavecallmeback($dial_time,$agent_for_lead,$skill,$number,$disposition);
            }
            if ($open_crm_ticket == "Y")
            {
                $crm_ticket_open = $this->actionOpenCrmTicket($skill,$number,$disposition,$note,$last_name,$account_id,$category_id);
            }

            $crm_model->addToAuditLog('Skill CRM', 'U', "", "Disposition updated");
        }
        $msg = ($msg && $call_back_save) || $crm_ticket_open  ? 1 : -1;

        echo $msg; exit;
    }

    function actionOpenCrmTicket($skill_id,$number,$disposition,$subject,$last_name,$account_id,$category_id)
    {
        include('model/MSkill.php');
        $msg = '-1';

        $crm_model = new MCrmIn();
        $skill_model = new MSkill();
        $skills = $skill_model->getSkillsNamesArray();

        $skill_id = array_search($skill_id,$skills);
        if (empty($skill_id) || empty($disposition) || empty($number) || empty($subject)|| empty($last_name) || empty($account_id) || empty($category_id))
        {
            die($msg);
        }

        $msg = $crm_model->openCrmTicket($skill_id,$number,$disposition,$subject,$last_name,$account_id,$category_id);
        if ($msg > 0) {
            $crm_model->addToAuditLog('CRM Ticket', 'A', "", "CRM ticket opened");
        }

        return $msg;
    }

	public function actionDelCallback()
    {
        include ('model/MCrmIn.php');
        $crm_model = new MCrmIn();
        $request = $this->getRequest();
        $id = $request->getRequest('id');
        $skill_id = $request->getRequest('sid');
        $number = $request->getRequest('number');
        $position = $request->getRequest('p');
        if (empty($id) || empty($skill_id) || empty($number) || empty($position))
        {
            echo -1; exit();
        }

        if ($crm_model->deleteCallMeBack($id,$skill_id,$number,$position))
        {
            echo 1; exit();
        }

        echo -1; exit();
    }

	function actionSavecallmeback($dial_time,$agent_id,$skill_id,$dial_number,$disposition)
	{
        include('model/MSkill.php');
        $msg = '-1';

        $crm_model = new MCrmIn();
        $skill_model = new MSkill();
        $skills = $skill_model->getSkillsNamesArray();

        $skill_id = array_search($skill_id,$skills);
        if (empty($skill_id) || empty($dial_number) || empty($dial_time) || !strtotime($dial_time) || !strtotime($dial_time) > time())
        {
            die($msg);
        }

        $msg = $crm_model->saveCallMeBack($dial_time,$agent_id,$skill_id,$dial_number,$disposition);
        if ($msg > 0) {
            $crm_model->addToAuditLog('Call Me Back', 'U', "", "Call Me Back Created");
        }

		return $msg;
	}
	
	function actionSavecustinfo()
	{
	    $msg = '-1';
	    $record_id = isset($_REQUEST['record_id']) ? trim($_REQUEST['record_id']) : "";
	    $caller_id = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
	    //$formData = json_decode($_POST['jsonData'], true);
	    $formData = json_decode((stripslashes($_POST['jsonData'])), true);
	
	    if (!empty($formData) && count($formData) > 0) {
	        include('model/MCrmIn.php');
	        $crm_model = new MCrmIn();
	        	
	        $logData = "";
	        $dbColmnVals = array();
	        foreach ($formData as $fRowData){
	            $dataKey = $fRowData['name'];
	            $dataVal = $fRowData['value'];
	            $dbColmnVals[$dataKey] = $dataVal;
	            $logData .= $dataKey."=".$dataVal.";";
	        }
	        	
	        if ($crm_model->addEditSkillCrm($dbColmnVals, $record_id, $caller_id)) {
	            $crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id", "Customer info updated;$logData");
	            $msg = 1;
	        }
	    }
	
	    echo $msg;
	}
	
	function actionRecordDetails(){
	    $data['dataUrl'] = $this->url('task=get-home-data&act=crmin-details');
	    $data['pageTitle'] = 'CRM-Inbound Record Details';
	    $data['topMenuItems'] = array(array('href'=>'task=crm_in&act=add-skill-crm', 'img'=>'fa fa-plus-square-o', 'label'=>'Add New CRM Record'), array('href'=>"task=crm_in", 'img'=>'fa fa-reply-all', 'label'=>'CRM-Inbound'));
	    $data['side_menu_index'] = 'home';
	    $data['smi_selection'] = 'crm_in_';
        $session_id = session_id();
        $download = md5($data['pageTitle'].$session_id);
        $dl_link = "&download=$download";
        $data['dataDlLink'] = $dl_link;

	    $this->getTemplate()->display('crm_in_records', $data);
	}
	
	function actionAddSkillCrm()
	{
	    $this->saveSkillCrm();
	}
	
	function actionEditSkillCrm()
	{
	    $rid = isset($_REQUEST['rid']) ? trim($_REQUEST['rid']) : '';
	    $this->saveSkillCrm($rid);
	}
	
	function saveSkillCrm($record_id='')
	{
	    include('model/MCrmIn.php');
	    $crm_model = new MCrmIn();
	
	    $request = $this->getRequest();
	    $errMsg = '';
	    $errType = 1;
        $data['zip_old'] = "";
        $data['fax_old'] = "";
	    if ($request->isPost()) {
	        $oldAccountId = isset($_POST['account_id_old']) ? trim($_POST['account_id_old']) : "";
            $data['zip_old'] = isset($_POST['zip_old']) ? trim($_POST['zip_old']) : "";
            $data['fax_old'] = isset($_POST['fax_old']) ? trim($_POST['fax_old']) : "";
	        $crmData = $this->getSubmittedCrmData();
	        $errMsg = $this->getValidationMsg($crmData);
	        
	        if (empty($crmData->account_id)) $errMsg = "Provide account id";
	        elseif (!preg_match("/^[0-9a-zA-Z_-]{3,20}$/", $crmData->account_id)) $errMsg = "Provide valid account id";
	        elseif (empty($crmData->mobile_phone) && empty($crmData->office_phone)) $errMsg = "Provide mobile phone or office phone";
	        
	        if (empty($errMsg)) {
	            $is_success = false;
	            if (empty($record_id)) {
	                $pageExist = $crm_model->getCrmRecordDetailByCond("account_id='$crmData->account_id'", 0, 1);
	                if ($pageExist != null && !empty($pageExist[0]->record_id)){
	                    $errMsg = 'Same account id already exist !!';
	                }else {
                        /*$existZipFax = $crm_model->getCrmRecordDetailByCond("zip='$crmData->zip' OR fax='$crmData->fax'", 0, 1);
                        if ($existZipFax[0]->zip == $crmData->zip){
                            $errMsg = 'Same Zip Code already exist !!';
                        }elseif ($existZipFax[0]->fax == $crmData->fax){
                            $errMsg = 'Same Fax already exist !!';
                        }else{*/
                            $logData = "";
                            $dbColmnVals = array();
                            foreach ($crmData as $formDataKey=>$formDataVal){
                                $dbColmnVals[$formDataKey] = $formDataVal;
                                $logData .= $formDataKey."=".$formDataVal.";";
                            }

                            if ($crm_model->addEditSkillCrm($dbColmnVals)) {
                                $crm_model->addToAuditLog('CRM Profile', 'A', "Account #=$crmData->account_id", "CRM Record Added;$logData");
                                $errMsg = 'CRM record added successfully !!';
                                $is_success = true;
                            } else {
                                $errMsg = 'Failed to add crm record !!';
                            }
                        //}
	                }
	            } else {
	                $readyToUpdate = true;
	                if ($crmData->account_id != $oldAccountId) {
	                    $pageExist = $crm_model->getCrmRecordDetailByCond("account_id='$crmData->account_id'", 0, 1);
	                    if ($pageExist != null && !empty($pageExist[0]->record_id)){
	                        $errMsg = 'Same account id already exist !!';
	                        $readyToUpdate = false;
	                    }
	                }
                    /*if ($readyToUpdate && ($crmData->zip != $data['zip_old'] || $crmData->fax != $data['fax_old'])) {
	                    $zipFaxCond = "";
	                    if (!empty($crmData->zip) && !empty($crmData->fax)){
                            $zipFaxCond = "zip='$crmData->zip' OR fax='$crmData->fax'";
                        }elseif (!empty($crmData->zip)){
                            $zipFaxCond = "zip='$crmData->zip'";
                        }elseif (!empty($crmData->fax)){
                            $zipFaxCond = "fax='$crmData->fax'";
                        }
                        if (!empty($zipFaxCond)) {
                            $existZipFax = $crm_model->getCrmRecordDetailByCond($zipFaxCond, 0, 1);

                            if (!empty($crmData->zip) && $crmData->zip != $data['zip_old'] && $existZipFax[0]->zip == $crmData->zip) {
                                $errMsg = 'Same Zip Code already exist !!';
                                $readyToUpdate = false;
                            } elseif (!empty($crmData->fax) && $crmData->fax != $data['fax_old'] && $existZipFax[0]->fax == $crmData->fax) {
                                $errMsg = 'Same Fax already exist !!';
                                $readyToUpdate = false;
                            }
                        }
                    }*/
	                if ($readyToUpdate){
	                    $logData = "";
	                    $dbColmnVals = array();
	                    foreach ($crmData as $formDataKey=>$formDataVal){
            	            $dbColmnVals[$formDataKey] = $formDataVal;
            	            $logData .= $formDataKey."=".$formDataVal.";";
            	        }
	                    
	                    if ($crm_model->addEditSkillCrm($dbColmnVals, $record_id)) {
	                        $crm_model->addToAuditLog('CRM Profile', 'U', "Record #=$record_id", "CRM Record Updated;$logData");
	                        $errMsg = 'CRM record updated successfully !!';
	                        $is_success = true;
	                    } else {
	                        $errMsg = 'No change found !!';
	                    }
	                }
	            }
	
	            if ($is_success) {
	                $errType = 0;
	                $url = $this->getTemplate()->url("task=".$this->getRequest()->getControllerName()."&act=record-details");
	                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
	            }
	        }
	    } else {
	        $crmData = $this->getInitialCrmData($record_id, $crm_model);
	        if (empty($crmData)) {
	            exit;
	        }
	        $oldAccountId = $crmData->account_id;
            $data['zip_old'] = $crmData->zip;
            $data['fax_old'] = $crmData->fax;
	    }

	    $data['crmData'] = $crmData;
	    $data['record_id'] = $record_id;
	    $data['accountId'] = $oldAccountId;
	    $data['request'] = $request;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['pageTitle'] = empty($record_id) ? 'Add New CRM Record' : 'Update CRM Record';
	
	    $data['side_menu_index'] = 'home';
	    $data['smi_selection'] = 'crm_in_';
	    $this->getTemplate()->display('crm_record_form', $data);
	}
	
	function getSubmittedCrmData()
	{
	    $posts = $this->getRequest()->getPost();
	    $crmData = new stdClass();
	    if (is_array($posts)) {
	        foreach ($posts as $key=>$val) {
	            $crmData->$key = trim($val);
	        }
	    }
	
	    return $crmData;
	}
	
	function getInitialCrmData($record_id, $crm_model)
	{
	    $crmData = null;
	
	    if (empty($record_id)) {
	        $crmData = new stdClass();
	        $crmData->account_id = "";
	        $crmData->title = "";
	        $crmData->first_name = "";
	        $crmData->middle_name = "";
	        $crmData->last_name = "";
	        $crmData->DOB = "";
	        $crmData->house_no = "";
	        $crmData->street = "";
	        $crmData->landmarks = "";
	        $crmData->city = "";
	        $crmData->state = "";
	        $crmData->zip = "";
	        $crmData->country = "";
	        $crmData->home_phone = "";
	        $crmData->office_phone = "";
	        $crmData->mobile_phone = "";
	        $crmData->other_phone = "";
	        $crmData->fax = "";
	        $crmData->email = "";
	        $crmData->priority_label = "";
	        $crmData->status = "A";
	    } else {
	        $cond = "record_id='$record_id'";
	        $crmData = $crm_model->getCrmRecordDetailByCond($cond, 0, 1);
	        if ($crmData != null) $crmData = $crmData[0];
	    }
	    return $crmData;
	}
	
	function actionTabDetails()
	{
	    $param = isset($_REQUEST['param']) ? trim($_REQUEST['param']) : '';
	    $secTabId = isset($_REQUEST['tabid']) ? trim($_REQUEST['tabid']) : '';
	
	    include('crm_in_data_tab.php');
	    $crm_info = get_api_data($param, $secTabId);
	     
	    //var_dump($crm_info);
	
	    echo json_encode($crm_info);
	    exit();
	}
	
	function actionTicketSubmit() {
	    $serviceType = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : "";
	    $template = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
	    $callid = isset($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
	    $agentId = isset($_REQUEST['agentid']) ? $_REQUEST['agentid'] : "";
	    $useEmailMod = isset($_REQUEST['emailmod']) ? $_REQUEST['emailmod'] : "N";
	    
	    if ($useEmailMod == "Y" && $this->getTemplate()->chat_module){
	        include('model/MSkill.php');
    	    $skill_model = new MSkill();
    	    include('model/MEmail.php');
    	    $eTicket_model = new MEmail();
    	    include('model/MSkillCrmTemplate.php');
    	    $tmodel = new MSkillCrmTemplate();
    	    $err = '';
    	    $errType = 1;
    	    
    	    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    	    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    	    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    	    $mail_body = isset($_POST['mail_body']) ? trim($_POST['mail_body']) : '';
    	    $skill_id = isset($_POST['skill_id']) ? trim($_POST['skill_id']) : '';
    	    $skill_email = isset($_POST['skill_email']) ? trim($_POST['skill_email']) : '';
    	    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    	    
    	    $cc = isset($_POST['cc']) ? $_POST['cc'] : null;
    	    $bcc = isset($_POST['bcc']) ? $_POST['bcc'] : null;

    	    $ticketData = $tmodel->getDispositionGroupById($serviceType, $template);
    	    if (!empty($ticketData->service_type_id) && $ticketData->service_type_id == $serviceType){
    	        $title = $ticketData->title;
    	        $emailFrom = $ticketData->from_email_name;
    	        $startPos = strpos($emailFrom, "<");
    	        $endPos = strpos($emailFrom, ">");
    	        if ($startPos !== false && $endPos !== false) {
    	            $startPos = $startPos + 1;
    	            $email = substr($emailFrom, $startPos, ($endPos - $startPos));
    	            $name = substr($emailFrom, 0, ($startPos - 1));
    	        }
    	    }else {
    	        $errMsg = "Invalid Service Type!";
    	    }
    	    
    	    $did = '';
    	    for ($i=0;$i<=10; $i++) {
    	        $did1 = isset($_POST['disposition_id'.$i]) ? trim($_POST['disposition_id'.$i]) : '';
    	        if (!empty($did1)) $did = $did1;
    	        else break;
    	    }
    	    
    	    $emails = empty($skill_id) ? array() : $skill_model->getEmails($skill_id, 'array');
    	    
    	    $data['ccmails'] = $eTicket_model->getAllowedEmails();
    	    $data['changable_status'] = $eTicket_model->getChangableTicketStatus('O');
    	    
    	    if (isset($_POST['skill_email'])) {
    	        $err = $this->validateCreateEmail($name, $email, $title, $mail_body, $skill_id, $skill_email, $status);
    	        if (empty($err)) {
    	    
    	            $is_success = false;
    	    
    	            $skillinfo = $skill_model->getSkillById($skill_id);
    	            $skill_name = empty($skillinfo) ? '' : $skillinfo->skill_name;
    	            if ($eTicket_model->createNewEmail(UserAuth::getCurrentUser(), $skill_name, $skill_email, $name, $email, $cc, $bcc, $did, $title, $mail_body, $status, $skill_id, $data['ccmails'])) {
    	                $dataObj = new stdClass();
    	                $dataObj->template_id = $template;
    	                $dataObj->service_id = $serviceType;
    	                $dataObj->agent_id = $agentId;
    	                $dataObj->email_to = $skill_email;
    	                $dataObj->email_subject = $title;
    	                $dataObj->email_body = addslashes(htmlentities($mail_body));
    	                $tmodel->addTicketingLog($callid, $dataObj);
    	                
    	                $err = 'New ticket created successfully !!';
    	                $is_success = true;
    	            } else {
    	                $err = 'Failed to create new ticket !!';
    	            }
    	    
    	            if ($is_success) {
    	                $errType = 0;
    	            }
    	        }
    	    }
    	    
    	    if (UserAuth::hasRole('admin')) {
    	        $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
    	    } else {
    	        $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
    	    }
    	    
    	    include_once('conf.email.php');
    	    
    	    $data['replace_text_pattern'] = $replace_text_pattern;
    	    $data['disposition_ids'] = $eTicket_model->getDispositionPathArray('');
    	    $data['email_model'] = $eTicket_model;
    	    $data['name'] = $name;
    	    $data['email'] = $email;
    	    $data['title'] = $title;
    	    $data['mail_body'] = $mail_body;
    	    $data['skill_id'] = $skill_id;
    	    $data['skill_email'] = $skill_email;
    	    $data['status'] = $status;
    	    $data['errMsg'] = $err;
    	    $data['errType'] = $errType;
    	    $data['emails'] = $emails;
    	    $data['request'] = $this->getRequest();
	    
	    }else {

    	    $emailFrom = "";
    	    $mail_body = "";	    
    	    $isValidTicketing = false;
    	    $errMsg = "";
    	    $errType = 0;
    	    $ticketFromEmail = "";
    	    $ticketFromName = "";
    	    $ticketEmailTo = "";
    	    $ticketToEmails = array();
    	    
    	    include('model/MSkillCrmTemplate.php');
    	    $tmodel = new MSkillCrmTemplate();
    	    $ticketData = $tmodel->getDispositionGroupById($serviceType, $template);
    	    if (!empty($ticketData->service_type_id) && $ticketData->service_type_id == $serviceType){
    	        $mail_to = $ticketData->to_email_name;
    	        $mail_subject = $ticketData->title;
    	        $emailFrom = $ticketData->from_email_name;
    	    }else {
    	        $errType = 2; //0=success, 1=error, 2=invalid
    	        $errMsg = "Invalid Service Type!";
    	    }
    
    	    $request = $this->getRequest();
    	    if ($request->isPost() && empty($errMsg)) {
    	        $mail_to = isset($_POST["tkt_email_to"]) ? trim($_POST["tkt_email_to"]) : "";
    	        $mail_body   = isset($_POST["mail_body"]) ? trim($_POST["mail_body"]) : "";
    	        $mail_subject   = isset($_POST["email_subject"]) ? trim($_POST["email_subject"]) : "";
    	        
    	        if (empty($mail_to)){
    	            $errType = 1;
    	            $errMsg = "Ticket Owner's Email Empty!";
    	        }else {
    	            $toEmailArr = explode(",", $mail_to);
    	            $arrIndex = 0;
    	            foreach ($toEmailArr as $email){
						$email = trim($email);
    	                if (!empty($email)){
        	                $startPos = strpos($email, "<");
        	                $endPos = strpos($email, ">");
        	                if ($startPos !== false && $endPos !== false) {
        	                    $startPos = $startPos + 1;
        	                    $emailAddr = trim(substr($email, $startPos, ($endPos - $startPos)));
        	                    if (!filter_var($emailAddr, FILTER_VALIDATE_EMAIL)) {
        	                        $errType = 1;
        	                        $errMsg = "Ticket Owner's Email Address Invalid!";
        	                    }else {
        	                        $ticketEmailTo .= $emailAddr.", ";
        	                        $toEmailObj = new stdClass();
        	                        $toEmailObj->email = $emailAddr;
        	                        $toEmailObj->name = trim(substr($email, 0, ($startPos-1)));
        	                        $ticketToEmails[$arrIndex] = $toEmailObj;
        	                        $arrIndex++;
        	                    }
        	                }else {
        	                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        	                        $errType = 1;
        	                        $errMsg = "Ticket Owner's Email Address Invalid!";
        	                    }
        	                }
    	                }
    	            }
    	            if (count($ticketToEmails) <= 0){
    	                $errMsg = "Ticket Owner's Email Address Invalid!";
    	            }
    	        }
    	        
    	        if (empty($errMsg)){
    	            if (empty($mail_subject)){
    	                $errType = 1;
    	                $errMsg = "Ticket Subject Empty!";
    	            }elseif (empty($mail_body)){
    	                $errType = 1;
    	                $errMsg = "Email Text Empty!";
    	            }else {
    	                $isValidTicketing = true;
    	            }
    	        }	        
    
                $startPos = strpos($emailFrom, "<");
                $endPos = strpos($emailFrom, ">");
                if ($startPos !== false && $endPos !== false) {
                    $startPos = $startPos + 1;
                    $ticketFromEmail = substr($emailFrom, $startPos, ($endPos - $startPos));
                    $ticketFromName = substr($emailFrom, 0, ($startPos-1));
                    $ticketFromEmail = trim($ticketFromEmail);
                    $ticketFromName = trim($ticketFromName);
                }
    	    }
    	    
    	    if ($isValidTicketing){
        	    include_once('conf.email.php');
        	    require_once("class.phpmailer.php");
        	    $mail = new PHPMailer();
        	
        	    $mail->IsSMTP();
        	    $mail->PluginDir = '';
        	    $mail->SMTPDebug = 0;
        	    $mail->Timeout = 30;
        	    $mail->SMTPKeepAlive = true;
        	    $mail->SMTPAuth = false;
        	    $mail->Port = 25;
        	    $mail->Host = $smtp_server;
        	    $mail->Username = '';
        	    $mail->Password = '';
        	    $mail->WordWrap = 80;
        	
        	    $mail->ClearAllRecipients();
        	    $mail->ClearReplyTos();
        	    $mail->ClearAttachments();
        	    $mail->SetFrom($ticketFromEmail, $ticketFromName);
        	    $mail->Subject = $mail_subject;
        	    $mail->MsgHTML($mail_body);
        	    
        	    foreach($ticketToEmails as $mailNames) {
        	        $mail->AddAddress($mailNames->email, $mailNames->name);
        	    }
        	
        	    try {
        	        if ($mail->Send() ) {
        	            $dataObj = new stdClass();
        	            $dataObj->template_id = $template;
        	            $dataObj->service_id = $serviceType;
        	            $dataObj->agent_id = $agentId;
        	            $dataObj->email_to = $ticketEmailTo;
        	            $dataObj->email_subject = $mail_subject;
        	            $dataObj->email_body = addslashes(htmlentities($mail_body));
        	            $tmodel->addTicketingLog($callid, $dataObj);
        	            
        	            $errType = 0;
        	            $errMsg = "Ticket Email sent successfully!";
        	        } else {
        	            $errType = 1;
        	            $errMsg = "Failed to send Ticket Email!";
        	        }
        	    } catch (phpmailerAppException $e) {
        	        ;
        	    }
        	    $mail->SmtpClose();
    	    }
    	    //echo "Has Email Module=".$this->getTemplate()->chat_module;
    
    	    $data = array();
    	    $data['ticketEmail'] = $mail_to;
    	    $data['ticketSubject'] = $mail_subject;
    	    $data['ticketMailBody'] = $mail_body;
    	    $data['errtype'] = $errType;
    	    $data['errmsg'] = $errMsg;
	    }
	    
	    $data['emailmod'] = $useEmailMod;
	    $data['serviceid'] = $serviceType;
	    $data['templateid'] = $template;
	    $data['callid'] = $callid;
	    $data['agentid'] = $agentId;
	    $this->getTemplate()->display_popup_chat('skill_crm_ticket_mail', $data);
	}
	
	function validateCreateEmail($name, $email, $title, $mail_body, $skill_id, $skill_email, $status)
	{
	    if (empty($email)) return 'Owner\'s Email is required';
	    if (empty($title)) return 'Ticket Title is required';
	    if (empty($mail_body)) return 'Text is required';
	    if (empty($skill_id)) return 'Skill is required';
	    if (empty($skill_email)) return 'Skill Email is required';
	    if (empty($status)) return 'Status is required';
	
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Provide valid email address';
	
	    return '';
	}

	function actionCrmindatadownload(){
        include('model/MCrmIn.php');
        $crm_model = new MCrmIn();

        $sessObject = UserAuth::getCDRSearchParams();
        $account_id = isset($sessObject->account_id) ? $sessObject->account_id : "";
        $first_name = isset($sessObject->first_name) ? $sessObject->first_name : "";
        $last_name = isset($sessObject->last_name) ? $sessObject->last_name : "";
        $mobile_no = isset($sessObject->mobile_no) ? $sessObject->mobile_no : "";
        $home_phone = isset($sessObject->home_phone) ? $sessObject->home_phone : "";
        $emailAddr = isset($sessObject->email) ? $sessObject->email : "";
        $office_phone = isset($sessObject->office_phone) ? $sessObject->office_phone : "";
        $status = isset($sessObject->status) ? $sessObject->status : "";

        $file_name = $crm_model->getCdrCsvSavePath() . 'crm_inbound_record_details_' . time() . '.csv';
        $title = 'CRM-Inbound Record Details';

        if (file_exists($file_name)) unlink($file_name);

        $is_success = $crm_model->downloadCrmInRecordFile($file_name, $account_id, $first_name, $last_name, $mobile_no, $home_phone, $emailAddr, $office_phone, $status);
        if ($is_success) {
            require_once('lib/DownloadHelper.php');
            $dl_helper = new DownloadHelper($title, $this->getTemplate());
            $dl_helper->set_local_file($file_name);
            $dl_helper->download_file('', "Account ID,First Name,Last Name,Date of Birth,Email Address,House No,Street,Landmarks,City,State,Zip Code,Country,Mobile Phone,Home Phone,Office Phone,Other Phone,Fax,Priority Label,Status\n");
        }
    }

    function actionVerifyimei()
    {
        $msg = '-1';
        $account_id = isset($_REQUEST['accid']) ? trim($_REQUEST['accid']) : "";
        $mobile_imei = isset($_REQUEST['mimei']) ? trim($_REQUEST['mimei']) : "";
        $agent_id = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : "";

        if (!empty($account_id) && !empty($mobile_imei) && !empty($agent_id)) {
            include('model/MCrmIn.php');
            $crm_model = new MCrmIn();

            if ($crm_model->verifyMobileImei($account_id, $mobile_imei, $agent_id)) {
                $crm_model->addToAuditLog('User Mobile IMEI', 'U', "IMEI=$mobile_imei", "User($account_id) Mobile IMEI Verified");
                $msg = 1;
            }
        }

        echo $msg;
    }
}
