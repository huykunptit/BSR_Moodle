<?php

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/filters/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

$delete       = optional_param('delete', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$confirmuser  = optional_param('confirmuser', 0, PARAM_INT);
$sort         = optional_param('sort', 'name', PARAM_ALPHANUMEXT);
$dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 30, PARAM_INT);        // how many per page
$ru           = optional_param('ru', '2', PARAM_INT);            // show remote users
$lu           = optional_param('lu', '2', PARAM_INT);            // show local users
$acl          = optional_param('acl', '0', PARAM_INT);           // id of user to tweak mnet ACL (requires $access)
$suspend      = optional_param('suspend', 0, PARAM_INT);
$unsuspend    = optional_param('unsuspend', 0, PARAM_INT);
$unlock       = optional_param('unlock', 0, PARAM_INT);
$resendemail  = optional_param('resendemail', 0, PARAM_INT);

admin_externalpage_setup('editusers');
global $CFG, $DB, $OUTPUT, $USER;
$sitecontext = context_system::instance();
$site = get_site();

if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
    throw new \moodle_exception('nopermissions', 'error', '', 'edit/delete users');
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');
$strdeletecheck = get_string('deletecheck');
$strshowallusers = get_string('showallusers');
$strsuspend = get_string('suspenduser', 'admin');
$strunsuspend = get_string('unsuspenduser', 'admin');
$strunlock = get_string('unlockaccount', 'admin');
$strconfirm = get_string('confirm');
$strresendemail = get_string('resendemail');

