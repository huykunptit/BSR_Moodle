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
 * Defines the custom question bank view used on the Edit quiz page.
 *
 * @package   mod_quiz
 * @category  question
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quiz\question\bank;

use core_course_category;
use core_question\local\bank\question_version_status;
use mod_quiz\question\bank\filter\custom_category_condition;
use qbank_managecategories\helper;
use core_tag_tag;
use moodleform;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Subclass to customise the view of the question bank for the quiz editing screen.
 *
 * @copyright  2009 Tim Hunt
 * @author     2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_view extends \core_question\local\bank\view {
    /** @var int number of questions per page to show in the add from question bank modal. */
    const DEFAULT_PAGE_SIZE = 20;

    /** @var bool $quizhasattempts whether the quiz this is used by has been attemptd. */
    protected $quizhasattempts = false;

    /** @var \stdClass $quiz the quiz settings. */
    protected $quiz = false;

    /** @var int The maximum displayed length of the category info. */
    const MAX_TEXT_LENGTH = 200;

    /**
     * Constructor.
     * @param \core_question\local\bank\question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param \stdClass $course course settings
     * @param \stdClass $cm activity settings.
     * @param \stdClass $quiz quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $cm, $quiz) {
        parent::__construct($contexts, $pageurl, $course, $cm);
        $this->quiz = $quiz;
        $this->pagesize = self::DEFAULT_PAGE_SIZE;
    }

    protected function get_question_bank_plugins(): array {
        $questionbankclasscolumns = [];
        $corequestionbankcolumns = [
            'add_action_column',
            'checkbox_column',
            'question_type_column',
            'question_name_text_column',
            'preview_action_column'
        ];

        if (question_get_display_preference('qbshowtext', 0, PARAM_INT, new \moodle_url(''))) {
            $corequestionbankcolumns[] = 'question_text_row';
        }

        foreach ($corequestionbankcolumns as $fullname) {
            $shortname = $fullname;
            if (class_exists('mod_quiz\\question\\bank\\' . $fullname)) {
                $fullname = 'mod_quiz\\question\\bank\\' . $fullname;
                $questionbankclasscolumns[$shortname] = new $fullname($this);
            } else if (class_exists('core_question\\local\\bank\\' . $fullname)) {
                $fullname = 'core_question\\local\\bank\\' . $fullname;
                $questionbankclasscolumns[$shortname] = new $fullname($this);
            } else {
                $questionbankclasscolumns[$shortname] = '';
            }
        }
        $plugins = \core_component::get_plugin_list_with_class('qbank', 'plugin_feature', 'plugin_feature.php');
        foreach ($plugins as $componentname => $plugin) {
            $pluginentrypointobject = new $plugin();
            $plugincolumnobjects = $pluginentrypointobject->get_question_columns($this);
            // Don't need the plugins without column objects.
            if (empty($plugincolumnobjects)) {
                unset($plugins[$componentname]);
                continue;
            }
            foreach ($plugincolumnobjects as $columnobject) {
                $columnname = $columnobject->get_column_name();
                foreach ($corequestionbankcolumns as $key => $corequestionbankcolumn) {
                    if (!\core\plugininfo\qbank::is_plugin_enabled($componentname)) {
                        unset($questionbankclasscolumns[$columnname]);
                        continue;
                    }
                    // Check if it has custom preference selector to view/hide.
                    if ($columnobject->has_preference() && !$columnobject->get_preference()) {
                        continue;
                    }
                    if ($corequestionbankcolumn === $columnname) {
                        $questionbankclasscolumns[$columnname] = $columnobject;
                    }
                }
            }
        }

        // Mitigate the error in case of any regression.
        foreach ($questionbankclasscolumns as $shortname => $questionbankclasscolumn) {
            if (empty($questionbankclasscolumn)) {
                unset($questionbankclasscolumns[$shortname]);
            }
        }

        return $questionbankclasscolumns;
    }

    protected function heading_column(): string {
        return 'mod_quiz\\question\\bank\\question_name_text_column';
    }

    protected function default_sort(): array {
        // Using the extended class for quiz specific sort.
        return [
            'qbank_viewquestiontype\\question_type_column' => 1,
            'mod_quiz\\question\\bank\\question_name_text_column' => 1,
        ];
    }

    /**
     * Let the question bank display know whether the quiz has been attempted,
     * hence whether some bits of UI, like the add this question to the quiz icon,
     * should be displayed.
     *
     * @param bool $quizhasattempts whether the quiz has attempts.
     */
    public function set_quiz_has_attempts($quizhasattempts): void {
        // dd($quizhasattempts);
        $this->quizhasattempts = $quizhasattempts;
        if ($quizhasattempts && isset($this->visiblecolumns['addtoquizaction'])) {
            unset($this->visiblecolumns['addtoquizaction']);
        }
    }

    /**
     * Question preview url.
     *
     * @param \stdClass $question
     * @return \moodle_url
     */
    public function preview_question_url($question) {
        return quiz_question_preview_url($this->quiz, $question);
    }

    /**
     * URL of add to quiz.
     *
     * @param $questionid
     * @return \moodle_url
     */
    public function add_to_quiz_url($questionid) {
        $params = $this->baseurl->params();
        $params['addquestion'] = $questionid;
        $params['sesskey'] = sesskey();
        return new \moodle_url('/mod/quiz/edit.php', $params);
    }

    /**
     * Renders the html question bank (same as display, but returns the result).
     *
     * Note that you can only output this rendered result once per page, as
     * it contains IDs which must be unique.
     *
     * @param array $pagevars
     * @param string $tabname
     * @return string HTML code for the form
     */
    public function render($pagevars, $tabname): string {
        ob_start();
        $this->display($pagevars, $tabname);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    protected function display_bottom_controls(\context $catcontext): void {
        $cmoptions = new \stdClass();
        $cmoptions->hasattempts = !empty($this->quizhasattempts);

        $canuseall = has_capability('moodle/question:useall', $catcontext);

        echo \html_writer::start_tag('div', ['class' => 'pt-2']);
        if ($canuseall) {
            // Add selected questions to the quiz.
            $params = [
                'type' => 'submit',
                'name' => 'add',
                'class' => 'btn btn-primary',
                'value' => get_string('addselectedquestionstoquiz', 'quiz'),
                'data-action' => 'toggle',
                'data-togglegroup' => 'qbank',
                'data-toggle' => 'action',
                'disabled' => true,
            ];
            echo \html_writer::empty_tag('input', $params);
        }
        echo \html_writer::end_tag('div');
    }

    protected function create_new_question_form($category, $canadd): void {
        // Don't display this.
    }

    /**
     * Override the base implementation in \core_question\local\bank\view
     * because we don't want to print the headers in the fragment
     * for the modal.
     */
    protected function display_question_bank_header(): void {
    }

    /**
     * Override the base implementation in \core_question\bank\view
     * because we don't want it to read from the $_POST global variables
     * for the sort parameters since they are not present in a fragment.
     *
     * Unfortunately the best we can do is to look at the URL for
     * those parameters (only marginally better really).
     */
    protected function init_sort_from_params(): void {
        $this->sort = [];
        for ($i = 1; $i <= self::MAX_SORTS; $i++) {
            if (!$sort = $this->baseurl->param('qbs' . $i)) {
                break;
            }
            // Work out the appropriate order.
            $order = 1;
            if ($sort[0] == '-') {
                $order = -1;
                $sort = substr($sort, 1);
                if (!$sort) {
                    break;
                }
            }
            // Deal with subsorts.
            list($colname) = $this->parse_subsort($sort);
            $this->get_column_type($colname);
            $this->sort[$sort] = $order;
        }
    }

    protected function build_query(): void {
        // Get the required tables and fields.
        $joins = [];
        $fields = ['qv.status', 'qc.id as categoryid', 'qv.version', 'qv.id as versionid', 'qbe.id as questionbankentryid'];
        if (!empty($this->requiredcolumns)) {
            foreach ($this->requiredcolumns as $column) {
                $extrajoins = $column->get_extra_joins();
                foreach ($extrajoins as $prefix => $join) {
                    if (isset($joins[$prefix]) && $joins[$prefix] != $join) {
                        throw new \coding_exception('Join ' . $join . ' conflicts with previous join ' . $joins[$prefix]);
                    }
                    $joins[$prefix] = $join;
                }
                $fields = array_merge($fields, $column->get_required_fields());
            }
        }
        $fields = array_unique($fields);
        
        // Build the order by clause.
        $sorts = [];
        foreach ($this->sort as $sort => $order) {
            list($colname, $subsort) = $this->parse_subsort($sort);
            $sorts[] = $this->requiredcolumns[$colname]->sort_expression($order < 0, $subsort);
        }

        // Build the where clause.
        $latestversion = 'qv.version = (SELECT MAX(v.version)
                                          FROM {question_versions} v
                                          JOIN {question_bank_entries} be
                                            ON be.id = v.questionbankentryid
                                         WHERE be.id = qbe.id)';
        $readyonly = "qv.status = '" . question_version_status::QUESTION_STATUS_READY . "' ";
        $tests = ['q.parent = 0', $latestversion, $readyonly];
        $this->sqlparams = [];
        foreach ($this->searchconditions as $searchcondition) {
            if ($searchcondition->where()) {
                $tests[] = '((' . $searchcondition->where() .'))';
            }
            if ($searchcondition->params()) {
                $this->sqlparams = array_merge($this->sqlparams, $searchcondition->params());
            }
        }
        // Build the SQL.
        $sql = ' FROM {question} q ' . implode(' ', $joins);
        $sql .= ' WHERE ' . implode(' AND ', $tests);
        $this->countsql = 'SELECT count(1)' . $sql;
        $this->loadsql = 'SELECT ' . implode(', ', $fields) . $sql . ' ORDER BY ' . implode(', ', $sorts);
    }

    public function wanted_filters($cat, $tagids, $showhidden, $recurse, $editcontexts, $showquestiontext): void {
        global $CFG,$DB;
        list(, $contextid) = explode(',', $cat);
        $catcontext = \context::instance_by_id($contextid);
        $thiscontext = $this->get_most_specific_context();
        // Category selection form.
        $this->display_question_bank_header();
        // Display tag filter if usetags setting is enabled/enablefilters is true.
        if ($this->enablefilters) {
            if (is_array($this->customfilterobjects)) {
                foreach ($this->customfilterobjects as $filterobjects) {
                    $this->searchconditions[] = $filterobjects;
                }
            } else {
                
                if ($CFG->usetags) {
                    array_unshift($this->searchconditions,
                        new \core_question\bank\search\tag_condition([$catcontext, $thiscontext], $tagids));
                       
                } 
                $editcontexts = array_slice($editcontexts, 1);
                foreach($editcontexts as $key => $context) {
                    if ($context->contextlevel == CONTEXT_COURSE) {
                        // remove this context from the list of contexts
                        unset($editcontexts[$key]);
                    }
                } 
                
          
                array_unshift($this->searchconditions, new \core_question\bank\search\hidden_condition(!$showhidden));
                array_unshift($this->searchconditions, new custom_category_condition(
                    $cat, $recurse, $editcontexts, $this->baseurl, $this->course));
                    $all_question_bank_categories = $DB->get_records('course_categories', ['isquestionbank' => 1]);
                    // dump($all_question_bank_categories);
                    $all_question_bank_categories_ids = array_keys($all_question_bank_categories);
                    $all_contexts = [];
                    $editcontexts = array_slice($editcontexts, 1);
                    
                    foreach($all_question_bank_categories_ids as $category_id) {
                        $cat = core_course_category::get($category_id);
                        if(!$cat->can_create_course()) {
                            continue;
                        }
                        $all_contexts[] = \core\context\coursecat::instance($category_id);
                    }
                    // $editcontexts = array_merge($editcontexts, $all_contexts);
                   
                    $catmenu = helper::question_category_options($editcontexts, true, 0,
                    true, -1, false);
                      
                
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
                  
        
              $name = get_string('selectacategory', 'question');
    
              $customHtml = '
                <button id="openModalBtn1" style="color:white;padding:10px;width: 11%; background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">' . get_string('selectacategory', 'question') . '</button>
                <div id="myModal1" class="modal">
                    <div class="modal-content" style="width: 50% !important">
                        <div class="d-flex">
                            <h2 style="text-align: center;">' . get_string('selectacategory', 'question') . '</h2>
                            <input type="text" id="searchInput" style="width:100%;margin-bottom:10px;" placeholder="Tìm kiếm...">
                            <span class="close close-modal d-flex align-items-center" style="place-content:center">&times;</span>
                        </div>
                        <div class="mt-3" style="padding: 0 1rem;overflow: auto">';

                // Start the tree structure
                $customHtml .= '<ul>';
                
                $stack = [];
                $currentDepth = 0;
                $lastDepth = 0;
                   
                    usort($catmenu, function($a, $b) {
                        return key($a) <=> key($b);
                    });
                    foreach ($catmenu as $item) {
                        foreach ($item as $folder => $files) {
                            $files = array_slice($files,1);
                            
                            $folderLabel = htmlspecialchars($folder);
                            $customHtml .= '<li class="modal-li mb-3"><details><summary><span class="folder-icon opened closed"></span><a>' . $folderLabel . '</a></summary>';
                            $customHtml .= '<ul>';
                    
                            foreach ($files as $key => $file) {
                                // dump($file);
                                $indentation = substr_count($file, '&nbsp;') / 3;
                                $file_clean = htmlspecialchars(trim(preg_replace('/&nbsp;/', '', $file)));
                    
                                list($catId, $pathId) = explode(',', $key);
                                $categoryParamValue = urlencode($catId) . '%2C' . urlencode($pathId);
                    
                                // Close previous levels if needed
                                while ($indentation < $currentDepth) {
                                    $customHtml .= '</ul></details></li>';
                                    $currentDepth--;
                                    array_pop($stack);
                                }
                    
                                // Open new levels if needed
                                if ($indentation >= $currentDepth) {
                                    $customHtml .= '<li class=""><details><summary><span class="folder-icon opened closed"></span><a class="modal-link" data-value="' . $key . '">' . $file_clean . '</a></summary><ul>';
                                    $currentDepth++;
                                    $stack[] = $file_clean;
                                } else {
                                    // Check if it's a sibling or a new parent
                                    // if ($indentation == $currentDepth) {
                                    //     // Close previous sibling if exists
                                    //     if (!empty($stack)) {
                                    //         $customHtml .= '</ul></details></li>';
                                    //         array_pop($stack);
                                    //     }
                                    // }
                                    $customHtml .= '<li class=""><details><summary><span class="folder-icon opened closed"></span><a class="modal-link" data-value="' . $key . '">' . $file_clean . '</a></summary><ul>';
                                    $stack[] = $file_clean;
                                }
                            }
                    
                            // Close remaining open levels
                            while ($currentDepth != 0) {
                                $customHtml .= '</ul></details></li>';
                                $currentDepth--;
                            }
                    
                            $customHtml .= '</ul></details></li>';
                        }
                    }
                    

                $customHtml .= '</ul>'; // Close the main tree structure
                

                // Complete the modal structure
                $customHtml .= '
                        </div>
                    </div>
                </div>

                <style>
                    .modal li {
                        position: relative;
                        margin: 0;
                        padding: 0 0 0 1em;
                                                }

                    .modal li li {
                    padding: 0 0 0 2em;
                    }
                    .modal li li::after {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 16px;
                    width: 13px;
                    height: 1em;
                    border-bottom: 2px dotted #055091; }


                    .modal li li::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 16px;
                    width: 13px;
                    height: 100%;
                    border-left: 2px dotted #055091;
                    }
                                    label.mr-1 {
                    display:none;
                }

                #categoryquestions .iconcol{
                    width: 50%;
            }

            

            #categoryquestions .iconcol:first-child{
                    width: 10%;
            }

            
                select#id_selectacategory {
                    position: absolute;
                    top: 1.7rem !important;
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
                #searchInput {
                    width: 70%;
                    border: 1px solid #ccc;
                    padding: 5px;
                    margin-right: 30px;
                }
                .modal-content {
                    background-color: #fefefe;
                    margin: auto; 
                    padding: 20px;
                    border: 1px solid #888;
                    width: 100%;
                    height: 100%;
                    overflow: hidden; 
                    max-height: 70%;
                }
                .close {
                    color: #f8f6f6;
                    position: absolute;
                    right: 30px;
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
                 .modal-li {
                    margin-bottom: 10px; 
                    font-weight: bold;
                    cursor: pointer;
                    outline: 2px dotted #3744f5;
                    margin-top:5px;
                    background: #fafbff;
                    color: #110101;
                    border-radius: 0.5em;
                    padding-bottom: 10px !important;
                    margin-left: 1rem;
                }
                .modal li .modal-li ul {
                    margin-left: 2rem; 
                }
                .modal li ul a {
                    text-decoration: none;
                    color: #000;
                    margin-left: 2rem;
                }
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
                $customHtml .= '<script>
    var modal = document.getElementById("myModal1");
    var btn = document.getElementById("openModalBtn1");
    var closeButtons = document.querySelectorAll(".close, .close-modal");
    var span = document.getElementsByClassName("close")[0];
    var closeModalButton = document.getElementsByClassName("close-modal");
    btn.onclick = function(event) {
        event.preventDefault(); 
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }
    closeButtons.forEach(function(button) {
        button.onclick = function() {
            modal.style.display = "none";
        }
    });
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    
    var searchInput = document.getElementById("searchInput");
    var allItems = document.querySelectorAll(".modal-content details li");
    
    function resetTreeView() {
        allItems.forEach(function(item) {
            item.style.display = "block";
            item.open = false;
        });
    }

    searchInput.addEventListener("input", function() {
        var searchText = this.value.toLowerCase().trim();
        if (searchText === "") {
            resetTreeView();
            return;
        }

        var hasResult = false;
        allItems.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            var hasMatch = text.includes(searchText);
            
            if (hasMatch) {
                showParents(item);
                item.style.display = "block";
                item.open = true;
                hasResult = true;
                
                // Hiển thị tất cả các mục con phù hợp
                var children = item.querySelectorAll("li");
                children.forEach(function(child) {
                    if (child.textContent.toLowerCase().includes(searchText)) {
                        child.style.display = "block";
                    } else {
                        child.style.display = "none";
                    }
                });
            } else {
                item.style.display = "none";
                item.open = false;
            }
        });
        
        allItems.forEach(function(item) {
            item.style.display = "none";
            item.open = false;
        });

        allItems.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            if (text.includes(searchText)) {
                showParents(item);
                item.style.display = "block";
                item.open = true;
            }
        });

        if (!hasResult) {
            // Nếu không có kết quả, ẩn tất cả các mục
            allItems.forEach(function(item) {
                item.style.display = "none";
            });
        }
    });

    function showParents(element) {
        var parent = element.parentElement;
        while (parent) {
            if (parent.tagName === "DETAILS") {
                parent.style.display = "block";
                parent.open = true;
            }
            parent = parent.parentElement;
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".modal-link").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                var value = this.getAttribute("data-value");
                
                // Tìm và chọn tùy chọn trong phần tử <select> có giá trị tương ứng
                var select = document.getElementById("id_selectacategory");
                var options = select.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == value) {
                        select.selectedIndex = i;
                        break;
                    }
                }
    
                // Kiểm tra xem tùy chọn đã được chọn đúng chưa
                console.log("Selected value: ", select.value);
    
                // Ẩn modal
                $("#myModal1").modal("hide");
            });
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".modal-link").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                var value = this.getAttribute("data-value");
                
                // Tìm và chọn tùy chọn trong phần tử <select> có giá trị tương ứng
                var select = document.getElementById("id_selectacategory");
                var options = select.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == value) {
                        select.selectedIndex = i;
                        break;
                    }
                }
    
                // Kiểm tra xem tùy chọn đã được chọn đúng chưa
                console.log("Selected value: ", select.value);
    
                // Ẩn modal
                $("#myModal1").modal("hide");
            });
        });
    });
    document.querySelectorAll(".modal-link").forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            var value = this.getAttribute("data-value");
            var select = document.getElementById("id_selectacategory");
            $("#id_selectacategory").val(value);
            console.log(value);
            modal.style.display = "none";
            var checkbox = document.getElementById("recurse_on");
            
            checkbox.checked = checkbox.checked;  // Toggle the checkbox state
            const clickEvent = new MouseEvent(\'click\', {
                bubbles: true,
                cancelable: true,
                view: window
            });
            checkbox.checked = !checkbox.checked;  
            checkbox.dispatchEvent(clickEvent);   

        });
        function selectOption(value) {
            document.getElementById("id_selectacategory").value = value;
            console.log(value);
            modal.style.display = "none";
        }

    });



    
    
   
</script>';
            
                // Output the final HTML
                echo $customHtml;
            }
        }
        $this->display_options_form($showquestiontext);
    }

  
}
