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

    include_once ('lib/Mail.php');

	$fetch_method = $fetch_method == 'ews' ? 'ews' : 'pop3';
	$auto_reply_texts = array();
	$auto_reply_skill_names = array();
	
	if ($debug) echo 'Fetching with method: '.$fetch_method . $debug_new_line;
	
	if ($fetch_method == 'ews') {
		include($web_path . 'EWS_Mail.php');
		$mail_server = new EWS_Mail($ews_host, $ews_user, $ews_password);
		$mails = $mail_server->mail_list();
		if (is_array($mails)) {
			$delete_items = array();
			foreach ($mails as $mail) {
				$mdetails = $mail_server->get_mail_details($mail->ItemId->Id);
				add_new_mail_ews($mail, $mdetails, $attachment_save_path, $mail_server, $conn, $attachment_directory_owner, $debug, $debug_new_line);
				$delete_items[$mail->ItemId->Id] = $mail->ItemId->ChangeKey;
			}
		
			if (count($delete_items) > 0) {
				$mail_server->delete_mails($delete_items, $is_delete_email_after_fetch);
			}
		}
	} else if ($fetch_method == 'pop3') {

		$mail_server = new Mail($pop3_user, $pop3_password, $pop3_host, $pop3_port);
		//$mail_model = new Model_Mail();
		$mail_server->pop3_login();
		if ($mail_server->pop3_is_connected()) {
		    //var_dump('connected');
			$mails = $mail_server->pop3_list('', $debug, $debug_new_line);
            //var_dump($mails);
			if (is_array($mails)) {
				if ($debug) echo 'Number of mails fetched: '.count($mails) . $debug_new_line;
				foreach ($mails as $mail) {
					$mdetails = $mail_server->mail_mime_to_array($mail['msgno'], true);
					if ($debug) {
						echo 'Mail details: [' . $mail['msgno'] . '] ' . $debug_new_line;
						var_dump($mdetails);
						echo $debug_new_line;
					}
                    //var_dump($mdetails);
					add_new_mail($mail, $mdetails, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line);
					if ($is_delete_email_after_fetch) $mail_server->pop3_delete($mail['msgno']);
					//echo 'mail details<br />';
					//var_dump($mail);
					//var_dump($mdetails);
					//echo 'end<br /><br />';
					//exit;
				}
				if ($is_delete_email_after_fetch) $mail_server->pop3_expunge();
			} else {
				if ($debug) echo 'No mails found' . $debug_new_line;
			}
			$mail_server->pop3_close();
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
			$id = random_digits(7);
			$sql = "SELECT ticket_id FROM e_ticket_info WHERE ticket_id='$id'";
			$result = $conn->query($sql);
			if (empty($result)) return $id;
			$i++;
		}
		return $id;
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
	
	function add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn)
	{
		$from_user = $from_address['user'];
		$from_email = $from_address['email'];
		
		$subject = $conn->escapeString($subject);
		//created_by='', created_for='', assigned_to='', status_updated_by='', product_id='', category_id='', 
		$sql = "INSERT INTO e_ticket_info SET ticket_id='$thread_id', skill_id='$skill_id', subject='$subject', status='O', ".
			"mail_to='$mail_to', created_by='$from_email', created_for='$from_email', assigned_to='', status_updated_by='$from_email', ".
			"num_mails='0', last_update_time='$now', create_time='$now'";
		$is_update = $conn->query($sql);
		
		if ($is_update) {
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
					
					$sql = "SELECT skill_name FROM skill WHERE skill_id='$skill_id' LIMIT 1";
					$result = $conn->query($sql);
					if (is_array($result)) $GLOBALS['auto_reply_skill_names'][$skill_id] = $result[0]->skill_name;
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
						"from_email='$mail_to', ".
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
		/*
		$ticketid = substr($subject, 0, 20);
		
		if (substr($ticketid, 0, 1) == '[') {
			$ticketid = substr($ticketid, 1, 7);
			if (strlen($ticketid) == 7 && ctype_digit($ticketid)) return $ticketid;
		}
		*/
		$a = preg_match("/\[[0-9]{7}\]/", $subject, $matches);
		
		if ($a > 0) {
			$ticketid = trim($matches[0], "[]");
			return $ticketid;
		}
		
		return '';
	}
	
	function add_new_mail($mail_meta, $mail_content, $attachment_save_path, $conn, $attachment_directory_owner, $debug, $debug_new_line)
	{
		if (!is_array($mail_meta) || !is_array($mail_content)) return false;

		$now = isset($mail_meta['udate']) ? $mail_meta['udate'] : strtotime($mail_meta['date']);
		
		if (empty($now)) $now = time();
		
		$subject = $mail_meta['subject'];
		$from = $mail_meta['from'];
		$to = $mail_meta['to'];
		$cc = '';
		//var_dump($mail_meta);
		//var_dump($mail_content);
		//die('......add new mail.....');

		//$skill_details = get_skill_from_to_address($to, $conn);
		$skill_details = get_skill_from_to_address_new($to, $from, $subject, $conn);
        //var_dump($skill_details);

		if (!is_array($skill_details)) {
			return false;       ///RND Forced Stop
		}

		$skill_id = $skill_details[0];
		$mail_to = $skill_details[1];

		if (empty($skill_id)) return false;      ///RND Forced Stop
        //var_dump('successsss.......11111111.........');
		$from_address = parse_email_address($from);
		$from_name = $from_address['name'];
		$from_email = $from_address['email'];
		$from_user = $from_address['user'];

		//$mail_id = get_ticket_id();
		$mail_id = '';
		$original_mail_id = substr($mail_meta['message_id'], 1, -1);
		$orig_mail_exist = get_mail_message_by_original_id($original_mail_id, $conn);
		if (!empty($orig_mail_exist)) {
			return false;
		}
        //var_dump('successsss.......222222222222.........');
		$references = isset($mail_meta['references']) ? $mail_meta['references'] : '';
		$thread_id = '';
		$parent_mail_id = '';
		$ref = '';
		$reference_str = '';
		$mail_body = '';
		
		if (isset($mail_content[0]['parsed']['Cc'])) {
			$cc = $mail_content[0]['parsed']['Cc'];
		}

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
		//echo 'ticket info 111111111 </br>'; var_dump($ticketinfo);echo '</br>';
		/*
			If ticket is closed then create new ticket
		*/
		if (empty($ticketinfo) || $ticketinfo[0]->status == 'E') {
			$thread_id = get_ticket_id($conn);
			if (empty($thread_id)) return false;
			$subject = "[" . $thread_id . "] " . $subject;
			add_new_thread($thread_id, $skill_id, $mail_to, $from_address, $subject, $now, $conn);
			$ticketinfo = get_ticket_details($thread_id, $conn);
		}
        //echo 'ticket info      2222222222 </br>'; var_dump($ticketinfo);echo '</br>';
		if (empty($ticketinfo)) return false;
        //echo 'ticket info      333333333333 </br>';
		$mail_id = $ticketinfo[0]->num_mails;
		
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
		
		
		//$j = count($mail_content);
		if (is_array($mail_content)) {
			$i = 0;
			foreach ($mail_content as $mail_content_i) {
				if ($i > 0) {

					if (isset($mail_content_i['type'])) {
						if ($mail_content_i['type'] == 0 && !$mail_content_i['is_attachment']) {
							//test needed
							if ($mail_content_i['type'] == 'PLAIN') {
								$mail_body = $mail_content_i['data'];
							} else if ($mail_content_i['type'] == 'HTML') {
								$mail_body = $mail_content_i['data'];
								//break;
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
        $first_name = reset($mailer_name);
        $last_name = end($mailer_name);

        $phone_number = empty($references) ? get_phone_number_from_email($subject) : '';
        $phone_number = empty($phone_number) ? get_phone_number_from_email($mail_body) : $phone_number;
        $phone_number = !empty($phone_number) ? $phone_number[0] : '';

        //echo '</br> reference </br>';var_dump($references);
        //echo '</br> number </br>';var_dump($phone_number);
        //echo '</br> body </br>';print_r($mail_body);

		if ($attachment_i > 0) {
			$has_attachment = 'Y';
		}
		
		//if (!empty($thread_id)) {
			//if ($thread_id != $mail_id) {
		$status_txt = '';
		if ($ticketinfo[0]->status == 'C') {	//pending-customer
			$status_txt = " status='P',";
		}
		$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1, first_name='$first_name',last_name='$last_name', phone='$phone_number' WHERE ticket_id='$thread_id'";
		$conn->query($sql);
			//}
		//}
		//var_dump($sql);
		//$mail_body = base64_encode($conn->escapeString($mail_body));
        //var_dump($conn->escapeString($mail_body));die;
		$mail_body = base64_encode(trim($conn->escapeString($mail_body)));
		$subject = $conn->escapeString($subject);
		var_dump($from_name);
		
		$sql = "INSERT INTO email_messages SET ticket_id='$thread_id', mail_sl='$mail_id', original_mail_id='$original_mail_id', ".
			"subject='$subject', from_name='$from_name', from_email='$from_email', mail_from='$from', mail_to='$to', mail_cc='$cc', ".
			"reference='$reference_str', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N', phone='$phone_number'";
		$conn->query($sql);
		
		$sql = "INSERT INTO e_ticket_activity SET ticket_id='$thread_id', agent_id='$from_email', activity='M', ".
			"activity_details='$mail_id', activity_time='$now'";
		$conn->query($sql);

		
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

		return true;
	}
	
	
	function add_new_mail_ews($mail_meta, $mail_details, $attachment_save_path, $mail_server, $conn, $attachment_directory_owner, $debug, $debug_new_line)
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

		$skill_details = get_skill_from_to_address($to, $conn);
		
		if (!is_array($skill_details)) {
			return false;
		}
		
		$skill_id = $skill_details[0];
		$mail_to = $skill_details[1];
		//echo $skill_id . 's';
		if (empty($skill_id)) return false;

		$from_address = parse_email_address($from);
		$from_name = $from_address['name'];
		$from_email = $from_address['email'];
		$from_user = $from_address['user'];
		
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
		
		if ($attachment_i > 0) {
			$has_attachment = 'Y';
		}
		
		//if (!empty($thread_id)) {
			//if ($thread_id != $mail_id) {
		$status_txt = '';
		if ($ticketinfo[0]->status == 'C') {	//pending-customer
			$status_txt = " status='P',";
		}
		$sql = "UPDATE e_ticket_info SET status_updated_by='$from_email', last_update_time='$now',$status_txt num_mails=num_mails+1 WHERE ticket_id='$thread_id'";
		$conn->query($sql);
			//}
		//}
		
		$mail_body = $conn->escapeString($mail_body);
		$subject = $conn->escapeString($subject);
		
		$sql = "INSERT INTO email_messages SET ticket_id='$thread_id', mail_sl='$mail_id', original_mail_id='$original_mail_id', ".
			"subject='$subject', from_name='$from_name', from_email='$from_email', mail_from='$from', mail_to='$to', mail_cc='$cc', ".
			"reference='$reference_str', mail_body='$mail_body', has_attachment='$has_attachment', tstamp='$now', status='N'";
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
		$yy = date("y", $mtime);
		$mm = date("m", $mtime);
		$dd = date("d", $mtime);
		$fname = $mail_content['filename'];
		$ftype = $mail_content['type'];
		$fsubtype = $mail_content['subtype'];
		
		$dir = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $thread_id . '/' . $mail_id . '/';

		if (!is_dir($dir)) {
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
		
		if (is_writable($dir)) {
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
				}
				fclose($fh);
				if (!empty($attachment_directory_owner) && $file_created) {
					chown($dir . $fname, $attachment_directory_owner);
				}
			}
		} else {
			echo "The directory is not writable - dir: " . $dir . ', file - ' . $fname . $debug_new_line;
		}
		
		$sql = "INSERT INTO email_attachments SET ticket_id='$thread_id', mail_sl='$mail_id', attach_type='$ftype', attach_subtype='$fsubtype', ".
			"part_position='$attachment_i', file_name='$fname'";
		return $conn->query($sql);
	}


    function get_skill_from_to_address_new($to, $from, $subject, $conn){
        $toes = explode(",", $to);
        // GET SKILL FROM Email (From which email)
        if (!empty($from)){
            $from_address = parse_email_address($from);
            $to_email = get_to_email($toes);
            $result = get_domain_skill_from_from_address($from_address['email'], $conn);
            if (!empty($result) && is_array($result)) return array($result[0]->skill_id, $to_email);
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

    function get_phone_number_from_email($text){
        if (strlen($text) > 0){

            $text = strip_tags($text);
            $text = str_replace("\xc2\xa0",' ',$text);
            preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
            //preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
            //echo 'number</br>';var_dump($matches);echo '</br>';
            $matches = $matches[0];
            return $matches;
        }
        return '';
    }


	
