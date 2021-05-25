<?php
require_once 'BaseTableDataController.php';

class Branch extends BaseTableDataController {

    private $FILE_NAME = "branch";

    private $valid_field = [
        'branch_name' => [
            'len' => 150,
            'validation' => [
                'required' => true,
                'max-len' => 150
            ],
            'validation_msg' => [
                'required' => "Branch name is required!",
                'max-len' => "Please enter no more than 150 characters!",
            ],
            'special_char_check' => false
        ],
        'branch_code' => [
            'len' => 5,
            'validation' => [
                'required' => true,
                'max-len' => 5
            ],
            'validation_msg' => [
                'required' => "Branch code is required!",
                'max-len' => "Please enter no more than 5 characters!",
            ],
            'special_char_check' => false
        ]
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $this->actionBranch();
    }

    public function actionBranch () {
        $data['pageTitle'] =  'Branch';
        $data['dataUrl'] = $this->url('task='.$this->request->getControllerName().'&act=branch-grid-list');
        $data['topMenuItems'] = array(
            ['href'=>'task='.$this->request->getControllerName().'&act=create', 'img'=>'fa fa-plus', 'label'=>'Create', 'title'=>'Create'],
            ['href'=>'task='.$this->request->getControllerName().'&act=upload', 'img'=>'fa fa-upload', 'label'=>'Upload', 'title'=>'Upload CSV']
        );
        $view = $this->request->getControllerName().'/branch';
        $this->getTemplate()->display("$view", $data);
    }

