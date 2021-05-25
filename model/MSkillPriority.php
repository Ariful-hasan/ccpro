<?php

class MSkillPriority extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function assignToAgent($agent_id, $skill_priority = [])
    {
        $status = false;
        $values = '';

       $this->getDB()->query("DELETE FROM agent_skill_priority WHERE agent_id='{$agent_id}'");

        if (!empty($skill_priority) && is_array($skill_priority)){
            foreach ($skill_priority as $skill_id => $priority){
                try{
                    $id = $this->generateUniqId(10);
                }catch (Exception $exception){
                   return false;
                }

                $values .= "('{$id}','{$agent_id}', '{$skill_id}', '$priority'), ";
            }
        }

        $values = !empty($values) ? substr(trim($values),0,-1) : $values;

        $sql = "INSERT INTO agent_skill_priority (priority_id, agent_id, skill_id, priority) VALUES {$values}";
        if ($this->getDB()->query($sql)) {
            $this->addToAuditLog('Skill', 'A', "Agent = ".$agent_id, 'agent added in the skill');
            $status = true;
        }

        return $status;
    }

    public function totalAgentPriorityRecord($agent_id = '', $skill_id='')
    {
        $sql = "select count(distinct agent_id) as totalRecord from agent_skill_priority";
        $sql .= !empty($agent_id) ? " WHERE  agent_id = '{$agent_id}' " : "";

        if($skill_id){
            $sql .= !empty($agent_id) && stripos($sql, 'WHERE') > -1 ? " AND " : "  WHERE ";
           $sql .= " skill_id = '{$skill_id}' ";
        }


        $result = $this->getDB()->query($sql);

        return !empty($result[0]->totalRecord) ? $result[0]->totalRecord : 0;
    }

  /*  public function getAgentPrioritySkills($agent_id, $skill_id='', $offset = 0, $limit = 20)
    {

       $sql = "SELECT asp.agent_id, GROUP_CONCAT(CONCAT(s.skill_name,':',asp.priority) ORDER BY asp.priority ASC SEPARATOR ', ') AS skill_priority";
       $sql .= " FROM agent_skill_priority asp INNER JOIN skill s ON asp.skill_id = s.skill_id ";
       $sql .= !empty($agent_id) ? " WHERE  asp.agent_id = '{$agent_id}' " : "";
        if($skill_id){
            $sql .= !empty($agent_id) && stripos($sql, 'WHERE') > -1 ? " AND " : "  WHERE ";
            $sql .= " asp.skill_id = '{$skill_id}' ";
        }

       $sql .= empty($agent_id) ? " GROUP BY asp.agent_id " : "";
       $sql .= " ORDER BY asp.priority ASC LIMIT {$limit} OFFSET {$offset} ";

       return $this->getDB()->query($sql);
    } */

    public function getAgentPrioritySkills($agent_id, $skill_id='', $offset = 0, $limit = 20)
    {
        $res = [];

       $sql = "SELECT asp.agent_id, CONCAT(s.skill_name,':',asp.priority) AS skill_priority";
       $sql .= " FROM agent_skill_priority asp INNER JOIN skill s ON asp.skill_id = s.skill_id ";
       $sql .= !empty($agent_id) ? " WHERE  asp.agent_id = '{$agent_id}' " : "";
        if($skill_id){
            $sql .= !empty($agent_id) && stripos($sql, 'WHERE') > -1 ? " AND " : "  WHERE ";
            $sql .= " asp.skill_id = '{$skill_id}' ";
        }

       //$sql .= empty($agent_id) ? " GROUP BY asp.agent_id " : "";
       $sql .= " ORDER BY asp.agent_id ASC, asp.priority ASC ";

       $response = $this->getDB()->query($sql);

       if (is_array($response)){
           foreach ($response  as $data){
               if (empty($res[$data->agent_id])){
                   $res[$data->agent_id] = new stdClass();
                   $res[$data->agent_id]->agent_id = $data->agent_id;
                   $res[$data->agent_id]->skill_priority = '';
               }
               $res[$data->agent_id]->skill_priority .= !empty($res[$data->agent_id]->skill_priority) ? ', '. $data->skill_priority : $data->skill_priority;
           }
       }

       return $res;
    }

    /**
     * @param int $length
     * @return bool|string
     * @throws Exception
     */
    function generateUniqId($length = 13) {

        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $length);
    }

    public function getAgentSkillPriority($agent_id)
    {
        $result = [];

        $skills = $this->getDB()->query("SELECT skill_id, priority FROM agent_skill_priority WHERE agent_id = '{$agent_id}' order by priority");

        if (!empty($skills)){
            foreach ($skills as $skill ){
                $result[$skill->skill_id] = $skill->priority;
            }
        }

        return $result;

    }

    public function deleteAgentPriority($agent_id)
    {
        if (empty($agent_id) || strlen($agent_id) !== 4){
            return false;
        }
        return $this->getDB()->query("DELETE FROM agent_skill_priority WHERE agent_id = '{$agent_id}'");
    }

}
