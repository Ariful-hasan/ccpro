<?php

class Maddresses extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MAppMapAddress.php');
		include('lib/Pagination.php');
		$address_model = new MAppMapAddress();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $address_model->numAddresses();
		$data['addresses'] = $pagination->num_records > 0 ? 
			$address_model->getAddresses($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['addresses']) ? count($data['addresses']) : 0;
		$data['pagination'] = $pagination;

		//$data['agent_model'] = $agent_model;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Map Address';
		
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'maddresses_';
		
		$data['topMenuItems'] = array(array('href'=>'task=maddresses&act=add', 'img'=>'add.png', 'label'=>'Add New Address'));
		$this->getTemplate()->display('addresses', $data);
	}

	function actionAdd()
	{
		$this->saveAddress('');
	}

	function actionUpdate()
	{
		$addressid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : '';
		$this->saveAddress($addressid);
	}

	function actionDel()
	{
		include('model/MAppMapAddress.php');
		$address_model = new MAppMapAddress();

		$aid = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		$_title = 'Address';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($address_model->deleteAddress($aid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete '.$agent_title, 'isError'=>false, 'msg'=>$agent_title.' Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete '.$agent_title, 'isError'=>true, 'msg'=>'Failed to Delete '.$agent_title, 'redirectUri'=>$url));
		}
	}
	
	function saveAddress($aid='')
	{
		include('model/MAppMapAddress.php');
		$address_model = new MAppMapAddress();
		
		//$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
		//$data['skill_options'] = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module);
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$address = $this->getSubmittedAddress($aid);
			$errMsg = $this->getValidationMsg($address, $aid);
			
			if (empty($errMsg)) {
				
					$is_success = false;					
					
					if (empty($aid)) {
						if ($address_model->addAddress($address)) {
							$is_success = true;
						} else {
							$errMsg = 'Failed to add address !!';
						}
					} else {
						$oldaddress = $this->getInitialAddress($aid, $address_model);
						if ($address_model->updateAddress($oldaddress, $address)) {
							//$errMsg = $agent_title.' updated successfully !!';
							$is_success = true;
						} else {
							$errMsg = 'No change found !!';
						}
					}
					
					if ($is_success) {
						$errType = 0;
						if (empty($agentid)) {
							$errMsg = 'Address added successfully !!';
						} else {
							$errMsg = 'Address updated successfully !!';
						}
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" 
							CONTENT=\"2;URL=$url\">";
					}
			}
			
		} else {
			$address = $this->getInitialAddress($aid, $address_model);
			if (empty($address)) {
				exit;
			}
		}
		
		$data['address'] = $address;
		$data['aid'] = $aid;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$agent_title = 'Address';
		$data['pageTitle'] = empty($agentid) ? 'Add New '.$agent_title : 'Update '.$agent_title.' : ' . $agentid;
		//$data['smi_selection'] = 'agents_';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'maddresses_';
		$this->getTemplate()->display('address_form', $data);
	}
	
	function getInitialAddress($aid, $address_model)
	{
		$address = null;

		if (empty($aid)) {
			$address = new stdClass();
			$address->address_id = "";
			$address->branch_name = "";
			$address->address = "";
			$address->latitude = "";
			$address->longitude = '';
		} else {
			$address = $address_model->getAddressById($aid);
			if (empty($address)) {
				exit;
			}
		}
		return $address;
	}

	function getSubmittedAddress($aid)
	{
		$posts = $this->getRequest()->getPost();
		$address = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$address->$key = trim($val);
			}
		}
		if (!empty($aid)) $address->address_id = $aid;
		//var_dump($address);
		return $address;
	}

	function getValidationMsg($address, $aid='')
	{
		$err = '';

		if (empty($address->longitude)) $err = "Provide address longitude";
		if (empty($address->latitude)) $err = "Provide address latitude";
		if (empty($address->address)) $err = "Provide branch address";
		if (empty($address->branch_name)) $err = "Provide branch name";
		
		//if (empty($user->role)) $err = "Provide User Role";
		return $err;
	}
	
}
