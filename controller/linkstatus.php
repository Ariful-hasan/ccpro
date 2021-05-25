<?php

class Linkstatus extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		if (!$this->getTemplate()->ss7_link_status) exit;
		$filename='/usr/local/gplexcc/regsrvr/engine/link_monitor.php';
		if(file_exists($filename)){
			require($filename);		
			$status = E1_link_status();
			$data['pageTitle'] = 'Link Status';
		}else{
			$status=new stdClass();
			$data['pageTitle'] = 'Link Status <span class="text-danger">Error: Please check <strong>Link Monitor</strong> file path</span>';
		}
		/**
			sample data provided for status
			for the last row IN_SERVICE for green, others red
			for other rows check green color for green otherwise red
		*/
		//$status = array('E1-1:GREEN', 'E1-1:IN', 'E1-1:RED', 'Signaling Link Status:IN_S');
		$data['link_status_array'] = $status;		
		$this->getTemplate()->display('link_status', $data);
	}
	

}
?>