<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");
    require("include/inc.bootstrap.php");

    standard_page_begin('all_users');
?>
<div class="container-fluid container_table">
<!-- toolbar beginning -->
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_user_add", "icon" => "glyphicon-plus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_user_remove", "icon" => "glyphicon-minus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_user_edit_ps_del", "icon" => "glyphicon-trash"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_group_edit_ps_upload", "icon" => "glyphicon-upload"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_list", "icon" => "glyphicon-menu-down"),
                ),
            ),

        ),
        "search_control" => array(
            "class" => "pull-right",
            "id" => "search_control",
        ),
        "id" => "crud_control",
    );
    $bar2 = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_user_edit_status", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                     array("i18n" => "s_user_edit_group", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                     array("i18n" => "s_user_edit_passwd", "icon" => "glyphicon-edit"),
                ),
            ),
            //  array(
            //     "buttons" => array(
            //         array("i18n" => "s_user_edit_ps", "icon" => "glyphicon-edit"),
            //     ),
            // ),
            //array(
            //    "buttons" => array(
            //         array("i18n" => "s_user_edit_bind_auto_login", "icon" => "glyphicon-edit"),
            //    ),
            //),

        ),
        "id" => "crud_control_2",
    );
    echo html_toolbar($bar);
    echo html_toolbar1($bar2);
?>
    </div>

    <div class="row row_div">
        <table class="table tc-table table_list" id="table_user_list">
            <thead>
                <tr>
                    <th> <input type="checkbox"> </th>
                    <th> <span i18n="s_name"> Name </span> </th>
                    <th> <span i18n="s_user_display"> Display </span> </th>
                    <th> <span i18n="s_group"> Group </span> </th>
                    <!-- <th> <span i18n="s_ps_size_alloc"> Storage Alloc </span> </th>
                    <th> <span i18n="s_ps_size_image"> Storage Image </span> </th> -->
                    <th> <span i18n="s_user_status"> Status </span> </th>
                    <th> <span i18n="s_user_auto_login_image"> Bind Image ID </span> </th>
                    <th> <span i18n="s_user_auto_login_client"> Bind Client ID </span> </th>
                    
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <p class="pull-right">
        <label class="legend">
            <span i18n="s_label_admin" class="label_admin"></span>
        </label>
    </p>

</div>

<div style="display: none;">
    <div id="dialog_change_password">
        <h4><span i18n="s_password_reset"></span></h4>
        <div class="form-group" style="margin-top:30px">
            <label for="old_password" class=""><span i18n="s_password_admin">Admin Password</span> :</label>
            <input type="password" class="form-control" id="admin_password" placeholder="Admin Password" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="new_password" class=""><span i18n="s_password_user_new">User New password</span> :</label>
            <input type="password" class="form-control" id="user_new_password" placeholder="User New password" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="repeat_password" class=""><span i18n="s_password_repeat">Re-type Password</span> :</label>
            <input type="password" class="form-control" id="repeat_password" placeholder="Re-type Password" autocomplete="off">
        </div>
    </div>
</div>

<div style="display: none;">
    <div id="dialog_bind_auto_login">
        <h4><span i18n="s_autoboot"></span></h4>
        <div class="form-group" style="margin-top:30px">
            <label   style="min-width:150px;text-align: right;"><span id="auto_login_image" i18n="s_select_bind_image">Bind Auto Login Image</span> :</label>
            <select class="form-control" id="selector-image" style="float:left;width:220px;margin-left: 15px;">
            </select>
        </div>
        <div class="form-group">
            <label class="" style="min-width:150px;text-align: right;"><span id="auto_login_client" i18n="s_select_bind_client">Bind Auto Login Client</span> :</label>
            <select class="form-control" id="selector-client" style="float:left;width:220px;margin-left: 15px;">
            </select>
        </div>
    </div>
