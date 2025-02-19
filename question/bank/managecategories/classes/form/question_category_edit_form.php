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

namespace qbank_managecategories\form;

use core_course_category;
use moodleform;
use qbank_managecategories\helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


/**
 * Defines the form for editing question categories.
 *
 * Form for editing questions categories (name, description, etc.)
 *
 * @package    qbank_managecategories
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_edit_form extends moodleform {

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the manage categories feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        global $DB;
        $mform = $this->_form;

        $contexts = $this->_customdata['contexts'];
        
        // dd($catmenu);
        $currentcat = $this->_customdata['currentcat'];

        $mform->addElement('header', 'categoryheader', get_string('addcategory', 'question'));
        
        // $contexts = array_slice($contexts,1);
        foreach($contexts as $key => $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                // remove this context from the list of contexts
                unset($contexts[$key]);
            }
        }
        $all_question_bank_categories = $DB->get_records('course_categories', ['isquestionbank' => 1]);
        $all_question_bank_categories_ids = array_keys($all_question_bank_categories);
        $all_contexts = [];
        foreach($all_question_bank_categories_ids as $category_id) {
            $cat = core_course_category::get($category_id);
                if(!$cat->can_create_course()) {
                    continue;
                }
            // tao instancee context
            $all_contexts[] = \core\context\coursecat::instance($category_id);
        }
      
        $contexts = array_merge($contexts, $all_contexts);
            
                    ($mform->addElement('questioncategory', 'parent', get_string('parentcategory', 'question'),
                    ['contexts' => $contexts, 'top' => true, 'currentcat' => $currentcat, 'nochildrenof' => $currentcat]));
                    $catmenu = helper::question_category_options($contexts, true, 0,
                    true, -1, false);
                    
                    foreach ($catmenu as &$category) {
                        foreach ($category as $key => &$options) {
                            $options = array_slice($options, 0);
                        }
                    }
                    unset($category); 
                
                    
                  $currentUrl = $_SERVER['REQUEST_URI']; 
                    if (strpos($currentUrl, '/question/edit1.php') !== false) {
         
                        $catmenu = array_slice($catmenu, 1);
                        
                    }
                    
                    
                    $totalQuestions = 0;
                    $totalQuestions = 0;
                    
                    for ($i = 0; $i < count($catmenu) - 1; $i++) {
                        $category = $catmenu[$i];
                        
                        $questions = $category[key($category)];
                        $questions= array_slice($questions, 1);
                        
                        
                        foreach ($questions as $question) {
                            
                            if (preg_match('/\((\d+)\)/', $question, $matches)) {
                      
                                $numberOfQuestions = (int)$matches[1];
                                $totalQuestions += $numberOfQuestions;
                            }
                        }
                    }  
    // Function to create summary/details elements recursively
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
                    $courseid = optional_param('courseid', 1, PARAM_INT);
                        $url = "/question/edit1.php?courseid={$courseid}&deleteall=1&category={$categoryParamValue}&qperpage=50&searchtext=&qbshowtext=1&recurse=0&recurse=1&showhidden=0&showhidden=1";
    
                    if ($depth > $prevDepth) {
                        $html .= '<li><details><summary class="sub sub1" ><a href="' . $url . '" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
                    } elseif ($depth == $prevDepth) {
                        $html .= '</details></li><li><details><summary class="sub sub2" ><a href="' . $url . '" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
                    } else {
                        $html .= '</details></li>';
                        for ($i = $prevDepth - $depth; $i > 0; $i--) {
                            $html .= '</ul></details></li>';
                        }
                        $html .= '<li><details><summary class="sub sub" ><a href="' . $url . '" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
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
                        <div id="myModal1" class="modal">
                        <div class="modal-content" style="width: 50% !important">
                        <div class="modal-header">
                            <h2 style="text-align: center;">'.$name.'</h2>
                            <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="Tìm kiếm...">

                            <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
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
                        z-index: 1; 
                        left: 0;
                        top: 0;
            
                        background-color: rgba(0,0,0,0.4);
            
                        }
            
                        .modal-content {
                        background-color: #fefefe;
                        margin: 15% auto; 
                        padding: 20px;
                        border: 1px solid #888;
                        width: 50%; 
                        overflow: hidden; 
                        max-height: 60%;
                        }
            
                        .close {
                        color: #f8f6f6;
                            
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
                        color: #f8f6f6;
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
              
        //    $displaydata['categoryselect'] = \html_writer::select($catmenu, 'category', $cat, [],
        //       array('class' => 'form-control custom-select ', 'id' => 'id_parent'));
        //     //   dd($displaydata);
      
        $mform->setType('parent', PARAM_SEQUENCE);
        if (helper::question_is_only_child_of_top_category_in_context($currentcat)) {
            $mform->hardFreeze('parent');
        }
        
        $mform->addHelpButton('parent', 'parentcategory', 'question');
        $mform->addElement('button', null, get_string('selectacategory', 'question'), array('id' => 'openModalBtn1'));
        
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("myModal1");
            var btn = document.getElementById("openModalBtn1");
            var span = document.getElementsByClassName("close")[0];
        
            
            if (btn) {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();
                    modal.style.display = "block";
                });
            }
        
            
            if (span) {
                span.addEventListener("click", function() {
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
                    var select = document.getElementById("id_parent");
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
                var select = document.getElementById("id_parent");
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
        $mform->addElement('text', 'name', get_string('name'), 'maxlength="254" size="50"');
        $mform->setDefault('name', '');
        $mform->addRule('name', get_string('categorynamecantbeblank', 'question'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('editor', 'info', get_string('categoryinfo', 'question'),
                ['rows' => 10], ['noclean' => 1]);
        $mform->setDefault('info', '');
        $mform->setType('info', PARAM_RAW);

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        $this->add_action_buttons(true, get_string('addcategory', 'question'));

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * Set data method.
     *
     * Add additional information to current data.
     * @param \stdClass|array $current Object or array of default current data.
     */
    public function set_data($current) {
        if (is_object($current)) {
            $current = (array) $current;
        }
        if (!empty($current['info'])) {
            $current['info'] = ['text' => $current['info'], 'infoformat' => $current['infoformat']];
        } else {
            $current['info'] = ['text' => '', 'infoformat' => FORMAT_HTML];
        }
        parent::set_data($current);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     * @throws \dml_exception|\coding_exception
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate idnumber.
        list($parentid, $contextid) = explode(',', $data['parent']);
        if (((string) $data['idnumber'] !== '') && !empty($contextid)) {
            $conditions = 'contextid = ? AND idnumber = ?';
            $params = [$contextid, $data['idnumber']];
            if (!empty($data['id'])) {
                $conditions .= ' AND id <> ?';
                $params[] = $data['id'];
            }
            if ($DB->record_exists_select('question_categories', $conditions, $params)) {
                $errors['idnumber'] = get_string('idnumbertaken', 'error');
            }
        }

        return $errors;
    }
}
