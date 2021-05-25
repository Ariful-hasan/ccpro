<?php
        error_reporting(7);
        set_time_limit(0);
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
        //define('APPPATH', '/usr/local/apache2/htdocs/ccpro/');
        define('APPPATH', getcwd() . '/');

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
        $site_key = isset($_REQUEST['site_key']) ? trim($_REQUEST['site_key']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        if (strlen($dbprefix) != 2) $dbprefix = 'AA';

        include_once($script_dir . 'lib/DBManager.php');
        include_once($script_dir . 'lib/UserAuth.php');
        UserAuth::setDBSuffix($dbprefix);
        
	$isValidReq = false;
        if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z_\-0-9]{5,32}$/i', $site_key)) {
		$isValidReq = true;
        }
	if (!$isValidReq) {
		exit;
	}
        $conn = new DBManager($db);

        $response = new stdClass();
        $response->available = 0;
        $sql = "SELECT service_id, service_name FROM chat_service WHERE page_id='$page_id'";
        $result = $conn->query($sql);
        $service = "";
        foreach($result as $row) {
                $service_id = $row->service_id;
                $service_name = $row->service_name;
                $service .= $service_id."=".$service_name."|";
        }
        $service = trim($service,"|");
        $response->service = $service;

        $sql = "SELECT skill_id FROM chat_page WHERE page_id='$page_id' AND site_key='$site_key' AND active='Y'";
        $result = $conn->query($sql);
        if (!is_array($result)) {
                echo $_GET['callback']."(".json_encode($response).")";
                exit;
        }

        $num_agents = 0;
        $ws_port = 0;

        $sql = "SELECT l.lang_key, l.lang_title FROM language l, chat_page c WHERE l.lang_key=c.language AND c.page_id='$page_id' AND l.status='A'";
        $result = $conn->query($sql);
        $language = "";
        foreach($result as $row) {
                $lang_key = $row->lang_key;
                $lang_title = $row->lang_title;
                $language .= $lang_key."=".$lang_title."|";
        }
        $language = trim($language,"|");
        $response->language = $language;


        if ($db->cctype > 0) {
                $sql = "SELECT ws_port, switch_ip FROM settings LIMIT 1";
                $result = $conn->query($sql);
                if (is_array($result)) {
                        $response->ws_port = $result[0]->ws_port;
                        $response->ws_ip = $result[0]->switch_ip;
                }
        } else {
                $sql = "SELECT ws_port, active_sip_srv, sip_srv_primary, sip_srv_backup FROM cc_master.account WHERE db_suffix='$dbprefix' LIMIT 1";
                $result = $conn->query($sql);
                if (is_array($result)) {
                        $response->ws_port = $result[0]->ws_port;
                        $response->ws_ip = $result[0]->active_sip_srv == 'P' ? $result[0]->sip_srv_primary : $result[0]->sip_srv_backup;
                }
        }

        if (!empty($response->ws_ip) && !is_public_ip($response->ws_ip)) {
                list($http_host, $http_port) = explode(":", $_SERVER['HTTP_HOST']);
                $http_host_ip = gethostbyname($http_host);
                if (is_public_ip($http_host_ip)) {
                        $response->ws_ip = $http_host_ip;
                }
        }

        $sql = "select count(agent_id) AS numrows FROM seat where agent_id in (select agent_id FROM agent_skill AS a, skill AS s where qtype='C' AND a.skill_id=s.skill_id)";
        $result = $conn->query($sql);

        if (is_array($result)) {
                $response->available = $result[0]->numrows > 0 ? '1' : '0';
        }

        include(APPPATH. "ipdb/geoipcity.inc");
        $gi = geoip_open(APPPATH. "ipdb/GeoLiteCity.dat", GEOIP_STANDARD);
        $ip = $_SERVER['REMOTE_ADDR'];

        //var_dump($ip);
        //var_dump($gi);
        $ipinfo = geoip_record_by_addr($gi, $ip);
        
        //var_dump($num_agents);
        $isAgentAvailable = 1;//$num_agents > 0 ? '1' : '0';
        header("Content-type: text/javascript");
        //echo "alert(\"$isAgentAvailable, $ipinfo->country_name, $ipinfo->region, $ipinfo->city, $ipinfo->postal_code\");";
        //echo "GetVisitorInfo(\"$isAgentAvailable\", \"$ipinfo->country_name\", \"$ipinfo->region\", \"$ipinfo->city\", \"$ipinfo->postal_code\", \"$ws_port\", \"$language\");";
        $response->country = empty($ipinfo->country_name) ? '' : $ipinfo->country_name;
        $response->region = empty($ipinfo->region) ? '' : $ipinfo->region;
        $response->city = empty($ipinfo->city) ? '' : $ipinfo->city;
        $response->postal_code = empty($ipinfo->postal_code) ? '' : $ipinfo->postal_code;
        //$response->message = serialize($_SERVER);
                //echo json_encode($response);
        echo $_GET['callback']."(".json_encode($response). ")";
        exit;

        function is_public_ip ($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE);
        }
        