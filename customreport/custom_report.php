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
 * Description of the file.
 *
 * @package   local_yourpluginname
 * @copyright Year Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once('../config.php');
 require_once($CFG->libdir.'/adminlib.php');
 require_once($CFG->libdir.'/authlib.php');
 require_once('filter_report.php');
 
 // Define context and check capabilities.
 $context = context_system::instance();
 //require_capability('moodle/site:config', $context);
   
 $PAGE->set_context($context);
 $PAGE->set_title(get_string('customreport', 'admin'));
 $PAGE->set_heading(get_string('customreport', 'admin'));
 $PAGE->set_pagelayout('admin');
 $PAGE->navbar->add(get_string('customreport', 'admin'));
 
 // Start page output.
 echo $OUTPUT->header();
 
 // Instantiate the form.
 $mform = new filter_form();
 
 // Form processing and displaying is done here.
 if ($mform->is_cancelled()) {
     // Handle form cancel operation, if cancel button is present on form.
 } else if ($data = $mform->get_data()) {
     // In this case you process validated data.
     $exam_name = $data->exam_name;
     $category_filter = $data->category;
     $select_category = [];
     $select_category = explode(' |||', $data->select_category);
     $includeCheckbox = $data->include_checkbox;
     $start_date = $data->_sdt;
     $end_date = $data->_edt;
 } else {
     // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed.
     // or on the first display of the form.
     $mform->set_data([]);
 }
 
 // Display the form.
 $mform->display();
 
 
 // Example function to generate a table.
 function generate_table($exam_name = '', $_category = '', $start_date = 0, $end_date = 0, $category_filter = '', $select_category = [], $includeCheckbox = 0) {
     global $DB, $USER;
    
     $role_assignments = $DB->get_records('role_assignments', array('userid' => $USER->id), '', 'id, roleid, contextid');
     $filtered_assignments = array_filter($role_assignments, function($assignment) {
        return $assignment->roleid != 5;
     });
     $category = [];
     if (!empty($filtered_assignments)) {
        foreach($filtered_assignments as $assignment) {
            $context_id = $assignment->contextid;
            $context = context::instance_by_id($context_id);
            array_push($category, $context->instanceid);
        }
    }
    
    
    $course_category = $DB->get_records('course_categories');
    uasort($course_category, function($a, $b) {
        return strcasecmp($a->name, $b->name);
    });
   
    function buildTree($categories) {
        $tree = [];
    
        //get properties of each category
        foreach ($categories as $category) {
            $tree[$category->id] = [
                'name' => $category->name,
                'id' => $category->id,
                'children' => [],
                'parent' => $category->parent
            ];
        }

        foreach ($tree as $id => &$node) {
            if ($node['parent'] != 0 && isset($tree[$node['parent']])) {
                $tree[$node['parent']]['children'][$id] = &$node;
            }
        }
    
        
        $tree = array_filter($tree, function($node) {
            return $node['parent'] == 0;
        });
    
        
        uasort($tree, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    
        
        $sortChildren = function(&$node) use (&$sortChildren) {
            uasort($node['children'], function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
            foreach ($node['children'] as &$child) {
                $sortChildren($child);
            }
        };
        //sort children categories
        foreach ($tree as &$node) {
            $sortChildren($node);
        }
    
        return $tree;
    }
    
    function buildHtmlList($tree, $prefix = '') {
        $html = '<div class="hello">';
    
        foreach ($tree as $id => $node) {
            $html .= '<details>';
            $html .= '<summary>';
            $html .= '<span class="folder-icon opened closed"></span>';
            $html .= '<a href="#" class="modal-link" data-value="' . $node['name'] . '" for="' . $prefix . $id . '">' . $node['name'] . '</a>';
            $html .= '</summary>';
    
            if (!empty($node['children'])) {
                $html .= '<ul>';
                $html .= buildHtmlList($node['children'], $prefix . $id . '_');
                $html .= '</ul>';
            }
    
            $html .= '</details>';
        }
        
        $html .= '</div>';
        return $html;
    }
    // dd($course_category);
    $tree = buildTree($course_category);
    $finalTree = buildHtmlList($tree);
    echo '
    <div id="treeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="text-align: center;">' .get_string('choosecategory', 'core_grades').'</h2>
                <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="'.get_string('search', 'core').'...">
                <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
            </div>
            <div class="modal-body">
                <div class="line-tree" style="padding:5px; width: 70%; float: left;">
                    '.$finalTree.'
                </div>
                <div class="selected-items" style="width: 30%; float: right; padding: 5px;">
                    <h3>'.get_string("selecteditems", "core_form").'</h3>
                    <ul id="selectedItemsList"></ul>
                </div>
            </div>
            <div class="modal-footer" style="clear: both; padding-top: 10px;">
                <button id="acceptBtn" class="btn btn-primary">'. get_string("accept", "mod_lti").'</button>
                <button id="cancelBtn" class="btn btn-secondary">'. get_string("cancel", "core").'</button>
            </div>
        </div>
    </div>';
    
    
    
    echo '<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 50%;
        max-width: 80%;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        position: relative;
    }
    .modal-header .close {
        margin: 0.2rem 0.2rem 0.2rem auto !important;
    }
    .close {
        color: #f8f6f6 !important;
        position: absolute;
        right: 20px;
        top: 20px;
        background: #f50707;
        width: 30px;
        height: 30px;
        text-align: center;
        border-radius: 50%;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    #searchInput {
        width: 70%;
        border: 1px solid #ccc;
        padding: 5px;
    }
   .line-tree {
    padding: 5px;
    width: 100%;
    box-sizing: border-box;
    max-height: 500px;
    overflow-y: scroll;
}
.line-tree .hello {
    padding-right: 10px;
}
.line-tree details {
    outline: 2px dotted #3744f5;
    border-radius: 0.5em;
    margin: 10px;
    position: relative;
    padding-left: 1em;
    padding-bottom: 10px
}
.line-tree ul {
    padding-left: 40px;  /* This indents child items by 40px */
}
.line-tree ul .hello > details {
    outline: none;
    padding: 0;
}
.line-tree ul .hello > details:before {
    content: "";
    position: absolute;
    top: 0;
    left: -24px;  /* Adjusted to account for the new indentation */
    width: 24px;  /* Increased width to reach the parent */
    height: 1em;
    border-bottom: 2px dotted #055091;
}
.line-tree ul .hello > details:after {
    content: "";
    position: absolute;
    top: 0;
    left: -24px;  /* Adjusted to account for the new indentation */
    width: 24px;  /* Increased width to reach the parent */
    height: 100%;
    border-left: 2px dotted #055091;
}
.line-tree details details {
    border: none;
    padding-left: 0;  /* Removed extra padding */
    margin-bottom: 0;
    margin-right: 0;  /* Removed right margin */
}
    .line-tree summary {
        font-weight: bold;
        cursor: pointer;
        margin-top: 5px;
        background: #e1e6ee;
        color: #000;
        border-radius: 0.3em;
        padding: 0.3em;
        outline: 1.5px solid #0876e5;
    }
    .line-tree a {
        color: black;
        text-decoration: none;
    }
    .line-tree a:hover {
        color: #276ef8;
    }
    .line-tree summary::marker {
        content: "";
    }
    .line-tree summary::-webkit-details-marker {
        display: none;
    }
    .line-tree summary::-moz-list-bullet {
        display: none;
    }
    .line-tree .folder-icon {
        display: inline-block;
        width: 16px;
        height: 16px;
        background-size: contain;
        background-repeat: no-repeat;
        margin-right: 5px;
    }
    .line-tree details > summary:not(:empty)::before {
        background: #055091;
        content: "+";
        font-family: "cocoonCustomPrimary", sans-serif;
        font-weight: 900;
        font-size: 16px;
        color: #fff0f0;
        display: inline-block;
        margin-right: 0.5em;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        border-radius: 50%;
    }
    .line-tree details[open] > summary:not(:empty)::before {
        content: "-";
    }
    .line-tree details > summary:not(:empty)::before {
        background: #055091;
        content: "+";
        font-family: "cocoonCustomPrimary", sans-serif;
        font-weight: 900;
        font-size: 16px;
        color: #fff0f0;
        display: inline-block;
        margin-right: 0.5em;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        border-radius: 50%;
    }
    .line-tree details[open] > summary:not(:empty)::before {
        content: "-";
    }
    .ccn-4-navigation{
        display: none;
    }
    </style>';
    
    echo '
<script>
$(document).ready(function() {
    var $openModalBtn = $("#id_openModalBtn1");
    var $treeModal = $("#treeModal");
    var $closeBtn = $treeModal.find(".close");
    var selectedValues = [];

    if ($openModalBtn.length) {
        $openModalBtn.on("click", function() {
            $treeModal.css("display", "block");
        });
    } else {
        console.warn("Button with id \'id_openModalBtn1\' not found");
        $("body").prepend(\'<button id="openModalBtn1">Open Category Tree</button>\');
        $("#id_openModalBtn1").on("click", function() {
            $treeModal.css("display", "block");
        });
    }

    $closeBtn.on("click", function() {
        $treeModal.css("display", "none");
    });

    $(window).on("click", function(event) {
        if (event.target == $treeModal[0]) {
            $treeModal.css("display", "none");
        }
    });

    var $searchInput = $("#searchInput");
    var $allItems = $(".line-tree details");

    function resetTreeView() {
        $allItems.css("display", "block").prop("open", false);
    }

    $searchInput.on("input", function() {
        var searchText = $(this).val().toLowerCase().trim();
        if (searchText === "") {
            resetTreeView();
            return;
        }

        $allItems.css("display", "none").prop("open", false);

        $allItems.each(function() {
            var $item = $(this);
            var text = $item.find("summary").text().toLowerCase();
            if (text.includes(searchText)) {
                showParents($item);
                $item.css("display", "block").prop("open", true);
            }
        });
    });

    function showParents($element) {
        $element.parents("details").css("display", "block").prop("open", true);
    }

    function updateElementsState(value) {
        var $selectCategory = $("#id_select_category");
        var $openModalBtn = $("#id_openModalBtn1");
        var $includeCheckbox = $("#id_include_checkbox");

        if (value === "similar") {
            $selectCategory.prop("disabled", true);
            $openModalBtn.prop("disabled", true);
            $includeCheckbox.prop("disabled", true);
        } else {
            $selectCategory.prop("disabled", false);
            $openModalBtn.prop("disabled", false);
            $includeCheckbox.prop("disabled", false);
        }
    }

    // Initial state setup
    var initialValue = $("#id_category").val();
    updateElementsState(initialValue);

    // Update state on change
    $("#id_category").on("change", function() {
        var value = $(this).val();
        updateElementsState(value);
    });

    // Modify modal link click event for multi-select
    $(".modal-link").off("click").on("click", function(event) {
        event.preventDefault();
        var value = $(this).data("value");
        var index = selectedValues.indexOf(value);
        
        if (index > -1) {
            selectedValues.splice(index, 1);
            $(this).removeClass("selected");
        } else {
            selectedValues.push(value);
            $(this).addClass("selected");
        }
        
        updateSelectedItemsList();
        console.log("Selected values:", selectedValues);
    });

    function updateSelectedItemsList() {
        var $list = $("#selectedItemsList");
        $list.empty();
        selectedValues.forEach(function(value) {
            $list.append("<li>" + value + " <button class=\'remove-selected\' data-value=\'" + value + "\'>×</button></li>");
        });
    }

    function updateInputAndTags() {
        var $input = $("#id_select_category");
        var $tagContainer = $("#tag-container");
        
        $input.val(selectedValues.join(" |||"));
        $tagContainer.empty();

        selectedValues.forEach(function(value) {
            var $tag = $("<span class=\'tag\'>" + value + " <button class=\'remove-tag\' data-value=\'" + value + "\'>×</button></span>");
            $tagContainer.append($tag);
        });
    }

    // Add tag container after the input
    $("#id_select_category").after("<div id=\'tag-container\'></div>");

    // Handle tag removal
    $(document).on("click", ".remove-tag", function() {
        removeValue($(this).data("value"));
    });

    // Handle removal from selected items list
    $(document).on("click", ".remove-selected", function() {
        removeValue($(this).data("value"));
    });

    function removeValue(value) {
        var index = selectedValues.indexOf(value);
        if (index > -1) {
            selectedValues.splice(index, 1);
        }
        updateSelectedItemsList();
        updateInputAndTags();
        $(".modal-link[data-value=\'" + value + "\']").removeClass("selected");
        console.log("Value removed:", value);
        console.log("Selected values:", selectedValues);
    }

    // Accept button click handler
    $("#acceptBtn").on("click", function() {
        updateInputAndTags();
        $treeModal.css("display", "none");
    });

    // Cancel button click handler
    $("#cancelBtn").on("click", function() {
        selectedValues = [];
        $(".modal-link").removeClass("selected");
        updateSelectedItemsList();
        $treeModal.css("display", "none");
    });

    // Update tags on input change
    $("#id_select_category").on("input", function() {
        selectedValues = $(this).val().split(",").filter(item => item.trim() !== "");
        updateInputAndTags();
    });

    // Initial update
    updateInputAndTags();
});
</script>
    
    <style>
       
    
      .modal-content {
        display: flex;
        flex-direction: column;
        height: 80vh;
    }

    .modal-body {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .line-tree {
        flex: 0 0 70%;
        overflow-y: auto;
    }

    .selected-items {
        flex: 0 0 30%;
        padding: 5px;
        background-color: #f0f0f0;
        overflow-y: auto;
    }

    .modal-footer {
        padding-top: 10px;
        text-align: right;
    }

    #id_select_category {
        display: none;
    }

    .tag {
        display: inline-block;
        background-color: #1e4fa5;
        box-shadow: 0 0 0 .2rem rgba(15,108,191,.75);
        padding: 2px 5px !important;
        margin: 4px;
        color: white;
        border-radius: 1.5rem;
        font-weight: bold;
    }


    #selectedItemsList li{
        display: inline-block;
        background-color: #1e4fa5;
        box-shadow: 0 0 0 .2rem rgba(15,108,191,.75);
        padding: 2px 5px !important;
        margin: 4px;
        color: white;
        border-radius: 1.5rem;
        font-weight: bold;
    }

    .remove-tag, .remove-selected, li .remove-selected {
        background: none;
        border: none;
        color: white;
        font-weight: bold;
        cursor: pointer;
        font-size: 20px;
    }

    .modal-link.selected {
        background-color: #e1e1e1;
        font-weight: bold;
    }

    #tag-container {
        margin-top: 5px;
        white-space: initial !important;
    }

    #selectedItemsList li {
        margin-bottom: 5px;
    }

    .remove-selected {
        color: red;
        margin-left: 5px;
    }
    </style>
    ';

    function get_descendant_categories($parent_id) {
        global $DB;
        
        $descendants = [];
        
        // Get all child categories of the current parent category
        $categories = $DB->get_records('course_categories', ['parent' => $parent_id], '', 'id');
        
        // Add the child categories to the descendants array
        foreach ($categories as $category) {
            $descendants[] = $category->id;
            // Recursively get the descendants of each child category
            $descendants = array_merge($descendants, get_descendant_categories($category->id));
        }
        
        return $descendants;
    }
    // Define your variables
    $category = [];
