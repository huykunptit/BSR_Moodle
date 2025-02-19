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
 * Strings for the quizaccess_seb plugin.
 *
 * @package    quizaccess_seb
 * @author     Luca Bösch <luca.boesch@bfh.ch>
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addtemplate'] = 'Thêm mẫu mới';
$string['allowedbrowserkeysdistinct'] = 'Các khóa phải khác nhau.';
$string['allowedbrowserkeyssyntax'] = 'Một khóa nên là một chuỗi hex có độ dài 64 ký tự.';
$string['cachedef_config'] = 'Bộ nhớ cache cấu hình SEB';
$string['cachedef_configkey'] = 'Bộ nhớ cache khóa cấu hình SEB';
$string['cachedef_quizsettings'] = 'Bộ nhớ cache cài đặt trắc nghiệm SEB';
$string['cantdelete'] = 'Không thể xóa mẫu vì đã được sử dụng cho một hoặc nhiều trắc nghiệm.';
$string['cantedit'] = 'Không thể chỉnh sửa mẫu vì đã được sử dụng cho một hoặc nhiều trắc nghiệm.';
$string['checkingaccess'] = 'Kiểm tra quyền truy cập vào Trình duyệt Trắc nghiệm An toàn...';
$string['clientrequiresseb'] = 'Trắc nghiệm này đã được cấu hình để sử dụng Trình duyệt Trắc nghiệm An toàn với cấu hình của máy khách.';
$string['confirmtemplateremovalquestion'] = 'Bạn có chắc chắn muốn xóa mẫu này không?';
$string['confirmtemplateremovaltitle'] = 'Xác nhận xóa mẫu?';
$string['conflictingsettings'] = 'Bạn không có quyền cập nhật các cài đặt Trình duyệt Trắc nghiệm An toàn hiện có.';
$string['content'] = 'Mẫu';
$string['description'] = 'Mô tả';
$string['disabledsettings'] = 'Các cài đặt bị vô hiệu.';
$string['disabledsettings_help'] = 'Cài đặt trắc nghiệm Trình duyệt An toàn không thể thay đổi nếu trắc nghiệm đã được thực hiện. Để thay đổi cài đặt, tất cả các lần thử nghiệm trắc nghiệm đầu tiên phải được xóa.';
$string['downloadsebconfig'] = 'Tải xuống tệp cấu hình SEB';
$string['duplicatetemplate'] = 'Một mẫu cùng tên đã tồn tại.';
$string['edittemplate'] = 'Chỉnh sửa mẫu';
$string['enabled'] = 'Đã kích hoạt';
$string['error:ws:nokeyprovided'] = 'Ít nhất một khóa Trình duyệt Trắc nghiệm An toàn phải được cung cấp.';
$string['error:ws:quiznotexists'] = 'Không tìm thấy trắc nghiệm phù hợp với ID của mô-đun khóa học: {$a}';
$string['event:accessprevented'] = "Truy cập vào trắc nghiệm đã bị ngăn chặn";
$string['event:templatecreated'] = 'Mẫu SEB đã được tạo';
$string['event:templatedeleted'] = 'Mẫu SEB đã bị xóa';
$string['event:templatedisabled'] = 'Mẫu SEB đã bị vô hiệu hóa';
$string['event:templateenabled'] = 'Mẫu SEB đã được kích hoạt';
$string['event:templateupdated'] = 'Mẫu SEB đã được cập nhật';
$string['exitsebbutton'] = 'Thoát Trình duyệt Trắc nghiệm An toàn';
$string['filemanager_sebconfigfile'] = 'Tải lên tệp cấu hình Trình duyệt Trắc nghiệm An toàn';
$string['filemanager_sebconfigfile_help'] = 'Vui lòng tải lên tệp cấu hình Trình duyệt Trắc nghiệm An toàn của riêng bạn cho trắc nghiệm này.';
$string['filenotpresent'] = 'Vui lòng tải lên một tệp cấu hình SEB.';
$string['fileparsefailed'] = 'Không thể lưu tệp đã tải lên dưới dạng tệp cấu hình SEB.';
$string['httplinkbutton'] = 'Tải xuống cấu hình';
$string['invalid_browser_key'] = "Khóa trình duyệt SEB không hợp lệ";
$string['invalid_config_key'] = "Khóa cấu hình SEB không hợp lệ";
$string['invalidkeys'] = 'Khóa Trình duyệt Trắc nghiệm An toàn không thể được xác minh. Hãy kiểm tra xem bạn có sử dụng Trình duyệt Trắc nghiệm An toàn với tệp cấu hình đúng không.';
$string['invalidtemplate'] = "Mẫu cấu hình SEB không hợp lệ";
$string['manage_templates'] = 'Quản lý các mẫu Trình duyệt Trắc nghiệm An toàn';
$string['managetemplates'] = 'Quản lý mẫu';
$string['missingrequiredsettings'] = 'Cài đặt cấu hình thiếu một số giá trị bắt buộc.';
$string['name'] = 'Tên';
$string['newtemplate'] = 'Mẫu mới';
$string['noconfigfilefound'] = 'Không tìm thấy tệp cấu hình SEB đã tải lên cho trắc nghiệm với cmid: {$a}';
$string['noconfigfound'] = 'Không tìm thấy cấu hình SEB cho trắc nghiệm với cmid: {$a}';
$string['not_seb'] = 'Không có Trình duyệt Trắc nghiệm An toàn nào được sử dụng.';
$string['notemplate'] = 'Không có mẫu';
$string['passwordnotset'] = 'Cài đặt hiện tại yêu cầu các trắc nghiệm sử dụng Trình duyệt Trắc nghiệm An toàn phải có mật khẩu trắc nghiệm được đặt.';
$string['pluginname'] = 'Quy tắc truy cập Trình duyệt Trắc nghiệm An toàn';
$string['privacy:metadata:quizaccess_seb_quizsettings'] = 'Cài đặt Trình duyệt Trắc nghiệm An toàn cho một trắc nghiệm. Điều này bao gồm ID của người dùng cuối cùng tạo hoặc sửa đổi cài đặt.';
$string['privacy:metadata:quizaccess_seb_quizsettings:quizid'] = 'ID của trắc nghiệm cài đặt.';
$string['privacy:metadata:quizaccess_seb_quizsettings:timecreated'] = 'Thời gian Unix khi cài đặt được tạo.';
$string['privacy:metadata:quizaccess_seb_quizsettings:timemodified'] = 'Thời gian Unix khi cài đặt được sửa đổi lần cuối.';
$string['privacy:metadata:quizaccess_seb_quizsettings:usermodified'] = 'ID của người dùng cuối cùng tạo hoặc sửa đổi cài đặt.';
$string['privacy:metadata:quizaccess_seb_template'] = 'Cài đặt mẫu Trình duyệt Trắc nghiệm An toàn. Điều này bao gồm ID của người dùng cuối cùng tạo hoặc sửa đổi mẫu.';
$string['privacy:metadata:quizaccess_seb_template:timecreated'] = 'Thời gian Unix khi mẫu được tạo.';
$string['privacy:metadata:quizaccess_seb_template:timemodified'] = 'Thời gian Unix khi mẫu được sửa đổi lần cuối.';
$string['privacy:metadata:quizaccess_seb_template:usermodified'] = 'ID của người dùng cuối cùng tạo hoặc sửa đổi mẫu.';
$string['quizsettings'] = 'Cài đặt trắc nghiệm';
$string['restoredfrom'] = '{$a->name} (khôi phục qua cmid {$a->cmid})';
$string['seb'] = 'Trình duyệt Trắc nghiệm An toàn';
$string['seb:bypassseb'] = 'Bỏ qua yêu cầu xem trắc nghiệm trong Trình duyệt Trắc nghiệm An toàn.';
$string['seb:manage_filemanager_sebconfigfile'] = 'Thay đổi cài đặt trắc nghiệm SEB: Chọn tệp cấu hình SEB';
$string['seb:manage_seb_activateurlfiltering'] = 'Thay đổi cài đặt trắc nghiệm SEB: Kích hoạt lọc URL';
$string['seb:manage_seb_allowedbrowserexamkeys'] = 'Thay đổi cài đặt trắc nghiệm SEB: Khóa trình duyệt trắc nghiệm được phép';
$string['seb:manage_seb_allowreloadinexam'] = 'Thay đổi cài đặt trắc nghiệm SEB: Cho phép tải lại';
$string['seb:manage_seb_allowspellchecking'] = 'Thay đổi cài đặt trắc nghiệm SEB: Bật kiểm tra chính tả';
$string['seb:manage_seb_allowuserquitseb'] = 'Thay đổi cài đặt trắc nghiệm SEB: Cho phép thoát';
$string['seb:manage_seb_enableaudiocontrol'] = 'Thay đổi cài đặt trắc nghiệm SEB: Bật điều khiển âm thanh';
$string['seb:manage_seb_expressionsallowed'] = 'Thay đổi cài đặt trắc nghiệm SEB: Các biểu thức được phép';
$string['seb:manage_seb_expressionsblocked'] = 'Thay đổi cài đặt trắc nghiệm SEB: Các biểu thức bị chặn';
$string['seb:manage_seb_filterembeddedcontent'] = 'Thay đổi cài đặt trắc nghiệm SEB: Lọc cả nội dung nhúng';
$string['seb:manage_seb_linkquitseb'] = 'Thay đổi cài đặt trắc nghiệm SEB: Liên kết thoát';
$string['seb:manage_seb_muteonstartup'] = 'Thay đổi cài đặt trắc nghiệm SEB: Tắt âm thanh khi khởi động';
$string['seb:manage_seb_quitpassword'] = 'Thay đổi cài đặt trắc nghiệm SEB: Mật khẩu thoát';
$string['seb:manage_seb_regexallowed'] = 'Thay đổi cài đặt trắc nghiệm SEB: Các biểu thức chính quy được phép';
$string['seb:manage_seb_regexblocked'] = 'Thay đổi cài đặt trắc nghiệm SEB: Các biểu thức chính quy bị chặn';
$string['seb:manage_seb_requiresafeexambrowser'] = 'Thay đổi cài đặt trắc nghiệm SEB: Yêu cầu Trình duyệt Trắc nghiệm An toàn';
$string['seb:manage_seb_showkeyboardlayout'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị bố cục bàn phím';
$string['seb:manage_seb_showreloadbutton'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị nút tải lại';
$string['seb:manage_seb_showsebtaskbar'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị thanh công cụ SEB';
$string['seb:manage_seb_showtime'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị thời gian';
$string['seb:manage_seb_showwificontrol'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị điều khiển Wi-Fi';
$string['seb:manage_seb_showsebdownloadlink'] = 'Thay đổi cài đặt trắc nghiệm SEB: Hiển thị liên kết tải xuống';
$string['seb:manage_seb_templateid'] = 'Thay đổi cài đặt trắc nghiệm SEB: Chọn mẫu SEB';
$string['seb:manage_seb_userconfirmquit'] = 'Thay đổi cài đặt trắc nghiệm SEB: Xác nhận thoát';
$string['seb:managetemplates'] = 'Quản lý các mẫu cấu hình SEB';
$string['seb_activateurlfiltering'] = 'Kích hoạt lọc URL';
$string['seb_activateurlfiltering_help'] = 'Nếu được kích hoạt, các URL sẽ được lọc khi tải các trang web. Bộ lọc phải được xác định dưới đây.';
$string['seb_allowedbrowserexamkeys'] = 'Khóa trình duyệt trắc nghiệm được phép';
$string['seb_allowedbrowserexamkeys_help'] = 'Trong trường này, bạn có thể nhập các khóa trình duyệt trắc nghiệm được phép cho các phiên bản của Trình duyệt Trắc nghiệm An toàn được phép truy cập vào trắc nghiệm này. Nếu không có khóa nào được nhập, thì các khóa trình duyệt trắc nghiệm không được kiểm tra.';
$string['seb_allowreloadinexam'] = 'Cho phép tải lại trong kỳ thi';
$string['seb_allowreloadinexam_help'] = 'Nếu được kích hoạt, trang web có thể được tải lại (nút tải lại trong thanh công cụ SEB, thanh công cụ trình duyệt, menu trượt bên của iOS, phím tắt bàn phím F5/cmd+R). Lưu ý rằng việc lưu trữ ngoại tuyến có thể bị hỏng nếu người dùng cố gắng tải lại trang mà không có kết nối internet.';
$string['seb_allowspellchecking'] = 'Bật kiểm tra chính tả';
$string['seb_allowspellchecking_help'] = 'Nếu được kích hoạt, kiểm tra chính tả trong trình duyệt SEB được phép.';
$string['seb_allowuserquitseb'] = 'Cho phép thoát khỏi SEB';
$string['seb_allowuserquitseb_help'] = 'Nếu được kích hoạt, người dùng có thể thoát khỏi SEB với nút "Thoát" trong thanh công cụ SEB hoặc bằng cách nhấn các phím Ctrl-Q hoặc bằng cách nhấn nút đóng cửa sổ chính của trình duyệt.';
$string['seb_enableaudiocontrol'] = 'Bật điều khiển âm thanh';
$string['seb_enableaudiocontrol_help'] = 'Nếu được kích hoạt, điều khiển âm thanh của SEB sẽ được hiển thị.';
$string['seb_expressionsallowed'] = 'Biểu thức được phép';
$string['seb_expressionsallowed_help'] = 'Trong trường này, bạn có thể nhập các biểu thức để bỏ qua các URL nếu chúng phù hợp. Bạn có thể nhập một hoặc nhiều biểu thức chính quy. Bạn cũng có thể nhập các biểu thức chính quy đa dòng, bằng cách cách biệt chúng bằng dấu phẩy.';
$string['seb_expressionsblocked'] = 'Biểu thức bị chặn';
$string['seb_expressionsblocked_help'] = 'Trong trường này, bạn có thể nhập các biểu thức để chặn các URL nếu chúng phù hợp. Bạn có thể nhập một hoặc nhiều biểu thức chính quy. Bạn cũng có thể nhập các biểu thức chính quy đa dòng, bằng cách cách biệt chúng bằng dấu phẩy.';
$string['seb_filterembeddedcontent'] = 'Lọc cả nội dung nhúng';
$string['seb_filterembeddedcontent_help'] = 'Nếu được kích hoạt, tất cả các yêu cầu nhúng được chặn.';
$string['seb_linkquitseb'] = 'Liên kết thoát';
$string['seb_linkquitseb_help'] = 'Trong trường này, bạn có thể nhập URL hoặc một số các giao thức để thoát khỏi SEB. Nếu người dùng nhấp vào liên kết có chứa một trong các URL này hoặc các giao thức, SEB sẽ thoát.';
$string['seb_muteonstartup'] = 'Tắt âm thanh khi khởi động';
$string['seb_muteonstartup_help'] = 'Nếu được kích hoạt, âm thanh được tắt khi trình duyệt SEB khởi động.';
$string['seb_quitpassword'] = 'Mật khẩu thoát';
$string['seb_quitpassword_help'] = 'Trong trường này, bạn có thể thiết lập mật khẩu thoát khỏi SEB.';
$string['seb_regexallowed'] = 'Các biểu thức chính quy được phép';
$string['seb_regexallowed_help'] = 'Trong trường này, bạn có thể nhập các biểu thức chính quy để bỏ qua các URL nếu chúng phù hợp. Bạn có thể nhập một hoặc nhiều biểu thức chính quy. Bạn cũng có thể nhập các biểu thức chính quy đa dòng, bằng cách cách biệt chúng bằng dấu phẩy.';
$string['seb_regexblocked'] = 'Các biểu thức chính quy bị chặn';
$string['seb_regexblocked_help'] = 'Trong trường này, bạn có thể nhập các biểu thức chính quy để chặn các URL nếu chúng phù hợp. Bạn có thể nhập một hoặc nhiều biểu thức chính quy. Bạn cũng có thể nhập các biểu thức chính quy đa dòng, bằng cách cách biệt chúng bằng dấu phẩy.';
$string['seb_requiresafeexambrowser'] = 'Yêu cầu Trình duyệt Trắc nghiệm An toàn';
$string['seb_requiresafeexambrowser_help'] = 'Nếu được kích hoạt, học viên sẽ được yêu cầu sử dụng Trình duyệt Trắc nghiệm An toàn để truy cập trắc nghiệm.';
$string['seb_showkeyboardlayout'] = 'Hiển thị bố cục bàn phím';
$string['seb_showkeyboardlayout_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị một bảng phím ảo.';
$string['seb_showreloadbutton'] = 'Hiển thị nút tải lại';
$string['seb_showreloadbutton_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị một nút tải lại trong thanh công cụ của mình.';
$string['seb_showsebtaskbar'] = 'Hiển thị thanh công cụ SEB';
$string['seb_showsebtaskbar_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị thanh công cụ của mình.';
$string['seb_showtime'] = 'Hiển thị thời gian';
$string['seb_showtime_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị thời gian trong thanh công cụ của mình.';
$string['seb_showwificontrol'] = 'Hiển thị điều khiển Wi-Fi';
$string['seb_showwificontrol_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị điều khiển Wi-Fi.';
$string['seb_showsebdownloadlink'] = 'Hiển thị liên kết tải xuống SEB';
$string['seb_showsebdownloadlink_help'] = 'Nếu được kích hoạt, SEB sẽ hiển thị một liên kết để tải xuống chính trình duyệt.';
$string['seb_templateid'] = 'Mẫu SEB';
$string['seb_templateid_help'] = 'Chọn một mẫu SEB để sử dụng cho trắc nghiệm này.';
$string['seb_userconfirmquit'] = 'Xác nhận thoát';
$string['seb_userconfirmquit_help'] = 'Nếu được kích hoạt, người dùng sẽ được yêu cầu xác nhận trước khi thoát khỏi SEB.';
$string['selecttemplate'] = 'Chọn một mẫu SEB';
$string['showpassword'] = 'Hiện mật khẩu';
$string['template'] = 'Mẫu';
$string['templatesaved'] = 'Mẫu đã được lưu';
$string['unknownerror'] = 'Có lỗi không rõ xảy ra.';
$string['userconfirmedquitseb'] = 'Người dùng đã xác nhận việc thoát khỏi Trình duyệt Trắc nghiệm An toàn';
$string['userdidnotconfirmquitseb'] = 'Người dùng không xác nhận việc thoát khỏi Trình duyệt Trắc nghiệm An toàn';
$string['userquitseb'] = 'Người dùng đã thoát khỏi Trình duyệt Trắc nghiệm An toàn';
$string['usetemplate'] = 'Sử dụng mẫu';
$string['usetemplate_help'] = 'Nếu được kích hoạt, một mẫu sẽ được sử dụng cho trắc nghiệm này.';
$string['whitelist'] = 'Danh sách trắng';
$string['whitelist_help'] = 'Nếu được kích hoạt, chỉ các URL được liệt kê sẽ được phép truy cập. Nếu không, mọi URL đều được phép truy cập trừ những URL được liệt kê trong danh sách đen.';
