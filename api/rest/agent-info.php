<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

require_once '../../conf.php';
require_once '../../lib/DBManager.php';
require_once '../../lib/Security.php';

$encryption = new Security();

$_conn = new DBManager($db);

$secured_message = !empty($_POST['auth']) ? $_POST['auth'] : "";

log_message($secured_message);

$agent_id = $secured_message ? $encryption->decrypt($secured_message) : "";



if ($agent_id && strlen($agent_id) == 4){
    $formated_response = new stdClass();
    $formated_response->skills = [];
    $formated_response->language = [];
    $count = 1;

    $query  = "SELECT ag.agent_id,sk.skill_name,ag.language_1, ag.language_2,ag.language_3, ag.nick, ag.name, su.agent_id AS supervisor_id, ";
    $query .= " su.nick AS supervisor_name, (SELECT screen_logger_quality FROM settings) AS screen_logger_quality FROM agents ag inner join agent_skill aq ON ag.agent_id = aq.agent_id inner join skill sk ";
    $query .= " ON aq.skill_id = sk.skill_id LEFT JOIN agents AS su ON  ag.supervisor_id = su.agent_id WHERE ag.agent_id = '{$agent_id}'";

    $response = $_conn->query($query);

    if (empty($response)){
     die();
    }


    foreach ($response as $row){
        $formated_response->agent_id = $row->agent_id;
        $formated_response->name = $row->name;
        $formated_response->nick = $row->nick;
        $formated_response->supervisor_id = $row->supervisor_id;
        $formated_response->supervisor_name = $row->supervisor_name;
        $formated_response->screen_logger_quality = $row->screen_logger_quality;
        $formated_response->skills[] = $row->skill_name;
        if ($count == 1){
            if (!empty($row->language_1)){
                array_push($formated_response->language, $row->language_1);
            }if (!empty($row->language_2)){
                array_push($formated_response->language, $row->language_2);
            }if (!empty($row->language_3)){
                array_push($formated_response->language, $row->language_3);
            }
        }

        $count++;
    }


    die($encryption->encrypt(json_encode($formated_response)));
}



function log_message($content)
{
    $log_fl = './log.txt';
    if (is_writable($log_fl)){
         file_put_contents($log_fl, date("d-M-Y H:i:s") . ": " . $content . "\n", FILE_APPEND);
    }

}