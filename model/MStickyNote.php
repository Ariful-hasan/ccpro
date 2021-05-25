<?php 

class MStickyNote extends Model 
{
	private $valid_field = [
		'title' => [
			'len' => 30,
			'validation' => [
				'required' => true, 
				'max-len' => 30
			],
			'validation_msg' => [
				'required' => "Title is required!", 
				'max-len' => "Please enter no more than 40 characters!",
			],
			'special_char_check' => false
		],
		'description' => [
			'len' => 500,
			'validation' => [
				'required' => true, 
				'max-len' => 500
			],
			'validation_msg' => [
				'required' => "Description is required!", 
				'max-len' => "Please enter no more than 500 characters!",
			],
			'special_char_check' => false
		],
		'status' => [
			'len' => 1,
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "Status is required!",
			],
			'special_char_check' => true
		],
		'agent_id' => [
			'len' => 9999,
			'special_char_check' => false,
			'type' => 'array',
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "Notify Person is required!",
			],
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function saveData($post_data){
		$post_data['agent_id'] = (isset($post_data['agent_id']) && !empty($post_data['agent_id'])) ? $post_data['agent_id'] : [];
		$validator = new FormValidator();
		$validate = $validator->valid($post_data, $this->valid_field);	
		// GPrint($validate);
		// die();

		if(!$validate[MSG_RESULT]){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_ERROR_DATA => $validate[MSG_ERROR_DATA],
				MSG_MSG => 'You have some form errors. Please check below!'
			];
		}

		$login_user = UserAuth::getCurrentUser();
		$update_flag = false;
		$sql = '';
		$log_txt = '';
		$is_insert = false;
		$al_page_name = 'Sticky Note Create';

		if(!empty($validate[MSG_DATA]->id)){
			$update_flag = true;
			$validate[MSG_DATA]->updated_by = $login_user;
			$validate[MSG_DATA]->updated_at = (string)date("Y-m-d H:i:s");
		}else{
			$id = str_replace('.', '', microtime(true));
			$validate[MSG_DATA]->created_by = $login_user;
			$validate[MSG_DATA]->id = (string)$id;
			$validate[MSG_DATA]->created_at = (string)date("Y-m-d H:i:s");
		}
		
		if(!empty($validate[MSG_DATA])){
			$count = 0;			
			foreach ((array)$validate[MSG_DATA] as $key => $value) {
				if($update_flag){
					$field_values[$count] = $key."= ? ";
				}else{
					$fields[$count] = $key;
					$values[$count] = '?';
				}

				$condParams[$count] = [
					"paramType" => "s", 
					"paramValue" => $value,
					'specialCharCheck' => (isset($this->valid_field[$key]['special_char_check']) ? $this->valid_field[$key]['special_char_check'] : false)				
				];	

				if(isset($this->valid_field[$key]['len'])){
					$condParams[$count]['paramLength'] = $this->valid_field[$key]['len'];
				}

				$log_txt .= ucfirst($key)."='$value'; ";
				$count++;
			}
			$sql = "";
			if($update_flag){
				$sql = "UPDATE sticky_notes SET ".implode(',', $field_values)." WHERE id= ? ";
				$condParams[] = ["paramType" => "s", "paramValue" => $validate[MSG_DATA]->id];
			}else{
				$sql = "INSERT INTO sticky_notes (".implode(",", $fields).") VALUES(".implode(",", $values).")";
			}
			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			// echo $sql;
			// GPrint($fields);
			// GPrint($values);
			// GPrint($field_values);
			// GPrint($condParams);
			// GPrint($validateQuery);
			// die();

			if(!empty($sql) &&$validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
			}
		}

		if ($is_insert) {
			$this->addToAuditLog($al_page_name, 'I', "User= ".$login_user, $log_txt);
			$this->deleteAssignStickyNote($validate[MSG_DATA]->id);
			$this->assignStickyNote($validate[MSG_DATA]);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Sticky Note has been submitted successfully.'
			];
		}else{
			$validate[MSG_DATA]->id = (!$update_flag) ? '' : $validate[MSG_DATA]->id;

			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Sticky Note has not been submitted successfully.'
			];
		}
	}

	public function assignStickyNote($data)
	{
		$sql = "INSERT INTO log_sticky_notes (snid, agent_id, acknowledged, acknowledged_at) VALUES ";
		$agent_ids = explode(',', $data->agent_id);
		foreach ($agent_ids as $key => $value) {
			$sql_str[] = "('$data->id', '$value', '', '')";
		}		
		$sql .= implode(',', $sql_str);
		// var_dump($sql); die();

		if($this->getDB()->query($sql)){
			$login_user = UserAuth::getCurrentUser();
			$this->addToAuditLog('Sticky Note Assign', 'I', "User= ".$login_user, '');
			return true;
		}
		return false;
	}

	public function deleteAssignStickyNote($st_id)
	{
		$sql = "DELETE FROM log_sticky_notes WHERE snid='$st_id'";

		if($this->getDB()->query($sql)){
			$login_user = UserAuth::getCurrentUser();
			$this->addToAuditLog('Sticky Note Assign', 'D', "User= ".$login_user, '');
			return true;
		}
		return false;
	}

	public function getStickyNotes($id='',$title='',$status='',$offset=0,$limit=0){
		$cond = '';
		$sql = "SELECT * FROM sticky_notes ";
		if (!empty($id)) $cond = "id=?";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE ?");		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramRawValue" => $id]; }
		if(!empty($title)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$title."%", "paramRawValue" => $title]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			// var_dump($validate);
			// var_dump($sql);
			// die();
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	public function numStickyNotes($id='', $title=''){
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM sticky_notes ";

		if (!empty($id)) $cond = "id=?";
		if (!empty($title)) $cond = $this->getAndCondition($cond, "title LIKE ?");		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => $id, "paramRawValue" => $id]; }
		if(!empty($title)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$title."%", "paramRawValue" => $title]; }

		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){
			// var_dump($validate);
			// var_dump($sql);
			// die();
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getStickyNoteById($id)
	{
		$sql = "SELECT * FROM sticky_notes WHERE id='$id' ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}

	public function deleteStickyNote($id)
	{
		if(!empty($id)){
			$login_user = UserAuth::getCurrentUser();
			$sql = "DELETE FROM sticky_notes WHERE id='$id' ";
			if ($this->getDB()->query($sql)) {				
				$ltxt = 'StickyNote delete';
				$this->addToAuditLog('Sticky Note', 'D', "User=$login_user", "Status=".$ltxt);
				$this->deleteAssignStickyNote($id);
				return true;
			}
		}
		return false;
	}

	public function updateStickyNoteStatus($id, $status)
	{
		if(!empty($id)){
			$login_user = UserAuth::getCurrentUser();
			$sql = "UPDATE sticky_notes SET status='$status' WHERE id='$id'";		
			if ($this->getDB()->query($sql)) {
				$ltxt = $status==STATUS_ACTIVE ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog('Sticky Note', 'U', "User=$login_user", "Status=".$ltxt);
				return true;
			}
		}
		return false;
	}

	public function getAssignAgents($id){
		$this->columns = "*";
		$this->tables = "log_sticky_notes";
		$this->leftJoin = " agents ON agents.agent_id = log_sticky_notes.agent_id ";
		$this->conditions = "snid='".$id."'";

		return $this->getResultData();
	}
}

?>