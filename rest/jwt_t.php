<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
//require("jwt.php");
require("../libs/libtc.php");

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
        //$jwt = new Jwt();

        //get token
        $payload=array('sub'=>'1234567890','name'=>'John Doe','iat'=>1516239022);

         //$jwtAuth = JWTAuth::getInstance();
         run_as_root("chmod +x ./jwt");
         $token = run_as_root("./jwt -secret=http://ldm.lenovo.com -id=61470a70e42ee57542d643a0");
        
        //$token = $jwt->getToken($payload);

        //decode token
        //$uid = $jwt->verifyToken($token);
	//return rest_result_ok($uid);
        return rest_result_ok($token['last_line']);
}

//auth_login_required();
session_start();

$handlers = array(
    "POST" => array("handle_post", "log_info"),
);
rest_start_loop($handlers);

?>
