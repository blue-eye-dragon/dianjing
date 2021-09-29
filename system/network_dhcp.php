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

    standard_page_begin('network_dhcp');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
<div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array(
                        "i18n" => "s_common_refresh",
                        "icon" => "glyphicon-refresh",
                    ),
                ),
            ),
            // array(
            //     "buttons" => array(
            //         array("i18n" => "s_dhcp_import", "icon" => "glyphicon-open"),
            //         array("i18n" => "s_dhcp_export", "icon" => "glyphicon-save"),
            //     ),
            // ),
            array(
                "buttons" => array(
                    array(
                        "i18n" => "s_dhcp_empty_leases",
                        "icon" => "glyphicon-warning-sign",
                    ),
                ),
            ),
        ),
    );
    echo html_toolbar($bar);
?>
</div>


<div class="row top15 row_div">

<!-- left side -->
<div class="col-xs-12" >
    <h4 class="title_span border_right"><span i18n="s_dhcp_subnet_settings" style="padding-left: 15px;"></span></h4>
    <div style="padding-left:24px;padding-bottom:35px;border-bottom:0px solid #D9D9D9">
        <table class="status-table table_list" >
            <tbody>
                <tr>
                    <td><span i18n="s_server_dhcp_services">DHCP service</span></td>
                    <td>
                        <div class="onoff-switch">
                            <input type="checkbox" id="switch-dhcp_services" class="onoff_swith_input" name="switch-dhcp_services">
                            <label for="switch-dhcp_services" class="green"></label>
                        </div>

                    </td>

                    <td style="padding-left: 50px"><span i18n="s_server_tftp_services">TFTP service</span></td>
                    <td>
                        <div class="onoff-switch">
                            <input type="checkbox" id="switch-tftp_services" class="onoff_swith_input" name="switch-tftp_services">
                            <label for="switch-tftp_services" class="green"></label>
                        </div>
                    </td>

                    <td style="padding-left: 50px"><span i18n="s_server_dns_services">DNS service</span></td>
                    <td>
                        <div class="onoff-switch">
                            <input type="checkbox" id="switch-dns_services" class="onoff_swith_input" name="switch-dns_services">
                            <label for="switch-dns_services" class="green"></label>
                        </div>
                    </td>

                </tr>

            </tbody>
        </table>
    </div>
    
</div>
<div class="col-xs-12" style="padding-left:24px;" >
    <div class="form-group bottom5" style="height:90px;margin-top:32px;margin-left: 15px;">
        <label class="input_label"><span i18n="s_subnet_settings" >Subnet setting</span></label>
        <div id="input-dhcp-subnet-range" >
            <input type="text" length="32" style="text-align: left" class="input_type">
            <span style="margin:0 24px">-</span>
            <input type="text" length="32" style="text-align: left" class="input_type">
            <button i18n="s_edit" class="btn btn-link">Change</button>
        </div>
    </div>
    <div class="col-xs-12" style="border-bottom:1px solid #D9D9D9;">
        <div class="form-group bottom5 network_set" style="padding-left:0;height:100px;">
            <label class="top5 input_label"><span i18n="s_dhcp_subnet_mask">Subnet mask</span></label>
            <div id="input-dhcp-subnet-mask">
                <input type="text" length="32" style="text-align: left" class="input_type">
                <button i18n="s_edit" class="btn btn-link">Change</button>
            </div>
        </div>
        <div class="form-group bottom5 network_set" style="padding-left:0;height:100px;">
            <label class="input_label"><span i18n="s_dhcp_routers" >Subnet router</span></label>
            <div id="input-dhcp-router">
                <input type="text" length="32" style="text-align: left" class="input_type">
                <button i18n="s_edit" class="btn btn-link">Change</button>
            </div>
        </div>
        <div class="form-group bottom5 network_set" style="padding-left:0;height:100px;">
            <label class="input_label"><span i18n="s_dhcp_dns" >Subnet DNS</span></label>
            <div id="input-dhcp-dns">
                <input type="text" length="32" style="text-align: left" class="input_type">
                <button i18n="s_edit" class="btn btn-link">Change</button>
            </div>
        </div>
        <div class="col-xs-8" style="margin-bottom: 30px;margin-top: 32px;">
            <button type="button" class="btn reset_submit" id="save-button" style="float:left;width:90px;margin-left: 40%;    margin-right: 50px;">
                <span i18n="s_save">Save</span>
            </button>
            <button type="button" class="btn reset_cencal" id="cancel-button" style="float:left;width:90px;color:#999">
                <span i18n="s_cancel">Cancel</span>
            </button>
        </div>
    </div>
    
    
