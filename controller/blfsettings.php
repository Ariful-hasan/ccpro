<?php 
class Blfsettings extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    include('model/MBlf_key.php');
	    $blf_model = new MBlf_key();
	    
	    $seat = isset($_REQUEST['seat']) ? trim($_REQUEST['seat']) : '';
	    $seatInfo = $blf_model->getSeatById($seat);
	    if (empty($seatInfo)) exit;
	    
	    $data['pageTitle'] = 'BLF Settings';
	    $data['tabTitle'] = 'BLF Settings for Seat - ' . $seatInfo->label;
	    $data['side_menu_index'] = 'settings';
	    $data['smi_selection'] = 'settseats_';
	    $this->getTemplate()->display_cbody('settings_blf', $data);
	}
	
	function actionAdd()
	{
	    $seat = isset($_REQUEST['seat']) ? trim($_REQUEST['seat']) : '';
	    $unit = isset($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';
	    $this->saveService($seat, $unit);
	}
	
	function actionUpdate()
	{
	    $seat = isset($_REQUEST['seat']) ? trim($_REQUEST['seat']) : '';
	    $unit = isset($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';
	    $bkey = isset($_REQUEST['bkey']) ? trim($_REQUEST['bkey']) : '';
	    $this->saveService($seat, $unit, $bkey);
	}
	
	function saveService($seat, $unit, $bkey='')
	{
	    include('model/MBlf_key.php');
	    $blf_model = new MBlf_key();
	
	    $seatInfo = $blf_model->getSeatById($seat);
	    if (empty($seatInfo)) exit;
	
	    $request = $this->getRequest();
	    $groups = null;
	    $errMsg = '';
	    $errType = 1;
	    if ($request->isPost()) {
	        $service = $this->getSubmittedService();
	        $errMsg = $this->getValidationMsg($service);
	        	
	        if (empty($errMsg)) {
	            $is_success = false;
	            
	            if (empty($bkey)) {
	                if ($blf_model->addBlfNewKey($seat, $unit, $service)) {
	                    $errMsg = 'BLF Settings added successfully !!';
	                    $is_success = true;
	                    $errType = 0;
	                } else {
	                    $errMsg = 'Failed to add BLF Settings !!';
	                }
	            } else {
	                $oldservice = $this->getInitialService($seat, $unit, $bkey, $blf_model);
	                if ($blf_model->updateBlfSettings($seat, $unit, $bkey, $oldservice, $service)) {
	                    $errMsg = 'BLF Settings updated successfully !!';
	                    $is_success = true;
	                } else {
	                    $errMsg = 'No change found !!';
	                }
	            }
	
	            if ($is_success) {
	                $errType = 0;
	                $url = $this->getTemplate()->url("task=blfsettings&seat=$seat&unit=$unit");
	                $data['redirectURL'] = $url;
	            }
	        }
	        	
	    } else {
	        $service = $this->getInitialService($seat, $unit, $bkey, $blf_model);
	        if (empty($service)) {
	            exit;
	        }
	    }
	    
	    $keyList = $blf_model->getAllBlfKeys($seat, $unit);
	
	    $data['seat'] = $seat;
	    $data['unit'] = $unit;
	    $data['bkey'] = $bkey;
	    $data['keylist'] = $keyList;
	    $data['request'] = $request;
	    $data['service'] = $service;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['pageTitle'] = empty($bkey) ? 'Add New BLF Settings' : 'Update BLF Settings';
	    $data['pageTitle'] .= ' for Seat - ' . $seatInfo->label;
	    $data['side_menu_index'] = 'settings';
	    $data['smi_selection'] = 'settseats_';
	    $this->getTemplate()->display_popup('blf_settings_form', $data);
	}

	function getInitialService($seat, $unit, $bkey='', $blf_model)
	{
	    $service = new stdClass();
	
	    if (empty($bkey)) {
	        $service->key_id = '';
	        $service->label = '';
	        $service->monitor = '';
	        $service->type = '';
	    } else {
	        $service = $blf_model->getBlfSettingByKey($seat, $unit, $bkey);
	    }
	    return $service;
	}
	
	function getSubmittedService()
	{
	    $posts = $this->getRequest()->getPost();
	    $service = new stdClass();
	    if (is_array($posts)) {
	        foreach ($posts as $key=>$val) {
	            $value = str_replace(array("\\", "\0", "\n", "\r", "'", '"', "\x1a"), '', $val);
	            $service->$key = trim($value);
	        }
	    }

	    return $service;
	}
	
	function getValidationMsg($service)
	{
	    if (empty($service->label)) return "Provide Title";
	    if (empty($service->key_id)) return "Provide Key";
	    if (empty($service->monitor)) return "Provide Monitor";
	    if (strlen($service->monitor) < 3) return "Monitor must contain 3/4 characters";
	
	    return '';
	}
}
