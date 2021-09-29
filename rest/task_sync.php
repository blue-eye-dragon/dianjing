<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

function handle_post($target, $params, $body) {
    $uid = intval(safe_array_get("uid", $_SESSION));
    if ($uid === 0) {
        return rest_error("e_login_required");
    }

    $pid = intval(db_safe_array_get('peer', $body));
    $file = db_safe_array_get('file', $body);

    if ($pid === 0) {
        return rest_error("e_peer_not_found");
    }
    $peer = peer_read($pid);
    $remote = $peer["ip_addr"];
    // ping first, before real connection
    // file specified means real sync or just a connection test
    if (is_cmd_fail(run_bin_as_root("tc-ar", "ping $remote"))) {
        return rest_error('e_remote_ssh_tunnel');
    }

    $remote_file = basename(append_timestamp($file));
    $location = "$remote:/opt/tci/upload/sync/$remote_file";
    $stat = tc_relpath(constant("TC_SYNC_STAT_PATH"));
    $crt_image_peer = tc_relpath(constant("TC_CURRENT_SYNC_IMAGE_PEER"));

    // Deny 2nd sync task when previous sync task is ongoing
    if (file_exists($stat) OR file_exists($crt_image_peer)) {
        return rest_error("e_backup_busy");
    }
    // Log start sync infor to db
    $message = '<font color="green"><b>START<\/b><\/font>  sending '. $file. ' to '. $remote;
    log_peer_sync($pid, $uid, "$message");
    file_put_contents($crt_image_peer, json_encode($body)."\n");
    // Start sync
    $result = run_bin_as_root("tc-ar", "push remote $file $location $stat");

    if (is_cmd_fail($result)) {
        $message = '<font color="red"><b>FAILED<\/b><\/font>   to send '. $file. ' to '. $remote;
        log_peer_sync($pid, $uid, $message);
        shell_delete($stat);
        shell_delete($crt_image_peer);
        return rest_error("e_backup_data");
    }

    $message = '<font color="green"><b>FINISH<\/b><\/font> sending '. $file. ' to '. $location;
    log_peer_sync($pid, $uid, $message);
    shell_delete($stat);
    shell_delete($crt_image_peer);
    return rest_result_ok($result);
}

auth_login_required();

$handlers = array(
    "POST" => array("handle_post", "log_info"),
);
rest_start_loop($handlers);

?>
