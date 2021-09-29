<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

require_once('../libs/inc.sugar.php');
require_once("include/inc.lang.php");
require_once('../libs/libtc.php');
    //standard_page_begin('reset_pwd');
    sidebar_item_home();




function is_dnsmasq_running() {
    $exit_value = -1;
    $last_line = exec("systemctl is-active --quiet tcs-dnsmasq", $output, $exit_value);
    return 0 == $exit_value;
}

$links = array(

      'clt_mgmt' => array(
        'title' => array('en' => "Client Machines Management", 'zh' => '终端管理'),
        'icon_url' => '/tc/images/client_management_backup.png',
        'class_name' => 'client_management_backup',
        'li_class_name' => 'li_client_management_backup',
        'links' => array(
              'unreg_clients'  => array(
                'title' => array('en' => 'Unregistered Clients', 'zh' => '未注册终端'),
                'url' => '/tc/system/unreg_clients.php',
            ),
            'register_client' => array(
                'title' => array('en' => 'Register Client', 'zh' => '注册客户端'),
                'url' => '/tc/system/client_create_page.php',
                'show' => FALSE,
            ),
            'client_full_table' => array(
                'title' => array('en' => 'Client List', 'zh' => '已注册终端'),
                'url' => '/tc/system/client_page.php',
            ),
             'online_clients' => array(
                'title' => array('en' => 'Online Clients', 'zh' => '在线终端'),
                'url' => '/tc/system/online_clients.php',
            //    'show' => FALSE,
            ),

            'client_groups' => array(
                'title' => array('en' => 'Client Groups', 'zh' => '终端分组'),
                'url' => '/tc/system/client_group_page.php',
            ),
            'client_struc' => array(
                'title' => array('en' => 'Client Structure', 'zh' => '客户端结构'),
                'url' => '/tc/system/client_structure.php',
                'show' => FALSE,
            ),
            'client_detail' => array(
                'title' => array('en' => 'Client Detail', 'zh' => '客户端详细'),
                'url' => '/tc/system/client_detail.php',
                'show' => FALSE,
            ),
        ),
    ),
     'img_mgmt' => array(
        'title' => array('en' => "Client Images Management", 'zh' => '镜像管理'),
        'icon_url' => '/tc/images/image_management_backup.png',
        'class_name' => 'image_management_backup',
        'li_class_name' => 'li_image_management_backup',
        'links' => array(
            'register_image' => array(
                'title' => array('en' => 'Register Image', 'zh' => '注册镜像'),
                'url' => '/tc/system/bootimage_create_page.php',
                 'show' => FALSE,
            ),
            'all_images' => array(
                'title' => array('en' => 'Client Image List', 'zh' => '镜像列表'),
                'url' => '/tc/system/bootimage_page.php',
            ),
            'sync_peers' => array(
                'title' => array('en' => 'Sync Images', 'zh' => '镜像同步'),
                'url' => '/tc/system/peer_sync_page.php',
                'show' => FALSE,
            ),
            'client_image' => array(
                'title' => array('en' => 'Client Image', 'zh' => '客户端镜像'),
                'url' => '/tc/system/client_image.php',
                'show' => FALSE,
            ),
        ),
    ),
    'usr_mgmt' => array(
        'title' => array('en' => "Users Management", 'zh' => '用户管理'),
        'icon_url' => '/tc/images/user_man.png',
        'class_name' => 'user_man',
        'li_class_name' => 'li_user_man',
        'links' => array(
            'home' => array(
                'title' => array('en' => 'Home', 'zh' => '用户信息'),
                'url' => '/tc/system/home.php',
                'show' => FALSE,
            ),
            'reset_pwd' => array(
                'title' => array('en' => 'Reset Password', 'zh' => '修改密码'),
                'url' => '/tc/system/reset_pwd.php',
                'show' => FALSE,
            ),
            // 'create_user' => array(
            //     'title' => array('en' => 'Register User', 'zh' => '注册用户'),
            //     'url' => '/tc/system/user_create_page.php',
            //     'show' => FALSE,
            // ),
            // 'create_group' => array(
            //     'title' => array('en' => 'Register Group', 'zh' => '注册组'),
            //     'url' => '/tc/system/group_create_page.php',
            //     'show' => FALSE,
            // ),
            'all_users' => array(
                'title' => array('en' => 'User List', 'zh' => '用户列表'),
                'url' => '/tc/system/user_page.php',
            ),
            'all_groups' => array(
                'title' => array('en' => 'Group List', 'zh' => '组列表'),
                'url' => '/tc/system/group_page.php',
            ),
        ),
    ),
      

    //    'svr_mgmt' => array(
    //     'title' => array('en' => "ECM Server Management", 'zh' => 'ECM服务管理'),
    //     'icon_url' => '/tc/images/about_my_backup.png',
    //     'class_name' => 'about_my_backup',
    //     'li_class_name' => 'li_about_my_backup',
    //     'links' => array(
    //         'dashboard' => array(
    //             'title' => array('en' => 'Dashboard', 'zh' => '系统状态'),
    //             'url' => '/tc/system/dashboard_page.php',
    //             'show' => FALSE,
    //         ),
    //          'all_images' => array(
    //             'title' => array('en' => 'Client Image List', 'zh' => 'demo镜像列表'),
    //             'url' => '/tc/system/bootimage_page.php',
    //             'show' => FALSE,
    //         ),
    //         'online_clients' => array(
    //             'title' => array('en' => 'Online Clients', 'zh' => '在线客户端'),
    //             'url' => '/tc/system/online_clients.php',
    //             'show' => FALSE,
    //         ),
    //         'unreg_clients'  => array(
    //             'title' => array('en' => 'Unregistered Clients', 'zh' => '未注册客户端'),
    //             'url' => '/tc/system/unreg_clients.php',
    //              'show' => FALSE,
    //         ),
    //         'advanced_settings'  => array(
    //             'title' => array('en' => 'Advanced Settings', 'zh' => '核心服务管理'),
    //             'url' => '/tc/system/advanced_settings.php',
    //         ),
    //         'network_dhcp' => array(
    //             'title' => array('en' => 'Network Settings', 'zh' => 'DHCP 配置'),
    //             'url' => '/tc/system/network_dhcp.php',
    //             'show' => is_dnsmasq_running(),
    //         ),
    //         'backup' => array(
    //             'title' => array('en' => 'Data Backup', 'zh' => '数据备份'),
    //             'url' => '/tc/system/system_backup.php',
    //             'show' => FALSE,
    //         ),
    //         'update_firmware' => array(
    //             'title' => array('en' => 'Update Firmware', 'zh' => '固件更新'),
    //             'url' => '/tc/system/firmware_page.php',
    //             'show' => FALSE,
    //         ),
    //         'about' => array(
    //             'title' => array('en' => 'About ECM', 'zh' => '关于 ECM 系统'),
    //             'url' => '/tc/system/about.php',
    //             'show' => FALSE,
    //         ),
    //         'role' => array(
    //             'title' => array('en' => 'User Right Management', 'zh' => '用户权限管理'),
    //             'url' => '/tc/system/role_page.php',
    //             'show' => TC_ENABLE_CUSTOMIZED_WEB_LOGIN,
    //         ),
            
    //     ),
    // ),
);


