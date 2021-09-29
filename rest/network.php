<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require('../libs/libtc.php');

function validate_dhcp_router($router) {
    $settings = parse_dhcp_settings();
    $mask = $settings['mask'];
    $begin = $settings['begin'];
    $subnet = sugar_ip4_op_mask($begin, $mask);
    log_info("DHCP subnet $subnet");
    if (sugar_ip4_op_same(sugar_ip4_op_mask($router, $mask), $subnet)) {
        if (sugar_ip4_op_range($router, $subnet)) {
            return NULL;
        }
    }
    return "e_invalid_dhcp_router";
}

function validate_dhcp_range($begin, $end, $mask) {
    $netmask = read_last_line(run_bin_as_root("network", "mask"));
    // if (!sugar_ip4_op_same($mask, $netmask)) {
    //     return "e_invalid_dhcp_subnet_mask";
    // }
    // this is the new subnet according to new DHCP range and mask
    $subnet = sugar_ip4_op_mask($begin, $mask);
    if (!sugar_ip4_op_same(sugar_ip4_op_mask($end, $mask), $subnet)) {
        return "e_invalid_dhcp_subnet_range";
    }
    if (sugar_ip4_op_compare($begin, $end) < 0) {
        $size = ip2long($end) - ip2long($begin);
        if ($size < sugar_ip4_net_size($mask)) {
            return NULL;
        }
    }
    return "e_invalid_dhcp_subnet_range";
}

/**
 * Validate rules
 *   - 1 ip format is valid, mask format is valid
 *   - 2 dhcp_subnet_mask == mask
 *   - 3 dhcp_subnet = dhcp_subnet_begin & dhcp_subnet_mask
 *   - 4 dhcp_subnet < dhcp_subnet_begin < dhcp_subnet_end
 *   - 5 dhcp_subnet_begin & dhcp_subnet_mask == dhcp_subnet_end & dhcp_subnet_mask
 *   - 6 dhcp_subnet_mask contains dhcp subnet range
 *   - 7 dhcp_subnet < dhcp_router
 */
