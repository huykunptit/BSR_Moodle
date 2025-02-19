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
use PhpOffice\{PhpWord\Settings};
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
        // dd($coursegrade);
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
    $report = new grade_report_overview($userid, $gpr, $context);
    
    
    $coursegrade = $report->setup_courses_data(true);
        

        
    if (!empty($report->studentcourseids)) {
        // If the course id matches the site id then we don't have a course context to work with.
        // Display a standard page.
        if ($courseid == SITEID) {
            $PAGE->set_pagelayout('standard');
            $header = get_string('grades', 'grades') . ' - ' . fullname($report->user);
            $PAGE->set_title($header);
            $PAGE->set_heading(fullname($report->user));
            
            if ($USER->id != $report->user->id) {
                $PAGE->navigation->extend_for_user($report->user);
                if ($node = $PAGE->settingsnav->get('userviewingsettings'.$report->user->id)) {
                    $node->forceopen = true;
                }
            } else if ($node = $PAGE->settingsnav->get('usercurrentsettings', navigation_node::TYPE_CONTAINER)) {
                $node->forceopen = true;
            }

            echo $OUTPUT->header();
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
                $fulluserdata->department = company_user::get_department_name($user->id);

            }
            
            $fullname = fullname($fulluserdata, true);
            // $a = userdate($coursegrade->startdate);
            $job_details = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => 1]);
            // $fullname = fullname($USER, true);
            // get user_department
            $user_department = $DB->get_record('company_users', ['userid' => $userid]);
           
            // dd($user_department);
            $departments = $DB->get_record('department', ['id' => $user_department->departmentid]);
            $parent = $DB->get_record('department', ['id' => $departments->parent]);
            // dd($departments,$parent);
            echo '
            <div id="user-info">
            
            <table class="flexible table table-striped table-hover boxaligncenter generaltable">
            <thead>
            <h3 class = "text-center">'.$fullname.'</h3>
        <br>
            </thead>
            <tr>
            <td class ="font-weight-bold text-dark">'.$idnumber.': <span>'.$fulluserdata->idnumber.'</span></td>
            <td></td>
            </tr>
            <tr>
            <td class ="font-weight-bold text-dark">'.get_string('email','moodle').': <span >'.$fulluserdata->email.'</span></td>

            </tr>
            <tr>
            <td class="font-weight-bold text-dark">
            '.$department.':
            
            <span>
               '.$parent->name.'
            </span>
        </td>
        
            
            </tr>
            <tr>
            <td class ="font-weight-bold text-dark">'.$position.': <span>'.$job_details.'</span></td>

            </tr>
            <tr>
            <td class ="font-weight-bold text-dark">'.get_string('team','moodle').': <span>'.$departments->name.'</span></td>

            </tr>
            <tr>
            <td class ="font-weight-bold text-dark">'.get_string('phone','moodle').': <span>'.$fulluserdata->phone1.'</span></td>
            
            </tr>
            </table>
            </div>
            ';
            

            echo '
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/Chart.js"></script>
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/Chart.bundle.min.js"></script>
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/jspdf.umd.min.js"></script>
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/html2canvas.min.js"></script>
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/pdf-lib.min.js"></script>
            <script src="' . $CFG->wwwroot . '/theme/edumy/javascript/download.js"></script>';

            echo '
            <style>
            th{
            width: 30%
            }

            #overview-grade td {
        border: 2px solid #dee2e6;
        }
            #overview-grade th {
        border: 2px solid #dee2e6;
        }


            </style>
            ';

            echo '
            <script> 
            window.jsPDF = window.jspdf.jsPDF;

            function exportUserInfoToPDF() {
                html2canvas(document.getElementById("user-info"), { useCORS: true }).then(canvas => {
                    const imgData = canvas.toDataURL("image/png");
                    const pdf = new jsPDF();

                    pdf.addImage(imgData, "PNG", 20, 10);
                    
                    pdf.save("user-info.pdf");
                }).catch(error => {
                    console.error("Error generating PDF:", error);
                });
            }</script>
            


            ';

            echo "<script>
            window.exportToPDF = async function() {
                console.log(12312);
                var exportButton = document.getElementById('exportButton');
                
                function toggleButtonVisibility(display) {
                    if (exportButton) {
                        exportButton.style.display = display;
                    }
                }
            
                try {
                    toggleButtonVisibility('none');
                    const style = document.createElement('style');
                    style.textContent = `
                        #region-main, #region-main * {
                            font-size: 30px !important;
                        }
                    `;
                    document.head.appendChild(style);
            
                    const pdfDoc = await PDFLib.PDFDocument.create();
                    const page = pdfDoc.addPage([595, 842]); // A4 size in points
                    const logoBytes = await fetch('logo.png').then(res => res.arrayBuffer());
                    const logoImage = await pdfDoc.embedPng(logoBytes);
                    page.drawImage(logoImage, {
                        x: 20,
                        y: 842 - 65,
                        width: 60,
                        height: 60,
                    });
                    const element = document.getElementById('region-main');
                    const scale = 2; // Increase scale for better quality
                    const canvas = await html2canvas(element, {
                        scale: scale,
                        useCORS: true,
                        logging: true
                    });
            
                    document.head.removeChild(style);
            
                    const imgData = canvas.toDataURL('image/png');
                    const imgBytes = await fetch(imgData).then(res => res.arrayBuffer());
                    const img = await pdfDoc.embedPng(imgBytes);
            
                    const { width, height } = page.getSize();
                    const aspectRatio = canvas.width / canvas.height;
                    
                    let imgWidth = width - 40;
                    let imgHeight = imgWidth / aspectRatio;
            
                    if (imgHeight > height - 60) {
                        imgHeight = height - 60;
                        imgWidth = imgHeight * aspectRatio;
                    }
            
                    page.drawImage(img, {
                        x: (width - imgWidth) / 2,
                        y: height - imgHeight - 30,
                        width: imgWidth,
                        height: imgHeight,
                    });
            
                    const fontSize = 10;
                    const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
                    const pageText = 'Trang 1 / 1';
                    const textWidth = font.widthOfTextAtSize(pageText, fontSize);
                    page.drawText(pageText, {
                        x: width - textWidth - 30,
                        y: 20,
                        size: fontSize,
                        font: font,
                        color: PDFLib.rgb(0, 0, 0),
                    });
            
                    const pdfBytes = await pdfDoc.save();
                    const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    
                    // Generate filename
                    const now = new Date();
                    const day = String(now.getDate()).padStart(2, '0');
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const year = String(now.getFullYear()).slice(-2);
                    const filename = `{$fullname}-Ket_qua_dao_tao_ca_nhan.pdf`;
                    
                
                    
                    link.download = filename;
                    link.click();
            
                    URL.revokeObjectURL(link.href);
                } catch (error) {
                    console.error('Error generating PDF:', error);
                } finally {
                    toggleButtonVisibility('');
                }
            };
        </script>";
        
                

        
        
            
            $userpicture = new \user_picture($USER);
        
            echo '<canvas id="myChart" ></canvas>';
            echo '
            <script>
                // Get data from PHP
                const courseNames = ' . json_encode($courseNames, JSON_UNESCAPED_UNICODE) . ';
                const finalGrades = ' . json_encode($finalGrades) . ';
            
                const baseColors = [
                    "rgba(255, 99, 132, 0.5)", "rgba(255, 159, 64, 0.5)", "rgba(255, 205, 86, 0.5)",
                    "rgba(75, 192, 192, 0.5)", "rgba(54, 162, 235, 0.5)", "rgba(153, 102, 255, 0.5)",
                    "rgba(201, 203, 207, 0.5)", "rgba(255, 0, 0, 0.5)", "rgba(0, 255, 0, 0.5)",
                    "rgba(0, 0, 255, 0.5)", "rgba(255, 255, 0, 0.5)", "rgba(255, 0, 255, 0.5)",
                    "rgba(0, 255, 255, 0.5)", "rgba(128, 0, 0, 0.5)", "rgba(0, 128, 0, 0.5)",
                    "rgba(0, 0, 128, 0.5)", "rgba(128, 128, 0, 0.5)", "rgba(128, 0, 128, 0.5)",
                    "rgba(0, 128, 128, 0.5)", "rgba(128, 128, 128, 0.5)", "rgba(192, 0, 0, 0.5)",
                    "rgba(0, 192, 0, 0.5)", "rgba(0, 0, 192, 0.5)", "rgba(192, 192, 0, 0.5)",
                    "rgba(192, 0, 192, 0.5)", "rgba(0, 192, 192, 0.5)", "rgba(192, 192, 192, 0.5)"
                ];
            
                const baseBorderColors = [
                    "rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)",
                    "rgb(54, 162, 235)", "rgb(153, 102, 255)", "rgb(201, 203, 207)", "rgb(255, 0, 0)",
                    "rgb(0, 255, 0)", "rgb(0, 0, 255)", "rgb(255, 255, 0)", "rgb(255, 0, 255)",
                    "rgb(0, 255, 255)", "rgb(128, 0, 0)", "rgb(0, 128, 0)", "rgb(0, 0, 128)",
                    "rgb(128, 128, 0)", "rgb(128, 0, 128)", "rgb(0, 128, 128)", "rgb(128, 128, 128)",
                    "rgb(192, 0, 0)", "rgb(0, 192, 0)", "rgb(0, 0, 192)", "rgb(192, 192, 0)",
                    "rgb(192, 0, 192)", "rgb(0, 192, 192)", "rgb(192, 192, 192)"
                ];
            
                // Function to generate colors based on the number of courses
                function generateColors(totalItems, baseColors) {
                    let colors = [];
                    for (let i = 0; i < totalItems; i++) {
                        colors.push(baseColors[i % baseColors.length]); // Cycle through base colors
                    }
                    return colors;
                }
            
                const backgroundColor = generateColors(courseNames.length, baseColors);
                const borderColor = generateColors(courseNames.length, baseBorderColors);
            
                const totalCourses = courseNames.length;
                const chartWidth = document.getElementById("myChart").offsetWidth;
                const barThickness = chartWidth / totalCourses - 10;
            
                const cty = document.getElementById("myChart").getContext("2d");
            
                const myChart = new Chart(cty, {
                    type: "bar",
                    data: {
                        labels: courseNames,
                        datasets: [{
                            label: "Điểm: ",
                            data: finalGrades.map(grade => parseFloat(grade)),
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            borderWidth: 3,
                            barThickness: barThickness,
                            indexAxis: "y",
                        }]  
                    },
                    options: {
                        scales: {
                            y: {
                                responsive: true,
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    stepSize: 1
                                },
                                axis: "x",
                            },
                        },
                        indexAxis: "y"
                    }
                });
            </script>
            ';  
                    echo '<br>';
                    if ($report->fill_table(true, true)) {
                        echo html_writer::tag('h3', get_string('coursesiamtaking', 'grades'));
                        
                        echo '<br />' . $report->print_table(true);
                    }


                    echo '<canvas id="myChart"></canvas>';
                    echo '<div id="chartMessage"></div>';
                    echo '<form method="post" action="export_word.php" id="exportForm">
                            <input type="hidden" name="chart_image" id="chartImage">
                    <input type="hidden" name="courseid" value="' . $courseid . '">
                    <input type="hidden" name="userid" value="' . $userid . '">
                    <input type="submit" name="export_word" value="Xuất ra file Word" class="btn btn-primary" id="exportButton">
                </form>';
                
                
                $job_details = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => 1]);
                $fullname = fullname($USER, true);
                
                
            
    } else { // We have a course context. We must be navigating from the gradebook.
                    print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview')
                        . ' - ' . fullname($report->user), false, false, true, null, null,
                        $report->user, $actionbar);
                    if ($report->fill_table()) {
                        echo '<br />' . $report->print_table(true);
                    }
                 }
                // $temp_file = tempnam($CFG->dirroot . '/bsr/results/', 'BSR-HRM-PRO-002-F-004-2 Ket qua dao tao ca nhan- Rev 8');
             



       
    } else {
        $PAGE->set_pagelayout('standard');
        $header = get_string('grades', 'grades') . ' - ' . fullname($report->user);
        $PAGE->set_title($header);
        $PAGE->set_heading(fullname($report->user));
        echo $OUTPUT->header();
    }

    if (count($report->teachercourses)) {
        echo html_writer::tag('h3', get_string('coursesiamteaching', 'grades'));
        $report->print_teacher_table();
        
    }
    
    if (empty($report->studentcourseids) && empty($report->teachercourses)) {
        // We have no report to show the user. Let them know something.
        echo $OUTPUT->notification(get_string('noreports', 'grades'), 'notifymessage');
    }
}

grade_report_overview::viewed($context, $courseid, $userid);

echo $OUTPUT->footer();
