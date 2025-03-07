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
 * This file contains the profile_define_base class.
 *
 * @package core_user
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_base
 *
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_base {

    /**
     * Prints out the form snippet for creating or editing a profile field
     * @param MoodleQuickForm $form instance of the moodleform class
     */
    public function define_form(&$form) {
        $form->addElement('header', '_commonsettings', get_string('profilecommonsettings', 'admin'));
        $this->define_form_common($form);

        $form->addElement('header', '_specificsettings', get_string('profilespecificsettings', 'admin'));
        $this->define_form_specific($form);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a profile field common to all data types.
     *
     * @param MoodleQuickForm $form instance of the moodleform class
     */
    public function define_form_common(&$form) {

        $strrequired = get_string('required');

        // Accepted values for 'shortname' would follow [a-zA-Z0-9_] pattern,
        // but we are accepting any PARAM_TEXT value here,
        // and checking [a-zA-Z0-9_] pattern in define_validate_common() function to throw an error when needed.
        $form->addElement('text', 'shortname', get_string('profileshortname', 'admin'), 'maxlength="100" size="25"');
        $form->addRule('shortname', $strrequired, 'required', null, 'client');
        $form->setType('shortname', PARAM_TEXT);

        $form->addElement('text', 'name', get_string('profilename', 'admin'), 'size="50"');
        $form->addRule('name', $strrequired, 'required', null, 'client');
        $form->setType('name', PARAM_TEXT);

        $form->addElement('editor', 'description', get_string('profiledescription', 'admin'), null, null);

        $form->addElement('selectyesno', 'required', get_string('profilerequired', 'admin'));

        $form->addElement('selectyesno', 'locked', get_string('profilelocked', 'admin'));

        $form->addElement('selectyesno', 'forceunique', get_string('profileforceunique', 'admin'));

        $form->addElement('selectyesno', 'signup', get_string('profilesignup', 'admin'));

        $choices = array();
        $choices[PROFILE_VISIBLE_NONE]    = get_string('profilevisiblenone', 'admin');
        $choices[PROFILE_VISIBLE_PRIVATE] = get_string('profilevisibleprivate', 'admin');
        $choices[PROFILE_VISIBLE_TEACHERS] = get_string('profilevisibleteachers', 'admin');
        $choices[PROFILE_VISIBLE_ALL]     = get_string('profilevisibleall', 'admin');

        $form->addElement('select', 'visible', get_string('profilevisible', 'admin'), $choices);
        $form->addHelpButton('visible', 'profilevisible', 'admin');
        $form->setDefault('visible', PROFILE_VISIBLE_ALL);

        $choices = profile_list_categories();
        $form->addElement('select', 'categoryid', get_string('profilecategory', 'admin'), $choices);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a profile field specific to the current data type.
     * @param MoodleQuickForm $form instance of the moodleform class
     */
    public function define_form_specific($form) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Validate the data from the add/edit profile field form.
     *
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function define_validate($data, $files) {

        $data = (object)$data;
        $err = array();

        $err += $this->define_validate_common($data, $files);
        $err += $this->define_validate_specific($data, $files);

        return $err;
    }

    /**
     * Validate the data from the add/edit profile field form that is common to all data types.
     *
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit profile field form
     * @param array $files
     * @return  array    associative array of error messages
     */
    public function define_validate_common($data, $files) {
        global $DB;

        $err = array();

        // Check the shortname was not truncated by cleaning.
        if (empty($data->shortname)) {
            $err['shortname'] = get_string('required');

        } else {
            // Check allowed pattern (numbers, letters and underscore).
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data->shortname)) {
                $err['shortname'] = get_string('profileshortnameinvalid', 'admin');
            } else {
                // Fetch field-record from DB.
                $field = profile_get_custom_field_data_by_shortname($data->shortname);
                // Check the shortname is unique.
                if ($field and $field->id <> $data->id) {
                    $err['shortname'] = get_string('profileshortnamenotunique', 'admin');
                }
                // NOTE: since 2.0 the shortname may collide with existing fields in $USER because we load these fields into
                // $USER->profile array instead.
            }
        }

        // No further checks necessary as the form class will take care of it.
        return $err;
    }

    /**
     * Validate the data from the add/edit profile field form
     * that is specific to the current data type
     * @param array $data
     * @param array $files
     * @return  array    associative array of error messages
     */
    public function define_validate_specific($data, $files) {
        // Do nothing - overwrite if necessary.
        return array();
    }

    /**
     * Alter form based on submitted or existing data
     * @param MoodleQuickForm $mform
     */
    public function define_after_data(&$mform) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Add a new profile field or save changes to current field
     * @param array|stdClass $data from the add/edit profile field form
     */
    public function define_save($data) {
        global $DB;

        $data = $this->define_save_preprocess($data); // Hook for child classes.

        $old = false;
        if (!empty($data->id)) {
            $old = $DB->get_record('user_info_field', array('id' => (int)$data->id));
        }

        // Check to see if the category has changed.
        if (!$old or $old->categoryid != $data->categoryid) {
            $data->sortorder = $DB->count_records('user_info_field', array('categoryid' => $data->categoryid)) + 1;
        }
        if (empty($data->id)) {
            unset($data->id);
            $data->id = $DB->insert_record('user_info_field', $data);
        } else {
            $old_position = $DB->get_record('user_info_field', array('id' => $data->id));
            $old_position_list = explode("\n", $old_position->param1);
           
            $DB->update_record('user_info_field', $data);
            
            $new_position = $DB->get_record('user_info_field', array('id' => $data->id));
            $new_position_list = explode("\n", $new_position->param1);
            foreach($old_position_list as $id => $old_post) {
                if($old_post != $new_position_list[$id]) {
                    //  $sql = "UPDATE {cohort} SET contextid = :newcontext WHERE contextid = :oldcontext";
                        $sql = "UPDATE {user_info_data}
                        SET data = :new_position
                        WHERE data = :old_position AND fieldid = :data_id";
                        $DB->execute($sql, array(
                            'new_position' => $new_position_list[$id], 
                            'old_position' => $old_post, 
                            'data_id' => $data->id
                        ));                
                    
                }
            }

        }

        $field = $DB->get_record('user_info_field', array('id' => $data->id));
        if ($old) {
            \core\event\user_info_field_updated::create_from_field($field)->trigger();
        } else {
            \core\event\user_info_field_created::create_from_field($field)->trigger();
        }
        profile_purge_user_fields_cache();
    }

    /**
     * Preprocess data from the add/edit profile field form before it is saved.
     *
     * This method is a hook for the child classes to overwrite.
     *
     * @param array|stdClass $data from the add/edit profile field form
     * @return array|stdClass processed data object
     */
    public function define_save_preprocess($data) {
        // Do nothing - overwrite if necessary.
        return $data;
    }

    /**
     * Provides a method by which we can allow the default data in profile_define_* to use an editor
     *
     * This should return an array of editor names (which will need to be formatted/cleaned)
     *
     * @return array
     */
    public function define_editors() {
        return array();
    }
}



