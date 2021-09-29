<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);

    require('../libs/libtc.php');
    require("libpage.php");

    $link = 'dashboard';

    browser_compatibility_check();
    page_permission_check($link);
    page_begin();
    page_head($link);
    body_begin();
    container_begin();

    sidebar_item_home();
    sidebar_left($link);
    content_right_begin($link);
?>

<div class="container-fluid">
<div class="service_status_div status_div">
    <div class="title_div"><span i18n="s_server_status">Server Side</span></div>
    <div class="content_div">
        <div class="status_left">
            <div class="status_left_top">
                <img src="/tc/images/service_status_1.png"/>
            </div>
            <div class="status_left_bottom">
                <p class="second_title"><span i18n="s_server_services">System Status</span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="server_services_action"></p>
            </div>
        </div>
        <div class="status_right">
            <div class="status_right_top ">
                <p class="second_title"><span i18n="s_server_service_dnsmasq">Network Service</span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="switch_dhcp_server"></p>
            </div>
            <div class="status_right_bottom">
                <p>
                    <span i18n="s_network_address">Server Address</span>:
                    <span id="server-ip"></span>
                </p>
                <p>
                    <span i18n="s_network_gateway">Server Gateway</span>:
                    <span id="server_gateway"></span>
                </p>
                <p>
                    <span i18n="s_network_dns">Server DNS</span>:
                    <span id="server_dns"></span>
                </p>

            </div>
        </div>
    </div>
</div>
<div class="client_status_div status_div">
    <div class="title_div"><span i18n="s_client_status">Client Side</span></div>
    <div class="content_div">
        <div class="status_left">
            <div class="status_left_top">
                <img src="/tc/images/client_status_1.png"/>
            </div>
            <div class="status_left_bottom">
                <p class="second_title"><span i18n="s_dashboard_client_registration"></span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="client_open_registration"></p>
            </div>
        </div>
        <div class="status_right">
            <div class="status_right_top status_right_top_bottom">
                <p class="second_title"><span i18n="s_dashboard_client_naming"></span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="client_naming"></p>
            </div>
            <div class="status_left_bottom status_p">
                <p class="second_title"><span i18n="s_dashboard_client_naming_rule"></span></p>
                <p class="border_p"></p>
                <p i18n="s_open"><span i18n="s_none_2" style="color:#333;font-weight:600" id="naming_rule">none</span></p>

            </div>
            
        </div>
    </div>
</div>
<div class="globel_div status_div">
    <div class="title_div"><span i18n="s_permission_global">Global Permissions</span></div>
    <div class="content_div">
        <div class="status_left">
            <div class="status_left_top">
                <img src="/tc/images/global_1.png"/>
            </div>
            <div class="status_left_bottom status_p">
                <p class="second_title"><span i18n="s_autoboot_image">Auto Login Image</span></p>
                <p class="border_p"></p>
                <p i18n="s_open"><span i18n="s_none_2" style="color:#333;font-weight:600" id="autoboot-image">none</p>
                
            </div>
            
        </div>
        <div class="status_right">
            <!-- <div class="status_right_top status_right_top_bottom">
                <p class="second_title"><span i18n="s_ps_background_upload_switch"></span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="ps_background_upload"></p>
            </div> -->
            <div class="status_left_bottom status_p">
                <p class="second_title second_title_one"><span i18n="s_auto_login_delay">Auto Login Delay</span></p>
                <p class="border_p"></p>
                <p i18n="s_open" id="auto_login_delay" style="color:#333;font-weight:600"><span></span>&nbsp;<span i18n="s_second"></span></p>

            </div>
        </div>
    </div>
