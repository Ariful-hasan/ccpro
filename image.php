<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 10/8/2018
 * Time: 6:00 PM
 */


$path = !empty($_REQUEST['path']) ? $_REQUEST['path']  : '';
$_file_name = !empty($_REQUEST['fname']) ? $_REQUEST['fname'] : '';
function getImageLink($_dir, $_file_name){
    $GLOBALS['path'] = $_dir.$_file_name;
    $file_type = pathinfo($GLOBALS['path'], PATHINFO_EXTENSION);
    $GLOBALS['content_type'] = $file_type=="png" ? "image/png" : "image/jpeg";
    return null;
    //$data = file_get_contents($path);
    //return 'data:image/' . $file_type . ';base64,' . base64_encode($data);
}


$path = $path.$_file_name;
$file_type = pathinfo($path, PATHINFO_EXTENSION);
$content_type = $file_type=="png" ? "image/png" : "image/jpeg";
header("Content-type: $content_type");
readfile("$path");
//echo file_get_contents($path);

