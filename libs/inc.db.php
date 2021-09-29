<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL ^ E_DEPRECATED);

//
// DBAL Database Abstract Layer
//
class DBAL {

    private static $db = null;
    private $connection;

    private function __construct() {
        $local_settings = parse_ini_file(tc_relpath("etc/local.ini"));

        $db_url = "localhost";
        $db_name = "tc";
        $db_user = "tcweb";
        $db_pwd  = $local_settings["tcweb_pass"];
        $db_encoding = "utf8";
        $dsn="mysql:host=$db_url;dbname=$db_name";
        try {
            if( version_compare(PHP_VERSION, '5.3.6', '<') ){
                if( defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $db_encoding;
                }
            }else{
                $dsn .= ';charset=' . $db_encoding;
            }
            $options_arr = [
              // PDO::ATTR_EMULATE_PREPARES   => false,
              PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_PERSISTENT         => true,
              PDO::MYSQL_ATTR_FOUND_ROWS   => true
            ];
            $this->connection = new PDO($dsn, $db_user, $db_pwd, $options_arr);
        } catch (PDOException $e) {
            die ("Connect fail: " . $e->getMessage() . "<br/>");
        }
    }


    public static function getInstance()
    {
        if (self::$db == null) {
            self::$db = new DBAL();
        }
        return self::$db;
    }


    public function getConn() {
        return $this->connection;
    }

    /**
     *  Query for SELECT
     *  Return NULL means a SQL error
     */
    public static function do_select($sql) {
        $result = NULL;
        try {
            $result = self::getInstance()->getConn()->query($sql);
        } catch (PDOException $e) {
            throw $e;
        }
        return $result;
    }

    public static function do_stmt($sql, $where_value='') {
        $stmt = NULL;
        try {
            $where_value = str_replace("'", "", $where_value);
            $stmt = self::getInstance()->getConn()->prepare($sql);
            $stmt->execute([$where_value]);
        } catch (PDOException $e) {
            throw $e;
        }
        return $stmt;
    }

    public static function do_stmt_where_mul_params($sql, $where_value1='',$where_value2='') {
        $where_value1 = str_replace("'", "", $where_value1 );
        $where_value2 = str_replace("'" ,"" ,$where_value2 );
        $stmt = self::getInstance()->getConn()->prepare($sql);
        $stmt->execute([$where_value1, $where_value2]);
        return $stmt ? $stmt : NULL;

    }

    public static function db_sql_select($table, $fields, $where=NULL, $order=NULL) {
        $cols = $fields;
        if (is_array($fields)) {
            $cols = implode(', ', $fields);
        }
        $where_content = do_deal_where($where, $table);
        if(count($where_content)==2){
            if(is_null($order)){
                $res = self::getInstance()->do_stmt("SELECT $cols FROM $table WHERE $where_content[0]", $where_content[1]);
            }else{
                $res = self::getInstance()->do_stmt("SELECT $cols FROM $table WHERE $where_content[0] ORDER BY $order", $where_content[1]);
            }

        }else if(count($where_content)==3){
            if(is_null($order)){
                $res = self::getInstance()->do_stmt_where_mul_params("SELECT $cols FROM $table WHERE $where_content[0]", $where_content[1], $where_content[2]);
            }else{
                $res = self::getInstance()->do_stmt_where_mul_params("SELECT $cols FROM $table WHERE $where_content[0] ORDER BY $order", $where_content[1], $where_content[2]);
            }
        }else{
            return rest_error_mysql('e_database');
        }
        return $res;
    }

