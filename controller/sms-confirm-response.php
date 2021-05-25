<?php

class SmsConfirmResponse extends Controller
{
    /**
     * @var AjaxConfirmResponse
     */
    private $response;
    public $pro;
    
    public function __construct()
    {
        parent::__construct();

        $this->response = new AjaxConfirmResponse();
        $this->pro = $this->request->getGet("pro");
        if (empty($this->pro)) {
            $this->SetMessage("Parameter Error", false);
            $this->ReturnResponse();
        }
    }

    public function init()
    {
        $this->SetMessage("Method doesn't exists", false);
        $this->ReturnResponse();
    }

    public function ReturnResponse()
    {
        die(json_encode($this->response));
    }

    public function SetMessage($msg, $isSuccess=false)
    {
        $this->response->status=$isSuccess;
        $this->response->msg=$msg;
    }

    
    public function actionDeleteSmsRoute()
    {
        if ($this->pro=="del") {
            include('model/MSms.php');
            $sms_model = new MSms();
            $prefix = isset($_REQUEST['prefix']) ? trim($_REQUEST['prefix']) : '';

            $this->SetMessage('Failed to Delete Sms Route', false);
            
            if ($sms_model->deleteRoute($prefix)) {
                $this->SetMessage('Sms Route Deleted Successfully', true);
            }
        }
        $this->ReturnResponse();
    }


    public function actionDeleteSmsGateway()
    {
        if ($this->pro=="del") {
            include('model/MSms.php');
            $sms_model = new MSms();
            $gateway_id = isset($_REQUEST['gw_id']) ? trim($_REQUEST['gw_id']) : '';

            $this->SetMessage('Failed to Delete Sms Gateway', false);

            if ($sms_model->deleteGateway($gateway_id)) {
                $this->SetMessage('Sms Gateway Deleted Successfully', true);
            }
        }
        $this->ReturnResponse();
    }
}
