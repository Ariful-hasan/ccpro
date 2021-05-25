<?php
require_once 'BaseTableDataController.php';
class GetIvrReportData extends BaseTableDataController{
	function __construct() {
		parent::__construct();		
	}
	
	function actionServicereq()
	{
	    /* include('model/MIvrServiceReport.php');
	    include('model/MIvrService.php');
	    include('lib/Pagination.php');
	    include('lib/DateHelper.php');
	
	    $report_model = new MIvrServiceReport();
	    $ivr_model = new MIvrService();
	    $pagination = new Pagination();
	
	    $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
	    $dateinfo = DateHelper::get_input_time_details(true);
	    $errMsg = '';
	    $errType = 1;
	    $reportDays = UserAuth::getReportDays();
	    $repLastDate = "";
	    if (!empty($reportDays)){
	        $toDate = date("Y-m-d");
	        $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
	    }
	
	    $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=".$this->getRequest()->getActionName().
	        "&dcode=$dcode&sdate=$dateinfo->sdate&edate=$dateinfo->edate&page=");
	    $data['pageTitle'] = 'IVR Service Request';// . date("M d, Y", $dateinfo->ststamp);
	
	    if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
	        $errMsg = UserAuth::getRepErrMsg();
	        $errType = 1;
	        $pagination->num_records = 0;
	    }else {
	        $pagination->num_records = $report_model->numSurviceRequest($dateinfo, $dcode);
	    }
	
	    if (!isset($_REQUEST['download'])) $data['logs'] = $pagination->num_records > 0 ?
	    $report_model->getServiceRequest($dateinfo, $dcode, $pagination->getOffset(), $pagination->rows_per_page) : null;
	
	    $pagination->num_current_records = isset($data['logs']) && is_array($data['logs']) ? count($data['logs']) : 0;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['pagination'] = $pagination;
	    $data['dateinfo'] = $dateinfo;
	    $data['dcode'] = $dcode;
	    $data['request'] = $this->getRequest();
	    $data['dp_options'] = $ivr_model->getServiceOptions();
	    $data['side_menu_index'] = 'reports';
	    $this->getTemplate()->display('report_ivr_service_request', $data); */
	    
	    $result = null;
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionServicelog()
	{
	    include('model/MIvrServiceReport.php');
	    include('lib/DateHelper.php');
	    include('model/MIvrService.php');
	    include('model/MAgent.php');
	
	    $report_model = new MIvrServiceReport();
	    $ivr_model = new MIvrService();
	    $agent_model = new MAgent();
	    
	    $dateTimeArray = array();
	    $dateRange = '';
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('date_time');
	        //print_r($dateTimeArray);
	        $dateRange = isset($_POST['dateRange']) ? trim($_POST['dateRange']) : '';
	        //print_r($dateRange);
	        if ($dateRange == 'Last Month') {
	            $dateTimeArray['from'] = date("Y-m-d", strtotime("first day of previous month"));
	            $dateTimeArray['to'] = date("Y-m-d", strtotime("last day of previous month"));
	        } elseif($dateRange == 'Last Week') {
	            $previous_week = strtotime("-1 week +1 day");
	            $start_week = strtotime("last sunday midnight",$previous_week);
	            $end_week = strtotime("next saturday",$start_week);
	            $dateTimeArray['from'] = date("Y-m-d", $start_week);
	            $dateTimeArray['to'] = date("Y-m-d", $end_week);
	        }
	        
	    }
	    $dateinfo = DateHelper::get_cc_time_details($dateTimeArray, true);
	    //print_r($dateinfo);
	    $dcode = "";
	    $clid = "";
	    $alid = "";
	     
	    if ($this->gridRequest->isMultisearch){
	        $dcode = $this->gridRequest->getMultiParam('dcode');
	        $clid = $this->gridRequest->getMultiParam('clid');
	        $alid = $this->gridRequest->getMultiParam('ivr_id');
	    }
	
	    $reportDays = UserAuth::getReportDays();
	    $repLastDate = "";
	    if (!empty($reportDays)){
	        $toDate = date("Y-m-d");
	        $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
	    }
	    
	    $current_agent = UserAuth::getCurrentUser();
	    $skills = $agent_model->getAgentSkill($current_agent);
	    
	    if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
	        //$errMsg = UserAuth::getRepErrMsg();
	        //$errType = 1;
	        $this->pagination->num_records = 0;
	    }else {
	        $this->pagination->num_records = $report_model->numServiceLog($dateinfo, $dcode, $clid, $alid, $skills);
	    }