    public static function db_select($stmt) {
        if (is_null($stmt->rowCount())) {
            return NULL;
        }
        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function db_select_row($stmt) {
        $res = NULL;
        if ($stmt) {
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (is_null($res) || !$res) {
            return NULL;
        }
        return $res;
    }

    public static function db_select_first_value($stmt) {
        if ($stmt) {
            $row = $stmt->fetch(PDO::FETCH_NUM);
            if($row && count($row)){
                return $row[0];
            }
        }
        return NULL;
    }

    public static function db_select_first_value_old($sql) {
        $res = self::getInstance()->do_select($sql);
        if ($res) {
            $row = $res->fetch(PDO::FETCH_NUM);
            if($row && count($row)){
                return $row[0];
            }
        }
        return NULL;
    }

    public static function db_select_old($sql) {
        $result = self::getInstance()->do_select($sql);
        if (is_null($result)) {
            return NULL;
        }
        $rows = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function db_select_row_old($sql) {
        $res = self::getInstance()->do_select($sql);
        $result = NULL;
        if ($res) {
            $result = $res->fetch(PDO::FETCH_ASSOC);
        }
        if (is_null($result) || !$result) {
            return NULL;
        }
        return $result;
    }



    public static function db_update_table_row($table, $changes, $id) {
        return self::getInstance()->update_table($table, $changes, array("id" => $id));
    }

    public static function db_insert_table($table, $fields) {
        $keys = array();
        $values = array();
        foreach ($fields as $key => $value) {
            $keys[] = $key;
            $values[] = "'$value'";
        }
        $keys = implode(',', $keys);
        $values = implode(',', $values);
        $sql = "INSERT INTO $table ( $keys ) VALUES ( $values )";
        try {
           self::getInstance()->do_select($sql);
        } catch (PDOException $e) {
            throw $e;
        }
        return intval(self::getInstance()->connection->lastInsertId());
    }

    public static function db_insert_table_multiple($table, $keys, $row_list) {
        $rows = array();
        foreach ($row_list as $row) {
            $es = array();
            foreach ($row as $item) {
                $es[] = "'$item'";
            }
            $rows[] = "(" . implode(',', $es) . ")";
        }
        $keys = implode(',', $keys);
        $values = implode(', ', $rows);
        $sql = "INSERT INTO $table ( $keys ) VALUES $values";

        try {
           self::getInstance()->do_select($sql);
        } catch (PDOException $e) {
            throw $e;
        }
        return intval(self::getInstance()->connection->lastInsertId());
    }


    public static function db_delete($table, $where) {
        $count = NULL;
        try {
            $result = self::getInstance()->do_select("DELETE FROM $table WHERE $where");
            $count = $result->rowCount();
        } catch (PDOException $e) {
            throw $e;
        }
        return $count;
    }

    public static function db_delete_row($table, $row_id) {
        $count = self::getInstance()->db_delete($table, "id='$row_id'");
        if (is_null($count) || $count === 0) {
            return NULL;
        }
        return $count;
    }

    /**
     * update table with changes, DB general api
     * @param  [type] $table   [description]
     * @param  [type] $changes [description]
     * @param  string $where   [description]
     * @return [int] number of rows changed, NULL means error
     */

    public static function update_table($table, $changes, $where=array()) {
        try {
            $params = array();

            // UPDATE users SET name=?, surname=?, sex=? WHERE id=?
            $change_clause = array();
            foreach ($changes as $key => $value) {
                array_push($change_clause, "$key = ?");
                $params[] = $value;
            }
            $change_clause = implode(",", $change_clause);

            $where_clause = array();
            foreach ($where as $key => $value) {
                array_push($where_clause, "$key = ?");
                $params[] = $value;
            }
            $where_clause = implode(" AND ", $where_clause);

            $stmt = "UPDATE $table SET $change_clause";
            if ($where_clause) {
                $stmt .= " WHERE $where_clause";
            }
            return self::getInstance()->getConn()->prepare($stmt)->execute($params);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function select_rows($table, $fields, $where="", $order="id") {
        try {
            $params = $where;
            $params["order"] = $order;

            $column_clause = implode(",", $fields);

            $stmt = "SELECT $column_clause FROM $table";
            if ($where) {
                $where_clause = array();
                foreach (array_keys($where) as $key) {
                    array_push($where_clause, "$key = ?");
                }
                $where_clause = implode(" AND ", $where_clause);

                $stmt .= " WHERE $where_clause";
            }
            $stmt .= " ORDER BY ?";
            $stmt = self::getInstance()->getConn()->prepare($stmt);
            $stmt->execute(array_values($params));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function select_row($table, $fields, $where) {
        $rows = self::select_rows($table, $fields, $where);
        if ($rows && count($rows)) {
            return $rows[0];
        }
        return NULL;
    }

    public static function select_cell($table, $field, $row_index, $row_name="id") {
        $row = self::select_row($table, array($field), array($row_name => $row_index));
        if ($row) {
            foreach (array_values($row) as $value) {
                return $value;
            }
        }
        return NULL;
    }

    public static function select_cell_bool($table, $field, $row_index, $row_name="id") {
        $value = self::select_cell($table, $field, $row_index, $row_name);
        return is_null($value) ? NULL : boolval($value);
    }

    public static function delete_rows($table, $where) {
        try {
            $where_clause = array();
            foreach (array_keys($where) as $key) {
                array_push($where_clause, "$key = :$key");
            }
            $where_clause = implode(" AND ", $where_clause);

            $stmt = "DELETE FROM $table WHERE $where_clause";

            return self::getInstance()->getConn()->prepare($stmt)->execute($where);
        } catch (PDOException $e) {
            throw $e;
        }
        return NULL;
    }

    public static function delete_row($table, $index_value, $index_key="id") {
        return self::delete_rows($table, array($index_key => $index_value));
    }

    public static function insert_table($table, $fields) {
        return self::db_insert_table($table, $fields);
    }

}

function db_safe_array_get($key, $array, $default=NULL) {
    return safe_array_get($key, $array, $default);
}

function db_safe_array_get_string($key, $array, $exceptions) {
        // removed in PHP 5.5: mysql_real_escape_string
    $value = safe_array_get($key, $array);
    if (is_null($value)) {
        return NULL;
    }
    if(!sugar_valid_alnum($value, $exceptions)) {
        return NULL;
    }
    return $value;
}

function do_deal_where($where='', $table) {
    if (trim($where)) {
        if(strpos($where,'AND')){
            $where_params1 = strstr($where,"AND",true);
                $where_params2 = explode(' ', trim(strstr($where,"AND")), 2); //$where_params2[0] : AMD
                $where_split_params1 = explode('=', trim($where_params1));
                $where_split_params2 = explode('=', trim($where_params2[1]));
                if(empty(trim($where_split_params1[1])) && empty(trim($where_split_params2[1]))){//a= AND b=
                    $sql = "id>?";
                    $res_array = array($sql, '0');
                }else if(empty(trim($where_split_params1[1]))){ // a= AND b=1
                    $sql = "$where_split_params2[0]=?";
                    $res_array = array($sql, $where_split_params2[1]);

                }else if(empty(trim($where_split_params2[1]))){ // a=1 AND b=
                    $sql = "$where_split_params1[0]=?";
                    $res_array = array($sql, $where_split_params1[1]);
                }else{//a=1 AND b=1
                    $sql = "$where_split_params1[0]=? AND $where_split_params2[0]=?";
                    $res_array = array($sql, $where_split_params1[1], $where_split_params2[1]);
                }
            }else{
                $where_split = explode('=', trim($where));
                if(count($where_split) == 1){ //a=
                    $sql = "id>?";
                    $res_array = array($sql, '0');
                }else{ //a=1
                    $sql = "$where_split[0]=?";
                    $res_array = array($sql, $where_split[1]);
                }
            }
        }else{ // no where
            if($table == 'client_group'){
                $sql = "cgid>?";
                $res_array = array($sql, '0');
            }else{
                $sql = "id>?";
                $res_array = array($sql, '0');
            }
        }
        return $res_array;
    }

function db_catch_errno($errno) {
    return mysql_errno() == $errno;
}

function db_get_client_id_by_token($token) {
    $select = "SELECT id FROM machines WHERE client_token='$token'";
    $value = DBAL::db_select_first_value_old($select);
    return $value;
}

function db_client_image_update($ciid, $fields) {
    return DBAL::update_table("osimages", $fields, array("id" => $ciid));
}

function db_client_image_read($ciid, $fields) {
    $sql = DBAL::db_sql_select("osimages", $fields, "id=$ciid");
    if (count($fields) == 1) {
        return DBAL::db_select_first_value($sql);
    }
    return DBAL::db_select_row($sql);
}

function db_client_image_count_running($ciid) {
    $fields = array('COUNT(id)');
    $where = "osImageId='$ciid'";
    $result = DBAL::db_select_first_value(DBAL::db_sql_select('userstatus', $fields, $where));
    if (is_null($result)) {
        log_error(json_encode(rest_error_mysql()));
        return TRUE;
    }
    return intval($result);
}

function db_client_image_revision_update($ciid, $rev, $field, $value) {
    $changes = array($field => $value);
    $where = array(
        "imageId" => $ciid,
        "revision" => $rev
    );
    return DBAL::update_table("imageupdatehistory", $changes, $where);
}

function db_restore_point_id($ciid, $ci_rev) {
    // query RP information
    $fields = array("id");
    $where = "imageId=$ciid AND revision=$ci_rev";
    return DBAL::db_select_first_value(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
}

function db_restore_point_read($rpid) {
    // query RP information
    $fields = array(
        "id AS rpid",
        "imageId AS ciid",
        "revision",
        "previous",
        "userDataPath AS udf",
        "metaDataPath AS mdf",
        "rstUserDataPath AS udf_rst",
        "rstMetaDataPath AS mdf_rst",
    );
    $where = "id=$rpid";
    $rp = DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
    if (is_null($rp)) {
        log_warning("Cannot read RP $rpid");
    }
    return $rp;
}

function db_restore_point_delete($rpid) {
    return DBAL::db_delete_row("imageupdatehistory", $rpid);
}

function db_client_group_read($cgid) {
    $cgid = intval($cgid);
    if ($cgid === 0) {
        return NULL;
    }
    $where = "cgid=$cgid";
    $fields = array("cg_name");
    $client_group = DBAL::db_select_row(DBAL::db_sql_select("client_group", $fields, $where));
    return $client_group;
}

function db_client_update($cid, $changes) {
    return DBAL::update_table("machines", $changes, array("id" => $cid));
}

/**
 * Read $cid client's basic info, or client list if $cid is NULL
 */
function db_client_read($cid) {
    $fields = array(
        "id",
        "machineName AS name",
        "mac",
        "memo",
        "cpu_model",
        "memory_size",
        "disk_size",
        "download_KBS",
        "upload_KBS",
        "client_group",
        "client_token",
        "resolution",
        "usb_storage",
    );
    if (is_null($cid)) {
        return DBAL::db_select(DBAL::db_sql_select("machines", $fields));
    }
    $where = "id=$cid";
    return DBAL::db_select_row(DBAL::db_sql_select("machines", $fields, $where));
}

function db_seed_create($file_type, $file_path, $seed_path) {
    $file_size = shell_file_size($file_path, array("unit" => "b"));

    $result = DBAL::db_insert_table("seed", array(
        "file_type" => $file_type,
        "file_path" => $file_path,
        "file_size" => $file_size,
        "seed_path" => $seed_path,
    ));
    return $result;
}

function delete_seed($file_path) {
    $result = DBAL::do_select("SELECT id, seed_path FROM seed WHERE file_path='$file_path'");
    $count = $result->rowCount();
    if ($result) {
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if(!$result){
            return NULL;
        }
        $id = $result['id'];
        $seed_path = $result['seed_path'];
        shell_cmd(home_bin('tc-delivery-server', "del $id $seed_path"));
        DBAL::do_select("DELETE FROM seed WHERE id='$id'");
    }
    return $count;
}

function delete_all_seeds($image_id) {
    // delete all related history images' seeds
    $files = DBAL::do_select("SELECT s.file_path FROM seed s
        JOIN (SELECT * FROM imageupdatehistory WHERE imageId='$image_id') AS o
        ON s.file_path=o.newPath");
    if ($files) {
        while ($file = $files->fetch(PDO::FETCH_ASSOC)) {
            delete_seed($file["file_path"]);
        }
    }
    // delete all related UDF file seeds
    $files = DBAL::do_select("SELECT s.file_path FROM seed s
        JOIN (SELECT * FROM imageupdatehistory WHERE imageId='$image_id') AS o
        ON s.file_path=o.userDataPath");
    if ($files) {
        while ($file = $files->fetch(PDO::FETCH_ASSOC)) {
            delete_seed($file["file_path"]);
        }
    }
}

function seed_id($path) {
    $result = DBAL::do_select("SELECT id FROM seed WHERE file_path='$path'");
    if(!$result) {
        return NULL;
    }
    $result = $result->fetch(PDO::FETCH_ASSOC);
    if(!$result) {
        return NULL;
    }
    return $result['id'];
}

function delete_client_image($ciid) {
    $result = DBAL::do_select("DELETE FROM osimages WHERE id='$ciid'");
    if (!$result) {
        log_error("delete_client_image::".rest_error_mysql());
        return NULL;
    }
    return $result->fetchColumn();
}


?>
