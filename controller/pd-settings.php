<?php
require_once 'BaseTableDataController.php';

class PdSettings extends BaseTableDataController
{
    private $results;

    public function __construct()
    {
        parent::__construct();
        include_once ('model/MPDSettings.php');
    }

    public function init()
    {
        $this->actionPdSkillList();
    }

    private function actionPdSkillList () {
        $data['pageTitle'] = 'Predictive Dial Skills';
        $data['topMenuItems'] = array(
            array(
                'href'=>'task='.$this->request->getControllerName().'&act=add',
                'img'=>'fa fa-cog',
                'label'=>'Add New Settings',
                'title'=>'New Predictive Dial Lead Upload Settings'
            )
        );

        $data['dataUrl'] = $this->url('task='.$this->request->getControllerName().'&act=pd-skill-grid-list');
        $data['userColumn'] = "Page ID";
        $data['main_menu'] = 'page';
        $this->getTemplate()->display('pd/pd-skill-list', $data);
    }

    public function actionPdSkillGridList()
    {
        $report_config_list = get_report_config_list();
        $controller_idx = $this->getRequest()->getControllerName() . '_' . $this->getRequest()->getActionName();
        $db_role_id = UserAuth::getSesGCCDBRoleId();
        $report_restriction_days = '';
        $report_hide_col = [];
        if (isset($report_config_list[$db_role_id])) {
            $report_restriction_days = $report_config_list[$db_role_id][$controller_idx]['days'];
            $report_hide_col = $report_config_list[$db_role_id][$controller_idx]['hide_col'];
        }

        $mainobj = new MPDSettings();
        $this->pagination->num_records = $mainobj->numPDSkills();
        $this->results = $this->pagination->num_records > 0 ? $mainobj->getPDSkills($this->pagination->rows_per_page, $this->pagination->getOffset()) : null;
        $this->modifyPdSkillsListResult();

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->hideCol = array_merge($response->hideCol, $report_hide_col);
        $response->rowdata = $this->results;
        $this->ShowTableResponse();
    }

    private function modifyPdSkillsListResult()
    {
        if (!empty($this->results)) {
            foreach ($this->results as $data) {
                $data->status = "<a class='confirm-status-link' onclick='confirm_status(event)' data-msg='Do you confirm that you want to " . ($data->status == STATUS_ACTIVE ? "inactivate" : "activate") . " this page: " . $data->name . "?' data-href='" . $this->url("task=".$this->request->getControllerName()."&act=status&sid=" . $data->skill_id . "&status=" . ($data->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE)) . "'>" . ($data->status == STATUS_ACTIVE ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
                $data->action = "<a href='". $this->url("task=".$this->request->getControllerName()."&act=update&sid=".$data->skill_id)."'> <i class=\"fa fa-edit\"></i> Edit</a> ";
                //$data->action .= " <a href='". $this->url("task=".$this->request->getControllerName()."&act=delete&sid=".$data->skill_id)."'> <i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>";
            }
        }
    }

    /*
     * add new pd upload
     * settings
     */
    public function actionAdd() {
        include_once('lib/FormValidator.php');

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $page = '';
        $error_data = '';
        $settings_data = [];
        $mainobj = new MPDSettings();
        $data['skill_list'] = $mainobj->getPDSettingsSkills();

        $data['topMenuItems'] = array(
            array(
                'href'=>'task='.$this->request->getControllerName(),
                'label'=>'Cancel',
                'title'=>'Cancel'
            )
        );

        if ($request->isPost()) {
            $response = $mainobj->saveData($this->getRequest()->getPost());
            if($response[MSG_RESULT]){
                $errType = 0;
                $errMsg = $response[MSG_MSG];
                $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
            }else{
                $errType = 1;
                $errMsg = $response[MSG_MSG];
                $settings_data = $response[MSG_DATA];
                $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
            }
        }

        $data['pageTitle'] = 'Add New PD Upload Settings';
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['error_data'] = $error_data;
        $data['settings_data'] = $settings_data;
        $data['pd_fields'] = PD_FIELD_LABELS;
        $this->getTemplate()->display('pd/settings-form', $data);
    }

    public function actionUpdate () {
        include_once('lib/FormValidator.php');
        include_once('model/MSkill.php');

        $request = $this->getRequest();
        $errMsg = '';
        $errType = 1;
        $page = '';
        $error_data = '';
        $settings_data = [];
        $skill_id = !empty($_REQUEST['sid']) ? $_REQUEST['sid'] : "";


        if (!empty($skill_id) && !empty($settings_data = MPDSettings::getDataBySkillId($skill_id))) {
            $skill_model = new MSkill();
            $skill_data = $skill_model->getSkillById($skill_id);

            $settings_data = !empty($settings_data) ? (object)array_column($settings_data, 'header', 'item') : [];
            $settings_data->skill_id = $skill_id;
            $settings_data->skill_name = !empty($skill_data) ? $skill_data->skill_name : "";

            if ($request->isPost()) {
                $mainobj = new MPDSettings();
                $response = $mainobj->saveData($this->getRequest()->getPost(), $settings_data);
                if($response[MSG_RESULT]){
                    $errType = 0;
                    $errMsg = $response[MSG_MSG];
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$url\">";
                }else{
                    $errType = 1;
                    $errMsg = $response[MSG_MSG];
                    $settings_data = $response[MSG_DATA];
                    $error_data = (isset($response[MSG_ERROR_DATA])) ? $response[MSG_ERROR_DATA] : [];
                }
            }

        } else {
            $errType = 1;
            $errMsg = "Invalid Data!!";
        }

        $data['topMenuItems'] = array(
            array(
                'href'=>'task='.$this->request->getControllerName(),
                'label'=>'Cancel',
                'title'=>'Cancel'
            )
        );
        $data['pageTitle'] = 'Add New PD Upload Settings';
        $data['main_menu'] = 'page';
        $data['request'] = $this->getRequest();
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['error_data'] = $error_data;
        $data['settings_data'] = $settings_data;
        $data['pd_fields'] = PD_FIELD_LABELS;
        $this->getTemplate()->display('pd/settings-form', $data);
    }

    public function actionStatus(){
        $skill_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

        // var_dump(empty($id));
        // var_dump(empty($id));

        if(empty($skill_id) || empty($status) || !in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE])){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'Your request is wrong!'
            ]));
        }

        $data = MPDSettings::getDataBySkillId($skill_id);
        if(empty($data)){
            die(json_encode([
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_MSG => 'This page is not exists!'
            ]));
        }

        if(MPDSettings::updateStatus($skill_id, $status)){
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


}
