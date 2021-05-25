<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if( !session_id() ) @session_start();
define('BASEPATH', dirname(__FILE__)."/../");

$script_dir = '';
$db = new stdClass();
include_once($script_dir . '../conf.php');
include_once($script_dir . '../config/constant.php');
include_once($script_dir . '../config/common.php');
include_once($script_dir . '../lib/DBManager.php');
include_once($script_dir . '../lib/FormValidator.php');
include_once($script_dir . '../lib/UserAuth.php');

$user = isset($_GET["user"]) ? strtoupper($_GET["user"]) : "AA";
$user = trim($user);
$method = isset($_GET["method"]) ? trim($_GET["method"]) : "";
$method = 'Fn_' . $method;

if (strlen($user) != 2 || !function_exists($method)) send_error(401, "Unauthorized");

UserAuth::setDBSuffix($user);
$conn = new DBManager($db);
$master_db = '';

$i = rand(1,10);
if ($i%5 == 0) {
	clearUnUsedTokens();
}

if (strlen($user) == 2) {
  	$master_db = 'cc' . strtoupper($user);
  	if ($user == 'AA') $master_db = 'cc';
} else {
  	$master_db = 'cc_master';
}
$method();

exit;

function clearUnUsedTokens()
{
	global $master_db, $conn;
	$conn->query("DELETE FROM " . $master_db . ".chat_token WHERE generate_time < DATE_SUB(NOW(),INTERVAL 15 MINUTE)");
}

function Fn_getServices()
{
	global $master_db, $conn;

	$src = isset($_GET["src"]) ? trim($_GET["src"]) : "";
	$sql = "SELECT service_id, service_name FROM " . $master_db . ".chat_service WHERE page_id='$src'";
    $result = $conn->query($sql);
    $service = "";
    if(!empty($result) && count($result) > 0){
	    foreach($result as $row) {
	            $service_id = $row->service_id;
	            $service_name = $row->service_name;
	            $service .= $service_id."=".$service_name."|";
	    }
	    $service = trim($service,"|");
	}
	send_error(200, $service);
}

function Fn_rateChat()
{
	global $master_db, $conn;
	
	$callId = isset($_GET["callId"]) ? trim($_GET["callId"]) : "";
	$chat_rating = isset($_GET["rate"]) ? trim($_GET["rate"]) : "";
    if (strpos($callId, "-") !== false) {
        $call_id = array_shift(explode('-', $callId));
    }

    // add log
	$logData = [
		'PostData: '.json_encode($_GET)
	];
	log_text($logData, 'temp/webchat_rate_log/');

	if (ctype_digit($callId) && ctype_digit($chat_rating)) {
		$ststamp = strtotime("-2 hours");
		$etstamp = strtotime("now");
		$sql = "UPDATE " . $master_db . ".cdrin_log SET agent_rating=$chat_rating WHERE tstamp between '".$ststamp."' and '".$etstamp."' and callid='$callId' LIMIT 1";
		// add log
		$logData = [
			'SQL: '.$sql
		];
		log_text($logData, 'temp/webchat_rate_log/');

		$result = $conn->query($sql);
		// add log
		$logData = [
			'Result: '.json_encode($result)
		];
		log_text($logData, 'temp/webchat_rate_log/');

        $sql = "UPDATE " . $master_db . ".chat_detail_log SET customer_feedback = '$chat_rating' WHERE callid='$callId' LIMIT 1";
        // add log
		$logData = [
			'SQL: '.$sql,
		];
		log_text($logData, 'temp/webchat_rate_log/');

        $result = $conn->query($sql);
        // add log
		$logData = [
			'Result: '.json_encode($result)
		];
		log_text($logData, 'temp/webchat_rate_log/');
	}
	send_error(200, "OK");
}

function Fn_checkChatRequest()
{
	$pageId = isset($_GET["pageId"]) ? trim($_GET["pageId"]) : "";
	$domain = isset($_GET["domain"]) ? trim($_GET["domain"]) : "";
	$siteKey = isset($_GET["siteKey"]) ? trim($_GET["siteKey"]) : "";
	//$wwwIP = isset($_GET["pageId"]) ? strtoupper($_GET["pageId"]) : "";
	global $master_db, $conn;

	$sql = "SELECT skill_id FROM " . $master_db . ".chat_page WHERE page_id='$pageId' AND www_domain='$domain' AND site_key='$siteKey' AND active='Y'";
	$result = $conn->query($sql);
	if (is_array($result)) {
		send_error(200, "OK");
	}
	send_error(400, 'Unauthorized');
}

function Fn_getLanguages()
{
	global $master_db, $conn;

	$src = isset($_GET["src"]) ? trim($_GET["src"]) : "";
	$sql = "SELECT l.lang_key, l.lang_title FROM " . $master_db . ".language l, " . $master_db . ".chat_page c WHERE l.lang_key=c.language AND c.page_id='$src' AND l.status='A'";
        $result = $conn->query($sql);
        $language = "";
        foreach($result as $row) {
                $lang_key = $row->lang_key;
                $lang_title = $row->lang_title;
                $language .= $lang_key."=".$lang_title."|";
        }
        $language = trim($language,"|");
        
        send_error(200, $language);
}

