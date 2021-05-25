<?php
$type = (isset($_GET['type']) && !empty($_GET['type'])) ? $_GET['type'] : '';
$name = (isset($_GET['name']) && !empty($_GET['name'])) ? $_GET['name'] : '';
$ym = (isset($_GET['ym']) && !empty($_GET['ym'])) ? $_GET['ym'] : '';
$day = (isset($_GET['day']) && !empty($_GET['day'])) ? $_GET['day'] : '';
$tid = (isset($_GET['tid']) && !empty($_GET['tid'])) ? $_GET['tid'] : '';
$sl = (isset($_GET['sl']) && !empty($_GET['sl'])) ? $_GET['sl'] : '';
$main_path = '/usr/local/ccpro/AA/email_attachment/';

if(!empty($type) && !empty($name) && !empty($ym) && !empty($day)){
	$folder_path = '';
	if($type == 'new')
		$folder_path = 'create-email/'.$ym.'/'.$day.'/';
	elseif($type == 'attachment')
		$folder_path = $ym.'/'.$day.'/'.$tid.'/'.$sl.'/';
	elseif($type == 'signature')
		$folder_path = 'signature/'.$ym.'/'.$day.'/'.$tid.'/';
    elseif($type == 'autoreply')
        $folder_path = 'autoreply/'.$ym.'/'.$day.'/'.$tid.'/';

	$img_file_path = $main_path.$folder_path.$name;

	$response = '';
	if (unlink($img_file_path)){
        $response = true;
    }
    echo $response;
}





