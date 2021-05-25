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
        include_once('conf.email.php');
        $db = new stdClass();
        include_once($script_dir . 'conf.php');
        
        $dbprefix = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
        $service = isset($_REQUEST['service']) ? trim($_REQUEST['service']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $client_name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $upd_log = isset($_POST['upd_log']) ? trim($_POST['upd_log']) : '';
        if (strlen($dbprefix) != 2) $dbprefix = 'AA';
        
        include_once($script_dir . 'lib/DBManager.php');
        include_once($script_dir . 'lib/UserAuth.php');
        UserAuth::setDBSuffix($dbprefix);
        $conn = new DBManager($db);

        if ($upd_log == '1') {
                $cid = isset($_POST['cid']) ? trim($_POST['cid']) : "";
                $logid = isset($_POST['logid']) ? trim($_POST['logid']) : "";
                if (!empty($cid)) {
                        $cid_ary = explode("-", $cid);
                        $cid = $cid_ary[0];
                        if (ctype_digit($cid) && ctype_digit($logid)) {
                                $sql = "UPDATE chat_detail_log SET callid='$cid' WHERE callid='$logid' LIMIT 1";
                                $conn->query($sql);
                        }
                }
                $response = new stdClass();
                $response->status = 1;
                echo $_GET['callback']."(".json_encode($response). ")";
                exit;
        }
        
        $isValidReq = false;
        if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z]{2}$/i', $service)) {
                $isValidReq = true;
        }
        if (!$isValidReq) {
                exit;
        }

        $response = new stdClass();
        $response->available = 0;   
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
        
	if (!empty($skill_id)) {
        	$sql = "select count(agent_id) AS numrows FROM seat where agent_id in (select agent_id FROM agent_skill AS a, skill AS s where s.skill_id='$skill_id' AND qtype='C' AND a.skill_id=s.skill_id)";
        	$result = $conn->query($sql);

	        if (is_array($result)) {
         	       $response->available = $result[0]->numrows > 0 ? '1' : '0';
        	}
        	
        	if (!empty($client_name) && preg_match('/^[A-Za-z][A-Za-z0-9\.\-\,]{1,35}$/', $client_name)) {
                	$email = isset($_POST['email']) ? trim($_POST['email']) : '';
			$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
			$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
			$service_id = isset($_POST['service_id']) ? trim($_POST['service_id']) : '';
			if (strlen($service_id) != 2) $service_id = '';
			$url = isset($_POST['url']) ? trim($_POST['url']) : '';
			$duration = isset($_POST['duration']) ? trim($_POST['duration']) : '';
			if (!ctype_digit($duration)) $duration = 0;

			$temp_callid = time() . rand(100000, 999999);

                        $sql = "INSERT INTO chat_detail_log SET ".
                                "callid='".$temp_callid."', ".
                                "tstamp=UNIX_TIMESTAMP(), ".
                                "client_name='$client_name', ".
                                "email='".$conn->escapeString($email)."', ".
                                "contact_number='".$conn->escapeString($contact)."', ".
                                "service_id='".$conn->escapeString($service_id)."', ".
                                "url='".$conn->escapeString($url)."', ".
                                "url_duration='".$user_arival_duration."'";			
			$conn->query($sql);
			$response->logid = $temp_callid;
        	}
	}
        
        echo $_GET['callback']."(".json_encode($response). ")";
        exit;
        
