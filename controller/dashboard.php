<?php

class Dashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function ActionSummary()
    {
        $request = $this->getRequest();
        $r = $request->getRequest('r', '');
        $dashboard_skills = "CATEGORY_". $r ."_SKILLS";

        if (empty($r) || !ctype_digit($r) || !defined($dashboard_skills)) {
            die("Invalid Request");
        }
        AddModel('MAgent');
        AddModel('MSkill');
        AddModel('MSetting');
        AddModel('MSeat');

        $agent_model = new MAgent();
        $skill_model = new MSkill();
        $setting_model = new MSetting();
        $seat_model = new MSeat();

        $agents_with_skillSet = [];

        $agents = $agent_model->getAgentSkill();

        foreach ($agents as $agent) {
            if (empty($agents_with_skillSet[$agent->agent_id])) {
                $agents_with_skillSet[$agent->agent_id] = new stdClass();
                $agents_with_skillSet[$agent->agent_id]->agent_id = $agent->agent_id;
                $agents_with_skillSet[$agent->agent_id]->skillSet = [];
            }
            $agents_with_skillSet[$agent->agent_id]->skillSet[] =  $agent->skill_id;
        }

        $aux_messages = $setting_model->getBusyMessages();
        $aux_messages[] = $this->getExtraAuxCodeHavingId25();

        $data['dashboard_title'] = 'Dashboard '. $r;
        $data['pageTitle'] = 'gPlex Contact Center Dashboard';
        $data['request'] = $this->getRequest();
        $data['skills'] = $skill_model->getAllSkillOptionsShort('Y');
        $data['active_seats'] = $seat_model->getSeatIdLabelAsKeyValue();
        $data['CCSettings'] = $setting_model->getCCSettings();
        $data['aux_messages'] = $aux_messages;
        $data['account_info'] = $agent_model->getCCAccountInfo(UserAuth::getDBSuffix());
        $data['dashboard_skills'] = constant($dashboard_skills);
        $data['dashboard_agent_with_skill_set'] = $agents_with_skillSet;
        $this->getTemplate()->display_only('dashboard_summary', $data);
    }



    protected function getExtraAuxCodeHavingId25()
    {
        $aux_code25 = new stdClass();
        $aux_code25->aux_code = "25";
        $aux_code25->aux_type = "I";
        $aux_code25->message = "Just Logged In, Not Ready Yet";
        $aux_code25->active = "Y";

        return $aux_code25;
    }
}
