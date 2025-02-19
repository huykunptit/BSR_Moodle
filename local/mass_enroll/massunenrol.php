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
 * A bulk enrolment plugin allowing teachers to enrol accounts to their courses, optionally adding every user to a group.
 *
 * Version for Moodle 1.9.x courtesy of Patrick POLLET & Valery FREMAUX  France, February 2010
 * Version for Moodle 2.x by pp@patrickpollet.net March 2012
 *
 * File         mass_enroll.php
 * Encoding     UTF-8
 *
 * @package     local_mass_enroll
 *
 * @copyright   1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @copyright   2012 onwards Patrick Pollet
 * @copyright   2015 onwards R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Get params.
$id = required_param('id', PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $id))) {
    throw new \moodle_exception("Course is misconfigured");
}

// Security and access check.
require_course_login($course);
$context = context_course::instance($course->id);
require_capability('local/mass_enroll:unenrol', $context);

// Start making page.
$strinscriptions = get_string('mass_unenroll', 'local_mass_enroll');
$PAGE->set_pagelayout('incourse');
$PAGE->set_url(new moodle_url($CFG->wwwroot . '/local/mass_enroll/massunenrol.php', array('id' => $id)));
$PAGE->set_title($course->fullname . ': ' . $strinscriptions);
$PAGE->set_heading($course->fullname . ': ' . $strinscriptions);

$course = $PAGE->course;
$renderer = $PAGE->get_renderer('local_mass_enroll');
$form = new \local_mass_enroll\local\forms\massunenrol(new moodle_url($PAGE->url), array(
    'course' => $course,
    'context' => $context
));
$result = $form->process();

if ($result) {
    \core\notification::success(get_string('process:massunenrol:success', 'local_mass_enroll'));
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}else {
    echo $renderer->header();
    echo $renderer->get_tabs($context, 'massunenrol', array('id' => $course->id));
    echo 'Chức năng này cho phép  xoá ghi danh nhiều thí sinh từ file <br> 

                Cột phải chứa mã nhân viên : <b>idnumber</b> của thí sinh cần  xoá ghi danh. <br>';
                echo $renderer->heading_with_help($strinscriptions, 'mass_enroll', 'local_mass_enroll',
                'icon', get_string('mass_enroll', 'local_mass_enroll'));
    // echo $renderer->box(get_string('mass_enroll_info', 'local_mass_enroll'), 'center');
    // $url1 = new moodle_url('Bulkenrollment - Test.csv');
    // $link1 = html_writer::link($url1, 'File mẫu');
    $url1 = new moodle_url('Bulkenrollment - Test.csv');

    // Tạo liên kết HTML
    $link1 = html_writer::link('#', 'File mẫu', array('id' => 'downloadLink'));
    
    // Tạo tên file mới dựa trên ngày hiện tại
    $currentDate = date('Ymd');
    $newFilename = $currentDate . '_Filemau_XoaGhidanhbangfile.csv';
    
    // Hiển thị liên kết và thêm JavaScript để xử lý tải xuống
    echo '
    <div class="linkdown" style="background-color: #1e4fa5; text-align: center; padding: 10px; width: 10%; border-radius: 4px; margin: 0 auto; display: block; color:white;">
        ' . $link1 . '
    </div>
    
    <style>
    .dashboard_main_content .linkdown > a {
        color: white;
        text-decoration: none;
    }
    </style>
    
    <script>
    document.getElementById("downloadLink").addEventListener("click", function(event) {
        event.preventDefault(); // Ngăn chặn hành động mặc định của liên kết
        var originalUrl = "' . $url1->out() . '";
        var newFilename = "' . $newFilename . '";
    
        var link = document.createElement("a");
        link.href = originalUrl;
        link.download = newFilename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
    </script>
    ';





    echo $form->render();
    // \core\notification::success(get_string('process:massunenrol:success', 'local_mass_enroll'));
    // redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
    echo $renderer->footer();
}

// echo $renderer->header();
// echo $renderer->get_tabs($context, 'massunenrol', array('id' => $course->id));
// echo $renderer->heading_with_help($strinscriptions, 'mass_unenroll', 'local_mass_enroll',
//                 'icon', get_string('mass_unenroll', 'local_mass_enroll'));
// echo $renderer->box(get_string('mass_unenroll_info', 'local_mass_enroll'), 'center');
// echo $form->render();
// echo $renderer->footer();
