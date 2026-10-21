# Tóm tắt báo cáo tiến độ theo đề cương

Chức năng chính: báo cáo PDF/DOCX → bóc tách văn bản → LLM → ba nhóm JSON → lưu theo phiên bản → giao diện sinh viên/giảng viên.

Ba nhóm: `completed_work` (công việc đã thực hiện), `difficulties` (khó khăn), `next_steps` (kế hoạch). Tổng quan, nguồn dẫn chứng và tình trạng xử lý là metadata hỗ trợ hiển thị. Giao diện chỉ mở khung nhóm có thông tin.

Xem [mô tả triển khai hiện tại](NGHIEN_CUU_AI_BAO_CAO_TIEN_DO.md).

## Kiểm thử

```text
python -B -m unittest discover -s tests/Unit -p "test_*.py"
php vendor/phpunit/phpunit/phpunit --no-configuration --do-not-cache-result tests/Unit --filter "Ai(SummaryIntegrity|RequirementsReview|EvaluationService)Test"
```

## Đo hiệu năng và đánh giá có đáp án chuẩn

```text
php artisan ai:benchmark-reports --dir=THU_MUC_MAU --method=heuristic_nlp --export=DUONG_DAN_KET_QUA.md
php artisan ai:benchmark-reports --dir=THU_MUC_MAU --method=map_reduce --ground-truth=DAP_AN.json --export=DUONG_DAN_KET_QUA.md
```

Lệnh không sửa dữ liệu nghiệp vụ. `map_reduce` gọi API theo cấu hình và có thể dùng fallback; kết quả ghi rõ nguồn thực tế. Không có đáp án chuẩn thì Precision/Recall/F1 là `null`, hiển thị “Chưa đánh giá”.

Đáp án JSON dùng tên tệp (hoặc đường dẫn tương đối trong thư mục mẫu) làm khóa; mỗi tệp có đủ ba nhóm, mỗi mục là câu evidence nguyên văn, nhóm không có nội dung là []. Ví dụ:

```json
{"bao-cao.docx":{"completed_work":["Nhóm đã hoàn thành thiết kế cơ sở dữ liệu."],"difficulties":[],"next_steps":["Tuần tới nhóm sẽ kiểm thử chức năng đăng ký."]}}
```

Điểm hiện tại là micro F1 cho việc khớp câu dẫn chứng và đúng nhóm, có khử trùng; không phải điểm ngữ nghĩa tổng quát của LLM. Muốn đánh giá diễn đạt/tóm tắt cần thêm tiêu chí và người chấm độc lập.

Các báo cáo benchmark/18 kịch bản cũ là lịch sử trước khi thay đổi cấu trúc; không dùng các điểm ước lượng cũ để khẳng định độ chính xác hiện tại.
