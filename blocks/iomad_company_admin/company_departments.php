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
global $dashboardurl, $DB;

/**
 * @package   block_iomad_company_admin
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Script to let a user create a department for a particular company.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once('lib.php');
require_once(dirname(__FILE__) . '/../../course/lib.php');

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$departmentid = optional_param('deptid', 0, PARAM_INT);
$deleteids = optional_param_array('departmentids', null, PARAM_INT);
$createnew = optional_param('createnew', 0, PARAM_INT);
$deleteid = optional_param('deleteid', 0, PARAM_INT);
$confirm = optional_param('confirm', null, PARAM_ALPHANUM);
$submit = optional_param('submitbutton', '', PARAM_ALPHANUM);

$context = context_system::instance();
require_login();

iomad::require_capability('block/iomad_company_admin:edit_departments', $context);

$urlparams = array();
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

$linktext = get_string('editdepartment', 'block_iomad_company_admin');

// Set the url.
$linkurl = new moodle_url('/blocks/iomad_company_admin/company_departments.php');

$PAGE->set_context($context);
$PAGE->set_url($linkurl);
$PAGE->set_pagelayout('base');
$PAGE->set_title($linktext);

// get output renderer
$output = $PAGE->get_renderer('block_iomad_company_admin');

// Set the companyid
$companyid = iomad::get_my_companyid($context);
$company = new company($companyid);

// Set the page heading.
$PAGE->set_heading(get_string('companydepartment', 'block_iomad_company_admin'). $company->get_name());

// Set up the initial forms.
$mform = new \block_iomad_company_admin\forms\department_display_form($PAGE->url, $companyid, $departmentid, $output);

// Delete any valid departments.
if ($deleteid && confirm_sesskey() && $confirm == md5($deleteid)) {
    // Get the list of department ids which are to be removed..
    if (!empty($deleteid)) {
        // Check if department has already been removed.
        if (company::check_valid_department($companyid, $deleteid)) {
            // If not delete it and its sub departments moving users to
            // $departmentid or the company parent id if not set (==0).
            company::delete_department_recursive($deleteid, $deleteid);
            redirect($linkurl);
        }
    }
}
$noticestring = '';

function gentree($children, &$sheet, &$row)
{
    foreach ($children as $department) {
        $sheet->setCellValue('A' . $row, $department->name);
        $sheet->setCellValue('B' . $row, $department->shortname);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $row++;
        if (!empty($department->children)) {
            gentree($department->children, $sheet, $row);
        }
    }
}

if ($mform->is_cancelled()) {
    redirect($dashboardurl);

} else if ($data = $mform->get_data()) {
    if (!empty($data->create) ) {
        redirect(new moodle_url($CFG->wwwroot . '/blocks/iomad_company_admin/company_department_create_form.php',
                                array('deptid' => $departmentid)));
        die;
    } else if (!empty($data->import)) {
        redirect(new moodle_url('/blocks/iomad_company_admin/company_department_import_form.php'));
    } else if (!empty($data->export)) {
        $company = new company($companyid);
        $parentlevel = company::get_company_parentnode($companyid);
        $departmenttree = company::get_all_subdepartments_raw($parentlevel->id);
        // export to excel with PHPSpreadsheet
        require_once $CFG->libdir . '/phpspreadsheet/vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', get_string('departmentname', 'block_iomad_company_admin'));
        $sheet->setCellValue('B1', get_string('departmentshortname', 'block_iomad_company_admin'));
        $row = 2;
        gentree($departmenttree->children, $sheet, $row);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->setTitle('Departments');
        // go to sheet 2
        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', "STT");
        $sheet->setCellValue('B1', "Chức vụ");
        $sheet->setCellValue('C1', 'Mã');
        // query to database user_info_field
        $sql = "SELECT * FROM {user_info_field} WHERE shortname = 'BSRPOS'";
        $user_info_field = $DB->get_record_sql($sql);
        $positions = explode("\n", $user_info_field->param1);
        $positions_code = explode("\n", $user_info_field->param2);
        foreach ($positions as $key => $position) {
            $sheet->setCellValue('A' . ($key + 2), $key + 1);
            $sheet->setCellValue('B' . ($key + 2), $position);
            $sheet->setCellValue('C' . ($key + 2), $positions_code[$key]);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->setTitle('Positions');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'departments_' . 1 . '.xlsx';
        $writer->save($filename);
        // download the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        readfile($filename);
        unlink($filename);

        die;
    } else if (isset($data->delete)) {
        // Check the department is valid.
        if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
            print_error('invaliddepartment', 'block_iomad_company_admin');
        }

        $departmentinfo = $DB->get_record('department', array('id' => $departmentid), '*', MUST_EXIST);
        if (!empty($departmentinfo->parent)) {
            echo $output->header();
            if (empty($departmentid)) {
                notice(get_string('departmentnoselect', 'block_iomad_company_admin'));
            }

            if (company::get_recursive_department_users($departmentid)) {
                // there are users under this department.  We can't delete them.
                notice(get_string('cantdeletedepartment', 'block_iomad_company_admin'), $linkurl);
            } else {
                echo $output->heading(get_string('deletedepartment', 'block_iomad_company_admin'));
                $optionsyes = array('deleteid' => $departmentid, 'confirm' => md5($departmentid), 'sesskey' => sesskey());
                echo $output->confirm(get_string('deletedepartmentcheckfull', 'block_iomad_company_admin', "'$departmentinfo->name'"),
                                      new moodle_url('company_departments.php', $optionsyes), 'company_departments.php');
            }
            echo $output->footer();
            die;
        } else {
            $noticestring = get_string('cantdeletetopdepartment', 'block_iomad_company_admin');
        }
    } else if (isset($data->edit)) {
        // Editing an existing department.
        if (!empty($departmentid)) {
            $departmentrecord = $DB->get_record('department', array('id' => $departmentid));

            // Check the department is valid.
            if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
                print_error('invaliddepartment', 'block_iomad_company_admin');
            } else {
                if (!empty($departmentrecord->parent)) {
                    redirect(new moodle_url($CFG->wwwroot . '/blocks/iomad_company_admin/company_department_create_form.php',
                                            array('departmentid' => $departmentid, 'deptid' => $departmentid)));
                    die;
                } else {
                    $noticestring = get_string('cantedittopdepartment', 'block_iomad_company_admin');
                }
            }
        }
    }
}

// Javascript for fancy select.
// Parameter is name of proper select form element.
$PAGE->requires->js_call_amd('block_iomad_company_admin/department_select', 'init', array('deptid', '', $departmentid));

$mform = new \block_iomad_company_admin\forms\department_display_form($PAGE->url, $companyid, $departmentid, $output, 0, 0, $noticestring);

echo $output->header();

// Check the department is valid.
if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
    print_error('invaliddepartment', 'block_iomad_company_admin');
}

$mform->display();

echo $output->footer();
