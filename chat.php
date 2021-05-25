<?php

if(isset($_GET["chat_rating"]) && !empty($_GET["chat_rating"])) {
    $dbSuffix = isset($_GET["user"]) ? trim($_GET["user"]) : "";
    $script_dir = '';
    $db = new stdClass();
    include_once($script_dir . 'conf.php');
    include_once($script_dir . 'lib/DBManager.php');
    include_once($script_dir . 'lib/UserAuth.php');
    UserAuth::setDBSuffix($dbSuffix);
    $conn = new DBManager($db);


    $chat_rating = isset($_GET["chat_rating"]) ? trim($_GET["chat_rating"]) : "0";
    $call_id = isset($_GET["call_id"]) ? trim($_GET["call_id"]) : "";
    $pos = strpos($call_id,"-");
    $call_id = substr($call_id,0,$pos);

    if (ctype_digit($call_id)) {
    	$sql = "UPDATE cdrin_log SET agent_rating=$chat_rating WHERE callid='$call_id' LIMIT 1";
    	$result = $conn->query($sql);
	}

    echo json_encode(array("chat_rating"=>$chat_rating));
    exit;
}

/**
 * set default value for chat
 */
//if($is_rnd==1) $url = "http://72.48.199.126/cloudcc";
//else $url = "http://ccportal.gplex.com";
$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$url = substr(rtrim($url, 'chat.php'), 0, -1);
$asset_url = $url.'/ccd/new/';
$js_source = 'src';
$css_source = 'src';

$layout= $domain = $page_id = $site_key = $www_ip = $user = $web_site_url = '';
$is_rnd = $user_arival_duration = 0;
if (isset($_GET["site_key"])) {
    $layout = $_GET["layout"];
    $domain = $_GET["domain"];
    $page_id = $_GET["page_id"];
    $site_key = $_GET["site_key"];
    $www_ip = $_GET["www_ip"];
    $user = strtoupper($_GET["user"]);
    $is_rnd = $_GET["is_rnd"];
} else {
    $query_string = base64_decode($_SERVER['QUERY_STRING']);
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
        $user_arival_duration = isset($qr["user_arival_duration"]) ? $qr["user_arival_duration"] : 0;
    }
}

$script_dir = '';
$error = "The credentials you supplied were not correct or did not grant access to chat session</body></html>";
$db = new stdClass();
include_once($script_dir . 'conf.php');
include_once($script_dir . 'lib/DBManager.php');
include_once($script_dir . 'lib/UserAuth.php');
include_once($script_dir . 'model/Model.php');
include_once($script_dir . 'model/MCcSettings.php');
include_once($script_dir . 'config/constant.php');

UserAuth::setDBSuffix('');
$conn = new DBManager($db);

$settings_model = new MCcSettings();
$settings_model->module_type = MOD_CHAT;
$settings_data = $settings_model->getFormatAllSettings();
?>
<html>
    <head>
        <title>Live Chat</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/jquery-ui/jquery-ui.css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>plugins/rateyo/jquery.rateyo.min.css?v=1.0">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>css/<?php echo $css_source; ?>/gchat.css?v=1.1.0">
        <link rel="stylesheet" type="text/css" href="<?php echo $asset_url; ?>css/<?php echo $css_source; ?>/gchat.<?php echo $layout; ?>.css?v=1.1.0">

        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery.browser.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-ui/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/rateyo/jquery.rateyo.min.js?v=1.0"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bootbox/bootbox.min.js?v=1.0"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-validation/jquery.validate.min.js?v=1.0"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/jquery-validation/additional-methods.min.js?v=1.0"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bnKb/driver.phonetic.js?v=1.0" charset="utf-8"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>plugins/bnKb/engine.js?v=1.0" charset="utf-8"></script>

        <script type="text/javascript" src="<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/wss.gPlexCCChatWS.js"></script>
        <script type="text/javascript" src="<?php echo $asset_url; ?>js/<?php echo $js_source; ?>/autosize.js"></script>
        
    </head>
    <body>
        <?php       
/*
        echo $sql = "SELECT module_chat FROM account WHERE db_suffix='$user' AND module_chat='Y'";
        $result = $conn->query($sql);
        if (!is_array($result)) {
                echo $error;
                exit;
        }
*/
    	if (preg_match('/^[a-z_\-0-9]{5,18}$/i', $page_id) && preg_match('/^[a-z_\-\.0-9]{5,40}$/i', $domain) && 
    		preg_match('/^[0-9\.]{5,15}$/i', $www_ip) && preg_match('/^[a-z_\-0-9]{5,32}$/i', $site_key)) {
    		$sql = "SELECT skill_id FROM chat_page WHERE page_id='$page_id' AND www_domain='$domain' AND www_ip='$www_ip' AND site_key='$site_key' AND active='Y'";
            $result = $conn->query($sql);
        } else {
    		$result = null;
    	}
            
    	if (!is_array($result)) {
        	echo $error;
        	exit;
    	}
        ?>
        <audio id="chatAudio">
                <source src="ccd/notify.wav" type="audio/wav">
        </audio>
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
            var service = "";
            var srv_name = "<?php echo $_SERVER['SERVER_NAME'];?>";
            var return_url = 'https://cc-gsoft.gplex.net/chat_example/chat.html';
            var greetings_start_time = '<?php echo json_encode(explode(',', $settings_data['greetings_start_time']->value));?>';
            var greetings_end_time = '<?php echo json_encode(explode(',', $settings_data['greetings_end_time']->value));?>';
            var greetings_message = '<?php echo json_encode(explode(',', $settings_data['greetings_message']->value));?>';
            
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
        </script>

        <div id="chat_preloader" class="chat-preloader text-center" style="display: block;">
            <label><small>Please wait...<small></label>
            <img class="img-fluid" src="<?php echo $asset_url; ?>images/5_34.gif" alt="preloader">
        </div>

        <?php
        if($user=="AB") {
        ?>
            <!-- Google Tag Manager -->
            <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-T3DXLK"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-T3DXLK');</script>
            <!-- End Google Tag Manager -->
        <?php } ?>
    </body>
</html>