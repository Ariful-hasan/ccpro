<?PHP
# Call external API for POP UP
#error_reporting(7);
error_reporting(0);
global $debug, $log_file, $soap_debug;
$debug = 0; // 1 = echo; 2= file
$soap_debug = false;
$log_file = '/usr/local/gplexcc/regsrvr/engine/log.txt';
require_once('db_conf.php');
db_conn();

function get_existing_set_values($callid, $caller_id='')
{
	if (empty($callid) && empty($caller_id)) return null;
	$existing_set_values = isset($_SESSION['set_values']) ? $_SESSION['set_values'] : array();
	if (!isset($existing_set_values['callid']) || ($existing_set_values['callid'] != $callid && $existing_set_values['callid'] != $caller_id)) $existing_set_values = array();
	$existing_set_values['callid'] = $callid;
	
	if (!empty($caller_id) && empty($callid)){
	    $existing_set_values['callid'] = $caller_id;
	}

	return $existing_set_values;
}

function set_crm_value($callid, $name, $api, $apidata)
{
	if (empty($callid)) return false;
	$existing_set_values = get_existing_set_values($callid);
	
	$value = '';
	$api_syn_pos = strpos($api, ':');
	if ($api_syn_pos == false) {
		$value = isset($apidata[$api]) ? $apidata[$api] : '';
	} else {
		$cc = db_select("SELECT callerid, altid, agent_id, language, did, ".
			"caller_auth_by FROM calls_in WHERE callid='$callid'");
		if ($cc) {
			$url = str_replace("<CLI>", $cc->callerid, $api);
			$url = str_replace("<DID>", $cc->did, $url);
			$url = str_replace("<AGENT>", $cc->agent_id, $url);
			
			if (is_array($existing_set_values)) {
				foreach ($existing_set_values as $key => $val) {
					$url = str_replace("<$key>", $val, $url);
				}
			}
			$api_data = call_extn_api($url);
			
			$value = $api_data;
		}
	}
	
	$existing_set_values[$name] = $value;
	$_SESSION['set_values'] = $existing_set_values;
}

