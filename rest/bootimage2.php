<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

function group_details($value) {
    $groups = array();
    if (empty($value)) {
        return $groups;
    }
    $ids = explode(',', $value);
    $gid_root = load_configuration_int('root_gid');
    foreach ($ids as $gid) {
        if (empty($gid) || $gid <= $gid_root) {
            continue;
        }
        $g = DBAL::db_select_row(DBAL::db_sql_select('usergroups', array('groupName', "groupDesc"), "id=$gid"));
        if (is_null($g)) {
            continue;
        }
        $groups[] = array(
            'gid' => $gid,
            'name' => $g['groupName'],
            'desc' => $g['groupDesc'],
        );
    }
    return $groups;
}

function extract_ps_group($image_id, $value) {
    $groups = group_details($value);
    foreach ($groups as &$g) {
        $gid = $g['gid'];
        $where = "group_id=$gid AND image_id=$image_id";
        $size = DBAL::db_select_first_value(DBAL::db_sql_select("group_storage", array("image_size"), $where));
        $g['ps_size'] = $size;
    }
    return $groups;
}

function extract_predicted_ps_group() {
    $res = DBAL::db_select(DBAL::db_sql_select("group_storage", array("group_id", "image_size")));
    $result = array();
    foreach ($res as $group) {
        $result[$group['group_id']] = array(
            'gid' => $group['group_id'],
            'ps_size' => $group['image_size'],
        );
    }
    return array_values($result);
}

