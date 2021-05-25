<?php
require_once 'BaseTableDataController.php';

class Chattemplate extends BaseTableDataController
{
	function __construct() {
		parent::__construct();
		
		$licenseInfo = UserAuth::getLicenseSettings();
		if ($licenseInfo->chat_module == 'N') {
			header("Location: ./index.php");
			exit;
		}
	}

	function init()
	{
        $data['topMenuItems'] = array(array('href'=>'task=chattemplate&act=add', 'img'=>'fa fa-envelope-o', 'label'=>'Add New Template'));
		$data['side_menu_index'] = 'chat';
		$data['pageTitle'] = 'Chat Templates';
		$data['dataUrl'] = $this->url('task=get-chat-data&act=chattemplate');
		$this->getTemplate()->display('chat_templates', $data);
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveService($tid);
	}

	function actionDel()
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();

		$tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($et_model->deleteTemplate($tstamp)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>false, 'msg'=>' Template Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>true, 'msg'=>'Failed to Delete Template', 'redirectUri'=>$url));
		}
	}
	
	function actionSettings()
	{
	    include_once('model/MCcSettings.php');
	    include_once('config/constant.php');
		$settings_model = new MCcSettings();
		$settings_model->module_type = MOD_CHAT;
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$dispWindow = '';
		$data['chat_dispos_old'] = '';
		if ($request->isPost()) {
		    $post_data['chat_return_url'] = isset($_POST['chat_return_url']) ? trim($_POST['chat_return_url']) : '';
		    $post_data['chat_disposition'] = isset($_POST['chat_disposition']) ? trim($_POST['chat_disposition']) : '';
		    $post_data['otp_form_name'] = isset($_POST['otp_form_name']) ? trim($_POST['otp_form_name']) : '';
		    $post_data['otp_form_email'] = isset($_POST['otp_form_email']) ? trim($_POST['otp_form_email']) : '';
		    $post_data['smtp_host'] = isset($_POST['smtp_host']) ? trim($_POST['smtp_host']) : '';
		    $post_data['smtp_port'] = isset($_POST['smtp_port']) ? trim($_POST['smtp_port']) : '';
		    $post_data['smtp_username'] = isset($_POST['smtp_username']) ? trim($_POST['smtp_username']) : '';
		    $post_data['smtp_password'] = isset($_POST['smtp_password']) ? trim($_POST['smtp_password']) : '';
		    $post_data['smtp_secure_opton'] = isset($_POST['smtp_secure_opton']) ? trim($_POST['smtp_secure_opton']) : '';
		    $post_data['greetings_start_time'] = (isset($_POST['greetings']['start_time'])) ? implode(',', $_POST['greetings']['start_time']) : '';
		    $post_data['greetings_end_time'] = (isset($_POST['greetings']['end_time'])) ? implode(',', $_POST['greetings']['end_time']) : '';
		    $post_data['greetings_message'] = (isset($_POST['greetings']['message'])) ? implode(',', $_POST['greetings']['message']) : '';
            $post_data['offtime_from'] = (isset($_POST['offtime_from'])) ? trim($_POST['offtime_from']) : '';
            $post_data['offtime_to'] = (isset($_POST['offtime_to'])) ? trim($_POST['offtime_to']) : '';
            $post_data['appstore_link'] = (isset($_POST['appstore_link'])) ? trim($_POST['appstore_link']) : '';
            $post_data['playstore_link'] = (isset($_POST['playstore_link'])) ? trim($_POST['playstore_link']) : '';
            $post_data['chat_queue_text'] = (isset($_POST['chat_queue_text'])) ? trim($_POST['chat_queue_text']) : '';
            $post_data['ice_feedback_msg'] = (isset($_POST['ice_feedback_msg'])) ? trim($_POST['ice_feedback_msg']) : '';
            $post_data['blank_ice_feedback_msg'] = (isset($_POST['blank_ice_feedback_msg'])) ? trim($_POST['blank_ice_feedback_msg']) : '';
            $post_data['chat_queue_text'] = addslashes($post_data['chat_queue_text']);
            $post_data['ice_feedback_msg'] = addslashes($post_data['ice_feedback_msg']);
            $post_data['blank_ice_feedback_msg'] = addslashes($post_data['blank_ice_feedback_msg']);
		    $response = $settings_model->saveData($post_data);
		    if($response){
		    	$errMsg = 'Chat settings updated successfully !!';
                $errType = 0;
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=".$this->getRequest()->getActionName());
                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
		    }else{
		    	$errType = 1;
                $errMsg = 'No change found to update !!';
		    }
		} else {
		    $settings_data = $settings_model->getFormatAllSettings();
		}
//		dd($settings_data["offtime_from"]->value);
		$data['settings_data'] = $settings_data;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Global Chat Settings';
		
		$data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chattemplate_settings';
		$this->getTemplate()->display('chat_settings_form', $data);
	}
	
	function actionMessage()
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		
		$tstamp = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$data['service'] = "";
		$data['errMsg'] = "";
		$data['errType'] = 0;
		if (!empty($tstamp)){
			$data['service'] = $et_model->getTemplateById($tstamp);;
		}else {
			$data['errType'] = 1;
			$data['errMsg'] = "Email template not found!";
		}		
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Email Template Details';
		
		$this->getTemplate()->display_popup('email_template_details', $data);
	}
	
	function actionCopy()
	{
		$tstamp_code = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		include('model/MChatTemplate.php');
		$et_model = new MChatTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$tstamp_code = '';
			$service = $this->getSubmittedService($tstamp_code);
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tstamp_code)) {
					if ($et_model->addTemplate($service)) {
						$errMsg = 'Template added successfully !!';
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					} else {
						$errType = 1;
						$errMsg = 'Failed to add template !!';
					}
				}
			}			
		} else {
			$service = $this->getInitialService($tstamp_code, $et_model);
			if (empty($service)) {
				exit;
			}
		}
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
			$data['skills'] = $skill_model->getSkills('', 'C', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = '';
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['iscopy'] = true;
		$data['pageTitle'] = 'Add New Template';
		
		$data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chattemplate_';
		$this->getTemplate()->display('chat_template_form', $data);
	}
	
	function saveService($tstamp_code='')
	{
		include('model/MChatTemplate.php');
		$et_model = new MChatTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($tstamp_code);
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($tstamp_code)) {
					if ($et_model->addTemplate($service)) {
						$errMsg = 'Template added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add template !!';
					}
				} else {
					$oldtemplate = $this->getInitialService($tstamp_code, $et_model);
					if ($et_model->updateTemplate($oldtemplate, $service)) {
						$errMsg = 'Template updated successfully !!';
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
			$service = $this->getInitialService($tstamp_code, $et_model);
			if (empty($service)) {
				exit;
			}
		}
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
			$data['skills'] = $skill_model->getSkills('', 'C', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = $tstamp_code;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['iscopy'] = false;
		$data['pageTitle'] = empty($tstamp_code) ? 'Add New Template' : 'Update Template';
		
		$data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chattemplate_';
		$this->getTemplate()->display('chat_template_form', $data);
	}
	
	function getInitialService($tstamp_code, $et_model)
	{
		$service = null;

		if (empty($tstamp_code)) {
			$service = new stdClass();
			$service->tstamp = "";
			$service->title = "";
			$service->message = "";
			$service->status = "Y";
			$service->skill_id = "";
		} else {
			$service = $et_model->getTemplateById($tstamp_code);
		}
		return $service;
	}

	function getSubmittedService($tstamp_code)
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {		
				if ($key == "message"){
				    $service->$key = preg_replace( "/\r|\n/", "", trim($val) );
				    if (!empty($service->$key)){
				        $service->$key = addslashes($service->$key);
				    }
				}else {
				    $service->$key = trim($val);
				}
			}
		}

		return $service;
	}

	function getValidationMsg($service)
	{
		//if (empty($service->disposition_code)) return "Provide disposition code";
		if (empty($service->title)) return "Provide template title";
		if (empty($service->message)) return "Provide template text";
		if (strlen($service->message) > 255) return "Template text must not contain more than 255 characters";
		//if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_code)) return "Provide valid disposition code";
		//if (!preg_match("/^[0-9a-zA-Z_ ]{1,40}$/", $service->service_title)) return "Provide valid service title";
		/*
		if ($service->disposition_code != $tstamp_code) {
			$existing_code = $ivr_model->getTemplateById($service->disposition_code);
			if (!empty($existing_code)) return "Disposition code $service->disposition_code already exist";
		}
		*/
		
		return '';
	}
	
	function chatRatingPage()
	{
		$this->getTemplate()->display_only('chat_rating');
	}

	function actionLeaveMessages()
	{
		$data['pageTitle'] = 'Leave Message List';
		$data['dataUrl'] = $this->url('task=chattemplate&act=leave-message-grid-list');
		$data['userColumn'] = "ID";
		$this->getTemplate()->display('leave-message-list', $data);
	}

	function actionLeaveMessageGridList(){
		include_once('model/MChatLeaveMessage.php');
		$leave_messages_model = new MChatLeaveMessage();

		// search item
		$field = $this->gridRequest->srcItem;
		$srcText = $this->gridRequest->srcText;

		$this->pagination->num_records = $leave_messages_model->numLeaveMessages($field, $srcText, '');
		$messages = $this->pagination->num_records > 0 ? $leave_messages_model->getChatLeaveMessages($field, $srcText, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$messages;
		if(!empty($result) && count($result)>0){
	        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {
				$data->customer_info = $data->cus_ip."<br/>".$data->cus_browser;
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}
	
}
