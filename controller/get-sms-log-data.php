<?php
require_once 'BaseTableDataController.php';
class GetSmsLogData extends BaseTableDataController
{
    var $pagination;

    function __construct() {
        parent::__construct();
    }

    function actionSmsreport(){
        include('model/MSmsReport.php');
        include('lib/DateHelper.php');
        include('model/MAgent.php');
        include('model/MSkill.php');

        $sms_model = new MSmsReport();
        $agent_model = new MAgent();
        $skill_model = new MSkill();

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
        }
        $date_info = DateHelper::get_cc_time_details($dateTimeArray);

        $this->pagination->num_records = $sms_model->numSmsLog($date_info);

        $result = $this->pagination->num_records > 0 ?
            $sms_model->getSmsLog($date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->actUrl .= "<a title='Sms Messages' class='btn btn-success btn-xs lightboxWIF' href='" . $this->url("task=smslogreport&act=sms-messages&session_id=" . urlencode($data->session_id)) . "'><i class='fa fa-commenting-o'></i></a>";
                $data->skill_id = $skill_model->getSkillById($data->skill_id)->skill_name;
                if ($data->status_code == 'C') {
                    $data->status_code = "Closed";
                } elseif ($data->status_code == 'N') {
                    $data->status_code = "New";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

}
