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
 * @package   local_report_license_usage
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Báo cáo tổng quan hoàn thành';
$string['privacy:metadata:local_report_user_lic_allocs:id'] = 'ID hồ sơ phân bổ giấy phép người dùng báo cáo cục bộ';
$string['privacy:metadata:local_report_user_lic_allocs:courseid'] = 'ID kỳ thi';
$string['privacy:metadata:local_report_user_lic_allocs:action'] = 'Hành động phân bổ';
$string['privacy:metadata:local_report_user_lic_allocs:userid'] = 'ID Người dùng';
$string['privacy:metadata:local_report_user_lic_allocs:licenseid'] = 'ID Giấy phép';
$string['privacy:metadata:local_report_user_lic_allocs:issuedate'] = 'Dấu thời gian phát hành giấy phép';
$string['privacy:metadata:local_report_user_lic_allocs'] = 'Thông tin người dùng phân bổ giấy phép báo cáo cục bộ';
$string['hideexpiry'] = 'Nổi bật hết hạn';
$string['report_completion_overview:view'] = 'Xem báo cáo tổng quan hoàn thành kỳ thi';
$string['showexpiry'] = 'Nổi bật tất cả';
$string['showexpiryonly'] = 'Nổi bật kỳ thi chỉ có thời hạn hợp lệ';
$string['showexpiryonly_help'] = 'Nếu tùy chọn này được chọn, các kỳ thi không có thời hạn hợp lệ sẽ không được hiển thị màu trong báo cáo tổng quan đồ họa theo mặc định.';
$string['showfulldetail'] = 'Hiển thị chi tiết hoàn thành đầy đủ';
$string['showfulldetail_help'] = 'Nếu tùy chọn này được chọn, tất cả thông tin hoàn thành sẽ được hiển thị, nếu không chỉ là ngày hoàn thành và hết hạn.';
$string['warningduration'] = 'Giới hạn cảnh báo hết hạn';
$string['warningduration_help'] = 'Đây là giá trị thời gian trước khi một kỳ thi hết hạn, trong đó báo cáo sẽ hiển thị màu cảnh báo hết hạn thay vì màu OK.';
$string['coursesummary'] = 'Đã ghi danh: {$a->enrolled}
Bắt đầu: {$a->timestarted}
Hoàn thành: {$a->timecompleted}
Hết hạn: {$a->timeexpires}
Điểm: {$a->finalscore}';
$string['coursesummary_noexpiry'] = 'Đã ghi danh: {$a->enrolled}
Bắt đầu: {$a->timestarted}
Hoàn thành: {$a->timecompleted}
Điểm: {$a->finalscore}';
$string['coursesummary_nograde'] = 'Đã ghi danh: {$a->enrolled}
Bắt đầu: {$a->timestarted}
Hoàn thành: {$a->timecompleted}
Hết hạn: {$a->timeexpires}
Kết quả: Đã vượt qua';
$string['coursesummary_nograde_noexpiry'] = 'Đã ghi danh: {$a->enrolled}
Bắt đầu: {$a->timestarted}
Hoàn thành: {$a->timecompleted}
Kết quả: Đã vượt qua';
$string['coursesummary_partial'] = 'Hoàn thành: {$a->timecompleted}
Hết hạn: {$a->timeexpires}';
$string['report_completion_overview_title'] = 'Báo cáo tổng quan hoàn thành';
$string['notcompleted'] = 'Đang tiến hành';
$string['notenrolled']  = 'Chưa ghi danh';
$string['indate'] = 'OK';
$string['expiring'] = 'Sắp hết hạn';
$string['expired'] = 'Đã hết hạn';
$string['coursestatus'] = '{$a} trạng thái';
$string['coursecompletion'] = '{$a} hoàn thành';
$string['courseexpiry'] = '{$a} hết hạn';

