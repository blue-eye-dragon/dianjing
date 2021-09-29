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

    standard_page_begin('client_struc');
?>
<body>
    <div id="tree">
    </div>
<div class="container-fluid">
    <div class="row">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_client_tree_add", "icon" => "glyphicon-plus"),
                    array("i18n" => "s_client_tree_del", "icon" => "glyphicon-minus"),
                ),
            )
        ),
        "id" => "crud_control",
    );
    echo html_toolbar($bar);
?>
    </div>

    <div class="row">
        <table class="table tc-table col-xs-12" id="table_client_strc_list">
            <thead>
                <tr>
                    <th> <input type="checkbox"> </th>
                    <th> <span i18n="s_name"> Name </span> </th>
                    <th> <span i18n="s_struc_type"> Type </span> </th>
                    <th> <span i18n="s_mac_address"> MAC Address </span> </th>
                    <th> <span i18n="s_struc_ip"> IP Adress </span> </th>
                    <th> <span i18n="s_struc_parent_node"> Parent Node </span> </th>
                    <th> <span i18n="s_struc_ping"> TTL </span> </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>


</div>
<div style="display: none;">
    <div id="dialog_add_machine_struc">
        <div class="form-group">
            <label class="col-sm-4 control-label" id="machine_name_tag">Name</label>
        </div>
        <div class="col-sm-8 form-group">
            <input class="form-control" id="machine_name" autocomplete="off">
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label" id="machine_type_tag">Type</label>
        </div>
        <div class="col-sm-8 form-group">
            <select class="form-control" id="selector_machine_type">
            </select>
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label" id="machine_ip_tag">IP Address</label>
        </div>
        <div class="col-sm-8 form-group">
            <input class="form-control" id="machine_ip" autocomplete="off">
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label" id="machine_mac_tag">Mac Address</label>
        </div>
        <div class="col-sm-8 form-group">
            <input class="form-control" id="machine_mac" autocomplete="off">
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label" id="machine_pid_tag">Parent Node</label>
        </div>
        <div class="col-sm-8 form-group">
            <select class="form-control" id="selector_machine_pid">
            </select>
        </div>

    </div>
</div>
</body>
<?php control_pagination(); ?>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var d3_flag = false;

var margin = 0,
    width = 0,
    height = 0,
    transfer = 0,
    root = null,
    tree = null,
    diagonal = null,
    svg = null;

var treeData = [];

var str_dict = {
        'nt_server': find_i18n('s_struc_type_01'),
        'nt_switch': find_i18n('s_struc_type_02'),
        'nt_mirror': find_i18n('s_struc_type_03'),
        'nt_client': find_i18n('s_struc_type_04')
    };

function check_d3_exsit(){
    var d3_check = null;
    try {
      d3_check = (typeof d3.layout.tree());
    }
    catch(err) {
    }
    finally {
        if(d3_check == 'function'){
            d3_flag = true;
        }
    }
}

function tree_config(){
    treeData = [];

    margin = {top: 20, right: 120, bottom: 20, left: 120};
    width = 960 - margin.right - margin.left;
    height = 500 - margin.top - margin.bottom;

    transfer = 750;

    tree = d3.layout.tree().size([height, width]);

    diagonal = d3.svg.diagonal().projection(function(d) { return [d.y, d.x]; });

    svg = d3.select("#tree").append("svg")
        .attr("width", width + margin.right + margin.left)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
}
function page_load() {
    check_d3_exsit();
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_table.create($('.tc-table'));
    util_table.enable_pagination($('#page-control'));
    util_table.enable_sort();
    util_table.bind_checked(function($trs) {
        if ($trs.length == 0) {
            $('#crud_control button:nth(1)').prop('disabled', true);
        } else if ($trs.length == 1) {
            $('#crud_control button:nth(1)').prop('disabled', false);
        } else if ($trs.length > 1) {
            $('#crud_control button:nth(1)').prop('disabled', true);
        }
    });
    load_table();
    $('#crud_control button:nth(0)').click(add_machine);
    $('#crud_control button:nth(1)').click(delete_machine);
}

