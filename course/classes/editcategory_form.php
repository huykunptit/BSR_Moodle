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
 * Edit category form.
 *
 * @package core_course
 * @copyright 2002 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
echo '<!DOCTYPE HTML>';
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * Edit category form.
 *
 * @package core_course
 * @copyright 2002 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_course_editcategory_form extends moodleform {

    /**
     * The form definition.
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        $categoryid = $this->_customdata['categoryid'];
        $parent = $this->_customdata['parent'];
        
        // Get list of categories to use as parents, with site as the first one.
        $options = array();
    
        if (has_capability('moodle/category:manage', context_system::instance()) || $parent == 0) {
            
            $options[0] = get_string('top');
            
        }
        
        if ($categoryid) {
           
            // Editing an existing category.
            $options += core_course_category::make_categories_list('moodle/category:manage', $categoryid);
            
         
            $displaylist = core_course_category::make_categories_list('moodle/course:create');
         
            if (empty($options[$parent])) {
                // Ensure the the category parent has been included in the options.
                $options[$parent] = $DB->get_field('course_categories', 'name', array('id'=>$parent));
               
            }
            $name = get_string('selectacategory', 'question');
                function buildTree($options) {
                    $tree = [];
                
                    foreach ($options as $key => $value) {
                        $parts = preg_split('/ \/ /', $value);
                
                        $name = $parts[0]; // First element is the name
                        $id = $key; // ID is used as the key in the options array
                
                        $ref = &$tree;
                
                        foreach ($parts as $part) {
                            if (!isset($ref[$part])) {
                                // Use associative array to store both name and ID
                                $ref[$part] = ['name' => $name, 'id' => $id];
                            }
                            $ref = &$ref[$part];
                        }
                    }
                
                    return $tree;
                }
                
                function buildHtmlList($tree, $prefix = '') {
                    $html = '<div class="hello">';
                    
                    foreach ($tree as $key => $value) {
                        if (is_array($value)) {
                            $name = $value['name']; // Get name from value array
                            $id = $value['id']; // Get id from value array
                            
                            $html .= '<details>';
                            $html .= '<summary>';
                            $html .= '<span class="folder-icon opened closed"></span>';
                            $html .= '<a href="#" class="modal-link" data-value="' . $id . '" for="' . $prefix . $key . '">' . $key . '</a>';
                            $html .= '</summary>';
                
                            if (!empty($value)) {
                                $html .= '<ul>';
                                $html .= buildHtmlList($value, $prefix . $key . '_');
                                $html .= '</ul>';
                            }
                
                            $html .= '</details>';
                        }
                    }
                    $html .= '</div>';
                    return $html;
                }
                asort($options);
                $chooseCategory = get_string('selectcategory', 'quiz');
                    $tree = buildTree($options);
                    $finalTree = buildHtmlList($tree);
                    $name = get_string('selectacategory', 'question');
                
                echo '
                <div id="treeModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                    <h2 style="text-align: center;">'.$name.'</h2>
                    <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="Tìm kiếm...">
                    <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
                    </div>
                
                    <div class="line-tree" style="padding:5px;">
                        '.$finalTree.'
                    </div>
                </div>
                </div>';
                
                echo '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var showTreeButton = document.getElementById("showTreeButton");
                    var treeModal = document.getElementById("treeModal");
                    var closeBtn = treeModal.querySelector(".close");
        
                    showTreeButton.addEventListener("click", function() {
                        treeModal.style.display = "block";
                    });
        
                    closeBtn.addEventListener("click", function() {
                        treeModal.style.display = "none";
                    });
        
                    window.addEventListener("click", function(event) {
                        if (event.target == treeModal) {
                            treeModal.style.display = "none";
                        }
                    });
        
                    var searchInput = document.getElementById("searchInput");
                    var allItems = document.querySelectorAll(".line-tree details");
        
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
                    document.querySelectorAll(".modal-link").forEach(function(link) {
                        link.addEventListener("click", function(event) {
                            event.preventDefault();
                            var value = this.getAttribute("data-value");
                            var treeContainer = document.getElementById("treeContainer");
                            var suggestionItem = document.querySelector(".form-autocomplete-suggestions li[role=\'option\'][data-value=\'" + value + "\']");
                            if (suggestionItem) {
                                suggestionItem.click();
                            }
                
                            if (treeContainer) {
                                treeContainer.style.display = "none";
                            }
                        });
                    });
                });
                </script>
                ';
        
                echo '<style>
                /* Modal styles */
                .modal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
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
                }
                #searchInput {
                    width: 70%;
                    border: 1px solid #ccc;
                    padding: 5px;
                }
                /* Tree styles */
                .line-tree {
                    padding: 5px;
                    width: 100%;
                    box-sizing: border-box;
                }

                .line-tree {
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

                 ul  .hello > details {
                    outline: none;
                    padding: 0;
                 }

              
                    ul  .hello > details:before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 16px;
                        width: 13px;
                        height: 1em;
                        border-bottom: 2px dotted #055091;
                    }

                    ul  .hello > details:after {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 16px;
                        width: 13px;
                        height: 100%;
                        border-left: 2px dotted #055091;
                        }


                .line-tree details details {
                    border: none;
                    padding-left: 2em;
                    margin-bottom: 0;
                    margin-right:-10px;
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
              
            $strsubmit = get_string('savechanges');
         
            
       
       
       
        } else {
            // Making a new category.
            $options += core_course_category::make_categories_list('moodle/category:manage', $categoryid);
            
         
            $displaylist = core_course_category::make_categories_list('moodle/course:create');
         
            if (empty($options[$parent])) {
                // Ensure the the category parent has been included in the options.
                $options[$parent] = $DB->get_field('course_categories', 'name', array('id'=>$parent));
               
            }
            $name = get_string('selectacategory', 'question');
                function buildTree($options) {
                    $tree = [];
                
                    foreach ($options as $key => $value) {
                        $parts = preg_split('/ \/ /', $value);
                
                        $name = $parts[0]; // First element is the name
                        $id = $key; // ID is used as the key in the options array
                
                        $ref = &$tree;
                
                        foreach ($parts as $part) {
                            if (!isset($ref[$part])) {
                                // Use associative array to store both name and ID
                                $ref[$part] = ['name' => $name, 'id' => $id];
                            }
                            $ref = &$ref[$part];
                        }
                    }
                
                    return $tree;
                }
                
                function buildHtmlList($tree, $prefix = '') {
                    $html = '<div class="hello">';
                    
                    foreach ($tree as $key => $value) {
                        if (is_array($value)) {
                            $name = $value['name']; // Get name from value array
                            $id = $value['id']; // Get id from value array
                            
                            $html .= '<details>';
                            $html .= '<summary>';
                            $html .= '<span class="folder-icon opened closed"></span>';
                            $html .= '<a href="#" class="modal-link" data-value="' . $id . '" for="' . $prefix . $key . '">' . $key . '</a>';
                            $html .= '</summary>';
                
                            if (!empty($value)) {
                                $html .= '<ul>';
                                $html .= buildHtmlList($value, $prefix . $key . '_');
                                $html .= '</ul>';
                            }
                
                            $html .= '</details>';
                        }
                    }
                    $html .= '</div>';
                    return $html;
                }
                asort($options);
                $chooseCategory = get_string('selectcategory', 'quiz');
                    $tree = buildTree($options);
                    $finalTree = buildHtmlList($tree);
                    $name = get_string('selectacategory', 'question');
                
                echo '
                <div id="treeModal" class="modal">
                <div class="modal-content">
                <div class="modal-header">
                    <h2 style="text-align: center;">'.$name.'</h2>
                    <input type="text" id="searchInput" style="margin-right:4rem;width:63%;margin-top:-10px;" placeholder="Tìm kiếm...">
                    <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
                </div>
                    <div class="line-tree" style="padding:5px;">
                        '.$finalTree.'
                    </div>
                </div>
                </div>';
                
                echo '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var showTreeButton = document.getElementById("showTreeButton");
                    var treeModal = document.getElementById("treeModal");
                    var closeBtn = treeModal.querySelector(".close");
        
                    showTreeButton.addEventListener("click", function() {
                        treeModal.style.display = "block";
                    });
        
                    closeBtn.addEventListener("click", function() {
                        treeModal.style.display = "none";
                    });
        
                    window.addEventListener("click", function(event) {
                        if (event.target == treeModal) {
                            treeModal.style.display = "none";
                        }
                    });
        
                    var searchInput = document.getElementById("searchInput");
                    var allItems = document.querySelectorAll(".line-tree details");
        
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
                });
                </script>
                ';
        
                echo '<style>
                /* Modal styles */
                .modal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
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
                }
                #searchInput {
                    width: 70%;
                    border: 1px solid #ccc;
                    padding: 5px;
                }
                /* Tree styles */
                .line-tree {
                    padding: 5px;
                    width: 100%;
                    box-sizing: border-box;
                }

                .line-tree {
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

                 ul  .hello > details {
                    outline: none;
                    padding: 0;
                 }

             

                    ul  .hello > details:before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 16px;
                        width: 13px;
                        height: 1em;
                        border-bottom: 2px dotted #055091;
                    }

                    ul  .hello > details:after {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 16px;
                        width: 13px;
                        height: 100%;
                        border-left: 2px dotted #055091;
                        }


                .line-tree details details {
                    border: none;
                    padding-left: 2em;
                    margin-bottom: 0;
                    margin-right:-10px;
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
                
                </style>';
           
            $strsubmit = get_string('createcategory');
        
        
        
        
        }
       
       
        $mform->addElement('autocomplete', 'parent', get_string('parentcategory'), $options);
        $mform->addRule('parent', null, 'required', null, 'client');
        $mform->addElement('button', null, get_string('selectacategory', 'question'), array('id' => 'showTreeButton'));
            
       
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var showTreeButton = document.getElementById("showTreeButton");
            var treeContainer = document.getElementById("treeContainer");
            var closeModalButton = document.querySelector(".close-modal");
            
            if (showTreeButton && treeContainer) {
                showTreeButton.addEventListener("click", function() {
                    if (treeContainer.style.display === "none" || treeContainer.style.display === "") {
                        treeContainer.style.display = "flex";
                    } else {
                        treeContainer.style.display = "none";
                    }
                });
            }
        
            // Hide the modal when clicking outside of it
            document.addEventListener("click", function(event) {
                var treeContainer = document.getElementById("treeModal");
                if (treeContainer && !treeContainer.contains(event.target) && !showTreeButton.contains(event.target)) {
                    treeContainer.style.display = "none";
                }
            });
        
            
            if (closeModalButton) {
                closeModalButton.addEventListener("click", function() {
                    treeContainer.style.display = "none";
                });
            }
            
           
        });

   
        var searchInput = document.getElementById("searchInput");
        var allItems = document.querySelectorAll(".line-tree details");

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
     
    
        document.querySelectorAll(".modal-link").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                var value = this.getAttribute("data-value");
                var treeContainer = document.getElementById("treeContainer");
                var suggestionItem = document.querySelector(".form-autocomplete-suggestions li[role=\'option\'][data-value=\'" + value + "\']");
                if (suggestionItem) {
                    suggestionItem.click();
                }
    
                if (treeContainer) {
                    treeContainer.style.display = "none";
                }
            });
        });
        </script>';
        
        $mform->addElement('text', 'name', get_string('categoryname'), array('size' => '30'));
        $mform->addRule('name', get_string('required'), 'required', null);
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'idnumber', get_string('idnumbercoursecategory'), 'maxlength="100" size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercoursecategory');
        $mform->setType('idnumber', PARAM_RAW);
        // add checkbox to modify isquestionbank
        // isquestionbank in the DB == 1 -> checked
        $mform->addElement('checkbox', 'isquestionbank', "Là ngân hàng câu hỏi");
        $mform->setDefault('isquestionbank', 0);
        $mform->setType('isquestionbank', PARAM_INT);

        $mform->addElement('editor', 'description_editor', get_string('description'), null,
            $this->get_description_editor_options());
        $mform->setType('description_editor', PARAM_RAW);

        if (!empty($CFG->allowcategorythemes)) {
            $themes = array(''=>get_string('forceno'));
            $allthemes = get_list_of_themes();
            foreach ($allthemes as $key => $theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
        }

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $categoryid);

        $this->add_action_buttons(true, $strsubmit);
    }

    /**
     * Returns the description editor options.
     * @return array
     */
    public function get_description_editor_options() {
        global $CFG;
        $context = $this->_customdata['context'];
        $itemid = $this->_customdata['itemid'];
        return array(
            'maxfiles'  => EDITOR_UNLIMITED_FILES,
            'maxbytes'  => $CFG->maxbytes,
            'trusttext' => false,
            'noclean'   => true,
            'context'   => $context,
            'subdirs'   => file_area_contains_subdirs($context, 'coursecat', 'description', $itemid),
        );
    }

    /**
     * Validates the data submit for this form.
     *
     * @param array $data An array of key,value data pairs.
     * @param array $files Any files that may have been submit as well.
     * @return array An array of errors.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['idnumber'])) {
            if ($existing = $DB->get_record('course_categories', array('idnumber' => $data['idnumber']))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['idnumber'] = get_string('categoryidnumbertaken', 'error');
                }
            }
        }
        return $errors;
    }
}
