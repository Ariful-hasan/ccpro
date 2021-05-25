<?php

global $mysqli;
global $mysqli_error;

function db_conn()
{
    global $mysqli;
    $ini_array = parse_ini_file("db_config.ini");
    $db_host = $ini_array['UPDATE_DB_HOST'];
    $db_user = $ini_array['UPDATE_DB_USER'];
    $db_pass = $ini_array['UPDATE_DB_PASSWORD'];
    $db = $ini_array['UPDATE_DATABSE_NAME'];
    $dbsuffix = $ini_array['DB_SUFFIX'];
    if (!empty($dbsuffix)) {
        $db = "cc".$dbsuffix;
    }

    $mysqli = new mysqli("$db_host", "$db_user", "$db_pass", "$db");

    if (!$mysqli) {
        msg("Can't connect to Database!");
        return 1;
    }
}

function mysql_keep_alive()
{
    global $mysqli;
    if ($mysqli->ping()!=1 && $mysqli->ping()!=1) {
        @$mysqli->close();
        while (db_conn()==1) {
            sleep(5);
        }
    }
}

function db_update($sql)
{
    global $mysqli, $mysqli_error;
    msg($sql);
    $mysqli_error = '';

    if (!$mysqli->query($sql)) {
        $mysqli_error = $mysqli->error;
    }
    return $mysqli->affected_rows;
}

function db_select($sql)
{
    global $mysqli;
    msg($sql);
    @$result = $mysqli->query($sql);
    if ($mysqli->affected_rows == 1) {
        $row = $result->fetch_object();
        $mysqli->next_result();
    }
    if (is_object($result)) {
        $result->close();
    }
    if (is_object($row)) {
        return $row;
    } else {
        return 0;
    }
}

function db_select_one($sql)
{
    global $mysqli;
    msg($sql);
    $data = null;
    @$result = $mysqli->query($sql);
    if ($mysqli->affected_rows == 1) {
        $row = $result->fetch_array(2);
        $data = $row[0];
    }
    if (is_object($result)) {
        $result->close();
    }
    return $data;
}


function db_select_array($sql, $i=0)
{
    global $mysqli;
    msg($sql);
    @$result = $mysqli->query($sql);
    if ($mysqli->affected_rows > 0) {
        while ($row = $result->fetch_object()) {
            if ($i == 0) {
                $key = current($row);   # first row should be unique
                $obj[$key] = $row;
            } else {
                if ($i == 1) {
                    $obj[] = $row;
                } else {
                    # i = 2
                    if (!$field) {
                        $field = key($row);
                    }
                    $obj[] = $row->$field;
                }
            }
        }
    } else {
        $obj = 0;
    }
    if (is_object($result)) {
        $result->close();
    }
    return $obj;
}


function msg($str)
{
    global $debug,$agi,$logfile;
    if ($debug) {
        if (is_array($str) || is_object($str)) {
            $str = print_r($str, true);
        }
    }

    if ($debug == 1) {
        echo "$str\n";
    } elseif ($debug == 2) {
        $agi->verbose($str);
    } elseif ($debug == 3) {
        file_put_contents($logfile, "$str\n", FILE_APPEND | LOCK_EX);
    }
}


function injection_filter($sql)
{
    $key_words = array('information_schema','UNION','CAST','column_name',';','--','\\');
    foreach ($key_words as $word) {
        $pos = stripos($sql, $word);
        if ($pos !== false) {
            $sql = "SELECT NOW()";
            break;
        }
    }
    return $sql;
}

## Back compatibility
function obj($row)
{
    foreach ($row as $key => $value) {
        $obj->{$key} = $value;
    }
    return $obj;
}

function escapeString($str) {
    global $mysqli;
    if (!empty($str)) {
        return $mysqli->real_escape_string($str);
    }
    return '';
}
