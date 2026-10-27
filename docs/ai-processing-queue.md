# Xử lý báo cáo AI ở nền

Luồng nộp theo đợt, nộp báo cáo nhỏ và hai nút Phân tích lại / Đối chiếu lại sử dụng `AnalyzeReportVersion` trên connection `ai`, queue `ai-reports`. Connection này dùng bảng `jobs` hiện có, độc lập với `QUEUE_CONNECTION=sync`. Giao dịch nộp theo đợt lưu bài và job cùng nhau, không gọi Gemini trong giao dịch.

## Chạy trên Laragon

Nhấp đúp `CHAY_AI.bat` trong thư mục project, giữ cửa sổ đó mở khi dùng AI. Script chỉ chạy worker, không migrate/seed hoặc thay đổi dữ liệu nghiệp vụ. Nếu đã có worker, khóa file sẽ chặn chạy trùng.

Hoặc mở terminal ở thư mục project và chạy:

```powershell
php artisan ai:work
```

Giữ tiến trình này chạy khi sử dụng chức năng AI. Sau khi khởi động lại máy hoặc dừng các tiến trình PHP, chạy lại lệnh. Worker dùng khóa file để tránh mở trùng trên cùng máy. Để kiểm tra một job có sẵn rồi thoát: `php artisan ai:work --once`.

Nếu giao diện báo **Tiến trình xử lý AI đang tắt**, chạy lại file/lệnh trên; các bài đang chờ sẽ tiếp tục, không cần nộp lại. Trạng thái này được kiểm tra qua khóa hệ điều hành, không suy đoán từ quota.

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

Điều chỉnh thấp hơn hạn mức thực tế đang hiển thị trong AI Studio. V6 chia tài liệu bằng ước lượng UTF-8 có nhãn rõ, sau đó mặc định gọi `countTokens` của đúng model trước khi gửi toàn bộ prompt; cache số đếm và dự trữ thêm ngân sách output khi điều tiết TPM. `usageMetadata` ghi số token thực tế của các phản hồi trong lượt. Việc gọi API bằng ứng dụng khác ngoài project này không nằm trong bộ điều tiết cục bộ.

Các tiến trình Python trên cùng máy dùng SQLite riêng `storage/app/private/ai_limits.sqlite` để đặt chỗ nguyên tử theo project/model và chia sẻ thời gian cooldown. Nếu chạy nhiều máy chủ, cần thay bộ điều tiết cục bộ bằng kho điều phối chung (ví dụ Redis). Không đặt mã quota theo từng key để cố tách quota của cùng Google project.

HTTP 429 không tự chuyển model và không tự gọi lại bên trong lớp HTTP. Metadata `QuotaFailure` phân biệt hạn mức ngày/phút, `RetryInfo` hoặc `Retry-After` cung cấp thời gian chờ. Khi không có metadata, hiển thị chưa xác định được loại quota và thử tiếp có giới hạn. `RESOURCE_EXHAUSTED` riêng lẻ không được coi là hết quota ngày.

Hạn mức ngày sử dụng mốc nửa đêm Pacific khi Python có dữ liệu múi giờ; nếu không có (có thể gặp trên Windows), dùng thời gian chờ thận trọng 24 giờ. Thời điểm hiển thị là lịch thử tiếp dự kiến, không đảm bảo Google sẽ cấp lại quota. Hệ thống thử tối đa 20 lượt xử lý cho mỗi yêu cầu, sau đó giữ kết quả và yêu cầu xem lại nguyên nhân. Lỗi xác thực, request không hợp lệ, file không đọc được dừng sớm.

## Tiếp tục và giữ kết quả

### Khi đã bấm chạy nhưng chưa có bản tóm tắt

Khung trạng thái hiển thị bước hiện tại, số phần đã đọc/tổng số phần, số lượt API thành công và số bước dùng lại từ cache. Ví dụ `Đã đọc 8/30 phần` là tiến độ đọc tài liệu, không phải tỷ lệ hoàn thành khóa luận. Lượt API và số phần tài liệu là hai số khác nhau vì còn bước tổng hợp và đối chiếu.

Nếu Python vượt thời gian của một lượt nền, mã lỗi là `timeout`: hệ thống chờ rồi tự tiếp tục bằng checkpoint, không coi là lỗi đọc PDF và không yêu cầu nộp lại. Cảnh báo `extraction_failed` chỉ dùng khi bộ trích xuất thực sự trả lỗi tương ứng. Khi worker đang chờ hoặc kết thúc một phần, tiến độ trước đó vẫn có thể xem được.

Khi kiểm chứng câu trích, dùng văn bản trong `source_spans` của chunk để so khớp; nhãn `[Trang N]` chèn vào prompt không phải văn bản của tác giả. Câu nhiều dòng vẫn phải khớp văn bản PDF gốc ở đúng trang và nằm trong phạm vi chunk; không được tìm rộng ra toàn trang để bỏ qua phạm vi đã đọc.

Với tài liệu dài, hoàn tất Map–Reduce và đối chiếu có thể cần nhiều lượt nền. Giữ cửa sổ `CHAY_AI.bat` hoạt động. Chọn **Cập nhật kết quả mới** để xem bản đã lưu; nút này không gọi lại API. Khi đang đọc lượt đầu, giao diện chưa hiển thị các nhóm rỗng như một kết quả phân tích.

Pipeline hiện tại dùng [V6 theo chương/mục và kết quả cố định](ai_research/AI_DOCUMENT_SUMMARY_V6.md). Bản V5 vẫn có nhánh đọc tương thích. Tóm tắt cấu trúc độc lập với yêu cầu đợt; khi yêu cầu đổi, có thể giữ nguyên bản tóm tắt cùng PDF và chạy lại phần đối chiếu. JSON V5 đổi ngữ cảnh vẫn được đưa qua luồng nâng cấp phù hợp.

- Checkpoint thành công V6 nằm trong `storage/app/private/ai_cache`, giữ cố định theo hash nội dung, phiên bản pipeline, prompt và model; không hết hạn sau 3 ngày. Snapshot `canonical-summary-*.json` chỉ được tạo sau khi kiểm chứng văn bản và tổng hợp hoàn tất. Cache trích xuất/số đếm token và cache lịch sử vẫn có TTL riêng. Đề bài đối chiếu nằm trong prompt; thay đề bài không dùng nhầm đối chiếu cũ.
- Khi cùng phiên bản có `_canonical_fingerprint` khớp, nút Phân tích lại trả kết quả đã lưu, không tạo job và không gọi Gemini. Bản chưa hoàn tất tiếp tục checkpoint, không đóng băng kết quả rỗng/lỗi. Thay đổi thuật toán có ý nghĩa phải cập nhật `pipeline_version` trong `config/ai_processing.php` để không dùng nhầm snapshot cũ.
- Trước khi thay bản phân tích đã lưu, giữ lịch sử riêng trong `storage/app/private/ai_history`. Không xóa cache hoặc lịch sử như một cách xử lý quota.
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