$page = optional_param('page', 0, PARAM_INT);
$perpage = 30;  
$limitfrom = $page * $perpage; 
$limitnum = $perpage;

// Prepare SQL filters
$filters = [];
$params = ['contextlevel' => CONTEXT_COURSE];

// Filter by category
if (!empty($select_category) || $select_category[0] == '') {
    $category_ids = [];
    foreach ($select_category as $select) {
        $cat = $DB->get_record('course_categories', ['name' => $select], 'id');
        if ($cat) {
            $category_ids[] = $cat->id;
            
            // If $includeCheckbox is set, find all descendant categories
            if ($includeCheckbox == 1) {
                $descendant_ids = get_descendant_categories($cat->id);
                $category_ids = array_merge($category_ids, $descendant_ids);
            }
        }
    }

    if ($category_filter == 'equal') {
        $filters[] = 'c.category IN (' . implode(',', array_map('intval', $category_ids)) . ')';
    } elseif ($category_filter == 'notequal') {
        $filters[] = 'c.category NOT IN (' . implode(',', array_map('intval', $category_ids)) . ')';
    }
}

// Filter by exam name
if (!empty($exam_name)) {
    $filters[] = $DB->sql_like('q.name', ':examname', false);
    $params['examname'] = "%$exam_name%";
}

