/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/
var uuid,user_name,user_url;
$(document).ready(function(){
    // util_page.disable_cache();
    // util_page.enable_locale();
    // util_page.render_sidebar();

    var domain = window.location.host;
    var url = window.location.href.split(domain)[1];
    if(url.indexOf("client_image.php")!= -1){
        $("#back_last").attr('href','/tc/system/bootimage_page.php');
    }else if(url.indexOf("client_detail.php")!= -1){
        $("#back_last").attr('href','/tc/system/client_page.php');
    }else if(url=='/tc/system/bootimage_create_page.php'){
        $("#back_last").attr('href','/tc/system/bootimage_page.php');
    }else{
        if(localStorage.getItem("historyUrl")){
            $("#back_last").attr('href',localStorage.getItem("historyUrl"));
        }
    }
　　 localStorage.setItem("url",url); 
    
　　console.log(localStorage.getItem("url"));//输出
    
    if(localStorage.getItem("text")){
        $("#second_name").text(localStorage.getItem("text"));
        $("#second_name").attr('href',localStorage.getItem("url"));
        $("#second_name_li").find('a').css('color', '#fff');
    }
    if(localStorage.getItem("first_name")){
        $("#first_name").text(localStorage.getItem("first_name"));
    }
    if( localStorage.getItem("url")=='/tc/system/bootimage_create_page.php'){
        $("#detail_name").text('注册镜像');
        $("#detail_name_li").css('display','inline-block');
        $("#detail_name_li").find('a').css('color', '#fff');
        $("#second_name_li").find('a').css('color', '#999');
    }
    if( localStorage.getItem("url").indexOf("client_image.php")!= -1 || localStorage.getItem("url").indexOf("client_detail.php")!= -1){
        $("#detail_name").text(localStorage.getItem("bootimage_name")+'_'+find_i18n('s_detail'));
        $("#detail_name_li").css('display','inline-block');
        $("#detail_name_li").find('a').css('color', '#fff');
        $("#second_name_li").find('a').css('color', '#999');
        if(localStorage.getItem("url").indexOf("client_image.php")!= -1){
            $("#second_name").attr('href','/tc/system/bootimage_page.php');
        }else{
            $("#second_name").attr('href','/tc/system/client_page.php');
        }
    }
    if( localStorage.getItem("url") == '/tc/system/dashboard_page.php'){
        $("#first_name").text('仪表盘');
        $("#dashbaord_page").css('background-color','#110BF5');
        $("#dashbaord_page").css('color','#fff');
        $("#dashbaord_page_icon").css('color','#fff');
        $("#dashbaord_page_icon").css('background','url(/tc/images/dashboard_backup_2.png) no-repeat');
        $("#detail_name_li").css('display','none');
        $("#second_name_li").css('display','none');
        $("#back_last").css('display','none');
        $(".breadcrumb").css('display','none');
    
    }else{
        $("#second_name_li").css('display','inline-block');
        // $("#back_last").css('display','inline-block');
        $(".breadcrumb").css('display','inline-block'); 
    }

    if( localStorage.getItem("url") == '/tc/system/about.php'){
        $("#first_name").text('版本管理');
        $("#first_name_li").find('a').css('color', '#999');
        $("#detail_name_li").css('display','none');
        $("#second_name_li").css('display','none');
        $("#version_manage_page").css('background-color','#110BF5');
        $("#version_manage_page").css('color','#fff');
        $("#version_manage_page_icon").css('color','#fff');
        $("#version_manage_page_icon").css('background','url(/tc/images/dashboard_backup_2.png) no-repeat');
        // $("#back_last").css('display','none');
    
    }
    
    
});