function Fn_getServiceSkillId()
{
	global $master_db, $conn;

	$srv = isset($_GET["srv"]) ? trim($_GET["srv"]) : "";
	$page_id = isset($_GET["page_id"]) ? trim($_GET["page_id"]) : "";

	if (empty($page_id)) send_error(404, "Not Found");

	$sql = "SELECT skill_id FROM " . $master_db . ".chat_service WHERE service_id='$srv' LIMIT 1";
        $result = $conn->query($sql);
        $skill_id = "";
        if (is_array($result)) {
                $skill_id = $result[0]->skill_id;
        }

        if (empty($skill_id)) {
                $sql = "SELECT skill_id FROM " . $master_db.".chat_page WHERE page_id='$page_id' AND active='Y'";
                $result = $conn->query($sql);
                if (is_array($result)) {
                        $skill_id = $result[0]->skill_id;
                }
        }

	send_error(200, $skill_id);
}

function Fn_getNumChatAgents()
{
	global $master_db, $conn;

	$skill_id = isset($_GET["skill_id"]) ? trim($_GET["skill_id"]) : "";
	
	$num = 0;
	
	$sql = "select count(agent_id) AS numrows FROM " . $master_db . ".seat where agent_id in (select agent_id FROM " . $master_db . 
		".agent_skill AS a, " . $master_db . ".skill AS s where qtype='C' AND a.skill_id=s.skill_id)";
	
	// $sql = "select count(agent_id) AS numrows FROM " . $master_db . ".agents where agent_id in (select agent_id FROM " . $master_db . 
	// 	".agent_skill AS a, " . $master_db . ".skill AS s where ";
	// if (!empty($skill_id)) $sql .= "s.skill_id='$skill_id' AND ";
	// $sql .= "qtype='C' AND a.skill_id=s.skill_id) AND seat_id!=''";
// var_dump($sql);

	$result = $conn->query($sql);
// var_dump($result);

	if (is_array($result)) {
		$num = ($result[0]->numrows > 0) ? '1' : '0';
	}
	send_error(200, $num);
}

function Fn_getWsEndPoint()
{
	global $master_db, $conn;
	
	$response = new stdClass();

	$sql = "SELECT ws_port, switch_ip FROM " . $master_db . ".settings LIMIT 1";
        $result = $conn->query($sql);
        if (is_array($result)) {
                        $response->ws_port = $result[0]->ws_port;
                        $response->ws_ip = $result[0]->switch_ip;
        }

	send_error(200, json_encode($response));
}

function Fn_checkToken()
{
	global $master_db, $conn;

	$type = isset($_GET["type"]) ? trim($_GET["type"]) : "2";
	$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
	$token = isset($_GET["token"]) ? trim($_GET["token"]) : "";
	
	if (!preg_match('/^[a-z0-9]{32}$/i', $token) || strlen($type) != 1) send_error(401, "Unauthorized");
	
	$sql = "SELECT ip FROM " . $master_db.".chat_token WHERE token='$token' AND token_type='$type' AND generate_time > DATE_SUB(NOW(),INTERVAL 5 MINUTE)"; // AND generate_time WITH IN 1/2 Minute
	$result = $conn->query($sql);
	if (is_array($result)) {
		if ($type == "2") {
			$conn->query("DELETE FROM " . $master_db.".chat_token WHERE token='$token' AND token_type='$type'");
			send_error(200, "OK");
		} else {
			$token2 = md5('gChat@cc:6754' . time() . rand(1,99999));
			$sql = "UPDATE " . $master_db.".chat_token SET token='$token2', generate_time=NOW(), token_type='2', ip='$ip' WHERE token='$token' AND token_type='$type' LIMIT 1";
			$conn->query($sql);
			send_error(200, $token2);
		}
		
	}
	send_error(401, "Unauthorized");
}

function Fn_getToken()
{
	global $master_db, $conn;

	$type = isset($_GET["type"]) ? trim($_GET["type"]) : "1";
	$src = isset($_GET["src"]) ? trim($_GET["src"]) : "";
	$ip = $_SERVER['REMOTE_ADDR'];

	if (empty($src)) send_error(401, "Unauthorized");
	//Validate Input

	$sql = "SELECT www_ip FROM " . $master_db.".chat_page WHERE page_id='$src'";	
	$result = $conn->query($sql);
	if (is_array($result)) {
		//check ip
		$token = md5('gChat@cc:6754' . time() . rand(1,99999));
		$conn->query("INSERT INTO " . $master_db.".chat_token SET token='$token', token_type='$type', generate_time=NOW(), ip='$ip'");
		send_error(200, $token);
	}
	send_error(401, "Unauthorized");
}

function Fn_getFormatAllSettings(){
	global $master_db, $conn;

	$sql = "SELECT * FROM ".$master_db . ".cc_settings WHERE module_type='".MOD_CHAT."'";
	$result = $conn->query($sql);
	if (!empty($result) && is_array($result)){
		send_error(200, json_encode($result));
	}else{
		send_error(200, json_encode([]));
	}
}

