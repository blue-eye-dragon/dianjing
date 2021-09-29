<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");
require("../system/libpage.php");

function handle_put($target, $params, $body) {
    $role_name = array_shift($target);

    $changes = array();

    if (array_key_exists("name", $body)) {
        $name = db_safe_array_get("name", $body);
        if (!sugar_mb_exceptions_strlen_range($name, 1, TC_ROLE_NAME_MAX, "-_")) {
            return rest_error("e_role_name");
        }
        $changes["name"] = $name;
    }

    if (array_key_exists("desc", $body)) {
        $desc = db_safe_array_get("desc", $body);
        if (!sugar_mb_exceptions_strlen_range($desc, 0, TC_ROLE_DESC_MAX, "-_")) {
            return rest_error("e_role_desc");
        }
        $changes["description"] = $desc;
    }

    if ($changes) {
        try {
            $role_id = DBAL::select_cell("role", "role_id", $role_name, "name");
            if (is_null($role_id)) {
                return rest_error("e_role_not_found");
            }
            $where = array("role_id" => $role_id);
            $result = DBAL::update_table("role", $changes, $where);
            return rest_result_ok($result);
        } catch (PDOException $e) {
            return rest_error_pdo_exception($e);
        }
    }


    return rest_error("e_operation_unsupported");
}

function handle_del($target, $params, $body) {
    $role_id = array_shift($target);
    if (is_null($role_id)) {
        return rest_error("e_operation_unsupported");
    }
    if (count($target)) {
        $topic = array_shift($target);
        $property_id = array_shift($target);
        if (is_null($topic) or is_null($property_id)) {
            return rest_error("e_operation_unsupported");
        }
        if ($topic == "url"){
            //delete authorized url
            $where = "allowed_url = \"" . $property_id . "\" AND role_id = \"" . $role_id ."\"";
            $res_durl = DBAL::db_delete("role_permission", $where);
            if (is_null($res_durl)) {
                return rest_error_mysql();
            }
            log_info("role.php DEL authorized url: " . json_encode($body));
            return rest_result_ok();
        }

        if ($topic == "group"){
            //delete authorized group
            $where = "group_id = \"" . $property_id . "\" AND role_id = \"" . $role_id ."\"";
            $res_dgroup = DBAL::db_delete("role_group_binds", $where);
            if (is_null($res_dgroup)) {
                return rest_error_mysql();
            }
            log_info("role.php DEL authorized group: " . json_encode($body));
            return rest_result_ok();
        }
    }

    //delete role: 1.delete role_permission rows
    $where1 = "role_id = '$role_id'";
    $res1 = DBAL::db_delete("role_permission", $where1);
    if (is_null($res1)) {
        return rest_error_mysql();
    }
    //delete role: 2.delete releated groups in role_group_binds
    $res2 = DBAL::db_delete("role_group_binds", $where1);
    if (is_null($res2)) {
        return rest_error_mysql();
    }
    //delete role: 3.delete role rows
    $res3 = DBAL::db_delete("role", $where1);
    if (is_null($res3)) {
        return rest_error_mysql();
    }
    log_info("role.php DEL role id: " . $role_id);
    return rest_result_ok();

}


