<?php

class MReportUser extends Model
{
    var $user_id = '';
    var $login_id = '';
    var $name = '';
    var $password = '';
    var $status = '';
    
    function __construct() {
        parent::__construct();
    }

    function notifyAgentUpdate($sync_type, $aid, $file='')
    {
        require_once('lib/DBNotify.php');
        DBNotify::NotifyAgentUpdate(UserAuth::getDBSuffix(), $aid, $this->getDB());
    }

    function isAgentExist($user)
    {
        $sql = "SELECT login_id,name FROM report_user WHERE user_id='$user'";
        $result = $this->getDB()->query($sql);

        if ($this->getDB()->getNumRows() == 1) {
            $result = $this->getDB()->query($sql);
            //Password validation rules apply...
            $this->setPasswordRules();
            return $result[0];
        }

        return null;
    }

    function setPasswordRules() {
        $sql = "SELECT rule_no, value, status FROM password_settings WHERE rule_no IN ('8', '9', '10', '11')";
        $passData = $this->getDB()->query($sql);
        if (count($passData) > 0){
            foreach ($passData as $rule) {
                if ($rule->status == 'Y'){
                    if ($rule->rule_no == '8'){
                        $this->passExpireDays = $rule->value;
                    }
                    if ($rule->rule_no == '9'){
                        $this->expireMsgDays = $rule->value;
                    }
                    if ($rule->rule_no == '10'){
                        $this->passRepeatNo = $rule->value;
                    }
                    if ($rule->rule_no == '11'){
                        $this->loginAttempts = $rule->value;
                    }
                }
            }
        }
    }

    function resetAgentPassword($user)
    {
        if (empty($user)) return false;

        $password = "";
        $numberchar = "135798642";
        $capletter = "ABCDEFGHMNKPTRVX";
        $smallletter = "zypqwjstuabgh";
        $alt = time () % 2;
        for($i = 0; $i < 8; $i ++) {
            if ($alt == 1) {
                $password .= $capletter [(rand () % strlen ( $capletter ))];
                $alt = 2;
            } elseif($alt == 2) {
                $password .= $numberchar [(rand () % strlen ( $numberchar ))];
                $alt = 0;
            }else {
                $password .= $smallletter [(rand () % strlen ( $smallletter ))];
                $alt = 1;
            }
        }

        //$pass_md5 = md5($password);
        $concatPass = $user . $password;
        $pass_md5 = md5($concatPass);
        $sql = "UPDATE report_user SET password = SHA2(CONCAT(login_id, '$pass_md5'), 256) WHERE login_id='$user'";
        if ($this->getDB()->query($sql)){
            $this->addToAuditLog('Report User Password', 'U', "User=$user", "Password reset");
            return $password;
        }else {
            return false;
        }
    }

    function deleteExtUser($agentid)
    {
        //$agentinfo = $this->getAgentById($agentid);
        if (!empty($agentid)) {
            $sql = "DELETE FROM report_user WHERE user_id='$agentid' LIMIT 1";
            if ($this->getDB()->query($sql)) {
                $sql = "DELETE FROM report_user_did WHERE user_id='$agentid'";
                $this->getDB()->query($sql);

                $this->addToAuditLog("Ext User", 'D', "$agentid", "");
                return true;
            }
        }
        return false;
    }

