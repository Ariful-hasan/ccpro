<?php

// ##################### SDF functions ######################
function SDF_AuthPIN($param) {
	$account = $param [0];
	$PIN = $param [1];
	$imei = isset($param[2]) ? $param[2] : '';
	$device_id = isset($param[3]) ? $param[3] : '';
	$app_version = isset($param[4]) ? $param[4] : '21';
	$phone_model = isset($param[5]) ? $param[5] : '';
	$res = 'INVALID';

	// $sql = "SELECT IF(SHA2(CONCAT(RIGHT(record_id,4),'$PIN'),0)=TPIN,1,0) res,status,IF(LENGTH(TPIN)=32,TPIN,'') TPIN FROM skill_crm WHERE account_id='$account' LIMIT 1";
	$auth = db_select ( "SELECT IF(SHA2(CONCAT(RIGHT(record_id,4),'$PIN'),0)=TPIN,1,0) res,status,IF(LENGTH(TPIN)=32,TPIN,'') TPIN, TPIN_exp_date FROM skill_crm WHERE account_id='$account' LIMIT 1" );
	// debug_log("SDF: ". serialize($auth) . ' ==> ' . $sql);
	// Convert md5 hash to SHA2
	if ($auth->TPIN) {
		if (md5 ( $PIN ) == $auth->TPIN) {
			$auth->res = 1;
			db_update ( "UPDATE skill_crm SET TPIN=SHA2(CONCAT(RIGHT(record_id,4),'$PIN'),0) WHERE account_id='$account' LIMIT 1" );
		}		
	}
	
	if (strlen ( $auth->res ) > 0) {
		if ($auth->status != 'A') {
			$res = 'INACTIVE';
		}elseif($auth->TPIN_exp_date != '0000-00-00' && $auth->TPIN_exp_date < date('Y-m-d')){
			$res = 'EXPIRED';
		}elseif ($auth->res) {
			//check imei is verified or not
			if ($app_version > 21){
				$mobileInfo = db_select ( "SELECT account_id, device_id, app_version FROM user_mobile_imei WHERE account_id='$account' AND verified_by != '' AND (mobile_IMEI='$imei' OR device_id='$device_id') LIMIT 1" );
			}else {
				//Old Version App can not login now
				//$mobileInfo = db_select ( "SELECT account_id, device_id, app_version FROM user_mobile_imei WHERE account_id='$account' AND mobile_IMEI='$imei' AND verified_by != '' LIMIT 1" );
				$res = 'OLD_VER';
				$row = array ();
				$row [0] = $res;
				$row ['res'] = $res;
	
				return $row;
			}
			if (!empty($mobileInfo->account_id)) {
				$cid = isset($_SESSION['gCCAppClientID']) ? $_SESSION['gCCAppClientID'] : '';
	
				$sql = "INSERT INTO app_request_log SET tstamp=UNIX_TIMESTAMP(), ip='$_SERVER[REMOTE_ADDR]', client_id='$account', ".
				"request_id='login_info', imsi='$imei', country_code='', cell_id='', area_code='', latitude='', ".
				"longitude='', log_data='Device-ID=$device_id, APP-Version=$app_version, Phone-Model=$phone_model, IMEI=$imei'";
				db_update($sql);

				if (empty($mobileInfo->device_id) && !empty($device_id)) {
					db_update ( "UPDATE user_mobile_imei SET device_id='$device_id', app_version='$app_version', phone_model='$phone_model' WHERE account_id='$account' AND mobile_IMEI='$imei' LIMIT 1" );
				}elseif (!empty($mobileInfo->app_version) && !empty($app_version) && $mobileInfo->app_version < $app_version) {
					db_update ( "UPDATE user_mobile_imei SET app_version='$app_version' WHERE account_id='$account' AND mobile_IMEI='$imei' LIMIT 1" );
				}
				$res = 'SUCCESS';
			}else {
				$endTime = time();
				$srtTime = strtotime("-30 minutes", $endTime);
				//Deleting old expired records
				db_update ( "DELETE FROM user_mobile_imei WHERE account_id='$account' AND verified_by='' AND request_time NOT BETWEEN '$srtTime' AND '$endTime'" );
				//check is already requested for verification
				$isRequested = db_select ( "SELECT activation_code FROM user_mobile_imei WHERE account_id='$account' AND mobile_IMEI='$imei' AND verified_by='' AND request_time BETWEEN '$srtTime' AND '$endTime' LIMIT 1" );
				if (empty($isRequested->activation_code)){
					//Total imei already registered
					//$imeiCount = db_select ( "SELECT COUNT(account_id) AS totalIMEI FROM user_mobile_imei WHERE account_id='$account' AND verified_by != ''" );
					$imeiCount = db_select ( "SELECT COUNT(account_id) AS totalIMEI FROM user_mobile_imei WHERE account_id='$account' AND mobile_IMEI != ''" );

					if (!empty($imeiCount->totalIMEI) && $imeiCount->totalIMEI >= 3) {
						$res = 'EXCEED';
					}else {
						$res = substr($srtTime, -4);
						//Add new request for imei verification
						if ($app_version > 21){
							db_update ( "INSERT INTO user_mobile_imei SET account_id='$account', mobile_IMEI='$imei', activation_code='$res', request_time='$endTime', device_id='$device_id', app_version='$app_version', phone_model='$phone_model'" );
						}else {
							db_update ( "INSERT INTO user_mobile_imei SET account_id='$account', mobile_IMEI='$imei', activation_code='$res', request_time='$endTime'" );
						}
					}
				}else {
					$res = $isRequested->activation_code;
				}
			}			
		}
	}
	$row = array ();
	$row [0] = $res;
	$row ['res'] = $res;
	
	return $row;
}
function SDF_ChangePIN($param) {
	global $cc, $callid;
	$account = $param [0];
	$PIN = $param [1];
	$res = 'FAILED';
	// f(!$cc[$callid]->CA) {
	// # Caller is NOT authenticated
	// $res = 'NOAUTH';
	//
	// lse {
	if ($account == '*') {
		$record_id = db_select_one ( "SELECT record_id FROM skill_crm_disposition_log WHERE callid='$callid' AND caller_auth_by='A' LIMIT 1" );
		if ($record_id)
			$account_id = db_select_one ( "SELECT account_id FROM skill_crm WHERE record_id='$record_id' LIMIT 1" );
		
		if ($account_id) {
			$i = db_update ( "UPDATE skill_crm SET TPIN=SHA2(CONCAT(RIGHT(record_id,4),'$PIN'),0) WHERE account_id='$account_id' LIMIT 1" );
		} else {
			// No dispositon session is set.
			$res = 'NOSESSION';
		}
	} else {
		$i = db_update ( "UPDATE skill_crm SET TPIN=SHA2(CONCAT(RIGHT(record_id,4),'$PIN'),0) WHERE account_id='$account' LIMIT 1" );
	}
	if ($i == 1)
		$res = 'SUCCESS';
		//
	
	$row = array ();
	$row [0] = $res;
	$row [res] = $res;
	
	return $row;
}
function SDF_SetOTP($param) {
	$account = $param [0];
	$expire = 0 + $param [1];
	$len = 0 + $param [2];
	
	$t = time ();
	$account = 'mykey!#' . $account . '#@';
	$account = md5 ( $account );
	if ($len == 0)
		$len = 6;
	$PIN = substr ( mt_rand ( 100100, 989800 ), 0, $len );
	$salt = substr ( $t, - 4 );
	$t -= $PIN * 17;
	$t += $expire;
	
	db_update ( "DELETE FROM tb_mpf WHERE MPF1='$account' AND MPF6='OTP'" );
	$i = db_update ( "INSERT INTO tb_mpf SET MPF1='$account',MPF2='$t',MPF3='$salt',MPF4=SHA2('$salt$PIN',0),MPF6='OTP'" );
	if ($i == 1)
		$res = $PIN;
	else
		$res = 0;
	
	$row = array ();
	$row [0] = $res;
	$row [OTP] = $res;
	$row [res] = $res;
	
	return $row;
}
function SDF_AuthOTP($param) {
	$account = $param [0];
	$PIN = $param [1];
	$res = 'INVALID';
	$account = 'mykey!#' . $account . '#@';
	$account = md5 ( $account );
	
	$auth = db_select ( "SELECT IF(SHA2(CONCAT(MPF3,'$PIN'),0)=MPF4,1,0) res,MPF2 expire FROM tb_mpf WHERE MPF1='$account' AND MPF6='OTP'LIMIT 1" );
	if (strlen ( $auth->res ) > 0) {
		$t = time ();
		$t -= $PIN * 17;
		if ($auth->res && $auth->expire > $t) {
			$res = 'SUCCESS';
		}
	}
	$row = array ();
	$row [0] = $res;
	$row [res] = $res;
	
	return $row;
}
function SDF_SelectCRMfields($param) {
	$fields = str_replace ( '&', ',', $param [0] );
	$account_id = $param [1];
	
	if (strlen ( $account_id ) > 0) {
		$row = db_select ( "SELECT $fields FROM skill_crm WHERE account_id='$account_id' LIMIT 1" );
	}
	
	if (! $row) {
		$row = array ();
		$row [0] = 0;
		$row [res] = 0;
	} else {
		$row->res = 1;
	}
	
	return ( array ) $row;
}
function SDF_GeoNPA($param) {
	// find country_code from CLI or DID
	$did = $param [0];
	if (substr ( $did, 0, 1 ) == '1')
		$npa = substr ( $did, 1, 3 );
	else
		$npa = substr ( $did, 0, 3 );
	
	$res = db_select_one ( "SELECT country_code FROM cc_master.npa2country WHERE npa='$npa' LIMIT 1" );
	$row = array ();
	$row [0] = $res;
	$row [res] = $res;
	
	return $row;
}
function SavePushNote($app_ver, $push_id) {
	if (! empty ( $app_ver ) && ! empty ( $push_id )) {
		$res = db_update ( "INSERT INTO dbl_push_note SET app_ver='$app_ver', push_id='$push_id'" );
		if ($res) {
			return true;
		}
	}
	return false;
}

