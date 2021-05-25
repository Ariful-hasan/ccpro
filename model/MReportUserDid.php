<?php
class MReportUserDid extends Model{
    var $user_id = '';
    var $did = '';

    function __construct() {
        parent::__construct();
    }
    function addDid($user_id, $did, $status)
    {
        if (empty($user_id)) return false;
        $sql = "INSERT INTO report_user_did SET ".
            "user_id='$user_id', ".
            "did='$did'";

        if ($this->getDB()->query($sql)) {
            $agent_title = "External User Did";
            $ltxt = $status=='Y' ? 'Active' : 'Inactive';
            $this->addToAuditLog($agent_title, 'A', $agent_title . "=".$user_id, $ltxt);
            return true;
        }
        return false;
    }

    function getAllReportUserDid()
    {
        $sql1 = "SELECT * user_id, did FROM report_user_did ";
        $cond = '';
        $cond1 = '';

        $result = $this->getDB()->query($sql1);

        $options = array();
        if (is_array($result)) {
            foreach ($result as $skill) {
                $options[$skill->user_id] = $skill->did;
            }
            return $options;
        }

        return $result;
    }
}