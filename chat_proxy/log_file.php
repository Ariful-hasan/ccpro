<?php
if (!function_exists('log_text')) {
	function log_text($log_data, $folder_path="chat_log/"){	
		$file_path = APPPATH . $folder_path;
		
		//deleting log files before 1 month
		$deleteCall = 5;
		//echo $file_path;
		if($deleteCall == rand(0,9)){
			$logFileList = scandir($file_path);
			$logFileList = array_diff($logFileList, array('.', '..'));
			$monthBeforeDate = date("Y_m_d", strtotime("-31 days"));
			foreach ($logFileList as $logFile) {
				if ($logFile < $monthBeforeDate) {					
					unlink($file_path . $logFile);
				}
			}
		}
		// deleting done

		array_unshift($log_data, date("Y-m-d H:i:s"));
		$text = implode("		", $log_data) . PHP_EOL;		
		$file_name = $file_path . date("Y_m_d") . ".txt";
		//var_dump($file_name);
		//die();
		$file_handler = fopen($file_name, "a");
		fwrite($file_handler, $text);
		fclose($file_handler);
	}
}