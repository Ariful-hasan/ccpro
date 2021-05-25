<?php

/*
$c->create_file('tmp');
$c->write();
$c->close_file();
$c->download_file('newname');
*/

class DownloadHelper
{
	var $_local_file_gen_path = 'temp/';
	var $_local_file_name = '';
	var $_local_file_pointer = null;
	var $_template = null;
	var $_title = '';
	
	function __construct($title, $template)
	{
		$this->_title = $title;
		$this->_template = $template;
		$session_id = session_id();
		$dl = isset($_REQUEST['download']) ? $_REQUEST['download'] : '';
		if (md5($title.$session_id) != $dl) {
			$this->display_error_msg("Invalid page!!");
		}
	}
	
	function set_title($title)
	{
		$this->_title = $title;
	}
	
	function display_error_msg($msg)
	{
		unset($_REQUEST['download']);
		$this->_template->display('msg', array('pageTitle'=>$this->_title, 'isError'=>true, 'msg'=>$msg));
	}
	
	function create_file($file_name)
	{
		if (!empty($file_name) && empty($_local_file_pointer)) {
			
			$this->_local_file_pointer = fopen($this->_local_file_gen_path . $file_name, "w");
			if (!$this->_local_file_pointer) {
				if (!file_exists($this->_local_file_gen_path)) {
					mkdir($this->_local_file_gen_path, 0700);
				}
				$this->_local_file_pointer = fopen($this->_local_file_gen_path . $file_name, "w");
			}
			if ($this->_local_file_pointer) {
				$this->_local_file_name = $this->_local_file_gen_path . $file_name;
				return true;
			}
			
		}
		$this->display_error_msg("Could not create file!!");
		return false;
	}
	
	function write_in_file($data)
	{
		fwrite($this->_local_file_pointer, $data);
	}
	
	function close_file()
	{
		fclose($this->_local_file_pointer);
		$this->_local_file_pointer = null;
	}
	
	function set_local_file($local_file)
	{
		$this->_local_file_name = $local_file;
	}
	
	function download_file($new_file_name='', $extra_pre_text='')
	{
		if (empty($new_file_name)) $new_file_name = $this->_title;
		if (!empty($this->_local_file_pointer)) $this->close_file();
		$new_file_name = $new_file_name . ".csv";
		
		$new_file_name = str_replace(array(' ', ',', ':'), '_', $new_file_name);
		$new_file_name = preg_replace("/_+/", '_', $new_file_name);

		if (!file_exists($this->_local_file_name)) {
			$this->display_error_msg("File not found!!");
		}
		
		//$mtime = ($mtime == filemtime($this->_local_file_name)) ? $mtime : time();
		$mtime = filemtime($this->_local_file_name);
		$size = intval(sprintf("%u", filesize($this->_local_file_name)));
		
		if (!empty($extra_pre_text)) {
			$size += strlen($extra_pre_text);
		}
		// Maybe the problem is we are running into PHPs own memory limit, so:
		if (intval($size + 1) > $this->return_bytes(ini_get('memory_limit')) && intval($size * 1.5) <= 1073741824) { //Not higher than 1GB
			ini_set('memory_limit', intval($size * 1.5));
		}
		// Maybe the problem is Apache is trying to compress the output, so:
		@apache_setenv('no-gzip', 1);
		@ini_set('zlib.output_compression', 0);
		// Maybe the client doesn't know what to do with the output so send a bunch of these headers:
		header("Content-type: application/force-download");
		header('Content-Type: application/octet-stream');
		if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE") != false) {
			header("Content-Disposition: attachment; filename=" . urlencode($new_file_name) . '; modification-date="' . date('r', $mtime) . '";');
		} else {
			header("Content-Disposition: attachment; filename=\"" . $new_file_name . '"; modification-date="' . date('r', $mtime) . '";');
		}
		// Set the length so the browser can set the download timers
		header("Content-Length: " . $size);
		// If it's a large file we don't want the script to timeout, so:
		//set_time_limit(300);
		// If it's a large file, readfile might not be able to do it in one go, so:
		if (!empty($extra_pre_text)) echo $extra_pre_text;
		
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		if ($size > $chunksize) {
			$handle = fopen($this->_local_file_name, 'rb');
			$buffer = '';
			while (!feof($handle)) {
				$buffer = fread($handle, $chunksize);
				echo $buffer;
				ob_flush();
				flush();
			}
			fclose($handle);
		} else {
			readfile($this->_local_file_name);
		}
		exit;
	}

	function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
}

?>