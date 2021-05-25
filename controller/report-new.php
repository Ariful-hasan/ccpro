<?php

class ReportNew extends Controller
{
    function __construct() {
        parent::__construct();
    }

    function init()
    {
    }

    function getParameter($param){
        $obj = new stdClass();
        $obj->skill_id = "";
        $obj->avg_method = "CO";
        $obj->sdate = date("Y-m-d");
        $obj->edate = date("Y-m-d");
        $obj->stime = "00";
        $obj->etime = "23";
        $obj->average_condition = "";
        $obj->from = $obj->sdate." 00:00";
        $obj->to = $obj->edate." 23:00";
        $obj->offset = 0;

        if (!empty($param)){
            $param = unserialize((base64_decode($param )));
            $obj->sdate = date("Y-m-d", strtotime($param['sdate']));
            $obj->edate = date("Y-m-d", strtotime($param['edate']));
            $obj->stime =  $param['stime'];
            $obj->etime = $param['etime'];
            $obj->skill_id = $param['skill_id'];
            $obj->avg_method = $param['avg_method'];
            $obj->average_condition = $param['average_condition'];

            $obj->from = $obj->sdate." ".$obj->stime.":00";
            $obj->to = $obj->edate." ".$obj->etime.":00";
            $obj->offset = $param['offset'];
        }
        //GPrint($obj->edate." ".$obj->etime.":00");die;
        return $obj;
    }
    function actionHourlyQueueWaitTimeNew($download_param=null){
        //include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/Pagination.php');
        include('get-report-new-data.php');
        include('lib/DateHelper.php');
        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $request = $this->getRequest();
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['average_combo'] = array("SA"=>"Skill Date Average", "DA"=>"Skill Hour Average", "SH"=>"Skill Hour Date Avg.", "HA"=>"Date Average", "HD"=>"Hour Date Avg.");

        $param_obj = empty($download_param) ? $this->getParameter($request->getRequest('param','')) : $this->getParameter($download_param);
        $result = "";
        $dateInfo = new stdClass();
        //if ($request->isPost()) {
        $date_format = get_report_date_format();
        $dateInfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format_from_report_datetime($request->getRequest('sdate'), $date_format) : $param_obj->sdate;
        $dateInfo->edate = !empty($request->getRequest('edate')) ? generic_date_format_from_report_datetime($request->getRequest('edate'), $date_format) : $param_obj->edate;
        $dateInfo->stime = !empty($request->getRequest('sdate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'H') : $param_obj->stime;
        $dateInfo->etime = !empty($request->getRequest('edate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'H') : $param_obj->etime;
        $skill_id = $request->getRequest('skill_id') ? $request->getRequest('skill_id') : $param_obj->skill_id;
        $average_condition = $request->getRequest('average_condition') ? $request->getRequest('average_condition') : $param_obj->average_condition;
        $avg_method = !empty($request->getRequest('avg_method')) ? $request->getRequest('avg_method') : $param_obj->avg_method;
        //}
        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate, $dateInfo->stime, $dateInfo->etime, '', '-1 second');
        $total_data = $report_model->getHourlyQueueWaitTime($dateInfo,$skill_id, $avg_method);
        //GPrint($total_data);
        $total_prepared_data = $this->prepareHourlyQueueWaitTimeData($total_data,$data['skills']);
        //GPrint($total_prepared_data);die;
        //GPrint($total_prepared_data);

        $pagination = new Pagination();
        if (!empty($download_param)){
            $total_offset_data = $this->getDataByOffset($total_prepared_data, $pagination->rows_per_page, $param_obj->offset, (int)$dateInfo->stime, $dateInfo->etime);
        } else {
            $total_offset_data = $this->getDataByOffset($total_prepared_data, $pagination->rows_per_page, $pagination->getOffset(), (int)$dateInfo->stime, $dateInfo->etime);
        }

        if ($average_condition == "SA"){
            $result = $data['total_skill_average_data'] = $this->getSkillWiseAverageData($total_offset_data, (int)$dateInfo->stime, $dateInfo->etime);
            $hold_arr = [];
            foreach ($total_prepared_data as $val){if (!in_array($val->skill_id,$hold_arr)) $hold_arr[] = $val->skill_id;}
            $pagination->num_records = count($hold_arr);
        } elseif ($average_condition == "HA"){
            $result = $data['total_average_hour_data'] = $this->getHourWiseAverageData($total_offset_data, (int)$dateInfo->stime, $dateInfo->etime);
            $pagination->num_records = count($result);
        } elseif ($average_condition == "DA"){
            $result = $data['total_average_date_data'] = $this->getDateWiseAverageData($total_offset_data, (int)$dateInfo->stime, $dateInfo->etime);
            $pagination->num_records = count($total_prepared_data);
        } elseif ($average_condition == "HD"){
            $result = $this->getHourDateAverageData($total_prepared_data, (int)$dateInfo->stime, $dateInfo->etime);
        } elseif ($average_condition == "SH"){
            $result = $this->getSkillHourDateAverage($total_prepared_data, (int)$dateInfo->stime, $dateInfo->etime);
        } else {
            $result = $total_offset_data;
            $pagination->num_records = count($total_prepared_data);
        }

        $offset = $pagination->getOffset();
        $dataArray = array(
            "sdate"=>"$dateInfo->sdate",
            "edate"=>"$dateInfo->edate",
            "stime"=>"$dateInfo->stime",
            "etime"=>"$dateInfo->etime",
            "skill_id"=>"$skill_id",
            "average_condition"=>"$average_condition",
            "avg_method"=>"$avg_method",
            "offset"=>"$offset"
        );
        $param_link = base64_encode(serialize($dataArray));
        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=hourly-queue-wait-time-new&param=".$param_link);
        //$pagination->num_records = count($total_prepared_data);
        $pagination->num_current_records = count($total_offset_data);

        if (count($result) > 0) {
            foreach ($result as $resultData) {
                $resultData->sdate = date_format(date_create_from_format('Y-m-d', $resultData->sdate), $date_format );
            }
        }
        if (!empty($download_param)){
            return $result;
        }

        $data['from'] = $request->getRequest('sdate') ?$request->getRequest('sdate'): date($date_format . " H:00", strtotime($param_obj->from));
        $data['to'] = $request->getRequest('edate') ? $request->getRequest('edate') :  date($date_format . " H:59", strtotime($param_obj->to));
        $data['pagination'] = $pagination;
        $data['param_link'] = $param_link;
        $data['result'] = $result;
        $data['dateinfo'] = $dateInfo;
        $data['average_condition'] = $average_condition;
        $data['avg_method'] = $avg_method;
        $data['skill_id'] = $skill_id;
        $data['pageTitle'] = 'Hourly Avg. Queue Wait Time';
        $data['request'] = $this->getRequest();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['date_format'] = $date_format;
        //$this->getTemplate()->display('report_new_hourly_queue_wait_time_status_without_grid', $data);
        $this->getTemplate()->display_report('report_new_hourly_queue_wait_time_status_without_grid', $data);
    }


    function getSkillHourDateAverage($data, $start_time, $end_time){
        $hour_avg_data = $this->getDateWiseAverageData($data, (int)$start_time, $end_time);
        $response = [];
        if (!empty($hour_avg_data))
        {
            $data = $hour_avg_data;
            foreach ($hour_avg_data as $key => $val)
            {
                list($date, $key_skill) = explode("_",$key);
                $total = 0;
                $count = 0;
                foreach ($data as $item => $value)
                {
                    list($date, $item_skill) = explode("_",$item);
                    if ($item_skill == $key_skill)
                    {
                        list($min,$sec) = explode(":",$value);
                        $total += $min*60 + $sec;
                        $count++;
                    }
                }
                $response[$key_skill] = $total>0 ?  date("i:s",round($total/$count)) : "00:00";
            }

        }
        return $response;
    }


    function actionDownloadHourlyQueueWaitTime(){
        require_once('lib/DownloadHelper.php');
        $columns = [];
        $request = $this->getRequest();
        $param = $request->getRequest('param','');
        $param_obj = $this->getParameter($param);
        $response = $this->actionHourlyQueueWaitTimeNew($param);

        if (!empty($param_obj) && $param_obj->average_condition == "SA"){
            $columns[] = "Skill";
            foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                $str = $hour < 12 ? " AM" : " PM";   $hour = $hour < 10 ? "0" . $hour. $str : $hour. $str;
                $columns[] = $hour;
            }
        } elseif (!empty($param_obj) && $param_obj->average_condition =="DA"){
            $columns[] = "Date"; $columns[] = "Skill"; $columns[] = "Average";
        } elseif (!empty($param_obj) && $param_obj->average_condition =="HA"){
            foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                $str = $hour < 12 ? " AM" : " PM";   $hour = $hour < 10 ? "0" . $hour. $str : $hour. $str;
                $columns[] = $hour;
            }
        } elseif (!empty($param_obj) && $param_obj->average_condition =="HD"){
            $columns[] = "Average";
        } else {
            $columns[] = "Date";
            $columns[] = "Skill";
            foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                $str = $hour < 12 ? " AM" : " PM";   $hour = $hour < 10 ? "0" . $hour. $str : $hour. $str;
                $columns[] = $hour;
            }
        }

        $pageTitle = 'Hourly Queue Wait Time';
        $dl_helper = new DownloadHelper($pageTitle, $this->getTemplate());
        $dl_helper->create_file('hourly-queue-wait-time.csv');

        /*--------------------Header row----------------*/
        foreach ($columns as $ckey => $cval) {
            $dl_helper->write_in_file("{$cval},");
        }
        $dl_helper->write_in_file("\n");
        /*--------------------Header row----------------*/

        if (!empty($param_obj) && $param_obj->average_condition == "SA"){
            foreach ($response as $key => $value){
                $dl_helper->write_in_file($key.",");
                foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                    $hour = $hour < 10 ? "shour_0" . $hour : "shour_".$hour;
                    $dl_helper->write_in_file($value[$hour].",");
                }
                $dl_helper->write_in_file("\n");
            }
        } elseif (!empty($param_obj) && $param_obj->average_condition == "DA"){
            foreach ($response as $key => $value){
                list($date,$skill) = explode("_",$key);
                $dl_helper->write_in_file($date.",");
                $dl_helper->write_in_file($skill.",");
                $dl_helper->write_in_file($value.",");
                $dl_helper->write_in_file("\n");
            }
        } elseif (!empty($param_obj) && $param_obj->average_condition == "HA"){
            foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                $hour = $hour < 10 ? "shour_0" . $hour : "shour_" .$hour;
                $dl_helper->write_in_file($response[$hour].",");
            }
        } elseif (!empty($param_obj) && $param_obj->average_condition == "HD"){
            $dl_helper->write_in_file($response);
        } else {
            foreach ($response as $key => $value){
                $dl_helper->write_in_file($value->sdate.",");
                $dl_helper->write_in_file($value->skill_id.",");
                foreach (range($param_obj->stime,$param_obj->etime,1) as $hour){
                    $hour = $hour < 10 ? "shour_0" . $hour : "shour_" .$hour;
                    $dl_helper->write_in_file($value->$hour.",");
                }
                $dl_helper->write_in_file("\n");
            }
        }
        $dl_helper->download_file();
        exit;
    }

    function getHourDateAverageData($data, $start_time, $end_time){
        $horizontal_value = $this->getDateWiseAverageData($data, $start_time, $end_time);
        $total_seconds = 0;
        if (!empty($horizontal_value)){
            foreach ($horizontal_value as $key => $value){
                list($min, $sec) = explode(":",$value);
                $total_seconds += ($min*60) + $sec;
            }
        }
        return !empty($total_seconds) ? date("i:s",round($total_seconds/count($horizontal_value))) : "00:00";
    }

    function getDataByOffset($data, $rows_per_page, $offset, $start_time, $end_time){
        $temp_array = array();
        $response_array = array();
        if (!empty($data)){
            $count = 0;
            foreach ($data as $key => $items){
                if ($key >= $offset && $count < $rows_per_page){
                    $count++;
                    $temp_array[] = $items;
                }
            }
        }

        if (!empty($temp_array)){
            foreach ($temp_array as $key => $value){
                $obj = new stdClass();
                $obj->avg_time = $value->avg_time;
                $obj->sdate = $value->sdate;
                $obj->skill_id = $value->skill_id;
                $start_time = (int)$start_time;
                for ($i=$start_time; $i<=$end_time; $i++){
                    $number = ($i<10) ? "0".$i : $i;
                    $str = "shour_".$number;
                    $obj->$str = $value->$str;
                }
                $response_array[] = $obj;
            }
        }
        //GPrint($response_array);die;
        return $response_array;
    }

    function getSkillWiseAverageData($data, $start_time, $end_time){
        $response = array();
        if (!empty($data)){
            $refrence_array  = $data;
            $temp_array = array();
            foreach ($data as $key){
                unset($temp_array);
                foreach ($refrence_array as $item => $value){
                    if ($key->skill_id == $value->skill_id){
                        $temp_array[] = $value;
                    }
                }
                $response[$key->skill_id] = $this->setSkillWiseAverageData($temp_array, $start_time, $end_time);
            }
        }
        return $response;
    }

    function setSkillWiseAverageData($data, $start_time, $end_time){
        $count = count($data);
        $response = array();
        if (!empty($data)){
            $start_time = (int)$start_time;
            for ($i=$start_time; $i<=$end_time; $i++){
                $total_seconds = 0;
                $number = ($i<10) ? "0".$i : $i;
                $str = "shour_".$number;
                foreach ($data as $key){
                    list($min, $sec) = explode(":",$key->$str);
                    $total_seconds += ($min*60) + $sec;
                }
                $response[$str] = !empty($total_seconds) ? date("i:s",round($total_seconds/$count)) : "00:00";
            }
        }
        return $response;
    }

    function getDateWiseAverageData ($data, $start_time, $end_time){
        $response = array();
        if (!empty($data)){
            $total_count = $end_time - ($start_time-1);
            foreach ($data as $key => $value){
                $total_seconds = 0;
                for ($i=$start_time; $i<=$end_time; $i++){
                    $number = $i < 10 ? "0".$i : $i;
                    $str = "shour_".$number;
                    if (array_key_exists($str, $value) ){
                        list($minutes,$seconds) = explode(":",$value->$str);
                        $total_seconds += $minutes*60 + $seconds;
                    }
                }
                $response[$value->sdate.'_'.$value->skill_id] = !empty($total_seconds) ? date("i:s",round($total_seconds/$total_count)) : "00:00";
            }
        }
        return $response;
    }

    function getHourWiseAverageData ($data, $start_time, $end_time){
        $response = array();
        if (!empty($data)){
            $total_count = count($data);
            for ($i=$start_time; $i<=$end_time; $i++){
                $number = $i < 10 ? "0".$i : $i;
                $str = "shour_".$number;

                $total_seconds = 0;
                foreach ($data as $key => $value){
                    if (array_key_exists($str, $value) ){
                        list($minutes,$seconds) = explode(":",$value->$str);
                        $total_seconds += $minutes*60 + $seconds;
                    }
                }
                $response[$str] = !empty($total_seconds) ? date("i:s",round($total_seconds/$total_count)) : "00:00";
            }
        }
        return $response;
    }


    function prepareHourlyQueueWaitTimeData($data, $skills){
        $temp_data = $data;
        $temp_array = array();
        $result = array();
        $obj_array = array();
        if (!empty($data)){
            $grnd = new GetReportNewData();
            foreach ($data as $key){
                $obj_array = null;
                if (!in_array($key->sdate,$temp_array)){
                    foreach ($temp_data as $item){
                        if ($key->sdate == $item->sdate){
                            $obj = new stdClass();
                            $obj->sdate = $item->sdate;
                            $obj->skill_id = $item->skill_id;
                            $obj->shour = $item->shour;
                            $obj->hold_time_in_queue = $item->hold_time_in_queue;
                            $obj->avg_time = $item->avg_time;
                            $obj_array []= $obj;
                        }
                    }
                    $responce = $grnd->calculate_hour_queue($obj_array,$skills, $start_hour=0, $end_hour=23);
                    if (count($responce) > 0){
                        foreach ($responce as $res){
                            $result[] = $res;
                        }
                    }
                    $temp_array[] = $key->sdate;
                }
            }
        }
        return $result;
    }

    function actionHourlyQueueWaitTime(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $dateinfo = DateHelper::get_input_time_details();
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());

        $data['pageTitle'] = 'Hourly Avg. Queue Wait Time : ' . date("M d, Y", $dateinfo->ststamp);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=hourly-queue-wait-time');
        //$data['side_menu_index'] = 'reports';
        $data['hour'] = array(
            "*"=>"All","00"=>"0:00 AM","01"=>"1:00 AM","02"=>"2:00 AM","03"=>"3:00 AM","04"=>"4:00 AM","05"=>"5:00 AM","06"=>"6:00 AM",
            "07"=>"7:00 AM","08"=>"8:00 AM","09"=>"9:00 AM","10"=>"10:00 AM","11"=>"11:00 AM","12"=>"12:00 PM",
            "13"=>"1:00 PM","14"=>"2:00 PM","15"=>"3:00 PM","16"=>"4:00 PM","17"=>"5:00 PM","18"=>"6:00 PM",
            "19"=>"7:00 PM","20"=>"8:00 PM","21"=>"9:00 PM","22"=>"10:00 PM","23"=>"11:00 PM"
        );
        $this->getTemplate()->display_report('report_new_hourly_queue_wait_time_status', $data);
    }

    function actionQueueWaitTimeChart(){
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $skill_model = new MSkill();
        $report_model = new MReportNew();
        $skills = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        //GPrint($skills);
        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $dateinfo->sdate = date("Y-m-d");
        $dateinfo->edate = date("Y-m-d", strtotime("+1 day"));
        $date_format = get_report_date_format();
        $skill_id = "";
        if ($request->isPost())
        {
            $dateinfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
            $dateinfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
            $skill_id = $request->getRequest('skill_id');
            $skill_id = $skill_id !="*" ? $skill_id : "";
        }
        $dateinfo = DateHelper::get_input_report_time_details(false, $dateinfo->sdate, $dateinfo->edate, '00', '00', '','-1 second');
        $date_validate = date_parse($dateinfo->sdate);

        $labels = "[";
        $backgroundColor = "[";
        $queuedata = "[";
        $result = "";
        if ($date_validate['error_count'] == 0){
            $result = $report_model->getQueueWaitTimeForChart($dateinfo, $skill_id);
            if (!empty($result)){
                foreach ($result as $key){
                    if (array_key_exists($key->skill_id, $skills)) {
                        $labels .= "'".$skills[$key->skill_id]."',";
                        $backgroundColor .= "'".$this->rand_color()."',";
                        $queuedata .= $key->max_hold_time_in_queue.",";
                    }
                }
            }
        }
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data["result"] = $result;
        $data["labels"] = rtrim($labels,',')."]";
        $data["backgroundColor"] = rtrim($backgroundColor,',')."]";
        $data["queuedata"] = rtrim($queuedata,',')."]";
        //GPrint($data["labels"]);die;
        $data["skills"] = $skills;
        $data['request'] = $request;
        $data['pageTitle'] = 'Maximum Queue Wait Time';  //dd($data);
        $this->getTemplate()->display_report('report_new_queue_wait_time_chart', $data);
    }

    function actionHourlyQueueWaitTimeChart(){
        //include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $skill_model = new MSkill();
        $report_model = new MReportNew();
        $skills = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());

        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $date_format = get_report_date_format();
        $dateinfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
        $dateinfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
        $skill_id = "";
        $dateinfo = DateHelper::get_input_report_time_details(false, $dateinfo->sdate, $dateinfo->edate, '00', '00', '', '-1 second');
        if ($request->isPost())
        {
            //$dateinfo->sdate = generic_date_format($request->getRequest('sdate'));
            //$dateinfo->edate = generic_date_format($request->getRequest('edate'));
            $skill_id = $request->getRequest('skill_id');
            $skill_id = $skill_id !="*" ? $skill_id : "";
        }
        $date_validate = date_parse($dateinfo->sdate);
        //$percent = [];
        //$fixed = [];
        $labels = [];
        $result = "";
        $max_min_array = [];

        if ($date_validate['error_count'] == 0){
            $result = $report_model->getHourlyQueueWaitTimeForChart($dateinfo, $skill_id);
            if (!empty($result)){
                $skillCount = array();
                foreach ($result as $key){
                    if (!in_array($key->skill_name,$skillCount)){
                        $skillCount[] = $key->skill_name;
                    }
                    if (!in_array($key->shour, $labels)){
                        $labels[] = "shour: ".$key->shour;
                    }
                    $max_min_array[] = $key->avg_time;
                }
            }
        }
        $data["format_data"] = $this->prepareChartData($result,$skillCount);
        $data["max_value"] = !empty($max_min_array) ? max($max_min_array) : "";
        $data["min_value"] = !empty($max_min_array) ? min($max_min_array) : "";

        //GPrint($result);die;
        //$data["result"] = $result;
        $data["skillCount"] = $skillCount;
        $data["skills"] = $skills;
        //$data["percent"] = json_encode($percent);
        //$data["fixed"] = json_encode($fixed);
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;

        $data["labels"] = json_encode($labels);
        $data['request'] = $request;
        $data['pageTitle'] = 'Average Hourly Queue Wait Time:: ' . date("d F Y");  //dd($data);
        $this->getTemplate()->display_report('report_new_hourly_queue_wait_time_chart', $data);
    }

    function prepareChartData($result,$skillCount){
        $tempdata = array();
        $str = "[";
        foreach ($skillCount as $key => $value){
            if (!empty($value)){
                unset($tempdata);
                foreach ($result as $item){
                    if ($value == $item->skill_name){
                        $tempdata[] = round($item->avg_time,2);
                        //$tempdata[] = date('i:s',$item->avg_time);
                    }
                }
                $str .= $this->getChartFormat($value, $tempdata);
            }
        }
        return rtrim($str,',')."]";
    }
    function rand_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    function getChartFormat($skill_name,$hold_array){
        $data = "[ ";
        $data .= implode(",",$hold_array);
        //$data .= '"'.implode('","',$hold_array).'"';
        $data .= " ]";
        $color = $this->rand_color();

        $str = '{
                    "label":"'.$skill_name.'",
                    "backgroundColor": "'.$color.'",
                    "borderColor": "'.$color.'",
                    "fill": false,
                    "data":'.$data.'
               },';
        return $str;
    }



    /*function prepareHourlyWaitTimeForChart($data,$skills){
        include ('get-report-new-data.php');
        $getReportNewObj = new GetReportNewData();
        $temp_data = $data;
        $temp_array = array();
        $result = array();
        if (!empty($data)){
            foreach ($data as $key){
                $hour_array = null;
                $avg_array_count = 0;
                $obj_array = null;
                if (!in_array($key->sdate,$temp_array)){
                    foreach ($temp_data as $item){
                        if ($key->sdate == $item->sdate){
                            $obj = new stdClass();
                            $obj->sdate = $item->sdate;
                            $obj->skill_id = $item->skill_id;
                            $obj->shour = $item->shour;
                            $obj->hold_time_in_queue = $item->hold_time_in_queue;
                            $obj->avg_time = $item->avg_time;
                            $obj_array []= $obj;
                        }
                    }
                    $responce = $getReportNewObj->calculate_hour_queue($obj_array,$skills);
                    if (count($responce) > 0){
                        foreach ($responce as $res){
                            $result[] = $res;
                        }
                    }
                    $temp_array[] = $key->sdate;
                }
            }
        }
        return $result;
    }*/

    function actionQueueWaitTime(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        //$report = new MReportNew();
        $dateinfo = DateHelper::get_input_time_details();
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());

        $data['pageTitle'] = 'Queue Wait Time : ' . date("M d, Y", $dateinfo->ststamp);
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=queue-wait-time');
        //$data['side_menu_index'] = 'reports';
        $this->getTemplate()->display_report('report_new_queue_wait_time_status', $data);
    }

    function actionAgentStatus()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $agent_model = new MAgent();
        $report = new MReportNew();

        $dateinfo = DateHelper::get_input_time_details();
        $data['pageTitle'] = 'Agent Status Report : ' . date("M d, Y", $dateinfo->ststamp);

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = $agent_options;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
        $shifts = $report->get_shift_key_value();
        $data['shifts'] = array_merge(array('*'=>'All'),$shifts);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=reportagentstatus');
        $this->getTemplate()->display_report('report_new_agent_status', $data);
    }



    function actionAgentCallStatus()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $agent_model = new MAgent();
        $report = new MReportNew();

        $dateinfo = DateHelper::get_input_time_details();
        $data['pageTitle'] = 'Agent Summary';

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['type'] = report_type_list();
        $data['report_type_list'] = array('*'=>'All') + array_slice($data['type'], 3);
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();

        $shifts = $report->get_shift_key_value();
        $data['shifts'] = array_merge(array('*'=>'All'),$shifts);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=reportagentcallstatus');
        $this->getTemplate()->display_report('report_new_agent_call_status', $data);
    }


    function actionAgentOutboundCallStatus()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        include('model/MSetting.php');

        $setting_model = new MSetting();
        $agent_model = new MAgent();
        $report = new MReportNew();


        $data['pageTitle'] = 'Agent Outbound Activity';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['aux_messages'] = $setting_model->getBusyMessages('Y');


        $shifts = $report->get_shift_key_value();
        $data['shifts'] = array_merge(array('*'=>'All'),$shifts);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-outbound-call-status');
        $this->getTemplate()->display_report('report_new_agent_outbound_call_status', $data);
    }

    function actionAgentProductivityStatus()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $agent_model = new MAgent();
        $report = new MReportNew();


        $data['pageTitle'] = 'Agent Productivity ';

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
        $shifts = $report->get_shift_key_value();
        $data['shifts'] = array_merge(array('*'=>'All'),$shifts);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-productivity-status');
        $this->getTemplate()->display_report('report_new_agent_productivity_status', $data);
    }

    function actionDispositionCall()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_new  = new MReportNew();
        $dispositions = array("*"=>"All") + $report_new->getDispositionTreeOptions();

        $dateinfo = DateHelper::get_input_time_details();

        $data['dispositions'] = $dispositions;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Call Disposition Summary : ' . date("M d, Y", $dateinfo->ststamp);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=reportdispostioncallsummary');
        $this->getTemplate()->display_report('report_new_disposition_call_summary', $data);
    }

    function actionDispositionDetails()
    {
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('model/MSkillCrmTemplate.php');

        $report_model = new MReportNew();
        $template_model = new MSkillCrmTemplate();
        $agent_model = new MAgent();

        $data['template_options'] = $template_model->getTemplateSelectOptions(true);
        $data['template_options']['*'] = "All";
        $data['dp_options'] = array("*"=>"All") + $report_model->getDispositionTreeOptions();
        $data['agents'] = array("*"=>"All") + $agent_model->get_as_key_value();
        $data['pageTitle'] = 'Call Disposition Details';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=disposition-details');
        $this->getTemplate()->display_report('report_new_disposition_details', $data);
    }

    function actionAutodialDispositionCall()
    {
        include ('model/MSkill.php');
        include('lib/DateHelper.php');

        $skill_model = new MSkill();

        $dateinfo = DateHelper::get_input_time_details();

        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Daily Call Disposition : ' . date("M d, Y", $dateinfo->ststamp);
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=autodial-dispostion-call');
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_new_autodial_disposition_call_summary', $data);
    }

    public function actionDailySkillSummary()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $skill_id = "*";

        $start_date = date("Y-m-d");
        if ($request->isPost())
        {
            $start_date = $request->getRequest('sdate');
            $skill_id = $request->getRequest('skill_id');
        }
        $date_validate = date_parse($start_date);

        $data['data'] = json_encode(array());
        if ($date_validate['error_count'] == 0){
            $data['data'] = json_encode($report_model->getDailySkillSummary($start_date,$skill_id));
        }

        $data['request'] = $request;
        $data['skills'] = $skill_model->getSkillsNamesArray();
        $data['pageTitle'] = 'Daily Skill Summary :: ' . date("d F Y");
        $this->getTemplate()->display_report('report_new_daily_skill_summary', $data);
    }
    public function actionCallbackRequests()
    {
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Callback Requests';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=callback-requests');
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_new_callback_requests', $data);
    }

    public function actionHourlyCallStatus()
    {
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $data['pageTitle'] = 'Hourly Call Status';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=hourly-call-status');
        $data['side_menu_index'] = 'new_report';
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $this->getTemplate()->display_report('report_new_hourly_call_status', $data);
    }

    public function actionHourlyCallStatusChart()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $dateinfo->sdate = date("Y-m-d");
        $dateinfo->edate = date("Y-m-d",strtotime("+1 day"));
        $date_format = get_report_date_format();
        $skill_id = "";

        if ($request->isPost())
        {
            $dateinfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
            $dateinfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
            $skill_id = $request->getRequest('skill_id');
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data["callStatus"] =  $report_model->getHourlyCallStatusChart($dateinfo, $skill_id);
        $data["skills"] = $skill_model->getSkillsNamesArray();
        $data['request'] = $request;
        $data['pageTitle'] = 'Hourly Average Call Status (Chart)';
        $this->getTemplate()->display_report('report_new_hourly_call_status_chart', $data);
    }

    public function actionHourlyServiceLevelStatus()
    {
        include ('model/MSkill.php');
        include("conf.extras.php");
        $skill_model = new MSkill();

        $service_level = $extra->sl_method == "A" ? "(Answered within service level / Calls offered) * 100" :
            "(Calls answered within service level / (Calls answered + Calls abandoned after threshold)) * 100";

        $data['pageTitle'] = 'Hourly Service Level Status';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skills'] = array_merge(["*" => "All"],$skill_model->getSkillsNamesArray());
        $data['service_level'] = "<b>*</b>Service Level = ".$service_level;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=hourly-service-level-status');
        $this->getTemplate()->display_report('report_new_hourly_service_level_status', $data);
    }

    public function actionCallVolume()
    {
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $data['pageTitle'] = 'Call Volume Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skills'] = array_merge(array("*" => "All"),$skill_model->getSkillsNamesArray());
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=call-volume');
        $this->getTemplate()->display_report('report_new_call_volume', $data);
    }

    public function actionCallVolumeChart()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $parameters = new stdClass();
        $parameters->sdate = date("Y-m-d");
        $parameters->edate = date("Y-m-d",strtotime("+1 day"));
        $date_format = get_report_date_format();
        $parameters->skill_id = "";

        if ($request->isPost())
        {
            $parameters->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
            $parameters->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
            $parameters->skill_id = $request->getRequest('skill_id');
        }
        $parameters = DateHelper::get_input_report_time_details(false, $parameters->sdate, $parameters->edate, '00', '00', '','-1 second');
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data["callVolumes"] =  $report_model->getCallVolumeChart($parameters);
        $data["skills"] = $skill_model->getSkillsNamesArray();
        $data['request'] = $request;
        $data['pageTitle'] = 'Call Volume (Chart)';
        $this->getTemplate()->display_report('report_new_call_volume_chart', $data);
    }

    /*
    public function actionHourlyCallStatuss()
    {
        include ('model/MReportNew.php');
        $report_model = new MReportNew();
        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $dateinfo->sdate = date("Y-m-d");
        $dateinfo->edate = date("Y-m-d");
        if ($request->isPost())
        {
            $dateinfo->sdate = trim($request->getRequest('sdate'));
            $dateinfo->edate = trim($request->getRequest('edate'));
        }
        $datetime1 = date_create($dateinfo->sdate);
        $datetime2 = date_create($dateinfo->edate);
        $interval = date_diff($datetime1, $datetime2);
        $interval = $interval->format('%a');
        $response = [];
       if ($interval >= 0 )
       {
           $response = $report_model->getHourlyCallStatus($dateinfo);
       }

        $hourly_situations = [];
        foreach ($response as $res)
        {
            $hourly_situations[$res->sdate]["Offered"][$res->shour] = $res->call_offered;
            $hourly_situations[$res->sdate]["Answered"][$res->shour] = $res->call_answered;
            $hourly_situations[$res->sdate]["Abandoned"][$res->shour] = $res->call_abandoned;
        }
        $data['hourly_situations'] = $hourly_situations;
        $data['pageTitle'] = 'Hourly Call Status';
        $this->getTemplate()->display('report_new_hourly_call_statuss', $data);
    } */

    public function actionCallsPerDispositionChart()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $dateinfo->sdate = date("Y-m-d");
        $dateinfo->edate = date("Y-m-d");
        $dateinfo->skill_id = "*";

        if ($request->isPost())
        {
            $dateinfo->sdate = $request->getRequest('sdate');
            $dateinfo->edate = $request->getRequest('edate');
            $dateinfo->skill_id = $request->getRequest('skill_id');
        }
        $date_validate = date_parse($dateinfo->sdate);

        $calls = array();
        if ($date_validate['error_count'] == 0){
            $calls = $report_model->getAutoDialCallsPerDispositionChart($dateinfo);
        }

        $list = [];
        foreach ($calls as $call)
        {
            if(empty($list[$call->disposition_id]))
            {
                $list[$call->disposition_id] = $call->call_count;
            }else{
                $list[$call->disposition_id] += $call->call_count;
            }
        }

        $data["skills"] = $skill_model->getSkillsNamesArray();
        $data["data"] = json_encode($list);
        $data['request'] = $request;
        $data['pageTitle'] = 'Auto Dial Calls Per Disposition :: ' . date("d F Y");
        //$data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_new_calls_per_disposition_chart', $data);
    }


    public function actionPdDailySuccessFailCall()
    {
        include ('model/MReportNew.php');
        $report_model = new MReportNew();


        $request = $this->getRequest();
        $start_date = date("Y-m-d",strtotime("- 30 Days"));
        $end_date = date("Y-m-d");

        if ($request->isPost())
        {
            $start_date = $request->getRequest('start_date');
            $end_date = $request->getRequest('end_date');
        }

        $calls = [];
        if (strtotime($start_date) && strtotime($end_date) && strtotime($start_date) <= strtotime($end_date) ){
            $calls = $report_model->getDailyPDCallSuccessFailurSummary($start_date,$end_date);
        }

        $datetime1 = date_create($start_date);
        $datetime2 = date_create($end_date);
        $interval = date_diff($datetime1, $datetime2);
        $report_day = $interval->format('%a days');

        $all_dates = [];
        if (strtotime($start_date) <= strtotime($end_date))
        {
            $start_log_time = strtotime($start_date);
            for($i=0; $i <= $report_day; $i++)
            {
                $logtime = $start_log_time + $i*86400;
                $all_dates[date("Y-m-d",$logtime)] = new stdClass();
                $all_dates[date("Y-m-d",$logtime)]->call_date = date("Y-m-d",$logtime);
                $all_dates[date("Y-m-d",$logtime)]->success_call = 0;
                $all_dates[date("Y-m-d",$logtime)]->fail_call = 0;

            }
        }

        foreach ($calls as $call)
        {
            if (array_key_exists($call->call_date, $all_dates))
            {
                $all_dates[$call->call_date]->success_call = $call->success_call;
                $all_dates[$call->call_date]->attempted_call = $call->attempted_call;
            }
        }

        $data["data"] = json_encode($all_dates);
        $data['request'] = $request;
        $data['pageTitle'] = 'Auto Dial Call Summary :: ' . date("d F Y");
        //$data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_new_daily_success_fail_call_count_chart', $data);
    }

    public function actionAgentOutboundActivity()
    {
        include('model/MAgent.php');
        $agent_model = new MAgent();

        $data['pageTitle'] = 'Agent Activity (Outbound)';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['agents'] = array("*" => "All")+$agent_model->get_as_key_value();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-activity-outbound');
        $this->getTemplate()->display_report('report_new_agent_outbound_activity', $data);
    }

    public function actionAbandonedCallsChart()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $dateinfo = new stdClass();
        $date_format = get_report_date_format();
