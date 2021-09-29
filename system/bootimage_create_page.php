<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

    error_reporting(E_ALL);
    require('../libs/libtc.php');
    require("libpage.php");

    standard_page_begin('register_image');
?>

<form class="form-horizontal container_table">
    <div class="row">
        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="row no-gutters" style="margin-top:30px">
                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label for="image-name-input"><span i18n="s_image_name">Image Name</span></label> *
                    </div>
                    <div class="col-xs-5">
                        <input type="text" class="form-control boot_input" id="image-name-input" maxlength="30">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label for="image-desc-input"><span i18n="s_image_desc">Image Description</span></label> *
                    </div>
                    <div class="col-xs-5">
                        <input type="text" class="form-control boot_input" id="image-desc-input" maxlength="40">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label for="image-type-selector"><span i18n="s_image_type">Image Type</span></label> *
                    </div>
                    <div class="col-xs-5">
                        <select class="form-control boot_input" id="image-type-selector">
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label for="image-file-selector"><span i18n="s_image_file">Image File</span></label> *
                    </div>
                    <div class="col-xs-5">
                        <select class="form-control boot_input" id="image-file-selector">
                            <option disabled selected> -- select a file -- </option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label for="image-pic-selector"><span i18n="s_image_pic">Image Picture</span></label> *
                    </div>
                    <div class="col-xs-5">
                        <select class="form-control boot_input" id="image-pic-selector">
                            <option disabled selected> -- select a picture -- </option>          
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <label><span i18n="s_image_ag">Authorized Group</span></label>
                    </div>
                    <div class="col-xs-5">
<!--                         <select multiple class="form-control" id="group-selector">
                        </select>
 -->                    </div>
                </div>

                <div class="form-group">
                    <!-- <div class="col-xs-3 control-label">
                        <span i18n="s_bootimage_ga">Accessible</span>

                    </div> -->
                    <div class="col-xs-3 control-label">
                        <label for="image-type-selector"><span i18n="s_bootimage_ga">Accessible</span></label>
                    </div>
                    <div class="col-xs-5">
                        <select class="form-control boot_input" id="bootimage_ga" multiple name="bootimage_ga">
                        </select>
                    </div>
                    <div class="col-xs-5自动注册规则" style="display:none">
                        <textarea class="form-control boot_input" rows="2" id="group-read" readonly></textarea>
                    </div>
                    <div class="col-xs-1" style="display:none">
                        <button i18n="s_edit" class="btn btn-link btn-xs" id="group-read-edit">Change</button>
                    </div>
                </div>

<!--
                 <div class="form-group">
                    <div class="col-xs-3 control-label">
                        <span i18n="s_bootimage_gw">Personalization</span>
                    </div>
                    <div class="col-xs-7">
                        <textarea class="form-control" rows="2" id="group-write" readonly></textarea>
                    </div>
                    <div class="col-xs-1">
                        <button i18n="s_edit" class="btn btn-link btn-xs" id="group-write-edit">Change</button>
                    </div>
                </div>
 -->
             </div>
        <small><span class="col-sm-12" i18n="s_asterisk_required"  style="margin-left: 187px;color: #999;margin-top: 15px;">* is required</span></small>
    </div>
    <!-- right side picture fields -->
        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="panel panel-default" style="margin-top:30px">
                <div class="panel-heading">
                    <span i18n="s_image_pic_preview">Image Picture Preview</span>
                </div>
                <div class="panel-body" id="picture-panel" style="height: 200px;">
                </div>
            </div>
        </div>
    </div> <!-- row ends -->

    <hr>

    <div class="form-group" style="padding-bottom: 30px;height: 55px;">
        <button type="button" class="btn reset_submit" id="save-button" style="float:left;width:90px;margin-left: 40%;    margin-right: 50px;">
            <span class="glyphicon glyphicon-ok"></span>
            <span i18n="s_save">Save</span>
        </button>
        <button type="button" class="btn reset_cencal" id="cancel-button" style="float:left;width:90px">
            <span i18n="s_cancel">Cancel</span>
        </button>
    </div>
    
</form>
<style>
    .modal-footer {
        display: block;
    }
</style>


<?php standard_page_mid(); ?>

<script language="javascript">

"use strict";

var uid = <?php echo $_SESSION["uid"] ?>,
    user_name = '<?php echo $_SESSION["user_name"] ?>';

function validate(image_data) {
    if (image_data.file == null) {
       return i18n_strings['e_no_file'] || 'Error: No file selected';
    }
    if (image_data.picture == null) {
        return i18n_strings['e_no_image_pic'] || 'Error: No picture selected';
    }
    return;
}


$(document).ready(function(){

    util_page.disable_cache();
    util_page.enable_locale();
    util_page.render_sidebar();

    // update words in options of selector, because only text allowed in option tag, no span at all
    $('#image-file-selector').find(':disabled').text(i18n_strings['s_select_file']);
    $('#image-pic-selector').find(':disabled').text(i18n_strings['s_select_picture']);

    util_page.rest_get('/tc/rest/group.php', function(result){
        var groups = {};
        $.map(result, function(group){
            groups[group.id] = group;
        });
        $('#group-read').data('groups', JSON.stringify(groups));

        load_page();
    }, util_page.dialog_message_error);

});

