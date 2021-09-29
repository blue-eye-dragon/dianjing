<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require('../libs/libtc.php');

function update_backup_service_settings() {

    if (load_configuration_bool("backup_headless")) {
        // start timer for backup service
        //run_bin("tc-config", "");
        $interval = load_configuration_int("backup_interval");
        $timer = "/etc/systemd/system/tcs-backup.timer";
        $interval .= "m";
        run_bin_as_root("tc-config", "store $timer OnActiveSec $interval");
        run_bin_as_root("tc-config", "store $timer OnUnitActiveSec $interval");
        run_as_root("systemctl daemon-reload");
        run_as_root("systemctl start tcs-backup.timer");
        run_as_root("systemctl enable tcs-backup.timer");
    } else {
        run_as_root("systemctl stop tcs-backup.timer");
        run_as_root("systemctl disable tcs-backup.timer");
    }
    $src = load_configuration_str("home");
    $env = tc_relpath(constant("TC_DEFAULT_SYSTEMD_ENV"));
    $stat = tc_relpath(constant("TC_BACKUP_STAT_PATH"));
    $mode = load_configuration_str("backup_mode");
    $dest = load_configuration_str("backup_local_location");
    $timestamp = sugar_date_now();

    if ($mode === "remote") {
        $dest = load_configuration_str("backup_remote_location");
        $dest = "$dest:/opt/remote-backup-tci";
    } else {
        $dest = "$dest/backup-tci-$timestamp";
        shell_mkdir($dest);
    }

    run_bin("tc-config", "store $env backup_mode $mode");
    run_bin("tc-config", "store $env backup_src $src");
    run_bin("tc-config", "store $env backup_dest $dest");
    run_bin("tc-config", "store $env backup_stat $stat");
}

function delete_backup_progress_file() {
    $stat = tc_relpath(constant("TC_BACKUP_STAT_PATH"));
    if (file_exists($stat)) {
        shell_delete($stat);
    }
}

function handle_put($target, $params, $body) {
    auth_login_required();

    if (is_backup_service_running()) {
        return rest_error("e_backup_busy");
    }

    $remote = db_safe_array_get('remote', $body);
    if (!is_null($remote)) {
        $valid = sugar_valid_ip4($remote);
        if (!$valid) {
            return rest_error('e_bad_ip_address');
        }
        save_configuration('backup_remote_location', $remote);
    }

    $local_prefix = db_safe_array_get('local', $body);
    if (!is_null($local_prefix)) {
        // handle new local backup location
        if (strpos($local_prefix, "/opt/tci/") === 0) {
            return rest_error('e_backup_location_dir');
        }
        if (!is_dir($local_prefix)) {
            return rest_error('e_backup_location_dir');
        }
        save_configuration('backup_local_location', $local_prefix);
    }

    $mode = db_safe_array_get('mode', $body);
    if($mode == 'remote' || $mode == 'local'){
        save_configuration('backup_mode', $mode);
    }else{
        return rest_error('e_backup_mode');
    }

    delete_backup_progress_file();

    $error = save_settings_entry_boolean($body, "backup_headless");
    if ($error) {
        return rest_error("e_backup_headless");
    }
    $error = save_settings_entry_int(
        $body,
        "backup_interval",
        constant("TC_DEFAULT_BACKUP_INTERVAL_MIN"),
        constant("TC_DEFAULT_BACKUP_INTERVAL_MAX")
    );
    if ($error) {
        return rest_error("e_backup_interval");
    }

    // update TCS env file for background task
    update_backup_service_settings();
    return rest_result_ok();
}

function handle_post($target, $params, $body) {
    auth_login_required();    

    if (is_backup_service_running()) {
        log_warning("backup service is running");
        return rest_error("e_backup_busy");
    }

    update_backup_service_settings();
    // package all configuration files and database
    $res = run_bin("tc-ar", "package");
    if (is_cmd_fail($res)) {
        log_error("backup service cannot package files");
        return rest_error_shell($res);
    }

    // test remote backup server aviablity if necessary
    $backup_mode = load_configuration_str("backup_mode");
    if ($backup_mode === "remote") {
        $remote = load_configuration_str("backup_remote_location");
        if (empty($remote)) {
            return rest_error('e_backup_server');
        }
        if (is_cmd_fail(run_bin_as_root("tc-ar", "ping $remote"))) {
            return rest_error('e_backup_server_tunnel');
        }
    }
    log_info("start backup in mode: $backup_mode");

    // start the backup process by using systemd
    $res = run_as_root("systemctl start tcs-backup");
    if (is_cmd_fail($res)) {
        log_error("backup service cannot start");
        delete_backup_progress_file();
        return rest_error_shell($res);
    }

    return rest_result_ok();
}

