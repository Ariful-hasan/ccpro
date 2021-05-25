<?php
class MNotes extends Model
{
    public $max_row_read = 2000;
    public $hide_column = [];
    public $linePointer = 0;
    public function __construct() {
        parent::__construct();
    }

    private $valid_field = [
        'cli' => [
            'len' => 15,
            'validation' => [
                'required' => true,
                'max-len' => 15
            ],
            'validation_msg' => [
                'required' => "Mobile number is required!",
                'max-len' => "Please enter no more than 11 digits!",
            ],
            'special_char_check' => false
        ],
        'note' => [
            'len' => 300,
            'validation' => [
                'required' => true,
                'max-len' => 300
            ],
            'validation_msg' => [
                'required' => "Note is required!",
                'max-len' => "Please enter no more than 300 characters!",
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
        ]
    ];

    function isValidNumber($number){
        return strlen($number) >= 6 && strlen($number) <= 15 ? true : false;
    }

    /*private function setCSVDataForPrepare($file, $validData) {
        $row = 0;
        $response = [];
        $param_type = "";
        $login_user = UserAuth::getCurrentUser();

        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row > 1 && !empty($data)) {
                    $cli = $note = "";
                    foreach ($data as $key => $value) {
                        if (!empty($validData[$key]) && strlen($value) <= $this->valid_field[$validData[$key]]['validation']['max-len']) {
                            if ($validData[$key] == "cli") {
                                $value = preg_replace("/[^0-9+]/","", $value);
                                if ($this->isValidNumber($value)) {
                                    $cli = trim($value);
                                }
                            } else {
                                $note = trim($value);
                            }
                        }
                    }
                    if (!empty($cli) && !empty($note)) {
                        $response[] = (string)str_replace('.', '', microtime(true)); //id
                        $response[] = $cli; //cli
                        $response[] = $note; //note
                        $response[] = "A"; //status
                        $response[] = $login_user; //created_by
                        $response[] = (string)date("Y-m-d H:i:s"); //created_at
                        $param_type .= "ssssss";
                    }
                }
            }
            fclose($handle);
        }
        return [$response, $param_type];
    }*/


    private function setCSVDataForPrepareNew($file, $validData) {
        $row = 0;
        $response = [];
        $param_type = "";
        $login_user = UserAuth::getCurrentUser($file);
        $unique_ary = [];


        $spl = new SplFileObject($file);
        $spl->setFlags(SplFileObject::SKIP_EMPTY);
        $spl->setFlags(SplFileObject::READ_CSV);
        $spl->seek($this->linePointer);
        while (!empty($data = $spl->fgetcsv(','))) {
            if ($row == $this->max_row_read) {
                break;
            }
            //$row++;
            $cli = $note = "";
			
            foreach ($data as $key => $value) {
                if (!empty($validData[$key]) && strlen($value) <= $this->valid_field[$validData[$key]]['validation']['max-len']) {
                    if ($validData[$key] == "cli") {
                        $value = preg_replace("/[^0-9+]/","", $value);
                        $value = substr($value, 0, 4) == "+880" ? $value : (substr($value, 0, 1) == "0" ? "+88".$value : "+880".$value);
                        if ($this->isValidNumber($value)) {
                            //$value = substr($value, 0, 1) > 0 ? "0".$value : $value;
                            $cli = (string)trim($value);
                        }
                    } else {
                        $note = trim($value);
                    }
                }
            }

            /*if (!empty($cli) && !empty($note)) {
                $response[] = (string)str_replace('.', '', microtime(true)); //id
                $response[] = $cli; //cli
                $response[] = $note; //note
                $response[] = "A"; //status
                $response[] = $login_user; //created_by
                $response[] = (string)date("Y-m-d H:i:s"); //created_at
                $param_type .= "ssssss";
            }*/

            //$id = (string)str_replace('.', '', microtime(true)).bin2hex(random_bytes(6));
            $id = (string)str_replace('.', '', microtime(true)).bin2hex(random_bytes(6));
            if (!empty($cli) && !empty($note) && !in_array($id, $unique_ary)) {
                unset($ary);
                $ary[] = $id; //id
                $ary[] = $cli; //cli
                $ary[] = $note; //note
                $ary[] = "A"; //status
                $ary[] = $login_user; //created_by
                $ary[] = (string)date("Y-m-d H:i:s"); //created_at
                $response[] = $ary;
                $unique_ary[] = $id;
				$row++;
            }
        }

        $obj = new stdClass();
        $obj->values = $response;
        //$obj->paramType = $param_type;
        //$obj->lineNumber = $row>0 ? $row-2 : $row;
        $obj->lineNumber = $row;
        $obj->successCount = count($unique_ary);
        unset($unique_ary);
        return $obj;
    }



    public function saveCSV($file, $valid_data) {
        $result = $this->setCSVDataForPrepareNew($file, $valid_data);
        $is_insert = false;
        //GPrint($values);

        /*if (!empty($result->values)){
            $variables = count($result->values)/6;
            $sql = "INSERT INTO notes (id, cli, note, status, created_by, created_at) values ";
            $sql .= implode(',', array_fill(0, $variables, '(?, ?, ?, ?, ?, ?)'));
            GPrint($sql);
            $is_insert = $this->getDB()->executeInsertQuery($sql, $result->paramType, $result->values);
        }*/

        if (!empty($result->values)) {
            $sql = "INSERT INTO notes (id, cli, note, status, created_by, created_at) values ('";
            foreach ($result->values as $key) {
                $sql .= implode("','", $key)."'), ('";
            }
            $sql = rtrim($sql,", ('");
            $is_insert = $this->getDB()->query($sql);
        }

        if ($is_insert) {
            $this->addToAuditLog("Note CSV Upload", 'I', "User= ".UserAuth::getCurrentUser(), "CSV DATA");
            return [
                MSG_RESULT => true,
                MSG_TYPE => MSG_SUCCESS,
                MSG_DATA => $result->values,
                MSG_MSG => 'CSV has been uploaded successfully.',
                "lineNumber" => $result->lineNumber,
                "successCount" => $result->successCount
            ];
        }else{
            return [
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_DATA => $result->values,
                MSG_MSG => 'CSV has not been uploaded successfully.',
                "lineNumber" => 0,
                "successCount" => 0
            ];
        }
    }


    public function saveData($post_data){
        $validator = new FormValidator();
        $validate = $validator->valid($post_data, $this->valid_field);

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
        $al_page_name = 'Note Create';

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
                $sql = "UPDATE notes SET ".implode(',', $field_values)." WHERE id= ? ";
                $condParams[] = ["paramType" => "s", "paramValue" => $validate[MSG_DATA]->id];
            }else{
                $sql = "INSERT INTO notes (".implode(",", $fields).") VALUES(".implode(",", $values).")";
            }
            $validateQuery = $this->getDB()->validateQueryParams($condParams);
            if(!empty($sql) &&$validateQuery['result'] == true){
                $is_insert = $this->getDB()->executeInsertQuery($sql, $validateQuery['paramTypes'], $validateQuery['bindParams']);
            }
        }

