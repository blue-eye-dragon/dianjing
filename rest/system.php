<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");


function license_file_backup($lic_file, $lic_back) {
    log_info("LIC2 backup license file");
    if(is_cmd_fail(shell_copy($lic_file, $lic_back))) {
        log_warning("LIC2 backup license file failed");
    }
}

function license_file_restore($lic_file, $lic_back) {
    if (!file_exists($lic_back)) {
        log_warning("LIC2 no license backup file");
        return;
    }
    log_warning("LIC2 restore backup file");
    if(is_cmd_fail(shell_rename($lic_back, $lic_file))) {
        log_warning("LIC2 restore backup file failed");
    }
}

function handle_post($target, $params, $body) {
    auth_login_required();

    $raw = safe_array_get('license2', $body);
    if (is_null($raw)) {
        return rest_error("e_operation_unsupported");
    }
    // no good way to check if it is uuencoded, so suppress it
    $lic = @convert_uudecode($raw);
    if (!$lic) {
        log_warning("LIC2 invalid license file decoded");
        return rest_error('e_invalid_license');
    }
    $lic_file = constant("TC_LIC_LIC");
    $lic_back = "$lic_file-backup";
    if (file_exists($lic_file)) {
        license_file_backup($lic_file, $lic_back);
    }
    if (!file_put_contents($lic_file, $lic)) {
        license_file_restore($lic_file, $lic_back);
        return rest_error("e_lic_lic_write");
    }
    $license_status = lic2_license_status();
    if ($license_status !== 'ls_verified') {
        log_warning("LIC2 registration failed, lic restored, $license_status");
        license_file_restore($lic_file, $lic_back);
        return rest_error($license_status);
    }
    // delete backup license file if new license is valid
    if(is_cmd_fail(shell_delete($lic_back))) {
        log_warning("LIC2 delete backup license file failed");
    }
    return rest_result_ok();
}

function read_servers() {
    $servers = array();

    $servers['disk'] = array(
        'status' => systemd_service_alive("tcs-disk-server") ? 'on' : 'off',
    );

    $servers['dnsmasq.dhcp'] = array(
        'status' => is_cmd_done(run_bin_as_root("tc-config", "dnsmasq.dhcp", FALSE, FALSE)) ? 'on' : 'off',
    );

    $servers['dnsmasq.dns'] = array(
        'status' => is_cmd_done(run_bin_as_root("tc-config", "dnsmasq.dns", FALSE, FALSE)) ? 'on' : 'off',
    );

    $ret = run_bin("tc-config", "status", FALSE);
    $servers["loaded"] = $ret["output"];

    $ret = run_bin("tc-config", "status.all", FALSE);
    $servers["installed"] = $ret["output"];

    return $servers;
}

function count_clients() {
    $count = DBAL::db_select_first_value_old("SELECT COUNT(id) FROM machines");
    return $count;
}

function lic2_license_profile() {
    if (file_exists(constant("TC_LIC_LIC"))) {
        $content = file_get_contents(constant("TC_LIC_LIC"));
        if ($content) {
            $lic_string = @simplexml_load_string($content);
            if ($lic_string === FALSE) {
                return "";
            }
            $lic_array = json_decode(json_encode($lic_string), TRUE);
            log_info("LIC2 ".json_encode($lic_array));
            $ixml = $lic_array['content']['issuer']['@attributes'];
            $uxml = $lic_array['content']['redistributor']['@attributes'];
            $pub = array(
                "i_name" => $ixml["name"],
                "i_contact" => $ixml["contact"],
                "i_email" => $ixml["email"],
                "i_tel" => $ixml["telephone"],
                "i_addr" => $ixml["address"],
                "u_name" => $uxml["name"],
                "u_contact" => $uxml["contact"],
                "u_email" => $uxml["email"],
                "u_tel" => $uxml["telephone"],
                "u_addr" => $uxml["address"],
            );
            return $pub;
        }
    }
    return "";
}

