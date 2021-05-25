<?php

class MCallTransferSetting extends Model
{

    public function numCallTransferSetting()
    {
        $sql = "SELECT COUNT(*) AS numrows FROM call_transfer_config";
        $result = $this->getDB()->query($sql);

        return !empty($result[0]->numrows) ?  $result[0]->numrows : 0;
    }

    public function getCallTransferSetting($offset=0, $perPage=20)
    {
        $sql = "SELECT * FROM call_transfer_config";
        $sql .=  $perPage > 0 ? " LIMIT $perPage OFFSET $offset" : "";
        return $this->getDB()->query($sql);
    }

    public function getByRoleType($agent_role='A')
    {
        return $this->getDB()->query("SELECT * FROM call_transfer_config WHERE user_type = '{$agent_role}' LIMIT 1");
    }

    public function updatePermissionToTransferCallAtAgents($agent_role,$permission='N')
    {
        return $this->getDB()->query("UPDATE call_transfer_config SET agents='{$permission}' WHERE user_type = '{$agent_role}' LIMIT 1");
    }

    public function updatePermissionToTransferCallAtSupervisors($agent_role, $permission='N')
    {
        return $this->getDB()->query("UPDATE call_transfer_config SET  supervisors='{$permission}' WHERE user_type = '{$agent_role}' LIMIT 1");
    }

    public function updatePermissionToTransferCallAtSkills($agent_role, $permission='N')
    {
        return $this->getDB()->query("UPDATE call_transfer_config SET  skills='{$permission}' WHERE user_type = '{$agent_role}' LIMIT 1");
    }

    public function updatePermissionToTransferCallAtIvrs($agent_role, $permission='N')
    {
        return $this->getDB()->query("UPDATE call_transfer_config SET  ivrs='{$permission}' WHERE user_type = '{$agent_role}' LIMIT 1");
    }

}
