<?php
require_once 'BaseTableDataController.php';

class UnattendedCdr extends BaseTableDataController
{
    private $unattendedCdrModel;
    private $errorResponseObject;
    private $results;

    function __construct()
    {
        parent::__construct();
        $this->includeClassFiles();
        $this->unattendedCdrModel = new MUnattended();
        $this->errorResponseObject = new AjaxResponse();
    }

    function init()
    {
        $this->actionUnattended();
    }

    private function includeClassFiles()
    {
        include("model/MUnattended.php");
        include("controller/ajax-response.php");
    }

    function actionUnattended()
    {
        include('model/MSkill.php');
        include('model/MIvr.php');

        $skillModel = new MSkill();
        $ivrModel = new MIvr();

        $ivrOptions = $ivrModel->getIvrOptions();
        $skillOptions = $skillModel->getSkillsTypeWithNameArray();

        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Unattended CDR';
        $data['ivr_options'] = $ivrOptions;
        $data['skill_list'] = $skillOptions;
        $data['skill_type'] = 'V';
        $data['type'] = UNATTENDED_CALLBACK_TYPE;
        $data['dataUrl'] = $this->url('task='.$this->request->getControllerName().'&act=unattended-cdr');
        $view = $this->request->getControllerName() . '/unattended_cdr';
        $this->getTemplate()->display($view, $data);
    }

    public function actionUnattendedCdr()
    {
        include('lib/DateHelper.php');
        include('model/MSkill.php');

        $cdr_model = new MUnattended();
        $skill_model = new MSkill();
        $skill_options = $skill_model->getSkillsTypeWithNameArray();
        $skill_type = 'V';
        $type = '*';
        $date_from = date("Y-m-d");
        $date_to = date("Y-m-d");

        //read user wise date range and hide col
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName() . '_' . $this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }
        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_from = !empty($date_range['from']) ? date("Y-m-d", strtotime($date_range['from'])) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? date("Y-m-d", strtotime($date_range['to'])) : date('Y-m-d');
            $hour_from = !empty($date_range['from']) ? date("H", strtotime($date_range['from'])) : "00";
            $hour_to = !empty($date_range['to']) ? date("H", strtotime($date_range['to'])) : "23";
            $skill_id = $this->gridRequest->getMultiParam('skill_id');
            $msisdn = $this->gridRequest->getMultiParam('msisdn');
            $did = $this->gridRequest->getMultiParam('did');
            $type = $this->gridRequest->getMultiParam('type');
        }

        $dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, $hour_from, $hour_to, '', '-1 second');

        if (empty($dateinfo->errMsg)) {
            $this->pagination->num_records = $cdr_model->numUnattendedCdr($dateinfo, $msisdn, $did, $skill_id, $type);
            $this->results = $this->pagination->num_records > 0 ?
                $cdr_model->getUnattendedCdr($dateinfo, $msisdn, $did, $skill_id, $type, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;
            $this->modifyUnattendedCdrResult($skill_options, $skill_type);
            $response = $this->getTableResponse();
            $response->records = $this->pagination->num_records;
            $response->hideCol = array_merge($response->hideCol, $report_hide_col);
            $response->rowdata = $this->results;
            $this->ShowTableResponse();
        }
    }

    private function modifyUnattendedCdrResult($skill_options, $skill_type)
    {
        foreach ($this->results as $data) {
            if ($data->language == 'BN') {
                $data->language = 'Bengali';
            } elseif ($data->language == 'EN') {
                $data->language = 'English';
            }
            if ($data->status == 'A') {
                $data->status = '<b class="text-danger">Abandoned</b>';
            } elseif ($data->status == 'I') {
                $data->status = '<b class="text-warning">In Progress</b>';
            }
            $data->disc_party = get_disc_party($data->disc_party);
            $data->skill = $skill_options[$skill_type][$data->skill_id];
//            $data->click_to_call = "<a title='Click To Call' class='ConfirmAjaxWR btn btn-success btn-xs' msg='Are you sure that you want to call 0" . $data->cli . "?' href='" . $this->url("task=unattended-cdr&act=click-to-call&callid=" . $data->callid) . "'><i class='fa fa-phone'></i> Call </a>";
            $data->click_to_call .= "<a class='btn btn-xs btn-success confirm-status-link' onclick='confirm_status(event)' 
                                      data-msg='Are you sure that you want to call 0" . $data->cli . "?' 
                                      data-href='" . $this->url("task=unattended-cdr&act=click-to-call&callid=" .
                                      $data->callid) . "' title='Call'> <i class='fa fa-phone'></i> Call </a>";

            $data->remove = "<a title='Manual Remove' class='btn btn-danger btn-xs lightboxWIF' href='" .
                            $this->url("task=unattended-cdr&act=manually-remove&callid=" . $data->callid) .
                            "'><i class='fa fa-user-times'></i> Remove </a>";
        }
    }

    function actionClickToCall()
    {
        include("controller/click-to-call.php");

        $callInfo = $this->getUnattendedCdrFromRequest();
        $phoneNumber = $callInfo->cli;
        $phoneNumber = '0' . substr($phoneNumber, -10);
        $apiObject = new ClickToCall($phoneNumber, $callInfo->skill_id);
        $apiResult = $apiObject->actionClickToCallApi();

        if ($apiResult != null && $apiResult == "200 OK") {
            $result = $this->unattendedCdrModel->updateCallStatus($callInfo->callid, 'I', $callInfo->type);
            if ($result) {
                $this->errorResponseObject->printSuccessResponse("Call Successful!");
            }
        }
        $this->errorResponseObject->printFailedResponse("Call Unsuccessful!");
    }

    private function getUnattendedCdrFromRequest()
    {
        $callId = !empty($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
        if ($callId == "") {
            $this->errorResponseObject->printFailedResponse("Call Unsuccessful!");
        }
        $callInfo = $this->unattendedCdrModel->getUnattendedCdrInfo($callId);
        if (is_array($callInfo) && count($callInfo) > 0) {
            $callInfo = reset($callInfo);
            if ($callInfo->status == 'A' || $callInfo->status == 'C')
                return $callInfo;
        }
        $this->errorResponseObject->printFailedResponse("Call Unsuccessful!");
    }

    /* after solution of callid, this function will be done
        function actionSaveOutboundData()
        {
            $this->setUnattendedCdrModel();
            $callId = getOutboundDataFromRequest();

            $currentDateTime = date("Y-m-d H:i:s");
            $this->printAjaxResponse();
        }

        private function getOutboundDataFromRequest()
        {
            $outboundCallId = !empty($_REQUEST['callid']) ? $_REQUEST['callid'] : "";
            if ($outboundCallId == "") $this->printAjaxResponse();
            return $outboundCallId;
        }
    */

    function actionManuallyRemove()
    {
        $templateId = UNATTENDED_CDR_DISPOSITION_TEMPLATE;
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->removeCdr($request)) {
                $data['error'] = 'success';
            } else {
                $data['error'] = 'failed';
            }
        }
        $callId = $request->getRequest('callid');
        $data['pageTitle'] = 'Manual Remove';
        $data['callId'] = $callId;
        $data['dispositionList'] = $this->unattendedCdrModel->getRemovingDispositions($templateId);

        $view = $this->request->getControllerName() . '/manual_remove';
        $this->getTemplate()->display_popup($view, $data, true);
    }

    private function removeCdr($request)
    {
        $callId = $request->getRequest('callid');
        $dispositionId = $request->getRequest('dispositionId');
        $comment = addslashes($request->getRequest('comment'));
        return $this->unattendedCdrModel->updateManualDisposition($callId, $dispositionId, $comment);
    }
}