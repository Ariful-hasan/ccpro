<?php
require_once 'BaseTableDataController.php';
class GetReportData extends BaseTableDataController
{
    function __construct() {
        parent::__construct();
    }
    function actionSeats(){
        include('model/MSetting.php');
        $setting_model = new MSetting();
        $this->pagination->num_records = $setting_model->numSeats();
        $result = $this->pagination->num_records > 0 ?
            $setting_model->getSeats('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if(count($result)>0){
            foreach ( $result as &$data ) {

                $data->active="<a class='ConfirmAjaxWR' msg='Be confirm to do ".($data->active=="Y"?"Inactive":"Active")." seat no :".$data->label."?' href='" . $this->url( "task=confirm-response&act=seats&pro=cs&seatid=".$data->seat_id."&active=".($data->active=="Y"?"N":"Y"))."'>".($data->active=="Y"?"<span class='text-success'>Active</span>":"<span class='text-danger'>Inactive</span>")."</a>";


                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete seat :".$data->label."?' href='" . $this->url( "task=confirm-response&act=seats&pro=da&sid={$data->seat_id}")."'><i class='fa fa-times'></i> Delete</a>";
                $data->label = "<a href='".$this->url("task=settseats&act=update&seatid={$data->seat_id}")."'>".$data->label."</a>";

            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionSkillCdr()
    {
        include('model/MReport.php');
        include('model/MSkill.php');
        include('model/MIvr.php');
        include('model/MSetting.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('conf.sms.php');

        $report_model = new MReport();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();
        $setting_model = new MSetting();
        $agent_model = new MAgent();



        /* $report_model = new MAgentReport();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();
        $agent_model = new MAgent(); */
        $isEnableSMS = isset($smsConfObj->sms_send_enable) && $smsConfObj->sms_send_enable ? true : false;
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $isSMSReq = isset($_GET['sms']) ? $_GET['sms'] : "";
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $cli = "";
        $did = "";
        $skillid = "";
        $aid = "";
        $callid = "";
        $status = "";
        $ivr_id = "";

        if ($this->gridRequest->isMultisearch){
            $cli = $this->gridRequest->getMultiParam('cli');
            $did = $this->gridRequest->getMultiParam('did');
            $skillid = $this->gridRequest->getMultiParam('skillid');
            $skillid = !empty($skillid) ? str_replace(array("(",")"," ","-"), "", $skillid) : "";
            $aid = $this->gridRequest->getMultiParam('agent_id');
            $callid = $this->gridRequest->getMultiParam('callid');
            $status = $this->gridRequest->getMultiParam('skill_status');
            $status=$status=="*"?"":$status;

            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
            $ivr_id = !empty($ivr_id) ? str_replace(array("(",")"," ","-"), "", $ivr_id) : "";

            if ($type=='outbound') {
                $pageTitle = 'Skill CDR - Outbound';
                $cli = $this->gridRequest->getMultiParam('agent_id');
                $did = $this->gridRequest->getMultiParam('callto');
            } else {
                if ($type == 'ivr-inbound') {
                    $pageTitle = 'IVR Log - Inbound';
                } else {
                    $type = 'inbound';
                    $pageTitle = 'Skill CDR - Inbound';
                }
            }

            $audit_text = '';
            if (!empty($skillid)) {
                $_opt = isset($skill_options[$skillid]) ? $skill_options[$skillid] : $skillid;
                $audit_text .= "Skill=$_opt;";
            }
            $audit_text .= DateHelper::get_date_log($dateinfo);
            $report_model->addToAuditLog($pageTitle, 'V', "", $audit_text);

            if ($isEnableSMS){
                if (empty($cli) && empty($did) && empty($skillid) && empty($aid) && empty($callid) && empty($status)){
                    if ((empty($dateinfo->edate) || $dateinfo->edate == date('Y-m-d')) && !empty($isSMSReq) && $isSMSReq == 'Y'){
                        $sessObject = UserAuth::getCDRSearchParams();
                        $dateinfo = isset($sessObject->dateinfo) ? $sessObject->dateinfo : null;
                        $cli = isset($sessObject->cli) ? $sessObject->cli : "";
                        $did = isset($sessObject->did) ? $sessObject->did : "";
                        $skillid = isset($sessObject->skillid) ? $sessObject->skillid : "";
                        $aid = isset($sessObject->aid) ? $sessObject->aid : "";
                        $callid = isset($sessObject->callid) ? $sessObject->callid : "";
                        $status = isset($sessObject->status) ? $sessObject->status : "";
                        $ivr_id = isset($sessObject->ivr_id) ? $sessObject->ivr_id : "";
                    }
                }
            }
        }

        $sessObject = new stdClass();
        $sessObject->dateinfo = $dateinfo;
        $sessObject->cli = $cli;
        $sessObject->did = $did;
        $sessObject->skillid = $skillid;
        $sessObject->aid = $aid;
        $sessObject->callid = $callid;
        $sessObject->status = $status;
        $sessObject->ivr_id = $ivr_id;
        $sessObject->type = $type;
        UserAuth::setCDRSearchParams($sessObject);

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if ($type=='outbound') {
            $pageTitle = 'Skill CDR - Outbound';
            $ivr_options = null;
        } else {
            if ($type == 'ivr-inbound') {
                $type = 'ivr-inbound';
            } else {
                $type = 'inbound';
                $pageTitle = 'Skill CDR - Inbound';
            }
            $ivr_options = $ivr_model->getIvrOptions();
        }

        $skill_options = $skill_model->getAllSkillOptions('', 'array');

        /*if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $errMsg = UserAuth::getRepErrMsg();
            $errType = 1;
            $this->pagination->num_records = 0;
        }else {
        //echo '
            $this->pagination->num_records = $report_model->numSkillCDRs($type, $skillid, $cli, $did, $aid, $callid, $dateinfo, $status, $ivr_id);
        }*/
        // echo "type:".$type. "skillid:".$skillid. "cli:".$cli. "did:".$did. "aid:".$aid. "fd:".$dateinfo;

        $this->pagination->num_records = $report_model->numSkillCDRs($type, $skillid, $cli, $did, $aid, $callid, $dateinfo, $status, $ivr_id);
        $callsData = $this->pagination->num_records > 0 ? $report_model->getSkillCDRs($type, $skillid, $cli, $did, $aid, $callid, $status,$ivr_id, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $isEnableSMS) : null;
        $trunk_options = is_array($callsData) ? $setting_model->getTrunkOptions() : array();

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $result=&$callsData;

        $backup_yyyy_mm_dd = "";
        if ($this->getTemplate()->backup_transfer_aft_month > 0) {
            $backup_yyyy_mm_dd = date("Y_m_d", strtotime("-".$this->getTemplate()->backup_transfer_aft_month." months"));
        }

        if(count($result) > 0){
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            $languages = array('E'=>'ENG', 'B'=>'BAN');
            $curAgentId = UserAuth::getCurrentUser();
            foreach ( $result as &$data ) {
                $data->total_time = 0;
                $total_time = 0;
                $data->ivr_enter_time = empty($data->ivr_enter_time) ? '-' : $data->ivr_enter_time;
                $time_in_ivr = empty($data->time_in_ivr) ? 0 : $data->time_in_ivr;
                $data->start_time = empty($data->start_time) ? '-' : $data->start_time;
                $stop_time = empty($data->stop_time) ? $data->cdr_stop_time : $data->stop_time;
                //$data->track = '1';
                if (empty($data->stop_time)) {
                    $stop_time = $data->cdr_stop_time;
                    if (empty($stop_time) && $type='outbound') {
                        $stop_time = date('Y-m-d H:i:s', strtotime('+'.$data->service_time.'seconds', strtotime($data->start_time)));
                    } elseif (empty($stop_time)) {
                        $stop_time = '-';
                    } else {
                        $total_time = $data->cdr_stop_time - $data->start_time;
                    }
                } else {
                    $stop_time = $data->stop_time;
                }
                //$data->track .= $total_time . ';';
                $data->stop_time = $stop_time;
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

                $data->did = empty($data->did) ? '-' : $data->did;
                if ($type=='inbound' || $type=='ivr-inbound') {
                    $data->ivr_name = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
                }
                if (empty($data->ivr_name)) $data->ivr_name = '-';
                if (empty($data->trunkid)) $trunk_name = '-';
                else $trunk_name = isset($trunk_options[$data->trunkid]) ?  $trunk_options[$data->trunkid] : $data->trunkid;

                $data->trunkid = $trunk_name;

                if ($type=='inbound' || $type=='ivr-inbound') {
                    $total_time = $total_time <= 0 ? $time_in_ivr + $data->hold_in_q + $data->service_time : $total_time;
                    if ($data->total_time == 0 && empty($data->skill_id) & empty($data->ivr_id)) {
                        $total_time = $data->duration;
                        $data->service_time = $data->talk_time;
                    }
                } else {
                    $total_time = $data->talk_time;
                    $data->is_reached = $data->is_reached == 'Y' ? 'Answered' : 'N/A';
                }
                $data->time_in_ivr = DateHelper::get_formatted_time($time_in_ivr);
                $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;

                $ivr_language = empty($data->ivr_language) ? '-' : $data->ivr_language;
                if (isset($languages[$ivr_language])) $ivr_language = $languages[$ivr_language];
                $data->ivr_language = $ivr_language;
                if ($type=='inbound' || $type=='ivr-inbound') {
                    $data->hold_in_q = DateHelper::get_formatted_time($data->hold_in_q);
                }

                $data->service_time = DateHelper::get_formatted_time($data->service_time);
                /*
                if ($type=='inbound') {
                    $total_time = $total_time <= 0 ? $time_in_ivr + $data->hold_in_q + $data->service_time : $total_time;
                }else {
                    $total_time = $data->talk_time;
                    $data->is_reached = $data->is_reached == 'Y' ? 'Answered' : 'N/A';
                }
                */
                $data->talk_time = DateHelper::get_formatted_time($total_time);

                $data->service_quality = "<a class='lightboxWIF' href='".$this->url("task=cdr&act=rate-call-quality&callid={$data->callid}&skill_id={$data->skill_id}&agent_id={$data->agent_id}&call_time={$data->start_time}")."'> <i class='fa fa-edit'></i> Service Quality </a>";


                if (!empty($data->skill_status)) {
                    if ($data->skill_status == 'S') $skill_status = 'Served';
                    elseif ($data->skill_status == 'A') $skill_status = 'Abandoned';
                    else $skill_status = $data->skill_status;
                } else {
                    if (empty($data->skill_id)) $skill_status = '-';
                    else $skill_status = 'Droped';
                }
                $data->skill_status = $skill_status;
                if (empty($data->agent_id)) {
                    $data->agent_id =  '-';
                    $data->nick = "-";
                } else {
                    $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                    $data->agent_id = $data->agent_id == 'VM' ? 'VM' : "<a data-w='900' data-h='450'  href=\"index.php?task=calldetails&act=agent&cid=$data->callid\" class=\"lightboxWIF\">$data->agent_id</a>";
                }
                $data->total_time = DateHelper::get_formatted_time($total_time);
                $data->callerid = empty($data->callerid) ? '-' : $data->callerid;
                $alarm = empty($data->alarm) ? 0 : $data->alarm;
                $alermtitle = "<a data-w='900' data-h='450'  href=\"index.php?task=calldetails&act=agent&cid=$data->callid\" class=\"lightboxWIF\">$data->agent_id</a>";
                $data->alarm = !empty($data->alarm) && $data->alarm > 0 ? "<a data-w='900' data-h='450'  href=\"index.php?task=calldetails&act=agent&fskill=1&cid=$data->callid\" class=\"lightboxWIF\"><font color='red'>" . $data->alarm . "</font></a>" : '0';
                $data->disc_cause = empty($data->disc_cause) ? '-' : $data->disc_cause;

                if ($data->disc_party == '1') $data->disc_party = 'A-Party';
                elseif ($data->disc_party == '2') $data->disc_party = 'B-Party';
                else $data->disc_party = $data->disc_party;

                if (empty($data->trunkid)) $data->trunk_name = '-';
                else $data->trunk_name = isset($trunk_options[$data->trunkid]) ?  $trunk_options[$data->trunkid] : $data->trunkid;

                $callid = empty($data->callid_sl) ? $data->callid : $data->callid_sl;
                //when IVR call id is different
                if ($callid == $data->callid && !empty($data->callid_ivr)){
                    $callid = $data->callid_ivr;
                    //                }else{
                    //               $data->callid = $callid;
                }

                $data->callid = $callid;

                if ($type=='inbound') {
                    $data->callid = $data->callid_sl;
                } else if ($type=='ivr-inbound') {
                    $data->callid = $data->callid_ivr;
                }

                if (!empty($callid)) {
                    $file_timestamp = substr($callid, 0, 10);
                    $yyyy = date("Y", $file_timestamp);
                    $yy = substr($yyyy, 2, 2);
                    $yyyy_mm_dd = date("Y_m_d", $file_timestamp);
                    $mm = substr($yyyy_mm_dd, 5, 2);
                    $dd = substr($yyyy_mm_dd, 8, 2);
                    $data->audio = '';
                    $data->sip_log = '';

                    if ($yyyy_mm_dd > $backup_yyyy_mm_dd) {
                        $vl_path = $this->getTemplate()->voice_logger_path;
                        $sip_path = $this->getTemplate()->sip_logger_path;
                    } else {
                        $vl_path = $this->getTemplate()->backup_hdd_path;
                        $sip_path = $this->getTemplate()->backup_hdd_path;
                    }
// var_dump($type);
// var_dump($vl_path);
                    if ($type == 'ivr-inbound') {
                        $sound_file_gsm = '';
                    } else {
                        $sound_file_gsm = $vl_path . "vlog/$yyyy/$yyyy_mm_dd/" . $callid . ".wav";
                        if (!file_exists($sound_file_gsm)) {
                            $sound_file_gsm = $vl_path . "vlog/$yyyy/$yyyy_mm_dd/" . $callid . ".mp3";
                            if (!file_exists($sound_file_gsm)) {
                                $sound_file_gsm = $vl_path . "vlog/$yyyy/$yyyy_mm_dd/" . $callid . ".ulaw";
                                if (!file_exists($sound_file_gsm)) {
                                    $sound_file_gsm = '';
                                }
                            }
                        }
                    }
                    // var_dump($sound_file_gsm);
                    if (!empty($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
                        $playUrl = $this->url("task=cdr&act=listen-audio-file&cid=".$callid."&cdr_page=".$pageTitle);
                        $playAudioUrl = $this->getTemplate()->getBasePath() . $this->getTemplate()->bkp_audio_url;
                        $downloadUrl = $this->url("task=cdr&act=playbrowser&cid=".$callid."&cdr_page=".$pageTitle);
                        //$data->audio = "<a href=\"#\" onClick=\"playFile('$callid')\"><img src=\"image/play2.gif\" title=\"gsm\" border=0></a>";
                        $data->audio .= " <a href='javascript:void(0)' class='playAudioFile' onClick='generateAudio(\"$playAudioUrl\", \"$callid\", this)' title='Play in browser' border='0'><i class=\"fa fa-play-circle\"></i></a> &nbsp; ";
                        $userRole = UserAuth::getRoleID();
                        $userRole = empty($userRole) ? 'S' : $userRole;
                        if ($this->getTemplate()->is_voice_file_downloadable && $userRole == 'R') {
                            $data->audio .= " <a title='Download audio file' href=\"$downloadUrl\"><i class=\"fa fa-download\"></i></a> &nbsp;";
                        }
                        $sound_file_gsm = $this->getTemplate()->voice_logger_path . "screen_log/$yyyy/$yyyy_mm_dd/" . $callid . ".zip";
                        if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
                            $playUrl = $this->url("task=cdr&act=playvideo&cid=".$callid);
                            $data->audio .= " <a target=\"blank\" href=\"$playUrl\"><img src=\"image/play2.gif\" title=\"Play video\" border=0 width=\"15\"></a>";
                        }
                    } else {
                        $data->audio = "NONE";
                    }
                    $sip_file = $sip_path . "siplog/$yy/$mm/$dd/" . $callid . ".sip";

                    //$data->audio .= " $sip_file";

                    if (file_exists($sip_file)) {
                        $data->sip_log = " <a title='SIP Log' data-h='400px' data-w='600px' class='lightboxWIFR' href=\"index.php?task=cdr&act=sip-log&cid=$callid\"><i class=\"fa fa-file\"></i></a>";
                        //$data->audio .= " <a title='SIP Log' data-h='400px' data-w='600px' class='lightboxWIFR' href=\"index.php?task=cdr&act=sip-log&cid=$callid\"><i class=\"fa fa-file\"></i></a>";
                    } else {
                        $data->sip_log = "NONE";
                    }
                    if ($isEnableSMS){
                        if ($type=='inbound' || $type=='ivr-inbound') {
                            $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->cli."@".$data->callid."@".$data->skill_id.'">';
                        }else {
                            $data->chkbox_sndsms = '<input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="'.$data->callto."@".$data->callid."@".$data->skill_id.'">';
                        }
                    }
                } else {
                    $data->audio = "NONE";
                    $data->sip_log = "NONE";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAutodialCdr()
    {
        include('model/MReport.php');
        include('model/MSetting.php');
        include('lib/DateHelper.php');

        $report_model = new MReport();
        $setting_model = new MSetting();

        $dateinfo = DateHelper::get_input_time_details();
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }

        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $option = "";
        $stext = "";

        if ($this->gridRequest->isMultisearch){
            $option = $this->gridRequest->getMultiParam('option');
            $stext = $this->gridRequest->getMultiParam('stext');
        }
        $this->pagination->num_records = $report_model->numAutodialCDRs($type, $option, $stext, $dateinfo);

        //echo $this->pagination->num_records;
        $result = $this->pagination->num_records > 0 ?
            $report_model->getAutodialCDRs($type, $option, $stext, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $trunk_options = is_array($result) ? $setting_model->getTrunkOptions() : array();
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                //$data->start_time = $data->start_time > 0 ? date("Y-m-d H:i:s", $data->start_time) : '0000-00-00 00:00:00';
                //$data->answer_time = $data->answer_time > 0 ? date("Y-m-d H:i:s", $data->answer_time) : '0000-00-00 00:00:00';
                //$data->stop_time = $data->stop_time > 0 ? date("Y-m-d H:i:s", $data->stop_time) : '0000-00-00 00:00:00';
                $data->duration = DateHelper::get_formatted_time($data->duration);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);

                if (empty($data->trunkid)) $trunk_name = '-';
                else $trunk_name = isset($trunk_options[$data->trunkid]) ?  $trunk_options[$data->trunkid] : $data->trunkid;

                $data->trunkid = $trunk_name;

                if ($data->disc_party == '1')
                    $data->disc_party = 'A-Party';
                else  if ($data->disc_party == '2')
                    $data->disc_party = 'B-Party';
                else  $data->disc_party = $data->disc_party;

                $status = $data->status;
                if ($data->status == "A") {
                    $status = 'Answered';
                } elseif ($data->status == "F") {
                    $status = 'Failed';
                } elseif ($data->status == "V") {
                    $status = 'VM';
                } elseif ($data->status == "S") {
                    $status = 'Silence';

                }
                if (empty($status)) {
                    $status = $data->disposition;
                }
                $data->status = $status;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionLineCdr()
    {
        include('model/MReport.php');
        include('model/MSetting.php');
        include('lib/DateHelper.php');

        $report_model = new MReport();
        $setting_model = new MSetting();

        $dateinfo = DateHelper::get_input_time_details();
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }

        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $option = "";
        $stext = "";

        if ($this->gridRequest->isMultisearch){
            $option = $this->gridRequest->getMultiParam('option');
            $stext = $this->gridRequest->getMultiParam('stext');
        }
        $this->pagination->num_records = $report_model->numCDRs($type, $option, $stext, $dateinfo);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getCDRs($type, $option, $stext, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $trunk_options = is_array($result) ? $setting_model->getTrunkOptions() : array();
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->start_time = $data->start_time > 0 ? date("Y-m-d H:i:s", $data->start_time) : '0000-00-00 00:00:00';
                $data->answer_time = $data->answer_time > 0 ? date("Y-m-d H:i:s", $data->answer_time) : '0000-00-00 00:00:00';
                $data->stop_time = $data->stop_time > 0 ? date("Y-m-d H:i:s", $data->stop_time) : '0000-00-00 00:00:00';
                $data->duration = DateHelper::get_formatted_time($data->duration);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);

                if (empty($data->trunkid)) $trunk_name = '-';
                else $trunk_name = isset($trunk_options[$data->trunkid]) ?  $trunk_options[$data->trunkid] : $data->trunkid;

                $data->trunkid = $trunk_name;

                if ($data->disc_party == '1')
                    $data->disc_party = 'A-Party';
                else  if ($data->disc_party == '2')
                    $data->disc_party = 'B-Party';
                else  $data->disc_party = $data->disc_party;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportAudit()
    {
        include('model/MReport.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $agent_model = new MAgent();


        //$dateinfo = DateHelper::get_input_time_details();
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }



        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $option = "";
        $stext = "";

        if ($this->gridRequest->isMultisearch){
            $user = $this->gridRequest->getMultiParam('agent_id');
        }

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAuditRecords($user, $dateinfo);
        }


        $result = $this->pagination->num_records > 0 ?
            $report_model->getAuditRecords($user, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->tstamp = date("Y-m-d H:i:s", $data->tstamp);
                $agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                $data->agent_id = $agent_name;
                if ($data->type == 'A') $data->type = 'Add';
                else if ($data->type == 'U') $data->type = 'Update';
                else if ($data->type == 'D') $data->type = 'Delete';
                else if ($data->type == 'V') $data->type = 'Visit';
                else if ($data->type == 'L') $data->type = 'Download';
                else if ($data->type == 'I') $data->type = 'Login';
                else if ($data->type == 'M') $data->type = 'Misslogin';
                $data->log_text = str_replace(";", "; ", $data->log_text);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionReportAgentInboundLog()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReport();

        $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        $edate = "";
        //$dateTimeArray = array();
        // get_cc_time_details

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('sdate');
            $edate = $this->gridRequest->getMultiParam('edate');
        }

        if ($option == 'daily') {
            $dateinfo = DateHelper::get_input_time_details(false, $sdate, $edate, '00:00', '23:59');
        } else {
            $month = strlen($sdate) == 7 ? substr($sdate, 2, 5) : date('y-m');
            //$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date('y-m');
            $m = substr($month, 3, 2);
            $y = '20' . substr($month, 0, 2);
            if (!checkdate($m, '01', $y)) {
                $month = date('y-m');
            }
            $dateinfo = DateHelper::get_input_time_details(false, $y.'-'.$m.'-'.'01', $y.'-'.$m.'-'.'31', '00:00', '23:59');
        }

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            //$this->pagination->num_records = $report_model->numAuditRecords($user, $dateinfo);
            $this->pagination->num_records = $report_model->numAgentLogInbound($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ? $report_model->getAgentInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->agent_id = "<a href='".$this->url("task=report&act=agentmonthlysession&option=$option&agentid={$data->agent_id}&sdate={$dateinfo->sdate}&edate={$dateinfo->edate}")."'>".$data->agent_id."</a>";
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->whour = DateHelper::get_formatted_time($data->agentinfo->staffed_time);
                $data->bcount = $data->agentinfo->pause_count;
                $data->btime = DateHelper::get_formatted_time($data->agentinfo->pause_time);

                $data->missed_count = $data->agentinfo->missed_call_count;
                $data->missed_time = DateHelper::get_formatted_time($data->agentinfo->missed_call_time);

                $data->acw_count = $data->agentinfo->acw_count;
                $data->acw_time = DateHelper::get_formatted_time($data->agentinfo->acw_time);

            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionDidInboundLog()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReport();

        $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        //$dateTimeArray = array();
        // get_cc_time_details

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('sdate');
        }

        if ($option == 'daily') {
            $dateinfo = DateHelper::get_input_time_details(false, $sdate, $sdate, '00:00', '23:59');
        } else {
            $month = strlen($sdate) == 7 ? substr($sdate, 2, 5) : date('y-m');
            //$month = isset($_REQUEST['month']) ? trim($_REQUEST['month']) : date('y-m');
            $m = substr($month, 3, 2);
            $y = '20' . substr($month, 0, 2);
            if (!checkdate($m, '01', $y)) {
                $month = date('y-m');
            }
            $dateinfo = DateHelper::get_input_time_details(false, $y.'-'.$m.'-'.'01', $y.'-'.$m.'-'.'31', '00:00', '23:59');
        }

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numDIDLogInbound($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ?$report_model->getDIDInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                /*
                $data->agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->agent_id = "<a href='".$this->url("task=report&act=agentmonthlysession&option=$option&agentid={$data->agent_id}&sdate={$dateinfo->sdate}")."'>".$data->agent_id."</a>";
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->whour = DateHelper::get_formatted_time($data->agentinfo->staffed_time);
                $data->bcount = $data->agentinfo->pause_count;
                $data->btime = DateHelper::get_formatted_time($data->agentinfo->pause_time);

                $data->missed_count = $data->agentinfo->missed_call_count;
                $data->missed_time = DateHelper::get_formatted_time($data->agentinfo->missed_call_time);

                $data->acw_count = $data->agentinfo->acw_count;
                $data->acw_time = DateHelper::get_formatted_time($data->agentinfo->acw_time);
                */

                $data->perc_ans = sprintf("%02d", $data->num_ans/$data->num_calls*100);
                $data->call_duration = gmdate("H:i:s", $data->call_duration);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionDidInboundLog2()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReport();

        $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        //$dateTimeArray = array();
        // get_cc_time_details

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('start_time');
        }

        //print_r($sdate);
        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        //print_r($dateinfo);

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numDIDLogInbound($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ?$report_model->getDIDInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                /*
                $data->agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->agent_id = "<a href='".$this->url("task=report&act=agentmonthlysession&option=$option&agentid={$data->agent_id}&sdate={$dateinfo->sdate}")."'>".$data->agent_id."</a>";
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->whour = DateHelper::get_formatted_time($data->agentinfo->staffed_time);
                $data->bcount = $data->agentinfo->pause_count;
                $data->btime = DateHelper::get_formatted_time($data->agentinfo->pause_time);

                $data->missed_count = $data->agentinfo->missed_call_count;
                $data->missed_time = DateHelper::get_formatted_time($data->agentinfo->missed_call_time);

                $data->acw_count = $data->agentinfo->acw_count;
                $data->acw_time = DateHelper::get_formatted_time($data->agentinfo->acw_time);
                */

                $data->perc_ans = sprintf("%02d", $data->num_ans/$data->num_calls*100);
                $data->call_duration = gmdate("H:i:s", $data->call_duration);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionCampaignRecordStatus()
    {
        include('model/MReport.php');
        include('model/MCampaign.php');

        $campaign_model = new MCampaign();
        $report_model = new MReport();

        //$option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
        $campaign_options = $campaign_model->getCampaignSelectOptions();

        $this->pagination->num_records = $report_model->numDialingCampaigns();

        $result = $this->pagination->num_records > 0 ?
            $report_model->getCampaignRecordStatus($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {

                $data->campaign_title = isset($campaign_options[$data->campaign_id]) ? $campaign_options[$data->campaign_id] : $data->campaign_id;
                $data->num_dialed = $data->num_records - $data->num_progress - $data->num_available;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentSessionSummary()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReport();

        $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : 'monthly';
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('start_time');
        }

        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        //print_r($dateinfo);

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentSessionSummary($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ? $report_model->getAgentSessionSummary($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $agent_model = new MAgent();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

            foreach ( $result as &$row ) {

                $row->nick = isset($agent_options[$row->agent_id]) ? $agent_options[$row->agent_id] : $row->agent_id;
                $row->calls_offered = $row->calls_in_ans+$row->bounce_count;
                $row->ans_percent = $row->calls_in_ans+$row->bounce_count > 0 ? sprintf("%02d", $row->calls_in_ans/($row->calls_in_ans+$row->bounce_count) * 100) : '-';
                $row->avg_talk_time = $row->calls_in_ans > 0 ? round($row->calls_in_time/$row->calls_in_ans) : 0;
                $row->avg_talk_time = gmdate("H:i:s", $row->avg_talk_time);

                $row->avg_talk_time_o = $row->calls_out_reached > 0 ? round($row->calls_out_time/$row->calls_out_reached) : 0;
                $row->avg_talk_time_o = gmdate("H:i:s", $row->avg_talk_time_o);

                $row->avg_hold_time = $row->hold_count > 0 ? round($row->hold_time/$row->hold_count) : 0;
                $row->avg_hold_time = gmdate("H:i:s", $row->avg_hold_time);

                $total_calls = $row->calls_in_ans+$row->calls_out_reached;

                $row->avg_acw_time = $row->acw_count > 0 ? round($row->acw_time/$row->acw_count) : 0;
                $row->avg_acw_time = gmdate("H:i:s", $row->avg_acw_time);

                $row->total_handling_time = $row->calls_in_time+$row->calls_out_time+$row->acw_time+$row->ring_in_time;
                $row->avg_handling_time = $total_calls > 0 ? round(($row->total_handling_time)/$total_calls) : 0;
                $row->avg_handling_time = gmdate("H:i:s", $row->avg_handling_time);

                $row->total_handling_time = gmdate("H:i:s", $row->total_handling_time);
                //$row->msg = $row->staff_time . ' ' . $row->calls_in_time . ' ' . $row->calls_out_time;

                $total_aux_time = $row->aux_12_time+$row->aux_13_time+$row->aux_14_time+$row->aux_15_time+$row->aux_16_time+$row->aux_17_time+$row->aux_18_time+$row->aux_19_time+$row->aux_20_time;
                $total_active_time = $row->ring_in_time+$row->bounce_time+$row->acw_time+$total_aux_time+$row->calls_in_time+$row->calls_out_time;
                $row->avail_time = gmdate("H:i:s", $row->staff_time-$total_active_time);
                $row->staff_time = gmdate("H:i:s", $row->staff_time);
                $row->bounce_time = gmdate("H:i:s", $row->bounce_time);
                $row->acw_time = gmdate("H:i:s", $row->acw_time);
                $row->aux_time = gmdate("H:i:s", $total_aux_time);


                for ($i=11; $i<=20; $i++) {
                    $row->{'aux_'.$i.'_time'} = gmdate("H:i:s", $row->{'aux_'.$i.'_time'});
                }

            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }



    function actionReportAgentMonthlyLog()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');

        $report_model = new MReport();
        $dateinfo = DateHelper::get_input_time_details();
        $agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
        $option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : '';
        if (empty($dateinfo->edate)) {
            if ($option == 'daily') {
                $dateinfo->edate = $dateinfo->sdate;
            } else {
                $dateinfo->edate = substr($dateinfo->sdate, 0, 7) . '-31';
            }
        }
        $result = $report_model->getAgentSessionDates($agentid, $dateinfo->sdate . ' 00:00:00', $dateinfo->edate . ' 23:59:59');
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $_dateinfo = new stdClass();

            $_dateinfo->etime = '';
            $_dateinfo->stime = '';
            $_dateinfo->edate = '';
            foreach ( $result as &$data ) {
                $_dateinfo->sdate = $data->sdate;
                $data->agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $_dateinfo);
                $data->inbound = $report_model->getAgentInboundLogByAgent($data->agent_id, $data->sdate);
                $data->calls_taken = $data->inbound->answered_calls;
                $data->t_time = DateHelper::get_formatted_time($data->inbound->talk_time);
                $data->w_hour = DateHelper::get_formatted_time($data->agentinfo->staffed_time);
                $data->b_count = $data->agentinfo->pause_count;
                $data->b_time = DateHelper::get_formatted_time($data->agentinfo->pause_time);
                $data->alarm = $data->inbound->alarm;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportsessionlog()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReport();

        //$dateinfo = DateHelper::get_input_time_details();

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('sdate');
        }

        $dateinfo = DateHelper::get_input_time_details(false, $sdate, $sdate, '00:00', '23:59');
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $errMsg = UserAuth::getRepErrMsg();
            $errType = 1;
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentsLoggedIn($dateinfo);
        }
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentsLoggedIn($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->agentinfo = $agentinfo->session_login;
                $data->logout = $agentinfo->session_logout;
                $data->s_count = $agentinfo->session_count;
                $data->staffed_time = DateHelper::get_formatted_time($agentinfo->staffed_time);
                $data->pause_count = $agentinfo->pause_count;
                $data->pause_time = DateHelper::get_formatted_time($agentinfo->pause_time);

                $data->missed_count = $agentinfo->missed_call_count;
                $data->missed_time = DateHelper::get_formatted_time($agentinfo->missed_call_time);

                $data->acw_count = $agentinfo->acw_count;
                $data->acw_time = DateHelper::get_formatted_time($agentinfo->acw_time);

                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->agent_id = "<a href='".$this->url("task=report&act=agentsessiondetails&agentid={$data->agent_id}&sdate={$dateinfo->sdate}")."'>".$data->agent_id."</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgent()
    {
        include('model/MReport.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $agent_model = new MAgent();

        //$dateinfo = DateHelper::get_input_time_details();
        $agentid = "";
        $callid = "";
        $callerid = "";
        $answered = "";
        $callto = "";
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";

        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }

        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $skill_options = null;
        if($type=='omanual') {
            include('model/MSkill.php');
            $skill_model = new MSkill();
            $skill_options = $skill_model->getAllSkillOptions('', 'array');
        }

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $pageTitle = "Agent CDR";//hard set, need to change dynamically

        if ($this->gridRequest->isMultisearch){
            $agentid = $this->gridRequest->getMultiParam('agent_id');
            $callid = $this->gridRequest->getMultiParam('callid');
            $callerid = $this->gridRequest->getMultiParam('cli');
            $answered = $this->gridRequest->getMultiParam('is_answer');
            $callto = $this->gridRequest->getMultiParam('callto');
        }
        if ($answered == '*') $answered = '';

        $this->pagination->num_records = $report_model->numAgentCDRs($type, $agentid, $callid, $dateinfo, $callerid, $answered, $callto);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentCDRs($type, $agentid, $callid, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $callerid, $answered, $callto) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $backup_yyyy_mm_dd = "";
        if ($this->getTemplate()->backup_transfer_aft_month > 0) {
            $backup_yyyy_mm_dd = date("Y_m_d", strtotime("-".$this->getTemplate()->backup_transfer_aft_month." months"));
        }
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->service_quality = "<a class='lightboxWIF' href='".$this->url("task=cdr&act=rate-call-quality&callid={$data->callid}&skill_id={$data->skill_id}&agent_id={$data->agent_id}&call_time={$data->start_time}")."'> <i class='fa fa-edit'></i> Service Quality </a>";
                $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                if ($type == 'omanual'){
                    $data->skill_id = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                }
                $data->is_answer = $data->is_answer == 'Y' ? 'Yes' : 'No';
                if($type == 'inbound'){
                    $data->ring_time = DateHelper::get_formatted_time($data->ring_time);
                } elseif($type == 'omanual'){
                    $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                    if ($data->start_time == '1970-01-01 00:00:00') $data->start_time = $data->stop_time;
                }
                $data->duration = DateHelper::get_formatted_time($data->duration);
                $data->hold_time = DateHelper::get_formatted_time($data->hold_time);
                $data->acw_time = DateHelper::get_formatted_time($data->acw_time);


                if (!empty($data->callid)) {
                    $file_timestamp = substr($data->callid, 0, 10);
                    $yyyy = date("Y", $file_timestamp);
                    $yy = substr($yyyy, 2, 2);
                    $yyyy_mm_dd = date("Y_m_d", $file_timestamp);
                    $mm = substr($yyyy_mm_dd, 5, 2);
                    $dd = substr($yyyy_mm_dd, 8, 2);
                    $data->audio = '';
                    $data->sip = '';

                    if ($yyyy_mm_dd > $backup_yyyy_mm_dd) {
                        $vl_path = $this->getTemplate()->voice_logger_path;
                        $sip_path = $this->getTemplate()->sip_logger_path;
                    } else {
                        $vl_path = $this->getTemplate()->backup_hdd_path;
                        $sip_path = $this->getTemplate()->backup_hdd_path;
                    }

                    $sound_file_gsm = $vl_path . "vlog/$yyyy/$yyyy_mm_dd/" . $data->callid . ".wav";
                    if (!file_exists($sound_file_gsm)) {
                        $sound_file_gsm = $vl_path . "vlog/$yyyy/$yyyy_mm_dd/" . $data->callid . ".mp3";
                        if (!file_exists($sound_file_gsm)) {
                            $sound_file_gsm = '';
                        }
                    }

                    if (!empty($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
                        $playUrl = $this->url("task=cdr&act=listen-audio-file&cid=".$data->callid."&cdr_page=".$pageTitle);
                        $downloadUrl = $this->url("task=cdr&act=playbrowser&cid=".$data->callid."&cdr_page=".$pageTitle);
                        $data->audio .= " <a href='javascript:void(0)' class='playAudioFile' onClick='playAudio(\"$playUrl\", \"$data->callid\", this)' title='Play in browser' border='0'><i class=\"fa fa-play-circle\"></i></a> &nbsp; ";
                        if ($this->getTemplate()->is_voice_file_downloadable) {
                            $data->audio .= " <a title='Download audio file' href=\"$downloadUrl\"><i class=\"fa fa-download\"></i></a> &nbsp;";
                        }
                    } else {
                        $data->audio = "NONE";
                    }
                    $sip_file = $sip_path . "siplog/$yy/$mm/$dd/" . $data->callid . ".sip";
                    if (file_exists($sip_file)) {
                        $data->sip = " <a title='SIP Log' data-h='400px' data-w='600px' class='lightboxWIFR' href=\"index.php?task=cdr&act=sip-log&cid=$data->callid\"><i class=\"fa fa-file\"></i></a>";
                    } else {
                        $data->sip = "";
                    }
                } else {
                    $data->audio = "NONE";
                    $data->sip = "";
                }

                if (empty($data->audio)) $data->audio = 'NONE';
                if (empty($data->sip)) $data->sip = '-';

            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionLogin()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();

        $dateinfo = DateHelper::get_input_time_details();
        $user = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';

        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('sdate');
        }

        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
        }else {
            $result  = $report_model->getLoginRecords($user, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page);
        }

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                /*
                if(!empty($data->nick)){
                    $nick = "[" .$data->nick. "]";
                    $data->agent_id = $data->agent_id . "  ". $nick;
                } else{
                    $data->agent_id = $data->agent_id;
                }
                */
                if(empty($data->nick)){
                    $data->nick = '-';
                }

                if ($data->usertype == 'A') $data->usertype = 'Agent';
                else if ($data->usertype == 'R') $data->usertype = 'Root';
                else if ($data->usertype == 'S') $data->usertype = 'Superviser';
                else if ($data->usertype == 'D') $data->usertype ='Dashboard Visitor';
                else if ($data->usertype == 'U') $data->usertype = 'Normal User';
                $data->active = $data->active == 'Y' ? "Active" : "Inactive";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAbdnskilldelayspectrum()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $report_model = new MReport();
        $skill_model = new MSkill();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('cdate');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $logs = null;
        }else {
            $logs = $report_model->getServiceAbdnDelaySpectrum('', $dateinfo);
        }

        $responce = $this->getTableResponse();
        $responce->records = is_array($logs) ? count($logs) : 0;
        $skill_options = $skill_model->getSkillOptions();
        $result=&$logs;

        if(count($result) > 0){
            $total_calls_offered = 0;
            $total_a_l5 = 0;
            $total_a_5to10 = 0;
            $total_a_10to20 = 0;
            $total_a_20to30 = 0;
            $total_a_30to40 = 0;
            $total_a_40to50 = 0;
            $total_a_50to60 = 0;
            $total_a_60to70 = 0;
            $total_a_70to80 = 0;
            $total_a_80to90 = 0;
            $total_a_90to120 = 0;
            $total_a_g120 = 0;
            $total_total = 0;
            foreach ( $result as &$data ) {
                $total = $data->a_l5 + $data->a_5to10 + $data->a_10to20 + $data->a_20to30 + $data->a_30to40
                    + $data->a_40to50 + $data->a_50to60 + $data->a_60to70 + $data->a_70to80 + $data->a_80to90 + $data->a_90to120 + $data->a_g120;

                $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;

                $total_calls_offered += $data->calls_offered;
                $total_a_l5 += $data->a_l5;
                $total_a_5to10 += $data->a_5to10;
                $total_a_10to20 += $data->a_10to20;
                $total_a_20to30 += $data->a_20to30;
                $total_a_30to40 += $data->a_30to40;
                $total_a_40to50 += $data->a_40to50;
                $total_a_50to60 += $data->a_50to60;
                $total_a_60to70 += $data->a_60to70;
                $total_a_70to80 += $data->a_70to80;
                $total_a_80to90 += $data->a_80to90;
                $total_a_90to120 += $data->a_90to120;
                $total_a_g120 += $data->a_g120;
                $total_total += $total;

                $data->total = $total;
            }

            $data2 = new stdClass();
            $data2->cdate = "<b>Total</b>";
            $data2->skill_name = "";
            $data2->calls_offered = $total_calls_offered;
            $data2->a_l5 = $total_a_l5;
            $data2->a_5to10 = $total_a_5to10;
            $data2->a_10to20 = $total_a_10to20;
            $data2->a_20to30 = $total_a_20to30;
            $data2->a_30to40 = $total_a_30to40;
            $data2->a_40to50 = $total_a_40to50;
            $data2->a_50to60 = $total_a_50to60;
            $data2->a_60to70 = $total_a_60to70;
            $data2->a_70to80 = $total_a_70to80;
            $data2->a_80to90 = $total_a_80to90;
            $data2->a_90to120 = $total_a_90to120;
            $data2->a_g120 = $total_a_g120;
            $data2->total = $total_total;

            $result[$responce->records] = $data2;
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAnsskilldelayspectrum()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $report_model = new MReport();
        $skill_model = new MSkill();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('cdate');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $logs = null;
        }else {
            $logs = $report_model->getServiceAnsDelaySpectrum('', $dateinfo);
        }

        $responce = $this->getTableResponse();
        $responce->records = is_array($logs) ? count($logs) : 0;
        $skill_options = $skill_model->getSkillOptions();
        $result=&$logs;

        if(count($result) > 0){
            $total_calls_offered = 0;
            $total_a_l5 = 0;
            $total_a_5to10 = 0;
            $total_a_10to20 = 0;
            $total_a_20to30 = 0;
            $total_a_30to40 = 0;
            $total_a_40to50 = 0;
            $total_a_50to60 = 0;
            $total_a_60to70 = 0;
            $total_a_70to80 = 0;
            $total_a_80to90 = 0;
            $total_a_90to120 = 0;
            $total_a_g120 = 0;
            $total_total = 0;
            foreach ( $result as &$data ) {
                $total = $data->a_l5 + $data->a_5to10 + $data->a_10to20 + $data->a_20to30 + $data->a_30to40
                    + $data->a_40to50 + $data->a_50to60 + $data->a_60to70 + $data->a_70to80 + $data->a_80to90 + $data->a_90to120 + $data->a_g120;

                $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;

                $total_calls_offered += $data->calls_offered;
                $total_a_l5 += $data->a_l5;
                $total_a_5to10 += $data->a_5to10;
                $total_a_10to20 += $data->a_10to20;
                $total_a_20to30 += $data->a_20to30;
                $total_a_30to40 += $data->a_30to40;
                $total_a_40to50 += $data->a_40to50;
                $total_a_50to60 += $data->a_50to60;
                $total_a_60to70 += $data->a_60to70;
                $total_a_70to80 += $data->a_70to80;
                $total_a_80to90 += $data->a_80to90;
                $total_a_90to120 += $data->a_90to120;
                $total_a_g120 += $data->a_g120;
                $total_total += $total;

                $data->total = $total;
            }

            $data2 = new stdClass();
            $data2->cdate = "<b>Total</b>";
            $data2->skill_name = "";
            $data2->calls_offered = $total_calls_offered;
            $data2->a_l5 = $total_a_l5;
            $data2->a_5to10 = $total_a_5to10;
            $data2->a_10to20 = $total_a_10to20;
            $data2->a_20to30 = $total_a_20to30;
            $data2->a_30to40 = $total_a_30to40;
            $data2->a_40to50 = $total_a_40to50;
            $data2->a_50to60 = $total_a_50to60;
            $data2->a_60to70 = $total_a_60to70;
            $data2->a_70to80 = $total_a_70to80;
            $data2->a_80to90 = $total_a_80to90;
            $data2->a_90to120 = $total_a_90to120;
            $data2->a_g120 = $total_a_g120;
            $data2->total = $total_total;

            $result[$responce->records] = $data2;
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentperfoutboundmanual(){
        include('model/MReport.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $agent_model = new MAgent();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentPerfOutBoundManual($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerfOutBoundManual($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                $data->avg_handling_time = 0;
                $data->success_rate = 0;
                if ($data->reached_calls > 0) {
                    $data->avg_handling_time = ($data->service_time+$data->acw_time)/$data->reached_calls;
                }
                $data->avg_handling_time = DateHelper::get_formatted_time($data->avg_handling_time);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->acw_time = DateHelper::get_formatted_time($data->acw_time);

                if ($data->attempted_calls > 0) {
                    $data->success_rate = $data->reached_calls/$data->attempted_calls*100;
                }
                $data->success_rate = sprintf("%02d", $data->success_rate);

            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentcdrsummary()
    {
        include('model/MReport.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $agent_model = new MAgent();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentPerfInbound($dateinfo);
        }

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerfInbound($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->avg_handling_time = 0;
                $data->service_time_queue = "&nbsp;-";
                $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;

                if ($data->answered_calls > 0) {
                    $data->avg_handling_time = ($data->aring_time+$data->talk_time+$data->acw_time)/$data->answered_calls;
                }

                $data->staffed_time = DateHelper::get_formatted_time($agentinfo->staffed_time);
                $data->longest_call = DateHelper::get_formatted_time($data->longest_call);
                $data->shortest_call = DateHelper::get_formatted_time($data->shortest_call);

                $data->avg_handling_time = DateHelper::get_formatted_time($data->avg_handling_time);
                $data->agent_name = '&nbsp;' . $data->agent_name;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentperfinbound()
    {
        include('model/MReport.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $agent_model = new MAgent();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);

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
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentPerfInbound($dateinfo);
        }
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerfInbound($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->acw_time = $agentinfo->acw_time;
                $data->missed_call_time = $agentinfo->missed_call_time;
                $data->avg_handling_time = 0;
                $data->transf_in = "&nbsp;-";
                $data->transf_out = "&nbsp;-";
                $data->service_time_queue = "&nbsp;-";
                $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                $data->available_time = 0;
                if ($agentinfo->staffed_time > 0) {
                    $data->available_time = $agentinfo->staffed_time-$data->aring_time-$data->talk_time-$data->acw_time-$agentinfo->pause_time-$agentinfo->missed_call_time;
                }
                $data->utilization = 0;
                if ($agentinfo->staffed_time > 0) {
                    $data->utilization = $data->talk_time/$agentinfo->staffed_time*100;
                }

                $data->occupancy = 0;
                if ($agentinfo->staffed_time > 0) {
                    $data->occupancy = ($data->talk_time+$data->acw_time+$data->aring_time)*100/$agentinfo->staffed_time;
                }

                $average = 0;
                if ($data->answered_calls > 0) {
                    $average = floor($data->aring_time / $data->answered_calls);
                }
                $data->speed_of_ans = DateHelper::get_formatted_time($average);
                $average = 0;
                if ($data->answered_calls > 0) {
                    $seconds = $data->aring_time + $data->talk_time + $data->acw_time + $data->hold_time;
                    $average = floor($seconds / $data->answered_calls);
                }
                $data->avg_handling_time = DateHelper::get_formatted_time($average);
                $data->staffed_time = DateHelper::get_formatted_time($agentinfo->staffed_time);
                $data->aring_time = DateHelper::get_formatted_time($data->aring_time);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->acw_time = DateHelper::get_formatted_time($data->acw_time);
                $data->missed_call_time = DateHelper::get_formatted_time($data->missed_call_time);
                $data->pause_time = DateHelper::get_formatted_time($agentinfo->pause_time);
                $data->available_time = DateHelper::get_formatted_time($data->available_time);
                $data->utilization = sprintf("%02d", $data->utilization);
                $data->occupancy = sprintf("%02d", $data->occupancy);

                $data->hold_time = DateHelper::get_formatted_time($data->hold_time);

                for ($i=12; $i<21; $i++) {
                    $data->{'p'.$i} = DateHelper::get_formatted_time($agentinfo->{'p'.$i});
                }

                $data->longest_ring = DateHelper::get_formatted_time($data->longest_ring);
                $data->longest_call = DateHelper::get_formatted_time($data->longest_call);
                $data->shortest_call = DateHelper::get_formatted_time($data->shortest_call);
                $data->agent_name = '&nbsp;' . $data->agent_name;
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentfulldaylog()
    {
        include('model/MReport.php');
        include('model/MSetting.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $setting_model = new MSetting();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentLogInbound($dateinfo);
        }
        $aux_messages = $setting_model->getBusyMessages('Y');

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $agent_model = new MAgent();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            foreach ( $result as &$data ) {
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $total_calls_offered = $data->answered_calls+$data->alarm;
                $data->total_calls_offered = $total_calls_offered;

                $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;

                $ans_percent = 0;
                if ($total_calls_offered > 0) {
                    $ans_percent = $data->answered_calls*100/$total_calls_offered;
                }
                $data->ans_percent = sprintf("%02d", $ans_percent);

                $data->acw_time = $agentinfo->acw_time;
                $data->missed_call_time = $agentinfo->missed_call_time;

                $avg_handling_time = 0;
                if ($data->answered_calls > 0) {
                    //$times = array($data->aring_time, $data->talk_time, $data->acw_time);
                    //$seconds = $this->timeToSeconds($times);
                    $seconds = $data->aring_time + $data->hold_time + $data->talk_time + $data->acw_time;
                    $avg_handling_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_talk_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->talk_time);
                    $seconds = $data->talk_time;
                    $avg_talk_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_talk_time = DateHelper::get_formatted_time($avg_talk_time);

                $avg_hold_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->hold_time);
                    $seconds = $data->hold_time;
                    $avg_hold_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_hold_time = DateHelper::get_formatted_time($avg_hold_time);

                $avg_acw_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->acw_time);
                    $avg_acw_time = floor($data->acw_time / $data->answered_calls);
                }
                $data->avg_acw_time = DateHelper::get_formatted_time($avg_acw_time);

                $handling_time = $data->aring_time+$data->talk_time+$data->acw_time;
                $avail_time = $agentinfo->staffed_time-$data->aring_time-$data->talk_time-$data->acw_time-$agentinfo->pause_time-$data->missed_call_time;

                $data->handling_time = DateHelper::get_formatted_time($handling_time);
                $data->pause_time = DateHelper::get_formatted_time($agentinfo->pause_time);
                $data->avail_time = DateHelper::get_formatted_time($avail_time);
                $data->acw_time = DateHelper::get_formatted_time($agentinfo->acw_time);
                $data->missed_call_time = DateHelper::get_formatted_time($agentinfo->missed_call_time);
                $data->staffed_time = DateHelper::get_formatted_time($agentinfo->staffed_time);
                $data->missed_call_time = DateHelper::get_formatted_time($data->missed_call_time);

                if (is_array($aux_messages)) {
                    foreach ($aux_messages as $aux) {
                        $auxindex = 'p'.$aux->aux_code;
                        $auxIndexData = 0;
                        if (isset($agentinfo->$auxindex)) {
                            $auxIndexData = $agentinfo->$auxindex;
                        }
                        $data->$auxindex = DateHelper::get_formatted_time($auxIndexData);
                    }
                }
                $aux_others = isset($agentinfo->p) ? $agentinfo->p : 0;
                $data->aux_other = DateHelper::get_formatted_time($aux_others);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    //SEBL Report
    function actionAgentfulldaylog_SEBL()
    {
        include('model/MReport.php');
        include('model/MSetting.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $setting_model = new MSetting();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentLogInbound($dateinfo);
        }
        $aux_messages = $setting_model->getBusyMessages('Y');

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $agent_model = new MAgent();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            foreach ( $result as &$data ) {
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $total_calls_offered = $data->answered_calls+$data->alarm;
                $data->total_calls_offered = $total_calls_offered;

                $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;

                $ans_percent = 0;
                if ($total_calls_offered > 0) {
                    $ans_percent = $data->answered_calls*100/$total_calls_offered;
                }
                $data->ans_percent = sprintf("%02d", $ans_percent);

                $data->acw_time = $agentinfo->acw_time;
                $data->missed_call_time = $agentinfo->missed_call_time;

                $avg_handling_time = 0;
                if ($data->answered_calls > 0) {
                    //$times = array($data->aring_time, $data->talk_time, $data->acw_time);
                    //$seconds = $this->timeToSeconds($times);
                    $seconds = $data->aring_time + $data->hold_time + $data->talk_time + $data->acw_time;
                    $avg_handling_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_talk_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->talk_time);
                    $seconds = $data->talk_time;
                    $avg_talk_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_talk_time = DateHelper::get_formatted_time($avg_talk_time);

                $avg_hold_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->hold_time);
                    $seconds = $data->hold_time;
                    $avg_hold_time = floor($seconds / $data->answered_calls);
                }
                $data->avg_hold_time = DateHelper::get_formatted_time($avg_hold_time);

                $avg_acw_time = 0;
                if ($data->answered_calls > 0) {
                    //$seconds = $this->timeToSeconds($data->acw_time);
                    $avg_acw_time = floor($data->acw_time / $data->answered_calls);
                }
                $data->avg_acw_time = DateHelper::get_formatted_time($avg_acw_time);

                $handling_time = $data->aring_time+$data->talk_time+$data->acw_time;
                $avail_time = $agentinfo->staffed_time-$data->aring_time-$data->talk_time-$data->acw_time-$agentinfo->pause_time-$data->missed_call_time;

                $data->handling_time = DateHelper::get_formatted_time($handling_time);
                $data->pause_time = DateHelper::get_formatted_time($agentinfo->pause_time);
                $data->avail_time = DateHelper::get_formatted_time($avail_time);
                $data->acw_time = DateHelper::get_formatted_time($agentinfo->acw_time);
                $data->missed_call_time = DateHelper::get_formatted_time($agentinfo->missed_call_time);
                $data->staffed_time = DateHelper::get_formatted_time($agentinfo->staffed_time);
                $data->missed_call_time = DateHelper::get_formatted_time($data->missed_call_time);

                $data->working_day = $agentinfo->working_day;
                $data->avg_staf_time = $agentinfo->working_day > 0 ? DateHelper::get_formatted_time($agentinfo->staffed_time/$agentinfo->working_day) : '00:00:00';
                $avg_ring_time = 0;
                if ($data->answered_calls > 0) {
                    $avg_ring_time = $data->aring_time/$data->answered_calls;
                }
                $data->avg_ring_time = DateHelper::get_formatted_time($avg_ring_time);
                $data->total_aux = $agentinfo->pause_count;
                $data->avg_aux_time = $agentinfo->working_day > 0 ? DateHelper::get_formatted_time($agentinfo->pause_time/$agentinfo->working_day) : '00:00:00' ;
                $data->avg_aux_count = $agentinfo->working_day > 0 ? number_format($agentinfo->pause_count/$agentinfo->working_day, 2) : '0' ;

                if (is_array($aux_messages)) {
                    foreach ($aux_messages as $aux) {
                        $auxindex = 'p'.$aux->aux_code;
                        $auxIndexData = 0;
                        if (isset($agentinfo->$auxindex)) {
                            $auxIndexData = $agentinfo->$auxindex;
                        }
                        $data->$auxindex = DateHelper::get_formatted_time($auxIndexData);
                    }
                }
                $aux_others = isset($agentinfo->p) ? $agentinfo->p : 0;
                $data->aux_other = DateHelper::get_formatted_time($aux_others);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentSkillReport()
    {
        include('model/MReport.php');
        include('model/MSetting.php');
        include('model/MAgent.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $setting_model = new MSetting();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numAgentLogInbound($dateinfo);
        }
        $aux_messages = $setting_model->getBusyMessages('Y');

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentSkillInboundLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $agent_model = new MAgent();
            $skill_model = new MSkill();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            $skill_options = $skill_model->getAllSkillOptions('', 'array');
            foreach ( $result as &$data ) {

                $total_calls_offered = $data->answered_calls+$data->alarm;
                $data->total_calls_offered = $total_calls_offered;
                $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                $ans_percent = 0;
                if ($total_calls_offered > 0) {
                    $ans_percent = $data->answered_calls*100/$total_calls_offered;
                }
                $data->ans_percent = sprintf("%02d", $ans_percent);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time);
                $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionFulldayservicelevel()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('cdate');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        $this->pagination->num_records = $report_model->numSkillLogPerDay($dateinfo);
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $errMsg = UserAuth::getRepErrMsg();
            $errType = 1;
            $logsData = "";
        }else {
            $logsData = $report_model->getSkillLogPerDay($dateinfo,$this->pagination->getOffset(), $this->pagination->rows_per_page);
        }

        if (is_array($logsData)) {
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');;
            $sl_method_val = $sl_method->value == 'B' ? 'B' : 'A';
        }

        $result=&$logsData;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $totalCount = $report_model->getSkillLogPerDayTotal($dateinfo);
            $totalCount = !empty($totalCount) ? (array) array_shift($totalCount) : [];
            $totalCount['service_level'] = "-";
            $totalCount['cdate'] = "<b>Total = </b>";;

            $sl_calls = $sl_method_val == 'B' ?  $totalCount['num_ans'] + $totalCount['num_abdns_after_th'] : $totalCount['num_calls'];
            $percent_within_sl = $sl_calls > 0 ? ($totalCount['ans_within_sl']/$sl_calls) * 100 : 0;

            $totalCount['level_ratio'] = sprintf("%0.02f", $percent_within_sl);

            foreach ( $result as &$data ) {

                $sl_calls = $sl_method_val == 'B' ? $data->num_ans + $data->num_abdns_after_th : $data->num_calls ;
                $percent_within_sl = $sl_calls > 0 ?  ($data->ans_within_sl/$sl_calls) * 100 : 0;
                $data->level_ratio = sprintf("%0.02f", $percent_within_sl);
            }

            $responce->userdata = $totalCount;
        }

        $responce->rowdata = $result;

        $this->ShowTableResponse();
    }

    function actionSkilllogbyinterval()
    {
        include('model/MReport.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $skill_model = new MSkill();

        $startDate = "";
        $skill = "";
        if ($this->gridRequest->isMultisearch){
            $startDate = $this->gridRequest->getMultiParam('sdate');
            $skill = $this->gridRequest->getMultiParam('skills');
        }
        //$dateinfo = DateHelper::get_input_time_details(false, $startDate);
        if (empty($startDate['to'])) {
            list($_sdate, $_stime) = explode(" ", $startDate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $startDate['from']);
            list($_edate, $_etime) = explode(" ", $startDate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        $stime_hhmm = '00:00';
        $etime_hhmm = '23:59';
        //if ($dateinfo->stime == '00:00') $dateinfo->stime = '';
        //if ($dateinfo->etime == '00:00') $dateinfo->etime = '';

        if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
            $stime_hhmm = $dateinfo->stime;
            $etime_hhmm = $dateinfo->etime;
        } elseif (!empty($dateinfo->stime)) {
            list($shour, $smin) = explode(":", $dateinfo->stime);
            $stime_hhmm = $dateinfo->stime;
            $etime_hhmm = $shour . ":59";
        } elseif (!empty($dateinfo->etime)) {
            list($shour, $smin) = explode(":", $dateinfo->etime);
            $stime_hhmm = $shour . ":00";
            $etime_hhmm = $dateinfo->etime;
        }

        //print_r($dateinfo);

        if (empty($dateinfo->edate)) {
            $dateinfo->edate = $dateinfo->sdate;
        }

        $start_tstamp = strtotime($dateinfo->sdate." $stime_hhmm:00");
        if ($dateinfo->edate == date('Y-m-d')) {
            $end_tstamp_1 = strtotime($dateinfo->edate." " . date("H:i:s"));
            $end_tstamp_2 = strtotime($dateinfo->edate." $etime_hhmm:59");
            if ($end_tstamp_1 < $end_tstamp_2) {
                //
                $end_tstamp = $end_tstamp_1;
            } else {
                $end_tstamp = $end_tstamp_2;
            }
        } else {
            $end_tstamp = strtotime($dateinfo->edate." $etime_hhmm:59");
        }
        //$end_tstamp = $dateinfo->edate == date('Y-m-d') ? strtotime($dateinfo->edate." " . date("H:i:s")) : strtotime($dateinfo->edate." $etime_hhmm:59");

        $total_records = ceil(($end_tstamp - $start_tstamp)/1800);

        //echo 'Offset: ' . $this->pagination->getOffset() . '; ' . $this->pagination->rows_per_page;
        if ($this->pagination->getOffset() > 0) {
            $start_tstamp = $start_tstamp + 1800*$this->pagination->getOffset();
        }
        if ($this->pagination->rows_per_page > 0) {
            //echo $this->pagination->rows_per_page;
            $end_tstamp_1 = $start_tstamp + 1800*$this->pagination->rows_per_page;
            if ($end_tstamp_1 < $end_tstamp)  $end_tstamp = $end_tstamp_1;
            //
        }

        $errMsg = '';
        $errType = 1;
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        //var_dump($dateinfo);
        //echo "repLastDate=$repLastDate;sdate=".$dateinfo->sdate.";";
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $errMsg = UserAuth::getRepErrMsg();
            $errType = 1;
            $logsData = "";
        }else {
            //
            $dateinfo->stime = date("H:i", $start_tstamp);
            $dateinfo->sdate = date("Y-m-d", $start_tstamp);
            $dateinfo->ststamp = $start_tstamp;
            $dateinfo->etime = date("H:i", $end_tstamp);
            $dateinfo->edate = date("Y-m-d", $end_tstamp);
            $dateinfo->etstamp = $end_tstamp;

            //print_r($dateinfo);
            $logsData = $report_model->getSkillLogByInterval($dateinfo, $skill);
        }

        if (is_array($logsData)) {
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');
            if ($sl_method) {
                $sl_method = $sl_method->value;
            }
        }

        $responce = $this->getTableResponse();
        $responce->records = 0;
        $dataObj = array();

        //if(count($logsData) > 0){
        if (empty($errMsg)) {
            $log_array = array();

            if (is_array($logsData)) {
                foreach ($logsData as $log) {
                    $_key = substr($log->cdate, 5, 2).'_'.substr($log->cdate, 8, 2).'_'.sprintf("%02d", $log->hour)."_".sprintf("%02d", $log->minute);
                    $log_array[$_key] = $log;
                }
            }

            //$responce->sl_method = $sl_method;
            $responce->records = $total_records;
            $responce->total = ceil($total_records/$this->pagination->rows_per_page);
            $inc = 0;
            for ($tstamp = $start_tstamp; $tstamp<=$end_tstamp; $tstamp+=1800){
                $key = date("m_d_H_i", $tstamp);
                $log = isset($log_array[$key]) ? $log_array[$key] : null;

                $dataObj[$inc] = new stdClass();
                $dataObj[$inc]->tstamp = date("H:i", $tstamp);
                $dataObj[$inc]->day = date("Y-m-d", $tstamp);
                if (!empty($log)){
                    $percent_within_sl = 0;
                    $sl_calls = $log->num_calls;
                    if ($sl_method == 'B') $sl_calls = $log->num_ans + $log->num_abdns_after_th;
                    if ($sl_calls > 0) {
                        $percent_within_sl = $log->ans_within_sl/$sl_calls*100;
                    }
                    $dataObj[$inc]->ans_within_sl = $log->ans_within_sl;
                    $dataObj[$inc]->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

                    $percent_abd_calls = 0;
                    if ($log->num_calls > 0) {
                        $percent_abd_calls = $log->num_abdns_after_th/$log->num_calls*100;
                    }
                    $dataObj[$inc]->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

                    $dataObj[$inc]->num_calls = $log->num_calls;

                    $speed_of_ans = 0;
                    if ($log->num_ans > 0) {
                        $speed_of_ans = $log->ring_sec/$log->num_ans;
                    }
                    $dataObj[$inc]->speed_of_ans = DateHelper::get_formatted_time($speed_of_ans);

                    $dataObj[$inc]->num_ans = $log->num_ans;

                    $avg_handling_time = 0;
                    if ($log->num_ans > 0) {
                        $avg_handling_time = ($log->talktime+$log->ring_sec)/$log->num_ans;
                    }
                    $dataObj[$inc]->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                    $dataObj[$inc]->num_abdns_after_th = $log->num_abdns_after_th;

                    $avg_abd_time = 0;
                    if ($log->num_abdns > 0) {
                        $avg_abd_time = $log->abdns_time/$log->num_abdns;
                    }
                    $dataObj[$inc]->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

                    $dataObj[$inc]->num_extn = $log->num_extn;

                    $avg_extn_time = 0;
                    if ($log->num_extn > 0) {
                        $avg_extn_time = $log->extn_time/$log->num_extn;
                    }
                    $dataObj[$inc]->avg_extn_time = DateHelper::get_formatted_time($avg_extn_time);
                }else{
                    $dataObj[$inc]->ans_within_sl = '0';
                    $dataObj[$inc]->percent_within_sl = '0.00';
                    $dataObj[$inc]->percent_abd_calls = '0.00';
                    $dataObj[$inc]->num_calls = '0';
                    $dataObj[$inc]->speed_of_ans = '00:00:00';
                    $dataObj[$inc]->num_ans = '0';
                    $dataObj[$inc]->avg_handling_time = '00:00:00';
                    $dataObj[$inc]->num_abdns_after_th = '0';
                    $dataObj[$inc]->avg_abd_time = '00:00:00';
                    $dataObj[$inc]->num_extn = '0';
                    $dataObj[$inc]->avg_extn_time = '00:00:00';
                }
                $inc++;
            }
        }

        $responce->rowdata = $dataObj;
        $this->ShowTableResponse();
    }

    function actionFulldayskill()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        $report_model = new MReport();
        $dateTimeArray = array();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
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
            $logsData = "";
        } else {
            $logsData = $report_model->getSkillLogPerDay($dateinfo);
        }

        if (is_array($logsData)) {
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');
            $sl_method_val = $sl_method->value == 'B' ? 'B' : 'A';
        }

        $result=&$logsData;
        $responce = $this->getTableResponse();
        $responce->records = is_array($logsData) ? count($logsData) : 0;

        if(count($result) > 0){
            $total_num_ans = 0;
            $total_num_calls = 0;
            $total_ans_within_sl = 0;
            $total_num_abdns_after_th = 0;
            $total_num_abdns_b4_th = 0;
            $total_abandoned = 0;
            $total_ring_sec = 0;
            $total_talktime = 0;
            $total_num_abdns = 0;
            $total_abdns_time = 0;
            $total_num_extn = 0;
            $total_extn_time = 0;

            foreach ( $result as &$data ) {
                $data->num_abdns_b4_th = $data->num_abdns-$data->num_abdns_after_th;
                $total_num_ans += $data->num_ans;
                $total_num_calls += $data->num_calls;
                $total_ans_within_sl += $data->ans_within_sl;
                $total_num_abdns_after_th += $data->num_abdns_after_th;
                $total_abandoned += $data->num_abdns;
                $total_num_abdns_b4_th += $data->num_abdns_b4_th;
                $total_ring_sec += $data->ring_sec;
                $total_talktime += $data->talktime;
                $total_num_abdns += $data->num_abdns;
                $total_abdns_time += $data->abdns_time;
                $total_num_extn += $data->num_extn;
                $total_extn_time += $data->extn_time;

                $percent_within_sl = 0;
                $sl_calls = $data->num_calls;
                if ($sl_method_val == 'B') $sl_calls = $data->num_ans + $data->num_abdns_after_th;
                if ($sl_calls > 0) {
                    $percent_within_sl = $data->ans_within_sl/$sl_calls*100;
                }
                $data->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

                $percent_abd_calls = 0;
                if ($data->num_calls > 0) {
                    $percent_abd_calls = $data->num_abdns_after_th/$data->num_calls*100;
                }
                $data->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

                $speed_of_ans = 0;
                if ($data->num_ans > 0) {
                    $speed_of_ans = $data->ring_sec/$data->num_ans;
                }
                $data->speed_of_ans = DateHelper::get_formatted_time($speed_of_ans);

                $avg_handling_time = 0;
                if ($data->num_ans > 0) {
                    $avg_handling_time = $data->talktime/$data->num_ans;
                }
                $data->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_abd_time = 0;
                if ($data->num_abdns > 0) {
                    $avg_abd_time = $data->abdns_time/$data->num_abdns;
                }
                $data->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

                $avg_extn_time = 0;
                if ($data->num_extn > 0) {
                    $avg_extn_time = $data->extn_time/$data->num_extn;
                }
                $data->avg_extn_time = DateHelper::get_formatted_time($avg_extn_time);

                $data->talktime = DateHelper::get_formatted_time($data->talktime);
            }

            $data2 = new stdClass();
            $data2->cdate = "<b>Total</b>";
            $data2->skill_name = "";

            $data2->talktime = DateHelper::get_formatted_time($total_talktime);
            $data2->num_abdns_b4_th = $total_num_abdns_b4_th;
            $percent_within_sl = 0;
            $sl_calls = $total_num_calls;
            if ($sl_method_val == 'B') $sl_calls = $total_num_ans + $total_num_abdns_after_th;
            if ($sl_calls > 0) {
                $percent_within_sl = $total_ans_within_sl/$sl_calls*100;
            }
            $data2->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

            $percent_abd_calls = 0;
            if ($total_num_calls > 0) {
                $percent_abd_calls = $total_num_abdns_after_th/$total_num_calls*100;
            }
            $data2->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

            $data2->num_calls = $total_num_calls;

            $speed_of_ans = 0;
            if ($total_num_ans > 0) {
                $speed_of_ans = $total_ring_sec/$total_num_ans;
            }
            $data2->speed_of_ans = DateHelper::get_formatted_time($speed_of_ans);

            $data2->num_ans = $total_num_ans;

            $avg_handling_time = 0;
            if ($total_num_ans > 0) {
                $avg_handling_time = $total_talktime/$total_num_ans;
            }
            $data2->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

            $data2->num_abdns_after_th = $total_num_abdns_after_th;

            $avg_abd_time = 0;
            if ($total_num_abdns > 0) {
                $avg_abd_time = $total_abdns_time/$total_num_abdns;
            }
            $data2->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

            $data2->num_extn = $total_num_extn;

            $avg_extn_time = 0;
            if ($total_num_extn > 0) {
                $avg_extn_time = $total_extn_time/$total_num_extn;
            }
            $data2->avg_extn_time = DateHelper::get_formatted_time($avg_extn_time);

            $result[$responce->records] = $data2;
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function timeToSeconds($timeOfDate) {
        $total_seconds = 0;
        if (is_array($timeOfDate)){
            if (count($timeOfDate) <= 0) return 0;

            foreach ($timeOfDate as $time){
                list($hour,$minute,$second) = explode(':', $time);
                $total_seconds += $hour*3600;
                $total_seconds += $minute*60;
                $total_seconds += $second;
            }
        }else {
            if (empty($timeOfDate)) return 0;

            $time_array = explode(':', $timeOfDate);
            $hours = (int)$time_array[0];
            $minutes = (int)$time_array[1];
            $seconds = (int)$time_array[2];

            $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        }
        return $total_seconds;
    }

    function actionCrmreport_init()
    {
        include('model/MCrmIn.php');
        include('model/MSkillCrmTemplate.php');
        include('lib/DateHelper.php');
        include('helper/helper_cc.php');

        $crm_model = new MCrmIn();
        $template_model = new MSkillCrmTemplate();
        $pagination = new Pagination();

        $dateinfo = DateHelper::get_input_time_details();
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $tempid = "";
        $did = "";
        $contactNo = "";
        $accountNo = "";
        if ($this->gridRequest->isMultisearch){
            $tempid = $this->gridRequest->getMultiParam('tempid');
            $did = $this->gridRequest->getMultiParam('did');
            $contactNo = $this->gridRequest->getMultiParam('contact_no');
            $accountNo = $this->gridRequest->getMultiParam('account_no');
        }
        $this->pagination->num_records = $crm_model->numCrmInLog($tempid, $did, $dateinfo, $contactNo, $accountNo);
        $result = $this->pagination->num_records > 0 ? $crm_model->getCrmInLog($tempid, $did, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $contactNo, $accountNo) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $template_options = $template_model->getTemplateSelectOptions();
        $dp_options = $template_model->getDispositionSelectOptions();

        if(count($result) > 0){
            $authByOption = array("*"=>"-", "I"=>"IVR", "A"=>"Agent", ""=>"-");
            foreach ( $result as &$data ) {
                $data->start_time = !empty($data->tstamp) ? date("Y-m-d H:i:s", $data->tstamp) : "";
                $data->tempid = !empty($data->template_id) && isset($template_options[$data->template_id]) ? $template_options[$data->template_id] : "";
                $data->did = !empty($data->disposition_id) && isset($dp_options[$data->disposition_id]) ? $dp_options[$data->disposition_id] : "";
                $data->contact_id = $data->contact_no;
                $data->auth_by = !empty($data->caller_auth_by) && isset($authByOption[$data->caller_auth_by]) ? $authByOption[$data->caller_auth_by] : "-";
                //if (!empty($row->service_type)) {if (isset($st_options[$row->service_type])) echo $st_options[$row->service_type]; else echo $row->service_type;}
                //if ($row->caller_auth_by == 'I') echo 'IVR'; else if ($row->caller_auth_by == 'A') echo 'Agent'; else echo '-';
                //$data->caller_auth_by = "I";
                /* $data->start_time = $data->start_time > 0 ? date("Y-m-d H:i:s", $data->start_time) : '0000-00-00 00:00:00';
                $data->answer_time = $data->answer_time > 0 ? date("Y-m-d H:i:s", $data->answer_time) : '0000-00-00 00:00:00';
                $data->stop_time = $data->stop_time > 0 ? date("Y-m-d H:i:s", $data->stop_time) : '0000-00-00 00:00:00';
                $data->duration = DateHelper::get_formatted_time($data->duration);
                $data->talk_time = DateHelper::get_formatted_time($data->talk_time); */

                /* Mask credit card number */
                $data->account_id =  (strlen($data->account_id) == 16) ? maskCreditCard($data->account_id) : $data->account_id ;//: $data->account_id;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionDidCallSummary()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');

        $report_model = new MReport();

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $sdate = '';

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('sdate_box');
        }
        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        $this->pagination->num_records = $report_model->numDidCallSummary($dateinfo);
        $result = $this->pagination->num_records > 0 ? $report_model->getDidCallSummary($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$row ) {
                $row->max_duration = gmdate("H:i:s", $row->mxduration);
                $row->total_duration = gmdate("H:i:s", $row->tduration);
                $row->avg_duration = $row->calls_count > 0 ? gmdate("H:i:s", $row->tduration/$row->calls_count) : '00:00:00';
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionIvrServiceSummary()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');

        $report_model = new MReport();

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $sdate = '';

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('sdate_box');
        }
        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        $this->pagination->num_records = $report_model->numIvrServiceSummary($dateinfo);
        $result = $this->pagination->num_records > 0 ? $report_model->getIvrServiceSummary($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        /*
        if(count($result) > 0){
            foreach ( $result as &$row ) {
                $row->max_duration = gmdate("H:i:s", $row->mxduration);
                $row->total_duration = gmdate("H:i:s", $row->tduration);
            }
        }
        */

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionSkillCallSummary()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $report_model = new MReport();

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        $skill_id = "";
        $language = "";

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('start_time');
            $skill_id = $this->gridRequest->getMultiParam('skillid');
            $language = $this->gridRequest->getMultiParam('lang_id');
        }
        if (empty($language)){
            $language = "";
        }

        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        //print_r($dateinfo);

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numSkillCallSummary($dateinfo, $skill_id, $language);
        }

        $result = $this->pagination->num_records > 0 ? $report_model->getSkillCallSummary($dateinfo, $skill_id, $language, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');
            if ($sl_method) {
                $sl_method = $sl_method->value;
            }
            $sl_method = $sl_method == 'B' ? 'B' : 'A';

            $skill_options = $skill_model->getAllSkillOptions('', 'array');

            include('model/MLanguage.php');
            $language_model = new MLanguage();
            $lang_options = $language_model->getActiveLanguageListArray();

            foreach ( $result as &$row ) {
                $row->sdate_time = $row->sdate." ".$row->shour.":".$row->sminute;

                $percent_within_sl = 0;

                $sl_calls = $row->calls_offered;
                if ($sl_method == 'B') $sl_calls = $row->calls_answerd + $row->abandoned_after_threshold;
                if ($sl_calls > 0) {
                    $percent_within_sl = $row->answerd_within_service_level/$sl_calls*100;
                }
                $row->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

                $percent_abd_calls = 0;
                if ($row->calls_offered > 0) {
                    $percent_abd_calls = $row->abandoned_after_threshold/$row->calls_offered*100;
                }
                $row->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

                $avg_hold_in_q = 0;
                if ($row->calls_offered > 0) {
                    $avg_hold_in_q = $row->hold_time_in_queue/$row->calls_offered;
                }
                $row->avg_hold_in_q = DateHelper::get_formatted_time($avg_hold_in_q);


                $avg_handling_time = 0;
                if ($row->calls_answerd > 0) {
                    $avg_handling_time = ($row->service_duration+$row->hold_time_in_queue)/$row->calls_answerd;
                }
                $row->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_abd_time = 0;
                if ($row->calls_abandoned > 0) {
                    $avg_abd_time = $row->abandon_duration/$row->calls_abandoned;
                }
                $row->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

                $row->skill_name = isset($skill_options[$row->skill_id]) ? $skill_options[$row->skill_id] : $row->skill_id;
                $row->language = isset($lang_options[$row->language]) ? $lang_options[$row->language] : $row->language;
                $row->abandon_duration = gmdate("H:i:s", $row->abandon_duration);
                $row->service_duration = gmdate("H:i:s", $row->service_duration);
                $row->hold_time_in_queue = gmdate("H:i:s", $row->hold_time_in_queue);
                $row->max_hold_time_in_queue = gmdate("H:i:s", $row->max_hold_time_in_queue);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionSkillCallReport()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $report_model = new MReport();

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        $skill_id = "";
        $language = "";

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('start_time');
            $skill_id = $this->gridRequest->getMultiParam('skillid');
            $language = $this->gridRequest->getMultiParam('lang_id');
        }
        if (empty($language)){
            $language = "";
        }

        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        //print_r($dateinfo);

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numSkillCallReportSummary($dateinfo, $skill_id, $language);
        }

        $result = $this->pagination->num_records > 0 ? $report_model->getSkillCallReportSummary($dateinfo, $skill_id, $language, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');
            if ($sl_method) {
                $sl_method = $sl_method->value;
            }
            $sl_method = $sl_method == 'B' ? 'B' : 'A';

            $skill_options = $skill_model->getAllSkillOptions('', 'array');

            include('model/MLanguage.php');
            $language_model = new MLanguage();
            $lang_options = $language_model->getActiveLanguageListArray();

            foreach ( $result as &$row ) {
                //$row->sdate_time = $row->sdate." ".$row->shour.":".$row->sminute;

                $percent_within_sl = 0;

                $sl_calls = $row->calls_offered;
                if ($sl_method == 'B') $sl_calls = $row->calls_answerd + $row->abandoned_after_threshold;
                if ($sl_calls > 0) {
                    $percent_within_sl = $row->answerd_within_service_level/$sl_calls*100;
                }
                $row->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

                $percent_abd_calls = 0;
                if ($row->calls_offered > 0) {
                    $percent_abd_calls = $row->abandoned_after_threshold/$row->calls_offered*100;
                }
                $row->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

                $avg_hold_in_q = 0;
                if ($row->calls_offered > 0) {
                    $avg_hold_in_q = $row->hold_time_in_queue/$row->calls_offered;
                }
                $row->avg_hold_in_q = DateHelper::get_formatted_time($avg_hold_in_q);


                $avg_handling_time = 0;
                if ($row->calls_answerd > 0) {
                    $avg_handling_time = ($row->service_duration+$row->hold_time_in_queue)/$row->calls_answerd;
                }
                $row->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_abd_time = 0;
                if ($row->calls_abandoned > 0) {
                    $avg_abd_time = $row->abandon_duration/$row->calls_abandoned;
                }
                $row->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

                $row->skill_name = isset($skill_options[$row->skill_id]) ? $skill_options[$row->skill_id] : $row->skill_id;
                $row->language = isset($lang_options[$row->language]) ? $lang_options[$row->language] : $row->language;
                $row->abandon_duration = gmdate("H:i:s", $row->abandon_duration);
                $row->service_duration = gmdate("H:i:s", $row->service_duration);
                $row->hold_time_in_queue = gmdate("H:i:s", $row->hold_time_in_queue);
                $row->max_hold_time_in_queue = gmdate("H:i:s", $row->max_hold_time_in_queue);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }



    function actionSkillCallReportDaily()
    {
        include('model/MReport.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $report_model = new MReport();

        $reportDays = UserAuth::getReportDays();
        $user = '';
        $repLastDate = "";
        $sdate = date('Y-m-d');
        $skill_id = "";
        $language = "";

        if ($this->gridRequest->isMultisearch){
            $sdate = $this->gridRequest->getMultiParam('start_time');
            $skill_id = $this->gridRequest->getMultiParam('skillid');
            $language = $this->gridRequest->getMultiParam('lang_id');
        }
        if (empty($language)){
            $language = "";
        }

        if (empty($sdate['to'])) {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, '', $_stime, '');
        } else {
            list($_sdate, $_stime) = explode(" ", $sdate['from']);
            list($_edate, $_etime) = explode(" ", $sdate['to']);
            $dateinfo = DateHelper::get_input_time_details(false, $_sdate, $_edate, $_stime, $_etime);
        }

        //print_r($dateinfo);

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }else {
            $this->pagination->num_records = $report_model->numSkillCallReportDaily($dateinfo, $skill_id, $language);
        }

        $result = $this->pagination->num_records > 0 ? $report_model->getSkillCallReportDaily($dateinfo, $skill_id, $language, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            include('model/MSetting.php');
            $setting_model = new MSetting();
            $sl_method = $setting_model->getSetting('sl_method');
            if ($sl_method) {
                $sl_method = $sl_method->value;
            }
            $sl_method = $sl_method == 'B' ? 'B' : 'A';

            $skill_options = $skill_model->getAllSkillOptions('', 'array');

            include('model/MLanguage.php');
            $language_model = new MLanguage();
            $lang_options = $language_model->getActiveLanguageListArray();

            foreach ( $result as &$row ) {

                $percent_within_sl = 0;

                $sl_calls = $row->calls_offered;
                if ($sl_method == 'B') $sl_calls = $row->calls_answerd + $row->abandoned_after_threshold;
                if ($sl_calls > 0) {
                    $percent_within_sl = $row->answerd_within_service_level/$sl_calls*100;
                }
                $row->percent_within_sl = sprintf("%0.02f", $percent_within_sl);

                $percent_abd_calls = 0;
                if ($row->calls_offered > 0) {
                    $percent_abd_calls = $row->abandoned_after_threshold/$row->calls_offered*100;
                }
                $row->percent_abd_calls = sprintf("%0.02f", $percent_abd_calls);

                $avg_hold_in_q = 0;
                if ($row->calls_offered > 0) {
                    $avg_hold_in_q = $row->hold_time_in_queue/$row->calls_offered;
                }
                $row->avg_hold_in_q = DateHelper::get_formatted_time($avg_hold_in_q);


                $avg_handling_time = 0;
                if ($row->calls_answerd > 0) {
                    $avg_handling_time = ($row->service_duration+$row->hold_time_in_queue)/$row->calls_answerd;
                }
                $row->avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);

                $avg_abd_time = 0;
                if ($row->calls_abandoned > 0) {
                    $avg_abd_time = $row->abandon_duration/$row->calls_abandoned;
                }
                $row->avg_abd_time = DateHelper::get_formatted_time($avg_abd_time);

                $row->skill_name = isset($skill_options[$row->skill_id]) ? $skill_options[$row->skill_id] : $row->skill_id;
                $row->language = isset($lang_options[$row->language]) ? $lang_options[$row->language] : $row->language;
                $row->abandon_duration = gmdate("H:i:s", $row->abandon_duration);
                $row->service_duration = gmdate("H:i:s", $row->service_duration);
                $row->hold_time_in_queue = gmdate("H:i:s", $row->hold_time_in_queue);
                $row->max_hold_time_in_queue = gmdate("H:i:s", $row->max_hold_time_in_queue);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionSmssummary()
    {
        include('model/MEmailTemplate.php');
        include ('model/MSkill.php');
        include ('model/MAgent.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $sms_model = new MEmailTemplate();


        $skills = $skill_model->getSkillsNamesArray();
        $agents = $agent_model->get_as_key_value();

        $dateTimeArray = array();
        $dest_num = "";
        $skill_id = "";
        $agent_id = "";

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
            $dest_num = $this->gridRequest->getMultiParam('dest_number');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }

        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $this->pagination->num_records = $sms_model->numSmsSended($dateinfo, $dest_num,$skill_id,$agent_id);

        $result = $this->pagination->num_records > 0 ? $sms_model->getSmsSended($dateinfo, $dest_num, $skill_id, $agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){

            foreach ( $result as &$data ) {
                $data->tstamp = !empty($data->tstamp) ? date('Y-m-d H:i:s', $data->tstamp) : '-';
                $data->skill_id = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->agent_id = !empty($agents[$data->agent_id]) ? $agents[$data->agent_id] : $data->agent_id;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportIvrTraceLog()
    {
        include('lib/DateHelper.php');
        include('model/MReport.php');
        include('model/MIvr.php');
        $ivr_report_model = new MReport();
        $ivr_model = new MIvr();

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('tstamp');
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);
        $this->pagination->num_records = $ivr_report_model->numIvrTraceLog($dateinfo);
        $branchNames = $ivr_model->getIvrBranchNamesArray();

        $result = $this->pagination->num_records > 0 ? $ivr_report_model->getIvrTraceLog($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->call_date = !empty($data->tstamp) ? date('d/m/Y' , $data->tstamp) : '-';
                $data->start_time = !empty($data->tstamp) ? date('H:i:s' , $data->tstamp) : '-';
                $data->end_time = !empty($data->tstamp) ? date('H:i:s' , $data->tstamp+$data->sum_time_in_ivr) : '-';
                $data->tstamp = !empty($data->tstamp) ? date('d/m/Y H:i:s' , $data->tstamp) : '-';

                $traversal = explode('|', $data->traversal);
                $traversal_enter_time = explode('|', $data->traversal_enter_time);
                // $traversal_sec = explode('|', $data->traversal_sec);

                $traversal_arr=[];
                foreach ($traversal as $key => $item) {
                    if(!empty($item))
                        $traversal_arr[] = $branchNames[$item].' ['.(!empty($traversal_enter_time[$key]) ? date('H:i:s' , strtotime($traversal_enter_time[$key])) : '').']';
                }
                $data->traversal = implode(' | ', $traversal_arr);
            }
        }
        // var_dump($result);
        // die();
        $responce->hideCol = ['tstamp'];
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportIvrTraceSummary()
    {
        include('lib/DateHelper.php');
        include('model/MReport.php');
        include('model/MIvr.php');
        $ivr_report_model = new MReport();
        $ivr_model = new MIvr();

        if ($this->gridRequest->isMultisearch){
            $trace_id = $this->gridRequest->getMultiParam('trace_id');
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from']) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to']) : date('Y-m-d');
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to);
        $this->pagination->num_records = $ivr_report_model->numIvrTraceSummary($dateinfo, $trace_id);
        $branchNames = $ivr_model->getIvrBranchNamesArray();

        $result = $this->pagination->num_records > 0 ? $ivr_report_model->getIvrTraceSummary($dateinfo, $trace_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->call_date = !empty($data->enter_time) ? date('d/m/Y' , strtotime($data->enter_time)) : '-';
                $data->start_time = !empty($data->enter_time) ? date('H:i:s' , strtotime($data->enter_time)) : '-';
                $data->end_time = !empty($data->enter_time) ? date('H:i:s' , strtotime($data->enter_time)+$data->sum_time_in_ivr) : '-';
                $data->sdate = !empty($data->enter_time) ? date('d/m/Y H:i:s' , strtotime($data->enter_time)) : '-';

                $traversal = explode('|', $data->traversal);
                $traversal_enter_time = explode('|', $data->traversal_enter_time);
                // $traversal_sec = explode('|', $data->traversal_sec);

                $traversal_arr=[];
                foreach ($traversal as $key => $item) {
                    if(!empty($item))
                        $traversal_arr[] = $branchNames[$item].' ['.(!empty($traversal_enter_time[$key]) ? date('H:i:s' , strtotime($traversal_enter_time[$key])) : '').']';
                }
                $data->traversal = implode(' | ', $traversal_arr);
            }
        }
        // var_dump($result);
        // die();
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportIvrCallSummary()
    {
        include('lib/DateHelper.php');
        include('model/MReport.php');
        include('model/MIvr.php');
        $ivr_report_model = new MReport();
        $ivr_model = new MIvr();
        $report_type = "";
        $ivr_id = "";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            // var_dump($datetime_range);
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $report_type = $this->gridRequest->getMultiParam('report_type');
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);
        // var_dump($dateinfo);
        $this->pagination->num_records = $ivr_report_model->numIvrCallSummary($dateinfo, $ivr_id, $report_type);
//        GPrint($this->pagination->num_records);die;
        $ivrNames = $ivr_model->getIvrOptions();

        $result = $this->pagination->num_records > 0 ? $ivr_report_model->getCallSummary($dateinfo, $ivr_id, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(!empty($result) && count($result) > 0){
            $row_count = 0;
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->shour = "-";
            $final_row->calls_count = 0;
            $final_row->calls_ans_count = 0;
            $final_row->avg_ivr_time = 0;
            foreach ( $result as &$data ) {
                $data->sdate = date(REPORT_DATE_FORMAT, strtotime($data->sdate));
                $data->calls_ans_count = $data->calls_count;
                $data->avg_ivr_time = $data->calls_count > 0 ? ceil($data->duration / $data->calls_count) : 0;
                $data->ivr_id = $ivrNames[$data->ivr_id];

                $final_row->calls_count += $data->calls_count;
                $final_row->calls_ans_count += $data->calls_count;
                $final_row->avg_ivr_time += $data->avg_ivr_time;
            }
            $responce->userdata = $final_row;
        }

        if($report_type == REPORT_YEARLY){
            $responce->hideCol = ['shour', 'smonth', 'sdate', 'quarter_no'];
            $responce->showCol = ['syear'];
        }elseif($report_type == REPORT_QUARTERLY){
            $responce->hideCol = ['shour',  'smonth', 'sdate', 'syear'];
            $responce->showCol = ['quarter_no'];
        }elseif($report_type == REPORT_MONTHLY){
            $responce->hideCol = ['shour', 'syear', 'sdate', 'quarter_no'];
            $responce->showCol = ['smonth'];
        }elseif($report_type == REPORT_DAILY){
            $responce->hideCol = ['shour',  'smonth', 'syear', 'quarter_no'];
            $responce->showCol = ['sdate'];
        }else if($report_type == REPORT_HOURLY){
            $responce->hideCol = ['smonth', 'syear', 'quarter_no', 'shour'];
            $responce->showCol = ['sdate', 'shour'];
        }else{
            $responce->showCol = ['sdate'];
            $responce->hideCol = ['smonth', 'syear', 'quarter_no', 'shour'];
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}