</div>
<div class="Util_div" id="fs-data">
    <div class="status_left Util_div_left">
        <div class="title_div">
            <span i18n="s_network_nic_usage_percent">TCI System NIC Usage Percent</span>
        </div>
        <div id="gauge" >
            <div class="usage_div usage_canvas_div">
                <canvas id="network-chart" width="180" height="180"></canvas>
            </div>
            <div class="usage_div">
                <p class="usage_second_title"><span i18n="s_network_nic_usage_percent">TCI System NIC Usage Percent</span></p>
                <p class="usage_second_value"><span id="network_nic_usage_percent"></span></p>
            </div>
        </div>
        <div class="status_right_bottom float_left usage_mess" id="nic_usage_data">
            <p style="margin-bottom: 20px;">
                <span style="width: 28px;height: 12px;background: #3E8AFF;border-radius: 1px;display: inline-block;margin-right: 15px;margin-left: 12px;"></span>
                <span i18n="s_remain" class="usage_mess_title">NIC Information</span>
                <span style="width: 28px;height: 12px;background: #FF566C;border-radius: 1px;display: inline-block;margin-right: 15px;"></span>
                <span i18n="s_used" class="usage_mess_title">NIC Information</span>
            </P>
            <p>
                <span i18n="s_network_nic" class="usage_mess_title">NIC Information</span>
                <span id="server_nic"></span>
            </p>
            <p>
                <span i18n="s_network_nic_kmodule" class="usage_mess_title">Kernal Module</span>
                <span id="server_nic_kmodule"></span>
            </p>
            <p>
                <span i18n="s_network_nic_driver" class="usage_mess_title">Driver Name</span>
                <span id="server_nic_driver"></span>
            </p>
        </div>
    </div>
    <div class="status_right">
        <div class="title_div">
            <span i18n="s_fs_usage_percent">TCI File System Usage Percent</span>
        </div>
        <div id="gauge_fs" >
            <div class="usage_div usage_canvas_div_2">
                <canvas id="fileSystem-chart" width="180" height="180"></canvas>
            </div>
            <div class="usage_div">
                <p class="usage_second_title"><span i18n="s_fs_usage_percent">TCI File System Usage Percent</span></p>
                <p class="usage_second_value"><span id="fs_usage_percent"></span></p>
            </div>
            
        </div>
        <div class="status_right_bottom float_left usage_mess" id="fs_usage_data">
            <p style="margin-bottom: 20px;">
                <span style="width: 28px;height: 12px;background: #3E8AFF;border-radius: 1px;display: inline-block;margin-right: 15px;margin-left: 12px;"></span>
                <span i18n="s_remain" class="usage_mess_title">NIC Information</span>
                <span style="width: 28px;height: 12px;background: #FF566C;border-radius: 1px;display: inline-block;margin-right: 15px;"></span>
                <span i18n="s_used"  class="usage_mess_title">NIC Information</span>
            </P>
            <p>
                <span i18n="s_fs_total_size" class="usage_mess_title"></span>
                <span id="fs_total_size">--</span> GiB
            </p>
            <p>
                <span i18n="s_fs_used_size" class="usage_mess_title"></span>
                <span id="fs_used_size">--</span> GiB
            </p>
            <p>
                <span i18n="s_fs_free_size" class="usage_mess_title"></span>
                <span id="fs_free_size">--</span> GiB
            </p>
        </div>
    </div>
</div>
<div class="list_active">
    <div class="image_list">
        <div class="title_div"><span i18n="s_sys_bootimage_list">Boot Image List</span></div>
        <div id="image_list" style="height: 200px;overflow-y: auto;">
            <table class="bootimage_list_table">
                <tbody>
                    <!-- <tr>
                        <td><span class="client_value">client004</span></td>
                        <td><span class="client_value">1</span></td>
                        <td><span class="client_value">2021-02-24 15:12:58</span></td>
                    </tr>
                    <tr>
                        <td><span class="client_value">client004</span></td>
                        <td><span class="client_value">1</span></td>
                        <td><span class="client_value">2021-02-24 15:12:58</span></td>
                    </tr>
                    <tr>
                        <td><span class="client_value">client004</span></td>
                        <td><span class="client_value">1</span></td>
                        <td><span class="client_value">2021-02-24 15:12:58</span></td>
                    </tr> -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="client_active">
        <div class="title_div">
            <span i18n="s_client_terminal">Online Terminal</span>
            <span id="next_title" class="num_title" style="float: right;cursor: pointer;">></span>
            <span id="num_title" class="num_title" style="float: right;cursor: pointer;"></span>
            <span id="pre_title" class="num_title" style="float: right;cursor: pointer;"><</span>

        </div>
        <div id="client_show">
            <div class="client_show client_terminal_show">
                <p class="client_name client_value" id="terminal_name">---</p>
                <p class="client_status client_value client_status_value">
                    <img src="/tc/images/status.png" style="margin-right:4px"/>
                    <span i18n="s_open_close" class="status_open_span" id="client_status"></span>
                </p>
            </div>
            <div class="client_show client_terminal_show">
                <p class="client_name client_value" id="terminal_name">---</p>
                <p class="client_status client_value client_status_value">
                    <img src="/tc/images/status.png" style="margin-right:4px"/>
                    <span i18n="s_open_close" class="status_open_span" id="client_status"></span>
            </div>
            <div class="client_show client_terminal_show">
                <p class="client_name client_value" id="terminal_name">---</p>
                <p class="client_status client_value client_status_value">
                    <img src="/tc/images/status.png" style="margin-right:4px"/>
                    <span i18n="s_open_close" class="status_open_span" id="client_status"></span>
                </p>
            </div>
        </div>
        <div id="client_group_page" style="display:none">
            <div class="client_show client_terminal_show" style="display:none">
                <p class="client_name client_value" id="terminal_name">---</p>
                <p class="client_status client_value client_status_value">
                    <img src="/tc/images/status.png" style="margin-right:4px"/>
                    <span i18n="s_open_close" class="status_open_span" id="client_status"></span>
                </p>
            </div>
        </div>
    </div>
