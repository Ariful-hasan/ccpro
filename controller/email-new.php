<?php
class EmailNew extends Controller{

    function __construct() {
        parent::__construct();

        $licenseInfo = UserAuth::getLicenseSettings();
        if ($licenseInfo->email_module == 'N') {
            header("Location: ./index.php");
            exit;
        }
    }

    function init(){
        include('model/MEmail.php');
        include('model/MAgent.php');
        include('model/MEmailFetchInbox.php');
        $eTicket_model = new MEmail();
        $agent_model = new MAgent();
        $inbox_model = new MEmailFetchInbox();

        $inbox_list = $inbox_model->getEmailFetchInboxes('','','A','Y','','','name,username,show_email_list,title,viewable_skills');
        $inbox_email_list = [];
        if (!empty($inbox_list)){
            foreach ($inbox_list as $itm)
                $inbox_email_list[] = $itm->username;
        }
        $inbox_email_list = base64_encode(serialize($inbox_email_list));

        $agent_options = $agent_model->get_as_key_value();
        $data['agent_list'] = $agent_options;
        $ticket_category = $eTicket_model->getTicketCategory('','A');
        $data['ticket_category'] = array();
        if (!empty($ticket_category)){
            foreach ($ticket_category as $cat)
                $data['ticket_category'][$cat->category_id] = $cat->title;
        }

        $urlParam = "";
        $urlParam .= "&inbox=".$inbox_email_list;
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $data['callid'] = !empty($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
        //dd($_REQUEST['info']);
        $fetch_inbox_info = !empty($_REQUEST['info']) ? unserialize(base64_decode($_REQUEST['info'])) : "";

        $isDateToday = true;
        $data['todaysDate'] = "";
        $data['pageTitle'] = !empty($fetch_inbox_info['title']) ? $fetch_inbox_info['title'] : 'All Emails';
        $data['smi_selection'] = 'email_init';
        if ($etype == 'myjob') {
            $data['pageTitle'] = 'My Emails';
            $data['smi_selection'] = 'email_init_myjob';
        }

        if (!empty($status)){
            $urlParam .= "&status=".$status;
            $isDateToday = false;
        }
        if (!empty($etype)){
            $urlParam .= "&type=".$etype;
            $isDateToday = false;
        }
        if (!empty($allNew)){
            $urlParam .= "&newall=".$allNew;
            $isDateToday = false;
        }

        $urlParam .= "&callid=".$data['callid'];
        if (!empty($fetch_inbox_info) && count($fetch_inbox_info) > 0) {
            $urlParam .= "&info=".$_REQUEST['info'];
        }
        if ($isDateToday){
            $data['todaysDate'] = date("Y-m-d")." 23:59";
        }

        $data['status'] = $status;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';

        $selectOpt = array('*'=>'Select');
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions(true));
        $data['dataUrl'] = $this->url('task=get-email-new-data&act=email-new-init'.$urlParam);
        $data['ticket_category'] = array_merge($selectOpt,$data['ticket_category']);

        $agentId = '';
        $utype = UserAuth::hasRole('admin');
        if (!$utype){
            $agentId = UserAuth::getCurrentUser();
        }
        $data['skill_list'] = [];
        $skill_list = $eTicket_model->getEmailSkill($agentId);
        if (!empty($skill_list)){
            foreach ($skill_list as $key)
                $data['skill_list'][$key->skill_id] = $key->skill_name;
        }
        $data['skill_list'] = array("*"=>"All") + $data['skill_list'];

        if (!empty($_REQUEST['cookie']) && $_REQUEST['cookie'] == "Y"){
            $_COOKIE['crt_sdate'] = !empty($_COOKIE['crt_sdate']) ? $_COOKIE['crt_sdate'] : date("Y-m-d H:i", strtotime("-2 month"));
            $_COOKIE['crt_edate'] = !empty($_COOKIE['crt_edate']) ? $_COOKIE['crt_edate'] : date("Y-m-d H:i");
            $_COOKIE['customer_id'] = !empty($_COOKIE['customer_id']) ? $_COOKIE['customer_id'] : "";
            $_COOKIE['from_email'] = !empty($_COOKIE['from_email']) ? $_COOKIE['from_email'] : "";
            $_COOKIE['did'] = !empty($_COOKIE['did']) ? $_COOKIE['did'] : "";
            $_COOKIE['phone'] = !empty($_COOKIE['phone']) ? $_COOKIE['phone'] : "";

            $_COOKIE['lut_sdate'] = !empty($_COOKIE['lut_sdate']) ? $_COOKIE['lut_sdate'] : "";
            $_COOKIE['lut_edate'] = !empty($_COOKIE['lut_edate']) ? $_COOKIE['lut_edate'] : "";

            $_COOKIE['lut_sdate'] = !empty($_COOKIE['lut_sdate']) ? $_COOKIE['lut_sdate'] : date("Y-m-d H:i", strtotime("-2 month"));
            $_COOKIE['lut_edate'] = !empty($_COOKIE['lut_edate']) ? $_COOKIE['lut_edate'] : date("Y-m-d H:i");

            $_COOKIE['ticket_id'] = !empty($_COOKIE['ticket_id']) ? $_COOKIE['ticket_id'] : "";
            $_COOKIE['last_name'] = !empty($_COOKIE['last_name']) ? $_COOKIE['last_name'] : "";
            $_COOKIE['subject'] = !empty($_COOKIE['subject']) ? $_COOKIE['subject'] : "";
            $_COOKIE['status'] = !empty($_COOKIE['status']) ? $_COOKIE['status'] : "O";
            $_COOKIE['skill_name'] = !empty($_COOKIE['skill_name']) ? $_COOKIE['skill_name'] : "";
            $_COOKIE['mail_to'] = !empty($_COOKIE['mail_to']) ? $_COOKIE['mail_to'] : "";
        } else {
//            $_COOKIE['crt_sdate'] = "";
//            $_COOKIE['crt_edate'] = "";

            $_COOKIE['crt_sdate'] = date("Y-m-d ", strtotime("-2 month"))."00:00";
            $_COOKIE['crt_edate'] = date("Y-m-d H:i");
            $_COOKIE['customer_id'] = "";
            $_COOKIE['from_email'] = "";
            $_COOKIE['did'] = "";
            $_COOKIE['phone'] = "";

            $_COOKIE['lut_sdate'] = "";
            $_COOKIE['lut_edate'] = "";

//            $_COOKIE['lut_sdate'] = date("Y-m-d ", strtotime("-2 month"))."00:00";
//            $_COOKIE['lut_edate'] = date("Y-m-d H:i");

            $_COOKIE['ticket_id'] ="";
            $_COOKIE['last_name'] ="";
            $_COOKIE['subject'] = "";
            $_COOKIE['status'] = "O";
            $_COOKIE['skill_name'] = "";
            $_COOKIE['mail_to'] = "";
        }

        $data['menu_ids'] = "";
        $data['menu_emails'] = "";
        $menus_data = $this->getInboxTopMenus($inbox_list, $skill_list);
        $inbox_menus = $menus_data['inbox_menus'];
        $data['menu_ids'] = $menus_data['menu_ids'];
        $data['menu_emails'] = $menus_data['menu_emails'];
        $data['show_inbox_color'] = "";

        if (count($inbox_menus) > 0 && empty($fetch_inbox_info)){
            $data['topMenuItems'] = $inbox_menus;
            $data['show_inbox_color'] = "Y";
        }else if (!empty($fetch_inbox_info)) {
            $data['topMenuItems'] = array(array('href'=>'task=email-new', 'img'=>'fa fa-envelope', 'label'=>'All Email'));
        }

        $data['side_menu_index'] = 'ticketmng';
        if (!empty($data['callid'])){
            $this->getTemplate()->display_email_popup('email_new', $data);
        } else {
            $this->getTemplate()->display('email_new', $data);
        }
    }

