<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$fetch = new FetchEmail();
$fetch->_debug = true;
$fetch->startFetching();


class FetchEmail {
    public $_debug=false;
    public $_db_con;
    public $_max_size = 52428800; //50MB.
    public $_read_email_body_image_url = "http://192.168.10.64/ccprodev/read_email_body_image.php";
    public $_directory_owner = "nobody";
    public $_subject_keyword = null;
    public $_email_2_skill = null;
    public $_max_attachment_size = 10485760; //10MB.

    private $_global_settings;
    private $_fetch_settings;
    private $_log_file_path = "/usr/local/ccpro/email/log";
    private $_log_delete_day = 31*24*60*60; // day's ago log file delete.
    private $_log_delete_count = 50; // file delete per cron-job.
    private $_log_delete_start_time = "01:30"; // "01:30"
    private $_log_delete_end_time = "02:00"; // "02:00"

    public function __construct()
    {
        include('config/constant.php');
        include_once('lib/DBManager.php');
        include_once ('lib/Mail.php');
        include_once('conf.php');
        include_once('conf.email.php');

        $this->_db_con = new DBManager($db);
        $this->deleteEmailLog();
        $this->_global_settings = $this->getEmailGlobalSettings();
        //$this->setEmailMessages();
        $this->dd($this->_global_settings);
    }

    /*
     * Set Email Messages
     */
    private function setEmailMessages ($mail_meta, $mail_content, $fetchObj) {
        $__to = $mail_content[0]['parsed']['To'];
        if (!empty($__to)){
            $__to = explode(',',$__to);
            $__to = array_map([$this, 'getValidEmailAddress'], $__to);
            $__to = !empty($__to) ? implode(",", $__to) : "";
        }

        $__cc = !empty($mail_content[0]['parsed']['Cc']) ? $mail_content[0]['parsed']['Cc'] : !empty($mail_content[0]['parsed']['CC']) ? $mail_content[0]['parsed']['CC'] : "";
        if (!empty($__cc)) {
            $__cc = explode(",", $__cc);
            $__cc = array_map(array($this, 'getValidEmailAddress'), $__cc);
            $__cc = implode(",", $__cc);
        }
        $__parsed_address = $this->parseEmailAddress($mail_meta['from']);

        $__obj_by_ref = $this->getEmailIdByReference($mail_meta['references']);
        $__obj_by_ref->email_id = !empty($__obj_by_ref->email_id) ? $__obj_by_ref->email_id : $this->getEmailIdBySubject(iconv_mime_decode($mail_meta['subject'], 0, 'UTF-8'));
        $__obj_by_ref->email_id = !empty($__obj_by_ref->email_id) ? $__obj_by_ref->email_id : $this->generateEmailId();

        $emailObj = new stdClass();
        $emailObj->ticket_id = $__obj_by_ref->email_id;
        $emailObj->original_mail_id = substr($mail_meta['message_id'], 1, -1);
        $emailObj->mail_sl = sprintf("%03d", $this->getMaxEmailSerailNo($__obj_by_ref->email_id));

        if (!$this->saveEmailMessage($emailObj)) {
            for (;;){
                $emailObj->mail_sl = sprintf("%03d", $this->getMaxEmailSerailNo($__obj_by_ref->email_id));
                if ($this->saveEmailMessage($emailObj))
                    break;
            }
        }

        $emailObj->subject = base64_encode($this->_db_con->escapeString(iconv_mime_decode($mail_meta['subject'], 0, 'UTF-8')));
        $emailObj->from_name = $this->_db_con->escapeString(iconv_mime_decode($__parsed_address['name'], 0, 'UTF-8'));
        $emailObj->from_email = $this->getValidEmailAddress($__parsed_address['email']);
        $emailObj->mail_from = $this->_db_con->escapeString(iconv_mime_decode($mail_meta['from'], 0, 'UTF-8'));
        $emailObj->mail_to = $__to;
        $emailObj->mail_cc = $__cc;
        $emailObj->reference = !empty($__obj_by_ref->reference) ? $__obj_by_ref->reference : "";
        $emailObj->tstamp = isset($mail_meta['udate']) ? $mail_meta['udate'] : !empty($mail_meta['date']) ? strtotime($mail_meta['date']) : time();

        $rawBody = $this->getEmailRawBody($mail_content);
        $__phone_number = $this->getPhoneNumberFromString($emailObj->reference, iconv_mime_decode($mail_meta['subject'], 0, 'UTF-8'), $rawBody);
        $attachmetnObj = $this->saveEmailAttachments($emailObj, $mail_content);
        $__emailBody = $this->setBodyImage($emailObj, $rawBody, $attachmetnObj);

        $emailObj->mail_body = base64_encode(trim($this->_db_con->escapeString($__emailBody)));
        $emailObj->has_attachment = $attachmetnObj->hasAttachment;
        $emailObj->status = 'N';
        $emailObj->phone = $__phone_number;
        $emailObj->session_id = '';
        return $emailObj;
    }

