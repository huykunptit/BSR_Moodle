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
//R
$sitecontext = context_system::instance();
$site = get_site();
echo '
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="/lib/jquery/jquery-3.6.4.min.js"></script>
';
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

    if ($user = $DB->get_record('user', array('id'=>$suspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
        if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
            $user->suspended = 1;
            // Force logout.
            \core\session\manager::kill_user_sessions($user->id);
            user_update_user($user, false);
        }
    }
    redirect($returnurl);

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
$requiredcolumns = array('city', 'country', 'lastaccess');
// Extra columns containing the extra user fields, excluding the required columns (city and country, to be specific).
$userfields = \core_user\fields::for_identity($context, true)->excluding(...$requiredcolumns);
$extracolumns = $userfields->get_required_fields();
// Get all user name fields as an array, but with firstname and lastname first.
$allusernamefields = \core_user\fields::get_name_fields(true);
$columns = array_merge($avatarcolumn,$allusernamefields, $extracolumns, $requiredcolumns);

foreach ($columns as $column) {
    $string[$column] = \core_user\fields::get_display_name($column);
//        dd($string[$column]);
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
    $table->head[] = $avatar;
    $table->head[] = 'Maid';
    $table->head[] = $fullnamedisplay;
    $table->attributes['class'] = 'admintable generaltable table-sm';
    foreach ($extracolumns as $field) {
        $table->head[] = ${$field};
    }

    $table->head[] = 'Phongban';
    $table->head[] = 'Chucdanh';
    $table->head[] = 'Bo phan';
    $table->head[] = $city;
    $table->head[] = $country;
    $table->head[] = $lastaccess;
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign';
    $table->head[] = "";
    $table->colclasses[] = 'centeralign';

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
//            dd($user->department);
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

        $row = array ();
        $row[] = $OUTPUT->user_picture($user, array('courseid'=>$site->id));
        $idnumber = $DB->get_field('user', 'idnumber', ['id' => $user->id]);
        $row[] = $idnumber;

        $row[] = "<a href=\"../user/view.php?id=$user->id&amp;course=$site->id\">$fullname</a>";
        foreach ($extracolumns as $field) {

            $row[] = s($user->{$field});
        }
//            Phong ban
        // Assume $user->department contains the department ID
        $department_name = $DB->get_field('user', 'department', ['id' => $user->id]);
        $row[] = $department_name;
//            Chuc danh
        $job_details = $DB->get_field('user_info_data', 'data', ['userid' => $user->id,'fieldid' => 1]);

        // Assume $user->department contains the department ID
        $row[] = $job_details;
        // Assume $user->id contains the user ID

        $row[] = $fulluserdata->department;





        $row[] = $user->city;
        $row[] = $user->country;
        $row[] = $strlastaccess;
        if ($user->suspended) {
            foreach ($row as $k=>$v) {
                $row[$k] = html_writer::tag('span', $v, array('class'=>'usersuspended'));
            }
        }
        $row[] = implode(' ', $buttons);
        $row[] = $lastcolumn;
        $table->data[] = $row;
    }
}

// add filters
$ufiltering->display_add();
$ufiltering->display_active();

