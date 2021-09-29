<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('about');
?>

<div class="container-fluid container_table">
    <div class="row row_div">
        <div class="col-xs-12">
            <div class="btn-toolbar" role="toolbar">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default">
                        <span i18n="s_lic_enter_lic" id="lic_enter_lic">Enter License</span>
                    </button>
                    <button type="button" class="btn btn-default">
                        <span i18n="s_lic_gen_key" id="lic_gen_key">Generate Key</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="padding-right: 30px;">
        <div class="col-xs-6">
            <table class="table table-striped about_table" style="margin-top: 20px;" id="left_table">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_status">License Status</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_client_count">Client Count</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_issuer_contact">Issuer Contact</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_issuer_telephone">Issuer Telephone</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_user_name">User Name</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_user_email">User Email</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_user_address">User Address</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_release_build">Build</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-xs-6">
            <table class="table table-striped about_table" style="margin-top: 20px;" id="right_table">
                <tbody>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_expired_time">Expired Date</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_issuer_name">Issuer Name</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_issuer_email">Issuer Email</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_issuer_address">Issuer Address</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_user_contact">User Contact</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_lic_user_telephone">User Telephone</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                    <tr>
                        <td class="about_title"><img src="/tc/images/details.png"/><span i18n="s_release_name">Release Name</span> : </td>
                        <td class="about_value"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
<style>
    .modal-footer{
        display:block;
    }
    .table>tbody>tr>td{
        border-bottom:0;
    }
    .table>tbody>tr>.about_value{
        border-bottom: 1px solid #C2C2C2;
    }
</style>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";
function load_release_table(data) {
    var $table = $('#right_table');

    $table.find('td:nth(1)').text(data.license_nature.expire);
    $table.find('td:nth(3)').text(data.license_profile.i_name);
    $table.find('td:nth(5)').text(data.license_profile.i_email);
    $table.find('td:nth(7)').text(data.license_profile.i_addr);
    $table.find('td:nth(9)').text(data.license_profile.u_contact);
    $table.find('td:nth(11)').text(data.license_profile.u_tel);
    $table.find('td:nth(13)').text(data.release);

}

function load_license_table(data) {
    var $table = $('#left_table');

    $table.find('td:nth(1)').text(find_i18n(data.license_status));
    if (data.license_status == 'ls_verified') {
        $table.find('td:nth(3)').text(data.license_nature.ccount);
        $table.find('td:nth(5)').text(data.license_profile.i_contact);
        $table.find('td:nth(7)').text(data.license_profile.i_tel);
        $table.find('td:nth(9)').text(data.license_profile.u_name);
        $table.find('td:nth(11)').text(data.license_profile.u_email);
        $table.find('td:nth(13)').text(data.license_profile.u_addr);
        $table.find('td:nth(15)').text(data.revision);
    }

}


$(document).ready( function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    util_page.rest_get('/tc/rest/system.php/license', function(data) {
        load_license_table(data);
        load_release_table(data);
    });
    var $button = $('button');

    $('#lic_enter_lic').click(function() {
        var options = {
            title: find_i18n('s_lic_lic'),
            message: '<textarea rows="10" class="license-text" style="width:407px;"></textarea>'
        };
        var $dialog = util_page.dialog_message2(options, function() {
            var reg_data = {
                'license2': $dialog.find('textarea').val()
            };
            util_page.rest_post('/tc/rest/system.php', reg_data, function() {
                util_page.dialog_message(find_i18n('s_lic_reg_done'), util_page.page_refresh);
            });
        });
    });

    $('#lic_gen_key').click(function() {
        util_page.rest_get('/tc/rest/system.php/license', function(data) {
            var options = {
                title: find_i18n('s_lic_key'),
                message: '<xmp>' + data.license_key_base64 + '</xmp>'
            };
            util_page.dialog_message2(options);
        });
    });
});

</script>

<?php standard_page_end(); ?>
