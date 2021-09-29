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

    standard_page_begin('backup');
?>

<div class="container-fluid container_table">
<!-- row for toolbar -->
    <div class="row row_div">
<?php
    $bar = array(
        "button_groups" => array(
            array(
                "buttons" => array(
                    array("i18n" => "s_backup_start", "icon" => "glyphicon-arrow-down"),
                    array("i18n" => "s_backup_preview", "icon" => "glyphicon-eye-open"),
                ),
            ),
            array(
                "buttons" => array(
                    array("i18n" => "s_backup_settings", "icon" => "glyphicon-edit"),
                ),
            ),
        ),
        "id" => "main",
    );
    echo html_toolbar($bar);
?>
    </div>

    <div class="row top5 row_div">
        <div class="col-xs-12" style="padding-left: 17px;">
            <h4 class="title_span border_right"><span i18n="s_backup_settings" style="padding-left:15px"></span></h4>
            <dl id="dl_info">
                <dt><span i18n="s_backup_time">Last Finished Date</span></dt>
                <dd></dd>
                <dt><span i18n="s_backup_mode"></span></dt>
                <dd style="border-right: 1px solid #D6D6D6;"></dd>
                <dt><span i18n="s_backup_headless"></span></dt>
                <dd></dd>
                <dt><span i18n="s_backup_interval"></span></dt>
                <dd style="border-right: 1px solid #D6D6D6;"></dd>
                <dt><span i18n="s_backup_remote_location"></span></dt>
                <dd></dd>
                <dt><span i18n="s_backup_local_location"></span></dt>
                <dd style="word-wrap: break-word;border-right: 1px solid #D6D6D6;"></dd>
            </dl>
        </div>

        <div class="col-xs-12">
            <h4 class="title_span border_right"><span i18n="s_backup_logs" style="padding-left:15px"></span></h4>
            <div class="well" id="backup_logs">
            </div>
        </div>
    </div>

</div>

<div id="dialog_backup_settings" style="display: none">
    <h4><span i18n="s_backup_settings"></span></h4>
    <div class="form-group">
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default">
                <input type="radio" name="backup_mode" value="local">
                <span i18n="s_backup_local"> :</span>
            </label>
            <label class="btn btn-default">
                <input type="radio" name="backup_mode" value="remote">
                <span i18n="s_backup_remote"> :</span>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label><span i18n="s_backup_local_location"></span> :</label>
        <input type="text" class="form-control" id="sb_location">
    </div>
    <div class="form-group">
        <label><span i18n="s_backup_interval"></span> :</label>
        <input type="number" class="form-control" id="sb_interval">
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="sb_headless">
            <span i18n="s_backup_headless"></span>
        </label>
    </div>
</div>
<style>
    .modal-footer{
        display:block;
        margin-top:0;
    }
    .form-group .form-control{
        margin-left:15px
    }
    .bootbox-body .form-group label{
        text-align: right;
        min-width: 125px;
    }
</style>

<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

function backup_edit_settings() {
    var settings = $('#main').data('settings');
    var $dialog = util_page.dialog_confirm_builder(
        $('#dialog_backup_settings').html(),
        find_i18n('s_save'),
        find_i18n('s_cancel'),
        function() {
            var data = {
                mode: $dialog.find('input[name=backup_mode]:checked').val(),
                backup_headless: $dialog.find('#sb_headless').prop('checked'),
                backup_interval: $dialog.find('#sb_interval').val()
            };
            if (data.mode === 'remote') {
                data['remote'] = $dialog.find('#sb_location').val();
            }
            if (data.mode === 'local') {
                data['local'] = $dialog.find('#sb_location').val();
            }
            util_page.rest_put('/tc/rest/backup.php', data, function() {
                update_backup_settings();
            });
        }
    );
    $dialog.find('.btn-group').change( function() {
        var mode = $('input[name=backup_mode]:checked').val(),
            text = find_i18n('s_backup_local_location'),
            location = settings.backup_local_location;
        if (mode === 'remote') {
            text = find_i18n('s_backup_remote_location');
            location = settings.backup_remote_location;
        }
        $dialog.find('.form-group:nth(1) span').text(text);
        $dialog.find('#sb_location').val(location);
    });

    if (settings.backup_mode === 'local') {
        $dialog.find('input[name=backup_mode]:nth(0)').click();
        $dialog.find('#sb_location').val(settings.backup_local_location);
    } else {
        $dialog.find('input[name=backup_mode]:nth(1)').click();
        $dialog.find('#sb_location').val(settings.backup_remote_location);
    }

    $dialog.find('#sb_interval').val(settings.backup_interval);
    $dialog.find('#sb_headless').prop('checked', settings.backup_headless);
}

function backup_start_backup() {
    util_page.rest_post('/tc/rest/backup.php', {}, function() {
        setTimeout(update_backup_settings, 1000);
        update_backup_progress();
    });
}

