<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

if (!session_id()) session_start();

function login_page($profile) {
    $root_gid = load_configuration_int("root_gid");
    $group_id = $profile["group_id"];
    $reset_pwd = $profile["reset_pwd"];
    $url = "system/zpermission_denied.html";
    //Check function enabled or not: customized web console by user role when login
    if(intval($reset_pwd) === 1){
        $url = "system/dashboard_page.php";
    }else if (intval($group_id) === intval($root_gid)) {
        $url = "system/dashboard_page.php";
    } else if ($group_id > $root_gid) {
        $url = "system/dashboard_page.php";
    }
    return array("token" => $profile["access_token"], "url" => $url);
}

function handle_post($target, $params, $body) {
    $username = db_safe_array_get("username", $body);
    $cipher = db_safe_array_get("password", $body);

    log_info("Current session " . json_encode($_SESSION));
    if (is_null($username) || is_null($cipher)) {
        log_warning("Auth Error, missing name/password, $username/$cipher");
        return rest_error("e_fail_login");
    }

    $status_lock = user_lock_check($username);
    if ($status_lock) {
        log_warning("Auth Error, account locked, $username");
        return rest_error("e_account_locked");
    }

    $status_approved = user_approve_check($username);
    if (!$status_approved) {
        log_warning("Auth Error, need approval, $username");
        return rest_error("e_account_not_approved");
    }

    if (strlen($cipher) !== 64 || !sugar_valid_alnum($cipher)) {
        return rest_error("e_invalid_password");
    }

    $profile = user_auth_check($username, $cipher);
    if (is_null($profile)) {
        log_warning("Auth Error, auth check failed, $username/$cipher");
        return rest_error("e_fail_login");
    }

    // if (isset($_SESSION["uid"])) {
    //     // session exists here, check if the same user login
    //     if ($_SESSION["uid"] === $profile["id"]) {
    //         log_info("Auth session reused, uid " . $profile["id"]);
    //         return rest_result_ok(login_page($profile));
    //     }
    //     log_info("Auth session renew for uid " . $profile["id"]);
    //     do_session_delete();
    //     session_start();
    // }

    $_SESSION["uid"] = $profile["id"];
    $_SESSION["gid"] = $profile["group_id"];
    $_SESSION["user_name"] = $profile["user_name"];

    $token = update_user_access_token($profile["id"]);
    if (is_null($token)) {
        return rest_error_mysql("e_database");
    }
    $_SESSION["access_token"] = $token;
    $profile["access_token"] = $token;

    // try to update user pssword, after login
    $cipher_new = db_safe_array_get("password_new", $body);
    if (!is_null($cipher_new)) {
        if (strlen($cipher_new) !== 64 || !sugar_valid_alnum($cipher_new)) {
            return rest_error("e_invalid_password");
        }
        $res = user_update_password($_SESSION["uid"], $_SESSION["user_name"], $cipher_new);
        if (is_null($res)) {
            log_error("cannot reset user password in database");
            return rest_error("e_invalid_password");
        }
        return rest_result_ok();
    }

    return rest_result_ok(login_page($profile));
}

function do_session_delete() {
    $_SESSION = array();
    session_unset();
    $name = session_name();
    if (isset($_COOKIE[$name])) {
        $r = session_get_cookie_params();
        setcookie($name, "", time() - 3600, $r["path"], $r["domain"], $r["secure"], TRUE);
    }
    session_destroy();
}

function handle_delete($target, $params, $body) {
    $auth = current_session_auth();
    if ($auth["uid"] <= 0) {
        log_warning("Auth, delete no user session, " . json_encode($_SESSION));
    }

    do_session_delete();
    return rest_result_ok($auth["uid"]);
}

$handlers = array(
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
);
rest_start_loop($handlers);

?>
