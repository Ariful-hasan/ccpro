<?php
require_once 'BaseTableDataController.php';
class GetEmailData extends BaseTableDataController
{
	var $pagination;
	
	function __construct() {
		parent::__construct();

	}
	
	function actionEmailInit()
	{
	    include('lib/DateHelper.php');
		include('model/MEmail.php');
		include('model/MAgent.php');
		$eTicket_model = new MEmail();
		//$this->pagination->rows_per_page = 20;

//        include('model/MSkillCrmTemplate.php');
//        $crm_dp_model = new MSkillCrmTemplate();
//        $dp_options = $crm_dp_model->getDispositionSelectOptions(true);
        $dp_options = $eTicket_model->getDispositionChildrenOptions();
		$emptyDate = false;
        $corporate_domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : '';
		$etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		$allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$callid = isset($_REQUEST['callid']) ? trim($_REQUEST['callid']) : '';
        $fetch_inbox_info = !empty($_REQUEST['info']) ? unserialize(base64_decode($_REQUEST['info'])) : "";
        $info = !empty($_REQUEST['info']) ? $_REQUEST['info'] : "";
        $inbox = !empty($_REQUEST['inbox']) ? unserialize(base64_decode($_REQUEST['inbox'])) : "";
//        GPrint($fetch_inbox_info);
//        GPrint($inbox);die;
		$tid = '';
		$email = '';
		$did = '';
		$agentId = "";
		$assignedId = "";
        $source = '';
        $sender_name = '';
        $account_id = '';
        $last_name = '';
        $ticket_category_id = "";
        $maker = '';
        $created_for = '';
        $customer_id = $subject = $phone = $skill = $mail_to = '';
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
		//$dateTimeInfo->dbfield = "create_time";
		//$lastDateInfo->dbfield = "last_update_time";
        //GPrint($dateTimeInfo);die;
		if ($this->gridRequest->isMultisearch){
		    $tid = $this->gridRequest->getMultiParam('ticket_id');
		    $email = $this->gridRequest->getMultiParam('email');
		    $status = $this->gridRequest->getMultiParam('status');
		    $did = $this->gridRequest->getMultiParam('did');
		    $source = $this->gridRequest->getMultiParam('source');

		    $account_id = $this->gridRequest->getMultiParam('account_id');
		    $last_name = $this->gridRequest->getMultiParam('last_name');
		    $sender_name = $this->gridRequest->getMultiParam('created_by');
		    $ticket_category_id = $this->gridRequest->getMultiParam('category_id');
		    $maker = $this->gridRequest->getMultiParam('name');
		    $created_for = $this->gridRequest->getMultiParam('created_for');
		    $customer_id = $this->gridRequest->getMultiParam('customer_id');
            $subject = $this->gridRequest->getMultiParam('subject');
            $phone = $this->gridRequest->getMultiParam('phone');
            $skill = $this->gridRequest->getMultiParam('skill_name');
            $mail_to = $this->gridRequest->getMultiParam('mail_to');
		}
		$utype = UserAuth::hasRole('admin');
		
		if (!$utype){
			$agentId = UserAuth::getCurrentUser();
		}
		
		if ($etype == 'myjob') {
			$assignedId = UserAuth::getCurrentUser();
		}

		$ticket_id_arr = !empty($created_for) ? $eTicket_model->getEmailIdByEmailAddress($created_for, $dateTimeInfo, $lastDateInfo) : [];

		$this->pagination->num_records = $eTicket_model->numETicket($agentId, $assignedId, $tid, $email, $did, $status, $dateTimeInfo, 'create_time', $allNew, $lastDateInfo,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for, $customer_id,$subject,$phone, $skill, $mail_to, $fetch_inbox_info, $ticket_id_arr);
		$eTickets = $this->pagination->num_records > 0 ?
			$eTicket_model->getETicket($agentId, $assignedId, $tid, $email, $did, $status, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, 'create_time', $allNew, $lastDateInfo,$source,$account_id,$last_name,$sender_name,$ticket_category_id,$maker,$created_for, $customer_id,$subject,$phone, $skill, $mail_to, $fetch_inbox_info, $ticket_id_arr) : null;
        //GPrint($eTickets);die;
		$this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;
		//GPrint($eTickets);die;

        //////ONE BANK START///////
        $ticket_IDS = [];
        $last_agents = array();
        if (!empty($eTickets)){
            foreach ($eTickets as $tic) 
                $ticket_IDS[] = $tic->ticket_id;
        }
        /*$update_res = $eTicket_model->getLastTicketUpdater($ticket_IDS);
        if (!empty($update_res)){
            foreach ($update_res as $key){
                $last_agents[$key->ticket_id] = $key->nick;
            }
        }*/
        //////ONE BANK END///////

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;

	    $source_name = array("C"=>"CRM","E"=>"Email","M"=>"Manual");

	    $result=&$eTickets;
	    if(count($result) > 0){
            //$email_message = $this->getLastEmailDetails($result);
            //$sender_name = $this->getSenderNames($result);
            $assigned_names = $this->getAssignedNames($result);

            $temp_status_updated_by = [];
            $status_updated_by_agent_names=[];
            $email_IDs = [];
            foreach ($result as $key){
                if (!filter_var($key->status_updated_by, FILTER_VALIDATE_EMAIL) && !in_array($key->status_updated_by, $temp_status_updated_by)){
                    $temp_status_updated_by[] = $key->status_updated_by;
                }
                $email_IDs[] = $key->ticket_id;
            }
            $status_updated_by_agent_names = !empty($temp_status_updated_by) ? $eTicket_model->getEmailAgentList($temp_status_updated_by) : '';
            $incoming_email_count = $eTicket_model->getIncomingEmailCountById($email_IDs);
            foreach ( $result as &$data ) {
                $time_difference = round((time() - $data->last_update_time)/60);
                $isWorkingAble = empty($data->current_user) || ($time_difference > $this->last_update_difference_time)  ? true : false;
                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                //$data->last_update_time = date("Y-m-d H:i:s", $data->last_update_time);
                $data->last_update_time = date("Y-m-d", time()) == date("Y-m-d", $data->last_update_time) ? get_time_ago($data->last_update_time, false) : date("M j 'y, h:i a", $data->last_update_time);
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;
                //$data->sender_name = !empty($sender_name) && array_key_exists($data->created_by,$sender_name) ? $sender_name[$data->created_by] : $data->created_by;
                //$data->sujectText = substr($data->subject, 0, 55);
                //if (strlen($data->subject) > 55) $data->sujectText .= ' ...';
                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->category_id = $data->title;
                $data->source = !empty($data->source)&&array_key_exists($data->source,$source_name) ? $source_name[$data->source]:'';
                $data->assigned_to = !empty($assigned_names[$data->assigned_to]) ? $assigned_names[$data->assigned_to] : "";
                //$data->Comments = "";
                $data->account_id = "`".$data->account_id."`";
                $customer_name = $data->first_name.' '.$data->last_name;
                $data->last_agent = '';
                if (!empty($data->status_updated_by) && (filter_var($data->status_updated_by, FILTER_VALIDATE_EMAIL))){
                    $data->last_agent = mb_strlen($customer_name) > 20 ? "<span title='".$customer_name."'>".mb_substr($customer_name, 0,20, "UTF-8")."</span>" : $customer_name;
                }elseif (!empty($status_updated_by_agent_names)) {
                    $data->last_agent = $status_updated_by_agent_names[$data->status_updated_by];
                }
                $href = json_encode($this->url('task=email&act=details&tid='.$data->ticket_id.'&callid='.$callid));
                $details_onCLick = "onclick=isTyping(".json_encode($data->ticket_id).",$href)";
                $mail_to_title = $data->mail_to;
                $first_email = explode(",",$data->mail_to);
                $data->is_inbox = !empty($inbox) && in_array($data->fetch_box_email,$inbox) ? "Y" : "";
                //$data->mail_to = "<a title='".$mail_to_title."' >".$first_email[0]."</a>";
                $data->mail_to = "<b title='".$mail_to_title."' >".$data->fetch_box_email."</b>";
                $sl = sprintf("%03d", $data->num_mails);
                //$data->attachmentUrl = "<a class='lightboxWIFR' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                $data->attachmentUrl = "<a class='' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                //$data->acd_agent = !empty($assigned_names[$data->acd_agent]) ? $assigned_names[$data->acd_agent] : '';
                //$data->acd_status = !empty($data->acd_status) ? $eTicket_model->getTicketStatusLabel($data->acd_status) : '';
                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $str = strlen(trim($str)) > 20 ? mb_substr(trim($str),0, 20)."..." : trim($str);
                $subject = !empty($str) ? htmlspecialchars($str, ENT_QUOTES) : "(no subject)";
                $data->last_name = mb_strlen($customer_name) > 40 ? "<span title='".$customer_name."'>".substr($customer_name, 0,20)."</span>" : $customer_name;

                $title_subject = !empty($data->subject) ? htmlspecialchars(base64_decode($data->subject), ENT_QUOTES) : "";
                if ($isWorkingAble){
                    $data->subject = "<a title='".$title_subject."'  href='".$this->url('task=email&act=details&tid='.$data->ticket_id.'&callid='.$callid.'&info='.$info)."'>".$subject."</a>";
                } else {
                    $data->subject = "<a title='".$title_subject."' href='".$this->url('task=email&act=details&tid='.$data->ticket_id.'&callid='.$callid.'&info='.$info)."'><i class='fa fa-user' style='color: #ff1a1a;'></i> ".$subject ."</a>";
                }
                $data->email_count = !empty($incoming_email_count[$data->ticket_id]) ? $incoming_email_count[$data->ticket_id] : 0;
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}

	function getSenderNames($result){
	    $create_by = array();
        $response = array();
        foreach ($result as $key)$create_by[] = $key->created_by;
        if (!empty($create_by)){
            $eTicket_model = new MEmail();
            $data = $eTicket_model->getCreatedByNames($create_by);
            if (count($data) > 0){
                foreach ($data as $key => $item){
                    $response[$item->agent_id] = $item->nick;
                }
            }
        }
        return $response;
    }

	function getLastEmailDetails($result){
        $ticket_id = array();
        $response = array();
        foreach ($result as $key)$ticket_id[] = $key->ticket_id;

        if (!empty($ticket_id)){
            $eTicket_model = new MEmail();
            $data = $eTicket_model->getEmailMessage($ticket_id);
            if (count($data) > 0){
                foreach ($data as $ksy => $item){
                    $response[$item->ticket_id] = $item->mail_body;
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
//        GPrint($assigned_id);die;
        if (!empty($assigned_id)){
            $agent_model = new MAgent();
            $data = $agent_model->getAllAgents($assigned_id);
            if (count($data) > 0){
                foreach ($data as $key)
                    $response_array[$key->agent_id] = $key->name;
            }
        }
        //GPrint($response_array);die;
        return $response_array;
    }
	
	function actionEmailtemplate(){
	    include('model/MEmailTemplate.php');
	    include('model/MEmail.php');
	    $email_model = new MEmail();
	    $et_model = new MEmailTemplate();

	    $this->pagination->num_records = $et_model->numTemplates();
	    $emails = $this->pagination->num_records > 0 ?
	    $et_model->getTemplates($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$emails;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	           $data->titleUrl = "";
	           $titleClass = "";
	           $titleUrl = "";
	           $titleUrl = $this->url("task=emailtemplate&act=update&tid=".$data->tstamp);
        		if (UserAuth::hasRole('agent')){
        			$titleUrl = $this->url("task=emailtemplate&act=message&tid=".$data->tstamp);
        			$titleClass = "class='showDetails'";
        		}
        		if(!empty($titleUrl)){
        		    $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
        		}
        		
        		$data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
        		$data->disposition = "";
		
        		if (!empty($data->disposition_id)) {
        		    $path = $email_model->getDispositionPath($data->disposition_id);
        		    if (!empty($path)) $data->disposition = $path . ' -> ';
        		}
        		$data->disposition .= $data->dc_title;
        		$data->actUrl = "";
        		if (!UserAuth::hasRole('agent')){
            		$data->actUrl = "<a href='".$this->url("task=emailtemplate&act=copy&tid=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-copy icon-purple'></i></span></a> &nbsp; ";
            		$data->actUrl .= "<a class='ConfirmAjaxWR' msg='Are you sure to delete disposition code : ". $data->tstamp ."' ";
            		$data->actUrl .= "href='" . $this->url("task=confirm-response&act=emailTempDel&pro=del&tstamp=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
        		}
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmailskills(){
	    include('model/MSkill.php');
		$skill_model = new MSkill();
		include('model/MEmail.php');
		$email_model = new MEmail();

		if (UserAuth::hasRole('admin')) {
			$skills = $skill_model->getSkills('', 'E', 0, 100);
		} else {
			$skills = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
		}

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$skills;
	    if(count($result) > 0){
	        $skills_inkpi = MEmail::getAllSkillInkpi();/// skills inkpi time.
	        foreach ( $result as &$data ) {
	            $properties = $email_model->skillEmailProperties($data->skill_id);
	            $data->disposition = "";
	            $data->autyReply = "";
	            $data->signature = "";
	            
	            $data->disposition = '<a href="'.$this->url('task=emaildc&sid='.$data->skill_id).'">Dispositions</a>';
	            $data->autyReply = '<a href="'.$this->url('task=emailartemplate&tid='.$data->skill_id).'">';	            
	            if ($properties->auto_reply == 'Y') {
	                $data->autyReply .= "<span class='text-success'>Enabled</span>";	            
	            }else {
	                $data->autyReply .= "<span class='text-danger'>Disabled</span>";
	            }
	            $data->autyReply .= '</a>';
	            
	            $data->signature = '<a href="'.$this->url('task=emailsignature&sid='.$data->skill_id).'">';
	            if ($properties->signature == 'Y') {
	                $data->signature .= "<span class='text-success'>Enabled</span>";
	            }else {
	                $data->signature .= "<span class='text-danger'>Disabled</span>";
	            }	            
	            $data->signature .= '</a>';
	            
	            $data->status = $data->active=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";

                //$data->domain = '<a href="'.$this->url('task=email&act=add-domain&sid='.$data->skill_id).'">Domain</a>';
                $data->domain = '<a href="'.$this->url('task=email&act=skill-domain&sid='.$data->skill_id).'">Domain</a>';
                $data->keyword = '<a href="'.$this->url('task=email&act=skill-keyword&sid='.$data->skill_id).'">Keyword</a>';
                $in_kpi_time = isset($skills_inkpi[$data->skill_id]) ? gmdate("H:i:s", $skills_inkpi[$data->skill_id]) : '00:00:00';
                $data->inkpi = '<a href="'.$this->url('task=email&act=skill-inkpi&sid='.$data->skill_id).'">'.$in_kpi_time.'</a>';
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmaildsinit(){
	    include('model/MEmail.php');
		include('model/MSkill.php');
		$dc_model = new MEmail();
		$skill_model = new MSkill();
		
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';		
		$templateinfo = $skill_model->getSkillById($sid);		
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();
		
		$this->pagination->num_records = $dc_model->numDispositions($sid);
		$dispositions = $this->pagination->num_records > 0 ? 
			$dc_model->getDispositions($sid, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($dispositions) ? count($dispositions) : 0;

		$responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
        $disposition_types = get_disposition_type();
	    $result=&$dispositions;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $parents = array();
	            $data->dispTitle = "";
	            $data->actUrl = "";	            
	            $parent = '';
	            
	            if (!empty($data->parent_id)) {
	                if (isset($parents[$data->parent_id])) {
	                    $parent = $parents[$data->parent_id];
	                } else {
	                    $parent = $dc_model->getDispositionPath($data->disposition_id);
	                    $parents[$data->parent_id] = $parent;
	                }
	            }
	            $copyText = !empty($parent) ? $parent . ' -> '.$data->title : $data->title;
	            
	            $data->dispTitle = "<a href='".$this->url("task=emaildc&act=update&sid=$sid&did=".$data->disposition_id)."'>";
	            if (!empty($parent)) $data->dispTitle .= $parent . ' -> ';
	            $data->dispTitle .= $data->title;
                $data->disposition_type = !empty($disposition_types[$data->disposition_type]) ? $disposition_types[$data->disposition_type] : $data->disposition_type;
                $data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == "Y" ? "inactivate" : "activate") . " this item: " . $data->title . "?' data-href='" . $this->url("task=email-confirm-response&act=disposition-status&id=" . $data->disposition_id . "&sts=" . ($data->status == "Y" ? "N" : "Y")."&pro=S") . "'>" . ($data->status == "Y" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";

                $data->dispTitle .= "</a>";
	            
	            $data->actUrl = "<a data-w='800' class='lightboxWIF' href='".$this->url("task=emaildscopy&did=".$data->disposition_id."&cptxt=".urlencode($copyText))."'><span><i style='font-size: 16px;' class='fa fa-copy icon-purple'></i></span></a> &nbsp; ";
//	            $data->actUrl .= "<a class='ConfirmAjaxWR' msg='Are you sure to delete disposition code : ". $data->disposition_id ."' ";
//	            $data->actUrl .= "href='" . $this->url("task=confirm-response&act=emailDsDel&pro=del&sid=$sid&dcode=".$data->disposition_id)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
	            
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmailallowedinit(){
	    include('model/MEmail.php');
		include('model/MSkill.php');
		$skill_model = new MSkill();
		$email_model = new MEmail();

		$this->pagination->num_records = $email_model->numAllowedEmails();
		$services = $this->pagination->num_records > 0 ? 
			$email_model->getAllowedEmails($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($services) ? count($services) : 0;
		$skills = $skill_model->getEmailSkillOptions();
	
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	     
	    $result=&$services;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $parents = array();
	            $data->addrName = "";
	            $data->actUrl = "";
	            $data->emailSkill = "";
	            
	            $data->addrName = "<a href='".$this->url("task=emailallowed&act=update&eid=".urlencode($data->email))."'>".$data->name."</a>";
	            
	            if (empty($data->skill_id)) {
	                $data->emailSkill = 'Global'; 
	            }else {
	                $data->emailSkill = isset($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
	            }
	            $data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
	             
	            $data->actUrl = "<a class='ConfirmAjaxWR' msg='Are you sure to delete email ". $data->name ."' ";
	            $data->actUrl .= "href='" . $this->url("task=confirm-response&act=emailAllowedDel&pro=del&eid=".urlencode($data->email))."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmailreportStatus(){
	    include('model/MSkill.php');
	    include('model/MEmail.php');
	    include('model/MEmailReport.php');
	    include('lib/DateHelper.php');
	    $email_model = new MEmail();
	    $skill_model = new MSkill();
	    $report_model = new MEmailReport();
	    
	    $emptyDate = false;
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
	        if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from']))){
	            $emptyDate = true;
	        }
	    }
	    
	    $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);

	    if ($this->gridRequest->isMultisearch){
	        $skillid = $this->gridRequest->getMultiParam('skillid');
	        $sid = $this->gridRequest->getMultiParam('sid');
	    }
	    $skill_options = $skill_model->getEmailSkillOptions();
	    $st_options = $email_model->getTicketStatusOptions();
	    
	    
	    $this->pagination->num_records = $report_model->numReportByStatus($skillid, $sid, $dateTimeInfo);
	    $records = $this->pagination->num_records > 0 ?
	    $report_model->getReportByStatus($skillid, $sid, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	    
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->create_time = date("Y-m-d H:i:s", $data->create_time);
	            $data->skillid = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
	            $data->sid = isset($st_options[$data->status]) ? $st_options[$data->status] : $data->status;
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmailreportDisposition(){
	    include('model/MSkill.php');
		include('model/MEmail.php');
		include('model/MEmailReport.php');
		include('lib/DateHelper.php');
		$skill_model = new MSkill();
		$email_model = new MEmail();
		$report_model = new MEmailReport();
	    
	    $emptyDate = false;
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
	        if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from']))){
	            $emptyDate = true;
	        }
	    }
	    
	    $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);

	    if ($this->gridRequest->isMultisearch){
	        $skillid = $this->gridRequest->getMultiParam('skillid');
	        $sid = $this->gridRequest->getMultiParam('did');
	    }
	    $skill_options = $skill_model->getEmailSkillOptions();
	    $dp_options = $email_model->getDispositionTreeOptions($skillid);
	    
	    
	    $this->pagination->num_records = $report_model->numReportByDisposition($skillid, $sid, $dateTimeInfo);
	    $records = $this->pagination->num_records > 0 ?
	    $report_model->getReportByDisposition($skillid, $sid, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	    
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->create_time = date("Y-m-d H:i:s", $data->create_time);
	            $data->skillid = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
	            $data->did = isset($dp_options[$data->disposition_id]) ? $dp_options[$data->disposition_id] : $data->disposition_id;
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionEmailreportAgentactivity(){
	    include('model/MAgent.php');
		include('model/MEmail.php');
		include('model/MEmailReport.php');
		include('lib/DateHelper.php');
		$email_model = new MEmail();
		$agent_model = new MAgent();
		$report_model = new MEmailReport();
	    
	    $emptyDate = false;
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
	        if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from']))){
	            $emptyDate = true;
	        }
	    }
	    
	    $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);

	    if ($this->gridRequest->isMultisearch){
	        $agentid = $this->gridRequest->getMultiParam('agentid');
	    }
	    
	    $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
	    $this->pagination->num_records = $report_model->numReportByAgentActivity($agentid, $dateTimeInfo);
	    $records = $this->pagination->num_records > 0 ?
	    $report_model->getReportByAgentActivity($agentid, $dateTimeInfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;	    
	    
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;

	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->agent_name = $data->agent_id;
	            $data->agent_name .= isset($agent_options[$data->agent_id]) ? ' - ' . $agent_options[$data->agent_id] : '';
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}

	function actionSmstemplate(){
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();

		$this->pagination->num_records = $et_model->numSmsTemplates();
		$emails = $this->pagination->num_records > 0 ?
			$et_model->getSmsTemplates($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;

		$result=&$emails;
		if(count($result) > 0){
			foreach ( $result as &$data ) {
				$data->titleUrl = "";
				$titleClass = "";
				$titleUrl = "";
				$titleUrl = $this->url("task=smstemplate&act=update&tid=".$data->tstamp);
				if (UserAuth::hasRole('agent')){
					$titleUrl = $this->url("task=smstemplate&act=message&tid=".$data->tstamp);
					$titleClass = "class='showDetails'";
				}
				if(!empty($titleUrl)){
					$data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
				}

				$data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
				$data->smsDetails = $data->sms_body;
				$data->actUrl = "";
				if (!UserAuth::hasRole('agent')){
					$data->actUrl = "<a class='ConfirmAjaxWR' msg='Are you sure to delete sms template : ". $data->title ."' ";
					$data->actUrl .= "href='" . $this->url("task=confirm-response&act=smsTempDel&pro=del&tstamp=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
				}
			}
		}
		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}

    function actionTicketCategory(){
        include('model/MEmail.php');
        $et_model = new MEmail();
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

                $titleUrl = $this->url("task=email&act=update-ticket-category&id=".$data->category_id);
                $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
                $status = $data->status=="A"?"I":"A";
                $data->status = "<a class='ConfirmAjaxWR' msg='Do you confirm that you want to change status ?' href='" . $this->url("task=confirm-response&act=ticket-category&pro=cs&id=".$data->category_id."&status=" .$status) . "'>" . ($data->status == "A" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionSkillEmailtemplate(){
        include('model/MEmailTemplate.php');
        include('model/MEmail.php');
        $email_model = new MEmail();
        $et_model = new MEmailTemplate();

        $this->pagination->num_records = $et_model->numTemplates();
        $emails = $this->pagination->num_records > 0 ?
            $et_model->getTemplates($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$emails;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->titleUrl = "";
                $titleClass = "";
                $titleUrl = "";
                $titleUrl = $this->url("task=template&act=update&tid=".$data->tstamp);
                if (UserAuth::hasRole('agent')){
                    $titleUrl = $this->url("task=template&act=message&tid=".$data->tstamp);
                    $titleClass = "class='showDetails'";
                }
                if(!empty($titleUrl)){
                    $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
                }

                $data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    function actionEmailSkillDomain(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        $dc_model = new MEmail();
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $this->pagination->num_records = $dc_model->numSkillDomain($sid);
        $domain = $this->pagination->num_records > 0 ? $dc_model->getSkillDomain($sid, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        //GPrint($domain);die;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $result=&$domain;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->titleUrl = "";
                $titleClass = "";

                $titleUrl = $this->url("task=email&act=update-skill-domain&sid=".$data->skill_id.'&dmn='.urlencode(base64_encode($data->domain)));
                //$data->domain = $data->domain;
                $data->update = "<a ".$titleClass." href='".$titleUrl."'>".$data->domain."</a>";
                //$data->action = "<a class='ConfirmAjaxWR' msg='Do you confirm that you want to change status ?' href='" . $this->url("task=confirm-response&act=ticket-category&pro=cs&id=".$data->category_id."&status=" .$status) . "'>" . ($data->status == "A" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
                $data->actUrl = "<a class='ConfirmAjaxWR' msg='Are you sure to delete domain : ". $data->domain ."' ";
                $data->actUrl .= "href='" . $this->url("task=confirm-response&act=skillDomainDel&pro=del&sid=".$sid.'&dmn='.urlencode(base64_encode($data->domain)))."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionEmailSkillKeyword(){
        include('model/MEmail.php');
        include('model/MSkill.php');
        //$mainobj = new MEmail();
        $skill_model = new MSkill();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $templateinfo = $skill_model->getSkillById($sid);
        if (empty($templateinfo) || $templateinfo->qtype != 'E') exit();

        $this->pagination->num_records = MEmail::numSkillKeyword($sid);
        $keywords = $this->pagination->num_records > 0 ? MEmail::getSkillKeyword($sid, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $result=&$keywords;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->titleUrl = "";
                $titleClass = "";

                $titleUrl = $this->url("task=email&act=update-skill-keyword&sid=".$data->skill_id.'&kwd='.urlencode(base64_encode($data->keyword)));
                $data->update = "<a ".$titleClass." href='".$titleUrl."'>".$data->keyword."</a>";
                $data->actUrl = "<a class='ConfirmAjaxWR' msg='Are you sure to delete domain : ". $data->keyword ."' ";
                $data->actUrl .= "href='" . $this->url("task=confirm-response&act=skillKeywordDel&pro=del&sid=".$sid.'&kwd='.urlencode(base64_encode($data->keyword)))."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionViewAttachment() {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $tid = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
        $sl = !empty($_REQUEST['sl']) ? $_REQUEST['sl'] : "";
        $attachments = array();
        if ( !empty($tid) && !empty($sl)) {
            $attachments = $eTicket_model->getAttachments($tid, $sl);
        }
        //GPrint($result);die;
        $total_records = !empty($result)?count($result):0;
        $this->pagination->num_records = $total_records;
        $this->pagination->num_current_records = $total_records;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result = &$attachments;
        if (count($result)) {
            foreach ($result as $data) {
                $data->downloadUrl = "<a href='".$this->url('task=email&act=attachment&tid='.$data->ticket_id.'&sl='.$data->mail_sl.'&p='.$data->part_position)."' onclick='removeMainLoader()'>Download</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionAllAttachment() {
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $name = $email = $customer_id = "";
        if ($this->gridRequest->isMultisearch) {
            $name = $this->gridRequest->getMultiParam('name');
            $email = $this->gridRequest->getMultiParam('email');
            $customer_id = $this->gridRequest->getMultiParam('customer_id');
        }

        $this->pagination->num_records = $eTicket_model->numCustomer($name, $email, $customer_id);
        $customers = $this->pagination->num_records > 0 ? $eTicket_model->getCustomer($name, $email, $customer_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        //GPrint($eTickets);die;
        $this->pagination->num_current_records = is_array($customers) ? count($customers) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result = &$customers;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                //$sl = sprintf("%03d", $data->num_mails);
                $data->attachment_list_url = "<a class='' href='".$this->url('task=email&act=attachment-list&cid='.$data->customer_id)."'>Attachments</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    function actionAttachmentList(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
        //if ($this->gridRequest->isMultisearch) {
            $ticket_id = !empty($this->gridRequest->getMultiParam('ticket_id')) ? $this->gridRequest->getMultiParam('ticket_id') : "";
            $sbjt = !empty($this->gridRequest->getMultiParam('subject')) ? $this->gridRequest->getMultiParam('subject') : "";
            $file_name = !empty($this->gridRequest->getMultiParam('file_name')) ? $this->gridRequest->getMultiParam('file_name') : "";
        //}

        $this->pagination->num_records = $eTicket_model->numAttachmentByCustomerID($cid, $ticket_id, $file_name, $sbjt);
        $attachments = $this->pagination->num_records > 0 ? $eTicket_model->getAttachmentByCustomerID($cid, $ticket_id, $file_name, $sbjt, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($attachments) ? count($attachments) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        //GPrint($attachments);die;

        $result = &$attachments;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $subject_length = !empty($data->subject) ? strlen(base64_decode($data->subject)) : 0;
                $data->subject = $subject_length > 50 ? substr(base64_decode($data->subject), 0, 50)."..." : base64_decode($data->subject);
                $file_length = !empty($data->file_name) ? strlen($data->file_name) : null;
                $data->file_name = !empty($file_length)&& $file_length > 50 ? substr($data->file_name, 0, 50)."..." : $data->file_name;
                $data->downloadUrl = "<a href='".$this->url('task=email&act=attachment&tid='.$data->ticket_id.'&sl='.$data->mail_sl.'&p='.$data->part_position)."' onclick='removeMainLoader()'>Download</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionEmailReportOld () {
        include('lib/DateHelper.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmail();
        //$this->pagination->rows_per_page = 20;

        include('model/MSkillCrmTemplate.php');
//        $crm_dp_model = new MSkillCrmTemplate();
//        $dp_options = $crm_dp_model->getDispositionSelectOptions(true);
        $dp_options = $eTicket_model->getDispositionTreeOptions();
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
        $ticket_category_id = $subject = "";
        $customer_id = "";
        $waiting_duration = $skill_id = "";
        $in_kpi = "";
        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
            $emptyDate = true;
        }

        $dateTimeArray = array();
        $lastDateArray = array();
        $firstOpenDateArray = array();
        $closedDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            $firstOpenDateArray = $this->gridRequest->getMultiParam('first_open_time');
            $closedDateArray = $this->gridRequest->getMultiParam('closed_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))  || (isset($firstOpenDateArray['from']) && empty($firstOpenDateArray['from']))  || (isset($closedDateArray['from']) && empty($closedDateArray['from']))  ){
                $emptyDate = true;
            }
        }

        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);
        $firstopenDateInfo = DateHelper::get_cc_time_details($firstOpenDateArray, $emptyDate);
        $closedDateInfo = DateHelper::get_cc_time_details($closedDateArray, $emptyDate);

        /*if (isset($lastDateInfo->sdate) && !empty($lastDateInfo->sdate) && $allNew == "24" && !$this->gridRequest->isMultisearch){
            $curTime = time();
            $lastDateInfo->stime = date("H:i", $curTime);
            $lastDateInfo->etime = date("H:i", $curTime);

            $dateTimeInfo->stime = "";
            $dateTimeInfo->etime = "";
            $dateTimeInfo->sdate = "";
            $dateTimeInfo->edate = "";
            $dateTimeInfo->ststamp = "";
            $dateTimeInfo->etstamp = "";
        }*/
        //$dateTimeInfo->dbfield = "create_time";
        //$lastDateInfo->dbfield = "last_update_time";
        //GPrint($dateTimeInfo);die;
        if ($this->gridRequest->isMultisearch){
            $customer_id = $this->gridRequest->getMultiParam('customer_id');
            $waiting_duration = $this->gridRequest->getMultiParam('waiting_duration');
            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $in_kpi = $this->gridRequest->getMultiParam('in_kpi');

            $did = $this->gridRequest->getMultiParam('did');
            $status = $this->gridRequest->getMultiParam('status');
            $subject = $this->gridRequest->getMultiParam('subject');
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }
        //dd($skill_id);
        $utype = UserAuth::hasRole('agent');

        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }

        if ($etype == 'myjob') {
            $assignedId = UserAuth::getCurrentUser();
        }
        $isAgent = UserAuth::hasRole('agent');
        $this->pagination->num_records = $eTicket_model->numETicketNew($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id);

        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getETicketNew($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            $disposition_names = $this->getDispositionName($result);
            foreach ( $result as &$data ) {
                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                $data->last_update_time = date("Y-m-d H:i:s", $data->last_update_time);
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;

                //$data->subject = base64_decode($data->subject);
                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->first_open_time = $data->first_open_time!=0 ? date("Y-m-d H:i:s", $data->first_open_time) : '';
                $data->agent_id = $data->name.' - '.$data->agent_id;
                $data->closed_time = $data->closed_time != 0 ? date("Y-m-d H:i:s", $data->closed_time): '';
                $data->in_kpi = $data->in_kpi=='Y'?'Yes':'No';
                //$data->attachmentUrl = "<a class='' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                $data->rs_tr = $data->rs_tr=='Y' ? 'Yes':'No';
                $data->reschedule_time = !empty($data->reschedule_time) ? date("Y-m-d H:i:s", $data->reschedule_time) : "";
                $data->rs_tr_create_time = !empty($data->rs_tr_create_time) ? date("Y-m-d H:i:s", $data->rs_tr_create_time) : '';


                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $dotdot = strlen($str) > 30 ? "..." : "";
                $data->subject = !empty($str) ? substr($str,0,30).$dotdot : "(no subject)";
                //$data->waiting_duration = abs($data->waiting_duration);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function getDispositionName($result){
	    $response = array();
	    $disp = array();
	    if (!empty($result)){
            foreach ($result as $key)$disp[] = $key->disposition_id;
            if (!empty($disp)){
                $eTicket_model = new MEmail();
                $response = $eTicket_model->getEmailDispositionName($disp);
            }
        }
        return $response;
    }

    function actionEmailReport () {
        include('lib/DateHelper.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmail();

        //include('model/MSkillCrmTemplate.php');
        $dp_options = $eTicket_model->getAllEmailDispositions();
        //$dp_options = $eTicket_model->getDispositionChildrenOptions();

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
        $ticket_category_id = $subject = "";
        $customer_id = "";
        $waiting_duration = $skill_id = "";
        $in_kpi = "";
        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
            $emptyDate = true;
        }

        $dateTimeArray = array();
        $lastDateArray = array();
        $firstOpenDateArray = array();
        $closedDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            $firstOpenDateArray = $this->gridRequest->getMultiParam('first_open_time');
            $closedDateArray = $this->gridRequest->getMultiParam('close_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))  || (isset($firstOpenDateArray['from']) && empty($firstOpenDateArray['from']))  || (isset($closedDateArray['from']) && empty($closedDateArray['from']))  ){
                $emptyDate = true;
            }
        }

        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);
        $firstopenDateInfo = DateHelper::get_cc_time_details($firstOpenDateArray, $emptyDate);
        $closedDateInfo = DateHelper::get_cc_time_details($closedDateArray, $emptyDate);

        if ($this->gridRequest->isMultisearch){
            $customer_id = $this->gridRequest->getMultiParam('customer_id');
            $waiting_mins = $this->gridRequest->getMultiParam('waiting_duration');
            //$regex = '/^[0-9]{2}:[0-9]{2}||[0-9]{1}:[0-9]{2}||[0-9]{2}:[0-9]{1}||[0-9]{1}:[0-9]{1}$/';

            $waiting_mins = explode(":",$waiting_mins);
            if (!empty($waiting_mins[0])) $waiting_duration = $waiting_mins[0]*60;
            if (!empty($waiting_mins[1])) $waiting_duration += $waiting_mins[1];

            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $in_kpi = $this->gridRequest->getMultiParam('in_kpi');

            $did = $this->gridRequest->getMultiParam('did');
            $status = $this->gridRequest->getMultiParam('status');
            $subject = $this->gridRequest->getMultiParam('subject');

            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $utype = UserAuth::hasRole('agent');
        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }

        $isAgent = UserAuth::hasRole('agent');
        $this->pagination->num_records = $eTicket_model->numEmailReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id);

        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getEmailReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        $email_agents = [];
        $names = [];
        if(count($result) > 0){
            $disposition_names = $this->getDispositionName($result);
            foreach ($result as $key){
                if (!in_array($key->agent_1,$email_agents) && !empty($key->agent_1)){
                    $email_agents[] = $key->agent_1;
                }
                if (!in_array($key->agent_2,$email_agents) && !empty($key->agent_2)){
                    $email_agents[] = $key->agent_2;
                }
                if (!in_array($key->agent_3,$email_agents) && !empty($key->agent_3)){
                    $email_agents[] = $key->agent_3;
                }
            }
            $agent_model = new MAgent();
            $temp_agent_names = !empty($email_agents) ? $agent_model->getAllAgents($email_agents) : "";
            if (!empty($temp_agent_names)){
                foreach ($temp_agent_names as $item){
                    $names[$item->agent_id] = $item->name;
                }
            }

            foreach ( $result as &$data ) {
                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                $data->last_update_time = $data->last_update_time > 0 ? date("Y-m-d H:i:s", $data->last_update_time) : "";
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;

                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->first_open_time = $data->first_open_time!=0 ? date("Y-m-d H:i:s", $data->first_open_time) : '';
                //$data->waiting_duration = $data->waiting_duration > 0 ? gmdate("H:i:s", $data->waiting_duration) : '';
                $data->waiting_duration = $data->waiting_duration > 0 ? get_timestamp_to_hour_format($data->waiting_duration) : '';
                $data->agent_1 = !empty($names[$data->agent_1]) ? $names[$data->agent_1].' - '.$data->agent_1 : $data->agent_1;
                $data->agent_2 = !empty($names[$data->agent_2]) ? $names[$data->agent_2].' - '.$data->agent_2 : $data->agent_2;
                $data->close_time = $data->close_time != 0 ? date("Y-m-d H:i:s", $data->close_time): '';
                $data->in_kpi = $data->in_kpi=='Y'?'Yes':'No';
                //$data->attachmentUrl = "<a class='' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                $data->rs_tr = $data->rs_tr=='Y' ? 'Yes':'No';
                $data->reschedule_time = !empty($data->reschedule_time) ? date("Y-m-d H:i:s", $data->reschedule_time) : "";
                $data->rs_tr_create_time = !empty($data->rs_tr_create_time) ? date("Y-m-d H:i:s", $data->rs_tr_create_time) : '';
                //$data->open_duration = $data->open_duration > 0 ? gmdate("H:i:s", $data->open_duration) : '';
                $data->open_duration = $data->open_duration > 0 ? get_timestamp_to_hour_format($data->open_duration) : '';

                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $dotdot = strlen($str) > 100 ? "..." : "";
                $data->subject = !empty($str) ? substr($str,0,100).$dotdot : "(no subject)";
                //$data->waiting_duration = abs($data->waiting_duration);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionEmailActivityReport(){
        include('lib/DateHelper.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmail();

        $agentId = "";
        $status = $ticket_id = $supervisor_id = "";

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('activity_time');
            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $status = $this->gridRequest->getMultiParam('status');
            $ticket_id = $this->gridRequest->getMultiParam('ticket_id');
        }
        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, false);

        $utype = UserAuth::hasRole('agent');
        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }
        if (UserAuth::hasRole('supervisor')){
            $supervisor_id = UserAuth::getCurrentUser();
            if (empty($agentId) ){
                $temp_agent_id = array_keys($eTicket_model->getEmailAgents($supervisor_id));
                $agentId = implode("','", $temp_agent_id);
            }
        }
        //GPrint($agentId);die;
        $this->pagination->num_records = $eTicket_model->numEmailActivityReport( $agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id);
        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        if(count($result) > 0){
            $email_agents = [];
            foreach ($result as $key){
                if (!in_array($key->agent_id,$email_agents) && !empty($key->agent_id)){
                    $email_agents[] = $key->agent_id;
                }
            }
            $agent_model = new MAgent();
            $temp_agent_names = !empty($email_agents) ? $agent_model->getAllAgents($email_agents) : "";
            if (!empty($temp_agent_names)){
                foreach ($temp_agent_names as $item){
                    $names[$item->agent_id] = $item->name;
                }
            }

            foreach ( $result as &$data ) {
                $data->activity_time = date("Y-m-d H:i:s", $data->activity_time);
                $data->emailStatus = '';
                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->activity_details);
                $data->agent_id = !empty($names[$data->agent_id]) ? $names[$data->agent_id].' - '.$data->agent_id : $data->agent_id;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionEmailDayWiseReport() {
        include('lib/DateHelper.php');
        include('model/MEmail.php');
        include('model/MAgent.php');
        $eTicket_model = new MEmail();
//        $dp_options = $eTicket_model->getDispositionTreeOptions();

        $emptyDate = true;
//        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
//        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
//        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $tid = '';
        $email = '';
        $did = '';
        $agentId = "";
        $assignedId = "";
        $source = '';
        $sender_name = '';
        $ticket_category_id = $subject = "";
        $customer_id = "";
        $waiting_duration = $skill_id = "";
        $in_kpi = "";
//        if ((!empty($allNew) && $allNew == "Y") || $etype == 'myjob'){
//            $emptyDate = true;
//        }

        $dateTimeArray = array();
        $lastDateArray = array();
        $firstOpenDateArray = array();
        $closedDateArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('create_time');
            $lastDateArray = $this->gridRequest->getMultiParam('last_update_time');
            $firstOpenDateArray = $this->gridRequest->getMultiParam('first_open_time');
            $closedDateArray = $this->gridRequest->getMultiParam('close_time');
            if ((isset($dateTimeArray['from']) && empty($dateTimeArray['from'])) || (isset($lastDateArray['from']) && empty($lastDateArray['from']))  || (isset($firstOpenDateArray['from']) && empty($firstOpenDateArray['from']))  || (isset($closedDateArray['from']) && empty($closedDateArray['from']))  ){
                $emptyDate = true;
            }
        }

        $dateTimeInfo = DateHelper::get_cc_time_details($dateTimeArray, $emptyDate);
        $lastDateInfo = DateHelper::get_cc_time_details($lastDateArray, $emptyDate);
        $firstopenDateInfo = DateHelper::get_cc_time_details($firstOpenDateArray, $emptyDate);
        $closedDateInfo = DateHelper::get_cc_time_details($closedDateArray, $emptyDate);

        if ($this->gridRequest->isMultisearch){
            $customer_id = $this->gridRequest->getMultiParam('customer_id');
            $waiting_mins = $this->gridRequest->getMultiParam('waiting_duration');

            $waiting_mins = explode(":",$waiting_mins);
            if (!empty($waiting_mins[0])) $waiting_duration = $waiting_mins[0]*60;
            if (!empty($waiting_mins[1])) $waiting_duration += $waiting_mins[1];

            $agentId = $this->gridRequest->getMultiParam('agent_id');
            $in_kpi = $this->gridRequest->getMultiParam('in_kpi');

            $did = $this->gridRequest->getMultiParam('did');
            $status = $this->gridRequest->getMultiParam('status');
            $subject = $this->gridRequest->getMultiParam('subject');

            $skill_id = $this->gridRequest->getMultiParam('skill_id');
        }

        $utype = UserAuth::hasRole('agent');
        if ($utype){
            $agentId = UserAuth::getCurrentUser();
        }

        $isAgent = UserAuth::hasRole('agent');
        $this->pagination->num_records = $eTicket_model->numEmailReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id);

        $eTickets = $this->pagination->num_records > 0 ?
            $eTicket_model->getEmailReport($customer_id, $waiting_duration, $agentId, $lastDateInfo, $in_kpi, $dateTimeInfo, $did, $firstopenDateInfo, $closedDateInfo,  $status, $subject, $isAgent, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($eTickets) ? count($eTickets) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$eTickets;
        $email_agents = [];
        $names = [];
        if(count($result) > 0){
            $disposition_names = $this->getDispositionName($result);
            foreach ($result as $key){
                if (!in_array($key->agent_1,$email_agents) && !empty($key->agent_1)){
                    $email_agents[] = $key->agent_1;
                }
                if (!in_array($key->agent_2,$email_agents) && !empty($key->agent_2)){
                    $email_agents[] = $key->agent_2;
                }
                if (!in_array($key->agent_3,$email_agents) && !empty($key->agent_3)){
                    $email_agents[] = $key->agent_3;
                }
            }
            $agent_model = new MAgent();
            $temp_agent_names = !empty($email_agents) ? $agent_model->getAllAgents($email_agents) : "";
            if (!empty($temp_agent_names)){
                foreach ($temp_agent_names as $item){
                    $names[$item->agent_id] = $item->name;
                }
            }

            foreach ( $result as &$data ) {
                $data->create_time = date("Y-m-d H:i:s", $data->create_time);
                $data->last_update_time = $data->last_update_time > 0 ? date("Y-m-d H:i:s", $data->last_update_time) : "";
                $data->disposition_id = !empty($dp_options) && array_key_exists($data->disposition_id,$dp_options)?$dp_options[$data->disposition_id]:$data->disposition_id;

                $data->emailStatus = $eTicket_model->getTicketStatusLabel($data->status);
                $data->first_open_time = $data->first_open_time!=0 ? date("Y-m-d H:i:s", $data->first_open_time) : '';
                $data->waiting_duration = $data->waiting_duration > 0 ? gmdate("H:i:s", $data->waiting_duration) : '';
                $data->agent_1 = !empty($names[$data->agent_1]) ? $names[$data->agent_1].' - '.$data->agent_1 : $data->agent_1;
                $data->agent_2 = !empty($names[$data->agent_2]) ? $names[$data->agent_2].' - '.$data->agent_2 : $data->agent_2;
                $data->close_time = $data->close_time != 0 ? date("Y-m-d H:i:s", $data->close_time): '';
                $data->in_kpi = $data->in_kpi=='Y'?'Yes':'No';
                //$data->attachmentUrl = "<a class='' href='".$this->url('task=email&act=view-attachment&tid='.$data->ticket_id.'&sl='.$sl)."'>Attachments</a>";
                $data->rs_tr = $data->rs_tr=='Y' ? 'Yes':'No';
                $data->reschedule_time = !empty($data->reschedule_time) ? date("Y-m-d H:i:s", $data->reschedule_time) : "";
                $data->rs_tr_create_time = !empty($data->rs_tr_create_time) ? date("Y-m-d H:i:s", $data->rs_tr_create_time) : '';
                $data->open_duration = $data->open_duration > 0 ? gmdate("H:i:s", $data->open_duration) : '';

                $str = preg_replace(array('/[0-9]{10}/', '/\[\]/'),'',base64_decode($data->subject));
                $dotdot = strlen($str) > 100 ? "..." : "";
                $data->subject = !empty($str) ? substr($str,0,100).$dotdot : "(no subject)";
                //$data->waiting_duration = abs($data->waiting_duration);
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }



}
