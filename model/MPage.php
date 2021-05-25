<?php

class MPage extends Model
{
	private $valid_field = [
		'name' => [
			'len' => 40,
			'validation' => [
				'required' => true, 
				'max-len' => 40
			],
			'validation_msg' => [
				'required' => "Name is required!", 
				'max-len' => "Please enter no more than 40 characters!",
			],
			'special_char_check' => false
		],
		'path' => [
			'len' => 50,
			'validation' => [
				'required' => true, 
				'max-len' => 50
			],
			'validation_msg' => [
				'required' => "Path is required!", 
				'max-len' => "Please enter no more than 50 characters!",
			],
			'special_char_check' => false
		],
		'icon' => [
			'len' => 25,
			'validation' => [
				'required' => true, 
				'max-len' => 25
			],
			'validation_msg' => [
				'required' => "Icon is required!", 
				'max-len' => "Please enter no more than 20 characters!",
			],
			'special_char_check' => false
		],
		'active_class' => [
			'len' => 50,
			'validation' => [
				'required' => true, 
				'max-len' => 50
			],
			'validation_msg' => [
				'required' => "Active Class is required!", 
				'max-len' => "Please enter no more than 50 characters!",
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
		'layout' => [
			'len' => 3,
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "Layout is required!",
			],
			'special_char_check' => true
		],
		'access_code' => [
			'len' => 25,
			// 'validation' => [
			// 	'required' => true,
			// ],
			// 'validation_msg' => [
			// 	'required' => "Access Code is required!",
			// ],
			'special_char_check' => true
		],
		'id' => [
			'len' => 10,
			'special_char_check' => true
		],
		'crud_function' => [
			'len' => 1000,
			'special_char_check' => false,
			'type' => 'json',
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "CRUD function is required!",
			],
		],
		'pop_out' => [
			'len' => 1,
			'special_char_check' => true,
			'validation' => [
				'required' => true,
			],
			'validation_msg' => [
				'required' => "Page pop out is required!",
			],
		]
	];

	public function __construct() {
		parent::__construct();
	}