</div>
<div style="display: none">
    <div id="dialog_register_users" class="dialog_model">
        <h4><span i18n="s_user_add"></span></h4>
        <div style="margin-top:30px;text-align: left;">
            <input type="checkbox" id="batch_checkbox" onchange="batch_cli(this)"> 
            <label i18n="s_batch_add" style="font-size:16px;font-weight: 400;color: #666666;">Batch Creation</label>
        </div>

        <div>
            <form class="form-horizontal">
                <div class="form-group single-only">
                    <div class="control-label">
                        <label for="input_username"><span i18n="s_username">User Name</span> <span class="required_tips">*</span> :</label>
                        <input type="text" class="form-control" id="input_username" maxlength="30">
                    </div>
                </div>
                <div class="form-group single-only">
                    <div class="control-label">
                        <label for="input_display_name"><span i18n="s_user_display">Display Name</span> <span class="required_tips">*</span> :</label>
                        <input type="text" class="form-control" id="input_display_name" maxlength="30">
                    </div>
                </div>
                <!-- batch name -->
                <div class="form-group batch-only" style="display: none">
                    <div class="control-label">
                        <label for="batch_prefix"><span i18n="s_batch_prefix"></span> :</label>
                        <input type="text" class="form-control" id="batch_prefix">
                    </div>
                </div>
                <div class="form-group batch-only" style="display: none">
                    <div class="control-label">
                        <label for="batch_suffix"><span i18n="s_batch_suffix"></span> :</label>
                        <input type="text" class="form-control" id="batch_suffix">
                    </div>
                </div>
                <div class="form-group batch-only" style="display: none">
                    <div class="control-label">
                        <label for="batch_first" ><span i18n="s_batch_first"></span> <span class="required_tips">*</span> :</label>
                        <input type="text" class="form-control" id="batch_first" value="1">
                    </div>
                </div>
                <div class="form-group batch-only" style="display: none">
                    <div class="control-label">
                        <label for="batch_count"><span i18n="s_batch_count">Count</span> <span class="required_tips">*</span> :</label>
                        <input type="text" class="form-control" id="batch_count" value="10">
                    </div>
                </div>
                <div class="form-group batch-only" style="display: none">
                    <div class="control-label">
                        <label for="batch_width"><span i18n="s_batch_width"></span> <span class="required_tips">*</span> :</label>
                        <input type="text" class="form-control" id="batch_width" value="4">
                    </div>
                </div>

                <div class="form-group single-only">
                    <div class="control-label">
                        <label for="password-input" ><span i18n="s_password">Password</span> <span class="required_tips">*</span> :</label>
                        <input type="password" class="form-control" id="password-input" maxlength="32">
                    </div>
                </div>
                <div class="form-group single-only">
                    <div class="control-label">
                        <label for="password2-input"><span i18n="s_password_confirm">Confirm Password</span> <span class="required_tips">*</span> :</label>
                        <input type="password" class="form-control" id="password2-input" maxlength="32">
                    </div>
                </div>
                <div class="form-group">
                    <div class="control-label">
                        <label><span i18n="s_group">User Group</span> <span class="required_tips">*</span> :</label>
                        <select class="form-control" id="group-selector">
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div style="height: 30px;">
                        <input type="checkbox" id="enable-selector" style="width:13px;margin-left: 151px;margin-top: 21px;" onchange="enable_selector_cli()"> 
                        <label i18n="s_user_enable" style="min-width:40px;margin-top: 15px;">Enable</label>
                    </div>
                    <div style="margin-top: 15px;">
                        <small style="color: #999;"><span i18n="s_asterisk_required">* is required</span></small>
                    </div> 
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
    .form-group label{
        min-width: 135px;
        text-align: right;
    }
    #group-selector{
        float: left;
        width: 245px
    }
    .bootbox-body .form-group label{
        text-align:right;
    }
    
</style>

<?php control_pagination(); ?>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var uid = <?php echo $_SESSION["uid"] ?>,
    user_name = '<?php echo $_SESSION["user_name"] ?>';

