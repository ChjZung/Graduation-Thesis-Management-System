# Xử lý báo cáo AI ở nền

Luồng nộp theo đợt, nộp báo cáo nhỏ và hai nút Phân tích lại / Đối chiếu lại sử dụng `AnalyzeReportVersion` trên connection `ai`, queue `ai-reports`. Connection này dùng bảng `jobs` hiện có, độc lập với `QUEUE_CONNECTION=sync`. Giao dịch nộp theo đợt lưu bài và job cùng nhau, không gọi Gemini trong giao dịch.

## Chạy trên Laragon

Mở terminal ở thư mục project và chạy:

```powershell
php artisan ai:work
```

Giữ tiến trình này chạy khi sử dụng chức năng AI. Sau khi khởi động lại máy hoặc dừng các tiến trình PHP, chạy lại lệnh. Worker dùng khóa file để tránh mở trùng trên cùng máy. Để kiểm tra một job có sẵn rồi thoát: `php artisan ai:work --once`.

Khi triển khai server, dùng process manager của hệ điều hành cho lệnh trên, bật tự khởi động lại. Worker timeout 390 giây; `retry_after` của connection là 450 giây để tránh một job đang chạy bị nhận lại quá sớm. Không dùng worker connection mặc định để thay thế lệnh này.

Model chính hiện tại là `gemini-3.5-flash-lite`. Khi chọn Flash-Lite, pipeline không tự chuyển ngược sang Flash thường khi dịch vụ bận.

## Quota và thử lại

`config/ai_processing.php` có các cấu hình tùy chọn qua môi trường:

| Biến | Mặc định | Ý nghĩa |
|---|---:|---|
| `GEMINI_QUOTA_SCOPE` | `default-gemini-project` | Một mã dùng chung cho mọi API key của cùng Google project |
| `GEMINI_SAFE_RPM` | 4 | Ngưỡng gọi/phút thận trọng cho model chính |
| `GEMINI_LITE_SAFE_RPM` | 12 | Ngưỡng gọi/phút cho Flash-Lite |
| `GEMINI_SAFE_TPM` | 200000 | Ngân sách ước lượng input token/phút |

Điều chỉnh thấp hơn hạn mức thực tế đang hiển thị trong AI Studio. TPM được ước lượng thận trọng từ số ký tự, không phải bộ đếm token chính xác của Google. Việc gọi API bằng ứng dụng khác ngoài project này không nằm trong bộ điều tiết cục bộ.

Các tiến trình Python trên cùng máy dùng SQLite riêng `storage/app/private/ai_limits.sqlite` để đặt chỗ nguyên tử theo project/model và chia sẻ thời gian cooldown. Nếu chạy nhiều máy chủ, cần thay bộ điều tiết cục bộ bằng kho điều phối chung (ví dụ Redis). Không đặt mã quota theo từng key để cố tách quota của cùng Google project.

HTTP 429 không tự chuyển model và không tự gọi lại bên trong lớp HTTP. Metadata `QuotaFailure` phân biệt hạn mức ngày/phút, `RetryInfo` hoặc `Retry-After` cung cấp thời gian chờ. Khi không có metadata, hiển thị chưa xác định được loại quota và thử tiếp có giới hạn. `RESOURCE_EXHAUSTED` riêng lẻ không được coi là hết quota ngày.

Hạn mức ngày sử dụng mốc nửa đêm Pacific khi Python có dữ liệu múi giờ; nếu không có (có thể gặp trên Windows), dùng thời gian chờ thận trọng 24 giờ. Thời điểm hiển thị là lịch thử tiếp dự kiến, không đảm bảo Google sẽ cấp lại quota. Hệ thống thử tối đa 20 lượt xử lý cho mỗi yêu cầu, sau đó giữ kết quả và yêu cầu xem lại nguyên nhân. Lỗi xác thực, request không hợp lệ, file không đọc được dừng sớm.

## Tiếp tục và giữ kết quả

- Checkpoint của phản hồi thành công nằm trong `storage/app/private/ai_cache`, có thời hạn 3 ngày; gắn với hash nội dung file, phiên bản pipeline, prompt, model và hash tài khoản. Thay nội dung file/đề bài/model không dùng nhầm checkpoint. Đề bài đối chiếu nằm trong prompt; thay đề bài có thể vẫn tái sử dụng phần tóm tắt không phụ thuộc đề bài.
- `_processing` trong JSON của từng khối chứa token thế hệ xử lý, trạng thái, số lượt, nguyên nhân và lịch thử tiếp; không thay trạng thái duyệt bài của giảng viên.
- File hoặc đề bài đổi trong lúc chạy thì không ghi kết quả cũ vào lượt mới.
- Kết quả thiếu hơn không thay phần tóm tắt hoàn chỉnh hơn. Đối chiếu giữ bằng chứng đã kiểm chứng khi cùng file/cùng đề bài, không trộn bằng chứng từ đề bài cũ.
- Bảng `TomTatBaoCao` là snapshot tương thích của bản nộp mới nhất, mỗi phiên bản vẫn giữ JSON riêng.
- Trích dẫn phải qua kiểm chứng hiện có; lỗi API không biến thành kết luận báo cáo thiếu yêu cầu.

Trang theo dõi tự kiểm tra trạng thái mỗi 5 giây trong lúc chờ/chạy. Nút **Cập nhật kết quả mới** tải lại kết quả đã lưu, không gọi AI. Giao diện không tự tải lại trang khi giảng viên đang đọc hoặc nhập nhận xét.

Luồng xem thử file trước khi nộp ở form cũ (`ajaxAiSummary`) vẫn là thao tác xem trước có chủ đích; dùng chung bộ điều tiết Python nhưng chưa chuyển thành job bài nộp vì chưa có bản nộp. Các luồng nộp và phân tích lại bản nộp đều chạy nền.

## Kiểm tra

```powershell
python -B -X utf8 -m unittest discover -s tests/Unit -p 'test_*.py'
php vendor/phpunit/phpunit/phpunit --no-configuration --do-not-cache-result tests/Unit --filter 'Ai(QueuePolicy|SummaryIntegrity|RequirementsReview|EvaluationService)Test'
php vendor/phpunit/phpunit/phpunit --no-configuration --do-not-cache-result tests/Feature/AiReportQueueTest.php
```

Feature test dùng bản sao báo cáo cục bộ trong transaction rồi rollback, không gọi API. Không chạy migration làm mới database để kiểm thử. Kiểm thử lỗi mô phỏng xác nhận hành vi hệ thống, không chứng minh độ chính xác nội dung của model.

Tài liệu tham khảo: [Google Gemini rate limits](https://ai.google.dev/gemini-api/docs/rate-limits), [Laravel queues](https://laravel.com/docs/11.x/queues).
