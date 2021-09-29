<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require_once('inc.conf.php');
require('inc.linux.php');
require_once('inc.db.php');


function patch_missing_entry($entry, $php_const) {
    if (is_null(load_configuration($entry))) {
        save_configuration_current($entry, constant($php_const));
    }
}

function load_systemd_env() {
    $envs = parse_ini_file(tc_relpath(constant("TC_DEFAULT_SYSTEMD_ENV")));
    if ($envs) {
        $GLOBALS['_TC2_'] = array_merge($GLOBALS['_TC2_'], $envs);
    }
}

function db_user_sha256ed_query($username) {
    return DBAL::db_select_first_value(DBAL::db_sql_select("users", array("sha256ed"), "userName='$username'"));
}

function load_global_config($force=False) {
    if (array_key_exists('_TC2_', $GLOBALS)) {
        if ($force) {
            $configs = parse_ini_file(TC_CONF_PATH);
            $GLOBALS['_TC2_'] = $configs;
        }
    } else {
        $configs = parse_ini_file(TC_CONF_PATH);
        $GLOBALS['_TC2_'] = $configs;
    }

    // patch for missing entries in INI for current configuration
    patch_missing_entry('page_refresh', 'TC_DEFAULT_PAGE_REFRESH');
    patch_missing_entry('dhcp_dns', 'TC_DEFAULT_CLIENT_DHCP_DNS');
    patch_missing_entry('autoboot_gid', 'TC_DEFAULT_GID_AUTOBOOT');
    patch_missing_entry('root_gid', 'TC_DEFAULT_GID_ROOT');
    patch_missing_entry('client_pagefile', 'TC_DEFAULT_CLIENT_PAGEFILE');
    patch_missing_entry('user_batch_max', 'TC_DEFAULT_USER_BATCH_MAX');
    patch_missing_entry('ps_sizes', 'TC_DEFAULT_PS_SIZES');
    patch_missing_entry('ps_cache_ratio', 'TC_DEFAULT_PS_CACHE_RATIO');
    patch_missing_entry('os_types', 'TC_DEFAULT_OS_TYPES');
    patch_missing_entry('heartbeat_timeout', 'TC_DEFAULT_HEARTBEAT_TIMEOUT');
    patch_missing_entry('backup_local_location', 'TC_DEFAULT_BACKUP_LOCAL_LOCATION');
    patch_missing_entry('backup_interval', 'TC_DEFAULT_BACKUP_INTERVAL');
    patch_missing_entry('backup_headless', 'TC_DEFAULT_BACKUP_HEADLESS');
    patch_missing_entry('auto_login_delay', 'TC_DEFAULT_CLIENT_AUTO_LOGIN_DELAY');
    patch_missing_entry('client_open_registration', 'TC_DEFAULT_CLIENT_OPEN_REGISTRATION');
    patch_missing_entry('ad_domain_enable', 'TC_DEFAULT_AD_DOMAIN_ENABLE');
    // client sequencing naming rule
    patch_missing_entry('client_naming', 'TC_DEFAULT_CLIENT_NAMING');
    patch_missing_entry('client_naming_prefix', 'TC_DEFAULT_CLIENT_NAMING_PREFIX');
    patch_missing_entry('client_naming_suffix', 'TC_DEFAULT_CLIENT_NAMING_SUFFIX');
    patch_missing_entry('client_naming_width', 'TC_DEFAULT_CLIENT_NAMING_WIDTH');
    patch_missing_entry('client_naming_first', 'TC_DEFAULT_CLIENT_NAMING_FIRST');

    load_systemd_env();
    return $configs;
}

function save_global_config($key, $value) {
    $new_config = TRUE;
    $fp = fopen(TC_CONF_PATH, 'r+');
    if ($fp) {
        $lines = array();
        while (($line = fgets($fp)) !== false) {
            $trline = trim($line);
            $tokens = explode('=', $trline);
            if (trim($tokens[0]) == $key) {
                $line = $key.' = '.$value.PHP_EOL;
                // update exitsted config
                $new_config = FALSE;
            }
            $lines[] = $line;
        }
        if ($new_config) {
            $line = $key.' = '.$value.PHP_EOL;
            $lines[] = $line;
        }
        fclose($fp);
        file_put_contents(TC_CONF_PATH, $lines);
    }
}

function save_config_lang($lang) {
    // save to config file
    save_global_config('lang', $lang);
    // update runtime config structure
    $GLOBALS['_TC2_']['lang'] = $lang;
}

function save_configuration_current($key, $value) {
    $GLOBALS['_TC2_'][$key] = $value;
}

function save_configuration_storage($key, $value) {
    return save_global_config($key, $value);
}

function save_configuration($key, $value) {
    save_configuration_current($key, $value);
    save_configuration_storage($key, $value);
    return $value;
}

function save_configuration_boolean($key, $boolean) {
    $flag = $boolean ? 'TRUE' : 'FALSE';
    return save_configuration($key, $flag);
}

function load_configuration($key) {
    return array_key_exists($key, $GLOBALS['_TC2_'])
        ? $GLOBALS['_TC2_'][$key] : NULL;
}

