# Nâng cấp giao diện báo cáo AI dành cho giảng viên

Đã đọc prompt ngày 26/10/2026 và triển khai trên mã Laravel hiện tại. Dùng Bootstrap 5.3.2 và các biến màu, font, radius, shadow của public/css/huit_theme.css. Không thay thuật toán PDF, Map-Reduce, prompt LLM, JSON kết quả AI hay schema database trong lần nâng cấp giao diện này.

## Kết quả

- Đúng 3 tab: Tóm tắt theo chương, Công việc đã hoàn thành, Yêu cầu của giảng viên theo đợt. Tab 1 tái sử dụng ai_document_node với đúng chương, thứ tự và nội dung đã lưu. Tab 2 lấy completed_work hiện có; không tự tạo công việc.
- Tab 3: nguyên văn yêu cầu gốc -> giải thích AI hiện có -> từng ý và dẫn chứng riêng. Giữ nguồn thuộc đúng đợt/bản báo cáo. Không sinh câu trả lời trong Blade.
- Thông tin đề tài, nhóm, GVHD, giai đoạn/đợt, thời điểm phân tích lấy từ dữ liệu thật; giờ phân tích ghi rõ timezone app (hiện UTC).
- Chỉ số: đếm câu nguồn riêng biệt đang hiển thị có quote_verified, phần văn bản đã xử lý từ analyzed_chunks/total_chunks, chương/yêu cầu chưa có dẫn chứng. Không gọi chúng là độ chính xác ngữ nghĩa hoặc điểm sinh viên. Thiếu phép đo hiển thị chưa có dữ liệu.
- Cảnh báo đúng câu người dùng yêu cầu; có link mở PDF gốc qua route kiểm tra quyền cũ.
- Ẩn mã nguồn/chunk và chi tiết vận hành trong giao diện giảng viên. Dữ liệu backend không bị xóa. Giao diện sinh viên dùng nhánh render cũ.
- Giữ Xem lớn/Sao chép/Thu gọn và dẫn chứng PDF. Clone cửa sổ xem lớn có ID tab/panel riêng, không chuyển nhầm tab phía sau.
- CSS media queries cho màn hình nhỏ, tab xuống dòng và card xếp dọc; transition 180ms và tôn trọng prefers-reduced-motion. Không thêm framework.

## Tiến độ

- POST phân tích cũ hỗ trợ JSON khi Accept application/json; người dùng/phiên bản vẫn qua authorizedReport và throttle cũ. Form thường vẫn redirect như trước.
- Bootstrap Modal mở khi gửi yêu cầu. GET trạng thái có queued_at và server_time để tính thời gian đã chờ từ thời điểm tác vụ thực tế, giữ qua các lượt worker và refresh. Cached result được nhận diện, không hiển thị thời gian chờ của job cũ cho yêu cầu mới dùng lại cache.
- Một poller cho mỗi URL báo cáo (5 giây; lỗi mạng 15 giây), chia sẻ cho card và cửa sổ xem lớn, dừng khi kết thúc/không có quyền. Đóng popup không hủy tác vụ. Reload trang tiếp tục lấy trạng thái, không POST job mới.
- Thanh tiến độ tổng thể dạng KHÔNG XÁC ĐỊNH khi đang chạy: chưa có cách đo tỷ trọng thời gian Map, tổng hợp, bản đọc, nối yêu cầu đủ đáng tin. Chỉ hiện số phần văn bản đã đọc nếu backend có dữ liệu; không coi 23/23 đã đọc là toàn tác vụ hoàn tất. Chỉ 100% khi backend status=completed.
- Kết thúc: tự GET lại trang, trích đúng card báo cáo để thay nội dung, giữ tab đã chọn; không tải lại trang và không đụng form đánh giá chuyên môn ngoài card. Có nút Xem kết quả, thông báo lỗi thân thiện và thử lại. Thử lại vẫn dùng cơ chế chống job trùng của backend.

## File chỉnh sửa

Mới:
- app/Support/AiReportPresentation.php
- resources/views/partials/ai_teacher_report.blade.php
- resources/views/partials/ai_teacher_requirements.blade.php
- resources/views/partials/ai_teacher_progress_modal.blade.php
- public/css/ai_teacher_report.css
- public/js/ai_teacher_report.js
- tests/Unit/AiTeacherPresentationTest.php
- tests/Feature/AiTeacherReportUiTest.php
- tests/Feature/AiTeacherProgressEndpointTest.php

Chỉnh trên nền thay đổi local được giữ nguyên:
- resources/views/partials/ai_summary_11_sections.blade.php
- resources/views/partials/ai_processing_status.blade.php
- resources/views/partials/ai_lab_requirements.blade.php
- resources/views/partials/ai_lab_sources.blade.php
- app/Http/Controllers/BaoCaoAiController.php (nhánh JSON cho reanalyze)
- app/Services/AiReportProcessingService.php (timestamp/clock trong status)

## Kiểm chứng

