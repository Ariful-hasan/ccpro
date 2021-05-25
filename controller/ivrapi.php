<?php

class Ivrapi extends Controller
{
	function __construct() {
		parent::__construct();
	}
	
	function init()
	{
	    $this->actionIvrApi();
	}
	
	function actionIvrApi()
	{
	    $data['pageTitle'] = 'IVR API Connections';
	    $data['topMenuItems'] = array(array('href'=>'task=ivrapi&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Connection'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('ivr_apis', $data);
	}

	function init2()
	{
		include('model/MIvrAPI.php');
		include('lib/Pagination.php');
		$ivr_model = new MIvrAPI();
		$pagination = new Pagination();
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $ivr_model->numConnections();
		$data['connections'] = $pagination->num_records > 0 ? 
			$ivr_model->getConnections($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['connections']) ? count($data['connections']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'IVR API Connections';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=ivrapi&act=add', 'img'=>'add.png', 'label'=>'Add New Connection'));
		$data['submit_methods'] = array('A'=>'Array', 'C'=>'Class', 'P'=>'Param', 'J'=>'JSON', 'X'=>'XML');
		$this->getTemplate()->display('ivr_apis', $data);
	}

	function actionAdd()
	{
		$this->saveConnection();
	}

	function actionUpdate()
	{
		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$this->saveConnection($cid);
	}

	function actionDel()
	{
		include('model/MIvrAPI.php');
		$ivr_model = new MIvrAPI();

		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
		
		if ($ivr_model->deleteConnection($cid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Connection', 'isError'=>false, 'msg'=>'Connection Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Connection', 'isError'=>true, 'msg'=>'Failed to Delete Connection', 'redirectUri'=>$url));
		}
	}
	
	function saveConnection($cname='')
	{
		include('model/MIvrAPI.php');
		$ivr_model = new MIvrAPI();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$conn_methods = array('SOAP'=>'SOAP', 'ODBC'=>'ODBC', 'HTTP'=>'HTTP');
		$yn_options = array('Y'=>'Yes', 'N'=>'No');
		$yn2_options = array('Y'=>'Enable', 'N'=>'Disable');
		$submit_methods = array('A'=>'Array', 'C'=>'Class', 'P'=>'Param', 'J'=>'JSON', 'X'=>'XML');
		
		if ($request->isPost()) {
			$connection = $this->getSubmittedConnection($cname);
			
			//var_dump($connection);
			//exit;
			$errMsg = $this->getValidationMsg($connection, $cname, $ivr_model);
			
			if (empty($errMsg)) {
				$is_success = false;

				if (empty($cname)) {
					if ($ivr_model->addConnection($connection)) {
						$errMsg = 'Connection added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add connection !!';
					}
				} else {
					$value_options = array(
						'conn_method' => $conn_methods,
						'url' => '',
						'credential' => '',
						'pass_credential' => $yn_options,
						'submit_method' => $submit_methods,
						'return_method' => $submit_methods,
						'active' => $yn2_options
					);

					$oldconn = $this->getInitialConnection($cname, $ivr_model);
					if ($ivr_model->updateConnection($oldconn, $connection, $value_options)) {
						$errMsg = 'Connection updated successfully !!';
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
			$connection = $this->getInitialConnection($cname, $ivr_model);
			if (empty($connection)) {
				exit;
			}
		}
		
		$data['connection'] = $connection;
		$data['conn_method_options'] = $conn_methods;
		$data['yn_options'] = $yn_options;
		$data['yn2_options'] = $yn2_options;
		$data['submit_methods'] = $submit_methods;
		$data['cname'] = $cname;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($cname) ? 'Add New Connection' : 'Update Connection'.' : ' . $cname;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'ivrapi_';
		$this->getTemplate()->display('ivr_api_form', $data);
	}
	
	function getInitialConnection($cname, $ivr_model)
	{
		$connection = null;

		if (empty($cname)) {
		    $connection=new stdClass();
			$connection->conn_name = '';
			$connection->conn_method = '';
			$connection->url = '';
			$connection->credential = '';
			$connection->pass_credential = 'N';
			$connection->submit_method = '';
			$connection->submit_param = '';
			$connection->return_method = '';
			$connection->return_param = '';
			$connection->active = 'Y';
		} else {
			$connection = $ivr_model->getConnectionByName($cname);
		}
		return $connection;
	}

	function getSubmittedConnection($cname)
	{
		$posts = $this->getRequest()->getPost();
		$connection = null;
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$connection->$key = trim($val);
			}
		}

		if (!empty($cname)) $connection->conn_name = $cname;

		return $connection;
	}

	function getValidationMsg($connection, $cname='', $ivr_model)
	{
		if (empty($connection->conn_name)) return "Provide connection name";
		if (!preg_match("/^[0-9a-zA-Z_]{1,8}$/", $connection->conn_name)) return "Provide valid connection name";
		
		if ($connection->conn_name != $cname) {
			$existing_conn = $ivr_model->getConnectionByName($connection->conn_name);
			if (!empty($existing_conn)) return "Connection name $connection->conn_name already exist";
		}
		
		return '';
	}
	
}