	public function numPages($id = '', $name='', $status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM pages ";
		if (!empty($id)) $cond .= "id LIKE ?";
		if (!empty($name)) $cond = $this->getAndCondition($cond, "name LIKE ?");
		if (!empty($status)) $cond = $this->getAndCondition($cond, "status=?");
		
		if (!empty($cond)) $sql .= "WHERE $cond ";

		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$id."%", "paramRawValue" => $id]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }	
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }

		$validate = $this->getDB()->validateQueryParams($condParams); 
		if($validate['result'] == true){
			// var_dump($validate);
			// var_dump($sql);
			// die();
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
	}

	public function getPages($id='', $name='', $status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT * FROM pages ";
		if (!empty($id)){ 
			$cond .= "id LIKE ?";
		}		
		if (!empty($name)){
			$cond = $this->getAndCondition($cond, "name LIKE ?");
		}
		if (!empty($status)){ 
			$cond = $this->getAndCondition($cond, "status=?");
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		// set condition params for sql injection
		$condParams = [];
		$result = [];
		if(!empty($id)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$id."%", "paramRawValue" => $id]; }
		if(!empty($name)){  $condParams[] = ["paramType" => "s", "paramValue" => "%".$name."%", "paramRawValue" => $name]; }
		if(!empty($status)){  $condParams[] = ["paramType" => "s", "paramValue" => $status]; }
		
		$validate = $this->getDB()->validateQueryParams($condParams);
		if($validate['result'] == true){ 
			// var_dump($validate);
			// var_dump($sql);
			// die();
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		return $result;
	}

	private function validate($post_data)
	{
		$page = new stdClass();
		$validator = new FormValidator();
		
		if (is_array($post_data)) {
			$count = 0;
			unset($post_data['submit']);
			foreach ($post_data as $key=>$val) {
				if(isset($this->valid_field[$key]['type']) && isset($this->valid_field[$key]['type']) == 'json')
					$val = json_encode($val, JSON_NUMERIC_CHECK);

				if(isset($this->valid_field[$key]) && isset($this->valid_field[$key]['validation'])){
					$valid = $validator->validation(trim($val), $this->valid_field[$key]['validation'], $this->valid_field[$key]['validation_msg']);
					if(!$valid['result']){
						$errors[$key] = $valid['msg'];
					}
				}
				$page->$key = trim($val);
			}
		}

		if(!empty($errors)){
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $page,
				MSG_ERROR_DATA => $errors
			];
		}

		$login_user = UserAuth::getCurrentUser();
		
		if(!empty($page->id)){
			$page->updated_by = $login_user;
			$page->updated_at = date("Y-m-d H:i:s");
		}else{
			$page->created_by = $login_user;
			$page->id = (string)strtotime(date("Y-m-d H:i:s"));
		}

		return [
			MSG_RESULT => true,
			MSG_TYPE => MSG_SUCCESS,
			MSG_DATA => $page,
		];
	}

	public function saveData($post_data){
		$post_data['crud_function'] = (isset($post_data['crud_function']) && !empty($post_data['crud_function'])) ? $post_data['crud_function'] : [ACCESS_READ];
		$validate = $this->validate($post_data);
		// dd($validate);

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
		$sql = '';
		$log_txt = '';
		$is_insert = false;

		if(!empty($validate[MSG_DATA])){
			$count = 0;			
			foreach ((array)$validate[MSG_DATA] as $key => $value) {
				$fields[$count] = $key;
				$values[$count] = '?';
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
			$sql = "INSERT INTO pages(".implode(",", $fields).") VALUES(".implode(",", $values).")";
			// echo $sql;
			// pr($fields);
			// pr($values);
			// pr($condParams);
			// die();

			$validateQuery = $this->getDB()->validateQueryParams($condParams);
			// dd($validateQuery);
			if($validateQuery['result'] == true){
				$is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
			}
		}

		if ($is_insert) {
			$this->addToAuditLog('Page add', 'PG', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Page has been submitted successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Page has not been submitted successfully.'
			];
		}
	}

	public function pageTreeData($status = '', $json=false){
		$pages = $this->getPages('', '', $status);
		foreach ($pages as $key => $value) {
			$pages[$key] = (array) $value;
		}

		$itemsByReference = array();
		// Build array of item references:
		foreach($pages as $key => &$item) {
			$itemsByReference[$item['id']] = &$item;
		}

		// Set items as children of the relevant parent item.
		foreach($pages as $key => &$item) {
			if($item['parent'] && isset($itemsByReference[$item['parent']])) {
				$itemsByReference [$item['parent']]['nodes'][] = &$item;
			}
		}

		// Remove items that were added to parents elsewhere:
		foreach($pages as $key => &$item) {
			if($item['parent'] && isset($itemsByReference[$item['parent']]))
			unset($pages[$key]);
		}
		
		if($json)
			return json_encode($pages);

		return $pages;
	}

	public function pageTreeViewWithRadio($checked_id = null){
		$data = $this->pageTreeData('A');
		$html_data = $this->htmlTreeViewWithRadio($data, $checked_id);

		return $html_data;
	}

	public function htmlTreeViewWithRadio($data, $checked_id){
		$htmlData = "";
		foreach($data as $d){
			$checked_txt = '';
			if(!empty($checked_id) && $checked_id == $d['id'])
				$checked_txt = "checked='checked'";

            if(!empty($d['nodes'])){
                $htmlData .= "<li class='checkbox' data-id='".$d['id']."'>";
                $htmlData .= "<label class='radio-container'>".$d['name']; 
                $htmlData .= "<input type='radio' name='parent' class='page-list-info' value='".$d['id']."' ".$checked_txt." /> ";
                $htmlData .= "<span class='radio-checkmark'></span></label>";
                $htmlData .= "<ul data-name='".$d['active_class']."'>";
                $htmlData .= $this->htmlTreeViewWithRadio($d['nodes'], $checked_id);
                $htmlData .= "</ul>";
                $htmlData .= "</li>";
            }else{
                $htmlData .= "<li class='checkbox' data-id='".$d['id']."'>";
                $htmlData .= "<label class='radio-container'>".$d['name']; 
                $htmlData .= "<input type='radio' name='parent' class='page-list-info' value='".$d['id']."' ".$checked_txt." /> ";
                $htmlData .= "<span class='radio-checkmark'></span></label>";
                $htmlData .= "</li>"; 
            }
        }

        return $htmlData;
	}

	public function pageTreeViewWithCheckbox($checked_id = null){
		$data = $this->pageTreeData('A');
		$html_data = $this->htmlTreeViewWithCheckbox($data, $checked_id);

		return $html_data;
	}

	public function htmlTreeViewWithCheckbox($data, $checked_id){
		$htmlData = "";
		foreach($data as $d){
			$checked_txt = '';
			if(!empty($checked_id) && $checked_id == $d['id'])
				$checked_txt = "checked='checked'";

            if(!empty($d['nodes'])){
                $htmlData .= "<li class='checkbox' data-id='".$d['id']."'>";
                $htmlData .= "<label class='checkbox-container'>".$d['name']; 
                $htmlData .= "<input type='checkbox' name='parent' class='page-list-info' value='page###".$d['id']."###".$d['name']."###".$d['path']."###".$d['icon']."###".$d['active_class']."###".$d['layout']."###".$d['pop_out']."' ".$checked_txt." /> ";
                $htmlData .= "<span class='checkbox-checkmark'></span></label>";
                $htmlData .= "<ul data-name='".$d['active_class']."'>";
                $htmlData .= $this->htmlTreeViewWithCheckbox($d['nodes'], $checked_id);
                $htmlData .= "</ul>";
                $htmlData .= "</li>";
            }else{
                $htmlData .= "<li class='checkbox' data-id='".$d['id']."'>";
                $htmlData .= "<label class='checkbox-container'>".$d['name']; 
                $htmlData .= "<input type='checkbox' name='parent' class='page-list-info' value='page###".$d['id']."###".$d['name']."###".$d['path']."###".$d['icon']."###".$d['active_class']."###".$d['layout']."###".$d['pop_out']."' ".$checked_txt." /> ";
                $htmlData .= "<span class='checkbox-checkmark'></span></label>";
                $htmlData .= "</li>";
            }
        }

        return $htmlData;
	}

	public function updatePageStatus($id, $status='')
	{		
		$login_user = UserAuth::getCurrentUser();
		$sql = "UPDATE pages SET status='$status' WHERE id='$id'";		
		if ($this->getDB()->query($sql)) {
			$ltxt = $status==STATUS_ACTIVE ? 'Inactive to Active' : 'Active to Inactive';
			$this->addToAuditLog('Page', 'PG', "User=$login_user", "Status=".$ltxt);
			return true;
		}
		return false;
	}

	public function updateData($post_data, $old_data){
		$post_data['crud_function'] = (isset($post_data['crud_function']) && !empty($post_data['crud_function'])) ? $post_data['crud_function'] : [ACCESS_READ];
		$validate = $this->validate($post_data);
		// dd($validate);
		
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
		$sql = '';
		$log_txt = '';
		$is_update = false;
		$isNotifyNeeded = false;

		if(!empty($validate[MSG_DATA])){
			$count = 0;
			
			foreach ((array)$validate[MSG_DATA] as $key => $value) {
				if ($value != $old_data->$key) {
					$field_values[$count] = $key."= ? ";				
					$condParams[$count] = [
						"paramType" => "s", 
						"paramValue" => $value,
						'specialCharCheck' => (isset($this->valid_field[$key]['special_char_check']) ? $this->valid_field[$key]['special_char_check'] : false)				
					];	

					if(isset($this->valid_field[$key]['len'])){
						$condParams[$count]['paramLength'] = $this->valid_field[$key]['len'];
					}

					$log_txt = $this->addAuditText($log_txt, ucfirst($key)."=".$old_data->$key." to ".$value);
					$count++;
				}
			}
			$sql = "UPDATE pages SET ".implode(',', $field_values)." WHERE id= ? ";
			$condParams[] = ["paramType" => "s", "paramValue" => $old_data->id];
			// echo $sql;
			// pr($field_values);
			// pr($condParams);
			// pr($log_txt);

			$validate = $this->getDB()->validateQueryParams($condParams);
			// dd($validate); 
			if($validate['result'] == true){
				$is_update = $this->getDB()->executeInsertQuery($sql, $validate['paramTypes'], $validate['bindParams']);
			}
		}

		if ($is_update) {
			$this->addToAuditLog('Page Update', 'PG', "User= ".$login_user, $log_txt);
			return [
				MSG_RESULT => true,
				MSG_TYPE => MSG_SUCCESS,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Page has been updated successfully.'
			];
		}else{
			return [
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_DATA => $validate[MSG_DATA],
				MSG_MSG => 'Page has not been updated successfully.'
			];
		}
	}

	/**
	 * Read all page and use array index by field
	 */
	public function pageListIndexWithField($idx_field, $options = []){
		$cond = '';
		$condParams = [];
		$sql = "SELECT * FROM pages ";
		if(isset($options['conditions']) && !empty($options['conditions'])){
			foreach ($options['conditions'] as $key => $item) {
				if(isset($item['condition']) && !empty($item['condition']))
					$cond .= ' '.$item['condition'].' ';

				$cond .= $item['field'].$item['operator']." ?";

				$condParams[] = ["paramType" => "s", "paramValue" => $item['value']];
			}			
		}
		
		if (!empty($cond)) $sql .= "WHERE $cond ";
		if (isset($options['order']) && !empty($options['order'])) $sql .= " ORDER BY ".$options['order'];
		if (isset($options['limit']) && $options['limit'] > 0) $sql .= " LIMIT ".$options['offset']. ", ".$options['limit'];
		
		// set condition params for sql injection
		$result = [];
		$tmp_result = [];	
		$validate = $this->getDB()->validateQueryParams($condParams);		

		if($validate['result'] == true){ 
			$result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
		}
		
		if(!empty($result)){
			foreach ($result as $key => $item) {
				$tmp_result[$item->id] = $item;
			}
		}
		
		return $tmp_result;
	}

    public function numReportPages($id = '')
    {
        $this->setReportPagesData($id);
        return $this->getResultCount("id");
    }

    public function getReportPages($id = '', $offset = 0, $limit = 0)
    {
        $this->setReportPagesData($id, $offset, $limit);
        return $this->getResultData();
    }

    private function setReportPagesData($id = '', $offset = 0, $limit = 0)
    {
        $this->tables = "pages";
        $this->columns = "id, name, path, ajax_path";
        $this->conditions = " path != '#' ";
        $this->conditions .= " AND layout = 'PRE' ";
        if ($id != '') {
            $this->conditions .= " AND id = '{$id}' ";
        }
        $this->offset = $offset;
        $this->limit = $limit;
    }

}

?>