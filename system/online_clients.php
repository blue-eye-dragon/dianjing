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

    standard_page_begin('online_clients');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
             array(
                "buttons" => array(
                    array("i18n" => "s_client_batch_management", "icon" => "glyphicon-menu-down"),
                ),
            ),
            // array(
            //     "buttons" => array(
            //         array("i18n" => "s_client_wakeup", "icon" => "glyphicon-bell"),
            //     ),
            // ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_filter", "icon" => "glyphicon-filter"),
                ),
            ),
            array(
                //"class" => "pull-right",
                "buttons" => array(
                    array("i18n" => "s_clients_delete_cci", "icon" => "glyphicon-trash"),
                ),
            ),
        ),
        "id" => "main",
    );
    $bar2 = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_batch_reboot", "icon" => "glyphicon-repeat"),
                ),
            ),
            array(
                "buttons" => array(
                     array("i18n" => "s_client_batch_shutdown", "icon" => "glyphicon-off"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_batch_initialize", "icon" => "glyphicon-warning-sign"),
                ),
            ),
            // array(
            //     "buttons" => array(
            //         array("i18n" => "s_client_batch_wakeup", "icon" => "glyphicon-bell"),
            //     ),
            // ),

        ),
        "id" => "main_2",
    );
    echo html_toolbar($bar);
    echo html_toolbar1($bar2);
?>

<!-- row for table -->
        <table class="table table-striped table_list" id="client-list" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="min-width:65px"><span i18n='s_client'>Client</span></th>
                    <th style="min-width:40px"><span i18n='s_client_group'>Client Group</span></th>
                    <th style="min-width:90px"><span i18n='s_client_status'>Status</span></th>
                    <th style="min-width:65px"><span i18n='s_username'>User</span></th>
                    <th style="min-width:55px"><span i18n='s_image_name'>Image</span></th>
                    <th><span i18n='s_ip_address'>IP Address</span></th>
                    <th style="min-width:55px"><span i18n='s_fw_version'>Firmware</span></th>
                    <th style="min-width:55px"><span i18n='s_disk_size'>Disk</span></th>
                    <th><span i18n='s_heartbeat'>Heartbeat</span>
                        <sub style="background-color: #fff; padding-left: 5px; padding-right: 5px;">
                            <button type="button" class="btn btn-link btn-xs" id="btn_hb_timeout" style="margin-top: -10px;">
                                <span i18n="s_client_hb_timeout_short" class="status_span"></span>
                            </button>
                        </sub>
                    </th>
                    <th><span></span></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div id="single-client-control" style="display: none;">
    <div class="btn-group pull-right single-client" role="group">
        <button type="button" class="btn btn-default btn-xs btn_right" title="Reboot client">
            <!-- <span class="glyphicon glyphicon-repeat" style="font-size: 1.2em;"></span> -->
            <span i18n="s_reboot"></span>
        </button>
        <button type="button" class="btn btn-default btn-xs btn_right" title="Shutdown client">
            <!-- <span class="glyphicon glyphicon-off" style="font-size: 1.2em;"></span> -->
            <span i18n="s_shutdown"></span>
        </button>
        <button type="button" class="btn btn-default btn-xs" title="Initialize client" style="border:0">
            <!-- <span class="glyphicon glyphicon-warning-sign" style="font-size: 1.2em;"></span> -->
            <span i18n="s_online_initialize"></span>
        </button>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
    }
</style>
<?php standard_page_mid(); ?>

<script language="javascript">

var auto_refresh = true,
    heartbeat_timeout = 120;