        if ($is_insert) {
            $this->addToAuditLog($al_page_name, 'I', "User= ".$login_user, $log_txt);
            return [
                MSG_RESULT => true,
                MSG_TYPE => MSG_SUCCESS,
                MSG_DATA => $validate[MSG_DATA],
                MSG_MSG => 'Note has been submitted successfully.'
            ];
        }else{
            $validate[MSG_DATA]->id = (!$update_flag) ? '' : $validate[MSG_DATA]->id;

            return [
                MSG_RESULT => false,
                MSG_TYPE => MSG_ERROR,
                MSG_DATA => $validate[MSG_DATA],
                MSG_MSG => 'Note has not been submitted successfully.'
            ];
        }
    }

    public function getNoteById($id){
        $sql = "SELECT * FROM notes WHERE id='$id' ";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0];
        }
        return null;
    }

    public function numNotes($search_obj){
        if (empty($search_obj->created_at->ststamp) || $search_obj->created_at->ststamp > $search_obj->created_at->etstamp)
            return null;

        $stime = $search_obj->created_at->sdate." ".$search_obj->created_at->stime;
        $etime = $search_obj->created_at->edate." ".$search_obj->created_at->etime;

        $sql = "SELECT COUNT(id) AS numrows FROM notes ";
        $sql .= "WHERE created_at BETWEEN '$stime' AND '$etime' ";
        $sql .= !empty($search_obj->cli) ? "AND cli=?" : "";
        // set condition params for sql injection
        $condParams = [];
        $result = [];
        if(!empty($search_obj->cli)){  $condParams[] = ["paramType" => "s", "specialCharCheck"=>false, "paramValue" => $search_obj->cli, "paramRawValue" => $search_obj->cli]; }
        $validate = $this->getDB()->validateQueryParams($condParams);
        if($validate['result'] == true){
            $result = $this->getDB()->executeQuery($sql, $validate['paramTypes'], $validate['bindParams']);
        }
        return isset($result[0]->numrows) && !empty($result[0]->numrows) ? $result[0]->numrows : 0;
    }

    public function getNotes($search_obj, $offset=0,$limit=0){
        $stime = $search_obj->created_at->sdate." ".$search_obj->created_at->stime;
        $etime = $search_obj->created_at->edate." ".$search_obj->created_at->etime;

        $sql = "SELECT * FROM notes ";
        $sql .= "WHERE created_at BETWEEN '$stime' AND '$etime' ";
        $sql .= !empty($search_obj->cli) ? "AND cli='$search_obj->cli'" : "";
        $sql .= "ORDER BY created_at DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    public function updateNoteStatus($id, $status) {
        if(!empty($id)){
            $login_user = UserAuth::getCurrentUser();
            $sql = "UPDATE notes SET status='$status' WHERE id='$id'";
            if ($this->getDB()->query($sql)) {
                $ltxt = $status==STATUS_ACTIVE ? 'Inactive to Active' : 'Active to Inactive';
                $this->addToAuditLog('Note', 'U', "User=$login_user", "Status=".$ltxt);
                return true;
            }
        }
        return false;
    }

    public function deleteNote($id) {
        if(!empty($id)){
            $login_user = UserAuth::getCurrentUser();
            $sql = "DELETE FROM notes WHERE id='$id' ";
            if ($this->getDB()->query($sql)) {
                $ltxt = 'Note delete';
                $this->addToAuditLog('Note', 'D', "User=$login_user", "Status=".$ltxt);
                return true;
            }
        }
        return false;
    }

    public function getColumnName(){
        $sql = "DESC notes";
        $result = $this->getDB()->query($sql);
        $response = [];
        if (!empty($result)) {
            foreach ($result as $key){
                if (!in_array($key->Field, $this->hide_column)) {
                    $response[] = $key->Field;
                }
            }
        }
        return $response;
    }

}