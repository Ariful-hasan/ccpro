<?php

class MHisData extends Model
{
	public function __construct() {
		parent::__construct();
	}

	public function numHisData($dateinfo='', $service_type = '', $skill_id='')
	{
		$cond = [];
		if(!empty($dateinfo)){
        	$from = $dateinfo->sdate;
        	$to = $dateinfo->edate;
    	}

        $sql = "SELECT COUNT(sdate) AS total_record FROM fc_historical_data ";

        if(!empty($from) && !empty($to))
        	$cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($service_type) && $service_type != '*')
        	$cond[] = "service_type='{$service_type}'";
        if(!empty($skill_id) && $skill_id != '*')
        	$cond[] = "skill_id='{$skill_id}'";

        if (!empty($cond)) 
        	$sql .= "WHERE ".implode(" AND ", $cond);    
        // echo $sql;

        $response = $this->getDB()->query($sql);

        return !empty($response) ? $response[0]->total_record : 0;
	}

	public function getHisData($dateinfo='', $service_type = '', $skill_id='', $offset=0, $limit = 20)
	{
		$cond = [];
		if(!empty($dateinfo)){
        	$from = $dateinfo->sdate;
        	$to = $dateinfo->edate;
    	}

        $sql = "SELECT * FROM fc_historical_data ";

        if(!empty($from) && !empty($to))
        	$cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($service_type) && $service_type != '*')
        	$cond[] = "service_type='{$service_type}'";
        if(!empty($skill_id) && $skill_id != '*')
        	$cond[] = "skill_id='{$skill_id}'";

        if (!empty($cond)) 
        	$sql .= "WHERE ".implode(" AND ", $cond);  

        $sql .= " ORDER BY sdate ASC ";
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
	}

	public function isDuplicate($date, $skill_id, $skill_name){
        if ($this->hasDuplicate($date, $skill_id, $skill_name)!=0) {
            return true;
        }
        return false;
    }

    public function hasDuplicate($date, $skill_id, $skill_name){
        $sql = "SELECT COUNT(sdate) AS total_record FROM fc_historical_data ";
        $sql .= " WHERE sdate='$date' AND skill_id='$skill_id' AND skill_name='$skill_name' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    public function updateHisData($date, $skill_id, $skill_name, $value){
        $sql = "UPDATE fc_historical_data SET ";
        $sql .= " count='$value' ";
        $sql .= " WHERE  sdate='$date' AND skill_id ='$skill_id' AND skill_name='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }
    public function storeHisData($date, $skill_id, $skill_name, $value, $service_type){
        $sql = "INSERT INTO fc_historical_data VALUES ";

        $sql .= "('$date', '$skill_id', '$skill_name' ,'$value', '$service_type' )";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    public function hisDataInCsv($skill_id, $post_data){
        $cond = [];
        $groupby = '';
        $sql="SELECT sdate, skill_id";
        $count_select = ", count ";

        if(!empty($post_data->service_type)){
            $cond[]="service_type='".$post_data->service_type."'";
        }
        // if(!empty($post_data->skill_name[$skill_id])){
        //     $cond[]="skill_name='".$post_data->skill_name[$skill_id]."'";
        // }

        if(empty($post_data->gplex_fc_group_ids[$skill_id])){
            $cond[] = "skill_id='".$skill_id."'";
        }elseif(!empty($post_data->gplex_fc_group_ids[$skill_id])){
            $skill_ids = explode(',', $post_data->gplex_fc_group_ids[$skill_id]);
            $cond[] = "skill_id IN('".implode("','", $skill_ids)."')";
            $count_select = ", SUM(count) as count ";
            $groupby = "GROUP BY sdate ";
        }

        $sql .= $count_select;
        $sql .= "FROM fc_historical_data ";
        $sql .= "WHERE ".implode(" AND ", $cond)." ";
        $sql .= $groupby;
        $sql .= "ORDER BY sdate ASC ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);

    }

    public function hourlyHisDataInCsv($skill_id, $post_data){
        $cond = [];
        $groupby = '';
        $sql="SELECT sdate, shour, skill_id";
        $count_select = ", count ";

        if(!empty($post_data->service_type)){
            $cond[]="service_type='".$post_data->service_type."'";
        }
        // if(!empty($post_data->skill_name[$skill_id])){
        //     $cond[]="skill_name='".$post_data->skill_name[$skill_id]."'";
        // }

        if(empty($post_data->gplex_fc_group_ids[$skill_id])){
            $cond[] = "skill_id='".$skill_id."'";
        }elseif(!empty($post_data->gplex_fc_group_ids[$skill_id])){
            $skill_ids = explode(',', $post_data->gplex_fc_group_ids[$skill_id]);
            $cond[] = "skill_id IN('".implode("','", $skill_ids)."')";
            $count_select = ", SUM(count) as count ";
            $groupby = "GROUP BY sdate, shour ";
        }

        $sql .= $count_select;
        $sql .= "FROM fc_historical_data_hourly ";
        $sql .= "WHERE ".implode(" AND ", $cond)." ";
        $sql .= $groupby;
        $sql .= "ORDER BY sdate, shour ASC ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);

    }

    public function getLastHisDate($service_type){
        $sql = "SELECT sdate FROM fc_historical_data WHERE service_type='$service_type' ORDER BY sdate DESC LIMIT 1 ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function getLastHisDateHourly($service_type){
        $sql = "SELECT sdate FROM fc_historical_data_hourly WHERE service_type='$service_type' ORDER BY sdate DESC LIMIT 1 ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function isDuplicateHourly($date, $shour, $skill_id, $skill_name){
        if ($this->hasDuplicateHourly($date, $shour, $skill_id, $skill_name)!=0) {
            return true;
        }
        return false;
    }

    public function hasDuplicateHourly($date, $shour, $skill_id, $skill_name){
        $sql = "SELECT COUNT(sdate) AS total_record FROM fc_historical_data_hourly ";
        $sql .= " WHERE sdate='$date' AND shour='$shour' AND skill_id='$skill_id' AND skill_name='$skill_name' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    public function updateHisDataHourly($date, $shour, $skill_id, $skill_name, $value){
        $sql = "UPDATE fc_historical_data_hourly SET ";
        $sql .= " count='$value' ";
        $sql .= " WHERE  sdate='$date' AND shour='$shour' AND skill_id ='$skill_id' AND skill_name='$skill_name' ";
        $sql .= " LIMIT 1 ";

        return $this->getDB()->query($sql);
    }
    public function storeHisDataHourly($date, $shour, $skill_id, $skill_name, $value, $service_type){
        $sql = "INSERT INTO fc_historical_data_hourly VALUES ";

        $sql .= "('$date', '$shour', '$skill_id', '$skill_name' ,'$value', '$service_type' )";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    public function numHisDataHourly($dateinfo='', $service_type = '', $skill_id='')
    {
        $cond = [];
        if(!empty($dateinfo)){
            $from = $dateinfo->sdate;
            $to = $dateinfo->edate;
        }

        $sql = "SELECT COUNT(sdate) AS total_record FROM fc_historical_data_hourly ";

        if(!empty($from) && !empty($to))
            $cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($service_type) && $service_type != '*')
            $cond[] = "service_type='{$service_type}'";
        if(!empty($skill_id) && $skill_id != '*')
            $cond[] = "skill_id='{$skill_id}'";

        if (!empty($cond)) 
            $sql .= "WHERE ".implode(" AND ", $cond);    
        // echo $sql;

        $response = $this->getDB()->query($sql);

        return !empty($response) ? $response[0]->total_record : 0;
    }

    public function getHisDataHourly($dateinfo='', $service_type = '', $skill_id='', $offset=0, $limit = 20)
    {
        $cond = [];
        if(!empty($dateinfo)){
            $from = $dateinfo->sdate;
            $to = $dateinfo->edate;
        }

        $sql = "SELECT * FROM fc_historical_data_hourly ";

        if(!empty($from) && !empty($to))
            $cond[] = "sdate BETWEEN '{$from}' AND '{$to}'";
        if(!empty($service_type) && $service_type != '*')
            $cond[] = "service_type='{$service_type}'";
        if(!empty($skill_id) && $skill_id != '*')
            $cond[] = "skill_id='{$skill_id}'";

        if (!empty($cond)) 
            $sql .= "WHERE ".implode(" AND ", $cond);  

        $sql .= " ORDER BY sdate, shour ASC ";
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }
}

?>