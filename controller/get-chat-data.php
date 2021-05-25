<?php
require_once 'BaseTableDataController.php';
class GetChatData extends BaseTableDataController
{
	var $pagination;
	
	function __construct() {
		parent::__construct();		
	}
	
	function actionChatdsinit(){
	    include('model/MChatTemplate.php');
	    include('model/MSkill.php');
	    $dc_model = new MChatTemplate();
	    $skill_model = new MSkill();
	
	    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
	    $templateinfo = $skill_model->getSkillById($sid);
	    if (empty($templateinfo) || $templateinfo->qtype != 'C') exit();
	    
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
	
	            $data->dispTitle = "<a href='".$this->url("task=chatdc&act=update&sid=$sid&did=".$data->disposition_id)."'>";
	            if (!empty($parent)) $data->dispTitle .= $parent . ' -> ';
	            $data->dispTitle .= $data->title;
	            $data->dispTitle .= "</a>";
	            $data->disposition_type = !empty($disposition_types[$data->disposition_type]) ? $disposition_types[$data->disposition_type] : $data->disposition_type;
	            $data->actUrl = "<a data-w='800' class='lightboxWIF' href='".$this->url("task=chatdscopy&did=".$data->disposition_id."&cptxt=".urlencode($copyText))."'><span><i style='font-size: 16px;' class='fa fa-copy icon-purple'></i></span></a> &nbsp; ";
	            // $data->actUrl .= "<a class='ConfirmAjaxWR' msg='Are you sure to delete disposition code : ". $data->disposition_id ."' ";
	            // $data->actUrl .= "href='" . $this->url("task=confirm-response&act=chatDsDel&pro=del&sid=$sid&dcode=".$data->disposition_id)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
                if ($data->status == "Y") {
                    $data->status = "<a data-w='800' class='lightboxWIF' href='" . $this->url("task=chatdc&act=change-status&did=" . $data->disposition_id . "&skill_id=" . $data->skill_id) . "' style='color: green;'> Active </a>";
                } elseif ($data->status == "N") {
                    $data->status = "<a data-w='800' class='lightboxWIF'  href='" . $this->url("task=chatdc&act=change-status&did=" . $data->disposition_id . "&skill_id=" . $data->skill_id) . "' style='color: red;'> Inactive </a>";
                }
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionChatpage(){
	    include('model/MChatTemplate.php');
	    include('model/MLanguage.php');
	    $et_model = new MChatTemplate();

	    $this->pagination->num_records = $et_model->numChatPages();
	    $emails = $this->pagination->num_records > 0 ?
	    $et_model->getChatPages($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$emails;
	    if(count($result) > 0){
	        $languages = MLanguage::getActiveLanguageListArray();
	        foreach ( $result as &$data ) {
	            $data->page_id_url = "<a href='".$this->url("task=chatpage&act=update&pid=".urlencode($data->page_id))."'>".$data->page_id."</a>";
        		$data->language = !empty($data->language) && isset($languages[$data->language]) ? $languages[$data->language] : $data->language;
        		//$data->status = $data->active=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
        		$data->statusTxt = "";
        		$data->actUrl = "";
        		if ($data->active == 'Y') {
        		    $data->actUrl = "<a title='Embedded Chat URL' class='btn btn-warning btn-xs lightbox' data-w='800px' href='".$this->url("task=chatpage&act=showEmbeddedUrl&pid=".urlencode($data->page_id))."'><i class='fa fa-external-link'></i></a> &nbsp; ";
        		    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to deactivate chat page " . $data->page_id . "?' ";
        		    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=chatpage&pro=activate&status=N&pid=".urlencode($data->page_id))."'><span class='text-success'>Active</span></a>";
        		} else {
        		    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to activate chat page " . $data->page_id . "?' ";
        		    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=chatpage&pro=activate&status=Y&pid=".urlencode($data->page_id))."'><span class='text-danger'>Inactive</span></a>";
        		}
        		$data->actUrl .= "<a title='Chat Service' class='btn btn-success btn-xs' href='".$this->url("task=chatpage&act=chatService&pid=".urlencode($data->page_id))."'><i class='fa fa-commenting-o'></i></a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}

	function actionChatservice(){
	    include('model/MChatTemplate.php');
	    $et_model = new MChatTemplate();

	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $this->pagination->num_records = $et_model->numChatServices("page_id='$page_id'");
	    $emails = $this->pagination->num_records > 0 ?
	    $et_model->getChatServices("page_id='$page_id'", $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$emails;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->service_name_url = "<a href='".$this->url("task=chatpage&act=updateChatService&pid=".urlencode($data->page_id))."&sid=".$data->service_id."'>".$data->service_name."</a>";
	            $data->statusTxt = "";
        		$data->actUrl = "";

        		if ($data->status == 'Y') {
        		    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to deactivate chat service " . $data->service_name . "?' ";
        		    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=chatservice&pro=activate&status=N&sid=".$data->service_id."&pid=".urlencode($data->page_id))."'>";
        		    $data->statusTxt .= "<span class='text-success'>Active</span></a>";
        		} else {
        		    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to activate chat service " . $data->service_name . "?' ";
        		    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=chatservice&pro=activate&status=Y&sid=".$data->service_id."&pid=".urlencode($data->page_id))."'>";
        		    $data->statusTxt .= "<span class='text-danger'>Inactive</span></a>";
        		}
        		$data->actUrl = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to Delete chat service " . $data->service_name . "?' ";
        		$data->actUrl .= "href='".$this->url("task=confirm-response&act=chatservice&pro=delete&isdel=Y&sid=".$data->service_id."&pid=".urlencode($data->page_id))."'><i class='fa fa-times'></i> Delete</a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionChatreport()
    {
        include('model/MChatTemplate.php');
        include('lib/DateHelper.php');
        $report_model = new MChatTemplate();

        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $disposition = "";
        $email = "";
        $contact_number = "";
        if ($this->gridRequest->isMultisearch){
            $disposition = $this->gridRequest->getMultiParam('disposition_id');
            $email = $this->gridRequest->getMultiParam('email');
            $contact_number = $this->gridRequest->getMultiParam('contact_number');
        }

        $this->pagination->num_records = $report_model->numChatLog($disposition, $dateinfo, $email, $contact_number);
        $callsData = $this->pagination->num_records > 0 ? $report_model->getChatLog($disposition, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page, $email, $contact_number) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$callsData;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->tstamp = date("Y-m-d H:i:s", $data->tstamp);
                $data->url = $data->url == 'undefined' || empty($data->url) ? '-' : $data->url;
                $data->url_duration = DateHelper::get_formatted_time($data->url_duration);

                $file_timestamp = substr($data->callid, 0, 10);
                $yy = date("y", $file_timestamp);
                $mm = date("m", $file_timestamp);
                $dd = date("d", $file_timestamp);

                $sip_file = $this->getTemplate()->sip_logger_path . "siplog/$yy/$mm/$dd/" . $data->callid . ".sip";
                if (file_exists($sip_file)) {
                    $data->history = "<a title='Chat History' data-h='400px' data-w='600px' class='lightboxWIFR' href=\"index.php?task=cdr&act=sip-log&cid=$data->callid\"><i class=\"fa fa-comments\"></i></a>";
                } else {
                    $data->history = '-';
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    
    function actionChatRecordCount()
    {
        include('model/MChatTemplate.php');
        include('lib/DateHelper.php');
        $report_model = new MChatTemplate();
         
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        $disposition = "";
         
        if ($this->gridRequest->isMultisearch){
            $disposition = $this->gridRequest->getMultiParam('disposition_id');
        }
         
        $this->pagination->num_records = $report_model->numChatRecordCount($disposition, $dateinfo);
        $callsData = $this->pagination->num_records > 0 ? $report_model->getChatRecordCount($disposition, $dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
    
        $total_crm_records = $this->pagination->num_records > 0 ? $report_model->getTotalCrmRecordCount($disposition, $dateinfo) : 0;
        
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
    
        $result=&$callsData;
         
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->percentage = $total_crm_records > 0 ? sprintf("%2d", $data->numrecords*100/$total_crm_records) : '-';
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    
    function actionChatAvgCount()
    {
        include('model/MChatTemplate.php');
        include('lib/DateHelper.php');
        $report_model = new MChatTemplate();
         
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
         
        $this->pagination->num_records = $report_model->numChatTimeAvg($dateinfo);
        $callsData = $this->pagination->num_records > 0 ? $report_model->getChatTimeAvg($dateinfo, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;    
        $result=&$callsData;
         
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->avg_duration = $data->tduration > 0 && $data->numrecords > 0 ? ($data->tduration/$data->numrecords) : 0;
                $data->avg_duration = DateHelper::get_formatted_time($data->avg_duration);
                $data->url = $data->url == 'undefined' ? '-' : $data->url;
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    
    function actionChattemplate(){
        include('model/MChatTemplate.php');
        $et_model = new MChatTemplate();
    
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
                $titleUrl = $this->url("task=chattemplate&act=update&tid=".$data->tstamp);
                if (UserAuth::hasRole('agent')){
                    $titleUrl = $this->url("task=chattemplate&act=message&tid=".$data->tstamp);
                    $titleClass = "class='showDetails'";
                }
                if(!empty($titleUrl)){
                    $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
                }
    
                $data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
                $data->message = str_replace(array('<br/>', '<br>', '<br />'), ' ', $data->message);
                $data->message = stripslashes($data->message);
                $data->message = strlen($data->message) > 100 ? substr($data->message, 0, 100)."..." : $data->message;
    
                $data->actUrl = "";
                if (!UserAuth::hasRole('agent')){
                    $data->actUrl = "<a href='".$this->url("task=chattemplate&act=copy&tid=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-copy icon-purple'></i></span></a> &nbsp; ";
                    $data->actUrl .= "<a class='ConfirmAjaxWR' msg='Are you sure to delete template : ". $data->title ."' ";
                    $data->actUrl .= "href='" . $this->url("task=confirm-response&act=chatTempDel&pro=del&tstamp=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    
    function actionChatAgentRating()
    {
        include('lib/DateHelper.php');
        include('model/MChatTemplate.php');
	    $template_model = new MChatTemplate();
    
        $dateTimeArray = array();
        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $dateinfo = DateHelper::get_cc_time_details($dateTimeArray);
        
        if ($this->gridRequest->isMultisearch){
            $aid = $this->gridRequest->getMultiParam('agent_option_id');
        }

        $callsData = $template_model->getChatRatingData($dateinfo, $aid);
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $result = &$callsData;
        
        if(count($result) > 0){
            $totalVeryGood = 0;
            $totalGood = 0;
            $totalNormal = 0;
            $totalBad = 0;
            $totalVeryBad = 0;
            $totalNoRate = 0;
            $overAllTotalRate = 0;
            foreach ( $result as &$data ) {
                $totalChatUser = $data->numrecords;
                $totalRate = $data->very_bad + $data->bad + $data->normal + $data->good + $data->very_good;
                
                $data->very_bad_rating = $data->very_bad;
                $data->bad_rating = $data->bad / 2;
                $data->normal_rating = $data->normal / 3;
                $data->good_rating = $data->good / 4;
                $data->very_good_rating = $data->very_good / 5;
                
                $totalUserRated = $data->very_bad_rating + $data->bad_rating + $data->normal_rating + $data->good_rating + $data->very_good_rating;
                $data->no_rating = $totalChatUser - $totalUserRated;
                $average_rating = number_format(($totalRate/$totalUserRated), 2);
                $data->average_rating = '<input id="rating_'.$data->agent_id.'" class="rating form-control" data-show-caption="false" data-show-clear="false" value="'.$average_rating.'" type="number" data-size="sm" data-stars="5" readonly="readonly"> &nbsp; ('.$average_rating.')';
                
                $totalVeryGood = $totalVeryGood + $data->very_good_rating;
                $totalGood = $totalGood + $data->good_rating;
                $totalNormal = $totalNormal + $data->normal_rating;
                $totalBad = $totalBad + $data->bad_rating;
                $totalVeryBad = $totalVeryBad + $data->very_bad_rating;
                $totalNoRate = $totalNoRate + $data->no_rating;
                $overAllTotalRate = $overAllTotalRate + $totalRate;
            }
            $totalRatedUsers = $totalVeryBad + $totalBad + $totalNormal + $totalGood + $totalVeryGood;
            $average_rating = number_format(($overAllTotalRate/$totalRatedUsers), 2);
            $dataObj = new stdClass();
            $dataObj->agent_id = "<font style='font-size:12px; font-weight:bold; width:100%; text-align:right; float:right;'>TOTAL</b> <i class='fa fa-arrow-right' style='font-size:12px'></i>";
            $dataObj->numrecords = "";
            $dataObj->very_good = "";
            $dataObj->good = "";
            $dataObj->normal = "";
            $dataObj->bad = "";
            $dataObj->very_bad = "";
            $dataObj->very_bad_rating = "<b>".$totalVeryBad."</b>";
            $dataObj->bad_rating = "<b>".$totalBad."</b>";
            $dataObj->normal_rating = "<b>".$totalNormal."</b>";
            $dataObj->good_rating = "<b>".$totalGood."</b>";
            $dataObj->very_good_rating = "<b>".$totalVeryGood."</b>";
            $dataObj->no_rating = "<b>".$totalNoRate."</b>";
            $dataObj->average_rating = "";//'<input id="rating_overall" class="rating form-control" data-show-caption="false" data-show-clear="false" value="'.$average_rating.'" type="number" data-size="sm" data-stars="5" readonly="readonly"> &nbsp; ('.$average_rating.')';;
            $result[] = $dataObj;
        }
        

        //var_dump($result);
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}
