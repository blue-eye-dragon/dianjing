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

    standard_page_begin('client_full_table');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
    <div class="row row_div" style="margin-bottom: 10px;">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_add", "icon" => "glyphicon-plus"),
                ),
            ),
            array(
                "buttons" => array(
                     array("i18n" => "s_client_remove", "icon" => "glyphicon-minus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_export", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_list", "icon" => "glyphicon-menu-down"),
                ),
            ),
        ),
        "search_control" => array(
            "class" => "pull-right",
            "id" => "search_control",
        ),
        "id" => "crud_control",
    );
    $bar2 = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_name", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_group", "icon" => "glyphicon-edit"),
                 ),
            ),
            // array(
            //     "buttons" => array(
            //         array("i18n" => "s_client_edit_mac", "icon" => "glyphicon-edit"),
            //      ),
            // ),
            // array(
            //     "buttons" =>  array(
            //         array("i18n" => "s_client_edit_limit", "icon" => "glyphicon-edit"),
            //     ),
            // ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_usb_storage", "icon" => "glyphicon-edit"),
                 ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_resolution", "icon" => "glyphicon-edit"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_client_edit_desc", "icon" => "glyphicon-edit"),
                ),
            ),
        ),
        "id" => "crud_control_2",
    );
    echo html_toolbar($bar);
    echo html_toolbar1($bar2);
?>
    </div>

    <div class="row row_div" style="margin-top: 10px;margin-bottom: 20px;">
        <table class="table tc-table col-xs-12" id="table_client_list">
            <thead>
                <tr>
                    <th> <input type="checkbox"> </th>
                    <th > <span i18n="s_name"> Name </span> </th>
                    <th > <span i18n="s_client_group"> Group </span> </th>
                    <th style="min-width:100px"> <span i18n="s_mac_address"> MAC Address </span> </th>
                    <th> CPU </th>
                    <th style="min-width:55px"> <span i18n="s_memory_size"> Memory Size </span> </th>
                    <th style="min-width:55px"> <span i18n="s_disk_size"> Disk Size </span> </th>
                    <th style="min-width:55px"> <span i18n="s_speed_limit_download_title"></span> </th>
                    <th style="min-width:55px"> <span i18n="s_speed_limit_upload_title"></span> </th>
                    <th style="min-width:55px"> <span i18n="s_usb_storage"></span> </th>
                    <th style="min-width:85px"> <span i18n="s_display_resolution"></span> </th>
                    <th> <span i18n="s_description"> Description </span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

<div style="display: none">
    <div id="dialog_edit_speed_limit">
        <h4><span i18n="s_change_limit"></span></h4>
        <div class="form-group" style="margin-top:30px">
            <label class="model_label"><span i18n="s_speed_limit_download_title">
                Download Speed Limit
            </span> :</label>
            <input type="text" class="form-control" value="0"  placeholder="MB/s, 0 means no limit">
        </div>
        <div class="form-group">
            <label class="model_label"><span i18n="s_speed_limit_upload_title">
                Upload Speed Limit
            </span> :</label>
            <input type="text" class="form-control" value="0"  placeholder="MB/s, 0 means no limit">
        </div>
    </div>
</div>

<div style="display: none">
    <div id="dialog_edit_name" class="dialog_edit_name">
        <h4><span i18n="s_edit_name"></span></h4>
        <div class="form-group">
            <label>
                <span i18n="s_terminal_name"></span>
            </label>
            <input type="text" class="form-control" plavalue="0" id="edit_name">
            <p class='name_tips'>
                <span i18n="s_clients_name_tips"></span>
            </p>
            
        </div>
    </div>
</div>

<div style="display: none">
    <div id="dialog_edit_group" class="dialog_edit_group">
        <h4><span i18n="s_change_group"></span></h4>
        <div class="form-group">
            <label>
                <span i18n="s_terminal_name"></span>
            </label>
            <input type="text" class="form-control" plavalue="0" id="edit_name">
            <p class='name_tips'>
                <span i18n="s_clients_name_tips"></span>
            </p>
            
        </div>
    </div>
