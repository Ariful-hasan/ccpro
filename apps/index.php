<?php 
//error_reporting(E_ALL ^ E_DEPRECATED);
//ini_set("display_errors", 1);
error_reporting(0);
if(function_exists('date_default_timezone_set')) date_default_timezone_set("Asia/Dhaka");
if (!session_id()) session_start();
include('../crm_in_data.php');
include("sdf_functions.php");
include("encryption.php");

/*$debug = 0;
$a = get_response_for_request('login', array('TPIN'=>'9292', 'CardNumber'=>'01717567386', 'IMEI'=>'352975070638660'));
var_dump($a);
exit;*/

$debug_log = 1;
$param = isset($_REQUEST['param']) ? trim($_REQUEST['param']) : '';

/*
$headers = print_r(get_headers(), true);
file_put_contents('log/hea_'.time().rand(1000,9999) . '.txt', '"' . $headers . '"');
file_put_contents('log/req_'.time().rand(1000,9999) . '.txt', '"' . $param . '"');
*/
debug_log("New request");

if (strlen($param) > 10) {
	if (!isset($_SESSION['gCCAppKey'])) send_error_msg('Invalid request.');

	$hex_len = substr($param, 0, 1);
	$rand = substr($param, 1, $hex_len);
	$param = substr($param, $hex_len+1);
	$rand = hexdec($rand);
	$rand_pos = substr($rand, 0, 1);
	$rand_key = substr($rand, $rand_pos, 2);
	$ekey = "gpl" . $rand_key . substr($_SESSION['gCCAppKey'], 9, 5) . "!@#%^&";
	$query_string = empty($param) ? '' : gPlexEncryption::AESDecrypt($param, $ekey, '12gPlex:gCC!@#12');
	parse_str($query_string, $params);

	if (!is_array($params)) {
		send_error_msg('Invalid request.');
	}
	
	$locked_time = isset($_SESSION['gCCAppLockedTime']) ? $_SESSION['gCCAppLockedTime'] : 0;
	if ($locked_time > time()) {
		//set_access_lock($query_string);
		set_access_lock($params);
		send_error_msg("Account is locked", 9);
	}
	debug_log("New request 2: " . serialize($params));
	if (isset($params['page'])) {
		//User Data filtering option added
		cleanRequestedData($params);
		$a = get_response_for_request($params['page'], $params);
		//echo json_encode($a);
		add_response_log($params['page'], serialize($a));
		debug_log("Response: " . serialize($a));
		send_enc_output($a);
	} else if (isset($params['service'])) {
		if (isset($params['list'])) $a = get_service_list($params['list'], $params['accid'], $params);
		else $a = do_service_request($params['accid'], $params['service'], $params['cli'], $params);
		//echo json_encode($a);
		send_enc_output($a);
	} elseif (isset($params['save_push'])) {
		if(!empty($params['app_ver']) && !empty($params['push_id'])){
			$res = SavePushNote($params['app_ver'], $params['push_id']);
			$response = new stdClass();
			if($res){
				$response->status = 1;
			}else{
				$response->status = 0;
			}
			echo '200:' .json_encode($response);
			exit;
		}
	}
	exit;	
} else {
	if ($param == 'logout') {
		if (isset($_SESSION['gCCAppKey'])) unset($_SESSION['gCCAppKey']);
		exit;
	} else if ($param == 'pin') {
		$sesid = '!&*@f()' . session_id() . '#.' . rand(100, 999999);
		$sesid = md5($sesid);
		$dgt = array(1, 3, 5, 7, 9);
		$rand = rand(0, 4);
		$_SESSION['gCCAppKey'] = substr($sesid, 12, 20) . $dgt[$rand] . substr($sesid, 0, 11);
		exit($_SESSION['gCCAppKey']);
	}
}
/*
if (isset($_REQUEST['page'])) {
   $a = get_response_for_request($_REQUEST['page'], $_REQUEST);
   echo json_encode($a);
} else if (isset($_REQUEST['service'])) {
	$a = do_service_request($_REQUEST['accid'], $_REQUEST['service'], $_REQUEST['cli']);
	echo json_encode($a);
}
*/
exit;
//$a = get_response_for_request('login', array('TPIN'=>'1234', 'CardNumber'=>'4255'));
//$b = get_response_for_request('account_list', $a->request_values);
//$c = get_response_for_request('transactions', array('ACC_NUMBER'=>'1001'));


//var_dump[B($a);
//var_dump($b);
//var_dump($c);
function send_enc_output($out)
{       $a = json_encode($out);
        $rand = rand(10000, 99999);
        $rand2 = rand(1, 4);
        $rand = $rand2 . $rand;
        $rand_key = substr($rand, $rand2, 2);
        $ekey = "!@#%^&" . substr($_SESSION['gCCAppKey'], 4, 5) . "gpl" . $rand_key;
        //$ekey = "gplCC" . substr($_SESSION['gCCAppKey'], 9, 5) . "!@#%^&";
        $hex = dechex($rand);
        $resp = strlen($hex) . $hex . gPlexEncryption::AESEncrypt($a, $ekey, '12gPlex:gCC!@#12');
        //file_put_contents('log/resp_'.time().'.txt', $ekey . ' || ' . '12gPlex:gCC!@#12' . ' || ' . $a . ' || '.$resp);
        echo $resp;
        exit;
}

