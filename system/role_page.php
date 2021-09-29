<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require('libpage.php');
    require("include/inc.bootstrap.php");

    standard_page_begin('role');
?>


<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_role_add", "icon" => "glyphicon-plus"),
                    array("i18n" => "s_role_del", "icon" => "glyphicon-minus"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_role_edit_name", "icon" => "glyphicon-edit"),
                    array("i18n" => "s_role_edit_desc", "icon" => "glyphicon-edit"),
                ),
            ),
        ),
        "id" => "main",
    );
    echo html_toolbar($bar);
?>


<div class="container-fluid" id = "TabPanel">

</div>

<div style="display: none" class="tab-content">
    <div class="row no-gutters" id = "TabAndContent">
        <ul id="RoleTab" class="nav nav-tabs" style="margin-top: 10px;">
        </ul>
    </div>
</div>

<div style="display: none" id="AllRoleContents" class="tab-content">
</div>


<div style="display: none" id="RoleContent">
    <div class="tab-pane fade">
        <div class="col-xs-12" id="RoleDesc">
            <p></p>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div class="container-fluid col-xs-8 col-sm-8 col-md-8 col-lg-8" id="" style="margin-top: 10px;">
                    <h4><span i18n="s_role_auth_url">Authorized Web Pages</span></h4>
                    <hr class="compact">
                </div>
                <div class="col-xs-4" style="margin-top: 10px;">
                    <div class="btn-group pull-right" role="group" id="tb_role_url">
                        <button type="button" class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-plus"></span>
                            <span i18n="s_role_url_add"></span>
                        </button>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="list-group" id="role_url_list"></div>
                </div>
            </div>


            <div class="col-xs-6">
                <div class="container-fluid col-xs-8 col-sm-8 col-md-8 col-lg-8" id="" style="margin-top: 10px;">
                    <h4><span i18n="s_role_auth_group">Authorized User Groups</span></h4>
                    <hr class="compact">
                </div>
                <div class="col-xs-4" style="margin-top: 10px;">
                    <div class="btn-group pull-right" role="group" id="tb_role_group">
                        <button type="button" class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-plus"></span>
                            <span i18n="s_role_group_add"></span>
                        </button>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="list-group" id="role_group_list"></div>
                </div>
            </div>

        </div>
    </div>

</div>


<div style="display: none" id="list_item_rgroup">
    <a class="list-group-item">
        <div class="row">
            <div class="col-xs-9" style="word-wrap: break-word;">
                <h4 class="list-group-item-heading"></h4>
                <p class="list-group-item-text"></p>
            </div>
            <div class="col-xs-3">
                <div class="btn-group pull-right" role="group" style="padding-right: 5px; padding-top: 0px;">
                    <button type="button" class="btn btn-default btn-link" style="padding-top: 0px;">
                        <span class="glyphicon glyphicon-remove" style="margin-right: 3px;"></span>
                        <span i18n="s_delete">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </a>
</div>

<div style="display: none" id="list_item_rurl">
    <a class="list-group-item">
        <div class="row">
            <div class="col-xs-9" style="word-wrap: break-word;">
                <h4 class="list-group-item-heading"></h4>
                <p class="list-group-item-text"></p>
            </div>
            <div class="col-xs-3">
                <div class="btn-group pull-right" role="group" style="padding-right: 5px; padding-top: 0px;">
                    <button type="button" class="btn btn-default btn-link" style="padding-top: 0px;">
                        <span class="glyphicon glyphicon-remove" style="margin-right: 3px;"></span>
                        <span i18n="s_delete">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </a>
</div>


<div id="dialog_role_add" hidden>
    <div class="form-group">
        <label><span i18n="s_name">Name</span></label>
        <input type="text" class="form-control" id="role_name"  maxlength="30">
    </div>
    <div class="form-group">
        <label><span i18n="s_description">Description</span></label>
        <input type="text" class="form-control" id="role_desc"  maxlength="30">
    </div>
    <div class="form-group">
        <label><span i18n="s_role_auth_url">Authorized Operation</span></label>
        <select multiple class="form-control" style="height: 200px;" id="role_urls"></select>
    </div>
    <div class="form-group">
        <label><span i18n="s_role_auth_group">Authorized Group</span></label>
        <select multiple class="form-control" style="height: 100px;" id="role_agids"></select>
    </div>
</div>


</div>
</div>



<?php standard_page_mid(); ?>

<?php
function get_links(){
    global $links;
    $lang = $GLOBALS["_TC2_"]["lang"];
    $operations = array();
    foreach ($links as $key => $value) {
        foreach ($value["links"] as $key1 => $value1) {
            if (!array_key_exists("show",$value1) || $value1["show"] || $key1 == "client_image") {
                $operations[$key1] =  $value["title"][$lang]. ">" . $value1["title"][$lang];
            }
        }
    }
    return $operations;
}
?>

<script language="javascript">

"use strict";

