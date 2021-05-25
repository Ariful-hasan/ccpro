<?php
require_once 'BaseTableDataController.php';
class GetAgentData extends BaseTableDataController
{
	var $pagination;
	
	function __construct() {
		parent::__construct();		
	}
	
	function actionAgentSkillCdr()
	{
		header('Access-Control-Allow-Origin: "*"');
		      //Access-Control-Allow-Origin
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: application/json');
		
	    include('model/MAgentReport.php');
	    include('model/MSkill.php');
	    include('model/MIvr.php');
	    include('model/MAgent.php');
	    include('lib/DateHelper.php');
        include('conf.sms.php');
	
	    $report_model = new MAgentReport();
	    $skill_model = new MSkill();
	    $ivr_model = new MIvr();
	    $agent_model = new MAgent();

        $isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
	    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
	    }
	    $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
	    $cli = "";
	    $did = "";
	    
	    if ($this->gridRequest->isMultisearch){
	        $cli = $this->gridRequest->getMultiParam('cli');
	        $did = $this->gridRequest->getMultiParam('did');
	        
	        if ($type=='outbound') {
	            $pageTitle = 'Skill CDR - Outbound';
                $cli = $this->gridRequest->getMultiParam('callto');
	        } else {
	            $type = 'inbound';
	            $pageTitle = 'Skill CDR - Inbound';
	        }
	        
	        $audit_text = '';
	        $audit_text .= DateHelper::get_date_log($dateinfo);
	        $report_model->addToAuditLog($pageTitle, 'V', "", $audit_text);

            if ($isEnableSMS){
                if (empty($cli) && empty($did) && empty($skillid) && empty($aid) && empty($callid) && empty($status)){
                    if ((empty($dateinfo->edate) || $dateinfo->edate == date('Y-m-d')) && !empty($isSMSReq) && $isSMSReq == 'Y'){
                        $sessObject = UserAuth::getCDRSearchParams();
                        $dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
                        $cli = isset($sessObject->cli) ? $sessObject->cli : "";
                        $did = isset($sessObject->did) ? $sessObject->did : "";
                    }
                }
            }
	    }

	    $aid = UserAuth::getCurrentUser();
	    $skills = $agent_model->getAgentSkill($aid);
	    
	    if ($type=='outbound') {
	        $pageTitle = 'Skill CDR - Outbound';
	        $ivr_options = null;
	        $template_file = 'agent_skill_cdr_outbound';
	    } else {
	        $type = 'inbound';
	        $pageTitle = 'Skill CDR - Inbound';
	        $ivr_options = $ivr_model->getIvrOptions();
	        $template_file = 'agent_skill_cdr_inbound';
	    }

        $sessObject = new stdClass();
        $sessObject->dateinfo = $dateinfo;
        $sessObject->cli = $cli;
        $sessObject->did = $did;
        $sessObject->type = $type;
        UserAuth::setCDRSearchParams($sessObject);
	    
