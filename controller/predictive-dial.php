<?php

class PredictiveDial extends Controller
{
    function __construct() {
        parent::__construct();
    }
    private $file_prefix = "pd";


    private $valid_field = [
        'number_1' => [
            'len' => 11,
            'validation' => [
                'required' => true,
                'max-len' => 13,
                'min-len' => 6
            ],
            'validation_msg' => [
                'required' => "Mobile number is required!",
                'max-len' => "Please enter no more than 11 digits!",
                'min-len' => "Please enter more than 5 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => true,
        ],
        'title' => [
            'len' => 6,
            'validation' => [
                'required' => false,
                'max-len' => 6,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 6 digits!",
                'min-len' => "Please enter more than 0 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'first_name' => [
            'len' => 15,
            'validation' => [
                'required' => false,
                'max-len' => 15,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 15 digits!",
                'min-len' => "Please enter more than 0 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'last_name' => [
            'len' => 15,
            'validation' => [
                'required' => false,
                'max-len' => 15,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 15 digits!",
                'min-len' => "Please enter more than 0 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'zip' => [
            'len' => 10,
            'validation' => [
                'required' => false,
                'max-len' => 10,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 10 digits!",
                'min-len' => "Please enter more than 0 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'customer_id' => [
            'len' => 16,
            'validation' => [
                'required' => false,
                'max-len' => 16,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 16 digits!",
                'min-len' => "Please enter more than 0 digits!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'custom_value_1' => [
            'len' => 20,
            'validation' => [
                'required' => false,
                'max-len' => 20,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 20 char!",
                'min-len' => "Please enter more than 0 char!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'custom_value_2' => [
            'len' => 20,
            'validation' => [
                'required' => false,
                'max-len' => 20,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 20 char!",
                'min-len' => "Please enter more than 0 char!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'custom_value_3' => [
            'len' => 20,
            'validation' => [
                'required' => false,
                'max-len' => 20,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 20 char!",
                'min-len' => "Please enter more than 0 char!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
        'custom_value_4' => [
            'len' => 20,
            'validation' => [
                'required' => false,
                'max-len' => 20,
                'min-len' => 0
            ],
            'validation_msg' => [
                'max-len' => "Please enter no more than 20 char!",
                'min-len' => "Please enter more than 0 char!",
            ],
            'special_char_check' => false,
            'is_mobile_number' => false,
        ],
    ];

    function init()
    {
        $this->actionNumbers();
    }

    function actionDelnumber()
    {
        include('model/MPredictiveDial.php');
        $predictive_dial_model = new MPredictiveDial();

        $numid = isset($_REQUEST['numid']) ? trim($_REQUEST['numid']) : 0;
        $num = isset($_REQUEST['num']) ? trim($_REQUEST['num']) : 0;
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : "";
        $cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

        if (strlen($num) > 0 && strlen($numid) > 0 && ctype_digit($numid) && ctype_digit($num) && strlen($sid)==2 && ctype_alpha($sid))
        {

            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=numbers&sid=$sid&page=".$cur_page);
            if ($predictive_dial_model->deleteNumber($numid, $num,$sid))
            {
                $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Number', 'isError'=>false, 'msg'=>'Number Deleted Successfully', 'redirectUri'=>$url));
            }
            else
            {
                $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Number', 'isError'=>true, 'msg'=>'Failed to Delete Number', 'redirectUri'=>$url));
            }
        }
    }
    
    function actionStartPd()
    {
        include('model/MPredictiveDial.php');
        $predictive_dial_model = new MPredictiveDial();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        if (strlen($sid) == 2)
        {

            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=manage&sid=".$sid);
            if ($predictive_dial_model->doNotifyPD($sid, 'start'))
            {
                $this->getTemplate()->display('msg', array('pageTitle'=>'Start PD', 'isError'=>false, 'msg'=>'Dialer Started Successfully.', 'redirectUri'=>$url));
            }
        }
    }
    
    function actionRestartLead()
    {
        include('model/MPredictiveDial.php');
        $predictive_dial_model = new MPredictiveDial();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        if (strlen($sid) == 2)
        {

            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=manage&sid=".$sid);
            if ($predictive_dial_model->RestartLead($sid))
            {
                $this->getTemplate()->display('msg', array('pageTitle'=>'Restart Lead', 'isError'=>false, 'msg'=>'Lead Re-started Successfully.', 'redirectUri'=>$url));
            }
        }
    }
    
    function actionStopPd()
    {
        include('model/MPredictiveDial.php');
        $predictive_dial_model = new MPredictiveDial();

        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        if (strlen($sid) == 2)
        {

            $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=manage&sid=".$sid);
            if ($predictive_dial_model->doNotifyPD($sid, 'stop'))
            {
                $this->getTemplate()->display('msg', array('pageTitle'=>'Stop PD', 'isError'=>false, 'msg'=>'Dialer Stopped Successfully.', 'redirectUri'=>$url));
            }
        }
    }

    function actionManage()
    {
        $skill_id = $this->getRequest()->getRequest('sid');

        include('model/MSkill.php');
        include('model/MPredictiveDial.php');

        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');

        if (strlen($skill_id) !== 2 || !array_key_exists($skill_id,$skills) || !ctype_upper($skill_id))
        {
            exit();
        }

        $predictive_dial = new MPredictiveDial();
        $profile = $predictive_dial->get_pd_profile($skill_id);

        $total_numbers = $predictive_dial->numDialNumbers($skill_id,"",TRUE);

        $data['total_records'] = $total_numbers;
        $data['dial_status_options'] = MPredictiveDial::get_dial_status_options();
        $data['skills'] = $skills;
        $data['vm_action'] = array('LM'=>'Leave Message','RT'=>'Retry');
        $data['drop_call_action'] = array('HU'=>'Hungup','SW'=>'Silent Wait','MH'=>'Music','PF'=>'PlayMessage','AN'=>'Play Message-DTMF');
        $data['status'] = array('A'=>'Active', 'N'=>'Inactive', 'T'=>'Timeout','C'=>'Completed','E'=>'Error');
        $data['dial_engines'] = array('AA'=>'Acknowledgement','AN'=>'Announcement','PD'=>'Predictive','PG'=>'Progressive','RS'=>'Responsive','SR'=>'Survey');
        $data['profile'] = $profile;
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Manage Predictive Dial :: '.$skills[$skill_id];
        $data['side_menu_index'] = 'predictive-dial';
        $data['smi_selection'] = 'skills_';
        $this->getTemplate()->display('predictive_dial_manage', $data);
    }

    function actionNumbers()
    {
/*    
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
*/        
        include('model/MPredictiveDial.php');
        include('model/MSkill.php');
        include('lib/Pagination.php');
        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');

        $skill_id = $this->getRequest()->getRequest('sid');
        $skill_name = !empty($skills[$skill_id]) ? $skills[$skill_id] : "";

        $number_1 = trim($this->getRequest()->getPost('number_1'));

        if (strlen($skill_id) != 2 || !array_key_exists($skill_id,$skills)
            || !ctype_upper($skill_id)) {
            exit();
        }

        $predictive_dial_model = new MPredictiveDial();

        $pagination = new Pagination();
        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=numbers&sid={$skill_id}&sname={$skill_name}");



        $pagination->num_records = $predictive_dial_model->numDialNumbers($skill_id,$number_1);
        $data['numbers'] = $pagination->num_records > 0 ?
            $predictive_dial_model->getDialNumbers($skill_id,$number_1,$pagination->getOffset(), $pagination->rows_per_page) : null;
        $pagination->num_current_records = is_array($data['numbers']) ? count($data['numbers']) : 0;
        $data['pagination'] = $pagination;

        $data['request'] = $this->getRequest();
        $data['skills'] = $skills;
        $data['dial_status_options'] = MPredictiveDial::get_dial_status_options();
        $data['pageTitle'] = 'Predictive Dial Number(s) :: '.$skill_name;
        $data['side_menu_index'] = 'predictive-dial';
        $data['smi_selection'] = 'skills_';
        $this->getTemplate()->display('predictive_dial_numbers', $data);
    }


    function actionDownload()
    {
        include('model/MPredictiveDial.php');
        include('model/MSkill.php');
        require_once('lib/DownloadHelper.php');

        $request = $this->getRequest();
        $skill_id = $request->getRequest('sid');
        if (strlen($skill_id) !=2) exit();
        $pd_model = new MPredictiveDial();

        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray();
        $dial_status = array("S"=>"Served","F"=>"Failed","A"=>"Active","P"=>"Progress","E"=>"End");

        $pageTitle = 'Predictive Dial:: Numbers';

        $columns = array('custom_value_1','custom_value_2','custom_value_3','custom_value_4');
        /*        if (!empty($leadinfo->custom_label_1)) $columns['custom_value_1'] = $leadinfo->custom_label_1;
                if (!empty($leadinfo->custom_label_2)) $columns['custom_value_2'] = $leadinfo->custom_label_2;
                if (!empty($leadinfo->custom_label_3)) $columns['custom_value_3'] = $leadinfo->custom_label_3;
                if (!empty($leadinfo->custom_label_4)) $columns['custom_value_4'] = $leadinfo->custom_label_4;*/

        $dl_helper = new DownloadHelper($pageTitle, $this->getTemplate());

        $dl_helper->set_title($pageTitle . '_' . $skill_id . '_' . date("ymdHi"));

        $dl_helper->create_file('predictive_dial_numbers_'.$skill_id.'_'.date("ymdHi").'.csv');
        $dl_helper->write_in_file("Customer ID,Agent ID, Skill, Number 1,Number 2,Number 3,Title,FName,LName,Street,City,State,Zip,Try Count, Status,Disposition,Agent Alt.ID");

        foreach ($columns as $ckey => $cval) {
            $dl_helper->write_in_file(",{$cval}");
        }

        $dl_helper->write_in_file("\n");



        $numbers = $pd_model->downloadDialNumbers($skill_id);

        if (is_array($numbers)) {

            foreach ($numbers as $num) {
                $skill_name = !empty($skills[$num->skill_id]) ? $skills[$num->skill_id] : $num->skill_id;
                $street = str_replace(',','',$num->street);
                $city = str_replace(',','',$num->city);
                $state = str_replace(',','',$num->state);
                $zip = str_replace(',','',$num->zip);
                $status = !empty($dial_status[$num->dial_status]) ? $dial_status[$num->dial_status] : $num->dial_status;

                $dl_helper->write_in_file("$num->customer_id,$num->agent_id,$skill_name,$num->number_1,$num->number_2,$num->number_3,$num->title,$num->first_name,$num->last_name,$street,$city,$state,$zip,$num->retry_count,$status,$num->disposition,$num->agent_altid");
                foreach ($columns as $ckey => $cval) {
                    $data = str_replace(",",'',$num->{$ckey});
                    $dl_helper->write_in_file(",".$data);
                }
                $dl_helper->write_in_file("\n");
            }
        }

        $dl_helper->download_file();
        exit;
    }

    function actionAdd()
    {
        //$this->savePredictiveDialNumber();
        //$this->actionAddNew();
        $this->actionAddNew2();
    }

    /*
     * Delete Old CSV files
     */
    private function actionDeleteFiles () {
        include('conf.extras.php');
        $day_before = 1*24*60*60;
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
     * load csv
     * using mysql
     */
    public function actionAddNew2 (){
        include('conf.extras.php');
        include('model/MSkill.php');
        include('model/MPredictiveDial.php');
        include('model/MPDSettings.php');
        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');
        $pd_model = new MPredictiveDial();
        /*$pd_model->hide_column = [
            'id', 'title', 'first_name', 'last_name', 'street', 'agent_id', 'skill_id', 'number_2', 'number_3',
            'agent_altid', 'dial_status', 'retry_count', 'is_vm_left', 'disposition',
            'city', 'state', 'zip', 'language'
        ];*/

        $request = $this->getRequest();
        $skill_id = $request->getRequest ( 'sid' );
        $skill_name = !empty($skills[$skill_id]) ? $skills[$skill_id] : "";
        $skill_name = !empty($skill_name) ? "to {$skill_name}" : "";
        $errMsg = '';
        $errType = 1;

        $this->actionDeleteFiles();
        $leads_column = $pd_model->getLeadsColumn();
        //$data['heading'] = !empty($leads_column) ? array_reverse($leads_column) : [];
        $pd_upload_settings_data = MPDSettings::getDataBySkillId($skill_id);
        $pd_upload_settings_data = !empty($pd_upload_settings_data) ? array_column($pd_upload_settings_data, 'header', 'item') : "";

        $data['pd_settings'] = $pd_upload_settings_data;
        $data['heading'] = PD_FIELD_LABELS;
        $data['skill_id'] = $skill_id;
        $data['side_menu_index'] = 'predictive';
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['pageTitle'] = "Add Predictive Dial Numbers ".$skill_name;
        $this->getTemplate()->display('pd/predictive_dial_form_new_2', $data);
    }

    private function setCSVData($file, $validData, $skill_id, $skill_index=0) {
        $row = 0;
        $response = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row > 1 && !empty($data)) {
                    $ary = [];
                    $isValidData = true;
                    foreach ($data as $key => $value) {
                        if (!empty($validData[$key])) {
                            if ($validData[$key] == "number_1") {
                                $value = preg_replace("/[^0-9]/","", $value);
                                if ($this->isValidNumber($value)) {
                                    $ary[] = trim($value);
                                } else {
                                    $isValidData = false;
                                    break;
                                }
                            } else {
                                $ary[] = trim($value);
                            }
                        }
                    }
                    if ($isValidData){
                        $ary[$skill_index] = $skill_id;
                        list($timestamp , $microseconds) = explode('.', microtime(true));
                        $ary['id'] = $timestamp.$microseconds;
                        $response[] = $ary;
                    }
                }
            }
            fclose($handle);
        }
        return $response;
    }

    function actionAddNew() {
        include('conf.extras.php');
        include('model/MSkill.php');
        include('model/MPredictiveDial.php');
        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');
        $pd_model = new MPredictiveDial();

        $request = $this->getRequest();
        $skill_id = $request->getRequest ( 'sid' );
        $skill_name = !empty($skills[$skill_id]) ? $skills[$skill_id] : "";
        $skill_name = !empty($skill_name) ? "to {$skill_name}" : "";
        $errMsg = '';
        $errType = 1;

        if ($request->isPost()){
            $file = $extra->pd_upload_url.$request->_posts['fname'];
            $is_delete = !empty($request->_posts['is_delete']) ? $request->_posts['is_delete'] : "";
            $head_option = $request->_posts['head_option'];
            $obj = [];

            if (!empty($head_option)) {
                foreach ($head_option as $key => $value){
                    if ($value != "") {
                        $obj[$key] = $value;
                    }
                }
            }

            $skill_index = count($head_option)+1;
            $obj[$skill_index] = 'skill_id';
            $obj['id'] = 'id';
            if (file_exists($file)) {
                $valid_data = $this->setCSVData($file, $obj, $skill_id, $skill_index);
                if ($pd_model->saveLeadData($valid_data, $obj, $is_delete, $skill_id)) {
                    $errType = 0;
                    $errMsg = 'Successfully add number.';
                    $url = $this->url("task=$request->_controller_name&act=manage&sid=$skill_id&sname=$skills[$skill_id]");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else {
                    $errType = 1;
                    $errMsg = 'Failed to save number.';
                }
            } else {
                $errMsg = 'No file found!!';
            }
        }

        $data['heading'] = $pd_model->getLeadsColumn();
        $data['side_menu_index'] = 'predictive';
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['pageTitle'] = "Add Predictive Dial Numbers ".$skill_name;
        $this->getTemplate()->display('pd/predictive_dial_form_new', $data);
    }

    /*
     * upload csv
     */
    function actionFileUpload() {
        include_once('lib/FileManager.php');
        include('conf.extras.php');
        $resp = FileManager::check_file_for_upload('pd_file', 'csv');

        //GPrint($_REQUEST);die;
        $response = new stdClass();
        $response->error = '';
        $response->status = 400;
        $response->fname = '';
        $response->header = [];
        $response->data = [];
        $fileManager = new FileManager();
        $skill_id = !empty($_REQUEST['skill_id']) ? $_REQUEST['skill_id'] : "";

        if ($resp == FILE_EXT_INVALID) {
            $response->error = 'Please select a CSV file';
        } elseif ($resp == FILE_UPLOADED && !empty($skill_id)) {

            $response->fname = "pd_".$skill_id.'_'.str_replace('.','',microtime(true)).'_'.$_FILES['pd_file']['name'];
            if (file_exists($response->fname)) {
                $fileManager->unlink_file($response->fname);
            }
            $row = 0;
            if (($handle = fopen($_FILES['pd_file']['tmp_name'], "r")) !== FALSE) {
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
            $upload = $fileManager->save_uploaded_file('pd_file', $file_name);

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
        include_once('lib/FileManager.php');
        include_once('model/MPredictiveDial.php');
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

            $id_start_value = (int) ord(substr($response->skill_id,0,1)).ord(substr($response->skill_id,1,1)).'000000';
            if ($response->is_delete) {
                $mainobj = new MPredictiveDial();
                $mainobj->deleteDialNumbers($response->skill_id);
            } else {
                $id_start_value = MPredictiveDial::GET_MAX_ID($response->skill_id);
            }

            /// is customer id exists in csv
            $is_customer_id_exist = in_array("customer_id", $obj) ? true : false;

            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    $data = array_filter($data);
                    if ($row > 1 && !empty($data)) {
                        $row_data_ary = [];
                        foreach ($data as $key => $value) {
                            $len = strlen($value);
                            if (!empty($this->valid_field[$obj[$key]]) && $len >= $this->valid_field[$obj[$key]]['validation']['min-len'] && $len <= $this->valid_field[$obj[$key]]['validation']['max-len']) {
                                if ($this->valid_field[$obj[$key]]['is_mobile_number']) {
                                    $value = preg_replace("/[^0-9]/","", $value);
                                    $value = substr($value, 0, 1) > 0 ? "0".$value : $value;
                                }
                                $row_data_ary[] = $value;
                            }
                        }
                        $response->success_count++;
                        $csv_data_ary[] = $row_data_ary;
                    }
                }
                fclose($handle);
            }

            if (!empty($csv_data_ary)) {
                $prepared_csv = $extra->file_upload_url."pd_sql_load".str_replace('.','',microtime(true)).".csv";
                $fp = fopen($prepared_csv ,'w');
                foreach ($csv_data_ary as $row) {
                    $id = $id_start_value++;
                    if (!$is_customer_id_exist) {
                        $customer_id = substr(bin2hex(random_bytes(8)), 0, 16);
                        array_unshift($row, $id, $response->skill_id, $customer_id);
                    } else {
                        array_unshift($row, $id, $response->skill_id);
                    }
                    fputcsv($fp, $row);
                }
                fclose($fp);

                if (!$is_customer_id_exist) {
                    array_unshift($obj, 'id', 'skill_id', 'customer_id');
                } else {
                    array_unshift($obj, 'id', 'skill_id');
                }

                $mainobj = new MPredictiveDial();
                $mainobj->csv_load_file = $prepared_csv;
                $mainobj->csv_load_column = implode(",", $obj);
                $response->is_success = $mainobj->csv_load();

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

    function savePredictiveDialNumber()
    {
        include('model/MSkill.php');
        include('model/MPredictiveDial.php');
        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');
        $pd_model = new MPredictiveDial();

        include('lib/FileManager.php');
        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;

        $is_overwrite = 'N';
        $skill_id = $request->getRequest ( 'sid' );
        $skill_name = !empty($skills[$skill_id]) ? $skills[$skill_id] : "";
        $skill_name = !empty($skill_name) ? "to {$skill_name}" : "";


        if (strlen($skill_id) !== 2 || !ctype_upper($skill_id) || !array_key_exists($skill_id,$skills)) {
            exit;
        }

        $data['heading'] = $this->getExcelHeading($skill_id);

        $data['skill_id'] = $skill_id;
        if ($request->isPost())
        {
            $predictive_dial_number = $this->getSubmittedPredictiveDialNumber();
            $is_number_file_uploaded = false;
            $file_type = '';
            if (empty($errMsg))
            {
                $resp = FileManager::check_file_for_upload('number', 'csv');
                if ($resp == FILE_EXT_INVALID)
                {
                    $resp = FileManager::check_file_for_upload('number', 'txt');
                    if ($resp == FILE_EXT_INVALID)
                    {
                        $errMsg = 'Please select a CSV or TXT file';
                    }
                    else if ($resp == FILE_UPLOADED)
                    {
                        $is_number_file_uploaded = true;
                        $file_type = 'csv';
                    }
                }
                else if ($resp == FILE_UPLOADED)
                {
                    $is_number_file_uploaded = true;
                    $file_type = 'csv';
                }

                if ($is_number_file_uploaded)
                {
                    $is_overwrite = isset($_POST['is_overwrite']) && $_POST['is_overwrite'] == 'Y' ? 'Y' : 'N';
                    $numValidationMsg = $this->getNumberValidationMsg($data['heading'], $data['skill_id'], $file_type);
                    if (!empty($numValidationMsg))
                    {
                        $errMsg = $numValidationMsg;
                    }
                }
            }

            if (empty($errMsg))
            {
                $isUpdate = false;

                if ($is_number_file_uploaded)
                {
                    if ($is_overwrite == 'Y')
                    {
                        $pd_model->deleteDialNumbers($skill_id);
                    }

                    $count_num = $this->processNumbers($pd_model, $data, $file_type);
                    //$count_num = $this->processNumbers($pd_model, $data['heading'], $file_type);
                }

                if ($isUpdate || $count_num > 0)
                {
                    $errType = 0;
                    $errMsg = 'Predictive Dial Numbers Added Successfully !!';
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=manage&sid=".$this->getRequest()->getRequest('sid')."&sname=".$this->getRequest()->getRequest('sname'));
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
                else
                {
                    $errMsg = 'Failed to add predictive dial number(s) !!';
                }

            }
        }

        $data['side_menu_index'] = 'predictive';
        $data['predictive_dial_number'] = $predictive_dial_number;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['is_overwrite'] = $is_overwrite;
        $data['pageTitle'] = "Add Predictive Dial Numbers ".$skill_name;
        $this->getTemplate()->display('predictive_dial_form', $data);
    }

    /**
     * @param $heading
     * @param $record
     * @return stdClass
     */
    function getUploadedHeadInfo($heading, $record)
    {
        $headIndex = new stdClass();
        $headIndex->hagent_id = -1;
        $headIndex->hcustomer_id = -1;
        $headIndex->hnumber_1 = -1;
        $headIndex->hnumber_2 = -1;
        $headIndex->hnumber_3 = -1;
        $headIndex->htitle = -1;
        $headIndex->hfirst_name = -1;
        $headIndex->hlast_name = -1;
        $headIndex->hstreet = -1;
        $headIndex->hcity = -1;
        $headIndex->hstate = -1;
        $headIndex->hzip = -1;
        //$headIndex->hemail = -1;

        $headIndex->hcustom_value_1 = -1;
        $headIndex->hcustom_value_2 = -2;
        $headIndex->hcustom_value_3 = -3;
        $headIndex->hcustom_value_4 = -4;

        $headIndex->hagent_altid = -1;

        $headIndex->error = "";

        $num = is_array($record) ? count($record) : 0;

        for ($c=0; $c < $num; $c++)
        {
            $hd = trim($record[$c]);
            if (!empty($hd))
            {
                if ($hd == $heading->agent_id_heading) $headIndex->hagent_id = $c;
                if ($hd == $heading->customer_id_heading) $headIndex->hcustomer_id = $c;
                if ($hd == $heading->number_1_heading) $headIndex->hnumber_1 = $c;
                if ($hd == $heading->number_2_heading) $headIndex->hnumber_2 = $c;
                if ($hd == $heading->number_3_heading) $headIndex->hnumber_3 = $c;
                if ($hd == $heading->title_heading) $headIndex->htitle = $c;
                if ($hd == $heading->first_name_heading) $headIndex->hfirst_name = $c;
                if ($hd == $heading->last_name_heading) $headIndex->hlast_name = $c;
                if ($hd == $heading->street_heading) $headIndex->hstreet = $c;
                if ($hd == $heading->city_heading) $headIndex->hcity = $c;
                if ($hd == $heading->state_heading) $headIndex->hstate = $c;
                if ($hd == $heading->zip_heading) $headIndex->hzip = $c;
                if ($hd == $heading->email_heading) $headIndex->hemail = $c;

                if ($hd == $heading->custom_value_1_heading) $headIndex->hcustom_value_1 = $c;
                if ($hd == $heading->custom_value_2_heading) $headIndex->hcustom_value_2 = $c;
                if ($hd == $heading->custom_value_3_heading) $headIndex->hcustom_value_3 = $c;
                if ($hd == $heading->custom_value_4_heading) $headIndex->hcustom_value_4 = $c;

                if ($hd == $heading->agent_altid_heading) $headIndex->hagent_altid = $c;
                //if ($c == 4) echo "'$record[$c]' , '$heading->number3_heading'";
            }
        }


        if (!empty($heading->agent_id_heading) && $headIndex->hagent_id < 0) $headIndex->error = "Invalid agent ID column defined !!";
        if (!empty($heading->customer_id_heading) && $headIndex->hcustomer_id < 0) $headIndex->error = "Invalid customer ID column defined !!";
        if (!empty($heading->number_1_heading) && $headIndex->hnumber_1 < 0) $headIndex->error = "Invalid Number 1 column defined !!";
        if (!empty($heading->number_2_heading) && $headIndex->hnumber_2 < 0) $headIndex->error = "Invalid Number 2 column defined !!";
        if (!empty($heading->number_3_heading) && $headIndex->hnumber_3 < 0) $headIndex->error = "Invalid Number 3 column defined !!";
        if (!empty($heading->title_heading) && $headIndex->htitle < 0) $headIndex->error = "Invalid Title column defined !!";
        if (!empty($heading->first_name_heading) && $headIndex->hfirst_name < 0) $headIndex->error = "Invalid FName column defined !!";
        if (!empty($heading->last_name_heading) && $headIndex->hlast_name < 0) $headIndex->error = "Invalid LName column defined !!";
        if (!empty($heading->street_heading) && $headIndex->hstreet < 0) $headIndex->error = "Invalid Street column defined !!";
        if (!empty($heading->city_heading) && $headIndex->hcity < 0) $headIndex->error = "Invalid City column defined !!";
        if (!empty($heading->state_heading) && $headIndex->hstate < 0) $headIndex->error = "Invalid State column defined !!";
        if (!empty($heading->zip_heading) && $headIndex->hzip < 0) $headIndex->error = "Invalid Zip column defined !!";
        //if (!empty($heading->email_heading) && $headIndex->hemail < 0) $headIndex->error = "Invalid Email column defined !!";
        if (!empty($heading->custom_value_1_heading) && $headIndex->hcustom_value_1 < 0) $headIndex->error = "Invalid column defined for Custom Label 1 !!";
        if (!empty($heading->custom_value_2_heading) && $headIndex->hcustom_value_2 < 0) $headIndex->error = "Invalid column defined for Custom Label 2 !!";
        if (!empty($heading->custom_value_3_heading) && $headIndex->hcustom_value_3 < 0) $headIndex->error = "Invalid column defined for Custom Label 3 !!";
        if (!empty($heading->custom_value_4_heading) && $headIndex->hcustom_value_4 < 0) $headIndex->error = "Invalid column defined for Custom Label 4 !!";
        if (!empty($heading->agent_altid_heading) && $headIndex->hagent_altid < 0) $headIndex->error = "Invalid Agent Alt. ID column defined !!";

        if (empty($headIndex->error) && $headIndex->hnumber_1 < 0) {
            $headIndex->error = "Number 1 column is not defined !!";
        }

        return $headIndex;
    }

    function GetValidNumberRecord($headIndex, $record)
    {
        $rec = null;
        //print_r($headIndex);exit;
        if ($headIndex->hnumber_1 >= 0 && is_array($record))	{

            if ($headIndex->hnumber_1 >= 0) {
                $callto_orig = isset($record[$headIndex->hnumber_1]) ? $record[$headIndex->hnumber_1] : '';
                //$callto = str_replace($discard, "", $callto_orig);
                $callto = preg_replace("/[^0-9]/","", $callto_orig);

                if ($this->isValidNumber($callto)) {
                    $rec['number_1'] = $callto;
                }

            }

            if ($headIndex->hnumber_2 >= 0) {
                $callto_orig = isset($record[$headIndex->hnumber_2]) ? $record[$headIndex->hnumber_2] : '';
                //$callto = str_replace($discard, "", $callto_orig);
                $callto = preg_replace("/[^0-9]/","", $callto_orig);

                if ($this->isValidNumber($callto)) {
                    $rec['number_2'] = $callto;
                }

            }

            if ($headIndex->hnumber_3 >= 0) {
                $callto_orig = isset($record[$headIndex->hnumber_3]) ? $record[$headIndex->hnumber_3] : '';
                //$callto = str_replace($discard, "", $callto_orig);
                $callto = preg_replace("/[^0-9]/","", $callto_orig);

                if ($this->isValidNumber($callto)) {
                    $rec['number_3'] = $callto;
                }

            }

            if ($headIndex->hagent_id >= 0) {
                $rec['agent_id'] = isset($record[$headIndex->hagent_id]) ? preg_replace("/[^0-9]/","", $record[$headIndex->hagent_id]) : '';
            }
            if ($headIndex->hcustomer_id >= 0) {
                $rec['customer_id'] = isset($record[$headIndex->hcustomer_id]) ? preg_replace("/[^0-9]/","", $record[$headIndex->hcustomer_id]) : '';
            }
            if ($headIndex->htitle >= 0) $rec['title'] = isset($record[$headIndex->htitle]) ? $record[$headIndex->htitle] : '';
            if ($headIndex->hfirst_name >= 0) $rec['first_name'] = isset($record[$headIndex->hfirst_name]) ? $record[$headIndex->hfirst_name] : '';
            if ($headIndex->hlast_name >= 0) $rec['last_name'] = isset($record[$headIndex->hlast_name]) ? $record[$headIndex->hlast_name] : '';
            if ($headIndex->hstreet >= 0) $rec['street'] = isset($record[$headIndex->hstreet]) ? $record[$headIndex->hstreet] : '';
            if ($headIndex->hcity >= 0) $rec['city'] = isset($record[$headIndex->hcity]) ? $record[$headIndex->hcity] : '';
            if ($headIndex->hstate >= 0) $rec['state'] = isset($record[$headIndex->hstate]) ? $record[$headIndex->hstate] : '';
            if ($headIndex->hzip >= 0) $rec['zip'] = isset($record[$headIndex->hzip]) ? $record[$headIndex->hzip] : '';
            //if ($headIndex->hemail >= 0) $rec['email'] = isset($record[$headIndex->hemail]) ? $record[$headIndex->hemail] : '';

            if ($headIndex->hcustom_value_1 >= 0) $rec['custom_value_1'] = isset($record[$headIndex->hcustom_value_1]) ? $record[$headIndex->hcustom_value_1] : '';
            if ($headIndex->hcustom_value_2 >= 0) $rec['custom_value_2'] = isset($record[$headIndex->hcustom_value_2]) ? $record[$headIndex->hcustom_value_2] : '';
            if ($headIndex->hcustom_value_3 >= 0) $rec['custom_value_3'] = isset($record[$headIndex->hcustom_value_3]) ? $record[$headIndex->hcustom_value_3] : '';
            if ($headIndex->hcustom_value_4 >= 0) $rec['custom_value_4'] = isset($record[$headIndex->hcustom_value_4]) ? $record[$headIndex->hcustom_value_4] : '';

            if ($headIndex->hagent_altid >= 0) $rec['agent_altid'] = isset($record[$headIndex->hagent_altid]) ? $record[$headIndex->hagent_altid] : '';

            //if (!empty($rec) && isset($rec['dial_number']) && !empty($rec['dial_number'])) array_push($number, $rec);

        }

        if (!empty($rec) && isset($rec['number_1']) && !empty($rec['number_1'])) {
            return $rec;
        }

        return null;
    }

    function getNumberValidationMsg($heading, $skillId, $num_file_type)
    {
        $headIndex = new stdClass();
        $headIndex->hcustomer_id = -1;
        $headIndex->hnumber_1 = -1;
        $headIndex->error = "File upload error !!";

        $file = $_FILES['number']['tmp_name'];
        $fileName = $_FILES['number']['name'];

        $err = "No valid number found !!";

        $row = 1;
        $fp = fopen($file, "r");

        if ($num_file_type == 'csv') {
            while (($record = fgetcsv($fp, 1000, ",")) !== FALSE) {
                if ($row == 1) {
                    $headIndex = $this->getUploadedHeadInfo($heading, $record);
                    if (empty($headIndex)) {
                        $headIndex->error = "No head definition found !!";
                    }
                    if (!empty($headIndex->error)) {
                        break;
                    }
                } else {
                    $rec = $this->GetValidNumberRecord($headIndex, $record);
                    if (!empty($rec)) {
                        $err = '';
                        break;
                    }
                }
                $row++;
            }
        }

        fclose($fp);

        if (!empty($headIndex->error)) {
            return $headIndex->error;
        }

        if (!empty($err)) {
            return $err;
        }

        if ($num_file_type == 'csv')
        {
            $hf = fopen("temp/heading_skill_".$skillId.".txt", "w");
            fwrite($hf, "$heading->agent_id_heading,$heading->customer_id_heading,$heading->number_1_heading,$heading->number_2_heading,".
                "$heading->number_3_heading,".
                "$heading->title_heading,$heading->first_name_heading,".
                "$heading->last_name_heading,$heading->street_heading,$heading->city_heading,".
                "$heading->state_heading,$heading->zip_heading,$heading->email_heading,$heading->custom_value_1_heading,$heading->custom_value_2_heading,".
                "$heading->custom_value_3_heading,$heading->custom_value_4_heading,$heading->agent_altid_heading");
            fclose($hf);
        }

        return '';
    }

    function processNumbers($pd_model, $heading, $num_file_type)
    {
        $skill_id = $heading['skill_id'];
        $heading = $heading['heading'];
        $headIndex = new stdClass();
        $headIndex->hcustomer_id = -1;
        $headIndex->hnumber_1 = -1;
        $headIndex->error = "File upload error !!";


        $file = $_FILES['number']['tmp_name'];
        $fileName = $_FILES['number']['name'];
        //echo 'asd';
        $row = 1;
        $discard = array("+", "(", ")", "-", " ");
        $fp = fopen($file, "r");

        $numNumberUploaded = 0;
        if ($num_file_type == 'csv') {
            while (($record = fgetcsv($fp, 1000, ",")) !== FALSE) {

                //echo $row . "<br>";

                $num = is_array($record) ? count($record) : 0;
                if ($row == 1) {
                    $headIndex = $this->getUploadedHeadInfo($heading, $record);
                    if (empty($headIndex)) {
                        $headIndex->error = "No head definition found !!";
                    }
                    if (!empty($headIndex->error)) {
                        break;
                    }

                } else {
                    $rec = $this->GetValidNumberRecord($headIndex, $record);
                    if (!empty($rec)) {
                        $isEdit = $pd_model->addDialNumber($rec,$skill_id);
                        if ($isEdit) $numNumberUploaded++;
                    }
                }
                $row++;
            }
        } elseif($num_file_type == 'txt') {
            //echo 'num'.$num;
            /*
            while (($record = fgetcsv($fp, 2048, ",")) !== FALSE) {
                $num = is_array($record) ? count($record) : 0;
                for ($c=0; $c < $num; $c++) {
                    $callto_orig = $record[$c];
                    $callto = str_replace($discard, "", $callto_orig);
                    $rec = null;
                    if ($this->isValidNumber($callto)) {
                        $rec['dial_number'] = $callto;
                        array_push($number, $rec);
                    }
                }

                $row++;
            }
            */
        }
        //var_dump($num_file_type);
        fclose($fp);
        //var_dump($hnumber1);
        //var_dump($hnumber2);
        //var_dump($hnumber3);
        //var_dump($number);
        //exit;
        /*
        if (count($number) <= 0) {
            return "There is no valid phone number(s) in the file <font color='#888888'>$fileName</font>";
        } else {
            //echo $num_file_type;
            if ($num_file_type == 'csv') {
                $hf = fopen("temp/heading_lead.txt", "w");
                fwrite($hf, "$heading->customer_id_heading,$heading->dial_number_heading,$heading->dial_number_2_heading,".
                    "$heading->dial_number_3_heading,".
                    "$heading->dial_number_4_heading,$heading->title_heading,$heading->first_name_heading,".
                    "$heading->last_name_heading,$heading->street_heading,$heading->city_heading,".
                    "$heading->state_heading,$heading->zip_heading,$heading->email_heading,$heading->custom_value_1_heading,$heading->custom_value_2_heading,".
                    "$heading->custom_value_3_heading,$heading->custom_value_4_heading,$heading->agent_altid_heading");
                fclose($hf);
            }
        }
        */

        return $numNumberUploaded;
        //return $number;
    }

    function isValidNumber($number)
    {
        if (ctype_digit($number)) {
            $len = strlen($number);
            //if ($len == 10 || ($len == 11 && substr($number, 0, 1) == 0)) return true;
            if ($len>=6 && $len<=13 && ctype_digit($number)) return true;
        }
        return false;
    }

    function getInitialLead()
    {
        $pd_number = new stdClass();
        $pd_number->title = '';
        $pd_number->reference = '';
        $pd_number->country_code = '';
        $pd_number->custom_label_1 = '';
        $pd_number->custom_label_2 = '';
        $pd_number->custom_label_3 = '';
        $pd_number->custom_label_4 = '';

        return $pd_number;
    }

    function getExcelHeading($skillId)
    {
        $data = new stdClass();
        $data->agent_id_heading = '';
        $data->customer_id_heading = '';
        $data->number_1_heading = '';
        $data->number_2_heading = '';
        $data->_number_3_heading = '';
        $data->title_heading = '';
        $data->first_name_heading = '';
        $data->last_name_heading = '';
        $data->street_heading = '';
        $data->city_heading = '';
        $data->state_heading = '';
        $data->zip_heading = '';
        //$data->email_heading = '';

        $data->custom_value_1_heading = '';
        $data->custom_value_2_heading = '';
        $data->custom_value_3_heading = '';
        $data->custom_value_4_heading = '';
        $data->agent_altid_heading = '';

        $heading_file = "temp/heading_skill_".$skillId.".txt";

        if (isset($_POST['number_1_heading'])) {
            $data->agent_id_heading = isset($_POST['agent_id_heading']) ? trim($_POST['agent_id_heading']) : '';
            $data->customer_id_heading = isset($_POST['customer_id_heading']) ? trim($_POST['customer_id_heading']) : '';
            $data->number_1_heading = isset($_POST['number_1_heading']) ? trim($_POST['number_1_heading']) : '';
            $data->number_2_heading = isset($_POST['number_2_heading']) ? trim($_POST['number_2_heading']) : '';
            $data->number_3_heading = isset($_POST['number_3_heading']) ? trim($_POST['number_3_heading']) : '';
            $data->title_heading = isset($_POST['title_heading']) ? trim($_POST['title_heading']) : '';
            $data->first_name_heading = isset($_POST['first_name_heading']) ? trim($_POST['first_name_heading']) : '';
            $data->last_name_heading = isset($_POST['last_name_heading']) ? trim($_POST['last_name_heading']) : '';
            $data->street_heading = isset($_POST['street_heading']) ? trim($_POST['street_heading']) : '';
            $data->city_heading = isset($_POST['city_heading']) ? trim($_POST['city_heading']) : '';
            $data->state_heading = isset($_POST['state_heading']) ? trim($_POST['state_heading']) : '';
            $data->zip_heading = isset($_POST['zip_heading']) ? trim($_POST['zip_heading']) : '';
            //$data->email_heading = isset($_POST['email_heading']) ? trim($_POST['email_heading']) : '';

            $data->custom_value_1_heading = isset($_POST['custom_value_1_heading']) ? trim($_POST['custom_value_1_heading']) : '';
            $data->custom_value_2_heading = isset($_POST['custom_value_2_heading']) ? trim($_POST['custom_value_2_heading']) : '';
            $data->custom_value_3_heading = isset($_POST['custom_value_3_heading']) ? trim($_POST['custom_value_3_heading']) : '';
            $data->custom_value_4_heading = isset($_POST['custom_value_4_heading']) ? trim($_POST['custom_value_4_heading']) : '';

            $data->agent_altid_heading = isset($_POST['agent_altid_heading']) ? trim($_POST['agent_altid_heading']) : '';

        }
        else if (file_exists($heading_file))
        {
            $hf = fopen($heading_file, "r");
            $heading = fread($hf, filesize($heading_file));
            fclose($hf);

            $rec_head = explode(",", $heading);
            if (is_array($rec_head))
            {
                $data->agent_id_heading = isset($rec_head[0]) ? $rec_head[0] : '';
                $data->customer_id_heading = isset($rec_head[1]) ? $rec_head[1] : '';
                $data->number_1_heading = isset($rec_head[2]) ? $rec_head[2] : '';
                $data->number_2_heading = isset($rec_head[3]) ? $rec_head[3] : '';
                $data->number_3_heading = isset($rec_head[4]) ? $rec_head[4] : '';
                $data->title_heading = isset($rec_head[5]) ? $rec_head[5] : '';
                $data->first_name_heading = isset($rec_head[6]) ? $rec_head[6] : '';
                $data->last_name_heading = isset($rec_head[7]) ? $rec_head[7] : '';
                $data->street_heading = isset($rec_head[8]) ? $rec_head[8] : '';
                $data->city_heading = isset($rec_head[9]) ? $rec_head[9] : '';
                $data->state_heading = isset($rec_head[10]) ? $rec_head[10] : '';
                $data->zip_heading = isset($rec_head[11]) ? $rec_head[11] : '';
                $data->email_heading = isset($rec_head[12]) ? $rec_head[12] : '';
                $data->custom_value_1_heading = isset($rec_head[13]) ? $rec_head[13] : '';
                $data->custom_value_2_heading = isset($rec_head[14]) ? $rec_head[14] : '';
                $data->custom_value_3_heading = isset($rec_head[15]) ? $rec_head[15] : '';
                $data->custom_value_4_heading = isset($rec_head[16]) ? $rec_head[16] : '';
                $data->agent_altid_heading = isset($rec_head[17]) ? $rec_head[17] : '';
            }
        }

        return $data;
    }

    function getSubmittedPredictiveDialNumber()
    {
        $posts = $this->getRequest()->getPost();
        $predictive_dial_number = null;

        if (is_array($posts))
        {
            foreach ($posts as $key=>$val)
            {
                $predictive_dial_number->$key = trim($val);
            }
        }

        return $predictive_dial_number;
    }

    function getValidationMsg($lead)
    {
        $err = '';
        if (empty($lead->title)) return "Provide lead title";
        return $err;
    }

    function getPredictiveProfileValidationMsg($profile)
    {
        $err = '';
        if (empty($profile->skill_id)) return "Skill ID is required";
        if (empty($profile->dial_engine)) return "Provide dial engine";

        if (!preg_match("/^[0-9]{1,3}$/", $profile->max_out_bound_calls)) return "Provide valid MAX outbound call(s)";
        if ($profile->dial_engine == 'PD' || $profile->dial_engine == 'RS') {
            if (strlen($profile->max_pacing_ratio) == 0 || $profile->max_pacing_ratio == '.') return "Provide MAX pacing ratio";
            if (!preg_match("/^[0-9]{0,2}(\.[0-9]{1,2})?$/", $profile->max_pacing_ratio)) return "Provide valid MAX pacing ratio";
            if ($profile->max_pacing_ratio < 1) return "Minimum allowed pacing ratio is 1.00";
        }
        if (empty($profile->dial_alt_number) || strlen($profile->dial_alt_number) != 1) return "Provide a Valid Dial Alt. Number Action";
        if (strlen($profile->max_drop_rate) == 0 || $profile->max_drop_rate == '.') return "Provide MAX drop ratio";
        if (!preg_match("/^[0-9]{0,2}(\.[0-9]{0,2})?$/", $profile->max_drop_rate)) return "Provide valid MAX drop ratio";

        if (!preg_match("/^[0-9]{1}$/", $profile->retry_count)) return "Provide valid retry count";
        //if (!preg_match("/^[0-9]{1,4}$/", $campaign->retry_interval_amd)) return "Provide valid retry interval AMD";
        //if (!preg_match("/^[0-9]{1,4}$/", $profile->retry_interval_drop)) return "Provide valid retry interval drop";
        //if (!preg_match("/^[0-9]{1,4}$/", $profile->retry_interval_vm)) return "Provide valid retry interval busy";
        if (!preg_match("/^[0-9]{1,4}$/", $profile->retry_interval_noanswer)) return "Provide valid retry interval noanswer";
        //if (!preg_match("/^[0-9]{1,4}$/", $profile->retry_interval_unreachable)) return "Provide valid retry interval unreachable";
        if (!preg_match("/^[0-9]{1,3}$/", $profile->max_out_bound_calls)) return "Provide valid max out bound call";
        if (!preg_match("/^[0-9]{1,4}$/", $profile->max_call_per_agent)) return "Provide valid max call per agent";
        if (empty($profile->drop_call_action) || !in_array($profile->drop_call_action, array('HU','SW','MH','PF','AN')) || strlen($profile->drop_call_action ) != 2) return "Provide a Valid Drop Call Action";
        if (empty($profile->vm_action) || !in_array($profile->vm_action, array('LM','RT')) || strlen($profile->vm_action ) != 2) return "Provide a Valid Voice Mail Action";
        if (empty($profile->fax_action) || !in_array($profile->fax_action, array('SF')) || strlen($profile->fax_action ) != 2) return "Provide a Valid Fax Action";
        if (empty($profile->status) || !in_array($profile->status, array('A','T','C','E','N')) || strlen($profile->status ) != 1) return "Provide a Valid Status";
        //if (empty($profile->start_time ) || strlen($profile->start_time  ) != 19) return "Provide a Valid Start Time";
        //if (empty($profile->stop_time ) || strlen($profile->stop_time  ) != 19) return "Provide a Valid Stop Time";
        if (empty($profile->timezone) || strlen($profile->timezone) > 30) return "Provide a Valid Timezone";
        if (empty($profile->run_hour ) || strlen($profile->run_hour ) > 24) return "Provide a valid Run Hour";


        return $err;
    }

    function findexts ($filename)
    {
        $filename = strtolower($filename) ;
        $exts = explode(".", $filename) ;
        $n = count($exts)-1;
        $exts = $exts[$n];
        return $exts;
    }

        function calc_new_time($old_tstamp)
        {
                $ctime = isset($_POST['ctime']) ? trim($_POST['ctime']) : 0;
                $topt = isset($_POST['topt']) ? trim($_POST['topt']) : 'min';
                $calc_opt = isset($_POST['calc_opt']) ? trim($_POST['calc_opt']) : 'A';

                if (empty($ctime)) return $old_tstamp;

                if ($topt == 'day') {
                        $mul = 86400;
                } else if ($topt == 'hrs') {
                        $mul = 3600;
                } else {
                        $mul = 60;
                }

                if ($calc_opt == 'L') {
                        $newtime = $old_tstamp - $ctime * $mul;
                } else {
                        $newtime = $old_tstamp + $ctime * $mul;
                }

                return $newtime;
        }

    function actionSetetime()
    {

	    include('model/MSkill.php');
        include('model/MPredictiveDial.php');

        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');

	    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';

        if (strlen($sid) !== 2 || !array_key_exists($sid,$skills) || !ctype_upper($sid)) {
            exit();
        }

        $pd_model = new MPredictiveDial();


        $ctime = 0;
        $topt = 'min';
        $calc_opt = 'A';
        $errMsg = '';
        //$data['pd_profile'] =
        $pdprofile = $pd_model->get_pd_profile($sid);
        $data['pd_profile'] = is_array($pdprofile) ? $pdprofile[0] : null;
        if (empty($data['pd_profile'])) exit;

        $data['pageTitle'] = 'Update End Time of Campaign :: ' . $skills[$sid];

        $new_end_time = '';
        if (isset($_POST['ctime'])) {

            if (!isset($_POST['submitreset'])) {
                $ctime = isset($_POST['ctime']) ? trim($_POST['ctime']) : 0;
                $topt = isset($_POST['topt']) ? trim($_POST['topt']) : 'min';
                $calc_opt = isset($_POST['calc_opt']) ? trim($_POST['calc_opt']) : 'A';
            }

            if (isset($_POST['submitcalc'])) {
                $ctime = trim($_POST['ctime']);
                if (!preg_match("/^[0-9]{1,4}$/", $ctime)) $errMsg = "Provide valid time";
                else if ($ctime == 0) $errMsg = "Time cannot be zero";
                else $new_end_time = $this->calc_new_time(strtotime($data['pd_profile']->stop_time));
            } else if (isset($_POST['submitsave'])) {

                $ctime = trim($_POST['ctime']);
                if (!preg_match("/^[0-9]{1,4}$/", $ctime)) $errMsg = "Provide valid time";
                else if ($ctime == 0) $errMsg = "Minute cannot be zero";
                else $new_end_time = $this->calc_new_time(strtotime($data['pd_profile']->stop_time));

                if (empty($errMsg) && !empty($new_end_time)) {

                    if ($pd_model->updatePDEndTime($sid, $new_end_time, $data['pd_profile'])) {
                        $data['message'] = 'End time updated Successfully !!';
                        $data['msgType'] = 'success';
                        $data['refreshParent'] = true;
                        $this->getTemplate()->display_popup('popup_message', $data);
                    } else {
                        $errMsg = "Failed to update end time";
                    }

                }

            }
        }


        $data['new_end_time'] = $new_end_time;
        $data['sid'] = $sid;
        $data['topt'] = $topt;
        $data['calc_opt'] = $calc_opt;
        $data['ctime'] = $ctime;
        $data['errMsg'] = $errMsg;
        $data['skill_name'] = $skills[$sid];
        $data['request'] = $this->getRequest();
        $this->getTemplate()->display_popup('pd_end_time_form', $data);
    }


    function actionAddProfile()
    {
        include('model/MSkill.php');
        include('model/MPredictiveDial.php');
        $predictive_model = new MPredictiveDial();

        $skill = new MSkill();
        $skills = $skill->getSkillsNamesArray('P');

        $request = $this->getRequest();
        $skill_id = $request->getRequest('sid');
        $skill_name = !empty($skills[$skill_id]) ? $skills[$skill_id] : "";

        if (strlen($skill_id) !== 2 || !array_key_exists($skill_id,$skills) || !ctype_upper($skill_id))
        {
            exit();
        }


        $errMsg = '';
        $errType = 1;

        $data['timezones'] = array(
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Phoenix',
            'America/Los_Angeles',
            'America/Anchorage',
            'America/Adak',
            'Pacific/Honolulu',
            'Asia/Dhaka'
        );
        
        $myTZ = date("e");
        if (!in_array($myTZ, $data['timezones'])) {
            $data['timezones'][] = $myTZ;
        }

        $data['dial_engine_options'] = array('PG'=>'Progressive', 'PD'=>'Predictive',  'RS'=>'Responsive', 'AN'=>'Announcement', 'AA'=>'Acknowledgement', 'SR'=>'Survey');
        $data['dial_alt_number_options'] = array('N'=>'DoNotDial', 'S'=>'Sequential', 'M'=>'Multi-Ring');
        $data['drop_call_action_options'] = array('HU'=>'Hung-up', 'SW'=>'Silent Wait','MH'=>'Music','PF'=>'Play Message','AN'=>'Play Msg-DTMF');
        $data['vm_action_options'] = array('LM'=>'Leave Message', 'RT'=>'Retry');


        if ($request->isPost()) {

            $predictive_model = $this->getSubmittedPdProfile();
            $kval = '';
            for ($i = 0; $i <= 23; $i++) {
                $l = 'rhour' . $i;
                $kval .= isset($predictive_model->$l) ? '1' : '0';
            }
            $predictive_model->run_hour = $kval;
            $errMsg = $this->getPredictiveProfileValidationMsg($predictive_model);


            if (empty($errMsg)) {
                $isUpdate = MPredictiveDial::addPredictiveProfile($predictive_model);
                if ($isUpdate) {
                    $errType = 0;
                    $errMsg = $isUpdate == "U" ? 'PD profile updated successfully !!' : 'PD profile added successfully';
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName().'&act=manage&sid='.$request->getRequest('sid').'&sname='.$request->getRequest('sname'));
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                } else {
                    $errMsg =  'Failed !!, Try later.';
                }

            }

        } else {
            $predictive_model = MPredictiveDial::getPdProfileBySkillId($skill_id);
        }

        $data['status_options'] = array('A'=>'Active', 'T'=>'Timeout', 'C'=>'Completed', 'E'=>'Error');
        $data['fax_action_options'] = array('SF'=>'Send Fax');

        $data['skill_id'] = $skill_id;
        $data['predictive_model'] = $predictive_model;
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['pageTitle'] = empty($predictive_model->skill_id) ? 'Add PD Profile' : 'Update PD Profile';
        $data['pageTitle'] .= " :: {$skill_name}";
        $data['side_menu_index'] = 'skills';
        $data['smi_selection'] = 'skills_';
        $this->getTemplate()->display('predictive_dial_profile_form', $data);
    }

    function getSubmittedPdProfile()
    {
        $posts = $this->getRequest()->getPost();
        $profile = null;

        if (is_array($posts))
        {
            foreach ($posts as $key=>$val)
            {
                if (is_string($val))
                {
                    $profile->$key = trim($val);
                } else if (is_array($val))
                {
                    $profile->$key = array();
                    foreach ($val as $val1)
                    {
                        array_push($profile->$key, trim($val1));
                    }
                }
            }
        }

        return $profile;
    }

    function actionList() {
        $data['pageTitle'] = 'Skill List';
        $data['dataUrl'] = $this->url('task=get-home-data&act=predictive-dial-list');
        $this->getTemplate()->display('predictive_dial_list', $data);
    }
}