// Filter by date range
if (!empty($start_date)) {
    $filters[] = 'q.timeopen >= :startdate';
    $params['startdate'] = $start_date;
}
if (!empty($end_date)) {
    $filters[] = 'q.timeopen <= :enddate';
    $params['enddate'] = $end_date;
}

// Construct the SQL query
$sql = "SELECT q.*, c.category, c.timecreated, ctx.id as contextid
        FROM {quiz} q
        JOIN {course} c ON q.course = c.id
        JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel";

// Apply the filters
if (!empty($filters)) {
    $sql .= " WHERE " . implode(' AND ', $filters);
}

// Add the ORDER BY and pagination (LIMIT and OFFSET)
$sql .= " ORDER BY q.timeopen DESC";


// Fetch the filtered and paginated quiz and course data
$quizzes = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

// Continue with your post-processing (e.g., fetching enrolled users, calculating totals, etc.)
$course_enrolled_users = [];
$category = $DB->get_records('course_categories');
$records = [];
foreach ($quizzes as $quiz) {
    // Fetch enrolled users and calculate completion stats
    $context = context_course::instance($quiz->course);
    $enrolledusers = get_enrolled_users($context);
    $quiz->total_users = count($enrolledusers);

    $cm = get_coursemodule_from_instance('quiz', $quiz->id);
    
    $sql = "SELECT userid 
    FROM {quiz_attempts} 
    WHERE quiz = :quizid AND state = :state AND preview = 0
    GROUP BY userid";

    $params = array('quizid' => $quiz->id, 'state' => 'finished');
    
    // Thực hiện truy vấn và lấy số lượng kết quả
    $finished = $DB->count_records_sql("SELECT COUNT(*) FROM ({$sql}) AS subquery", $params);
    $course_module = $DB->get_record('course_modules', ['instance' => $quiz->id]);
    $category_name = $category[$quiz->category]->name;

    $record = (object) [
        'id' => new moodle_url('/mod/quiz/report.php', ['id' => $course_module->id]),
        'column1' => $quiz->name, 
        'column2' => $category_name,  
        'column3' => date('Y-m-d', $quiz->timeopen), 
        'column4' => $quiz->total_users, 
        'column5' => min($quiz->total_users, $finished), 
        'column6' => max(0, $quiz->total_users - $finished)
    ];

    $records[] = $record;
}

