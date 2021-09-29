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

    standard_page_begin('unreg_clients');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
    <div class="row">
        <div class="col_table">
        <table class="table table-striped table_list" id="client-list">
            <thead>
                <tr>
                    <th><span i18n='s_client_status'>Status</span></th>
                    <th><span i18n='s_ip_address'>IP Address</span></th>
                    <th> CPU </th>
                    <th><span i18n="s_memory_size"> Memory Size </span></th>
                    <th><span i18n='s_disk_size'>Disk</span></th>
                    <th><span i18n='s_heartbeat'>Heartbeat</span>
                        <sub><button type="button" class="btn btn-link btn-xs" id="btn_hb_timeout" style="padding-left: 5px; padding-right: 5px;margin-top: -10px;">
                            <span i18n="s_client_hb_timeout_short" class="status_span"></span>
                        </button></sub>
                    </th>
                    <th><span></span></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
        /* margin-top:0; */
    }
</style>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";
var auto_refresh = true,
    heartbeat_timeout = 120;

var handle_heartbeat_timeout = function(seconds) {
    var title = find_i18n('s_client_hb_timeout');
    title += ' ' + (seconds / 60) + ' ';
    title += find_i18n('s_unit_minute');

    var note = find_i18n('s_client_hb_timeout_title');
    note = '<a title="'+note+'" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 120px;display: inline-block;color:#666;line-height: 30px;">' + note + '</a>';

    util_page.dialog_prompt_required(title, function(val) {
        var settings = {heartbeat_timeout: parseInt(val) * 60};
        util_page.rest_put('/tc/rest/system.php', settings, function() {
            heartbeat_timeout = settings.heartbeat_timeout;
            refresh_clients();
        });
    }).find('input').parent().prepend(note);
};

var bind_table_buttons = function() {
    $('#btn_hb_timeout').click( function() {
        handle_heartbeat_timeout(heartbeat_timeout);
    });
};

var update_client_list = function(clients) {
    if(clients.length>0){
        for (var i in clients) {
            var status = clients[i].client_status;
            var $tr = $('<tr></tr>')
                .append('<td>' + find_i18n('cs_' + status) + '</td>')
                .append('<td>' + clients[i].ip + '</td>')
                .append('<td>' + clients[i].cpu_model + '</td>')
                .append('<td>' + util_page.print_size(clients[i].memory_size, 'MiB') + '</td>')
                .append('<td>' + util_page.print_size(clients[i].disk_size, 'GiB') + '</td>')
                .append('<td>' + clients[i].heartbeat + '</td>');

            if (clients[i].heartbeat_delay > heartbeat_timeout) {
                $tr.addClass('warning');
            }

            $('#client-list tbody').append($tr);
        }
    }else{
        var $noData = '<tr style="text-align: center;"><td colspan="7" style="border-bottom:0"><img src="/tc/images/no_data.png" style="width:100px;margin-top:20px"/><p style="margin-top: 20px;margin-right: 23px;"><span style="color:#bfbfbf">' + find_i18n('s_no_data') +'</span></p></td></tr>'
        $('#client-list tbody').append($noData)
    }
    
    
};

var update_client_counters = function(res) {
    // update online counts
    $('h3 label:nth(0)').text(res.clients.length);
};

var refresh_clients = function() {
    if (!auto_refresh) {
        return;
    }

    // will enable it again if no error
    auto_refresh = false;
    util_page.rest_get('/tc/rest/machine.php?online=1&reg=0', function(res) {
        $('#client-list tbody').empty(); 

        update_client_list(res.clients);
        update_client_counters(res);
        auto_refresh = true;
    });
    

};

$(document).ready(function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    refresh_clients();
    setInterval(refresh_clients, 2000);

    bind_table_buttons();

    // add elements into page header for client counters
    var counter_html = '<label>--</label>';
    counter_html = '<strong class="pull-right">' + counter_html + '</strong>';
    $('h3').append(counter_html);

    util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
        heartbeat_timeout = settings.heartbeat_timeout;
    });
});

</script>

<?php standard_page_end(); ?>