<?php
define('APPPATH', dirname(__FILE__)."/");
include_once(APPPATH . 'log_file.php');

if (!empty($_REQUEST['args'])) {
    $log_data = reset(json_decode($_REQUEST['args'], true));
    $log_data = json_encode($log_data);

    log_text([$log_data],  'accept_join_log/');
}

echo "success";
exit();