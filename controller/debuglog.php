<?php

class Debuglog extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MDebug.php');
		include('lib/Pagination.php');
		include('lib/DateHelper.php');
		
		$dateinfo = DateHelper::get_input_time_details(true);
		
		$debug_model = new MDebug();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&sdate=$dateinfo->sdate&edate=$dateinfo->edate&page=");
		$pagination->num_records = $debug_model->numDebugLog($dateinfo);
		$data['logs'] = $pagination->num_records > 0 ? 
			$debug_model->getDebugLog($dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Debug Log';
		$data['dateinfo'] = $dateinfo;
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('debug_log', $data);
	}

}