</div>
<style>
    .client_status_value span{
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 400;
        color: #000;
    }
    .open_img_span{
        color: #333;
        font-weight: 600;
    }
    .modal-foote{
        display: block;
    }
</style>

<!-- ======== TOP ROW END  ======== -->

<hr>


<div class="row">



<?php
    content_right_end();
//    rows_end();
    container_end();

    page_script_files();
?>

<script language="javascript">

"use strict";

var global_refresh = true,
    heartbeat_timeout = 120;
var $text_autoboot = $('#autoboot-image');
var pageNum = 1;
var pageTotalNum = 1;

function change_server_ip() {
    var do_change_ip = function() {
        var settings = {
            addr: $(this).find('input:nth(0)').val(),
            mask: $(this).find('input:nth(1)').val()
        };
        util_page.rest_put(
            '/tc/rest/network.php',
            settings,
            function() {
                $('#server-ip')
                    .data('sip', settings.addr)
                    .data('smask', settings.mask)
                    .text(settings.addr);
            }
        );
    };

    var html = $('#dialog_server_address').html(),
        btn_text = find_i18n('s_save'),
        cancel_btn_text = find_i18n('s_cancel');
        old_ip = $('#server-ip').data('sip'),
        old_mask = $('#server-ip').data('smask');

    util_page.dialog_confirm_builder(html, btn_text,cancel_btn_text, do_change_ip)
        .find('input:nth(0)').val(old_ip).end()
        .find('input:nth(1)').val(old_mask);
}

function load_client_list(clients) {
    // let clients = [
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    //     {client_name:'test',status:'test',user:'test',image:'test',ip:'test',heartbeat:'test'},
    // ]
    pageTotalNum = Math.ceil((clients.length)/3);
    $('#num_title').text(' 0 / '+ Math.ceil((clients.length)/3 +' '));
    $('#num_title').click(function() {
        $(".client_show").css('display','none');
        if(pageNum < pageTotalNum){
            pageNum ++;
            $('#num_title').text(' '+pageNum+' / '+ Math.ceil((clients.length)/3+' '));
            $(".client_show:nth("+(pageNum*3-2)+")").css('display','block');
            $(".client_show:nth("+(pageNum*3-1)+")").css('display','block');
            $(".client_show:nth("+(pageNum*3-3)+")").css('display','block');
        }else{
            pageNum = 1;
            $('#num_title').text(' 1 / '+ Math.ceil((clients.length)/3+' '));
            $(".client_show:nth(0)").css('display','block');
            $(".client_show:nth(1)").css('display','block');
            $(".client_show:nth(2)").css('display','block');
        }
    });
    for (var i in clients) {
        $('#num_title').text('1 / '+ Math.ceil((clients.length)/3));
        var status = clients[i].client_status;
        var $tr = $('<tr></tr>')
            .append('<td>' + clients[i].client_name + '</td>')
            .append('<td>' + find_i18n('cs_' + status) + '</td>')
            .append('<td>' + clients[i].user + '</td>')
            .append('<td>' + clients[i].image + '</td>')
            .append('<td>' + clients[i].ip + '</td>')
            .append('<td>' + clients[i].heartbeat + '</td>');

        if (clients[i].heartbeat_delay > heartbeat_timeout) {
            $tr.addClass('warning');
        }
        var $status_value = find_i18n('cs_status_'+status)
        var client_html = $('#client_group_page').html()
        // $(".client_show").css('display','none');
        if(i<3){
            $(".client_terminal_show:nth("+i+")").find('p:nth(0)').text(clients[i].client_name);
            $(".client_terminal_show:nth("+i+")").find('.status_open_span:nth(0)').text($status_value);
            $(".client_show:nth("+i+")").css('display','block');
        }else {
            $(client_html)
                    .find('#terminal_name').text(clients[i].client_name).end()
                    .find('#client_status').text($status_value).end()
                    .appendTo($('#client_show')); 
        }  
    }
}

