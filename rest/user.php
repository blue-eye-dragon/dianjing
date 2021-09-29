<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

function handle_put($target, $params, $body) {
    $auth = auth_login_required();
    if (is_null($auth)) {
        log_warning("edit user without auth, " . json_encode($body));
        return rest_error("e_login_required");
    }

    $uid = array_shift($target);
    if (is_null($uid)) {
        // change user's password with admin's password
        // that is, override user's password
        // only support admin group overrides passwords
        if ($auth["gid"] !== load_configuration_int("root_gid")) {
            return rest_error("e_login_required");
        }

        $cipher_auth = db_safe_array_get("cipher_auth", $body);
        if (strlen($cipher_auth) !== 64 || !sugar_valid_alnum($cipher_auth)) {
            return rest_error("e_invalid_password");
        }

        $cipher_user = db_safe_array_get("cipher_user", $body);
        if (strlen($cipher_user) !== 64 || !sugar_valid_alnum($cipher_user)) {
            return rest_error("e_invalid_password");
        }

        $profile = user_auth_password_check($auth["user_name"], $cipher_auth);
        if ($profile["authorized"]) {
            $uid = db_safe_array_get("uid", $body);
            if (sugar_valid_int($uid)) {
                $uid = intval($uid);
            } else {
                return rest_error("e_operation_unsupported");
            }
            if (user_is_busy($uid)) {
                log_warning("change busy user's passowrd");
                return rest_error("e_user_busy");
            }

            $user_auth = user_auth_create("", $cipher_user);
            $fields = array(
                "password" => $user_auth["hash_result"],
                "salt" => $user_auth["salt"],
                "sha256ed" => $cipher_user,
            );

            try {
                $result = DBAL::update_table("users", $fields, array("id" => $uid));
                if (!$result) {
                    log_warning("failed to change user's passowrd");
                    continue;
                }
            } catch (PDOException $e) {
                return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
            }
            return rest_result_ok();
        }

        return rest_error("e_invalid_password");
    }

    // update current session ciid, no need to check busy or not
    $ciid = db_safe_array_get("ciid", $body);
    if (!is_null($ciid) && sugar_valid_int($ciid)) {
        $_SESSION["ciid"] = intval($ciid);
        log_info("SESSION changed, " . json_encode($_SESSION));
        return rest_result_ok($ciid);
    }

    $uid = sugar_valid_int($uid);
    if (!$uid) {
        return rest_error("e_operation_unsupported");
    }
    // make sure the user is not busy
    if (user_is_busy($uid)) {
        return rest_error("e_user_busy");
    }
    $user = user_read($uid);
    if (is_null($user)) {
        return rest_error("e_operation_unsupported");
    }
    $edit_admin = (intval($user["group_id"]) === load_configuration_int("root_gid"));
    $edit_self = ($auth["uid"] === $uid);
    $admin_override = ($auth["gid"] === load_configuration_int("root_gid"));
    // admin user can override normal users properties
    if (!$edit_self && !$admin_override) {
        log_warning("edit user without valid auth, self $edit_self, override $admin_override");
        return rest_error("e_login_required");
    }

    // special permissions for admin users
    // disable user property editing
    // update ps_background_upload of usergroups table
    if (!$edit_admin && array_key_exists("ps_background_upload", $body)) {
        $val = db_safe_array_get("ps_background_upload", $body);
        if (!(($val == -1) OR ($val == 0) OR ($val == 1))) {
            return rest_error("e_ps_background_upload");
        }

        try {
            $group_id = DBAL::select_cell("users", "groupId", $uid, "id");
            $res = DBAL::update_table("usergroups", array("ps_background_upload" => $val), array("id" => $group_id));
            if (!$res) {
                return rest_error_mysql("e_group_edit");
            }
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        return rest_result_ok();

    }

    // generate KEY=VALUE,KEY=VALUE string for SQL SET sentence
    $changes = array();
    if (!$edit_admin && array_key_exists("enable", $body)) {
        $changes["isApproved"] = filter_var($body['enable'], FILTER_VALIDATE_BOOLEAN);
    }
    if (!$edit_admin && array_key_exists('group', $body)) {
        $changes["groupId"] = intval($body['group']);
    }
    if (array_key_exists('password', $body)) {
        $password = db_safe_array_get("password", $body);
        if (strlen($password) !== 64 || !sugar_valid_alnum($password)) {
            return rest_error("e_invalid_password");
        }

        $password_new = db_safe_array_get("password_new", $body);
        if (strlen($password_new) !== 64 || !sugar_valid_alnum($password_new)) {
            return rest_error("e_invalid_password");
        }

        $result = user_auth_password_check($auth["user_name"], $password);
        if (is_null($result) || !$result["authorized"]) {
            return rest_error("e_invalid_password");
        }
        $reset_pwd = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("reset_password"), "id=$uid"));
        if(intval($reset_pwd) === 1){
            $changes["reset_password"] = 0;
        }
        $user_auth = user_auth_create($auth["user_name"], $password_new);
        $changes["password"] = $user_auth["hash_result"];
        $changes["salt"] = $user_auth["salt"];
        $changes["sha256ed"] = $password_new;
    }
    if (!$edit_admin && array_key_exists('storage_frozen', $body)) {
        $val = filter_var($body['storage_frozen'], FILTER_VALIDATE_BOOLEAN);
        if (is_null($val)) {
            return rest_error("e_invalid_format_boolean");
        }
        if (user_ps_is_locked($uid)) {
            return rest_error("e_ps_busy");
        }
        $changes["storage_frozen"] = ($val ? "true" : "false");
    }
    if (!$edit_admin && array_key_exists('bind_image', $body)) {
        $val = filter_var($body['bind_image'], FILTER_VALIDATE_INT);
        if (is_null($val)) {
            return rest_error("e_operation_unsupported");
        }
        $changes["bind_image_id"] = $val;
    }
    if (!$edit_admin && array_key_exists('bind_client', $body)) {
        $val = filter_var($body['bind_client'], FILTER_VALIDATE_INT);
        if (is_null($val)) {
            return rest_error("e_operation_unsupported");
        }
        $changes["bind_client_id"] = $val;
    }
    if (!$edit_admin && array_key_exists('pname', $body)) {
        $changes["printName"] = db_safe_array_get("pname", $body);
    }

    if (empty($changes)) {
        return rest_error("e_operation_unsupported");
    }
    // apply this changes with SQL query
    $res = user_update($uid, $changes);
    if (is_null($res)) {
        return rest_error_mysql();
    }
    return rest_result_ok($res);
}

