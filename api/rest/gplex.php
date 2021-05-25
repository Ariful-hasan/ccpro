<?php

//exit('ASD');
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
require 'vendor/autoload.php';

require 'Slim/Slim.php';

require_once '../../conf.php';
require_once '../../lib/DBManager.php';

$_conn = new DBManager($db);
$dbSuffix = '';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();

function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    //echo 'a';exit;
    if (validateUserKey() !== true) {
      //$app->response()->header('X-Powered-By', 'Genusys/1.0.0');
      $app->halt(401);
    }
    //exit;
}

function validateUserKey()
{
  global $_conn, $dbSuffix;
  $key = isset($_GET['sessionId']) ? trim($_GET['sessionId']) : '';
  if (empty($key)) return false;
  $sql = "SELECT db_suffix FROM cc_master.api_access_key WHERE api_key='$key'";
  $result = $_conn->query($sql);
  if (is_array($result)) {
	if (strlen($result[0]->db_suffix) == 2) {
		$dbSuffix = 'cc' . $result[0]->db_suffix;
		return true;
	}
	//check last insert time
  }
  return false;
}

$app->get('/skills', 'authenticate', function () use ($app) {
        global $_conn,$dbSuffix;
	$sql = "SELECT skill_id AS id, skill_name AS name FROM $dbSuffix.skill WHERE active='Y'";
	$result = $_conn->query($sql);
	$response = new stdClass();
        $response->success = true;
        $response->reason = 'SUCCESS';
        $response->result = $result;
	echo json_encode($response);
});


$app->post('/callback', 'authenticate', function () use ($app) {    
  try {
    global $_conn,$dbSuffix;
    // get and decode JSON request body
    $request = $app->request();
    $body = $request->getBody();
    $input = json_decode($body); 

    $response_result = new stdClass();
    $response = new stdClass();
    $response->success = false;
    $response->reason = '';
    
    $msg = '';

    $cli = (string)$input->phone;
    $skillid = (string)$input->skillId;
    $disposition = '5002';
    
    $cli = str_replace(array("+", " ", "(", ")", "-"), "", $cli);

    if (strlen($cli) < 6) {
      $msg = 'Phone number too small.';
    }
    if (strlen($cli) > 13) {
      $msg = 'Phone number too large.';
    }
    if (empty($msg)) {
      if (!ctype_digit($cli)) $msg = 'Invalid phone number provided.';
    }
    
    if (empty($msg)) {
      if (empty($skillid)) {
        $msg = 'Skill ID is empty.';
      } elseif(strlen($skillid) != 2) {
        $msg = "Invalid Skill ID provided.";
      } else {
        $sql = "SELECT skill_name FROM $dbSuffix.skill WHERE skill_id='$skillid' AND active='Y'";
        $result = $_conn->query($sql);
        if (!is_array($result)) {
          $msg = "Invalid Skill ID provided.";
        }
      }
    }
    
    if (empty($msg)) {
    $sql = "SELECT tstamp, UNIX_TIMESTAMP() AS curtime FROM $dbSuffix.ivr_service_request WHERE caller_id='$cli' ORDER BY tstamp DESC LIMIT 1";
    $result = $_conn->query($sql);
    if (is_array($result)) {
      $time = $result[0];
      if ($time->curtime - $time->tstamp < 300) {
          $msg = 'Phone number ' . $cli . ' added recently.';
      }
    }
    }
    
    if (empty($msg)) {
        $sql = "INSERT INTO $dbSuffix.ivr_service_request SET tstamp=UNIX_TIMESTAMP(), caller_id='$cli', disposition_code='$disposition', skill_id='$skillid'";
        if ($_conn->query($sql)) {
          $sql = "SELECT tstamp FROM $dbSuffix.ivr_service_request WHERE caller_id='$cli' ORDER BY tstamp DESC LIMIT 1";
          $result = $_conn->query($sql);
          if (is_array($result)) {
            $time = $result[0];
            $response->success = true;
            $response_result->referenceId = $time->tstamp;
            $response_result->phone = $cli;
            $response->reason = 'SUCCESS';
          }
       } else {
         $response->reason = 'Failed to add callback number';
       }
    } else {
       $response->reason = $msg;
    }
    $response->result = $response_result;
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($response);
    
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});


$app->delete('/callback/:id/:phone', 'authenticate', function ($id, $phone) use ($app) {    
  try {
    global $_conn,$dbSuffix;
    // query database for article
    $request = $app->request();
    $response_result = new stdClass();
    $response = new stdClass();
    $response->success = false;
    $response->reason = '';
    $msg = '';

    $phone = str_replace(array("+", " ", "(", ")", "-"), "", $phone);

    if (strlen($phone) < 6) {
      $msg = 'Phone number too small.';
    }
    if (strlen($phone) > 13) {
      $msg = 'Phone number too large.';
    }
    if (empty($msg)) {
      if (!ctype_digit($phone)) $msg = 'Invalid phone number provided.';
    }

    if (empty($msg) && (strlen($id) != 10 || !ctype_digit($id))) {
	$msg = "Invalid ID provided.";
    }

    if (empty($msg)) {    
    $sql = "SELECT tstamp FROM $dbSuffix.ivr_service_request WHERE caller_id='$phone' AND tstamp='$id' ORDER BY tstamp DESC LIMIT 1";
    $result = $_conn->query($sql);
    
    if (is_array($result)) {
      $time = $result[0];
      $app->response->setStatus(200);
      
      $sql = "DELETE FROM $dbSuffix.ivr_service_request WHERE caller_id='$phone' AND tstamp='$id' LIMIT 1";
      if ($_conn->query($sql)) {
      	$response->success = true;
      	$response_result->referenceId = $id;
      	$response_result->phone = $phone;
      	$response->reason = 'SUCCESS';
      }
    } else {
      $app->response()->status(404);
	$response->reason = 'Not found.';      
    }
    } else {
	$app->response()->status(404);
	$response->reason = $msg;
    }
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
  
  $response->result = $response_result;
  $app->response()->header('Content-Type', 'application/json');
  echo json_encode($response);
});

$app->notFound(function () use ($app) {
	echo "Page Not Found";
});

// run
$app->run();

class UserAuth
{
	static function getDBSuffix()
	{
		return '_master';
	}

}