function locale_title($link) {
    global $links;
    $lang = $GLOBALS['_TC2_']['lang'];
    foreach ($links as $key => $value) {
        if ($link == $key) {
            return $value['title'][$lang];
        }
        foreach ($value['links'] as $key2 => $value2) {
            if ($link == $key2) {
                return $value2['title'][$lang];
            }
        }
    }
    return $link;
}


function page_head_html($title) {
    $html = <<< HTML_END
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/webconsole.css" rel="stylesheet" type="text/css">
    <title>$title</title>
</head>
HTML_END;

    return $html;
}

function page_head($link) {
    echo page_head_html(locale_title($link));
}

function page_script_files() {
    $lang_id = global_config_lang_id();
    $html = <<< HTML_END
    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../assets/bootbox.min.js"></script>
    <script src="../assets/chart.bundle.min.js"></script>
    <script src="js/dialog.js"></script>
    <script src="js/table.js"></script>
    <script src="js/page.js"></script>
    <script src="js/iefix.js"></script>
    <script src="js/d3.min.js"></script>
    <script src='langs/lang.$lang_id.js'></script>
HTML_END;
    echo $html, PHP_EOL;
}

function control_pagination_html() {
    $html = <<< HTML_END
<div class="row" id="page-control">
    <nav class="col-xs-6">
      <ul class="pagination">
      </ul>
    </nav>
    <div class="col-xs-4 col-xs-offset-2 text-right">
        <label style="display: inline-block;">
            <span i18n="s_total">Total</span> <span data-id="total"></span>
        </label>
        &nbsp;
        <label style="display: inline-block;"><span i18n="s_perpage">PerPage</span></label>
        <select style="background-color: #32bedb;color: #fff;">
            <option value="20" selected>20</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
</div>
HTML_END;
    return $html;
}