function Fn_insetChatLeaveMessage(){
	global $master_db, $conn;

	$condParams = []; //mysql injection param
	$insert_fields = []; //prepared insert field
	$insert_values = []; //prepared insert value
	$errors = [];
	$count = 0;
	$err_count = 0;

	// form validation and sql insert data prepare
	$db_field = [
		'cCustomerName' => [
			'title' => 'Name',
			'db_name' => 'name',
			'len' => 20,
			'validation' => [
				'required' => true, 
				'max-len' => 20,
			],
			'validation_msg' => [
				'required' => "Name is required!",
				'max-len' => "Please enter no more than 20 characters!",
			],
			'special_char_check' => true
		],
		'cCustomerEmail' => [
			'title' => 'Email',
			'db_name' => 'email',
			'len' => 35,
			'validation' => [
				// 'required' => true, 
				// 'email' => true,
				// 'max-len' => 35, 
			],
			'validation_msg' => [
				// 'required' => "Email is required!",
				// 'email' => "Email is not valid!",
				// 'max-len' => "Please enter no more than 35 characters!",
			],
			'special_char_check' => false,
			'special_char_skip' => ['.'],
		],
		'cCustomerContactNumber' => [
			'title' => 'Contact Number',
			'db_name' => 'number',
			'len' => 11,
			'validation' => [
				'required' => true,
				'max-len' => 11
			],
			'validation_msg' => [
				'required' => "Contact Number is required!",
				'max-len' => "Please enter no more than 11 characters!",
			],
			'special_char_check' => true
		],
		'leave_text_field' => [
			'title' => 'Message',
			'db_name' => 'msg',
			'len' => 300,
			'validation' => [
				'required' => true, 
				'max-len' => 300
			],
			'validation_msg' => [
				'required' => "Message is required!", 
				'max-len' => "Please enter no more than 300 characters!",
			],
			'special_char_check' => true,			
			'special_char_skip' => ['.']
		],
		'cCustomerSubject' => [
			'title' => 'Service',
			'db_name' => 'service',
			'len' => 2,
			'validation' => [
				// 'required' => true, 
				// 'max-len' => 2
			],
			'validation_msg' => [
				// 'required' => "Service is required!", 
				// 'max-len' => "Please enter no more than 2 characters!",
			],
			'special_char_check' => false
		],
	];

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);
	$validator = new FormValidator();
	// var_dump($post_data);

	foreach ($post_data as $key => $value) {
		$valid = $validator->validation(trim($post_data[$key]), $db_field[$key]['validation'], $db_field[$key]['validation_msg']);
		// var_dump($valid);

		if($valid['result']){
			// if (!empty($changed_fields)) $changed_fields .= ', ';
			$insert_fields[$count] = $db_field[$key]['db_name'];
			$insert_values[$count] = "?";
			$post_data_key = trim($post_data[$key]);
			$post_data_key = filter_var($post_data_key, FILTER_SANITIZE_STRING);
			
			$condParams[$count] = [
				"param" => $db_field[$key]['db_name'],
				"paramType" => "s", 
				"paramValue" => $post_data_key,
				'specialCharCheck' => $db_field[$key]['special_char_check'],
				'specialCharSkip' => (isset($db_field[$key]['special_char_skip'])) ? $db_field[$key]['special_char_skip'] : [],
				'paramLength' => $db_field[$key]['len']
			];

			$count++;
		}else{
			$errors[$err_count]['field'] = $key;
			$errors[$err_count]['msg'] = $valid['msg'];
			$err_count++;
		}
	}

	// var_dump($errors);
	// die('ghghgh');
	if(!empty($errors)){
		send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_DATA => $errors,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'You have some form errors. Please check below!'
		]));
	}

	if (!empty($insert_fields) && !empty($insert_values)) {		
		// set user agent ip
		$insert_fields[] = 'cus_ip';
		$insert_values[] = '?';
		$condParams[] = [
				"param" => 'cus_ip',
				"paramType" => "s", 
				"paramValue" => $_SERVER['REMOTE_ADDR'],
				'paramLength' => 15,
				'specialCharCheck' => false
			];

		// set id
		$insert_fields[] = 'id';
		$insert_values[] = '?';
		$condParams[] = [
				"param" => 'id',
				"paramType" => "s", 
				"paramValue" => (string)strtotime(date("Y-m-d H:i:s")),
				'paramLength' => 10,
				'specialCharCheck' => false
			];

		// set user agent browser info
		// $insert_fields[] = 'cus_browser';
		// $insert_values[] = '?';
		// $condParams[] = [
		// 		"param" => 'cus_browser',
		// 		"paramType" => "s", 
		// 		"paramValue" => $_SERVER['HTTP_USER_AGENT'],
		// 		'paramLength' => 150,
		// 		'specialCharCheck' => false
		// 	];

		$sql = "INSERT INTO chat_leave_msg(".implode(",", $insert_fields).") VALUES(".implode(",", $insert_values).")";
		
		$validate = $conn->validateQueryParams($condParams);
		// var_dump($sql);
		// var_dump($condParams);
		// var_dump($validate);
		// die('insert');

		if($validate['result'] == true){
			$is_insert = $conn->executeInsertQuery($sql, $validate['paramTypes'], $validate['bindParams']);
			// var_dump($is_insert);
			// die();

			if($is_insert){
				send_error(200, json_encode([
					MSG_RESULT => true,
					MSG_TYPE => MSG_SUCCESS,
					MSG_MSG => 'Your message has been submitted successfully.'
				]));			
			}
		}else{
			send_error(200, json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => $validate['msg']
			]));
		}
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! some technical reason we can not submit your message. Please try again.'
		]));
}

