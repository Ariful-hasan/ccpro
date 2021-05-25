<?PHP
// Call external API for POP UP
error_reporting(0);
global $debug, $log_file, $soap_debug;
$debug = 0; // 1 = echo; 2= file
$soap_debug = false;
$log_file = '/usr/local/gplexcc/regsrvr/engine/log.txt';
require_once ('db_conf.php');
db_conn ();
// id = 'AAE';
// data = get_api_data($id);
// rint_r($data);
//print_r($_SESSION ['set_values']);exit;
function get_existing_set_values($callid, $caller_id = '') {
	if (empty ( $callid ) && empty ( $caller_id ))
		return null;
	$existing_set_values = isset ( $_SESSION ['set_values'] ) ? $_SESSION ['set_values'] : array ();
	if (! isset ( $existing_set_values ['callid'] ) || ($existing_set_values ['callid'] != $callid && $existing_set_values ['callid'] != $caller_id))
		$existing_set_values = array ();
	$existing_set_values ['callid'] = $callid;
	
	if (! empty ( $caller_id ) && empty ( $callid )) {
		$existing_set_values ['callid'] = $caller_id;
	}
	
	return $existing_set_values;
}

function set_crm_value($callid, $name, $api, $apidata, $postedValue = '') {
	if (empty ( $callid )) return false;
	
	$existing_set_values = get_existing_set_values ( $callid );
	
	$value = '';
	$api_syn_pos = strpos ( $api, ':' );
	if ($api_syn_pos == false) {
		$value = isset ( $apidata [$api] ) ? $apidata [$api] : '';
		if (empty ( $value ) && empty ( $postedValue )) {
			$value = get_concated_data ( $api, $apidata );
		}
	} else {
		$cc = db_select ( "SELECT callerid, altid, agent_id, language, did, " . "caller_auth_by FROM calls_in WHERE callid='$callid'" );
		if ($cc) {
			$url = str_replace ( "<CLI>", $cc->callerid, $api );
			$url = str_replace ( "<DID>", $cc->did, $url );
			$url = str_replace ( "<AGENT>", $cc->agent_id, $url );
			
			if (is_array ( $existing_set_values )) {
				foreach ( $existing_set_values as $key => $val ) {
					$url = str_replace ( "<$key>", $val, $url );
				}
			}
			$api_data = call_extn_api ( $url );
			
			$value = $api_data;
		}
	}
	
	if (empty ( $value ) && ! empty ( $postedValue )) {
		$value = $postedValue;
	}
	
	$existing_set_values [$name] = $value;
	$_SESSION ['set_values'] = $existing_set_values;
}

function search_account($data, $callid) {
	$_SESSION ['set_values'] = null;
	$searchItems = new stdClass ();
	$template = db_select ( "SELECT api FROM skill_crm_template WHERE template_id='$data[template_id]'" );
	
	if (strlen ( $template->api ) < 6)
		return '';
		// if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
	$cc = null;
	// echo $callid;
	if (! empty ( $callid )) {
		
		// $cc = db_select("SELECT callerid, altid, agent_id, language, did, ".
		// "caller_auth_by FROM calls_in WHERE callid='$callid'");
		$cc = db_select ( "SELECT * FROM skill_crm_disposition_log WHERE callid='$callid'" );
		// echo '404|'.$callid;
	}
	
	if ($cc) {
		$url = $template->api;
		$url = str_replace ( "<CLI>", $cc->callerid, $template->api );
		$url = str_replace ( "<DID>", $cc->did, $url );
		$url = str_replace ( "<AGENT>", $cc->agent_id, $url );
		// $url = str_replace("<ID>", $cc->altid, $url);
		// $url = str_replace("<CITY1>",'CB'.substr($cc->altid,3,7),$url); //Fix for CityBank
		
		if (is_array ( $data )) {
			foreach ( $data as $key => $val ) {
				$url = str_replace ( "<$key>", $val, $url );
				$searchItems->$key = $val;
			}
			$searchItems->searchByCLI = $callid;
		}
		
		// echo '404|'.$url;
		// var_dump($url);exit;
		$api_data = call_extn_api ( $url );
		$account_id = $api_data [0] [0];
		if (! empty ( $account_id )) {
			
			// $existing_set_values['callid'] = $callid;
			// set_crm_value($callid, $sval->name, $sval->api, $api_data[0]);
			
			$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$data[template_id]' AND section_id=''" );
			
			if (is_array ( $set_values )) {
				foreach ( $set_values as $sval ) {
					$postedData = "";
					if (! empty ( $sval->api ) && strpos ( $sval->api, 'CONCAT' ) !== false) {
						$postedData = get_concated_data ( $sval->api, $data );
					} elseif (! empty ( $sval->api ) && strpos ( $sval->api, 'SUBSTR' ) !== false) {
						$postedData = get_substring_data ( $sval->api, $data );
					} else {
						$postedData = isset ( $data [$sval->api] ) ? $data [$sval->api] : "";
					}
					set_crm_value ( $callid, $sval->name, $sval->api, $api_data [0], $postedData );
				}
			}
			
			$existing_set_values = get_existing_set_values ( $callid );
			$existing_set_values ['s_items'] = serialize ( $searchItems );
			$_SESSION ['set_values'] = $existing_set_values;
			
			// $callid_a = explode('-', $callid);
			// $callid = $callid_a[0];
			// db_update("UPDATE calls_in SET altid='$account_id',caller_auth_by='' WHERE callid='$callid'");
			// Why change record id?????????
			// db_update("UPDATE skill_crm_disposition_log SET record_id='$account_id', caller_auth_by='' WHERE callid='$callid'");
			// echo "200|OK";
			return $account_id;
		}
	}
	return '';
	// echo "404|Agent not in service";
}
function search_account_custom($data, $callid, $caller_id) {
	$_SESSION ['set_values'] = null;
	$template = db_select ( "SELECT api FROM skill_crm_template WHERE template_id='$data[template_id]'" );
	
	if (strlen ( $template->api ) < 6 && empty ( $caller_id ))
		return '';
	$cc = null;
	
	if (! empty ( $callid )) {
		$cc = db_select ( "SELECT callerid, altid, agent_id, language, did, " . "caller_auth_by FROM calls_in WHERE callid='$callid'" );
	}
	
	if ($cc) {
		$url = str_replace ( "<CLI>", $cc->callerid, $template->api );
		$url = str_replace ( "<DID>", $cc->did, $url );
		$url = str_replace ( "<AGENT>", $cc->agent_id, $url );
		
		if (is_array ( $data )) {
			foreach ( $data as $key => $val ) {
				$url = str_replace ( "<$key>", $val, $url );
			}
		}
		
		$api_data = call_extn_api ( $url );
		$account_id = $api_data [0] [0];
		if (! empty ( $account_id )) {
			$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$data[template_id]' AND section_id=''" );
			
			if (is_array ( $set_values )) {
				foreach ( $set_values as $sval ) {
					set_crm_value ( $callid, $sval->name, $sval->api, $api_data [0] );
				}
			}
			
			$existing_set_values = get_existing_set_values ( $callid );
			$_SESSION ['set_values'] = $existing_set_values;
			db_update ( "UPDATE calls_in SET altid='$account_id',caller_auth_by='' WHERE callid='$callid'" );
			return $account_id;
		}
	}
	
	// Custom Steps Starts Here
	if (! empty ( $caller_id )) {
		$dbFields = getDBFieldNames (); // array('account_id', 'title', 'first_name', 'middle_name', 'last_name', 'DOB', 'house_no', 'street', 'landmarks', 'city', 'state', 'zip', 'country', 'home_phone', 'office_phone', 'mobile_phone', 'other_phone', 'fax', 'email', 'status');
		
		if (is_array ( $data )) {
			$searchItems = new stdClass ();
			$existing_set_values = get_existing_set_values ( $callid, $caller_id );
			foreach ( $data as $key => $val ) {
				if (in_array ( $key, $dbFields )) {
					$searchItems->$key = $val;
				}
				$searchItems->searchByCLI = $caller_id;
			}
			$existing_set_values ['s_items'] = $searchItems;
			$_SESSION ['set_values'] = $existing_set_values;
		}
		
		return $caller_id;
	}
	
	return '';
}

function add_all_accounts_to_skill_crm_record($all_account_ids, $status, $phone, $callid)
{
        if (!empty($all_account_ids)) {
                $acc_ids = explode(',', $all_account_ids);
                if (is_array($acc_ids)) {
                        foreach ($acc_ids as $crm_account_id) {
                                $crm_account_id = trim($crm_account_id);
                                if (!empty($crm_account_id)) {
                                        $existRecordId = db_select_one ( "SELECT record_id FROM skill_crm WHERE account_id='$crm_account_id'" );
                                        if (empty ( $existRecordId )) {
                                                $crm_record_id = time () . rand ( 1000, 9999 );
                                                db_update ( "INSERT INTO skill_crm SET record_id='$crm_record_id', account_id='$crm_account_id', status='$status', mobile_phone='$phone', last_callid='$callid'" );
                                        }
                                }
                        }
                }
        }
}