</div>


<!-- Right side for ip static assignment  -->
<div class="col-xs-6">
    <div class="row ">
        <h4 class="col-xs-12 title_span border_right" style="margin-left:15px;margin-top:65px"><span i18n="s_static_ip_assignment">Static IP Assignment</span></h4>
        <div class="col-xs-12" style="padding-left: 0;">
            <div class="btn-toolbar tc-toolbar" role="toolbar">
                <div class="btn-group" role="group" style="width:100%">
                    <button type="button" class="btn btn-default btn-sm" id="add-sip">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span i18n="s_add">Add</span>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="del-sip">
                        <span class="glyphicon glyphicon-minus"></span>
                        <span i18n="s_delete_selected">Delete Selected</span>
                    </button>
                    <!-- <span id="check_edit" style="float:right" onclick="update_check()"><img src="/tc/images/batch.png"/></span> -->
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table tc-table table_list" id="sip-table">
                <thead>
                    <tr>
                        <th> <input type="checkbox"> </th>
                        <th> <span i18n="s_client"></span> </th>
                        <th> <span i18n="s_ip_address"> IP Address </span> </th>
                        <th> <span i18n="s_mac_address"> MAC Address </span> </th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12">
            <?php control_pagination(); ?>
        </div>
    </div>
</div>

    <div class="col-xs-6">
        <h4 class="title_span border_right" style="margin-top:65px"><span i18n="s_dhcp_lease" style="padding-left: 15px;"></span></h4>
        <table class="table table-striped table_list" id="lease_table">
            <thead>
                <tr>
                    <th></th>
                    <th> <span i18n="s_client"></span> </th>
                    <th> <span i18n="s_dhcp_lease_ip"></span> </th>
                    <th> <span i18n="s_dhcp_lease_mac"></span> </th>
                    <th> <span i18n="s_dhcp_lease_expiration"></span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

<!-- top layout row ending -->
</div>




<!-- top container ending -->
</div>

<div style="display: none;">
    <div id="dialog-add-sip">
        <div class="btn-group" data-toggle="buttons" id="toolbar_add_sip">
            <button type="button" class="btn btn-default" id="client_btn"><span i18n="s_client">Client</span></button>
            <button type="button" class="btn btn-default" id="mac_btn"><span i18n="s_mac_address">MAC Address</span></button>
        </div>
        <div class="form-group">
            <label class="client_label"><span i18n="s_client">Client</span> :</label>
            <select class="form-control client_select" ></select>
        </div>
        <div class="form-group" style="display: none;">
            <label class="client_label"><span i18n="s_mac_address">MAC Address</span> :</label>
            <input type="text" class="form-control client_select"></input>
        </div>
        <div class="form-group">
            <label class="client_label"><span i18n="s_ip_address">IP Address</span> :</label>
            <input type="text" class="form-control client_select"></input>
        </div>
    </div>
    <div id="dialog-input-range">
        <h4><span i18n="s_dhcp_subnet_range" >Subnet range</span></h4>
        <div class="form-group" style="margin-top:30px">
            <label class="client_label"><span i18n="s_dhcp_subnet_begin">Subnet Begin</span> :</label>
            <input type="text" class="form-control client_select"></input>
        </div>
        <div class="form-group">
            <label class="client_label"><span i18n="s_dhcp_subnet_end">Subnet End</span> :</label>
            <input type="text" class="form-control client_select"></input>
        </div>
    </div>