function load_table() {
    util_page.rest_get('/tc/rest/client_struc.php', function(res) {
        if(res.length != 0 && d3_flag == true){
            tree_config();
            treeData = listToTree(res);
            root = treeData[0];
            root.x0 = height / 2;
            root.y0 = 0;

            refresh_tree(root);
            d3.select(self.frameElement).style("height", "490px");
        }
        util_table.load(parse_record(res));
    });
    init_buttons_status();
}

var init_buttons_status = function() {
    $('#crud_control button:nth(1)').prop('disabled', true);
};

 function refresh_tree(tree_body) {

        var tree_nodes_arr = tree.nodes(root).reverse(),
            tree_links = tree.links(tree_nodes_arr);

        tree_nodes_arr.forEach(function(a) { a.y = a.depth * 180; });

        var point = svg.selectAll("g.node")
            .data(tree_nodes_arr, function(a) { return a.id || (a.id = ++i); });

        var point_parent = point.enter().append("g")
            .attr("transform", function(d) { return "translate(" + tree_body.y0 + "," + tree_body.x0 + ")"; })
            .attr("class", "node")
            .on("click", touch);

        point_parent.append("circle")
            .attr("r", 1e-7)
            .style("fill", function(a) { return a._children ? "#b0c4de" : "white"; });

        point_parent.append("text").attr("dy", ".35em")
            .attr("text-anchor", function(a) { return a.children || a._children ? "end" : "start"; })
            .attr("x", function(a) { return a.children || a._children ? -13 : 13; })
            .text(function(a) { return a.name; })
            .style("fill-opacity", 1e-7);

        var point_quit = point.exit().transition()
            .duration(transfer)
            .attr("transform", function(d) { return "translate(" + tree_body.y + "," + tree_body.x + ")"; })
            .remove();

        point_quit.select("circle")
            .attr("r", 1e-7);

        point_quit.select("text")
            .style("fill-opacity", 1e-7);

        var point_refresh = point.transition()
            .duration(transfer)
            .attr("transform", function(a) { return "translate(" + a.y + "," + a.x + ")"; });

        point_refresh.select("circle")
            .attr("r", 9)
            .style("fill", function(a) { return a._children ? "#b0c4de" : "white"; });

        point_refresh.select("text")
            .style("fill-opacity", 1);

        var addr = svg.selectAll("path.link")
            .data(tree_links, function(a) { return a.target.id; });

        addr.enter().insert("path", "g")
            .attr("d", function(d) {
                var o = {x: tree_body.x0, y: tree_body.y0};
                return diagonal({source: o, target: o});
            }).attr("class", "link");

        addr.transition()
            .duration(transfer)
            .attr("d", diagonal);

        addr.exit().transition()
            .duration(transfer)
            .attr("d", function(d) {
                var o = {x: tree_body.x, y: tree_body.y};
                return diagonal({source: o, target: o});
            })
            .remove();

        tree_nodes_arr.forEach(function(n) {
            n.x0 = n.x;
            n.y0 = n.y;
        });
    }

    function touch(x) {
        if (!x.children) {
            x.children = x._children;
            x._children = null;
        } else {
            x._children = x.children;
            x.children = null;
        }
        refresh_tree(x);
    }

function listToTree(list) {
    var map = {},
        node,
        tree= [],
        i;
    for (i = 0; i < list.length; i ++) {
        map[Number(list[i].id)] = list[i];
        list[i].children = [];
    }
    for (i = 0; i < list.length; i += 1) {
        node = list[i];
        if (node.pid !== '-1') {
            map[node.pid].children.push(node);
        } else {
            tree.push(node);
        }
    }
    return tree;
}