function load_configuration_bool($key) {
    $conf = load_configuration($key);
    if (is_null($conf)) {
        return FALSE;
    }
    $bool = filter_var($conf, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if (is_null($bool)) {
        return FALSE;
    }
    return $bool;
}

function load_configuration_int($key) {
    $conf = load_configuration($key);
    return is_null($conf) ? 0 : intval($conf);
}

function load_configuration_str($key) {
    $conf = load_configuration($key);
    return is_null($conf) ? "" : $conf;
}

function save_settings_entry_boolean($body, $entry) {
    if (!array_key_exists($entry, $body)) {
        return NULL;
    }
    $value = filter_var($body[$entry], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if (is_null($value)) {
        return "e_invalid_format_boolean";
    }
    save_configuration_boolean($entry, $value);
}

function save_settings_entry_int($body, $entry, $min, $max) {
    if (!array_key_exists($entry, $body)) {
        return NULL;
    }
    $options = array(
        'options' => array(
            'min_range' => $min,
            'max_range' => $max
        ),
        "flags" => FILTER_NULL_ON_FAILURE
    );
    $value = filter_var($body[$entry], FILTER_VALIDATE_INT, $options);
    if (is_null($value)) {
        return "e_invalid_format_int";
    }
    save_configuration($entry, $value);
}

function save_settings_entry_str($body, $entry, $validator) {
    if (!array_key_exists($entry, $body)) {
        return NULL;
    }
    $value = $body[$entry];
    if ($validator($value)) {
        save_configuration($entry, $value);
        return NULL;
    }
    return "e_invalid_format_str";
}


/**
 * Get directory full path from configuration
 * @param  [string] $key [one from name list supported]
 * @return [string]      [directory full path]
 *
 * List: TC_BOOT_IMAGE, TC_BOOT_IMAGE_SYNC
 */
function tc_conf_dir($key) {
    if ($key == "TC_BOOT_IMAGE") {
        return $GLOBALS['_TC2_']['home'].$GLOBALS['_TC2_']['home_img_dir'];
    }
    if ($key == "TC_BOOT_IMAGE_SYNC") {
        return $GLOBALS['_TC2_']['home'].$GLOBALS['_TC2_']['home_sync_dir'];
    }
    if ($key == "TC_BOOT_IMAGE_PIC") {
        return $GLOBALS['_TC2_']['www_root'].'/images/clientboot';
    }
    if ($key == "TC_BOOT_IMAGE_PIC_REL") {
        return '/images/clientboot';
    }
    if ($key == "TC_USER_DATA") {
        return $GLOBALS['_TC2_']['home'].$GLOBALS['_TC2_']['home_usr_dir'];
    }
    if ($key == "TC_VAR") {
        return $GLOBALS['_TC2_']['home'].'/var';
    }
    if ($key == "TC_BOOT_IMAGE_OPT") {
        return $GLOBALS['_TC2_']['home'].'/opt/diskimage';
    }
    if ($key == "TC_P2P_SEED") {
        return $GLOBALS['_TC2_']['home'].'/opt/p2p';
    }
    if ($key == "TC_PS_ROOT") {
        return $GLOBALS['_TC2_']['home'].'/psroot';
    }
    if ($key == "TC_PS_UPLOAD") {
        return $GLOBALS['_TC2_']['home'].'/upload/ps';
    }
    if ($key == "TC_DOC") {
        return $GLOBALS['_TC2_']['home'].'/webconsole/doc';
    }

    $p = constant($key);
    return is_null($p) ? "" : tc_relpath($p);
}

/**
 * Return absolute path according to TC home dir
 */
function tc_relpath($path) {
    return tc_path_join($GLOBALS['_TC2_']['home'], $path);
}

function tc_conf_path_join($key, $path) {
    return tc_path_join(tc_conf_dir($key), $path);
}

function tc_path_join($p0, $p1) {
    return implode('/', array($p0, $p1));
}

function path_join($segs) {
    return implode("/", $segs);
}

function tc_realpath($path, $parent=NULL) {
    if (is_null($parent)) {
        $parent = load_configuration_str("home");
    } else {
        $parent = tc_conf_dir($parent);
    }
    return realpath(path_join(array($parent, $path)));
}

function global_config_debug() {
    return array_key_exists('debug', $GLOBALS['_TC2_']) && $GLOBALS['_TC2_']['debug'];
}

function global_config_lang_id() {
    if ($GLOBALS['_TC2_']['lang'] == 'zh')
        return 2;
    return 1;
}

function run_as_root($cmd, $full=FALSE, $trace=TRUE) {
    $home = load_configuration_str("home");
    $cmd = "$home/bin/tc-perm \"$cmd\"";
    if ($full) {
        return shell_cmd($cmd);
    }
    return shell_cmd_short($cmd, $trace);
}

function run_bin($bin, $args, $trace=TRUE) {
    $home = load_configuration_str("home");
    return shell_cmd_short("$home/bin/$bin $args", $trace);
}

function run_bin_as_root($bin, $args, $full=FALSE, $trace=TRUE) {
    $home = load_configuration_str("home");
    return run_as_root("$home/bin/$bin $args", $full, $trace);
}

function read_last_line($run_bin_result) {
    return is_cmd_done($run_bin_result) ? $run_bin_result["last_line"] : NULL;
}

// handy function for path wrapper in home root, /opt/tci/bin, etc.
// deprecated
// 51 matches across 8 files
function home_bin($bin, $args) {
    $home = $GLOBALS['_TC2_']['home'];
    $cmd = "$home/bin/$bin $args";
    return "$home/bin/tc-perm \"$cmd\"";
}

/**
 * deprecated
 * 7 matches across 2 files
 * same as home_bin but no tc-perm
 * @param  [type] $bin  [description]
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function home_bin2($bin, $args) {
    $home = $GLOBALS['_TC2_']['home'];
    return "$home/bin/$bin $args";
}

// deprecated
// 32 matches across 6 files
function call_home_bin($bin, $args) {
    return shell_cmd(home_bin($bin, $args));
}


/**
 * Short handy function for "merge", -p means write through, -r means with RP
 */
function update_blockio_file($img, $mdf, $udf, $backup=NULL) {
    // close the session to release the sync blocked requester
    // or the web page will stop response due to busy waiting for http server
    // the client image update operation takes long time
    session_write_close();

    $args = array("-p", "-r", $img, $mdf, $udf);
    if ($backup) {
        $args[] = $backup;
    }
    return run_bin("merge", implode(' ', $args));
}

function archive_client_image_changes($base_mdf, $base_udf, $diff_mdf, $diff_udf) {
    return shell_cmd_short(home_bin2("tc-disk-ar", "$base_mdf $base_udf $diff_mdf $diff_udf"));
}

function archive_client_image_changes_progress($stat, $base_mdf, $base_udf, $diff_mdf, $diff_udf) {
    return shell_cmd_short(home_bin2("tc-disk-ar", "-p $stat $base_mdf $base_udf $diff_mdf $diff_udf"));
}

/**
 * Short handy function for tc-client-control/tcdctl
 */
function send_cc_command($cmd) {
    $value = load_configuration_bool('ssl_encryption');
    if ($value) {
        $cmd .= " --ssl";
    }
    return run_bin_as_root('tc-client-control', $cmd);
}

function send_cc_command_registered($cmd) {
    $tmp = client_list_store(client_list_read("registered"));
    send_cc_command("$cmd --ip-file $tmp");
    shell_delete($tmp);
}

function send_cc_command_cid($cid, $cmd) {
    $client = db_client_read($cid);
    if (is_null($client)) {
        log_error("Cannot find client for cid $cid");
        return NULL;
    }
    $mac = $client["mac"];
    $ip = DBAL::db_select_first_value(DBAL::db_sql_select("userstatus", "ip", "mac='$mac'"));
    if (is_null($ip)) {
        log_error("Cannot find IP for mac $mac");
        return NULL;
    }
    return send_cc_command("$cmd --ip $ip");
}

//
// handy function for server status checking
//
function is_disk_server_running() {
    $ret = shell_cmd_short("systemctl is-active --quiet tcs-disk-server");
    return 0 == $ret['exit_value'];
}

function is_ccontrol_server_running() {
    $ret = shell_cmd_short("systemctl is-active --quiet tcs-ccontrol-server");
    return 0 == $ret['exit_value'];
}

function is_dhcp_server_running() {
    $ret = shell_cmd_short("systemctl is-active --quiet tcs-dnsmasq");
    return 0 == $ret['exit_value'];
}

function is_backup_service_running() {
    $ret = shell_cmd_short('systemctl is-active --quiet tcs-backup');
    return 0 == $ret['exit_value'];
}

function systemd_service_alive($service_name) {
    $ret = shell_cmd_short("systemctl is-active --quiet $service_name", FALSE);
    return 0 == $ret['exit_value'];
}


/**
 * Return command line result if error occurs, or None for OK
 */
function systemd_service_start($service_name) {
    if (systemd_service_alive($service_name)) {
        return;
    }
    $res = run_as_root("systemctl start $service_name");
    if (is_cmd_done($res)) {
        return;
    }
    return $res;
}

function systemd_service_stop($service_name) {
    if (!systemd_service_alive($service_name)) {
        return;
    }
    $res = run_as_root("systemctl stop $service_name");
    if (is_cmd_done($res)) {
        return;
    }
    return $res;
}

function lic2_verify_expiration() {
    $res = run_bin("network", "mac", FALSE);
    if (is_cmd_fail($res)) {
        log_warning("LIC2 cannot read nic MAC");
        return FALSE;
    }
    $mac = $res["last_line"];
    $res = run_bin_as_root("tc-license", "--verify-formally '$mac'", FALSE, FALSE);
    if (is_cmd_fail($res)) {
        log_warning("LIC2 license expired");
        return FALSE;
    }
    return TRUE;
}

function lic2_verify_hardware() {
    $res = run_bin("network", "mac", FALSE);
    if (is_cmd_fail($res)) {
        log_warning("LIC2 cannot read nic MAC");
        return FALSE;
    }
    $mac = $res["last_line"];
    $res = run_bin("tc-license", "--verify '$mac'", FALSE);
    if (is_cmd_fail($res)) {
        log_warning("LIC2 license invalid");
        return FALSE;
    }
    return TRUE;
}

function lic2_license_client_count() {
    $res = run_bin("tc-license", "--get-client-count");
    if (is_cmd_fail($res)) {
        return rest_error_shell($res);
    }
    $pos = strpos($res['last_line'], ":");
    if ($pos === FALSE) {
        return rest_error_shell($res);
    }
    return rest_result_ok(substr($res['last_line'], $pos + 1));
}

function lic2_license_expired_time() {
    $res = run_bin("tc-license", "--get-expire-date-value");
    if (is_cmd_fail($res)) {
        return rest_error_shell($res);
    }
    $pos = strpos($res['last_line'], ":");
    if ($pos === FALSE) {
        return rest_error_shell($res);
    }
    return rest_result_ok(substr($res['last_line'], $pos + 1));
}


// common utility functions
// keep the original file extension and append a timestamp string
function append_timestamp($path) {
    $timestamp = sugar_date_now();
    $pi = pathinfo($path);
    return $pi['dirname'].DIRECTORY_SEPARATOR.$pi['filename'].".$timestamp.".$pi['extension'];
}

function current_session_auth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    return array(
        "uid" => intval(safe_array_get("uid", $_SESSION)),
        "gid" => intval(safe_array_get("gid", $_SESSION)),
        "user_name" => safe_array_get("user_name", $_SESSION),
        "access_token" => safe_array_get("access_token", $_SESSION),
    );
}

/**
 * Check if admin account exsit.
 * @return bool
 */
function check_admin_exsit() {
    $count = NULL;
    try {
        $result = DBAL::do_select("SELECT * FROM users");
        $count = $result->rowCount();
    } catch (PDOException $e) {
        throw $e;
    }
    return $count;
}

/**
 * Perform basic session check, return current session auth, or die 401
 * @return $auth or die()
 */
function auth_login_required($resource="common") {
    $auth = current_session_auth();
    if (empty($auth["uid"]) || empty($auth["gid"]) || empty($auth["access_token"])) {
        log_warning("$resource: current session check failed: login required, ".json_encode($auth));
        http_die_unauthorized();
        return NULL;
    }
    return $auth;
}

function current_nic() {
    return $GLOBALS['_TC2_']['nic'];
}

function update_client_token($cid) {
    $client = db_client_read($cid);
    if ($client) {
        try {
            $token = substr(md5($client['mac'].rand()), 0, 12);
            $result = db_client_update($cid, array("client_token" => $token));
            return $result;
        } catch (PDOException $e) {
            return rest_error_pdo_exception($e);
        }
    }
}

function generate_access_token() {
    $length = 12;
//  $token = openssl_random_pseudo_bytes(12, TRUE);
    $token = substr(
        base_convert(
            bin2hex(hash('sha512', uniqid(mt_rand() . microtime(true) * 10000, true), true)),
            16,
            36
        ),
        0,
        $length
    );
    return $token;
}

function update_user_access_token($uid) {
    $access_token = generate_access_token();
    $changes = array(
        'access_token' => $access_token,
    );
    $result = DBAL::update_table('users', $changes, array("id" => $uid));
    if (!$result && db_catch_errno(1062)) {
        return update_user_access_token($uid);
    }
    return $result ? $access_token : NULL;
}

/**
 *  remote boot client
 *  return error if there is any, or NULL for succeed
 */
function remote_boot_client($client_token, $access_token, $image_id) {
    $client_id = db_get_client_id_by_token($client_token);
    if (!$client_id) {
        return 'e_new_client';
    }
    $client = db_client_read($client_id);
    if (!$client) {
        return 'e_new_client';
    }
    $client_mac = $client['mac'];
    $client_ip = DBAL::db_select_first_value_old("SELECT ip FROM userstatus WHERE mac='$client_mac'");
    if (!$client_ip) {
        return 'e_client_offline';
    }

    $image_filename = DBAL::db_select_first_value_old("SELECT path FROM osimages WHERE id=$image_id");
    if (!$image_filename) {
        return 'e_client_image_not_found';
    }
    $image_filename = basename($image_filename);

    $image_orig_filename = DBAL::db_select_first_value_old("SELECT newPath FROM imageupdatehistory WHERE imageId=$image_id AND revision=1");
    if (!$image_orig_filename) {
        return 'e_client_image_not_found';
    }
    $image_orig_filename = basename($image_orig_filename);

    $user = DBAL::db_select_row_old("SELECT id, userName AS name FROM users WHERE access_token='$access_token'");
    if (!$user) {
        return 'e_user_not_found';
    }
    $user_id = $user['id'];
    $user_name = $user['name'];

    $parts = array(
        "--boot",
        "--image-id $image_id",
        "--image-org-name $image_orig_filename",
        "--image-name $image_filename",
        "--user-token $user_id",
        "--user-name $user_name",
        "--ip '$client_ip'",
    );
    $result = send_cc_command(implode(" ", $parts));
    if (is_cmd_fail($result)) {
        log_error("REMOTE BOOT ERROR :: " . json_encode(array(
            "image_id" => $image_id,
            "image_orig_filename" => $image_orig_filename,
            "image_filename" => $image_filename,
            "user_id" => $user_id,
            "user_name" => $user_name,
            "client_ip" => $client_ip
        )));
        return 'e_remote_boot';
    }
}

/**
 *  remote boot client
 *  return error if there is any, or NULL for succeed
 */
function remote_boot_client2($client_token, $access_token, $image_id, $username) {
    $client_id = db_get_client_id_by_token($client_token);
    if (!$client_id) {
        return 'e_new_client';
    }
    $client = db_client_read($client_id);
    if (!$client) {
        return 'e_new_client';
    }
    $client_mac = $client['mac'];
    $client_ip = DBAL::db_select_first_value_old("SELECT ip FROM userstatus WHERE mac='$client_mac'");
    if (!$client_ip) {
        return 'e_client_offline';
    }

    $image_filename = db_client_image_read($image_id, array("path"));
    if (!$image_filename) {
        return 'e_client_image_not_found';
    }
    $image_filename = basename($image_filename);

    $image_orig_filename = DBAL::db_select_first_value_old("SELECT newPath FROM imageupdatehistory WHERE imageId=$image_id AND revision=1");
    if (!$image_orig_filename) {
        return 'e_client_image_not_found';
    }
    $image_orig_filename = basename($image_orig_filename);

    $parts = array(
        "--boot",
        "--image-id $image_id",
        "--image-org-name $image_orig_filename",
        "--image-name $image_filename",
        "--user-token $access_token",
        "--user-name $user_name",
        "--ip '$client_ip'",
    );
    $result = send_cc_command(implode(" ", $parts));
    if (is_cmd_fail($result)) {
        log_error("REMOTE BOOT ERROR :: " . json_encode(array(
            "image_id" => $image_id,
            "image_orig_filename" => $image_orig_filename,
            "image_filename" => $image_filename,
            "user_id" => $user_id,
            "user_name" => $user_name,
            "client_ip" => $client_ip
        )));
        return 'e_remote_boot';
    }
}


function update_client_online_hardware($client_mac, $cpu_model, $memory_size, $disk_size) {
    $changes = array(
        'cpu_model' => $cpu_model,
        'memory_size' => $memory_size,
        'disk_size' => $disk_size,
    );
    $where = array("mac" => $client_mac);
    $result = DBAL::update_table('userstatus', $changes, $where);
    return $result;
}

function update_client_online_status($client_id, $status) {
    $client_mac = DBAL::db_select_first_value_old("SELECT mac FROM machines WHERE id=$client_id");
    $changes = array(
        'client_status' => $status,
    );
    $where = array("mac" => $client_mac);
    $result = DBAL::update_table('userstatus', $changes, $where);
    return $result;
}

function network_ethers_delete($ether_mac) {
    $mac = str_replace("-", ":", $ether_mac);
    $ethers_file_path = tc_relpath(constant("TC_DHCP_ETHERS"));

    // $fp = fopen("/opt/tci/var/ether.lock", "w+");
    // if ($fp) {
    //     if (flock($fp, LOCK_EX)) {
    //         // I: case insensitive match
    //         // d: delete
    //         shell_cmd_short("sed -i \"/$mac/Id\" $ethers_file_path");
    //         flock($fp, LOCK_UN);
    //         fclose($fp);
    //         system_restart_dnsmasq();
    //         return TRUE;
    //     }
    //     fclose($fp);
    //     log_error("Cannot lock ether lock file");
    // } else {
    //     log_error("Cannot open ether lock file");
    // }
    // return FALSE;
    shell_cmd_short("sed -i \"/$mac/Id\" $ethers_file_path");
    system_restart_dnsmasq();
    return TRUE;
}

function client_image_change_status($ciid, $status) {
    db_client_image_update($ciid, array("needUpdate" => $status));
}

function client_image_change_status_ready($ciid) {
    client_image_change_status($ciid, constant("TC_CLIENT_IMAGE_READY"));
}

function client_image_change_status_merging($ciid) {
    client_image_change_status($ciid, constant("TC_CLIENT_IMAGE_MERGING"));
}

function client_image_is_busy($ciid) {
    return db_client_image_count_running($ciid) > 0;
}

function client_image_is_merging($ciid) {
    $status = db_client_image_read($ciid, array("needUpdate"));
    return intval($status) == constant("TC_CLIENT_IMAGE_MERGING");
}

function client_image_files($ext, $ciid, $revision, $username) {
    $dir = tc_conf_dir("TC_USER_DATA");
    $ext = ".".$ext;
    $name = $ciid."_".$revision."_".$username.$ext;

    return array(
        "name" => $name,
        "pending" => "$dir/$name",
        "archive" => "$dir/$ciid/$revision/$username" . $ext,
        "rst_name" => $name . ".rst",
        "rst_pending" => "$dir/$name" . ".rst",
        "rst_archive" => "$dir/$ciid/$revision/$username" . $ext. ".rst",
    );
}

function client_image_files_udf($ciid, $revision, $username="admin") {
    return client_image_files("udf", $ciid, $revision, $username);
}

function client_image_files_mdf($ciid, $revision, $username="admin") {
    return client_image_files("mdf", $ciid, $revision, $username);
}

function __client_image_history_fields() {
    return array("id", "revision", "memo", "timestamp", "newPath as path", "previous", "uuid");
}

function client_image_revision_fields() {
    return array("imageId as ciid", "revision", "memo", "timestamp", "newPath as path", "uuid");
}

function client_image_revision_update($ciid, $rev, $body, $field) {
    $value = db_safe_array_get($field, $body);
    if (!is_null($value)) {
        return db_client_image_revision_update($ciid, $rev, $field, $value);
    }
    return TRUE;
}

function client_image_history($ciid, $civ=NULL) {
    $fields = __client_image_history_fields();

    // full history
    if (is_null($civ)) {
        $history = DBAL::db_select(DBAL::db_sql_select("imageupdatehistory", $fields, "imageId='$ciid'", "revision"));
        return $history;
    }
    // history with specified revision
    $where = "imageId='$ciid' AND revision='$civ'";
    return DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
}

function client_image_history_oldest($ciid) {
    $fields = __client_image_history_fields();
    array_push($fields, "MIN(revision)");
    return DBAL::db_select_row(DBAL::db_sql_select("imageupdatehistory", $fields, "imageId='$ciid'"));
}

function client_image_history_oldest_id($ciid) {
    $fields = array("MIN(id)");
    return DBAL::db_select_first_value(DBAL::db_sql_select("imageupdatehistory", $fields, "imageId='$ciid'"));
}

function client_image_history_count($ciid) {
    $fields = array("COUNT(id)");
    return DBAL::db_select_first_value(DBAL::db_sql_select("imageupdatehistory", $fields, "imageId='$ciid'"));
}

function client_image_history_export($ciid, $rev) {
    $export = array("udf" => "", "mdf" => "", "udf_seed" => "", "next_revision" => "");
    $ci_revision = db_client_image_read($ciid, array("revision"));
    if (is_null($ci_revision)) {
        log_warning("Cannot read revision for $ciid");
        return NULL;
    }
    if (intval($rev) >= intval($ci_revision)) {
        log_warning("Cannot find files for $ciid, $rev");
        return $export;
    }
    $ci_root = path_join(array(
        constant("TC_EXPORT_CLIENT_IMAGE_ROOT"), $ciid,
        $rev
    ));
    if (!file_exists($ci_root)) {
        shell_change_owner("/opt/lampp/htdocs");
        shell_mkdir($ci_root);
    }
    $udf_file = client_image_files_udf($ciid, $rev);
    $udf_file = $udf_file["archive"];
    $mdf_file = client_image_files_mdf($ciid, $rev);
    $mdf_file = $mdf_file["archive"];
    $udf_seed = implode("_", array($ciid, $rev, "admin.udf.seed"));
    $udf_seed = tc_conf_path_join("TC_P2P_SEED", $udf_seed);

    shell_link_symbol($udf_file, path_join(array($ci_root, "admin.udf")));
    shell_link_symbol($mdf_file, path_join(array($ci_root, "admin.mdf")));
    shell_link_symbol($udf_seed, path_join(array($ci_root, "admin.udf.seed")));

    $export["udf"] = path_join(array(constant("TC_EXPORT_CLIENT_IMAGE_SITE"), $ciid, $rev, "admin.udf"));
    $export["mdf"] = path_join(array(constant("TC_EXPORT_CLIENT_IMAGE_SITE"), $ciid, $rev, "admin.mdf"));
    $export["udf_seed"] = path_join(array(constant("TC_EXPORT_CLIENT_IMAGE_SITE"), $ciid, $rev, "admin.udf.seed"));
    $export["next_revision"] = intval($rev) + 1;
    $export["udf_size"] = shell_file_size($udf_file);
    return $export;
}

function db_user_id_query($username) {
    return DBAL::db_select_first_value(DBAL::db_sql_select("users", array("id"), "userName='$username'"));
}

/**
 * Get user secret profile, NEVER avaiable outside TC server
 * @param  [type] $uid [description]
 * @return [type]      [description]
 */
function user_read_internal($uid) {
    $fields = array("id", "salt", "login_failure", "password", "groupId", "access_token", "userName", "storage_lock");
    return DBAL::db_select_row(DBAL::db_sql_select("users", $fields, "id='$uid'", "id"));
}

function user_read($uid=NULL) {
    $fmt = "SELECT u.id, u.userName AS 'name', u.groupId AS 'group_id', u.storage_frozen,u.storage_lock,o.imageName AS 'image_name',m.machineName AS 'client_name',u.isApproved AS 'enabled',u.printName AS 'display'FROM users u LEFT JOIN machines m ON m.id=u.bind_client_id LEFT JOIN osimages o ON u.bind_image_id=o.id WHERE m.id IS NULL AND o.id IS NULL ";
    //$fmt = "SELECT u.id, u.userName AS 'name', u.groupId AS 'group_id', u.storage_frozen,u.storage_lock,o.imageName AS 'image_name',m.machineName AS 'client_name',u.isApproved AS 'enabled',u.printName AS 'display'FROM users u LEFT JOIN machines m ON m.id=u.bind_client_id LEFT JOIN osimages o ON u.bind_image_id=o.id";

    if (empty($uid)) {
        return DBAL::db_select_old($fmt);
    }
    return DBAL::db_select_row(DBAL::do_stmt($fmt . " AND u.id=? ORDER BY u.id", $uid));
}

function user_read_u($uid=NULL) {
    //$fmt = "SELECT u.id, u.userName AS 'name', u.groupId AS 'group_id', u.storage_frozen,u.storage_lock,o.imageName AS 'image_name',m.machineName AS 'client_name',u.isApproved AS 'enabled',u.printName AS 'display'FROM users u LEFT JOIN machines m ON m.id=u.bind_client_id LEFT JOIN osimages o ON u.bind_image_id=o.id WHERE m.id IS NULL AND o.id IS NULL ";
    $fmt = "SELECT u.id, u.userName AS 'name', u.groupId AS 'group_id', u.storage_frozen,u.storage_lock,o.imageName AS 'image_name',m.machineName AS 'client_name',u.isApproved AS 'enabled',u.printName AS 'display'FROM users u LEFT JOIN machines m ON m.id=u.bind_client_id LEFT JOIN osimages o ON u.bind_image_id=o.id";
    if (empty($uid)) {
        return DBAL::db_select_old($fmt);
    }
    return DBAL::db_select_row(DBAL::do_stmt($fmt . " AND u.id=? ORDER BY u.id", $uid));
}

function user_update($uid, $changes) {
    return DBAL::db_update_table_row("users", $changes, $uid);
}

function user_update_password($uid, $username, $sha256ed_new) {
    $auth_new = user_auth_create($username, $sha256ed_new);
    $changes = array();
    $changes["password"] = $auth_new["hash_result"];
    $changes["salt"] = $auth_new["salt"];
    $changes["sha256ed"] = $sha256ed_new;
    return user_update($uid, $changes);
}

function user_delete($uid) {
    return DBAL::db_delete("users", "id='$uid'");
}

function user_is_busy($uid) {
    $c = DBAL::db_select_first_value(DBAL::db_sql_select("userstatus", array("COUNT(id)"), "userId='$uid'"));
    return $c > 0;
}

function user_ps_is_locked($uid) {
    $u = user_read($uid);
    return !empty($u["storage_lock"]);
}

function user_revision_update_revision($uid, $ciid, $revision, $desc) {
    $changes = array(
        "user_revision" => $revision,
        "description" => $desc
    );
    $where = array(
        "image_id" => $ciid,
        "user_id" => $uid
    );
    $result = DBAL::update_table("user_revision", $changes, $where);
    if (is_null($result)) {
        log_error("Cannot update user revision, $uid, $ciid, $revision");
    }
    return $result;
}

function user_auth_password_check($username, $password) {
    $uid = db_user_id_query($username);
    if (is_null($uid)) {
        return NULL;
    }

    $settings = user_read_internal($uid);
    $hash = hash_pbkdf2("sha256", $password, $settings["salt"], 100000, 64);
    $settings["authorized"] = ($hash === $settings["password"]);
    return $settings;
}
/**
 *  Return an array with user information or an empty array
 *  Return NULL means a SQL error
 */
function user_auth_check($username, $password) {
    //prepair fields for user_auth_history
    $clientip = db_safe_array_get("REMOTE_ADDR", $_SERVER);
    if(is_null($clientip) || empty($clientip)) {
        $clientip = "Unknown IP";
    }
    $client_name = db_safe_array_get("HTTP_USER_AGENT", $_SERVER);
    if(is_null($client_name) || empty($client_name)) {
        $client_name = "Unknown Client";
    }

    $settings = user_auth_password_check($username, $password);
    if (is_null($settings)) {
        return NULL;
    }
    $uid = $settings["id"];

    $fields = array(
        "user_id" => $uid,
        "user_name" => $username,
        "client_name" => $client_name,
        "client_ip" => $clientip,
        "event" => "login",
        "result" => 0,
        "logger_id" => $uid,
    );

    $where = array("id" => $uid);
    if (! $settings["authorized"]) {
        //Update failure info of user_auth_history
        $stmt = DBAL::getInstance()->getConn()->prepare("INSERT INTO user_auth_history (user_id, user_name, client_name, client_ip, event, result, logger_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$uid, $username, $client_name, $clientip, "login", 0, $uid]);
        //Update login_failure of users
        $fields = array("login_failure" => $settings["login_failure"] + 1);
        $result = DBAL::update_table("users", $fields, $where);
        log_info("login failed $uid");
        return NULL;
    }

    $fields["result"] = 1;
    //Update success info of user_auth_history
    $stmt = DBAL::getInstance()->getConn()->prepare("INSERT INTO user_auth_history (user_id, user_name, client_name, client_ip, event, result, logger_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $username, $client_name, $clientip, "login", 0, $uid]);
    //clean account lock
    $result = DBAL::update_table('users', array('login_failure' => 0), $where);
    $reset_pwd = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("reset_password"), "userName=$username"));
//    log_info(DBAL::select_cell("users", "reset_password", $userName, "userName"));
    $info = array(
        "id" => $uid,
        "group_id" => $settings["groupId"],
        "access_token" => $settings["access_token"],
        "user_name" => $username,
        "reset_pwd" => $reset_pwd,
    );
    return $info;
}

function user_lock_check($username) {
    //check user lock status: login failure times
    $where = "configName='max_login_failure'";
    $max_login_failure = DBAL::db_select_first_value(DBAL::db_sql_select("serverconfigs", array("configValue"), $where));
    $where = "userName='$username'";
    $login_failure = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("login_failure"), $where));
    //lock account principle: fail 5 times and last failure in 5 seconds
    $table = "user_auth_history";
    $fields = array("create_time");
    $where = "user_name='$username' AND event='login'";
    $order_by = "create_time DESC";
    $last_login_failure_timestamp = strtotime(DBAL::db_select_first_value(DBAL::db_sql_select($table, $fields, $where, $order_by)));
    $current_timestamp = strtotime("now");

    if ($login_failure >= $max_login_failure) {
        //check user lock status: last login timestamp
        log_info("current $current_timestamp, last_login_failure_timestamp $last_login_failure_timestamp");
        if($current_timestamp - $last_login_failure_timestamp <= 5){
            //account locked
            return TRUE;
        } else {
            //clean account lock
            $result = DBAL::update_table('users', array('login_failure' => 0), array("userName" => $username));
            return FALSE;
        }
    } else {
        //account unlocked
        return FALSE;
    }
}

