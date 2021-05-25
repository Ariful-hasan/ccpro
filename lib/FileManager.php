<?php

define ('FILE_NOT_UPLOADED', 0);
define ('FILE_EXT_INVALID', 2);
define ('FILE_UPLOADED', 1);

class FileManager
{

	static function check_file_for_upload($file_name, $extn)
	{
		$response = FILE_NOT_UPLOADED;
		if (!empty($_FILES[$file_name]) && $_FILES[$file_name]["error"] == 0) {
			$extention = FileManager::findexts(basename( $_FILES[$file_name]['name']));
			if ($extention != $extn) {
				//$errMsg = 'Please select a wav file';
				return FILE_EXT_INVALID;
			} else {
				//$is_welcome_en_file_uploaded = true;
				return FILE_UPLOADED;
			}
		}
		return $response;
	}

	function save_uploaded_file($file_name, $target_path)
	{
		$is_uploaded = move_uploaded_file($_FILES[$file_name]['tmp_name'], $target_path);
		
		if ($is_uploaded) {
			FileManager::notify_file_change();
		}
		
		return $is_uploaded;
	}
	
	function unlink_file($file_name)
	{
		$resp = unlink($file_name);
		if ($resp) {
			FileManager::notify_file_change();
		}
		return $resp;
	}
	
	function notify_file_change()
	{
		$fp = @fopen("./dashvar/ivr.moh", "w");
		if ($fp)  {
			fclose($fp);
		}
	}
	
	static function findexts ($filename)
	{
		$filename = strtolower($filename) ;
		$exts = explode(".", $filename) ;
		$n = count($exts)-1;
		$exts = $exts[$n];
		return $exts;
	}
	
}

?>