function page_load() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_table.create($('.tc-table'));
    util_table.enable_search($('#search_control input'), $('#search_control button'));
    util_table.enable_pagination($('#page-control'));
    util_table.enable_sort();
    
    
    // button list: create, delete, batch enabling, batch disabling, change group, change password
    $("#crud_control_2").css("display",'none');
    // $("#table_user_list thead tr th input").css("display",'none');

    util_table.bind_checked(function($trs){
        if ($trs.length == 0) {
            $('#crud_control button:nth(1)').prop('disabled', true);
            $('#crud_control button:nth(2)').prop('disabled', true);
            $('#crud_control button:nth(3)').prop('disabled', true);
            //$('#crud_control button:nth(4)').prop('disabled', true);
            $('#crud_control_2 button:nth(0)').prop('disabled', true);
            $('#crud_control_2 button:nth(1)').prop('disabled', true);
            $('#crud_control_2 button:nth(2)').prop('disabled', true);
            $('#crud_control_2 button:nth(3)').prop('disabled', true);
            // $('#crud_control_2 button:nth(4)').prop('disabled', true);
        } else if ($trs.length == 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            $('#crud_control button:nth(2)').prop('disabled', false);
            $('#crud_control button:nth(3)').prop('disabled', false);
           // $('#crud_control button:nth(4)').prop('disabled', false);
            $('#crud_control_2 button:nth(0)').prop('disabled', false);
            $('#crud_control_2 button:nth(1)').prop('disabled', false);
            $('#crud_control_2 button:nth(2)').prop('disabled', false);
            $('#crud_control_2 button:nth(3)').prop('disabled', false);
            // $('#crud_control_2 button:nth(4)').prop('disabled', false);
        } else if ($trs.length > 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            $('#crud_control button:nth(2)').prop('disabled', false);
            $('#crud_control button:nth(3)').prop('disabled', false);
           // $('#crud_control button:nth(4)').prop('disabled', false);
            $('#crud_control_2 button:nth(0)').prop('disabled', false);
            $('#crud_control_2 button:nth(1)').prop('disabled', false);
            $('#crud_control_2 button:nth(2)').prop('disabled', false);
            $('#crud_control_2 button:nth(3)').prop('disabled', false);
            // $('#crud_control_2 button:nth(4)').prop('disabled', false);
        }
    });

    load_table();

    // bind button handlers
    // create
    // $('#crud_control button:nth(0)').click(function() {
    //     util_page.navi_page('user_create_page.php');
    // });
    $('#crud_control button:nth(0)').click(registration_users);
    $('.batch_checkbox').click(function(){
        var in_batch = $('.batch_checkbox').prop('checked');

        $('.single-only').css('display', in_batch ? 'none' : '');
        $('.batch-only').css('display', in_batch ? '' : 'none');
    });
    init_buttons_status();
    // delete
    $('#crud_control button:nth(1)').click(delete_users);

    //edit show
    $('#crud_control button:nth(4)').click(function(){
       // style="display:none;"
       var $control = $("#crud_control_2").css('display')
       if($control == 'block'){
         $("#crud_control_2").css("display",'none');
       }else{
         $("#crud_control_2").css("display",'block');
       }
    });
    // $('#check_edit').click(function(){
    //     var $control_edit = $("#table_user_list thead tr th input").css("display");
        
    //     if($control_edit == 'block'){
    //         $("#table_user_list thead tr th input").css("display",'none');
    //         $("#table_user_list tbody input").css("display",'none');
    //     }else{
    //         $("#table_user_list thead tr th input").css("display","block");
    //         $("#table_user_list tbody input").css("display",'block');
    //     }
    // });
    // batch enabling
    $('#crud_control_2 button:nth(0)').click(function() {
        var selections = util_page.dialog_select_selections('EYN');
        util_page.dialog_select(find_i18n('s_user_enable'), selections, function(sel) {
            change_user({enable: sel});
        });
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_user_status')+'</span> :</label>')
        $form.append($client);
    });
    // change group
    $('#crud_control_2 button:nth(1)').click(change_group);
    // change password
    $('#crud_control_2 button:nth(2)').click(change_password);
    // frozen storage on
    // $('#crud_control_2 button:nth(3)').click(function() {
    //     var selections = util_page.dialog_select_selections('EYN');
    //     util_page.dialog_select(find_i18n('s_ps_frozen'), selections, function(sel) {
    //         change_user({storage_frozen: sel});
    //     });
    //     var $form = $('.bootbox-form');
    //     var $client = $('<label ><span >'+find_i18n('s_ps_frozen')+'</span> :</label>')
    //     $form.append($client);
    // });
    // bind auto login
    $('#crud_control_2 button:nth(3)').click(change_bind_auto_login);

    // delete PS file
    $('#crud_control button:nth(2)').click(function() {
        var bags = util_table.checked(),
            message = find_i18n('c_delete_user_storage');

        util_page.dialog_confirm(message, function() {
            var urls = $.map(bags, function(bag){
                return '/tc/rest/user.php/' + parseInt(bag.key) + '/storage';
            });
            util_page.rest_delete(urls, {}, function() {
                load_table();
                util_page.dialog_message(find_i18n('s_operation_complete'));
            });
        });
        var $form = $('.modal-body');
        var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
        $form.append($client);
    });

    $('#crud_control button:nth(3)').click(change_global_ps_background_upload);

}
var $in_batch = false;
var $enable_select_check = false;

