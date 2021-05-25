<?php

class Chatpage extends Controller
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
        $data['topMenuItems'] = array(array('href'=>'task=chatpage&act=add', 'img'=>'fa fa-envelope-o', 'label'=>'Add New Chat Page'));
		$data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chatpage_';
		$data['pageTitle'] = 'Chat Page';
		$data['dataUrl'] = $this->url('task=get-chat-data&act=chatpage');
		$this->getTemplate()->display('chat_page', $data);
	}

	function actionAdd()
	{
		$this->saveService();
	}

	function actionUpdate()
	{
		$tid = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
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
	
	function saveService($page_id='')
	{
		include('model/MChatTemplate.php');
		$et_model = new MChatTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($page_id);
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				if (empty($page_id)) {
				    $pageExist = $et_model->getChatPageById($service->page_id);
				    if ($pageExist != null && !empty($pageExist->page_id)){
				        $errMsg = 'Same Chat page already exist !!';
				    }else {
    					if ($et_model->addChatPage($service)) {
    						$errMsg = 'Chat page added successfully !!';
    						$is_success = true;
    					} else {
    						$errMsg = 'Failed to add chat page !!';
    					}
				    }
				} else {
				    $readyToUpdate = true;
					$oldtemplate = $this->getInitialService($page_id, $et_model);
					if ($service->page_id != $oldtemplate->page_id) {
					    $pageExist = $et_model->getChatPageById($service->page_id);
					    if ($pageExist != null && !empty($pageExist->page_id)){
					        $errMsg = 'Same Chat page already exist !!';
					        $readyToUpdate = false;
					    }
					}
					if ($readyToUpdate){
    					if ($et_model->updateChatPage($oldtemplate, $service)) {
    						$errMsg = 'Chat page updated successfully !!';
    						$is_success = true;
    					} else {
    						$errMsg = 'No change found !!';
    					}
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($page_id, $et_model);
			if (empty($service)) {
				exit;
			}
		}
		include('model/MLanguage.php');
		include('model/MSkill.php');
		$skill_model = new MSkill();
		if (UserAuth::hasRole('admin')) {
			$data['skills'] = $skill_model->getSkills('', 'C', 0, 100);
		} else {
			$data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
		}
		
		$data['languages'] = MLanguage::getActiveLanguageListArray();
		$data['themes'] = $et_model->getChatTheme();
		$data['service'] = $service;
		$data['page_id'] = $page_id;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($page_id) ? 'Add New Chat Page' : 'Update Chat Page';
		
		$data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chatpage_';
		$this->getTemplate()->display('chat_page_form', $data);
	}
	
	function getInitialService($page_id, $et_model)
	{
		$service = null;

		if (empty($page_id)) {
			$service = new stdClass();
			$service->page_id = "";
			$service->www_domain = "";
			$service->www_ip = "";
			$service->skill_id = "";
			$service->site_key = "";
			$service->language = "";
			$service->max_session_per_agent = "4";
			$service->theme = "";
			$service->active = "Y";
		} else {
			$service = $et_model->getChatPageById($page_id);
		}
		return $service;
	}

	function getSubmittedService($page_id)
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}

		return $service;
	}

	function getValidationMsg($service)
	{
		if (empty($service->page_id)) return "Provide page id";
		if (!preg_match("/^[0-9a-zA-Z_-]{1,11}$/", $service->page_id)) return "Provide valid page id";
		if (empty($service->www_domain)) return "Provide domain";
		if (empty($service->www_ip)) return "Provide ip";
		if(!filter_var($service->www_ip, FILTER_VALIDATE_IP)) return "Provide valid ip";
		
		if (empty($service->skill_id)) return "Provide skill";
		if (empty($service->site_key)) return "Provide site key";
		if (empty($service->language)) return "Provide language";
		if (empty($service->max_session_per_agent)) return "Provide max session";
		if (empty($service->theme)) return "Provide theme";
		
		return '';
	}
	
	function is_valid_domain_name($domain_name)
	{
	    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
	        && preg_match("/^.{1,253}$/", $domain_name) //overall length check
	        && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
	}
	
	function actionShowEmbeddedUrl(){
	    include('model/MChatTemplate.php');
	    $chat_model = new MChatTemplate();
	    
	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';	    
	    $chatData = $chat_model->getChatPageById($page_id, "active='Y'");
	    
	    if ($chatData != null){
	        $layout = $chatData->theme;
    	    $domain = $chatData->www_domain;
    	    $page_id = $chatData->page_id;
    	    $site_key = $chatData->site_key;
    	    $www_ip = $chatData->www_ip;
    	    $language = $chatData->language;
    	    $user = UserAuth::getDBSuffix();
    	    
    	    $urlParam = "layout=".$layout."&domain=".$domain."&page_id=".$page_id."&site_key=".$site_key."&www_ip=".$www_ip."&user=".$user."&language=".$language;
    	    $urlParam = base64_encode($urlParam);
    	    $embeddedUrl = $this->getBaseUrl()."/chat.php?".$urlParam;
    	    echo "<textarea rows='3' style='width:100%;' id='embeddedTxtBox' onClick='this.select();' readonly='readonly'>";
    	    echo $embeddedUrl;
    	    echo "</textarea>";
    	    echo "<script type='text/javascript'>";
    	    echo "setTimeout(function(){";
    	    echo "setTxtSelected();";
    	    echo "}, 400);";
    	    echo "function setTxtSelected(){ var text_input = document.getElementById('embeddedTxtBox'); text_input.focus(); text_input.select();}";
    	    echo "</script>";
	    }else {
	        echo "<div style='color:#ff0000;'>";
	        echo "Invalid Chat Page Information!";
	        echo "</div>";
	    }
	}
	
	function actionChatService()
	{
	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $data['topMenuItems'] = array(array('href'=>'task=chatpage&act=addChatService&pid='.urlencode($page_id), 'img'=>'fa fa-commenting-o', 'label'=>'Add New Chat Service'), array('href'=>'task=chatpage', 'img'=>'fa fa-weixin', 'label'=>'Chat Page'));
	    $data['side_menu_index'] = 'chat';
	    $data['smi_selection'] = 'chatpage_';
	    $data['pageTitle'] = 'Chat Service';
	    $data['dataUrl'] = $this->url('task=get-chat-data&act=chatservice&pid='.urlencode($page_id));
	    $this->getTemplate()->display('chat_service', $data);
	}
	
	function actionAddChatService(){
	    include('model/MChatTemplate.php');
	    $et_model = new MChatTemplate();
	    
	    $data['topMenuItems'] = array(array('href'=>'task=chatpage', 'img'=>'fa fa-weixin', 'label'=>'Chat Page'));
	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $request = $this->getRequest();
	    $errMsg = '';
	    $errType = 1;
	    $service = new stdClass();
	    $service->service_name = "";
	    $service->status = "Y";
	    if ($request->isPost()) {
	        $service->service_name = isset($_REQUEST['service_name']) ? trim($_REQUEST['service_name']) : '';
	        $service->status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	        $service->skill_id = isset($_REQUEST['skill_id']) ? trim($_REQUEST['skill_id']) : '';
            $service->page_id = $page_id;
            
	        if (empty($service->page_id)) $errMsg = "Provide page id";
	        elseif (empty($service->service_name)) $errMsg = "Provide service name";
	        else {
	            $is_success = false;	            
	            if ($et_model->addChatService($service)) {
                    $errMsg = 'Chat service added successfully !!';
                    $is_success = true;
                } else {
                    $errMsg = 'Failed to add chat service !!';
                }
	    
	            if ($is_success) {
	                $errType = 0;
	                $url = $this->getTemplate()->url("task=chatpage&act=chatService&pid=".urlencode($page_id));
	                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
	            }
	        }	        	
	    }
	    $data['skills'] = array();
	    include('model/MSkill.php');
	    $skill_model = new MSkill();
	    if (UserAuth::hasRole('admin')) {
	        $data['skills'] = $skill_model->getSkills('', 'C', 0, 100);
	    } else {
	        $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
	    }
	    
	    $data['service'] = $service;
	    $data['page_id'] = $page_id;
	    $data['service_id'] = '';
	    $data['request'] = $request;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['pageTitle'] = 'Add New Chat Service';
	    
	    $data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chatpage_';
	    $this->getTemplate()->display('chat_service_form', $data);
	}
	
	function actionUpdateChatService(){
	    include('model/MChatTemplate.php');
	    $et_model = new MChatTemplate();
	    
	    $data['topMenuItems'] = array(array('href'=>'task=chatpage', 'img'=>'fa fa-weixin', 'label'=>'Chat Page'));
	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $service_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
	    $request = $this->getRequest();
	    $errMsg = '';
	    $errType = 1;
	    $service = new stdClass();	    
	    $service = $et_model->getChatServiceById($service_id);
	    if ($request->isPost()) {
	        $service->service_name = isset($_REQUEST['service_name']) ? trim($_REQUEST['service_name']) : '';
	        $service->status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	        $service->skill_id = isset($_REQUEST['skill_id']) ? trim($_REQUEST['skill_id']) : '';
            $service->page_id = $page_id;
            $service->service_id = $service_id;
            
	        if (empty($service->page_id)) $errMsg = "Provide page id";
	        elseif (empty($service->service_name)) $errMsg = "Provide service name";
	        else {
	            $is_success = false;
	            $oldService = $et_model->getChatServiceById($service_id);
                if ($et_model->updateChatService($oldService, $service)) {
                    $errMsg = 'Chat service updated successfully !!';
                    $is_success = true;
                } else {
                    $errMsg = 'No change found !!';
                }
	    
	            if ($is_success) {
	                $errType = 0;
	                $url = $this->getTemplate()->url("task=chatpage&act=chatService&pid=".urlencode($page_id));
	                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
	            }
	        }	        	
	    }
	    $data['skills'] = array();
	    include('model/MSkill.php');
	    $skill_model = new MSkill();
	    if (UserAuth::hasRole('admin')) {
	        $data['skills'] = $skill_model->getSkills('', 'C', 0, 100);
	    } else {
	        $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'C', 0, 100);
	    }
	    
	    $data['service'] = $service;
	    $data['page_id'] = $page_id;
	    $data['service_id'] = $service_id;
	    $data['request'] = $request;
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['pageTitle'] = 'Update Chat Service';
	     
	    $data['side_menu_index'] = 'chat';
		$data['smi_selection'] = 'chatpage_';
	    $this->getTemplate()->display('chat_service_form', $data);
	}
	
	function getBaseUrl(){
	    //$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https://' : 'http://';
	    $protocol = '//';
	    $path = $_SERVER['PHP_SELF'];
	    $path_parts = pathinfo($path);
	    $directory = $path_parts['dirname'];
	    $directory = ($directory == "/") ? "" : $directory;
	    $host = $_SERVER['HTTP_HOST'];
	    return $protocol . $host . $directory;
	}
}
