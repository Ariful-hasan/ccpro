<?php

class Lanka extends Controller
{
    /**
     * Lanka constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function init()
    {
        $data['topMenuItems'] = array(array('href'=>'task=lanka&act=add-email-template', 'img'=>'fa fa-envelope-o', 'label'=>'Add New Template'));
        $data['side_menu_index'] = 'lanka';
        $data['pageTitle'] = 'Email Templates';
        $data['dataUrl'] = $this->url('task=get-lanka-data&act=email-templates');
        $this->getTemplate()->display('lanka_email_templates', $data);
    }


    public function actionAddEmailTemplate()
    {
        $this->saveService();
    }

    public function actionUpdateEmailTemplate()
    {
        $tid = $this->getRequest()->getRequest('tid','');
        $this->saveService($tid);
    }

    public function actionDelEmailTemplate()
    {
        AddModel('MEmailTemplate');
        $et_model = new MEmailTemplate();

        $tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';
        $cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

        $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);

        if ($et_model->deleteTemplate($tstamp)) {
            $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>false, 'msg'=>' Template Deleted Successfully', 'redirectUri'=>$url));
        } else {
            $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>true, 'msg'=>'Failed to Delete Template', 'redirectUri'=>$url));
        }
    }

    protected function saveService($tstamp_code='')
    {
        include('model/MEmailTemplate.php');
        $et_model = new MEmailTemplate();
        include('model/MEmail.php');
        $email_model = new MEmail();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        if ($request->isPost()) {
            $service = $this->getSubmittedService($tstamp_code);
            $service->mail_body = !empty($service->mail_body) ? nl2br(base64_encode($service->mail_body)) : "";
            $errMsg = $this->getValidationMsg($service);

            if (empty($errMsg)) {
                $is_success = false;
                if (empty($tstamp_code)) {
                    if ($et_model->addTemplate($service)) {
                        $errMsg = 'Template added successfully !!';
                        $is_success = true;
                    } else {
                        $errMsg = 'Failed to add template !!';
                    }
                } else {
                    $oldtemplate = $this->getInitialService($tstamp_code, $et_model);
                    if ($et_model->updateTemplate($oldtemplate, $service)) {
                        $errMsg = 'Template updated successfully !!';
                        $is_success = true;
                    } else {
                        $errMsg = 'No change found !!';
                    }
                }

                if ($is_success) {
                    $errType = 0;
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
            }

        } else {
            $service = $this->getInitialService($tstamp_code, $et_model);
            if (empty($service)) {
                exit;
            }
        }
        include('model/MSkill.php');
        $skill_model = new MSkill();
        if (UserAuth::hasRole('admin')) {
            $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
        } else {
            $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
        }

        $data['service'] = $service;
        $data['tstamp_code'] = $tstamp_code;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['iscopy'] = false;
        $data['pageTitle'] = empty($tstamp_code) ? 'Add New Template' : 'Update Template';
        $data['skill_id'] = $skill_model->getSkillField("skill_id", $service->disposition_id);
        $data['disposition_ids'] = $email_model->getDispositionPathArray($service->disposition_id);
        $data['dispositions0'] = $email_model->getDispositionChildrenOptions($data['skill_id'], '');
        $data['email_model'] = $email_model;

        $data['side_menu_index'] = 'lanka';
        $data['smi_selection'] = 'lanka';
        $this->getTemplate()->display('lanka_email_template_form', $data);
    }

    function getInitialService($tstamp_code, $et_model)
    {
        $service = null;

        if (empty($tstamp_code)) {
            $service = new stdClass();
            $service->tstamp = "";
            $service->title = "";
            $service->mail_body = "";
            $service->disposition_id = "";
            $service->status = "Y";
        } else {
            $service = $et_model->getTemplateById($tstamp_code);
        }
        return $service;
    }

    function getSubmittedService($tstamp_code)
    {
        $posts = $this->getRequest()->getPost();
        $service = new stdClass();
        if (is_array($posts)) {
            foreach ($posts as $key=>$val) {
                $service->$key = trim($val);
            }
        }

        $did = '';
        for ($i=0;$i<=10; $i++) {
            $did1 = isset($_POST['disposition_id'.$i]) ? trim($_POST['disposition_id'.$i]) : '';
            if (!empty($did1)) $did = $did1;
            else break;
        }
        $service->disposition_id = $did;

        return $service;
    }

    function getValidationMsg($service)
    {
        if (empty($service->title)) return "Provide template title";
        if (empty($service->mail_body)) return "Provide template text";

        return '';
    }

    function actionCrm()
    {
        AddModel('MLanka');

        $lanka_model = new MLanka();
        $request = $this->getRequest();

        $data = [];
        $cli = $request->getGet('cli','');

        $data['cli']= $cli;
        $data['callid']= $request->getGet('callid','');
        $data['skill_id']= $request->getGet('skill_id','');
        $data['dispositions']= $lanka_model->getDispositionByCli($cli);

        if ($request->isPost()){
            $mobile = $request->getRequest('mobile','');
            $data['client']= $this->getBoInformation($mobile);
        }else{
            $data['client']= $this->getBoInformation($cli);
        }

        $this->getTemplate()->display_only('lanka_bangla_crm', $data);
    }

    function actionSmsTemplateOptions()
    {
        AddModel('MSkill');
        AddModel('MEmailTemplate');

        $et_model = new MEmailTemplate();
        $skill_model = new MSkill();

        $request = $this->getRequest();
        $data['msg_type'] = false; //Failed


        if ($request->isPost()){
            $destination = $request->getRequest('mobile','');
            $callid = $request->getRequest('callid','');
            $skill_id = $request->getRequest('skill_id','');
            $smsBody = $request->getRequest('sms','');
            $smsTemplate = $request->getRequest('title','');

            $response = $this->sendMobileSms($destination, $smsBody,$smsTemplate,$callid, $skill_id, $skill_model);

            if ($response){
                $data['msg'] = 'SMS Sent Successfully';
                $data['msg_type'] = true;
            }else{
                $data['msg'] = "Failed to sent SMS";
            }
        }

        $data['templates'] = $et_model->getSmsTemplates(0, 0, 'Y');
        $data['pageTitle'] = 'SMS Templates';
        $data['request'] = $request;
        $data['mobile'] = $request->getRequest('mobile','');
        $data['callid'] = $request->getRequest('callid','');
        $data['skill_id'] = $request->getRequest('skill_id','');
        $this->getTemplate()->display_popup('lanka_sms_template_options', $data, true);
    }

    protected function sendMobileSms($dnumber, $smsText, $smsTemplate, $callid='', $skill_id='', $skill_model = null)
    {
        AddModel('MSms');
        $sms_model = new MSms();
        $smsCounter = 0;
        $url = '';
       // $smsText = urlencode(strlen($smsText) > 250 ? substr($smsText, 0, 250) : $smsText);

        $gateways =  $sms_model->getGatewaysAsKeyValue();
        $gateway_id_object_key_value = [];

        if (empty($dnumber)) { return false; }

        $mobileNumber = preg_replace("/[^0-9]/","", $dnumber);
        $mobileNumber = strlen($mobileNumber) == 13 ? substr($mobileNumber,2) : $mobileNumber;
        $mobileNumberLength = strlen($mobileNumber);
        if (empty($mobileNumber) || $mobileNumberLength < 11 ) {
            return false;
        }

        $prefix = $mobileNumberLength == 11 ? substr($mobileNumber,0,3) : substr($mobileNumber,2,3);

        if (in_array("*",$gateways)){
            $gateways = array_flip($gateways);
            $gateway_id = $gateways['*'];
        }elseif(in_array($prefix,$gateways)){
            $gateway_id = array_search($prefix,$gateways);
        }elseif(in_array("**",$gateways)){   // fallback gateway if not common gateway and prefix not found
            $gateways = array_flip($gateways);
            $gateway_id = $gateways['**'];
        }else{
            return false;
        }

        if (array_key_exists($gateway_id, $gateway_id_object_key_value)) {
            $gateway_object = $gateway_id_object_key_value[$gateway_id];
        }else{
            $gateway_object = $sms_model->getApiByID($gateway_id);
            $gateway_id_object_key_value[$gateway_id] = $gateway_object;
        }


        $api = $gateway_object->api;

        if (empty($api)){
            return false;
        }

        list($connectionType, $api_id, $method) = explode(":", $api);


        if (!empty($api_id)){
            $url = $sms_model->prepareURLByApiId($api_id, $method, $mobileNumber, $smsText);
        }

        if ($this->sendSMSTApi($url)) {
            $saveCallId = explode("-", $callid);
            $saveCallId = $saveCallId[0];

            $skill_model->addSendSMSLog($saveCallId, $mobileNumber, $smsText, $smsTemplate,UserAuth::getUserID(),$skill_id);
            $smsCounter++;
        }

        return $smsCounter > 0;
    }

    function sendSMSTApi($url)
    {
        $headers=  ["content-type: application/x-www-form-urlencoded"];
        $response = $this->curlRequest($url, $headers);
        if (!empty($response)){
            $response = simplexml_load_string($response);
        }

        return !empty($response->ServiceClass->StatusText) && $response->ServiceClass->StatusText == "success";
    }



    protected function getValidEmail($emails_arr, $return_type="string"){
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

    function actionCreate()
    {
        AddModel('MLanka');
        include_once('conf.email.php');

        $eTicket_model = new MLanka();

        $err = '';
        $errType = 1;

        $name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $email = isset($_REQUEST['email']) ? $this->getValidEmail(trim($_REQUEST['email'])) : '';
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $mail_body = isset($_POST['mail_body']) ? trim($_POST['mail_body']) : '';


        $cc = isset($_POST['cc']) ? $this->getValidEmail($_POST['cc'],'array') : null;
        $bcc = isset($_POST['bcc']) ? $this->getValidEmail($_POST['bcc'],'array') : null;


        if ( isset($_POST['email'])) {
            $err = $this->validateCreateEmail($email, $title, $mail_body);

            if (empty($err)) {
                if ($eTicket_model->createNewEmail($name, $email, $cc, $bcc, $title, $mail_body, $_FILES['att_file'], $company_from_name, $company_from_email)) {
                    AddInfo('Email sent successfully !!');
                } else {
                    $err = 'Failed to create new Email !!';
                }
            }
        }


        $data['replace_text_pattern'] = $replace_text_pattern;
        $data['attachment_save_path'] = $attachment_save_path;

        $data['email_model'] = $eTicket_model;
        $data['email'] = $email;
        $data['title'] = $title;
        $data['mail_body'] = $mail_body;
        $data['errMsg'] = $err;
        $data['errType'] = $errType;
        $data['templates'] = $eTicket_model->getEmailTemplates();
        $data['request'] = $this->getRequest();

        $data['pageTitle'] = 'Send Email';
        $this->getTemplate()->display_popup('lanka_email_create', $data,true); //////hiding side menu bar
    }

    function validateCreateEmail($email, $title, $mail_body)
    {
        if (empty($title)) return 'Email address is required';
        if (empty($title)) return 'Email title is required';
        if (empty($mail_body)) return 'Mail body is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Provide valid email address';

        return '';
    }


    protected function validateEmail($string)
    {
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $string, $matches);
        return $matches[0][0];
    }

    public function sendMail($recipientName='', $recipientEmail, $subject, $body, $cc, $bcc, $attachment)
    {
        include_once('lib/phpmailer/autoload.php');

        $mail = new \PHPMailer\PHPMailer\PHPMailer();

        $mail->IsSMTP();
        $mail->Timeout = 30;
        $mail->WordWrap = 80;
        $mail->SMTPKeepAlive = true;
        $mail->SMTPAuth = true;
        $mail->Port = 25;
        $mail->Host = 'smtp.mailtrap.io';
        $mail->Username = 'a93e309ef89c01';
        $mail->Password = 'a573916b5d778f';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';

        $mail->AddReplyTo('chayan.bawn@genuitysystems.com', 'Chayan Bawn');
        $mail->From = 'chayan.bawn@genuitysystems.com';
        $mail->FromName = 'Chayan Bawn';
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->AddAddress('95acbf7f13-050d9b@inbox.mailtrap.io');
        $mail->Body = $body;

        if (!empty($attachment)){
            foreach ($attachment['att_file']['error'] as $error){
                if ($error != 0 ){
                    return false;
                }
            }

            foreach ($attachment['att_file']['tmp_name'] as $path){
                try {
                    $mail->addAttachment($path, base64_encode($path));
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    return false;
                }
            }
        }

        try {
            return $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return false;
        }

    }

    /**
     * @return null|string
     */
    public function getToken(){

        $url = "https://ibroker.lbsbd.com:444/Token";
        $post_fields = "grant_type=password&username=LBSL_IVR&password=IVR_123@$!";
        $headers=  ["content-type: application/x-www-form-urlencoded"];

        $data = $this->curlRequest($url,$headers,true,$post_fields);

        $data = !empty($data) ? json_decode($data) : null;
        return !empty($data->access_token) ? $data->access_token : null;
    }

