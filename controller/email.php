<?php

class Email extends Controller
{
    function __construct() {
        parent::__construct();

        $licenseInfo = UserAuth::getLicenseSettings();
        if ($licenseInfo->email_module == 'N') {
            header("Location: ./index.php");
            exit;
        }
    }
    //public $last_update_difference_time = 2;

    function getInboxTopMenus($inbox_list, $agent_skill_list) {
        $button_colors = array("lgrn btn-rds","ard btn-rds","yel btn-rds","prpl btn-rds");
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
            foreach ($inbox_list as $item) {
                $info['name'] = $item->name;
                $info['username'] = $item->username;
                $info['title'] = $item->title;

                $temp['href'] = "task=email&info=".base64_encode(serialize($info));
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

                $i = $i > $numbers_of_color-1 ? 0 : $i;
            }
        }
        $response['inbox_menus'] = $inbox_menus;
        $response['menu_ids'] = $menu_ids;
        $response['menu_emails'] = $menu_emails;
        return $response;
    }

    function actionGetTopMenuCount() {
        $emails = !empty($_REQUEST['email']) ? json_decode($_REQUEST['email']) : "";
        $date_info = !empty($_REQUEST['last_update_info']) ? $_REQUEST['last_update_info'] : "";

        $response = [];
        if (!empty($emails)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $response = $eTicket_model->getTopMenuEmailCount($emails, $date_info);
        }
        //dd($response);
        echo json_encode($response);
        exit;
    }

    /*
     * Sender priority email button
     * shows only for allowed skills
     */
    function priorityButton($priority_skills, $agent_skill_list){
        $skill_list = [];
        if (!empty($agent_skill_list)){
            foreach ($agent_skill_list as $key){
                $skill_list[] = $key->skill_id;
            }
        }

        $skills = $skill_list;
        $utype = UserAuth::hasRole('admin');
        if (!$utype){
            $skills = array_intersect($skill_list, $priority_skills);
        }
        $info['name'] = "Priority";
        $info['username'] = "is_priority";
        $info['title'] = "Priority";

        $temp['href'] = "task=email&info=".base64_encode(serialize($info));
        $temp['img'] = "fa fa-envelope";
        $temp['label'] = "Priority";
        $temp['color'] = "priority btn-rds";
        $temp['property'] = array("id" => "is_priority", "data-email"=>"is_priority");

        $_SESSION['priorityEmailSkill'] = $skills;
        //GPrint($skills);die;
        if (!empty($skills)){
            return $temp;
        }
        return null;
    }

    /*
     * priority emails count
     */
    function actionGetPriorityTopmenuCount() {
        $skill = !empty($_REQUEST['skill_list']) ? $_REQUEST['skill_list'] : "";
        $date_info = !empty($_REQUEST['last_update_info']) ? $_REQUEST['last_update_info'] : "";

        $response = 0;
        if (!empty($skill)) {
            $skill_list = [];
            foreach ($skill as $key => $value){
                if (!empty($key) && strlen($key) == 2) {
                    $skill_list[] = $key;
                }
            }

            include_once('model/MEmail.php');
            $eTicket_model = new MEmail();
            $response = $eTicket_model->getPriorityTopMenuEmailCount($skill_list, $date_info);
        }
        echo json_encode($response);
        exit();
    }

    function init() {
        include('model/MEmail.php');
        include('model/MAgent.php');
        include('model/MEmailFetchInbox.php');
        include('model/MCcSettings.php');
        $eTicket_model = new MEmail();
        $agent_model = new MAgent();
        $inbox_model = new MEmailFetchInbox();
        $cc_settings = new MCcSettings();
        $cc_settings->module_type = MOD_EMAIL;

        /*
         * Only for priority email
         */
        if (empty($_SESSION['priorityEmailSkill'])) {
            $cc_settings_data = $cc_settings->getAllSettings();
            $priority_skills = [];
            if (!empty($cc_settings_data)) {
                foreach ($cc_settings_data as $index){
                    if ($index->item = "viewable_priority_skill" && !empty($index->value))
                        $priority_skills = explode(",", $index->value);
                }
            } else {
                $_SESSION['priorityEmailSkill'] = "No Skills";
            }
        }

        $inbox_list = $inbox_model->getEmailFetchInboxes('','','A','Y','','','name,username,show_email_list,title,viewable_skills,inbox_row_color');
        $inbox_email_list = [];
        $email_row_color = [];
        if (!empty($inbox_list)){
            foreach ($inbox_list as $itm){
                $inbox_email_list[] = $itm->username;
                $email_row_color[$itm->username] = $itm->inbox_row_color;
            }
        }
        $inbox_email_list = base64_encode(serialize($inbox_email_list));
        //$agent_options = $agent_model->get_as_key_value_for_ticket();
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

        $data['source_name'] = array("*"=>"Select", "C"=>"CRM", "E"=>"Email", "M"=>"Manual");

        $data['status'] = $status;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';

        $selectOpt = array('*'=>'Select');
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions(true));
        $data['dataUrl'] = $this->url('task=get-email-data&act=emailinit'.$urlParam);
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
            $_COOKIE['crt_sdate'] = !empty($_COOKIE['crt_sdate']) ? $_COOKIE['crt_sdate'] : "";
            $_COOKIE['crt_edate'] = !empty($_COOKIE['crt_edate']) ? $_COOKIE['crt_edate'] : "";
            $_COOKIE['customer_id'] = !empty($_COOKIE['customer_id']) ? $_COOKIE['customer_id'] : "";
            $_COOKIE['created_for'] = !empty($_COOKIE['created_for']) ? $_COOKIE['created_for'] : "";
            $_COOKIE['did'] = !empty($_COOKIE['did']) ? $_COOKIE['did'] : "";
            $_COOKIE['phone'] = !empty($_COOKIE['phone']) ? $_COOKIE['phone'] : "";
            $_COOKIE['lut_sdate'] = !empty($_COOKIE['lut_sdate']) ? $_COOKIE['lut_sdate'] : date("Y-m-d H:i", strtotime("-2 month"));
            $_COOKIE['lut_edate'] = !empty($_COOKIE['lut_edate']) ? $_COOKIE['lut_edate'] : date("Y-m-d H:i");

            $_COOKIE['ticket_id'] = !empty($_COOKIE['ticket_id']) ? $_COOKIE['ticket_id'] : "";
            $_COOKIE['last_name'] = !empty($_COOKIE['last_name']) ? $_COOKIE['last_name'] : "";
            $_COOKIE['subject'] = !empty($_COOKIE['subject']) ? $_COOKIE['subject'] : "";
            $_COOKIE['status'] = !empty($_COOKIE['status']) ? $_COOKIE['status'] : "O";
            $_COOKIE['skill_name'] = !empty($_COOKIE['skill_name']) ? $_COOKIE['skill_name'] : "";
            $_COOKIE['mail_to'] = !empty($_COOKIE['mail_to']) ? $_COOKIE['mail_to'] : "";
        } else {
            $_COOKIE['crt_sdate'] = "";
            $_COOKIE['crt_edate'] = "";
            $_COOKIE['customer_id'] = "";
            $_COOKIE['created_for'] = "";
            $_COOKIE['did'] = "";
            $_COOKIE['phone'] = "";
            $_COOKIE['lut_sdate'] = date("Y-m-d ", strtotime("-2 month"))."00:00";
            $_COOKIE['lut_edate'] = date("Y-m-d H:i");

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
        $data['email_row_color'] = $email_row_color;

        if (count($inbox_menus) > 0 && empty($fetch_inbox_info)) {
            $data['topMenuItems'] = $inbox_menus;
            $data['show_inbox_color'] = "Y";
        } else if (!empty($fetch_inbox_info)) {
            $data['topMenuItems'] = array(array('href'=>'task=email', 'img'=>'fa fa-envelope', 'label'=>'All Email'));
        }

        /*
         * Priority Email Menu
         */
        if (empty($fetch_inbox_info) && !empty($_SESSION['priorityEmailSkill']) && is_array($_SESSION['priorityEmailSkill']) && $priority_button = $this->priorityButton($_SESSION['priorityEmailSkill'], $skill_list)) {
            $data['topMenuItems'][] = $priority_button;
        } elseif (empty($fetch_inbox_info) && empty($_SESSION['priorityEmailSkill'])  && !empty($priority_skills) && $priority_button = $this->priorityButton($priority_skills, $skill_list)) {
            $data['topMenuItems'][] = $priority_button;
        }

        $data['side_menu_index'] = 'ticketmng';
        if (!empty($data['callid'])){
            $this->getTemplate()->display_email_popup('email', $data);
        } else {
            $this->getTemplate()->display('email', $data);
        }
    }

    function actionCreate() {
        include('model/MSkill.php');
        include('model/MCcSettings.php');
        $skill_model = new MSkill();
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $err = '';
        $errType = 1;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ?$this->getValidEmail(trim($_POST['email'])) : '';
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $mail_body = isset($_POST['mail_body']) ? trim($_POST['mail_body']) : '';
        $skill_id = isset($_POST['skill_id']) ? trim($_POST['skill_id']) : '';
        $skill_email = isset($_POST['skill_email']) ? trim($_POST['skill_email']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $created_for = isset($_POST['created_for']) ? trim($_POST['created_for']) : '';
        $data['dd'] = '';
        $cc = isset($_POST['cc']) ? $this->getValidEmail($_POST['cc'],'array') : null;
        $bcc = isset($_POST['bcc']) ? $this->getValidEmail($_POST['bcc'],'array') : null;
        $data['ccmails'] = $eTicket_model->getEmailAddressBook();
        $category_id = "";
        $account_id = "";

        $type = isset($_REQUEST['type'])?$_REQUEST['type']:"";
        if (!empty($type) && $type == "ticket"){
            $data['ticket_category'] = array();
            $ticket_category = $eTicket_model->getTicketCategory('','A');
            if (!empty($ticket_category)){
                foreach ($ticket_category as $item){
                    $data['ticket_category'][$item->category_id] = $item->title;
                }
            }
            $category_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
            $account_id = isset($_POST['account_id']) ? trim($_POST['account_id']) : '';
        }
        $did = '';
        for ($i=0;$i<=10; $i++) {
            $did1 = isset($_POST['disposition_id'.$i]) ? trim($_POST['disposition_id'.$i]) : '';
            if (!empty($did1)) $did = $did1;
            else break;
        }
        $emails = empty($skill_id) ? array() : $skill_model->getEmails($skill_id, 'array');
        //$data['ccmails'] = $eTicket_model->getAllowedEmails();
        $data['changable_status'] = $eTicket_model->getChangableTicketStatus('O');

        if ( isset($_POST['skill_email']) || ( $type == "ticket" && !empty($category_id) )) {
            if ($type == "ticket"){
                $err = $this->validateCreateTicket($skill_id, $did, $mail_body, $name, $account_id, $category_id, $status, $created_for);
            }else {
                $err = $this->validateCreateEmail($name, $email, $title, $mail_body, $skill_id, $skill_email, $status);
            }
            if (empty($err)) {
                $is_success = false;
                $data['dd'] = $did;
                $skillinfo = $skill_model->getSkillById($skill_id);
                $skill_name = empty($skillinfo) ? '' : $skillinfo->skill_name;
                if (empty($type) && $eTicket_model->createNewEmail(UserAuth::getCurrentUser(), $skill_name, $skill_email, $name, $email, $cc, $bcc, $did, $title, $mail_body, $status, $skill_id, $data['ccmails'], $_FILES['att_file'])) {
                    $err = 'New Email created successfully !!';
                    $is_success = true;
                } elseif ((!empty($type) && $type == "ticket") ) {
                    include_once('model/MCrmIn.php');
                    $crm_model = new MCrmIn();
                    if ($crm_model->openCrmTicket($skill_id, $created_for, $did, $mail_body, $name, $account_id, $category_id, $status,'M')){
                        $crm_model->addToAuditLog('Manual Ticket Create', 'A', "ticket_id", "");
                        //$this->addToAuditLog("ticket category", 'A', 'category_id' , "category_id=".$id);
                        $err = 'New ticket created successfully !!';
                        $is_success = true;
                    }
                } else {
                    $err = 'Failed to create new Email !!';
                }

                if ($is_success) {
                    $errType = 0;
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    if (!empty($type) && $type == "ticket"){
                        $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    }
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
            }
        }

        if (!empty($type) && $type == "ticket"){
            $data['skills'] = $skill_model->getSkills('', '', 0, 100);
        } else {
            if (UserAuth::hasRole('admin')) {
                $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
            } else {
                $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
            }
        }

        include_once('conf.email.php');
        $cc_settings = new MCcSettings();
        $email_settings = $cc_settings->getEmailSettings();

        if (!empty($email_settings)){
            foreach ($email_settings as $itm){
                if ($itm->item == "replace_text_pattern") $data['replace_text_pattern'] = $itm->value;
                if ($itm->item == "attachment_save_path") $data['attachment_save_path'] = $itm->value;
            }
        }
        $data['replace_text_pattern'] = !empty($data['replace_text_pattern']) ? $data['replace_text_pattern'] : $replace_text_pattern;
        $data['attachment_save_path'] = !empty($data['attachment_save_path']) ? $data['attachment_save_path'] : '';
        $data['disposition_ids'] = $eTicket_model->getDispositionPathArray('');
        $data['email_model'] = $eTicket_model;
        $data['name'] = $name;
        $data['email'] = $email;
        $data['title'] = $title;
        $data['mail_body'] = $mail_body;
        $data['skill_id'] = $skill_id;
        $data['skill_email'] = $skill_email;
        $data['status'] = $status;
        $data['errMsg'] = $err;
        $data['errType'] = $errType;
        $data['emails'] = $emails;
        $data['category_id'] = $category_id;
        $data['account_id'] = $account_id;
        $data['agent_name'] = UserAuth::getUserName();
        $data['side_menu_index'] = 'email';
        if (!empty($type)){
            $data['side_menu_index'] = 'ticketmng';
            $data['smi_selection'] = 'ticket_create';
        }
        $data['type'] = $type;
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'New Email';
//		$this->getTemplate()->display('email_create', $data);
        $this->getTemplate()->display('email_create_new', $data);
    }

    function validateCreateTicket($skill_id, $disposition, $subject, $last_name, $account_id, $category_id,$status, $created_for){
        if (empty($skill_id)) return 'Skill is required';
        if (empty($disposition)) return 'Disposition is required';
        if (empty($subject)) return 'Text is required';
        if (!empty($subject) && strlen($subject) > 256) return 'Text not more than 256 char';
        if (empty($last_name)) return 'Ticket Owner\'s Name is required';
        if (empty($account_id)) return 'Acoount ID is required';
        if (empty($category_id)) return 'Category is required';
        if (empty($status)) return 'Status is required';
        if (empty($created_for)) return 'Mobile No. is required';
        return '';
    }

    function validateCreateEmail($name, $email, $title, $mail_body, $skill_id, $skill_email, $status) {
        if (empty($email)) return 'Owner\'s Email is required';
        if (empty($title)) return 'Ticket Title is required';
        if (empty($mail_body)) return 'Text is required';
        if (empty($skill_id)) return 'Skill is required';
        if (empty($skill_email)) return 'Skill Email is required';
        if (empty($status)) return 'Status is required';

        if (!empty($email)){
            $email = explode(",",$email);
            if (!filter_var($email[0], FILTER_VALIDATE_EMAIL)) return 'Provide valid email address';
        } else {
            return 'Email address is required';
        }
        return '';
    }

    function actionSkills() {
        /* include('model/MSkill.php');
        $skill_model = new MSkill();
        include('model/MEmail.php');
        $eTicket_model = new MEmail();

        if (UserAuth::hasRole('admin')) {
            $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
        } else {
            $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
        }

        $data['side_menu_index'] = 'email';
        $data['request'] = $this->getRequest();
        $data['email_model'] = $eTicket_model;
        $data['pageTitle'] = 'Email Skill List';
        $this->getTemplate()->display('skills_email', $data); */


        $data['side_menu_index'] = 'email';
        $data['pageTitle'] = 'Email Skill List';
        $data['dataUrl'] = $this->url('task=get-email-data&act=emailskills');
        $this->getTemplate()->display('skills_email', $data);
    }

    function actionSkillemails()
    {
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $emails = empty($sid) ? array() : $skill_model->getEmails($sid, 'array');
        echo json_encode($emails);
    }

    function actionSignatureText()
    {
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $sig_text = '';
        include('model/MEmail.php');
        $email_model = new MEmail();
        if (!empty($sid)) {
            $signature = $email_model->getEmailSignature($sid);
            //$agent_name = UserAuth::getUserName()."<br />";
            //if (!empty($signature) && $signature->status == 'Y') $sig_text = '<br /><br /><br />' .$agent_name. nl2br(base64_decode($signature->signature_text));
            if (!empty($signature) && $signature->status == 'Y') $sig_text = '<br /><br /><br />' . nl2br(base64_decode($signature->signature_text));
        }
        echo $sig_text;
        exit;
    }

    function actionSkillDispositions()
    {
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $dispositions = array();
        if (empty($type)) {
            include('model/MEmail.php');
            $email_model = new MEmail();
            if (!empty($sid)) {
                $dispositions = $email_model->getDispositionChildrenOptions($sid, '');
            }
        } else {
            include('model/MSkillCrmTemplate.php');
            $template_model = new MSkillCrmTemplate();
            $result = $template_model->getDispositions($sid);
            if (!empty($result)){
                foreach ($result as $key)$dispositions[$key->disposition_id] = $key->title;
            }
        }

        echo json_encode($dispositions);
    }

    function actionDashboard(){
        include('model/MEmailReport.php');
        $email_model = new MEmailReport();
        $data['source'] = "";

        $data['from_date'] =  !empty($_POST['from_date']) ? $_POST['from_date'] : date(REPORT_DATE_FORMAT, strtotime( date( "d-m-Y", strtotime( date("d-m-Y") ) ) . "-1 month" ) );
        $data['to_date'] = !empty($_POST['to_date']) ? $_POST['to_date'] : date(REPORT_DATE_FORMAT);
        $data['sl'] = 0;
        $data['awt']="00:00:00";
        $data['aht']="00:00:00";
        $data['inbox_emails_count'] = [];
        $data['inbox_emails_color'] = [];
        $data['outbound_emails_count'] = [];
        $data['outbound_emails_color'] = [];
        $data['recentjob'] = null;
        $data['alljob'] = null;
        $data['myjob'] = null;

        $request = $this->getRequest();
        if ($this->getRequest()->isPost() && !empty($_POST['source'])){
//            $data['source'] = $_POST['source'];
//            $data['myjob'] = $email_model->getMyJobSummary(UserAuth::getCurrentUser(), $data['source'], $data['from_date'], $data['to_date']);
//            if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
//                $data['recentjob'] = $email_model->getRecentJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'), $data['source']);
//                $data['alljob'] = $email_model->getJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'),$data['source']);
//            } else {
//                $data['recentjob'] = null;
//                $data['alljob'] = null;
//            }
        }else {
            $data['myjob'] = $email_model->getMyJobSummary(UserAuth::getCurrentUser(), $data['source'], $data['from_date'], $data['to_date']);
            if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
                $data['recentjob'] = $email_model->getRecentJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
                $data['alljob'] = $email_model->getJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
                $sl_data = $email_model->get_today_SL_AWT_AHT();//// 18/7/19
                $data['sl'] = !empty($sl_data->total_closed) && !empty($sl_data->total_in_kpi) ? round(($sl_data->total_in_kpi/$sl_data->total_closed)*100,'2') : 0;
                $data['awt'] = !empty($sl_data->WaitDuration) && !empty($sl_data->total_closed) ? get_timestamp_to_hour_format($sl_data->WaitDuration/$sl_data->total_closed) : "00:00:00";
                $data['aht'] = !empty($sl_data->total_closed) && !empty($sl_data->total_open_duration) ? get_timestamp_to_hour_format($sl_data->total_open_duration/$sl_data->total_closed) : "00:00:00";
                $data['inbox_emails_count'] = $email_model->getTodayInboundEmailsCount();

                if (!empty($data['inbox_emails_count'])){
                    for ($i=1; $i<=count($data['inbox_emails_count']); $i++){
                        $data['inbox_emails_color'][] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
                    }
                }

                $data['outbound_emails_count'] = $email_model->getTodayInboxOutboundEmailsCount();
                if (!empty($data['outbound_emails_count'])){
                    for ($i=1; $i<=count($data['outbound_emails_count']); $i++){
                        $data['outbound_emails_color'][] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
                    }
                }
            }
        }
        $data['side_menu_index'] = 'ticketmng';
        //$data['side_menu_index'] = 'email';
        $data['source_option'] = array("E"=>"Email", "C"=>"CRM");
        $data['pageTitle'] = 'Email Dashboard';
        $data['request'] = $request;
        $this->getTemplate()->display('email_dashboard_new', $data);
    }

    function actionTemplates()
    {
        include('model/MEmailTemplate.php');
        $et_model = new MEmailTemplate();

        $data['sid'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $dispositionid = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $data['emails'] = $et_model->getTemplateOptions($dispositionid, 'Y');
        $data['pageTitle'] = 'Select Email';
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode($data);exit;
        }
        $this->getTemplate()->display_popup('email_template_select', $data, false, false);
        //$this->getTemplate()->display_popup('email_template_select_2', $data);
    }

    function actionTemplatetext()
    {
        include('model/MEmailTemplate.php');
        $et_model = new MEmailTemplate();
        include('model/MEmail.php');
        $email_model = new MEmail();

        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
        $email = $et_model->getTemplateById($ticketid);
        if (!empty($email)) {
            //$email->mail_body .= "<p>";
            //$eTicketInfo = $email_model->getETicketById($ticketid);
            //if (!empty($eTicketInfo)) {
            //$signature = $email_model->getEmailSignature($skillid);
            //if (!empty($signature) && $signature->status == 'Y') $email->mail_body .= $signature->signature_text;
            //}
            $email->mail_body = !empty($email->mail_body) ? nl2br(base64_decode($email->mail_body)) : "";
            echo $email->mail_body;
        }
        exit;
    }

    function actionTicketCategory(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['agents'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {

                $data['pageTitle'] = 'Change Category to ticket :: '  . $eTicketInfo->ticket_id;

                if (isset($_POST['submitcategory'])) {
                    $aid = isset($_REQUEST['category_id']) ? trim($_REQUEST['category_id']) : '';
                    if ($eTicketInfo->category_id != $aid) {
                        $isAllowed = true;
                        /*if (!empty($aid)) {
                            $result = $eTicket_model->getSkillAgents($eTicketInfo->skill_id, $aid);
                            if (empty($result)) $isAllowed = false;
                        }*/

                        if ($isAllowed) {
                            $is_update = $eTicket_model->updateCategory($ticketid, $aid, UserAuth::getCurrentUser()) ;

                            if ($is_update) {
                                $data['message'] = 'Category changed successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to Change Category!!';
                            }

                        } else {
                            $errMsg = 'Privilege error!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                $data['categories'] = $eTicket_model->getTicketCategory('','A');
                //var_dump($data['agents']);
            } else {
                $fatalErr = 'Invalid ticket!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }

        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Change Category to ticket';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Change Category to ticket';
        $this->getTemplate()->display_popup('email_ticket_category', $data);
    }

    function actionAssign()
    {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['agents'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {

                $data['pageTitle'] = 'Assign agent to email :: '  . $eTicketInfo->ticket_id;

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
                            //if (!empty($acd_status) && $acd_status->acd_status == 'N') {
                            $is_update = $eTicket_model->assignAgent($ticketid, $aid, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionTransfer($ticketid, '', $aid);
                                /*$distribution_res = $eTicket_model->isDistributed($ticketid);
                                if (!empty($distribution_res->status) && $distribution_res->status=='R') {
                                    $eTicket_model->setDistributionSkill($ticketid, '',$aid, 'A');  //////   A = Assign
                                } else {
                                    $eTicket_model->deleteEmailDistribution($ticketid);
                                }*/
                                if (!empty($acd_status)) $eTicket_model->deleteEmailDistribution($ticketid);
                                $eTicket_model->addToAuditLog('Agent Assign', 'U', "ticket_id", "$ticketid");
                                $data['message'] = 'Agent assigned successfully.';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to assign agent!!';
                            }
                            /*} else {
                                $errMsg = 'Already Assigned!!';
                            }*/
                        } else {
                            $errMsg = 'Privilege error!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                $data['agents'] = $eTicket_model->getSkillAgents($eTicketInfo->skill_id);
                //var_dump($data['agents']);
            } else {
                $fatalErr = 'Invalid email!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }

        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Assign agent to email';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Assign agent to email';
        $this->getTemplate()->display_popup('email_assign_agent', $data);
    }

    function actionDispositionchildren()
    {
        $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $options = array();
        if (!empty($did)) {
            include('model/MEmail.php');
            $eTicket_model = new MEmail();

            $options = $eTicket_model->getDispositionChildrenOptions('', $did);
        }

        echo json_encode($options);
    }

    function actionDispositionOld()
    {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
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


                    if ($eTicketInfo->disposition_id != $did) {
                        $isAllowed = true;
                        if (!empty($did)) {
                            $result = $eTicket_model->getDispositionById($did, $eTicketInfo->skill_id);
                            if (empty($result)) $isAllowed = false;
                        }

                        if ($isAllowed) {
                            $is_update = $eTicket_model->setEmailDisposition($ticketid, $did, UserAuth::getCurrentUser());

                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionDisposition($ticketid);
                                $data['message'] = 'Disposition updated successfully !!';
                                $data['msgType'] = 'success';
                                $eTicket_model->addToAuditLog('Disposition', 'U', "ticket_id", "$ticketid");
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

                $data['dispositions0'] = $eTicket_model->getDispositionChildrenOptions($eTicketInfo->skill_id, '');//;$eTicket_model->getDispositionTreeOptions($eTicketInfo->skill_id);
                //var_dump($data['agents']);
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

        $data['disposition_ids'] = $eTicket_model->getDispositionPathArray($eTicketInfo->disposition_id);
        $data['eTicket_model'] = $eTicket_model;
        //var_dump($data['disposition_ids']);

        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Set disposition to ticket';
        $this->getTemplate()->display_popup('email_set_disposition', $data);
    }

    function actionDisposition(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['dispositions'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {
                $data['dispositions'] = $eTicket_model->getDispositionChildrenOptions($eTicketInfo->skill_id);

                //dd($eTicketInfo);
                $data['pageTitle'] = 'Set disposition to email :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {
                    $did = $_REQUEST['disposition_id'];
                    if ($eTicketInfo->disposition_id != $did) {
                        $isAllowed = true;
                        if (!empty($did)) {
                            $result = $eTicket_model->getDispositionById($did, $eTicketInfo->skill_id);
                            if (empty($result)) $isAllowed = false;
                        }
                        if ($isAllowed) {
                            $is_update = $eTicket_model->setEmailDisposition($ticketid, $did, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionDisposition($ticketid);
                                $data['message'] = 'Disposition updated successfully !!';
                                $data['msgType'] = 'success';
                                $eTicket_model->addToAuditLog('Disposition', 'U', "ticket_id", "$ticketid");
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
            } else {
                $fatalErr = 'Invalid email!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Set disposition to email';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }
        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Set disposition to email';
        $this->getTemplate()->display_popup('email_set_disposition', $data);
    }

    function actionStatus()
    {
        include('model/MEmail.php');
        include('model/MCustomerJourney.php');
        $eTicket_model = new MEmail();
        $journey_model = new MCustomerJourney();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['callid'] = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $data['statuses'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);
        $data['statuses'] = $eTicket_model->getChangableTicketStatus('S');

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);
            if (!empty($eTicketInfo)) {

                $data['pageTitle'] = 'Update status of email :: '  . $eTicketInfo->ticket_id;

                if (isset($_POST['submitagent'])) {

                    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
                    $phone_number = isset($_REQUEST['phone_number']) ? trim(substr($_REQUEST['phone_number'], -10)) : '';
                    $reschedule_date_time = isset($_POST['sdate']) ? trim($_POST['sdate']) : '';
                    $reschedule_date_time = !empty($reschedule_date_time) ? date_format(date_create_from_format("d/m/Y H:i",$reschedule_date_time), 'Y-m-d H:i') : '';
                    if ( in_array($status, $data['statuses']) && ($eTicketInfo->status != $status) || ($status=='R' && date("Y-m-d H:i", $eTicketInfo->reschedule_time) != $reschedule_date_time) ) {
                        $isAllowed = true;
                        if (empty($status)) {
                            $isAllowed = false;
                        }
                        if ($isAllowed) {
                            $is_update = $eTicket_model->updateTicketStatus($ticketid, $status, UserAuth::getCurrentUser(), $eTicketInfo->created_for, UserAuth::getRoleID(), $eTicketInfo, $reschedule_date_time, $data['callid']);
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSession($ticketid, $status, UserAuth::getCurrentUser());
                                if (!empty($phone_number) && ($status=="S" || $status=="E")) {
                                    $session_id = $eTicket_model->getSessionId($ticketid, $status);
                                    $journey_model->addToCustomerJourney($phone_number, "EM","", $session_id);
                                }
                                $data['message'] = 'Status updated successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to update status!!';
                            }
                        } else {
                            $errMsg = 'Provide valid status!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                /*$data['statuses'] = $eTicket_model->getChangableTicketStatus($eTicketInfo->status);
                if ($eTicketInfo->status == 'E' && (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor'))) {
                    $data['statuses'] = array('P');
                }*/
                //$data['statuses'] = $eTicket_model->getChangableTicketStatus('S');
            } else {
                $fatalErr = 'Invalid email!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }

        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Update status of email';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update status of email';
        $this->getTemplate()->display_popup('email_set_status', $data);
    }

//    function actionStatusMulti(){
//        include('model/MEmail.php');
//        $eTicket_model = new MEmail();
//        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
//        $data['callid'] = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
//        $data['session_id'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
//        $data['mail_sl'] = isset($_REQUEST['msl']) ? trim($_REQUEST['msl']) : '';
//        $data['session_mail_count'] = isset($_REQUEST['mc']) ? trim($_REQUEST['mc']) : 0;
//
//        $data['statuses'] = null;
//        $eTicketInfo = null;
//        $errMsg = '';
//        $fatalErr = '';
//        $role = UserAuth::getRole();
//        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);
//
//        if ($isAllowedToChange) {
//            $eTicketInfo = $eTicket_model->getETicketById($ticketid);
//            $eTicketMsg = $eTicket_model->getETicketById($ticketid);
//            if (!empty($eTicketInfo)) {
//                $data['pageTitle'] = 'Update status of ticket :: '  . $eTicketInfo->ticket_id;
//                if (isset($_POST['submitagent'])) {
//
//                    $is_update_2 = false;
//                    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
//                    $reschedule_date_time = isset($_POST['sdate']) ? trim($_POST['sdate']) : '';
//                    $reschedule_date_time = !empty($reschedule_date_time) ? date_format(date_create_from_format("d/m/Y H:i",$reschedule_date_time), 'Y-m-d H:i') : '';
//                    if ( ($eTicketInfo->status != $status) || ($status=='R' && date("Y-m-d H:i", $eTicketInfo->reschedule_time) != $reschedule_date_time) ) {
//                        $isAllowed = true;
//                        if (empty($status)) {
//                            $isAllowed = false;
//                        }
//                        if ($isAllowed) {
//                            $is_update = $eTicket_model->updateTicketStatus($ticketid, $status, UserAuth::getCurrentUser(), $eTicketInfo->created_for, UserAuth::getRoleID(), $eTicketInfo, $reschedule_date_time, $data['callid']);
//                            if ($is_update) {
//                                $is_update_2 = true;
//                                $eTicket_model->updateLogEmailSessionMulti($ticketid, $status, UserAuth::getCurrentUser(), $data['session_id'], $data['mail_sl']);
//                                $eTicket_model->setShowStatus($ticketid, $data['session_id']);
//                                $eTicket_model->setEmailMessageStatus($ticketid, $data['mail_sl'], $status);
//                            } else {
//                                $errMsg = 'Failed to update status!!';
//                            }
//
//                        } else {
//                            $errMsg = 'Provide valid status!!';
//                        }
//                    } else {
//                        $errMsg = 'No change found for status!!';
//                    }
//                    ////// Disposition START ////
//                    $session_data = $eTicket_model->isLogExists($ticketid, $data['session_id']);
//                    if (!empty($session_data)){
//                        $is_update_1 = false;
//                        $did = '';
//                        for ($i=0;$i<=10; $i++) {
//                            $did1 = isset($_REQUEST['disposition_id'.$i]) ? trim($_REQUEST['disposition_id'.$i]) : '';
//                            if (!empty($did1)) $did = $did1;
//                            else break;
//                        }
//                        if ($eTicketInfo->disposition_id != $did) {
//                            $isAllowed = true;
//                            if (!empty($did)) {
//                                $result = $eTicket_model->getDispositionById($did, $eTicketInfo->skill_id);
//                                if (empty($result)) $isAllowed = false;
//                            }
//                            if ($isAllowed) {
//                                $is_update = $eTicket_model->setEmailDisposition($ticketid, $did, UserAuth::getCurrentUser());
//                                if ($is_update) {
//                                    $is_update_1 = true;
//                                    $eTicket_model->setSessionDisposition($ticketid, $did, $data['session_id'], UserAuth::getCurrentUser(), $session_data);
//                                    $eTicket_model->setEmailMessageDisposition($ticketid, $data['mail_sl'], $did);
//                                    $eTicket_model->addETicketActivity($ticketid, UserAuth::getCurrentUser(), 'D', $did);
//                                } else {
//                                    $errMsg = 'Failed to update disposition!!';
//                                }
//                            } else {
//                                $errMsg = 'Privilege error!!';
//                            }
//                        }else {
//                            $errMsg = 'No change found for disposition!!';
//                        }
//                    } else {
//                        $errMsg = 'Please change status first !!';
//                    }
//                    ////// Disposition END ////
//                    if ($is_update_1 || $is_update_2){
//                        $data['message'] = 'Updated successfully !!';
//                        $data['msgType'] = 'success';
//                        $this->getTemplate()->display_popup('popup_message', $data);
//                    }
//                }
//                $data['statuses'] = $eTicket_model->getChangableTicketStatus('K');
//                $data['disposition_ids'] = $eTicket_model->getDispositionPathArray($eTicketInfo->disposition_id);
//            } else {
//                $fatalErr = 'Invalid ticket!!';
//            }
//        } else {
//            $fatalErr = 'Privilege error!!';
//        }
//
//        if (!empty($fatalErr)) {
//            $data['pageTitle'] = 'Update status of ticket';
//            $data['message'] = $fatalErr;
//            $data['msgType'] = 'error';
//            $this->getTemplate()->display_popup('popup_message', $data);
//        }
//
//        $data['request'] = $this->getRequest();
//        $data['tid'] = $ticketid;
//        $data['errMsg'] = $errMsg;
//        $data['ticket_info'] = $eTicketInfo;
//        $data['eTicket_model'] = $eTicket_model;
//        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update status of ticket';
//        $this->getTemplate()->display_popup('email_set_status_multi', $data);
//    }

    /*function actionStatus_new()
    {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['statuses'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);
            if (!empty($eTicketInfo)) {

                $data['pageTitle'] = 'Update status of ticket :: '  . $eTicketInfo->ticket_id;

                if (isset($_POST['submitagent'])) {
                    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
                    if ($eTicketInfo->status != $status) {
                        $isAllowed = true;
                        if (empty($status)) {
                            $isAllowed = false;
                        }

                        if ($isAllowed) {
                            $is_update = $eTicket_model->updateTicketStatus($ticketid, $status, UserAuth::getCurrentUser());

                            if ($is_update) {
                                $data['message'] = 'Status updated successfully !!';
                                $data['msgType'] = 'success';
                                $this->getTemplate()->display_popup('popup_message', $data);
                            } else {
                                $errMsg = 'Failed to update status!!';
                            }

                        } else {
                            $errMsg = 'Provide valid status!!';
                        }
                    } else {
                        $errMsg = 'No change found!!';
                    }
                }

                $data['statuses'] = $eTicket_model->getChangableTicketStatus($eTicketInfo->status);
                if ($eTicketInfo->status == 'E' && (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor'))) {
                    $data['statuses'] = array('P');
                }
                $data['dispositions0'] = $eTicket_model->getDispositionChildrenOptions($eTicketInfo->skill_id, '');//DID
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

        $data['disposition_ids'] = $eTicket_model->getDispositionPathArray($eTicketInfo->disposition_id);//did
        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update status of ticket';
        $this->getTemplate()->display_popup('email_set_status_new', $data);
    }*/

    function actionAttachment()
    {
        include('model/MEmail.php');
        include('model/MCcSettings.php');
        $ccsettings_model = new MCcSettings();
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $sl = isset($_REQUEST['sl']) ? trim($_REQUEST['sl']) : '';
        $p = isset($_REQUEST['p']) ? trim($_REQUEST['p']) : '';

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

        $attachment = $eTicket_model->getAttachmentDetails($ticketid, $sl, $p);

        if (!empty($attachment))  {
            include_once('conf.email.php');

            $yy = date("y", $attachment->tstamp);
            $mm = date("m", $attachment->tstamp);
            $dd = date("d", $attachment->tstamp);

            $email_settings = $ccsettings_model->getEmailSettings();
            //$attachment_save_path = !empty($email_settings->attachment_save_path) ? base64_decode($email_settings->attachment_save_path) : "content/";
            $attachment_save_path = '';
            if (!empty($email_settings)){
                foreach ($email_settings as $itm){
                    if ($itm->item == "attachment_save_path"){
                        $attachment_save_path =  base64_decode($itm->value);
                    }
                }
            }
            $file = $attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $ticketid . '/' . $sl . '/' . $attachment->file_name;

            if (file_exists($file)) {
                if (FALSE!== ($handler = fopen($file, 'r'))) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'. str_replace('"', '\\"', basename($file)) . '"');
                    header('Content-Transfer-Encoding: chunked'); //changed to chunked
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    //header('Content-Length: ' . filesize($file)); //Remove
                    //Send the content in chunks
                    /*
                    while(false !== ($chunk = fread($handler,4096))) {
                        echo $chunk;
                        exit;
                    }
                    */

                    while (!feof($handler)) {
                        echo fread($handler, 4096);
                        ob_flush();  // flush output
                        flush();
                    }
                }
                exit;
            }

        }
        //$emails = $eTicket_model->getTicketEmails($ticketid, $sl);
        exit;
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

    private function validateEmailPostData($ticketinfo, $callid, $post){
        $obj = new stdClass();
        $obj->error_msg = "";

        $_STATUS = $post['st'];
        $_RESCHEDULETIME = !empty($post['reschedule_datetime']) ? date_format(date_create_from_format("d/m/Y H:i",$post['reschedule_datetime']), 'Y-m-d H:i') : '';
        $is_forward = !empty($post['forward']) ? "Y" : "N";
        $_number = $this->get_phone_number_from_email_body($post['message']);
        $_TO = !empty($post['to']) ? $post['to'] : $post['forward'];
        $_TO = $this->getValidEmail($_TO, "string");
        $mail_body = $post['message'];
        $now = time();

        if (empty($_TO)){
            $obj->error_msg = "There is no To email address!";
            return $obj;
        } elseif (empty($mail_body)){
            $obj->error_msg = "Email body missing!";
            return $obj;
        }elseif (empty($_STATUS)){
            $obj->error_msg = "Status missing!";
            return $obj;
        } elseif ($_STATUS == 'R' && empty($_RESCHEDULETIME)){
            $obj->error_msg = "Reschedule time missing!";
            return $obj;
        }elseif (empty($ticketinfo->ticket_id)){
            $obj->error_msg = "Invalid Information!";
            return $obj;
        }
        $obj->ticket_id=$ticketinfo->ticket_id;
        $obj->mail_sl="";
        $obj->subject=$ticketinfo->subject;
        $obj->from_name=$ticketinfo->fetch_box_name;
        $obj->from_email=$ticketinfo->fetch_box_email;
        $obj->mail_from=$ticketinfo->fetch_box_email;
        $obj->mail_to=$this->getValidEmail($_TO, "string");
        $obj->mail_cc=$this->getValidEmail($post['cc'], "string");
        $obj->mail_bcc=$this->getValidEmail($post['bcc'], "string");
        $obj->mail_body=base64_encode($mail_body);
        $obj->has_attachment='';
        $obj->is_forward=$is_forward;
        $obj->agent_id=UserAuth::getCurrentUser();
        $obj->status="O";
        $obj->tstamp=$now;
        $obj->phone=$_number;
        $obj->email_status=$_STATUS;
        $obj->email_did=!empty($post['dd']) ? $post['dd'] : "";
        $obj->acd_agent=!empty($callid) ? UserAuth::getCurrentUser() : "";
        $obj->acd_status=!empty($callid) ? $_STATUS : "";
        $obj->single_reply = !empty($post['specific_email_sl']) ? trim($post['specific_email_sl']) : '';
        $obj->session_id="";

        $obj->skill_id=$ticketinfo->skill_id;
        $obj->rs_tr=$_STATUS=="R" ? "Y": "";
        $obj->reschedule_time=$_STATUS=="R" ? $_RESCHEDULETIME: "";
        $obj->rs_tr_create_time=$_STATUS=="R" ? $now : "";
        $obj->fetch_box_email=$ticketinfo->fetch_box_email;
        $obj->fetch_box_name=$ticketinfo->fetch_box_name;
        $obj->forwarded_attachment_hidden=!empty($post['forwarded_attachment_hidden']) ? json_decode($post['forwarded_attachment_hidden']) : "";
        $obj->customer_phone_number = !empty($post['customer_phone_number']) ? substr($post['customer_phone_number'], -10) : '';
        return $response['data']=$obj;
    }

    function actionGetEmailAddressbook(){
        include_once('model/MEmail.php');
        $eTicket_model = new MEmail();
        $total_count = $_REQUEST['total'] ?? 0;
        $address_book_emails = [];
        $response = new stdClass();
        $response->status = false;
        $response->count = $total_count;

        $current_count = $eTicket_model::getTotalNumberOfEmailAddress();
        if ($current_count > $total_count){
            $ccmails = $eTicket_model->getEmailAddressBook();
            if (!empty($ccmails)){
                foreach ($ccmails as $item) {
                    $address_book_emails[] = $item->name . ' (' . $item->email . ')';
                }
            }
            $response->status = true;
            $response->data = $address_book_emails;
            $response->count = $current_count;
        }
        echo json_encode($response);
        exit();
    }

    function actionDetails(){
        include_once('conf.email.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        include('model/MCcSettings.php');
        include('model/MCustomerJourney.php');
        $log_start_time = microtime(true)*1000;
        $agent_model = new MAgent();
        $eTicket_model = new MEmail();
        $journey_model = new MCustomerJourney();

        unset($_SESSION['msg_srv_ip']);
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['callid'] = !empty($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
        $data['info']  = !empty($_REQUEST['info']) ? $_REQUEST['info'] : "";

        if (!empty($data['info'])) $_SESSION['info'] = $data['info'];
        $data['info'] = !empty($_SESSION['info']) ? $_SESSION['info'] : ""; ////when posted

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
        $data['current_user'] = '';
        $data['attachment_save_path'] = '';
        //$data['source_name'] = array("C"=>"CRM","E"=>"Email","M"=>"Manual");
        //$data['ccmails'] = $eTicket_model->getEmailAddressBook();    ///////  NEW EMAIL ADDRESS ///////

        $ccsettings_model = new MCcSettings();
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

        $data['sublink'] = site_url().$this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=details&tid=".$ticketid."&callid=".$data['callid'].'&info='.$data['info']);
        if ($this->getRequest()->isPost() && $isAllowedToChange) {
            if ($_POST['message'] != "") {
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName().'&callid='.$data['callid'].'&info='.$data['info']);
                $eTicketInfo = $eTicket_model->getETicketById($ticketid);
                $post_data = $this->validateEmailPostData($eTicketInfo, $data['callid'], $_POST);

                if (empty($post_data->error_msg) && $eTicket_model->isEmailSendValid($ticketid)) {
                    $result = $eTicket_model->addEmailMsg($post_data, $_FILES['att_file'], $data['attachment_save_path'], $eTicketInfo);
                    if ($result){
                        $eTicket_model->updateTicketStatus($post_data->ticket_id, $post_data->email_status, $post_data->agent_id, $eTicketInfo->created_for, UserAuth::getRoleID(), $eTicketInfo, $post_data->reschedule_time, $data['callid'], true);
                        if ($post_data->email_did) $eTicket_model->setEmailDisposition($post_data->ticket_id, $post_data->email_did, $post_data->agent_id);
                        $eTicket_model->UpdateLogEmailSession($post_data->ticket_id, $post_data->email_status, $post_data->agent_id, $post_data);
                        if (!empty($post_data->customer_phone_number)) {
                            //$session_id = $eTicket_model->getSessionId($post_data->ticket_id, $post_data->email_status);
                            //$journey_model->addToCustomerJourney($post_data->customer_phone_number, "EM", "",$session_id);//slow query
                            $sessionDetails = $eTicket_model->getLastSessionById($post_data->ticket_id, $post_data->email_status);//06-10-2020
                            $journey_model->addCustomerJourneyForEmail($post_data->customer_phone_number, $sessionDetails);//06-10-2020
                        }
                        if ($data['callid']) {
                            $_msg = array("method"=>"AG_EMAIL_CLOSE", "email_id"=>"MAIL/".$ticketid, "call_id"=>$data['callid']);
                            $udp_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';
                            $eTicket_model->send_Udp_msg(json_encode($_msg), $udp_port);
                            $this->getTemplate()->display_popup_chat('msg', array('pageTitle'=>'Ticket Details', 'isError'=>false, 'msg'=>'Message successfully sent'));
                        }
                        $this->getTemplate()->display('msg', array('pageTitle'=>'Ticket Details', 'isError'=>false, 'msg'=>'Message successfully sent', 'redirectUri'=>$url));
                    }
                } else {
                    if ($data['callid']){
                        $this->getTemplate()->display_popup_chat('msg', array('pageTitle'=>'Ticket Details', 'isError'=>true, 'msg'=>$post_data->error_msg));
                    }
                    $this->getTemplate()->display('msg', array('pageTitle'=>'Ticket Details', 'isError'=>true, 'msg'=>$post_data->error_msg));
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
        $data['status_option_list'] = $eTicket_model->getTicketStatusOptions();
        $data['queue_msg '] = '';
        $data['agent_msg '] = '';
        $data['status_updated_by'] = '';
        $data['disposition_list'] = [];

        if ($ticketid != '') {
            $data['eTickets'] = $eTicket_model->getETicketById($ticketid);
            $data['status_updated_by'] = strlen($data['eTickets']->status_updated_by)==4 ? $agent_model->getAgentNameById($data['eTickets']->status_updated_by)->nick." [".$data['eTickets']->status_updated_by."]" : $data['eTickets']->status_updated_by;
            ////09-08-18///////
            $data['is_setinterval_active'] = $data['eTickets']->current_user==UserAuth::getCurrentUser() ? 'active' : '';
            $time_difference = round((time()-$data['eTickets']->last_update_time)/60);
            $data['isAllowedToPull'] = empty($data['eTickets']->current_user) || ($time_difference > $this->last_update_difference_time) ? true : false;
            if (!$data['isAllowedToPull']) {
                $data['working_agent'] = $data['eTickets']->agent_id;
                $data['working_agent'] = $data['eTickets']->agent_id_2;
            }
            $data['current_user'] = !empty($agent_model->getAgentNameById($data['eTickets']->current_user)) ? $agent_model->getAgentNameById($data['eTickets']->current_user)->nick : $data['eTickets']->current_user;
            $distribution = $eTicket_model->isDistributed($ticketid);
            $data['acd_status'] = !empty($distribution->acd_status) ? $distribution->acd_status : "";
            $data['dist_status'] = !empty($distribution->status) ? $distribution->status : "";
            if (!empty($distribution->acd_status) && $distribution->acd_status == "Q"){
                $data['queue_msg'] = "This email is already in the queue and the system will automatically distribute it to an agent in the Call Control panel";
            } elseif (!empty($distribution->acd_status) && $distribution->acd_status == "A"){
                $data['agent_msg'] = "This email is already distributed to an Agent and he/she does not pull it yet";
            }
            $data['eTicketEmail'] = $eTicket_model->getTicketEmails($ticketid);
            $data['cc_emails'] = $this->getValidEmail($data['eTicketEmail'][0]->mail_cc);
            $data['bcc_emails'] = $this->getValidEmail($data['eTicketEmail'][0]->mail_bcc);
            //GPrint($data['eTicketEmail']);die;
            ////////  START 22-09-18   //////////
            $this_email_cc = !empty($data['cc_emails']) ? explode(',',$data['cc_emails']) : "";
            $this_email_bcc = !empty($data['bcc_emails']) ? explode(',',$data['bcc_emails']) : "";
//            $data['cc_emails_outoff_addressbook'] = [];
//            if (!empty($data['ccmails'])){
//                foreach ($data['ccmails'] as $key )$address_book_emails[] = $key->email;
//                $data['cc_emails_outoff_addressbook'] = $this->getEmailsFromOutOfAddressBook($address_book_emails, $this_email_cc);
//                $data['bcc_emails_outoff_addressbook'] = $this->getEmailsFromOutOfAddressBook($address_book_emails, $this_email_bcc);
//            }
            ////////  END 22-09-18   //////////email-module-udp-msg
            if (!empty($data['eTickets'])) {
                $data['disposition'] = $eTicket_model->getDispositionById($data['eTickets']->disposition_id, $data['eTickets']->skill_id);
                if (!empty($data['eTickets']->assigned_to)) {
                    $agent_model = new MAgent();
                    $data['assigned_to'] = $agent_model->getAgentById($data['eTickets']->assigned_to);
                }
                $data['signature'] = "";//$eTicket_model->getEmailSignature($data['eTickets']->skill_id);
                $data['last_email'] = "";//$eTicket_model->getLastEmail($ticketid);
                $previos_email_datetime = !empty($data['last_email']->tstamp) ? 'On '.date('D, M j, Y', $data['last_email']->tstamp).' at '.date('h:m A', $data['last_email']->tstamp). htmlspecialchars($data['last_email']->mail_from) . " wrote:</b>" : '';
                $data['last_email_body'] = !empty($data['last_email']->mail_body) ? $previos_email_datetime.'<br/>'.nl2br(base64_decode($data['last_email']->mail_body)) : '';
                $eTicket_model->addETicketActivity($ticketid, UserAuth::getCurrentUser(), 'V');
            }

            //First Time open updated here
            if (!$this->getRequest()->isPost() && !FIRST_OPEN_TIME_SET_FROM_FIRST_PULL) {
                //$eTicket_model->updateFirstOpenTimeById($ticketid);
                $eTicket_model->updateFirstOpenTimeForLogEmailSession($data['eTickets'], UserAuth::getCurrentUser());
            }

            //CHECK IF FIRST OPEN TIME MISSING
            if (FIRST_OPEN_TIME_SET_FROM_FIRST_PULL && !empty($data['is_setinterval_active'])) {
                $eTicket_model->updateFirstOpenTimeForLogEmailSession($data['eTickets'], UserAuth::getCurrentUser());
            }

            $eTicket_model->check_empty_log_email_session($data['eTickets'], UserAuth::getCurrentUser());
            //$data['disposition_list'] = $eTicket_model->getDispositionTreeOptions($data['eTickets']->skill_id);
            $data['disposition_list'] = $eTicket_model->getDispositionChildrenOptions($data['eTickets']->skill_id);
        }
        //$data['disposition_ids'] = !empty($data['eTickets']->disposition_id)?$eTicket_model->getDispositionPathArray($data['eTickets']->disposition_id):'';///////DID change////
        $data['disposition_ids'] = "";
//        $data['msg_pattern'] = isset($replace_text_pattern) ? $replace_text_pattern : "";
        $data['side_menu_index'] = 'ticketmng';
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Email Details';
        $data['pageTitle2'] = htmlspecialchars(base64_decode($data['eTickets']->subject), ENT_QUOTES);//'Email Details';
        $data['smi_selection'] = 'email_init';
        $data['read_image_file_path'] = $this->getTemplate()->read_email_body_image;

        $log_end_time = microtime(true)*1000;
        $filename = "/usr/local/ccpro/dblog/email_details_time.log";
        $file = fopen( $filename, "a+" );
        $log_data = array("IP"=>$_SERVER['REMOTE_ADDR'], "AGENT-ID"=>UserAuth::getCurrentUser(), "EMAIL-ID"=>$ticketid, "START TIME"=>date("y-m-d H:i:s", $log_start_time), "START TIME(milisec)"=>$log_start_time, "END TIME"=>date("y-m-d H:i:s", $log_end_time), "END TIME(milisec)"=>$log_end_time, "DURATION"=>round($log_end_time-$log_start_time, 3));
        $log_data = json_encode($log_data);
        if( $file == true) {
            fwrite($file, "\n".$log_data."\n");
            fclose($file);
        }

        if (!empty($data['callid'])){
            $this->getTemplate()->display_popup_chat('eTicketInfo_new', $data); //////hiding side menu bar
        } else {
            $this->getTemplate()->display('eTicketInfo_new', $data);
        }
    }


    function actionAgentPingForEmailDashboard(){
        $msg_res['method'] = !empty($_REQUEST['method']) ? $_REQUEST['method'] : "";
        $msg_res['email_id'] = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : "";
        $msg_res['agent_id'] = !empty($_REQUEST['agent_id']) ? $_REQUEST['agent_id'] : "";
        $msg = json_encode($msg_res);
        $msg_srv_ip = "192.168.10.60";
        $msg_srv_port = 9090;

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($sock, $msg, strlen($msg), 0, $msg_srv_ip, $msg_srv_port);
        socket_close($sock);
        //var_dump($test);
        echo json_encode($sock);
        exit();
    }

    function actionLastEmailBody(){
        $ticket_id = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : "";
        $skill_id = !empty($_REQUEST['skill_id']) ? str_replace('"','', $_REQUEST['skill_id']) : "";
        $type= !empty($_REQUEST['type']) ? $_REQUEST['type'] : "";
        $response = "";
        $signature = "";
        if ($ticket_id){
            include_once('model/MEmail.php');
            $email_model = new MEmail();
            $emailObj = $email_model->getLastEmail($ticket_id);
            $previos_email_datetime = !empty($emailObj->tstamp) ? 'On '.date('D, M j, Y', $emailObj->tstamp).' at '.date('h:m A', $emailObj->tstamp). htmlspecialchars($emailObj->mail_from) . " wrote:</b>" : '';
            $mail_body = !empty($emailObj->mail_body) ? str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",base64_decode($emailObj->mail_body)) : "";

//            $dom = new DOMDocument();
//            $dom->loadHTML($dom);

//            $xpath = new DOMXPath($dom);
//            $xpath->query('');

//            GPrint($dom->documentElement);die;

            echo json_encode(stripslashes(nl2br($mail_body)));
            exit();

            $mail_body = !empty($mail_body) ? $previos_email_datetime.'<br/>'.addslashes(nl2br($mail_body)) : '';

            $sigObj = !empty($skill_id) ? $email_model->getEmailSignature($skill_id) : "";
            if (!empty($sigObj) && $sigObj->status == 'Y'){
                $signature = !empty($sigObj->signature_text) ? nl2br(base64_decode($sigObj->signature_text)) : "";
                $signature = str_replace("|AGENT-NAME|", "<br><br>".UserAuth::getUserName(), $signature);
                $signature = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$signature);
                $signature = addslashes($signature);
            }

            $type = $type=="FWD" ? '<b>---------- Forwarded Message -----------</b><br/><br/>' :'<b>---------- Previous Message -----------</b><br/><br/>';

            $response = "<br/><br/><br/><br/>".$signature.$type.$mail_body;
            if ($response){
                preg_match_all('/<img .*?>/',$response, $matches);
                $search = [];
                $replace = [];
                foreach ($matches[0] as $key => $item) {
                    $search[] = $item;
                    $replace[] = stripslashes($item);
                }
                $response = str_replace($search, $replace, $response);
            }
        }
        echo json_encode($response);
        exit;
    }

    function getEmailsFromOutOfAddressBook($addressBookEmails, $checkEmails){
        $response_email = [];
        if (!empty($checkEmails)){
            foreach ($checkEmails as $_eml){
                if(!in_array($_eml, $addressBookEmails)){
                    $response_email[] = $_eml;
                }
            }
        }
        return $response_email;
    }

    function actionchatSkillDispositions()
    {
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        include('model/MEmail.php');
        $email_model = new MEmail();
        $dispositions = array();
        if (!empty($sid)) {
            $dispositions = $email_model->getChatDispositionChildrenOptions($sid, '');
        }
        echo json_encode($dispositions);
    }

    function actionChatdispositionchildren()
    {
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $options = array();
        if (!empty($did)) {
            include('model/MEmail.php');
            $eTicket_model = new MEmail();

            $options = $eTicket_model->getChatDispositionChildrenOptions($sid, $did);
        }

        echo json_encode($options);
    }

    function actionChattemplates()
    {
        include('model/MEmailTemplate.php');
        $et_model = new MEmailTemplate();

        $skill_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $data['emails'] = $et_model->getChatTemplateOptions($skill_id, 'Y');
        //print_r($data['emails']);return;
        $data['pageTitle'] = 'Select Email';
        $this->getTemplate()->display_popup('email_template_select', $data, false, false);
    }

    function actionChattemplatetext()
    {
        include('model/MEmailTemplate.php');
        $et_model = new MEmailTemplate();
        include('model/MEmail.php');
        $email_model = new MEmail();

        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
        $email = $et_model->getChatTemplateById($ticketid);

        if (!empty($email)) {
            $email->message .= "";
            //$eTicketInfo = $email_model->getETicketById($ticketid);
            //if (!empty($eTicketInfo)) {
            //$signature = $email_model->getEmailSignature($skillid);
            //if (!empty($signature) && $signature->status == 'Y') $email->mail_body .= $signature->signature_text;
            //}

            echo $email->message;
        }
        exit;
    }

    function actionEmaillist() {
        include('model/MEmail.php');
        $email_model = new MEmail();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $emails =  empty($sid) ? array() : $email_model->getEmailBySkillId($sid);
        $emails = $emails==null ? array() : $emails;
        echo json_encode($emails);
    }

    private function get_default_ticket_category()
    {
        $ticket_category = new stdClass();
        $ticket_category->category_id = "";
        $ticket_category->title = "";
        $ticket_category->status = "";
        return $ticket_category;
    }
    private function validate_ticket_category($post=[], $isUpdate=false)
    {
        $email_model = new MEmail();
        $error_message = "";
        if (empty($post['title'])){
            $error_message .= "Title is required\n";
        }
        if (strlen($post['title']) > 40){
            $error_message .= "Title is Invalid\n";
        }
        if (empty($post['status'])){
            $error_message .= "Status is required\n";
        }
        if (strlen($post['status']) > 1){
            $error_message .= "Status is Invalid\n";
        }
        if (empty($error_message) && !$isUpdate)
        {
            $error_message .= $email_model->ticket_category_exists($post['title']) ? "Ticket Category Exists" : "";
        }

        if (empty($error_message) && !$isUpdate)
        {
            $error_message .= $email_model->ticket_category_exists($post['title']) ? "Ticket Category Exists" : "";
        }
        return $error_message;
    }

    function actionAddTicketCategory (){
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MEmail.php');
        $email_model = new MEmail();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $ticket_category = $this->get_default_ticket_category();

        if ($request->isPost())
        {
            $ticket_category->title = $request->getRequest('title');
            $ticket_category->status = $request->getRequest('status');

            $errMsg = $this->validate_ticket_category($request->getPost());
            if (empty($errMsg))
            {
                if($email_model->addTicketCategory($ticket_category->title, $ticket_category->status)){
                    $errMsg = "Ticket Category Successfully Added";
                    $errType = 0;
                    $url = $this->url("task=email&act=ticket-category-list");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else{
                    $errMsg = "Failed to Add Ticket Category";
                    $errType = 1;
                }
            }
        }

        $data['ticket_category'] = $ticket_category;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = false;
        $data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Email Category List', 'title'=>'Email Category List.'));
        //$data['pageTitle'] = empty($agentid) ? 'Add New Shift Profile' : 'Update Shift Profile';
        $data['pageTitle'] = 'Add New Email Category';
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        $this->getTemplate()->display('ticket_category_form', $data);
    }

    function actionUpdateTicketCategory(){
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MEmail.php');
        $email_model = new MEmail();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $id = $request->getRequest('id');
        //$ticket_category = "";
        $obj = new stdClass();
        $obj->title = "";
        $obj->status = "";
        $old_title = "";
        if (!empty($id)){
            $ticket_category = $email_model->getTicketCategoryByID($id);
            if (!empty($ticket_category)){
                $obj->title = $ticket_category->title;
                $old_title = $obj->title;
                $obj->status = $ticket_category->status;
            }
        }else {
            $errMsg = 'Invalid Information';
        }

        if ($request->isPost()) {
            $obj->title = $request->getRequest('title');
            $obj->status = $request->getRequest('status');

            $validation_check = true;
            if ($old_title != $obj->title) $validation_check = false;

            $errMsg = $this->validate_ticket_category($request->getPost(),$validation_check);
            if (empty($errMsg)) {
                //GPrint($id);die;
                if($email_model->updateTicketCategory($id, $obj->title, $obj->status)){
                    $errMsg = "Ticket Category Successfully Updated";
                    $errType = 0;
                    $url = $this->url("task=email&act=ticket-category-list");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else{
                    $errMsg = "Failed to Update Ticket Category";
                    $errType = 1;
                }
            }
        }

        //GPrint($id);die;
        $data['ticket_category'] = $obj;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = true;
        $data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Email Category List', 'title'=>'Email Category List.'));
        //$data['pageTitle'] = empty($agentid) ? 'Add New Shift Profile' : 'Update Shift Profile';
        $data['pageTitle'] = 'Update Email Category';
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        $this->getTemplate()->display('ticket_category_form', $data);
    }

    function actionTicketCategoryList(){

        include('model/MEmail.php');
        $eTicket_model = new MEmail();

        //GPrint($eTicket_model->getTicketCategory('','A'));die;
        //$agent_options = $agent_model->get_as_key_value();
        //$data['agent_list'] = $agent_options;
        //GPrint($agent_options);die;

        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $data['pageTitle'] = 'Email Category List';

        if (!empty($status)){
            $urlParam .= "&status=".$status;
            //$isDateToday = false;
        }
        $data['status'] = $status;
        $selectOpt = array('*'=>'Select');
        //$data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        //$data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions());
        //$data['dataUrl'] = $this->url('task=get-email-data&act='.$urlParam);
        $data['dataUrl'] = $this->url('task=get-email-data&act=ticket-category');

        //$data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        if(in_array(UserAuth::getRoleID(), array("R","S"))){
            $data['topMenuItems'] = array(array('href'=>'task=email&act=add-ticket-category', 'img'=>'fa fa-bar', 'label'=>'Add Email Category', 'title'=>'Add Email Category.'));
        }
        $this->getTemplate()->display('ticket_category_list', $data);
    }


    function actionAddDomain(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $errMsg = '';
        $errType = 1;
        $domain = '';
        $request = $this->getRequest();

        if ($request->isPost()){
            $domain = $request->getPost('domain');
            if (!empty($sid) && !empty($domain) && strlen($domain) <= 30){
                if (!MEmail::isDomainExists($sid,$domain)){
                    if (MEmail::addDomain($sid,$domain)){
                        $errMsg = 'Successfully Added.';
                        $errType = 0;
                        $url = $this->url("task=email&act=skill-domain&sid=$sid");
                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                    }else {
                        $errMsg = 'Failed to add Domain';
                        $errType = 1;
                    }
                } else {
                    $errMsg = 'Domain Name Already Exists.';
                    $errType = 1;
                }
            } else {
                $errMsg = 'Invalid Domain Name.';
                $errType = 1;
            }
        }

        $data['sid'] = $sid;
        $data['domain'] = $domain;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = false;
        //$data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Add New Skill Domain';
        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $this->getTemplate()->display('skill_domain_form', $data);
    }

    function actionUpdateSkillDomain(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $dmn = isset($_REQUEST['dmn']) ? trim($_REQUEST['dmn']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $dmn_url = $dmn;
        $request = $this->getRequest();
        $groups = null;
        $errMsg = '';
        $errType = 1;
        $domain = '';
        $dmn = urldecode(base64_decode($dmn));

        if (!empty($dmn)){
            $result = MEmail::getSkillDomainById($sid,$dmn);
            if (!empty($result) && is_array($result)){
                $domain = $result[0]->domain;
            } else {
                $errMsg = 'Invalid Data';
                $errType = 1;
            }
        } else {
            $errMsg = 'Invalid Data';
            $errType = 1;
        }

        if ($request->isPost()){
            $new_domain = $request->getPost('domain');
            if (!empty($sid) && !empty($new_domain) && strlen($new_domain) <= 30){
                if (!MEmail::isDomainExists($sid,$new_domain)){
                    if (MEmail::updateDoman($new_domain, $sid, $domain)){
                        $errMsg = 'Successfully Updated.';
                        $errType = 0;
                        $url = $this->url("task=email&act=skill-domain&sid=$sid");
                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                    }else {
                        $errMsg = 'Failed to Update Domain';
                        $errType = 1;
                    }
                } else {
                    $errMsg = 'Domain Name Already Exists.';
                    $errType = 1;
                }
            } else {
                $errMsg = 'Invalid Domain Name.';
                $errType = 1;
            }
            $domain = $new_domain;
        }


        $data['dmn_url'] = $dmn_url;
        $data['sid'] = $sid;
        $data['domain'] = $domain;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = true;
        //$data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Add New Skill Domain';
        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $this->getTemplate()->display('skill_domain_form', $data);
    }

    function actionSkillDomain(){
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $data['topMenuItems'] = array(
            array('href'=>'task=skills', 'img'=>'fa fa-tasks', 'label'=>'List Skill(s)'),
            array('href'=>'task=email&act=add-domain&sid='.$sid, 'img'=>'fa fa-plus-square', 'label'=>'Add New Skill Domain')
        );
        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $data['sid'] = $sid;
        $data['pageTitle'] = 'Skill Domain';
        $data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-skill-domain&sid='.$sid);
        //var_dump($data['dataUrl']);die;
        $this->getTemplate()->display('email_skill_domain', $data);
    }

    function actionSkillKeyword(){
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $data['topMenuItems'] = array(
            array('href'=>'task=skills', 'img'=>'fa fa-tasks', 'label'=>'List Skill(s)'),
            array('href'=>'task=email&act=add-keyword&sid='.$sid, 'img'=>'fa fa-plus-square', 'label'=>'Add New Skill Keyword')
        );

        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $data['sid'] = $sid;
        $data['pageTitle'] = 'Skill Keyword';
        $data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-skill-keyword&sid='.$sid);
        //var_dump($data['dataUrl']);die;
        $this->getTemplate()->display('email_skill_keyword', $data);
    }

    function actionAddKeyword(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $errMsg = '';
        $errType = 1;
        $keyword = '';
        $request = $this->getRequest();

        if ($request->isPost()){
            $keyword = $request->getPost('keyword');
            if (!empty($sid) && !empty($keyword) && strlen($keyword) <= 30){
                if (!MEmail::isKeywordExists($keyword)){
                    if (MEmail::addKeyword($sid,$keyword)){
                        $errMsg = 'Successfully Added.';
                        $errType = 0;
                        $url = $this->url("task=email&act=skill-keyword&sid=$sid");
                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                    }else {
                        $errMsg = 'Failed to add Keyword';
                        $errType = 1;
                    }
                } else {
                    $errMsg = 'Keyword Already Exists.';
                    $errType = 1;
                }
            } else {
                $errMsg = 'Invalid Keyword.';
                $errType = 1;
            }
        }

        $data['sid'] = $sid;
        $data['keyword'] = $keyword;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = false;
        //$data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Add New Skill Keyword';
        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $this->getTemplate()->display('skill_keyword_form', $data);
    }

    function actionUpdateSkillKeyword(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $kwd = isset($_REQUEST['kwd']) ? trim($_REQUEST['kwd']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $kwd_url = $kwd;
        $request = $this->getRequest();
        $groups = null;
        $errMsg = '';
        $errType = 1;
        $keyword = '';
        $kwd = urldecode(base64_decode($kwd));

        if (!empty($kwd)){
            $result = MEmail::getSkillKeywordById($sid,$kwd);
            if (!empty($result) && is_array($result)){
                $keyword = $result[0]->keyword;
            } else {
                $errMsg = 'Invalid Data';
                $errType = 1;
            }
        } else {
            $errMsg = 'Invalid Data';
            $errType = 1;
        }

        if ($request->isPost()){
            $new_keyword = $request->getPost('keyword');
            if (!empty($sid) && !empty($new_keyword) && strlen($new_keyword) <= 30){
                if (!MEmail::isKeywordExists($sid,$new_keyword)){
                    if (MEmail::updateKeyword($new_keyword, $sid, $keyword)){
                        $errMsg = 'Successfully Updated.';
                        $errType = 0;
                        $url = $this->url("task=email&act=skill-keyword&sid=$sid&kwd=$kwd_url)");
                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                    }else {
                        $errMsg = 'Failed to Update Keyword';
                        $errType = 1;
                    }
                } else {
                    $errMsg = ' Already Exists.';
                    $errType = 1;
                }
            } else {
                $errMsg = 'Invalid Keyword.';
                $errType = 1;
            }
            $keyword=$new_keyword;
        }


        $data['kwd_url'] = $kwd_url;
        $data['sid'] = $sid;
        $data['keyword'] = $keyword;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = true;
        //$data['topMenuItems'] = array(array('href'=>'task=email&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Add New Skill Keyword';
        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_skills';
        $this->getTemplate()->display('skill_keyword_form', $data);
    }

    function actionUploadEmailImage()
    {
        $res = new stdClass();
        $res->result = false;
        $res->url = '';
        $res->msg = '';
        $allowed = array('png', 'jpg');
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $allowed)) {
                echo '{"status":"error"}';
                exit;
            } else {
                $ticket_id = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
                $mail_id = !empty($_REQUEST['mid']) ? $_REQUEST['mid'] : "";
                $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : "";
                $attachment_save_path = !empty($_REQUEST['attachment_save_path']) ? $_REQUEST['attachment_save_path'] : "";

                if ( !empty($type) || (!empty($ticket_id) && !empty($mail_id)) ) {
                    $data = $this->CreateSummerNoteImagePath($ticket_id, $mail_id, $_FILES, $type, $extension, $attachment_save_path);
                    if (!empty($data) && $data != "error-1" && is_array($data)){
                        $res->result = true;
                        $res->url = $this->getTemplate()->read_email_body_image."?".$data['return_path'];
                        $res->name = $data['name'];
                    } elseif (!empty($data) && $data == "error-1") {
                        $res->msg = 'Total Image Upload not more than 10MB';
                    }
                }
            }
        }

        echo json_encode($res);
        exit();
    }
    function CreateSummerNoteImagePath($ticket_id, $sl, $file, $type, $extension, $attachment_save_path=null, $debug=false){
        $ten_MB = 10;
        include('conf.email.php');
        $now = time();
        $sl +=1;
        $sl = sprintf('%03d', $sl);
        $yy = date("y", $now);
        $mm = date("m", $now);
        $dd = date("d", $now);
        // var_dump($attachment_save_path);

        $dir = $attachment_save_path.'/'.$yy.$mm.'/'.$dd.'/'.$ticket_id.'/'.$sl.'/';
        $dir = !empty($type) ? $create_email_image_save_path.'/'.$yy.$mm.'/'.$dd.'/' : $dir;
        // var_dump($dir);

        // if (!is_dir($dir)) mkdir($dir, 0750, true);
        if(isset($file['file']) && $file['file']['error'] == 0) {
            $fname = $file['file']['name'];
            $fname = !empty($type) ? $now.'.'.$extension : $file['file']['name'];
            $mid = str_replace('.','_', microtime(true));
            $fname = $mid."_".$fname;

            $current_file_size = $file['file']['size']/(1024*1024);
            
            //if (($this->measureFileSize($dir) + $current_file_size) < $ten_MB){
            if ($current_file_size < $ten_MB){
            	$url = $this->getTemplate()->upload_email_body_image; 
				if (function_exists('curl_file_create')) {
				  	$cFile = curl_file_create($file['file']['tmp_name']);
				} else {
				  	$cFile = '@' . realpath($file['file']['tmp_name']);
				}
				// var_dump($fname);
				// var_dump($dir);
				// var_dump($cFile);
				$post = array('new_filename' => $fname, 'dir' => $dir,'file_contents'=> $cFile);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
				$result=curl_exec($ch);				
				// var_dump($result);
				curl_close ($ch);
				$result = json_decode($result);
				// var_dump($result);
				// die();

                if ($result){
                	$return_path = "type=attachment&name=".$fname."&ym=".$yy.$mm.'&day='.$dd.'&tid='.$ticket_id."&sl=".$sl;
        			$return_path = !empty($type) ? "type=".$type."&name=".$fname."&ym=".$yy.$mm.'&day='.$dd : $return_path;
                    return ['return_path' => $return_path, 'name' => $fname];
                }
            }
            return "error-1";
        }
        return '';
    }

    function measureFileSize($ImagesDirectory) {
        $total_size = 0;
        if($dir = opendir($ImagesDirectory)) {
            while(($file = readdir($dir))!== false){
                $imagePath = $ImagesDirectory.$file;
                if (file_exists($imagePath)){
                    $KB = filesize ($imagePath)/1024;
                    $total_size += $KB;
                }
            }
            closedir($dir);
        }
        return $total_size;
    }

    function actionDeleteUploadedImage () {
        $response = false;
        $src = '';
        $img_src = !empty($_REQUEST['img_src']) ? urldecode($_REQUEST['img_src']) : "";
        $img_src = explode('?', $img_src);

        // GPrint($img_src);
        if(count($img_src)>1){
    		$url = $this->getTemplate()->delete_email_body_image.'?'.$img_src[1];
    		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			$result=curl_exec($ch);
			curl_close ($ch);
			if(!empty($result))
    			echo json_encode(true);
        }
        exit();
    }

    function actionChangeImageURL(){
        $html = '<div dir="ltr"><div><img src="cid:ii_jjgxt6eb0" alt="4.jpg" width="219" height="133" style="margin-right: 0px;"><br></div><div>srgeryuui</div><div>rtyi7ui</div><div><div><img src="cid:ii_jjgxthso1" alt="3.jpg" width="284" height="173" style="margin-right: 0px;"><br></div><br></div><br clear="all"><div><div dir="ltr" class="gmail_signature" data-smartmail="gmail_signature"><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div><div>==========<br>Ariful Hasan<br></div>Software En';
        /*//var_dump($email_body);die;
        preg_match_all('/<img[^>]+>/i',$html, $result);
        //preg_match_all( '@src="([^"]+)"@' , $html, $match );
        //var_dump($match);
        $save_path = 'src="content"';
        if (!empty($result)){
            foreach ($result[0] as &$key){

                preg_match_all('/(alt)=("[^"]*")/i',$key, $img);
                var_dump($img[0][0]);
                $alt = str_replace(array('"',"''","=","alt"), '', $img[0][0]);
                //GPrint($alt);
//                preg_match_all('/alt="[^"]*"/', $img[0][0], $alt);
//                var_dump($alt);
                $update_img = preg_replace('@src="([^"]+)"@', $save_path.'/'.$alt, $key);
                var_dump($update_img);
                $update_html = preg_replace('/<img[^>]+>/i', $update_img, $html);
            }
        }
        var_dump($update_html);*/



        preg_match_all('/src="cid:(.*)"/Uims', $html, $matches);
        preg_match_all('/alt="(.*)"/Uims', $html, $altrs);
        $save_path = "content";
        if(count($matches)) {

            $search = array();
            $replace = array();
            foreach($matches[1] as $key => $match) {
                $uniqueFilename = $altrs[1][$key];
                //file_put_contents("/path/to/images/$uniqueFilename", $emailMessage->attachments[$match]['data']);
                $search[] = "src=\"cid:$match\"";
                $replace[] = "src=\"$save_path/images/$uniqueFilename\"";
            }
            $html = str_replace($search, $replace, $html);
            //var_dump($html);
        }
    }

    function actionSettings()
    {
        include_once('model/MCcSettings.php');
        include_once('config/constant.php');
        include_once('model/MSkill.php');
        $skill_model = new MSkill();
        $settings_model = new MCcSettings();
        $settings_model->module_type = MOD_EMAIL;

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $dispWindow = '';
        $data['chat_dispos_old'] = '';
        if ($request->isPost()) {
            $post_data['attachment_save_path'] = isset($_POST['attachment_save_path']) ? trim($_POST['attachment_save_path']) : '';
            $post_data['replace_text_pattern'] = isset($_POST['replace_text_pattern']) ? trim($_POST['replace_text_pattern']) : '';
            $post_data['form_name'] = isset($_POST['form_name']) ? trim($_POST['form_name']) : '';
            $post_data['form_email'] = isset($_POST['form_email']) ? trim($_POST['form_email']) : '';
            $post_data['smtp_host'] = isset($_POST['smtp_host']) ? trim($_POST['smtp_host']) : '';
            $post_data['smtp_port'] = isset($_POST['smtp_port']) ? trim($_POST['smtp_port']) : '';
            $post_data['smtp_username'] = isset($_POST['smtp_username']) ? trim($_POST['smtp_username']) : '';
            $post_data['smtp_password'] = isset($_POST['smtp_password']) ? trim($_POST['smtp_password']) : '';
            $post_data['smtp_secure_opton'] = isset($_POST['smtp_secure_opton']) ? trim($_POST['smtp_secure_opton']) : '';
            $post_data['viewable_priority_skill'] = isset($_POST['viewable_priority_skill']) ? implode(",", $_POST['viewable_priority_skill']) : '';
            $post_data['priority_email'] = isset($_POST['priority_email']) ? $this->getValidEmail($_POST['priority_email']) : '';
            $post_data['priority_email_skill'] = isset($_POST['priority_email_skill']) ? trim($_POST['priority_email_skill']) : '';
            $response = $settings_model->saveData($post_data);

            if($response){
                $errMsg = 'Email settings updated successfully !!';
                $errType = 0;
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=".$this->getRequest()->getActionName());
                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
            }else{
                $errType = 1;
                $errMsg = 'No change found to update !!';
            }
        } else {
            $settings_data = $settings_model->getFormatAllSettings();
//            GPrint(base64_decode($settings_data['attachment_save_path']->value));die;
        }

        if (UserAuth::hasRole('admin')) {
            $data['skills'] = $skill_model->getSkills('Y', 'E', 0, 100);
        } else {
            $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
        }

        $data['settings_data'] = $settings_data;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['pageTitle'] = 'Global Email Settings';
        //GPrint($data['skills']);die;

        $data['side_menu_index'] = 'email';
        $data['smi_selection'] = 'email_settings';
        $this->getTemplate()->display('email_settings_form', $data);
    }

    function actionViewAttachment(){
        $num_mails = !empty($_REQUEST['sl']) ? $_REQUEST['sl'] : "";
        $tid = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        $data['topMenuItems'] = array(
            array('href'=>'task=email', 'img'=>'fa fa-envelope', 'label'=>'All Emails')
        );

        $data['pageTitle'] = 'Attachment List';
        $data['dataUrl'] = $this->url('task=get-email-data&act=view-attachment&tid='.$tid.'&sl='.$num_mails);
        $this->getTemplate()->display('view_attachment', $data);
        //$this->getTemplate()->display_popup('view_attachment', $data);
    }

    function actionAllAttachment() {
        $data['pageTitle'] = 'All Attachment';
        $data['dataUrl'] = $this->url('task=get-email-data&act=all-attachment&tid=');
        //$data['topMenuItems'] = array(array('href'=>'task=email&status=S&newall=Y', 'img'=>'email_open.png', 'label'=>'Served Emails'));
        $data['side_menu_index'] = 'ticketmng';
        $data['smi_selection'] = 'email_settings';
        $this->getTemplate()->display('all_attachment', $data);
    }

    function actionAttachmentList(){
        $cid = !empty($_REQUEST['cid']) ? $_REQUEST['cid'] : "";
        $data['pageTitle'] = 'Attachment List [ Customer ID : '.$cid." ]";
        $data['dataUrl'] = $this->url('task=get-email-data&act=attachment-list&cid='.$cid);

        $data['side_menu_index'] = 'ticketmng';
        $data['smi_selection'] = 'email_settings';
        $this->getTemplate()->display('attachment_list', $data);
    }

    function actionEmailReportOld () {
        include('model/MEmail.php');
//        include('model/MAgent.php');
        $eTicket_model = new MEmail();
//        $agent_model = new MAgent();
//        $agent_options = $agent_model->get_as_key_value();
        $data['skill_list'] = array();
        if (UserAuth::hasRole('supervisor')){
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents(UserAuth::getCurrentUser());
            $skill_list = $eTicket_model->getEmailSkill(UserAuth::getCurrentUser());
        } else {
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents();
            $skill_list = $eTicket_model->getEmailSkill();
        }
        if (!empty($skill_list)){
            foreach ($skill_list as $key){
                $data['skill_list'][$key->skill_id] = $key->skill_name;
            }
        }
        $data['skill_list'] = array("*"=>"All")+$data['skill_list'];
        //GPrint($data['skill_list']);die;
        $ticket_category = $eTicket_model->getTicketCategory('','A');
        $data['ticket_category'] = array();
        if (!empty($ticket_category)){
            foreach ($ticket_category as $cat)
                $data['ticket_category'][$cat->category_id] = $cat->title;
        }

        $data['in_kpi_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $data['rs_tr_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $agent_id = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';

        $isDateToday = true;
        $data['todaysDate'] = "";
        //$data['pageTitle'] = 'Email Tickets';
        $data['pageTitle'] = 'Emails Report';
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
        if ($isDateToday){
            $data['todaysDate'] = date("Y-m-d")." 23:59";
        }

        //GPrint(date("Y-m-d", strtotime("-1 month")));die;
        $data['source_name'] = array("*"=>"Select", "C"=>"CRM", "E"=>"Email", "M"=>"Manual");
        $data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = $status;
        $data['agent_id'] = $agent_id;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
        $selectOpt = array('*'=>'Select');
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-report-old'.$urlParam);
        $data['ticket_category'] = array_merge($selectOpt,$data['ticket_category']);
        //GPrint($data['ticket_category']);die;
        //$data['side_menu_index'] = 'email';
        $data['side_menu_index'] = 'ticketmng';
//        $data['topMenuItems'] = array(array('href'=>'task=email&status=S&newall=Y', 'img'=>'email_open.png', 'label'=>'Served Emails'));
        $this->getTemplate()->display('email_report_old', $data);
    }

    function actionManuallyWorkOnEmail(){
        $tid = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        $obj = new stdClass();
        $obj->response = false;
        $obj->msg = '';
        $log_data[] = "Email ID : $tid";

        if (!empty($tid)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $result = $eTicket_model->isDistributed($tid);
            if (!empty($result) && $result->acd_status == 'N') {
                if ($eTicket_model->deleteEmailDistribution($tid)) {
                    //$obj->response = 'success';
                }
            } else {
                //$obj->msg = 'You can not work on it.';
            }
            $ticket_info = $eTicket_model->getETicketById($tid);
            $current_user = $ticket_info->current_user;
            $count_mins = (time() - $ticket_info->last_update_time)/60;
            $log_data[] = "Current User : $current_user";
            if (empty($current_user) || ($count_mins > $this->last_update_difference_time)){
                $obj->response = true;
                $obj->msg = 'success';
                $eTicket_model->addETicketActivity($tid, UserAuth::getCurrentUser(), 'P','');
                if (FIRST_OPEN_TIME_SET_FROM_FIRST_PULL) {
                    $log_data[] = "Set First Open Time : TRUE";
                    $set_result = $eTicket_model->updateFirstOpenTimeForLogEmailSession($ticket_info, UserAuth::getCurrentUser());
                    $log_data[] = "First Open Time Set Result : $set_result";
                }
            }
        }
        log_text($log_data, "temp/email_log/");
        echo json_encode($obj);
        exit();
    }
    function actionisEmailTyping() {
        $res = '';
        $tid = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        if (!empty($tid)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $res = $eTicket_model->isTyping($tid);
        }
        echo json_encode($res);
        exit;
    }
    function actionEmptyCurrentUser() {
        $response = '';
        $tid = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        if (!empty($tid)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $response = $eTicket_model->emptyCurrentUser($tid);
        }

        echo json_encode($response);
        exit();
    }

    function actionSkill() {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        //$data['callid'] = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $data['skills'] = null;
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {
                $data['pageTitle'] = 'Update skill of email :: '  . $eTicketInfo->ticket_id;
                if (isset($_POST['submitagent'])) {

                    $skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
                    if ( $eTicketInfo->skill_id !=  $skill) {
                        $isAllowed = true;
                        if (empty($skill)) {
                            $isAllowed = false;
                        }

                        if ($isAllowed) {
                            $is_update = $eTicket_model->setEmailSkill($ticketid, $skill, UserAuth::getCurrentUser());
                            if ($is_update) {
                                $eTicket_model->updateLogEmailSessionTransfer($ticketid, $skill);
//                                $distribution_res = $eTicket_model->isDistributed($ticketid);
//                                if (!empty($distribution_res->status) && $distribution_res->status=='R') {
                                $eTicket_model->setDistributionSkill($ticketid, $skill,'', 'S',$eTicketInfo);//// S = skill;
//                                }
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
                //GPrint($data['skills']);die;
            } else {
                $fatalErr = 'Invalid email!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Update Skill of email';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Update skill of email';
        $this->getTemplate()->display_popup('email_set_skill', $data);
    }

    function actionEmailModuleUdpMsg(){
        include_once('conf.email.php');
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $_SESSION['msg_srv_ip'] = !empty($_SESSION['msg_srv_ip']) ? $_SESSION['msg_srv_ip'] : $eTicket_model->getDBIPSettings();
        $msg_srv_ip = $_SESSION['msg_srv_ip'];

        $response = '';
        $msg_res['method'] = !empty($_REQUEST['method']) ? $_REQUEST['method'] : "";
        $msg_res['email_id'] = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : "";
        $msg_res['call_id'] = !empty($_REQUEST['call_id']) ? $_REQUEST['call_id'] : "";
        $agentid = !empty($_REQUEST['agent_id']) ? $_REQUEST['agent_id'] : "";
        //$skip = !empty($_REQUEST['skip']) ? $_REQUEST['skip'] : "";
        $evt_type = !empty($_REQUEST['evt_type']) ? $_REQUEST['evt_type'] : "";
        $msg = json_encode($msg_res);
        $len = strlen($msg);

        if (!empty($msg_res['call_id'])) {
            $msg_srv_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_sendto($sock, $msg, $len, 0, $msg_srv_ip, $msg_srv_port);
            socket_close($sock);
            if ($evt_type == 'skip'){
                $eTicket_model->updateSkipStatusOfEticket($agentid, $msg_res['email_id']);
                $eTicket_model->addETicketActivity($msg_res['email_id'], $agentid, 'I', $msg_res['call_id']);
            }
            if ($evt_type == 'close'){
                $eTicket_model->EmptyCurrentUser($msg_res['email_id']);
            }
        }
        $response = empty($evt_type) ? $eTicket_model->updateETicketInfo($agentid, $msg_res['email_id']) : $evt_type;

        echo json_encode($response);
        exit();
    }

    function actionTransfer() {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['callid'] = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $data['skills'] = null;
        $data['agents'] = null;
        $data['trns_val'] = "S";
        $data['redirectURL'] = '';
        $eTicketInfo = null;
        $errMsg = '';
        $fatalErr = '';
        $role = UserAuth::getRole();
        $isAllowedToChange = $eTicket_model->isAllowedToChangeTicket($ticketid, UserAuth::getCurrentUser(), $role);

        if ($isAllowedToChange) {
            $eTicketInfo = $eTicket_model->getETicketById($ticketid);

            if (!empty($eTicketInfo)) {

                $time_difference = round((time() - $eTicketInfo->last_update_time)/60);
                $isTransferAble = empty($eTicketInfo->current_user) || ($time_difference > $this->last_update_difference_time) ? true : false;

                if ( $isTransferAble ){
                    $data['pageTitle'] = 'Transfer of email :: '  . $eTicketInfo->ticket_id;
                    $transfer_val = isset($_REQUEST['transfer']) ? trim($_REQUEST['transfer']) : '';

                    if (isset($_POST['submitagent']) && !empty($transfer_val)) {
                        $data['trns_val'] = $transfer_val;
                        $skill = isset($_REQUEST['skill']) ? trim($_REQUEST['skill']) : '';
                        $aid = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';

                        if ($transfer_val == 'S'){
                            if ( $eTicketInfo->skill_id !=  $skill) {
                                $isAllowed = true;
                                if (empty($skill)) {
                                    $isAllowed = false;
                                }
                                if ($isAllowed) {
                                    $is_update = $eTicket_model->setEmailSkill($ticketid, $skill, UserAuth::getCurrentUser());
                                    if ($is_update) {
                                        $eTicket_model->updateLogEmailSessionTransfer($ticketid, $skill);

                                        $eTicket_model->setDistributionSkill($ticketid, $skill,'', 'S', $eTicketInfo);//// S = skill;
                                        $eTicket_model->addToAuditLog('Skill Transfered', 'U', "ticket_id", "$ticketid");
                                        $data['message'] = 'Skill successfully transfered.';
                                        $data['msgType'] = 'success';
                                        $data['redirectURL'] = $this->getTemplate()->url("task=email&act=init");
                                        $this->getTemplate()->display_popup('popup_message', $data);
                                    } else {
                                        $errMsg = 'Failed to transfer skill!!';
                                    }
                                }else {
                                    $errMsg = 'Provide valid skill!!';
                                }
                            } else {
                                $errMsg = 'No change found!!';
                            }
                        } else {
                            if ($eTicketInfo->assigned_to != $aid) {
                                $isAllowed = true;
                                if (!empty($aid)) {
                                    $result = $eTicket_model->getSkillAgents($eTicketInfo->skill_id, $aid);
                                    if (empty($result)) $isAllowed = false;
                                }
                                if ($isAllowed) {
                                    $acd_status = $eTicket_model->isDistributed($ticketid);
                                    //if (empty($acd_status) || (!empty($acd_status) && $acd_status->acd_status == 'N')) {
                                    $is_update = $eTicket_model->assignAgent($ticketid, $aid, UserAuth::getCurrentUser());
                                    if ($is_update) {
                                        if (!empty($acd_status)) $eTicket_model->deleteEmailDistribution($ticketid);
                                        $eTicket_model->updateLogEmailSessionTransfer($ticketid,'', $aid);

                                        $eTicket_model->addToAuditLog('Agent Transfered', 'U', "ticket_id", "$ticketid");
                                        $data['message'] = 'Agent successfully transfered.';
                                        $data['msgType'] = 'success';
                                        $data['redirectURL'] = $this->getTemplate()->url("task=email&act=init");
                                        $this->getTemplate()->display_popup('popup_message', $data);
                                    } else {
                                        $errMsg = 'Failed to transfer agent!!';
                                    }
                                    /*} else {
                                        $errMsg = 'Already Transfered!!';
                                    }*/
                                } else {
                                    $errMsg = 'Privilege error!!';
                                }
                            } else {
                                $errMsg = 'No change found!!';
                            }
                        }
                    }

                    $data['skills'] = UserAuth::getCurrentUser()=='root' ? $eTicket_model->getEmailSkill() : $eTicket_model->getEmailSkill(UserAuth::getCurrentUser());
                    $data['agents'] = $eTicket_model->getSkillAgents($eTicketInfo->skill_id);
                } else {
                    $fatalErr = "User ( $eTicketInfo->current_user ) Working on it.";
                }
            } else {
                $fatalErr = 'Invalid Email!!';
            }
        } else {
            $fatalErr = 'Privilege error!!';
        }
        if (!empty($fatalErr)) {
            $data['pageTitle'] = 'Transfer of Email';
            $data['message'] = $fatalErr;
            $data['msgType'] = 'error';
            $this->getTemplate()->display_popup('popup_message', $data);
        }

        $data['request'] = $this->getRequest();
        $data['tid'] = $ticketid;
        $data['errMsg'] = $errMsg;
        $data['ticket_info'] = $eTicketInfo;
        $data['eTicket_model'] = $eTicket_model;
        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Transfer of Email';
        $this->getTemplate()->display_popup('email_transfer', $data);
    }

    function validateEmail($string){
//        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
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

    function actionGetAttachment(){
        $ticket_id = !empty($_REQUEST['ticket_id']) ? $_REQUEST['ticket_id'] : "";
        $mail_sl = !empty($_REQUEST['mail_sl']) ? $_REQUEST['mail_sl'] : "";
        $data = '';
        if (!empty($ticket_id) && !empty($mail_sl)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $result = $eTicket_model->getAttachments($ticket_id, $mail_sl, true);
            if (is_array($result)){
                $yy = date("y", $result['tstamp']);
                $mm = date("m", $result['tstamp']);
                $dd = date("d",$result['tstamp']);
                $forwarded_attachment_path = '';
                if (!empty($result['data'])){
                    $list = &$result['data'];
                    foreach ($list as &$key){
                        $key->file_path =  $yy . $mm . '/' . $dd . '/' . $key->ticket_id . '/' . $key->mail_sl . '/'.$key->file_name ;
                        $forwarded_attachment_path =  $yy . $mm . '/' . $dd . '/' . $key->ticket_id . '/' . $key->mail_sl . '/';
                    }
                }
                $data['forwarded_attachment_path']  = $forwarded_attachment_path;
                $data['data']  = $list;
            }
        }
        echo json_encode($data);
        exit();
    }

    function actionGetEmailText() {
        $ticket_id = !empty($_REQUEST['ticket_id']) ? $_REQUEST['ticket_id'] : '';
        $mail_sl = !empty($_REQUEST['mail_sl']) ? $_REQUEST['mail_sl'] : '';
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        if (!empty($ticket_id) && !empty($mail_sl)){
            $detais = $eTicket_model->getLastEmail($ticket_id, $mail_sl);

            $email_datetime = !empty($detais->tstamp) ? 'On '.date('D, M j, Y', $detais->tstamp).' at '.date('h:m A ', $detais->tstamp). htmlspecialchars($detais->mail_from) . " wrote:</b>" : '';
            $email_body = !empty($detais->mail_body) ? $email_datetime.'<br/>'.nl2br(base64_decode($detais->mail_body)) : '';

            $email_body = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$email_body);            
            $email_body = addslashes($email_body);
            preg_match_all('/<img .*?>/',$email_body, $matches);
            $search = [];
            $replace = [];
            foreach ($matches[0] as $key => $item) {
            	$search[] = $item;
            	$replace[] = stripslashes($item);
            }
            $email_body = str_replace($search, $replace, $email_body);
            echo json_encode($email_body);
            exit();
        }
        return '';
    }

    function actionUpdateLogEmailActivity(){
        $ticket_id = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : '';
        $skip = !empty($_REQUEST['skip']) ? $_REQUEST['skip'] : '';
        $close = !empty($_REQUEST['close']) ? $_REQUEST['close'] : '';
        $response = null;
        //dd($close);
        if (!empty($ticket_id)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            if (!empty($skip)){
                $response = $eTicket_model->updateLogEmailActivity($ticket_id, '','', UserAuth::getCurrentUser(), '', 'Y', '',time(), '', UserAuth::getCurrentUser());
            } elseif (!empty($close)) {
                $response = $eTicket_model->updateLogEmailActivity($ticket_id, '','', UserAuth::getCurrentUser(), '', '', time(), '', '', UserAuth::getCurrentUser());
            }
        }
        echo json_encode($response);
        exit();
    }


    function actionEmailReport(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['skill_list'] = array();
        if (UserAuth::hasRole('supervisor')){
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents(UserAuth::getCurrentUser());
            $skill_list = $eTicket_model->getEmailSkill(UserAuth::getCurrentUser());
        } elseif(UserAuth::hasRole('admin')) {
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents();
            $skill_list = $eTicket_model->getEmailSkill();
        }
        if (!empty($skill_list)){
            foreach ($skill_list as $key){
                $data['skill_list'][$key->skill_id] = $key->skill_name;
            }
        }
        $data['skill_list'] = array("*"=>"All")+$data['skill_list'];

        $data['in_kpi_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $data['rs_tr_list'] = array('*'=>'All','Y'=>'Yes','N'=>'No');
        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $agent_id = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';

        $isDateToday = true;
        $data['todaysDate'] = "";
        $data['pageTitle'] = 'Emails Report';
        $data['smi_selection'] = 'email_init';

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
        if ($isDateToday){
            $data['todaysDate'] = date("Y-m-d")." 23:59";
        }

        $data['report_date_format'] = get_report_date_format();
        $data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = $status;
        $data['agent_id'] = $agent_id;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
        $selectOpt = array('*'=>'Select');
        //$data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionChildrenOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-report'.$urlParam);
        //GPrint($data['ticket_category']);die;
        $data['side_menu_index'] = 'ticketmng';
//        $data['topMenuItems'] = array(array('href'=>'task=email&status=S&newall=Y', 'img'=>'email_open.png', 'label'=>'Served Emails'));
        $this->getTemplate()->display('email_report', $data);
    }

    function actionIsNewEmailArrive(){
        $ticket_id = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : '';
        $old_mail_sl = !empty($_REQUEST['mail_sl']) ? (int)$_REQUEST['mail_sl'] : '';
        $response = 0;

        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $new_email_sl = !empty($ticket_id) ? $eTicket_model->isNewEmailArrive($ticket_id) : 0;
        $response = $new_email_sl;

        echo json_encode($response);
        exit();
    }

    function actionSetSessionMsg() {
        $ticket_id = !empty($_REQUEST['email_id']) ? $_REQUEST['email_id'] : '';
        $condition = !empty($_REQUEST['condition']) ? $_REQUEST['condition'] : '';
        $msg = !empty($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
        $last_email_body = !empty($_REQUEST['last_email_body']) ? $_REQUEST['last_email_body'] : '';

        unset($_SESSION['current_msg']);
        if ($condition == "L"){
            $user_text = explode("---------- Previous Message -----------", $msg);
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            $new_email_body = $eTicket_model->getLastEmail($ticket_id);
            $new_email_body = !empty($new_email_body) ? base64_decode($new_email_body) : "";
            $text = $user_text[0]."<br/><br/>---------- Previous Message -----------".$new_email_body;
            $_SESSION['current_msg'] = $text;
        }
        if ($condition == "C") {
            $_SESSION['current_msg'] = $msg;
        }
        echo json_encode(true);
        exit();
    }


    /*
     * RND Agent Dashboard
     * using ws
     * */
    function actionEmailAgentDashboard(){
        include('model/MSkill.php');
        $skill_model = new MSkill();
        $result = $skill_model->getSkills('', 'E', 0, 100);
        $skills = [];
        if (!empty($result)) {
            foreach ($result as $key)
                $skills[$key->skill_id] = $key->skill_name;
        }

        $data['ip'] = email_realtime_dashboard_host;
        $data['port'] = email_realtime_dashboard_port;
        $data['skills'] = $skills;
        $data['pageTitle'] = 'Email Agent Dashboard';
        $data['suffix'] = strtolower(UserAuth::getDBSuffix());
        $this->getTemplate()->display_only('email_agent_dashboard', $data);
    }

    public function actionSkillInkpi()
    {
        include('model/MEmail.php');
        $skill_id = $this->getRequest()->_request['sid'];
        $old_inkpi = $this->getRequest()->_request['old_inkpi'];
        $is_update = $old_inkpi ? true : false;
        $errMsg = '';
        $errType = 1;

        if ($skill_id) {
            if ($this->getRequest()->isPost()){
                $inkpi = !empty($_REQUEST['skill_inkpi_time']) && ctype_digit($_REQUEST['skill_inkpi_time']) && $_REQUEST['skill_inkpi_time'] > 0 ? $_REQUEST['skill_inkpi_time'] : '';
                if (($old_inkpi != $inkpi) && !empty($skill_id)) {
                    if (MEmail::updateInkpiBySkillId($inkpi, $skill_id, $is_update)) {
                        $errMsg = 'Successfully Updated.';
                        $errType = 0;
                        $url = $this->url("task=".$this->getRequest()->getControllerName()."&act=skills");
                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                    } else {
                        $errMsg = 'Failed to Update!';
                    }
                }
            }
            $inkpi = MEmail::getInKpiBySkillId($skill_id)->in_kpi;
        } else {
            $errMsg = 'Invalid Information!';
        }

        $data['inkpi'] = $inkpi;
        $data['sid'] = $skill_id;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Email Skill In Kpi Settings';
        $this->getTemplate()->display('email/email_skill_inkpi', $data);
    }

    /*
     *log javascript error
     *for all emails
     */
    public function actionLogJavascriptError () {
        $contents = PHP_EOL."*****************".date("Y-m-d H:i:s")."****************".PHP_EOL;
        $contents .= "MSG : ".$_REQUEST['msg'].PHP_EOL;
        $contents .= "URL : ".$_REQUEST['url'].PHP_EOL;
        $contents .= "LINE : ".$_REQUEST['line'].PHP_EOL;
        $contents .= "COLUMN : ".$_REQUEST['column'].PHP_EOL;
        $contents .= "STACK : ".$_REQUEST['error'].PHP_EOL;
        file_put_contents("/usr/local/ccpro/email/log/temp/EMAIL_JS_ERROR.log", $contents, FILE_APPEND | LOCK_EX);
        return null;
    }

}
