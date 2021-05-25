<?PHP
# Call external API for POP UP
#error_reporting(7);
global $debug,$log_file;
$debug = 0;    # 1 = echo; 2= file
$log_file = '/usr/local/gplexcc/regsrvr/engine/log.txt';
require_once('db_conf.php');
db_conn();
#$id = 'AAE';
#$data = get_api_data($id);
#print_r($data);

function get_existing_set_values($callid)
{
	if (empty($callid)) return null;
	$existing_set_values = isset($_SESSION['set_values']) ? $_SESSION['set_values'] : array();
	if (!isset($existing_set_values['callid']) || $existing_set_values['callid'] != $callid) $existing_set_values = array();
	$existing_set_values['callid'] = $callid;
	
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

function search_account($data,$callid) {
	
	$_SESSION['set_values'] = null;
	$template = db_select("SELECT api FROM skill_crm_template WHERE template_id='$data[template_id]'");
	
	if (strlen($template->api) < 6) return '';
	//if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
	$cc = null;
	//echo $callid;
	if (!empty($callid)) {
		$cc = db_select("SELECT callerid, altid, agent_id, language, did, ".
			"caller_auth_by FROM calls_in WHERE callid='$callid'");
	}
	
	if ($cc) {
		$url = str_replace("<CLI>", $cc->callerid, $template->api);
		$url = str_replace("<DID>", $cc->did, $url);
		$url = str_replace("<AGENT>", $cc->agent_id, $url);
		//$url = str_replace("<ID>", $cc->altid, $url);
		//$url = str_replace("<CITY1>",'CB'.substr($cc->altid,3,7),$url); //Fix for CityBank
	
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				$url = str_replace("<$key>", $val, $url);
			}
		}
		//var_dump($url);exit;
		$api_data = call_extn_api($url);	
		$account_id = $api_data[0][0];
   		if (!empty($account_id)) {
			
			//$existing_set_values['callid'] = $callid;
			//set_crm_value($callid, $sval->name, $sval->api, $api_data[0]);
	
			$set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$data[template_id]' AND section_id=''");
			
			if (is_array($set_values)) {
				foreach ($set_values as $sval) {
					set_crm_value($callid, $sval->name, $sval->api, $api_data[0]);
				}
			}
			
			$existing_set_values = get_existing_set_values($callid);
			$_SESSION['set_values'] = $existing_set_values;
			
      		db_update("UPDATE calls_in SET altid='$account_id',caller_auth_by='' WHERE callid='$callid'");
      		//echo "200|OK";
			return $account_id;
		}
	}
	return '';
	//echo "404|Agent not in service";
}

function caller_verified($account_id,$callid,$agent_id='') {
   if($account_id && $callid) {
      if(db_update("UPDATE calls_in SET caller_auth_by='A' WHERE callid='$callid' AND altid='$account_id'")) {
         $record_id = db_select_one("SELECT record_id FROM skill_crm WHERE account_id='$account_id'");
          insert_disposition_log($record_id,$callid,$agent_id,'A');
         echo "200|OK";
      }
      return;
   }
   echo "404|Fail to verify the caller";
}


function insert_disposition_log($record_id,$callid,$agent_id,$caller_auth_by) {
   if(!db_select_one("SELECT record_id FROM skill_crm_disposition_log WHERE callid='$callid'"))
         db_update("INSERT INTO skill_crm_disposition_log SET record_id='$record_id',callid='$callid',tstamp=UNIX_TIMESTAMP(),agent_id='$agent_id',caller_auth_by='$caller_auth_by'");
}

