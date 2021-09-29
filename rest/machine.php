<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

function handle_put($target, $params, $body) {
    $cid = array_shift($target);
    if (is_null($cid)) {
        return rest_error("e_operation_unsupported");
    }
    $cid = sugar_valid_int($cid);
    if (!$cid) {
        return rest_error("e_operation_unsupported");
    }
    $exist = DBAL::db_select_first_value(DBAL::db_sql_select("machines", array("COUNT(id)"), "id=$cid"));
    if (intval($exist) != 1) {
        return  rest_error_extra("e_database", 0, "e_id_not_found");
    }
    if (!is_array($body)){
        return rest_error("e_operation_unsupported");
    }
    log_info(json_encode($body));

    // running client check
    if (array_key_exists("name", $body)
        || array_key_exists("mac", $body) ) {
        if (client_is_busy($cid)) {
            return rest_error("e_client_busy");
        }
    }

    $download_MBS = db_safe_array_get("download_MBS", $body);
    $upload_MBS = db_safe_array_get("upload_MBS", $body);
    if (!is_null($download_MBS) && !is_null($upload_MBS)) {
        if (!is_numeric($download_MBS) || sugar_decimals_length($download_MBS) > 1) {
            return rest_error("e_invalid_download_mbs");
        }
        if (!is_numeric($upload_MBS) || sugar_decimals_length($upload_MBS) > 1) {
            return rest_error("e_invalid_upload_mbs");
        }
        $download = floatval($download_MBS) * 1000;
        if (!sugar_int_range(
            $download,
            constant("TC_CLIENT_DOWNLOAD_KBS_MIN"),
            constant("TC_CLIENT_DOWNLOAD_KBS_MAX")
        )) {
            return rest_error("e_invalid_download_mbs");
        }
        $upload = floatval($upload_MBS) * 1000;
        if (!sugar_int_range(
            $upload,
            constant("TC_CLIENT_UPLOAD_KBS_MIN"),
            constant("TC_CLIENT_UPLOAD_KBS_MAX")
        )) {
            return rest_error("e_invalid_upload_mbs");
        }
        $res = db_client_update($cid,
            array(
                "download_KBS" => $download,
                "upload_KBS"=> $upload,
            )
        );
        if (is_null($res)) {
            return rest_error_mysql();
        }
        return rest_result_ok();
    }
    // save autoboot user name
    $ab_name = DBAL::db_select_first_value(DBAL::db_sql_select("machines", array("machineName"), "id=$cid"));
    $ab_name = $ab_name . constant("TC_AUTOBOOT_SUFFIX");
    $ab_uid = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("id"), "userName='$ab_name'"));
    $set = array();
    foreach($body as $k => $v) {
        if ($k === "name") {
            if(sugar_valid_alnum($v, TC_CLIENT_NAMING_EX) && strlen($v) <= TC_CLIENT_NAME_MAX) {
                array_push($set, "machineName='$v'");
            } else {
                return rest_error("e_bad_client_name");
            }
        } else if ($k === "usb_storage") {
            // $v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            if($v == "true" || $v == "false"){
                $v = ($v == "true") ? 1 : 0;
            } else {
                return rest_error("e_invalid_usb_storage");
            }
            // log_info($v);
            // $v = $v ? 1 : 0;
            array_push($set, "usb_storage='$v'");
            send_cc_command_cid($cid, "--enable-usb-storage $v");
        } else if ($k === "resolution") {
            if(preg_match("/[\d]{3,5}x[\d]{3,5}$/", $v)) {
                $nums = explode("x", $v);
                $width = $nums[0];
                $height = $nums[1];
                if(!sugar_valid_int($width, 100, 100000)) {
                    return rest_error("e_client_resolution");
                }
                if(!sugar_valid_int($height, 100, 100000)) {
                    return rest_error("e_client_resolution");
                }
                array_push($set, "resolution='$v'");
                send_cc_command_cid($cid, "--set-resolution --width $width --height $height");
            } else {
                return rest_error("e_client_resolution");
            }
        } else if ($k === "mac") {
            if (sugar_valid_mac(trim($v))) {
                array_push($set, "mac='$v'");
            } else {
                return rest_error("e_bad_client_mac");
            }
        } else if ($k === "memo") {
            if (mb_strlen($v) > TC_CLIENT_MEMO_MAX) {
                return rest_error("e_bad_client_memo");
            } else {
                array_push($set, "memo='$v'");
            }
        } else {
            if(is_array($v)){
                return rest_error("e_operation_unsupported");
            }
            array_push($set, "$k='$v'");
        }
    }
    if (empty($set)) {
        return rest_error("e_operation_unsupported");
    }
    $set = implode(',', $set);
    try {
        $result = DBAL::do_select("UPDATE machines SET $set WHERE id='$cid'");
        if (!$result) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return  rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }

    // update autoboot user name
    $new_machine_name = DBAL::db_select_first_value(DBAL::db_sql_select("machines", array("machineName"), "id=$cid"));
    $username = $new_machine_name . constant("TC_AUTOBOOT_SUFFIX");
    $ab_gid = constant("TC_DEFAULT_GID_AUTOBOOT");
    $sha256ed = hash("sha256", constant('TC_AUTOBOOT_PWD').":$username");
    $auth = user_auth_create($username, $sha256ed);
    $changes = array(
        "userName" => $username,
        "printName" => $username,
        "password" => $auth["hash_result"],
        "salt" => $auth["salt"],
        "sha256ed" => $auth["sha256ed"],
    );
    log_info("update ab-user, $ab_uid, $ab_gid, $username");
    $result = NULL;
    try {
        $where = array(
            "id" => $ab_uid,
            "groupId" => $ab_gid,
        );
        $result = DBAL::update_table("users", $changes, $where);
    } catch (PDOException $e) {
        return  rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }

    if (is_null($result)) {
        return rest_error_mysql($result);
    }
    return rest_result_ok();
}

