<?php
error_reporting(0);

include_once('../../lib/DBManager.php');

class gPlex_CC_Service {

	private $dbConn = null;
	private $_authenticationHeaderPresent = false;
	
	public  function  __construct()
	{
		include_once('../../conf.php');
		$this->dbConn = new DBManager($db);
	}
	
	/**
	 * Authenticates the SOAP request. (This one is the key to the authentication, it will be called upon the server request)
	 *
	 * @param string
	 * @return bool|Exception
	 */
	public function authenticate($credential)
	{
		$this->_authenticationHeaderPresent = true;
		
		if (!empty($credential->user) && !empty($credential->password)) {
			
			$pass = trim($credential->password);
			$user = trim($credential->user);//API_XML-RPC
			
			$page = '';
			$result = $this->dbConn->query("SELECT page FROM page_access WHERE page='$user' AND password='$pass' LIMIT 1");
		
			if (is_array($result)) {
				$page = $result[0]->page;
			}
		
			if ($page != $user) {
				throw new SOAPFault("Authentication failed. Credential error.", 401);
			}
		
			return true;
			
		} else {
			throw new SOAPFault("Authentication failed. Credential error.", 401);
		}
	}
	
	
	
	
	/**
     * Adds number(s) in a campaign lead
     *
     * @param string $LeadID
     * @param array $Numbers
     * @return Object|Exception
     */	 
	public function AddLeadNumbers($LeadID, $Numbers) 
	{
		if (!$this->_authenticationHeaderPresent) {
			throw new SOAPFault("Authentication failed. Credential error.", 401);
		}
		
		if(strlen($LeadID) != 4) {
			return $this->send_error('Invalid Lead ID');
		}
		
		
		$result = $this->dbConn->query("SELECT lead_id FROM lead_profile WHERE lead_id='$LeadID' LIMIT 1");
		
		$leadid = '';
		
		if (is_array($result)) {
			$leadid = $result[0]->lead_id;
		}
		
		if (empty($leadid)) {
			return $this->send_error('Wrong Lead ID (' . $LeadID . ')');
		}
		
		$response = new stdClass();		
		
		if (is_array($Numbers)) {
			if (count($Numbers) > 100) {
				return $this->send_error('Numbers array is too large');
			}
			
			$response->Result = 1;
			//$response->ErrorMessage = '';
			$response->NumSucceeded = 0;
			$response->NumFailed = 0;
			$response->FailedNumbers = array();
			
			foreach ($Numbers as $number) {
				$resp = $this->add_lead_number($LeadID, $number);
				if (strlen($resp) > 0) {
					$response->Failed++;
					$nm = new stdClass();
					$nm->Number = $number;
					$nm->ErrorMsg = $resp;
					$response->FailedNumbers[] = $nm;
				} else {
					$response->NumSucceeded++;
				}
			}
			
			$mdate = date("Y-m-d");
			$sql = "SELECT COUNT(lead_id) AS total FROM leads WHERE lead_id='$LeadID'";
			$result = $this->dbConn->query($sql);
			//echo $sql;
			$num_count = 0;
			if (is_array($result)) {
				if (empty($result[0]->total)) $result[0]->total = 0;
				$num_count = $result[0]->total;
			}
			
			$sql = "UPDATE lead_profile SET modify_date='$mdate', number_count='$num_count' WHERE lead_id='$LeadID' LIMIT 1";
			$this->dbConn->query($sql);
			
		} else {
			return $this->send_error('Numbers should be an array');
		}
				
		return $response;
	}
	
	private function add_lead_number($lead_id, $number) 
	{
	
		$number_1 = isset($number->Number) ? trim($number->Number) : '';
		$number_2 = isset($number->AltNumber1) ? trim($number->AltNumber1) : '';
		$number_3 = isset($number->AltNumber2) ? trim($number->AltNumber2) : '';
		$number_4 = isset($number->AltNumber3) ? trim($number->AltNumber3) : '';
		$custom_value_1 = isset($number->CustomValue1) ? trim($number->CustomValue1) : '';
		$custom_value_2 = isset($number->CustomValue2) ? trim($number->CustomValue2) : '';
		$custom_value_3 = isset($number->CustomValue3) ? trim($number->CustomValue3) : '';
		$custom_value_4 = isset($number->CustomValue4) ? trim($number->CustomValue4) : '';
		$title = isset($number->Title) ? trim($number->Title) : '';
		$fname = isset($number->FirstName) ? trim($number->FirstName) : '';
		$lname = isset($number->LastName) ? trim($number->LastName) : '';
		$street = isset($number->Street) ? trim($number->Street) : '';
		$city = isset($number->City) ? trim($number->City) : '';
		$state = isset($number->State) ? trim($number->State) : '';
		$zip = isset($number->Zip) ? trim($number->Zip) : '';
		$agent_altid = isset($number->AgentAltID) ? trim($number->AgentAltID) : '';
	
		if(strlen($number_1) == 0 || !is_numeric($number_1)) {
			return 'Invalid Number';
		}
		if(strlen($number_2) > 0 && !is_numeric($number_2)) {
			return 'Invalid AltNumber1';
		}
		if(strlen($number_3) > 0 && !is_numeric($number_3)) {
			return 'Invalid AltNumber2';
		}
		if(strlen($number_4) > 0 && !is_numeric($number_4)) {
			return 'Invalid AltNumber3';
		}
	
		$sql = "INSERT INTO leads SET lead_id='$lead_id',".
				"title='$title',first_name='$fname',last_name='$lname',".
				"street='$street',city='$city',state='$state',zip='$zip',".
				"dial_number='$number_1',dial_number_2='$number_2',".
				"dial_number_3='$number_3',dial_number_4='$number_4',".
				"custom_value_1='$custom_value_1',custom_value_2='$custom_value_2',".
				"custom_value_3='$custom_value_3',custom_value_4='$custom_value_4',".
				"agent_altid='$agent_altid'";
	
		if ($this->dbConn->query($sql)) {
			return '';
		} else {
			return "Server Error, Could not add record.";
		}
	
		return '';
	}
	
	function send_error($err)
	{
		$response = new stdClass();
		$response->Result = 0;
		$response->ErrorMsg = $err;
	
		return $response;;
	}
	
}