function stat_client_list(clients) {
    var counters = {
        _total: 0,
        power_off: 0,
        power_on: 0,
        authenticated: 0,
        syncing: 0,
        booting: 0,
        running: 0,
        uploading: 0,
        shutting_down: 0,
        unknown: 0,
        initializing_disk: 0,
        updating_firmware: 0
    };

    counters._total = clients.length;
    for (var i in clients) {
        if (clients[i].client_status in counters)
            counters[clients[i].client_status] += 1;
        else
            counters[clients[i].client_status] = 1;
    }

    return counters;
}

function load_client_counters(clients) {
    var c = stat_client_list(clients);

    $('#total-client-count').text(c._total);

    $('#running-count').text(c.running + c.shutting_down);
    $('#tcboot-count').text(c.power_on + c.authenticated);
    $('#osboot-count').text(c.booting);
    $('#syncing-count').text(c.syncing + c.uploading);
    $('#maintaining-count').text(c.initializing_disk + c.updating_firmware);
    $('#error-count').text(c.unknown);

    $('#client_count_online').text(clients.length);
    $('#client_count_running').text(c.running + c.shutting_down);
    $('#client_count_booting').text(
        c.power_on + c.authenticated + c.booting + c.syncing
        + c.uploading + c.initializing_disk + c.updating_firmware
        + c.unknown
    );

    // update online counts
    $('#client-list label:nth(0)').text(clients.length);
    // update total counts
    $('#client-list label:nth(1)').text(c._total);
}


function update_system_services_status(servers) {
    // check TC server status
    // disk server: tc-sync-server, or disk server
    // ccontrol server: tc-client-control-server
    // pxe server: dhcpd with pxe settings
    var active_dhcp = false;
    var disk_status = false;
    var deploy_status = false;
    var tracker_status = false;
    $.map(servers.loaded, function(l) {
        let loaded = l.split(/\s+/);
        if(loaded[0] == 'tcs-dnsmasq.service'){
            active_dhcp = loaded[3] == 'running';
        }
        if(loaded[0] == 'tcs-disk-server.service'){
            disk_status = loaded[3] == 'running';
        }
        if(loaded[0] == 'tcs-ccontrol-server.service'){
            deploy_status = loaded[3] == 'running';
        }
        if(loaded[0] == 'tcs-delivery-static-tracker.service'){
            tracker_status = loaded[3] == 'running';
        }
    });

    var is_on = disk_status && deploy_status;
    if(is_on){
        var $client = $('<img src="/tc/images/open.png" class="open_img"/><span class="open_img_span open_img">'+find_i18n('s_open')+'</span>')
        $('#server_services_action').append($client);
    }else{
        var $client = $('<img src="/tc/images/close.png" class="close_img"/><span style="color:#FF566C;font-weight: 600;" >'+ find_i18n('s_general_close') + '</span>')
        $('#server_services_action').append($client);
    }
    // IE8 on off switch
    $('#switch-system-on').attr('checked', is_on);
    $('#switch-system-off').attr('checked', !is_on);
    // standard on off switch
    $('#switch-system').prop('checked', is_on);

    // update toolbar status according to server on/off
    $('#toolbar_cci button').attr('disabled', !is_on);

    // update DHCP server status
    // IE8 on off switch
    $('#ie8-switch-dhcp-server-on').attr('checked', active_dhcp);
    $('#ie8-switch-dhcp-server-off').attr('checked', !active_dhcp);
    // standard on off switch
    if(active_dhcp){
        var $client = $('<img src="/tc/images/open.png" class="open_img"/><span class="open_img_span">'+find_i18n('s_open')+'</span>')
        $('#switch_dhcp_server').append($client);
    }else{
        var $client = $('<img src="/tc/images/close.png" class="close_img"/><span style="color:#FF566C;font-weight: 600;">'+ find_i18n('s_general_close') + '</span>')
        $('#switch_dhcp_server').append($client);
    }
    $('#switch-dhcp-server').prop('checked', active_dhcp);
    $("input[name='tracker_wan']").prop('checked', tracker_status);
}