    /**
     * @param string $client_id
     * @return array|mixed
     */
    protected function getBoInformation($client_id = '')
    {
        if (empty($client_id)){
            return new stdClass();
        }

        $token = $this->getToken();
        if (strlen($client_id) >= 11){
            $url = "https://ibroker.lbsbd.com:444/Help/Api/GET-api-IVRHome-GetInvestorByMobile-mobile/".$client_id;
        }else{
            $url = "https://ibroker.lbsbd.com:444/api/IVRHome/GetInvestor/".$client_id;
        }


        $headers =  ["authorization: Bearer {$token}"];

        $result = $this->curlRequest($url, $headers);

        return json_decode($result);
    }


    /**
     * @param $url
     * @param array $request_headers
     * @param bool $post_request
     * @param string $post_data
     * @return string
     */
    protected function curlRequest($url, $request_headers = [], $post_request = false, $post_data='')
    {
       // $url = "https://api.mobireach.com.bd/SendTextMultiMessage?Username=lbfltest&Password=Dcba%40321&From=01841116325&To=01521508655&Message=%E0%A7%A9%E0%A7%AE%20%E0%A6%9F%E0%A6%BE%E0%A6%95%E0%A6%BE%E0%A7%9F%20%E0%A7%A9GB%20%E0%A6%87%E0%A6%A8%E0%A7%8D%E0%A6%9F%E0%A6%BE%E0%A6%B0%E0%A6%A8%E0%A7%87%E0%A6%9F%20%2C%20%E0%A6%AE%E0%A7%87%E0%A7%9F%E0%A6%BE%E0%A6%A6%20%E0%A7%A8%20%E0%A6%A6%E0%A6%BF%E0%A6%A8%20%7C%20%E0%A6%B8%E0%A6%BE%E0%A6%A5%E0%A7%87%20%E0%A6%AA%E0%A6%BE%E0%A6%9A%E0%A7%8D%E0%A6%9B%E0%A7%87%E0%A6%A8%20%E0%A7%A8GB%20%E0%A6%8F%E0%A6%A8%E0%A6%BF%20%E0%A6%87%E0%A6%89%E0%A6%B8%20%E0%A6%8F%E0%A6%AC%E0%A6%82%20%E0%A7%A7%20GB%20%E0%A7%AAG%20%7C%20%E0%A6%85%E0%A6%AB%E0%A6%BE%E0%A6%B0%20%E0%A6%9F%E0%A6%BF%20%E0%A6%AA%E0%A7%87%E0%A6%A4%E0%A7%87%20%E0%A6%A1%E0%A6%BE%E0%A7%9F%E0%A6%BE%E0%A6%B2%20%E0%A6%95%E0%A6%B0%E0%A7%81%E0%A6%A8%20*121*038%20#";
        $headers = ["cache-control: no-cache"];
        $headers = array_merge($headers, $request_headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($post_request){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response =  trim(curl_exec($ch));
        $response =  trim(curl_exec($ch));
        $err = curl_error($ch);


        return !empty($err) ? null : $response;
    }


}
