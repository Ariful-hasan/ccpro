<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/10/2018
 * Time: 10:52 AM
 */

class EmailConfirmResponse extends Controller
{
    /**
     * @var AjaxConfirmResponse
     */
    public $_cresponse;
    public $pro;

    function __construct(){
        parent::__construct();
        $this->_cresponse = new AjaxConfirmResponse();
        $this->pro = $this->request->getGet("pro");
        if (empty($this->pro)) {
            $this->SetMessage("Parametter Error", false);
            $this->ReturnResponse();
        }
    }

    function init(){
        $this->SetMessage("Method doesn't exists", false);
        $this->ReturnResponse();
    }

    function ReturnResponse(){
        die(json_encode($this->_cresponse));
    }

    function SetMessage($msg, $isSuccess = false){
        $this->_cresponse->status = $isSuccess;
        $this->_cresponse->msg = $msg;
    }
    /* start codde here*/

    function actionMarkAsClosed(){
        $ticket_ids = !empty($_REQUEST['tids']) ? json_decode($_REQUEST['tids']) : "";
        if (!empty($ticket_ids)){
            include('model/MEmail.php');
            $eTicket_model = new MEmail();
            if ($eTicket_model->setMarkAsClosedForLogSession($ticket_ids, UserAuth::getCurrentUser())) {
                $this->SetMessage('Successfully Updated', true);
            }else {
                $this->SetMessage('Failed to Update', false);
            }
        }
        $this->ReturnResponse();
    }

    /*function actionDispositionStatus(){
        dd($_REQUEST);
        $disposition_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
        $status = !empty($_REQUEST['sts']) ? $_REQUEST['sts'] : "";
        if (!empty($disposition_id) && !empty($status)) {
            include('model/MEmail.php');
            $dc_model = new MEmail();
            if ($dc_model->changeDispositionStatus($disposition_id, $status)){
                $this->SetMessage('Successfully Updated', true);
            }
        }else{
            $this->SetMessage('Failed to Update', false);
        }
        $this->ReturnResponse();
    }*/
    function actionDispositionStatus(){
        $disposition_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
        $status = !empty($_REQUEST['sts']) ? $_REQUEST['sts'] : "";
        if (!empty($disposition_id) && !empty($status)) {
            include('model/MEmail.php');
            $dc_model = new MEmail();
            if ($dc_model->changeDispositionStatus($disposition_id, $status)){
                die(json_encode([
                    MSG_RESULT => true,
                    MSG_TYPE => MSG_SUCCESS,
                    MSG_MSG => 'Status has been updated successfully!'
                ]));
            }else{
                die(json_encode([
                    MSG_RESULT => false,
                    MSG_TYPE => MSG_ERROR,
                    MSG_MSG => 'Status has not been updated successfully!!'
                ]));
            }
        }else{
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'Incorrect Information!!'
            ]));
        }
    }
}