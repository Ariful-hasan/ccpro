<?php
error_reporting(7);
set_time_limit(0);

define('APPPATH', '/usr/local/apache2/htdocs/cloudcc/');

$debug = false;
$debug_new_line = '<br />';
        //$is_live = true;
        //$script_dir = $is_live ? '' : '';
        //$script_dir = '';
$script_dir = '';
include_once('request_cc_service.php');
        //include_once('conf.email.php');
        //$db = new stdClass();
        //include_once($script_dir . 'conf.php');

$dbprefix = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
$service = isset($_REQUEST['service']) ? trim($_REQUEST['service']) : '';
$page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
if (strlen($dbprefix) != 2) $dbprefix = 'AA';

        //include_once($script_dir . 'lib/DBManager.php');
        //include_once($script_dir . 'lib/UserAuth.php');
        //UserAuth::setDBSuffix($dbprefix);
        //$conn = new DBManager($db);

$isValidReq = false;
if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z]{2}$/i', $service)) {
    $isValidReq = true;
}
if (!$isValidReq) {
    exit;
}

$response = new stdClass();
$response->available = 0;   
$skill_id = "";
$result = do_cc_request("user=$dbprefix&method=getServiceSkillId&srv=$service&page_id=$page_id");
if (substr($result, 0, 3) == "200") {
    $skill_id = substr($result, 4);
}
    /*
        $sql = "SELECT skill_id FROM chat_service WHERE service_id='$service' LIMIT 1";
        $result = $conn->query($sql);
        $skill_id = "";
        if (is_array($result)) {
                $skill_id = $result[0]->skill_id;
        }
        
        if (empty($skill_id)) {
                $sql = "SELECT skill_id FROM chat_page WHERE page_id='$page_id' AND active='Y'";
                $result = $conn->query($sql);
                if (is_array($result)) {
                    $skill_id = $result[0]->skill_id;
                }
        }
        */
        
        if (!empty($skill_id)) {
            $result = do_cc_request("user=$dbprefix&method=getNumChatAgents&skill_id=$skill_id");
            if (substr($result, 0, 3) == "200") {
                $response->available = substr($result, 4);
            }
        /*
            $sql = "select count(agent_id) AS numrows FROM seat where agent_id in (select agent_id FROM agent_skill AS a, skill AS s where s.skill_id='$skill_id' AND qtype='C' AND a.skill_id=s.skill_id)";
            $result = $conn->query($sql);

            if (is_array($result)) {
                   $response->available = $result[0]->numrows > 0 ? '1' : '0';
            }
        */
        }
        
        echo $_GET['callback']."(".json_encode($response). ")";
        exit;
        
