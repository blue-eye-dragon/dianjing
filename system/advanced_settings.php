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

standard_page_begin('advanced_settings');
?>

<!-- <div class="container-fluid container_advanced">
    
</div> -->
<div style="display: flex;justify-content:flex-start ;margin-bottom:13px">
    <!-- 服务 -->
    <div style="background-color: #FFF;margin-right:10px;height:250px;width:33%">
        <div style="display: flex;height:159px">
            <div class="item_border" >
                <span i18n="s_server_status"></span>
                <div style="text-align: center;margin-top: 25px;">
                    <img src="/tc/images/service.png" />
                </div>
            </div>
            <div class="item_right">
                <div class="item_cont1" style="display: flex;justify-content: center;align-items:center;">
                    <div class="item_right_title">
                        <p><span i18n="s_TCI_core_services"></span></p>
                    </div>
                    <div style="text-align: center;">
                        <div class="onoff-switch">
                            <!-- <input type="checkbox" name="switch-system" class="onoff-switch-checkbox" id="switch-system">
                            <label class="onoff-switch-label" for="switch-system">
                                <span class="onoff-switch-inner"></span>
                                <span class="onoff-switch-switch"></span>
                            </label> -->
                            <input type="checkbox" id="switch-system" class="onoff_swith_input" name="switch-system">
                            <label for="switch-system" class="green"></label>
                        </div>
                    </div>
                </div>
                <div class="item_cont2" style="display: flex;justify-content: center;margin-top:25px;align-items:center">
                    <div class="item_right_title"><span i18n="s_TCI_network_service"></span></div>
                    <div style="text-align: center;">
                        <div class="onoff-switch">
                            <!-- <input type="checkbox" name="switch-dhcp-server" class="onoff-switch-checkbox" id="switch-dhcp-server">
                            <label class="onoff-switch-label" for="switch-dhcp-server">
                                <span class="onoff-switch-inner"></span>
                                <span class="onoff-switch-switch"></span>
                            </label> -->
                            <input type="checkbox" id="switch-dhcp-server" class="onoff_swith_input" name="switch-dhcp-server">
                            <label for="switch-dhcp-server" class="green"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="address_show">
            <div style="display: flex;justify-content:flex-start;">
                <div style="display: flex;justify-content:flex-start;" class="server_ip_div">
                    <div style="min-width:68px"><span i18n="s_network_address" ></span>:
                    </div>
                    <a href="javascript:change_server_ip()" class="text_space server_a" id="server_ip_a"><span id="server-ip">10.121.142.29</span></a>
                </div>
                <div style="display: flex;justify-content:center">
                    <div style="min-width:80px"><span i18n="s_network_dns" ></span>:
                    </div>
                    <a href="#" class="text_space server_a" id="server_dns_a"><span id="server_dns">10.96.1.18</span></a>
                </div>
            </div>
            <div style="display: flex;justify-content:center;justify-content:flex-start;margin-top:15px;">
                <div style="min-width:68px"><span i18n="s_network_gateway" ></span>:</div>
                 <a href="#" class="text_space server_a" id="server_gateway_a"><span id="server_gateway" >10.121.142.29</span></a>
            </div>
        </div>

    </div>
    <!-- 状态 -->
    <div style="background-color: #FFF;margin-right:10px;height:250px;width:33%">
        <div style="display: flex;height:159px">
            <div class="item_border item_border_2">
                <span i18n="s_client_status"></span>
                <div style="text-align: center;margin-top: 25px; ">
                    <!-- <p class="img_div img_client_div"></p> -->
                    <img src="/tc/images/client.png" />
                </div>
            </div>
            <div class="item_right">
                <div class="item_cont1" style="display: flex;justify-content: center;align-items:center">
                    <div class="item_right_title">
                        <p><span i18n="s_dashboard_client_naming"></span></p>
                    </div>
                    <div style="text-align: center;">
                        <div class="onoff-switch">
                            <!-- <input type="checkbox" name="client_naming" class="onoff-switch-checkbox" id="client_naming">
                            <label class="onoff-switch-label" for="client_naming">
                                <span class="onoff-switch-inner"></span>
                                <span class="onoff-switch-switch"></span>
                            </label> -->
                            <input type="checkbox" id="client_naming" class="onoff_swith_input" name="client_naming">
                            <label for="client_naming" class="green"></label>
                        </div>
                    </div>
                </div>
                <div class="item_cont2" style="display: flex;justify-content: center;margin-top:25px;align-items:center">
                    <div class="item_right_title"><span i18n="s_dashboard_client_registration"></span></div>
                    <div style="text-align: center;">
                        <div class="onoff-switch">
                            <!-- <input type="checkbox" name="client_open_registration" class="onoff-switch-checkbox" id="client_open_registration">
                            <label class="onoff-switch-label" for="client_open_registration">
                                <span class="onoff-switch-inner"></span>
                                <span class="onoff-switch-switch"></span>
                            </label> -->
                            <input type="checkbox" id="client_open_registration" class="onoff_swith_input" name="client_open_registration">
                            <label for="client_open_registration" class="green"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style=" background-color: #fff;border-top:  1px solid #ECECEC;padding:10px 0; margin: 0 25px">

            <div style="text-align:center">
                <div>自动注册规则</div> 
                <div style="margin-top:15px;">
                <a href="#" ><span id="naming_rule">----无----</span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- 权限 -->
    <div style="background-color: #FFF;margin-right:10px;height:250px;width:33%">
        <div style="display: flex;height:159px">
            <div class="item_border item_border_3">
                <span i18n="s_permission_global"></span>
                <div style="text-align: center;margin-top: 25px;">
                    <!-- <p class="img_div img_authority_div" id="img_div"></p> -->
                    <img src="/tc/images/authority.png" />
                </div>
            </div>
            <div  style="flex-grow: 1;text-align: center;margin: 25px 0;">
                <div style="text-align: center;">
                    <p><span i18n="s_ps_background_upload_short"></span></p>
                    <div style="text-align: center;margin-top:45px">

                        <div class="onoff-switch">
                            <!-- <input type="checkbox" name="ps_background_upload" class="onoff-switch-checkbox" id="ps_background_upload">
                            <label class="onoff-switch-label" for="ps_background_upload">
                                <span class="onoff-switch-inner"></span>
                                <span class="onoff-switch-switch"></span>
                            </label> -->
                            <input type="checkbox" id="ps_background_upload" class="onoff_swith_input" name="ps_background_upload">
                            <label for="ps_background_upload" class="green"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style=" background-color: #fff;border-top:  1px solid #ECECEC;padding:10px 0; margin: 0 25px">
            <div style="display: flex;justify-content:center">
                <!-- <div class="auto_login_image">
                    <div>
                        自动登录镜像
                    </div>
                    <div style="margin-top:15px;">
                    <a href="javascript:change_autoboot()"><span id="autoboot-image">----无----</span></a>
                    </div>
                </div> -->
                <div style="text-align:center">
                    <div>
                        自动登录延时
                    </div>
                    <div style="margin-top:15px;">
                    <a href="#"><span  id="auto_login_delay"></span>&nbsp;<span i18n="s_second"></span></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- ssL -->
    <div style="background-color: #FFF;height:136px;height:250px;width:15%;display:none">
        <div class="SSL_set">
            <span i18n="s_ssl_encryption"></span>
            <div class="onoff-switch" style="margin-top: 25px;">
                <!-- <input type="checkbox" name="switch-system6" style="height: 15px;width: 30px;" class="onoff-switch-checkbox" id="switch-system6">
                <label class="onoff-switch-label" for="switch-system6">
                    <span class="onoff-switch-inner"></span>
                    <span class="onoff-switch-switch"></span>
                </label> -->
                <input type="checkbox" id="switch-system6" class="onoff_swith_input" name="switch-system6">
                <label for="switch-system6" class="green"></label>
            </div>
        </div>
        <div style="margin: 45px 0 0;text-align: center;">
            <span i18n="s_settings_nic"></span>
            <div style="text-align: center;margin-top:15px"><a href="#"><span id="nic_settings">ens5f0</span></a></div>
        </div>

    </div>





