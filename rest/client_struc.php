<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");


function handle_put($target, $params, $body) {

    return rest_result_ok();
}

function handle_delete($target, $params, $body) {
    $mid = array_shift($target);
    if (is_null($mid)) {
        return rest_error("e_operation_unsupported");
    }
    $cid = $mid;
    $cid = sugar_valid_int($cid);
    if (!$cid) {
        return rest_error("e_operation_unsupported");
    }
    $u = DBAL::db_sql_select("node_topology", "id", "parent_node='$mid'");
    if ($u->rowCount() != 0) {
        return rest_error("e_machine_busy");
    }
    try {
        $res = DBAL::db_delete("node_topology", "id='$mid'");
        if (empty($res)) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return  rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok();
}

function handle_post($target, $params, $body) {

    $name = db_safe_array_get_string("name", $body, TC_CLIENT_NAMING_EX);
    log_info($name);
    if (is_null($name) || strlen($name) > TC_CLIENT_NAME_MAX) {
        return rest_error("e_bad_client_name");
    }

    $mac = strtoupper(db_safe_array_get("mac_addr", $body));
    if (is_null($mac) || !sugar_valid_mac($mac)) {
        return rest_error("e_bad_client_mac");
    }

    $ip_addr = db_safe_array_get("ip_addr", $body);
    if (is_null($ip_addr) || !sugar_valid_ip4($ip_addr)) {
        return rest_error("e_bad_ip_address");
    }

    $machine = array(
        "name" => $name,
        "node_type" => db_safe_array_get("type", $body),
        "mac_address" => $mac,
        "ip_address" => $ip_addr,
        "parent_node" => db_safe_array_get("pid", $body),
        "TTL" => 0, // TBD
    );
    $machine_id = NULL;
    try {
        $machine_id = DBAL::db_insert_table("node_topology", $machine);
        if (is_null($machine_id)) {
            return rest_error_mysql("e_database");
        }
    } catch (PDOException $e) {
        return  rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }

    return rest_result_ok($machine_id);
}

function handle_get($target, $params, $body) {
    $fmt = "SELECT *
            FROM node_topology";

    $result = DBAL::do_select($fmt);
    if (!$result) {
        return rest_error_mysql("e_fail_load_groups");
    }
    $groups = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $group = array(
            "id" => $row["id"],
            "name" => $row["name"],
            "type" => $row["node_type"],
            "mac_addr" => $row["mac_address"],
            "ip_addr" => $row["ip_address"],
            "pid" => $row["parent_node"],
            "ping" => $row["TTL"],
        );
        $groups[] = $group;
    }
    return rest_result_ok($groups);
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