function user_approve_check($username) {
    //check user isApproval status
    $where = "userName='$username'";
    $isapproved = DBAL::db_select_first_value(DBAL::db_sql_select("users", array("isApproved"), $where));
    if ($isapproved) {
        return TRUE;
    }
    return FALSE;
}

function user_auth_create($name, $sha256ed) {
    $salt_length = 16;
    $salt_strong = TRUE;
    $salt = openssl_random_pseudo_bytes($salt_length, $salt_strong);
    $salt = bin2hex($salt);
    $hash = hash_pbkdf2("sha256", $sha256ed, $salt, 100000, 64);
    $auth = array(
        "name" => $name,
        "sha256ed" => $sha256ed,
        "hash_result" => $hash,
        "salt" => $salt,
    );
    return $auth;
}

function restore_point_delete($rpid) {
    $rp = db_restore_point_read($rpid);
    if (is_null($rp)) {
        log_error("Cannot read rpid $rpid");
        return NULL;
    }

    $result = db_restore_point_delete($rpid);
    if (is_null($result)) {
        log_error("Cannot delete rpid $rpid");
        return NULL;
    }

    // remove the folder for revision - 1, to align with the size calculation
    $rev = intval($rp["revision"]) - 1;
    if ($rev > 0) {
        $dir = path_join(array(tc_conf_dir("TC_USER_DATA"), $rp["ciid"], $rev));
        shell_delete_dir($dir);
        delete_seed($rp['udf']);
        delete_seed($rp['udf_rst']);
    }
    return $rpid;
}
// default functions to be invoked

