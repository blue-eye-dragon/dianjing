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

standard_page_begin('client_groups');
?>

<div class="container-fluid container_table">
    <!-- row for toolbar -->
    <div class="row row_div">
        <?php
        $bar = array(
            "button_groups" => array(
                array(
                    "buttons" => array(
                        array("i18n" => "s_client_group_add", "icon" => "glyphicon-plus"),
                        array("i18n" => "s_client_group_remove", "icon" => "glyphicon-trash"),
                        array("i18n" => "s_client_group_edit", "icon" => "glyphicon-edit"),
                    ),
                ),
            ),
            "id" => "toolbar_control",
        );
        echo html_toolbar($bar);
        ?>
    </div>

    <div class="row row_div">
        <div class="list-group" id="client_group_list">
        </div>
        <div id="client_group_member_list" readonly class="col-xs-6 well" style="margin-top: 10px; display: none;">
        </div>
    </div>
</div>

<div id="dialog_client_group_details" style="display: none;">
        <h4 id="edit_group_title"><span ></span></h4>
    <div class="model_div" style="display: flex;margin-top:30px" >
        <label style="width: 35%;color: #666666;font-weight: 400;font-size: 16px;text-align: right;margin-right: 15px;" class="model_label"><span i18n="s_client_group_name">Group Name</span> :</label>
        <input style="width: 60%;" type="text" class="form-control" maxlength="20" placeholder="Up to 20 characters"></input>
    </div>
    <div class="model_div" style="margin-top:15px">
        <label class="model_label" style="color: #666666;font-weight: 400;font-size: 16px;float:left;width: 35%;line-height:54px;text-align: right;margin-right: 15px;"><span i18n="s_client_group_desc">Description</span> :</label>
        <textarea class="form-control" maxlength="100" style="width:60%;float:left"></textarea>
    </div>
    <div class="form-group" style="margin-top:35px">
        <label class="model_label" style="color: #666666;font-weight: 400;font-size: 16px;width: 35%;text-align: right;margin-right: 15px;"><span i18n="s_group_autoboot">Autoboot Image</span> :</label>
        <select class="form-control" style="float:left;width: 60%;">
            <option value="0"></option>
            <option value="-1"></option>
        </select>
    </div>
</div>
<!-- 第一个 -->
<div id="client_group_page" style="display:none">
    <div id="client_group_list">
        <!-- <a> -->
        <div style="flex-grow:1;background-image: linear-gradient(to left,#0D2B9D, #1E56CC); margin-right:20px;margin-top:20px;float:left" class="client_group_list_div" id="client_group_list_div">
            <div style="display: flex;justify-content:space-between;padding-bottom:15px;border-bottom:1px solid #E7E7E7">
                <span id="list-group-item-name"></span>
                <span id="client_group_operation" class="">
                    <span id="client_group_num" class=""></span>
                    <span id="client_group_edit" class="glyphicon glyphicon-edit" style="display:none"></span>
                    <span id="client_group_remove" class="glyphicon glyphicon-remove" style="display:none"></span>
                </span>
                <!-- <span id="client_group_id" style="display:block"></span> -->
            </div>
            <div style="display: flex;margin-top:25px;">
                <div style="flex-grow: 1;border-right:1px solid #E7E7E7;text-align:center;width:20%">
                    <div>
                        <button type="button" class="btn dropdown-toggle" id="dropdownMenu2" style="padding:0;background:#fff" data-toggle="dropdown">
                            <!-- <img src="/tc/images/group_2.png" id="dropdownMenu_img_2">
                            <img src="/tc/images/group.png" id="dropdownMenu_img_1"> -->
                        </button>
                        <ul class="dropdown-menu client_group_ul" id="client_group_list_ul" role="menu" aria-labelledby="dropdownMenu2">
                        </ul>
                        <span i18n="s_groud_client" id="groud_client_name" style="color:#5D9DFF">Group</span>
                        <a id="list-group-item-name-2" style="margin-top:16px;width:100%;display: block;" class="text_space"></a>
                    </div>
                </div>
                <div style="flex-grow: 3;border-right:1px solid #E7E7E7;text-align:center;width: 54%;">
                    <p style="margin-bottom:18px">
                        <img src="/tc/images/create_time.png" style="margin-right:6px;margin-top: -2px;">
                        <span i18n="s_create_time" style="color:#fff"> Create Time </span>
                    </p>
                    <a id="list-group-item-text" style="display:inline-block" class="text_space">---</a>
                </div>
                <div style="flex-grow: 1;text-align:center;width:20%">
                    <p style="margin-bottom:18px">
                        <img src="/tc/images/des.png" style="margin-top: -2px" class="group_image">
                        <span i18n="s_des" style="color:#fff">Describe</span>
                    </p>
                    <a id="list-group-item-desc" class="text_space">---</a>
                </div>
            </div>
            <!-- <div style="text-align: center;margin-top:25px;">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" id="show_client_list">
                        <img src="/tc/images/down.png">
                    </a>
                </h4>
                <div id="collapseOne" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div style="display: flex;margin-top:25px;border-top:1px solid #E7E7E7;padding-top:15px" id="client_list">
                        </div>
                    </div>
                </div>
            </div> -->
            <ul class="dropdown-menu" id="group_select_value" style="display:none">
                <li role="presentation" id="select_lang_zh">
                    <a role="menuitem" tabindex="-1" href="javascript:select_lang('zh')">1</a>
                </li>
                <li role="presentation" id="select_lang_en">
                    <a role="menuitem" tabindex="-1" href="javascript:select_lang('en')">2</a>
                </li>
            </ul>

        </div>
        <!-- </a> -->
    </div>
