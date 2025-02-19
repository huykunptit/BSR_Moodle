
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
 * Strings for component 'qbehaviour_deferredcbm', language 'en'.
 *
 * @package    qbehaviour
 * @subpackage deferredcbm
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */$string['accuracy'] = 'Độ chính xác';
$string['accuracyandbonus'] = 'Độ chính xác + Thưởng';
$string['assumingcertainty'] = 'Bạn chưa chọn mức độ chắc chắn. Giả định: {$a}.';
$string['averagecbmmark'] = 'Điểm trung bình CBM';
$string['basemark'] = 'Điểm cơ bản {$a}';
$string['breakdownbycertainty'] = 'Phân tích theo mức độ chắc chắn';
$string['cbmbonus'] = 'Thưởng CBM';
$string['cbmmark'] = 'Điểm CBM {$a}';
$string['cbmgradeexplanation'] = 'Đối với CBM, điểm được hiển thị ở trên so với điểm tối đa cho tất cả câu trả lời đúng ở C=1.';
$string['cbmgrades'] = 'Điểm CBM';
$string['cbmgrades_help'] = 'Với CBM (Chấm điểm dựa trên mức độ chắc chắn), việc đúng tất cả các câu hỏi với C=1 (chắc chắn thấp) sẽ cho điểm là 100%. Điểm có thể cao nhất là 300% nếu mỗi câu hỏi đều đúng với C=3 (chắc chắn cao). Sự hiểu lầm (phản hồi sai mạnh mẽ) làm giảm điểm nhiều hơn so với phản hồi sai được thừa nhận là không chắc chắn. Điều này có thể dẫn đến điểm tổng thể âm.

**Độ chính xác** là tỷ lệ % câu trả lời đúng bỏ qua độ chắc chắn nhưng được cân nhắc cho điểm cao nhất của mỗi câu hỏi. Phân biệt thành công giữa các phản hồi đáng tin cậy hơn và ít đáng tin cậy hơn cho điểm cao hơn so với việc chọn cùng một độ chắc chắn cho mỗi câu hỏi. Điều này được phản ánh trong **Thưởng CBM**. **Độ chính xác** + **Thưởng CBM** là một phép đo tốt hơn về kiến thức so với **Độ chính xác**. Sự hiểu lầm có thể dẫn đến một thưởng âm, một cảnh báo để chú ý đến những điều đã và chưa biết.';
$string['cbmgrades_link'] = 'qbehaviour/deferredcbm/certaintygrade';
$string['certainty'] = 'Mức độ chắc chắn';
$string['certainty_help'] = 'Chấm điểm dựa trên mức độ chắc chắn yêu cầu bạn chỉ ra bạn nghĩ câu trả lời của mình đáng tin cậy đến đâu. Các cấp độ có sẵn là:
Mức độ chắc chắn    | C=1 (Không chắc chắn) | C=2 (Trung bình) | C=3 (Khá chắc chắn)
------------------- | ------------------ | ----------- | -----------------
Điểm nếu đúng       |   1                |    2        |      3
Điểm nếu sai        |   0                |   -2        |     -6
Xác suất đúng       |  <67%              | 67-80%      |    >80%

Điểm tốt nhất được đạt được bằng cách thừa nhận sự không chắc chắn. Ví dụ, nếu bạn nghĩ rằng có hơn 1 trong 3 khả năng là sai, bạn nên nhập C=1 và tránh rủi ro của điểm âm.';
$string['certainty_link'] = 'qbehaviour/deferredcbm/certainty';
$string['certainty-1'] = 'Không ý kiến';
$string['certainty1'] = 'C=1 (Không chắc chắn: <67%)';
$string['certainty2'] = 'C=2 (Trung bình: >67%)';
$string['certainty3'] = 'C=3 (Khá chắc chắn: >80%)';
$string['certaintyshort-1'] = 'Không ý kiến';
$string['certaintyshort1'] = 'C=1';
$string['certaintyshort2'] = 'C=2';
$string['certaintyshort3'] = 'C=3';
$string['dontknow'] = 'Không ý kiến';
$string['foransweredquestions'] = 'Kết quả chỉ cho {$a} câu hỏi đã trả lời';
$string['forentirequiz'] = 'Kết quả cho toàn bộ bài kiểm tra ({$a} câu hỏi)';
$string['judgementok'] = 'Đồng ý';
$string['judgementsummary'] = 'Các phản hồi: {$a->responses}. Độ chính xác: {$a->fraction}. (Phạm vi lý tưởng {$a->idealrangelow} đến {$a->idealrangehigh}). Bạn đã {$a->judgement} sử dụng mức độ chắc chắn này.';
$string['howcertainareyou'] = 'Mức độ chắc chắn{$a->help}: {$a->choices}';
$string['noquestions'] = 'Không có câu trả lời';
$string['overconfident'] = 'Quá tự tin';
$string['pluginname'] = 'Phản hồi trì hoãn với CBM';
$string['privacy:metadata'] = 'Trình cắm hành vi câu hỏi phản hồi trì hoãn với CBM không lưu trữ bất kỳ dữ liệu cá nhân nào.';
$string['slightlyoverconfident'] = 'Một chút quá tự tin';
$string['slightlyunderconfident'] = 'Một chút thiếu tự tin';
$string['underconfident'] = 'Thiếu tự tin';
$string['weightx'] = 'Trọng số {$a}';
