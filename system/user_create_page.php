<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('create_user');
?>
<div>
    <!-- <input type="checkbox" id="batch_checkbox">  -->
    <label i18n="s_batch_add">Batch Creation</label>
</div>

<div class="container">
    <form class="form-horizontal">
        <div class="form-group single-only">
            <div class="col-xs-2 control-label">
                <label for="input_username" i18n="s_username">User Name</label> *
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="input_username" maxlength="30">
            </div>
        </div>
        <div class="form-group single-only">
            <div class="col-xs-2 control-label">
                <label for="input_display_name" i18n="s_user_display">Display Name</label> *
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="input_display_name" maxlength="30">
            </div>
        </div>
        <!-- batch name -->
        <div class="form-group batch-only" style="display: none">
            <div class="col-xs-2 control-label">
                <label for="batch_prefix" i18n="s_batch_prefix"></label>
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="batch_prefix">
            </div>
        </div>
        <div class="form-group batch-only" style="display: none">
            <div class="col-xs-2 control-label">
                <label for="batch_suffix" i18n="s_batch_suffix"></label>
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="batch_suffix">
            </div>
        </div>
        <div class="form-group batch-only" style="display: none">
            <div class="col-xs-2 control-label">
                <label for="batch_first" i18n="s_batch_first"></label> *
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="batch_first" value="1">
            </div>
        </div>
        <div class="form-group batch-only" style="display: none">
            <div class="col-xs-2 control-label">
                <label for="batch_count" i18n="s_batch_count">Count</label> *
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="batch_count" value="10">
            </div>
        </div>
        <div class="form-group batch-only" style="display: none">
            <div class="col-xs-2 control-label">
                <label for="batch_width" i18n="s_batch_width"></label> *
            </div>
            <div class="col-xs-4">
                <input type="text" class="form-control" id="batch_width" value="4">
            </div>
        </div>

        <div class="form-group single-only">
            <div class="col-xs-2 control-label">
                <label for="password-input" i18n="s_password">Password</label> *
            </div>
            <div class="col-xs-4">
                <input type="password" class="form-control" id="password-input" maxlength="32">
            </div>
        </div>
        <div class="form-group single-only">
            <div class="col-xs-2 control-label">
                <label for="password2-input" i18n="s_password_confirm">Confirm Password</label> *
            </div>
            <div class="col-xs-4">
                <input type="password" class="form-control" id="password2-input" maxlength="32">
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-2 control-label">
                <label><span i18n="s_group">User Group</span></label> *
            </div>
            <div class="col-xs-4">
                <select class="form-control" id="group-selector">
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-offset-2 col-xs-4">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="enable-selector" checked><span i18n="s_user_enable">Enable</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <small><span class="col-xs-offset-2 col-sm-4 col-md-3" i18n="s_asterisk_required">* is required</span></small>
        </div>
        <hr>
        <?php save_cancel_buttons(); ?>
    </form>
</div>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var $checkbox_batch = $('#batch_checkbox'),
    $text_batch_prefix = $('#batch_prefix'),
    $text_batch_suffix = $('#batch_suffix'),
    $text_batch_first = $('#batch_first'),
    $text_batch_count = $('#batch_count'),
    $text_batch_width = $('#batch_width'),
    $text_username = $('#input_username'),
    $text_display_name = $('#input_display_name');

function validate(input_data) {
    if (input_data.is_batch) {
        if (util_page.is_empty(input_data.batch_first)) {
            return find_i18n('e_batch_first');
        }
    } else {
        if (util_page.is_empty(input_data.name)) {
            return find_i18n('e_empty_username', 'Error: Empty user name');
        }
        if (!util_page.is_alphanumeric(input_data.name)) {
            return find_i18n('e_bad_user_name_alphanumeric', 'Error: Invalid user name, only support numbers and letters');
        }
        if (util_page.is_empty(input_data.display)) {
            return find_i18n('e_empty_user_display', 'Error: Empty real display name');
        }

        if (util_page.is_empty(input_data.password)) {
            return find_i18n('e_empty_password', 'Error: Empty password');
        }
        if (input_data.password.length < 10 || input_data.password.length > 32) {
            return find_i18n('e_bad_password');
        }
        for(var i = 0; i < input_data.password.length; i++) {
            var char = input_data.password.charCodeAt(i);
            if (char <= 0 || char > 255 || char === 32) {
                return find_i18n('e_bad_password');
            }
        }
        if (input_data.password != input_data.password2) {
            return find_i18n('e_bad_password_mismatch',
                'Error: Confirm password is not the same as password'
            );
        }
    }
    return;
}


var load_page = function() {

    $('#save-button').click(function() {
        var user_data = {
            'name': $text_username.val(),
            'display': $text_display_name.val(),
            'group': $('#group-selector').val(),
            'password': $('#password-input').val(),
            'password2': $('#password2-input').val(),
            'enable': $('#enable-selector').prop('checked'),
            'is_batch': $checkbox_batch.prop('checked'),
            'batch_prefix': $text_batch_prefix.val(),
            'batch_suffix': $text_batch_suffix.val(),
            'batch_first': $text_batch_first.val(),
            'batch_count': $text_batch_count.val(),
            'batch_width': $text_batch_width.val()
        };

        var error = validate(user_data);
        if (error) {
            util_page.dialog_message(error);
            return;
        }

        util_page.hash_password(user_data.name, user_data.password)
            .then(function(hash_result) {
                user_data.password = hash_result;

                util_page.rest_post('/tc/rest/user.php', user_data, function(){
                    util_page.navi_page('user_page.php');
                });
            });

    });

    $('#cancel-button').click(function(){
        util_page.navi_page('user_page.php');
    });

    $checkbox_batch.change(function(){
        var in_batch = $checkbox_batch.prop('checked');

        $('.single-only').css('display', in_batch ? 'none' : '');
        $('.batch-only').css('display', in_batch ? '' : 'none');
    });
};

$(document).ready(function(){
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_page.rest_get('/tc/rest/group.php', function(groups){
        // will navi to group create page if no group at all
        if (groups.length == 0) {
            util_page.dialog_message(
                find_i18n('s_create_client_group_first', 'Please create a user group first'),
                function() {
                    util_page.navi_page('group_create_page.php');
                }
            );
            return;
        }

        $.map(groups, function(group){
            var $opt = $('<option></option>');
            $opt.val(group.id).text(group.name);
            $('#group-selector').append($opt);
        });

        load_page();
    });

});

</script>

<?php standard_page_end(); ?>