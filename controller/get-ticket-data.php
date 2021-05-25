<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/5/2018
 * Time: 10:27 AM
 */


require_once 'BaseTableDataController.php';
class GetTicketData extends BaseTableDataController
{
    var $pagination;

    function __construct(){
        parent::__construct();

    }

    function actionTicketInit() {
        include('lib/DateHelper.php');
        include('model/MTicket.php');
        include('model/MAgent.php');
        $eTicket_model = new MTicket();
        //$this->pagination->rows_per_page = 20;

        include('model/MSkillCrmTemplate.php');
        $crm_dp_model = new MSkillCrmTemplate();
        $dp_options = $crm_dp_model->getDispositionSelectOptions(true);
        //GPrint($dp_options);die;

        $emptyDate = false;
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $tid = '';
        $email = '';
        $did = '';
        $agentId = "";
        $assignedId = "";
        $source = '';
        $sender_name = '';
        $ticket_category_id = "";
        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
            $emptyDate = true;
        }

        $dateTimeArray = array();
        $lastDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))){
                $emptyDate = true;
            }
        }

        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);

        if (isset($lastDateInfo->sdate) && !empty($lastDateInfo->sdate) && $allNew == "24" && !$this->gridRequest->isMultisearch){
            $curTime = time();
            $lastDateInfo->stime = date("H:i", $curTime);
            $lastDateInfo->etime = date("H:i", $curTime);

            $dateTimeInfo->stime = "";
            $dateTimeInfo->etime = "";
            $dateTimeInfo->sdate = "";
            $dateTimeInfo->edate = "";
            $dateTimeInfo->ststamp = "";
            $dateTimeInfo->etstamp = "";
        }
        //$dateTimeInfo->dbfield = "create_time";
        //$lastDateInfo->dbfield = "last_update_time";
        //GPrint($dateTimeInfo);die;
        if ($this->gridRequest->isMultisearch) {
            $tid = $this->gridRequest->getMultiParam('ticket_id');
            //$email = $this->gridRequest->getMultiParam('email');
            $status = $this->gridRequest->getMultiParam('status');
            $did = $this->gridRequest->getMultiParam('did');
            $source = $this->gridRequest->getMultiParam('source');

            $account_id = $this->gridRequest->getMultiParam('account_id');
            $last_name = $this->gridRequest->getMultiParam('last_name');
            $sender_name = $this->gridRequest->getMultiParam('created_by');
            $ticket_category_id = $this->gridRequest->getMultiParam('category_id');
            $maker = $this->gridRequest->getMultiParam('name');
            $created_for = $this->gridRequest->getMultiParam('created_for');
        }
        $utype = UserAuth::hasRole('admin');

        if (!$utype){
            $agentId = UserAuth::getCurrentUser();
        }

        if ($etype == 'myjob') {
            $assignedId = UserAuth::getCurrentUser();
        }


        $this->pagination->num_records =
            $eTicket_model->numETicket($agentId, $assignedId, $tid,  $did, $status, $dateTimeInfo, 'create_time', $allNew, $lastDateInfo, $source, $account_id, $last_name, $sender_name, $ticket_category_id, $maker, $created_for);
        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getETicket($agentId, $assignedId, $tid, $did, $status, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, 'create_time', $allNew, $lastDateInfo,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for) : null;
        //GPrint($eTickets);die;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;
        //GPrint($eTickets);die;

        //////ONE BANK START///////
        $ticket_IDS = '';
        $last_agents = array();
        if (!empty($eTickets)){
            foreach ($eTickets as $tic)$ticket_IDS[] = $tic->ticket_id;
        }
        $update_res = !empty($ticket_IDS) ? $eTicket_model->getLastTicketUpdater($ticket_IDS) : "";
        if (!empty($update_res)){
            foreach ($update_res as $key){
                $last_agents[$key->ticket_id] = $key->nick;
            }
        }
        //////ONE BANK END///////

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $source_name = array("C"=>"CRM","E"=>"Email","M"=>"Manual");

        $result=&$eTickets;
        if(count($result) > 0){
            $email_message = $this->getLastEmailDetails($result);
            $sender_name = $this->getSenderNames($result);
            $assigned_names = $this->getAssignedNames($result);
            //dd($email_message);
            foreach ( $result as &$data ) {
                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                $data->last_update_time = date("Y-m-d H:i:s", $data->last_update_time);
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;

                $data->sender_name = !empty($sender_name) && array_key_exists($data->created_by,$sender_name) ? $sender_name[$data->created_by] : $data->created_by;
                //$data->sujectText = substr($data->subject, 0, 55);

                //if (strlen($data->subject) > 55) $data->sujectText .= ' ...';
                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->category_id = $data->title;
                $data->source = array_key_exists($data->source,$source_name) ? $source_name[$data->source]:$data->source;
                $data->assigned_to = !empty($assigned_names[$data->assigned_to]) ? $assigned_names[$data->assigned_to] : "";
                $data->Comments = "";

                $data->account_id = "`".$data->account_id."`";
                $data->last_agent = !empty($last_agents) ? $last_agents[$data->ticket_id] : "";

                if (!empty($email_message) && array_key_exists($data->ticket_id,$email_message)) {
                    $title = strip_tags(base64_decode($email_message[$data->ticket_id]));
                    $data->Comments = trim(html_entity_decode($title), " \t\n\r\0\x0B\xC2\xA0");
                    $data->detailsUrl = "<a title='".$title."' href='".$this->url('task=ticket&act=details&tid='.$data->ticket_id)."'>Details</a>";
                } else {
                    $data->detailsUrl = "<a href='".$this->url('task=ticket&act=details&tid='.$data->ticket_id)."'>Details</a>";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function getLastEmailDetails($result){
        $ticket_id = array();
        $response = array();
        foreach ($result as $key)$ticket_id[] = $key->ticket_id;

        if (!empty($ticket_id)){
            $eTicket_model = new MTicket();
            $data = $eTicket_model->getEmailMessage($ticket_id);
            if (count($data) > 0){
                foreach ($data as $ksy => $item){
                    $response[$item->ticket_id] = $item->ticket_body;
                }
            }
        }
        return $response;
    }
    function getSenderNames($result){
        $create_by = array();
        $response = array();
        foreach ($result as $key)$create_by[] = $key->created_by;
        if (!empty($create_by)){
            $eTicket_model = new MTicket();
            $data = $eTicket_model->getCreatedByNames($create_by);
            if (count($data) > 0){
                foreach ($data as $key => $item){
                    $response[$item->agent_id] = $item->nick;
                }
            }
        }
        return $response;
    }
    function getAssignedNames($result){
        $assigned_id = array();
        $response_array = array();
        foreach ($result as $key){
            if (!empty($key->assigned_to))$assigned_id[] = $key->assigned_to;
            if (!empty($key->acd_agent))$assigned_id[] = $key->acd_agent;
        }
        if (!empty($assigned_id)){
            $agent_model = new MAgent();
            $data = $agent_model->getAllAgents($assigned_id);
            if (count($data) > 0){
                foreach ($data as $key)
                    $response_array[$key->agent_id] = $key->name;
            }
        }
        return $response_array;
    }

    function actionTicketCategory(){
        include('model/MTicket.php');
        $et_model = new MTicket();
        $title = "";
        $status = "";
        if ($this->gridRequest->isMultisearch){
            $title = $this->gridRequest->getMultiParam('title');
            $status = $this->gridRequest->getMultiParam('status');
        }

        $this->pagination->num_records = $et_model->numTicketCategory($title,$status);
        $category = $this->pagination->num_records > 0 ?
            $et_model->getTicketCategory($title,$status,$this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($category) ? count($category) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$category;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->titleUrl = "";
                $titleClass = "";

                $titleUrl = $this->url("task=ticket&act=update-ticket-category&id=".$data->category_id);
                $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
                $status = $data->status=="A"?"I":"A";
                $data->status = "<a class='ConfirmAjaxWR' msg='Do you confirm that you want to change status ?' href='" . $this->url("task=confirm-response&act=ticket-category&pro=cs&id=".$data->category_id."&status=" .$status) . "'>" . ($data->status == "A" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}