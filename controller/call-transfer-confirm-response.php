<?php

class CallTransferConfirmResponse extends Controller
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

    
    public function actionUpdatePermission()
    {
        $response = false;
        $this->SetMessage('Failed to Change permission', false);

        if ($this->pro === "permission") {

            AddModel('MCallTransferSetting');
            $setting_model = new MCallTransferSetting();

            $user_type = isset($_REQUEST['user_type']) ? trim($_REQUEST['user_type']) : '';
            $target = isset($_REQUEST['target']) ? trim($_REQUEST['target']) : '';


            
            if (!($user_type && $target)) {
                $this->SetMessage('Failed to Change permission', false);
                $this->ReturnResponse();
            }

            $current_permission = $setting_model->getByRoleType($user_type);
            if (empty($current_permission)){
                $this->ReturnResponse();
            }
            $current_permission = array_shift($current_permission);
            if ($target=='agents'){
                $new_permission = $current_permission->agents == "Y" ? "N" : "Y";
                $response = $setting_model->updatePermissionToTransferCallAtAgents($user_type, $new_permission);
            }
            elseif($target=='supervisors'){
                $new_permission = $current_permission->supervisors == "Y" ? "N" : "Y";
                $response = $setting_model->updatePermissionToTransferCallAtSupervisors($user_type, $new_permission);
            }
            elseif($target=='skills'){
                $new_permission = $current_permission->skills == "Y" ? "N" : "Y";
                $response = $setting_model->updatePermissionToTransferCallAtSkills($user_type, $new_permission);
            } elseif($target=='ivrs'){
                $new_permission = $current_permission->ivrs == "Y" ? "N" : "Y";
                $response = $setting_model->updatePermissionToTransferCallAtIvrs($user_type, $new_permission);
            }

            if ($response){
                $this->SetMessage('Permission updated successfully!', true);
            }
        }
        $this->ReturnResponse();
    }

}
