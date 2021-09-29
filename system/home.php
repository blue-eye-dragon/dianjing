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

    standard_page_begin('home');
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_user_edit_passwd"),
                    array("i18n" => "s_user_edit_status"),
                    array("i18n" => "s_user_edit_display"),
                ),
            ),
        ),
        "id" => "home_button",
    );
    echo html_toolbar($bar);
?>

<div class="container-fluid container_table">

<div class="row no-gutters row_div">
	<div class="container-fluid col-md-3 col-lg-3" id="server-side" style="max-width: 280px;">
        <h4>
            <span i18n="s_info_main">User Information</span>
        </h4>
        <table class="info-table dashboard-table" id="user_info">
            <tbody>
                <tr>
                    <td><span i18n="s_username">User Name</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span i18n="s_user_display">Display Name</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span i18n="s_group">User Group</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span i18n="s_ps_frozen">Storage Frozen</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span i18n="s_user_status">User Status</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span i18n="s_user_online">User Online</span></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="dialog_reset_password" hidden>
    <div>
        <h4>
            <span i18n="s_password_reset"> Reset password for current user </span> 
        </h4>
        <label for="old_password" i18n="s_password_old">Old Password</label>
        <input type="password" name="old_password" class="form-control" required>
        <label for="new_password" i18n="s_password_new">New Password</label>
        <input type="password" name="new_password" class="form-control" required maxlength="16">
        <label for="repeat_password" i18n="s_password_repeat">Type Again</label>
        <input type="password" name="repeat_password" class="form-control" required maxlength="16">
    </div>
</div>


<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var uid = <?php echo $_SESSION['uid'] ?>,
    user_name = '<?php echo $_SESSION["user_name"] ?>',
    user_url = "/tc/rest/user.php/" + uid;

var fill_user_information = function(uinfor) {
    var enabled_text = '',
        frozen_text = '',
        onlie_text = '';
    if (uinfor.enabled === '1') {
        enabled_text = find_i18n('s_user_enabled');
    } else {
        enabled_text = find_i18n('s_user_disabled');
    }
    if (uinfor.online) {
        onlie_text = find_i18n('s_yesno_yes');
    } else {
        onlie_text = find_i18n('s_yesno_no');
    }
    var frozen = uinfor.storage_frozen ? JSON.parse(uinfor.storage_frozen) : false;
    frozen_text = frozen ? find_i18n('s_yesno_yes') : find_i18n('s_yesno_no');

    $("#user_info td")
        .eq(1).text(uinfor.name).end()
        .eq(3).text(uinfor.display).end()
        .eq(5).text(uinfor.group_name).end()
        .eq(7).text(frozen_text).end()
        .eq(9).text(enabled_text).end()
        .eq(11).text(onlie_text).end();
}


var change_home_user_status = function(user_data) {
    //var urls = '/tc/rest/user.php/' + uid;
    util_page.rest_put(user_url, user_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_user_info();
    });
};

var load_user_info = function(){
    util_page.rest_get(user_url, fill_user_information);
}

var change_user = function(user_data) {
    //var urls = '/tc/rest/user.php/' + uid;
    util_page.rest_put(user_url, user_data, function() {
        util_page.dialog_message_i18n('s_update_done_ok');
        load_user_info();
    });
};

var change_home_display_name = function() {
    var title = find_i18n('s_input_new_display_name');
    var $dialog = util_page.dialog_prompt_required(title, function(display) {
        change_user({'pname': display});
    });
    $dialog.find('input').attr('maxlength', '30');
};

$(document).ready( function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    load_user_info();

    //$('#home_button button:nth(0)').click(ps_add_group);
    $('#home_button button:nth(0)').click(function() {
        util_page.navi_page("dashboard_page.php");
    });

    $('#home_button button:nth(1)').click(function() {
        var selections = util_page.dialog_select_selections('YN');
        util_page.dialog_select(find_i18n('s_user_enable'), selections, function(sel) {
            change_home_user_status({enable: sel});
        });
    });

    $('#home_button button:nth(2)').click(change_home_display_name);

});
</script>

<?php standard_page_end(); ?>


