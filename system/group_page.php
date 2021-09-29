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

    standard_page_begin('all_groups');
?>

<div class="container-fluid container_table">
<!-- toolbar beginning -->
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_group_add", "icon" => "glyphicon-plus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_group_remove", "icon" => "glyphicon-minus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_group_edit_name", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                     array("i18n" => "s_group_edit_desc", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_group_edit_ps_upload", "icon" => "glyphicon-edit"),
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
<!-- toolbar ends -->
<div class="row row_div">
    <div class="col-xs-12">
        <table class="table tc-table table_css table_list" id="table_user_group_list">
            <thead>
                <tr style="cursor: pointer;">
                    <th> <input type="checkbox"> </th>
                    <th> <span i18n="s_name"> Name </span> </th>
                    <th> <span i18n="s_description"> Description </span> </th>
                    <th> <span i18n="s_create_time"> Create Time </span> </th>
                    <th> <span i18n="s_member_count"> Member Count </span> </th>
                    <th> <span i18n="s_ps_background_upload_short"> </span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
</div>

<div style="display: none">
    <div id="dialog_register_group" class="dialog_model">
        <h4><span i18n="s_group_add"></span></h4>
        <div class="form-group" style="margin-top:30px">
            <div class="control-label">
                <label for="name-input"><span i18n="s_name">Name</span> <span class="required_tips">*</span> :</label>
                <input type="text" class="form-control" id="name-input" maxlength="16" style="margin-left:20px">
            </div>
        </div>

        <div class="form-group">
            <div class="control-label">
                <label for="desc-input"><span i18n="s_description">Description</span> <span class="required_tips"></span> :</label>
                <input type="text" class="form-control" id="desc-input" maxlength="40" style="margin-left:20px">
            </div>
        </div>
        <small>
            <span i18n="s_asterisk_required">* is required</span>
        </small>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
    .form-control{
        margin-left:20px
    }
    .form-group label{
        min-width: 90px;
        text-align: right;
    }
    small{
        margin-left: -46px;
        color: #999;
    }
</style>

<?php control_pagination(); ?>

<?php standard_page_mid(); ?>

<script language="javascript">

$(document).ready(function(){

    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    create_table();
});

function create_table() {
    util_table.create($('.tc-table'));
    util_table.enable_search($('#search_control input'), $('#search_control button'));
    util_table.enable_pagination($('#page-control'));
    util_table.enable_sort();
    util_table.bind_sort(4, util_table.integer_comparator);
    // button list: create, delete, batch enabling, batch disabling, change group, change password
    // $("#table_user_group_list thead tr th input").css("display",'none');
    util_table.bind_checked(function($trs){
        if ($trs.length == 0) {
            $('#crud_control button:nth(1)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_empty'));

            $('#crud_control button:nth(2)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_empty'));
            $('#crud_control button:nth(3)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_empty'));
            $('#crud_control button:nth(4)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_empty'));
        } else if ($trs.length == 1) {
            $('#crud_control button:nth(1)')
                .prop('disabled', !$trs[0].deletable)
                .attr('title', find_i18n('tp_group_disabled_delete'));
            $('#crud_control button:nth(2)').prop('disabled', false);
            $('#crud_control button:nth(3)').prop('disabled', false);
            $('#crud_control button:nth(4)').prop('disabled', false);
        } else if ($trs.length > 1) {
            var deletable = $trs[0].deletable;
            $.map($trs, function(tr) {
                deletable &= tr.deletable;
            });
            $('#crud_control button:nth(1)')
                .prop('disabled', !deletable)
                .attr('title', find_i18n('tp_group_disabled_delete'));

            $('#crud_control button:nth(2)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_multiple'));
            $('#crud_control button:nth(3)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_multiple'));
            $('#crud_control button:nth(4)')
                .prop('disabled', true)
                .attr('title', find_i18n('tp_group_selected_multiple'));
        }
    });

    load_table();

    // bind button handlers
    // create
    // $('#crud_control button:nth(0)').click(function(){
    //     util_page.navi_page('group_create_page.php');
    // });
    $('#crud_control button:nth(0)').click(registration_groups)

    init_buttons_status();
    // delete
    $('#crud_control button:nth(1)').click(delete_groups);
    // batch enabling
    $('#crud_control button:nth(2)').click(change_name);
    $('#crud_control button:nth(3)').click(change_desc);
    $('#crud_control button:nth(4)').click(change_permit);
    $('#check_edit').click(function(){
        var $control_edit = $("#table_user_group_list thead tr th input").css("display");
        
        if($control_edit == 'block'){
            $("#table_user_group_list thead tr th input").css("display",'none');
            $("#table_user_group_list tbody input").css("display",'none');
        }else{
            $("#table_user_group_list thead tr th input").css("display","block");
            $("#table_user_group_list tbody input").css("display",'block');
        }
    });
}