</div>
<div id="form_upload_file" style="display: none;">
    <h4><span i18n="s_dhcp_export"></span></h4>
    <div class="form-group">
        <label i18n="s_dhcp_import_title">Select DHCP settings file</label>
        <hr class="compact">
        <input type="file" id="file-0" name="file-0">
    </div>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
    .client_label{
        min-width: 90px;
        text-align: right;
    }
    .client_select{
        float: left;
        width: 245px;
        margin-left:15px;
    }
    #toolbar_add_sip button span{
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 400;
        color: #666666;
    }
    #toolbar_add_sip button{
        width:45%
    }
    #toolbar_add_sip .focus{
        border-style:0;
        outline:none;
    }
    #toolbar_add_sip{
        width: 100%;
        border-bottom: 1px solid #DADADA;
        padding-bottom: 10px;
        margin-bottom: 30px;
    }
    /* .onoff-switch{
        width:55px;
    }
    .onoff-switch-label{
        height:25px;
    } */
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
    #switch-dns_services:checked+label.green,
    #switch-tftp_services:checked+label.green,
    #switch-dhcp_services:checked+label.green{
        background: #1890FF;
    }
    #switch-dns_services:checked+label:after,
    #switch-tftp_services:checked+label:after,
    #switch-dhcp_services:checked+label:after{
        left: calc(100% - 21px);
    } 
    #switch-dns_services+label,
    #switch-tftp_services+label,
    #switch-dhcp_services+label{
        background: #ddd;
        border-radius: 25px;
    }
    #switch-dns_services+label:after,
    #switch-tftp_services+label:after,
    #switch-dhcp_services+label:after{
      background: #fff;
      border-radius: 50%;
      width: 20px;
      height: 19px;
      top: 3px;
      left: 2px;
    }

    .btn-link{
        display:none;
    }

</style>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";
var checkShow = false;
function enable_input_dhcp_subnet(subnet_begin, subnet_end, subnet_mask,dhcp_router,dhcp_dns) {
    //dhcp configs
    var $subnet_begin = $('#input-dhcp-subnet-range').find('input:nth(0)'),
        $subnet_end = $('#input-dhcp-subnet-range').find('input:nth(1)'),
        $subnet_mask = $('#input-dhcp-subnet-mask').find('input'),
        $subnet_route = $('#input-dhcp-router').find('input'),
        $subnet_dns = $('#input-dhcp-dns').find('input');

    $subnet_begin.val(subnet_begin);
    $subnet_end.val(subnet_end);
    $subnet_mask.val(subnet_mask);
    $subnet_route.val(dhcp_router);
    $subnet_dns.val(dhcp_dns);

    var send_subnet = function(begin, end, mask, fn_done, fn_error) {
        util_page.rest_put(
            '/tc/rest/network.php',
            {
                dhcp_subnet_begin: begin,
                dhcp_subnet_end: end,
                dhcp_subnet_mask: mask
            },
            fn_done,
            fn_error);
    }

    $('#save-button').click(function(){
        var $isOk = true;
        var $input_dns = $('#input-dhcp-dns input');
        $subnet_begin.removeClass('bg-danger');
        $subnet_end.removeClass('bg-danger');

        send_subnet(
            $subnet_begin.val(),
            $subnet_end.val(),
            $subnet_mask.val(),
            function(){
            },
            function(error){
                $subnet_begin.addClass('bg-danger');
                $subnet_end.addClass('bg-danger');
                util_page.dialog_message_error(error);
                $isOk = false;
            }
        );
        $('#input-dhcp-router').find('input').removeClass('bg-danger');
        // $('#input-dhcp-router').find('input').val(value);
        util_page.rest_put('/tc/rest/network.php', {dhcp_router: $('#input-dhcp-router').find('input').val()}, function(){
        }, function(error){
            $('#input-dhcp-router').find('input').addClass('bg-danger');
            util_page.dialog_message_i18n(error.error);
            $isOk = false;
        });
        $input_dns
            .attr('disabled', util_page.is_empty($input_dns.val()))
            .removeClass('bg-danger')

        util_page.rest_put('/tc/rest/network.php', {dhcp_dns: $input_dns.val()}, $.noop, function(error){
            $input_dns.addClass('bg-danger');
            util_page.dialog_message_error(error);
            $isOk = false;
        });

        if($isOk){
            util_page.dialog_message_i18n('s_dhcp_net_edit');
        }
        
    })

    $('#cancel-button').click(function(){
        util_page.rest_get('/tc/rest/network.php/dhcp', function(data){
            
            var $subnet_begin = $('#input-dhcp-subnet-range').find('input:nth(0)'),
            $subnet_end = $('#input-dhcp-subnet-range').find('input:nth(1)'),
            $subnet_mask = $('#input-dhcp-subnet-mask').find('input');

            $subnet_begin.val(data.dhcp_subnet_begin);
            $subnet_end.val(data.dhcp_subnet_end);
            $subnet_mask.val(data.dhcp_subnet_mask);
            $('#input-dhcp-router').find('input').val(data.dhcp_router);
            var $input_dns = $('#input-dhcp-dns input');

            $input_dns
                .attr('disabled', util_page.is_empty(data.dhcp_dns))
                .val(data.dhcp_dns)
        });
    })

    $('#input-dhcp-subnet-range').find('button').click(function(){
        var $dialog = util_page.dialog_confirm_builder(
            $('#dialog-input-range').html(),
            find_i18n('s_save'),
            find_i18n('s_cancel'),
            function(){
                var begin = $dialog.find('input:nth(0)').val(),
                    end = $dialog.find('input:nth(1)').val();
                $subnet_begin.val(begin);
                $subnet_begin.removeClass('bg-danger');
                $subnet_end.val(end);
                $subnet_end.removeClass('bg-danger');

                send_subnet(
                    begin,
                    end,
                    $subnet_mask.val(),
                    function(){
                        $subnet_mask.val(mask);
                    },
                    function(error){
                        $subnet_begin.addClass('bg-danger');
                        $subnet_end.addClass('bg-danger');
                        util_page.dialog_message_error(error);
                        $subnet_mask.addClass('bg-danger');
                        util_page.dialog_message_i18n(error.error);
                    }
                );
            });
        $dialog.find('input:nth(0)').val($subnet_begin.val());
        $dialog.find('input:nth(0)').focus();
        $dialog.find('input:nth(1)').val($subnet_end.val());
    });

    $('#input-dhcp-subnet-mask').find('button').click(function(){
        util_page.dialog_prompt_required(find_i18n('s_input_new_netmask'), function(mask){
            $subnet_mask.removeClass('bg-danger');
            send_subnet($subnet_begin.val(), $subnet_end.val(), mask,
                function() {
                    $subnet_mask.val(mask);
                },
                function(error){
                    $subnet_mask.addClass('bg-danger');
                    util_page.dialog_message_i18n(error.error);
                }
            );
        }).find('input').val($subnet_mask.val());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_input_netmask')+'</span> :</label>')
        $form.append($client);
    });
}

