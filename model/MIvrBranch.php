<?php

class MIvrBranch extends Model
{

    /**
     * @return int
     */
    public function countAll()
    {
        $result = $this->getDB()->query("SELECT COUNT(*) AS  aggregate FROM ivr_branch_title");

        return (is_array($result) && !empty($result[0]->aggregate)) ? $result[0]->aggregate : 0;
    }

    public function all($offset = 0, $limit = 20)
    {
        return $this->getDB()->query("SELECT * FROM ivr_branch_title LIMIT {$offset}, {$limit} ");
    }

    public function allAsKeyValue()
    {
        $branch_list = [];

        $branch_titles = $this->all(0, 100000);
        if (is_array($branch_titles)){
            foreach ($branch_titles as $branch_title){
                $branch_list[$branch_title->title_id] = $branch_title->title;
            }
        }

        return $branch_list;
    }

    public function findByID($id)
    {
        $result = $this->getDB()->query("SELECT * FROM ivr_branch_title WHERE title_id='{$id}' ");

        return (is_array($result) && !empty($result[0])) ? array_shift($result) : null;
    }

    /**
     * @param $title
     * @return bool
     */
    public function saveTitle($title)
    {
        $id = $this->getAutoIncrementId();

        if (empty($id)){
            return false;
        }

        return $this->getDB()->query("INSERT INTO ivr_branch_title(title_id, title) VALUES ('{$id}', '{$title}')");
    }

    /**
     * @param $id
     * @param $title
     * @return bool
     */
    public function updateBranchTitle($id, $title)
    {
        return $this->getDB()->query("UPDATE ivr_branch_title SET title = '{$title}' WHERE  title_id = '{$id}' LIMIT 1");
    }

    /**
     * @param integer $default
     * @return int
     */
    protected function getAutoIncrementId($default = 100001)
    {
        $result = $this->getDB()->query("SELECT MAX(title_id) AS id from ivr_branch_title ");

        return empty($result[0]->id) ? $default : $result[0]->id + 1;
    }

}
