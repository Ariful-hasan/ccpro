<?php
require_once 'BaseTableDataController.php';
class GetSmsData extends BaseTableDataController
{
    function __construct()
    {
        parent::__construct();
    }





    public function actionRoutes()
    {

        include('model/MSms.php');


        $sms_model = new MSms();


        $this->pagination->num_records = $sms_model->countRoute();

        $result = $this->pagination->num_records > 0 ?
            $sms_model->getRoute($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            foreach ( $result as &$data )
            {
                $data->status = $data->status == 'A' ? "<b class='text-primary'> Active </b>" : "<b class='text-danger'>Inactive </b>";
                $data->action = "<a class='btn btn-primary btn-xs LightboxWIFR' href='". $this->url("task=sms&act=add-route&gw_id={$data->gw_id}")."'> <i class='fa fa-save'></i> Update</a>";
                $data->action .= " <a class='btn btn-danger btn-xs ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=sms-confirm-response&act=delete-sms-route&pro=del&prefix={$data->prefix}")."'> <i class='fa fa-trash'></i> Delete</a>";
            }
        }
        $responce->rowdata = $result;

        $this->ShowTableResponse();
    }



    public function actionGateways()
    {

        include('model/MSms.php');


        $sms_model = new MSms();


        $this->pagination->num_records = $sms_model->countGateway();

        $result = $this->pagination->num_records > 0 ?
            $sms_model->getGateway($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0)
        {
            $routes = $sms_model->getGatewaysAsKeyValue();
            foreach ( $result as &$data )
            {
                $data->route = !empty($routes[$data->gw_id]) ? $routes[$data->gw_id] : "";
                $data->status = $data->status == 'A' ? "<b class='text-primary'> Active </b>" : "<b class='text-danger'>Inactive </b>";
                $data->action = "<a class='btn btn-primary btn-xs LightboxWIFR' href='". $this->url("task=sms&act=update-api&gw_id={$data->gw_id}")."'> <i class='fa fa-save'></i> Update</a>";
                $data->action .= " <a class='btn btn-danger btn-xs ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=sms-confirm-response&act=delete-sms-gateway&pro=del&gw_id={$data->gw_id}")."'> <i class='fa fa-trash'></i> Delete</a>";

            }
        }

        $responce->rowdata = $result;

        $this->ShowTableResponse();
    }



}
