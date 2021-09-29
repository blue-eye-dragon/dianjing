<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require("../libs/inc.sugar.php");

function rest_result($done, $result='') {
    return array("success" => $done, "result" => $result);
}

function rest_result_ok($result='') {
    return rest_result(TRUE, $result);
}

/**
 * REST error result wrapper
 *
 * replacement of deprecated rest_result_error()
 * @param  string $error error name, used by translation
 * @param  string $errno error number, maybe SQL error number
 * @return array         for rest handler in HTTP
 */
function rest_error($error='', $errno=0) {
    return rest_error_extra($error, $errno, "");
}

function rest_result_content($result) {
    return $result['result'];
}

function rest_result_success($result) {
    // boolval from PHP 5.5
    if (!function_exists('boolval')) {
        return (bool) $result['success'];
    }
    return boolval($result['success']);
}

/**
 * rest_error with extra
 * @param  string $error error name, used by translation
 * @param  string $errno error number, maybe SQL error number
 * @param  string $extra extra message, maybe SQL error message
 * @return array         for rest handler in HTTP
 */
function rest_error_extra($error, $errno, $extra="") {
    if(empty($extra)){
        $extra = "";
    }
    if(empty($errno)){
        $errno = 0;
    }
    return rest_result(FALSE,
        array("error" => $error, "errno" => $errno, "extra" => $extra));
}

/**
 * rest_error with fn for errno and extra
 * @param  string $error        error name, used by translation
 * @param  string $fn_errno     fn for error number, like mysql_errno()
 * @param  string $fn_extra     fn for extra message, like mysql_error()
 * @return array         for rest handler in HTTP
 */
function rest_error_from_functions($error, $fn_errno=0, $fn_extra=NULL) {
    return rest_error_extra($error,
        is_callable($fn_errno) ? $fn_errno() : 0,
        is_callable($fn_extra) ? $fn_extra() : ""
    );
}

/**
 * handy function for building rest_error with mysql API
 */
function rest_error_mysql($error="e_database") {
    return rest_error_from_functions($error, "mysql_errno", "mysql_error");
}

function rest_error_pdo_exception($e) {
    return rest_error_extra("e_database", $e->errorInfo[1], $e->getMessage());
}

/**
 * Wrap a REST error based on shell result
 * @param  [Array] $shell_result Array returned by shell_cmd
 * @return [Array]               REST error
 */
function rest_error_shell($shell_result, $error='e_shell') {
    return rest_error_extra(
        $error,
        $shell_result['exit_value'],
        $shell_result
    );
}

function rest_handlers($fn_get, $fn_post=NULL, $fn_del=NULL, $fn_put=NULL) {
    return array (
        'GET' => $fn_get,
        'POST' => $fn_post,
        'DELETE' => $fn_del,
        'PUT' => $fn_put,
    );
}

/**
 * Convert special values format
 *   null => ""
 *
 */
function rest_value($value) {
    if (is_null($value)) {
        return "";
    }
    return $value;
}

function rest_value_int($value) {
    if ($value) {
        return $value;
    }
    return 0;
}

function rest_value_obj($obj) {
    foreach ($obj as $key => $value) {
        if (is_null($value)) {
            $obj[$key] = "";
        }
    }
    return $obj;
}


function rest_log_delete($target, $params, $body) {
    return "DEL ".$_SERVER['REQUEST_URI'].' :: '.json_encode($body).' :: '.json_encode($params);
}

function rest_log_post($target, $params, $body) {
    return "POST ".$_SERVER['REQUEST_URI'].' :: '.json_encode($body).' :: '.json_encode($params);
}

function rest_log_put($target, $params, $body) {
    return "PUT ".$_SERVER['REQUEST_URI'].' :: '.json_encode($body).' :: '.json_encode($params);
}

function rest_log_get($target, $params, $body) {
    // GET should not contain body in request
    return "GET ".$_SERVER['REQUEST_URI'].' :: '.json_encode($params);
}

