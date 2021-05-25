<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include('conf.php');
include_once('request_cc_service.php');
include_once('log_file.php');

define('APPPATH', dirname(__FILE__).'/');
	
if(isset($_GET["chat_rating"])) {
    $dbSuffix = isset($_GET["user"]) ? trim($_GET["user"]) : "";
	/*
	$script_dir = '';
    $db = new stdClass();
    include_once($script_dir . 'conf.php');
    include_once($script_dir . 'lib/DBManager.php');
    include_once($script_dir . 'lib/UserAuth.php');
    UserAuth::setDBSuffix($dbSuffix);
    $conn = new DBManager($db);
	*/

    $chat_rating = $_GET["chat_rating"]!='' ? trim($_GET["chat_rating"]) : "";
    $call_id = isset($_GET["call_id"]) ? trim($_GET["call_id"]) : "";
    $pos = strpos($call_id,"-");
    $call_id = substr($call_id,0,$pos);
    $logData = [
        'Request Data: '.json_encode($_GET)
    ];
    log_text($logData, "rating_log/");

    if (ctype_digit($call_id) && ctype_digit($chat_rating) && strlen($dbSuffix) == 2) {
    	//$sql = "UPDATE cdrin_log SET agent_rating=$chat_rating WHERE callid='$call_id' LIMIT 1";
    	//$result = $conn->query($sql);
		$resp = do_cc_request("user=$dbSuffix&method=rateChat&rate=$chat_rating&callId=$call_id");
	}
    echo json_encode(array("chat_rating"=>$chat_rating));
    exit;
}

$logData = [
	'Request Data: '.json_encode($_REQUEST)
	//'Server Data: '.json_encode($_SERVER)
];
log_text($logData, "chat_req_log/");

/**
 * set default value for chat
 */
//if($is_rnd==1) $url = "http://72.48.199.126/cloudcc";
//else $url = "http://ccportal.gplex.com";
$url =  ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ?  "https" : "http");
$url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$url = substr(rtrim($url, 'chat.php'), 0, -1);
$asset_url = $url.'/ccd/';
$js_source = 'src';
$css_source = 'src';

$layout= $domain = $page_id = $site_key = $www_ip = $user = $web_site_url = '';
$is_rnd = $user_arival_duration = 0;
$token = '';
if (isset($_GET["site_key"])) {
    $layout = $_GET["layout"];
    $domain = $_GET["domain"];
    $page_id = $_GET["page_id"];
    $site_key = $_GET["site_key"];
    $www_ip = $_GET["www_ip"];
    $user = strtoupper($_GET["user"]);
    $is_rnd = $_GET["is_rnd"];
    $language = isset($qr["language"]) ? $qr["language"] : "BN";
	$token = isset($_GET["token"]) && strlen($_GET["token"]) == 32 ? $_GET["token"] : "";
} else {
    $query_string = base64_decode($_SERVER['QUERY_STRING']);
	$api_token = "";
    
    if(count($_REQUEST) == 2){
        foreach($_REQUEST as $key => $item){
            if($key == 'api_token'){
                $api_token = $item;
            }elseif(strlen($key) > 10){
                $qs_val = explode('&api_token', $_SERVER['QUERY_STRING']);
                $query_string = isset($qs_val[0]) ? base64_decode($qs_val[0]) : '';
            }
        }
    }
	
    parse_str($query_string, $qr);

    if (!empty($qr['site_key'])) {
        //var_dump($qr);
        $layout = $qr["layout"];
        $domain = $qr["domain"];
        $page_id = $qr["page_id"];
        $site_key = $qr["site_key"];
        $www_ip = $qr["www_ip"];
        $user = strtoupper($qr["user"]);
        $is_rnd = isset($qr["is_rnd"]) ? $qr["is_rnd"] : 0;
        $web_site_url = isset($qr["url"]) ? $qr["url"] : "";
        $language = isset($qr["language"]) ? $qr["language"] : "BN";
        $user_arival_duration = isset($qr["user_arival_duration"]) ? $qr["user_arival_duration"] : 0;
		$token = isset($qr["token"]) && strlen($qr["token"]) == 32 ? $qr["token"] : "";
		$subject = isset($qr["subject"]) ? trim($qr["subject"]) : "General";
    }
}

