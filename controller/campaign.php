<?php

class Campaign extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$this->actionCampaign();
	}

	function actionSelectlead()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		
		$data['pageTitle'] = 'Select Lead(s)';
		
		$data['campaign_leads'] = $campaign_model->getCampaignLeadOptions($cid);
		
		$this->getTemplate()->display_popup('campaign_select_lead', $data);
	}
	
	function actionRinterval()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		
		$data['campaign'] = $campaign_model->getCampaignById($cid);
		
		if (empty($data['campaign'])) exit;
		
		$data['pageTitle'] = 'Retry Interval of Campaign :: ' . $data['campaign']->title;
		
		$this->getTemplate()->display_popup('campaign_retry_interval', $data);
	}
	
	function actionSetetime()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$ctime = 0;
		$topt = 'min';
		$calc_opt = 'A';
		$errMsg = '';
		$data['campaign'] = $campaign_model->getCampaignById($cid);
		
		if (empty($data['campaign'])) exit;
		$data['pageTitle'] = 'Update End Time of Campaign :: ' . $data['campaign']->title;;
		
		$new_end_time = '';
		if (isset($_POST['ctime'])) {

			if (!isset($_POST['submitreset'])) {
				$ctime = isset($_POST['ctime']) ? trim($_POST['ctime']) : 0;
				$topt = isset($_POST['topt']) ? trim($_POST['topt']) : 'min';
				$calc_opt = isset($_POST['calc_opt']) ? trim($_POST['calc_opt']) : 'A';
			}

			if (isset($_POST['submitcalc'])) {
				$ctime = trim($_POST['ctime']);
				if (!preg_match("/^[0-9]{1,4}$/", $ctime)) $errMsg = "Provide valid time";
				else if ($ctime == 0) $errMsg = "Time cannot be zero";
				else $new_end_time = $this->calc_new_time($data['campaign']->end_time);
			} else if (isset($_POST['submitsave'])) {

				$ctime = trim($_POST['ctime']);
				if (!preg_match("/^[0-9]{1,4}$/", $ctime)) $errMsg = "Provide valid time";
				else if ($ctime == 0) $errMsg = "Minute cannot be zero";
				else $new_end_time = $this->calc_new_time($data['campaign']->end_time);
			
				if (empty($errMsg) && !empty($new_end_time)) {

						//if (isset($_POST['submitadd'])) $etime = $ctime;
						//else $etime = -$ctime;
						if ($campaign_model->updateCampaignEndTime($cid, $new_end_time, $data['campaign'])) {
							$data['message'] = 'End time updated Successfully !!';
							$data['msgType'] = 'success';
							$data['refreshParent'] = true;
							$this->getTemplate()->display_popup('popup_message', $data);
						} else {
							$errMsg = "Failed to update end time";
						}

				}

			}
		}
		
		
		$data['new_end_time'] = $new_end_time;
		$data['cid'] = $cid;
		$data['topt'] = $topt;
		$data['calc_opt'] = $calc_opt;
		$data['ctime'] = $ctime;
		$data['errMsg'] = $errMsg;
		$data['request'] = $this->getRequest();
		$this->getTemplate()->display_popup('campaign_end_time_form', $data);
	}
	
	function calc_new_time($old_tstamp)
	{
		$ctime = isset($_POST['ctime']) ? trim($_POST['ctime']) : 0;
		$topt = isset($_POST['topt']) ? trim($_POST['topt']) : 'min';
		$calc_opt = isset($_POST['calc_opt']) ? trim($_POST['calc_opt']) : 'A';

		if (empty($ctime)) return $old_tstamp;
				
		if ($topt == 'day') {
			$mul = 86400;
		} else if ($topt == 'hrs') {
			$mul = 3600;
		} else {
			$mul = 60;
		}
		
		if ($calc_opt == 'L') {
			$newtime = $old_tstamp - $ctime * $mul;
		} else {
			$newtime = $old_tstamp + $ctime * $mul;
		}
		
		return $newtime;
	}
	
	function actionCampaign()
	{
		/* include('model/MCampaign.php');
		include('lib/Pagination.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		$ivr_model = new MIvr();
		$campaign_model = new MCampaign();
		$skill_model = new MSkill();

		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=lead");
		$pagination->num_records = $campaign_model->getCampaignCount();
		
		$data['campaigns'] = $pagination->num_records > 0 ? 
			$campaign_model->getCampaignList($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['campaigns']) ? count($data['campaigns']) : 0;
		$data['pagination'] = $pagination;

		$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		
		$data['dial_engine_options'] = array('PG'=>'Progressive', 'PD'=>'Predictive', 'RS'=>'Responsive', 'AN'=>'Announcement', 'AA'=>'Acknowledgement', 'SR'=>'Survey');
		$data['status_options'] = array('A'=>'Active', 'T'=>'Timeout', 'C'=>'Completed', 'N'=>'Inactive', 'E'=>'Error');
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Campaign Management';
		$data['side_menu_index'] = 'campaign';
		$this->getTemplate()->display('campaign_list', $data); */		
		
		$data['dataUrl'] = $this->url('task=get-home-data&act=campaign');
		$data['pageTitle'] = 'Campaign Management';
		$data['side_menu_index'] = 'campaign';
		$this->getTemplate()->display('campaign_list', $data);
	}
	
	function actionActivate()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		if ($status == 'A' || $status == 'N') {
			include('model/MCampaign.php');
			$campaign_model = new MCampaign();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
			if ($campaign_model->updateCampaignStatus($cid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Campaign', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Campaign', 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionLeadstatus()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		if ($status == 'A' || $status == 'N') {
			include('model/MCampaign.php');
			$campaign_model = new MCampaign();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=update&cid=".$cid);
			if ($campaign_model->updateCampaignLeadStatus($cid, $lid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Campaign Lead', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Campaign Lead', 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}
	}

	function actionLeaddelete()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		
		if (!empty($cid) && !empty($lid)) {

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=update&cid=".$cid);
			if ($campaign_model->deleteCampaignLead($cid, $lid)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Campaign Lead', 'isError'=>false, 'msg'=>'Lead Deleted Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Campaign Lead', 'isError'=>true, 'msg'=>'Failed to Delete Lead', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionLeadreload()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		
		if (!empty($cid) && !empty($lid)) {
			$isSuccess = false;
			if ($campaign_model->deleteCampaignLead($cid, $lid)) {
				$lead = new stdClass();
				$lead->lead_id = $lid;
				$lead->priority = 0;
				$lead->weight = 0;
				$lead->run_hour = '111111111111111111111111';
				if ($campaign_model->addCampaignLead($cid, $lead)) {
					$isSuccess = true;
				}
			}
			
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=update&cid=".$cid);
			if ($isSuccess) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Reload Campaign Lead', 'isError'=>false, 'msg'=>'Lead Reloaded Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Reload Campaign Lead', 'isError'=>true, 'msg'=>'Failed to Reload Lead', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionLeadupdate()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		if (empty($cid) || empty($lid)) exit;
		$this->saveCampaignLead($cid, $lid);
	}
	
	function actionLeadadd()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		if (empty($cid)) exit;
		$this->saveCampaignLead($cid);
	}
	
	function saveCampaignLead($cid='', $lid='')
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$lead = $this->getSubmittedCampaignLead($cid, $lid);
			$kval = '';
			for ($i = 0; $i <= 23; $i++) {
				$l = 'rhour' . $i;
				$kval .= isset($lead->$l) ? '1' : '0';
			}
			$lead->run_hour = $kval;
			
			$errMsg = $this->getLeadValidationMsg($cid, $lid, $lead, $campaign_model);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($lid)) {
					if ($campaign_model->addCampaignLead($cid, $lead)) {
						$errMsg = 'Lead added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add lead !!';
					}
				} else {
					$oldlead = $this->getInitialCampaignLead($cid, $lid, $campaign_model);
					if ($campaign_model->updateCampaignLead($cid, $oldlead, $lead)) {
						$errMsg = 'Lead updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to update lead !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=update&cid=" . $cid);
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
		} else {
			$lead = $this->getInitialCampaignLead($cid, $lid, $campaign_model);
			if (!empty($lid)) $data['lead_title'] = $lead->title;
		}
		
		$data['cid'] = $cid;
		$data['lid'] = $lid;
		$data['lead'] = $lead;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['campaign_leads'] = $campaign_model->getCampaignLeadOptions($cid);
		if (!empty($lid)) {
			if (!isset($data['lead_title'])) {
				$leadinfo = $this->getInitialCampaignLead($cid, $lid, $campaign_model);
				$data['lead_title'] = $leadinfo->title;
			}
		}
		$data['pageTitle'] = empty($lid) ? 'Add Campaign (' .$cid.') Lead' : 'Update Campaign ('.$cid.') Lead :: ' . $lid;
		$data['side_menu_index'] = 'campaign';
		$data['smi_selection'] = 'campaign_';
		$this->getTemplate()->display('campaign_lead_form', $data);
	}
	
	function actionDelete()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		if (!empty($cid)) {

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
			if ($campaign_model->deleteCampaign($cid)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Campaign', 'isError'=>false, 'msg'=>'Campaign Deleted Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Campaign', 'isError'=>true, 'msg'=>'Failed to Delete Campaign', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionRefreshlead()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		if (!empty($cid)) {

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
			if ($campaign_model->refreshCampaign($cid)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Refresh Campaign Lead', 'isError'=>false, 'msg'=>'Campaign lead refreshed successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Refresh Campaign Lead', 'isError'=>true, 'msg'=>'Failed to refresh campaign lead', 'redirectUri'=>$url));
			}
		}
	}

	function actionUpdate()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		if (empty($cid)) exit;
		$this->saveCampaign($cid);
	}

	function actionAdd()
	{
		$this->saveCampaign();
	}
	
	function saveCampaign($cid='')
	{
		include('model/MCampaign.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('model/MLanguage.php');
		include('model/MCti.php');
		$campaign_model = new MCampaign();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$lang_model = new MLanguage();
		$cti = new MCti();
		include('lib/FileManager.php');
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		//$data['heading'] = $this->getExcelHeading();
		//$data['skill_options'] = $skill_model->getOutSkillOptions();
		$data['timezones'] = array(
			'America/New_York',
			'America/Chicago',
			'America/Denver',
			'America/Phoenix',
			'America/Los_Angeles',
			'America/Anchorage',
			'America/Adak',
			'Pacific/Honolulu'
		);
		$data['skill_options'] = $skill_model->getDialerSkillOptions();
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		$data['dial_engine_options'] = array('PD'=>'Predictive');
		$data['did_options'] = $cti->getDID('', 'array', true);

		//print_r($data['did_options']);		exit;
		//array('PG'=>'Progressive', 'PD'=>'Predictive', 'RS'=>'Responsive', 'AN'=>'Announcement', 'AA'=>'Acknowledgement', 'SR'=>'Survey');
		//$data['campaign_leads'] = $campaign_model->getCampaignLeadOptions($cid);
				
		if ($request->isPost()) {

			$campaign = $this->getSubmittedCampaign($cid);
            $kval = '';
            for ($i = 0; $i <= 23; $i++) {
                $l = 'rhour' . $i;
                $kval .= isset($campaign->$l) ? '1' : '0';
            }
            $campaign->run_hour = $kval;
			/*
			if (is_array($data['campaign_leads'])) {
				$a = 0;
				foreach ($data['campaign_leads'] as $cl) {
					$k = 'priority' . $cl->lead_id;
					if (isset($campaign->$k)) {
						//if (!empty($campaign->leads)) $campaign->leads .= ',';
						//$campaign->leads .= $cl->lead_id;
						$data['campaign_leads'][$a]->priority = $campaign->$k;
						$k = 'weight' . $cl->lead_id;
						$data['campaign_leads'][$a]->weight = $campaign->$k;
						$k = 'runhour' . $cl->lead_id;
						$kval = '';
						for ($i = 0; $i <= 23; $i++) {
							$l = $k . $i;
							$kval .= isset($campaign->$l) ? '1' : '0';
						}
						$data['campaign_leads'][$a]->run_hour = $kval;
					}
					$a++;
				}
			}
			*/
			//var_dump($data['campaign_leads']);
			$errMsg = $this->getValidationMsg($campaign);
			
			if (empty($errMsg)) {
				$oldSkill = $campaign_model->getCampaignBySkillId($campaign->skill_id);
				if (!empty($oldSkill)) {
					if (empty($cid) || $oldSkill->campaign_id != $cid) {
						$errMsg = 'Selected skill already added to campaign ' . $oldSkill->title;
					}
				}
			}
			
			$file_uploaded = false;
			if (empty($errMsg)) {
				if ($campaign->dial_engine == 'RS' || $campaign->dial_engine == 'AN' || $campaign->dial_engine == 'AA') {
					$ext = 'wav';
					$resp = FileManager::check_file_for_upload('fl_announcement', 'wav');
					if ($resp == FILE_EXT_INVALID) {
						$ext = 'gsm';
						$resp = FileManager::check_file_for_upload('fl_announcement', 'gsm');
						if ($resp == FILE_EXT_INVALID) {
							$ext = '729';
							$resp = FileManager::check_file_for_upload('fl_announcement', '729');
							$errMsg = 'Provide valid file type';
						}
					}
				
					if ($resp == FILE_UPLOADED) {
						$file_uploaded = true;
					}
				}
			}
			
			if (empty($errMsg)) {
				$isUpdate = false;
				
				if (!empty($cid)) {
					$oldcampaign = $this->getInitialCampaign($cid, $campaign_model);
					$ltext = '';

					if ($oldcampaign->title != $campaign->title) {
						$ltext .= 'Title='.$campaign->title . ';';
					}
					if ($oldcampaign->skill_id != $campaign->skill_id) {
						$skillname = isset($data['skill_options'][$campaign->skill_id]) ? $data['skill_options'][$campaign->skill_id] : $campaign->skill_id;
						$ltext .= 'Skill='.$skillname . ';';
					}
					if ($oldcampaign->cli != $campaign->cli) {
						$ltext .= 'CLI='.$campaign->cli . ';';
					}
					if ($oldcampaign->agent_id != $campaign->agent_id) {
						$ltext .= 'Agent='.$campaign->agent_id . ';';
					}
					if ($oldcampaign->dial_engine != $campaign->dial_engine) {
						$ename = isset($data['dial_engine_options'][$campaign->dial_engine]) ? $data['dial_engine_options'][$campaign->dial_engine] : $campaign->dial_engine;
						$ltext .= 'Dial engine=' . $ename . ';';
					}
					if ($oldcampaign->max_call_per_agent != $campaign->max_call_per_agent) {
						$ltext .= 'Max call per agent='.$campaign->max_call_per_agent . ';';
					}
					if ($oldcampaign->drop_call_action != $campaign->drop_call_action) {
						$ltext .= 'Drop call action='.$campaign->drop_call_action . ';';
					}
					if ($oldcampaign->retry_count != $campaign->retry_count) {
						$ltext .= 'Retry count='.$campaign->retry_count . ';';
					}

					if ($oldcampaign->retry_interval_drop != $campaign->retry_interval_drop) {
						$ltext .= 'Retry interval drop='.$campaign->retry_interval_drop . ';';
					}
					if ($oldcampaign->retry_interval_vm != $campaign->retry_interval_vm) {
						$ltext .= 'Retry interval VM='.$campaign->retry_interval_vm . ';';
					}
					if ($oldcampaign->retry_interval_noanswer != $campaign->retry_interval_noanswer) {
						$ltext .= 'Retry interval noanswer='.$campaign->retry_interval_noanswer . ';';
					}
					if ($oldcampaign->retry_interval_unreachable != $campaign->retry_interval_unreachable) {
						$ltext .= 'Retry interval unreachable='.$campaign->retry_interval_unreachable . ';';
					}

					if ($oldcampaign->max_out_bound_calls != $campaign->max_out_bound_calls) {
						$ltext .= 'MAX out bound calls='.$campaign->max_out_bound_calls . ';';
					}
					if ($oldcampaign->max_pacing_ratio != $campaign->max_pacing_ratio) {
						$ltext .= 'MAX pacing ratio='.$campaign->max_pacing_ratio . ';';
					}
					if ($oldcampaign->max_drop_rate != $campaign->max_drop_rate) {
						$ltext .= 'MAX drop ratio='.$campaign->max_drop_rate . ';';
					}
					$isUpdate = $campaign_model->updateCampaign($cid, $campaign, $ltext);
					$campaign_id = $cid;
					//}
				} else {
					$ltext = '';
										
					$ltext .= 'Title='.$campaign->title . ';';
					$skillname = isset($data['skill_options'][$campaign->skill_id]) ? $data['skill_options'][$campaign->skill_id] : $campaign->skill_id;
					$ltext .= 'Skill='.$skillname . ';';
					$ltext .= 'CLI='.$campaign->cli . ';';
					$ltext .= 'Agent='.$campaign->agent_id . ';';
					$ename = isset($data['dial_engine_options'][$campaign->dial_engine]) ? $data['dial_engine_options'][$campaign->dial_engine] : $campaign->dial_engine;
					$ltext .= 'Dial engine=' . $ename . ';';
					$ltext .= 'Max call per agent=' . $campaign->max_call_per_agent . ';';
					$ltext .= 'Drop call action=' . $campaign->drop_call_action . ';';
					$ltext .= 'Retry count='.$campaign->retry_count . ';';
					$ltext .= 'Retry interval drop='.$campaign->retry_interval_drop . ';';
					$ltext .= 'Retry interval vm='.$campaign->retry_interval_vm . ';';
					$ltext .= 'Retry interval noanswer='.$campaign->retry_interval_noanswer . ';';
					$ltext .= 'Retry interval unreachable='.$campaign->retry_interval_unreachable . ';';
					$ltext .= 'MAX out bound calls='.$campaign->max_out_bound_calls . ';';
					if ($campaign->dial_engine == 'PD' || $campaign->dial_engine == 'RS') {
						$ltext .= 'MAX pacing ratio='.$campaign->max_pacing_ratio . ';';
						$ltext .= 'MAX drop ratio='.$campaign->max_drop_rate . ';';
					}
					
					//$ltext .= 'Reference='.$lead->reference . ';';
					//$ltext .= 'Country code='.$lead->country_code . ';';
					$campaign_id = $campaign_model->addCampaign($campaign, $ltext);
					if (!empty($campaign_id)) $isUpdate = true;
				}
				
				if ($file_uploaded) {
					$music_folder_path = $this->getTemplate()->file_upload_path . 'AUTO_DIAL/';
					$file_name = $music_folder_path . 'campaign_' . $campaign_id;
					if (file_exists($file_name . '.wav')) FileManager::unlink_file($file_name . '.wav');
					if (file_exists($file_name . '.gsm')) FileManager::unlink_file($file_name . '.gsm');
					if (file_exists($file_name . $this->getTemplate()->store_file_extension)) FileManager::unlink_file($file_name . ".".$this->getTemplate()->store_file_extension);
					if (FileManager::save_uploaded_file('fl_announcement', $file_name . '.' . $ext)) $isUpdate = true;
				}
				
				if ($isUpdate) {
					$errType = 0;
					$errMsg = empty($cid) ?  'Campaign added successfully !!' : 'Campaign updated successfully !!';
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				} else {
					$errMsg = empty($cid) ?  'Failed to add campaign !!' : 'No change found !!';
				}
				
			}

		} else {
			$campaign = $this->getInitialCampaign($cid, $campaign_model);
			/*
			if (is_array($data['campaign_leads'])) {
				foreach ($data['campaign_leads'] as $cl) {
					if (!empty($cl->campaign_id)) {
						if (!empty($campaign->leads)) $campaign->leads .= ',';
						$campaign->leads .= $cl->lead_id;
					}
				}
			}
			*/
		}

		$data['call_drop_opt'] = array('HU'=>'Hung Up', 'SW'=>'Silent Wait', 'MH'=>'Music', 'PF'=>'Play Message', 'AN'=>'Play Message DTMF');
		$data['languages'] = $lang_model->getAllLanguage('',0,0,'A');
		$data['campaign_leads'] = $campaign_model->getCampaignLeads($cid);
		$data['status_options'] = array('A'=>'Active', 'T'=>'Timeout', 'C'=>'Completed', 'N'=>'Inactive', 'E'=>'Error');
		//var_dump($data['campaign_leads']);
		$data['cid'] = $cid;
		$data['campaign'] = $campaign;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($cid) ? 'Add Campaign' : 'Update Campaign :: ' . $cid;
		$data['side_menu_index'] = 'campaign';
		$data['smi_selection'] = 'campaign_';
		$this->getTemplate()->display('campaign_form', $data);
	}

	function actionUploadVoiceFile(){

		$upload_file_extension = 'wav';
		$upload_file_name = 'vm';
		
                $cid = $this->getRequest()->getGet('cid');

                if (!ctype_digit($cid)) {
                	AddError("Campaign not found");
                }
                
                AddModel("MCampaign");
                $campaign_model = new MCampaign();
                $campaign = $campaign_model->getCampaignById($cid);
                if (!$campaign){
                        AddError("Campaign not found");
                }
                
                $data = array();
                $data['fullload'] = true;
                $data['pageTitle'] = 'Upload VM Voice';
                $data['buttonTitle'] = 'Cmapaign VM Message';
                //$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
                AddHiddenFields("cid", $cid);
                $isError = false;
                $fileupload = array();
                $target_path = $this->getTemplate()->file_upload_path . 'PD/' . $cid ."/";
                $isFileChanged = false;
                if ($this->getRequest()->isPost()){
                        //$needtosaveeventkey=false;
                        //if (strlen($ivr_menu->event_key) == 13 && substr($ivr_menu->event_key, 0, 3) == 'fl_') {
                                //$file_name = $ivr_menu->event_key;
                        //} else {
                                $needtosaveeventkey = true;
                                $file_name = 'vm';
                        //}

                        $isUploadable = false;
                        if (isset($_FILES["uploadfile"])) {
			    $value = $_FILES["uploadfile"]['name'];
                        //foreach ($_FILES["uploadfile"]['name'] as $lan=>$value){
                            if ($_FILES["uploadfile"]["error"] != UPLOAD_ERR_NO_FILE){
                                if($_FILES["uploadfile"]["error"] == 0) {
                                        $target_file = basename($value);
                                        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                                        //if(in_array($imageFileType, $this->getTemplate()->supported_extn)){ // need to add more file
                                        if(in_array($imageFileType, array('wav'))){
                                                if (true ||$_FILES["uploadfile"]["size"] > 500000) { // temporary skipped
                                                        $fileupload[] = $_FILES["uploadfile"]["tmp_name"];
                                                        $isUploadable = true;
                                                }else{
                                                    $isError = true;
                                                        AddError("Sorry, audio file is too large.");
                                                }
                                        }else{
                                            $isError = true;
                                                AddError("Invalid file type");
                                        }
                                }
                            } else {
                                $isError = true;
                                AddError("No file selected");
                            }
                        //}
                        }

                        if ($isUploadable){
                            $isError = false;
                        }
						
                        if(!$isError){
                                foreach ($fileupload as $vl){
                                        $extension_type = pathinfo($target_file,PATHINFO_EXTENSION);
                                        //$fl=$target_path."{$key}/";
                                        $fl=$target_path;
                                        if(!is_dir($fl)){
                                                mkdir($fl,0755,true);
                                        }
                                        
                                        if(file_exists($fl.$file_name.".$extension_type")){
                                                unlink($fl.$file_name.".$extension_type");
                                        }
                                        
                                        //echo $fl.$file_name.".$extension_type";exit;
                                        if(!move_uploaded_file($vl, $fl.$file_name.".$extension_type")){
                                                $isError =true;
                                        } else {
                                                $isFileChanged = true;
                                        }
                                }
                                
                                /*
                                if ($isFileChanged) {
                                        if($needtosaveeventkey){
                                                $ivrmodel->updateIVRDetailsByBranch($branch, array('event_key'=>$file_name));
                                        }
                                        AddModel("MMOHFiller");
                                        $mh=new MMOHFiller();
                                        $mh->notifyIVRVoiceFileUpdate($ivr_menu->ivr_id, '');
                                }
                                */
                                
                                if (!$isError) {
                                        AddInfo("File uploaded successfully");
                                        $this->getTemplate()->display_popup_msg($data);
                                }
                                
                        }

                }

                $vm_file = $target_path . 'vm.wav';
                if (file_exists($vm_file)) {
                	$file_delete_url=$this->url("task=confirm-response&act=delete-campaign-vm-file&pro=df&cid=".$cid);
                } else {
                	$file_delete_url="";
                }
                
                $data['file_delete_url'] = $file_delete_url;
                $this->getTemplate()->display_popup('campaign-upload-vm', $data);
        }
	
	
	function isValidNumber($number)
	{
		if (strlen($number) > 0 && ctype_digit($number)) return true;
		return false;
	}
	
	function getInitialCampaign($cid, $campaign_model)
	{
		$campaign = new stdClass();
		$campaign->title = '';
		$campaign->skill_id = '';
		$campaign->cli = '';
		$campaign->agent_id = '';
		$campaign->dial_engine = '';
		$campaign->max_out_bound_calls = '0';
		$campaign->max_pacing_ratio = '1';
		$campaign->max_drop_rate = '0';
		$campaign->max_call_per_agent = '0';
		$campaign->drop_call_action = '';
		$campaign->retry_count = '0';
		$campaign->retry_interval_drop = '0';
		$campaign->retry_interval_vm = '0';
		$campaign->retry_interval_noanswer = '0';
		$campaign->retry_interval_unreachable = '0';
		$campaign->timezone = 'America/Chicago';
	        $campaign->run_hour = '111111111111111111111111';

		//$campaign->leads = '';

		if (!empty($cid)) {
			$campaign = $campaign_model->getCampaignById($cid);
			if (empty($campaign)) exit;
		} else {
			$campaign->campaign_id = '';
		}
		
		return $campaign;
	}
	
	function getInitialCampaignLead($cid, $lid, $campaign_model)
	{
		$lead = new stdClass();
		$lead->lead_id = '';
		$lead->priority = '4';
		$lead->weight = '4';
		$lead->run_hour = '111111111111111111111111';

		if (!empty($lid)) {
			$lead = $campaign_model->getCampaignLeadById($cid, $lid);
			if (empty($lead)) exit;
		}
		
		return $lead;
	}

	function getSubmittedCampaign($cid)
	{
		$posts = $this->getRequest()->getPost();
		$campaign = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				if (is_string($val)) {
					$campaign->$key = trim($val);
				} else if (is_array($val)) {
					$campaign->$key = array();
					foreach ($val as $val1) {
						array_push($campaign->$key, trim($val1));
					}
				}
				//if (is_array($val)) var_dump($val);
			}
		}
		/*
		$campaign->leads = '';
		if (isset($campaign->leads_selected)) {
			foreach ($campaign->leads_selected as $ld) {
				if (!empty($campaign->leads)) $campaign->leads .= ',';
				$campaign->leads .= $ld;
			}
		}
		*/
		if ($campaign->dial_engine == 'SR') {
			$campaign->skill_id = $campaign->ivr_id;
		}
		
		$campaign->campaign_id = $cid;
		return $campaign;
	}
	
	function getSubmittedCampaignLead($cid, $lid)
	{
		$posts = $this->getRequest()->getPost();
		$lead = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				if (is_string($val)) {
					$lead->$key = trim($val);
				} else if (is_array($val)) {
					$lead->$key = array();
					foreach ($val as $val1) {
						array_push($lead->$key, trim($val1));
					}
				}
				//if (is_array($val)) var_dump($val);
			}
		}
		
		return $lead;
	}

	function getValidationMsg($campaign)
	{
		$err = '';
		if (empty($campaign->title)) return "Provide campaign title";
		if (empty($campaign->dial_engine)) return "Provide dial engine";
		
		if ($campaign->dial_engine == 'PG' || $campaign->dial_engine == 'PD' || $campaign->dial_engine == 'RS') {
			if (empty($campaign->skill_id)) return "Provide skill";
		} else if ($campaign->dial_engine == 'SR') {
			if (empty($campaign->skill_id)) return "Provide IVR";
		}
		
		if (empty($campaign->cli)) {
			return "Provide Caller ID";
		}
		if (!ctype_digit($campaign->cli)) return "Invalid Caller ID";
		$cli_len = strlen($campaign->cli);
		if ($cli_len != 10 && $cli_len != 11) return "Invalid Caller ID";
		
		if (!preg_match("/^[0-9]{1,3}$/", $campaign->max_out_bound_calls)) return "Provide valid MAX outbound call(s)";
		if ($campaign->dial_engine == 'PD' || $campaign->dial_engine == 'RS') {
			if (strlen($campaign->max_pacing_ratio) == 0 || $campaign->max_pacing_ratio == '.') return "Provide MAX pacing ratio";
			if (!preg_match("/^[0-9]{0,2}(\.[0-9]{1,2})?$/", $campaign->max_pacing_ratio)) return "Provide valid MAX pacing ratio";
			if ($campaign->max_pacing_ratio < 1) return "Minimum allowed pacing ratio is 1.00";
		}
		if (strlen($campaign->max_drop_rate) == 0 || $campaign->max_drop_rate == '.') return "Provide MAX drop ratio";
		if (!preg_match("/^[0-9]{0,2}(\.[0-9]{0,2})?$/", $campaign->max_drop_rate)) return "Provide valid MAX drop ratio";

		if (!preg_match("/^[0-9]{1}$/", $campaign->retry_count)) return "Provide valid retry count";
		//if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_amd)) return "Provide valid retry interval AMD";
		if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_drop)) return "Provide valid retry interval drop";
		if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_vm)) return "Provide valid retry interval busy";
		if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_noanswer)) return "Provide valid retry interval noanswer";
		if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_unreachable)) return "Provide valid retry interval unreachable";
		//if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_cancel)) return "Provide valid retry interval cancel";
		//if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_chanunavail)) return "Provide valid retry interval chanunavail";
		
		return $err;
	}
	
	function getLeadValidationMsg($cid, $lid, $lead, $campaign_model)
	{
		$err = '';
		if (empty($lead->lead_id)) return 'Select lead !!';
		
		if ($lid != $lead->lead_id) {
			$existing_lead = $campaign_model->getCampaignLeadById($cid, $lead->lead_id);
			if (!empty($existing_lead)) return 'Selected lead already exist !!';
		}
		
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
