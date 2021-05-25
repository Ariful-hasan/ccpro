<?php

class Ivrdc extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $data['pageTitle'] = 'IVR Disposition Code';
		$data['side_menu_index'] = 'settings';
	    $data['dataUrl'] = $this->url('task=get-tools-data&act=ivrdc');
	    $data['topMenuItems'] = array(array('href'=>'task=ivrdc&act=add', 'img'=>'fa fa-plus-square-o', 'label'=>'Add New Disposition Code'));
		$this->getTemplate()->display('ivr_services', $data);
	    
		/* include('model/MIvrService.php');
		include('lib/Pagination.php');
		$ivr_model = new MIvrService();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $ivr_model->numServices();
		$data['services'] = $pagination->num_records > 0 ? 
			$ivr_model->getServices($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['services']) ? count($data['services']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'IVR Disposition Code';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=ivrdc&act=add', 'img'=>'add.png', 'label'=>'Add New Disposition Code'));
		$this->getTemplate()->display('ivr_services', $data); */
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$this->saveService($sid);
	}

	function actionDel()
	{
		include('model/MIvrService.php');
		$ivr_model = new MIvrService();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($ivr_model->deleteDispositionCode($dcode)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>false, 'msg'=>' Disposition Code Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>true, 'msg'=>'Failed to Delete  Disposition Code', 'redirectUri'=>$url));
		}
	}
	
	function saveService($dis_code='')
	{
		include('model/MIvrService.php');
		$ivr_model = new MIvrService();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($dis_code);
			$errMsg = $this->getValidationMsg($service, $dis_code, $ivr_model);
            //dd($service);
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($dis_code)) {
					if ($ivr_model->addService($service)) {
						$errMsg = 'Disposition code added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add disposition code !!';
					}
				} else {
					$oldservice = $this->getInitialService($dis_code, $ivr_model);
					if ($ivr_model->updateService($oldservice, $service)) {
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
            $service = $this->getInitialService($dis_code, $ivr_model);
            if (empty($service)) {
                exit;
            }
        }
		
		$data['report_category_list'] = array("S"=>"Summary", "D"=>"Details");
		$data['report_type_list'] = array("I"=>"Info", "M"=>"Menu");
		$data['disp_list'] = $ivr_model->getAllDisposition($dis_code);
		//dd($data['disp_list']);
		$data['service'] = $service;
		$data['dis_code'] = $dis_code;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($dis_code) ? 'Add New Disposition Code' : 'Update Disposition Code'.' : ' . $dis_code;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'ivrdc_';
		$this->getTemplate()->display('ivr_service_form', $data);
	}
	
	function getInitialService($dis_code, $ivr_model)
	{
		$service = null;

		if (empty($dis_code)) {
			$service->disposition_code = "";
			$service->service_title = "";
			$service->service_type = "M";
			$service->parent_id = "";
			$service->report_category = "";
			$service->report_type = "";
		} else {
			$service = $ivr_model->getServiceByCode($dis_code);
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

	function getValidationMsg($service, $dis_code='', $ivr_model)
	{
		if (empty($service->disposition_code)) return "Provide disposition code";
		if (empty($service->service_title)) return "Provide service title";
		if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_code)) return "Provide valid disposition code";
		if (!preg_match("/^[0-9a-zA-Z_ ]{1,40}$/", $service->service_title)) return "Provide valid service title";
		
		if ($service->disposition_code != $dis_code) {
			$existing_code = $ivr_model->getServiceByCode($service->disposition_code);
			if (!empty($existing_code)) return "Disposition code $service->disposition_code already exist";
		}
		
		return '';
	}
	
}
