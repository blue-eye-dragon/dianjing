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

    standard_page_begin('sync_peers');
?>

<div class="container-fluid container_table">

    <div class="row row_div">
    <?php
        $bar = array(
            "button_groups" => array(
                array(
                    "buttons" => array(
                        array("i18n" => "s_start_sync","icon" => "glyphicon-play"),
                    ),
                ),
            ),
            "id" => "main",
        );
        $bar2 = array(
            "button_groups" => array(
                array(
                    "buttons" => array(
                        array("i18n" => "s_add", "icon" => "glyphicon-plus"),
                    ),
                ),
                array(
                    "buttons" => array(
                        array("i18n" => "s_edit", "icon" => "glyphicon-list-alt"),
                    ),
                ),
                array(
                    "buttons" => array(
                        array("i18n" => "s_remove", "icon" => "glyphicon-trash"),
                    ),
                ),
            ),
            "id" => "main_1",
        );
        echo html_toolbar1($bar);
        echo html_toolbar1($bar2);
    ?>
    <!-- <span id="check_edit" style="float:right"><img src="/tc/images/batch.png"/></span> -->
    </div>

    <div class="row row_div">
        <div class="col-xs-5" style="margin-right:97px">
            <h4 class="title_span border_right" style="margin-left:7px">
                <span i18n="s_sys_bootimage_list" style="padding-left:15px" class="list_show">Boot Image List</span>
            </h4>
            <hr class="compact">
            <table class="table table-striped peer_table" id="image-list">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th><span i18n='s_path'>Path</span></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div class="col-xs-6">
            <h4 class="title_span border_right" style="margin-left:7px">
                <span i18n="s_peer_list" style="padding-left:15px" class="list_show">Peer List</span>
            </h4>
            <hr class="compact">
            <table class="table table-striped peer_table" id="peer-list">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th><span i18n='s_name'>Name</span></th>
                        <th><span i18n='s_ip_address'>IP Address</span></th>
                        <th><span i18n='s_general_test'></span></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div> <!-- row ends -->

    <div class="row row_div">
        <div class="col-xs-12">
            <h4 class="title_span border_right" style="margin-left:7px">
                <span i18n="s_log_sync" style="padding-left:15px" class="list_show">Sync Log</span>
            </h4>
        </div>
        <div class="col-xs-12" id="log-area">
            <pre style="background:#FBFCFE"></pre>
        </div>
    </div> <!-- row ends -->

</div>
<div style="display: none">
    <div id="create_peer_div" class="dialog_model">
        <h4><span i18n="s_add_peer"></span></h4>
        <div class="form-group">
            <div class="model_div">
                <label class="model_label" for="input_name">
                    <span i18n="s_name">name:</span> :
                </label>
                <input type="text" class="form-control" id="input_name">
            </div>
        </div>
        <div class="form-group">
            <div class="model_div">
                <label class="model_label" for="input_addr">
                    <span i18n="s_ip_address">address</span> :
                </label>
                <input type="text" class="form-control" id="input_addr">
            </div>
            <p class="error_tips"></p>
        </div>
    </div>
</div>
<style>
    .modal-footer{
        display: block;
    }
    .model_label{
        width: 23%;
    }
    .btn_list{
        width:47.8%;
        float: left;
    }
    #check_edit{
        position: relative;
        top: 6px;
        left: 23px;
    }
    /* #image-list tbody tr td{
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
    } */
</style>
<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var checkShow = false;
function load_image_list(data) {
    for (var i in data.files) {
        $('<tr></tr>')
            .append('<td><input type="checkbox" onclick="update_image_list_buttons()"></td>', '<td>'+data.files[i].path+'</td>')
            .appendTo($('#image-list tbody'));
    }

}

function do_load_peer_list(peers) {
    var $tbody = $('#peer-list tbody').detach();
    $tbody.empty();
    for (var i in peers) {
        $('<tr></tr>')
            .attr('data-id', peers[i].id)
            .append('<td><input type="checkbox"></td>')
            .append('<td class="peers_name"></td>')
            .append('<td>'+peers[i].ip_addr+'</td>')
            .append('<td><a href="#">' + find_i18n('s_general_test') + '</a></td>')
            .find('.peers_name').text(peers[i].name).end()
            .find('a').click(function() {
                util_page.rest_post('/tc/rest/peer.php/' + peers[i].id, {}, function(){
                    util_page.dialog_message_i18n('s_general_success');
                });
            }).end()
            .appendTo($tbody);
    }

    $('#peer-list').append($tbody);
}

