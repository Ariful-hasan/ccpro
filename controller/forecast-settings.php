<?php
require_once 'BaseTableDataController.php';

class ForecastSettings extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	public function init()
	{
		
	}

	public function actionDayName(){
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=add-day-name', 
										'img'=>'fa fa-plus', 
										'label'=>'New Name', 
										'title'=>'New Name'
									)
								);
		$data['pageTitle'] = 'Day Name';
		$data['dataUrl'] = $this->url('task=forecast-settings&act=day-name-grid-list');
		$data['userColumn'] = "Day Name";
		$data['request'] = $this->getRequest();		
		$this->getTemplate()->display('fc-day-name', $data);
	}

	public function actionDayNameGridList(){
		include_once('model/MDayName.php');
		$day_name_model = new MDayName();

		// search item
		$type = '';
		$name = '';
		$day_type_list = fc_day_type();
		if ($this->gridRequest->srcItem=="type") {
			foreach ($day_type_list as $key => $item) {
				if(strpos(strtolower($item), $this->gridRequest->srcText) > -1){
					$type = $key;
					break;
				}
			}
			$type = empty($type) ? $this->gridRequest->srcText : $type;
		} elseif ($this->gridRequest->srcItem=="name") {
		    $name = $this->gridRequest->srcText;
		}
		$this->pagination->num_records = $day_name_model->numDayNames('',$type, $name, '');
		$day_names = $this->pagination->num_records > 0 ? $day_name_model->getDayNames('',$type, $name, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$day_names;
		
		if(!empty($result) && count($result)>0){	        
			foreach ( $result as &$data ) {
				$data->type = $day_type_list[$data->type];
				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == MSG_YES ? "inactivate" : "activate") . " this name: " . $data->name . "?' data-href='" . $this->url("task=forecast-settings&act=day-name-status&id=" . $data->id . "&status=" . ($data->status == MSG_YES ? MSG_NO : MSG_YES)) . "'>" . ($data->status == MSG_YES ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


				$data->name = "<a href='". $this->url("task=forecast-settings&act=day-name-update&id=".$data->id)."'>".$data->name."</a>";
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionDayNameStatus(){
		include_once('model/MDayName.php');
		$day_name_model = new MDayName();

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		if(empty($id) || empty($status) || !in_array($status, [MSG_YES, MSG_NO])){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}

		$day_name = $day_name_model->getDayNames($id);
		if(empty($day_name)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This name is not exists!'
			]));
		}
		
		if($day_name_model->updateDayNameStatus($id, $status)){
			die(json_encode([
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_MSG => 'Status has been updated successfully!'
			]));
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Status has not been updated successfully!!'
			]));
		}
	}

	public function actionAddDayName(){
		include_once('model/MDayName.php');
		include_once('lib/FormValidator.php');
		$day_name_model = new MDayName();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$day_name = '';
		$error_data = '';

		if ($request->isPost()) {					
			$response = $day_name_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=forecast-settings&act=day-name");
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$day_name = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Add New Name';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=day-name',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'forecast-settings_day-name';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['day_name_data'] = $day_name;
		$data['error_data'] = $error_data;
		$data['status_list'] = [MSG_YES=>'Active', MSG_NO=>'Inactive'];
		$data['type_list'] = fc_day_type();
		$this->getTemplate()->display('fc-day-name-form', $data);
	}
	public function actionDayNameUpdate(){
		include_once('model/MDayName.php');
		include_once('lib/FormValidator.php');
		$day_name_model = new MDayName();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$day_name = '';
		$error_data = '';

		$day_name = $day_name_model->getDayNames($request->getRequest('id'));
		if(empty($day_name)){
			$errType = 1;
			$errMsg ='This name is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName())."&act=day-name";
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $day_name_model->updateData($this->getRequest()->getPost(), $day_name[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName())."&act=day-name";
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$day_name[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Update Day Name';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=day-name',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		
		$data['main_menu'] = 'forecast-settings_day-name';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['day_name_data'] = $day_name[0];
		$data['error_data'] = $error_data;
		$data['status_list'] = [MSG_YES=>'Active', MSG_NO=>'Inactive'];
		$data['type_list'] = fc_day_type();
		$this->getTemplate()->display('fc-day-name-form', $data);
	}

	public function actionUniqueDayName(){
		include_once('model/MDayName.php');
		$day_name_model = new MDayName();

		$name = trim($this->getRequest()->getRequest('name'));
		$id = trim($this->getRequest()->getRequest('id'));

        if(!empty($name)) {
            $flag = $day_name_model->checkUniqueDayName($name, $id);
            // var_dump($flag);
            die($flag);
        } else {
            die("false");
        }
	}

	public function actionDaySettings(){
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=add-day-settings', 
										'img'=>'fa fa-plus', 
										'label'=>'New Day Settings', 
										'title'=>'New Day Settings'
									)
								);
		$data['pageTitle'] = 'Day Settings';
		$data['dataUrl'] = $this->url('task=forecast-settings&act=day-settings-grid-list');
		$data['userColumn'] = "Day Settings";
		$data['request'] = $this->getRequest();		
		$this->getTemplate()->display('fc-day-settings', $data);
	}

	public function actionDaySettingsGridList(){
		include_once('model/MDaySetting.php');
		include_once('model/MDayName.php');
		$day_settings_model = new MDaySetting();
		$day_name_model = new MDayName();

		// search item
		$type = '';
		$day_id = '';
		$day_type_list = fc_day_type();
		if ($this->gridRequest->srcItem=="type") {
			foreach ($day_type_list as $key => $item) {
				if(strpos(strtolower($item), $this->gridRequest->srcText) > -1){
					$type = $key;
					break;
				}
			}
			$type = empty($type) ? $this->gridRequest->srcText : $type;
		} elseif ($this->gridRequest->srcItem=="day_id") {
		    $day_id = $this->gridRequest->srcText;
		}
		$this->pagination->num_records = $day_settings_model->numDaySettings('',$type, $day_id, '');
		$result = $this->pagination->num_records > 0 ? $day_settings_model->getDaySettings('',$type, $day_id, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		// $result=&$day_settings;
		$day_names = $day_name_model->getNameList('', '', '', 'Y');
		// var_dump($day_names);
		
		if(!empty($result) && count($result)>0){	        
			foreach ( $result as &$data ) {	
				$data->mdate = date('d/m/Y', strtotime($data->mdate));
				$data->created_at = date('d/m/Y h:i:s a', strtotime($data->created_at));

				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == MSG_YES ? "inactivate" : "activate") . " this item.' data-href='" . $this->url("task=forecast-settings&act=day-settings-status&id=" . $data->id . "&status=" . ($data->status == MSG_YES ? MSG_NO : MSG_YES)) . "'>" . ($data->status == MSG_YES ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


				$data->day_id = "<a href='". $this->url("task=forecast-settings&act=day-settings-update&id=".$data->id)."'>".$day_names[$data->type][$data->day_id]."</a>";
				$data->type = $day_type_list[$data->type];
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionDaySettingsStatus(){
		include_once('model/MDaySetting.php');
		$day_setting_model = new MDaySetting();

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		if(empty($id) || empty($status) || !in_array($status, [MSG_YES, MSG_NO])){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}

		$day_setting = $day_setting_model->getDaySettings($id);
		if(empty($day_setting)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This item is not exists!'
			]));
		}
		
		if($day_setting_model->updateDaySettingsStatus($id, $status)){
			die(json_encode([
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_MSG => 'Status has been updated successfully!'
			]));
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Status has not been updated successfully!!'
			]));
		}
	}

	public function actionAddDaySettings(){
		include_once('model/MDayName.php');
		include_once('model/MDaySetting.php');
		include_once('lib/FormValidator.php');
		$day_name_model = new MDayName();
		$day_setting_model = new MDaySetting();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$day_setting = '';
		$error_data = '';

		if ($request->isPost()) {					
			$response = $day_setting_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=forecast-settings&act=day-settings");
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$day_setting = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Add New Day';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=day-settings',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'forecast-settings_day-settings';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['day_setting_data'] = $day_setting;
		$data['error_data'] = $error_data;
		$data['status_list'] = [MSG_YES=>'Active', MSG_NO=>'Inactive'];
		$data['type_list'] = fc_day_type();
		$data['day_names'] = $day_name_model->getNameList('', '', '', 'Y');
		$this->getTemplate()->display('fc-day-setting-form', $data);
	}

	public function actionDaySettingsUpdate(){
		include_once('model/MDayName.php');
		include_once('model/MDaySetting.php');
		include_once('lib/FormValidator.php');
		$day_name_model = new MDayName();
		$day_setting_model = new MDaySetting();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$day_setting = '';
		$error_data = '';

		$day_setting = $day_setting_model->getDaySettings($request->getRequest('id'));
		if(empty($day_setting)){
			$errType = 1;
			$errMsg ='This day is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName())."&act=day-setting";
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $day_setting_model->updateData($this->getRequest()->getPost(), $day_setting[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName())."&act=day-settings";
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$day_setting[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Update Day';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=forecast-settings&act=day-settings',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		
		$day_setting[0]->mdate = date('d/m/Y', strtotime($day_setting[0]->mdate));
		$data['main_menu'] = 'forecast-settings_day-settings';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['day_setting_data'] = $day_setting[0];
		$data['error_data'] = $error_data;
		$data['status_list'] = [MSG_YES=>'Active', MSG_NO=>'Inactive'];
		$data['type_list'] = fc_day_type();
		$data['day_names'] = $day_name_model->getNameList('', '', '', 'Y');
		$this->getTemplate()->display('fc-day-setting-form', $data);
	}

	public function actionHisDataUpload(){	
		include_once('model/MForecastSkill.php');
		include_once('model/MHisData.php');
		$forecast_skill_model = new MForecastSkill();
		$hid_data_model = new MHisData();

		$service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();
		$selected_skill_list['*'] = 'All';
		foreach ($forecast_skill_list['V'] as $key => $item) {
			if(empty($item['gplex_fc_group_ids']))
				$selected_skill_list[$key] = $item['name'];
		}

		$dateinfo = "";
		$his_data = $hid_data_model->getHisData($dateinfo, 'V', '', 0, 100000);
		$chart_labels = [];
		$chart_data = [];
		foreach ($his_data as $key => $item) {
			if(!in_array($item->sdate, $chart_labels))
				$chart_labels[]=$item->sdate;

			$chart_data[$item->skill_name][]=$item->count;
		}

		$data['topMenuItems'] = array(
            array(
            	'href' => 'task=forecast-settings&act=add-his-data', 
            	'img' => 'fa fa-file-text-o', 
            	'label' => 'Upload Historical Data', 
            	'class' => 'lightboxWIFR', 
            	'dataattr' => array('w' => 500, 'h' => 300)
            ),
            array(
            	'img' => 'fa fa-file', 
            	'label' => 'Chart View', 
            	'class' => 'btn-show-chart',
            )
        );

		$data['pageTitle'] = 'Daily Historical Data';
		$data['dataUrl'] = $this->url('task=forecast-settings&act=his-data-grid-list');
		$data['chartDataUrl'] = $this->url('task=forecast-settings&act=his-chart-data');
		$data['userColumn'] = "Historical Data";
		$data['request'] = $this->getRequest();
		$data['service_type_list'] = $service_type_list;
		$data['forecast_skill_list'] = $forecast_skill_list;
		$data['selected_skill_list'] = $selected_skill_list;
		$data['chart_labels'] = $chart_labels;
		$data['chart_data'] = $chart_data;

		$this->getTemplate()->display('fc-his-data', $data);
	}

	public function actionHisDataGridList(){
		include_once('model/MHisData.php');
        include_once('lib/DateHelper.php');
		include_once('model/MForecastSkill.php');
		$hid_data_model = new MHisData();
		$forecast_skill_model = new MForecastSkill();

		// search item
		$service_type = '';
		$skill_id = '';
		$service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();

		if ($this->gridRequest->isMultisearch){
			$date_range = $this->gridRequest->getMultiParam('sdate');
			if(!empty($date_range['from']) && !empty($date_range['to'])){
				$date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], 'd/m/Y') : date('Y-m-d');
            	$date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], 'd/m/Y') : date('Y-m-d');

            	$dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
            }
			$service_type = trim($this->gridRequest->getMultiParam('service_type'));
			$skill_id = trim($this->gridRequest->getMultiParam('skill_id'));
		}

		$this->pagination->num_records = $hid_data_model->numHisData($dateinfo, $service_type, $skill_id);
		$response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

		$his_data = $this->pagination->num_records > 0 ? $hid_data_model->getHisData($dateinfo, $service_type, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$result=&$his_data;
		
		if(!empty($result) && count($result)>0){
			foreach ( $result as &$data ) {
				$data->sdate = date('d/m/Y', strtotime($data->sdate));
				$data->skill_id = $forecast_skill_list[$data->service_type][$data->skill_id]['name'];
				$data->service_type = $service_type_list[$data->service_type];
			}
		}

		$response->rowdata = $result;
		$this->ShowTableResponse();
	}

	public function actionHisChartData(){
		include_once('model/MHisData.php');
        include_once('lib/DateHelper.php');
		$hid_data_model = new MHisData();
		$request = $this->getRequest();
		$ms = $request->getRequest('ms');

		// search item
		$service_type = (isset($ms['service_type']) && !empty($ms['service_type'])) ? $ms['service_type'] : '';
		$skill_id = (isset($ms['skill_id']) && !empty($ms['skill_id'])) ? $ms['skill_id'] : '';
		$date_range = (isset($ms['sdate']) && !empty($ms['sdate'])) ? $ms['sdate'] : [];
		$service_type_list = fc_service_type_list();
		$dateinfo = "";
		
		if(!empty($date_range['from']) && !empty($date_range['to'])){
			$date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], 'd/m/Y') : date('Y-m-d');
        	$date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], 'd/m/Y') : date('Y-m-d');
        	$dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
		}
		// Gprint($ms);
		// Gprint($dateinfo);

		$his_data = $hid_data_model->getHisData($dateinfo, $service_type, $skill_id, 0, 100000);
		$chart_labels = [];
		$chart_data = [];
		if(!empty($dateinfo)){
			$sdate = $dateinfo->sdate;
			while ($sdate <= $dateinfo->edate) {
				$chart_labels[]=$sdate;
				$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
			}
		}
		foreach ($his_data as $key => $item) {
			if(empty($dateinfo) && !in_array($item->sdate, $chart_labels))
				$chart_labels[]=$item->sdate;

			$chart_data[$item->skill_name][]=$item->count;
		}
		// Gprint($chart_labels);
		// Gprint($chart_data);
		die(json_encode([
			'labels' => $chart_labels,
			'data' => $chart_data
		]));
	}

	function actionAddHisData() {
		include_once('model/MHisData.php');
		include_once('model/MForecastSkill.php');
		$hid_data_model = new MHisData();
		$forecast_skill_model = new MForecastSkill();

        $service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();
        $data = array();
        $data['pageTitle'] = 'Add Historical Data';
        $data_uploaded = false;
        if (count($_FILES) > 0) {
            if ($_FILES["forcast_his_data"]["name"] == null) {
                $data['errMsg'] = "No file was uploaded";
                $data['errType'] = 0;
                $this->getTemplate()->display_popup('msg', $data);
            } else {
                $fileType = end(explode(".", $_FILES["forcast_his_data"]["name"]));
                $fileSize = $_FILES["forcast_his_data"]["size"];
                $error_data_list = array();
                if ($fileType == "csv" && $fileSize <= 10000000) { //csv file less than 10 MB size                    
                    $fileHandler = fopen($_FILES["forcast_his_data"]["tmp_name"], "r");
                    while (!feof($fileHandler)) {
                        $result = fgetcsv($fileHandler);
                        $forcast_his_data = array_map("trim", $result);
                        if (sizeof($forcast_his_data) == 4) {
                            $date = generic_date_format($forcast_his_data[0], 'Y-m-d');
                            $skill_name = $forcast_his_data[1];
                            $skill_id = $forecast_skill_model->getSkillIdFromName($forecast_skill_list[$forcast_his_data[3]], $skill_name);
                            // var_dump($date);
                            // var_dump($skill_id);

                            if ($skill_id != null) {
                                $data_uploaded = true;
                                if ($hid_data_model->isDuplicate($date, $skill_id, $skill_name)) {
                                    $forcast_value = $forcast_his_data[2];
                                    $hid_data_model->updateHisData($date, $skill_id, $skill_name, $forcast_value);
                                } else {
                                    $forcast_value = $forcast_his_data[2];
                                    $hid_data_model->storeHisData($date, $skill_id, $skill_name, $forcast_value, $forcast_his_data[3]);
                                }
                            }else{
                            	// Gprint($skill_name);
                            	// Gprint($forcast_his_data);
                            	// Gprint($forecast_skill_list[$forcast_his_data[3]]);
                            	// Gprint($skill_id);
                                //skill not found error
                                $error_data = new stdClass();
                                $error_data->date = $date;
                                $error_data->skill_name = $skill_name;
                                $error_data->value = $forcast_his_data[2];
                                $error_data->service_type = $forcast_his_data[3];
                                $error_data->error = "Skill Not Found";
                                array_push($error_data_list, $error_data);
                                // die();
                            }
                        }else{
                            //error data format
                            if (sizeof($forcast_his_data) == 0) continue;
                            $error_data = new stdClass();
                            $error_data->date = sizeof($forcast_his_data) == 1 ? $forcast_his_data[0] : null;
                            $error_data->skill_name = sizeof($forcast_his_data) == 2 ? $forcast_his_data[1] : null;
                            $error_data->value = sizeof($forcast_his_data) == 3 ? $forcast_his_data[2] : null;
                            $error_data->service_type = sizeof($forcast_his_data) == 4 ? $forcast_his_data[3] : null;
                            $error_data->error = "Error Data Format";
                            array_push($error_data_list, $error_data);
                        }
                    }
                    fclose($fileHandler);

                    if ($data_uploaded) 
                    	$data['errMsg'] = "File Upload Complete";
                    $data['errData'] = $error_data_list;
                    $this->getTemplate()->display_popup('fc-his-data-form', $data, true);
                } else {
                    $data['errMsg'] = "Uploaded file must be csv and not bigger than 10 MB";
                    $data['errType'] = 0;
                }
            }
        }
        $this->getTemplate()->display_popup('fc-his-data-form', $data, true);
    }

    function actionGlobal() {
	    include_once('model/MForecastGlobalSetting.php');
		$forecast_global_settings_model = new MForecastGlobalSetting();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$trend_list = fc_trend_list();

		if ($request->isPost()) {
		    $post_data['avg_call_duration'] = isset($_POST['avg_call_duration']) ? trim($_POST['avg_call_duration']) : '';
		    $post_data['total_interval_length'] = isset($_POST['total_interval_length']) ? trim($_POST['total_interval_length']) : '';
		    $post_data['agent_occupancy'] = isset($_POST['agent_occupancy']) ? trim($_POST['agent_occupancy']) : '';
		    $post_data['forecast_trend'] = isset($_POST['forecast_trend']) ? trim($_POST['forecast_trend']) : '';
		    $post_data['forecast_upper_scale'] = isset($_POST['forecast_upper_scale']) ? trim($_POST['forecast_upper_scale']) : '';
		    $post_data['forecast_lower_scale'] = isset($_POST['forecast_lower_scale']) ? trim($_POST['forecast_lower_scale']) : '';
		    
		    $post_data['avg_call_duration_hourly'] = isset($_POST['avg_call_duration_hourly']) ? trim($_POST['avg_call_duration_hourly']) : '';
		    $post_data['total_interval_length_hourly'] = isset($_POST['total_interval_length_hourly']) ? trim($_POST['total_interval_length_hourly']) : '';
		    $post_data['agent_occupancy_hourly'] = isset($_POST['agent_occupancy_hourly']) ? trim($_POST['agent_occupancy_hourly']) : '';
		    $post_data['forecast_trend_hourly'] = isset($_POST['forecast_trend_hourly']) ? trim($_POST['forecast_trend_hourly']) : '';
		    $post_data['forecast_upper_scale_hourly'] = isset($_POST['forecast_upper_scale_hourly']) ? trim($_POST['forecast_upper_scale_hourly']) : '';
		    $post_data['forecast_lower_scale_hourly'] = isset($_POST['forecast_lower_scale_hourly']) ? trim($_POST['forecast_lower_scale_hourly']) : '';
		    $post_data['wait_time_hourly'] = isset($_POST['wait_time_hourly']) ? trim($_POST['wait_time_hourly']) : '';
		    $post_data['service_level_hourly'] = isset($_POST['service_level_hourly']) ? trim($_POST['service_level_hourly']) : '';
		    $post_data['shrinkage_hourly'] = isset($_POST['shrinkage_hourly']) ? trim($_POST['shrinkage_hourly']) : '';
         	// GPrint($_POST); die();

		    $response = $forecast_global_settings_model->saveData($post_data);
		    if($response){
		    	$errMsg = 'Settings updated successfully !!';
                $errType = 0;
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=".$this->getRequest()->getActionName());
                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
		    }else{
		    	$errType = 1;
                $errMsg = 'No change found to update !!';
		    }
		} else {
		    $settings_data = $forecast_global_settings_model->getSettingsData();
		}

		$data['settings_data'] = $settings_data;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['trend_list'] = $trend_list;
		$data['pageTitle'] = 'Global Forecast Settings';
		$this->getTemplate()->display('fc-global-settings-form', $data);
	}
	public function actionHourlyHisDataUpload(){	
		include_once('model/MForecastSkill.php');
		include_once('model/MHisData.php');
		$forecast_skill_model = new MForecastSkill();
		$hid_data_model = new MHisData();

		$service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();
		$selected_skill_list['*'] = 'All';
		foreach ($forecast_skill_list['V'] as $key => $item) {
			if(empty($item['gplex_fc_group_ids']))
				$selected_skill_list[$key] = $item['name'];
		}

		$dateinfo = "";
		$his_data = $hid_data_model->getHisData($dateinfo, 'V', '', 0, 100000);
		$chart_labels = [];
		$chart_data = [];
		foreach ($his_data as $key => $item) {
			if(!in_array($item->sdate, $chart_labels))
				$chart_labels[]=$item->sdate;

			$chart_data[$item->skill_name][]=$item->count;
		}

		$data['topMenuItems'] = array(
            array(
            	'href' => 'task=forecast-settings&act=add-hourly-his-data', 
            	'img' => 'fa fa-file-text-o', 
            	'label' => 'Upload Historical Data', 
            	'class' => 'lightboxWIFR', 
            	'dataattr' => array('w' => 500, 'h' => 300)
            ),
            array(
            	'img' => 'fa fa-file', 
            	'label' => 'Chart View', 
            	'class' => 'btn-show-chart',
            )
        );

		$data['pageTitle'] = 'Hourly Historical Data';
		$data['dataUrl'] = $this->url('task=forecast-settings&act=hourly-his-data-grid-list');
		$data['chartDataUrl'] = $this->url('task=forecast-settings&act=hourly-his-chart-data');
		$data['userColumn'] = "Historical Data";
		$data['request'] = $this->getRequest();
		$data['service_type_list'] = $service_type_list;
		$data['forecast_skill_list'] = $forecast_skill_list;
		$data['selected_skill_list'] = $selected_skill_list;
		$data['chart_labels'] = $chart_labels;
		$data['chart_data'] = $chart_data;

		$this->getTemplate()->display('fc-hourly-his-data', $data);
	}

	public function actionHourlyHisDataGridList(){
		include_once('model/MHisData.php');
        include_once('lib/DateHelper.php');
		include_once('model/MForecastSkill.php');
		$hid_data_model = new MHisData();
		$forecast_skill_model = new MForecastSkill();

		// search item
		$service_type = '';
		$skill_id = '';
		$service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();

		if ($this->gridRequest->isMultisearch){
			$date_range = $this->gridRequest->getMultiParam('sdate');
			if(!empty($date_range['from']) && !empty($date_range['to'])){
				$date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], 'd/m/Y') : date('Y-m-d');
            	$date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], 'd/m/Y') : date('Y-m-d');

            	$dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
            }
			$service_type = trim($this->gridRequest->getMultiParam('service_type'));
			$skill_id = trim($this->gridRequest->getMultiParam('skill_id'));
		}

		$this->pagination->num_records = $hid_data_model->numHisDataHourly($dateinfo, $service_type, $skill_id);
		$response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;

		$his_data = $this->pagination->num_records > 0 ? $hid_data_model->getHisDataHourly($dateinfo, $service_type, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$result=&$his_data;
		
		if(!empty($result) && count($result)>0){
			foreach ( $result as &$data ) {
				$data->sdate = date('d/m/Y', strtotime($data->sdate));
				$data->skill_id = $forecast_skill_list[$data->service_type][$data->skill_id]['name'];
				$data->service_type = $service_type_list[$data->service_type];
			}
		}

		$response->rowdata = $result;
		$this->ShowTableResponse();
	}

	public function actionHourlyHisChartData(){
		include_once('model/MHisData.php');
        include_once('lib/DateHelper.php');
		$hid_data_model = new MHisData();
		$request = $this->getRequest();
		$ms = $request->getRequest('ms');

		// search item
		$service_type = (isset($ms['service_type']) && !empty($ms['service_type'])) ? $ms['service_type'] : '';
		$skill_id = (isset($ms['skill_id']) && !empty($ms['skill_id'])) ? $ms['skill_id'] : '';
		$date_range = (isset($ms['sdate']) && !empty($ms['sdate'])) ? $ms['sdate'] : [];
		$service_type_list = fc_service_type_list();
		$dateinfo = "";
		
		if(!empty($date_range['from']) && !empty($date_range['to'])){
			$date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], 'd/m/Y') : date('Y-m-d');
        	$date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], 'd/m/Y') : date('Y-m-d');
        	$dateinfo = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');
		}
		// Gprint($ms);
		// Gprint($dateinfo);

		$his_data = $hid_data_model->getHisDataHourly($dateinfo, $service_type, $skill_id, 0, 100000);
		$chart_labels = [];
		$chart_data = [];
		if(!empty($dateinfo)){
			$sdate = $dateinfo->sdate;
			while ($sdate <= $dateinfo->edate) {
				$chart_labels[]=$sdate;
				$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
			}
		}
		foreach ($his_data as $key => $item) {
			if(empty($dateinfo) && !in_array($item->sdate, $chart_labels))
				$chart_labels[]=$item->sdate;

			$chart_data[$item->skill_name][]=$item->count;
		}
		// Gprint($chart_labels);
		// Gprint($chart_data);

		die(json_encode([
			'labels' => [], //$chart_labels,
			'data' => [] //$chart_data
		]));
	}

	function actionHourlyAddHisData() {
		include_once('model/MHisData.php');
		include_once('model/MForecastSkill.php');
		$hid_data_model = new MHisData();
		$forecast_skill_model = new MForecastSkill();

        $service_type_list = fc_service_type_list();
		$forecast_skill_list = $forecast_skill_model->getFcSkillList();
        $data = array();
        $data['pageTitle'] = 'Add Historical Data';
        $data_uploaded = false;
        if (count($_FILES) > 0) {
            if ($_FILES["forcast_his_data"]["name"] == null) {
                $data['errMsg'] = "No file was uploaded";
                $data['errType'] = 0;
                $this->getTemplate()->display_popup('msg', $data);
            } else {
                $fileType = end(explode(".", $_FILES["forcast_his_data"]["name"]));
                $fileSize = $_FILES["forcast_his_data"]["size"];
                $error_data_list = array();
                if ($fileType == "csv" && $fileSize <= 10000000) { //csv file less than 10 MB size                    
                    $fileHandler = fopen($_FILES["forcast_his_data"]["tmp_name"], "r");
                    while (!feof($fileHandler)) {
                        $result = fgetcsv($fileHandler);
                        $forcast_his_data = array_map("trim", $result);
                        if (sizeof($forcast_his_data) == 5) {
                            $date = generic_date_format($forcast_his_data[0], 'Y-m-d');
                            $shour = $forcast_his_data[1];
                            $skill_name = $forcast_his_data[2];
                            $skill_id = $forecast_skill_model->getSkillIdFromName($forecast_skill_list[$forcast_his_data[4]], $skill_name);
                            // var_dump($date); 
                            // var_dump($shour);
                            // var_dump($skill_id);

                            if ($skill_id != null) {
                                $data_uploaded = true;
                                if ($hid_data_model->isDuplicateHourly($date, $shour, $skill_id, $skill_name)) {
                                    $forcast_value = $forcast_his_data[3];
                                    $hid_data_model->updateHisDataHourly($date, $shour, $skill_id, $skill_name, $forcast_value);
                                } else {
                                    $forcast_value = $forcast_his_data[3];
                                    $hid_data_model->storeHisDataHourly($date, $shour, $skill_id, $skill_name, $forcast_value, $forcast_his_data[4]);
                                }
                            }else{
                            	// Gprint($skill_name);
                            	// Gprint($forcast_his_data);
                            	// Gprint($forecast_skill_list[$forcast_his_data[3]]);
                            	// Gprint($skill_id);
                                //skill not found error
                                $error_data = new stdClass();
                                $error_data->date = $date;
                                $error_data->shour = $shour;
                                $error_data->skill_name = $skill_name;
                                $error_data->value = $forcast_his_data[3];
                                $error_data->service_type = $forcast_his_data[4];
                                $error_data->error = "Skill Not Found";
                                array_push($error_data_list, $error_data);
                                // die();
                            }
                        }else{
                            //error data format
                            if (sizeof($forcast_his_data) == 0) continue;
                            $error_data = new stdClass();
                            $error_data->date = sizeof($forcast_his_data) == 1 ? $forcast_his_data[0] : null;
                            $error_data->shour = sizeof($forcast_his_data) == 2 ? $forcast_his_data[1] : null;
                            $error_data->skill_name = sizeof($forcast_his_data) == 3 ? $forcast_his_data[2] : null;
                            $error_data->value = sizeof($forcast_his_data) == 4 ? $forcast_his_data[3] : null;
                            $error_data->service_type = sizeof($forcast_his_data) == 5 ? $forcast_his_data[4] : null;
                            $error_data->error = "Error Data Format";
                            array_push($error_data_list, $error_data);
                        }
                    }
                    fclose($fileHandler);

                    if ($data_uploaded) 
                    	$data['errMsg'] = "File Upload Complete";
                    $data['errData'] = $error_data_list;
                    $this->getTemplate()->display_popup('fc-hourly-his-data-form', $data, true);
                } else {
                    $data['errMsg'] = "Uploaded file must be csv and not bigger than 10 MB";
                    $data['errType'] = 0;
                }
            }
        }
        $this->getTemplate()->display_popup('fc-hourly-his-data-form', $data, true);
    }
}