function update_server_network() {
    util_page.rest_get('/tc/rest/network.php', function(data) {
        $('#server-ip')
            .text(data.ip)
            .data('sip', data.ip)
            .data('smask', data.mask);
        var speed_text = data.speed_mbps  + ' mbps';
        if (data.speed_mbps === -1) {
            speed_text = find_i18n('s_network_nic_speed_unknown');
        }
        $('#server_nic').text(data.nic + ' (' + speed_text + ')');

        $('#server_nic_kmodule').text(data.kmod);
        $('#server_nic_driver').text(data.driver);

        page_update_network_gateway(data.gateway);
        page_update_network_dns(data.dns);
    });
}

var page_update_switch = function(settings, entry) {
    $("input[name='" + entry + "']").prop('checked', settings[entry]);
    if(settings[entry]){
        var $client = $('<img src="/tc/images/open.png" class="open_img"/><span class="open_img_span">'+ find_i18n('s_open') + '</span>')
        $('#'+entry).append($client);
    }else{
        var $client = $('<img src="/tc/images/close.png" class="close_img"/><span style="color:#FF566C;font-weight: 600;">'+ find_i18n('s_general_close') + '</span>')
        $('#'+entry).append($client);
    }
}

var page_bind_switch = function(entry) {
    $("input[name='" + entry + "']").change(function() {
        var settings = {};
        settings[entry] = $(this).prop('checked');
        util_page.rest_put('/tc/rest/system.php', settings, update_status, function(err) {
            util_page.dialog_message_error(err);
            update_status();
        });
    });
}

var page_update_auto_login_delay = function(val) {
    $('#auto_login_delay span:nth(0)').text(val);
};

var page_read_auto_login_delay = function() {
    return $('#auto_login_delay span:nth(0)').text();
};

var page_update_network_gateway = function(val) {
    if (val) {
        $('#server_gateway').text(val);
    } else {
        $('#server_gateway').text(find_i18n('s_none'));
    }
};

var page_read_network_gateway = function() {
    var text = $('#server_gateway').text();
    if (text === find_i18n('s_none')) {
        return '';
    }
    return text;
};

var page_update_network_dns = function(val) {
    if (val) {
        $('#server_dns').text(val);
    } else {
        $('#server_dns').text(find_i18n('s_none'));
    }
};

var page_read_network_dns = function() {
    var text = $('#server_dns').text();
    if (text === find_i18n('s_none')) {
        return '';
    }
    return text;
};

var page_update_input_text = function(key, val) {
    var value = val;
    if (!value) {
        value = find_i18n('s_none');
    }
    $('#' + key).text(value).data('val', val);
}

var page_update_client_naming_rule = function(settings) {
    if (settings === undefined) {
        util_page.rest_get('/tc/rest/system.php/settings', page_update_client_naming_rule);
        return;
    }
    var rule = find_i18n('s_none');
    if (settings.client_naming) {
        var begin = settings.client_naming_first;
        var width = settings.client_naming_width;
        begin = ('0'.repeat( Math.max(width - begin.toString().length, 0)) + begin).slice(-width);
        var end = '9'.repeat(width);
        rule = settings.client_naming_prefix;
        rule += '[' + begin + '-' + end + ']';
        rule += settings.client_naming_suffix;
    }
    $('#naming_rule').text(rule)
        .data('prefix', settings.client_naming_prefix)
        .data('suffix', settings.client_naming_suffix)
        .data('width', settings.client_naming_width)
        .data('first', settings.client_naming_first);
}