</div>
<style>
    .modal-footer {
        display: block;
    }
    #list-group-item-name {
      color: #fff
    }
    #client_group_num{
        background-color:'#fff';
        color: #fff;
    }
    #client_group_edit{
        background-color:'#fff';
        color: #5D9DFF;
    }
    #client_group_remove{
        background-color:'#fff';
        color: #5D9DFF;
    }
    .dropdown-menu>li>.client_group_li{
        width: 100%;
        text-align: center;
        padding: 15px 0;
        /* border-bottom: 1px solid #E7E7E7; */
        border-right:0;
    }
    .dropdown-menu>li>a:hover{
        background:#fff;
    }

    .client_group_ul{
        background: #FFFFFF;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.5);
        border: 1px solid #F1F1F1;
        padding-left: 15px;
        padding-right:15px;
        margin-top: -67px;
        margin-left: 17px;
    }
    .btn-default{
        background-color: transparent;
    }
    .container_table{
        background-color: transparent;
    }
    /* #client_group_list{
        position: absolute;
        top: 120px;
        left: 30px;
    } */
    /* .dropdown-menu{
        top:320px;
    } */
</style>





<?php standard_page_mid(); ?>

<script language="javascript">
    "use strict";

    var cgidList = [];
    var create_group = function($dialog) {
        var name = $dialog.find('input').val(),
            desc = $dialog.find('textarea').val(),
            autoboot = $dialog.find('select').val();
            
        if(!autoboot){
            autoboot = '0';
        }
        var data = {
                name: name,
                desc: desc,
                autoboot: autoboot
            };

        util_page.rest_post('/tc/rest/client_group.php', data, function() {
            load_groups();
        });

        
    };
    var show_group = function(client_members,index) {
        $('#client_group_member_list').text('').hide();

        if (client_members.length>0) {
            var name_string = '';
            // $("#client_list").html('')
            $(".client_group_list_div #client_group_list_ul").html('')
            $(".client_group_list_div .client_group_ul:nth("+index+")").css('display','block')
            
            $(".client_group_list_div #client_group_list_ul").append('<li style="border-bottom:1px solid #eee;height:55px"><a href="###" id="content_logout" class="user_quit_div client_group_li"><span style="margin-right:30px;color: #999;">No.</span><span style="display: inline-block;width: 100px;">'+find_i18n('s_terminal_name')+'</span></a></li>')
            $.map(client_members, function(m,i) {
                // name_string += m.name + ', ';
                // var $client_html = '<div style="flex-grow: 1;border-right:1px solid #E7E7E7;text-align:center">'+m.name+'</div>';
                var $client_li = '<li role="presentation" id="select_lang_zh"><a href="###" id="content_logout" class="user_quit_div client_group_li"><span style="margin-right:30px;color: #999;">'+ (i+1) +'</span><span style="display: inline-block;width: 100px;">'+m.name+'</span></a></li>'
                // $("#client_list").append($client_html)
                $(".client_group_list_div #client_group_list_ul").append($client_li)
            });
            // $('#client_group_member_list').show().text(name_string);
        }else{
            var name_string = '';
            // $("#client_list").html('')
            $(".client_group_list_div #client_group_list_ul").css('display','none')
        }
    };

    var delete_group = function(cgid) {
        util_page.dialog_confirm(find_i18n('c_client_group_delete'), function() {
            util_page.rest_delete('/tc/rest/client_group.php/' + cgid, {}, function() {
                load_groups();
                util_page.dialog_message(find_i18n('s_operation_complete'));
            });
        });
        var $form = $('.modal-body');
        var $client = $('<div style="text-align: center;margin-top: 10px;color:#D5D5D5;font-size:12px;margin-right: 21px;">'+find_i18n('s_clients_delete_mes')+'</div>')
        $form.append($client);
    };

    var edit_group = function(cgid) {
        $('#edit_group_title span').text(find_i18n('s_client_group_edit'));
        create_empty_details_dialog(function($dialog) {
            var name = $dialog.find('input').val(),
                desc = $dialog.find('textarea').val(),
                autoboot = $dialog.find('select').val();
            if(!autoboot){
                autoboot = '0';
            }
            var data = {
                name: name,
                desc: desc,
                autoboot: autoboot
            };
            util_page.rest_put('/tc/rest/client_group.php/' + cgid, data, function() {
                load_groups();
                util_page.dialog_message(find_i18n('s_operation_complete'));
            });
        }, function($dialog) {
            util_page.rest_get('/tc/rest/client_group.php/' + cgid, function(cg) {
                $dialog.find('input').val(cg.name);
                $dialog.find('textarea').val(cg.description);
                $dialog.find('select').val(cg.autoboot_ciid);
            });
        });
    };

    var html_list_item = function(cg) {
        // var html = '<a href="#" class="list-group-item">';
        // html += '<span class="badge">' + cg.client_members.length + '</span>';
        // html += '<h4 id="list-group-item-name" class="list-group-item-heading"></h4>';
        // html += '<p id="list-group-item-desc" class="list-group-item-text"></p>';
        // if (cg.autoboot_image) {
        //     html += '<p class="list-group-item-text pull-right">' + cg.autoboot_image + '</p>';
        // }
        // html += '<p class="list-group-item-text">' + cg.create_timestamp + '</p>';
        // html += '</a>';
        var html = $('#client_group_page').html()
        return html;
    };
    

    var load_groups = function() {
        let button = $('#toolbar_control button')
        let $img_2 = "<img src='/tc/images/group_2.png'>"
        let $img_1 = "<img src='/tc/images/group.png'>"
        util_page.rest_get('/tc/rest/client_group.php', function(cgs) {
            $('#client_group_list').empty();
            $('#client_group_member_list').text('').hide();
            

            $.map(cgs, function(cg,index) {
                cgidList.push(cg.cgid)
                $(html_list_item(cg))
                    .find('#list-group-item-name').text(cg.name?cg.name:'---').end()
                    .find('#list-group-item-name-2').text(cg.name?cg.name:'---').end()
                    .find('#list-group-item-name-2').attr('title',cg.name?cg.name:'---').end()
                    .find('#list-group-item-desc').text(cg.description?cg.description:'---').end()
                    .find('#list-group-item-desc').attr('title',cg.description?cg.description:'---').end()
                    .find('#list-group-item-text').text(cg.create_timestamp?cg.create_timestamp:'---').end()
                    .find('#list-group-item-text').attr('title',cg.create_timestamp?cg.create_timestamp:'---').end()
                    .find('#client_group_num').text(cg.client_members?cg.client_members.length:0).end()
                    // .find('#client_group_id').text(cg.cgid).end()
                    .find('#client_group_edit').click(function(e) {
                        edit_group(cg.cgid);
                    }).end()
                    .find('#client_group_remove').click(function(e) {
                        delete_group(cg.cgid);
                    }).end()
                    .find('#dropdownMenu2').html(cg.client_members.length>0?$img_1:$img_2).end()
                    .find('#groud_client_name').css('color',cg.client_members.length>0?'#5D9DFF':"#fff").end()
                    .find("#dropdownMenu2").click(function(e){
                        show_group(cg.client_members,index);
                    }).end()
                    .data('cgid', cg.cgid)
                    .css('word-wrap', 'break-word')
                    .click(function(e) {
                        e.preventDefault();

                        // $('#client_group_list a').removeClass('active');
                        // $(this).addClass('active');
                        
                    })
                    .appendTo($('#client_group_list'));
            });
        });
    };

    var create_empty_details_dialog = function(fn_done, fn_init) {
        var $dialog = util_page.dialog_confirm_builder(
            $('#dialog_client_group_details').html(),
            find_i18n('s_save'),
            find_i18n('s_cancel'),
            function() {
                fn_done($dialog);
            }
        );
        util_page.rest_get('/tc/rest/bootimage2.php', function(result) {
            var options = $.map(result['images'], function(img) {
                return '<option value="' + img.id + '">' + img.name + '</option';
            });
            $dialog.find('select').append(options);
            fn_init($dialog);
        });
        $dialog.find('option:nth(1)').text(find_i18n('s_general_global'));
        return $dialog;
    }

    function page_load() {
        util_page.disable_cache();
        util_page.enable_locale();
        util_page.render_sidebar();

        load_groups();

        $('#toolbar_control button:nth(0)').click(function() {
            $('#edit_group_title span').html(find_i18n('s_client_group_add'));
            // $('#edit_group_title span').append(find_i18n('s_client_group_add'));
            create_empty_details_dialog(create_group);
        });

        $('#toolbar_control button:nth(1)').click(function() {
            var $span = $('#client_group_list span');
            var test = $span.hasClass('glyphicon-edit')
            var test2 = $span.hasClass('glyphicon-remove')
            // if ($span.hasClass('glyphicon-remove')) {
            //     load_groups();
            //     return;
            // }
            // if ($span.hasClass('glyphicon-edit')) {
            //     $('#client_group_list #client_group_num').off('click');
            // }
            var $control_remove = $("#client_group_remove").css("display");
            var $control_edit = $("#client_group_edit").css("display");
            var $control_num = $("#client_group_num").css("display");
           
            
            if($control_remove == 'block'){
                $("#client_group_operation #client_group_remove").css("display",'none');
                $("#client_group_operation #client_group_num").css("display",'block');
            }else if($control_edit == 'block'){
                $("#client_group_operation #client_group_remove").css("display",'block');
                $("#client_group_operation #client_group_edit").css("display",'none');
            }else if($control_num == 'block'|| $control_num == 'inline'){
                $("#client_group_operation #client_group_remove").css("display",'block');
                $("#client_group_operation #client_group_num").css("display",'none');
            }
            // $('#client_group_list #client_group_num')
            //     .html('<span class="glyphicon glyphicon-remove"></span>')
            //     .click(function() {
            //         // delete_group($(this).closest('a').data('cgid'));
            //         delete_group($(this).find('#client_group_id').text());
            //     });
        });

        $('#toolbar_control button:nth(2)').click(function() {
            var $span = $('#client_group_list span');
            var test = $span.hasClass('glyphicon-edit')
            var test2 = $span.hasClass('glyphicon-remove')
            
            // if ($span.hasClass('glyphicon-edit') ) {
            //     load_groups();
            //     return;
            // }
            // if ($span.hasClass('glyphicon-remove')) {
            //     $('#client_group_list #client_group_num').off('click');
            // }
            let client_group_list  = $('#client_group_operation #client_group_edit');
            var $control_edit = $("#client_group_edit").css("display");
            var $control_remove = $("#client_group_remove").css("display");
            var $control_num = $("#client_group_num").css("display");

            if($control_edit == 'block'){
                $("#client_group_operation #client_group_edit").css("display",'none');
                $("#client_group_operation #client_group_num").css("display",'block');
            }else if($control_remove == 'block'){
                $("#client_group_operation #client_group_edit").css("display",'block');
                $("#client_group_operation #client_group_remove").css("display",'none');
            }else if($control_num == 'block' || $control_num == 'inline'){
                $("#client_group_operation #client_group_edit").css("display",'block');
                $("#client_group_operation #client_group_num").css("display",'none');
            }
            // for(var i=0;i<client_group_list.length;i++){
            //     client_group_list[i].html('<span class="glyphicon glyphicon-edit"></span>')
            //     .click(function(i) {
            //         let test2  = i;
            //         let test1  = $(this).find('#client_group_id');
            //         let test  = $('#client_group_list').closest('#client_group_id').text()
            //         edit_group($('#client_group_id').text());
            //     });
            // }
            // $.map(client_group_list, function(g,i) {
            //     g.html = '<span class="glyphicon glyphicon-edit"></span>'
            //     g.click(function() {
            //             edit_group(cgidList[i]);
            //         });
            // })
            // client_group_list.foreach((item,index)=>{
            //     item.html('<span class="glyphicon glyphicon-edit"></span>')
            //         .click(function() {
            //         edit_group(cgidList[index]);
            //         });
            // })
            // $('#client_group_list #client_group_num')
            // client_group_list.html('<span class="glyphicon glyphicon-edit">1</span>')
            //     .click(function(i) {
            //         let test2  = i;
            //         let test1  = $(this).find('#client_group_id');
            //         let test  = $('#client_group_list').closest('#client_group_id').text()
            //         edit_group($('#client_group_id').text());
            //     });
        });

        var test = $("#show_group");
        $("#show_group").click(function(){
            var test = $("#group_select_value").css("display");
            $("#group_select_value").css("display","block");
        });
    }

    $(document).ready(page_load);
</script>

<?php standard_page_end(); ?>