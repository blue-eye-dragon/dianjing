<?php

/******************************************************************************
Copyright 2012 - 2021 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require('../libs/libtc.php');


function post_process_imgs($status) {
    // $status:
    //   0:  rename, from .demo.img to demo.img
    //   non-zero:  remove .demo.img
    // remove .vdf files as well

    $d = tc_conf_dir("TC_BOOT_IMAGE");
    foreach(scandir($d) as $img) {
        if ($img[0] != ".") {
            continue;
        }
        if (substr($img, -4) == ".vdf") {
            shell_change_owner("$d/$img");
            shell_delete("$d/$img");
            continue;
        }
        if (substr($img, -4) != ".img") {
            continue;
        }
        shell_change_owner("$d/$img");
        if (intval($status) == 0) {
            if (!rename("$d/$img", "$d/".substr($img, 1))) {
                log_warning("blobs: Cannot rename $d/$img");
            }
        }
        else {
            if (!unlink("$d/$img")); {
                log_warning("blobs: Cannot delete $d/$img");
            }
        }
    }
}


function handle_post($target, $params, $body) {
    if (array_key_exists("post-status", $body)) {
        post_process_imgs($body["post-status"]);
    }
    return rest_result_ok();
}



rest_start_loop(
    array(
        "POST" => array("handle_post", "log_info"),
    )
);

?>