//        $dateinfo->sdate = date("Y-m-d");
//        $dateinfo->edate = date("Y-m-d");
        $dateinfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
        $dateinfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
        $dateinfo->skill_id = "*";
        $dateinfo = DateHelper::get_input_report_time_details(false, $dateinfo->sdate, $dateinfo->edate, '00', '00', '', '-1 second');
        if ($request->isPost())
        {
            //$dateinfo->sdate = $request->getRequest('sdate');
            //$dateinfo->edate = $request->getRequest('edate');
            $dateinfo->skill_id = $request->getRequest('skill_id');
        }
        $date_validate = date_parse($dateinfo->sdate);

        $abandoned_calls = [];
        $percent = [];
        $fixed = [];
        $labels = [];
        if ($date_validate['error_count'] == 0){
            $abandoned_calls = $report_model->getAbandonedCallsChart($dateinfo);
        }


        $datetime1 = date_create($dateinfo->sdate);
        $datetime2 = date_create($dateinfo->edate);
        $interval = date_diff($datetime1, $datetime2);
        $report_day = $interval->format('%a days');

        $all_dates = [];
        if (strtotime($dateinfo->sdate) <= strtotime($dateinfo->edate))
        {
            $start_log_time = strtotime($dateinfo->sdate);
            for($i=0; $i <= $report_day; $i++)
            {
                $logtime = $start_log_time + $i*86400;
                $all_dates[date("Y-m-d",$logtime)] = new stdClass();
                $all_dates[date("Y-m-d",$logtime)]->sdate = date("Y-m-d",$logtime);
                $all_dates[date("Y-m-d",$logtime)]->calls_offered = 0;
                $all_dates[date("Y-m-d",$logtime)]->calls_abandoned = 0;

            }
        }



        if (!empty($abandoned_calls)){
            foreach ($abandoned_calls as $call)
            {
                if (array_key_exists($call->sdate, $all_dates))
                {
                    $all_dates[$call->sdate]->calls_offered = $call->calls_offered;
                    $all_dates[$call->sdate]->calls_abandoned = $call->calls_abandoned;
                }
            }
        }

        foreach ($all_dates as $call)
        {
            $percent[] = $call->calls_offered > 0 ? sprintf("%.2f",100 * ($call->calls_abandoned/$call->calls_offered)) : 0;
            $fixed[] = (int) $call->calls_abandoned;
            $labels[] = $call->sdate;
        }

        $data['report_date_format'] = $date_format;
        $data["skills"] = $skill_model->getSkillsNamesArray();
        $data["percent"] = json_encode($percent);
        $data["fixed"] = json_encode($fixed);
        $data["labels"] = json_encode($labels);
        $data['request'] = $request;
        $data['pageTitle'] = 'Abandoned Calls :: ' . date("d F Y");  //dd($data);
        $this->getTemplate()->display_report('report_new_abandoned_calls_chart', $data);
    }

    function actionAgentIdleTime()
    {
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $agent_model = new MAgent();
        $report = new MReportNew();

        $data['pageTitle'] = 'Agent Idle Time : ' . date("M d, Y");
        $data['agent_list'] = array('*'=>'All')+$agent_model->get_as_key_value();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['shifts'] = array_merge(array('*'=>'All'),$report->get_shift_key_value());
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=reportagentcallstatus');
        $this->getTemplate()->display_report('report_new_agent_idle_time', $data);
    }


    function actionAgentIdleTimeChart()
    {
        include('model/MReportNew.php');
        include ('model/MAgent.php');
        include('lib/DateHelper.php');
        $agent_model = new MAgent();
        $report_model = new MReportNew();

        $dateInfo = new stdClass();
        $dateInfo->sdate = date('Y-m-d');
        $dateInfo->edate = date('Y-m-d',strtotime("+1 day"));
        $date_format = get_report_date_format();
        $agent_id = "";
        $request =  $this->request;


        if ($request->isPost())
        {
//            $dateInfo->sdate = $request->getRequest('sdate',$dateInfo->sdate);
//            $dateInfo->edate = $request->getRequest('edate',$dateInfo->edate);
            $dateInfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
            $dateInfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
            $agent_id = $request->getRequest('agent_id',$agent_id);
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate, '00', '00', '','-1 second');
        $date_validate = date_parse($dateInfo->sdate);

        $agent_idle_times = [];
        $available_time = [];
        $calls_in_time = [];
        $calls_out_time = [];
        $idle_time = [];
        $labels = [];
        if ($date_validate['error_count'] == 0){
            $agent_idle_times = $report_model->agentIdleTime($agent_id, $dateInfo);
        }


        $datetime1 = date_create($dateInfo->sdate);
        $datetime2 = date_create($dateInfo->edate);
        $interval = date_diff($datetime1, $datetime2);
        $report_day = $interval->format('%a days');

        $all_dates = [];
        if (strtotime($dateInfo->sdate) <= strtotime($dateInfo->edate))
        {
            $start_log_time = strtotime($dateInfo->sdate);
            for($i=0; $i <= $report_day; $i++)
            {
                $logtime = $start_log_time + $i*86400;
                $all_dates[date("Y-m-d",$logtime)] = new stdClass();
                $all_dates[date("Y-m-d",$logtime)]->sdate = date("Y-m-d",$logtime);
                $all_dates[date("Y-m-d",$logtime)]->available_time = 0;
                $all_dates[date("Y-m-d",$logtime)]->calls_in_time = 0;
                $all_dates[date("Y-m-d",$logtime)]->calls_out_time = 0;
                $all_dates[date("Y-m-d",$logtime)]->idle_time = 0;

            }
        }



        if (!empty($agent_idle_times)){
            foreach ($agent_idle_times as $call)
            {
                if (array_key_exists($call->sdate, $all_dates))
                {
                    $all_dates[$call->sdate]->available_time = $call->available_time;
                    $all_dates[$call->sdate]->calls_in_time = $call->calls_in_time;
                    $all_dates[$call->sdate]->calls_out_time = $call->calls_out_time;
                    $all_dates[$call->sdate]->idle_time = ($call->available_time - $call->calls_in_time - $call->calls_out_time) < 0
                        ? 0 : ($call->available_time - $call->calls_in_time - $call->calls_out_time);
                }
            }
        }

        foreach ($all_dates as $call)
        {
            $available_time[] = $call->available_time;
            $calls_in_time[] = $call->calls_in_time;
            $calls_out_time[] = $call->calls_out_time;
            $idle_time[] = $call->idle_time;
            $labels[] = $call->sdate;
        }



        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data['available_time'] = $available_time;
        $data['calls_in_time'] = $calls_in_time;
        $data['calls_out_time'] = $calls_out_time;
        $data['idle_time'] = $idle_time;
        $data['labels'] = $labels;
        $data['request'] = $request;
        $data['agents'] = $agent_model->get_as_key_value();
        $data['pageTitle'] = 'Agent Idle Time (Chart)';
        $this->getTemplate()->display_report('report_new_agent_idle_time_chart', $data);
    }

    function actionAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Abandoned Call Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=abandoned-call-report');
        $this->getTemplate()->display_report('report_new_abandoned_call_report', $data);
    }


    function actionDailyAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Daily Abandoned Call Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=daily-abandoned-call-report');
        $this->getTemplate()->display_report('report_new_daily_abandoned_call_report', $data);
    }

    function actionHourlyAbandonedCallReport()
    {
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();

        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Hourly Abandoned Call(%) Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=hourly-abandoned-call-report');
        $this->getTemplate()->display_report('report_new_hourly_abandoned_call_report', $data);
    }

    function actionHourlyAbandonedCallChart()
    {
        include('model/MReportNew.php');
        include ('model/MSkill.php');
        include('lib/DateHelper.php');
        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $dateInfo = new stdClass();
        $dateInfo->sdate = date('Y-m-d');
        $dateInfo->edate = date('Y-m-d',strtotime("+1 day"));
        $date_format = get_report_date_format();
        $skill = "";
        $request =  $this->request;


        if ($request->isPost())
        {
            $dateInfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $request->getRequest('date_format')) : date("Y-m-d");
            $dateInfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $request->getRequest('date_format')) : date("Y-m-d");
            $skill = $request->getRequest('skill_id',$skill);
        }
        $dateInfo = DateHelper::get_input_report_time_details(false,  $dateInfo->sdate,  $dateInfo->edate, '00', '00', '','-1 second');
        $abandon_calls = $report_model->getHourlyAbandonedCallReport($skill, $dateInfo);

        $abandon_calls = $this->fillMissingHour($abandon_calls,true,0,23,'');

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data['abandoned_calls'] = $abandon_calls;
        $data['request'] = $request;
        $data['skills'] = $skill_model->getSkillsNamesArray();
        $data['pageTitle'] = 'Hourly Abandoned Call Report (Chart)';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=hourly-abandoned-call-chart');
        $this->getTemplate()->display_report('report_new_hourly_abandoned_call_report_chart', $data);
    }

    function actionAverageHandlingTime()
    {
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $data['pageTitle'] = 'Average Call Handling Time : ' . date("M d, Y");
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skills'] = array_merge(array('*'=>'All'),$skill_model->getSkillsNamesArray());
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=average-handling-time');
        $this->getTemplate()->display_report('report_new_average_handling_time', $data);
    }

    function actionAverageHandlingTimeChart()
    {
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('lib/DateHelper.php');
        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $dateInfo = new stdClass();
        $dateInfo->sdate = date('Y-m-d');
        $dateInfo->edate = date('Y-m-d',strtotime("+1 day"));
        $date_format = get_report_date_format();
        $skill_id = '';

        $request = $this->request;
        if ($request->isPost())
        {
            $dateInfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
            $dateInfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
            $skill_id = $request->getRequest('skill_id',$skill_id);

        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate, '00', '00', '','-1 second');
        $handling_times = $report_model->getAverageHandlingTime($skill_id,$dateInfo,0,0);

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = $date_format;
        $data['pageTitle'] = 'Skill Wise Average Call Handling Time ';
        $data['handling_times'] = $handling_times;
        $data['request'] = $request;
        $data['skills'] = $skill_model->getSkillsNamesArray();
        $this->getTemplate()->display_report('report_new_average_handling_time_chart', $data);
    }

    public function actionDailyServiceLevelChart()
    {
        include ('model/MReportNew.php');
        include ('model/MSkill.php');
        include ('conf.extras.php');
        include('lib/DateHelper.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $dateinfo = new stdClass();
//        $dateinfo->sdate = date("Y-m-d");
//        $dateinfo->edate = date("Y-m-d");
        $date_format = get_report_date_format();
        $dateinfo->sdate = !empty($request->getRequest('sdate')) ? generic_date_format($request->getRequest('sdate'), $date_format) : date("Y-m-d");
        $dateinfo->edate = !empty($request->getRequest('edate')) ? generic_date_format($request->getRequest('edate'), $date_format) : date("Y-m-d",strtotime("+1 day"));
        $dateinfo->skill_id = "*";
        $dateinfo = DateHelper::get_input_report_time_details(false,  $dateinfo->sdate,  $dateinfo->edate, '00', '00', '','-1 second');
        if ($request->isPost())
        {
//            $dateinfo->sdate = $request->getRequest('sdate');
//            $dateinfo->edate = $request->getRequest('edate');
            $dateinfo->skill_id = $request->getRequest('skill_id');
        }
        $date_validate = date_parse($dateinfo->sdate);

        if ($date_validate['error_count'] == 0){
            $service_level = $report_model->getDailyServiceLevelChart($dateinfo);
            $services = [];

            if (!empty($service_level)){
                foreach ($service_level as $service)
                {
                    if (!array_key_exists($service->skill_id, $services))
                    {
                        $services[$service->skill_id] = new stdClass();
                    }

                    $service_level = $extra->sl_method == "A" ?
                        ($service->answerd_within_service_level / $service->calls_offered) * 100 :
                        ($service->answerd_within_service_level / ($service->calls_answerd + $service->abandoned_after_threshold)) * 100;


                    $services[$service->skill_id]->{"h-".$service->shour} = $service_level;
                }
            }
        }

        $data['report_date_format'] =  $date_format;
        $services = $this->fillMissingHour($services);
        $data['services'] = $services;
        $data['request'] = $request;
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['pageTitle'] = 'Daily Service Level :: ' . date("d F Y");
        $this->getTemplate()->display_report('report_new_daily_service_level_chart', $data);
    }

    function actionServiceLevel()
    {
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('conf.extras.php');
        include('lib/DateHelper.php');

        $skill_id = "*";
        $sum_date = "N";
        $sum_hour = "N";

        $dateInfo = new stdClass();
        $dateInfo->sdate = date("Y-m-d");
        $dateInfo->edate = date("Y-m-d",strtotime("+1day"));
        $dateInfo->stime = "00";
        $dateInfo->etime = "00";
        $date_format = get_report_date_format();

        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $request = $this->getRequest();

        if ($request->isPost())
        {
//            $date_format = $request->getRequest('date_format');
            $dateInfo->sdate = generic_date_format_from_report_datetime($request->getRequest('sdate'), $date_format);
            $dateInfo->edate = generic_date_format_from_report_datetime($request->getRequest('edate'), $date_format);
            $dateInfo->stime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d H:i')));
            $dateInfo->etime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d H:i')));
            $sum_date = $request->getRequest('sum_date', 'N');
            $sum_hour = $request->getRequest('sum_hour', 'N');
            $skill_id = $request->getRequest('skill_id', '*');

        }
        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate, $dateInfo->stime, $dateInfo->etime, '','-1 second');

        $result = $report_model->getServiceLevel($dateInfo,$sum_date,$sum_hour,$skill_id);
        $final_response = $this->prepareServiceLevelResult($result);
        $data['result'] = ($sum_hour == "Y") ?  array_values($final_response) : array_values($this->fillMissingHour($final_response,FALSE,$dateInfo->stime,$dateInfo->etime));


        $data['request'] = $request;
        $data['dateinfo'] = $dateInfo;
        $data['skills'] = $skill_model->getSkillsNamesArray();
        $data['hours'] = $this->getHourList($dateInfo->stime, $dateInfo->etime);
        $data['pageTitle'] = 'Service Level';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['date_format'] = $date_format;
        $data['service_level_calculation_method'] = $extra->sl_method == "A" ? "Service Level = (Answered within service level / Calls offered) * 100" :
            "Service Level = (Answered within service level / (Calls answered + Abandoned after threshold)) * 100";
        $this->getTemplate()->display_report('report_new_service_level', $data);
    }


    private function fillMissingHour($response, $isRound=false,$start_hour=0,$end_hour=23,$hour_prefix="h-")
    {
        $start_hour = (int) $start_hour;
        $end_hour = (int) $end_hour;
        $hours = [];
        for($i=$start_hour; $i <= $end_hour; $i++)
        {
            $i = $i < 10 ? "0".$i : $i;
            $hours[$hour_prefix.$i] = $i;
        }

        $default_value = $isRound ? "0" : "0.00";


        foreach ($response as &$data)
        {
            foreach ($hours as $key => $hour)
            {
                if (empty($data->$key))
                {
                    $data->$key = $default_value;
                }

                $data->$key = $isRound ?  round($data->$key) : sprintf("%.2f",$data->$key);

            }
        }
        return $response;
    }

    function actionDownloadServiceLevel()
    {
        $columns = [];
        require_once('lib/DownloadHelper.php');
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $request = $this->getRequest();
        $from = $request->getRequest('sdate');
        $to = $request->getRequest('edate');
        $date_format = get_report_date_format();
        $skill_id = $request->getRequest('skill_id');
        $average_date = $request->getRequest('sum_date');
        $average_hour = $request->getRequest('sum_hour');

        $dateInfo = new stdClass();
        $dateInfo->sdate = generic_date_format_from_report_datetime($from, $date_format);
        $dateInfo->edate = generic_date_format_from_report_datetime($to, $date_format);
        $dateInfo->stime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $from), 'Y-m-d H:i')));
        $dateInfo->etime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $to), 'Y-m-d H:i')));

        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate, $dateInfo->stime, $dateInfo->etime, '','-1 second');
        $report_model = new MReportNew();


        $hours = $this->getHourList($dateInfo->stime,$dateInfo->etime);
        $pageTitle = 'Service Level';

        if ($average_date == "N"){
            $columns[] = "Date";
        }

        if ($average_hour == "Y" ){
            $columns[] = "Service Level";
        }
        else{
            foreach (range($dateInfo->stime,$dateInfo->etime,1) as $hour){
                $columns[] = $hours['h-'.sprintf("%02d",$hour)];
            }
        }

        $result = $report_model->getServiceLevel($dateInfo,$average_date,$average_hour,$skill_id);
        $final_response = $this->prepareServiceLevelResult($result);

        $dl_helper = new DownloadHelper($pageTitle, $this->getTemplate());
        $dl_helper->create_file('service-level.csv');

        /*--------------------Header row----------------*/
        foreach ($columns as $ckey => $cval) {
            $dl_helper->write_in_file("{$cval},");
        }
        $dl_helper->write_in_file("\n");
        /*--------------------Header row----------------*/

        $response = ($average_hour == "Y") ?  array_values($final_response) : array_values($this->fillMissingHour($final_response,FALSE,$dateInfo->stime,$dateInfo->etime));
        foreach ($response as $item){
            $item = (array) $item; ksort($item);
            if (!empty($item['sdate'])){
                $dl_helper->write_in_file(date($date_format, strtotime($item['sdate'])) . ",");
            }
            unset($item['sdate']);
            foreach ($item as $hourly_service_level){
                $dl_helper->write_in_file($hourly_service_level.",");
            }
            $dl_helper->write_in_file("\n");
        }


        $dl_helper->download_file();
        exit;
    }

    private function prepareServiceLevelResult($result)
    {
        $final_response = [];

        if (empty($result)){
            return $final_response;
        }

        foreach ( $result as $item ) {

            if (!array_key_exists($item->sdate,$final_response)){
                $final_response[$item->sdate] = new stdClass();
            }

            $final_response[$item->sdate]->sdate = $item->sdate;

            $service_level = !empty($extra->sl_method) && $extra->sl_method  == "A" ?
                ($item->answerd_within_service_level / $item->calls_offered) * 100 :
                ($item->answerd_within_service_level / ($item->calls_answerd + $item->abandoned_after_threshold)) * 100;

            $hour = empty($item->shour) ? "00" : $item->shour;

            $final_response[$item->sdate]->{"h-".$hour} = sprintf("%.2f",$service_level); //$data->service_level;
        }

        return $final_response;
    }

    private function getHourList($from=0,$to=23,$append="h-",$format="%02d")
    {
        $hours = [];
        $from = ($from < 0 || $from > 23) ? 0 : $from;
        $to = ($to < 0 || $to > 23) ? 0 : $to;

        if ($to < $from) return $hours;

        for ($i=$from; $i <= $to ; $i++)
        {
            $am_pm = $i < 12 ? "AM" : "PM";
            $hour = sprintf('%02d', $i%12);
            $hours[$append.sprintf($format,$i)] = $hour.":00 {$am_pm}";
        }

        return $hours;
    }
    function actionSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');
        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        // $skill_options = $skill_model->getSkillsNamesArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list; //array('*'=>'All') + $skill_options['V'];
        $data['report_type_list'] = array('*'=>'All') + report_type_list();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'V';
        $data['pageTitle'] = 'Summary Report';
        $data['report_type'] = REPORT_DAILY;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-summary');
        $this->getTemplate()->display_report('report_new_summary', $data);
    }
    function actionCategorySummaryReport()
    {
        include('lib/DateHelper.php');
        // include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');
        // $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        // $skill_options = $skill_model->getSkillsNamesArray();
        $skill_category_options = $skill_category_model->getSkillCategoriesNameArray();
        $dateinfo = DateHelper::get_input_time_details();

        // $data['skill_list'] = array('*'=>'All') + $skill_options;
        $data['skill_category_list'] = array('*'=>'---Select---') + $skill_category_options;
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list();
        $data['pageTitle'] = 'Summary Report';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-category-summary');
        $this->getTemplate()->display_report('report_new_category_summary', $data);
    }

    function actionCategoryDetailsReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkillCategory.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();
        $skill_model = new MSkill();

        $skill_category_options = $skill_category_model->getSkillCategoriesNameArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);
        $disposition_ids = $report->get_disposition_as_key_value();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-category-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        // $data['skill_category_list'] = array(''=>'---Select---') + $skill_category_options;
        // $data['report_type_list'] = array(''=>'---Select---') + report_type_list();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['disposition_ids'] = $disposition_ids;
        $data['pageTitle'] = 'Details Report';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['skill_list'] = $skill_list;
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'V';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['hangup_ini_list'] = ['*' => 'All']+get_disc_party('');

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-category-details');
        $this->getTemplate()->display_report('report_new_category_details', $data);
    }

    function actionDailySnapShot() {
        include('model/MReportNew.php');
        include('model/MSkill.php');
        $report = new MReportNew();
        $skill_model = new MSkill();
        $data['pageTitle'] = 'Daily SnapShot - Inbound';

        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type = $skill_list['V'];
        $skill_ids = array_keys($skill_type);
        $skill_ids = implode("','", $skill_ids);

        $data['ftd'] = $report->getDailySnapShotChartDataForDay($skill_ids);
        $data['mtd'] = $report->getDailySnapShotChartDataForMonth($skill_ids);
        $data['ytd'] = $report->getDailySnapShotChartDataForYear($skill_ids);
        $this->getTemplate()->display_report('report_new_daily_snapshot', $data);
    }

    function getAvgWaitTimeForMonth($data, $item) {
        $total_month_data = array();
        $total_today_data = array();
        $yaear_total_avg_count = 0;
        $this_month = date('m',time());
        $today = date('d',time());

        $obj = new stdClass();
        $obj->ftd = 0;
        $obj->mtd = 0;
        $obj->ytd = 0;

        if (!empty($data)) {
            foreach ($data as $key) {
                if (date("d", strtotime($key->sdate)) == $today) {
                    $total_today_data[] = $key->$item;
                }
                if (date("m", strtotime($key->sdate)) == $this_month){
                    $total_month_data[] = $key->$item;
                }
                $yaear_total_avg_count += $key->$item;
            }

            $obj->ftd =count($total_today_data)>1 ? array_sum($total_today_data)/count($total_today_data) : 0;
            //GPrint(round(array_sum($total_month_data)/count($total_month_data)));die;
            $obj->mtd = count($total_month_data)>1 ? array_sum($total_month_data)/count($total_month_data) : 0;
            $obj->ytd = $yaear_total_avg_count/count($data);
            $obj->target = 0.5;
        }
        return $obj;
    }


    function actionDashboardWorkingWithCategory()
    {
        include('lib/DateHelper.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');

        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        $skill_category_options = $skill_category_model->getSkillCategoriesNameArray();
        $dateinfo = DateHelper::get_input_time_details();

        $data['skill_category_list'] = array('*'=>'---Select---') + $skill_category_options;
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list();
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Dashboard Working';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-dashboard-working-with-category');
        $this->getTemplate()->display_report('report_new_dashboard_working', $data);
    }
    function actionDashboardWorking()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');

        $skill_model = new MSkill();
        $report = new MReportNew();

        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-dashboard-working';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list;
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list();
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['pageTitle'] = 'Dashboard Working';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-dashboard-working');
        $this->getTemplate()->display_report('report_new_dashboard_working', $data);
    }
    function actionWorkcodeReport()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        $agent_model = new MAgent();
        $report = new MReportNew();

        $dateinfo = DateHelper::get_input_time_details();
        $data['pageTitle'] = 'Workcode Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-workcode');
        $this->getTemplate()->display_report('report_new_workcode', $data);
    }

    function actionMsisdnReport()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');
        include('model/MSkill.php');
        $agent_model = new MAgent();
        $report = new MReportNew();

        $dateinfo = DateHelper::get_input_time_details();
        $skill_model = new MSkill();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-msisdn';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['pageTitle'] = 'MSISDN Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-msisdn');
        $this->getTemplate()->display_report('report_new_msisdn', $data);
    }
    function actionSkillSetSummaryWithCategoryReport()
    {
        include('lib/DateHelper.php');
        // include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');
        // $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        // $skill_options = $skill_model->getSkillsNamesArray();
        $skill_category_options = $skill_category_model->getSkillCategoriesNameArray();
        $dateinfo = DateHelper::get_input_time_details();

        // $data['skill_list'] = array('*'=>'All') + $skill_options;
        $data['skill_category_list'] = array('*'=>'---Select---') + $skill_category_options;
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list();
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['pageTitle'] = 'SkillSet Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-skill-set-summary-with-category');
        $this->getTemplate()->display_report('report_new_skillset_summary', $data);
    }

    function actionSkillSetSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');

        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-skill-set-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list;
        $data['report_type_list'] = array('*'=>'---Select---') + report_type_list();
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'V';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'SkillSet Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-skill-set-summary');
        $this->getTemplate()->display_report('report_new_skillset_summary', $data);
    }
    function actionOutboundSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MSkill.php');

        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $data['agent_list'] = $agent_model->get_as_key_value();
        $data['skill_list'] = $skill_model->getSkillsTypeWithNameArray();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-outbound-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $dateinfo = DateHelper::get_input_time_details();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Outbound Summary Report';
        $data["skill_type"] = 'O';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-outbound-summary');
        $this->getTemplate()->display_report('report_new_outbound_summary', $data);
    }
    /*function actionServiceRecoveryReport() {
        $data['pageTitle'] = 'Service Recovery Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=service-recovery-report');
        $this->getTemplate()->display_report('report_new_service_recovery', $data);
    }*/


    public function actionWorkcode()
    {
        AddModel('MReportNew');
        AddModel('MSkill');
        include_once('lib/DownloadHelper.php');
        $report_model = new MReportNew();
        $skill_model = new MSkill();

        $result = [];
        $result_datewise = [];
        $summary = [];

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = REPORT_WORKECODE_DAY;
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $request = $this->getRequest();
        $date_format = $request->getRequest('date_format',get_report_date_format());
        $skill_type = $request->getRequest('skill_type','V');
        $skill_id = $request->getRequest('skill_id','*');
        $interval = $request->getRequest('interval','daily');
        $sdate = $datePeriodStart =  $request->getRequest('sdate') ? $this->convertDate($request->getRequest('sdate')) : date(get_report_date_format()." 00:00", strtotime("-".($report_restriction_days-1)." days"));
        $edate = $request->getRequest('edate') ? $this->convertDate($request->getRequest('edate')) : date(get_report_date_format()." 00:00", strtotime("+1 day"));
        $download = $request->getRequest('download','');
        if (in_array($interval, ['quarter-hourly','half-hourly','hourly'])){
            $edate = DateTime::createFromFormat($this->convertDate($date_format)." H:i", $this->convertDate($sdate))->format($date_format." 23:59");
        }
        // GPrint($sdate);die();

        // workcode audit log
        $request_param = [
            'sdate' => $sdate,
            'edate' => $edate,
            'skill_type' => $skill_type,
            'interval' => $interval
        ];
        $report_model->saveReportAuditRequest('NRS::Workcode', $request_param);

        $date_interval = DateInterval::createFromDateString('1 day');
        $date_format_for_header = $date_format;
        $date_format_for_row_access = "Y-m-d";

        if ($interval == 'quarter-hourly'){
            $date_interval = DateInterval::createFromDateString('15 minute');
            $date_format_for_header = $date_format." H:i";
            $date_format_for_row_access = "Y-m-d-H-";
        }
        elseif ($interval == 'half-hourly'){
            $date_interval = DateInterval::createFromDateString('30 minute');
            $date_format_for_header = $date_format." H:i";
            $date_format_for_row_access = "Y-m-d-H-";
        }
        elseif ($interval == 'hourly'){
            $date_interval = DateInterval::createFromDateString('1 hour');
            $date_format_for_header = $date_format." H:00";
            $date_format_for_row_access = "Y-m-d-H:00";
        }elseif ($interval == 'daily'){
            $date_interval = DateInterval::createFromDateString('24 hours');
            $date_format_for_header = $date_format;
            $date_format_for_row_access = 'Y-m-d';
        }elseif ($interval == 'monthly'){
            $date_interval = DateInterval::createFromDateString('1 month');
            $date_format_for_header = REPORT_YEAR_MONTH_FORMAT;
            $date_format_for_row_access = "F,Y";
        }elseif ($interval == 'quarterly'){
            $date_interval = DateInterval::createFromDateString('3 months');
            $date_format_for_header = "n,Y";
            $date_format_for_row_access = "Y-";
        }
        elseif ($interval == 'yearly'){
            $date_interval = DateInterval::createFromDateString('1 year');
            $date_format_for_header = REPORT_YEAR_FORMAT;
            $date_format_for_row_access = "Y";
        }

        $sdate = $this->convertDate($sdate);
        $edate = $this->convertDate($edate);
        $date_format = $this->convertDate($date_format);
        $sdate = DateTime::createFromFormat($date_format." H:i",$sdate)->format("Y-m-d H:i:s");
        $edate = DateTime::createFromFormat($date_format." H:i",$edate)->format("Y-m-d H:i");
        $edate = date("Y-m-d H:i:s", strtotime($edate));
        $datePeriodStart = date("Y-m-d H:i", strtotime($this->convertDate($sdate)));

        if ($interval == 'monthly'){
            $datePeriodStart = date("Y-m-1 H:i", strtotime($this->convertDate($datePeriodStart)));
        }elseif ($interval == 'quarterly'){
            $month = date("m", strtotime($this->convertDate($datePeriodStart)));
            $month = $month < 4 ? '01' : ( $month < 7 ? '04' : ($month < 10 ? '07' : 10 ));
            $datePeriodStart = date("Y-".$month."-1 H:i", strtotime($this->convertDate($datePeriodStart)));
        }elseif ($interval == 'yearly'){
            $datePeriodStart = date("Y-01-01", strtotime($this->convertDate($datePeriodStart)));
        }

        // var_dump($datePeriodStart); dd($edate);
        $dateinfo =  new DatePeriod(new DateTime($sdate), $date_interval, new DateTime($edate));
        $dispositions =  $report_model->get_disposition_as_key_value();
        $data['dispositions'] = $dispositions;
        $response = $report_model->getDayWiseDisposition($sdate, $edate, $skill_id, $interval, $skill_type);
        // dd($date_format_for_row_access);
        // GPrint($sdate);
        // GPrint($edate);
        // die();

        if (!empty($response)){
            foreach ($response as $res) {
                if (empty($result_datewise[$res->csdate])) {
                    $result_datewise[$res->csdate][$res->disposition_id] = new stdClass();
                    $result_datewise[$res->csdate][$res->disposition_id]->calls_answered = 0;
                    $summary[$res->csdate] = new stdClass();
                    $summary[$res->csdate]->calls_answered = 0;
                    $summary[$res->csdate]->disposition_count = 0;
                    $summary[$res->csdate]->disposition_percent = 0;
                }
                if (empty($result_datewise[$res->csdate][$res->disposition_id]->sdate)){
                    $result_datewise[$res->csdate][$res->disposition_id] = new stdClass();
                    $result_datewise[$res->csdate][$res->disposition_id]->calls_answered = 0;
                }
                $result_datewise[$res->csdate][$res->disposition_id]->sdate = $res->csdate;
                $result_datewise[$res->csdate][$res->disposition_id]->call_count = $res->call_count;
                $result_datewise[$res->csdate][$res->disposition_id]->calls_answered += $res->call_count;
                $result_datewise[$res->csdate][$res->disposition_id]->disposition_id = $res->disposition_id;
                $result_datewise[$res->csdate][$res->disposition_id]->disposition_percent = ($res->call_count / $result_datewise[$res->csdate][$res->disposition_id]->calls_answered) * 100;
                $summary[$res->csdate]->calls_answered += $res->call_count;
                $summary[$res->csdate]->disposition_count += !empty($res->disposition_id) ? $res->call_count : 0;
                $summary[$res->csdate]->disposition_percent = round(($summary[$res->csdate]->disposition_count / $summary[$res->csdate]->calls_answered) * 100,2);
            }
        }

        // total ans call
        $total_call_ans_result = $report_model->getTotalAnsCallWorkcode($sdate, $edate, $skill_id, $interval, $skill_type);
        foreach ($dateinfo as $key => $value){
            $d = $value->format($date_format_for_row_access);

            if ($interval == 'quarter-hourly'){
                $d .= str_pad(ceil(((int) $value->format('i') / 15) * 15),2,0);
            }if ($interval == 'half-hourly'){
                $d .= str_pad(ceil(((int) $value->format('i') / 30) * 30),2,0);
            }elseif ($interval == 'hourly'){
                $d = $value->format('Y-m-d-H:00');
            }elseif ($interval == 'daily'){
                $d = $value->format('Y-m-d');
            }elseif ($interval == 'monthly'){
                $d = $value->format('F,Y');
            }elseif ($interval == 'quarterly'){
                $d .= ceil((int) $value->format('m') / 3);
            }elseif ($interval == 'yearly'){
                $d = $value->format('Y');
            }
            foreach ($total_call_ans_result as $key => $item) {
                $str_date = '';
                if ($interval == 'hourly'){
                    $str_date = $item->cdate.'-'.$item->shour.':00';
                }elseif ($interval == 'daily'){
                    $str_date = $item->cdate;
                }elseif ($interval == 'monthly'){
                    $str_date = $item->smonth.','.$item->syear;
                }elseif ($interval == 'quarterly'){
                    $str_date = $item->syear.'-'.$item->quarter_no;
                }elseif ($interval == 'yearly'){
                    $str_date = $item->syear;
                }

                if($d == $str_date){
                    // GPrint($item);
                    $summary[$d]->calls_answered = $item->total_ans_call;
                    $summary[$d]->disposition_percent = (!empty($summary[$d]->calls_answered)) ? fractionFormat(($summary[$d]->disposition_count / $summary[$d]->calls_answered) * 100, '%.2f', $download).'%' : '0.00%';
                    break;
                }else{
                    $summary[$d]->calls_answered = 0;
                    $summary[$d]->disposition_percent = '0.00%';
                }
            }
        }
        // GPrint($total_call_ans_result);
        // GPrint($response);
        // GPrint($result_datewise);
        // GPrint($dateinfo);
        // GPrint($summary);

        foreach ($dispositions as $id => $disposition){
            if (empty($result[$id])){
                $result[$id] = new stdClass();
            }
            $result[$id]->disposition_id = $disposition;
            foreach ($dateinfo as $key => $value){
                $d = $value->format($date_format_for_row_access);

                if ($interval == 'quarter-hourly'){
                    $d .= str_pad(ceil(((int) $value->format('i') / 15) * 15),2,0);
                }if ($interval == 'half-hourly'){
                    $d .= str_pad(ceil(((int) $value->format('i') / 30) * 30),2,0);
                }elseif ($interval == 'hourly'){
                    $d = $value->format('Y-m-d-H:00');
                }elseif ($interval == 'daily'){
                    $d = $value->format('Y-m-d');
                }elseif ($interval == 'monthly'){
                    $d = $value->format('F,Y');
                }elseif ($interval == 'quarterly'){
                    $d .= ceil((int) $value->format('m') / 3);
                }elseif ($interval == 'yearly'){
                    $d = $value->format('Y');
                }
                // GPrint($result_datewise[$d][$id]); echo 'ID='.$id; GPrint($d);
                if(!empty($result_datewise[$d][$id])){
                    $result[$id]->$d = $result_datewise[$d][$id];
                    // $result[$id]->$d->calls_answered = $total_call_ans[$result[$id]->$d->sdate]->total_ans_call;
                    // GPrint($result[$id]->$d);
                }else{
                    $result[$id]->$d = new stdClass();
                }
            }
        }
        // die();

        if ($download){
            $report_model->saveReportAuditRequest('NRD::Workcode', $request_param);

            $count = 0;
            $dl_helper = new DownloadHelper("CC Workcode Report", $this->getTemplate());
            $dl_helper->create_file('cc-workcode-report.csv');

            /*--------------------Header row----------------*/
            $dl_helper->write_in_file("Workcode, ");
            foreach ($dateinfo as $ckey => $cval) {
                //$dl_helper->write_in_file($cval->format($date_format_for_header).", ");
                if ($interval == 'quarterly'){
                    $quarter = ceil($value->format('n') /3);
                    $dl_helper->write_in_file("Quarter: ".$quarter." Year: ".$cval->format(REPORT_YEAR_FORMAT).", ");
                }else{
                    $dl_helper->write_in_file(str_replace(",","-",$cval->format($date_format_for_header)).", ");
                }
            }
            $dl_helper->write_in_file("\n");
            /*--------------------Header row----------------*/

            foreach ($result as $data){
                $dl_helper->write_in_file($data->disposition_id. ",");
                foreach ($dateinfo as $key => $value){
                    $d = $value->format($date_format_for_row_access);
                    if ($interval == 'quarterly'){
                        $d .= ceil((int) $value->format('m') / 3);
                    }
                    if ($interval == 'quarter-hourly'){
                        $d .= str_pad(ceil(((int) $value->format('i') / 15) * 15),2,0);
                    }if ($interval == 'half-hourly'){
                        $d .= str_pad(ceil(((int) $value->format('i') / 30) * 30),2,0);
                    }

                    $call_count = !empty($data->{$d}->call_count) ? $data->{$d}->call_count : "0";
                    $dl_helper->write_in_file($call_count .", ");

                    if ($count < 1){
                        // GPrint($d);
                        // GPrint($summary[$d]);
                        if (!array_key_exists($d,$summary)){
                            $summary[$d] = new stdClass();
                        }
                        $summary[$d]->calls_answered = !empty($summary[$d]->calls_answered) ? $summary[$d]->calls_answered : 0 ;
                        $summary[$d]->disposition_count = !empty($summary[$d]->disposition_count) ? $summary[$d]->disposition_count : 0 ;
                        $summary[$d]->disposition_percent = !empty($summary[$d]->disposition_percent) ? $summary[$d]->disposition_percent : 0 ;
                    }
                }
                $count++;
                $dl_helper->write_in_file("\n");
            }
            // GPrint($summary);
            // die();

            $dl_helper->write_in_file("Workcode Count, ");
            foreach ($summary as $item){
                $dl_helper->write_in_file($item->disposition_count.", ");
            }

            $dl_helper->write_in_file("\n");
            $dl_helper->write_in_file("Calls Answered, ");
            foreach ($summary as $item){
                $dl_helper->write_in_file($item->calls_answered.", ");
            }

            $dl_helper->write_in_file("\n");
            $dl_helper->write_in_file("Percent(%), ");
            foreach ($summary as $item){
                $dl_helper->write_in_file($item->disposition_percent.", ");
            }
            $dl_helper->download_file();
            exit;
        }

        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        $data['summary'] = $summary;
        $data['result'] = $result;
        $data['request'] = $request;
        $data['skills'] = array('*' => '----Select----')+$skill_model->getSkillsNamesArray();
        $data['interval'] = $interval;
        $data['dateinfo'] = $dateinfo;
        $data['date_format_for_header'] = $date_format_for_header;
        $data['date_format_for_row_access'] =   $date_format_for_row_access;
        $data['pageTitle'] = 'Workcode Report';
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = $skill_type;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['report_date_format'] = get_report_date_format();
        $this->getTemplate()->display_report('report_new_workcode', $data);
    }

    function convertDate($date)
    {
        return str_replace('/', '-', $date);
    }

    function actionWorkcodeCountReport()
    {
        include('model/MSkill.php');
        include('model/MReportNew.php');

        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_workcode-count-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['pageTitle'] = 'Workcode Count Report';
        $data['skill_list'] = $skill_model->getSkillsTypeWithNameArray();
        $data['dispositions'] = $report_model->get_disposition_as_key_value();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=workcode-count-report');
        $this->getTemplate()->display_report('report_new_workcode_count_report', $data);
    }

    function actionCallHangUpReport()
    {
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('conf.extras.php');
        include('lib/DateHelper.php');

        $date_format = get_report_date_format();
        $report_type = REPORT_DAILY;
        $skill_type = 'V';
        $time_list = [];
        $hangupIniList = [];
        $dateWiseData = [];
        $total_call_ans = [];

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = DETAILS_REPORT_DAY;
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $dateInfo = new stdClass();
        $dateInfo->stime = "00";
        $dateInfo->etime = "23";
        $dateInfo->sdate = date("Y-m-d", strtotime('-'.$report_restriction_days.' day'));
        $dateInfo->edate = date("Y-m-d", strtotime('-1 day'));

        $skill_model = new MSkill();
        $report_model = new MReportNew();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $dateInfo->sdate = generic_date_format_from_report_datetime($request->getRequest('sdate'), $date_format);
            $dateInfo->edate = generic_date_format_from_report_datetime($request->getRequest('edate'), $date_format);;
            $dateInfo->stime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d H:i')));
            $dateInfo->etime = date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d H:i')));
            $report_type = $request->getRequest('report_type');
            $skill_type = $request->getRequest('skill_type');
        }

        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate,  $dateInfo->stime,  $dateInfo->etime, '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $time_list = $this->getTimeBreakDown($dateInfo, $report_type);

            $result = array();
            if ($report_type == REPORT_DAILY || $report_type == REPORT_15_MIN_INV || $report_type == REPORT_MONTHLY || $report_type == REPORT_YEARLY || $report_type == REPORT_QUARTERLY) {
                $result = $report_model->getCallHangUpReport($dateInfo, $report_type, null, null, $skill_type);
                $total_call_ans_result = $report_model->getTotalAnsCall($dateInfo, $report_type, null, null, $skill_type);
            }elseif ($report_type == REPORT_HOURLY){
                $date = $dateInfo->sdate;
                $result = $report_model->getCallHangUpReport($dateInfo, $report_type, $date, null, $skill_type);
                $total_call_ans_result = $report_model->getTotalAnsCall($dateInfo, $report_type, $date, null, $skill_type);
            }elseif ($report_type == REPORT_HALF_HOURLY){

            }

            foreach ($result as $key => $item) {
                if(!in_array($item->disc_party, $hangupIniList))
                    $hangupIniList[] = $item->disc_party;
                if ($report_type == REPORT_MONTHLY){
                    $date_str = $item->smonth.' '.$item->syear;
                }elseif ($report_type == REPORT_YEARLY){
                    $date_str = $item->syear;
                }elseif ($report_type == REPORT_QUARTERLY) {
                    if ($item->quarter_no == 1) {
                        $date_str = "Jan-Mar ".$item->syear;
                    } elseif ($item->quarter_no == 2) {
                        $date_str = "Apr-Jun ".$item->syear;
                    } elseif ($item->quarter_no == 3) {
                        $date_str = "Jul-Sept ".$item->syear;
                    } elseif ($item->quarter_no == 4) {
                        $date_str = "Oct-Dec ".$item->syear;
                    }
                }elseif ($report_type == REPORT_HOURLY) {
                    $date_str = date("d/m/Y", strtotime($item->sdate)).' '.$item->shour.':00';
                }else{
                    $date_str = date("d/m/Y", strtotime($item->sdate));
                }
                if(!array_key_exists($date_str.'_'.$item->disc_party, $dateWiseData)){
                    $dateWiseData[$date_str.'_'.$item->disc_party]['sdate'] = $date_str;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['disc_party'] = $item->disc_party;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['hang_up_count'] = $item->hang_up_count;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['service_time'] = $item->service_time;
                }
            }

            foreach ($total_call_ans_result as $key => $item) {
                if ($report_type == REPORT_MONTHLY){
                    $date_str = $item->smonth.' '.$item->syear;
                }elseif ($report_type == REPORT_YEARLY){
                    $date_str = $item->syear;
                }elseif ($report_type == REPORT_QUARTERLY) {
                    if ($item->quarter_no == 1) {
                        $date_str = "Jan-Mar ".$item->syear;
                    } elseif ($item->quarter_no == 2) {
                        $date_str = "Apr-Jun ".$item->syear;
                    } elseif ($item->quarter_no == 3) {
                        $date_str = "Jul-Sept ".$item->syear;
                    } elseif ($item->quarter_no == 4) {
                        $date_str = "Oct-Dec ".$item->syear;
                    }
                }elseif ($report_type == REPORT_HOURLY) {
                    $date_str = date("d/m/Y", strtotime($item->sdate)).' '.$item->shour.':00';
                }else{
                    $date_str = date("d/m/Y", strtotime($item->sdate));
                }
                $total_call_ans[$date_str] = $item;
            }
        }

        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        $data['report_type_list'] = array('*'=>'---Select---') + array_slice(report_type_list(), 0, 2);
        $data['dateList'] = $this->getHangupReportDateFormat($time_list,$report_type,"d/m/Y");
        $data ['report_type'] = $report_type;
        $data['results'] =  $dateWiseData; //$finalResult;
        $data['request'] = $request;
        $data['dateinfo'] = $dateInfo;
        $data['skills'] = $skill_model->getSkillsNamesArray();
        $data['pageTitle'] = 'Call Hang-Up Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['date_format'] = $date_format;
        $data['hangup_ini_list'] = $hangupIniList;
        $data['get_disc_party'] = get_disc_party('');
        $data['total_ans_call'] = $total_call_ans;
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = $skill_type;
        $data['report_restriction_days'] = $report_restriction_days;

        $this->getTemplate()->display_report('report_new_call_hangup', $data);
    }

    private function getHangupReportDateFormat($dateList, $reportType, $dateFormat)
    {
        $dateTimeList = array();
        $count = 0;
        foreach ($dateList as $time){
            if ($reportType == REPORT_15_MIN_INV) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d H:i', $time), $dateFormat . ' H:i'));
            } elseif ($reportType == REPORT_HALF_HOURLY) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d H:i', $time), $dateFormat . ' H:i'));
            } elseif ($reportType == REPORT_HOURLY) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d H:i', $time), $dateFormat . ' H:i'));
            } elseif ($reportType == REPORT_DAILY) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d', $time), $dateFormat));
            } elseif ($reportType == REPORT_MONTHLY) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d', $time), "F Y"));
            } elseif ($reportType == REPORT_QUARTERLY) {
                $month = date("m", strtotime($time));
                if ($month <= 3) {
                    array_push($dateTimeList, "Jan-Mar " . date_format(date_create_from_format('Y-m-d', $time), 'Y'));
                } elseif ($month > 3 && $month <= 6) {
                    array_push($dateTimeList, "Apr-Jun " . date_format(date_create_from_format('Y-m-d', $time), 'Y'));
                } elseif ($month > 6 && $month <= 9) {
                    array_push($dateTimeList, "Jul-Sept " . date_format(date_create_from_format('Y-m-d', $time), 'Y'));
                } elseif ($month > 9 && $month <= 12) {
                    array_push($dateTimeList, "Oct-Dec " . date_format(date_create_from_format('Y-m-d', $time), 'Y'));
                }
            } elseif ($reportType == REPORT_YEARLY) {
                array_push($dateTimeList, date_format(date_create_from_format('Y-m-d', $time), 'Y'));
            }
            $count++;

            // if($count == count($dateList)-1)
            // break;
        }
        return $dateTimeList;
    }

    private function getTimeBreakDown($dateInfo, $type)
    {
        $startDate = date('Y-m-d', $dateInfo->ststamp);
        $endDate = date('Y-m-d', $dateInfo->etstamp);
        $startTime = date('H:i', $dateInfo->ststamp);
        $endTime = date('H:i', $dateInfo->etstamp);
        $timeSlabs = array();
        if ($type == REPORT_DAILY) {
            $dateValue = $startDate;
            do {
                array_push($timeSlabs, $dateValue);
                $dateValue = date('Y-m-d', strtotime("+1 day", strtotime($dateValue)));
            } while (strtotime($dateValue) <= strtotime($endDate));
        } elseif ($type == REPORT_15_MIN_INV) {
            $timeValue = $startDate . " " . $startTime;
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-m-d H:i', strtotime("+15 minute", strtotime($timeValue)));
            } while (strtotime($timeValue) <= strtotime($endDate . " " . $endTime));
        } elseif ($type == REPORT_HALF_HOURLY) {
            $timeValue = $startDate . " " . $startTime;
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-m-d H:i', strtotime("+30 minute", strtotime($timeValue)));

            } while (strtotime($timeValue) <= strtotime($endDate . " " . $endTime));
        } elseif ($type == REPORT_HOURLY) {
            $timeValue = $startDate . " " . $startTime;
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-m-d H:i', strtotime("+1 hour", strtotime($timeValue)));

            } while (strtotime($timeValue) <= strtotime($startDate . " 23:00"));
        } elseif ($type == REPORT_MONTHLY) {
            $timeValue = date('Y-m-01',strtotime($startDate));
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-m-01', strtotime("+1 month", strtotime($timeValue)));

            } while (strtotime($timeValue) <= strtotime($endDate));
        } elseif ($type == REPORT_QUARTERLY) {
            $month = date('m',strtotime($startDate));
            if ($month <= 3) {
                $timeValue = date('Y-01-01',strtotime($startDate));
            } elseif ($month > 3 && $month <= 6) {
                $timeValue = date('Y-04-01',strtotime($startDate));
            } elseif ($month > 6 && $month <= 9) {
                $timeValue = date('Y-07-01',strtotime($startDate));
            } elseif ($month > 9 && $month <= 12) {
                $timeValue = date('Y-10-01',strtotime($startDate));
            }
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-m-01', strtotime("+3 month", strtotime($timeValue)));
            } while (strtotime($timeValue) <= strtotime($endDate));

        } elseif ($type == REPORT_YEARLY){
            $timeValue = date('Y-01-01',strtotime($startDate));
            do {
                array_push($timeSlabs, $timeValue);
                $timeValue = date('Y-01-01', strtotime("+1 year", strtotime($timeValue)));
            } while (strtotime($timeValue) <= strtotime($endDate));
        }
        return $timeSlabs;
    }

    private function getCallHangUpFinalData($timeList, $reportData, $reportType){
        $finalData = array();
        foreach ($timeList as $time){
            $flag = false;
            foreach($reportData as $result){
                $resultDate = $result->sdate;
                if($reportType == REPORT_15_MIN_INV || $reportType == REPORT_HALF_HOURLY  ){
                    $resultDate = $result->sdate . " " . $result->shour . ":" . $result->sminute;
                }elseif ($reportType == REPORT_HOURLY){
                    $resultDate = $result->sdate . " " . $result->shour . ":00";
                }elseif($reportType == REPORT_MONTHLY){
                    $resultDate = date('Y-m-01', strtotime($result->sdate));
                }elseif ($reportType == REPORT_YEARLY){
                    $resultDate = date('Y-01-01', strtotime($result->sdate));
                }elseif ($reportType == REPORT_QUARTERLY){
                    if ($result->quarter_no == 1) {
                        $resultDate = date('Y-01-01', strtotime($result->sdate));
                    }elseif ($result->quarter_no == 2){
                        $resultDate =  date('Y-04-01', strtotime($result->sdate));
                    }elseif ($result->quarter_no == 3){
                        $resultDate =  date('Y-07-01', strtotime($result->sdate));
                    }elseif ($result->quarter_no == 4){
                        $resultDate =  date('Y-10-01', strtotime($result->sdate));
                    }
                }
                $data = new stdClass();
                if($resultDate == $time){
                    $data->Agent = $result->Agent;
                    $data->Subscriber = $result->Subscriber;
                    $data->System = $result->System;
                    $data->TotalAnsweredCall = $result->Agent + $result->Subscriber + $result->System;
                    $data->AgentCallsHangupRate = number_format((($result->Agent / ($result->Agent + $result->Subscriber + $result->System)) * 100),2);
                    $data->SubscriberCallsHangupRate = number_format((($result->Subscriber / ($result->Agent + $result->Subscriber + $result->System)) * 100),2);
                    $data->SystemCallsHangupRate = number_format((($result->System / ($result->Agent + $result->Subscriber + $result->System)) * 100),2);
                    array_push($finalData,$data);
                    $flag = true;
                    break;
                }
            }
            if ($flag == false) {
                $data->Agent = 0;
                $data->Subscriber = 0;
                $data->System = 0;
                $data->TotalAnsweredCall = 0;
                $data->AgentCallsHangupRate = 0;
                $data->SubscriberCallsHangupRate = 0;
                $data->SystemCallsHangupRate = 0;
                array_push($finalData, $data);
            }
        }

        return $finalData;
    }

    function actionDownloadCallHangUpReport()
    {
        $columns = [];
        require_once('lib/DownloadHelper.php');
        include('model/MReportNew.php');
        include('lib/DateHelper.php');

        $request = $this->getRequest();
        $from = $request->getRequest('sdate');
        $to = $request->getRequest('edate');
        $report_type = $request->getRequest('report_type');
        $skill_type = $request->getRequest('skill_type');
        $date_format = get_report_date_format();
        $report_model = new MReportNew();

        $dateInfo = new stdClass();
        $dateInfo->sdate = generic_date_format_from_report_datetime($from, $date_format);
        $dateInfo->edate = generic_date_format_from_report_datetime($to, $date_format);
        $dateInfo->stime = date_format(date_create_from_format($date_format." H:i",$from),'H');
        $dateInfo->etime = date_format(date_create_from_format($date_format." H:i",$to),'H');
        $dateInfo = DateHelper::get_input_report_time_details(false, $dateInfo->sdate, $dateInfo->edate,  $dateInfo->stime,  $dateInfo->etime, '','-1 second');
        if(empty($dateInfo->errMsg)){
            $time_list = $this->getTimeBreakDown($dateInfo, $report_type);

            $result = array();

            if ($report_type == REPORT_DAILY || $report_type == REPORT_15_MIN_INV || $report_type == REPORT_MONTHLY || $report_type == REPORT_YEARLY || $report_type == REPORT_QUARTERLY) {
                $result = $report_model->getCallHangUpReport($dateInfo, $report_type, null, null, $skill_type);
                $total_call_ans_result = $report_model->getTotalAnsCall($dateInfo, $report_type, null, null, $skill_type);
            }elseif ($report_type == REPORT_HOURLY){
                $date = $dateInfo->sdate;
                $result = $report_model->getCallHangUpReport($dateInfo, $report_type, $date, null, $skill_type);
                $total_call_ans_result = $report_model->getTotalAnsCall($dateInfo, $report_type, $date, null, $skill_type);
            }elseif ($report_type == REPORT_HALF_HOURLY){

            }
            // ---------------------------------------------
            $hangupIniList = [];
            $dateWiseData = [];
            foreach ($result as $key => $item) {
                if(!in_array($item->disc_party, $hangupIniList))
                    $hangupIniList[] = $item->disc_party;

                if ($report_type == REPORT_MONTHLY){
                    $date_str = $item->smonth.' '.$item->syear;
                }elseif ($report_type == REPORT_YEARLY){
                    $date_str = $item->syear;
                }elseif ($report_type == REPORT_QUARTERLY) {
                    if ($item->quarter_no == 1) {
                        $date_str = "Jan-Mar ".$item->syear;
                    } elseif ($item->quarter_no == 2) {
                        $date_str = "Apr-Jun ".$item->syear;
                    } elseif ($item->quarter_no == 3) {
                        $date_str = "Jul-Sept ".$item->syear;
                    } elseif ($item->quarter_no == 4) {
                        $date_str = "Oct-Dec ".$item->syear;
                    }
                }elseif ($report_type == REPORT_HOURLY) {
                    $date_str = date("d/m/Y", strtotime($item->sdate)).' '.$item->shour.':00';
                }else{
                    $date_str = date("d/m/Y", strtotime($item->sdate));
                }

                if(!array_key_exists($date_str.'_'.$item->disc_party, $dateWiseData)){
                    $dateWiseData[$date_str.'_'.$item->disc_party]['sdate'] = $date_str;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['disc_party'] = $item->disc_party;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['hang_up_count'] = $item->hang_up_count;
                    $dateWiseData[$date_str.'_'.$item->disc_party]['service_time'] = $item->service_time;
                }
            }

            $total_call_ans = [];
            foreach ($total_call_ans_result as $key => $item) {
                if ($report_type == REPORT_MONTHLY){
                    $date_str = $item->smonth.' '.$item->syear;
                }elseif ($report_type == REPORT_YEARLY){
                    $date_str = $item->syear;
                }elseif ($report_type == REPORT_QUARTERLY) {
                    if ($item->quarter_no == 1) {
                        $date_str = "Jan-Mar ".$item->syear;
                    } elseif ($item->quarter_no == 2) {
                        $date_str = "Apr-Jun ".$item->syear;
                    } elseif ($item->quarter_no == 3) {
                        $date_str = "Jul-Sept ".$item->syear;
                    } elseif ($item->quarter_no == 4) {
                        $date_str = "Oct-Dec ".$item->syear;
                    }
                }elseif ($report_type == REPORT_HOURLY) {
                    $date_str = date("d/m/Y", strtotime($item->sdate)).' '.$item->shour.':00';
                }else{
                    $date_str = date("d/m/Y", strtotime($item->sdate));
                }

                $total_call_ans[$date_str] = $item;
            }

            $time_list = $this->getHangupReportDateFormat($time_list,$report_type,"d/m/Y");
            $columns[] = "Hangup Initiator";
            foreach ($time_list as $time){
                $columns[] = $time;
            }

            // GPrint($dateWiseData);
            // GPrint($request);
            // die();

            $request_param = [
                'sdate' => $from,
                'edate' => $to,
                'report_type' => $report_type,
                'skill_type' => $skill_type
            ];
            $report_model->saveReportAuditRequest('NRD::Call Hangup', $request_param);

            $pageTitle = 'Call Hang Up Report';
            $dl_helper = new DownloadHelper($pageTitle, $this->getTemplate());
            $dl_helper->create_file("Call_Hang_Up_Report_".date('Y-m-d_H-i-s').'.csv');

            /*--------------------Header row----------------*/
            foreach ($columns as $ckey => $cval) {
                $dl_helper->write_in_file("{$cval},");
            }
            $dl_helper->write_in_file("\n");
            /*--------------------Header row----------------*/

            foreach ($hangupIniList as $key => $hangup) {
                if(!empty($hangup))
                    $dl_helper->write_in_file(get_disc_party($hangup).',');
                else
                    $dl_helper->write_in_file('Others,');

                foreach ($time_list as $key => $time) {
                    $data = isset($dateWiseData[$time.'_'.$hangup]['hang_up_count']) ? $dateWiseData[$time.'_'.$hangup]['hang_up_count'] : '0';
                    $dl_helper->write_in_file("{$data},");
                }
                $dl_helper->write_in_file("\n");
            }

            $dl_helper->write_in_file("Total Answer Calls,");
            foreach ($time_list as $key => $time) {
                $data = isset($total_call_ans[$time]->total_ans_call) ? $total_call_ans[$time]->total_ans_call : '0';
                $dl_helper->write_in_file("{$data},");
            }
            $dl_helper->write_in_file("\n");

            $dl_helper->write_in_file("Total Offered Calls,");
            foreach ($time_list as $key => $time) {
                $data = isset($total_call_ans[$time]->total_offer_call) ? $total_call_ans[$time]->total_offer_call : '0';
                $dl_helper->write_in_file("{$data},");
            }
            $dl_helper->write_in_file("\n");

            foreach ($hangupIniList as $key => $hangup) {
                if(!empty($hangup))
                    $dl_helper->write_in_file(get_disc_party($hangup).' Calls Hangup %,');
                else
                    $dl_helper->write_in_file('Others Calls Hangup %,');

                foreach ($time_list as $key => $time) {
                    if(isset($dateWiseData[$time.'_'.$hangup]) && !empty($total_call_ans[$time]->total_ans_call)){
                        $data = ($dateWiseData[$time.'_'.$hangup]['hang_up_count']/$total_call_ans[$time]->total_ans_call)*100;
                    }else{
                        $data = '0.00';
                    }
                    $dl_helper->write_in_file("{$data}%,");
                }
                $dl_helper->write_in_file("\n");
            }

            $dl_helper->download_file("Call_Hang_Up_Report_".date('Y-m-d_H-i-s'));
        }
        exit;
    }

    public function actionWebChatSummaryReport()
    {
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $data['pageTitle'] = 'Web Chat Summary Report';
        $data['types'] = array(
            REPORT_DAILY => 'Daily',
            REPORT_MONTHLY => 'Monthly'
        );

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_web-chat-summary-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skills'] = $skill_model->getChatSkills();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=web-chat-summary-report');

        $this->getTemplate()->display_report('report_new_web_chat_summary', $data);

    }

    public function actionOnlineChatEfficiencyReport()
    {
        include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $data['pageTitle'] = 'Online Chat Efficiency Report';
        $data['agents'] =  $agent_model->getAgentNameKeyValue();
        $data['skills'] = $skill_model->getChatSkills();
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=online-chat-efficiency-report');
        $this->getTemplate()->display_report('online_chat_efficiency_report', $data);
    }
    public function actionDateFormat()
    {
        $data['pageTitle'] = 'Set Date Format';
        $this->getTemplate()->display_popup('set_date_format', $data,TRUE);
    }
    public function actionAgentSkillWiseReport()
    {
        include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $agent_options = $agent_model->get_as_key_value();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-skill-wise-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['pageTitle'] = 'Daily Skill Wise Agent Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data["skill_list"] = $skill_list;
        $data['report_type_list'] = [REPORT_HOURLY => 'Hourly', REPORT_DAILY=>'Daily'];
        $data['report_type'] = REPORT_DAILY;

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-skill-wise-report');
        $this->getTemplate()->display_report('report_new_agent_skill_wise', $data);
    }
    public function actionOutboundDetailsReport()
    {
        include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_outbound-details-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['pageTitle'] = 'Outbound Details Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data["skill_list"] = $skill_model->getSkillsTypeWithNameArray();
        $data["report_restriction_days"] = $report_restriction_days;
        $data["skill_type"] = 'O';

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=outbound-details-report');
        $this->getTemplate()->display_report('report_new_outbound_details', $data);
    }

    function actionPdDetailsReport(){
        include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_pd-details-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['pageTitle'] = 'Pd Details Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data["skill_list"] = $skill_model->getSkillsTypeWithNameArray();
        $data["report_restriction_days"] = $report_restriction_days;
        $data["skill_type"] = 'P';

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=pd-details-report');
        $this->getTemplate()->display_report('report_new_pd_details', $data);
    }

    public function actionAgentDetailsReport()
    {
        include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $agent_options = $agent_model->get_as_key_value();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-details-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['pageTitle'] = 'Agent Details Report';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data["skill_list"] = $skill_list;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-details-report');
        $this->getTemplate()->display_report('report_new_agent_details', $data);
    }

    function actionIceSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');

        $skill_model = new MSkill();
        $report = new MReportNew();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $dateinfo = DateHelper::get_input_time_details();

        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-ice-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data["skill_list"] = $skill_list;
        $data['report_type_list'] = array('*'=>'---Select---') + array(
                REPORT_DAILY => 'Daily',
                REPORT_MONTHLY => 'Monthly'
            );
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['pageTitle'] = 'ICE Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-ice-summary');
        $this->getTemplate()->display_report('report_new_ice_summary', $data);
    }

    function actionIceDetailsReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $report = new MReportNew();
        $skill_model = new MSkill();

        $dateinfo = DateHelper::get_input_time_details();
        $agent_options = $agent_model->get_as_key_value();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-ice-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'ICE Details Report';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data["skill_list"] = $skill_list;
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-ice-details');
        $this->getTemplate()->display_report('report_new_ice_details', $data);
    }

    function actionTopReasonsDissatisfaction()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report = new MReportNew();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-top-reasons-dissatisfaction';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Top reasons of dissatisfaction report by date range';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-top-reasons-dissatisfaction');
        $this->getTemplate()->display_report('report_new_top_reasons_dissatisfaction', $data);
    }

    function actionTopAgentsDissatisfaction()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report = new MReportNew();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-top-agents-dissatisfaction';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Top agents of dissatisfaction report by date range';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-top-agents-dissatisfaction');
        $this->getTemplate()->display_report('report_new_top_agents_dissatisfaction', $data);
    }

    function actionCategoryDetailsReport2()
    {
        include('lib/DateHelper.php');
        include('model/MSkillCategory.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();
        $skill_model = new MSkill();

        $skill_category_options = $skill_category_model->getSkillCategoriesNameArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);
        $disposition_ids = $report->get_disposition_as_key_value();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-category-details2';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Details Report';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data['skill_list'] = $skill_list;
        $data['disposition_ids'] = $disposition_ids;
        $data['skill_type_list'] = get_report_skill_type_list();
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';
        $data['hangup_ini_list'] = ['*' => 'All']+get_disc_party('');

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-category-details2');
        $this->getTemplate()->display_report('report_new_category_details2', $data);
    }

    function actionIvrSummary()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_ivr-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $ivr_options = $ivr_model->getIvrOptions();
        unset($ivr_options["AX"]);
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'IVR Summary';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivrs'] = ['*'  => 'All'] + $ivr_options;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=ivr-summary');
        $this->getTemplate()->display_report('report_new_ivr_summary', $data);
    }

    function actionIvrDetails()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_ivr-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $ivr_options = $ivr_model->getIvrOptions();
        unset($ivr_options["AX"]);

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'IVR Details';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivrs'] = ['*'  => 'All'] + $ivr_options;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=ivr-details');
        $this->getTemplate()->display_report('report_new_ivr_details', $data);
    }

    function actionAgentIceSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MReportNew.php');

        $agent_model = new MAgent();

        $agent_list = array('*'=>'All') + $agent_model->get_as_key_value();
        $data["agent_list"] = $agent_list;
        $data['report_type_list'] = array('*' => '---Select---') + array(
                REPORT_DAILY => 'Daily',
                REPORT_MONTHLY => 'Monthly'
            );
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Agent ICE Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-agent-ice-summary');
        $this->getTemplate()->display_report('report_new_agent_ice_summary', $data);
    }

    function actionWebChatDetailsReport()
    {
        include('model/MSkill.php');
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-web-chat-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['pageTitle'] = 'Web Chat Details Report';
        $data['skills'] = $skill_model->getChatSkills();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-web-chat-details');

        $this->getTemplate()->display_report('report_new_web_chat_details', $data);
    }

    function actionQaReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(INBOUND_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'QA Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-qa');
        $this->getTemplate()->display_report('report_new_qa', $data);
    }

    public function actionChatDispositionReport()
    {
        include('model/MSkillCrmTemplate.php');
        $template_model = new MSkillCrmTemplate();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-chat-disposition';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['dp_options'] = $template_model->getDisposChatSelectOptions(true);
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Chat Disposition';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-chat-disposition');
        $this->getTemplate()->display_report('report_new_chat_disposition', $data);
    }

    function actionIceRawDataReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $report = new MReportNew();
        $skill_model = new MSkill();

        $dateinfo = DateHelper::get_input_time_details();
        $agent_options = $agent_model->get_as_key_value();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-ice-raw-data';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agent_list'] = array('*'=>'All') + $agent_options;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'ICE Raw Data';
        $data['report_type'] = REPORT_15_MIN_INV;
        $data["skill_list"] = $skill_list;
        $data['skill_type_list'] = $skill_type_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['skill_type'] = 'V';

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-ice-raw-data');
        $this->getTemplate()->display_report('report_new_ice_raw_data', $data);
    }

    function actionAgentPerformanceSummary()
    {
        die();

        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-performance-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Performance Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-performance-summary');
        $this->getTemplate()->display_report('report_new_agent_performance_summary', $data);
    }

    function actionDailyEmailActivityReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include('model/MSkill.php');
        include('model/MEmail.php');
        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $report_model = new MReportNew();
        $email_model = new MEmail();

        $request = $this->getRequest();
        $date_format = get_report_date_format();
        $sdate = $request->getRequest('sdate');
        $edate = $request->getRequest('edate');
        $skill_id = $request->getRequest('skill_id','*');
        $agent_id = $request->getRequest('agent_id','*');
        $download = $request->getRequest('download','');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName().'_'.$this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = DETAILS_REPORT_DAY;
        $report_hide_col = [];
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $date_from = !empty($sdate) ? generic_date_format($sdate, $date_format) : date('Y-m-d', strtotime('-'.$report_restriction_days.' day'));
        $date_to = !empty($edate) ? generic_date_format($edate, $date_format) : date('Y-m-d');
        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second', $report_restriction_days);
        // GPrint($date_from);
        // GPrint($date_to);
        // GPrint($dateinfo);
        // die();
        if(empty($dateinfo->errMsg)){
            $result = $report_model->getDailyEmailActivity($dateinfo, $agent_id); //all data
            $result_agent_list = $report_model->getDailyEmailActivityAgents($dateinfo, $agent_id); // agent list
            $new_result = [];
            $agent_names = $agent_model->getAgentsFullName();
            // GPrint($agent_names);

            foreach ($result_agent_list as $key => $item) {
                $result_agent_list[$key]->agent_name = $agent_names[$item->agent_id];
            }

            foreach ($result as $key => $item) {
                $idx_str = $item->sdate.'_'.$item->agent_id.'_'.$item->new_activity;
                $new_result[$idx_str] = $item->activity_count;
                $result[$key]->agent_name = $agent_names[$item->agent_id];
            }
            // GPrint($result);
            // GPrint($agent_list);
            // GPrint($new_result);
            // GPrint($result_agent_list);
            // die();

            if ($download){
                $request_param = [
                    'sdate' => $sdate,
                    'edate' => $edate,
                    'skill_id' => $skill_id,
                    'agent_id' => $agent_id
                ];
                $report_model->saveReportAuditRequest('NRD::Daily Email Activity', $request_param);

                $email_activity_list = get_report_email_activity_list();
                $delimiter = ',';
                $csv_email_activity_list = array_merge(['Date','Agents'], get_report_email_activity_list_text());

                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename="daily-email-activity-report'.'_'.date('Y-m-d_H-i-s').'.csv"');
                $f = fopen('php://output', 'w');
                fputcsv($f, $csv_email_activity_list, $delimiter);

                $idx_date = $dateinfo->sdate;
                $csv_result = [];
                while (true) {
                    foreach($result_agent_list as $key=>$item){
                        $csv_result[] = date($date_format, strtotime($idx_date));
                        $csv_result[] = $item->agent_id;
                        foreach($email_activity_list as $idx=>$email_item){
                            $idx_str = $idx_date.'_'.$item->agent_id.'_'.$idx;
                            $csv_result[] = isset($new_result[$idx_str]) ? $new_result[$idx_str] : 0;
                        }
                        fputcsv($f, $csv_result, $delimiter);
                        $csv_result = [];
                    }

                    $idx_date = date('Y-m-d', strtotime('+1 day', strtotime($idx_date)));
                    if($idx_date > $dateinfo->edate)
                        break;
                }

                fclose($f);
                die();
            }
        }

        $agent_list = array('*' => 'All') + $email_model->getEmailAgents();
        $data['request'] = $request;
        $data['pageTitle'] = 'Daily Email Activity Report';
        $data["agents"] = $agent_list;
        $data['skills'] = array('*' => 'All') + $skill_model->getSkillsFromType('E');
        $data['report_date_format'] = get_report_date_format();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['result_agent_list'] = $result_agent_list;
        $data['new_result'] = $new_result;
        $data['email_activity_list'] = get_report_email_activity_list();
        $data['sdate'] = $dateinfo->sdate;
        $data['edate'] = $dateinfo->edate;
        $data['date_format'] = $date_format;
        $data['report_restriction_days'] = $report_restriction_days;

        $this->getTemplate()->display_report('report_new_daily_email_activity', $data);
    }

    function actionEmailActivityReport()
    {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['skill_list'] = [];
        $data['agent_list'] = [];
        $data['status'] = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $data['agent_id'] = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';
        if (UserAuth::hasRole('supervisor')) {
            $data['agent_list'] = array("*" => "All") + $eTicket_model->getEmailAgents(UserAuth::getCurrentUser());
        } elseif (UserAuth::hasRole('admin')) {
            $data['agent_list'] = array("*" => "All") + $eTicket_model->getEmailAgents();
        }
        $data['report_date_format'] = get_report_date_format();
        $data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = "S";
        $data['status_options'] = array_merge(array('*' => 'Select'), $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-email-activity');
        $data['side_menu_index'] = 'ticketmng';
        $data['pageTitle'] = 'Email Activity Report';
        $this->getTemplate()->display_report('report_new_email_activity', $data);
    }

    public function actionDailyIvrHitReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MSkill.php');
        include('model/MEmail.php');
        include('model/MIvr.php');

        $ivr_model = new MIvr();
        $report_model = new MReportNew();
        $request = $this->getRequest();
        $date_format = get_report_date_format();
        $sdate = $request->getRequest('sdate');
        $download = $request->getRequest('download','');
        $ivr_id = $request->getRequest('ivr_id', '*');

        $current_date = !empty($sdate) ? generic_date_format($sdate, $date_format) : date('Y-m-d');
        $previous_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));

        $previous_date_result = $report_model->getDailyIvrHitData($previous_date, $ivr_id);
        $current_date_result = $report_model->getDailyIvrHitData($current_date, $ivr_id);
        if ($previous_date_result == null && $current_date_result == null) {
            $ivr_nodes = array();
        }else{
            $ivr_nodes = array_merge($report_model->getChildIvrNodes(), $report_model->getParentIvrNodes());
            /*$result_current = $this->getRankedNodeData($this->getAllNodeData($current_date_result, $ivr_nodes, $ivr_id));
            $result_previous = $this->getRankedNodeData($this->getAllNodeData($previous_date_result, $ivr_nodes, $ivr_id));*/
            $result_current = $this->getAllNodeData($current_date_result, $ivr_nodes, $ivr_id);
            $result_previous = $this->getAllNodeData($previous_date_result, $ivr_nodes, $ivr_id);

            $result_previous = $this->getMatchedData($result_previous, $result_current);
            $result_current = $this->getMatchedData($result_current, $result_previous);

            $result_previous = $this->getRankedNodeData($result_previous);
            $result_current = $this->getRankedNodeData($result_current);
//            $total_current_hit = $this->getTotalIvrHitCount($result_current);
            $parent_nodes = $report_model->getParentIvrNodes();
        }
        $total_current_hit = 0;
        $final_data = array();

        foreach ($ivr_nodes as $ivr_node) {
            $result_data = new stdClass();
            $result_data->service_title = $ivr_node->service_title;
            $parent_node = $this->getNodeFromDispositionCode($ivr_node->parent_id, $parent_nodes);
            $result_data->integration_info = $this->getIntegrationInfoOfNode($ivr_node);
            $result_data->parent_node = $parent_node->service_title;

            $has_current_data = false;
            foreach ($result_current as $result) {
                if ($ivr_node->disposition_code == $result->trace_id) {
                    $result_data->current_hit_count = $result->hit_count;
                    $result_data->current_rank = $result->rank;
                    $has_current_data = true;
                    break;
                }
            }
            $has_previous_data = false;
            foreach ($result_previous as $result) {
                if ($ivr_node->disposition_code == $result->trace_id) {
                    $result_data->previous_hit_count = $result->hit_count;
                    $result_data->previous_rank = $result->rank;
                    $has_previous_data = true;
                    break;
                }
            }
            $result_data->based_on_count = $result_data->current_hit_count - $result_data->previous_hit_count = $result->hit_count;
            if ($has_previous_data || $has_current_data) {
                array_push($final_data, $result_data);
            }
        }
        foreach ($final_data as $result) {
            $total_current_hit += $result->current_hit_count;
        }

        foreach ($final_data as $result) {
            if ($result->based_on_count == 0) {
                $result->comparison = 0 . "%";
            } else {
                if ($result->previous_hit_count == 0) {
                    $result->comparison = "NA";
                } else {
                    $result->comparison = round(($result->based_on_count / $result->previous_hit_count) * 100) . "%";
                }
            }
            if ($total_current_hit == 0) {
                $result->percentage_of_total_ivr_hit = "NA";
            } else {
                $result->percentage_of_total_ivr_hit = round(($result->current_hit_count / $total_current_hit) * 100) . "%";
            }
        }
        $current_date = date($date_format, strtotime($current_date));
        $previous_date = date($date_format, strtotime($previous_date));
        if ($download) {
            $delimiter = ',';
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="daily-ivr-hit-report' . '_' . date('Y-m-d_H-i-s') . '.csv"');
            $f = fopen('php://output', 'w');
            fputcsv($f, array("", "", "", "$current_date", "", "$previous_date", "", "", "", ""), $delimiter);
            fputcsv($f, array("IVR Nodes", "Integration info of Node", "Node No.", "Count", "Rank", "Count", "Rank", "Comparison (+ increase & - decrease)", "Based on Count: (+ increase & - decrease)", "% on Total IVR Info Hits"), $delimiter);

            $csv_result = [];
            foreach ($final_data as $data) {
                $csv_result[] = $data->service_title;
                $csv_result[] = $data->integration_info;
                $csv_result[] = $data->parent_node;
                $csv_result[] = $data->current_hit_count;
                $csv_result[] = $data->current_rank;
                $csv_result[] = $data->previous_hit_count;
                $csv_result[] = $data->previous_rank;
                $csv_result[] = $data->comparison;
                $csv_result[] = $data->based_on_count;
                $csv_result[] = $data->percentage_of_total_ivr_hit;
                fputcsv($f, $csv_result, $delimiter);
                $csv_result = [];
            }
            fclose($f);
            die();
        }

        $data['request'] = $request;
        $data['pageTitle'] = 'Daily IVR Hit Report';
        $data['report_date_format'] = get_report_date_format();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['results'] = $final_data;
        $data['ivr_nodes'] = $ivr_nodes;
        $data['sdate'] = $current_date;
        $data['previous_date'] = $previous_date;
        $data['date_format'] = $date_format;
        $data['ivr_list'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['ivr_id'] = $ivr_id;

        $this->getTemplate()->display_report('report_new_daily_ivr_hit', $data);
    }


    private function getMatchedData($matchForData, $matchWithData)
    {
        foreach ($matchWithData as $data) {
            $data_matched = false;
            foreach ($matchForData as $checkerData) {
                if ($data->trace_id == $checkerData->trace_id) {
                    $data_matched = true;
                    break;
                }
            }
            if (!$data_matched) {
                $notMatchedData = new stdClass();
                $notMatchedData->trace_id = $data->trace_id;
                $notMatchedData->service_title = $data->service_title;
                $notMatchedData->hit_count = 0;
                $matchForData [] = $notMatchedData;
            }
        }
        return $matchForData;
    }

    private function getAllNodeData($results, $ivr_nodes, $ivr_id)
    {
        $final_node_data = array();
        foreach ($ivr_nodes as $node) {
            $node_data = new stdClass();
            $has_data = false;
            if (isset($results)) {
                foreach ($results as $result) {
                    if ($result->trace_id == $node->disposition_code) {
                        $node_data->service_title = $node->service_title;
                        $node_data->trace_id = $node->disposition_code;
                        $node_data->hit_count = $result->hit_count;
                        $has_data = true;
                        break;
                    }
                }
            }
            if ($has_data == false) {
                $node_data->service_title = $node->service_title;
                $node_data->trace_id = $node->disposition_code;
                $node_data->hit_count = 0;
            }
            if ($ivr_id == "*") {
                array_push($final_node_data, $node_data);
            } elseif ($has_data) {
                array_push($final_node_data, $node_data);
            }
        }
        return $final_node_data;
    }

    private function getRankedNodeData($results)
    {
        $ranked_data = $results;
        usort($ranked_data, function ($a, $b) {
            return $b->hit_count - $a->hit_count;
        });
        $rank = 0;
        $previous_total = null;
        foreach ($ranked_data as $data) {
            if ($previous_total != $data->hit_count) {
                $rank++;
            }
            $data->rank = $rank;
            $previous_total = $data->hit_count;
        }

        return $ranked_data;
    }

    private function getTotalIvrHitCount($results)
    {
        $total = 0;
        foreach ($results as $result) {
            $total += $result->hit_count;
        }
        return $total;
    }


    public function actionAgentSessionDetails()
    {
        AddModel('MAgent');
        include('lib/DateHelper.php');

        $agent_model = new MAgent();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-session-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agents'] = ['*' => 'All'] + $agent_model->get_as_key_value();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Session Details';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-session-details');
        $this->getTemplate()->display_report('report_new_agent_session_details', $data);
    }

    public function actionDailyIvrHitSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MIvr.php');

        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-daily-ivr-hit-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Daily IVR Hit Summary Report';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivr_list'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-daily-ivr-hit-summary');
        $this->getTemplate()->display_report('report_new_daily_ivr_hit_summary', $data);

    }

    public function actionDailyIvrHitCountReport()
    {
        AddModel('MIvr');
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-daily-ivr-hit-count';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Daily IVR Hit Count Report';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivr_list'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-daily-ivr-hit-count');
        $this->getTemplate()->display_report('report_new_daily_ivr_hit_count', $data);

    }

    public function actionDayWiseIvrHitReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MIvr.php');

        $ivr_model = new MIvr();
        $report_model = new MReportNew();
        $request = $this->getRequest();
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

        $sdate = $request->getRequest('sdate');
        $edate = $request->getRequest('edate');
        $download = $request->getRequest('download', '');
        $ivr_id = $request->getRequest('ivr_id', '*');
        $error_msg = "";

        $date_from = !empty($sdate) ? generic_date_format($sdate, $date_format) : date('Y-m-d');
        $date_to = !empty($edate) ? generic_date_format($edate, $date_format) : date('Y-m-d', strtotime("+1day"));

        $dateInfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '','-1 second', $report_restriction_days);
        if(empty($dateInfo->errMsg)){
            $dates = $this->getTimeBreakDown($dateInfo,REPORT_DAILY);
            if (count($dates) <= 7) {
                $results = $report_model->getDayWiseIvrHitCountData($dateInfo, $ivr_id);
            }else{
                $results = [];
                $error_msg .= "** Please Give Date Range of One Week **";
            }

            $child_nodes = array_merge($report_model->getChildIvrNodes(), $report_model->getParentIvrNodes());
            $parent_nodes = $report_model->getParentIvrNodes();

            $table_headings = array();
            $table_headings[] = "IVR Nodes";
            $table_headings[] = "Integration Info of Node";
            $table_headings[] = "Node No";

            foreach ($dates as $date) {
                $table_headings[] = date("d-M", strtotime($date));
            }

            $final_results = array();

            if (count($results) > 0) {
                foreach ($child_nodes as $child_node) {
                    $final_data = new stdClass();
                    $final_data->ivr_node = $child_node->service_title;
                    $parent_node = $this->getNodeFromDispositionCode($child_node->parent_id, $parent_nodes);
                    $final_data->parent_node = $parent_node->service_title;
                    $final_data->integration_info = $this->getIntegrationInfoOfNode($child_node);

                    $has_result = false;
                    foreach ($dates as $date) {
                        $has_date_result = false;
                        foreach ($results as $result) {
                            if ($result->sdate == $date && $result->trace_id == $child_node->disposition_code) {
                                $final_data->{$date} = $result->hit_count;
                                $has_result = true;
                                $has_date_result = true;
                                break;
                            }
                        }
                        if ($has_date_result == false) {
                            $final_data->{$date} = 0;
                        }
                    }
                    if($ivr_id == "*"){
                        array_push($final_results, $final_data);
                    }
                    else if ($has_result) {
                        array_push($final_results, $final_data);
                    }
                }
            }
        }

        if ($download) {
            $request_param = [
                'sdate' => $sdate,
                'edate' => $edate,
                'ivr_id' => $ivr_id
            ];
            $report_model->saveReportAuditRequest('NRD::Day Wise IVR Hit', $request_param);

            $delimiter = ',';
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="day-wise-ivr-hit-report' . '_' . date('Y-m-d_H-i-s') . '.csv"');
            $f = fopen('php://output', 'w');

            fputcsv($f, array_merge(array("IVR Nodes", "Integration Info of Node", "Node No"), $dates), $delimiter);

            $csv_result = [];
            foreach ($final_results as $csv_data) {
                $csv_result[] = $csv_data->ivr_node;
                $csv_result[] = $csv_data->integration_info;
                $csv_result[] = $csv_data->parent_node;
                foreach ($dates as $date) {
                    $csv_result[] = $csv_data->{$date};
                }
                fputcsv($f, $csv_result, $delimiter);
                $csv_result = [];
            }
            fclose($f);
            die();
        }

        $data['request'] = $request;
        $data['results'] = $final_results;
        $data['table_headings'] = $table_headings;
        $data['dates'] = $dates;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Day Wise Ivr Hit Report';
        $data['input_err_msg'] = $error_msg;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivr_list'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['ivr_id'] = $ivr_id;

        $this->getTemplate()->display_report('report_new_day_wise_ivr_hit', $data);
    }

    private function getNodeFromDispositionCode($disposition_code, $nodes)
    {
        $node_name = null;
        foreach ($nodes as $node) {
            if ($node->disposition_code == $disposition_code) {
                return $node;
            }
        }
        return $node_name;
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

    function actionEmailAgentReport(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['skill_list'] = array();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_email-agent-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents();
        $skill_list = $eTicket_model->getEmailSkill();
        if (!empty($skill_list)){
            foreach ($skill_list as $key){
                $data['skill_list'][$key->skill_id] = $key->skill_name;
            }
        }
        $data['skill_list'] = array("*"=>"All")+$data['skill_list'];
        $data['in_kpi_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $data['rs_tr_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $agent_id = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';

        $isDateToday = true;
        $data['todaysDate'] = "";
        $data['pageTitle'] = 'Emails Agentwise Report';
        //$data['smi_selection'] = 'email_init';

        if (!empty($status)){
            $urlParam .= "&status=".$status;
            $isDateToday = false;
        }
        if (!empty($etype)){
            $urlParam .= "&type=".$etype;
            $isDateToday = false;
        }
        if (!empty($allNew)){
            $urlParam .= "&newall=".$allNew;
            $isDateToday = false;
        }
        if ($isDateToday){
            $data['todaysDate'] = date("Y-m-d")." 23:59";
        }

        $data['report_date_format'] = get_report_date_format();
        $data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = $status;
        $data['agent_id'] = $agent_id;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
        $selectOpt = array('*'=>'Select');
//        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionChildrenOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=email-agent-report'.$urlParam);
        $data['report_date_format'] = get_report_date_format();
        $this->getTemplate()->display_report('email_agent_report', $data);
    }

    function actionEmailDayWiseReport(){
        include('model/MReportNew.php');
        $reportnew_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_email-day-wise-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['pageTitle'] = 'Email Day Wise Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=email-day-wise-report');
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $this->getTemplate()->display_report('email_day_wise_report', $data);
    }

    function actionWebChatDayWiseReport(){
        include('model/MSkill.php');
        $skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-web-chat-day-wise';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['pageTitle'] = 'Web Chat Day Wise Report';
        $data['skills'] = $skill_model->getChatSkills();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['report_date_format'] = get_report_date_format();
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-web-chat-day-wise');

        $this->getTemplate()->display_report('report_new_web_chat_day_wise', $data);
    }

    function actionServicelog() {
        include('lib/DateHelper.php');
        include('model/MIvrService.php');
        AddModel('MIvr');
        $ivr_model = new MIvrService();
        $ivr = new MIvr();

        if (isset($_REQUEST['download'])){
            include('model/MIvrServiceReport.php');
            $report_model = new MIvrServiceReport();
            $data['report_model'] = $report_model;
        }
        $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
        $clid = isset($_REQUEST['clid']) ? trim($_REQUEST['clid']) : '';
        $alid = isset($_REQUEST['alid']) ? trim($_REQUEST['alid']) : '';
        $dateinfo = DateHelper::get_input_time_details(true);

        $data['dateinfo'] = $dateinfo;
        $data['dcode'] = $dcode;
        $data['clid'] = $clid;
        $data['alid'] = $alid;
        $data['ivrs'] = array_merge(['*'=>'All'], $ivr->getIvrOptions());
        $data['dp_options'] = $ivr_model->getServiceOptions('', true);
        $data['side_menu_index'] = UserAuth::hasRole('agent') ? '' : 'reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=servicelog');
        $data['pageTitle'] = 'IVR Service Request Log';
        $data['report_date_format'] = get_report_date_format();
        $this->getTemplate()->display_report('report_new_ivr_service_log', $data);
    }

    function actionQaOutboundReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();
        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa-outbound';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(OUTBOUND_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'QA Outbound Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-qa-outbound');
        $this->getTemplate()->display_report('report_new_qa_outbound', $data);
    }

    function actionEmailDetailsView() {
        $ticket_id = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        $sdate = !empty($_REQUEST['sdate']) ? $_REQUEST['sdate'] : "";
        $edate = !empty($_REQUEST['edate']) ? $_REQUEST['edate'] : "";
        $data['email_list'] = [];

        if (empty($ticket_id) || empty($sdate) || empty($edate) || ($sdate > $edate))
            return false;

        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['email_list'] = $eTicket_model->getSessionEmail($ticket_id, $sdate, $edate);
        $data['eTickets'] = $eTicket_model->getETicketById($ticket_id);
        $data['status_option_list'] = $eTicket_model->getTicketStatusOptions();

        $data['pageTitle'] = 'Email Deails';
        $this->getTemplate()->display_report('report_new_email_details_view', $data);
    }
    public function actionChatRatingsReport() {
        //include('model/MSkill.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();
        //$skill_model = new MSkill();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_chat-ratings-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['pageTitle'] = ' Chat Ratings Report';
        $data['agents'] =  $agent_model->getAgentNameKeyValue();
        //$data['skills'] = $skill_model->getChatSkills();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=chat-ratings-report');
        $this->getTemplate()->display_report('report_new_chat_ratings', $data);
    }

    function actionApiAccessSummaryReport(){
        include('lib/DateHelper.php');
        $dateinfo = DateHelper::get_input_time_details();
        $data['report_type_list'] = array('*'=>'---Select---') + array(
                REPORT_HOURLY => 'Hourly',
                REPORT_DAILY => 'Daily'
            );
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Api Access Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-api-access-summary');
        $this->getTemplate()->display('report_new_api_access_summary', $data);
    }

    function actionVivrSummary()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'VIVR Summary';
        $data['ivrs'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['sources'] = array('*' => 'All', "W" => "Web", "I" => "IVR");
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=vivr-summary');
        $this->getTemplate()->display_report('report_new_vivr_summary', $data);
    }

    function actionVivrDetails()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'VIVR Details';
        $data['ivrs'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['dids'] = ['*'  => 'All'] + get_did_list();
        $data['sources'] = array('*' => 'All', "W" => "Web", "I" => "IVR");
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=vivr-details');
        $this->getTemplate()->display_report('report_new_vivr_details', $data);
    }

    function actionVivrIceSummaryReport()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_vivr-ice-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $data['report_restriction_days'] = $report_restriction_days;
        $data['ivrs'] = ['*' => 'All'] + $ivr_model->getIvrOptions();
        $data['dids'] = ['*'  => 'All'] + get_did_list();
        $data['sources'] = array('*' => 'All', "W" => "Web", "I" => "IVR");
        $data['report_type_list'] = array('*' => '---Select---') + array(
                REPORT_DAILY => 'Daily',
                REPORT_MONTHLY => 'Monthly'
            );
        $data['report_type'] = REPORT_DAILY;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();

        $data['pageTitle'] = 'Vivr ICE Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-vivr-ice-summary');
        $this->getTemplate()->display_report('report_new_vivr_ice_summary', $data);
    }

    function actionApiAccessDetailsReport(){
        include('lib/DateHelper.php');
        $dateinfo = DateHelper::get_input_time_details();

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Api Access Details Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-api-access-details');
        $this->getTemplate()->display('report_new_api_access_details', $data);
    }

    function actionVivrServiceReport(){
        AddModel('MIvr');

        include('model/MReportNew.php');
        include('model/MVivr.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $vivr_model = new MVivr();
        $ivr_model = new MIvr();
        $request = $this->getRequest();

        $date_format = get_report_date_format();
        $ivr_id = !empty($request->getRequest('ivr_id')) ? $request->getRequest('ivr_id') : "*";
        $date_from = !empty($request->getRequest('sdate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d') : date('Y-m-d');
        $date_to = !empty($request->getRequest('edate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($request->getRequest('sdate')) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d H:i'))) : "00";
        $hour_to = !empty($request->getRequest('edate')) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d H:i'))) : "23";
        $download = $request->getRequest('download', '');

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');
        $date_list = $this->getDateList($date_from, $date_to);

        $results = $report_model->getVivrServiceReport($date_info, $ivr_id);
        $vivr_services = $vivr_model->getVivrServices($ivr_id);

        $vivr_services = array_column($vivr_services,null, 'page_id');
        $main_menu = $vivr_model->getVivrMainMenu($ivr_id);
        foreach ($vivr_services as $service) {
            foreach ($main_menu as $main_menu_data) {
                if ($service->main_menu_service == null && $service->ivr_id == $main_menu_data->ivr_id) {
                    $service->main_menu_service = $this->getMenuPageId($service, $vivr_services, $main_menu_data->page_id);
                    $service->main_menu_service = $vivr_services[$service->main_menu_service]->page_heading_en;
                }
            }
        }
        $final_result = array();
        if (!empty($results)) {
            foreach ($vivr_services as $vivr_service) {
                $final_result [] = $this->getFinalData($vivr_service, $results, $date_list);
            }
        }

        if ($download) {
            $delimiter = ',';
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="vivr-service-report' . '_' . date('Y-m-d_H-i-s') . '.csv"');
            $f = fopen('php://output', 'w');

            $date_heading = array();
            foreach ($date_list as $date) {
                $date_heading [] = report_date_format($date);
            }
            fputcsv($f, array_merge(array("Vivr Service","Service"), $date_heading), $delimiter);

            $csv_result = [];
            foreach ($final_result as $csv_data) {
                $csv_result[] = $csv_data->service_name;
                $csv_result[] = $csv_data->main_menu_service;
                foreach ($date_list as $date) {
                    $csv_result[] = $csv_data->{$date};
                }
                fputcsv($f, $csv_result, $delimiter);
                $csv_result = [];
            }
            fclose($f);
            die();
        }

        $data['ivrs'] = ['*' => 'All'] + $ivr_model->getIvrOptions();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Vivr Service Report';
        $data['request'] = $request;
        $data['results'] = $final_result;
        $data['date_list'] = $date_list;

        $this->getTemplate()->display_report('report_new_vivr_service', $data);
    }

    private function getDateList($date_from, $date_to){
        $date_list = array();
        $dateValue = $date_from;
        do {
            array_push($date_list, $dateValue);
            $dateValue = date('Y-m-d', strtotime("+1 day", strtotime($dateValue)));
        } while (strtotime($dateValue) <= strtotime($date_to));

        return $date_list;
    }

    private function getFinalData($vivr_service, $results, $date_list)
    {
        $data = new stdClass();
        $data->service_name = $vivr_service->page_heading_en;
        $data->main_menu_service = $vivr_service->main_menu_service;

        foreach ($date_list as $date) {
            foreach ($results as $result) {
                if ($result->sdate == $date && $result->service_title_id == $vivr_service->page_id) {
                    $data->{$date} = $result->total;
                    break;
                }
                $data->{$date} = 0;
            }
        }
        return $data;
    }

    private function getMenuPageId($page_data, $vivr_pages, $main_menu_id)
    {
        if ($page_data->parent_page_id == $main_menu_id)
            return $page_data->page_id;
        $menu_page_id = null;

        if ($page_data->parent_page_id == null)
            return $menu_page_id;

        $page_data = $vivr_pages[$page_data->parent_page_id];
        $menu_page_id = $this->getMenuPageId($page_data, $vivr_pages, $main_menu_id);
        return $menu_page_id;
    }

    function actionVivrServiceSummaryReport(){
        include('model/MReportNew.php');
        include('model/MVivr.php');
        include('lib/DateHelper.php');

        $report_model = new MReportNew();
        $vivr_model = new MVivr();
        $request = $this->getRequest();
        $report_types = array(
            REPORT_DAILY => "Daily",
            "Weekly" => "Weekly"
        );

        $date_format = get_report_date_format();
        $vivr_id = !empty($request->getRequest('ivr_id')) ? $request->getRequest('ivr_id') : "AB";
        $date_from = !empty($request->getRequest('sdate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d') : date('Y-m-d');
        $date_to = !empty($request->getRequest('edate')) ? date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d') : date('Y-m-d');
        $hour_from = !empty($request->getRequest('sdate')) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('sdate')), 'Y-m-d H:i'))) : "00";
        $hour_to = !empty($request->getRequest('edate')) ? date("H", strtotime(date_format(date_create_from_format($date_format . " H:i", $request->getRequest('edate')), 'Y-m-d H:i'))) : "23";
        $report_type = !empty($request->getRequest('report_type')) ? $request->getRequest('report_type') : REPORT_DAILY;
        $download = $request->getRequest('download', '');
        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');
        $date_list = $this->getDateList($date_from, $date_to);

        if ($report_type == "Weekly") {
            $date_list = array();
            $weeks = $report_model->getVivrWeekList($date_info);
            foreach ($weeks as $week){
                $date_list [] = $week->sdate;
            }
        }

        $vivr_pages = $vivr_model->getVivrPages($vivr_id);
        $menu_page = $vivr_model->getVivrMenuPage($vivr_id);
        $sub_menu_pages = $vivr_model->getChildNodesFromParentID($menu_page);

        foreach ($sub_menu_pages as $page){
            $page->child_pages = $this->getChild($page->page_id, $vivr_pages);
            $page->child_pages = "'" . implode("','", $page->child_pages) . "'";
            $result = $report_model->getVivrMenuServiceReport($date_info, $vivr_id, $page->child_pages, $report_type);

            if (isset($result)) {
                $page->data = $result;
            } else {
                $page->data = null;
            }
        }

        $vivr_services = $vivr_model->getVivrMenuServices($menu_page);

        $results = $sub_menu_pages;
        $final_result = array();
        $data_set = new stdClass();

        if (!empty($results)) {
            foreach ($vivr_services as $vivr_service) {
                $data_set->service_name =  $vivr_service->page_heading_en;
                foreach ($date_list as $date) {
                    foreach ($results as $result) {
                        if ($result->page_id == $vivr_service->page_id) {
                            $data_set->{$date} = 0;
                            foreach ($result->data as $result_data) {
                                if ($result_data->sdate == $date) {
                                    $data_set->{$date} += $result_data->total;
                                }
                            }
                        }
                    }
                }
                $final_result [] = $data_set;
                $data_set = new stdClass();
            }
        }

        if ($download) {
            $delimiter = ',';
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="vivr-service-summary-report' . '_' . date('Y-m-d_H-i-s') . '.csv"');
            $f = fopen('php://output', 'w');

            $date_heading = array();
            foreach ($date_list as $date) {
                if($report_type == REPORT_DAILY){
                    $date_heading [] = report_date_format($date);
                }else if ($report_type == "Weekly"){
                    $date_heading [] = $date;
                }
            }
            fputcsv($f, array_merge(array("Vivr Service"), $date_heading), $delimiter);

            $csv_result = [];
            foreach ($final_result as $csv_data) {
                $csv_result[] = $csv_data->service_name;
                foreach ($date_list as $date) {
                    $csv_result[] = $csv_data->{$date};
                }
                fputcsv($f, $csv_result, $delimiter);
                $csv_result = [];
            }
            fclose($f);
            die();
        }

        $data['ivrs'] = array(
            "AB" => "786 Prepaid",
            "AC" => "786 Postpaid"
        );

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Vivr Menu Service Report';
        $data['request'] = $request;
        $data['report_types'] = $report_types;
        $data['report_type'] = $report_type;
        $data['results'] = $final_result;
        $data['date_list'] = $date_list;


        $this->getTemplate()->display_report('report_new_vivr_service_summary', $data);
    }

    private function getChild($parent_page, $vivr_pages, $child_nodes = array())
    {
        $has_child = $this->checkHasChild($parent_page, $vivr_pages);
        if (!$has_child) {
            if(!in_array($parent_page,$child_nodes)){
                $child_nodes [] = $parent_page;
            }
            return $child_nodes;
        }

        foreach ($vivr_pages as $page) {
            if ($page->parent_page_id == $parent_page) {
                $child_nodes = $this->getChild($page->page_id, $vivr_pages, $child_nodes);
                if (!in_array($parent_page, $child_nodes)) {
                    $child_nodes [] = $parent_page;
                }
            }
        }
        return $child_nodes;
    }

    private function checkHasChild($page_id, $vivr_pages)
    {
        $has_child = false;
        foreach ($vivr_pages as $vivr_page) {
            if ($vivr_page->parent_page_id == $page_id) {
                $has_child = true;
                break;
            }
        }
        return $has_child;
    }

    function actionQaWebChatReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa-web-chat';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(WEBCHAT_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'QA Web Chat Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-qa-web-chat');
        $this->getTemplate()->display_report('report_new_qa_web_chat', $data);
    }

    function actionQaEmailReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa-email';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(EMAIL_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'QA Email Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-qa-email');
        $this->getTemplate()->display_report('report_new_qa_email', $data);
    }

    function actionIvrGlobalGroupReport()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa-email';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['ivrs'] = ['*'  => 'All'] + $ivr_model->getIvrOptions();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Ivr Global Group Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-ivr-global-group');
        $this->getTemplate()->display_report('report_new_ivr_global_group', $data);
    }

    function actionDiameterBillSummaryReport()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-diameter-bill-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Diameter Bill Summary Report';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-diameter-bill-summary');
        $this->getTemplate()->display_report('report_new_diameter_bill_summary', $data);
    }

    function actionDiameterBillDetailsReport()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-diameter-bill-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Diameter Bill Details Report';
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-diameter-bill-details');
        $this->getTemplate()->display_report('report_new_diameter_bill_details', $data);
    }

    function actionQaSmsReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-qa-sms';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(SMS_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'QA SMS Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-qa-sms');
        $this->getTemplate()->display_report('report_new_qa_sms', $data);
    }

    function actionPdSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MSkill.php');

        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $data['agent_list'] =    $agent_model->get_as_key_value();
        $data["skill_list"] = $skill_model->getSkillsTypeWithNameArray();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-pd-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $dateinfo = DateHelper::get_input_time_details();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
        $data['report_restriction_days'] = $report_restriction_days;
        $data["skill_type"] = 'P';
        $data['pageTitle'] = 'Pd Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-pd-summary');
        $this->getTemplate()->display_report('report_new_pd_summary', $data);
    }

    function actionSmsDetailsReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-sms-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Sms Details Report';

        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-sms-details');
        $this->getTemplate()->display_report('report_new_sms_details', $data);
    }

    function actionSmsSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');
        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        // $skill_options = $skill_model->getSkillsNamesArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-sms-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list; //array('*'=>'All') + $skill_options['V'];
        $data['report_type_list'] = array('*'=>'All') + report_type_list();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'S';
        $data['pageTitle'] = 'SMS Summary Report';
        $data['report_type'] = REPORT_DAILY;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-sms-summary');
        $this->getTemplate()->display_report('report_new_sms_summary', $data);
    }

    function actionAgentPerformanceObmSummary()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-performance-obm-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Performance (OBM) Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-performance-obm-summary');
        $this->getTemplate()->display_report('report_new_agent_performance_obm_summary', $data);
    }

    function actionAgentPerformanceSummary2()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-performance-summary2';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Performance Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-performance-summary2');
        $this->getTemplate()->display_report('report_new_agent_performance_summary2', $data);
    }

    function actionVivrIceDetailsReport()
    {
        AddModel('MIvr');
        $ivr_model = new MIvr();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_vivr-ice-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $data['ivrs'] = ['*' => 'All'] + $ivr_model->getIvrOptions();
        $data['dids'] = ['*'  => 'All'] + get_did_list();
        $data['sources'] = array('*' => 'All', "W" => "Web", "I" => "IVR");

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Vivr ICE Details Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-vivr-ice-details');
        $this->getTemplate()->display_report('report_new_vivr_ice_details', $data);
    }

    public function actionEmailAgentPerformanceReport(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_email-agent-performance-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Email Agent Performance Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=email-agent-performance-summary');
        $this->getTemplate()->display_report('report_new_email_agent_performance_summary', $data);
    }

    public function actionEvaluationSummaryReport(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        $agent_model = new MAgent();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_evaluation-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $agent_list = $agent_model->get_as_key_value();

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['service_list'] = evp_service_list();
        $data['evaluator_list'] = array("*" => "All") + $agent_list;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Evaluation Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=evaluation-summary');
        $this->getTemplate()->display_report('report_new_evaluation_summary', $data);
    }

    /*
     * Customer Journey Activity Report
     */
    function actionCjAgentActivityReport(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');
        include ('model/MSkill.php');
        $skill_model = new MSkill();
        $agent_model = new MAgent();

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_customer-journey-agent-activity-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $agent_list = $agent_model->get_as_key_value();
        $data['agent_list'] = array("*" => "All") + $agent_list;
        $data['skills'] = array_merge(array("*"=>"All"),$skill_model->getSkillsNamesArray());
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Customer Journey Agent Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=customer-journey-agent-activity-summary');
        $this->getTemplate()->display_report('report_new_customer_journey_agent_activity_summary', $data);
    }

    function actionCjReport(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_customer-journey-report';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['type_list'] = array("*" => "All") + customer_journey_module;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Customer Journey Details Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=customer-journey-report');
        $this->getTemplate()->display_report('report_new_customer_journey_report', $data);
    }

    function actionPdQaReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_model = new MReportNew();

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-pd-qa';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['form_list'] = $report_model->getEvaluationFormFields(PD_EVALUATION_TYPE);
        $data['comparison_types'] = get_comparison_types();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'PD Reports';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-pd-qa');
        $this->getTemplate()->display_report('report_new_pd_qa', $data);
    }

    /*
     * Agent Webchat Performance.
     */
    function actionWcAgentPerformanceSummary(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-webchat-performance-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent Webchat Performance Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-webchat-performance-summary');
        $this->getTemplate()->display_report('report_new_agent_webchat_performance_summary', $data);
    }

    /*
     * Agent SMS Performance.
     */
    function actionSmsAgentPerformanceSummary(){
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_agent-sms-performance-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Agent SMS Performance Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=agent-sms-performance-summary');
        $this->getTemplate()->display_report('report_new_agent_sms_performance_summary', $data);
    }

    function actionAutoSmsReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-auto-sms';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Auto SMS Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-auto-sms');
        $this->getTemplate()->display_report('report_new_auto_sms', $data);
    }

    function actionUnattendedCdrSummaryReport()
    {
        include('model/MSkill.php');
        $skillModel = new MSkill();

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-unattended-cdr-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $skillOptions = $skillModel->getSkillsTypeWithNameArray();
        $data['skill_list'] = $skillOptions;
        $data['skill_type'] = 'V';
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Unattended Cdr Summary';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=unattended-cdr-summary');
        $view = 'unattended-cdr/report_new_unattended_cdr_summary';
        $this->getTemplate()->display_report($view, $data);
    }

    function actionUnattendedCdrDetailsReport()
    {
        include('model/MSkill.php');
        include('model/MAgent.php');
        include('model/MUnattended.php');
        $skillModel = new MSkill();
        $agentModel = new MAgent();

        $agentOptions = $agentModel->get_as_key_value();
        $dispositions = $this->getDispositionList();
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-unattended-cdr-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $skillOptions = $skillModel->getSkillsTypeWithNameArray();
        $data['agent_list'] = $agentOptions;
        $data['skill_list'] = $skillOptions;
        $data['disposition_list'] = array(''=>'All') + $dispositions;
        $data['skill_type'] = 'V';
        $data['call_status'] = array(''=>'All') + $this->getCallStatus();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Unattended Cdr Details';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=unattended-cdr-details');
        $view = 'unattended-cdr/report_new_unattended_cdr_details';
        $this->getTemplate()->display_report($view, $data);
    }

    private function getDispositionList()
    {
        $unattendedCdrModel = new MUnattended();
        $dispositionList = $unattendedCdrModel->getRemovingDispositions(UNATTENDED_CDR_DISPOSITION_TEMPLATE);
        $dispositions = array();
        foreach ($dispositionList as $key => $value) {
            $dispositions[$value->disposition_id] = $value->title;
        }
        return $dispositions;
    }

    private function getCallStatus()
    {
        $callStatus = array(
            'A' => 'Abandoned',
            'C' => 'Callback Request',
            'I' => 'In Progress'
        );
        return $callStatus;
    }

    function actionBtrcSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include('model/MSkillCategory.php');
        include('model/MReportNew.php');
        $skill_model = new MSkill();
        $skill_category_model = new MSkillCategory();
        $report = new MReportNew();

        // $skill_options = $skill_model->getSkillsNamesArray();
        $dateinfo = DateHelper::get_input_time_details();
        $skill_list = $skill_model->getSkillsTypeWithNameArray();
        $skill_type_list = get_report_skill_type_list();
        $skill_type_list = array_slice($skill_type_list, 0, 2);

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-btrc-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if(isset($report_config_list[$db_role_id])){
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }

        $data['skill_list'] = $skill_list; //array('*'=>'All') + $skill_options['V'];
        $data['report_type_list'] = array('*'=>'All') + report_type_list();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['skill_type_list'] = $skill_type_list;
        $data['skill_type'] = 'V';
        $data['pageTitle'] = 'BTRC Summary Report';
        $data['report_type'] = REPORT_DAILY;
        $data['report_restriction_days'] = $report_restriction_days;
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-btrc-summary');
        $this->getTemplate()->display_report('report_new_btrc_summary', $data);
    }

    function actionEvaluatorSummaryReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-evaluator-summary';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $agent_list = $agent_model->get_as_key_value();
        $data['agent_list'] = array("*" => "All") + $agent_list;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Evaluator Summary Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-evaluator-summary');
        $this->getTemplate()->display_report('report_new_evaluator_summary', $data);
    }

    function actionChatCoBrowseDetailsReport()
    {
        include('lib/DateHelper.php');
        include('model/MReportNew.php');
        include('model/MAgent.php');

        $agent_model = new MAgent();

        $report_config_list = get_report_config_list();
        $controller_idx = 'get-report-new-data_report-chat-co-browse-details';
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
        }
        $agent_list = $agent_model->get_as_key_value();
        $data['agent_list'] = array("*" => "All") + $agent_list;
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['report_restriction_days'] = $report_restriction_days;
        $data['pageTitle'] = 'Web Chat Co Browse Details Report';
        $data['dataUrl'] = $this->url('task=get-report-new-data&act=report-chat-co-browse-details');
        $this->getTemplate()->display_report('report_new_webchat_co_browse_details', $data);
    }

}