</div>

<div class="container-fluid container_table" style="display:none">
    <div style="margin: 28px 0 0 25px;">
        <div style="font-size: 16px;">系统工作状态</div>
    </div>
    <div class="row no-gutters row_div" style="margin-bottom: 20px;display:none">
        <div class="container-fluid col-xs-3 col-sm-3 col-md-3 col-lg-3" id="settings_side" style="max-width: 280px;">
            <table class="info-table dashboard-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="tooltip-settings">
                                <span i18n="s_ssl_encryption" id="ssl_label">SSL Encryption Data Transmission(Reboot clients required)</span>
                                <div class="bottom">
                                    <h4><span i18n="s_tooltip_desc">Description</span></h4>
                                    <p><span i18n="s_tooltip_ssl">Use SSL Encryption to encrypt the data transmitting <br>between TCI server and client.</span></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="onoff-switch">
                                <!-- <input type="checkbox" name="switch-system" class="onoff-switch-checkbox" id="switch-system">
                                <label class="onoff-switch-label" for="switch-system">
                                    <span class="onoff-switch-inner"></span>
                                    <span class="onoff-switch-switch"></span>
                                </label> -->
                                <input type="checkbox" id="switch-system" class="onoff_swith_input" name="switch-system">
                                <label for="switch-system" class="green"></label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span i18n="s_settings_nic">NIC Settings</span></td>
                        <td><a href="#"><span id="nic_settings"></span></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 网络地址的modal -->
    <div style="display: none;">
        <div id="dialog_server_address">
        <h4><span i18n="s_dashboard_client_naming_rule"></span></h4>
            <div class="form-group" style="margin-top:30px;">
                <label style="min-width: 125px;"><span i18n="s_ip_address">IP Address</span> :</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label style="min-width: 125px;"><span i18n="s_subnet_mask">Subnet Mask</span>: </label>
                <input type="text" class="form-control">
            </div>
        </div>
        <div id="dialog_client_naming_rule">
            <h4 style="margin-bottom: 20px;"><span i18n="s_dashboard_client_naming_rule"></span></h4>
            <div class="form-group" >
                <label i18n="s_batch_prefix" style="margin-left: 35px;"></label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label i18n="s_batch_suffix" style="margin-left: 35px;"></label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label i18n="s_batch_width" style="margin-left: 35px;"></label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label i18n="s_batch_first" style="margin-left: 35px;"></label>
                <input type="text" class="form-control">
            </div>
        </div>
    </div>

    <div class="row row_div">
        <table class="table tc-table table_list">
            <thead>
                <tr>
                    <th> <input type="checkbox" style="display:none"> </th>
                    <th> <span i18n="s_name"> Name </span> </th>
                    <th> <span> </span> </th>
                    <th> <span> </span> </th>
                    <th> <span> </span> </th>
                    <th> <span> </span> </th>
                    <th> <span> </span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>
