<?php

class MReportPartition extends Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function getPartitionRecordType()
    {
        return [
            'IV' => 'IVR',
            'SQ' => 'Skill'
        ];
    }

    public function numPartitions()
    {
        $response = $this->getDB()->query("SELECT COUNT(*) AS total_record FROM report_partition");
        return $response[0]->total_record;
    }

    public function getPartitions($offset = 0, $limit = 20)
    {
        $query = "SELECT * FROM report_partition ORDER BY partition_id ASC LIMIT {$offset}, {$limit} ";
        return $this->getDB()->query($query);
    }

    public function getPartitionsKeyValue($type = '')
    {
        $partitions = [];
        $result = $this->getPartitions(0, 26);

        if (empty($result)){
            return $partitions;
        }

        foreach ($result as $partition)
        {
            if ( ! empty($type) && $type == $partition->type)
            {
                $partitions[$partition->partition_id] = $partition->label;
            } else
            {
                $partitions[$partition->partition_id] = $partition->label;
            }
        }

        return $partitions;
    }

    public function numPartitionRecords()
    {
        $response = $this->getDB()->query("SELECT COUNT(*) AS total_record FROM partition_record");
        return $response[0]->total_record;
    }

    public function getPartitionRecords($offset = 0, $limit = 20)
    {
        $query = "SELECT * FROM partition_record ORDER BY partition_id ASC LIMIT {$offset}, {$limit} ";
        return $this->getDB()->query($query);
    }

    public function savePartition($label, $id = '')
    {
        if ( ! empty($id))
        {
            $query = "UPDATE report_partition SET label = '{$label}' WHERE partition_id = '{$id}' ";
            return $this->getDB()->query($query);
        }

        $id = $this->getNewPartitionID();
        if (is_null($id)) return FALSE;
        $query = "INSERT INTO report_partition(partition_id,label) VALUES('{$id}','{$label}')";
        return $this->getDB()->query($query);
    }

    private function getNewPartitionID()
    {
        $sql = "SELECT MAX(partition_id) AS partitionId FROM report_partition";
        $response = $this->getDB()->query($sql);
        $id = $response[0]->partitionId;
        if ($id == "Z") return NULL;

        return $id ? ++$id : "A";
    }

    public function savePartitionRecord($partition_id, $selected_skills, $selected_ivrs)
    {

        $query = "DELETE FROM partition_record WHERE partition_id='{$partition_id}' ";
        $this->getDB()->query($query);

        $values = "";
        $i = 0;
        $j = 0;
        $skill_length = count($selected_skills);
        $ivr_length = count($selected_ivrs);

        foreach ($selected_skills as $selected_skill)
        {
            $values .= "('{$selected_skill}', '{$partition_id}', 'SQ') ";
            $values .= ++$i == $skill_length && $ivr_length <= 0 ? " " : ", ";
        }
        foreach ($selected_ivrs as $selected_ivr)
        {
            $values .= "('{$selected_ivr}', '{$partition_id}', 'IV') ";
            $values .= ++$j == $ivr_length ? " " : ", ";
        }

        $query = "INSERT INTO partition_record(record_id, partition_id, type) VALUES {$values} ";
        return $this->getDB()->query($query);
    }

    public function findByID($id)
    {
        if ( ! ctype_upper($id) || ! strlen($id) == 1) return NULL;
        $response = $this->getDB()->query("SELECT * FROM report_partition WHERE partition_id = '{$id}' LIMIT 1");
        return $response[0];
    }

    public function getPartitionSkills($partition_id)
    {
        return $this->getDB()->query("SELECT * FROM partition_record WHERE partition_id = '{$partition_id}' ");

    }

    public function getPartionSkillsArray($partition_id, $type = '')
    {
        $list = [];

        $query = "SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' ";
        $query .= !empty($type) ? " AND type='{$type}' " : "";
        $partitions = $this->getDB()->query($query);
        foreach ($partitions as $partition)
        {
            $list[] = $partition->record_id;
        }

        return $list;
    }


}