// Process and output the records (if needed)

 
     // Start table output.
     
     $total = $DB->count_records('course');
     $totalPages = ceil($total / $perpage);
     $start = $page * $perpage;
     if ($totalPages > 1) {
        echo '<div class="mbp_pagination">';
        echo '<ul class="page_navigation">';
        
        // Add "Quay lại" button
        if ($page > 0) {
            $prevPageUrl = new moodle_url($PAGE->url, array_merge($_GET, ['page' => $page - 1]));
            echo '<li class="page-item ccn-page-item-prev">
                    <a href="' . $prevPageUrl . '" class="page-link">
                        <span class="flaticon-left-arrow"></span> Quay lại
                    </a>
                  </li>';
        } else {
            echo '<li class="page-item disabled ccn-page-item-prev">
                    <span class="page-link">
                        <span class="flaticon-left-arrow"></span> Quay lại
                    </span>
                  </li>';
        }
        
        $displayPages = 10; // Number of page links to display
        $startPage = max(1, min($page - floor($displayPages / 2), $totalPages - $displayPages));
        $endPage = min($startPage + $displayPages - 1, $totalPages);
        
        // Always show page 1
        if ($startPage > 1) {
            echo '<li class="page-item">';
            $url = new moodle_url($PAGE->url, array_merge($_GET, ['page' => 0]));
            echo '<a href="' . $url . '" class="page-link">1</a></li>';
            
            if ($startPage > 2) {
                echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }
        
        // Display the page links
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page + 1) ? ' active' : '';
            echo '<li class="page-item' . $activeClass . '">';
            $url = new moodle_url($PAGE->url, array_merge($_GET, ['page' => $i - 1]));
            echo '<a href="' . $url . '" class="page-link">';
            echo $i;
            if ($i == $page + 1) {
                echo '<span class="sr-only">(current)</span>';
            }
            echo '</a></li>';
        }
        
        // Add ellipsis and last page if necessary
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            echo '<li class="page-item">';
            $url = new moodle_url($PAGE->url, array_merge($_GET, ['page' => $totalPages - 1]));
            echo '<a href="' . $url . '" class="page-link">' . $totalPages . '</a></li>';
        }
      
        // Add "Trang tiếp" button
        if ($page < $totalPages - 1) {
            $nextPageUrl = new moodle_url($PAGE->url, array_merge($_GET, ['page' => $page + 1]));
            echo '<li class="page-item ccn-page-item-prev">
                    <a href="' . $nextPageUrl . '" class="page-link" aria-label="Next">
                        Tiếp theo <span class="flaticon-right-arrow-1"></span>
                    </a>
                  </li>';
        }
        
        echo '</ul>';
        echo '</div>';
    }
     $table = new html_table();
     $table->head = array(
         html_writer::tag('a', get_string('quizname','admin'), array('href' => '#')),
         html_writer::tag('a', get_string('quizcategory','admin'), array('href' => '#')),
         html_writer::tag('a', get_string('startdate','admin'), array('href' => '#')),
         html_writer::tag('a', get_string('totaluser','admin'), array('href' => '#')),
         html_writer::tag('a', get_string('usercomplete','admin'), array('href' => '#')),
         html_writer::tag('a', get_string('usernotcomplete','admin'), array('href' => '#'))
     ); // Define table headers.

     foreach ($records as $record) {
        $row = array();
        $row[] = '<a href="' . $record->id . '">' . $record->column1 . '</a>';
        $row[] = $record->column2;
        $row[] = $record->column3;
        $row[] = $record->column4;
        $row[] = $record->column5;
        $row[] = $record->column6;
        $table->data[] = $row;
    }
    
 
     // Render the table.
     echo html_writer::table($table);
    

 }
 
 // Call the function to display the table with filtered data.
 generate_table($exam_name ?? '', $_category ?? '', $start_date ?? 0, $end_date ?? 0, $category_filter ?? '', $select_category ?? '', $includeCheckbox ?? 0);
 
 echo $OUTPUT->footer();
 