<?php
class EvPortalAccess extends Controller
{
    function __construct() {
        parent::__construct();
    }

    function init()
    {
        $this->actionEvaluationPortalAccess();
    }

    function actionEvaluationPortalAccess()
    {
        include_once('model/MRole.php');
        $role_model = new MRole();

        $roles = $role_model->getRoles();
        $role_list = array();
        foreach ($roles as $role) {
            $role_list["$role->id"] = $role->name;
        }

        $data['pageTitle'] = 'Evaluation Portal Access';
        $data['role_list'] = $role_list;
        $data['dataUrl'] = $this->url('task=get-tools-data&act=evaluation-portal-access');
        $this->getTemplate()->display('evaluation_portal_access', $data);
    }

    function actionUpdateAccess()
    {
        include_once('model/MEvAccess.php');

        $ev_portal_access_model = new MEvAccess();

        $data = array();
        $privilege_list = ev_access_list();
        $agent_id = $this->request->getRequest('agent_id');

        if ($this->getRequest()->isPost()) {
            $access_list = $this->request->getRequest('privileges');
            if ($this->hasAllPrivileges($privilege_list, $access_list)) {
                $access_list = "*";
            } else {
                if(!empty($access_list)){
                    $access_list = implode(",", $access_list);
                }else{
                    $access_list = "";
                }
            }
            if ($ev_portal_access_model->hasEvPortalAccess($agent_id) != null) {
                if ($ev_portal_access_model->updateEvPortalAccess($agent_id, $access_list)) {
                    $data["errMsg"] = "EV Portal Access Updated Successfully!";
                } else {
                    $data["errType"] = 0;
                    $data["errMsg"] = "EV Portal Access Update Failed!";
                }
            } else {
                if ($ev_portal_access_model->insertEvPortalAccess($agent_id, $access_list)) {
                    $data["errMsg"] = "EV Portal Access Updated Successfully!";
                } else {
                    $data["errType"] = 0;
                    $data["errMsg"] = "EV Portal Access Update Failed!";
                }
            }
        }

        $agent_access_list = $ev_portal_access_model->getEvPortalAccess($agent_id);
        $privileges = array();

        foreach ($privilege_list as $privilege_id => $privilege_name) {
            $privilege_data = new stdClass();
            $privilege_data->privilege_id = $privilege_id;
            $privilege_data->privilege_name = $privilege_name;
            if ($this->hasPrivilege($privilege_id, $agent_access_list)) {
                $privilege_data->checked = true;
            } else {
                $privilege_data->checked = false;
            }
            array_push($privileges, $privilege_data);
        }

        $data['pageTitle'] = 'Update Evaluation Portal Access';
        $data['agentAccessList'] = $agent_access_list;
        $data['privileges'] = $privileges;
        $data['agent_id'] = $agent_id;
        $this->getTemplate()->display_popup('update_portal_access', $data, true);
    }

    private function hasPrivilege($privilege_id, $agent_privileges)
    {
        foreach ($agent_privileges as $agent_privilege) {
            if ($agent_privilege->privileges == "*") {
                return true;
            }
            if (strpos($agent_privilege->privileges, $privilege_id) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasAllPrivileges($privilege_list, $agent_access_list)
    {
        $hasAllPrivileges = true;
        foreach ($privilege_list as $privilege_id => $privilege_name) {
            if (!in_array($privilege_id, $agent_access_list)) {
                return false;
            }
        }
        return $hasAllPrivileges;
    }
}