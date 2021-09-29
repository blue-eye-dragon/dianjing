<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require('rest.inc.php');
require("../libs/libtc.php");

function handle_put($target, $params, $body) {
    $pid = intval(array_shift($target));
    //check if data backup to selected peer is ongoing
    if (peer_is_busy($pid)) {
        return rest_error("e_backup_peer_busy");
    }
    $changes = array();
    $name = db_safe_array_get("name", $body);
    if ($name) {
        $changes["name"] = $name;
    }
    $ip_addr = db_safe_array_get("ip_addr", $body);
    $valid = sugar_valid_ip4($ip_addr);
    if (!$valid) {
        return rest_error('e_bad_ip_address');
    } else {
         $changes["ip_addr"] = $ip_addr;
    }
    if (empty($changes)) {
        return rest_error("e_operation_unsupported");
    }
    $result = peer_update($pid, $changes);
    if (is_null($result)) {
        $auth = current_session_auth();
        log_peer_update($pid, $auth['uid'], json_encode($body));
        return rest_error_mysql();
    }
    return rest_result_ok();
}

function handle_del($target, $params, $body) {
    $peer_id = intval(array_shift($target));
    //check if data backup to selected peer is ongoing
    if (peer_is_busy($peer_id)) {
        return rest_error("e_backup_peer_busy");
    }
    $result = peer_delete($peer_id) ? rest_result_ok() : rest_error_mysql();
    if ($result['success']) {
        $auth = current_session_auth();
        log_peer_delete($peer_id, $auth['uid']);
    }
    return $result;
}

function handle_post($target, $params, $body) {
    $pid = intval(array_shift($target));

    if ($pid) {
        // post on single peer, perform a ping test
        $peer = peer_read($pid);
        if (is_null($peer)) {
            return rest_error("e_peer_not_found");
        }
        if (is_cmd_fail(run_bin_as_root("tc-ar", "ping ".$peer["ip_addr"]))) {
            return rest_error("e_remote_ssh_tunnel");
        }
        return rest_result_ok();
    }

    $name = db_safe_array_get("name", $body);
    $addr = db_safe_array_get("ip_addr", $body);

    $valid = sugar_valid_ip4($addr);
    if (!$valid) {
        return rest_error('e_bad_ip_address');
    }

    $res = run_bin("network", "ip");
    if(sugar_ip4_op_compare($res["last_line"], $valid) === 0) {
        log_warning("Cannot create a peer with same address, $valid");
        return rest_error("e_peer_addr_reserved");
    }

    $fields = array("name" => $name, "ip_addr" => $valid);
    $peer_id = NULL;
    try {
        $peer_id = DBAL::db_insert_table("peer", $fields);
        if (is_null($peer_id)) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }
    $auth = current_session_auth();
    log_peer_create($peer_id, $auth['uid']);
    return rest_result_ok($peer_id);
}

function handle_get($target, $params, $body) {
    $peers = peer_read(0);
    if (is_null($peers)) {
        return rest_error_mysql();
    }
    return rest_result_ok($peers);
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_del", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
