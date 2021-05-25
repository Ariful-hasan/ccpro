<?php 
require_once 'BaseTableDataController.php';

class StickyNote extends BaseTableDataController 
{
	function __construct() 
	{		
		parent::__construct();
	}

	function init()
	{
		return $this->actionStickyNote();
	}

	function actionStickyNote()
	{
		$data['pageTitle'] =  'Sticky Notes';
		$data['dataUrl'] = $this->url('task=sticky-note&act=sticky-grid-list');
		$data['topMenuItems'] = array(
			array('href'=>'task=sticky-note&act=create-sticky-note', 'img'=>'fa fa-plus', 'label'=>'Create', 'title'=>'Create Sticky Note')
		);
		$this->getTemplate()->display('sticky-note/sticky_note', $data);
	}

	function actionCreateStickyNote()
	{
		include_once('model/MAgent.php');
		include_once('lib/FormValidator.php');
		require_once('model/MStickyNote.php');
		$agent_model = new MAgent();
		$sticky_note_model = new MStickyNote();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$sticky_data = "";
		$error_data = '';

		if ($request->isPost()) {
			$response = $sticky_note_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$sticky_data = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		$agents =  $agent_model->getAllAgentsAndSupervisor(['*']);	
		$agent_list = [];
		foreach($agents as $struct) {
			$agent_list[$struct->agent_id] = $struct->name;
		}
		$data['agent_list'] =  $agent_list;
		$data['pageTitle'] =  empty($sticky_data->id) ? 'Create Sticky Note' : 'Edit Sticky Note';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=sticky-note&act=sticky-note',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);

		$data['main_menu'] = 'page';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['sticky_note'] = $sticky_data;
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$data['buttonName'] = 'Create';

		$this->getTemplate()->display('sticky-note/sticky_note_create', $data);		
	}

	public function actionEdit(){		
		include_once('model/MAgent.php');
		include_once('lib/FormValidator.php');
		require_once('model/MStickyNote.php');
		$agent_model = new MAgent();
		$sticky_note_model = new MStickyNote();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$sticky_data = '';
		$error_data = '';

		$sticky_data = $sticky_note_model->getStickyNoteById($request->getRequest('id'));		
		if(empty($sticky_data)){
			$errType = 1;
			$errMsg ='This sticky note is not exists!';
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
			redirect_page($url);
		}
		if ($request->isPost()) {					
			$response = $sticky_note_model->saveData($this->getRequest()->getPost());
			
			if($response[MSG_RESULT]){
				$errType = 0;
				$errMsg = $response[MSG_MSG];
				$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
				redirect_page($url);
			}else{
				$errType = 1;
				$errMsg = $response[MSG_MSG];
				$sticky_data = $response[MSG_DATA];
				$error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
			}
		}
		$agents =  $agent_model->getAllAgentsAndSupervisor(['*']);	
		$agent_list = [];
		foreach($agents as $struct) {
			$agent_list[$struct->agent_id] = $struct->name;
		}
		$data['agent_list'] =  $agent_list;
		$data['pageTitle'] =  empty($sticky_data->id) ? 'Create Sticky Note' : 'Edit Sticky Note';
		$data['topMenuItems'] = array(
									array(
										'href'=>'task=sticky-note&act=sticky-note',
										'label'=>'Cancel', 
										'title'=>'Cancel'
									)
								);

		$data['main_menu'] = 'page';
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['sticky_note'] = $sticky_data;
		$data['error_data'] = $error_data;
		$data['status_list'] = get_status_list();
		$data['buttonName'] = 'Create';

		$this->getTemplate()->display('sticky-note/sticky_note_create', $data);
	}

	function actionViewAgents()
	{		
		require_once('model/MStickyNote.php');
		require_once('model/MAgent.php');

		$sticky_note_model = new MStickyNote();
		$agent_model = new MAgent();

		$id=$this->getRequest()->getRequest('id');
		if (!empty($id)) {
			$sticky_note_agents = $sticky_note_model->getAssignAgents($id);
		    $data['sticky_note_agents'] = $sticky_note_agents;

		} else {
			$data['errMsg'] = "Invalid ID";
			$data['errType'] = 1;
		}
		
		$data['pageTitle'] = 'Notify Persons';
		$this->getTemplate()->display_popup('sticky-note/sticky-note-view-agents', $data);
	}

	public function actionStickyGridList(){
		include_once('model/MStickyNote.php');
        $sticky_note_model = new MStickyNote();

        $id = '';
        $title = '';

        if ($this->gridRequest->srcItem=="id") {
            $id = $this->gridRequest->srcText;
        } elseif ($this->gridRequest->srcItem=="title") {
            $title = $this->gridRequest->srcText;
        } 

        $this->pagination->num_records = $sticky_note_model->numStickyNotes($id, $title);
        $result = $this->pagination->num_records > 0 ? $sticky_note_model->getStickyNotes($id, $title, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        // $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result)>0){
            foreach ( $result as &$data ) {
                $data->id = $data->id;
                $data->title = $data->title;
                $data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this sticky note: " . $data->title . "?' data-href='" . $this->url("task=sticky-note&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";

                $data->action1 = '<a title="Agents" class="lightboxWIFR" href=' . $this->url("task=sticky-note&act=view-agents&id={$data->id}") .'><i class="sticky-note-agents glyphicon glyphicon-user"></i></a>';
                $data->action3 = '<a title="Edit"  href=' . $this->url("task=sticky-note&act=edit&id={$data->id}") .'><i class="sticky-note-edit glyphicon glyphicon-edit"></i></a>';
                // $data->action4 = '<a title="Delete" href="javascript:void(0)" onclick="confirm_bootbox(this)" data-msg="Do you want to delete this sticky note?" data-href=' . $this->url("task=confirm-response&act=sticky-note&pro=delete&id={$data->id}") .'><i class="sticky-note-delete glyphicon glyphicon-trash"></i></a>';
                $data->action4 = '<a title="Delete" href="javascript:void(0)" onclick="confirm_status(event)" data-msg="Do you want to delete this sticky note?" data-href=' . $this->url("task=sticky-note&act=delete&id={$data->id}") .'><i class="sticky-note-delete glyphicon glyphicon-trash"></i></a>';

                $data->action = $data->action1."  ". $data->action3."  ". $data->action4;
            }
        }
            
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionStatus(){
		include_once('model/MStickyNote.php');
		$sticky_note_model = new MStickyNote();

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

		$sticky_note = $sticky_note_model->getStickyNoteById($id);
		if(empty($sticky_note)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This sticky note is not exists!'
			]));
		}
		
		if($sticky_note_model->updateStickyNoteStatus($id, $status)){
			die(json_encode([
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_MSG => 'Status has been updated successfully!'
			]));
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Status has not been updated successfully!'
			]));
		}
	}

	public function actionDelete(){
		include_once('model/MStickyNote.php');
		$sticky_note_model = new MStickyNote();

		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

		if(empty($id)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}

		$sticky_note = $sticky_note_model->getStickyNoteById($id);
		if(empty($sticky_note)){
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This sticky note is not exists!'
			]));
		}
		
		if($sticky_note_model->deleteStickyNote($id)){
			die(json_encode([
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_MSG => 'This item has been deleted successfully!'
			]));
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'This item has not been deleted successfully!'
			]));
		}
	}
}