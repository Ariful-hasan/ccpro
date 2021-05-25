<?php


class MPDSettings extends Model
{
    private $valid_field = [
        'skill_id' => [
            'len' => 2,
            'validation' => [
                'required' => true,
                'max-len' => 2
            ],
            'validation_msg' => [
                'required' => "Skill is required!",
                'max-len' => "Please enter no more than 2 characters!",
            ],
            'special_char_check' => false
        ],
        'field' => [
            'len' => 20,
            'validation' => [
                'required' => true,
                'max-len' => 20
            ],
            'validation_msg' => [
                'required' => "Field is required!",
                'max-len' => "Please enter no more than 20 characters!",
            ],
            'special_char_check' => false
        ],
        'header' => [
            'len' => 30,
            'validation' => [
                'required' => true,
                'max-len' => 30
            ],
            'validation_msg' => [
                'required' => "Header is required!",
                'max-len' => "Please enter no more than 30 characters!",
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
            'special_char_check' => false
        ]
    ];

    public $tables = "pd_settings";
    public $qtype = "P";


    public function __construct() {
        parent::__construct();
    }

    public function getPDSettingsSkills () {
        $sql = "SELECT s.skill_id, s.skill_name FROM skill AS s ";
        $sql .= "LEFT JOIN $this->tables AS ps ON ps.skill_id = s.skill_id ";
        $sql .= "WHERE ps.skill_id IS NULL AND s.qtype ='$this->qtype' ";
        $result = $this->getDB()->query($sql);
        return count($result) > 0 ? array_column($result, skill_name , skill_id) : null;
    }

    private function setPDSkillsData($isJoin=false, $limit = 0, $offset = 0)
    {
        $this->columns = " pd_settings.skill_id, pd_settings.status";
        $this->groupByColumns = "pd_settings.skill_id";
        if ($isJoin) {
            $this->leftJoin = "skill ON skill.skill_id=pd_settings.skill_id";
            $this->columns .= " , skill.skill_name";
            $this->orderByColumns = " skill.skill_name ";
        }

        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numPDSkills() {
        $this->setPDSkillsData();
        return $this->getResultCount("pd_settings.skill_id");
    }

    public function getPDSkills($limit = 0, $offset = 0)
    {
        $this->setPDSkillsData(true, $limit, $offset);
        return $this->getResultData();
    }


    private function validate($post_data)
    {
        $pdsettings = new stdClass();
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
                $pdsettings->$key = trim($val);
            }
        }

        if(!empty($errors)){
            return [
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_DATA => $pdsettings,
                MSG_ERROR_DATA => $errors
            ];
        }

//        $login_user = UserAuth::getCurrentUser();
//        if(!empty($pdsettings->id)){
//            $pdsettings->updated_by = $login_user;
//            $pdsettings->updated_at = date("Y-m-d H:i:s");
//        }else{
//            $pdsettings->created_by = $login_user;
//            $pdsettings->id = (string)substr(bin2hex(random_bytes(6)), 0, 12);
//        }

        return [
            MSG_RESULT => true,
            MSG_TYPE => MSG_SUCCESS,
            MSG_DATA => $pdsettings,
        ];
    }

    public function saveData($post_data, $old_data=null)
    {
        //GPrint($old_data);
        $skill_id = $post_data['skill_id'];
        unset($post_data['skill_id']);
        $validate = $this->validate($post_data);
        //dd($validate[MSG_DATA]);

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
        $insert_values = [];
        $update_values = "";
        $condParams = [];
        $update_item = [];
        $login_user = UserAuth::getCurrentUser();

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

                if (property_exists($old_data, $key) && $old_data->$key != $value) {
                    $update_values .= "WHEN item='".$key."' THEN '".$value."' ";
                    $update_item[] = $key;
                } elseif (empty($old_data->$key) && !empty($value)) {
                    $id = (string)substr(bin2hex(random_bytes(6)), 0, 12);
                    $insert_values[] = "('".$id."','".$skill_id."','".$key."','".$value."', 'A', '".$login_user."', '".date("Y-m-d H:i:s")."')";
                }
            }

            $validateQuery = $this->getDB()->validateQueryParams($condParams);
            if($validateQuery['result'] == true) {
                if ($update_values){
                    $update_sql = "UPDATE pd_settings SET updated_by='".$login_user."', updated_at='".date("Y-m-d H:i:s")."', header = CASE ".$update_values." END WHERE item IN ('".implode("','", $update_item)."') AND skill_id='$skill_id'";
                    $is_insert = $this->getDB()->query($update_sql);
                }
                if ($insert_values) {
                    $insert_sql = "INSERT INTO pd_settings(id, skill_id, item, header, status, created_by, created_at) VALUES".implode(",", $insert_values);
                    $is_insert = $this->getDB()->query($insert_sql);
                }
            }
        }

        if ($is_insert) {
            $this->addToAuditLog('Settings add', 'PD', "User= ".$login_user, $log_txt);
            return [
                MSG_RESULT => true,
                MSG_TYPE => MSG_SUCCESS,
                MSG_DATA => $validate[MSG_DATA],
                MSG_MSG => 'Settings has been submitted successfully.'
            ];
        }else{
            return [
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_DATA => $validate[MSG_DATA],
                MSG_MSG => 'Settings has not been submitted successfully.'
            ];
        }
    }

    public static function getDataBySkillId ($skill_id) {
        $sql = "SELECT * FROM pd_settings WHERE skill_id='$skill_id'";
        return self::getDB()->query($sql);
    }

    public static function updateStatus ($skill_id, $status) {
        $sql = "UPDATE pd_settings SET `status`='$status' WHERE skill_id='$skill_id'";
        return self::getDB()->query($sql);
    }
}