<?php
use Dompdf\Dompdf;
class Agent extends Controller
{
	function __construct() {		
		parent::__construct();
	}

	function init()
	{
		include('model/MSkill.php');
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$skill_model = new MSkill();
		$agentid = UserAuth::getCurrentUser();
		$data['agent'] = $agent_model->getAgentById($agentid);
		if (empty($data['agent'])) exit;
		$data['agent_model'] = $agent_model;
		$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module, '', 'array');
		$data['pageTitle'] = 'Home';
		$this->getTemplate()->display('agent_info', $data);
	}
	
	function actionAddWebSockLoggedIn()
	{
		UserAuth::addWSLoggedIn();
	}
	
	function actionAddDashboardLoggedIn()
	{
		UserAuth::addDBLoggedIn();
	}
	
	function actionAddWebSockLoggedOut()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();
		$agentid = UserAuth::getCurrentUser();
		$ocxtoken=$agent_model->GenerateToken($agentid);
		UserAuth::resetWSLoggedIn($ocxtoken);
	}
	
	function actionGetPdSkills()
	{
	    include('model/MSkill.php');
	    $skill_model = new MSkill();
	    $agentid = UserAuth::getCurrentUser();
	    $skills = $skill_model->getSkillsForAgent($agentid, 'P', '1');
	    echo json_encode($skills);
	}
	
	function actionGetFootprints()
	{
	    include('model/MReportNew.php');
	    $rp_model = new MReportNew();
	    $callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : "";
	    list($callid, $tmp) = explode('-', $callid);
	    $result = $rp_model->get_ivr_footprint($callid);
	    $title = '';
	    $data = '';
	    if ($result) {
	        $data = "<table border='0'>";
	        foreach ($result as $row) {
	            if (empty($title)) $title = $row->service_title;
	            $data .= "<tr><td>".date("m/d/y H:i", strtotime($row->log_time))."</td><td>$row->service_title</td></tr>";
                }
                $data .= "</table>";
	    }
	    $resp = new stdClass();
	    $resp->title = $title;
	    $resp->data = $data;
	    header('Content-Type: application/json');
	    echo json_encode($resp);
	    exit;
	}
	
	function actionAddDashboardLoggedOut()
	{
		UserAuth::resetWSLoggedIn();
	}
	
	function actionEmailChatHistory()
	{
		require_once("lib/dompdf/autoload.inc.php");		

		/*
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		if (strlen($cid) >= 10) {
			$tstamp = substr($cid, 0, 10);
			$yy_mm_dd = date("y/m/d", $tstamp);
			$sip_file = $this->getTemplate()->sip_logger_path . "siplog/$yy_mm_dd/" . $cid . ".sip";
			if (file_exists($sip_file)) {
			}
		}
		*/
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		$cid_ary = explode("-",$cid);
		$cid = $cid_ary[0];
		$mailid = isset($_REQUEST['mailid']) ? trim($_REQUEST['mailid']) : "";	
		$skill_id = isset($_REQUEST['skill_id']) ? trim($_REQUEST['skill_id']) : "";		
		
		
		$filename = BASEPATH."temp/".$cid.".pdf";
		
		if (strlen($cid) >= 10) {
			$tstamp = substr($cid, 0, 10);
			$yy_mm_dd = date("y/m/d", $tstamp);
			// var_dump($this->getTemplate()->sip_logger_path);
			// var_dump(UserAuth::getDBSuffix());
			// var_dump($_SESSION);
			$sip_file = $this->getTemplate()->sip_logger_path.(!empty(UserAuth::getDBSuffix()) ? UserAuth::getDBSuffix() : 'AA'). "/siplog/$yy_mm_dd/" . $cid . ".sip";
						
			//$fp = fopen($filename, "w");
            if (file_exists($sip_file)) {
				//$fh = fopen($sip_file, 'r');
				$lines = file($sip_file);
				/*
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\r\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					$timestamp = date("Y-m-d h:i:s a",$timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\r\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = $line2_ary[1];
					
					//fwrite($fp, "\r\n" . trim($name) . " --- $timestamp\r\n");
					fwrite($fp,"$name --- $timestamp\r\n");
					fwrite($fp,base64_decode($msg)."\r\n");
				}*/
				
				$message_array = array();
				
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\r\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					$timestamp = date("d/m/Y h:i:s a", $timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\r\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = base64_decode($line2_ary[1]);
					
					$msg_ary = explode("|",$msg);
					$index = $msg_ary[0];
					$message = $msg_ary[1];
					$packet_no = $msg_ary[3];
					
					if ($message != '.') {
						$message_array[$index]["name"] = $name;
						$message_array[$index]["timestamp"] = $timestamp;
						$message_array[$index]["msg"][$packet_no] = $message;
					}
					
					//fwrite($fp,"$name --- $timestamp\r\n");
					//fwrite($fp,base64_decode($msg)."\r\n");
				}
				/*
				foreach($message_array as $data) {
					fwrite($fp, $data["name"]." --- ".$data["timestamp"]."\r\n");
					$msg = implode("",$data["msg"]);
					fwrite($fp,$msg."\r\n");
				}*/
				
				$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
				$html .= '<style type="text/css">html, body {font-family: solaimanlipi !important;}</style>';
				$html .= '</head><body><table>';
				foreach($message_array as $data) {
					/*
					fwrite($fp, $data["name"]." --- ".$data["timestamp"]."\r\n");
					$msg = implode("",$data["msg"]);
					fwrite($fp,$msg."\r\n");*/
					$msg = implode("",$data["msg"]);
                                        $msg = base64_decode($msg);
                                        // var_dump($msg);
                                        if ($msg != ".") {
                                                $html .= "<tr><td width='100'>" . $data["name"] . "</td><td>---" . $data["timestamp"] . "</td></tr>";
                                                $html .= "<tr><td colspan='2'>" . $msg . "</td></tr>";
                                        }
				}
				$html .= "</table></body></html>";
				// $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
				// var_dump($html);				

				if ( get_magic_quotes_gpc() )
					$html = stripslashes($html);
			
				$old_limit = ini_set("memory_limit", "16M");
			
				$dompdf = new Dompdf();
				$dompdf->set_option('isHtml5ParserEnabled', true);
				$dompdf->loadHtml($html, '');
				// $dompdf->load_html($html);
				// $dompdf->set_paper("a4");
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->render();
				$pdf = $dompdf->output();
			
				//$dompdf->stream($voucherId.".pdf");
				
				file_put_contents($filename,$pdf);
				
				
			} else {
				echo "File not exists: ".$sip_file;
				//fclose($fp);
				return;
			}
			fclose($fp);
		} else {
			echo "Wrong call id: ".$cid;
			return;
		}
		
		// die('pdf');

		//include_once('conf.email.php');
		include_once('model/MCcSettings.php');
		$settings_model = new MCcSettings();
		$settings_model->module_type = MOD_CHAT;
		$settings_data = $settings_model->getFormatAllSettings();

		
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/Exception.php' );
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/PHPMailer.php' );
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/SMTP.php' );
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);
		
		// $mail->isSMTP();
		// $mail->PluginDir  = '';
		// $mail->SMTPDebug  = 0;
		// $mail->SMTPAuth   = true;
		// $mail->Port       = 25;
		// $mail->Host   = $settings_data['smtp_host']->value;
		// $mail->Username   = $settings_data['smtp_username']->value;
		// $mail->Password   = $settings_data['smtp_password']->value;

		//Server settings
	    $mail->SMTPDebug = 0;                               // Enable verbose debug output
	    $mail->isSMTP();                                    // Set mailer to use SMTP
	    $mail->Host = $settings_data['smtp_host']->value;  	// Specify main and backup SMTP servers
	    $mail->SMTPAuth = true;                             // Enable SMTP authentication
	    $mail->Username = $settings_data['smtp_username']->value;  // SMTP username
	    $mail->Password = $settings_data['smtp_password']->value;    // SMTP password
	    $mail->SMTPSecure = $settings_data['smtp_secure_opton']->value;  // Enable TLS encryption, `ssl` also accepted
	    $mail->Port = $settings_data['smtp_port']->value; 
		$mail->WordWrap   = 80;
		$mail->Timeout    = 30;
		$mail->SMTPKeepAlive = true;
		
		$mail->clearAllRecipients();
		$mail->clearReplyTos();
		$mail->clearAttachments();
		$mail->isHTML(true); 
		
		//$mail->AddReplyTo('masud.haque@genusys.us', 'gPlex Contact Center');
		//$mail->SetFrom('masud.haque@genusys.us', 'gPlex Contact Center');
		// include('model/MEmail.php');
		// $email_model = new MEmail();
        // $email_froms = $email_model->getSkillEmail($skill_id); 
        // var_dump($email_froms);               
            	
        $mail->addReplyTo($settings_data['otp_form_email']->value, $settings_data['otp_form_name']->value);
        $mail->setFrom($settings_data['otp_form_email']->value, $settings_data['otp_form_name']->value);
                
        $mail->Subject = 'Chat History from ' . $settings_data['otp_form_name']->value;
		//$mail->Subject = 'Chat History from gPlex';
		$mail->msgHTML("Chat history is attached here with.<br /><br />Thanks");
		//$mail->AddAddress('masud.haque@genusys.us');
		$mail->addAddress($mailid);
		//$mail->AddAttachment('/usr/local/cc/AA/siplog/15/11/02/1446435554213424638.sip', 'chat-history.txt');
		$mail->addAttachment($filename, 'chat-history.pdf');
		
		try {
			if ($mail->send() ) {
				unlink($filename);
			} else {

			}
		} catch (Exception $e) {

		}		
		$mail->smtpClose();
	}
	
	function actionDayReportOLD(){
		include('model/MAgentReport.php');
		include('model/MReport.php');
		include('model/MSetting.php');
		include('lib/DateHelper.php');
		$report_model = new MReport();
		$ag_report_model = new MAgentReport();

		$setting_model = new MSetting();
		$dateinfo = DateHelper::get_input_time_details();
		$data['pageTitle'] = 'Day Report : ' . DateHelper::get_date_title($dateinfo);
		$data['log'] = $ag_report_model->getDayReportForAgent(UserAuth::getCurrentUser(), $dateinfo);
		$data['agentinfo'] = $report_model->getAgentSessionInfo(UserAuth::getCurrentUser(), $dateinfo);
		$data['dateinfo'] = $dateinfo;
		$data['request'] = $this->getRequest();
		$data['aux_messages'] = $setting_model->getBusyMessages('Y');

		if (isset($_POST['search'])) {
		    dd($dateinfo);
			$audit_text = '';
			$audit_text .= DateHelper::get_date_log($dateinfo);
			$report_model->addToAuditLog('Day Report', 'V', "", $audit_text);
		}

		$data['smi_selection'] = 'agent_report';
		$this->getTemplate()->display('agent_day_report', $data);
	}


