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


global $CFG, $PAGE;
require_once('../config.php');
require_once($CFG->dirroot.'/question/coursequestionmanagement.php');

$categoryid = required_param('categoryid', PARAM_INT);

$coursecat_question_manager =  new coursecat_question_manager();

$url = new moodle_url('/question/coursecatquestview.php');

$category = coursecat::get($categoryid);
$context = context_coursecat::instance($category->id);
$url->param('categoryid', $category->id);


$strmanagement = get_string('disciplinequestionbank');

$pageheading = format_string($SITE->fullname, true, array('context' => $context));

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($strmanagement);
$PAGE->set_heading($pageheading);

require_login();

/////////////////

$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('disciplinequestionbank'));

$parents = coursecat::get_many($category->get_parents());
foreach ($parents as $parent) {
    $PAGE->navbar->add(
        $parent->get_formatted_name(),
        new moodle_url('/question/coursecatquestview.php', array('categoryid' => $parent->id))
    );
}
$PAGE->navbar->add(
    $category->get_formatted_name());
/////////
echo $OUTPUT->header();

$coursecat_question_manager->render_coursecat_question_bank($category);

echo $OUTPUT->footer();

