<?php

class Model
{
	private static $_conn = null;
	public static $_template=null;

    public $columns;
    public $tables;
    public $conditions;
    public $groupByColumns;
    public $havingConditions;
    public $orderByColumns;
    public $limit;
    public $offset;
    public $dateInfo;
    public $dateFrom;
    public $dateTo;
    public $leftJoin;

	function __construct()
	{
		//$this->_conn = $conn;
	}

	function getAndCondition($previous_cond, $new_cond)
	{
		if (!empty($previous_cond)) {
			return $previous_cond . ' AND ' . $new_cond;
		}
		return $new_cond;
	}
	static function  setTemplate(&$template)
	{
		Model::$_template = $template;
	}
	function getTemplate()
	{
		return Model::$_template;
	}
	function getUpdateCondition($previous_cond, $new_cond)
	{
		if (!empty($previous_cond)) {
			return $previous_cond . ', ' . $new_cond;
		}
		return $new_cond;
	}

	function addAuditText($previous_cond, $new_cond)
	{
		if (!empty($previous_cond)) {
			return $previous_cond . ';' . $new_cond;
		}
		return $new_cond;
	}

	function getSqlOfChangedFields($oldobj, $obj, $fields)
	{
		$changed_fields = '';
		if (is_array($fields)) {
			foreach ($fields as $dbfield => $objfield) {
				if (isset($obj->$objfield)){
				    if ($obj->$objfield != $oldobj->$dbfield) {
				        $changed_fields = $this->getUpdateCondition($changed_fields, $dbfield."='".$obj->$objfield."'");
				    }
				}
			}
		}
		
		return $changed_fields;
	}
	
	function getAuditText($oldobj, $obj, $fields, $fields_names, $value_options=null)
	{
		$audit_text = '';
		$options = is_array($value_options) ? $value_options : array();
		$fields_names = is_array($fields_names) ? $fields_names : array();
		if (is_array($fields)) {
			foreach ($fields as $dbfield => $objfield) {
				$_field = isset($fields_names[$objfield]) ? $fields_names[$objfield] : '';
				//echo '#'.$obj->$objfield . $oldobj->$dbfield . $_field.'<br />';
				if (isset($obj->$objfield) != $oldobj->$dbfield && !empty($_field)) {
					$_atext = '';
					if (isset($options[$objfield])) {
						$obj_value_options = $options[$objfield];
						if (is_array($obj_value_options)) {
							$old_value = isset($obj_value_options[$oldobj->$dbfield]) ? $obj_value_options[$oldobj->$dbfield] : $oldobj->$dbfield;
							$new_value = isset($obj_value_options[$obj->$objfield]) ? $obj_value_options[$obj->$objfield] : $obj->$objfield;
							$_atext = $old_value." to ".$new_value;
						}
					} else {
						$old_value = $oldobj->$dbfield;
						$new_value = $obj->$objfield;
						$_atext = $old_value." to ".$new_value;
					}
					if (!empty($_atext)) $_atext = "=".$_atext;
					$audit_text = $this->addAuditText($audit_text, $_field.$_atext);
				}
			}
		}
		return $audit_text;
	}
	/*
	function getAuditTextOfChangedFields($oldobj, $obj, $fields)
	{
		$changed_fields = '';
		if (is_array($fields)) {
			foreach ($fields as $field => $options) {
				if ($obj->$field != $oldobj->$field) {
					if (is_array($options)) {
						//$fro
						$changed_fields = $this->addAuditText($changed_fields, $dbfield."='".$obj->$objfield."'");
					}
					$changed_fields = $this->getUpdateCondition($changed_fields, $dbfield."='".$obj->$objfield."'");
				}
			}
		}
		
		return $changed_fields;
	}
	*/
	
	function addToAuditLog($page, $type, $identity, $obj1, $obj2=null, $mapping=null)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$user_id = UserAuth::getCurrentUser();
		$user_id = empty($user_id) && !empty($identity) ? $identity : $user_id;
		//$tstamp = time();
		$logText = $identity;
		if (is_string($obj1)) {
			$logText = $this->addAuditText($logText, $obj1);
		}
		
