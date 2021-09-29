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

    standard_page_begin('client_detail');
?>

<div class="container-fluid container_table" style="margin-left: -15px;height: calc(100vh - 100px);">
    <div class="row">
        <div class="col-xs-6" >
            <table class="table table-striped about_table" style="margin-top: 20px;">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_name"> Name </span> : </td>
                        <td class="about_value" id="table_client_name"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_mac_address"> MAC Address </span> : </td>
                        <td class="about_value" id="table_client_mac"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_memory_size"> Memory Size </span> : </td>
                        <td class="about_value" id="table_client_memory_size"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_speed_limit_download_title"></span> : </td>
                        <td class="about_value" id="table_client_download_KBS"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_usb_storage"></span> : </td>
                        <td class="about_value" id="table_client_usb_storage"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_description"> Description </span> : </td>
                        <td class="about_value" id="table_client_memo"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-xs-6" style="padding-right:45px">
            <table class="table table-striped about_table" style="margin-top: 20px;">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_client_group"> Group </span> : </td>
                        <td class="about_value" id="table_client_group"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span >CPU</span> : </td>
                        <td class="about_value" id="table_client_cpu"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_disk_size"> Disk Size </span> : </td>
                        <td class="about_value" id="table_client_disk_size"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_speed_limit_upload_title"></span> : </td>
                        <td class="about_value" id="table_client_upload_KBS"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_display_resolution"></span> : </td>
                        <td class="about_value" id="table_client_resolution"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<div class="wrapper" style="display: none;">
    <div class="row row_div">
        <h4 class="title_span border_right">
            <span i18n="s_ad_domain" style="padding-left:15px">AD Domain</span>
        </h4>
        <div style="margin-bottom:25px">
            <button class="btn btn-default" id="t01_add"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span><span i18n="s_add">Add</span></button>
            <button class="btn btn-default" id="t01_del" disabled><span class="glyphicon glyphicon-minus" aria-hidden="true"></span><span i18n="s_delete">Delete</span></button>
            <button class="btn btn-default" id="t01_edit" disabled><span class="glyphicon glyphicon-edit" aria-hidden="true"></span><span i18n="s_edit">Edit</span></button>
            <span id="check_edit" style="float:right"><img src="/tc/images/batch.png"/></span>
        </div>
        <table class="table col-md-12 table01" id="table_client_list">
            <thead>
                <tr>
                    <th> <input type="checkbox"> </th>
                    <th> <span i18n="s_client_detail_client_image"></span> </th>
                    <th> <span i18n="s_client_detail_profile_name"></span> </th>
                    <th> <span i18n="s_client_detail_profile_value"></span> </th>
                    <th> <span i18n="s_client_detail_last_update"></span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


</div>

<div style="display: none;">
    <div id="dialog_put_data">
        <h4><span i18n="s_add"></span></h4>
        <div class="form-group" style="margin-top:30px">
            <label class="model_label control-label" id="dialog_client_image" style="width: 29%;"></label>
            <select class="form-control" id="selector_client_image">
            </select>
        </div>
        <div class="form-group">
            <label class="model_label control-label" id="dialog_profile_name" style="width: 29%;"></label>
            <select class="form-control" id="selector_profile_name">
            </select>
        </div>
        <div class="form-group">
            <label class="model_label control-label" id="dialog_profile_value" style="width: 29%;"></label>
            <select class="form-control" id="selector_profile_value">
            </select>
        </div>
        
    </div>
</div>
<style>
    .form-group{
        height:40px;
    }
    .modal-footer{
        display:block;
    }
    /* .bootbox-body{
        height: 190px;
    } */
    .table>tbody>tr>td{
        border-bottom:0;
    }
    .table>tbody>tr>.about_value{
        border-bottom: 1px solid #C2C2C2;
    }
    .about_title{
        width:160px
    }
    .model_label{
        width: 25%;
    }
    .bootbox-body .form-group .form-control{
        float: left;
        width: 245px;
        margin-left: 15px;
    }
</style>

<?php standard_page_mid(); ?>

<script language="javascript">

