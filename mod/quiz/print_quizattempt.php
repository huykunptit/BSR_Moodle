<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/printquizlib.php');
require_once($CFG->dirroot . '/lib/gradelib.php');


    $attemptid = required_param('attempt', PARAM_INT);
    $attemptobj = quiz_attempt::create($attemptid);

    $course = $attemptobj->get_course();
    require_login($course, false, $attemptobj->get_cm());
    


    require_capability('mod/quiz:manage', context_module::instance($attemptobj->get_cmid()));

    offlinequiz_create_pdf_question_by_attempt($attemptobj, $course->id,context_module::instance($attemptobj->get_cmid()));