var update_client_list = function(clients) {
    if(clients.length>0){
        for (var i in clients) {
            if (parseInt(sessionStorage.getItem(clients[i].client_group_name)) === 0) {
                continue;
            }

            var status = clients[i].client_status;
            var $tr = $('<tr></tr>')
                .append('<td>' + clients[i].client_name + '</td>')
                .append('<td>' + clients[i].client_group_name + '</td>')
                .append('<td>' + find_i18n('cs_' + status) + '</td>')
                .append('<td>' + clients[i].user + '</td>')
                .append('<td>' + clients[i].image + '</td>')
                .append('<td>' + clients[i].ip + '</td>')
                .append('<td>' + clients[i].firmware + '</td>')
                .append('<td>' + util_page.print_size(clients[i].disk_size, 'GiB') + '</td>')
                .append('<td>' + clients[i].heartbeat + '</td>')
                .append('<td>' + $('#single-client-control').html() + '</td>');

            if (clients[i].heartbeat_delay > heartbeat_timeout) {
                $tr.addClass('warning');
            }

            $('#client-list tbody').append($tr);

            var op_arr = ['initializing_disk', 'updating_firmware', 'updating_firmware_cmd_sent',
                    'updating_firmware_done', 'updating_firmware_failed'
                ],
                op_disabled = $.inArray(status, op_arr) > -1;

            $tr.find('button:nth(0)')
                .data('ip', clients[i].ip)
                .prop('title', find_i18n('s_reboot_client'))
                .prop('disabled', op_disabled)
                .click(function() {
                    var ip_value = $(this).data('ip');
                    util_page.dialog_confirm(find_i18n('c_client_reboot'), function() {
                        util_page.rpc_i18n('reboot-client', {'ip': ip_value});
                    });
                });

            $tr.find('button:nth(1)')
                .data('ip', clients[i].ip)
                .prop('title', find_i18n('s_shutdown_client'))
                .prop('disabled', op_disabled)
                .click(function() {
                    var ip_value = $(this).data('ip');
                    util_page.dialog_confirm(find_i18n('c_client_shutdown'), function() {
                        util_page.rpc_i18n('shutdown-client', {'ip': ip_value});
                    });
                });

            $tr.find('button:nth(2)')
                .data('ip', clients[i].ip)
                .prop('title', find_i18n('s_initialize'))
                .prop('disabled', op_disabled)
                .click(function() {
                    var ip_value = $(this).data('ip');
                    request_auth( function() {
                        util_page.dialog_confirm(find_i18n('c_client_init'), function() {
                            util_page.rpc_i18n('init-client', {ip: ip_value});
                        });
                    });
                });
        }
    }else{
        var $noData = '<tr style="text-align: center;"><td colspan="10" style="border-bottom:0"><img src="/tc/images/no_data.png" style="width:100px;margin-top:20px"/><p style="margin-top: 20px;margin-right: 23px;"><span style="color:#bfbfbf">' + find_i18n('s_no_data') +'</span></p></td></tr>'
        $('#client-list tbody').append($noData);
    }
    
};

var update_client_counters = function(res) {
    // update online counts
    $('h3 label:nth(0)').text(res.clients.length);
    // update total counts
    $('h3 label:nth(1)').text(res.total_count); 
};

var refresh_clients = function() {
    if (!auto_refresh) {
        return;
    }

    // will enable it again if no error
    auto_refresh = false;
    util_page.rest_get('/tc/rest/machine.php?online=1', function(res) {
        $('#client-list tbody').empty(); 

        update_client_list(res.clients);
        update_client_counters(res);
        auto_refresh = true;
    });

};

function request_auth(fn_passed) {
    util_page.dialog_password(find_i18n('s_password_admin'), function(value) {
        var name = 'admin';
        util_page.hash_password(name, value).then(function(cipher) {
            util_page.rest_post('/tc/rest/auth.php',
                {
                    username: name,
                    password: cipher
                },
                fn_passed
            );
        });
    });
    var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_password_admin')+'</span> :</label>')
        $form.append($client);
}

