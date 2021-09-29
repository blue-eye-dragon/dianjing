<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");


function generate_qrcode($client_id) {
    $client = db_client_read($client_id);
    if ($client) {
        $data = json_encode($client);
        $folder = '/opt/tci/etc/qr';
        if (!file_exists($folder)) {
            shell_cmd("mkdir $folder");
        }
        $png = "$folder/$client_id.png";
        $result = shell_cmd("qrencode -o $png '$data'");
        return $png;
    }
    return NULL;
}

/**
 * download handler
 */
function download_handler($target, $params) {
    $target = array();
    if (array_key_exists('PATH_INFO', $_SERVER)) {
        $target = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    }
    $topic = array_shift($target);

    if ($topic === "network") {
        $password = constant("TC_DHCP_SETTINGS_PWD");
        $filepath = shell_last_line("/opt/tci/bin/network export $password");
        return $filepath;
    }

    if ($topic === "qrcode") {
        $id = array_shift($target);
        $filepath = generate_qrcode(intval($id));
        return $filepath;
    }

    if ($topic === "logs") {
        $timestamp = date("YmdHis", time());
        $log_name = "logs_$timestamp.tgz";
        $log_file = tc_conf_dir("TC_VAR")."/$log_name";

        $logs = array(
            "/opt/lampp/logs/*",
            "/var/log/messages",
            "/var/log/tc-*.log"
        );
        $cmd = "tar czf $log_file " . implode(" ", $logs);
        $result = run_as_root($cmd);
        return $log_file;        
    }

    return NULL;
}

auth_login_required();

rest_bind_file("download_handler");

?>
