<?php
require_once 'BaseTableDataController.php';
class GetCallTransferData extends BaseTableDataController
{	
	function __construct()
    {
		parent::__construct();		
	}	
	
	public function actionCallTransferSetting()
    {
		AddModel('MCallTransferSetting');
		$setting_model = new MCallTransferSetting();


		$this->pagination->num_records = $setting_model->numCallTransferSetting();
		$result = $this->pagination->num_records > 0 ?
	              $setting_model->getCallTransferSetting($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$this->pagination->num_current_records = is_array($result) ? count($result) : 0;
		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;


		if(count($result) > 0){
			foreach ( $result as &$data ) {

                $data->agents      = $data->agents == "Y" ? "<b class='text-primary'> Yes</b>" : "<b class='text-danger'> No</b>";
                $data->agents = "<a class='ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=call-transfer-confirm-response&act=update-permission&pro=permission&target=agents&user_type={$data->user_type}")."'> $data->agents </a>";

                $data->supervisors = $data->supervisors == "Y" ? "<b class='text-primary'> Yes</b>" : "<b class='text-danger'> No</b>";
                $data->supervisors = "<a class='ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=call-transfer-confirm-response&act=update-permission&pro=permission&target=supervisors&user_type={$data->user_type}")."'> $data->supervisors </a>";

                $data->skills      = $data->skills == "Y" ? "<b class='text-primary'> Yes</b>" : "<b class='text-danger'> No</b>";
                $data->skills = "<a class='ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=call-transfer-confirm-response&act=update-permission&pro=permission&target=skills&user_type={$data->user_type}")."'> $data->skills </a>";

                $data->ivrs        = $data->ivrs == "Y" ? "<b class='text-primary'> Yes</b>" : "<b class='text-danger'> No</b>";
                $data->ivrs = "<a class='ConfirmAjaxWR' oncompleted='reloadSite' msg='Are you sure?' href='". $this->url("task=call-transfer-confirm-response&act=update-permission&pro=permission&target=ivrs&user_type={$data->user_type}")."'> $data->ivrs </a>";

                $data->user_type   = $data->user_type == "S" ? "Supervisor" : "Agent";
			}
		}

		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}
}