    /*
     * Set E-Ticket-Info
     */
    private function setETicketInfo ($emailObj, $mail_meta, $fetchObj) {
        $__skill_id = $this->getSkillIdByFilter($emailObj->mail_to, $mail_meta['from'], iconv_mime_decode($mail_meta['subject'], 0, 'UTF-8'), $fetchObj);

        $__parsed_address = $this->parseEmailAddress($mail_meta['from']);
        $mailer_name = !empty($__parsed_address['name']) ? explode(' ',$__parsed_address['name']):"";
        $__f_name = is_array($mailer_name) ? $this->_db_con->escapeString(current($mailer_name)) : "";
        $__l_name = is_array($mailer_name) ? $this->_db_con->escapeString(trim(str_replace($__f_name, '', $__parsed_address['name']))) : "";

        $check_chars = array("[","`","'","\"","~","!","#","$","^","&","%","*","(",")","{","}","<",">",",","?",";",":","|","+","=", 'column_name','--', ';','information_schema','\\');
        $__subject_db = str_replace($check_chars, ' ', trim(base64_decode($emailObj->subject)));
        $threadInfo = $this->getThreadInfoById($emailObj->ticket_id);
        $__session_id = $this->generateSessionId($threadInfo, $emailObj->ticket_id);
        $priorityEmails = !empty($this->_global_settings->priority_email) ? explode(",", $this->_global_settings->priority_email) : [];

        $emailThread = new stdClass();
        $emailThread->ticket_id = $emailObj->ticket_id;
        $emailThread->created_by = $emailObj->from_email;
        $emailThread->created_for = $emailObj->from_email;
        $emailThread->status_updated_by = $emailObj->from_email;
        $emailThread->mail_to = $emailObj->mail_to;
        $emailThread->skill_id = $__skill_id;
        $emailThread->subject = $emailObj->subject;
        $emailThread->subject_db = $__subject_db;
        $emailThread->status = !empty($threadInfo) && $threadInfo->status == "C" ? "P" : "O";
        $emailThread->num_mails = $emailObj->mail_sl;
        $emailThread->last_update_time = $emailObj->tstamp;
        $emailThread->create_time = $emailObj->tstamp;
        $emailThread->first_name = $__f_name;
        $emailThread->last_name = $__l_name;
        $emailThread->phone = $emailObj->phone;
        $emailThread->customer_id = $this->getCustomerIdByEmail($__parsed_address['name'], $emailObj->from_email);
        $emailThread->fetch_box_email = $fetchObj->username;
        $emailThread->fetch_box_name = $fetchObj->name;
        $emailThread->is_reopen = !empty($threadInfo) && ($threadInfo->status == 'S' || $threadInfo->status == 'E') ? "Y" : "";
        $emailThread->reopen_count = !empty($emailThread->is_reopen) ? "reopen_count+1" : "";
        $emailThread->last_reopen_time = !empty($emailThread->is_reopen) ? $emailObj->tstamp : 0;
        $emailThread->session_id = $__session_id;
        $emailThread->is_priority = !empty($priorityEmails) && in_array($emailObj->from_email, $priorityEmails) ? "Y" : "";
        return $emailThread;
    }

    /*
     * Save Email Message
     */
    private function saveEmailMessage($data, $isUpdate=false) {
        if (!empty($data)){
            $sql = $isUpdate ? "UPDATE email_messages SET " : "INSERT INTO email_messages SET ";
            foreach ($data as $key => $value){
                $sql .= $key."='$value', ";
            }
            $sql = rtrim($sql, ',');
            $sql .= $isUpdate ? " WHERE ticket_id='$data->ticket_id' AND mail_sl='$data->mail_sl' LIMIT 1 " : "";
            return $this->_db_con->query($sql);
        }
        return false;
    }