if (!empty($table)) {
    echo html_writer::start_tag('div', array('class'=>'no-overflow'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}
if (has_capability('moodle/user:create', $sitecontext)) {
    $url = new moodle_url('/user/editadvanced.php', array('id' => -1));
    echo $OUTPUT->single_button($url, get_string('addnewuser'), 'get');
}

echo $OUTPUT->footer;




try {
    // Lấy tất cả các bản ghi từ bảng 'department'
    $departments = $DB->get_records('department', null, 'name ASC');
//    $departmentTree = new MyClass();
    $departmentTree = buildTree($departments);

} catch (dml_exception $e) {

}

echo '<input type="text" id="selectedDepartment1" readonly>';
echo'<div id = "showtext"> </div>';
echo '
<!-- Modal -->

<div class="modal fade" id="treeModal" tabindex="-1" role="dialog" aria-labelledby="treeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="display: flex; place-content:center">
    <div class="modal-content col-lg-7 p-0">
      <div class="modal-header">
        <h5 class="modal-title" id="treeModalLabel">Chọn phòng / ban</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="background:white;">
        <div id="treeContainer">';
echo displayTree($departmentTree);

echo '<style>

.tree-container ul>li>a {
    color: black;
    transition: color 0.3s ease;
}

.tree-container ul>li>a:hover {
    /* Chuyển sang màu xanh khi hover */
    color: blue;
}
.tree-container ul{
  display:inline-block; 0position: relative; float: left; clear: left;
	margin:.15em;
	padding:0;

}
.tree-container ul:before{
	content:""; position: absolute; z-index: 1;
	top:.25em; right:auto; bottom:0; left: 1.75em; 
	margin: auto;
	border-right: dotted black .1em;
	width: 0; height: auto;

}
.tree-container ul:after{
	content: "-"; position: absolute; z-index: 3;
	top:0; left:-.5em;
	margin:.65em; padding:0;
	width:.8em; height: .8em; 
	text-align:center; line-height: .6em; font-size: 1em;

}
.tree-container ul>li{
	display: block; position: relative; float: left; clear: both;
	right:auto;
	padding-left: 1em;
	width:auto;
	text-align: center; 

}
.tree-container ul>li>input{
	display:block; position: absolute; float: left; z-index: 4;
	margin:0 0 0 -1em; padding:0;
	width:1em; height: 2em;
	font-size: 1em;
	opacity: 0;
	cursor: pointer;
}
.tree-container ul>li>input:checked~ul:before{
	display: none;
}
.tree-container ul>li>input:checked~ul:after{
	content: "+"
}
.tree-container ul>li>input:checked~ul *{
	display: none;
}
.tree-container ul>li>a{
	display: block; position: relative; float: left; z-index: 3;
	margin:.25em; padding:.25em;

}
.tree-container ul>li>a:after{
	content: ""; display: block; position: absolute;
	left:-1em; top:0; bottom:0;
	margin: auto .25em auto .25em;
	border-top: dotted black .1em;
	width: .75em; height: 0;
	
}

.tree-container ul>li:last-child:before{
	content: ""; display: block; position: absolute; z-index: 2;
	top:1em; left:0; bottom:-.25em;
	width:.75em; height:auto;

}

#tree{
	position: relative; font-family: "Georgia"; 
}
#tree:before{
	left:.5em;
}
#tree:after{
	display: none;
}