function lic2_encode_key($encode="uuencode") {
    $res = run_bin("network", "mac");
    if (is_cmd_fail($res)) {
        log_warning("LIC2 cannot read nic MAC");
        return FALSE;
    }
    $mac = $res["last_line"];
    if (is_null($mac)) {
        log_warning("LIC2 cannot read nic for key");
        return "";
    }
    $content = json_encode(array("mac" => $mac));
    if ($encode === "base64") {
        return base64_encode($content);
    }
    return convert_uuencode($content);
}

function lic2_license_nature() {
    $nature = array (
        "expire" => "",
        "ccount" => "",
    );
    if (file_exists(constant("TC_LIC_LIC"))) {
        $rest = lic2_license_expired_time();
        if (rest_result_success($rest)) {
            date_default_timezone_set('Asia/Shanghai');
            $nature['expire'] = date("Y-m-d H:i:s", rest_result_content($rest));
        }
        $rest = lic2_license_client_count();
        if (rest_result_success($rest)) {
            $nature['ccount'] = rest_result_content($rest);
        }
    }
    return $nature;
}

function auto_client_naming() {
    $client_naming = load_configuration_bool('client_naming');
    $client_naming_prefix = load_configuration_str('client_naming_prefix');
    $client_naming_suffix = load_configuration_str('client_naming_suffix');
    $client_naming_width = load_configuration_int('client_naming_width');
    $client_naming_first = load_configuration_int('client_naming_first');
    if (!$client_naming) {
        return rest_error("e_operation_denied");
    }
    if (strlen($client_naming_first) > $client_naming_width) {
        return rest_error("e_client_naming_max");
    }
    $first = intval($client_naming_first);
    save_configuration('client_naming_first', $first+1);

    $names = sugar_expand_regex(
        $client_naming_prefix,
        $client_naming_suffix,
        $first,
        1,
        $client_naming_width
    );
    if (sizeof($names)) {
        return rest_result_ok($names[0]);
    }
    return rest_error("e_client_naming");
}

function load_tracker_wan_ip() {
    if (file_exists(constant("TC_TRACKER_WAN_PATH"))) {
        $lines = file_get_contents(constant("TC_TRACKER_WAN_PATH"));
        $its = explode(" ", $lines);
        return $its[0];
    }
    return "";
}


function load_nic_list(){
    $nic_list = shell_cmd("ip link show | grep BROADCAST | awk -F ':' '{print $2}' ");
    return array_map('trim', $nic_list["output"]);
}