	    $result = $this->pagination->num_records > 0 ?
	    $report_model->getServiceLog($dateinfo, $dcode, $clid, $alid, $this->pagination->getOffset(), $this->pagination->rows_per_page, $skills) : null;
	    $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    if(count($result)>0){
	        AddModel('MIvrService');
	        $ivrServiceModel = new MIvrService();
	        $dispositions = $ivrServiceModel->getServiceOptions('',true);
	        $dbSuffix = UserAuth::getDBSuffix();
	        $agent_options = $agent_model->getAgentNames('', '', 0, 0, 'array');
	        foreach ( $result as &$data ) {
	            if ($data->status == 'S') $data->statusTxt = 'Served';
	            elseif ($data->status == 'B') $data->statusTxt = 'Bad Request';
	            elseif ($data->status == 'A') $data->statusTxt = 'Abandoned';
	            else $data->statusTxt = $data->status;
	            
	            $data->nick = isset($agent_options[$data->agent_id]) ? $agent_options[$data->agent_id] : $data->agent_id;
	            $data->date_time = date("Y-m-d H:i:s", $data->tstamp);
	            $data->servedTime = date("Y-m-d H:i:s", $data->served_time);
	            $data->disposition_code = !empty($dispositions[$data->disposition_code]) ? $dispositions[$data->disposition_code] : $data->disposition_code;
	            $data->caller_id = strlen($data->caller_id) > 10 ? substr($data->caller_id,strlen($data->caller_id) - 10) : $data->caller_id;
	            if ($dbSuffix == 'AD') {
	                
	                if ($data->disposition_code == 'EXTFPA' || $data->disposition_code == 'EXTFCL') {
	                    if (!isset($insurance_companies[$data->account_id])) {
	                        $insurance_companies[$data->account_id] = $report_model->AMAX_get_insurance_name($data->account_id);
	                    }
	                    $data->account_id = $insurance_companies[$data->account_id];
	                } elseif ($data->disposition_code == 'EXTFSC') {
	                    if (!isset($sales_centers[$data->account_id])) {
	                        $sales_centers[$data->account_id] = $report_model->AMAX_get_sales_center_name($data->account_id);
	                    }
	                    $data->account_id = $sales_centers[$data->account_id];
	                }
	            }
	        }
	    }
	    
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionServiceSummary()
	{
	    include('model/MIvrServiceReport.php');
	    include('lib/DateHelper.php');
	    include('model/MIvrService.php');
	    
	    $report_model = new MIvrServiceReport();
	    $ivr_model = new MIvrService();
	     
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('log_date');
	    }

            $dateRange = isset($_POST['dateRange']) ? trim($_POST['dateRange']) : '';
                //print_r($dateRange);
            if (!empty($dateRange)) {
                if ($dateRange == 'Last Month') {
                    $dateTimeArray['from'] = date("Y-m-d", strtotime("first day of previous month"));
                    $dateTimeArray['to'] = date("Y-m-d", strtotime("last day of previous month"));
                } elseif($dateRange == 'Last Week') {
                    $previous_week = strtotime("-1 week +1 day");
                    $start_week = strtotime("last sunday midnight",$previous_week);
                    $end_week = strtotime("next saturday",$start_week);
                    $dateTimeArray['from'] = date("Y-m-d", $start_week);
                    $dateTimeArray['to'] = date("Y-m-d", $end_week);
                }
            }

	    $dateinfo = DateHelper::get_cc_time_details($dateTimeArray, true);
	    $dcode = "";
	    
	    if ($this->gridRequest->isMultisearch){
	        $dcode = $this->gridRequest->getMultiParam('dcode');
	    }
	    
	    $reportDays = UserAuth::getReportDays();
	    $repLastDate = "";
	    if (!empty($reportDays)){
	        $toDate = date("Y-m-d");
	        $repLastDate = date('Y-m-d', strtotime($toDate. ' - '.$reportDays.' days'));
	    }
	     
	    if (!empty($repLastDate) && !empty($dateinfo->sdate) && $dateinfo->sdate < $repLastDate){
	        //$errMsg = UserAuth::getRepErrMsg();
	        //$errType = 1;
	        $this->pagination->num_records = 0;
	    }else {
	        $this->pagination->num_records = $report_model->numServiceSummary($dateinfo, $dcode);
	    }
	    
	    $result = $this->pagination->num_records > 0 ?
	    $report_model->getServiceSummary($dateinfo, $dcode, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    if(count($result)>0){
	        $dp_options = $ivr_model->getServiceOptions();
	        foreach ( $result as &$data ) {
	            $data->service_count = "<a href='".$this->url("task=ivrreport&act=servicelog&sdate=".$data->log_date."&dcode=".$data->disposition_code)."'>";
	            $data->service_count .= $data->num_services."</a>";
	            if (isset($dp_options[$data->disposition_code])) $data->disposition_code = $dp_options[$data->disposition_code];
	        }
	    }
	     
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
}
