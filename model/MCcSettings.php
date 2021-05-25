<?php
class MCcSettings extends Model
{
	public $module_type;

	public function __construct() {
		parent::__construct();
	}

	public function getAllSettings()
	{
		$cond = '';
		$sql = "SELECT * FROM cc_settings ";
		if (!empty($this->module_type)) 
			$cond = "module_type='$this->module_type'";
		if (!empty($cond)) 
			$sql .= "WHERE $cond";

		$result = $this->getDB()->query($sql);
		if (!empty($field) && is_array($result)) 
			return $result;
		return $result;
	}
	public function getSingleSettings($item)
	{
		$cond = '';
		$sql = "SELECT * FROM cc_settings ";
		if (!empty($this->module_type)) 
			$cond = "module_type='$this->module_type'";
		if (!empty($item)) 
			$cond .= " and item='$item'";

		if (!empty($cond)) 
			$sql .= "WHERE $cond";

		$result = $this->getDB()->query($sql);
		if (!empty($field) && is_array($result)) 
			return $result;
		return $result;
	}
	public function saveData($post_data){
		try {
			$old_data = $this->getAllSettings();
		    $login_user = UserAuth::getCurrentUser();

		    $insert_sql = '';
		    $update_sql = '';
		    $insert_ltxt = '';
		    $update_ltxt = '';
//            $id = $this->GetNewIncId('id','AAAAAAAAAA','cc_settings');
            $id = $this->GetNewMaxId('id','AAAAAAAAAA','cc_settings');
		    if(empty($old_data)){
		    	$insert_values = [];

		    	foreach ($post_data as $key => $value) {
		    		//$id = (string)strtotime(date("Y-m-d H:i:s.u"));
                    $id++;
                    if ($key =="attachment_save_path"){
                        $insert_values[] = "('".$id."','".$this->module_type."','".$key."','".base64_encode($value)."', '".$login_user."')";
                    }else {
                        $insert_values[] = "('".$id."','".$this->module_type."','".$key."','".$value."', '".$login_user."')";
                    }
		    		$insert_ltxt .= $key."=".$value.";";
		    	}
		    	if(!empty($insert_values))
		    		$insert_sql = "INSERT INTO cc_settings (id,module_type, item, value, created_by) VALUES ".implode(',', $insert_values);
		    }else{
	    		$update_id = [];
	    		$insert_values = [];
	    		$update_values = '';
	    		$gen_insert_id = [];
                $new_id = $this->GetNewMaxId('id','AAAAAAAAAA','cc_settings');
		    	foreach ($post_data as $key => $value) {
		    		$update_flag = false;
		    		foreach ($old_data as $old_key => $old_value) {
		    			if($old_value->item == $key){
		    				$update_flag = true;
		    				$update_id[] = $old_value->id;
		    				break;
		    			}
		    		}

		    		if($update_flag){
		    		    if ($key =="attachment_save_path"){
                            $update_values .= "when id = ".$update_id[count($update_id)-1]." then '".base64_encode($value)."' ";
                        }else {
                            $update_values .= "when id = ".$update_id[count($update_id)-1]." then '".$value."' ";
                        }
		    			$update_ltxt .= $key."=".$value.";";
		    		}else{
                        //$new_id = $this->GetNewMaxId('id','AAAAAAAAAA','cc_settings');
                        $new_id += 1;
                        if(!in_array($new_id, $gen_insert_id)) {
                            $gen_insert_id[] = $new_id;
                            $id = $new_id;
                        }else{
                            $id = $gen_insert_id[count($gen_insert_id)-1]+1;
                        }
                        if ($key =="attachment_save_path"){
                            $insert_values[] = "('".$id."','".$this->module_type."','".$key."','".base64_encode($value)."', '".$login_user."')";
                        }else {
                            $insert_values[] = "('".$id."','".$this->module_type."','".$key."','".$value."', '".$login_user."')";
                        }
		    			$insert_ltxt .= $key."=".$value.";";
		    		}		    		
		    	}
		    	if(!empty($update_values)){
		    		$update_sql = "UPDATE cc_settings SET updated_by='".$login_user."', updated_at='".date("Y-m-d H:i:s")."', value = CASE ".$update_values." END WHERE id IN (".implode(',', $update_id).")";
		    	}

		    	if(!empty($insert_values))
		    		$insert_sql = "INSERT INTO cc_settings (id, module_type, item, value, created_by) VALUES ".implode(',', $insert_values);
		    }

//            GPrint($update_sql);
//            var_dump($insert_sql);
//            die();

			if (!empty($insert_sql) && $this->getDB()->query($insert_sql)) {
				$this->addToAuditLog('Global Settings ('.$this->module_type.')', 'A', "Root User= ".$login_user, $insert_ltxt);
			}
			if (!empty($update_sql) && $this->getDB()->query($update_sql)) {
				$this->addToAuditLog('Global Settings ('.$this->module_type.')', 'A', "Root User= ".$login_user, $update_ltxt);
			}
			return true;

		} catch (Exception $e) {
			return false;
		}
	}

	public function getFormatAllSettings(){
		$old_data = $this->getAllSettings();
		$tmp_data = [];

		if (!empty($old_data)){
            foreach ($old_data as $key => $value) {
                $tmp_data[$value->item] = $value;
            }
        }
		return $tmp_data;
	}

    function getEmailSettings(){
        $sql = "SELECT item,value FROM cc_settings WHERE module_type='".MOD_EMAIL."' ";
        return $this->getDB()->query($sql);
    }

}

?>