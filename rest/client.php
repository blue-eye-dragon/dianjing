<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

error_reporting(E_ALL);

require("rest.inc.php");
require("../libs/libtc.php");

function handle_get($target, $params, $body) {
    if ($target) {
        $cid = array_shift($target);
        if (!is_null($cid)) {
            $cid = sugar_valid_int($cid);
            if (!$cid) {
                return rest_error("e_operation_unsupported");
            }
        }
        $client = client_read($cid);
        if(empty($client)){ // wrong client id
            return rest_error('e_client_not_found');
        }
        if (empty($client['client_token'])) {
            update_client_token($cid);
            $client = client_read($cid);
        }

        $ciid = safe_array_get_int("ciid", $params);
        if ($ciid) {
            $profile = array();
            foreach ($client["client_profile"] as $item) {
                log_info("profile item: ".json_encode($item));
                if (intval($item["image_id"]) == $ciid) {
                    array_push($profile, $item);
                }
            }
            return rest_result_ok($profile);
        }
        return rest_result_ok($client);
    }

    // support client profile
    // client.php?uuid=<image_uuid>&mac=<client_mac>
    $image_uuid = safe_array_get_uuid("uuid", $params);
    // log_info("mac $client_mac, $image_uuid");

    if (!is_null($image_uuid)) {
        $client_mac = safe_array_get_mac("mac", $params);
        $client_id = safe_array_get_int("client_id", $params);
        if (!is_null($client_mac)) {
            $client_id = client_mac2id($client_mac);
        }

        if (is_null($client_id)) {
            return rest_error('e_client_not_found');
        }
        $ciid = client_image_uuid2id($image_uuid);
        if (is_null($ciid)) {
            return rest_error("e_image_not_found");
        }

        $fields = array("id AS client_profile_id", "image_id", "profile_type", "profile_name", "profile_value", "profile_version", "create_time");
        $where = array("machine_id" => $client_id, "image_id" => $ciid);

        $profile = DBAL::select_rows("client_profile", $fields, $where);
        if (is_null($profile)) {
            $profile = array();
        }
        return rest_result_ok($profile);
    }

    return rest_error('e_operation_unsupported');
}

/**
 * /client.php      query if mac is registered, support lower case in
 *                  both of 00-00-00-00-00-00 and 00:00:00:00:00:00
 * /client.php/:id  command for this client, details in body
 * @param  [type]
 * @param  [type]
 * @param  [type]
 * @return [type]
 */
function handle_post($target, $params, $body) {
    if ($target) {
        $client_id = array_shift($target);
        log_info("client.php handle post $client_id");
        $cid = $client_id;
        $cid = sugar_valid_int($cid);
        if (!$cid) {
            return rest_error("e_operation_unsupported");
        }
        $client = client_read($client_id);
        if (empty($client['client_token'])) {
            update_client_token($client_id);
        }
        $boot = safe_array_get('boot', $body);
        if ($boot) {
            $client_token = safe_array_get('client_token', $boot);
            $access_token = safe_array_get('access_token', $boot);
            if (empty($access_token)) {
                $auth = current_session_auth();
                $access_token = $auth["access_token"];
                log_info("load session access token, $access_token");
            }
            $image_id = safe_array_get('image_id', $boot);
            $error = remote_boot_client($client_token, $access_token, $image_id);
            return $error ? rest_error($error) : rest_result_ok();
        }

        $status = safe_array_get('client_status', $body);
        if ($status) {
            $res = update_client_online_status($client_id, $status);
            if(empty($res)){
                return rest_error("e_update_client_online_status");
            }
        }

        $profile_type = safe_array_get_alnum_key("profile_type", $body);
        $profile_name = safe_array_get_alnum_key("profile_name", $body);
        $profile_value = safe_array_get_string("profile_value", $body);
        $ciid = safe_array_get_int("ciid", $body);

        if (!is_null($profile_name) && !is_null($profile_type) && !is_null($profile_value)) {

            $duplicate_check_where = array(
                "machine_id" => $client_id,
                "image_id" => $ciid,
                "profile_name" => $profile_name
            );
            $duplicate_check = DBAL::select_row("client_profile", array("id"), $duplicate_check_where);
            if (!is_null($duplicate_check)) {
                return rest_error_mysql("e_duplicate_value");
            }

            $fields = array(
                "machine_id" => $client_id,
                "image_id" => $ciid,
                "profile_name" => $profile_name,
                "profile_type" => $profile_type,
                "profile_value" => $profile_value,
                "profile_version" => 1,
            );

            $res = DBAL::db_insert_table("client_profile", $fields);
            if (is_null($res) || !$res) {
                return rest_error_mysql("e_update_client_profile");
            }
        }
        return rest_result_ok();
    }

    // client use this 'mac' to query the register status
    $mac = safe_array_get('mac', $body);
    if ($mac) {
        $mac = str_replace(':', '-', $mac);
        $mac = strtoupper($mac);

        // update hardware information
        $cpu_model = db_safe_array_get('cpu_model', $body);
        $disk_size = db_safe_array_get('disk_size', $body);
        $memory_size = db_safe_array_get('memory_size', $body);

        $client_id = DBAL::db_select_first_value_old("SELECT id FROM machines WHERE mac='$mac'");
        if (is_null($client_id)) {
            // new client
            if (!is_null($cpu_model)) {
                update_client_online_hardware($mac, $cpu_model, $memory_size, $disk_size);
            }
            return rest_error('e_new_client');
        }

        // update token
        $client = client_read($client_id);
        if (empty($client['client_token'])) {
            update_client_token($client_id);
        }
        $client['qrcode'] = "/tc/rest/file.php/qrcode/$client_id";

        if (!is_null($cpu_model)) {
            $changes = array(
                'cpu_model' => $cpu_model,
                'memory_size' => $memory_size,
                'disk_size' => $disk_size,
            );
            try {
                DBAL::update_table('machines', $changes, array("id" => $client_id));
                DBAL::update_table('userstatus', $changes, array("mac" => $mac));
                $firmware_version = db_safe_array_get('firmware_version', $body);
                $changes = array(
                    'firmware' => $firmware_version,
                );
                DBAL::update_table('machines', $changes, array("id" => $client_id));
            } catch (PDOException $e) {
                return rest_error_extra("", $e->errorInfo[1], $e->getMessage());
            }

        }
        return rest_result_ok($client);
    }
    return rest_result_ok();
}


