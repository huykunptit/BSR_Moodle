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
global $DB;

/**
 * This script controls the display of the quiz reports.
 *
 * @package   mod_quiz
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\quiz_settings;

define('NO_OUTPUT_BUFFERING', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

$id = optional_param('id', 0, PARAM_INT);
$q = optional_param('q', 0, PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHA);
$download = optional_param('download', '', PARAM_ALPHA);
// dd($USER);
if ($id) {
    $quizobj = quiz_settings::create_for_cmid($id);
    
} else {
    $quizobj = quiz_settings::create($q);
}

$quiz = $quizobj->get_quiz();
//dd($quiz);


// Tạo điều kiện truy vấn từ đối tượng $record
$conditions = array(
    'courseid' => $quiz->course, // Sử dụng giá trị course từ đối tượng $quiz
    'itemname' => $quiz->name, // Sử dụng giá trị name từ đối tượng $quiz
);
// Thực hiện truy vấn để lấy bản ghi từ bảng grade_items
$grade_item_id = $DB->get_record('grade_items', $conditions);

// In ra kết quả để kiểm tra
//dd($grade_item_id->gradepass);

$cm = $quizobj->get_cm();
$course = $quizobj->get_course();

$url = new moodle_url('/mod/quiz/report.php', ['id' => $cm->id]);
$quizGrades = $DB->get_records('quiz_grades', array('quiz' => $quiz->id));

$resultArrays = [];

if ($quizGrades) {
    foreach ($quizGrades as $quizGrade) {
        $userId = $quizGrade->userid;
        $user_grade = $quizGrade->grade;
//        dd(grade_pass($quiz));
        $userRecord = $DB->get_record('user', array('id' => $userId));
        if ($userRecord) {
            $user_name = $userRecord->lastname . ' ' . $userRecord->firstname;
            $user_department = $userRecord->department;
            $user_info_record = $DB->get_record('user_info_data', array('userid' => $userId, 'fieldid' => 1));

            if ($user_info_record !== false) {
                // Record found, you can safely access its properties
                $user_position = $user_info_record->data;
            } else {
                // Record not found, handle the situation accordingly
                $user_position = null; // or any default value you prefer
            }

            $resultArray = [
                'name'       => $user_name,
                'department' => $user_department,
                'position'   => $user_position,
                'grade'      => $user_grade,
                'result'     => $user_grade >= grade_pass($quiz) ? 'Đạt' : 'K.Đạt',
            ];
            $resultArrays[] = $resultArray;
        }
    }
}

if ($mode !== '') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);
require_login($course, false, $cm);
$PAGE->set_pagelayout('report');
//dd($PAGE);
$PAGE->activityheader->disable();
$reportlist = quiz_report_list($quizobj->get_context());
if (empty($reportlist)) {
    throw new \moodle_exception('erroraccessingreport', 'quiz');
}

// Validate the requested report name.
if ($mode == '') {
    // Default to first accessible report and redirect.
    $url->param('mode', reset($reportlist));
    redirect($url);
} else if (!in_array($mode, $reportlist)) {
    throw new \moodle_exception('erroraccessingreport', 'quiz');
}
if (!is_readable("report/$mode/report.php")) {
    throw new \moodle_exception('reportnotfound', 'quiz', '', $mode);
}

require_once($CFG->dirroot . '/bsr/lib.php');
require_once($CFG->dirroot . '/bsr/vendor/autoload.php');
use PhpOffice\PhpWord\TemplateProcessor;
use PhpXmlRpc\Helper\Date;

if($download === "docx" && $mode === 'overview') {
   
    $templatePath = $CFG->dirroot . '/bsr/resources/BSR-HRM-PRO-002-F-004-2 Ket qua dao tao - Rev 8.docx';

    // Load the Word document template
    $templateProcessor = new TemplateProcessor($templatePath);
    $now = Date('d/m/Y');
    
    if ($quizGrades) {
        $gradepass = grade_pass($quiz);
        $resultArrays = [];
        $key = 0;
        foreach ($quizGrades as $quizGrade) {
            $userId = $quizGrade->userid;
            $user_grade = number_format($quizGrade->grade, 2, '.', '');
    
            $userRecord = $DB->get_record('user', ['id' => $userId]);
            $user_result = $user_grade >= $gradepass ? 'Đạt' : 'K.Đạt';
    
            if ($userRecord) {
                $user_name = $userRecord->firstname . ' ' . $userRecord->lastname;
                $user_department = company_user::get_department_parent_id($userId);
                
                $ban = $DB->get_record(('department'), array('id' => $user_department));
           
            if ($ban && !empty($ban->name)) {
                // Split the department name into words
                $words = explode(' ', $ban->name);
                
                // Remove the first word
                array_shift($words);
                
                // Create acronym by taking the first letter of each remaining word
                $acronym = '';
                foreach ($words as $word) {
                    $acronym .= mb_substr($word, 0, 1); // Use mb_substr for multibyte support
                }
                
                // Convert acronym to uppercase
                $acronym = strtoupper($acronym);
               
               $user_department = $acronym;
               
            }
            
                $user_position = $DB->get_record('user_info_data', ['userid' => $userId, 'fieldid' => 1])->data;
    
                $resultArrays[] = [
                    'key' => ++$key,
                    'manv' => $userRecord->idnumber,
                    'hoten' => $user_name,
                    'ban' => $user_department,
                    'chucdanh' => $user_position,
                    'diem' => $user_grade,
                    'dat' => $user_result == 'Đạt' ? 'x' : '',
                    'kodat' => $user_result == 'K.Đạt' ? 'x' : ''
                ];
            }
        }
    }
    usort($resultArrays, function($a, $b) {
        return strcmp($a['ban'], $b['ban']); 
    });
    $timelimit = $quiz->timelimit;
    $hours = floor($timelimit / 3600);
    $minutes = floor(($timelimit % 3600) / 60);
    $formattedTime = $hours ? sprintf("%02dh:%02dp", $hours, $minutes) : sprintf("%02dp", $minutes);
    
    // Set simple placeholders
    $templateProcessor->setValue('tieude', $quiz->name);
    $templateProcessor->setValue('now', $now);
    $templateProcessor->setValue('quiz_time', $formattedTime);
    $templateProcessor->setValue('startdate', date('d/m/Y', $quiz->timeopen));
    $templateProcessor->setValue('enddate', date('d/m/Y', $quiz->timeclose));
   
    // Clone rows in the table and set values for each row
    $templateProcessor->cloneRowAndSetValues('manv', $resultArrays);
    
    // Save the file temporarily
    $temp_file = tempnam(sys_get_temp_dir(), 'BSR-HRM-PRO-002-F-004-2');
    $templateProcessor->saveAs($temp_file);
    
    $fullname = $quiz->name;
    $filename = $fullname . '_BSR-HRM-PRO-002-F-004-2 Ket qua dao tao Rev 8.docx';
    $encoded_filename = urlencode($filename);
    $encoded_filename = str_replace('+', ' ', $encoded_filename);
    
    // Clear output buffer and set headers for file download
    if (ob_get_length()) ob_end_clean();
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Length: " . filesize($temp_file));
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    header("Pragma: public");
    
    // Output the file to the browser and clean up
    readfile($temp_file);
    unlink($temp_file);
    exit;
}
 else {
    // Open the selected quiz report and display it.
    $file = $CFG->dirroot . '/mod/quiz/report/' . $mode . '/report.php';

    if (is_readable($file)) {
        include_once($file);
    }
    $reportclassname = 'quiz_' . $mode . '_report';
    if (!class_exists($reportclassname)) {
        throw new \moodle_exception('preprocesserror', 'quiz');
    }

    $report = new $reportclassname();
    $report->display($quiz, $cm, $course);

// Print footer.
    echo $OUTPUT->footer();

// Log that this report was viewed.
    $params = [
        'context' => $quizobj->get_context(),
        'other' => [
            'quizid' => $quiz->id,
            'reportname' => $mode
        ]
    ];
    $event = \mod_quiz\event\report_viewed::create($params);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('quiz', $quiz);
    $event->trigger();
}
