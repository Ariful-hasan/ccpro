<?php
require_once 'BaseTableDataController.php';

class EmailFetchInboxes extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	private $row_color = array("email_inbox_blue"=>"Light Blue", "email_inbox_red"=>"Light Red", "email_inbox_green"=>"Light Green");

	public function init()
	{
		$this->actionEmailFetchInboxList();
	}
	
	private function actionEmailFetchInboxList()
	{
		$data['pageTitle'] = 'Email Fetch Inbox List';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=email-fetch-inboxes&act=add', 
										'img'=>'fa fa-user', 
										'label'=>'Add New Item', 
										'title'=>'New Item'
									)
								);

		$data['dataUrl'] = $this->url('task=email-fetch-inboxes&act=inbox-grid-list');
		$data['userColumn'] = "ID";
		$this->getTemplate()->display('email-fetch-inbox-list', $data);
	}

	public function actionInboxGridList(){
		include_once('model/MEmailFetchInbox.php');
		$ef_inbox_model = new MEmailFetchInbox();

		// search item
		$id = '';
		$name = '';
		if ($this->gridRequest->srcItem=="id") {
		    $id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="name") {
		    $name = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $ef_inbox_model->numEmailFetchInboxes($id, $name, '');
		$ef_inboxes = $this->pagination->num_records > 0 ? $ef_inbox_model->getEmailFetchInboxes($id, $name, '', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;
		$result=&$ef_inboxes;
		if(!empty($result) && count($result)>0){
	        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {
				$data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this item: " . $data->name . "?' data-href='" . $this->url("task=email-fetch-inboxes&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";


				$data->name = "<a href='". $this->url("task=email-fetch-inboxes&act=update&id=".$data->id)."'>".$data->name."</a>";
			}
		}

		$response->rowdata = $result;		
		$this->ShowTableResponse();
	}

	public function actionAdd(){
		include_once('model/MEmailFetchInbox.php');
		include_once('lib/FormValidator.php');
		include_once('model/MSkill.php');
		$skill_model = new MSkill();
		$ef_inbox_model = new MEmailFetchInbox();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$ef_inbox = '';
		$error_data = '';

		if ($request->isPost()) {
            $post_data = $this->getRequest()->getPost();
		    $temp = $post_data['viewable_skills'];
		    $viewable_skills = isset($temp) ? rtrim(implode(",",$temp),',') : "";

		    unset($post_data['viewable_skills']);
            $post_data['viewable_skills'] = $viewable_skills;
			$response = $ef_inbox_model->saveData($post_data);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$ef_inbox = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}		
		if (UserAuth::hasRole('admin')) {
            $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
        } else {
            $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
        }

		$data['pageTitle'] = 'Add New Inbox';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=email-fetch-inboxes',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['ef_inbox_data'] = $ef_inbox;
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$data['yes_no_list'] = array("Y"=>"Yes", "N"=>"No");
		$data['fetch_method_list'] = get_email_fetch_method_list();
		$data['inbox_row_color'] = $this->row_color;
		$this->getTemplate()->display('email-fetch-inbox-form', $data);
	}

	public function actionStatus(){
		include_once('model/MEmailFetchInbox.php');
		$ef_inbox_model = new MEmailFetchInbox();

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

		$ef_inbox = $ef_inbox_model->getEmailFetchInboxes($id);
		if(empty($ef_inbox)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This item is not exists!'
			]));
		}
		
		if($ef_inbox_model->updateEmailFetchInboxStatus($id, $status)){
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

	public function actionUpdate() {
		include_once('model/MEmailFetchInbox.php');
		include_once('lib/FormValidator.php');
		include_once('model/MSkill.php');
		$skill_model = new MSkill();
		$ef_inbox_model = new MEmailFetchInbox();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$ef_inbox = '';
		$error_data = '';

		$ef_inbox = $ef_inbox_model->getEmailFetchInboxes($request->getRequest('id'));
		if(empty($ef_inbox)){
			$errType = 1;
			$errMsg ='This item is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			redirect_page($url);
		}
		if ($request->isPost()) {
		    //GPrint($this->getRequest()->getPost());die;
            $post_data = $this->getRequest()->getPost();
            $temp = $post_data['viewable_skills'];
            $viewable_skills = isset($temp) ? rtrim(implode(",",$temp),',') : "";

            unset($post_data['viewable_skills']);
            $post_data['viewable_skills'] = $viewable_skills;
			$response = $ef_inbox_model->updateData($post_data, $ef_inbox[0]);
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$ef_inbox[0] = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}		
		if (UserAuth::hasRole('admin')) {
            $data['skills'] = $skill_model->getSkills('', 'E', 0, 100);
        } else {
            $data['skills'] = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), 'E', 0, 100);
        }

		$data['pageTitle'] = 'Update Inbox';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=email-fetch-inboxes',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['ef_inbox_data'] = $ef_inbox[0];
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$data['fetch_method_list'] = get_email_fetch_method_list();
        $data['yes_no_list'] = array("Y"=>"Yes", "N"=>"No");
        $data['inbox_row_color'] = $this->row_color;
		$this->getTemplate()->display('email-fetch-inbox-form', $data);
	}
}