function handle_put($target, $params, $body) {
    $result = array();
    $restart_nic = FALSE;
    $restart_dhcp = FALSE;
    $nic = current_nic();

    $addr = safe_array_get('addr', $body);
    if (!is_null($addr)) {
        $valid = sugar_valid_ip4($addr);
        if (!$valid) {
            return rest_error('e_bad_ip_address');
        }

        // retrive current IP address for recovery
        $old_ip = read_last_line(run_bin("network", "ip"));

        // add mask support for format like 255.255.255.0
        // use ipcalc to check it
        $mask = safe_array_get('mask', $body, "");
        if ($mask && is_cmd_fail(shell_cmd_short("ipcalc -c 0.0.0.0 $mask"))) {
            return rest_error("e_bad_format_netmask");
        }

        // using the old IP for management
        // notify clients with new IP address based on the old IP
        send_cc_command_registered("--change-server --server-ip $valid");

        if (is_cmd_fail(run_bin_as_root("network", "ip $nic $valid $mask"))) {
            log_warning("Restore IP address to $old_ip");
            send_cc_command_registered("--change-server --server-ip $old_ip");
            run_bin_as_root("network", "ip $nic $old_ip $mask");
            return rest_error('e_fail_change_ip');
        }

        $restart_nic = TRUE;
        $restart_dhcp = TRUE;
    }

    $nic_gw = safe_array_get('gateway', $body);
    if (!is_null($nic_gw)) {
        $valid = sugar_valid_ip4($nic_gw);
        if (!$valid && $nic_gw !== "") {
            return rest_error('e_bad_ip_address');
        }
        if ($nic_gw === "") {
            if (is_cmd_fail(call_home_bin("network", "gateway $nic delete"))) {
                log_warning("Fail to delete $nic gateway");
                return rest_error('e_fail_change_gw');
            }
        } else {
            if (is_cmd_fail(call_home_bin("network", "gateway $nic $valid"))) {
                log_warning("Fail to update $nic gateway, $nic_gw");
                return rest_error('e_fail_change_gw');
            }
        }
        $restart_nic = TRUE;
    }

    $nic_dns = safe_array_get('dns', $body);
    if (!is_null($nic_dns)) {
        $valid = sugar_valid_ip4($nic_dns);
        if (!$valid && $nic_dns !== "") {
            return rest_error('e_bad_ip_address');
        }
        if ($nic_dns === "") {
            $dns = shell_last_line(home_bin("network", "dns $nic"));
            if ($dns) {
                if (is_cmd_fail(call_home_bin("network", "nameserver del $dns"))) {
                    log_warning("Fail to delete nameserver $dns from resolv.conf");
                    return rest_error('e_fail_change_dns');
                }
            }            
            if (is_cmd_fail(call_home_bin("network", "dns $nic delete"))) {
                log_warning("Fail to delete $nic dns");
                return rest_error('e_fail_change_dns');
            }
        } else {
            if (is_cmd_fail(call_home_bin("network", "dns $nic $valid"))) {
                log_warning("Fail to update $nic dns, $nic_dns");
                return rest_error('e_fail_change_dns');
            }
        }
        $restart_nic = TRUE;
    }

    $dhcp_router = safe_array_get('dhcp_router', $body);
    if ($dhcp_router) {
        $valid = sugar_valid_ip4($dhcp_router);
        if (!$valid) {
            return rest_error("e_bad_ip_address");
        }
        $err = validate_dhcp_router($valid);
        if ($err) {
            return rest_error($err);
        }
        if (is_cmd_fail(call_home_bin("network", "dhcp router $valid"))) {
            return rest_error("e_fail_change_dhcp_router");
        }

        $restart_dhcp = TRUE;
        $result['dhcp_router'] = $valid;
    }

    $dhcp_subnet_begin = safe_array_get('dhcp_subnet_begin', $body);
    $dhcp_subnet_end = safe_array_get('dhcp_subnet_end', $body);
    $dhcp_subnet_mask = safe_array_get('dhcp_subnet_mask', $body);
    if ($dhcp_subnet_begin && $dhcp_subnet_end && $dhcp_subnet_mask) {
        $begin = sugar_valid_ip4($dhcp_subnet_begin);
        $end = sugar_valid_ip4($dhcp_subnet_end);
        $mask = $dhcp_subnet_mask;
        if (is_cmd_fail(shell_cmd("ipcalc -c 0.0.0.0 $mask"))) {
            return rest_error("e_bad_format_netmask");
        }
        if (!$begin) {
            return rest_error_extra("e_invalid_dhcp_subnet_range", 0, $dhcp_subnet_begin);
        }
        if (!$end) {
            return rest_error_extra("e_invalid_dhcp_subnet_range", 0, $dhcp_subnet_end);
        }
        $err = validate_dhcp_range($begin, $end, $mask);
        if ($err) {
            return rest_error($err);
        }
        if (is_cmd_fail(call_home_bin("network", "dhcp range $begin $end $mask"))) {
            return rest_error("e_invalid_dhcp_subnet_range");
        }

        $restart_dhcp = TRUE;
        $result['dhcp_subnet_begin'] = $begin;
        $result['dhcp_subnet_end'] = $end;
        $result['dhcp_subnet_mask'] = $mask;
    }

    $dhcp_dns = safe_array_get('dhcp_dns', $body, NULL);
    if ($dhcp_dns !== NULL) {
        if ($dhcp_dns === "") {
            $res = run_bin("network", "ip");
            if (is_cmd_fail($res)) {
                log_error("Cannot read IP for nic");
            }
            $nic_ip = $res["last_line"];
            $settings = parse_dhcp_settings();
            $dhcp_dns = $settings['dns'];
            if ( $dhcp_dns && $nic_ip != $dhcp_dns) {
                if (is_cmd_fail(call_home_bin("network", "dhcp dns del $dhcp_dns"))) {
                    return rest_error("e_fail_change_dhcp_dns");
                }
            }
            $result['dhcp_dns'] = "";
        } else {
            $valid = sugar_valid_ip4($dhcp_dns);
            if (!$valid) {
                return rest_error("e_bad_ip_address");
            }
            if (is_cmd_fail(call_home_bin("network", "dhcp dns add $valid"))) {
                return rest_error("e_fail_change_dhcp_dns");
            }
            $result['dhcp_dns'] = $valid;
        }
        $restart_dhcp = TRUE;
    }

    if ($restart_nic) {
        call_home_bin("network", "restart $nic");
        run_as_root("systemctl restart tcs-disk-server");
        run_as_root("systemctl restart tcs-delivery-server");
        run_as_root("systemctl restart tcs-ccontrol-server");
    }
    if ($restart_dhcp) {
        call_home_bin("network", "dhcp restart");
    }

    return rest_result_ok($result);
}

