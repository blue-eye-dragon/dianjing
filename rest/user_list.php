<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

function user_storage($uid, $gid) {
    if ($gid <= load_configuration_int("root_gid")) {
        return NULL;
    }

    $storage = array();
    $group_ps = DBAL::db_select_row(DBAL::db_sql_select('group_storage', array("id", "image_size"), "group_id=$gid"));
    if (!is_null($group_ps)) {
        $storage['uri'] = $group_ps['id']."_$uid"."_$gid";
        $storage['group_size'] = $group_ps['image_size'];
    }
    $where = "user_id='$uid' AND revision='1'";
    $alloc_size = DBAL::db_select_first_value(DBAL::db_sql_select("private_storage", array("image_size"), $where));
    $psid = private_storage_read_psid_head($uid);
    $fields = array('image_file', 'image_size', 'udf_file', 'mdf_file', 'revision');
    $file_ps = DBAL::db_select_row(DBAL::db_sql_select('private_storage', $fields, "id=$psid"));
    if (is_null($file_ps) || !$file_ps) {
        $storage['user_size'] = 0;
        $storage["revision"] = 0;
    } else {
        $storage['user_size'] = $alloc_size;
        $storage['image_file'] = $file_ps['image_file'];
        $storage['image_size'] = $file_ps['image_size'];
        $storage['mdf_file'] = $file_ps['mdf_file'];
        $storage['udf_file'] = $file_ps['udf_file'];
        $storage['revision'] = $file_ps['revision'];
    }

    // $storage["image_size"] = rest_value_int($storage["image_size"]);
    // $storage["group_size"] = rest_value_int($storage["group_size"]);
    // $storage["user_size"] = rest_value_int($storage["user_size"]);
    // $storage["revision"] = rest_value_int($storage["revision"]);
    $storage = rest_value_obj($storage);

    return array($storage);
}

function group_filter($u) {
    $root_gid = load_configuration_int("root_gid");
    return intval($u["group_id"]) >= $root_gid;
}
function handle_get($target, $params, $body) {
    $uid = array_shift($target);
    $root_gid = load_configuration_int("root_gid");
    $ps_bg_upload_global = load_configuration_int("ps_background_upload");

    if (is_null($uid)) {
        // filter internal groups
        $users = user_read_u();
        if (is_null($users)) {
            return rest_error_mysql();
        }
        $users = array_filter($users, "group_filter");
        foreach ($users as &$user) {
            $user["is_admin"] = (intval($user["group_id"]) == $root_gid);
            $user["online"] = user_is_busy($user["id"]);
            $user["storage_lock"] = user_ps_is_locked($user["id"]);

            $user["storage"] = user_storage($user["id"], $user["group_id"]);
            $user["storage_background_upload_global"] = $ps_bg_upload_global;

            $group = user_group_read($user["group_id"]);
            $user["group_name"] = $group["group_name"];
            $user["storage_background_upload"] = $group["ps_background_upload"];
            $user = rest_value_obj($user);
        }

        return rest_result_ok($users);
    }

    $uid = sugar_valid_int($uid);
    if(!$uid) {
        return rest_error("e_operation_unsupported");
    }
    $user = user_read($uid);
    if (is_null($user)) {
        return rest_error("e_operation_unsupported");
    }

    $user["online"] = user_is_busy($user["id"]);
    $user["storage_lock"] = user_ps_is_locked($user["id"]);
    $user["storage"] = user_storage($user["id"], $user["group_id"]);
    $user["storage_background_upload_global"] = $ps_bg_upload_global;

    $group = user_group_read($user["group_id"]);
    $user["group_name"] = $group["group_name"];
    $user["storage_background_upload"] = $group["ps_background_upload"];

    return rest_result_ok(rest_value_obj($user));
}

//auth_login_required();
session_start();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
);
rest_start_loop($handlers);

?>
