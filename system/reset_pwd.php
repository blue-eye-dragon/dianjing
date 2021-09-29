<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);

    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('reset_pwd');
?>

<div class="container">
    <div class="row">
        <div class="col-xs-5">
            <form action="#" class="reset_pwd-form" id="reset_pwd_form">
                <div class="form-group">
                    <h4><div  id="pwd_less_10" class="alert alert-danger" role="alert" style="display:none;"></div></h4>
                </div>
                <div class="form-group">
                    <div  id="pwd_not_match" class="alert alert-danger" role="alert" style="display:none;" ></div>
                </div>
                <div class="form-group">
                    <label for="old_password" class="sr-only">Old Password</label>
                    <input type="password" class="form-control" id="old_password" placeholder="Old Password" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="new_password" class="sr-only">New Password</label>
                    <input type="password" class="form-control" id="new_password" placeholder="New Password" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="repeat_password" class="sr-only">Re-type Password</label>
                    <input type="password" class="form-control" id="repeat_password" placeholder="Re-type New Password" autocomplete="off">
                </div>
                <div class="form-group" id="reset_pwd_tips">
                    <p><span class="glyphicon glyphicon-warning-sign" style="color:#FFC683; margin-right: 3px;"></span></p>
                </div>
                <div class="form-group">
                    <input id="reset_pwd_submit" type="submit" class="btn btn-primary btn-reset-pwd">
                </div>
            </form>
            <!-- END Sign In Form -->

        </div>
    </div>
</div>

<?php standard_page_mid(); ?>

<script type="text/javascript">

"use strict";

var uid = <?php echo $_SESSION["uid"] ?>,
    user_name = '<?php echo $_SESSION["user_name"] ?>',
    user_url = "/tc/rest/user.php/" + uid;

$(document).ready(function(){
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    var old_password = find_i18n('s_password_old'),
        new_password = find_i18n('s_password_new'),
        retype_password = find_i18n('s_password_retype'),
        pwd_less_10 = find_i18n('s_password_rule'),
        pwd_not_match = find_i18n('e_bad_password_mismatch');

    $('#old_password').attr("placeholder", old_password);
    $('#new_password').attr("placeholder", new_password);
    $('#repeat_password').attr("placeholder", retype_password);
    $('#reset_pwd_submit').val(find_i18n('s_password_reset'));
    $('#reset_pwd_tips p').append(pwd_less_10);

    $('form').submit(function(event) {
        event.preventDefault();

        var old_password = $('#old_password').val(),
            new_password = $('#new_password').val(),
            repeat = $('#repeat_password').val();

        if (new_password.length < 10 || new_password.length > 32) {
            $('#pwd_not_match').hide();
            $('#pwd_less_10').text(pwd_less_10).show();
            return false;
        }
        if (new_password !== repeat) {
            $('#pwd_less_10').hide();
            $('#pwd_not_match').text(pwd_not_match).show();
            return false;
        }

        util_page.hash_password(user_name, old_password)
            .then(function(hash_result_old) {

            util_page.hash_password(user_name, new_password)
                .then(function(hash_result_new) {

                var data = {
                    password: hash_result_old,
                    password_new: hash_result_new
                };
                util_page.rest_put(user_url, data, function() {
                    util_page.dialog_message2(
                        {
                            message: find_i18n('s_password_reset_done')
                        },
                        function() {
                            util_page.navi_page("dashboard_page.php");
                        }
                    );
                });
            });
        });

    });

});
</script>