function control_pagination() {
    echo control_pagination_html();
    
}


function save_cancel_buttons_html() {
    $html = <<< HTML_END
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-3 col-md-2">
                <button type="button" class="btn btn-default btn-primary btn-block" id="save-button">
                    <span class="glyphicon glyphicon-ok"></span>
                    <span i18n="s_save">Save</span>
                </button>
            </div>
            <div class="col-sm-3 col-md-2">
                <button type="button" class="btn btn-default btn-block" id="cancel-button">
                    <span i18n="s_cancel">Cancel</span>
                </button>
            </div>
        </div>
HTML_END;
    return $html;
}

function save_cancel_buttons() {
    echo save_cancel_buttons_html();
}

function page_begin() {
    echo "<!DOCTYPE html>", PHP_EOL;
    echo "<html>", PHP_EOL;
}

function page_end() {
    echo "</html>", PHP_EOL;
}

function body_begin() {
    echo "<body>", PHP_EOL;
}

function body_end() {
    echo "</body>", PHP_EOL;
}

function container_begin() {
    echo '<div class="container-fluid">', PHP_EOL;
}

function container_end() {
    echo "</div>", PHP_EOL;
}

function rows_begin() {
    echo '<div class="row">', PHP_EOL;
}

function rows_end() {
    echo "</div>", PHP_EOL;
}

/**
 * Generate left sidebar
 *
 * Example:
 *
 * @param  [type] $active_link [description]
 * @return [type]              [description]
 */
function sidebar_item_groups($links, $active_link) {
    $lis = "";
    foreach ($links as $key => $value) {
        if (safe_array_get('show', $value, true) === false) {
            continue;
        }
        $html = "";
        $ul_class = "collapse";
        foreach ($value['links'] as $key2 => $value2) {
            if (safe_array_get('show', $value2, true) === false) {
                continue;
            }
            if ($active_link == $key2) {
                $html .= '<li class="active">';
                $ul_class = "collapse.in";
                // $("#name").text($key2);
            } else {
                $html .= '<li>';
            }
            $text = $value2["title"][load_configuration_str("lang")];
            $html .= '<a class="page_a" href="'.$value2['url'].'">'.$text.'</a></li>'.PHP_EOL;
        }
        $text = $value["title"][load_configuration_str("lang")];
        $iconUrl = $value["icon_url"];
        $class_name = $value["class_name"];
        $li_class_name = $value["li_class_name"];
        $li_html = <<< HTML_END
<li class="$li_class_name">
    <a class="nav-a" href="#$key" data-toggle="collapse" aria-expanded="false">
        <span class="$class_name img_span"></span>  
        <span>$text</span>
    </a>
    <ul class="$ul_class list-unstyled" id="$key">$html</ul>
</li>
HTML_END;
        $lis .= $li_html;
    }
    return $lis;
}