function caller_verified($account_id, $callid, $agent_id = '', $record_id = '', $caller_phone_no = '', $all_acc_ids='') {
	$crm_record_id = '';
	$crm_account_id = $account_id;
	if (! empty ( $callid )) {
		$existing_set_values = get_existing_set_values ( $callid );
		if (is_array ( $existing_set_values )) {
			foreach ( $existing_set_values as $key => $val ) {
				if ($key == 'ID') {
					$crm_account_id = $val;
					break;
				}
			}
		}
		//echo "####ACCID=$account_id, CALLID=$callid, AGID=$agent_id, RECID=$record_id, PHNNO=$caller_phone_no, CRM-RECID=$crm_record_id, CRM-ACCID=$crm_account_id";

		if (! empty ( $record_id ) && ! empty ( $crm_account_id )) {
			$crm_record_id = $record_id;
			$existRecordId = '';
			if (! db_select_one ( "SELECT record_id FROM skill_crm WHERE record_id='$record_id'" )) {
				$existRecordId = db_select_one ( "SELECT record_id FROM skill_crm WHERE account_id='$crm_account_id'" );
				if (! empty ( $existRecordId )) {
					db_update ( "UPDATE skill_crm SET account_id='$crm_account_id', last_callid='$callid', status='A' WHERE record_id='$existRecordId' LIMIT 1" );
				} else {
					db_update ( "INSERT INTO skill_crm SET record_id='$record_id', account_id='$crm_account_id', status='A', mobile_phone='$caller_phone_no', last_callid='$callid'" );
				}
			} else {
				db_update ( "UPDATE skill_crm SET account_id='$crm_account_id', last_callid='$callid', status='A' WHERE record_id='$record_id' LIMIT 1" );
			}
			
			if (empty ( $existRecordId )) {
				$existRecordId = db_select_one ( "SELECT record_id FROM skill_crm WHERE account_id='$crm_account_id'" );
			}
			if (! empty ( $existRecordId )) {
				$crm_record_id = $existRecordId;
				$callid_a = explode ( '-', $callid );
				$callid = $callid_a [0];
				if (! db_select_one ( "SELECT callid FROM skill_crm_disposition_log WHERE callid='$callid'" )) {
					db_update ( "INSERT INTO skill_crm_disposition_log SET record_id='$existRecordId', callid='$callid', tstamp=UNIX_TIMESTAMP(), log_date=CURDATE(), agent_id='$agent_id', caller_auth_by='A'" );
				} else {
					
					db_update ( "UPDATE skill_crm_disposition_log SET record_id='$existRecordId', agent_id='$agent_id', caller_auth_by='A' WHERE callid='$callid'" );
				}
                                add_all_accounts_to_skill_crm_record($all_acc_ids, 'A', $caller_phone_no, $callid);
				echo "200|OK";
				return;
			}
		} else {
			if (! empty ( $crm_account_id )) {
				$existRecordId = db_select_one ( "SELECT record_id FROM skill_crm WHERE account_id='$crm_account_id'" );
				if (! empty ( $existRecordId )) {
					db_update ( "UPDATE skill_crm SET account_id='$crm_account_id', last_callid='$callid', status='A' WHERE record_id='$existRecordId' LIMIT 1" );
				} else {
					$crm_record_id = time () . rand ( 1000, 9999 );
					if (! db_select_one ( "SELECT record_id FROM skill_crm WHERE record_id='$crm_record_id'" )) {
						db_update ( "INSERT INTO skill_crm SET record_id='$crm_record_id', account_id='$crm_account_id', status='A', mobile_phone='$caller_phone_no', last_callid='$callid'" );
					}
				}
				
				if (! empty ( $existRecordId )) {
					$crm_record_id = $existRecordId;
					$callid_a = explode ( '-', $callid );
					$callid = $callid_a [0];
					if (! db_select_one ( "SELECT record_id FROM skill_crm_disposition_log WHERE callid='$callid'" )) {
						db_update ( "INSERT INTO skill_crm_disposition_log SET record_id='$existRecordId', callid='$callid', tstamp=UNIX_TIMESTAMP(), log_date=CURDATE(), agent_id='$agent_id', caller_auth_by='A'" );
					} else {
						db_update ( "UPDATE skill_crm_disposition_log SET record_id='$existRecordId', agent_id='$agent_id', caller_auth_by='A' WHERE callid='$callid'" );
					}
					add_all_accounts_to_skill_crm_record($all_acc_ids, 'A', $caller_phone_no, $callid);
					echo "200|OK";
					return;
				}
			}
		}
	}
	
	if (! empty ( $crm_record_id )) {
		insert_disposition_log ( $crm_record_id, $callid, $agent_id, 'A' );
		add_all_accounts_to_skill_crm_record($all_acc_ids, 'A', $caller_phone_no, $callid);
		echo "200|OK";
		return;
	} elseif ($account_id && $callid) {
		// if(db_update("UPDATE calls_in SET caller_auth_by='A' WHERE callid='$callid' AND altid='$account_id'")) {
		$record_id = db_select_one ( "SELECT record_id FROM skill_crm WHERE account_id='$account_id'" );
		insert_disposition_log ( $record_id, $callid, $agent_id, 'A' );
		add_all_accounts_to_skill_crm_record($all_acc_ids, 'A', $caller_phone_no, $callid);
		echo "200|OK";
		// }
		return;
	}
	echo "404|Fail to verify the caller";
}
function insert_disposition_log($record_id, $callid, $agent_id, $caller_auth_by) {
	$callid_a = explode ( '-', $callid );
	$callid = $callid_a [0];
	if (! db_select_one ( "SELECT record_id FROM skill_crm_disposition_log WHERE callid='$callid'" )) {
		db_update ( "INSERT INTO skill_crm_disposition_log SET record_id='$record_id',callid='$callid',tstamp=UNIX_TIMESTAMP(),log_date=CURDATE(),agent_id='$agent_id',caller_auth_by='$caller_auth_by'" );
	} else {
		db_update ( "UPDATE skill_crm_disposition_log SET record_id='$record_id',callid='$callid',agent_id='$agent_id',caller_auth_by='$caller_auth_by' WHERE callid='$callid'" );
	}
}
function get_api_response($callid, $section, $request_params) {
	if (strlen ( $section->api ) < 6)
		return '';
		// if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
	$cc = null;
	$s_items_array = array ();
	
	// echo $callid;
	if (! empty ( $callid )) {
		$cc = db_select ( "SELECT record_id, agent_id, caller_auth_by FROM skill_crm_disposition_log WHERE callid='$callid'" );
		// $cc = db_select("SELECT callerid, altid, agent_id, language, did, caller_auth_by FROM calls_in WHERE callid='$callid'");
		if (! empty ( $request_params ['callerid'] ))
			$cc->callerid = $request_params ['callerid'];
	}
	
	if ($cc) {
		$url = str_replace ( "<AGENT>", $cc->agent_id, $section->api );
		$url = str_replace ( "<CLI>", $cc->callerid, $url );
		// $url = str_replace("<DID>", $cc->did, $url);
		// $url = str_replace("<ID>", $cc->altid, $url);
		// $url = str_replace("<CITY1>",'CB'.substr($cc->altid,3,7),$url); //Fix for CityBank
		
		$existing_set_values = get_existing_set_values ( $callid );
		$settedCLI = '';
		if (is_array ( $existing_set_values )) {
			foreach ( $existing_set_values as $key => $val ) {
				if ($key == 'CLI') {
					$settedCLI = $val;
					break;
				}
			}
			if (! empty ( $settedCLI )) {
				$cc->callerid = $settedCLI;
			}
		}
		
		if (is_array ( $existing_set_values )) {
			foreach ( $existing_set_values as $key => $val ) {
				$url = str_replace ( "<$key>", $val, $url );
			}
		}
		
		if (is_array ( $request_params )) {
			foreach ( $request_params as $key => $val ) {
				$url = str_replace ( "<$key>", $val, $url );
				if ($key != 'callid' && $key != 'section_id' && $key != 'template_id' && $key != 'callerid') {
					$s_items_array [$key] = $val;
				}
			}
		}
		
		$api_data = call_extn_api ( $url );
		$section_data = array ();
		$fields = db_select_array ( "SELECT field_label,field_key,field_mask,field_tab_id FROM skill_crm_template_fields WHERE template_id='$section->template_id' AND section_id='$section->section_id' ORDER BY sl" );
		
		if ($section->section_type == 'G') {
			
			foreach ( $fields as $field ) {
				$section_data ['column'] [] = $field->field_label;
				$section_data ['tab_id'] [] = $field->field_tab_id;
			}
			
			if (is_array ( $api_data )) {
				$i = 0;
				foreach ( $api_data as $val ) {
					$isShowRow = true;
					if (count ( $s_items_array ) > 0) {
						foreach ( $fields as $field ) {
							$field_key = $field->field_key;
							foreach ( $s_items_array as $pkey => $pval ) {
								if ($pkey == $field_key && ! empty ( $val [$field_key] ) && ! empty ( $pval )) {
									if (stripos ( $val [$field_key], $pval ) !== false) {
										$isShowRow = true;
										break;
									} else {
										$isShowRow = false;
										break;
									}
								}
							}
							if (! $isShowRow) {
								break;
							}
						}
					}
					
					if ($isShowRow) {
						foreach ( $fields as $field ) {
							$field_key = $field->field_key;
							if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
								$field_key = substr ( $field_key, 1 ) - 1;
							
							if (! empty ( $field->field_mask )) {
								// $val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
								$val [$field_key] = mask_value ( $val [$field_key], $field->field_mask );
							}
							$section_data [$i] [] = $val [$field_key];
						}
						++ $i;
					}
				}
			}
		}
		
		return $section_data;
	}
	
	return '';
}