function bootimage_details($image_id) {
    $fields = array("imageName", "imageDesc", "iconPath", "ostype",
                    "autoboot", "revision", "path", "autoboot_revision", "reg_timestamp",
                    "needUpdate", "groupRead", "personalization", "reg_path");
    $result = DBAL::db_select_row(DBAL::db_sql_select("osimages", $fields, "id='$image_id'"));
    if (is_null($result)) {
        return NULL;
    }

    $image = array('id' => $image_id);
    $image['name'] = $result['imageName'];
    $image['description'] = $result['imageDesc'];
    $image['ostype'] = $result['ostype'];
    $image['revision'] = $result['revision'];
    $image['picture'] = $result['iconPath'];
    $image['autoboot'] = $result['autoboot'];
    $image['autoboot_revision'] = $result['autoboot_revision'];
    $image['path'] = $result['path'];
    $image['status'] = 'ready';
    $image["base_path"] = $result["reg_path"];
    $image["register_time"] = $result["reg_timestamp"];
    $image['group_read'] = group_details($result['groupRead']);
    $image['ps_group'] = extract_ps_group($image_id, $result['personalization']);
    $image['ps_group_predicted'] = extract_predicted_ps_group($image_id);

    if (intval($result['needUpdate']) === 1) {
        $image['status'] = 'pending';
    } else if (intval($result['needUpdate']) === 2) {
        $image['status'] = 'preparing';
        $image["merging_progress"] = 0;
        $rs_gap_ufiles_stat = tc_relpath(constant("TC_RS_GAP_UFILES"));
        if(file_exists($rs_gap_ufiles_stat)){
            $rs_gap_ufiles = json_decode(trim(file_get_contents($rs_gap_ufiles_stat)), true);
            $is_preparing = array_pop($rs_gap_ufiles);
            if ($is_preparing){
                $image['status'] = 'preparing';
                $total_size = 0;
                $merged_size = 0;
                //Calculate total size of UDFs to be copied/merged
                for($row = 0; $row < count($rs_gap_ufiles); $row++){
                    $total_size += $rs_gap_ufiles[$row]["file_size"];
                }
                //Calculate preparing progress
                for($row = 0; $row < count($rs_gap_ufiles); $row++){
                    $rs_merge_stat = $rs_gap_ufiles[$row]["merge_stat"];
                    if($row < 2){
                        // Calculate copy progress of UDFs
                        if(file_exists($rs_merge_stat)){
                            $result = call_home_bin("tc-ar", "stat $rs_merge_stat");
                            foreach ($result['output'] as $line) {
                            // empty line is always in the end
                                if (empty($line)) {
                                    continue;
                                }
                                $cp_progress = parse_rsync_progress($line);
                                $cp_progress = intval(substr($cp_progress["trans_pct"], 0, strlen($cp_progress["trans_pct"])-1));
                                $merged_size += ceil($cp_progress * $rs_gap_ufiles[$row]["file_size"] / 100);
                            }
                        }
                    } else {
                        // Calculate merge progress of UDFs
                        if(file_exists($rs_merge_stat)){
                            $pre_progress_raw = file_get_contents($rs_merge_stat);
                            $merged_size += ceil($pre_progress_raw * $rs_gap_ufiles[$row]["file_size"] / 100);
                        }
                    }
                }
                $image["merging_progress"] = intval($merged_size / $total_size * 100);
            }
        } else {
            // Calculate image update progress
            $image['status'] = 'merging';
            $image_merging = $image["path"] . constant("TC_EXT_MERGING");
            if (file_exists($image_merging)) {
                $image["merging_progress"] = intval(trim(file_get_contents($image_merging)));
            } else {
                log_warning("cannot find merging progress file, $image_merging");
            }
        }
    }

    $fields = client_image_revision_fields();
    $revision = $image['revision'];
    $where = "imageId='$image_id' AND revision='$revision'";
    $res = DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
    if (!is_null($res)) {
        $image["uuid"] = $res["uuid"];
    }

    $image_path = $image['path'];
    $fields = array("id", "status", "file_size", "seed_path");
    $result = DBAL::db_select_row(DBAL::db_sql_select("seed", $fields, "file_path='$image_path' AND file_type='img'"));
    if (is_null($result)) {
        $image['file_size'] = 0;
        $image['seed_path'] = '';
        $image['seed_status'] = 'missing';
    } else {
        $image['file_size'] = $result['file_size'];
        $image['seed_path'] = $result['seed_path'];
	if (file_exists($result['seed_path'])) {
	    $image['seed_status'] = $result['status'];
	} else {
	    $image['seed_status'] = 'missing';
	    $sid = $result['id'];
	    try {
            DBAL::update_table('seed', array("status" => "error"), array("id" => $sid));
	    } catch (PDOException $e) {
	        return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
	    }
	}
    }

    // calculate file/folder size
    $image['file_size'] = 0;
    $image['file_mtime'] = "";
    $image['total_size'] = 0;
    if (file_exists($image['path'])) {
        $image['file_size'] = shell_file_size($image['path']);
        $image['file_mtime'] = date("Y-m-d H:i:s", filemtime($image['path']));
        $revision_dir = tc_conf_dir("TC_USER_DATA") . "/" . $image_id;
        $size = 0;
        if (file_exists($revision_dir)) {
            $size = shell_file_size($revision_dir, array('dir' => TRUE));
        }
        $image['total_size'] = intval($size) + intval($image['file_size']);
        $ap_size = shell_file_size($image['path'], array('apparent' => TRUE));
        $image['apparent_size'] = intval($ap_size);
    }

    $image['history'] = client_image_history($image_id);
    return $image;
}

function list_img_files($folder) {
    $img_files = array();
    // list all files in the image folder
    // $images = scandir($folder);
    // foreach ($images as $name) {
    //     $fullpath = $folder.'/'.$name;
    //     $ext = pathinfo($name, PATHINFO_EXTENSION);

    //     if (!is_dir($fullpath) && strtolower($ext) == 'img') {
    //         $result = DBAL::db_select_row(DBAL::db_sql_select("osimages", array("id"), "path='$fullpath'"));
    //         $img_files[] = array(
    //             'name' => $name,
    //             'path' => $fullpath,
    //             'registered' => ($result !== NULL),
    //         );
    //     }
    // }
    // scandir returns an empty array on some servers
    // using glob instead
    foreach (glob($folder."/*.img") as $fullpath) {
        if (is_dir($fullpath)) {
            continue;
        }
        $name = pathinfo($fullpath, PATHINFO_BASENAME);
        $result = DBAL::db_select_row(DBAL::db_sql_select("osimages", array("id"), "path='$fullpath'"));
        $img_files[] = array(
            'name' => $name,
            'path' => $fullpath,
            'registered' => !empty($result),//($result !== NULL),
        );
    }
    return $img_files;
}