function set_save_value($request_id, $request_params, $apidata)
{
	$response_set_values = array();	
	$set_values = db_select_array("SELECT name, api FROM app_request_values WHERE request_id='$request_id'");
	
	if (is_array($set_values)) {
		foreach ($set_values as $sval) {
			//set_crm_value($callid, $sval->name, $sval->api, $api_data[0]);
			$value = '';
			$api_syn_pos = strpos($sval->api, ':');
			$constant_pos = strpos($sval->api, '"');
			if ($api_syn_pos == false) {
				if ($constant_pos === false) $value = isset($apidata[$sval->api]) ? $apidata[$sval->api] : '';
				else $value = trim($sval->api, '"');
				//$value = $constant_pos;
			} else {
					
				$url = $sval->api;
				
				if (is_array($request_params)) {
					foreach ($request_params as $key => $val) {
						$url = str_replace("<$key>", $val, $url);
					}
				}
				
				if (is_array($response_set_values)) {
					foreach ($response_set_values as $key => $val) {
						$url = str_replace("<$key>", $val, $url);
					}
				}

				$api_data = call_extn_api($url);				
				$value = $api_data;

			}
			$response_set_values[$sval->name] = $value;
			
		}
		if ($request_id == 'login') {
			$_SESSION['gCCAppClientID'] = '';
			if (isset($response_set_values['CID'])) {
				$_SESSION['gCCAppClientID'] = $response_set_values['CID'];
			} 
			
			if (empty($_SESSION['gCCAppClientID'])) {
				$sql = "UPDATE app_history_misslogin SET hit_date=NOW(), card_number='$request_params[CardNumber]', ".
					"ip='$_SERVER[REMOTE_ADDR]', imsi='$request_params[IMEI]' WHERE hit_date<SUBDATE(NOW(), INTERVAL 30 MINUTE) LIMIT 1";
				if (!db_update($sql)) {
					$sql = "INSERT INTO app_history_misslogin SET hit_date=NOW(), card_number='$request_params[CardNumber]', ".
						"ip='$_SERVER[REMOTE_ADDR]', imsi='$request_params[IMEI]'";
					db_update($sql);
				}
				
				set_access_lock($request_params);
				send_error_msg("Invalid TPIN or Card Number");
			}
		}
	}

	return $response_set_values;
}

function set_access_lock($request_params=array())
{
	$sql = "SELECT COUNT(hit_date) AS numrows FROM app_history_misslogin WHERE hit_date>SUBDATE(NOW(), INTERVAL 30 MINUTE) AND ".
		"ip='$_SERVER[REMOTE_ADDR]' AND imsi='$request_params[IMEI]'";
	$count = db_select_one($sql);
	if ($count >= 4) {
		$_SESSION['gCCAppLockedTime'] = time() + 1800;
		return;
	}
	
	if (!empty($request_params['CardNumber'])) {
		$sql = "SELECT COUNT(hit_date) AS numrows FROM app_history_misslogin WHERE hit_date>SUBDATE(NOW(), INTERVAL 30 MINUTE) AND ".
			"card_number='$request_params[CardNumber]'";
		$count = db_select_one($sql);
		if ($count >= 4) {
	        $_SESSION['gCCAppLockedTime'] = time() + 1800;
	        return;
		}
    }
        
    $_SESSION['gCCAppLockedTime'] = 0;
    return;
}

function send_error_msg($msg, $status=0)
{
	$response = new stdClass();
	$response->status = 0;
	if ($status != 0) $response->status = $status;
	$response->message = $msg;
	echo '404:' .json_encode($response);
	exit;
}

function do_service_request($account_id, $disposition_id, $cli, $request_params)
{
	$response = new stdClass();
	$response->status = 0;
	$response->message = 'Failed to request the service';
	
	$sql = "SELECT tstamp FROM ivr_service_request WHERE tstamp>(UNIX_TIMESTAMP()-600) AND account_id='$account_id' AND disposition_code='$disposition_id' LIMIT 1";
	$existing_req = db_select($sql);
	$status = 'Fail';
	if (!$existing_req) {
		$sql = "INSERT INTO ivr_service_request SET tstamp=UNIX_TIMESTAMP(), caller_id='$cli', account_id='$account_id', disposition_code='$disposition_id', source='A'";
		if (db_update($sql)) {
			$response->status = 1;
			$status = 'Success';
			$response->message = 'Requested the service successfully';
		}
	} else {
		$response->message = 'You have already requested the service';
		$status = 'Already Requested';
	}
	add_audit_log($request_params, 'service_request', "AccountID=$account_id;DispositionId=$disposition_id;Status=$status;");
	return $response;
}

function add_audit_log($request_params, $request_id, $data)
{
	$cid = isset($_SESSION['gCCAppClientID']) ? $_SESSION['gCCAppClientID'] : '';
	
	$sql = "INSERT INTO app_request_log SET tstamp=UNIX_TIMESTAMP(), ip='$_SERVER[REMOTE_ADDR]', client_id='$cid', ".
		"request_id='$request_id', imsi='$request_params[imsi]', country_code='$request_params[country]', ".
		"cell_id='$request_params[cellid]', area_code='$request_params[area_code]', latitude='$request_params[lat]', ".
		"longitude='$request_params[long]', log_data='$data'";
	db_update($sql);
}

function add_android_log($request_params, $request_id, $data)
{
	$cid = isset($_SESSION['gCCAppClientID']) ? $_SESSION['gCCAppClientID'] : '';
	
	$sql = "INSERT INTO app_request_log SET tstamp=UNIX_TIMESTAMP(), ip='$_SERVER[REMOTE_ADDR]', client_id='$cid', ".
		"request_id='$request_id', imsi='$request_params[imsi]', country_code='$request_params[country]', ".
		"cell_id='$request_params[cellid]', area_code='$request_params[area_code]', latitude='$request_params[lat]', ".
		"longitude='$request_params[long]', log_data='$data'";
	db_update($sql);

	$response = new stdClass();
	$response->status = 1;
	$response->msg = 'success';

	return $response;
}

function add_response_log($request_id, $data)
{
	$cid = isset($_SESSION['gCCAppClientID']) ? $_SESSION['gCCAppClientID'] : '';	
	$sql = "INSERT INTO app_response_log SET tstamp=UNIX_TIMESTAMP(), ip='$_SERVER[REMOTE_ADDR]', client_id='$cid', request_id='$request_id', log_data='$data'";
	db_update($sql);
}