function update_status() {
    util_page.rest_get('/tc/rest/system.php/servers', function(status){
        update_system_services_status(status);
    });

    util_page.rest_get('/tc/rest/system.php/settings', function(settings){
        global_refresh = settings.page_refresh;
        heartbeat_timeout = settings.heartbeat_timeout;

        page_update_auto_login_delay(settings.auto_login_delay);
        page_update_switch(settings, 'client_open_registration');
        page_update_switch(settings, 'client_naming');
        page_update_client_naming_rule(settings);
        page_update_switch(settings, 'ad_domain_enable');
        page_update_switch(settings, 'ps_background_upload');

        page_update_input_text('ad_domain_server_name', settings.ad_domain_server_name);
        page_update_input_text('ad_domain_server_ip', settings.ad_domain_server_ip);

        page_update_input_text('tracker_wan_ip', settings.tracker_wan_ip);
    });

    update_server_network();

    util_page.rest_get('/tc/rest/system.php', function(result){

        $('#ie8-switch-client-registration-on').attr('checked', result.client_open_registration);
        $('#ie8-switch-client-registration-off').attr('checked', !result.client_open_registration);
        $('#total_client_reg').text(result.client_registered);
    });

    util_page.rest_get('/tc/rest/bootimage2.php', function(result){
        update_autoboot_images(result.images);
        $('#image_list tbody').empty();
        show_images_list(result.images);
    });

}

function update_autoboot_images(images) {
    // clear previous autoboot image history
    $text_autoboot.text(find_i18n('s_none', 'none')).attr('title', '');
    // search the image list for the autoboot image
    $.map(images, function(img) {
        if (img.autoboot == 'y') {
            $.map(img.history, function(rev) {
                if (img.autoboot_revision !== rev.revision) {
                    return;
                }
                var text = img.name,
                    title = rev.timestamp;
                //Dashboard show autoboot image revision
                //if (rev.revision) {
                    //var image_revision = find_i18n('s_version') + rev.revision;
                    //text += ', ' + image_revision;
                    //title += ', ' + image_revision;
                //}
                $text_autoboot.text(text).attr('title', title);
            });
        }
    });
}

function show_images_list(imagess) {
    for (var i in imagess) {
        if(i<3){
            var $images = $('<tr></tr>')
            .append('<td class="images_name"><span class="client_value images_name_value">' + imagess[i].name + '</span></td>')
            .append('<td><span class="client_value">' + imagess[i].revision + '</span></td>')
            .append('<td><span class="client_value">' + imagess[i].file_mtime + '</span></td>')

            $('#image_list tbody').append($images);
        }
        
    }
}

function select_autoboot_image(images) {
    // search for autoboot selected image, set the value to '' as the default selection of combo
    var selections = $.map(images, function(img){
        if (img.autoboot == 'y') {
            var selection = {value: ''};
            $.map(img.history, function(history){
                if (history.revision == img.autoboot_revision) {
                    var image_revision = find_i18n('s_version') + history.revision;
                    //Dashboard show autoboot image "name+revision"
                    //selection.text = [img.name, image_revision, history.timestamp].join(', ');
                    selection.text = [img.name, history.timestamp].join(', ');//Dashboard only show autoboot image name
                    selection.revision = history.revision;
                }
            });
            return selection;
        }
    });

    var none_selection_value = -1;
    if($.isEmptyObject(selections)) {
        none_selection_value = '';
    }
    // append other boot images for selection
    $.map(images, function(img){
        // auto boot image has been added already
        if (img.autoboot == 'y') {
            return;
        }
        var selection = {
            revision: img.revision,
            value: img.id
        };
        $.map(img.history, function(history){
            if (parseInt(history.revision) >= parseInt(selection.revision)) {
                var image_revision = find_i18n('s_version') + history.revision;
                //Dashboard only show autoboot image "name+revision"
                //selection.text = [img.name, image_revision, history.timestamp].join(', ');
                selection.text = [img.name, history.timestamp].join(', ');//Dashboard only show autoboot image name
                selection.revision = history.revision;
            }
        });
        selections.push(selection);
    });
    // append option 'none' for clear autoboot image
    selections.push({text: find_i18n('s_none', 'none'), value: none_selection_value, revision: 0});

    var title = find_i18n('s_select_client_autoboot_image', 'Select auto login image file for clients');
    util_page.dialog_select(title, selections, function(select_id){
        if (select_id) {
            var data = {autoboot: 'y', id: select_id},
                url = '/tc/rest/bootimage2.php/' + select_id;
            $.map(selections, function(selection){
                if (selection.value == select_id) {
                    data['revision'] = selection.revision;
                }
            });
            util_page.rest_put(url, data, update_status);
        }
    });
}

var page_bind_input_text = function(name) {
    var save_value = function(val) {
        var settings = {};
        settings[name] = val;
        util_page.rest_put('/tc/rest/system.php/settings', settings, function() {
            page_update_input_text(name, val);
        });
    }

    $('#' + name).click(function() {
        util_page.dialog_prompt(find_i18n('s_input_server_' + name), save_value)
            .find('input').val($(this).data('val'));
    });
}

