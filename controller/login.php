<?php

class Login extends Controller
{
	function __construct() {
		parent::__construct();
	}
	
	function init()
	{
		global $db;
		$request = $this->getRequest();
		
		$errMsg = '';
		if ($request->isPost()) {
			$errMsg = $this->_validate_request();
			if (empty($errMsg)) {
				$user = trim($request->getPost('user'));
				$user_len = strlen($user);
				if ($user_len <= 4) {
					include('model/MAgent.php');
					$agent_model = new MAgent();
				
					if ($agent_model->isAgentExist($user)){
						if ($agent_model->isAgentBlocked($user)){
							$errMsg = "Your account($user) has been auto locked out due to ".$agent_model->loginAttempts." failed attempts to log on";
							$agent_model->addToAuditLog('Misslogin', 'U', $user, "Account blocked");
						}else {
							$valid_user = $agent_model->getValidAgent($user, $request->getPost('pass'));
							if (!empty($valid_user) && $valid_user->active=='Y') {
								// Generate OCX Token
								$ocxtoken=$agent_model->GenerateToken($user);								
								$timeline = $agent_model->getPanelAccessTimeline($user);
								$valid_user->account_id='100001';
								$valid_user->db_prefix=$db->db_suffix;
								if ($valid_user->usertype == 'S') {
									
									$allowed_skills = $agent_model->getAssignedSkills($valid_user->agent_id);
									$allowed_agents = $agent_model->getSupervisedAgents($valid_user->agent_id);
									//var_dump($allowed_skills);
									//var_dump($allowed_agents);exit;
									$valid_user->allowed_agents = array();
									$valid_user->allowed_skills = array();
									if (is_array($allowed_skills)) {
										foreach ($allowed_skills as $skillobj) {
											$valid_user->allowed_skills[] = $skillobj->skill_id;
										}
									}
									 if (is_array($allowed_agents)) {
									 	foreach ($allowed_agents as $agentobj) {
									 		$valid_user->allowed_agents[] = $agentobj->agent_id;
									 	}
									 }
								}
								$licenseInfo = $agent_model->getLicenseInfo();
								UserAuth::setLicenseSettings($licenseInfo);
								UserAuth::login($user, $valid_user->usertype,$ocxtoken,$valid_user);
								UserAuth::setExpirationTime($timeline->is_check_required, $timeline->stop_timestamp);
								$agent_model->addToAuditLog('Login', 'I', "", "Logged in");
								if ($agent_model->isPassChangeOver90Days($user, $request->getPost('pass'))){
									UserAuth::setForcePasswordOption(true);
									$uri = './index.php?task=password';
									if (isset($_REQUEST['lparam']) & !empty($_REQUEST['lparam'])) {
										$uri .= '&lparam=' . $_REQUEST['lparam'];
									}
								}else {
									if (isset($_REQUEST['lparam']) & !empty($_REQUEST['lparam'])) {
										$uri = urldecode($_REQUEST['lparam']);
									} else {
										$uri = './index.php?task=agents';
									}
									if (!empty($agent_model->passExpireDays) && !empty($agent_model->expireMsgDays)){
									    $passChDate = $agent_model->isPassChangeOver70Days($user, $request->getPost('pass'));
									    
									    if (!empty($passChDate)){
									        $now = time();
									        $changedDate = strtotime($passChDate);
									        $datediff = $now - $changedDate;
									        $datediff = floor($datediff/(60*60*24));
									        $dayDifferent = $agent_model->passExpireDays - $datediff + 1;
									        if ($agent_model->expireMsgDays <= $datediff && $dayDifferent > 0){
									            UserAuth::showPassMessage(true, $dayDifferent);
									            $pathInPieces = explode(DIRECTORY_SEPARATOR, __DIR__);
									            $projectRoot = isset($pathInPieces[count($pathInPieces)-2]) ? $pathInPieces[count($pathInPieces)-2] : "";
									            if (empty($uri) || $uri == '/ccpro/' || $uri == $projectRoot){
									                $uri = './index.php?task=agents';
									            }
									            unset($pathInPieces);
									            $uri .= '&msgdays='.$dayDifferent;
									        }
									    }
									}									
								}
								$this->getTemplate()->redirect($uri);
								
							} else {
								$agent_model->addToAuditLog('Login', 'M', $user, "Failed login attempt");
								$agent_model->saveLoginAttempt($user);
								$errMsg = "Invalid user!! Check your password!!";
							}
						}
					}else {
						$errMsg = "Invalid user!! Check your password!!";
						$agent_model->addToAuditLog('Login', 'M', "", $user."; Failed login attempt");
					}
					/*$valid_user = $agent_model->getValidAgent($user, $request->getPost('pass'));
					if (!empty($valid_user)) {
						UserAuth::login($user, $valid_user->usertype);
						if (isset($_REQUEST['lparam']) & !empty($_REQUEST['lparam'])) {
							$this->getTemplate()->redirect(urldecode($_REQUEST['lparam']));
						} else {
							$this->getTemplate()->redirect("./index.php?task=agents");
						}
					} else {
						$agent_model->saveLoginAttempt($user);
						$errMsg = "Invalid user!! Check your password!!";
					}*/
				} else {
					include('model/MSetting.php');
					$setting_model = new MSetting();

					$valid_user = $setting_model->getValidPage($user, $request->getPost('pass'));
					if (!empty($valid_user)) {
						//UserAuth::login($user, $valid_user->usertype);
						//$this->getTemplate()->redirect("./index.php?task=agents");
						
						if ($user == 'dashboard') {
							//$this->getTemplate()->redirect("./index.php?task=agents");
							UserAuth::login_page($user);
						} else if ($user == 'linkstatus') {
							UserAuth::login_page($user);
						}
						
						$this->getTemplate()->redirect("./index.php");

					} else {
						$errMsg = "Invalid user!! Check your password!!";
					}
					$errMsg = "Invalid user!! Check your password!!";
					include('model/MAgent.php');
					$agent_model = new MAgent();
					$agent_model->addToAuditLog('Login', 'M', "", $user."; Failed login attempt");
				}
			}
		}
		
		if (empty($errMsg)) {
			$errMsg = UserAuth::getMessage();
		}
		
		$templateVar['errMsg'] = $errMsg;
		$templateVar['request'] = $request;		
		
		// Gprint($_SERVER);
		// Gprint(base_url());
		$lparam = urlencode($_SERVER['REQUEST_URI']);
		if(isset($_REQUEST['lparam']) && !preg_match("/[^A-Za-z0-9_\.\-\+\?\/=]/", $lparam)){
			$lparam = trim($_REQUEST['lparam']);
			$templateVar['lparam'] = $lparam;
		}else{
			$templateVar['lparam'] = (preg_match("/[^A-Za-z0-9_\.\-\+\?\/=]/", $lparam)) ? base_url().'index.php?task=login' : urlencode($lparam);
		}
		
		// $templateVar['lparam'] = isset($_REQUEST['lparam']) ? trim($_REQUEST['lparam']) : urlencode($_SERVER['REQUEST_URI']);
		//if (isset($_REQUEST['tid'])) {
		//$templateVar['lparam'] = ;
		//echo $templateVar['param'];
		//}

		$this->getTemplate()->display_only('login', $templateVar);
	}
	
	function _validate_request()
	{
		$err = '';
		$request = $this->getRequest();
		$user = trim($request->getPost('user'));
		$pass = $request->getPost('pass');
		if (empty($user)) $err = "Please provide user";
		else if (empty($pass)) $err = "Please provide password";
		
		if (empty($err) && !preg_match("/^[a-zA-Z0-9]{4,12}$/",$user)) {
			$err = "Please provide valid user";
		}
		
		if (empty($err)) {
			$pos = $this->multi_strpos($pass, array("'", " ", ",", ";"));
			if ($pos !== false) {
				$err = "Please provide valid password";
			}
		}

		return $err;
	}
	
	function multi_strpos($haystack, $needles, $offset = 0) {
        	foreach ($needles as $n) {
            		if (strpos($haystack, $n, $offset) !== false)
                    		return strpos($haystack, $n, $offset);
        		}
        	return false;
	}
}

?>