$is_rnd = 0;
if (!preg_match('/^[a-z0-9_\.\/&=\?]{0,255}$/i', $web_site_url)) {
	$web_site_url = "";
}
//$web_site_url = ""; //Validate properly before use
if (!is_numeric($user_arival_duration)) {
	$user_arival_duration = 0;
}
if (!preg_match('/^[a-z0-9]{3,10}$/i', $layout)) {
	$layout = "blue";
}
if (!preg_match('/^[a-z]{2}$/i', $user)) {
	$user = 'AA';
}
$script_dir = '';
$error = "The credentials you supplied were not correct or did not grant access to chat session</body></html>";
//$db = new stdClass();
//include_once($script_dir . 'conf.php');
//include_once($script_dir . 'lib/DBManager.php');
//include_once($script_dir . 'lib/UserAuth.php');
//include_once($script_dir . 'model/Model.php');
//include_once($script_dir . 'model/MCcSettings.php');
//include_once($script_dir . 'config/constant.php');

//UserAuth::setDBSuffix('');
//$conn = new DBManager($db);

$settings_data = do_cc_request("user=$user&method=getFormatAllSettings&pageId=$page_id&domain=$domain&siteKey=$site_key&wwwIp=$www_ip");
// echo "<pre>";
// var_dump($settings_data);
if (substr($settings_data, 0, 3) == "200") {
    // var_dump(substr($settings_data, 4));
    $settings_data = substr($settings_data, 4);
    $settings_data = json_decode($settings_data, false);
}else{
    $settings_data = [];
}
// var_dump($settings_data);

$tmp_data = [];
if (!empty($settings_data) && is_array($settings_data)){
    foreach ($settings_data as $key => $value) {
        $tmp_data[$value->item] = $value;
    }
    $settings_data = $tmp_data;
}
// var_dump($settings_data);
// die('settings_data');


$isApiErr = true;
$ws_token_data = "";
$ws_token_data_log = "";
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($api_token) && preg_match('/^[a-z0-9]{40}$/i', $api_token)) {
	//echo "user=$user&method=checkSingleSignToken&token=$api_token&ip=$ip";
    $resp = do_cc_request("user=$user&method=checkSingleSignToken&token=$api_token&ip=$ip");
    //echo $resp;
    	
    if (substr($resp, 0, 3) == "200") {
        $ws_token_data = substr($resp, 4);
		$ws_token_data_log = $ws_token_data;
        $ws_token_data = json_decode($ws_token_data);
        $isApiErr = false;
    } else {
        $error = "The request has expired</body></html>";
    }
} else {
    $error = "Access Token has expired</body></html>";
}

$logData = [
	'ws_token_data: '.$ws_token_data_log
];
log_text($logData, "chat_req_log/");


$isErr = true;
$ws_token = "";
$ip = $_SERVER['REMOTE_ADDR'];
if ($token_validation) {
	if (preg_match('/^[a-z0-9]{32}$/i', $token)) {
		$resp = do_cc_request("user=$user&method=checkToken&token=$token&ip=$ip&type=1");
		//echo $resp;
		if (substr($resp, 0, 3) == "200") {
			$ws_token = substr($resp, 4);
			$isErr = false;
		} else {
			$error = "The page has expired</body></html>";
		}
	}
} else {
	
	$resp = do_cc_request("user=$user&method=getToken&src=$page_id&ip=$ip&type=2");	
	//echo $resp;
	if(substr($resp, 0, 3) == "200") {
		$ws_token = substr($resp, 4);
		$isErr = false;
	}
}

$result = null;

// echo "<pre>";
// var_dump($isErr);
// var_dump($page_id);
// var_dump(preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id));
// var_dump(preg_match('/^[a-z_\-\.0-9]{5,40}$/i', $domain));
// var_dump(preg_match('/^[0-9\.]{0,15}$/i', $www_ip));
// var_dump(preg_match('/^[a-z_\-0-9]{5,32}$/i', $site_key));