function handle_get($target, $params, $body) {
    $topic = array_shift($target);
    if ($topic == "servers") {
        return rest_result_ok(read_servers());
    } else if ($topic == "settings") {
        return rest_result_ok(array(
            "lang" => load_configuration_str('lang'),
            "page_refresh" => load_configuration_bool('page_refresh'),
            "client_pagefile" => load_configuration_int('client_pagefile'),
            "ps_sizes" => load_configuration_str('ps_sizes'),
            "ps_cache_ratio" => floatval(load_configuration_str('ps_cache_ratio')),
            "ps_background_upload" => load_configuration_bool('ps_background_upload'),
            "os_types" => load_configuration_str('os_types'),
            "backup_mode" => load_configuration_str('backup_mode'),
            "backup_local_location" => load_configuration_str('backup_local_location'),
            "backup_remote_location" => load_configuration_str('backup_remote_location'),
            "heartbeat_timeout" => load_configuration_int('heartbeat_timeout'),
            "auto_login_delay" => load_configuration_int('auto_login_delay'),
            "client_open_registration" => load_configuration_bool('client_open_registration'),
            "client_naming" => load_configuration_bool('client_naming'),
            "client_naming_prefix" => load_configuration_str('client_naming_prefix'),
            "client_naming_suffix" => load_configuration_str('client_naming_suffix'),
            "client_naming_width" => load_configuration_int('client_naming_width'),
            "client_naming_first" => load_configuration_int('client_naming_first'),
            "ad_domain_enable" => load_configuration_bool('ad_domain_enable'),
            "ad_domain_server_name" => load_configuration_str('ad_domain_server_name'),
            "ad_domain_server_ip" => load_configuration_str('ad_domain_server_ip'),
            "tracker_wan_ip" => load_tracker_wan_ip(),
            "ssl_encryption" => load_configuration_bool('ssl_encryption'),
            "client_language" => load_configuration_str('client_language'),
            "nic_list" => load_nic_list(),
        ));
    } else if ($topic == "license") {
        $revision = read_last_line(run_bin("tc-config", "revision"));
        $micro = read_last_line(run_bin("tc-config", "micro"));
        $release = read_last_line(run_bin("tc-config", "release"));

        return rest_result_ok(array(
            "license_profile" => lic2_license_profile(),
            "license_key" => lic2_encode_key(),
            "license_key_base64" => lic2_encode_key("base64"),
            "license_status" => lic2_license_status(),
            "license_nature" => lic2_license_nature(),
            "revision" => "$revision$micro",
            "release" => "TCI $release",
        ));
    } else if ($topic == "filesystem") {
        return rest_result_ok(read_disk_usage());
    } else if ($topic === "auto_client_naming") {
        return auto_client_naming();
    } else if ($topic === "documents") {
        $docs = check_documents();
        if (!$docs) {
            return rest_error();
        }
        return rest_result_ok($docs);
    }
    $info = array(
        "client_registered" => count_clients(),
    );
    return rest_result_ok($info);
}

function validator_client_naming_prefix_suffix($value) {
    if (empty($value)) {
        return TRUE;
    }
    if (strlen($value) > TC_CLIENT_NAMING_PREFIX_SUFFIX_MAX) {
        return FALSE;
    }

    return sugar_valid_alnum($value, TC_CLIENT_NAMING_EX);
}

function validator_ad_domain_server_name($value) {
    if (empty($value)) {
        return TRUE;
    }
    if (strlen($value) > TC_AD_DOMAIN_SERVER_NAME_MAX) {
        return FALSE;
    }

    return sugar_valid_alnum($value, TC_DEFAULT_AD_DOMAIN_NAME_RULE);
}