function license_status_html() {
    $status = lic2_license_status();
    if ($status === "ls_verified") {
        return "";
    }
    $status_text = "";
    if ($status === "ls_unregistered") {
        $status_text = lang_text_i18n("s_license_missing");
    }
    if ($status === "ls_expired") {
        $status_text = lang_text_i18n("s_license_expired");
    }
    if ($status === "ls_invalid") {
        $status_text = lang_text_i18n("s_license_invalid");
    }
    return '<a href="about.php" class="text-error">'."<span>$status_text</span></a>";
}

function sidebar_left($active_link) {
    global $links;
    global $settings_links;
    
    $lis = sidebar_item_groups($links, $active_link);
    $lis2 = sidebar_item_groups($settings_links, $active_link);

    $brand1 = "Lenovo ";
    $brand2 = "ECM";
    $year = date("Y");
    $company = $GLOBALS['_TC2_']['company_name'];
   // $logout_text = lang_text_i18n("s_common_logout");
    $dashboard_text = lang_text_i18n("s_home_dashboard");
    $versionmanagment_text = lang_text_i18n("s_home_versions_management");
    $ls_html = license_status_html();

    $html = <<< HTML_END
    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="/tc/images/10.png" style="height:35px;display:none"/>
            $ls_html
            
        </div>
        <ul class="list-unstyled" style="display:none">
            <li>
                <a href="/tc/system/dashboard_page.php" id="dashbaord_page">
                    <span class="dashboard_backup img_span" id="dashbaord_page_icon"></span>
                    <span>$dashboard_text</span>
                </a>
            </li>
        </ul>
        <ul class="list-unstyled" id="sub_title">$lis</ul>
        <ul class="list-unstyled">$lis2</ul>
        <ul class="list-unstyled" style="display:none">
            <li>
                <a href="/tc/system/about.php" id="version_manage_page">
                    <span class="dashboard_backup img_span" id="version_manage_page_icon"></span>
                    <span>$versionmanagment_text</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
        <!--<p>&copy; $year $company</p>
            <img src="/tc/images/siderbar_bac.png" class="footer_image"/>
            <img src="/tc/images/siderbar_bac2.png"/>-->
        </div>
   </nav>

HTML_END;
    echo $html, PHP_EOL;
}