function update_peer_list_buttons() {
    var count = $('#peer-list tr').slice(1).filter(':has(:checkbox:checked)').length,
        $btn_edit = $('#main_1 button:nth(1)'),
        $btn_remove = $('#main_1 button:nth(2)');

    if (count == 0) {
        $btn_edit.attr('disabled', true);
        $btn_remove.attr('disabled', true);
    } else if (count == 1) {
        $btn_edit.attr('disabled', false);
        $btn_remove.attr('disabled', false);
    } else if (count > 1) {
        $btn_edit.attr('disabled', true);
        $btn_remove.attr('disabled', false);
    }
}

function update_image_list_buttons(){
    var count = $('#image-list tr').slice(1).filter(':has(:checkbox:checked)').length,
        $btn_edit = $('#main button:nth(0)');

    if(count == 1) {
        $btn_edit.attr('disabled', false);
    }else{
        $btn_edit.attr('disabled', true);
    }

}

function load_peer_list() {
    util_page.rest_get('/tc/rest/peer.php', function(result) {
        // clear the checked state for column header
        $('#peer-list tr:nth(0) input').prop('checked', false);

        do_load_peer_list(result);

        $('#peer-list input').click(function(){
            update_peer_list_buttons();
        });

        // $('#image-list input').click(function(){
        //     update_image_list_buttons();
        // });
    });
}

function update_log_area() {
    $('#log-area pre').empty();
    util_page.rest_get('/tc/rest/peer_history.php?event=sync', function(lines){
        $.map(lines, function(line){
            var text = line.timestamp + '::' + line.message;
            $('#log-area pre').prepend('<p>' + text + '</p>');
        });
    });
}

function do_sync() {
    var peers = [];
    $('#peer-list tr').filter(':has(:checkbox:checked)').each(function(){
        peers.push($(this).data('id'));
    });
    if (peers.length == 0) {
        util_page.dialog_message_i18n('e_no_peer_selected', 'Error: No peer selected');
        return;
    }
    if (peers.length > 1) {
        util_page.dialog_message_i18n('e_more_than_one_peer_selected', 'Error: Only support sync to one peer');
        return;
    }
    var files = [];
    $('#image-list td').filter(':has(:checkbox:checked)').next().each(function(){
        files.push($(this).text());
    });
    if (files.length == 0) {
        util_page.dialog_message_i18n('e_no_image_selected', 'Error: No system image selected');
        return;
    }
    if (files.length > 1) {
        util_page.dialog_message_i18n('e_more_than_one_image_selected', 'Error: Only support sync one system image');
        return;
    }
    //util_page.dialog_message_i18n('s_peer_sync_starts');
    update_progress_title();
    for (var idx in peers) {
        for (var idx2 in files) {
            util_page.rest_post(
                '/tc/rest/task_sync.php', 
                {peer: peers[idx], file: files[idx2]}, 
                function() {
                    update_log_area();
                    util_page.dialog_message_i18n('s_backup_done');
                }, 
                function(error){
                    if (error.error != "e_backup_busy") {
                        update_log_area();
                    }
                    update_log_area();
                    util_page.dialog_message_error(error);
                }
            );
        }
    }
    update_log_area();
}

function start_sync() {
    util_page.dialog_confirm(
        find_i18n('c_start_sync_image'), 
        do_sync
    );
}

function delete_peer() {
    var msg = find_i18n('c_delete_selected_peer');
    util_page.dialog_confirm(msg, function() {
        $('#peer-list tr')
            .filter(':has(:checkbox:checked)')
            .each(function() {
                var pid = $(this).data('id');
                if (pid) {
                    util_page.rest_delete('/tc/rest/peer.php/' + pid, {}, load_peer_list);
                }
            });
    });
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
    $form.append($client);

}