var key = null;
var mac = null;
var uuid_dic = {};
var image_name_dic = {};
function page_load() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_table.create($('.table01'));
    // util_table.enable_pagination($('#page-control')); //wait to deal
    util_table.enable_sort();
    // $("#table_client_list thead tr th input").css("display",'none');
    util_table.bind_checked(function($trs){
        if ($trs.length == 0) {
            $('#t01_add').prop('disabled', false);
            $('#t01_del').prop('disabled', true);
            $('#t01_edit').prop('disabled', true);
        } else if ($trs.length == 1) {
            $('#t01_add').prop('disabled', false);
            $('#t01_del').prop('disabled', false);
            $('#t01_edit').prop('disabled', false);
        } else if ($trs.length > 1) {
            $('#t01_add').prop('disabled', false);
            $('#t01_del').prop('disabled', false);
            $('#t01_edit').prop('disabled', true);
        }
    });
    key = location.search.replace(/[^\d]/g, "");

    util_page.rest_get('/tc/rest/machine.php/'+key, function(res) {
        mac = res['mac'];
        var memory = res['memory_size'] == 0 ? '' : res['memory_size'],
            disk = res['disk_size'] == 0 ? '' : res['disk_size'],
            usb_storage = find_i18n(
                res['usb_storage'] ? 's_usb_storage_enabled' : 's_usb_storage_disabled');
        if (memory) {
            memory = util_page.print_size(memory, 'MiB');
        }

        if (disk) {
            disk = util_page.print_size(disk, 'GiB');
        }
        $('#table_client_name').text(res['name']);
        $('#table_client_group').text(res['client_group_name']);
        $('#table_client_mac').text(res['mac']);
        $('#table_client_cpu').text(res['cpu_model']);
        $('#table_client_memory_size').text(memory);
        $('#table_client_disk_size').text(disk);
        $('#table_client_download_KBS').text(res['download_MBS'] == 0 ? '' : res['download_MBS'] + ' MB/s' );
        $('#table_client_upload_KBS').text(res['upload_MBS'] == 0 ? '' : res['upload_MBS'] + ' MB/s' );
        $('#table_client_usb_storage').text(usb_storage);
        $('#table_client_resolution').text(res['resolution']);
        $('#table_client_memo').text(res['memo']);
        load_table();
    });

    $('#t01_add').click(dialog_add);
    $('#t01_del').click(dialog_del);
    $('#t01_edit').click(dialog_edit);
    $('#check_edit').click(function(){
        var $control_edit = $("#table_client_list thead tr th input").css("display");
        
        if($control_edit == 'block'){
            $("#table_client_list thead tr th input").css("display",'none');
            $("#table_client_list tbody input").css("display",'none');
        }else{
            $("#table_client_list thead tr th input").css("display","block");
            $("#table_client_list tbody input").css("display",'block');
        }
    });
}

function load_table() {
    util_page.rest_get('/tc/rest/client.php/' + key, function(data) {
        save_uuid(data);
        save_image_name(data);
        util_table.load(parse_record(data));
    });
}

function save_uuid(record){
    $.map(record.client_images_available, function(r) {
        uuid_dic[r.id] = r.base_uuid;
    });
}

function save_image_name(record){
    $.map(record.client_images_available, function(r) {
        image_name_dic[r.id] = r.name;
    });
}

function parse_record(record) {
    var records = [];
    var id = 1;
    var profile_value = null;
    $.map(record.client_profile, function(r) {
        let profile_N = '';
        if(r.profile_name.trim() == 'save_domain_diff'){
            profile_N = find_i18n('s_profile_name_save');
        }else if(r.profile_name.trim()  == 'patch_domain_diff'){
            profile_N = find_i18n('s_profile_name_patch');
        }else{
            profile_N = '';
        }
        profile_value = (r.profile_value == 'true') ? find_i18n('s_general_open') : find_i18n('s_general_close');
        var row = [image_name_dic[r.image_id], profile_N, profile_value, r.create_time];
        records.push({
            key: r.client_profile_id,
            row: row,
            checkable: true
        });
    });
    return records;
}

var dialog_del = function() {
    var bags = util_table.checked(),
        message = find_i18n('c_delete_selected_client_detail'),
        total_count = bags.length,
        done_count = 0,
        error_count = 0,
        error_lines = '';
    util_page.dialog_confirm(message, function(){
        $.map(bags, function(bag){
            util_page.rest_delete(
                '/tc/rest/client.php/' + key,
                {'client_profile_id': bag.key},
                function() {
                    done_count += 1;
                    if (total_count === done_count + error_count) {
                        if (error_count === 0) {
                            util_page.dialog_message_i18n('s_update_done_ok');
                        } else {
                            util_page.dialog_message(error_lines);
                        }
                        load_table();
                    }
            }, function(error) {
                error_count += 1;
                error_lines += '<p>['+ bag.row[0] + '] ' + find_i18n(error.error) + '</p>';
                if (total_count === done_count + error_count) {
                    if (error_count === 0) {
                        util_page.dialog_message_i18n('s_update_done_ok');
                    } else {
                        util_page.dialog_message(error_lines);
                    }
                    load_table();
                }
            });
        });
    });

}

