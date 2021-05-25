<?php
	error_reporting(7);
	set_time_limit(0);
	
	$debug = false;
	$debug_new_line = '<br />';
	//$is_live = true;
	//$script_dir = $is_live ? '' : '';
	//$script_dir = '';
	$script_dir = '';
	include_once('conf.email.php');
	include_once($script_dir . 'conf.php');
	include_once($script_dir . 'lib/DBManager.php');
	$conn = new DBManager($db);
	
	$sql = "SELECT * FROM emails_out ORDER BY tstamp LIMIT 0, 20";
	$result = $conn->query($sql);
	//exit($sql);
	if (is_array($result)) {

		require_once($script_dir . "class.phpmailer.php");
		$mail = new PHPMailer();

		$mail->IsSMTP();							// telling the class to use SMTP
		$mail->PluginDir  = $script_dir;
		$mail->SMTPDebug  = 0;
		$mail->Timeout    = 30;
		$mail->SMTPKeepAlive = true;
		$mail->SMTPAuth   = false;					// enable SMTP authentication
		$mail->Port       = 25;				// set the SMTP port
		$mail->Host   = $smtp_server;	// SMTP server

		//$mail->AddBCC('mdialer@genusys.us');
		//$mail->Sender     = 'vcarrier.mkt@genusys.us';
		$mail->Username   = '';						// SMTP account username
		$mail->Password   = '';						// SMTP account password
		$mail->WordWrap   = 80;

		foreach ($result as $mail_details) {
			//echo $brand->brand_pin . '<br>';
			$mail_details->to_email = trim($mail_details->to_email);
			if (!empty($mail_details->to_email)) {

				$from_email = $mail_details->from_email;
				$from_name = $mail_details->from_name;

				$mail->ClearAllRecipients();
				$mail->ClearReplyTos();
				$mail->ClearAttachments();

				$mail->AddReplyTo($from_email, $from_name);
				$mail_details->From       = $from_email;
				$mail_details->FromName   = $from_name;
				//$mail->Sender     = $from_email;
				$mail->SetFrom($from_email, $from_name);

				if ($mail_details->is_forward == 'Y') $mail_details->subject = 'Fw: ' . $mail_details->subject;
				
				$mail->Subject = $mail_details->subject;

				
				$sql = "SELECT tstamp, mail_from, mail_body FROM email_messages WHERE ticket_id='$mail_details->ticket_id' AND ".
					"mail_sl<'$mail_details->mail_sl' ORDER BY mail_sl DESC LIMIT 100";
				$oldmailtext = $conn->query($sql);
				if (is_array($oldmailtext)) {
					$mail_details->body .= '<br /><br />';
					if ($mail_details->is_forward == 'Y') $mail_details->body .= '<b>---------- Forwarded Message -----------</b><br />';
					else $mail_details->body .= '<b>---------- Previous Message -----------</b><br />';
					
					foreach ($oldmailtext as $textrow) {
						$mail_details->body .= "<br /><b>On " . date("D, M j, Y \a\t g:i A", $textrow->tstamp) . ", " . htmlspecialchars($textrow->mail_from) . " wrote:</b><br /><br />";
						$mail_details->body .= $textrow->mail_body . '<br /><br />';
					}
				}


				$mail->MsgHTML($mail_details->body);
				//$mail->ClearAddresses();
				
				if (!empty($mail_details->cc)) {
					$ccs = explode(',', $mail_details->cc);
					if (is_array($ccs)) {
						foreach ($ccs as $_cc) {
							$_cc = trim($_cc);
							if (!empty($_cc)) $mail->AddCC($_cc);
						}
					}
				}
				
				if (!empty($mail_details->bcc)) {
					$ccs = explode(',', $mail_details->bcc);
					if (is_array($ccs)) {
						foreach ($ccs as $_cc) {
							$_cc = trim($_cc);
							if (!empty($_cc)) $mail->AddBCC($_cc);
						}
					}
				}

				$mail->AddAddress($mail_details->to_email);
				
				if ($mail_details->has_attachment) {
					$sql = "SELECT * FROM email_attachments WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl' ORDER BY part_position LIMIT 5";
					$attachments = $conn->query($sql);
					if (is_array($attachments)) {
						$yy = date("y", $mail_details->tstamp);
						$mm = date("m", $mail_details->tstamp);
						$dd = date("d", $mail_details->tstamp);
						$dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $mail_details->ticket_id . '/' . $mail_details->mail_sl . '/';
						
						foreach ($attachments as $att) {
							if (file_exists($dir . $att->file_name)) {
								//echo 'dir='.$dir . $att->file_name;
								$mail->AddAttachment($dir . $att->file_name);
								if ($debug) echo 'Attaching file - ' . $dir . $att->file_name . $debug_new_line;
							} else {
								if ($debug) echo 'Error [Not found]: Attaching file - ' . $dir . $att->file_name . $debug_new_line;
							}
						}
					}
				}
				
				$is_email_found = true;
				
				/*
				$email_addresses = explode(",", $brand->email);
				$is_email_found = false;

				if (is_array($email_addresses)) {
					foreach ($email_addresses as $eml) {
						$eml = trim($eml);
						if (!empty($eml)) {
							//echo $eml . '<br>';
							$mail->AddAddress($eml);
							$is_email_found = true;
						}
					}
				}*/
				
				//$mail->AddAddress($brand->email);
				
				if ($is_email_found) {
					
					try {
						if ( !$mail->Send() ) {
							$error = "Unable to send to: " . $mail_details->to_email . "<br />";
							throw new phpmailerAppException($error);
						} else {
							$sql_update = "UPDATE email_messages SET tstamp=".time()." WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl'";
							$result = $conn->query($sql_update);
							
							//$sql_update = "UPDATE email_messages SET tstamp=".time()." WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl'";
							//$result = $conn->query($sql_update);
							
							$sql_update = "DELETE FROM emails_out WHERE ticket_id='$mail_details->ticket_id' AND mail_sl='$mail_details->mail_sl'";
							$result = $conn->query($sql_update);
						}
					} catch (phpmailerAppException $e) {
						//$errorMsg[] = $e->errorMessage();
					}
					
				}
			}
		}

		//closing smtp connection
		$mail->SmtpClose();
	}
	//echo $sql;
	exit;

class phpmailerAppException extends Exception {
	public function errorMessage() {
		$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
		return $errorMsg;
	}
}