var cci_delete = function(drafts) {
    var selections = [],
        title = find_i18n('s_cci_clear_dialog_title');

    if ($.isEmptyObject(drafts)){
        util_page.dialog_message_i18n('e_no_available_image');
        return;
    }
    selections = $.map(drafts, function(d){
        return {
            text: [d.image_name, d.image_path, d.image_desc].join(', '),
            value: d.image_id
        };
    });

    util_page.dialog_select(title, selections, function(id_selected){
        var img = drafts[id_selected],
            data = {
                image_id: id_selected,
                image_path: img.image_path,
                image_revision: img.image_rev
            };
        util_page.rest_put('/tc/rest/client.php', data, function(){
            util_page.dialog_message_i18n('s_cci_clear_sent');
        });
        
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_cci_clear_dialog_title')+'</span> :</label>')
    $form.append($client);
};

var select_client_group = function(title, fn_done, online_all,title2) {
    if (online_all === undefined) {
        online_all = true;
    }
    var selections = [{text: '', value: 0}];
    if (online_all) {
        selections = [{text: find_i18n('s_client_online_all'), value: 0}];
    } else {
        selections = [{text: find_i18n('s_client_all'), value: 0}];
    }

    util_page.rest_get('/tc/rest/client_group.php', function(cgs) {
        $.map(cgs, function(cg) {
            if (cg.client_members.length > 0) {
                var text = cg.name;
                if (cg.description) {
                    text += ' (' + cg.description + ')';
                }
                selections.push({text: text, value: cg.cgid});
            }
        });
        util_page.dialog_select(title, selections, function(sel) {
            fn_done(sel);
        });
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+title2+'</span> :</label>')
        $form.append($client);
    });
};

var bind_toolbar_main = function() {
    
    $("#main_2").css("display",'none');
    // reboot client group
    $('#main_2 button:nth(0)').click(function() {
        select_client_group(find_i18n('c_online_client_reboot_group'), function(sel) {
            util_page.rpc_i18n('reboot-client', {'client_group': sel});
        },'',find_i18n('s_client'));
        
    });
    // shutdown client group
    $('#main_2 button:nth(1)').click(function() {
        select_client_group(find_i18n('c_online_client_shutdown_group'), function(sel) {
            util_page.rpc_i18n('shutdown-client', {'client_group': sel});
        },'',find_i18n('s_client'));
    });
    // initialize client group
    $('#main_2 button:nth(2)').click(function() {
        request_auth( function() {
            select_client_group(find_i18n('s_password_admin'), function(sel) {
                util_page.rpc_i18n('init-client', {'client_group': sel});
            }, false,find_i18n('s_password_admin'));
        });
    });
    // WOL client group
    $('#main_2 button:nth(3)').click(function() {
        select_client_group(find_i18n('c_online_client_wakeup_group'), function(sel) {
            util_page.rpc_i18n('wake-on-lan-client', {'client_group': sel});
        }, false,find_i18n('s_client'));
    });

    $('#main button:nth(0)').click(function() {
        var $main_control = $("#main_2").css('display')
        if($main_control == 'block'){
            $("#main_2").css("display",'none');
        }else{
            $("#main_2").css("display",'block');
        }
    });
    // wake on lan
    // $('#main button:nth(1)').click(function() {
    //     var s_select_client = find_i18n('s_client', 'Select client');

    //     util_page.rest_get('/tc/rest/machine.php', function(machines) {
    //         var macs = [{text: '', value: ''}];
    //         $.map(machines, function(m){
    //             macs.push({
    //                 text: m.name,
    //                 value: m.mac
    //             });
    //         });
    //         util_page.dialog_select(s_select_client, macs, function(selected){
    //             if (selected) {
    //                 // use 00:00:00:00:00:00 format instead of -
    //                 // sorry for the inconsistency
    //                 selected = selected.split('-').join(':');
    //                 util_page.rpc_i18n('wake-on-lan-client', {'mac': selected});
    //             }
    //         });
    //         var $form = $('.bootbox-form');
    //         var $client = $('<label ><span >'+find_i18n('s_select_client')+'</span> :</label>')
    //         $form.append($client);
            
    //     });
    // });

    // filter by client group name
    $('#main button:nth(1)').click(function() {
        var html = '<h4>' + find_i18n('s_select_client_group') + '</h4><div class="list-group"></div>';
        var $d = util_page.dialog_confirm_builder(
            html,
            btn_text = find_i18n('s_general_ok'),
            btn_text = find_i18n('s_cancel'),
            function() {
            }
        );
        util_page.rest_get('/tc/rest/client_group.php', function(cgs) {
            $.map(cgs, function(cg) {
                var display_key = cg.name,
                    badge = find_i18n('s_general_show');
                if (parseInt(sessionStorage.getItem(display_key)) === 0) {
                    badge = find_i18n('s_general_hide');
                }
                var $btn = $('<button type="button" class="list-group-item"></button');
                $btn.append('<span class="badge">' + badge + '</span>')
                    .append('<span>' + cg.name + '</span>')
                    .appendTo($d.find('.list-group'))
                    .click(function() {
                        if (parseInt(sessionStorage.getItem(display_key)) === 0) {
                            // before click is hidden, after click is shown
                            badge = find_i18n('s_general_show');
                            sessionStorage.setItem(display_key, 1);
                        } else {
                            // before click is shown, after click is hidden
                            badge = find_i18n('s_general_hide');
                            sessionStorage.setItem(display_key, 0);
                        }
                        $btn.find('span:nth(0)').text(badge);
                    });
            });
        });
    });

    // delete cached client image (cci)
    $('#main button:nth(2)').click(function() {
        util_page.rest_get('/tc/rest/bootimage2.php', function(res) {
            var drafts = {};
            $.map(res.images, function(img) {
                drafts[img.id] = {
                    image_id: img.id,
                    image_path: img.path,
                    image_name: img.name,
                    image_desc: img.description,
                    image_rev: img.revision
                };
            });
            cci_delete(drafts);
        });
    });
};

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

var page_load = function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    refresh_clients();
    // setInterval(refresh_clients, 2000);

    bind_toolbar_main();
    bind_table_buttons();

    // add elements into page header for client counters
    var counter_html = '<label>--</label> / <label>--</label>';
    counter_html += '<small> ' + find_i18n('s_client_counter_legend') + ' </small>';
    counter_html = '<strong class="pull-right">' + counter_html + '</strong>';
    $('h3').append(counter_html);

    util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
        heartbeat_timeout = settings.heartbeat_timeout;
    });
};

$(document).ready(page_load);
</script>

<?php standard_page_end(); ?>