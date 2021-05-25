<?php

class Sms extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function actionRoutes()
    {
        $data['pageTitle'] = 'SMS Routes';
        $data['topMenuItems'] = [['href'=>'task=sms&act=gateways', 'img'=>'fa fa-user', 'label'=>'Gateways'], ['href'=>'task=sms&act=add-route', 'img'=>'fa fa-user','dataattr'=>array('w'=>500,'h'=>350), 'class'=>'LightboxWIFR cboxElement', 'label'=>'Add Route']];
        $data['dataUrl'] = $this->url('task=get-sms-data&act=routes');
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] ='sms_';
        $this->getTemplate()->display('sms_route_list', $data);
    }



    public function actionGateways()
    {

        $data['pageTitle'] = 'SMS Api List';
        $data['topMenuItems'] = [['href'=>'task=sms&act=routes', 'img'=>'fa fa-user', 'label'=>'Routes'], ['href'=>'task=sms&act=add-api', 'img'=>'fa fa-user', 'class'=>'LightboxWIFR', 'label'=>'Add Gateway']];
        $data['dataUrl'] = $this->url('task=get-sms-data&act=gateways');
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] ='sms_';
        $this->getTemplate()->display('sms_gateway_list', $data);
    }

    public function actionAddRoute()
    {
        require_once("model/MSms.php");

        $sms_model = new MSms();


        $data['pageTitle'] = 'Add Route';
        $data['request'] = $this->request->getRequest();
        $data['isAutoResize'] = true;



        $request = $this->request;
        $gw_id = $request->getRequest('gw_id','');
        $mainobj = $this->getInitialRoute($gw_id, $sms_model);

        if ($request->isPost()){

            $mainobj->prefix = $request->getRequest('prefix','');
            $mainobj->status = $request->getRequest('status','A');

            $response = !empty($mainobj->gw_id) ? $sms_model->updateRoute($mainobj->gw_id, $mainobj->prefix,$mainobj->status) : $sms_model->saveRoute($mainobj->prefix,$mainobj->status);


            if ($response){
                AddInfo("Operation Successful");
                $this->getTemplate()->display_popup_msg($data,true);
            }else{
                AddError("Operation failed !!, Please try again");
            }
        }

        $data['mainobj'] = $mainobj;

        $this->getTemplate()->display_popup('sms_route_create', $data, true);
    }


    private function getInitialRoute($gateway_id = "", $sms_model = null)
    {
        $route = new stdClass();
        $route->gw_id = '';
        $route->prefix = '';
        $route->status = '';

       return empty($gateway_id) ? $route : $sms_model->getRouteByID($gateway_id);
    }



    public function actionAddApi()
    {
        require_once("model/MSms.php");

        $sms_model = new MSms();

        $data['request'] = $this->request->getRequest();
        $data['gateways'] = $sms_model->getGatewaysAsKeyValue();

        $request = $this->request;
        $gw_id = $request->getRequest('gw_id','');

        $mainobj = $this->getInitialGateway($gw_id, $sms_model);

        if ($request->isPost()){

            $mainobj->gw_id = $gw_id;
            $mainobj->api = $request->getRequest('api','');
            $mainobj->status = $request->getRequest('status','');

            $response = empty($gw_id) ? $sms_model->updateGateway($mainobj->gw_id, $mainobj->api,$mainobj->status) : $sms_model->saveGateway($gw_id,$mainobj->api,$mainobj->status);


            if ($response){
                AddInfo("Operation Successful");
                $this->getTemplate()->display_popup_msg($data,true);
            }else{
                AddError("Operation failed !!, Please try again");
            }
        }

        $data['pageTitle'] = empty($gw_id) ? 'Add Api' : 'Update Api';
        $data['isAutoResize'] = true;
        $data['mainobj'] = $mainobj;
        $data['isUpdate'] = empty($mainobj->gw_id) ? false : true;

        $this->getTemplate()->display_popup('sms_gateway_create', $data, true);
    }

    public function actionUpdateApi()
    {
        require_once("model/MSms.php");

        $sms_model = new MSms();

        $data['request'] = $this->request->getRequest();
        $data['gateways'] = $sms_model->getGatewaysAsKeyValue();

        $request = $this->request;
        $gw_id = $request->getRequest('gw_id','');

        $mainobj = $this->getInitialGateway($gw_id, $sms_model);

        if ($request->isPost()){

            $mainobj->api = $request->getRequest('api','');
            $mainobj->status = $request->getRequest('status','A');

            $response = $sms_model->updateGateway($mainobj->gw_id, $mainobj->api,$mainobj->status);


            if ($response){
                AddInfo("Operation Successful");
                $this->getTemplate()->display_popup_msg($data,true);
            }else{
                AddError("Operation failed !!, Please try again");
            }
        }

        $data['pageTitle'] = 'Update Api';
        $data['isAutoResize'] = true;
        $data['mainobj'] = $mainobj;
        $data['isUpdate'] = empty($gw_id) ? false : true;

        $this->getTemplate()->display_popup('sms_gateway_create', $data, true);
    }



    private function getInitialGateway($gateway_id = "", $sms_model = null)
    {
        $route = new stdClass();
        $route->gw_id = '';
        $route->api = '';
        $route->status = '';

        return empty($gateway_id) ? $route : $sms_model->getApiByID($gateway_id);
    }

    /* =============== For SMS service from CRM =======================*/
    public function ActionGetUserSmsBySession()
    {
        require_once("model/MSms.php");
        $sms_model = new MSms();

        $cli = $this->getRequest()->getRequest('cli', '');

        $response = $sms_model->get_user_sms_by_session($cli);
		//log sms data
        $logData = json_encode($response);
        log_text(array("CLI:{$cli}", $logData), "temp/sms_log/");
        // log end
        echo is_array($response) ? die(json_encode($response)) : null;
    }

    public function ActionSendSmsToUser()
    {
        include_once('model/MSms.php');
        $sms_model = new MSms();

        $session_id = $this->getRequest()->getRequest('session_id', '');
        $call_id = $this->getRequest()->getRequest('callid', '');
        $sms = $this->getRequest()->getRequest('msg', '');
        $phone = $this->getRequest()->getRequest('phone_number', '');
        $did = $this->getRequest()->getRequest('did', '');
        $agent_id = UserAuth::getUserID();

        $response = $sms_model->send_sms_to_user($session_id, $call_id, $sms, $phone, $agent_id, $did);

        echo die(json_encode($response));
    }

    public function ActionGetSmsTemplates()
    {
        include_once('model/MSms.php');
        $sms_model = new MSms();
        $response = $sms_model->get_sms_templates($active_only = true);

        echo die(json_encode($response));
    }

    public function ActionCloseSMSSession()
    {
        $response = new stdClass();
        $response->status = false;

        include_once("model/MSms.php");
        include_once("conf.email.php");
        $sms_model = new MSms();

        $session_id = $this->getRequest()->getRequest('session_id', '');
        $call_id = $this->getRequest()->getRequest('callid', '');
        $disposition = $this->getRequest()->getRequest('disposition', '');

        $msg = [
            "method"=>"AG_EMAIL_CLOSE",
            "session_id"=>$session_id,
            "call_id"=>$call_id
        ];
        $udp_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';

        $r = $sms_model->save_sms_service_disposition($session_id, $disposition);
        $response->status = $sms_model->close_sms_session(json_encode($msg), $udp_port);

        echo die(json_encode($response));
    }


    public function ActionSendSMSPing()
    {
        include_once("model/MSms.php");
        include_once("conf.email.php");
        $sms_model = new MSms();

        $session_id = $this->getRequest()->getRequest('session_id', '');
        $call_id = $this->getRequest()->getRequest('call_id', '');
        $agent_id = UserAuth::getCurrentUser();//UserAuth::getUserID();

        $msg = [
            "method"=>"EMAIL_PING",
            "session_id"=>$session_id,
            "call_id"=>$call_id,
            "agent_id"=>$agent_id
        ];
	
	 $t = date("Y-m-d H:i:s");
        //file_put_contents("temp/sms-chat-ping.log", "=============={$t}======== \n", FILE_APPEND);
        //file_put_contents("temp/sms-chat-ping.log", "Data: ". json_encode($msg) ."\n", FILE_APPEND);
        //file_put_contents("temp/sms-chat-ping.log", "=========================== \n", FILE_APPEND);

        $udp_port = !empty($UDP_PORT) ? $UDP_PORT : '5186';

        $sms_model->sms_ping(json_encode($msg), $udp_port);
	
	$t = date("Y-m-d H:i:s");

        //file_put_contents("temp/sms-chat-ping.log", "Ping sent at : {$t} for session id: {$session_id} and call ID: {$call_id} \n", FILE_APPEND);
        //file_put_contents("temp/sms-chat-ping.log", "=========================== \n", FILE_APPEND);
        echo die("true");
    }
	
    public function ActionLogSmsFromJs()
    {
        $cli = $this->getRequest()->getRequest('phone_number', '');
        $counter = $this->getRequest()->getRequest('counter', '');
        $lastMsg = $this->getRequest()->getRequest('last_msg', '');

        //log sms data
        $logData = array("CLI:{$cli}", "Counter:{$counter}", "Last Msg:{$lastMsg}");
        log_text($logData, "temp/sms_log/");
        // log end
        die(json_encode(true)) ;
    }
}