var page_bind_client_naming_rule = function() {
    $('#naming_rule').click(function() {
        var html = $('#dialog_client_naming_rule').html(),
            btn_text = find_i18n('s_save');

        util_page.dialog_confirm_builder(html, btn_text, function() {
            var rule = {
                client_naming_prefix: $(this).find('input:nth(0)').val(),
                client_naming_suffix: $(this).find('input:nth(1)').val(),
                client_naming_width: $(this).find('input:nth(2)').val(),
                client_naming_first: $(this).find('input:nth(3)').val()
            };
            util_page.rest_put('/tc/rest/system.php', rule, function() {
                page_update_client_naming_rule();
            });
        })
            .find('input:nth(0)').val($(this).data('prefix')).end()
            .find('input:nth(1)').val($(this).data('suffix')).end()
            .find('input:nth(2)').val($(this).data('width')).end()
            .find('input:nth(3)').val($(this).data('first'));
    });
}

function bind_server_side_control() {

    // $('#server_services_action').click(function() {
    //     if($('#server_services_action .close_img')){
        
    //     }
    //     if($('#server_services_action .open_img')){

    //     }
    // });
    var $system_switch = $('#server-side .onoff-switch input:nth(0)'),
        $dhcp_switch = $('#server-side .onoff-switch input:nth(1)');

    $system_switch.change(function(){
        if ($system_switch.prop('checked')) {
            util_page.rest_post(
                '/tc/rest/controller.php',
                {rpc: 'start-system'},
                update_status,
                function(res) {
                    util_page.dialog_message_error(res);
                    update_status();
                }
            );
        } else {
            util_page.do_rpc3('stop-system', {}, update_status);
        }
    });

    $dhcp_switch.change(function() {
        var data = {rpc: 'stop-dhcp'};
        if ($(this).prop('checked')) {
            data = {rpc: 'start-dhcp'};
        }
        util_page.rest_post('/tc/rest/controller.php', data, update_status);
    });

    // $('#auto_login_delay').click(function() {
    //     util_page.dialog_prompt_required(
    //         find_i18n('s_input_auto_login_delay'),
    //         function(val) {
    //             var settings = {auto_login_delay: val};
    //             util_page.rest_put(
    //                 '/tc/rest/system.php',
    //                 settings,
    //                 function() {
    //                     page_update_auto_login_delay(val);
    //                 }
    //             );
    //         }
    //     ).find('input').val(page_read_auto_login_delay());
    // });

    // $('#server_gateway').click(function() {
    //     util_page.dialog_prompt(
    //         find_i18n('s_input_server_gateway'),
    //         function(val) {
    //             var settings = {gateway: val};
    //             util_page.rest_put(
    //                 '/tc/rest/network.php',
    //                 settings,
    //                 function() {
    //                     page_update_network_gateway(val);
    //                 }
    //             );
    //         }
    //     ).find('input').val(page_read_network_gateway());
    // });

    // $('#server_dns').click(function() {
    //     util_page.dialog_prompt(
    //         find_i18n('s_input_server_dns'),
    //         function(val) {
    //             var settings = {dns: val};
    //             util_page.rest_put(
    //                 '/tc/rest/network.php',
    //                 settings,
    //                 function() {
    //                     page_update_network_dns(val);
    //                 }
    //             );
    //         }
    //     ).find('input').val(page_read_network_dns());
    // });

    page_bind_input_text('ad_domain_server_name');
    page_bind_input_text('ad_domain_server_ip');

    page_bind_switch('client_open_registration');
    page_bind_switch('client_naming');
    page_bind_switch('ad_domain_enable');
    page_bind_switch('tracker_wan');
    page_bind_switch('ps_background_upload');
    page_bind_input_text('tracker_wan_ip');

    page_bind_client_naming_rule();
}

function change_autoboot() {
    util_page.rest_get('/tc/rest/bootimage2.php', function(result){
        select_autoboot_image(result.images);
    });
}

