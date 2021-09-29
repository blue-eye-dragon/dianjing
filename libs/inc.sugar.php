<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

/**
 * Return NULL if key does not exist, no more undefined index error
 */
function safe_array_get($key, $array, $default=NULL) {
    if(!is_array($array)){
        return $default;
    }
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }
    return $default;
}

function safe_array_get_mac($key, $array) {
    $value = safe_array_get($key, $array);
    if ($value && sugar_valid_mac($value)) {
        return $value;
    }
    return NULL;
}

function safe_array_get_uuid($key, $array) {
    $value = safe_array_get($key, $array);
    if ($value && sugar_valid_alnum($value, "-")) {
        return $value;
    }
    return NULL;
}

function safe_array_get_alnum($key, $array, $exceptions="") {
    $value = safe_array_get($key, $array);
    if ($value && sugar_valid_alnum($value, $exceptions)) {
        return $value;
    }
    return NULL;
}

function safe_array_get_alnum_key($key, $array, $exceptions="-_.") {
    return safe_array_get_alnum($key, $array, $exceptions);
}

/**
 * Get a trimmed string from an array according to key
 * @param  $key         for exampke, "name"
 * @param  $array       for example, $body
 * @return String or NULL
 */
function safe_array_get_string($key, $array) {
    $value = safe_array_get($key, $array);
    if (is_null($value) || (strlen(trim($value)) == 0)) {
        return NULL;
    }
    return $value;
}

function safe_array_get_int($key, $array) {
    $value = safe_array_get($key, $array);
    if ($value && sugar_valid_int($value)) {
        return $value;
    }
    return NULL;
}

/**
 * Parse line for key-value pair, format is KEY=VALUE,VALUE
 * @param  [type] $line [description]
 * @param  [type] $key  [description]
 * @return [type]       [description]
 */
function sugar_parse_values($line, $key) {
    $pair = explode("=", $line);
    if (count($pair) > 1 && $pair[0] == $key) {
        $value = trim($pair[1]);
        if ($value) {
            return explode(",", $value);
        }
    }
    return NULL;
}

function sugar_tail($pathname, $lines) {
    ob_start();
    passthru("tail -$lines " . escapeshellarg($pathname));
    return trim(ob_get_clean());
}

function sugar_netmask($cidr) {
    $mask = ~((1 << (32 - $cidr)) - 1);
    return long2ip($mask);
}

/**
 * Upper case with separator - or : or none
 *
 * standard format: 00-E0-B4-16-40-8C
 * other format:    00:E0:B4:16:40:8C
 *                  00:E0:B4:16-40:8C
 *                  00:E0:B4:16:40-8C-
 *                  00E0B416408C
 */
function sugar_valid_mac($mac) {
    // no separator format
    if (strlen($mac) == 12) {
        return preg_match('/[A-F0-9]{12}/', $mac) == 1;
    }
    if (strlen($mac) == 17) {
        // separator : format
        $matches = preg_match('/([A-F0-9]{2}[:]){5}[A-F0-9]{2}/', $mac);
        if ($matches == 1) {
            return TRUE;
        }
        // separator - format
        $matches = preg_match('/([A-F0-9]{2}[\-]){5}[A-F0-9]{2}/', $mac);
        return ($matches == 1);
    }
    return FALSE;
}

function sugar_valid_ip4($addr) {
    return filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function sugar_valid_ip4_empty($addr) {
    if ($addr === "") {
        return TRUE;
    }
    return filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

/**
 * Validate the input $value, default range is 1 - 65535
 *
 * @param  string  $value
 * @param  integer $min   default 1
 * @param  integer $max   default 65535
 * @return int value or false(bool) if failed
 */
function sugar_valid_int($value, $min=1, $max=65535) {
    $options = array(
        'options' => array(
            'min_range' => $min,
            'max_range' => $max,
        ),
    );
    return filter_var($value, FILTER_VALIDATE_INT, $options);
}

function sugar_valid_int_array($value) {
    return filter_var_array($value, FILTER_VALIDATE_INT);
}

function sugar_valid_alnum($value, $exceptions="") {
    for ($i = 0; $i < strlen($exceptions); ++$i) {
        $value = str_replace($exceptions[$i], "X", $value);
    }
    return ctype_alnum($value);
}

function sugar_ip4_op_range($addr, $begin, $end=NULL) {
    $mid = ip2long($addr);
    if ($end) {
        $max = ip2long($end);
        if (!ip4_range($max, $mid)) {
            return FALSE;
        }
    }
    $min = ip2long($begin);
    return $mid > $min;
}

function sugar_ip4_op_compare($left, $right) {
    return ip2long($left) - ip2long($right);
}

function sugar_ip4_op_same($left, $right) {
    return sugar_ip4_op_compare($left, $right) == 0;
}

function sugar_ip4_op_mask($addr, $mask) {
    $la = ip2long($addr);
    $lm = ip2long($mask);
    return long2ip($la & $lm);
}

function sugar_ip4_net_size($mask) {
    $table = array(
        "255.255.252.0" => 1024,
        "255.255.254.0" => 512,
        "255.255.255.0" => 256,
        "255.255.255.128" => 128,
        "255.255.255.192" => 64,
        "255.255.255.224" => 32,
        "255.255.255.240" => 16,
        "255.255.255.248" => 8,
        "255.255.255.252" => 4,
        "255.255.255.254" => 2,
        "255.255.255.255" => 1,
    );
    return safe_array_get($mask, $table, 2048);
}

function sugar_int_range($v, $min, $max) {
    return $v >= $min && $v <= $max;
}

function sugar_mb_strlen_range($mbstr, $min, $max) {
    $length = mb_strlen(trim($mbstr));
    return sugar_int_range($length, $min, $max);
}

function sugar_mb_exceptions_strlen_range($mbstr, $min, $max, $exceptions="") {
    $ignore_chinese_mbstr = preg_replace('/([\x80-\xff]*)/i','', $mbstr);
    if(!empty($ignore_chinese_mbstr)) {
        for ($i = 0; $i < strlen($exceptions); ++$i) {
            $ignore_chinese_mbstr = str_replace($exceptions[$i], "X", $ignore_chinese_mbstr);
        }
        if (!ctype_alnum($ignore_chinese_mbstr)) {
            return FALSE;
        }
    }

    $length = mb_strlen(trim($mbstr));
    return sugar_int_range($length, $min, $max);
}

function sugar_decimals_length($v) {
    return strlen(substr(strrchr($v, "."), 1));
}

function sugar_expand_regex($prefix, $suffix, $first, $count, $width) {
    $result = array();
    for ($i = 0; $i < $count; $i++) {
        $var = sprintf("%0$width"."d", $first + $i);
        $result[] = $prefix . $var . $suffix;
    }
    return $result;
}

function sugar_date_now() {
    return date("YmdHis", time());
}

function sugar_valid_path($path) {
    return realpath($path);
}

function http_die_not_found() {
    http_response_code(404);
    die(404);
}

function http_die_unauthorized() {
    http_response_code(401);
    die(401);
}

function http_die_forbidden() {
    http_response_code(403);
    die(403);
}

?>
