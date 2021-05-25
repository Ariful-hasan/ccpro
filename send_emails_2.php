<?php
error_reporting(7);
set_time_limit(0);

$debug = true;
$debug_new_line = '<br />';
//$is_live = true;
//$script_dir = $is_live ? '' : '';
//$script_dir = '';
$script_dir = '';
include('config/constant.php');
include_once('conf.email.php');
include_once($script_dir . 'conf.php');
include_once($script_dir . 'lib/DBManager.php');
$conn = new DBManager($db);

$sql = "SELECT * FROM emails_out where to_email!='' AND status='' ORDER BY tstamp LIMIT 30";
$result = $conn->query($sql);
if (is_array($result)) {

    include_once( 'lib/phpmailer/phpmailer/phpmailer/src/Exception.php' );
    include_once( 'lib/phpmailer/phpmailer/phpmailer/src/PHPMailer.php' );
    include_once( 'lib/phpmailer/phpmailer/phpmailer/src/SMTP.php' );

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug = 2;                               // Enable verbose debug output
    $mail->isSMTP();                                    // Set mailer to use SMTP
    //$mail->Timeout = 30;
    $mail->getSMTPInstance()->Timelimit = 5;
    /////    SMTPAUTH = false for ROBI ////////
    $mail->SMTPAuth = true;   /////   For google its true.
    $mail->CharSet = 'UTF-8';
    // Enable SMTP authentication
//    $mail->Host = 'smtp.gmail.com';  				   // Specify main and backup SMTP servers
//    $mail->Username = 'demogsl007@gmail.com';           // SMTP username
//    $mail->Password = 'pajqnizmnyjqenid';               // SMTP password
//    $mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
//    $mail->Port = 587;
//    $mail->WordWrap   = 80;


    $smtp_settings = getEmailSettings($conn);
    $mail->Host = !empty($smtp_settings->smtp_host) ? $smtp_settings->smtp_host : "";
    $mail->Username = !empty($smtp_settings->smtp_username) ? $smtp_settings->smtp_username : "";
    $mail->Password = !empty($smtp_settings->smtp_password) ? $smtp_settings->smtp_password : "";

    /////   Turn Off by Masud Bhai  ////////
    //$mail->SMTPSecure = !empty($smtp_settings->smtp_secure_opton) ? $smtp_settings->smtp_secure_opton : 'tls';
    $mail->Port = !empty($smtp_settings->smtp_port) ? $smtp_settings->smtp_port : '587';

    foreach ($result as $mail_details) {
        $mail_details->to_email = trim($mail_details->to_email);

        if (!empty($mail_details->to_email)) {

            $from_email = !empty($mail_details->from_email) ? $mail_details->from_email : $smtp_settings->form_email;
            $from_name = !empty($mail_details->from_name) ? $mail_details->from_name : $smtp_settings->form_name;

            $mail->ClearAllRecipients();
            $mail->ClearReplyTos();
            $mail->ClearAttachments();

            $to_emails = $mail_details->to_email;
            $to_emails = getValidEmails($to_emails);
            if (empty($to_emails)) continue;
            if (!filter_var($from_email, FILTER_VALIDATE_EMAIL)) continue;  /////  20-09-18  //////

            $mail->AddReplyTo($from_email, $from_name);
            $mail_details->From       = $from_email;
            $mail_details->FromName   = $from_name;
            $mail->SetFrom($from_email, $from_name);
            $mail->Subject = ($mail_details->is_forward == 'Y') ? 'Fw: ' .base64_decode($mail_details->subject) :  base64_decode($mail_details->subject);
            $mail_details->body = base64_decode($mail_details->body);
            preg_match_all('/src="(.*)"/Uims',$mail_details->body, $matches);
            $main_path = '/usr/local/ccpro/AA/email_attachment/';
            $copy_file_path = [];

            if (!empty($matches[1])){
                $n=1;
                foreach ($matches[1] as $key){
                    $path = trim($key);
                    $parse_url = parse_url($path, PHP_URL_QUERY);

                    parse_str(html_entity_decode($parse_url), $parse_arr);
                    $type = (isset($parse_arr['type']) && !empty($parse_arr['type'])) ? $parse_arr['type'] : '';
                    $name = (isset($parse_arr['name']) && !empty($parse_arr['name'])) ? urldecode($parse_arr['name']) : '';
                    $ym = (isset($parse_arr['ym']) && !empty($parse_arr['ym'])) ? $parse_arr['ym'] : '';
                    $day = (isset($parse_arr['day']) && !empty($parse_arr['day'])) ? $parse_arr['day'] : '';
                    $tid = (isset($parse_arr['tid']) && !empty($parse_arr['tid'])) ? $parse_arr['tid'] : '';
                    $sl = (isset($parse_arr['sl']) && !empty($parse_arr['sl'])) ? $parse_arr['sl'] : '';                 

                    $folder_path = '';
                    if($type == 'new')
                        $folder_path = 'create-email/'.$ym.'/'.$day.'/';
                    elseif($type == 'attachment')
                        $folder_path = $ym.'/'.$day.'/'.$tid.'/'.$sl.'/';
                    elseif($type == 'signature')
                        $folder_path = 'signature/'.$ym.'/'.$day.'/'.$tid.'/'.$sl.'/';
                    elseif($type == 'autoreply')
                        $folder_path = 'autoreply/'.$ym.'/'.$day.'/'.$tid.'/';

                    if(!empty($folder_path)){
                        $img_file_path = $main_path.$folder_path.$name;
                        if(file_exists($img_file_path)){
                            echo 'IMAGE EXISTS: ';
                            $copy = copy($img_file_path, $email_body_image_copy_path."/".$name);
                            var_dump($copy);
                            if($copy){
                                $copy_file_path[]=$email_body_image_copy_path."/".$name;                            
                                $mail->AddEmbeddedImage($email_body_image_copy_path."/".$name, 'logo_'.$n, $name);
                                $search[] = "src=\"$path\"";
                                $replace[] = "src=\"cid:logo_".$n++."\"";
                            }
                        } else {
                            echo 'IMAGE NOT EXISTS: ';
                        }
                    }
                }
            }

            $mail_details->body = str_replace($search, $replace, $mail_details->body);
            $mail_details->body = "<html><head><meta charset=\"UTF-8\"></head><body>". $mail_details->body."</body></html>";
            $rnd = $mail->MsgHTML(str_replace('\r\n','',$mail_details->body));
            //$mail->ClearAddresses();
            foreach ($to_emails as $to_address){
                $mail->AddAddress($to_address);
            }

            if (!empty($mail_details->cc)) {
                $ccs = explode(',', $mail_details->cc);
                if (is_array($ccs)) {
                    foreach ($ccs as $_cc) {
                        $_cc = trim($_cc);
                        if (!empty($_cc) && filter_var($_cc, FILTER_VALIDATE_EMAIL)) $mail->AddCC($_cc);
                    }
                }
            }

            if (!empty($mail_details->bcc)) {
                $ccs = explode(',', $mail_details->bcc);
                if (is_array($ccs)) {
                    foreach ($ccs as $_cc) {
                        $_cc = trim($_cc);
                        if (!empty($_cc) && filter_var($_cc, FILTER_VALIDATE_EMAIL)) $mail->AddBCC($_cc);
                    }
                }
            }

            if ($mail_details->has_attachment) {
                $sql = "SELECT * FROM email_attachments WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl' ORDER BY part_position";
                $attachments = $conn->query($sql);

                if (is_array($attachments)) {
                    $yy = date("y", $mail_details->tstamp);
                    $mm = date("m", $mail_details->tstamp);
                    $dd = date("d", $mail_details->tstamp);
                    $email_settings = getEmailSettings($conn);

                    $attachment_save_path = !empty($email_settings->attachment_save_path) ? base64_decode($email_settings->attachment_save_path) : '';
                    $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $mail_details->ticket_id . '/' . $mail_details->mail_sl . '/';

                    foreach ($attachments as $att) {
                        if (!empty($att->file_name) && file_exists($dir . $att->file_name)) {
                            $mail->AddAttachment($dir . $att->file_name);
                            if ($debug) echo 'Attaching file - ' . $dir . $att->file_name . $debug_new_line;
                        } else {
                            if ($debug) echo 'Error [Not found]: Attaching file - ' . $dir . $att->file_name . $debug_new_line;
                        }
                    }
                }
            }
            $is_email_found = true;

            if ($is_email_found) {
                try {
                    if ( !$mail->Send($conn, $mail_details) ) {
                        $error = "Unable to send to: " . $mail_details->to_email . "<br />";
                        //throw new phpmailerAppException($error);
                        updateStatus($conn, $mail_details, $mail->ErrorInfo);
                    } else {
                        $sql_update = "UPDATE email_messages SET send_time=".time()." WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl'";
                        $result = $conn->query($sql_update);

                        $sql_update = "DELETE FROM emails_out WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl'";
                        $result = $conn->query($sql_update);

                        foreach ($copy_file_path as $key => $value) {
                            unlink($value);
                        }
                    }
                } catch (phpmailerAppException $e) {
                    //$errorMsg[] = $e->errorMessage();
                    updateStatus($conn, $mail_details, $mail->ErrorInfo);
                }

            }
        }
    }
    $mail->SmtpClose();
} else {
    if ($debug) echo '0 Email';
}
exit;

