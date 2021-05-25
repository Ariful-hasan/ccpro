<?php

class Settbmsg extends Controller
{
	function __construct() {
		parent::__construct();
	}
	
	function init()
	{
	    $this->actionSettBMsg();
	}
	
	function actionSettBMsg()
	{
	    $data['pageTitle'] = 'Busy Message List';
	    //$data['topMenuItems'] = array(array('href'=>'task=agents&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Agent'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('settings_busy_messages', $data);
	}

	function init2()
	{
		include('model/MSetting.php');
		include('lib/Pagination.php');
		$setting_model = new MSetting();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $setting_model->numBusyMessages();
		$data['busy_messages'] = $pagination->num_records > 0 ? 
			$setting_model->getBusyMessages('', $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['busy_messages']) ? count($data['busy_messages']) : 0;
		$data['pagination'] = $pagination;

		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Busy Message List';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_busy_messages', $data);
	}

	function actionActivate()
	{
		$bid = isset($_REQUEST['bid']) ? trim($_REQUEST['bid']) : '';
		$status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		
		if ($status == 'Y' || $status == 'N') {
			include('model/MSetting.php');
			$setting_model = new MSetting();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&page=$pageNum");
			if ($setting_model->updateBusyMessageStatus($bid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Busy Message', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Busy Message', 'isError'=>true, 'msg'=>'Failed to Update Status', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionUpdate()
	{
		$bid = isset($_REQUEST['bid']) ? trim($_REQUEST['bid']) : '';
		if ($bid>11 && $bid<20) {
			$this->saveBusyMessage($bid);
		}
	}

	function saveBusyMessage($bid='')
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {

			$busy_message = $this->getSubmittedBusyMessage($bid);
			$errMsg = $this->getValidationMsg($busy_message);

			if (empty($errMsg)) {
				$oldmessage = $this->getInitialBusyMessage($bid, $setting_model);
				if (!empty($oldmessage)) {
					$is_success = false;

					if ($setting_model->updateBusyMessage($oldmessage, $busy_message)) {
						$errMsg = 'Message updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
					
					if ($is_success) {
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'Invalid Message !!';
				}
			}

		} else {
			$busy_message = $this->getInitialBusyMessage($bid, $setting_model);
		}

		$data['bid'] = $bid;
		$data['busy_message'] = $busy_message;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update Busy Message';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settbmsg_';
		$this->getTemplate()->display('settings_bmessage_form', $data);
	}

	function getInitialBusyMessage($bid, $setting_model)
	{
		$bmsg = null;

		$bmsg = $setting_model->getBusyMessageById($bid);
		if (empty($bmsg)) {
			exit;
		}
		return $bmsg;
	}

	function getSubmittedBusyMessage($bid)
	{
		$posts = $this->getRequest()->getPost();
		$msg = null;

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$msg->$key = trim($val);
			}
		}
		$msg->aux_code = $bid;

		return $msg;
	}

	function getValidationMsg($busy_message)
	{
		$err = '';
		if (empty($busy_message->message)) $err = 'Message is Required';
		return $err;
	}

}