function Fn_sendChatCode(){
	global $master_db, $conn, $db;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);
	$auth_str_arr = [];

	$post_data_name = isset($post_data['cCustomerName']) ? trim($post_data['cCustomerName']) : '';
	$post_data_name = filter_var($post_data_name, FILTER_SANITIZE_STRING);
	$auth_str_arr[] = $post_data_name;
	$auth_str_arr[] = isset($post_data['cCustomerEmail']) ? trim($post_data['cCustomerEmail']) : '';
	$auth_str_arr[] = isset($post_data['cCustomerContactNumber']) ? trim($post_data['cCustomerContactNumber']) : '';
	$auth_str_arr[] = isset($post_data['cCustomerSubject']) ? trim($post_data['cCustomerSubject']) : '';
	$auth_str_arr[] = isset($post_data['cCustomerVerify']) ? trim($post_data['cCustomerVerify']) : '';

	$isValidReq = false;
	if (preg_match("/[`'\"~!#$^&%*(){}<>,?;:\|+=]/", $auth_str_arr[1]) && preg_match("/[`'\"~!#$^&%*(){}<>,.?;:\|=]/", $auth_str_arr[2])) {
		$isValidReq = true;
	}
	if ($isValidReq) {
		$msg_err = 'Special character found in '.($post_data['otp_sent_type'] == 'MO') ? 'Contact number.' : 'Email.';
		send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => $msg_err
		]));
	}

	$auth_str = implode('###', $auth_str_arr);
	$auth_code = generate_base64_encode($auth_str, 3);
	$_SESSION[$db->db_suffix.'chat_auth_code'] = $auth_code;

	// unique code generate
	$code = '';
	// while (true) {
		$code = random_number(STR_NUMBER, 6);
		// $sql = "SELECT * FROM opt_otc_number where code='".$code."'";
		// $result = $conn->query($sql);
		// if (empty($result)) {
		// 	break;
		// }
	// }

	// otp/otc code
	$insert_fields[] = 'code';
	$insert_values[] = $code;
	// otp/otc
	$insert_fields[] = 'type';
	$insert_values[] = 'OTP';
	// module type
	$insert_fields[] = 'module_type';
	$insert_values[] = OM_CHAT;
	// code sent by email or mobile
	$insert_fields[] = 'sent_type';
	$insert_values[] = ($post_data['otp_sent_type'] == 'MO') ? OS_MOBILE : OS_EMAIL;
	// sent address email or mobile
	$insert_fields[] = 'sent_address';
	$insert_values[] = ($post_data['otp_sent_type'] == 'MO') ? $auth_str_arr[2] : $auth_str_arr[1];
	// auth code
	$insert_fields[] = 'auth_code';
	$insert_values[] = $_SESSION[$db->db_suffix.'chat_auth_code'];
	// set user agent ip
	$insert_fields[] = 'cus_ip';
	$insert_values[] = $_SERVER['REMOTE_ADDR'];
	// set user agent browser info
	// $insert_fields[] = 'cus_browser';
	// $insert_values[] = $_SERVER['HTTP_USER_AGENT'];
	// set status
	$insert_fields[] = 'status';
	$insert_values[] = 'A';	

	// set id
	$insert_fields[] = 'id';
	$insert_values[] = (string)strtotime(date("Y-m-d H:i:s"));

	$sql = "INSERT INTO opt_otc_number(".implode(",", $insert_fields).") VALUES('".implode("','", $insert_values)."')";
	// var_dump($sql);
	$result = $conn->query($sql);
	// var_dump($result);
	// die('fsfs');

	if($result){
		if($post_data['otp_sent_type']=='MO'){
			$res = 'SUCCESS';
			$dialto = $auth_str_arr[2];
			// $url = "http://127.0.0.1:3025/cgi-bin/sendsms";
			$url = "http://10.101.92.20:3025/cgi-bin/sendsms";
			if (substr($dialto,0,1) == 0) $dialto = '88' . $dialto;
			elseif (substr($dialto,0,1) == 1 && strlen(10)) $dialto = '880' . $dialto;

			$smstext = "OTP for Chat authentication is ".$code.'. Valid for next 3 minutes.';
			$postdata = "username=gPlexCCT&password=gPlexCCT&from=22274&to=+$dialto&text=" . urlencode($smstext);
			$url = $url . '?' . $postdata;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_POST, true );
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			$result = trim(@curl_exec($ch));
			curl_close($ch);

			send_error(200, json_encode([
					MSG_RESULT => true,
					MSG_TYPE => MSG_SUCCESS,
					MSG_MSG => '',
					MSG_DATA => [
						'auth_code' => $_SESSION[$db->db_suffix.'chat_auth_code']
					]
				]));

		}elseif($post_data['otp_sent_type']=='EM'){
			include_once( APPPATH.'lib/phpmailer/phpmailer/phpmailer/src/Exception.php' );
			include_once( APPPATH.'lib/phpmailer/phpmailer/phpmailer/src/PHPMailer.php' );
			include_once( APPPATH.'lib/phpmailer/phpmailer/phpmailer/src/SMTP.php' );

			$sql = "SELECT * FROM cc_settings WHERE module_type='".MOD_CHAT."' ";
			$old_data = $conn->query($sql);
			$settings_data = [];
			foreach ($old_data as $key => $value) {
				$settings_data[$value->item] = $value;
			}

			$mail = new PHPMailer\PHPMailer\PHPMailer(true);
			// var_dump($mail);
			// die();
			
			try {
			    //Server settings
			    $mail->SMTPDebug = 0;                               // Enable verbose debug output
			    $mail->isSMTP();                                    // Set mailer to use SMTP
			    $mail->Host = $settings_data['smtp_host']->value;  				   // Specify main and backup SMTP servers
			    $mail->SMTPAuth = true;                             // Enable SMTP authentication
			    $mail->Username = $settings_data['smtp_username']->value;           // SMTP username
			    $mail->Password = $settings_data['smtp_password']->value;               // SMTP password
			    $mail->SMTPSecure = $settings_data['smtp_secure_opton']->value;                          // Enable TLS encryption, `ssl` also accepted
			    $mail->Port = $settings_data['smtp_port']->value;                                  // TCP port to connect to

			    //Recipients
			    $mail->setFrom($settings_data['otp_form_email']->value, $settings_data['otp_form_name']->value);
			    $mail->addAddress($auth_str_arr[1], $auth_str_arr[0]);     // Add a recipient
			    // $mail->addAddress('ellen@example.com');                    // Name is optional
			    // $mail->addReplyTo('info@example.com', 'Information');
			    // $mail->addCC('cc@example.com');
			    // $mail->addBCC('bcc@example.com');

			    //Content
			    $mail->isHTML(true);                                       // Set email format to HTML
			    $mail->Subject = 'One Time Password (OTP) to verify your authentication.';
			    $body = 'Dear '.$auth_str_arr[0].',<br/><br/>';
			    $body .= 'Your One Time Password (OPT) for chat authentication is: '.$code.'<br/><br/>';
			    $body .= 'Please insert the password in the pop-up window on the screen asking for OTP verification. Please note that the One-Time Password remains valid for only 5 minutes.<br/><br/>';
			    $body .= 'For security reason, you should not share this password with anyone. In case of any query or any suspicious situation (for example: if you have not requested for OTP), please call our 24/7 Call Center on 55555.<br/><br/>';
			    $body .= 'Have a great online shopping experience!<br/><br/>';
			    $body .= 'Regards,<br/>';
			    $body .= 'Genuity Systems Ltd.<br/><br/>';

			    $mail->Body = $body;

			    $flag = $mail->send();
			    // var_dump($flag);
			    send_error(200, json_encode([
						MSG_RESULT => true,
						MSG_TYPE => MSG_SUCCESS,
						MSG_MSG => '',
						MSG_DATA => [
						'auth_code' => $_SESSION[$db->db_suffix.'chat_auth_code']
					]
				]));
			} catch (Exception $e) {
			    //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			    send_error(200, json_encode([
					MSG_RESULT => false,
					MSG_TYPE => MSG_ERROR,
					MSG_MSG => 'OTP could not be sent. Mailer Error: '. $mail->ErrorInfo
				]));
			}
		}
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! some technical reason we can not submit your message. Please try again.'
		]));
}

