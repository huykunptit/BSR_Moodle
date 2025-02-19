<?php
/**
 * Save them nhan su ngoai bsr
 *
 * @copyright  2023 HA VU KIEN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('ROLEASSIGN_STUDENT',         5);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/totara/hierarchy/prefix/position/lib.php');
require_once($CFG->dirroot.'/totara/hierarchy/lib.php');
require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

require_sesskey();
require_login();

$positionid = required_param('positionid', PARAM_INT);
$organisationid = required_param('organisationid', PARAM_INT);
$users = required_param_array('users', PARAM_RAW);
$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'));
$plugin = enrol_get_plugin('manual');

$outcome = new stdClass();
$outcome->success = true;
$outcome->response = new stdClass();
$outcome->error = '';
$outcome->members = array();

$validusers = array_values(array_filter($users, function($v) {
    return strlen(explode(',', trim($v))[0]) > 0 && strlen(explode(',', trim($v))[1]) > 0;
}));
foreach ($validusers as $m) {
	$u = new stdClass();
	$u->fullname = trim(explode(',', $m)[0]);
	$u->idnumber = trim(explode(',', $m)[1]);
	$u->id = -1;
	$u->newuser = true;
	$u->newenrol = true;
	$outcome->members[] = $u;
}

$useridnumbers = array();
foreach ($outcome->members as $m) {
	$useridnumbers[] = $m->idnumber;
}
// var_dump($useridnumbers);

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlsrv',
    'host'      => "$CFG->dbhost",
    'database'  => "$CFG->dbname",
    'username'  => "$CFG->dbuser",
    'password'  => "$CFG->dbpass",
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => '',
]);
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
// $capsule->bootEloquent();

$users = Capsule::table('mdl_user')
			->whereIn('mdl_user.idnumber', $useridnumbers)
			->select('id', 'idnumber', 'firstname', 'lastname', 'username', 'email')
			->get();
// var_dump($users);die;
foreach ($outcome->members as $m) {
	$userexist = $users->first(function ($u) use ($m) {
		return $u->idnumber == $m->idnumber;
	});
	if (!empty($userexist)) {
		// user da ton tai, kiem tra da enrol hay chua
		$m->id = $userexist->id;
		$m->newuser = false;
		if (user_has_role_assignment($userexist->id, ROLEASSIGN_STUDENT, $context->id)) {
        	# code...
        	$m->newenrol = false;
        } else {
        	$timestart = time();
            // remove time part from the timestamp and keep only the date part
            $timestart = make_timestamp(date('Y', $timestart), date('m', $timestart), date('d', $timestart), 0, 0, 0);
            if ($instance->enrolperiod) {
                $timeend = $timestart + $instance->enrolperiod;
            } else {
                $timeend = 0;
            }
            $plugin->enrol_user($instance, $userexist->id, ROLEASSIGN_STUDENT, $timestart, $timeend);
        }

	} else {
		//== Creating new user ==//
		$usernew = new stdClass();
		$usernew->id = -1;
		$usernew->auth = 'manual';
		$usernew->mnethostid = $CFG->mnet_localhost_id;
		$usernew->confirmed = 1;
		$usernew->deleted = 0;
		$usernew->suspended = 0;
		$usernew->idnumber = $m->idnumber;
		$usernew->lastname = explode(' ', $m->fullname)[count(explode(' ', $m->fullname)) - 1];
		$usernew->firstname = trim(substr($m->fullname, 0, strlen($m->fullname) - strlen($usernew->lastname)));
		$usernew->username = $m->idnumber;
		$usernew->password = hash_internal_user_password($m->idnumber);
		$usernew->email = $m->idnumber . '@etest.bsr';
		$usernew->lang = $CFG->lang;
		
		$usernew->id = user_create_user($usernew, false, false);
		$m->id = $usernew->id;

		$position_assignment = new position_assignment(
				array(
						'userid'    => $usernew->id,
						'type'      => $POSITION_CODES['primary']
				)
		);
		$data = new stdClass(); //$usernew;
        $data->positionid = $positionid;
        $data->organisationid = $organisationid;         
        $data->type = $POSITION_CODES['primary'];
        $data->userid = $usernew->id;
        $data->id = $position_assignment->id;
        $position_assignment->userid = $usernew->id;
         
        ///////////////////
        // Setup data for position / organisation
        position_assignment::set_properties($position_assignment, $data);
        assign_user_position($position_assignment);
        //===== END add new user =====//

        //== Add to this course ==//
        $timestart = time();
        // remove time part from the timestamp and keep only the date part
        $timestart = make_timestamp(date('Y', $timestart), date('m', $timestart), date('d', $timestart), 0, 0, 0);
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }
        $plugin->enrol_user($instance, $usernew->id, ROLEASSIGN_STUDENT, $timestart, $timeend);
        //==== END enrol user ===//
	}
}

echo json_encode($outcome);
die();