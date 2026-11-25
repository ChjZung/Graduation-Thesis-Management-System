# Tóm tắt báo cáo tiến độ theo đề cương

Chức năng chính: báo cáo PDF/DOCX → bóc tách văn bản → LLM → ba nhóm JSON → lưu theo phiên bản → giao diện sinh viên/giảng viên.

Ba nhóm: `completed_work` (công việc đã thực hiện), `difficulties` (khó khăn), `next_steps` (kế hoạch). Tổng quan, nguồn dẫn chứng và tình trạng xử lý là metadata hỗ trợ hiển thị. Giao diện chỉ mở khung nhóm có thông tin.

Xem [mô tả triển khai hiện tại](NGHIEN_CUU_AI_BAO_CAO_TIEN_DO.md).

## Kiểm thử

```text
python -B -m unittest discover -s tests/Unit -p "test_*.py"
php vendor/phpunit/phpunit/phpunit --no-configuration --do-not-cache-result tests/Unit --filter "Ai(SummaryIntegrity|RequirementsReview)Test"
```

## Kết quả đo trước đây

Lệnh Artisan benchmark đã được gỡ khỏi ứng dụng. Các báo cáo benchmark và 18 kịch bản được lưu làm tài liệu lịch sử. Các chỉ số trong đó mô tả phiên bản đã được đo.
