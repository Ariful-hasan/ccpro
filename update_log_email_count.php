<?php
//error_reporting(1);

include_once('config/constant.php');
include_once('conf.php');
include_once('lib/DBManager.php');
$conn = new DBManager($db);


$sql = "SELECT *, from_unixtime(create_time, '%Y-%m-%d    %h:%i:%s') AS Datetime FROM log_email_session WHERE create_time BETWEEN '1541030400' AND '1543622399' LIMIT 0,1";
$result = $conn->query($sql);

//var_dump($result);
//exit;

$_i = 0;
if (!empty($result) && is_array($result)){
    foreach ($result as $key){
        $_i++;

//        echo $_i . "\n";

        $sql = "SELECT count(*) AS total FROM email_messages WHERE ticket_id='".$key->ticket_id."' AND agent_id ='' AND tstamp BETWEEN '".$key->create_time."' AND '".$key->close_time."' ";
        $count_data = $conn->query($sql);
        //var_dump($count_data[0]->total);
        if (!empty($count_data[0]->total)){
            $sql = "UPDATE log_email_session SET customer_email_count='".$count_data[0]->total."' WHERE ticket_id='".$key->ticket_id."' AND session_id='".$key->session_id."' LIMIT 1";
            if ($conn->query($sql)) {
                $sql = "UPDATE email_messages SET session_id='".$key->session_id."' WHERE ticket_id='".$key->ticket_id."' AND tstamp BETWEEN '".$key->create_time."' AND '".$key->close_time."'";
                $conn->query($sql);
            }
        }
    }
}

