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
 * Move questions page.
 *
 * @package    qbank_bulkmove
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../editlib.php');
use qbank_managecategories\helper;
global $DB, $OUTPUT, $PAGE, $COURSE;

$moveselected = optional_param('move', false, PARAM_BOOL);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);
$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$category = optional_param('category', null, PARAM_SEQUENCE);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);
$movequestionselected = optional_param('movequestionsselected', null, PARAM_RAW);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
}

\core_question\local\bank\helper::require_plugin_enabled('qbank_bulkmove');

if ($cmid) {
    list($module, $cm) = get_module_from_cmid($cmid);
    require_login($cm->course, false, $cm);
    $thiscontext = context_module::instance($cmid);
    
} else if ($courseid) {
    require_login($courseid, false);
    $thiscontext = context_course::instance($courseid);
} else {
    throw new moodle_exception('missingcourseorcmid', 'question');
}
$contexts = new core_question\local\bank\question_edit_contexts($thiscontext);
$all_question_bank_categories = $DB->get_records('course_categories', ['isquestionbank' => 1]);
$all_question_bank_categories_ids = array_keys($all_question_bank_categories);
$all_contexts = [];

foreach($all_question_bank_categories_ids as $category_id) {
    $all_contexts[] = \core\context\coursecat::instance($category_id);
    
}

$url = new moodle_url('/question/bank/bulkmove/move.php');

$PAGE->set_url($url);
$streditingquestions = get_string('movequestions', 'qbank_bulkmove');
$PAGE->set_title($streditingquestions);
$PAGE->set_heading($COURSE->fullname);
$PAGE->activityheader->disable();
$PAGE->set_secondary_active_tab("questionbank");

if ($category) {
    list($tocategoryid, $contextid) = explode(',', $category);
    if (! $tocategory = $DB->get_record('question_categories',
        ['id' => $tocategoryid, 'contextid' => $contextid])) {
        throw new \moodle_exception('cannotfindcate', 'question');
    }
}


if ($movequestionselected && $confirm && confirm_sesskey()) {
    if ($confirm == md5($movequestionselected)) {
        \qbank_bulkmove\helper::bulk_move_questions($movequestionselected, $tocategory);
    }
    redirect(new moodle_url($returnurl, ['category' => "{$tocategoryid},{$contextid}"]));
}

echo $OUTPUT->header();

