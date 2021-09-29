<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

function do_merge($id, $merge, $reason) {
    client_image_change_status_merging($id);

    $revision = db_client_image_read($id, array('revision'));
    $username = "admin";

    $mfiles = client_image_files_mdf($id, $revision, $username);
    $ufiles = client_image_files_udf($id, $revision, $username);

    $mdf_file = $mfiles["pending"];
    $udf_file = $ufiles["pending"];

    if (file_exists($mdf_file)) {
        shell_change_owner($mdf_file);
    } else {
        log_error("Missing MDF, $mdf_file");
        client_image_change_status_ready($id);
        return rest_error("e_fail_merge_no_mdf");
    }

    if (file_exists($udf_file)) {
        shell_change_owner($udf_file);
    } else {
        log_error("Missing UDF, $udf_file");
        client_image_change_status_ready($id);
        return rest_error("e_fail_merge_no_udf");
    }

    $img_file = db_client_image_read($id, array("path"));
    shell_change_owner($img_file);
    $img_file_updated = $img_file;

    // disable this method and using rst method instead
    // save rev 1 as base file for restoring client image in future
    // ALL restore points are merged on demand based on the copy of rev 1
    if (constant("TC_COPY_IMAGE") && intval($revision) === 1) {
        $base_file = tc_conf_dir("TC_BOOT_IMAGE_OPT")."/$id.1";
        $result = shell_cmd("cp -a $img_file $base_file");
        if (is_cmd_fail($result)) {
            return rest_error("e_fail_merge");
        }
    }

    if ($merge == 'inline') {
        // do inline merge
        $merge_result = update_blockio_file($img_file, $mdf_file, $udf_file);
    } else {
        // not inline merge, will create a new file with revision number
        $original = DBAL::db_select_first_value(
            DBAL::db_sql_select("imageupdatehistory", array("newPath"), "imageId=$id AND revision=1"));
        $basename = basename($original, ".img");
        $index = $revision + 1;
        $bootimage_dir = tc_conf_dir("TC_BOOT_IMAGE");
        $img_file_updated = "$bootimage_dir/$basename.$index.img";
        $merge_result = update_blockio_file($img_file, $mdf_file, $udf_file, $img_file_updated);
    }

    // merge will print 'successfully' as the flag
    $pos = strpos($merge_result['last_line'], 'successfully');
    if ($pos === FALSE) {
        // merge failed, retry required
        $extra = str_replace('"', '\"', $merge_result['last_line']);
        client_image_change_status_ready($id);
        return rest_error_extra('e_fail_merge', 0, $extra);
    }

    // update seed for IMG file
    // delete the seed because image is changed
    delete_seed($img_file);

    $dir = tc_conf_dir("TC_USER_DATA");
    $dir = "$dir/$id/$revision";
    shell_mkdir($dir);

    // move all MDF/UDF to long-term storage folder
    shell_rename($mdf_file, $mfiles["archive"]);
    shell_rename($udf_file, $ufiles["archive"]);
    shell_rename($mfiles["rst_pending"], $mfiles["rst_archive"]);
    shell_rename($ufiles["rst_pending"], $ufiles["rst_archive"]);

    // update image revision/autoboot_revision for this change
    db_client_image_update($id, array(
        "revision" => intval($revision) + 1,
        "autoboot_revision" => intval($revision) + 1,
    ));

    $fields = array("path", "revision");
    $updated = DBAL::db_select_row(DBAL::db_sql_select("osimages", $fields, "id=$id"));
    $fields = array(
        "imageId" => $id,
        "newPath" => $img_file_updated,
        "oldPath" => $updated['path'],
        "revision" => $updated['revision'],
        "previous" => $revision,
        "memo" => $reason,
        "userDataPath" => $ufiles["archive"],
        "metaDataPath" => $mfiles["archive"],
        "rstUserDataPath" => $ufiles["rst_archive"],
        "rstMetaDataPath" => $mfiles["rst_archive"],
        "uuid" => shell_uuid(),
    );
    // update imageupdatehistory table
    $result = DBAL::db_insert_table("imageupdatehistory", $fields);
    if (is_null($result)) {
        log_error("Cannot create image history, " . json_encode($fields));
    }

    $files = array($img_file_updated, $mfiles["archive"], $ufiles["archive"]);
    // deprecated: reduce MDF/UDF size based on IMG file
    // cost too much time and break the progress reporting
    #run_bin_as_root("tc-tune-mudf", implode(" ", $files));
    // use p2p service to speed up UDF pulling from client
    seed_create("udf", $ufiles["archive"], $ufiles["name"]);
    seed_create("udf", $ufiles["rst_archive"], $ufiles["rst_name"]);
    seed_create('img', $img_file_updated, basename($img_file_updated));

    db_client_image_update($id, array("path" => $img_file_updated));

    client_image_change_status_ready($id);
    return rest_result_ok($fields);
}

function handle_post($target, $params, $body) {
    $image_id = array_shift($target);
    if (is_null($image_id)) {
        log_error("task_merging: no client image id");
        return rest_error('e_operation_unsupported');
    }

    //Check restore point quota limitation
    $rp_quota = db_client_image_read($image_id, array("revision_quota"));
    $image_rp_count = client_image_history_count($image_id);
    $img_rev_min_id = client_image_history_oldest_id($image_id);
    if ($image_rp_count >= $rp_quota) {
        return rest_error_extra("c_restore_point_revision_max", "", array('img_rev_min_id' => $img_rev_min_id));
    }

    // merging request
    $merge = safe_array_get('merge', $body);
    if (is_null($merge)) {
        log_error("task_merging: no merge type");
        return rest_error('e_operation_unsupported');
    }

    log_info("task_merging: merge image $image_id, $merge");
    $flag = db_client_image_read($image_id, array("needUpdate"));
    if (is_null($flag)) {
        log_error("task_merging: client image status read error");
        return rest_error('e_db_osimages');
    }
    if ($flag == 1) {
        $reason = db_safe_array_get('reason', $body);
        $result = do_merge($image_id, $merge, $reason);
        // reset all user revisions to 1
        update_user_revision(NULL, $image_id, 1, "auto reset");
        return $result;
    } else if ($flag == 2) {
        log_error("task_merging: client image status is updating");
        return rest_error('e_image_updating');
    }
    return rest_result_ok();
}

function handle_get($target, $params, $body) {
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
);
rest_start_loop($handlers);

?>
