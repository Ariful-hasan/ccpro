<?php
error_reporting(7);
set_time_limit(0);
$debug = false;
$debug_new_line = "\n";
$attachment_directory_owner = 'nobody';
if ($debug) echo 'Start fetching ...'.$debug_new_line;
include_once('conf.email.php');
//$mail_server = new Mail('ccenter21@gmail.com', 'genusys21cc', 'pop.gmail.com', '995');
include_once($web_path . 'lib/DBManager.php');
include_once($web_path . 'conf.php');
$conn = new DBManager($db);
//var_dump($conn);

include_once ('lib/Mail.php');
//include($web_path . 'EWS_Mail.php');
include('config/constant.php');


$EMAIL_SIZE_LIMIT = 52428800; ////50MB////

////Fetching from database
//if ($debug) echo 'Fetching with method: '.$fetch_method . $debug_new_line;
$auto_reply_texts = array();
$auto_reply_skill_names = array();

$email_settings = getEmailSettings($conn);
$attachment_save_path = !empty($email_settings->attachment_save_path) ? base64_decode($email_settings->attachment_save_path) : 'content';
//$attachment_save_path = 'D:\Attch';

$method_list = getFetchDetails($conn);
if (!empty($method_list)) {
    foreach ($method_list as $key){
        $host = $key->host;
        $username = $key->username;
        $password = $key->password;
        $port = $key->port;
        $skill_id = $key->skill_id;
        $is_delete_email_after_fetch = $key->email_delete=='Y' ? true : false;
        $fetch_method = $key->fetch_method;
        if ($key->fetch_method == EMAIL_EWS) {
            EWS($host, $username, $password, $conn, $attachment_save_path, $attachment_directory_owner, $is_delete_email_after_fetch, $skill_id, $debug, $debug_new_line);
        }elseif ($key->fetch_method == EMAIL_POP3 || $key->fetch_method == EMAIL_IMAP){
            POP3($host, $username, $password, $port, $conn, $attachment_save_path, $attachment_directory_owner, $is_delete_email_after_fetch, $key->apply_domain_rule, $skill_id, $EMAIL_SIZE_LIMIT, $fetch_method, $key, $debug, $debug_new_line);
        }
    }
}


if ($debug) echo 'End fetching ....' . $debug_new_line;
add_mails_to_queue($conn);

function parse_email_address($email)
{
    $name = '';
    $user_name = '';
    $eadd = '';

    $lt_pos = strpos($email, '<');

    if ($lt_pos !== false) {
        $name = substr($email, 0, $lt_pos);
        $name = trim($name);
        $name = trim($name, '"');
        $eadd = substr($email, $lt_pos+1);
        $eadd = rtrim($eadd, '>');
    } else {
        $eadd = trim($email);
    }

    $user_name = $name;
    if (empty($name)) {
        $at_pos = strpos($eadd, '@');
        if ($at_pos !== false) {
            $user_name = substr($eadd, 0, $at_pos);
        } else {
            $user_name = $eadd;
        }
    }

    return array('name' => $name, 'user' => $user_name, 'email' => $eadd);
}

function get_ticket_id($conn)
{
    //have to check database
    $id = '';
    $max_try = 50;
    $i = 0;
    while ($i<=$max_try) {
        $id = random_digits(10);
        $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
        $result = $conn->query($sql);
        if (empty($result)) return $id;
        $i++;
    }
    return $id;
}

function get_customer_id($conn){
    $id = '';
    $max_try = 50;
    $i = 0;
    while ($i<=$max_try) {
        $id = random_digits(10);
        $sql = "SELECT customer_id FROM email_address_book WHERE customer_id='$id'";
        $result = $conn->query($sql);
        if (empty($result)) return $id;
        $i++;
    }
    return $id;
}

function is_customer_exists($conn, $email) {
    $sql = "SELECT customer_id FROM email_address_book WHERE email = '$email'";
    $result = $conn->query($sql);
    if (is_array($result)){
        return $result[0]->customer_id;
    }
    return '';
}

function random_digits($num_digits)
{
    if ($num_digits <= 0) {
        return '';
    }
    return mt_rand(1, 9) . random_digits($num_digits - 1);
}

function get_mail_message_by_original_id($oid='', $conn)
{
    if (empty($oid)) return null;
    $sql = "SELECT * FROM email_messages WHERE original_mail_id='$oid'";
    return $conn->query($sql);
}

function get_skill_from_to_address($to, $conn)
{
    $toes = explode(",", $to);
    if (is_array($toes)) {
        foreach ($toes as $_to) {
            if (!empty($_to)) {
                $to_address = parse_email_address($_to);
                if (!empty($to_address['email'])) {
                    $sql = "SELECT skill_id FROM e_mail2skill WHERE email='".$to_address['email']."'";
                    $result = $conn->query($sql);
                    if (is_array($result)) return array($result[0]->skill_id, $to_address['email']);
                    //if () return $to
                }
            }
        }
    }
    return '';
}