		$sql = "INSERT INTO audit_log SET tstamp=UNIX_TIMESTAMP(), ip='$ip', agent_id='$user_id', ".
			"page='$page', log_text='$logText', type='$type'";
		// GPrint($sql);
		return $this->getDB()->query($sql, false, [';']);
	}
	
	function getDB()
	{
		if (empty(self::$_conn)) {
			include('./conf.php');
			self::$_conn = new DBManager($db);
		}
		//var_dump(self::$_conn->sel_conn);
		return self::$_conn;
	}

    function GetNewIncId($fieldName=null, $default=null, $table_name=null) {
        if (!empty($fieldName) && !empty($default) && !empty($table_name))
        {
            $nthis=new static();
            $sql = "SELECT MAX({$fieldName}) as lastS FROM {$table_name}";
            $result = $this->getDB()->query($sql);
            if($result[0]->lastS)
            {
                $a=$result[0]->lastS;
                $a++;
                return $a;
            }
            return $default;
        }
        return null;
    }

    function GetNewMaxId($fieldName = null, $default = null, $table_name = null)
    {
        if (!empty($fieldName) && !empty($default) && !empty($table_name)) {
            $nthis = new static();
            $sql = "SELECT COUNT({$fieldName}) as lastS FROM {$table_name}";
            $result = $this->getDB()->query($sql);
//            dd($sql);
            if ($result[0]->lastS) {
                $a = $result[0]->lastS;
                $a++;
                return $a;
            }
            return $default;
        }
        return null;
    }

    public function getSelectSql()
    {
        $sql = "SELECT {$this->columns} FROM {$this->tables} ";
        if (!empty($this->leftJoin)) $sql .= " LEFT JOIN {$this->leftJoin} ";
        if (!empty($this->conditions)) $sql .= " WHERE {$this->conditions} ";
        if (!empty($this->groupByColumns)) $sql .= " GROUP BY {$this->groupByColumns} ";
        if (!empty($this->havingConditions)) $sql .= " HAVING {$this->havingConditions} ";
        if (!empty($this->orderByColumns)) $sql .= " ORDER BY {$this->orderByColumns} ";
        if ($this->limit > 0) $sql .= " LIMIT {$this->limit} ";
        if ($this->offset > 0) $sql .= " OFFSET {$this->offset} ";
        return $sql;
    }

    public function getInsertSql()
    {
        $sql = "INSERT INTO {$this->tables} ";
        $sql .= " SET {$this->columns} ";
        return $sql;
    }

    public function getUpdateSql()
    {
        $sql = "UPDATE {$this->tables} ";
        $sql .= " SET {$this->columns} ";
        if (!empty($this->conditions)) $sql .= " WHERE {$this->conditions} ";
        if ($this->limit > 0) $sql .= " LIMIT {$this->limit} ";
        return $sql;
    }

    public function getDeleteSql()
    {
        $sql = "DELETE FROM {$this->tables} ";
        if (!empty($this->conditions)) $sql .= " WHERE {$this->conditions} ";
        if ($this->limit > 0) $sql .= " LIMIT {$this->limit} ";
        return $sql;
    }

    public function getResultCount($primaryKey = "*")
    {
        $this->columns = "COUNT({$primaryKey}) AS total_record";
        $sql = $this->getSelectSql();
        $record = $this->getDB()->query($sql);
        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    public function getResultData()
    {
        $sql = $this->getSelectSql();
        return $this->getDB()->query($sql);
    }

    public function getUpdateResult()
    {
        $sql = $this->getUpdateSql();
        return $this->getDB()->query($sql);
    }

    public function getInsertResult()
    {
        $sql = $this->getInsertSql();
        return $this->getDB()->query($sql);
    }

    public function getDeleteResult()
    {
        $sql = $this->getDeleteSql();
        return $this->getDB()->query($sql);
    }

    public function setDate($dateInfo)
    {
        if ($this->checkDate($dateInfo)) {
            $this->dateInfo = $dateInfo;
            $this->dateFrom = date("Y-m-d H:i:s", $this->dateInfo->ststamp);
            $this->dateTo = date("Y-m-d H:i:s", $this->dateInfo->etstamp);
            return true;
        }
        return false;
    }

    public function checkDate($dateInfo)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return false;
        }
        return true;
    }

}
