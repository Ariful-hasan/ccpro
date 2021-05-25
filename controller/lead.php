<?php

class Lead extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionLead();
	}

	function actionLead()
	{
		$data['dataUrl'] = $this->url('task=get-home-data&act=campaign-lead');
		$data['pageTitle'] = 'Lead Management';
		$data['side_menu_index'] = 'campaign';
		$this->getTemplate()->display('lead_list', $data);
	}
	
	function actionDelete()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		$nums = isset($_REQUEST['nums']) ? trim($_REQUEST['nums']) : 0;
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		if (!is_numeric($lid)) {
			$nums_db = $campaign_model->numLeadNumbers($lid);
		
			if ($nums == $nums_db) {

				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
				if ($campaign_model->deleteLead($lid)) {
					$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Lead', 'isError'=>false, 'msg'=>'Lead Deleted Successfully', 'redirectUri'=>$url));
				} else {
					$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Lead', 'isError'=>true, 'msg'=>'Failed to Delete Lead', 'redirectUri'=>$url));
				}
			}
		}
	}

	function actionDelnumber()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		$num = isset($_REQUEST['num']) ? trim($_REQUEST['num']) : 0;
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		if (strlen($num) > 0) {

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=numbers&lid=$lid&page=".$cur_page);
			if ($campaign_model->deleteNumber($lid, $num)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Number', 'isError'=>false, 'msg'=>'Number Deleted Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Number', 'isError'=>true, 'msg'=>'Failed to Delete Number', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionNumbers()
	{
		include('model/MCampaign.php');
		include('lib/Pagination.php');
		$campaign_model = new MCampaign();

		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		
		if (empty($lid) || !ctype_digit($lid)) exit;
		
		$leadinfo = $campaign_model->getLeadById($lid);
		
		if (empty($leadinfo)) exit;
		
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=numbers");
		
		
		
		$pagination->num_records = $leadinfo->number_count;//$campaign_model->numLeadNumbers($lid);
		$data['numbers'] = $pagination->num_records > 0 ? 
			$campaign_model->getLeadNumbers($lid, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['numbers']) ? count($data['numbers']) : 0;
		$data['pagination'] = $pagination;

		$data['request'] = $this->getRequest();
		$data['lid'] = $lid;
		$data['pageTitle'] = 'Numbers for Lead :: ' . $leadinfo->title;
		$data['side_menu_index'] = 'campaign';
		$data['smi_selection'] = 'lead_';
		$data['leadinfo'] = $leadinfo;
		$this->getTemplate()->display('lead_numbers', $data);
	}

	function actionDownload()
	{
		include('model/MCampaign.php');
		require_once('lib/DownloadHelper.php');
		$campaign_model = new MCampaign();

		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		if (empty($lid) || !ctype_digit($lid)) exit;
		
		$pageTitle = 'Numbers for Lead ' . $lid;

		$leadinfo = $campaign_model->getLeadById($lid);
		if (empty($leadinfo)) exit;

		$columns = array();
		if (!empty($leadinfo->custom_label_1)) $columns['custom_value_1'] = $leadinfo->custom_label_1;
		if (!empty($leadinfo->custom_label_2)) $columns['custom_value_2'] = $leadinfo->custom_label_2;
		if (!empty($leadinfo->custom_label_3)) $columns['custom_value_3'] = $leadinfo->custom_label_3;
		if (!empty($leadinfo->custom_label_4)) $columns['custom_value_4'] = $leadinfo->custom_label_4;

		$dl_helper = new DownloadHelper($pageTitle, $this->getTemplate());
		
		$dl_helper->create_file('lead_numbers_' . $lid . '.csv');
		$dl_helper->write_in_file("Customer ID,Number 1,Number 2,Number 3,Number 4,Title,FName,LName,Street,City,State,Zip,Email");

		foreach ($columns as $ckey => $cval) {
			$dl_helper->write_in_file(",$cval");
		}

		$dl_helper->write_in_file("\n");
		
		
		
		$numbers = $campaign_model->getLeadNumbers($lid);

		if (is_array($numbers)) {

			foreach ($numbers as $num) {
				$dl_helper->write_in_file("$num->customer_id,$num->dial_number,$num->dial_number_2,$num->dial_number_3,$num->dial_number_4,$num->title,$num->first_name,$num->last_name,$num->street,$num->city,$num->state,$num->zip,$num->email");
				foreach ($columns as $ckey => $cval) {
					$dl_helper->write_in_file(",".$num->{$ckey});
				}
				$dl_helper->write_in_file("\n");
			}
		}

		$dl_helper->download_file();
		exit;
	}

	function actionUpdate()
	{
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		if (empty($lid)) exit;
		$this->saveLead($lid);
	}

	function actionAdd()
	{
		$this->saveLead();
	}
	
	function saveLead($lid='')
	{
		/*
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		*/
		
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		include('lib/FileManager.php');
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$data['heading'] = $this->getExcelHeading();
		//$data['skill_options'] = $skill_model->getOutSkillOptions();
		$is_overwrite = 'N';
		if ($request->isPost()) {

			$lead = $this->getSubmittedLead($lid);
			$errMsg = $this->getValidationMsg($lead);
			$is_number_file_uploaded = false;
			$file_type = '';
			$numbers = array();
			
			if (empty($errMsg)) {
				$resp = FileManager::check_file_for_upload('number', 'csv');
				//var_dump($resp);
				if ($resp == FILE_EXT_INVALID) {
					$resp = FileManager::check_file_for_upload('number', 'txt');
					if ($resp == FILE_EXT_INVALID) {
						$errMsg = 'Please select a CSV or TXT file';
					} else if ($resp == FILE_UPLOADED) {
						$is_number_file_uploaded = true;
						$file_type = 'csv';
					}
				} else if ($resp == FILE_UPLOADED) {
					$is_number_file_uploaded = true;
					$file_type = 'csv';
				}
				//var_dump($is_number_file_uploaded);
				if ($is_number_file_uploaded) {
					$is_overwrite = isset($_POST['is_overwrite']) && $_POST['is_overwrite'] == 'Y' ? 'Y' : 'N';
					
					/*
					$numbers = $this->processNumbers($data['heading'], $file_type);
					if (!is_array($numbers)) {
						$errMsg = $numbers;
					}
					*/
					$numValidationMsg = $this->getNumberValidationMsg($data['heading'], $file_type);
					if (!empty($numValidationMsg)) {
						$errMsg = $numValidationMsg;
					}
				}
			}
			
			//var_dump($is_number_file_uploaded);
			if (empty($errMsg)) {
				$isUpdate = false;
				
				if (!empty($lid)) {
					$oldlead = $this->getInitialLead($lid, $campaign_model);
					$ltext = '';

					if ($oldlead->title != $lead->title) {
						$ltext .= 'Title='.$lead->title . ';';
					}
					if ($oldlead->reference != $lead->reference) {
						$ltext .= 'Reference='.$lead->reference . ';';
					}
					if ($oldlead->country_code != $lead->country_code) {
						$ltext .= 'Country code='.$lead->country_code . ';';
					}
					
					if ($oldlead->custom_label_1 != $lead->custom_label_1) {
						$ltext .= 'Custom Label 1='.$lead->custom_label_1 . ';';
					}
					if ($oldlead->custom_label_2 != $lead->custom_label_2) {
						$ltext .= 'Custom Label 2='.$lead->custom_label_2 . ';';
					}
					if ($oldlead->custom_label_3 != $lead->custom_label_3) {
						$ltext .= 'Custom Label 3='.$lead->custom_label_3 . ';';
					}
					if ($oldlead->custom_label_4 != $lead->custom_label_4) {
						$ltext .= 'Custom Label 4='.$lead->custom_label_4 . ';';
					}
					
					if (!empty($ltext)) $isUpdate = $campaign_model->updateLead($lid, $lead, $ltext);
					//}
				} else {
					$ltext = '';
										
					$ltext .= 'Title='.$lead->title . ';';
					$ltext .= 'Reference='.$lead->reference . ';';
					$ltext .= 'Country code='.$lead->country_code . ';';
					
					$ltext .= 'Custom Label 1='.$lead->custom_label_1 . ';';
					$ltext .= 'Custom Label 2='.$lead->custom_label_2 . ';';
					$ltext .= 'Custom Label 3='.$lead->custom_label_3 . ';';
					$ltext .= 'Custom Label 4='.$lead->custom_label_4 . ';';
					
					$lead_id = $campaign_model->addLead($lead, $ltext);
					if (!empty($lead_id)) $isUpdate = true;
				}
				$count_num = 0;
				$lead_id = isset($lead_id) ? $lead_id : $lid;
				if ($is_number_file_uploaded && !empty($lead_id)) {
					//var_dump($numbers);
					
					if ($is_overwrite == 'Y') {
						$campaign_model->deleteLeadNumbers($lead_id);
					}
					
					$count_num = $this->processNumbers($lead_id, $campaign_model, $data['heading'], $file_type);
					
					/*				
					foreach($numbers as $num) {
						$isEdit = $campaign_model->addLeadNumber($lead_id, $num);
						if ($isEdit) $count_num++;
						//echo 'asd';
					}
					*/
					
					$campaign_model->updateLeadCount($lead_id);
					if ($count_num > 0) {
						$campaign_model->addToAuditLog('Lead', 'A', "Lead=$lead_id", "Numbers:$count_num");
					}
				}
				
				if ($isUpdate || $count_num > 0) {
					$errType = 0;
					$errMsg = empty($lid) ?  'Lead added successfully !!' : 'Lead updated successfully !!';
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				} else {
					$errMsg = empty($lid) ?  'Failed to add lead !!' : 'No change found !!';
				}
				
			}

		} else {
			$lead = $this->getInitialLead($lid, $campaign_model);
		}

		$data['lid'] = $lid;
		$data['lead'] = $lead;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['is_overwrite'] = $is_overwrite;
		$data['pageTitle'] = empty($lid) ? 'Add Lead' : 'Update Lead :: ' . $lid;
		$data['side_menu_index'] = 'campaign';
		$data['smi_selection'] = 'lead_';
		$this->getTemplate()->display('lead_form', $data);
	}

	function actionCrmlead()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		include('model/MCrm.php');
		$crm_model = new MCrm();

		$lid = '';
		$dial_num = isset($_REQUEST['dial_num']) ? trim($_REQUEST['dial_num']) : "";
		$camid = isset($_REQUEST['camid']) ? trim($_REQUEST['camid']) : "";
		$leadid = isset($_REQUEST['leadid']) ? trim($_REQUEST['leadid']) : "";
		$disp_code = isset($_REQUEST['disp_code']) ? trim($_REQUEST['disp_code']) : "";
		$num_selected_req = isset($_POST['num_selected']) ? trim($_POST['num_selected']) : 0;

		if (empty($dial_num) && empty($camid) && empty($leadid) && empty($disp_code)) exit;
		
		$data['num_selected'] = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);
		
		if ($data['num_selected'] == 0) exit;
		$data['pageTitle'] = 'New Lead with selected CRM Record(s)';
		
		$request = $this->getRequest();
		$errMsg = '';
		if ($request->isPost()) {
			$lead = $this->getSubmittedLead($lid);
			$errMsg = $this->getValidationMsg($lead);
			
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
		} else {
			$lead = $this->getInitialLead($lid, $campaign_model);
		}
		
		$data['dial_num'] = $dial_num;
		$data['camid'] = $camid;
		$data['leadid'] = $leadid;
		$data['disp_code'] = $disp_code;
		$data['lead'] = $lead;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = 1;
		$this->getTemplate()->display_popup('crm_lead_form', $data);
	}

	function getUploadedHeadInfo($heading, $record)
	{
		$headIndex = new stdClass();
		$headIndex->hcustomer_id = -1;
		$headIndex->hdial_number = -1;
		$headIndex->hdial_number_2 = -1;
		$headIndex->hdial_number_3 = -1;
		$headIndex->hdial_number_4 = -1;
		$headIndex->htitle = -1;
		$headIndex->hfirst_name = -1;
		$headIndex->hlast_name = -1;
		$headIndex->hstreet = -1;
		$headIndex->hcity = -1;
		$headIndex->hstate = -1;
		$headIndex->hzip = -1;
		$headIndex->hemail = -1;
		
		$headIndex->hcustom_value_1 = -1;
		$headIndex->hcustom_value_2 = -2;
		$headIndex->hcustom_value_3 = -3;
		$headIndex->hcustom_value_4 = -4;
		
		$headIndex->hagent_altid = -1;
		
		$headIndex->error = "";
		
		$num = is_array($record) ? count($record) : 0;
		
		for ($c=0; $c < $num; $c++) {
			$hd = trim($record[$c]);
			if (!empty($hd)) {
				if ($hd == $heading->customer_id_heading) $headIndex->hcustomer_id = $c;
				if ($hd == $heading->dial_number_heading) $headIndex->hdial_number = $c;
				if ($hd == $heading->dial_number_2_heading) $headIndex->hdial_number_2 = $c;
				if ($hd == $heading->dial_number_3_heading) $headIndex->hdial_number_3 = $c;
				if ($hd == $heading->dial_number_4_heading) $headIndex->hdial_number_4 = $c;
				if ($hd == $heading->title_heading) $headIndex->htitle = $c;
				if ($hd == $heading->first_name_heading) $headIndex->hfirst_name = $c;
				if ($hd == $heading->last_name_heading) $headIndex->hlast_name = $c;
				if ($hd == $heading->street_heading) $headIndex->hstreet = $c;
				if ($hd == $heading->city_heading) $headIndex->hcity = $c;
				if ($hd == $heading->state_heading) $headIndex->hstate = $c;
				if ($hd == $heading->zip_heading) $headIndex->hzip = $c;
				if ($hd == $heading->email_heading) $headIndex->hemail = $c;
				
				if ($hd == $heading->custom_value_1_heading) $headIndex->hcustom_value_1 = $c;
				if ($hd == $heading->custom_value_2_heading) $headIndex->hcustom_value_2 = $c;
				if ($hd == $heading->custom_value_3_heading) $headIndex->hcustom_value_3 = $c;
				if ($hd == $heading->custom_value_4_heading) $headIndex->hcustom_value_4 = $c;
				
				if ($hd == $heading->agent_altid_heading) $headIndex->hagent_altid = $c;
				//if ($c == 4) echo "'$record[$c]' , '$heading->number3_heading'";
			}
		}
		
		if (!empty($heading->customer_id_heading) && $headIndex->hcustomer_id < 0) $headIndex->error = "Invalid customer ID column defined !!";
		if (!empty($heading->dial_number_heading) && $headIndex->hdial_number < 0) $headIndex->error = "Invalid Number 1 column defined !!";
		if (!empty($heading->dial_number_2_heading) && $headIndex->hdial_number_2 < 0) $headIndex->error = "Invalid Number 2 column defined !!";
		if (!empty($heading->dial_number_3_heading) && $headIndex->hdial_number_3 < 0) $headIndex->error = "Invalid Number 3 column defined !!";
		if (!empty($heading->dial_number_4_heading) && $headIndex->hdial_number_4 < 0) $headIndex->error = "Invalid Number 4 column defined !!";
		if (!empty($heading->title_heading) && $headIndex->htitle < 0) $headIndex->error = "Invalid Title column defined !!";
		if (!empty($heading->first_name_heading) && $headIndex->hfirst_name < 0) $headIndex->error = "Invalid FName column defined !!";
		if (!empty($heading->last_name_heading) && $headIndex->hlast_name < 0) $headIndex->error = "Invalid LName column defined !!";
		if (!empty($heading->street_heading) && $headIndex->hstreet < 0) $headIndex->error = "Invalid Street column defined !!";
		if (!empty($heading->city_heading) && $headIndex->hcity < 0) $headIndex->error = "Invalid City column defined !!";
		if (!empty($heading->state_heading) && $headIndex->hstate < 0) $headIndex->error = "Invalid State column defined !!";
		if (!empty($heading->zip_heading) && $headIndex->hzip < 0) $headIndex->error = "Invalid Zip column defined !!";
		if (!empty($heading->email_heading) && $headIndex->hemail < 0) $headIndex->error = "Invalid Email column defined !!";
		if (!empty($heading->custom_value_1_heading) && $headIndex->hcustom_value_1 < 0) $headIndex->error = "Invalid column defined for Custom Label 1 !!";
		if (!empty($heading->custom_value_2_heading) && $headIndex->hcustom_value_2 < 0) $headIndex->error = "Invalid column defined for Custom Label 2 !!";
		if (!empty($heading->custom_value_3_heading) && $headIndex->hcustom_value_3 < 0) $headIndex->error = "Invalid column defined for Custom Label 3 !!";
		if (!empty($heading->custom_value_4_heading) && $headIndex->hcustom_value_4 < 0) $headIndex->error = "Invalid column defined for Custom Label 4 !!";
		if (!empty($heading->agent_altid_heading) && $headIndex->hagent_altid < 0) $headIndex->error = "Invalid Agent Alt. ID column defined !!";
		
		if (empty($headIndex->error) && $headIndex->hdial_number < 0) {
			$headIndex->error = "Number 1 column is not defined !!";
		}
		
		return $headIndex;
	}
	
	function GetValidNumberRecord($headIndex, $record)
	{
		$rec = null;
		
		if ($headIndex->hdial_number >= 0 && is_array($record))	{
			
			if ($headIndex->hdial_number >= 0) {
				$callto_orig = isset($record[$headIndex->hdial_number]) ? $record[$headIndex->hdial_number] : '';
				//$callto = str_replace($discard, "", $callto_orig);
				$callto = preg_replace("/[^0-9]/","", $callto_orig);

				if ($this->isValidNumber($callto)) {
					$rec['dial_number'] = $callto;
				}
							
			}
						
			if ($headIndex->hdial_number_2 >= 0) {
				$callto_orig = isset($record[$headIndex->hdial_number_2]) ? $record[$headIndex->hdial_number_2] : '';
				//$callto = str_replace($discard, "", $callto_orig);
				$callto = preg_replace("/[^0-9]/","", $callto_orig);
						
				if ($this->isValidNumber($callto)) {
					$rec['dial_number_2'] = $callto;
				}
								
			}
						
			if ($headIndex->hdial_number_3 >= 0) {
				$callto_orig = isset($record[$headIndex->hdial_number_3]) ? $record[$headIndex->hdial_number_3] : '';
				//$callto = str_replace($discard, "", $callto_orig);
				$callto = preg_replace("/[^0-9]/","", $callto_orig);
						
				if ($this->isValidNumber($callto)) {
					$rec['dial_number_3'] = $callto;
				}
							
			}
						
			if ($headIndex->hdial_number_4 >= 0) {
				$callto_orig = isset($record[$headIndex->hdial_number_4]) ? $record[$headIndex->hdial_number_4] : '';
				//$callto = str_replace($discard, "", $callto_orig);
				$callto = preg_replace("/[^0-9]/","", $callto_orig);
						
				if ($this->isValidNumber($callto)) {
					$rec['dial_number_4'] = $callto;
				}
								
			}
						
			if ($headIndex->hcustomer_id >= 0) {
				$rec['customer_id'] = isset($record[$headIndex->hcustomer_id]) ? preg_replace("/[^0-9]/","", $record[$headIndex->hcustomer_id]) : '';
			}
			if ($headIndex->htitle >= 0) $rec['title'] = isset($record[$headIndex->htitle]) ? $record[$headIndex->htitle] : '';
			if ($headIndex->hfirst_name >= 0) $rec['first_name'] = isset($record[$headIndex->hfirst_name]) ? $record[$headIndex->hfirst_name] : '';
			if ($headIndex->hlast_name >= 0) $rec['last_name'] = isset($record[$headIndex->hlast_name]) ? $record[$headIndex->hlast_name] : '';
			if ($headIndex->hstreet >= 0) $rec['street'] = isset($record[$headIndex->hstreet]) ? $record[$headIndex->hstreet] : '';
			if ($headIndex->hcity >= 0) $rec['city'] = isset($record[$headIndex->hcity]) ? $record[$headIndex->hcity] : '';
			if ($headIndex->hstate >= 0) $rec['state'] = isset($record[$headIndex->hstate]) ? $record[$headIndex->hstate] : '';
			if ($headIndex->hzip >= 0) $rec['zip'] = isset($record[$headIndex->hzip]) ? $record[$headIndex->hzip] : '';
			if ($headIndex->hemail >= 0) $rec['email'] = isset($record[$headIndex->hemail]) ? $record[$headIndex->hemail] : '';
						
			if ($headIndex->hcustom_value_1 >= 0) $rec['custom_value_1'] = isset($record[$headIndex->hcustom_value_1]) ? $record[$headIndex->hcustom_value_1] : '';
			if ($headIndex->hcustom_value_2 >= 0) $rec['custom_value_2'] = isset($record[$headIndex->hcustom_value_2]) ? $record[$headIndex->hcustom_value_2] : '';
			if ($headIndex->hcustom_value_3 >= 0) $rec['custom_value_3'] = isset($record[$headIndex->hcustom_value_3]) ? $record[$headIndex->hcustom_value_3] : '';
			if ($headIndex->hcustom_value_4 >= 0) $rec['custom_value_4'] = isset($record[$headIndex->hcustom_value_4]) ? $record[$headIndex->hcustom_value_4] : '';
					
			if ($headIndex->hagent_altid >= 0) $rec['agent_altid'] = isset($record[$headIndex->hagent_altid]) ? $record[$headIndex->hagent_altid] : '';
						
			//if (!empty($rec) && isset($rec['dial_number']) && !empty($rec['dial_number'])) array_push($number, $rec);

		}
		
		if (!empty($rec) && isset($rec['dial_number']) && !empty($rec['dial_number'])) {
			return $rec;
		}
		
		return null;
	}

	function getNumberValidationMsg($heading, $num_file_type)
	{
		$headIndex = new stdClass();
		$headIndex->hcustomer_id = -1;
		$headIndex->hdial_number = -1;
		$headIndex->error = "File upload error !!";
		
		$file = $_FILES['number']['tmp_name'];
		$fileName = $_FILES['number']['name'];
		
		$err = "No valid number found !!";
		
		$row = 1;
		$fp = fopen($file, "r");
		
		if ($num_file_type == 'csv') {
			while (($record = fgetcsv($fp, 1000, ",")) !== FALSE) {
				if ($row == 1) {
					$headIndex = $this->getUploadedHeadInfo($heading, $record);
					if (empty($headIndex)) {
						$headIndex->error = "No head defination found !!";
					} 
					if (!empty($headIndex->error)) {
						break;
					}
				} else {
					$rec = $this->GetValidNumberRecord($headIndex, $record);
					if (!empty($rec)) {
						$err = '';
						break;
					}
				}
				$row++;
			}
		}
		
		fclose($fp);
		
		if (!empty($headIndex->error)) {
			return $headIndex->error;
		}
		
		if (!empty($err)) {
			return $err;
		}
		

		if ($num_file_type == 'csv') {
			$hf = fopen("temp/heading_lead.txt", "w");
			fwrite($hf, "$heading->customer_id_heading,$heading->dial_number_heading,$heading->dial_number_2_heading,".
			"$heading->dial_number_3_heading,".
                        "$heading->dial_number_4_heading,$heading->title_heading,$heading->first_name_heading,".
			"$heading->last_name_heading,$heading->street_heading,$heading->city_heading,".
			"$heading->state_heading,$heading->zip_heading,$heading->email_heading,$heading->custom_value_1_heading,$heading->custom_value_2_heading,".
			"$heading->custom_value_3_heading,$heading->custom_value_4_heading,$heading->agent_altid_heading");
			fclose($hf);
		}
		
		return '';
	}
	
	function processNumbers($lead_id, $campaign_model, $heading, $num_file_type)
	{
		$number = array();
		$headIndex = new stdClass();
		$headIndex->hcustomer_id = -1;
		$headIndex->hdial_number = -1;
		$headIndex->error = "File upload error !!";

		
		$file = $_FILES['number']['tmp_name'];
		$fileName = $_FILES['number']['name'];
		//echo 'asd';
		$row = 1;
		$discard = array("+", "(", ")", "-", " ");
		$fp = fopen($file, "r");
		
		$numNumberUploaded = 0;
		if ($num_file_type == 'csv') {
			while (($record = fgetcsv($fp, 1000, ",")) !== FALSE) {
			
			//echo $row . "<br>";
			
				$num = is_array($record) ? count($record) : 0;
				if ($row == 1) {
					$headIndex = $this->getUploadedHeadInfo($heading, $record);
					//var_dump($heading);
					//var_dump($record);
					//exit;
					if (empty($headIndex)) {
                                                $headIndex->error = "No head defination found !!";
                                        }
                                        if (!empty($headIndex->error)) {
                                                break;
                                        }

				} else {
					$rec = $this->GetValidNumberRecord($headIndex, $record);
					if (!empty($rec)) {
						$isEdit = $campaign_model->addLeadNumber($lead_id, $rec);
                                                if ($isEdit) $numNumberUploaded++;
					}
				}
				$row++;
			}
		} else if ($num_file_type == 'txt') {
			//echo 'num'.$num;
			/*
			while (($record = fgetcsv($fp, 2048, ",")) !== FALSE) {
				$num = is_array($record) ? count($record) : 0;
				for ($c=0; $c < $num; $c++) {
					$callto_orig = $record[$c];
					$callto = str_replace($discard, "", $callto_orig);
					$rec = null;
					if ($this->isValidNumber($callto)) {
						$rec['dial_number'] = $callto;
						array_push($number, $rec);
					}
				}

				$row++;
			}
			*/
		}
		//var_dump($num_file_type);
		fclose($fp);
		//var_dump($hnumber1);
		//var_dump($hnumber2);
		//var_dump($hnumber3);
		//var_dump($number);
		//exit;
		/*
		if (count($number) <= 0) {
			return "There is no valid phone number(s) in the file <font color='#888888'>$fileName</font>";
		} else {
			//echo $num_file_type;
			if ($num_file_type == 'csv') {
				$hf = fopen("temp/heading_lead.txt", "w");
				fwrite($hf, "$heading->customer_id_heading,$heading->dial_number_heading,$heading->dial_number_2_heading,".
					"$heading->dial_number_3_heading,".
					"$heading->dial_number_4_heading,$heading->title_heading,$heading->first_name_heading,".
					"$heading->last_name_heading,$heading->street_heading,$heading->city_heading,".
					"$heading->state_heading,$heading->zip_heading,$heading->email_heading,$heading->custom_value_1_heading,$heading->custom_value_2_heading,".
					"$heading->custom_value_3_heading,$heading->custom_value_4_heading,$heading->agent_altid_heading");
				fclose($hf);
			}
		}
		*/
		
		return $numNumberUploaded;
		//return $number;			
	}
	
	function isValidNumber($number)
	{
		if (ctype_digit($number)) {
			$len = strlen($number);
			if ($len == 10 || ($len == 11 && substr($number, 0, 1) == 1)) return true;
		}
		return false;
	}
	
	function getInitialLead($lid, $campaign_model)
	{
		$lead = new stdClass();
		$lead->title = '';
		$lead->reference = '';
		$lead->country_code = '';
		$lead->custom_label_1 = '';
		$lead->custom_label_2 = '';
		$lead->custom_label_3 = '';
		$lead->custom_label_4 = '';

		if (!empty($lid)) {
			$lead = $campaign_model->getLeadById($lid);
			if (empty($lead)) exit;
		} else {
			$lead->lead_id = '';
		}
		
		//var_dump($lead);
		return $lead;
	}

	function getExcelHeading()
	{
		$data = new stdClass();
		$data->customer_id_heading = '';
		$data->dial_number_heading = '';
		$data->dial_number_2_heading = '';
		$data->dial_number_3_heading = '';
		$data->dial_number_4_heading = '';
		$data->title_heading = '';
		$data->first_name_heading = '';
		$data->last_name_heading = '';
		$data->street_heading = '';
		$data->city_heading = '';
		$data->state_heading = '';
		$data->zip_heading = '';
		$data->email_heading = '';
		
		$data->custom_value_1_heading = '';
		$data->custom_value_2_heading = '';
		$data->custom_value_3_heading = '';
		$data->custom_value_4_heading = '';
		$data->agent_altid_heading = '';
		
		$heading_file = "temp/heading_lead.txt";

		if (isset($_POST['dial_number_heading'])) {
			$data->customer_id_heading = isset($_POST['customer_id_heading']) ? trim($_POST['customer_id_heading']) : '';
			$data->dial_number_heading = isset($_POST['dial_number_heading']) ? trim($_POST['dial_number_heading']) : '';
			$data->dial_number_2_heading = isset($_POST['dial_number_2_heading']) ? trim($_POST['dial_number_2_heading']) : '';
			$data->dial_number_3_heading = isset($_POST['dial_number_3_heading']) ? trim($_POST['dial_number_3_heading']) : '';
			$data->dial_number_4_heading = isset($_POST['dial_number_4_heading']) ? trim($_POST['dial_number_4_heading']) : '';
			$data->title_heading = isset($_POST['title_heading']) ? trim($_POST['title_heading']) : '';
			$data->first_name_heading = isset($_POST['first_name_heading']) ? trim($_POST['first_name_heading']) : '';
			$data->last_name_heading = isset($_POST['last_name_heading']) ? trim($_POST['last_name_heading']) : '';
			$data->street_heading = isset($_POST['street_heading']) ? trim($_POST['street_heading']) : '';
			$data->city_heading = isset($_POST['city_heading']) ? trim($_POST['city_heading']) : '';
			$data->state_heading = isset($_POST['state_heading']) ? trim($_POST['state_heading']) : '';
			$data->zip_heading = isset($_POST['zip_heading']) ? trim($_POST['zip_heading']) : '';
			$data->email_heading = isset($_POST['email_heading']) ? trim($_POST['email_heading']) : '';
			
			$data->custom_value_1_heading = isset($_POST['custom_value_1_heading']) ? trim($_POST['custom_value_1_heading']) : '';
			$data->custom_value_2_heading = isset($_POST['custom_value_2_heading']) ? trim($_POST['custom_value_2_heading']) : '';
			$data->custom_value_3_heading = isset($_POST['custom_value_3_heading']) ? trim($_POST['custom_value_3_heading']) : '';
			$data->custom_value_4_heading = isset($_POST['custom_value_4_heading']) ? trim($_POST['custom_value_4_heading']) : '';

			$data->agent_altid_heading = isset($_POST['agent_altid_heading']) ? trim($_POST['agent_altid_heading']) : '';
			
		} else if (file_exists($heading_file)) {
			$hf = fopen($heading_file, "r");
			$heading = fread($hf, filesize($heading_file));
			fclose($hf);
		
			$rec_head = explode(",", $heading);
			if (is_array($rec_head)) {
				$data->customer_id_heading = isset($rec_head[0]) ? $rec_head[0] : '';
				$data->dial_number_heading = isset($rec_head[1]) ? $rec_head[1] : '';
				$data->dial_number_2_heading = isset($rec_head[2]) ? $rec_head[2] : '';
				$data->dial_number_3_heading = isset($rec_head[3]) ? $rec_head[3] : '';
				$data->dial_number_4_heading = isset($rec_head[4]) ? $rec_head[4] : '';
				$data->title_heading = isset($rec_head[5]) ? $rec_head[5] : '';
				$data->first_name_heading = isset($rec_head[6]) ? $rec_head[6] : '';
				$data->last_name_heading = isset($rec_head[7]) ? $rec_head[7] : '';
				$data->street_heading = isset($rec_head[8]) ? $rec_head[8] : '';
				$data->city_heading = isset($rec_head[9]) ? $rec_head[9] : '';
				$data->state_heading = isset($rec_head[10]) ? $rec_head[10] : '';
				$data->zip_heading = isset($rec_head[11]) ? $rec_head[11] : '';
				$data->email_heading = isset($rec_head[12]) ? $rec_head[12] : '';
				$data->custom_value_1_heading = isset($rec_head[13]) ? $rec_head[13] : '';
				$data->custom_value_2_heading = isset($rec_head[14]) ? $rec_head[14] : '';
				$data->custom_value_3_heading = isset($rec_head[15]) ? $rec_head[15] : '';
				$data->custom_value_4_heading = isset($rec_head[16]) ? $rec_head[16] : '';
				$data->agent_altid_heading = isset($rec_head[17]) ? $rec_head[17] : '';
			}
		}
		//var_dump($data);
		return $data;
	}

	function getSubmittedLead($lid)
	{
		$posts = $this->getRequest()->getPost();
		$lead = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$lead->$key = trim($val);
			}
		}
		
		$lead->lead_id = $lid;
		return $lead;
	}

	function getValidationMsg($lead)
	{
		$err = '';
		if (empty($lead->title)) return "Provide lead title";
		return $err;
	}

	function findexts ($filename)
	{
		$filename = strtolower($filename) ;
		$exts = explode(".", $filename) ;
		$n = count($exts)-1;
		$exts = $exts[$n];
		return $exts;
	}
}