if (!$isErr && !$isApiErr) {
	if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z_\-\.0-9]{5,40}$/i', $domain) && preg_match('/^[0-9\.]{0,15}$/i', $www_ip) && preg_match('/^[a-z_\-0-9]{5,32}$/i', $site_key)) {
		$resp = do_cc_request("user=$user&method=checkChatRequest&pageId=$page_id&domain=$domain&siteKey=$site_key&wwwIp=$www_ip");
		//$sql = "SELECT skill_id FROM chat_page WHERE page_id='$page_id' AND www_domain='$domain' AND www_ip='$www_ip' AND site_key='$site_key' AND active='Y'";
		//$result = $conn->query($sql);
  //       var_dump($resp);
		if (substr($resp, 0, 3) == "200") {
			$result = array();
		}
        // var_dump($result);
	}
}
// die('dgdg');

// check offhour
$officeStatus = 'open';
if(isset($settings_data['offtime_from']->value) && isset($settings_data['offtime_to']->value)){
    $currentTime = date("H:i");
    $closeFrom = $settings_data['offtime_from']->value;
    $closeTo = $settings_data['offtime_to']->value;
    // $closeFrom = "20:00:00";
    // $closeTo = "09:59:59";
	if($closeFrom < $closeTo){                  //same day
       if ($currentTime >= $closeFrom && $currentTime <= $closeTo) {
           $officeStatus = 'close';
       }else{
           $officeStatus = 'open';
       }
   }else{                      //different day
       if ($currentTime > $closeTo && $currentTime < $closeFrom) {
           $officeStatus = 'open';
       }else{
           $officeStatus = 'close';
       }
   }
}
$officeStatus = 'open';
/*if ($currentTime < $closeFrom && $currentTime > $closeTo) {
    send_error(200, json_encode([
        MSG_RESULT => true,
        MSG_TYPE => MSG_SUCCESS,
        MSG_MSG => 'open'
    ]));
}
send_error(200, json_encode([
    MSG_RESULT => false,
    MSG_TYPE => MSG_ERROR,
    MSG_MSG => 'close'
]));*/