function list_pic_files($folder) {
    $pic_files = array();
    foreach (glob($folder."/*.png") as $fullpath) {
        if (!is_file($fullpath)) {
            continue;
        }
        $name = pathinfo($fullpath, PATHINFO_BASENAME);
        // workaround to filter client background picture
        if (strpos($name, "preboot") === 0) {
            continue;
        }
        $path = tc_conf_dir("TC_BOOT_IMAGE_PIC_REL")."/".$name;
        $pic_files[] = array(
            'name' => $name,
            'path' => $path,
        );
    }
    return $pic_files;
}

function list_sync_files($sync_folder) {
    $sync = array();
    // list all files in the image folder
    if (!file_exists($sync_folder)) {
        mkdir($sync_folder, 0755, TRUE);
    }
    if (file_exists($sync_folder)) {
        foreach (glob($sync_folder."/*.img") as $fullpath) {
            if (is_dir($fullpath)) {
                continue;
            }
            $name = pathinfo($fullpath, PATHINFO_BASENAME);
            $sync[] = array(
                'name' => $name,
                'path' => $fullpath,
            );
        }
    } else {
        log_error("Cannot create folder for sync image: ".print_r($sync_folder, TRUE));
    }
    return $sync;
}

function handle_put($target, $params, $body) {
//    auth_login_required();

    // all edits are based on bootimage ID with PUT request
    $ciid = array_shift($target);
    if (is_null($ciid)) {
        log_warning("PUT bootimage index");
        return rest_error("e_operation_unsupported");
    }
    $ciid = sugar_valid_int($ciid, -1);
    if (!$ciid) {
        log_warning("PUT on client image, $ciid");
        return rest_error("e_operation_unsupported");
    }

    $rev = array_shift($target);
    if (is_null($rev)) {
        // edit client image
        // contains all pending changes for the bootimage
        $changes = array();

        $autoboot = safe_array_get('autoboot', $body);
        $revision = safe_array_get('revision', $body);
        if ($autoboot !== NULL && $revision !== NULL) {
            $res = NULL;
            try {
                $res = DBAL::update_table("osimages", array("autoboot" => "n"));
                if (!$res) {
                    log_warning("Cannot clear autoboot flag of all images");
                    return rest_error_mysql('e_change_bootimage');
                }
            } catch (PDOException $e) {
                return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
            }
            $changes["autoboot"] = $autoboot;
            $changes["autoboot_revision"] = $revision;
        }

        $desc = db_safe_array_get('desc', $body);
        if(!is_null($desc)) {
            $desc = trim($desc);
            if ($desc == "") {
                return rest_error("e_empty_desc");
            }
            $changes["imageDesc"] = $desc;
        }

        $name = db_safe_array_get('name', $body);
        if(!is_null($name)) {
            $name = trim($name);
            if ($name == "") {
                return rest_error("e_empty_name");
            }
            $changes["imageName"] = $name;
        }

        $r_gids = safe_array_get('r_gids', $body);
        if(!is_null($r_gids)) {
            // empty is OK for removing groups
            if (!empty($r_gids)) {
                $r_gids = sugar_valid_int_array($r_gids);
                if (!$r_gids) {
                    return rest_error("e_operation_unsupported");
                }
            }

            $r_gids = ',2,'.implode(',', $r_gids).',';
            $changes["groupRead"] = $r_gids;
        }

        $w_gids = safe_array_get('w_gids', $body);
        if(!is_null($w_gids)) {
            // empty is OK for removing groups
            if (!empty($w_gids)) {
                $w_gids = sugar_valid_int_array($w_gids);
                if (!$w_gids) {
                    return rest_error("e_operation_unsupported");
                }
            }
            $w_gids = ','.implode(',', $w_gids).',';
            $changes["personalization"] = $w_gids;
        }

        try {
            $res = db_client_image_update($ciid, $changes);
            if (is_null($res)) {
                log_warning("Cannot edit bootimage $ciid");
                return rest_error_mysql('e_change_bootimage');
            }
        } catch (PDOException $e) {
            return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
        }

        if (is_null($res)) {
            log_warning("Cannot edit bootimage $ciid");
            return rest_error_mysql('e_change_bootimage');
        }

        return rest_result_ok($res);
    }

    $rev = sugar_valid_int($rev);
    if (!$rev) {
        return rest_error("e_operation_unsupported");
    }

    // edit the revision of this client image
    if (is_null(safe_array_get_string("uuid", $body))) {
        return rest_error("e_empty_input");
    }
    $res = client_image_revision_update($ciid, $rev, $body, "uuid");
    if (is_null($res)) {
        log_warning("Cannot edit client image revision $ciid, $rev");
        return rest_error_mysql();
    }
    return rest_result_ok();
}

