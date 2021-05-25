<?php

class PriorityConfirmResponse extends Controller
{
    /**
     * @var AjaxConfirmResponse
     */
    private $response;
    public $pro;
    
    public function __construct()
    {
        parent::__construct();

        $this->response = new AjaxConfirmResponse();
        $this->pro = $this->request->getGet("pro");
        if (empty($this->pro)) {
            $this->SetMessage("Parameter Error", false);
            $this->ReturnResponse();
        }
    }

    public function init()
    {
        $this->SetMessage("Method doesn't exists", false);
        $this->ReturnResponse();
    }

    public function ReturnResponse()
    {
        die(json_encode($this->response));
    }

    public function SetMessage($msg, $isSuccess=false)
    {
        $this->response->status=$isSuccess;
        $this->response->msg=$msg;
    }

    
    public function actionAgentSkillPriorityDelete()
    {
        if ($this->pro=="del") {
            AddModel('MSkillPriority');
            $priority_model = new MSkillPriority();
            $agent_id = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';

            $this->SetMessage('Failed to Delete Agent Skill Priority', false);
            
            if ($priority_model->deleteAgentPriority($agent_id)) {
                $this->SetMessage('Skill Priority Deleted Successfully', true);
            }
        }
        $this->ReturnResponse();
    }

}
