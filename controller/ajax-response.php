<?php

class AjaxResponse extends Controller
{
    public $ajaxResponseData;

    public function __construct()
    {
        parent::__construct();
    }

    public function setSuccessResponse($successMsg = MSG_SUCCESS, $code = 200, $data = array(), $errorData = array())
    {
        $this->ajaxResponseData = new stdClass();
        $this->ajaxResponseData->msg = $successMsg;
        $this->ajaxResponseData->status = true;
        $this->ajaxResponseData->code = $code;
        $this->ajaxResponseData->data = $data;
        $this->ajaxResponseData->errorData = $errorData;
    }

    public function setFailedResponse($failedMsg = MSG_ERROR, $code = 401, $data = array(), $errorData = array())
    {
        $this->ajaxResponseData = new stdClass();
        $this->ajaxResponseData->msg = $failedMsg;
        $this->ajaxResponseData->status = false;
        $this->ajaxResponseData->code = $code;
        $this->ajaxResponseData->data = $data;
        $this->ajaxResponseData->errorData = $errorData;
    }

    public function getResponseData()
    {
        return $this->ajaxResponseData;
    }

    public function printSuccessResponse($successMsg = MSG_SUCCESS, $code = 200, $data = array(), $errorData = array())
    {
        $this->setSuccessResponse($successMsg, $code, $data, $errorData);
        $this->printAjaxResponse();
    }

    public function printFailedResponse($failedMsg = MSG_ERROR, $code = 401, $data = array(), $errorData = array())
    {
        $this->setFailedResponse($failedMsg, $code, $data, $errorData);
        $this->printAjaxResponse();
    }

    private function printAjaxResponse()
    {
        echo json_encode($this->ajaxResponseData);
        exit();
    }
}