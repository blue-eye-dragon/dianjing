<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

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
    $sql = "SELECT s.file_path AS file_path, s.file_size AS file_size, o.imageName AS image_name
            FROM seed s
            LEFT JOIN osimages o ON o.path=s.file_path
            WHERE s.file_type='img'";
    $result = DBAL::do_select($sql);
    if (!$result) {
        return rest_error_mysql();
    }
    $seeds = array();
    while ($row = mysql_fetch_assoc($result)) {
        $seeds[] = $row;
    }
    return rest_result_ok($seeds);
}

auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
