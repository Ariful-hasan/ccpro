<?php

class Cdr extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MReport.php');
		include('model/MSetting.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$setting_model = new MSetting();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
		
		$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
		$stext = isset($_REQUEST['stext']) ? str_replace(array("(",")"," ","-"), "", $_REQUEST['stext']) : "";
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=linecdr&type='.$type);

		if ($type=='outbound') {
			$data['pageTitle'] = 'Outbound Calls';
			$data['cliColumn'] = 'Call From';
			$data['didColumn'] = 'Call To';
			$selectArr = array('cli'=>'Call From','did'=>'Call To');
		} else if($type=='pabx') {
			$data['pageTitle'] = 'PABX Calls';
			$data['cliColumn'] = 'Agent From';
			$data['didColumn'] = 'Agent To';
			$selectArr = array('cli'=>'Agent From','did'=>'Agent To');
		} else {
			$type='inbound';
			$data['pageTitle'] = 'Inbound Calls';
			$data['cliColumn'] = 'Caller ID';
			$data['didColumn'] = 'DID';
			$selectArr = array('cli'=>'Caller ID','did'=>'DID');
		}
		
		$data['select_options'] = $selectArr;
		

		if (isset($_POST['search'])) {
			$audit_text = '';
			if (!empty($stext)) {
				$_opt = isset($data['option_item'][$option]) ? $data['option_item'][$option] : $option;
				$audit_text .= "$_opt=$stext;";
			}
			$audit_text .= DateHelper::get_date_log($dateinfo);
			$report_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
		}
				
		$data['side_menu_index'] = 'reports';
		/*
		$data['topMenuItems'] = array(
			array('href'=>'task=cdr&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
			array('href'=>'task=cdr&type=outbound', 'img'=>'report.png', 'label'=>'Outbound CDR')
		);
		*/
		$this->getTemplate()->display('report_cdr', $data);
	}

	function actionDownload()
	{
	    include('model/MReport.php');
        include('lib/Pagination.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $pagination = new Pagination();
        $pagination->rows_per_page = 20;
        if (!empty($_REQUEST['etime'])) {
            if (empty($_REQUEST['edate'])) $_REQUEST['edate'] = $_REQUEST['sdate'];
        }
        /*
        Time Validation
        */
        if (!empty($_REQUEST['stime']) && !empty($_REQUEST['etime'])) {
            $_times = explode(':', $_REQUEST['stime']);
            $_hh = isset($_times[0]) ? (int) $_times[0] : 0;
            $_mm = isset($_times[1]) ? (int) $_times[1] : 0;
            if ($_hh >= 0 && $_hh < 24 && $_mm >= 0 && $_mm < 60) {
                $_REQUEST['stime'] = sprintf("%02d", $_hh) . ':' . sprintf("%02d", $_mm);
            }
            
            $_times = explode(':', $_REQUEST['etime']);
            $_hh = isset($_times[0]) ? (int) $_times[0] : 23;
            $_mm = isset($_times[1]) ? (int) $_times[1] : 59;
            if ($_hh >= 0 && $_hh < 24 && $_mm >= 0 && $_mm < 60) {
                $_REQUEST['etime'] = sprintf("%02d", $_hh) . ':' . sprintf("%02d", $_mm);
            }
        }
        
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        
        if ($type=='outbound') {
            $data['pageTitle'] = 'Download CDR - Outbound';
            $template_name = 'report_cdr_download_outbound';
        } else {
            $data['pageTitle'] = 'Download CDR - Inbound';
            $template_name = 'report_cdr_download';
        }
            
	    // $dateinfo = DateHelper::get_input_time_details();
	    // Gprint($dateinfo);
	    // Gprint($_REQUEST); 

	    $date_format = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
	    // var_dump($date_format);

    	$date_from = !empty($_REQUEST['sdate']) ? generic_date_format($_REQUEST['sdate'], $date_format) : date('Y-m-d');
        $date_to = !empty($_REQUEST['edate']) ? generic_date_format($_REQUEST['edate'], $date_format) : date('Y-m-d');
        $hour_from = !empty($_REQUEST['stime']) ? date("H:00",strtotime(date_format(date_create_from_format($date_format." H:i", $_REQUEST['sdate'].' '.$_REQUEST['stime']),'Y-m-d H:i'))) : "00:00";
        $hour_to = !empty($_REQUEST['etime']) ? date("H:59", strtotime(date_format(date_create_from_format($date_format." H:i",$_REQUEST['edate'].' '.$_REQUEST['etime']),'Y-m-d H:i'))) : "23:59";
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','');
	    // var_dump($date_from);
	    // var_dump($date_to); 
	    // Gprint($hour_from); 
	    // Gprint($hour_to); 
	    // Gprint($dateinfo);
	    // die();

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
            $data['logs'] = null;
        } else {
            $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=download&type=$type&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime");
            //$pagination->num_records = $report_model->numDLoadCDR($dateinfo);
            if (isset($_REQUEST['download'])) {
                $data['report_model'] = $report_model;
            } else {
                if ($type=='outbound') {
                    $pagination->num_records = $report_model->numDLoadOutboundCDR($dateinfo);
                    $data['logs'] = $pagination->num_records > 0 ?
                        $report_model->getDLoadOutboundCDR($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;                        
                } else {
                    $pagination->num_records = $report_model->numDLoadCDR($dateinfo);
                    $data['logs'] = $pagination->num_records > 0 ?
                        $report_model->getDLoadCDR($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
                }
                $pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;
            }
	    }
	    
	    //$data['pageTitle'] = 'Download CDR - Inbound';
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['dateinfo'] = $dateinfo;
	    $data['type'] = $type;
	    $data['pagination'] = $pagination;
	    $data['request'] = $this->getRequest();
	    $data['reportHeader'] = true;
        $data['side_menu_index'] = 'reports';
        $data['topMenuItems'] = array(
            array('href'=>'task=cdr&act=download&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
            array('href'=>'task=cdr&act=download&type=outbound', 'img'=>'report.png', 'label'=>'Outbound CDR')
        );
	    $this->getTemplate()->display($template_name, $data);
	}
	
	function downloadTelcoCDR($report_model, $title, $template, $type, $option, $stext, $dateinfo)
	{
		//path also used in model function
		$file_name = $report_model->getCdrCsvSavePath() . 'cdr_' . $type . '.csv';

		$is_success = $report_model->prepareTelcoCDRFile($type, $option, $stext, $dateinfo, $file_name);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			if ($type == 'outbound') {
				$dl_helper->download_file('', "Start time,Answer time,Stop time,Call from,Call to,Duration,Talk time,Trunk,Disc. cause,Disc. party\n");
			} else {
				$dl_helper->download_file('', "Start time,Answer time,Stop time,Caller ID,DID,Duration,Talk time,Trunk,Disc. cause,Disc. party\n");
			}
		}
	}
	/*
	function actionVmessage()
	{
		include('model/MReport.php');
		include('model/MSkill.php');
		include('lib/Pagination.php');
		$report_model = new MReport();
		$skill_model = new MSkill();
		$pagination = new Pagination();

		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "new";

		if ($type=='queue') {
			$option = 'Q';
			$data['pageTitle'] = 'Voice Message in Queue';
		} else if ($type=='served') {
			$option = 'S';
			$data['pageTitle'] = 'Served Voice Message';
		} else {
			$option = 'I';
			$type = 'new';
			$data['pageTitle'] = 'New Voice Message';
		}
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&type=$type");
		$pagination->num_records = $report_model->numVMRecords($option);
		$data['calls'] = $pagination->num_records > 0 ? 
			$report_model->getVMRecords($option, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['calls']) ? count($data['calls']) : 0;
		$data['pagination'] = $pagination;
		$data['type'] = $type;
		$data['option'] = $option;
		$data['request'] = $this->getRequest();
		$data['skill_options'] = $skill_model->getSkillOptions();
		$this->getTemplate()->display('report_voice_message', $data);
	}
	
	function actionVmstatus()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		$_title = 'Voice Message';
		if ($status == 'S') {
			include('model/MReport.php');
			$report_model = new MReport();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=vmessage&page=".$cur_page."&type=new");
			if ($report_model->updateVMStatus($cid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>$_title, 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>$_title, 'isError'=>true, 'msg'=>'Failed to Update Status Option', 'redirectUri'=>$url));
			}
		}

	}
	*/
	
	function actionAgent()
	{
		include('model/MReport.php');
		include('model/MAgent.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$agent_model = new MAgent();

		$dateinfo = DateHelper::get_input_time_details();
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
		
		$data['skill_options'] = null;
		if ($type=='oauto') {
			$data['pageTitle'] = 'Agent Outbound Auto Dialed Calls';
		} else if($type=='omanual') {
			include('model/MSkill.php');
			$skill_model = new MSkill();
			$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
			$data['pageTitle'] = 'Agent Outbound Calls';
		} else {
			$type='inbound';
			$data['pageTitle'] = 'Agent Inbound Calls';
		}
		
		$data['dataUrl'] = $this->url('task=get-report-data&act=agent&type='.$type);
		$data['type'] = $type;
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		
		$data['topMenuItems'] = array(
			array('href'=>'task=cdr&act=agent&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
			array('href'=>'task=cdr&act=agent&type=omanual', 'img'=>'report.png', 'label'=>'Outbound CDR')
		);
		$this->getTemplate()->display('report_cdr_agent', $data);
	}
	
	function actionAgent_bk()
	{
	    include('model/MReport.php');
	    include('model/MAgent.php');
	    include('lib/Pagination.php');
	    include('lib/DateHelper.php');
	    $report_model = new MReport();
	    $agent_model = new MAgent();
	    $pagination = new Pagination();
	
	    $dateinfo = DateHelper::get_input_time_details();
	    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
	    //$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
	    $agentid = isset($_REQUEST['agentid']) ? str_replace(array("(",")"," ","-"), "", $_REQUEST['agentid']) : "";
	
	    $data['skill_options'] = null;
	    if ($type=='oauto') {
	        $data['pageTitle'] = 'Agent Outbound Auto Dialed Calls';
	    } else if($type=='omanual') {
	        include('model/MSkill.php');
	        $skill_model = new MSkill();
	        $data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
	        $data['pageTitle'] = 'Agent Outbound Calls';
	    } else {
	        $type='inbound';
	        $data['pageTitle'] = 'Agent Inbound Calls';
	    }
	
	
	    $data['dataUrl'] = $this->url('task=get-report-data&act=agent&type='.$type);
	
	    $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=agent&type=$type&agentid=$agentid&" .
	        "sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime");
	
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
	        $pagination->num_records = $report_model->numAgentCDRs($type, $agentid, $dateinfo);
	    }
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	
	    if (isset($_REQUEST['download']) && $pagination->num_records>0) {
	        $this->downloadAgentCDR($report_model, $data['pageTitle'],  $this->getTemplate(), $data['skill_options'], $type, $agentid, $dateinfo);
	        exit;
	    }
	    //$report_model->getAgentCDRs($type, $agentid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page);
	
	    $data['calls'] = $pagination->num_records > 0 ?
	    $report_model->getAgentCDRs($type, $agentid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
	    $pagination->num_current_records = is_array($data['calls']) ? count($data['calls']) : 0;
	    $data['pagination'] = $pagination;
	
	    $data['type'] = $type;
	
	    $data['dateinfo'] = $dateinfo;
	    $data['agentid'] = $agentid;
	    $data['agent_options'] = $agent_model->getAgentNames('', '', 0, 0, 'array');
	    $data['request'] = $this->getRequest();
	    $data['reportHeader'] = true;
	    $data['side_menu_index'] = 'reports';
	
	    if (isset($_POST['search'])) {
	        $audit_text = '';
	        if (!empty($agentid)) {
	            $audit_text .= "Agent ID=$agentid;";
	        }
	        $audit_text .= DateHelper::get_date_log($dateinfo);
	        $report_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
	    }
	    $data['topMenuItems'] = array(
	        array('href'=>'task=cdr&act=agent&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
	        array('href'=>'task=cdr&act=agent&type=omanual', 'img'=>'report.png', 'label'=>'Outbound CDR')
	    );
	    $this->getTemplate()->display('report_cdr_agent', $data);
	}

	function downloadAgentCDR($report_model, $title, $template, $skill_options, $type, $agentid, $dateinfo)
	{
		//path also used in model function
		$file_name = $report_model->getCdrCsvSavePath() . 'agent_cdr_' . $type . '.csv';

		$is_success = $report_model->prepareAgentCDRFile($type, $agentid, $dateinfo, $file_name, $skill_options);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			if ($type == 'omanual') {
				$dl_helper->download_file('', "Log time,Agent ID,Nick name,Skill,Call to,Answered,Talk time,Service time,Hold time,Hold count,ACW time\n");
			} else {
				$dl_helper->download_file('', "Log time,Agent ID,Nick name,Answered,Ring time,Service time,Hold time,Hold count,ACW time\n");
			}
		}
	}

	function actionDownloadskillvoice()
	{
		include('model/MReport.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('model/MSetting.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		
		$report_model = new MReport();
		
		$dateinfo = DateHelper::get_input_time_details();
		//$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
		$type = "inbound";
		$skillid = isset($_REQUEST['skillid']) ? str_replace(array("(",")"," ","-"), "", $_REQUEST['skillid']) : "";
		$cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : "";
		$aid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : "";
		
		$num_records = $report_model->numSkillCDRs($type, $skillid, $cli, $did, $aid, $dateinfo);
		
		if ($num_records > 0 && isset($_POST['fname1'])) {
			$rows = $report_model->getSkillCDRsForVoice($type, $skillid, $cli, $did, $aid, $dateinfo, 0, 0);
			//var_dump($rows);
			$voice_files = array();
			if (is_array($rows)) {
				foreach ($rows as $call) {
					$file_timestamp = substr($call->callid, 0, 10);
					$yyyy = date("Y", $file_timestamp);
					$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
					$sound_file_gsm = $this->getTemplate()->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $call->callid . ".gsm";
					//echo $sound_file_gsm;
					if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
						$newname = '';
						if (!empty($_POST['fname1'])) {
							if ($_POST['fname1'] == 'DT') {if (!empty($call->tstamp)) $newname .= date("Y_m_d_H_i_s", $call->tstamp) . '-';}
							else if ($_POST['fname1'] == 'AI') {if (!empty($call->agent_id)) $newname .= $call->agent_id . '-';}
							else if ($_POST['fname1'] == 'CL') {if (!empty($call->cli))  $newname .= $call->cli . '-';}
							else if ($_POST['fname1'] == 'DD') {if (!empty($call->did)) $newname .= $call->did . '-';}
						}
						if (!empty($_POST['fname2'])) {
							if ($_POST['fname2'] == 'DT') {if (!empty($call->tstamp)) $newname .= date("Y_m_d_H_i_s", $call->tstamp) . '-';}
							else if ($_POST['fname2'] == 'AI') {if (!empty($call->agent_id)) $newname .= $call->agent_id . '-';}
							else if ($_POST['fname2'] == 'CL') {if (!empty($call->cli)) $newname .= $call->cli . '-';}
							else if ($_POST['fname2'] == 'DD') {if (!empty($call->did)) $newname .= $call->did . '-';}
						}
						if (!empty($_POST['fname3'])) {
							if ($_POST['fname3'] == 'DT') {if (!empty($call->tstamp)) $newname .= date("Y_m_d_H_i_s", $call->tstamp) . '-';}
							else if ($_POST['fname3'] == 'AI') {if (!empty($call->agent_id)) $newname .= $call->agent_id . '-';}
							else if ($_POST['fname3'] == 'CL') {if (!empty($call->cli)) $newname .= $call->cli . '-';}
							else if ($_POST['fname3'] == 'DD') {if (!empty($call->did)) $newname .= $call->did . '-';}
						}
						if (!empty($_POST['fname4'])) {
							if ($_POST['fname4'] == 'DT') {if (!empty($call->tstamp)) $newname .= date("Y_m_d_H_i_s", $call->tstamp) . '-';}
							else if ($_POST['fname4'] == 'AI') {if (!empty($call->agent_id)) $newname .= $call->agent_id . '-';}
							else if ($_POST['fname4'] == 'CL'){if (!empty($call->cli))  $newname .= $call->cli . '-';}
							else if ($_POST['fname4'] == 'DD') {if (!empty($call->did)) $newname .= $call->did . '-';}
						}
						
						$newname .= $call->callid . '.gsm';
						$voice_files[] = array($sound_file_gsm, $newname);
					}
				}
			}

			if (count($voice_files) > 0) {
				$zip_file_path = '/tmp/skill-voices.zip';//$this->getTemplate()->voice_logger_path . 'skill-voices.zip';
				if (file_exists($zip_file_path)) unlink($zip_file_path);
				if ($this->create_zip($voice_files, $zip_file_path, true)) {

					$new_file_name = 'skill-voices.zip';
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
				exit;
			} else {
				$data['errMsg'] = 'No voice file found !!';
			}
		}
		
		if ($num_records > 0) {
			$data['num_records'] = $num_records;
			$data['pageTitle'] = 'Download Voice File(s)';
			$data['request'] = $this->getRequest();
			$data['dateinfo'] = $dateinfo;
			$data['skillid'] = $skillid;
			$data['cli'] = $cli;
			$data['did'] = $did;
			$data['aid'] = $aid;
			$data['errType'] = 1;
			$this->getTemplate()->display_popup('report_cdr_skill_voice_download', $data);
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


       function actionAutoDial()
       {
               //error_reporting(0);
               include('model/MReport.php');
               include('model/MSkill.php');
               include('model/MIvr.php');
               include('model/MSetting.php');
               include('lib/Pagination.php');
               include('lib/DateHelper.php');
               include('conf.sms.php');
               $report_model = new MReport();
               $skill_model = new MSkill();
               $ivr_model = new MIvr();
               $setting_model = new MSetting();
               $pagination = new Pagination();

               $dateinfo = DateHelper::get_input_time_details();
               $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
               $extParam = "";
               $isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
               //if ($type != 'outbound') $type = 'inbound';

               $skillid = isset($_REQUEST['skillid']) ? str_replace(array("(", ")", " ", "-"), "", $_REQUEST['skillid']) : "";
               $cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
               $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : "";
               $aid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : "";
               $ivr = isset($_REQUEST['ivr_id']) ? trim($_REQUEST['ivr_id']) : "";
               $skill_status = isset($_REQUEST['skill_status']) ? trim($_REQUEST['skill_status']) : "";
               $errMsg = '';
               $errType = 1;
               $reportDays = UserAuth::getReportDays();
               $repLastDate = "";
               if (!empty($reportDays)) {
                       $toDate = date("Y-m-d");
                       $repLastDate = date('Y-m-d', strtotime($toDate . ' - ' . $reportDays . ' days'));
               }

               $data['pageTitle'] = 'Auto-Dial CDR';
               $data['ivr_options'] = null;
               $template_file = 'report_cdr_auto_dial';

               $data['date_stime'] = date("Y-m-d 00:00");
               $data['date_etime'] = "";
               $data['skillid'] = "";
               $data['cli'] = "";
               $data['did'] = "";
               $data['aid'] = "";
               $data['callid'] = "";
               $data['status'] = "";
               $data['ivr_id'] = "";
               $data['sms_enabled'] = $isEnableSMS;

               //will need for current settings
               $selectArr = array('0'=>'Select');
               $data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
               $data['ivr_options'] = array_merge ( $selectArr , $data['ivr_options']);
               $data['request'] = $this->getRequest();
               $data['reportHeader'] = true;
               $data['side_menu_index'] = 'reports';
               $data['errMsg'] = $errMsg;
               $data['errType'] = $errType;

               if (isset($_POST['search'])) {
                       $audit_text = '';
                       if (!empty($skillid)) {
                               $_opt = isset($data['skill_options'][$skillid]) ? $data['skill_options'][$skillid] : $skillid;
                               $audit_text .= "Skill=$_opt;";
                       }
                       $audit_text .= DateHelper::get_date_log($dateinfo);
                       $report_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
               }

               $session_id = session_id();
               $download = md5($data['pageTitle'].$session_id);
               $dl_link = "&download=$download";
               //Will need to configure
               if (isset($_REQUEST['download']) && $pagination->num_records>0) {
                       $trunk_options = $setting_model->getTrunkOptions();
                       $this->downloadSkillCDR($report_model, $data['pageTitle'],  $this->getTemplate(), $data['ivr_options'], $data['skill_options'], $trunk_options, $type, $skillid, $cli, $did, $aid, $dateinfo);
                       exit;
               }

               $data['dataDlLink'] = $dl_link;
               $data['dataUrl'] = $this->url('task=get-report-data&act=autodialcdr&type='.$type.$extParam);
               $this->getTemplate()->display($template_file, $data);
       }
	
	function actionIvr()
	{
	    $this->actionSkill('ivr');
	}
	
	function actionSkill($logtype = '')
	{
		//error_reporting(0);
		include('model/MReport.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('model/MSetting.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		include('conf.sms.php');
		$report_model = new MReport();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$setting_model = new MSetting();
		$pagination = new Pagination();

		$dateinfo = DateHelper::get_input_time_details();
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
		if ($logtype == 'ivr') $type = 'ivr-inbound'; 
		$extParam = "";
		$isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
		//if ($type != 'outbound') $type = 'inbound';

		$skillid = isset($_REQUEST['skillid']) ? str_replace(array("(", ")", " ", "-"), "", $_REQUEST['skillid']) : "";
		$cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : "";
		$aid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : "";
		$skill_status = isset($_REQUEST['skill_status']) ? trim($_REQUEST['skill_status']) : "";
		$errMsg = '';
		$errType = 1;
		$reportDays = UserAuth::getReportDays();
		$repLastDate = "";
		if (!empty($reportDays)) {
			$toDate = date("Y-m-d");
			$repLastDate = date('Y-m-d', strtotime($toDate . ' - ' . $reportDays . ' days'));
		}
		
		$selectArr = array('0'=>'Select');
		
		if ($type == 'outbound') {
			$data['pageTitle'] = 'Skill CDR - Outbound';
			//$data['cliColumn'] = 'Call From';
			//$data['didColumn'] = 'Call To';
			$data['ivr_options'] = null;
			$template_file = 'report_cdr_skill_outbound';
		} else {
		        if ($type == 'ivr-inbound') {
		            $data['pageTitle'] = 'Ivr Log - Inbound';
		        } else {
			$type = 'inbound';
			$data['pageTitle'] = 'Skill CDR - Inbound';
			}
			//$data['cliColumn'] = 'Caller ID';
			//$data['didColumn'] = 'DID';
			//$data['skill_options'] = $skill_model->getSkillOptions();
			$data['ivr_options'] = array_merge ( $selectArr, $ivr_model->getIvrOptions() );
			$template_file = 'report_cdr_skill';
		}

		$data['date_stime'] = date("Y-m-d 00:00");
		$data['date_etime'] = "";
		$data['skillid'] = "";
		$data['cli'] = "";
		$data['did'] = "";
		$data['aid'] = "";
		$data['callid'] = "";
		$data['status'] = "";
		$data['sms_enabled'] = $isEnableSMS;

		if ($isEnableSMS) {
			if (!empty($_POST['last_sms_text']) && !empty($_POST['ssms_template'])
                && is_array($_POST['cid_selector']) && count($_POST['cid_selector']) > 0)
			{
				$smsText = $_POST['last_sms_text'];
				$destNumbers = $_POST['cid_selector'];
				$smsTemplate = $_POST['ssms_template'];
				//$agent_id = UserAuth::getUserID();
				//$smsText = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $smsText);
				//$smsText = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $smsText);
			/*	if (strlen($smsText) > 250) {
					$smsText = substr($smsText, 0, 250);
				}
                include_once('model/MSms.php');
                $sms_model = new MSms();
                $gateways =  $sms_model->getGatewaysAsKeyValue();
				$smsCounter = 0;
				$totalSMSNumber = is_array($destNumbers) ? count($destNumbers) : 0;
				$smsNumsArr = array();
				foreach ($destNumbers as $dnumber) {
					if (!empty($dnumber)) {
						$callIdMobNum = explode("@", $dnumber);
						$saveCallId = $callIdMobNum[1];
						$mobileNumber = $callIdMobNum[0];
                        $mobileNumber = preg_replace("/[^0-9]/","", $mobileNumber);
                        $skill_id = $callIdMobNum[2];
                        if (!empty($mobileNumber) && strlen($mobileNumber) > 10 && !in_array($mobileNumber, $smsNumsArr)) {

                            $prefix = substr($mobileNumber,0,3);

                            if (in_array("*",$gateways)){
                                $gateway_id = array_search("*");
                            }elseif(array_search($prefix)){
                                $gateway_id = array_search($prefix);
                            }else{
                                continue;
                            }

                            $url = $sms_model->getApiByID($gateway_id);
                            $url = str_replace(array("<number>","<msg>"),array($mobileNumber,$smsText),$url);

                            $isSmsSend = $this->sendSMSTApi($url);
							if ($isSmsSend) {
                                $smsNumsArr[] = $mobileNumber;
								$skill_model->addSendSMSLog($saveCallId, $mobileNumber, $smsText, $smsTemplate,$agent_id,$skill_id);
								$smsCounter++;
							}
						}
					}
				}  */

                $smsCounter = $this->sendMobileSms($destNumbers,$smsText,$smsTemplate,$skill_model);
				$data['smsCounter'] = $smsCounter;
				$extParam = "&sms=Y";
                $data['grid_info_msg'] = "Failed to send SMS";
				if ($smsCounter > 0){
                    $data['grid_info_msg'] = "Total $smsCounter of ". count($destNumbers)." SMS send successfully";
                }

				$sessObject = UserAuth::getCDRSearchParams();
				$dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
				$cli = isset($sessObject->cli) ? $sessObject->cli : "";
				$did = isset($sessObject->did) ? $sessObject->did : "";
				$skillid = isset($sessObject->skillid) ? $sessObject->skillid : "";
				$aid = isset($sessObject->aid) ? $sessObject->aid : "";
				$callid = isset($sessObject->callid) ? $sessObject->callid : "";
				$status = isset($sessObject->status) ? $sessObject->status : "";

				$data['date_stime'] = $dateinfo->sdate . " " . $dateinfo->stime;
				$data['date_etime'] = $dateinfo->edate . " " . $dateinfo->etime;
				$data['skillid'] = $skillid;
				$data['cli'] = $cli;
				$data['did'] = $did;
				$data['aid'] = $aid;
				$data['callid'] = $callid;
				$data['status'] = $status;
			}
		}
		//will need for current settings
		//$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
		/*
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&type=$type&act=skill&skillid=$skillid&cli=$cli&did=$did&" .
				"aid=$aid&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime");
		
		if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
		    $errMsg = UserAuth::getRepErrMsg();
		    $errType = 1;
		    $pagination->num_records = 0;
		}else {		    
		    $pagination->num_records = $report_model->numSkillCDRs($type, $skillid, $cli, $did, $aid, $dateinfo);
		}
		
		//echo "test";die;
		//$report_model->getSkillCDRs($type, $skillid, $cli, $did, $aid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page);

		$data['calls'] = $pagination->num_records > 0 ? 
			$report_model->getSkillCDRs($type, $skillid, $cli, $did, $aid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['calls']) ? count($data['calls']) : 0;
		$data['trunk_options'] = is_array($data['calls']) ? $setting_model->getTrunkOptions() : array();
		$data['pagination'] = $pagination;
		$data['type'] = $type;
		$data['dateinfo'] = $dateinfo;
		$data['skillid'] = $skillid;
		$data['cli'] = $cli;
		$data['did'] = $did;
		$data['aid'] = $aid;
		$data['skill_status'] = $skill_status;
		//$data['skill_options'] = $skill_model->getSkillOptions();
		*/
		$data['request'] = $this->getRequest();
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		
		if (isset($_POST['search'])) {
			$audit_text = '';
			if (!empty($skillid)) {
				$_opt = isset($data['skill_options'][$skillid]) ? $data['skill_options'][$skillid] : $skillid;
				$audit_text .= "Skill=$_opt;";
			}
			$audit_text .= DateHelper::get_date_log($dateinfo);
			$report_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
		}
		$data['type'] = $type;
		/* if ($type=='outbound') {
		    $data['pageTitle'] = 'Skill CDR - Outbound';
		    $data['ivr_options'] = null;
		    $template_file = 'report_cdr_skill_outbound';
		} else {
		    $type = 'inbound';
		    $data['pageTitle'] = 'Skill CDR - Inbound';
		    $data['ivr_options'] = $ivr_model->getIvrOptions();
		    $template_file = 'report_cdr_skill';
		} */
		$session_id = session_id();
		$download = md5($data['pageTitle'].$session_id);
		$dl_link = "&download=$download";
		//Will need to configure
		if (isset($_REQUEST['download']) && $pagination->num_records>0) {
			$trunk_options = $setting_model->getTrunkOptions();
			$this->downloadSkillCDR($report_model, $data['pageTitle'],  $this->getTemplate(), $data['ivr_options'], $data['skill_options'], $trunk_options, $type, $skillid, $cli, $did, $aid, $dateinfo);
			exit;
		}

		/*$data['topMenuItems'] = array(
			array('href'=>'task=cdr&act=skill&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
			array('href'=>'task=cdr&act=skill&type=outbound', 'img'=>'report.png', 'label'=>'Outbound CDR'),
			array('href'=>'task=cdr&act=skill-abandoned&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR Abandoned Calls')
		);*/
		if ($type != 'ivr-inbound') {
        		$data['topMenuItems'] = array(
	    	        	array('href'=>'task=cdr&act=skill&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
	    	        	array('href'=>'task=cdr&act=skill&type=outbound', 'img'=>'report.png', 'label'=>'Outbound CDR')
	        	);
		}

		$data['dataDlLink'] = $dl_link;
		$data['dataUrl'] = $this->url('task=get-report-data&act=skillcdr&type='.$type.$extParam);
		$this->getTemplate()->display($template_file, $data);
	}


	private function sendMobileSms($destNumbers, $smsText, $smsTemplate, $skill_model = null)
    {
        include_once('model/MSms.php');
        $sms_model = new MSms();
        $smsCounter = 0;
        $smsNumsArr = [];

        $smsText = urlencode(strlen($smsText) > 250 ? substr($smsText, 0, 250) : $smsText);



        $gateways =  $sms_model->getGatewaysAsKeyValue();
        $gateway_id_object_key_value = [];

        foreach ($destNumbers as $dnumber) {

            if (empty($dnumber)) { continue; }

            $callIdMobNum = explode("@", $dnumber);
            $saveCallId = $callIdMobNum[1];
            $mobileNumber = $callIdMobNum[0];
            $mobileNumber = preg_replace("/[^0-9]/","", $mobileNumber);
            $skill_id = $callIdMobNum[2];
            $mobileNumber = strlen($mobileNumber) == 13 ? substr($mobileNumber,2) : $mobileNumber;
            $mobileNumberLength = strlen($mobileNumber);
            if (empty($mobileNumber) || $mobileNumberLength < 11 || in_array($mobileNumber, $smsNumsArr)) {
                continue;
            }

            $prefix = $mobileNumberLength == 11 ? substr($mobileNumber,0,3) : substr($mobileNumber,2,3);

            if (in_array("*",$gateways)){
                $gateways = array_flip($gateways);
                $gateway_id = $gateways['*'];
            }elseif(in_array($prefix,$gateways)){
                $gateway_id = array_search($prefix,$gateways);
            }elseif(in_array("**",$gateways)){   // fallback gateway if not common gateway and prefix not found
                $gateways = array_flip($gateways);
                $gateway_id = $gateways['**'];
            }else{
                continue;
            }

            if (array_key_exists($gateway_id, $gateway_id_object_key_value)) {
                $gateway_object = $gateway_id_object_key_value[$gateway_id];
            }else{
                $gateway_object = $sms_model->getApiByID($gateway_id);
                $gateway_id_object_key_value[$gateway_id] = $gateway_object;
            }


            $url = $gateway_object->api;
            if (empty($url)){
                continue;
            }

            $url = str_replace(array("<number>","<msg>"),array($mobileNumber, $smsText), $url);

            if ($this->sendSMSTApi($url)) {
                $agent_id =  UserAuth::getUserID();
                $smsNumsArr[] = $mobileNumber;
                $skill_model->addSendSMSLog($saveCallId, $mobileNumber, urldecode($smsText), $smsTemplate,$agent_id,$skill_id);
                $smsCounter++;
            }
        }

        return $smsCounter;
    }
	
	function actionSkillcdrdownload(){
		include('model/MReport.php');
		$report_model = new MReport();
	
		$sessObject = UserAuth::getCDRSearchParams();
		$dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
		$cli = isset($sessObject->cli) ? $sessObject->cli : "";
		$did = isset($sessObject->did) ? $sessObject->did : "";
		$skillid = isset($sessObject->skillid) ? $sessObject->skillid : "";
		$aid = isset($sessObject->aid) ? $sessObject->aid : "";
		$callid = isset($sessObject->callid) ? $sessObject->callid : "";
		$status = isset($sessObject->status) ? $sessObject->status : "";
		$type = isset($sessObject->type) ? $sessObject->type : "inbound";
	
		$file_name = $report_model->getCdrCsvSavePath() . 'skill_cdr_' . $type . '.' . time() . '.csv';
		$title = "Skill CDR - Inbound";
		if ($type == 'outbound') {
			$title = 'Skill CDR - Outbound';
		}
	
		if (file_exists($file_name)) unlink($file_name);
		
		$is_success = $report_model->downloadSkillCDRFile($file_name, $type, $skillid, $cli, $did, $aid, $callid, $status, $dateinfo);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $this->getTemplate());
			$dl_helper->set_local_file($file_name);
			if ($type == 'outbound') {
				$dl_helper->download_file('', "Start time,Stop time,Agent,Nick name,ANI num,Call to,Skill,Status,Talk time,Service time,Disc. cause,Disc. party,Trunk,Callid\n");
			} else {
				$dl_helper->download_file('', "Start time,Stop time,Caller ID,DID,IVR enter time,IVR,Time in IVR,IVR language,Skill enter time,Skill,Hold in queue,Agent ID,Nick name,Status,Service time,".
						"Total time,Missed call,Disc party,Call ID\n");//Total time,
			}
		}
	}
	
	function actionSipLog(){
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		
		if (strlen($cid) >= 15 && strlen($cid) < 25 && ctype_digit($cid)) {
			$direction = isset($_REQUEST['dir']) ? trim($_REQUEST['dir']) : "";
			$tstamp = substr($cid, 0, 10);
			$yy_mm_dd = date("y/m/d", $tstamp);
			
			$backup_yy_mm_dd = "";
		        if ($this->getTemplate()->backup_transfer_aft_month > 0) {
                    		$backup_yy_mm_dd = date("y/m/d", strtotime("-".$this->getTemplate()->backup_transfer_aft_month." months"));
                	}
                	if ($yy_mm_dd > $backup_yy_mm_dd) {
                	    $sip_path = $this->getTemplate()->sip_logger_path;
                	} else {
                	    $sip_path = $this->getTemplate()->backup_hdd_path;
                	}

			$sip_file = $sip_path . "message/$yy_mm_dd/" . $cid . ".sip";
			//echo $sip_file;
			if (file_exists($sip_file)) {
				$data=array();
				$data['sipDlUrl'] = $this->url("task=cdr&act=dowloadSip&cid=".$cid);
				$data['filePath'] = $sip_file;
				include('model/MReport.php');
				$rpt_model = new MReport();
				$data['skillinfo'] = $rpt_model->getSkillFromCallid($cid, $direction);
				if ($data['skillinfo']->qtype == 'C') {
					include('model/MChatTemplate.php');
					$cm = new MChatTemplate();
					$data['chatDetailLog'] = $cm->getChatLogByCallId($cid);
					$data['pageTitle'] = 'Chat Log #'. $cid;
				} else {
					$data['pageTitle'] = 'Sip Log #'. $cid;
				}
				$this->getTemplate()->display_popup("sip-log", $data,true);
			}
		}
	}
	function actionChatLog(){
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		if (strlen($cid) >= 10) {
			$tstamp = substr($cid, 0, 10);
			$yy_mm_dd = date("y/m/d", $tstamp);
			$sip_file = $this->getTemplate()->sip_logger_path . "message/$yy_mm_dd/" . $cid . ".sip";
			
			$array = array();
            if (file_exists($sip_file)) {
				//$fh = fopen($sip_file, 'r');
				$lines = file($sip_file);
				
				/*
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\r\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					//$timestamp = date("Y-m-d h:m:d a",$timestamp);
					$timestamp = date("h:m:d a",$timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\r\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = $line2_ary[1];
					
					$from_type = strlen($name)==4 && is_numeric($name) ? "AGNT" : "CUST";
					
					$array[] = array("name"=>$name,"timestamp"=>$timestamp,"msg"=>$msg,"from_type"=>$from_type);
				}*/
				//print_r($array);
				
				$message_array = array();
				
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\r\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					$timestamp = date("Y-m-d h:i:s a",$timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\r\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = base64_decode($line2_ary[1]);
					
					$msg_ary = explode("|",$msg);
					$index = $msg_ary[0];
					$message = $msg_ary[1];
					$packet_no = $msg_ary[3];
					
					$from_type = strlen($name)==4 && is_numeric($name) ? "AGNT" : "CUST";
					$message_array[$index]["name"] = $name;
					$message_array[$index]["timestamp"] = $timestamp;
					$message_array[$index]["msg"][$packet_no] = $message;
					$message_array[$index]["from_type"] = $from_type;
				}
				
				foreach($message_array as $index=>$data) {
					//$html .= "<tr><td width='100'>".$data["name"]."</td><td>---".$data["timestamp"]."</td></tr>";
					$msg = implode("",$data["msg"]);
					$data["msg"] = $msg;
					$message_array[$index] = $data;
					//$html .= "<tr><td colspan='2'>".base64_decode($msg)."</td></tr>";
				}

				echo json_encode($message_array);
				exit;
                //$data=array();
                //$data['pageTitle'] = 'Sip Log #'. $cid;
				//$data['filePath'] = $sip_file;
				//$this->getTemplate()->display_popup("sip-log", $data,true);
			}
		}
	}
	function actionListen(){
		$page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : "";
		$agentId = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : "";
		$cid = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : "";
		$pageSection = isset($_REQUEST['cdr_page']) ? trim($_REQUEST['cdr_page']) : "Play voice file";
		if (!empty($cid)){
			include('model/MReport.php');
			$reportModel = new MReport();
			$reportModel->addToAuditLog($pageSection, "V", "", "Listen voice file");
			include('cc_web_agi.php');
		}
	}
	
	function actionPlayvideo()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		$data['pageTitle'] = 'Playing Screen Log Video';
		$data['cid'] = $cid;
		$data['main_url'] = site_url().base_url();
		$data['request'] = $this->getRequest();
		$data['video_path'] = $this->getTemplate()->getBasePath() . $this->getTemplate()->bkp_audio_url;
		$this->getTemplate()->display_popup('report_cdr_play_video', $data);
	}
	
	function actionProcessvideo()
	{
		$cid = isset($_POST['cid']) ? trim($_POST['cid']) : "";
		
		if (!empty($cid)) {
			include('model/MReport.php');
			$reportModel = new MReport();
			$reportModel->addToAuditLog($pageSection, "V", "", "Play video file");
			$file_timestamp = substr($cid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			$voice_logger_files_path = $this->getTemplate()->voice_logger_path . "vlog/$yyyy/$yyyy_mm_dd/" . $cid;
			$screen_logger_files_path = $this->getTemplate()->screen_logger_path . "screen_log/$yyyy/$yyyy_mm_dd/" . $cid;
			
			$sound_file_gsm = $voice_logger_files_path . ".gsm";
			$video_file_zip = $screen_logger_files_path . ".zip";
			$audio_file = $voice_logger_files_path . ".mp3";
			
			$temp_path = 'temp/' . $cid . '/';
			
			if (!file_exists($temp_path) && !file_exists($temp_path.$cid.".mp4")) {
				mkdir($temp_path, 0755, true);				
				// $result = shell_exec("/usr/bin/openssl enc -d -aes-256-cbc -k 'skI1ls*7JfptUDgm~jGOko56Is' -in ".$sound_file_gsm." -out ".$temp_path. $cid . ".gsm");
				//var_dump("unzip " . $video_file_zip . " -d ".BASEPATH.$temp_path);
				$result = shell_exec("unzip " . $video_file_zip . " -d ".BASEPATH.$temp_path);
				// $result = shell_exec("/usr/local/ffmpeg/ffmpeg -f concat -i ".$temp_path."data.txt -i ".$temp_path.$cid.".gsm -pix_fmt yuv420p -c:a aac -strict -2 ".$temp_path.$cid.".mp4");
				 //var_dump("/usr/bin/ffmpeg -nostats -loglevel 0 -f concat -i ".BASEPATH.$temp_path."screenLog/data.txt -i ".$audio_file." -s 1024x768 -crf 25 -vcodec libx264 -pix_fmt yuv420p ".BASEPATH.$temp_path.$cid.".mp4");
				 //die();
				$result = shell_exec("/usr/bin/ffmpeg -nostats -loglevel 0 -f concat -i ".BASEPATH.$temp_path."screenLog/data.txt -i ".$audio_file." -s 1024x768 -crf 25 -vcodec libx264 -pix_fmt yuv420p ".BASEPATH.$temp_path.$cid.".mp4");

				echo $temp_path.$cid.".mp4";exit;
			}else{
				echo $temp_path.$cid.".mp4";
				exit;
			}

			//$this->getTemplate()->redirect($sound_file_gsm);
		}
		
		echo '';
	}
	
	function actionPlaybrowser(){
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		$pageSection = isset($_REQUEST['cdr_page']) ? trim($_REQUEST['cdr_page']) : "Play voice file";
		$sound_file_gsm = "";
		if (!empty($cid)) {
			include('model/MReport.php');
			$reportModel = new MReport();
			$reportModel->addToAuditLog($pageSection, "V", "", "Download voice file");
			$file_timestamp = substr($cid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			
			$backup_yy_mm_dd = "";
                        if ($this->getTemplate()->backup_transfer_aft_month > 0) {
                                $backup_yy_mm_dd = date("Y_m_d", strtotime("-".$this->getTemplate()->backup_transfer_aft_month." months"));
                        }
                        if ($yyyy_mm_dd > $backup_yy_mm_dd) {
                            $vlog_path = $this->getTemplate()->voice_logger_path;
                        } else {
                            $vlog_path = $this->getTemplate()->backup_hdd_path;
                        }

			$sound_file_gsm = $vlog_path . "vlog/$yyyy/$yyyy_mm_dd/" . $cid . ".wav";
			if (!file_exists($sound_file_gsm)) {
			    $sound_file_gsm = $vlog_path . "vlog/$yyyy/$yyyy_mm_dd/" . $cid . ".mp3";
			    if (!file_exists($sound_file_gsm)) {
			        $sound_file_gsm = '';
			    }
			}
			//$this->getTemplate()->redirect($sound_file_gsm);
			if (!empty($sound_file_gsm)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($sound_file_gsm).'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($sound_file_gsm));
				readfile($sound_file_gsm);
				exit;
			}
		}
	}
	
	function actionListenAudioFile(){
	    $cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
	    $pageSection = isset($_REQUEST['cdr_page']) ? trim($_REQUEST['cdr_page']) : "Play voice file";
	    $sound_file_gsm = "";
	    if (!empty($cid)) {
	        include('model/MReport.php');
	        $reportModel = new MReport();
	        $reportModel->addToAuditLog($pageSection, "V", "", "Listen voice file");
	        $file_timestamp = substr($cid, 0, 10);
	        $yyyy = date("Y", $file_timestamp);
	        $yyyy_mm_dd = date("Y_m_d", $file_timestamp);
	        
	        $backup_yy_mm_dd = "";
                if ($this->getTemplate()->backup_transfer_aft_month > 0) {
                	$backup_yy_mm_dd = date("Y_m_d", strtotime("-".$this->getTemplate()->backup_transfer_aft_month." months"));
                }
                if ($yyyy_mm_dd > $backup_yy_mm_dd) {
                	$vlog_path = $this->getTemplate()->voice_logger_path;
                } else {
                        $vlog_path = $this->getTemplate()->backup_hdd_path;
                }

	        $sound_file_gsm = $vlog_path . "vlog/$yyyy/$yyyy_mm_dd/" . $cid . ".wav";
	        if (!file_exists($sound_file_gsm)) {
	            $sound_file_gsm = $vlog_path . "vlog/$yyyy/$yyyy_mm_dd/" . $cid . ".mp3";
	            if (!file_exists($sound_file_gsm)) {
	                $sound_file_gsm = '';
	            }
	        }
	        //$this->getTemplate()->redirect($sound_file_gsm);
	        if (!empty($sound_file_gsm)) {
	            header("Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3");
	            header('Accept-Ranges: bytes');
	            header('Content-Range: bytes 0-1025/' . filesize($sound_file_gsm));
	            header('Content-Length: ' . filesize($sound_file_gsm));
	            /* header('Content-Description: File Transfer');
	            header("Content-Transfer-Encoding: binary");
	            header("Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3");
	            header('Content-Disposition: attachment; filename="'.basename($sound_file_gsm).'"');
	            header('Expires: 0');
	            header('Cache-Control: must-revalidate');
	            header('Pragma: public');
	            header('Content-Length: ' . filesize($sound_file_gsm)); */
	            echo readfile($sound_file_gsm);
	            exit;
	        }
	    }
	}
	
	function actionDowloadSip(){
	    $cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
	    $filePath = "";
	    $fileName = "";
	    if (strlen($cid) >= 10 && strlen($cid) <= 25 && ctype_digit($cid)) {
	        $direction = isset($_REQUEST['dir']) ? trim($_REQUEST['dir']) : "";
	        $tstamp = substr($cid, 0, 10);
	        $yy_mm_dd = date("y/m/d", $tstamp);
	        $sip_file = $this->getTemplate()->sip_logger_path . "siplog/$yy_mm_dd/" . $cid . ".sip";
	        if (file_exists($sip_file)) {
	            $data=array();
	            $filePath = $sip_file;
	            include('model/MReport.php');
	            $rpt_model = new MReport();
	            $skillinfo = $rpt_model->getSkillFromCallid($cid, $direction);
	            if ($skillinfo->qtype == 'C') {
	            	include('model/MChatTemplate.php');
                        $cm = new MChatTemplate();
                        $chatDetailLog = $cm->getChatLogByCallId($cid);
	                $fileName = "Chat_Log_".$cid;
	            } else {
	                $fileName = "Sip_Log_".$cid;
	            }
	        }
	    }
	    
		if(!empty($filePath) && file_exists($filePath)){
	        $contents = "";
	        $fileName = 'temp/' . $fileName.".txt";
	        $fh = fopen($filePath, 'r');
    	    
    	    if (!empty($skillinfo) && $skillinfo->qtype == 'C') {
    	    	$i = 0;
    	    	$msgId = '';
    	    	$message = '';
    	    	$countChunk = 0;
    	    	$totalChunk = 0;
    	    	$isChunkUsed = false;
    	    	$line_ts = "";
    	    	
    	    	if (!empty($chatDetailLog)) {
            		$contents .= "Client Name: $chatDetailLog->client_name \r\nPhone: $chatDetailLog->contact_number \r\nEmail: $chatDetailLog->email \r\n";
			$contents .= "Service: $chatDetailLog->service_name \r\n";
            		if (!empty($chatDetailLog->title)) $contents .= "Disposition: $chatDetailLog->title \r\n";
            		if (!empty($chatDetailLog->note)) $contents .= "Note: $chatDetailLog->note \r\n";

            		$contents .= "\r\n";
        	}

    	    	while(!feof($fh)){
    	    		$line = fgets($fh);
    	    		if ($line[0] != '#') {
    	    			$pos = strpos($line, ':');
    	    			if ($pos !== false) {
    	    				$l1 = substr($line, 0, $pos);
    	    				$l2 = substr($line, $pos+1);
    	    				$msgArray = explode("|", base64_decode($l2));
    	    				
    	    				if (count($msgArray) > 1) {
    	    					$isChunkUsed = true;
    	    					if ($msgId != $msgArray[0] || $msgArray[2] == 0){
    	    						$msgId = $msgArray[0];
    	    						$countChunk = 0;
    	    						$totalChunk = $msgArray[2];
    	    						$message = $msgArray[1];
    	    					}else {
    	    						$countChunk++;
    	    						$message .= $msgArray[1];
    	    					}
    	    				}else {
    	    					$isChunkUsed = false;
    	    					$message = $msgArray[0];
    	    				}
    	    				
    	    				if ($isChunkUsed){
    	    					if ($countChunk == $totalChunk) {
    	    						$message = base64_decode($message);
    	    						$breaks = array("<br />", "<br>", "<br/>");
    	    						$message = str_ireplace($breaks, "\r\n", $message);
    	    						if ($message == '.') $line = '';
    	    						else $line = trim($l1) . ' : ' . $message . "\r\n";
    	    					}else {
    	    						continue;
    	    					}
    	    				}else {
    	    					$breaks = array("<br />", "<br>", "<br/>");
    	    					$message = str_ireplace($breaks, "\r\n", $message);
    	    					if ($message == '.') $line = '';
    	    					else $line = trim($l1) . ' : ' . $message . "\r\n";
    	    				}
    	    			}
    	    		} else {
    	    			if ($isChunkUsed){
    	    				if ($countChunk == $totalChunk) {
    	    					$line_ts = date("Y-m-d H:i:s", substr($line, 2, 10)) . "\r\n";
    	    					if ($i > 0) {
    	    						$line_ts = "\r\n" . $line_ts;
    	    					}
    	    					$i++;
    	    				}else {
    	    					continue;
    	    				}
    	    			}else {
    	    				$line_ts = date("Y-m-d H:i:s", substr($line, 2, 10)) . "\r\n";
    	    				if ($i > 0) {
    	    					$line_ts = "\r\n" . $line_ts;
    	    				}
    	    				$i++;
    	    			}
    	    			$line = '';
    	    		}
    	    		if (!empty($line)) {
    	    			$contents .= $line_ts . $line;
    	    			$line = '';
    	    			$line_ts = '';
    	    		}
    	    	}
    	    } else {
    	    	$line = fread($fh, filesize($filePath));
    	    	$contents .= $line;
    	    }
    	    fclose($fh);

    	    $handle = fopen($fileName, "w");
    	    fwrite($handle, $contents);
    	    fclose($handle);
    	    
    	    header('Content-Type: application/octet-stream');
    	    header('Content-Disposition: attachment; filename='.basename($fileName));
    	    header('Expires: 0');
    	    header('Cache-Control: must-revalidate');
    	    header('Pragma: public');
    	    header('Content-Length: ' . filesize($fileName));
    	    readfile($fileName);
    	    exit;
	    }
	}
	
	function downloadSkillCDR($report_model, $title, $template, $ivr_options, $skill_options, $trunk_options, $type, $skillid, $cli, $did, $aid, $dateinfo)
	{
		//path also used in model function
		$file_name = $report_model->getCdrCsvSavePath() . 'skill_cdr_' . $type . '.csv';

		$is_success = $report_model->prepareSkillCDRFile($type, $skillid, $cli, $did, $aid, $dateinfo, $file_name, $ivr_options, $skill_options, $trunk_options);
		if ($is_success) {
			require_once('lib/DownloadHelper.php');
			$dl_helper = new DownloadHelper($title, $template);
			$dl_helper->set_local_file($file_name);
			if ($type == 'outbound') {
				$dl_helper->download_file('', "Start time,Stop time,Agent,ANI num,Call to,Skill,Status,Talk time,Service time,Disc. cause,Disc. party,Trunk\n");
			} else {
				$dl_helper->download_file('', "Start time,Stop time,Caller ID,DID,IVR enter time,IVR,Time in IVR,IVR language,Skill enter time,".
					"Skill,Hold in queue,Status,Service time,Agent,Alarm,Disc. cause,Disc. party,Trunk\n");//Total time,
			}
		}
	}


	function actionSkillAbandoned()
	{
		include('model/MReport.php');
		include('model/MSkill.php');
		include('model/MIvr.php');
		include('model/MSetting.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$setting_model = new MSetting();
		$pagination = new Pagination();

		$dateInfoObj = new stdClass();
		//$dateinfo = DateHelper::get_input_time_details();
		$type = "inbound";
		$dateTimeArr = array();
		$dateTimeArr['from'] = isset($_POST['start_time']) ? trim($_POST['start_time']) : "";
		$dateTimeArr['to'] = isset($_POST['end_time']) ? trim($_POST['end_time']) : "";
		if (!empty($_GET['sdate']) && !empty($_GET['stime'])){
			$dateTimeArr['from'] = $_GET['sdate'] . " " . $_GET['stime'];
			$dateTimeArr['to'] = $_GET['edate'] . " " . $_GET['etime'];
		}
		$dateInfoObj->sdate = $dateTimeArr['from'];
		$dateInfoObj->edate = $dateTimeArr['to'];

		$skillid = isset($_REQUEST['skillid']) ? str_replace(array("(",")"," ","-"), "", $_REQUEST['skillid']) : "";
		$cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
		$did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : "";
		$aid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : "";
		$callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : "";
		$skill_status = "A";
		$status = "A";
		$errMsg = '';
		$errType = 1;
		$dateinfo = DateHelper::get_cc_time_details($dateTimeArr);

		$type = 'inbound';
		$data['pageTitle'] = 'Inbound Skill CDR - Abandoned';
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		$template_file = 'report_cdr_skill_abandoned';

		//will need for current settings
		$selectArr = array('0'=>'Select');
		$data['skill_options'] = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );

		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=skill-abandoned&skillid=$skillid&cli=$cli&did=$did&" .
			"aid=$aid&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime");

		$pagination->num_records = $report_model->numSkillCDRs($type, $skillid, $cli, $did, $aid, $callid, $dateinfo, $status);

		//$data['calls'] = $pagination->num_records > 0 ? $report_model->getSkillCDRs($type, $skillid, $cli, $did, $aid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$data['calls'] = $pagination->num_records > 0 ? $report_model->getSkillCDRs($type, $skillid, $cli, $did, $aid, $callid, $status, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page, $status) : null;
		$pagination->num_current_records = is_array($data['calls']) ? count($data['calls']) : 0;
		$data['trunk_options'] = is_array($data['calls']) ? $setting_model->getTrunkOptions() : array();
		$data['pagination'] = $pagination;
		$data['type'] = $type;
		$data['dateinfo'] = $dateInfoObj;
		$data['skillid'] = $skillid;
		$data['cli'] = $cli;
		$data['did'] = $did;
		$data['aid'] = $aid;
		$data['callid'] = $callid;
		$data['skill_status'] = $skill_status;
		//$data['skill_options'] = $skill_model->getSkillOptions();
		$data['request'] = $this->getRequest();
		$data['reportHeader'] = true;
		$data['side_menu_index'] = 'reports';
		$data['smi_selection'] = 'cdr_skill';
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['smsCounter'] = "empty";
		$data['currentPage'] = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;

		if (isset($_POST['search'])) {echo "Searched...";var_dump($_POST['cid_selector']);
			$audit_text = '';
			if (!empty($skillid)) {
				$_opt = isset($data['skill_options'][$skillid]) ? $data['skill_options'][$skillid] : $skillid;
				$audit_text .= "Skill=$_opt;";
			}
			$audit_text .= DateHelper::get_date_log($dateinfo);
			$report_model->addToAuditLog($data['pageTitle'], 'V', "", $audit_text);
		}
		if (!empty($_POST['smsTextLast']) && !empty($_POST['send_sms_now'])
            && $_POST['send_sms_now'] == 'Y' && is_array($_POST['cid_selector'])
            && count($_POST['cid_selector']) > 0)
		{

			$smsText = $_POST['smsTextLast'];
			$destNumbers = $_POST['cid_selector'];
            $smsTemplate = !empty($_POST['ssms_template']) ? $_POST['ssms_template'] : '';

		  /*
			if (strlen($smsText) > 250){
				$smsText = substr($smsText, 0, 250);
			}

            include_once('model/MSms.php');
            $sms_model = new MSms();
            $gateways =  $sms_model->getGatewaysAsKeyValue();
			$smsCounter = 0;
			foreach ($destNumbers as $dnumber){
				if (!empty($dnumber)){
					$callIdMobNum = explode("@", $dnumber);
					$saveCallId = $callIdMobNum[1];
					$mobileNumber = $callIdMobNum[0];
					$sms_skill_id = $callIdMobNum[2];
					if (!empty($mobileNumber) && strlen($mobileNumber) > 10){
					    $prefix = substr($mobileNumber,0,3);

					    if (in_array("*",$gateways)){
					        $gateway_id = array_search("*");
                        }elseif(array_search($prefix)){
					        $gateway_id = array_search($prefix);
                        }else{
					        continue;
                        }

                        $url = $sms_model->getApiByID($gateway_id);
					    $url = str_replace(array("<number>","<msg>"),array($mobileNumber,$smsText),$url);


						$isSmsSend = $this->sendSMSTApi($url);
						if ($isSmsSend){
						    $agent = UserAuth::getUserID();
							$skill_model->addSendSMSLog($saveCallId, $mobileNumber, $smsText,$agent, $sms_skill_id);
							$smsCounter++;
						}
					}
				}
			}

			*/
			$data['smsCounter'] = $this->sendMobileSms($destNumbers,$smsText,$smsTemplate, $skill_model);
		}

		$session_id = session_id();
		$download = md5($data['pageTitle'].$session_id);
		$dl_link = "&download=$download";
		//Will need to configure
		if (isset($_REQUEST['download']) && $pagination->num_records>0) {
			$trunk_options = $setting_model->getTrunkOptions();
			$this->downloadSkillCDR($report_model, $data['pageTitle'],  $this->getTemplate(), $data['ivr_options'], $data['skill_options'], $trunk_options, $type, $skillid, $cli, $did, $aid, $dateinfo);
			exit;
		}

		$data['topMenuItems'] = array(
			array('href'=>'task=cdr&act=skill&type=inbound', 'img'=>'report.png', 'label'=>'Inbound CDR'),
			array('href'=>'task=cdr&act=skill&type=outbound', 'img'=>'report.png', 'label'=>'Outbound CDR')
		);
		//$data['dataDlLink'] = $dl_link;
		//$data['dataUrl'] = $this->url('task=get-report-data&act=skillcdr&type='.$type);
		$this->getTemplate()->display($template_file, $data);
	}

	function actionSmstemplate()
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		$request = $this->getRequest();

		$data['templates'] = $et_model->getSmsTemplates(0, 0, 'Y');
		$data['pageTitle'] = 'SMS Templates';
		$data['request'] = $request;
		$data['errMsg'] = '';
		$data['errType'] = 0;
		$this->getTemplate()->display_popup('sms_template_options', $data);
	}

	function sendSMSTApi($url)
    {
        include_once('crm_in_data.php');
        if (empty($url)){
            return false;
        }
        $response = call_extn_api($url);

        return isset($response['Status']) && ($response['Status'] == 0) ? true : false;
       /* if (empty($mobileNumber) || empty($smsText)) return false;

        try {
            include('conf.sms.php');
            ini_set("soap.wsdl_cache_enabled", 0);

            $is_enable_sms = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
            if ($is_enable_sms){
                $WSDL_URL = $smsConfObj->wsdl_url;
                $data = $smsConfObj->data_param;
                $mobileNumber = preg_replace("/[^0-9]/","", $mobileNumber);
                $data['mobileNumber'] = $mobileNumber;
                $data['smsText'] = $smsText;
                $auth = new StdClass();
                foreach($data as $dataKey => $dataVal){
                    $auth->$dataKey = $dataVal;
                }
                $client = new SoapClient ( $WSDL_URL );
                $auth2 = new StdClass();
                $auth2->request = $auth;
                $result = $client->SendSMS( $auth2 );

                if (isset($result->return)){
                    if (!empty($result->return->responseCode) && $result->return->responseCode == '100'){
                        return true;
                    }elseif (!empty($result->return->responseCode) && $result->return->responseCode == '000'){
                        return false;
                    }
                }elseif (isset($result['return'])){
                    if (!empty($result['return']->responseCode) && $result['return']->responseCode == '100'){
                        return true;
                    }elseif (!empty($result['return']->responseCode) && $result['return']->responseCode == '000'){
                        return false;
                    }
                }

                if (is_soap_fault ( $result )) {
                    return false;
                }
                unset ( $client );
            }else{
                return false;
            }
        } catch ( SoapFault $fault ) {
            return false;
        } */

        return FALSE;
    }

    public function ActionRateCallQuality()
    {
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $call_quality_profiles = $agent_model->getActiveCallQualityProfiles($id='');

        $request = $this->getRequest();
        $call_id = $request->getRequest('callid');
        $call_time = $request->getRequest('call_time');
        $skill_id = $request->getRequest('skill_id');
        $agent_id = $request->getRequest('agent_id');
        $ratings = $request->getRequest('rating');

        if ($request->isPost()){
            if($agent_model->AddCallQualityRating($call_id,$call_time,$skill_id,$agent_id,$ratings))
            {
                AddInfo("Call Quality Rating Added Successfully");
                $this->getTemplate()->display_popup_msg(array("isAutoResize"=>"true"),TRUE);
            }else{
                AddError("Failed to Add Call Quality Rating");
            }
        }

        $data['pageTitle'] = 'Service Quality Rating';
        $data['request'] = $request;
        $data['current_ratings'] = $agent_model->getServiceQualityRatingByCallID($call_id);
        $data['quality_profiles'] = $call_quality_profiles;
        $this->getTemplate()->display_popup('rate_call_quality', $data,TRUE);

    }
}
