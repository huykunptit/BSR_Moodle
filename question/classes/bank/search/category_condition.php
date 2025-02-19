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
 * A search class to control from which category questions are listed.
 *
 * @package   core_question
 * @copyright 2013 Ray Morris
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// include_once($CFG->dirroot . 'core_question\bank\search\core_course_category');
namespace core_question\bank\search;

use core_course_category;
use qbank_managecategories\helper;
/**
 *  This class controls from which category questions are listed.
 *
 * @copyright 2013 Ray Morris
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_condition extends condition {
    /** @var \stdClass The course record. */
    protected $course;

    /** @var \stdClass The category record. */
    protected $category;

    /** @var array of contexts. */
    protected $contexts;

    /** @var bool Whether to include questions from sub-categories. */
    protected $recurse;

    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /** @var array query param used in where. */
    protected $params;

    /** @var string categoryID,contextID as used with question_bank_view->display(). */
    protected $cat;

    /** @var int The maximum displayed length of the category info. */
    protected $maxinfolength;

    /** @var \moodle_url The URL the form is submitted to. */
    protected $baseurl;

    /**
     * Constructor
     * @param string     $cat           categoryID,contextID as used with question_bank_view->display()
     * @param bool       $recurse       Whether to include questions from sub-categories
     * @param array      $contexts      Context objects as used by question_category_options()
     * @param \moodle_url $baseurl       The URL the form is submitted to
     * @param \stdClass   $course        Course record
     * @param integer    $maxinfolength The maximum displayed length of the category info.
     */
    public function __construct($cat, $recurse, $contexts, $baseurl, $course, $maxinfolength = null) {
        $this->cat = $cat;
        $this->recurse = $recurse;
        $this->contexts = $contexts;
        $this->baseurl = $baseurl;

        $this->course = $course;
        $this->init();
        $this->maxinfolength = $maxinfolength;
    }

    /**
     * Initialize the object so it will be ready to return where() and params()
     */
    private function init() {
        global $DB;
        if (!$this->category = $this->get_current_category($this->cat)) {
            return;
        }
        if ($this->recurse) {
            $categoryids = question_categorylist($this->category->id);
        } else {
            $categoryids = [$this->category->id];
        }
        list($catidtest, $this->params) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'cat');
        $this->where = 'qbe.questioncategoryid ' . $catidtest;
        // dd($this->recurse);
    }

    /**
     * SQL fragment to add to the where clause.
     *
     * @return string
     */
    public function where() {
        return  $this->where;
    }

    /**
     * Return parameters to be bound to the above WHERE clause fragment.
     * @return array parameter name => value.
     */
    public function params() {
        return $this->params;
    }

    /**
     * Called by question_bank_view to display the GUI for selecting a category
     */

     
    public function display_options() {
        global $PAGE, $identifier,$DB;
        $displaydata = [];
        $all_question_bank_categories = $DB->get_records('course_categories', ['isquestionbank' => 1]);
        $all_question_bank_categories_ids = array_keys($all_question_bank_categories);
        $all_contexts = [];
        foreach($all_question_bank_categories_ids as $category_id) {
            $cat = core_course_category::get($category_id);
            if(!$cat->can_create_course()) {
                continue;
            }
            $all_contexts[] = \core\context\coursecat::instance($category_id);
            
        }
        dd($all_contexts);
        $this->contexts = array_merge($this->contexts, $all_contexts);
        // $a = $this->course->id;
        // dd($a);
                $catmenu = helper::question_category_options($this->contexts, true, 0,
                true, -1, false);
                
                foreach ($catmenu as $key => $category) {
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
                    $html .= '<li><details class = "last"><summary class="sub sub1"><a href="' . $url . '" data-value="' . $key . '" class="modal-link">' . $file_clean . '</a></summary><ul>';
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

            // Assuming $catmenu is properly defined elsewhere
            $displaydata['categoryselect'] = \html_writer::select($catmenu, 'category', $this->cat, [],
                array('class' => 'searchoptions custom-select', 'id' => 'id_selectacategory'));

            // Sorting the $catmenu
            usort($catmenu, function ($a, $b) {
                $keyA = key($a);
                $keyB = key($b);
                return strcoll($keyA, $keyB);
            });

            
            $name = get_string('selectacategory', 'question');
            echo '<button id="openModalBtn1" style="color:white;padding:10px;background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">'.$name.'</button>
            <div id="myModal1" class="modal">
            <div class="modal-content">
          <div class="modal-header">
                <h2 style="text-align: center;">'.$name.'</h2>
                <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="Tìm kiếm...">
                <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
            </div>';
            echo '     
            <div style="padding: 0 1rem; overflow: auto;">
            <ul>
                ' . buildCategoryTree($catmenu) . ' </ul>
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
            margin: 10% auto; 
            padding: 20px;
            border: 1px solid #888;
            max-width: 80%; 
            overflow: hidden; 
            max-height: 70%;
            }

            .close {
            color: #f8f6f6 !important;
                
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
            </style>';

        echo '
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var modal = document.getElementById("myModal1");
                    var btn = document.getElementById("openModalBtn1");
                    var span = document.getElementsByClassName("close")[0];
                    var select = document.getElementById("id_selectacategory");
                    var links = document.querySelectorAll("a.modal-link");
                    var searchInput = document.getElementById("searchtext");
                        btn.onclick = function(event) {
                            event.preventDefault(); 
                            modal.style.display = "block";
                        }
                        if (searchInput) {
                            searchInput.addEventListener("keydown", function(event) {
                                
                                if (event.key === "Enter") {
                                    console.log("Enter key pressed on searchInput");
                                    event.preventDefault();
                                    if (modal) {
                                        modal.style.display = "none";
                                        
                                   
                                }
                            }
                            });
                        } else {
                            console.error("SearchInput not found");
                        }
                    
                        span.onclick = function() {
                            modal.style.display = "none";
                        }
                    
                        window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }
                    
                     
                    
                        links.forEach(function(link) {
                            link.addEventListener("click", function(e) {
                                e.preventDefault();
                                window.location.href = this.href;
                            });
                        });
                    
                        const currentUrl = new URL(window.location.href);
                        const categoryValue = currentUrl.searchParams.get("category");
                        if (categoryValue) {
                            const decodedValue = decodeURIComponent(categoryValue);
                            const [catId, pathId] = decodedValue.split("%2C");
                            const optionValue = catId + "," + pathId;
                           
                            const option = Array.from(select.options).find(opt => opt.value === optionValue);
                            if (option) {
                                option.selected = true;
                            }
                        }
                    });

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
                    </script>';

           
        if ($this->category) {

            $displaydata['categorydesc'] = $this->print_category_info($this->category);
        }
        return $PAGE->get_renderer('core_question', 'bank')->render_category_condition($displaydata);
    }


    /**
     * Displays the recursion checkbox GUI.
     * question_bank_view places this within the section that is hidden by default
     */
    public function display_options_adv() {
        global $PAGE;
        $displaydata = [];
        if ($this->recurse) {
            $displaydata['checked'] = 'checked';
        }
        return $PAGE->get_renderer('core_question', 'bank')->render_category_condition_advanced($displaydata);
    }

    /**
     * Display the drop down to select the category.
     *
     * @param array $contexts of contexts that can be accessed from here.
     * @param \moodle_url $pageurl the URL of this page.
     * @param string $current 'categoryID,contextID'.
     * @deprecated since Moodle 4.0
     */
    protected function display_category_form($contexts, $pageurl, $current) {
        debugging('Function display_category_form() is deprecated,
         please use the core_question renderer instead.', DEBUG_DEVELOPER);
        echo \html_writer::start_div('choosecategory');
        $catmenu = question_category_options($contexts, true, 0, true, -1, false);
        echo \html_writer::label(get_string('selectacategory', 'question'), 'id_selectacategory', true, ["class" => "mr-1"]);
        echo \html_writer::select($catmenu, 'category', $current, [],
                array('class' => 'searchoptions custom-select', 'id' => 'id_selectacategory'));
        echo \html_writer::end_div() . "\n";
    }

    /**
     * Look up the category record based on cateogry ID and context
     * @param string $categoryandcontext categoryID,contextID as used with question_bank_view->display()
     * @return \stdClass The category record
     */
    protected function get_current_category($categoryandcontext) {
        global $DB, $OUTPUT;
        list($categoryid, $contextid) = explode(',', $categoryandcontext);
        if (!$categoryid) {
            $this->print_choose_category_message($categoryandcontext);
            return false;
        }

        if (!$category = $DB->get_record('question_categories', ['id' => $categoryid, 'contextid' => $contextid])) {
            echo $OUTPUT->box_start('generalbox questionbank');
            echo $OUTPUT->notification('Category not found!');
            echo $OUTPUT->box_end();
            return false;
        }

        return $category;
    }

    /**
     * Print the category description
     * @param \stdClass $category the category information form the database.
     */
    protected function print_category_info($category): string {
        $formatoptions = new \stdClass();
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        if (isset($this->maxinfolength)) {
            return shorten_text(format_text($category->info, $category->infoformat, $formatoptions, $this->course->id),
                    $this->maxinfolength);
        } else {
            return format_text($category->info, $category->infoformat, $formatoptions, $this->course->id);
        }
    }


}
