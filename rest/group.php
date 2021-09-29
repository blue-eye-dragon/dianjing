<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");


function handle_put($target, $params, $body) {
    $gid = array_shift($target);
    if (is_null($gid)) {
        return rest_error("e_operation_unsupported");
    }
    $gid = sugar_valid_int($gid);
    if (!$gid) {
        return rest_error("e_operation_unsupported");
    }

    $where = array("id" => $gid);

    if (array_key_exists("name", $body)) {
        $name = db_safe_array_get("name", $body);
        if (!sugar_mb_strlen_range($name, 1, TC_USER_GROUP_NAME_MAX)) {
            return rest_error("e_group_name");
        }

        try {
            $res = DBAL::update_table("usergroups", array("groupName" => $name), $where);
            if (is_null($res)) {
                return rest_error_mysql("e_group_edit");
            }
        } catch (PDOException $e) {
            return  rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
    }

    if (array_key_exists("desc", $body)) {
        $desc = db_safe_array_get("desc", $body);
        if (!sugar_mb_strlen_range($desc, 0, TC_USER_GROUP_DESC_MAX)) {
            return rest_error("e_group_desc");
        }
        $res = DBAL::update_table("usergroups", array("groupDesc" => $desc), $where);
        if (is_null($res)) {
            return rest_error_mysql("e_group_edit");
        }
    }

    if (array_key_exists("ps_background_upload", $body)) {
        $g_ps_bk_up = db_safe_array_get("ps_background_upload", $body);
        if (!(($g_ps_bk_up == -1) OR ($g_ps_bk_up == 0) OR ($g_ps_bk_up == 1))) {
            return rest_error("e_group_ps_background_upload");
        }
        try {
            $res = DBAL::update_table("usergroups", array("ps_background_upload" => $g_ps_bk_up), $where);
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        if (is_null($res)) {
            return rest_error_mysql("e_group_edit");
        }
    }

    return rest_result_ok();
}

function handle_del($target, $params, $body) {
    $gid = array_shift($target);
    if (is_null($gid)) {
        return rest_error("e_operation_unsupported");
    }
    $gid = sugar_valid_int($gid);
    if (!$gid) {
        return rest_error("e_operation_unsupported");
    }

    $where = "groupId='$gid'";

    $count = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("COUNT(id)"), $where));
    // make sure no user works on that image
    if (is_null($count)) {
        return rest_error_mysql("e_group_delete");
    }
    if (intval($count) > 0) {
        return rest_error("e_group_busy");
    }
    // delete it from database
    $res = DBAL::db_delete_row("usergroups", $gid);
    if (is_null($res)) {
        return rest_error_mysql("e_group_delete");
    }
    return rest_result_ok();
}

function handle_post($target, $params, $body) {
    $name = db_safe_array_get("name", $body);
    if (is_null($name) || !sugar_mb_strlen_range($name, 1, TC_USER_GROUP_NAME_MAX)) {
        return rest_error("e_group_name");
    }

    $desc = db_safe_array_get("desc", $body);
    if (is_null($desc) || !sugar_mb_strlen_range($desc, 0, TC_USER_GROUP_DESC_MAX)) {
        return rest_error("e_group_desc");
    }

    $result = NULL;
    try {
       $result = DBAL::db_insert_table("usergroups", array("groupName" => $name, "groupDesc" => addslashes($desc)));
    } catch (PDOException $e) {
        return  rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }
    return rest_result_ok($result);
}

function handle_get($target, $params, $body) {
    $root_gid = load_configuration_int("root_gid");
    $where = "g.id > $root_gid";

    $group_id = array_shift($target);
    if (!is_null($group_id)) {
        $group_id = sugar_valid_int($group_id);
        if (!$group_id) {
            return rest_error("e_operation_unsupported");
        }
        $where .= " AND g.id = $group_id";
    }

    $fmt = "SELECT g.id, g.groupName, g.groupDesc, g.createTime, g.ps_background_upload, COUNT(u.id) AS count
            FROM usergroups g
            LEFT JOIN users u ON u.groupId = g.id
            WHERE $where
            GROUP BY g.id";

    $result = DBAL::do_select($fmt);
    if (!$result) {
        return rest_error_mysql("e_fail_load_groups");
    }

    $embed = safe_array_get("embed", $params);
    $groups = array();
    $global_ps_background_upload = (load_configuration_bool("ps_background_upload")) ? "1" : "0";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $group = array(
            "id" => $row["id"],
            "name" => $row["groupName"],
            "desc" => $row["groupDesc"],
            "create_time" => $row["createTime"],
            "member_count" => $row["count"],
            "ps_background_upload" => $row["ps_background_upload"],
            "ps_background_upload_global" => $global_ps_background_upload,
        );
        $id_token = ",".$row["id"].",";
        if ($embed === "bootimage") {
            $group["bootimage"] = array();
            $images = DBAL::do_select("SELECT imageName FROM osimages WHERE INSTR(`groupRead`, '{$id_token}') > 0");
            if ($images) {
                while ($img = $images->fetch(PDO::FETCH_ASSOC)) {
                    $group["bootimage"][] = $img["imageName"];
                }
            }
            $group["bootimage_readable"] = array();
            $fields = array("id as image_id", "imageName as image_name", "imageDesc as image_desc");
            $rows = DBAL::db_select(DBAL::db_sql_select("osimages", $fields, "INSTR(`groupRead`, '{$id_token}') > 0"));
            if ($rows) {
                $group["bootimage_read"] = $rows;
            }
        }
        $groups[] = $group;
    }
    if ($embed === "autoboot") {
        $fields = array("id", "groupName AS name");
        $gid = constant("TC_DEFAULT_GID_AUTOBOOT");
        $where = "id='$gid'";
        $autoboot_group = DBAL::db_select_row(DBAL::db_sql_select("usergroups", $fields, $where));
        if (is_null($autoboot_group)) {
            return rest_error_mysql("e_fail_load_groups");
        }
        $groups[] = $autoboot_group;
    }

    return rest_result_ok($groups);
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