/**
 * delete $gid from the $gid_string, for example, delete 4 from ,4,5,6,7,
 * return -1 if $gid_string is empty
 */
function gid_string_delete($gid_string, $gid) {
    $gid_array = explode(',', $gid_string);
    $gid_array = array_diff($gid_array, array($gid));
    if (count($gid_array) < 3) {
        return "-1";
    }
    return implode(',', $gid_array);
}

function gid_string_append($gid_string, $gid) {
    if ($gid_string === "-1") {
        return ",$gid,";
    }
    return $gid_string."$gid,";
}

function handle_delete($target, $params, $body) {
    auth_login_required();

    $image_id = array_shift($target);
    if (is_null($image_id)) {
        return rest_error("e_operation_unsupported");
    }

    $image_id = sugar_valid_int($image_id);
    if (!$image_id) {
        return rest_error("e_operation_unsupported");
    }

    if(client_image_is_busy($image_id)) {
        return rest_error("e_client_image_busy");
    }

    $topic = array_shift($target);
    // clear uploaded MDF/UDF
    if ($topic === "pending") {
        if (client_image_is_merging($image_id)) {
            return rest_error("e_client_image_status_merging");
        }
        user_revision_update_revision(constant("TC_DEFAULT_UID_ROOT"), $image_id, 1, "discard pendings");

        $rev = db_client_image_read($image_id, array("revision"));
        $mdf = client_image_files_mdf($image_id, $rev);
        $mdf = $mdf["pending"];
        $udf = client_image_files_udf($image_id, $rev);
        $udf = $udf["pending"];

        if (!file_exists($mdf) || !file_exists($udf)) {
            return rest_error("s_client_image_pending_uploaded");
        }

        run_as_root("rm -f $mdf $udf");
        client_image_change_status_ready($image_id);
        return rest_result_ok();
    }

    if ($topic == "ps") {
        $gid = db_safe_array_get('gid', $body);
        if (!is_null($gid)) {
            $gids = db_client_image_read($image_id, array("personalization"));
            if (is_null($gids)) {
                return rest_error_mysql('e_database');
            }
            if ($gids == "-1") {
                return rest_result_ok();
            }
            $gids = gid_string_delete($gids, $gid);
            $result = db_client_image_update($image_id, array("personalization" => $gids));
            if (is_null($result)) {
                return rest_error_mysql('e_database');
            }
            $result = DBAL::db_delete('group_storage', "image_id=$image_id AND group_id=$gid");
            if (is_null($result)) {
                return rest_error_mysql('e_database');
            }
        }
        return rest_result_ok($result);
    }
    if ($topic == "acl") {
        $gid = db_safe_array_get('gid', $body);
        $gids = db_client_image_read($image_id, array("groupRead"));
        $gids = gid_string_delete($gids, $gid);
        $result = db_client_image_update($image_id, array("groupRead" => $gids));
        if (is_null($result)) {
            return rest_error_mysql('e_database');
        }
        return rest_result_ok();
    }

    // delete it from database
    $result = DBAL::db_delete("osimages", "id=$image_id");
    if (is_null($result)) {
        return rest_error_mysql("e_delete_bootimage");
    }
    if ($result === 0) {
        log_warning("Bootimage $image_id not found in database");
        return rest_result_ok();
    }

    // delete all $image_id related seeds from seed table, and history also
    delete_all_seeds($image_id);

    // purge means deleting all bootimage files according to history
    $purge = safe_array_get('purge', $body, FALSE);

    $history = DBAL::db_select(DBAL::db_sql_select("imageupdatehistory", array("newPath"), "imageId='$image_id'"));
    foreach ($history as $file) {
        if ($purge) {
            shell_delete($file["newPath"]);
        }
    }
    $result = DBAL::db_delete("imageupdatehistory", "imageId=$image_id");
    if (is_null($result)) {
        return rest_error_mysql("e_delete_bootimage");
    }

    $dir = tc_conf_dir("TC_USER_DATA").'/'.$image_id;
    shell_delete_dir($dir);

    return rest_result_ok();
}

