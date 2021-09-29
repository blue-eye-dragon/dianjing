<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    require('../libs/libtc.php');
    require("libpage.php");
    require("include/inc.bootstrap.php");

    standard_page_begin('update_firmware');
?>

<div class="container-fluid container_table">
<!-- toolbar beginning -->
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_firmware_upload", "icon" => "glyphicon-upload"),
                ),
            ),
        ),
        "id" => "crud_control",
    );
    echo html_toolbar($bar);
?>
    </div>

    <div class="row row_div">
        <h3 id="fw_version">
            <span i18n="s_fw_version_pending"></span>
            <span> -- </span>
        </h3>
    </div>

    <table>
    </table>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
</style>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

function load_firmware_information() {
    util_page.rest_get('/tc/rest/firmware.php', function(result) {
        var ver = result.filesystem[0];
        $('#fw_version span:nth(1)').text(ver);
    });
}

$(document).ready(function(){
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    load_firmware_information();

    $('#crud_control button:nth(0)').click(function() {
        util_page.dialog_upload('/tc/rest/firmware.php', find_i18n('s_firmware_upload'), function() {
            util_page.dialog_message(find_i18n('s_fw_update_done'), function() {
                location.reload();
            });
        });
    });

});


</script>

<?php standard_page_end(); ?>