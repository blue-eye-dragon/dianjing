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

    standard_page_begin('client_image');
?>

<div class="container-fluid container_table">
    <h3 style="padding-right:20px"><span i18n="s_client_detail_client_image" style="margin-left:30px"></span></h3>
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_image_update", "icon" => "glyphicon-refresh"),
                    array("i18n" => "s_client_image_remove_changes", "icon" => "glyphicon-trash"),
                ),
            ),
        ),
        "id" => "toolbar_control",
    );
    echo html_toolbar($bar);
?>
    </div>

    <div class="row row_div" style="margin-top: 10px;">
        <h4 class="title_span border_right" style="margin-left:7px">
            <span i18n="s_info_main" style="padding-left:15px">info main</span>
        </h4>
        <div class="col-xs-6">
            <table class="table table-striped about_table" style="margin-top: 20px;" id="table_ci_info">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_revision">Revision</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_file_size">File Size</span> : </td>
                        <td class="about_value" ></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_register_time">Date Registered</span> : </td>
                        <td class="about_value" ></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span>uuid</span> : </td>
                        <td class="about_value" ></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <table class="table table-striped about_table" style="margin-top: 20px;" id="table_right_info">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_image_type">Image Type</span> : </td>
                        <td class="about_value" id="table_client_group"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_apparent_size">Image Size</span> : </td>
                        <td class="about_value" id="table_client_cpu"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_updated_time">Date Updated</span> : </td>
                        <td class="about_value" id="table_client_disk_size"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12">
            <h4 class="title_span border_right" style="margin-bottom:10px">
                <span i18n="s_rp_list" style="padding-left:15px">list</span>
            </h4>
            <div id="rp_list"></div>
            
            <!-- <div class="rp_list">
                <p><span id="rp_version">list</span></p>
                <p style="width:40%;float:left" id="rp_name"><span >win10-enterwin10</span></p>
                <p><span id="register_time">win10-enterwin10</span></p>
                <p class="rp_btn" ><span i18n="s_rp_discard" id="rp_discard">list</span></p>
                <p class="rp_btn" style="margin-right:15px"><span i18n="s_rp_restore" id="s_rp_restore">list</span></p>
            </div> -->
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-6">
                    <h4 class="title_span border_right" style="margin-left:10px">
                        <span i18n="s_ps_group_title" style="padding-left:15px">Personal Storage</span>
                    </h4>
                    <div class="btn-group" role="group" id="tb_group_ps">
                        <button type="button" class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-plus"></span> 
                            <span i18n="s_ps_group_add"></span>
                         </button>
                    </div>
                    <div class="list-group" id="ps_group_list"></div>
                </div>
                <div class="col-xs-6">
                    <h4 class="title_span border_right">
                        <span i18n="s_image_ag" style="padding-left:15px">Authorized Group</span>
                    </h4>
                    <div class="btn-group" role="group" id="tb_group_gr">
                        <button type="button" class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-plus"></span> 
                            <span i18n="s_bootimage_acl_add"></span>
                        </button>
                    </div>
                    <div class="list-group" id="acl_group_list"></div>
                </div>
            </div>
        </div>
        

    </div>
</div>
<div style="display: none" id="rp_list_item">
    <div class="rp_list list-rp-item" style="width:242px;float:left;margin-left:20px;margin-top:20px">
        <p class="col-xs-8" style="float:left;padding-left:0"><img src="/tc/images/version.png" style="margin-right:10px"/><span id="rp_version" class="rp_version">版本1</span></p>
        <p class="rp_btn" ><span  id="rp_restore" class="rp_restore"><img src="/tc/images/delete.png"/></span></p>
        <p class="rp_btn" ><span id="rp_discard" class="rp_discard"><img src="/tc/images/back_top.png" style="margin-bottom: 4px;"/></span></p>
        <p id="rp_name"  class="rp_name" style="float:left;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 200px;"><span >ubuntu14</span></p>
        <p><span id="register_time" class="register_time" style="float:left;">2021-02-24 15:12:58</span></p>
    </div>
</div>

