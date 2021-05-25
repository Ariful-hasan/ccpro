<?php
include_once('lib/DBManager.php');
include_once('conf.php');
$conn = new DBManager($db);
$limit = 5;
$offset = 0;

$_from_date = "2019-04-21 00:00";
$_to_date = "2019-04-24 23:59";
$agents = array('2502');
$test = 0;
for ($i=0; $i<count($agents); $i++) {
    $activity = total_email_activity($conn, $agents[$i], $_from_date, $_to_date);
    $session_log = total_email_sessoion($conn, $agents[$i], $_from_date, $_to_date);
    echo "<br> Activity count : ";var_dump(count($activity));echo "<br>";
    echo "<br> Session count : ";var_dump(count($session_log));echo "<br>";
    if (!empty($activity) && (count($activity) > count($session_log))) {
        foreach ($activity as $actv){
            if (!is_session_exist($conn, $actv->agent_id, $actv->ticket_id, $actv->activity_time)){
                remove_activity($conn, $actv);
            }
        }
    }
    elseif (!empty($session_log) && (count($session_log) > count($activity))){
        foreach ($session_log as $key){
            $log_count = get_log_email_count_by_id($conn, $agents[$i], $key->ticket_id, $_from_date, $_to_date);
            $activity_count = get_activity_count_by_id($conn, $agents[$i], $key->ticket_id, $_from_date, $_to_date);
            if ($log_count != $activity_count) {
                if (!is_activity_exist($conn, $key, $agents[$i])){
                    add_activity($conn, $key, $agents[$i]);
                }
            }
        }
    }
}
function add_activity($conn, $obj, $agent_id){
    $sql = "INSERT INTO e_ticket_activity SET ticket_id='".$obj->ticket_id."', agent_id='$agent_id', activity='S', activity_details='S', activity_time='".$obj->close_time."'";
    echo "<br> $sql <br>";
    $conn->query($sql);
}
function remove_activity($conn, $obj) {
    $sql = "UPDATE e_ticket_activity SET activity='W' WHERE ticket_id='".$obj->ticket_id."' AND agent_id='".$obj->agent_id."' AND activity='S' AND activity_details='S' AND activity_time='".$obj->activity_time."'";
    echo "<br> $sql <br>";
    $conn->query($sql);
}
function get_activity_count_by_id($conn, $agent_id, $email_id, $sdate, $edate){
    $sql = "SELECT COUNT(*) AS numrows FROM e_ticket_activity WHERE ticket_id='$email_id' AND agent_id='$agent_id' AND activity='S' AND activity_details='S' ";
    $sql .= " AND activity_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate') ";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
}
function get_log_email_count_by_id($conn, $agent_id, $email_id, $sdate, $edate){
    $sql = "SELECT COUNT(*) AS numrows FROM log_email_session WHERE ticket_id='$email_id' AND (agent_1='$agent_id' OR agent_2='$agent_id')  ";
    $sql .= " AND close_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate') ";
    $result = $conn->queryOnUpdateDB($sql);
    return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
}

function is_activity_exist($conn, $obj, $agent_id){
    $sql = "SELECT * FROM e_ticket_activity WHERE ticket_id='".$obj->ticket_id."' AND agent_id='$agent_id' AND activity='S' AND activity_details='S' AND activity_time='".$obj->close_time."'";
    $result = $conn->queryOnUpdateDB($sql);
    if (!empty($result)) return true;
    return false;
}

function is_session_exist($conn, $agent_id, $email_id, $tstamp){
    $sql = "SELECT * FROM log_email_session WHERE ticket_id = '$email_id' AND (agent_1='$agent_id' OR agent_2='$agent_id') AND close_time='$tstamp' AND `status`='S'";
    $result = $conn->queryOnUpdateDB($sql);
    if (!empty($result)) return true;
    return false;
}

function is_email_exists($conn, $agent_id, $email_id, $tstamp){
    $sql = "SELECT ticket_id, tstamp, email_status, email_did FROM email_messages WHERE ticket_id='$email_id' AND tstamp='$tstamp' AND agent_id='$agent_id' AND email_status='S'";
    $result = $conn->queryOnUpdateDB($sql);
    if (!empty($result)) return true;
    return false;
}
function total_email_activity($conn, $agent_id, $sdate, $edate){
   $sql = "SELECT * FROM e_ticket_activity WHERE agent_id='$agent_id' AND activity = 'S' AND activity_details='S' AND activity_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate')";
    return $conn->queryOnUpdateDB($sql);
}
function total_email_sessoion($conn, $agent_id, $sdate, $edate){
    $sql = "SELECT * FROM log_email_session WHERE (agent_1='$agent_id' OR agent_2='$agent_id') ";
    $sql .= " AND close_time BETWEEN UNIX_TIMESTAMP('$sdate') AND UNIX_TIMESTAMP('$edate') AND `status`='S' ";
    return $conn->queryOnUpdateDB($sql);
}