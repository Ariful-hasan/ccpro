<?php
define("FILE_DIRECTORY", "socket_disconnection_log/");
define("LOG_FILE_NAME", "disconnection_log.txt");
define("LENGTH_OF_CALL_ID", 20);
define('BASE_PATH', '/usr/local/apache2/htdocs/robiwebchat/');

function validateCallId($callId)
{
    if (!ctype_digit($callId)) {
        return false;
    }
    return true;
}

function checkIsDuplicate($callId)
{
    $fileHandler = fopen(BASE_PATH . FILE_DIRECTORY . LOG_FILE_NAME, "r");
    $hasDuplicate = false;
    if ($fileHandler) {
        while (($line = fgets($fileHandler)) !== false) {
            $line = explode(",", $line);
            $line = array_pop($line);
            $line = trim($line);
            if ($line == $callId) {
                fclose($fileHandler);
                return true;
            }
        }
    }
    fclose($fileHandler);
    return $hasDuplicate;
}

function logData($callId)
{
    $fileHandler = fopen(BASE_PATH . FILE_DIRECTORY . LOG_FILE_NAME, "a");
    $logData = date("Y-m-d H:i:s") . "," . $callId . PHP_EOL;
    if (fwrite($fileHandler, $logData)) {
        fclose($fileHandler);
        return true;
    }
}

$callId = $_POST['callid'];
$callId = explode("-", $callId);
$callId = reset($callId);
$status = 'failed';
$isValid = validateCallId($callId);
if($isValid) {
    $hasDuplicate = checkIsDuplicate($callId);
    if ($hasDuplicate == false) {
        if (logData($callId)) {
            $status = 'success';
        }
    }
}
echo json_encode(array(
    'status' => $status
));
die;