var enable_chart_fs = function() {
    var request_usage = function(done) {
        util_page.rest_get('/tc/rest/system.php/filesystem', function(res) {
            var total = res.disk_total_gib.toFixed(2),
                free = res.disk_free_gib.toFixed(2),
                used = res.disk_used_gib.toFixed(2),
                percent = res.disk_used_percent.toFixed(2);
            done(percent, total, used, free);
        });
    };

    var fileSystemChart = null;

    request_usage(function(percent, total, used, free) {
        var used_text = find_i18n('s_used'),
            remain_text = find_i18n('s_remain'),
            user_percent = (100-percent).toFixed(2);
            

        fileSystemChart = new Chart(document.getElementById("fileSystem-chart"), {
            type: 'doughnut',
            data: {
              labels: [used_text, remain_text],
              datasets: [
                {
                  backgroundColor:  ["#FF566C", "#3E8AFF"],
                  data: [percent,user_percent]
                }
              ]
            },
            options: {
                 hover: {
                    mode: 'index'
                },
                legend: {
                    display: false
                }
            }
        });

        // $('#fs-data #fs_usage_data span:nth(5)').text(total);
        // $('#fs-data #fs_usage_data span:nth(7)').text(used);
        // $('#fs-data #fs_usage_data span:nth(9)').text(free);
        $('#fs_total_size').text(total);
        $('#fs_used_size').text(used);
        $('#fs_free_size').text(free);
        $('#fs_usage_percent').text(percent + ' %');
    });

    var refresh_usage_fs = function() {
        if (global_refresh && fileSystemChart) {
            request_usage(function(percent, total, used, free) {
                fileSystemChart.data.datasets[0].data[0] = percent.toFixed(2);
                fileSystemChart.data.datasets[0].data[1] = (100-percent).toFixed(2);
                fileSystemChart.update();

                $('#ie8_fs_usage_percent').text(percent + ' %');
                $('#fs_usage_percent').text(percent + ' %');
                $('#fs-data #fs_usage_data span:nth(1)').text(total);
                $('#fs-data #fs_usage_data span:nth(3)').text(used);
                $('#fs-data #fs_usage_data span:nth(5)').text(free);

                global_refresh = true;
            });
        }
        global_refresh = false;
        setTimeout(refresh_usage_fs, 2000);
    };
    setTimeout(refresh_usage_fs, 2000);
};

$(document).ready(function(){
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();
    // bind server side controls
    bind_server_side_control();

    // update data for whole page
    update_status();

    // util_page.rest_get('/tc/rest/machine.php?online=1', function(result){
    //     $('#client-list tbody').empty();
    //     // $('#client_show').empty();
    //     load_client_list(result.clients);
    //     load_client_counters(result.clients);
    //     global_refresh = true;
    // });

    var refresh_clients = function() {
        if (global_refresh) {
            setTimeout(refresh_clients, 2000);
        }

        if ($('#client-toolbar button').is(':disabled')) {
            return;
        }

        if (global_refresh) {
            util_page.rest_get('/tc/rest/machine.php?online=1', function(result){
                $('#client-list tbody').empty();
                // $('#client_show').empty();
                load_client_list(result.clients);
                load_client_counters(result.clients);
                global_refresh = true;
            });

        }
        global_refresh = false;
    };

    setTimeout(refresh_clients, 2000);
    var used_text = find_i18n('s_used'),
        remain_text = find_i18n('s_remain');
    var networkChart = new Chart(document.getElementById("network-chart"), {
        type: 'doughnut',
        data: {
          labels: [used_text, remain_text],
          datasets: [
            {
              label: "My First dataset",
              backgroundColor: ["#FF566C", "#3E8AFF"],
              data: [1,99]
            }
          ]
        },
        options: {
             hover: {
                mode: 'index'
            },
            legend: {
                display: false
            }
        }
    });
    $('#network_nic_usage_percent').text('1.00 %');
    

    var refresh_nic_usage = function() {
        if (global_refresh) {
            util_page.rest_get('/tc/rest/network.php/nic_usage', function(result) {
            networkChart.data.datasets[0].data[0] = result.usage_tx_percent.toFixed(2);
            networkChart.data.datasets[0].data[1] = (100-result.usage_tx_percent).toFixed(2);
            networkChart.update();
             //   nic_usage_gauge.refresh(result.usage_tx_percent);
                $('#network_nic_usage_percent').text(result.usage_tx_percent + ' %');
                global_refresh = true;
            });
        }
        global_refresh = false;
        setTimeout(refresh_nic_usage, 2000);
    };
    setTimeout(refresh_nic_usage, 2000);

    enable_chart_fs();
});

</script>

<?php
    body_end();
    page_end();
?>
