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
 * @package   local_report_users
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Hành động';
$string['blocktitle'] = 'Báo cáo người dùng';
$string['certificate'] = 'Chứng chỉ';
$string['clearcourse'] = 'Xóa kỳ thi';
$string['clearconfirm'] = 'Người dùng sẽ bị gỡ khỏi kỳ thi và tất cả các tương tác của họ trong kỳ thi sẽ bị xóa. Điều này không xóa bản ghi khỏi các báo cáo. Hành động này KHÔNG thể hoàn tác.<br>Bạn có chắc chắn muốn tiếp tục không?';
$string['clearreallocateconfirm'] = 'Người dùng sẽ bị gỡ khỏi kỳ thi và tất cả thông tin của họ trong kỳ thi sẽ bị xóa. Điều này không xóa bản ghi khỏi các báo cáo. Nếu sử dụng giấy phép và nếu thích hợp, một giấy phép mới sẽ được phân bổ cho người dùng. Hành động này KHÔNG thể hoàn tác.<br>Bạn có chắc chắn muốn tiếp tục không?';
$string['clear_successful'] = 'Người dùng đã được xóa khỏi kỳ thi thành công.';
$string['completed'] = 'Hoàn thành';
$string['coursedetails'] = 'Báo cáo đầy đủ của kỳ thi';
$string['datecompleted'] = 'Ngày hoàn thành';
$string['datestarted'] = 'Ngày được phân bổ/bắt đầu kỳ thi';
$string['delete_successful'] = 'Người dùng đã được xóa khỏi kỳ thi thành công.';
$string['department'] = 'Phòng ban';
$string['downloadcert'] = 'Xem chứng chỉ dưới dạng PDF';
$string['inprogress'] = 'Đang tiến hành';
$string['newentry_successful'] = 'Bản ghi kỳ thi mới đã được tạo thành công';
$string['nocerttodownload'] = 'Chứng chỉ chưa được đạt được';
$string['nofurtherdetail'] = 'Không có chi tiết thêm để hiển thị';
$string['notstarted'] = 'Chưa bắt đầu';
$string['pluginname'] = 'Báo cáo người dùng';
$string['purgerecord'] = 'Xóa bản ghi';
$string['purgerecordconfirm'] = 'Bản ghi báo cáo kỳ thi của người dùng sẽ bị <b>xóa vĩnh viễn khỏi tất cả các báo cáo</b>. Hành động này KHÔNG thể hoàn tác.<br>Bạn có chắc chắn muốn tiếp tục không?';
$string['privacy:metadata'] = 'Báo cáo hoàn thành người dùng BSR chỉ hiển thị dữ liệu được lưu trữ tại các vị trí khác.';
$string['redocert'] = 'Tạo lại chứng chỉ';
$string['redocert_successful'] = 'Tệp chứng chỉ đã được tạo lại thành công. Tùy thuộc vào cài đặt bộ nhớ cache của trình duyệt của bạn, có thể có vẻ như bạn vẫn nhận được tệp gốc khi tải xuống.';
$string['redocertificateconfirm'] = 'Bạn có chắc chắn muốn tạo lại tệp chứng chỉ đã lưu cho người dùng này?<br>Hành động này KHÔNG thể hoàn tác.';
$string['report_users_title'] = 'Báo cáo người dùng';
$string['report_users:view'] = 'Xem báo cáo người dùng';
$string['report_users:deleteentries'] = 'Xóa thông tin người dùng từ một kỳ thi và giải phóng bất kỳ giấy phép nào.';
$string['report_users:clearentries'] = 'Xóa thông tin người dùng từ một kỳ thi.';
$string['report_users:deleteentriesfull'] = 'Xóa bản ghi người dùng đã lưu trữ cho một kỳ thi.';
$string['report_users:redocertificates'] = 'Tạo lại chứng chỉ người dùng đã lưu trữ cho một kỳ thi.';
$string['report_users:addentry'] = 'Thêm một bản ghi kỳ thi đã lưu cho một người dùng.';
$string['report_users:updateentries'] = 'Cập nhật các bản ghi người dùng đã lưu.';
$string['repusercompletion'] = 'Báo cáo hoàn thành theo người dùng';
$string['resetcourse'] = 'Xóa kỳ thi';
$string['resetcourseconfirm'] = 'Người dùng sẽ bị gỡ khỏi kỳ thi và tất cả dữ liệu báo cáo của họ sẽ bị xóa. Hành động này KHÔNG thể hoàn tác.<br>Bạn có chắc chắn muốn tiếp tục không?';
$string['revokelicense'] = 'Thu hồi giấy phép';
$string['revokeconfirm'] = 'Giấy phép sẽ được gỡ bỏ khỏi người dùng và sẽ được phát hành lại cho cơ sở dữ liệu giấy phép. Dữ liệu báo cáo cũng sẽ bị xóa. Hành động này KHÔNG thể hoàn tác.<br>Bạn có chắc chắn muốn tiếp tục không?';
$string['revoke_successful'] = 'Giấy phép đã được thu hồi thành công cho người dùng này và dữ liệu kỳ thi và báo cáo của họ đã được xóa.';
$string['user_detail_title'] = 'Báo cáo người dùng';
$string['usercoursedetails'] = 'Chi tiết người dùng';
$string['userdetails'] = 'Thông tin báo cáo cho ';
$string['viewfullcourse'] = 'Xem tổng quan đầy đủ cho kỳ thi';