function get_api_response($callid, $section, $request_params)
{
	if (strlen($section->api) < 6) return '';
	//if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
	$cc = null;
	//echo $callid;	
	if (!empty($callid)) {
		$cc = db_select("SELECT callerid, altid, agent_id, language, did, ".
			"caller_auth_by FROM calls_in WHERE callid='$callid'");
		
	}
	
	if ($cc) {
		$url = str_replace("<CLI>", $cc->callerid, $section->api);
		$url = str_replace("<DID>", $cc->did, $url);
		$url = str_replace("<AGENT>", $cc->agent_id, $url);
		$url = str_replace("<ID>", $cc->altid, $url);
		$url = str_replace("<CITY1>",'CB'.substr($cc->altid,3,7),$url); //Fix for CityBank
		
		if (is_array($request_params)) {
			foreach ($request_params as $key => $val) {
				$url = str_replace("<$key>", $val, $url);
			}
		}
		
		$api_data = call_extn_api($url);
		$section_data = array();
		$fields = db_select_array("SELECT field_label,field_key,field_mask FROM skill_crm_template_fields WHERE template_id='$section->template_id' AND section_id='$section->section_id' ORDER BY sl");
		
		if ($section->section_type == 'G') {		
		
			foreach($fields AS $field) $section_data['column'][] = $field->field_label;
        
			if (is_array($api_data)) {
				$i = 0;
				foreach($api_data AS $val) {
					foreach($fields AS $field) {
						$field_key = $field->field_key; 
						if (strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
               
						if (!empty($field->field_mask)) {
							$val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
						}
						$section_data[$i][] = $val[$field_key];
					}
					++$i;
				}
			}
		}
		
		return $section_data;
	}
	
	return '';
}

 function get_api_data($id) {
 global $SQL_debug,$cc;
   if(strlen($id) > 10) $id = unserialize(base64_decode($id));
   $callid = isset($id['callid']) ? $id['callid'] : '';
   //var_dump($id);exit;
   $lcrm = new stdClass();
   if($callid) {
      # Get data from calls_in
      $cc = db_select("SELECT callerid,altid,agent_id,language,did,caller_auth_by FROM calls_in WHERE callid='$callid'");
      if(!$cc) {
          $cc = db_select("SELECT record_id,agent_id,caller_auth_by FROM skill_crm_disposition_log WHERE callid='$callid'");
          $cc->altid = db_select_one("SELECT account_id FROM skill_crm WHERE record_id='$cc->record_id'");
          $lcrm->record_id = $cc->record_id;
		  $lcrm->tpin_status = 'N';
          #$data->error = 'Agent not in service';
          #return $data;
      }

      # Local CRM Search
      if(empty($cc->record_id)) {
      $lcrm = db_select("SELECT record_id,IF(TPIN='','N','Y') tpin_status FROM skill_crm WHERE account_id='$cc->altid'");
          if(!$lcrm && $cc->caller_auth_by) {
             unset($lcrm);
             $lcrm->record_id = time() . rand(1000,9999);
             $lcrm->tpin_status = 'N';
             db_update("INSERT INTO skill_crm SET record_id='$lcrm->record_id',account_id='$cc->altid',status='A'");
          }
      }
         
   }
   /*
   if (empty($lcrm->record_id)) {
	   	$data->error = 'Profile not found';
   		return $data;
   }
   */
   if (empty($lcrm->record_id)) {
	   $lcrm = new stdClass();
	   $lcrm->record_id = '';
	   $lcrm->tpin_status = 'N';
   }
   if (!empty($cc->caller_auth_by)) {
      $caller_auth_status = 'Y';
      if($cc->caller_auth_by == 'I') $auth_by = 'through IVR'; else $auth_by = "by Agent";
      $caller_auth_msg = "Caller authenticated $auth_by";
   } else {
      $caller_auth_status = '';
      $caller_auth_msg = "Caller is NOT authenticated";
   }
   insert_disposition_log($lcrm->record_id,$callid,$cc->agent_id,$cc->caller_auth_by);
   
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
   //var_dump($cc);exit;
   $data->template_id = $id['template_id'];
   $data->page_title = $crm->title;
   $data->skill_name = $id['skill_name'];
   $data->caller_prefered_language = isset($cc->language) ? $cc->language : '';
   $data->caller_auth_status = $caller_auth_status;
   $data->caller_auth_msg = $caller_auth_msg;
   $data->account_id = $cc->altid;
   $data->crm_record_id = $lcrm->record_id;
   $data->callid = $callid;
   $data->agent_id = $cc->agent_id;
   
   $section = db_select_array("SELECT sl,section_id,section_title,section_type,api,is_editable,debug_mode,is_searchable,search_submit_label FROM skill_crm_template_section WHERE template_id='$template_id' AND active='Y' ORDER BY sl");
   if(!$section) {
      $data->error = "No section record found on template_id=$template_id";
      return $data;
   }
   #Call API
   foreach($section AS $row) {
      if(strlen($row->api) < 6) continue;
      if($row->debug_mode == 'Y') $SQL_debug = 1; else $SQL_debug = 0;
      $url = str_replace("<CLI>",$cc->callerid,$row->api);
      $url = str_replace("<DID>",$cc->did,$url);
      $url = str_replace("<AGENT>",$cc->agent_id,$url);
      $url = str_replace("<ID>",$cc->altid,$url);
      $url = str_replace("<CITY1>",'CB' . substr($cc->altid,3,7),$url);	# Fix for CityBank
      msg("Template_id=$data->template_id ;  page title=$data->page_title ;  Account_id=$cc->altid",1);
	  
	  $existing_set_values = get_existing_set_values($callid);
	  if (is_array($existing_set_values)) {
			foreach ($existing_set_values as $key => $val) {
				$url = str_replace("<$key>", $val, $url);
			}
		}
	  
      $api_data[$row->section_id] = call_extn_api($url);
      
	  $set_values = db_select_array("SELECT name, api FROM skill_crm_template_values WHERE template_id='$template_id' AND section_id='$row->section_id'");
			
	if (is_array($set_values)) {
		foreach ($set_values as $sval) {
			set_crm_value($callid, $sval->name, $sval->api, $api_data[$row->section_id][0]);
		}
	}
			
		$existing_set_values = get_existing_set_values($callid);
		$_SESSION['set_values'] = $existing_set_values;

   }

   //print_r($api_data);exit;
   
   foreach($section AS $row) {
      unset($section_data);
      $fields = db_select_array("SELECT field_label,field_key,field_mask FROM skill_crm_template_fields WHERE template_id='$template_id' AND section_id='$row->section_id' ORDER BY sl");
      if(substr($row->api, 0, 3) == 'SEC') $api_data_key = substr($row->api, -1);
      else $api_data_key = $row->section_id;
      $data->section[$row->section_id]->section_title = $row->section_title;
      $data->section[$row->section_id]->section_type = $row->section_type;
      $data->section[$row->section_id]->is_editable = $row->is_editable;
	  $data->section[$row->section_id]->is_searchable = $row->is_searchable;
	  
		if ($row->is_searchable == 'Y') {
			$data->section[$row->section_id]->search_submit_label = $row->search_submit_label;
			$data->section[$row->section_id]->filters = array();
			//array('0' => $filter, '1' => $filter2);
			$filters = db_select_array("SELECT field_label,field_key,field_type ".
				"FROM skill_crm_template_filters WHERE ".
				"template_id='$template_id' AND section_id='$row->section_id' ".
				"ORDER BY sl");
			if (is_array($filters)) {
				foreach($filters AS $filter) {
					$data->section[$row->section_id]->filters[] = $filter;
				}
			}
		}
		
		
      if($row->section_type == 'F') {		# Fields
         $i = 0;
         foreach($fields AS $field) {
            $section_data[$i]->field_label = $field->field_label;
            $field_key = $field->field_key;
            if(strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
            $section_data[$i]->data_value = isset($api_data[$api_data_key]) ? $api_data[$api_data_key][0][$field_key] : '';
			if (!empty($field->field_mask)) {
				$section_data[$i]->data_value = mask_value($section_data[$i]->data_value, '([0-9-]{2})([0-9-]*)([0-9]{4})');
			}
            ++$i;
         }
         $data->section[$row->section_id]->fields  = $section_data;
      } elseif($row->section_type == 'G') {	# Grid

		foreach($fields AS $field) $section_data['column'][] = $field->field_label;
         
		if (isset($api_data[$api_data_key])) {
			$i = 0;
			foreach($api_data[$api_data_key] AS $val) {
				foreach($fields AS $field) {
					$field_key = $field->field_key; 
					if (strtoupper(substr($field_key,0,1)) == 'V' && is_numeric(substr($field_key,1))) $field_key = substr($field_key,1) - 1;
               
					if (!empty($field->field_mask)) {
						$val[$field_key] = mask_value($val[$field_key], '([0-9-]{2})([0-9-]*)([0-9]{4})');
					}
					$section_data[$i][] = $val[$field_key];
				}
				++$i;
			}
		}
		 
		$data->section[$row->section_id]->grid = $section_data;
      } elseif($row->section_type == 'T') {		# TPIN
         if(db_select_one("SELECT LEFT(var1,2) FROM agents WHERE agent_id='$cc->agent_id' AND callid='$callid'") == 'GT') $lcrm->tpin_status = 'P';
         $data->section[$row->section_id]->tpin_status = $lcrm->tpin_status;
      }
   }
return $data;
}


function call_extn_api($url) {
  $api_result_array = array();
      $conn_method = strtoupper(substr($url,0,4));
      if($conn_method == 'HTTP') {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_TIMEOUT, 30);
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = trim(curl_exec($ch));
          curl_close($ch);

          $json = 0;
          if(substr($result,0,2) == '[{' || substr($result,-2) == '}]') $json = 1;
          if(substr($result,0,1) == '{'  || substr($result,-1) == '}') $json = 1;

          if($json) {
              $result = json_decode($result);
          } else {
              $result = explode("\n", $result);
          }
          foreach($result as $val) {
              if($json) {
                  $api_result_array[] = obj2array($val);
              } else {
                  $api_result_array[] = explode(',',trim( $val));
              }
          }
      }
      else if($conn_method == 'ODBC') {
          list($conn_method,$DSN,$sql_query) = explode(':',$url);
          putenv("ODBCINI=/etc/odbc.ini");
          if($DSN=='LOCAL') {
            msg("DSN>>>>> $conn_method,$DSN,$sql_query");
            $odbc_conn = odbc_connect("DSN=$DSN;", '', '');             # DSN is define in file
          } else if($DSN=='informix') {
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
              break;
          }

          $result = odbc_exec($odbc_conn,trim($sql_query));
          while($row = odbc_fetch_array($result)) {
              $api_result_array[] = obj2array($row);
          }

          odbc_free_result($result);
          odbc_close($odbc_conn);
      }
      else if($conn_method == 'SOAP' || $conn_method == 'XML') {
          list($conn_method,$conn_name,$function,$find_array) = explode(':',$url);      # WSDL-URL is defined in Dadabase
          $soap = db_select("SELECT url,credential,pass_credential,submit_method,submit_param,return_method,return_param FROM ivr_api WHERE conn_name='$conn_name' AND active='Y'");
          msg($soap);
          $WSDL_URL = $soap->url;
          if(!$WSDL_URL) {
              msg("SOAP conn [$conn_name] not defined",1);
              hangup();
          }
          list($function,$soap_var) = explode('(', $function);
          $soap_var = substr($soap_var, 0, -1);
          $soap_var = explode(',', $soap_var);
          unset($soap_param);
          if($soap->pass_credential == 'Y') {
              $credential = explode(',', $soap->credential);
              foreach($credential as $val) {
                  list($key,$val) = explode('=', $val);
                  $soap_param[$key] = $val;
              }
          }
          foreach($soap_var as $val) {
              $val = trim($val);
              if(strpos($val,'=')) {
                  list($key,$val) = explode('=', $val);
                  $key = trim($key);
                  $val = trim($val);
                  if(substr($val,0,1) == '<') {
                      $val = substr($val,1,-1);
                      $val = isset($cc->customerid[$val]) ? $cc->customerid[$val] : '';
                  }
                  $soap_param[$key] = $val;
              } else {
                  $soap_param[$val] = $cc->customerid[$val];
              }
          }
          msg($soap_param);
          if($soap->submit_method == 'C') {                     # Class
                $soap_param = array2obj($soap_param);
          }
          elseif($soap->submit_method == 'J') {                 # JSON
                $soap_param = json_encode($soap_param);
                $data->args0 = $soap_param;
          }
          elseif($soap->submit_method == 'P') {                 # param
                $data = implode(',' , $soap_param);
				//$data = array($soap_param);
          }

          if($soap->submit_param) {
                $submit_param = $soap->submit_param;
                $data = new stdClass();
                $data->$submit_param = $soap_param;

          } else {                                              # Array
                $data = $soap_param;
          }
          
          if($conn_method == 'SOAP') {
             # SOAP connection and function call
             msg("Calling SOAP function $function");
             $client = new SoapClient($WSDL_URL);
			 //echo $soap->submit_method;
			 //var_dump($data);exit;
             $result = $client->$function($data);
			 
             unset($client);
			 //var_dump($result);exit;
			 
          } else {
             #  XML-RPC connection and function call
             $request = xmlrpc_encode_request($function, $data);
             $context = stream_context_create(array('http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml",
                'content' => $request
             )));
             $rpc_conn = file_get_contents("http://$server_ip/gplexcc/api/xml/gplex.php", false, $context);
             $result = xmlrpc_decode($rpc_conn);
             msg($result);
          }


          if($soap->return_param) {                             # return param
                $return_param = $soap->return_param;
                $result = $result->$return_param;
          }
          # grab a spacific array defined at the end of the function;
          //var_dump($result);
		  if($find_array) {
                $find_array = explode(';', $find_array);
                foreach($find_array as $find_ver) {
                   $result = $result->$find_ver;
                }
          }
			//var_dump($result);
          if($soap->return_method == 'C') {                     # Class
                if(is_object($result)) $result = array($result);
          }
          elseif($soap->return_method == 'J') {                     # JSON
                msg($result);
                $result = json_decode($result);
          }

//print_r($result);echo '<br><br>';

          if(!is_array($result))  $result = array($result);

          $api_result_array = array();
          if(is_object($result[0]) || is_array($result[0])) {
            foreach($result as $row) {
              $api_result_array[] = obj2array($row, 1);
            }
          } else {
            $api_result_array[] = array($result);
          }
     } else {
        msg("Method $conn_method not found.",1);
        hangup();
     }

	 //var_dump($function);
	//var_dump($function);
	//if ($function == 'getCardAccountStatement') print_r($api_result_array);
	//if ($function != 'getAccountDetails') exit;	 

	return $api_result_array;
}


function array2obj($array){
  foreach($array as $key => $value){
    if(is_array($value)) $array[$key] = array2obj($value);
  }
  return (object) $array;
}


# convert array or object to array[0] + array[$key]
function obj2array($obj,$i=0) {
#  global $_tmp_ROW;
  if(!$i) $_tmp_ROW = array();
   foreach($obj as $key => $val) {
       if(is_object($val)) {
          obj2array($val,1);
       } else {
          $key = trim($key);
          $val = trim($val);
          $_tmp_ROW[] = $val;
          if(!is_numeric($key) && strlen($key) > 0) $_tmp_ROW[$key] = $val;
       }
   }
 return $_tmp_ROW;
}

function mask_value($value, $mask)
{
	//([0-9-]{2})([0-9-]*)([0-9]{4})
	$pattern = '/^'.$mask.'$/U';
	$matches = array();
	preg_match($pattern, $value, $matches);
	return $matches[1] . preg_replace('([0-9])', '*', $matches[2]) . $matches[3];
}
/*
function hangup()
{
	
}
*/