</div>

<div style="display: none">
    <div id="dialog_register_client" class="dialog_model">
        <h4><span i18n="s_client_register_client"></span></h4>
        <div class="form-group">
            <div class="model_div model_first_div">
                <label class="model_label" for="name-input">
                    <span i18n="s_struc_name">name:</span> :
                </label>
                <input type="text" class="form-control" plavalue="0" id="name-input">
            </div>
        </div>
        <div class="form-group">
            <div class="model_div ">
                <label class="model_label">
                    <span>MAC</span> :
                </label>
                <input type="text" class="form-control" plavalue="0" id="mac-input">
            </div>
        </div>
        <div class="form-group">
            <div class="model_div">
                <label class="model_label">
                    <span i18n="s_description">description</span> :
                </label>
                <input type="text" class="form-control" plavalue="0" id="desc-input">
            </div>
        </div>
    </div>
</div>

<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
    .model_label{
        width: 25%;
    }
</style>

</div>
<?php control_pagination(); ?>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var uid = <?php echo $_SESSION["uid"] ?>;

function page_load() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_table.create($('.tc-table'));
    util_table.enable_search($('#search_control input'), $('#search_control button'));
    util_table.enable_pagination($('#page-control'));
    util_table.enable_sort();
    util_table.bind_sort(4, util_table.size_comparator);
    util_table.bind_sort(5, util_table.size_comparator);
    // button list: create, delete, batch enabling, batch disabling, change group, change password
    $("#crud_control_2").css("display",'none');
    // $("#table_client_list thead tr th input").css("display",'none');
    util_table.bind_checked(function($trs) {
        if ($trs.length == 0) {
            $('#crud_control button:nth(1)').prop('disabled', true);
            // $('#crud_control button:nth(2)').prop('disabled', true);
           // $('#crud_control button:nth(3)').prop('disabled', true);
            $('#crud_control_2 button:nth(0)').prop('disabled', true);
            $('#crud_control_2 button:nth(1)').prop('disabled', true);
            $('#crud_control_2 button:nth(2)').prop('disabled', true);
            $('#crud_control_2 button:nth(3)').prop('disabled', true);
            $('#crud_control_2 button:nth(4)').prop('disabled', true);
            $('#crud_control_2 button:nth(5)').prop('disabled', true);
            $('#crud_control_2 button:nth(6)').prop('disabled', true);
        } else if ($trs.length == 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            // $('#crud_control button:nth(2)').prop('disabled', false);
           // $('#crud_control button:nth(3)').prop('disabled', false);
            $('#crud_control_2 button:nth(0)').prop('disabled', false);
            $('#crud_control_2 button:nth(1)').prop('disabled', false);
            $('#crud_control_2 button:nth(2)').prop('disabled', false);
            $('#crud_control_2 button:nth(3)').prop('disabled', false);
            $('#crud_control_2 button:nth(4)').prop('disabled', false);
            $('#crud_control_2 button:nth(5)').prop('disabled', false);
            $('#crud_control_2 button:nth(6)').prop('disabled', false);
        } else if ($trs.length > 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
            // $('#crud_control button:nth(2)').prop('disabled', true);
           // $('#crud_control button:nth(3)').prop('disabled', false);
            $('#crud_control_2 button:nth(0)').prop('disabled', true);
            $('#crud_control_2 button:nth(1)').prop('disabled', false);
            $('#crud_control_2 button:nth(2)').prop('disabled', false);
            $('#crud_control_2 button:nth(3)').prop('disabled', false);
            $('#crud_control_2 button:nth(4)').prop('disabled', false);
            $('#crud_control_2 button:nth(5)').prop('disabled', false);
            $('#crud_control_2 button:nth(6)').prop('disabled', false);
        }
    });

    load_table();

    var old_password = find_i18n('s_password_old'),
        new_password = find_i18n('s_password_new'),
        retype_password = find_i18n('s_password_retype'),
        edit_name = find_i18n('s_password_rule'),
        pwd_not_match = find_i18n('e_bad_password_mismatch');

    // $('#edit_name').attr("placeholder", edit_name);

    // bind button handlers
    // create
    // $('#crud_control button:nth(0)').click(function(){
    //     util_page.navi_page('client_create_page.php');
    // });
    $('#crud_control button:nth(0)').click(register_client);

    

    // delete
    init_buttons_status();
    $('#crud_control button:nth(1)').click(delete_rows);
    $('#crud_control button:nth(2)').click(export_csv);
    $('#crud_control button:nth(3)').click(function(){
       // style="display:none;"
       var $control = $("#crud_control_2").css('display')
       if($control == 'block'){
         $("#crud_control_2").css("display",'none');
       }else{
         $("#crud_control_2").css("display",'block');
       }
    });
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
    $('#crud_control_2 button:nth(0)').click(change_name);
    $('#crud_control_2 button:nth(1)').click(change_group);
    // $('#crud_control_2 button:nth(2)').click(change_mac);
    // $('#crud_control_2 button:nth(3)').click(change_limit);
    $('#crud_control_2 button:nth(2)').click(change_usb);
    $('#crud_control_2 button:nth(3)').click(change_resolution);
    $('#crud_control_2 button:nth(4)').click(change_desc);
}

