<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

function handle_get($target, $params, $body) {
    $sha256ed = db_user_sha256ed_query("admin");
    return rest_result_ok($sha256ed);
}

//auth_login_required();
if (!session_id()) session_start();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
);
rest_start_loop($handlers);

?>
