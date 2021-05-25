<?php

class MForcastData extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function getForcastData($date_info = '', $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $sql = "SELECT * FROM fc_forecast_daily ";
        $sql .= " WHERE sdate BETWEEN '{$date_info->sdate}' AND '{$date_info->edate}' ";

        $sql .= " ORDER BY sdate ";
        if ($limit > 0) $sql .= " LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function numForcastData($date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $sql = "SELECT COUNT(*) AS total_record FROM fc_forecast_daily ";
        $sql .= "WHERE sdate BETWEEN '{$date_info->sdate}' AND '{$date_info->edate}' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    function storeForcastData($date, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "INSERT INTO fc_forecast_daily VALUES ";
        $sql .= "('$date', '$skill_id', '$skill_name','$forcast_value', '$increase_fc_data', '$decrease_fc_data', '$agent', '$service_type' )";

        return $this->getDB()->query($sql);
    }

    function updateForcastData($date, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "UPDATE fc_forecast_daily SET ";
        $sql .= " forcast_value = '$forcast_value', agent ='$agent', service_type ='$service_type', fc_upper_scale_value ='$increase_fc_data', fc_lower_scale_value ='$decrease_fc_data' ";
        $sql .= " WHERE  sdate = '$date' AND skill_id ='$skill_id' AND skill_name ='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    function hasDuplicate($date, $skill_id, $skill_name)
    {
        $sql = "SELECT COUNT(*) AS total_record FROM fc_forecast_daily ";
        $sql .= " WHERE  sdate = '$date' AND skill_id ='$skill_id' AND skill_name ='$skill_name' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    function getReportForecastData($dateinfo){
        $data = $this->getForcastData($dateinfo);
        $new_data = [];
        foreach ($data as $key => $item) {
            $idate = date('d/m/Y', strtotime($item->sdate));
            $new_data[$idate.'_'.$item->skill_id]['forecast'] = $item->forcast_value;
            $new_data[$idate.'_'.$item->skill_id]['rgb'] = $item->rgb;
        }
        return $new_data;
    }

    public function getMinDate($service_type='V'){
        $sql = "SELECT MIN(sdate) as min_date FROM fc_forecast_daily WHERE service_type='".$service_type."' ";
        $result = $this->getDB()->query($sql);
        return !empty($result) ? $result[0]->min_date : '';
    }

    public function getMinDateHourly($service_type='V'){
        $sql = "SELECT MIN(sdate) as min_date FROM fc_forecast_hourly WHERE service_type='".$service_type."' ";
        $result = $this->getDB()->query($sql);
        return !empty($result) ? $result[0]->min_date : '';
    }

    public function getForcastDataForChart($form_data, $skill_id){
        $cond = [];
        if(!empty($form_data)){
            $from = $form_data->sdate;
            $to = $form_data->edate;
        }
        $sql = "SELECT * FROM fc_forecast_daily ";
        if($form_data->type=='H')
            $sql = "SELECT * FROM fc_forecast_hourly ";

        if(!empty($from) && !empty($to))
            $cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($form_data->service_type) && $form_data->service_type != '*')
            $cond[] = "service_type='{$form_data->service_type}'";
        if(!empty($skill_id) && $skill_id != '*')
            $cond[] = "skill_id='{$skill_id}'";

        if (!empty($cond)) 
            $sql .= "WHERE ".implode(" AND ", $cond);

        if($post_data->type=='H')
            $sql .= " ORDER BY sdate, shour ASC ";
        else
            $sql .= " ORDER BY sdate ASC ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function getOriginalDataForChart($post_data, $skill_ids){
        $cond = [];
        if(!empty($post_data)){
            $from = $post_data->sdate;
            $to = $post_data->edate;
        }

        if($post_data->type=='H')
            $sql = "SELECT sdate, shour, SUM(calls_offered) as calls_offered FROM rt_skill_call_summary_tmp ";
        else
            $sql = "SELECT sdate, SUM(calls_offered) as calls_offered FROM rt_skill_call_summary_tmp ";

        if(!empty($from) && !empty($to))
            $cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($skill_ids) && $skill_ids != '*')
            $cond[] = "skill_id IN({$skill_ids})";

        if (!empty($cond)) 
            $sql .= "WHERE ".implode(" AND ", $cond);

        if($post_data->type=='H')
            $sql .= " GROUP BY sdate, shour ";
        else
            $sql .= " GROUP BY sdate ";

        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function hasDuplicateHourly($date, $hour, $skill_id, $skill_name)
    {
        $sql = "SELECT COUNT(*) AS total_record FROM fc_forecast_hourly ";
        $sql .= " WHERE  sdate='$date' AND shour='$hour' AND skill_id='$skill_id' AND skill_name='$skill_name' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }
    public function updateForcastDataHourly($date, $hour, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "UPDATE fc_forecast_hourly SET ";
        $sql .= " forcast_value = '$forcast_value', agent ='$agent', service_type ='$service_type', fc_upper_scale_value ='$increase_fc_data', fc_lower_scale_value ='$decrease_fc_data' ";
        $sql .= " WHERE  sdate='$date' AND shour='$hour' AND skill_id='$skill_id' AND skill_name='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }
    public function storeForcastDataHourly($date, $hour, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "INSERT INTO fc_forecast_hourly VALUES ";
        $sql .= "('$date', '$hour', '$skill_id', '$skill_name','$forcast_value', '$agent', '$service_type', '$increase_fc_data', '$decrease_fc_data' )";

        return $this->getDB()->query($sql);
    }
}

?>