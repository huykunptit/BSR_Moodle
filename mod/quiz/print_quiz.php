<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/printquizlib.php');

$id = required_param('id', PARAM_INT);
$include_correction = optional_param('correction', 0, PARAM_INT);

if($include_correction > 0){
	$include_correction = true;
}else{
	$include_correction = false;
}

if (!$cm = get_coursemodule_from_id('quiz', $id)) {
	print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
	print_error('coursemisconf');
}
if (!$quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
	print_error('invalidcoursemodule');
}

require_login($course, false, $cm);

require_capability('mod/quiz:manage', context_module::instance($cm->id));
$quiz_papercopy =  new quiz_papercopy($quiz, $cm, $course  );
$quiz_papercopy->print_quiz($include_correction);