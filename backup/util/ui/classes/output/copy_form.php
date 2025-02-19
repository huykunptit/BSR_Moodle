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
 * Course copy form class.
 *
 * @package     core_backup
 * @copyright   2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author      Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_backup\output;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Course copy form class.
 *
 * @package     core_backup
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class copy_form extends \moodleform {

    /**
     * Build form for the course copy settings.
     *
     * {@inheritDoc}
     * @see \moodleform::definition()
     */
    public function definition() {
        global $CFG, $OUTPUT, $USER;

        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $coursecontext = \context_course::instance($course->id);
        $courseconfig = get_config('moodlecourse');
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];

        if (empty($course->category)) {
            $course->category = $course->categoryid;
        }

        // Course ID.
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        // Return to type.
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        // Notifications of current copies.
        $copies = \copy_helper::get_copies($USER->id, $course->id);
        if (!empty($copies)) {
            $progresslink = new \moodle_url('/backup/copyprogress.php?', array('id' => $course->id));
            $notificationmsg = get_string('copiesinprogress', 'backup', $progresslink->out());
            $notification = $OUTPUT->notification($notificationmsg, 'notifymessage');
            $mform->addElement('html', $notification);
        }

        // Return to URL.
        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);

        // Form heading.
        $mform->addElement('html', \html_writer::div(get_string('copycoursedesc', 'backup'), 'form-description mb-3'));

        // Course fullname.
        $mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);

        // Course shortname.
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);

        // Course category.
        $displaylist = \core_course_category::make_categories_list(\core_course\management\helper::get_course_copy_capabilities());
        if (!isset($displaylist[$course->category])) {
            // Always keep current category.
            $displaylist[$course->category] = \core_course_category::get($course->category, MUST_EXIST, true)->get_formatted_name();
        }
               
        function buildTree($options) {
            $tree = [];
        
            foreach ($options as $key => $value) {
                $parts = preg_split('/ \/ /', $value);
                $name = $parts[0]; // The first element is the name
                $id = $key; // The ID used as the key in the options array
        
                $ref = &$tree;
        
                foreach ($parts as $part) {
                    if (!isset($ref[$part])) {
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
                    $name = $value['name']; 
                    $id = $value['id']; 
                    
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
        
        $chooseCategory = get_string('selectcategory', 'quiz');
        $tree = buildTree($displaylist);
        $finalTree = buildHtmlList($tree);
        
        echo '
        <div id="treeContainer" class="line-tree-container">
            <div class="line-tree-modal">
                <button class="close-modal">&times;</button>
                <div class="line-tree" style="padding:5px;">
                    ' . $finalTree . '
                </div>
            </div>
        </div>';
        
        echo '<style>
        .line-tree-container {
            position: fixed;
            z-index: 2;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        }

        .class {
        max-height: 400px;
         overflow-y: scroll;
            }
        
        
       .hello {
        max-height: 400px;
         overflow-y: scroll;
            }
        
        
        
        
        .line-tree-modal {
            width: 50%;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            position: relative; /* Make it easier to position the close button */
            overflow: hidden;
            height: auto;
        }
        
        .line-tree-container.show {
            display: flex;
        }
        
        .line-tree {
            padding: 5px;
            width: 100%;
            height: 50%;
        }
        
        .line-tree summary {
            margin: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            outline: 2px dotted #ccc;
            margin-top: 5px;
            background: #fafbff;
            color: #110101;
            border-radius: 0.5em;
            padding-bottom: 5px;
        }
        
        
        
        .line-tree a {
            color: black;
            text-decoration: none;
        }
        
        .line-tree a:hover {
            color: #276ef8;
        }
        
        .line-tree ul {
            margin-left: 4em;
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
        
        .line-tree details[open] > summary:not(:empty)::before {
            background-image: none;
            content: "-";
            font-family: "cocoonCustomPrimary";
            font-weight: 900;
            font-size: 16px;
            color: #fff0f0;
            display: inline-block;
            margin-left: 0.8em;
        }
        
        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f50707;
            color: white;
            border: none;
            font-size: 24px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .close-modal:hover {
            background: #d40505;
        }
        </style>';

        $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $displaylist);
        $mform->addRule('category', null, 'required', null, 'client');
        $mform->addHelpButton('category', 'coursecategory');
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
     
        </script>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".modal-link").forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault();
                    var value = this.getAttribute("data-value");
                    
                    $(".form-autocomplete-suggestions").find("li[role=\'option\'][data-value=\'" + value + "\']").click();
                });
            });
        });
        </script>';
      
        // Course visibility.
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('coursevisibility'), $choices);
        $mform->addHelpButton('visible', 'coursevisibility');
        $mform->setDefault('visible', $courseconfig->visible);
        if (!has_capability('moodle/course:visibility', $coursecontext)) {
            $mform->hardFreeze('visible');
            $mform->setConstant('visible', $course->visible);
        }
         
        // Course start date.
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $date = (new \DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        // Course enddate.
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
            $relativedatesmodegroup[] = $mform->createElement('html', \html_writer::span(get_string('relativedatesmode_warning'),
                '', ['id' => 'relativedatesmode_warning']));
            $mform->addGroup($relativedatesmodegroup, 'relativedatesmodegroup', get_string('relativedatesmode'), null, false);
            $mform->addHelpButton('relativedatesmodegroup', 'relativedatesmode');
        }

        // Course ID number (default to the current course ID number; blank for users who can't change ID numbers).
        $mform->addElement('text', 'idnumber', get_string('idnumbercourse'), 'maxlength="100"  size="10"');
        $mform->setDefault('idnumber', $course->idnumber);
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        if (!has_capability('moodle/course:changeidnumber', $coursecontext)) {
            $mform->hardFreeze('idnumber');
            $mform->setConstant('idnumber', '');
        }

        // Keep source course user data.
        $mform->addElement('select', 'userdata', get_string('userdata', 'backup'),
            [0 => get_string('no'), 1 => get_string('yes')]);
        $mform->setDefault('userdata', 0);
        $mform->addHelpButton('userdata', 'userdata', 'backup');

        $requiredcapabilities = array(
            'moodle/restore:createuser', 'moodle/backup:userinfo', 'moodle/restore:userinfo'
        );
        if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
            $mform->hardFreeze('userdata');
            $mform->setConstant('userdata', 0);
        }

        // Keep manual enrolments.
        // Only get roles actually used in this course.
        $roles = role_fix_names(get_roles_used_in_context($coursecontext, false), $coursecontext);

        // Only add the option if there are roles in this course.
        if (!empty($roles) && has_capability('moodle/restore:createuser', $coursecontext)) {
            $rolearray = array();
            foreach ($roles as $role) {
                $roleid = 'role_' . $role->id;
                $rolearray[] = $mform->createElement('advcheckbox', $roleid,
                    $role->localname, '', array('group' => 2), array(0, $role->id));
            }

            $mform->addGroup($rolearray, 'rolearray', get_string('keptroles', 'backup'), ' ', false);
            $mform->addHelpButton('rolearray', 'keptroles', 'backup');
            $this->add_checkbox_controller(2);
        }

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitreturn', get_string('copyreturn', 'backup'));
        $buttonarray[] = $mform->createElement('submit', 'submitdisplay', get_string('copyview', 'backup'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

    /**
     * Validation of the form.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        $courseshortname = $DB->get_record('course', array('shortname' => $data['shortname']), 'fullname', IGNORE_MULTIPLE);
        if ($courseshortname) {
            $errors['shortname'] = get_string('shortnametaken', '', $courseshortname->fullname);
        }

        // Add field validation check for duplicate idnumber.
        if (!empty($data['idnumber'])) {
            $courseidnumber = $DB->get_record('course', array('idnumber' => $data['idnumber']), 'fullname', IGNORE_MULTIPLE);
            if ($courseidnumber) {
                $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $courseidnumber->fullname);
            }
        }

        // Validate the dates (make sure end isn't greater than start).
        if ($errorcode = course_validate_dates($data)) {
            $errors['enddate'] = get_string($errorcode, 'error');
        }

        return $errors;
    }

}