var batch_cli = function(e){
    // $in_batch = !$in_batch;
    $in_batch = $(e).is(':checked');
    // console.log($(e).attr(':checked'))
    // console.log($(e).prop(':checked'))
    // console.log($(e).is(':checked'))
    // console.log($(e))
    $('.single-only').css('display', $in_batch ? 'none' : '');
    $('.batch-only').css('display', $in_batch ? '' : 'none');
}

var enable_selector_cli = function(){
    $enable_select_check = !$enable_select_check;
}
var change_global_ps_background_upload = function() {
    util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
        util_page.dialog_select(
            find_i18n('s_ps_background_upload'),
            util_page.dialog_select_selections('YN'),
            function(v) {
                util_page.rest_put(
                    '/tc/rest/system.php/settings',
                    {ps_background_upload: v}
                );
            }
        ).find('select').val(settings.ps_background_upload.toString());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_upload_data')+'</span> :</label>')
        $form.append($client);
    });
}

var init_buttons_status = function() {
    $('#crud_control button:nth(1)').prop('disabled', true);
    $('#crud_control button:nth(2)').prop('disabled', true);
    $('#crud_control button:nth(3)').prop('disabled', true);
    $('#crud_control_2 button:nth(0)').prop('disabled', true);
    $('#crud_control_2 button:nth(1)').prop('disabled', true);
    $('#crud_control_2 button:nth(2)').prop('disabled', true);
    $('#crud_control_2 button:nth(3)').prop('disabled', true);
    // $('#crud_control_2 button:nth(4)').prop('disabled', true);
};

var registration_users = function() {
    util_page.rest_get('/tc/rest/group.php', function(groups){
        // will navi to group create page if no group at all
        if (groups.length == 0) {
            util_page.dialog_message(
                find_i18n('s_create_client_group_first', 'Please create a user group first'),
                function() {
                    util_page.navi_page('group_page.php');
                }
            );
            return;
        }else{
            $.map(groups, function(group){
                var $opt = $('<option></option>');
                $opt.val(group.id).text(group.name);
                
                $('#group-selector').append($opt);
            });
            var $dialog = util_page.dialog_confirm_builder(
                $('#dialog_register_users').html(),
                find_i18n('s_save'),
                find_i18n('s_cancel'),
                function() {
                    save_add_users(
                        {
                            'name': $dialog.find('#input_username').val(),
                            'display': $dialog.find('#input_display_name').val(),
                            'group':$dialog.find('#group-selector').val(),
                            'password': $dialog.find('#password-input').val(),
                            'password2': $dialog.find('#password2-input').val(),
                            'enable': $enable_select_check,
                            'is_batch': $in_batch,
                            'batch_prefix': $dialog.find('#batch_prefix').val(),
                            'batch_suffix': $dialog.find('#batch_suffix').val(),
                            'batch_first': $dialog.find('#batch_first').val(),
                            'batch_count': $dialog.find('#batch_count').val(),
                            'batch_width': $dialog.find('#batch_width').val()
                        }
                    );
                }
            );
        }

        
    });
    
}