var init_buttons_status = function() {
    $('#crud_control button:nth(1)').prop('disabled', true);
    // $('#crud_control button:nth(2)').prop('disabled', true);
  //  $('#crud_control button:nth(3)').prop('disabled', true);
    $('#crud_control_2 button:nth(0)').prop('disabled', true);
    $('#crud_control_2 button:nth(1)').prop('disabled', true);
    $('#crud_control_2 button:nth(2)').prop('disabled', true);
    $('#crud_control_2 button:nth(3)').prop('disabled', true);
    $('#crud_control_2 button:nth(4)').prop('disabled', true);
    $('#crud_control_2 button:nth(5)').prop('disabled', true);
    $('#crud_control_2 button:nth(6)').prop('disabled', true);
};

var register_client = function() {
    var $dialog = util_page.dialog_confirm_builder(
        $('#dialog_register_client').html(),
        find_i18n('s_save'),
        find_i18n('s_cancel'),
        function() {
            register_client_ok({
                'name': $dialog.find('input:nth(0)').val(),
                'mac': $dialog.find('input:nth(1)').val(),
                'memo': $dialog.find('input:nth(2)').val(),
                'browser':true
            });
        }
    );
    
}

var change_limit = function() {
    var $dialog = util_page.dialog_confirm_builder(
        $('#dialog_edit_speed_limit').html(),
        find_i18n('s_save'),
        find_i18n('s_cancel'),
        function() {
            change_rows({
                'download_MBS': $dialog.find('input:nth(0)').val(),
                'upload_MBS': $dialog.find('input:nth(1)').val()
            });
        });
    var bags = util_table.checked();
    // load selected client speed limit value
    // use 0 for multiple selections
    if (bags.length == 1) {
        util_page.rest_get('/tc/rest/machine.php/' + bags[0].key, function(c) {
            $dialog.find('input:nth(0)').val(c.download_MBS);
            $dialog.find('input:nth(1)').val(c.upload_MBS);
        });
    }
};

var change_group = function() {
    var title = find_i18n('s_change_group');
    util_page.rest_get('/tc/rest/client_group.php', function(cgs) {
        var selections = util_page.dialog_select_selections();
        $.map(cgs, function(cg) {
            var text = cg.name + ', ' + cg.create_timestamp;
            if (cg.desc) {
                text += ', ' + cg.desc;
            }
            selections.push({text: text, value: cg.cgid});
        });
        util_page.dialog_select(title, selections, function(value) {
            change_rows({client_group: value});
        });
        var $form = $('.bootbox-form');
        var $client = $('<label ><span >'+find_i18n('s_input_client_group')+'</span> :</label>')
        $form.append($client);
    });
    
};

