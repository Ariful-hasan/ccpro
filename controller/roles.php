<?php
require_once 'BaseTableDataController.php';

class Roles extends BaseTableDataController
{
    public $roleModel;
    public $pageModel;
    public $errorResponse;

    public function __construct()
    {
        parent::__construct();
        $this->insertClassFiles();
        $this->roleModel = new MRole();
        $this->pageModel = new MPage();
    }

    private function insertClassFiles()
    {
        include_once('model/MRole.php');
        include_once('model/MPage.php');
    }

	public function init()
	{
		$this->actionRoleList();
	}
	
	private function actionRoleList()
	{
		$data['pageTitle'] = 'User Role List';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=roles&act=add',
										'img'=>'fa fa-user',
										'label'=>'Add New Role',
										'title'=>'New Role'
									)
								);

		$data['dataUrl'] = $this->url('task=roles&act=role-grid-list');
		$data['userColumn'] = "Role ID";
		$data['main_menu'] = 'role';
		$this->getTemplate()->display('role-list', $data);
	}

	public function actionRoleGridList(){
		// search item
		$id = '';
		$name = '';
		if ($this->gridRequest->srcItem=="id") {
		    $id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="name") {
		    $name = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $this->roleModel->numRoles($id, $name, '');
		$roles = $this->pagination->num_records > 0 ? $this->roleModel->getRoles($id, $name, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$roles;
		
		if(!empty($result) && count($result)>0){
	        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {
				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this role: " . $data->name . "?' data-href='" . $this->url("task=roles&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


//				$data->name = "<a href='". $this->url("task=roles&act=update&id=".$data->id)."'>".$data->name."</a>";
				$data->name = "<a href='". $this->url("task=roles&act=page-info&id=".$data->id)."'>".$data->name."</a>";
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionAdd(){
		include_once('lib/FormValidator.php');

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$role = '';
		$error_data = '';

		if ($request->isPost()) {					
			$response = $this->roleModel->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$role = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Add New Role';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=roles',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['main_menu'] = 'role';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['role_data'] = $role;
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$this->getTemplate()->display('role-form', $data);
	}

	public function actionStatus(){

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		if(empty($id) || empty($status) || !in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE])){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}

		$role = $this->roleModel->getRoles($id);
		if(empty($role)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This role is not exists!'
			]));
		}
		
		if($this->roleModel->updateRoleStatus($id, $status)){
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
		include_once('lib/FormValidator.php');

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$role = '';
		$error_data = '';

		$role = $this->roleModel->getRoles($request->getRequest('id'));
		if(empty($role)){
			$errType = 1;
			$errMsg ='This role is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $this->roleModel->updateData($this->getRequest()->getPost(), $role[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$role[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}

		$data['pageTitle'] = 'Update Role';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=roles',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$role[0]->access_info = json_decode($role[0]->access_info, false);
		$data['main_menu'] = 'role';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['role_data'] = $role[0];
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$this->getTemplate()->display('role-form', $data);
	}

	// role access info codes from here
    private function getPages($pageId = '')
    {
        $pageList = array();
        $pages = $this->pageModel->getPages($pageId);

        foreach ($pages as $pageKey => $pageValue) {
            if ($pageValue->layout == "PRE" && $pageValue->path != "#") {
                $pageList[] = $pageValue;
            }
        }
        return $pageList;
    }

    public function actionPageInfo()
    {
        $roleId = $this->request->getRequest("id");
        $roleName = $this->roleModel->getRoles($roleId)[0]->name;
        $pages = $this->getPages();
        $pageList = array();
        foreach ($pages as $page) {
            $pageList [$page->id] = $page->id.' - '.$page->name;
        }
        $data['pageTitle'] = 'Page Roles : ' .$roleName;
        $data['topMenuItems'] = array(
            array(
                'href' => 'task=roles',
                'label' => 'Roles',
                'title' => 'Roles'
            )
        );
        $data['dataUrl'] = $this->url('task=roles&act=get-page-info&roleId='.$roleId);
        $data['main_menu'] = 'role';
        $data['pages'] = array("" => "All") + $pageList;
        $this->getTemplate()->display('role_pages', $data);
    }

    public function actionGetPageInfo()
    {
        $roleId = $this->request->getRequest("roleId");
        $pageId = '';
        if ($this->gridRequest->isMultisearch) {
            $pageId = $this->gridRequest->getMultiParam('page_id');
        }
        $this->pagination->num_records = $this->pageModel->numReportPages($pageId);
        $results = $this->pagination->num_records > 0 ?
            $this->pageModel->getReportPages($pageId, $this->pagination->getOffset(), $this->pagination->num_records) : null;

        foreach ($results as $result) {
            $url = $this->url("task=roles&act=update-page-access-info&pageId=" . $result->id . "&roleId=" . $roleId);;
            $result->action = "<a class='btn btn-primary btn-xs lightboxWIF' href='" . $url . "'><i class='fa fa-edit'>
                                </i> Update Access </a> ";
        }
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $results;
        $this->ShowTableResponse();
    }

    public function actionUpdatePageAccessInfo()
    {
        include ('controller/ajax-response.php');
        $this->errorResponse = new AjaxResponse();
        $request = $this->request;
        $roleId = $request->getRequest("roleId");
        $pageId = $request->getRequest("pageId");
        if ($request->isPost()) {
            $this->updatePageAccessInfo($roleId, $pageId);
        }
        $pageData = $this->getPages($pageId)[0];
        $data['hideColumnData'] = $this->getHideColumnData($roleId, $pageId);
        $data['dateRange'] = $this->getRoleAccessData($roleId, $pageId)->dateRange;
        $data['postResponse'] = $this->errorResponse->getResponseData();
        $data['pageTitle'] = 'Update Access Info';
        $data['roleId'] = $roleId;
        $data['pageId'] = $pageId;
        $data['path'] = $pageData->path;
        $data['ajaxPath'] = $pageData->ajax_path;
        $view = 'update_report_access_info';
        $this->getTemplate()->display_popup($view, $data, true);
    }

    private function updatePageAccessInfo($roleId, $pageId)
    {
        $hideColumns = $this->request->getRequest("hideColumns");
        $dateRange = $this->request->getRequest("dateRange");
        $path = $this->request->getRequest("path");
        $ajaxPath = $this->request->getRequest("ajaxPath");
        $accessData = $this->getRoleAccessData($roleId);
        $hasValue = false;
        foreach ($accessData as $data) {
            if ($data->pageId == $pageId) {
                $data->dateRange = $dateRange;
                $data->hideColumns = $hideColumns;
                if($data->path != $path){
                    $data->path = $path;
                }
                if($data->ajaxPath != $ajaxPath){
                    $data->ajaxPath = $ajaxPath;
                }
                $hasValue = true;
                break;
            }
        }
        $finalData = $accessData;
        if (!$hasValue) {
            $newData = new stdClass();
            $newData->pageId = $pageId;
            $newData->dateRange = $dateRange;
            $newData->hideColumns = $hideColumns;
            $newData->path = $path;
            $newData->ajaxPath = $ajaxPath;
            $finalData[] = $newData;
        }
        return $this->updatePageAccess($roleId, $finalData);
    }

    private function updatePageAccess($roleId, $finalData)
    {
        if ($this->roleModel->updateAccessInfo($roleId, json_encode($finalData))) {
            $this->errorResponse->setSuccessResponse("Updated Successfully!");
            return true;
        }
        $this->errorResponse->setFailedResponse("Update Failed!");
        return false;
    }

    private function getHideColumnData($roleId, $pageId)
    {
        $pageHideColumns = $this->getPages($pageId)[0]->report_fields;
        $pageHideColumns = ($pageHideColumns != null) ? explode(",", $pageHideColumns) : null;
        $roleHideColumns = $this->getRoleAccessData($roleId, $pageId)->hideColumns;
        $hideColumnData = array();
        foreach ($pageHideColumns as $pageHideColumn) {
            $data = new stdClass();
            $data->name = $pageHideColumn;
            if (in_array($pageHideColumn, $roleHideColumns)) {
                $data->isHidden = true;
            } else {
                $data->isHidden = false;
            }
            $hideColumnData [] = $data;
        }
        return $hideColumnData;
    }

    private function getRoleAccessData($roleId, $pageId = null)
    {
        $roleData = $this->roleModel->getRoles($roleId);
        $accessData = $roleData[0]->access_info;
        $accessData = json_decode($accessData);
        if ($pageId == null) {
            return $accessData;
        }
        foreach ($accessData as $data) {
            if ($data->pageId == $pageId) {
                return $data;
            }
        }
        return null;
    }

}
