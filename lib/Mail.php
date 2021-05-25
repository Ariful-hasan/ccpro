<?php
class Mail
{
    var $email_address = '';
    var $email_user = '';
    var $email_host = '';
    var $host_port = '';
    var $is_ssl = false;
    var $pass = '';
    var $connection = null;
    var $fetch_method = null;


    function __construct($user, $pass, $host, $port, $fetch_method)
    {
        $this->email_address = $user;
        $this->pass = $pass;

        $arr = explode('@', $this->email_address);

        /////    FOR Windows ////////
        //        $this->email_user = trim($arr[0]);

        $this->email_user = $this->email_address;
        $this->email_host = $host;
        $this->host_port = $port;
        $this->fetch_method = $fetch_method;
    }

    function pop3_login($folder = 'INBOX')
    {

        $ssl = "ssl";
        $method = $this->fetch_method=="EP3"? "pop3" : "imap";
        $host = empty($this->host_port) ? $this->email_host : $this->email_host . ':' . $this->host_port;
        $connection_string = "{".$host."/".$method."/".$ssl."}";

        ///////WORKING ONE FOR GMAIL
        $this->connection = (imap_open("{pop.gmail.com:995/pop3/ssl/novalidate-cert/notls}INBOX", $this->email_user, $this->pass)); ///gmail
        var_dump($this->connection);
        ///////WORKING ONE FOR OUTLOOK
        //$this->connection = (imap_open("{imap-mail.outlook.com:993/imap/ssl}INBOX", $this->email_user, $this->pass)); ///outlook
        $err = imap_errors();
        if ($err)var_dump($err);
    }

    function pop3_stat()
    {
        $check = imap_mailboxmsginfo($this->connection);
        return ((array)$check);
    }

    function pop3_is_connected()
    {
        return $this->connection ? true : false;
    }

    function pop3_close()
    {
        return imap_close($this->connection);
    }
    /*
        array(15) {
          ["subject"]=>
              string(30) "Get Gmail on your mobile phone"
          ["from"]=>
              string(36) "Gmail Team <mail-noreply@google.com>"
          ["to"]=>
          string(29) "CC Test <ccenter21@gmail.com>"
          ["date"]=>
              string(31) "Tue, 20 Mar 2012 22:05:14 -0700"
          ["message_id"]=>
              string(68) "<CAHa0AqpzD3kGfSGf8M4dNCL8HWZQ4xfQCHrGnpMZUYzXLB=TzA@mail.gmail.com>"
          ["size"]=>
              int(2428)
          ["uid"]=>
              int(2)
          ["msgno"]=>
              int(2)
          ["recent"]=>
              int(1)
          ["flagged"]=>
              int(0)
          ["answered"]=>
              int(0)
          ["deleted"]=>
              int(0)
          ["seen"]=>
              int(0)
          ["draft"]=>
              int(0)
          ["udate"]=>
              int(1332306314)
        }
    */
    function pop3_list($message="", $email_size_limit, $debug=false, $debug_new_line='<br />')
    {
        //$email_size_limit = 52428800; ////50MB////
        //$email_size_limit = 5242880; ////5MB////
        $result = array();
        $range = '';
        if ($message) {
            $range = $message;
        } else {
            /*
            $MC = imap_check($this->connection);
            //$range = "1:".$MC->Nmsgs;
            $range = "1:".$MC->Nmsgs;
            */
            //var_dump(imap_header($this->connection, 1));
//			echo 'status';
//$status = imap_setflag_full($this->connection, "1,3", "\\Seen \\Flagged");
//echo gettype($status) . "\n";
//echo $status . "\n";
            $unreads = imap_search($this->connection, 'UNSEEN');
            if (is_array($unreads)) $range = implode(',', $unreads);
            //$range = '';
            //var_dump($unreads);
        }
        if ($debug) echo 'Fetching range '.$range.$debug_new_line;
        if (!empty($range)) {
            $response = imap_fetch_overview($this->connection, $range);
            if ($debug) {
                echo 'Messages: ' . $debug_new_line;
                var_dump($response);
                echo $debug_new_line;
            }
            foreach ($response as $msg) {
//                echo ' Email Size <br/>';var_dump($msg->size);echo '<br/>';
                if ($msg->size < $email_size_limit){
                    $result[$msg->msgno] = (array)$msg;
                }
            }
        }
        return $result;
    }

    function pop3_retr($message)
    {
        return(imap_fetchheader($this->connection, $message, FT_PREFETCHTEXT));
    }

    function pop3_delete($message)
    {
        return(imap_delete($this->connection, $message));
    }

    function pop3_expunge()
    {
        return imap_expunge($this->connection);
    }

    function mail_parse_headers($headers)
    {
        $headers = preg_replace('/\r\n\s+/m', '',$headers);
        preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
        foreach ($matches[1] as $key =>$value) $result[$value] = $matches[2][$key];
        return($result);
    }

    function mail_mime_to_array($mid, $parse_headers=false)
    {
        $mail = imap_fetchstructure($this->connection, $mid);
        $mail = $this->mail_get_parts($mid, $mail, 0);
        //var_dump($mail);
        if ($parse_headers) $mail[0]["parsed"] = $this->mail_parse_headers($mail[0]["data"]);
        return($mail);
    }

    function mail_get_parts($mid, $part, $prefix)
    {
        $attachments = array();
        $attachments[$prefix] = $this->mail_decode_part($mid, $part, $prefix);
        if (isset($part->parts)) // multipart
        {
            $prefix = ($prefix == "0") ? "" : "$prefix.";
            foreach ($part->parts as $number=>$subpart)
                $attachments = array_merge($attachments, $this->mail_get_parts($mid, $subpart, $prefix.($number+1)));
        } else {
            if ($prefix == 0) $attachments[1] = $this->mail_decode_part($mid, $part, 1);
        }
        return $attachments;
    }

    function mail_decode_part($message_number, $part, $prefix)
    {
        $attachment = array();
        //echo '<br>part<br>';var_dump($part);echo '<br>';
        if ($part->ifdparameters) {
            foreach($part->dparameters as $object) {
                $attachment[strtolower($object->attribute)] = $object->value;
                if (strtolower($object->attribute) == 'filename') {
                    $attachment['is_attachment'] = true;
                    $attachment['filename'] = $object->value;
                }
            }
        }

        if ($part->ifparameters) {
            foreach($part->parameters as $object) {
                $attachment[strtolower($object->attribute)] = $object->value;
                if (strtolower($object->attribute) == 'name') {
                    $attachment['is_attachment'] = true;
                    $attachment['name'] = $object->value;
                }
            }
        }
        $attachment['type'] = $part->type;
        $attachment['subtype'] = $part->subtype;
        $attachment['content_id'] = isset($part->id) ? str_replace(array('<', '>'), '', $part->id) : "";
        $attachment['data'] = imap_fetchbody($this->connection, $message_number, $prefix);

        if ($part->encoding == 3) { // 3 = BASE64
            $attachment['data'] = base64_decode($attachment['data']);
        } elseif ($part->encoding == 4) { // 4 = QUOTED-PRINTABLE
            $attachment['data'] = quoted_printable_decode($attachment['data']);
        }

        return($attachment);
    }

}