function Fn_checkChatCode(){
	global $master_db, $conn, $db;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$auth_code_decode = generate_base64_decode($_SESSION[$db->db_suffix.'chat_auth_code'], 3);
	$auth_str_arr = explode('###', $auth_code_decode);
	// var_dump($auth_str_arr);
	// var_dump($_SESSION);

	$sql = "SELECT * FROM opt_otc_number WHERE status='A' AND code='".$post_data['code']."'";
	$result = $conn->query($sql);
	// var_dump($result);
	// var_dump($sql);
	// die();

	if(!empty($result) && $result[0]->code == trim($post_data['code'])){
		$sql = "UPDATE opt_otc_number SET status='I', updated_at='".date("Y-m-d H:i:s")."' WHERE id=".$result[0]->id;
		$result_up = $conn->query($sql);
		// var_dump($result_up);

		send_error(200, json_encode([
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_MSG => ''
		]));
	}else{
		send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Your OTP number is not valid.'
		]));
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! some technical reason we can not authorize for chat session. Please try again.'
		]));
}
function Fn_insertChatQueue(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$condParams = []; //mysql injection param
	$insert_fields = []; //prepared insert field
	$insert_values = []; //prepared insert value
	$errors = [];
	$count = 0;
	$err_count = 0;


	// sql insert data prepare
	$db_field = [
		'cCustomerCallId' => [
			'db_name' => 'call_id',
			'len' => 22,
			'special_char_check' => true,
			'special_char_skip' => ['-'],
		],	
		'cCustomerName' => [
			'db_name' => 'name',
			'len' => 20,
			'special_char_check' => true
		],
		'cCustomerEmail' => [
			'db_name' => 'email',
			'len' => 35,
			'special_char_check' => false,
			'special_char_skip' => ['.'],
		],
		'cCustomerContactNumber' => [
			'db_name' => 'contact_number',
			'len' => 11,
			'special_char_check' => true
		],
		'cCustomerSubject' => [
			'db_name' => 'service_id',
			'len' => 2,
			'special_char_check' => true
		],
		'cCustomerVerify' => [
			'db_name' => 'verify_user',
			'len' => 1,
			'special_char_check' => true
		],
	];
	// var_dump($post_data);

	$count = 0;
	foreach ($post_data as $key => $value) {
		$insert_fields[$count] = $db_field[$key]['db_name'];
		$insert_values[$count] = "?";
		$post_data_key = trim($post_data[$key]);
		$post_data_key = filter_var($post_data_key, FILTER_SANITIZE_STRING);
		
		$condParams[$count] = [
			"param" => $db_field[$key]['db_name'],
			"paramType" => "s",
			"paramValue" => $post_data_key,
			'specialCharCheck' => $db_field[$key]['special_char_check'],
			'paramLength' => $db_field[$key]['len'],
			'specialCharSkip' => isset($db_field[$key]['special_char_skip']) ? $db_field[$key]['special_char_skip'] : ''
		];

		$count++;
	}
	// var_dump($insert_fields);
	// var_dump($insert_values);
	// var_dump($condParams);
	// die('sfsfsf');

	if (!empty($insert_fields) && !empty($insert_values)) {
		//read queue number 
		$sql = "SELECT MAX(queue) as max_queue FROM chat_queue WHERE queue_status='".STATUS_WAITING."'";
		$max_queue = $conn->query($sql);
		$queue_number = (empty($max_queue[0]->max_queue)) ? 1 : $max_queue[0]->max_queue+1;

		// set id
		$insert_fields[] = 'id';
		$insert_values[] = '?';
		$condParams[] = [
				"param" => 'id',
				"paramType" => "s", 
				"paramValue" => (string)strtotime(date("Y-m-d H:i:s")),
				'paramLength' => 10,
				'specialCharCheck' => false
			];

		// set queue number
		$insert_fields[] = 'queue';
		$insert_values[] = '?';
		$condParams[] = [
				"param" => 'queue',
				"paramType" => "d", 
				"paramValue" => $queue_number,
				'paramLength' => 3,
				'specialCharCheck' => false
			];

		// set queue status
		$insert_fields[] = 'queue_status';
		$insert_values[] = '?';
		$condParams[] = [
				"param" => 'queue_status',
				"paramType" => "s", 
				"paramValue" => STATUS_WAITING,
				'paramLength' => 1,
				'specialCharCheck' => false
			];

		$sql = "INSERT INTO chat_queue(".implode(",", $insert_fields).") VALUES(".implode(",", $insert_values).")";
		// var_dump($sql);		
		$validate = $conn->validateQueryParams($condParams);
		// var_dump($validate);
		// die('sfsfsf');

		if($validate['result'] == true){
			$is_insert = $conn->executeInsertQuery($sql, $validate['paramTypes'], $validate['bindParams']);
			if($is_insert){
				send_error(200, json_encode([
					MSG_RESULT => true,
					MSG_TYPE => MSG_SUCCESS,
					MSG_MSG => '',
					'queue_number' => $queue_number
				]));			
			}
		}else{
			send_error(200, json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => $validate['msg']
			]));
		}
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! Your request is wrong!'
		]));
}