<div style="display: none" id="list_item_rp">
    <a href="#" class="list-group-item">
        <div class="row">
            <div class="col-xs-9" style="word-wrap: break-word;">
                <h4 class="list-group-item-heading"></h4>
                <h4 class="list-group-item-text"></h4>
            </div>
            <div class="col-xs-3" style="padding: 0px;">
                <div class="btn-group pull-right" role="group" style="padding-right: 5px; display: none">
                    <button type="button" class="btn btn-default btn-link pull-right">
                        <span class="glyphicon glyphicon-refresh"></span> 
                    </button>
                     <button type="button" class="btn btn-default btn-link pull-right">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                </div>
            </div>
        </div>
    </a>
</div>

<div style="display: none" id="dialog_ps_group_add">
    <h4><span i18n="s_ps_group_add"></span></h4>
    <div class="form-group" style="margin-top:30px">
        <label><span i18n="s_group">Group</span> :</label>
        <select class="form-control" style="width:245px"></select>
    </div>
    <div class="form-group">
        <label><span i18n="s_ps_group_size">Personal Storage Size</span> :</label>
        <select class="form-control" style="width:245px"></select>
    </div>
</div>
<div style="display: none" id="dialog_acl_group_add">
    <div class="form-group">
        <label><span i18n="s_group">Group</span> :</label>
        <select class="form-control" style="width:245px"></select>
    </div>
</div>

<div style="display: none" id="list_item_ps">
    <a href="#" class="list-group-item">
        <div class="row">
            <div class="col-xs-9" style="word-wrap: break-word;">
                <h4 class="list-group-item-heading"></h4>
                <p class="list-group-item-text"></p>
            </div>
            <div class="col-xs-3">
                <div class="btn-group pull-right" role="group" style="padding-right: 5px; padding-top: 0px; display: none;">
                    <button type="button" class="btn btn-default btn-link" style="padding-top: 0px;">
                        <span class="glyphicon glyphicon-remove" style="margin-right: 3px;"></span>
                        <span i18n="s_delete">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </a>
</div>

<div style="display: none" id="list_item_acl">
    <a href="#" class="list-group-item">
        <div class="row">
            <div class="col-xs-9" style="word-wrap: break-word;">
                <h4 class="list-group-item-heading"></h4>
                <p class="list-group-item-text"></p>
            </div>
            <div class="col-xs-3">
                <div class="btn-group pull-right" role="group" style="padding-right: 5px; padding-top: 0px; display: none;">
                    <button type="button" class="btn btn-default btn-link" style="padding-top: 0px;">
                        <span class="glyphicon glyphicon-remove" style="margin-right: 3px;"></span>
                        <span i18n="s_delete">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </a>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0px;
    }
    .rp_list{
        border-radius: 4px;
        border: 1px solid #C6C6C6;
        padding: 12px;
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 400;
        color: #333333;
        margin-left:15px;
        height: 134px;
        background: #FEFEFE;
        box-shadow: 0px 1px 2px 0px rgba(213, 213, 213, 0.5);
        border-radius: 8px;
        border: 1px solid #BFBFBF;
    }
    .rp_list p{
        margin-bottom:20px;
    }
    .rp_btn{
        color: #5D9DFF;
        float: right;
    }
    hr{
        display: none;
    }
    .btn-default{
        border:0;
    }
    .table>tbody>tr>td{
        padding: 20px 8px 20px 0;
    }

    .about_title img{
        margin-left:0;
    }
    .about_title{
        width:160px;
    }
    .col-xs-6{
        padding-left:7px;
    }
    .col-xs-12{
        padding-left:7px;
    }
    .btn-group{
        margin-left:15px;
    }
    .list-group-item.current{
        border:0;
        background:#fff;
        padding:0;
    }
    .list-group-item:hover{
        background:#fff
    }
    .list-group-item h4{
        font-size: 16px;
    }
    small{
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 400;
        color: #333333;
        margin-left:30px;
    }
    .list-group-item-heading{
        margin-bottom:15px;
    }
    .bootbox-body .form-group label{
        min-width: 127px;
        margin-right:15px;
    }
    .table>tbody>tr>td{
        border-bottom:0;
    }
    .table>tbody>tr>.about_value{
        border-bottom: 1px solid #C2C2C2;
    }
    h3 span{
        font-size:18px;
    }
    #rp_discard{
        padding-right: 10px;
        border-right: 1px solid #DADADA;
        margin-right:10px;
    }
    /* .bootbox-body{
        padding:20px 20px 0 20px;
    } */
    .bootbox-body{
        text-align: center;
        color: #5D9DFF;
        font-size:16px;
    }
</style>


<?php standard_page_mid(); ?>

