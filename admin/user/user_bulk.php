<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk user actions
 *
 * @package    core
 * @copyright  Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/user_bulk_forms.php');

admin_externalpage_setup('userbulk');


echo '
<script src="/lib/jquery/jquery-3.6.4.min.js"></script>
';

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}
// Create the user filter form.
$ufiltering = new user_filtering();

// Create the bulk operations form.
$actionform = new user_bulk_action_form();
if ($data = $actionform->get_data()) {
    // Check if an action should be performed and do so.
    $bulkactions = $actionform->get_actions();
    if (array_key_exists($data->action, $bulkactions)) {
        redirect($bulkactions[$data->action]->url);
    }

}

$userbulkform = new user_bulk_form(null, get_selection_data($ufiltering));
//dd($userbulkform->get_data());
if ($data = $userbulkform->get_data()) {
//    dd($data);
    if (!empty($data->buttonsgrp2['addall'])) {
        add_selection_all($ufiltering);
//        dd($SESSION->bulk_users);
    } else if (!empty($data->addsel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers)) {
                add_selection_all($ufiltering);
            } else {
                foreach ($data->ausers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    if (!isset($SESSION->bulk_users[$userid])) {
                        $SESSION->bulk_users[$userid] = $userid;
                    }
                }
            }
        }

    } else if (!empty($data->buttonsgrp2['removeall'])) {
        $SESSION->bulk_users = array();

    } else if (!empty($data->removesel)) {
        if (!empty($data->susers)) {
            if (in_array(0, $data->susers)) {
                $SESSION->bulk_users = array();
            } else {
                foreach ($data->susers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    unset($SESSION->bulk_users[$userid]);
                }
            }
        }
    }

    // Reset the form selections.
    unset($_POST);
    $userbulkform = new user_bulk_form(null, get_selection_data($ufiltering));

}
echo $OUTPUT->header();

$ufiltering->display_add();
$ufiltering->display_active();

$userbulkform->display();

$actionform->display();