function add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn, $customer_id, $fetch_box_name, $fetch_object)
{
    $from_user = $from_address['user'];
    $from_email = getValidEmail($from_address['email']);
    //echo 'add_new_thread :</br>';var_dump($mail_to);echo '>>>>></br>';
    $subject = $conn->escapeString($subject);
    $check_chars = array("[","`","'","\"","~","!","#","$","^","&","%","*","(",")","{","}","<",">",",","?",";",":","|","+","=", 'column_name','--', ';','information_schema','\\');
    $subjet_for_search = str_replace($check_chars, ' ', trim($subject));
    $subject = base64_encode(trim($subject));

    $session_id = $thread_id.'001';
    //created_by='', created_for='', assigned_to='', status_updated_by='', product_id='', category_id='',
    echo $sql = "INSERT INTO e_ticket_info SET ticket_id='$thread_id', skill_id='$skill_id', subject='$subject', subject_db='$subjet_for_search', status='O', ".
        "mail_to='$mail_to', created_by='$from_email', created_for='$from_email', assigned_to='', status_updated_by='$from_email', ".
        "num_mails='0', last_update_time='$now', create_time='$now', customer_id='$customer_id', fetch_box_name='$fetch_box_name', fetch_box_email='$fetch_object->username', ".
        " session_id='$session_id'";
    echo $is_update = $conn->query($sql);
    save_email_address_book($conn, $customer_id, $from_address['name'], $from_email);
    if ($is_update) {
        generate_log_email_session($thread_id, $conn);
        if (!empty($skill_id)) {
            if (isset($GLOBALS['auto_reply_texts'][$skill_id])) {
                $text = $GLOBALS['auto_reply_texts'][$skill_id];
                $skill_name = $GLOBALS['auto_reply_skill_names'][$skill_id];
            } else {
                $GLOBALS['auto_reply_texts'][$skill_id] = '';
                $GLOBALS['auto_reply_skill_names'][$skill_id] = '';
                $sql = "SELECT * FROM email_auto_reply_templates WHERE skill_id='$skill_id' AND status='Y' LIMIT 1";
                $result = $conn->query($sql);
                if (is_array($result)) $GLOBALS['auto_reply_texts'][$skill_id] = $result[0]->mail_body;
                $text = $GLOBALS['auto_reply_texts'][$skill_id];

//                $sql = "SELECT skill_name FROM skill WHERE skill_id='$skill_id' LIMIT 1";
//                if (is_array($result)) $GLOBALS['auto_reply_skill_names'][$skill_id] = $result[0]->skill_name;
                /*$sql = "SELECT `name` FROM email_fetch_inboxes WHERE skill_id='$skill_id' AND `status`='A'";
                $result = $conn->query($sql);
                if (is_array($result)) $GLOBALS['auto_reply_skill_names'][$skill_id] = $result[0]->name;*/
                if (!empty($fetch_box_name)) $GLOBALS['auto_reply_skill_names'][$skill_id] = $fetch_box_name;
                $skill_name = $GLOBALS['auto_reply_skill_names'][$skill_id];
            }

            if (!empty($text)) {
                $msgBody = str_replace('TICKET_ID', $thread_id, $text);
                $sql = "INSERT INTO emails_out SET ".
                    "ticket_id='$thread_id', ".
                    "mail_sl='002', ".
                    "subject='$subject', ".
//			"from_name='$emailInfo->skill_name', ".
                    "from_name='$skill_name', ".
//                    "from_email='$mail_to', ".
                    "from_email='$fetch_object->username', ".
                    "to_email='$from_email', ".
//  			"cc='$email_cc', ".
                    // 			"bcc='$email_bcc', ".
                    "cc='', ".
                    "bcc='', ".
                    "body='$msgBody', ".
                    "has_attachment='N', ".
                    "is_forward='N', ".
                    "tstamp='".time()."'";
                $conn->query($sql);
            }
        }
    }
    return $is_update;
}

function get_ticket_details($thread_id, $conn)
{
    $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$thread_id'";
    return $conn->query($sql);
}

function get_ticket_id_from_subject($subject)
{
    $a = preg_match("/\[[0-9]{10}\]/", $subject, $matches);
    if ($a > 0) {
        $ticketid = trim($matches[0], "[]");
        return $ticketid;
    }
    return '';
}

function add_new_mail($mail_meta, $mail_content, $attachment_save_path, $conn, $attachment_directory_owner, $skill_id, $apply_domain_rule, $pop3_user, $fetch_object, $debug, $debug_new_line)
{
    if (!is_array($mail_meta) || !is_array($mail_content)) return false;

    $now = isset($mail_meta['udate']) ? $mail_meta['udate'] : strtotime($mail_meta['date']);
    if (empty($now)) $now = time();

//    $subject = $mail_meta['subject'];
    $subject = iconv_mime_decode($mail_meta['subject'], 0, 'UTF-8');
    $from = $mail_meta['from'];
    $cc = '';
    //$to = getValidEmail($mail_meta['to']);

    $to = $mail_content[0]['parsed']['To'];
    if (!empty($to)){
        $str = '';
        $to_array = explode(',',$to);
        if (!empty($to_array)){
            foreach ($to_array as $to_ar){
                $str .= getValidEmail($to_ar).',';
            }
            $to = rtrim($str,',');
        }
    }

    $skill_details = get_skill_from_to_address_new($to, $from, $subject, $apply_domain_rule, $conn);

    //$fetch_box_name = getFetchBoxNameBySkillid($skill_id, $conn);
    //$fetch_box_name = getFetchBoxNameByEmail($pop3_user, $conn);
    $fetch_box_name = $fetch_object->name;
    var_dump($fetch_box_name.'    success  -----');
    $skill_id = !empty($skill_details[0]) ? $skill_details[0] : $skill_id;
    $mail_to = $to;

//    if (empty($skill_id)) return false;      ///RND Forced Stop

    $from_address = parse_email_address($from);
    $from_name = $from_address['name'];
    $from_email = getValidEmail($from_address['email']);
    $from_user = $from_address['user'];
    $customer_id = "";

    //$mail_id = get_ticket_id();
    $mail_id = '';
    $original_mail_id = substr($mail_meta['message_id'], 1, -1);
    $orig_mail_exist = get_mail_message_by_original_id($original_mail_id, $conn);
    if (!empty($orig_mail_exist)) {
        return false;
    }
    $references = isset($mail_meta['references']) ? $mail_meta['references'] : '';
    $thread_id = '';
    $parent_mail_id = '';
    $ref = '';
    $reference_str = '';
    $mail_body = '';
    $sub_type = '';

    if (isset($mail_content[0]['parsed']['Cc']) || isset($mail_content[0]['parsed']['CC'])) {
        $cc = !empty($mail_content[0]['parsed']['Cc']) ? $mail_content[0]['parsed']['Cc'] : $mail_content[0]['parsed']['CC'];
    }

    if (!empty($cc)){
        $str = '';
        $cc_array = explode(',',$cc);
        if (!empty($cc_array)){
            foreach ($cc_array as $cc_ar){
                $str .= getValidEmail($cc_ar).',';
            }
            $cc = rtrim($str,',');
        }
    }

    //echo 'references<br/>';
    //var_dump($references);
    if (!empty($references)) {

        $reference_array = explode('> <', $references);
        if (is_array($reference_array)) {
            foreach ($reference_array as $ref) {
                $ref = trim($ref, "< >");
                if (!empty($ref)) {
                    if (empty($thread_id)) {

                        $thread_mail = get_mail_message_by_original_id($ref, $conn);
                        //echo 'thread_mail<br/>';
                        //var_dump($thread_mail);
                        if (!empty($thread_mail)) {
                            $thread_id = $thread_mail[0]->ticket_id;
                            //$mail_id = $thread_mail[0]->num_mails;
                        }
                        /*
                         else {
                            $thread_id = $this->getRandomMailId();
                            $this->addEmptyThread($thread_id, $now);
                        }
                        */
                    }

                    if (!empty($reference_str)) {
                        $reference_str .= '|';
                    }
                    $reference_str .= $ref;
                }
            }
        }
    }
    /*
     else {
        $thread_id = $mail_id;
        $this->addNewThread($mail_id, $from_address, $subject, $now);
    }
    */
    $is_thread_new = false;
    if (empty($thread_id)) {
        $threadid_from_subject = get_ticket_id_from_subject($subject);
        if (!empty($threadid_from_subject)) {
            $thread_id = $threadid_from_subject;
        }
    }

    $customer_id = is_customer_exists($conn, $from_email);
    if (empty($customer_id)){
        $customer_id = get_customer_id($conn);
    }

    if (empty($thread_id)) {
        $thread_id = get_ticket_id($conn);
        if (empty($thread_id)) return false;
        $subject = "[" . $thread_id . "] " . $subject;
        $is_thread_new = add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn, $customer_id, $fetch_box_name, $fetch_object);
    }
    $ticketinfo = get_ticket_details($thread_id, $conn);
    /*
        If ticket is closed then create new ticket
    */

    if (empty($ticketinfo)) {
        $thread_id = get_ticket_id($conn);
        if (empty($thread_id)) return false;
        $subject = "[" . $thread_id . "] " . $subject;
//        echo 'Thread Id 2: </br>';var_dump($thread_id);
        $is_thread_new = add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn, $customer_id, $fetch_box_name, $fetch_object);
        $ticketinfo = get_ticket_details($thread_id, $conn);
