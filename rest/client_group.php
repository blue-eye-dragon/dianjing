<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");


function handle_put($target, $params, $body) {
    $cgid = array_shift($target);
    if (is_null($cgid)) {
        return rest_error("e_operation_unsupported");
    }
    $cgid = sugar_valid_int($cgid);
    if (!$cgid) {
        return rest_error("e_operation_unsupported");
    }

    // name check
    $name = db_safe_array_get("name", $body);
    $name_pcre = "/^[a-zA-Z0-9]{1,20}$/";
    if (!preg_match($name_pcre, $name)) {
        return rest_error("e_client_group_name");
    }

    //desc check
    $desc = db_safe_array_get("desc", $body, "");
    if (mb_strlen($desc) > TC_CLIENT_GROUP_DESC_MAX) {
        return rest_error("e_client_group_desc");
    }

    //autoboot check
    $autoboot = db_safe_array_get("autoboot", $body);
    $autoboot_pcre = "/^([1-9]\d*|0|-1)$/";
    if (!preg_match($autoboot_pcre, $autoboot)) {
        return rest_error("e_client_group_autoboot");
    }

    $fields = array(
        "cg_name" => $name,
        "cg_desc" => $desc,
        "cg_autoboot_ciid" => $autoboot,
    );

    // update clients in machine table
    try {
        $res = DBAL::update_table("client_group", $fields, array("cgid" => $cgid));
        if (is_null($res)) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return rest_error_pdo_exception($e);
    }
    return rest_result_ok();
}

function handle_delete($target, $params, $body) {
    $cgid = array_shift($target);
    if (is_null($cgid)) {
        return rest_error("e_operation_unsupported");
    }
    $cgid = sugar_valid_int($cgid);
    if (!$cgid) {
        return rest_error("e_operation_unsupported");
    }
    // delete client group row
    try {
        $res = DBAL::db_delete("client_group", "cgid='$cgid'");
        if (empty($res)) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return rest_error_pdo_exception($e);
    }

    try {
        $result = DBAL::update_table("machines", array("client_group" => "0"), array("client_group" => $cgid));
    } catch (PDOException $e) {
        return rest_error_pdo_exception($e);
    }
    if (empty($result)) {
        log_error("Cannot clear clients on machines client group for $cgid");
    }
    return rest_result_ok();
}

function handle_post($target, $params, $body) {
    // count check
    $max = constant("TC_CLIENT_GROUP_MAX");
    $count = DBAL::db_select_first_value(DBAL::db_sql_select("client_group", array("count(cgid)")));
    if (is_null($count)) {
        return rest_error_mysql();
    }
    if ($count >= $max) {
        log_error("Client group count $count, max $max");
        return rest_error("e_client_group_max");
    }
    // name check
    $name = db_safe_array_get("name", $body);
    $name_pcre = "/^[a-zA-Z0-9]{1,20}$/";
    if (!preg_match($name_pcre, $name)) {
        return rest_error("e_client_group_name");
    }

    //desc check
    $desc = db_safe_array_get("desc", $body, "");
    if (mb_strlen($desc) > TC_CLIENT_GROUP_DESC_MAX) {
        return rest_error("e_client_group_desc");
    }

    //autoboot check
    $autoboot = db_safe_array_get("autoboot", $body);
    $autoboot_pcre = "/^([1-9]\d*|0|-1)$/";
    if (!preg_match($autoboot_pcre, $autoboot)) {
        return rest_error("e_client_group_autoboot");
    }

    $fields = array(
        "cg_name" => $name,
        "cg_desc" => $desc,
        "cg_autoboot_ciid" => $autoboot,
    );

    $result = NULL;

    try {
       $result = DBAL::db_insert_table("client_group", $fields);
    } catch (PDOException $e) {
        return rest_error_pdo_exception($e);
    }
    return rest_result_ok($result);
}

function handle_get($target, $params, $body) {
    $cgid = array_shift($target);
    $fields = array(
        "cgid",
        "cg_name AS name",
        "cg_desc AS description",
        "cg_create_ts AS create_timestamp",
        "cg_autoboot_ciid AS autoboot_ciid",
    );
    if (empty($cgid)) {
        $result = DBAL::db_select(DBAL::db_sql_select("client_group", $fields));
        if (is_null($result)) {
            return rest_error_mysql();
        }
        foreach ($result as &$cg) {
            $cg["client_members"] = client_group_members($cg["cgid"]);
            $cg["autoboot_image"] = "";
            if (intval($cg["autoboot_ciid"])) {
                $cg["autoboot_image"] = db_client_image_read($cg["autoboot_ciid"], array("imageName"));
            }
        }
        return rest_result_ok($result);
    }

    $cgid = sugar_valid_int($cgid);
    if (!$cgid) {
        return rest_error("e_operation_unsupported");
    }
    $where = "cgid=$cgid";
    $result = DBAL::db_select_row(DBAL::db_sql_select("client_group", $fields, $where));
    if (is_null($result)) {
        return rest_error_mysql();
    }
    $result['client_members'] = client_group_members($result["cgid"]);
    if (intval($result["autoboot_ciid"])) {
        $result["autoboot_image"] = db_client_image_read($result["autoboot_ciid"], array("imageName"));
    }
    return rest_result_ok($result);
}

auth_login_required();

$handlers = array(
    "GET" => array("handle_get"),
    "PUT" => array("handle_put", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
);
rest_start_loop($handlers);

?>
