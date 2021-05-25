<?php

class MAverageHandlingTime extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getBySkills($skills)
    {
        $date = date("Y-m-d");
        $sql = "SELECT COALESCE((SUM(ring_time) + SUM(service_duration) + SUM(wrap_up_time)) / SUM(calls_answerd), 0) AS aht, ";
        $sql .= "COALESCE(SUM(answerd_within_service_level),0) AS answerd_within_service_level, COALESCE(SUM(calls_offered),0) AS calls_offered, ";
        $sql .= "COALESCE(SUM(calls_answerd), 0) AS calls_answerd, COALESCE(SUM(abandoned_after_threshold),0) AS abandoned_after_threshold ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate = '{$date}' AND skill_id IN('". implode("','", $skills)."')";

        $result = $this->getDB()->query($sql);

        return !empty($result) ? array_shift($result) : null;
    }

    public function getFor777Category()
    {
        $date = date("Y-m-d");
        $sql = "SELECT COALESCE((SUM(ring_time) + SUM(service_duration) + SUM(wrap_up_time)) / SUM(calls_answerd), 0) AS aht, ";
        $sql .= "COALESCE(SUM(answerd_within_service_level),0) AS answerd_within_service_level, COALESCE(SUM(calls_offered),0) AS calls_offered, ";
        $sql .= "COALESCE(SUM(calls_answerd), 0) AS calls_answerd, COALESCE(SUM(abandoned_after_threshold),0) AS abandoned_after_threshold ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate = '{$date}' AND skill_id IN('". implode("','", CATEGORY_777_SKILLS)."')";

        $result = $this->getDB()->query($sql);

        return !empty($result) ? array_shift($result) : null;
    }


    public function getFor786Category()
    {
        $date = date("Y-m-d");
        $sql = "SELECT COALESCE((SUM(ring_time) + SUM(service_duration) + SUM(wrap_up_time)) / SUM(calls_answerd), 0) AS aht, ";
        $sql .= " COALESCE(SUM(answerd_within_service_level),0) AS answerd_within_service_level, COALESCE(SUM(calls_offered),0) AS calls_offered, ";
        $sql .= " COALESCE(SUM(calls_answerd), 0) AS calls_answerd, COALESCE(SUM(abandoned_after_threshold),0) AS abandoned_after_threshold ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate = '{$date}' AND skill_id IN('". implode("','", CATEGORY_786_SKILLS)."')";

        $result = $this->getDB()->query($sql);

        return !empty($result) ? array_shift($result) : null;
    }
}
