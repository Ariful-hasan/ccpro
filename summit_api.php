<?php
define('DEBUG', false);
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ERROR);
} else {
    error_reporting(0);
}

$_cli = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : "";
$_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : "";
$_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$_impact = !empty($_REQUEST['impact']) ? $_REQUEST['impact'] : "";

//GPrint($_impact);
//GPrint($_REQUEST['category']);
//GPrint($_REQUEST);die;

if (!empty($_cli)) {
    $response = [];
    $auth_api_response = get_auth_token();
    //echo 'state 2 ';GPrint($auth_api_response->auth_token);

    if (empty($auth_api_response->auth_token)) {
        return json_encode($auth_api_response);
    }

    //$result = get_contact_info($auth_api_response->auth_token);
    $result = get_info($auth_api_response->auth_token, $_type, $_id, $_impact);
    //echo 'state 3 ';GPrint($result);die;
    echo $result;
    exit();
    //return $result;
}

function set_info($data, $_type) {
	
	if ($_type == "ncr") {
		if (is_object($data)) {
			$data->impacts = $data->ncr_id;
		}
		if (is_array($data)) {
			foreach ($data as $key){
				if (is_array($key->impacts)){
					$key->impacts = $key->ncr_id;
				}
			}
		}
	} elseif ($_type == "ticket") {
		if (is_object($data)) {
			$data->impacts = $data->ticket_id;
		}
		if (is_array($data)) {
			foreach ($data as $key){
				if (is_array($key->impacts)){
					$key->impacts = $key->ticket_id;
				}
			}
		}
	}
	
	
    return $data;
}

function get_info($auth_token, $_type=null, $_id=null, $_impact=null) {
    global $_cli;
    $info_url = "http://172.20.17.50/address_book/public/api/gplex_get_contact_info";

    $mainobj = new stdClass();
    $mainobj->payload = '';
    $mainobj->msg = '';
    $payload = json_encode(["auth_token"=>$auth_token, "phone"=>$_cli]);
    $response = get_api_response($info_url, $payload);
	
    if (isJson($response)) {
        $response = json_decode($response);
        if ($response->status == "200") {
            if ($_type == "ncr") {
				$result = $response->payload->ncr_info;
				
				if (!empty($_impact) && !empty($_id)) {
                    foreach ($result as $key) {
                        if ($key->ncr_id == $_id) {
                            return json_encode($key->impacts);
                        }
                    }
                } elseif(!empty($_id) && empty($_impact)) {
                    foreach ($result as $key) {
                        if ($key->ncr_id == $_id) {
							$key = set_info($key, $_type);
                            return json_encode([0=>$key]);
                        }
                    }
                }
				
				$result = set_info($response->payload->ncr_info, $_type);
                return json_encode($result);
            } elseif ($_type == "ticket") {
				$result = $response->payload->ticket_info;
				
				// for ticket impacts
				/*if (!empty($_impact) && !empty($_id)) {
                    foreach ($result as $key) {
                        if ($key->ticket_id == $_id) {
                            return json_encode($key->impacts);
                        }
                    }
                }*/ 
				if(!empty($_id) && empty($_impact)) {
					foreach ($result as $key) {
                        if ($key->ticket_id == $_id) {
							$key = set_info($key, $_type);
                            return json_encode([0=>$key]);   
                        }
                    }
				}
				
				//$result = set_info($response->payload->ticket_info, $_type);
				return json_encode($result);
            } else {
                return json_encode($response->payload->contact_info);
            }
        } else {
            $mainobj->msg = $response->payload->response;
            return $mainobj;
        }
    }
    return null;
}



/*function get_contact_info($auth_token) {
    global $_cli;
    $url = "http://172.20.17.50/address_book/public/api/gplex_get_contact_info";
    $mainobj = new stdClass();
    $mainobj->payload = '';
    $mainobj->msg = '';

    $payload = json_encode(["auth_token"=>$auth_token, "phone"=>$_cli]);
    //echo 'state 4 ';GPrint($payload);die;
    $response = get_api_response($url, $payload);

    if (isJson($response)) {
        $response = json_decode($response);
        if ($response->status == "200") {
            return json_encode($response->payload->contact_info);
        } else {
            $mainobj->msg = $response->payload->response;
            return $mainobj;
        }
    }
}*/

function get_auth_token() {
    $url = "http://172.20.17.50/address_book/public/api/api_auth";
    $payload = '{"user_id":"gplex_user","password":"sclgplex2020"}';

    $mainobj = new stdClass();
    $mainobj->auth_token = '';
    $mainobj->msg = '';

    $response = get_api_response($url, $payload);
    //echo 'state 1 ';GPrint($response);die;
    if (isJson($response)) {
        $response = json_decode($response);
        if ($response->status == "200") {
            if ($response->payload->auth_token) {
                $mainobj->auth_token = $response->payload->auth_token;
            }
        } else {
            $mainobj->msg = $response->payload->response;
        }
    }
    return $mainobj;
}

function get_api_response($url, $payload) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'cache-control: no-cache'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $payload);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function get_response_data ($data) {
    $response = [];
    if (!empty($data)){
        foreach ($data as $item){
            foreach ($item as $key => $value){
                $response[$key] = $value;
            }
        }
    }
    return $response;
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function GPrint($obj){
    echo"<pre>".print_r($obj,true)."</pre>";
}