function handle_delete($target, $params, $body) {
    $topic = array_shift($target);
    if($topic == "dhcp") {
        $option = db_safe_array_get("option", $body);
        if (is_null($option)) {
            return rest_error("e_operation_unsupported");
        }
        if ($option == "empty_leases") {
            $lease_file = tc_relpath(constant("TC_DHCP_LEASE_FILE"));
            run_as_root("systemctl stop tcs-dnsmasq");
            shell_cmd_short("truncate -s 0 $lease_file");
            run_as_root("systemctl start tcs-dnsmasq");
            return rest_result_ok();
        }
    }
    return rest_result_ok(array(
        "target" => $target,
        "params" => $params,
        "body" => $body,
    ));
}

function handle_post($target, $params, $body) {
    $uploaded = rest_save_upload2();
    if (is_null($uploaded)) {
        return rest_error("e_dhcp_import_empty");
    }
    $tarball = $uploaded["file_path"];
    $password = constant("TC_DHCP_SETTINGS_PWD");
    $result = call_home_bin("network", "import $tarball $password");

    if (is_cmd_done($result)) {
        return rest_result_ok();
    }

    return rest_error("e_dhcp_import");
}

function calc_usage_tx_percent() {
    $nic = current_nic();
    $tx_bytes_file = "/sys/class/net/$nic/statistics/tx_bytes";
    $speed_file = "/sys/class/net/$nic/speed";
    $stat_file = "/opt/tci/var/stat_$nic";

    $before = 0;
    $after_txbytes = 0;
    if (file_exists($stat_file)) {
        $before = file_get_contents($stat_file);
    }
    if (file_exists($tx_bytes_file)) {
        $after_txbytes = file_get_contents($tx_bytes_file);        
    }
    if ($before === FALSE || $after_txbytes === FALSE) {
        log_warning("Cannot calc tx usage, $before, $after_txbytes");
        return 0;
    }

    $after_timestamp = time();
    $after_txbytes = floatval($after_txbytes);
    $before_timestamp = 0;
    $before_txbytes = 0;
    $ws = preg_split("/\s+/", $before);
    if (count($ws) > 1) {
        $before_timestamp = floatval($ws[0]);
        $before_txbytes = floatval($ws[1]);
    }
//    log_info("B $before_timestamp $before_txbytes");

    if ($after_txbytes) {
        $line = "$after_timestamp $after_txbytes\n";
        $result = file_put_contents($stat_file, $line);
    }
//    log_info("A $after_timestamp $after_txbytes");
    if (!file_exists($speed_file)) {
        log_error("Cannot find nic $nic");
        return 0;
    }
    $speed_mbps = file_get_contents($speed_file);
    // speed in bytes
    $bytes = $after_txbytes - $before_txbytes;
    $duration = $after_timestamp - $before_timestamp;
//    log_info("BYTES $bytes DURATION $duration");
    if ($duration == 0) {
        $tx_speed = $bytes / 0.5;
    } else {
        $tx_speed = $bytes / $duration;
    }
    $percent = 0;
    if ($speed_mbps) {
        $percent = round($tx_speed * 8 * 100 / 1000 / 1000 / $speed_mbps);
    }
    if ($percent > 100) {
        $percent = 100;
    }
//    log_info("PERCENT $percent SPEED $tx_speed");
    return $percent;
}