function handle_put($target, $params, $body) {

    // support client profile
    // client.php?uuid=<image_uuid>&mac=<client_mac>
    $client_mac = safe_array_get_mac("mac", $params);
    $image_uuid = safe_array_get_uuid("uuid", $params);

    if (!is_null($client_mac) && !is_null($image_uuid)) {
        $client_id = client_mac2id($client_mac);
        if (is_null($client_id)) {
            return rest_error('e_client_not_found');
        }
        $ciid = client_image_uuid2id($image_uuid);
        if (is_null($ciid)) {
            return rest_error("e_image_not_found");
        }

        $profile_type = safe_array_get_alnum_key("profile_type", $body);
        $profile_name = safe_array_get_alnum_key("profile_name", $body);
        $profile_value = safe_array_get_string("profile_value", $body);
        $profile_version = safe_array_get("profile_version", $body);
        if (is_null($profile_name) || is_null($profile_type)) {
            return rest_error("e_operation_unsupported");
        }

        $profile_value = is_null($profile_value) ? "" : $profile_value;
        $changes = array(
            "profile_value" => $profile_value,
        );
        if (!is_null($profile_version)) {
            $profile_version = sugar_valid_int($profile_version);
            if (!$profile_version) {
                return rest_error("e_operation_unsupported");
            }
            $changes["profile_version"] = $profile_version;
        }
        $where = array(
            "machine_id" => $client_id,
            "image_id" => $ciid,
            "profile_name" => $profile_name,
            "profile_type" => $profile_type
        );
        $done = DBAL::update_table("client_profile", $changes, $where);
        return $done ? rest_result_ok() : rest_error_mysql();
    }

    $image_id = safe_array_get('image_id', $body);
    if (empty($image_id) || empty(sugar_valid_int($image_id))) {
        return rest_error("e_operation_unsupported");
    }
    $filepath = DBAL::db_select_first_value_old("
        SELECT newPath FROM imageupdatehistory
        WHERE revision=1 AND imageId=$image_id"
    );
    if(empty($filepath)){
        return rest_error("e_image_not_found");
    }
    $filename = basename($filepath);

    $result = send_cc_command_registered("--remove-image --image-org-name $filename --image-id $image_id");
    if (is_cmd_fail($result)) {
        return rest_error_shell($result);
    }
    return rest_result_ok($result['exit_value']);
}


function handle_delete($target, $params, $body) {
    $cid = array_shift($target);
    if (is_null($cid)) {
        return rest_error("e_operation_unsupported");
    }

    $profile_id = safe_array_get_alnum_key("client_profile_id", $body);
    if (!is_null($profile_id)) {
        $res = DBAL::delete_row("client_profile", $profile_id);
        if (is_null($res)) {
            return rest_error("e_delete_client_profile");
        }
        return rest_result_ok($res);
    }

    return rest_error("e_operation_unsupported");
}


$handlers = array(
    "GET" => array("handle_get", "log_info"),
    "POST" => array("handle_post", "log_info"),
    "PUT" => array("handle_put", "log_info"),
    "DELETE" => array("handle_delete", "log_info"),
);
rest_start_loop($handlers);

?>