var save_add_users = function(user_data){
    var $checkbox_batch = $('#batch_checkbox'),
        $text_batch_prefix = $('#batch_prefix'),
        $text_batch_suffix = $('#batch_suffix'),
        $text_batch_first = $('#batch_first'),
        $text_batch_count = $('#batch_count'),
        $text_batch_width = $('#batch_width'),
        $text_username = $('#input_username'),
        $text_display_name = $('#input_display_name');
    // var user_data = {
    //         'name': $text_username.val(),
    //         'display': $text_display_name.val(),
    //         'group': $('#group-selector').val(),
    //         'password': $('#password-input').val(),
    //         'password2': $('#password2-input').val(),
    //         'enable': $enable_select_check,
    //         'is_batch': $in_batch,
    //         'batch_prefix': $text_batch_prefix.val(),
    //         'batch_suffix': $text_batch_suffix.val(),
    //         'batch_first': $text_batch_first.val(),
    //         'batch_count': $text_batch_count.val(),
    //         'batch_width': $text_batch_width.val()
    //     };

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
        
}

var delete_users = function() {
    var bags = util_table.checked(),
        message = find_i18n('c_delete_user', 'Delete selected users?');

    util_page.dialog_confirm(message, function() {
        var urls = $.map(bags, function(bag){
            return '/tc/rest/user.php/' + parseInt(bag.key);
        });

        util_page.rest_delete(urls, null, load_table);
    });
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
    $form.append($client);
};

var change_group = function() {
    util_page.rest_get('/tc/rest/group.php', function(groups) {
        var group_options = [{text: find_i18n('s_select_user_group', 'Select a new user group'), value: ''}];
        $.map(groups, function(group){
            group_options.push({
                text: group.name,
                value: group.id
            });
        });
        util_page.dialog_select(
            find_i18n('s_change_user_group', 'Change user group'),
            group_options,
            function(result) {
                if (result) {
                    change_user({group: result});
                } else {
                    util_page.dialog_message_i18n('e_no_group_selected');
                }
            }
        );
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_group')+'</span> :</label>')
        $form.append($client);
    });
};


var change_bind_auto_login = function() {
    // var auto_login_image = find_i18n('s_select_bind_image'),
    //     auto_login_client = find_i18n('s_select_bind_client');
    // $('#auto_login_image').text(auto_login_image);
    // $('#auto_login_client').text(auto_login_client);
    new Promise(function (resolve, reject) {
        util_page.rest_get('/tc/rest/machine.php', function(machines) {
            $('#selector-client').html('');
            $.map(machines, function (r) {
                $('#selector-client').append($('<option>', {
                    value: r.id,
                    text : r.name
                }));
            });
            resolve("200");
        });
    }).then(function (r) {
        return new Promise(function (resolve, reject) {
            util_page.rest_get('/tc/rest/bootimage2.php', function(res) {
            $('#selector-image').html('')
            $.map(res.images, function (img) {
                $('#selector-image').append($('<option>', {
                    value: img.id,
                    text : img.name
                }));
            });
            resolve("200");
        });
    });
    }).then(function (r) {
            var html = $('#dialog_bind_auto_login').html(),
                btn_text = find_i18n('s_save');
            var $dialog = util_page.dialog_confirm_builder(html, btn_text, find_i18n('s_cancel'),function() {
                var image_id = $dialog.find('#selector-image :selected').val();
                var client_id = $dialog.find('#selector-client :selected').val();
                change_user({bind_image: image_id, bind_client: client_id});
                $("#selector-image").empty();
                $("#selector-client").empty();
            });
    }).catch(function (reason) {
        console.log('Failed: ' + reason);
    });
}