function handle_put($target, $params, $body) {
    auth_login_required();

    if (array_key_exists("tracker_wan", $body)) {
        $value = $body["tracker_wan"];
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($value)) {
            return rest_error("e_invalid_format_boolean");
        }

        if ($value) {
            $wanip = load_tracker_wan_ip();
            if (empty($wanip)) {
                return rest_error("e_tracker_wan_ip");
            }
            run_as_root("systemctl start tcs-delivery-static-tracker");
            run_as_root("systemctl enable tcs-delivery-static-tracker");
        } else {
            run_as_root("systemctl stop tcs-delivery-static-tracker");
            run_as_root("systemctl disable tcs-delivery-static-tracker");
        }
        return rest_result_ok();
    }
    if (array_key_exists("tracker_wan_ip", $body)) {
        if (systemd_service_alive("tcs-delivery-static-tracker")) {
            return rest_error("e_tracker_wan_busy");
        }

        $port = constant("TC_TRACKER_WAN_PORT");
        $conf = constant("TC_TRACKER_WAN_PATH");

        if (empty($body["tracker_wan_ip"])) {
            shell_delete($conf);
        } else {
            $value = sugar_valid_ip4($body["tracker_wan_ip"]);
            if (empty($value)) {
                return rest_error("e_tracker_wan_ip");
            }
            shell_cmd_short("echo $value $port > $conf");
        }
        return rest_result_ok();
    }
    if (array_key_exists("ssl_encryption", $body)) {
        $entry = "ssl_encryption";
        $value = filter_var($body[$entry], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($value)) {
            return rest_error("e_invalid_format_boolean");
        }

        if ($value) {
            run_bin_as_root("tc-config", "socket.ssl true");
        } else {
            run_bin_as_root("tc-config", "socket.ssl false");
        }

        return rest_result_ok();
    }
    if (array_key_exists("client_language", $body)) {
        $entry = "client_language";
        if($body[$entry] == "zh" || $body[$entry] == "en") {
            run_bin_as_root("tc-config", "client.lang $body[$entry]");
        } else {
            return rest_error("e_invalid_format_client_language");
        }

        return rest_result_ok();
    }
    if (array_key_exists("nic_setting", $body)) {
        if (empty($body["nic_setting"])) {
            return rest_error("e_operation_unsupported");
        }
        $value = $body["nic_setting"];
        $ipv4 = run_bin("network", "cidr");
        if (is_cmd_done($ipv4)) {
            $ipv4 = $ipv4["last_line"];
            run_bin_as_root("tc-config", "nic.init $value $ipv4");
            return rest_result_ok();
        }
        run_bin_as_root("tc-config", "nic.init $value");
        return rest_error("e_operation_denied");
    }
    if (array_key_exists('heartbeat_timeout', $body)) {
        $min = intval(constant("TC_HEARTBEAT_TIMEOUT_MIN"));
        $max = intval(constant("TC_HEARTBEAT_TIMEOUT_MAX"));

        $timeout = filter_var(
            $body['heartbeat_timeout'],
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'min_range' => $min,
                    'max_range' => $max
                )
            )
        );

        if ($timeout) {
            save_configuration("heartbeat_timeout", $timeout);
            return rest_result_ok($timeout);
        }
        return rest_error("e_hb_timeout_value");
    }
    if (array_key_exists('auto_login_delay', $body)) {
        $delay = filter_var(
            $body['auto_login_delay'],
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'min_range' => constant("TC_CLIENT_AUTO_LOGIN_DELAY_MIN"),
                    'max_range' => constant("TC_CLIENT_AUTO_LOGIN_DELAY_MAX")
                )
            )
        );

        if ($delay) {
            save_configuration("auto_login_delay", $delay);
            return rest_result_ok($delay);
        }
        return rest_error("e_invalid_auto_login_delay");
    }
    $error = save_settings_entry_boolean($body, "client_open_registration");
    if ($error) {
        return rest_error($error);
    }
    $error = save_settings_entry_boolean($body, "client_naming");
    if ($error) {
        return rest_error($error);
    }
    $error = save_settings_entry_boolean($body, "ps_background_upload");
    if ($error) {
        return rest_error($error);
    }
    $error = save_settings_entry_int($body, "client_naming_width", TC_CLIENT_NAMING_WIDTH_MIN, TC_CLIENT_NAMING_WIDTH_MAX);
    if ($error) {
        return rest_error("e_client_naming_width");
    }
    $error = save_settings_entry_int($body, "client_naming_first", TC_CLIENT_NAMING_FIRST_MIN, TC_CLIENT_NAMING_FIRST_MAX);
    if ($error) {
        return rest_error("e_client_naming_first");
    }
    $error = save_settings_entry_str($body, "client_naming_prefix", "validator_client_naming_prefix_suffix");
    if ($error) {
        return rest_error("e_client_naming_prefix_suffix");
    }
    $error = save_settings_entry_str($body, "client_naming_suffix", "validator_client_naming_prefix_suffix");
    if ($error) {
        return rest_error("e_client_naming_prefix_suffix");
    }
    $error = save_settings_entry_boolean($body, "ad_domain_enable");
    if ($error) {
        return rest_error($error);
    }
    $error = save_settings_entry_str($body, "ad_domain_server_name", "validator_ad_domain_server_name");
    if ($error) {
        return rest_error("e_ad_domain_server_name");
    }
    $error = save_settings_entry_str($body, "ad_domain_server_ip", "sugar_valid_ip4_empty");
    if ($error) {
        return rest_error("e_ad_domain_server_ip");
    }
    return rest_result_ok();
}


$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