function Fn_updateChatQueue(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$call_id = trim($_REQUEST['cCustomerCallId']);
	$contact_number = trim($_REQUEST['cCustomerContactNumber']);
	$contact_number = substr($contact_number, -10);
	$customer_verify = trim($_REQUEST['cCustomerVerify']);
	
	//Read chat queue
	$sql = "SELECT * FROM chat_queue WHERE call_id='".$call_id."' AND queue_status='".STATUS_WAITING."'";
	$result = $conn->query($sql);
	$old_queue = $result[0]->queue;

	//update queue number after caht accept
	$sql = "UPDATE chat_queue SET queue = 0, queue_status='".STATUS_SERVED."', old_queue=".$result[0]->queue.", updated_at='".date("Y-m-d H:i:s")."' WHERE call_id='".$call_id."' AND queue_status='".STATUS_WAITING."'";
	// var_dump($sql);
	$result = $conn->query($sql);

	//update queue number after caht accept
	if($_REQUEST['queue_number'])
		$sql = "UPDATE chat_queue SET queue = queue -1 WHERE queue > ".trim($_REQUEST['queue_number'])." AND queue_status='".STATUS_WAITING."'";
	else
		$sql = "UPDATE chat_queue SET queue = queue -1 WHERE queue > 1 AND queue_status='".STATUS_WAITING."'";

	$result = $conn->query($sql);

	if($customer_verify == 'Y'){
		$call_id = explode('-', $call_id);
		$sql = "INSERT INTO log_customer_journey (customer_id, module_type, module_sub_type, log_time, journey_id ) VALUES('".$contact_number."', 'CT', 'CA', '".date("Y-m-d H:i:s")."', '".$call_id[0]."') ";
		// var_dump($sql);
		$result_journey = $conn->query($sql);
	}
	
	if(!empty($result)){
		send_error(200, json_encode([
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_MSG => '',
			'queue_number' => $result[0]->queue
		]));	
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! Your request is wrong!'
		]));
}
function Fn_checkChatQueue(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$call_id = trim($_REQUEST['cCustomerCallId']);
	$sql = "SELECT * FROM chat_queue WHERE call_id='".$call_id."' AND queue_status='".STATUS_WAITING."'";
	$result = $conn->query($sql);
	if(!empty(result)){
		send_error(200, json_encode([
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_MSG => '',
			'queue_number' => $result[0]->queue
		]));	
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! Your request is wrong!'
		]));
}
function Fn_checkVerifyUser(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$call_id = trim($_REQUEST['cCustomerCallId']);
	$sql = "SELECT * FROM chat_queue WHERE call_id='".$call_id."' ";
	// var_dump($sql);

	$result = $conn->query($sql);
	if(!empty($result)){
		send_error(200, json_encode([
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_MSG => '',
			'verify_user' => $result[0]->verify_user
		]));	
	}

	send_error(200, json_encode([
			MSG_RESULT => false,
			MSG_TYPE => MSG_ERROR,
			MSG_MSG => 'Sorry! Your request is wrong!'
		]));
}
function send_error($code, $msg='')
{
	echo $code;
	if (!empty($msg)) echo " " . $msg;
	exit;
}