<style>
    .modal-footer {
        display: block;
    }
    .form-control{
        margin-left:15px;
    }
    #img_div img{
        margin-top:5px;
    }
    input[type="checkbox"]{
        display: none;
    }
    input[type="checkbox"]+label{
        display: inline-block;
        width: 45px;
        height: 25px;
        position: relative;
        transition: 0.3s;
        margin: 0px 20px;
        box-sizing: border-box;
    }
    input[type="checkbox"]+label:after{
        content: '';
        display: block;
        position: absolute;
        left: 0px;
        top: 0px;
        width: 25px;
        height: 25px;
        transition: 0.3s;
        cursor: pointer;
    }
    input[type="checkbox"]+label:before{
        content: '';
        display: block;
        position: absolute;
        left: 0px;
        top: 0px;
        width: 25px;
        height: 25px;
        transition: 0.3s;
        cursor: pointer;
    }
    #switch-system6:checked+label.green,
    #ps_background_upload:checked+label.green,
    #client_open_registration:checked+label.green,
    #client_naming:checked+label.green,
    #switch-dhcp-server:checked+label.green,
    #switch-system:checked+label.green{
        background: #1890FF;
    }
    #switch-system6:checked+label:after,
    #ps_background_upload:checked+label:after,
    #client_open_registration:checked+label:after,
    #switch-dhcp-server:checked+label:after,
    #client_naming:checked+label:after,
    #switch-system:checked+label:after{
        left: calc(100% - 21px);
    } 
    #switch-system6+label,
    #ps_background_upload+label,
    #client_open_registration+label,
    #switch-dhcp-server+label,
    #client_naming+label,
    #switch-system+label{
        background: #ddd;
        border-radius: 25px;
    }
    #switch-system6+label:after,
    #ps_background_upload+label:after,
    #client_open_registration+label:after,
    #switch-dhcp-server+label:after,
    #client_naming+label:after,
    #switch-system+label:after{
      background: #fff;
      border-radius: 50%;
      width: 20px;
      height: 19px;
      top: 3px;
      left: 2px;
    }