$returnurl = new moodle_url('/admin/user.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'page'=>$page));

$PAGE->set_primary_active_tab('siteadminnode');
$PAGE->navbar->add(get_string('userlist', 'admin'), $PAGE->url);

// The $user variable is also used outside of these if statements.
$user = null;
if ($confirmuser and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);
    if (!$user = $DB->get_record('user', array('id'=>$confirmuser, 'mnethostid'=>$CFG->mnet_localhost_id))) {
        throw new \moodle_exception('nousers');
    }

    $auth = get_auth_plugin($user->auth);

    $result = $auth->user_confirm($user->username, $user->secret);

    if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
        redirect($returnurl);
    } else {
        echo $OUTPUT->header();
        redirect($returnurl, get_string('usernotconfirmed', '', fullname($user, true)));
    }

} else if ($resendemail && confirm_sesskey()) {
    if (!$user = $DB->get_record('user', ['id' => $resendemail, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0])) {
        throw new \moodle_exception('nousers');
    }

    // Prevent spamming users who are already confirmed.
    if ($user->confirmed) {
        throw new \moodle_exception('alreadyconfirmed', 'moodle');
    }

    $returnmsg = get_string('emailconfirmsentsuccess');
    $messagetype = \core\output\notification::NOTIFY_SUCCESS;
    if (!send_confirmation_email($user)) {
        $returnmsg = get_string('emailconfirmsentfailure');
        $messagetype = \core\output\notification::NOTIFY_ERROR;
    }

    redirect($returnurl, $returnmsg, null, $messagetype);
} else if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation
    require_capability('moodle/user:delete', $sitecontext);

    $user = $DB->get_record('user', array('id'=>$delete, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);
    if ($user->deleted) {
        throw new \moodle_exception('usernotdeleteddeleted', 'error');
    }
    if (is_siteadmin($user->id)) {
        throw new \moodle_exception('useradminodelete', 'error');
    }

    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        $fullname = fullname($user, true);
        echo $OUTPUT->heading(get_string('deleteuser', 'admin'));

        $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());
        $deleteurl = new moodle_url($returnurl, $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $returnurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {

        if (delete_user($user)) {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($returnurl);

        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            echo $OUTPUT->header();
            echo $OUTPUT->notification($returnurl, get_string('deletednot', '', fullname($user, true)));
        }
    }
} else if ($acl and confirm_sesskey()) {
    if (!has_capability('moodle/user:update', $sitecontext)) {
        throw new \moodle_exception('nopermissions', 'error', '', 'modify the NMET access control list');
    }
    if (!$user = $DB->get_record('user', array('id'=>$acl))) {
        throw new \moodle_exception('nousers', 'error');
    }
    if (!is_mnet_remote_user($user)) {
        throw new \moodle_exception('usermustbemnet', 'error');
    }
    $accessctrl = strtolower(required_param('accessctrl', PARAM_ALPHA));
    if ($accessctrl != 'allow' and $accessctrl != 'deny') {
        throw new \moodle_exception('invalidaccessparameter', 'error');
    }

    $aclrecord = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid));

    if (empty($aclrecord)) {
        $aclrecord = new stdClass();
        $aclrecord->mnet_host_id = $user->mnethostid;
        $aclrecord->username = $user->username;
        $aclrecord->accessctrl = $accessctrl;
        $DB->insert_record('mnet_sso_access_control', $aclrecord);
    } else {
        $aclrecord->accessctrl = $accessctrl;
        $DB->update_record('mnet_sso_access_control', $aclrecord);
    }
    $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
    redirect($returnurl);

} else if ($suspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);
    $userInfoFields = $DB->get_records('user_info_field', null, 'shortname ASC');
  
    
    if (isset($_POST['confirm_suspension'])) {
        try{
            $user_select = ($_POST['suspension_reason'] == 'other') ? $_POST['otherreason'] : $_POST['suspension_reason'];
        
            if ($user = $DB->get_record('user', array('id'=>$suspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
                
                if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
                    $user->suspended = 1;
                    
                    // Force logout.
                    \core\session\manager::kill_user_sessions($user->id);
                    user_update_user($user, false);
                    
                    // Lưu lý do đình chỉ
                    $user_id = $user->id;
                    
                    $fieldid = 4;
                    $existing_record = $DB->get_record('user_info_data', ['userid' => $user_id, 'fieldid' => $fieldid]);
                    if ($existing_record) {
                  
                        $existing_record->data = $user_select;
                        
                        $DB->update_record('user_info_data', $existing_record);
                        
                    } else {
            
                        $new_record = new stdClass();
                        $new_record->userid = $user_id;
                        $new_record->fieldid = $fieldid;
                        $new_record->data = $user_select;
                        $DB->insert_record('user_info_data', $new_record);
                    }
        
                   
                    redirect(new moodle_url('/admin/user.php'), 'Người dùng đã bị đình chỉ', null, \core\output\notification::NOTIFY_SUCCESS);
                }
            }
        }
        catch(Exception $e){ 
            // dd($e);
        }
       
    } else {
        // Hiển thị modal chỉ khi form chưa được submit
        echo '<div id="suspensionModal" class="modal" style="display:block;">'; 
        echo '<div class="modal-content">';
        echo '<span class="close text-white">&times;</span>';
        echo '<h2>Chọn lý do đình chỉ</h2>';
        echo '<form method="post" action="">';
        echo '<select class="w-100 my-4" name="suspension_reason">';
        
        foreach ($userInfoFields as $field) {
            if ($field->id == 4) {
                $options = explode("\n", trim($field->param1));
                foreach ($options as $option) {
                    if (trim($option) == 'Khác...') {
                        echo '<option value="other">Khác...</option>';
                    } else {
                        echo '<option value="' . trim($option) . '">' . trim($option) . '</option>';
                    }
                }
            }
        }
        
        echo '</select>';
        echo '<input type="text" name="otherreason" style="display:none;">';
        echo '<input type="hidden" name="suspend" value="'.$suspend.'">';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'">';
        echo '<input class="btn btn-secondary d-block float-right " type="submit" name="confirm_suspension" value="Xác nhận">';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("suspensionModal");
            var span = document.getElementsByClassName("close")[0];
            var select = document.querySelector("select[name=\'suspension_reason\']");
            var otherReason = document.querySelector("input[name=\'otherreason\']");
            
            span.onclick = function() {
                modal.style.display = "none";
                
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            select.addEventListener("change", function() {
                if (this.value === "other") {
                    otherReason.style.display = "block";
                } else {
                    otherReason.style.display = "none";
                }
            });
        });
        </script>';
        
        echo '<style>
        .modal {
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
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
        </style>';
    }


} else if ($unsuspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id'=>$unsuspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
        if ($user->suspended != 0) {
            $user->suspended = 0;
            user_update_user($user, false);
        }
    }
    redirect($returnurl);

} else if ($unlock and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id'=>$unlock, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
        login_unlock_account($user);
    }
    redirect($returnurl);
}

// create the user filter form
$ufiltering = new user_filtering();
echo $OUTPUT->header();

// Carry on with the user listing
$context = context_system::instance();

// These columns are always shown in the users list.
$avatarcolumn = array('avatar');
$requiredcolumns = array('lastaccess','phone1','department','institution','idnumber');
// Extra columns containing the extra user fields, excluding the required columns (city and country, to be specific).
$userfields = \core_user\fields::for_identity($context, true)->excluding(...$requiredcolumns);
// dd($userfields);
$extracolumns = $userfields->get_required_fields();
// dd($extracolumns);
// Get all user name fields as an array, but with firstname and lastname first.
$allusernamefields = \core_user\fields::get_name_fields(true);
$columns = array_merge($avatarcolumn,$allusernamefields, $extracolumns, $requiredcolumns);