function read_disk_usage() {
    $home = load_configuration_str("home");
    $total_bytes = disk_total_space($home);
    $free_bytes = disk_free_space($home);
    $used_bytes = $total_bytes - $free_bytes;
    $usage = array(
        "disk_total_gib" => round($total_bytes / (1024*1024*1024), 2),
        "disk_free_gib" => round($free_bytes / (1024*1024*1024), 2),
        "disk_used_gib" => round($used_bytes / (1024*1024*1024), 2),
        "disk_total_bytes" => $total_bytes,
        "disk_free_bytes" => $free_bytes,
        "disk_used_bytes" => $used_bytes,
        "disk_free_percent" => round($free_bytes * 100 / $total_bytes, 2),
        "disk_used_percent" => round($used_bytes * 100 / $total_bytes, 2),
    );
    return $usage;
}

function client_group_name($cgid) {
    $cgid = intval($cgid);
    if ($cgid === 0) {
        return "";
    }
    $group = db_client_group_read($cgid);
    if (is_null($group)) {
        log_warning("client group not found by id $cgid");
        return "";
    }
    return $group["cg_name"];
}

function client_group_members($cgid) {
    $c = DBAL::db_select(DBAL::db_sql_select(
        "machines",
        array("id", "machineName AS name", "mac"),
        "client_group='$cgid'"
    ));
    return $c;
}

