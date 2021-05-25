<?php
require_once 'BaseTableDataController.php';
class GetCrmticketData extends BaseTableDataController
{
    var $pagination;

    function __construct() {
        parent::__construct();
        $role_id = UserAuth::getRoleID();
        if ($role_id !="R" && $role_id != "S")
        exit;
    }

    function actionCrmticketInit()
    {
        include('lib/DateHelper.php');
        include('model/MCrmTicket.php');
        $eTicket_model = new MCrmTicket();
        $this->pagination->rows_per_page = 20;

        $emptyDate = false;
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $tid = '';
        $agentId = "";
        $assignedId = "";
        $skill_id = "*";
        $disposition_id = "*";
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

        if ($this->gridRequest->isMultisearch){
            $tid = $this->gridRequest->getMultiParam('ticket_id');
            $status = $this->gridRequest->getMultiParam('status');
            $disposition_id = $this->gridRequest->getMultiParam('disposition_id');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }
        $utype = UserAuth::hasRole('admin');

        if (!$utype){
            $agentId = UserAuth::getCurrentUser();
        }

        if ($etype == 'myjob') {
            $assignedId = UserAuth::getCurrentUser();
        }

        $this->pagination->num_records = $eTicket_model->numETicket($agentId, $assignedId, $tid, $skill_id, $disposition_id, $status, $dateTimeInfo, 'create_time', $allNew, $lastDateInfo);
        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getETicket($agentId, $assignedId, $tid, $skill_id, $disposition_id, $status, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, 'create_time', $allNew, $lastDateInfo) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            $status = $eTicket_model->getETicketInfoStatusOptions();
            foreach ( $result as &$data ) {
                $data->create_time = date("Y-m-d H:i", $data->create_time);
                $data->last_update_time = date("Y-m-d H:i", $data->last_update_time);
                $data->sujectText = substr($data->subject, 0, 55);
                if (strlen($data->subject) > 55) $data->sujectText .= ' ...';
                $data->status = !empty($status[$data->status]) ? $status[$data->status] : $data->status;
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

/*
    function actionCrmticketInit(){
        include('lib/DateHelper.php');
        include('model/MCrmTicket.php');
        $crmTicket_model = new MCrmTicket();
        $this->pagination->rows_per_page = 20;

        $skill_id = "";
        $disposition_id = "";
        $status = "";
        $emptyDate = false;

        $dateTimeArray = array();
        $lastDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))){
                $emptyDate = true;
            }

            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $disposition_id = $this->gridRequest->getMultiParam('disposition_id');
            $status = $this->gridRequest->getMultiParam('status');
        }
        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);

        $this->pagination->num_records = $crmTicket_model->numETicketInfo($skill_id,$disposition_id,$status,$dateTimeInfo,"create_time",$lastDateInfo,$tabName="");
        $eTickets = $this->pagination->num_records > 0 ?
            $crmTicket_model->getETicketInfo($skill_id,$disposition_id, $status, $dateTimeInfo, "create_time", $lastDateInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;


        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->source = $data->source=="E"?"Email":"CRM";
                if ($data->status=="O") $data->status = "Open";
                elseif ($data->status=="P") $data->status = "Pending";
                elseif ($data->status=="C") $data->status = "Client";
                $data->last_update_time = date("Y-m-d H:i:s", $data->last_update_time);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

*/
}