foreach ($columns as $column) {
    $string[$column] = \core_user\fields::get_display_name($column);

    if ($sort != $column) {
        $columnicon = "";
        if ($column == "lastaccess") {
            $columndir = "DESC";
        } else {
            $columndir = "ASC";
        }
    } else {
        $columndir = $dir == "ASC" ? "DESC":"ASC";
        if ($column == "lastaccess") {
            $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
        } else {
            $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
        }
        $columnicon = $OUTPUT->pix_icon('t/' . $columnicon, get_string(strtolower($columndir)), 'core',
            ['class' => 'iconsort']);

    }
    $$column = "<a href=\"user.php?sort=$column&amp;dir=$columndir\">".$string[$column]."</a>$columnicon";
}




// We need to check that alternativefullnameformat is not set to '' or language.
// We don't need to check the fullnamedisplay setting here as the fullname function call further down has
// the override parameter set to true.
$fullnamesetting = $CFG->alternativefullnameformat;
// If we are using language or it is empty, then retrieve the default user names of just 'firstname' and 'lastname'.
if ($fullnamesetting == 'language' || empty($fullnamesetting)) {
    // Set $a variables to return 'firstname' and 'lastname'.
    $a = new stdClass();
    $a->firstname = 'firstname';
    $a->lastname = 'lastname';
    // Getting the fullname display will ensure that the order in the language file is maintained.
    $fullnamesetting = get_string('fullnamedisplay', null, $a);
}

// Order in string will ensure that the name columns are in the correct order.
$usernames = order_in_string($allusernamefields, $fullnamesetting);
$department =  get_string('department','moodle');
$position1 =  get_string('position', 'filters');
$idnumber =  get_string('idnumber','moodle');

$fullnamedisplay = array();
foreach ($usernames as $name) {
    // Use the link from $$column for sorting on the user's name.
    $fullnamedisplay[] = ${$name};
}
// All of the names are in one column. Put them into a string and separate them with a /.
$fullnamedisplay = implode(' / ', $fullnamedisplay);
// If $sort = name then it is the default for the setting and we should use the first name to sort by.
if ($sort == "name") {
    // Use the first item in the array.
    $sort = reset($usernames);
}

list($extrasql, $params) = $ufiltering->get_sql_filter();
$users = get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '',
    $extrasql, $params, $context);
$usercount = get_users(false);
$usersearchcount = get_users(false, '', false, null, "", '', '', '', '', '*', $extrasql, $params);

if ($extrasql !== '') {
    echo $OUTPUT->heading("$usersearchcount / $usercount ".get_string('users'));
    $usercount = $usersearchcount;
} else {
    echo $OUTPUT->heading("$usercount ".get_string('users'));
}

$strall = get_string('all');

