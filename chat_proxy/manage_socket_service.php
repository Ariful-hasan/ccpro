<?php
define("FILE_DIRECTORY", "socket_disconnection_log/");
define("LOG_FILE_NAME", "disconnection_log.txt");
define('BASE_PATH', '/usr/local/apache2/htdocs/robiwebchat/');
define("SOCKET_START_COMMAND","/etc/init.d/ccwspAB restart");

function getDisconnectLogCount()
{
    $fileHandler = fopen(BASE_PATH . FILE_DIRECTORY . LOG_FILE_NAME, "r");
    $logCount = 0;
    if ($fileHandler) {
        while (($line = fgets($fileHandler)) !== false) {
            $logCount++;
        }
    }
    fclose($fileHandler);
    return $logCount;
}

function logSocketCommand($log_data, $folder_path = FILE_DIRECTORY)
{
    $file_path = BASE_PATH . $folder_path;

    array_unshift($log_data, date("Y-m-d H:i:s"));
    $text = implode("		", $log_data) . PHP_EOL;
    $file_name = $file_path . date("Y_m_d") . ".txt";
    $file_handler = fopen($file_name, "a");
    fwrite($file_handler, $text);
    fclose($file_handler);
}

$logCount = getDisconnectLogCount();

if ($logCount >= 3) {
    $result = shell_exec(SOCKET_START_COMMAND);
    file_put_contents(BASE_PATH . FILE_DIRECTORY . LOG_FILE_NAME, ""); //clear the file
	logSocketCommand(array($result));
   //var_dump($result);
}else{
    echo "Error log was not found";
}
