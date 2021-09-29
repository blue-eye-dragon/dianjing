<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

ini_set('memory_limit', '256M');


function handle_get($target, $params, $body) {
    // no auth check here, for client firmware automated update
    $fw = array();

    $firmware_log = $GLOBALS['_TC2_']['home'] . '/tftpboot/rootfs_log';
    if (is_readable($firmware_log)) {
        $file = new SplFileObject($firmware_log);
        while (!$file->eof()) {
            $fw['filesystem'][] = $file->fgets();
        }
        $file = null;
    }
    return rest_result_ok($fw);
}

function build_error($i18n, $message, $result_file) {
    log_error("$i18n, $message");
    $error = rest_error($i18n);
    if (!is_null($result_file)) {
        file_put_contents($result_file, json_encode($error));
    }
    return $error;
}

function handle_post($target, $params, $body) {
    // auth check for firmware updating
    auth_login_required();

    $result_file = NULL;
    if (!empty($body) && array_key_exists("result_id", $body)) {
        $result_id = $body["result_id"];
        $result_file = tc_conf_path_join("TC_VAR", $result_id);
        if (file_exists($result_file)) {
            $res = file_get_contents($result_file);
            shell_delete($result_file);
            return rest_result_ok($res);
        }
        return rest_error();
    }
    $result_id = array_shift($target);
    if (!is_null($result_id)) {
        $result_file = tc_conf_path_join("TC_VAR", $result_id);
    }
    $uploaded = rest_save_upload2();
    if (is_null($uploaded)) {
        return build_error("e_upload_file_empty", "firmware file not found", $result_file);
    }
    $size_limit = constant("TC_CLIENT_FIRMWARE_SIZE");
    if ($uploaded["file_size"] > $size_limit) {
        return build_error("e_firmware_size", "firmware file size exceeds $size_limit", $result_file);
    }
    $fw_file = basename($uploaded["file_path"]);
    $fw_file = escapeshellcmd($fw_file);
    $fw_file = tc_conf_path_join("TC_UPLOAD_PATH", $fw_file);
    log_info("decompress firmware file $fw_file");

    $phase = constant("TC_FIRMWARE_PWD");
    $result = run_bin_as_root("tc-config", "fw.upgrade $fw_file $phase");
    if (is_cmd_fail($result)) {
        return build_error("e_fail_update_firmware", "invalid firmware file", $result_file);        
    }
    if (!is_null($result_file)) {
        file_put_contents($result_file, json_encode(rest_result_ok()));
    }
    return rest_result_ok();
}


$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
);
rest_start_loop($handlers);

?>
