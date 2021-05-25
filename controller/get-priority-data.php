<?php
require_once 'BaseTableDataController.php';
class GetPriorityData extends BaseTableDataController
{
    function __construct()
    {
        parent::__construct();
    }

    public function actionAgentSkills()
    {

        AddModel('MSkillPriority');

        $priority_model = new MSkillPriority();

        $agent_id = '';
        $skill_id = '';

        if ($this->gridRequest->isMultisearch){
            $agent_id = $this->gridRequest->getMultiParam('agent_id','');
            $skill_id = $this->gridRequest->getMultiParam('skill_id','');
        }


        $this->pagination->num_records = $priority_model->totalAgentPriorityRecord($agent_id, $skill_id);

        $result = $this->pagination->num_records > 0 ?
            $priority_model->getAgentPrioritySkills($agent_id, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $res = [];

        if(is_array($result) && count($result) > 0)
        {
            foreach ( $result as $data )
            {
                if (empty($res[$data->agent_id])){
                    $res[$data->agent_id] = new stdClass();
                    $res[$data->agent_id]->agent_id = $data->agent_id;
                    $res[$data->agent_id]->skill_priority = '';

                    if(UserAuth::getRoleID() == 'R'){
                        $res[$data->agent_id]->action = "<a class='btn btn-success btn-xs' href='".$this->url('task=priority&act=agent-skill&agent_id='.$data->agent_id)."'><i class='fa fa-save'></i> Update</a>";
                        $res[$data->agent_id]->action .= " <a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ?' href=' ".$this->url('task=priority-confirm-response&act=agent-skill-priority-delete&pro=del&agent_id='.$data->agent_id)."'><i class='fa fa-trash'></i> Delete</a>";
                    }
                }
                $res[$data->agent_id]->skill_priority .= !empty($res[$data->skill_priority]) ? ', '. $data->skill_priority : $data->skill_priority ;
            }
        }

        $responce->rowdata = array_values($res);
        //$responce->rowdata = $result;

        $this->ShowTableResponse();
    }
}
