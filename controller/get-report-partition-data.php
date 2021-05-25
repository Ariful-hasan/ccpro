<?php
require_once 'BaseTableDataController.php';
class GetReportPartitionData extends BaseTableDataController
{
    function __construct()
    {
        parent::__construct();
    }

    function actionGetPartitions()
    {

        include('model/MReportPartition.php');
        include('model/MSkill.php');
        include('model/MIvr.php');
        $report_partition_model = new MReportPartition();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();

        $this->pagination->num_records = $report_partition_model->numPartitions();

        $result = $this->pagination->num_records > 0 ?
            $report_partition_model->getPartitions($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $skills = $skill_model->getSkillsNamesArray();
            $ivrs = $ivr_model->getIvrOptions();
            foreach ( $result as &$data ) {
                $partition_skills = $report_partition_model->getPartitionSkills($data->partition_id);
                $partition_skill_length = count($partition_skills);
                $skills_and_ivrs = "";
                $i = 0;
                foreach ($partition_skills as $partition_skill)
                {
                    if ($partition_skill->type === "SQ" && !empty($skills[$partition_skill->record_id])){
                        $skills_and_ivrs .= $skills[$partition_skill->record_id];
                    }else if ($partition_skill->type === "IV" && !empty($ivrs[$partition_skill->record_id])){
                        $skills_and_ivrs .= $ivrs[$partition_skill->record_id];
                    }
                    $skills_and_ivrs .= (++$i == $partition_skill_length) ? " " : ", ";
                }

                $skills_and_ivrs = !empty($skills_and_ivrs) ? $skills_and_ivrs : "<i title='Add Skill to this partition' class='fa fa-plus'>";



                $data->skill = "<a class='' href='".$this->url("task=report-partition&act=add-partition-records&pid=".$data->partition_id)."'> $skills_and_ivrs </a>";
                $data->action = "<a class='btn btn-primary btn-xs' href='".$this->url("task=report-partition&act=update&id=".$data->partition_id)."'> <i class='fa fa-edit'></i> Edit</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionGetPartitionRecords()
    {

        include('model/MReportPartition.php');
        $report_partition_model = new MReportPartition();

        $this->pagination->num_records = $report_partition_model->numPartitionRecords();

        $result = $this->pagination->num_records > 0 ?
            $report_partition_model->getPartitionRecords($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if(count($result) > 0){
            $partitions = $report_partition_model->getPartitionsKeyValue();
            $partition_types = $report_partition_model->getPartitionRecordType();
            foreach ( $result as &$data ) {
                $data->partition_id = !empty($partitions[$data->partition_id]) ? $partitions[$data->partition_id] : $data->partition_id;
                $data->type = !empty($partition_types[$data->type]) ? $partition_types[$data->type] : $data->type;
                $data->action = "<a class='btn btn-primary btn-xs' href='".$this->url("task=report-partition&act=record-update&id=".$data->record_id)."'> <i class='fa fa-edit'></i>Edit</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

}