var change_password = function() {
    var admin_pwd_string = find_i18n('s_password_admin'),
        user_new_password_string = find_i18n('s_password_user_new'),
        repeat_password = find_i18n('s_password_repeat');
        $('#admin_password').attr("placeholder", admin_pwd_string);
        $('#user_new_password').attr("placeholder", user_new_password_string);
        
    var html = $('#dialog_change_password').html(),
        btn_text = find_i18n('s_save');
    var $dialog = util_page.dialog_confirm_builder(html, btn_text, find_i18n('s_cancel'),function() {
        var admin_password =  $dialog.find('input:nth(0)').val();
        var user_new_password = $dialog.find('input:nth(1)').val();
        var repeat_user_new_password = $dialog.find('input:nth(2)').val();

        if (user_new_password.length < 10 || user_new_password.length > 32) {
            util_page.dialog_message_i18n('e_bad_password');
            return false;
        }
        if (user_new_password !== repeat_user_new_password) {
            util_page.dialog_message_i18n('e_bad_password_mismatch');
            return false;
        }

        util_page.hash_password(user_name, admin_password).then(function(hash_pwd_admin) {

            var checked_rows = util_table.checked();

            var results = [],
                rest_error;

            checked_rows.reduce(function(prev, cur, index) {
                return prev.then(function(data) {
                    return util_page.hash_password(cur.row[0], user_new_password)
                        .then(function(hash_result) {
                            var options = {
                                cipher_auth: hash_pwd_admin,
                                uid: cur.key,
                                cipher_user: hash_result,
                            };

                            util_page.rest_put('/tc/rest/user.php', options, function() {
                                results.push(data);
                                util_page.dialog_message_i18n('s_update_done_ok');
                            }, function(error) {
                                if (rest_error) {
                                    return;
                                }
                                rest_error = error;
                                util_page.dialog_message_error(rest_error);
                            });
                        });
                })
            }, $().promise()).done(function() {
            });
        });
    });
    $('#repeat_password').attr("placeholder", find_i18n('s_password_repeat'));

};

var change_user = function(user_data) {
    var checked_rows = util_table.checked();
    var urls = $.map(checked_rows, function(bag){
        return '/tc/rest/user.php/' + parseInt(bag.key);
    });

    util_page.rest_put(urls, user_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_table();
    });
};

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

function load_table() {
    util_page.rest_get('/tc/rest/user_list.php', function(data) {
        util_table.load(parse_record(data));
        // $('.tc-table tr').find('td:eq(-1)').hide();
        $(".tc-table tr").filter(function() {
            if($(this).find("td:last-child").text() == "true"){
                $(this).css('background-color', '#fff');
            }
        });
    });
    init_buttons_status();
    // $("#table_user_list thead tr th input").css("display",'none');
    // $("#table_user_list tbody input").css("display",'none');
}

function parse_record(result) {
    var records = [];
    $.map(result, function(r) {
        var checkable = !r.online,
            enabled_text = '';

        if (r.enabled === '1') {
            enabled_text = find_i18n('s_user_enabled');
        } else {
            enabled_text = find_i18n('s_user_disabled');
        }
        if(r.image_name && r.client_name){
            checkable = false;
        }

        var image_size = find_i18n('s_general_disabled'),
            alloc_size = find_i18n('s_general_disabled'),
            frozen = find_i18n('s_general_disabled');

        if (r.storage) {
            image_size = parseInt(r.storage[0].image_size);
            if (image_size > 0) {
                image_size = util_page.print_size(image_size, 'MiB');
            } else {
                image_size = '0 MiB';
            }
            alloc_size = parseInt(r.storage[0].user_size);
            if (alloc_size > 0) {
                alloc_size = util_page.print_size(alloc_size, 'MiB');
            } else {
                alloc_size = '0 MiB';
            }

            frozen = r.storage_frozen ? JSON.parse(r.storage_frozen) : false;
            frozen = frozen ? find_i18n('s_yesno_yes') : find_i18n('s_yesno_no');
        }

        if(r.image_name == null){
            r.image_name = '';
        }
        if(r.client_name == null){
            r.client_name = '';
        }
        records.push({
            key: r.id,
            row: [r.name, r.display, r.group_name, enabled_text, r.image_name, r.client_name],
            checkable: checkable
        });
    });
    return records;
}

$(document).ready(page_load);

</script>

<?php standard_page_end(); ?>