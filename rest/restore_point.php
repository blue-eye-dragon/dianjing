<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require('../libs/libtc.php');


function handle_delete($target, $params, $body) {
    $rpid = array_shift($target);
    if (is_null($rpid)) {
        return rest_error("e_operation_unsupported");
    }
    $rpid = sugar_valid_int($rpid);
    if (!$rpid) {
        return rest_error("e_operation_unsupported");
    }

    // query RP information
    $rp = db_restore_point_read($rpid);
    if (is_null($rp)) {
        return rest_error_mysql("e_restore_point_not_found");
    }

    $ciid = $rp['ciid'];
    if (client_image_is_busy($ciid) || client_image_is_merging($ciid)) {
        return rest_error("e_client_image_busy");
    }

    $ci_revision = DBAL::select_cell("osimages", "revision", $ciid);
    if (is_null($ci_revision)) {
        return rest_error('e_restore_point_delete');
    }
    $ci_revision = intval($ci_revision);
    $rp_revision = intval($rp['revision']);

    if ($ci_revision == $rp_revision) {
        log_info("Cannot delete the current revision from restore point list, ciid $ciid, cirev $ci_revision, rprev $rp_revision");
        return rest_error('e_restore_point_delete');
    }

    //    $oldest_rp = client_image_history_oldest($ciid);
    $oldest_rp_revision = DBAL::select_cell("imageupdatehistory", "MIN(revision)", $ciid, "imageId");
    if (is_null($oldest_rp_revision)) {
        log_info("cannot find the lowest revision");
        return rest_error("e_restore_point_delete");
    }
    $oldest_rp_revision = intval($oldest_rp_revision);
    // if ($rp_revision > intval($oldest["revision"])) {
    //     return rest_error("e_restore_point_delete_middle");
    // }

    $count = client_image_history_count($ciid);
    if (is_null($count)) {
        log_error("Cannot find image history for $ciid");
        return rest_error_mysql("e_restore_point_not_found");
    }
    if (intval($count) === 1) {
        return rest_error('e_restore_point_delete');
    }

    for($iter = $oldest_rp_revision; $iter <= $rp_revision; $iter++) {
        $rpid = db_restore_point_id($ciid, $iter);
        if (!is_null($rpid)) {
            $result = restore_point_delete($rpid);
            if (is_null($result)) {
                log_error("Cannot delete RP $rpid, ciid $ciid, rev $iter");
                return rest_error('e_restore_point_delete');
            }
        }
    }
    return rest_result_ok();
}

function rp_loading_progress($rpid) {
    return tc_conf_dir("TC_BOOT_IMAGE_OPT") . "/$rpid." . constant("TC_RP_LOADING");
}