//        echo 'Thread Id 3: </br>';var_dump($thread_id);
    }


    if (empty($ticketinfo)) return false;
    //$mail_id = $ticketinfo[0]->num_mails;
    $mail_id = get_num_of_mails($thread_id, $conn);

    $mail_id++;

    $mail_id = sprintf("%03d", $mail_id);

    //$parent_mail_id = isset() ? ;
    //if (empty($thread_id)) {
    //$thread_id = $this->getRandomMailId();
    //$this->addEmptyThread($thread_id, $now);
    //}

    /*
    if (!empty($ref)) {
        if (!empty($thread_mail)) {
            if ($ref == $thread_mail[0]->original_mail_id) {
                $parent_mail_id = $thread_mail[0]->mail_sl;
            }
        }
        if (empty($parent_mail_id)) {
            $parent_mail = $this->getMailMessageByOriginalId($ref);
            if (!empty($parent_mail)) {
                $parent_mail_id = $parent_mail[0]->mail_id;
            }
        }
    }
    */

    $has_attachment = 'N';
    $attachment_i = 0;

    //print_r($mail_content);echo '<br/>';
    //$j = count($mail_content);

    if (is_array($mail_content)) {
        $i = 0;
        //var_dump($mail_content);
        foreach ($mail_content as $mail_content_i) {
            if ($i > 0) {

                if (isset($mail_content_i['type'])) {
                    //if ($mail_content_i['type'] == 0 && !$mail_content_i['is_attachment']) {
                    if ($mail_content_i['type'] == 0 && !isset($mail_content_i['is_attachment'])) {
//                        var_dump($mail_content_i['subtype']);
//                        var_dump($mail_content_i['data']);
//                        var_dump($mail_content_i['subtype'] == 'PLAIN' && !empty($mail_content_i['data']));
                        if ($mail_content_i['subtype'] == 'PLAIN' && !empty($mail_content_i['data'])) {
                            $mail_body = nl2br($mail_content_i['data']);
                        } else if (!empty($mail_content_i['data'])) {
                            $mail_body = $mail_content_i['data'];
                        }
                    }
                    if (isset($mail_content_i['is_attachment']) && $mail_content_i['is_attachment']) {
                        $attachment_i++;
                        write_to_file($thread_id, $mail_id, $attachment_i, $now, $mail_content_i, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line);
                    }
                }
            }
            $i++;
        }
        //echo 'body 2:  <br/>'.$mail_body;
    }
    //for ($i = 1; $i < $j; $i++) {
    /*
        if (isset($mail_content[$i]['type'])) {
            if ($mail_content[$i]['type'] == 0) {
                if ($mail_content[$i]['type'] == 'PLAIN') {
                    $mail_body = $mail_content[$i]['data'];
                } else if ($mail_content[$i]['type'] == 'HTML') {
                    $mail_body = $mail_content[$i]['data'];
                    //break;
                }
            }

            if (isset($mail_content[$i]['is_attachment']) && $mail_content[$i]['is_attachment']) {
                $attachment_i++;
                write_to_file($thread_id, $mail_id, $attachment_i, $now, $mail_content[$i], $conn);
            }
        }
        */
    //}


    $mailer_name = !empty($from_address['name']) ? explode(' ',$from_address['name']):'';
    if (is_array($mailer_name)) {
        $first_name = reset($mailer_name);
        $last_name = end($mailer_name);
    } else {
        $first_name = $from_address['name'];
        $last_name = '';
    }

    $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$thread_id'";
    $isPhoneNumberExists = $conn->query($sql);

    $phone_number = empty($references) ? get_phone_number_from_email($subject) : '';
    $phone_number = empty($phone_number) ? get_phone_number_from_email($mail_body) : $phone_number;
    $phone_number = !empty($phone_number) ? $phone_number[0] : '';

    if ($attachment_i > 0) {
        $has_attachment = 'Y';
    }

    //if (!empty($thread_id)) {
    //if ($thread_id != $mail_id) {
    $status_txt = '';
    $is_reopen = '';
    //$reopen_count = '';
    $last_reopen_time = '';
    if ($ticketinfo[0]->status == 'C') {	//pending-customer
        $status_txt = " status='P',";
    }elseif ($ticketinfo[0]->status == 'S' || $ticketinfo[0]->status == 'E'){
        $status_txt = " status='O',";
        $is_reopen = 'Y';
        //$reopen_count = 'reopen_count+1';
        $last_reopen_time = $now;
    }
    //echo 'ticketinfo : <br/>';
    //var_dump($ticketinfo);
    //echo 'is_reopen : <br/>';
    //var_dump($is_reopen);
    //$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1 WHERE ticket_id='$thread_id'";

    if (empty($isPhoneNumberExists)){
        //$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1, first_name='$first_name',last_name='$last_name', phone='$phone_number' WHERE ticket_id='$thread_id'";
        $sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails='$mail_id', first_name='$first_name',last_name='$last_name', phone='$phone_number', ";
    }else {
        //$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1, first_name='$first_name',last_name='$last_name' WHERE ticket_id='$thread_id'";
        $sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails='$mail_id', first_name='$first_name',last_name='$last_name', ";
    }
    if (!empty($is_reopen)){
        //$session_id = $thread_id.$mail_id;
        $session_id = generate_session_id($thread_id, $ticketinfo[0]->session_id);
        //generate_log_email_session($session_id, $thread_id, $skill_id, $from_email, $conn);
        $sql .= " is_reopen='$is_reopen', reopen_count=reopen_count+1, last_reopen_time='$last_reopen_time', session_id='$session_id' ";
    }
    $sql = rtrim(trim($sql),',');
    $sql .= " WHERE ticket_id='$thread_id'";
    $is_eticket_update = $conn->query($sql);
    if ($is_eticket_update && !empty($is_reopen)){
        generate_log_email_session($thread_id, $conn);
    }
    //}
    //}
    //var_dump($mail_body);
    $mail_body = changeImageURL($mail_body, $attachment_save_path, $now, $thread_id, $mail_id);
    //$mail_body = base64_encode($conn->escapeString($mail_body));

    $mail_body = base64_encode(trim($conn->escapeString($mail_body)));
    $subject = base64_encode(trim($conn->escapeString($subject)));

    $sql = "INSERT INTO email_messages SET ticket_id='$thread_id', mail_sl='$mail_id', original_mail_id='$original_mail_id', ".
        "subject='$subject', from_name='$from_name', from_email='$from_email', mail_from='$from', mail_to='$mail_to', mail_cc='$cc', ".
        "reference='$reference_str', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N', phone='$phone_number' ";
    $conn->query($sql);

    $sql = "INSERT INTO e_ticket_activity SET ticket_id='$thread_id', agent_id='$from_email', activity='M', ".
        "activity_details='$mail_id', activity_time='$now'";
    $conn->query($sql);

    /////////////System Distribution///////////
    $sql = "SELECT email_id FROM emails_in WHERE email_id = '$thread_id'";
    if (!$conn->query($sql)) {
        $sql = "INSERT INTO emails_in SET email_id= '$thread_id', tstamp = UNIX_TIMESTAMP('$now'), ";
        $sql .= " sender_name='$from_name', phone='$phone_number', email='$from_email', `subject`='$subject', skill_id='$skill_id', `language`='EN', status='N', agent_id='' ";
        $conn->query($sql);
    }
    /////////////System Distribution///////////
    file_put_contents('/usr/local/ccpro/email/log/' . $thread_id . '_' . $mail_id . '_meta.log', print_r($mail_meta, true));
    file_put_contents('/usr/local/ccpro/email/log/' . $thread_id . '_' . $mail_id . '_content.log', print_r($mail_content, true));