<script language="javascript">
//window.location.search.substr(1).split('=')

const urlParams = new URLSearchParams(window.location.search);
const iid = <?php echo $_SESSION["ciid"] ?>;
const debug_mode = urlParams.get('debug');
const client_image_url = "/tc/rest/bootimage2.php/" + iid;

var flags = {
    status_rest_error: false,
    prediction: []
};

var hide_buttons_list_items = function() {
    $('.list-group-item').hover(
        function() {
            $(this).find('.btn-group').css("display", "");
        },
        function() {
            $(this).find('.btn-group').css("display", "none");
        }
    );
}

var html_list_item = function(cg) {
    var html = $('#rp_list_item').html()
    return html;
};

var rp_update_list_item = function(img, $rpa, rp ,index) {
    var loadp = parseInt(rp.load_progress);
        heading = '';

    $rpa.data('rpid', rp.rpid).data('loadp', loadp);

    if (loadp < 100) {
        heading = find_i18n("s_rp_loading") + " " + loadp + "%";
    }

    var heading = find_i18n('s_version') + rp.revision;
    // $('#rp_version').text(heading);
    $(".rp_version:nth("+index+")").text(heading);
    // hide folder size per customer request
    if (debug_mode) {
        heading += ' (' + util_page.print_size(rp.folder_size, 'MiB') + ')';
    }

    $rpa.find(".list-group-item-heading").text(heading);

    var text = '<small>' + rp.timestamp + '</small>';
    // $('#rp_name').text(rp.name);
    // $('#register_time').text(rp.timestamp);
    $(".rp_name:nth("+index+")").text(rp.name);
    $(".register_time:nth("+index+")").text(rp.timestamp);

    $rpa.find(".list-group-item-text").text(rp.name).append(text);

    $rpa.toggleClass('current', img.revision === rp.revision);
};

var rp_create_list_item = function(img, rp, index) {
    var $rpa =$(html_list_item(rp)),
        rp_url = '/tc/rest/restore_point.php/' + rp.rpid;
        $rpa.data('rpid', rp.rpid)
        
        // $html = $('#rp_list_item').html()

    
    rp_update_list_item(img, $rpa, rp, index);

    $(html_list_item(rp))
    .find('#rp_discard').click(function(e) {
        restore_restore_point(rp.rpid);
    }).end()
    .find('#rp_restore').click(function(e) {
        delete_restore_point($rpa, rp_url);
    }).end()
    .data('rpid', rp.rpid)
    .click(function(e) {
        e.preventDefault()
    })
    .appendTo($('#rp_list'));
    // restore RP operation
    // $rpa.find('#rp_discard').click(function() {
    //     console.log($rpa.find('.rp_discard'));
    //     console.log(rp_url);
    //     console.log($(this).data('rpid'))
    //     restore_restore_point(rp.rpid);
    // });

    // // discard RP operation
    // $rpa.find('#rp_restore').click(function() {
    //     delete_restore_point($rpa, rp_url);
    // });
    // $rpa.find('#rp_discard').text(find_i18n('s_rp_restore'));
    // $rpa.find('#rp_restore').text(find_i18n('s_rp_discard'));
    // stop default scroll effect
    // $rpa.click(function(e) {
    //     e.preventDefault();
    // });
    // $rpa.prependTo($("#rp_list"));
    // $html.prependTo($("#rp_list"));

    hide_buttons_list_items();
}

var rp_fill_list = function(img) {
    util_page.rest_get(
        "/tc/rest/restore_point.php?iid=" + iid,
        function(rps) {
            // $("#rp_list").html('')
            $.map(rps, function(rp,index){
                var updated = false;

                $('#rp_list .list-rp-item').each(function() {
                    if (!updated && $(this).data('rpid') === rp.rpid) {
                        rp_update_list_item(img, $(this), rp,index);
                        updated = true;
                    }
                });
                if (!updated) {
                    rp_create_list_item(img, rp,index);
                }
            });
        }
    );
}

