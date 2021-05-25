<?php
include_once('lib/DBManager.php');
include_once('conf.php');
$conn = new DBManager($db);
$limit = 5;
$offset = 0;

$_from_date = "2019-05-02 00:00";
$_to_date = "2019-05-02 23:59";
//$agents = array( '2200', '2201', '2202', '2207', '2208', '2204', '2500', '2501', '2502','2504', '2505', '2506', '2507', '2509', '2511', '2512','2514', '2515', '2516', '', '', '', '','');
//$agents = array( '2201', '2202', '2207', '2208', '2204',  '2502','2504', '2505', '2506', '2507', '2511', '2514');////// 1-10April'19
//$agents = array('2201', '2202', '2204',  '2502','2504', '2505', '2506', '2507', '2511');////// 11-20April'19
//$agents = array('2202', '2502');////// 21-25April'19
$agents = array('2202');
$total_query = [];
session_unset();
for ($i=0; $i < count($agents); $i++){
    $result = get_email_activity_count($conn, $_from_date, $_to_date, $agents[$i]);
    //var_dump($result);
    if (!empty($result)){
        foreach ($result as $key){
            $log_count = get_log_email_session_count($conn, $key->ticket_id, $_from_date, $_to_date, $agents[$i]);
            if ($key->served != $log_count[0]->total) {
                $activitys = get_activity_by_id($conn, $key->ticket_id, $_from_date, $_to_date, $agents[$i]);
                //var_dump($key->ticket_id);
                if (!empty($activitys)){
                    foreach ($activitys as $item){
                        if (!is_log_email_session_exists($conn, $item->ticket_id, $item->agent_id, $item->activity_time)){
                             set_email_msg($conn, $item->ticket_id, $item->agent_id, $item->activity_time);
                        }
                    }
                }
            }
        }
    }
}
function insert_log($conn, $email_id, $create_time, $close_time, $agent_id, $did, $customer_email_count){

    $e_into = get_e_ticket_info($conn, $email_id);
    $skill_id = "";
    if (!empty($e_into)) {
        //$did = $e_into->disposition_id;
        $skill_id = $e_into->skill_id;
    }

    $session_id = generate_sessionid($conn);
    $open_duration = $close_time - $create_time;
    $IN_KPI = $open_duration <= "86400" ? "Y" : "N";
    $sql = "INSERT INTO log_email_session SET  ticket_id='$email_id',session_id='$session_id',";
    $sql .= " create_time='$create_time', first_open_time='$create_time', waiting_duration='0', ";
    $sql .= " skill_id='$skill_id', agent_1='$agent_id', close_time='$close_time', ";
    $sql .= " open_duration='$open_duration', in_kpi='$IN_KPI', status='S', last_update_time='$close_time',";
    $sql .= " status_updated_by='$agent_id', disposition_id='$did', customer_email_count='$customer_email_count'";
    //$conn->query($sql);
    echo "<br> $sql <br>";
}
function generate_sessionid($conn, $email_id=null, $sid=null) {
    $sql = "SELECT MAX(session_id) FROM log_email_session WHERE session_id LIKE 'A%'";
    $result = $conn->queryOnUpdateDB($sql);
    $default = empty($result[0]->session_id) ? "AAAAAAAAAAAAA" : $result[0]->session_id;
    for ($i=0;$i<50;$i++){
        $default++;
        $sql = "SELECT session_id FROM log_email_session WHERE session_id='$default'";
        if (!$conn->queryOnUpdateDB($sql)){
            return $default;
        }
    }
}
function get_email_activity_count($conn, $sdate, $edate, $agent_id){
    $sql = "SELECT eta.*, SUM(IF(eta.activity_details = 'S', 1, 0)) AS served";
    $sql .= " FROM e_ticket_activity as eta";
    $sql .= " WHERE eta.activity_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate')";
    $sql .= " AND eta.agent_id IN ('$agent_id') AND eta.activity='S' AND eta.activity_details='S'  ";
    $sql .= " GROUP BY eta.ticket_id ORDER BY eta.ticket_id DESC;";
    return $conn->queryOnUpdateDB($sql);
}
function get_log_email_session_count($conn, $email_id="", $sdate, $edate, $agent_id){
    $sql = "SELECT count(*) AS total FROM log_email_session WHERE ";
    $sql .= !empty($email_id) ? " ticket_id='$email_id' AND " : "";
    $sql .= " close_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate') AND `status`='S' AND (agent_1='$agent_id' OR agent_2='$agent_id') ";
    return $conn->queryOnUpdateDB($sql);
}
function get_activity_by_id($conn, $email_id, $sdate, $edate, $agent_id){
    $sql = "SELECT * FROM e_ticket_activity WHERE ticket_id='$email_id' AND agent_id='$agent_id' AND activity='S' AND activity_details='S'";
    $sql .= " AND activity_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate')";
    return $conn->queryOnUpdateDB($sql);
}
function is_log_email_session_exists($conn, $email_id, $agent_id, $close_time){
    $sql = "SELECT count(*) AS numrows FROM log_email_session WHERE ticket_id='$email_id' AND (agent_1='$agent_id' OR agent_2='$agent_id') AND close_time='$close_time' AND `status`='S' ";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
}
function set_email_msg($conn, $email_id, $agent_id, $close_time){
    $select = " ticket_id, agent_id, tstamp, email_status, email_did, acd_status ";
    $sql = "SELECT $select FROM email_messages WHERE ticket_id='$email_id' AND tstamp='$close_time' AND agent_id='$agent_id' AND email_status='S'";
    //echo "<br> $sql <br>";
    $result = $conn->queryOnUpdateDB($sql);
    if (!empty($result)){
        return insert_log($conn, $email_id, $close_time, $close_time, $agent_id, $result[0]->email_did, "1");
    } else {
        $e_into = get_e_ticket_info($conn, $email_id);
        if (!empty($e_into) && $e_into->status_updated_by == $agent_id && $e_into->status=='S'){
            return insert_log($conn, $email_id, $close_time, $close_time, $agent_id, $e_into->disposition_id, "0");
        }
    }
    return null;
}
/*function set_email_msg($conn, $email_id, $agent_id, $close_time){
    $select = " ticket_id, agent_id, tstamp, email_status, email_did, acd_status ";
    echo $sql = "SELECT $select FROM email_messages WHERE ticket_id='$email_id' AND tstamp='$close_time' AND agent_id='$agent_id' AND email_status='S'";
    $result = $conn->query($sql);
    if (!empty($result)){
        return insert_log($conn, $email_id, $close_time, $close_time, $agent_id, $result[0]->email_did, "1");
        $details = get_create_details($conn, $email_id, $close_time);
        if (!empty($details[0])){
            insert_log($conn, $email_id, $details[0]->tstamp, $close_time, $agent_id, "", "");
        }
    } else {
        insert_log($conn, $email_id, $close_time, $close_time, $agent_id, $result[0]->email_did, "N");
    }
    return null;
}*/

function get_customer_email_count($conn, $email_id, $create_time, $close_time){
    $sql = "SELECT COUNT(*) AS numrows FROM email_messages WHERE ticket_id='$email_id' AND tstamp BETWEEN '$create_time' AND '$close_time'";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
}
function get_e_ticket_info($conn, $email_id){
    $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$email_id'";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result) ? $result[0] : null;
}
function is_sessionid_in_middle_position($conn, $email_id){
    $sql = "SELECT COUNT(*) AS numrows FROM log_email_session WHERE ticket_id='$email_id' AND (`status`!='E' AND `status` !='S')";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
}
function get_create_details($conn, $email_id, $close_time){
    $sql = "SELECT * FROM email_messages WHERE ticket_id = '$email_id' AND tstamp > ";
    $sql .= " (SELECT tstamp FROM email_messages WHERE ticket_id = '$email_id' AND tstamp < '$close_time' AND email_status = 'S' ORDER BY tstamp DESC LIMIT 1) ";
    $sql .= " AND tstamp < '$close_time' ORDER BY tstamp ASC LIMIT 1";
    return $conn->queryOnUpdateDB($sql);
}

