<?php

class ReportPartition extends Controller
{
	public function __construct()
    {
		parent::__construct();
	}

	public function init()
	{
        $data['pageTitle'] = 'Report Partitions';
        $data['dataUrl'] = $this->url('task=get-report-partition-data&act=get-partitions');
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'report_partitions';
        $this->getTemplate()->display('report_partitions', $data);
	}

	public function actionCreate()
	{
	    include('model/MReportPartition.php');
	    $partition_model = new MReportPartition();

	    $msg = "";
	    $msg_type = FALSE;

	    $mainobj = NULL;
	    $request = $this->getRequest();
	    if ($request->isPost())
        {
            $label = $request->getRequest('label','');
            $msg = $this->isValidatePartitionRequest($label);
            if (empty($msg))
            {
                if ($partition_model->savePartition($label))
                {
                    $msg_type = TRUE;
                    $msg = "New partition successfully created";
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
                else{
                    $msg = "Partition creation failed";
                }
            }
        }

        $data['isAutoResize'] = true;
        $data['msg'] = $msg;
        $data['msg_type'] = $msg_type;
        $data['pageTitle'] = 'Add Report Partition';
        $this->getTemplate()->display_popup('report_partitions_create', $data,true);
	}

    public function actionUpdate()
    {
        include('model/MReportPartition.php');
        $partition_model = new MReportPartition();

        $msg = "";
        $msg_type = FALSE;

        $request = $this->getRequest();
        $id = $request->getRequest('id');
        $mainobj = $partition_model->findByID($id);
        if ($request->isPost())
        {
            $label = $request->getRequest('label','');
            $msg = $this->isValidatePartitionRequest($label);
            if (empty($msg))
            {
                if ($partition_model->savePartition($label,$mainobj->partition_id))
                {
                    $msg_type = TRUE;
                    $msg = "New partition successfully Updated";
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
                else{
                    $msg = "Partition Update failed";
                }
            }
        }

        $data['msg'] = $msg;
        $data['msg_type'] = $msg_type;
        $data['pageTitle'] = 'Update Report Partition';
        $data['mainobj'] = $mainobj;
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_partitions_create', $data);
    }

	private function isValidatePartitionRequest($label='')
    {
        $label = trim($label);
        if (empty($label)) return "Lable is required";
        if (strlen($label) > 20 ) return "Maximum 20 characters are allowed";
        return "";
    }

    public function actionPartitionRecords()
    {
        $data['pageTitle'] = 'Partitions Records';
        $data['dataUrl'] = $this->url('task=get-report-partition-data&act=get-partition-records');
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_partition_records', $data);
    }

    public function actionAddPartitionRecords()
    {
        include('model/MSkill.php');
        include('model/MIvr.php');
        include('model/MReportPartition.php');
        $partition_model = new MReportPartition();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();

        $msg = "";
        $msg_type = FALSE;
        $partitions = $partition_model->getPartitionsKeyValue();
        $types = $partition_model->getPartitionRecordType();

        $mainobj = NULL;
        $request = $this->getRequest();
        $partition_id = $request->getRequest('pid','');

        if ($request->isPost())
        {
            $partition_id = $request->getRequest('partition_id','');
            $selected_skills = $request->getRequest('selected_skills',[]);
            $selected_ivrs = $request->getRequest('selected_ivrs',[]);
            $msg = $this->isValidatePartitionRecordRequest($partition_id,$selected_skills,$selected_ivrs,$partitions);
            if (empty($msg))
            {
                if ($partition_model->savePartitionRecord($partition_id, $selected_skills, $selected_ivrs))
                {
                    $msg_type = TRUE;
                    $msg = "Partition record created successfully";
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
                else{
                    $msg = "Partition record creation failed";
                }
            }
        }

        $data['exixting_skill_and_ivrs'] = $partition_model->getPartitionSkills($partition_id);
        $data['available_skills'] = $skill_model->getSkillsNamesArray();
        $data['available_ivrs'] = $ivr_model->getIvrOptions();
        $data['partitions'] = $partitions;
        $data['types'] = $types;
        $data['request'] = $request;
        $data['msg'] = $msg;
        $data['msg_type'] = $msg_type;
        $data['pageTitle'] = 'Add Report Partition Record';
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_partition_record_create', $data);
    }


    /* public function actionRecordUpdate()
    {
        include('model/MReportPartition.php');
        $partition_model = new MReportPartition();

        $msg = "";
        $msg_type = FALSE;
        $partitions = $partition_model->getPartitionsKeyValue();
        $types = $partition_model->getPartitionRecordType();
        $request = $this->getRequest();
        $id = $request->getRequest('id');
        $mainobj = $partition_model->findPartitionRecordByID($id);
        if ($request->isPost())
        {
            $partition_id = trim($request->getRequest('partition_id',''));
            $type = trim($request->getRequest('type',''));
            $msg = $this->isValidatePartitionRecordRequest($partition_id,$type,$partitions,$types);
            if (empty($msg))
            {
                if ($partition_model->savePartitionRecord($partition_id,$type,$mainobj->record_id))
                {
                    $msg_type = TRUE;
                    $msg = "New partition successfully Updated";
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&act=partition-records");
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
                else{
                    $msg = "Partition Update failed";
                }
            }
        }

        $data['partitions'] = $partitions;
        $data['types'] = $types;
        $data['msg'] = $msg;
        $data['msg_type'] = $msg_type;
        $data['pageTitle'] = 'Update Report Partition';
        $data['mainobj'] = $mainobj;
        $data['request'] = $request;
        $data['side_menu_index'] = 'reports';
        $this->getTemplate()->display('report_partition_record_create', $data);
    }
*/
    private function isValidatePartitionRecordRequest($partition_id,$selected_skills,$selected_ivrs,$partitions)
    {
        if (empty($partition_id)) return "Partition is required";
        if (!array_key_exists($partition_id,$partitions)) return "Invalid record Partition";
        if (empty($selected_skills) && empty($selected_ivrs)) return "Minimum one skill/ivr is required";

        return "";
    }

    public function actionChoose()
    {
        include('model/MReportPartition.php');
        $partition_model = new MReportPartition();

        $partitions = $partition_model->getPartitionsKeyValue();
        $partitions = array_merge(array("*" => "All"),$partitions);


        $request = $this->getRequest();

        if ($request->isPost())
        {
            $partition_id = $request->getRequest('partition_id','');
            if (array_key_exists($partition_id,$partitions)){
                $partition_id == "*" ? UserAuth::setPartition('','') : UserAuth::setPartition($partition_id,$partitions[$partition_id]) ;
                AddInfo("Partition Changed successfully");
            }else{
                AddError("Unknown partition");
            }
        }

        $data['mainobj'] =  UserAuth::getPartition();;
        $data['request'] = $request;
        $data['partitions'] = $partitions;
        $data['pageTitle'] = 'Set Report Partition';
        $this->getTemplate()->display_popup('report_partition_set', $data,TRUE);
    }


}