    /*
     * Branch List view
     * generate
     */
    public function actionBranchGridList() {
        include('lib/DateHelper.php');
        include_once('model/MBranch.php');
        include_once('model/MAgent.php');
        $branch_model = new MBranch();
        $agent_model = new MAgent();

        $search_obj = new stdClass();
        $search_obj->created_at = "";
        $search_obj->branch_code = "";
        $search_obj->branch_name = "";

        if ($this->gridRequest->isMultisearch){
            $dateTimeArray = $this->gridRequest->getMultiParam('created_at');
            $search_obj->branch_code = $this->gridRequest->getMultiParam('branch_code');
            $search_obj->branch_name = $this->gridRequest->getMultiParam('branch_name');
        }
        $search_obj->created_at = DateHelper::get_cc_time_details($dateTimeArray, false);

        $this->pagination->num_records = $branch_model->numBranch($search_obj);
        $result = $this->pagination->num_records > 0 ? $branch_model->getBranch($search_obj, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;
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
                $data->action3 = '<a title="Edit"  href=' . $this->url("task=".$this->request->getControllerName()."&act=update&id={$data->id}") .'><i class="sticky-note-edit glyphicon glyphicon-edit"></i></a>';
                $data->action4 = '<a title="Delete" href="javascript:void(0)" onclick="confirm_status(event)" data-msg="Do you want to delete this note?" data-href=' . $this->url("task=".$this->request->getControllerName()."&act=delete&id={$data->id}") .'><i class="sticky-note-delete glyphicon glyphicon-trash"></i></a>';
                $data->action = $data->action3."  ". $data->action4;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    private function validateFormPostData () {
        $validator = new FormValidator();
        $validate = $validator->valid($this->getRequest()->getPost(), $this->valid_field);

        if(!$validate[MSG_RESULT]){
            return [
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_DATA => $validate[MSG_DATA],
                MSG_ERROR_DATA => $validate[MSG_ERROR_DATA],
                MSG_MSG => 'You have some form errors. Please check below!'
            ];
        }

        return [
            MSG_RESULT => true,
            MSG_DATA => $validate[MSG_DATA],
            MSG_MSG => 'Successfully Updated!'
        ];
    }

    /*
     * create branch
     */
    public function actionCreate() {
        include_once('lib/FormValidator.php');
        include_once('model/MBranch.php');
        $mainobj = new MBranch();
        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $branch_data = "";
        $error_data = '';

        if ($request->isPost()) {
            $response = $this->validateFormPostData();

            if($response[MSG_RESULT]) {
                $isSave = $mainobj->saveBranch($response[MSG_DATA]);
                if ($isSave) {
                    $errType = 0;
                    $errMsg = $response[MSG_MSG];
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    redirect_page($url);
                } else {
                    $errType = 1;
                    $errMsg = "Failed to add!";
                    $note_data = $response[MSG_DATA];
                    $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
                }
            } else {
                $errType = 1;
                $errMsg = $response[MSG_MSG];
                $note_data = $response[MSG_DATA];
                $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
            }
        }

        $data['pageTitle'] = empty($branch_data->id) ? 'Create Branch' : 'Edit Branch';
        $data['topMenuItems'] = array(['href'=>'task=branch', 'label'=>'Cancel', 'title'=>'Cancel']);
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['branch_data'] = $branch_data;
        $data['error_data'] = $error_data;
        $data['buttonName'] = 'Create';
        $view = $request->getControllerName().'/'.$request->getActionName();
        $this->getTemplate()->display($view, $data);
    }

    /*
     * update branch
     */
    public function actionUpdate(){
        include_once('lib/FormValidator.php');
        require_once('model/MBranch.php');
        $branch_model = new MBranch();

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $error_data = '';

        $branch_data = $branch_model->getBranchById($request->getRequest('id'));
        $branch_data = !empty($branch_data) ? array_shift($branch_data) : null;

        if(empty($branch_data)){
            $errType = 1;
            $errMsg ='This branch is not exists!';
            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
            redirect_page($url);
        }
        if ($request->isPost()) {
            $response = $this->validateFormPostData();
            if($response[MSG_RESULT]) {
                $isUpdate = $branch_model->updateBranch($request->getRequest('id'), $response[MSG_DATA], $branch_data);
                if ($isUpdate) {
                    $errType = 0;
                    $errMsg = $response[MSG_MSG];
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    redirect_page($url);
                } else {
                    $errType = 1;
                    $errMsg = "Failed to update!";
                    $branch_data = $response[MSG_DATA];
                    $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
                }
            } else {
                $errType = 1;
                $errMsg = $response[MSG_MSG];
                $branch_data = $response[MSG_DATA];
                $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
            }
        }
        //dd($branch_data);
        $data['pageTitle'] =  empty($branch_data->id) ? 'Create Branch' : 'Edit Branch';
        $data['topMenuItems'] = array(['href'=>'task=branch', 'label'=>'Cancel', 'title'=>'Cancel']);

        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['branch_data'] = $branch_data;
        $data['error_data'] = $error_data;
        $data['status_list'] = get_status_list();
        $data['buttonName'] = 'Create';
        $view = $this->request->getControllerName().'/create';
        $this->getTemplate()->display($view, $data);
    }

    /*
     * Notes List view
     * delete
     */
    public function actionDelete(){
        include_once('model/MBranch.php');
        $mainobj = new MBranch();

        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

        if(empty($id)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'Your request is wrong!'
            ]));
        }

