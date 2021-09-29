<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    require_once('libs/inc.conf.php');
    if (!file_exists(TC_POST_INSTALLATION_CHECK_PATH)) {
        echo "<script> window.location = './system/setup_denied.php';</script>";
        exit();
    }

    require('libs/libtc.php');
    # use GET parameter first
    if (array_key_exists("lang", $_GET)) {
        $lang = $_GET["lang"];
        if ($lang === "en") {
            $GLOBALS["_TC2_"]["lang"] = "en";
        } else if ($lang === "zh_CN") {
            $GLOBALS["_TC2_"]["lang"] = "zh";
        }
    }

    $lang_id = global_config_lang_id();
    $lang_html = "<script src='system/langs/lang.$lang_id.js'></script>";
    //Deny access request from browser IE5/6/7/8/9/10
    if(!(isset($_SERVER['HTTP_USER_AGENT'])) || (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)){
        echo '<script language="javascript">
            window.location = "./system/zbroswer_denied.html"
            </script>';
        exit();
    }
?>


<!doctype html>
<html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="system/css/webconsole.css" rel="stylesheet" type="text/css">
        <title>Lenovo ECM Management System</title>
    </head>

<body>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/tc/tcs.ca.certificate.crt" download="ca.crt" id="ca_crt"></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="login.php?lang=en">English</a></li>
                <li><a href="login.php?lang=zh_CN">中文</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <!-- <h1 class="align-center top50">Lenovo ECM</h1> -->
        <h1 class="align-center top50" i18n="s_title">LESport管理平台</h1>
    </div>
    <div class="row">
        <form class="form-signin login-form" method="post" action="rest/auth.php" autocomplete="off">
            <label for="inputUsername" i18n="s_username">User Name</label>
            <input type="username" name="inputUsername" class="form-control" style="background-color: #fff" required autofocus>
            <label for="inputPassword" i18n="s_password">Password</label>
            <input type="password" name="inputPassword" class="form-control" style="background-color: #fff" required>
            <button class="btn btn-lg btn-primary btn-block " type="submit" id="log_btn">
                <span i18n="s_login">Login</span>
            </button>
        </form>
    </div>
</div> <!-- /container -->

<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/bootbox.min.js"></script>
<script src="system/js/page.js"></script>
<script src="system/js/iefix.js"></script>
<?php echo $lang_html; ?>

<script type="text/javascript">

$(document).ready(function(){

    util_page.enable_locale();

    $('#ca_crt').text(find_i18n('s_ssl_ca_cert'));
    $('input[name="inputUsername"]')
        .prop('title', find_i18n('s_input_login_name', 'Input login user name'))
        .prop('error', find_i18n('e_empty_username', 'Error: User name is empty'));
    $('input[name="inputPassword"]')
        .prop('title', find_i18n('s_input_login_password', 'Input login password'))
        .prop('error', find_i18n('e_empty_password', 'Error: Password is empty'));

    $('form').submit(function(event) {
        event.preventDefault();

        var username = $('input[name="inputUsername"]').val()
            password = $('input[name="inputPassword"]').val();

        util_page.hash_password(username, password)
            .then(function(hash_result) {
                var data = {
                    username: username,
                    password: hash_result,
                    login: true
                };
                util_page.rest_post("/tc/rest/auth.php", data, function(result) {
                    util_page.navi_page(result['url']);
                });

            });
    });
});

</script>
</body>
</html>