//var_dump($sql);
    /*
    $sql = "SELECT * FROM skill WHERE skill_id='$skill_id'";
    $result = $conn->query($sql);

    if (!is_array($result)) return false;

    $acd_mode = $result[0]->acd_mode;
    $delay_between_calls = $result[0]->delay_between_calls;
    $agent_ring_timeout = $result[0]->agent_ring_timeout;
    $find_last_agent = $result[0]->find_last_agent;

    $find_last_agent = $find_last_agent>0 ? 'Y' : 'N';

    $values = "qtype='E', tstamp='$now', callid='$thread_id', skill_id='$skill_id', status='N', acd_mode='$acd_mode', ".
        "delay_between_calls='$delay_between_calls', ring_timeout='$agent_ring_timeout', last_agent='$find_last_agent'";
    $sql = "UPDATE calls_in SET $values WHERE callid='' LIMIT 1";
    if (!$conn->query($sql)) {
        $sql = "INSERT INTO calls_in SET $values";
        $conn->query($sql);
    }
    */
    if (empty($is_reopen) && empty($is_thread_new)){
        check_empty_log_email_session($ticketinfo[0], $conn);
        update_log_email_session($conn, $thread_id);
    }
    return true;
}


function add_new_mail_ews($mail_meta, $mail_details, $attachment_save_path, $mail_server, $conn, $attachment_directory_owner, $skill_id, $debug, $debug_new_line)
{
    if (empty($mail_meta) || empty($mail_details)) return false;

    $now = isset($mail_meta->DateTimeSent) ? strtotime($mail_meta->DateTimeSent) : 0;

    if (empty($now)) $now = time();

    $subject = $mail_meta->Subject;
    $from = '"' . $mail_details->Sender->Mailbox->Name . '" <' . $mail_details->Sender->Mailbox->EmailAddress . '>';//$mail_meta['from'];
    if (is_array($mail_details->ToRecipients->Mailbox)) {
        $to = '';
        foreach ($mail_details->ToRecipients->Mailbox as $mbox) {
            if (!empty($to)) $to .= ', ';
            $to .= '"' . $mbox->Name . '" <' . $mbox->EmailAddress . '>';
        }
    } else {
        $to = '"' . $mail_details->ToRecipients->Mailbox->Name . '" <' . $mail_details->ToRecipients->Mailbox->EmailAddress . '>';//$mail_meta['from'];
    }

    $cc = '';
    if (isset($mail_details->CcRecipients)) {
        if (is_array($mail_details->CcRecipients->Mailbox)) {
            foreach ($mail_details->CcRecipients->Mailbox as $mbox) {
                if (!empty($cc)) $cc .= ', ';
                $cc .= '"' . $mbox->Name . '" <' . $mbox->EmailAddress . '>';
            }
        } else {
            $cc = '"' . $mail_details->CcRecipients->Mailbox->Name . '" <' . $mail_details->CcRecipients->Mailbox->EmailAddress . '>';
        }
    }
    //$to = '';

    //$skill_details = get_skill_from_to_address($to, $conn);

    /*if (!is_array($skill_details)) {
        return false;
    }*/

    $skill_id = $skill_id;
    $mail_to = $to;
    //echo $skill_id . 's';
    if (empty($skill_id)) return false;

    $from_address = parse_email_address($from);
    $from_name = $from_address['name'];
    $from_email = $from_address['email'];
    $from_user = $from_address['user'];

    //////RND/////
    $mailer_name = !empty($from_address['name']) ? explode(' ',$from_address['name']):'';
    $first_name = reset($mailer_name);
    $last_name = end($mailer_name);
    //////RND/////

    //$mail_id = get_ticket_id();
    $mail_id = '';
    $original_mail_id = empty($mail_details->InternetMessageId) ? substr($mail_details->ItemId->Id, -100) : substr($mail_details->InternetMessageId, 1, -1);
    $orig_mail_exist = get_mail_message_by_original_id($original_mail_id, $conn);
    if (!empty($orig_mail_exist)) {
        return false;
    }

    $references = empty($mail_details->References) ? '' : $mail_details->References;
    //$references = '';

    $thread_id = '';
    $parent_mail_id = '';
    $ref = '';
    $reference_str = '';
    $mail_body = '';

    /*
    if (isset($mail_content[0]['parsed']['Cc'])) {
        $cc = $mail_content[0]['parsed']['Cc'];
    }
    */

    if (!empty($references)) {

        $reference_array = explode('>,<', $references);
        if (is_array($reference_array)) {
            foreach ($reference_array as $ref) {
                $ref = trim($ref, "< >");
                if (!empty($ref)) {
                    if (empty($thread_id)) {

                        $thread_mail = get_mail_message_by_original_id($ref, $conn);
                        if (!empty($thread_mail)) {
                            $thread_id = $thread_mail[0]->ticket_id;
                            //$mail_id = $thread_mail[0]->num_mails;
                        }
                        /*
                         else {
                            $thread_id = $this->getRandomMailId();
                            $this->addEmptyThread($thread_id, $now);
                        }
                        */
                    }

                    if (!empty($reference_str)) {
                        $reference_str .= '|';
                    }
                    $reference_str .= $ref;
                }
            }
        }
    }
    /*
     else {
        $thread_id = $mail_id;
        $this->addNewThread($mail_id, $from_address, $subject, $now);
    }
    */
    if (empty($thread_id)) {
        $threadid_from_subject = get_ticket_id_from_subject($subject);
        if (!empty($threadid_from_subject)) {
            $thread_id = $threadid_from_subject;
        }
    }
    if (empty($thread_id)) {
        $thread_id = get_ticket_id($conn);
        if (empty($thread_id)) return false;
        $subject = "[" . $thread_id . "] " . $subject;
        add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn);
    }
    $ticketinfo = get_ticket_details($thread_id, $conn);
    if (empty($ticketinfo) || $ticketinfo[0]->status == 'E') {
        $thread_id = get_ticket_id($conn);
        if (empty($thread_id)) return false;
        $subject = "[" . $thread_id . "] " . $subject;
        add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn);
        $ticketinfo = get_ticket_details($thread_id, $conn);
    }

    if (empty($ticketinfo)) return false;

    $mail_id = $ticketinfo[0]->num_mails;

    $mail_id++;

    $mail_id = sprintf("%03d", $mail_id);


    $has_attachment = 'N';
    $attachment_i = 0;

    $mail_body_type = $mail_details->Body->BodyType;

    if ($mail_body_type == 'HTML') {

        $mail_body_tmp = $mail_details->Body->_;
        $pos = stripos($mail_body_tmp, '<body');
        if ($pos !== false) {
            $pos2 = strpos($mail_body_tmp, '>', $pos);
            if ($pos2 !== false) {
                $pos = $pos2;
            }
            $mail_body_tmp = substr($mail_body_tmp, $pos+1);
        }
        $pos = strripos($mail_body_tmp, '</body>');
        if ($pos !== false) {
            $mail_body_tmp = substr($mail_body_tmp, 0, $pos);
        }
        $mail_body = $mail_body_tmp;
        /*
        $d = new DOMDocument;
        $d->loadHTML($mail_body_tmp);
        $bodynode = $d->getElementsByTagName('body')->item(0);
        $mail_body = $bodynode->nodeValue;
        */
    } else {
        $mail_body = $mail_details->Body->_;
    }

    if (!empty($mail_details->HasAttachments)) {
        //echo 'att';
        $attachment_i = 0;

        if (isset($mail_details->Attachments->FileAttachment)) {
            $atts = $mail_details->Attachments->FileAttachment;
            if (is_array($atts)) {
                foreach ($atts as $att) {
                    $attid = $att->AttachmentId->Id;

                    $atttype = 7;
                    $attsubtype = '';
                    if (isset($att->ContentType) && !empty($att->ContentType)) {
                        list($atttype, $attsubtype) = get_attachment_types($att->ContentType);
                    }
                    $attdetails = $mail_server->get_attachment_details($attid);
                    if (!empty($attdetails)) {
                        $attachment_i++;
                        $mail_content_i['filename'] = $att->Name;
                        $mail_content_i['type'] = $atttype;
                        $mail_content_i['subtype'] = $attsubtype;
                        $mail_content_i['data'] = $attdetails->Content;
                        write_to_file($thread_id, $mail_id, $attachment_i, $now, $mail_content_i, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line);
                    }
                }
            } else {
                $attid = $atts->AttachmentId->Id;
                $attname = $atts->Name;
                $atttype = isset($atts->ContentType) ? $atts->ContentType : '';
            }
        }
    }

    //////RND/////
    $phone_number = empty($references) ? get_phone_number_from_email($subject) : '';
    $phone_number = empty($phone_number) ? get_phone_number_from_email($mail_body) : $phone_number;
    $phone_number = !empty($phone_number) ? $phone_number[0] : '';
    //////RND/////

    if ($attachment_i > 0) {
        $has_attachment = 'Y';
    }

    //if (!empty($thread_id)) {
    //if ($thread_id != $mail_id) {
    $status_txt = '';
    if ($ticketinfo[0]->status == 'C') {	//pending-customer
        $status_txt = " status='P',";
    }
    //$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1 WHERE ticket_id='$thread_id'";
    $sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1, first_name='$first_name',last_name='$last_name', phone='$phone_number' WHERE ticket_id='$thread_id'";
    $conn->query($sql);
    //}
    //}

    $mail_body = changeImageURL($mail_body, $attachment_save_path, $now, $thread_id, $mail_id);////RND////

    //$mail_body = $conn->escapeString($mail_body);
    $mail_body = base64_encode(trim($conn->escapeString($mail_body)));
    $subject = base64_encode(trim($conn->escapeString($subject)));

    $sql = "INSERT INTO email_messages SET ticket_id='$thread_id', mail_sl='$mail_id', original_mail_id='$original_mail_id', ".
        "subject='$subject', from_name='$from_name', from_email='$from_email', mail_from='$from', mail_to='$to', mail_cc='$cc', ".
        "reference='$reference_str', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N', phone='$phone_number'";
    $conn->query($sql);

    $sql = "INSERT INTO e_ticket_activity SET ticket_id='$thread_id', agent_id='$from_email', activity='M', ".
        "activity_details='$mail_id', activity_time='$now'";
    $conn->query($sql);

    return true;
}