$baseurl = new moodle_url('/admin/user.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
// dd(($usercount));
flush();




if (!$users) {
    $match = array();
    echo $OUTPUT->heading(get_string('nousersfound'));

    $table = NULL;

} else {

    $countries = get_string_manager()->get_list_of_countries(true);
    if (empty($mnethosts)) {
        $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
    }

    foreach ($users as $key => $user) {
        if (isset($countries[$user->country])) {
            $users[$key]->country = $countries[$user->country];
        }
    }
    if ($sort == "country") {
        // Need to resort by full country name, not code.
        foreach ($users as $user) {
            $susers[$user->id] = $user->country;
        }
        // Sort by country name, according to $dir.
        if ($dir === 'DESC') {
            arsort($susers);
        } else {
            asort($susers);
        }
        foreach ($susers as $key => $value) {
            $nusers[] = $users[$key];
        }
        $users = $nusers;
    }

    $table = new html_table();
    $table->head = array ();
    $table->colclasses = array();
    $table->head[] = 'Avatar';
    $table->head[] = $idnumber;
    $table->head[] = $fullnamedisplay;
   
    $table->attributes['class'] = 'admintable generaltable table-sm';
    foreach ($extracolumns as $field) {
      
        $table->head[] = ${$field};
    }
    $table->head[] = $position1;
    $table->head[] = $department;
    $table->head[] = get_string('team','moodle');
    
    // $table->head[] = $city;
    // $table->head[] = $country;
    $table->head[] = $lastaccess;
    $table->head[] = get_string('edit');
    // $table->colclasses[] = 'centeralign';
    // $table->head[] = "";
    // $table->colclasses[] = 'centeralign';

    $table->id = "users";
    
    $table->data = array();
    foreach ($users as $user) {
        $buttons = array();
        $fulluserdata = $DB->get_record('user', array('id' => $user->id));
        // Check if user data exists
        if ($fulluserdata) {
            // Update department
            $fulluserdata->department = company_user::get_department_name($user->id);

        } else {
            // Handle the case where user data is not found
            // You might want to log this or handle it differently based on your requirements
            echo "User data not found for user ID: " . $user->id;
        }

        $lastcolumn = '';

        // delete button
        if (has_capability('moodle/user:delete', $sitecontext)) {
            if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
                // no deleting of self, mnet accounts or admins allowed
            } else {
                $url = new moodle_url($returnurl, array('delete'=>$user->id, 'sesskey'=>sesskey()));
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
            }
        }

        // suspend button
        if (has_capability('moodle/user:update', $sitecontext)) {
            if (is_mnet_remote_user($user)) {
                // mnet users have special access control, they can not be deleted the standard way or suspended
                $accessctrl = 'allow';
                if ($acl = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid))) {
                    $accessctrl = $acl->accessctrl;
                }
                $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
                $buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=".sesskey()."\">".get_string($changeaccessto, 'mnet') . " access</a>)";

            } else {
                if ($user->suspended) {
                    $url = new moodle_url($returnurl, array('unsuspend'=>$user->id, 'sesskey'=>sesskey()));
                    $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/show', $strunsuspend));
                } else {
                    if ($user->id == $USER->id or is_siteadmin($user)) {
                        // no suspending of admins or self!
                    } else {
                        $url = new moodle_url($returnurl, array('suspend'=>$user->id, 'sesskey'=>sesskey()));
                        $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/hide', $strsuspend));
                    }
                }

                if (login_is_lockedout($user)) {
                    $url = new moodle_url($returnurl, array('unlock'=>$user->id, 'sesskey'=>sesskey()));
                    $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/unlock', $strunlock));
                }
            }
        }

        // edit button
        if (has_capability('moodle/user:update', $sitecontext)) {
            // prevent editing of admins by non-admins
            if (is_siteadmin($USER) or !is_siteadmin($user)) {
                $url = new moodle_url('/user/editadvanced.php', array('id'=>$user->id, 'course'=>$site->id));
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit));
            }
        }

        // the last column - confirm or mnet info
        if (is_mnet_remote_user($user)) {
            // all mnet users are confirmed, let's print just the name of the host there
            if (isset($mnethosts[$user->mnethostid])) {
                $lastcolumn = get_string($accessctrl, 'mnet').': '.$mnethosts[$user->mnethostid]->name;
            } else {
                $lastcolumn = get_string($accessctrl, 'mnet');
            }

        } else if ($user->confirmed == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
                $lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser'=>$user->id, 'sesskey'=>sesskey())), $strconfirm);
            } else {
                $lastcolumn = "<span class=\"dimmed_text\">".get_string('confirm')."</span>";
            }

            $lastcolumn .= ' | ' . html_writer::link(new moodle_url($returnurl,
                    [
                        'resendemail' => $user->id,
                        'sesskey' => sesskey()
                    ]
                ), $strresendemail);
        }

        if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
        } else {
            $strlastaccess = get_string('never');
        }
        $fullname = fullname($user, true);
        $departments = $DB->get_records('department');
        //dd($departments);
        $row = array ();
        $row[] = $OUTPUT->user_picture($user, array('courseid'=>$site->id));
        $idnumber = $DB->get_field('user', 'idnumber', ['id' => $user->id]);
        $row[] = $idnumber;

        $row[] = "<a href=\"../user/view.php?id=$user->id&amp;course=$site->id\">$fullname</a>";
        foreach ($extracolumns as $field) {

            $row[] = s($user->{$field});
        }

        $job_details = $DB->get_field('user_info_data', 'data', ['userid' => $user->id,'fieldid' => 1]);

        // Assume $user->department contains the department ID
        $row[] = $job_details;
        // Assume $user->id contains the user ID
        if($departments[company_user::get_department_parent_id($user->id)]->id <= 2) {
            $row[] = $fulluserdata->department;
            $row[] = '';

        }
        else {
            $row[] = $departments[company_user::get_department_parent_id($user->id)]->name;
            $row[] = $fulluserdata->department;
        }
        





        // $row[] = $user->city;
        // $row[] = $user->country;
        $row[] = $strlastaccess;
        if ($user->suspended) {
            foreach ($row as $k=>$v) {
                $row[$k] = html_writer::tag('span', $v, array('class'=>'usersuspended'));
            }
        }
        $row[] = implode(' ', $buttons);
        // $row[] = $lastcolumn;
        //dd($row);
        $table->data[] = $row;
    }
}

   