- 45 test PHP pass, 188 assertions: kết quả/queue cũ, 3 tab, nguồn không lặp đếm, thiếu phép đo, HTML escape, thứ tự hỏi/trả lời, nhánh sinh viên, JSON endpoint, status không tạo job/đổi dữ liệu, người ngoài/guest bị chặn.
- Node kiểm tra cú pháp và kiểm thử runtime với DOM/timers/network giả lập: một poller cho các bản sao card; elapsed từ timestamp server; không có phần trăm giả khi active; dừng khi complete/403; backoff khi mất mạng; không lộ raw error; chống gửi trùng. Đây là kiểm thử giả lập, không phải gọi LLM mới.
- Browser thật N12: kiểm tra Bootstrap tabs; Enter và ArrowRight; duy nhất ID kể cả clone; Tab 3 mở trang 72/124 và tô vàng câu nguồn. Popup thật nhận cached completion và cập nhật card không reload.
- N12 trước/sau có cùng payload SHA256 b7b30cb9d4fc6249387c533cc4416d4e2dc73df62d193022ef774ffca3804ac5, các bộ đếm generation không đổi, queue 0. Không gọi Gemini mới để thay giao diện.
- Không đánh giá/nhận xét/đổi hạn nộp hoặc sửa kết quả nghiệp vụ của sinh viên. Không migrate, không push GitHub.

## Giới hạn kiểm tra

Đã kiểm tra browser ở kích thước laptop hiện tại, card hẹp và chế độ xem lớn. CSS responsive đã bổ sung, nhưng chưa xác nhận trực tiếp trên thiết bị mobile/tablet. Công cụ IAB không có API đổi viewport và chặn file:// khi mở bản render QA cục bộ; không đưa dữ liệu báo cáo ra thư mục web công khai để vượt hạn chế này. Chưa giả lập job LLM mới kéo dài để chỉ thử popup; các trạng thái queued/running/error/network được kiểm tra runtime giả lập, cached completion kiểm tra trên trang thật.

Bản sao trước sửa: workspace/work/before-teacher-ui. Báo cáo kiểm tra và ảnh: workspace/outputs/teacher-ui-*. Không xóa/ghi đè các thay đổi Git có sẵn của người dùng.


## 2026-10-26: concise atomic requirements and stable tab scrolling

Teacher requirements now render as sequential short headings, a direct answer, and citations beneath. Original teacher instructions remain in the disclosure at the bottom. New linker 1.2 separates compound questions into `report_points`; source counters never become teacher question titles. Older payloads retain a concise fallback until reanalysis.

Each point must quote an actual substring of its parent requirement, use only that parent's retrieved candidate IDs, and provide exact supporting substrings from the canonical matched quotes. A failed supporting quote becomes `not_found` for that point. An additional cached model review rejects topical-only support and retains omitted ideas as `not_found`. This is a model review, not proof of semantic correctness or a grade. 'Không có dẫn chứng' means no accepted source in the selected text, not proof that the full PDF lacks the topic. Images are still left for the teacher to inspect.

Map and chapter caches are reused. Only requirement matching/support review is refreshed; old completed matching caches without `support_review=complete` cannot bypass review. Runtime manifest matches Lab. Canonical pipeline is `pdf-lab-v1.5-requirement-points-support-check`.

Tab initialization runs once per card, including expanded clones. Bootstrap tab events preserve both the inner scroll and page position; a viewport-sized minimum height prevents transient scroll clamping. No full old-panel height is retained.

Validation: Lab 214 passed / 2 skipped; bridge 19 passed; PHP 46 tests / 194 assertions; JS runtime tests cover scroll preservation plus previous polling behavior. Live N12 (124 pages): 23 maps reused, 13 requirement points, 3 points without accepted evidence, complete result saved. Window and inner scroll remained 499.5/388.5 in the page and 499.5/248.5 in the expanded view across tab switches. Source relevance remains model-based; no human semantic accuracy score is claimed.


Display correction requested by user: preserve original numbered teacher requirement headings (one card per original item). Atomic answers and their accepted citations remain inside the matching parent card, without separately numbered headings. Missing subtopics are labeled within that card. Original requirements disclosure stays above the cards; scope conditions are unnumbered and shown once. This is a presentation change; no LLM call or new report analysis is required.

Requirement UI now has two nested tabs: Công việc đã hoàn thành (related_text with accepted references) and Công việc chưa hoàn thành (no accepted references/pending). Original parent numbering is preserved in both; partial requirements can appear in both with different contents. Labels do not constitute a completion grade; inline notes explain this. Expanded clones remap nested tab IDs; tab scroll preservation applies to nested tabs. Verified in live page/expanded view and 46 PHP tests (201 assertions), plus JS runtime checks. No model calls required for this presentation change.

Completion feedback now hides the progress bar and displays a green check success panel. Cached reanalysis explicitly says it reuses saved output and hides the 00:00 elapsed timer. Running states retain indeterminate progress; failures do not display the success check. JS runtime tests exercise complete/cached/active/failure states; PHP suite remains 47 tests / 203 assertions.

Both report tab levels now use a single nowrap row with local horizontal scrolling at all widths. Removed the mobile full-width tab rule. Selecting an offscreen tab scrolls only its tab bar; page and report vertical position are preserved. Verified in the real 427px report column (601px tab content) and nested 393px bar (401px content), plus JS runtime checks. No backend or AI changes.