function update_backup_settings() {
    util_page.rest_get('/tc/rest/backup.php', function(res) {
        $('#main').data('settings', res);

        if (res.mtime) {
            $('#dl_info dd:nth(0)').text(res.mtime);
        } else {
            $('#dl_info dd:nth(0)').html('<br>');
        }

        var backup_mode = find_i18n('s_backup_local');
        if (res.backup_mode === 'remote') {
            backup_mode = find_i18n('s_backup_remote');
        }
        $('#dl_info dd:nth(1)').text(backup_mode);

        if (res.backup_interval) {
            $('#dl_info dd:nth(3)').text(res.backup_interval);
        } else {
            $('#dl_info dd:nth(3)').html('--');
        }

        if (res.backup_headless) {
            $('#dl_info dd:nth(2)').text(find_i18n('s_yesno_yes'));
        } else {
            $('#dl_info dd:nth(2)').html(find_i18n('s_yesno_no'));
        }

        if (res.backup_remote_location) {
            $('#dl_info dd:nth(4)').text(res.backup_remote_location);
        } else {
            $('#dl_info dd:nth(4)').html('<br>');
        }
        if (res.backup_local_location) {
            $('#dl_info dd:nth(5)').text(res.backup_local_location);
        } else {
            $('#dl_info dd:nth(5)').html('<br>');
        }
    });
}

function update_backup_progress(onload) {
    util_page.rest_get('/tc/rest/backup.php/progress', function(progress) {
        if (progress && progress.hasOwnProperty('trans_pct')) {
            var percent = progress.trans_pct;
            if (percent === '100%') {
                // call update settings with 1 second delay as settings refresh
                setTimeout(update_backup_settings, 1000);
            } else {
                setTimeout(update_backup_progress, 1000);
            }
            var message = percent;
            if (progress.hasOwnProperty('trans_speed')) {
                message += ' ' + progress.trans_speed;
            }
            util_page.update_main_area_title(message);
        } else {
            if (onload) {
                // use log refresh interval if nothing to do when page is on load
                setTimeout(update_backup_progress, 5000);
            } else {
                // some error here, try again
                setTimeout(update_backup_progress, 1000);
            }
        }
    }, function(error) {
        util_page.dialog_message_error(error);
    });
}

var backup_show_preview = function() {
    var label = find_i18n('s_general_ok'),
        html = '<h4>'+ find_i18n('s_backup_contents') + '</h4>',
        style = 'margin-top: 15px;margin-bottom: 0px;';

    style += 'max-height: 200px; overflow-y: scroll;';
    html += '<div class="well" style="' + style + '"></div>';
    var $dialog = util_page.dialog_confirm_builder(html, label, find_i18n('s_cancel'), $.noop);

    util_page.rest_get('/tc/rest/backup.php/list', function(files) {
        $.map(files, function(f) {
            var $li = $('<div></div>');
            $('<span></span>').text(f.file_path).appendTo($li);

            if (f.file_mode.charAt(0) === 'd') {
                // hide all directories
                return;
            } else {
                $('<span></span>')
                    .addClass('pull-right')
                    .text(util_page.print_size(f.file_size, 'B'))
                    .appendTo($li);
            }
            $dialog.find('.well').append($li);
        });
    });
}

var update_backup_logs = function() {
    // append to the log output every 3 seconds
    // only new lines will be appended
    var append_backup_logs = function(lines) {
        var $text = $('#backup_logs');
        $text.find('span').css('font-weight', 'normal');
        // lines maybe "" if nothing here
        if (lines) {
            $.map(lines, function(line) {
                var $line = $('<span></span></br>');
                if (line.indexOf('SUCCESS') >= 0) {
                    $line.addClass('log-success');
                } else if (line.indexOf('FAILURE') >= 0 || line.indexOf('ERROR') >= 0 ) {
                    $line.addClass('log-error');
                }
                $line.text(line).css('font-weight', 'bold').appendTo($text);
            });
            $text.scrollTop($text[0].scrollHeight);
        }
    }

    var fetch_new_logs = function() {
        util_page.rest_get('/tc/rest/backup.php/logs?since=5s', append_backup_logs);
        setTimeout(fetch_new_logs, 5000);
    }

    util_page.rest_get('/tc/rest/backup.php/logs', function(res) {
        append_backup_logs(res);
        $('#backup_logs span').css('font-weight', 'normal');
        setTimeout(fetch_new_logs, 5000);
    });
}

$(document).ready( function() {
    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    $('#main button:nth(0)').click(backup_start_backup);
    $('#main button:nth(1)').click(backup_show_preview);
    $('#main button:nth(2)').click(backup_edit_settings);

    update_backup_settings();
    update_backup_logs();
    update_backup_progress(true);
});

</script>

<?php standard_page_end(); ?>