var dialog_add = function() {
    $('#selector_client_image').empty();
    $('#selector_profile_name').empty();
    $('#selector_profile_value').empty();
    var client_image_label = find_i18n('s_client_detail_client_image'),
        profile_name_label = find_i18n('s_client_detail_profile_name'),
        profile_value_label = find_i18n('s_client_detail_profile_value');
    $('#dialog_client_image').text(client_image_label+' :');
    $('#dialog_profile_name').text(profile_name_label+' :');
    $('#dialog_profile_value').text(profile_value_label+' :');

    var profile_name_array = { 'save_domain_diff': find_i18n('s_profile_name_save'), 'patch_domain_diff': find_i18n('s_profile_name_patch')};

    $.each(profile_name_array, function(key, value) {
     $('#selector_profile_name').append($('<option></option>').attr('value', key).text(value));
    });

    var profile_value_array = { 'true': true, 'false': false};

    $.each(profile_value_array, function(key, value) {
        var profile_value_display = (key == 'true') ? find_i18n('s_general_open') : find_i18n('s_general_close');
        $('#selector_profile_value').append($('<option></option>').attr('value', key).text(profile_value_display));
    });

    util_page.rest_get('/tc/rest/client.php/' + key, function(clients) {
        $.map(clients.client_images_available, function (client) {
            if(client.ostype == 'win7' || client.ostype == 'win10') {
                $('#selector_client_image').append($('<option>', {
                    value: client.id,
                    text : client.name
                }));
            }
        });
        var html = $('#dialog_put_data').html(),
            save_btn_text = find_i18n('s_save');
            cancel_btn_text = find_i18n('s_cancel');
        var $dialog = util_page.dialog_confirm_builder(html, save_btn_text,cancel_btn_text, function() {
            let ciid = $dialog.find('#selector_client_image :selected').val();
            let p_n = $dialog.find('#selector_profile_name :selected').val();
            let p_v = $dialog.find('#selector_profile_value :selected').val();
            ad_domain_add({profile_type: 'domain',
                            profile_name: p_n,
                            profile_value: p_v,
                            ciid: ciid});
        });
    });
}
var ad_domain_add = function(user_data) {
    util_page.rest_post('/tc/rest/client.php/' + key, user_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_table();
    });
};

function getKeyByValue(object, value) {
  return Object.keys(object).find(key => object[key] === value);
}

var dialog_edit = function() {
    $('#selector_profile_value').empty();

    var client_image_label = find_i18n('s_client_detail_client_image'),
        profile_name_label = find_i18n('s_client_detail_profile_name'),
        profile_value_label = find_i18n('s_client_detail_profile_value');
    $('#dialog_client_image').text(client_image_label);
    $('#dialog_profile_name').text(profile_name_label);
    $('#dialog_profile_value').text(profile_value_label);

    var profile_name_array = { 'save_domain_diff': find_i18n('s_profile_name_save'), 'patch_domain_diff': find_i18n('s_profile_name_patch')};

    $.each(profile_name_array, function(key, value) {
     $('#selector_profile_name').append($('<option></option>').attr('value', key).text(value));
    });

    var profile_value_array = { 'true': true, 'false': false};

    $.each(profile_value_array, function(key, value) {
        var profile_value_display = (key == 'true') ? find_i18n('s_general_open') : find_i18n('s_general_close');
        $('#selector_profile_value').append($('<option></option>').attr('value', key).text(profile_value_display));
    });

    util_page.rest_get('/tc/rest/client.php/' + key, function(clients) {
        $.map(clients.client_images_available, function (client) {
            $('#selector_client_image').append($('<option>', {
                value: client.id,
                text : client.name
            }));
        });

        var p_n = null;
        var p_v = null;
        var bags = util_table.checked();
        $('#selector_client_image :selected').text(bags[0].row[0]);
        $('#selector_profile_name :selected').text(bags[0].row[1]);
        if(bags[0].row[1] == find_i18n('s_profile_name_save')){
            p_n = 'save_domain_diff';
        }else{
            p_n = 'patch_domain_diff';
        }
        $('#selector_client_image').prop('disabled', true);
        $('#selector_profile_name').prop('disabled', true);
        var html = $('#dialog_put_data').html(),
            save_btn_text = find_i18n('s_save');
            cancel_btn_text = find_i18n('s_cancel');
        var $dialog = util_page.dialog_confirm_builder(html, save_btn_text, cancel_btn_text,function() {
            // p_n = $dialog.find('#selector_profile_name :selected').val();
            p_v = $dialog.find('#selector_profile_value :selected').val();
            ad_domain_edit({profile_type: 'domain',
                            profile_name: p_n,
                            profile_value: p_v,
                            profile_version: '1',
                            image_id: getKeyByValue(image_name_dic, bags[0].row[0]),
            });
        });
        $('#selector_client_image').prop('disabled', false);
        $('#selector_profile_name').prop('disabled', false);
    });
}

var ad_domain_edit = function(user_data) {
    var this_uuid = uuid_dic[user_data.image_id];
    delete user_data.image_id;
    util_page.rest_put('/tc/rest/client.php?uuid='+this_uuid+'&mac='+mac, user_data, function() {
            util_page.dialog_message_i18n('s_update_done_ok');
            load_table();
    });
};

$(document).ready(page_load);


</script>

<?php standard_page_end(); ?>