var show_dialog_update_client_image = function() {
    util_page.dialog_prompt_required(
        find_i18n('s_ci_update_reason'),
        function(reason) {
            var options = {
                    'reason': reason,
                    'merge': 'inline'
                };
            util_page.rest_post(
                '/tc/rest/task_merging.php/' + iid,
                options, 
                fn_done = function(){},
                fn_error = function(rest_error){
                    if (typeof rest_error === 'string') {
                        // not a standard REST error, maybe a PHP error
                        util_page.dialog_message(rest_error);
                    }
                    if (rest_error === undefined) {
                        util_page.dialog_message('Unknown error received from server');
                    }
                    if (rest_error.error == "c_restore_point_revision_max"){
                        var $rpa = $('#rp_list>a:last');
                        util_page.dialog_confirm(find_i18n('c_restore_point_revision_max'), function() {
                            var rp_min_url = '/tc/rest/restore_point.php/' + rest_error.extra.img_rev_min_id;
                            util_page.rest_delete(rp_min_url, {}, function() {
                                $rpa.fadeOut(300, function() {
                                    $rpa.remove();
                                });
                                util_page.rest_post('/tc/rest/task_merging.php/' + iid, options);
                            });
                        });
                    }
                }
            );
        }
    );
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_client_image_update')+'</span> :</label>')
    $form.append($client);
}

var get_client_image_revision_uuid = function(img, rev) {
    var uuid = '';
    $.map(img.history, function(r) {
        if (r.revision === rev) {
            uuid = r.uuid;
        }
    });
    return uuid;
}

var fill_client_image_information = function(img) {
    var file_size = '',
        total_size = '',
        uuid = get_client_image_revision_uuid(img, img.revision);
    if (img.file_size) {
        file_size = util_page.print_size(img.file_size, 'MiB');
    }
    if (img.total_size) {
        total_size = util_page.print_size(img.total_size, 'MiB');
    }
    if (img.apparent_size) {
        apparent_size = util_page.print_size(img.apparent_size, 'MiB');
    }

    $('#table_ci_info').data('uuid', uuid).data('revision', img.revision);
    $('#table_ci_info td')
        .eq(1).text(img.revision).end()
        .eq(3).text(file_size).end()
        .eq(5).text(img.register_time).end()
        .eq(7).text(uuid);

    $('#table_right_info td')
    .eq(1).text(img.ostype).end()
    .eq(3).text(apparent_size).end()
    .eq(5).text(img.file_mtime).end()

    var $title = $('#main-area h3:nth(0)');
    // clear previous data
    $title.find(".tc-volatile").remove();

    // fill with latest information
    $('<span></span>')
        .css("margin-left", "0.5em")
        .addClass("tc-volatile")
        .text(img.name)
        .appendTo($title);
    $('<small></small>')
        .css("margin-left", "0.5em")
        .addClass("tc-volatile")
        .text(img.description)
        .appendTo($title);

    // status in the top right
    var fs_dict = {
        ready: "cis_file_ready",
        merging: "cis_file_merging",
        pending: "cis_file_pending",
        preparing: "cis_file_preparing"
    },
    fs_text = img.status;
    if (img.status in fs_dict) {
        fs_text = find_i18n(fs_dict[img.status], fs_text);
    }
    $title.css('background-color', '');
    var $cis_label = $('<span></span>');
    if (img.status === "pending") {
        $cis_label = $('<a></a>')
            .attr('href', '#')
            .click(show_dialog_update_client_image);
        $title.css('background-color', '#d9edf7');
    } else if (img.status === "preparing" && img.merging_progress) {
        fs_text += ' ('+img.merging_progress+'%)';
        $title.css('background-color', '#fff');
    } else if (img.status === "merging" && img.merging_progress) {
        fs_text += ' ('+img.merging_progress+'%)';
        $title.css('background-color', '#fff');
    }
    $cis_label
        .css("margin-right", "0.5em")
        .addClass("pull-right")
        .addClass("tc-volatile")
        .text(fs_text)
        .appendTo($title);

    if (img.status == "ready") {
        var ss_dict = {
            active: "cis_seed_active",
            hashing: "cis_seed_hashing",
            error: "cis_seed_error",
            raw: "cis_seed_raw",
            missing: "cis_seed_missing"
        },
        ss_text = img.seed_status;
        if (img.seed_status in ss_dict) {
            ss_text = find_i18n(ss_dict[img.seed_status], ss_text);
        }

        $('<span></span>')
            .css("margin-right", "0.5em")
            .addClass("pull-right")
            .addClass("tc-volatile")
            .text(ss_text)
            .appendTo($title);
    }

    $('#toolbar_control').data('image_status', img.status);
}

