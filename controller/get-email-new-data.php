<?php
require_once 'BaseTableDataController.php';
class GetEmailNewData extends BaseTableDataController {

    var $pagination;
    function __construct() {
        parent::__construct();

    }
    function getSearchObject($gridRequest, $status, $agent_id, $assigned_to, $fetch_inbox_info){
        $obj = new stdClass();
        $obj->ticket_id = !empty($gridRequest->getMultiParam('ticket_id')) ? $gridRequest->getMultiParam('ticket_id') : "";
        $obj->status = !empty($gridRequest->getMultiParam('status')) ? $gridRequest->getMultiParam('status') : $status;
        $obj->did = !empty($gridRequest->getMultiParam('did')) ? $gridRequest->getMultiParam('did') : "";
        $obj->account_id = !empty($gridRequest->getMultiParam('account_id')) ? $gridRequest->getMultiParam('account_id') : "";
        $obj->last_name = !empty($gridRequest->getMultiParam('last_name')) ? $gridRequest->getMultiParam('last_name') : "";
        $obj->from_email = !empty($gridRequest->getMultiParam('from_email')) ? $gridRequest->getMultiParam('from_email') : "";
        $obj->customer_id = !empty($gridRequest->getMultiParam('customer_id')) ? $gridRequest->getMultiParam('customer_id') : "";
        $obj->subject = !empty($gridRequest->getMultiParam('subject')) ? $gridRequest->getMultiParam('subject') : "";
        $obj->phone = !empty($gridRequest->getMultiParam('phone')) ? $gridRequest->getMultiParam('phone') : "";
        $obj->skill = !empty($gridRequest->getMultiParam('skill_name')) ? $gridRequest->getMultiParam('skill_name') : "";
        $obj->mail_to = !empty($gridRequest->getMultiParam('mail_to')) ? $gridRequest->getMultiParam('mail_to') : "";
        $obj->fetch_box_email = !empty($fetch_inbox_info['username']) ? $fetch_inbox_info['username'] : "";
        $obj->agent_id = $agent_id;
        $obj->assigned_to = $assigned_to;
        return $obj;
    }
    function actionEmailNewInit(){
        include('lib/DateHelper.php');
        include('model/MEmailNew.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmailNew();
        $dp_options = $eTicket_model->getDispositionTreeOptions();

        $emptyDate = false;
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $fetch_inbox_info = !empty($_REQUEST['info']) ? unserialize(base64_decode($_REQUEST['info'])) : "";
        $info = !empty($_REQUEST['info']) ? $_REQUEST['info'] : "";
        $inbox = !empty($_REQUEST['inbox']) ? unserialize(base64_decode($_REQUEST['inbox'])) : "";

        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
            $emptyDate = true;
        }

        $dateTimeArray = array();
        $lastDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_pull_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))){
                $emptyDate = true;
            }
        }

        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);

        if (isset($lastDateInfo->sdate) && !empty($lastDateInfo->sdate) && $allNew == "24" && !$this->gridRequest->isMultisearch) {
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

        $utype = UserAuth::hasRole('admin');
        $agentId = !$utype ? UserAuth::getCurrentUser() : "";
        $assignedId = $etype == 'myjob' ? UserAuth::getCurrentUser() : "";
        $search_obj = $this->getSearchObject($this->gridRequest, $status, $agentId, $assignedId, $fetch_inbox_info);

        $this->pagination->num_records = $eTicket_model->numETicket($search_obj, $dateTimeInfo, $lastDateInfo, $allNew);
        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getETicket($search_obj, $dateTimeInfo, $lastDateInfo, $allNew, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            $agent_names = $this->getAgentName($result);
            $skill_names = $this->getSkillName($result);
            $assigned_names = $this->getAssignedNames($result);
            //GPrint($result);die;
            foreach ( $result as &$data ) {
//                $time_difference = round((time() - $data->last_update_time)/60);
//                $isWorkingAble = empty($data->current_user) || ($time_difference > $this->last_update_difference_time)  ? true : false;

                $data->create_time = date("Y-m-d H:i:s", $data->tstamp);
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->email_did,$dp_options)?$dp_options[$data->email_did]:$data->email_did;
                $data->emailStatus = !empty($data->email_status) ? $eTicket_model->getTicketStatusLabel($data->status) : "New";
                $data->assigned_to = !empty($assigned_names[$data->assigned_to]) ? $assigned_names[$data->assigned_to] : "";
                $data->account_id = "`".$data->account_id."`";
                $data->last_agent = !empty($data->agent_id && !empty($agent_names[$data->agent_id])) ? $agent_names[$data->agent_id] : $data->first_name.' '.$data->last_name;

                $mail_to_title = $data->mail_to;
                $first_email = explode(",",$data->mail_to);
                $data->mail_to = "<a title='".$mail_to_title."' >".$first_email[0]."</a>";

                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $subjet_title = $str;
                $dotdot = strlen($str) > 30 ? "..." : "";
                $str =  explode(" ", $str);
                $str = implode(" ", array_splice($str, 0, 4));
                $subject = !empty($str) ? $str.$dotdot : "(no subject)";

                $data->last_name = $data->first_name.' '.$data->last_name;
                $data->subject = "<a title='$subjet_title' href='".$this->url('task=email-new&act=details&tid='.$data->ticket_id.'&msl='.$data->mail_sl.'&callid='.$callid.'&info='.$info)."'>".$subject."</a>";
//                if ($isWorkingAble){
//                    $data->subject = "<a  href='".$this->url('task=email&act=details&tid='.$data->ticket_id.'&callid='.$callid.'&info='.$info)."'>".$subject."</a>";
//                } else {
//                    $data->subject = "<a  href='".$this->url('task=email&act=details&tid='.$data->ticket_id.'&callid='.$callid.'&info='.$info)."'><i class='fa fa-user' style='color: #F05050;'></i> ".$subject ."</a>";
//                }
//                $data->email_count = !empty($incoming_email_count[$data->ticket_id]) ? $incoming_email_count[$data->ticket_id] : 0;
                $data->skill_name = !empty($skill_names[$data->skill_id]) ? $skill_names[$data->skill_id] : $data->skill_id;
                $data->type = $data->status=="N" ? "Inbound" : "Outbound";
                $data->last_pull_time = !empty($data->last_pull_time) ? date("Y-m-d H:i:s", $data->last_pull_time) : "";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
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

    function getAgentName($list_data=null){
        $response = [];
        if (!empty($list_data)){
            $temp = [];
            foreach ($list_data as $key){
                if (!in_array($key->agent_id, $temp)){
                    $temp[] = $key->agent_id;
                }
            }
            if (!empty($temp)){
                $mainobj = new MAgent();
                $result = $mainobj->getAllAgents($temp);
                if (!empty($result)){
                    foreach ($result as $key){
                        $response[$key->agent_id] = $key->name;
                    }
                }
            }
        }
        return $response;
    }

    function getSkillName($list_data=null){
        $response = [];
        if (!empty($list_data)){
            $temp = [];
            foreach ($list_data as $key){
                if (!in_array($key->skill_id, $temp)){
                    $temp[] = $key->skill_id;
                }
            }
            if (!empty($temp)){
                $mainobj = new MEmailNew();
                $result = $mainobj->getAllSkillName($temp);
                if (!empty($result)){
                    foreach ($result as $key){
                        $response[$key->skill_id] = $key->skill_name;
                    }
                }
            }
        }
        return $response;
    }
}