function handle_del($target, $params, $body) {
    $mid = array_shift($target);
    if (is_null($mid)) {
        return rest_error("e_operation_unsupported");
    }
    $cid = $mid;
    $cid = sugar_valid_int($cid);
    if (!$cid) {
        return rest_error("e_operation_unsupported");
    }
    $m = DBAL::db_select_row(DBAL::db_sql_select("machines", array("machineName", "mac"), "id=$mid"));
    if (is_null($m)) {
        return rest_error("e_client_not_found");
    }
    $mac = $m['mac'];
    $u = DBAL::db_select(DBAL::db_sql_select("userstatus", "id", "mac='$mac'"));
    if (is_null($u)) {
        return rest_error("e_client_not_found");
    }
    if (!empty($u)) {
        return rest_error('e_client_busy');
    }

    // delete autoboot user first
    $ab_name = $m['machineName'] . constant("TC_AUTOBOOT_SUFFIX");
    $ab_gid = constant("TC_DEFAULT_GID_AUTOBOOT");
    $result = NULL;
    try {
        $result = DBAL::db_delete("users", "userName='$ab_name' AND groupId=$ab_gid");
        if (is_null($result)) {
            return rest_error("e_user_not_found");
        }
        // delete client info from DB table
        $result = DBAL::db_delete("machines", "id='$mid'");
        if (empty($result)) {
            return rest_error("e_client_not_found");
        }
    } catch (PDOException $e) {
        return  rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }

    // delete ethers if exist
    network_ethers_delete($mac);
    return rest_result_ok($result);
}