</style>




<?php standard_page_mid(); ?>
<script language="javascript">
    "use strict";

var global_refresh = true,
    heartbeat_timeout = 120;
var $text_autoboot = $('#autoboot-image');

    // update data for whole page
    update_status();
    // 改变192.168的modal
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
            cancel_btn_text = find_i18n('s_cancel'),
            old_ip = $('#server-ip').data('sip'),
            old_mask = $('#server-ip').data('smask');
        util_page.dialog_confirm_builder(html, btn_text, cancel_btn_text,do_change_ip)
            .find('input:nth(0)').val(old_ip).end()
            .find('input:nth(1)').val(old_mask);
    }

    function update_server_network() {
        util_page.rest_get('/tc/rest/network.php', function(data) {
            $('#server-ip')
                .text(data.ip)
                .data('sip', data.ip)
                .data('smask', data.mask);
            $('#server_ip_a').attr('title',data.ip);
            var speed_text = data.speed_mbps + ' mbps';
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
    //网关网络
    var page_update_network_gateway = function(val) {
        if (val) {
            $('#server_gateway').text(val);
            $('#server_gateway_a').attr('title',val);
        } else {
            $('#server_gateway').text(find_i18n('s_none'));
            $('#server_gateway_a').attr('title',find_i18n('s_none'));
        }
    };
    var page_read_network_gateway = function() {
        var text = $('#server_gateway').text();
        if (text === find_i18n('s_none')) {
            return '';
        }
        return text;
    };
    $('#server_gateway').click(function() {
        util_page.dialog_prompt(
            find_i18n('s_network_gateway'),
            function(val) {
                var settings = {
                    gateway: val
                };
                util_page.rest_put(
                    '/tc/rest/network.php',
                    settings,
                    function() {
                        page_update_network_gateway(val);
                    }
                );
            }
        ).find('input').val(page_read_network_gateway());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_network_gateway')+'</span> :</label>')
        $form.append($client);
    });
    // DNS地址
    var page_update_network_dns = function(val) {
        if (val) {
            $('#server_dns').text(val);
            $('#server_dns_a').attr('title',val);
        } else {
            $('#server_dns').text(find_i18n('s_none'));
            $('#server_dns_a').attr('title',find_i18n('s_none'));
        }
    };
    var page_read_network_dns = function() {
        var text = $('#server_dns').text();
        if (text === find_i18n('s_none')) {
            return '';
        }
        return text;
    };
    // $('#server_dns').click(function() {
    //     util_page.dialog_prompt(
    //         find_i18n('s_network_dns'),
    //         function(val) {
    //             var settings = {
    //                 dns: val
    //             };
    //             util_page.rest_put(
    //                 '/tc/rest/network.php',
    //                 settings,
    //                 function() {
    //                     page_update_network_dns(val);
    //                 }
    //             );
    //         }
    //     ).find('input').val(page_read_network_dns());
    //     var $form = $('.bootbox-form');
    //     var $client = $('<label ><span >'+find_i18n('s_network_dns')+'</span> :</label>')
    //     $form.append($client);
    // });
    // 自动注册规则

    var page_update_client_naming_rule = function(settings) {
        if (settings === undefined) {
            util_page.rest_get('/tc/rest/system.php/settings', page_update_client_naming_rule);
            return;
        }
        var rule = find_i18n('s_none');
        if (settings.client_naming) {
            var begin = settings.client_naming_first;
            var width = settings.client_naming_width;
            begin = ('0'.repeat(Math.max(width - begin.toString().length, 0)) + begin).slice(-width);
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
    $('#naming_rule').click(function() {
        var html = $('#dialog_client_naming_rule').html(),
            btn_text = find_i18n('s_save'),
            cancel_btn_text = find_i18n('s_cancel')
        util_page.dialog_confirm_builder(html, btn_text, cancel_btn_text,function() {
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
    //自动登陆镜像
    var $text_autoboot = $('#autoboot-image');

    function change_autoboot() {
        util_page.rest_get('/tc/rest/bootimage2.php', function(result) {
            select_autoboot_image(result.images);
        });
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
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_image_file')+'</span> :</label>')
        $form.append($client);
        
    }
    //自动登录延时
    var page_update_auto_login_delay = function(val) {
        $('#auto_login_delay').text(val);
    };
    var page_read_auto_login_delay = function() {
        return $('#auto_login_delay').text();
    };

    var page_update_switch = function(settings, entry) {
        $("input[name='" + entry + "']").prop('checked', settings[entry]);
    }

    var page_update_input_text = function(key, val) {
        var value = val;
        if (!value) {
            value = find_i18n('s_none');
        }
        $('#' + key).text(value).data('val', val);
    }

    function update_status() {
        util_page.rest_get('/tc/rest/system.php/servers', function(status) {
            update_system_services_status(status);
        });

        util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
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

        util_page.rest_get('/tc/rest/system.php', function(result) {
            $('#ie8-switch-client-registration-on').attr('checked', result.client_open_registration);
            $('#ie8-switch-client-registration-off').attr('checked', !result.client_open_registration);

            $('#total_client_reg').text(result.client_registered);
        });

        util_page.rest_get('/tc/rest/bootimage2.php', function(result) {
            update_autoboot_images(result.images);
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
        $('#switch-dhcp-server').prop('checked', active_dhcp);

        $("input[name='tracker_wan']").prop('checked', tracker_status);
    }

    var $system_switch = $('#switch-system'),
        $dhcp_switch = $('#switch-dhcp-server');

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
    page_bind_switch('client_open_registration');
    page_bind_switch('client_naming');
    page_bind_switch('ad_domain_enable');
    page_bind_switch('tracker_wan');
    page_bind_switch('ps_background_upload');
    $('#auto_login_delay').click(function() {
        util_page.dialog_prompt_required(
            find_i18n('s_input_auto_login_delay'),
            function(val) {
                var settings = {
                    auto_login_delay: val
                };
                util_page.rest_put(
                    '/tc/rest/system.php',
                    settings,
                    function() {
                        page_update_auto_login_delay(val);
                    }
                );
            }
        ).find('input').val(page_read_auto_login_delay());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_login_delay')+'</span> :</label>')
        $form.append($client);
    });

    
    
    // 网关设置
    var read_nic = function() {
        util_page.rest_get('/tc/rest/network.php', function(data) {
            $('#nic_settings').text(data.nic);
        });
    };
    // $('#nic_settings').click(function() {
    //         var title = find_i18n('s_input_nic_settings');
    //         util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
    //             var selections = util_page.dialog_select_selections();
    //             for (let i in settings.nic_list) {
    //                 selections.push({text: settings.nic_list[i].trim(), value: settings.nic_list[i].trim()});
    //             }
    //             util_page.dialog_select(title, selections, function(value) {
    //                 update_nic(value);
    //             });
    //         });
    //     });

    // 下面是原来的
    function bind_server_side_control() {
        var $ssl_encryp = $('#switch-system6');
        var message = find_i18n('c_ssl_encrption');
        $ssl_encryp.change(function() {
            if ($ssl_encryp.prop('checked')) {
                util_page.dialog_confirm_or_cancel(message, function(result) {
                    if (result == false) {
                        $ssl_encryp.prop('checked', false);
                    } else {
                        util_page.rest_put(
                            '/tc/rest/system.php/', {
                                ssl_encryption: 'true'
                            },
                            update_status,
                            function(res) {
                                util_page.dialog_message_error(res);
                                update_status();
                            }
                        );
                    }
                });
            } else {
                util_page.dialog_confirm_or_cancel(message, function(result) {
                    if (result == false) {
                        $ssl_encryp.prop('checked', true);
                    } else {
                        util_page.rest_put(
                            '/tc/rest/system.php/', {
                                ssl_encryption: 'false'
                            },
                            update_status,
                            function(res) {
                                util_page.dialog_message_error(res);
                                update_status();
                            }
                        );
                    }
                });
            }
        });

        $('#nic_settings').click(function() {
            var title = find_i18n('s_nic');
            util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
                var selections = util_page.dialog_select_selections();
                for (let i in settings.nic_list) {
                    selections.push({
                        text: settings.nic_list[i].trim(),
                        value: settings.nic_list[i].trim()
                    });
                }
                util_page.dialog_select(title, selections, function(value) {
                    update_nic(value);
                });
                var $form = $('.bootbox-form');
                var $client = $('<label ><span >'+find_i18n('s_nic')+'</span> :</label>')
                $form.append($client);
            });
        });
    }



    var read_ssl_encryption = function() {
        util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
            $('#settings_side .onoff-switch input:nth(0)').prop('checked', settings.ssl_encryption);
        });
    };

    var update_nic = function(val) {
        util_page.rest_put('/tc/rest/system.php/settings', {
            nic_setting: val
        }, function(data) {
            util_page.dialog_message_i18n('s_update_done_ok');
            read_nic();
        });
    };

    var read_nic = function() {
        util_page.rest_get('/tc/rest/network.php', function(data) {
            $('#nic_settings').text(data.nic);
        });
    };

    function load_table() {
        util_page.rest_get('/tc/rest/system.php/servers', function(data) {
            util_table.load(parse_record(data));
        });
    }

    function parse_record(result) {
        var records = [];
        var installed_arr = [];
        var id = 1;
        var i = 0;

        $.map(result.installed, function(r) {
            installed_arr[i++] = r.split(/\s+/); //0:name 1:able/disable
        });

        $.map(result.loaded, function(l) {
            let arr = l.split(/\s+/);
            let des = arr.slice(4).join(' ');
            $.map(installed_arr, function(s) {
                if (arr[0] == s[0]) {
                    s[2] = arr[1];
                    s[3] = arr[2];
                    s[4] = arr[3];
                    s[5] = des;
                }
            });
        });

        $.map(installed_arr, function(a) {
            records.push({
                key: id,
                row: [a[0], a[2], a[3], a[4], a[5], a[1]],
                checkable: true
            });
            id++;
        });
        return records;
    }

    $(document).ready(function() {
        util_page.disable_cache();
        util_page.enable_locale();
        util_page.render_sidebar();
        // bind server side controls
        bind_server_side_control();
        read_ssl_encryption();
        read_nic();

        util_table.create($('.tc-table'));
        load_table();

    });
</script>
<?php standard_page_end(); ?>