function get_attachment_types($type_text)
{
    $type = 7;
    $subtype = '';
    $mail_types = array('TEXT'=>0, 'MULTIPART'=>1, 'MESSAGE'=>2, 'APPLICATION'=>3, 'AUDIO'=>4, 'IMAGE'=>5, 'VIDEO'=>6, 'OTHER'=>7);

    $pos = strpos($type_text, '/');

    if ($pos !== false) {
        $ttext = substr($type_text, 0, $pos);
        $subtype = substr($type_text, $pos+1);
    } else {
        $ttext = $type_text;
    }
    $ttext = strtoupper($ttext);
    if (array_key_exists($ttext, $mail_types)) {
        $type = $mail_types[$ttext];
    }

    return array($type, $subtype);
}

function add_mails_to_queue($conn)
{
    $skills = find_skills_with_agent($conn);
    $cur_mails_in_queue = get_cur_mails_count_in_queues($conn);
    foreach ($skills as $skillid=>$skill) {

        $in_queue = isset($cur_mails_in_queue[$skillid]) ? $cur_mails_in_queue[$skillid] : 0;
        $space_available = $skill->max_calls_in_queue - $in_queue;

        if ($space_available > 0) {

            $acd_mode = $skill->acd_mode;
            $delay_between_calls = $skill->delay_between_calls;
            $agent_ring_timeout = $skill->agent_ring_timeout;
            $find_last_agent = $skill->find_last_agent;
            $find_last_agent = $find_last_agent>0 ? 'Y' : 'N';

            $sql = "SELECT DISTINCT m.ticket_id, t.skill_id FROM email_messages AS m LEFT JOIN e_ticket_info AS t ON m.ticket_id=t.ticket_id ".
                "WHERE m.status='N' AND t.skill_id='$skillid' ORDER BY tstamp LIMIT 0, $space_available";
            $result = $conn->query($sql);
            if (is_array($result)) {
                foreach ($result as $row) {
                    //$skills[$row->skill_id] = $row;
                    $now = time();
                    $is_update = false;
                    $values = "qtype='E', tstamp='$now', callid='$row->ticket_id', skill_id='$row->skill_id', status='I', acd_mode='$acd_mode', ".
                        "delay_between_calls='$delay_between_calls', ring_timeout='$agent_ring_timeout', last_agent='$find_last_agent', altid=callid";
                    $sql = "UPDATE calls_in SET $values WHERE callid='' LIMIT 1";
                    if (!$conn->query($sql)) {
                        $sql = "INSERT INTO calls_in SET $values";
                        $is_update = $conn->query($sql);
                    } else {
                        $is_update = true;
                    }

                    if ($is_update) {
                        $sql = "UPDATE email_messages SET status='Q' WHERE ticket_id='$row->ticket_id'";
                        $conn->query($sql);
                    }
                }
            }
        }
    }
}

