<?php

class DBManager
{
	var $db_host_s;
	var $db_user_s;
	var $db_pass_s;
	var $db_s;
	var $db_host_u;
	var $db_user_u;
	var $db_pass_u;
	var $db_u;

	var $sel_conn;
	var $upd_conn;
	var $num_rows;
	var $affected_rows;
	var $insert_id;

	var $isSmaeBothConn;
	
	var $cctype;

	function __construct($db)
	{
		//echo "<br><br>CONSTRUCTOR<br><br>";
		$this->db_host_s = $db->select_host;
		$this->db_user_s = $db->select_user;
		$this->db_pass_s = $db->select_pass;
		$this->db_s = $db->select_db;
		
		$this->db_host_u = $db->update_host;
		$this->db_user_u = $db->update_user;
		$this->db_pass_u = $db->update_pass;
		$this->db_u = $db->update_db;
		
		if (empty($this->db_pass_u) || $this->db_host_s === $this->db_host_u) {
			$this->isSmaeBothConn = 1;
		} else {
			$this->isSmaeBothConn = 0;
		}
		
		$this->sel_conn = null;
		$this->upd_conn = null;
				
		$this->num_rows = 0;
		$this->affected_rows = 0;
		$this->insert_id = null;
		
		if (isset($db->cctype)) {
			$this->cctype = $db->cctype;
		} else {
			$this->cctype = 0;
		}
	}
	
	function __destruct()
	{
		//echo "<br><br>DESTRUCTOR<br><br>";
		if($this->sel_conn != null)
			$this->sel_conn->close();
		if($this->upd_conn != null)
			$this->upd_conn->close();
	}
	
	function getCCType()
	{
		if ($this->cctype == 1) {
			return 'CCPRO';
		} elseif ($this->cctype == 2) {
			return 'CCE';
		}
		
		return 'CLOUDCC';
	}
	
	function escapeString($str)
	{
		if (!empty($str)) {
			return mysqli_real_escape_string($this->getSelectConn(),$str);
		}
		return '';
	}
	