// dialog for add personal storage group settings
var ps_add_group_dialog = function(groups, sizes) {
    save_btn_text = find_i18n('s_ok');
    cancel_btn_text = find_i18n('s_cancel');
    var $d = util_page.dialog_confirm_builder($('#dialog_ps_group_add').html(), save_btn_text,cancel_btn_text, function(){
        var prediction = util_page.array_filter(flags.prediction, function(g){
            return (g.gid == $d.find('select:nth(0)').val()) 
                && (g.ps_size != $d.find('select:nth(1)').val());
        });
        if (prediction.length > 0) {
            util_page.dialog_message(find_i18n("e_ps_size_predicted") + " " + util_page.print_size(prediction[0].ps_size, 'MiB'));
            return false;
        }
        util_page.rest_post(
            client_image_url + '/ps', 
            {
                group: $d.find('select:nth(0)').val(),
                size: $d.find('select:nth(1)').val()
            },
            function() {
                refresh_ps_group_list();
            }
        );
    });
    $.map(groups, function(g){
        $('<option></option')
            .attr('value', g.id)
            .text(g.name)
            .appendTo($d.find('select:nth(0)'));
    });
    var selected_value = sizes[0];
    $.map(sizes.slice(1), function(s){
        $('<option></option')
            .attr('value', s)
            .attr('selected', s === selected_value)
            .text(util_page.print_size(s, 'MiB'))
            .appendTo($d.find('select:nth(1)'));
    });
}

var ps_add_group = function() {
    function ps_group_not_exist(g) {
        var exist = false;
        $('#ps_group_list a').each(function(){
            if ($(this).data('gid') == g.id) {
                exist = true;
            }
        });
        return !exist;
    }

    util_page.rest_get('/tc/rest/group.php', function(groups) {
        var groups = util_page.array_filter(groups, ps_group_not_exist);

        util_page.rest_get('/tc/rest/system.php/settings', function(settings) {
            ps_add_group_dialog(groups, settings.ps_sizes.split(','));
        });
    });
}

var refresh_ps_group_list = function() {
    util_page.rest_get(client_image_url, function(img){
        $('#ps_group_list').empty();
        fill_ps_group_list(img);
    });
}

// fill personal storage group list
var fill_ps_group_list = function(img) {
    $.map(img.ps_group, function(g) {
        var $rpa = $('#list_item_ps a').clone();
        $rpa.data('gid', g.gid);
        $rpa.find(".list-group-item-heading").text(g.name);
        $rpa.find(".list-group-item-text").text(util_page.print_size(g.ps_size, 'MiB'));
        $rpa.find("button:nth(0)").click(function() {
            util_page.rest_delete(
                client_image_url + '/ps',
                {gid: g.gid},
                refresh_ps_group_list
            );
        });
        $rpa.appendTo($('#ps_group_list'));
    });
    flags.prediction = img.ps_group_predicted;
    hide_buttons_list_items();
}

var acl_add_group = function() {
    function acl_group_not_exist(g) {
        var exist = false;
        $('#acl_group_list a').each(function(){
            if ($(this).data('gid') == g.id) {
                exist = true;
            }
        });
        return !exist;
    }

    util_page.rest_get('/tc/rest/group.php', function(groups) {
        var groups = util_page.array_filter(groups, acl_group_not_exist),
            groups_select = [{text: find_i18n('s_select_user_group', "Select a user group"), value: ''}];
        $.map(groups, function(g) {
            groups_select.push({
                text: g.name,
                value: g.id
            });
        });

        util_page.dialog_select(
            find_i18n("s_bootimage_acl_add"),
            groups_select,
            function(select) {
                if (select === "") {
                    util_page.dialog_message_i18n('e_no_group_selected');
                    return;
                }
                util_page.rest_post(
                    client_image_url + '/acl',
                    {group: select},
                    refresh_acl_group_list
                );
            }
        );
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_bootimage_acl')+'</span> :</label>')
        $form.append($client);
    });
}

var refresh_acl_group_list = function() {
    util_page.rest_get(client_image_url, function(img){
        $('#acl_group_list').empty();
        fill_acl_group_list(img);
    });
}

var fill_acl_group_list = function(img) {
    $.map(img.group_read, function(g) {
        var $rpa = $('#list_item_acl a').clone();
        $rpa.data('gid', g.gid);
        $rpa.find(".list-group-item-heading").text(g.name);
        $rpa.find(".list-group-item-text").text(g.desc);
        $rpa.find("button:nth(0)").click(function() {
            util_page.rest_delete(
                client_image_url + '/acl',
                {gid: g.gid},
                refresh_acl_group_list
            );
        });
        $rpa.appendTo($('#acl_group_list'));
    });

    hide_buttons_list_items();
}

