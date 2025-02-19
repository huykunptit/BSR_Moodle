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

namespace mod_quiz\form;

use core_course_category;
use core_tag_tag;
use moodleform;
use qbank_managecategories\helper;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


/**
 * The add random questions form.
 *
 * @package   mod_quiz
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_random_form extends moodleform {

    protected function definition() {
        global $OUTPUT, $PAGE, $CFG,$DB;
        $mform = $this->_form;
        $mform->setDisableShortforms();

        $contexts = $this->_customdata['contexts'];
       
        $usablecontexts = $contexts->having_cap('moodle/question:useall');
        // Random from existing category section.
        $mform->addElement('header', 'existingcategoryheader',
                get_string('randomfromexistingcategory', 'quiz'));

        foreach($usablecontexts as $key => $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                // remove this context from the list of contexts
                unset($usablecontexts[$key]);
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
            $all_contexts[] = \core\context\coursecat::instance($category_id);
        }
        $usablecontexts = array_merge($usablecontexts, $all_contexts);
        $catmenu = helper::question_category_options($usablecontexts, true, 0,
                true, -1, false);
     
                foreach ($catmenu as &$category) {
                    foreach ($category as $key => &$options) {
                        $options = array_slice($options, 0);
                    }
                    unset($category);
                }
              
             
            
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
              
        //    $displaydata['categoryselect'] = \html_writer::select($catmenu, 'category', $cat, [],
        //       array('class' => 'form-control custom-select ', 'id' => 'id_parent'));
        //     //   dd($displaydata);
        
          $name = get_string('selectacategory', 'question');
          
          $customHtml = '
          <button id="openModalBtn1" style="color:white;padding:10px;width: 11%; background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">' . get_string('selectacategory', 'question') . '</button>
          <div id="myModal1" class="modal">
              <div class="modal-content" style="width: 50% !important">
                  <div class="modal-header2 d-flex" style="align-items:center; justify-content: space-between">
                      <h2 class="text-center mr-3">' . get_string('selectacategory', 'question') . '</h2>
                      <input type="text" id="searchInput" placeholder="Tìm kiếm...">
                      <span class="close close-modal d-flex align-items-center" style="place-content:center">&times;</span>
                  </div>
                  <div class="mt-3" style="padding: 0 1rem;overflow: auto">';
                  $customHtml .= '<ul>';
          // Start the tree structure
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
          .modal li li {
  padding: 0 0 0 2em;
}
         .modal li {
            position: relative;
            margin: 0;
            padding: 0 0 0 1em;
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
        
          .modal-li {
              margin-bottom: 10px; 
              font-weight: bold;
              cursor: pointer;
              outline: 2px dotted #3744f5;
              margin-top:5px;
              background: #fafbff;
              color: #110101;
              border-radius: 0.5em;
              margin-left: 1rem;
              padding-bottom: 10px !important;
              padding-left: 2rem;
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
                      var select = document.getElementById("id_category");
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
                      var select = document.getElementById("id_category");
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
                  var select = document.getElementById("id_category");
                  $("#id_category").val(value);
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
                  document.getElementById("id_category").value = value;
                  console.log(value);
                  modal.style.display = "none";
              }
      
          });
      </script>';
          
        
        
        $mform->addElement('html', $customHtml);
        $mform->addElement('questioncategory', 'category', get_string('category'),
                ['contexts' => $usablecontexts, 'top' => true]);
        $mform->setDefault('category', $this->_customdata['cat']);

        $mform->addElement('checkbox', 'includesubcategories', '', get_string('recurse', 'quiz'));

        $tops = question_get_top_categories_for_contexts(array_column($contexts->all(), 'id'));
        $mform->hideIf('includesubcategories', 'category', 'in', $tops);

        if ($CFG->usetags) {
            $tagstrings = [];
            $tags = core_tag_tag::get_tags_by_area_in_contexts('core_question', 'question', $usablecontexts);
            foreach ($tags as $tag) {
                $tagstrings["{$tag->id},{$tag->name}"] = $tag->name;
            }
            $options = [
                'multiple' => true,
                'noselectionstring' => get_string('anytags', 'quiz'),
            ];
            $mform->addElement('autocomplete', 'fromtags', get_string('randomquestiontags', 'mod_quiz'), $tagstrings, $options);
            $mform->addHelpButton('fromtags', 'randomquestiontags', 'mod_quiz');
        }

        // TODO: in the past, the drop-down used to only show sensible choices for
        // number of questions to add. That is, if the currently selected filter
        // only matched 9 questions (not already in the quiz), then the drop-down would
        // only offer choices 1..9. This nice UI hint got lost when the UI became Ajax-y.
        // We should add it back.
        $mform->addElement('text', 'numbertoadd', get_string('randomnumber', 'quiz'), array(
            'type' => 'number',
            'style' => 'width: 30%;', // Add your inline styles here
            'oninput' => 'this.value=this.value.replace(/[^0-9]/g, "");'
        ));
        $mform->setType('numbertoadd', PARAM_INT);
        
        $previewhtml = $OUTPUT->render_from_template('mod_quiz/random_question_form_preview', []);
        $mform->addElement('html', $previewhtml);
        $mform->addElement('submit', 'existingcategory', get_string('addrandomquestion', 'quiz'));
        
        

        // If the manage categories plugins is enabled, add the elements to create a new category in the form.
        if (\core\plugininfo\qbank::is_plugin_enabled(\qbank_managecategories\helper::PLUGINNAME)) {
            // Random from a new category section.
            $mform->addElement('header', 'newcategoryheader',
                    get_string('randomquestionusinganewcategory', 'quiz'));

            $mform->addElement('text', 'name', get_string('name'), 'maxlength="254" size="50"');
            $mform->setType('name', PARAM_TEXT);
            $customHtml = '
            <button id="openModalBtn2" style="color:white;padding:10px;width: 11%; background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">' . get_string('selectacategory', 'question') . '</button>
        <div id="myModal2" class="modal2">
            <div class="modal-content2" style="width: 50% !important">
        <div class="modal-header2 d-flex" style="justify-content: space-between; align-items: center">
            <h2 style="text-align: center;">' . get_string('selectacategory', 'question') . '</h2>
            <input type="text" id="searchInput2" style="width:70%;margin-bottom:10px;" placeholder="Tìm kiếm...">
            <span class="close2 close-modal2 d-flex align-items-center" style="place-content:center">&times;</span>
        </div>
        <div class="mt-3" style="padding: 0 1rem;overflow: scroll;
  height: 100%;" >';
        $customHtml .= '<ul>';
  
        $stack = [];
        $currentDepth = 0;
        $lastDepth = 0;
           
            usort($catmenu, function($a, $b) {
                return key($a) <=> key($b);
            });
            
            foreach ($catmenu as $item) {
                foreach ($item as $folder => $files) {
                    // $files = array_slice($files, 1);
                    
                    $folderLabel = htmlspecialchars($folder);
                    $customHtml .= '<li class="modal-li mb-3"><details><summary><span class="folder-icon opened closed"></span><a>' . $folderLabel . '</a></summary>';
                    $customHtml .= '<ul>';
            
                    foreach ($files as $key => $file) {
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
                    while ($currentDepth > 0) {
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
    .modal2 {
        display: none;  
        position: fixed; 
        z-index: 1; 
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.4);
    }
    #searchInput {
        width: 70%;
        border: 1px solid #ccc;
        padding: 5px;
        margin-right: 30px;
    }
    .modal-content2 {
        background-color: #fefefe;
        margin: auto; 
        padding: 20px;
        border: 1px solid #888;
        width: 100%;
        height: 100%;
        overflow: hidden; 
        max-height: 90%;
        border-radius: 1em;
    }
    .close2 {
        color: #f8f6f6;
        // position: absolute;
        // right: 40px;
        // top: 40px;
        background: #f50707;
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        border-radius: 50%;
        font-size: 28px;
        font-weight: bold;
    }
    .close2:hover,
    .close2:focus {
        color: #f8f6f6;
        text-decoration: none;
        cursor: pointer;
    }
    .modal2 ul {
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
    }
    .modal2 li .modal-li ul {
        margin-left: 2rem; 
    }
    .modal2 li ul a {
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
    var modal2 = document.getElementById("myModal2");
    var btn2 = document.getElementById("openModalBtn2");
    var closeButtons2 = document.querySelectorAll(".close2, .close-modal2");
    var span2 = document.getElementsByClassName("close2")[0];
    var closeModalButton2 = document.getElementsByClassName("close-modal2");
    
    btn2.onclick = function(event) {
        event.preventDefault(); 
        modal2.style.display = "block";
    }
    
    span2.onclick = function() {
        modal2.style.display = "none";
    }
    
    closeButtons2.forEach(function(button) {
        button.onclick = function() {
            modal2.style.display = "none";
        }
    });
    
    window.onclick = function(event) {
        if (event.target == modal2) {
            modal2.style.display = "none";
        }
    }
    
    var searchInput2 = document.getElementById("searchInput2");
    var allItems2 = document.querySelectorAll(".modal-content2 details li");
    
    function resetTreeView2() {
        allItems2.forEach(function(item) {
            item.style.display = "block";
            item.open = false;
        });
    }
    
    searchInput2.addEventListener("input", function() {
        var searchText = this.value.toLowerCase().trim();
        if (searchText === "") {
            resetTreeView2();
            return;
        }
    
        var hasResult = false;
        allItems2.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            var hasMatch = text.includes(searchText);
            
            if (hasMatch) {
                showParents2(item);
                item.style.display = "block";
                item.open = true;
                hasResult = true;
                
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
        
        allItems2.forEach(function(item) {
            item.style.display = "none";
            item.open = false;
        });
    
        allItems2.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            if (text.includes(searchText)) {
                showParents2(item);
                item.style.display = "block";
                item.open = true;
            }
        });
    
        if (!hasResult) {
            allItems2.forEach(function(item) {
                item.style.display = "none";
            });
        }
    });
    
    function showParents2(element) {
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
                
                var select = document.getElementById("id_parent");
                var options = select.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == value) {
                        select.selectedIndex = i;
                        break;
                    }
                }
    
                console.log("Selected value: ", select.value);
    
                $("#myModal2").modal("hide");
            });
        });
    });
    
    document.querySelectorAll(".modal-link").forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            var value = this.getAttribute("data-value");
            var select = document.getElementById("id_parent");
            $("#id_parent").val(value);
            console.log(value);
            modal2.style.display = "none";
            var checkbox = document.getElementById("recurse_on2");
            checkbox.checked = checkbox.checked;
            const clickEvent = new MouseEvent(\'click\', {
                bubbles: true,
                cancelable: true,
                view: window
            });
            checkbox.checked = !checkbox.checked;  
            checkbox.dispatchEvent(clickEvent);   
        });
    });
    
    function selectOption2(value) {
        document.getElementById("id_parent").value = value;
        console.log(value);
        modal2.style.display = "none";
    }
    </script>';
            
            
            $mform->addElement('html', $customHtml);
            
            $mform->addElement('questioncategory', 'parent', get_string('parentcategory', 'question'),
                    ['contexts' => $usablecontexts, 'top' => true]);
            $mform->addHelpButton('parent', 'parentcategory', 'question');

            $mform->addElement('submit', 'newcategory',
                    get_string('createcategoryandaddrandomquestion', 'quiz'));
        }

        // Cancel button.
        $mform->addElement('cancel');
        $mform->closeHeaderBefore('cancel');

        $mform->addElement('hidden', 'addonpage', 0, 'id="rform_qpage"');
        $mform->setType('addonpage', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'cmid', 0);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'returnurl', 0);
        $mform->setType('returnurl', PARAM_LOCALURL);

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('mod_quiz/add_random_form', 'init', [
            $mform->getAttribute('id'),
            $contexts->lowest()->id,
            $tops,
            $CFG->usetags
        ]);
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        if (!empty($fromform['newcategory']) && trim($fromform['name']) == '') {
            $errors['name'] = get_string('categorynamecantbeblank', 'question');
        }

        return $errors;
    }

    /**
     * Return an arbitrary array for the dropdown menu
     *
     * @param int $maxrand
     * @return array of integers [1, 2, ..., 100] (or to the smaller of $maxrand and 100.)
     */
    private function get_number_of_questions_to_add_choices($maxrand = 100) {
        $randomcount = [];
        for ($i = 1; $i <= min(100, $maxrand); $i++) {
            $randomcount[$i] = $i;
        }
        return $randomcount;
    }
}
