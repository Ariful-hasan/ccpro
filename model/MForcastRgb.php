<?php

class MForcastRgb extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function getForcastRgbData($date_info = '', $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $sql = "SELECT * FROM forcast_rgb ";
        $sql .= " WHERE sdate BETWEEN '{$date_info->sdate}' AND '{$date_info->edate}' ";

        $sql .= " ORDER BY sdate ";
        if ($limit > 0) $sql .= " LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function numForcastRgbData($date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $sql = "SELECT COUNT(*) AS total_record FROM forcast_rgb ";
        $sql .= "WHERE sdate BETWEEN '{$date_info->sdate}' AND '{$date_info->edate}' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    function storeForcastData($date, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "INSERT INTO forcast_rgb VALUES ";
        $sql .= "('$date', '$skill_id', '$skill_name','$forcast_value', '', '$agent', '$service_type', '$increase_fc_data', '$decrease_fc_data' )";

        return $this->getDB()->query($sql);
    }

    function storeRgbData($date, $skill_id, $skill_name, $rgb_value)
    {
        $sql = "INSERT INTO forcast_rgb VALUES ";

        $sql .= "('$date', '$skill_id', '$skill_name','','','', '$rgb_value' )";

        return $this->getDB()->query($sql);
    }

    function updateForcastData($date, $skill_id, $skill_name, $forcast_value, $agent, $service_type, $increase_fc_data=0, $decrease_fc_data=0)
    {
        $sql = "UPDATE forcast_rgb SET ";
        $sql .= " forcast_value = '$forcast_value', agent ='$agent', service_type ='$service_type', fc_upper_scale_value ='$increase_fc_data', fc_lower_scale_value ='$decrease_fc_data' ";
        $sql .= " WHERE  sdate = '$date' AND skill_id ='$skill_id' AND skill_name ='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    function updateRgbData($date, $skill_id, $skill_name, $rgb_value)
    {
        $sql = "UPDATE forcast_rgb SET ";
        $sql .= " rgb = '$rgb_value' ";
        $sql .= " WHERE  sdate = '$date' AND skill_id ='$skill_id' AND skill_name ='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    function hasDuplicate($date, $skill_id, $skill_name)
    {
        $sql = "SELECT COUNT(*) AS total_record FROM forcast_rgb ";
        $sql .= " WHERE  sdate = '$date' AND skill_id ='$skill_id' AND skill_name ='$skill_name' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    function getReportForecastRgbData($dateinfo){
        $data = $this->getForcastRgbData($dateinfo);
        $new_data = [];
        foreach ($data as $key => $item) {
            $idate = date('d/m/Y', strtotime($item->sdate));
            $new_data[$idate.'_'.$item->skill_id]['forecast'] = $item->forcast_value;
            $new_data[$idate.'_'.$item->skill_id]['rgb'] = $item->rgb;
        }
        return $new_data;
    }
}

?>