function get_api_data($id) {
	global $SQL_debug, $debug, $cc;
	if (strlen ( $id ) > 10){
        $id = unserialize ( base64_decode ( $id ) );
    }
	$callid = isset ( $id ['callid'] ) ? $id ['callid'] : '';
	$id ['altid'] = ''; // making problem
	
	$callid_a = explode ( '-', $callid );
	$callid = $callid_a [0];
	$accountIdForDB = "";
	
	$caller_id = isset ( $id ['callerid'] ) ? $id ['callerid'] : '';
	$tabDefaultValue = isset ( $_REQUEST ['tabDefaultVal'] ) ? $_REQUEST ['tabDefaultVal'] : "";
	$isNewRecord = false;
	$paramKeys = array ( 'callid', 'template_id', 'skill_name', 'callerid', 'altid', 'agent_id', 'language', 'did', 'caller_auth_by' );
	
	if (count ( $id ) > 0) {
		foreach ( $id as $arrKey => $arrVal ) {
			if (! in_array ( $arrKey, $paramKeys )) {
				if ($arrKey == 'account') {
					$accountIdForDB = $arrVal;
				}
				set_crm_value ( $callid, $arrKey, '', array (), $arrVal );
				// City Bank Custom Set ACCID When IVR Send account
				set_crm_value ( $callid, 'ACCID', '', array (), $arrVal );
			}
		}
	}
	
	//var_dump($id);exit("Exited...");
	$data = new stdClass ();
	$lcrm = new stdClass ();
	$cc = new stdClass ();
	$cc->callerid = ! empty ( $id ['callerid'] ) ? $id ['callerid'] : $caller_id;
	$cc->agent_id = ! empty ( $id ['agent_id'] ) ? $id ['agent_id'] : "";
	$cc->altid = ! empty ( $id ['altid'] ) ? $id ['altid'] : "";
	$cc->language = ! empty ( $id ['language'] ) ? $id ['language'] : "";
	$cc->did = ! empty ( $id ['did'] ) ? $id ['did'] : "";
	$cc->caller_auth_by = ! empty ( $id ['caller_auth_by'] ) ? $id ['caller_auth_by'] : "";
	$cc->callid = ! empty ( $id ['callid'] ) ? $id ['callid'] : $callid;
	
	if ($callid) {
		// Get data from skill_crm_disposition_log
		$cc = db_select ( "SELECT record_id, agent_id, caller_auth_by FROM skill_crm_disposition_log WHERE callid='$callid'" );
		if ($debug) {
			print_r($cc);
		}
		if (empty ( $cc->record_id )) {
			$qtype = db_select_one("SELECT qtype FROM skill WHERE skill_id='$id[skill_id]'");
			if ($qtype == 'P') {
				$campaignId = db_select_one("SELECT campaign_id FROM campaign_profile WHERE skill_id='$id[skill_id]'");
				$cc = db_select ( "SELECT customer_id record_id, customer_id account_id, '' last_callid FROM campaign_dial_list WHERE dial_number='$caller_id' AND campaign_id='$campaignId'" );
			}
		}
		
		if (empty ( $cc->record_id ) && ! empty ( $accountIdForDB )) {
			$cc = db_select ( "SELECT record_id, account_id, last_callid FROM skill_crm WHERE account_id='$accountIdForDB'" );
		}
		
		if (! empty ( $cc->account_id )) {
			$cc->altid = $cc->account_id;
		} else {
			$cc->altid = empty ( $cc->record_id ) ? '' : db_select_one ( "SELECT account_id FROM skill_crm WHERE record_id='$cc->record_id'" );
		}
		$cc->callerid = ! empty ( $id ['callerid'] ) ? $id ['callerid'] : $caller_id;
		$cc->agent_id = ! empty ( $cc->agent_id ) ? $cc->agent_id : $id ['agent_id'];
		$cc->language = ! empty ( $id ['language'] ) ? $id ['language'] : "";
		$cc->caller_auth_by = ! empty ( $cc->caller_auth_by ) ? $cc->caller_auth_by : $id ['caller_auth_by'];
		$cc->callid = ! empty ( $id ['callid'] ) ? $id ['callid'] : $callid;
		$lcrm->record_id = ! empty ( $cc->record_id ) ? $cc->record_id : "";
		$lcrm->tpin_status = 'N';
		
		if (! $cc && empty ( $cc->callid )) {
			// Recent structure can give us the following values from param
			$cc = new stdClass ();
			$cc->callerid = ! empty ( $id ['callerid'] ) ? $id ['callerid'] : $caller_id;
			$cc->agent_id = ! empty ( $id ['agent_id'] ) ? $id ['agent_id'] : "";
			$cc->altid = ! empty ( $id ['altid'] ) ? $id ['altid'] : "";
			$cc->language = ! empty ( $id ['language'] ) ? $id ['language'] : "";
			$cc->did = ! empty ( $id ['did'] ) ? $id ['did'] : "";
			$cc->caller_auth_by = ! empty ( $id ['caller_auth_by'] ) ? $id ['caller_auth_by'] : "";
			$cc->callid = ! empty ( $id ['callid'] ) ? $id ['callid'] : $callid;
		}
		// echo $lcrm->record_id . ';';
		// echo "B";exit;
		// Local CRM Search
		if (empty ( $cc->record_id )) {
			// Customization start here
			if (! empty ( $accountIdForDB )) {
				$sql_query = "SELECT record_id, account_id, IF(TPIN='','N','Y') tpin_status FROM skill_crm ";
				$sql_query .= "where account_id='$accountIdForDB' AND (mobile_phone LIKE '%$caller_id%' OR home_phone LIKE '%$caller_id%' OR ";
				$sql_query .= "office_phone LIKE '%$caller_id%' OR other_phone LIKE '%$caller_id%') LIMIT 1";
			} else {
				$sql_query = "SELECT record_id, account_id, IF(TPIN='','N','Y') tpin_status FROM skill_crm ";
				$sql_query .= "where account_id LIKE '%$caller_id%' OR mobile_phone LIKE '%$caller_id%' OR home_phone LIKE '%$caller_id%' OR ";
				$sql_query .= "office_phone LIKE '%$caller_id%' OR other_phone LIKE '%$caller_id%' LIMIT 1";
			}
			// Customization ends here
			$lcrm = db_select ( $sql_query );
			
			if (! $lcrm && empty ( $cc->caller_auth_by )) {
				unset ( $lcrm );
				$lcrm = new stdClass ();
				$lcrm->record_id = time () . rand ( 1000, 9999 );
				$lcrm->tpin_status = 'N';
				$isNewRecord = true;
				
				// Stopped entry to skill_crm due to account id be unique
				// db_update("INSERT INTO skill_crm SET record_id='$lcrm->record_id',account_id='$cc->altid',status='A'");
				// db_update("INSERT INTO skill_crm SET record_id='$lcrm->record_id', status='A'");
			} elseif ($lcrm) {
				if (empty ( $cc ) && empty ( $cc->altid )) {
					$cc = new stdClass ();
				}
				$cc->altid = $lcrm->account_id;
				$data->data_record_id = ! empty ( $lcrm->record_id ) ? $lcrm->record_id : "";
			}
		}
	}
	
	$cc->callerid = empty ( $cc->callerid ) && ! empty ( $id ['callerid'] ) ? $id ['callerid'] : $cc->callerid;
	$cc->altid = empty ( $cc->altid ) && ! empty ( $id ['altid'] ) ? $id ['altid'] : $cc->altid;
	$cc->language = empty ( $cc->language ) && ! empty ( $id ['language'] ) ? $id ['language'] : $cc->language;
	$cc->did = empty ( $cc->did ) && ! empty ( $id ['did'] ) ? $id ['did'] : ! empty ( $cc->did ) ? $cc->did : '';
	$cc->callid = empty ( $cc->callid ) && ! empty ( $id ['callid'] ) ? $id ['callid'] : $cc->callid;
	$cc->agent_id = empty ( $cc->agent_id ) && ! empty ( $id ['agent_id'] ) ? $id ['agent_id'] : $cc->agent_id;
	
	// City Bank and Firoze Bhai forced to do this
	// if($cc->agent_id == $cc->altid){
	$cc->altid = "";
	// }
	if (! empty ( $accountIdForDB )) {
		$cc->altid = $accountIdForDB;
	}
	
	if (empty ( $lcrm->record_id )) {
		$lcrm = new stdClass ();
		// $lcrm->record_id = ''; Changed as record_id for table skill_crm_disposition_log must not empty
		$lcrm->record_id = time () . rand ( 1000, 9999 );
		$lcrm->tpin_status = 'N';
		$isNewRecord = true;
	}
	if (! empty ( $cc->caller_auth_by )) {
		$caller_auth_status = 'Y';
		if ($cc->caller_auth_by == 'I')
			$auth_by = 'through IVR';
		else
			$auth_by = "by Agent";
		$caller_auth_msg = "Caller authenticated $auth_by";
	} else {
		$caller_auth_status = '';
		$caller_auth_msg = "Caller is NOT authenticated";
	}
	// echo "$cc->altid , $lcrm->record_id, $cc->agent_id<br>";exit;
    /* disable the following line for Robi */
	/* insert_disposition_log ( $lcrm->record_id, $callid, $cc->agent_id, $cc->caller_auth_by ); */
	
	$template_id = $id ['template_id'];
	$data->error = '';
	
	if ($template_id) {
		$crm = db_select ( "SELECT title FROM skill_crm_template WHERE template_id='$template_id'" );
	}
	if (! $crm) {
		$data->error = 'Template not found';
		return $data;
	}
	
	if (empty ( $cc->language ))
		$cc->language = isset ( $id ['language'] ) ? $id ['language'] : '';
	if (empty ( $cc->language ))
		$cc->language = 'EN';
		// var_dump($cc);exit;
	$data->template_id = $id ['template_id'];
	$data->page_title = $crm->title;
	$data->skill_name = $id ['skill_name'];
	$data->caller_prefered_language = isset ( $cc->language ) ? $cc->language : '';
	$data->caller_auth_status = $caller_auth_status;
	$data->caller_auth_msg = $caller_auth_msg;
	$data->caller_auth_by = $cc->caller_auth_by;
	$data->account_id = $accountIdForDB;
	$data->crm_record_id = $isNewRecord ? '' : $lcrm->record_id;
	$data->callid = $callid;
	$data->caller_id = $caller_id;
	$data->agent_id = $cc->agent_id;
	$data->data_record_id = ! empty ( $data->data_record_id ) ? $data->data_record_id : '';
	$data->search_items = "";
	$data->tab_default_value = $tabDefaultValue;
	$data->call_id_full = ! empty ( $callid_a [0] ) && ! empty ( $callid_a [1] ) ? $callid_a [0] . '-' . $callid_a [1] : $callid;
	$multiple_ids = '';
	// $section = db_select_array("SELECT sl,section_id,section_title,section_type,api,is_editable,debug_mode,is_searchable,search_submit_label FROM skill_crm_template_section WHERE template_id='$template_id' AND active='Y' ORDER BY sl");
	$section = db_select_array ( "SELECT sl,section_id,section_title,section_type,api,is_editable,debug_mode,is_searchable,search_submit_label FROM skill_crm_template_section WHERE template_id='$template_id' AND active='Y' AND tab_id='' ORDER BY sl" );
	if (! $section) {
		$data->error = "No section record found on template_id=$template_id";
		return $data;
	}
	$secRecordObject = new stdClass ();
	// all API
	
	//if ($debug == 1) print_r($section);
	
	foreach ( $section as $row ) {
		$sectionAPI = ! empty ( $row->api ) && strlen ( $row->api ) >= 4 ? substr ( $row->api, 0, 4 ) : "";
		$secRecordId = "";
		$existing_set_values = get_existing_set_values ( $callid );
		
		if ($sectionAPI == "MSDB") {
			if (strlen ( $row->api ) > 4) {
				list ( $conn_method, $DSN, $sql_query ) = explode ( ':', $row->api );
				if ($DSN == 'LOCAL') {
					$squeryCond = "";
					$posStar = strpos ( $sql_query, '*' );
					if (isset ( $existing_set_values ['s_items'] ) && count ( $existing_set_values ['s_items'] ) > 0) {
						$dbFields = getDBFieldNames ();
						$searchedItems = unserialize ( $existing_set_values ['s_items'] );
						$squeryCond = "";
						$data->search_items = serialize ( $searchedItems );
						foreach ( $searchedItems as $itemKey => $itemVal ) {
							if (! empty ( $itemVal ) && in_array ( $itemKey, $dbFields )) {
								if (! empty ( $squeryCond )) {
									$squeryCond .= " OR ";
								}
								$squeryCond .= $itemKey . " LIKE '%$itemVal%'";
							}
						}
					}
					if (substr ( $sql_query, - 1 ) == ";") {
						$sql_query = trim ( $sql_query, ";" );
					}
					$sql_query = trim ( $sql_query );
					// is select all fields
					if ($posStar === false) {
						$sql_query = "SELECT record_id AS recid, " . substr ( $sql_query, - (strlen ( $sql_query ) - 7) );
					}
					
					if (! empty ( $squeryCond )) {
						$posWhere = strpos ( strtolower ( $sql_query ), 'where' );
						if ($posWhere === false) {
							$posTabName = strpos ( $sql_query, 'skill_crm' );
							$sql_query = substr ( $sql_query, 0, ($posTabName + 10) ) . " WHERE $squeryCond " . substr ( $sql_query, - (strlen ( $sql_query ) - $posTabName - 10) );
						} else {
							$sql_query = substr ( $sql_query, 0, ($posWhere + 6) ) . " ($squeryCond) OR " . substr ( $sql_query, - (strlen ( $sql_query ) - $posWhere - 6) );
						}
					}
					
					$settedCLI = '';
					if (is_array ( $existing_set_values )) {
						foreach ( $existing_set_values as $key => $val ) {
							if ($key == 'CLI') {
								$settedCLI = $val;
								break;
							}
						}
						if (! empty ( $settedCLI )) {
							$cc->callerid = $settedCLI;
						}
					}
					
					if (! empty ( $cc->callerid )) {
						$sql_query = str_replace ( "<CLI>", $cc->callerid, $sql_query );
						$sql_query = str_replace ( "<DID>", $cc->did, $sql_query );
					} else {
						$sql_query = str_replace ( "<DID>", $cc->did, $row->api );
					}
					$sql_query = str_replace ( "<AGENT>", $cc->agent_id, $sql_query );
					$sql_query = str_replace ( "<ID>", $cc->altid, $sql_query );
					
					if (is_array ( $existing_set_values )) {
						foreach ( $existing_set_values as $key => $val ) {
							$sql_query = str_replace ( "<$key>", $val, $sql_query );
						}
					}
					
					$api_result_array = db_select_array ( $sql_query, 1 );
					$api_result_array = json_decode ( json_encode ( $api_result_array ), true );
					$api_data [$row->section_id] = $api_result_array;
					$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'" );
					
					if (is_array ( $set_values )) {
						foreach ( $set_values as $sval ) {
							set_crm_value ( $callid, $sval->name, $sval->api, $api_data [$row->section_id] [0] );
						}
					}
					if (isset ( $api_data [$row->section_id] [0] ['record_id'] ) && ! empty ( $api_data [$row->section_id] [0] ['record_id'] )) {
						$secRecordId = $api_data [$row->section_id] [0] ['record_id'];
					} elseif (isset ( $api_data [$row->section_id] [0] ['recid'] ) && ! empty ( $api_data [$row->section_id] [0] ['recid'] )) {
						$secRecordId = $api_data [$row->section_id] [0] ['recid'];
					}
					// if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
					// $data->data_record_id = $api_data[$row->section_id][0]['record_id'];
					// }
				}
			} else {
				$api_result_array [0] = array (
						'record_id' => '' 
				);
				if (! empty ( $data->crm_record_id )) {
					$sql_query = "SELECT * FROM skill_crm where record_id='$data->crm_record_id' LIMIT 1";
					$api_result_array [0] = ( array ) db_select ( $sql_query );
				}
				
				if (empty ( $data->crm_record_id ) || empty ( $api_result_array [0] ['record_id'] )) {
					// Customization start here
					if (! empty ( $accountIdForDB )) {
						$sql_query = "SELECT * FROM skill_crm where account_id='$accountIdForDB' AND (mobile_phone LIKE '%$caller_id%' ";
						$sql_query .= "OR home_phone LIKE '%$caller_id%' OR office_phone LIKE '%$caller_id%' OR ";
						$sql_query .= "other_phone LIKE '%$caller_id%') LIMIT 1";
					} else {
						$sql_query = "SELECT * FROM skill_crm where account_id LIKE '%$caller_id%' OR mobile_phone LIKE '%$caller_id%' ";
						$sql_query .= "OR home_phone LIKE '%$caller_id%' OR office_phone LIKE '%$caller_id%' OR ";
						$sql_query .= "other_phone LIKE '%$caller_id%' LIMIT 1";
					}
					// Customization ends here
					// $api_result_array[0] = (array)db_select($sql_query);
					$api_result_array = db_select_array ( $sql_query, 1 );
					$api_result_array = json_decode ( json_encode ( $api_result_array ), true );
				}
				
				$api_data [$row->section_id] = $api_result_array; // call_extn_api($url);
				$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'" );
				
				if (is_array ( $set_values )) {
					foreach ( $set_values as $sval ) {
						set_crm_value ( $callid, $sval->name, $sval->api, $api_data [$row->section_id] [0] );
					}
				}
				if (isset ( $api_data [$row->section_id] [0] ['record_id'] ) && ! empty ( $api_data [$row->section_id] [0] ['record_id'] )) {
					$data->data_record_id = $api_data [$row->section_id] [0] ['record_id'];
				}
				$existing_set_values = get_existing_set_values ( $callid, $caller_id );
				$_SESSION ['set_values'] = $existing_set_values;
				
				if (isset ( $existing_set_values ['s_items'] ) && count ( $existing_set_values ['s_items'] ) > 0) {
					$dbFields = getDBFieldNames ();
					$searchedItems = unserialize ( $existing_set_values ['s_items'] );
					$squeryCond = "";
					if (isset ( $searchedItems->searchByCLI ) && $searchedItems->searchByCLI == $caller_id) {
						$data->search_items = serialize ( $searchedItems );
						foreach ( $searchedItems as $itemKey => $itemVal ) {
							if (! empty ( $itemVal ) && in_array ( $itemKey, $dbFields )) {
								if (! empty ( $squeryCond )) {
									$squeryCond .= " OR ";
								}
								$squeryCond .= $itemKey . " LIKE '%$itemVal%'";
							}
						}
					}
					if (! empty ( $squeryCond )) {
						// Customization start here
						if (! empty ( $accountIdForDB )) {
							$sql_query = "SELECT * FROM skill_crm where $squeryCond AND account_id='$accountIdForDB' LIMIT 1";
						} else {
							$sql_query = "SELECT * FROM skill_crm where $squeryCond LIMIT 1";
						}
						// Customization ends here
						// $api_result_array[0] = (array)db_select($sql_query);
						$api_result_array = db_select_array ( $sql_query, 1 );
						$api_result_array = json_decode ( json_encode ( $api_result_array ), true );
						$api_data [$row->section_id] = $api_result_array;
						
						$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'" );
						
						if (is_array ( $set_values )) {
							foreach ( $set_values as $sval ) {
								set_crm_value ( $callid, $sval->name, $sval->api, $api_data [$row->section_id] [0] );
							}
						}
						if (isset ( $api_data [$row->section_id] [0] ['record_id'] ) && ! empty ( $api_data [$row->section_id] [0] ['record_id'] )) {
							$data->data_record_id = $api_data [$row->section_id] [0] ['record_id'];
						}
					}
				}
			}
		} elseif (strlen ( $row->api ) < 6) {
			continue;
		} else {
			if ($row->debug_mode == 'Y') $SQL_debug = 1;
			else $SQL_debug = 0;
			
			$existing_set_values = get_existing_set_values ( $callid );
			$settedCLI = '';
			if (is_array ( $existing_set_values )) {
				foreach ( $existing_set_values as $key => $val ) {
					if ($key == 'CLI') {
						$settedCLI = $val;
						break;
					}
				}
				if (! empty ( $settedCLI )) {
					$cc->callerid = $settedCLI;
				}
			}
			
			if (! empty ( $cc->callerid )) {
				$url = str_replace ( "<CLI>", $cc->callerid, $row->api );
				$url = str_replace ( "<DID>", $cc->did, $url );
			} else {
				$url = str_replace ( "<DID>", $cc->did, $row->api );
			}
			$url = str_replace ( "<AGENT>", $cc->agent_id, $url );
			//$url = str_replace ( "<ID>", $cc->altid, $url ); //making problem in first load
			
			if (is_array ( $existing_set_values )) {
				foreach ( $existing_set_values as $key => $val ) {
					$url = str_replace ( "<$key>", $val, $url );
					if ($key == 'ID') {
						$cc->altid = $val;
					}
				}
			}
			$url = str_replace ( "<ID>", $cc->altid, $url );
			$url = str_replace ( "<CITY1>", 'CB' . substr ( $cc->altid, 3, 7 ), $url ); // Fix for CityBank
			msg ( "Template_id=$data->template_id ;  page title=$data->page_title ;  Account_id=$cc->altid", 1 );
			
			// Debug start from here
			$api_data [$row->section_id] = call_extn_api ( $url );
			//print_r($api_data [$row->section_id]);
			$set_values = db_select_array ( "SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'" );
			
			if (is_array ( $set_values )) {
				foreach ( $set_values as $sval ) {
					set_crm_value ( $callid, $sval->name, $sval->api, $api_data [$row->section_id] [0] );
					if ($sval->name == 'ID') {
					        foreach ($api_data [$row->section_id] as $_row_api_data) {
					                $multiple_ids .= isset ( $_row_api_data [$sval->api] ) ? $_row_api_data [$sval->api] : '';
					                $multiple_ids .= ',';
					        }
					}
				}
			}
			if (isset ( $api_data [$row->section_id] [0] ['record_id'] ) && ! empty ( $api_data [$row->section_id] [0] ['record_id'] )) {
				$data->data_record_id = $api_data [$row->section_id] [0] ['record_id'];
			}
			$existing_set_values = get_existing_set_values ( $callid );
			$_SESSION ['set_values'] = $existing_set_values;
		}
		$secRecordObject->{$row->section_id} = $secRecordId;
	}
	//print_r($api_data);exit;
	foreach ( $section as $row ) {
		unset ( $section_data );
		$fields = db_select_array ( "SELECT field_label,field_key,field_mask,save_in_session,field_tab_id,api FROM skill_crm_template_fields WHERE template_id='$template_id' AND section_id='$row->section_id' ORDER BY sl" );

		if (substr ( $row->api, 0, 3 ) == 'SEC')
			$api_data_key = substr ( $row->api, - 1 );
		else
			$api_data_key = $row->section_id;
		
		$isEditable = 'N';
		if ($row->api == "MSDB")
			$isEditable = 'Y';
		elseif (substr ( $row->api, 0, 4 ) == "MSDB" && ! empty ( $secRecordObject->{$row->section_id} ))
			$isEditable = 'Y';
		
		$data->section [$row->section_id] = new stdClass ();
		$data->section [$row->section_id]->section_title = $row->section_title;
		$data->section [$row->section_id]->section_type = $row->section_type;
		$data->section [$row->section_id]->is_editable = $isEditable == 'Y' ? 'Y' : $row->is_editable;
		$data->section [$row->section_id]->is_searchable = $row->is_searchable;
		$data->section [$row->section_id]->crm_rec_id = ! empty ( $secRecordObject->{$row->section_id} ) ? $secRecordObject->{$row->section_id} : "";
		
		if ($row->is_searchable == 'Y') {
			$data->section [$row->section_id]->search_submit_label = $row->search_submit_label;
			$data->section [$row->section_id]->filters = array ();
			// array('0' => $filter, '1' => $filter2);
			$filters = db_select_array ( "SELECT field_label,field_key,field_type FROM skill_crm_template_filters WHERE " . "template_id='$template_id' AND section_id='$row->section_id' ORDER BY sl" );
			if (is_array ( $filters )) {
				foreach ( $filters as $filter ) {
					$data->section [$row->section_id]->filters [] = $filter;
				}
			}
		}
		
		$existing_set_values = get_existing_set_values ( $callid );
		
		if ($row->section_type == 'F') { // Fields
			if (isset ( $row->section_node ) && ! empty ( $row->section_node )) { // Customized for xml data when added node
				$apiNodeParam = $row->section_node;
				$apiNodeArray = explode ( ":", $apiNodeParam );
				$apiDataFromNode = array ();
				$isFirstTime = true;
				foreach ( $apiNodeArray as $apiNodeKey ) {
					if ($isFirstTime) {
						$apiDataFromNode = $api_data [$api_data_key] [0] [$apiNodeKey];
						$isFirstTime = false;
					} else {
						$apiDataFromNode = $apiDataFromNode [$apiNodeKey];
					}
				}
				
				if (! empty ( $apiDataFromNode ) && count ( $apiDataFromNode ) > 0) {
					$i = 0;
					foreach ( $fields as $field ) {
						$section_data [$i] = new stdClass ();
						$section_data [$i]->field_label = $field->field_label;
						$field_key = $field->field_key;
						if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
							$field_key = substr ( $field_key, 1 ) - 1;
						$section_data [$i]->data_value = isset ( $apiDataFromNode [$field_key] ) ? $apiDataFromNode [$field_key] : '';
						if (! empty ( $field->field_mask )) {
							$section_data [$i]->data_value = mask_value ( $section_data [$i]->data_value, $field->field_mask );
						}
						++ $i;
					}
					$data->section [$row->section_id]->fields = $section_data;
				}
			} else {
				$i = 0;
				$apiProvData = array ();
				foreach ( $fields as $field ) {
					$section_data [$i] = new stdClass ();
					$section_data [$i]->field_label = $field->field_label;
					$field_key = $field->field_key;
					if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
						$field_key = substr ( $field_key, 1 ) - 1;
					$section_data [$i]->data_value = isset ( $api_data [$api_data_key] ) ? $api_data [$api_data_key] [0] [$field_key] : '';
					
					if (! empty ( $field->api )) {
						$fld_var = '';
						$_api = $field->api;
						$api_syn_pos = strpos ( $field->api, '[' );
						if ($api_syn_pos > 0) {
							$fld_var = substr ( $field->api, $api_syn_pos + 1, - 1 );
							$_api = substr ( $field->api, 0, $api_syn_pos );
						}
						
						if (! empty ( $fld_var )) {
							foreach ( $val as $_key => $_val ) {
								$_api = str_replace ( "<$_key>", $_val, $_api );
							}
							if (is_array ( $existing_set_values )) {
								foreach ( $existing_set_values as $key => $kval ) {
									$_api = str_replace ( "<$key>", $kval, $_api );
								}
							}
							
							$apiProvData = call_extn_api ( $_api );
						}
					}
					
					$api_syn_pos = strpos ( $field_key, ':' );
					if ($api_syn_pos > 0) {
						$fld_var = substr ( $field_key, 0, $api_syn_pos );
						$field_key = substr ( $field_key, $api_syn_pos + 1 );
						$section_data [$i]->data_value = isset ( $apiProvData [0] [$field_key] ) ? $apiProvData [0] [$field_key] : '';
					}
					
					if (! empty ( $field->field_mask )) {
						$section_data [$i]->data_value = mask_value ( $section_data [$i]->data_value, $field->field_mask );
					}
					$section_data [$i]->field_key = $field_key;
					$section_data [$i]->ftab_id = isset($field->field_tab_id) ? $field->field_tab_id : "";
					//$section_data [$i]->field_type = 'T';
					++ $i;
				}
				$data->section [$row->section_id]->fields = $section_data;
			}
		} elseif ($row->section_type == 'G') { // Grid
			foreach ( $fields as $field ) {
				$section_data ['column'] [] = $field->field_label;
				$section_data ['tab_id'] [] = $field->field_tab_id;
				$section_data ['settings'][] = empty($field->save_in_session) ? null : array(
						'save_in_session_field' => $field->field_key,
						'save_in_session_name' => $field->save_in_session
				);
			}
			
			if (isset ( $row->section_node ) && ! empty ( $row->section_node )) { // Customized for xml data when added node
				$apiNodeParam = $row->section_node;
				$apiNodeArray = explode ( ":", $apiNodeParam );
				$apiDataFromNode = array ();
				$isFirstTime = true;
				$isNeedBreak = false;
				$apiExtArrayNode = array ();
				
				foreach ( $apiNodeArray as $apiNodeKey ) {
					if ($isNeedBreak) {
						$apiExtArrayNode [] = $apiNodeKey;
					} else {
						if ($apiNodeKey == 'ARRAY') {
							$isNeedBreak = true;
						} else {
							if ($isFirstTime) {
								$apiDataFromNode = $api_data [$api_data_key] [0] [$apiNodeKey];
								$isFirstTime = false;
							} else {
								$apiDataFromNode = $apiDataFromNode [$apiNodeKey];
							}
						}
					}
				}
				
				if (! isset ( $apiDataFromNode [0] )) {
					$apiDataFromNode = array (
							$apiDataFromNode 
					);
				}
				
				if (! empty ( $apiDataFromNode ) && count ( $apiDataFromNode ) > 0) {
					$i = 0;
					$my_counter = 0;
					$_field_value = "";
					foreach ( $apiDataFromNode as $val ) {
						if (count ( $apiExtArrayNode ) > 0) {
							$apiExtNodeData = array ();
							foreach ( $apiExtArrayNode as $apiNodeKey ) {
								if ($apiNodeKey == $apiExtArrayNode [0]) {
									$apiExtNodeData = isset ( $val [$apiNodeKey] ) ? $val [$apiNodeKey] : array ();
								} else {
									$apiExtNodeData = isset ( $apiExtNodeData [$apiNodeKey] ) ? $apiExtNodeData [$apiNodeKey] : array ();
								}
							}
							if (count ( $apiExtNodeData ) > 0) {
								if (! isset ( $apiExtNodeData [0] )) {
									$tmpApiData = $apiExtNodeData;
									unset ( $apiExtNodeData );
									$apiExtNodeData = array ();
									$apiExtNodeData [0] = $tmpApiData;
									unset ( $tmpApiData );
								}
								foreach ( $apiExtNodeData as $val2 ) {
									if (is_array ( $val2 )) {
										foreach ( $fields as $field ) {
											$field_key = $field->field_key;
											
											if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
												$field_key = substr ( $field_key, 1 ) - 1;
											
											$_field_value = $val2 [$field_key];
											if (! empty ( $field->field_mask )) {
												$_field_value = mask_value ( $val2 [$field_key], $field->field_mask );
											}
											$section_data [$i] [] = $_field_value;
											
											if (!empty($field->save_in_session)) {
												$section_data [$i][$field_key] = encrypt_value($val2 [$field_key], $data->callid);
											}
										}
										++ $i;
									}
								}
							}
						} else {
							foreach ( $fields as $field ) {
								$field_key = $field->field_key;
								if (! empty ( $field->api )) {
									$fld_var = '';
									$_api = $field->api;
									$api_syn_pos = strpos ( $field->api, '[' );
									if ($api_syn_pos > 0) {
										$fld_var = substr ( $field->api, $api_syn_pos + 1, - 1 );
										$_api = substr ( $field->api, 0, $api_syn_pos );
									}
									
									if (! empty ( $fld_var )) {
										foreach ( $val as $_key => $_val ) {
											$_api = str_replace ( "<$_key>", $_val, $_api );
										}
										if (is_array ( $existing_set_values )) {
											foreach ( $existing_set_values as $key => $kval ) {
												$_api = str_replace ( "<$key>", $kval, $_api );
											}
										}
										
										$field_data [$fld_var] = call_extn_api ( $_api );
									}
								}
								if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
									$field_key = substr ( $field_key, 1 ) - 1;
								
								$api_syn_pos = strpos ( $field_key, ':' );
								if ($api_syn_pos > 0) {
									$fld_var = substr ( $field_key, 0, $api_syn_pos );
									$field_key = substr ( $field_key, $api_syn_pos + 1 );
									$val [$field_key] = isset ( $field_data [$fld_var] [0] [$field_key] ) ? $field_data [$fld_var] [0] [$field_key] : '';
								}
								
								$_field_value = $val [$field_key];
								
								if (! empty ( $field->field_mask )) {
									$_field_value = mask_value ( $val [$field_key], $field->field_mask );
								}
								$section_data [$i] [] = $_field_value;
								
								if (!empty($field->save_in_session)) {
									$section_data [$i][$field_key] = encrypt_value($val [$field_key], $data->callid);
								}
							}
						}
						++ $i;
					}
				}
			} else {
				if (isset ( $api_data [$api_data_key] )) {
					$i = 0;
					foreach ( $api_data [$api_data_key] as $val ) {
						foreach ( $fields as $field ) {
							$field_key = $field->field_key;
							if (! empty ( $field->api )) {
								$fld_var = '';
								$_api = $field->api;
								$api_syn_pos = strpos ( $field->api, '[' );
								if ($api_syn_pos > 0) {
									$fld_var = substr ( $field->api, $api_syn_pos + 1, - 1 );
									$_api = substr ( $field->api, 0, $api_syn_pos );
								}
								
								if (! empty ( $fld_var )) {
									foreach ( $val as $_key => $_val ) {
										$_api = str_replace ( "<$_key>", $_val, $_api );
									}
									
									if (is_array ( $existing_set_values )) {
										foreach ( $existing_set_values as $key => $kval ) {
											$_api = str_replace ( "<$key>", $kval, $_api );
										}
									}
									
									$field_data [$fld_var] = call_extn_api ( $_api );
								}
							}
							if (strtoupper ( substr ( $field_key, 0, 1 ) ) == 'V' && is_numeric ( substr ( $field_key, 1 ) ))
								$field_key = substr ( $field_key, 1 ) - 1;
							
							$api_syn_pos = strpos ( $field_key, ':' );
							if ($api_syn_pos > 0) {
								$fld_var = substr ( $field_key, 0, $api_syn_pos );
								$field_key = substr ( $field_key, $api_syn_pos + 1 );
								$val [$field_key] = isset ( $field_data [$fld_var] [0] [$field_key] ) ? $field_data [$fld_var] [0] [$field_key] : '';
							}
							
							$_field_value = $val [$field_key];
							if (! empty ( $field->field_mask )) {
								$_field_value = mask_value ( $val [$field_key], $field->field_mask );
							}
							$section_data [$i] [] = $_field_value;
							
							if (!empty($field->save_in_session)) {
								$section_data [$i][$field_key] = encrypt_value($val [$field_key], $data->callid);
							}
						}
						++ $i;
					}
				}
			}
			
			$data->section [$row->section_id]->grid = $section_data;
		} elseif ($row->section_type == 'T') { // TPIN
			if (db_select_one ( "SELECT LEFT(var1,2) FROM agents WHERE agent_id='$cc->agent_id' AND callid='$callid'" ) == 'GT')
				$lcrm->tpin_status = 'P';
			$data->section [$row->section_id]->tpin_status = $lcrm->tpin_status;
		}
	}
	if (! empty ( $_SESSION ['set_values'] ['s_items'] )) {
		$data->search_items = $_SESSION ['set_values'] ['s_items'];
	}
	
	//$multiple_ids = '';
	$existing_set_values = get_existing_set_values ( $callid );
	if (is_array ( $existing_set_values )) {
		$crm_account_id = '';
		foreach ( $existing_set_values as $key => $val ) {
			if ($key == 'ID') {
			        if (empty($crm_account_id)) {
				        $crm_account_id = $val;
				        $data->account_id = $crm_account_id;
				        break;
				}
				//$multiple_ids .= $val . ',';
				//break;
			}
		}
	}

	//print_r($data);
	$data->account_id_all = $multiple_ids;
	return $data;
}

