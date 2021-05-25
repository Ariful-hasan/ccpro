<?php

class ClickToCall extends Controller
{
    private $shareKey;
    private $accountId;
    private $agentId;
    private $number;
    private $time;
    private $hash;
    private $skillOut;
    private $apiUrl;

    function __construct($number,$skillId)
    {
        parent::__construct();
        $this->shareKey = get_share_key();
        $this->agentId = UserAuth::getCurrentUser();
        $this->number = $number;
        $this->skillOut = $skillId;
    }

    function init()
    {
    }

    function actionClickToCallApi()
    {
        include('model/MSetting.php');
        $settingsModel = new MSetting();
        $result = $settingsModel->getCCSettings();
        $this->time = time();

        if (!empty($result) && !empty($this->shareKey)) {
            $this->accountId = $result->account_id;
            $jsonObject = $this->getParamObjectJson();
            $requestString = base64_encode($jsonObject);
            $this->apiUrl = get_click_to_call_url($requestString);

            return file_get_contents($this->apiUrl);
        }
        return null;
    }

    private function getParamObjectJson()
    {
        $strText = $this->accountId . $this->agentId . $this->number . "$this->time$this->shareKey";
        $this->hash = MD5($strText);
        $param_object = new stdClass();
        $param_object->account_id = $this->accountId;
        $param_object->agent_id = $this->agentId;
        $param_object->dial = $this->number;
        $param_object->time = $this->time;
        $param_object->hash = $this->hash;
        $param_object->skillout = $this->skillOut;
        return json_encode($param_object);
    }
}