<?php

class Priority extends Controller
{
	function __construct()
    {
		parent::__construct();
	}

    public function actionAgentPriorities()
    {
        AddModel('MAgent');
        AddModel('MSkill');

        $agent_model = new MAgent();
        $skill_model = new MSkill();

        $agents =  $agent_model->get_as_key_value();
        unset($agents['root']);

        $skills = ['*' => 'All'] + $skill_model->getAllSkillOptions('Y','array');


        $this->setPageTitle('Agent Skill Priority List');
        if(UserAuth::getRoleID() == 'R'){
            $this->setTopMenu('task=priority&act=agent-skill', 'fa fa-plus','Add Priority');
        }
        $this->setSideMenuIndex('priority');
        $this->setGridDataUrl($this->url("task=get-priority-data&act=agent-skills"));
        $this->setViewData('agents',$agents);
        $this->setViewData('skills', $skills);
        $this->display('agent_skill_priority_list');
	}


    function actionAgentSkill()
    {
        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MSkillPriority');

	    $agent_model = new MAgent();
	    $skill_model = new MSkill();
	    $skill_priority_model = new MSkillPriority();

	    $skills = $skill_model->getSkillsNamesArray();
	    $this->setPageTitle("Agent Priority");
        $request = $this->getRequest();
        $not_found_skill_names = '';
        $found_skill_names = [];
        $isUpdated = false;
        $agent_id = $this->getRequest()->getRequest('agent_id','');
        $this->setViewData('skill_priority', '');
        if (!empty($agent_id)){
            $this->setViewData('skill_priority', $skill_priority_model->getAgentPrioritySkills($agent_id));
        }

        if($request->isPost()){
            $skills = array_flip($skills);
            $agent_id = $request->getRequest('agent_id');
            $agent_new_skills = $request->getRequest('skill_priority');


            $this->setViewData('msg',"Skill Priority Assigned was Unsuccessful.");
            $this->setViewData('msgType', false);

            if (empty($agent_id) || $agent_id == '*'){
                $this->setViewData('msg',"Agent is required.");
                $this->setViewData('msgType', false);
            }

            if (empty($agent_new_skills)){
                $this->setViewData('msg',"Skill priority is required.");
                $this->setViewData('msgType', false);
            }



            $agent_new_skills = explode(',', $agent_new_skills);

             if (!empty($agent_id) && $agent_id != '*' && !empty($agent_new_skills)){

                 foreach ($agent_new_skills as $agent_new_skill){
                     list($skill_name, $skill_priority) = explode(':', $agent_new_skill);
                     $skill_name = trim($skill_name);
                     !empty($skills[$skill_name]) ? $found_skill_names[$skills[$skill_name]] = $skill_priority : $not_found_skill_names .= $skill_name.', ';
                 }


                 $this->setViewData('error', substr(trim($not_found_skill_names),0,-1));

                 if (empty($not_found_skill_names) && !empty($found_skill_names)){
                     $agent_previous_skill = $skill_priority_model->getAgentSkillPriority($agent_id);

                     if (array_diff_assoc($found_skill_names, $agent_previous_skill) || array_diff_assoc($agent_previous_skill, $found_skill_names)){
                        $isUpdated = $skill_priority_model->assignToAgent($agent_id, $found_skill_names);
                     }else{
                         $this->setViewData('msg', "No Change Found!");
                     }



                 }

             }

             if ($isUpdated){
                 $this->setViewData('msg', "Skill Priority Assigned Successfully.");
                 $this->setViewData('msgType', true);
                 $url = $this->getTemplate()->url("task=priority&act=agent-priorities");
                 $this->setViewData('metaText', "<META HTTP-EQUIV='refresh' CONTENT='2;URL=$url'>");
             }
        }

        $this->setTopMenu('task=priority&act=agent-priorities','fa fa-list','Agent Priority List');
	    $this->setViewData('request', $request);
	    $this->setViewData('agent', $agent_id);
	    $this->setViewData('agents', $agent_model->get_as_key_value());
	    $this->display('agent_priority');

	}

    public function actionGetAgentSkillPriority()
    {
        AddModel('MSkillPriority');

        $skill_priority_model = new MSkillPriority();

        $agent_id = $this->getRequest()->getRequest('agent_id','');

        $response = $skill_priority_model->getAgentPrioritySkills($agent_id);
        $result = new stdClass();
        $result->skill_priority = '';

        if (is_array($response)){
            foreach ($response as $priority){
                $result->agent_id = $priority->agent_id;
                $result->skill_priority .= !empty($result->skill_priority) ? ', '.$priority->skill_priority : $priority->skill_priority;
            }
        }

        return !empty($result) ? die(json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR)) : null;

	}

}
