<?php
require_once 'BaseTableDataController.php';
class GetReportNewData extends BaseTableDataController
{
    function __construct()
    {
        parent::__construct();
        if($this->gridRequest->isDownloadCSV){
            setcookie('download_csv', 1);
        }
    }

    function actionHourlyQueueWaitTime(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $skills = $skill_model->getSkillsNamesArray();

        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        $stime = "00";
        $etime = "23";
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? $date_range['to'] : date('Y-m-d');
            $shour = $this->gridRequest->getMultiParam('shour');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $avg_condition = $this->gridRequest->getMultiParam('avg_con');

            $stime = date("H", strtotime($date_from));
            $etime = date("H", strtotime($date_to));
        }
        //$dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');
        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $stime, $etime);
        //GPrint($dateinfo);die;
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        //GPrint($dateinfo);die;
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $data = $report_model->getHourlyQueueWaitTime($dateinfo,$skill_id, $avg_condition);
        $temp_data = $data;
        $temp_array = array();
        //$hour_array = array();
        $result = array();
        $obj_array = array();
        //GPrint($data);die;

        if (!empty($data)){
            foreach ($data as $key){
                $hour_array = null;
                $avg_array_count = 0;
                $obj_array = null;
                if (!in_array($key->sdate,$temp_array)){
                    foreach ($temp_data as $item){
                        if ($key->sdate == $item->sdate){
                            //$hour_array[$item->shour] = $item->hold_time_in_queue;
                            //$avg_array_count += $item->avg_time;
                            //$skill = !empty($skills[$item->skill_id]) ? $skills[$item->skill_id] : $item->skill_id;
                            $obj = new stdClass();
                            $obj->sdate = $item->sdate;
                            $obj->skill_id = $item->skill_id;
                            $obj->shour = $item->shour;
                            $obj->hold_time_in_queue = $item->hold_time_in_queue;
                            $obj->avg_time = $item->avg_time;
                            $obj_array []= $obj;
                        }
                    }
                    //GPrint($obj_array);die;
                    $response = $this->calculate_hour_queue($obj_array,$skills);
                    if (count($response) > 0){
                        foreach ($response as $res){
                            $result[] = $res;
                        }
                    }
                    //$result[] = $this->calculate_hour_queue($key->sdate, $hour_array,$avg_array_count,$skill);
                    $temp_array[] = $key->sdate;
                }
            }
        }


        /*if (count($result) > 0){
            $reserve_array = array();
            $avg_reserve_array = array();
            $i = 0;
            $total_count = count($result);
            $temp_array = $result;
            $total_avg_sec = 0;

            foreach ($result as $key => $value){
                $number = $i < 10 ? "0".$i : $i;
                $total_Second = 0;
                $str = "shour_".$number;
                echo $str;
                foreach ($temp_array  as $item => $tmp){
                   if (array_key_exists($str,$tmp)){
                       list($min_temp, $sec_temp) = explode(":",$tmp->$str);
                       $total_Second = $total_Second + $min_temp*60 + $sec_temp;
                   }
                }

                list($avg_min,$avg_sec) = explode(":",$value->avg_time);
                $total_avg_sec = $total_avg_sec + $avg_min*60 + $avg_sec;

                $reserve_array[$str] = !empty($total_Second) ? date("i:s",round($total_Second/$total_count)) : "00:00";
                //$avg_reserve_array[$str] = !empty($total_avg_sec) ? date("i:s",round($total_avg_sec/$total_count)) : "00:00";
                $i++;
            }
            GPrint($result);die;
            $result[] = $this->setAverageHourlyQueueWaitTime($reserve_array,round($total_avg_sec/$total_count));
        }*/


        $this->pagination->num_records = count($result);
        $result = $this->pagination->num_records > 0 ? $result : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        if (count($result)){
            $response->userdata = $this->getAverageSummaryForHourlyQueueWaitTime($result);
            //GPrint($response->userdata);die;
        }
        $this->ShowTableResponse();
    }

    function getAverageSummaryForHourlyQueueWaitTime($result, $start_time=0, $end_time=23){
        $total_count = count($result);
        $average_time_arr = [];
        $total_avg_sec = 0;
        //GPrint($end_time);die;
        for ($n=$start_time; $n<=$end_time; $n++){
            $number = $n < 10 ? "0".$n : $n;
            $str = "shour_".$number;
            $total_Second = 0;
            foreach ($result as $key => $value){
                if (array_key_exists($str,$value)){
                    list($min_temp, $sec_temp) = explode(":",$value->$str);
                    $total_Second = $total_Second + $min_temp*60 + $sec_temp;
                }
                //if ($n == 23){
                if ($n == $end_time){
                    list($avg_min,$avg_sec) = explode(":",$value->avg_time);
                    $total_avg_sec = $total_avg_sec + $avg_min*60 + $avg_sec;
                }
            }
            $average_time_arr[$str] = !empty($total_Second) ? date("i:s",round($total_Second/$total_count)) : "00:00";
            //$average_time_arr[$str] = !empty($total_Second) ? date("i:s",round($total_Second/$total_count)) : "";
        }
        return $this->setAverageHourlyQueueWaitTime($average_time_arr, round($total_avg_sec/$total_count), $start_time, $end_time);
    }

    function setAverageHourlyQueueWaitTime($data,$avg_data, $start_time=0, $end_time=23){
        $obj = new stdClass();
        $obj->avg_time = !empty($avg_data) ? date("i:s",$avg_data) : "00:00";
        //$obj->avg_time = !empty($avg_data) ? date("i:s",$avg_data) : "";
        $obj->sdate = "-";
        $obj->skill_id = "";
        if (!empty($data)){
            for ($i=$start_time; $i<=$end_time; $i++){
                $number = $i < 10 ? "0".$i : $i;
                $str = "shour_".$number;
                $obj->$str = array_key_exists($str,$data)? $data[$str] : "00:00" ;
                //$obj->$str = array_key_exists($str,$data)? $data[$str] : "" ;
            }
        }
        return $obj;
    }

    function calculate_hour_queue($obj_array,$skills, $start_time=0, $end_time=23){
        $temp_array = array();
        $return_array = array();
        //GPrint($end_time);die;
        if (!empty($obj_array)){
            foreach ($obj_array as $key){
                if (!in_array($key->skill_id,$temp_array)){
                    $temp_array[] = $key->skill_id;
                }
            }

            if (!empty($temp_array)){
                foreach ($temp_array as $skill => $value){
                    $obj = new stdClass();
                    $obj->avg_time = 0;
                    //$obj->hold_time_in_queue = 0;
                    foreach ($obj_array as $key){
                        $obj->sdate = $key->sdate;
                        if ($value == $key->skill_id ){
                            $str = "shour_".$key->shour;
                            //$obj->$str = !empty($key->shour)?gmdate("i:s",$key->hold_time_in_queue):"00:00";
                            $obj->$str = !empty($key->shour)?gmdate("i:s",round($key->avg_time)):"00:00";
                            //$obj->$str = !empty($key->shour)?gmdate("i:s",round($key->avg_time)):"";
                            $obj->skill_id = !empty($skills[$key->skill_id])?$skills[$key->skill_id]:$key->skill_id;
                            $obj->avg_time += $key->avg_time;
                            //$obj->hold_time_in_queue = $obj->hold_time_in_queue < $key->hold_time_in_queue ? $key->hold_time_in_queue : $obj->hold_time_in_queue;
                        }
                    }
                    $obj->avg_time = !empty($obj->avg_time)?gmdate("i:s",round($obj->avg_time)):"00:00";

                    //GPrint($obj);die;
                    for ($i=$start_time; $i<=$end_time; $i++){
                        $number = $i < 10 ? "0".$i : $i;
                        $str = "shour_".$number;
                        $obj->$str = !empty($obj->$str)? $obj->$str : "00:00" ;
                        //$obj->$str = !empty($obj->$str)? $obj->$str : "" ;
                    }
                    $return_array[] = $obj;
                }
            }
        }
        return $return_array;
    }

    /*function calculate_hour_queue_old($date, $hour_array, $avg_time_count,$skill){
        $obj = new stdClass();
        $obj->sdate = $date;
        for ($i=0; $i<24; $i++){
            $number = $i < 10 ? "0".$i : $i;
            $str = "shour_".$number;
            $obj->$str = !empty($hour_array[$number])? gmdate('i:s',$hour_array[$number]): "00:00";
        }
        $obj->avg_time = !empty($avg_time_count)? date('i:s',  round($avg_time_count)):"00:00";
        $obj->skill_id = $skill;
        return $obj;
    }*/

    function actionQueuewaittime(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numQueueWaitTime($dateinfo,$skill_id);


        $result = $this->pagination->num_records > 0 ?
            $report_model->getQueueWaitTime($dateinfo, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $skills = $skill_model->getSkillsNamesArray();
            foreach ( $result as &$data ) {
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->skill_id = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->avg_queue_wait_time = $data->avg_queue_wait_time > 0 ? gmdate("H:i:s", round($data->avg_queue_wait_time)) : "00:00:00";
                $data->max_hold_time_in_queue = $data->max_hold_time_in_queue > 0 ? gmdate("H:i:s", $data->max_hold_time_in_queue) : "00:00:00";
                $data->min_hold_time_in_queue = $data->min_hold_time_in_queue > 0 ? gmdate("H:i:s", $data->min_hold_time_in_queue) : "00:00:00";
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();

    }

    function actionReportagentstatus()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $shift_code = $this->gridRequest->getMultiParam('shift_code');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numAgentStatus($dateinfo,$agent_id,$shift_code);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentStatus($dateinfo, $agent_id, $shift_code, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->agent_id = "-";
            $final_row->agent_name = "-";
            $final_row->shift_code = "-";
            $final_row->first_login = "-";
            $final_row->last_logout = "-";
            $final_row->staff_time = 0;
            $final_row->no_login_time = 0;
            $final_row->total_break_time = 0;
            $final_row->total_aux_in_time = 0;
            $final_row->total_aux_out_time = 0;
            $final_row->login_count = 0;
            $final_row->logout_count = 0;

            foreach ( $result as &$data )
            {
                /*--------------------Summery/Final row data calculation----------------------*/
                $final_row->staff_time += $data->staff_time;
                $final_row->no_login_time += $data->no_login_time;
                $final_row->total_break_time += $data->total_break_time;
                $final_row->total_aux_in_time += $data->total_aux_in_time;
                $final_row->total_aux_out_time += $data->total_aux_out_time;
                $final_row->login_count += $data->login_count;
                $final_row->logout_count += $data->login_count; // login and logout count are same
                /*--------------------End of Summery/Final row data calculation----------------------*/

                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->staff_time = DateHelper::get_formatted_time($data->staff_time);
                $data->no_login_time = DateHelper::get_formatted_time($data->no_login_time);
                $data->total_unready_time = DateHelper::get_formatted_time($data->total_break_time + $data->total_aux_in_time + $data->total_aux_out_time);

                $data->total_break_time = DateHelper::get_formatted_time($data->total_break_time);
                $data->total_aux_in_time = DateHelper::get_formatted_time($data->total_aux_in_time);
                $data->total_aux_out_time = DateHelper::get_formatted_time($data->total_aux_out_time);
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->first_login = date($date_format . " H:i:s", strtotime($data->first_login));
                $data->last_logout = date($date_format . " H:i:s", strtotime($data->last_logout));

            }

            $final_row->staff_time = DateHelper::get_formatted_time($final_row->staff_time);
            $final_row->no_login_time = DateHelper::get_formatted_time($final_row->no_login_time);
            $final_row->total_unready_time = DateHelper::get_formatted_time($final_row->total_break_time + $final_row->total_aux_in_time + $final_row->total_aux_out_time);
            $final_row->total_break_time = DateHelper::get_formatted_time($final_row->total_break_time);
            $final_row->total_aux_in_time = DateHelper::get_formatted_time($final_row->total_aux_in_time);
            $final_row->total_aux_out_time = DateHelper::get_formatted_time($final_row->total_aux_out_time);

            $response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportagentcallstatus()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $shift_code = $this->gridRequest->getMultiParam('shift_code');
            $report_type = $this->gridRequest->getMultiParam('type');
        }
        // $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');

        if(empty($dateinfo->errMsg)){
            $agent_names = $agent_model->getAgentsFullName();
            if ($report_type == REPORT_YEARLY) {
                $hideCol = ['smonth', 'sdate', 'quarter_no'];
                $showCol = ['syear'];
            } elseif ($report_type == REPORT_QUARTERLY) {
                $hideCol = ['smonth', 'sdate', 'syear'];
                $showCol = ['quarter_no'];
            } elseif ($report_type == REPORT_MONTHLY) {
                $hideCol = ['syear', 'sdate', 'quarter_no'];
                $showCol = ['smonth'];
            } else {
                $hideCol = ['smonth', 'syear', 'quarter_no'];
                $showCol = ['sdate'];
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
            $original_result = [];  // use grid view

            // calculate total count
            $this->pagination->num_records = $report_model->numAgentStatus($dateinfo,$agent_id, $shift_code, $report_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = $hideCol;
            $response->showCol = $showCol;

            //download
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'agent_id' => $agent_id,
                'shift_code' => $shift_code,
                'report_type' => $report_type
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Agent Summary', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if (!empty($hideCol)) {
                    foreach ($hideCol as $key => $value) {
                        unset($cols->$value);
                    }
                }
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                        array_push($csv_titles,$value);
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }
            }else{
                $report_model->saveReportAuditRequest('NRS::Agent Summary', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getAgentStatus($dateinfo, $agent_id, $shift_code,  $dbResultOffset, $dbResultRow, $report_type);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getAgentStatus($dateinfo, $agent_id, $shift_code,  $this->pagination->getOffset(), $this->pagination->rows_per_page, $report_type) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download
                    foreach ( $result as &$data )
                    {
                        $data->sdate = date($date_format, strtotime($data->sdate));
                        $data->total_not_ready_time = $data->total_aux_in_time+$data->total_aux_out_time+$data->total_break_time;
                        $data->total_ready_time = $data->staff_time - $data->total_not_ready_time;
                        $data->idle_time = ($data->available_time-$data->calls_in_time-$data->calls_out_time) < 0 ? 0 : ($data->available_time-$data->calls_in_time-$data->calls_out_time);

                        $data->agent_name = isset($agent_names[$data->agent_id]) ? ' ' . $agent_names[$data->agent_id] : ' ' . $data->agent_id;
                        $data->ring_after_5_count = $data->ring_6_to_10_count + $data->ring_gt_11_count;
                        $data->staff_time = DateHelper::get_formatted_time($data->staff_time);
                        $data->idle_time = DateHelper::get_formatted_time($data->idle_time);
                        $data->available_time = DateHelper::get_formatted_time((int)($data->available_time));
                        $data->disconnected_call = 0;

                        $data->number_of_not_ready = $data->aux_11_count+$data->aux_12_count+$data->aux_13_count+$data->aux_14_count+$data->aux_15_count+$data->aux_16_count+$data->aux_17_count+$data->aux_18_count+$data->aux_19_count+$data->aux_20_count+$data->aux_21_count;

                        $data->first_login = date_format(date_create_from_format("Y-m-d H:i:s", $data->first_login), $date_format . ' H:i:s');
                        $data->last_logout = (!empty($data->last_logout) && $data->last_logout != '0000-00-00 00:00:00') ? date_format(date_create_from_format("Y-m-d H:i:s", $data->last_logout), $date_format . ' H:i:s') : '';

                        $data->asa_result = (!empty($data->calls_in_ans)) ? fractionFormat($data->ring_in_time / $data->calls_in_ans, "%.0f", $this->gridRequest->isDownloadCSV) : 0;
                        // $data->delay_between_call = $data->calls_in_ans*DELAY_BETWEEN_CALLS;
                        $data->talk_time = $data->calls_in_time - $data->hold_time;

                        if($this->gridRequest->isDownloadCSV){
                            $data->avg_talk_time = $data->calls_in_ans > 0 ? $data->talk_time / $data->calls_in_ans : 0;
                            $data->avg_hold_time = $data->calls_in_ans > 0 ? $data->hold_time / $data->calls_in_ans : 0;
                            $data->aux_11_time = $data->calls_in_ans > 0 ? $data->aux_11_time /$data->calls_in_ans : 0;
                            $data->avg_call_handling_time = $data->calls_in_ans > 0 ? ($data->talk_time + $data->hold_time + $data->ring_in_time+$data->wrap_up_time)/$data->calls_in_ans : 0;
                            // $data->fcr_call_count = "";
                            // $data->repeat_call_count = "";
                            // $data->wrap_up_time = '';
                            // $data->wrap_up_count = '';
                        }else{
                            $data->avg_call_handling_time = $data->calls_in_ans > 0 ? round(($data->talk_time + $data->hold_time + $data->ring_in_time+$data->wrap_up_time)/$data->calls_in_ans) : 0;
                            $data->avg_talk_time = $data->calls_in_ans > 0 ? round($data->talk_time / $data->calls_in_ans) : 0;
                            $data->avg_hold_time = $data->calls_in_ans > 0 ? round($data->hold_time / $data->calls_in_ans) : 0;
                            $data->aux_11_time = $data->calls_in_ans > 0 ? round($data->aux_11_time /$data->calls_in_ans) : 0;
                            // $data->fcr_call_count = "-";
                            // $data->repeat_call_count = "-";
                            // $data->wrap_up_time = '-';
                            // $data->wrap_up_count = '-';
                            // $data->fcr_call_percentage = "-";
                            // $data->repeat_call_percentage = "-";
                            // $data->wrap_up_count_percentage = (!empty($data->calls_in_ans)) ? fractionFormat((($data->wrap_up_count / $data->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        }

                        $data->fcr_call_percentage = (!empty($data->calls_in_ans)) ? fractionFormat((($data->fcr_call_count / $data->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        $data->repeat_call_percentage = (!empty($data->calls_in_ans)) ? fractionFormat((($data->repeat_call_count / $data->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        $data->wrap_up_count_percentage = (!empty($data->calls_in_ans)) ? fractionFormat((($data->wrap_up_count / $data->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getAgentStatus($dateinfo, $agent_id, $shift_code,  $this->pagination->getOffset(), $this->pagination->rows_per_page, $report_type, false) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                        $final_row->shift_code = "-";
                        $final_row->first_login = "-";
                        // $final_row->fcr_call_count = "-";
                        // $final_row->fcr_call_percentage = "-";
                        // $final_row->repeat_call_count = "-";
                        // $final_row->repeat_call_percentage = "-";
                        $final_row->last_logout = "-";
                        $final_row->sdate = "-";

                        /*--------------   Formatting Summery row data   -------------*/
                        $final_row->asa_result = (!empty($final_row->calls_in_ans)) ? fractionFormat($final_row->ring_in_time / $data->calls_in_ans, "%.0f", $this->gridRequest->isDownloadCSV) : 0;
                        $final_row->total_not_ready_time = $final_row->total_aux_in_time+$final_row->total_aux_out_time+$final_row->total_break_time;
                        $final_row->number_of_not_ready = $final_row->aux_11_count+$final_row->aux_12_count+$final_row->aux_13_count+$final_row->aux_14_count+$final_row->aux_15_count+$final_row->aux_16_count+$final_row->aux_17_count+$final_row->aux_18_count+$final_row->aux_19_count+$final_row->aux_20_count+$final_row->aux_21_count;
                        // $final_row->delay_between_call = $final_row->calls_in_ans*DELAY_BETWEEN_CALLS;
                        $final_row->staff_time = '-';
                        $final_row->idle_time = '-';
                        $final_row->available_time = '-';
                        // $final_row->wrap_up_time = '-';
                        // $final_row->wrap_up_count = '-';
                        $final_row->fcr_call_percentage = (!empty($final_row->calls_in_ans)) ? fractionFormat((($final_row->fcr_call_count / $final_row->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        $final_row->repeat_call_percentage = (!empty($final_row->calls_in_ans)) ? fractionFormat((($final_row->repeat_call_count / $final_row->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        $final_row->wrap_up_count_percentage = (!empty($final_row->calls_in_ans)) ? fractionFormat((($final_row->wrap_up_count / $final_row->calls_in_ans) * 100), $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                        $final_row->talk_time = $final_row->calls_in_time - $final_row->hold_time;

                        $final_row->avg_talk_time = $final_row->calls_in_ans > 0 ? round($final_row->talk_time / $final_row->calls_in_ans) : 0;
                        $final_row->avg_hold_time = $final_row->calls_in_ans > 0 ? round($final_row->hold_time / $final_row->calls_in_ans) : 0;
                        $final_row->aux_11_time = $final_row->calls_in_ans > 0 ? round($final_row->aux_11_time /$final_row->calls_in_ans) : 0;
                        $final_row->avg_call_handling_time = $final_row->calls_in_ans > 0 ? round(($final_row->talk_time + $final_row->hold_time + $data->ring_in_time+$final_row->wrap_up_time)/$final_row->calls_in_ans) : 0;

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
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionAgentOutboundCallStatus()
    {

        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $shift_code = $this->gridRequest->getMultiParam('shift_code');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numAgentOutboundCallStatus($dateinfo,$agent_id,$shift_code);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentOutboundCallStatus($dateinfo, $agent_id, $shift_code, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            foreach ( $result as &$data )
            {
                $data->success_rate = $data->calls_out_attempt > 0 ? sprintf("%.02f", ($data->calls_out_reached / $data->calls_out_attempt) * 100) : 0;
                $data->staff_time = DateHelper::get_formatted_time($data->staff_time);
                $data->ring_out_time = DateHelper::get_formatted_time($data->ring_out_time);
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                $data->talk_time = DateHelper::get_formatted_time($data->calls_out_time);

                $data->hold_time = DateHelper::get_formatted_time($data->hold_time );
                $data->sdate = date($date_format, strtotime($data->sdate));
                for ($i = 11; $i <= 20; $i++){
                    $aux_time = "aux_".$i."_time";
                    $data->{$aux_time} = DateHelper::get_formatted_time($data->{$aux_time});
                }

            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAgentProductivityStatus()
    {

        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');


        $report_model = new MReportNew();


        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : 'd/m/Y';
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $shift_code = $this->gridRequest->getMultiParam('shift_code');
            $sum_date = $this->gridRequest->getMultiParam('sum_date');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');

        $this->pagination->num_records = $report_model->numAgentProductivityStatus($dateinfo,$agent_id,$shift_code,$sum_date);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentProductivityStatus($dateinfo, $agent_id, $shift_code, $sum_date, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            $row_count = 0;
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->agent_id = "-";
            $final_row->shift_code = "-";
            $final_row->avg_talk_time = 0;
            $final_row->avg_call_handling_time = 0;
            $final_row->hold_time = 0;
            $final_row->aux_11_time = 0;


            foreach ( $result as &$data )
            {
                /*-------------------------- Summery row data calculation ---------------------------*/

                $row_count++;
                $final_row->avg_talk_time += $data->calls_in_ans ? ceil($data->calls_in_time / $data->calls_in_ans) : $data->calls_in_time;
                $final_row->avg_call_handling_time += $data->calls_in_ans > 0 ? ceil(($data->calls_in_time + $data->hold_time + $data->aux_11_time) /$data->calls_in_ans) : ($data->calls_in_time + $data->hold_time + $data->aux_11_time);
                $final_row->hold_time += $data->calls_in_ans > 0 ? ceil($data->hold_time / $data->calls_in_ans) : $data->hold_time;
                $final_row->aux_11_time += $data->calls_in_ans > 0 ? ceil($data->aux_11_time /$data->calls_in_ans) : $data->aux_11_time;


                /*--------------------------End of Summery row data calculation ---------------------------*/

                $data->avg_talk_time = $data->calls_in_ans > 0 ? ceil($data->calls_in_time / $data->calls_in_ans) : $data->calls_in_time;
                $data->hold_time = $data->calls_in_ans > 0 ? ceil($data->hold_time / $data->calls_in_ans) : $data->hold_time;
                $data->aux_11_time = $data->calls_in_ans > 0 ? ceil($data->aux_11_time /$data->calls_in_ans) : $data->aux_11_time;

                $data->avg_call_handling_time = DateHelper::get_formatted_time($data->avg_talk_time + $data->hold_time + $data->aux_11_time);
                $data->avg_talk_time = DateHelper::get_formatted_time($data->avg_talk_time);
                $data->hold_time = DateHelper::get_formatted_time($data->hold_time );
                $data->aux_11_time = DateHelper::get_formatted_time($data->aux_11_time);
                $data->sdate = date($date_format, strtotime($data->sdate));
            }

            /*--------------   Formatting Summery row data   -------------*/
            $final_row->avg_talk_time = DateHelper::get_formatted_time(ceil($final_row->avg_talk_time / $row_count));
            $final_row->hold_time = DateHelper::get_formatted_time(ceil($final_row->hold_time / $row_count));
            $final_row->aux_11_time = DateHelper::get_formatted_time(ceil($final_row->aux_11_time));
            $final_row->avg_call_handling_time = DateHelper::get_formatted_time(ceil($final_row->avg_call_handling_time/$row_count));

            /*-------------- End of Formatting Summery row data   -------------*/

            //$response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    private function addHMS($time_1="00:00:00",$time_2="00:00:00")
    {

    }


    function actionReportDispostionCallSummary()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();

        $dispositions = $report_model->getDispositionTreeOptions();
        $dispositions['*'] = 'All';

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('log_date');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $disposition_id = $this->gridRequest->getMultiParam('disposition_id');
        }



        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numDispositionCall($dateinfo,$disposition_id);

        $data = $this->pagination->num_records > 0 ?
            $report_model->getDispositionCall($dateinfo, $disposition_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $result = !empty($data) ? $data->data : null;
        $total_record = !empty($data) ? $data->total_record : 0;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;



        $final_row = new stdClass();
        $final_row->call_count = 0;

        if(count($result) > 0){

            foreach ( $result as &$data ) {

                /*--------------  Final Row Calculation ----------------*/
                $final_row->call_count += $data->call_count;
                /*--------------End of Final Row Calculation ----------------*/

                $data->log_date = date($date_format, strtotime($data->log_date));
                $data->total_record = $total_record;
                $data->percent = sprintf("%.2f",($data->call_count / $total_record) * 100);
                $data->disposition_id = !empty($dispositions[$data->disposition_id]) ? $dispositions[$data->disposition_id] : $data->disposition_id;
            }
            $final_row->log_date = "-";
            $final_row->disposition_id = "-";

            $response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionDispositionDetails()
    {
        include('model/MReportNew.php');
        include('model/MSkillCrmTemplate.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $template_model = new MSkillCrmTemplate();

//        $dateTimeArray = array();
//        if ($this->gridRequest->isMultisearch){
//            $dateTimeArray = $this->gridRequest->getMultiParam('start_time');
//        }
//        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $tempid = "";
        $did = "";
        $contactNo = "";
        $agent_id = "";
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('start_time');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $date_range['from']), 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $date_range['to']), 'Y-m-d H:i'))) : "23";
            $tempid = $this->gridRequest->getMultiParam('tempid');
            $did = $this->gridRequest->getMultiParam('did');
            $contactNo = $this->gridRequest->getMultiParam('contact_no');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }
//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');
        $this->pagination->num_records = $report_model->numDispositionDetails($tempid, $did, $dateinfo, $contactNo, $agent_id);
        $result = $this->pagination->num_records > 0 ? $report_model->getDispositionDetails($tempid, $did, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $contactNo, $agent_id) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $template_options = $template_model->getTemplateSelectOptions();
        $dp_options = $report_model->getDispositionTreeOptions();
        $dp_options['*'] = 'All';

        if(count($result) > 0){
            $authByOption = array("*"=>"-", "I"=>"IVR", "A"=>"Agent", ""=>"-");
            foreach ( $result as &$data ) {//var_dump($data);die;
                $data->start_time = !empty($data->tstamp) ? date($date_format . " H:i:s", $data->tstamp) : "";
                $data->tempid = !empty($data->template_id) && isset($template_options[$data->template_id]) ? $template_options[$data->template_id] : "";
                $data->did = !empty($data->disposition_id) && isset($dp_options[$data->disposition_id]) ? $dp_options[$data->disposition_id] : "";
                $data->contact_id = $data->contact_no;
                $data->auth_by = !empty($data->caller_auth_by) && isset($authByOption[$data->caller_auth_by]) ? $authByOption[$data->caller_auth_by] : "-";
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAutodialDispostionCall()
    {

        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MSkill.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? $date_range['to'] : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }



        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numAutoDialDispositionCall($dateinfo,$skill_id);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAutoDialDispositionCall($dateinfo, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $skills = $skill_model->getSkillsNamesArray();
            foreach ( $result as &$data ) {
                $data->skill_id = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionDailyCallSummery()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $report_model = new MReportNew();

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $date_to = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }



        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');

        $result = $report_model->getDispositionCall($dateinfo, $skill_id);

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {

            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionCallbackRequests()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MSkill.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+ 30 days"));

        if ($this->gridRequest->isMultisearch){
            $dial_time = $this->gridRequest->getMultiParam('dial_time');
            $date_from = !empty($dial_time['from']) ? $dial_time['from'] : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? $dial_time['to'] : date('Y-m-d');
        }



        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00:00', '23:59:59');
        $this->pagination->num_records = $report_model->numCallbackRequests($dateinfo);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getCallbackRequests($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $skills = $skill_model->getSkillsNamesArray();
            $agents = $agent_model->get_as_key_value();
            foreach ( $result as &$data ) {
                $data->skill_id = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->number_1 = empty($data->number_1) ? "-" : $data->number_1;
                $data->number_2 = empty($data->number_2) ? "-" : $data->number_2;
                $data->number_3 = empty($data->number_3) ? "-" : $data->number_3;
                $data->title = $data->title." ".$data->first_name." ".$data->last_name;
                $data->agent_id = !empty($agents[$data->agent_id]) ? $agents[$data->agent_id] : $data->agent_id;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionHourlyCallStatus()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $res = [];
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $skill_id = "*";

        if ($this->gridRequest->isMultisearch){
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date('Y-m-d');
//            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from']) : date('Y-m-d');
//            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to']) : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00:00', '23:59:59');

        $result = $report_model->getHourlyCallStatus($dateinfo,$skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page);


        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;





        if(count($result) > 0){

            $res[0] = new stdClass();
            $res[0]->hourly_situation = "Avg. Calls Offered";

            $res[1] = new stdClass();
            $res[1]->hourly_situation = "Avg. Calls Answered";

            $res[2] = new stdClass();
            $res[2]->hourly_situation = "Avg. Calls Abandoned";

            for ($i = 0; $i < 3 ; $i++)
            {
                for ($j = 0; $j < 24; $j++)
                {
                    $res[$i]->{"Shour_".sprintf("%02d",$j)} = 0;
                }
            }


            foreach ( $result as $data ) {
                /*     $res[0]->hourly_situation = "Avg. Calls Offered";
                     $res[1]->hourly_situation = "Avg. Calls Answered";
                     $res[2]->hourly_situation = "Avg. Calls Abandoned";*/
                $res[0]->{"Shour_".$data->shour} += $data->call_offered;
                $res[1]->{"Shour_".$data->shour} += $data->call_answered;
                $res[2]->{"Shour_".$data->shour} += $data->call_abandoned;
            }
        }
        $this->pagination->num_records = count($res) > 0 ? count($res) : 0;
        $response->records = $this->pagination->num_records;
        $response->rowdata = $this->fillMissingHour($res, true);
        $this->ShowTableResponse();
    }

    private function fillMissingHour($response, $isRound=false)
    {
        $hours = [];
        for($i=0; $i<24; $i++)
        {
            $i = $i < 10 ? "0".$i : $i;
            $hours["Shour_".$i] = $i;
        }

        $default_value = $isRound ?  "0" : "0.00";

        foreach ($response as &$data)
        {
            $average = 0;
            foreach ($hours as $key => $hour)
            {
                if (empty($data->$key))
                {
                    $data->$key = $default_value;
                }
                $average += $isRound ? round($data->$key) : sprintf("%.2f",$data->$key);
                $data->$key = $isRound ? round($data->$key) : sprintf("%.2f",$data->$key);
            }

            $data->average = $isRound ? round($average/24) : sprintf("%.2f",$average/24);
        }
//print_r($response);
        return $response;
    }

    public function actionHourlyServiceLevelStatus()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('conf.extras.php');

        $final_response = [];
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $hour = "*";
        $skill_id = "*";

        if ($this->gridRequest->isMultisearch){
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date('Y-m-d');
            $hour = $this->gridRequest->getMultiParam('shour');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
        $dateinfo->hour = $hour;
        $this->pagination->num_records = $report_model->numHourlyServiceLevelStatus($dateinfo,$skill_id);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getHourlyServiceLevelStatus($dateinfo,$skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;



        if(count($result) > 0){
            foreach ( $result as $data ) {
                if (!array_key_exists($data->sdate,$final_response)){
                    $final_response[$data->sdate] = new stdClass();
                }
                $final_response[$data->sdate]->sdate = date($date_format, strtotime($data->sdate));

                $service_level = $extra->sl_method == "A" ?
                    ($data->answerd_within_service_level / $data->calls_offered) * 100 :
                    ($data->answerd_within_service_level / ($data->calls_answerd + $data->abandoned_after_threshold)) * 100;

                $final_response[$data->sdate]->{"Shour_".$data->shour} = $service_level; //$data->service_level;
            }
        }

        $response->rowdata = array_values($this->fillMissingHour($final_response));
        //$this->pagination->num_records = count($response->rowdata);
        // $response->records = count($response->rowdata);
        $this->ShowTableResponse();
    }

    public function actionCallVolume()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();

        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");

        if ($this->gridRequest->isMultisearch){
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'],$date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'],$date_format) : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }


        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
        $this->pagination->num_records = $report_model->numCallVolume($dateinfo,$skill_id);


        $result = $this->pagination->num_records > 0 ?
            $report_model->getCallVolume($dateinfo,$skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;


        if(count($result) > 0){
            include('model/MSkill.php');
            $skill_model = new MSkill();
            $skills = $skill_model->getSkillsNamesArray();


            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->calls_offered = 0;
            $final_row->calls_repeated = 0;
            $final_row->calls_answerd = 0;
            $final_row->answerd_within_service_level = 0;
            $final_row->daily_avg_call = 0;
            $final_row->abandoned_within_th = 0;
            $final_row->abandoned_after_threshold = 0;
            $final_row->calls_abandoned = 0;
            $final_row->abandoned_ratio = 0;

            $total_row = 0;
            foreach ( $result as $data ) {
                $data->sdate = date($date_format, strtotime($data->sdate));
                $skill_name = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->skill_name = $skill_name;
                $data->daily_avg_call = $data->agents_worked > 0 ? round($data->calls_answerd/$data->agents_worked) : "0";
                $data->abandoned_within_th = $data->calls_abandoned-$data->abandoned_after_threshold;
                $data->abandoned_ratio = $data->calls_offered > 0 ? sprintf("%0.02f", 100* $data->calls_abandoned / $data->calls_offered) : "0.00";

                /*---------------- Final row Calculation start-----------------*/
                $total_row++;
                $final_row->sdate = "-";
                $final_row->calls_offered += $data->calls_offered;
                $final_row->calls_repeated += $data->calls_repeated;
                $final_row->calls_answerd += $data->calls_answerd;
                $final_row->answerd_within_service_level += $data->answerd_within_service_level;
                $final_row->daily_avg_call += $data->daily_avg_call;
                $final_row->abandoned_within_th += $data->abandoned_within_th;
                $final_row->abandoned_after_threshold += $data->abandoned_after_threshold;
                $final_row->calls_abandoned += $data->calls_abandoned;
                $final_row->abandoned_ratio += $data->abandoned_ratio;
                /*---------------- Final row Calculation start-----------------*/

                /*
                $single_response[$data->sdate]->sdate = $data->sdate;
                $single_response[$data->sdate]->avg_agent_per_hour_call .= $skill_name." : ".round($data->calls_offered /($data->agents_worked *24),2)."\n";
                $single_response[$data->sdate]->calls_offered .= $skill_name." : ".$data->calls_offered."\n";
                $single_response[$data->sdate]->calls_answered .= $skill_name." : ".$data->calls_answerd."\n";
                $single_response[$data->sdate]->calls_repeated .= $skill_name." : ".$data->calls_repeated."\n";
                $single_response[$data->sdate]->calls_abandoned .= $skill_name." : ".$data->calls_abandoned."\n";
                $single_response[$data->sdate]->abandoned_after_threshold .= $skill_name." : ".$data->abandoned_after_threshold."\n";
                $single_response[$data->sdate]->abandoned_ratio .= $skill_name." : ".round((($data->calls_abandoned + $data->abandoned_after_threshold) / $data->calls_offered ) * 100,2)."\n";
                */
            }
            $final_row->skill_name = "-";
            $final_row->daily_avg_call = ( $final_row->daily_avg_call / $total_row);
            $final_row->abandoned_ratio = sprintf("%.2f",( $final_row->abandoned_ratio / $total_row));

            $response->userdata = $final_row;
        }



        //$response->rowdata = array_values($single_response);

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionAgentActivityOutbound()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();

        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $agent_id = "*";

        if ($this->gridRequest->isMultisearch){
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00:00', '23:59:59');
        $this->pagination->num_records = $report_model->numAgentOutboundActivity($dateinfo,$agent_id);


        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentOutboundActivity($dateinfo,$agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;


        if(count($result) > 0)
        {
            $row_count = 0;
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->day = "-";
            $final_row->agent_worked = 0;
            $final_row->daily_avg_initiated = 0;
            $final_row->total_reached = 0;
            $final_row->total_unreached = 0;
            $final_row->total_initiated = 0;

            foreach ( $result as $data ) {
                /*---------------Summery row data calculation start -----------*/
                $row_count++;
                $final_row->agent_worked += $data->agent_worked;
                $final_row->daily_avg_initiated += $data->total_initiated / $data->agent_worked;
                $final_row->total_reached += $data->total_reached;
                $final_row->total_unreached += $data->total_initiated - $data->total_reached;
                $final_row->total_initiated += $data->total_initiated;
                /*---------------Summery row data calculation start -----------*/

                $data->day = date("l",strtotime($data->sdate));
                $data->daily_avg_initiated = sprintf("%.2f",$data->total_initiated / $data->agent_worked);
                $data->total_unreached = $data->total_initiated - $data->total_reached;
                $data->reached_ratio = sprintf("%.2f",($data->total_reached / $data->total_initiated) * 100);
                $data->unreached_ratio = sprintf("%.2f",($data->total_unreached / $data->total_initiated) * 100);
                $data->sdate = date($date_format, strtotime($data->sdate));

            }
            /*---------------Formatting Summery row data calculation start -----------*/
            $final_row->daily_avg_initiated = sprintf("%.2f",$final_row->daily_avg_initiated/$row_count);
            $final_row->reached_ratio = sprintf("%.2f",($final_row->total_reached / $final_row->total_initiated) * 100);
            $final_row->unreached_ratio = (100 - $final_row->reached_ratio);
            /*---------------Formatting Summery row data calculation start -----------*/
            $response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $process_response = [];

        if ($this->gridRequest->isMultisearch){

            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($datetime_range['from']) ? generic_date_format_from_report_datetime($datetime_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? generic_date_format_from_report_datetime($datetime_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";

            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $hourly_abandoned = $this->gridRequest->getMultiParam('hourly_abandoned',"N");
            $sum_date = $this->gridRequest->getMultiParam('sum_date',"N");
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');
//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);

        $this->pagination->num_records = 0;

        $this->pagination->num_records = $report_model->numAbandonedCallReport($sum_date,$skill_id,$dateinfo);
        $this->pagination->num_records = $report_model->numAbandonedCallReport($sum_date,$skill_id,$dateinfo);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAbandonedCallReport($sum_date,$skill_id, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $final_row = new stdClass();
            $final_row->calls_offered = 0;
            $final_row->calls_abandoned = 0;
            $row_count = 0;


            foreach ( $result as &$data ) {
                $data->sdate = empty($data->sdate) ? 0 : date($date_format, strtotime($data->sdate));
                if (empty($process_response[$data->sdate]) || !array_key_exists($data->skill_id,$process_response[$data->sdate])){

                    $process_response[$data->sdate][$data->skill_id] = new stdClass();
                    $process_response[$data->sdate][$data->skill_id]->calls_offered = 0;
                    $process_response[$data->sdate][$data->skill_id]->calls_abandoned = 0;
                    $process_response[$data->sdate][$data->skill_id]->abandoned_percentage = 0;
                }
                $process_response[$data->sdate][$data->skill_id]->sdate = $data->sdate;
                $process_response[$data->sdate][$data->skill_id]->skill_id = $data->skill_id;
                $process_response[$data->sdate][$data->skill_id]->skill_name = $data->skill_name;
                $process_response[$data->sdate][$data->skill_id]->{"Shour_".$data->shour} = $hourly_abandoned == "P" ? ($data->calls_offered > 0 ? ($data->calls_abandoned / $data->calls_offered) * 100 : 0) : $data->calls_abandoned;
                $process_response[$data->sdate][$data->skill_id]->calls_offered += $data->calls_offered;
                $process_response[$data->sdate][$data->skill_id]->calls_abandoned += $data->calls_abandoned;
                $process_response[$data->sdate][$data->skill_id]->abandoned_percentage = $process_response[$data->sdate][$data->skill_id]->calls_offered > 0 ? sprintf("%.02f",($process_response[$data->sdate][$data->skill_id]->calls_abandoned / $process_response[$data->sdate][$data->skill_id]->calls_offered) * 100) : "0.00";
                $row_count++;
                $data->ratio = $data->calls_abandoned."/".$data->calls_offered;
                $data->abandoned_percentage = !empty($data->abandoned_percentage) ? round($data->abandoned_percentage,2) : "0.00";

                /*------------------ Final Row Data Calculation---------------*/
                $final_row->calls_offered += $data->calls_offered;
                $final_row->calls_abandoned += $data->calls_abandoned;
            }

            $final_row->sdate = "-";
            $final_row->skill_name = "-";
            $final_row->ratio = $final_row->calls_abandoned."/".$final_row->calls_offered;
            $final_row->abandoned_percentage = round(($final_row->calls_abandoned/$final_row->calls_offered) *100,2);
            $final_row->abandoned_percentage = !empty($final_row->abandoned_percentage) ? $final_row->abandoned_percentage : "0.00";

            //$result[] = $final_row;
            $response->userdata = $final_row;
        }
        $s = [];

        foreach ($process_response as $key => $item)
        {
            foreach ($item as $sKey => $sValue){
                $s[] = $sValue;
            }

        }

        $s = $this->fillMissingHour($s);


        $response->rowdata = $s;
        // $response->rowdata = $result;

        $this->ShowTableResponse();
    }

    public function actionDailyAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['to']),'Y-m-d H:i'))) : "23";
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }

        $this->pagination->num_records = $report_model->numDailyAbandonedCallReport($skill_id,$dateinfo);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getDailyAbandonedCallReport($skill_id, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $final_row = new stdClass();
            $final_row->calls_offered = 0;
            $final_row->calls_abandoned = 0;
            $row_count = 0;

            foreach ( $result as &$data ) {
                $row_count++;
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->ratio = $data->calls_abandoned."/".$data->calls_offered;
                $data->abandoned_percentage = !empty($data->abandoned_percentage) ? round($data->abandoned_percentage,2) : "0.00";

                /*------------------ Final Row Data Calculation---------------*/
                $final_row->calls_offered += $data->calls_offered;
                $final_row->calls_abandoned += $data->calls_abandoned;
            }

            $final_row->sdate = "-";
            $final_row->skill_name = "-";
            $final_row->ratio = $final_row->calls_abandoned."/".$final_row->calls_offered;
            $final_row->abandoned_percentage = round(($final_row->calls_abandoned/$final_row->calls_offered) *100,2);
            $final_row->abandoned_percentage = !empty($final_row->abandoned_percentage) ? $final_row->abandoned_percentage : "0.00";

            //$result[] = $final_row;
            $response->userdata = $final_row;
        }

        $response->rowdata = $result;

        $this->ShowTableResponse();
    }

    public function actionHourlyAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $result_object = [];


        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? $date_range['to'] : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');



        $result = $report_model->getHourlyAbandonedCallReport($skill_id, $dateinfo);

        $this->pagination->num_records = 0;//count($result);

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){

            $this->pagination->num_records = 1;

            foreach ( $result as &$data ) {

                if (!array_key_exists(0,$result_object))
                {
                    $result_object[0] = new stdClass();
                }

                $result_object[0]->{"Shour_".$data->shour} = $data->calls_offered > 0 ? ($data->calls_abandoned / $data->calls_offered) * 100 : 0.00;

            }
        }

        $response->rowdata = $this->fillMissingHour(array_values($result_object));

        $this->ShowTableResponse();
    }

    public function actionAverageHandlingTime()
    {
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();
        $report_model = new MReportNew();

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

//        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
        $this->pagination->num_records = $report_model->numAverageHandlingTime($skill_id,$dateinfo);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAverageHandlingTime($skill_id, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            $skills = $skill_model->getSkillsNamesArray();
            $final_row = new stdClass();
            $final_row->sdate = "<b>-</b>";
            $final_row->skill_id = "<b>-</b>";
            $final_row->service_duration = 0;
            $final_row->hold_time = 0;
            $final_row->acw_time = 0;
            $final_row->calls_answerd = 0;

            foreach ( $result as &$data ) {
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->skill_id = !empty($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->average_handling_time = DateHelper::get_formatted_time($data->calls_answerd > 0 ? ceil(($data->service_duration+$data->hold_time+$data->acw_time) / $data->calls_answerd) : 0);//round(($data->service_duration+$data->hold_time+$data->acw_time) / $data->calls_answerd,2);

                /*-----------------------Summery Row Calculation-------------------*/
                $final_row->service_duration += $data->service_duration;
                $final_row->hold_time += $data->hold_time;
                $final_row->acw_time += $data->acw_time;
                $final_row->calls_answerd += $data->calls_answerd;
            }

            $final_row->average_handling_time = DateHelper::get_formatted_time(ceil(($final_row->service_duration+$final_row->hold_time+$final_row->acw_time) / $final_row->calls_answerd));

            $response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }


    public function actionAgentCallHistory()
    {

        AddModel('MReportNew');
        include('lib/DateHelper.php');


        $report_model = new MReportNew();

        $agent_id = UserAuth::getCurrentUser();
        $date_from = date("Y-m-d",strtotime("-15 days"));
        $date_to = date("Y-m-d");

        if ($this->gridRequest->isMultisearch){
            $duration = $this->gridRequest->getMultiParam('duration', 15);
            $date_from = date("Y-m-d",strtotime("-". $duration." days"));
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');

        $this->pagination->num_records = $report_model->numAgentCallHistory($agent_id, $dateinfo);

        $result = $this->pagination->num_records > 0 ? $report_model->getAgentCallHistory($agent_id, $dateinfo) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->calls_in_count = $data->ring_lt_6_count + $data->ring_6_to_10_count + $data->ring_gt_11_count;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportCategorySummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            // $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);
        $skill_options = $skill_model->getSkillsNamesArray();

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }

        // calculate total count
        // var_dump($this->pagination->rows_per_page);
        $skill_categories = $skill_category_model->getSkillCategories('', '', 'A');
        foreach ($skill_categories as $key => $item) {
            $skill_ids = explode(',', $item->skill_ids);
            $skill_ids = implode("','", $skill_ids);

            if(empty($category)){
                $cat_total = $report_model->numCategorySummaryReport($dateinfo, $skill_ids, $report_type);
                // var_dump($cat_total);
                $this->pagination->num_records += $cat_total;
            }elseif (!empty($category) && $item->cat_id == $category){
                $this->pagination->num_records = $report_model->numCategorySummaryReport($dateinfo, $skill_ids, $report_type);
                $category_skill_ids = $skill_ids;
                $skill_categories = [];
                $skill_categories[] = $item;
                break;
            }
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $original_result = [];
        $grid_row_count = 0;
        $page_count = 1;
        // $half_hour_count = 0;
        // $half_hour_first_data = [];
        // var_dump($this->pagination->num_records);

        // var_dump(date(REPORT_DATE_FORMAT, strtotime('2018-05-31')));
        // die();

        if($this->pagination->num_records > 0){
            $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
            // last row initialization
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->shour = "-";
            $final_row->sminute = "-";
            $final_row->category = "-";
            $final_row->skill_id = "-";
            $final_row->skill_name = "-";
            $final_row->smonth = "-";
            $final_row->quarter_no = "-";
            $final_row->hour_minute = "-";
            $final_row->rgb_call_count = 0;
            $final_row->forecasted_calls = 0;
            $final_row->calls_offered = 0;
            $final_row->calls_answerd = 0;
            $final_row->calls_abandoned = 0;
            $final_row->ans_lte_10_count = 0;
            $final_row->ans_lte_20_count = 0;
            $final_row->ans_lte_30_count = 0;
            $final_row->ans_lte_60_count = 0;
            $final_row->ans_lte_90_count = 0;
            $final_row->ans_lte_120_count = 0;
            $final_row->ans_gt_120_count = 0;
            $final_row->abd_lte_10_count = 0;
            $final_row->abd_lte_20_count = 0;
            $final_row->abd_lte_30_count = 0;
            $final_row->abd_lte_60_count = 0;
            $final_row->abd_lte_90_count = 0;
            $final_row->abd_lte_120_count = 0;
            $final_row->abd_gt_120_count = 0;
            $final_row->avg_handling_time = 0;
            $final_row->service_duration = 0;
            $final_row->ring_time = 0;
            $final_row->agent_hold_time = 0;
            $final_row->wrap_up_time = 0;
            $final_row->avg_wrap_up_time = '0.00%';
            $final_row->asa = '0.00%';
            $final_row->service_level_lte_10_count = 0;
            $final_row->service_level_lte_20_count = 0;
            $final_row->service_level_lte_30_count = 0;
            $final_row->service_level_lte_60_count = 0;
            $final_row->service_level_lte_90_count = 0;
            $final_row->service_level_lte_120_count = 0;
            $final_row->abandoned_ratio_10 = '0.00%';
            $final_row->abandoned_ratio_20 = '0.00%';
            $final_row->abandoned_ratio_30 = '0.00%';
            $final_row->abandoned_ratio_60 = '0.00%';
            $final_row->abandoned_ratio_90 = '0.00%';
            $final_row->abandoned_ratio_120 = '0.00%';
            $final_row->fcr_call_percentage = '0.00%';
            $final_row->forecasted_calls = 0;
            $final_row->short_call_count = 0;
            $final_row->short_call_percentage = '0.00%';
            $final_row->wrap_up_call_count = 0;
            $final_row->wrap_up_percentage = '0.00%';
            $final_row->unique_caller = 0;
            $final_row->unique_caller_percentage = '0.00%';
            $final_row->repeat_call_percentage = '0.00%';
            $final_row->agent_hangup_count = 0;
            $final_row->agent_hangup_percentage = '0.00%';
            $final_row->cpc = 0;
            $final_row->fcr_call_count = 0;
            $final_row->repeat_1_count = 0;

            while (true) {
                if(empty($category)){
                    $category_result = $report_model->getCategorySummaryReport($dateinfo, '', $report_type, $offset, $this->pagination->rows_per_page);
                }elseif (!empty($category) && $item->cat_id == $category){
                    $category_result = $report_model->getCategorySummaryReport($dateinfo, $category_skill_ids, $report_type, $offset, $this->pagination->rows_per_page);
                }

                if(count($category_result) > 0){
                    // var_dump($category_result);
                    if($report_type == REPORT_HALF_HOURLY){
                        $tmp_category_result = [];
                        /*
                         * half hourly calculation
                         *
                        */
                        foreach ($category_result as $key => $data) {
                            if($data->sminute == '00' || $data->sminute == '15'){
                                $idx = $data->sdate.'##'.$data->shour."##00##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '00';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '00';
                                }
                            }elseif($data->sminute == '30' || $data->sminute == '45'){
                                $idx = $data->sdate.'##'.$data->shour."##30##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '30';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '30';
                                }
                            }
                        }
                        $category_result = $tmp_category_result;
                        // var_dump($tmp_category_result);
                    }
                    // die();
                    foreach ($category_result as $key => $data) {
                        foreach ($skill_categories as $key => $item) {
                            $skill_id_arr = explode(',', $item->skill_ids);
                            $skill_id_str = implode("','", $skill_id_arr);

                            if(in_array($data->skill_id, $skill_id_arr)){
                                // skip previous page item
                                if($response->page > 1){
                                    if($grid_row_count < (($response->page*$this->pagination->rows_per_page) - $this->pagination->rows_per_page)){
                                        $grid_row_count++;
                                        continue;
                                    }
                                }
                                // pr($skill_id_arr);
                                // pr($grid_row_count);
                                // if($report_type != REPORT_HALF_HOURLY){
                                // $data->half_hour = '';
                                // }

                                if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                                    $data->hour_minute = $data->shour.':'.$data->sminute;
                                    $data->half_hour = '';
                                }else if($report_type == REPORT_HALF_HOURLY){
                                    $data->hour_minute = $data->shour.':'.$data->half_hour;
                                }else{
                                    $data->hour_minute = '';
                                    $data->half_hour = '';
                                }

                                $data->category = $item->name;
                                $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                                $data->skill_name = isset($skill_options[$data->skill_id]) ? ' ' . $skill_options[$data->skill_id] : ' ' . $data->skill_id;
                                $data->avg_handling_time = (!empty($data->calls_answerd)) ? ceil(($data->ring_time+$data->service_duration+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                                $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->asa = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->ring_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? ceil($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)) : 0;
                                $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? ceil($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)) : 0;
                                $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? ceil($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)) : 0;
                                $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? ceil($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)) : 0;
                                $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? ceil($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)) : 0;
                                $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? ceil($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)) : 0;
                                $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_10_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_20_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_30_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_60_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_90_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_120_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->fcr_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->fcr_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->short_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->short_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->unique_caller = $data->calls_answerd-$data->repeat_1_count;
                                $data->unique_caller_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->unique_caller / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->repeat_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->repeat_1_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->agent_hangup_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->cpc = (!empty($data->rgb_call_count)) ? ceil($data->calls_offered / $data->rgb_call_count) : 0;

                                // footer data
                                $final_row->rgb_call_count += $data->rgb_call_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->calls_offered += $data->calls_offered;
                                $final_row->calls_answerd += $data->calls_answerd;
                                $final_row->calls_abandoned += $data->calls_abandoned;
                                $final_row->ans_lte_10_count += $data->ans_lte_10_count;
                                $final_row->ans_lte_20_count += $data->ans_lte_20_count;
                                $final_row->ans_lte_30_count += $data->ans_lte_30_count;
                                $final_row->ans_lte_60_count += $data->ans_lte_60_count;
                                $final_row->ans_lte_90_count += $data->ans_lte_90_count;
                                $final_row->ans_lte_120_count += $data->ans_lte_120_count;
                                $final_row->ans_gt_120_count += $data->ans_gt_120_count;
                                $final_row->abd_lte_10_count += $data->abd_lte_10_count;
                                $final_row->abd_lte_20_count += $data->abd_lte_20_count;
                                $final_row->abd_lte_30_count += $data->abd_lte_30_count;
                                $final_row->abd_lte_60_count += $data->abd_lte_60_count;
                                $final_row->abd_lte_90_count += $data->abd_lte_90_count;
                                $final_row->abd_lte_120_count += $data->abd_lte_120_count;
                                $final_row->abd_gt_120_count += $data->abd_gt_120_count;
                                $final_row->avg_handling_time += $data->avg_handling_time;
                                $final_row->service_duration += $data->service_duration;
                                $final_row->ring_time += $data->ring_time;
                                $final_row->agent_hold_time += $data->agent_hold_time;
                                $final_row->wrap_up_time += $data->wrap_up_time;
                                $final_row->service_level_lte_10_count += $data->service_level_lte_10_count;
                                $final_row->service_level_lte_20_count += $data->service_level_lte_20_count;
                                $final_row->service_level_lte_30_count += $data->service_level_lte_30_count;
                                $final_row->service_level_lte_60_count += $data->service_level_lte_60_count;
                                $final_row->service_level_lte_90_count += $data->service_level_lte_90_count;
                                $final_row->service_level_lte_120_count += $data->service_level_lte_120_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->short_call_count += $data->short_call_count;
                                $final_row->wrap_up_call_count += $data->wrap_up_call_count;
                                $final_row->unique_caller += $data->unique_caller;
                                $final_row->agent_hangup_count += $data->agent_hangup_count;
                                $final_row->cpc += $data->cpc;
                                $final_row->fcr_call_count += $data->fcr_call_count;
                                $final_row->repeat_1_count += $data->repeat_1_count;

                                $original_result[] = (array)$data;
                                $original_result[count($original_result)-1]['sdate'] = date(REPORT_DATE_FORMAT, strtotime($data->sdate));

                                $grid_row_count++;
                            }


                            if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                                break;
                            }
                        }
                        if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                            break;
                        }
                    }
                    if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                        break;
                    }

                    $page_count++;
                    $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
                }else{
                    break;
                }
            }

            $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->asa = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->ring_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_10_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_20_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_30_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_60_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_90_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_120_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->fcr_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->fcr_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->short_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->unique_caller_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->unique_caller / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->repeat_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->repeat_1_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->agent_hangup_count / $final_row->calls_answerd) * 100).'%' : '0.00%';

            $response->userdata = $final_row;

        }

        // var_dump($this->pagination->num_records);
        // var_dump($page_count);
        // var_dump($offset);
        // var_dump($grid_row_count);
        // var_dump($original_result);
        // die();

        if($report_type == REPORT_YEARLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['syear'];
        }elseif($report_type == REPORT_QUARTERLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
            $response->showCol = ['quarter_no'];
        }elseif($report_type == REPORT_MONTHLY){
            $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['smonth'];
        }elseif($report_type == REPORT_DAILY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate'];
        }else if($report_type == REPORT_HOURLY){
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate', 'shour'];
        }else if($report_type == REPORT_HALF_HOURLY){
            $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
            $response->showCol = ['sdate', 'hour_minute'];
        }else{
            $response->showCol = ['sdate', 'hour_minute'];
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
        }

        $response->rowdata = $original_result;
        // var_dump($response->rowdata);
        $this->ShowTableResponse();
    }

    function actionReportCategoryDetails(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $disposition_type_list = disposition_type_list();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $hangup_initiator = $this->gridRequest->getMultiParam('hangup_initiator');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $disposition_id = $this->gridRequest->getMultiParam('dispositions_ids');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

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
            $dispositions =  $report_model->get_disposition_all_value();            

            // calculate total count
            $query_param = [
                "dateinfo" => $dateinfo,
                "skill_ids" => $skill_ids,
                // "report_type" => $report_type,
                "skill_type" => $skill_type,
                "hangup_initiator" => $hangup_initiator,
                "disposition_id" => $disposition_id,
                "isSum" => false,
                "multiple_wrap_up" => false,
            ];
            $this->pagination->num_records = $report_model->numCategoriesDetailsReport($query_param);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            // $request_param = [
            //     'sdate' => $datetime_range['from'],
            //     'edate' => $datetime_range['to'],
            //     'skill_type' => $skill_type,
            //     'hangup_initiator' => $hangup_initiator,
            //     'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            // ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Skill Details', $this->gridRequest->getMultiParam());

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
                $report_model->saveReportAuditRequest('NRS::Skill Details', $this->gridRequest->getMultiParam());
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $query_param["isSum"] = false;
                    $query_param["multiple_wrap_up"] = false;
                    $result = $report_model->getCategoriesDetailsReport($query_param, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $query_param["isSum"] = false;
                    $query_param["multiple_wrap_up"] = false;
                    $result = $this->pagination->num_records > 0 ? $report_model->getCategoriesDetailsReport($query_param, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
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
                        // $data->delay_between_call = ($data->status == 'S') ? DELAY_BETWEEN_CALLS : 0;
                        // $data->title = '';
                        // $data->wrap_up_time = ($this->gridRequest->isDownloadCSV ? '' : '-');
                        // $data->disposition_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                        // $data->title = $dispositions[$data->disposition_id]; //($this->gridRequest->isDownloadCSV ? '' : '-');
                        // $data->qrc_type_tagging = ($this->gridRequest->isDownloadCSV ? '' : '-');

                        if($this->gridRequest->isDownloadCSV){ // for download
                            // $data->custom_title = $dispositions[$data->disposition_id];
                            // $data->custom_title = $dispositions[$data->disposition_id]['title'];
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
                        $query_param["isSum"] = true;
                        $query_param["multiple_wrap_up"] = false;
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getCategoriesDetailsReport($query_param, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
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
                $multiple_disposition = count($callIdArr) > 0 ? $report_model->getMultipleDisposition($callIdStr) : null;
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
    function actionReportDailySnapShot()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? $date_range['from'] : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? $date_range['to'] : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            // $shift_code = $this->gridRequest->getMultiParam('shift_code');
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, '00:00', '23:59');

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }
        $this->pagination->num_records = $report_model->numDailySnapShotReport($dateinfo);
        $result = $this->pagination->num_records > 0 ? $report_model->getDailySnapShotReport($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            $row_count = 0;
            $final_row = new stdClass();
            $final_row->sdate = "-";
            foreach ( $result as &$data ){

                $row_count++;
            }
            $response->userdata = $final_row;
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }
    function actionReportOutboundSummary () {
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        $skill_type = "O";
        $fraction_format = "%.2f";

        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days);

        if(empty($dateInfo->errMsg)){
            $skills_types = $skill_model->getAllSkillsTypeArray();
            // Gprint($skills_types);

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }
            // Gprint($skill_ids);

            $this->pagination->num_records = $report_model->numOutboundSummary($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_ids);
            $result = $this->pagination->num_records > 0 ? $report_model->getOutboundSummary($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_ids, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $final_result = array();
            if(count($result) > 0) {
                foreach ($result as  &$data) {
                    if($data->agent_id != '' && $data->skill_id != ''){
                        $data->sdate = date_format(date_create_from_format('Y-m-d', $data->sdate), $date_format);
                        $data->agent_name = $agent_model->getAgentById($data->agent_id)->name;
                        $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                        if($this->gridRequest->isDownloadCSV)
                            $data->aht = (!empty($data->success_call)) ? round(($data->ring_time+$data->talk_time+$data->hold_time+$data->wrap_up_time)/$data->success_call, 2) : 0;
                        else
                            $data->aht = (!empty($data->success_call)) ? round(($data->ring_time+$data->talk_time+$data->hold_time+$data->wrap_up_time)/$data->success_call) : 0;

                        $final_result [] = $data;
                    }
                }
            }

            if(!$this->gridRequest->isDownloadCSV){
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getOutboundSummary($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_ids, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->sdate = "-";
                $final_row->skill_id = "-";
                $final_row->agent_id = "-";
                $final_row->agent_name = "-";

                if($this->gridRequest->isDownloadCSV)
                    $final_row->aht = (!empty($final_row->success_call)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->hold_time+$final_row->wrap_up_time)/$final_row->success_call, 2) : 0;
                else
                    $final_row->aht = (!empty($final_row->success_call)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->hold_time+$final_row->wrap_up_time)/$final_row->success_call) : 0;


            }
            $response->userdata = $final_row;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $final_result;
            $this->ShowTableResponse();
        }
    }

    function actionReportSkillSetSummaryWithCategory()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            // $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);
        $skill_options = $skill_model->getSkillsNamesArray();

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }

        // calculate total count
        // var_dump($this->pagination->rows_per_page);
        $skill_categories = $skill_category_model->getSkillCategories('', '', 'A');
        foreach ($skill_categories as $key => $item) {
            $skill_ids = explode(',', $item->skill_ids);
            $skill_ids = implode("','", $skill_ids);

            if(empty($category)){
                $cat_total = $report_model->numSkillSetSummaryWithCategoryReport($dateinfo, $skill_ids, $report_type);
                // var_dump($cat_total);
                $this->pagination->num_records += $cat_total;
            }elseif (!empty($category) && $item->cat_id == $category){
                $this->pagination->num_records = $report_model->getSkillSetSummaryWithCategoryReport($dateinfo, $skill_ids, $report_type);
                $category_skill_ids = $skill_ids;
                $skill_categories = [];
                $skill_categories[] = $item;
                break;
            }
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $original_result = [];
        $grid_row_count = 0;
        $page_count = 1;
        // $half_hour_count = 0;
        // $half_hour_first_data = [];

        if($this->pagination->num_records > 0){
            $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
            // last row initialization
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->shour = "-";
            $final_row->sminute = "-";
            $final_row->category = "-";
            $final_row->skill_id = "-";
            $final_row->skill_name = "-";
            $final_row->smonth = "-";
            $final_row->quarter_no = "-";
            $final_row->rgb_call_count = 0;
            $final_row->forecasted_calls = 0;
            $final_row->calls_offered = 0;
            $final_row->calls_answerd = 0;
            $final_row->calls_abandoned = 0;
            $final_row->ans_lte_10_count = 0;
            $final_row->ans_lte_20_count = 0;
            $final_row->ans_lte_30_count = 0;
            $final_row->ans_lte_60_count = 0;
            $final_row->ans_lte_90_count = 0;
            $final_row->ans_lte_120_count = 0;
            $final_row->ans_gt_120_count = 0;
            $final_row->abd_lte_10_count = 0;
            $final_row->abd_lte_20_count = 0;
            $final_row->abd_lte_30_count = 0;
            $final_row->abd_lte_60_count = 0;
            $final_row->abd_lte_90_count = 0;
            $final_row->abd_lte_120_count = 0;
            $final_row->abd_gt_120_count = 0;
            $final_row->avg_handling_time = 0;
            $final_row->service_duration = 0;
            $final_row->ring_time = 0;
            $final_row->agent_hold_time = 0;
            $final_row->wrap_up_time = 0;
            $final_row->avg_wrap_up_time = '0.00%';
            $final_row->asa = '0.00%';
            $final_row->service_level_lte_10_count = 0;
            $final_row->service_level_lte_20_count = 0;
            $final_row->service_level_lte_30_count = 0;
            $final_row->service_level_lte_60_count = 0;
            $final_row->service_level_lte_90_count = 0;
            $final_row->service_level_lte_120_count = 0;
            $final_row->abandoned_ratio_10 = '0.00%';
            $final_row->abandoned_ratio_20 = '0.00%';
            $final_row->abandoned_ratio_30 = '0.00%';
            $final_row->abandoned_ratio_60 = '0.00%';
            $final_row->abandoned_ratio_90 = '0.00%';
            $final_row->abandoned_ratio_120 = '0.00%';
            $final_row->fcr_call_percentage = '0.00%';
            $final_row->forecasted_calls = 0;
            $final_row->short_call_count = 0;
            $final_row->short_call_percentage = '0.00%';
            $final_row->wrap_up_call_count = 0;
            $final_row->wrap_up_percentage = '0.00%';
            $final_row->unique_caller = 0;
            $final_row->unique_caller_percentage = '0.00%';
            $final_row->repeat_call_percentage = '0.00%';
            $final_row->agent_hangup_count = 0;
            $final_row->agent_hangup_percentage = '0.00%';
            $final_row->cpc = 0;
            $final_row->fcr_call_count = 0;
            $final_row->repeat_1_count = 0;

            while (true) {
                if(empty($category)){
                    $category_result = $report_model->getCategorySummaryReport($dateinfo, '', $report_type, $offset, $this->pagination->rows_per_page);
                }elseif (!empty($category) && $item->cat_id == $category){
                    $category_result = $report_model->getCategorySummaryReport($dateinfo, $category_skill_ids, $report_type, $offset, $this->pagination->rows_per_page);
                }

                if(count($category_result) > 0){
                    // var_dump($category_result);
                    if($report_type == REPORT_HALF_HOURLY){
                        $tmp_category_result = [];
                        /*
                         * half hourly calculation
                         *
                        */
                        foreach ($category_result as $key => $data) {
                            if($data->sminute == '00' || $data->sminute == '15'){
                                $idx = $data->sdate.'##'.$data->shour."##00##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '00';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '00';
                                }
                            }elseif($data->sminute == '30' || $data->sminute == '45'){
                                $idx = $data->sdate.'##'.$data->shour."##30##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '30';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '30';
                                }
                            }
                        }
                        $category_result = $tmp_category_result;
                        // var_dump($tmp_category_result);
                    }
                    // die();
                    foreach ($category_result as $key => $data) {
                        foreach ($skill_categories as $key => $item) {
                            $skill_id_arr = explode(',', $item->skill_ids);
                            $skill_id_str = implode("','", $skill_id_arr);

                            if(in_array($data->skill_id, $skill_id_arr)){
                                // skip previous page item
                                if($response->page > 1){
                                    if($grid_row_count < (($response->page*$this->pagination->rows_per_page) - $this->pagination->rows_per_page)){
                                        $grid_row_count++;
                                        continue;
                                    }
                                }
                                // pr($skill_id_arr);
                                // pr($grid_row_count);
                                // if($report_type != REPORT_HALF_HOURLY){
                                // $data->half_hour = '';
                                // }

                                if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                                    $data->hour_minute = $data->shour.':'.$data->sminute;
                                    $data->half_hour = '';
                                }else if($report_type == REPORT_HALF_HOURLY){
                                    $data->hour_minute = $data->shour.':'.$data->half_hour;
                                }else{
                                    $data->hour_minute = '';
                                    $data->half_hour = '';
                                }

                                $data->category = $item->name;
                                $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                                $data->skill_name = isset($skill_options[$data->skill_id]) ? ' ' . $skill_options[$data->skill_id] : ' ' . $data->skill_id;
                                $data->avg_handling_time = (!empty($data->calls_answerd)) ? ceil(($data->ring_time+$data->service_duration+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                                $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->asa = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->ring_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? ceil($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)) : 0;
                                $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? ceil($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)) : 0;
                                $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? ceil($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)) : 0;
                                $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? ceil($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)) : 0;
                                $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? ceil($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)) : 0;
                                $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? ceil($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)) : 0;
                                $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_10_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_20_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_30_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_60_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_90_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_120_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->fcr_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->fcr_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->short_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->short_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->unique_caller = $data->calls_answerd-$data->repeat_1_count;
                                $data->unique_caller_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->unique_caller / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->repeat_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->repeat_1_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->agent_hangup_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->cpc = (!empty($data->rgb_call_count)) ? ceil($data->calls_offered / $data->rgb_call_count) : 0;

                                // footer data
                                $final_row->rgb_call_count += $data->rgb_call_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->calls_offered += $data->calls_offered;
                                $final_row->calls_answerd += $data->calls_answerd;
                                $final_row->calls_abandoned += $data->calls_abandoned;
                                $final_row->ans_lte_10_count += $data->ans_lte_10_count;
                                $final_row->ans_lte_20_count += $data->ans_lte_20_count;
                                $final_row->ans_lte_30_count += $data->ans_lte_30_count;
                                $final_row->ans_lte_60_count += $data->ans_lte_60_count;
                                $final_row->ans_lte_90_count += $data->ans_lte_90_count;
                                $final_row->ans_lte_120_count += $data->ans_lte_120_count;
                                $final_row->ans_gt_120_count += $data->ans_gt_120_count;
                                $final_row->abd_lte_10_count += $data->abd_lte_10_count;
                                $final_row->abd_lte_20_count += $data->abd_lte_20_count;
                                $final_row->abd_lte_30_count += $data->abd_lte_30_count;
                                $final_row->abd_lte_60_count += $data->abd_lte_60_count;
                                $final_row->abd_lte_90_count += $data->abd_lte_90_count;
                                $final_row->abd_lte_120_count += $data->abd_lte_120_count;
                                $final_row->abd_gt_120_count += $data->abd_gt_120_count;
                                $final_row->avg_handling_time += $data->avg_handling_time;
                                $final_row->service_duration += $data->service_duration;
                                $final_row->ring_time += $data->ring_time;
                                $final_row->agent_hold_time += $data->agent_hold_time;
                                $final_row->wrap_up_time += $data->wrap_up_time;
                                $final_row->service_level_lte_10_count += $data->service_level_lte_10_count;
                                $final_row->service_level_lte_20_count += $data->service_level_lte_20_count;
                                $final_row->service_level_lte_30_count += $data->service_level_lte_30_count;
                                $final_row->service_level_lte_60_count += $data->service_level_lte_60_count;
                                $final_row->service_level_lte_90_count += $data->service_level_lte_90_count;
                                $final_row->service_level_lte_120_count += $data->service_level_lte_120_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->short_call_count += $data->short_call_count;
                                $final_row->wrap_up_call_count += $data->wrap_up_call_count;
                                $final_row->unique_caller += $data->unique_caller;
                                $final_row->agent_hangup_count += $data->agent_hangup_count;
                                $final_row->cpc += $data->cpc;
                                $final_row->fcr_call_count += $data->fcr_call_count;
                                $final_row->repeat_1_count += $data->repeat_1_count;

                                $original_result[] = (array)$data;
                                $original_result[count($original_result)-1]['sdate'] = date(REPORT_DATE_FORMAT, strtotime($data->sdate));

                                $grid_row_count++;
                            }

                            if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                                break;
                            }
                        }
                        if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                            break;
                        }
                    }
                    if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                        break;
                    }

                    $page_count++;
                    $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
                }else{
                    break;
                }
            }

            $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->asa = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->ring_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_10_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_20_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_30_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_60_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_90_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_120_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->fcr_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->fcr_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->short_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->unique_caller_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->unique_caller / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->repeat_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->repeat_1_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->agent_hangup_count / $final_row->calls_answerd) * 100).'%' : '0.00%';

            $response->userdata = $final_row;

        }

        // var_dump($this->pagination->num_records);
        // var_dump($page_count);
        // var_dump($offset);
        // var_dump($grid_row_count);
        // var_dump($original_result);
        // die();

        if($report_type == REPORT_YEARLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['syear'];
        }elseif($report_type == REPORT_QUARTERLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
            $response->showCol = ['quarter_no'];
        }elseif($report_type == REPORT_MONTHLY){
            $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['smonth'];
        }elseif($report_type == REPORT_DAILY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate'];
        }else if($report_type == REPORT_HOURLY){
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate', 'shour'];
        }else if($report_type == REPORT_HALF_HOURLY){
            $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
            $response->showCol = ['sdate', 'hour_minute'];
        }else{
            $response->showCol = ['sdate', 'hour_minute'];
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
        }

        $response->rowdata = $original_result;
        $this->ShowTableResponse();
    }
    function actionReportMsisdn(){
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range= $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($datetime_range['from']) ? generic_date_format_from_report_datetime($datetime_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? generic_date_format_from_report_datetime($datetime_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = implode("','", $skills_types[$skill_type]);
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
            $original_result = [];  // use grid view
            $hideCol=[];

            // calculate total count
            $this->pagination->num_records = $report_model->numMsisdnReport($skill_ids,$dateInfo, $skill_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            //download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::MSISDN', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if (!empty($hideCol)) {
                    foreach ($hideCol as $key => $value) {
                        unset($cols->$value);
                    }
                }
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                        array_push($csv_titles,$value);
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }
            }else{
                $report_model->saveReportAuditRequest('NRS::MSISDN', $request_param);
            }

            // $result = $this->pagination->num_records > 0 ? $report_model->getMsisdnReport($skill_ids,$dateInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getMsisdnReport($skill_ids,$dateInfo,  $dbResultOffset, $dbResultRow, $skill_type);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getMsisdnReport($skill_ids,$dateInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $skill_type) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download
                    foreach ( $result as &$data )
                    {
                        $data->sdate = date_format(date_create_from_format('Y-m-d H:i:s', $data->sdate), $date_format . ' H:i:s');
                        if(strlen($data->msisdn_880) == 10){
                            $data->msisdn_880 = "880". $data->msisdn_880;
                        }elseif (strlen($data->msisdn_880) == 11){
                            $data->msisdn_880 = "88" . $data->msisdn_880;
                        }

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                            // var_dump($row);
                            $fileInputRowCount++;
                        }
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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }
    function convertDate($date, $newFormat="Y-m-d H:i:s")
    {
        return str_replace("/","-",$date);
        // $newdate = DateTime::createFromFormat(REPORT_DATE_FORMAT." H:i:s", $date);
        //return $newdate->format($newFormat);
    }
    public function actionWorkcodeCountReport()
    {

        AddModel('MReportNew');
        include('model/MSkill.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $formatedData = [];

        $date_format = get_report_date_format();
        $sdate = date($date_format." H:i");
        $edate = date($date_format." H:i");
        $skill_id = '*';
        $interval = 'daily';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = $this->gridRequest->getMultiParam('date_format', $date_format);
            $date_range = $this->gridRequest->getMultiParam('sdate');

            $sdate = !empty($date_range['from']) ? $date_range['from'] : $sdate;
            $edate = !empty($date_range['to']) ? $date_range['to'] : $edate;

            $sdate = date_format(date_create_from_format($date_format." H:i", $sdate),'Y-m-d H:i');
            $edate = date_format(date_create_from_format($date_format." H:i",$edate),'Y-m-d H:i');

            $interval = $this->gridRequest->getMultiParam('interval','daily');
            $interval = empty($interval) || $interval == '*' ? 'daily' : $interval;
            $skill_id = $this->gridRequest->getMultiParam('skill_id','*');
            $skill_type = $this->gridRequest->getMultiParam('skill_type','*');
        }
        /*===================================================================================*/
        if (in_array($interval, ['quarter-hourly','half-hourly','hourly'])){
            $edate = DateTime::createFromFormat("Y-m-d H:i", $sdate)->format(DB_DATE_HHMM_END_FORMAT);
        }

        $date_interval = DateInterval::createFromDateString('1 day');
        $date_format_for_row_access = "Y-m-d";
        $date_show_format = $date_format;

        if ($interval == 'quarter-hourly'){
            $date_interval = DateInterval::createFromDateString('15 minutes');
            $date_format_for_row_access = "d-F-Y_H:";
            $date_show_format = $date_format." H:i";
        }
        elseif ($interval == 'half-hourly'){
            $date_interval = DateInterval::createFromDateString('30 minutes');
            $date_format_for_row_access = "d-F-Y_H:";
            $date_show_format = $date_format." H:i";
        }
        elseif ($interval == 'hourly'){
            $date_interval = DateInterval::createFromDateString('1 hour');
            $date_format_for_row_access = $date_format." H:00";
            $date_show_format = $date_format." H:00";
        }elseif ($interval == 'daily'){
            $date_interval = DateInterval::createFromDateString('24 hours');
            $date_format_for_row_access = $date_format;
        }elseif ($interval == 'monthly'){
            $date_interval = DateInterval::createFromDateString('1 month');
            $date_format_for_row_access = "F, Y";
            $date_show_format = REPORT_YEAR_MONTH_FORMAT;
            // $sdate = date("Y-m-1 H:i:s",strtotime($sdate)); // remove and select date range of less than 30 days to see the effect
        }elseif ($interval == 'quarterly'){
            $date_interval = DateInterval::createFromDateString('3 months');
            $date_format_for_row_access = "Y_";
            //$date_show_format = REPORT_YEAR_FORMAT;
        }elseif ($interval == 'yearly'){
            $date_interval = DateInterval::createFromDateString('1 year');
            $date_format_for_row_access = "Y";
            $date_show_format = REPORT_YEAR_FORMAT;
        }
        $dateinfo =  new DatePeriod(
            new DateTime($sdate), $date_interval, new DateTime($edate)
        );
        $skills = $skill_model->getActiveSkillAsKeyValue();

        foreach ($dateinfo as $key => $interval_date){
            $quarter = '';
            if ($interval == 'quarterly'){
                $quarter = ceil($interval_date->format('m')/3);
                $quarter = "Quarter-".$quarter;
            }elseif ($interval == 'half-hourly'){
                $quarter = str_pad(floor($interval_date->format('i')/30) *30,2,'0');
            }elseif ($interval == 'quarter-hourly'){
                $quarter = str_pad(floor($interval_date->format('i')/15) *15,2,'0');
            }

            $formatedData[$interval_date->format($date_format_for_row_access).$quarter] = new stdClass();

            foreach ($skills as $id => $skill_title){

                $formatedData[$interval_date->format($date_format_for_row_access) .$quarter ]->sdate = str_replace("_"," ",$interval_date->format($date_format_for_row_access).$quarter);
                $formatedData[$interval_date->format($date_format_for_row_access) .$quarter ]->sdate = $interval_date->format($date_show_format);
                if ($interval == 'quarterly'){
                    $formatedData[$interval_date->format($date_format_for_row_access) .$quarter ]->sdate = $quarter.", ".$interval_date->format(REPORT_YEAR_FORMAT);
                }
                $formatedData[$interval_date->format($date_format_for_row_access) . $quarter]->{$id} = 0;
            }
            //dd($formatedData);
        }

        /*===================================================================================*/

        $result = $report_model->getWorkCodeCountReport($sdate, $edate, $skill_id, $interval, $skill_type);


        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if((is_array($result) || is_object($result)) && count($result) > 0){
            // var_dump($result);
            foreach ( $result as &$data ) {
                if ($interval == 'quarter-hourly'){

                }
                elseif ($interval == 'half-hourly'){

                }
                elseif ($interval == 'hourly'){
                    $data->sdate = date($date_format.' H:00', strtotime($data->sdate));
                }
                elseif ($interval == 'daily'){
                    $data->sdate = date($date_format, strtotime($data->sdate));
                }
                elseif ($interval == 'monthly'){
                    $data->sdate = date('F, Y', strtotime($data->sdate));
                } elseif ($interval == 'quarterly'){

                }
                elseif ($interval == 'yearly'){
                    $data->sdate = date('Y', strtotime($data->sdate));
                }
                // $date = new DateTime(str_replace("_"," ",$data->sdate));

                // $formatedData[$data->sdate]->sdate = $date->format($date_format);

                $formatedData[$data->sdate]->{$data->skill_id} = $data->call_count;
            }
        }
        // var_dump($formatedData);
        // die();
        $response->records = $this->pagination->num_records = count($formatedData);
        $response->hideCol = array_merge($response->hideCol, $report_hide_col);
        $response->rowdata = array_values($formatedData);
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate,
            'skill_id' => $skill_id,
            'skill_type' => $skill_type,
            'interval' => $interval
        ];
        $this->reportAudit('NR', 'Workecode Count', $request_param);
        $this->ShowTableResponse();
    }
    function actionReportDashboardWorkingWithCategory(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            // $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format(REPORT_DATE_FORMAT." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_time_details(false, $date_from, $date_to, $hour_from, $hour_to);
        $skill_options = $skill_model->getSkillsNamesArray();

        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            $this->pagination->num_records = 0;
        }

        // calculate total count
        // var_dump($this->pagination->rows_per_page);
        $skill_categories = $skill_category_model->getSkillCategories('', '', 'A');
        foreach ($skill_categories as $key => $item) {
            $skill_ids = explode(',', $item->skill_ids);
            $skill_ids = implode("','", $skill_ids);

            if(empty($category)){
                $cat_total = $report_model->numDashboardWorkingReport($dateinfo, $skill_ids, $report_type);
                // var_dump($cat_total);
                $this->pagination->num_records += $cat_total;
            }elseif (!empty($category) && $item->cat_id == $category){
                $this->pagination->num_records = $report_model->numDashboardWorkingReport($dateinfo, $skill_ids, $report_type);
                $category_skill_ids = $skill_ids;
                $skill_categories = [];
                $skill_categories[] = $item;
                break;
            }
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $original_result = [];
        $grid_row_count = 0;
        $page_count = 1;
        // $half_hour_count = 0;
        // $half_hour_first_data = [];
        // var_dump($this->pagination->num_records);

        if($this->pagination->num_records > 0){
            $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
            // last row initialization
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->shour = "-";
            $final_row->sminute = "-";
            $final_row->category = "-";
            $final_row->skill_id = "-";
            $final_row->skill_name = "-";
            $final_row->smonth = "-";
            $final_row->quarter_no = "-";
            $final_row->rgb_call_count = 0;
            $final_row->forecasted_calls = 0;
            $final_row->calls_offered = 0;
            $final_row->calls_answerd = 0;
            $final_row->calls_abandoned = 0;
            $final_row->ans_lte_10_count = 0;
            $final_row->ans_lte_20_count = 0;
            $final_row->ans_lte_30_count = 0;
            $final_row->ans_lte_60_count = 0;
            $final_row->ans_lte_90_count = 0;
            $final_row->ans_lte_120_count = 0;
            $final_row->ans_gt_120_count = 0;
            $final_row->abd_lte_10_count = 0;
            $final_row->abd_lte_20_count = 0;
            $final_row->abd_lte_30_count = 0;
            $final_row->abd_lte_60_count = 0;
            $final_row->abd_lte_90_count = 0;
            $final_row->abd_lte_120_count = 0;
            $final_row->abd_gt_120_count = 0;
            $final_row->avg_handling_time = 0;
            $final_row->service_duration = 0;
            $final_row->ring_time = 0;
            $final_row->agent_hold_time = 0;
            $final_row->wrap_up_time = 0;
            $final_row->avg_wrap_up_time = '0.00%';
            $final_row->asa = '0.00%';
            $final_row->service_level_lte_10_count = 0;
            $final_row->service_level_lte_20_count = 0;
            $final_row->service_level_lte_30_count = 0;
            $final_row->service_level_lte_60_count = 0;
            $final_row->service_level_lte_90_count = 0;
            $final_row->service_level_lte_120_count = 0;
            $final_row->abandoned_ratio = '0.00%';
            $final_row->abandoned_ratio_10 = '0.00%';
            $final_row->abandoned_ratio_20 = '0.00%';
            $final_row->abandoned_ratio_30 = '0.00%';
            $final_row->abandoned_ratio_60 = '0.00%';
            $final_row->abandoned_ratio_90 = '0.00%';
            $final_row->abandoned_ratio_120 = '0.00%';
            $final_row->fcr_call_percentage = '0.00%';
            $final_row->forecasted_calls = 0;
            $final_row->short_call_count = 0;
            $final_row->short_call_percentage = '0.00%';
            $final_row->wrap_up_call_count = 0;
            $final_row->wrap_up_percentage = '0.00%';
            $final_row->unique_caller = 0;
            $final_row->unique_caller_percentage = '0.00%';
            $final_row->repeat_call_percentage = '0.00%';
            $final_row->agent_hangup_count = 0;
            $final_row->agent_hangup_percentage = '0.00%';
            $final_row->cpc = 0;
            $final_row->fcr_call_count = 0;
            $final_row->repeat_1_count = 0;
            $final_row->ice_count = 0;
            $final_row->request_call_count = 0;
            $final_row->query_call_count = 0;
            $final_row->complaint_call_count = 0;
            $final_row->per_request_call_count = 0;
            $final_row->per_query_call_count = 0;
            $final_row->per_complaint_call_count = 0;
            $final_row->ice_positive_count = 0;
            $final_row->ice_negative_count  = 0;
            $final_row->repeat_2_count  = 0;
            $final_row->repeat_3_count  = 0;
            $final_row->repeat_7_count  = 0;
            $final_row->repeat_30_count  = 0;
            $final_row->repeat_cli_1_count  = 0;
            $final_row->repeat_cli_2_count  = 0;
            $final_row->repeat_cli_3_count  = 0;
            $final_row->repeat_cli_7_count  = 0;
            $final_row->repeat_cli_30_count  = 0;
            $final_row->per_repeat_1_count = 0;
            $final_row->per_repeat_2_count = 0;
            $final_row->per_repeat_3_count = 0;
            $final_row->per_repeat_7_count = 0;
            $final_row->per_repeat_30_count = 0;
            $final_row->per_repeat_cli_1_count = 0;
            $final_row->per_repeat_cli_2_count = 0;
            $final_row->per_repeat_cli_3_count = 0;
            $final_row->per_repeat_cli_7_count = 0;
            $final_row->per_repeat_cli_30_count = 0;
            $final_row->max_hold_time_in_queue = 0;

            while (true) {
                if(empty($category)){
                    $category_result = $report_model->getDashboardWorkingReport($dateinfo, '', $report_type, $offset, $this->pagination->rows_per_page);
                }elseif (!empty($category) && $item->cat_id == $category){
                    $category_result = $report_model->getDashboardWorkingReport($dateinfo, $category_skill_ids, $report_type, $offset, $this->pagination->rows_per_page);
                }

                if(!empty($category_result) && count($category_result) > 0){
                    // var_dump($category_result);
                    if($report_type == REPORT_HALF_HOURLY){
                        $tmp_category_result = [];
                        /*
                         * half hourly calculation
                         *
                        */
                        foreach ($category_result as $key => $data) {
                            if($data->sminute == '00' || $data->sminute == '15'){
                                $idx = $data->sdate.'##'.$data->shour."##00##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '00';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '00';
                                }
                            }elseif($data->sminute == '30' || $data->sminute == '45'){
                                $idx = $data->sdate.'##'.$data->shour."##30##".$data->skill_id;
                                if(array_key_exists($idx, $tmp_category_result)){
                                    $tmp_category_result[$idx] = $report_model->half_hour_data_calculation($tmp_category_result[$idx], $data);
                                    $tmp_category_result[$idx]->half_hour = '30';
                                    // var_dump($tmp_category_result[$idx]->half_hour );
                                }else{
                                    $tmp_category_result[$idx] = $data;
                                    $tmp_category_result[$idx]->half_hour = '30';
                                }
                            }
                        }
                        $category_result = $tmp_category_result;
                        // var_dump($tmp_category_result);
                    }
                    // die();
                    foreach ($category_result as $key => $data) {
                        foreach ($skill_categories as $key => $item) {
                            $skill_id_arr = explode(',', $item->skill_ids);
                            $skill_id_str = implode("','", $skill_id_arr);

                            if(in_array($data->skill_id, $skill_id_arr)){
                                // skip previous page item
                                if($response->page > 1){
                                    if($grid_row_count < (($response->page*$this->pagination->rows_per_page) - $this->pagination->rows_per_page)){
                                        $grid_row_count++;
                                        continue;
                                    }
                                }
                                // pr($skill_id_arr);
                                // pr($grid_row_count);
                                // if($report_type != REPORT_HALF_HOURLY){
                                // $data->half_hour = '';
                                // }

                                if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                                    $data->hour_minute = $data->shour.':'.$data->sminute;
                                    $data->half_hour = '';
                                }else if($report_type == REPORT_HALF_HOURLY){
                                    $data->hour_minute = $data->shour.':'.$data->half_hour;
                                }else{
                                    $data->hour_minute = '';
                                    $data->half_hour = '';
                                }

                                $data->category = $item->name;
                                $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                                $data->skill_name = isset($skill_options[$data->skill_id]) ? ' ' . $skill_options[$data->skill_id] : ' ' . $data->skill_id;
                                $data->avg_handling_time = (!empty($data->calls_answerd)) ? ceil(($data->ring_time+$data->service_duration+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                                $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->asa = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->ring_time / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? ceil($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)) : 0;
                                $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? ceil($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)) : 0;
                                $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? ceil($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)) : 0;
                                $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? ceil($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)) : 0;
                                $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? ceil($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)) : 0;
                                $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? ceil($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)) : 0;
                                $data->abandoned_ratio = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->calls_abandoned / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_10_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_20_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_30_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_60_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_90_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->abd_lte_120_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->fcr_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->fcr_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->short_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->short_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->wrap_up_call_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->unique_caller = $data->calls_answerd-$data->repeat_1_count;
                                $data->unique_caller_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->unique_caller / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->repeat_call_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->repeat_1_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? sprintf("%.2f", ($data->agent_hangup_count / $data->calls_answerd) * 100).'%' : '0.00%';
                                $data->cpc = (!empty($data->rgb_call_count)) ? ceil($data->calls_offered / $data->rgb_call_count) : 0;
                                $data->per_complaint_call_count = (!empty($data->wrap_up_call_count)) ? sprintf("%.2f", ($data->per_complaint_call_count / $data->wrap_up_call_count) * 100).'%' : '0.00%';
                                $data->per_request_call_count = (!empty($data->wrap_up_call_count)) ? sprintf("%.2f", ($data->per_request_call_count / $data->wrap_up_call_count) * 100).'%' : '0.00%';
                                $data->per_query_call_count = (!empty($data->wrap_up_call_count)) ? sprintf("%.2f", ($data->per_query_call_count / $data->wrap_up_call_count) * 100).'%' : '0.00%';
                                $data->ice_score = (!empty($data->ice_count)) ? sprintf("%.2f", ($data->ice_positive_count / $data->ice_count) * 100).'%' : '0.00%';
                                $data->per_repeat_1_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_1_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_2_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_2_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_3_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_3_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_7_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_7_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_30_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_30_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_cli_1_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_cli_1_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_cli_2_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_cli_2_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_cli_3_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_cli_3_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_cli_7_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_cli_7_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->per_repeat_cli_30_count = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->repeat_cli_30_count / $data->calls_offered) * 100).'%' : '0.00%';
                                $data->ave_hold_time_in_queue = (!empty($data->calls_offered)) ? sprintf("%.2f", ($data->hold_time_in_queue / $data->calls_offered) * 100).'%' : '0.00%';


                                // footer data
                                $final_row->rgb_call_count += $data->rgb_call_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->calls_offered += $data->calls_offered;
                                $final_row->calls_answerd += $data->calls_answerd;
                                $final_row->calls_abandoned += $data->calls_abandoned;
                                $final_row->ans_lte_10_count += $data->ans_lte_10_count;
                                $final_row->ans_lte_20_count += $data->ans_lte_20_count;
                                $final_row->ans_lte_30_count += $data->ans_lte_30_count;
                                $final_row->ans_lte_60_count += $data->ans_lte_60_count;
                                $final_row->ans_lte_90_count += $data->ans_lte_90_count;
                                $final_row->ans_lte_120_count += $data->ans_lte_120_count;
                                $final_row->ans_gt_120_count += $data->ans_gt_120_count;
                                $final_row->abd_lte_10_count += $data->abd_lte_10_count;
                                $final_row->abd_lte_20_count += $data->abd_lte_20_count;
                                $final_row->abd_lte_30_count += $data->abd_lte_30_count;
                                $final_row->abd_lte_60_count += $data->abd_lte_60_count;
                                $final_row->abd_lte_90_count += $data->abd_lte_90_count;
                                $final_row->abd_lte_120_count += $data->abd_lte_120_count;
                                $final_row->abd_gt_120_count += $data->abd_gt_120_count;
                                $final_row->avg_handling_time += $data->avg_handling_time;
                                $final_row->service_duration += $data->service_duration;
                                $final_row->ring_time += $data->ring_time;
                                $final_row->agent_hold_time += $data->agent_hold_time;
                                $final_row->wrap_up_time += $data->wrap_up_time;
                                $final_row->service_level_lte_10_count += $data->service_level_lte_10_count;
                                $final_row->service_level_lte_20_count += $data->service_level_lte_20_count;
                                $final_row->service_level_lte_30_count += $data->service_level_lte_30_count;
                                $final_row->service_level_lte_60_count += $data->service_level_lte_60_count;
                                $final_row->service_level_lte_90_count += $data->service_level_lte_90_count;
                                $final_row->service_level_lte_120_count += $data->service_level_lte_120_count;
                                $final_row->forecasted_calls += 0;
                                $final_row->short_call_count += $data->short_call_count;
                                $final_row->wrap_up_call_count += $data->wrap_up_call_count;
                                $final_row->unique_caller += $data->unique_caller;
                                $final_row->agent_hangup_count += $data->agent_hangup_count;
                                $final_row->cpc += $data->cpc;
                                $final_row->fcr_call_count += $data->fcr_call_count;
                                $final_row->ice_count += $data->ice_count;
                                $final_row->complaint_call_count += $data->complaint_call_count;
                                $final_row->request_call_count += $data->request_call_count;
                                $final_row->query_call_count += $data->query_call_count;
                                $final_row->ice_positive_count += $data->ice_positive_count;
                                $final_row->ice_negative_count += $data->ice_negative_count;
                                $final_row->repeat_1_count += $data->repeat_1_count;
                                $final_row->repeat_2_count += $data->repeat_2_count;
                                $final_row->repeat_3_count += $data->repeat_3_count;
                                $final_row->repeat_7_count += $data->repeat_7_count;
                                $final_row->repeat_30_count += $data->repeat_30_count;
                                $final_row->repeat_cli_1_count += $data->repeat_cli_1_count;
                                $final_row->repeat_cli_2_count += $data->repeat_cli_2_count;
                                $final_row->repeat_cli_3_count += $data->repeat_cli_3_count;
                                $final_row->repeat_cli_7_count += $data->repeat_cli_7_count;
                                $final_row->repeat_cli_30_count += $data->repeat_cli_30_count;
                                $final_row->max_hold_time_in_queue += $data->max_hold_time_in_queue;

                                $original_result[] = (array)$data;
                                $original_result[count($original_result)-1]['sdate'] = date(REPORT_DATE_FORMAT, strtotime($data->sdate));

                                $grid_row_count++;
                            }


                            if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                                break;
                            }
                        }
                        if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                            break;
                        }
                    }
                    if($grid_row_count >= $this->pagination->rows_per_page*$response->page || $grid_row_count >= $this->pagination->num_records){
                        break;
                    }

                    $page_count++;
                    $offset = ($page_count*$this->pagination->rows_per_page) - $this->pagination->rows_per_page;
                }else{
                    break;
                }
            }

            $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->asa = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->ring_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->calls_abandoned / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_10_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_20_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_30_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_60_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_90_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->abd_lte_120_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->fcr_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->fcr_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->short_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->unique_caller_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->unique_caller / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->repeat_call_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->repeat_1_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->agent_hangup_count / $final_row->calls_answerd) * 100).'%' : '0.00%';
            $final_row->per_request_call_count = (!empty($final_row->wrap_up_call_count)) ? sprintf("%.2f", ($final_row->request_call_count / $final_row->wrap_up_call_count) * 100).'%' : '0.00%';
            $final_row->per_complaint_call_count = (!empty($final_row->wrap_up_call_count)) ? sprintf("%.2f", ($final_row->complaint_call_count / $final_row->wrap_up_call_count) * 100).'%' : '0.00%';
            $final_row->per_query_call_count = (!empty($final_row->wrap_up_call_count)) ? sprintf("%.2f", ($final_row->query_call_count / $final_row->wrap_up_call_count) * 100).'%' : '0.00%';
            $final_row->ice_score = (!empty($final_row->ice_count)) ? sprintf("%.2f", ($final_row->ice_positive_count / $final_row->ice_count) * 100).'%' : '0.00%';
            $final_row->per_repeat_1_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_1_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_2_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_2_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_3_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_3_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_7_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_7_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_30_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_30_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_cli_1_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_cli_1_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_cli_2_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_cli_2_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_cli_3_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_cli_3_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_cli_7_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_cli_7_count / $final_row->calls_offered) * 100).'%' : '0.00%';
            $final_row->per_repeat_cli_30_count = (!empty($final_row->calls_offered)) ? sprintf("%.2f", ($final_row->repeat_cli_30_count / $final_row->calls_offered) * 100).'%' : '0.00%';

            $response->userdata = $final_row;

        }

        // var_dump($this->pagination->num_records);
        // var_dump($page_count);
        // var_dump($offset);
        // var_dump($grid_row_count);
        // var_dump($original_result);
        // die();

        if($report_type == REPORT_YEARLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['syear'];
        }elseif($report_type == REPORT_QUARTERLY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
            $response->showCol = ['quarter_no'];
        }elseif($report_type == REPORT_MONTHLY){
            $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['smonth'];
        }elseif($report_type == REPORT_DAILY){
            $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate'];
        }else if($report_type == REPORT_HOURLY){
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate', 'shour'];
        }else if($report_type == REPORT_HALF_HOURLY){
            $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
            $response->showCol = ['sdate', 'hour_minute'];
        }else{
            $response->showCol = ['sdate', 'hour_minute'];
            $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
        }

        $response->rowdata = $original_result;
        // var_dump($response->rowdata);
        $this->ShowTableResponse();
    }
    public function actionWebChatSummaryReport()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date("Y-m-d",strtotime("+1 day"));
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $skills_types = $skill_model->getSkillsTypeArray();

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types['C']);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types['C'])){
                $skill_ids = implode("','", $skills_types['C']);
            }

            $this->pagination->num_records = $report_model->numWebChatSummaryData($dateInfo, $skill_ids);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            $result = $this->pagination->num_records > 0 ?
                $report_model->getWebChatSummaryData($dateInfo, $skill_ids, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            if(count($result) > 0)
            {
                foreach ( $result as $data ){
                    $data->avg_handling_time = $data->service_time+$data->wrap_up_time;
                    $data->avg_handling_time = gmdate("H:i:s", round($data->avg_handling_time/$data->calls_answerd));
                    $data->hold_in_q = gmdate("H:i:s", round($data->hold_in_q/$data->calls_answerd));
                    $data->service_level = (!empty($data->kpi_in)) ? fractionFormat(($data->kpi_in / $data->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->max_hold_in_q = gmdate("H:i:s", $data->max_hold_in_q);

                    $data->sdate = date($date_format, strtotime($data->sdate));
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getWebChatSummaryData($dateInfo, $skill_ids,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";

                    $final_row->avg_handling_time = $final_row->service_time+$final_row->wrap_up_time;
                    $final_row->avg_handling_time = gmdate("H:i:s", round($final_row->avg_handling_time/$final_row->calls_answerd));
                    $final_row->hold_in_q = gmdate("H:i:s", round($final_row->hold_in_q/$final_row->calls_answerd));

                    $final_row->service_level = (!empty($final_row->kpi_in)) ? fractionFormat(($final_row->kpi_in / $final_row->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->max_hold_in_q = gmdate("H:i:s", $final_row->max_hold_in_q);
                }
                $response->userdata = $final_row;
            }

            $response->rowdata = $result;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $dial_time['from'],
                'edate' => $dial_time['to'],
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            $this->reportAudit('NR', 'Web Chat Summary', $request_param);
            $this->ShowTableResponse();
        }
    }
    public function actionOnlineChatEfficiencyReport()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date("Y-m-d", strtotime("+1 day"));
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');

        }
//        $dateInfo = DateHelper::get_input_time_details(false, $date_from, $date_to);
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');
        $this->pagination->num_records = $report_model->numOnlineChatEfficiencyData($dateInfo, $agent_id, $skill_id);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getOnlineChatEfficiencyData($dateInfo, $agent_id, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        if (count($result) > 0) {
            foreach ($result as $data) {
                $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->start_time = date("H:i:s", strtotime( $data->start_time));
                $data->end_time = date("H:i:s", strtotime($data->end_time));
                $data->agent_name = $agent_model->getAgentById($data->agent_id)->name;
            }
        }
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }
    function actionReportSummary()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MForcastRgb.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $forecast_rgb_model = new MForcastRgb();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            // $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days );
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }

            // calculate total count
            $this->pagination->num_records = $report_model->numSummaryReport($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            $forecast_rgb_data=[];
            if($report_type==REPORT_DAILY){
                $forecast_rgb_data = $forecast_rgb_model->getReportForecastRgbData($dateinfo);
            }

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => $data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->category = ''; //$item->name;
                    $data->forecasted_call_count = 0;
                    $data->forecasted_call_percentage = 0;
                    $data->cpc = 0;
                    $data->rgb_call_count = 0;
                    // $data->wrap_up_time = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->wrap_up_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->delay_between_call = $data->calls_answerd*DELAY_BETWEEN_CALLS;
                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->talk_time = $data->service_duration-$data->agent_hold_time;

                    if($this->gridRequest->isDownloadCSV){
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? ($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? $data->wrap_up_time / $data->calls_answerd : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? $data->ring_time / $data->calls_answerd : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? $data->calls_offered / $data->rgb_call_count : 0;
                    }else{
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? round(($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? round($data->wrap_up_time / $data->calls_answerd) : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? round($data->ring_time / $data->calls_answerd) : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? round($data->calls_offered / $data->rgb_call_count) : 0;
                    }

                    $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? fractionFormat(($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? fractionFormat(($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? fractionFormat(($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? fractionFormat(($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? fractionFormat(($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? fractionFormat(($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_10_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_20_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_60_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_90_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_120_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->fcr_call_percentage = !empty($data->calls_answerd) ? fractionFormat(($data->fcr_call_count / $data->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->short_call_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->short_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->wrap_up_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->unique_caller = $data->calls_offered-$data->repeat_cli_1_count;
                    $data->unique_caller_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->unique_caller / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->repeat_call_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->repeat_cli_1_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->agent_hangup_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->sdate = date($date_format, strtotime($data->sdate));

                    if($report_type==REPORT_DAILY){
                        $data->forecasted_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'] : '-';
                        $data->rgb_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'] : '-';

                        $data->forecasted_call_percentage = (!empty($data->forecasted_call_count) && $data->forecasted_call_count !='-') ? fractionFormat(($data->calls_offered / $data->forecasted_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                        $data->cpc = (!empty($data->rgb_call_count) && $data->rgb_call_count !='-') ? fractionFormat(($data->calls_offered / $data->rgb_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }
                }

                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    $final_row->hour_minute = "-";
                    // $final_row->wrap_up_time = '-';
                    // $final_row->avg_wrap_up_time = '-';
                    // $final_row->fcr_call_percentage = '-';
                    $final_row->forecasted_call_count = '-';
                    $final_row->rgb_call_count = '-';
                    $final_row->cpc = '-';
                    $final_row->forecasted_call_percentage = '-';
                    // $final_row->wrap_up_call_count = '-';
                    // $final_row->wrap_up_percentage = '-';
                    $final_row->unique_caller = $final_row->calls_offered-$final_row->repeat_cli_1_count;
                    // $final_row->delay_between_call = $final_row->calls_answerd*DELAY_BETWEEN_CALLS;
                    $final_row->talk_time = $final_row->service_duration-$final_row->agent_hold_time;

                    $final_row->avg_handling_time = (!empty($final_row->calls_answerd)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->wrap_up_time+$final_row->agent_hold_time)/$final_row->calls_answerd) : 0;
                    $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? round($final_row->wrap_up_time / $final_row->calls_answerd) : 0;
                    $final_row->asa = (!empty($final_row->calls_answerd)) ? round($final_row->ring_time / $final_row->calls_answerd) : 0;

                    $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_10_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_20_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_60_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_90_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_120_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->fcr_call_percentage = !empty($final_row->calls_answerd) ? fractionFormat(($final_row->fcr_call_count / $final_row->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->short_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->unique_caller_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->unique_caller / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->repeat_call_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_1_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->agent_hangup_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->service_level_lte_10_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_10_count))) ? fractionFormat(($final_row->ans_lte_10_count / ($final_row->calls_offered - $final_row->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_20_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_20_count))) ? fractionFormat(($final_row->ans_lte_20_count / ($final_row->calls_offered - $final_row->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_30_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_30_count))) ? fractionFormat(($final_row->ans_lte_30_count / ($final_row->calls_offered - $final_row->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_60_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_60_count))) ? fractionFormat(($final_row->ans_lte_60_count / ($final_row->calls_offered - $final_row->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_90_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_90_count))) ? fractionFormat(($final_row->ans_lte_90_count / ($final_row->calls_offered - $final_row->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_120_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_120_count))) ? fractionFormat(($final_row->ans_lte_120_count / ($final_row->calls_offered - $final_row->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $response->userdata = $final_row;
                }
            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id'),
            ];
            $this->reportAudit('NR', 'Skill Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionReportSkillSetSummary()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MForcastRgb.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $forecast_rgb_model = new MForcastRgb();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            // $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

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

            // calculate total count
            $this->pagination->num_records = $report_model->numSkillSetSummaryReport($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getSkillSetSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            $forecast_rgb_data=[];
            if($report_type==REPORT_DAILY){
                $forecast_rgb_data = $forecast_rgb_model->getReportForecastRgbData($dateinfo);
            }

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => $data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->category = ''; //$item->name;
                    $data->forecasted_call_count = 0;
                    $data->forecasted_call_percentage = 0;
                    $data->cpc = 0;
                    $data->rgb_call_count = 0;
                    // $data->wrap_up_time = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->wrap_up_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->delay_between_call = $data->calls_answerd*DELAY_BETWEEN_CALLS;
                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->talk_time = $data->service_duration - $data->agent_hold_time;

                    if($this->gridRequest->isDownloadCSV){
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? ($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? $data->wrap_up_time / $data->calls_answerd : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? $data->ring_time / $data->calls_answerd : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? $data->calls_offered / $data->rgb_call_count : 0;
                    }else{
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? round(($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? round($data->wrap_up_time / $data->calls_answerd) : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? round($data->ring_time / $data->calls_answerd) : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? round($data->calls_offered / $data->rgb_call_count) : 0;
                    }

                    $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? fractionFormat(($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? fractionFormat(($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? fractionFormat(($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? fractionFormat(($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? fractionFormat(($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? fractionFormat(($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_10_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_20_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_60_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_90_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_120_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->fcr_call_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->fcr_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->short_call_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->short_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->wrap_up_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->unique_caller = $data->calls_offered-$data->repeat_cli_1_count;
                    $data->unique_caller_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->unique_caller / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->repeat_call_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->repeat_cli_1_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->agent_hangup_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->sdate = date($date_format, strtotime($data->sdate));

                    if($report_type==REPORT_DAILY){
                        $data->forecasted_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'] : '-';
                        $data->rgb_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'] : '-';

                        $data->forecasted_call_percentage = (!empty($data->forecasted_call_count) && $data->forecasted_call_count !='-') ? fractionFormat(($data->calls_offered / $data->forecasted_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                        $data->cpc = (!empty($data->rgb_call_count) && $data->rgb_call_count !='-') ? fractionFormat(($data->calls_offered / $data->rgb_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getSkillSetSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    $final_row->hour_minute = "-";
                    // $final_row->wrap_up_time = '-';
                    // $final_row->avg_wrap_up_time = '-';
                    // $final_row->fcr_call_percentage = '-';
                    $final_row->forecasted_call_count = '-';
                    $final_row->rgb_call_count = '-';
                    $final_row->cpc = '-';
                    $final_row->forecasted_call_percentage = '-';
                    // $final_row->wrap_up_call_count = '-';
                    // $final_row->wrap_up_percentage = '-';
                    $final_row->unique_caller = $final_row->calls_offered-$final_row->repeat_cli_1_count;
                    // $final_row->delay_between_call = $final_row->calls_answerd*DELAY_BETWEEN_CALLS;
                    $final_row->talk_time = $final_row->service_duration - $final_row->agent_hold_time;

                    $final_row->avg_handling_time = (!empty($final_row->calls_answerd)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->wrap_up_time+$final_row->agent_hold_time)/$final_row->calls_answerd) : 0;
                    $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? round($final_row->wrap_up_time / $final_row->calls_answerd) : 0;
                    $final_row->asa = (!empty($final_row->calls_answerd)) ? round($final_row->ring_time / $final_row->calls_answerd) : 0;

                    $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_10_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_20_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_60_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_90_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_120_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->fcr_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->fcr_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->short_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->unique_caller_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->unique_caller / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->repeat_call_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_1_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->agent_hangup_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->service_level_lte_10_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_10_count))) ? fractionFormat(($final_row->ans_lte_10_count / ($final_row->calls_offered - $final_row->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_20_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_20_count))) ? fractionFormat(($final_row->ans_lte_20_count / ($final_row->calls_offered - $final_row->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_30_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_30_count))) ? fractionFormat(($final_row->ans_lte_30_count / ($final_row->calls_offered - $final_row->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_60_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_60_count))) ? fractionFormat(($final_row->ans_lte_60_count / ($final_row->calls_offered - $final_row->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_90_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_90_count))) ? fractionFormat(($final_row->ans_lte_90_count / ($final_row->calls_offered - $final_row->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_120_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_120_count))) ? fractionFormat(($final_row->ans_lte_120_count / ($final_row->calls_offered - $final_row->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $response->userdata = $final_row;
                }
            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id'),
            ];
            $this->reportAudit('NR', 'SkillSet Summary', $request_param);
            $this->ShowTableResponse();
        }
    }
    function actionReportDashboardWorking(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MForcastRgb.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $forecast_rgb_model = new MForcastRgb();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $date_format = get_report_date_format();
            // $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

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

            // calculate total count
            $this->pagination->num_records = $report_model->numDashboardWorkingReport($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getDashboardWorkingReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            $forecast_rgb_data=[];
            if($report_type==REPORT_DAILY){
                $forecast_rgb_data = $forecast_rgb_model->getReportForecastRgbData($dateinfo);
            }

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => &$data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->category = '-';
                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->unique_caller = $data->calls_answerd-$data->repeat_cli_1_count;
                    // $data->delay_between_call = $data->calls_answerd*DELAY_BETWEEN_CALLS;
                    // $data->wrap_up_time = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->wrap_up_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->complaint_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->request_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->query_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->per_complaint_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->per_request_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->per_query_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->fcr_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    $data->forecasted_call_count = 0;
                    $data->forecasted_call_percentage = 0;
                    $data->cpc = 0;
                    $data->rgb_call_count = 0;
                    $data->talk_time = $data->service_duration - $data->agent_hold_time;

                    if($this->gridRequest->isDownloadCSV){
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? ($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? ($data->calls_offered / $data->rgb_call_count) : 0;
                        $data->ave_hold_time_in_queue = (!empty($data->calls_offered)) ? $data->hold_time_in_queue / $data->calls_offered : 0;
                    }else{
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? round(($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? round($data->calls_offered / $data->rgb_call_count) : 0;
                        $data->ave_hold_time_in_queue = (!empty($data->calls_offered)) ? round($data->hold_time_in_queue / $data->calls_offered) : 0;
                    }

                    if(!empty($data->calls_answerd)){
                        $data->avg_wrap_up_time = fractionFormat(($data->wrap_up_time / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->asa = fractionFormat(($data->ring_time / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->fcr_call_percentage = fractionFormat(($data->fcr_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->short_call_percentage = fractionFormat(($data->short_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->wrap_up_percentage = fractionFormat(($data->wrap_up_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->unique_caller_percentage = fractionFormat(($data->unique_caller / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->repeat_call_percentage = fractionFormat(($data->repeat_cli_1_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->agent_hangup_percentage = fractionFormat(($data->agent_hangup_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                    }else{
                        $data->avg_wrap_up_time = '0.00%';
                        $data->asa = '0.00%';
                        $data->fcr_call_percentage = '0.00%';
                        $data->short_call_percentage = '0.00%';
                        $data->wrap_up_percentage = '0.00%';
                        $data->unique_caller_percentage = '0.00%';
                        $data->repeat_call_percentage = '0.00%';
                        $data->agent_hangup_percentage = '0.00%';
                    }

                    $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? fractionFormat(($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? fractionFormat(($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? fractionFormat(($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? fractionFormat(($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? fractionFormat(($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? fractionFormat(($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    if(!empty($data->calls_offered)){
                        $data->abandoned_ratio = fractionFormat(($data->calls_abandoned / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_10 = fractionFormat(($data->abd_lte_10_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_20 = fractionFormat(($data->abd_lte_20_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_30 = fractionFormat(($data->abd_lte_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_60 = fractionFormat(($data->abd_lte_60_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_90 = fractionFormat(($data->abd_lte_90_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->abandoned_ratio_120 = fractionFormat(($data->abd_lte_120_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';

                        $data->per_repeat_1_count = fractionFormat(($data->repeat_1_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_2_count = fractionFormat(($data->repeat_2_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_3_count = fractionFormat(($data->repeat_3_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_7_count = fractionFormat(($data->repeat_7_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_30_count = fractionFormat(($data->repeat_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';

                        $data->per_repeat_cli_1_count = fractionFormat(($data->repeat_cli_1_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_cli_2_count = fractionFormat(($data->repeat_cli_2_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_cli_3_count = fractionFormat(($data->repeat_cli_3_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_cli_7_count = fractionFormat(($data->repeat_cli_7_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_repeat_cli_30_count = fractionFormat(($data->repeat_cli_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                    }else{
                        $data->abandoned_ratio = '0.00%';
                        $data->abandoned_ratio_10 = '0.00%';
                        $data->abandoned_ratio_20 = '0.00%';
                        $data->abandoned_ratio_30 = '0.00%';
                        $data->abandoned_ratio_60 = '0.00%';
                        $data->abandoned_ratio_90 = '0.00%';
                        $data->abandoned_ratio_120 = '0.00%';
                        $data->per_repeat_1_count = '0.00%';
                        $data->per_repeat_2_count = '0.00%';
                        $data->per_repeat_3_count = '0.00%';
                        $data->per_repeat_7_count = '0.00%';
                        $data->per_repeat_30_count = '0.00%';
                        $data->per_repeat_cli_1_count = '0.00%';
                        $data->per_repeat_cli_2_count = '0.00%';
                        $data->per_repeat_cli_3_count = '0.00%';
                        $data->per_repeat_cli_7_count = '0.00%';
                        $data->per_repeat_cli_30_count = '0.00%';
                    }

                    if(!empty($data->wrap_up_call_count)){
                        $data->per_complaint_call_count = fractionFormat(($data->per_complaint_call_count / $data->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_request_call_count = fractionFormat(($data->per_request_call_count / $data->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                        $data->per_query_call_count = fractionFormat(($data->per_query_call_count / $data->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%';
                    }else{
                        $data->per_complaint_call_count = '0.00%';
                        $data->per_request_call_count = '0.00%';
                        $data->per_query_call_count = '0.00%';
                    }

                    $data->ice_score = (!empty($data->ice_count)) ? fractionFormat(($data->ice_positive_count / $data->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->sdate = date($date_format, strtotime($data->sdate));

                    if($report_type==REPORT_DAILY){
                        $data->forecasted_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'] : '-';
                        $data->rgb_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'] : '-';

                        $data->forecasted_call_percentage = (!empty($data->forecasted_call_count) && $data->forecasted_call_count !='-') ? fractionFormat(($data->calls_offered / $data->forecasted_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                        $data->cpc = (!empty($data->rgb_call_count) && $data->rgb_call_count !='-') ? fractionFormat(($data->calls_offered / $data->rgb_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getDashboardWorkingReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    // $final_row->delay_between_call = $final_row->calls_answerd*DELAY_BETWEEN_CALLS;
                    $final_row->talk_time = $final_row->service_duration - $final_row->agent_hold_time;

                    $final_row->avg_handling_time = (!empty($final_row->calls_answerd)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->wrap_up_time+$final_row->agent_hold_time)/$final_row->calls_answerd) : 0;

                    $final_row->service_level_lte_10_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_10_count))) ? fractionFormat(($final_row->ans_lte_10_count / ($final_row->calls_offered - $final_row->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_20_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_20_count))) ? fractionFormat(($final_row->ans_lte_20_count / ($final_row->calls_offered - $final_row->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_30_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_30_count))) ? fractionFormat(($final_row->ans_lte_30_count / ($final_row->calls_offered - $final_row->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_60_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_60_count))) ? fractionFormat(($final_row->ans_lte_60_count / ($final_row->calls_offered - $final_row->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_90_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_90_count))) ? fractionFormat(($final_row->ans_lte_90_count / ($final_row->calls_offered - $final_row->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_120_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_120_count))) ? fractionFormat(($final_row->ans_lte_120_count / ($final_row->calls_offered - $final_row->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? sprintf("%.2f", ($final_row->wrap_up_time / $final_row->calls_answerd) * 100).'%' : '0.00%';
                    $final_row->asa = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->ring_time / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->calls_abandoned / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_10_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_20_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_60_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_90_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_120_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->fcr_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->fcr_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->short_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->unique_caller_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->unique_caller / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->repeat_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->repeat_1_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->agent_hangup_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_request_call_count = (!empty($final_row->wrap_up_call_count)) ? fractionFormat(($final_row->request_call_count / $final_row->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_complaint_call_count = (!empty($final_row->wrap_up_call_count)) ? fractionFormat(($final_row->complaint_call_count / $final_row->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_query_call_count = (!empty($final_row->wrap_up_call_count)) ? fractionFormat(($final_row->query_call_count / $final_row->wrap_up_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->ice_score = (!empty($final_row->ice_count)) ? fractionFormat(($final_row->ice_positive_count / $final_row->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_1_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_1_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_2_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_2_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_3_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_3_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_7_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_7_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_30_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_cli_1_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_1_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_cli_2_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_2_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_cli_3_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_3_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_cli_7_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_7_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->per_repeat_cli_30_count = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->ave_hold_time_in_queue = (!empty($final_row->calls_offered)) ? round($final_row->hold_time_in_queue / $final_row->calls_offered) : 0;


                    $final_row->forecasted_call_count = '-';
                    $final_row->rgb_call_count = '-';
                    $final_row->cpc = '-';
                    $final_row->forecasted_call_percentage = '-';
                    // $final_row->complaint_call_count =  '-';
                    // $final_row->request_call_count = '-';
                    // $final_row->query_call_count = '-';
                    // $final_row->wrap_up_call_count = '-';
                    // $final_row->wrap_up_percentage = '-';
                    // $final_row->fcr_call_count = '-';
                    // $final_row->fcr_call_percentage = '-';
                }
                $response->userdata = $final_row;

            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id'),
            ];
            $this->reportAudit('NR', 'DashboardWorking', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionAgentSkillWiseReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch) {
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $agent_options = $agent_model->getAgentsFullName();

            $result=array();
            $csv_titles=array();
            $csv_data=array();
            $isRemoveTag = true; // use csv download
            $delimiter = ',';    // use csv download
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download
            $skip_row_count = 0;  // use grid view
            $original_result = [];  // use grid view

            // calculate total count
            $this->pagination->num_records = $report_model->numAgentSkillWiseData($dateinfo, $agent_id, $skill_ids, $skill_type, $report_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            //download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'agent_id' => $agent_id
            ];

            if($report_type == REPORT_DAILY){
                $response->hideCol = ['shour'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = [];
                $response->showCol = ['sdate', 'shour'];
            }

            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Daily Skill Wise Agent', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        if(!in_array($key, $response->hideCol)){
                            $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                            array_push($csv_titles,$value);
                        }
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }

            }else{
                // $report_model->saveReportAuditRequest('NRS::Daily Skill Wise Agent', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getAgentSkillWiseData($dateinfo, $agent_id, $skill_ids, $skill_type, $report_type, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getAgentSkillWiseData($dateinfo, $agent_id, $skill_ids, $skill_type, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format, strtotime($data->new_sdate));
                        $data->agent_name = $agent_options[$data->agent_id];
                        $data->skill_name = $skill_options[$data->skill_id];
                        // $data->wrap_time = '-';
                        // $data->delay_between_call = $data->answered*DELAY_BETWEEN_CALLS;
                        $data->talk_time = $data->srv_time - $data->hold_time;
                        $data->aht = $data->answered > 0 ? round(($data->talk_time + $data->hold_time + $data->rtime+$data->wrap_time)/$data->answered) : 0;

                        if($this->gridRequest->isDownloadCSV){ // for download
                            // $data->wrap_time = '';
                            $data->aht = $data->answered > 0 ? ($data->talk_time + $data->hold_time + $data->rtime+$data->wrap_time)/$data->answered : 0;
                            $row=array();
                            foreach ($cols as $key=>$value){
                                if(!in_array($key, $response->hideCol)){
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
                            }
                            fputcsv($f, $row, $delimiter);
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getAgentSkillWiseData($dateinfo, $agent_id, $skill_ids, $skill_type, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->shour = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                        $final_row->skill_name = "-";
                        // $final_row->wrap_time = "-";
                        // $final_row->delay_between_call = $final_row->answered*DELAY_BETWEEN_CALLS;
                        $final_row->talk_time = $final_row->srv_time - $final_row->hold_time;
                        $final_row->aht = $final_row->answered > 0 ? round(($final_row->talk_time + $final_row->hold_time + $final_row->rtime+$final_row->wrap_time)/$final_row->answered) : 0;

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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }
    function actionOutboundDetailsReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MAgent.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $disposition_type_list = disposition_type_list();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        $skill_type = 'O';

        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $agent_id = (!empty($this->gridRequest->getMultiParam('agent_id'))) ? $this->gridRequest->getMultiParam('agent_id') : '';
            // $skill_type = $this->gridRequest->getMultiParam('skill_type');
            // $category = $this->gridRequest->getMultiParam('category');
            // $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $agent_options = $agent_model->getAgentsFullName();
            $skills_types = $skill_model->getAllSkillsTypeArray();
            $callIdArr = [];  // use grid view
            $dispositions =  $report_model->get_disposition_all_value();
            // Gprint($skill_ids);

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
            $original_result = [];  // use grid view
            $disconnect_cause = get_disconnect_cause();

            // calculate total count
            $this->pagination->num_records = $report_model->numOutboundDetailsReport($dateinfo, $skill_ids, $agent_id, '');
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);

            //download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_id' => (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '',
                'agent_id' => $agent_id
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Outbound Details', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        if(!in_array($key, $response->hideCol)){
                            $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                            array_push($csv_titles,$value);
                        }
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }

            }else{
                $report_model->saveReportAuditRequest('NRS::Outbound Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getOutboundDetailsReport($dateinfo, $skill_ids, $agent_id, '',  $dbResultOffset, $dbResultRow, false, true);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getOutboundDetailsReport($dateinfo, $skill_ids, $agent_id, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format.' H:i:s', strtotime($data->start_time));
                        $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                        $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                        $data->disc_party = empty($data->disc_party) ? '' : get_disc_party($data->disc_party);
                        $data->aht = $data->ring_time+$data->talk_time+$data->hold_time+$data->wrap_up_time;
                        $data->disc_cause_text = empty($data->disc_cause) ? '' : (array_key_exists($data->disc_cause, $disconnect_cause) ? $disconnect_cause[$data->disc_cause] : '');

                        $data->is_reached = ($data->is_reached == 'Y') ? 'Answered' : '-';

                        if($this->gridRequest->isDownloadCSV){ // for download
                            //$data->disc_party = '';
                            $data->custom_title = $dispositions[$data->disposition_id]['title'];
                            $row=array();
                            foreach ($cols as $key=>$value){
                                if(!in_array($key, $response->hideCol)){
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
                            }
                            fputcsv($f, $row, $delimiter);
                            // var_dump($row);

                            $fileInputRowCount++;
                        }else{
                            $callIdArr[] = $data->callid;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getOutboundDetailsReport($dateinfo, $skill_ids, $agent_id, '',  $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                        $final_row->skill_name = "-";
                        $final_row->callerid = "-";
                        $final_row->callto = "-";
                        $final_row->disc_party = "-";
                        $final_row->is_reached = "-";
                        $final_row->disc_cause = "-";
                        $final_row->callid = "-";
                        $final_row->aht = $final_row->ring_time+$final_row->talk_time+$final_row->hold_time+$final_row->wrap_up_time;

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
                // Gprint($callIdArr);
                $callIdStr = implode("','", $callIdArr);
                $multiple_disposition = count($callIdArr) > 0 ? $report_model->getMultiplePdDisposition($callIdStr) : null;
                // Gprint($multiple_disposition);
                // die();
                if(!empty($multiple_disposition)){
                    $call_id_disposition = [];
                    foreach ($multiple_disposition as $key => $value) {
                        $result_key = array_search($value->callid, $callIdArr);
                        $call_id_disposition[$value->callid][] = $dispositions[$value->disposition_id]['title'];

                        if(!empty($call_id_disposition[$value->callid]) && count($call_id_disposition[$value->callid]) > 1){
                            $result[$result_key]->custom_title = $call_id_disposition[$value->callid][0].' <a onclick="showDispostionModal(\''.implode(',', $call_id_disposition[$value->callid]).'\')" class="show-multiple-disposition" ><i class="fa fa-arrow-circle-right" style="cursor: pointer;" aria-hidden="true"></i></a>';
                        }else{
                            $result[$result_key]->custom_title = $dispositions[$value->disposition_id]['title'];
                        }
                    }
                }

                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionPdDetailsReport() {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MAgent.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $disposition_type_list = disposition_type_list();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_id = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $agent_id = (!empty($this->gridRequest->getMultiParam('agent_id'))) ? $this->gridRequest->getMultiParam('agent_id') : '';

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $agent_options = $agent_model->getAgentsFullName();
            $dispositions =  $report_model->get_disposition_all_value();

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
            $original_result = [];  // use grid view
            $callIdArr = [];  // use grid view
            $disconnect_cause = get_disconnect_cause();

            // calculate total count
            $this->pagination->num_records = $report_model->numPdDetailsReport($dateinfo, $skill_id, $agent_id, '');
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);

            //download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_id' => $skill_id,
                'agent_id' => $agent_id
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::PD Details', $request_param);

                error_reporting(0);
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                $f = fopen('php://output', 'w');
                $cols=$this->gridRequest->getRequest("cols");
                $cols=(urldecode($cols));
                $cols=json_decode($cols);
                if(count($cols)>0){
                    foreach ($cols as $key=>$value){
                        if(!in_array($key, $response->hideCol)){
                            $value=preg_replace("/&.*?;|<.*?>/", "", $value);
                            array_push($csv_titles,$value);
                        }
                    }
                    fputcsv($f, $csv_titles, $delimiter);
                }

            }else{
                $report_model->saveReportAuditRequest('NRS::PD Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getPdDetailsReport($dateinfo, $skill_id, $agent_id, '',  $dbResultOffset, $dbResultRow, false, true);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getPdDetailsReport($dateinfo, $skill_id, $agent_id, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format.' H:i:s', strtotime($data->start_time));
                        $data->agent_name = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                        $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                        $data->disc_party = empty($data->disc_party) ? '' : get_disc_party($data->disc_party);
                        // var_dump($data->disc_cause);
                        // var_dump(array_key_exists($data->disc_cause, $disconnect_cause));
                        $data->disc_cause_text = empty($data->disc_cause) ? '' : (array_key_exists($data->disc_cause, $disconnect_cause) ? $disconnect_cause[$data->disc_cause] : '');

                        $data->status = ($data->status == 'A') ? 'Answered' : '-';
                        $data->title = $dispositions[$data->disposition_id]['title'];

                        if($this->gridRequest->isDownloadCSV){ // for download
                            //$data->disc_party = '';
                            $data->custom_title = $dispositions[$data->disposition_id]['title'];
                            $row=array();
                            foreach ($cols as $key=>$value){
                                if(!in_array($key, $response->hideCol)){
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
                            }
                            fputcsv($f, $row, $delimiter);
                            // var_dump($row);

                            $fileInputRowCount++;
                        }else{
                            $callIdArr[] = $data->callid;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getPdDetailsReport($dateinfo, $skill_id, $agent_id, '',  $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                        $final_row->skill_name = "-";
                        $final_row->callto = "-";
                        $final_row->disc_party = "-";
                        $final_row->is_reached = "-";
                        $final_row->disc_cause = "-";
                        $final_row->callid = "-";

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
                // Gprint($callIdArr);
                $callIdStr = implode("','", $callIdArr);
                $multiple_disposition = count($callIdArr) > 0 ? $report_model->getMultiplePdDisposition($callIdStr) : null;
                // Gprint($multiple_disposition);
                // die();
                if(!empty($multiple_disposition)){
                    $call_id_disposition = [];
                    foreach ($multiple_disposition as $key => $value) {
                        $result_key = array_search($value->callid, $callIdArr);
                        $call_id_disposition[$value->callid][] = $dispositions[$value->disposition_id]['title'];

                        if(!empty($call_id_disposition[$value->callid]) && count($call_id_disposition[$value->callid]) > 1){
                            $result[$result_key]->custom_title = $call_id_disposition[$value->callid][0].' <a onclick="showDispostionModal(\''.implode(',', $call_id_disposition[$value->callid]).'\')" class="show-multiple-disposition" ><i class="fa fa-arrow-circle-right" style="cursor: pointer;" aria-hidden="true"></i></a>';
                        }else{
                            $result[$result_key]->custom_title = $dispositions[$value->disposition_id]['title'];
                        }
                    }
                }

                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    public function actionAgentDetailsReport()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['to']),'Y-m-d H:i'))) : "23";
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $msisdn = $this->gridRequest->getMultiParam('msisdn');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $result=array();
            $csv_titles=array();
            $csv_data=array();
            $isRemoveTag = true; // use csv download
            $delimiter = ',';    // use csv download
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download
            $skip_row_count = 0;  // use grid view
            $original_result = [];  // use grid view

            // calculate total count
            $this->pagination->num_records = $report_model->numAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn);
            $skill_options = $skill_model->getSkillsNamesArray();
            $agent_options = $agent_model->getAgentsFullName();

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            //download
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'agent_id' => $agent_id,
                'skill_type' => $skill_type,
                'msisdn' => $msisdn,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Agent Details', $request_param);

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
                $report_model->saveReportAuditRequest('NRS::Agent Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download
                    $transfer_types = ['I'=>'IVR','A'=>'Agent','Q'=>'Skill'];

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format . " H:i:s", strtotime($data->call_start_time));
                        $data->agent_name = $agent_options[$data->agent_id];
                        $data->skill_name = $skill_options[$data->skill_id];

                        // $data->wrap_time = '-';
                        // $data->delay_between_call = $data->answered*DELAY_BETWEEN_CALLS;

                        $data->talk_time = $data->srv_time - $data->hold_time;
                        $data->aht = $data->answered > 0 ? round(($data->talk_time + $data->hold_time + $data->rtime+$data->wrap_time)/$data->answered) : 0;
                        $data->agent_hangup = ($data->agent_hangup == 1) ? 'Yes' : 'No';
                        $data->transfer_to = (!empty($data->transfer_to ) && !empty($transfer_types[$data->transfer_to]) )? $transfer_types[$data->transfer_to] : $data->transfer_to;

                        if($this->gridRequest->isDownloadCSV){ // for download
                            // $data->wrap_time = '';
                            $data->aht = $data->answered > 0 ? ($data->talk_time + $data->hold_time + $data->rtime+$data->wrap_time)/$data->answered : 0;
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
                        }
                    }
                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                        $final_row->skill_name = "-";
                        $final_row->msisdn = '-';
                        // $final_row->wrap_time = "-";
                        $final_row->agent_hangup = "-";
                        // $final_row->delay_between_call = $final_row->answered*DELAY_BETWEEN_CALLS;
                        $final_row->talk_time = $final_row->srv_time - $final_row->hold_time;
                        $final_row->aht = $final_row->answered > 0 ? round(($final_row->talk_time + $final_row->hold_time + $final_row->rtime+$final_row->wrap_time)/$final_row->answered) : 0;
                        $final_row->transfer_to = '-';

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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportIceDetails()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $agent_id = $this->gridRequest->getMultiParam('agent_id');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";

            $skill_type = $this->gridRequest->getMultiParam('skill_type');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $agent_names = $agent_model->getAgentsFullName();

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
            $dispositions =  $report_model->get_disposition_as_key_value();

            // calculate total count
            $this->pagination->num_records = $report_model->numIceDetailsReport($dateinfo, $skill_ids, $agent_id, $skill_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'agent_id' => $agent_id,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::ICE Details', $request_param);
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
                $report_model->saveReportAuditRequest('NRS::ICE Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getIceDetailsReport($dateinfo, $skill_ids, $agent_id, $skill_type, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getIceDetailsReport($dateinfo, $skill_ids, $agent_id, $skill_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format.' H:i:s', strtotime($data->call_start_time));
                        $data->msisdn_880 = !empty($data->cli) ? '880'.substr($data->cli, -10) : '';
                        $data->cli = substr($data->cli, -10);
                        $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                        $data->agent_name = isset($agent_names[$data->agent_id]) ? $agent_names[$data->agent_id] : $data->agent_id;
                        if(isset($data->ice_feedback) && ($data->ice_feedback == 'Y')){
                            $data->ice_value = "100%";
                        }else if(isset($data->ice_feedback) && ($data->ice_feedback == 'N')){
                            $data->ice_value = "0%";
                        }else{
                            $data->ice_value = "";
                        }

                        if($this->gridRequest->isDownloadCSV){ // for download
                            $data->custom_title = $dispositions[$data->disposition_id];
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
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportIceSummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $date_format = get_report_date_format();
            $report_type = $this->gridRequest->getMultiParam('report_type');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();
            // var_dump($skills_types);

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

            // calculate total count
            $this->pagination->num_records = $report_model->numIceSummaryReport($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getIceSummaryReport($dateinfo, $skill_ids, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => &$data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->category = '-';
                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->ice_score = (!empty($data->ice_count)) ? fractionFormat(($data->ice_positive_count / $data->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    if (empty($data->ice_sent)) {
                        $data->ice_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->ice_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    } else {
                        $data->ice_percentage = (!empty($data->ice_sent)) ? fractionFormat(($data->ice_count / $data->ice_sent) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }

                    $data->sdate = date($date_format, strtotime($data->sdate));
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getIceSummaryReport($dateinfo, $skill_ids, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    $final_row->ice_score = (!empty($final_row->ice_count)) ? fractionFormat(($final_row->ice_positive_count / $final_row->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    if (empty($final_row->ice_sent)){
                        $final_row->ice_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->ice_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    } else {
                        $final_row->ice_percentage = (!empty($final_row->ice_sent)) ? fractionFormat(($final_row->ice_count / $final_row->ice_sent) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }
                }
                $response->userdata = $final_row;

            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                // $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                // $response->showCol = ['sdate', 'hour_minute'];
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'report_type' => $report_type,
                'skill_type' => $skill_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            $this->reportAudit('NR', 'ICE Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionReportTopReasonsDissatisfaction()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
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
            $dispositions =  $report_model->get_disposition_as_key_value();
            $total_neg = 0;

            // calculate total count
            $this->pagination->num_records = $report_model->numTopReasonsDissatisfaction($dateinfo, $skill_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'skill_type' => $skill_type
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Top Reasons Dissatisfaction', $request_param);

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
                // $report_model->saveReportAuditRequest('NRS::Top Reasons Dissatisfaction', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getTopReasonsDissatisfaction($dateinfo, $skill_type, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getTopReasonsDissatisfaction($dateinfo, $skill_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format, strtotime($data->sdate));
                        $data->title = isset($dispositions[$data->disposition_id]) ? $dispositions[$data->disposition_id] : $data->disposition_id;
                        $total_neg += $data->dis_ice_negative;


                        if($this->gridRequest->isDownloadCSV){ // for download
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
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getTopReasonsDissatisfaction($dateinfo, $skill_type, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->title = "-";
                    }
                    $response->userdata = $final_row;

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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportTopAgentsDissatisfaction()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
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
            $agent_names = $agent_model->getAgentsFullName();
            $total_neg = 0;

            // calculate total count
            $this->pagination->num_records = $report_model->numTopAgentsDissatisfaction($dateinfo, $skill_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'skill_type' => $skill_type
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Top Agents Dissatisfaction', $request_param);

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
                // $report_model->saveReportAuditRequest('NRS::Top Agents Dissatisfaction', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getTopAgentsDissatisfaction($dateinfo, $skill_type, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getTopAgentsDissatisfaction($dateinfo, $skill_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format, strtotime($data->sdate));
                        $data->agent_name = isset($agent_names[$data->agent_id]) ? $agent_names[$data->agent_id] : $data->agent_id;
                        $total_neg += $data->ag_ice_negative;

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
                    }

                    if(!$this->gridRequest->isDownloadCSV){
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getTopAgentsDissatisfaction($dateinfo, $skill_type, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->agent_id = "-";
                        $final_row->agent_name = "-";
                    }
                    $response->userdata = $final_row;

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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportCategoryDetails2()
    {
        // die();
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $disposition_type_list = disposition_type_list();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '';
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            $hangup_initiator = $this->gridRequest->getMultiParam('hangup_initiator');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $disposition_id = $this->gridRequest->getMultiParam('dispositions_ids');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        // GPrint($dateinfo);
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

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
            $dispositions =  $report_model->get_disposition_all_value();
            // GPrint($dispositions[1145]);

            // calculate total count
            $query_param = [
                "dateinfo" => $dateinfo,
                "skill_ids" => $skill_ids,
                // "report_type" => $report_type,
                "skill_type" => $skill_type,
                "hangup_initiator" => $hangup_initiator,
                "disposition_id" => $disposition_id,
                "isSum" => false,
                "multiple_wrap_up" => false,
            ];
            $this->pagination->num_records = $report_model->numCategoriesDetailsReport($query_param);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            // $request_param = [
            //     'sdate' => $datetime_range['from'],
            //     'edate' => $datetime_range['to'],
            //     'skill_type' => $skill_type,
            //     'hangup_initiator' => $hangup_initiator,
            //     'skill_id' => (!empty($this->gridRequest->getMultiParam('skill_id'))) ? $this->gridRequest->getMultiParam('skill_id') : '',
            // ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Skill Details', $this->gridRequest->getMultiParam());

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
                $report_model->saveReportAuditRequest('NRS::Skill Details', $this->gridRequest->getMultiParam());
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $query_param["isSum"] = false;
                    $query_param["multiple_wrap_up"] = true;
                    $result = $report_model->getCategoriesDetailsReport($query_param, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $query_param["isSum"] = false;
                    $query_param["multiple_wrap_up"] = false;
                    $result = $this->pagination->num_records > 0 ? $report_model->getCategoriesDetailsReport($query_param, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

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
                        // GPrint($data->disposition_id);
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
                        // $data->delay_between_call = ($data->status == 'S') ? DELAY_BETWEEN_CALLS : 0;

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                        $query_param["isSum"] = true;
                        $query_param["multiple_wrap_up"] = false;
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getCategoriesDetailsReport($query_param, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->sdate = "-";
                        $final_row->skill_id = "-";
                        $final_row->skill_name = "-";
                        $final_row->msisdn_880 = "-";
                        $final_row->cli = "-";
                        $final_row->abandon_flag = "-";
                        $final_row->abandon_cli = "-";
                        $final_row->agent_id = "-";
                        $final_row->title = '-';
                        $final_row->qrc_tagging = '-';
                        $final_row->qrc_type_tagging = '-';
                        $final_row->short_call = '-';
                        $final_row->repeated_call_flag = '-';
                        $final_row->disc_party = '-';
                        $final_row->ice_feedback = '-';

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
                $multiple_disposition = count($callIdArr) > 0 ? $report_model->getMultipleDisposition($callIdStr) : null;
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
                            if($result[$result_key]->disposition_count > 0)
                                $result[$result_key]->custom_title = $result[$result_key]->title.' <a onclick="showDispostionModal(\''.implode(',', $call_id_disposition[$value->callid]).'\')" class="show-multiple-disposition" ><i class="fa fa-arrow-circle-right" style="cursor: pointer;" aria-hidden="true"></i></a>';
                        }else{
                            if($result[$result_key]->disposition_count > 0)
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

    function actionIvrSummary()
    {
        AddModel('MReportNew');
        AddModel('MIvr');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $ivr_model = new MIvr();
        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $ivr_id =  '*';
        $footer_row = [];
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');

            $date_format = $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);

        if(empty($dateinfo->errMsg)){
            $this->pagination->num_records = $report_model->numIvrSummary($dateinfo, $ivr_id);

            $result = $this->pagination->num_records > 0 ? $report_model->getIvrSummary($dateinfo, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            if(is_array($result) && count($result) > 0)
            {
                $ivr_list = $ivr_model->getIvrOptions();
                foreach ( $result as &$data )
                {
                    $data->ivr_ratio = !empty($data->total_hit) ? fractionFormat(($data->ivr_only / $data->total_hit) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->agent_only = !empty($data->total_hit) ? ($data->total_hit - $data->ivr_only) : '';
                    $data->ivr_name = isset($ivr_list[$data->ivr_id]) ? $ivr_list[$data->ivr_id] : '';
                    $data->avg_time_in_ivr = !empty($data->total_hit) ? fractionFormat(($data->time_in_ivr / $data->total_hit), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';

                    $data->sdate = date($date_format, strtotime($data->sdate));
                }

                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getIvrSummary($dateinfo, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->ivr_name = "-";

                    $final_row->ivr_ratio = !empty($final_row->total_hit) ? fractionFormat(($final_row->ivr_only / $final_row->total_hit) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->agent_only = !empty($final_row->total_hit) ? ($final_row->total_hit - $final_row->ivr_only) : '';
                    $final_row->avg_time_in_ivr = !empty($final_row->total_hit) ? fractionFormat(($final_row->time_in_ivr / $final_row->total_hit), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';

                    $response->userdata = $final_row;
                }
            }

            $response->rowdata = $result;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'ivr_id' => $ivr_id
            ];
            $this->reportAudit('NR', 'IVR Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionIvrDetails()
    {
        AddModel('MReportNew');
        AddModel('MIvr');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $ivr_type = '*';
        $ivr_id =  '*';
        $footer_row = [];
        $fraction_format = "%.2f";
        $footer_row = new stdClass();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = $date_format = get_report_date_format();
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
            $ivr_type = $this->gridRequest->getMultiParam('ivr_type', '*');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
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

            $this->pagination->num_records = $report_model->numIvrDetails($dateinfo, $ivr_type, $ivr_id);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'ivr_id' => $ivr_id,
                'ivr_type' => $ivr_type
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::IVR Details', $request_param);

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
                // $report_model->saveReportAuditRequest('NRS::IVR Details', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getIvrDetails($dateinfo, $ivr_type, $ivr_id, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getIvrDetails($dateinfo, $ivr_type, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format." H:i:s", strtotime($data->call_start_time));
                        $data->stop_time = date($date_format." H:i:s", strtotime($data->new_tstamp));
                        $data->enter_time = date($date_format." H:i:s", strtotime($data->enter_time));

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                        }
                    }
                    if(!$this->gridRequest->isDownloadCSV){ // for grid view
                        // last row initialization
                        $final_row = $this->pagination->num_records > 0 ? $report_model->getIvrDetails($dateinfo, $ivr_type, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                        $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                        $final_row->call_start_time = '-';
                        $final_row->stop_time = '-';
                        $final_row->cli = '-';
                        $final_row->did = '-';
                        $final_row->enter_time = '-';

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

                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportAgentIceSummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $report_model = new MReportNew();

        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $date_format = get_report_date_format();
            $report_type = $this->gridRequest->getMultiParam('report_type');
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        // calculate total count
        $this->pagination->num_records = $report_model->numAgentIceSummaryReport($dateinfo, $agent_id, $report_type);
        $result = $this->pagination->num_records > 0 ? $report_model->getAgentIceSummaryReport($dateinfo, $agent_id, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if ($report_type == REPORT_MONTHLY) {
            $response->hideCol = ['sdate'];
            $response->showCol = ['smonth'];
        } elseif ($report_type == REPORT_DAILY) {
            $response->hideCol = ['smonth'];
            $response->showCol = ['sdate'];
        }

        if(!empty($result) && count($result) > 0){
            foreach ($result as $key => &$data) {
                $data->ice_count = $data->ice_feedback_yes + $data->ice_feedback_no;
                $data->ice_score = (!empty($data->ice_count)) ? fractionFormat(($data->ice_feedback_yes / $data->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                $data->ice_percentage = (!empty($data->call_ans)) ? fractionFormat(($data->ice_count / $data->call_ans) * 100, $fraction_format, $this->gridRequest->isDownloadCSV) . '%' : '0.00%';
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->agent_name = $agent_model->getAgentById($data->agent_id)->name;
            }
            if(!$this->gridRequest->isDownloadCSV){
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getAgentIceSummaryReport($dateinfo, $agent_id, $report_type, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->sdate = "-";
                $final_row->smonth = "-";
                $final_row->agent_id = "-";
                $final_row->ice_score = (!empty($final_row->ice_count)) ? fractionFormat(($final_row->ice_feedback_yes / $final_row->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                $final_row->ice_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->ice_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
            }
            $response->userdata = $final_row;

        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionReportWebChatDetails()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $dispositions =  $report_model->get_webchat_disposition_all_value();
            $agent_options = $agent_model->getAgentsFullName();
            $this->pagination->num_records = $report_model->numWebChatDetailsData($dateInfo, $skill_id);

            $result = $this->pagination->num_records > 0 ?
                $report_model->getWebChatDetailsData($dateInfo, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            if (count($result) > 0) {
                foreach ($result as $data) {
                    $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;

                    $data->handling_time = !empty($data->stop_time) ? strtotime($data->stop_time)-strtotime($data->agent_response_time) : null;
                    $data->first_respond_duration = !empty($data->agent_first_response) && $data->agent_first_response != "0000-00-00 00:00:00" ? strtotime($data->agent_first_response)-strtotime($data->agent_response_time) : null;
                    $data->agent_first_response = $data->agent_first_response != "0000-00-00 00:00:00" ? $data->agent_first_response : null;

                    $data->sdate = date($date_format." H:i:s", strtotime($data->sdate));
                    if(!empty($data->agent_response_time)){
                        $data->agent_response_time = date($date_format . " H:i:s", strtotime($data->agent_response_time));
                    }
                    $data->stop_time = date($date_format . " H:i:s", strtotime($data->stop_time));

                    $data->aht = ($data->status == 'S') ? $data->service_time+$data->wrap_up_time : 0;
                    $data->aht_min = gmdate("H:i:s", $data->aht);

                    $data->reason = $dispositions[$data->disposition_id]['title'];
                    $data->agent_name = $agent_options[$data->agent_id];

                    $file_timestamp = substr($data->callid, 0, 10);
                    $yyyy = date("Y", $file_timestamp);
                    $yy = substr($yyyy, 2, 2);
                    $yyyy_mm_dd = date("Y_m_d", $file_timestamp);
                    $mm = substr($yyyy_mm_dd, 5, 2);
                    $dd = substr($yyyy_mm_dd, 8, 2);

                    $sip_path = $this->getTemplate()->sip_logger_path;

                    $sip_file = $sip_path . "message/$yy/$mm/$dd/" . $data->callid . ".sip";

                    if (file_exists($sip_file)) {
                        $data->sip_log = " <a title='Chat Log' data-h='400px' data-w='600px' class='lightboxWIFR' href=\"index.php?task=cdr&act=sip-log&cid=$data->callid\"><i class=\"fa fa-file\"></i></a>";
                    } else {
                        $data->sip_log = "NONE";
                    }
					
					//if(empty($data->agent_first_response) && $data->sip_log == "NONE"){
					//	$data->status = 'A';
					//}
					//var_dump($data->agent_first_response);
					if(empty($data->agent_first_response)){
						$data->status = 'A';
					}

                    if ($data->is_verified == "Y") {
                        $data->is_verified = "Yes";
                    } else {
                        $data->is_verified = "No";
                    }

                    if ($data->customer_feedback === '0') {
                        $data->customer_feedback = "Bad";
                    } elseif ($data->customer_feedback === '5') {
                        $data->customer_feedback = "Good";
                    } else if ($data->customer_feedback === null) {
                        $data->customer_feedback = "";
                    }

                    if ($data->status != S) {
                        if($data->hold_in_q <=60){
                            $data->abandon_flag = "Yes";
                            $data->abandon_af_60 = "No";
                        }else{
                            $data->abandon_flag = "No";
                            $data->abandon_af_60 = "Yes";
                        }
						
                        $data->sl = '';
                        $data->service_time_min = '';
                        $data->service_time = '';
                        $data->stop_time = '';
                        $data->wait_time = gmdate("H:i:s", $data->hold_in_q);
                        $data->handling_time = '';
						$data->agent_name = '';
						$data->agent_id = '';
						$data->reason = '';
						$data->customer_feedback = '';
						$data->agent_response_time = '';
                    } else {
                        $data->abandon_flag = "No";
                        $data->abandon_af_60 = "No";
						
                        if (!empty($data->first_respond_duration)) {
                            $data->sl =  $data->first_respond_duration <= WEB_CHAT_SL_TIME ? "100%" : "0%";
							$data->wait_time = gmdate("H:i:s", $data->hold_in_q);
                        } else {
                            $data->sl = $data->wait_time <= WEB_CHAT_SL_TIME ? "100%" : "0%";
							$data->wait_time = gmdate("H:i:s", $data->wait_time);
                        }

                        /*
                         * Manually By Sanaul
                         */
                        if (in_array($data->callid, webchat_temporary_callid)){
                            $data->sl = "100%";
                        }

                        $data->service_time_min = gmdate("H:i:s", $data->service_time);
                        //$data->wait_time = gmdate("H:i:s", $data->wait_time);
                        $data->handling_time = gmdate("H:i:s", $data->handling_time);
                        $data->first_respond_duration = gmdate("H:i:s", $data->first_respond_duration);
                    }
                    if ($data->reason == "Blank" || $data->disc_party == "S"){
                        $data->customer_feedback = "";
                    }
					$data->disc_party = !empty($data->disc_party) ? get_disc_party($data->disc_party) : "";
					if ($data->service_time_min < "00:02:00") { //no ice feedback for chat duration less than 120 seconds
                        $data->customer_feedback = "";
                    }
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ?
                    $report_model->getWebChatDetailsData($dateInfo, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->skill_id = '';
                    $final_row->agent_id = '';
                    $final_row->agent_name = '';
                    $final_row->customer_number = '';
                    $final_row->customer_name = '';
                    $final_row->agent_response_time = '';
                    $final_row->stop_time = '';
                    $final_row->SL = '';
                    $final_row->reason = '';
                    $final_row->aht = '';
                    $final_row->service_time_min = gmdate("H:i:s", $final_row->service_time);
                    $final_row->sip_log = '';
                    $final_row->sdate = '';
                    $final_row->service_time = '';
                    $final_row->customer_feedback = '';
                    $final_row->wait_time = gmdate("H:i:s", $final_row->wait_time+$final_row->abd_hold_in_q);
                    $final_row->is_verified = '';
                    $final_row->agent_first_response = '';
                }

                //$response->userdata = $final_row;
            }
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_id' => $skill_id
            ];
            $this->reportAudit('NR', 'Web Chat Details', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionReportQa()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
//        $score_comparison = $this->gridRequest->getMultiParam('score_comparison');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numQaReportData($date_info, $agent_score, $score_comparison);

            $results = $this->pagination->num_records > 0 ?
                $report_model->getQaReportData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(INBOUND_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    $result->sdate = date($date_format." H:i:s",strtotime($result->sdate));
                    $result->evaluation_time = date($date_format." H:i:s",strtotime($result->evaluation_time));

                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {

                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }

                            /*if ($form_value->percentage_value != null) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $form_value->percentage_value;
                            }*/
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                        /*if ($form_value->field_name == "comments") {
                            $result->{$form_value->field_name} = $form_value->field_value;
                        }*/
                    }
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'Inbound QA', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionReportChatDisposition(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();

        $date_format = get_report_date_format();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $disposition = $this->gridRequest->getMultiParam('disposition_id');
        if(empty($disposition)) $disposition = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numChatRecordCount($disposition, $date_info);
            $callsData = $this->pagination->num_records > 0 ? $report_model->getChatRecordCount($disposition, $date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $total_crm_records = $this->pagination->num_records > 0 ? $report_model->getTotalCrmRecordCount($disposition, $date_info) : 0;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            $result=&$callsData;

            if(count($result) > 0){
                foreach ( $result as &$data ) {
                    //$data->percentage = $total_crm_records > 0 ? sprintf("%2d", $data->numrecords*100/$total_crm_records) : '-';
                    $data->percentage = $total_crm_records > 0 ? fractionFormat(($data->numrecords / $total_crm_records) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                }
            }
            $response->rowdata = $result;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'disposition' => $disposition
            ];
            $this->reportAudit('NR', 'Web Chat Disposition', $request_param);
            $this->ShowTableResponse();
        }
    }


    public function actionAgentPerformanceSummary()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $report_model = new MReportNew();

        $date_format = get_report_date_format();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');

        $sdate = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['from']),'Y-m-d H:i') : date('Y-m-d H:i');
        $edate = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['to']),'Y-m-d H:i') : date('Y-m-d H:i');

        $sdate = $sdate.':00';
        $edate = date('Y-m-d H:i:s', strtotime(" $edate - 1 second"));

        $report_daterange_diff = strtotime($edate) - strtotime($sdate);
        if ($report_daterange_diff > (3600 * 24)){
            $edate = date("Y-m-d H:i:s", strtotime($sdate. " + 24 hours"));
        }

        $selected_date_for_report = $sdate. "<b> To </b>".$edate;

        $date_info = new stdClass();
        $date_info->sdate = $sdate;
        $date_info->edate = $edate;

        $this->pagination->num_records = $report_model->numAgentPerformanceSummary($date_info);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary($date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        if (is_array($result) && count($result) > 0) {
            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $date_info);

                $data->first_login =  date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->not_ready_time =  $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->staffed_time > $agentinfo->not_ready_time ? $agentinfo->staffed_time - $data->not_ready_time /*($data->ring_time + $data->talk_time + $data->wrap_up_time ) */: 0;

                $data->login_time = gmdate("H:i:s", $agentinfo->staffed_time);
                $data->total_idle_time = $agentinfo->staffed_time - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : 0;
                $data->logout_time =  empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->not_ready_count = $agentinfo->not_ready_count;

                $data->agent_info = $agentinfo;

                $data->aht = $data->calls_answered > 0 ? round(($data->ring_time + $data->talk_time + $data->hold_time + $data->wrap_up_time) / $data->calls_answered) : 0;
                $data->workcode_percent = $data->calls_answered > 0 ? round(($data->workcode_count / $data->calls_answered) * 100,2) : 0;
                $data->workcode_percent .= '%';
                $data->fcr_percent = $data->calls_answered > 0 ? round(($data->fcr_call / $data->calls_answered) * 100,2) : 0;
                $data->fcr_percent .= '%';
                $data->repeated_percent = $data->calls_answered > 0 ? round(($data->repeated_call / $data->calls_answered) * 100,2) : 0;
                $data->repeated_percent .= '%';
                if(in_array($data->skill_set, ['EM-AT-CE, WC-AT-GEN', 'EM-AT-CE', 'WC-AT-GEN', 'WC-AT-GEN, EM-AT-CE']))
                    $data->ring_time = 0;
            }
        }

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Agent Performance', $request_param);
        $this->ShowTableResponse();

    }

    function actionReportIceRawData()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $this->pagination->num_records = 0;
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

            // calculate total count
            $this->pagination->num_records = $report_model->numIceRawDataReport($dateinfo);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            // download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to']
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::ICE Raw Data', $request_param);

                error_reporting(0);
                header('Content-Encoding: UTF-8');
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachement; filename="'.$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv".'";');
                header("Content-Transfer-Encoding: UTF-8");
                echo "\xEF\xBB\xBF";

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
                // $report_model->saveReportAuditRequest('NRS::ICE Raw Data', $request_param);
            }

            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getIceRawDataReport($dateinfo, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getIceRawDataReport($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format.' H:i:s', strtotime($data->log_time));
                        // $data->msisdn_880 = !empty($data->cli) ? '880'.substr($data->cli, -10) : '';
                        $data->cli = $data->cli; //substr($data->cli, -10);
                        // $data->sms_text = base_convert($data->text_b, 2, 10);

                        if($this->gridRequest->isDownloadCSV){ // for download
                            $row=array();
                            foreach ($cols as $key=>$value){
                                $rvalue="";
                                if($isRemoveTag){
                                    if(isset($data->$key)){
                                        $rvalue=strip_tags($data->$key);
                                    }
                                }else{
                                    if(isset($data->$key)){
                                        $rvalue=$data->$key;
                                    }else{
                                        $rvalue="";
                                    }
                                }
                                $rvalue=preg_replace("/&.*?; /", "", $rvalue);
                                // $rvalue=mb_convert_encoding($rvalue, 'UTF-16LE', 'UTF-8');
                                array_push($row, $rvalue);
                            }
                            fputcsv($f, $row, $delimiter);
                            // var_dump($row);

                            $fileInputRowCount++;
                        }
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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionReportEmailActivity(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        $eTicket_model = new MReportNew();
        $agent_model = new MAgent();

        $agentId = "";
        $status = $ticket_id = $supervisor_id = "";
        $date_format = get_report_date_format();

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('activity_time');
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format . " H:i", $datetime_range['from']), 'Y-m-d') : date('Y-m-d',strtotime("-1 month"));
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format . " H:i", $datetime_range['to']), 'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $datetime_range['from']), 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $datetime_range['to']), 'Y-m-d H:i'))) : "23";
            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $status = $this->gridRequest->getMultiParam('status');
            $ticket_id = $this->gridRequest->getMultiParam('ticket_id');
        }

        $dateTimeInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');
        $utype = UserAuth::hasRole('agent');
        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }
        if (UserAuth::hasRole('supervisor')){
            $supervisor_id = UserAuth::getCurrentUser();
            if (empty($agentId) ){
                $temp_agent_id = array_keys($agent_model->getEmailAgents($supervisor_id));
                $agentId = implode("','", $temp_agent_id);
            }
        }
        //GPrint($agentId);die;
        $this->pagination->num_records = $eTicket_model->numEmailActivityReport( $agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id);
        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            $email_agents = [];
            foreach ($result as $key){
                if (!in_array($key->agent_id,$email_agents) && !empty($key->agent_id)){
                    $email_agents[] = $key->agent_id;
                }
            }

            $temp_agent_names = !empty($email_agents) ? $agent_model->getAllAgents($email_agents) : "";
            if (!empty($temp_agent_names)){
                foreach ($temp_agent_names as $item){
                    $names[$item->agent_id] = $item->name;
                }
            }

            foreach ( $result as &$data ) {
                $data->activity_time = date($date_format . " H:i:s", $data->activity_time);
                $data->emailStatus = '';
                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->activity_details);
                $data->agent_id = !empty($names[$data->agent_id]) ? $names[$data->agent_id].' - '.$data->agent_id : $data->agent_id;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }


    public function actionAgentSessionDetails()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();

        $date_format = get_report_date_format();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');

        $sdate = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['from']),'Y-m-d H:i') : date('Y-m-d H:i');
        $edate = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['to']),'Y-m-d H:i') : date('Y-m-d H:i');
        $agent_id = $this->gridRequest->getMultiParam('agent_id','');
        $agent_names = $agent_model->getAgentsFullName();

        $sdate = $sdate.':00';
        $edate = $edate.":00";

        $report_daterange_diff = strtotime($edate) - strtotime($sdate);
        if ($report_daterange_diff > (3600 * 24 * 1)){
            $edate = date("Y-m-d H:i:s", strtotime("+ 1 days", strtotime($sdate)));
        }

        $date_info = new stdClass();
        $date_info->sdate = $sdate;
        $date_info->edate = $edate;

        $this->pagination->num_records = $report_model->numAgentSessionDetailsReport($agent_id, $date_info);

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentSessionDetailsReport($agent_id, $date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        if (is_array($result) && count($result) > 0) {
            $status_list = ['I' => '<b class="text-success">Login</b>', 'R'=>'<b class="text-primary">Ready</b>', 'X'=>'<b class="text-warning">Busy</b>', 'O'=>'<b class="text-danger"> Logout </b>'];
            foreach ($result as &$data){
                $data->agent_name = $agent_names[$data->agent_id];
                $data->tstamp = date($date_format." H:i:s", strtotime($data->tstamp));
                $data->type = !empty($status_list[$data->type]) ? $status_list[$data->type] : $data->type;
            }
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate,
            'agent_id' => $agent_id
        ];
        $this->reportAudit('NR', 'Agent Session Details', $request_param);
        $this->ShowTableResponse();

    }

    public function actionReportDailyIvrHitSummary()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }


        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date("Y-m-d",strtotime("+1 day"));
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $results = $report_model->getDailyIvrHitSummaryData($dateInfo, $ivr_id);

            $parent_nodes = $report_model->getParentIvrNodes();
            $child_nodes = $report_model->getChildIvrNodes();

            $total_hit_count = 0;
            $final_result = array();
            if (count($results) > 0) {
                foreach ($parent_nodes as $parent_node) {
                    $data = new stdClass();
                    $data->service_title = $parent_node->service_title;
                    $data->hit_count = 0;
                    $has_data = false;
                    $branch_nodes = $this->getBranchNodes($child_nodes, $parent_node);
                    foreach ($results as $result) {
                        if  ($this->isChildInfoNode($branch_nodes, $result->trace_id)) {
                            $data->hit_count += $result->hit_count;
                            $has_data = true;
                        }
//                        else if ($result->trace_id == $parent_node->disposition_code) {
//                            $data->hit_count += $result->hit_count;
//                            $has_data = true;
//                        }
                    }
                    if ($ivr_id == "") {
                        array_push($final_result, $data);
                    } else if ($has_data) {
                        array_push($final_result, $data);
                    }
                }
                foreach ($final_result as $result) {
                    $total_hit_count += $result->hit_count;
                }

                usort($final_result, function ($first, $second) { // sort the result
                    return $first->hit_count < $second->hit_count;
                });

                $rank = 0;
                $previous_hit = null;
                foreach ($final_result as $final_data) {
                    if ($total_hit_count != 0) {
                        $final_data->percentage = sprintf("%.02f", ($final_data->hit_count / $total_hit_count) * 100) . "%";
                    }
                    if ($final_data->hit_count != $previous_hit) {
                        $rank++;
                    }
                    $final_data->rank = $rank;
                    $previous_hit = $final_data->hit_count;
                }
            }
            if (!$this->gridRequest->isDownloadCSV) {
                $final_row = new stdClass();
                $final_row->service_title = "-";
                $final_row->hit_count = $total_hit_count;
                $final_row->percentage = "100%";
                $final_row->rank = "-";
            }

            $response = $this->getTableResponse();

            $response->userdata = $final_row;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->records = $this->pagination->num_records;
            $response->rowdata = $final_result;
            $request_param = [
                'sdate' => $dial_time['from'],
                'edate' => $dial_time['to'],
                'ivr_id' => $ivr_id
            ];
            $this->reportAudit('NR', 'Daily IVR Hit Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    private function isChildInfoNode($child_nodes, $node_id)
    {
        foreach ($child_nodes as $child_node) {
            if ($child_node->disposition_code == $node_id && $child_node->report_type == 'I') {
                return true;
            }
        }
        return false;
    }

    private function getBranchNodes($child_nodes, $parent_node, $bucket = array())
    {
        foreach ($child_nodes as $child_node) {
            if ($parent_node->disposition_code == $child_node->parent_id) {
                $bucket = $this->getBranchNodes($child_nodes, $child_node, $bucket);
            }
        }
        $bucket[] = $parent_node;
        return $bucket;
    }

    public function actionReportDailyIvrHitCount()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date("Y-m-d",strtotime("+1 day"));
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);

        if(empty($dateInfo->errMsg)){
            $results = $report_model->getDailyIvrHitCountData($dateInfo,$ivr_id);
            $child_nodes = array_merge($report_model->getChildIvrNodes(), $report_model->getParentIvrNodes());

            $total_hit_count = 0;
            $final_results = array();
            if (count($results) > 0) {
                foreach ($child_nodes as $child_node) {
                    $data = new stdClass();
                    $hasHit = false;
                    $data->service_title = $child_node->service_title;
                    $data->integration_info = $this->getIntegrationInfoOfNode($child_node);
                    foreach ($results as $result) {
                        if ($result->trace_id == $child_node->disposition_code) {
                            $data->hit_count = $result->hit_count;
                            $hasHit = true;
                            break;
                        }
                    }
                    if ( $ivr_id == "") {
                        if($hasHit == false){
                            $data->hit_count = 0;
                        }
                        array_push($final_results, $data);
                    }elseif ($hasHit == true && $ivr_id != "*"){
                        array_push($final_results, $data);
                    }

                }
                usort($final_results, function ($first, $second) { // sort the result
                    return $first->hit_count < $second->hit_count;
                });

                $rank = 0;
                $temp = null;
                foreach ($final_results as $data) {
                    if ($data->hit_count != $temp) {
                        $rank++;
                    }
                    $data->rank = $rank;
                    $temp = $data->hit_count;
                }

                foreach ($final_results as $result) {
                    $total_hit_count += $result->hit_count;
                }

                foreach ($final_results as $result) {
                    if ($total_hit_count != 0) {
                        $result->percentage = sprintf("%.02f", ($result->hit_count / $total_hit_count) * 100) . " %";
                    }
                }
            }

            if (!$this->gridRequest->isDownloadCSV) {
                $final_row = new stdClass();
                $final_row->service_title = "-";
                $final_row->hit_count = $total_hit_count;
                $final_row->percentage = 100 . "%";
                $final_row->rank = "-";
            }

            $response = $this->getTableResponse();
            $response->userdata = $final_row;
            //        $response->records = $this->pagination->num_records;
            $response->rowdata = $final_results;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $dial_time['from'],
                'edate' => $dial_time['to'],
                'ivr_id' => $ivr_id
            ];
            $this->reportAudit('NR', 'Daily IVR Hit Count', $request_param);
            $this->ShowTableResponse();
        }
    }

    private function getIntegrationInfoOfNode($node)
    {
        $info = '';

        if ($node->report_type == "M") {
            $info .= 'Menu';
        } elseif ($node->report_type == "I") {
            $info .= 'Info';
        }
        if ($node->is_sms_served == "Y") {
            $info .= '-Text';
        }
        return $info;
    }

    function getDispositionName($result){
        $response = array();
        $disp = array();
        if (!empty($result)){
            foreach ($result as $key)$disp[] = $key->disposition_id;
            if (!empty($disp)){
                $eTicket_model = new MEmail();
                $response = $eTicket_model->getEmailDispositionName($disp);
            }
        }
        return $response;
    }
    function getEmailDate($date_range, $date_format, $report_restriction_days=EMAIL_SUMMARY_REPORT_DAY){
        $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : "";
        $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : "";
        $hour_from = !empty($date_range['from']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $date_range['from']), 'Y-m-d H:i'))) : "00";
        $hour_to = !empty($date_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $date_range['to']), 'Y-m-d H:i'))) : "23";
        if (!empty($date_from) && !empty($date_to))
            return $dateTimeInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);

        return null;
    }
    function actionEmailAgentReport () {
        include('lib/DateHelper.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');

        $reportnew_model = new MReportNew();
        $eTicket_model = new MEmail();
        //$dp_options = $eTicket_model->getDispositionTreeOptions();
        $dp_options = $eTicket_model->getAllEmailDispositions();
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = EMAIL_SUMMARY_REPORT_DAY;
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $emptyDate = false;
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $tid = $email = $did = $agentId = $assignedId = $sender_name = $subject = $customer_id = $waiting_duration = $skill_id = $in_kpi = $from_email = '';

        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
            $emptyDate = true;
        }

        $dateTimeArray = array();
        $lastDateArray = array();
        $firstOpenDateArray = array();
        $closedDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            $firstOpenDateArray = $this->gridRequest->getMultiParam('first_open_time');
            $closedDateArray = $this->gridRequest->getMultiParam('close_time');
        }
        $dateTimeInfo = $this->getEmailDate($dateTimeArray, $date_format, $report_restriction_days);
        $lastDateInfo = $this->getEmailDate($lastDateArray, $date_format, $report_restriction_days);
        $firstopenDateInfo = $this->getEmailDate($firstOpenDateArray, $date_format, $report_restriction_days);
        $closedDateInfo = $this->getEmailDate($closedDateArray, $date_format, $report_restriction_days);

        if ($this->gridRequest->isMultisearch){
            $customer_id = $this->gridRequest->getMultiParam('customer_id');
            $waiting_mins = $this->gridRequest->getMultiParam('waiting_duration');
            $waiting_mins = explode(":",$waiting_mins);
            if (!empty($waiting_mins[0])) $waiting_duration = $waiting_mins[0]*60;
            if (!empty($waiting_mins[1])) $waiting_duration += $waiting_mins[1];
            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $in_kpi = $this->gridRequest->getMultiParam('in_kpi');
            $did = $this->gridRequest->getMultiParam('did');
            $status = $this->gridRequest->getMultiParam('status');
            $subject = $this->gridRequest->getMultiParam('subject');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $from_email = $this->gridRequest->getMultiParam('from_email');
        }
        $utype = UserAuth::hasRole('agent');
        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }
        $isAgent = UserAuth::hasRole('agent');
        $this->pagination->num_records = $reportnew_model->numEmailAgentReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id, $from_email);

        $eTickets = $this->pagination->num_records > 0 ?
            $reportnew_model->getEmailAgentReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id, $from_email, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        $result=&$eTickets;
        $email_agents = [];
        $names = [];
        if(count($result) > 0){
            $disposition_names = $this->getDispositionName($result);
            foreach ($result as $key){
                if (!in_array($key->agent_1,$email_agents) && !empty($key->agent_1)){
                    $email_agents[] = $key->agent_1;
                }
                if (!in_array($key->agent_2,$email_agents) && !empty($key->agent_2)){
                    $email_agents[] = $key->agent_2;
                }
                if (!in_array($key->agent_3,$email_agents) && !empty($key->agent_3)){
                    $email_agents[] = $key->agent_3;
                }
            }
            $agent_model = new MAgent();
            $temp_agent_names = !empty($email_agents) ? $agent_model->getAllAgents($email_agents) : "";
            if (!empty($temp_agent_names)){
                foreach ($temp_agent_names as $item){
                    $names[$item->agent_id] = $item->name;
                }
            }

            foreach ( $result as &$data ) {
                $sdate = $data->create_time;
                $edate = $data->close_time != 0 ? $data->close_time : $data->last_update_time;

                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                $data->last_update_time = $data->last_update_time > 0 ? date("Y-m-d H:i:s", $data->last_update_time) : "";
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;

                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->first_open_time = $data->first_open_time!=0 ? date("Y-m-d H:i:s", $data->first_open_time) : '';
                $data->waiting_duration = $data->waiting_duration > 0 ? gmdate("H:i:s", $data->waiting_duration) : '';
                $data->agent_1 = !empty($names[$data->agent_1]) ? $names[$data->agent_1].' - '.$data->agent_1 : $data->agent_1;
                $data->agent_2 = !empty($names[$data->agent_2]) ? $names[$data->agent_2].' - '.$data->agent_2 : $data->agent_2;
                $data->close_time = $data->close_time != 0 ? date("Y-m-d H:i:s", $data->close_time): '';
                $data->in_kpi = $data->in_kpi=='Y'?'Yes':'No';
                //$data->attachmentUrl = "<a class='' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                $data->rs_tr = $data->rs_tr=='Y' ? 'Yes':'No';
                $data->reschedule_time = !empty($data->reschedule_time) ? date("Y-m-d H:i:s", $data->reschedule_time) : "";
                $data->rs_tr_create_time = !empty($data->rs_tr_create_time) ? date("Y-m-d H:i:s", $data->rs_tr_create_time) : '';
                //$data->open_duration = $data->open_duration > 0 ? gmdate("H:i:s", $data->open_duration) : '';
                $data->open_duration = $data->open_duration > 0 ? get_timestamp_to_hour_format($data->open_duration) : '';

                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $dotdot = strlen($str) > 100 ? "..." : "";
//                $data->subject = !empty($str) ? substr($str,0,100).$dotdot : "(no subject)";
                $subject = !empty($str) ? substr($str,0,100).$dotdot : "(no subject)";
//                $data->details = "<a target='blank' href='".$this->url('task=report-new&act=email-details-view&tid='.$data->ticket_id.'&sid='.$data->session_id)."'>Details</a>";
                $data->subject = "<a target='blank' href='".$this->url('task=report-new&act=email-details-view&tid='.$data->ticket_id.'&sdate='.$sdate.'&edate='.$edate)."'>".$subject."</a>";
            }
        }

        $response->rowdata = $result;
        $response->hideCol = array_merge($response->hideCol, $report_hide_col);
        $request_param = [
            'sdate_create_time' => $dateTimeArray['from'],
            'edate_create_time' => $dateTimeArray['to'],
            'sdate_last_update_time' => $lastDateArray['from'],
            'edate_last_update_time' => $lastDateArray['to'],
            'sdate_first_open_time' => $firstopenDateInfo['from'],
            'edate_first_open_time' => $firstopenDateInfo['to'],
            'sdate_close_time' => $closedDateArray['from'],
            'edate_close_time' => $closedDateArray['to'],
            'agent_id' => $agent_id,
            'in_kpi' => $in_kpi,
            'did' => $did,
            'status' => $status,
            'subject' => $subject,
            'skill_id' => $skill_id
        ];
        $this->reportAudit('NR', 'Email Agent', $request_param);
        $this->ShowTableResponse();
    }

    function actionEmailDayWiseReport(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MEmail.php');
        $report_model = new MReportNew();
        $eTicket_model = new MEmail();

        $skill_list = $eTicket_model->getEmailSkill();
        if (!empty($skill_list)){
            foreach ($skill_list as $key)
                $skill_list[$key->skill_id] = $key->skill_name;
        }

        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $date_format = get_report_date_format();
        if ($this->gridRequest->isMultisearch){
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            $date_from = !empty($lastDateArray['from']) ? generic_date_format($lastDateArray['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($lastDateArray['to']) ? generic_date_format($lastDateArray['to'], $date_format) : date('Y-m-d');
        }

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $lastDateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($lastDateInfo->errMsg)){
            $this->pagination->num_records = $report_model->numEmailDaywiseRepoet($lastDateInfo);
            $result = $this->pagination->num_records > 0 ?
                $report_model->getEmailDaywiseRepoet($lastDateInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            if (is_array($result) && count($result) > 0) {
                foreach ($result as &$data){
                    $data->last_update_time = date($date_format, strtotime($data->sdate));
                    $data->skill_id = !empty($skill_list[$data->skill_id]) ? $skill_list[$data->skill_id] : $data->skill_id;
                    $data->awt = !empty($data->WaitDuration) && !empty($data->total_closed) ? get_timestamp_to_hour_format($data->WaitDuration/$data->total_closed) : "00:00:00";
                    //$data->mwt = !empty($data->MAX_Wait) ? gmdate("H:i:s", $data->MAX_Wait) : "00:00:00";
                    $data->mwt = !empty($data->MAX_Wait) ? get_timestamp_to_hour_format($data->MAX_Wait) : "00:00:00";
                    $data->sl =  !empty($data->total_closed) && !empty($data->total_in_kpi)? round(($data->total_in_kpi/$data->total_closed)*100,'2') . "%" : "0%";
                    $data->aht = !empty($data->total_closed) && !empty($data->total_open_duration) ? get_timestamp_to_hour_format($data->total_open_duration/$data->total_closed) : "00:00:00";
                    $data->tod = !empty($data->total_open_duration) ? get_timestamp_to_hour_format($data->total_open_duration) : "00:00:00";
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $lastDateArray['from'],
                'edate' => $lastDateArray['to']
            ];
            $this->reportAudit('NR', 'Email Day Wise', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionReportWebChatDayWise(){
        include('model/MReportNew.php');
        include('model/MSkill.php');
        //include('model/MAgent.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();
        //$agent_model = new MAgent();
        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($datetime_range['from']) ? generic_date_format($datetime_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? generic_date_format($datetime_range['to'], $date_format) : date('Y-m-d');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, "00", "00", '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $this->pagination->num_records = $report_model->numWebChatDsywiseData($dateInfo, $skill_id);
//            $result = $this->pagination->num_records > 0 ? $report_model->getWebChatDayWiseData($dateInfo, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $result = $this->getWebChatDayWiseResult($dateInfo, $skill_id);

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            //var_dump($result); die();
            if (count($result) > 0) {
                foreach ($result as $data) {
                    $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                    $data->sdate = date($date_format, strtotime($data->csdate));
					
					/*
					 * Manually By Sanaul
					 */
					if (in_array($data->sdate, webchat_temporary_date)){
						$index = array_search($data->sdate, webchat_temporary_date);
						$data->in_kpi += webchat_temporary_date_value[$index];
					}						
					//var_dump(WEBCHAT_AGENT_RESPONSE);
					//var_dump($dateInfo->sdate);
					if($dateInfo->sdate >= WEBCHAT_AGENT_RESPONSE){
						if(isset($data->abd_new) && !empty($data->abd_new)){
							$data->total_chat -= $data->abd_new;
						}
						if (!empty($data->in_kpi_new)){
							$data->in_kpi = $data->in_kpi_new;
						}
						$data->out_kpi = !empty($data->total_chat) ? ($data->total_chat-$data->in_kpi) : 0;
					}else{
						$data->out_kpi = !empty($data->total_chat) ? ($data->total_chat-$data->in_kpi) : 0;
					}
					$data->sl = !empty($data->total_chat) && !empty($data->in_kpi) ? fractionFormat(($data->in_kpi / $data->total_chat) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';					
					//var_dump($data->total_service_time);
					//var_dump(gmdate("H:i:s", $data->total_service_time));					
					//var_dump($data->total_service_time / $data->total_chat);
					//var_dump(round($data->total_service_time / $data->total_chat));
                    $data->aht = !empty($data->total_service_time) && !empty($data->total_chat) ?  fractionFormat($data->total_service_time / $data->total_chat, $fraction_format, $this->gridRequest->isDownloadCSV) : 0.00;
                    //if(!$this->gridRequest->isDownloadCSV)					
                    $data->aht = gmdate("H:i:s", round($data->aht));

                    //$data->avg_wait_time = fractionFormat($data->total_wait_time / $data->total_chat, $fraction_format, $this->gridRequest->isDownloadCSV);
                    //$data->avg_wait_time = !empty($data->offered_chat) && !empty(round(($data->total_wait_time+$data->total_abd_wait_time) / $data->offered_chat)) ? gmdate("H:i:s", round(($data->total_wait_time+$data->total_abd_wait_time) / $data->offered_chat)) : "";
					//$data->total_wait_time = gmdate("H:i:s", $data->total_wait_time+$data->total_abd_wait_time);
					
					$data->avg_wait_time = !empty($data->offered_chat) && !empty(round($data->total_hold_in_q / $data->offered_chat)) ? gmdate("H:i:s", round($data->total_hold_in_q / $data->offered_chat)) : "";
					$data->total_wait_time = gmdate("H:i:s", $data->total_hold_in_q);
                    $data->abandoned_chat = $data->offered_chat - $data->total_chat;
					$data->abd_before_60 = $data->abd_new_before_60 + $data->abd_before_60;
					$data->abd_after_60 = $data->abd_new_after_60 + $data->abd_after_60;                 
                    $data->non_verified = $data->offered_chat-$data->verified;					
					
                    //$data->total_service_time = gmdate("H:i:s", $data->total_service_time); 
					$hours = floor($data->total_service_time / 3600);
					$minutes = floor(($data->total_service_time / 60) % 60);
					$seconds = floor($data->total_service_time) % 60;
					$hours = strlen($hours)==1 ? '0'.$hours : $hours;
					$minutes = strlen($minutes)==1 ? '0'.$minutes : $minutes;
					$seconds = strlen($seconds)==1 ? '0'.$seconds : $seconds;
					$data->total_service_time = $hours.":".$minutes.":".$seconds;
                    $data->ice_count = $data->ice_positive_count + $data->ice_negative_count;
                }
            }
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_id' => $skill_id
            ];
            $this->reportAudit('NR', 'Web Chat Day Wise', $request_param);
            $this->ShowTableResponse();
        }
    }

     private function getWebChatDayWiseResult($dateInfo, $skillId)
    {
        $reportModel = new MReportNew();
        $results = $reportModel->getWebChatRawDetails($dateInfo, $skillId);
        $fromDate = date('Y-m-d', $dateInfo->ststamp);
        $toDate = date('Y-m-d', $dateInfo->etstamp);
        $dateList = $this->get_date_list($fromDate, $toDate);

        $dayWiseResult = array();
        foreach ($dateList as $date) {
            $data = new stdClass();
            $data->csdate = $date;
            $data->offered_chat = 0;
            $data->total_chat = 0;
            $data->in_kpi = 0;
            $data->in_kpi_new = 0;
            $data->abd_new = 0;
            $data->abd_new_before_60 = 0;
            $data->abd_new_after_60 = 0;
            $data->abd_before_60 = 0;
            $data->abd_after_60 = 0;
            $data->total_service_time = 0;
            $data->total_wait_time = 0;
            $data->verified = 0;
            $data->ice_positive_count = 0;
            $data->ice_negative_count = 0;
            $data->total_abd_wait_time = 0;
            $data->total_hold_in_q = 0;
            $data->skill_id = null;
            foreach ($results as $result) {
                if(empty($data->skill_id)){
                    $data->skill_id = $result->skill_id;
                }
                if (date("Y-m-d", strtotime($result->call_start_time)) == $date) {
                    $data->offered_chat++;
                    $data->total_hold_in_q += $result->hold_in_q;
                    if ($result->status == 'S') {
                        $data->total_chat++;
                        if (abs(strtotime($result->call_start_time) - strtotime($result->start_time)) <= WEB_CHAT_SL_TIME) {
                            $data->in_kpi++;
                        }
                    } elseif ($result->status != 'S') {
                        $data->total_abd_wait_time += $result->hold_in_q;
                    }
                    if ($fromDate >= WEBCHAT_AGENT_RESPONSE) {
                        if ($result->status == 'S') {
                            if (abs(strtotime($result->start_time) - strtotime($result->agent_first_response)) <= WEB_CHAT_SL_TIME) {
                                $data->in_kpi_new++;
                            }
                            if ($result->agent_first_response == '0000-00-00 00:00:00' || $result->agent_first_response == '') {
                                $data->abd_new++;
                                if ($result->hold_in_q <= 60) {
                                    $data->abd_new_before_60++;
                                } elseif ($result->hold_in_q > 60) {
                                    $data->abd_new_after_60++;
                                }
                            } elseif ($result->agent_first_response != '0000-00-00 00:00:00') {
                                $data->total_service_time += abs(strtotime($result->start_time) - strtotime($result->tstamp));
                                if ($result->agent_first_response != '' && $result->disc_party != 'S' && $result->disposition_id != '1457') {
                                    $service_time_min = gmdate("H:i:s", $result->service_time);
                                    if ($service_time_min >= "00:02:00") {
                                        if ($result->customer_feedback == '5') {
                                            $data->ice_positive_count++;
                                        } elseif ($result->customer_feedback == '0') {
                                            $data->ice_negative_count++;
                                        }
                                    }
                                }
                            }
                            $data->total_wait_time += abs(strtotime($result->start_time) - strtotime($result->call_start_time));
                        } elseif ($result->status != 'S') {
                            if ($result->hold_in_q <= 60) {
                                $data->abd_new_before_60++;
                            } elseif ($result->hold_in_q > 60) {
                                $data->abd_new_after_60++;
                            }
                        }
                    } else {
                        $data->total_service_time += abs(strtotime($result->start_time) - strtotime($result->tstamp));
                        $data->total_wait_time += abs(strtotime($result->start_time) - strtotime($result->call_start_time));
                    }
                    if ($result->verify_user == 'Y') {
                        $data->verified++;
                    }
                }
            }
            if ($data->offered_chat) {
                $dayWiseResult[] = $data;
            }
        }
        return $dayWiseResult;
    }

    private function get_date_list($date_from, $date_to)
    {
        $date_list = array();
        $dateValue = $date_from;
        do {
            array_push($date_list, $dateValue);
            $dateValue = date('Y-m-d', strtotime("+1 day", strtotime($dateValue)));
        } while (strtotime($dateValue) <= strtotime($date_to));

        return $date_list;
    }

    function actionServicelog(){
        //include('model/MIvrServiceReport.php');
        include('lib/DateHelper.php');
        include('model/MIvrService.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $report_model = new MReportNew();

        //$report_model = new MIvrServiceReport();
        $ivr_model = new MIvrService();
        $agent_model = new MAgent();
        $date_format = get_report_date_format();

        $dateTimeArray = array();
        $dateRange = '';
        $dateinfo = '';
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('date_time');
            $dateRange = isset($_POST['dateRange']) ? trim($_POST['dateRange']) : '';
            if ($dateRange == 'Last Month') {
                $dateTimeArray['from']  = generic_date_format(date($date_format, strtotime("first day of previous month")), $date_format);
                $dateTimeArray['to']  = generic_date_format(date($date_format, strtotime("last day of previous month")), $date_format);
                $dateinfo = DateHelper::get_input_report_time_details(false, $dateTimeArray['from'], $dateTimeArray['to'], "00", "00", '','-1 second');
            } elseif($dateRange == 'Last Week') {
                $previous_week = strtotime("-1 week +1 day");
                $start_week = strtotime("last sunday midnight",$previous_week);
                $end_week = strtotime("next saturday",$start_week);

                $dateTimeArray['from'] = generic_date_format(date($date_format, $start_week), $date_format);
                $dateTimeArray['to'] = generic_date_format(date($date_format, $end_week), $date_format);
                $dateinfo = DateHelper::get_input_report_time_details(false, $dateTimeArray['from'], $dateTimeArray['to'], "00", "00", '','-1 second');
            } else {
                $dateinfo = $this->getEmailDate($dateTimeArray, $date_format);
            }
        }

        $dcode = "";
        $clid = "";
        $alid = "";

        if ($this->gridRequest->isMultisearch){
            $dcode = $this->gridRequest->getMultiParam('dcode');
            $clid = $this->gridRequest->getMultiParam('clid');
            $alid = $this->gridRequest->getMultiParam('ivr_id');
        }

        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        if (!empty($reportDays)){
            $toDate = date("Y-m-d");
            $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
        }

        $current_agent = UserAuth::getCurrentUser();
        $skills = $agent_model->getAgentSkill($current_agent);
        if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
            //$errMsg = UserAuth::getRepErrMsg();
            //$errType = 1;
            $this->pagination->num_records = 0;
        }else {
            //dd($dateinfo);
            $this->pagination->num_records = $report_model->numServiceLog($dateinfo, $dcode, $clid, $alid, $skills);
        }

        $result = $this->pagination->num_records > 0 ?
            $report_model->getServiceLog($dateinfo, $dcode, $clid, $alid, $this->pagination->getOffset(), $this->pagination->rows_per_page, $skills) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        if(count($result)>0){
            AddModel('MIvrService');
            $ivrServiceModel = new MIvrService();
            $dispositions = $ivrServiceModel->getServiceOptions('',true);
            $dbSuffix = UserAuth::getDBSuffix();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            foreach ( $result as &$data ) {
                if ($data->status == 'S') $data->statusTxt = 'Served';
                elseif ($data->status == 'B') $data->statusTxt = 'Bad Request';
                elseif ($data->status == 'A') $data->statusTxt = 'Abandoned';
                else $data->statusTxt = $data->status;

                $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
                $data->date_time = date("Y-m-d H:i:s", $data->tstamp);
                $data->servedTime = date("Y-m-d H:i:s", $data->served_time);
                $data->disposition_code = !empty($dispositions[$data->disposition_code]) ? $dispositions[$data->disposition_code] : $data->disposition_code;
                $data->caller_id = strlen($data->caller_id) > 10 ? substr($data->caller_id,strlen($data->caller_id) - 10) : $data->caller_id;
                if ($dbSuffix == 'AD') {

                    if ($data->disposition_code == 'EXTFPA' || $data->disposition_code == 'EXTFCL') {
                        if (!isset($insurance_companies[$data->account_id])) {
                            $insurance_companies[$data->account_id] = $report_model->AMAX_get_insurance_name($data->account_id);
                        }
                        $data->account_id = $insurance_companies[$data->account_id];
                    } elseif ($data->disposition_code == 'EXTFSC') {
                        if (!isset($sales_centers[$data->account_id])) {
                            $sales_centers[$data->account_id] = $report_model->AMAX_get_sales_center_name($data->account_id);
                        }
                        $data->account_id = $sales_centers[$data->account_id];
                    }
                }
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionReportQaOutbound()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numQaOutboundReportData($date_info, $agent_score, $score_comparison);

            $results = $this->pagination->num_records > 0 ?
                $report_model->getQaReportOutboundData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(OUTBOUND_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    $result->sdate = date($date_format . " H:i:s", strtotime($result->sdate));
                    $result->evaluation_time = date($date_format." H:i:s",strtotime($result->evaluation_time));

                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {

                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }
                            /*if ($form_value->percentage_value != null) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $form_value->percentage_value;
                            }*/
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                    }
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'Outbound QA', $request_param);
            $this->ShowTableResponse();
        }
    }

    private function isFormCalculative($form_list, $form)
    {
        foreach ($form_list as $item) {
            if ($item->fid == $form) {
                if ($item->calculative == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getFormOptionValue($options, $form_value)
    {
        foreach ($options as $form_option) {
            if ($form_value->field_name == $form_option->fid) {
                if ($form_option->foption_value == null) {
                    return $form_value->field_value;
                }
                $form_option = explode(",", $form_option->foption_value);
                foreach ($form_option as $option) {
                    list($field_value, $field_option) = explode("##", $option);
                    if ($form_value->field_value == $field_value) {
                        return $field_option;
                    }
                }
            }
        }
        return null;
    }

    function actionChatRatingsReport(){
        include('model/MReportNew.php');
        //include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        //$skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_id = $this->gridRequest->getMultiParam('agent_id');

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($dateinfo->errMsg)){
            $total_num = $report_model->numChatRatingReportData($dateinfo, $agent_id);
            $this->pagination->num_records = !empty($total_num) ? count($total_num) : 0;

            $callsData = $results = $this->pagination->num_records > 0 ? $report_model->getChatRatingReportData($dateinfo, $agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $result = &$callsData;
            if (count($result) > 0){
                $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
                foreach ($result as $data){
                    $data->agent_name = !empty($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : "";
                    $data->sdate = date($date_format." H:i", strtotime($data->sdate));
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_id' => $agent_id
            ];
            $this->reportAudit('NR', 'Web Chat Rating', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionVivrSummary()
    {
        AddModel('MReportNew');
        AddModel('MIvr');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $ivr_model = new MIvr();
        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $footer_row = [];
        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $ivr_id = ($this->gridRequest->getMultiParam('ivr_id') != "") ? $this->gridRequest->getMultiParam('ivr_id') : "*";
            $source = ($this->gridRequest->getMultiParam('source') != "") ? $this->gridRequest->getMultiParam('source') : "*";
            $date_format = $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second');

        $this->pagination->num_records = $report_model->numVivrSummary($dateinfo, $ivr_id, $source);
        $result = $this->pagination->num_records > 0 ? $report_model->getVivrSummary($dateinfo, $ivr_id, $source, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        if(is_array($result) && count($result) > 0)
        {
            $ivr_list = $ivr_model->getIvrOptions();
            foreach ( $result as &$data )
            {
                $data->ivr_ratio = !empty($data->total_hit) ? fractionFormat(($data->ivr_only / $data->total_hit) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                $data->ivr_name = isset($ivr_list[$data->ivr_id]) ? $ivr_list[$data->ivr_id] : '';
                $data->avg_time_in_ivr = !empty($data->total_hit) ? fractionFormat(($data->time_in_ivr / $data->total_hit), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
                $data->sdate = date($date_format, strtotime($data->sdate));
                if ($source == "W") {
                    $data->source = "WEB";
                } elseif ($source == "I") {
                    $data->source = "IVR";
                } else {
                    $data->source = "WEB & IVR";
                }
            }

            if(!$this->gridRequest->isDownloadCSV){
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getVivrSummary($dateinfo, $ivr_id, $source, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->sdate = "-";
                $final_row->ivr_name = "-";
                $final_row->source = "-";

                $final_row->ivr_ratio = !empty($final_row->total_hit) ? fractionFormat(($final_row->ivr_only / $final_row->total_hit) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                $final_row->agent_only = !empty($final_row->total_hit) ? ($final_row->total_hit - $final_row->ivr_only) : '';
                $final_row->avg_time_in_ivr = !empty($final_row->total_hit) ? fractionFormat(($final_row->time_in_ivr / $final_row->total_hit), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';

                $response->userdata = $final_row;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionVivrDetails()
    {
        AddModel('MReportNew');
        AddModel('MIvr');
        include('lib/DateHelper.php');

        $ivr_model = new MIvr();
        $report_model = new MReportNew();
        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $ivr_id =  '*';
        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = $date_format = get_report_date_format();
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
            $source = ($this->gridRequest->getMultiParam('source') != "") ? $this->gridRequest->getMultiParam('source') : "*";
            $did = ($this->gridRequest->getMultiParam('did') != "") ? $this->gridRequest->getMultiParam('did') : "*";
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        $this->pagination->num_records = $report_model->numVivrDetails($dateinfo, $ivr_id, $source, $did);
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        $result = $this->pagination->num_records > 0 ? $report_model->getVivrDetails($dateinfo, $ivr_id, $source, $did, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        if(is_array($result) && count($result) > 0)
        {
            $ivr_list = $ivr_model->getIvrOptions();
            foreach ($result as $key => &$data) {
                $data->sdate = date($date_format . " H:i:s", strtotime($data->start_time));
                $data->stop_time = date($date_format . " H:i:s", strtotime($data->stop_time));
                if ($data->source == "W") {
                    $data->source = "WEB";
                } elseif ($data->source == "I") {
                    $data->source = "IVR";
                } else {
                    $data->source = "WEB & IVR";
                }
                $data->ivr_name = isset($ivr_list[$data->ivr_id]) ? $ivr_list[$data->ivr_id] : '';
            }
            if (!$this->gridRequest->isDownloadCSV) { // for grid view
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getVivrDetails($dateinfo, $ivr_id, $source, $did, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->call_start_time = '-';
                $final_row->stop_time = '-';
                $final_row->cli = '-';
                $final_row->soucre = '-';
                $final_row->did = '-';
                $final_row->enter_time = '-';

                $response->userdata = $final_row;
            }
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportVivrIceSummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');

        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";
        $vivr_list = array("AB", "AC");

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
            $date_format = get_report_date_format();
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $source = ($this->gridRequest->getMultiParam('source') != "") ? $this->gridRequest->getMultiParam('source') : "*";
            $did = ($this->gridRequest->getMultiParam('did') != "") ? $this->gridRequest->getMultiParam('did') : "*";
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        // calculate total count
        $this->pagination->num_records = $report_model->numVivrIceSummaryReport($dateinfo, $ivr_id, $report_type, $source, $did);
        $result = $this->pagination->num_records > 0 ? $report_model->getVivrIceSummaryReport($dateinfo, $ivr_id, $report_type, $source, $did, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(!empty($result) && count($result) > 0){
            foreach ($result as $key => &$data) {
                $data->sdate = date($date_format, strtotime($data->sdate));
                $data->ivr_id = $data->ivr;
                $data->ice_count = $data->positive_ice_count + $data->negative_ice_count;
                $data->ice_score = (!empty($data->ice_count)) ? fractionFormat(($data->positive_ice_count / $data->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                $data->ice_percentage = (!empty($data->hit_count)) ? fractionFormat(($data->ice_count / $data->hit_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
            }

            if(!$this->gridRequest->isDownloadCSV){
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getVivrIceSummaryReport($dateinfo, $ivr_id, $report_type,  $source, $did, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;

                $total_hit_count = 0;
                $total_ice_count = 0;
                $total_positive_ice_count = 0;
                $total_negative_ice_count = 0;
                foreach ($final_row as $key => &$data) {
                    $data->sdate = date($date_format, strtotime($data->sdate));
                    $data->ivr_id = $data->ivr;
                    $data->ice_count = $data->positive_ice_count + $data->negative_ice_count;
                    $data->ice_score = (!empty($data->ice_count)) ? fractionFormat(($data->positive_ice_count / $data->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->ice_percentage = (!empty($data->hit_count)) ? fractionFormat(($data->ice_count / $data->hit_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $total_hit_count += $data->hit_count;
                    $total_ice_count += $data->ice_count;
                    $total_positive_ice_count += $data->positive_ice_count;
                    $total_negative_ice_count += $data->negative_ice_count;
                }

                $final_row =  new stdClass();
                $final_row->sdate = "-";
                $final_row->ivr = "-";
                $final_row->ivr_name = "-";
                $final_row->smonth = "-";
                $final_row->hit_count = $total_hit_count;
                $final_row->ice_count = $total_ice_count;
                $final_row->positive_ice_count = $total_positive_ice_count;
                $final_row->negative_ice_count = $total_negative_ice_count;
                $final_row->ice_score = (!empty($final_row->ice_count)) ? fractionFormat(($final_row->positive_ice_count / $final_row->ice_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                $final_row->ice_percentage = (!empty($final_row->hit_count)) ? fractionFormat(($final_row->ice_count / $final_row->hit_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
            }
            $response->userdata = $final_row;
        }

        if ($report_type == REPORT_MONTHLY) {
            $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['smonth'];
        } elseif ($report_type == REPORT_DAILY) {
            $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate'];
        } else {
            $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
            $response->showCol = ['sdate'];
        }

        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportApiAccessSummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('ldate');
            $date_format = get_report_date_format();
            $report_type = $this->gridRequest->getMultiParam('report_type');
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        $this->pagination->num_records = $report_model->numApiAccessSummaryReport($dateinfo, $report_type);
        $result = $this->pagination->num_records > 0 ? $report_model->getApiAccessSummaryReport($dateinfo, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(!empty($result) && count($result) > 0){
            foreach ($result as $key => &$data) {
                $data->ldate = date($date_format, strtotime($data->ldate));
                $data->total_response_time = (!empty($data->total_response_time)) ? fractionFormat(($data->total_response_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
                $data->avg_response_time = (!empty($data->avg_response_time)) ? fractionFormat(($data->avg_response_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
                $data->success_ration = !empty($data->total_count) ? fractionFormat((($data->success_count/$data->total_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV)."%" : "0.00%";
            }

            if(!$this->gridRequest->isDownloadCSV){
                $final_row = $this->pagination->num_records > 0 ? $report_model->getApiAccessSummaryReport($dateinfo, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->ldate = "-";
                $final_row->lhour = "-";
                $final_row->conn_name = "-";

                $final_row->total_count = !empty($final_row->total_count) ? $final_row->total_count: '00';
                $final_row->success_count = !empty($final_row->success_count) ? $final_row->success_count : '00';
                $final_row->error_count = !empty($final_row->error_count) ? $final_row->error_count : '00';
                $final_row->timeout_count = !empty($final_row->timeout_count) ? $final_row->timeout_count : '00';
                $final_row->total_response_time = !empty($final_row->total_response_time) ? fractionFormat(($final_row->total_response_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
                $final_row->avg_response_time = !empty($final_row->avg_response_time) ? fractionFormat(($final_row->avg_response_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '00';
                $final_row->success_ration = !empty($final_row->success_ration) ? fractionFormat(($final_row->success_ration*100), $fraction_format, $this->gridRequest->isDownloadCSV)."%" : '0.00%';
                $response->userdata = $final_row;
            }
        }
        if ($report_type == REPORT_DAILY) {
            $response->hideCol = ['lhour'];
        } else {
            $response->showCol = ['lhour'];
        }
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionReportApiAccessDetails(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $fraction_format = "%.2f";

        if ($this->gridRequest->isMultisearch){
            $call_id = $this->gridRequest->getMultiParam('callid');
            $datetime_range = $this->gridRequest->getMultiParam('log_time');
            $date_format = get_report_date_format();
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second');

        $this->pagination->num_records = $report_model->numApiAccessDetailsReport($dateinfo, $call_id);
        $result = $this->pagination->num_records > 0 ? $report_model->getApiAccessDetailsReport($dateinfo, $call_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        //GPrint($result);die;
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if(!empty($result) && count($result) > 0) {
            foreach ($result as $key => &$data) {
                $data->log_time = date($date_format.' H:i:s', strtotime($data->log_time));
                $data->response_time = (!empty($data->response_time)) ? fractionFormat(($data->response_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
                $data->transfer_time = (!empty($data->transfer_time)) ? fractionFormat(($data->transfer_time), $fraction_format, $this->gridRequest->isDownloadCSV) : '0.00';
            }
        }
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionReportQaWebChat()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numQaWebChatReportData($date_info, $agent_score, $score_comparison);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            $results = $this->pagination->num_records > 0 ? $report_model->getQaWebChatReportData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(WEBCHAT_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $key => &$result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    $result->sdate = date($date_format." H:i:s",strtotime($result->sdate));
                    $result->evaluation_time = date($date_format." H:i:s",strtotime($result->evaluation_time));

                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {
                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                    }
                }
            }
            $response->rowdata = $results;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'Webchat QA', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionReportQaEmail()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numQaEmailReportData($date_info, $agent_score, $score_comparison);

            $results = $this->pagination->num_records > 0 ?
                $report_model->getQaEmailReportData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(EMAIL_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    if (isset($result->agent_1)) {
                        $result->agent_id = $result->agent_1;
                    } elseif (isset($result->agent_2)) {
                        $result->agent_id = $result->agent_2;
                    }
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    //$result->sdate = date($date_format." H:i:s",strtotime($result->sdate));
                    $result->create_time = date($date_format . " H:i:s", $result->create_time);
                    $result->sdate = date($date_format." H:i:s",strtotime($result->sdate));
//                    $result->evaluation_time = date($date_format." H:i:s",strtotime($result->evaluation_time));


                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {

                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                    }
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'Email QA', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionReportIvrGlobalGroup()
    {
        AddModel('MReportNew');
        AddModel('MIvr');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $ivr_model = new MIvr();
        $date_from  = date('Y-m-d');
        $date_to =  date('Y-m-d');
        $ivr_id =  '*';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = $date_format = get_report_date_format();
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)) {
            $this->pagination->num_records = $report_model->numIvrGlobalGroup($date_info, $ivr_id);
            $results = $this->pagination->num_records > 0 ?
                $report_model->getIvrGlobalGroup($date_info, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            foreach ($results as $result){
                $result->ivr = $ivr_model->getIVRById($result->ivr_id)->ivr_name;
                if ($result->dtmf == "*") {
                    $result->group = $result->ivr . "_Main_Menu_Star (*)";
                }
                if ($result->dtmf == "#") {
                    $result->group = $result->ivr . "_Previous_Menu_Hit (#)";
                }
                if ($result->status_flag == "T") {
                    $result->group = $result->ivr . "_Repeat_Times";
                }
                if ($result->status_flag == "W") {
                    $result->group = $result->ivr . "_Wrong_Input";
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;

            $this->ShowTableResponse();
        }
    }

    function actionReportDiameterBillSummary()
    {
        AddModel('MReportNew');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)) {
            $this->pagination->num_records = $report_model->numDiameterBillSummary($date_info);
            $results = $this->pagination->num_records > 0 ?
                $report_model->getDiameterBillSummary($date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            foreach ($results as $result){
                $result->sdate = date($date_format, strtotime($result->sdate));
                $result->bill_amount = round($result->bill_amount/10000, 2);
                if ($result->dm_result_code == '2001') { //2001 means success
                    $result->status = 'Success';
                } else {
                    $result->status = 'Failed';
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;

            $this->ShowTableResponse();
        }
    }

    function actionReportDiameterBillDetails()
    {
        AddModel('MReportNew');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)) {
            $this->pagination->num_records = $report_model->numDiameterBillDetails($date_info);

            $csv_titles=array();
            $result=array();
            $isRemoveTag = true; // use csv download
            $delimiter = ',';    // use csv download
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download

            // download
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to']
            ];
            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Diameter Bill Details', $request_param);

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
            }
            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getDiameterBillDetails($date_info, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getDiameterBillDetails($date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format . " H:i:s", strtotime($data->call_start_time));
                        $data->dm_bill_time = date($date_format . " H:i:s", strtotime($data->dm_bill_time));
                        $data->bill_amount = $data->bill_amount / 10000;
                        if ($data->status == 'S') {
                            $data->status = "Success";
                        } else {
                            $data->status = "Failed";
                        }

                        if($this->gridRequest->isDownloadCSV){ // for download
                            $row=array();
                            foreach ($cols as $key=>$value){
                                $rvalue="";
                                if($isRemoveTag){
                                    if(isset($data->$key)){
                                        $rvalue=strip_tags($data->$key);
//                                        if ($key == 'callid') $rvalue = 'ID-' . $rvalue;
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
                        }
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

                $response = $this->getTableResponse();
                $response->records = $this->pagination->num_records;
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;

                $this->ShowTableResponse();

            }
        }
    }

    public function actionReportQaSms()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numQaSmsReportData($date_info, $agent_score, $score_comparison);

            $results = $this->pagination->num_records > 0 ?
                $report_model->getQaSmsReportData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(SMS_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    $result->sdate = date($date_format." H:i:s", strtotime($result->sdate));
                    $result->evaluation_time = date($date_format." H:i:s",strtotime($result->evaluation_time));


                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {

                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                    }
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'SMS QA', $request_param);
            $this->ShowTableResponse();
        }
    }
    function actionReportPdSummary() {
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days);

        $number_of_records = $report_model->numPdSummery($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_id);
        $this->pagination->num_records = !empty($number_of_records) ? count($number_of_records) : 0;
        $result = $this->pagination->num_records > 0 ? $report_model->numPdSummery($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, true) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $final_result = array();
        if(count($result) > 0) {
            foreach ($result as  &$data) {
                if($data->agent_id != '' && $data->skill_id != ''){
                    $data->sdate = date_format(date_create_from_format('Y-m-d', $data->sdate), $date_format);
                    $data->agent_name = $agent_model->getAgentById($data->agent_id)->name;
                    $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                    $data->total_failed = $data->total_dial - $data->total_answered;
                    $final_result [] = $data;
                }
            }
        }

        if(!$this->gridRequest->isDownloadCSV){
            // last row initialization
            //$row_data = $this->pagination->num_records > 0 ? $report_model->numPdSummery($dateInfo->sdate, $dateInfo->edate, $agent_id, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
            $row_data = $number_of_records;
            $final_row = new stdClass();
            $final_row->sdate = "-";
            $final_row->skill_id = "-";
            $final_row->agent_id = "-";
            $final_row->agent_name = "-";
            $final_row->total_dial = 0;
            $final_row->total_answered = 0;
            $final_row->total_failed = 0;
            $final_row->total_talk = 0;
            $final_row->total_ring = 0;
            if (!empty($row_data)) {
                foreach ($row_data as $data){
                    if($data->agent_id != '' && $data->skill_id != ''){
                        $final_row->total_dial += $data->total_dial;
                        $final_row->total_answered += $data->total_answered;
                        //$final_row->failed_call += $data->failed_call;
                        $final_row->total_failed += ($data->total_dial - $data->total_answered);
                        $final_row->total_talk += $data->total_talk;
                        $final_row->total_ring += $data->total_ring;
                    }
                }
            }
        }
        $response->userdata = $final_row;
        $response->hideCol = array_merge($response->hideCol, $report_hide_col);
        $response->rowdata = $final_result;
        $this->ShowTableResponse();
    }
    public function actionReportSmsDetails()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');

        $report_model = new MReportNew();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $phone_number = $this->gridRequest->getMultiParam('phone_number');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";


        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numSmsLog($date_info, $phone_number);
            $result = $this->pagination->num_records > 0 ?
                $report_model->getSmsLog($date_info, $phone_number, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            // Gprint($result);
            // die();

            if(count($result) > 0){
                foreach ( $result as &$data ) {
                    if ($data->answer_time != "0000-00-00 00:00:00") {
                        $data->wait_time = strtotime($data->answer_time) - strtotime($data->start_time);
                    } else {
                        $data->wait_time = 0;
                    }

                    if ($data->last_out_msg_time == "0000-00-00 00:00:00"){
                        //$data->agent_handling_time = $data->last_update_time != "0000-00-00 00:00:00" ? strtotime($data->last_update_time) - strtotime($data->answer_time) : null;
                        $data->agent_handling_time = null;
                    } else {
                        $data->agent_handling_time = $data->last_out_msg_time != "0000-00-00 00:00:00" ? strtotime($data->last_out_msg_time) - strtotime($data->answer_time) : null;
                    }



                    $data->wrapup_time = $data->last_update_time != "0000-00-00 00:00:00" && $data->last_out_msg_time != "0000-00-00 00:00:00" ? strtotime($data->last_update_time)-strtotime($data->last_out_msg_time) : null;

                    $data->receive_time = ($data->answer_time != "0000-00-00 00:00:00") ? date(get_report_date_format() . ' H:i:s', strtotime($data->answer_time)) : null;
                    // $data->sl_difference = $data->last_out_msg_time != "0000-00-00 00:00:00" ? strtotime($data->last_out_msg_time)-strtotime($data->start_time) : null;
                    if (!empty($data->agent_handling_time)){
                        $data->sl = $data->agent_handling_time <= SMS_SL_TIME ? "100%" : "0%";
                    } else {
                        $data->sl = null;
                    }
//                    $data->last_in_msg_time = date(get_report_date_format(). ' H:i:s', strtotime($data->last_in_msg_time));
                    $data->arrival_time = ($data->last_in_msg_time != "0000-00-00 00:00:00") ? date(get_report_date_format() . ' H:i:s', strtotime($data->last_in_msg_time)) : null;
                    $data->last_out_msg_time = ($data->last_out_msg_time != "0000-00-00 00:00:00") ? date(get_report_date_format() . ' H:i:s', strtotime($data->last_out_msg_time)) : null;
                    $data->last_update_time = ($data->last_update_time != "0000-00-00 00:00:00") ? date(get_report_date_format() . ' H:i:s', strtotime($data->last_update_time)) : null;
                    $data->sdate = date(get_report_date_format(). ' H:i:s', strtotime($data->start_time));
                    $data->actUrl .= "<a title='Sms Messages' class='btn btn-success btn-xs lightboxWIF' href='" . $this->url("task=smslogreport&act=sms-messages&source=report&session_id=" . urlencode($data->session_id)) . "'><i class='fa fa-commenting-o'></i></a>";

                    $data->wait_time = gmdate("H:i:s", $data->wait_time);
                    $data->wrapup_time = gmdate("H:i:s", $data->wrapup_time);
                    $data->agent_handling_time = gmdate("H:i:s", $data->agent_handling_time);

                    $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                    if ($data->status_code == 'C') {
                        $data->status_code = "Closed";
                    } elseif ($data->status_code == 'N') {
                        $data->status_code = "New";
                    } elseif ($data->status_code == 'A') {
                        $data->status_code = "Not Responded";
                    } elseif ($data->status_code == 'R') {
                        $data->status_code = "Redistribute";
                    }

                    if (!empty($data->last_out_msg_time) && $data->customer_feedback == 'Y') {
                        $data->customer_feedback = 'Positive';
                    } else if (!empty($data->last_out_msg_time) && $data->customer_feedback == 'N') {
                        $data->customer_feedback = 'Negative';
                    } else {
                        $data->customer_feedback = '-';
                    }

                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            if($this->gridRequest->isDownloadCSV){
                $response->hideCol = array_merge($response->hideCol, ['actUrl']);
            }
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to']
            ];
            $this->reportAudit('NR', 'SMS Log Report', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionReportSmsSummaryOLD(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MForcastRgb.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $forecast_rgb_model = new MForcastRgb();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');

            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = "S"; // S for SMS
            // $category = $this->gridRequest->getMultiParam('category');
            $report_type = REPORT_DAILY; //REPORT_DAILY
            $date_from = !empty($datetime_range['from']) ? generic_date_format($datetime_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? generic_date_format($datetime_range['to'], $date_format) : date('Y-m-d');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days );

        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = 'BB'; //implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = 'BB'; //implode("','", $skills_types[$skill_type]);
            }
            // Gprint($skills_types);
            // Gprint($skills_type);
            // var_dump($skill_ids);
            // calculate total count
            // $record = $report_model->numSmsSummaryReport($dateinfo, $skill_ids, $report_type);
            $this->pagination->num_records = $report_model->numSmsSummaryReportOLD($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getSmsSummaryReportOLD($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            $forecast_rgb_data=[];
            $details_data = $report_model->getMaxWaitTimeData($dateinfo, $skill_ids, $report_type);
            // var_dump($details_data);

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => $data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->talk_time = $data->service_duration-$data->agent_hold_time;

                    if($this->gridRequest->isDownloadCSV){
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? ($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? $data->wrap_up_time / $data->calls_answerd : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? $data->ring_time / $data->calls_answerd : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? $data->calls_offered / $data->rgb_call_count : 0;
                    }else{
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? round(($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? round($data->wrap_up_time / $data->calls_answerd) : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? round($data->ring_time / $data->calls_answerd) : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? round($data->calls_offered / $data->rgb_call_count) : 0;
                    }

                    $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? fractionFormat(($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $wait_time_data = $this->getMaxWaitTime($data->sdate, $data->skill_id, $details_data);
                    $data->max_wait_time = (isset($wait_time_data['max_wait_time']) && !empty($wait_time_data['max_wait_time'])) ? $wait_time_data['max_wait_time'] : 0;
                    $data->hold_time_in_queue = (isset($wait_time_data['hold_time_in_queue']) && !empty($wait_time_data['hold_time_in_queue'])) ? $wait_time_data['hold_time_in_queue'] : 0;

                    $data->sdate = date($date_format, strtotime($data->sdate));
                    $total_max_wait_time += $data->max_wait_time;
                    $total_hold_time_in_queue += $data->hold_time_in_queue;
                }

                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getSmsSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    $final_row->hour_minute = "-";
                    $final_row->forecasted_call_count = '-';
                    $final_row->rgb_call_count = '-';
                    $final_row->cpc = '-';
                    $final_row->talk_time = $final_row->service_duration-$final_row->agent_hold_time;

                    $final_row->avg_handling_time = (!empty($final_row->calls_answerd)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->wrap_up_time+$final_row->agent_hold_time)/$final_row->calls_answerd) : 0;
                    $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? round($final_row->wrap_up_time / $final_row->calls_answerd) : 0;
                    $final_row->asa = (!empty($final_row->calls_answerd)) ? round($final_row->ring_time / $final_row->calls_answerd) : 0;

                    $final_row->service_level_lte_10_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_10_count))) ? fractionFormat(($final_row->ans_lte_10_count / ($final_row->calls_offered - $final_row->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->max_wait_time = $total_max_wait_time;
                    $final_row->hold_time_in_queue = $total_hold_time_in_queue;

                    $response->userdata = $final_row;
                }
            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id'),
            ];
            $this->reportAudit('NR', 'Skill Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    public function actionReportSmsSummary(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');

        $skill_model = new MSkill();
        $report_model = new MReportNew();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = "S"; // S for SMS
            $date_from = !empty($datetime_range['from']) ? generic_date_format($datetime_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? generic_date_format($datetime_range['to'], $date_format) : date('Y-m-d');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days );

        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

            $this->pagination->num_records = $report_model->numSmsSummaryReport($dateinfo, $skill_ids);
            $result = $this->pagination->num_records > 0 ? $report_model->getSmsSummaryReport($dateinfo, $skill_ids, SMS_SL_TIME ,$this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            if(!empty($result) && count($result) > 0) {
                foreach ($result as &$data) {
                    $data->skill_name = !empty($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->aht = !empty($data->total_served) ? round(($data->total_serve_time + $data->total_wrapup_time)/$data->total_served,2) : 0;
                    $data->service_level = !empty($data->total_served) ? round(($data->total_in_kpi/$data->total_served)*100, 2)."%" : "0%";

                    $data->avg_wait_time = !empty($data->total_offered) && !empty(round($data->total_wait_time/$data->total_offered)) ? gmdate("H:i:s", round($data->total_wait_time/$data->total_offered)) : "";
                    $data->total_serve_time = gmdate("H:i:s", $data->total_serve_time);
                    $data->total_wrapup_time = gmdate("H:i:s", $data->total_wrapup_time);
                    $data->total_wait_time = gmdate("H:i:s", $data->total_wait_time);
                    $data->max_wait_time = gmdate("H:i:s", $data->max_wait_time);
                    $data->aht = gmdate("H:i:s", round($data->aht));
                    $data->sdate = report_date_format($data->sdate);
                    $data->ice_count = $data->ice_positive_count + $data->ice_negative_count;
                }
            }
            $response->rowdata = $result;
            $this->ShowTableResponse();
        }
    }

    private function getMaxWaitTime($date, $skill_id, $details_data)
    {
        $max_wait_time = 0;
        $total_in_queue = 0;
        // var_dump($details_data);

        foreach ($details_data as $detail_data) {
            if ($detail_data->sdate == $date && $detail_data->skill_id == $skill_id) {
                return ['max_wait_time' => $detail_data->max_wait_time, 'hold_time_in_queue' => $detail_data->total_in_queue];
            }
        }

        return ['max_wait_time' => $max_wait_time, 'hold_time_in_queue' => $total_in_queue];
    }

    public function actionAgentPerformanceObmSummary()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";
        $report_current_date = date('Y-m-d H:i:s');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){

            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
        }

        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentsFullName();
        $this->pagination->num_records = $report_model->numAgentPerformanceSummary2($dateinfo, 'O');
        // Gprint($this->pagination->num_records);
        // Gprint(gmdate("H:i:s", 31601));
        // var_dump($report_model->getAgentObmCallInformation($date_info, 2057));
        // die();

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary2($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, 'O') : null;

        if (is_array($result) && count($result) > 0) {
            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString2($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";

                $agent_call_info = $report_model->getAgentObmCallInformation($dateinfo, $data->agent_id);
                // Gprint($agent_call_info);
                if(!empty($agent_call_info)){
                    $data->calls_out_attempt  = (empty($agent_call_info->calls_out_attempt)) ? 0 : $agent_call_info->calls_out_attempt;
                    $data->calls_answered = (empty($agent_call_info->calls_answered)) ? 0 : $agent_call_info->calls_answered;
                    $data->ring_time = (empty($agent_call_info->ring_time)) ? 0 : $agent_call_info->ring_time;
                    $data->wrap_up_time = (empty($agent_call_info->wrap_up_time)) ? 0 : $agent_call_info->wrap_up_time;
                    $data->service_time = (empty($agent_call_info->service_time)) ? 0 : $agent_call_info->service_time;
                    $data->hold_time = (empty($agent_call_info->hold_time)) ? 0 : $agent_call_info->hold_time;
                    $data->agent_hangup = (empty($agent_call_info->agent_hangup)) ? 0 : $agent_call_info->agent_hangup;
                    $data->agent_reject_calls = (empty($agent_call_info->agent_reject_calls)) ? 0 : $agent_call_info->agent_reject_calls;
                    $data->agent_disc_calls = (empty($agent_call_info->agent_disc_calls)) ? 0 : $agent_call_info->agent_disc_calls;
                    $data->workcode_count = (empty($agent_call_info->workcode_count)) ? 0 : $agent_call_info->workcode_count;
                    $data->talk_time = $data->service_time - $data->hold_time;
                }

                //agent session info
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->first_login = date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->login_time = gmdate("H:i:s", $agentinfo->login_duration);
                $data->not_ready_time = $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->login_duration > $agentinfo->not_ready_time ? $agentinfo->login_duration - $data->not_ready_time : 0;
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->total_idle_time = $agentinfo->login_duration - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : 0;
                $data->logout_time = empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                $data->not_ready_count = $agentinfo->not_ready_count;
                $data->agent_info = $agentinfo;

                $data->agent_name = $agent_options[$data->agent_id];
                $data->aht = $data->calls_answered > 0 ? round(($data->ring_time + $data->talk_time + $data->hold_time + $data->wrap_up_time) / $data->calls_answered) : 0;
                $data->workcode_percent = $data->calls_answered > 0 ? round(($data->workcode_count / $data->calls_answered) * 100,2) : 0;
                $data->workcode_percent .= '%';
            }
        }

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Agent OBM Performance', $request_param);
        $this->ShowTableResponse();
    }

    public function actionAgentPerformanceSummary2()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";
        $report_current_date = date('Y-m-d H:i:s');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){

            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
        }

        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentsFullName();
        $this->pagination->num_records = $report_model->numAgentPerformanceSummary2($dateinfo);
        // Gprint($this->pagination->num_records);
        // Gprint($dateinfo);
        // die();

        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary2($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		//Gprint($result);
        //die();

        if (is_array($result) && count($result) > 0) {
            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString2($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";

                $agent_call_info = $report_model->getAgentCallInformation($dateinfo, $data->agent_id);
                // Gprint($agent_call_info);
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
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                //$agentinfo = $report_model->getAgentSessionInfo(1010, $dateinfo);
				//Gprint($agentinfo);
				//die();

                //$data->first_login =  date($date_format." H:i:s",strtotime($agentinfo->first_login));
                //$data->not_ready_time =  $agentinfo->not_ready_time;
                // $data->available_time = $agentinfo->staffed_time > $agentinfo->not_ready_time ? $agentinfo->staffed_time - $data->not_ready_time /*($data->ring_time + $data->talk_time + $data->wrap_up_time ) */: 0;
                //$data->available_time = $agentinfo->staffed_time > $agentinfo->not_ready_time ? $agentinfo->staffed_time - $data->not_ready_time : 0;
                //$data->login_time = gmdate("H:i:s", $agentinfo->staffed_time);                
                // $data->total_idle_time = $agentinfo->staffed_time - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                //$data->total_idle_time = $agentinfo->staffed_time - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);

                $data->first_login = date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->login_time = gmdate("H:i:s", $agentinfo->login_duration);
                $data->not_ready_time = $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->login_duration > $agentinfo->not_ready_time ? $agentinfo->login_duration - $data->not_ready_time : 0;
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->total_idle_time = $agentinfo->login_duration - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : '00:00:00';
                $data->logout_time = empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                //$data->logout_time =  empty($agentinfo->logout_time) && date("Y-m-d", strtotime($dateinfo->sdate)) == date("Y-d-m", time()) ? 'In Progress' : !empty($agentinfo->logout_time) ? date($date_format." H:i:s",strtotime($agentinfo->logout_time)) : "";
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

        // Gprint($session_agent_id);
        // Gprint($call_agent_id);
        // var_dump(array_diff($session_agent_id, $call_agent_id));

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Agent Performance', $request_param);
        $this->ShowTableResponse();

    }

    function actionReportVivrIceDetails()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');


        $report_model = new MReportNew();
        $skill_model = new MSkill();

        if ($this->gridRequest->isMultisearch) {
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $ivr_id = $this->gridRequest->getMultiParam('ivr_id');
            $date_format = get_report_date_format();
            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format . " H:i", $datetime_range['from']), 'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format . " H:i", $datetime_range['to']), 'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $datetime_range['from']), 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $datetime_range['to']), 'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');

        // calculate total count
        $this->pagination->num_records = $report_model->numVivrIceDetailsReport($dateinfo, $ivr_id);
        $result = $this->pagination->num_records > 0 ? $report_model->getVivrIceDetailsReport($dateinfo, $ivr_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $key => &$data) {
                $data->sdate = date($date_format, strtotime($data->start_time));
                $data->skill_name= $skill_model->getSkillById($data->ivr_id)->skill_name;
                $data->msisdn_880 = "880" . $data->cli;
            }

            $response->rowdata = $result;
            $this->ShowTableResponse();
        }
    }

    public function actionEmailAgentPerformanceSummary() {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');        
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";
        $report_current_date = date('Y-m-d H:i:s');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){

            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
        }

        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentsFullName();
        $this->pagination->num_records = $report_model->numAgentPerformanceSummary2($dateinfo, "E");
        // Gprint($this->pagination->num_records);
        // die();
        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary2($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, "E") : null;

        if (is_array($result) && count($result) > 0) {
            $e_agents = [];
            foreach ($result as $item){
                $e_agents[] = $item->agent_id;
            }
            $email_agent_info = $this->formatEmailAgentWiseActivity($report_model->getEmailPerformanceActivity($dateinfo, $e_agents));
            // GPrint($email_agent_info);die;
            // Gprint($result);
            // die();

            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString2($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";

                //agent session info
                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);

                // $data->first_login =  date($date_format." H:i:s",strtotime($agentinfo->first_login));
                // $data->not_ready_time =  $agentinfo->not_ready_time;
                // $data->available_time = $agentinfo->staffed_time > $agentinfo->not_ready_time ? $agentinfo->staffed_time - $data->not_ready_time /*($data->ring_time + $data->talk_time + $data->wrap_up_time ) */: 0;
                // $data->login_time = gmdate("H:i:s", $agentinfo->staffed_time);
                // $data->total_idle_time = $agentinfo->staffed_time - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                // $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : 0;
                // $data->logout_time =  empty($agentinfo->logout_time) && date("Y-m-d", strtotime($date_info->sdate)) == date("Y-d-m", time()) ? 'In Progress' : !empty($agentinfo->logout_time) ? date($date_format." H:i:s",strtotime($agentinfo->logout_time)) : "";
                // $data->available_time = gmdate("H:i:s", $data->available_time);
                // $data->not_ready_count = $agentinfo->not_ready_count;
                // $data->agent_info = $agentinfo;
                $data->first_login = date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->login_time = gmdate("H:i:s", $agentinfo->login_duration);
                $data->not_ready_time = $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->login_duration > $agentinfo->not_ready_time ? $agentinfo->login_duration - $data->not_ready_time : 0;
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->total_idle_time = $agentinfo->login_duration - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : '00:00:00';
                $data->logout_time = empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                $data->not_ready_count = $agentinfo->not_ready_count;
                $data->agent_info = $agentinfo;                

                $data->agent_name = $agent_options[$data->agent_id];
                $data->pending = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->pending : 0;
                $data->pen_client = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->pen_client : 0;
                $data->served = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->served : 0;
                $data->closed = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->closed : 0;
                $data->rescheduled = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->rescheduled : 0;
                $data->park = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->park : 0;
                $data->new = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->new : 0;
                $data->view = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->view : 0;
                $data->pull = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->pull : 0;
                $data->mail_send = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->mail_send : 0;
                $data->disposition = !empty($email_agent_info[$data->agent_id]) ? $email_agent_info[$data->agent_id]->disposition : 0;
                $data->workcode_percent = $data->mail_send > 0 ? round(($data->disposition / $data->mail_send) * 100,2) : 0;
                $data->workcode_percent .= '%';

                //Gprint($data);

            }
        }
        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Email Agent Performance', $request_param);
        $this->ShowTableResponse();
    }

    private function formatEmailAgentWiseActivity($data){
        $response = [];
        // Gprint($data);
        if (!empty($data)){
            foreach ($data as $key) {
                $obj = new stdClass();
                $obj->pending = $key->pending;
                $obj->pen_client = $key->pen_client;
                $obj->served = $key->served;
                $obj->closed = $key->closed;
                $obj->rescheduled = $key->rescheduled;
                $obj->park = $key->park;
                $obj->new = $key->new;
                $obj->view = $key->view;
                $obj->pull = $key->pull;
                $obj->mail_send = $key->mail_send;
                $obj->disposition = $key->disposition;

                $response [$key->agent_id] = $obj;
            }
        }
        return $response;
    }

    public function actionEvaluationSummary()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d",strtotime("+1 day"));
        $service_id = "*";
        $agent_id = "*";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $date_format = get_report_date_format();
            $dial_time = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($dial_time['from']) ? generic_date_format($dial_time['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($dial_time['to']) ? generic_date_format($dial_time['to'], $date_format) : date("Y-m-d",strtotime("+1 day"));
            $service_id = $this->gridRequest->getMultiParam('service_id');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $this->pagination->num_records = $report_model->numEvaluationSummaryData($dateInfo, $service_id, $agent_id);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            $result = $this->pagination->num_records > 0 ?
                $report_model->getEvaluationSummaryData($dateInfo, $service_id, $agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            if(count($result) > 0)
            {
                foreach ( $result as $data ){
                    $data->sdate = date($date_format, strtotime($data->sdate));
                    $data->evaluator_name = $data->name;
                    $data->service_name = $this->getServiceName($data->skill_type);
                }
                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getEvaluationSummaryData($dateInfo, $service_id, $agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->service_name = "-";
                    $final_row->evaluator_name = "-";
                }
                $response->userdata = $final_row;
            }

            $response->rowdata = $result;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $request_param = [
                'sdate' => $dial_time['from'],
                'edate' => $dial_time['to'],
                'skill_id' => $this->gridRequest->getMultiParam('skill_id')
            ];
            $this->reportAudit('NR', 'Evaluation Summary Repport', $request_param);
            $this->ShowTableResponse();
        }
    }

    private function getServiceName($service_id)
    {
        $service_name = "";
        $service_list = evp_service_list();
        foreach ($service_list as $key => $value) {
            if ($key == $service_id) {
                $service_name = $value;
                break;
            }
        }
        return $service_name;
    }

    public function actionCustomerJourneyAgentActivitySummary() {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_format = get_report_date_format();
        $searchObj = new stdClass();
        $searchObj->agent_id = "";
        $searchObj->skill_id = "";
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $searchObj->agent_id = $this->gridRequest->getMultiParam('agent_id');
            $searchObj->skill_id = $this->gridRequest->getMultiParam('skill_id');

            $date_format = $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }
        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);

        if(empty($dateInfo->errMsg)){
            $agent_options = $agent_model->getAgentsFullName();
            $skill_options = $skill_model->getSkillsNamesArray();

            $this->pagination->num_records = $report_model->numCustomerJourneyAgentActivitySummary($date_info, $searchObj);
            $result = $this->pagination->num_records > 0 ?
                $report_model->getCustomerJourneyAgentActivitySummary($date_info, $searchObj, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            if (is_array($result) && count($result) > 0) {
                foreach ($result as &$data){
                    $data->agent_name = !empty(array_key_exists($data->agent_id, $agent_options)) ? $agent_options[$data->agent_id] : $data->agent_id;
                    $data->skill_name = !empty(array_key_exists($data->skill_id, $skill_options)) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->sdate = date($date_format, strtotime($data->sdate));
                }
            }
            $response = $this->getTableResponse();
            $response->rowdata = $result;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->records = $this->pagination->num_records;
            $request_param = [
                'sdate' => $sdate,
                'edate' => $edate
            ];
            $this->reportAudit('NR', 'Customer Journey Agent Activity Summary', $request_param);
            $this->ShowTableResponse();
        }
    }

    function actionCustomerJourneyReport(){
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $module_names = customer_journey_module;
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $searchObj = new stdClass();
        $searchObj->customer_id = "";
        if ($this->gridRequest->isMultisearch){
            $searchObj->customer_id = $this->gridRequest->getMultiParam('customer_id');
            $searchObj->module_type = $this->gridRequest->getMultiParam('module_type');
            $datetime_range = $this->gridRequest->getMultiParam('sdate');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }
        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($date_info->errMsg)){
            $csv_titles=array();
            $csv_data=array();
            $result=array();
            $isRemoveTag = false; // use csv download
            $delimiter = ',';    // use csv download
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download
            $skip_row_count = 0;  // use grid view
            $callIdArr = [];  // use grid view

            $this->pagination->num_records = $report_model->numCustomerJourneyReport($date_info, $searchObj);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'msisdn' => $searchObj->customer_id,
                'type' => $searchObj->module_type
            ];

            if($this->gridRequest->isDownloadCSV){
                $report_model->saveReportAuditRequest('NRD::Customer Journey Details', $request_param);
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
                $report_model->saveReportAuditRequest('NRS::Customer Journey Details', $request_param);
            }

            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getCustomerJourneyReport($date_info, $searchObj, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getCustomerJourneyReport($date_info, $searchObj, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if (is_array($result) && count($result) > 0) {
                    $fileInputRowCount = 0; // for download

                    foreach ($result as &$data){
                        $data->module_type = !empty(array_key_exists($data->module_type, $module_names)) ? $module_names[$data->module_type] : $data->module_type;
                        $data->sdate = date($date_format." H:i:s",strtotime($data->log_time));

                        if($this->gridRequest->isDownloadCSV){ // for download
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
                        }
                    }

                    if($fileInputRowCount < DOWNLOAD_PER_PAGE){
                        break;
                    }else{
                        $fileInputRow++;
                        $dbResultOffset = $dbResultRow*($fileInputRow-1);
                    }

                } else {
                    break;
                }
            }
            //array_push($response->hideCol,'sdate');
            if($this->gridRequest->isDownloadCSV){  // for download
                fclose($f);
                die();
            }else{  // for grid view
                //$response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $this->ShowTableResponse();
            }
        }
    }

    public function actionReportPdQa()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $datetime_range = $this->gridRequest->getMultiParam('sdate');
        $date_format = get_report_date_format();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
        $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
        $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        $agent_score = $this->gridRequest->getMultiParam('agent_score');
//        $score_comparison = $this->gridRequest->getMultiParam('score_comparison');
        $score_comparison = $this->gridRequest->getMultiParam('op')['agent_score'];

        if ($score_comparison == "eq") {
            $score_comparison = "=";
        } elseif ($score_comparison == "gr") {
            $score_comparison = ">";
        } elseif ($score_comparison == "lg") {
            $score_comparison = "<";
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);
        if(empty($date_info->errMsg)){
            $this->pagination->num_records = $report_model->numPdQaReportData($date_info, $agent_score, $score_comparison);

            $results = $this->pagination->num_records > 0 ?
                $report_model->getPdQaReportData($date_info, $agent_score, $score_comparison, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $skill_options = $skill_model->getSkillsNamesArray();
            $form_fields = $report_model->getEvaluationFormFields(PD_EVALUATION_TYPE);

            if (count($results) > 0) {
                foreach($results as $result){
                    $form_values = $report_model->getEvaluationFormValue($result->callid);
                    $result->agent_name = $agent_model->getAgentById($result->agent_id)->name;
                    $result->evaluator_name = $agent_model->getAgentById($result->evaluator_id)->name;
                    $result->skill_name = $skill_options[$result->skill_id];

                    $result->sdate = date($date_format . " H:i:s", strtotime($result->sdate));
                    $result->call_start_time = date($date_format." H:i:s",strtotime($result->call_start_time));

                    foreach ($form_values as $form_value) {
                        if ($form_value->field_value != null && $form_value->field_value != "false") {
                            if ($this->isFormCalculative($form_fields, $form_value->field_name)) {
                                $result->{$form_value->field_name} = $form_value->percentage_value . " %";
                            } else {
                                $result->{$form_value->field_name} = $this->getFormOptionValue($form_fields, $form_value);
                            }
                        } elseif ($form_value->field_value == "false") {
                            $result->{$form_value->field_name} = "N/A";
                        }
                    }
                }
            }

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $results;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'agent_score' => $agent_score,
                'score_comparison' => $score_comparison
            ];
            $this->reportAudit('NR', 'PD QA', $request_param);
            $this->ShowTableResponse();
        }
    }

    /*
     * Webchat performance summary
     */
    public function actionAgentWebchatPerformanceSummary()
    {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";
        $report_current_date = date('Y-m-d H:i:s');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){

            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
        }

        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentsFullName();
        $this->pagination->num_records = $report_model->numAgentPerformanceSummary2($dateinfo, "C");
        //dd($this->pagination->num_records);
        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary2($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, "C") : null;
		//Gprint($result);
		//die();
		
        if (is_array($result) && count($result) > 0) {
            $e_agents = [];
            foreach ($result as $item){
                $e_agents[] = $item->agent_id;
            }
            $webchat_agent_info = $report_model->getWebchatAgentPerformance($dateinfo, $e_agents, $this->pagination->getOffset(), $this->pagination->rows_per_page, $this->gridRequest->isDownloadCSV);

            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString2($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";

                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->first_login = date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->login_time = gmdate("H:i:s", $agentinfo->login_duration);
                $data->not_ready_time = $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->login_duration > $agentinfo->not_ready_time ? $agentinfo->login_duration - $data->not_ready_time : 0;
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->total_idle_time = $agentinfo->login_duration - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : '00:00:00';
                $data->logout_time = empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                $data->not_ready_count = $agentinfo->not_ready_count;
                $data->agent_info = $agentinfo;
                
                $data->agent_name = !empty($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : null;
                $data->offered_chat = $webchat_agent_info[$data->agent_id]->offered_chat;
                $data->total_chat = $webchat_agent_info[$data->agent_id]->total_chat;
                $data->in_kpi = $webchat_agent_info[$data->agent_id]->in_kpi;
                $data->in_kpi_new = $webchat_agent_info[$data->agent_id]->in_kpi_new;
                $data->out_kpi = $webchat_agent_info[$data->agent_id]->out_kpi;
                $data->total_service_time = $webchat_agent_info[$data->agent_id]->total_service_time;
                $data->total_wait_time = $webchat_agent_info[$data->agent_id]->total_wait_time;
                $data->verified = $webchat_agent_info[$data->agent_id]->verified;
                $data->total_abd_wait_time = $webchat_agent_info[$data->agent_id]->total_abd_wait_time;
                $data->aht = $webchat_agent_info[$data->agent_id]->aht;
                $data->verified = $webchat_agent_info[$data->agent_id]->verified;
                $data->non_verified = $webchat_agent_info[$data->agent_id]->non_verified;
                $data->awt = $webchat_agent_info[$data->agent_id]->awt;
                $data->sl = $webchat_agent_info[$data->agent_id]->sl;
            }
        }

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Webchat Agent Performance', $request_param);
        $this->ShowTableResponse();
    }

    /*
     * SMS performance summary
     */
    function actionAgentSmsPerformanceSummary(){
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $reportDays = UserAuth::getReportDays();
        $fraction_format = "%.2f";
        $report_current_date = date('Y-m-d H:i:s');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){

            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
        }

        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentsFullName();
        $this->pagination->num_records = $report_model->numAgentPerformanceSummary2($dateinfo, "S");
        $result = $this->pagination->num_records > 0 ?
            $report_model->getAgentPerformanceSummary2($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, "S") : null;

        if (is_array($result) && count($result) > 0) {
            $e_agents = [];
            foreach ($result as $item){
                $e_agents[] = $item->agent_id;
            }
            $sms_agent_info = $report_model->getSmsAgentPerformance($dateinfo, $e_agents, SMS_SL_TIME);

            foreach ($result as &$data){
                $skill_set = $report_model->getAgentSkillSetAsString2($data->agent_id);
                $data->skill_set = !empty($skill_set[$data->agent_id]->skill_set) ? $skill_set[$data->agent_id]->skill_set : "No Skill";

                $agentinfo = $report_model->getAgentSessionInfo($data->agent_id, $dateinfo);
                $data->first_login = date($date_format." H:i:s",strtotime($agentinfo->first_login));
                $data->login_time = gmdate("H:i:s", $agentinfo->login_duration);
                $data->not_ready_time = $agentinfo->not_ready_time;
                $data->available_time = $agentinfo->login_duration > $agentinfo->not_ready_time ? $agentinfo->login_duration - $data->not_ready_time : 0;
                $data->available_time = gmdate("H:i:s", $data->available_time);
                $data->total_idle_time = $agentinfo->login_duration - ($data->not_ready_time + $data->ring_time +  $data->talk_time + $data->wrap_up_time);
                $data->total_idle_time = $data->total_idle_time > 0 ? gmdate("H:i:s", $data->total_idle_time) : '00:00:00';
                $data->logout_time = empty($agentinfo->logout_time) ? 'In Progress' : date($date_format." H:i:s",strtotime($agentinfo->logout_time));
                $data->not_ready_count = $agentinfo->not_ready_count;
                $data->agent_info = $agentinfo;

                $data->agent_name = !empty($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : null;
                $data->total_offered = $sms_agent_info[$data->agent_id]->total_offered;
                $data->total_wrapup_time = $sms_agent_info[$data->agent_id]->total_wrapup_time;
                $data->total_in_kpi = $sms_agent_info[$data->agent_id]->total_in_kpi;
                $data->total_service_time = $sms_agent_info[$data->agent_id]->total_serve_time;
                $data->total_wait_time = $sms_agent_info[$data->agent_id]->total_wait_time;
                $data->aht = $sms_agent_info[$data->agent_id]->aht;
                $data->awt = $sms_agent_info[$data->agent_id]->avg_wait_time;
                $data->sl = $sms_agent_info[$data->agent_id]->service_level;
                $data->total_served = $sms_agent_info[$data->agent_id]->total_served;
            }
        }

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        array_push($response->hideCol,'sdate');
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate
        ];
        $this->reportAudit('NR', 'Agent SMS Performance', $request_param);
        $this->ShowTableResponse();
    }

    public function actionReportAutoSms()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$date_range['to']),'Y-m-d H:i'))) : "23";
            $cli = $this->gridRequest->getMultiParam('cli');

        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '','-1 second', $report_restriction_days);

        if(empty($dateinfo->errMsg)){
            $result = array();
            $csv_titles = array();
            $csv_data = array();
            $isRemoveTag = true; // use csv download
            $delimiter = ',';    // use csv download
            $dbResultRow = DOWNLOAD_PER_PAGE;  // use csv download
            $dbResultOffset = 0;  // use csv download
            $fileInputRow = 1;  // use csv download

            // calculate total count
            $this->pagination->num_records = $report_model->numAutoSms($dateinfo, $cli);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;

            //download
            $request_param = [
                'sdate' => $date_range['from'],
                'edate' => $date_range['to'],
                'cli' => $cli
            ];
            if($this->gridRequest->isDownloadCSV){
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
            }
            $report_model->saveReportAuditRequest('NRD::Auto SMS', $request_param);
            // data read for grid/download
            while (true) {
                if($this->gridRequest->isDownloadCSV){ // for download
                    $result = $report_model->getAutoSms($dateinfo, $cli, $dbResultOffset, $dbResultRow);
                }else{ // for grid view
                    $result = $this->pagination->num_records > 0 ? $report_model->getAutoSms($dateinfo, $cli, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
                }

                if(!empty($result) && count($result) > 0){
                    $fileInputRowCount = 0; // for download

                    foreach ($result as $key => &$data) {
                        $data->sdate = date($date_format . " H:i:s", strtotime($data->log_time));
                        if ($this->gridRequest->isDownloadCSV) { // for download
                            $row = array();
                            foreach ($cols as $key => $value) {
                                $rvalue = "";
                                if ($isRemoveTag) {
                                    if (isset($data->$key)) {
                                        $rvalue = strip_tags($data->$key);
                                    }
                                } else {
                                    if (isset($data->$key)) {
                                        $rvalue = $data->$key;
                                    } else {
                                        $rvalue = "";
                                    }
                                }
                                $rvalue = preg_replace("/&.*?; /", "", $rvalue);
                                array_push($row, $rvalue);
                            }
                            fputcsv($f, $row, $delimiter);
                            $fileInputRowCount++;
                        }
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
                $response->hideCol = array_merge($response->hideCol, $report_hide_col);
                $response->rowdata = $result;
                $this->ShowTableResponse();
            }
        }
    }

    function actionUnattendedCdrSummary()
    {
        $this->includeUnattendedCdrSummaryFiles();
        $unattended_cdr_model = new MUnattended();
        $fraction_format = "%.2f";
        $skill_id = '';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }
        $dateinfo=DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);

        $this->pagination->num_records = $unattended_cdr_model->numUnattendedCdrSummary($dateinfo, $skill_id);
        $result = $this->pagination->num_records > 0 ?
            $unattended_cdr_model->getUnattendedCdrSummary($dateinfo, $skill_id, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

        if (is_array($result) && count($result) > 0) {
            $skill_model = new MSkill();
            $skill_options = $skill_model->getSkillsTypeWithNameArray();
            $skill_type = 'V';
            foreach ($result as &$data){
                $data->skill = $skill_options[$skill_type][$data->skill_id];
                $data->avg_hold_in_q = fractionFormat($data->avg_hold_in_q = $data->hold_in_q / $data->total_call, $fraction_format, $this->gridRequest->isDownloadCSV);
                $data->sdate = report_date_format($data->sdate);
            }
        }
        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        $request_param = [
            'sdate' => $date_range['from'],
            'edate' => $date_range['to'],
            'skill_id' => $skill_id
        ];
        $this->reportAudit('NR', 'Unattended Cdr Summary Report', $request_param);
        $this->ShowTableResponse();
    }

    private function includeUnattendedCdrSummaryFiles()
    {
        include('model/MReportNew.php');
        include('model/MUnattended.php');
        include('model/MSkill.php');
        include('lib/DateHelper.php');
    }

    function actionUnattendedCdrDetails()
    {
        $this->includeUnattendedCdrDetailsFiles();
        $unattended_cdr_model = new MUnattended();
        $skill_id = '';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
            $remove_date_range = $this->gridRequest->getMultiParam('remove_time');
            $remove_date_from = !empty($remove_date_range['from']) ? generic_date_format_from_report_datetime($remove_date_range['from'], $date_format) : date('Y-m-d');
            $remove_date_to = !empty($remove_date_range['to']) ? generic_date_format_from_report_datetime($remove_date_range['to'], $date_format) : date('Y-m-d');
            $remove_hour_from = !empty($remove_date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($remove_date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $remove_hour_to = !empty($remove_date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($remove_date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $cli = $this->gridRequest->getMultiParam('cli');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
            $did = $this->gridRequest->getMultiParam('did');
            $callback_within = $this->gridRequest->getMultiParam('callback_within');
            $removed_by = $this->gridRequest->getMultiParam('removed_by');
            $status = $this->gridRequest->getMultiParam('status');
            $disposition_id = $this->gridRequest->getMultiParam('disposition_id');
            $callid = $this->gridRequest->getMultiParam('callid');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);
        $remove_dateinfo = DateHelper::get_input_report_time_details(false, $remove_date_from, $remove_date_to, $remove_hour_from, $remove_hour_to, '', '-1 second', $report_restriction_days);
        $params = $this->getUnattendedCdrDetailsParamObject($remove_dateinfo, $skill_id, $cli, $agent_id, $did, $callback_within, $removed_by, $status, $disposition_id, $callid);

        $this->pagination->num_records = $unattended_cdr_model->numUnattendedCdrDetails($dateinfo, $params);
        $result = $this->pagination->num_records > 0 ?
            $unattended_cdr_model->getUnattendedCdrDetails($dateinfo, $params, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

        if (is_array($result) && count($result) > 0) {
            $skill_model = new MSkill();
            $agent_model = new MAgent();
            $skill_type = 'V';
            $skill_options = $skill_model->getSkillsTypeWithNameArray();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            $removal_dispositions = $this->getDispositions();
            $disc_party_list =  get_disc_party(null);
            foreach ($result as &$data){
                $data->skill = $skill_options[$skill_type][$data->skill_id];
                $data->sdate = report_date_format($data->sdate);
                $data->stop_time = date(REPORT_DATETIME_FORMAT, strtotime($data->stop_time));
                $data->remove_time = date(REPORT_DATETIME_FORMAT, strtotime($data->manual_update_time));
                $data->threshold_status = $this->getThresholdStatus($data->threshold_status);
                $data->status = $this->getCallStatus($data->status);
                if (!empty($data->removal_status)) $data->removal_status = $this->getRemovalStatus($data->removal_status);
                if (!empty($data->disc_cause)) $data->disc_cause = get_disconnect_cause()[$data->disc_cause];
                $data->disc_party = $disc_party_list[$data->disc_party];
                $data->disposition = $removal_dispositions[$data->disposition_id];
                $data->removal_disposition = $removal_dispositions[$data->removal_disposition_id];
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
            }
        }
        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        $request_param = [
            'sdate' => $date_range['from'],
            'edate' => $date_range['to'],
            'skill_id' => $skill_id
        ];
        $this->reportAudit('NR', 'Unattended Cdr Details Report', $request_param);
        $this->ShowTableResponse();
    }

    private function includeUnattendedCdrDetailsFiles()
    {
        include('model/MUnattended.php');
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');
    }

    private function getThresholdStatus($data)
    {
        if ($data == 'A') {
            return "After Threshold";
        } elseif ($data == 'B') {
            return "Before Threshold";
        }
        return null;
    }

    private function getCallStatus($data)
    {
        if ($data == 'A') {
            return "Abandoned";
        } elseif ($data == 'C') {
            return "Call Back";
        } elseif ($data == 'I') {
            return "In Progress";
        }
        return null;
    }

    private function getRemovalStatus($data)
    {
        if ($data == 'A') {
            return "Auto";
        } elseif ($data == 'M') {
            return "Manual";
        }
        return null;
    }

    private function getDispositions($templateId = UNATTENDED_CDR_DISPOSITION_TEMPLATE)
    {
        $unattended_cdr_model = new MUnattended();
        $dispositionList = $unattended_cdr_model->getRemovingDispositions($templateId);
        $dispositions = array();
        foreach ($dispositionList as $key => $value) {
            $dispositions[$value->disposition_id] = $value->title;
        }
        return $dispositions;
    }

    private function getUnattendedCdrDetailsParamObject($remove_dateinfo, $skill_id, $cli, $agent_id, $did, $callback_within, $removed_by, $status, $disposition_id, $callid)
    {
        $params = new stdClass();
        $params->removeDateInfo = $remove_dateinfo;
        $params->skillId = $skill_id;
        $params->cli = $cli;
        $params->agentId = $agent_id;
        $params->did = $did;
        $params->callbackWithin = $callback_within * 60;
        $params->removedBy = $removed_by;
        $params->status = $status;
        $params->dispositionId = $disposition_id;
        $params->callId = $callid;

        return $params;
    }

	function actionReportBtrcSummary()
    {
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MForcastRgb.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report_model = new MReportNew();
        $forecast_rgb_model = new MForcastRgb();
        $reportDays = UserAuth::getReportDays();
        $repLastDate = "";
        $category_skill_ids = "";
        $fraction_format = "%.2f";

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        if ($this->gridRequest->isMultisearch){
            $datetime_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $skill_ids = $this->gridRequest->getMultiParam('skill_id');
            $skill_type = $this->gridRequest->getMultiParam('skill_type');
            // $category = $this->gridRequest->getMultiParam('category');
            $report_type = $this->gridRequest->getMultiParam('report_type');

            $date_from = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d') : date('Y-m-d');
            $date_to = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d') : date('Y-m-d');
            $hour_from = !empty($datetime_range['from']) ? date("H",strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['from']),'Y-m-d H:i'))) : "00";
            $hour_to = !empty($datetime_range['to']) ? date("H", strtotime(date_format(date_create_from_format($date_format." H:i",$datetime_range['to']),'Y-m-d H:i'))) : "23";
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days );
        if(empty($dateinfo->errMsg)){
            $skill_options = $skill_model->getSkillsNamesArray();
            $skills_types = $skill_model->getSkillsTypeArray();

            if(empty($skill_ids) || $skill_ids=='*'){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }elseif(!empty($skill_ids) && !in_array($skill_ids, $skills_types[$skill_type])){
                $skill_ids = implode("','", $skills_types[$skill_type]);
            }

            // calculate total count
            $this->pagination->num_records = $report_model->numSummaryReport($dateinfo, $skill_ids, $report_type);
            $result = $this->pagination->num_records > 0 ? $report_model->getSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $original_result = [];

            $forecast_rgb_data=[];
            if($report_type==REPORT_DAILY){
                $forecast_rgb_data = $forecast_rgb_model->getReportForecastRgbData($dateinfo);
            }

            if(!empty($result) && count($result) > 0){
                foreach ($result as $key => $data) {
                    if(empty($report_type) || $report_type == '*' || $report_type == REPORT_15_MIN_INV){
                        $data->hour_minute = $data->shour.':'.$data->sminute;
                        $data->half_hour = '';
                    }else if($report_type == REPORT_HALF_HOURLY){
                        $data->hour_minute = $data->shour.':'.($data->hour_minute_val==0 ? '00' : '30');
                        $data->half_hour = '';
                    }else{
                        $data->hour_minute = '';
                        $data->half_hour = '';
                    }

                    $data->category = ''; //$item->name;
                    $data->forecasted_call_count = 0;
                    $data->forecasted_call_percentage = 0;
                    $data->cpc = 0;
                    $data->rgb_call_count = 0;
                    // $data->wrap_up_time = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->wrap_up_call_count = ($this->gridRequest->isDownloadCSV ? '' : '-');
                    // $data->delay_between_call = $data->calls_answerd*DELAY_BETWEEN_CALLS;
                    $data->quarter_no = isset($data->quarter_no) ? $data->quarter_no : '';
                    $data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                    $data->talk_time = $data->service_duration-$data->agent_hold_time;

                    if($this->gridRequest->isDownloadCSV){
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? ($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? $data->wrap_up_time / $data->calls_answerd : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? $data->ring_time / $data->calls_answerd : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? $data->calls_offered / $data->rgb_call_count : 0;
                    }else{
                        $data->avg_handling_time = (!empty($data->calls_answerd)) ? round(($data->ring_time+$data->talk_time+$data->wrap_up_time+$data->agent_hold_time)/$data->calls_answerd) : 0;
                        $data->avg_wrap_up_time = (!empty($data->calls_answerd)) ? round($data->wrap_up_time / $data->calls_answerd) : 0;
                        $data->asa = (!empty($data->calls_answerd)) ? round($data->ring_time / $data->calls_answerd) : 0;
                        // $data->cpc = (!empty($data->rgb_call_count)) ? round($data->calls_offered / $data->rgb_call_count) : 0;
                    }

                    $data->service_level_lte_10_count = (!empty(($data->calls_offered - $data->abd_lte_10_count))) ? fractionFormat(($data->ans_lte_10_count / ($data->calls_offered - $data->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_20_count = (!empty(($data->calls_offered - $data->abd_lte_20_count))) ? fractionFormat(($data->ans_lte_20_count / ($data->calls_offered - $data->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_30_count = (!empty(($data->calls_offered - $data->abd_lte_30_count))) ? fractionFormat(($data->ans_lte_30_count / ($data->calls_offered - $data->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_60_count = (!empty(($data->calls_offered - $data->abd_lte_60_count))) ? fractionFormat(($data->ans_lte_60_count / ($data->calls_offered - $data->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_90_count = (!empty(($data->calls_offered - $data->abd_lte_90_count))) ? fractionFormat(($data->ans_lte_90_count / ($data->calls_offered - $data->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->service_level_lte_120_count = (!empty(($data->calls_offered - $data->abd_lte_120_count))) ? fractionFormat(($data->ans_lte_120_count / ($data->calls_offered - $data->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->abandoned_ratio_10 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_10_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_20 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_20_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_30 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_30_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_60 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_60_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_90 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_90_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->abandoned_ratio_120 = (!empty($data->calls_offered)) ? fractionFormat(($data->abd_lte_120_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->fcr_call_percentage = !empty($data->calls_answerd) ? fractionFormat(($data->fcr_call_count / $data->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->short_call_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->short_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->wrap_up_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->wrap_up_call_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->unique_caller = $data->calls_offered-$data->repeat_cli_1_count;
                    $data->unique_caller_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->unique_caller / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->repeat_call_percentage = (!empty($data->calls_offered)) ? fractionFormat(($data->repeat_cli_1_count / $data->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $data->agent_hangup_percentage = (!empty($data->calls_answerd)) ? fractionFormat(($data->agent_hangup_count / $data->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $data->sdate = date($date_format, strtotime($data->sdate));

                    if($report_type==REPORT_DAILY){
                        $data->forecasted_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['forecast'] : '-';
                        $data->rgb_call_count = (isset($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]) && !empty($forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'])) ? $forecast_rgb_data[$data->sdate.'_'.$data->skill_id]['rgb'] : '-';

                        $data->forecasted_call_percentage = (!empty($data->forecasted_call_count) && $data->forecasted_call_count !='-') ? fractionFormat(($data->calls_offered / $data->forecasted_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                        $data->cpc = (!empty($data->rgb_call_count) && $data->rgb_call_count !='-') ? fractionFormat(($data->calls_offered / $data->rgb_call_count) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    }
                }

                if(!$this->gridRequest->isDownloadCSV){
                    // last row initialization
                    $final_row = $this->pagination->num_records > 0 ? $report_model->getSummaryReport($dateinfo, $skill_ids, $report_type,  $this->pagination->getOffset(), $this->pagination->rows_per_page, false) : null;
                    $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                    $final_row->sdate = "-";
                    $final_row->shour = "-";
                    $final_row->sminute = "-";
                    $final_row->category = "-";
                    $final_row->skill_id = "-";
                    $final_row->skill_name = "-";
                    $final_row->smonth = "-";
                    $final_row->quarter_no = "-";
                    $final_row->hour_minute = "-";
                    // $final_row->wrap_up_time = '-';
                    // $final_row->avg_wrap_up_time = '-';
                    // $final_row->fcr_call_percentage = '-';
                    $final_row->forecasted_call_count = '-';
                    $final_row->rgb_call_count = '-';
                    $final_row->cpc = '-';
                    $final_row->forecasted_call_percentage = '-';
                    // $final_row->wrap_up_call_count = '-';
                    // $final_row->wrap_up_percentage = '-';
                    $final_row->unique_caller = $final_row->calls_offered-$final_row->repeat_cli_1_count;
                    // $final_row->delay_between_call = $final_row->calls_answerd*DELAY_BETWEEN_CALLS;
                    $final_row->talk_time = $final_row->service_duration-$final_row->agent_hold_time;

                    $final_row->avg_handling_time = (!empty($final_row->calls_answerd)) ? round(($final_row->ring_time+$final_row->talk_time+$final_row->wrap_up_time+$final_row->agent_hold_time)/$final_row->calls_answerd) : 0;
                    $final_row->avg_wrap_up_time = (!empty($final_row->calls_answerd)) ? round($final_row->wrap_up_time / $final_row->calls_answerd) : 0;
                    $final_row->asa = (!empty($final_row->calls_answerd)) ? round($final_row->ring_time / $final_row->calls_answerd) : 0;

                    $final_row->abandoned_ratio_10 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_10_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_20 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_20_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_30 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_30_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_60 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_60_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_90 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_90_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->abandoned_ratio_120 = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->abd_lte_120_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->fcr_call_percentage = !empty($final_row->calls_answerd) ? fractionFormat(($final_row->fcr_call_count / $final_row->calls_answerd)*100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->short_call_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->short_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->wrap_up_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->wrap_up_call_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->unique_caller_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->unique_caller / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->repeat_call_percentage = (!empty($final_row->calls_offered)) ? fractionFormat(($final_row->repeat_cli_1_count / $final_row->calls_offered) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->agent_hangup_percentage = (!empty($final_row->calls_answerd)) ? fractionFormat(($final_row->agent_hangup_count / $final_row->calls_answerd) * 100, $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $final_row->service_level_lte_10_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_10_count))) ? fractionFormat(($final_row->ans_lte_10_count / ($final_row->calls_offered - $final_row->abd_lte_10_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_20_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_20_count))) ? fractionFormat(($final_row->ans_lte_20_count / ($final_row->calls_offered - $final_row->abd_lte_20_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_30_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_30_count))) ? fractionFormat(($final_row->ans_lte_30_count / ($final_row->calls_offered - $final_row->abd_lte_30_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_60_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_60_count))) ? fractionFormat(($final_row->ans_lte_60_count / ($final_row->calls_offered - $final_row->abd_lte_60_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_90_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_90_count))) ? fractionFormat(($final_row->ans_lte_90_count / ($final_row->calls_offered - $final_row->abd_lte_90_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';
                    $final_row->service_level_lte_120_count = (!empty(($final_row->calls_offered - $final_row->abd_lte_120_count))) ? fractionFormat(($final_row->ans_lte_120_count / ($final_row->calls_offered - $final_row->abd_lte_120_count)*100), $fraction_format, $this->gridRequest->isDownloadCSV).'%' : '0.00%';

                    $response->userdata = $final_row;
                }
            }

            if($report_type == REPORT_YEARLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['syear'];
            }elseif($report_type == REPORT_QUARTERLY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'sdate', 'syear', 'half_hour', 'hour_minute'];
                $response->showCol = ['quarter_no'];
            }elseif($report_type == REPORT_MONTHLY){
                $response->hideCol = ['shour', 'sminute', 'syear', 'sdate', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['smonth'];
            }elseif($report_type == REPORT_DAILY){
                $response->hideCol = ['shour', 'sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate'];
            }else if($report_type == REPORT_HOURLY){
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'sminute', 'half_hour', 'hour_minute'];
                $response->showCol = ['sdate', 'shour'];
            }else if($report_type == REPORT_HALF_HOURLY){
                $response->hideCol = ['sminute', 'smonth', 'syear', 'quarter_no', 'half_hour', 'shour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }else{
                $response->hideCol = ['smonth', 'syear', 'quarter_no', 'shour', 'sminute', 'half_hour'];
                $response->showCol = ['sdate', 'hour_minute'];
            }

            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $result;
            $request_param = [
                'sdate' => $datetime_range['from'],
                'edate' => $datetime_range['to'],
                'skill_type' => $skill_type,
                'report_type' => $report_type,
                'skill_id' => $this->gridRequest->getMultiParam('skill_id'),
            ];
            $this->reportAudit('NR', 'Skill Summary', $request_param);
            $this->ShowTableResponse();
        }
    }
	
	function actionReportEvaluatorSummary()
    {
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $agent_model = new MAgent();
        $agent_id = '';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName() . '_' . $this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }
        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days);
        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');

        $this->pagination->num_records = $report_model->numEvaluatorSummaryData($dateInfo, $agent_id);
        $result = $this->pagination->num_records > 0 ?
            $report_model->getEvaluatorSummaryData($dateInfo, $agent_id, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

		$response = $this->getTableResponse();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $data) {
                $data->sdate = report_date_format($data->sdate);
                $data->evaluator_name = $agent_options[$data->evaluator_id];
            }
			if(!$this->gridRequest->isDownloadCSV){
                // last row initialization
                $final_row = $this->pagination->num_records > 0 ? $report_model->getEvaluatorSummaryData($dateInfo, $agent_id, $this->pagination->rows_per_page, $this->pagination->getOffset(), false)  : null;
                $final_row = (isset($final_row[0])) ? $final_row[0] : new stdClass();
                $final_row->sdate = "-";
                $final_row->evaluator_id = "-";
                $final_row->evaluator_name = "-";
                $response->userdata = $final_row;
            }
        }        
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        $request_param = [
            'sdate' => $date_range['from'],
            'edate' => $date_range['to'],
            'agent_' => $agent_id
        ];
        $this->reportAudit('NR', 'QA Evaluation Summary Report', $request_param);
        $this->ShowTableResponse();
    }

	function actionReportChatCoBrowseDetails()
    {
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('lib/DateHelper.php');


        $report_model = new MReportNew();
        $agent_id = '';

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }
        if ($this->gridRequest->isMultisearch){
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format_from_report_datetime($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format_from_report_datetime($date_range['to'], $date_format) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H",strtotime(generic_date_format_from_report_datetime($date_range['from'], $date_format, 'Y-m-d H:i'))) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime(generic_date_format_from_report_datetime($date_range['to'], $date_format, 'Y-m-d H:i'))) : "23";
            $agent_id = $this->gridRequest->getMultiParam('agent_id');
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second', $report_restriction_days);

        $this->pagination->num_records = $report_model->numChatCoBrowseDetails($dateinfo, $agent_id);
        $result = $this->pagination->num_records > 0 ?
            $report_model->getChatCoBrowseDetails($dateinfo, $agent_id, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

        if (is_array($result) && count($result) > 0) {
            $agent_model = new MAgent();
            $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
            foreach ($result as &$data) {
                $data->sdate = date($date_format . "H:i:s", strtotime($data->log_start_time));
                $data->agent_name = isset($agent_options[$data->agent_id]) ? ' ' . $agent_options[$data->agent_id] : ' ' . $data->agent_id;
                if ($data->request_type == "C") {
                    $data->request_type = "Customer";
                } elseif ($data->request_type == "A") {
                    $data->request_type = "Agent";
                }
            }
        }

        $response = $this->getTableResponse();
        $response->rowdata = $result;
        $response->records = $this->pagination->num_records;
        $request_param = [
            'sdate' => $date_range['from'],
            'edate' => $date_range['to'],
            'agent_id' => $agent_id
        ];
        $this->reportAudit('NR', 'Web Chat Co Browse Details Report', $request_param);
        $this->ShowTableResponse();
    }

}