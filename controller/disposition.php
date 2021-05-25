<?php

class Disposition extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$data['dataUrl'] = $this->url('task=get-home-data&act=campaign-disposition');
		$data['pageTitle'] = 'Disposition Code';
		$data['side_menu_index'] = 'campaign';
		$data['topMenuItems'] = array(array('href'=>'task=disposition&act=add', 'img'=>'fa fa-plus-square-o', 'label'=>'Add New Disposition Code'));
		$this->getTemplate()->display('dispositions', $data);
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		if ($sid > '1000' && $sid < '2001') exit;
		$this->saveService($sid);
	}

	function actionDel()
	{
		include('model/MCrmDisposition.php');
		$dc_model = new MCrmDisposition();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		//if ($dcode > '1000' && $dcode < '2001') exit;
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($dc_model->deleteDispositionCode($dcode)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>false, 'msg'=>' Disposition Code Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>true, 'msg'=>'Failed to Delete  Disposition Code', 'redirectUri'=>$url));
		}
	}
	
	function saveService($dis_code='')
	{
		include('model/MCrmDisposition.php');
		$dc_model = new MCrmDisposition();
		
		$data['campaign_options'] = $dc_model->getCampaignOptions();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($dis_code);
			
			//var_dump($service);
			//exit;
			$errMsg = $this->getValidationMsg($service, $dis_code, $dc_model);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($dis_code)) {
					if ($dc_model->addDisposition($service, $data['campaign_options'])) {
						$errMsg = 'Disposition code added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add disposition code !!';
					}
				} else {
					$oldservice = $this->getInitialService($dis_code, $dc_model);
					if ($dc_model->updateDisposition($oldservice, $service)) {
						$errMsg = 'Disposition code updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($dis_code, $dc_model);
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['dis_code'] = $dis_code;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($dis_code) ? 'Add New Disposition Code' : 'Update Disposition Code'.' : ' . $dis_code;
		$data['side_menu_index'] = 'campaign';
		$data['smi_selection'] = 'disposition_';
		$this->getTemplate()->display('disposition_form', $data);
	}
	
	function getInitialService($dis_code, $dc_model)
	{
		$service = null;

		if (empty($dis_code)) {
			$service->campaign_id = "";
			//$service->disposition_id = "";
			$service->title = "";
		} else {
			$service = $dc_model->getDispositionById($dis_code);
		}
		return $service;
	}

	function getSubmittedService($dis_code)
	{
		$posts = $this->getRequest()->getPost();
		$service = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}

		//if (!empty($dis_code)) $service->disposition_code = $dis_code;

		return $service;
	}

	function getValidationMsg($service, $dis_code='', $dc_model)
	{
		if (empty($service->campaign_id)) return "Select campaign";
		//if (empty($service->disposition_id)) return "Provide disposition code";
		if (empty($service->title)) return "Provide disposition title";
		//if (!preg_match("/^[0-9a-zA-Z]{1,4}$/", $service->disposition_id)) return "Provide valid disposition code";
		if (!preg_match("/^[0-9a-zA-Z_ ]{1,20}$/", $service->title)) return "Provide valid disposition title";

		/*		
		if ($service->disposition_id != $dis_code) {
			$existing_code = $dc_model->getDispositionById($service->disposition_id);
			if (!empty($existing_code)) return "Disposition code $service->disposition_id already exist";
		}
		*/
		
		return '';
	}
	
}
