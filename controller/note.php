<?php
require_once 'BaseTableDataController.php';

class Note extends BaseTableDataController {

    private $file_prefix = "cc_notes_upload_";

    public function __construct()
    {
        parent::__construct();
    }

    function init()
    {
        return $this->actionNote();
    }

    /*
     * Notes List
     */
    function actionNote()
    {
        $data['pageTitle'] =  'Notes';
        $data['dataUrl'] = $this->url('task=note&act=note-grid-list');
        $data['topMenuItems'] = array(
            array('href'=>'task=note&act=create', 'img'=>'fa fa-plus', 'label'=>'Create', 'title'=>'Create Note'),
            array('href'=>'task=note&act=upload', 'img'=>'fa fa-upload', 'label'=>'Upload', 'title'=>'Upload CSV')
        );
        $this->getTemplate()->display('note/note', $data);
    }

    /*
     * Create Notes
     */
    function actionCreate() {
        include_once('lib/FormValidator.php');
        include_once('model/MNote.php');
        $mainobj = new MNotes();
        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $note_data = "";
        $error_data = '';

        if ($request->isPost()) {
            $response = $mainobj->saveData($this->getRequest()->getPost());

            if($response[MSG_RESULT]) {
                $errType = 0;
                $errMsg = $response[MSG_MSG];
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                redirect_page($url);
            } else {
                $errType = 1;
                $errMsg = $response[MSG_MSG];
                $note_data = $response[MSG_DATA];
                $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
            }
        }

        $data['pageTitle'] = empty($note_data->id) ? 'Create Sticky Note' : 'Edit Sticky Note';
        $data['topMenuItems'] = array(
                                        array(
                                            'href'=>'task=note&act=note',
                                            'label'=>'Cancel',
                                            'title'=>'Cancel'
                                        )
                                );
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['note_data'] = $note_data;
        $data['error_data'] = $error_data;
        $data['status_list'] = get_status_list();
        $data['buttonName'] = 'Create';
        $this->getTemplate()->display('note/note_create', $data);
    }

    /*
     * Delete Old CSV files
     */
    private function actionDeleteFiles () {
        include('conf.extras.php');
        $day_before = 24*60*60;
        $now = time();

        if (is_dir($extra->file_upload_url)) {
            $iterator = new DirectoryIterator($extra->file_upload_url);
            if (!empty($iterator)){
                foreach ($iterator as $fileinfo) {
                    //$file_name = (string)$fileinfo->getFilename();
                    if ($fileinfo->isFile() && strpos($fileinfo->getFilename(),$this->file_prefix) != FALSE && $fileinfo->getExtension() == "csv" && $now - $fileinfo->getCTime() >= $day_before) {
                        unlink($fileinfo->getRealPath());
                    }
                }
            }
        }
        return null;
    }

    /*
     * Upload bulk cli
     * using CSV
     */
    public function actionUpload () {
        include_once('lib/FormValidator.php');
        include_once('model/MNote.php');

        unset($_SESSION['notes_seek']);
        $_SESSION['notes_seek'] = [];

        $mainobj = new MNotes();
        $mainobj->hide_column = ['id', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];
        $errMsg = '';
        $errType = 1;
        $note_data = "";
        $error_data = '';
        $this->actionDeleteFiles();

        $data['pageTitle'] = 'Upload  Notes';
        $data['topMenuItems'] = array(
                                    array(
                                        'href'=>'task=note&act=note',
                                        'label'=>'Cancel',
                                        'title'=>'Cancel'
                                    )
                                );
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['heading'] = $mainobj->getColumnName();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['note_data'] = $note_data;
        $data['error_data'] = $error_data;
        $data['buttonName'] = 'Create';
        $this->getTemplate()->display('note/note_upload', $data);
    }

