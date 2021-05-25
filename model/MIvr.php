<?php

class MIvr extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getIvrOptionsForCallControl()
	{
		$options = array();
		$sql = "SELECT ivr_branch,skill_id,title,verified_call_only FROM ivr_xfer WHERE active='Y' ORDER BY ivr_branch";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			foreach ($result as $ivr) {
				$options[] = $ivr;
			}
		}
		
		return $options;
	}
	
	function getIvrOptions()
	{
		$options = array();
		$sql = "SELECT ivr_id, ivr_name FROM ivr ORDER BY ivr_name ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $ivr) {
				$options[$ivr->ivr_id] = $ivr->ivr_name;
			}
		}
		return $options;
	}

	function getIvrs($offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT ivr_id, ivr_name, dtmf_timeout, ivr_timeout, timeout_action, ".
			"value, language FROM ivr ";
		$sql .= "ORDER BY ivr_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function isLeafNode($node)
	{
		$sql = "SELECT ivr_id FROM ivr_tree WHERE branch LIKE '$node" . "_' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? false : true;
	}
	
	function moveNode($from_node, $to_node, $ivr_name)
	{
		$len = strlen($from_node) + 1;
		$sql = "UPDATE ivr_tree SET branch=CONCAT('$to_node', SUBSTR(branch, $len)) WHERE branch like '$from_node%'";
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('IVR', 'U', "IVR=".$ivr_name, "Moved node $from_node to $to_node");
			return true;
		}

		return false;
	}
	
	function getMaxBranchLength($node)
	{
		$sql = "SELECT MAX(LENGTH(branch)) AS max_len FROM ivr_tree WHERE branch LIKE '$node%'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$maxlen = $result[0]->max_len;
			if (empty($maxlen)) return 0;
			return $maxlen;
		}
		return 21;
	}
	
	function insertBeforeNode($from_node, $ivr_name)
	{
		$to_node = substr($from_node, 0, -1) . 'Z';
		$to_node_real = substr($from_node, 0, -1) . '.';
		$len = strlen($from_node);
		$sql = "UPDATE ivr_tree SET branch=CONCAT('$to_node', SUBSTR(branch, $len)) WHERE branch like '$from_node%'";
		if ($this->getDB()->query($sql)) {
			
			$this->addIVRDetailsByBranch(array('ivr_id'=>substr($from_node, 0, 2), 
				'branch'=>$to_node, 'event'=>'AN', 'event_key'=>'', 'text'=>'Inserted Node'));

			$len++;
			$sql = "UPDATE ivr_tree SET branch=CONCAT('$to_node_real', SUBSTR(branch, $len)) WHERE branch like '$to_node%'";
			$this->getDB()->query($sql);
			
			$this->addToAuditLog('IVR', 'U', "IVR=".$ivr_name, "Inserted above node $from_node");
			return true;
		}

		return false;
	}
	
	function getIVRDetails($ivrid='')
	{
		if (empty($ivrid)) return null;
		$sql = "SELECT branch, event, event_key, text,arg, arg2 FROM ivr_tree WHERE ".
			"ivr_id='$ivrid' and branch not like '{$ivrid}TO%' ORDER BY branch";
		return $this->getDB()->query($sql);
	}
	function getMultiIVRAsrData($ivrid,$branches)
	{  
		if (!empty($ivrid) && is_array($branches) && !empty($branches)){
			$this->getDB()->setCharset("utf8");
			$branches = "('" . implode("','", $branches) . "')";
			$sql = "SELECT * FROM ivr_asr_words WHERE branch IN $branches AND ivr_id='$ivrid'";
			return $this->getDB()->query($sql);
		}
		return null;
		
	}
	function GenerateTimeoutNode($ivrid){
		if (empty($ivrid)) return null;
		$sql = "INSERT IGNORE INTO ivr_tree SET ivr_id='$ivrid', branch='{$ivrid}T', event='AN', event_key='', arg='', text=''";
		if ($this->getDB()->query($sql)) {
			$sql = "INSERT IGNORE INTO ivr_tree SET ivr_id='$ivrid', branch='{$ivrid}TO', event='AN', event_key='', arg='', text=''";
			$this->getDB()->query($sql);
			$sql = "INSERT IGNORE INTO ivr_tree SET ivr_id='$ivrid', branch='{$ivrid}TO0', event='AN', event_key='', arg='', text=''";
			$this->getDB()->query($sql);
			$sql = "INSERT IGNORE INTO ivr_tree SET ivr_id='$ivrid', branch='{$ivrid}TO1', event='AN', event_key='', arg='', text=''";
			$this->getDB()->query($sql);
			return true;
		}
		return false;
	}
	function getIVRTimeoutDetails($ivrid){
		$result=$this->getFromDBIVRTimeoutDetails($ivrid);
		foreach ($result as &$ivrto){
			if($ivrto->branch==$ivrid ||  $ivrto->branch==$ivrid."T" ){
				$ivrto->event="--";
			}elseif($ivrto->branch==$ivrid."TO"){
				$ivrto->event="---";
				$ivrto->text="Timeout";
			}
			
		}
		return $result;
	} 
	private function getFromDBIVRTimeoutDetails($ivrid)
	{		
		if (empty($ivrid)) return null;
		$sql = "SELECT branch, event, event_key, text,arg FROM ivr_tree WHERE ".
			"branch='{$ivrid}T' or  branch like '".$ivrid."TO%' ORDER BY branch";
		$result=$this->getDB()->query($sql);
		if(!$result || count($result)==0){
			if($this->GenerateTimeoutNode($ivrid)){
				return $this->getIVRTimeoutDetails($ivrid);
			}
		}else{
			return $result;
		}		
	}

	function getIVRDetailsByBranch($branch='')
	{
		if (empty($branch)) return null;

		$sql = "SELECT * FROM ivr_tree WHERE branch='$branch'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	function getIVRMultiFiles($tag)
	{
		if (empty($tag)) return null;

		$sql = "SELECT * FROM ivr_multi_prompt WHERE tag='$tag' ORDER BY sl ASC";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result : null;
	}

	function getIVRById($ivrid)
	{
		$sql = "SELECT * FROM ivr WHERE ivr_id='$ivrid'";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function getParent($branch='')
	{
		if (strlen($branch) <= 1) return null;
		$branch = substr($branch, 0, strlen($branch)-1);
		$sql = "SELECT * FROM ivr_tree WHERE branch='$branch'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function getPlayFile($file_name)
	{
		if (empty($file_name)) return null;

		$sql = "SELECT * FROM ivr_tree WHERE event IN ('PF','AN','ID','GV') AND event_key='$file_name'";
		return $this->getDB()->query($sql);
	}

	function addIVRDetailsByBranch($data)
	{
		if (!is_array($data))	return false;
		if (count($data) <= 0)	return false;
		$insert = '';
		if ($data['event'] == 'CP') {
			$data['event_key'] = 'Y';
		}
		
		foreach ($data as $key=>$value) {
			if (!empty($insert)) $insert .= ", ";
			$insert .= "$key='".$this->getDB()->escapeString($value)."'";
		}
		if ($data['event'] == 'PF' || $data['event'] == 'AN' || $data['event'] == 'UI') {
			$insert .= ", ";
			$insert .= "TTS_update='Y'";
		}
		$sql = "INSERT INTO ivr_tree SET $insert";
		return $this->getDB()->query($sql, false, array('\\'));
	}

	function getIVRChildrenDetails($branch='', $select = '')
	{
		if (empty($branch)) return null;
		$sql = "SELECT $select FROM ivr_tree WHERE branch LIKE '$branch"."_' ORDER BY branch";
		return $this->getDB()->query($sql);
	}

	function updateIVRDetailsByBranch($branch, $data)
	{
		if (empty($branch))	return false;
		if (!is_array($data))	return false;
		if (count($data) <= 0)	return false;

		$insert = '';
		if ($data['event'] == 'CP') {
                        $data['event_key'] = 'Y';
                }

		foreach ($data as $key=>$value) { 
			if (!empty($insert)) $insert .= ", ";
			$insert .= "$key='".$this->getDB()->escapeString($value)."'";
		}
		$sql = "UPDATE ivr_tree SET $insert WHERE branch='$branch'";
		return $this->getDB()->query($sql, false, array('\\'));
	}

	function insertOrUpdateIVRAsrWords($ivrId, $branch, $node, $data)
	{
		if(!empty($ivrId) && !empty($branch) && $node != '' && !empty($data) && is_array($data)){
			$this->getDB()->setCharset("utf8");
			$setSql = '';
			foreach ($data as $key => $value) { 
				if (!empty($setSql)) $setSql .= ", ";
				$setSql .= "$key='".$this->getDB()->escapeString($value)."'";
			}
			$newrecord = true;
			$sql = "INSERT INTO ivr_asr_words SET $setSql, ivr_id='$ivrId', branch='$branch', node='$node'";
			$asrData = $this->getIVRAsrWords($ivrId, $branch, $node);
			if(!empty($asrData)){
				$newrecord = false;
				$sql = "UPDATE ivr_asr_words SET $setSql WHERE ivr_id='$ivrId' AND branch='$branch' AND node='$node'";
			}
			$result = $this->getDB()->query($sql, false, array('\\'));
			return [
				'result' => $result,
				'is_new_record' => $newrecord
			];
		}
		return [
			'result' => false
		];
	}
	function removeIVRAsrWords($ivrId, $branch, $node)
	{
		if(!empty($ivrId) && !empty($branch) && $node != ''){
			$this->getDB()->setCharset("utf8");
			$sql = "DELETE FROM ivr_asr_words WHERE ivr_id='$ivrId' AND branch='$branch' AND node='$node'";
			return $this->getDB()->query($sql);
		}
		
	}
	function getIVRAsrWords($ivrId, $branch, $node)
	{
		if(!empty($ivrId) && !empty($branch) && $node != ''){
			$this->getDB()->setCharset("utf8");
			$sql = "SELECT * FROM ivr_asr_words WHERE ivr_id='$ivrId' AND branch='$branch' AND node='$node'";
			$result = $this->getDB()->query($sql);
			return is_array($result) ? $result[0] : null;
		}
		return null;
		
	}

	function updateIVRMultiPrompt($tag, $ivr_menu, $data, $file_name)
	{ 
		$branch = $ivr_menu->branch;
		$ivr_id = $ivr_menu->ivr_id;
		if(empty($ivr_menu->arg2)){
			$sql = "UPDATE ivr_tree SET arg2 = '$tag' WHERE branch='$branch'";
			$this->getDB()->query($sql, false, array('\\'));
		}
	
		foreach($data as $lan => $lanVal){
			if(isset($lanVal['name']['0'])){
				unset($lanVal['name']['0']);
			}
			$langFiles[$lan] = count($lanVal['name']);
			

			if(!empty($lanVal['name'])){
				foreach($lanVal['name'] as $key => $val){ 
					$sl = $key;
					// $fileId = $tag."_".$lan."_".$key;
					$fileId = $file_name.$sl;
					$selSql = "SELECT * FROM ivr_multi_prompt WHERE sl='$sl' AND tag='$tag' LIMIT 1";
					$result = $this->getDB()->query($selSql, false, array('\\'));
					
					if(empty($result)){
						$sql = "INSERT INTO ivr_multi_prompt (ivr_id,tag,sl,file_id) VALUES ('$ivr_id','$tag','$sl','$fileId')";
						$this->getDB()->query($sql, false, array('\\'));
					}else{
						$updateSql = "UPDATE ivr_multi_prompt SET file_id='$fileId' WHERE sl='$sl' AND tag='$tag'";
						$this->getDB()->query($updateSql, false, array('\\'));

					}
					
				}
			}
		}
		
		
	}


	function updateChildrenBranch($old_branch, $new_branch)
	{
		if(strlen($old_branch) <= 1 || substr($old_branch, 0, -1) != substr($new_branch, 0, -1)) return false;
		$branch_length = strlen($new_branch) + 1;

		$sql = "UPDATE ivr_tree SET branch=CONCAT('$new_branch', SUBSTRING(branch, $branch_length)) WHERE branch LIKE '$old_branch%'";
		return $this->getDB()->query($sql);
	}
	
	function getSubTreeByBranch($branch)
	{
		if(empty($branch)) return null;
		
		$sql = "SELECT * FROM ivr_tree WHERE branch LIKE '$branch%'";
		return $this->getDB()->query($sql);
	}

	function removeBranchOnly($branch)
	{
		if (empty($branch)) return false;
		$ivrid = substr($branch, 0, 2);
		$len = strlen($branch);
		$prefix = substr($branch, 0, -1);
		
		$sql = "SELECT COUNT(ivr_id) AS numrows FROM ivr_tree WHERE ivr_id='$ivrid' AND branch LIKE '$prefix%' AND $len=LENGTH(branch)";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$numrows = $result[0]->numrows;
			if ($numrows > 1) return false;
		}

		
		$sql = "DELETE FROM ivr_tree WHERE branch='$branch'";
		$is_removed = $this->getDB()->query($sql);
		
		if ($is_removed) {
			$len++;
			$sql = "UPDATE ivr_tree SET branch=CONCAT('$prefix', SUBSTR(branch, $len)) WHERE ivr_id='$ivrid' AND  branch LIKE '$branch%'";
			$this->getDB()->query($sql);
			$ivrinfo = $this->getIVRById($ivrid);
			$ivr_name = empty($ivrinfo) ? $ivrid : $ivrinfo->ivr_name;
			$dtmf_txt = '';
			if (strlen($branch) > 1) {
				$dtmfs = substr($branch, 1);
				$dtmf_length = strlen($dtmfs);
				$dtmf_txt = '';
				for ($i=0; $i<$dtmf_length; $i++) {
					if (!empty($dtmf_txt)) $dtmf_txt .= '->';
					$dtmf_txt .= $dtmfs[$i];
				}
			}
			if (empty($dtmf_txt)) $dtmf_txt = 'Root Node';
			$audit_text = "Number of nodes=1, DTMF $dtmf_txt";
			$this->addToAuditLog('IVR', 'D', "IVR=".$ivr_name, $audit_text);
		}
		
		return $is_removed;
	}
	
	function removeIvrMultiFiles($branch,$tag){
		$sql = "UPDATE ivr_tree SET arg2 = '' WHERE branch='$branch' AND arg2 = '$tag'";
		$res = $this->getDB()->query($sql);
		if($res){
			$delSql = "DELETE FROM ivr_multi_prompt WHERE tag = '$tag'";
			$this->getDB()->query($delSql); 
		}
		
	}
	function getIvrMultiFileList($tag)
	{
		$sql = "SELECT sl, file_id FROM ivr_multi_prompt WHERE tag='$tag'";
		$result = $this->getDB()->query($sql); 
		$list = [];
		if(!empty($result)){
			foreach($result as $val){
				$list[$val->sl]  = $val->file_id;
			}
		}
        return $list;
	}
	
	function clearBranchFile($branch, $key)
	{ 
		$sql = "UPDATE ivr_tree SET event_key='' WHERE branch='$branch' AND event_key='$key'";
		return $this->getDB()->query($sql);
	}
	/**
	 * delete multi prompt file
	 */
	function clearBranchMultiFile($branch, $key, $fileid, $tag)
	{ 
		$sql = "DELETE FROM ivr_multi_prompt WHERE file_id='$fileid' AND tag='$tag'";
		$this->getDB()->query($sql);

		$sql1 = "SELECT * FROM ivr_multi_prompt WHERE tag='$tag'";
		$hasData = $this->getDB()->query($sql1);
		// update table if multi prompt has not data
		if(!count($hasData)){
			$uSql = "UPDATE ivr_tree SET arg2='' WHERE branch='$branch' AND event_key='$key'";
			$this->getDB()->query($uSql);
		}
		
	}
	
	function removeBranch($branch)
	{
		if (empty($branch)) return false;
		
		$sql = "DELETE FROM ivr_tree WHERE branch LIKE '$branch%'";
		$is_removed = $this->getDB()->query($sql);
		
		if ($is_removed) {
			$audit_text = '';
			$num_nodes_affected = $this->getDB()->getAffectedRows();
			$ivr_id = substr($branch, 0, 2);
			$ivrinfo = $this->getIVRById($ivr_id);
			$ivr_name = empty($ivrinfo) ? $ivr_id : $ivrinfo->ivr_name;
			if (strlen($branch) > 1) {
				$dtmfs = substr($branch, 1);
				$dtmf_length = strlen($dtmfs);
				$dtmf_txt = '';
				for ($i=0; $i<$dtmf_length; $i++) {
					if (!empty($dtmf_txt)) $dtmf_txt .= '->';
					$dtmf_txt .= $dtmfs[$i];
				}
				$audit_text = "Number of nodes=$num_nodes_affected starting with DTMF $dtmf_txt";
			} else {
				$audit_text = "Whole tree with number of nodes=$num_nodes_affected";
			}
			$this->addToAuditLog('IVR', 'D', "IVR=".$ivr_name, $audit_text);
		}
		
		return $is_removed;
	}

	function updateIVRByID($oldivr, $ivr, $extra_audit_text, $value_options, $isTTS, $isTTSUpdate)
	{
		if (empty($oldivr->ivr_id)) return false;
		$is_update = false;
		$fields_array = array(
			'ivr_name' => 'ivr_name',
			'dtmf_timeout' => 'dtmf_timeout',
			'ivr_timeout' => 'ivr_timeout',
			'timeout_action' => 'timeout_action',
			'dayhour' => 'dayhour',
			'welcome_voice' => 'welcome_voice',
			//'debug_mode' => 'debug_mode',
			'debug_cli_filter' => 'debug_cli_filter',
			'debug_expire_time' => 'debug_expire_time',
			'language' => 'language',
			'value' => 'value'
		);
		
		if ($isTTS) {
			$fields_array['TTS'] = 'TTS';
		}
		
		if ($isTTSUpdate) {
			$fields_array['TTS_update'] = 'TTS_update';
			$ivr->TTS_update = 'Y';
		}
		if (!empty($ivr->debug_cli_filter) && isset($oldivr->debug_cli_filter) && $oldivr->debug_cli_filter != $ivr->debug_cli_filter){
			$ivr->debug_expire_time = time() + 6*60*60;
		}else {
			$ivr->debug_expire_time = $oldivr->debug_expire_time;
		}
		$changed_fields = $this->getSqlOfChangedFields($oldivr, $ivr, $fields_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE ivr SET $changed_fields WHERE ivr_id='$oldivr->ivr_id'";
			$is_update = $this->getDB()->query($sql);
		}

		if ($is_update || !empty($extra_audit_text)) {
			$field_names = array(
				'ivr_name' => 'IVR name',
				'dtmf_timeout' => 'DTMF timeout',
				'ivr_timeout' => 'IVR timeout',
				'timeout_action' => 'Timeout event',
				'welcome_voice' => 'Welcome voice',
				'TTS' => 'TTS',
				//'debug_mode' => 'Debug mode',
				'debug_cli_filter' => 'Debug CLI Filter',
				'language' => 'Language',
//				'TTS_update' => 'TTS Text',
				'value' => 'Skill'	//depends on timeout event, this time only stores skill
			);

			$audit_text = $extra_audit_text . $this->getAuditText($oldivr, $ivr, $fields_array, $field_names, $value_options);
			if (isset($ivr->TTS_update)) $audit_text .= 'TTS Text changed';
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('IVR', 'U', "IVR=".$oldivr->ivr_name, $audit_text);
		}
		
		return $is_update;
	}
	
	function numTransferToIVR($title='', $skill_id=''){
		$cond = '';
		$sql = "SELECT COUNT(ivr_branch) AS numrows FROM ivr_xfer ";
		if (!empty($title)) $cond .= "title LIKE '$title%'";
		if (!empty($skill_id)) {
			$cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY ivr_branch ";
		$result = $this->getDB()->query($sql);
		
		// if($this->getDB()->getNumRows() == 1) {
		// 	return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		// }
		
		return count($result);
	}
	
	function getTransferToIVR($title='', $skill_id='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM ivr_xfer ";
		if (!empty($title)) $cond .= "title LIKE '$title%'";
		if (!empty($skill_id)) {
			$cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY ivr_branch ";
		$sql .= "ORDER BY title ASC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		// echo $sql;
	
		return $this->getDB()->query($sql);
	}
	
	function getIVRTranById($tid='')
	{
		$sql = "SELECT * FROM ivr_xfer WHERE ivr_branch='$tid'";
		$result = $this->getDB()->query($sql);

		return is_array($result) ? $result : null;
	}
	
	function isIVRBranchExist($node)
	{
		$sql = "SELECT ivr_id FROM ivr_tree WHERE branch='$node' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? true : false;
	}
	
	function isIVRAndBranchExist($ivr_id, $node)
	{
		$sql = "SELECT ivr_id FROM ivr_tree WHERE ivr_id='$ivr_id' AND branch='$node' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? true : false;
	}

	function saveTransferToIVR($dataObj){
		// $xferId = $this->getNextTransIVRId();
		// if (empty($xferId)) {
		// 	return false;
		// }
		$sql = "INSERT INTO ivr_xfer SET ".
				// "id='$xferId', ".
				"skill_id='$dataObj->skill_id_transfer', ".
				"title='$dataObj->title', ".
				"ivr_branch='$dataObj->ivr_branch', ".
				"verified_call_only='$dataObj->verified_call_only', ".
				"active='$dataObj->active'";
		
		// echo $sql;

		if ($this->getDB()->query($sql)) {
			return true;
		}
		return false;
	}
	
	function updateTransferToIVR($oldDataObj, $dataObj)
	{
		if (empty($oldDataObj->id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
	
		if ($dataObj->title != $oldDataObj->title) {
			$changed_fields .= "title='$dataObj->title'";
			$ltext = "Title=$oldDataObj->title to $dataObj->title";
		}
		if ($dataObj->skill_id != $oldDataObj->skill_id) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "skill_id='$dataObj->skill_id'";
			$ltext = $this->addAuditText($ltext, "skill_id changed");
		}
		if ($dataObj->ivr_branch != $oldDataObj->ivr_branch) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "ivr_branch='$dataObj->ivr_branch'";
			$ltext = $this->addAuditText($ltext, "ivr_branch changed");
		}
		if ($dataObj->verified_call_only != $oldDataObj->verified_call_only) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "verified_call_only='$dataObj->verified_call_only'";
			$ltext = $this->addAuditText($ltext, "verified_call_only=$oldDataObj->verified_call_only to $dataObj->verified_call_only");
		}
		if ($dataObj->active != $oldDataObj->active) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "active='$dataObj->active'";
			$ltext = $this->addAuditText($ltext, "Status=$oldDataObj->active to $dataObj->active");
		}

		if (!empty($changed_fields)) {
			$sql = "UPDATE ivr_xfer SET $changed_fields WHERE id='$oldDataObj->id'";
			$is_update = $this->getDB()->query($sql);
		}
	
		if ($is_update) {
			$this->addToAuditLog('Transfer to IVR', 'U', "Title=".$oldDataObj->title, $ltext);
		}
	
		return $is_update;
	}
	
	function deleteTransferToIVR($tid)
	{
		if (!empty($tid)) {
			// $sql = "DELETE FROM ivr_xfer WHERE id='$tid' LIMIT 1";
			$sql = "DELETE FROM ivr_xfer WHERE ivr_branch='$tid' ";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Transfer to IVR', 'D', "IvrBranch=$tid", "");
				return true;
			}
		}
		return false;
	}
	
	function getNextTransIVRId()
	{
		$id = '';
		$sql = "SELECT MAX(id) AS max_id FROM ivr_xfer";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			if (!empty($result[0]->max_id)) $id = $result[0]->max_id;
		}
		if (empty($id)) return 'A';
		if ($id == 'Z') return '';
		$id++;
		return $id;
	}
	function getIvrBranchNamesArray($ivr_id='')
	{
		$returnArray=array();
		$sql = "SELECT ivr_id, branch, text FROM ivr_tree ";
		if (!empty($ivr_id)){
            $sql .= "WHERE  ivr_id='{$ivr_id}' ";
        }
        $sql .= " ORDER BY branch ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->branch]=$data->text;
			}
		}
		return $returnArray;
	}

    function hasTransferIvrBranchAdded($branch)
    {
        $sql = "SELECT * FROM ivr_xfer ";
        $sql .= " WHERE ivr_branch = '$branch'";
        $result = $this->getDB()->query($sql);

        if (is_array($result) && count($result) > 0) {
            return true;
        }
        return false;
    }

    // function hasIvrSkillBranchAdded($skill_id, $branch)
    // {
    //     $sql = "SELECT * FROM ivr_xfer ";
    //     $sql .= " WHERE skill_id = '$skill_id'";
    //     $sql .= " AND ivr_branch = '$branch'";
    //     $result = $this->getDB()->query($sql);

    //     if (is_array($result) && count($result) > 0) {
    //         return true;
    //     }
    //     return false;
    // }

    function removeExistingTransferToIVR($ivr_branch)
    {
        $sql = "DELETE FROM ivr_xfer WHERE ivr_branch='$ivr_branch' ";
        // echo $sql;

		return $this->getDB()->query($sql);
    }

    function getTransferBranchSkillList($branch)
    {
        $sql = "SELECT skill_id FROM ivr_xfer ";
        $sql .= " WHERE ivr_branch = '$branch'";
        $result = $this->getDB()->query($sql);

        return $result;
    }
}

?>