function enable_input_router(router) {
    // dhcp router
    $('#input-dhcp-router').find('input').val(router);
    $('#input-dhcp-router').find('button').click(function(){
        var $dialog = util_page.dialog_prompt_required(find_i18n('s_input_new_dhcp_router'), function(value){
            $('#input-dhcp-router').find('input').removeClass('bg-danger');
            $('#input-dhcp-router').find('input').val(value);
            util_page.rest_put('/tc/rest/network.php', {dhcp_router: value}, function(){
            }, function(error){
                $('#input-dhcp-router').find('input').addClass('bg-danger');
                util_page.dialog_message_i18n(error.error);
            });
        });
        $dialog.find('input').val($('#input-dhcp-router').find('input').val());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_input_dhcp_router')+'</span> :</label>')
        $form.append($client);
    });
}

function enable_input_dhcp_dns(dhcp_dns) {
    // dhcp router
    var $input_dns = $('#input-dhcp-dns input');

    $input_dns
        .attr('disabled', util_page.is_empty(dhcp_dns))
        .val(dhcp_dns);

    $('#input-dhcp-dns button').click(function(){
        var $dialog = util_page.dialog_prompt(find_i18n('s_input_new_dhcp_dns'), function(value){
            $input_dns
                .attr('disabled', util_page.is_empty(value))
                .removeClass('bg-danger')
                .val(value);

            util_page.rest_put('/tc/rest/network.php', {dhcp_dns: value}, $.noop, function(error){
                $input_dns.addClass('bg-danger');
                util_page.dialog_message_error(error);
            });
        });
        $dialog.find('input').val($input_dns.val());
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_input_dhcp_dns')+'</span> :</label>')
        $form.append($client);
    });
}

function fill_ethers(ethers) {
    function parse_record() {
        var records = [],
            $select = $('#dialog-add-sip').find('select:nth(0)');
        $select.empty();
        $.map(ethers, function(r) {
            var $option = $('<option></option').val(r.id);

            if (r.ip) {
                records.push({
                    key: r.id,
                    row: [r.name, r.ip, r.mac],
                    checkable: true
                });
                $option.text(r.name + ', ' + r.mac + ', ' + r.ip);
            } else {
                $option.text(r.name + ', ' + r.mac);
            }

            $option.appendTo($select);
        });
        return records;
    }

    if(parse_record().length>0){
        util_table.load(parse_record());
    }
    // else{
    //     var $noData = '<tr style="text-align: center;"><td colspan="6" style="border-bottom:0"><img src="/tc/images/no_data.png" style="width:100px;margin-top:20px"/><p style="margin-top: 20px;margin-right: 23px;"><span style="color:#bfbfbf">' + find_i18n('s_no_data') +'</span></p></td></tr>'
    //     $('#sip-table tbody').append($noData);
    // }
    
    $('#del-sip').prop('disabled', true);
    // $("#sip-table thead tr th input").css("display",'none');
    // $('#check_edit').click(function(){
        
    //     // checkShow = !checkShow;
    // });
}