    function getInboxTopMenus($inbox_list, $agent_skill_list){
        $button_colors = array("prpl","yel","ard", "lgrn");
        $agent_allowed_skills = [];
        if (!empty($agent_skill_list) && count($agent_skill_list) > 0){
            foreach ($agent_skill_list as $key)
                $agent_allowed_skills[] = $key->skill_id;
        }

        $inbox_menus = [];
        $menu_ids = [];
        $menu_emails = [];
        if (!empty($inbox_list) && count($inbox_list) > 0){
            $numbers_of_color = count($button_colors);
            $i=0;
            foreach ($inbox_list as $item){
                $info['name'] = $item->name;
                $info['username'] = $item->username;
                $info['title'] = $item->title;

                $temp['href'] = "task=email-new&info=".base64_encode(serialize($info));
                $temp['img'] = "fa fa-envelope";
                $temp['label'] = $item->title;
                $temp['color'] = $button_colors[$i++];

                $id = strtolower(implode("_", explode(" ",$item->title)));
                $temp['property'] = array("id" => $id,"data-email"=>$item->username);

                $viewable_skills = isset($item->viewable_skills) ? explode(",",$item->viewable_skills) : "";
                if (array_intersect($viewable_skills,$agent_allowed_skills)){
                    $inbox_menus [] = $temp;
                    $menu_ids[$id] = $item->username;
                    $menu_emails[] = $item->username;
                }

                $i = $i == $numbers_of_color-1 ? 0 : $i;
            }
        }
        $response['inbox_menus'] = $inbox_menus;
        $response['menu_ids'] = $menu_ids;
        $response['menu_emails'] = $menu_emails;
        return $response;
    }