///////        DayReport New For Robi   /////////////
    function actionDayReport(){
        include('model/MReport.php');
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $mainobj = new MReport();
        $data['agent'] = "";
        $data['current_session'] = "";
        $day = !empty($_POST['sdate']) ? generic_date_format($_POST['sdate']) : date('Y-m-d');
        $dateinfo = DateHelper::get_input_time_details(false, $day);
        $data['sdate'] = date('d/m/Y',$dateinfo->ststamp);
        $data['request'] = $this->getRequest();
        $aid = UserAuth::getCurrentUser();

        $data['agent'] = $mainobj->getAgentShiftSummary($aid, $dateinfo->sdate);
        $cur_session_info = $mainobj->getAgentCurrentSessionInfo($aid);
        $data['current_session_id'] = !empty($cur_session_info) ? $cur_session_info->session_id : "";

        $dateObj = new stdClass();
        $dateObj->ststamp = strtotime($day." 00:00:00");
        $dateObj->etstamp = strtotime($day." 23:59:59");
        $agent_session_info = $report_model->getAgentSessionInfo($aid, $dateObj);
		//$agent_session_info = $report_model->getAgentSessionInfo(2034, $dateObj);
		//Gprint($dateObj);
		//Gprint($agent_session_info);
		//Gprint(gmdate("H:i:s", $agent_session_info->login_duration));
		//die();
        $data['agent_session_info'] = $agent_session_info;
        $ice_feedback = $mainobj->getIceFeedBackByAgentId($dateObj, $aid);
        $total_ice = !empty($ice_feedback) ? $ice_feedback->positive_ice + $ice_feedback->negative_ice : 0;
        $data['positive_ice_percentage'] = !empty($total_ice) ? round(($ice_feedback->positive_ice/$total_ice)*100, 2) : 0;
        $data['ice_feedback'] = $ice_feedback;

        $data['pageTitle'] = 'Day Report : ' . DateHelper::get_date_title($dateinfo);
        //$data['smi_selection'] = 'agent_report';
        $data['smi_selection'] = 'agent_skillcdr';
        $this->getTemplate()->display('agent_day_report_new', $data);
    }


	
	/*
	function actionAddDisposition()
	{
		include('model/MSkillCrmTemplate.php');
		$template_model = new MSkillCrmTemplate();
		
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		$template_id = $template_model->getCallTemplate($cid);
		
		$data['pageTitle'] = 'Update Disposition';
		$data['cid'] = $cid;
		$data['request'] = $this->getRequest();
		$data['group_options'] = $template_model->getDispositionGroupOptions($template_id);
		$data['dp_options'] = $template_model->getDispositions($template_id);
		$data['readonly'] = isset($_REQUEST['readonly']) ? true : false;
		$this->getTemplate()->display_popup('skill_disposition_form', $data);
	}
	
	function actionD()
	{
		$a = '$YTozOntzOjY6ImNhbGxpZCI7czoxMzoiMTM4NTI4NjYyOTE1NiI7czoxMToidGVtcGxhdGVfaWQiO3M6MzoiQUFCIjtzOjEwOiJza2lsbF9uYW1lIjtzOjc6IlNraWxsLUEiO30=';
		$b = unserialize(base64_decode($a));
		print_r($b);
	}
	*/
	
	function actionCrmInDetails()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		
		if (!empty($cid)) {
			include('model/MCrmIn.php');
			$crm_model = new MCrmIn();
			
			include('model/MSkillCrmTemplate.php');
			$template_model = new MSkillCrmTemplate();
			
			$log = $crm_model->getCRMLogByCallID($cid);
			
			if (!empty($log) && UserAuth::getCurrentUser() == $log->agent_id) {
			
				$template_id = '';
				$skill_name = '';
				
				$skill = $template_model->getCallSkill($cid);
				if (!empty($skill)) {
					$skill_name = $skill->skill_name;
					if ($skill->popup_type == 'T') {
						$template_id = substr($skill->popup_url, -3);
					}
				}
				
				if (!empty($template_id)) {
					$param = array();
					$param['callid'] = $cid;
					$param['template_id'] = $template_id;
					$param['skill_name'] = $skill_name;
					
					$a = base64_encode(serialize($param));
					
					header('Location: index.php?task=crm_in&act=details&param=' . $a);
					exit;
				}
			}
		}
	}
	
	function actionSkillCdr()
	{
        include('model/MSkill.php');
        include('conf.sms.php');
        $skill_model = new MSkill();
	    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $extParam = "";
        $isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
		if ($type=='outbound') {
		    $data['pageTitle'] = 'Skill CDR - Outbound';
		    //$data['ivr_options'] = null;
		    $template_file = 'agent_skill_cdr_outbound';
		} else {
		    $type = 'inbound';
		    $data['pageTitle'] = 'Skill CDR - Inbound';
		    //$data['ivr_options'] = $ivr_model->getIvrOptions();
		    $template_file = 'agent_skill_cdr_inbound';
		}
        $data['date_stime'] = date("Y-m-d 00:00");
        $data['date_etime'] = "";
        $data['cli'] = "";
        $data['did'] = "";

        $data['sms_enabled'] = $isEnableSMS;
        if ($isEnableSMS) {
            if (!empty($_POST['last_sms_text']) && !empty($_POST['ssms_template']) && is_array($_POST['cid_selector']) && count($_POST['cid_selector']) > 0) {
                $smsText = $_POST['last_sms_text'];
                $destNumbers = $_POST['cid_selector'];
                $smsTemplate = $_POST['ssms_template'];
                $agent_id = UserAuth::getUserID();

                if (strlen($smsText) > 250) {
                    $smsText = substr($smsText, 0, 250);
                }
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
                            $isSmsSend = $this->sendSMSTApi($mobileNumber, $smsText);
                            if ($isSmsSend) {
                                $smsNumsArr[] = $mobileNumber;
                                $skill_model->addSendSMSLog($saveCallId, $mobileNumber, $smsText, $smsTemplate,$agent_id,$skill_id);
                                $smsCounter++;
                            }
                        }
                    }
                }
                $data['smsCounter'] = $smsCounter > 0 ? $smsCounter : "";
                $extParam = "&sms=Y";
                if ($smsCounter > 0){
                    $data['grid_info_msg'] = "Total $smsCounter of $totalSMSNumber SMS send successfully";
                }else{
                    $data['grid_info_msg'] = "Failed to send SMS";
                }

                $sessObject = UserAuth::getCDRSearchParams();
                $dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
                $cli = isset($sessObject->cli) ? $sessObject->cli : "";
                $did = isset($sessObject->did) ? $sessObject->did : "";

                $data['date_stime'] = $dateinfo->sdate . " " . $dateinfo->stime;
                $data['date_etime'] = $dateinfo->edate . " " . $dateinfo->etime;
                $data['cli'] = $cli;
                $data['did'] = $did;
            }
        }

		$data['topMenuItems'] = array(
			array('href'=>'task=agent&act=skill-cdr&type=inbound', 'img'=>'fa fa-list-alt', 'label'=>'Inbound CDR'),
			array('href'=>'task=agent&act=skill-cdr&type=outbound', 'img'=>'fa fa-list-alt', 'label'=>'Outbound CDR')
		);
		$data['dataUrl'] = $this->url('task=get-agent-data&act=agentskillcdr&type='.$type);
		$data['smi_selection'] = 'agent_skillcdr';
		$this->getTemplate()->display($template_file, $data);
	}
	
	function actionDialer(){
		include('model/MAgent.php');
                $agent_model = new MAgent();
                
		$templateVar=array();		
		AddModel("MAgentHotKey");	
		$templateVar['hotKeySettings']=MAgentHotKey::getAllAsObject();		
		//GPrint($templateVar); die;
		$templateVar['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
		$agentid = UserAuth::getCurrentUser();
		$hashcode=$agent_model->GenerateToken($agentid);
		UserAuth::resetWSLoggedIn($hashcode);
		$agents = $agent_model->getSupervisedAgents($agentid);
		$agent_list_gui = new stdClass();
		$agent_list_gui->row_count = is_array($agents) ? count($agents) : 0;
		$agent_list_gui->data = $agent_list_gui->row_count>0 ? $agents : array();
		//$_COOKIE['wsCCListAgent'] = json_encode($agent_list_gui->data);
		//setcookie('wsCCListAgent', json_encode($agent_list_gui->data));
		//setcookie('wsTest', json_encode($agent_list_gui->data));
		$templateVar['AgentList'] = json_encode($agent_list_gui->data);
		$this->getTemplate()->display_only('dialer', $templateVar);
	}
	
	
	function actionDialernew()
    {
        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MIvr');
        AddModel('MSetting');
        //AddModel('MSkillCrmTemplate');
        AddModel('MCallTransferSetting');
        AddModel("MAgentHotKey");
        //include('helper/Structure.php');
        include ('conf.extras.php');

        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();
        //$template_model = new MSkillCrmTemplate();
        $aux_model = new MSetting();
        $call_trans_config_model = new MCallTransferSetting();

        $templateVar = [];
        $supervisors = [];
        $allAgents   = [];
        $agentList   = [];

		$agentid = UserAuth::getCurrentUser();
		$hashcode=$agent_model->GenerateToken($agentid);
		UserAuth::resetWSLoggedIn($hashcode);
        $call_transfer_config = $call_trans_config_model->getByRoleType();
        $call_transfer_config = !empty($call_transfer_config) ? array_shift($call_transfer_config) : [];

		$agents = $agent_model->getSupervisedAgents($agentid);

		if (!empty($call_transfer_config)){
		    $user_type = $call_transfer_config->agents == "Y" && $call_transfer_config->supervisors == "Y"  ? "" : ($call_transfer_config->agents == "Y" ? "A" : "S");
		    $agentList = $agent_model->getAgents($user_type,'','','Y');
        }

        if (!empty($agentList)){
            foreach ($agentList as $agent){
                if ($agent->usertype == "S"){
                    $supervisors[$agent->agent_id] = $agent->name."({$agent->agent_id})";
                }elseif ($agent->usertype == "A"){
                    $allAgents[$agent->agent_id] = $agent->name."({$agent->agent_id})";
                }
            }
        }

        $agent_list_gui = new stdClass();
		$agent_list_gui->row_count = is_array($agents) ? count($agents) : 0;
		$agent_list_gui->data = $agent_list_gui->row_count>0 ? $agents : array();
		//$_COOKIE['wsCCListAgent'] = json_encode($agent_list_gui->data);
		//setcookie('wsCCListAgent', json_encode($agent_list_gui->data));
		//setcookie('wsTest', json_encode($agent_list_gui->data));

		
		//$skillOptions = $skill_model->getVoiceSkillOptions();
		$skillOptions = $agent_model->getAgentSkillWithNameAsKeyValue();
		/* $skillArray = array();
		if (is_array($skillOptions)) {
		    foreach ($skillOptions as $key => $val) {
		        $obj = new stdClass();
		        $obj->skill_id = $key;
		        $obj->skill_name = $val;
		        $skillArray[] = $obj;
		    }
		} */


        $templateVar['CCSettings'] = $aux_model->getCCSettings();
        $templateVar['cctype'] = $agent_model->getDB()->cctype;
        $templateVar['AgentList'] = json_encode($agent_list_gui->data);
        $templateVar['hotKeySettings']=MAgentHotKey::getAllAsObject();
		$templateVar['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
		//$templateVar['dispositions'] = Structure::buildTree($template_model->getDispositions(''),'disposition_id');
		$templateVar['SkillList'] = json_encode($skillOptions);
		//$templateVar['SkillList'] = json_encode($skillArray);
		$templateVar['IvrList'] = json_encode(!empty($call_transfer_config) && $call_transfer_config->ivrs == "Y" ? $ivr_model->getIvrOptionsForCallControl() : []);
		$templateVar['AUXList'] = json_encode($aux_model->getBusyMessagesForCallControl());
		$templateVar['supervisors'] = json_encode($supervisors);
		$templateVar['allAgent'] = json_encode($allAgents);
		$templateVar['callTransferConfig'] = $call_transfer_config;
		$templateVar['cobrowseLinks'] = json_encode($aux_model->getCobrowseLinks());
		/*======================== Show secondary call control bar =========================*/
        $templateVar['show_sccb'] = (!empty($extra->show_sccb) && $extra->show_sccb) ? true : false;
        $templateVar['did_prefix_replace'] = !empty($extra->did_prefix_replace) ? json_encode($extra->did_prefix_replace) : json_encode([]);

		$this->getTemplate()->display_only('dialer-new', $templateVar);
	}
	
	function actionSendtext() {
		$data = array();
		$this->getTemplate()->display_popup_chat('send_text', $data);
	}
	
	function actionTextsubmit() {
		$to = isset($_REQUEST["to"]) ? trim($_REQUEST["to"]) : "";
		$text = isset($_REQUEST["text"]) ? trim($_REQUEST["text"]) : "";
		
		if(empty($to)) exit(json_encode(array("status"=>1,"message"=>"Receipient number empty")));
		if(empty($text)) exit(json_encode(array("status"=>1,"message"=>"Text empty")));
		if(empty($text)) exit(json_encode(array("status"=>1,"message"=>"Text length must not be more than 160 characters")));
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://www.gtalk.us/pinless/gTalkSMS_send_us.php',
			//CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array(
				to => $to,
				msg => urldecode($text)
			)
		));
		$resp = curl_exec($curl);
		curl_close($curl);
		echo $resp;
	}
	
	function actionDialerbk(){
		include('model/MAgent.php');
                $agent_model = new MAgent();
                
		$templateVar=array();		
		AddModel("MAgentHotKey");	
		$templateVar['hotKeySettings']=MAgentHotKey::getAllAsObject();		
		//GPrint($templateVar); die;
		$templateVar['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
		$this->getTemplate()->display_only('dialerbk', $templateVar);
	}
	function actionAssignseat(){			
		$ajaxresposne=new AjaxConfirmResponse();
		$ajaxresposne->msg="Post data is empty";
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			//seat, suid,supass, pcmac
			$seat=$request->getPost('seat');
			$suid=$request->getPost('suid');
			$supass=$request->getPost('supass');
			$pcmac=$request->getPost('pcmac');
			include_once ('model/MAgent.php');
			$agent_model = new MAgent();
	
			if ($agent_model->isAgentExist($suid)){
				if ($agent_model->isAgentBlocked($suid)){
					$ajaxresposne->msg="Your account($suid) has been auto locked out due to ".$agent_model->loginAttempts." failed attempts to log on";
					$agent_model->addToAuditLog('Misslogin', 'S', $suid, "Account blocked");
				}else {
					$valid_user = $agent_model->getValidAgent($suid, $request->getPost('supass'));
					if (!empty($valid_user)) {
						include_once ('model/MSeat.php');
						$seatm=new MSeat();
						if($seatm->updateSeatMac($seat, $pcmac)){
							$ajaxresposne->status=true;
							$ajaxresposne->msg="Successfully MAC Updated";
							$agent_model->addToAuditLog('Login', 'I', "", "[Assign seat] Update seat mac");
						}else{
							$ajaxresposne->msg="MAC Update Failed";
						}
	
					} else {
						$agent_model->addToAuditLog('Login', 'M', $suid, "[Assign seat] Failed login attempt");
						$agent_model->saveLoginAttempt($suid);
						$ajaxresposne->msg = "Invalid user!! Check your password!!";
					}
				}
			}else {
				$ajaxresposne->msg = "Invalid user!! Check your password!!";
				$agent_model->addToAuditLog('Login', 'M', "", "[Assign seat]".$suid."; Failed login attempt");
			}
	
		}
	
		die(json_encode($ajaxresposne));
			
	
	}
	function actionResetLoginPin(){	
		$request = $this->getRequest();
		$data=array();
		$data['fullload']=true;
		$data['loadbv']=true;
		$data['pageTitle'] = 'New Login PIN Generation';
		//$data['buttonTitle'] = 'Generate';		
		if($this->getRequest()->isPost()){
			$agentid = UserAuth::getCurrentUser();
			include('model/MAgent.php');		
			$agent_model = new MAgent();
			$valid_user = $agent_model->getValidAgent($agentid, $request->getPost('pass'));
			if (!empty($valid_user)) {
				$generate="";
				$try=0;
				while ($try<=4){
					$generate=rand(10000,99999);
					if(!$agent_model->isLoginPINExist($generate)){
						break;
					}else{
						$generate="";
					}
					$try++;
				}
				if(!empty($generate)){
					if($agent_model->updateLoginPIN($agentid,$generate)){
						AddInfo("Login PIN successfully updated<br/> &nbsp; &nbsp;New Login PIN is : <strong>$generate</strong>");
						$this->getTemplate()->display_popup_msg($data);
					}else{
						AddError("Try again");
					}
					
				}else{
					AddError("Try again");
				}
			}else{
				AddError("Current Password is invalid");
			}
		}			
		$this->getTemplate()->display_popup('reset-login-pin', $data);	
	}
	function actionAssignTest(){
		$templateVar=array();
		$templateVar['pageTitle'] = 'Debug List';	
		$this->getTemplate()->display('assign-test', $templateVar);
	}
	function actionSeats(){
		include_once ('model/MSeat.php');
		$seatm=new MSeat();
		$allseat=$seatm->getAllSeat(10);
		print_r($allseat);
		die;
	}
	
	function actionChat_disposition_mail() {
	
		include('model/MSkill.php');
		$skill_model = new MSkill();
		
		include('model/MSetting.php');
		$settings_model = new MSetting();
		
		include('model/MEmail.php');
		$email_model = new MEmail();
		
		$web_site_url = isset($_REQUEST["url"]) ? trim($_REQUEST["url"]) : "";
		$user_arival_duration = isset($_REQUEST["uad"]) ? trim($_REQUEST["uad"]) : "";
		$email = isset($_REQUEST["email"]) ? trim($_REQUEST["email"]) : "";
		$contact = isset($_REQUEST["contact"]) ? trim($_REQUEST["contact"]) : "";
		$service_id = isset($_REQUEST["service_id"]) ? trim($_REQUEST["service_id"]) : "";
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		$tstamp = time();
		
		
		//echo "test".$web_site_url.$user_arival_duration.$cid;
		$result = $settings_model->getSetting('chat_disposition');
		
		$chat_disposition = "Y";
		if($result) {
			$chat_disposition = $result->value;
		}
		//echo $chat_disposition;
		
		if(isset($chat_disposition) && $chat_disposition=="N") {
			$isAdded = $email_model->addDispositionModel($cid, '', $tstamp, UserAuth::getCurrentUser(), '', $email, $contact, $web_site_url, $user_arival_duration, $service_id);
			echo $chat_disposition;
		} else {
			$data = array();
			$data['web_site_url'] = $web_site_url;
			$data['user_arival_duration'] = $user_arival_duration;
			$data['cid'] = $cid;
			$data['email'] = $email;
			$data['contact'] = $contact;
			$data['service_id'] = $service_id;
			$data['disposition_ids'] = [];
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
			$this->getTemplate()->display_popup_chat('chat_disposition_mail', $data);
		}
	}
	
	function actionDispositionSubmit() {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		require_once("lib/dompdf/autoload.inc.php");
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : "";
		//$callid = $cid;
		$cid_ary = explode("-",$cid);
		$cid = $cid_ary[0];
		$mailid = isset($_REQUEST['mailid']) ? trim($_REQUEST['mailid']) : "";		
		
		
		$filename = BASEPATH."temp/".$cid.".pdf";
		
		if (strlen($cid) >= 10) {
			$tstamp = substr($cid, 0, 10);
			$yy_mm_dd = date("y/m/d", $tstamp);
			$sip_file = $this->getTemplate()->sip_logger_path.(!empty(UserAuth::getDBSuffix()) ? UserAuth::getDBSuffix() : 'AA'). "/siplog/$yy_mm_dd/" . $cid . ".sip";
						
			//$fp = fopen($filename, "w");
            if (file_exists($sip_file)) {
				//$fh = fopen($sip_file, 'r');
				$lines = file($sip_file);
				/*
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					$timestamp = date("Y-m-d h:i:s a",$timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = $line2_ary[1];
					
					fwrite($fp,"$name --- $timestamp\n");
					fwrite($fp,base64_decode($msg)."\n");
				}*/
				
				$message_array = array();
				
				for($i=0; $i<count($lines); $i++) {
					$line1 = trim($lines[$i],"\r\n");
					$timestamp = str_replace("# ","",$line1);
					$timestamp = ceil($timestamp);
					$timestamp = date("d/m/Y h:i:s a",$timestamp);
					
					$i++;
					$line2 = trim($lines[$i],"\r\n");
					$line2_ary = explode(":",$line2);
					$name = $line2_ary[0];
					$msg = base64_decode($line2_ary[1]);
					
					$msg_ary = explode("|",$msg);
					$index = $msg_ary[0];
					$message = base64_decode($msg_ary[1]);
					$packet_no = $msg_ary[3];
					
					$message_array[$index]["name"] = $name;
					$message_array[$index]["timestamp"] = $timestamp;
					$message_array[$index]["msg"][$packet_no] = $message;
					
					//fwrite($fp,"$name --- $timestamp\r\n");
					//fwrite($fp,base64_decode($msg)."\r\n");
				}
				
				// $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><body><table>";
				$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
				$html .= '<style type="text/css">html, body {font-family: solaimanlipi !important;}</style>';
				$html .= '</head><body><table>';
				foreach($message_array as $data) {
					$html .= "<tr><td width='100'>".$data["name"]."</td><td>---".$data["timestamp"]."</td></tr>";
					$msg = implode("",$data["msg"]);
					$html .= "<tr><td colspan='2'>".base64_decode($msg)."</td></tr>";
				}
				$html .= "</table></body></html>";
		
				if ( get_magic_quotes_gpc() )
					$html = stripslashes($html);
			
				$old_limit = ini_set("memory_limit", "16M");
			
				$dompdf = new Dompdf();
				$dompdf->set_option('isHtml5ParserEnabled', true);
				$dompdf->loadHtml($html, '');
				// $dompdf->load_html($html);
				// $dompdf->set_paper("a4");
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->render();
				$pdf = $dompdf->output();

				file_put_contents($filename,$pdf);
				
			} else {
				echo "File not exists: ".$sip_file;
				//fclose($fp);
				//return;
			}
			fclose($fp);
		} else {
			echo "Wrong call id: ".$cid;
			//return;
		}
	
	
		include_once('model/MEmail.php');
		$email_model = new MEmail();
		//print_r($_POST);
		
		//$_POST["skill_email"] = array("shahidul.islam@genuitysystems.com");
		
		$skill_email = isset($_POST["skill_email"]) ? $_POST["skill_email"] : array();
		$mail_body   = isset($_POST["mail_body"]) ? trim($_POST["mail_body"]) : "";
		$web_site_url   = isset($_POST["web_site_url"]) ? trim($_POST["web_site_url"]) : "";
		$user_arival_duration = isset($_POST["user_arival_duration"]) ? trim($_POST["user_arival_duration"]) : 0;
		$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
		$contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : "";
		$service_id = isset($_POST["service_id"]) ? trim($_POST["service_id"]) : "";
		$disposition_array = array();
		$d_id = "";
		for($i=0; $i<6; $i++) {
			 $val = !empty($_POST["disposition_id".$i]) ? trim($_POST["disposition_id".$i]) : "";
			 $disposition_array[] = $val;
			if(!empty($val)) $d_id = $val;
		}
		$dids = implode("','",$disposition_array);
		$dispositions = $email_model->getDispositionByIds($dids);
		
		$disposition = "";
		if (is_array($disposition_array)) {
			foreach($disposition_array as $did) {
				if (is_array($dispositions)) {
					foreach($dispositions as $obj) {
						if($obj->disposition_id==$did) {
							$disposition .= "=>".$obj->title;
							break;
						}
					}
				}
			}
		}
	
		$tstamp = substr($cid, 0, 10);
		//$email = is_array($skill_email) ? $skill_email[0] : "";
		$tstamp = time();

		//$did = end($disposition_array);
		//echo $email;
		//echo "$callid, $did, $tstamp, ".UserAuth::getCurrentUser().", $mail_body, $email, $web_site_url, $user_arival_duration";
		$isAdded = $email_model->addDispositionModel($cid, $d_id, $tstamp, UserAuth::getCurrentUser(), $mail_body, $email, $contact, $web_site_url, $user_arival_duration, $service_id);
		
		if(!empty($disposition)) $mail_body = "Disposition: ".$disposition."<br/><br/>".$mail_body;
		
		// var_dump($skill_email);
		// var_dump(count($skill_email));
		// die('hi');
		if(count($skill_email)==0 || (count($skill_email) == 1 && empty($skill_email[0]))) {
			echo "Disposition sent successfully!";
			return;
		}


		
		//include_once('conf.email.php');
		include_once('model/MCcSettings.php');
		$settings_model = new MCcSettings();
		$settings_model->module_type = MOD_CHAT;
		$settings_data = $settings_model->getFormatAllSettings();

		
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/Exception.php' );
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/PHPMailer.php' );
		include_once( 'lib/phpmailer/phpmailer/phpmailer/src/SMTP.php' );
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);

		//Server settings
	    $mail->SMTPDebug = 0;                               // Enable verbose debug output
	    $mail->isSMTP();                                    // Set mailer to use SMTP
	    $mail->Host = $settings_data['smtp_host']->value;  	// Specify main and backup SMTP servers
	    $mail->SMTPAuth = true;                             // Enable SMTP authentication
	    $mail->Username = $settings_data['smtp_username']->value;  // SMTP username
	    $mail->Password = $settings_data['smtp_password']->value;    // SMTP password
	    $mail->SMTPSecure = $settings_data['smtp_secure_opton']->value;  // Enable TLS encryption, `ssl` also accepted
	    $mail->Port = $settings_data['smtp_port']->value; 
		$mail->WordWrap   = 80;
		$mail->Timeout    = 30;
		$mail->SMTPKeepAlive = true;
		
		$mail->clearAllRecipients();
		$mail->clearReplyTos();
		$mail->clearAttachments();
		$mail->isHTML(true); 
		
		//$mail->AddReplyTo('masud.haque@genusys.us', 'gPlex Contact Center');
		//$mail->SetFrom('masud.haque@genusys.us', 'gPlex Contact Center');
		// include('model/MEmail.php');
		// $email_model = new MEmail();
        // $email_froms = $email_model->getSkillEmail($skill_id); 
        // var_dump($email_froms);               
            	
        $mail->addReplyTo($settings_data['otp_form_email']->value, $settings_data['otp_form_name']->value);
        $mail->setFrom($settings_data['otp_form_email']->value, $settings_data['otp_form_name']->value);		
		$mail->Subject = 'Chat Disposition Query';
		$mail->msgHTML($mail_body);
		
		foreach($skill_email as $mailid) {
			$mail->addAddress($mailid);
		}
		//$mail->AddAttachment('/usr/local/cc/AA/siplog/15/11/02/1446435554213424638.sip', 'chat-history.txt');
		$mail->addAttachment($filename, 'chat-history.pdf');
		
		try {
			if ($mail->send() ) {
				unlink($filename);
				echo "Disposition sent successfully!";
			} else {
			}
		} catch (Exception $e) {

		}		
		$mail->smtpClose();		
	}
	
	function actionChatTemplate() {
		include('model/MEmail.php');
		$email_model = new MEmail();
		
		include('model/MSkill.php'); 
		$skill_model = new MSkill();
		
		
		$skills = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
		
		$skillArray = array();
		foreach($skills as $skill) {
			$skillArray[] = $skill->skill_id; 
		}
		
		$data = array();
		$user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '';
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$sid = implode("','",$skillArray);
		$templates =  empty($sid) ? array() : $email_model->getChatTemplateBySkillId($sid);
		
		$templates = $templates==null ? array() : $templates;
		$data["templates"] = $templates;
		$data["user_id"] = $user_id;
		$this->getTemplate()->display_popup_chat('chat_template_selection', $data);
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

    function sendSMSTApi($mobileNumber, $smsText){
        if (empty($mobileNumber) || empty($smsText)) return false;

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
        }

        return true;
    }

    public function actionSaveDisposition(){
	    $response = new stdClass();
	    $response->status = false;
	    $response->message = "Failed to Save Disposition.";

	    AddModel('MAgent');
	    $agent_model = new MAgent();

	    $request = $this->getRequest();

        $dispositions = array_filter($request->getRequest('dispositions',[]));
        $callid = $request->getRequest('callid','');
        $cli = $request->getRequest('cli','');
        $notes = $request->getRequest('notes','');
        $direction = $request->getRequest('direction','IN');


        if (!empty($dispositions[0])){
            $response = $agent_model->saveDisposition($callid, $dispositions, $cli, $notes, $direction);
        }

        echo json_encode($response); die;
    }

    public function actionGetPdSkillEngine(){
	    $response = [];

	    AddModel('MAgent');
	    $agent_model = new MAgent();

	    $response = $agent_model->getPdSkillEngine();

	    echo json_encode($response); die;
    }


    public function actionGetSkillWiseDisposition()
    {
        AddModel('MSkillCrmTemplate');
        include('helper/Structure.php');

        $request = $this->getRequest();
        $skill_id = $request->getRequest('skill_id');

        $skill_crm_template_model = new MSkillCrmTemplate();

        $dispositions = Structure::buildTree($skill_crm_template_model->getDispositionsBySkillId($skill_id),'disposition_id');
        echo json_encode($dispositions, JSON_PARTIAL_OUTPUT_ON_ERROR);
        die;
    }

    public function actionGetSessionSummary()
    {
        AddModel('MAgent');
        include('lib/DateHelper.php');

        $summary = new stdClass();

        $agent_model = new MAgent();
        $response = $agent_model->getAgentSessionSummary();
        if (!empty($response)){
            $response[0]->first_login = date(REPORT_DATE_FORMAT." h:i:s A", strtotime($response[0]->first_login));
            $response[0]->staff_time = DateHelper::get_formatted_time((int)$response[0]->staff_time);
            $response[0]->wrap_up_time = DateHelper::get_formatted_time((int)$response[0]->wrap_up_time);
            $response[0]->total_break_time = DateHelper::get_formatted_time((int) $response[0]->total_break_time + $response[0]->total_aux_in_time+ $response[0]->total_aux_out_time);
        }
        echo !empty($response) ? json_encode($response) : json_encode([]);
        die;
    }

    function actionSkillCdrNew(){
        include('model/MSkill.php');
        include('conf.sms.php');
        //$skill_model = new MSkill();
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $extParam = "";
        //$isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
        if ($type=='outbound') {
            $data['pageTitle'] = 'Skill CDR - Outbound';
            $template_file = 'agent_skill_cdr_outbound';
        } else {
            $type = 'inbound';
            $data['pageTitle'] = 'Skill CDR - Inbound';
            $template_file = 'agent_skill_cdr_inbound_new';
        }
        $data['date_stime'] = date("Y-m-d 00:00");
        $data['date_etime'] = "";
        $data['cli'] = "";
        $data['did'] = "";

        //$data['sms_enabled'] = $isEnableSMS;
        /*if ($isEnableSMS) {
            if (!empty($_POST['last_sms_text']) && !empty($_POST['ssms_template']) && is_array($_POST['cid_selector']) && count($_POST['cid_selector']) > 0) {
                $smsText = $_POST['last_sms_text'];
                $destNumbers = $_POST['cid_selector'];
                $smsTemplate = $_POST['ssms_template'];
                $agent_id = UserAuth::getUserID();

                if (strlen($smsText) > 250) {
                    $smsText = substr($smsText, 0, 250);
                }
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
                            $isSmsSend = $this->sendSMSTApi($mobileNumber, $smsText);
                            if ($isSmsSend) {
                                $smsNumsArr[] = $mobileNumber;
                                $skill_model->addSendSMSLog($saveCallId, $mobileNumber, $smsText, $smsTemplate,$agent_id,$skill_id);
                                $smsCounter++;
                            }
                        }
                    }
                }
                $data['smsCounter'] = $smsCounter > 0 ? $smsCounter : "";
                $extParam = "&sms=Y";
                if ($smsCounter > 0){
                    $data['grid_info_msg'] = "Total $smsCounter of $totalSMSNumber SMS send successfully";
                }else{
                    $data['grid_info_msg'] = "Failed to send SMS";
                }

                $sessObject = UserAuth::getCDRSearchParams();
                $dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
                $cli = isset($sessObject->cli) ? $sessObject->cli : "";
                $did = isset($sessObject->did) ? $sessObject->did : "";

                $data['date_stime'] = $dateinfo->sdate . " " . $dateinfo->stime;
                $data['date_etime'] = $dateinfo->edate . " " . $dateinfo->etime;
                $data['cli'] = $cli;
                $data['did'] = $did;
            }
        }*/

        // $data['topMenuItems'] = array(
        //     array('href'=>'task=agent&act=skill-cdr&type=inbound', 'img'=>'fa fa-list-alt', 'label'=>'Inbound CDR'),
        //     array('href'=>'task=agent&act=skill-cdr&type=outbound', 'img'=>'fa fa-list-alt', 'label'=>'Outbound CDR')
        // );
//		$data['dataUrl'] = $this->url('task=get-agent-data&act=agentskillcdr&type='.$type);
        $data['dataUrl'] = $this->url('task=get-agent-data&act=agentskillcdrNew&type='.$type);
        $data['smi_selection'] = 'agent_skillcdr';
        $this->getTemplate()->display($template_file, $data);
    }

    function actionCurrentShiftReport() {
        include('model/MReport.php');
        $aid = UserAuth::getCurrentUser();
        $today = date("Y-m-d");
        $dateObj = new stdClass();
        $dateObj->sdate = $today." 00:00:00";
        $dateObj->edate = $today." 23:59:59";

        $mainobj = new MReport();
        $data['agent'] = $mainobj->getAgentShiftSummary($aid);

        $ice_feedback = $mainobj->getIceFeedBackByAgentId($dateObj, $aid);
        $total_ice = !empty($ice_feedback) ? $ice_feedback->positive_ice + $ice_feedback->negative_ice : 0;
        $data['positive_ice_percentage'] = !empty($total_ice) ? round(($ice_feedback->positive_ice/$total_ice)*100, 2) : 0;


        $data['topMenuItems'] = array(
            array('href'=>'task=agent&act=day-report', 'img'=>'fa fa-list-alt', 'label'=>'Previous Shift Report')
        );

        $data['ice_feedback'] = $ice_feedback;
        $data['pageTitle'] = 'Current Shift';
        $data['smi_selection'] = 'agent_skillcdr';
        $this->getTemplate()->display('current_shift_report', $data);
    }

    function actionCoBrowserLinks() {
		include('model/MCoBrowser.php');
		$co_browser_model = new MCoBrowser();
		
		$data = array();
		$user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '';		
		$user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';		
		$links =  empty($user_id) ? array() : $co_browser_model->getCoBrowserLink();
		
		$links = $links==null ? array() : $links;
		$data["links"] = $links;
		$data['pageTitle'] = 'Link';
		$data["user_id"] = $user_id;
		$data["user_name"] = $user_name;
		$this->getTemplate()->display_popup_chat('co_browser_link_selection', $data);
	}
    function actionSaveCobrowseLog() {
		$response = new stdClass();

		include('model/MSetting.php');
		$setting_model = new MSetting();

		$request = $this->getRequest();
		$response = $setting_model->saveCobrowseLog($request); 
		
		echo json_encode($response); die;
	}

    public function ActionGetCustomerLmsInfo()
    {
        AddModel('MAgent');
        $agent_model = new MAgent();

        $cli = $this->getRequest()->getRequest('cli','');
        if (empty($cli)){
            die(json_encode(false));
        }

        $response = $agent_model->get_lms_info($cli);

        echo die(json_encode(is_array($response) ? array_shift($response): null));
    }

    public function ActionGetCustomerLastMonthDisposition($cli)
    {
        AddModel('MAgent');
        $agent_model = new MAgent();

        $cli = $this->getRequest()->getRequest('cli','');
        if (empty($cli)){
            die(json_encode(false));
        }

        $voice_call_disposition = $agent_model->get_last_month_voice_disposition($cli);
        $email_disposition = $agent_model->get_last_month_email_disposition($cli);

        /*==================== convert dispositions to array ========================*/
        $voice_call_disposition = is_array($voice_call_disposition) ? $voice_call_disposition : [];
        $email_disposition = is_array($email_disposition) ? $email_disposition : [];

        $dispositions = array_merge($voice_call_disposition, $email_disposition);

        return die(json_encode($dispositions));
    }
	
	function actionCheckVerifyUser()
    {
        AddModel('MAgent');
        $agent_model = new MAgent();
        $post_data = $_REQUEST; // post data
        $call_id = trim($_REQUEST['cCustomerCallId']);
        $call_id = explode('-', $call_id);
        $result = "";
        sleep(3);

        if(isset($call_id[0]) && !empty($call_id[0]))
            $result = $agent_model->checkVerifiedUser($call_id[0]);
        //var_dump($result);
        if (!empty($result) && $result != false) {
            die(json_encode([
                MSG_RESULT => true,
                MSG_TYPE => MSG_SUCCESS,
                MSG_MSG => '',
                'verify_user' => $result
            ]));
        }

        die(json_encode([
            MSG_RESULT => false,
            MSG_TYPE => MSG_ERROR,
            MSG_MSG => 'Sorry! Your request is wrong!'
        ]));
    }
	
	public function actionJsErrorLog()
    {
        $msg = $this->getRequest()->getRequest('msg', '');
        $url = $this->getRequest()->getRequest('url', '');
        $line = $this->getRequest()->getRequest('line', '');        
		$current_date = date('Y-m-d H:i:s');

        $text = $current_date."    ".$line . "    " . $url . "    " . $msg . "    " . PHP_EOL;
        $file_path = BASEPATH . "temp/webchat_js_log/";
        $file_name = $file_path . date("Y_m_d") . ".txt";
        $file_handler = fopen($file_name, "a");
        fwrite($file_handler, $text);
        fclose($file_handler);

        return die(json_encode(true));
    }	
	public function actionSaveAgentFirstResponse()
    {
        AddModel('MAgent');
        $agent_model = new MAgent();
        $call_id = explode("-", $this->getRequest()->getRequest('call_id', ''));
        if (is_array($call_id)) {
            $call_id = $call_id[0];
        }
		
		$agent_id = UserAuth::getCurrentUser();
        $agentFirstResponseTime = $this->getAgentFirstResponseTimeFromSip($call_id, $agent_id);
        if ($agentFirstResponseTime == null) {
            $agentFirstResponseTime = date("Y-m-d H:i:s");
        }
        $text = ["Date:" => $agentFirstResponseTime, "Call_id:" => $call_id, "Agent_id:" => $agent_id, "post-param:" => json_encode($_REQUEST)];
        log_text($text);
		
        if ($agent_model->setAgentFirstResponseTime($call_id, $agentFirstResponseTime)) {
            return die(json_encode(true));
        } else {
            return die(json_encode(false));
        }
    }

    public function actionLogChatCoBrowse()
    {
        AddModel('MAgent');
        $agentModel = new MAgent();

        $logStartTime = date("Y-m-d H:i:s");
        $agentId = UserAuth::getCurrentUser();
        $customerName = $this->getRequest()->getRequest('customerName', '');
        $customerNumber = $this->getRequest()->getRequest('customerNumber', '');
        $callId = explode("-", $this->getRequest()->getRequest('callId', ''));
        if (is_array($callId)) {
            $callId = $callId[0];
        }
        $requestType = $this->getRequest()->getRequest('requestType', '');
        $customerUrl = $this->getRequest()->getRequest('customerUrl', '');
        $agentUrl = $this->getRequest()->getRequest('agentUrl', '');

        $logParams = array(
            'logStartTime' => $logStartTime,
            'agentId' => $agentId,
            'customerName' => $customerName,
            'customerNumber' => $customerNumber,
            'callId' => $callId,
            'requestType' => $requestType,
            'customerUrl' => $customerUrl,
            'agentUrl' => $agentUrl
        );

        if ($agentModel->logChatCoBrowse($logParams)) {
            return die(json_encode(true));
        } else {
            return die(json_encode(false));
        }
    }

    private function getAgentFirstResponseTimeFromSip($callId, $agentId)
    {
        $responseTime = null;
        $sipFilePath = $this->getTemplate()->sip_logger_path . "message/" . date("y/m/d") . "/" . $callId . ".sip";
        $fileHandler = fopen($sipFilePath, "r");
        if ($fileHandler) {
            $timeStamp = null;
            while (($line = fgets($fileHandler)) !== false) {
                if (substr($line, 0, 1) == "#") {
                    $timeStamp = substr($line, 2, 10);
                } elseif (substr($line, 0, 4) == $agentId) {
                    $msgInfo = explode("|", base64_decode(substr($line, 5)));
                    if (base64_decode($msgInfo[1]) == ".") continue;
                    $responseTime = date("Y-m-d H:i:s", $timeStamp);
                    break;
                }
            }
        }
        fclose($fileHandler);
        return $responseTime;
    }

    /*
     * Temporarily log
     * agent aux code
     */
    public function actionLogAux () {
        $aux_code = isset($_REQUEST['aux_code']) ? $_REQUEST['aux_code'] : "";
        $web_key = !empty($_REQUEST['web_key']) ? $_REQUEST['web_key'] : "";
        if (!isset($aux_code)){
            return null;
        }
        log_text([UserAuth::getCurrentUser(), $aux_code, $web_key], "temp/aux_log/", 3);
        return true;
    }
}
