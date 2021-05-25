<?php

class Password extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MAgent.php');
		include('model/MPassword.php');
		$agent_model = new MAgent();		
		$pass_model = new MPassword();
		$errMsg = '';
		$errType = 1;
		$data['maxlength'] = 15;
		$lparam = isset($_REQUEST['lparam']) ? trim($_REQUEST['lparam']) : '';
		$agent_model->setPasswordRules();
		
		if (UserAuth::isPasswordChangeRequired()){
			$errMsg = 'Your login password has expired. Please change your login password.';
			$passChDate = $agent_model->isPassChangeOver70Days(UserAuth::getCurrentUser(), '');
			if (!empty($passChDate)){
				$now = time();
				$changedDate = strtotime($passChDate);
				$datediff = $now - $changedDate;
				$dayDifferent = floor($datediff/(60*60*24));
				if (!empty($agent_model->passExpireDays) && $dayDifferent > $agent_model->passExpireDays){
					$errMsg = 'Your login password has been in use for '.$agent_model->passExpireDays.' days and has expired.';
				}
			}
		}
		$passRulesData = $pass_model->getPasswordRules(0, 7);
		$passRuleObj = new stdClass();
		if (count($passRulesData) > 0){
			foreach ($passRulesData as $pRule) {
				$objKey = $pRule->rule_key;
				if ($pRule->status == 'Y') $passRuleObj->$objKey = $pRule->value;
				else $passRuleObj->$objKey = "";
				if ($pRule->status == 'Y' && $pRule->rule_key == 'max'){
					$data['maxlength'] = $pRule->value;
				}
			}
		}
		
		if (isset($_POST['submit'])) {			
			$errMsg = $this->getValidationMsg($agent_model, $passRuleObj);
			if (empty($errMsg)) {
				$isUpdated = $agent_model->setAgentPassword(UserAuth::getCurrentUser(), $_POST['npass1']);
				if ($isUpdated) {
					$errType = 0;
					UserAuth::setForcePasswordOption(false);
					$agent_model->addToAuditLog('Password', 'U', "", "Password changed");
					$url = $this->getTemplate()->url("task=agent");
					if (!empty($lparam)) $url = $lparam;
					$msg = "Password Updated Successfully.";
					if (!empty($agent_model->passExpireDays)) $msg .= " &nbsp; Password should be changed within ".$agent_model->passExpireDays." days.";
					$this->getTemplate()->display('msg', array('pageTitle'=>'Change Password', 'isError'=>false, 'msg'=>$msg, 'redirectUri'=>$url));
				} else {
					$errMsg = 'Failed to Update Password';
				}
			}
		}
		
		$data['passRules'] = $passRuleObj;
		$data['lparam'] = $lparam;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Change Password';
		$this->getTemplate()->display('password_change', $data);
	}
	
	function actionMisslogin(){
		include('model/MAgent.php');
		include('lib/Pagination.php');
		$agent_model = new MAgent();
		$pagination = new Pagination();
		$errMsg = '';
		$errType = 1;
		
		$pagination->base_link = $this->getTemplate()->url("task=password&act=misslogin");
		$pagination->num_records = $agent_model->numMissLogins();
		
		if (!isset($_REQUEST['download'])){
		$data['mislogins'] = $pagination->num_records > 0 ? 
			$agent_model->getMissLogins($pagination->getOffset(), $pagination->rows_per_page) : null;
		}else {
			$data['pass_model'] = $agent_model;
		}			
			
		$pagination->num_current_records = is_array($data['mislogins']) ? count($data['mislogins']) : 0;
		//$data['attmNumber'] = $agent_model->attemptsNumUnlock();
		$data['attmNumber'] = 2;
		$data['pagination'] = $pagination;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Failed Login Attempts';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('misslogin_attempt', $data);
	}
	
	function actionMisslogin_bk(){
	    include('model/MAgent.php');
	    include('lib/Pagination.php');
	    $agent_model = new MAgent();
	    $pagination = new Pagination();
	    $errMsg = '';
	    $errType = 1;
	    $isUnlock = isset($_REQUEST['unlock']) ? trim($_REQUEST['unlock']) : '';
	    $agentId = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
	    $agentIP = isset($_REQUEST['aip']) ? trim($_REQUEST['aip']) : '';
	
	    if (!empty($agentId) && !empty($agentIP) && $isUnlock == "yes"){
	        if($agent_model->clearMisslogin2Unlock($agentId, $agentIP)){
	            $agent_model->addToAuditLog('Misslogin', 'U', $agentId, "Account unblocked");
	            $url = $this->getTemplate()->url("task=password&act=misslogin");
	            $msg = "User(".$agentId.") Account Unblocked Successfully";
	            $this->getTemplate()->display('msg', array('pageTitle'=>'Failed Login Attempts', 'isError'=>false, 'msg'=>$msg, 'redirectUri'=>$url));
	        }
	    }
	
	    $pagination->base_link = $this->getTemplate()->url("task=password&act=misslogin");
	    $pagination->num_records = $agent_model->numMissLogins();
	
	    if (!isset($_REQUEST['download'])){
	        $data['mislogins'] = $pagination->num_records > 0 ?
	        $agent_model->getMissLogins($pagination->getOffset(), $pagination->rows_per_page) : null;
	    }else {
	        $data['pass_model'] = $agent_model;
	    }
	    	
	    $pagination->num_current_records = is_array($data['mislogins']) ? count($data['mislogins']) : 0;
	    //$data['attmNumber'] = $agent_model->attemptsNumUnlock();
	    $data['attmNumber'] = 2;
	    $data['pagination'] = $pagination;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['request'] = $this->getRequest();
	    $data['pageTitle'] = 'Failed Login Attempts';
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('misslogin_attempt', $data);
	}

	function getValidationMsg($agent_model=null, $passRuleObj=null)
	{
		$err = '';

		$allowChars = "";
		if (isset($passRuleObj->spe) && !empty($passRuleObj->spe)){
			$arrSpecial = str_split($passRuleObj->spe);
			foreach ($arrSpecial as $sinChar) {
				$allowChars .= "\\".$sinChar;
			}
		}
		if (empty($_POST['opass'])){
			$err = 'Provide Old Password';
		} elseif (empty($_POST['npass1'])){
			$err = 'Provide New Password';
		} elseif (empty($_POST['npass2'])){
			$err = 'Provide Confirm New Password';
		} elseif (isset($passRuleObj->max) && !empty($passRuleObj->max) && strlen($_POST['npass1']) > $passRuleObj->max){
			$err = 'New Password length must not be more than '.$passRuleObj->max.' characters';
		} elseif (isset($passRuleObj->min) && !empty($passRuleObj->min) && strlen($_POST['npass1']) < $passRuleObj->min){
			$err = 'New Password must consist of at least '.$passRuleObj->min.' non-blank characters';
		} elseif (isset($passRuleObj->cha) && !empty($passRuleObj->cha) && !preg_match('/[a-zA-Z]{'.$passRuleObj->cha.',}/i', $_POST['npass1'])) {
			$err = 'New Password field must contain at least '.$passRuleObj->cha.' character';
		} elseif (isset($passRuleObj->spe) && empty($passRuleObj->spe) && preg_match('/[^a-zA-Z0-9]/i', $_POST['npass1'])) {
			$err = 'New Password field contain invalid character';
		} elseif (isset($passRuleObj->spe) && !empty($passRuleObj->spe) && preg_match('/[^a-zA-Z0-9'.$allowChars.']/i', $_POST['npass1'])) {
			$err = 'New Password field contain invalid character';
		} elseif (isset($passRuleObj->upp) && !empty($passRuleObj->upp) && !preg_match('/(?:[A-Z].*){'.$passRuleObj->upp.',}/', $_POST['npass1'])){
			$err = 'New Password contain at least '.$passRuleObj->upp.' uppercase character';
		} elseif (isset($passRuleObj->low) && !empty($passRuleObj->low) && !preg_match('/(?:[a-z].*){'.$passRuleObj->low.',}/', $_POST['npass1'])){
			$err = 'New Password contain at least '.$passRuleObj->low.' lowercase Character';
		} elseif (isset($passRuleObj->num) && !empty($passRuleObj->num) && !preg_match('/(?:[0-9].*){'.$passRuleObj->num.',}/', $_POST['npass1'])){
			$err = 'New Password contain at least '.$passRuleObj->num.' number';
		}
		
		if (empty($err)) {
			if ($_POST['npass1'] !== $_POST['npass2']) $err = "Password mismatched";
		}

		if (empty($err)) {
			if ($_POST['npass1'] === $_POST['opass']) $err = "New and Old Password same";
		}

		if (empty($err)) {
			$is_valid_credentials = $agent_model->getValidAgent(UserAuth::getCurrentUser(), $_POST['opass']);
			if (empty($is_valid_credentials)) $err = 'Old Password incorrect';
		}
		
		if (empty($err)) {
			$isPasswordUsed = $agent_model->isAgentPassExist(UserAuth::getCurrentUser(), $_POST['npass1']);
			if ($isPasswordUsed) $err = 'The last '.$agent_model->passRepeatNo.' used password can not be repeated.';
		}

		return $err;
	}
	
	function actionRules(){
		include('model/MPassword.php');
		$pass_model = new MPassword();
		$errMsg = '';
		$errType = 1;
		
		if (isset($_POST['submit'])) {
			$errMsg = $this->getPassValidMsg($pass_model);
			if (empty($errMsg)) {
				$totalRul = isset($_POST['totalRule']) && !empty($_POST['totalRule']) ? (int)$_POST['totalRule'] : 0;
				for ($i = 1; $i <= $totalRul; $i++) {
					$ruleKey = "rule".$i;
					$statusKey = "status".$i;
					$oldRuleKey = "old_rule".$i;
					$oldStatusKey = "old_status".$i;
					$status = isset($_POST[$statusKey]) && $_POST[$statusKey]=="status" ? 'Y' : 'N';
					$oldStatus = isset($_POST[$oldStatusKey]) && !empty($_POST[$oldStatusKey]) ? $_POST[$oldStatusKey] : 'N';
					$ruleValue = isset($_POST[$ruleKey]) && !empty($_POST[$ruleKey]) ? trim($_POST[$ruleKey]) : '';
					$oldRuleValue = isset($_POST[$oldRuleKey]) && !empty($_POST[$oldRuleKey]) ? trim($_POST[$oldRuleKey]) : '';
					if ($status != $oldStatus || $ruleValue != $oldRuleValue){
						if ($i != 7) $ruleValue = (int)$ruleValue;
						$pass_model->updatePasswordRule($i, $status, $ruleValue);
					}
				}				
				$errType = 0;
				$errMsg = "Password validation rules updated successfully.";
			}
		}		
		
		$data['passRules'] = $pass_model->getPasswordRules();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Password Validation Rules';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('password_rules', $data);
	}

	function getPassValidMsg($passModel=null)
	{
		$err = '';
		$totalRul = isset($_POST['totalRule']) && !empty($_POST['totalRule']) ? (int)$_POST['totalRule'] : 0;
		$isUpdated = false;
		$rule1value = "";
		$rule2value = "";
		$rule8value = "";
		$rule9value = "";
		for ($i = 1; $i <= $totalRul; $i++) {
			$ruleKey = "rule".$i;
			$oldRuleKey = "old_rule".$i;
			$statusKey = "status".$i;
			$oldStatusKey = "old_status".$i;
			$status = isset($_POST[$statusKey]) && $_POST[$statusKey]=="status" ? 'Y' : 'N';
			$oldStatus = isset($_POST[$oldStatusKey]) && !empty($_POST[$oldStatusKey]) ? $_POST[$oldStatusKey] : 'N';
			$ruleValue = isset($_POST[$ruleKey]) && !empty($_POST[$ruleKey]) ? trim($_POST[$ruleKey]) : '';
			$oldRuleValue = isset($_POST[$oldRuleKey]) && !empty($_POST[$oldRuleKey]) ? trim($_POST[$oldRuleKey]) : '';
						
			if (empty($ruleValue)){
				$err = "Rule ".$i."'s value is empty or 0!";
				break;
			}elseif($i != 7 && !preg_match("/^[0-9]+$/", $ruleValue)){
				$err = "Rule ".$i."'s value($ruleValue) is not valid number!";
				break;
			}elseif ($i == 7 && preg_match('/[a-zA-Z0-9]+/', $ruleValue)){
				$err = "Rule ".$i."'s value($ruleValue) contain regular character or number!";
				break;
			}elseif ($i > 2 && $i < 7 && $ruleValue > 5){
				$err = "Rule ".$i."'s value($ruleValue) must be a number from 1 to 5!";
				break;
			}
			if ($i == 1) $rule1value = (int)$ruleValue;
			elseif ($i == 2) $rule2value = (int)$ruleValue;
			elseif ($i == 8) $rule8value = (int)$ruleValue;
			elseif ($i == 9) $rule9value = (int)$ruleValue;

			if ($status != $oldStatus || $ruleValue != $oldRuleValue){
				$isUpdated = true;
			}
		}
		if ($rule1value >= $rule2value){
			$err = "Minimum length must not greater than or equal to maximum length of password!";
		}
		if ($rule9value >= $rule8value){
			$err = "Rule 9's value must not greater than or equal to rule 8's value!";
		}
		if (!$isUpdated && empty($err)){
			$err = "No change found!";
		}

		return $err;
	}
}