// add filters
$ufiltering->display_add();
$ufiltering->display_active();
$jsonUfiltering = json_encode($ufiltering);
$urlencode = urlencode($jsonUfiltering);
//$urlJsonUfiltering = urlencode($jsonUfiltering);
//dd($urlencode);
$url = 'user/user_bulk_download.php?sesskey='.sesskey().'&dataformat=csv'.'&ufiltering='.$urlencode;
//dd($ufiltering);
echo '<div class="exportbtn" style="display: inline-block; margin-right: 10px;">' 
     . $OUTPUT->single_button($url, get_string('download'), 'post', array('title'=>get_string('export'))) 
     . '</div>';

if (has_capability('moodle/user:create', $sitecontext)) {
    $url = new moodle_url('/user/editadvanced.php', array('id' => -1));
    echo '<div style="display: inline-block;">' 
         . $OUTPUT->single_button($url, get_string('addnewuser'), 'get') 
         . '</div>';
}
$showresult = get_string('showlength','moodle');
echo '  <div class = "showlenght" style = "display: inline-block;float:right;">
        <label for="perpage-select">'.$showresult.'</label>
        <select id="perpage-select" onchange="navigateToURL(this)">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="'.$usercount.'">All</option>
        </select>
        </div>
        <script>
                function navigateToURL(selectElement) {
                    const value = selectElement.value;
                    const baseURL = "/admin/user.php?perpage=";
                    const url = baseURL + value;
                    window.location.href = url;
                }

                function setSelectedOption() {
                    const params = new URLSearchParams(window.location.search);
                    const selectedValue = params.get("perpage");
                    if (selectedValue) {
                        const selectElement = document.getElementById("perpage-select");
                        selectElement.value = selectedValue;
                    }
                }

                window.onload = setSelectedOption;
        </script>
';


