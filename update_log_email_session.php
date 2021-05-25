<?php
error_reporting(E_ALL);
include_once('lib/DBManager.php');
include_once('conf.php');
$conn = new DBManager($db);
$limit = 5;
$offset = 0;
$GLOBALS['conn'] = $conn;

$_date = "2019-08-01";
$_till_date = "2019-08-01";
$GLOBALS['succ_count'] = 0;
for (;;){
    if ($_date <= $_till_date){
        $_from_tstamp = strtotime($_date." 00:00:00");
        $_to_tstamp = strtotime($_date." 23:59:59");
        $result = get_log_email_data($_from_tstamp, $_to_tstamp);
        process_data($result);
    } else {
        break;
    }
    $_date = date("Y-m-d", strtotime("+1 day", strtotime($_date)));
}

function get_log_email_data($from, $to){
    $sql = "SELECT ticket_id,session_id,create_time,first_open_time,close_time,in_kpi  FROM log_email_session WHERE close_time BETWEEN '$from' AND '$to'";
    return $GLOBALS['conn']->queryOnUpdateDB($sql);
}
function process_data($result){
    if (!empty($result)){
        //var_dump($result);die;
        foreach ($result as $key){
            $open_duration = !empty($key->close_time) && ($key->close_time >= $key->first_open_time) ? $key->close_time - $key->first_open_time : 0;
            $in_kpi='';
//            if ( (1800 >= ($key->close_time - $key->create_time)) && $key->in_kpi == "N"){
//                $in_kpi = 'Y';
//            }
//            if ( (1800 < ($key->close_time - $key->create_time)) && $key->in_kpi == "Y"){
//                $in_kpi = 'N';
//            }
            if (update_open_duration($open_duration, $key->ticket_id, $key->session_id, $in_kpi)){
                $GLOBALS['succ_count']++;
            }
        }
    }
    return null;
}
function update_open_duration($duration, $email_id, $session_id, $in_kpi=""){
    $sql = "UPDATE log_email_session SET open_duration='$duration' ";
    //$sql .= !empty($in_kpi) ? ", in_kpi='$in_kpi' " : "";
    echo $sql .= " WHERE ticket_id='$email_id' AND session_id='$session_id' LIMIT 1";
    return $GLOBALS['conn']->query($sql);
}
echo "Successfully Update: ";echo $GLOBALS['succ_count'];