function client_runtime_status($mac) {
    $c = DBAL::db_select_row(DBAL::db_sql_select(
        "userstatus",
        array("ip"),
        "mac='$mac'"
    ));
    return $c;
}

function client_is_busy($cid) {
    $c = DBAL::db_select_first_value(
        DBAL::db_sql_select(
            "machines m INNER JOIN userstatus u ON m.mac=u.mac",
            array("COUNT(m.id)"),
            "m.id='$cid'"
        ));
    return $c > 0;
}

function client_image_autoboot_ciid() {
    $fields = array("id");
    $where = "autoboot='y'";
    $res = DBAL::db_select_first_value(DBAL::db_sql_select("osimages", $fields, $where));
    if (is_null($res) || empty($res)) {
        return 0;
    }
    return intval($res);
}

function client_autoboot_ciid($client) {
    $cgid = intval($client["client_group"]);
    if ($cgid) {
        $fields = array("cg_autoboot_ciid");
        $where = "cgid='$cgid'";
        $ciid = DBAL::db_select_first_value(DBAL::db_sql_select("client_group", $fields, $where));
        if (is_null($ciid)) {
            log_warning("CANNOT get autoboot ciid for client group $cgid");
            return 0;
        }
        return $ciid;
    }
    return -1;
}

function client_mac2id($mac) {
    $mac = str_replace(":", "-", $mac);
    $sql_res = DBAL::db_select_first_value(DBAL::db_sql_select("machines", array("id"), "mac='$mac'"));
    return $sql_res ? $sql_res : NULL;
}

