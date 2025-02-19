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
 * Allows you to edit a users profile
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */
// define max file size of avatar
DEFINE ('MAX_FILE_SIZE', 3000000);

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editadvanced_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/webservice/lib.php');
// set upload_max_filesize to 3M

echo "<!DOCTYPE html>";

$id     = optional_param('id', $USER->id, PARAM_INT);    // User id; -1 if creating new user.
$course = optional_param('course', SITEID, PARAM_INT);   // Course id (defaults to Site).
$returnto = optional_param('returnto', null, PARAM_ALPHA);  // Code determining where to return to after save.

$PAGE->set_url('/user/editadvanced.php', array('course' => $course, 'id' => $id));

$course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);
if (!empty($USER->newadminuser)) {
    // Ignore double clicks, we must finish all operations before cancelling request.
    ignore_user_abort(true);

    $PAGE->set_course($SITE);
    $PAGE->set_pagelayout('maintenance');
} else {
    if ($course->id == SITEID) {
        require_login();
        $PAGE->set_context(context_system::instance());
    } else {
        require_login($course);
    }
    $PAGE->set_pagelayout('admin');
    $PAGE->add_body_class('limitedwidth');
}

if ($course->id == SITEID) {
    $coursecontext = context_system::instance();   // SYSTEM context.
} else {
    $coursecontext = context_course::instance($course->id);   // Course context.
}
$systemcontext = context_system::instance();

if ($id == -1) {
    // Creating new user.
    $user = new stdClass();
    $user->id = -1;
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->deleted = 0;
    $user->timezone = '99';
    require_capability('moodle/user:create', $systemcontext);
    admin_externalpage_setup('addnewuser', '', array('id' => -1));
    $PAGE->set_primary_active_tab('siteadminnode');
    $PAGE->navbar->add(get_string('addnewuser', 'moodle'), $PAGE->url);
} else {
    // Editing existing user.
    require_capability('moodle/user:update', $systemcontext);
    $user = $DB->get_record('user', array('id' => $id), '*', MUST_EXIST);
    $PAGE->set_context(context_user::instance($user->id));
    $PAGE->navbar->includesettingsbase = true;
    if ($user->id != $USER->id) {
        $PAGE->navigation->extend_for_user($user);
    } else {
        if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
            $node->force_open();
        }
    }
}

// Remote users cannot be edited.
if ($user->id != -1 and is_mnet_remote_user($user)) {
    redirect($CFG->wwwroot . "/user/view.php?id=$id&course={$course->id}");
}

if ($user->id != $USER->id and is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins.
    throw new \moodle_exception('useradmineditadmin');
}

if (isguestuser($user->id)) { // The real guest user can not be edited.
    throw new \moodle_exception('guestnoeditprofileother');
}

if ($user->deleted) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

// Load user preferences.
useredit_load_preferences($user);

// Load custom profile fields data.
profile_load_data($user);

// User interests.
$user->interests = core_tag_tag::get_item_tags_array('core', 'user', $id);

