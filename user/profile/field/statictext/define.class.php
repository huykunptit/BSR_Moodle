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
 * Text profile field definition.
 *
 * @package    profilefield_statictext
 * @copyright  2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_statictext
 *
 * @copyright  2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_statictext extends profile_define_base {

    /**
     * Add elements for creating/editing a text profile field.
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        // Default data.
        $label = get_string('description', 'profilefield_statictext');
        $options = ['rows' => 6, 'cols' => 50];
        $form->addElement('textarea', 'defaultdata', $label, $options);
        $form->setType('defaultdata', PARAM_TEXT);
        $form->addRule('defaultdata', get_string('descriptionrequired', 'profilefield_statictext'), 'required', null, 'client');

        $form->addElement('text', 'param1', get_string('label', 'profilefield_statictext'), 'size="50"');
        $form->setType('param1', PARAM_TEXT);

        $options = [get_string('no'), get_string('yes')];
        $form->addElement('select', 'param2', get_string('showinprofile', 'profilefield_statictext'), $options, 0);
    }
}
