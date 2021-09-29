<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require("rest.inc.php");
require("../libs/libtc.php");

function handle_get($target, $params, $body) {
    $result = read_peer_history(
        db_safe_array_get("event", $params),
        db_safe_array_get("user", $params),
        db_safe_array_get("peer", $params)
    );
    if (is_null($result)) {
        return rest_error("e_operation_unsupported");
    }
    return rest_result_ok($result);
}

auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
);
rest_start_loop($handlers);

?>