    function actionGetTopMenuCount(){
        $emails = !empty($_REQUEST['email']) ? json_decode($_REQUEST['email']) : "";
        $date_info = !empty($_REQUEST['create_info']) ? $_REQUEST['create_info'] : "";

        $response = [];
        if (!empty($emails)){
            include('model/MEmailNew.php');
            $eTicket_model = new MEmailNew();
            $response = $eTicket_model->getTopMenuEmailCount($emails, $date_info);
        }
        echo json_encode($response);
        exit;
    }

    function validateEmail($string){
        $pattern = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
        preg_match_all($pattern, $string, $matches);
        return $matches[0][0];
    }

    function getValidEmail($emails_arr, $return_type="string"){
        $response = '';
        if (!empty($emails_arr)){
            $cc_arr = is_array($emails_arr) ? $emails_arr : explode(',',$emails_arr);
            if (!empty($cc_arr)){
                $str = (strtolower($return_type)=='string') ? '' : [];
                if (strtolower($return_type)=='string'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str .= $email.',';
                        }
                    }
                    $response= rtrim($str,',');
                }elseif (strtolower($return_type)=='array'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str[]= $email;
                        }
                    }
                    $response= $str;
                }
            }
        }
        return $response;
    }

    function get_phone_number_from_email_body($text){
        if (strlen($text) > 0){
            $text = strip_tags($text);
            preg_match_all('/ [0-9]{5}[\-][0-9]{6}|[\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6}|[0-9]{11}|[\+][0-9]{13}|[\+][0-9]{7}[\-][0-9]{6}|[\+][0-9]{3}[\-][0-9]{8} /', $text, $matches);
            $matches = !empty($matches[0]) ? $matches[0][0] : '';
            return $matches;
        }
        return '';
    }

    function setEmailPostData($ticketinfo, $callid, $post){
        $response = [];
        $_STATUS = $post['st'];
        $_RESCHEDULETIME = !empty($post['reschedule_datetime']) ? date_format(date_create_from_format("d/m/Y H:i",$post['reschedule_datetime']), 'Y-m-d H:i') : '';
        $is_forward = !empty($post['forward']) ? "Y" : "N";
        $_number = $this->get_phone_number_from_email_body($post['message']);
        $_TO = !empty($post['to']) ? $post['to'] : $post['forward'];
        $now = time();

        if (empty($_TO)){
            $response['msg'] = "There is no To email address!";
            return $response;
        }elseif (empty($_STATUS)){
            $response['msg'] = "Status missing!";
            return $response;
        } elseif ($_STATUS == 'R' && empty($_RESCHEDULETIME)){
            $response['msg'] = "Reschedule time missing!";
            return $response;
        }elseif (empty($ticketinfo->ticket_id)){
            $response['msg'] = "Invalid Information!";
            return $response;
        }

        $obj = new stdClass();
        $obj->ticket_id=$ticketinfo->ticket_id;
        $obj->mail_sl="";
        $obj->subject=$ticketinfo->subject;
        $obj->from_name=$ticketinfo->fetch_box_name;
        $obj->from_email=$ticketinfo->fetch_box_email;
        $obj->mail_from=$ticketinfo->fetch_box_email;
        $obj->mail_to=$this->getValidEmail($_TO, "string");;
        $obj->mail_cc=$this->getValidEmail($post['cc'], "string");
        $obj->mail_bcc=$this->getValidEmail($post['bcc'], "string");
        $obj->mail_body=base64_encode($post['message']);
        $obj->has_attachment='';
        $obj->is_forward=$is_forward;
        $obj->agent_id=UserAuth::getCurrentUser();
        $obj->status="O";
        $obj->skill_id=$ticketinfo->skill_id;
        $obj->tstamp=$now;
        $obj->phone=$_number;
        $obj->email_status=$post['st'];
        $obj->email_did=$post['dd'];
        $obj->acd_agent=!empty($callid) ? UserAuth::getCurrentUser() : "";
        $obj->acd_status=!empty($callid) ? $post['st'] : "";
        $obj->rs_tr=$_STATUS=="R" ? "Y": "";
        $obj->reschedule_time=$_STATUS=="R" ? $_RESCHEDULETIME: "";
        $obj->rs_tr_create_time=$_STATUS=="R" ? $now : "";

        $obj->fetch_box_email=$ticketinfo->fetch_box_email;
        $obj->fetch_box_name=$ticketinfo->fetch_box_name;
        $obj->forwarded_attachment_hidden=!empty($post['forwarded_attachment_hidden']) ? json_decode($post['forwarded_attachment_hidden']) : "";
        return $response['data']=$obj;
    }

    function actionDetails(){
        include_once('conf.email.php');
        include('model/MCcSettings.php');
        include('model/MEmailNew.php');
        include('model/MAgent.php');
        include('model/MEmailTemplate.php');

        $agent_model = new MAgent();
        $eTicket_model = new MEmailNew();
        $et_model = new MEmailTemplate();
        $ccsettings_model = new MCcSettings();

        unset($_SESSION['msg_srv_ip']);
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['callid'] = !empty($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
        $mail_sl = !empty($_REQUEST['msl']) ? $_REQUEST['msl'] : "";
        $data['info']  = !empty($_REQUEST['info']) ? $_REQUEST['info'] : "";

        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role, $data['callid']);

        if (!$isAllowedToChange) {
            if ($role == 'supervisor') {
                exit;
            } else {
                $isAllowedToRead = $eTicket_model->isAllowedToReadTicket($ticketid, UserAuth::getCurrentUser());
                if (!$isAllowedToRead) exit;
            }
        }

        $data['msg_pattern'] = '';
        $data['current_user'] = '';
        $data['attachment_save_path'] = '';
        $data['ccmails'] = $eTicket_model->getEmailAddressBook();    ///////  NEW EMAIL ADDRESS ///////

        $email_settings = $ccsettings_model->getEmailSettings();
        if (!empty($email_settings)){
            foreach ($email_settings as $itm){
                if ($itm->item == "attachment_save_path"){
                    $data['attachment_save_path'] =  base64_decode($itm->value);
                }
                if ($itm->item == "replace_text_pattern"){
                    $data['msg_pattern'] = $itm->value;
                }
            }
        }

        $data['sublink'] = site_url().$this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=details&tid=".$ticketid."&callid=".$data['callid']."&msl=".$mail_sl);

        if ($this->getRequest()->isPost() && $isAllowedToChange) {
            if ($_POST['message'] != "") {
                $eTicketInfo = $eTicket_model->getETicketById($ticketid, $mail_sl);
                $post_data = $this->setEmailPostData($eTicketInfo, $data['callid'], $_POST);
                $upResult = $eTicket_model->addEmailMsg($post_data, $_FILES['att_file'], $data['attachment_save_path']);
                if ($upResult) {
                    $post_data->mail_sl = $upResult;
                    $eTicket_model->updateLogEmailSession($eTicketInfo, $post_data);
                    $callid = $data['callid'];
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName().'&callid='.$callid);
                    if ($callid){
                        $_msg = array("method"=>"AG_EMAIL_CLOSE", "email_id"=>"$ticketid", "call_id"=>"$callid");
                        $udp_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';
                        $eTicket_model->send_Udp_msg(json_encode($_msg), $udp_port);
                        $this->getTemplate()->display_popup_chat('msg', array('pageTitle'=>'Ticket Details', 'isError'=>false, 'msg'=>'Message successfully sent'));
                    }
                    $this->getTemplate()->display('msg', array('pageTitle'=>'Ticket Details', 'isError'=>false, 'msg'=>'Message successfully sent', 'redirectUri'=>$url));
                }
            }
        }

        $data['eTicket_model'] = $eTicket_model;
        $data['update_privilege'] = $isAllowedToChange;
        $data['eTickets'] = "";
        $data['signature'] = null;
        $data['eTicketEmail'] = "";
        $data['agent_id'] = UserAuth::getCurrentUser();
        $data['agent_name'] = UserAuth::getUserName();
        $data['cc_emails'] = '';
        $data['subject'] = "";
        $data['last_email_body'] = "";
        //$data['status_option_list'] = $eTicket_model->getTicketStatusOptions(false, array("O","R"));
        $data['queue_msg '] = '';
        $data['agent_msg '] = '';
        $data['is_setinterval_active'] = '';
        $data['disposition_name'] = "";
        $data['agent_names'] = "";
        $data['total_email'] = "";
        $data['email_template'] = [];
        $data['attachments'] = [];

        if ($ticketid != '') {

            $data['eTickets'] = $eTicket_model->getETicketById($ticketid, $mail_sl);
            $data['agent_names'] = $this->getAllEmailAgent($data['eTickets']);
            $data['is_setinterval_active'] = $data['eTickets']->current_user==UserAuth::getCurrentUser() ? 'active' : '';
            $time_difference = round((time()-$data['eTickets']->last_pull_time)/60);
            $data['isAllowedToPull'] = empty($data['eTickets']->current_user) || ($time_difference > $this->last_update_difference_time) ? true : false;
            $current_user = !empty($data['agent_names'][$data['eTickets']->current_user]) ? $data['agent_names'][$data['eTickets']->current_user] : "";
            $current_user = empty($current_user) && !empty($data['eTickets']->current_user) ? $agent_model->getAgentNameById($data['eTickets']->current_user) : "";
            $data['current_user'] = is_object($current_user) ? $current_user->nick : $current_user;

            $distribution = $eTicket_model->isDistributed($ticketid);
            $data['acd_status'] = !empty($distribution->acd_status) ? $distribution->acd_status : "";
            $data['dist_status'] = !empty($distribution->status) ? $distribution->status : "";
            if (!empty($distribution->acd_status) && $distribution->acd_status == "Q"){
                $data['queue_msg'] = "This email is already in the queue and the system will automatically distribute it to an agent in the Call Control panel";
            } elseif (!empty($distribution->acd_status) && $distribution->acd_status == "A"){
                $data['agent_msg'] = "This email is already distributed to an Agent and he/she does not pull it yet";
            }

            $data['cc_emails'] = $this->getValidEmail($data['eTickets']->mail_cc);
            $data['bcc_emails'] = $this->getValidEmail($data['eTickets']->mail_bcc);
            $data['total_email'] = $eTicket_model->getTotalEmailById($ticketid);
            $data['email_template'] = $et_model->getNewEmailTemplate("");
            $data['changable_status'] = $eTicket_model->getTicketStatusOptions(false, array("O","R"));
            $attachments = !empty($data['eTickets']->has_attachment) && $data['eTickets']->has_attachment=="Y" ?  $eTicket_model->getAttachments($ticketid, $data['eTickets']->mail_sl) : [];
            $data['attachments'] = !empty($attachments) ? $this->actionGetAttachment($attachments, $data['eTickets']->tstamp, $data['attachment_save_path']) : [];

            if (!empty($data['eTickets'])) {
                $data['disposition'] = $eTicket_model->getDispositionById($data['eTickets']->email_did, $data['eTickets']->skill_id);
                $disp_path = !empty($data['disposition']->disposition_id) ? $eTicket_model->getDispositionPath($data['disposition']->disposition_id) : "";
                $data['disposition_name'] = !empty($disp_path) ?  $disp_path .' -> ' : "";
                $data['disposition_name'] .= !empty($data['disposition']->title) ? $data['disposition']->title : "";
                $data['signature'] = $eTicket_model->getEmailSignature($data['eTickets']->skill_id);
                $previos_email_datetime = !empty($data['eTickets']->tstamp) ? 'On '.date('D, M j, Y', $data['eTickets']->tstamp).' at '.date('h:m A', $data['eTickets']->tstamp). htmlspecialchars($data['eTickets']->mail_from) . " wrote:</b>" : '';
                $data['last_email_body'] = !empty($data['eTickets']->mail_body) ? $previos_email_datetime.'<br/>'.nl2br(base64_decode($data['eTickets']->mail_body)) : '';
                $eTicket_model->addETicketActivity($ticketid, $mail_sl, UserAuth::getCurrentUser(), 'V');
            }

            if (empty($data['eTickets']->first_seen_by)) {
                if ($eTicket_model->updateFirstOpenTimeById($ticketid, $mail_sl, UserAuth::getCurrentUser())){
                    $eTicket_model->updateFirstOpenTimeForLogEmailSession($data['eTickets'], UserAuth::getCurrentUser());
                }
            }
        }
        $data['disposition_ids'] = !empty($data['eTickets']->email_did)?$eTicket_model->getDispositionPathArray($data['eTickets']->email_did):'';///////DID change////
        $data['side_menu_index'] = 'ticketmng';
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Email Details';
        $data['pageTitle2'] = base64_decode($data['eTickets']->subject);//'Email Details';
        $data['smi_selection'] = 'email_init';
        $data['read_image_file_path'] = $this->getTemplate()->read_email_body_image;

        if (!empty($data['callid'])){
            $this->getTemplate()->display_popup_chat('email_new_details', $data); //////hiding side menu bar
        } else {
            $this->getTemplate()->display('email_new_details', $data); //////hiding side menu bar
        }
    }

    function actionGetAttachment($attachments, $time, $attachment_save_path){
        $data = '';
        if (!empty($attachments) && !empty($time)){
            $yy = date("y", $time);
            $mm = date("m", $time);
            $dd = date("d",$time);
            $forwarded_attachment_path = '';
            $list = &$attachments;
            foreach ($list as &$key){
                $key->tstamp = $time;
                $key->path = $attachment_save_path;
                $key->file_path =  $yy . $mm . '/' . $dd . '/' . $key->ticket_id . '/' . $key->mail_sl . '/'.$key->file_name;
                $forwarded_attachment_path =  $yy . $mm . '/' . $dd . '/' . $key->ticket_id . '/' . $key->mail_sl . '/';
            }
            $data['forwarded_attachment_path']  = $forwarded_attachment_path;
            $data['data']  = $list;
        }
        return $data;
    }

    function getAllEmailAgent($email_data){
        $temp = [];
        $response = [];
        if (!empty($email_data)) {
            foreach ($email_data as $key){
                if (!empty($key->agent_id) && !in_array($key->agent_id, $temp)){
                    $temp[] =$key->agent_id;
                }
                if (!empty($key->assigned_to) && !in_array($key->assigned_to, $temp)){
                    $temp[] =$key->assigned_to;
                }
                if (!empty($key->first_seen_by) && !in_array($key->first_seen_by, $temp)){
                    $temp[] =$key->first_seen_by;
                }
                if (!empty($key->current_user) && !in_array($key->current_user, $temp)){
                    $temp[] =$key->current_user;
                }
            }
        }
        if (!empty($temp)){
            $agent_model = new MAgent();
            $result = $agent_model->getAllAgents($temp);
            if (!empty($result)){
                foreach ($result as $key){
                    $response[$key->agent_id] = $key->name;
                }
            }
        }
        return $response;
    }

    function actionEmailModuleUdpMsg(){
        include_once('conf.email.php');
        include('model/MEmailNew.php');
        $mainobj = new MEmailNew();
        $_SESSION['msg_srv_ip'] = !empty($_SESSION['msg_srv_ip']) ? $_SESSION['msg_srv_ip'] : $mainobj->getDBIPSettings();
        $msg_srv_ip = $_SESSION['msg_srv_ip'];
        $request_data = !empty($_REQUEST['data']) ? $_REQUEST['data'] : "";
        //GPrint($request_data);
        $response = '';
        $agentid = !empty($request_data['agent_id']) ? $request_data['agent_id'] : "";
        $msg_res['call_id'] = !empty($request_data['call_id']) ? $request_data['call_id'] : "";
        $msg_res['email_id'] = !empty($request_data['email_id']) ? $request_data['email_id'] : "";
        $msg_res['method'] = !empty($request_data['method']) ? $request_data['method'] : "";
        $mail_sl = !empty($request_data['mail_sl']) ? $request_data['mail_sl'] : "";
        $event = !empty($request_data['event']) ? $request_data['event'] : "";
        $msg = json_encode($msg_res);
        $len = strlen($msg);

        if (!empty($msg_res['call_id'])) {
            $msg_srv_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
            socket_close($sock);
            if ($event == 'skip' || $event == 'close'){
                $mainobj->updateSkipCloseOfEticket($agentid, $msg_res['email_id'], $mail_sl, $event);
                $mainobj->addETicketActivity($msg_res['email_id'], $mail_sl, $agentid, 'I', $msg_res['call_id']);
            }
        }
        $response = $event=='pull' ? $mainobj->updatePullInfo($agentid, $msg_res['email_id'], $mail_sl) : "";
        echo json_encode($response);
        exit();
    }

    function actionEmptyCurrentUser() {
        $response = '';
        $request = !empty($_REQUEST['data']) ? $_REQUEST['data'] : "";
        $email_id = !empty($request['email_id']) ? $request['email_id'] : "";
        $mail_sl = !empty($request['mail_sl']) ? $request['mail_sl'] : "";
        if (!empty($email_id)){
            include('model/MEmailNew.php');
            $mainobj = new MEmailNew();
            $response = $mainobj->emptyCurrentUser($email_id, $mail_sl);
        }
        echo json_encode($response);
        exit();
    }

    function actionManuallyWorkOnEmail(){
        $request = !empty($_REQUEST['data']) ? $_REQUEST['data'] : "";
        $tid = !empty($request['email_id']) ? $request['email_id'] : "";
        $mail_sl = !empty($request['mail_sl']) ? $request['mail_sl'] : "";
        $obj = new stdClass();
        $obj->response = false;
        $obj->msg = '';
        if (!empty($tid) && !empty($mail_sl)){
            include('model/MEmailNew.php');
            $eTicket_model = new MEmailNew();
            $result = $eTicket_model->isDistributed($tid);
            if (!empty($result) && $result->acd_status == 'N') {
                $eTicket_model->deleteEmailDistribution($tid);
            }
            $ticket_info = $eTicket_model->getEmailMessageDetails($tid, $mail_sl);
            $current_user = $ticket_info->current_user;
            $count_mins = (time() - $ticket_info->last_pull_time)/60;
            if (empty($current_user) || ($count_mins > $this->last_update_difference_time)){
                $obj->response = true;
                $obj->msg = 'success';
                $eTicket_model->addETicketActivity($tid, $mail_sl, UserAuth::getCurrentUser(), 'P','');
            }
        }
        echo json_encode($obj);
        exit();
    }

    function actionIsNewEmailArrive(){
        $request = !empty($_REQUEST['data']) ? $_REQUEST['data'] : "";
        $ticket_id = !empty($request['email_id']) ? $request['email_id'] : '';
        include('model/MEmailNew.php');
        $eTicket_model = new MEmailNew();
        $new_email_sl = !empty($ticket_id) ? $eTicket_model->isNewEmailArrive($ticket_id) : 0;
        $response = $new_email_sl;

        echo json_encode($response);
        exit();
    }

    function actionSkill() {
        include('model/MEmailNew.php');
        $eTicket_model = new MEmailNew();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $mail_sl = isset($_REQUEST['msl']) ? trim($_REQUEST['msl']) : '';
        $data['skills'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid, $mail_sl);

            if (!empty($eTicketInfo)) {
                $data['pageTitle'] = 'Update skill of ticket :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {

                    $skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
                    if ( $eTicketInfo->skill_id !=  $skill) {
                        $isAllowed = true;
                        if (empty($skill)) {
                            $isAllowed = false;
                        }

                        if ($isAllowed) {
                            $is_update = $eTicket_model->setEmailSkill($ticketid, $mail_sl, $skill, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionTransfer($eTicketInfo, UserAuth::getCurrentUser(), $skill);
                                $eTicket_model->setDistributionSkill($ticketid, $skill,'', 'S',$eTicketInfo);
                                $data['message'] = 'Skill updated successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to update skill!!';
                            }
                        }else {
                            $errMsg = 'Provide valid skill!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                $data['skills'] = $eTicket_model->getEmailSkill();
            } else {
                $fatalErr = 'Invalid ticket!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Update Skill of ticket';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['msl'] = $mail_sl;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update skill of ticket';
        $this->getTemplate()->display_popup('email_set_skill', $data);
    }

    function actionStatus(){
        include('model/MEmailNew.php');
        $eTicket_model = new MEmailNew();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['callid'] = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $data['msl'] = isset($_REQUEST['msl']) ? trim($_REQUEST['msl']) : '';
        //$data['session_mail_count'] = isset($_REQUEST['mc']) ? trim($_REQUEST['mc']) : 0;

        $data['statuses'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid, $data['msl']);
            if (!empty($eTicketInfo)) {
                $data['pageTitle'] = 'Update status of ticket :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {
                    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
                    $reschedule_date_time = isset($_POST['sdate']) ? trim($_POST['sdate']) : '';
                    $reschedule_date_time = !empty($reschedule_date_time) ? date_format(date_create_from_format("d/m/Y H:i",$reschedule_date_time), 'Y-m-d H:i') : '';
                    if ( ($eTicketInfo->status != $status) || ($status=='R' && date("Y-m-d H:i", $eTicketInfo->reschedule_time) != $reschedule_date_time) ) {
                        $isAllowed = true;
                        if (empty($status)) {
                            $isAllowed = false;
                        }
                        if ($isAllowed) {
                            $is_update = $eTicket_model->updateTicketStatus($ticketid, $data['msl'], $status, UserAuth::getCurrentUser(), $eTicketInfo->from_email, $eTicketInfo, $reschedule_date_time, $data['callid'], $eTicketInfo);
                            if ($is_update) {

                                $obj = new stdClass();
                                $obj->email_status = $status;
                                $obj->tstamp = time();
                                $obj->rs_tr_create_time = time();
                                $obj->agent_id = UserAuth::getCurrentUser();
                                $obj->reschedule_time = $reschedule_date_time;
                                $obj->rs_tr = $status=="R" ? "Y" : "N";

                                $eTicket_model->updateEmailSession($eTicketInfo, $obj);
                                $data['message'] = 'Updated successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to update status!!';
                            }
                        } else {
                            $errMsg = 'Provide valid status!!';
                        }
                    } else {
                        $errMsg = 'No change found for status!!';
                    }
                }
                $data['statuses'] = $eTicket_model->getTicketStatusOptions(false, array("O","S"));
            } else {
                $fatalErr = 'Invalid ticket!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Update status of ticket';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }
        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update status of ticket';
        $this->getTemplate()->display_popup('email_new_set_status', $data);
    }

    function actionAssign(){
        include('model/MEmailNew.php');
        $eTicket_model = new MEmailNew();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['msl'] = isset($_REQUEST['msl']) ? trim($_REQUEST['msl']) : '';
        $data['agents'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {
                $data['pageTitle'] = 'Assign agent to ticket :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {
                    $aid = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';
                    if ($eTicketInfo->assigned_to != $aid) {
                        $isAllowed = true;
                        if (!empty($aid)) {
                            $result = $eTicket_model->getSkillAgents($eTicketInfo->skill_id, $aid);
                            if (empty($result)) $isAllowed = false;
                        }

                        if ($isAllowed) {
                            $acd_status = $eTicket_model->isDistributed($ticketid);
                            $is_update = $eTicket_model->assignAgent($ticketid, $data['msl'], $aid, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionTransfer($eTicketInfo, UserAuth::getCurrentUser(), '', $aid);
                                if (!empty($acd_status)) $eTicket_model->deleteEmailDistribution($ticketid);
                                $data['message'] = 'Agent assigned successfully.';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to assign agent!!';
                            }
                        } else {
                            $errMsg = 'Privilege error!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                $data['agents'] = $eTicket_model->getSkillAgents($eTicketInfo->skill_id);
            } else {
                $fatalErr = 'Invalid ticket!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }

        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Assign agent to ticket';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Assign agent to ticket';
        $this->getTemplate()->display_popup('email_assign_agent', $data);
    }

    function actionDisposition(){
        include('model/MEmailNew.php');
        $eTicket_model = new MEmailNew();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['msl'] = isset($_REQUEST['msl']) ? trim($_REQUEST['msl']) : '';
        $data['dispositions'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {
                $data['pageTitle'] = 'Set disposition to ticket :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {
                    $did = '';
                    for ($i=0;$i<=10; $i++) {
                        $did1 = isset($_REQUEST['disposition_id'.$i]) ? trim($_REQUEST['disposition_id'.$i]) : '';
                        if (!empty($did1)) $did = $did1;
                        else break;
                    }
                    if ($eTicketInfo->email_did != $did) {
                        $isAllowed = true;
                        if (!empty($did)) {
                            $result = $eTicket_model->getDispositionById($did, $eTicketInfo->skill_id);
                            if (empty($result)) $isAllowed = false;
                        }
                        if ($isAllowed) {
                            $is_update = $eTicket_model->setEmailDisposition($ticketid, $data['msl'], $did, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionDisposition($ticketid, $data['msl'], $did, UserAuth::getCurrentUser());
                                $data['message'] = 'Disposition updated successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to update disposition!!';
                            }
                        } else {
                            $errMsg = 'Privilege error!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }
                $data['dispositions0'] = $eTicket_model->getDispositionChildrenOptions($eTicketInfo->skill_id, '');
            } else {
                $fatalErr = 'Invalid ticket!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Set disposition to ticket';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }
        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;

        $data['ticket_info'] = $eTicketInfo;
        $data['disposition_ids'] = $eTicket_model->getDispositionPathArray($eTicketInfo->email_did);
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Set disposition to ticket';
        $this->getTemplate()->display_popup('email_set_disposition', $data);
    }

    function actionDispositionchildren(){
        $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $options = array();
        if (!empty($did)) {
            include('model/MEmailNew.php');
            $eTicket_model = new MEmailNew();
            $options = $eTicket_model->getDispositionChildrenOptions('', $did);
        }
        echo json_encode($options);
    }

    function actionGetEmailTemplate(){
        $id = !empty($_REQUEST['data']) ? $_REQUEST['data'] :"";
        $response = "";
        if (!empty($id)){
            include('model/MEmailTemplate.php');
            $mainobj = new MEmailTemplate();
            $response = $mainobj->getNewEmailTemplateById($id);
        }
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode($response);exit;
        }
        return $response;
    }

    function actionAttachment(){
        include('model/MEmailNew.php');
        include('model/MCcSettings.php');
        $ccsettings_model = new MCcSettings();
        $eTicket_model = new MEmailNew();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $sl = isset($_REQUEST['sl']) ? trim($_REQUEST['sl']) : '';
        $p = isset($_REQUEST['p']) ? trim($_REQUEST['p']) : '';
        $tstamp = isset($_REQUEST['tsm']) ? trim($_REQUEST['tsm']) : '';
        $file_name = isset($_REQUEST['fname']) ? urldecode($_REQUEST['fname']) : "";

        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if (!$isAllowedToChange) {
            if ($role == 'supervisor') {
                exit;
            } else {
                $isAllowedToRead = $eTicket_model->isAllowedToReadTicket($ticketid, UserAuth::getCurrentUser());
                if (!$isAllowedToRead) exit;
            }
        }

        //$attachment = $eTicket_model->getAttachmentDetails($ticketid, $sl, $p);
        if (!empty($tstamp))  {
            $yy = date("y", $tstamp);
            $mm = date("m", $tstamp);
            $dd = date("d", $tstamp);
            $email_settings = $ccsettings_model->getEmailSettings();
            $attachment_save_path = '';
            if (!empty($email_settings)){
                foreach ($email_settings as $itm){
                    if ($itm->item == "attachment_save_path"){
                        $attachment_save_path =  base64_decode($itm->value);
                    }
                }
            }
            $file = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $ticketid . '/' . $sl . '/' . $file_name;
            if (file_exists($file)) {
                if (FALSE!== ($handler = fopen($file, 'r'))) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'. str_replace('"', '\\"', basename($file)) . '"');
                    header('Content-Transfer-Encoding: chunked'); //changed to chunked
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    while (!feof($handler)) {
                        echo fread($handler, 4096);
                        ob_flush();  // flush output
                        flush();
                    }
                }
                exit;
            }
        }
        exit;
    }

}