<?php
$db = new stdClass();
$str = explode('/',$_SERVER['SCRIPT_FILENAME']);
array_pop($str);
$base_path = implode('/',$str);

include_once($base_path . '/conf.php');
include_once($base_path . '/lib/UserAuth.php');
include_once($base_path . '/lib/DBManager.php');
include_once($base_path . '/conf.email.php');
$conn = new DBManager($db);


$msg_res['method'] = !empty($_REQUEST['method']) ? $_REQUEST['method'] : "";
$msg_res['email_id'] = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : "";
$msg_res['call_id'] = !empty($_REQUEST['call_id']) ? $_REQUEST['call_id'] : "";
$agentid = !empty($_REQUEST['agent_id']) ? $_REQUEST['agent_id'] : "";
$skip = !empty($_REQUEST['skip']) ? $_REQUEST['skip'] : "";
$msg = json_encode($msg_res);

$len = strlen($msg);
if (!empty($msg_res['call_id'])) {
    $msg_srv_ip = getDBIPSettings();
    $msg_srv_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
    socket_close($sock);
    if ($skip)updateSkipStatusOfEticket($agentid, $msg_res['email_id']);
}
updateETicketInfo($agentid, $msg_res['email_id']);

//$_msg = '{"method": "AG_EMAIL_CLOSE","email_id": "1533377046", "call_id":"1234567890123456"}';
//send_data($_msg);
////// This is done in
/*function send_data($msg){
    $msg_srv_ip = getDBIPSettings();
    var_dump($msg_srv_ip);die;
    $msg_srv_port = '5196';
    $len = strlen($msg);
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
    socket_close($sock);
}*/
function getDBIPSettings() {
    $conn = db_con();
    $sql = "SELECT db_ip FROM settings";
    $rsult = $conn->query($sql);
    return !empty($rsult[0]->db_ip) ? $rsult[0]->db_ip : "";
}

function updateSkipStatusOfEticket($agent, $ticket_id) {
    $conn = db_con();
    $sql = "UPDATE e_ticket_info SET acd_agent='$agent', acd_status='Z', typing='', `current_user`='' WHERE ticket_id='$ticket_id'";
    return $conn->query($sql);
}
function updateETicketInfo($agent_id, $ticket_id) {
    $now = time();
    if (!empty($agent_id) && !empty($ticket_id)){
        $conn = db_con();
        $sql = "SELECT assigned_to,agent_id FROM e_ticket_info WHERE ticket_id='$ticket_id'";
        $result = $conn->query($sql);
        if (empty($result[0]->agent_id) || (!empty($result[0]->agent_id) && $result[0]->agent_id==$agent_id)){
            $sql = "UPDATE e_ticket_info SET last_update_time='$now', agent_id='$agent_id', typing='Y', `current_user`='$agent_id' WHERE ticket_id='$ticket_id'";
        }else {
            $sql = "UPDATE e_ticket_info SET last_update_time='$now', agent_id_2='$agent_id', typing='Y', `current_user`='$agent_id' WHERE ticket_id='$ticket_id'";
        }
        return $conn->query($sql);
    }
}

function db_con(){
    global $conn;
    return $conn;
}