/**
 *  image merging is moved to task_merging
 */
function handle_post($target, $params, $body) {
//    auth_login_required();

    $image_id = array_shift($target);
    $ciid = $image_id;
    if (!systemd_service_alive("tcs-delivery-server")) {
        return rest_error("e_core_service_not_running");
    }

    if (!is_null($ciid)) {
        $ciid = sugar_valid_int($ciid);
        if (!$ciid) {
            return rest_error("e_operation_unsupported");
        }

        $topic = array_shift($target);
        if ($topic === "ps") {
            $gid = db_safe_array_get('group', $body);
            $gids = db_client_image_read($image_id, array("personalization"));
            $gids = gid_string_append($gids, $gid);
            $result = db_client_image_update($image_id, array("personalization" => $gids));
            if (is_null($result)) {
                return rest_error_mysql('e_database');
            }

            $size = db_safe_array_get('size', $body);
            $fields = array(
                'group_id' => $gid,
                'image_id' => $image_id,
                'image_size' => $size,
            );
            try {
                $result = DBAL::db_insert_table('group_storage', $fields);
                if (is_null($result)) {
                    return rest_error_mysql('e_database');
                }
            } catch (PDOException $e) {
                return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
            }
            return rest_result_ok($result);
        }

        if ($topic === "acl") {
            $gid = db_safe_array_get('group', $body);
            $gids = db_client_image_read($ciid, array("groupRead"));
            $gids = gid_string_append($gids, $gid);
            log_info("updated acl_group_read: $gids");
            $result = db_client_image_update($ciid, array("groupRead" => $gids));
            if (is_null($result)) {
                return rest_error_mysql('e_database');
            }
            return rest_result_ok($result);
        }

        // catch if query user UDF revision
        $uid = db_safe_array_get("uid", $body);
        if (!is_null($uid)) {
            $uid = sugar_valid_int($uid);
            if (!$uid) {
                return rest_error("e_operation_unsupported");
            }
            $rev = db_safe_array_get('private_revision', $body);
            $rev = sugar_valid_int($rev);
            if (!$rev) {
                return rest_error("e_operation_unsupported");
            }
            $desc = db_safe_array_get('desc', $body);
            if (is_null($desc)) {
                $desc = "";
            }
            $result = update_user_revision($uid, $image_id, $rev, $desc);
            if (is_null($result)) {
                log_error("failed to update user revision, $uid, $image_id, $rev, $desc");
                return rest_error_mysql('e_database');
            }
            return read_user_revision($uid, $image_id);
        }
        return rest_error("e_operation_unsupported");
    }

    $file = tc_conf_dir("TC_BOOT_IMAGE").'/'.$body['file'];
    $sync_old_path = tc_conf_dir("TC_BOOT_IMAGE_SYNC").'/'.$body['file'];
    // check if it is image from syncing
    if (array_key_exists('sync_image', $body) && $body['sync_image']) {
        $new_name = $body['file'].'_sync_'.date("YmdHis", time()).'.img';
        $new_path = tc_conf_dir("TC_BOOT_IMAGE").'/'.$new_name;

        if (!rename($sync_old_path, $new_path)) {
            return rest_error("e_move_sync_file");
        }
        $file = $new_path;
    }

    // check file path to prevent injection
    $file = sugar_valid_path($file);
    if (!$file) {
        return rest_error("e_operation_unsupported");
    }

    // file size 0 will break P2P tracker
    $size = shell_file_size($file);
    if ($size <= 0) {
        return rest_error('e_empty_file');
    }

    $pic = $body['picture'];
    if ($pic) {
        $pic = tc_realpath($pic, "TC_BOOT_IMAGE_PIC");
        $pic = sugar_valid_path($pic);
        if (!$pic) {
            return rest_error("e_operation_unsupported");
        }
    }
    $autoboot_gid = load_configuration_int('autoboot_gid');
    $root_gid = load_configuration_int('root_gid');
    $acl_gi = ",$root_gid,";
    $acl_gr = ",$autoboot_gid,";
    if (array_key_exists('agroups', $body)) {
        $acl_gr = implode(',', $body['agroups']);
        $acl_gr = ",$autoboot_gid,".$acl_gr.',';
    }

    log_info("ACL GroupRead: ".print_r($acl_gr, TRUE));

    $name = db_safe_array_get('name', $body);
    $desc = db_safe_array_get('desc', $body);
    if (is_null($name) || empty($name)) {
        return rest_error("e_empty_name");
    }
    // Invalid name, only support numbers, letters, and _-.()[]'
    if (!preg_match("/^[a-zA-Z0-9_\-\.\(\)\[\]]*$/", $name)) {
        return rest_error("e_bad_name");
    }
    if (strlen($name) > TC_BOOTIMAGE_NAME_MAX) {
        return rest_error("e_bad_name");
    }
    if (is_null($desc) || empty($desc)) {
        return rest_error("e_empty_desc");
    }

    if (mb_strlen($desc) > TC_BOOTIMAGE_DESC_MAX) {
        return rest_error("e_bad_desc");
    }

    $ostype = db_safe_array_get('ostype', $body);

    $fields = array(
        "imageName" => $name,
        "imageDesc" => $desc,
        "path" => $file,
        "reg_path" => $file,
        "iconPath" => $pic,
        "groupInstall" => $acl_gi,
        "groupRead" => $acl_gr,
        "ostype" => $ostype,
    );

    $image_id = NULL;
    $history_id = NULL;
    try {
        $image_id = DBAL::db_insert_table("osimages", $fields);

        if (is_null($image_id)) {
            if ($body['sync_image']) {
                rename($file, $sync_old_path);
            }
            return rest_error_mysql('e_client_image_register');
        }

        $fields = array(
            "imageId" => $image_id,
            "newPath" => $file,
            "memo" => $desc,
            "uuid" => shell_uuid(),
        );
        $history_id = DBAL::db_insert_table("imageupdatehistory", $fields);

        if (is_null($history_id)) {
            log_error("failed to add history record for client image $image_id");
            return rest_error_mysql("e_fail_record_history");
        }
    } catch (PDOException $e) {
        return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }

    // create a new record in seed table for P2P service
    seed_create('img', $file, basename($file));

    // cleanup UDF/MDF under this image id because of the repeated installation
    $dir = tc_conf_dir("TC_USER_DATA") . "/" . $image_id;
    if (file_exists($dir)) {
        shell_delete_dir($dir);
    }

    $_SESSION["ciid"] = $image_id;
    return rest_result_ok($image_id);
}

