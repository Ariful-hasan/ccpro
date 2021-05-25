<?php

class MLanka extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    function createNewEmail($name, $email, $cc, $bcc, $subject, $mail_body, $att_files, $from_name, $from_email)
    {

        $id = $this->random_digits(10);

        if (empty($id)) return false;

        $email_cc = '';
        $email_bcc = '';

        if (!empty($cc) && is_array($cc)) {
            $email_cc = implode(', ',$cc);
        }
        if (!empty($bcc) && is_array($bcc)) {
            $email_bcc = implode(", ", $bcc);
        }

        $has_attachment = 'N';
        if (is_array($att_files['error'])) {
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $has_attachment = 'Y';
                }
            }

        }

        $now = time();
        $subject = '['. $id .'] ' . $subject;
        $subject = nl2br(base64_encode($subject));
        $mail_body = nl2br(base64_encode($mail_body));



        $sql = "INSERT INTO emails_out SET ticket_id='$id', mail_sl='001', ".
                "subject='$subject', from_name='$from_name', ".
                "from_email='$from_email', to_email='$email', to_name='$name', ".
                "cc='$email_cc', bcc='$email_bcc', body='$mail_body', ".
                "has_attachment='$has_attachment', is_forward='N', ". "tstamp='".$now."'";

        $is_update = $this->getDB()->query($sql);


        if ($has_attachment=='Y'){
            $this->saveAttachment($has_attachment, $att_files, $id, "001", $now);
        }


        return $is_update;
    }


    function random_digits($num_digits){
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
    }
    function saveAttachment($has_attachment="N", $att_files, $ticket_id, $sl, $time){
        $now = $time;
        $i = 0;

        if ($has_attachment == 'Y') {

            include('conf.email.php');
            $yy = date("y", $now);
            $mm = date("m", $now);
            $dd = date("d", $now);
            $dir = $attachment_save_path . '/' . $yy . $mm . $dd . '/' . $ticket_id . '/' . $sl . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
            foreach ($att_files['error'] as $err) {
                if ($err == 0) {
                    $fname = $att_files["name"][$i];

                    $file_name_ext = explode(".", $fname);
                    $extention = end($file_name_ext);
                    $fsubtype = strtoupper($extention);

                    $ftype = $fsubtype == 'TXT' || $fsubtype == 'CSV' ? 0 : 7;
                    $attachment_i = $i+1;
                    $filepath = $dir.$fname;

                    move_uploaded_file($att_files["tmp_name"][$i], $dir . $fname);
                    $sql = "INSERT INTO email_attachments SET ticket_id='$ticket_id', mail_sl='$sl', ".
                        "attach_type='$ftype', attach_subtype='$fsubtype', ".
                        "part_position='$attachment_i', file_name='{$filepath}'";

                    $this->getDB()->query($sql);
                }
                $i++;
            }
        }
    }


    /**
     * @param $cli
     * @param int $limit
     * @return array|bool
     */
    public function getDispositionByCli($cli, $limit=15){
       $sql = "SELECT cli, scdl.* FROM cdr_in AS ci INNER JOIN skill_crm_disposition_log AS scdl ";
       $sql .= " ON ci.callid = scdl.callid WHERE ci.cli='{$cli}' ORDER BY ci.tstamp DESC LIMIT {$limit}";

       return $this->getDB()->query($sql);
    }

    public function getEmailTemplates()
    {
        $sql = "SELECT * FROM email_templates WHERE status != 'N' ORDER BY title";
        return $this->getDB()->query($sql);
    }

}