        $sticky_note = $mainobj->getBranchById($id);
        if(empty($sticky_note)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'This sticky note is not exists!'
            ]));
        }

        if($mainobj->deleteBranchById($id)){
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
     * Upload page
     */
    public function actionUpload () {
        include_once('lib/FormValidator.php');
        include_once('model/MBranch.php');

        $errMsg = '';
        $errType = 1;

        $data['pageTitle'] = 'Upload Branch';
        $data['topMenuItems'] = array(['href'=>'task=branch', 'label'=>'Cancel', 'title'=>'Cancel']);
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['heading'] = MBranch::$header;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['file_name'] = $this->FILE_NAME;
        $data['buttonName'] = 'Create';
        $view = $this->getRequest()->getControllerName()."/".$this->getRequest()->getActionName();
        $this->getTemplate()->display($view, $data);
    }

    /*
     * upload file into server
     */
    function actionFileUpload() {
        include_once('lib/FileManager.php');
        include('conf.extras.php');
        $resp = FileManager::check_file_for_upload($this->FILE_NAME, 'csv');

        //GPrint($_REQUEST);die;
        $response = new stdClass();
        $response->error = '';
        $response->status = 400;
        $response->fname = '';
        $response->header = [];
        $response->data = [];
        $fileManager = new FileManager();
        $unique_id = bin2hex(random_bytes(8)).UserAuth::getUserID();

        if ($resp == FILE_EXT_INVALID) {
            $response->error = 'Please select a CSV file';
        } elseif ($resp == FILE_UPLOADED) {
            $response->fname = $this->file_prefix.$unique_id.'_'.$_FILES[$this->FILE_NAME]['name'];
            if (file_exists($response->fname)) {
                $fileManager->unlink_file($response->fname);
            }
            $row = 0;
            if (($handle = fopen($_FILES[$this->FILE_NAME]['tmp_name'], "r")) !== FALSE) {
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
            $upload = $fileManager->save_uploaded_file($this->FILE_NAME, $file_name);
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
    public function actionUploadData () {
        include_once('lib/FileManager.php');
        include_once('model/MBranch.php');
        include('conf.extras.php');
        $response = new stdClass();
        $response->file_name = !empty($_REQUEST['fname']) ? $_REQUEST['fname'] : "";
        $response->head_option = !empty($_REQUEST['head_option']) ? $_REQUEST['head_option'] : "";
        $response->valid_head = !empty($_REQUEST['valid_head']) ? $_REQUEST['valid_head'] : "";
        $response->skill_id = !empty($_REQUEST['skill_id']) ? $_REQUEST['skill_id'] : "";
        $response->is_delete = !empty($_REQUEST['is_delete']) ? true : false;
        $response->success_count = !empty($_REQUEST['success_count']) ? $_REQUEST['success_count'] : 0;
        $response->err_msg = "";
        $response->is_success = false;
        $current_user = UserAuth::getCurrentUser();

        $obj = [];
        $file = $extra->file_upload_url.$response->file_name;
        if (!empty($response->head_option)) {
            foreach ($response->head_option as $key => $value){
                if ($value != "") {
                    $obj[$key] = $value;
                }
            }
        }

        $csv_data_ary = [];
        if (file_exists($file) && !empty($obj)) {

            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;

                $prepared_csv = $extra->file_upload_url.$this->FILE_NAME."_sql_load".bin2hex(random_bytes(10)).UserAuth::getUserID().".csv";
                $fp = fopen($prepared_csv ,'w');

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    $data = array_filter($data);
                    if ($row > 1 && !empty($data)) {
                        $row_data_ary = [];
                        foreach ($data as $key => $value) {
                            $len = strlen($value);
                            if (!empty($this->valid_field[$obj[$key]]) && $len >= $this->valid_field[$obj[$key]]['validation']['min-len'] && $len <= $this->valid_field[$obj[$key]]['validation']['max-len']) {
                                //$row_data_ary[] = $value;
                                $row_data_ary[] = strval($value);
                            }
                        }
                        if (!empty($row_data_ary)) {
                            array_unshift($row_data_ary, substr(bin2hex(random_bytes(7)), 0, 12));
                            $row_data_ary[] = $current_user;
                            //fputcsv($fp, $row_data_ary, ';',"");
                            fwrite($fp, implode(',', $row_data_ary) . PHP_EOL);
                        }
                        $response->success_count++;
                    }
                }
                fclose($handle);
                fclose($fp);

                if (!empty($response->success_count) && $response->success_count > 0) {
                    array_unshift($obj, 'id');
                    $obj[] = 'created_by';

                    $mainobj = new MBranch();
                    $mainobj->csv_load_file = $prepared_csv;
                    $mainobj->csv_load_column = implode(",", $obj);
                    $response->is_success = $mainobj->csv_load();
                }

                unset($csv_data_ary);
                $fileManager = new FileManager();
                $fileManager->unlink_file($prepared_csv); /// newly prepared csv
                $fileManager->unlink_file($file); //// original csv
            }
        } else {
            if ($response->success_count) {
                $response->err_msg = $response->success_count." data have been uploaded.";
            } else {
                $response->err_msg = empty($obj) ? 'No data selectd!!' : 'File not found!!';
            }
        }
        echo json_encode($response);
        exit();
    }

}