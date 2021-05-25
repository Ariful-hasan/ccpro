<?php
require_once 'BaseTableDataController.php';
class GetLankaData extends BaseTableDataController
{
    function __construct()
    {
        parent::__construct();
    }

    public function actionEmailTemplates()
    {
        include('model/MEmailTemplate.php');
        include('model/MEmail.php');
        $email_model = new MEmail();
        $et_model = new MEmailTemplate();

        $this->pagination->num_records = $et_model->numTemplates();
        $emails = $this->pagination->num_records > 0 ?
            $et_model->getTemplates($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($emails) ? count($emails) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$emails;
        if(count($result) > 0){
            foreach ( $result as &$data ) {
                $data->titleUrl = "";
                $titleClass = "";
                $titleUrl = $this->url("task=lanka&act=update-email-template&tid=".$data->tstamp);
              /*  if (UserAuth::hasRole('agent')){
                    $titleUrl = $this->url("task=emailtemplate&act=message&tid=".$data->tstamp);
                    $titleClass = "class='showDetails'";
                }*/
                if(!empty($titleUrl)){
                    $data->titleUrl = "<a ".$titleClass." href='".$titleUrl."'>".$data->title."</a>";
                }

                $data->status = $data->status=='Y' ? "<span class='text-success'>Enabled</span>" : "<span class='text-danger'>Disabled</span>";
                $data->disposition = "";

                if (!empty($data->disposition_id)) {
                    $path = $email_model->getDispositionPath($data->disposition_id);
                    if (!empty($path)) $data->disposition = $path . ' -> ';
                }
                $data->disposition .= $data->dc_title;
                $data->actUrl = "";
                if (!UserAuth::hasRole('agent')){
                    $data->actUrl .= "<a class='ConfirmAjaxWR' msg='Are you sure to delete disposition code : ". $data->tstamp ."' ";
                    $data->actUrl .= "href='" . $this->url("task=confirm-response&act=emailTempDel&pro=del&tstamp=".$data->tstamp)."'><span><i style='font-size: 16px;' class='fa fa-times-circle icon-danger'></i></span></a>";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}
