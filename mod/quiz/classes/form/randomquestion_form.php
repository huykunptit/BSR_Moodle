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
 * Defines the editing form for random questions.
 *
 * @package    mod_quiz
 * @copyright  2018 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quiz\form;

use core_course_category;
use qbank_managecategories\helper;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class randomquestion_form
 *
 * @package    mod_quiz
 * @copyright  2018 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class randomquestion_form extends \moodleform {

    /**
     * Form definiton.
     */
    public function definition() {
        $mform = $this->_form;

        $contexts = $this->_customdata['contexts'];
        $usablecontexts = $contexts->having_cap('moodle/question:useall');

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

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
            // dd($catmenu);
            function compareByName($a, $b) {
                return strcmp($a, $b);
            }
            
         
            uasort($catmenu, 'compareByName');
            
      $currentUrl = $_SERVER['REQUEST_URI']; 


      if (strpos($currentUrl, '/question/edit1.php') !== false) {

          $catmenu = array_slice($catmenu, 1);
      }

      // dd($catmenu);
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

//   $customHtml = '
//   <button id="openModalBtn1" style="color:white;padding:10px;background: #1e4fa5;border-radius: 0.4em;border:solid #1e4fa5;margin-top:8px;">' . get_string('selectacategory', 'question') . '</button>
//   <div id="myModal1" class="modal">
//       <div class="modal-content" style="width: 50% !important">
//           <div class="modal-header">
//               <h2 style="text-align: center;">' . get_string('selectacategory', 'question') . '</h2>
//               <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
//           </div>
//           <div class="mt-3"  style="padding: 0 1rem; height: 358px; overflow: auto">
//               <ul>';
  
//       $stack = [];
//       $currentDepth = 0;
  
//       foreach ($catmenu as $item) {
//           foreach ($item as $folder => $files) {
//               $folderLabel = htmlspecialchars($folder);
//               $customHtml .= '<li><details><summary><span class="folder-icon opened closed"></span><a href="#">' . $folderLabel . '</a></summary>';
//               $customHtml .= '<ul>';
  
//               foreach ($files as $key => $file) {
//                   $indentation = substr_count($file, '&nbsp;');
//                   $file_clean = htmlspecialchars(trim(preg_replace('/&nbsp;/', '', $file)));
  
//                   list($catId, $pathId) = explode(',', $key);
//                   $categoryParamValue = urlencode($catId) . '%2C' . urlencode($pathId);
  
//                   if ($indentation > $currentDepth) {
//                       $customHtml .= '<ul>';
//                       array_push($stack, $currentDepth);
//                       $currentDepth = $indentation;
//                   }
//                   $customHtml .= '<li><summary><span class="folder-icon opened closed"></span><a href="#" class="modal-link" data-value="' . $key . '">' . $file_clean . '</a></summary></li>';
//               }
  
//               while (!empty($stack)) {
//                   $customHtml .= '</ul>';
//                   array_pop($stack);
//               }
  
//               $customHtml .= '</ul></details></li>';
//           }
//       }
  
//       $customHtml .= '</ul>
//           </div>
//       </div>
//   </div>
//   <style>
//   label.mr-1 {
//       display:none;
//   }
//   select#id_selectacategory {
//       position: absolute;
//       top: 14.7rem;
//       margin-left: 13rem;
//       pointer-events: none; 
//       touch-action: none; 
//       user-select: none; 
//       -webkit-touch-callout: none;
//       -moz-user-select: none;
//       -ms-user-select: none;
//       -khtml-user-select: none;
//       -webkit-user-select: none;
//   }
//   .modal {
//       display: none;  
//       position: fixed; 
//       z-index: 1; 
//       left: 0;
//       top: 0;
//       background-color: rgba(0,0,0,0.4);
//   }
//   .modal-content {
//       background-color: #fefefe;
//       margin: 10% auto; 
//       padding: 20px;
//       border: 1px solid #888;
//       width: 50%; 
//       height: 50%;
//       overflow: hidden; 
//       max-height: 60%;
//   }
//   .close {
//       color: #f8f6f6 !important;
//       position: absolute;
//       right: 40px;
//       top: 40px;
//       background: #f50707;
//       width: 30px;
//       height: 30px;
//       text-align: center;
//       line-height: 30px;
//       border-radius: 50%;
//       font-size: 28px;
//       font-weight: bold;
//   }
//   .close:hover,
//   .close:focus {
//       color: #f8f6f6 !important;
//       text-decoration: none;
//       cursor: pointer;
//   }
//   .modal ul {
//       list-style-type: none;
//       padding: 0;
//       margin: 0;
//   }
//   .modal li {
//       margin-bottom: 10px; 
//       font-weight: bold;
//       cursor: pointer;
//       outline: 2px dotted #3744f5;
//       margin-top:5px;
//       background: #fafbff;
//       color: #110101;
//       border-radius: 0.5em;
//       padding-bottom: 5px;
//   }
//   .modal  li ul {
//       margin-left: 2Rem; 
//   }
//   .modal li ul a {
//       text-decoration: none;
//       color: #000;
//       margin-left: 2Rem;
//   }
//   details .folder-icon {
//       display: inline-block;
//       width: 16px; 
//       height: 16px; 
//       background-size: contain;
//       background-repeat: no-repeat;
//       margin-right: 5px; 
//   }
//   details summary {
//       font-weight: bold;
//       cursor: pointer;
//       outline: 1.5px solid #0876e5;
//       margin-top: 5px;
//       background: #e1e6ee;
//       color: #110101;
//       border-radius: 0.5em;
//       padding-bottom: 5px;
//   }
//   details summary::marker {
//       content: "";
//   }
//   details > summary {
//       cursor: pointer;
//   }
//   details > summary::before {
//       background: #055091; 
//       content: "+"; 
//       font-family: "cocoonCustomPrimary";
//       font-weight: 900;
//       font-size: 16px;
//       color: #fff0f0; 
//       display: inline-block;
//       margin-left: 0.8em; 
//       width: 20px; 
//       height: 20px;
//       text-align: center; 
//       line-height: 20px;
//       border-radius: 50%;
//   }
//   details[open] > summary::before {
//       background-image: none; 
//       content: "-";
//       font-family: "cocoonCustomPrimary";
//       font-weight: 900;
//       font-size: 16px; 
//       color: #fff0f0;       
//       display: inline-block;
//       margin-left: 0.8em;
//   }
//   </style>
//   <script>
//   var modal = document.getElementById("myModal1");
//   var btn = document.getElementById("openModalBtn1");
//   var span = document.getElementsByClassName("close")[0];
  
//   btn.onclick = function(event) {
//       event.preventDefault(); 
//       modal.style.display = "block";
//   }
  
//   span.onclick = function() {
//       modal.style.display = "none";
//   }
  
//   window.onclick = function(event) {
//       if (event.target == modal) {
//           modal.style.display = "none";
//       }
//   }
//   document.querySelectorAll(".modal-link").forEach(function(link) {
//       link.addEventListener("click", function(event) {
//           event.preventDefault();
//           var value = this.getAttribute("data-value");
//           var select = document.getElementById("id_category");
//           select.value = value;
//           modal.style.display = "none";
//       });
//   });
//   function selectOption(value) {
//       document.getElementById("id_category").value = value;
//       $("#myModal").modal("hide");
//   }
//   </script>';

// $mform->addElement('html', $customHtml);
        $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                ['contexts' => $usablecontexts, 'top' => true]);
        
        $mform->addElement('advcheckbox', 'includesubcategories', get_string('recurse', 'quiz'), null, null, [0, 1]);

        $tops = question_get_top_categories_for_contexts(array_column($contexts->all(), 'id'));
        $mform->hideIf('includesubcategories', 'category', 'in', $tops);

        $tags = \core_tag_tag::get_tags_by_area_in_contexts('core_question', 'question', $usablecontexts);
        $tagstrings = [];
        foreach ($tags as $tag) {
            $tagstrings["{$tag->id},{$tag->name}"] = $tag->name;
        }
        $options = [
                'multiple' => true,
                'noselectionstring' => get_string('anytags', 'quiz'),
        ];
        $mform->addElement('autocomplete', 'fromtags', get_string('randomquestiontags', 'mod_quiz'), $tagstrings, $options);
        $mform->addHelpButton('fromtags', 'randomquestiontags', 'mod_quiz');

        $mform->addElement('hidden', 'slotid');
        $mform->setType('slotid', PARAM_INT);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function set_data($defaultvalues) {
        $mform = $this->_form;

        if ($defaultvalues->fromtags) {
            $fromtagselement = $mform->getElement('fromtags');
            foreach ($defaultvalues->fromtags as $fromtag) {
                if (!$fromtagselement->optionExists($fromtag)) {
                    $optionname = get_string('randomfromunavailabletag', 'mod_quiz', explode(',', $fromtag)[1]);
                    $fromtagselement->addOption($optionname, $fromtag);
                }
            }
        }

        parent::set_data($defaultvalues);
    }
}
