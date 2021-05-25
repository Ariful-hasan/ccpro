<?php
require_once 'BaseTableDataController.php';
class GetVivrData extends BaseTableDataController
{

    function __construct()
    {
        parent::__construct();
    }

    function actionVivrs()
    {
        include('model/MIvr.php');
        $ivr_model = new MIvr();

        $ivrsData = $ivr_model->getIvrs('', 0, 100);
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result =& $ivrsData;
        if (count($result) > 0) {
            foreach ($result as &$data) {
                $data->ivr_name = "<a href='" . $this->url("task=vivr-panel&act=vivr-config&vivrid=" . $data->ivr_id) . "'>" . $data->ivr_name . "</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

}