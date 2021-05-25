<?php

include_once('config/constant.php');
include_once('conf.php');
include_once('lib/DBManager.php');
$conn = new DBManager($db);


$sql = "SELECT * FROM log_email_session WHERE  close_time > 0 limit 1";
$result = $conn->query($sql);
//var_dump($result);
if (!empty($result) && is_array($result)){
    foreach ($result as $key){
        if (!empty($key->close_time)){
            $IN_KPI = $key->close_time - $key->create_time <= 86400 ? "Y" : "N";
            $sql = "UPDATE log_email_session SET in_kpi='$IN_KPI' WHERE ticket_id='".$key->ticket_id."' AND session_id='".$key->session_id."' LIMIT 1";
            $res = $conn->query($sql);
            var_dump($res);
            echo '<br/>';
        }
    }
}
?>