function content_right_begin($link) {
    $title = locale_title($link);
    $quit_text = lang_text_i18n("s_common_logout");
    $password_text = lang_text_i18n("s_common_edit_password");
    $user_text = lang_text_i18n("s_common_edit_user_status");
    $user_mes_text = lang_text_i18n("s_common_edit_password");
    $language_text = lang_text_i18n("s_home_language");
    $password_old = lang_text_i18n("s_password_old");
    $password_new = lang_text_i18n("s_password_new");
    $password_reset = lang_text_i18n("s_password_reset");
    $password_rule = lang_text_i18n("s_password_rule");
    $tips = lang_text_i18n('s_password_rule');
    $uid =$_SESSION["uid"];
    $user_name =$_SESSION["user_name"];
    $html = <<< HTML_END
    <div id="tip-area">
        <div style="float:left">
            <img src="/tc/images/10.png" style="float: left;width: 197px;"/>
            <a href="###" id="back_last" style="float: left;display:none"><img src="/tc/images/goBack.png" style="width:20px;margin-top: 9px;"/></a>
            <span  i18n="s_title" style="color:#fff;font-size: 24px;margin-left: 3px;font-weight: 600;margin-top: 4px;
            display: inline-block;">LESport管理平台</span>
        </div>
        <ul class="breadcrumb" style="float:left;background:rgba(255,255,255,0);color:#fff;margin-top: 5px;margin-left: 25px;">
            <li id="first_name_li"><a href="#" id="first_name" style="color:#fff"></a></li>
            <li id="second_name_li"><a href="#" id="second_name" style="color:#fff"></a></li>
            <li id="detail_name_li"><a href="#" id="detail_name" style="color:#fff"></a></li>
        </ul>
        
         <div class="language_select" style="display:none">
            <button type="button" class="btn dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown">$language_text
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" id="language_select_value" role="menu" aria-labelledby="dropdownMenu1">
                <li role="presentation" id="select_lang_zh">
                    <a role="menuitem" tabindex="-1" href="javascript:select_lang('zh')">中文</a>
                </li>
                <li role="presentation" id="select_lang_en">
                    <a role="menuitem" tabindex="-1" href="javascript:select_lang('en')">Englsh</a>
                </li>
            </ul>
        </div>
	    <div class="language_select" style="margin-right: -3px;">
            <button type="button" class="btn dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown" style="padding-top:0">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" id="common_select_value" role="menu" aria-labelledby="dropdownMenu1">
                <li role="presentation" id="select_lang_zh">
                    <a href="###" id="content_logout" class="user_quit_div">$quit_text</a>
                </li>
                <li role="presentation" id="select_lang_en">
                    <a href="###" id="edit_user_message" class="user_quit_div">$user_mes_text</a>
                </li>
            </ul>
        </div>
    	 <p class="user_div">admin</p>
        <img src="/tc/images/user_icon.png"/>
    </div>
    <div id="main-area">
        <div class="row">
            <span id='uid_span' style="display:none">$uid</span>
            <span id='user_name_span' style="display:none">$user_name</span>
            <div id="dialog_edit_pwd" class="dialog_model" style="display:none">
                <div class="btn-group" data-toggle="buttons" id="user_add">
                    <button type="button" class="btn" id="client_btn" style="background:#fff">
                        <a href="#password" data-toggle="tab">$password_text</a>
                    </button>
                    <button type="button" class="btn" id="mac_btn" style="background:#fff;display:none">
                        <a href="#user" data-toggle="tab">$user_text</a>
                    </button>
                </div>
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="password">
                        <form action="#" class="reset_pwd-form" id="reset_pwd_form">
                            <h4><div  id="pwd_less_10" class="alert alert-danger" role="alert" style="display:none;"></div></h4>
                            <div  id="pwd_not_match" class="alert alert-danger" role="alert" style="display:none;" ></div>
                            <div class="form-group">
                                <label for="old_password" class="sr-only pwd_label">$password_old</label>
                                <input type="password" class="form-control pwd_label" id="old_password" placeholder="$password_old" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="new_password" class="sr-only pwd_label">$password_new</label>
                                <input type="password" class="form-control pwd_label" id="new_password" placeholder="$password_new" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="repeat_password" class="sr-only pwd_label">$password_reset</label>
                                <input type="password" class="form-control pwd_label" id="repeat_password" placeholder="$password_reset" autocomplete="off">
                            </div>
                            <div class="form-group" id="reset_pwd_tips">
                                <p><span class="glyphicon glyphicon-warning-sign" style="color:#FFC683; margin-right: 3px;"></span>$tips</p>
                            </div>
                            <div class="form-group">
                                
                                <button type="submit" class="btn btn-default reset_submit" id="reset_pwd_submit" style="float: left;">
                                    <span i18n="s_general_ok">ok</span>
                                </button>
                                <button type="button" class="btn btn-default reset_cancel" id="reset_pwd_cancel" style="float: left;">
                                    <span i18n="s_cancel">center</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="user">
                        <table class="info-table dashboard-table" id="set_user_info">
                            <tbody>
                                <tr>
                                    <td><span i18n="s_username">User Name</span>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><span i18n="s_user_display">Display Name</span>:</td>
                                    <td><input type="text" class="form-control pwd_label" id="user_display" autocomplete="off"></td>
                                </tr>
                                <tr>
                                    <td><span i18n="s_group">User Group</span>:</td>
                                    <td>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span i18n="s_ps_frozen">Storage Frozen</span>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><span i18n="s_user_status">User Status</span>:</td>
                                    <td>
                                    <select class="form-control" id="user_status">
                                        <option value="true" selected>是</option>
                                        <option value="false">否</option>
                                    </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span i18n="s_user_online">User Online</span>:</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-group" style="text-align:center;margin-top: 30px;">
                                
                            <button type="button" class="btn btn-default reset_submit" id="reset_user_submit" >
                                <span i18n="s_general_ok">ok</span>
                            </button>
                            <button type="button" class="btn btn-default reset_cancel" id="reset_user_cancel">
                                <span i18n="s_cancel">center</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade in" style="display:none" id="modal-backdrop"></div>
        </div>
        <style>
           
            #reset_pwd_form{
                height: 350px;
                margin-bottom:0;
            }
            .breadcrumb > li + li::before{
                content:" > ";
                color:"#666"
            }
            .breadcrumb li a{
                font-size: 16px;
                font-family: PingFangSC-Semibold, PingFang SC;
                font-weight: 600;
                color: #999999;
                line-height: 22px;
            }

            #detail_name_li{
                display:none;
            }
            #user_add button a{
                font-size: 16px;
                font-family: PingFangSC-Regular, PingFang SC;
                font-weight: 400;
                color: #666666;
                text-decoration:none;
            }
            #user_add button{
                width:45%;
                padding:15px 0 15px 0;
            }
            #user_add .focus{
                border-style:0;
                outline:none;
            }
            #user_add{
                width: 100%;
                border-bottom: 1px solid #DADADA;
            }


        </style>
