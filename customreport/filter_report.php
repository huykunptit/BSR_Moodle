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
 * Filter form for the custom report.
 *
 * @package   local_yourpluginname
 * @copyright Year Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class filter_form extends moodleform {
    // Define the form.
    public function definition() {
        $mform = $this->_form;
        
        // Add elements to form.
        $mform->addElement('text', 'exam_name', get_string('quizname', 'quiz'));
        $mform->setType('exam_name', PARAM_NOTAGS);
        
        // Define the elements to be grouped
        $elements = array();
        $options = array(
            'similar' => get_string('all', 'core'),
            'equal' => get_string('contains', 'filters'),
            'notequal' => get_string('doesnotcontain', 'filters')
        );
        
        $elements[] = $mform->createElement('select', 'category', '', $options);
        $elements[] = $mform->createElement('button', 'openModalBtn1', get_string('selectacategory', 'question'));
        $elements[] = $mform->createElement('advcheckbox', 'include_checkbox', '', get_string('subcats', 'qtype_randomsamatch'));

        // Add the group to the form with a label
        $mform->addGroup($elements, 'category_button_group', get_string('category', 'question'), array(' '), false);

        // Set types for the elements
        $mform->setType('_category', PARAM_NOTAGS);
        $mform->setType('include_checkbox', PARAM_BOOL);

        $mform->addElement('text', 'select_category', '');
        
        $objs = array();

        $objs[] = $mform->createElement('static', $this->_name.'_s1', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', $this->_name.'_s2', null,
            html_writer::tag('div', get_string('isafter', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_selector', $this->_name.'_sdt', null, array('optional' => true));
        $objs[] = $mform->createElement('static', $this->_name.'_s3', null, html_writer::end_tag('div'));
        
        $objs[] = $mform->createElement('static', $this->_name.'_s4', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', $this->_name.'_s5', null,
            html_writer::tag('div', get_string('isbefore', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_selector', $this->_name.'_edt', null, array('optional' => true));
        $objs[] = $mform->createElement('static', $this->_name.'_s6', null, html_writer::end_tag('div'));
        
        $grp =& $mform->addGroup($objs, $this->_name.'_grp', get_string('startdate', 'core'), '', false);
        

        if ($this->_advanced) {
            $mform->setAdvanced($this->_name.'_grp');
        }

        // Add action buttons.
        $this->add_action_buttons(true, get_string('search', 'core'));
    }
}
