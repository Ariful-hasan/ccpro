<?php
require_once 'BaseTableDataController.php';
class DetailReport extends BaseTableDataController
{
    function __construct() {
        parent::__construct();
    }

    function actionAgentPerformanceSummary()
    {
        include('lib/DateHelper.php');
//        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
//        $controller_idx = 'get-report-new-data_agent-performance-summary2';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
//        if(isset($report_config_list[$db_role_id])){
//            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
//        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = "Y-m-d";
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Performance Summary';
        $data['dataUrl'] = $this->url('task=detail-report&act=agent-performance-summary-report');
        $this->getTemplate()->display('report_new_agent_performance_summary_report', $data);
    }

    function actionCategoryDetailsReport()
    { 
        include('lib/DateHelper.php');
        include('model/MDetailReport.php');
        $model = new MDetailReport();
        $skill_list = $model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);
        
        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        // $controller_idx = 'get-report-new-data_report-category-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        // if(isset($report_config_list[$db_role_id])){
        //     $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        // }
      
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = "Y-m-d";
        $data['pageTitle'] = 'Details Report';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['skill_list'] = $skill_list;
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'V';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['hangup_ini_list'] = ['*' => 'All']+get_disc_party('');
        $disposition_list = $model->get_disposition_all_value();
        $data['disposition_list'] =  array_column($disposition_list,'title','disposition_id');
        
