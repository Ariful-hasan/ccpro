<?php

class MMOHFiller extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function syncVoice($sync_type, $sid, $file='')
	{
		$db_suff = UserAuth::getDBSuffix();
		$sql = "INSERT INTO cc_master.sync_web_uploads SET db_suffix='$db_suff', ".
				"update_type='$sync_type', update_id='$sid', upload_path='$file', update_time=NOW()";
		$this->getDB()->query($sql);
	}
	
	function notifyIVRVoiceFileUpdate($ivr_id, $file)
	{
		//$notify_txt = "WWW\r\nType: UPD_IVR_VOICE\r\nivr_id: $ivr_id\r\nvoice: $file\r\n";
		//$this->notifyUpdate($notify_txt);
		//$this->syncVoice('UPD_IVR_VOICE', $ivr_id, $file);
		include_once('lib/DBNotify.php');
		DBNotify::NotifyIVRUpdate(UserAuth::getDBSuffix(), $ivr_id, $file, $this->getDB());
	}
	
	function numTotal($skill_id)
	{
		$cond = '';
		$sql  = "SELECT COUNT(*) AS numrows FROM skill_moh_filler ";		
		$sql .= "WHERE skill_id='$skill_id' ";
		
		$result = $this->getDB()->query($sql);
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	function getTreeDetails($skillid,$skill_name='Skill',$type="Q")
	{		
		$sql = "SELECT branch, event, event_key, text,arg,filler_type FROM skill_moh_filler WHERE skill_id='$skillid' and filler_type='$type' ORDER BY branch ";
		$rdata= $this->getDB()->query($sql);
		$return=array();
		$obj=new stdClass();
		$obj->branch=$skillid;
		$obj->event="--";
		$obj->event_key="";
		$obj->text=$skill_name;
		$obj->arg="";
		$obj->filler_type="";
		$return[]=$obj;
		
		$mhtype=array('Q'=>'Queue','H'=>'Hold','X'=>'Queue Timeout');
		$isQadded=false;
		$isHadded=false;
		$isXadded=false;		
		if(is_array($rdata)){
			$branchPrefix="";
			foreach ($rdata as $rd){
				$currentPrefix=substr($rd->branch,0,3);
				if(empty($branchPrefix) || $currentPrefix!=$branchPrefix){					
					$firstkey=substr($rd->branch, 0,2);
					$secondkey=substr($rd->branch, 2,1);
					$obj=new stdClass();
					$obj->branch=$firstkey.$secondkey;
					$obj->event="--";
					$obj->event_key="";
					if($secondkey=="Q"){$isQadded=true;}
					elseif($secondkey=="X"){$isXadded=true;}
					elseif($secondkey=="H"){$isHadded=true;}					
					$obj->text=!empty($mhtype[$secondkey])?$mhtype[$secondkey]:"-";				
					$obj->arg="";
					$obj->filler_type=$type;
					$return[]=$obj;
					$branchPrefix=$firstkey.$secondkey;
				}				
				$return[]=$rd;
				
			}
		}else{
			
		}
		
		if($type=="Q" && !$isQadded){
			$obj=new stdClass();
			$obj->branch=$skillid."Q";
			$obj->event="--";
			$obj->event_key="";
			$obj->text="Queue";
			$obj->arg="";
			$obj->filler_type="Q";
			$return[]=$obj;
		}
		if(!$isHadded){
			$obj=new stdClass();
			$obj->branch=$skillid."H";
			$obj->event="--";
			$obj->event_key="";
			$obj->text="Hold";
			$obj->arg="";
			$obj->filler_type="H";
			$return[]=$obj;
		}
		if($type=="X" && !$isXadded){
			$obj=new stdClass();
			$obj->branch=$skillid."X";
			$obj->event="--";
			$obj->event_key="";
			$obj->text="End Call";
			$obj->arg="";
			$obj->filler_type="X";
			$return[]=$obj;
		}
		
		return $return;
	}
	function getFillerIDsArray($skill_id='')
	{
		$where="";
		if(!empty($skill_id)){
			$where=" WHERE skill_id='$skill_id' ";
		}
		$returnArray=array();
		$sql = "SELECT filler_id FROM skill_moh_filler $where ORDER BY filler_id ASC";
		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[]=$data->filler_id;
			}
		}
		return $returnArray;
	}
	function getUsedKey($branch)
	{	
		$returnArray=array();
		$sql = "SELECT branch FROM skill_moh_filler WHERE branch like '{$branch}_' ORDER BY branch ASC";
		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[]=$data->branch;
			}
		}
		return $returnArray;
	}
	function GetNewFilterId($prefix,$default='01')
	{
		$returnArray=array();
		$sql = "SELECT max(filler_id) as maxid FROM skill_moh_filler where filler_id like '$prefix%'";
		$result= $this->getDB()->query($sql);
		if($result && count($result)>0 && !empty($result[0]->maxid)){						
				$result[0]->maxid++;	
				return $result[0]->maxid;		
		}
		return $prefix.$default;
	}
	/**
	 * @param unknown $filler_id
	 * @deprecated this funciton has been deprecated
	 */
	function GetMOHFillerByFillerId($filler_id)
	{
		$sql = "SELECT * FROM  skill_moh_filler where filler_id ='{$filler_id}'";
		$result= $this->getDB()->query($sql);
		if($result && count($result)>0){			
			return $result[0];
		}
		return null;
	}
	function GetMOHFillerByBranch($branch)
	{
		$sql = "SELECT * FROM  skill_moh_filler where branch ='{$branch}' limit 1";
		$result= $this->getDB()->query($sql);
		if($result && count($result)>0){
			return $result[0];
		}
		return null;
	}

	function getAll($skill_id='', $type='', $offset=0, $limit=0)
	{		
		$sql = "SELECT *  FROM skill_moh_filler ";
		$cond = "skill_id='$skill_id'";
		if (!empty($type)) {
			if (!empty($cond)) $cond .= ' AND ';
			$cond.= " type='$type' ";
		}
		if (!empty($cond)) $sql .= "WHERE  $cond ";
		$sql .= "ORDER BY filler_id ";
		
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";	
		//echo $sql;	
		return $this->getDB()->query($sql);
	}
	function getValidationArray(){
		$fields=array(
				"skill_id"=>true,
				"filler_type"=>true,
				"filler_id"=>true,
				"event"=>true,
				"file_id"=>true,
				"text"=>true,
		
		);
	}
	
	function AddNewEntry($param=array(),$files,$path){		
			if(count($param)==0){ 
				return false;
			}
			$fileupload=array();
			$maxFileSize = $this->getTemplate()->maxFileSize;
			$isError = false;
			
			$isUploadable = false;
			if($files){ 
				foreach ($files['name'] as $lan=>$value){		
					if ($files["error"][$lan] != UPLOAD_ERR_NO_FILE){		
						if($files["error"][$lan]==0){
							$target_file = basename($value);
							$fileExt = pathinfo($target_file,PATHINFO_EXTENSION);

							$fileProp = exec('file -b '. $files["tmp_name"][$lan]);
							
							$check16Bit = strpos($fileProp, "16 bit");
							$checkMono = strpos($fileProp, "mono");
							$checkHz = strpos($fileProp, "8000 Hz");
							
							if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) &&  ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
								// size in bytes, 1000000 bytes = 1 MB
								if ($files["size"][$lan] <= $maxFileSize) {
									$fileupload[$lan]=$files["tmp_name"][$lan];
									$isUploadable = true;
								}else{
									$isError = true;
									$sizeInMB = $maxFileSize / 1000000;
									AddError("[$lan] Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB");
								}
							}else{ 
								$isError = true;
								AddError("[$lan] Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
							}
						}

					}		
				}
			}	

			if ($isUploadable){
			    $isError = false;
			}

			$path.='Q/' . $param['skill_id']."/FILLER/";
			if(!$isError){
				$is_uploaded=true;
				if($param['event']!="GO" && $param['event']!="LN" )	{		
					$filename = $param['event_key'];	
					$filenameWithExt = $filename.".".$this->getTemplate()->voice_store_file_extension;	

					foreach ($fileupload as $langg=>$temppath){	
						$fpath=$path.$langg."/";
						if(!is_dir($fpath)){
							mkdir($fpath,0755,true);
						}	
						
						if(file_exists($fpath.$filenameWithExt)){
							unlink($fpath.$filenameWithExt);
							unlink($fpath.$filename.".ulaw");
							unlink($fpath.$filename.".alaw");
							unlink($fpath.$filename.".txt");
						} 
						if(!move_uploaded_file($temppath, $fpath.$filenameWithExt)){ 
							$is_uploaded =false;
						}else {
		
							// create to auto genarated file
							$autoGenFileName = $fpath.$filename; 
							exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
							exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
							// create a file with real media file name
							$file = fopen($autoGenFileName.".txt","w");
							fwrite($file,$filenameWithExt);
							fclose($file);
						}

					}
					if(count($fileupload)==0){
						$param['event_key']="";
					}	
				}	
			
				if($is_uploaded){
					$sql = "INSERT INTO skill_moh_filler SET";
					foreach ($param as $key=>$value){
						$sql.=" ".$key."='$value',";
					}
					$sql=rtrim($sql,",");				
					if ($this->getDB()->query($sql)) { 
						//$this->syncVoice('UPD_SKILL_MOH', $param['skill_id']);
						// commented because this message was not found in the sender script, probably not working - masud
						include_once('lib/DBNotify.php');
						DBNotify::NotifySkillMOHUpdate(UserAuth::getDBSuffix(), $param['skill_id'], $this->getDB());
						return true;					
					}				
					return false;
				}else{
					AddError("File upload failed try again");
				}
			}
			
			
		}
		

		function UpdateByBranchFromPost($branch,$oldparam,$files,$path){
			$newparam=array();
			$maxFileSize = $this->getTemplate()->maxFileSize;
			$isError = false;
			foreach ($_POST as $nkey=>$nw){
				if(isset($oldparam->$nkey)){
					if($oldparam->$nkey!=$nw){
						$newparam[$nkey]=$nw;
					}
				}
			}


			$fileExt = "";
			$fileupload=array();
			$isUploadable = false;
			if($files){
				foreach ($files['name'] as $lan=>$value){
					if ($files["error"][$lan] != UPLOAD_ERR_NO_FILE){

						if($files["error"][$lan]==0){
							$target_file = basename($value);
							$fileExt = pathinfo($target_file,PATHINFO_EXTENSION);

							$fileProp = exec('file -b '. $files["tmp_name"][$lan]);
							
							$check16Bit = strpos($fileProp, "16 bit");
							$checkMono = strpos($fileProp, "mono");
							$checkHz = strpos($fileProp, "8000 Hz");

							if( ($check16Bit != false) && ($checkMono != false) && ($checkHz != false) &&  ($fileExt === $this->getTemplate()->voice_store_file_extension )  ){ 
								// size in bytes, 1000000 bytes = 1 MB
								if ($files["size"][$lan] <= $maxFileSize) {
									$fileupload[$lan]=$files["tmp_name"][$lan];
									$isUploadable = true;
								}else{
									$isError = true;
									$sizeInMB = $maxFileSize / 1000000;
									AddError("[$lan] Sorry, audio file is too large. Please upload file less than ".$sizeInMB." MB");
								}
							}else{
								$isError = true;
								AddError("[$lan] Invalid file type. The uploaded file is not 16 bit mono 8000 Hz WAV file");    	
							}
						}
					}
				}
			}

			if ($isUploadable){
			    $isError = false;
			}
			
			if(count($newparam)==0 && count($fileupload)==0){
				AddError("No Update found");
				return false;
			}
			if (empty($oldparam->event_key)) {
				$oldparam->event_key = $oldparam->skill_id.rand(1000,9999).time();
				$newparam['event_key'] = $oldparam->event_key;
			}
			$path.='Q/' . $oldparam->skill_id."/FILLER/";
			$fileExt =  $this->getTemplate()->voice_store_file_extension;
			$filename = $oldparam->event_key;
			$filenameWithExt = $oldparam->event_key.".".$fileExt;

			
			if(!$isError){
				$is_uploaded = true;
				foreach ($fileupload as $langg=>$temppath){
					$fpath=$path.$langg."/";
					if(!is_dir($fpath)){
						mkdir($fpath,0755,true);
					}
					if(file_exists($fpath.$filenameWithExt)){
						unlink($fpath.$filenameWithExt);
						unlink($fpath.$filename.".ulaw");
						unlink($fpath.$filename.".alaw");
						unlink($fpath.$filename.".txt");
					} 
					if(!move_uploaded_file($temppath, $fpath.$filenameWithExt)){ 
						$is_uploaded =false;
					}else {

						// create to auto genarated file
						$autoGenFileName = $fpath.$filename; 
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t al '.$autoGenFileName.'.alaw');
						exec('sox '.$autoGenFileName.'.wav -r 8000 -c 1 -t ul '.$autoGenFileName.'.ulaw');
						// create a file with real media file name
						$file = fopen($autoGenFileName.".txt","w");
						fwrite($file,$filenameWithExt);
						fclose($file);
					}
				}
				if($is_uploaded){
					if(count($newparam)>0){
					$sql = "UPDATE  skill_moh_filler SET";
					foreach ($newparam as $key=>$value){
						$sql.=" ".$key."='$value',";
					}
					$sql=rtrim($sql,",");
					$sql.=" WHERE branch='$branch' limit 1";
					if ($this->getDB()->query($sql)) {
						//$this->syncVoice('UPD_SKILL_MOH', $oldparam->skill_id);
						include_once('lib/DBNotify.php');
						DBNotify::NotifySkillMOHUpdate(UserAuth::getDBSuffix(), $oldparam->skill_id, $this->getDB());
						return true;
					}
					return false;
					}else{
						return true;
					}
				}else{
					AddError("File upload failed try again");
				}
			}	
			
		}

		function DeleteMOHFillerWithFile($branch,$path){
			$mohfiller=$this->GetMOHFillerByBranch($branch);
			if($mohfiller){
				$moh_model=new MMOHFiller();
				$mohdata=$moh_model->GetMOHFillerByBranch($branch);	
				$branchdeleted=false;
				$sql="DELETE FROM skill_moh_filler WHERE branch='$branch' limit 1";
				if ($this->getDB()->query($sql)) {
					$branchdeleted=true;
					}else{
					return false;
				}					
				if($branchdeleted && !empty($mohdata->event_key)){
					AddModel("MLanguage");
					foreach (MLanguage::getActiveLanguageListArray('A') as $lang=>$value){
						$target_path=$path.'Q/' . $mohdata->skill_id."/"."$lang/".$mohdata->event_key.".".$this->getTemplate()->store_file_extension;
						if(file_exists($target_path)){
							unlink($target_path);
						}						
					}
				}
				
				/*$brlength=strlen($branch);
				$branchupdated=false;
				if($branchdeleted){
					// It should be delete operation.
					$sql="UPDATE skill_moh_filler REPLACE(branch, '$branch', '".substr($branch,0 ,$brlength-1)."') WHERE branch like '$branch%' limit 1";
					
					if ($this->getDB()->query($sql)) {
						$branchupdated=true;
					}
				}	*/
				//$this->syncVoice('UPD_SKILL_MOH', $mohdata->skill_id);			
				include_once('lib/DBNotify.php');
				DBNotify::NotifySkillMOHUpdate(UserAuth::getDBSuffix(), $mohdata->skill_id, $this->getDB());
				return true;
			}
			return false;
		}
		
		
	}
	
	
	



?>