/**
 * Reorder the profile fields within a given category starting at the field at the given startorder.
 */
function profile_reorder_fields() {
    global $DB;

    if ($categories = $DB->get_records('user_info_category')) {
        foreach ($categories as $category) {
            $i = 1;
            if ($fields = $DB->get_records('user_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
                foreach ($fields as $field) {
                    $f = new stdClass();
                    $f->id = $field->id;
                    $f->sortorder = $i++;
                    $DB->update_record('user_info_field', $f);
                }
            }
        }
        profile_purge_user_fields_cache();
    }
}

/**
 * Reorder the profile categoriess starting at the category at the given startorder.
 */
function profile_reorder_categories() {
    global $DB;

    $i = 1;
    if ($categories = $DB->get_records('user_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $cat) {
            $c = new stdClass();
            $c->id = $cat->id;
            $c->sortorder = $i++;
            $DB->update_record('user_info_category', $c);
        }
        profile_purge_user_fields_cache();
    }
}

/**
 * Delete a profile category
 * @param int $id of the category to be deleted
 * @return bool success of operation
 */
function profile_delete_category($id) {
    global $DB;

    // Retrieve the category.
    if (!$category = $DB->get_record('user_info_category', array('id' => $id))) {
        throw new \moodle_exception('invalidcategoryid');
    }

    if (!$categories = $DB->get_records('user_info_category', null, 'sortorder ASC')) {
        throw new \moodle_exception('nocate', 'debug');
    }

    unset($categories[$category->id]);

    if (!count($categories)) {
        return false; // We can not delete the last category.
    }

    // Does the category contain any fields.
    if ($DB->count_records('user_info_field', array('categoryid' => $category->id))) {
        if (array_key_exists($category->sortorder - 1, $categories)) {
            $newcategory = $categories[$category->sortorder - 1];
        } else if (array_key_exists($category->sortorder + 1, $categories)) {
            $newcategory = $categories[$category->sortorder + 1];
        } else {
            $newcategory = reset($categories); // Get first category if sortorder broken.
        }

        $sortorder = $DB->count_records('user_info_field', array('categoryid' => $newcategory->id)) + 1;

        if ($fields = $DB->get_records('user_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
            foreach ($fields as $field) {
                $f = new stdClass();
                $f->id = $field->id;
                $f->sortorder = $sortorder++;
                $f->categoryid = $newcategory->id;
                if ($DB->update_record('user_info_field', $f)) {
                    $field->sortorder = $f->sortorder;
                    $field->categoryid = $f->categoryid;
                    \core\event\user_info_field_updated::create_from_field($field)->trigger();
                }
            }
        }
    }

    // Finally we get to delete the category.
    $DB->delete_records('user_info_category', array('id' => $category->id));
    profile_reorder_categories();

    \core\event\user_info_category_deleted::create_from_category($category)->trigger();
    profile_purge_user_fields_cache();

    return true;
}

/**
 * Deletes a profile field.
 * @param int $id
 */
function profile_delete_field($id) {
    global $DB;

    // Remove any user data associated with this field.
    if (!$DB->delete_records('user_info_data', array('fieldid' => $id))) {
        throw new \moodle_exception('cannotdeletecustomfield');
    }

    // Note: Any availability conditions that depend on this field will remain,
    // but show the field as missing until manually corrected to something else.

    // Need to rebuild course cache to update the info.
    rebuild_course_cache(0, true);

    // Prior to the delete, pull the record for the event.
    $field = $DB->get_record('user_info_field', array('id' => $id));

    // Try to remove the record from the database.
    $DB->delete_records('user_info_field', array('id' => $id));

    \core\event\user_info_field_deleted::create_from_field($field)->trigger();
    profile_purge_user_fields_cache();

    // Reorder the remaining fields in the same category.
    profile_reorder_fields();
}

/**
 * Change the sort order of a field
 *
 * @param int $id of the field
 * @param string $move direction of move
 * @return bool success of operation
 */
function profile_move_field($id, $move) {
    global $DB;

    // Get the field object.
    if (!$field = $DB->get_record('user_info_field', array('id' => $id))) {
        return false;
    }
    // Count the number of fields in this category.
    $fieldcount = $DB->count_records('user_info_field', array('categoryid' => $field->categoryid));

    // Calculate the new sortorder.
    if ( ($move == 'up') and ($field->sortorder > 1)) {
        $neworder = $field->sortorder - 1;
    } else if (($move == 'down') and ($field->sortorder < $fieldcount)) {
        $neworder = $field->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the field object that is currently residing in the new position.
    $params = array('categoryid' => $field->categoryid, 'sortorder' => $neworder);
    if ($swapfield = $DB->get_record('user_info_field', $params)) {

        // Swap the sortorders.
        $swapfield->sortorder = $field->sortorder;
        $field->sortorder     = $neworder;

        // Update the field records.
        $DB->update_record('user_info_field', $field);
        $DB->update_record('user_info_field', $swapfield);

        \core\event\user_info_field_updated::create_from_field($field)->trigger();
        \core\event\user_info_field_updated::create_from_field($swapfield)->trigger();
    }

    profile_reorder_fields();
    return true;
}

/**
 * Change the sort order of a category.
 *
 * @param int $id of the category
 * @param string $move direction of move
 * @return bool success of operation
 */
function profile_move_category($id, $move) {
    global $DB;
    // Get the category object.
    if (!($category = $DB->get_record('user_info_category', array('id' => $id)))) {
        return false;
    }

    // Count the number of categories.
    $categorycount = $DB->count_records('user_info_category');

    // Calculate the new sortorder.
    if (($move == 'up') and ($category->sortorder > 1)) {
        $neworder = $category->sortorder - 1;
    } else if (($move == 'down') and ($category->sortorder < $categorycount)) {
        $neworder = $category->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the category object that is currently residing in the new position.
    if ($swapcategory = $DB->get_record('user_info_category', array('sortorder' => $neworder))) {

        // Swap the sortorders.
        $swapcategory->sortorder = $category->sortorder;
        $category->sortorder     = $neworder;

        // Update the category records.
        $DB->update_record('user_info_category', $category);
        $DB->update_record('user_info_category', $swapcategory);

        \core\event\user_info_category_updated::create_from_category($category)->trigger();
        \core\event\user_info_category_updated::create_from_category($swapcategory)->trigger();
        profile_purge_user_fields_cache();

        return true;
    }

    return false;
}

/**
 * Retrieve a list of all the available data types
 * @return   array   a list of the datatypes suitable to use in a select statement
 */
function profile_list_datatypes() {
    $datatypes = array();

    $plugins = core_component::get_plugin_list('profilefield');
    foreach ($plugins as $type => $unused) {
        $datatypes[$type] = get_string('pluginname', 'profilefield_'.$type);
    }
    asort($datatypes);

    return $datatypes;
}

/**
 * Retrieve a list of categories and ids suitable for use in a form
 * @return   array
 */
function profile_list_categories() {
    global $DB;
    $categories = $DB->get_records_menu('user_info_category', null, 'sortorder ASC', 'id, name');
    return array_map('format_string', $categories);
}

/**
 * Create or update a profile category
 *
 * @param stdClass $data
 */
function profile_save_category(stdClass $data): void {
    global $DB;

    if (empty($data->id)) {
        unset($data->id);
        $data->sortorder = $DB->count_records('user_info_category') + 1;
        $data->id = $DB->insert_record('user_info_category', $data, true);

        $createdcategory = $DB->get_record('user_info_category', array('id' => $data->id));
        \core\event\user_info_category_created::create_from_category($createdcategory)->trigger();
    } else {
        $DB->update_record('user_info_category', $data);

        $updatedcateogry = $DB->get_record('user_info_category', array('id' => $data->id));
        \core\event\user_info_category_updated::create_from_category($updatedcateogry)->trigger();
    }
    profile_reorder_categories();
    profile_purge_user_fields_cache();
}

/**
 * Edit a category
 *
 * @deprecated since Moodle 3.11 MDL-71051 - please do not use this function any more.
 * @todo MDL-71413 This will be deleted in Moodle 4.3.
 * @see profile_save_category()
 *
 * @param int $id
 * @param string $redirect
 */
function profile_edit_category($id, $redirect) {
    global $DB, $OUTPUT, $CFG;

    debugging('Function profile_edit_category() is deprecated without replacement, see also profile_save_category()',
        DEBUG_DEVELOPER);

    $categoryform = new \core_user\form\profile_category_form();

    if ($category = $DB->get_record('user_info_category', array('id' => $id))) {
        $categoryform->set_data($category);
    }

    if ($categoryform->is_cancelled()) {
        redirect($redirect);
    } else {
        if ($data = $categoryform->get_data()) {
            profile_save_category($data);
            redirect($redirect);
        }

        if (empty($id)) {
            $strheading = get_string('profilecreatenewcategory', 'admin');
        } else {
            $strheading = get_string('profileeditcategory', 'admin', format_string($category->name));
        }

        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $categoryform->display();
        echo $OUTPUT->footer();
        die;
    }

}

/**
 * Save updated field definition or create a new field
 *
 * @param stdClass $data data from the form profile_field_form
 * @param array $editors editors for this form field type
 */
function profile_save_field(stdClass $data, array $editors): void {
    global $CFG;

    require_once($CFG->dirroot.'/user/profile/field/'.$data->datatype.'/define.class.php');
    $newfield = 'profile_define_'.$data->datatype;
    /** @var profile_define_base $formfield */
    $formfield = new $newfield();

    // Collect the description and format back into the proper data structure from the editor.
    // Note: This field will ALWAYS be an editor.
    $data->descriptionformat = $data->description['format'];
    $data->description = $data->description['text'];

    // Check whether the default data is an editor, this is (currently) only the textarea field type.
    if (is_array($data->defaultdata) && array_key_exists('text', $data->defaultdata)) {
        // Collect the default data and format back into the proper data structure from the editor.
        $data->defaultdataformat = $data->defaultdata['format'];
        $data->defaultdata = $data->defaultdata['text'];
    }

    // Convert the data format for.
    if (is_array($editors)) {
        foreach ($editors as $editor) {
            if (isset($field->$editor)) {
                $field->{$editor.'format'} = $field->{$editor}['format'];
                $field->$editor = $field->{$editor}['text'];
            }
        }
    }
    $formfield->define_save($data);
    profile_reorder_fields();
    profile_reorder_categories();
}

/**
 * Edit a profile field.
 *
 * @deprecated since Moodle 3.11 MDL-71051 - please do not use this function any more.
 * @todo MDL-71413 This will be deleted in Moodle 4.3.
 * @see profile_save_field()
 *
 * @param int $id
 * @param string $datatype
 * @param string $redirect
 */
function profile_edit_field($id, $datatype, $redirect) {
    global $OUTPUT, $PAGE;

    debugging('Function profile_edit_field() is deprecated without replacement, see also profile_save_field()',
        DEBUG_DEVELOPER);

    $fieldform = new \core_user\form\profile_field_form();
    $fieldform->set_data_for_dynamic_submission();

    if ($fieldform->is_cancelled()) {
        redirect($redirect);

    } else {
        if ($data = $fieldform->get_data()) {
            profile_save_field($data, $fieldform->editors());
            redirect($redirect);
        }

        $datatypes = profile_list_datatypes();

        if (empty($id)) {
            $strheading = get_string('profilecreatenewfield', 'admin', $datatypes[$datatype]);
        } else {
            $strheading = get_string('profileeditfield', 'admin', format_string($fieldform->get_field_record()->name));
        }

        // Print the page.
        $PAGE->navbar->add($strheading);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $fieldform->display();
        echo $OUTPUT->footer();
        die;
    }
}

/**
 * Purge the cache for the user profile fields
 */
function profile_purge_user_fields_cache() {
    $cache = \cache::make_from_params(cache_store::MODE_REQUEST, 'core_profile', 'customfields',
        [], ['simplekeys' => true, 'simpledata' => true]);
    $cache->purge();
}