function get_response_for_request($request_id, $request_params)
{
	$response = new stdClass();
	$response->status = 0;
	
	debug_log("Request for: ".$request_id);
	if ($request_id == 'login') {
		if(empty($request_params['IMEI']) || empty($request_params['CardNumber'])){
			return $response;
		}
	$deviceID = isset($request_params['device_id']) ? $request_params['device_id'] : '';
	$appVersion = isset($request_params['app_version']) ? $request_params['app_version'] : '21';
	$phoneModel = isset($request_params['phone_model']) ? $request_params['phone_model'] : '';
        //$adata = call_extn_api("MSQL:LOCAL:CALL AuthPIN('$request_params[CardNumber]','$request_params[TPIN]')");
        $adata = call_extn_api("XSQL:SDF:AuthPIN($request_params[CardNumber],$request_params[TPIN],$request_params[IMEI],$deviceID,$appVersion,$phoneModel)");
        //var_dump($adata);

        debug_log("AuthPIN($request_params[CardNumber],$request_params[TPIN],$request_params[IMEI],$deviceID,$appVersion,$phoneModel) Returned: " . serialize($adata));
        //mysql_close();
        //db_conn();
        if (is_array($adata) && isset($adata[0]['res']) && strlen($adata[0]['res']) == 4 && is_numeric($adata[0]['res'])){
        	$request_params['LPIN'] = $adata[0]['res'];
        	$request_params['mobno'] = $request_params['CardNumber'];
        	$request_params['smsmsg'] = $adata[0]['res'].' is your mobile IMEI verification code.';
        	get_specific_api_data('sendSMSPinVerify', $request_params);
        	
        	$response->status = $adata[0]['res'];
        	$response->sms_count = 0;
        	$smsSend = db_select("SELECT COUNT(account_id) AS total_sms FROM dbl_sms_tracker WHERE account_id='$request_params[CardNumber]' AND mobile_IMEI='$request_params[IMEI]' AND activation_code='".$adata[0]['res']."'");
        	if (isset($smsSend->total_sms)) {
        		$response->sms_count = $smsSend->total_sms;
        	}
        	return $response;
        }elseif (is_array($adata) && isset($adata[0]['res']) && $adata[0]['res'] == 'EXCEED'){
        	$response->status = 2;
        	return $response;
        }elseif (is_array($adata) && isset($adata[0]['res']) && $adata[0]['res'] == 'INACTIVE'){
            $response->status = 0;
            $response->message = "Account Inactive";
            return $response;
        }elseif (is_array($adata) && isset($adata[0]['res']) && $adata[0]['res'] == 'EXPIRED'){
            $response->status = 0;
            $response->message = "TPIN Expired! Please Contact with DBL Contact Center.";
            return $response;
        }elseif (is_array($adata) && isset($adata[0]['res']) && $adata[0]['res'] == 'OLD_VER'){
            $response->status = 0;
            $response->message = "You are using old version! Please update DHAKA BANK GO App.";
            return $response;
        }elseif (!is_array($adata) || !isset($adata[0]['res']) || $adata[0]['res'] != 'SUCCESS') {
		if (isset($adata[0]['res']) && $adata[0]['res'] == 'INVALID') {
			set_save_value($request_id, $request_params, array());
		}else{
        		return $response;
		}
        }
	        
	}elseif ($request_id == 'sendSMSPinVerify') {
		return get_specific_api_data($request_id, $request_params);
	}elseif ($request_id == 'saveRegBiller') {
		return save_data_to_db($request_id, $request_params);
	}elseif ($request_id == 'saveTransaction') {
		return save_data_to_db($request_id, $request_params);
	}elseif ($request_id == 'saveTransAccount') {
		return save_data_to_db($request_id, $request_params);
	}elseif (substr($request_id, 0, 4) == 'save'){
		return save_data_to_db($request_id, $request_params);
	}elseif ($request_id == 'sendOTPSMS') {
		return get_specific_api_data($request_id, $request_params);
	}elseif ($request_id == 'accTransferable') {
		return isAccTransferable($request_id, $request_params);
	}elseif ($request_id == 'getTopupTimeDiff') {
		return get_specific_api_data($request_id, $request_params);
	}elseif ($request_id == 'deleteRegPayee') {
		return delete_data_from_db($request_id, $request_params);
	}elseif ($request_id == 'deleteTransferAcc') {
		return delete_data_from_db($request_id, $request_params);
	}elseif ($request_id == 'addAndroidLog') {
		$logData = !empty($request_params['log_data']) ? $request_params['log_data'] : '-';
		return add_android_log($request_params, $request_id, $logData);
	}
	
	if (strlen($request_id) == 0) return $response;
	$request = null;
	$request = db_select("SELECT request_id, response_type, api FROM app_requests WHERE request_id='$request_id'");
	debug_log("API: " . serialize($request));
	if ($request) {
		$url = $request->api;
		
		$log_data = '';
		if (is_array($request_params)) {
			foreach ($request_params as $key => $val) {
				$new_url = str_replace("<$key>", $val, $url);
				if ($new_url!=$url) {
					$log_data .= "$key=$val;";
				}
				$url = $new_url;
			}
		}
		//echo $url;
		add_audit_log($request_params, $request_id, $log_data);
		//echo "API URL=".$url."===";
		debug_log("API Call: " . $url);

		$api_data = call_extn_api($url);
		//var_dump($api_data);

		if(isset($api_data[0]["ErrorCode"]) && (empty($api_data[0]["ErrorCode"]) || $api_data[0]["ErrorCode"] == '0')){
			$functionNames = array('topupBillFT', 'descoBillFT', 'dpdcBillFT', 'wasaBillFT', 'btclBillFT', 'titasBillFT');
			if (in_array($request_id, $functionNames)) {
				$isBillPayRequest = true;
				$transactionId = $request_params['trxid'];
				$transIdUpdate = $transactionId;
				$accountId = $request_params['AccNum'];
				$TransferType = "D";
				$payThrough = "F";
				$narration = $request_params['narr'];
				$narration = str_replace("#_#", ":", $narration);
				$transDateTime = date('Y-m-d H:i:s');
				$fromAccount = $request_params['dbacc'];
				
				$totalAmount = isset($request_params['amount']) ? $request_params['amount'] : 0;
				$transAccount = isset($request_params['cracc']) ? $request_params['cracc'] : "";
				$ftForwardType = isset($request_params['transferType']) ? $request_params['transferType'] : "FORWARD";
				
				$billType = "M";
				$billOrMobNo = "";
				if($request_id == 'topupBillFT'){
					$totalAmount = isset($request_params['tramount']) ? $request_params['tramount'] : 0;
					$transAccount = isset($request_params['cracc']) ? $request_params['cracc'] : ""; 
					$billOrMobNo = isset($request_params['topupNumber']) ? $request_params['topupNumber'] : "";
					$billType = "M";
					if (!empty($request_params['userAccount'])){
                        			$billType = "X";
                    			}
				}elseif($request_id == 'descoBillFT'){
					$totalAmount = isset($request_params['total_payable_amount']) ? $request_params['total_payable_amount'] : 0;
					$transAccount = isset($request_params['utility_account']) ? $request_params['utility_account'] : ""; 
					$billOrMobNo = isset($request_params['billno']) ? $request_params['billno'] : "";
					$billType = "E";
				}elseif($request_id == 'dpdcBillFT'){
					$totalAmount = isset($request_params['TOTAL_BILL_AMOUNT']) ? $request_params['TOTAL_BILL_AMOUNT'] : 0;
					$transAccount = isset($request_params['ACCOUNT_NUMBER']) ? $request_params['ACCOUNT_NUMBER'] : ""; 
					$billOrMobNo = isset($request_params['BILL_NUMBER']) ? $request_params['BILL_NUMBER'] : "";
					$billType = "D";
				}elseif($request_id == 'wasaBillFT'){
					$totalAmount = isset($request_params['net_bill']) ? $request_params['net_bill'] : 0;
					$transAccount = isset($request_params['account_no']) ? $request_params['account_no'] : ""; 
					$billOrMobNo = isset($request_params['billno']) ? $request_params['billno'] : "";
					$billType = "W";

					if(isset($request_params['vat'])){
						$totalAmount = $totalAmount + $request_params['vat'];
					}
					if(isset($request_params['other1'])){
						$totalAmount = $totalAmount + $request_params['other1'];
					}
				}elseif($request_id == 'btclBillFT'){
					$totalAmount = isset($request_params['BTCL_Amount']) ? $request_params['BTCL_Amount'] : 0;
					$transAccount = isset($request_params['PhoneNumber']) ? $request_params['PhoneNumber'] : ""; 
					$billOrMobNo = isset($request_params['BillNo']) ? $request_params['BillNo'] : "";
					$billType = "L";
				}elseif($request_id == 'titasBillFT'){
					$totalAmount = isset($request_params['topupNumber']) ? $request_params['topupNumber'] : 0;
					$transAccount = isset($request_params['PhoneNumber']) ? $request_params['PhoneNumber'] : "";
					$billOrMobNo = isset($request_params['billno']) ? $request_params['billno'] : "";
					$billType = "T";
				}
				
				$amount = $totalAmount;
				//$creditAmount = $ftForwardType == "REVERSE" ? $totalAmount : 0;
				//$debitAmount = $ftForwardType == "FORWARD" ? $totalAmount : 0;
				//$isBillPayRequest = $ftForwardType == "FORWARD" ? true : false;
				if($ftForwardType == "REVERSE"){
					$transIdUpdate = $transactionId;
					$transactionId = "REV@".$transactionId;
					$narration = $narration . "-REVERSE";
					$creditAmount = $totalAmount;
					$debitAmount = 0;
					$isBillPayRequest = false;
				}else{
					$creditAmount = 0;
					$debitAmount = $totalAmount;
					$isBillPayRequest = true;
				}

				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$ipAddr = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$ipAddr = $_SERVER['REMOTE_ADDR'];
				}

				if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ipAddr)) {
            				$ipAddr = '';
        			}
				
				if (!empty($transactionId) && !empty($accountId) && !empty($amount) && !empty($fromAccount)){
					$isSaved = true;
					$saveQuery = "INSERT INTO dbl_app_ledger SET transaction_id='$transactionId', account_id='$accountId', transfer_type='$TransferType', ";
					$saveQuery .= "pay_type='$payThrough', amount=$amount, cr_amount=$creditAmount, dr_amount=$debitAmount, narration='$narration', ";
					$saveQuery .= "trans_time='$transDateTime', ip='$ipAddr', trans_account='$transAccount', from_account='$fromAccount'";
					
					$rowAffected = db_update ( $saveQuery );

					if($ftForwardType == "FORWARD"){
						$saveQuery = "INSERT INTO dbl_bill_payment_log SET transaction_id='$transactionId', account_id='$accountId', bill_type='$billType', ";
						$saveQuery .= "amount=$amount, trans_time='$transDateTime', status='T', ip='$ipAddr', bill_mob_no='$billOrMobNo'";
						db_update ( $saveQuery );
					}else{
						$updateTime = date('Y-m-d H:i:s');
						$updateSql = "UPDATE dbl_bill_payment_log SET status='R', update_time='$updateTime' WHERE transaction_id='$transIdUpdate' AND account_id='$accountId' LIMIT 1";
                    				db_update ( $updateSql );
					}
				}
				
				if (!empty($billType) && !empty($accountId) && !empty($amount) && !empty($fromAccount)){
					if($isBillPayRequest){
						$isSaved = true;
						$saveQuery = "INSERT INTO dbl_billpay_requests SET bill_type='$billType', account_id='$accountId', bill_mob_no='$billOrMobNo', ";
						$saveQuery .= "bill_amount=$amount, request_time='$transDateTime', bill_account='$transAccount', request_type='P', transaction_id='$transactionId'";
					
						$rowAffected = db_update ( $saveQuery );
					}else{
						$isSaved = true;
						$saveQuery = "INSERT INTO dbl_billpay_requests SET bill_type='$billType', account_id='$accountId', bill_mob_no='$billOrMobNo', ";
						$saveQuery .= "bill_amount=$amount, request_time='$transDateTime', bill_account='$transAccount', request_type='F', transaction_id='$transactionId'";
					
						$rowAffected = db_update ( $saveQuery );
					}
				}
			}
		}elseif(isset($api_data[0]["status"]) || isset($api_data[0]["Status"]) || isset($api_data[0]["STATUS"]) || isset($api_data[0]["recharge_status"])) {
            		$billRequests = array('dpdcBillPay', 'wasaBillPay', 'descoBillPay', 'titasBillPay', 'bkashBillPay');
            		$rechargeRequests = array('initRecharge', 'initWimax');
            		$transactionId = $request_params['trxid'];
            		$accountId = $request_params['AccNum'];
			$updateTime = date('Y-m-d H:i:s');

            		if (in_array($request_id, $rechargeRequests)) {
                		if (!empty($api_data[0]["recharge_status"]) && $api_data[0]["recharge_status"] == '200'){
                    			$updateSql = "UPDATE dbl_bill_payment_log SET status='S', update_time='$updateTime' WHERE transaction_id='$transactionId' AND account_id='$accountId' LIMIT 1";
                    			db_update ( $updateSql );
                		}
            		}elseif (in_array($request_id, $billRequests)) {
                		if((!empty($api_data[0]["status"]) && $api_data[0]["status"] == '000') || (!empty($api_data[0]["Status"]) && $api_data[0]["Status"] == '000') || (isset($api_data[0]["STATUS"]) && $api_data[0]["STATUS"] == '000')) {
                    			$updateSql = "UPDATE dbl_bill_payment_log SET status='S', update_time='$updateTime' WHERE transaction_id='$transactionId' AND account_id='$accountId' LIMIT 1";
                    			db_update ( $updateSql );
                		}
            		}
        	}

		debug_log("API Response: " . serialize($api_data));
		$section_data = array();
		$fields = db_select_array("SELECT field_label,field_key,field_mask FROM app_request_fields WHERE request_id='$request->request_id' ORDER BY sl");

		$response->heads = array();

		debug_log("Response Fields: " . serialize($fields));		
		if (is_array($fields)) {
			foreach ($fields as $fld) {
				$response->heads[$fld->field_key] = $fld->field_label;
			}
		}
		
		
		//if ($request->response_type == 'G') {

			//foreach($fields AS $field) $section_data['column'][] = $field->field_label;

			if (is_array($api_data)) {
				$response->status = 1;
				$i = 0;
				foreach($api_data AS $val) {
					$section_data[$i] = new stdClass();
					foreach($fields AS $field) {
						$field_key = $field->field_key;
						if (strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
						 
						if (!empty($field->field_mask)) {
							$val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
						}
						$section_data[$i]->{$field_key} = $val[$field_key];
					}
					++$i;
				}
			}
		//}
		

		$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		$response->data = $section_data;
	}

	//var_dump($api_data);
	//var_dump($response);
	//Temporary Code to bypass fund transfer
	/*if($request_id == 'fund_trans' || $request_id == 'wasaBillFT' || $request_id == 'dpdcBillFT' || $request_id == 'descoBillFT' || $request_id == 'topupBillFT'){
		if(isset($response->data[0]->ErrorCode)){
			$response->data[0]->ErrorCode = "0";
			$response->data[0]->ErrorDesc = "";
			$response->data[0]->FTWS_FTID = "";
		}
	}*/
	//Temporary Code End
	
	return $response;
}