function Fn_updateChatFeedback()
{
    global $master_db, $conn;

    $post_data = $_REQUEST; // post data
    unset($post_data['method']);

    $call_id = trim($_REQUEST['call_id']);
    $rating = trim($_REQUEST['chat_feedback']);

    if (strpos($call_id, "-") !== false){
        $call_id = array_shift(explode('-', $call_id));
    }
    $sql = "UPDATE log_skill_inbound SET ice_feedback = '$rating' WHERE callid='$call_id '";

    $result = $conn->query($sql);

    if (!empty($result)) {
        send_error(200, json_encode([
            MSG_RESULT => true,
            MSG_TYPE => MSG_SUCCESS,
            MSG_MSG => ''
        ]));
    }

    send_error(200, json_encode([
        MSG_RESULT => false,
        MSG_TYPE => MSG_ERROR,
        MSG_MSG => 'Sorry! Your request is wrong!!!'
    ]));
}

function Fn_checkServerTime()
{
    $currentTime = date("H:i");
    $closeFrom = date("H:i", strtotime(trim($_REQUEST['offtime_from'])));
    $closeTo = date("H:i", strtotime(trim($_REQUEST['offtime_to'])));
//    $closeFrom = "20:00:00";
//    $closeTo = "09:59:59";

    if ($currentTime < $closeFrom && $currentTime > $closeTo) {
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
    ]));
}

