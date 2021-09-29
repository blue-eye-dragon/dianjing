<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

//
// most powerful shell function for linux local shell
// other functions are wrappers based on shell_cmd
//

function __shell_cmd($command) {
    $exit_value = -1;
    $last_line = exec($command, $output, $exit_value);
    $result = array(
        'exit_value' => $exit_value,
        'last_line' => $last_line,
        'output' => $output,
    );
    return $result;    
}

function shell_cmd_short($command, $trace=TRUE) {
    $result = __shell_cmd($command);
    if ($trace) {
        log_info("SHELL:: $command :: ".$result['exit_value']);
    }
    return $result;
}

function shell_cmd($command) {
    log_info("SHELL::>> $command");
    $result = __shell_cmd($command);
    $result['ret'] = $result['exit_value'];
    $result['first_line'] = isset($result['output'][0]) ? $result['output'][0] : "";

    $info = $result['exit_value'];
    $info .= "\nSHELL::FST " . $result['first_line'];
    $info .= "\nSHELL::LST " . $result['last_line'];
    foreach ($result['output'] as $line) {
        $info .= "\nSHELL::  " . $line;
    }
    log_info("SHELL::<< $info");
    return $result;
}

function shell_delete($path) {
    return shell_cmd_short("rm -f $path");
}

function shell_delete_dir($path) {
    return shell_cmd_short("rm -rf $path");    
}

function shell_rename($src, $dest) {
    // add double quote for escaping
    $src = '"'.$src.'"';
    $dest = '"'.$dest.'"';
    return shell_cmd_short("mv -f $src $dest");
}

function shell_copy($src, $dest) {
    $src = '"'.$src.'"';
    $dest = '"'.$dest.'"';
    return shell_cmd_short("cp --remove-destination --force $src $dest");
}

function shell_mkdir($dir) {
    $dir = '"'.$dir.'"';
    return shell_cmd_short("mkdir -p $dir");
}

function shell_link_symbol($target, $link) {
    return shell_cmd_short("ln -sf $target $link");
}

function shell_last_line($command) {
    $result = shell_cmd_short($command, FALSE);
    if (is_cmd_fail($result)) {
        return NULL;
    }
    return $result['last_line'];
}

/**
 * Calc folder/file size in MiB as default, from shell command
 * @param  string $path folder or file path
 * @param  array  $options  array includes, unit, dir, apparent
 *                unit      b for byte, k for KiB, m (default) for MiB
 *                dir       boolean, default is false
 *                apparent  boolean, default is false
 * @return int    size number
 */
function shell_file_size($path, $options=array()) {
    $unit="m";
    if (array_key_exists("unit", $options)) {
        $unit = $options["unit"];
    }
    $params = "-$unit";
    if (array_key_exists("dir", $options) && $options["dir"]) {
        $params .= " --summarize";
    }
    if (array_key_exists("apparent", $options) && $options["apparent"]) {
        $params .= " --apparent-size";
    }
    $result = shell_cmd_short("/usr/bin/du $params $path", FALSE);
    if (is_cmd_done($result)) {
        if ($result["last_line"]) {
            $size = preg_split("/[\s]+/", $result["last_line"]);
            return $size[0];
        }
    }
    return 0;
}

function shell_change_owner($dir, $owner="daemon:daemon") {
    $dir = '\"'.$dir.'\"';
    return run_as_root("chown -R $owner $dir");
}

function shell_uuid() {
    $res = shell_cmd_short("cat /proc/sys/kernel/random/uuid");
    if (is_cmd_fail($res)) {
        return "";
    }
    return $res["last_line"];
}

function is_cmd_done($ret) {
    return $ret['exit_value'] == 0;
}

function is_cmd_fail($ret) {
    return !is_cmd_done($ret);
}

function log_info($message) {
    syslog(LOG_INFO, 'TC_L '.$message);
}

function log_warning($message) {
    syslog(LOG_WARNING, 'TC_W '.$message);
}

function log_error($message) {
    syslog(LOG_ERR, 'TC_E '.$message);
}

/**
 * parse rsync 3.1.2 progress line into an array
 * 
 * typical none empty line
 *   440,612,777 100%   50.22MB/s    0:00:08
 *   440,612,777 100%   50.22MB/s    0:00:08 (xfr#19, to-chk=0/34)
 * 
 * @param  [type] $line [description]
 * @return array        {"trans_bytes" => "",
 *                       "trans_pct" => "",
 *                       "trans_speed" => "",
 *                       "trans_time" => ""}
 */
function parse_rsync_progress($line) {
    // trim prefix/suffix empty spaces
    $line = trim($line);
    if (empty($line)) {
        log_warning("Abnormal tc-ar stat, empty line");
        return NULL;
    }

    // split the line with ()
    $parts = preg_split("/(\(|\))/", $line);
    if (empty($parts)) {
        log_warning("Abnormal tc-ar stat, $line");
        return NULL;
    }

    // split the first part with blank spaces
    $words = preg_split("/\s+/", trim($parts[0]));
    // check the words format in splitted result
    if (count($words) == 4 && strpos($words[1], "%")) {
        $progress = array(
            "trans_bytes" => $words[0],
            "trans_pct" => $words[1],
            "trans_speed" => $words[2],
            "trans_time" => $words[3]
        );
        return $progress;
    }

    log_warning("Abnormal tc-ar stat part 0, ".$parts[0]."::".json_encode($words));
    return NULL;
}

/**
 * parse dnsmasq lease database file, only supports IPv4
 *
 * A DHCPv4 lease entry consists of these fields separated by spaces:
 *   - The expiration time (seconds since unix epoch) or duration
       (if dnsmasq is compiled with HAVE_BROKEN_RTC) of the lease.
       0 means infinite.
 *   - The link address, in format XX-YY:YY:YY[...], where XX is the ARP
       hardware type. "XX-" may be omitted for Ethernet.
 *   - The IPv4 address
 *   - The hostname (sent by the client or assigned by dnsmasq) or '*' for none.
 *   - The client identifier (colon-separated hex bytes) or '*' for none.
 *
 * Example:
 * 1518398470 08:00:27:ba:e3:48 192.168.0.125 * 01:08:00:27:ba:e3:48
 */
function parse_dnsmasq_lease($lease_file) {
    $leases = array();
    if (!file_exists($lease_file)) {
        log_warning("Cannot find DHCP lease file $lease_file");
        return $leases;
    }
    $lines = file($lease_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        log_warning("Cannot read DHCP lease file $lease_file");
        return $leases;
    }

    foreach($lines as $line) {
        $line = trim($line);
        $fields = explode(' ', $line);
        $addr_array = explode("-", $fields[1]);
        $hw_addr = end($addr_array);
        $hw_type = $addr_array[0];
        if (count($addr_array) == 1) {
            $hw_type = "";
        }
        $lease = array(
            "expiration" => $fields[0],
            "hw_addr" => $hw_addr,
            "hw_type" => $hw_type,
            "ipv4_addr" => $fields[2],
            "hostname" => $fields[3],
            "client_id" => $fields[4],
        );
        $leases[] = $lease;
    }

    return $leases;
}

?>