$version = "1.4.2";
?>
<html>
    <head>
        <title>Live Chat</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/jquery-ui/jquery-ui.min.css">
        <link href="<?php echo $asset_url; ?>plugins/font-awesome-4.4.0/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/rateyo/jquery.rateyo.min.css?v=<?php echo $version; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>css/<?php echo $css_source; ?>/gchat.css?v=<?php echo $version; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>css/<?php echo $css_source; ?>/gchat.<?php echo $layout; ?>.css?v=<?php echo $version; ?>">

        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery.browser.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-ui/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/rateyo/jquery.rateyo.min.js?v=<?php echo $version; ?>"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bootbox/bootbox.min.js?v=<?php echo $version; ?>"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-validation/jquery.validate.min.js?v=<?php echo $version; ?>"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-validation/additional-methods.min.js?v=<?php echo $version; ?>"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bnKb/driver.phonetic.js?v=<?php echo $version; ?>" charset="utf-8"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bnKb/engine.js?v=<?php echo $version; ?>" charset="utf-8"></script>
		<script type="text/javascript" src="<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/gplex.localStorage.js"></script>

        <script type="text/javascript" src="<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/wss.gPlexCCChatWS.js?v=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/autosize.js"></script>
        
    </head>
    <body>
        <?php
        if (!is_array($result)) {
		?>
		<div id="chat_preloader" class="chat-preloader text-center" style="display: block;">
			<?php echo $error; ?>
		</div>
		<?php
        }else{
        ?>
            <audio id="chatAudio">
				<source src="ccd/notify.wav" type="audio/wav">
            </audio>
			<div id="chat_preloader" class="chat-preloader text-center" style="display: block;">
                <label><small>Please wait...</small></label>
                <img class="img-fluid" src="<?php echo $asset_url; ?>images/5_34.gif" alt="preloader"/>
            </div>
			
			<div id="wsCCOverlay" class="text-center" style="display: none;">
				<div class="wsCCOverlay-div">
					<span class='cell_1'>Not connected.</span> Connecting in <span class='cell_2'>10s</span> 
					<br/><br/>
					<a href='javascript:void(0)' class='cell_3 btn btn-danger'>Try Now</a> OR <a href='javascript:void(0)' class='cell_4 btn btn-warning'>Leave Message</a> 
				</div>
            </div>
			
            <script>
                var layout = "<?php echo $layout; ?>";
                var domain = "<?php echo $domain; ?>";
                var page_id = "<?php echo $page_id; ?>";
                var site_key = "<?php echo $site_key; ?>";
                var www_ip = "<?php echo $www_ip; ?>";
                var user = "<?php echo $user; ?>";
                var is_rnd = "<?php echo $is_rnd; ?>";
                var baseUrl = "<?php echo $url; ?>";
                var assetUrl = "<?php echo $asset_url; ?>";
                var js_source = "<?php echo $js_source; ?>";
                var css_source = "<?php echo $css_source; ?>";
                var web_site_url = "<?php echo $web_site_url; ?>";
                var user_arival_duration = "<?php echo $user_arival_duration; ?>";
                var language = "";
                var default_language = "<?php echo $language; ?>";
                var service = "";
                var srv_name = "<?php echo $_SERVER['SERVER_NAME'];?>";
    			var srv_token = "<?php echo $ws_token;?>";
                var return_url = '<?php echo (isset($settings_data['chat_return_url']->value)) ? $settings_data['chat_return_url']->value : ''; ?>';
                var greetings_start_time = '<?php echo (isset($settings_data['greetings_start_time']->value)) ? json_encode(explode(',', $settings_data['greetings_start_time']->value)) : json_encode([]); ?>';
                var greetings_end_time = '<?php echo (isset($settings_data['greetings_end_time']->value)) ? json_encode(explode(',', $settings_data['greetings_end_time']->value)) : json_encode([]); ?>';
                var greetings_message = '<?php echo (isset($settings_data['greetings_message']->value)) ? json_encode(explode(',', $settings_data['greetings_message']->value)) : json_encode([]);?>';
                var offtime_from = '<?php echo (isset($settings_data['offtime_from']->value)) ? date("h:i a",strtotime($settings_data['offtime_from']->value) ) : ''; ?>';
                var offtime_to = '<?php echo (isset($settings_data['offtime_to']->value)) ? date("h:i a",strtotime($settings_data['offtime_to']->value) ): ''; ?>';
                var office_status = '<?php echo (isset($officeStatus)) ? $officeStatus: 'open'; ?>';
				var cob_subject = '<?php echo (isset($subject)) ? trim($subject): 'General'; ?>';
				var playstore_link = '<?php echo (isset($settings_data['playstore_link']->value)) ? $settings_data['playstore_link']->value : ''; ?>';
				var appstore_link = '<?php echo (isset($settings_data['appstore_link']->value)) ? $settings_data['appstore_link']->value : ''; ?>';
				var chat_queue_text = '<?php echo (isset($settings_data['chat_queue_text']->value)) ? addslashes($settings_data['chat_queue_text']->value) : ''; ?>';
                var ice_feedback_msg = '<?php echo (isset($settings_data['ice_feedback_msg']->value)) ? addslashes($settings_data['ice_feedback_msg']->value) : ''; ?>';
                var ice_feedback_msg_for_blank_webchat = '<?php echo (isset($settings_data['blank_ice_feedback_msg']->value)) ? addslashes($settings_data['blank_ice_feedback_msg']->value) : ''; ?>';
				<?php if(!empty($ws_token_data)){ ?>
                    var ws_token_falg = true;
                    var c_name = '<?php echo $ws_token_data->cname; ?>';
                    var c_number = '<?php echo $ws_token_data->cnumber; ?>';
                    var c_verify = '<?php echo $ws_token_data->cverify; ?>';
                <?php }else{ ?>
                    var ws_token_falg = false;
                <?php } ?>
				
                (function(d, s, id) {
                    /*
                    if (typeof (history.pushState) != "undefined") {
                        var obj = {Page: 'chat', Url: '/'};
                        history.pushState(obj, obj.Page, obj.Url);
                    }
                    */
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/wss.jssdk.js?<?php echo time(); ?>";
                    //js.src = "http://ccportal.gplex.com/ccd/jssdk.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'chat-jssdk'));

                console.log();
            </script>

            
			
            <?php
            if($user=="AB") {
            ?>
                
            <?php } ?>
        <?php } ?>
    </body>
</html>