var change_usb = function() {
    var title = find_i18n('s_client_edit_usb_storage');
    var selections = util_page.dialog_select_selections('EYN');
    util_page.dialog_select(title, selections, function(sel) {
        change_rows({usb_storage: sel});
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_client_usb_storage')+'</span> :</label>')
    $form.append($client);
};

var change_resolution = function() {
    var title = find_i18n('s_client_edit_resolution');
    var $dialog = util_page.dialog_prompt(title, function(value) {
        change_rows({resolution: value});
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_client_resolution')+'</span> :</label>')
    $form.append($client);
    $dialog.find('input').attr('maxlength', '32');
};

var change_desc = function() {
    var title = find_i18n('s_input_client_desc');
    var $dialog = util_page.dialog_prompt(title, function(value) {
        change_rows({memo: value});
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_description')+'</span> :</label>')
    $form.append($client);
    $dialog.find('input').attr('maxlength', '40');
};

var export_csv = function() {

    var $trs = $("#table_client_list").find("tr");
    var str = "";
    for (var i = 0; i < $trs.length; i++) {
        var $tds = $trs.eq(i).find("td,th");
        for (var j = 1; j < $tds.length; j++) {
            str += $tds.eq(j).text() + ",";
        }
        str += "\n";
    }
    var uri = "data:text/csv;charset=utf-8,\ufeff" + encodeURIComponent(str);
    var ie_chinese = "\ufeff" + str;
    var blob = new Blob([ie_chinese],{type: "text/csv;charset=utf-8;"});
    var date=new Date();
    var data_format = date.getFullYear()+""+(date.getMonth()+1)+""+date.getDate();
    var csv_name = "client_list-"+data_format + ".csv";
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, csv_name);
    } else {
        var link = document.createElement("a");
        if (link.download !== undefined) {
            link.href = uri;
            link.setAttribute("download", csv_name);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
};

var change_name = function() {
    var title = find_i18n('s_edit_name');
    var $dialog = util_page.dialog_prompt_required(title, function(name) {
        change_rows({name: name});
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_terminal_name')+'</span> :</label>')
    $form.append($client);
    $dialog.find('input').attr('maxlength', '30');
    // $dialog.find('.bootbox-form').attr('maxlength', '30');
    // var $dialog = util_page.dialog_confirm_builder(
    //     $('#dialog_edit_name').html(),
    //     find_i18n('s_ok'),
    //     find_i18n('s_cancel'),
    //     function(name) {
    //         change_rows({name: name});
    //     });
};

var change_mac = function() {
    var title = find_i18n('s_edit_mac');
    var $dialog = util_page.dialog_prompt_required(title, function(value) {
        if (util_page.is_mac(value)) {
            value = value.toUpperCase();
            change_rows({mac: value});
        } else {
            util_page.dialog_message_i18n('e_bad_client_mac');
        }
    });
    var $form = $('.bootbox-form');
    var $client = $('<label ><span >'+find_i18n('s_mac')+'</span> :</label>')
    $form.append($client);
    $dialog.find('input').attr('maxlength', '17').css('text-transform', 'uppercase');
};

var register_client_ok = function(client_info){
    var error = validate(client_info);
    if (error) {
        util_page.dialog_message(error);
        return;
    }

    client_info.mac = client_info.mac.toUpperCase();

    util_page.rest_post('/tc/rest/machine.php', client_info, function() {
        util_page.navi_page('client_page.php');
    });

}


function validate(input_data) {
    if (util_page.is_empty(input_data.name)) {
        return find_i18n('e_empty_name', 'Error: Name is empty');
    }
    if (util_page.is_empty(input_data.mac)) {
        return find_i18n('e_empty_mac', 'Error: MAC address is empty');
    }
    return;
}

var change_rows = function(row_data) {
    var bags = util_table.checked(),
        total_count = bags.length,
        done_count = 0,
        error_count = 0,
        error_lines = '';

    $.map(bags, function(bag){
        var url = '/tc/rest/machine.php/' + bag.key;
        util_page.rest_put(url, row_data, function(res) {
            done_count += 1;
            if (total_count === done_count + error_count) {
                if (error_count === 0) {
                    util_page.dialog_message_i18n('s_update_done_ok');
                } else {
                    util_page.dialog_message(error_lines);
                }
                load_table();
                $("#crud_control_2").css("display",'none');
                // $("#table_client_list thead tr th input").css("display",'none');
            }
        }, function(error) {
            error_count += 1;
            error_lines += '<br>['+ bag.row[0] + '] ' + find_i18n(error.error);
            if (total_count === done_count + error_count) {
                if (error_count === 0) {
                    util_page.dialog_message_i18n('s_update_done_ok');
                } else {
                    util_page.dialog_message(error_lines);
                }
                load_table();
                $("#crud_control_2").css("display",'none');
                // $("#table_client_list thead tr th input").css("display",'none');
            }
        });
    });

};

var delete_rows = function() {
    var bags = util_table.checked(),
        message = find_i18n('c_delete_selected_client'),
        total_count = bags.length,
        done_count = 0,
        error_count = 0,
        error_lines = '';

    util_page.dialog_confirm(message, function(){
        var urls = $.map(bags, function(bag){
            var url = '/tc/rest/machine.php/' + bag.key;
            util_page.rest_delete(url, {}, function() {
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
    
    var $form = $('.modal-body');
    var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
    $form.append($client);
};

function load_table() {
    util_page.rest_get('/tc/rest/machine.php', function(res) {
        util_table.load(parse_record(res), function(){
            create_details_link();
            var $control_edit_1 = $("#table_client_list tbody input").css("display");
            // $("#table_client_list tbody input").css("display",'none');
        });
    });
    init_buttons_status();
}

$('.tc-table').mouseenter(create_details_link);


function create_details_link() {
    $('.tc-table tbody tr').each(function() {
        var key = $(this).data('key');
        var name =  $(this).data('name');
        var bootimage_name = $(this).data('bag').row[0];
        var createClickHandler = function(row) {
            return function() {
                localStorage.setItem("bootimage_name",bootimage_name); 
                // var historyUrl = window.location.href.split(window.location.host)[1];
                // localStorage.setItem("historyUrl",historyUrl); 
                util_page.navi_page('client_detail.php?key='+key);
            };
        };

        $(this).find('th,td').each(function (colIndex, c) {
            if(colIndex != 0) {
                this.onclick = createClickHandler(this);
            }
        });
    });
    
}

function parse_record(result) {
    var records = [];
    $.map(result, function(r) {
        var memory = r.memory_size == 0 ? '' : r.memory_size,
            disk = r.disk_size == 0 ? '' : r.disk_size,
            limit_d = r.download_MBS == 0 ? '' : r.download_MBS + ' MB/s',
            limit_u = r.upload_MBS == 0 ? '' : r.upload_MBS + ' MB/s',
            usb_storage = find_i18n(
                r.usb_storage ? 's_usb_storage_enabled' : 's_usb_storage_disabled');

        if (memory) {
            memory = util_page.print_size(memory, 'MiB');
        }

        if (disk) {
            disk = util_page.print_size(disk, 'GiB');
        }

        records.push({
            key: r.id,
            row: [
                r.name,
                r.client_group_name,
                r.mac,
                r.cpu_model,
                memory,
                disk,
                limit_d,
                limit_u,
                usb_storage,
                r.resolution,
                r.memo
            ],
            checkable: true
        });
    });
    return records;
}

$(document).ready(page_load);
</script>

<?php standard_page_end(); ?>