function cleanInputParams(){
	$preg = "/\-\-|[;'\"]|eval|cast\s*\(|base64_decode|gzinflate|str_rot13|xor|sleep|select|schema|sysdate|server|cookie|session|x_forward_for|union|column_name| OR |IFnulL|javascript/i";

	foreach ($_GET as &$value){
		if(!empty($value)){
			if(is_string($value)){
				$value = preg_replace($preg, "", $value);
				$value = mysqlEscapeMimic($value);
			}elseif(is_array($value)){
				foreach ($value as &$v){
					$v = preg_replace($preg, "", $v);
					$v = mysqlEscapeMimic($v);
				}
			}
		}
	}
}

function cleanRequestedData($params){
	$preg = "/\-\-|[;'\"]|eval|cast\s*\(|base64_decode|gzinflate|str_rot13|xor|sleep|select|schema|sysdate|server|cookie|session|x_forward_for|union|column_name| OR |IFnulL|javascript/i";

	if(is_array($params)){
	foreach ($params as &$value){
		if(!empty($value)){
			if(is_string($value)){
				$value = preg_replace($preg, "", $value);
				$value = mysqlEscapeMimic($value);
			}elseif(is_array($value)){
				foreach ($value as &$v){
					$v = preg_replace($preg, "", $v);
					$v = mysqlEscapeMimic($v);
				}
			}
		}
	}
	}
}

function mysqlEscapeMimic($inputValue) {
	//Used as mysql_real_escape_string alternative
	if(!empty($inputValue) && is_string($inputValue)) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inputValue);
	}
	
	return $inputValue;
}