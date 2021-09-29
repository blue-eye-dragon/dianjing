<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);
require('rest.inc.php');
require("../libs/libtc.php");


function handle_get($target, $params, $body) {
    $topic = array_shift($target);
    $stat = tc_relpath(constant("TC_SYNC_STAT_PATH"));
    $crt_image_peer = tc_relpath(constant("TC_CURRENT_SYNC_IMAGE_PEER"));

    if ($topic === "progress") {
        // default progress is 100% in order to hide progress bar on clients
        $progress = array("trans_pct" => "100%");

        if (file_exists($stat)) {
            $result = run_bin_as_root("tc-ar", "stat $stat");
            foreach ($result['output'] as $line) {
                // empty line is always in the end
                if (empty($line)) {
                    continue;
                }
                $progress = parse_rsync_progress($line);
            }
        }
        if (file_exists($crt_image_peer)) {
            $result = run_bin_as_root("tc-ar", "stat $crt_image_peer");
            foreach ($result['output'] as $line) {
                // empty line is always in the end
                if (empty($line)) {
                    continue;
                }
                $progress = array_merge($progress, json_decode($line, true));
                $peer = peer_read($progress["peer"]);
                $progress["peer_addr"] = $peer["ip_addr"];
                $progress["peer_name"] = $peer["name"];
            }
        }

        return rest_result_ok($progress);
    }
    return rest_result_ok();
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
);
rest_start_loop($handlers);

?>