function handle_post($target, $params, $body) {
    // add license check for client count
    $count = DBAL::db_select_first_value(DBAL::db_sql_select('machines', array("COUNT('id')")));
    if (!file_exists(constant("TC_LIC_LIC"))) {
        return rest_error("e_invalid_license");
    }
    $res = run_bin('tc-license', "--get-client-count");
    if (is_cmd_fail($res)) {
        return rest_error_shell($res);
    }
    $max_count = substr($res['last_line'], strpos($res['last_line'], ":") + 1);
    if (intval($count) >= intval($max_count)) {
        return rest_error("e_lic_count_max");
    }
    if (!is_array($body)){
        return rest_error("e_operation_unsupported");
    }
    $browser = db_safe_array_get("browser", $body);
    if (is_null($browser) || !$browser) {
        if(!load_configuration_bool("client_open_registration")) {
            return rest_error("e_operation_denied");
        }
    }

    $name = db_safe_array_get_string("name", $body, TC_CLIENT_NAMING_EX);
    if (is_null($name) || strlen($name) > TC_CLIENT_NAME_MAX) {
        return rest_error("e_bad_client_name");
    }

    $mac = strtoupper(db_safe_array_get("mac", $body));
    if (is_null($mac) || !sugar_valid_mac($mac)) {
        return rest_error("e_bad_client_mac");
    }

    $memo = db_safe_array_get("memo", $body, "");
    if (is_null($memo) || mb_strlen($memo) > TC_CLIENT_MEMO_MAX) {
        return rest_error("e_bad_client_memo");
    }

    $client = array(
        "machineName" => $name,
        "mac" => $mac,
        "memo" => $memo,
        "cpu_model" => db_safe_array_get("cpu_model", $body),
        "disk_size" => db_safe_array_get("disk_size", $body),
        "memory_size" => db_safe_array_get("memory_size", $body),
        "firmware" => db_safe_array_get("firmware_version", $body),
    );

    $client_id = NULL;
    try {
        $client_id = DBAL::db_insert_table("machines", $client);
        if (is_null($client_id)) {
            return rest_error_mysql("e_duplicate_value");
        }
    } catch (PDOException $e) {
        return  rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }


    update_client_token($client_id);

    // create autoboot user
    $username = $name.constant("TC_AUTOBOOT_SUFFIX");
    $sha256ed = hash("sha256", constant('TC_AUTOBOOT_PWD').":$username");
    $auth = user_auth_create($username, $sha256ed);
    $fields = array(
        "userName" => $username,
        "printName" => $name.constant("TC_AUTOBOOT_SUFFIX"),
        "groupId" => constant('TC_DEFAULT_GID_AUTOBOOT'),
        "password" => $auth["hash_result"],
        "salt" => $auth["salt"],
        "sha256ed" => $auth["sha256ed"],
        "isApproved" => TRUE,
    );
    $result = NULL;
    try {
        $result = DBAL::db_insert_table("users", $fields);
    } catch (PDOException $e) {
        return  rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }
    if (is_null($result)) {
        $res = DBAL::db_delete_row("machines", $client_id);
        log_warning("Fail to create autoboot user for client $client_id");
        return rest_error_mysql("e_fail_register_user");
    }
    return rest_result_ok($client_id);
}

function filter_unregistered($client) {
    return empty($client["client_id"]);
}

function filter_registered($client) {
    return !empty($client["client_id"]);
}

function online_clients($reg_filter) {
    $fmt = "
        SELECT u.ip, u.client_status, u.last_login, u.heartbeat,
            c.id AS 'client_id',
            c.machineName AS 'client_name',
            uu.printName AS 'user',
            o.imageName AS 'image',
            u.cpu_model AS 'cpu_model',
            u.memory_size AS 'memory_size',
            u.disk_size AS 'disk_size',
            c.firmware AS 'firmware',
            u.mac AS 'mac_addr',
            c.client_group
        FROM userstatus u
        LEFT JOIN machines c ON c.mac=u.mac
        LEFT JOIN users uu ON uu.id=u.userId
        LEFT JOIN osimages o ON u.osImageId=o.id
        ORDER BY c.machineName";

    $result = DBAL::db_select_old($fmt);
    if (is_null($result)) {
        return rest_error_mysql();
    }
    foreach ($result as &$client) {
        foreach ($client as $key => $value) {
            if (is_null($value)) {
                 $client[$key] = "";
            }
        }
        $client['heartbeat_delay'] = time() - $client['heartbeat'];
        $client['heartbeat'] = strftime("%T", $client['heartbeat']);
        $client["client_group_name"] = client_group_name($client["client_group"]);
    }
    if ($reg_filter) {
        $result = array_filter($result, "filter_registered");
    } else {
        $result = array_filter($result, "filter_unregistered");
    }
    $total_count = DBAL::db_select_first_value(DBAL::db_sql_select(
        "machines", "COUNT(id)"
    ));
    return rest_result_ok(array(
        'clients' => array_values($result),
        'total_count' => $total_count,
    ));
}

function handle_get($target, $params, $body) {
    $online = safe_array_get('online', $params);
    if (!is_null($online)) {
        $reg = safe_array_get('reg', $params, true);
        return online_clients($reg);
    }
    // empty target requests for whole machine list
    $cid = array_shift($target);
    if (!is_null($cid)) {
        $cid = sugar_valid_int($cid);
        if (!$cid) {
            return rest_error("e_operation_unsupported");
        }
    }
    $clients = client_read($cid);
    if (is_null($clients)) {
        return rest_error_mysql();
    }
    return rest_result_ok($clients);
}


$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_del", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>