function Fn_insertChatDetailLog(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	unset($post_data['method']);

	$condParams = []; //mysql injection param
	$insert_fields = []; //prepared insert field
	$insert_values = []; //prepared insert value
	$errors = [];
	$count = 0;
	$err_count = 0;


	// sql insert data prepare
	$db_field = [
		'callid' => [
			'db_name' => 'callid',
			'len' => 20,
			'special_char_check' => true,
		],	
		'cCustomerName' => [
			'db_name' => 'name',
			'len' => 20,
			'special_char_check' => true
		],
		'cCustomerEmail' => [
			'db_name' => 'email',
			'len' => 35,
			'special_char_check' => false,
			'special_char_skip' => ['.'],
		],
		'cCustomerContactNumber' => [
			'db_name' => 'contact_number',
			'len' => 15,
			'special_char_check' => true
		],
		'cCustomerSubject' => [
			'db_name' => 'service_id',
			'len' => 2,
			'special_char_check' => true
		],
		'cCustomerVerify' => [
			'db_name' => 'verify_user',
			'len' => 1,
			'special_char_check' => true
		],
	];
	// var_dump($post_data);

	$count = 0;
	foreach ($post_data as $key => $value) {
		$insert_fields[$count] = $db_field[$key]['db_name'];
		$post_data_key = trim($post_data[$key]);
		$post_data_key = filter_var($post_data_key, FILTER_SANITIZE_STRING);
		$insert_values[$count] = "'".$post_data_key."'";
		
		$condParams[$count] = [
			"param" => $db_field[$key]['db_name'],
			"paramType" => "s",
			"paramValue" => $post_data_key,
			'specialCharCheck' => $db_field[$key]['special_char_check'],
			'paramLength' => $db_field[$key]['len'],
			'specialCharSkip' => isset($db_field[$key]['special_char_skip']) ? $db_field[$key]['special_char_skip'] : ''
		];

		$count++;
	}
	// var_dump($insert_fields);
	// var_dump($insert_values);
	// var_dump($condParams);
	// die('sfsfsf');

	if (!empty($insert_fields) && !empty($insert_values)) {
		// set disposition_id
		$insert_fields[] = 'disposition_id';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'disposition_id',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 4,
				'specialCharCheck' => false
			];

		// set tstamp
		$insert_fields[] = 'tstamp';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'tstamp',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 10,
				'specialCharCheck' => false
			];

		// set agent_id
		$insert_fields[] = 'agent_id';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'agent_id',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 4,
				'specialCharCheck' => false
			];

		// set note
		$insert_fields[] = 'note';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'note',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 255,
				'specialCharCheck' => false
			];

		// set url
		$insert_fields[] = 'url';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'url',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 100,
				'specialCharCheck' => false
			];

		// set url_duration
		$insert_fields[] = 'url_duration';
		$insert_values[] = "''";
		$condParams[] = [
				"param" => 'url_duration',
				"paramType" => "s", 
				"paramValue" => '',
				'paramLength' => 3,
				'specialCharCheck' => false
			];

		$sql = "INSERT INTO chat_detail_log(".implode(",", $insert_fields).") VALUES(".implode(",", $insert_values).")";
		// var_dump($sql);
		// var_dump($conn->query($sql));
		// die('chat_detail_log');

		if($conn->query($sql)){
			send_error(200, json_encode([
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_MSG => '',
			]));
		}else{
			send_error(200, json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => $validate['msg']
			]));
		}
	}

	send_error(200, json_encode([
		MSG_RESULT => false,
		MSG_TYPE => MSG_ERROR,
		MSG_MSG => 'Sorry! Your request is wrong!'
	]));
}

function Fn_createChatTokenSingleSign(){
	global $master_db, $conn;

	$post_data = $_REQUEST; // post data
	// add log
	$logData = [
		'PostData:'.json_encode($post_data),
	];
	log_text($logData, 'temp/webchat_token_log/');
	unset($post_data['method']);

	$cname = isset($post_data["cname"]) ? trim($post_data["cname"]) : "";
	$cnumber = isset($post_data["cnumber"]) ? trim($post_data["cnumber"]) : "";
	$cverify = isset($post_data["cverify"]) ? trim($post_data["cverify"]) : "";
	$page_id = isset($post_data["page_id"]) ? trim($post_data["page_id"]) : "";
	$ip = isset($post_data["ip"]) ? trim($post_data["ip"]) : "";

	if (empty($page_id)) send_error(401, "Unauthorized");
	//Validate Input

	$sql = "SELECT www_ip FROM " . $master_db.".chat_page WHERE page_id='$page_id'";	
	$result = $conn->queryOnUpdateDB($sql);
	if (is_array($result)) {
		while (1) {
			$token = hash_hmac('sha1', 'gChatT@785!~'.time(), 'vl6LE(WrV^=S%3T');
			$sql = "SELECT * FROM " . $master_db.".chat_token_single_sign WHERE token='$token' ";	
			$result = $conn->queryOnUpdateDB($sql);
			// var_dump($result);
			if(empty($result)){
				break;
			}
		}		
		// echo "INSERT INTO " . $master_db.".chat_token_single_sign SET token='$token', cname='$cname', generate_time=NOW(), ip='$ip', cnumber='$cnumber', cverify='$cverify' ";

		$conn->query("INSERT INTO " . $master_db.".chat_token_single_sign SET token='$token', cname='$cname', generate_time=NOW(), ip='$ip', cnumber='$cnumber', cverify='$cverify' ");
		send_error(200, $token);
	}
	send_error(401, "Unauthorized");
}

function Fn_checkSingleSignToken(){
	global $master_db, $conn;

	$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
	$token = isset($_GET["token"]) ? trim($_GET["token"]) : "";
	
	if (!preg_match('/^[a-z0-9]{40}$/i', $token)) send_error(401, "Unauthorized");
	
	$current_date_time = date('Y-m-d H:i:s', strtotime('now', strtotime('-15 min')));
	$sql = "SELECT * FROM " . $master_db.".chat_token_single_sign WHERE token='$token' AND generate_time > '".$current_date_time."'";
	// echo $sql;
	$result = $conn->queryOnUpdateDB($sql);
	if (is_array($result)) {
		$conn->query("DELETE FROM ".$master_db.".chat_token_single_sign WHERE generate_time < '".$current_date_time."' ");
		//$conn->query("DELETE FROM ".$master_db.".chat_token_single_sign WHERE token='$token' ");
		$data = isset($result[0]) ? json_encode($result[0]) : json_encode($result);
		send_error(200, $data);
	}
	send_error(401, "Unauthorized");
}