function get_service_list($list, $account_id, $request_params)
{
	$response = new stdClass();
	$response->status = 0;
	
	$list = strtoupper($list);
	
	if ($list == 'PENDING') {
	
	$sql = "SELECT FROM_UNIXTIME(s.tstamp, '%b %e, %Y %h:%i %p') AS request_time, caller_id, s.account_id, d.service_title, source ".
		"FROM ivr_service_request AS s, ivr_service_code AS d WHERE s.account_id='$account_id' AND s.disposition_code=d.disposition_code ".
		"ORDER BY tstamp DESC limit 5";
	$result = db_select_array($sql);
	
	$section_data = array();
	$response->heads = array('request_time'=>'Request Time', 'caller_id'=>'Phone Number', 'account_id'=>'Account ID', 'service_title'=>'Service', 'source'=>'Requested From');
	                
	if (is_array($result)) {
		$response->status = 1;
		$i = 0;
		foreach ($result as $row) {
			$section_data[$i] = $row;
			$section_data[$i]->account_id = mask_value($section_data[$i]->account_id, '([0-9-]{2})([0-9-]*)([0-9]{4})');
			if ($section_data[$i]->source == 'I') {
				$section_data[$i]->source = 'IVR';
			} else if ($section_data[$i]->source == 'A') {
				$section_data[$i]->source = 'App';
			}
			
			$i++;
		}
		$response->data = $section_data;
	}
	
	} else if ($list == 'SERVED') {
	
	$sql = "SELECT FROM_UNIXTIME(tstamp, '%b %e, %Y %h:%i %p') AS request_time, FROM_UNIXTIME(served_time, '%b %e, %Y %h:%i %p') AS served_time, ".
		"caller_id, account_id, service_title, source FROM ivr_service_request_log AS l, ivr_service_code AS d WHERE ".
		"l.account_id='$account_id' AND l.disposition_code=d.disposition_code AND status='S' ORDER BY served_time DESC limit 5";
	$result = db_select_array($sql);

	$section_data = array();
	$response->heads = array('request_time'=>'Request Time', 'served_time'=>'Served Time', 'caller_id'=>'Phone Number', 'account_id'=>'Account ID', 'service_title'=>'Service', 'source'=>'Requested From');

	if (is_array($result)) {
                $response->status = 1;
                $i = 0;
		foreach ($result as $row) {
			$section_data[$i] = $row;
			$section_data[$i]->account_id = mask_value($section_data[$i]->account_id, '([0-9-]{2})([0-9-]*)([0-9]{4})');
			if ($section_data[$i]->source == 'I') {
				$section_data[$i]->source = 'IVR';
			} else if ($section_data[$i]->source == 'A') {
				$section_data[$i]->source = 'App';
			}
			$i++;
		}
		$response->data = $section_data;
	}	
	
	}
	
	$log_data = "AccountID=$account_id;List=$list;";
	add_audit_log($request_params, 'service_list', $log_data);
	return $response;
}