/*decoration*/
//.tree-container ul,ul>li:last-child:before{
//	background: white;
//}
.tree-container ul>li{
	background: transparent;
}
.tree-container ul:after{
	
	color: black;
	border:solid gray 1px;
	border-radius: .1em;
}
.tree-container ul>li>a{	
	border-radius: .25em;
	color: black;

}
.tree-container ul>li>input~a:before{
	content:""; display: inline-block;
	margin: 0 .25em  0 0;
	width:1em; height: 1em; ;line-height: 1em;
	background: url("data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAAAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAD///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAAABwAAABcAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABcAAAAPAAAACQNMcIsDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDTHCTAAAAHwAAABEDbqSpb8Hn/4bQ7/+Fz+7/hc/u/4XP7v+Fz+7/hc/u/4XP7v+Fz+7/hc/u/4XP7v+H0PD/OpfDyQJcikX///8BA3i0m1e13/+N1vH/h9Hu/4fR7v+H0e7/h9Hu/4fR7v+H0e7/h9Hu/4fR7v+H0e7/iNHu/3jJ6e0CgL97////AQJ8uZdGrdz/l9/2/5Tb9P+U2/T/lNv0/5Tb9P+U2/T/lNv0/5Tb9P+U2/T/lNv0/5Tb9P+b4fj/JpvTnQKGyCMCf76TYL7n/4XX8v+h5vr/oOX6/6Dl+v+g5fr/oOX6/6Lo+v+l6vv/per7/6Xq+/+l6vv/puz7/2vJ68kCis9VAoLCj33P8P9px+z/r/P//63x/v+t8f7/rfH+/63x/v+Y5fj/SbLj/0my4/9JsuP/Tbbl/wKKz8UCjtZ9Ao7WfQKFx4uW3vb/Trjn/0645/9OuOf/Trjn/0645/9OuOf/Trjn/4HR8P+S2vP/ktrz/5ng9v8ChceL////Af///wECiMuHn+X5/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+f5fn/AojLh////wH///8BAorPg6Pp+/+d4/n/neP5/53j+f+d4/n/neP5/53j+f+d4/n/neP5/53j+f+d4/n/o+n7/wKKz4P///8B////AQKN0oGo7f3/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+i5/v/ouf7/6jt/f8CjdKB////Af///wECj9Z9rvP//6vw/v+r8P7/q/D+/6vw/v+r8P7/q/D+/6vw/v+r8P7/q/D+/6vw/v+u8///Ao/Wff///wH///8BApHZXQKR2XsCkdl7ApHZewKR2XsCkdl7ApHZe/7+/f/19e7/6+vd//7JQf/0ti7/ApHZewKR2V3///8B////Af///wH///8B////Af///wH///8B////AQKS2ysCktt5ApLbeQKS23kCktt5ApLbeQKS2yv///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//w==");
	background-repeat:no-repeat;
	background-size:contain;
}
.tree-container ul>li>input:checked~a:before{
	background-image: url("data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAAAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAD///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAAABwAAABcAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAbAAAAGwAAABsAAAAXAAAABwNMcIsDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNklLcDZJS3A2SUtwNMcIsDbqSphs/w/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4LL7f+Cy+3/gsvt/4bP8P8DbqSpA3i0m4bP7v99yOj/fcjo/33I6P99yOj/fcjo/33I6P99yOj/fcjo/33I6P99yOj/fcjo/33I6P+Gz+7/A3i0mwJ8uZeK0/D/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/gszr/4LM6/+CzOv/itPw/wJ8uZcCf76Tj9fy/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4fQ7f+H0O3/h9Dt/4/X8v8Cf76TAoLCj5Tb9P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+N1fD/jdXw/43V8P+U2/T/AoLCjwKFx4uZ4Pb/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/ktrz/5La8/+S2vP/meD2/wKFx4sCiMuHn+X5/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5jf9v+Y3/b/mN/2/5/l+f8CiMuHAorPg6Pp+/+d4/n/neP5/53j+f+d4/n/neP5/53j+f+j6fr/o+n6/6Pp+v+j6fr/o+n6/6Pp+v+m7Pv/AorPgwKN0oGo7f3/ouf7/6Ln+/+i5/v/ouf7/6Ln+/+r8P3/jNDt/4HF5/+Bxef/gcXn/4HF5/+Bxef/gcXn/wKN0oECj9Z9rvP//6vw/v+r8P7/q/D+/6vw/v+u8///j9Pv/4/T7/+r8P7/q/D+/6vw/v+r8P7/q/D+/67z//8Cj9Z9ApHZXQKR2XsCkdl7ApHZewKR2XsCkdl7ApHZewKR2Xv+/v3/+Pjz//Dw5v/p6dv//slB//S2Lv8Ckdl7ApHZXf///wH///8B////Af///wH///8B////Af///wECktsrApLbeQKS23kCktt5ApLbeQKS23kCktt5ApLbK////wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8B////Af///wH///8BAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//w==");
}
</style>';

echo '
<script>

$(document).ready(function() {
    $("#treeModal .tree-container a").click(function() {
        var selectedDepartment = $(this).text();
         $("#selectedDepartment1").val(selectedDepartment);
        $("#selectedDepartment").val(selectedDepartment);
         
        $("#treeModal").modal("hide");
         
        
    });
});

</script>
';

function buildTree($departments, $parent_id = 0): array
{
    $branch = [];
    foreach ($departments as $department) {
        if ($department->parent == $parent_id) {
            $children = buildTree($departments, $department->id);
            if ($children) {
                $department->children = $children;
            }
            $branch[$department->id] = $department;
            unset($departments[$department->id]);
        }
    }
    return $branch;
}
function displayTree($tree): void
{
    echo '<div id="treeContainer" class="tree-container">';
    echo '<ul  class="custom-tree" id="tree">';
    foreach ($tree as $node) {
        echo '<li>';
        echo '<input type="checkbox" checked>';
        echo '<a href ="#">' . $node->name . '</a>';
        if (!empty($node->children)) {
            echo '<ul class = "childen">';
            displayTree($node->children);
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
    echo'</div>';
}

//dd($a);






//<!-- Button trigger modal -->









