<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    require_once('../libs/inc.conf.php');
    if (file_exists(TC_POST_INSTALLATION_CHECK_PATH)) {
        echo "<script> window.location = '../login.php';</script>";
    }

?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="css/webconsole.css" rel="stylesheet" type="text/css">
        <title>ECM System</title>
    </head>

<body>


<div class="container">
    <div class="jumbotron">
        <h2>Access Denied</h2>
        <h2>拒绝访问</h2>
        <br>
        <p>Please do the post installation steps to complete the whole installation.</p>
        <p>请参考手册，完成后续安装步骤。</p>
    </div>
</div> <!-- /container -->

<script src="../assets/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/bootbox.min.js"></script>
<script src="../system/js/dialog.js"></script>

</body>
</html>