var fill_user_information = function(uinfor) {
    var enabled_text = '',
        frozen_text = '',
        onlie_text = '';
    if (uinfor.enabled === '1') {
        enabled_text = find_i18n('s_user_enabled');
        $("#user_status").val(true);
    } else {
        enabled_text = find_i18n('s_user_disabled');
        $("#user_status").val(false);
    }
    if (uinfor.online) {
        onlie_text = find_i18n('s_yesno_yes');
    } else {
        onlie_text = find_i18n('s_yesno_no');
    }
    var frozen = uinfor.storage_frozen ? JSON.parse(uinfor.storage_frozen) : false;
    frozen_text = frozen ? find_i18n('s_yesno_yes') : find_i18n('s_yesno_no');

    $("#user_display").val(uinfor.display);
    
    $("#set_user_info td")
        .eq(1).text(uinfor.name).end()
        .eq(5).text(uinfor.group_name).end()
        .eq(7).text(frozen_text).end()
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

$('#sub_title a').click(function () {
    //aria-expanded status returns old status with transfer before.
    var a_href = $(this).attr('href');
    if(a_href.length > 10){//sub header
        a_href = $(this).parent().parent().parent().children('a').attr('href');
        localStorage.setItem(a_href, 1);
    }else{//header
        if($(this).attr('aria-expanded') == 'true' || localStorage.getItem(a_href) == 1) {
            localStorage.removeItem(a_href);
        }else{
            localStorage.setItem(a_href, 1);
        }
    }
});

var util_page = (function() {
    var __debug = false;

    var is_alphanumeric = function(str) {
        var pattern = /[a-zA-Z0-9]/;

        for (var i = 0; i < str.length; i++) {
            if (!pattern.test(str.charAt(i))) {
                return false;
            }
        }
        return true;
    };

    var is_empty = function(str) {
        return str.replace(/(^\s*)|(\s*$)/g, '') == '';
    };

    var is_mac = function(str) {
        var search = /(?:[A-Fa-f0-9]{2}-){5}[A-Fa-f0-9]{2}/i;
        return search.test(str);
    };

    var parse_mac = function(str) {
        // standard format: 00-E0-B4-16-40-8C
        // other format: 00:E0:B4:16:40:8C
        //               00E0B416408C
        str = str.toUpperCase();
        var value = str;
        if (str.length == 12) {
            value = new Array(6);
            value[0] = str.substr(0, 2);
            value[1] = str.substr(2, 2);
            value[2] = str.substr(4, 2);
            value[3] = str.substr(6, 2);
            value[4] = str.substr(8, 2);
            value[5] = str.substr(10, 2);
            value = value.join('-');
        }
        if (str.length == 17) {
            value = str.replace(':', '-');
        }
        if (is_mac(value)) {
            return value;
        }
        return null;
    };

    var array_filter = function(arr, fn_filter) {
        var result = [];
        $.map(arr, function(a){
            var res = fn_filter(a);
            if (res) {
                result.push(a);
            }
        });
        return result;
    };

    var count_repeat = function(arr) {
        var counters = {};
        // setup unique object counter
        $.map(arr, function(o){
            counters[o] = 0;
        });

        $.map(arr, function(o){
            counters[o] += 1;
        });
        return counters;
    };

    var print_size = function(size, unit) {
        // remove comma
        if (typeof size === 'string') {
            size = size.replace(/,/g, '');
        }
        var _size = parseInt(size),
            next_unit = {
                'B': 'KiB',
                'KiB': 'MiB',
                'MiB': 'GiB',
                'GiB': 'TiB'
            };

        while (_size > 1024) {
            if (unit === 'TiB') {
                break;
            }
            _size = _size / 1024;
            unit = next_unit[unit];
        }
        return parseFloat(_size.toFixed(1)) + ' ' + unit;
    };

    var print_speed = function(speed, unit) {
        var _speed = parseInt(speed),
            next_unit = {
                'bps': 'Kbps',
                'Kbps': 'Mbps',
                'Mbps': 'Gbps'
            };

        while (_speed > 1000) {
            if (unit === 'Gbps') {
                break;
            }
            _speed = _speed / 1000;
            unit = next_unit[unit];
        }
        return _speed.toFixed(2) + ' ' + unit;
    };

    var print_time = function(seconds) {
        var hours = Math.floor(seconds / 3600 ),
            mins = Math.floor((seconds - hours * 3600) / 60),
            secs = Math.floor(seconds - mins * 60 - hours * 3600);
        return hours + ':' + mins + ':' + secs;
    };

    var safe_invoke = function(fn, params) {
        if (fn) {
            fn(params);
        }
    };

    var rest_do_request = function(options, fn_done, fn_error) {
        $.ajax(options).done( function(res) {
            debug_print('rest_requests done', res);
            if (res.success) {
                safe_invoke(fn_done, res.result);
            } else {
                safe_invoke(fn_error, res.result);
            }
        }).fail( function(jqXHR, textStatus, errorThrown) {
            debug_print('rest_request ajax error', jqXHR);
            debug_print('rest_request ajax error', textStatus);
            debug_print('rest_request ajax error', errorThrown);

            if (jqXHR.readyState == 0) {
                // connection error, readyState is 4, if HTTP error
                safe_invoke(fn_error, find_i18n('e_ajax_failed'));
                return;
            }

            if (jqXHR.status == 200) {
                if (textStatus === 'parsererror') {
                    textStatus = jqXHR.responseText;
                }
            } else {
                textStatus = jqXHR.statusText;
            }

            safe_invoke(fn_error, textStatus);
        });
    };

    var rest_requests = function(urls, rest, data, fn_done, fn_error) {
        debug_print('rest_requests', urls);

        var count = 0;
        var rest_req = function(url) {
            var options = {
                url: url,
                type: rest,
                contentType: 'application/json',
                dataType: 'json'
            };
            if (data) {
                options['data'] = JSON.stringify(data);
            }
            $.ajax(options).done(function(res) {
                debug_print('rest_requests done', res);
                count += 1;
                if (res.success) {
                    if (count == urls.length) {
                        debug_print('rest_requests fn_done()', res.result);
                        safe_invoke(fn_done, res.result);
                    }
                } else {
                    safe_invoke(fn_error, res.result);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                debug_print('rest_request ajax error', jqXHR);
                debug_print('rest_request ajax error', textStatus);
                debug_print('rest_request ajax error', errorThrown);

                if (jqXHR.readyState == 0) {
                    // connection error, readyState is 4, if HTTP error
                    safe_invoke(fn_error, find_i18n('e_ajax_failed'));
                    return;
                }

                if (jqXHR.status == 200) {
                    if (textStatus === 'parsererror') {
                        textStatus = jqXHR.responseText;
                    }
                } else {
                    textStatus = jqXHR.statusText;
                }

                safe_invoke(fn_error, textStatus);
            });
        };

        $.map(urls, rest_req);
    };

    var do_rest = function(url, rest, data, fn_done, fn_error) {
        if (typeof url == 'string'){
            return rest_requests([url], rest, data, fn_done, fn_error);
        }
        return rest_requests(url, rest, data, fn_done, fn_error);
    };

    var do_rpc3 = function(rpc_name, params, fn_done, fn_error) {
        if (params) {
            params['rpc'] = rpc_name;
        } else {
            params = {rpc: rpc_name};
        }

        return do_rest('/tc/rest/controller.php', 'POST', params, fn_done, fn_error);
    };

    var rpc_handler_i18n = function(rpc_name, rpc_handler, rpc_data) {
        if (rpc_data === undefined) {
            rpc_data = {};
        }
        util_page.do_rpc3(
            rpc_name,
            rpc_data,
            rpc_handler,
            dialog_message_error
        );
    };

    var rpc_i18n = function(rpc_name, rpc_data) {
        if (rpc_data === undefined) {
            rpc_data = {};
        }
        rpc_handler_i18n(rpc_name, $.noop, rpc_data);
    };

    var rest_get = function(url, fn_done, fn_error) {
        if (fn_error === undefined) {
            fn_error = dialog_message_error;
        }
        return do_rest(url, 'GET', null, fn_done, fn_error);
    };

    var rest_post = function(url, data, fn_done, fn_error) {
        if (fn_done === undefined) {
            fn_done = $.noop;
        }
        if (fn_error === undefined) {
            fn_error = dialog_message_error;
        }
        return do_rest(url, 'POST', data, fn_done, fn_error);
    };

    var rest_delete = function(url, data, fn_done, fn_error) {
        if (fn_error === undefined) {
            fn_error = dialog_message_error;
        }
        if (typeof data === 'function') {
            alert('rest_delete() reads a function as data');
        }
        return do_rest(url, 'DELETE', data, fn_done, fn_error);
    };

    var rest_put = function(url, data, fn_done, fn_error) {
        if (fn_error === undefined) {
            fn_error = dialog_message_error;
        }
        return do_rest(url, 'PUT', data, fn_done, fn_error);
    };

    var rest_upload_file = function(url, file_data, fn_done, fn_error) {
        if (fn_error === undefined) {
            fn_error = dialog_message_error;
        }
        var formData = new FormData();
        formData.append(file_data.name, file_data.content);
        var options = {
            url: url,
            type: 'POST',
            cache: false,
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false
        };
        return rest_do_request(options, fn_done, fn_error);
    };

    var render_sidebar = function() {
        var svr_mgmt_string = localStorage.getItem('#svr_mgmt');
        var usr_mgmt_string = localStorage.getItem('#usr_mgmt');
        var img_mgmt_string = localStorage.getItem('#img_mgmt');
        var clt_mgmt_string = localStorage.getItem('#clt_mgmt');
        if(typeof svr_mgmt_string !== 'undefined' && svr_mgmt_string !== null){
            $('#svr_mgmt').addClass('in');
        }
         if(typeof usr_mgmt_string !== 'undefined' && usr_mgmt_string !== null){
             $('#usr_mgmt').addClass('in');
        }
         if(typeof img_mgmt_string !== 'undefined' && img_mgmt_string !== null){
             $('#img_mgmt').addClass('in');
        }
         if(typeof clt_mgmt_string !== 'undefined' && clt_mgmt_string !== null){
             $('#clt_mgmt').addClass('in');
        }
        $('#back_last').click(function(){
            // util_page.dialog_confirm(find_i18n('c_web_goback'), function(){
            //     util_page.navi_page(localStorage.getItem("historyUrl"));
                
            // });
            if(localStorage.getItem("url").indexOf("client_image.php") == -1 && localStorage.getItem("url").indexOf("client_detail.php") == -1 && localStorage.getItem("url")!=='/tc/system/bootimage_create_page.php'){
                var historyUrl = window.location.href.split(window.location.host)[1];
                localStorage.setItem("historyUrl",historyUrl);
            }
            
            
        })

        $('#sidebar_logout').click(function() {
            util_page.dialog_confirm(find_i18n('c_web_logout'), function(){
                util_page.rest_delete('/tc/rest/auth.php', null, function() {
                    util_page.navi_page('/tc/login.php');
                });
            });
        });
        $('#content_logout').click(function() {
            // var $name = $('#name');
            // $name.text('9999999999999999')
            util_page.dialog_confirm(find_i18n('c_web_logout'), function(){
                util_page.rest_delete('/tc/rest/auth.php', null, function() {
                    util_page.navi_page('/tc/login.php');
                });
            });
        });
        $('#edit_user_message').click(function(e) {
            // $('#reset_pwd_tips p').html();
            $('#dialog_edit_pwd').css('display','block');
            $('#modal-backdrop').css('display','block');
            $('#pwd_less_10').css('display','none');
            $('#pwd_not_match').css('display','none');
            $('#user_name_span').css('display','none');
            
            uid =  $("#uid_span").text();
            user_name =  $("#user_name_span").text();
            user_url = "/tc/rest/user.php/" + uid;

            var old_password = find_i18n('s_password_old'),
                new_password = find_i18n('s_password_new'),
                retype_password = find_i18n('s_password_retype'),
                pwd_less_10 = find_i18n('s_password_rule'),
                pwd_not_match = find_i18n('e_bad_password_mismatch');

            $('#old_password').attr("placeholder", old_password);
            $('#new_password').attr("placeholder", new_password);
            $('#repeat_password').attr("placeholder", retype_password);
            $('#reset_pwd_submit').val(find_i18n('s_password_reset'));
            // $('#reset_pwd_tips p').append(pwd_less_10);


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
                            $('#dialog_edit_pwd').css('display','none');
                            $('#modal-backdrop').css('display','none');
                            util_page.dialog_message_i18n('s_password_reset_done');
                            
                            // var timer = setTimeout(function(){
                            //     util_page.navi_page("dashboard_page.php");
                            // }, 2000);
                            // if (timer) {
                            //     clearInterval(this.$timer);
                            // }
                            // util_page.dialog_message2(
                            //     {
                            //         message: find_i18n('s_password_reset_done')
                            //     },
                            //     function() {
                            //         util_page.navi_page("dashboard_page.php");
                            //     }
                            // );
                        });
                    });
                });

            });
            load_user_info();
            $('#client_btn a').css('color','#5D9DFF');
            $('#user_add button:nth(0)').click(function() {
                $('#client_btn a').css('color','#5D9DFF');
                $('#mac_btn a').css('color','#666');
                $('#user_add button:nth(0)').css('border','0');
            });
            $('#user_add button:nth(1)').click(function() {
                $('#mac_btn a').css('color','#5D9DFF');
                $('#client_btn a').css('color','#666');
                $('#user_add button:nth(1)').css('border','0');
            });
        });

        $('#reset_pwd_cancel').click(function(e) {
            $('#dialog_edit_pwd').css('display','none');
            $('#modal-backdrop').css('display','none');
        });

        $('#reset_user_cancel').click(function(e) {
            $('#dialog_edit_pwd').css('display','none');
            $('#modal-backdrop').css('display','none');
        });

        $('#reset_user_submit').click(function(e) {
            util_page.rest_put(user_url, {'pname': $('#user_display').val()}, function() {
                util_page.dialog_message_i18n('s_update_done_ok');
                load_user_info();
            });
            util_page.rest_put(user_url, {enable: $('#user_status').val()}, function() {
                util_page.dialog_message_i18n('s_update_done_ok');
                load_user_info();
            });
            $('#dialog_edit_pwd').css('display','none');
            $('#modal-backdrop').css('display','none');
            
        });

        $(".page_a").click(function(e){
            // console.log(e);
            // console.log(e.originalEvent.currentTarget.innerText);
            // var text = e.toElement.innerText;
            var text = e.originalEvent.currentTarget.innerText;
            var historyUrl = window.location.href.split(window.location.host)[1];
            localStorage.setItem("historyUrl",historyUrl); 
            localStorage.setItem("text",text); 
        })
        $(".li_client_management_backup").click(function(e){
            var first_name = $(".li_client_management_backup").find('a span:nth(1)').text()
            console.log(first_name);
            localStorage.setItem("first_name",first_name);
        })
        $(".li_image_management_backup").click(function(e){
            var first_name = $(".li_image_management_backup").find('a span:nth(1)').text()
            console.log(first_name);
            localStorage.setItem("first_name",first_name);
        })
        $(".li_user_man").click(function(e){
            var first_name = $(".li_user_man").find('a span:nth(1)').text()
            console.log(first_name);
            localStorage.setItem("first_name",first_name);
        })
        $(".li_about_my_backup").click(function(e){
            var first_name = $(".li_about_my_backup").find('a span:nth(1)').text()
            console.log(first_name);
            localStorage.setItem("first_name",first_name);
        })
        $(".li_set_up_backup").click(function(e){
            var first_name = $(".li_set_up_backup").find('a span:nth(1)').text()
            console.log(first_name);
            localStorage.setItem("first_name",first_name);
        })
        
        // $('#reset_pwd_submit').click(function(e) {
        //     $('#dialog_edit_pwd').css('display','none');
        // });
        
    };

    var disable_cache = function() {
        // disable ajax cache for page updating
        $.ajaxSetup({
            cache: false
        });
    };

    var enable_locale = function($scope) {
        if ($scope === undefined) {
            $scope = $(document);
        }
        // update locale for bootbox library
        bootbox.setLocale(webconsole_locale);
        // update locale in web console
        update_i18n($scope);
    };

    var navi_page = function(url) {
        window.location.href = url;
        var test = url.split('10.121.142.29')[1];
        $("#back_last").attr('href',test);
        var test2 = $("#back_last").attr('href');
    };

    var page_refresh = function(url) {
        window.location.reload();
    };

    /*
     * Dialog related functions
     */
    // show an alert message dialog
    var dialog_message = function(message, fn_ok) {
        if (fn_ok === undefined) {
            fn_ok = $.noop;
        }
        if(message.indexOf('<b>Warning</b>:  implode(): Invalid arguments passed in <b>') == -1){
            return bootbox.alert(message, fn_ok);
        }else if(message.indexOf('<b>Warning</b>:  implode(): Invalid arguments passed in <b>') != -1){
            navi_page('bootimage_page.php');
        }
    };

    var dialog_message2 = function(options, fn_ok) {
        if (fn_ok === undefined) {
            fn_ok = $.noop;
        }
        var dialog_options = {
            title: '',
            message: '',
            onEscape: true,
            buttons: {
                ok: {
                    label: 'OK',
                    callback: fn_ok
                }
            }
        };
        if (options.hasOwnProperty('message')) {
            dialog_options.message = options.message;
        }
        if (options.hasOwnProperty('title')) {
            dialog_options.title = options.title;
        }
        return bootbox.dialog(dialog_options);
    };

    var dialog_message_i18n = function(i18n, default_value) {
        if (default_value === undefined) {
            default_value = i18n;
        }
        // dialog_message(find_i18n(i18n, default_value));
        dialog_modal(find_i18n(i18n, default_value))
    };
    var dialog_modal = function(message) {
        var dialog = bootbox.dialog({
            message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i> '+ message +'</p>',
            closeButton: false
        });
                    
        // do something in the background
        var text = setTimeout(function () {
            dialog.modal('hide');
        }, 2000)
        
    }

    var dialog_modal_hide = function(dialog){
        
    }
    
    var dialog_message_error = function(rest_error, fn_ok) {
        if (fn_ok === undefined) {
            fn_ok = $.noop;
        }
        if (typeof rest_error === 'string') {
            // not a standard REST error, maybe a PHP error
            return dialog_message(rest_error);
        }
        if (rest_error === undefined) {
            return dialog_message('Unknown error received from server');
        }
        var message = find_i18n(rest_error.error, rest_error.error);
        if (rest_error.hasOwnProperty('errno')) {
            // 1062 is the error number from mysql
            // extra example: Duplicate entry 'bb' for key 'PRIMARY
            if (parseInt(rest_error.errno) == 1062) {
                message += '<br>' + find_i18n('e_duplicate_value');
                if (rest_error.hasOwnProperty('extra')) {
                    var ws = rest_error.extra.split('\'');
                    if (ws.length > 1) {
                        message += ', ' + ws[1];
                    }
                }
            } else if (parseInt(rest_error.errno) == 1064) {
                message += '<br>' + find_i18n('e_db_inject_error');
            } else if (rest_error.extra) {
                message += '<br>' + JSON.stringify(rest_error.extra);
            }
        }

        if(rest_error.error!="e_invalid_password"){
            dialog_message2({message: message}, fn_ok);
        }else{
            $('#pwd_less_10').hide();
            $('#pwd_not_match').text(find_i18n('e_invalid_password_error')).show();
        }
        
    };

    var _init_prompt_basic_options = function(message, fn_ok, fn_cancel) {
        if (fn_cancel === undefined) {
            fn_cancel = $.noop;
        }

        return {
            title: message,
            callback: function(value) {
                // null means dialog is cancelled
                var cancelled = (value === null);

                if (cancelled) {
                    debug_print('dialog cancel', message);
                    fn_cancel();
                } else {
                    fn_ok(value);
                }
            }
        };
    };

    var _init_prompt_options = function(message, fn_ok, fn_cancel) {
        return _init_prompt_basic_options(message, function(value){
            if (value === '') {
                dialog_message_i18n('e_empty_input', 'Error: Input is empty');
                return;
            }
            fn_ok(value);
        }, fn_cancel);
    };

    // show a user text input dialog
    var dialog_prompt = function(message, fn_ok, fn_cancel) {
        if (fn_cancel === undefined) {
            fn_cancel = $.noop;
        }
        return bootbox.prompt(message, function(value) {
            // null means dialog is cancelled
            if (value === null) {
                debug_print('dialog cancel', message);
                fn_cancel();
            } else {
                fn_ok(value);
            }
        });
    };

    // will not accept empty input value, based on prompt
    var dialog_prompt_required = function(message, fn_ok, fn_cancel) {
        if (fn_cancel === undefined) {
            fn_cancel = $.noop;
        }
        return dialog_prompt(message, function(value) {
            if (value === '') {
                dialog_message(find_i18n('e_empty_input', 'Error: Input is empty'));
                return;
            }
            fn_ok(value);
        }, fn_cancel);
    };

    var dialog_confirm = function(message, fn_confirmed) {
        return bootbox.confirm(message, function(confirmed){
            if (confirmed) {
                fn_confirmed();
            }
        });
    };

    var dialog_confirm_or_cancel = function(message, fn_deal) {
        return bootbox.confirm({
            message: message,
            callback: fn_deal,
        });
    };

    /**
     * input format:
     *     [{text: 'default select', value: ''}, {text: 'op 1', value: '1'}]
     *
     * Example:
     *   Yes/No:
     *     [{text: 'Yes', value: '1'}, {text: 'No', value: '0'}]
     *     [{text: find_i18n('s_yesno_no'), value: '0'},
     *      {text: find_i18n('s_yesno_yes'), value: '1'}
     *     ];
     */
    var dialog_select = function(message, input, fn_select) {
        return bootbox.prompt({
            title: message,
            inputType: 'select',
            inputOptions: input,
            callback: function(result) {
                if (result === null)
                    return;
                fn_select(result);
            }
        });
    };

    var dialog_select_selections = function(format) {
        if (format === 'YN') {
            return [
                {text: find_i18n('s_yesno_yes'), value: true},
                {text: find_i18n('s_yesno_no'), value: false}
            ];
        }
        if (format === 'EYN') {
            return [
                {text: '', value: ''},
                {text: find_i18n('s_yesno_yes'), value: true},
                {text: find_i18n('s_yesno_no'), value: false}
            ];
        }
        return [{text: '', value: ''}];
    };

    var dialog_password = function(message, fn_ok) {
        var options = _init_prompt_options(message, function(value){
            fn_ok(value);
        });
        options.inputType = 'password';
        return bootbox.prompt(options);
    };

    var dialog_prompt_value = function(message, value, fn_ok, fn_cancel) {
        var options = _init_prompt_options(message, fn_ok, fn_cancel);
        options.value = value;
        return bootbox.prompt(options);
    };



    var dialog_confirm_builder = function(dialog_html, ok_button_label,cancel_button_label, fn_confirmed) {
        var $batch_checkbox= $('.batch_checkbox')
        // if($batch_checkbox){
        //     $('.batch_checkbox').click(function(){
        //         var in_batch = $('.batch_checkbox').prop('checked');
    
        //         $('.single-only').css('display', in_batch ? 'none' : '');
        //         $('.batch-only').css('display', in_batch ? '' : 'none');
        //     });

        // }
        return bootbox.dialog({
            message: dialog_html,
            onEscape: true,
            buttons: {
                cancel: {
                    label: cancel_button_label,
                    callback: ''
                },
                ok: {
                    label: ok_button_label,
                    callback: fn_confirmed
                },
            }
        });
    };

    var browser_is_ie8 = function() {
        $('body').append('<!--[if IE 8]><div id="ie8_flag"></div><![endif]-->');
        return $('#ie8_flag').length > 0;
    };

    var dialog_upload = function(url, title,done) {
        var html = '<div id="form_upload_file"><h4><span>' + find_i18n(title) +'</span></h4>';
        html += '<div class="form-group">',
        html += '<label>' + find_i18n('s_upload_select_file') +'</label>',
        html += '<hr class="compact">',
        html += '<input type="file" id="file-0" name="file-0">',
        html += '</div>',
        html += '</div>';
        var $dialog = util_page.dialog_confirm_builder(
            html,
            find_i18n('s_upload_file'),
            find_i18n('s_cancel'),
            function() {
                var html_input = $dialog.find('#file-0')[0],
                    data = {name: 'file-0', content: html_input.files[0]};
                util_page.rest_upload_file(url, data, function() {
                    done();
                });
            }
        );
        return $dialog;
    };

    var html_table_row = function(cells) {
        var text = '';
        $.map(cells, function(c) {
            text += '<td>' + c + '</td>';
        });
        return $('<tr>' + text + '</tr>');
    }

    var update_main_area_title = function(message) {
        var $title = $('#main-area h3:nth(0)');
        // clear previous data
        $title.find(".tc-volatile").remove();

        // fill with latest information
        $('<span></span>')
            .css("margin-left", "0.5em")
            .addClass("pull-right")
            .addClass("tc-volatile")
            .html(message)
            .appendTo($title);
    }

    var up_retry_count = 3;
    var up_interval = 1000;

    var enable_progress_title = function(url, fn_format) {
        // reset the retry count for coming progress reporting
        up_retry_count = 3;

        var update_progress = function() {
            console.log("up_retry_count " + up_retry_count);
            rest_get(url, function(progress) {
                var progress_text = fn_format(progress);

                if (progress.hasOwnProperty('trans_pct')) {
                    var percent = progress.trans_pct;

                    if (percent === '100%') {
                        up_retry_count--;
                    }

                    if (up_retry_count) {
                        update_main_area_title(progress_text);
                        setTimeout(update_progress, up_interval);
                    } else {
                        // clear all progress because 100%
                        update_main_area_title('');
                    }
                }
            });
        }

        if (up_retry_count) {
            setTimeout(update_progress, up_interval);
        }
    }

    var hash_password = async function(username, password) {
        const plaintext = password + ":" + username;
        const plaintext_unit8 = new TextEncoder().encode(plaintext);
        const buffer = await crypto.subtle.digest('SHA-256', plaintext_unit8);
        const array = Array.from(new Uint8Array(buffer));
        const hash = array.map(b => b.toString(16).padStart(2, '0')).join('');
        return hash;
    }

    var debug_print = function(message, data) {
        if (__debug && window.console) {
            window.console.log(message + ' :: ' + JSON.stringify(data));
        }
    };

    return {
        render_sidebar: render_sidebar,
        disable_cache: disable_cache,
        enable_locale: enable_locale,
        enable_progress_title: enable_progress_title,
        hash_password: hash_password,
        is_alphanumeric: is_alphanumeric,
        is_empty: is_empty,
        is_mac: is_mac,
        parse_mac: parse_mac,
        do_rpc3: do_rpc3,
        rpc_i18n: rpc_i18n,
        rpc_handler_i18n: rpc_handler_i18n,
        rest_get: rest_get,
        rest_post: rest_post,
        rest_delete: rest_delete,
        rest_put: rest_put,
        rest_upload_file: rest_upload_file,
        navi_page: navi_page,
        page_refresh: page_refresh,
        browser_is_ie8: browser_is_ie8,
        dialog_confirm: dialog_confirm,
        dialog_confirm_or_cancel: dialog_confirm_or_cancel,
        dialog_message: dialog_message,
        dialog_message2: dialog_message2,
        dialog_message_error: dialog_message_error,
        dialog_message_i18n: dialog_message_i18n,
        dialog_select: dialog_select,
        dialog_select_selections: dialog_select_selections,
        dialog_password: dialog_password,
        dialog_prompt: dialog_prompt,
        dialog_prompt_required: dialog_prompt_required,
        dialog_prompt_value: dialog_prompt_value,
        dialog_confirm_builder: dialog_confirm_builder,
        dialog_upload: dialog_upload,
        print_size: print_size,
        print_speed: print_speed,
        print_time: print_time,
        update_main_area_title: update_main_area_title,
        html_table_row: html_table_row,
        count_repeat: count_repeat,
        array_filter: array_filter
    };
})();


function select_lang(lang) {
    var lang_id = 2;
    if (lang == 'en') {
        lang_id = 1;
    }
    $.ajax({
        method: 'PUT',
        url: '/tc/rest/controller.php?lang='+lang
    }).done(function(data){
        // reload top panel
        location.reload();
    });
}