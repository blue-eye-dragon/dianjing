<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');

function handle_put($target, $params, $body) {
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

function handle_delete($target, $params, $body) {
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

function handle_post($target, $params, $body) {
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

function handle_get($target, $params, $body) {
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

/**
* Example: use log_info as the default logger
*/
rest_start_loop( array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "PUT" => array("handle_put", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
));

?>
