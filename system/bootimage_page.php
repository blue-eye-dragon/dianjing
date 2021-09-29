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

    standard_page_begin('all_images');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
    <div class="row row_div" style="margin-bottom: 10px;">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_image_add", "icon" => "glyphicon-user"),
                    array("i18n" => "s_client_image_remove", "icon" => "glyphicon-trash"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_image_edit", "icon" => "glyphicon-edit"),
                ),
            ),
        ),
        "search_control" => array(
            "class" => "pull-right",
            "id" => "search_control",
        ),
        "id" => "crud_control",
    );
    echo html_toolbar($bar);
?>
    </div>

    <div class="row row_div" style="margin-top: 10px;">
        <table class="table table-hover tc-table col-xs-12 table_list" id="table_bootimage_list">
            <thead>
                <tr>
                    <th> <input type="checkbox"> </th>
                    <th style="min-width:55px"> <span i18n="s_image_name"> Name </span> </th>
                    <th style="min-width:55px"> <span i18n="s_image_desc"> Description </span> </th>
                    <th style="min-width:55px"> <span i18n="s_revision"> Revision </span> </th>
                    <th> <span i18n="s_image_file"> Path </span> </th>
                    <th> <span i18n="s_last_update"> Last Update </span> </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <p class="pull-right">
        <label class="legend" style="background-color: #fff;color:#333;font-size:16px">
            <span i18n="cis_file_pending"></span>
        </label>
        <label class="legend" style="background-color: #fff;color:#333;font-size:16px">
            <span i18n="cis_file_merging"></span>
        </label>
    </p>
</div>

<div id="dialog_image_edit" hidden>
    <h4><span i18n="s_client_image_edit"></span></h4>
    <div class="form-group" style="margin-top:30px">
        <label><span i18n="s_image_name">Name</span> : </label>
        <input type="text" class="form-control" id="image_name"  maxlength="30">
    </div>
    <div class="form-group">
        <label><span i18n="s_image_desc">Description</span> : </label>
        <input type="text" class="form-control" id="image_desc" maxlength="40">
    </div>
    <div class="form-group">
        <label><span i18n="s_image_ag">Authorized Group</span> : </label>
    </div>
    <div class="form-group" style="height: 90px;">
        <label><span i18n="s_bootimage_ga" style="float:left;">Accessible</span> :</label> 
        <select multiple class="form-control" id="image_rgids" style="width: 245px;float: left;margin-left:20px">
        </select>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
    }
    .bootbox-body .form-group input{
        margin-left: 20px;
    }
    /* .modal-body{
        height:375px;
    } */
    .modal-footer .btn-default{
        margin-right:10px
    }
    .modal-footer .btn-primary{
        margin-right:10px
    }
    .bootbox-body .form-group label{
        margin-left:35px
    }
</style>


<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var uid = <?php echo $_SESSION["uid"] ?>;
var checkShow = false;

function edit_image(img, groups) {
    var key = util_table.checked()[0].key;

    var dialog = util_page.dialog_confirm_builder(
        $('#dialog_image_edit').html(),
        find_i18n('s_edit', 'Edit'),
        find_i18n('s_cancel', 'Cancel'),
        function() {
            var name = dialog.find('#image_name').val();
            var desc = dialog.find('#image_desc').val();
            var client_image_url = '/tc/rest/bootimage2.php/' + key,
                changes = {};

            if (img.name != name) {
                changes['name'] = name;
            }
            if (img.description != desc) {
                changes['desc'] = desc;
            }

            var rgid = [];
            dialog.find('#image_rgids option').filter(':selected').each(function(){
                rgid.push($(this).val());
            });

            changes['r_gids'] = rgid;

            util_page.rest_put(client_image_url, changes, function() {
                load_table();
            });
        }
    );

    dialog.find('#image_name').val(img.name);
    dialog.find('#image_desc').val(img.description);

    var rgids = $.map(img.group_read, function(g) {
        return g.gid;
    });
    $.map(groups, function(group){
        var $opt = $('<option></option>').val(group.id).text(group.name);
        if ($.inArray(group.id, rgids) >= 0) {
            $opt.prop('selected', true);
        }
        dialog.find('#image_rgids').append($opt);
    });

    dialog.modal('show');
}

var on_client_image_edit = function() {
    var selected = util_table.checked();
    if (selected.length === 0) {
        util_page.dialog_message_i18n('e_no_selected_image');
        return;
    }
    if (selected.length > 1) {
        util_page.dialog_message_i18n('e_max_selected_image');
        return;
    }

    var url = '/tc/rest/bootimage2.php/' + selected[0].key;
    util_page.rest_get(
        '/tc/rest/group.php',
        function(groups) {
            util_page.rest_get(url, function(img) {
                edit_image(img, groups);
            });
        }
    );
};

function request_delete(bags, purge) {
    $.map(bags, function(bag){
        util_page.rest_delete(
            '/tc/rest/bootimage2.php/' + bag.key,
            {purge: purge},
            function(){
                load_table();
            },
            util_page.dialog_message_error);
    });
}

