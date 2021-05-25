<?php
require_once 'BaseTableDataController.php';

class Pages extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	public function init()
	{
		$this->actionPageList();
	}
	
	private function actionPageList()
	{
		$data['pageTitle'] = 'Page List';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=pages&act=add', 
										'img'=>'fa fa-user', 
										'label'=>'Add New Page', 
										'title'=>'New Page'
									)
								);

		$data['dataUrl'] = $this->url('task=pages&act=page-grid-list');
		$data['userColumn'] = "Page ID";
		$data['main_menu'] = 'page';
		$this->getTemplate()->display('page-list', $data);
	}

	public function actionPageGridList(){
		include_once('model/MPage.php');
		$page_model = new MPage();

		// search item
		$id = '';
		$name = '';
		if ($this->gridRequest->srcItem=="id") {
		    $id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="name") {
		    $name = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $page_model->numPages($id, $name, '');
		$pages = $this->pagination->num_records > 0 ? $page_model->getPages($id, $name, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$pages;
		if(!empty($result) && count($result)>0){
	        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {
				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this page: " . $data->name . "?' data-href='" . $this->url("task=pages&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


				$data->name = "<a href='". $this->url("task=pages&act=update&id=".$data->id)."'>".$data->name."</a>";
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionAdd(){
		include_once('model/MPage.php');
		include_once('lib/FormValidator.php');
		$page_model = new MPage();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$page = '';
		$error_data = '';

		if ($request->isPost()) {					
			$response = $page_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$page = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		
		$parent_page_list = $page_model->pageTreeViewWithRadio();

		$data['pageTitle'] = 'Add New Page';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=pages',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'page';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['page_data'] = $page;
		$data['error_data'] = $error_data;
		$data['parent_page_list'] = $parent_page_list;
		$data['status_list'] = get_status_list();
		$data['layout_list'] = get_page_layout_list();
		$this->getTemplate()->display('page-form', $data);
	}

	public function actionStatus(){
		include_once('model/MPage.php');
		$page_model = new MPage();

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		// var_dump(empty($id));
		// var_dump(empty($id));

		if(empty($id) || empty($status) || !in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE])){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}

		$page = $page_model->getPages($id);
		if(empty($page)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This page is not exists!'
			]));
		}
		
		if($page_model->updatePageStatus($id, $status)){
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

	public function actionUpdate(){
		include_once('model/MPage.php');
		include_once('lib/FormValidator.php');
		$page_model = new MPage();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$page = '';
		$error_data = '';

		$page = $page_model->getPages($request->getRequest('id'));
		if(empty($page)){
			$errType = 1;
			$errMsg ='This page is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $page_model->updateData($this->getRequest()->getPost(), $page[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$page[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		$parent_page_list = $page_model->pageTreeViewWithRadio($page[0]->parent);

		$data['pageTitle'] = 'Update Page';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=pages',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'page';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['page_data'] = $page[0];
		$data['error_data'] = $error_data;
		$data['parent_page_list'] = $parent_page_list;
		$data['status_list'] = get_status_list();
		$data['layout_list'] = get_page_layout_list();
		$this->getTemplate()->display('page-form', $data);
	}
}