function load_backup_file_list($mode, $location) {
    // package all configuration files and database
    $res = run_bin("tc-ar", "package");
    if (is_cmd_fail($res)) {
        return rest_error_shell($res);
    }

    if ($mode === "remote") {
        $location = "$location:/opt/remote-backup-tci";
    }

    $file_list = array();
    $result = run_bin_as_root("tc-ar", "list $mode /opt/tci $location");
    if (is_cmd_fail($result)) {
        log_error("Cannot write down backup file list");
        return $file_list;
    }
    //file_put_contents($list, implode("\n", $result['output']));
    foreach ($result['output'] as $line) {
        if (strpos($line, "[") === 0 ) {
            continue;
        }
        $ws = preg_split("/\s+/", $line);
        $file_list[] = array(
            "file_mode" => $ws[0],
            "file_path" => $ws[4],
            "file_size" => $ws[1],
            "file_date" => $ws[2] . " " . $ws[3]
        );
    }
    return $file_list;
}

function handle_get($target, $params, $body) {
    $topic = array_shift($target);

    $backup_mode = load_configuration_str("backup_mode");
    $backup_local_location = load_configuration_str("backup_local_location");
    $backup_remote_location = load_configuration_str("backup_remote_location");

    if ($topic === "list") {
        $location = $backup_local_location;
        if ($backup_mode == "remote") {
            $location = $backup_remote_location;
        }
        if (empty($location)) {
            return rest_error("e_backup_location_empty");
        }
        $list = load_backup_file_list($backup_mode, $location);
        if (is_null($list)) {
            return rest_error("e_backup_load_list");
        }
        return rest_result_ok($list);
    }

    $stat = tc_relpath(constant("TC_BACKUP_STAT_PATH"));
    $pid = tc_relpath(constant("TC_BACKUP_PID_PATH"));

    if ($topic === "progress") {
        // default progress is 100% in order to hide progress bar on clients
        $progress = array();
        if (file_exists($stat)) {
//            if (file_exists($pid)) {
            if (systemd_service_alive(constant("TC_SYSTEMD_BACKUP"))) {
                $result = run_bin_as_root("tc-ar", "stat $stat");
                foreach ($result['output'] as $line) {
                    // empty line is always in the end
                    if (empty($line)) {
                        continue;
                    }
                    $progress = parse_rsync_progress($line);
                }
            } else {
                //delete_backup_progress_file();
                $progress = array("trans_pct" => "100%");
            }
        }
        return rest_result_ok($progress);
    }
    if ($topic === "logs") {
        $parts = array(
            "journalctl",
            "-u tcs-backup.service",
            "-u tcs-backup.timer",
            "--no-pager",
        );
        if (array_key_exists("since", $params)) { //1~3600
            $since_front = substr($params["since"], 0, strlen($params["since"]) - 1);
            $since_end = substr($params["since"], -1, 1);
            $since_front_int = intval($since_front);
            if($since_front_int > 0 && $since_front_int < 3600 && $since_end == "s"){
                array_push($parts, "--since -" . $params["since"]);
            }else{
                return rest_error("e_backup_logs_since");
            }
        } else {
            array_push($parts, "--lines=100");
        }

        $res = run_as_root(implode(" ", $parts));
        if (is_cmd_fail($res)) {
            return rest_error("e_backup_logs");
        }
        if (count($res["output"]) === 2) {
            foreach ($res["output"] as $line) {
                if (strpos($line, "No entries")) {
                    return rest_result_ok();
                }
            }
        }
        return rest_result_ok($res["output"]);
    }

    $backup_settings = array(
        "backup_mode" => $backup_mode,
        "backup_interval" => load_configuration_int("backup_interval"),
        "backup_headless" => load_configuration_bool("backup_headless"),
        "backup_local_location" => $backup_local_location,
        "backup_remote_location" => $backup_remote_location,
        "mtime" => "",
    );

    $hist = tc_conf_path_join("TC_VAR", "ar.lastrun");
    if (file_exists($hist)) {
        $backup_settings['mtime'] = date("Y-m-d H:i:s", filemtime($hist));
    }

    return rest_result_ok($backup_settings);
}


$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