	function getSelectConn($isTestConnection=false)
	{
		if($this->sel_conn != null)	return $this->sel_conn;
		
		$dbname = '';
		
		if ($this->cctype == 1) {
			$dbname = $this->db_s;
		} else {
			$dbsuffix = UserAuth::getDBSuffix();
			if (!empty($dbsuffix)) {
				$dbname = $this->db_s . $dbsuffix;
			} else {
				$dbname = 'cc_master';
			}
		}
		if($isTestConnection) {
			$this->sel_conn = new mysqli($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			//$this->sel_conn = @mysql_connect($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			if (!$this->sel_conn) {
				$this->sel_conn =  null;
			} else {
				$this->sel_conn->select_db($dbname);
				//mysql_select_db($dbname, $this->sel_conn) or $this->sel_conn =  null;
			}
		} else {
			$this->sel_conn = new mysqli($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			//$this->sel_conn = mysql_connect($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			if (!$this->sel_conn) die ("Could not connect MySQL at $this->db_host_s");
			//mysql_select_db($dbname, $this->sel_conn) or die ("Could not open database ". $dbname);
			$this->sel_conn->select_db($dbname);
		}

		return $this->sel_conn;
	}
	
	function getUpdateConn()
	{
		if($this->isSmaeBothConn == 1)
		{
			if($this->sel_conn != null)	return $this->sel_conn;
			
			$connection = $this->getSelectConn();
			
			return $connection;
		}

		if($this->upd_conn != null)	return $this->upd_conn;
		
		$dbname = '';

                if ($this->cctype == 1) {
                        $dbname = $this->db_s;
                } else {
                        $dbsuffix = UserAuth::getDBSuffix();
                        if (!empty($dbsuffix)) {
                                $dbname = $this->db_s . $dbsuffix;
                        } else {
                                $dbname = 'cc_master';
                        }
                }
		
		//$this->upd_conn = mysql_connect($this->db_host_u, $this->db_user_u, $this->db_pass_u);
		$this->upd_conn = new mysqli($this->db_host_u, $this->db_user_u, $this->db_pass_u);
		if (!$this->upd_conn) die ("Could not connect");
		//mysql_select_db($dbname, $this->upd_conn) or die ("Could not open database..");
		$this->upd_conn->select_db($dbname);

		return $this->upd_conn;
	}
	
	function getNumRows()
	{
		return $this->num_rows;
	}

	function getInsertId()
	{
		return $this->insert_id;
	}
	
	function getAffectedRows()
	{
		return $this->affected_rows;
	}

	function dumpResult($sql)
	{
		$is_success = mysqli_query($this->getSelectConn(), $sql);
		if (!$is_success) {
			$err = mysqli_error($this->getSelectConn());
			echo $err . '<br />';
		}
		return $is_success;
	}

    function queryOnUpdateDB($stmt, $ignoreChar = array()){
        $key_words = array('information_schema','UNION','CAST','column_name','--', ';', '\\');
        foreach($key_words as $word)  {
            if (!in_array($word, $ignoreChar)) {
                $pos = stripos($stmt, $word);
                if ($pos !== false) {
                    $stmt = $stmt; //"";
                    break;
                }
            }
        }
        if (empty($stmt)) return false;

        $result = $this->getUpdateConn()->query($stmt);
        $err = $this->getUpdateConn()->error;
        if(!empty($err)){
            $obj['error']=$err;
            $obj['query']=$stmt;
            $this->saveMysqlErrorLog(json_encode($obj));
        }
        if (!empty($err) || $result->num_rows < 1)
            return null;
        $this->num_rows = $result->num_rows;
        $data = array();
        while($row = $result->fetch_object()) {
            $data[] = $row;
        }
        $result->free_result();
        return $data;
    }

	function query($stmt, $debug = false, $ignoreChar = array())
	{
		$key_words = array('information_schema','UNION','CAST','column_name','--', ';', '\\');
		foreach($key_words as $word)  {
			if (!in_array($word, $ignoreChar)) {
				$pos = stripos($stmt, $word);
				if ($pos !== false) {
					$stmt = $stmt; //"";
					break;
				}
			}
		}

		if (empty($stmt)) return false;

		$sql = explode(' ', $stmt, 2);
		$query_type = strtolower($sql[0]);
		// echo($stmt);
		
		
		if($query_type == 'select' || $query_type == 'desc') {

			//if($debug)
				//$this->echo_debug_msg($stmt);
	
			$result = $this->getSelectConn()->query($stmt);
			$err = $this->getSelectConn()->error;//mysql_error($this->getSelectConn());
			
			if(!empty($err)){
                $obj['error']=$err;
				$obj['query']=$stmt;
				$this->saveMysqlErrorLog(json_encode($obj));
            }
	
			//if ($debug)
				//$this->echo_debug_msg($err);
	
			if (!empty($err) || $result->num_rows < 1)
				return null;
	
			$this->num_rows = $result->num_rows; //mysql_num_rows($result);
			//echo 'asd'. $this->num_rows;
			$data = array();
	
			while($row = $result->fetch_object())
			{
				$data[] = $row;
			}
	
			//mysql_free_result($result);
			$result->free_result();
			
			return $data;

		} else {

			//$result = mysql_query($stmt, $this->getUpdateConn());
			$result = $this->getUpdateConn()->query($stmt);
			$err = $this->getUpdateConn()->error;
			//$err = mysql_error($this->getUpdateConn());
			/*
			if ($debug)
			{
				$this->echo_debug_msg("$stmt");
				$this->echo_debug_msg("Error: $err");
				$this->echo_debug_msg("Affected Rows: " . mysql_affected_rows($this->getUpdateConn()));
			}
			*/
			if(!empty($err)){
                $obj['error']=$err;
				$obj['query']=$stmt;
				$this->saveMysqlErrorLog(json_encode($obj));
            }
			
			if (!empty($err) || $this->getUpdateConn()->affected_rows < 1)
			{
			    //echo "<b>".$err."</b><br>";
			    //exit($stmt);
				return false;
			}
			else
			{
				$this->affected_rows = $this->getUpdateConn()->affected_rows;
				return true;
			}
		}
	}

	function validateQueryParams($condParams){ 
		$paramTypes = "";
		$bindParams = [];
		if(!empty($condParams)){ 
			foreach($condParams as $pkey => $pval){
				if(isset($pval['paramValue'])){
					// param char length
					$paramLength = isset($pval['paramLength']) && !empty($pval['paramLength']) ? $pval['paramLength'] : 32;
					// check for special chars
					$isSpecialCharCheck = isset($pval['specialCharCheck']) ? $pval['specialCharCheck'] : true; 
					
					if($isSpecialCharCheck == true){
						//$regex_str = "/[`'\"~!#$^&%*(){}<>,.?;:\|+=]/";
						$regex_str = "/[`'\"~!#$^&%*(){}<>,?;:\|+=]/";
						if(isset($pval['specialCharSkip']) && !empty($pval['specialCharSkip'])){
							$regex_str = str_replace($pval['specialCharSkip'], '', $regex_str);
						}

						$regex = $regex_str;	
						$paramRawVal = isset($pval['paramRawValue']) && !empty($pval['paramRawValue']) ? $pval['paramRawValue'] : $pval['paramValue'];
						$specialCharFound = preg_match($regex, $paramRawVal); 	
					}
					if( strlen($pval['paramValue']) > $paramLength){
						return [
							"result" => false,
							"msg" => "Param length exceed.",
							"field" => $pval['param']
						];
					}else if( ($isSpecialCharCheck == true) && ( isset($specialCharFound) && ($specialCharFound == true) ) ){ 
						return [
							"result" => false,
							"msg" => "Special character found in param string.",
							"field" => $pval['param']
						];
					}					
					$paramTypes .= $pval['paramType'];
					$bindParams[] =  $pval['paramValue'];
					
				}
			} 
		}
		return [
			"result" => true,
			"paramTypes" => $paramTypes,
			"bindParams" => $bindParams
		];
		
	}

	function executeQuery($sql, $paramTypes = "", $bindParams = []){
		$resArr = [];
		$stmt = $this->getSelectConn()->prepare($sql);
		
		if(!empty($bindParams)){
			$stmt->bind_param($paramTypes, ...$bindParams);			
		}

		$stmt->execute();
        //GPrint($stmt);die;
		$result = $stmt->get_result();
		while($row = $result->fetch_object()) {
			$resArr[] = $row;
		}
		$err = $this->getSelectConn()->error; 
		if (!empty($err) || empty($resArr) ){
			return null;

		}

		return $resArr; 
	}
	function executeInsertQuery($sql, $paramTypes = "", $bindParams = []){
		$stmt = $this->getUpdateConn()->prepare($sql);

		if(!empty($bindParams)){
			$stmt->bind_param($paramTypes, ...$bindParams);			
		}
		
		$stmt->execute();
		$err = $this->getUpdateConn()->error; 
		if (!empty($err) || $this->getUpdateConn()->affected_rows < 1){
			return false;

		}
		return true;
	}
	
	function getLastInsertId(){
		return $this->getSelectConn()->insert_id;
	}

	function setCharset($str)
	{
		if (!empty($str)) {
			return mysqli_set_charset($this->getSelectConn(), $str);
		}
	}
	
	function saveMysqlErrorLog($errData){
//	    GPrint($errData);
//        $filename = "/usr/local/ccpro/dblog/mysql_error.log";
//        $file = fopen( $filename, "a+" );
//        if( $file == true) {
//            fwrite($file, "\n\n".$errData );
//            fclose($file);
//            //die($errData);
//        }
        return null;
    }
}

?>