    /*
     * Get E-Ticket Info
     * details by email id
     */
    private function getThreadInfoById($thread_id) {
        $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$thread_id'";
        $result = $this->_db_con->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    /*
     * Generate Session Id
     */
    private function generateSessionId ($theadObj, $emailId) {
        if (!empty($theadObj)){
            $sess = substr($theadObj->session_id, -3);
            return sprintf("%03d", (int)$sess+1);
        } else {
            return $emailId."001";
        }
    }

    /*
     * Get Email Body
     */
    public function getEmailRawBody($mail_content) {
        $body = "";
        if (is_array($mail_content)) {
            $i = 0;
            foreach ($mail_content as $mail_content_i) {
                if ($i > 0) {
                    if (isset($mail_content_i['type'])) {
                        if ($mail_content_i['type'] == 0 && !isset($mail_content_i['is_attachment'])) {
                            if ($mail_content_i['subtype'] == 'PLAIN' && !empty($mail_content_i['data'])) {
                                $body = nl2br($mail_content_i['data']);
                            } else if (!empty($mail_content_i['data'])) {
                                $body = $mail_content_i['data'];
                            }
                        }
                    }
                }
                $i++;
            }
        }
        return $body;
    }

    public function setBodyImage($emailMessageObj, $mail_body, $attachmentObj){
        $yy = date("y", $emailMessageObj->tstamp);
        $mm = date("m", $emailMessageObj->tstamp);
        $dd = date("d", $emailMessageObj->tstamp);

        preg_match_all('/src="cid:(.*)"/Uims', $mail_body, $matches);
        if(count($matches) > 0) {
            $search = array();
            $replace = array();
            foreach($matches[1] as $key => $match) {
                $uniqueFilename = !empty($attachmentObj->attachmentNameWithId[$match]) ? $attachmentObj->attachmentNameWithId[$match] : $attachmentObj->attachmentFiles[$key]['filename'];
                $search[] = "src=\"cid:$match\"";
                $replace[] = "src=".$this->_read_email_body_image_url."?type=attachment&name=".urlencode($uniqueFilename)."&ym=".$yy.$mm."&day=".$dd."&tid=".$emailMessageObj->ticket_id."&sl=".$emailMessageObj->mail_sl;
            }
            $mail_body = str_replace($search, $replace, $mail_body);
        }
        return $mail_body;
    }

    /*
     * Save Email Attachments
     */
    public function saveEmailAttachments ($emailMessageObj, $mail_content) {
        $attachmentObj = new stdClass();
        $attachmentObj->attachmentFiles = [];
        $attachmentObj->attachmentNameWithId = [];
        $attachmentObj->hasAttachment = '';

        if (is_array($mail_content)) {
            $i = 0;
            $attachment_i = 0;
            $is_same_attachment_files_exist = [];

            foreach ($mail_content as $mail_content_i) {
                if ($i > 0) {
                    if (isset($mail_content_i['type'])) {
                        if (isset($mail_content_i['is_attachment']) && $mail_content_i['is_attachment']) {
                            $file_name = $mail_content_i['filename'];
                            $file_name = in_array($file_name, $is_same_attachment_files_exist) ? $attachment_i.$file_name : $file_name;
                            $is_same_attachment_files_exist[] = $file_name;
                            $mail_content_i['filename'] = $file_name;

                            $attachmentObj->attachmentNameWithId[$mail_content_i['content_id']] = $file_name;
                            $attachmentObj->attachmentFiles[$attachment_i] = $mail_content_i;
                            $attachment_i++;
                            $this->writeFiles ($emailMessageObj, $attachment_i, $mail_content_i);
                        }
                    }
                }
                $i++;
            }
            $attachmentObj->hasAttachment = $attachment_i > 0 ? "Y" : "";
        }
        return $attachmentObj;
    }

    public function writeFiles ($emailMessageObj, $attachment_i, $mail_content){
        $yy = date("y", $emailMessageObj->tstamp);
        $mm = date("m", $emailMessageObj->tstamp);
        $dd = date("d", $emailMessageObj->tstamp);
        $fname = $mail_content['filename'];
        $ftype = $mail_content['type'];
        $fsubtype = $mail_content['subtype'];

        if (strlen($mail_content['data']) > $this->_max_attachment_size)
            return false;

        $attachment_save_path = !empty($this->_global_settings->attachment_save_path) ? base64_decode($this->_global_settings->attachment_save_path) : "content";
        $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/';

        if (!is_dir($dir) && !empty($fname)) {
            if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/')) {
                mkdir($attachment_save_path . '/' . $yy . $mm . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', 0750);
                if (!empty($this->_directory_owner)) {
                    chown($attachment_save_path . '/' . $yy . $mm . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', $this->_directory_owner);
                }
            } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/')) {
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', 0750);
                if (!empty($this->_directory_owner)) {
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', $this->_directory_owner);
                }
            } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/')) {
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', 0750);
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', 0750);
                if (!empty($this->_directory_owner)) {
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/', $this->_directory_owner);
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', $this->_directory_owner);
                }
            } else if (!is_dir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/')) {
                mkdir($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', 0750);
                if (!empty($this->_directory_owner)) {
                    chown($attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $emailMessageObj->ticket_id . '/' . $emailMessageObj->mail_sl . '/', $this->_directory_owner);
                }
            }
        }

        if (is_writable($dir) && !empty($fname)) {
            $fh = fopen($dir . $fname, 'w');
            if ($this->_debug && $fh == false) {
                $this->GPrint("Failed to open file in write mode - file: " . $dir . $fname);
            }
            if ($fh) {
                if ($this->_debug){
                    $this->GPrint("Opened file in write mode - file: " . $dir . $fname);
                }
                $file_created = false;
                if (fwrite($fh, $mail_content['data']) === FALSE) {
                    if ($this->_debug) {
                        $this->GPrint("Failed to write file: " . $dir . $fname);
                    }
                } else {
                    $file_created = true;
                    //remove_files_or_directory($dir,$fname);
                }
                fclose($fh);
                if (!empty($this->_directory_owner) && $file_created) {
                    chown($dir . $fname, $this->_directory_owner);
                }
            }
        } else {
            if ($this->_debug) {
                $this->GPrint("The directory is not writable - dir: " . $dir . ', file - ' . $fname);
            }
        }

        if (!empty($fname)){
            $fname = $this->_db_con->escapeString($fname);
            $sql = "INSERT INTO email_attachments SET ticket_id='$emailMessageObj->ticket_id', mail_sl='$emailMessageObj->mail_sl', ";
            $sql .= "attach_type='$ftype', attach_subtype='$fsubtype', part_position='$attachment_i', file_name='$fname'";
            return $this->_db_con->query($sql);
        }
        return false;
    }

    /*
     * Get Mobile Number
     * from subject or boby
     */
    public function getPhoneNumberFromString($reference, $subject, $body){
        $str = "";
        $number = null;
        if (empty($reference) && strlen($subject) > 0){
            $str = $subject;
            goto findNumber;
        } else {
            $str = $body;
            goto findNumber;
        }

        findNumber:
            $str = strip_tags($str);
            $str = str_replace("\xc2\xa0",' ',$str);
            preg_match_all('/[0-9]{5}[\-][0-9]{6}|[\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6}|[0-9]{11}|[\+][0-9]{13}|[\+][0-9]{7}[\-][0-9]{6}|[\+][0-9]{3}[\-][0-9]{8}/', $str, $matches);
            $number = $matches[0][0];

        return $number;
    }

    /*
     * Get Last Email
     * messages serial No
     */
    private function getMaxEmailSerailNo ($ticket_id) {
        $sql = "SELECT COUNT(ticket_id) AS total from email_messages WHERE ticket_id='$ticket_id'";
        $result = $this->_db_con->query($sql);
        if (is_array($result)) return $result[0]->total+1;
        return 0;
    }

    /*
     * Get Customer ID
     */
    public function getCustomerIdByEmail($name=null, $email) {
        $sql = "SELECT customer_id FROM email_address_book WHERE email = '$email'";
        $result = $this->_db_con->query($sql);
        if (is_array($result))
            return $result[0]->customer_id;

        $customer_id = time();
        $sql = "INSERT INTO email_address_book SET customer_id='$customer_id', name='$name', email='$email'";
        $this->_db_con->query($sql);
        return $customer_id;
    }

    /*
     * Email ID by
     * email subject
     */
    public function getEmailIdBySubject($subject){
        $a = preg_match("/\[[0-9]{10}\]/", $subject, $matches);
        if ($a > 0) {
            return trim($matches[0], "[]");
        }
        return null;
    }

    /*
     * Email Id by
     * email-reference.
     */
    public function getEmailIdByReference($references=null) {
        if (empty($references))
            return null;

        $obj = new stdClass();
        $obj->email_id = '';
        $obj->reference = '';

        $reference_array = explode('> <', $references);
        if (is_array($reference_array)) {
            foreach ($reference_array as $ref) {
                $ref = trim($ref, "< >");
                if (!empty($ref)) {
                    $thread_mail = $this->getEmailIdByOriginalId($ref);
                    if (!empty($thread_mail)) {
                        $obj->email_id = $thread_mail->ticket_id;
                    }
                    if (!empty($obj->reference)) {
                        $obj->reference .= '|';
                    }
                    $obj->reference .= $ref;
                }
            }
        }
        return $obj;
    }

    public function addNewEmail($mail_meta, $mail_content, $fetch_object,    $attachment_save_path, $conn, $attachment_directory_owner, $skill_id, $apply_domain_rule, $pop3_user, $debug, $debug_new_line){
        if (!is_array($mail_meta) || !is_array($mail_content))
            return false;

        $emailMessagesObj = $this->setEmailMessages($mail_meta, $mail_content, $fetch_object);
        $eTicketInfoObj = $this->setETicketInfo($emailMessagesObj, $mail_meta, $fetch_object);

        if (empty($eTicketInfoObj->skill_id) || !empty($this->getEmailIdByOriginalId($emailMessagesObj->original_mail_id)))
            return false;





        if (!empty($references)) {
            $reference_array = explode('> <', $references);
            if (is_array($reference_array)) {
                foreach ($reference_array as $ref) {
                    $ref = trim($ref, "< >");
                    if (!empty($ref)) {
                        if (empty($thread_id)) {
                            $thread_mail = get_mail_message_by_original_id($ref, $conn);
                            if (!empty($thread_mail)) {
                                $thread_id = $thread_mail[0]->ticket_id;
                            }
                        }
                        if (!empty($reference_str)) {
                            $reference_str .= '|';
                        }
                        $reference_str .= $ref;
                    }
                }
            }
        }


       // add new thread.

        if (empty($ticketinfo)) return false;

        $mail_id = get_num_of_mails($thread_id, $conn);
        $mail_id++;
        $mail_id = sprintf("%03d", $mail_id);
        $has_attachment = 'N';
        $attachment_i = 0;
        $email_attachment_files = [];
        $is_same_attachment_files_exist = [];
        $attachment_name_with_id = [];

        if (is_array($mail_content)) {
            $i = 0;
            foreach ($mail_content as $mail_content_i) {
                if ($i > 0) {
                    if (isset($mail_content_i['type'])) {
                        if ($mail_content_i['type'] == 0 && !isset($mail_content_i['is_attachment'])) {
                            if ($mail_content_i['subtype'] == 'PLAIN' && !empty($mail_content_i['data'])) {
                                $mail_body = nl2br($mail_content_i['data']);
                            } else if (!empty($mail_content_i['data'])) {
                                $mail_body = $mail_content_i['data'];
                            }
                        }
                        if (isset($mail_content_i['is_attachment']) && $mail_content_i['is_attachment']) {
                            $file_name = $mail_content_i['filename'];
                            $file_name = in_array($file_name, $is_same_attachment_files_exist) ? $attachment_i.$file_name : $file_name;
                            $is_same_attachment_files_exist[] = $file_name;
                            $mail_content_i['filename'] = $file_name;
                            $attachment_name_with_id[$mail_content_i['content_id']] = $file_name;
                            $email_attachment_files[$attachment_i] = $mail_content_i;
                            $attachment_i++;
                            write_to_file($thread_id, $mail_id, $attachment_i, $now, $mail_content_i, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line);
                        }
                    }
                }
                $i++;
            }
        }

        $mailer_name = !empty($from_address['name']) ? explode(' ',$from_address['name']):'';
        if (is_array($mailer_name)) {
            $first_name = current($mailer_name);
            $last_name = trim(str_replace($first_name, "", $from_address['name']));
        } else {
            $first_name = $from_address['name'];
            $last_name = '';
        }
        $first_name = !empty($first_name) ? $conn->escapeString($first_name) : "";
        $last_name = !empty($last_name) ? $conn->escapeString($last_name) : "";

        $sql = "SELECT * FROM e_ticket_info WHERE ticket_id='$thread_id'";
        $isPhoneNumberExists = $conn->query($sql);

        $phone_number = empty($references) ? get_phone_number_from_email($subject) : '';
        $phone_number = empty($phone_number) ? get_phone_number_from_email($mail_body) : $phone_number;
        $phone_number = !empty($phone_number) ? $phone_number[0] : '';

        if ($attachment_i > 0) {
            $has_attachment = 'Y';
        }

        $status_txt = '';
        $is_reopen = '';
        $last_reopen_time = '';
        $session_id = $ticketinfo[0]->session_id;

        if ($ticketinfo[0]->status == 'C') {	//pending-customer
            $status_txt = " status='P',";
        }elseif ($ticketinfo[0]->status == 'S' || $ticketinfo[0]->status == 'E'){
            $status_txt = " status='O',";
            $is_reopen = 'Y';
            //$reopen_count = 'reopen_count+1';
            $last_reopen_time = $now;
        }

        if (empty($isPhoneNumberExists)){
            $sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails='$mail_id', first_name='$first_name',last_name='$last_name', phone='$phone_number', skill_id='$skill_id', ";
        }else {
            $sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails='$mail_id', first_name='$first_name',last_name='$last_name', skill_id='$skill_id', ";
        }
        if (!empty($is_reopen)){
            $session_id = generate_session_id($thread_id, $ticketinfo[0]->session_id);
            $sql .= " is_reopen='$is_reopen', reopen_count=reopen_count+1, last_reopen_time='$last_reopen_time', session_id='$session_id', ";
        }
        $sql .= !empty($GLOBALS['priority_emails_list']) && in_array($from_email, $GLOBALS['priority_emails_list']) ? " is_priority='Y'" : "";
        $sql = rtrim(trim($sql),',');
        $sql .= " WHERE ticket_id='$thread_id'";
        $conn->setCharset("utf8");
        $is_eticket_update = $conn->query($sql);
        if ($is_eticket_update && !empty($is_reopen)){
            generate_log_email_session($thread_id, $conn);
        }

        $mail_body = changeImageURL($mail_body, $attachment_save_path, $now, $thread_id, $mail_id, $email_attachment_files, $attachment_name_with_id);
        $mail_body = base64_encode(trim($conn->escapeString($mail_body)));
        $subject = base64_encode(trim($conn->escapeString($subject)));
        $from_name = trim($conn->escapeString($from_name));
        $from = iconv_mime_decode($from, 0, 'UTF-8');
        $from = trim($conn->escapeString($from));

        $sql = "INSERT INTO email_messages SET ticket_id='$thread_id', mail_sl='$mail_id', original_mail_id='$original_mail_id', ";
        $sql .= " subject='$subject', from_name='$from_name', from_email='$from_email', mail_from='$from', mail_to='$mail_to', mail_cc='$cc', ";
        $sql .= " reference='$reference_str', mail_body='$mail_body', has_attachment='$has_attachment', skill_id='$skill_id', tstamp='$now', status='N', phone='$phone_number', session_id='$session_id' ";
        $conn->setCharset("utf8");
        $conn->query($sql,'', array('--', ';', '\\','UNION','CAST'));

        $sql = "INSERT INTO e_ticket_activity SET ticket_id='$thread_id', agent_id='$from_email', activity='M', ".
            "activity_details='$mail_id', activity_time='$now'";
        $conn->query($sql);

        /////////////System Distribution///////////
        $sql = "SELECT email_id FROM emails_in WHERE email_id = '$thread_id'";
        if (!$conn->query($sql)) {
            $sql = "INSERT INTO emails_in SET email_id= '$thread_id', tstamp = UNIX_TIMESTAMP('$now'), ";
            $sql .= " sender_name='$from_name', phone='$phone_number', email='$from_email', `subject`='$subject', skill_id='$skill_id', `language`='EN', status='N', agent_id='' ";
            $conn->setCharset("utf8");
            $conn->query($sql);
        }
        /////////////System Distribution///////////

        file_put_contents('/usr/local/ccpro/email/log/' . $thread_id . '_' . $mail_id . '_meta.log', print_r($mail_meta, true));
        file_put_contents('/usr/local/ccpro/email/log/' . $thread_id . '_' . $mail_id . '_content.log', print_r($mail_content, true));

        if (empty($is_reopen) && empty($is_thread_new)){
            check_empty_log_email_session($ticketinfo[0], $conn);
            update_log_email_session($conn, $thread_id);
        }
        return true;
    }

    function test(){
        var_dump(EMAIL_EWS);
        var_dump(TEST_CONST);
        $mail = new Mail('demogsl007@gmail.com', 'dqukzgighucplrwt', 'pop.gmail.com', '995', 'EIM');
        $mail->pop3_login();
    }

    /*
     * Start fetching
     */
    public function startFetching(){
        $inboxConfigs = $this->getInboxConfigurations();
        $this->dd($inboxConfigs);
        if (!empty($inboxConfigs)) {
            foreach ($inboxConfigs as $config) {
                if ($config->fetch_method == EMAIL_POP3 || $config->fetch_method == EMAIL_IMAP) {
                    $this->POP3($config);
                }
            }
        }
    }


    public function POP3($fetch_info){
        try {
            $mail_server = new Mail($fetch_info->username, $fetch_info->password, $fetch_info->host, $fetch_info->port, $fetch_info->fetch_method);
            $mail_server->pop3_login();
            if ($mail_server->pop3_is_connected()) {
                $this->showDebug("'------------Connected------------'");
                $mails = $mail_server->pop3_list('', $this->_max_size, $this->_debug);
                if (is_array($mails)) {
                    $this->showDebug('Number of mails fetched: '.count($mails));
                    foreach ($mails as $mail) {
                        $mdetails = $mail_server->mail_mime_to_array($mail['msgno'], true);
                        $this->showDebug('Mail details: ['.$mail['msgno'].']');
                        $this->showDebug($mdetails);
                        $this->addNewEmail($mail, $mdetails, $fetch_info);
                        if ($fetch_info->email_delete == "Y") {
                            $mail_server->pop3_delete($mail['msgno']);
                        }
                    }
                    if ($fetch_info->email_delete == "Y") {
                        $mail_server->pop3_expunge();
                    }
                } else {
                    $this->showDebug("No mails found");
                }
                $mail_server->pop3_close();
            } else {
                $this->showDebug('not connected');
            }
        } catch (Throwable $t) {
            $this->GPrint($t->getCode());
            $this->GPrint($t->getMessage());
        }
    }

    /*
     *
     */
    private function generateEmailId(){
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->getRandomDigits(10);
            $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
            $result = $this->_db_con->query($sql);
            if (empty($result)) return $id;
            $i++;
        }
        return $id;
    }