function client_image_uuid2id($uuid) {
    $uuid = strtolower($uuid);
    $sql_res = DBAL::db_select_first_value(DBAL::db_sql_select("imageupdatehistory", array("imageId AS id"), "uuid='$uuid'"));
    return $sql_res ? $sql_res : NULL;
}

function client_read($cid) {
    $clients = db_client_read($cid);
    if (is_null($clients)) {
        log_warning("CANNOT get clients info");
        return NULL;
    }
    if (empty($cid)) {
        foreach ($clients as &$r) {
            $r["client_group_name"] = client_group_name($r["client_group"]);
            $r["autoboot_user"] = $r["name"].constant("TC_AUTOBOOT_SUFFIX");
            $r["autoboot_ciid"] = client_autoboot_ciid($r);
            $r["autoboot_ciid_global"] = client_image_autoboot_ciid();
            $r["autoboot_image"] = "";
            $r["autoboot_image_global"] = "";
            if (intval($r["autoboot_ciid"]) > 0) {
                $r["autoboot_image"] = db_client_image_read($r["autoboot_ciid"], array("imageName"));
            }
            if (intval($r["autoboot_ciid_global"]) > 0) {
                $r["autoboot_image_global"] = db_client_image_read($r["autoboot_ciid_global"], array("imageName"));
            }
            $r["download_KBS"] = intval($r["download_KBS"]);
            $r["download_MBS"] = floatval($r["download_KBS"]) / 1000;
            $r["upload_KBS"] = intval($r["upload_KBS"]);
            $r["upload_MBS"] = floatval($r["upload_KBS"]) / 1000;
            $r["auto_login_delay"] = load_configuration_int("auto_login_delay");
            $r["usb_storage"] = intval($r["usb_storage"]);
        }
        return $clients;
    }
    // only one client in the result
    $r = $clients;
    $r["client_group_name"] = client_group_name($r["client_group"]);
    $r["autoboot_user"] = $r["name"].constant("TC_AUTOBOOT_SUFFIX");
    $r["autoboot_ciid"] = client_autoboot_ciid($r);
    $r["autoboot_ciid_global"] = client_image_autoboot_ciid();
    $r["autoboot_image"] = "";
    $r["autoboot_image_global"] = "";
    if (intval($r["autoboot_ciid"]) > 0) {
        $r["autoboot_image"] = db_client_image_read($r["autoboot_ciid"], array("imageName"));
    }
    if (intval($r["autoboot_ciid_global"]) > 0) {
        $r["autoboot_image_global"] = db_client_image_read($r["autoboot_ciid_global"], array("imageName"));
    }
    $r["download_KBS"] = intval($r["download_KBS"]);
    $r["download_MBS"] = floatval($r["download_KBS"]) / 1000;
    $r["upload_KBS"] = intval($r["upload_KBS"]);
    $r["upload_MBS"] = floatval($r["upload_KBS"]) / 1000;
    $r["auto_login_delay"] = load_configuration_int("auto_login_delay");
    $r["usb_storage"] = intval($r["usb_storage"]);


    $r["client_images_available"] = array();
    $r["client_profile"] = array();
    // read client profile
    $fields = array("id", "imageName", "ostype");
    $cis = DBAL::db_select(DBAL::db_sql_select("osimages", $fields));
    if (!is_null($cis)) {
        foreach ($cis as &$ci) {
            $ci["name"] = $ci["imageName"];
            unset($ci["imageName"]);

            // query uuid for client image revision 1
            $fields = array("uuid");
            $where = "revision=1 AND imageId=".$ci["id"];
            $record = DBAL::db_select_first_value(DBAL::db_sql_select("imageupdatehistory", $fields, $where));
            if(!is_null($record)) {
                $ci["base_uuid"] = $record;
            }

            $fields = array("id AS client_profile_id", "image_id", "profile_type", "profile_name", "profile_value", "profile_version", "create_time");
            $where = "machine_id=$cid";
            $profile = DBAL::db_select(DBAL::db_sql_select("client_profile", $fields, $where));
            if(!is_null($profile)) {
                $r["client_profile"] = $profile;
            }
        }
        $r["client_images_available"] = $cis;
    }

    return $r;
}