	    $skill_options = $skill_model->getAllSkillOptions('', 'array');
	    $this->pagination->num_records = $report_model->numSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo);
	    
	    $callsData = $this->pagination->num_records > 0 ?
	    $report_model->getSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $isEnableSMS) : null;
	
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	
	    $result=&$callsData;
	    if(count($result) > 0){
	        $languages = array('E'=>'ENG', 'B'=>'BAN');
	        $curAgentId = UserAuth::getCurrentUser();
	        foreach ( $result as &$data ) {
	            $total_time = 0;
	            $data->ivr_enter_time = empty($data->ivr_enter_time) ? '-' : $data->ivr_enter_time;
	            $time_in_ivr = empty($data->time_in_ivr) ? 0 : $data->time_in_ivr;
	            $data->start_time = empty($data->start_time) ? '-' : $data->start_time;
	            $stop_time = empty($data->stop_time) ? $data->cdr_stop_time : $data->stop_time;
	            if (empty($data->stop_time)) {
	                $stop_time = $data->cdr_stop_time;
	                if (empty($stop_time)) {
	                    $stop_time = '-';
	                } else {
	                    $total_time = strtotime($data->cdr_stop_time) - strtotime($data->start_time);
	                }
	            } else {
	                $stop_time = $data->stop_time;
	            }

                if (empty($data->cli)){
                    $data->cli = '-';
                }else{
                    if (!empty($data->sms_callid)){
                        $data->cli = "<span class='text-success'>".$data->cli."</span>";
                    }else{
                        $data->cli = $data->cli;
                    }
                }
                if (empty($data->callto)){
                    $data->callto = '-';
                }else{
                    if (!empty($data->sms_callid)){
                        $data->callto = "<span class='text-success'>".$data->callto."</span>";
                    }else{
                        $data->callto = $data->callto;
                    }
                }

	            $data->stop_time = $stop_time;
	            //$data->cli = empty($data->cli) ? '-' : $data->cli;
	            $data->did = empty($data->did) ? '-' : $data->did;
	            $data->ivr_name = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
	            $data->time_in_ivr = DateHelper::get_formatted_time($time_in_ivr);
	            $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
	            
	            $ivr_language = empty($data->ivr_language) ? '-' : $data->ivr_language;
	            if (isset($languages[$ivr_language])) $ivr_language = $languages[$ivr_language];
	            $data->ivr_language = $ivr_language;
	            
	            $total_time = $total_time <= 0 ? $time_in_ivr + $data->hold_in_q + $data->service_time : $total_time;
	            
	            $data->hold_in_q = DateHelper::get_formatted_time($data->hold_in_q);
	            $data->service_time = DateHelper::get_formatted_time($data->service_time);
	            
	            if ($type=='outbound') {
	                if ($data->is_reached == 'Y') {
	                    $skill_status = 'Answered';
	                } else {
	                    $skill_status = 'No Answer';
	                }
	            } else {
	                if (!empty($data->skill_status)) {
	                    if ($data->skill_status == 'S') $skill_status = 'Served';
	                    else if ($data->skill_status == 'A') $skill_status = 'Abandoned';
	                    else $skill_status = $data->skill_status;
                        } else {
	                    if (empty($data->skill_id)) $skill_status = '-';
	                    else $skill_status = 'Droped';
                        }
	            }
	            $data->skill_status = $skill_status;
	            $data->agent_id = isset($data->agent_id) && !empty($data->agent_id) ? $data->agent_id : "-";
	            $data->total_time = DateHelper::get_formatted_time($total_time);
	            $data->callerid = empty($data->callerid) ? '-' : $data->callerid;
	            $alarm = empty($data->alarm) ? 0 : $data->alarm;
	            $data->alarm = !empty($data->alarm) && $data->alarm > 0 ? '<font color="red">' . $data->alarm . '</font>' : '0';
	            if ($data->disp_agent == $curAgentId) {
	                $data->disposition = '<a class="btn btn-mini" href="'. $this->url("task=agent&act=crm-in-details&cid=".$data->callid) .'">update</a>';
	            } else {
	                $data->disposition = '-';
	            }
                if ($isEnableSMS){
                    if ($type=='inbound') {
                        $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->cli."@".$data->callid."@".$data->skill_id.'">';
                    }else {
                        $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->callto."@".$data->callid."@".$data->skill_id.'">';
                    }
                }
	        }
	    }
	    $responce->rowdata = $result;
	  $this->ShowTableResponse();
	}

        function actionVmlog()
        {
            include('model/MAgentReport.php');
            include('model/MSkill.php');
            include('model/MAgent.php');
            include('lib/DateHelper.php');

            $report_model = new MAgentReport();
            $skill_model = new MSkill();
            $agent_model = new MAgent();
            
            $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "N";
            if (strlen($status) != 1) $status = "N";

            $dateTimeArray = array();
            if ($this->gridRequest->isMultisearch){
                $dateTimeArray = $this->gridRequest->getMultiParam('stop_time');
            }
            $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
            $cli = "";
            $did = "";
            $agent_id = "";
            if ($this->gridRequest->isMultisearch){
                $cli = $this->gridRequest->getMultiParam('cli');
                $did = $this->gridRequest->getMultiParam('did');
                $agent_id = $this->gridRequest->getMultiParam('agent_id');

                $audit_text = '';
                $audit_text .= DateHelper::get_date_log($dateinfo);
                $report_model->addToAuditLog('Voice Mail', 'V', "", $audit_text);
            }

            $aid = UserAuth::getCurrentUser();
            $skills = $agent_model->getAgentSkill($aid);

            $skill_options = $skill_model->getAllSkillOptions('', 'array');
            $agent_options = $agent_model->getAgentNames('','','','','array');
            
            $this->pagination->num_records = $report_model->numVMLogs($aid, $skills, $cli, $did, $agent_id, $dateinfo, $status);

            $callsData = $this->pagination->num_records > 0 ?
            $report_model->getVMLogs($aid, $skills, $cli, $did, $agent_id, $dateinfo, $status, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $responce = $this->getTableResponse();
            $responce->records = $this->pagination->num_records;

	    $result=&$callsData;
            if(count($result) > 0){
                foreach ( $result as &$data ) {
                    $total_time = 0;
                    
                    if (empty($data->agent_id)) {
                        $data->agent_id = '-';
                        $data->nick = '-';
                        $data->served_time = '-';
                    } else {
                        $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : '-';
                    }
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    
                    $data->duration = gmdate("H:i:s", $data->duration);
                    
                    if ($data->status == 'N') {
                        //$data->status = 'New';
                        /*
                        $data->status="<a class='ConfirmAjaxWR' msg='Are you sure to mark this voicemail as served?' ".
                            "href='" . $this->url( "task=confirm-response&act=vm&cid=$data->callid&ts=". $data->tstamp . "&st=S&pro=st").
                            "'>"."<span class='text-danger'>New</span>"."</a>";
                        */
                        $data->status="<span class='text-danger'>New</span>";
                    } elseif ($data->status == 'R') {
                        //$data->status = 'Listened';
                        $data->status="<a class='ConfirmAjaxWR' msg='Are you sure to mark this voicemail as served?' ".
                            "href='" . $this->url( "task=confirm-response&act=vm&cid=$data->callid&ts=". $data->tstamp . "&st=S&pro=st").
                            "'>"."Listened"."</a>";
                    } elseif ($data->status == 'S') {
                        $data->status = 'Served';
                    } else {
                        $data->status = 'N/A';
                    }
                    
                    if (strlen($data->cli) == 11 && substr($data->cli, 0, 1) == '1') {
                        if (preg_match( '/^\d(\d{3})(\d{3})(\d{4})$/', $data->cli,  $matches ) ) {
                            $data->cli = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
                        }
                    }
                    
                    if (strlen($data->did) == 11 && substr($data->did, 0, 1) == '1') {
                        if (preg_match( '/^\d(\d{3})(\d{3})(\d{4})$/', $data->did,  $matches ) ) {
                            $data->did = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
                        }
                    }
                    
                    //$playUrl = $this->url("task=vm&act=voice&cid=".$data->callid."&ts=".$data->tstamp);
                    $data->audio .= " <a class=\"btn btn-xs btn-success\" href=\"#\" onclick=\"playVMB('$data->callid', '$data->tstamp');return false;\"><i class=\"fa fa-play\"></i> Play</a>";
                    /*
                    $data->ivr_enter_time = empty($data->ivr_enter_time) ? '-' : $data->ivr_enter_time;
                    $time_in_ivr = empty($data->time_in_ivr) ? 0 : $data->time_in_ivr;
                    $data->start_time = empty($data->start_time) ? '-' : $data->start_time;
                    $stop_time = empty($data->stop_time) ? $data->cdr_stop_time : $data->stop_time;
                    if (empty($data->stop_time)) {
                        $stop_time = $data->cdr_stop_time;
                        if (empty($stop_time)) {
                            $stop_time = '-';
                        } else {
                            $total_time = strtotime($data->cdr_stop_time) - strtotime($data->start_time);
                        }
                    } else {
                        $stop_time = $data->stop_time;
                    }
                    $data->stop_time = $stop_time;
                    $data->cli = empty($data->cli) ? '-' : $data->cli;
                    $data->did = empty($data->did) ? '-' : $data->did;
                    $data->ivr_name = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
                    $data->time_in_ivr = DateHelper::get_formatted_time($time_in_ivr);
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $total_time = $total_time <= 0 ? $time_in_ivr + $data->hold_in_q + $data->service_time : $total_time;

                    $data->hold_in_q = DateHelper::get_formatted_time($data->hold_in_q);
                    $data->service_time = DateHelper::get_formatted_time($data->service_time);

                    if ($type=='outbound') {
                        if ($data->is_reached == 'Y') {
                            $skill_status = 'Answered';
                        } else {
                            $skill_status = 'No Answer';
                        }
                    } else {
                        if (!empty($data->skill_status)) {
                            if ($data->skill_status == 'S') $skill_status = 'Served';
                            else if ($data->skill_status == 'A') $skill_status = 'Abandoned';
                            else $skill_status = $data->skill_status;
                        } else {
                            if (empty($data->skill_id)) $skill_status = '-';
                            else $skill_status = 'Droped';
                        }
                    }
                    $data->skill_status = $skill_status;
                    $data->agent_id = isset($data->agent_id) && !empty($data->agent_id) ? $data->agent_id : "-";
                    $data->total_time = DateHelper::get_formatted_time($total_time);
                    $data->callerid = empty($data->callerid) ? '-' : $data->callerid;
                    $alarm = empty($data->alarm) ? 0 : $data->alarm;
                    $data->alarm = !empty($data->alarm) && $data->alarm > 0 ? '<font color="red">' . $data->alarm . '</font>' : '0';
                    */
                }
            }
            $responce->rowdata = $result;
          $this->ShowTableResponse();

	}	
	
	
        function actionMyVmlog()
        {
            include('model/MAgentReport.php');
            include('lib/DateHelper.php');

            $report_model = new MAgentReport();
            
            $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "N";
            if (strlen($status) != 1) $status = "N";

            $dateTimeArray = array();
            if ($this->gridRequest->isMultisearch){
                $dateTimeArray = $this->gridRequest->getMultiParam('stop_time');
            }
            $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
            $cli = "";
            $did = "";
            if ($this->gridRequest->isMultisearch){
                $cli = $this->gridRequest->getMultiParam('cli');
                $did = $this->gridRequest->getMultiParam('did');

                $audit_text = '';
                $audit_text .= DateHelper::get_date_log($dateinfo);
                $report_model->addToAuditLog('Voice Mail', 'V', "", $audit_text);
            }

            $aid = UserAuth::getCurrentUser();
            
            $this->pagination->num_records = $report_model->numAgentVMLogs($aid, $cli, $did, $dateinfo, $status);

            $callsData = $this->pagination->num_records > 0 ?
            $report_model->getAgentVMLogs($aid, $cli, $did, $dateinfo, $status, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $responce = $this->getTableResponse();
            $responce->records = $this->pagination->num_records;

	    $result=&$callsData;
            if(count($result) > 0){
                foreach ( $result as &$data ) {
                    $total_time = 0;
                    
                    
                    $data->duration = gmdate("H:i:s", $data->duration);
                    
                    if ($data->status == 'N') {
                        $data->status="<span class='text-danger'>New</span>";
                    } elseif ($data->status == 'R') {
                        //$data->status = 'Listened';
                        $data->status="<a class='ConfirmAjaxWR' msg='Are you sure to mark this voicemail as served?' ".
                            "href='" . $this->url( "task=confirm-response&act=vm&cid=$data->callid&ts=". $data->tstamp . "&st=S&pro=st").
                            "'>"."Listened"."</a>";
                    } elseif ($data->status == 'S') {
                        $data->status = 'Served';
                    } else {
                        $data->status = 'N/A';
                    }
                    
                    if (strlen($data->cli) == 11 && substr($data->cli, 0, 1) == '1') {
                        if (preg_match( '/^\d(\d{3})(\d{3})(\d{4})$/', $data->cli,  $matches ) ) {
                            $data->cli = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
                        }
                    }
                    
                    if (strlen($data->did) == 11 && substr($data->did, 0, 1) == '1') {
                        if (preg_match( '/^\d(\d{3})(\d{3})(\d{4})$/', $data->did,  $matches ) ) {
                            $data->did = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
                        }
                    }
                    
                    $data->audio .= " <a class=\"btn btn-xs btn-success\" href=\"#\" onclick=\"playVMB('$data->callid', '$data->tstamp');return false;\"><i class=\"fa fa-play\"></i> Play</a>";
                }
            }
            $responce->rowdata = $result;
          $this->ShowTableResponse();

	}

    function actionAgentSkillCdrNew() {
        header('Access-Control-Allow-Origin: "*"');
        //Access-Control-Allow-Origin
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: application/json');

        include('model/MAgentReport.php');
//        include('model/MSkill.php');
//        include('model/MIvr.php');
//        include('model/MAgent.php');
        include('lib/DateHelper.php');
//        include('conf.sms.php');

        $report_model = new MAgentReport();
//        $skill_model = new MSkill();
//        $ivr_model = new MIvr();
//        $agent_model = new MAgent();

//        $isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $cli = "";
        $did = "";
        $callid_cti = "";

        if ($this->gridRequest->isMultisearch){
            $cli = $this->gridRequest->getMultiParam('cli');
            $did = $this->gridRequest->getMultiParam('did');
            $callid_cti = $this->gridRequest->getMultiParam('callid_cti');

            if ($type=='outbound') {
//                $pageTitle = 'Skill CDR - Outbound';
//                $cli = $this->gridRequest->getMultiParam('callto');
            } else {
                $type = 'inbound';
                $pageTitle = 'Skill CDR - Inbound';
            }

            $audit_text = '';
            $audit_text .= DateHelper::get_date_log($dateinfo);
            $report_model->addToAuditLog($pageTitle, 'V', "", $audit_text);

            /*if ($isEnableSMS){
                if (empty($cli) && empty($did) && empty($skillid) && empty($aid) && empty($callid) && empty($status)){
                    if ((empty($dateinfo->edate) || $dateinfo->edate == date('Y-m-d')) && !empty($isSMSReq) && $isSMSReq == 'Y'){
                        $sessObject = UserAuth::getCDRSearchParams();
                        $dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
                        $cli = isset($sessObject->cli) ? $sessObject->cli : "";
                        $did = isset($sessObject->did) ? $sessObject->did : "";
                    }
                }
            }*/
        }

        $aid = UserAuth::getCurrentUser();
        //$skills = $agent_model->getAgentSkill($aid);

        if ($type=='outbound') {
            $pageTitle = 'Skill CDR - Outbound';
            $ivr_options = null;
            $template_file = 'agent_skill_cdr_outbound';
        } else {
            $type = 'inbound';
            $pageTitle = 'Skill CDR - Inbound';
            //$ivr_options = $ivr_model->getIvrOptions();
            $template_file = 'agent_skill_cdr_inbound';
        }

        $sessObject = new stdClass();
        $sessObject->dateinfo = $dateinfo;
        $sessObject->cli = $cli;
        $sessObject->callid = $callid_cti;
        $sessObject->did = $did;
        $sessObject->type = $type;
        UserAuth::setCDRSearchParams($sessObject);

        //$skill_options = $skill_model->getAllSkillOptions('', 'array');
//        $this->pagination->num_records = $report_model->numSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo);
        $this->pagination->num_records = $report_model->numSkillCDRsNew($type, '', $callid_cti, $did, $aid, $dateinfo);
        $callsData = $this->pagination->num_records > 0 ?
            $report_model->getSkillCDRsNew($type, '', $callid_cti, $did, $aid, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $isEnableSMS) : null;
//        GPrint($this->pagination->num_records);die;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$callsData;
        if(count($result) > 0){
            $languages = array('E'=>'ENG', 'B'=>'BAN');
            $curAgentId = UserAuth::getCurrentUser();
            foreach ( $result as &$data ) {
                $total_time = 0;
                /*$data->ivr_enter_time = empty($data->ivr_enter_time) ? '-' : $data->ivr_enter_time;
                $time_in_ivr = empty($data->time_in_ivr) ? 0 : $data->time_in_ivr;
                $data->start_time = empty($data->start_time) ? '-' : $data->start_time;
                $stop_time = empty($data->stop_time) ? $data->cdr_stop_time : $data->stop_time;
                if (empty($data->stop_time)) {
                    $stop_time = $data->cdr_stop_time;
                    if (empty($stop_time)) {
                        $stop_time = '-';
                    } else {
                        $total_time = strtotime($data->cdr_stop_time) - strtotime($data->start_time);
                    }
                } else {
                    $stop_time = $data->stop_time;
                }

                if (empty($data->cli)){
                    $data->cli = '-';
                }else{
                    if (!empty($data->sms_callid)){
                        $data->cli = "<span class='text-success'>".$data->cli."</span>";
                    }else{
                        $data->cli = $data->cli;
                    }
                }
                if (empty($data->callto)){
                    $data->callto = '-';
                }else{
                    if (!empty($data->sms_callid)){
                        $data->callto = "<span class='text-success'>".$data->callto."</span>";
                    }else{
                        $data->callto = $data->callto;
                    }
                }

                $data->stop_time = $stop_time;*/
                //$data->cli = empty($data->cli) ? '-' : $data->cli;
                //$data->did = empty($data->did) ? '-' : $data->did;
                //$data->ivr_name = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
                //$data->time_in_ivr = DateHelper::get_formatted_time($time_in_ivr);
                //$data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;

                //$ivr_language = empty($data->ivr_language) ? '-' : $data->ivr_language;
                //if (isset($languages[$ivr_language])) $ivr_language = $languages[$ivr_language];
                //$data->ivr_language = $ivr_language;

                $total_time = $data->ring_time + $data->service_time + $data->wrap_up_time + $data->hold_time;
                $data->total_time = DateHelper::get_formatted_time($total_time);

                $data->hold_in_q = DateHelper::get_formatted_time($data->hold_time);
                $data->service_time = DateHelper::get_formatted_time($data->service_time);
                $data->ring_time = DateHelper::get_formatted_time($data->ring_time);
                $data->wrap_up_time = DateHelper::get_formatted_time($data->wrap_up_time);

                if ($type=='outbound') {
                    /*if ($data->is_reached == 'Y') {
                        $skill_status = 'Answered';
                    } else {
                        $skill_status = 'No Answer';
                    }*/
                } else {
                    $data->is_answer =  $data->is_answer=='Y' ? "Answered" : "Not Answered";
                }
                $data->disc_party = !empty($data->disc_party) ? get_disc_party($data->disc_party) : "";
                //$data->skill_status = $skill_status;
                //$data->agent_id = isset($data->agent_id) && !empty($data->agent_id) ? $data->agent_id : "-";

                //$data->callerid = empty($data->callerid) ? '-' : $data->callerid;
                //$alarm = empty($data->alarm) ? 0 : $data->alarm;
                //$data->alarm = !empty($data->alarm) && $data->alarm > 0 ? '<font color="red">' . $data->alarm . '</font>' : '0';
                /*if ($data->disp_agent == $curAgentId) {
                    $data->disposition = '<a class="btn btn-mini" href="'. $this->url("task=agent&act=crm-in-details&cid=".$data->callid) .'">update</a>';
                } else {
                    $data->disposition = '-';
                }*/
                /*if ($isEnableSMS){
                    if ($type=='inbound') {
                        $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->cli."@".$data->callid."@".$data->skill_id.'">';
                    }else {
                        $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->callto."@".$data->callid."@".$data->skill_id.'">';
                    }
                }*/
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}