var on_client_image_delete = function() {
    var bags = util_table.checked();

    if(bags.length === 0) {
        util_page.dialog_message_i18n('e_no_selected_image');
        return;
    }

    bootbox.dialog({
        message: find_i18n('c_delete_image', 'Delete selected images?'),
        buttons: {
            purge: {
                label: find_i18n('s_delete_image_files', 'Delete record and files'),
                className: 'btn-default',
                callback: function() {
                    request_delete(bags, true);
                }
            },
            confirm: {
                label: find_i18n('s_delete_image', 'Delete record'),
                className: 'btn-primary btn-confirm',
                callback: function() {
                    request_delete(bags, false);
                }
            },
            cancel: {
                label: find_i18n('s_cancel', 'Cancel'),
                callback: function() {
                }
            }
        }
    });

    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
    $form.append($client);

};

var on_client_image_create = function() {
    util_page.navi_page('bootimage_create_page.php');
};

$(document).ready(function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_table.create($('.tc-table'));
    util_table.enable_search($('#search_control input'), $('#search_control button'));
    util_table.enable_sort();
    util_table.bind_sort(3, util_table.integer_comparator);


    load_table();
    init_buttons_status();
    util_table.bind_checked(function($trs) {
        if ($trs.length == 0) {
            $('#crud_control button:nth(1)').prop('disabled', true);
            $('#crud_control button:nth(2)').prop('disabled', true);
        } else if ($trs.length == 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            $('#crud_control button:nth(2)').prop('disabled', false);
        } else if ($trs.length > 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            $('#crud_control button:nth(2)').prop('disabled', true);
        }
    });
    $('#crud_control button:nth(0)').click(on_client_image_create);
    $('#crud_control button:nth(1)').click(on_client_image_delete);
    $('#crud_control button:nth(2)').click(on_client_image_edit);
    // $("#table_bootimage_list thead tr th input").css("display",'none');
    
    var $control_edit_1 =$("#table_bootimage_list tbody input").css("display");
    $('#check_edit').click(function(){
        var $control_edit = $("#table_bootimage_list thead tr th input").css("display");
        
        if(!checkShow){
            $("#table_bootimage_list thead tr th input").css("display","block");
            $("#table_bootimage_list tbody input").css("display",'block');
        }else{
            $("#table_bootimage_list thead tr th input").css("display",'none');
            $("#table_bootimage_list tbody input").css("display",'none');
        }
        checkShow = !checkShow;
    });
});

var init_buttons_status = function() {
    $('#crud_control button:nth(1)').prop('disabled', true);
    $('#crud_control button:nth(2)').prop('disabled', true);
};

function load_table() {
    var bags = util_table.checked();

    util_page.rest_get(
        '/tc/rest/bootimage2.php',
        function(imgs) {
            var $control_edit = $("#table_bootimage_list tbody input").css("display");
            util_table.load(parse_record(imgs), function() {
                append_merge(imgs);
                disable_running(imgs);
                create_details_link();
                // if(checkShow){
                //     $("#table_bootimage_list tbody input").css("display",'block');
                // }else{
                //     $("#table_bootimage_list tbody input").css("display",'none');
                // }
            });
            util_table.checked(bags);
        }
    );

    // setTimeout(function() {
    //     load_table();
    // }, 10000);
}

function append_merge(record) {
    var $control_edit = $("#table_bootimage_list tbody input").css("display");
    var status_map = {};
    $.map(record.images, function(r) {
        status_map[r.id] = r.status;
    });
    
    $('.tc-table tbody tr').each(function() {
        var key = $(this).data('key');
        if (status_map[key] == 'pending') {
            var last = $(this).find('td').last();
            if (last) {
                last.append($('<div></div>').text(find_i18n('s_update', 'Update')));
            }
            $(this).addClass('info');
        } else if (status_map[key] == 'merging') {
            var last = $(this).find('td').last();
            if (last) {
                last.append($('<div></div>').text(find_i18n('s_updating', 'Updating')));
            }
            $(this).addClass('warning');
        }
    });
}

function disable_running(record) {
    var disabled = {};
    $.map(record.images, function(r) {
        if (r.status == 'merging') {
            // image is merging with UDF/MDF
            disabled[r.id] = true;
        } else {
            disabled[r.id] = false;
        }
    });

    $('.tc-table tbody tr').each(function() {
        var key = $(this).data('key');
        // client > 0, means in using
        $(this).find(':checkbox').prop('disabled', disabled[key]);
        if ($(this).find(':checkbox').prop('disabled')) {
            $(this).prop('title', find_i18n('s_bootimage_inuse'));
        }
    });
}
$('.tc-table').mouseenter(create_details_link);
function create_details_link() {
    $('.tc-table tbody tr').each(function() {
        var key = $(this).data('key');
        var bootimage_name = $(this).data('bag').row[0];
        var createClickHandler = function(row) {
            return function() {
                localStorage.setItem("bootimage_name",bootimage_name);
                // var historyUrl = window.location.href.split(window.location.host)[1];
                // localStorage.setItem("historyUrl",historyUrl); 
                util_page.rest_put('/tc/rest/user.php/' + uid, {ciid: key}, function() {
                    util_page.navi_page('client_image.php');
                });
            };
        };

        $(this).find('th,td').each(function (colIndex, c) {
            if(colIndex != 0) {
                this.onclick = createClickHandler(this);
            }
        });
    });
}

function parse_record(record) {
    var records = [];
    $.map(record.images, function(r) {
        var last = r.history[r.history.length - 1];
        var row = [r.name, r.description, last.revision, last.path, last.timestamp, ''];
        records.push({
            key: r.id,
            row: row,
            checkable: !(r.status == 'merging')
        });
    });
    return records;
}

</script>

<?php standard_page_end(); ?>