function add_role() {
    var dialog = util_page.dialog_confirm_builder(
        $('#dialog_role_add').html(),
        find_i18n('s_save', 'Save'),
        function() {
            var name = dialog.find('#role_name').val(),
                desc = dialog.find('#role_desc').val();
            var client_role_url = '/tc/rest/role.php/role',
                changes = {};
                changes['name'] = name;
                changes['desc'] = desc;
            var role_urlids = [];
            dialog.find('#role_urls option').filter(':selected').each(function(){
                role_urlids.push($(this).val());
            });
            var role_grpids = [];
            dialog.find('#role_agids option').filter(':selected').each(function(){
                role_grpids.push($(this).val());
            });
            changes['role_urlids'] = role_urlids;
            changes['role_grpids'] = role_grpids;
            util_page.rest_post(
                client_role_url,
                changes,
                load_nav_last_active
            );
        }
    );

    var $operations = <?php echo json_encode(get_links()); ?>;
    //List urls in dialog
    for(let $key in $operations) {
        var $url_name = $operations[$key];
        var $opt = $('<option></option>').val($key).text($url_name);
        dialog.find('#role_urls').append($opt);
    }
    //List user group in dialog
    util_page.rest_get(
        '/tc/rest/group.php',
        function(groups) {
            $.map(groups, function(group){
                var $opt = $('<option></option>').val(group.id).text(group.name);
                dialog.find('#role_agids').append($opt);
            });
        }
    );

    dialog.modal('show');
}


function del_role() {
    util_page.rest_get('/tc/rest/role.php', function(roles) {
        var $role_name = $('#RoleTab .active a').text();
        var roles_select = [{text: find_i18n('s_select_role', 'Select a role'), value: ''}];
        for (let role in roles) {
            roles_select.push({'text': roles[role]['role_name'], 'value': roles[role]['role_id']});
        }
        util_page.dialog_select(
            find_i18n('s_select_role'),
            roles_select,
            function(select) {
                if (select === '') {
                    util_page.dialog_message_i18n('e_no_role_selected');
                    return;
                }
                util_page.rest_delete(
                    '/tc/rest/role.php/' + select,
                    {},
                    function() {
                        load_nav_first_active();
                        util_page.dialog_message(find_i18n('s_operation_complete'));
                    }
                    //load_nav
                );
            }
        );
    });
}



function edit_name() {
    var title = find_i18n('s_input_new_role_name');
    var $dialog = util_page.dialog_prompt_required(title, function(new_role_name) {
        change_role({'name': new_role_name});
    });
    $dialog.find('input').attr('maxlength', '30');
}

function edit_desc() {
    var title = find_i18n('s_input_new_role_desc');
    var $dialog = util_page.dialog_prompt_required(title, function(new_role_desc) {
        change_role({'desc': new_role_desc});
    });
    $dialog.find('input').attr('maxlength', '30');
}

function change_role(role_data) {
    var $role_name = $('#RoleTab .active a').text(),
        urls = '/tc/rest/role.php/' + $role_name;
    util_page.rest_put(urls, role_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_nav();
    });
}


function del_role_property(property_id, property){
    var $role_id = $('#AllRoleContents .active').data("role_id");
    util_page.rest_delete(
        '/tc/rest/role.php/' + $role_id + '/' + property + '/' + property_id,
        {},
        load_nav
    );
}

var add_url = function() {
    function url_not_authed(allowed_url) {
        var $role_id = $('#AllRoleContents .active').data('role_id'),
            $operations = <?php echo json_encode(get_links()); ?>;
        for (let url in allowed_url){
            for (let key in $operations){
                if (url == key) {
                    delete $operations[key];
                }
            }
        }
        return $operations;
    }

    util_page.rest_get('/tc/rest/role.php', function(roles) {
        var $role_name = $('#RoleTab .active a').text();
        for(let role in roles){
            if(roles[role]['role_name'] == $role_name) {
                var allowed_url = roles[role]['allowed_url'],
                role_id = roles[role]['role_id'];
                break;
            }
        }
        var urls = url_not_authed(allowed_url),
            urls_select = [{'text': '', 'value': ''}];

        for (let key in urls){
            urls_select.push({'text': urls[key], 'value': key});
        }
        util_page.dialog_select(
            find_i18n('s_role_auth_url'),
            urls_select,
            function(select) {
                if (select === '') {
                    util_page.dialog_message_i18n('e_role_no_url_selected');
                    return;
                }
                util_page.rest_post(
                    '/tc/rest/role.php/' + role_id +'/url',
                    {element_id: select},
                    load_nav
                );
            }
        );
    });
}


