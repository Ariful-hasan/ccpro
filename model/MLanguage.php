<?php

class MLanguage extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getAllLanguage($key='',$offset=0, $limit=0,$status='')
	{
		$options = array();
		$sql = "SELECT * FROM language ";
		if(!empty($status)){
			$sql.= " WHERE status='$status' ";
		}
		if ($limit > 0) $sql .= " LIMIT $offset, $limit";
		//echo $sql; die;
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result;
		}
		return array();
	}
	public static function getActiveLanguageList($key='',$offset=0, $limit=0)
	{
		$obj=new self();
		$lang = $obj->getAllLanguage('',0,0,'A');
		$languages = array();
		
		if (is_array($lang)) {
		
		foreach ($lang as $_lang) {
			if ($_lang->lang_key == 'EN') {
				$languages[] = $_lang;
				break;
			}
		}
		
		foreach ($lang as $_lang) {
			if ($_lang->lang_key != 'EN') {
				$languages[] = $_lang;
			}
		}
		
		}
		
		return $languages;
	}
	public static function getActiveLanguageListArray($key='',$offset=0, $limit=0)
	{
		$return=array();
		$obj=new self();
		$result=$obj->getAllLanguage('',0,0,'A');
		foreach ($result as $r){
			$return[$r->lang_key]=$r->lang_title;
		}
		return $return;
	}
	function numLanguage($status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(*) AS numrows FROM language ";
		if (!empty($status)) $cond = "active='$status'";		
		$result = $this->getDB()->query($sql);	
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
	
		return 0;
	}
	function getLanguageByKey($key)
	{
		$options = array();
		$sql = "SELECT * FROM language WHERE lang_key='$key'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	function AddLanguage($key,$title,$status='A'){		
		if(empty($key) || empty($title)){
			return  false;
		}
			
		$sql = "INSERT INTO language SET ".
				"lang_key='$key', ".
				"lang_title='$title', ".
				"status='$status'";
	
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Language', 'A', "key=".$key, "Title=$title");
			return true;
		}
		return false;
		
	}
	function updateStatusLanguage($key,$status)
	{
		$language = $this->getLanguageByKey($key);
		if (!empty($language)) {
			$sql = "UPDATE language SET status = '$status' WHERE lang_key='$key' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Language', 'U', "Key=$key","");
				return true;
			}
		}
		return false;
	}
	function deleteLanguage($key)
	{
		$language = $this->getLanguageByKey($key);
		if (!empty($language)) {
			$sql = "DELETE FROM language WHERE lang_key='$key' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Language', 'D', "Key=$key","");
				return true;
			}
		}
		return false;
	}
	function IsCanAddNewLanguage()
	{
		$currentoptions=GetLanguageList();
		$cond = '';
		$sql = "SELECT COUNT(*) AS numrows FROM language ";
		$where="";
		if(count($currentoptions)>0){
			foreach ($currentoptions as $key=>$ltitle){
				$where.=!empty($where)?',':"";
				$where.="'$key'";
			}
		}else{
			return false;
		}
		$sql.=" WHERE lang_key in ($where)";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		if ($this->getDB()->getNumRows() == 1) {
			return $result[0]->numrows<count($currentoptions);
		}	
		return false;
	}
	

	
}

?>