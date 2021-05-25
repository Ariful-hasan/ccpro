<?php

    $allowed_hour = date("H");
   // die($allowed_hour);

    if ($allowed_hour  < 9 && $allowed_hour > 21) { // 9:00 PM to 9:00 AM
        exit(0);
    }

    require_once('lib/phpmailer/autoload.php');

    $ivrs = ['AM' => 'Click to call Robi','AN'=>'Click to call Airtel'];
    $files = [];
    $hour = date("H", strtotime(" - 1 hour"));
    $report_time = date("d M Y");
    $presentation_start_hour = $hour > 12 ? $hour % 12 : $hour;
    $presentation_start_hour .= ":00:00 ". ($hour >= 12 ? "PM" : "AM");
    $presentation_end_hour = ($hour+1) > 12 ? ($hour+1) % 12 : ($hour + 1);
    $presentation_end_hour .= ":00:00 ". ($hour >= 12 ? "PM" : "AM");

    $start_time = strtotime(date("Y-m-d $hour:00:00"));
    $start_time = $hour == 9 ? strtotime($start_time." - 11 hour") : $start_time;
    $end_time = strtotime(date("Y-m-d $hour:59:59"));


    $mail_address = "Dear Concern, <br><br>";
    $mail_signature .= "<br><br><br>Thanks,<br>gPlex Contact Center";
    $mail_body_date =  date("d M Y");



    $script_dir = '';
    include_once('conf.email.php');
    include_once($script_dir . 'conf.php');
    include_once($script_dir . 'lib/DBManager.php');
    $conn = new DBManager($db);

    foreach ($ivrs as $ivr_id => $ivr_name) {




        $sql .= " SELECT count(isrl.caller_id) AS aggregate ";
        $sql .= " FROM  ivr_service_request_log AS isrl INNER join ivr ON isrl.ivr_id = ivr.ivr_id ";
        $sql .= " LEFT JOIN ivr_service_code AS isc ON isrl.disposition_code = isc.disposition_code ";
        $sql .= " WHERE isrl.tstamp BETWEEN '{$start_time}' AND '{$end_time}' AND isrl.ivr_id='{$ivr_id}' ";

        $mail_subject = "{$ivr_name} LN_" . date("dmy", strtotime("-1 hour")) ."_". sprintf('%02d', $hour + 1)."00_" . ($hour - 7);

        $no_record_mail_body = "There is no call in {$ivr_name} LN HVC/LVC base from {$presentation_start_hour} - {$presentation_end_hour} ";
        $mail_body = "Please find the attachment for {$ivr_name} LN HVC/LVC base from {$presentation_start_hour} - {$presentation_end_hour} ";

        $result = $conn->query($sql);

        if (!empty($result[0]->aggregate) && $result[0]->aggregate > 0) {
            $filename = "/tmp/ivr_{$ivr}_service_request_log_" . date('Y_m_d') . "_at_{$hour}.csv";
            $files[] = $filename;

            $sql = "SELECT 'Date Time', 'Caller', 'Disposition', 'IVR' , 'Status' UNION ALL ";
            $sql .= " SELECT FROM_UNIXTIME(isrl.tstamp), isrl.caller_id, isc.service_title, ivr.ivr_name, isrl.status ";
            $sql .= " FROM  ivr_service_request_log AS isrl INNER join ivr ON isrl.ivr_id = ivr.ivr_id ";
            $sql .= " LEFT JOIN ivr_service_code AS isc ON isrl.disposition_code = isc.disposition_code ";
            $sql .= " WHERE isrl.tstamp BETWEEN '{$start_time}' AND '{$end_time}' AND isrl.ivr_id='{$ivr_id}' ";
            $sql .= " INTO OUTFILE '{$filename}' ";

            $result = $conn->query($sql);
        }
        //}


        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Username = 'palashbawn5@gmail.com';
        $mail->Password = 'pyraxyuzfdygidlk';
        $mail->setFrom('palashbawn5@gmail.com', 'gPlex CCT');
        $mail->addAddress('chayanbawn@gmail.com', 'Chayan Bawn');
        $mail->addCC('mahady@genuitysystems.com');
        $mail->addCC('ccnoc@genuitysystems.com');
        $mail->Subject = $mail_subject;

        $mail->msgHTML($mail_address.$no_record_mail_body.$mail_body_date.$mail_signature);

        //Attach an image file
        if (is_array($files) && count($files)) {
            $mail->msgHTML($mail_address.$mail_body.$mail_body_date.$mail_signature);

            foreach ($files as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        if ($mail->send()) {
            foreach ($files as $attachment) {
                if (file_exists($attachment)) {
                    unlink($attachment);
                }
            }
        }

    }
    exit(0);
