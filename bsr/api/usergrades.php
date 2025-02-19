<?php
require __DIR__.'/../vendor/autoload.php';
require(__DIR__.'/../../config.php');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    // throw new \Exception('Request method must be POST!');
	http_response_code(400);
	return;
}
if (!isset($_POST['idnumber'])) {
	http_response_code(400);
	return;
}
$inputs = json_decode($_POST['idnumber']);
if (!is_array($inputs)) {
	http_response_code(400);
	return;
}

$output = array();
if (count($inputs) > 0) {
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
				->whereIn('mdl_user.idnumber', array_map('strval', $inputs))
				->select(Capsule::raw('id, idnumber, firstname, lastname, firstname + \' \' + lastname as fullname, email'))
				->get();
	$luotthi = Capsule::table('mdl_course_modules')
			->join('mdl_course', 'mdl_course_modules.course', '=', 'mdl_course.id')
			->join('mdl_grade_items', function($join)
	        {
	            $join->on('mdl_course_modules.instance', '=', 'mdl_grade_items.iteminstance')
	                 ->where('mdl_grade_items.itemmodule', '=', 'quiz');
	        })
			->join('mdl_quiz', 'mdl_course_modules.instance', '=', 'mdl_quiz.id')
			->join('mdl_enrol', 'mdl_course_modules.course', '=', 'mdl_enrol.courseid')
			->join('mdl_user_enrolments', 'mdl_enrol.id', '=', 'mdl_user_enrolments.enrolid')
			->join('mdl_quiz_attempts', function($join)
	        {
	            $join->on('mdl_user_enrolments.userid', '=', 'mdl_quiz_attempts.userid');
                $join->on('mdl_course_modules.instance', '=', 'mdl_quiz_attempts.quiz');
	        })
			->where('mdl_enrol.enrol', 'manual')
			->where(function($query)
            {
                $query->where('mdl_quiz_attempts.preview', '=', 0)
                      ->orWhereNull('mdl_quiz_attempts.preview');
            })
            ->whereIn('mdl_user_enrolments.userid', $users->pluck('id'))
			->select(
				'mdl_course.id as courseid',
				'mdl_course.fullname as coursefullname',
				'mdl_course.shortname as courseshortname',
				'mdl_course_modules.instance as quiz',
				'mdl_quiz.name as quizname',
				'mdl_user_enrolments.userid',
				'mdl_quiz_attempts.state',
				Capsule::raw('dateadd(S, mdl_quiz_attempts.timestart, \'1970-01-01 07:00:00\') as timestart'),
				Capsule::raw('dateadd(S, mdl_quiz_attempts.timefinish, \'1970-01-01 07:00:00\') as timefinish'),
				Capsule::raw('FORMAT(mdl_grade_items.grademax, \'N1\') as grademax'),
				Capsule::raw('FORMAT(round(mdl_quiz_attempts.sumgrades * mdl_quiz.grade / mdl_quiz.sumgrades, 1), \'N1\') as grade')
			)
			->orderBy('mdl_quiz_attempts.timefinish', 'desc')
			->get();
			//->toSql();
	$users->each(function($ts) use ($luotthi) {
		$userid = $ts->id;
		$ts->attempts = array();
		$luotthi->filter(function($lt) use ($userid) {
			return $lt->userid == $userid;
		})->each(function($lt) use ($ts) {
			$ts->attempts[] = $lt;
		});
	});
	$output = $users->toArray();
}


// return as json
header('Content-Type: application/json; charset=utf-8');
echo json_encode($output);
?>