function create_user_revision($uid, $iid) {
    $fields = array(
        'image_id' => $iid,
        'user_id' => $uid,
        'user_revision' => 1,
        'description' => "user revision auto created",
    );
    $result = NULL;
    try {
        $result = DBAL::db_insert_table('user_revision', $fields);
    } catch (PDOException $e) {
        return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
    }
    return $result;
}

function read_user_revision($uid, $iid) {
    $fields = array('user_revision', 'description', 'timestamp');
    $where = "user_id=$uid AND image_id=$iid";
    $result = DBAL::db_select_row(DBAL::db_sql_select('user_revision', $fields, $where));
    if (is_null($result) || !$result) {
        $result = create_user_revision($uid, $iid);
        if (is_null($result)) {
            log_error("cannot create UDF revision record");
            return rest_error_mysql('e_database');
        }
    }
    $result = DBAL::db_select_row(DBAL::db_sql_select('user_revision', $fields, $where));
    if (is_null($result) || !$result) {
        return rest_error_mysql('e_database');
    }

    if (array_key_exists('user_revision', $result)) {
        $result['private_revision'] = $result['user_revision'];
    }
    return rest_result_ok($result);
}

function bootimage_filter_null_check($b) {

    if(array_key_exists("latest_revision", $b) && is_null($b["latest_revision"])){
        $b["latest_revision"] = 0;
    }

    if(array_key_exists("next_udf_size", $b) && is_null($b["next_udf_size"])){
        $b["next_udf_size"] = 0;
    }

    return $b;
}