function add_group() {
    util_page.rest_get('/tc/rest/role.php', function(roles) {
        var $role_name = $('#RoleTab .active a').text();
        for(let role in roles){
            if(roles[role]['role_name'] == $role_name) {
                var allowed_groups = roles[role]['allowed_group'],
                    role_id = roles[role]['role_id'];
                break;
            }
        }
        var groups_select = [{'text': '', 'value': ''}];
        util_page.rest_get('/tc/rest/group.php', function(all_groups) {
            for (let group in allowed_groups){
                for (let key in all_groups){
                    if (group == all_groups[key]['id']) {
                        delete all_groups[key];
                    }
                }
            }
            for (let key in all_groups) {
                groups_select.push({'text': all_groups[key]['name'], 'value': all_groups[key]['id']});
            }
            util_page.dialog_select(
                find_i18n('s_role_auth_group'),
                groups_select,
                function(select) {
                    if (select === '') {
                        util_page.dialog_message_i18n('e_role_no_group_selected');
                        return;
                    }
                    util_page.rest_post(
                        '/tc/rest/role.php/' + role_id +'/group',
                        {element_id: select},
                        //load_nav(role_id)
                        load_nav
                    );
                }
            );
        });
    });
}



var fill_role_tab_content = function(roles, active_role = null){

    $('#TabPanel').empty();

    var $role_tab = '';
    var $tab_and_content = $('#TabAndContent').clone();
    var $all_role_cont = $('#AllRoleContents').clone();
    for(let role in roles){
        var g = roles[role]['allowed_group'],
            role_name = roles[role]['role_name'],
            role_id = roles[role]['role_id'],
            urls = roles[role]['allowed_url'];
        // fill role tab
        var $tab_li = '<li ' + 'id="tab_' + role_id + '"><a href="#' + role_id + '" data-toggle="tab">' + role_name +'</a></li>';
        $role_tab += $tab_li;
        var $role_cont = $('#RoleContent div:first').clone();

        //fill role AuthGroup content
        for (let key in g){
            var $rga = $('#list_item_rgroup a').clone();
            $rga.data('gid', key);
            $rga.find('.list-group-item-heading').text(g[key]['group_name']);
            $rga.find('.list-group-item-text').text(g[key]['group_desc']);
            var role_group_id = role_id + '_' + key;
            $rga.attr('id',role_group_id);
            $rga.find('button:nth(0)').click(function() {
                del_role_property(key, 'group');
            });
            $role_cont.find('#role_group_list').after($rga);
        }

        //fill role AuthURL content
        for (let key in urls){
            var $rua = $('#list_item_rurl a').clone();
            $rua.data('urlid', key);
            $rua.find('.list-group-item-heading').text(urls[key]['url_title']);
            $rua.find('.list-group-item-text').text(urls[key]['url_category_title']);
            var role_url_id = role_id + '_' + key;
            $rua.attr('id',role_url_id);
            $rua.find('button:nth(0)').click(function() {
                del_role_property(key, 'url');
            });
            $role_cont.find('#role_url_list').after($rua);
        }
        $role_cont.attr('id', role_id);
        $role_cont.find('.col-xs-4:first button:nth(0)').click(add_url);
        $role_cont.find('.col-xs-4:last button:nth(0)').click(add_group);
        $role_cont.data('role_id', role_id);
        $role_cont.find('.container-fluid:first').attr('id', role_id + '_' + 'url');
        $role_cont.find('.container-fluid:last').attr('id', role_id + '_' + 'group');
        $role_cont.find('#RoleDesc p').text(roles[role]['role_desc']);
        $role_cont.appendTo($all_role_cont);
    }

    $all_role_cont.css('display','');
    $tab_and_content.find('#RoleTab').append($role_tab);
    if (!active_role){
        $all_role_cont.find('.tab-pane:first').addClass('in active');
        $tab_and_content.find('#RoleTab li:first').addClass('active');
    } else if (active_role ==  'first' || active_role ==  'last'){
        $all_role_cont.find('.tab-pane:' + active_role).addClass('in active');
        $tab_and_content.find('#RoleTab li:' + active_role).addClass('active');
    } else {
        $all_role_cont.find('#' + active_role).addClass('in active');
        $tab_and_content.find('#tab_' + active_role).addClass('active');
    }
    $tab_and_content.find('ul').after($all_role_cont);


    $('#TabPanel').append($tab_and_content);
}

var load_nav = function(){
    var $active_role = $('#AllRoleContents .active').data('role_id');
    var role_rul = '/tc/rest/role.php';
    util_page.rest_get(role_rul,
        function(roles) {
            fill_role_tab_content(roles, $active_role);
        },
        function(error) {
            util_page.dialog_message_error(error);
        }
    );
}
var load_nav_first_active = function(){
    var role_rul = '/tc/rest/role.php';
    util_page.rest_get(role_rul,
        function(roles) {
            fill_role_tab_content(roles, 'first');
        },
        function(error) {
            util_page.dialog_message_error(error);
        }
    );
}


var load_nav_last_active = function(){
    var role_rul = '/tc/rest/role.php';
    util_page.rest_get(role_rul,
        function(roles) {
            fill_role_tab_content(roles, 'last');
        },
        function(error) {
            util_page.dialog_message_error(error);
        }
    );
}

$(document).ready(function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();
    load_nav();

    $('#main button:nth(0)').click(add_role);
    $('#main button:nth(1)').click(del_role);
    $('#main button:nth(2)').click(edit_name);
    $('#main button:nth(3)').click(edit_desc);
});


</script>

<?php
    body_end();
    page_end();
?>