function get_cur_mails_count_in_queues($conn)
{
    $skills = array();
    $sql = "SELECT COUNT(callid) AS num_messages, skill_id FROM calls_in WHERE qtype='E' AND callid!='' GROUP BY skill_id;";
    $result = $conn->query($sql);

    if (is_array($result)) {
        foreach ($result as $row) {
            $skills[$row->skill_id] = $row->num_messages;
        }
    }

    return $skills;
}

function find_skills_with_agent($conn)
{
    $skills = array();
    $sql = "SELECT DISTINCT a.skill_id, acd_mode, delay_between_calls, agent_ring_timeout, ".
        "max_calls_in_queue, find_last_agent FROM agent_skill AS a LEFT JOIN skill AS s ON ".
        "s.skill_id=a.skill_id  WHERE tstamp!=0 AND s.qtype='E'";
    //$sql = "SELECT skill_id, acd_mode, delay_between_calls, agent_ring_timeout, max_calls_in_queue, find_last_agent FROM skill WHERE qtype='E'";
    $result = $conn->query($sql);

    if (is_array($result)) {
        foreach ($result as $row) {
            $skills[$row->skill_id] = $row;
        }
    }

    return $skills;
}

function write_to_file($thread_id, $mail_id, $attachment_i, $mtime, $mail_content, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line)
{
    /*
    array(6) {
        ["filename"]=>
        string(17) "tezt for mail.txt"
        ["is_attachment"]=>
        bool(true)
        ["name"]=>
        string(17) "tezt for mail.txt"
        ["type"]=>
        int(0)
        ["subtype"]=>
        string(5) "PLAIN"
        ["data"]=>
        string(13) "test mail ..."
      }
    */
    $MAX_FILE_SIZE = 10485760; /// 1*1024*1024*10 ////// 10MB
    $yy = date("y", $mtime);
    $mm = date("m", $mtime);
    $dd = date("d", $mtime);
    $fname = $mail_content['filename'];
    $ftype = $mail_content['type'];
    $fsubtype = $mail_content['subtype'];
//    echo ' File Size <br/>';var_dump(strlen($mail_content['data']));echo '<br/>';
    if (strlen($mail_content['data']) > $MAX_FILE_SIZE) return false;

    $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/';

    if (!is_dir($dir) && !empty($fname)) {
        //mkdir($dir, 0750, true);
        if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/')) {
            mkdir($attachment_save_path . '/' . $yy . $mm . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', 0750);
            if (!empty($attachment_directory_owner)) {
                chown($attachment_save_path . '/' . $yy . $mm . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', $attachment_directory_owner);
            }
        } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/')) {
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', 0750);
            if (!empty($attachment_directory_owner)) {
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', $attachment_directory_owner);
            }
        } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/')) {
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', 0750);
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', 0750);
            if (!empty($attachment_directory_owner)) {
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/', $attachment_directory_owner);
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', $attachment_directory_owner);
            }
        } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/')) {
            mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', 0750);
            if (!empty($attachment_directory_owner)) {
                chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/', $attachment_directory_owner);
            }
        }
    }

    if (is_writable($dir) && !empty($fname)) {
        $fh = fopen($dir . $fname, 'w');
        if ($debug && $fh == false) {
            echo "Failed to open file in write mode - file: " . $dir . $fname . $debug_new_line;
        }
        if ($fh) {
            if ($debug) echo "Opened file in write mode - file: " . $dir . $fname . $debug_new_line;
            $file_created = false;
            if (fwrite($fh, $mail_content['data']) === FALSE) {
                if ($debug) echo "Failed to write file: " . $dir . $fname . $debug_new_line;
            } else {
                $file_created = true;
                //remove_files_or_directory($dir,$fname);
            }
            fclose($fh);
            if (!empty($attachment_directory_owner) && $file_created) {
                chown($dir . $fname, $attachment_directory_owner);
            }
        }
    } else {
        echo "The directory is not writable - dir: " . $dir . ', file - ' . $fname . $debug_new_line;
    }

    if (!empty($fname)){
        $sql = "INSERT INTO email_attachments SET ticket_id='$thread_id', mail_sl='$mail_id', attach_type='$ftype', attach_subtype='$fsubtype', ".
            "part_position='$attachment_i', file_name='$fname'";
        return $conn->query($sql);
    }
}