function seed_create($file_type, $file_path, $seed_file) {
    $seed_path = tc_conf_dir('TC_P2P_SEED') . '/' . $seed_file . '.seed';
    $seed_id = db_seed_create($file_type, $file_path, $seed_path);
    if (is_null($seed_id)) {
        return NULL;
    }
    $seed_path = DBAL::db_select_first_value(DBAL::db_sql_select("seed", array("seed_path"), "id=$seed_id"));
    shell_cmd_short(home_bin("tc-delivery-server", "add $seed_id $file_path $seed_path")." &> /dev/null &");
    return $seed_id;
}

function private_storage_read($psid) {
    $fields = array("revision", "image_file", "image_size", "mdf_file", "udf_file", "rst_mdf_file", "rst_udf_file");
    $res = DBAL::db_select_row(DBAL::db_sql_select('private_storage', $fields, "id=$psid"));
    return $res;
}

function private_storage_read_psid_head($uid) {
    $where = "user_id='$uid'";
    $psid = DBAL::db_select_first_value(DBAL::db_sql_select("private_storage", array("MAX(id)"), $where));
    return $psid;
}

function private_storage_delete($uid, $rev=NULL) {
    $user_ps = tc_conf_path_join("TC_PS_ROOT", $uid);

    if (is_null($rev)) {
        DBAL::db_delete("private_storage", "user_id='$uid'");
        shell_delete_dir($user_ps);
        return NULL;
    }
    $psid = private_storage_read_psid_head($uid);
    $ps = private_storage_read($psid);
    if (is_null($ps)) {
        return "e_ps_not_found";
    }
    if ($ps["revision"] != $rev) {
        return "e_ps_delete_head";
    }
    $img = tc_conf_path_join("TC_PS_ROOT", $ps["image_file"]);
    $mdf_rst = tc_conf_path_join("TC_PS_ROOT", $ps["rst_mdf_file"]);
    $udf_rst = tc_conf_path_join("TC_PS_ROOT", $ps["rst_udf_file"]);
    if (empty($img) || empty($mdf_rst) || empty($udf_rst)) {
        return "e_ps_delete_head";
    }
    // revert the changes based on RST files
    $res = update_blockio_file($img, $mdf_rst, $udf_rst);
    if (is_cmd_fail($res)) {
        log_error("cannot update the block io file, $img, $mdf_rst, $udf_rst");
        return "e_ps_delete_head";
    }
    $rev = intval($rev) - 1;
    $user_ps_rev = tc_path_join($user_ps, $rev);
    DBAL::db_delete("private_storage", "id='$psid'");
    shell_delete_dir($user_ps_rev);
    return NULL;
}

function update_user_revision($uid, $iid, $revision, $desc) {
    $where = array(
        'user_id' => $uid,
        'image_id' => $iid,
    );
    if (is_null($uid)) {
        // uid maybe null for reseting revision for all users
        $where = array("image_id" => $iid);
    }
    $changes = array(
        'user_revision' => $revision,
        'description' => $desc,
    );
    return DBAL::update_table('user_revision', $changes, $where);
}

/**
 * Restart dnsmasq if it is running
 * @return NULL
 */
function system_restart_dnsmasq() {
    if (is_dhcp_server_running()) {
        run_as_root("systemctl restart tcs-dnsmasq");
    }
}

/**
 * List all clients from userstatus table
 */
function client_list_read($filter=NULL) {
    $fields = array("ip");
    $sql = DBAL::db_sql_select("userstatus", $fields);

    if ($filter === "registered") {
        $sql = "SELECT u.ip
            FROM userstatus u
            INNER JOIN machines c ON c.mac=u.mac";
    }

    $result = DBAL::do_select($sql);
    return $result;
}