echo $OUTPUT->footer();
//try {
//    // Lấy tất cả các bản ghi từ bảng 'department'
//    $departments = $DB->get_records('department', null, 'name ASC');
////    $departmentTree = new MyClass();
//    $departmentTree = buildTree($departments);
//
//} catch (dml_exception $e) {
//
//}
//
//echo'<div id = "showtext"> </div>';
//echo '
//<!-- Modal -->
//
//<div class="modal fade" id="treeModal" tabindex="-1" role="dialog" aria-labelledby="treeModalLabel" aria-hidden="true">
//  <div class="modal-dialog" role="document" style="display: flex; place-content:center">
//    <div class="modal-content col-lg-7 p-0">
//      <div class="modal-header">
//        <h5 class="modal-title" id="treeModalLabel">Chọn phòng / ban</h5>
//        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
//          <span aria-hidden="true">&times;</span>
//        </button>
//      </div>
//      <div class="modal-body" style="background:white;">
//        <div id="treeContainer">';
//echo displayTree($departmentTree);
//
//echo '<style>
//
//.tree-container ul>li>a {
//    color: black;
//    transition: color 0.3s ease;
//}
//
//.tree-container ul>li>a:hover {
//    /* Chuyển sang màu xanh khi hover */
//    color: blue;
//}
//.tree-container ul{
//  display:inline-block; 0position: relative; float: left; clear: left;
//	margin:.15em;
//	padding:0;
//
//}
//.tree-container ul:before{
//	content:""; position: absolute; z-index: 1;
//	top:.25em; right:auto; bottom:0; left: 1.75em;
//	margin: auto;
//	border-right: dotted black .1em;
//	width: 0; height: auto;
//
//}
//.tree-container ul:after{
//	content: "-"; position: absolute; z-index: 3;
//	top:0; left:-.5em;
//	margin:.65em; padding:0;
//	width:.8em; height: .8em;
//	text-align:center; line-height: .6em; font-size: 1em;
//
//}
//.tree-container ul>li{
//	display: block; position: relative; float: left; clear: both;
//	right:auto;
//	padding-left: 1em;
//	width:auto;
//	text-align: center;
//
//}
//.tree-container ul>li>input{
//	display:block; position: absolute; float: left; z-index: 4;
//	margin:0 0 0 -1em; padding:0;
//	width:1em; height: 2em;
//	font-size: 1em;
//	opacity: 0;
//	cursor: pointer;
//}
//.tree-container ul>li>input:checked~ul:before{
//	display: none;
//}
//.tree-container ul>li>input:checked~ul:after{
//	content: "+"
//}
//.tree-container ul>li>input:checked~ul *{
//	display: none;
//}
//.tree-container ul>li>a{
//	display: block; position: relative; float: left; z-index: 3;
//	margin:.25em; padding:.25em;
//
//}
//.tree-container ul>li>a:after{
//	content: ""; display: block; position: absolute;
//	left:-1em; top:0; bottom:0;
//	margin: auto .25em auto .25em;
//	border-top: dotted black .1em;
//	width: .75em; height: 0;
//
//}
//
//.tree-container ul>li:last-child:before{
//	content: ""; display: block; position: absolute; z-index: 2;
//	top:1em; left:0; bottom:-.25em;
//	width:.75em; height:auto;
//
//}
//
//#tree{
//	position: relative; font-family: "Georgia";
//}
//#tree:before{
//	left:.5em;
//}
//#tree:after{
//	display: none;
//}
//
///*decoration*/
////.tree-container ul,ul>li:last-child:before{
////	background: white;
////}
//.tree-container ul>li{
//	background: transparent;
//}
//.tree-container ul:after{
//
//	color: black;
//	border:solid gray 1px;
//	border-radius: .1em;
//}
//.tree-container ul>li>a{
//	border-radius: .25em;
//	color: black;
//
//}
//
//.tree-container ul>li>input~a:before{
//	content:""; display: inline-block;
//	margin: 0 .25em  0 0;
//	width:1em; height: 1em; ;line-height: 1em;
//	background: url("data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAAAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAD///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAAABwAAABcAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABcAAAAPAAAACQNMcIsDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDTHCTAAAAHwAAABEDbqSpb8Hn/4bQ7/+Fz+7/hc/u/4XP7v+Fz+7/hc/u/4XP7v+Fz+7/hc/u/4XP7v+H0PD/OpfDyQJcikX///8BA3i0m1e13/+N1vH/h9Hu/4fR7v+H0e7/h9Hu/4fR7v+H0e7/h9Hu/4fR7v+H0e7/iNHu/3jJ6e0CgL97////AQJ8uZdGrdz/l9/2/5Tb9P+U2/T/lNv0/5Tb9P+U2/T/lNv0/5Tb9P+U2/T/lNv0/5Tb9P+b4fj/JpvTnQKGyCMCf76TYL7n/4XX8v+h5vr/oOX6/6Dl+v+g5fr/oOX6/6Lo+v+l6vv/per7/6Xq+/+l6vv/puz7/2vJ68kCis9VAoLCj33P8P9px+z/r/P//63x/v+t8f7/rfH+/63x/v+Y5fj/SbLj/0my4/9JsuP/Tbbl/wKKz8UCjtZ9Ao7WfQKFx4uW3vb/Trjn/0645/9OuOf/Trjn/0645/9OuOf/Trjn/4HR8P+S2vP/ktrz/5ng9v8ChceL////Af///wECiMuHn+X5/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+f5fn/AojLh////wH///8BAorPg6Pp+/+d4/n/neP5/53j+f+d4/n/neP5/53j+f+d4/n/neP5/53j+f+d4/n/o+n7/wKKz4P///8B////AQKN0oGo7f3/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+i5/v/ouf7/6jt/f8CjdKB////Af///wECj9Z9rvP//6vw/v+r8P7/q/D+/6vw/v+r8P7/q/D+/6vw/v+r8P7/q/D+/6vw/v+u8///Ao/Wff///wH///8BApHZXQKR2XsCkdl7ApHZewKR2XsCkdl7ApHZe/7+/f/19e7/6+vd//7JQf/0ti7/ApHZewKR2V3///8B////Af///wH///8B////Af///wH///8B////AQKS2ysCktt5ApLbeQKS23kCktt5ApLbeQKS2yv///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//w==");
//	background-repeat:no-repeat;
//	background-size:contain;
//}
//.tree-container ul>li>input:checked~a:before{
//	background-image: url("data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAAAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAD///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAAABwAAABcAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAXAAAABwNMcIsDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNMcIsDbqSphs/w/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4bP8P8DbqSpA3i0m4bP7v99yOj/fcjo/33I6P99yOj/fcjo/33I6P99yOj/fcjo/33I6P99yOj/fcjo/33I6P+Gz+7/A3i0mwJ8uZeK0/D/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/itPw/wJ8uZcCf76Tj9fy/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4/X8v8Cf76TAoLCj5Tb9P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+U2/T/AoLCjwKFx4uZ4Pb/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/meD2/wKFx4sCiMuHn+X5/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5/l+f8CiMuHAorPg6Pp+/+d4/n/neP5/53j+f+d4/n/neP5/53j+f+j6fr/o+n6/6Pp+v+j6fr/o+n6/6Pp+v+m7Pv/AorPgwKN0oGo7f3/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+r8P3/jNDt/4HF5/+Bxef/gcXn/4HF5/+Bxef/gcXn/wKN0oECj9Z9rvP//6vw/v+r8P7/q/D+/6vw/v+u8///j9Pv/4/T7/+r8P7/q/D+/6vw/v+r8P7/q/D+/67z//8Cj9Z9ApHZXQKR2XsCkdl7ApHZewKR2XsCkdl7ApHZewKR2Xv+/v3/+Pjz//Dw5v/p6dv//slB//S2Lv8Ckdl7ApHZXf///wH///8B////Af///wH///8B////Af///wECktsrApLbeQKS23kCktt5ApLbeQKS23kCktt5ApLbK////wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//w==");
//}
//</style>';
//
//echo '
//<script>
//
//$(document).ready(function() {
//    $("#treeModal .tree-container a").click(function() {
//        var selectedDepartment = $(this).text();
//         $("#selectedDepartment1").val(selectedDepartment);
//        $("#selectedDepartment").val(selectedDepartment);
//
//        $("#treeModal").modal("hide");
//
//
//    });
//});
//
//</script>
//';
//
//function buildTree($departments, $parent_id = 0): array
//{
//    $branch = [];
//    foreach ($departments as $department) {
//        if ($department->parent == $parent_id) {
//            $children = buildTree($departments, $department->id);
//            if ($children) {
//                $department->children = $children;
//            }
//            $branch[$department->id] = $department;
//            unset($departments[$department->id]);
//        }
//    }
//    return $branch;
//}
//function displayTree($tree): void
//{
//    echo '<div id="treeContainer" class="tree-container">';
//    echo '<ul  class="custom-tree" id="tree">';
//    foreach ($tree as $node) {
//        echo '<li>';
//        echo '<input type="checkbox" checked>';
//        echo '<a href ="#">' . $node->name . '</a>';
//        if (!empty($node->children)) {
//            echo '<ul class = "childen">';
//            displayTree($node->children);
//            echo '</ul>';
//        }
//        echo '</li>';
//    }
//    echo '</ul>';
//    echo'</div>';
//}