function handle_post($target, $params, $body) {
    if (count($target) == 1){
        $topic = array_shift($target);
    } else {
        $role_id = array_shift($target);
        $topic = array_shift($target);
    }
    if (is_null($body)) {
        log_info("POST role.php 0");
        return rest_error("e_operation_unsupported");
    }

    //add url
    if ($topic == "url"){
        log_info("POST role.php add url");
        if (is_null($role_id) or is_null($body["element_id"])) {
            return rest_error("e_empty_url_id");
        }
        $url_fields = array("role_id" => $role_id, "allowed_url" => $body["element_id"]);

        try {
            $result = DBAL::db_insert_table("role_permission", $url_fields);
            if (is_null($result)) {
                return rest_error_mysql();
            }
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        return rest_result_ok($result);
    }

    if ($topic == "group"){
        log_info("POST role.php add group");
        if (is_null($role_id) or is_null($body["element_id"])) {
            return rest_error("e_empty_group_id");
        }
        $group_fields = array("role_id" => $role_id, "group_id" => $body["element_id"]);
        try {
            $result = DBAL::db_insert_table("role_group_binds", $group_fields);
            if (is_null($result)) {
                return rest_error_mysql();
            }
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        return rest_result_ok($result);
    }

    if ($topic == "role"){
        //add role: 1.name check
        $name = db_safe_array_get("name", $body);
        if (is_null($name) || $name === "") {
            return rest_error("e_empty_role_name");
        }
        if (!sugar_mb_exceptions_strlen_range($name, 1, TC_ROLE_NAME_MAX, "-_")) {
            return rest_error("e_role_name");
        }
        $desc = db_safe_array_get("desc", $body, "");
        if (!sugar_mb_exceptions_strlen_range($desc, 0, TC_ROLE_DESC_MAX, "-_")) {
            return rest_error("e_role_desc");
        }
        $role_urlids = $body["role_urlids"];
        $role_grpids = $body["role_grpids"];

        //add role: 2.save to db: role
        $role_id = substr(md5($name.rand()), 0, 4);
        $role_fields = array("role_id" => $role_id,"name" => $name,"description" => $desc,);

        try {
            $result = DBAL::db_insert_table("role", $role_fields);
            if (is_null($result)) {
                return rest_error_mysql();
            }
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }

        //add role: 3.save to db: role_group_binds
        if (count($role_grpids)) {
            $group_fields = array("role_id","group_id");
            $group_values = array();
            foreach ($role_grpids as $value) {
                $group_value = array();
                array_push($group_value, $role_id);
                array_push($group_value, $value);
                array_push($group_values, $group_value);
            }
            $rst_g = DBAL::db_insert_table_multiple("role_group_binds", $group_fields, $group_values);
            if (is_null($rst_g)) {
                return rest_error_mysql();
            }
        }
        //add role: 4.save to db: role_permission
        if (count($role_urlids)) {
            $permission_fields = array("role_id","allowed_url");
            $permission_values = array();
            foreach ($role_urlids as $value) {
                $permission_value = array();
                array_push($permission_value, $role_id);
                array_push($permission_value, $value);
                array_push($permission_values, $permission_value);
            }
            $rst_u = DBAL::db_insert_table_multiple("role_permission", $permission_fields, $permission_values);
            if (is_null($rst_u)) {
                return rest_error_mysql();
            }
        }
        log_info("POST role.php: " . json_encode($body));
        return rest_result_ok($role_id);
    }
}


function locale_category_title($link) {
    global $links;
    $lang = $GLOBALS["_TC2_"]["lang"];
    foreach ($links as $key => $value) {
        foreach ($value["links"] as $key2 => $value2) {
            if ($link == $key2) {
                return $value["title"][$lang];
            }
        }
    }
    return $link;
}

function role_details() {
    $fields_r = array("role_id, name, description, create_time");
    $result = DBAL::db_select(DBAL::db_sql_select("role", $fields_r, "role.create_time"));
    $roles = array();
    foreach ($result as $role){
        $r = array(
            "role_id" => $role["role_id"],
            "role_name" => $role["name"],
            "role_desc" => $role["description"],
            "role_time" => $role["create_time"],
            "allowed_group" => array(),
            "allowed_url" => array()
        );
        array_push($roles, $r);
    }
    $where_g = "role_group_binds.group_id = usergroups.id AND role.role_id = role_group_binds.role_id";
    $fields_g = "role.role_id, role_group_binds.group_id, usergroups.groupName, usergroups.groupDesc";
    // $fields_g = array("role.role_id, role_group_binds.group_id, usergroups.groupName, usergroups.groupDesc");
    // $result = DBAL::db_select(DBAL::db_sql_select("role, usergroups, role_group_binds", $fields_g, $where_g, "role_group_binds.create_time DESC"));
    $result = DBAL::do_select("SELECT " . $fields_g . " FROM " . "role, usergroups, role_group_binds WHERE " . $where_g . " ORDER BY role_group_binds.create_time DESC");
    foreach ($result as $role_group){
        $role_id = $role_group["role_id"];
        $rgid =$role_group["group_id"];
        foreach ($roles as $key => $value){
            if ($value["role_id"] == $role_id) {
                $roles[$key]["allowed_group"][$rgid]["group_name"] = $role_group["groupName"];
                $roles[$key]["allowed_group"][$rgid]["group_desc"] = $role_group["groupDesc"];
                continue 2;
            }
        }
    }
    $where_p = "role.role_id = role_permission.role_id";
    $fields_p = "DISTINCT role.role_id, role_permission.allowed_url";
    // $fields_p = array("DISTINCT role.role_id, role_permission.allowed_url");
    // $result = DBAL::db_select(DBAL::db_sql_select("role, role_permission", $fields_p, $where_p, "role_permission.create_time DESC"));
    $result = DBAL::do_select("SELECT " . $fields_p . " FROM " . "role, role_permission" . " WHERE " . $where_p . " ORDER BY role_permission.create_time DESC");
    foreach ($result as $role_pmsn){
        $role_id = $role_pmsn["role_id"];
        $alowed_url = $role_pmsn["allowed_url"];
        foreach ($roles as $key => $value){
            if ($value["role_id"] == $role_id) {
                $roles[$key]["allowed_url"][$alowed_url]["url_title"] = locale_title($alowed_url);
                $roles[$key]["allowed_url"][$alowed_url]["url_category_title"] = locale_category_title($alowed_url);
                continue 2;
            }
        }
    }
    return $roles;
}


function handle_get($target, $params, $body) {
    $roles = role_details();
    if ($roles === NULL) {
        return rest_error_mysql();
    }
    return rest_result_ok($roles);
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