    function actionFileUpload() {
        include_once('lib/FileManager.php');
        include('conf.extras.php');
        $resp = FileManager::check_file_for_upload("note_file", 'csv');

        //GPrint($_REQUEST);die;
        $response = new stdClass();
        $response->error = '';
        $response->status = 400;
        $response->fname = '';
        $response->header = [];
        $response->data = [];
        $fileManager = new FileManager();
        $unique_id = str_replace('.', '', microtime(true));

        if ($resp == FILE_EXT_INVALID) {
            $response->error = 'Please select a CSV file';
        } elseif ($resp == FILE_UPLOADED) {
            $response->fname = $this->file_prefix.$unique_id.'_'.$_FILES["note_file"]['name'];
            if (file_exists($response->fname)) {
                $fileManager->unlink_file($response->fname);
            }
            $row = 0;
            if (($handle = fopen($_FILES["note_file"]['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;

                    //for header row
                    if ($row == 1) {
                        $response->header = $data;
                    } else {
                        // data row
                        $response->data [] = $data;
                    }
                    if ($row == 3) {
                        break;
                    }        
                }
                fclose($handle);
            }
          
            $file_name = $extra->file_upload_url.$response->fname;
            $upload = $fileManager->save_uploaded_file("note_file", $file_name);

            if ($upload) {
                $response->status = 200;
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
     * Number Upload
     * form uploaded csv
     * using ajax
     */
    public function actionUploadNumber () {
        include_once('model/MNote.php');
        include('conf.extras.php');
        $response = new stdClass();
        $response->file_name = !empty($_REQUEST['fname']) ? $_REQUEST['fname'] : "";
        $response->head_option = !empty($_REQUEST['head_option']) ? $_REQUEST['head_option'] : "";
        $response->valid_head = !empty($_REQUEST['valid_head']) ? $_REQUEST['valid_head'] : "";
        $response->err_msg = "";
        $response->count = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 0;
        $response->success_count = !empty($_REQUEST['success_count']) ? $_REQUEST['success_count'] : 0;
        $response->is_success = false;

        $obj = [];
        $file = $extra->file_upload_url.$response->file_name;
        if (!empty($response->head_option)) {
            foreach ($response->head_option as $key => $value){
                if ($value != "") {
                    $obj[$key] = $value;
                }
            }
        }


        if (file_exists($file) && !empty($obj)) {
            $mainobj = new MNotes();
            //$mainobj->max_row_read = 4;    
            if (!in_array($response->count, $_SESSION['notes_seek'])) {
                $_SESSION['notes_seek'][] = $response->count;
                $mainobj->linePointer = $response->count;     
                $result = $mainobj->saveCSV($file, $obj);
                if ($result[MSG_RESULT]) {
                    $response->valid_head = $obj;
                    $response->count += $result['lineNumber'];
                    $response->is_success = true;
                    $response->success_count += $result['successCount'];
                }
            }
        } else {

            $response->err_msg = empty($obj) ? 'No data selectd!!' : 'File not found!!';
        }
        echo json_encode($response);
        exit();
    }

    /*
     * Update Notes
     */
    public function actionUpdate(){
        include_once('lib/FormValidator.php');
        require_once('model/MNote.php');

        $note_model = new MNotes();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $note_data = '';
        $error_data = '';

        $note_data = $note_model->getNoteById($request->getRequest('id'));
        if(empty($note_data)){
            $errType = 1;
            $errMsg ='This note is not exists!';
            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
            redirect_page($url);
        }
        if ($request->isPost()) {
            $response = $note_model->saveData($this->getRequest()->getPost());

            if($response[MSG_RESULT]){
                $errType = 0;
                $errMsg = $response[MSG_MSG];
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                redirect_page($url);
            }else{
                $errType = 1;
                $errMsg = $response[MSG_MSG];
                $note_data = $response[MSG_DATA];
                $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
            }
        }

        $data['pageTitle'] =  empty($note_data->id) ? 'Create Sticky Note' : 'Edit Sticky Note';
        $data['topMenuItems'] = array(
            array(
                'href'=>'task=note&act=note',
                'label'=>'Cancel',
                'title'=>'Cancel'
            )
        );

        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['note_data'] = $note_data;
        $data['error_data'] = $error_data;
        $data['status_list'] = get_status_list();
        $data['buttonName'] = 'Create';
        $this->getTemplate()->display('note/note_create', $data);
    }

    /*
     * Notes List view
     * generate
     */
    public function actionNoteGridList(){
        include('lib/DateHelper.php');
        include_once('model/MNote.php');
        include_once('model/MAgent.php');
        $note_model = new MNotes();
        $agent_model = new MAgent();

        $search_obj = new stdClass();
        $search_obj->created_at = "";
        $search_obj->cli = "";

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('created_at');
            $search_obj->cli = $this->gridRequest->getMultiParam('cli');
        }
        $search_obj->created_at = DateHelper::get_cc_time_details($dateTimeArray, false);

        $this->pagination->num_records = $note_model->numNotes($search_obj);
        $result = $this->pagination->num_records > 0 ? $note_model->getNotes($search_obj, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result)>0){
            $agent_names = $agent_model->getAgentNames();
            if (!empty($agent_names)){
                $agent_names = array_column($agent_names, 'name', 'agent_id');
            }
            foreach ( $result as &$data ) {
                $data->created_by = !empty($agent_names[$data->created_by]) ? $data->created_by." - ".$agent_names[$data->created_by] : $data->created_by;
                $data->updated_by = !empty($agent_names[$data->updated_by]) ? $data->updated_by." - ".$agent_names[$data->updated_by] : $data->updated_by;
                $data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this note: " . $data->cli . "?' data-href='" . $this->url("task=note&act=status&id=" . $data->id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
                $data->action3 = '<a title="Edit"  href=' . $this->url("task=note&act=update&id={$data->id}") .'><i class="sticky-note-edit glyphicon glyphicon-edit"></i></a>';
                $data->action4 = '<a title="Delete" href="javascript:void(0)" onclick="confirm_status(event)" data-msg="Do you want to delete this note?" data-href=' . $this->url("task=note&act=delete&id={$data->id}") .'><i class="sticky-note-delete glyphicon glyphicon-trash"></i></a>';
                $data->action = $data->action1."  ". $data->action3."  ". $data->action4;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    /*
     * Notes list view
     * status chnage
     */
    public function actionStatus(){
        include_once('model/MNote.php');
        $note_model = new MNotes();

        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

        if(empty($id) || empty($status) || !in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE])){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'Your request is wrong!'
            ]));
        }

        $sticky_note = $note_model->getNoteById($id);
        if(empty($sticky_note)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'This sticky note is not exists!'
            ]));
        }

        if($note_model->updateNoteStatus($id, $status)){
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

    /*
     * Notes List view
     * delete
     */
    public function actionDelete(){
        include_once('model/MNote.php');
        $note_model = new MNotes();

        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

        if(empty($id)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'Your request is wrong!'
            ]));
        }

        $sticky_note = $note_model->getNoteById($id);
        if(empty($sticky_note)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'This sticky note is not exists!'
            ]));
        }

        if($note_model->deleteNote($id)){
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
