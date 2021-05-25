<?php

class MForecastSkill extends Model
{
	public function __construct() {
		parent::__construct();
	}

	public function getFcSkillList(){
		$sql = "SELECT * FROM fc_skill order by name";
		$data = $this->getDB()->query($sql);
		$tmp_data = [];

		foreach ($data as $key => $item) {
			$tmp_data[$item->service_type][$item->gplex_fc_skill_id]['name'] = $item->name;  
			$tmp_data[$item->service_type][$item->gplex_fc_skill_id]['gplex_his_skill_id'] = $item->gplex_his_skill_id;  
			$tmp_data[$item->service_type][$item->gplex_fc_skill_id]['gplex_fc_group_ids'] = $item->gplex_fc_group_ids;  
		}

		return $tmp_data;
	}

	public function getSkillIdFromName($skill_data, $skill_full_name){
        foreach ($skill_data as $skill_id => $skill) {
            if ($skill['name'] == $skill_full_name) {
                return $skill_id;
            }
        }
        return null;
    }

    public function getSkill($skill_id){
        $sql = "SELECT * FROM fc_skill WHERE gplex_fc_skill_id='$skill_id' ";
        // Gprint($sql);

		return $this->getDB()->query($sql);
    }

    public function getFcSkillListForOrinalSkillId($ids){
		$sql = "SELECT * FROM fc_skill WHERE gplex_fc_skill_id IN(".$ids.")";

		return $this->getDB()->query($sql);
	}
}

?>