function getImagesIfExists($email_body,$mail){
    preg_match_all('/src="(.*)"/Uims',$email_body, $matches);
    $images = '';
    $image_str = '';
    if (!empty($matches[1])){
        foreach ($matches[1] as $key){
            $path = $key;
            $links = explode('/', $key);
            $images = end($links);
            $mail->AddEmbeddedImage($path, $images, $images);
        }
    }
}

function getEmailSettings($conn){
    $sql = "SELECT * FROM cc_settings WHERE module_type='".MOD_EMAIL."' ";
    $result = $conn->query($sql);
    $obj = new stdClass();
    if (!empty($result)){
        foreach ($result as $key){
            $itme = $key->item;
            $obj->$itme = $key->value;
        }
    }
    return $obj;
}

function getValidEmails($to_emails){
    $valid_to_emails = [];
    if (!empty($to_emails)){
        $to_emails = explode(",", $to_emails);
        foreach ($to_emails as $email){
            if (filter_var($email, FILTER_VALIDATE_EMAIL)){
                $valid_to_emails []= $email;
            }
        }
    }
    return $valid_to_emails;
}

class phpmailerAppException extends Exception {
    public function errorMessage() {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
        return $errorMsg;
    }
}

/*
 * Update status for send failed Email
 */
function updateStatus($conn, $mailboj, $error_info){
    if (!empty($conn) && !empty($mailboj)){
        $sql = "UPDATE emails_out SET `status`='E' WHERE ticket_id='$mailboj->ticket_id' AND mail_sl='$mailboj->mail_sl' LIMIT 1;";
        $conn->query($sql);
        file_put_contents('/usr/local/ccpro/email/log/temp/smtp/'. $mailboj->ticket_id . '_' . $mailboj->mail_sl .'_smtp.log', print_r($error_info, true));
    }
}