function get_api_data($id, $secTabId) {
   global $SQL_debug,$cc;
   if(strlen($id) > 10) $id = unserialize(base64_decode($id));
   $callid = isset($id['callid']) ? $id['callid'] : '';
   
   $callid_a = explode('-', $callid);
   $callid = $callid_a[0];
   
   $caller_id = isset($id['callerid']) ? $id['callerid'] : '';
   $tabDefaultValue = isset($_REQUEST['tabDefaultVal']) ? $_REQUEST['tabDefaultVal'] : "";
   $isNewRecord = false;

   $lcrm = new stdClass();
   $cc = new  stdClass();
   $cc->callerid = !empty($id['callerid']) ? $id['callerid'] : $caller_id;
   $cc->agent_id = !empty($id['agent_id']) ? $id['agent_id'] : "";
   $cc->altid = !empty($id['altid']) ? $id['altid'] : $caller_id;
   $cc->language = !empty($id['language']) ? $id['language'] : "";
   $cc->did = !empty($id['did']) ? $id['did'] : "";
   $cc->caller_auth_by = !empty($id['caller_auth_by']) ? $id['caller_auth_by'] : "";
   $cc->callid = !empty($id['callid']) ? $id['callid'] : $callid;
   
   if($callid) {	  
      $cc = db_select("SELECT record_id,agent_id,caller_auth_by FROM skill_crm_disposition_log WHERE callid='$callid'");
      $cc->altid = empty($cc->record_id) ? '' : db_select_one("SELECT account_id FROM skill_crm WHERE record_id='$cc->record_id'");
      $lcrm->record_id = $cc->record_id;
      $lcrm->tpin_status = 'N';

      if(!$cc) {
          $cc = new  stdClass();
          $cc->callerid = !empty($id['callerid']) ? $id['callerid'] : $caller_id;
          $cc->agent_id = !empty($id['agent_id']) ? $id['agent_id'] : "";
          $cc->altid = !empty($id['altid']) ? $id['altid'] : $caller_id;
          $cc->language = !empty($id['language']) ? $id['language'] : "";
          $cc->did = !empty($id['did']) ? $id['did'] : "";
          $cc->caller_auth_by = !empty($id['caller_auth_by']) ? $id['caller_auth_by'] : "";
          $cc->callid = !empty($id['callid']) ? $id['callid'] : $callid;
      }      

		# Local CRM Search
		if(empty($cc->record_id)) {
			$lcrm = empty($cc->altid) ? null : db_select("SELECT record_id,IF(TPIN='','N','Y') tpin_status FROM skill_crm WHERE account_id='$cc->altid'");
			if (!$lcrm) {
				$sql_query = "SELECT record_id,account_id,IF(TPIN='','N','Y') tpin_status FROM skill_crm ";
				$sql_query .= "where account_id LIKE '%$caller_id%' OR home_phone LIKE '%$caller_id%' OR office_phone LIKE '%$caller_id%' OR ";
				$sql_query .= "mobile_phone LIKE '%$caller_id%' OR other_phone LIKE '%$caller_id%' LIMIT 1";
				$lcrm = db_select($sql_query);
			}
          
			if(!$lcrm && empty($cc->caller_auth_by)) {
				unset($lcrm);
				$lcrm->record_id = time() . rand(1000,9999);
				$lcrm->tpin_status = 'N';
				$isNewRecord = true;
				 if (empty($cc)) {
					$cc = new  stdClass();
					$cc->altid = $caller_id;
				}
			} else if ($lcrm) {
				if (empty($cc)) {
					$cc = new  stdClass();
				}
				$cc->altid = $lcrm->account_id;
			}
		}  
    }
   
   $cc->callerid = empty($cc->callerid) && !empty($id['callerid']) ? $id['callerid'] : $cc->callerid;
   $cc->altid = empty($cc->altid) && !empty($id['altid']) ? $id['altid'] : $cc->altid;
   $cc->language = empty($cc->language) && !empty($id['language']) ? $id['language'] : $cc->language;
   $cc->did = empty($cc->did) && !empty($id['did']) ? $id['did'] : $cc->did;
   $cc->callid = empty($cc->callid) && !empty($id['callid']) ? $id['callid'] : $cc->callid;
   $cc->agent_id = empty($cc->agent_id) && !empty($id['agent_id']) ? $id['agent_id'] : $cc->agent_id;
   
   if($cc->agent_id == $cc->altid){
	   $cc->altid = "";
   }
   if (empty($lcrm->record_id)) {
	   $lcrm = new stdClass();
	   $lcrm->record_id = time() . rand(1000,9999);
	   $lcrm->tpin_status = 'N';
	   $isNewRecord = true;
   }
   
   if (!empty($cc->caller_auth_by)) {
      $caller_auth_status = 'Y';
      if($cc->caller_auth_by == 'I') $auth_by = 'through IVR'; else $auth_by = "by Agent";
      $caller_auth_msg = "Caller authenticated $auth_by";
   } else {
      $caller_auth_status = '';
      $caller_auth_msg = "Caller is NOT authenticated";
   }
   
   $template_id = $id['template_id'];
   $data = new stdClass();
   $data->error = '';
   
   if($template_id) {
      $crm = db_select("SELECT title FROM skill_crm_template WHERE template_id='$template_id'");
   }
   if(!$crm) {
      $data->error = 'Template not found';
      return $data;
   }
   
   if (empty($cc->language)) $cc->language = isset($id['language']) ? $id['language'] : '';
   if (empty($cc->language)) $cc->language = 'EN';
   //var_dump($cc);exit;
   $data->template_id = $id['template_id'];
   $data->page_title = $crm->title;
   $data->skill_name = $id['skill_name'];
   $data->caller_prefered_language = isset($cc->language) ? $cc->language : '';
   $data->caller_auth_status = $caller_auth_status;
   $data->caller_auth_msg = $caller_auth_msg;
   $data->caller_auth_by = $cc->caller_auth_by;
   $data->account_id = $cc->altid;
   $data->crm_record_id = $isNewRecord ? '' : $lcrm->record_id;
   $data->callid = $callid;
   $data->caller_id = $caller_id;
   $data->agent_id = $cc->agent_id;
   $data->data_record_id = '';
   $data->search_items = new stdClass();
   $data->tab_default_value = $tabDefaultValue;
   
   $section = db_select_array("SELECT sl,section_id,section_title,section_type,api,is_editable,debug_mode,is_searchable,search_submit_label FROM skill_crm_template_section WHERE template_id='$template_id' AND active='Y' AND tab_id='$secTabId' ORDER BY sl");
   if(!$section) {
      $data->error = "No section record found on template_id=$template_id";
      return $data;
   }
   $secRecordObject = new stdClass();
   #Call API
   foreach($section AS $row) {
       $sectionAPI = !empty($row->api) && strlen($row->api) >= 4 ? substr($row->api, 0, 4) : "";
       $secRecordId = "";
       
   if ($sectionAPI == "MSDB"){
          if(strlen($row->api) > 4){
              list($conn_method, $DSN, $sql_query) = explode(':', $row->api);
              if($DSN == 'LOCAL') {
                  $squeryCond = "";
                  $posStar = strpos($sql_query, '*');
                  if (isset($existing_set_values['s_items']) && count($existing_set_values['s_items']) > 0){
                      $dbFields = getDBFieldNames();
                      $searchedItems = unserialize($existing_set_values['s_items']);
                      $squeryCond = "";
                      $data->search_items = serialize($searchedItems);
                      foreach ($searchedItems as $itemKey => $itemVal){
                          if (!empty($itemVal) && in_array($itemKey, $dbFields)){
                              if (!empty($squeryCond)){
                                  $squeryCond .= " OR ";
                              }
                              $squeryCond .= $itemKey." LIKE '%$itemVal%'";
                          }
                      }
                  }
                  if (substr($sql_query, -1) == ";"){
                      $sql_query = trim($sql_query, ";");
                  }
                  $sql_query = trim($sql_query);
                  //is select all fields
                  if ($posStar === false) {
                      $sql_query = "SELECT record_id AS recid, ".substr($sql_query, -(strlen($sql_query)-7));
                  }
                  
                  if (!empty($squeryCond)){
                      $posWhere = strpos(strtolower($sql_query), 'where');
                      if ($posWhere === false){
                          $posTabName = strpos($sql_query, 'skill_crm');
                          $sql_query = substr($sql_query, 0, ($posTabName+10)). " WHERE $squeryCond ".substr($sql_query, -(strlen($sql_query)-$posTabName-10));
                      }else {
                          $sql_query = substr($sql_query, 0, ($posWhere+6)). " ($squeryCond) OR ".substr($sql_query, -(strlen($sql_query)-$posWhere-6));
                      }
                  }

                  $existing_set_values = get_existing_set_values($callid);
                  $settedCLI = '';
                  if (is_array($existing_set_values)) {
                      foreach ($existing_set_values as $key => $val) {
                          if($key == 'CLI'){
                              $settedCLI = $val;
                              break;
                          }
                      }
                      if(!empty($settedCLI)){
                          $cc->callerid = $settedCLI;
                      }
                  }
                  
                  if(!empty($cc->callerid)){
                      $sql_query = str_replace("<CLI>", $cc->callerid, $sql_query);
                      $sql_query = str_replace("<DID>", $cc->did, $sql_query);
                  }else{
                      $sql_query = str_replace("<DID>", $cc->did, $row->api);
                  }
                  $sql_query = str_replace("<TAB_VALUE>", $tabDefaultValue, $sql_query);
                  $sql_query = str_replace("<AGENT>", $cc->agent_id, $sql_query);
                  $sql_query = str_replace("<ID>", $cc->altid, $sql_query);

                  if (is_array($existing_set_values)) {
                      foreach ($existing_set_values as $key => $val) {
                          $sql_query = str_replace("<$key>", $val, $sql_query);
                      }
                  }
                  
                  $api_result_array = db_select_array($sql_query, 1);
                  $api_result_array = json_decode(json_encode($api_result_array), true);                  
                  $api_data[$row->section_id] = $api_result_array;
                  $set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'");
                   
                  if (is_array($set_values)) {
                      foreach ($set_values as $sval) {
                          set_crm_value($callid, $sval->name, $sval->api, $api_data[$row->section_id][0]);
                      }
                  }
                  if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
                      $secRecordId = $api_data[$row->section_id][0]['record_id'];
                  }elseif (isset($api_data[$row->section_id][0]['recid']) && !empty($api_data[$row->section_id][0]['recid'])){
                      $secRecordId = $api_data[$row->section_id][0]['recid'];
                  }
                  //if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
                      //$data->data_record_id = $api_data[$row->section_id][0]['record_id'];
                  //}
              }
          }else {
              $api_result_array[0] = array('record_id' => '');
              if (!empty($data->crm_record_id)) {
                  $sql_query = "SELECT * FROM skill_crm where record_id='$data->crm_record_id' LIMIT 1";
                  $api_result_array[0] = (array)db_select($sql_query);              
              }
              
              if (empty($data->crm_record_id) || empty($api_result_array[0]['record_id'])){
                  $sql_query = "SELECT * FROM skill_crm where account_id LIKE '%$caller_id%' OR mobile_phone LIKE '%$caller_id%' ";
                  $sql_query .= "OR home_phone LIKE '%$caller_id%' OR office_phone LIKE '%$caller_id%' OR ";
                  $sql_query .= "other_phone LIKE '%$caller_id%' LIMIT 1";
                  //$api_result_array[0] = (array)db_select($sql_query);
                  $api_result_array = db_select_array($sql_query, 1);
                  $api_result_array = json_decode(json_encode($api_result_array), true);
              }
              
              $api_data[$row->section_id] = $api_result_array;//call_extn_api($url);          
              $set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'");
               
              if (is_array($set_values)) {
                  foreach ($set_values as $sval) {
                  	  set_crm_value($callid, $sval->name, $sval->api, $api_data[$row->section_id][0]);
                  }
              }
              if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
                  $data->data_record_id = $api_data[$row->section_id][0]['record_id'];
              }
              $existing_set_values = get_existing_set_values($callid, $caller_id);
              $_SESSION['set_values'] = $existing_set_values;
              
              if (isset($existing_set_values['s_items']) && count($existing_set_values['s_items']) > 0){
                  $dbFields = getDBFieldNames();
                  $searchedItems = unserialize($existing_set_values['s_items']);
                  $squeryCond = "";
                  if (isset($searchedItems->searchByCLI) && $searchedItems->searchByCLI == $caller_id){
                      $data->search_items = serialize($searchedItems);
                      foreach ($searchedItems as $itemKey => $itemVal){
                          if (!empty($itemVal) && in_array($itemKey, $dbFields)){
                              if (!empty($squeryCond)){
                                  $squeryCond .= " OR ";
                              }
                              $squeryCond .= $itemKey." LIKE '%$itemVal%'";
                          }
                      }
                  }
                  if (!empty($squeryCond)){
                      $sql_query = "SELECT * FROM skill_crm where $squeryCond LIMIT 1";
                      //$api_result_array[0] = (array)db_select($sql_query);
                      $api_result_array = db_select_array($sql_query, 1);
                      $api_result_array = json_decode(json_encode($api_result_array), true);
                      $api_data[$row->section_id] = $api_result_array;
    
                      $set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'");
                       
                      if (is_array($set_values)) {
                          foreach ($set_values as $sval) {
                              set_crm_value($callid, $sval->name, $sval->api, $api_data[$row->section_id][0]);
                          }
                      }
                      if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
                          $data->data_record_id = $api_data[$row->section_id][0]['record_id'];
                      }
                  }
              }
          }
      }elseif(strlen($row->api) < 6){
          continue;
      }else {
          if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
          $url = str_replace("<CLI>",$cc->callerid,$row->api);
          $url = str_replace("<DID>",$cc->did,$url);
          $url = str_replace("<AGENT>",$cc->agent_id,$url);
          //$url = str_replace("<ID>",$cc->altid,$url);

    	  $existing_set_values = get_existing_set_values($callid);
    	  if (is_array($existing_set_values)) {
    			foreach ($existing_set_values as $key => $val) {
    				$url = str_replace("<$key>", $val, $url);
					if($key == 'ID'){
						$cc->altid = $val;
					}
    			}
    	   }
		   $url = str_replace("<CITY1>", 'CB' . substr($cc->altid,3,7), $url);	# Fix for CityBank
           $url = str_replace("<TAB_VALUE>", $tabDefaultValue, $url);
           msg("Template_id=$data->template_id ;  page title=$data->page_title ;  Account_id=$cc->altid", 1);
    
    	   //Debug strat from here
           $api_data[$row->section_id] = call_extn_api($url);
          
    	   $set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'");
    			
    		if (is_array($set_values)) {
    			foreach ($set_values as $sval) {
    				set_crm_value($callid, $sval->name, $sval->api, $api_data[$row->section_id][0]);
    			}
    		}
    		if (isset($api_data[$row->section_id][0]['record_id']) && !empty($api_data[$row->section_id][0]['record_id'])){
    			$data->data_record_id = $api_data[$row->section_id][0]['record_id'];
    		}
    		$existing_set_values = get_existing_set_values($callid);
    		$_SESSION['set_values'] = $existing_set_values;
        }
        $secRecordObject->{$row->section_id} = $secRecordId;
   }

   //print_r($api_data);exit;

   foreach($section AS $row) {
      unset($section_data);
      $fields = db_select_array("SELECT field_label,field_key,field_mask,field_tab_id FROM skill_crm_template_fields WHERE template_id='$template_id' AND section_id='$row->section_id' ORDER BY sl");
      if(substr($row->api, 0, 3) == 'SEC') $api_data_key = substr($row->api, -1);
      else $api_data_key = $row->section_id;
      
      $isEditable = 'N';
      if ($row->api == "MSDB") $isEditable = 'Y';
      elseif (substr($row->api, 0, 4) == "MSDB" && !empty($secRecordObject->{$row->section_id})) $isEditable = 'Y';
      
      $data->section[$row->section_id] = new stdClass();
      $data->section[$row->section_id]->section_title = $row->section_title;
      $data->section[$row->section_id]->section_type = $row->section_type;
      //$data->section[$row->section_id]->is_editable = $row->api == "MSDB" ? 'Y' : $row->is_editable;
      //$data->section[$row->section_id]->is_editable = substr($row->api, 0, 4) == "MSDB" ? 'Y' : $row->is_editable;
      $data->section[$row->section_id]->is_editable = $isEditable == 'Y' ? 'Y' : $row->is_editable;
      $data->section[$row->section_id]->is_searchable = $row->is_searchable;
      $data->section[$row->section_id]->crm_rec_id = !empty($secRecordObject->{$row->section_id}) ? $secRecordObject->{$row->section_id} : "";
	  
		if ($row->is_searchable == 'Y') {
			$data->section[$row->section_id]->search_submit_label = $row->search_submit_label;
			$data->section[$row->section_id]->filters = array();
			//array('0' => $filter, '1' => $filter2);
			$filters = db_select_array("SELECT field_label,field_key,field_type FROM skill_crm_template_filters ".
				"WHERE template_id='$template_id' AND section_id='$row->section_id' ORDER BY sl");
			if (is_array($filters)) {
				foreach($filters AS $filter) {
					$data->section[$row->section_id]->filters[] = $filter;
				}
			}
		}
		
		
      if($row->section_type == 'F') {		# Fields
         $i = 0;
         foreach($fields AS $field) {
            $section_data[$i] = new stdClass();
            $section_data[$i]->field_label = $field->field_label;
            $field_key = $field->field_key;
            $section_data[$i]->field_key = $field_key;
            
            if(strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) {
                $field_key = substr($field_key,1) - 1;
            }
            $section_data[$i]->data_value = isset($api_data[$api_data_key]) ? $api_data[$api_data_key][0][$field_key] : '';
			if (!empty($field->field_mask)) {
				//$section_data[$i]->data_value = mask_value($section_data[$i]->data_value, '([0-9-]{2})([0-9-]*)([0-9]{4})');
				$section_data[$i]->data_value = mask_value($section_data[$i]->data_value, $field->field_mask);
			}
			$section_data[$i]->ftab_id = $field->field_tab_id;
            ++$i;
         }
         $data->section[$row->section_id]->fields  = $section_data;

      } elseif($row->section_type == 'G') {	# Grid

		foreach($fields AS $field) {
		    $section_data['column'][] = $field->field_label;
		    $section_data['tab_id'][] = $field->field_tab_id;
		}

         
		if (isset($api_data[$api_data_key])) {
			$i = 0;
			//foreach($api_data[$api_data_key][0] AS $val) {
			foreach($api_data[$api_data_key] AS $val) {
                if(!is_array($val)) $val = (array)$val;
				foreach($fields AS $field) {
					$field_key = $field->field_key; 
					if (strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
               
					if (!empty($field->field_mask)) {
						//$val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
						$val[$field_key] = mask_value($val[$field_key], $field->field_mask);
					}
					$section_data[$i][] = isset($val[$field_key]) && !is_null($val[$field_key])? $val[$field_key] : "";
				}
				++$i;
			}
		}
		 
		$data->section[$row->section_id]->grid = $section_data;
      }
   }
   
    return $data;
}

function call_extn_api($url) {
    global $soap_debug;
    $api_result_array = array ();
    $conn_method = strtoupper ( substr ( $url, 0, 4 ) );
    if ($conn_method == 'HTTP') {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = trim ( curl_exec ( $ch ) );
        curl_close ( $ch );

        $json = 0;
        if (substr ( $result, 0, 2 ) == '[{' || substr ( $result, - 2 ) == '}]')
            $json = 1;
        if (substr ( $result, 0, 1 ) == '{' || substr ( $result, - 1 ) == '}')
            $json = 1;

        if ($json) {
            $result = json_decode ( $result );
        } else {
            $result = explode ( "\n", $result );
        }
        foreach ( $result as $val ) {
            if ($json) {
                $api_result_array [] = obj2array ( $val );
            } else {
                $api_result_array [] = explode ( ',', trim ( $val ) );
            }
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
