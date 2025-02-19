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
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once '../../../config.php';
 require_once $CFG->libdir.'/gradelib.php';
 require_once $CFG->dirroot.'/grade/lib.php';
 require_once $CFG->dirroot.'/grade/report/overview/lib.php';
 require_once($CFG->dirroot . '/bsr/lib.php');
 require_once($CFG->dirroot . '/bsr/vendor/autoload.php');
 require_once($CFG->dirroot.'/bsr/bootstrap.php');
 use PhpOffice\PhpWord\Settings;
 Settings::setOutputEscapingEnabled(true);
 
use ChartJs\ChartJs;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Chart;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
// dd($userid,$courseid);
$PAGE->set_url(new moodle_url('/grade/report/overview/index.php', array('id' => $courseid, 'userid' => $userid)));

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login(null, false);
$PAGE->set_course($course);

$department =  get_string('department','moodle');
$position =  get_string('position', 'filters');
$idnumber =  get_string('idnumber','moodle');
$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;

// If we are accessing the page from a site context then ignore this check.
if ($courseid != SITEID) {
    require_capability('gradereport/overview:view', $context);
}

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        throw new \moodle_exception('invaliduserid');
    }
    $personalcontext = context_user::instance($userid);
}

if (isset($personalcontext) && $courseid == SITEID) {
    $PAGE->set_context($personalcontext);
} else {
    $PAGE->set_context($context);
}
if ($userid == $USER->id) {
    $settings = $PAGE->settingsnav->find('mygrades', null);
    $settings->make_active();
} else if ($courseid != SITEID && $userid) {
    // Show some other navbar thing.
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    $PAGE->navigation->extend_for_user($user);
}

$access = grade_report_overview::check_access($systemcontext, $context, $personalcontext, $course, $userid);

