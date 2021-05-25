<?php

class MBlackwhite extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function getBlackList($cli = '', $offset = 0, $limit = 0)
    {
        $sql = "SELECT * FROM Black_list_786 ";
        $sql .= !empty($cli) && $cli != '*' ? " WHERE cli = '$cli'  " : "";

        if ($limit > 0) $sql .= " LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function numBlackList($cli = '')
    {
        $sql = "SELECT COUNT(*) AS total_record FROM Black_list_786 ";
        $sql .= !empty($cli) && $cli != '*' ? " WHERE cli = '$cli'  " : "";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result[0]->total_record;;
        }
        return 0;
    }

    function saveBlackListData($black_list_data)
    {
        $sql = "INSERT INTO Black_list_786 VALUES ";
        foreach ($black_list_data as $data) {
            $sql .= "('$data[0]','$data[1]','$data[2]'),";
        }
        $sql = substr($sql, 0, -1);

        return $this->getDB()->query($sql);
    }

    function getWhiteList($cli = '', $offset = 0, $limit = 0)
    {
        $sql = "SELECT * FROM white_list_777 ";
        $sql .= !empty($cli) && $cli != '*' ? " WHERE cli = '$cli'  " : "";

        if ($limit > 0) $sql .= " LIMIT $offset, $limit";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function numWhiteList($cli = '')
    {
        $sql = "SELECT COUNT(*) AS total_record FROM white_list_777 ";
        $sql .= !empty($cli) && $cli != '*' ? " WHERE cli = '$cli'  " : "";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result[0]->total_record;
        }
        return 0;
    }

    function saveWhiteListData($white_list_data)
    {
        $sql = "INSERT INTO white_list_777 VALUES ";
        foreach ($white_list_data as $data) {
            $sql .= "('$data[0]','$data[1]','$data[2]'),";
        }
        $sql = substr($sql, 0, -1);

        return $this->getDB()->query($sql);
    }

    function deleteBlackListData($black_list_data)
    {
        $sql = "DELETE FROM Black_list_786 WHERE cli = '$black_list_data' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    function deleteWhiteListData($white_list_data)
    {
        $sql = "DELETE FROM white_list_777 WHERE cli = '$white_list_data' LIMIT 1";
        return $this->getDB()->query($sql);
    }

    function getBlackDuplicateData()
    {
        $sql = "SELECT cli, grade, COUNT(cli) AS total_count
                FROM Black_list_786
                GROUP BY cli, grade
                HAVING total_count > 1";

        return $this->getDB()->query($sql);
    }

    function getWhiteDuplicateData()
    {
        $sql = "SELECT cli, grade, COUNT(cli) AS total_count
                FROM white_list_777
                GROUP BY cli, grade
                HAVING total_count > 1";

        return $this->getDB()->query($sql);
    }

    function removeBlackDuplicates($data)
    {
        $limit = $data->total_count - 1;

        $sql = "DELETE FROM Black_list_786 WHERE cli = '$data->cli' AND grade = '$data->grade' ";
        $sql .= " LIMIT $limit";

        return $this->getDB()->query($sql);
    }

    function removeWhiteDuplicates($data)
    {
        $limit = $data->total_count - 1;

        $sql = "DELETE FROM white_list_777 WHERE cli = '$data->cli' AND grade = '$data->grade' ";
        $sql .= " LIMIT $limit";

        return $this->getDB()->query($sql);
    }
}

?>