function handle_del($target, $params, $body) {
    $uid = intval(array_shift($target));

    $topic = array_shift($target);
    if ($topic === "storage") {
        $rev = array_shift($target);
        if (user_ps_is_locked($uid)) {
            return rest_error("e_ps_busy");
        }
        $err = private_storage_delete($uid, $rev);
        if ($err) {
            return rest_error($err);
        }
        return rest_result_ok($uid);

        $psid = private_storage_read_psid_head($uid);
        if (is_null($psid)) {
            log_error("Cannot find head psid for $uid");
            return rest_error_mysql();
        }
        $ps = private_storage_read($psid);
        if (is_null($ps)) {
            log_error("Cannot find ps record for $uid");
            return rest_error_mysql();
        }
        if (intval($ps["revision"]) === 1) {
            return rest_result_ok($psid);
        }
        // insert a new ps record to reset the revision to 1
        $fields = array(
            "user_id" => $uid,
            "image_file" => $ps["image_file"],
            "image_size" => $ps["image_size"], // in MiB
            "mdf_file" => "",
            "udf_file" => "",
            "revision" => 1,
        );
        try {
            $psid = DBAL::db_insert_table('private_storage', $fields);
            if (is_null($psid)) {
                return rest_error_mysql();
            }
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        return rest_result_ok($psid);
    }

    // make sure no user works on that image
    if (user_is_busy($uid)) {
        return rest_error("e_user_busy");
    }
    // prevent injection
    $uid = sugar_valid_int($uid);
    if (!$uid) {
        return rest_error("e_operation_unsupported");
    }
    $user = user_read($uid);
    if (is_null($user) || intval($user["group_id"]) === load_configuration_int("root_gid")) {
        return rest_error("e_operation_unsupported");
    }

    // delete it from database
    $result = user_delete($uid);
    if (is_null($result)) {
        return rest_error_mysql();
    }
    return rest_result_ok($result);
}

function create_user_batch($body) {
    $group_id = $body['group'];
    $enabled = filter_var($body['enable'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    $prefix = db_safe_array_get('batch_prefix', $body, "");
    $suffix = db_safe_array_get('batch_suffix', $body, "");
    $first = db_safe_array_get('batch_first', $body, "");
    $count = db_safe_array_get('batch_count', $body, "");
    $width = db_safe_array_get('batch_width', $body, "");

    log_info("$prefix, $suffix, $first, $count, $width");

    if (!empty($prefix) && !ctype_alnum(str_replace('_', 'a', $prefix))) {
        return rest_error('e_batch_prefix');
    }

    if (!empty($suffix) && !ctype_alnum(str_replace('_', 'a', $suffix))) {
        return rest_error('e_batch_suffix');
    }

    if (!ctype_digit($first) || intval($first) < 0 || intval($first) > 99999999) {
        return rest_error('e_batch_first');
    }

    if (!ctype_digit($count) || intval($count) < 1) {
        return rest_error('e_batch_count');
    }

    if ((PHP_INT_MAX - intval($first)) <= $count) {
        return rest_error('e_batch_overflow');
    }

    if (!ctype_digit($width) || intval($width) < 1) {
        return rest_error('e_batch_width');
    }

    // pass number format check
    $first = intval($first);
    $count = intval($count);
    $width = intval($width);
    if (strlen($prefix) + strlen($suffix) + $width > TC_CLIENT_NAME_MAX) {
        return rest_error('e_batch_name');
    }
    if ($count > load_configuration_int('user_batch_max')) {
        return rest_error("e_batch_count_max");
    }
    $last = $first + $count - 1;
    if ($width < strlen("$last")) {
        return rest_error('e_batch_width_small');
    }

    $names = sugar_expand_regex($prefix, $suffix, $first, $count, $width);

    $rows = array();
    // for batch creation, use user name as the default password
    foreach ($names as $name) {
        $sha256ed = hash("sha256", "$name:$name");
        $auth = user_auth_create($name, $sha256ed);
        $rows[] = array($name, $name, $group_id, $auth["hash_result"], $enabled, $auth["salt"], $sha256ed, 1);
    }
    $keys = array("userName", "printName", "groupId", "password", "isApproved", "salt", "sha256ed", "reset_password");

    try {
        $result = DBAL::db_insert_table_multiple("users", $keys, $rows);
        if (is_null($result)) {
            return rest_error_mysql('e_fail_register_user');
        }
    } catch (PDOException $e) {
        return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok();
}

function create_admin($body) {
    $username = 'admin';
    $password = db_safe_array_get("password", $body);

    $auth = user_auth_create($username, $password);

    $fields = array(
        "userName" => $username,
        "printName" => 'administrator',
        "groupId" => 3,
        "password" => $auth["hash_result"],
        "salt" => $auth["salt"],
        "isApproved" => 1,
        "sha256ed" => $auth["sha256ed"],
        "reset_password" => 0,
    );

    try {
       $result = DBAL::db_insert_table("users", $fields);
        if (is_null($result)) {
            return rest_error_mysql('e_fail_register_user');
        }
    } catch (PDOException $e) {
        return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok($result);
}


function create_user($body) {
    $group_id = db_safe_array_get("group", $body);
    $password = db_safe_array_get("password", $body);
    $enabled = db_safe_array_get("enable", $body);
    $name = db_safe_array_get("name", $body);
    $print_name = db_safe_array_get("display", $body);

    $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    if (is_null($group_id) || is_null($password) || is_null($enabled)
        || is_null($name) || is_null($print_name)) {
        log_error("failed to create user, lack fields");
        return rest_error("e_fail_register_user");
    }

    $auth = user_auth_create($name, $password);

    $fields = array(
        "userName" => $name,
        "printName" => $print_name,
        "groupId" => $group_id,
        "password" => $auth["hash_result"],
        "salt" => $auth["salt"],
        "isApproved" => $enabled,
        "sha256ed" => $auth["sha256ed"],
        "reset_password" => 1,
    );

    try {
       $result = DBAL::db_insert_table("users", $fields);
        if (is_null($result)) {
            return rest_error_mysql('e_fail_register_user');
        }
    } catch (PDOException $e) {
        return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok($result);
}

function create_ps($uid, $size) {
    $img_file = tc_path_join($uid, sugar_date_now().".img");
    $fields = array(
        "user_id" => $uid,
        "image_file" => $img_file,
        "image_size" => $size,
    );

    try {
        $psid = DBAL::db_insert_table('private_storage', $fields);
        if (is_null($psid)) {
            return rest_error_mysql();
        }

        $size = intval($size) / 1024;
        $abs_img_file = tc_conf_path_join("TC_PS_ROOT", $img_file);
        $res = run_bin_as_root("tc-ps", "create $size $abs_img_file");
        if (is_cmd_fail($res)) {
            $dbres = DBAL::delete_row("private_storage", $psid);
            if (is_null($dbres)) {
                log_error("Cannot cleanup private_storage record after tc-ps failure");
            }
            return rest_error_shell($res);
        }
        shell_change_owner(tc_conf_dir("TC_PS_ROOT"));

        // $res = run_bin_as_root("tc-ps", "commit $size $uid");
        // if (is_cmd_fail($res)) {
        //     return rest_error_shell($res);
        // }
        $ps_prefix_src = tc_relpath("opt/ps/");
        $mdf_name = $size."G.img-$uid.mdf";
        $udf_name = $size."G.img-$uid.udf";

        shell_copy("$ps_prefix_src".$size."G.img-guest.mdf", tc_conf_path_join("TC_PS_UPLOAD", $mdf_name));
        shell_copy("$ps_prefix_src".$size."G.img-guest.udf", tc_conf_path_join("TC_PS_UPLOAD", $udf_name));

        merge_ps($uid, "head", $mdf_name, $udf_name, 1);

        $psid = private_storage_read_psid_head($uid);
        return rest_result_ok($psid);

    } catch (PDOException $e) {
        return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
    }
}

function merge_ps($uid, $uri, $mdf, $udf, $rev) {
    // take required permissions
    shell_change_owner(tc_conf_dir('TC_PS_ROOT'));
    shell_change_owner(tc_conf_dir('TC_PS_UPLOAD'));

    $psid = private_storage_read_psid_head($uid);
    if (is_null($psid)) {
        return rest_error_mysql('e_ps_db_storage');
    }
    $fields = array("revision", "image_file");
    $res = DBAL::db_select_row(DBAL::db_sql_select('private_storage', $fields, "id=$psid"));
    // only same revision can be mreged together
    if (intval($res['revision']) != intval($rev)) {
        return rest_error('e_ps_invalid_revision');
    }
    $img_rel = $res['image_file'];
    $img = tc_conf_path_join("TC_PS_ROOT", $img_rel);
    $mdf = tc_conf_path_join("TC_PS_UPLOAD", $mdf);
    $udf = tc_conf_path_join("TC_PS_UPLOAD", $udf);

    $merge_result = update_blockio_file($img, $mdf, $udf);
    if (is_cmd_fail($merge_result)) {
        return rest_error_shell($merge_result);
    }
    // PS relative path, like 1/1
    $ps_rel = tc_path_join($uid, $rev);
    $ps_abs = tc_conf_path_join("TC_PS_ROOT", $ps_rel);
    shell_mkdir($ps_abs);
    shell_rename($mdf, tc_path_join($ps_abs, basename($mdf)));
    shell_rename($udf, tc_path_join($ps_abs, basename($udf)));
    shell_rename("$mdf.rst", tc_path_join($ps_abs, basename("$mdf.rst")));
    shell_rename("$udf.rst", tc_path_join($ps_abs, basename("$udf.rst")));

    // increase the revision number
    $rev = intval($rev) + 1;
    $fields = array(
        "user_id" => $uid,
        "image_file" => $img_rel,
        "image_size" => shell_file_size($img), // in MiB
        "mdf_file" => tc_path_join($ps_rel, basename($mdf)),
        "udf_file" => tc_path_join($ps_rel, basename($udf)),
        "rst_mdf_file" => tc_path_join($ps_rel, basename("$mdf.rst")),
        "rst_udf_file" => tc_path_join($ps_rel, basename("$udf.rst")),
        "revision" => $rev,
    );
    try {
        $psid = DBAL::db_insert_table('private_storage', $fields);
        if (is_null($psid)) {
            return rest_error_mysql();
        }
    } catch (PDOException $e) {
        return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok($psid);
}

function handle_post($target, $params, $body) {
    $uid = array_shift($target);
    if (is_null($uid)) {
        if (array_key_exists('is_batch', $body)) {
            $batch = filter_var($body['is_batch'], FILTER_VALIDATE_BOOLEAN) ? TRUE : FALSE;
            if ($batch) {
                return create_user_batch($body);
            }
        } else if (array_key_exists('is_admin', $body)) {
            $admin = filter_var($body['is_admin'], FILTER_VALIDATE_BOOLEAN) ? TRUE : FALSE;
            if ($admin) {
                return create_admin($body);
            }
        }
        return create_user($body);
    }

    $status = db_safe_array_get("status", $body);
    if (!is_null($status)) {
        if ($status === "lock") {
            $token = db_safe_array_get("token", $body);
            $where = array(
                "id" => $uid,
                "storage_lock" => ""
            );
            $result = DBAL::update_table("users", array("storage_lock" => $token), $where);
            $where = "id='$uid' AND storage_lock='$token'";
            $res = DBAL::db_select_row(DBAL::db_sql_select("users", array("id"), $where));
            if (is_null($res)) {
                return rest_error("e_ps_lock");
            }
            return rest_result_ok();
        } elseif ($status === "unlock") {
            $token = db_safe_array_get("token", $body);
            $where = array(
                "id" => $uid,
                "storage_lock" => $token,
            );
            $result = DBAL::update_table("users", array("storage_lock" => ""), $where);

            $where = "id='$uid' AND storage_lock=''";
            $res = DBAL::db_select_row(DBAL::db_sql_select("users", array("id"), $where));
            if (is_null($res)) {
                return rest_error("e_ps_unlock");
            }
            return rest_result_ok();
        } elseif ($status === "reset") {
            $where = "id='$uid'";
            DBAL::update_table("users", array("storage_lock" => ""), array("id" => $uid));

            $where = "id='$uid' AND storage_lock=''";
            $res = DBAL::db_select_row(DBAL::db_sql_select("users", array("id"), $where));
            if (is_null($res)) {
                return rest_error("e_ps_reset");
            }
            return rest_result_ok();
        }
        return rest_error("e_operation_unsupported");
    }

    $uri = db_safe_array_get('uri', $body);
    $size = db_safe_array_get('size', $body);
    if (is_null($size)) {
        $mdf = db_safe_array_get('mdf', $body);
        $udf = db_safe_array_get('udf', $body);
        $rev = db_safe_array_get('revision', $body);
        if (!is_null($uri) && !is_null($mdf) && !is_null($udf) && !is_null($rev)) {
            return merge_ps($uid, $uri, $mdf, $udf, $rev);
        }
        return rest_error('e_operation_unsupported');
    }
    return create_ps($uid, $size);
}

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
        $users = user_read();
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
if (!session_id()) session_start();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_del", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