var load_page = function() {

    // load boot image data from server
    util_page.rest_get('/tc/rest/bootimage2.php', function(result){
        // fill boot image file selector
        for (var idx in result.files) {
            var $opt = $('<option></option>'),
                $img = result.files[idx];
            if(!$img.registered) {
                $opt.text($img.name);
                $('#image-file-selector').append($opt);
            }
        }

        if (result.sync.length > 0) {
            var $optg = $('<optgroup label="' + i18n_strings['s_sync_image'] + '"></optgroup>');
            for (var idx2 in result.sync) {
                $('<option></option>')
                    .text(result.sync[idx2].name)
                    .attr('data-sync', true)
                    .appendTo($optg);
                $('#image-file-selector').append($optg);
            }
        }

        // fill boot image picture selector
        for (var idx in result.pictures) {
            var $opt = $('<option></option>'),
                $pic = result.pictures[idx];

            $opt.text($pic.name);
            $opt.data('src', '..' + $pic.path);
            $('#image-pic-selector').append($opt);
        }

        $.map(result.types.split(','), function(tname){
            $('<option></option>')
                .text(tname)
                .appendTo($('#image-type-selector'));
        });
        //$('#image-pic-selector').append('<option value="-31"> -- upload a picture -- </option>');
    });

    // bind image picture selector controller
    $('#image-pic-selector').change(function() {
        $('#picture-panel').empty();

        if ($(this).val() == -31) {
            // upload a picture to server
            $('#upload-picture-input').trigger('click');
        } else {
            // select a picture on server
            var line = '<img src="' + $(this).find(':selected').data('src') + '"></img>';
            $('#picture-panel').html(line);
        }
    });


    $('#upload-picture-form').on('submit', (function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('upload-picture-input', $('#upload-picture-input').val());

        $.ajax({
            type: 'POST',
            url: 'upload_picture.php',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data){
                console.log('success');
                console.log(data);
            },
            error: function(data){
                console.log('error');
                console.log(data);
            }
        });
    }));

    $('#upload-picture-input').on('change', function() {
        $('#upload-picture-form').submit();
    });        

    var groups = JSON.parse($('#group-read').data('groups'));
    var readable_gids = $.map(groups, function(g){
        return parseInt(g.id);
    });

    var list_items = '';
    // $.map(groups, function(o){
    //     list_items += '<li class="list-group-item"';
    //     list_items += ' data-id="' + o.id + '"';
    //     list_items += '>';
    //     list_items += o.name;
    //     list_items += '<span class="badge">';
    //     list_items += o.member_count;
    //     list_items += '</span></li>';
    //     return o.name;
    // });
    $.map(groups, function(tname){
        $('<option value=' + tname.id + ' label='+ tname.name +'></option>')
            .text(tname.name)
            .appendTo($('#bootimage_ga'));
    });
    $("#bootimage_ga").val("")
    var list_group_html = '<ul class="list-group">' + list_items + '</ul>';

    function extract_name_list(gids) {
        var names = $.map(gids, function(gid){
            return groups[gid].name;
        });
        return names.join(' ');
    }

    $('#group-read').text(extract_name_list(readable_gids));

    $('#group-read-edit').click(function(e){
        e.preventDefault();

        // var $dialog = bootbox.dialog({
        //     message: list_group_html,
        //     buttons: {
        //         ok: {
        //             label: 'OK',
        //             callback: function() {
        //                 readable_gids = [];
        //                 $dialog.find('.list-group-item.active').each(function(){
        //                     readable_gids.push($(this).data('id'));
        //                 });
        //                 $('#group-read').text(extract_name_list(readable_gids));
        //             }
        //         }
        //     }
        // });

        $dialog.find('.list-group-item')
            .on('click', function(){
                $(this).toggleClass('active');
            }).each(function(){
                if ($.inArray($(this).data('id'), readable_gids) >= 0) {
                    $(this).addClass('active');
                }
            });

        return false;
    });

    var do_save = function() {
        var groups = $("select[name='bootimage_ga']").val();
        var image_data = {
            'name': $.trim($('#image-name-input').val()),
            'file': $('#image-file-selector').val(),
            'picture': $('#image-pic-selector').val(),
            'agroups': groups,
            'desc': $.trim($('#image-desc-input').val()),
            'sync_image': false,
            'ostype': $('#image-type-selector').val()
        };

        if ($('#image-file-selector :selected').data('sync')) {
            image_data['sync_image'] = true;
        }

        var $error = validate(image_data);
        if ($error) {
            util_page.dialog_message($error);
            return;
        }

        util_page.rest_post(
            '/tc/rest/bootimage2.php',
            image_data,
            function(result) {
                var iid = parseInt(result);
                if ($.isNumeric(iid)) {
                    util_page.navi_page('client_image.php');
                } else {
                    util_page.navi_page('bootimage_page.php');
                }
            }
        );
    }

    $('#save-button').click(function() {
        if ($.isEmptyObject(readable_gids)) {
            util_page.dialog_confirm(find_i18n('c_ci_no_group_selected'), do_save);
        } else {
            do_save();
        }
    });
    $('#cancel-button').click(function(){
        util_page.navi_page('bootimage_page.php');
    });
}

</script>
<?php standard_page_end(); ?>