function client_list_store($client_list) {
    $store = tc_conf_path_join("TC_VAR", "cl-" . sugar_date_now());
    file_put_contents($store, "");
    $file = fopen($store, "r+");
    foreach ($client_list as $client) {
        fwrite($file, $client["ip"] . PHP_EOL);
        log_info("LOG ip address ".$client["ip"]);
    }
    fclose($file);
    return $store;
}

/**
 * Status list: unregistered, verified, expired, invalid
 * @return string status
 */
function lic2_license_status() {
    clearstatcache();

    if (!file_exists(constant("TC_LIC_LIC"))) {
        return "ls_unregistered";
    }

    if (!lic2_verify_hardware()) {
        return "ls_invalid";
    }

    if (!lic2_verify_expiration()) {
        return "ls_expired";
    }

    return "ls_verified";
}



function get_links_array(){
    global $links;
    $link_array = array();
    $lang = $GLOBALS["_TC2_"]["lang"];
    foreach ($links as $key => $value) {
        foreach ($value['links'] as $key2 => $value2) {
            if(array_key_exists("links", $value2)) {
                foreach ($value2['links'] as $key3 => $value3) {
                    $link_array[$key2] = $key3;
                }
            }
        }
    }
    return $link_array;
}



function read_permissions_by_user($user_id) {
    $sub_pages = get_links_array();
    $permissions = array();
    if (!TC_ENABLE_CUSTOMIZED_WEB_LOGIN){
        return $permissions;
    }
    $gid = DBAL::db_select_row(DBAL::db_sql_select("users", "groupId", "id = $user_id"));
    $gid1 = $gid['groupId'];
    $fields = array(" DISTINCT role_permission.allowed_url");
    $where = array("role_group_binds.group_id" => $gid1);
    $result = DBAL::select_rows("role INNER JOIN role_group_binds ON role_group_binds.role_id = role.role_id INNER JOIN role_permission ON role.role_id = role_permission.role_id",  $fields, $where);

    foreach ($result as $pms){
        array_push($permissions, $pms["allowed_url"]);
        if(!is_null($sub_pages)){
            foreach ($sub_pages as $key => $value) {
                if ($key == $pms["allowed_url"]) {
                    array_push($permissions, $value);
                }
            }
        }
    }
    return $permissions;
}

/**
 *  check whether tci-manual installed.
 *  If file exist, but not any return from file_exists, please check file/dir access right.
 */
function check_documents() {
    $result = array();
    $docs = array("TCI_CLIENT_IMAGE_HANDBOOK", "TCI_USER_MANUAL");
    foreach ($docs as $did) {
        if (file_exists(tc_conf_path_join("TC_DOC", constant($did)))) {
            $result[$did] = constant("$did");
        }
    }
    return empty($result) ? false : $result;
}

/**
 *  Permission check for CURD operations based on database in future
 */
function can_read($url, $user, $group) {
    if (empty($user) || empty($group)) {
        return FALSE;
    }
    $gid = intval($group);
    if ($gid <= 3) {
        return TRUE;
    }
    $permissions = read_permissions_by_user($user);
    array_push($permissions, "home");
    array_push($permissions, "reset_pwd");

    if (in_array($url, $permissions)){
        return TRUE;
    }
    return FALSE;
}


function peer_read($pid=0, $filter=NULL) {
    $fields = array("id", "name", "ip_addr");
    if ($pid === 0) {
        // read all peers, with filter function
        return DBAL::db_select(DBAL::db_sql_select("peer", $fields));
    }
    $res = DBAL::db_select_row(DBAL::db_sql_select('peer', $fields, "id=$pid"));
    return $res;
}

function peer_is_busy($pid) {
    $crt_image_peer = tc_relpath(constant("TC_CURRENT_SYNC_IMAGE_PEER"));
    if (file_exists($crt_image_peer)) {
        $result = run_bin_as_root("tc-ar", "stat $crt_image_peer");
        foreach ($result['output'] as $line) {
            // empty line is always in the end
            if (empty($line)) {
                continue;
            }
            $peer_image = json_decode($line, true);
        }
        $peer = peer_read($p_id);
        if ($peer_image["addr"] == $peer["ip_addr"]) {
            return TRUE;
        }
    }
    return FALSE;
}

function peer_delete($pid) {
    return DBAL::delete_row("peer", $pid);
}

function peer_update($pid, $changes) {
    return DBAL::db_update_table_row("peer", $changes, $pid);
}


function log_peer_create($peer_id, $user_id) {
    $fields = array(
        "peer_id" => $peer_id,
        "event" => "create",
        "user_id" => $user_id,
    );
    return DBAL::db_insert_table("peer_history", $fields);
}

function log_peer_delete($peer_id, $user_id) {
    $fields = array(
        "peer_id" => $peer_id,
        "event" => "delete",
        "user_id" => $user_id,
    );
    return DBAL::db_insert_table("peer_history", $fields);
}

function log_peer_update($peer_id, $user_id, $message) {
    $fields = array(
        "peer_id" => $peer_id,
        "event" => "update",
        "user_id" => $user_id,
        "message" => $message,
    );
    return DBAL::db_insert_table("peer_history", $fields);
}

function log_peer_sync($peer_id, $user_id, $message) {
    $fields = array(
        "peer_id" => $peer_id,
        "event" => "sync",
        "user_id" => $user_id,
        "message" => $message,
    );
    return DBAL::db_insert_table("peer_history", $fields);
}

function read_peer_history($event=NULL, $user=NULL, $peer=NULL) {
    $fields = array("id", "peer_id", "event", "user_id", "message", "timestamp");
    $where = array();
//  `event` enum('create','update','delete','sync') NOT NULL,
    if ($event) {
        if(in_array($event, array("create", "update", "delete", "sync"))) {
            $where[] = "event='$event'";
        } else {
            return NULL;
        }
    }
    if ($user) {
        if(sugar_valid_int($user)) {
            $where[] = "user_id='$user'";
        } else {
            return NULL;
        }
    }
    if ($peer) {
        if(sugar_valid_int($peer)) {
            $where[] = "peer_id='$peer'";
        } else {
            return NULL;
        }
    }
    if (empty($where)) {
        $where = NULL;
    } else {
        $where = ''.join($where, ' AND ');
    }

    return DBAL::db_select(DBAL::db_sql_select("peer_history", $fields, $where, "id"));
}

function user_group_read($gid=NULL) {
    $fields = array("id", "groupName AS group_name", "groupDesc AS group_desc", "createTime AS create_time", "ps_background_upload");
    $where = NULL;
    if (is_null($gid)) {
        return DBAL::db_select(DBAL::db_sql_select("usergroups", $fields, $where, "id"));
    }
    $where = "id='$gid'";
    return DBAL::db_select_row(DBAL::db_sql_select("usergroups", $fields, $where));
}

load_global_config();
// connectDatabase();

?>