        $data['dataUrl'] = $this->url('task=detail-report&act=report-category-details');
        $this->getTemplate()->display('detail_report', $data);
    }

    public function actionAgentPerformanceSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MDetailReport.php');

        $model = new MDetailReport();

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = "Y-m-d";
        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($dateinfo->errMsg)){
            if (!empty($reportDays)){
                $toDate = date("Y-m-d");
                $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
            }
            if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
                $this->pagination->num_records = 0;
            }

            $agent_options = $model->getAgentsFullName();
            $this->pagination->num_records = $model->numAgentPerformanceSummary($dateinfo);
            $result = $this->pagination->num_records > 0 ?
                $model->getAgentPerformanceSummary($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            if (is_array($result) && count($result) > 0) {
                foreach ($result as &$data){
                    $skill_set = $model->getAgentSkillSetAsString2($data->agent_id);
                    $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";
                    $agent_call_info = $model->getAgentCallInformation($dateinfo, $data->agent_id);

                    if(!empty($agent_call_info)){
                        $data->calls_answered = (empty($agent_call_info->calls_answered)) ? 0 : $agent_call_info->calls_answered;
                        $data->ring_time = (empty($agent_call_info->ring_time)) ? 0 : $agent_call_info->ring_time;
                        $data->wrap_up_time = (empty($agent_call_info->wrap_up_time)) ? 0 : $agent_call_info->wrap_up_time;
                        $data->service_time = (empty($agent_call_info->service_time)) ? 0 : $agent_call_info->service_time;
                        $data->hold_time = (empty($agent_call_info->hold_time)) ? 0 : $agent_call_info->hold_time;
                        $data->hold_time_in_queue = (empty($agent_call_info->hold_time_in_queue)) ? 0 : $agent_call_info->hold_time_in_queue;
                        $data->agent_hangup = (empty($agent_call_info->agent_hangup)) ? 0 : $agent_call_info->agent_hangup;
                        $data->agent_reject_calls = (empty($agent_call_info->agent_reject_calls)) ? 0 : $agent_call_info->agent_reject_calls;
                        $data->agent_disc_calls = (empty($agent_call_info->agent_disc_calls)) ? 0 : $agent_call_info->agent_disc_calls;
                        $data->short_call = (empty($agent_call_info->short_call)) ? 0 : $agent_call_info->short_call;
                        $data->repeated_call = (empty($agent_call_info->repeated_call)) ? 0 : $agent_call_info->repeated_call;
                        $data->fcr_call = (empty($agent_call_info->fcr_call)) ? 0 : $agent_call_info->fcr_call;
                        $data->workcode_count = (empty($agent_call_info->workcode_count)) ? 0 : $agent_call_info->workcode_count;
                        $data->talk_time = $data->service_time - $data->hold_time;
                    }

                    //agent session info
                    $agentinfo = $model->getAgentSessionInfo($data->agent_id, $dateinfo);
                    $data->first_login =  date($date_format." H:i:s",strtotime($agentinfo->first_login));
                    $data->not_ready_time =  $agentinfo->not_ready_time;
                    $data->available_time = $agentinfo->staffed_time > $agentinfo->not_ready_time ? $agentinfo->staffed_time - $data->not_ready_time : 0;
                    $data->login_time = gmdate("H:i:s", $agentinfo->staffed_time);
                    $data->total_idle_time = $agentinfo->staffed_time - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);

                    $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : 0;
                    $data->logout_time =  empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                    $data->available_time = gmdate("H:i:s", $data->available_time);
                    $data->not_ready_count = $agentinfo->not_ready_count;
                    $data->agent_info = $agentinfo;

                    $data->agent_name = $agent_options[$data->agent_id];
                    $data->aht = $data->calls_answered > 0 ? round(($data->ring_time + $data->talk_time + $data->hold_time + $data->wrap_up_time) / $data->calls_answered) : 0;
                    $data->workcode_percent = $data->calls_answered > 0 ? round(($data->workcode_count / $data->calls_answered) * 100,2) : 0;
                    $data->workcode_percent .= '%';
                    $data->fcr_percent = $data->calls_answered > 0 ? round(($data->fcr_call / $data->calls_answered) * 100,2) : 0;
                    $data->fcr_percent .= '%';
                    $data->repeated_percent = $data->calls_answered > 0 ? round(($data->repeated_call / $data->calls_answered) * 100,2) : 0;
                    $data->repeated_percent .= '%';
                }
            }
            $response = $this->getTableResponse();
            $response->rowdata = $result;
            $response->records = $this->pagination->num_records;
            array_push($response->hideCol,'sdate');
            $request_param = [
                'sdate' => $dateinfo->sdate,
                'edate' => $dateinfo->edate
            ];
            $this->reportAudit('NR', 'Agent Performance', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionReportCategoryDetails()
    { 
        include('lib/DateHelper.php');
        include('model/MDetailReport.php');        

        $model = new MDetailReport();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $disposition_type_list = disposition_type_list();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        // $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = []; 
        // if(isset($report_config_list[$db_role_id])){ 
        //     $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        //     $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        // }
           
        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate'); 
            $date_format = "Y-m-d";
            $skill_ids = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $hangup_initiator = $this->gridRequest->getMultiParam('hangup_initiator');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $disposition_id = (!empty($this->gridRequest->getMultiParam('disposition_id'))) ? $this->gridRequest->getMultiParam('disposition_id') : '';
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $model->getSkillsNamesArray();
            $skills_types = $model->getSkillsTypeArray();
            
            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }     

            if (!empty($reportDays)){
                $toDate = date("Y-m-d");
                $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
            }
            if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
                $this->pagination->num_records = 0;
            }
            $csv_titles=array();
            $csv_data=array();
            $result=array();
            $isRemoveTag = true; // use csv download
            $delimiter = ',';    // use csv download     
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download
            $skip_row_count = 0;  // use grid view
            $callIdArr = [];  // use grid view
            $dispositions =  $model->get_disposition_all_value();
            // GPrint($dispositions);
            // die();

            // calculate total count
            $this->pagination->num_records = $model->numCategoriesDetailsReport($dateinfo, $skill_ids, '', $skill_type, $hangup_initiator, $disposition_id);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'hangup_initiator' => $hangup_initiator,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            if($this->gridRequest->isDownloadCSV){
                $model->saveReportAuditRequest('NRD::Skill Details', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                        array_push($csv_titles,$value);
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }

            }else{                
                $model->saveReportAuditRequest('NRS::Skill Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download                
                    $result = $model->getCategoriesDetailsReport($dateinfo, $skill_ids, '', $skill_type, $hangup_initiator, $disposition_id,  $dbResultOffset, $dbResultRow, false, false);
                }else{ // for grid view                
                    $result = $this->pagination->num_records > 0 ? $model->getCategoriesDetailsReport($dateinfo, $skill_ids, '', $skill_type, $hangup_initiator, $disposition_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){                
                    $fileInputRowCount = 0; // for download
                    $transfer_types = ['A'=>'Agent', 'I'=>'IVR', 'Q'=>'Skill'];

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format.' H:i:s', strtotime($data->call_start_time));
                        if(strlen($data->cli) == 10){
                            $data->msisdn_880 = "880". $data->cli;
                        } elseif (strlen($data->cli) == 11){
                            $data->msisdn_880 = "88" . $data->cli;
                        }else{
                            $data->msisdn_880 = $data->cli;
                        }

                        // $data->msisdn_880 = !empty($data->cli) ? '880'.substr($data->cli, -10) : '';
                        // $data->cli = substr($data->cli, -10);

                        $data->title = $dispositions[$data->disposition_id]['title'];
                        $data->disposition_type = $dispositions[$data->disposition_id]['type'];
                        $data->responsible_party = $dispositions[$data->disposition_id]['responsible_party'];

                        $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                        $data->abandon_flag = ($data->status != 'S') ? 'Yes' : 'No';
                        $data->abandon_cli = ($data->status != 'S') ? $data->cli : ($this->gridRequest->isDownloadCSV ? '' : '-');
                        $data->talk_time = $data->service_time - $data->agent_hold_time;
                        $data->agent_handling_time = ($data->status == 'S') ? $data->ring_time+$data->talk_time+$data->agent_hold_time+$data->wrap_up_time : 0;

                        $data->repeated_call_flag = ($data->repeated_call == 'Y') ? 'Yes' : 'No';
                        $data->short_call = ($data->status == 'S' && $data->service_time < 10) ? 'Yes' : 'No';
                        $data->ice_feedback = ($data->ice_feedback == 'Y') ? 'Positive' : ($data->ice_feedback == 'N' ? 'Negative' : ($this->gridRequest->isDownloadCSV ? '' : '-'));
                        $data->qrc_tagging = (!empty($data->disposition_type)) ? $disposition_type_list[$data->disposition_type] : ($this->gridRequest->isDownloadCSV ? '' : '-');
                        $data->disc_party = empty($data->disc_party) ? '' : get_disc_party($data->disc_party);
                        $data->transfer_tag = (!empty($data->transfer_tag) && !empty($transfer_types[ $data->transfer_tag])) ? $transfer_types[ $data->transfer_tag] : $data->transfer_tag;
                        

                        if($this->gridRequest->isDownloadCSV){ 
                            if($data->disposition_count > 0)
                                $data->custom_title = $dispositions[$data->disposition_id]['title'];
                            
                            $row=array();
                            foreach ($cols as $key=>$value){
                                $rvalue="";
                                if($isRemoveTag){
                                    if(isset($data->$key)){
                                        $rvalue=strip_tags($data->$key);
                                        if ($key == 'callid') $rvalue = 'ID-' . $rvalue;
                                    }
                                }else{
                                    if(isset($data->$key)){
                                        $rvalue=$data->$key;
                                    }else{
                                        $rvalue="";
                                    }
                                }
                                $rvalue=preg_replace("/&.*?; /", "", $rvalue);
                                array_push($row, $rvalue);
                            }
                            fputcsv($f, $row, $delimiter);

                            $fileInputRowCount++;
                        }else{
                            $callIdArr[] = $data->callid;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $model->getCategoriesDetailsReport($dateinfo, $skill_ids, $report_type, $skill_type, $hangup_initiator, $disposition_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->skill_id = "-";
                        $final_row->skill_name = "-";
                        $final_row->msisdn_880 = "-";
                        $final_row->cli = "-";
                        $final_row->abandon_flag = "-";
                        $final_row->abandon_cli = "-";
                        $final_row->agent_id = "-";
                        // $final_row->wrap_up_time = '-';
                        $final_row->title = '-';
                        $final_row->qrc_tagging = '-';
                        $final_row->qrc_type_tagging = '-';
                        $final_row->short_call = '-';
                        $final_row->repeated_call_flag = '-';
                        $final_row->disc_party = '-';
                        $final_row->ice_feedback = '-';
                        // $final_row->disposition_count = '-'; 

                        // $final_row->delay_between_call = $final_row->call_ans*DELAY_BETWEEN_CALLS;
                        $final_row->talk_time = $final_row->service_time - $final_row->agent_hold_time;
                        $final_row->agent_handling_time = $final_row->ring_time+$final_row->talk_time+$final_row->agent_hold_time+$final_row->wrap_up_time;

                        $response->userdata = $final_row;
                    }                    
                    
                    if($fileInputRowCount < DOWNLOAD_PER_PAGE){
                        break;
                    }else{
                        $fileInputRow++;
                        $dbResultOffset = $dbResultRow*($fileInputRow-1);
                    }         
                }else{
                    break;
                }
            }  

            if($this->gridRequest->isDownloadCSV){  // for download
                fclose($f);
                die();
            }else{  // for grid view 
                // var_dump($callIdArr);
                $callIdStr = implode("','", $callIdArr);
                $multiple_disposition = count($callIdArr) > 0 ? $model->getMultipleDisposition($callIdStr) : null;
                // var_dump($multiple_disposition);
                if(!empty($multiple_disposition)){
                    $call_id_disposition = [];
                    foreach ($multiple_disposition as $key => $value) {
                        // if(!empty($value->disposition_id)){
                            $result_key = array_search($value->callid, $callIdArr);
                            if($result[$result_key]->disposition_count > 1){
                                $call_id_disposition[$value->callid][] = $dispositions[$value->disposition_id]['title'];
                            }

                            if(!empty($call_id_disposition[$value->callid])){
                                $result[$result_key]->custom_title = $result[$result_key]->title.' <a onclick="showDispostionModal(\''.implode(',', $call_id_disposition[$value->callid]).'\')" class="show-multiple-disposition" ><i class="fa fa-arrow-circle-right" style="cursor: pointer;" aria-hidden="true"></i></a>';
                            }else{
                                $result[$result_key]->custom_title = $result[$result_key]->title;
                            }
                        // }
                    }
                }

                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

}