if (!empty($table)) {
    echo html_writer::start_tag('div', array('class'=>'no-overflow'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}
// if (has_capability('moodle/user:create', $sitecontext)) {
//     $url = new moodle_url('/user/editadvanced.php', array('id' => -1));
//     echo $OUTPUT->single_button($url, get_string('addnewuser'), 'get');
// }
$departments = $DB->get_records('department', null, 'name ASC');
$choosedepartment = get_string('choosedepartment','moodle');
// dd($departments);
echo '
<div id="myModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 style="text-align:center;">'.$choosedepartment.'</h2>
      <input type="text" id="departmentSearch" placeholder="Tìm kiếm phòng ban..." style="margin-right:4rem;width:63%;margin-top:-10px;">
      <span class="close d-flex align-items-center" style="place-content:center">&times;</span>
    </div>
    <div style="overflow-y: scroll; max-height: 60%; padding: 0 1rem;">
      <ul class="big">';
foreach ($departments as $dept) {
    if ($dept->parent == "0") {
        echo '<li><details';
        if ($dept === reset($departments)) {
            echo ' open'; // Automatically open the first department
        }
        echo '><summary><span class="folder-icon opened closed"></span><a href="#">'.$dept->name.'</a></summary>';
        
        foreach ($departments as $subdept) {
            if ($subdept->parent == $dept->id) {
                echo '<ul><li><details><summary><span class="folder-icon opened closed"></span><a href="#">'.$subdept->name.'</a></summary>';

                // foreach ($departments as $subsubdept) {
                    
                //     if ($subsubdept->parent == $subdept->id) {
                //         echo '<ul><li><details><summary><span class="folder-icon opened closed"></span><a href="#">'.$subsubdept->name.'</a></summary></details></li></ul>';
                //     }
                // }

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
    border-bottom: 2px dotted #055091;
  }

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
    color: #f8f6f6;
    position: absolute;
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
    display: flex;
    place-content: center;
    align-items: center;
  }

  .modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 60% !important;
    max-height: 60%;
    overflow-y: auto;
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
    margin-top: 5px;
    background: #e1e6ee;
    color: #110101;
    border-radius: 0.5em;
    height: 50%;
  }

  details summary::marker {
    content: "";
  }

  details > summary::before {
    background: #1e4fa5;
    content: "-";
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
    content: "-";
  }
</style>';

echo '<script>
var modal = document.getElementById("myModal");
var btn = document.getElementById("id_openModalBtn");
var span = document.getElementsByClassName("close")[0];

btn.onclick = function() {
  modal.style.display = "block";

  // Automatically expand the first <details> element
  var firstDetails = modal.querySelector("details");
  if (firstDetails) {
    firstDetails.setAttribute("open", "");
  }
}

span.onclick = function() {
  modal.style.display = "none";
}

window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

document.addEventListener("DOMContentLoaded", function() {
    // var toggleButton = document.querySelector(".moreless-toggler");
    var targetButton = document.querySelector("#id_openModalBtn");

    toggleButton.addEventListener("click", function() {
        if (targetButton.style.display === "none") {
            targetButton.style.display = "inline-block";
        } else {
            targetButton.style.display = "none";
        }
    });
});

// Select the table with the specific classes
var table = document.querySelector(".admintable.generaltable.table-sm");

// Check if the table exists to avoid errors
if (table) {
    // Select all elements within the table that have both "header" and "c2" classes
    var elements = table.querySelectorAll(".header.c2");

    // Loop through each of these elements and add the "col-2" class
    elements.forEach(function(element) {
        element.style.width = "10%";
    });
}


document.addEventListener("DOMContentLoaded", function() {
    var selectedDepartmentInput = document.getElementById("selectedDepartment");
    var modal = document.getElementById("myModal");
    var treeLinks = modal.querySelectorAll("a");
    treeLinks.forEach(function(link) {
        link.addEventListener("click", function() {
            var selectedValue = this.innerText;
            event.preventDefault();
            selectedDepartmentInput.value = selectedValue;
            modal.style.display = "none";
        });
    });

    var closeButton = modal.querySelector(".close");
    closeButton.addEventListener("click", function() {
        modal.style.display = "none";
    });
});
document.addEventListener("DOMContentLoaded", function() {
    var searchInput = document.getElementById("departmentSearch");
    var allItems = document.querySelectorAll(".modal-content details");

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
</script>';

$positionsString = $DB->get_field('user_info_field', 'param1', array('shortname' => 'BSRPOS'),'null','name ASC');

$positions = array_map('trim', explode("\n", $positionsString));
$searchposition = get_string('searchposition','moodle');
sort($positions);
echo '
<div id="positionModal1">
    <div class="modal-content">
        <span id="closeModal">&times;</span>
        <h2>'.$searchposition.'</h2>
        <input type="text" id="searchInput1" placeholder="Tìm kiếm..." onkeyup="filterList()" style="width: 100%; padding: 5px; margin-bottom: 10px;">
        <hr>
        <ul id="positionsList">';
       
        foreach ($positions as $position) {
    
    if (!empty($position)) {
        echo '<li onclick="selectPosition1(\'' . $position . '\')">' . $position . '</li>';
    }
}
echo '
        </ul>
    </div>
</div>
';
echo '
<style>
        #positionModal1 {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        #positionModal1 .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }
        #closeModal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        #positionsList {
            list-style: none;
            padding: 0;
            height: 50%;
            overflow-y: scroll;
        }
        #positionsList li {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }
        #positionsList li:hover {
            background-color: #1e4fa5;
            color: white;
        }
        fieldset#id_category_1 {
            display: none;
        }
        input#id_profile {
            width: 400px;
        }
        #closeModal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 28px;
            font-weight: bold;
            color: white;
            background-color: red;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
    
        #closeModal:hover {
            background-color: darkred;
        }
    </style>
<script src="/lib/jquery/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function() {
    $("#searchButton1").click(function() {
        $("#positionModal1").show();
    });

    $("#closeModal").click(function() {
        $("#positionModal1").hide();
    });

    $(window).click(function(event) {
        if (event.target == document.getElementById("positionModal1")) {
            $("#positionModal1").hide();
        }
    });
});

function filterList() {
    let input = document.getElementById("searchInput1").value.trim().toLowerCase();
    let ul = document.getElementById("positionsList");
    let li = ul.getElementsByTagName("li");
    for (let i = 0; i < li.length; i++) {
        let text = (li[i].textContent || li[i].innerText).trim().toLowerCase();
        if (text.indexOf(input) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}

function selectPosition1(position) {
    document.getElementById("id_profile").value = position;
    document.getElementById("positionModal1").style.display = "none";
}
</script>

';


echo $OUTPUT->footer();