var init_buttons_status = function() {
    $('#crud_control button:nth(1)').prop('disabled', true);
    $('#crud_control button:nth(2)').prop('disabled', true);
    $('#crud_control button:nth(3)').prop('disabled', true);
    $('#crud_control button:nth(4)').prop('disabled', true);
    // $("#table_user_group_list thead tr th input").css("display",'none');
};

var change_name = function() {
    var title = find_i18n('s_input_new_group_name');
    var $dialog = util_page.dialog_prompt_required(title, function(name) {
        change_group({name: name});
    });
    $dialog.find('input').attr('maxlength', '16');
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_input_group_name')+'</span> :</label>')
    $form.append($client);
};

var change_desc = function() {
    var title = find_i18n('s_input_new_group_desc');
    var $dialog = util_page.dialog_prompt_required(title, function(desc) {
        change_group({desc: desc});
    });
    $dialog.find('input').attr('maxlength', '40');
    $dialog.find('input').attr('maxlength', '16');
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_description')+'</span> :</label>')
    $form.append($client);
};

var change_permit = function() { 
    var selections = [
        {text: find_i18n('s_general_global'), value: -1},
        {text: find_i18n('s_general_disabled'), value: 0},
        {text: find_i18n('s_general_enabled'), value: 1},
    ];
    util_page.dialog_select(
        find_i18n('s_ps_background_upload'),
        selections,
        function(val) {
            change_group({ps_background_upload: val});
        }
    );
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_ps_group_data')+'</span> :</label>')
    $form.append($client);
};

var change_group = function(row_data) {
    var bags = util_table.checked();
    var urls = $.map(bags, function(bag){
        return '/tc/rest/group.php/' + bag.key;
    });

    util_page.rest_put(urls, row_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_table();
    });
};
var registration_groups = function() {
    var $dialog = util_page.dialog_confirm_builder(
        $('#dialog_register_group').html(),
        find_i18n('s_save'),
        find_i18n('s_cancel'),
        function() {
            do_create_group(
                {
                    'name': $dialog.find('#name-input').val(),
                    'desc': $dialog.find('#desc-input').val()
                }
            );
        }
    );
}


var delete_groups = function() {
    var bags = util_table.checked(),
        message = find_i18n('c_delete_group');

    util_page.dialog_confirm(message, function() {
        var urls = $.map(bags, function(bag){
            return '/tc/rest/group.php/' + bag.key;
        });

        util_page.rest_delete(urls, null, function() {
            load_table();
        });
    });
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
    $form.append($client);
};

var do_create_group = function(data) {
    // var name = $('#name-input').val()
    // var des = $('#name-input').val()
    // var data = {
    //     'name': $.trim($('#name-input').val()),
    //     'desc': $.trim($('#desc-input').val())
    // };
    util_page.rest_post('/tc/rest/group.php', data, navi_group_list, function(rest_error){
        util_page.dialog_message_error(rest_error);
    });
};

var navi_group_list = function() {
    util_page.navi_page('group_page.php');
}

function load_table() {
    util_page.rest_get('/tc/rest/group.php?embed=bootimage', function(result){
        util_table.load(parse_record(result));
    });
    init_buttons_status();
}

function parse_record(result) {
    var records = [],
        str_dict = {
            '1': find_i18n('s_general_enabled'),
            '0': find_i18n('s_general_disabled'),
            '-1': find_i18n('s_general_global')
        };

    $.map(result, function(r) {
        var title = '';
        if (r.bootimage) {
            title = find_i18n('s_available_bootimage_list', 'Available Bootimage List: ') + r.bootimage.join(', ');
        }

        var str_ps_bg_upload = str_dict[Number(r.ps_background_upload)];
        if (r.ps_background_upload === '-1') {
            str_ps_bg_upload += '(' + str_dict[r.ps_background_upload_global] + ')';
        }

        records.push({
            key: r.id,
            row: [r.name, r.desc, r.create_time, r.member_count, str_ps_bg_upload],
            checkable: true,
            deletable: (r.member_count == 0),
            title: title
        });
    });
    return records;
}

</script>

<?php standard_page_end(); ?>