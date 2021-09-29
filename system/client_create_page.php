<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('register_client');
?>

<div class="container-fluid container_table">
    <form class="form-horizontal">
        <div class="row row_div">
            <div class="form-group">
                <div class="col-sm-3 control-label">
                    <label for="name-input" i18n="s_name">Name</label> *
                </div>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name-input" maxlength="30">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3 control-label">
                    <label for="name-input" i18n="s_mac_address">MAC Address</label> *
                </div>
                <div class="col-sm-8">
                    <input type="text" class="form-control tc-mac" id="mac-input" maxlength="17">
                </div>
            </div>
            <div class="form-group">
                <label for="desc-input" class="col-sm-3 control-label" i18n="s_description">Description</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="desc-input" maxlength="40">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3"></label>
                <small><span class="col-sm-8" i18n="s_asterisk_required">* is required</span></small>
            </div>
        </div>

        <hr>

        <?php save_cancel_buttons(); ?>
    </form>
</div>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

function validate(input_data) {
    if (util_page.is_empty(input_data.name)) {
        return find_i18n('e_empty_name', 'Error: Name is empty');
    }
    if (util_page.is_empty(input_data.mac)) {
        return find_i18n('e_empty_mac', 'Error: MAC address is empty');
    }
    return;
}

var page_load = function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    $('#save-button').click(function() {
        var client_info = {
            'name': $('#name-input').val(),
            'mac': $('#mac-input').val(),
            'memo': $('#desc-input').val(),
            'browser': true
        };

        var error = validate(client_info);
        if (error) {
            util_page.dialog_message(error);
            return;
        }

        client_info.mac = client_info.mac.toUpperCase();

        util_page.rest_post('/tc/rest/machine.php', client_info, function() {
            util_page.navi_page('client_page.php');
        });

    });

    $('#cancel-button').click(function() {
        util_page.navi_page('client_page.php');
    });
}

$(document).ready(page_load);

</script>

<?php standard_page_end(); ?>