function rest_format_line($uri, $method, $params, $body, $prefix=NULL) {
    $line = is_null($prefix) ? "$method " : "$method $prefix ";
    $line .= "$uri :: ".json_encode($params);
    // GET should not contain body according to HTTP
    if ($method !== "GET") {
        $line .= ' :: '.json_encode($body);
    }
    return $line;
}

function rest_logging($logger, $line) {
    if (is_callable($logger)) {
        $logger($line);
    }
}

/*
 * $handlers: array of GET, POST, DELETE, and PUT handlers,
 *            including function pointer and logger pointer
 *
 */
function rest_start_loop($handlers) {
    $method = $_SERVER["REQUEST_METHOD"];

    if (!array_key_exists($method, $handlers)) {
        http_die_not_found();
    }

    $handler = $handlers[$method];
    if (!$handler) {
        http_die_not_found();
    }
    $target = array();
    if (array_key_exists("PATH_INFO", $_SERVER)) {
        $target = explode("/", trim($_SERVER["PATH_INFO"], "/"));
    }
    // set UTF-8 as the default encoding for TC
    mb_internal_encoding("UTF-8");

    parse_str($_SERVER["QUERY_STRING"], $params);
    // decode as an array instead of a PHP object
    $body = json_decode(file_get_contents("php://input"), TRUE);
    if (is_null($body)) {
        $body = array();
    }

    $fn_handler = NULL;
    $fn_logger = NULL;
    if(count($handler) > 0) {
        $fn_handler = $handler[0];
    }
    if (count($handler) > 1) {
        $fn_logger = $handler[1];
    }
    if (is_callable($fn_handler)) {
        // unified logging support if fn_logger is available
        if(is_callable($fn_logger)) {
            $uri = $_SERVER["REQUEST_URI"];
            rest_logging($fn_logger, rest_format_line($uri, $method, $params, $body));
        }
        $result = $fn_handler($target, $params, $body);
        header("X-PHP-Response-Code: 200", true, 200);
        // shortcut for direct HTML output
        if (is_null($result)) {
            return;
        }
        header("Content-Type: application/json");
        echo json_encode($result);
        return;
    }

    http_die_not_found();
}

function rest_save_upload2() {
    $file_id = "file-0";
    if (!array_key_exists($file_id, $_FILES)) {
        log_warning("Failed to save uploaded file $file_id");
        return NULL;
    }
    $file_obj = $_FILES[$file_id];
    $file_suffix=explode('.', $file_obj["name"]);
    if (empty($file_obj["name"]) && end($file_suffix) == "tc") {
        log_warning("Failed to save uploaded file $file_id");
        return NULL;
    }
    log_info("Files uploaded, ".json_encode($file_obj));

    $upload_path = tc_relpath(constant("TC_UPLOAD_PATH"));
    $file_path = tc_path_join($upload_path, sugar_date_now());

    // user input validation
    $size = filesize($file_obj["tmp_name"]);
    if (empty($size)) {
        log_warning("HTTP upload file size check failed, ".json_encode($file_obj));
        return NULL;
    }

    if (!move_uploaded_file($file_obj["tmp_name"], $file_path)) {
        log_warning("HTTP upload check failed, ".json_encode($file_obj));
        return NULL;
    }
    log_info("Move $file_id to: ".json_encode($file_path));

    return array(
        "file_path" => $file_path,
        "file_size" => intval($file_obj["size"])
    );
}


/*
 * $handlers: array() of GET, POST for file uploading and downloading
 */
function rest_bind_file($download) {
    $method = $_SERVER['REQUEST_METHOD'];
    parse_str($_SERVER['QUERY_STRING'], $params);

    if ($method === "GET") {
        $filepath = $download(NULL, $params);
        if ($filepath) {
            $filename = basename($filepath);
            header('Content-Type: application/tgz');
            header("Content-Disposition: attachment; filename=$filename");
            header('Pragma: no-cache');

            readfile($filepath);
            return;
        }
    }

    http_die_not_found();
}

?>