function parse_dhcp_settings() {
    $settings = array(
        "begin" => "",
        "end" => "",
        "mask" => "",
        "router" => "",
        "dns" => ""
    );
    $result = call_home_bin("network", "dhcp");
    if (is_cmd_done($result)) {
        foreach ($result['output'] as $line) {
            $ips = sugar_parse_values($line, "dhcp-range");
            if (isset($ips)) {
                $settings['begin'] = $ips[0];
                $settings['end'] = $ips[1];
                $settings['mask'] = $ips[2];
            }
            $ips = sugar_parse_values($line, "dhcp-option");
            if (isset($ips)) {
                if ($ips[0] == "3") {
                    $settings['router'] = $ips[1];
                }
                if ($ips[0] == "6") {
                    $settings['dns'] = $ips[1];
                }
            }
        }
    }
    return $settings;    
}

function cmp_lease_ip($a, $b) {
    return ip2long($a['ipv4_addr']) - ip2long($b['ipv4_addr']);
}

function explain_leases($leases) {
    foreach ($leases as &$lease) {
        $lease["expiration"] = date("Y-m-d H:i:s", $lease["expiration"]);
        // MAC in DB is upper case and uses "-" instead of ":"
        $mac = $lease["hw_addr"];
        $mac = str_replace(":", "-", strtoupper($mac));
        $lease["hw_addr"] = $mac;
        $tcc = DBAL::db_select_first_value(
            DBAL::db_sql_select("machines", "machineName", "mac='$mac'")
        );
        $lease["tc_client"] = is_null($tcc) ? "" : $tcc;
    }
    // sort array with IP address in asc order
    usort($leases, 'cmp_lease_ip');
    return $leases;
}

function handle_get($target, $params, $body) {
    $topic = array_shift($target);
    if($topic == "nic_usage") {
        return rest_result_ok(array(
            'usage_tx_percent' => calc_usage_tx_percent(),
        ));
    }

    // following topics require IP address
    $nic = current_nic();
    $ipaddr = read_last_line(run_bin("network", "ip"));

    if($topic == "dhcp") {
        $res = array();
        $settings = parse_dhcp_settings();
        $res['dhcp_subnet_begin'] = $settings['begin'];
        $res['dhcp_subnet_end'] = $settings['end'];
        $res['dhcp_subnet_mask'] = $settings['mask'];
        $res['dhcp_router'] = $settings['router'];
        $res['dhcp_dns'] = $settings['dns'];
        // hide local IP in the DHCP DNS list
        if ($ipaddr == $res['dhcp_dns']) {
            $res['dhcp_dns'] = "";
        }
        $lease_file = tc_relpath(constant("TC_DHCP_LEASE_FILE"));
        $res['dhcp_leases'] = parse_dnsmasq_lease($lease_file);
        $res['dhcp_leases'] = explain_leases($res['dhcp_leases']);
        return rest_result_ok($res);
    }

    $mask = read_last_line(run_bin_as_root("network", "mask $nic"));
    $dns = shell_last_line(home_bin("network", "dns $nic"));
    $gateway = shell_last_line(home_bin("network", "gateway $nic"));
    $kmod = shell_last_line(home_bin('network', "show $nic module"));
    $driver = shell_last_line(home_bin('network', "show $nic driver"));
    $speed_mbps = 0;
    $speed_mBps = 0;
    $speed = shell_last_line(home_bin('network', "show $nic speed"));
    if (strlen($speed)) {
        $speed_mbps = intval($speed);
        $speed_mBps = $speed_mbps / 8;
    }

    $res = array();
    $res["nic"] = $nic;
    $res["ip"] = $ipaddr;
    $res["mask"] = $mask;
    $res["kmod"] = rest_value($kmod);
    $res["driver"] = rest_value($driver);
    $res["speed_mbps"] = $speed_mbps;
    $res["speed_mBps"] = $speed_mBps;
    $res["gateway"] = rest_value($gateway);
    $res["dns"] = rest_value($dns);

    return rest_result_ok($res);
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
    "PUT" => array("handle_put", "log_info"),
);
rest_start_loop($handlers);

?>
