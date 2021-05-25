<?php
require_once 'BaseTableDataController.php';
class GetIvrBranchData extends BaseTableDataController
{	
	function __construct()
    {
		parent::__construct();		
	}	
	
	public function actionGetTitles()
    {
		include('model/MIvrBranch.php');
		$ivr_branch_model = new MIvrBranch();


		$this->pagination->num_records = $ivr_branch_model->countAll();
		$result = $this->pagination->num_records > 0 ?
            $ivr_branch_model->all($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$this->pagination->num_current_records = is_array($result) ? count($result) : 0;
		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;


		if(is_array($result) && count($result) > 0){
			foreach ( $result as &$data ) {
                $data->title = "<a class='lightboxIF cboxElement' href='". $this->url('task=ivr-branch&act=update&title_id='.$data->title_id)."'> ". $data->title ."</a>";
			}
		}

		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}
}