function call_extn_api($url) {
    global $soap_debug;
	$api_result_array = array ();

    list($conn_method, $conn_name, $function, $find_array) = explode(':', $url); // HTTP URL is defined in Dadabase

	if ($conn_method == 'HTTP') {
        /*=====================================*/

        $http_service = db_select("SELECT url,credential,pass_credential,submit_method,submit_param,return_method,return_param FROM ivr_api WHERE conn_name='{$conn_name}' AND active='Y'");

        $HTTP_URL = $http_service->url;
        empty($HTTP_URL) ? msg("HTTP connection [$conn_name] not defined", 1) : "";


        list($function, $http_var) = explode('(', $function);
        $http_var = str_replace(")","",$http_var);

        $http_var = explode(',', $http_var);
        unset($http_param);
        $http_get_variable = "";  // Parameters to appended to url for get request

        if ($http_service->pass_credential == 'Y') {
            $credential = explode(',', $http_service->credential);
            foreach ($credential as $val) {
                list($key, $val) = explode('=', $val);
                $http_param [$key] = $val;
                $http_get_variable .= "&{$key}=$val";
            }
        }
        foreach ($http_var as $val) {
            $val = trim($val);
            if (strpos($val, '=')) {
                list($key, $val) = explode('=', $val);
                $key = trim($key);
                $val = trim($val);
                if (substr($val, 0, 1) == '<') {
                    $val = substr($val, 1, - 1);
                }
                $http_param [$key] = $val;

                $http_get_variable .= "&{$key}=$val";
            }
        }
        msg($http_param);
        $data = $http_param;


        /*===================================================*/


        if ($function == "GET") {
            $HTTP_URL = strpos($HTTP_URL, "?") == false ? $HTTP_URL . "?" . substr($http_get_variable, 1) : $HTTP_URL . $data;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $HTTP_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($function == "POST")
        {
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $result = trim(curl_exec($ch));
        if(curl_error($ch))
        {
            return $api_result_array;
        }
        curl_close($ch);

        $return_param = $http_service->return_param;

        $json = 0;
        if (is_string($result) && is_array(json_decode($result, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $result = json_decode($result);
            $json = 1;
        }
        elseif ($http_service->return_method == 'X') {

            $result = simplexml_load_string($result);
            if (!empty($return_param)) { // return param
                return get_object_vars($result->$return_param);
            }
            return get_object_vars($result);

        }else {
            $result = explode("\n", $result);
        }

        foreach ($result as $val) {
            $api_result_array [] = $json ?  obj2array($val) : explode(',', trim($val));;
        }
	} elseif($conn_method == 'ODBC') {
        list($conn_method,$DSN,$sql_query) = explode(':',$url);
        putenv("ODBCINI=/etc/odbc.ini");

        if($DSN=='LOCAL') {
            msg("DSN>>>>> $conn_method,$DSN,$sql_query");
            $odbc_conn = odbc_connect("DSN=$DSN;", '', '');             # DSN is define in file
        } elseif($DSN=='informix') {
            putenv("INFORMIXDIR=/opt/IBM/informix");
            msg("DSN>>>>> $conn_method,$DSN,$sql_query");
            $odbc_conn = odbc_connect("$DSN", '', '');          # DSN is define in file
        } else {
            $odbc_credential = db_select("SELECT credential,pass_credential FROM ivr_api WHERE conn_name='$DSN' AND active='Y'");
            if($odbc_credential->pass_credential=='Y') {
                $credential = explode(',', $odbc_credential->credential);
                $username=trim($credential[0]);
                $password=trim($credential[1]);
                $odbc_conn = odbc_connect("$DSN","$username","$password");         # DSN is define in file
                msg("DSN>>>>> $conn_method,$DSN,$username,$password,$sql_query");
            } else {
                $odbc_conn = odbc_connect("$DSN", '', '');
                msg("DSN>>>>> $conn_method,$DSN,'Password is in INI',$sql_query");
            }
        }
        if(!$odbc_conn) {
            msg("ODBC connection error DSN=$DSN",1);
            $api_result_array = array ();
            return $api_result_array;
        }

        $result = odbc_exec($odbc_conn,trim($sql_query));
        while($row = odbc_fetch_array($result)) {
            $api_result_array[] = obj2array($row);
        }

        odbc_free_result($result);
        odbc_close($odbc_conn);
    } elseif ($conn_method == 'MSQL') {
		list ( $conn_method, $DSN, $sql_query ) = explode ( ':', $url );
		$result = db_select_array ( $sql_query, 1 );
		// echo "<br>Query: ".$sql_query;
        if (is_array($result)){
            foreach ( $result as $row ) {
                $api_result_array [] = obj2array ( $row );
            }
        }else{
            $api_result_array = array ();
            return $api_result_array;
        }
		// echo "<br>$sql_query</br>";
		// print_r($api_result_array);
		// exit;
	} elseif ($conn_method == 'SOAP' || $conn_method == 'XML') {
		list ( $conn_method, $conn_name, $function, $find_array ) = explode ( ':', $url ); // WSDL-URL is defined in Dadabase
		$soap = db_select ( "SELECT url,credential,pass_credential,submit_method,submit_param,return_method,return_param FROM ivr_api WHERE conn_name='$conn_name' AND active='Y'" );
		msg ( $soap );
		$WSDL_URL = $soap->url;
		if (! $WSDL_URL) {
			msg ( "SOAP conn [$conn_name] not defined", 1 );
			hangup ();
		}
		list ( $function, $soap_var ) = explode ( '(', $function );
		$soap_var = substr ( $soap_var, 0, - 1 );
		$soap_var = explode ( ',', $soap_var );
		unset ( $soap_param );
		if ($soap->pass_credential == 'Y') {
			$credential = explode ( ',', $soap->credential );
			foreach ( $credential as $val ) {
				list ( $key, $val ) = explode ( '=', $val );
				$soap_param [$key] = $val;
			}
		}
		foreach ( $soap_var as $val ) {
			$val = trim ( $val );
			if (strpos ( $val, '=' )) {
				list ( $key, $val ) = explode ( '=', $val );
				$key = trim ( $key );
				$val = trim ( $val );
				if (substr ( $val, 0, 1 ) == '<') {
					$val = substr ( $val, 1, - 1 );
					$val = isset ( $cc->customerid [$val] ) ? $cc->customerid [$val] : '';
				}
				$soap_param [$key] = $val;
			} else {
				$soap_param [$val] = $cc->customerid [$val];
			}
		}
		msg ( $soap_param );
		if ($soap->submit_method == 'C') { // Class
			$soap_param = array2obj ( $soap_param );
		} elseif ($soap->submit_method == 'J') { // JSON
			$soap_param = json_encode ( $soap_param );
			$data->args0 = $soap_param;
		} elseif ($soap->submit_method == 'P') { // param
			$data = implode ( ',', $soap_param );
			// $data = array($soap_param);
		}
		
		if ($soap->submit_param) {
			$submit_param = $soap->submit_param;
			$data = new stdClass ();
			$data->$submit_param = $soap_param;
		} else { // Array
			$data = $soap_param;
		}
		
		if ($conn_method == 'SOAP') {
			ini_set("soap.wsdl_cache_enabled", 0);
			// SOAP connection and function call
			msg ( "Calling SOAP function $function" );
			// var_dump($data);//exit;
			try {
				$client = new SoapClient ( $WSDL_URL );
				msg ( $WSDL_URL );
				// echo $WSDL_URL . $function;
				// print_r($data);
				$result = $client->$function ( $data );
				if (is_soap_fault ( $result )) {
					echo "SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})";
				}
				unset ( $client );
				// var_dump($result);
			} catch ( SoapFault $fault ) {
				$error = 1;
				$log_file = '/usr/local/gplexcc/logs/crm_api_log.txt';
				if (is_writable($log_file)) {
					$msgData = json_encode ( $data );
					file_put_contents ( $log_file, date ( 'Y-m-d H:i:s' ) . " :: SOAP Request :: $function \n", FILE_APPEND );
					file_put_contents ( $log_file, "Request Param :: $msgData \n", FILE_APPEND );
					file_put_contents ( $log_file, "Returned ERROR: Code [" . $fault->faultcode . "] - " . $fault->faultstring . "\n", FILE_APPEND );
				}
				
				if ($soap_debug) {
					echo 'Returned the following ERROR: Code [' . $fault->faultcode . '] - ' . $fault->faultstring . "\nTrace:\n";
					echo $fault->getTraceAsString ();
				}
				$result = array ( NULL );
			}
		} else {
			// XML-RPC connection and function call
			$request = xmlrpc_encode_request ( $function, $data );
			$context = stream_context_create ( array (
					'http' => array (
							'method' => "POST",
							'header' => "Content-Type: text/xml",
							'content' => $request 
					) 
			) );
            $server_ip = empty($server_ip) ? $_SERVER['REMOTE_ADDR'] : $server_ip;
			$rpc_conn = file_get_contents ( "http://$server_ip/gplexcc/api/xml/gplex.php", false, $context );
			$result = xmlrpc_decode ( $rpc_conn );
			msg ( $result );
		}
		
		if ($soap->return_param) { // return param
			$return_param = $soap->return_param;
			$result = $result->$return_param;
		}
		
		// grab a spacific array defined at the end of the function;
		if ($find_array) {
			$find_array = explode ( ';', $find_array );
			if (is_array($find_array)){
                foreach ( $find_array as $find_ver ) {
                    $find_ver = trim($find_ver);
                    if (!empty($find_ver)){
                        $result = $result->$find_ver;
                    }
                }
            }else{
                $result = $result->$find_array;
            }
		}
		
		if ($soap->return_method == 'C') { // Class
			if (is_object ( $result )) $result = array ( $result );
		} elseif ($soap->return_method == 'J') { // JSON
			msg ( $result );
			$result = json_decode ( $result );
		} elseif ($soap->return_method == 'X') {
			$resultNormal = simplexml_load_string ( $result );
			$result = xml2array ( $resultNormal );
			//$p = xml_parser_create(); xml_parse_into_struct($p, $result, $result2, $index); xml_parser_free($p);
		}
		
		if (! is_array ( $result )) $result = array ( $result );
		
		$api_result_array = array ();
		if (is_object ( $result [0] ) || is_array ( $result [0] )) {
			foreach ( $result as $row ) {
				$api_result_array [] = obj2array ( $row, 1 );
			}
		} elseif ($soap->return_method == 'X') {
			$api_result_array [] = $result;
		} else {
			$api_result_array [] = array ( $result );
		}
	} else {
		msg ( "Method $conn_method not found.", 1 );
		hangup ();
	}

	// var_dump($function);
	
	return $api_result_array;
}

function array2obj($array) {
	foreach ( $array as $key => $value ) {
		if (is_array ( $value ))
			$array [$key] = array2obj ( $value );
	}
	return ( object ) $array;
}

// convert array or object to array[0] + array[$key]
function obj2array($obj, $i = 0) {
	// global $_tmp_ROW;
	if (! $i)
		$_tmp_ROW = array ();
	foreach ( $obj as $key => $val ) {
		if (is_object ( $val )) {
			obj2array ( $val, 1 );
		} else {
			$key = trim ( $key );
			$val = trim ( $val );
			$_tmp_ROW [] = $val;
			if (! is_numeric ( $key ) && strlen ( $key ) > 0)
				$_tmp_ROW [$key] = $val;
		}
	}
	return $_tmp_ROW;
}

function mask_value($value, $mask) {
	// ([0-9-]{2})([0-9-]*)([0-9]{4})
	$pattern = '/^' . $mask . '$/U';
	$value = preg_replace ( '/\s+/', '', $value );
	$matches = array ();
	preg_match ( $pattern, $value, $matches );
	return $matches [1] . preg_replace ( '([0-9])', '*', $matches [2] ) . $matches [3];
}

function get_concated_data($apiKey, $apiData) {
	$finalData = "";
	if (! empty ( $apiKey ) && strpos ( $apiKey, 'CONCAT' ) !== false) {
		$delimtrArr = array ( 'CONCAT', '(', ')' );
		$concatStr = str_replace ( $delimtrArr, "", $apiKey );
		$concatedArr = explode ( ",", $concatStr );
		foreach ( $concatedArr as $concatKey ) {
			$concatKey = trim ( $concatKey );
			if ($concatKey == "' '") {
				$finalData .= " ";
			} else {
				if (! empty ( $apiData [$concatKey] )) {
					$finalData .= $apiData [$concatKey];
				}
			}
		}
	}
	return trim ( $finalData );
}

function get_substring_data($apiKey, $apiData) {
	$finalData = "";
	if (! empty ( $apiKey ) && strpos ( $apiKey, 'SUBSTR' ) !== false) {
		$delimtrArr = array ( 'SUBSTR', '(', ')' );
		$concatStr = str_replace ( $delimtrArr, "", $apiKey );
		$concatedArr = explode ( ",", $concatStr );
		if (count ( $concatedArr ) == 2) {
			$substrKey = $concatedArr [0];
			$fullString = $apiData [$substrKey];
			$finalData = substr ( $fullString, $concatedArr [1], strlen ( $fullString ) );
		} elseif (count ( $concatedArr ) == 3) {
			$substrKey = $concatedArr [0];
			$fullString = $apiData [$substrKey];
			$finalData = substr ( $fullString, $concatedArr [1], $concatedArr [2] );
		}
	}
	return trim ( $finalData );
}

function encrypt_value($data, $callid)
{
	return openssl_encrypt($data, "AES-256-ECB", substr($callid, -16) . "421i5089@6587A^Z");
}

function getDBFieldNames(){
    $dbFields = array('account_id', 'title', 'first_name', 'middle_name', 'last_name', 'DOB', 'house_no', 'street', 'landmarks', 'city', 'state', 'zip', 'country', 'home_phone', 'office_phone', 'mobile_phone', 'other_phone', 'fax', 'email', 'status');
    return $dbFields;
}
/*
function hangup()
{
	
}
*/
