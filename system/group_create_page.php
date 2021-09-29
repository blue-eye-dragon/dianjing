<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('create_group');
?>

<div class="container-fluid">
    <div class="row top20">
        <div class="col-xs-6">
            <div class="form-group">
                <div class="control-label">
                    <label for="name-input" i18n="s_name">Name</label> *
                </div>
                <div>
                    <input type="text" class="form-control" id="name-input" maxlength="16">
                </div>
            </div>

            <div class="form-group">
                <div class="control-label">
                    <label for="desc-input" i18n="s_description">Description</label>
                </div>
                <div>
                    <input type="text" class="form-control" id="desc-input" maxlength="40">
                </div>
            </div>
            <small>
                <span i18n="s_asterisk_required">* is required</span>
            </small>
            <hr>
        </div>
    </div>
    <div class="row">
        <?php save_cancel_buttons(); ?>
    </div>
</div>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";
var navi_group_list = function() {
    util_page.navi_page('group_page.php');
}

var do_create_group = function() {
    var data = {
        'name': $.trim($('#name-input').val()),
        'desc': $.trim($('#desc-input').val())
    };
    util_page.rest_post('/tc/rest/group.php', data, navi_group_list, function(rest_error){
        util_page.dialog_message_error(rest_error);
    });
};

$(document).ready(function(){
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    $('#save-button').click(do_create_group);
    $('#cancel-button').click(navi_group_list);
});

</script>

<?php standard_page_end(); ?>