if ($user->id !== -1) {
    $usercontext = context_user::instance($user->id);
    $editoroptions = array(
        'maxfiles'   => EDITOR_UNLIMITED_FILES,
        'maxbytes'   => $CFG->maxbytes,
        'trusttext'  => false,
        'forcehttps' => false,
        'context'    => $usercontext
    );

    $user = file_prepare_standard_editor($user, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
} else {
    $usercontext = null;
    // This is a new user, we don't want to add files here.
    $editoroptions = array(
        'maxfiles' => 0,
        'maxbytes' => 0,
        'trusttext' => false,
        'forcehttps' => false,
        'context' => $coursecontext
    );
}
// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                             'subdirs'        => 0,
                             'maxfiles'       => 1,
                             'accepted_types' => 'optimised_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;
// Create form.
$userform = new user_editadvanced_form(new moodle_url($PAGE->url, array('returnto' => $returnto)), array(
    'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions,
    'user' => $user));
// Deciding where to send the user back in most cases.
if ($returnto === 'profile') {
    if ($course->id != SITEID) {
        $returnurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
    } else {
        $returnurl = new moodle_url('/user/profile.php', array('id' => $user->id));
    }
} else if ($user->id === -1) {
    $returnurl = new moodle_url("/admin/user.php");
} else {
    $returnurl = new moodle_url('/user/preferences.php', array('userid' => $user->id));
}

if ($userform->is_cancelled()) {
    redirect($returnurl);
} else if ($usernew = $userform->get_data()) {
    $usercreated = false;

    if (empty($usernew->auth)) {
        // User editing self.
        $authplugin = get_auth_plugin($user->auth);
        unset($usernew->auth); // Can not change/remove.
    } else {
        $authplugin = get_auth_plugin($usernew->auth);
    }

    $usernew->timemodified = time();
    $createpassword = false;

    if ($usernew->id == -1) {
        unset($usernew->id);
        $createpassword = !empty($usernew->createpassword);
        unset($usernew->createpassword);
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, null, 'user', 'profile', null);
        $usernew->mnethostid = $CFG->mnet_localhost_id; // Always local user.
        $usernew->confirmed  = 1;
        $usernew->timecreated = time();
        if ($authplugin->is_internal()) {
            if ($createpassword or empty($usernew->newpassword)) {
                $usernew->password = '';
            } else {
                $usernew->password = hash_internal_user_password($usernew->newpassword);
            }
        } else {
            $usernew->password = AUTH_PASSWORD_NOT_CACHED;
        }
        $usernew->id = user_create_user($usernew, false, false);

        if (!$authplugin->is_internal() and $authplugin->can_change_password() and !empty($usernew->newpassword)) {
            if (!$authplugin->user_update_password($usernew, $usernew->newpassword)) {
                // Do not stop here, we need to finish user creation.
                debugging(get_string('cannotupdatepasswordonextauth', 'error', $usernew->auth), DEBUG_NONE);
            }
        }
        // dd($usernew);
        $usercreated = true;
    } else {
        // User editing self.
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
        // Pass a true old $user here.
        if (!$authplugin->user_update($user, $usernew)) {
            // Auth update failed.
            throw new \moodle_exception('cannotupdateuseronexauth', '', '', $user->auth);
        }
        // dd($usernew);
        user_update_user($usernew, false, false);
        // Set new password if specified.
        if (!empty($usernew->newpassword)) {
            if ($authplugin->can_change_password()) {
                if (!$authplugin->user_update_password($usernew, $usernew->newpassword)) {
                    throw new \moodle_exception('cannotupdatepasswordonextauth', '', '', $usernew->auth);
                }
                unset_user_preference('create_password', $usernew); // Prevent cron from generating the password.

                if (!empty($CFG->passwordchangelogout)) {
                    // We can use SID of other user safely here because they are unique,
                    // the problem here is we do not want to logout admin here when changing own password.
                    \core\session\manager::kill_user_sessions($usernew->id, session_id());
                }
                if (!empty($usernew->signoutofotherservices)) {
                    webservice::delete_user_ws_tokens($usernew->id);
                }
            }
        }

        // Force logout if user just suspended.
        if (isset($usernew->suspended) and $usernew->suspended and !$user->suspended) {
            \core\session\manager::kill_user_sessions($user->id);
        }
        // dd($usernew);
    }

    $usercontext = context_user::instance($usernew->id);

    // Update preferences.
    useredit_update_user_preference($usernew);

    // Update tags.
    if (empty($USER->newadminuser) && isset($usernew->interests)) {
        useredit_update_interests($usernew, $usernew->interests);
    }

    // Update user picture.
    if (empty($USER->newadminuser)) {
        core_user::update_picture($usernew, $filemanageroptions);
    }

    // Update mail bounces.
    useredit_update_bounces($user, $usernew);

    // Update forum track preference.
    useredit_update_trackforums($user, $usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);

    // Reload from db.
    $usernew = $DB->get_record('user', array('id' => $usernew->id));

    if ($createpassword) {
        setnew_password_and_mail($usernew);
        unset_user_preference('create_password', $usernew);
        set_user_preference('auth_forcepasswordchange', 1, $usernew);
    }

    // Trigger update/create event, after all fields are stored.
    if ($usercreated) {
        \core\event\user_created::create_from_userid($usernew->id)->trigger();
    } else {
        \core\event\user_updated::create_from_userid($usernew->id)->trigger();
    }

    if ($user->id == $USER->id) {
        // Override old $USER session variable.
        foreach ((array)$usernew as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
        // Preload custom fields.
        profile_load_custom_fields($USER);
        // dd(profile_load_custom_fields($USER))
        if (!empty($USER->newadminuser)) {
            unset($USER->newadminuser);
            // Apply defaults again - some of them might depend on admin user info, backup, roles, etc.
            admin_apply_default_settings(null, false);
            // Admin account is fully configured - set flag here in case the redirect does not work.
            unset_config('adminsetuppending');
            // Redirect to admin/ to continue with installation.
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else if (empty($SITE->fullname)) {
            // Somebody double clicked when editing admin user during install.
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else {
            redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
    } else if ($returnto === 'profile') {
        \core\session\manager::gc(); // Remove stale sessions.
        redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core\session\manager::gc(); // Remove stale sessions.
        redirect("$CFG->wwwroot/$CFG->admin/user.php", get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    // Never reached..
}


// Display page header.
if ($user->id == -1 or ($user->id != $USER->id)) {
    if ($user->id == -1) {
        echo $OUTPUT->header();
        
    } else {
        $streditmyprofile = get_string('editmyprofile');
        $userfullname = fullname($user, true);
        $PAGE->set_heading($userfullname);
        $coursename = $course->id !== SITEID ? "$course->shortname" : '';
        $PAGE->set_title("$streditmyprofile: $userfullname" . moodle_page::TITLE_SEPARATOR . $coursename);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($userfullname);
    }
} 
else if (!empty($USER->newadminuser)) {
    $strinstallation = get_string('installation', 'install');
    $strprimaryadminsetup = get_string('primaryadminsetup');

    $PAGE->navbar->add($strprimaryadminsetup);
    $PAGE->set_title($strinstallation);
    $PAGE->set_heading($strinstallation);
    $PAGE->set_cacheable(false);

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('configintroadmin', 'admin'), 'generalbox boxwidthnormal boxaligncenter');
    echo '<br />';
} else {
    $streditmyprofile = get_string('editmyprofile');
    $strparticipants  = get_string('participants');
    $strnewuser       = get_string('newuser');
    $userfullname     = fullname($user, true);

    $PAGE->set_title("$course->shortname: $streditmyprofile");
    $PAGE->set_heading($userfullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($streditmyprofile);
}

// Finally display THE form.
$userform->display();
$choosedepartment = get_string('choosedepartment','moodle');
$departments = $DB->get_records('department', null, 'name ASC');
echo '
<div id="myModal1" class="modal">
  <div class="modal-content1">
  <div class="modal-header" style="position: relative">
 <h2 style="text-align:center;">'.$choosedepartment.'</h2>
  <input type="text" id="departmentSearch" placeholder="Tìm kiếm phòng ban..."style="margin-right:4rem;width:63%;margin-top:-10px;">
  <span class="close d-flex align-items-center" style="place-content:center">&times;</span>    </div>
   <div style="overflow-y: scroll; max-height: 500px; padding: 0 1rem;">
    <ul class="big">';

foreach ($departments as $dept) {
    if ($dept->parent == "0") {
        echo '<li><details><summary><span class="folder-icon opened closed"></span><a href="#" id = "' .$dept->id . '">' .$dept->name.'</a></summary>';

        foreach ($departments as $subdept) {
            if ($subdept->parent == $dept->id) {
                echo '<ul><li><details><summary><span class="folder-icon opened closed"></span><a href="#" id = "' .$subdept->id . '">' .$subdept->name.'</a></summary>';

                foreach ($departments as $subsubdept) {
                    if ($subsubdept->parent == $subdept->id) {
                        echo '<ul><li><details><summary><span class="folder-icon opened closed"></span><a href="#" id = "' .$subsubdept->id . '">'.$subsubdept->name.'</a></summary></details></li></ul>';
                    }
                }

                echo "</details></li></ul>";
            }
        }

        echo "</details></li>";
    }
}

echo '</ul>
</div>
  </div>
</div>';

echo '<style>

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
.big {
margin-bottom: 10px;
  font-weight: bold;
  cursor: pointer;
  outline: 2px dotted #3744f5;
  margin-top: 5px;
  background: #fafbff;
  color: #110101;
  border-radius: 0.5em;
  padding-bottom: 10px !important;
}
.modal {
  display: none; 
  position: fixed; 
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.5); 
}
.close {
    color: #f8f6f6 !important;
    position: absolute;
    right: 40px;
    top: 20px;
    background: #f50707;
    width: 30px;
    height: 30px;
    text-align: center;
    line-height: 30px;
    border-radius: 50%;
    font-size: 28px;
    font-weight: bold;
}


.modal-content1 {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 100%;
    max-width: 80%;
    border-radius: 0.5em;

}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

li {
  margin-bottom: 10px; 
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
    margin-top:5px;
    background: #e1e6ee;;
    color: #110101;
    border-radius: 0.5em;
   margin:0.4rem;
   height: 50%;
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




#fgroup_id_department_grp .d-flex {
    display: flex;
    align-items: center;
    gap: 10px; 
}

#fgroup_id_department_grp .form-control {
    flex: 1;
}

#fgroup_id_department_grp .btn {
    margin-left: 10px;
}
</style>';


echo '
<script src="/lib/jquery/jquery-3.6.4.min.js"></script>

<script>
$(document).ready(function() {
    var modal = $("#myModal1");
    var btn = $("#id_openModalBtn");
    var span = $(".close");
    var searchInput = $("#departmentSearch");
    var departmentList = modal.find(".big");

    btn.click(function() {
        modal.show();
        var firstDetails = departmentList.find("details").first();
        firstDetails.attr("open", true);
    });

    span.click(function() {
        modal.hide();
    });

    $(window).click(function(event) {
        if ($(event.target).is(modal)) {
            modal.hide();
        }
    });

    // Improved search functionality
    searchInput.on("input", function() {
        var searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === "") {
            // If search input is empty, close all folders and show all items
            departmentList.find("li").show();
            departmentList.find("details").removeAttr("open");
        } else {
            departmentList.find("li").each(function() {
                var $item = $(this);
                var $summary = $item.children("details").children("summary");
                var text = $summary.text().toLowerCase();
                var shouldShow = text.includes(searchTerm);
                
                if (shouldShow) {
                    $item.show();
                    $item.parents("li").show();
                    $item.find("> details").attr("open", true);
                    $item.parents("details").attr("open", true);
                } else {
                    if ($item.find("li:visible").length === 0) {
                        $item.hide();
                    } else {
                        $item.show();
                    }
                }
            });
        }
    });

   

    // Check and set value for the position input before the search button is clicked
    var selectedPositionInput = $("#id_position_data");    // Target the #id_position_data input
    var positionValue = $("#positionInput").val();         // Get the value from #positionInput
    selectedPositionInput.val(positionValue);              // Set the value to the selected position input
    
    // Log values for debugging
    console.log("Selected Position Input (jQuery Object):", selectedPositionInput);  // This logs the entire jQuery object
    console.log("Selected Position Value:", selectedPositionInput.val());           // This logs the value of #id_position_data
    console.log("Original Position Value from #positionInput:", positionValue);     // This logs the value from #positionInput

    // Toggle modal and search button visibility using one event handler
    $(".moreless-toggler").click(function() {
        $("#id_openModalBtn").toggle();  // This will show/hide the modal
        $("#searchButton1").toggle();    // This will show/hide the search button
    });

    // Event handler for department selection in the modal
    $(document).ready(function() {
        // Event handler for department selection in the modal
        var selectedDepartmentInput = $("#id_department");  // Target the #id_department input
        
     
        modal.find("a").click(function(event) {
            event.preventDefault();
    
            // Get the selected department name and ID from the modal links
            var selectedDepartment = $(this).text();  // Get the department name
            var selectedDepartmentId = $(this).attr("id");  // Get the department ID from the link
    
            // Set the department name in the input #id_department
            selectedDepartmentInput.val(selectedDepartment);
            
            // Explicitly set the selected department ID to the hidden input #id_department_id
            $("#id_department_id").val(selectedDepartmentId);
    
            // Log values for debugging
            console.log("Selected Department:", selectedDepartment);
            console.log("Selected Department ID:", selectedDepartmentId);
    
            // Hide the modal after selection
            modal.hide();
        });
    
        // Additional code to log or handle other fields as necessary
    });
    
    
});
</script>';

// And proper footer.
// dd($request);
// dd($user->profile_field_BSRPOS);
echo $OUTPUT->footer();