function changeImageURL($mail_body, $attachment_save_path, $now, $thread_id, $mail_id){
    $yy = date("y", $now);
    $mm = date("m", $now);
    $dd = date("d", $now);
    $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/';

    preg_match_all('/src="cid:(.*)"/Uims', $mail_body, $matches);
    preg_match_all('/alt="(.*)"/Uims', $mail_body, $altrs);

    if(count($matches) > 0) {
        $search = array();
        $replace = array();
        foreach($matches[1] as $key => $match) {
            $uniqueFilename = $altrs[1][$key];
            //file_put_contents("/path/to/images/$uniqueFilename", $emailMessage->attachments[$match]['data']);
            $search[] = "src=\"cid:$match\"";
            $replace[] = "src=$dir$uniqueFilename";
        }
        $mail_body = str_replace($search, $replace, $mail_body);
    }
    return $mail_body;
}

function getFetchDetails($conn){
    $sql = "SELECT `skill_id`,`name`,`fetch_method`,`host`,`username`,`password`,`port`, `email_delete`, apply_domain_rule FROM email_fetch_inboxes WHERE `status` = 'A'";
    return $conn->query($sql);
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

function EWS($ews_host, $ews_user, $ews_password, $conn, $attachment_save_path, $attachment_directory_owner, $is_delete_email_after_fetch, $skill_id, $debug, $debug_new_line){
    $mail_server = new EWS_Mail($ews_host, $ews_user, $ews_password);
    $mails = $mail_server->mail_list();
    if (is_array($mails)) {
        $delete_items = array();
        foreach ($mails as $mail) {
            $mdetails = $mail_server->get_mail_details($mail->ItemId->Id);
            add_new_mail_ews($mail, $mdetails, $attachment_save_path, $mail_server, $conn, $attachment_directory_owner, $skill_id, $debug, $debug_new_line);
            $delete_items[$mail->ItemId->Id] = $mail->ItemId->ChangeKey;
        }

        if (count($delete_items) > 0) {
            $mail_server->delete_mails($delete_items, $is_delete_email_after_fetch);
        }
    }
}

function POP3($pop3_host, $pop3_user, $pop3_password, $pop3_port, $conn, $attachment_save_path, $attachment_directory_owner, $is_delete_email_after_fetch, $apply_domain_rule, $skill_id, $EMAIL_SIZE_LIMIT, $fetch_method, $fetch_object, $debug, $debug_new_line){
    $mail_server = new Mail($pop3_user, $pop3_password, $pop3_host, $pop3_port, $fetch_method);
    //$mail_model = new Model_Mail();
    $mail_server->pop3_login();
    if ($mail_server->pop3_is_connected()) {
        if ($debug) var_dump('POP3 --------- Connected');
        $mails = $mail_server->pop3_list('', $EMAIL_SIZE_LIMIT, $debug, $debug_new_line);
        //echo 'full mails<br/>';var_dump($mails);die;
        if (is_array($mails)) {
            if ($debug) echo 'Number of mails fetched: '.count($mails) . $debug_new_line;
            foreach ($mails as $mail) {
                $mdetails = $mail_server->mail_mime_to_array($mail['msgno'], true);
                if ($debug) {
                    echo 'Mail details: [' . $mail['msgno'] . '] ' . $debug_new_line;
                    var_dump($mdetails);
                    echo $debug_new_line;
                }
                add_new_mail($mail, $mdetails, $attachment_save_path, $conn, $attachment_directory_owner, $skill_id, $apply_domain_rule, $pop3_user, $fetch_object, $debug, $debug_new_line);
                if ($is_delete_email_after_fetch) $mail_server->pop3_delete($mail['msgno']);
            }
            if ($is_delete_email_after_fetch) $mail_server->pop3_expunge();
        } else {
            if ($debug) echo 'No mails found' . $debug_new_line;
        }
        $mail_server->pop3_close();
    } else {
        if ($debug) var_dump('not connected');
    }
}

function save_email_address_book($conn, $customer_id, $name, $email){
    $sql = "INSERT INTO email_address_book SET customer_id='$customer_id', name='$name', email='$email'";
    $conn->query($sql);
}


function get_phone_number_from_email($text){
    if (strlen($text) > 0){

        $text = strip_tags($text);
        $text = str_replace("\xc2\xa0",' ',$text);
        preg_match_all('/[0-9]{5}[\-][0-9]{6}|[\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6}|[0-9]{11}|[\+][0-9]{13}|[\+][0-9]{7}[\-][0-9]{6}|[\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
        //preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
        //echo 'number</br>';var_dump($matches);echo '</br>';
        $matches = $matches[0];
        return $matches;
    }
    return '';
}


function get_skill_from_to_address_new($to, $from, $subject, $apply_domain_rule, $conn){
    $toes = explode(",", $to);
    // GET SKILL FROM Email (From which email)
    if (!empty($from)){
        $from_address = parse_email_address($from);
        $to_email = get_to_email($toes);
        if ($apply_domain_rule == 'Y') {
            $result = get_domain_skill_from_from_address($from_address['email'], $conn);
            if (!empty($result) && is_array($result)) return array($result[0]->skill_id, $to_email);
        }
    }
    // GET SKILL FROM Email-Subject
    if (!empty($subject)){
        $to_email = get_to_email($toes);
        $skill_id = get_subject_skill_from_subject($subject, $conn);
        //var_dump('skill   >>>'.$skill_id);echo '</br>';
        //var_dump('mail   >>>>>'.$to_email);echo '</br>';
        if (!empty($skill_id)) return array($skill_id, $to_email);
    }
    // GET SKILL FROM Email-to
    if (is_array($toes)) {
        foreach ($toes as $_to) {
            if (!empty($_to)) {
                $to_address = parse_email_address($_to);
                if (!empty($to_address['email'])) {
                    $sql = "SELECT skill_id FROM e_mail2skill WHERE email='".$to_address['email']."'";
                    $result = $conn->query($sql);
                    if (is_array($result)) return array($result[0]->skill_id, $to_address['email']);
                    //if () return $to
                }
            }
        }
    }
    return '';
}

function get_to_email($emails){
    //var_dump($emails);
    if (is_array($emails)) {
        foreach ($emails as $email) {
            $to_address = parse_email_address($email);
            if (!empty($to_address['email'])){
                //echo ' get to email  >>> </br>';var_dump($to_address['email']);
                return $to_address['email'];
            }
        }
    }
    return '';
}
function get_domain_skill_from_from_address($from, $conn){
    $explode = explode("@",$from);
    $domain = array_pop($explode);
    if (!empty($domain)){
        $sql = "SELECT skill_id FROM email_domain2skill WHERE domain = '{$domain}'";
        return $conn->query($sql);
    }
    return '';
}

function get_subject_skill_from_subject($subject, $conn){
    $sql = "SELECT * FROM email_routing";
    $keywords = $conn->query($sql);
    //var_dump($keywords);
    if (!empty($keywords) && is_array($keywords)){
        foreach ($keywords as $keyword){
            if (preg_match("/$keyword->keyword/", $subject)) {
                return $keyword->skill_id;
            }
        }
    }
    return '';
}

function getToEmail($to){
    return preg_replace('/<(.*)>|"/','',$to);
}

function getValidEmail($string){
    //$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
    $pattern = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
    preg_match_all($pattern, $string, $matches);
    return $matches[0][0];
}

function getFetchBoxNameBySkillid($skill_id, $conn){
    $sql = "SELECT `name` FROM email_fetch_inboxes WHERE skill_id='$skill_id' AND `status`='A'";
    $result = $conn->query($sql);
    if (is_array($result))
        return $result[0]->name;
    return null;
}
function getFetchBoxNameByEmail($email, $conn){
    $sql = "SELECT `name` FROM email_fetch_inboxes WHERE username='$email' AND `status`='A'";
    $result = $conn->query($sql);
    if (is_array($result))
        return $result[0]->name;
    return null;
}

function update_log_email_session($conn, $ticket_id){
    $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$ticket_id'";
    $result = $conn->query($sql);
    if (!empty($result)){
        $sql = "SELECT * FROM log_email_session WHERE ticket_id='$ticket_id' AND session_id='".$result[0]->session_id."' ";
        $is_exists = $conn->query($sql);
        if (!empty($is_exists)){
            $sql = "UPDATE log_email_session SET `status`='".$result[0]->status."', last_update_time='".$result[0]->last_update_time."', status_updated_by='".$result[0]->status_updated_by."' ";
            $sql .= " WHERE ticket_id='".$result[0]->ticket_id."' AND session_id='".$result[0]->session_id."'";
            return $conn->query($sql);
        }
    }
    return null;
}

function get_num_of_mails($ticket_id, $conn){
    $sql = "SELECT COUNT(*) AS total from email_messages WHERE ticket_id='$ticket_id'";
    $result = $conn->query($sql);
    if (is_array($result)) return $result[0]->total;
    return 0;
}

/*function generate_log_email_session_old($session_id=null, $thread_id, $skill_id, $from_email, $conn){
    echo $sql = "INSERT INTO log_email_session SET ticket_id='".$thread_id."' session_id='$session_id', create_time='".time()."', skill_id='".$skill_id."', `status`='O', last_update_time='".time()."', status_updated_by='".$from_email."'";
    echo  $conn->query($sql);
}*/

function generate_log_email_session($thread_id, $conn){
    $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$thread_id'";
    $result = $conn->query($sql);
    if (is_array($result)){
        $sql = "INSERT INTO log_email_session SET  ticket_id='$thread_id', session_id='".$result[0]->session_id."', create_time='".$result[0]->last_update_time."', ";
        $sql .= " skill_id='".$result[0]->skill_id."', ";
        $sql .= "  `status`='".$result[0]->status."', reopen_num='".$result[0]->reopen_count."', last_update_time='".$result[0]->last_update_time."', ";
        $sql .= " status_updated_by='".$result[0]->status_updated_by."', disposition_id='".$result[0]->disposition_id."' ";
        $sql;
        return $conn->query($sql);
    }
    return null;
}

function generate_session_id($ticket_id, $old_session_id){
    $last_3_digit = substr($old_session_id, -3);
    $last_3_digit = (int)$last_3_digit;
    $last_3_digit = sprintf("%03d", $last_3_digit+1);
    return $ticket_id.$last_3_digit;
}

function check_empty_log_email_session($eTicket_info, $conn){
    if (!empty($eTicket_info) && empty($eTicket_info->session_id)) {
        $again_check_query = "SELECT session_id FROM log_email_session WHERE ticket_id='".$eTicket_info->ticket_id."' ";

        $session_id = $eTicket_info->ticket_id.'001';
        $first_open_time = 0;
        $create_time = 0;
        if ($eTicket_info->status!='S' && $eTicket_info->status!='E'){
            $create_time = $eTicket_info->create_time;
            $first_open_time = $eTicket_info->first_open_time;
        }
        $waiting_duration = $first_open_time - $create_time;

        $sql = "INSERT INTO log_email_session SET  ticket_id='".$eTicket_info->ticket_id."', session_id='$session_id', create_time='$create_time', first_open_time='$first_open_time', ";
        $sql .= "  waiting_duration='".$waiting_duration."', skill_id='".$eTicket_info->skill_id."', disposition_id='".$eTicket_info->disposition_id."',  agent_1='".$eTicket_info->status_updated_by."', first_seen_by='".$eTicket_info->status_updated_by."' ";
        $sql = trim($sql);

        $result = $conn->query($again_check_query);

        if (!is_array($result) && empty($result)){
            if ($conn->query($sql)){
                $sql = "UPDATE e_ticket_info SET session_id='$session_id' WHERE ticket_id='".$eTicket_info->ticket_id."' ";
                return $conn->query($sql);
            }
        }
    }
    return null;
}


/*function remove_files_or_directory($dir, $file_name){
    $MAX_LIMIT = 10420;
    $file_size = filesize($dir.$file_name);
    if ($file_size > $MAX_LIMIT) {
        recurDir($dir, $file_name);
    }
}

function recurDir($dir, $file=null){
    if ($file) unlink($dir . $file);
    $scanned_directory = array_diff(scandir($dir), array('..', '.'));
    $is_deleted = false;
    if (empty($scanned_directory)) {
        if (rmdir($dir)){
            $is_deleted = true;
        }
        $links = explode("/", $dir);
        if (!empty($links)) {
            foreach ($links as $link) {
                if ($link == '') {
                    array_pop($links);
                }
            }
        }
        if ($is_deleted) array_pop($links);
        $links = implode("/", $links);
        recurDir($links);
    }
}*/

