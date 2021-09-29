<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require("rest.inc.php");
require("../libs/libtc.php");


function handle_get($target, $params, $body) {
    return rest_result_ok();
}

function handle_put($target, $params, $body) {

    if (array_key_exists("lang", $params)) {
        save_config_lang($params["lang"]);
        return rest_result_ok($GLOBALS["_TC2_"]["lang"]);
    }

    return rest_result_ok();
}

function parse_ip_array($body) {
    $ip = trim(safe_array_get("ip", $body, ""));
    if (strlen($ip) > 0) {
        return array($ip);
    }
    $cgid = sugar_valid_int(safe_array_get("client_group", $body), 0);
    if ($cgid === 0) {
        return array();
    }
    if (!$cgid) {
        log_warning("invalid client group $cgid");
        return NULL;
    }
    $ips = array();
    $clients = client_group_members($cgid);
    foreach ($clients as $c) {
        $info = client_runtime_status($c["mac"]);
        if ($info) {
            array_push($ips, $info["ip"]);
        }
    }
    return $ips;
}

function clients_to_macs($clients) {
    $macs = array();
    foreach ($clients as $c) {
        array_push($macs, str_replace("-", ":", $c["mac"]));
    }
    return $macs;
}

function parse_mac_array($body) {
    $mac = trim(safe_array_get("mac", $body, ""));
    if (strlen($mac) > 0) {
        return array($mac);
    }
    $cgid = sugar_valid_int(safe_array_get("client_group", $body), 0);
    if ($cgid === 0) {
        return clients_to_macs(client_read(NULL));
    }
    if (!$cgid) {
        log_warning("invalid client group $cgid");
        return NULL;
    }
    return clients_to_macs(client_group_members($cgid));
}

function handle_post($target, $params, $body) {
    run_bin("tc-config", "status.all > /opt/tci/var/tcs.status");    

    $rpc = safe_array_get("rpc", $body);
    if ($rpc == "reboot-client") {
        //
        // reboot all clients if ip is empty, or the ip specified client
        //
        $cips = parse_ip_array($body);
        if (is_null($cips)) {
            return rest_error("e_operation_unsupported");
        }
        if (empty($cips)) {
            send_cc_command_registered("--reboot");
            return rest_result_ok();
        }
        foreach ($cips as $cip) {
            send_cc_command("--reboot --ip $cip");
        }
    } else if ($rpc == "shutdown-client") {
        //
        // shutdown all clients if ip is empty, or the ip specified client
        //
        $cips = parse_ip_array($body);
        if (is_null($cips)) {
            return rest_error("e_operation_unsupported");
        }
        if (empty($cips)) {
            send_cc_command_registered("--poweroff");
            return rest_result_ok();
        }
        foreach ($cips as $cip) {
            send_cc_command("--poweroff --ip $cip");
        }
    } else if ($rpc == "wake-on-lan-client") {
        //
        // WOL for a single client or a client group
        //
        $macs = parse_mac_array($body);
        if (is_null($macs)) {
            return rest_error("e_operation_unsupported");
        }
        if (empty($macs)) {
            return rest_error("e_mac_required");
        }
        $nic = current_nic();
        $cmd = "ether-wake -i $nic";
        if (safe_array_get("broadcast", $body, FALSE)) {
            $cmd = "ether-wake -i $nic -b";
        }
        $error_count = 0;
        foreach ($macs as $mac) {
            if (sugar_valid_mac($mac)) {
                $result = run_as_root($cmd . " $mac");
                if (is_cmd_fail($result)) {
                    log_error("Cannot WOL client $mac");
                    $error_count += 1;
                }
            } else {
                log_error("invalid format for mac [$mac]");
                $error_count += 1;
            }
        }
        if ($error_count) {
            return rest_error("e_mac_required");
        }

    } else if ($rpc === "start-system") {
        //
        // start TC system disk server with NIC defined in tc.ini
        //
        $nic = current_nic();
        if (!systemd_service_alive("tcs-disk-server")) {
            $res = run_as_root("systemctl start tcs-disk-server");
            if (is_cmd_fail($res)) {
                $res2 = run_as_root("systemctl show tcs-disk-server | grep ExecStart | grep 255");
                if (is_cmd_done($res2)) {
                    return rest_error("e_invalid_license");
                }
                return rest_error_shell($res);
            }
        }

        if (!is_ccontrol_server_running()) {
            $pid_file = tc_relpath(constant("TC_CCONTROL_PID_PATH"));
            if (file_exists($pid_file)) {
                run_as_root("rm -f $pid_file");
            }
            $res = run_as_root("systemctl start tcs-ccontrol-server");
            if (is_cmd_fail($res)) {
                return rest_error_shell($res);
            }
        }

        $res = run_bin_as_root("network", "dhcp start");
        if (is_cmd_fail($res)) {
            return rest_error_shell($res);
        }
        systemd_service_start("tcs-mudf-server");
        systemd_service_start("tcs-delivery-server");
        systemd_service_start("tcs-delivery-tracker");
        systemd_service_start("tcs-delivery-static-tracker");
    } else if ($rpc === "stop-system") {
        //
        // check if the service is running or return value will be 1 for nothing killed
        //
        systemd_service_stop("tcs-ccontrol-server");
        systemd_service_stop("tcs-disk-server");

        // cleanup userstatus for online clients
        DBAL::db_delete("userstatus", "id > 0");

        // stop DHCP service also
        $res = run_bin_as_root("network", "dhcp stop");
        if (is_cmd_fail($res)) {
            return rest_error_shell($res);
        }

        systemd_service_stop("tcs-mudf-server");
        systemd_service_stop("tcs-delivery-server");
        systemd_service_stop("tcs-delivery-tracker");
        systemd_service_stop("tcs-delivery-static-tracker");

    } else if ($rpc == "init-client") {
        //
        // initialize all clients if ip is empty, or the ip specified client
        //
        $pagefile = load_configuration_int("client_pagefile");
        $cips = parse_ip_array($body);
        if (is_null($cips)) {
            return rest_error("e_operation_unsupported");
        }
        if (empty($cips)) {
            send_cc_command_registered("--initialize --user-partsz $pagefile");
            return rest_result_ok();
        }
        foreach ($cips as $cip) {
            send_cc_command("--initialize --user-partsz $pagefile --ip $cip");
        }

    } else if ($rpc == "start-dhcp") {
        $result = run_bin_as_root("network", "dhcp start");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }

    } else if ($rpc == "stop-dhcp") {
        $result = run_bin_as_root("network", "dhcp stop");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }

    } else if ($rpc == "start-dnsmasq-dhcp") {
        $result = run_bin_as_root("tc-config", "dnsmasq.dhcp start");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }
    } else if ($rpc == "start-dnsmasq-dns") {
        $result = run_bin_as_root("tc-config", "dnsmasq.dns start");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }
    } else if ($rpc == "stop-dnsmasq-dhcp") {
        $result = run_bin_as_root("tc-config", "dnsmasq.dhcp stop");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }
    } else if ($rpc == "stop-dnsmasq-dns") {
        $result = run_bin_as_root("tc-config", "dnsmasq.dns stop");
        if (is_cmd_fail($result)) {
            return rest_error_shell($result);
        }
    }

    return rest_result_ok();
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