function restore_point_restore($rp) {
    $ciid = $rp['ciid'];
    client_image_change_status_merging($ciid);

    $img_path = db_client_image_read($ciid, array("path"));
    if (constant("TC_COPY_IMAGE")) {
        $img_backup = $img_path . constant("TC_EXT_BACKUP");
        $result = shell_cmd_short("mv $img_path $img_backup");
        if (is_cmd_fail($result)) {
            log_error("Cannot move client image $ciid to $img_backup");
            client_image_change_status_ready($ciid);
            return rest_error("e_shell");
        }
    }

    if (constant("TC_COPY_IMAGE")) {
        // set the seed status to RAW to stop next read from tc-sync-server
        $changes = array("status" => "raw");
        $where = array(
            "file_type" => "img",
            "file_path" => $img_path,
        );
        try {
            DBAL::update_table("seed", $changes, $where);
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        $progress = rp_loading_progress($rp["rpid"]);
        $img_rp = tc_conf_dir("TC_BOOT_IMAGE_OPT")."/$ciid.1";
        $result = shell_cmd(home_bin2("tc-cp", "$progress $img_rp $img_path"));
        if (is_cmd_fail($result)) {
            log_error("Cannot copy client image $ciid to $img_path");
            shell_cmd_short("mv $img_backup $img_path");
            client_image_change_status_ready($ciid);
            return rest_error("e_shell");
        }

        // apply all UDF/MDF
        for ($iter = 1; $iter < $rp["revision"]; $iter++) {
            $mdf = archived_mdf($ciid, $iter);
            $udf = archived_udf($ciid, $iter);
            $result = update_blockio_file($img_path, $mdf, $udf);
            if (is_cmd_fail($result)) {
                log_error("Cannot apply revision $iter to ciid $ciid");
                shell_cmd_short("mv $img_backup $img_path");
                client_image_change_status_ready($ciid);
                return rest_error_shell($result);
            }
        }
    } else {
        //
        // Ascending revisions in update history, restore to lower revision only
        // that is, restore point revision is always lower than image revision
        //
        $img_rev = intval(db_client_image_read($ciid, array("revision")));
        $rp_rev = intval($rp["revision"]);
        if ($rp_rev === $img_rev) {
            client_image_change_status_ready($ciid);
            return rest_result_ok();
        }

        if ($rp_rev > $img_rev) {
            log_error("Restore to revision $rp_rev, which is greater than $img_rev of $ciid");
            client_image_change_status_ready($ciid);
            return rest_error("e_restore_point_restore");
        }

        // set the seed status to RAW to stop next read from tc-sync-server
        $changes = array("status" => "raw");
        $where = array(
            "file_type" => "img",
            "file_path" => $img_path,
        );
        try {
            DBAL::update_table("seed", $changes, $where);
        } catch (PDOException $e) {
            return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
        }
        // create dir for new image update record
        $dir = tc_conf_dir("TC_USER_DATA");
        $dir = "$dir/$ciid/$img_rev";
        shell_mkdir($dir);
        // calculate all file names for client image and restore points
        $img_mfiles = client_image_files_mdf($ciid, $img_rev);
        $img_ufiles = client_image_files_udf($ciid, $img_rev);
        // alway copy RST of img.rev-1 to the UDF of img.rev
        $ptr_mfiles = client_image_files_mdf($ciid, $img_rev-1);
        $ptr_ufiles = client_image_files_udf($ciid, $img_rev-1);

        // collect udf file/size infor during image restore
        $rst_archive_cp_stat = $img_ufiles["archive"] . constant("TC_EXT_MERGING");
        $archive_cp_stat = $img_ufiles["rst_archive"] . constant("TC_EXT_MERGING");
        $rs_gap_ufiles = array(
            array(
                "file_path" => $ptr_ufiles["rst_archive"],
                "file_size" => shell_file_size($ptr_ufiles["rst_archive"]),
                "merge_stat" => $rst_archive_cp_stat,
            ),
            array(
                "file_path" => $ptr_ufiles["archive"],
                "file_size" => shell_file_size($ptr_ufiles["archive"]),
                "merge_stat" => $archive_cp_stat,
            ),
        );
        //$rs_gap_ufiles = array(
        //    $ptr_ufiles["rst_archive"] => shell_file_size($ptr_ufiles["rst_archive"]),
        //    $ptr_ufiles["archive"] => shell_file_size($ptr_ufiles["archive"]),
        //);
        if ($img_rev - $rp_rev > 1) {
            for ($iter = $img_rev-2; $iter >= $rp_rev; $iter--) {
                $iter_ufiles = client_image_files_udf($ciid, $iter);
                $iter_mfiles = client_image_files_mdf($ciid, $iter);
                $merge_stat = $dir . "/" . $iter . constant("TC_EXT_MERGING");
                array_push($rs_gap_ufiles, array(
                    "file_path" => $iter_ufiles["rst_archive"],
                    "file_size" => shell_file_size($iter_ufiles["rst_archive"]),
                    "merge_stat" => $merge_stat)
                );
            }
        }
        $rs_gap_ufiles["is_preparing"] = TRUE;
        $rs_gap_ufiles_stat = tc_relpath(constant("TC_RS_GAP_UFILES"));
        file_put_contents($rs_gap_ufiles_stat, json_encode($rs_gap_ufiles));

        if(is_cmd_fail(shell_copy($ptr_mfiles["rst_archive"], $img_mfiles["archive"]))) {
            log_error("Cannot copy file " . $ptr_mfiles["rst_archive"] . " => ". $img_mfiles["archive"]);
            client_image_change_status_ready($ciid);
            return rest_error("e_restore_point_restore");
        }
        $rs_cp_param = "push local " . $ptr_ufiles["rst_archive"] . " " . $img_ufiles["archive"] . " " . $rst_archive_cp_stat;
        if(is_cmd_fail(call_home_bin("tc-ar", $rs_cp_param))) {
            log_error("Cannot copy file " . $ptr_ufiles["rst_archive"] . " => ". $img_ufiles["archive"]);
            client_image_change_status_ready($ciid);
            return rest_error("e_restore_point_restore");
        }
        if(is_cmd_fail(shell_copy($ptr_mfiles["archive"], $img_mfiles["rst_archive"]))) {
            log_error("Cannot copy file " . $ptr_mfiles["archive"] . " => ". $img_mfiles["rst_archive"]);
            client_image_change_status_ready($ciid);
            return rest_error("e_restore_point_restore");
        }
        $rs_cp_param = "push local " . $ptr_ufiles["archive"] . " " . $img_ufiles["rst_archive"] . " " . $archive_cp_stat;
        if(is_cmd_fail(call_home_bin("tc-ar", $rs_cp_param))) {
            log_error("Cannot copy file " . $ptr_ufiles["archive"] . " => ". $img_ufiles["rst_archive"]);
            client_image_change_status_ready($ciid);
            return rest_error("e_restore_point_restore");
        }

        // if rp.rev + 1 = img.rev, only revert RST files
        // if the rev gap is greater than 1, merge RST accordingly
        // after the switch above, the UDF of img is the RST of rp
        if ($img_rev - $rp_rev > 1) {
            // need to merge these revisions into ONE revision
            for ($iter = $img_rev-2; $iter >= $rp_rev; $iter--) {
                $iter_ufiles = client_image_files_udf($ciid, $iter);
                $iter_mfiles = client_image_files_mdf($ciid, $iter);

                //write restore progress info to disk for bootimage2.php GET
                $merge_stat = $dir . "/" . $iter . constant("TC_EXT_MERGING");

                $result = archive_client_image_changes_progress(
                    $merge_stat,
                    $img_mfiles["archive"], $img_ufiles["archive"],
                    $iter_mfiles["rst_archive"], $iter_ufiles["rst_archive"]
                );
                if (is_cmd_fail($result)) {
                    log_error("Cannot archive MDF/UDF " . $iter_ufiles["rst_archive"] . " => ". $img_ufiles["archive"]);
                    client_image_change_status_ready($ciid);
                    return rest_error_shell($result);
                }
            }
            $rs_gap_ufiles["is_preparing"] = FALSE;
            file_put_contents($rs_gap_ufiles_stat, json_encode($rs_gap_ufiles));
            $all_stat_files = $dir . "/". "*.merging";
            shell_delete($all_stat_files);
            shell_delete($rs_gap_ufiles_stat);
        }
        // do real image update with archived UDF/MDF
        shell_change_owner(tc_conf_dir("TC_USER_DATA"));
        $result = update_blockio_file($img_path, $img_mfiles["archive"], $img_ufiles["archive"]);
        if (is_cmd_fail($result)) {
            log_error("Cannot update client image $img_path " . $img_mfiles["archive"] . ", ". $img_ufiles["archive"]);
            client_image_change_status_ready($ciid);
            return rest_error_shell($result);
        }
        // create a new update record in history with increased revision
        // restoring means an update with inverse changes
        $memo = "Restore from revision $rp_rev";
        if ($GLOBALS["_TC2_"]["lang"] === "zh") {
            $memo = "还原自版本 $rp_rev";
        }
        $his = client_image_history($ciid, $rp_rev);
        $memo .= ": " . $his["memo"];
        $fields = array(
            "imageId" => $ciid,
            "newPath" => $img_path,
            "oldPath" => $img_path,
            "revision" => $img_rev + 1,
            "previous" => $rp_rev,
            "memo" => $memo,
            "userDataPath" => $img_ufiles["archive"],
            "metaDataPath" => $img_mfiles["archive"],
            "rstUserDataPath" => $img_ufiles["rst_archive"],
            "rstMetaDataPath" => $img_mfiles["rst_archive"],
            "uuid" => shell_uuid(),
        );
        // update imageupdatehistory table
        $result = DBAL::db_insert_table("imageupdatehistory", $fields);
        if (is_null($result)) {
            log_error("Cannot create image history, " . json_encode($fields));
            client_image_change_status_ready($ciid);
            return rest_error_shell($result);
        }

        seed_create("udf", $img_ufiles["archive"], $img_ufiles["name"]);
        seed_create("udf", $img_ufiles["rst_archive"], $img_ufiles["rst_name"]);
    }

    // delete the old seed and add again
    delete_seed($img_path);
    seed_create("img", $img_path, basename($img_path));
    // restore DB records for osimages
    db_client_image_update($ciid, array(
        "revision" => $img_rev + 1,
        "autoboot_revision" => $img_rev + 1,
    ));
    client_image_change_status_ready($ciid);

    if (constant("TC_COPY_IMAGE")) {
        shell_cmd_short("rm -f $img_backup");
    }

    return rest_result_ok();
}
/**
 * POST     /rest/restore_point.php/:rpid   restore/load a restore point
 *
 * Steps
 *   1. Move IMG into backup file, with suffix .backup
 *   2. Copy opt/diskimage/<cidd>.<rev> as the base IMG
 *   3. Apply UDF/MDF one by one according to revision
 *   4. Delete backup file, or restore backup file if error found
 *   5. Update p2p tracker with new seed
 */
function handle_post($target, $params, $body) {
    $rpid = array_shift($target);
    if (is_null($rpid)) {
        return rest_error("e_operation_unsupported");
    }
    $rpid = sugar_valid_int($rpid);
    if (!$rpid) {
        return rest_error("e_operation_unsupported");
    }

    // query RP information
    $rp = db_restore_point_read($rpid);
    if (is_null($rp)) {
        return rest_error_mysql("e_restore_point_not_found");
    }

    $ciid = $rp['ciid'];
    $count = client_image_history_count($ciid);
    if (is_null($count)) {
        log_error("Cannot find image history for $ciid");
        return rest_error_mysql("e_restore_point_not_found");
    }
    if (intval($count) === 1) {
        return rest_error("e_restore_point_restore");
    }

    if (client_image_is_busy($ciid) || client_image_is_merging($ciid)) {
        return rest_error("e_client_image_busy");
    }

    //Check restore point quota limitation
    $rp_quota = db_client_image_read($ciid, array("revision_quota"));
    $img_rev_min_id = client_image_history_oldest_id($rp['ciid']);
    if ($count >= $rp_quota) {
        if ($rpid == $img_rev_min_id){
            return rest_error("e_restore_point_del_and_rst_to_min_rev");
        }
        return rest_error_extra("c_restore_point_revision_max", "", array('img_rev_min_id' => $img_rev_min_id, 'rpid' => $rpid));
    }

    $result = restore_point_restore($rp);
    return $result;
}

function rp_list_read($rpid, $ciid) {
    $where = "id='$rpid'";
    if (is_null($rpid)) {
        $where = "imageId='$ciid'";
    }
    $fields = array("id AS rpid", "imageId AS ciid", "memo AS name", "revision", "timestamp", "previous");
    $result = DBAL::db_select(DBAL::db_sql_select('imageupdatehistory', $fields, $where, "revision"));
    if (is_null($result)) {
        return $result;
    }
    foreach ($result as &$rp) {
        $rp['load_progress'] = 100;
        $progress = rp_loading_progress($rp["rpid"]);
        if (file_exists($progress)) {
            $percent = shell_last_line(home_bin2("tc-cp", $progress));
            $rp['load_progress'] = trim($percent, "%");
        }
        $rp["folder_size"] = 0;
        $rev = intval($rp["revision"]);
        if ($rev > 1) {
            $dir = implode("/", array(tc_conf_dir("TC_USER_DATA"), $ciid, $rev-1));
            $rp["folder_size"] = shell_file_size($dir, array("dir" => TRUE));
        }
    }
    return $result;
}

function handle_get($target, $params, $body) {
    $rpid = array_shift($target);
    if (!is_null($rpid)) {
        $rpid = sugar_valid_int($rpid);
        if (!$rpid) {
            return rest_error("e_operation_unsupported");
        }
    }

    $ciid = db_safe_array_get('iid', $params);
    if (!is_null($ciid)) {
        $ciid = sugar_valid_int($ciid);
        if (!$ciid) {
            return rest_error("e_operation_unsupported");
        }
    }

    $rp_list = rp_list_read($rpid, $ciid);
    if (is_null($rp_list)) {
        return rest_error_mysql('e_restore_point_read');
    }
    // no rpid means list all rps for the client image
    if (is_null($rpid)) {
        return rest_result_ok($rp_list);
    }

    $rp = $rp_list[0];
    return rest_result_ok($rp);
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
);
rest_start_loop($handlers);

?>