// function update_check(){
//     var $control_edit = $("#sip-table thead tr th input").css("display");
        
//         if($control_edit!=='block'){
//             $("#sip-table thead tr th input").css("display","block");
//             $("#sip-table tbody input").css("display",'block');
//         }else{
//             $("#sip-table thead tr th input").css("display",'none');
//             $("#sip-table tbody input").css("display",'none');
//         }
// }

function load_ethers() {
    util_page.rest_get('/tc/rest/ethers.php', function(data){
        fill_ethers(data);
    });
}

function load_leases(leases) {
    $('#lease_table tbody').empty();
    var count = 1;
    if(leases.length>0){
        $.map(leases, function(lease) {
            var html = '';
            html += '<td>' + count + '</td>';
            html += '<td>' + lease.tc_client + '</td>';
            html += '<td>' + lease.ipv4_addr + '</td>';
            html += '<td>' + lease.hw_addr + '</td>';
            html += '<td>' + lease.expiration + '</td>';
            html = '<tr>' + html + '</tr>';
            count = count + 1;
            $('#lease_table tbody').append(html);
        });
    }else{
        var $noData = '<tr style="text-align: center;"><td colspan="12" style="border-bottom:0"><img src="/tc/images/no_data.png" style="width:100px;margin-top:20px"/><p style="margin-top: 20px;margin-right: 23px;"><span style="color:#bfbfbf">' + find_i18n('s_no_data') +'</span></p></td></tr>'
        $('#lease_table tbody').append($noData)
    }
    
}
function bind_services_control() {
    var $dhcp_services = $('#switch-dhcp_services');
    var $tftp_services = $('#switch-tftp_services');
    var $dns_services = $('#switch-dns_services');
    var tftp_message = find_i18n('c_tftp_services');
    $dhcp_services.change(function(){
        var dhcp_data = {rpc: 'stop-dnsmasq-dhcp'};
        if ($dhcp_services.prop('checked')) {
            dhcp_data = {rpc: 'start-dnsmasq-dhcp'};
        }
        util_page.rest_post('/tc/rest/controller.php', dhcp_data, update_services_control_status);
    });

    $tftp_services.change(function(){
        if ($tftp_services.prop('checked')) {
            //tftp switch open
        } else {
            util_page.dialog_message(tftp_message);
            $tftp_services.prop('checked', true);
        }
    });

    $dns_services.change(function(){
        var dns_data = {rpc: 'stop-dnsmasq-dns'};
        if ($dns_services.prop('checked')) {
            dns_data = {rpc: 'start-dnsmasq-dns'};
        }
        util_page.rest_post('/tc/rest/controller.php', dns_data, update_services_control_status);
    });
}

function update_services_control_status() {
    var $dhcp_services = $('#switch-dhcp_services');
    var $tftp_services = $('#switch-tftp_services');
    var $dns_services = $('#switch-dns_services');
    $tftp_services.prop('checked', true);

    util_page.rest_get('/tc/rest/system.php/servers', function(res) {
        let dhcp_status = res['dnsmasq.dhcp']['status'] == 'on' ? true : false;
        let dns_status = res['dnsmasq.dns']['status'] == 'on' ? true : false;
        $dhcp_services.prop('checked', dhcp_status);
        $dns_services.prop('checked', dns_status);
    });
}