function get_specific_api_data($request_id, $request_params)
{
	$response = new stdClass();
	$response->status = 0;

	if ($request_id == 'sendSMSPinVerify') {
		$smsSend = db_select("SELECT COUNT(account_id) AS total_sms FROM dbl_sms_tracker WHERE account_id='$request_params[CardNumber]' AND mobile_IMEI='$request_params[IMEI]' AND activation_code='$request_params[LPIN]'");

		debug_log("SMS Already Send: " . serialize($smsSend));
		//Deleting old expired records
		db_update ( "DELETE FROM dbl_sms_tracker WHERE account_id='$request_params[CardNumber]' AND mobile_IMEI='$request_params[IMEI]' AND activation_code != '$request_params[LPIN]'" );

		if (isset($smsSend->total_sms) && $smsSend->total_sms >= 3) {
			$response->msg = 'SMS already send 3 times';
			return $response;
		}
	}
	
	if (strlen($request_id) == 0) return $response;
	$request = null;
	$request = db_select("SELECT request_id, response_type, api FROM app_requests WHERE request_id='$request_id'");
	debug_log("API: " . serialize($request));
	
	if ($request) {
		$url = $request->api;		
		$log_data = '';
		if (is_array($request_params)) {
			foreach ($request_params as $key => $val) {
				$new_url = str_replace("<$key>", $val, $url);
				if ($new_url!=$url) {
					$log_data .= "$key=$val;";
				}
				$url = $new_url;
			}
		}
		
		add_audit_log($request_params, $request_id, $log_data);
		debug_log("API Call: " . $url);
		$api_data = call_extn_api($url);
		//var_dump($api_data);
		debug_log("API Response: " . serialize($api_data));

		$section_data = array();
		$fields = db_select_array("SELECT field_label,field_key,field_mask FROM app_request_fields WHERE request_id='$request->request_id' ORDER BY sl");

		$response->heads = array();
		debug_log("Response Fields: " . serialize($fields));		
		if (is_array($fields)) {
			foreach ($fields as $fld) {
				$response->heads[$fld->field_key] = $fld->field_label;
			}
		}

		if ($request_id == 'sendSMSPinVerify') {
			$section_data[0]->SendResult = 0;
			if((isset($api_data['SendResult']) && $api_data['SendResult'] > 0) || (isset($api_data->SendResult) && $api_data->SendResult > 0) || (isset($api_data[0]) && $api_data[0] > 0)){
				db_update ( "INSERT INTO dbl_sms_tracker SET account_id='$request_params[CardNumber]', mobile_IMEI='$request_params[IMEI]', activation_code='$request_params[LPIN]'" );
				$section_data[0]->SendResult = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}elseif ($request_id == 'sendOTPSMS') {
			$section_data[0]->SendResult = 0;
			if((isset($api_data['SendResult']) && $api_data['SendResult'] > 0) || (isset($api_data->SendResult) && $api_data->SendResult > 0) || (isset($api_data[0]) && $api_data[0] > 0)){
				$section_data[0]->SendResult = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}elseif ($request_id == 'getTopupTimeDiff') {
			$section_data[0]->timeDifference = 0;
			$response->status = 1;

			if(isset($api_data) && is_array($api_data) && count($api_data) > 0){
				$requestTime = "";
				$requestType = "";
				foreach($api_data as $dataRow){
					if(!empty($requestType) && $requestType != $dataRow["request_type"]){
						$seconds = strtotime($requestTime) - strtotime($dataRow["request_time"]);
						if($seconds > 60){
							$seconds = time() - strtotime($dataRow["request_time"]);
							$section_data[0]->timeDifference = $seconds;
							break;
						}
					}
					$requestTime = $dataRow["request_time"];
					$requestType = $dataRow["request_type"];

					if($requestType == 'P'){
						$seconds = time() - strtotime($requestTime);
						$section_data[0]->timeDifference = $seconds;
						break;
					}
				}
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}else{
			if (is_array($api_data)) {
				$response->status = 1;
				$i = 0;
				foreach($api_data AS $val) {
					$section_data[$i] = new stdClass();
					foreach($fields AS $field) {
						$field_key = $field->field_key;
						if (strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
					 
						if (!empty($field->field_mask)) {
							$val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
						}
						$section_data[$i]->{$field_key} = $val[$field_key];
					}
					++$i;
				}
			}

			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
			$response->data = $section_data;
		}
	}

	return $response;
}

function save_data_to_db($request_id, $request_params)
{
	$response = new stdClass();
	$response->status = 0;
	$isSaved = false;

	if (strlen($request_id) == 0) {
		return $response;
	}elseif ($request_id == 'saveRegBiller') {
		$bname_id = $request_params['bnameId'];
		$btype_id = $request_params['btypeId'];
		$reg_bname = $request_params['regBillName'];
		$ref_acc_no = isset($request_params['regBillAcc']) ? $request_params['regBillAcc'] : "";
		$account_no = $request_params['AccNum'];
		$topupLimit = isset($request_params['topUpLimit']) ? $request_params['topUpLimit'] : "";
		
		if (!empty($bname_id) && !empty($btype_id) && !empty($reg_bname) && !empty($account_no)){
			$isSaved = true;
			$isRowExist = db_select("SELECT reg_id FROM dbl_reg_biller WHERE bname_id='$bname_id' AND ref_acc_no='$ref_acc_no' AND account_id='$account_no' LIMIT 1");

			if ($isRowExist){
				$response->msg = 'Same Biller Name and Reference No Exits!';
				return $response;
			}else {
				$rowAffected = db_update ( "INSERT INTO dbl_reg_biller (bname_id, btype_id, reg_bname, ref_acc_no, account_id, topup_limit) VALUES ('$bname_id', '$btype_id', '$reg_bname', '$ref_acc_no', '$account_no', '$topupLimit')" );
				if ($rowAffected <= 0){
					$response->msg = 'Invalid Information to Register Biller!';
					return $response;
				}
			}
		}else {
			$response->msg = 'Failed to Save!';
			return $response;
		}
	}elseif ($request_id == 'saveTransAccount') {
		$account_no = isset($request_params['ACC_NUMBER']) ? $request_params['ACC_NUMBER'] : "";
		$payeeTitle = isset($request_params['title']) ? $request_params['title'] : "";
		$transferType = isset($request_params['tType']) ? $request_params['tType'] : "";
		$bankName = isset($request_params['bankName']) ? $request_params['bankName'] : "";
		$districtName = isset($request_params['district']) ? $request_params['district'] : "";
		$ref_acc_no = isset($request_params['transAccount']) ? $request_params['transAccount'] : "";		
		$branchName = isset($request_params['brchName']) ? $request_params['brchName'] : "";
		$routeNo = isset($request_params['routNo']) ? $request_params['routNo'] : "";
		
		if (!empty($account_no) && !empty($payeeTitle) && !empty($transferType) && !empty($ref_acc_no)){
			$isSaved = true;
			$isRowExist = db_select("SELECT ref_account_no FROM dbl_transfer_account WHERE account_id='$account_no' AND ref_account_no='$ref_acc_no' AND payee_title='$payeeTitle' LIMIT 1");
			if ($isRowExist){
				$response->msg = 'Same Title and Account No Exits!';
				return $response;
			}else {
				$rowAffected = db_update ( "INSERT INTO dbl_transfer_account (account_id, payee_title, ref_account_no, transfer_type, bank_name, district_name, branch_name, routing_no) VALUES ('$account_no', '$payeeTitle', '$ref_acc_no', '$transferType', '$bankName', '$districtName', '$branchName', '$routeNo')" );
				if ($rowAffected <= 0){
					$response->msg = "Same Account No Already Added!";
					return $response;
				}
			}
		}else {
			$response->msg = 'Failed to Save!';
			return $response;
		}
	}elseif ($request_id == 'saveTransaction') {
		$transactionId = $request_params['transId'];
		$accountId = $request_params['ACC_NUMBER'];
		$TransferType = !empty($request_params['tType']) ? $request_params['tType'] : "D";
		$payThrough = !empty($request_params['pType']) ? $request_params['pType'] : "F";
		$amount = $request_params['amount'];
		$creditAmount = $request_params['cr'];
		$debitAmount = $request_params['dr'];
		$narration = $request_params['narration'];
		$transDateTime = date('Y-m-d H:i:s');
		$transAccount = $request_params['toAcc'];
		$fromAccount = $request_params['fromAcc'];
		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ipAddr = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ipAddr = $_SERVER['REMOTE_ADDR'];
		}

		if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ipAddr)) {
            		$ipAddr = '';
        	}
		
		if (!empty($transactionId) && !empty($accountId) && !empty($amount) && !empty($transAccount) && !empty($fromAccount)){
			$isSaved = true;
			$saveQuery = "INSERT INTO dbl_app_ledger SET transaction_id='$transactionId', account_id='$accountId', transfer_type='$TransferType', ";
			$saveQuery .= "pay_type='$payThrough', amount=$amount, cr_amount=$creditAmount, dr_amount=$debitAmount, narration='$narration', ";
			$saveQuery .= "trans_time='$transDateTime', ip='$ipAddr', trans_account='$transAccount', from_account='$fromAccount'";
			
			$rowAffected = db_update ( $saveQuery );
			if ($rowAffected <= 0){
				$response->msg = 'Failed to Save!';
				return $response;
			}
		}else {
			$response->msg = 'Failed to Save!';
			return $response;
		}
	}
	
	$request = null;
	$request = db_select("SELECT request_id, response_type, api FROM app_requests WHERE request_id='$request_id'");
	debug_log("API: " . serialize($request));

	if ($request) {
		$api_data = array();
		$settedFieldArr = array();
		$sql_query = "";
		if ($request_id != 'saveRegBiller' && $request_id != 'saveTransaction' && $request_id != 'saveTransAccount') {
			$url = $request->api;
			
			if (!empty($url)){
				list ( $conn_method, $DSN, $sql_query ) = explode ( ':', $url );
				if (!empty($sql_query)){
					$fieldArr = explode("<", $sql_query);
					if (count($fieldArr)){
						foreach ($fieldArr as $fieldItem){
							$itemValue = strstr($fieldItem, '>', true);
							if ($itemValue) {
								$settedFieldArr[$itemValue] = $itemValue;
							}
						}
					}
				}
			}
			
			$log_data = '';
			if (is_array($request_params)) {
				$preg = "/\-\-|[;'\"]|eval|cast\s*\(|base64_decode|gzinflate|str_rot13|xor|sleep|select|schema|sysdate|server|cookie|session|x_forward_for|union|column_name| OR |IFnulL|javascript/i";

				foreach ($request_params as $key => $val) {

					if(!empty($val) && is_string($val)){
						$val = preg_replace($preg, "", $val);
					}

					$new_url = str_replace("<$key>", "'".$val."'", $sql_query);
					if ($new_url != $sql_query) {
						$log_data .= "$key=$val;";
					}
					$sql_query = $new_url;
					if (isset($settedFieldArr[$key])) {
						unset($settedFieldArr[$key]);
					}
				}
			}
			
			add_audit_log($request_params, $request_id, $log_data);
			debug_log("API Call: " . $url);
			//$api_data = call_extn_api($url);
			
			if (count($settedFieldArr) > 0){
				$allKeys = array_keys($settedFieldArr);
				$response->msg = "Value empty for the field ".$allKeys[0];
				return $response;
			}else {
				$rowAffected = db_update ( $sql_query );
				if ($rowAffected <= 0){
					$response->msg = 'Failed to Save!';
					return $response;
				}else {
					$responseData = array();
					$responseData['isSaved'] = 1;
					$api_data[0] = $responseData;
				}
			}

			//var_dump($api_data);
			debug_log("API Response: " . serialize($api_data));
		}

		$section_data = array();
		$fields = db_select_array("SELECT field_label,field_key,field_mask FROM app_request_fields WHERE request_id='$request->request_id' ORDER BY sl");

		$response->heads = array();
		debug_log("Response Fields: " . serialize($fields));
		if (is_array($fields)) {
			foreach ($fields as $fld) {
				$response->heads[$fld->field_key] = $fld->field_label;
			}
		}

		if ($request_id == 'saveRegBiller') {
			$section_data[0]->isSaved = 0;
			if($isSaved){
				$section_data[0]->isSaved = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}elseif ($request_id == 'saveTransAccount') {
			$section_data[0]->isSaved = 0;
			if($isSaved){
				$section_data[0]->isSaved = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}elseif ($request_id == 'saveTransaction') {
			$section_data[0]->isSaved = 0;
			if($isSaved){
				$section_data[0]->isSaved = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}else{
			if (is_array($api_data)) {
				$response->status = 1;
				$i = 0;
				foreach($api_data AS $val) {
					$section_data[$i] = new stdClass();
					foreach($fields AS $field) {
						$field_key = $field->field_key;
						$section_data[$i]->{$field_key} = $val[$field_key];
					}
					++$i;
				}
			}

			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
			$response->data = $section_data;
		}
	}

	return $response;
}

function delete_data_from_db($request_id, $request_params)
{
	$response = new stdClass();
	$response->status = 0;
	$isSaved = false;

	if (strlen($request_id) == 0) {
		return $response;
	}elseif ($request_id == 'deleteRegPayee') {
		$reg_id = $request_params['reg_id'];
		$account_no = $request_params['AccNum'];
		
		if (!empty($reg_id) && !empty($account_no)){
			$isSaved = true;
			$rowAffected = db_update ( "DELETE FROM dbl_reg_biller WHERE reg_id=$reg_id AND account_id='$account_no' LIMIT 1" );
			if ($rowAffected <= 0){
				$response->msg = 'Invalid Information to Delete Registered Biller!';
				return $response;
			}
		}else {
			$response->msg = 'Failed to Delete!';
			return $response;
		}
	}elseif ($request_id == 'deleteTransferAcc') {
		$account_no = $request_params['ACC_NUMBER'];
		$ref_acc_no = $request_params['ref_account_no'];
		$trans_type = isset($request_params['transferType']) ? $request_params['transferType'] : 'D';
		
		if (!empty($ref_acc_no) && !empty($account_no)){
			$isSaved = true;
			$rowAffected = db_update ( "DELETE FROM dbl_transfer_account WHERE account_id='$account_no' AND ref_account_no='$ref_acc_no' AND transfer_type='$trans_type' LIMIT 1" );
			if ($rowAffected <= 0){
				$response->msg = 'Invalid Information to Delete Transfer Account!';
				return $response;
			}
		}else {
			$response->msg = 'Failed to Delete!';
			return $response;
		}
	}
	
	$request = null;
	$request = db_select("SELECT request_id, response_type, api FROM app_requests WHERE request_id='$request_id'");
	debug_log("API: " . serialize($request));

	if ($request) {
		$api_data = array();
		$settedFieldArr = array();
		$sql_query = "";
		

		$section_data = array();
		$fields = db_select_array("SELECT field_label,field_key,field_mask FROM app_request_fields WHERE request_id='$request->request_id' ORDER BY sl");

		$response->heads = array();
		debug_log("Response Fields: " . serialize($fields));
		if (is_array($fields)) {
			foreach ($fields as $fld) {
				$response->heads[$fld->field_key] = $fld->field_label;
			}
		}

		if ($request_id == 'deleteRegPayee') {
			$section_data[0]->isDeleted = 0;
			if($isSaved){
				$section_data[0]->isDeleted = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}elseif ($request_id == 'deleteTransferAcc') {
			$section_data[0]->isDeleted = 0;
			if($isSaved){
				$section_data[0]->isDeleted = 1;
				$response->status = 1;
			}

			$response->data = $section_data;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}
	}

	return $response;
}

function isAccTransferable($request_id, $request_params){
	$response = new stdClass();
	$response->status = 0;
	$logData = "";
	
	if ($request_id == "accTransferable"){
		$account_id = $request_params['ACC_NUMBER'];
		$from_account = $request_params['FROM_ACC'];
		$transfer_type = $request_params['TTYPE'];
		$transfer_amount = $request_params['TAMOUNT'];
		$to_date = date('Y-m-d');
		
		//Default transaction limit
		//$daily_count = 6;
		//$daily_total = 50000;
		//$once_maximum = 50000;		
		$daily_count = 0;
		$daily_total = 0;
		$once_maximum = 0;
		if (!empty($transfer_type) && $transfer_type == 'D'){
			$daily_count = 5;
			$daily_total = 500;
			$once_maximum = 500;
		}
		
		if (!empty($account_id) && !empty($transfer_type)){
			$query = "SELECT dtg.num_trans_per_day, dtg.total_amount_per_day, dtg.max_trans_amount FROM dbl_transaction_group AS dtg ";
			$query .= "LEFT JOIN dbl_grouped_users AS dgu ON dgu.group_id=dtg.group_id WHERE dgu.account_id='$account_id' ";
			$query .= "AND dtg.transfer_type='$transfer_type' AND dtg.limit_type='D'";
			$request = db_select($query);
			debug_log("API: " . serialize($request));
			
			if ($request) {
				$daily_count = $request->num_trans_per_day;
				$daily_total = $request->total_amount_per_day;
				$once_maximum = $request->max_trans_amount;
			}
		}
			
		if ($transfer_amount > $once_maximum){
			$response->msg = 'Maximum transfer amount limit exceed!';
			return $response;
		}
		
		//$thistory = db_select_array("SELECT amount, cr_amount, dr_amount FROM dbl_app_ledger WHERE account_id='$account_id' AND DATE(trans_time)='$to_date' AND from_account='$from_account' AND transfer_type='$transfer_type' ORDER BY trans_time");
		$thistory = db_select("SELECT SUM(amount) AS sumtotal, COUNT(transaction_id) AS numrows FROM dbl_app_ledger WHERE account_id='$account_id' AND DATE(trans_time)='$to_date' AND from_account='$from_account' AND transfer_type='$transfer_type' ORDER BY trans_time");

		debug_log("Response Fields: " . serialize($thistory));
		$logData .= "SELECT amount, cr_amount, dr_amount FROM dbl_app_ledger WHERE account_id='$account_id' AND DATE(trans_time)='$to_date' AND from_account='$from_account' AND transfer_type='$transfer_type' ORDER BY trans_time";
		
		$trans_count = 0;
		$trans_total = 0;

		if ($thistory) {
			$trans_count = $thistory->numrows;
			$trans_total = $thistory->sumtotal;
		}
		$ts_total_added = $trans_total + $transfer_amount;

		if ($trans_count >= $daily_count){
			$response->msg = 'Daily transaction count limit exceed!';
			return $response;
		}elseif ($trans_total >= $daily_total){
			$response->msg = 'Daily total transaction limit exceed!';
			return $response;
		}elseif ($ts_total_added > $daily_total){
			$response->msg = 'Daily total transaction limit exceed!';
			return $response;
		}else {
			$response->status = 1;
			$responseData = array();
			$api_data = array();
			$responseData['isSuccess'] = 1;
			$api_data[0] = $responseData;
			$response->request_values = set_save_value($request_id, $request_params, $api_data[0]);
		}
	}
	return $response;
}

function debug_log($msg)
{
        global $debug_log;
        if ($debug_log > 0) {
                $file = 'log/app_log.txt';
                $msg = date("Y-m-d H:i:s") . " => " . $msg . "\n";
                try {
                if ($debug_log == 1) $a = file_put_contents($file, $msg);
                else $a = file_put_contents($file, $msg, FILE_APPEND);
                //var_dump($a);
                } catch ( Exception $e ) {
                $msg = "Exception " . $e->getCode() . " / " . $e->getMessage();
                echo "<p>$msg</p>";
                }
                $debug_log++;
        }
}
