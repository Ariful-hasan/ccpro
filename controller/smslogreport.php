<?php

class Smslogreport extends Controller
{
    function __construct() {
        parent::__construct();
    }

    function init()
    {
        $data['pageTitle'] = 'Sms Log Report';
        $data['dataUrl'] = $this->url('task=get-sms-log-data&act=smsreport');
        $this->getTemplate()->display('sms_log_report', $data);
    }

    function actionSmsMessages(){
        include('model/MSmsReport.php');
        include('lib/DateHelper.php');

        $sms_report_model = new MSmsReport();
        $request = $this->getRequest();
        $session_id = $request->getRequest('session_id');
        $source = $request->getRequest('source');
        $sms_log = $sms_report_model->getSmsLogFromSession($session_id);
        $sms_detail_data = null;
        if (is_array($sms_log) && !empty($sms_log)){
            $sms_detail_data = $sms_log[0];
        }

        if ($request->isPost()) {
            $this->replyMsg($request, $sms_detail_data);
        }

        $sms_messages = $sms_report_model->getSmsMesseges($session_id);

        $data['pageTitle'] = 'Sms Messages';
        $data['sms_messages'] = $sms_messages;
        $data['session_id'] = $session_id;
        $data['agent_id'] = $sms_detail_data->agent_id;
        $data['call_id'] = $sms_detail_data->callid;
        if ($source == 'report') {
            $data['source'] = $source;
        }
        $this->getTemplate()->display_popup('sms_message', $data, true);
    }

    private function replyMsg($request, $sms_detail_data)
    {
        $agent_id = UserAuth::getCurrentUser();
        $sms_report_model = new MSmsReport();
        $reply_msg = $request->getRequest('reply_msg');
        $session_id = $request->getRequest('session_id');
        $phone_number = $sms_detail_data->phone_number;
        $did = $sms_detail_data->did;
        if (strlen($reply_msg) > 160) {
            $sms1 = substr($reply_msg, 0, 160);
            $sms2 = substr($reply_msg, 160);
        } else {
            $sms1 = $reply_msg;
            $sms2 = null;
        }
        $sms1 = base64_encode($sms1);
        if ($sms2 != null) {
            $sms2 = base64_encode($sms2);
        }

        if ($sms_report_model->logSmsOut($session_id, $phone_number, $sms1, $sms2, $did)) {
            if ($sms_report_model->logSmsMessage($reply_msg, $sms_detail_data, $agent_id)) {
                if ($sms_report_model->updateSmsDetail($session_id, $agent_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    function actionSmsDashboard()
    {
        include('model/MSmsReport.php');
        include('lib/DateHelper.php');

        $sms_report_model = new MSmsReport();

        $last_day_data = new stdClass();
        $all_data = new stdClass();
        $from_date = date("Y-m-d H:i:s", strtotime('-24 hours', time()));
        $to_date = date("Y-m-d H:i:s");

        $last_day_data->total_sms = $sms_report_model->getTotalSms($from_date, $to_date);
        $last_day_data->pending_sms = $sms_report_model->getPendingSms($from_date, $to_date);
        $last_day_data->served_sms = $sms_report_model->getServedSms($from_date, $to_date);

        $all_data->total_sms = $sms_report_model->getTotalSms();
        $all_data->pending_sms = $sms_report_model->getPendingSms();
        $all_data->served_sms = $sms_report_model->getSERvedSms();

        $data['pageTitle'] = 'SMS Dashboard';
        $data['last_day_data'] = $last_day_data;
        $data['all_data'] = $all_data;

        $this->getTemplate()->display('sms_dashboard', $data, true);
    }

    function actionCreateNewSms()
    {
        include('model/MSmsReport.php');
        include('lib/DateHelper.php');

        $sms_report_model = new MSmsReport();
        $request =  $this->getRequest();

        if ($request->isPost()) {
            $reply_msg = $request->getRequest('sms_body');
            $phone_numbers = $request->getRequest('phone_numbers');
            if ($phone_numbers == "" || $reply_msg= "") {
                $error_msg = "Phone number or sms body is empty";
            }else{
                $result = $this->sendNewSms($request, $sms_report_model);
                if($result){
                    $error_code = 200; //success
                    $error_msg = "SMS Served Successfully"; //success
                }else{
                    $error_msg = "SMS Served Failed";
                }
            }
        }

        $data['pageTitle'] = 'New SMS';
        $data['error_code'] = $error_code;
        $data['error_msg'] = $error_msg;
        $data['request'] = $request;
        $data['did'] = OUTBOUND_SMS_CLI;

        $this->getTemplate()->display('sms_create_new', $data, true);
    }

    private function sendNewSms($request, $sms_report_model)
    {
        $did = OUTBOUND_SMS_CLI;
        $reply_msg = $request->getRequest('sms_body');
        $phone_numbers = $request->getRequest('phone_numbers');
        if (strlen($reply_msg) > 160) {
            $sms1 = substr($reply_msg, 0, 160);
            $sms2 = substr($reply_msg, 160);
        } else {
            $sms1 = $reply_msg;
            $sms2 = null;
        }
        $sms1 = base64_encode($sms1);
        if ($sms2 != null) {
            $sms2 = base64_encode($sms2);
        }

        $phone_numbers = explode(",", $phone_numbers);

        $is_served = true;
        foreach ($phone_numbers as $phone_number) {
            $session_id = time();
            if (!$sms_report_model->logSmsOut($session_id, $phone_number, $sms1, $sms2, $did)) {
                $is_served = false;
            }else{
                $sms_report_model->addToAuditLog("New SMS", "S", UserAuth::getCurrentUser(), "New Sms Send");
            }
        }
        return $is_served;
    }

    function actionTemplates()
    {
        include('model/MSmsReport.php');
        include('lib/DateHelper.php');

        $sms_report_model = new MSmsReport();
        $sms_templates = $sms_report_model->getSmsTemplate();

//        $data['pageTitle'] = 'SMS Template';
//        $data['request'] = $this->getRequest();
        $data['sms_templates'] = $sms_templates;
        die(json_encode($sms_templates));

//        $this->getTemplate()->display_popup('sms_template_select', $data, false, false);
    }



}

