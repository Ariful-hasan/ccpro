<?php

require_once 'BaseTableDataController.php';
class LocationBasedRouting extends BaseTableDataController
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		/*
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		*/
		
		include('model/VirtualCC.php');
		$obj_model = new VirtualCC();

		//$dbSuffix = UserAuth::getDBSuffix();
		//include(UserAuth::getConfExtraFile());
		
		
		$data['routing'] = $obj_model->getVCCRouting();
		
		$errMsg = '';
		$errType = 1;
		$request = $this->getRequest();
		if ($request->isPost()) {
			
			$routing = new stdClass();
			
			$routing->staffing = isset($_REQUEST['staffing']) ? 'Y' : 'N';
			$routing->service_level = isset($_REQUEST['staffing']) ? 'Y' : 'N';
			$routing->aht = isset($_REQUEST['aht']) ? 'Y' : 'N';
			$routing->ahq = isset($_REQUEST['ahq']) ? 'Y' : 'N';
			
			$routing->sl_threshold = isset($_POST['sl_threshold']) ? trim($_POST['sl_threshold']) : '0';
			$routing->call_reduce_on_sl = isset($_POST['call_reduce_on_sl']) ? trim($_POST['call_reduce_on_sl']) : '0';
			$routing->aht_threshold = isset($_POST['aht_threshold']) ? trim($_POST['aht_threshold']) : '0';
			$routing->call_reduce_on_aht = isset($_POST['call_reduce_on_aht']) ? trim($_POST['call_reduce_on_aht']) : '0';
			$routing->ahq_threshold = isset($_POST['ahq_threshold']) ? trim($_POST['ahq_threshold']) : '0';
			$routing->call_reduce_on_ahq = isset($_POST['call_reduce_on_ahq']) ? trim($_POST['call_reduce_on_ahq']) : '0';
			
			if (!ctype_digit($routing->sl_threshold)) {
				$errMsg = 'SL Threshold is Invalid';
			} elseif (!ctype_digit($routing->call_reduce_on_sl)) {
				$errMsg = 'Reduce Call on SL Threshold is Invalid';
			} elseif (!ctype_digit($routing->aht_threshold)) {
				$errMsg = 'AHT Threshold is Invalid';
			} elseif (!ctype_digit($routing->call_reduce_on_aht)) {
				$errMsg = 'Reduce Call on AHT Threshold is Invalid';
			} elseif (!ctype_digit($routing->ahq_threshold)) {
				$errMsg = 'AHQ Threshold is Invalid';
			} elseif (!ctype_digit($routing->call_reduce_on_ahq)) {
				$errMsg = 'Reduce Call on AHQ Threshold is Invalid';
			}
			
			if (empty($errMsg)) {
				$is_success = false;
				if ($obj_model->updateVCCRouting($routing)) {
					$errMsg = 'Configuration saved successfully !!';
					$is_success = true;
				} else {
					$errMsg = 'No change found !!';
				}
					
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
				}
			}
		}
		//var_dump($data['sl_method']);
		//exit;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $request;
		$data['topMenuItems'] = array(
		        array('href'=>'task=location-based-routing&act=location-list', 'img'=>'fa fa-th', 'label'=>'Location List', 'title'=>'Shows the existing locations.')
                );
		$data['pageTitle'] = 'OCC Routing';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('location_based_routing', $data);
	}

	function actionLocationList()
        {
	  $data['pageTitle'] = 'OCC Locations';
          $data['side_menu_index'] = 'settings';
	  $data['smi_selection'] = 'location-based-routing_';
	  $data['topMenuItems'] = array(array('href'=>'task=location-based-routing&act=add', 'img'=>'fa fa-plus-circle', 'label'=>'Add New Location', 'title'=>'Clicking this button Admin may add a new location.'),array('href'=>'task=location-based-routing', 'img'=>'fa fa-cog', 'label'=>'OCC Routing', 'title'=>'Configure location based routing.'));
	  $this->getTemplate()->display('location_list', $data);
        }
        
        function actionAdd()
        {
                $this->saveLocation();
        }

        function actionUpdate()
        {
                $lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
                if (strlen($lid) != 1) exit;
                $this->saveLocation($lid);
        }

	function saveLocation($loc_id='')
        {
                include('model/VirtualCC.php');
                $vcc_model = new VirtualCC();

                $request = $this->getRequest();
                $errMsg = '';
                $errType = 1;
                if ($request->isPost()) {
                        $vcc = $this->getSubmittedLocation($loc_id);

                        //var_dump($service);
                        //exit;
                        $errMsg = $this->getValidationMsg($vcc, $loc_id, $vcc_model);

                        if (empty($errMsg)) {
                                $is_success = false;
                                if (empty($loc_id)) {
                                        if ($vcc_model->addLocation($vcc)) {
                                                $errMsg = 'Location added successfully !!';
                                                $is_success = true;
                                        } else {
                                                $errMsg = 'Failed to add location !!';
                                        }
                                } else {
                                        $oldvcc = $this->getInitialLocation($loc_id, $vcc_model);
                                        if ($vcc_model->updateLocation($oldvcc, $vcc)) {
                                                $errMsg = 'Location updated successfully !!';
                                                $is_success = true;
                                        } else {
                                                $errMsg = 'No change found !!';
                                        }
                                }

                                if ($is_success) {
                                        $errType = 0;
                                        $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=location-list");
                                        $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                                }
                        }

                } else {
                        $vcc = $this->getInitialLocation($loc_id, $vcc_model);
                        if (empty($vcc)) {
                                exit;
                        }
                }

                $data['vcc'] = $vcc;
                $data['loc_id'] = $loc_id;
                $data['request'] = $request;
                $data['errMsg'] = $errMsg;
                $data['errType'] = $errType;
                $data['pageTitle'] = empty($loc_id) ? 'Add New Location' : 'Update Location';
                $data['side_menu_index'] = 'settings';
                $data['smi_selection'] = 'location-based-routing_';
                $this->getTemplate()->display('vcc_location_form', $data);
        }
        
        function getInitialLocation($loc_id, $vcc_model)
        {
                $vcc = null;

                if (empty($loc_id)) {
                        $vcc->vcc_id = "";
                        $vcc->name = "";
                        $vcc->call_ration = "0";
                } else {
                        $vcc = $vcc_model->getLocationById($loc_id);
                }
                return $vcc;
        }
        
        function getSubmittedLocation($loc_id)
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

        function getValidationMsg($vcc, $loc_id='', $vcc_model)
        {
                if (empty($vcc->vcc_id)) return "Provide location ID";
                if (empty($vcc->name)) return "Provide location name";
                if (!preg_match("/^[A-Z]{1,1}$/", $vcc->vcc_id)) return "Provide valid location ID";
                if (!preg_match("/^[0-9a-zA-Z_ ]{1,25}$/", $vcc->name)) return "Provide valid location name";

		if (!ctype_digit($vcc->call_ratio)) return "Provide valid call ratio";

                if ($vcc->vcc_id != $loc_id) {
                        $existing_code = $vcc_model->getLocationByID($vcc->vcc_id);
                        if (!empty($existing_code)) return "Location ID $vcc->vcc_id already exist";
                }

                return '';
        }
}
