<?php
log_w("\nReceived Request Initial");

$agent = trim($_SERVER['HTTP_USER_AGENT']);

if ($agent != 'gPlexCC-ScrLogger') exit;

log_w("\nReceived Request");

if(isset($_FILES["file"]["name"])) {

        $cnumber  = isset($_REQUEST["cnumber"]) ? trim($_REQUEST["cnumber"]) : 0;
        $num_chunks = isset($_REQUEST["total"]) ? trim($_REQUEST["total"]) : 1;
        
 
        $filename = $_FILES["file"]["name"];
        $filepath = $_FILES["file"]["tmp_name"];

        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        $callId = str_replace(".".$file_ext,"",$filename);

        list($realCallId, $dummy) = explode(".zip", $callId);

        log_w("CallID: $callId; Ext: $file_ext; Chunk: $cnumber; Total: $num_chunks;");
        
        $logger_path = 'temp/';
        $logger_dst_path = '/usr/local/ccpro/AA/screen_log/';
        
        //$folder = date("Y_m_d",substr($callId,0,10));
        $folder = date("Y_m_d", substr($callId, 0, 10));
        $dst_path = $logger_path . substr($folder,0,4) . '/' . $folder;
        if (!file_exists($dst_path)) {
          mkdir($dst_path, 0755, true);
          chown($dst_path, 'nobody');
          chgrp($dst_path, 'nobody');
        }
        
        $fl_dst_path = $logger_dst_path . substr($folder,0,4) . '/' . $folder;
        if (file_exists($fl_dst_path . '/' . $realCallId . '.zip')) {
                echo "200 OK FULL";
                exit;
        }

        if (move_uploaded_file($filepath, $dst_path . '/' . $callId . "." . $file_ext)) {
                $fl = rtrim($callId, "-".$cnumber);
                
                if ($num_chunks == ($cnumber+1)) {
                        $isFullFileUploaded = true;
                        if (file_exists($dst_path . '/' . $fl)) {
                                unlink($dst_path . '/' . $fl);
                        }
                        
                        for ($i=0; $i<$num_chunks; $i++) {
                                $src = $dst_path . '/' . $fl . "-" . $i . ".tmp";
                                
                                if (file_exists($src)) {
                                        $content = file_get_contents($src);
                                        file_put_contents($dst_path . '/' . $fl, $content, FILE_APPEND);
                                } else {
                                        $isFullFileUploaded = false;
                                }
                        }
                        
                        if ($isFullFileUploaded) {
                                //$fl_dst_path = $logger_dst_path . substr($folder,0,4) . '/' . $folder;
                                if (!file_exists($fl_dst_path)) mkdir($fl_dst_path, 0755, true);
                                
                                if (rename($dst_path . '/' . $fl, $fl_dst_path . '/' . $realCallId . '.zip')) {
                                
                                        echo "200 OK FULL";
                                        exit;
                                } else {
                                        echo "500 ERROR";
                                        exit;
                                }
                        }
                        
                }
                echo "200 OK";
                exit;
        }
}

log_w("END");

echo "400";
exit;

function log_w($content)
{
        $log_fl = 'temp/fl_ipload.txt';
        file_put_contents($log_fl, date("d H:i:s") . ": " . $content . "\n", FILE_APPEND);
}