    function updateUserStatus($agentid, $status='', $agent_title='')
    {
        if (empty($agentid)) return false;
        if ($status=='A' || $status=='L') {
            $sql = "UPDATE report_user SET status='$status' WHERE user_id='$agentid' LIMIT 1";
            if ($this->getDB()->query($sql)) {

                $ltxt = $status=='A' ? 'Inactive to Active' : 'Active to Inactive';
                $this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "Status=".$ltxt);
                return true;
            }
        }
        return false;
    }

    function numExternals($user_id='', $did='', $status='', $nick_name='')
    {
        $cond = '';
        $sql = "SELECT COUNT(user_id) AS numrows FROM report_user ";
        if (!empty($user_id)) $cond .= "login_id LIKE '%$user_id'";
        //if (!empty($did)) $cond = $this->getAndCondition($cond, "did LIKE '$did%'");
        if (!empty($nick_name)) $cond = $this->getAndCondition($cond, "name LIKE '%$nick_name%'");
        if (!empty($status)) $cond = $this->getAndCondition($cond, "status='$status'");

        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    function getExternals($user_id='', $did='', $status='', $nick_name='', $offset=0, $limit=0){
        $cond = '';
        $sql = "SELECT * FROM report_user ";
        if (!empty($user_id)) $cond .= "login_id LIKE '%$user_id'";
        //if (!empty($did)) $cond = $this->getAndCondition($cond, "did LIKE '$did%'");
        if (!empty($nick_name)) $cond = $this->getAndCondition($cond, "name LIKE '%$nick_name%'");
        if (!empty($status)) $cond = $this->getAndCondition($cond, "status='$status'");

        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY login_id ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getExt($usertype='', $user_id='', $did='', $status='', $offset=0, $limit=0, $nick_name='')
    {

        $cond = '';
        $sql = "SELECT * FROM report_user";
        if (!empty($user_id)) $cond .= "user_id='$user_id'";
        if (!empty($did)) $cond = $this->getAndCondition($cond, "did LIKE '$did%'");
        //if (!empty($usertype)) $cond = $this->getAndCondition($cond, "usertype='$usertype'");
        //if (!empty($usertype)) {$cond = $this->getAndCondition($cond, "usertype='$usertype'");}
        else{
            //$cond = $this->getAndCondition($cond, "usertype in ('A','S')");
        }
        if (!empty($status)) $cond = $this->getAndCondition($cond, "status='$status'");

        /*$sound_nick = !empty($nick_name) ? explode(" ", $nick_name) : "";
        $sound_nick[0] = !empty($sound_nick[0]) ? preg_replace("/[^A-Za-z0-9]/", "", $sound_nick[0]) : "";
        $soundNick = !empty($sound_nick[0]) ? soundex($sound_nick[0]) : "";*/

        //if (!empty($nick_name)) $cond = $this->getAndCondition($cond, "nick LIKE '%$nick_name' OR nick_sound='$soundNick'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY user_id ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getExtUserById($user_id)
    {
        $sql = "SELECT * FROM report_user WHERE user_id='$user_id'";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }
    function getExtUserByLoginId($login_id)
    {
        $sql = "SELECT * FROM report_user WHERE login_id='$login_id'";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getUserDid($user_id='', $skill_id='')
    {
        $cond = '';
        $sql = "SELECT * FROM report_user_did ";
        if (!empty($user_id)) $cond = "user_id='$user_id'";
        //if (!empty($skill_id)) $cond = $this->getAndCondition($cond, "skill_id='$skill_id'");
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY user_id ASC ";
        return $this->getDB()->query($sql);
    }
    function getAllDid()
    {
        $sql = "SELECT * FROM did ";
        if (!empty($user_id)) $cond = "user_id='$user_id'";
        return $this->getDB()->query($sql);
    }

    function getNextId($fld_id, $tbl_name, $min_id , $max_id)
    {
        $id = '';
        $sql = "SELECT MAX($fld_id) AS max_id FROM $tbl_name";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            if (!empty($result[0]->max_id)) $id = $result[0]->max_id;
        }
        if (empty($id)) return $min_id;
        if ($id == $max_id) return '';
        $id++;
        return $id;
    }
    function addExt($extuser)
    {
        $extuser->user_id = $this->getNextId('user_id','report_user','AAAA','ZZZZ');
        if (empty($extuser->user_id)) return false;

        $concatPass = $extuser->login_id . $extuser->password;
        $pass_md5 = md5($concatPass);

        $sql = "INSERT INTO report_user SET ".
            "user_id='$extuser->user_id', ".
            "login_id='$extuser->login_id', ".
            "password=SHA2(CONCAT('$extuser->login_id', '$pass_md5'), 256), ".
            "name='$extuser->name', ".
            "status='A'";

        if ($this->getDB()->query($sql)) {
            $this->addUserDid($extuser->user_id, $extuser->did);
            $agent_title = "External";
            //$agent_title = $agent->usertype == 'S' ? 'Supervisor' : 'Agent';
            $ltxt = $extuser->status=='Y' ? 'Active' : 'Inactive';
            $this->addToAuditLog($agent_title, 'A', $agent_title . "=".$extuser->user_id, $ltxt);
            return true;
        }
        return false;
    }



    function addUserDid($user_id, $did)
    {
        $is_update = false;
        if (!empty($user_id) && !empty($did)){
            foreach ($did as $key => $value){
                $sql = "INSERT INTO report_user_did SET user_id='$user_id', did='$value'";
                if ($this->getDB()->query($sql)) $is_update = true;
            }
        }
        return $is_update;
    }

    function updateUserDid($user_id, $did)
    {
        $is_update = false;
        if (!empty($user_id) && !empty($did)){
            foreach ($did as $item){
                $sql = "Update report_user_did SET did='$item->did' WHERE user_id='$user_id' LIMIT 1";
                if ($this->getDB()->query($sql)) $is_update = true;
            }
        }
        return $is_update;
    }

    function updateAgentStatus($agentid, $status='', $utype='A', $agent_title='')
    {
        if (empty($agentid)) return false;
        if ($status=='Y' || $status=='N') {
            $sql = "UPDATE agents SET active='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
            if ($this->getDB()->query($sql)) {

                $ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
                $this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "Status=".$ltxt);
                return true;
            }
        }
        return false;
    }

    function updateExt($oldagent, $extuser)
    {
        if (empty($oldagent->user_id)) return false;
        $is_update = false;
        $isNotifyNeeded = false;
        $changed_fields = '';
        $ltext = '';
        $isDidUpdate = false;
        $difference_result = '';

        if ($extuser->name != $oldagent->name) {
            if (!empty($changed_fields)) $changed_fields .= ', ';
            $changed_fields .= "name='$extuser->name'";
            $ltext = $this->addAuditText($ltext, "Name=$oldagent->name to $extuser->name");
        }


        if (count($oldagent->did) == count($extuser->did)){
            $difference_result = array_diff( $extuser->did, $oldagent->did);
        }else {
            $difference_result = "change";
        }


        if (!empty($changed_fields)) {
            $sql = "UPDATE report_user SET $changed_fields WHERE user_id='$oldagent->user_id'";
            $is_update = $this->getDB()->query($sql);
        }

        if (!empty($difference_result)) {
            $sql = "DELETE FROM report_user_did WHERE user_id = '$oldagent->user_id';";
            $this->getDB()->query($sql);
            if (empty($extuser->did)){
                $is_update = true;
            }else {
                $is_update = $this->addUserDid($oldagent->user_id,$extuser->did);
            }
        }
        
        if ($is_update) {
            $agent_title = "External User";
            /*if ($isNotifyNeeded) {
                $this->notifyAgentUpdate('UPD_AGENT', $oldagent->agent_id);
            }*/
            $this->addToAuditLog($agent_title, 'U', $agent_title . "=".$oldagent->user_id, $ltext);
        }

        return $is_update;
    }
}

?>