<?php
require_once 'BaseTableDataController.php';

class Menus extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	public function init()
	{
		$this->actionMenuList();
	}
	
	private function actionMenuList()
	{
		$data['pageTitle'] = 'Menu List';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=menus&act=add', 
										'img'=>'fa fa-user', 
										'label'=>'Add New Menu', 
										'title'=>'New Menu'
									)
								);

		$data['dataUrl'] = $this->url('task=menus&act=menu-grid-list');
		$data['userColumn'] = "Menu ID";
		$data['main_menu'] = 'menu';
		$this->getTemplate()->display('menu-list', $data);
	}

	public function actionMenuGridList(){
		include_once('model/MMenu.php');
		$menu_model = new MMenu();

		// search item
		$id = '';
		$name = '';
		if ($this->gridRequest->srcItem=="id") {
		    $id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="name") {
		    $name = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $menu_model->numMenus($id, $name, '');
		$menus = $this->pagination->num_records > 0 ? $menu_model->getMenus($id, $name, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$menus;
		if(!empty($result) && count($result)>0){
	        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {
				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this menu: " . $data->name . "?' data-href='" . $this->url("task=menus&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


				$data->name = "<a href='". $this->url("task=menus&act=update&id=".$data->id)."'>".$data->name."</a>";
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionAdd(){
		include_once('model/MMenu.php');
		include_once('model/MPage.php');
		include_once('model/MRole.php');
		include_once('lib/FormValidator.php');
		$page_model = new MPage();
		$menu_model = new MMenu();
		$role_model = new MRole();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$menu = '';
		$error_data = '';

		if ($request->isPost()) {					
			$response = $menu_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$menu = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		
		$page_list = $page_model->pageTreeViewWithCheckbox();
		$page_idx_list = $page_model->pageListIndexWithField('id', [
								'conditions' => [
									[
										'field'=> 'status',
										'value'=> STATUS_ACTIVE,
										'operator'=> '=',
									],
								],
							]
						);

		$data['pageTitle'] = 'Add New Menu';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=menus',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'menu';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['menu_data'] = $menu;
		$data['error_data'] = $error_data;
		$data['page_list'] = $page_list;
		$data['page_idx_list'] = $page_idx_list;
		$data['role_list'] = $role_model->getRoles();
		$data['status_list'] = get_status_list();
		$this->getTemplate()->display('menu-form', $data);
	}

	public function actionStatus(){
		include_once('model/MMenu.php');
		$menu_model = new MMenu();

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

		$menu = $menu_model->getMenus($id);
		if(empty($menu)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This menu is not exists!'
			]));
		}
		
		if($menu_model->updateMenuStatus($id, $status)){
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
		include_once('model/MMenu.php');
		include_once('model/MPage.php');
		include_once('model/MRole.php');
		include_once('lib/FormValidator.php');
		
		$page_model = new MPage();
		$menu_model = new MMenu();
		$role_model = new MRole();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$menu = '';
		$error_data = '';

		$menu = $menu_model->getMenus($request->getRequest('id'));
		if(empty($menu)){
			$errType = 1;
			$errMsg ='This menu is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $menu_model->updateData($this->getRequest()->getPost(), $menu[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$menu[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		$page_list = $page_model->pageTreeViewWithCheckbox();
		$page_idx_list = $page_model->pageListIndexWithField('id', [
								'conditions' => [
									[
										'field'=> 'status',
										'value'=> STATUS_ACTIVE,
										'operator'=> '=',
									],
								],
							]
						);

		$data['pageTitle'] = 'Update Menu';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=menus',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'menu';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['menu_data'] = $menu[0];
		$data['error_data'] = $error_data;
		$data['page_list'] = $page_list;
		$data['page_idx_list'] = $page_idx_list;
		$data['role_list'] = $role_model->getRoles();
		$data['status_list'] = get_status_list();
		$this->getTemplate()->display('menu-form', $data);
	}
}