    public function getRandomDigits($num_digits){
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . random_digits($num_digits - 1);
    }

    /*
     * Get Skill ID using filter
     * To email address
     * From email address
     * Subject
     * Domain Rules
     */
    function getSkillIdByFilter($to, $from, $subject, $fetchObj){
        $priority_emails_list = !empty($this->_global_settings->priority_email) ? explode(",", $this->_global_settings->priority_email) : "";
        $priority_email_skill = $this->_global_settings->priority_email_skill;

        // GET SKILL Email From Address
        if (!empty($from)){
            $from_address = $this->parseEmailAddress($from);
            if (!empty($priority_emails_list) && !empty($priority_email_skill) && in_array($from_address['email'], $priority_emails_list)) {
                return $priority_email_skill;
            } elseif ($fetchObj->apply_domain_rule == 'Y') {
                $result = $this->getSkillIdByDomain($from_address['email']);
                if (!empty($result))
                    return $result->skill_id;
            }
        }

        // GET SKILL Email Subject
        if (!empty($subject)){
            $skill_id = $this->getSillIdBySubject($subject);
            if (!empty($skill_id))
                return $skill_id;
        }

        // GET SKILL Email To Address
        if (!empty($to)){
            $skill_id = $this->getSkillIdByEmail2Skill($to);
            if (!empty($skill_id)){
                return $skill_id;
            }
        }

        return $fetchObj->skill_id;
    }

