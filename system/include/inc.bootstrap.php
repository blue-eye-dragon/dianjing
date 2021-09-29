<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/


/**
 * library for printing bootstrap based html
 */

require_once("inc.lang.php");
// <div class="row">
//     <div class="col-xs-12">
//         <div class="btn-toolbar" role="toolbar">
//             <div class="btn-group" role="group">
//                 <button type="button" class="btn btn-default">
//                     <span class="glyphicon glyphicon-arrow-down"></span> 
//                     <span i18n="s_lic_enter_lic">Enter License</span>
//                 </button>
//                 <button type="button" class="btn btn-default">
//                     <span i18n="s_lic_gen_key">Generate Key</span>
//                 </button>
//             </div>
//         </div>
//     </div>
// </div>
//
//  Search Conrol
//  <div class="form-inline">
//      <div class="form-group pull-right">
//          <div class="input-group" id="search-contorl">
//              <input type="text" class="form-control" maxlength="32">
//              <span class="input-group-btn">
//                  <button class="btn btn-default" type="button">
//                      <span class="glyphicon glyphicon-search"></span>
//                      <span i18n="s_search">Search</span>
//                  </button>
//              </span>
//          </div>
//      </div>
//  </div>
//
/**
 * html for a toolbar, button groups, buttons in bootstrap
 *
 * full width in layout, using bootstrap grid system
 */
function html_toolbar($toolbar) {
    $html_toolbar = "";
    foreach ($toolbar["button_groups"] as $btn_group) {
        $html_btns = "";
        foreach ($btn_group["buttons"] as $btn) {
            $cls = "btn-default";
            if (array_key_exists("class", $btn)) {
                $cls = $btn["class"];
            }
            $html = "<button type=\"button\" class=\"btn $cls\">";
            if (array_key_exists("icon", $btn)) {
                $icon = $btn["icon"];
                $html .= "<span class=\"glyphicon $icon\"></span> ";
            }
            $text = lang_text_i18n($btn["i18n"]);
            $html .= $text;
            $html .= '</button>';
            $html_btns .= $html;
        }
        $cls = "btn-group";
        if (array_key_exists('class', $btn_group)) {
            $cls .= " " . $btn_group['class'];
        }
        $html_btns = "<div class=\"$cls\" role=\"group\" >$html_btns</div>";
        $html_toolbar .= $html_btns . "\n";
    }

    if(array_key_exists("search_control", $toolbar)) {
        $search = $toolbar["search_control"];
        $text = lang_text_i18n("s_common_search");
        $cls = "";
        if (array_key_exists("class", $search)) {
            $cls .= "pull-right";
        }
        $id_html = "";
        if (array_key_exists("id", $search)) {
            $id_html = 'id="' . $search['id'] . '"';
        }
        $html_toolbar .= <<< HTML_END
<div class="form-inline">
    <div class="form-group $cls">
        <div class="input-group" $id_html>
            <input type="text" class="form-control" maxlength="32">
            <span class="input-group-btn">
                <button class="btn btn-default btn-group" type="button">
                    <span class="glyphicon glyphicon-search"></span>
                    $text
                </button>
            </span>
            
        </div> <!-- input group -->
    </div>
</div>
HTML_END;
    }

    $id_html = "";
    if (array_key_exists("id", $toolbar)) {
        $id_html = 'id="' . $toolbar["id"] . '"';
    }

    $html_toolbar = <<< HTML_END
<div class="btn-toolbar" role="toolbar" $id_html>
    $html_toolbar
</div>
HTML_END;

    $html_full = <<< HTML_END
    
<div class="btn_list">
        $html_toolbar
</div>
<hr id="btn_list_hr" style="display:none"/>
HTML_END;

    return $html_full;
}
function html_toolbar1($toolbar) {
    $html_toolbar = "";
    foreach ($toolbar["button_groups"] as $btn_group) {
        $html_btns = "";
        foreach ($btn_group["buttons"] as $btn) {
            $cls = "btn-default";
            if (array_key_exists("class", $btn)) {
                $cls = $btn["class"];
            }
            $html = "<button type=\"button\" class=\"btn $cls\">";
            if (array_key_exists("icon", $btn)) {
                $icon = $btn["icon"];
                $html .= "<span class=\"glyphicon $icon\"></span> ";
            }
            $text = lang_text_i18n($btn["i18n"]);
            $html .= $text;
            $html .= '</button>';
            $html_btns .= $html;
        }
        $cls = "btn-group";
        if (array_key_exists('class', $btn_group)) {
            $cls .= " " . $btn_group['class'];
        }
        $html_btns = "<div class=\"$cls\" role=\"group\" >$html_btns</div>";
        $html_toolbar .= $html_btns . "\n";
    }

    if(array_key_exists("search_control", $toolbar)) {
        $search = $toolbar["search_control"];
        $text = lang_text_i18n("s_common_search");
        $cls = "";
        if (array_key_exists("class", $search)) {
            $cls .= "pull-right";
        }
        $id_html = "";
        if (array_key_exists("id", $search)) {
            $id_html = 'id="' . $search['id'] . '"';
        }
        $html_toolbar .= <<< HTML_END
<div class="form-inline">
    <div class="form-group $cls">
        <div class="input-group" $id_html>
            <input type="text" class="form-control" maxlength="32">
            <span class="input-group-btn">
                <button class="btn btn-default btn-group" type="button">
                    <span class="glyphicon glyphicon-search"></span>
                    $text
                </button>
            </span>
            <span class="glyphicon glyphicon-edit" id="check_edit"></span>
        </div> <!-- input group -->
    </div>
</div>
HTML_END;
    }

    $id_html = "";
    if (array_key_exists("id", $toolbar)) {
        $id_html = 'id="' . $toolbar["id"] . '"';
    }

    $html_toolbar = <<< HTML_END
<div class="btn-toolbar" role="toolbar" $id_html>
    $html_toolbar
</div>
HTML_END;

    $html_full = <<< HTML_END
    
<div class="btn_list">
        $html_toolbar
</div>
HTML_END;

    return $html_full;
}

?>
