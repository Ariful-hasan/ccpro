<?php

class MEvAccess extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getEvPortalAccess($agent_id)
    {
        $sql = "Select privileges FROM ev_agent_privileges WHERE agent_id = '$agent_id'";
        $result = $this->getDB()->query($sql);
        return $result;
    }

    function hasEvPortalAccess($agent_id)
    {
        $sql = "Select * FROM ev_agent_privileges WHERE agent_id = '$agent_id'";
        $result = $this->getDB()->query($sql);
        return $result;
    }

    function updateEvPortalAccess($agent_id, $access_list)
    {
        $updated_at = date("Y-m-d H:i:s");
        $sql = "UPDATE ev_agent_privileges SET `privileges` = '$access_list', updated_at = '$updated_at' ";
        $sql .= "WHERE agent_id = '$agent_id' LIMIT 1";
        $result = $this->getDB()->query($sql);
        return $result;
    }

    function insertEvPortalAccess($agent_id, $access_list)
    {
        $id = time();
        $status = 'A';
        $created_at = date("Y-m-d H:i:s");
        $updated_at = null;
        $sql = "INSERT INTO ev_agent_privileges VALUES ('$id','$agent_id','$access_list','$status', '$created_at','$updated_at')";
        $result = $this->getDB()->query($sql);
        return $result;
    }

}



