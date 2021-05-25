<?php
class BaseTableDataController extends Controller
{

	public $gridRequest;	

	public $pagination;	
	
	public $tableResponse;

	function __construct()
    {
		parent::__construct ();
		include_once ('lib/Pagination.php');

		$this->pagination = new Pagination ();
		$this->gridRequest = new GridRequest ();

		if($this->gridRequest->isDownloadCSV){
		    $this->pagination->rows_per_page = 200000; //Maximum 200000 records will download for csv
		}	
		$this->tableResponse = new stdClass ();
		$this->tableResponse->page = $this->pagination->current_page;
		$this->tableResponse->total =$this->pagination->getTotalPageCount();
		$this->tableResponse->hideCol = [];
		$this->tableResponse->showCol = [];		
		$this->tableResponse->errMsg = '';
	}
	
	function &getTableResponse()
	{		
		return $this->tableResponse;
	}

	function ShowTableResponse($response=null)
    {
		if($this->gridRequest->isDownloadCSV){
			$cols=$this->gridRequest->getRequest("cols");
			$cols=(urldecode($cols));
			$cols=json_decode($cols);
			if((!empty($cols->action) && strtolower($cols->action)=="action" )){
				unset($cols->action);
			}

			if((!empty($cols->action2))){
				unset($cols->action2);
			}
            if (!empty($this->tableResponse->hideCol)) {
                foreach ($this->tableResponse->hideCol as $key => $value) {
                    unset($cols->$value);
                }
            }
			DownloadCSV($cols,$this->tableResponse,$this->gridRequest->filename."_".date('Y-m-d_H-i-s').".csv");			
		}
		else
		{
			if($this->tableResponse->total==1){ //double check
				$this->tableResponse->total=$this->pagination->getTotalPageCount();
			}
			die(json_encode($this->tableResponse, JSON_PARTIAL_OUTPUT_ON_ERROR));
		}	
	}
	function reportAudit($type='OR', $report_name='', $request_param=[])
    {
    	include_once ('model/MReportNew.php');
    	$report_model = new MReportNew();

    	if($this->gridRequest->isDownloadCSV){
    		$report_model->saveReportAuditRequest($type.'D::'.$report_name, $request_param);
    	}else{
    		// $report_model->saveReportAuditRequest($type.'S::'.$report_name, $request_param);
    	}
    }
}