function parse_record(result) {
    var records = [];
    $.map(result, function(r) {
        let p_Node = null;
        if(r.pid == -1){
            p_Node = "BASE";
        }else{
            for(let i in result){
                if(result[i].id == r.pid){
                    p_Node = result[i].name;
                }
            }
        }
        var str_struc_type = str_dict[r.type];
        records.push({
            key: r.id,
            row: [
                r.name,
                str_struc_type,
                r.mac_addr,
                r.ip_addr,
                p_Node,
                r.ping
            ],
            checkable: true
        });
    });
    return records;
}



var str_dict = {
        'nt_server': find_i18n('s_struc_type_01'),
        'nt_switch': find_i18n('s_struc_type_02'),
        'nt_mirror': find_i18n('s_struc_type_03'),
        'nt_client': find_i18n('s_struc_type_04')
    };

var add_machine = function() {
    var machine_name_tag = find_i18n('s_struc_name'),
        machine_type_tag = find_i18n('s_struc_type'),
        machine_ip_tag = find_i18n('s_struc_ip'),
        machine_mac_tag = find_i18n('s_struc_mac'),
        machine_pid_tag = find_i18n('s_struc_parent_node');
    $('#machine_name_tag').text(machine_name_tag);
    $('#machine_type_tag').text(machine_type_tag);
    $('#machine_ip_tag').text(machine_ip_tag);
    $('#machine_mac_tag').text(machine_mac_tag);
    $('#machine_pid_tag').text(machine_pid_tag);
    $.map(str_dict, function(val, i) {
        $('#selector_machine_type').append($('<option>', {
            value: i,
            text : val
        }));
    });
    new Promise(function (resolve, reject) {
        util_page.rest_get('/tc/rest/client_struc.php', function(result) {
            $.map(result, function (r) {
                $('#selector_machine_pid').append($('<option>', {
                    value: r.id,
                    text : r.name
                }));
            });
            if(result.length == 0){
                $('#selector_machine_pid').append($('<option>', {
                    value: -1,
                    text : "No parent."
                }));
            }
            resolve("200");
        });
    }).then(function (r) {
            var html = $('#dialog_add_machine_struc').html(),
            save_btn_text = find_i18n('s_save');
            cancel_btn_text = find_i18n('s_cancel');
            var $dialog = util_page.dialog_confirm_builder(html, save_btn_text, cancel_btn_text,function() {
                var machine_data = {
                    'name': $dialog.find('input:nth(0)').val(),
                    'type': $dialog.find('#selector_machine_type :selected').val(),
                    'mac_addr': $dialog.find('input:nth(2)').val(),
                    'ip_addr': $dialog.find('input:nth(1)').val(),
                    'pid': $dialog.find('#selector_machine_pid :selected').val(),
                };
                register_machine_tree(machine_data);
                $("#selector_machine_type").empty();
                $("#selector_machine_pid").empty();
            });
    }).catch(function (reason) {
        console.log('Failed: ' + reason);
    });
}

var register_machine_tree = function(user_data) {
    util_page.rest_post('/tc/rest/client_struc.php', user_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
    });
    location.reload();
};

var delete_machine = function() {
    var bags = util_table.checked(),
        message = find_i18n('c_delete_selected_machine'),
        total_count = bags.length,
        done_count = 0,
        error_count = 0,
        error_lines = '';

    util_page.dialog_confirm(message, function(){
        var urls = $.map(bags, function(bag){
            var url = '/tc/rest/client_struc.php/' + bag.key;
            util_page.rest_delete(url, {}, function() {
                done_count += 1;
                if (total_count === done_count + error_count) {
                    if (error_count === 0) {
                        util_page.dialog_message_i18n('s_update_done_ok');
                    } else {
                        util_page.dialog_message(error_lines);
                    }
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
                }
            });
            location.reload();
        });
    });
};

$(document).ready(page_load);

</script>

<?php standard_page_end(); ?>

