<?php
$type = (isset($_GET['type']) && !empty($_GET['type'])) ? $_GET['type'] : '';
$name = (isset($_GET['name']) && !empty($_GET['name'])) ? urldecode($_GET['name']) : '';
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

    $name = getImageName($main_path.$folder_path, $name);
	$img_file_path = $main_path.$folder_path.$name;
	//var_dump($img_file_path);
	//die();

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Length: ' . filesize($img_file_path));
	readfile($img_file_path);
	exit;
}


function getImageName($__dir, $img_name){
    $response = $img_name;
    if (!empty($img_name) && preg_match("/cid:/", $img_name)){
        $_img_str_arr = explode(":", $img_name);
        if (!empty($_img_str_arr[1])){
            $_img_str_arr = explode("@", $_img_str_arr[1]);
            if (!file_exists($__dir."/".$_img_str_arr[0])) {
                $response = findByImageName($__dir, $_img_str_arr[0]);
            }else {
                $response = $_img_str_arr[0];
            }
        }
    }
    return $response;
}

function findByImageName($__dir, $_file_name){
    $_FILES_ARR = scandir($__dir,  SCANDIR_SORT_ASCENDING);
    $ultimate_file_name = $_file_name;

    if (!empty($_FILES_ARR)){
        $_FILES_ARR = array_reverse($_FILES_ARR);
        $_name_arr = explode(".", $_file_name);

        array_pop($_name_arr);
        $_name = implode(".",$_name_arr);

        foreach ($_FILES_ARR as $key => $file){
            $_temp = explode(".", $file);
            $_extn = end($_temp);
            array_pop($_temp);
            $exists_file = implode(".", $_temp);

            if ($_name == $exists_file){
                $ultimate_file_name = $_name.".".$_extn;
                break;
            }
        }
    }
    return $ultimate_file_name;
}