function page_init() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();
    update_services_control_status();
    bind_services_control();
    // load network configurations
    util_page.rest_get('/tc/rest/network.php/dhcp', function(data){
        enable_input_dhcp_subnet(data.dhcp_subnet_begin, data.dhcp_subnet_end, data.dhcp_subnet_mask,data.dhcp_router,data.dhcp_dns);
        // enable_input_router(data.dhcp_router);
        // enable_input_dhcp_dns(data.dhcp_dns);

        load_leases(data.dhcp_leases);
    });

    // load Static IP assignment SIP
    util_table.create($('.tc-table'));
    util_table.enable_pagination($('#page-control'));
    util_table.enable_sort();
    util_table.bind_checked(function($trs){
        if ($trs.length == 0) {
            $('#del-sip').prop('disabled', true);
        } else {
            $('#del-sip').prop('disabled', false);
        }
    });

    load_ethers();

    // bind buttons handler
    $('#add-sip').click(function(){
        var $dialog = util_page.dialog_confirm_builder(
            $('#dialog-add-sip').html(),
            find_i18n('s_save'),
            find_i18n('s_cancel'),
            function() {
                var use_mac = $dialog.find('input:nth(0)').parent().is(':visible'),
                    cid = $dialog.find('select').val();

                if (use_mac) {
                    cid = null;
                    var mac = $.trim($dialog.find('input:nth(0)').val());
                    if (mac === '') {
                        util_page.dialog_message_i18n('e_empty_mac');
                        return false;
                    }
                    mac = util_page.parse_mac(mac);
                    $dialog.find('option').map(function(){
                        var ws = $(this).text().split(',');
                        if (ws[1].trim() === mac) {
                            cid = $(this).val();
                        }
                    });
                }
                if (cid) {
                    var ip = $dialog.find('input:nth(1)').val();
                    util_page.rest_delete(
                        '/tc/rest/ethers.php/' + cid,
                        '',
                        function(){
                            util_page.rest_post(
                                '/tc/rest/ethers.php/' + cid,
                                {'ip': ip},
                                function() {
                                    load_ethers();
                                }
                            );
                        });
                } else {
                    util_page.dialog_message_i18n('e_static_ip_binding');
                }
            }
        );
        $dialog.find('#client_btn span').css('color','#5D9DFF');
        $dialog.find('#toolbar_add_sip button:nth(0)').click(function() {
            $dialog.find('#client_btn span').css('color','#5D9DFF');
            $dialog.find('#mac_btn span').css('color','#666');
            $dialog.find('#toolbar_add_sip button:nth(0)').css('border','0');
            $dialog.find('.form-group').eq(0).css('display', '');
            $dialog.find('.form-group').eq(1).css('display', 'none');
        });
        $dialog.find('#toolbar_add_sip button:nth(1)').click(function() {
            $dialog.find('#mac_btn span').css('color','#5D9DFF');
            $dialog.find('#client_btn span').css('color','#666');
            $dialog.find('#toolbar_add_sip button:nth(1)').css('border','0');
            $dialog.find('.form-group').eq(0).css('display', 'none');
            $dialog.find('.form-group').eq(1).css('display', '');
        });

        function fill_ip_input($dialog, text) {
            $dialog.find('input:nth(1)').val('');
            var ws = text.split(',');
            if (ws.length > 2) {
                $dialog.find('input:nth(1)').val(ws[2]);
            }
        }
        function fill_mac_input($dialog, text) {
            var ws = text.split(',');
            if (ws.length > 1) {
                $dialog.find('input:nth(0)').val(ws[1].trim());
            }
        }
        var $select = $dialog.find('select');
        $select.val(0).change(function(){
            fill_ip_input($dialog, $select.find('option:selected').text());
            fill_mac_input($dialog, $select.find('option:selected').text());
        });
    });
    $('#del-sip').click(function(){
        var bags = util_table.checked();
        var urls = $.map(bags, function(bag){
            return '/tc/rest/ethers.php/' + bag.key;
        });

        util_page.rest_delete(urls, null, load_ethers);
    });

    // refresh button
    $('.btn-toolbar button:nth(0)').click(function() {
        util_page.page_refresh();
    });
    // import button
    // $('.btn-toolbar button:nth(1)').click(function() {
    //     util_page.dialog_upload('/tc/rest/network.php', find_i18n('s_btn_dhcp_import'),function() {
    //         util_page.dialog_message(find_i18n('s_dhcp_import_done'), function() {
    //             location.reload();
    //         });
    //     });
    // });
    // // export button
    // $('.btn-toolbar button:nth(2)').click(function() {
    //     util_page.navi_page('/tc/rest/file.php/network');
    //     util_page.dialog_message_i18n('s_dhcp_export_done');
    // });
    // empty leases button
    $('.btn-toolbar button:nth(1)').click(function() {
        util_page.dialog_confirm(find_i18n('c_dhcp_empty_leases'), function(){
            util_page.rest_delete(
                '/tc/rest/network.php/dhcp',
                {option: 'empty_leases'},
                function() {
                    util_page.page_refresh();
                }
            );
        });
        var $form = $('.modal-body');
        var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
        $form.append($client);
    });
}

$(document).ready(page_init);

</script>

<?php standard_page_end(); ?>