var load_page = function() {
    if (flags.status_rest_error) {
        return;
    }

    util_page.rest_get(client_image_url,
        function(img) {
            fill_client_image_information(img);
            rp_fill_list(img);
        },
        function(error) {
            util_page.dialog_message_error(error);
            flags.status_rest_error = true;
        }
    );

    setTimeout(load_page, 3000);
};

var update_client_image_file = function() {
    if ($('#toolbar_control').data('image_status') === 'pending') {
        show_dialog_update_client_image();
    } else {
        util_page.dialog_message_i18n('s_client_image_pending_uploaded');
    }
}

var delete_client_image_pending = function() {
    util_page.rest_delete(client_image_url + '/pending', {}, function() {
        util_page.dialog_message(find_i18n('s_operation_complete'), util_page.page_refresh);
    });
}

var update_client_image_revision_uuid = function() {
    util_page.dialog_prompt_required('UUID', function(v) {
        util_page.rest_put(
            client_image_url + '/' + $('#table_ci_info').data('revision'),
            {uuid: v},
            function() {
                $('#table_ci_info td:nth(15)').text(v);
                $('#table_ci_info').data('uuid', v);
            }
        );
    }).find('input').val($('#table_ci_info').data('uuid'));
}

var restore_restore_point = function(rpa) {
    
    var rp_url = '/tc/rest/restore_point.php/' + rpa;
    
    util_page.dialog_confirm(find_i18n('c_discard_tips'), function() {
        
        util_page.rest_post(rp_url, {}, function(){}, fn_error = function(rest_error){
            if (typeof rest_error === 'string') {
                // not a standard REST error, maybe a PHP error
                util_page.dialog_message(rest_error);
            }
            if (rest_error === undefined) {
                util_page.dialog_message('Unknown error received from server');
            }
            if (rest_error.error == "c_restore_point_revision_max"){
                var $rpa = $('#rp_list>a:last');
                var rp_url = '/tc/rest/restore_point.php/' + rest_error.extra.rpid;
                util_page.dialog_confirm(find_i18n('c_restore_point_revision_max'), function() {
                    var rp_min_url = '/tc/rest/restore_point.php/' + rest_error.extra.img_rev_min_id;
                    var options = {};
                    util_page.rest_delete(rp_min_url, options, function() {
                        $rpa.fadeOut(300, function() {
                            $rpa.remove();
                        });
                        util_page.rest_post(rp_url, {});
                    });
                });
            } else {
                util_page.dialog_message(find_i18n(rest_error.error));
            }
        });
    });
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:14px;margin-right: 5px;">'+find_i18n('c_delete_tips')+'</div>')
    $form.append($client);
}

var delete_restore_point = function($rpa, rp_url) {
    util_page.dialog_confirm(find_i18n('c_restore_point_delete'), function() {
        var options = {};
        util_page.rest_delete(rp_url, options, function() {
            $rpa.fadeOut(300, function() {
                $rpa.remove();
            });
            util_page.dialog_message(find_i18n('s_operation_complete'), util_page.page_refresh);
        });
    });
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:14px;margin-right: 21px;">'+find_i18n('c_delete_tips')+'</div>')
    $form.append($client);
    
}

$(document).ready( function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    // load group lists without auto refresh
    // because group list will be changed after editing
    util_page.rest_get(client_image_url, 
        function(img) {
            fill_ps_group_list(img);
            fill_acl_group_list(img);
        },
        function(error) {
            util_page.dialog_message_error(error);
            flags.status_rest_error = true;
        }
    );

    load_page();

    $('#tb_group_ps button:nth(0)').click(ps_add_group);
    $('#tb_group_gr button:nth(0)').click(acl_add_group);

    $('#toolbar_control button:nth(0)').click(update_client_image_file);
    $('#toolbar_control button:nth(1)').click(delete_client_image_pending);

    $('#table_ci_info td:nth(14)').click(update_client_image_revision_uuid);

    if (debug_mode) {
        $('#table_ci_info tr:nth(4)').removeClass('hidden');
    }

});

</script>

<?php standard_page_end(); ?>