HTML_END;
    echo $html, PHP_EOL;

    
}

function content_right_end() {
    echo "</div>", PHP_EOL;
}

function page_permission_check($link) {
    if (!session_id()) session_start();
    $auth = current_session_auth();
    if (!can_read($link, $auth["uid"], $auth["gid"])) {
        echo '<script language="javascript">
                window.location = "zpermission_denied.html"
            </script>';
        exit();
    }
    
}

function browser_compatibility_check(){
    //Deny access request from browser IE5/6/7/8/9/10
    if(!(isset($_SERVER['HTTP_USER_AGENT'])) || (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)){
        echo '<script language="javascript">
            window.location = "zbroswer_denied.html"
            </script>';
        exit();
    }
}

function sidebar_item_home() {
    $auth = current_session_auth();
    $root_gid = load_configuration_int("root_gid");

    if($auth["gid"] > $root_gid) {
        global $links;
        global $settings_links;

        $permissions = read_permissions_by_user($auth["uid"]);
        foreach ($links as $key => $value) {
            $links[$key]['show'] = FALSE;
            foreach ($value['links'] as $key2 => $value2) {
                if (!in_array($key2, $permissions)){
                    $links[$key]['links'][$key2]['show'] = FALSE;
                } else{
                    $links[$key]['show'] = TRUE;
                }
            }
        }
        $links['usr_mgmt']['links']['home']['show'] = TRUE;
        $links['usr_mgmt']['show'] = TRUE;

        //Change server setting of sidebar
        $settings_links['svr_settings']['links']['log']['show'] = FALSE;
        $settings_links['svr_settings']['links']['about']['show'] = FALSE;
    }
}

/*
 * Handy functions for most pages as the template
 *
 */

function standard_page_begin($link) {
    browser_compatibility_check();
    page_permission_check($link);

    page_begin();
    page_head($link);
    body_begin();
    container_begin();

    sidebar_item_home();
    sidebar_left($link);
    content_right_begin($link);
    
}

function standard_page_mid() {
    content_right_end();
    container_end();

    page_script_files();
}

function standard_page_end() {
    body_end();
    page_end();
}
?>

