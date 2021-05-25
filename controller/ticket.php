<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/2/2018
 * Time: 12:49 PM
 */

class Ticket extends Controller
{
    function __construct(){
        parent::__construct();

        $licenseInfo = UserAuth::getLicenseSettings();
        if ($licenseInfo->email_module == 'N') {
            header("Location: ./index.php");
            exit;
        }
    }

    function actionCreate() {
        include('model/MSkill.php');
        include('model/MTicket.php');
        include('model/MEmail.php');
        $skill_model = new MSkill();
        $eTicket_model = new MTicket();
        $email_model = new MEmail();

        $err = '';
        $errType = 1;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $ticket_body = isset($_POST['mail_body']) ? trim($_POST['mail_body']) : '';//mail_body
        $skill_id = isset($_POST['skill_id']) ? trim($_POST['skill_id']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $created_for = isset($_POST['created_for']) ? trim($_POST['created_for']) : '';
        $category_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
        $account_id = isset($_POST['account_id']) ? trim($_POST['account_id']) : '';
        $did = '';
        for ($i=0;$i<=10; $i++) {
            $did1 = isset($_POST['disposition_id'.$i]) ? trim($_POST['disposition_id'.$i]) : '';
            if (!empty($did1)) $did = $did1;
            else break;
        }

        $data['ticket_category'] = array();
        $ticket_category = $eTicket_model->getTicketCategory('','A');
        if (!empty($ticket_category)){
            foreach ($ticket_category as $item){
                $data['ticket_category'][$item->category_id] = $item->title;
            }
        }
        $data['changable_status'] = $eTicket_model->getChangableTicketStatus('O');
        $data['skills'] = $skill_model->getSkills('', '', 0, 100);

        if ( !empty($category_id) ) {
            $err = $this->validateCreateTicket($skill_id, $did, $ticket_body, $name, $account_id, $category_id, $status, $created_for);
            if (empty($err)) {
                $is_success = false;
                //$eTicket_model->createNewEmail(UserAuth::getCurrentUser(), $skill_id, $did, $ticket_body, $name, $account_id, $category_id, $status, $created_for, 'M');
                    include_once('model/MCrmIn.php');
                    $crm_model = new MCrmIn();
                    if ($crm_model->openCrmTicket($skill_id, $created_for, $did, $ticket_body, $name, $account_id, $category_id, $status,'M')){
                        $err = 'New ticket created successfully !!';
                        $is_success = true;
                    } else {
                        $err = 'Failed to create new ticket !!';
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

        include_once('conf.email.php');
        $data['replace_text_pattern'] =  $replace_text_pattern ;
        $data['attachment_save_path'] = $ticket_attachment_save_path ;
        $data['disposition_ids'] = $email_model->getDispositionPathArray('');
        $data['email_model'] = $eTicket_model;
        $data['name'] = $name;
        $data['title'] = $title;
        $data['ticket_body'] = $ticket_body;
        $data['skill_id'] = $skill_id;
        $data['status'] = $status;
        $data['errMsg'] = $err;
        $data['errType'] = $errType;
        $data['category_id'] = $category_id;
        $data['account_id'] = $account_id;
        $data['side_menu_index'] = 'ticketmng';
        $data['smi_selection'] = 'ticket_create';
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Create Ticket';
        $this->getTemplate()->display('ticket_create', $data);
    }

    function actionTemplates(){
        include('model/MTicket.php');
        $et_model = new MTicket();

        $data['sid'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $dispositionid = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $data['emails'] = $et_model->getTicketTemplateOptions($dispositionid, 'Y');
        $data['pageTitle'] = 'Select Template';
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode($data);exit;
        }
        $this->getTemplate()->display_popup('email_template_select', $data, false, false);
    }

    function validateCreateTicket($skill_id, $disposition, $subject, $name, $account_id, $category_id,$status, $created_for){
        if (empty($skill_id)) return 'Skill is required';
        if (empty($disposition)) return 'Disposition is required';
//        if (empty($subject)) return 'Text is required';
//        if (!empty($subject) && strlen($subject) > 256) return 'Text not more than 256 char';
        if (empty($name)) return 'Customer  Name is required';
        if (empty($account_id)) return 'Acoount ID is required';
        if (empty($category_id)) return 'Category is required';
        if (empty($status)) return 'Status is required';
        if (empty($created_for)) return 'Mobile No. is required';
        return '';
    }

    function init() {
        //New dsign start here
        include('model/MEmail.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmail();
        $agent_model = new MAgent();

        $agent_options = $agent_model->get_as_key_value_for_ticket();
        $data['agent_list'] = $agent_options;
        $ticket_category = $eTicket_model->getTicketCategory('','A');
        $data['ticket_category'] = array();
        if (!empty($ticket_category)){
            foreach ($ticket_category as $cat)
                $data['ticket_category'][$cat->category_id] = $cat->title;
        }

        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

        $isDateToday = true;
        $data['todaysDate'] = "";
        $data['pageTitle'] = 'All Tickets';
        $data['smi_selection'] = 'email_init';
        if ($etype == 'myjob') {
            $data['pageTitle'] = 'My Tickets';
            $data['smi_selection'] = 'ticket_init_myjob';
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

        $data['source_name'] = array("*"=>"Select", "C"=>"CRM", "E"=>"Email", "M"=>"Manual");
        $data['status'] = $status;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
        $selectOpt = array('*'=>'Select');
        $data['did_options'] = array_merge ( $selectOpt, $eTicket_model->getDispositionTreeOptions());
        $data['status_options'] = array_merge ( $selectOpt, $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-ticket-data&act=ticketinit'.$urlParam);
        $data['ticket_category'] = array_merge($selectOpt,$data['ticket_category']);
        $data['side_menu_index'] = 'ticketmng';

        $data['smi_selection'] = !empty($etype) ? 'ticket_init_myjob' : 'ticket_init';
        $data['topMenuItems'] = array(array('href'=>'task=email&status=S&newall=Y', 'img'=>'email_open.png', 'label'=>'Served Tickets'));
        $this->getTemplate()->display('ticket', $data);
    }

    function actionDetails() {
        include_once('conf.email.php');
        include('model/MEmail.php');
        include('model/MTicket.php');
        $eTicket_model = new MTicket();

        $ticketid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
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
        $data['source_name'] = array("C"=>"CRM","E"=>"Email","M"=>"Manual");
        $data['sublink'] = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=details&tid=".$ticketid);

        if ($this->getRequest()->isPost() && $isAllowedToChange) {
            //dd($_FILES);
            if ($_POST['message'] != ""){
                $eTicketInfo = $eTicket_model->getETicketById($ticketid);
                $upResult = $eTicket_model->addTicketMsg($eTicketInfo, $_POST['message'], $_FILES['att_file'],  UserAuth::getCurrentUser(), $_POST['st']);
                if ($upResult){
//                    $st = isset($_POST['st']) ? trim($_POST['st']) : '';
//                    if (strlen($st) == 1) $eTicket_model->updateTicketStatus($ticketid, $st, UserAuth::getCurrentUser());
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $this->getTemplate()->display('msg', array('pageTitle'=>'Ticket Details', 'isError'=>false, 'msg'=>'Message successfully sent', 'redirectUri'=>$url));
                }
            }
        }

        $data['eTicket_model'] = new MEmail();
        $data['mainobj'] = new MTicket();
        $data['update_privilege'] = $isAllowedToChange;
        $data['eTickets'] = "";
        $data['signature'] = null;
        $data['eTicketEmail'] = "";
        $data['agent_id'] = UserAuth::getCurrentUser();
        $data['subject'] = "";

        if ($ticketid != ''){
            $data['eTickets'] = $eTicket_model->getETicketById($ticketid);
            if (!empty($data['eTickets'])){
                $data['subject'] = $eTicket_model->getEmailDispositionByTicketId($data['eTickets']->ticket_id);
            }
            $data['eTicketEmail'] = $eTicket_model->getTicketEmails($ticketid);
            $data['status_options'] =  $eTicket_model->getTicketStatusOptions(true);

            if (!empty($data['eTickets'])) {
                $data['disposition'] = $eTicket_model->getDispositionById($data['eTickets']->disposition_id, $data['eTickets']->skill_id);
                if (!empty($data['eTickets']->assigned_to)) {
                    include('model/MAgent.php');
                    $agent_model = new MAgent();
                    $data['assigned_to'] = $agent_model->getAgentById($data['eTickets']->assigned_to);
                }
                $data['signature'] = "";//$eTicket_model->getEmailSignature($data['eTickets']->skill_id);
                $eTicket_model->addETicketActivity($ticketid, UserAuth::getCurrentUser(), 'V');
            }
        }

        $data['msg_pattern'] = isset($replace_text_pattern) ? $replace_text_pattern : "";
        $data['side_menu_index'] = 'ticketmng';
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Ticket Details';
        $data['smi_selection'] = 'email_init';
        $this->getTemplate()->display('ticket_details', $data);
    }
    function actionAttachment(){
        include('model/MTicket.php');
        $eTicket_model = new MTicket();
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

            $file = $ticket_attachment_save_path . '/' . $yy . $mm . '/' . $dd . '/' . $ticketid . '/' . $sl . '/' . $attachment->file_name;
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

    function actionStatus(){
        include('model/MTicket.php');
        $eTicket_model = new MTicket();
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
        $this->getTemplate()->display_popup('email_set_status', $data);
    }

    function actionAssign(){
        include('model/MTicket.php');
        $eTicket_model = new MTicket();
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
                            $is_update = $eTicket_model->assignAgent($ticketid, $aid, UserAuth::getCurrentUser());

                            if ($is_update) {
                                $data['message'] = 'Agent assigned successfully !!';
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
                //var_dump($data['agents']);
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
        include('model/MTicket.php');
        include('model/MEmail.php');
        $eTicket_model = new MTicket();
        $email_model = new MEmail();

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
                $data['dispositions0'] = $email_model->getDispositionChildrenOptions($eTicketInfo->skill_id, '');//;$eTicket_model->getDispositionTreeOptions($eTicketInfo->skill_id);
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

        $data['disposition_ids'] = $email_model->getDispositionPathArray($eTicketInfo->disposition_id);
        $data['eTicket_model'] = $eTicket_model;

        if (!isset($data['pageTitle'])) $data['pageTitle'] = 'Set disposition to ticket';
        $this->getTemplate()->display_popup('email_set_disposition', $data);
    }

    function actionTicketCategory(){
        include('model/MTicket.php');
        $eTicket_model = new MTicket();
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

    function actionDashboard(){
        include('model/MTicket.php');
        $email_model = new MTicket();
        $data['source'] = "";

        if ($this->getRequest()->isPost() && !empty($_POST['source'])){
            $data['source'] = $_POST['source'];
            $data['myjob'] = $email_model->getMyJobSummary(UserAuth::getCurrentUser(), $data['source']);
            if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
                $data['recentjob'] = $email_model->getRecentJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'), $data['source']);
                $data['alljob'] = $email_model->getJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'),$data['source']);
            } else {
                $data['recentjob'] = null;
                $data['alljob'] = null;
            }
        }else {
            $data['myjob'] = $email_model->getMyJobSummary(UserAuth::getCurrentUser());
            if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
                $data['recentjob'] = $email_model->getRecentJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
                $data['alljob'] = $email_model->getJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
            } else {
                $data['recentjob'] = null;
                $data['alljob'] = null;
            }
        }

        $data['side_menu_index'] = 'ticketmng';
        $data['smi_selection'] = 'ticket_dashboard';
        $data['source_option'] = array("E"=>"Email", "C"=>"CRM");
        $data['pageTitle'] = 'Ticketing Dashboard';
        $this->getTemplate()->display('ticket_dashboard', $data);
    }

    function actionTicketCategoryList(){

        include('model/MTicket.php');
        $eTicket_model = new MTicket();

        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $data['pageTitle'] = 'Ticket Category List';

        if (!empty($status)){
            $urlParam .= "&status=".$status;
            //$isDateToday = false;
        }
        $data['status'] = $status;
        $selectOpt = array('*'=>'Select');
        $data['dataUrl'] = $this->url('task=get-ticket-data&act=ticket-category');
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        if(in_array(UserAuth::getRoleID(), array("R","S"))){
            $data['topMenuItems'] = array(array('href'=>'task=ticket&act=add-ticket-category', 'img'=>'fa fa-bar', 'label'=>'Add Ticket Category', 'title'=>'Add Ticket Category.'));
        }
        $this->getTemplate()->display('ticket_category_list', $data);
    }

    function actionUpdateTicketCategory(){
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MTicket.php');
        $email_model = new MTicket();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $id = $request->getRequest('id');
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
                if($email_model->updateTicketCategory($id, $obj->title, $obj->status)){
                    $errMsg = "Ticket Category Successfully Updated";
                    $errType = 0;
                    $url = $this->url("task=ticket&act=ticket-category-list");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else{
                    $errMsg = "Failed to Update Ticket Category";
                    $errType = 1;
                }
            }
        }

        $data['ticket_category'] = $obj;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['isUpdate'] = true;
        $data['topMenuItems'] = array(array('href'=>'task=ticket&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Update Ticket Category';
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        $this->getTemplate()->display('ticket_category_form', $data);
    }

    private function validate_ticket_category($post=[], $isUpdate=false){
        $email_model = new MTicket();
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
        if (empty($error_message) && !$isUpdate) {
            $error_message .= $email_model->ticket_category_exists($post['title']) ? "Ticket Category Exists" : "";
        }
        if (empty($error_message) && !$isUpdate) {
            $error_message .= $email_model->ticket_category_exists($post['title']) ? "Ticket Category Exists" : "";
        }
        return $error_message;
    }

    function actionAddTicketCategory (){
        if(!in_array(UserAuth::getRoleID(), array("R","S"))){
            exit();
        }
        include('model/MTicket.php');
        $email_model = new MTicket();

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
        $data['topMenuItems'] = array(array('href'=>'task=ticket&act=ticket-category-list', 'img'=>'fa fa-bar', 'label'=>'Ticket Category List', 'title'=>'Ticket Category List.'));
        $data['pageTitle'] = 'Add New Ticket Category';
        $data['smi_selection'] = 'ticket_category';
        $data['side_menu_index'] = 'ticketmng';
        $this->getTemplate()->display('ticket_category_form', $data);
    }

    private function get_default_ticket_category(){
        $ticket_category = new stdClass();
        $ticket_category->category_id = "";
        $ticket_category->title = "";
        $ticket_category->status = "";
        return $ticket_category;
    }

    function actionSkillDispositions(){
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $dispositions = array();
        include('model/MSkillCrmTemplate.php');
        $template_model = new MSkillCrmTemplate();
        $result = $template_model->getDispositions($sid);
        if (!empty($result)){
            foreach ($result as $key)$dispositions[$key->disposition_id] = $key->title;
        }
        echo json_encode($dispositions);
    }
}