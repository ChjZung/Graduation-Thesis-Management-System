# HỒ SƠ TÀI LIỆU & THỰC NGHIỆM MÔ-ĐUN AI PHÂN TÍCH TIẾN ĐỘ KHÓA LUẬN

> **Đề tài:** Xây dựng hệ thống quản lý công tác khóa luận tốt nghiệp  
> **Khoa:** Công Nghệ Thông Tin — Trường Đại học Công Thương TP.HCM (HUIT)  
> **Thư mục lưu trữ:** `docs/ai_research/`

---

## 1. Vị Trí Lưu Trữ Các Thành Phần Trong Dự Án

Toàn bộ mã nguồn, cấu hình, dữ liệu thực nghiệm và tài liệu khoa học của mô-đun AI đều được lưu trữ trực tiếp và hoàn chỉnh bên trong thư mục dự án:

### A. Tài liệu nghiên cứu & Báo cáo thực nghiệm (Tại `docs/ai_research/`)
* **[`docs/ai_research/KET_QUA_DANH_GIA_THUC_NGHIEM_18_SCENARIOS.md`](./KET_QUA_DANH_GIA_THUC_NGHIEM_18_SCENARIOS.md):**  
  Báo cáo kết quả đánh giá thực nghiệm 18/18 kịch bản nghiệp vụ tích hợp AI: đo đạc độ trễ (latency), xác thực tính toàn vẹn phiên bản (v1, v2), kiểm tra phân quyền an toàn chéo nhóm (IDOR) và tính độc lập học thuật (AI không bao giờ tự ý duyệt Đạt).
* **[`docs/ai_research/NGHIEN_CUU_AI_BAO_CAO_TIEN_DO.md`](./NGHIEN_CUU_AI_BAO_CAO_TIEN_DO.md):**  
  Báo cáo khoa học hoàn chỉnh gồm: Cơ sở lý thuyết NLP & LLM, kiến trúc Map–Reduce, kỹ thuật Sliding-window Chunking with Overlap, Sơ đồ hoạt động UML Activity Diagram 5 swimlanes (Mermaid), bảng số liệu thực nghiệm đo đạc từ các file PDF thực tế của hệ thống, công thức tính toán Precision/Recall/F1/Fidelity, và dàn ý viết Chương 3 + Chương 4 cho cuốn Báo cáo Khóa luận Tốt nghiệp.
* **[`docs/ai_research/ai_benchmark_report.md`](./ai_benchmark_report.md):**  
  Báo cáo kết quả đo đạc đối chiếu 4 cấu hình chunking (C1: 1500 ký tự, C2: 3000 ký tự, C3: 5000 ký tự, C4: Direct 15000 ký tự).
* **[`docs/ai_research/ai_benchmark_results.json`](./ai_benchmark_results.json):**  
  Toàn bộ dữ liệu JSON thô của đợt chạy benchmark thực nghiệm (thời gian trích xuất, số chunk, số từ/ký tự, F1-score từng tài liệu).

---

### B. Mã nguồn xử lý AI & Backend (Tại `app/`)
* **[`app/Services/ai_summarizer.py`](../../app/Services/ai_summarizer.py):**  
  Script Python NLP cốt lõi: Đọc PDF bằng `PyMuPDF` (`fitz`), phân đoạn trượt có overlap, thực hiện Map–Reduce qua Gemini API hoặc bộ quy tắc Heuristic Rule-Based NLP, ghi nhận độ trễ và số từ/ký tự.
* **[`app/Services/AiSummaryService.php`](../../app/Services/AiSummaryService.php):**  
  Service trung gian Laravel kết nối Python, nhận diện tham số cấu hình, định dạng lược đồ JSON chuẩn (`standard_report`) và cây 11 mục chuyên nghiệp (`data`).
* **[`app/Services/ThesisProgressService.php`](../../app/Services/ThesisProgressService.php):**  
  Nghiệp vụ lưu trữ phiên bản báo cáo (`nopBaoCaoPhienBanMoi`): tự động gọi AI khi sinh viên tải PDF, tự động điền các trường để trống, lưu bền vững vào bảng CSDL `khoibaocaotiendo` và `tomtatbaocao`.
* **[`app/Http/Controllers/SinhVien/BaoCaoController.php`](../../app/Http/Controllers/SinhVien/BaoCaoController.php):**  
  Controller xử lý giao diện sinh viên: Tối giản form nộp (không bắt buộc sinh viên gõ lại thủ công các nội dung đã có trong file PDF).
* **[`app/Console/Commands/AiProgressBenchmarkCommand.php`](../../app/Console/Commands/AiProgressBenchmarkCommand.php):**  
  Lệnh Artisan `php artisan ai:benchmark-reports` dùng để chạy kiểm thử và đo lường thực nghiệm khoa học tự động.

---

### C. Giao diện người dùng Blade (Tại `resources/views/`)
* **[`resources/views/partials/ai_summary_11_sections.blade.php`](../../resources/views/partials/ai_summary_11_sections.blade.php):**  
  Giao diện hiển thị 11 mục thẩm định tự động từ AI, gồm thanh tiến độ %, danh sách đầu việc có trích dẫn `[Trang X]`, deliverables, công nghệ, khó khăn, kế hoạch và khuyến nghị.
* **[`resources/views/sinhvien/baocao/show_dot.blade.php`](../../resources/views/sinhvien/baocao/show_dot.blade.php):**  
  Màn hình nộp báo cáo và xem lịch sử các phiên bản của sinh viên kèm kết quả thẩm định AI.
* **[`resources/views/giangvien/baocao/show_dot.blade.php`](../../resources/views/giangvien/baocao/show_dot.blade.php):**  
  Màn hình dành cho Giảng viên hướng dẫn: đối chiếu kết quả thẩm định AI với file PDF gốc và đưa ra nhận xét chuyên môn độc lập.

---

## 2. Cách Chạy Lại Thực Nghiệm (Reproducing Experiments)

Mở Terminal tại thư mục gốc dự án và thực hiện các lệnh sau:

### Cách 1: Chạy toàn bộ Benchmark trên tập dữ liệu PDF bằng Artisan
```powershell
php artisan ai:benchmark-reports --limit=5
```
*Lệnh này sẽ tự động duyệt các tệp PDF thực tế trong `storage/app/public/`, chạy qua 4 cấu hình phân đoạn, in bảng thống kê ra màn hình và cập nhật kết quả vào `docs/ai_research/ai_benchmark_report.md`.*

### Cách 2: Chạy trực tiếp qua Python CLI với tệp PDF tùy chọn
```powershell
python app/Services/ai_summarizer.py --pdf "storage/app/public/de_cuong/de_cuong_1792073636_cntt-klcn185-nguyen-van-le.pdf" --chunk-size 3000 --chunk-overlap 300
```

### Cách 3: Chạy bộ kiểm thử thực nghiệm 18 kịch bản tích hợp nghiệp vụ
```powershell
php artisan ai:evaluate-scenarios
```
*Lệnh này sẽ tự động mô phỏng toàn bộ quy trình: Sinh viên nộp PDF -> AI trích xuất Map-Reduce -> GVHD yêu cầu nộp lại -> SV nộp v2 -> GVHD duyệt Đạt, kiểm tra IDOR bảo mật, stress test F5 cache và xuất báo cáo.*