    /*
     * Skill Id
     * by email to address
     */
    private function getSkillIdByEmail2Skill ($emails){
        if (empty($this->_subject_keyword)) {
            $sql = "SELECT * FROM e_mail2skill";
            $result = $this->_db_con->query($sql);
            if (!empty($result)) {
                $this->_email_2_skill = array_column($result, 'skill_id', 'email');
            }
        }

        if (!empty($this->_email_2_skill) && !empty($emails)) {
            $emails = explode(",", $emails);
            foreach ($emails as $email) {
                $ary = $this->parseEmailAddress($email);
                if (!empty($ary['email']) && array_key_exists($ary['email'], $this->_email_2_skill)) {
                    return $this->_email_2_skill[$ary['email']];
                }
            }
        }

        return null;
    }

    /*
     * Skill id
     * filter by subject
     */
    private function getSillIdBySubject($subject){
        if (empty($this->_subject_keyword)) {
            $sql = "SELECT * FROM email_routing";
            $this->_subject_keyword = $this->_db_con->query($sql);
        }

        if (!empty($this->_subject_keyword) && is_array($this->_subject_keyword)){
            foreach ($this->_subject_keyword as $keyword){
                if (preg_match("/$keyword->keyword/", $subject)) {
                    return $keyword->skill_id;
                }
            }
        }
        return null;
    }

