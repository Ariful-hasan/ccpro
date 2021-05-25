<?php

class MForecastGlobalSetting extends Model
{
	public function __construct() {
		parent::__construct();
	}

	public function getSettings(){
		$sql = "SELECT * FROM fc_global_settings ";
		return $this->getDB()->query($sql);
	}

	public function getSettingsData(){
		$sql = "SELECT * FROM fc_global_settings ";
		$data = $this->getDB()->query($sql);
		$tmp_data = [];
		foreach ($data as $key => $value) {
			$tmp_data[$value->key_name] = $value->key_value;
		}
		return $tmp_data;
	}

	public function saveData($post_data){
		try {
			$old_data = $this->getSettings();
		    $login_user = UserAuth::getCurrentUser();

		    $insert_sql = '';
		    $update_sql = '';
		    $insert_ltxt = '';
		    $update_ltxt = '';

            $id = $this->GetNewMaxId('id','AAAAAAAAAA','fc_global_settings');
		    if(empty($old_data)){
		    	$insert_values = [];

		    	foreach ($post_data as $key => $value) {
                    $id++;
                    $insert_values[] = "('".$id."','".$key."','".$value."', '')";
		    		$insert_ltxt .= $key."=".$value.";";
		    	}
		    	if(!empty($insert_values))
		    		$insert_sql = "INSERT INTO fc_global_settings (id, key_name, key_value, key_unit) VALUES ".implode(',', $insert_values);
		    }else{
	    		$update_id = [];
	    		$insert_values = [];
	    		$update_values = '';
	    		$gen_insert_id = [];
		    	foreach ($post_data as $key => $value) {
		    		$update_flag = false;
		    		foreach ($old_data as $old_key => $old_value) {
		    			if($old_value->key_name == $key){
		    				$update_flag = true;
		    				$update_id[] = $old_value->id;
		    				break;
		    			}
		    		}

		    		if($update_flag){
		    		    $update_values .= "when id = '".$update_id[count($update_id)-1]."' then '".$value."' ";
		    			$update_ltxt .= $key."=".$value.";";
		    		}else{
                        $new_id = $this->GetNewMaxId('id','AAAAAAAAAA','fc_global_settings');
                        if(!in_array($new_id, $gen_insert_id)) {
                            $gen_insert_id[] = $new_id;
                            $id = $new_id;
                        }else{
                            $id = $gen_insert_id[count($gen_insert_id)-1]+1;
                        }
                        $insert_values[] = "('".$id."','".$key."','".$value."', '')";
		    			$insert_ltxt .= $key."=".$value.";";
		    		}		    		
		    	}
		    	if(!empty($update_values)){
		    		$update_sql = "UPDATE fc_global_settings SET key_value = CASE ".$update_values." END WHERE id IN ('".implode("','", $update_id)."')";
		    	}

		    	if(!empty($insert_values))
		    		$insert_sql = "INSERT INTO fc_global_settings (id, key_name, key_value, key_unit) VALUES ".implode(',', $insert_values);
		    }

           // GPrint($update_sql);
           // var_dump($insert_sql);
           // die();

			if (!empty($insert_sql) && $this->getDB()->query($insert_sql)) {
				$this->addToAuditLog('Global Settings (Forecast)', 'F', "Root User= ".$login_user, $insert_ltxt);
			}
			if (!empty($update_sql) && $this->getDB()->query($update_sql)) {
				$this->addToAuditLog('Global Settings (Forecast)', 'F', "Root User= ".$login_user, $update_ltxt);
			}
			return true;

		} catch (Exception $e) {
			return false;
		}
	}

}

?>