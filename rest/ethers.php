<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require('rest.inc.php');
require("../libs/libtc.php");

function search_mac($clients, $id) {
    foreach($clients as $client) {
        if(intval($client["id"]) === $id) {
#            log_info("FOUND compare $id with " . json_encode($client));
            return $client['mac'];
        }
#        log_info("compare $id with " . json_encode($client));
    }
    return NULL;
}

function handle_del($target, $params, $body) {
    $cid = array_shift($target);

    if (is_null($cid)) {
        return rest_error('e_client_not_found');
    }

    $cid = sugar_valid_int($cid);
    if (!$cid) {
        return rest_error('e_client_not_found');
    }

    $mac = DBAL::select_cell("machines", "mac", $cid);
    if (is_null($mac)) {
        log_error("No record for client $cid");
        return rest_error('e_client_not_found');
    }

    if (network_ethers_delete($mac)) {
        return rest_result_ok($cid);
    }
    return rest_error("e_no_permission");
}

function handle_post($target, $params, $body) {
    $id = array_shift($target);
    $id = sugar_valid_int($id);
    if (!$id) {
        return rest_error('e_operation_unsupported');
    }

    $ip = $body['ip'];
    if (!sugar_valid_ip4($ip)) {
        return rest_error('e_bad_ip_address');
    }

    $mac = search_mac(read_clients(), $id);
    $ethers_file_path = tc_relpath(constant("TC_DHCP_ETHERS"));
    if ($mac) {
        if (is_cmd_done(shell_cmd("grep '$ip' $ethers_file_path"))) {
            return rest_error("e_static_ip_existed");
        }
        $mac = str_replace("-", ":", $mac);
        $result = shell_cmd("echo \"$mac  $ip\" >> $ethers_file_path");
        if (is_cmd_fail($result)) {
            return rest_error("e_shell");
        }
        system_restart_dnsmasq();
        return rest_result_ok($id);
    }
    return rest_error("e_operation_unsupported");
}

function search_client_index($clients, $mac) {
    // TC uses upper case in database for hex number
    $mac = strtoupper($mac);
    // TC uses - instead of : in database
    $mac = str_replace(":", "-", $mac);
    foreach($clients as $key => $client) {
        if($client["mac"] === $mac) {
            return $key;
        }
    }
    return NULL;
}

function read_clients() {
    $result = DBAL::do_select("SELECT id, machineName AS name, mac FROM machines");
    if ($result) {
        $clients = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['id'] = $row['id'];
            $row['name'] = $row['name'] ? $row['name'] : "";
            $row['mac'] = $row['mac'] ? $row['mac'] : "";
            $row['ip'] = "";
            $clients[] = $row;
        }
        return $clients;
    }
    return NULL;
}

function handle_get($target, $params, $body) {
    $clients = read_clients();
    $ethers_file_path = tc_relpath(constant("TC_DHCP_ETHERS"));

    if (file_exists($ethers_file_path)) {
        $ethers_file = file_get_contents($ethers_file_path);
        if ($ethers_file) {
            foreach(explode("\n", $ethers_file) as $line) {
                $line = trim($line);
                if ($line && substr($line, 0, 1) !== "#") {
                    $pos = explode(" ", $line, 2);
                    if (count($pos) > 1) {
                        $index = search_client_index($clients, $pos[0]);
                        if ($index !== NULL) {
                            $clients[$index]["ip"] = trim($pos[1]);
                        }
                    }
                }
            }
        }
    }

    return rest_result_ok($clients);
}


auth_login_required();

$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "DELETE" => array("handle_del", "log_info"),
);
rest_start_loop($handlers);

?>