function handle_get($target, $params, $body) {
    $ciid = array_shift($target);

    // $ciid is null, means a full summary list request
    if (is_null($ciid)) {
        $uuid = db_safe_array_get("uuid", $params);
        if (!is_null($uuid)) {
            $fields = client_image_revision_fields();
            $uuid = strtolower($uuid);
            $where = "uuid='$uuid'";
            $res = DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
            return rest_result_ok($res);
        }

        $image_rows = array();
        $fields = array("id");
        $result = DBAL::db_select(DBAL::db_sql_select("osimages", $fields, NULL, "id"));
        foreach ($result as $row) {
            $image_rows[] = bootimage_details($row['id']);
        }

        return rest_result_ok(array(
            "folder" => tc_conf_dir("TC_BOOT_IMAGE"),
            "files" => list_img_files(tc_conf_dir("TC_BOOT_IMAGE")),
            "pictures" => list_pic_files(tc_conf_dir("TC_BOOT_IMAGE_PIC")),
            "images" => $image_rows,
            "sync" => list_sync_files(tc_conf_dir("TC_BOOT_IMAGE_SYNC")),
            "types" => load_configuration_str("os_types"),
        ));
    }

    $ciid = sugar_valid_int($ciid);
    if (!$ciid) {
        return rest_error("e_operation_unsupported");
    }

    $rev_queried = array_shift($target);
    if (is_null($rev_queried)) {
        // catch if query user UDF revision
        $uid = safe_array_get('uid', $params);
        if (!is_null($uid)) {
            return read_user_revision($uid, $ciid);
        }

        $details = bootimage_details($ciid);
        if (is_null($details)) {
            return rest_error('e_client_image_not_found');
        }
        return rest_result_ok($details);
    }
    $topic = array_shift($target);
    if (is_null($topic)) {
        $fields = client_image_revision_fields();
        $where = "imageId='$ciid' AND revision='$rev_queried'";
        $res = DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
        return rest_result_ok($res);
    }
    if ($topic === "next") {
        $ci_revision = db_client_image_read($ciid, array("revision"));
        if (is_null($ci_revision)) {
            log_warning("Cannot read revision for $ciid");
            return rest_error_mysql();
        }
        if (intval($rev_queried) >= intval($ci_revision)) {
            log_warning("Cannot find files for $ciid, $rev_queried");
            return rest_error("e_client_image_next_revision");
        }
        $next = client_image_history($ciid, intval($rev_queried)+1);
        if (is_null($next)) {
            return rest_error("e_client_image_next_revision");
        }
        $export = client_image_history_export($ciid, $rev_queried);

        $next_info = array(
            "latest_revision" => intval(db_client_image_read($ciid, array("revision"))),
            "next_revision" => $export["next_revision"],
            "next_udf" => $export["udf"],
            "next_udf_size" => $export["udf_size"],
            "next_udf_seed" => $export["udf_seed"],
            "next_mdf" => $export["mdf"],
        );

        $next_info = array_map( "bootimage_filter_null_check", array($next_info));

        return rest_result_ok($next_info);
    }

    return rest_result_ok();
}

if (!session_id()) session_start();

$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