function update_peer() {
    var $tr = null;
    $('#peer-list tr').filter(':has(:checkbox:checked)').each(function(){
        $tr = $(this);
    });

    if ($tr) {
        util_page.dialog_prompt_value(
            find_i18n('s_ip_address'),
            $tr.find('td:nth(2)').text(),
            function(new_addr) {
                var $title = $('.modal-title');
                $title.text(find_i18n('s_connect', 'Connect to') + '&ensp;' + new_addr);
                util_page.rest_put(
                    '/tc/rest/peer.php/' + $tr.data('id'),
                    {'ip_addr': new_addr},
                    load_peer_list
                );
            }
        );
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_ip_address')+'</span> :</label>')
        $form.append($client);

    }
}

function create_peer() {
    // bind button handlers
    var dialog_html = '<div class="form-group">';
    dialog_html += '<label for="input_name">' + find_i18n('s_name', 'Server Name') + '</label>';
    dialog_html += '<input type="text" class="form-control" id="input_name">';
    dialog_html += '</div>';
    dialog_html += '<div class="form-group">';
    dialog_html += '<label for="input_addr">' + find_i18n('s_ip_address', 'IP Address') + '</label>';
    dialog_html += '<input type="text" class="form-control" id="input_addr">';
    dialog_html += '</div>';
    dialog_html += '<p></p>';

    

    util_page.dialog_confirm_builder(
        $('#create_peer_div').html(),
        find_i18n('s_save', 'Save'), 
        find_i18n('s_cancel','Cancel'),
        function() {
        var $dialog = $(this),
            $title = $dialog.find('p'),
            $name = $dialog.find('input:nth(0)'),
            $addr = $dialog.find('input:nth(1)');

        if ($.trim($name.val()).length == 0) {
            $title.text(find_i18n('e_empty_name'));
            return false;
        }

        if ($.trim($addr.val()).length == 0) {
            $title.text(find_i18n('e_empty_ip', 'Error: IP address is empty'));
            return false;
        }

        var data = {
            name: $name.val(),
            ip_addr: $addr.val()
        };
        util_page.rest_post('/tc/rest/peer.php', data, load_peer_list);
    });

    return;
}

//Functuons for image sync progress

var global_status = {
    current_image: "NoFile",
    current_server: "NoServer"
};


var update_progress_title = function() {
    util_page.enable_progress_title('/tc/rest/sync.php/progress', function(progress) {
        if (progress.hasOwnProperty('trans_pct')) {
            var percent = progress.trans_pct,
                crt_image = progress.file,
                crt_peer = progress.peer_name;

            var message = percent;
            if (progress.hasOwnProperty('trans_speed')) {
                message += ' ' + progress.trans_speed;
            }
            if (crt_image) {
                message += '<p style="font-size: 0.5em;">' + crt_peer + ', ' + crt_image + '</p>';
            }
            return message;
        }
    });
}

$(document).ready(function(){

    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    // fill tables in this page
    util_page.rest_get('/tc/rest/bootimage2.php', function(result){
        load_image_list(result);
    });

    load_peer_list();
    // bind peer list table selection handler
    update_peer_list_buttons();
    update_image_list_buttons();

    // bind controllers for checkall in image list
    $('#image-list input:first').on('click', function(){
        $('#image-list input').prop('checked', $(this).prop('checked'));
        update_image_list_buttons();
    });

    // bind controllers for checkall in peer list
    $('#peer-list input:first').on('click', function(){
        $('#peer-list input').prop('checked', $(this).prop('checked'));
    });

    $('#main button:nth(0)').click(start_sync);
    $('#main_1 button:nth(0)').click(create_peer);
    $('#main_1 button:nth(1)').click(update_peer);
    $('#main_1 button:nth(2)').click(delete_peer);

    $('#check_edit').click(function(){
        var $control_edit = $("#peer-list thead tr th input").css("display");
        
        if(!checkShow){
            $("#peer-list thead tr th input").css("display","block");
            $("#peer-list tbody input").css("display",'block');
            $("#image-list thead tr th input").css("display","block");
            $("#image-list tbody input").css("display",'block');
        }else{
            $("#peer-list thead tr th input").css("display",'none');
            $("#peer-list tbody input").css("display",'none');
            $("#image-list thead tr th input").css("display","none");
            $("#image-list tbody input").css("display",'none');
        }
        checkShow = !checkShow;
    });

    $('#log-area pre').css({overflow: 'auto'}).height('300px');
    update_log_area();
    update_progress_title();
});

</script>

<?php standard_page_end(); ?>