if ($moveselected) {
    $rawquestions = $_REQUEST;
    list($questionids, $questionlist) = \qbank_bulkmove\helper::process_question_ids($rawquestions);
    // No questions were selected.
    if (!$questionids) {
        redirect($returnurl);
    }
    // Create the urls.
    $moveparam = [
        'movequestionsselected' => $questionlist,
        'confirm' => md5($questionlist),
        'sesskey' => sesskey(),
        'returnurl' => $returnurl,
        'cmid' => $cmid,
        'courseid' => $courseid,
    ];
    $moveurl = new \moodle_url($url, $moveparam);

    $addcontexts = $contexts->having_cap('moodle/question:add');
    $addcontexts = array_merge($addcontexts, $all_contexts);
    // dd($addcontexts);

    $displaydata = \qbank_bulkmove\helper::get_displaydata($addcontexts, $moveurl, $returnurl);
    $catmenu = helper::question_category_options($addcontexts, true, 0,
        true, -1, false);
        // $catmenu = array_slice($catmenu, 1);
        
        foreach ($catmenu as &$category) {
            foreach ($category as $key => &$options) {
               
                $options = array_slice($options, 0);
            }
        }
        unset($category); 
        // $catmenu= array_slice($catmenu,1);
            function compareByName($a, $b) {
                return strcmp($a, $b);
            }
        function compareValues($a, $b) {
        // Get the keys from the inner arrays
        $keyA = key($a);
        $keyB = key($b);
        
        // Compare the keys lexicographically
        return strcoll($keyA, $keyB);
        }
        usort($catmenu, function ($a, $b) {
            // Get the keys from the inner arrays
            $keyA = key($a);
            $keyB = key($b);
            
            // Compare the keys lexicographically
            return strcoll($keyA, $keyB);
        });    

      $currentUrl = $_SERVER['REQUEST_URI']; 

      if (strpos($currentUrl, '/question/edit1.php') !== false) {

          $catmenu = array_slice($catmenu, 1);
      }
     
      $totalQuestions = 0;
      $totalQuestions = 0;
      for ($i = 0; $i < count($catmenu) - 1; $i++) {
          $category = $catmenu[$i];
         
          $questions = $category[key($category)];
          foreach ($questions as $question) {
    
              if (preg_match('/\((\d+)\)/', $question, $matches)) {
        
                  $numberOfQuestions = (int)$matches[1];
                  $totalQuestions += $numberOfQuestions;
              }
          }
      }  
      function buildCategoryTree($categories) {
        $html = '';
        foreach ($categories as $category) {
            foreach ($category as $folder => $files) {
                $folder_clean = trim(preg_replace('/&nbsp;/', '', $folder));
                $html .= '<li class="big mb-3"><details><summary>' . htmlspecialchars($folder_clean) . '</summary>';
                $html .= buildSubcategories($files);
                $html .= '</details></li>';
            }
        }
      
        return $html;
    }

    // Function to build subcategories
    function buildSubcategories($subcategories) {
        $html = '';
        $prevDepth = -1;
        foreach ($subcategories as $key => $subcategory) {
       
            $depth = substr_count($subcategory, '&nbsp;') / 3;
            $file_clean = trim(preg_replace('/\[\d+\]/', '', preg_replace('/&nbsp;/', '', $subcategory)));
            list($catId, $pathId) = explode(',', $key);
            $categoryParamValue = urlencode($catId) . '%2C' . urlencode($pathId);
            $url = "/question/edit1.php?courseid=1&deleteall=1&category={$categoryParamValue}&searchtext=&qbshowtext=1&recurse=0&recurse=1&showhidden=0&showhidden=1";
    
            if ($depth > $prevDepth) {
                $html .= '<li><details><summary class="sub sub1" ><a href="#" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
            } elseif ($depth == $prevDepth) {
                $html .= '</details></li><li><details><summary class="sub sub2" ><a href="#" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
            } else {
                $html .= '</details></li>';
                for ($i = $prevDepth - $depth; $i > 0; $i--) {
                    $html .= '</ul></details></li>';
                }
                $html .= '<li><details><summary class="sub sub" ><a href="#" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
            }
    
            $prevDepth = $depth;
        }
    
     
    
        for ($i = $prevDepth; $i >= 0; $i--) {
            $html .= '</ul></details></li>';
        }
    
    
        return $html;
    }
// $displaydata['categoryselect'] = \html_writer::select($catmenu, 'category', $this->cat, [],
// array('class' => 'searchoptions custom-select', 'id' => 'id_selectacategory'));

// Sorting the $catmenu
usort($catmenu, function ($a, $b) {
    $keyA = key($a);
    $keyB = key($b);
    return strcoll($keyA, $keyB);
});


        $name = get_string('selectacategory', 'question');
        echo '
        <button id="openModalBtn1" style="color:white;padding:10px;background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">'.$name.'</button>
        <div id="myModal1" class="modal">
        <div class="modal-content">
        <div class="modal-header">
            <h2 style="text-align: center;">'.$name.'</h2>
            <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="Tìm kiếm...">
            <span class="close1 d-flex align-items-center" style="place-content:center">&times;</span>
        </div>';
        echo '     
        <div style="padding: 0 1rem; overflow: auto;">
        <ul>' . buildCategoryTree($catmenu) . ' </ul>
        </div>
        </div>
        </div>';

    

        echo '
        <style>           
        label.mr-1 {
            display:none;
        }
        select#id_selectacategory {
            position: absolute;
            top: 14.7rem;
            margin-left: 13rem;
            pointer-events: none; 
            touch-action: none; 
            user-select: none; 
            -webkit-touch-callout: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -khtml-user-select: none;
            -webkit-user-select: none;
        }
                    
        .modal {
        display: none;  
        position: fixed; 
        z-index: 999; 
        left: 0;
        top: 0;

        background-color: rgba(0,0,0,0.4);

        }

      
            .modal-content {
            background-color: #fefefe;
            margin: 10% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 100%;
            max-width: 80%; 
            overflow: hidden; 
            max-height: 70%;
            }

        .close1{
        color: #f8f6f6 !important;
            cursor: pointer;
            position: absolute;
            right: 40px;
            top: 40px;
            background: #f50707;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
        color: #f8f6f6 !important;
        text-decoration: none;
        cursor: pointer;
        }

        .modal ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        }

        .modal li {
        position: relative;
        margin: 0;
        padding: 0 0 0 1em;
        }

        .modal li li {
        padding: 0 0 0 2em;

        }

        .modal li li::before {
        content: "";
        position: absolute;
        top: 0;
        left: 16px;
        width: 13px;
        height: 100%;
        border-left: 2px dotted #055091; /* Dotted line connecting nodes */
        }

        .modal li li::after {
        content: "";
        position: absolute;
        top: 0;
        left: 16px;
        width: 13px;
        height: 1em;
        border-bottom: 2px dotted #055091; /* Dotted line connecting nodes horizontally */
        }

        .big {
            margin-bottom: 10px; 
            font-weight: bold;
            cursor: pointer;
            outline: 2px dotted #3744f5;
            margin-top:5px;
            background: #fafbff;
            color: #110101;
            border-radius: 0.5em;
            padding-bottom: 10px !important; }


        details .folder-icon {
            display: inline-block;
            width: 16px; 
            height: 16px; 
            background-size: contain;
            background-repeat: no-repeat;
            margin-right: 5px; 

            
        }
        details summary {
            font-weight: bold;
            cursor: pointer;
            outline: 1.5px solid #0876e5;
            margin-top: 5px;
            background: #e1e6ee;
            color: #110101;
            border-radius: 0.5em;
            padding-bottom: 5px;
        }

        details summary::marker {
        content: "";
        }


        details > summary {
        cursor: pointer;
        }


        details > summary::before {
        background: #055091; 
            content: "+"; 
            font-family: "cocoonCustomPrimary";
            font-weight: 900;
            font-size: 16px;
            color: #fff0f0; 
            display: inline-block;
            margin-left: 0.8em; 
            margin-right: 10px;
            width: 20px; 
            height: 20px;
            text-align: center; 
            line-height: 20px;
            border-radius: 50%;
        }

        details[open] > summary::before {
            background-image: none; 
            content: "-";
            font-family: "cocoonCustomPrimary";
            font-weight: 900;
            font-size: 16px; 
            color: #fff0f0;       
            display: inline-block;
            margin-left: 0.8em;
            margin-right: 10px;
        }
        details:not(:has(details)) sumaary::maker{
            content: none;
            margin-left: 0.8em;
            margin-right: 10px;  
        }
        
        details:not(:has(details)) > summary::before {
            content: none;
            margin-left: 0.8em;
            margin-right: 10px;
            
        }
        details[open]:not(:has(details)) > summary::before {
            content: none;
            margin-left: 0.8em;
            margin-right: 10px;
            
        }
        
        details:not(:has(details)) > summary {
            content: none;
            margin-left: 0.8em;
            margin-right: 10px;
            
        }
        
        details:not(:has(details)) > summary > a {
            content: none;
            margin-left: 0.8em;
            margin-right: 10px;
            
        }
        </style>
                
                ';

                echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("myModal1");
            var btn = document.getElementById("openModalBtn1");
            var span = document.getElementsByClassName("close1")[0];
            console.log(span);
        
            
            if (btn) {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();
                    modal.style.display = "block";
                });
            }
        
            
            if (span) {
                span.addEventListener("click", function() {
                    console.log("span clicked");
                    modal.style.display = "none";
                });
            }
        
          
            window.addEventListener("click", function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        
            
            document.querySelectorAll(".modal-link").forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault();
                    var value = this.getAttribute("data-value");
                    var select = document.getElementById("id_movetocategory");
                    if (select) {
                        select.value = value;
                        modal.style.display = "none";
                        $(`input[name="cat"]`).val(select.value);
                        console.log($(`input[name="cat"]`).val());
                    } else {
                        console.error("Không tìm thấy phần tử có id \'id_category\'");
                    }
                });
            });
        
            
            function selectOption(value) {
                var select = document.getElementById("id_movetocategory");
                if (select) {
                    select.value = value;
                } else {
                    console.error("Không tìm thấy phần tử có id \'id_category\'");
                }
                $("#myModal").modal("hide"); 
            }
            var searchInput = document.getElementById("searchInput");
            var allItems = document.querySelectorAll("#myModal1 li");

            function resetTreeView() {
                allItems.forEach(function(item) {
                    item.style.display = "block";
                    var details = item.querySelector("details");
                    if (details) details.open = false;
                });
            }

            searchInput.addEventListener("input", function() {
                var searchText = this.value.toLowerCase().trim();

                if (searchText === "") {
                    resetTreeView();
                    return;
                }

                var foundItems = [];

                allItems.forEach(function(item) {
                    item.style.display = "none";
                    var details = item.querySelector("details");
                    if (details) details.open = false;
                });


                allItems.forEach(function(item) {
                    var text = item.textContent.toLowerCase();
                    if (text.includes(searchText)) {
                        foundItems.push(item);
                        var parent = item.parentElement;
                        while (parent && parent.tagName === "UL") {
                            var parentLi = parent.closest("li");
                            if (parentLi) {
                                foundItems.push(parentLi);
                                parent = parentLi.parentElement;
                            } else {
                                break;
                            }
                        }
                    }
                });

        
                foundItems.forEach(function(item) {
                    item.style.display = "block";
                    var details = item.querySelector("details");
                    if (details) details.open = true;
                });
            });



        });
        </script>';




    echo $PAGE->get_renderer('qbank_bulkmove')->render_bulk_move_form($displaydata);
}

echo $OUTPUT->footer();
