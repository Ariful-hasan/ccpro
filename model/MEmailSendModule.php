<?php
class MEmailSendModule extends Model
{
    function __construct() {
        parent::__construct();
    }
    function random_digits($num_digits) {
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
    }
    private function getNewId(){
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT email_id FROM emailsendmodule_email WHERE email_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            $i++;
        }
        return $id;
    }

    function Send($data, $att_files=null){

        $email_id = $this->getNewId();
        if (empty($email_id)) return false;

        $has_attachment = 'N';
        if (is_array($att_files['error'])) {
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }
        }

        $data->email_id = $email_id;
        $data->has_attachment = $has_attachment;
        $data->subject = base64_encode($data->subject);
        $data->body = base64_encode($data->body);

        $sql = "INSERT INTO emailsendmodule_email SET ";
        foreach ($data as $key => $value)
            $sql .= "`$key`='$value', ";
        $sql = rtrim(trim($sql), ",");
        if ($this->getDB()->query($sql)){
            $this->saveEmailsOut($data);
            $this->saveAttachment($data->has_attachment, $att_files, $data->email_id, $data->tstamp);
            return true;
        }
        return false;
    }

    function saveEmailsOut($data){
        $sql = "INSERT INTO emailsendmodule_out SET ";
        foreach ($data as $key => $value)
            $sql .= " `$key`='$value', ";
        $sql = rtrim(trim($sql), ",");
        return $this->getDB()->query($sql);
    }

    function saveAttachment($has_attachment="N", $att_files, $email_id, $time){
        $now = $time;
        $i = 0;
        $response = false;
        if ($has_attachment == 'Y') {
            include('conf.email.php');

            $yy = date("y", $now);
            $mm = date("m", $now);
            $dd = date("d", $now);
            $dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $email_id . '/' ;
            if (!is_dir($dir)) mkdir($dir, 0750, true);
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $fname = $att_files["name"][$i];
                    $fsubtype = strtoupper( end((explode(".", $fname))) );
                    $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                    $attachment_i = $i+1;
                    move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);

                    $fname = $this->getDB()->escapeString($fname);
                    $sql = "INSERT INTO emailsendmodule_attachment SET email_id='$email_id', attach_type='$ftype', attach_subtype='$fsubtype', part_position='$attachment_i', file_name='$fname' ";
                    $response = $this->getDB()->query($sql);
                }
                $i++;
            }
        }
        return $response;
    }







    function createNewEmail($agent_id, $skill_name, $skill_email, $name, $email, $cc, $bcc, $did, $subject, $mail_body, $status, $skill_id, $ccmails, $att_files)
    {
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try) {
            $id = $this->random_digits(10);
            $sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            $i++;
        }
        if (empty($id)) return false;

        $email_cc = !empty($cc) ? implode(",", $cc) :'';
        $email_bcc = !empty($bcc) ? implode(",", $bcc) : '';

        $has_attachment = 'N';
        if (is_array($att_files['error'])) {
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }
        }

        $now = time();
        $email_fetch_inbox_name = self::getEmailFetchInboxName($skill_email);
        $email_fetch_inbox_name = !empty($email_fetch_inbox_name) ? $email_fetch_inbox_name : $skill_name;

        $subject = '['. $id .'] ' . $subject;
        $subject = nl2br(base64_encode($subject));
        $mail_body = nl2br(base64_encode($mail_body));
        $full_name = explode(' ',$name);
        $first_name = reset($full_name);
        $last_name = end($full_name);
        $customer_id =  $this->is_customer_exists($email);
        $customer_id = !empty($customer_id) ? $customer_id : $this->get_customer_id();
        $phone = $this->get_phone_number_from_email($subject);
        $phone = !empty($phone) ? $phone : $this->get_phone_number_from_email($mail_body);
        $created_for_email = explode(",", $email);
        $session_id = $id.'001';
        $in_kpi = !empty($status) && ($status=="S" || $status=="E") ? "Y" : "N";
        $close_time = !empty($status) && ($status=="S" || $status=="E") ? $now : "0";
        $sql = "INSERT INTO e_ticket_info SET ticket_id='$id', skill_id='$skill_id', subject='$subject', status='$status', ".
            "mail_to='$skill_email', created_by='$agent_id', created_for='$created_for_email[0]', assigned_to='', status_updated_by='$agent_id', ".
            "num_mails='1', disposition_id='$did', last_update_time='$now', create_time='$now', ".
            "first_name='$first_name', last_name='$last_name', phone='$phone', customer_id='$customer_id', agent_id='$agent_id', ".
            " in_kpi='$in_kpi', first_open_time='$now', closed_time='$close_time', session_id='$session_id', fetch_box_name='$email_fetch_inbox_name', fetch_box_email='$skill_email'";
        $is_update = $this->getDB()->query($sql);

        if ($is_update) {
            $from = empty($name) ? $created_for_email[0] : "$name <$created_for_email[0]>";
            $sql = "INSERT INTO email_messages SET ticket_id='$id', mail_sl='001', original_mail_id='', ".
                "subject='$subject', from_name='$name', from_email='$skill_email', mail_from='$from', ".
                "mail_to='$email', mail_cc='$email_cc', mail_bcc='$email_bcc', agent_id='$agent_id',  ".
                "reference='', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N', ".
                "phone='$phone', email_status='$status', email_did='$did', session_id='$session_id'";
            $this->getDB()->query($sql);

            //first decide who will receive the mail
            $sql = "INSERT INTO emails_out SET ".
                "ticket_id='$id', ".
                "mail_sl='001', ".
                "subject='$subject', ".
                "from_name='$email_fetch_inbox_name', ".
                "from_email='$skill_email', ".
                "to_email='$email', ".
                "to_name='$name', ".
                "cc='$email_cc', ".
                "bcc='$email_bcc', ".
                "body='$mail_body', ".
                "has_attachment='$has_attachment', ".
                "is_forward='N', ".
                "tstamp='".$now."'";
            $this->getDB()->query($sql);

            $this->addETicketActivity($id, $agent_id, 'M', '001');
            $this->addETicketActivity($id, $agent_id, 'S', $status);
            if (!empty($did)) {
                $this->addETicketActivity($id, $agent_id, 'D', $did);
            }
            if ($has_attachment=='Y'){
                $this->saveAttachment($has_attachment, $att_files, $id, "001", $now);
            }
            /// Log Email Session /////
            $this->generateLogEmailSession($id);
        }

        return $is_update;
    }
}