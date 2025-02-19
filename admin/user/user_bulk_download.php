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
global $SESSION;

/**
 * Bulk export user into any dataformat
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @copyright  2007 Petr Skoda
 * @package    core
 */

use core_user\fields;

define('NO_OUTPUT_BUFFERING', true);
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/admin/user/lib.php');

$dataformat = optional_param('dataformat', '', PARAM_ALPHA);
$jsonUfiltering = optional_param('ufiltering', '', PARAM_RAW);
admin_externalpage_setup('userbulk');
require_capability('moodle/user:update', context_system::instance());
//dd(json_decode($jsonUfiltering));
$ufiltering = new user_filtering(json_decode($jsonUfiltering)->_fields,json_decode($jsonUfiltering)->_addform, json_decode($jsonUfiltering)->_activeform);
//dd($ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest)));
//if (empty($SESSION->bulk_users)) {
//    redirect(new moodle_url('/admin/user/user_bulk.php'));
//}else{
    $SESSION->bulk_users=array();
    add_selection_all($ufiltering);
//}

if ($dataformat) {
    $originfields = array('id'        => 'id',
                    'username'  => 'username',
                    'email'     => 'email',
                    'firstname' => 'firstname',
                    'lastname'  => 'lastname',
                    'idnumber'  => 'idnumber',
                    'institution' => 'institution',
                    'department' => 'department',
                    'phone1'    => 'phone1',
                    'phone2'    => 'phone2',
                    'city'      => 'city',
                    'country'   => 'country');
    
    $extrafields = profile_get_user_fields_with_data(0);
    $extrafields = array_slice($extrafields, 0, 1);    
  
    
    // dd(array_slice($extrafields,0,1));
    $profilefields = [];
    foreach ($extrafields as $formfield) {
        // dd($formfield);
        $profilefields[fields::PROFILE_FIELD_PREFIX . $formfield->get_shortname()] = 
        $formfield->field->name;
            // dd(($formfield->field->name));
    }
    // dd($profilefields,$extrafields);
    $date = date('dmy');
    
    $filename = $date . '_DSNguoiDung';

    $filename = clean_filename($filename);

    $downloadusers = new ArrayObject($SESSION->bulk_users);
    $iterator = $downloadusers->getIterator();
    session_write_close();

    \core\dataformat::download_data($filename, $dataformat, array_merge($originfields, $profilefields), $iterator,
            function($userid, $supportshtml) use ($originfields) {

        global $DB;

        if (!$user = $DB->get_record('user', array('id' => $userid))) {
            return null;
        }
       // Query for the user's department based on their userid.
$user_department = $DB->get_record_sql("
SELECT d.name 
FROM {company_users} cu
JOIN {department} d ON cu.departmentid = d.id
WHERE cu.userid = :userid", 
array('userid' => $userid)
);

// If a department is found, include it in the user's profile data.
$userprofiledata = array();
foreach ($originfields as $field) {
if (is_array($user->$field)) {
    $userprofiledata[$field] = reset($user->$field);
} else if ($supportshtml) {
    $userprofiledata[$field] = s($user->$field);
} else {
    $userprofiledata[$field] = $user->$field;
}
}

// Add the department to the 'department' field in the user profile data.
$userprofiledata['department'] = $user_department ? $user_department->name : 'Unknown';

// Formatting extra fields if transform is true.
$extrafields = profile_get_user_fields_with_data($userid);
foreach ($extrafields as $field) {
$fieldkey = fields::PROFILE_FIELD_PREFIX . $field->get_shortname();
if ($field->is_transform_supported()) {
    $userprofiledata[$fieldkey] = $field->display_data();
} else {
    $userprofiledata[$fieldkey] = $field->data;
}
}

return $userprofiledata;

    });

    exit;
}
//dd($SESSION->bulk_users);
$PAGE->set_primary_active_tab('siteadminnode');
$PAGE->set_secondary_active_tab('users');

echo $OUTPUT->header();
//dd($SESSION->bulk_users);
echo $OUTPUT->heading(get_string('download', 'admin'));
echo $OUTPUT->download_dataformat_selector(get_string('userbulkdownload', 'admin'), 'user_bulk_download.php');
echo $OUTPUT->footer();

