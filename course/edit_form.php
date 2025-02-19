<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir . '/pdflib.php');


/**
 * The form for handling editing a course.
 */
class course_edit_form extends moodleform {
    protected $course;
    protected $context;

    /**
     * Form definition.
     */
    function definition() {
        global $CFG, $PAGE;

        $mform    = $this->_form;
        $PAGE->requires->js_call_amd('core_course/formatchooser', 'init');

        $course        = $this->_customdata['course']; // this contains the data of this form
        $category      = $this->_customdata['category'];
        $editoroptions = $this->_customdata['editoroptions'];
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];

        $systemcontext   = context_system::instance();
        $categorycontext = context_coursecat::instance($category->id);

        if (!empty($course->id)) {
            $coursecontext = context_course::instance($course->id);
            $context = $coursecontext;
        } else {
            $coursecontext = null;
            $context = $categorycontext;
        }

        $courseconfig = get_config('moodlecourse');

        $this->course  = $course;
        $this->context = $context;

        // Form definition with new course defaults.
        $mform->addElement('header','general', get_string('general', 'form'));

        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);
        
        $mform->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);
        if (!empty($course->id) and !has_capability('moodle/course:changefullname', $coursecontext)) {
            $mform->hardFreeze('fullname');
            $mform->setConstant('fullname', $course->fullname);
            
        }

        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);
        if (!empty($course->id) and !has_capability('moodle/course:changeshortname', $coursecontext)) {
            $mform->hardFreeze('shortname');
            
            $mform->setConstant('shortname', $course->shortname);
        }
       
        // Verify permissions to change course category or keep current.
        if (empty($course->id)) {
            if (has_capability('moodle/course:create', $categorycontext)) {
                $displaylist = core_course_category::make_categories_list('moodle/course:create');
                
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
                asort($displaylist);
                $chooseCategory = get_string('selectcategory', 'quiz');
                    $tree = buildTree($displaylist);
                    $finalTree = buildHtmlList($tree);
                    $name = get_string('selectacategory', 'question');
                
                echo '
                <div id="treeModal1" class="modal">
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
                    var treeModal = document.getElementById("treeModal1");
                    var closeBtn = treeModal.querySelector(".close");
        
                    showTreeButton.addEventListener("click", function() {
                        treeModal1.style.display = "block";
                    });
        
                    closeBtn.addEventListener("click", function() {
                        treeModal1.style.display = "none";
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
                                treeModal1.style.display = "none";
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
                </style>';
               
              
                $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $displaylist);
                $mform->addRule('category', null, 'required', null, 'client');
                $mform->addHelpButton('category', 'coursecategory');
                $mform->setDefault('category', $category->id);
                $mform->addElement('button', null, get_string('selectacategory', 'question'), array('id' => 'showTreeButton'));
                
                // echo '
                // <script>
                // document.addEventListener("DOMContentLoaded", function() {
                //     var showTreeButton = document.getElementById("showTreeButton");
                //     var treeContainer = document.getElementById("treeContainer");
                //     var closeModalButton = document.querySelector(".close-modal");
                    
                //     if (showTreeButton && treeContainer) {
                //         showTreeButton.addEventListener("click", function() {
                //             if (treeContainer.style.display === "none" || treeContainer.style.display === "") {
                //                 treeContainer.style.display = "flex";
                //             } else {
                //                 treeContainer.style.display = "none";
                //             }
                //         });
                //     }
                
                //     // Hide the modal when clicking outside of it
                //     document.addEventListener("click", function(event) {
                //         if (treeContainer && !treeContainer.contains(event.target) && !showTreeButton.contains(event.target)) {
                //             treeModal.style.display = "none";
                //         }
                //     });
                
                    
                //     if (closeModalButton) {
                //         closeModalButton.addEventListener("click", function() {
                //             treeContainer.style.display = "none";
                //         });
                //     }
                    
                   
                // });
             
                // </script>';
                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                var closeBtn = treeModal.querySelector(".close");
                    document.querySelectorAll(".modal-link").forEach(function(link) {
                        link.addEventListener("click", function(event) {
                            event.preventDefault();
                            var value = this.getAttribute("data-value");
                            
                            $(".form-autocomplete-suggestions").find("li[role=\'option\'][data-value=\'" + value + "\']").click();
                            treeModal.style.display = "block";
                        });
                    });
                });
                </script>';
                
            } else {
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                
                $mform->setConstant('category', $category->id);
            }
      
        } else {
            if (has_capability('moodle/course:changecategory', $coursecontext)) {
              
                $displaylist = core_course_category::make_categories_list('moodle/course:changecategory');
                if (!isset($displaylist[$course->category])) {
                    //always keep current
                    $displaylist[$course->category] = core_course_category::get($course->category, MUST_EXIST, true)
                        ->get_formatted_name();
                }
                
                // $options += core_course_category::make_categories_list('moodle/category:manage');
            $displaylist = core_course_category::make_categories_list('moodle/course:create');
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
                asort($displaylist);
                $chooseCategory = get_string('selectcategory', 'quiz');
                    $tree = buildTree($displaylist);
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
                                treeModal.style.display = "none";
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
                </style>';
               
                $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $displaylist);
                $mform->addRule('category', null, 'required', null, 'client');
                $mform->addHelpButton('category', 'coursecategory');
                $mform->setDefault('category', $category->id);
                $mform->addElement('button', null, get_string('selectacategory', 'question'), array('id' => 'showTreeButton'));
               
               
            } else {
                //keep current
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $course->category);
            }
        }

        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('coursevisibility'), $choices);
        $mform->addHelpButton('visible', 'coursevisibility');
        $mform->setDefault('visible', $courseconfig->visible);
        if (!empty($course->id)) {
            if (!has_capability('moodle/course:visibility', $coursecontext)) {
                $mform->hardFreeze('visible');
                $mform->setConstant('visible', $course->visible);
            }
        } else {
            if (!guess_if_creator_will_have_course_capability('moodle/course:visibility', $categorycontext)) {
                $mform->hardFreeze('visible');
                $mform->setConstant('visible', $courseconfig->visible);
            }
        }
        

        // Download course content.
        if ($CFG->downloadcoursecontentallowed) {
            $downloadchoices = [
                DOWNLOAD_COURSE_CONTENT_DISABLED => get_string('no'),
                DOWNLOAD_COURSE_CONTENT_ENABLED => get_string('yes'),
            ];
            $sitedefaultstring = $downloadchoices[$courseconfig->downloadcontentsitedefault];
            $downloadchoices[DOWNLOAD_COURSE_CONTENT_SITE_DEFAULT] = get_string('sitedefaultspecified', '', $sitedefaultstring);
            $downloadselectdefault = $courseconfig->downloadcontent ?? DOWNLOAD_COURSE_CONTENT_SITE_DEFAULT;

            $mform->addElement('select', 'downloadcontent', get_string('enabledownloadcoursecontent', 'course'), $downloadchoices);
            $mform->addHelpButton('downloadcontent', 'downloadcoursecontent', 'course');
            $mform->setDefault('downloadcontent', $downloadselectdefault);

            if ((!empty($course->id) && !has_capability('moodle/course:configuredownloadcontent', $coursecontext)) ||
                    (empty($course->id) &&
                    !guess_if_creator_will_have_course_capability('moodle/course:configuredownloadcontent', $categorycontext))) {
                $mform->hardFreeze('downloadcontent');
                $mform->setConstant('downloadcontent', $downloadselectdefault);
            }
        }

        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        $mform->addElement('date_time_selector', 'enddate', get_string('enddate'), array('optional' => true));
        $mform->addHelpButton('enddate', 'enddate');

        if (!empty($CFG->enablecourserelativedates)) {
            $attributes = [
                'aria-describedby' => 'relativedatesmode_warning'
            ];
            if (!empty($course->id)) {
                $attributes['disabled'] = true;
            }
            $relativeoptions = [
                0 => get_string('no'),
                1 => get_string('yes'),
            ];
            $relativedatesmodegroup = [];
            $relativedatesmodegroup[] = $mform->createElement('select', 'relativedatesmode', get_string('relativedatesmode'),
                $relativeoptions, $attributes);
            $relativedatesmodegroup[] = $mform->createElement('html', html_writer::span(get_string('relativedatesmode_warning'),
                '', ['id' => 'relativedatesmode_warning']));
            $mform->addGroup($relativedatesmodegroup, 'relativedatesmodegroup', get_string('relativedatesmode'), null, false);
            $mform->addHelpButton('relativedatesmodegroup', 'relativedatesmode');
        }

        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        if (!empty($course->id) and !has_capability('moodle/course:changeidnumber', $coursecontext)) {
            $mform->hardFreeze('idnumber');
            $mform->setConstants('idnumber', $course->idnumber);
        }

        // Description.
        $mform->addElement('header', 'descriptionhdr', get_string('description'));
        $mform->setExpanded('descriptionhdr');

        $mform->addElement('editor','summary_editor', get_string('coursesummary'), null, $editoroptions);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);
        $summaryfields = 'summary_editor';

        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null, $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

        if (!empty($course->id) and !has_capability('moodle/course:changesummary', $coursecontext)) {
            // Remove the description header it does not contain anything any more.
            $mform->removeElement('descriptionhdr');
            $mform->hardFreeze($summaryfields);
        }

        // Course format.
        $mform->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));

        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        if (isset($course->format)) {
            $course->format = course_get_format($course)->get_format(); // replace with default if not found
            if (!in_array($course->format, $courseformats)) {
                // this format is disabled. Still display it in the dropdown
                $formcourseformats[$course->format] = get_string('withdisablednote', 'moodle',
                        get_string('pluginname', 'format_'.$course->format));
            }
        }

        $mform->addElement('select', 'format', get_string('format'), $formcourseformats, [
            'data-formatchooser-field' => 'selector',
        ]);
        $mform->addHelpButton('format', 'format');
        $mform->setDefault('format', $courseconfig->format);

        // Button to update format-specific options on format change (will be hidden by JavaScript).
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'), [
            'data-formatchooser-field' => 'updateButton',
            'class' => 'd-none',
        ]);

        // Just a placeholder for the course format options.
        $mform->addElement('hidden', 'addcourseformatoptionshere');
        $mform->setType('addcourseformatoptionshere', PARAM_BOOL);

        // Appearance.
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if (!empty($CFG->allowcoursethemes)) {
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
        }

        if ((empty($course->id) && guess_if_creator_will_have_course_capability('moodle/course:setforcedlanguage', $categorycontext))
                || (!empty($course->id) && has_capability('moodle/course:setforcedlanguage', $coursecontext))) {

            $languages = ['' => get_string('forceno')];
            $languages += get_string_manager()->get_list_of_translations();

            $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);
            $mform->setDefault('lang', $courseconfig->lang);
        }

        // Multi-Calendar Support - see MDL-18375.
        $calendartypes = \core_calendar\type_factory::get_list_of_calendar_types();
        // We do not want to show this option unless there is more than one calendar type to display.
        if (count($calendartypes) > 1) {
            $calendars = array();
            $calendars[''] = get_string('forceno');
            $calendars += $calendartypes;
            $mform->addElement('select', 'calendartype', get_string('forcecalendartype', 'calendar'), $calendars);
        }

        $options = range(0, 10);
        $mform->addElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
        $courseconfig = get_config('moodlecourse');
        $mform->setDefault('newsitems', $courseconfig->newsitems);
        $mform->addHelpButton('newsitems', 'newsitemsnumber');

        $mform->addElement('selectyesno', 'showgrades', get_string('showgrades'));
        $mform->addHelpButton('showgrades', 'showgrades');
        $mform->setDefault('showgrades', $courseconfig->showgrades);

        $mform->addElement('selectyesno', 'showreports', get_string('showreports'));
        $mform->addHelpButton('showreports', 'showreports');
        $mform->setDefault('showreports', $courseconfig->showreports);

        // Show activity dates.
        $mform->addElement('selectyesno', 'showactivitydates', get_string('showactivitydates'));
        $mform->addHelpButton('showactivitydates', 'showactivitydates');
        $mform->setDefault('showactivitydates', $courseconfig->showactivitydates);

        // Files and uploads.
        $mform->addElement('header', 'filehdr', get_string('filesanduploads'));

        if (!empty($course->legacyfiles) or !empty($CFG->legacyfilesinnewcourses)) {
            if (empty($course->legacyfiles)) {
                //0 or missing means no legacy files ever used in this course - new course or nobody turned on legacy files yet
                $choices = array('0'=>get_string('no'), '2'=>get_string('yes'));
            } else {
                $choices = array('1'=>get_string('no'), '2'=>get_string('yes'));
            }
            $mform->addElement('select', 'legacyfiles', get_string('courselegacyfiles'), $choices);
            $mform->addHelpButton('legacyfiles', 'courselegacyfiles');
            if (!isset($courseconfig->legacyfiles)) {
                // in case this was not initialised properly due to switching of $CFG->legacyfilesinnewcourses
                $courseconfig->legacyfiles = 0;
            }
            $mform->setDefault('legacyfiles', $courseconfig->legacyfiles);
        }

        // Handle non-existing $course->maxbytes on course creation.
        $coursemaxbytes = !isset($course->maxbytes) ? null : $course->maxbytes;

        // Let's prepare the maxbytes popup.
        $choices = get_max_upload_sizes($CFG->maxbytes, 0, 0, $coursemaxbytes);
        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        $mform->addHelpButton('maxbytes', 'maximumupload');
        $mform->setDefault('maxbytes', $courseconfig->maxbytes);

        // PDF font.
        if (!empty($CFG->enablepdfexportfont)) {
            $pdf = new \pdf;
            $fontlist = $pdf->get_export_fontlist();
            // Show the option if the font is defined more than one.
            if (count($fontlist) > 1) {
                $defaultfont = $courseconfig->pdfexportfont ?? 'freesans';
                if (empty($fontlist[$defaultfont])) {
                    $defaultfont = current($fontlist);
                }
                $mform->addElement('select', 'pdfexportfont', get_string('pdfexportfont', 'course'), $fontlist);
                $mform->addHelpButton('pdfexportfont', 'pdfexportfont', 'course');
                $mform->setDefault('pdfexportfont', $defaultfont);
            }
        }

        // Completion tracking.
        if (completion_info::is_enabled_for_site()) {
            $mform->addElement('header', 'completionhdr', get_string('completion', 'completion'));
            $mform->addElement('selectyesno', 'enablecompletion', get_string('enablecompletion', 'completion'));
            $mform->setDefault('enablecompletion', $courseconfig->enablecompletion);
            $mform->addHelpButton('enablecompletion', 'enablecompletion', 'completion');

            $showcompletionconditions = $courseconfig->showcompletionconditions ?? COMPLETION_SHOW_CONDITIONS;
            $mform->addElement('selectyesno', 'showcompletionconditions', get_string('showcompletionconditions', 'completion'));
            $mform->addHelpButton('showcompletionconditions', 'showcompletionconditions', 'completion');
            $mform->setDefault('showcompletionconditions', $showcompletionconditions);
            $mform->hideIf('showcompletionconditions', 'enablecompletion', 'eq', COMPLETION_DISABLED);
        } else {
            $mform->addElement('hidden', 'enablecompletion');
            $mform->setType('enablecompletion', PARAM_INT);
            $mform->setDefault('enablecompletion', 0);
        }

        enrol_course_edit_form($mform, $course, $context);

        $mform->addElement('header','groups', get_string('groupsettingsheader', 'group'));

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $choices);
        $mform->addHelpButton('groupmode', 'groupmode', 'group');
        $mform->setDefault('groupmode', $courseconfig->groupmode);

        $mform->addElement('selectyesno', 'groupmodeforce', get_string('groupmodeforce', 'group'));
        $mform->addHelpButton('groupmodeforce', 'groupmodeforce', 'group');
        $mform->setDefault('groupmodeforce', $courseconfig->groupmodeforce);

        //default groupings selector
        $options = array();
        $options[0] = get_string('none');
        $mform->addElement('select', 'defaultgroupingid', get_string('defaultgrouping', 'group'), $options);

        if ((empty($course->id) && guess_if_creator_will_have_course_capability('moodle/course:renameroles', $categorycontext))
                || (!empty($course->id) && has_capability('moodle/course:renameroles', $coursecontext))) {
            // Customizable role names in this course.
            $mform->addElement('header', 'rolerenaming', get_string('rolerenaming'));
            $mform->addHelpButton('rolerenaming', 'rolerenaming');

            if ($roles = get_all_roles()) {
                $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL);
                $assignableroles = get_roles_for_contextlevels(CONTEXT_COURSE);
                foreach ($roles as $role) {
                    $mform->addElement('text', 'role_' . $role->id, get_string('yourwordforx', '', $role->localname));
                    $mform->setType('role_' . $role->id, PARAM_TEXT);
                }
            }
        }

        if (core_tag_tag::is_enabled('core', 'course') &&
                ((empty($course->id) && guess_if_creator_will_have_course_capability('moodle/course:tag', $categorycontext))
                || (!empty($course->id) && has_capability('moodle/course:tag', $coursecontext)))) {
            $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
            $mform->addElement('tags', 'tags', get_string('tags'),
                    array('itemtype' => 'course', 'component' => 'core'));
        }

        // Add custom fields to the form.
        $handler = core_course\customfield\course_handler::create();
        $handler->set_parent_context($categorycontext); // For course handler only.
        $handler->instance_form_definition($mform, empty($course->id) ? 0 : $course->id);

        // When two elements we need a group.
        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        if ($returnto !== 0) {
            $buttonarray[] = &$mform->createElement('submit', 'saveandreturn', get_string('savechangesandreturn'), $classarray);
        }
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('savechangesanddisplay'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        // Prepare custom fields data.
        $handler->instance_form_before_set_data($course);
      
        // Finally set the current form data
       
        $this->set_data($course);
    }

    /**
     * Fill in the current page data for this course.
     */
    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // add available groupings
        $courseid = $mform->getElementValue('id');
        if ($courseid and $mform->elementExists('defaultgroupingid')) {
            $options = array();
            if ($groupings = $DB->get_records('groupings', array('courseid'=>$courseid))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
            core_collator::asort($options);
            $gr_el =& $mform->getElement('defaultgroupingid');
            $gr_el->load($options);
        }

        // add course format options
        $formatvalue = $mform->getElementValue('format');
        if (is_array($formatvalue) && !empty($formatvalue)) {

            $params = array('format' => $formatvalue[0]);
            // Load the course as well if it is available, course formats may need it to work out
            // they preferred course end date.
            if ($courseid) {
                $params['id'] = $courseid;
            }
            $courseformat = course_get_format((object)$params);

            $elements = $courseformat->create_edit_form_elements($mform);
            for ($i = 0; $i < count($elements); $i++) {
                $mform->insertElementBefore($mform->removeElement($elements[$i]->getName(), false),
                        'addcourseformatoptionshere');
            }

            // Remove newsitems element if format does not support news.
            if (!$courseformat->supports_news()) {
                $mform->removeElement('newsitems');
            }
        }

        // Tweak the form with values provided by custom fields in use.
        $handler  = core_course\customfield\course_handler::create();
        $handler->instance_form_definition_after_data($mform, empty($courseid) ? 0 : $courseid);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $course->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametaken', '', $course->fullname);
            }
        }

        // Add field validation check for duplicate idnumber.
        if (!empty($data['idnumber']) && (empty($data['id']) || $this->course->idnumber != $data['idnumber'])) {
            if ($course = $DB->get_record('course', array('idnumber' => $data['idnumber']), '*', IGNORE_MULTIPLE)) {
                if (empty($data['id']) || $course->id != $data['id']) {
                    $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $course->fullname);
                }
            }
        }

        if ($errorcode = course_validate_dates($data)) {
            $errors['enddate'] = get_string($errorcode, 'error');
        }

        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        $courseformat = course_get_format((object)array('format' => $data['format']));
        $formaterrors = $courseformat->edit_form_validation($data, $files, $errors);
        if (!empty($formaterrors) && is_array($formaterrors)) {
            $errors = array_merge($errors, $formaterrors);
        }

        // Add the custom fields validation.
        $handler = core_course\customfield\course_handler::create();
        $errors  = array_merge($errors, $handler->instance_form_validation($data, $files));
        
        return $errors;
    }
}
