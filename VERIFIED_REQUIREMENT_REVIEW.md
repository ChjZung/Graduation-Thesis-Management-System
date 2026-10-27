# Tích hợp đối chiếu yêu cầu từ Lab

Pipeline pdf-lab-v1.7-verified-requirement-review. config/ai_processing.php bật lab.review_engine=verified_v2.

Giữ Map–Reduce và bản đọc theo chương hiện có. Sau khi hoàn tất bản đọc, verified_requirement_review.py gọi engine ai_runtime/requirement_review để đối chiếu trực tiếp văn bản/ô bảng của PDF với từng yêu cầu gốc. Không dùng tóm tắt con như nguồn thay thế PDF.

Laravel database queue ai-reports vẫn quản lý công việc: nộp báo cáo/lệnh phân tích chỉ enqueue, AnalyzeReportVersion xử lý trong worker ai:work. Không đưa hàng đợi SQLite/Streamlit hoặc kho DPAPI của Lab vào ứng dụng Laravel. Key lấy từ cấu hình server và truyền qua biến môi trường cho Python; không nằm trong tham số lệnh hay JSON.

Cache Map và chương giữ cấu trúc cũ. Cache đối chiếu mới ở storage/app/private/ai_cache/verified_pdf_review, gắn hash PDF, phiên bản bài nộp, mô tả, model và phiên bản prompt/guard. Checkpoint được lưu từng bước. Các lượt nền chia ngân sách, chờ quota/503 và tiếp tục; lỗi ngữ nghĩa không tự được coi là thành công. Không tuyên bố độ chính xác 70%/80% khi chưa có bộ nhãn chuẩn.

UI giảng viên và sinh viên: mô tả gốc ở đầu; Có dẫn chứng / Chưa đủ dẫn chứng. Chỉ SUPPORTS + câu trích hợp lệ được xuất làm nguồn được chấp nhận. PARTIAL giữ nội bộ. Phần thiếu nguồn có link mở báo cáo gốc, không kết luận sinh viên chưa làm. Hình/sơ đồ cần người đọc xem trực tiếp. Kết quả cũ không bị xóa; cần chọn Phân tích lại để sinh đối chiếu theo pipeline mới.

Worker: php artisan ai:work. Sau khi thay mã/cấu hình, dùng php artisan config:clear, php artisan view:clear, php artisan queue:restart rồi bảo đảm có ai:work đang chạy. Lệnh này dùng khóa file để tránh nhiều worker trên cùng máy. Cần trình quản lý dịch vụ cho tự khởi động sau khi reboot nếu triển khai lên máy chủ.

Kiểm tra tích hợp: 23 Python tests (bridge cũ + đối chiếu mới), 8 Laravel adapter/view tests (52 assertions), 17 queue/endpoint tests (68 assertions) qua; không gọi Gemini. Có thể kiểm tra cấu hình/worker mà không in key bằng php scripts/check_verified_review_runtime.php. Chưa thực hiện lượt Gemini mới trong project hay đo độ chính xác trên bộ nhãn chuẩn. Việc kiểm tra giao diện trong phiên tài khoản cần một phiên đăng nhập hợp lệ.