if (!$access) {
    // no access to grades!
    throw new \moodle_exception('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$course->id, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'overview';

$actionbar = new \core_grades\output\general_action_bar($context,
    new moodle_url('/grade/report/overview/index.php', ['id' => $courseid]), 'report', 'overview');

if (has_capability('moodle/grade:viewall', $context) && $courseid != SITEID) {
    // Please note this would be extremely slow if we wanted to implement this properly for all teachers.
    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = $gpr->groupid;

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup)) {
        // no separate group access, can view only self
        $userid = $USER->id;
        $user_selector = false;
    } else {
        $user_selector = true;
    }

    if (empty($userid)) {
        print_grade_page_head($courseid, 'report', 'overview', false, false, false,
            true, null, null, null, $actionbar);

        groups_print_course_menu($course, $gpr->get_return_url('index.php?id='.$courseid, array('userid'=>0)));

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_overview');
            echo $renderer->graded_users_selector('overview', $course, $userid, $currentgroup, false);
        }
        // do not list all users

    } else { // Only show one user's report
       
        $report = new grade_report_overview($userid, $gpr, $context);
       
        // Make sure we have proper final grades - this report shows grades from other courses, not just the
        // selected one, so we need to check and regrade all courses the user is enrolled in.
        $report->regrade_all_courses_if_needed(true);
        $coursegrade = $report->setup_courses_data(true);
     
        print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview') .
            ' - ' . fullname($report->user), false, false, true, null, null,
            $report->user, $actionbar);
        groups_print_course_menu($course, $gpr->get_return_url('index.php?id='.$courseid, array('userid'=>0)));

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_overview');
            echo $renderer->graded_users_selector('overview', $course, $userid, $currentgroup, false);
        }

        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
        } else {
            if ($report->fill_table()) {
                echo '<br />'.$report->print_table(true);
            }
        }
    }
} else { // Non-admins and users viewing from the site context can just see their own report.

    // Create a report instance
    $userid = required_param('userid', PARAM_INT);
    
    $report = new grade_report_overview($userid, $gpr, $context);
    
    
    $coursegrade = $report->setup_courses_data(true);
       
            
    if (!empty($report->studentcourseids)) {
        
        // If the course id matches the site id then we don't have a course context to work with.
        // Display a standard page.
        if ($courseid == SITEID) {
           
            $courseNames = [];
            $finalGrades = [];
            $urlObject = $PAGE->url;
            $path = $urlObject->get_path(); 
                
            // Get the userid parameter
            $params = $urlObject->params();
            $userid = isset($params['userid']) ? $params['userid'] : null;
            $exporthtml .= '<h4>' .get_string('coursegradeprofile'). '</h4>';
    
    
            
            foreach ($coursegrade as $data) {
               
                $startdate = userdate($data['course']->startdate,  $startDate = get_string('activitydate:opened', 'course'), 0);
                // dump($startdate);
                $courseNames[] = $data['course']->fullname;
                $finalGrades[] = number_format((float)($data['finalgrade'] ?? 0), 2, '.', ''); // Use 0 if finalgrade is null
            }

            $fulluserdata = $DB->get_record('user', array('id' => $userid));
            // Check if user data exists
            if ($fulluserdata) {
                // Update department
                $fulluserdata->department = company_user::get_department_name($userid);

            }
            $fullname = fullname($fulluserdata, true);
            
            $job_details = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => 1]);
            $fullname = fullname($fulluserdata, true);
        
            
                
                
            $templatePath = $CFG->dirroot . '/bsr/resources/BSR-HRM-PRO-002-F-004-2 Ket qua dao tao ca nhan- Rev 8.docx';

            // Create a TemplateProcessor instance
            $templateProcessor = new TemplateProcessor($templatePath);
            $phpWord = new PhpWord();

            $chartFilename = $_POST['chartFilename'] ?? ''; 
           
            $manv = $fulluserdata->idnumber;
            $chucdanh = $job_details; // Example value
            $ban = $fulluserdata->department; // Example value
            $dienthoai = $fulluserdata->phone1; // Example value
            
            $courseNames = [];
            $finalGrades = [];
            $startDates = [];
            
            foreach ($coursegrade as $data) {
                $startdate = userdate($data['course']->startdate, get_string('strftimedatefullshort', 'langconfig'), 0);
                $startDates[] = $startdate;
               
                $courseNames[] = $data['course']->fullname;
                $finalGrades[] = number_format((float)($data['finalgrade'] ?? 0), 2, '.', ''); // Use 0 if finalgrade is null
            }
            // dd($startDates);
            
            $combinedData = [];
            
            foreach ($courseNames as $index => $courseName) {
                $combinedData[] = [
                    'coursename' => $courseName,
                    'grade' => $finalGrades[$index],
                    'startdate' => $startDates[$index],
                ];
            }
            
            $rowCount = count($combinedData);
            
            // Clone rows in the template
            $templateProcessor->cloneRow('index', $rowCount);
            
            // Fill data into the cloned rows
            foreach ($combinedData as $index => $data) {
                $rowIndex = $index + 1;
                $templateProcessor->setValue("index#{$rowIndex}", $rowIndex);
                $templateProcessor->setValue("coursename#{$rowIndex}", $data['coursename']);
                $templateProcessor->setValue("startdate#{$rowIndex}", $data['startdate']);
                $templateProcessor->setValue("grade#{$rowIndex}", $data['grade']);
            }
            
            // Other values
            $templateProcessor->setValue('manv', $manv);
            $templateProcessor->setValue('hoten', $fullname); // Assuming $fullname is defined elsewhere
            $templateProcessor->setValue('chucdanh', $chucdanh);
            $templateProcessor->setValue('ban', $ban);
            $templateProcessor->setValue('dienthoai', $dienthoai);
            
            $chartImagePath = $CFG->dirroot . '/bsr/results/chart.png';
            $chartWidth = 4800; // Width for A4 in pixels
            $chartHeight = 2000; // Custom height
            
            $image = imagecreatetruecolor($chartWidth, $chartHeight);
            
            // Set background and color
            $backgroundColor = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $backgroundColor);
            
            
            $barColors = [
                imagecolorallocate($image, 255, 99, 132),
                imagecolorallocate($image, 255, 159, 64),
                imagecolorallocate($image, 255, 205, 86),
                imagecolorallocate($image, 75, 192, 192),
                imagecolorallocate($image, 54, 162, 235),
                imagecolorallocate($image, 153, 102, 255),
                imagecolorallocate($image, 201, 203, 207),
                imagecolorallocate($image, 255, 0, 0),
                imagecolorallocate($image, 0, 255, 0),
                imagecolorallocate($image, 0, 0, 255),
                imagecolorallocate($image, 255, 255, 0),
                imagecolorallocate($image, 255, 0, 255),
                imagecolorallocate($image, 0, 255, 255),
                imagecolorallocate($image, 128, 0, 0),
                imagecolorallocate($image, 0, 128, 0),
                imagecolorallocate($image, 0, 0, 128),
                imagecolorallocate($image, 128, 128, 0),
                imagecolorallocate($image, 128, 0, 128),
                imagecolorallocate($image, 0, 128, 128),
                imagecolorallocate($image, 128, 128, 128),
                imagecolorallocate($image, 192, 0, 0),
                imagecolorallocate($image, 0, 192, 0),
                imagecolorallocate($image, 0, 0, 192),
                imagecolorallocate($image, 192, 192, 0),
                imagecolorallocate($image, 192, 0, 192),
                imagecolorallocate($image, 0, 192, 192),
                imagecolorallocate($image, 192, 192, 192),
                imagecolorallocate($image, 255, 128, 0),
                imagecolorallocate($image, 255, 0, 128),
                imagecolorallocate($image, 128, 255, 0),
                imagecolorallocate($image, 0, 255, 128),
                imagecolorallocate($image, 128, 0, 255),
                imagecolorallocate($image, 0, 128, 255),
                imagecolorallocate($image, 255, 255, 128),
                imagecolorallocate($image, 255, 128, 255),
                imagecolorallocate($image, 128, 255, 255),
                imagecolorallocate($image, 192, 255, 0),
                imagecolorallocate($image, 0, 192, 255),
                imagecolorallocate($image, 255, 192, 0)
            ];
            
            $borderColor = imagecolorallocate($image, 0, 0, 0);
            $textColor = imagecolorallocate($image, 0, 0, 0);
            function wrapText($fontSize, $angle, $fontPath, $text, $maxWordsPerLine = 4) {
                $words = explode(' ', $text);
                $wrappedText = '';
                $line = '';
                $wordCount = 0;
            
                foreach ($words as $word) {
                    $line .= $word . ' ';
                    $wordCount++;
            
                    if ($wordCount >= $maxWordsPerLine) {
                        $wrappedText .= trim($line) . "\n";
                        $line = '';
                        $wordCount = 0;
                    }
                }
            
                // Append any remaining words to the wrapped text
                if (!empty(trim($line))) {
                    $wrappedText .= trim($line);
                }
            
                return $wrappedText;
            }
            
            // Define dimensions
            $maxGrade = 10;
            $step = 2;
            $ySteps = $maxGrade / $step;
            $chartStartX = 150; // Left padding
            $chartStartY = 200; // Top padding
            $chartEndX = $chartWidth - $chartStartX; // Right padding
            $chartEndY = $chartHeight - $chartStartY-1000;// Bottom padding
            $barWidth = ($chartEndX - $chartStartX) / count($finalGrades); // Calculate bar width
            $fontSize = 40; // Font size for the grades
            $fontPath = $CFG->dirroot . '/font.ttf';
            // Draw X and Y axis lines with arrows
            function drawArrow($image, $x1, $y1, $x2, $y2, $color) {
                $arrowSize = 20;
                $angle = atan2($y2 - $y1, $x2 - $x1);
                $arrowX1 = $x2 - $arrowSize * cos($angle - M_PI / 6);
                $arrowY1 = $y2 - $arrowSize * sin($angle - M_PI / 6);
                $arrowX2 = $x2 - $arrowSize * cos($angle + M_PI / 6);
                $arrowY2 = $y2 - $arrowSize * sin($angle + M_PI / 6);
                imageline($image, $x1, $y1, $x2, $y2, $color);
                imageline($image, $x2, $y2, $arrowX1, $arrowY1, $color);
                imageline($image, $x2, $y2, $arrowX2, $arrowY2, $color);
            }
            
            // Draw Y-axis
            drawArrow($image, $chartStartX, $chartEndY, $chartStartX, $chartStartY, $textColor);
            
            // Draw X-axis
            drawArrow($image, $chartStartX, $chartEndY, $chartEndX, $chartEndY, $textColor);
            $fontSizeAxis = 20; // Font size for the axis labels
            // Draw Y-axis lines and labels
            for ($i = 0; $i <= $ySteps; $i++) {
                $y = $chartEndY - ($i * ($chartEndY - $chartStartY) / $ySteps);
                $label = $i * $step;
                imageline($image, $chartStartX - 20, $y, $chartEndX + 20, $y, $textColor);
                
                // Draw the grade label with the specified font size (40px)
                $textX = $chartStartX - 80; // Adjust X position to align with Y-axis
                $textY = $y + ($fontSize / 2) - 5; // Center the text vertically
                imagettftext($image, $fontSizeAxis, 0, $textX, $textY, $textColor, $fontPath, $label);
            }
            
            // Draw X-axis lines and labels
            $fontSize = 40;
            $fontPath = $CFG->dirroot . '/font.ttf'; // Path to the font file
            
            // Draw bars
            foreach ($finalGrades as $index => $grade) {
                $barHeight = ($grade / $maxGrade) * ($chartEndY - $chartStartY);
                $x1 = $chartStartX + $index * $barWidth;
                $y1 = $chartEndY;
                $x2 = $x1 + $barWidth - 10;
                $y2 = $y1 - $barHeight;
            
                $color = $barColors[$index % count($barColors)];
                imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
                imagerectangle($image, $x1, $y1, $x2, $y2, $borderColor);
            
                // Wrap course names if they exceed 4 words
                // $wrappedCourseName = wrapText($fontSize, 0, $fontPath, $courseNames[$index], 4);
            
                // Draw course names and grades
                $fontSize = 16; // Font size for labels
                $angle = -90; // Set angle to 0 for horizontal text
                $textX = $x1 + ($barWidth / 2) - 10; // Center the text
                $textY = $y1 + 40; // Adjust Y position based on your needs
                $gradeText = number_format($grade, 1);
                if ($grade == 0.00) {
                    $gradeText = null; // Set grade to null so the bar won't display any value
                }
                // Draw wrapped course name
                $lines = explode("\n", $courseNames[$index]);
                foreach ($lines as $lineIndex => $line) {
                    $lineY = $textY + ($lineIndex * $fontSize);
                    imagettftext($image, $fontSize, $angle, $textX, $lineY, $textColor, $fontPath, $line);
                }
            
                // Draw the grade at the top of the bar
                imagettftext($image, $fontSize, 0, $x1 + ($barWidth / 2) - 10, $y2 - 10, $textColor, $fontPath, $gradeText);
            }
            
            
            // Draw axis labels
            $fontSize = 20;
            
            // X-axis label
            // $xAxisLabel = 'Kỳ thi';
            $xAxisLabelBox = imagettfbbox($fontSize, 0, $fontPath, $xAxisLabel);
            $xAxisLabelWidth = $xAxisLabelBox[2] - $xAxisLabelBox[0];
            $xAxisLabelX = ($chartWidth - $xAxisLabelWidth) / 2 + 2200;
            $xAxisLabelY = $chartEndY + 80;
            imagettftext($image, $fontSize, 0, $xAxisLabelX, $xAxisLabelY, $textColor, $fontPath, $xAxisLabel);
            
            // Y-axis label
            // $yAxisLabel = 'Điểm';
            $yAxisLabelBox = imagettfbbox($fontSize, 90, $fontPath, $yAxisLabel);
            $yAxisLabelHeight = $yAxisLabelBox[2] - $yAxisLabelBox[0];
            $yAxisLabelX = $chartStartX - 10;
            $yAxisLabelY = ($chartHeight / 2) + ($yAxisLabelHeight / 2) - 800;
            imagettftext($image, $fontSize, 90, $yAxisLabelX, $yAxisLabelY, $textColor, $fontPath, $yAxisLabel);
            
            // Save and use the chart image
            imagepng($image, $chartImagePath);
            imagedestroy($image);
            
            // Insert the chart image into the Word template
            $templateProcessor->setImageValue('abc', array('path' => $chartImagePath, 'width' => 600, 'height' => 300, 'ratio' => true));
            
            imagedestroy($image);
            
            // Save the modified template to a temporary file
            $temp_file = tempnam($CFG->dirroot . '/bsr/results/', 'BSR-HRM-PRO-002-F-004-2');
            $templateProcessor->saveAs($temp_file);
            
            // Set the filename for download
            $filename = $fullname . '_BSR-HRM-PRO-002-F-004-2 Ket qua dao tao ca nhan- Rev 8.docx';
            $encoded_filename = urlencode($filename);
            $encoded_filename = str_replace('+', ' ', $encoded_filename);
            
            if (ob_get_length()) ob_clean();
            
          
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($temp_file));
            header('Cache-Control: max-age=0');
            
            
            readfile($temp_file);
            
            // Xóa file tạm
            unlink($temp_file);
            imagedestroy($image);

            unlink($chartImagePath);
            
               
        } 
        // $temp_file = tempnam($CFG->dirroot . '/bsr/results/', 'BSR-HRM-PRO-002-F-004-2 Ket qua dao tao ca nhan- Rev 8');
}
}
