<?php

class Scrmdc extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $this->actionSDisposition();
    }

    public function actionSDisposition()
    {
        $data['pageTitle'] = 'Skill CRM Disposition Code';
        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $data['topMenuItems'] = array(
            array('href'=>'task=stemplate', 'img'=>'icon/application_view_list.png', 'label'=>'List Template(s)'),
            array('href'=>'task=scrmdc&act=add&tid='.$tid, 'img'=>'add.png', 'label'=>'Add New Disposition Code')
        );
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'stemplate_';
        $this->getTemplate()->display('skill_crm_dispositions', $data);
    }


    public function init2()
    {
        include('model/MSkillCrmTemplate.php');
        include('lib/Pagination.php');
        $dc_model = new MSkillCrmTemplate();
        $pagination = new Pagination();

        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';

        $templateinfo = $dc_model->getTemplateById($tid);

        if (empty($templateinfo)) {
            exit;
        }

        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . '&tid=' . $tid);

        $pagination->num_records = $dc_model->numDispositions($tid);
        $data['dispositions'] = $pagination->num_records > 0 ?
            $dc_model->getDispositions($tid, $pagination->getOffset(), $pagination->rows_per_page) : null;
        $pagination->num_current_records = is_array($data['dispositions']) ? count($data['dispositions']) : 0;
        $data['pagination'] = $pagination;
        $data['request'] = $this->getRequest();
        $data['dc_model'] = $dc_model;
        $data['tid'] = $tid;
        $data['pageTitle'] = 'Skill CRM Disposition Code';
        $data['pageTitle'] .= ' for Template - ' . $templateinfo->title;
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'stemplate_';

        $data['topMenuItems'] = array(
            array('href'=>'task=stemplate', 'img'=>'icon/application_view_list.png', 'label'=>'List Template(s)'),
            array('href'=>'task=scrmdc&act=add&tid='.$tid, 'img'=>'add.png', 'label'=>'Add New Disposition Code')
        );

        $this->getTemplate()->display('skill_crm_dispositions', $data);
    }

    public function actionAdd()
    {
        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $this->saveService($tid);
    }

    public function actionUpdate()
    {
        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        $this->saveService($tid, $sid);
    }

    public function actionDel()
    {
        include('model/MSkillCrmTemplate.php');
        $dc_model = new MSkillCrmTemplate();

        $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

        $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&tid=$tid&page=".$cur_page);

        if ($dc_model->deleteDispositionId($dcode, $tid)) {
            $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>false, 'msg'=>'Disposition Code Deleted Successfully', 'redirectUri'=>$url));
        } else {
            $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Disposition Code', 'isError'=>true, 'msg'=>'Failed to Delete  Disposition Code', 'redirectUri'=>$url));
        }
    }

    public function actionDispositionchildren()
    {
        $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : '';
        $options = array();
        if (!empty($did)) {
            include('model/MSkillCrmTemplate.php');
            $dc_model = new MSkillCrmTemplate();

            $options = $dc_model->getDispositionChildrenOptions('', $did);
        }

        echo json_encode($options);
    }

    public function saveService($tid, $dis_code='')
    {
        include('model/MSkillCrmTemplate.php');
        include('model/MReportPartition.php');
        $dc_model = new MSkillCrmTemplate();
        $partition_model = new MReportPartition();

        $templateinfo = $dc_model->getTemplateById($tid);
        if (empty($templateinfo)) {
            exit;
        }

        $request = $this->getRequest();
        $groups = null;
        $errMsg = '';
        $errType = 1;
        if ($request->isPost()) {
            $service = $this->getSubmittedService();
            //dd($service);
            //var_dump($service);
            //exit;
            $groups = $dc_model->getDispositionGroupOptions($tid);
            $errMsg = $this->getValidationMsg($service, $tid, $dis_code, $groups, $dc_model);
            if (empty($errMsg)) {
                $is_success = false;

                $dis_group_id = '';
                if (!empty($service->group_id)) {
                    $dis_group_id = $service->group_id;
                } elseif (!empty($service->group_title)) {
                    //if (in_array($service->group_title, $groups, true)) return "Disposition group $service->group_title already exist";
                    $dis_group_id = $dc_model->addDispositionGroup($tid, $service->group_title);
                }

                $service->group_id = $dis_group_id;

                if (empty($dis_code)) {
                    if ($dc_model->addService($tid, $service)) {
                        $errMsg = 'Disposition code added successfully !!';
                        $is_success = true;
                    } else {
                        $errMsg = 'Failed to add disposition code !!';
                    }
                } else {
                    $oldservice = $this->getInitialService($tid, $dis_code, $dc_model);
                    if ($dc_model->updateService($oldservice, $service)) {
                        $errMsg = 'Disposition code updated successfully !!';
                        $is_success = true;
                    } else {
                        $errMsg = 'No change found !!';
                    }
                }

                if ($is_success) {
                    $errType = 0;
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&tid=$tid");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
            }
        } else {
            $service = $this->getInitialService($tid, $dis_code, $dc_model);
            if (empty($service)) {
                exit;
            }
        }

        $data['partitions'] = $partition_model->getPartitionsKeyValue();
        $data['service'] = $service;
        $data['dis_code'] = $dis_code;
        $data['tid'] = $tid;
        $data['groups'] = empty($groups) ? $dc_model->getDispositionGroupOptions($tid) : $groups;
        $data['disposition_ids'] = $dc_model->getDispositionPathArray($service->parent_id);
        $data['dc_model'] = $dc_model;
        $data['request'] = $request;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['pageTitle'] = empty($dis_code) ? 'Add New Disposition Code' : 'Update Disposition Code'.' : ' . $dis_code;
        $data['pageTitle'] .= ' for Template - ' . $templateinfo->title;
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'stemplate_';
        $this->getTemplate()->display('skill_crm_disposition_form', $data);
    }

    public function getInitialService($tid, $dis_code, $dc_model)
    {
        $service = new stdClass();

        if (empty($dis_code)) {
            $service->disposition_id = '';
            $service->title = '';
            $service->group_id = '';
            $service->parent_id = '';
            $service->altr_disposition_id = '';
            $service->partition_id = '';
            $service->disposition_type = '';
        } else {
            $service = $dc_model->getDispositionById($dis_code, $tid);
        }
        return $service;
    }

    public function getSubmittedService()
    {
        $posts = $this->getRequest()->getPost();
        $service = new stdClass();
        if (is_array($posts)) {
            foreach ($posts as $key=>$val) {
                $service->$key = trim($val);
            }
        }

        //if (!empty($dis_code)) $service->disposition_code = $dis_code;

        return $service;
    }

    public function getValidationMsg($service, $tid, $dis_code='', $groups, $dc_model)
    {
        if (empty($service->disposition_id)) {
            return "Disposition code is required.";
        }
        if (empty($service->title)) {
            return "Disposition title is required.";
        }
        if (empty($service->disposition_type)) {
            return "Disposition type is required.";
        }
        if (!preg_match("/^[0-9a-zA-Z]{1,4}$/", $service->disposition_id)) {
            return "Provide valid disposition code";
        }
        if (!preg_match("/^[0-9a-zA-Z_'&-\/ ]{1,50}$/", $service->title)) {
            return "Provide valid title";
        }

        if ($service->disposition_id != $dis_code) {
            $existing_code = $dc_model->getDispositionById($service->disposition_id);
            if (!empty($existing_code)) {
                return "Disposition code $service->disposition_id already exist";
            }
        }

        if (!empty($service->group_id) && !empty($service->group_title)) {
            return "Please select only one option for disposition group";
        }
        if (strlen($service->altr_disposition_id) > 10) {
            return "Maximum 10 character allowed for alternative disposition ID";
        }

        if (!empty($service->group_id)) {
            if (!array_key_exists($service->group_id, $groups)) {
                return "Invalid disposition group selected";
            }
        } elseif (!empty($service->group_title)) {
            if (in_array($service->group_title, $groups, true)) {
                return "Disposition group $service->group_title already exist";
            }
        }

        return '';
    }

    public function actionChangePartition()
    {
        include('model/MReportPartition.php');
        include('model/MSkillCrmTemplate.php');
        $partition_model = new MReportPartition();
        $template_model = new MSkillCrmTemplate();
        $request = $this->getRequest();

        $template_id = $request->getRequest('tid');
        $disposition_id = $request->getRequest('dcode');

        $mainobj = $template_model->getDispositionById($disposition_id, $template_id);

        $data['partitions'] = array("" => "All") + $partition_model->getPartitionsKeyValue();
        if ($request->isPost()) {
            $partition_id = $request->getRequest('partition_id');
            $mainobj->partition_id = $partition_id;

            if (array_key_exists($partition_id, $data['partitions'])) {
                if ($template_model->changePartition($mainobj, $partition_id)) {
                    AddInfo("Partition Successfully Updated");
                    $this->getTemplate()->display_popup_msg(array(), true);
                } else {
                    AddError("Failed to update partition");
                }
            } else {
                AddError("Invalid Partition Selected");
            }
        }

        $data['request'] = $request;
        $data['mainobj'] = $mainobj;
        $data['pageTitle'] = "Change Disposition's Partition";
        $this->getTemplate()->display_popup('skill_crm_disposition_change_form', $data, true);
    }


    public function actionChangeStatus()
    {
        include('model/MSkillCrmTemplate.php');
        $template_model = new MSkillCrmTemplate();
        $request = $this->getRequest();

        $disposition_id = $request->getRequest('dcode');
        $template_id = $request->getRequest('tid');

        $mainobj = $template_model->getDispositionById($disposition_id, $template_id);


        if ($request->isPost()) {
            $status = $request->getRequest('status');
            $mainobj->status = $status;

            if ($template_model->changeStatus($mainobj, $status)) {
                AddInfo("Status Successfully Updated");
                $this->getTemplate()->display_popup_msg(array(), true);
            } else {
                AddError("Failed to update Status");
            }
        }

        $data['statuses'] = ['Y'=>'Active', 'N'=>'Inactive'];
        $data['request'] = $request;
        $data['mainobj'] = $mainobj;
        $data['pageTitle'] = "Change Disposition's Status";
        $this->getTemplate()->display_popup('skill_crm_disposition_status_change', $data, true);
    }
}