    /*
     * Skill id
     * filter by from-address domain
     */
    private function getSkillIdByDomain($from){
        $explode = explode("@",$from);
        $domain = array_pop($explode);
        if (!empty($domain)){
            $sql = "SELECT skill_id FROM email_domain2skill WHERE domain = '{$domain}'";
            $result = $this->_db_con->query($sql);
            return is_array($result) ? $result[0] : null;
        }
        return null;
    }

    /*
     * Parse email address
     * as name, email
     */
    function parseEmailAddress($email){
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

    /*
     * Validate specific email address.
     */
    private function getValidEmailAddress($string){
        //$pattern = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $string, $matches);
        return $matches[0][0];
    }

    /*
     * Inbox settings
     */
    private function getInboxConfigurations(){
        $sql = "SELECT skill_id,`name`,fetch_method,host,username,`password`,`port`, email_delete, apply_domain_rule FROM email_fetch_inboxes WHERE `status` = 'A'";
        return $this->_db_con->query($sql);
    }

    /*
     *  Global settings
     */
    private function getEmailGlobalSettings(){
        $sql = "SELECT * FROM cc_settings WHERE module_type='".MOD_EMAIL."' ";
        $result = $this->_db_con->query($sql);
        $obj = new stdClass();
        if (!empty($result)){
            foreach ($result as $key){
                $itme = $key->item;
                $obj->$itme = $key->value;
            }
        }
        return $obj;
    }

    /*
     * Email ID By
     * email original id.
     */
    private function getEmailIdByOriginalId($oid=''){
        if (empty($oid)) return null;
        $sql = "SELECT ticket_id FROM email_messages WHERE original_mail_id='$oid'";
        $result = $this->_db_con->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    /*
     * Delete log files
     */
    private function deleteEmailLog() {
        $now = time();
        $count = 0;
        $startTime = new DateTime("$this->_log_delete_start_time");
        $endTime = new DateTime("$this->_log_delete_end_time");

        if (date("H:i", $now) >= $startTime->format("H:i") &&  date("H:i", $now) <= $endTime->format("H:i")) {
            $iterator = new DirectoryIterator($this->_log_file_path);
            foreach ($iterator as $fileinfo) {
                if ($count == $this->_log_delete_count)
                    break;

                if ($fileinfo->isFile() && $now - $fileinfo->getCTime() >= $this->_log_delete_day) {
                    unlink($fileinfo->getRealPath());
                    $count++;
                }
            }
        }
        return null;
    }

    function showDebug($data){
        if ($this->_debug)
            $this->GPrint($data);
    }

    function GPrint($obj){
        echo"<pre>".print_r($obj,true)."</pre>";
    }

    function dd($value){
        echo "<pre>";var_dump($value);echo "</pre>";
        die();
    }

}

