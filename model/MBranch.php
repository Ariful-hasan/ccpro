<?php

class MBranch extends Model {

    public static $header = ['branch_name'=>'Branch Name', 'branch_code'=>'Branch Code'];
    public $csv_load_file = null;
    public $csv_load_column = null;

    /*
     * insert csv
     */
    function csv_load(){
        $sql = "LOAD DATA INFILE '".$this->csv_load_file."' ";
        $sql .= "INTO TABLE branch FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' ";
        $sql .= "(".$this->csv_load_column.")";
        return $this->getDB()->query($sql);
    }

    public function numBranch($search_obj)
    {
        if (!$this->setDate($search_obj->created_at)) return 0;
        $this->setBranchListData($search_obj);
        return $this->getResultCount("id");
    }

    public function getBranch($search_obj, $limit=0, $offset=0)
    {
        if (!$this->setDate($search_obj->created_at)) return [];
        $this->setBranchListData($search_obj, $limit, $offset);
        return $this->getResultData();
    }

    private function setBranchListData($search_obj, $limit=0, $offset=0)
    {
        $this->tables = "branch";
        $this->columns = " * ";
        $this->conditions = " created_at BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}' ";
        if (!empty($search_obj->branch_code)) $this->conditions .= " AND branch_code = '{$search_obj->branch_code}' ";
        if (!empty($search_obj->branch_name)) $this->conditions .= " AND branch_name LIKE '%{$search_obj->branch_name}%' ";

        $this->orderByColumns = " created_at ";
        $this->limit = $limit;
        $this->offset = $offset;
    }

    private function setBranchData($id)
    {
        $this->tables = "branch";
        $this->columns = " * ";
        $this->conditions = " id = '{$id}' ";
    }

    public function getBranchById($id)
    {
        $this->setBranchData($id);
        return $this->getResultData();
    }

    private function setBranchUpdateData($id=null, $post_data)
    {
        $agentId = UserAuth::getCurrentUser();
        $currentTime= date("Y-m-d H:i:s");

        $this->tables = "branch";

        if (!empty($id)) {
            $this->columns = " branch_name = '{$post_data->branch_name}', branch_code = '{$post_data->branch_code}',updated_at = '{$currentTime}', updated_by = '{$agentId}'";
            $this->conditions = " id = '{$id}' ";
            $this->limit = 1;
        } else {
            $id = substr(bin2hex(random_bytes(8)), 0, 12);
            $this->columns = " id='{$id}', branch_name='{$post_data->branch_name}', branch_code='{$post_data->branch_code}', created_by='{$agentId}'";
        }
    }

    public function updateBranch($id, $post_data, $old_data=null)
    {
        if (!empty($old_data) && $post_data->branch_code != $old_data->branch_code) {
            if ($this->getBranchByBranchCode($post_data->branch_code))
                return false;
        }
        $this->setBranchUpdateData($id, $post_data);
        return $this->getUpdateResult();
    }

    public function saveBranch ($post_data) {
        if ($this->getBranchByBranchCode($post_data->branch_code))
            return false;

        $this->setBranchUpdateData(null, $post_data);
        return $this->getInsertResult();
    }

    public function getBranchByBranchCode ($branch_code) {
        $this->tables = "branch";
        $this->columns = " * ";
        $this->conditions = " branch_code = '{$branch_code}' ";
        return $this->getResultData();
    }

    public function deleteBranchById ($id) {
        $this->tables = "branch";
        $this->conditions = " id = '{$id}' ";
        $this->limit = 1;
        return $this->getDeleteResult();
    }

}
