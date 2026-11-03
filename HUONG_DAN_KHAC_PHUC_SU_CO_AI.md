# 🛠️ HƯỚNG DẪN KHẮC PHỤC CÁC SỰ CỐ KHI CLONE & CHẠY TÍNH NĂNG AI TÓM TẮT

Tài liệu này tổng hợp toàn bộ các nguyên nhân gây lỗi khi clone dự án về máy mới (đặc biệt là tính năng **AI Tóm tắt báo cáo tiến độ** bị kẹt loading, không hiển thị tiến độ *"Đã đọc X/Y phần"* hoặc sập web) và các bước xử lý chuẩn hóa.

---

## 📌 TÓM TẮT NGUYÊN NHÂN CỐT LÕI
Khi kéo code từ GitHub về môi trường máy mới (đặc biệt là Windows dùng Laragon):
1. **Môi trường Python chưa có thư viện `PyMuPDF`**: Tiến trình trích xuất PDF crash ngay ở dòng import đầu tiên khiến AI không thể phân tích văn bản.
2. **Đường dẫn Python bị hardcode ổ `C:\` & lỗi cú pháp Escape trong `.env`**: Gây lỗi khi máy đặt Laragon ở ổ `D:\` hoặc ổ đĩa khác.
3. **Xung đột phiên bản Vite với Node.js v18**: `package.json` cũ yêu cầu Node >= 20 khiến `npm run build` bị lỗi, thiếu file `manifest.json` dẫn đến lỗi HTTP 500 khi mở web.
4. **Cơ chế Cooldown của hàng đợi (Queue Backoff)**: Khi gặp lỗi, hệ thống tự động hoãn tác vụ (1 - 15 phút) và khóa nút *"Phân tích lại"*, làm người dùng tưởng hệ thống bị treo.
5. **Thứ tự Migration**: Thiếu bảng `khoibaocaotiendo` khi migrate trên database mới.

---

## 🔍 CHI TIẾT CÁC VẤN ĐỀ & CÁCH KHẮC PHỤC

### 1. Cài đặt thư viện `PyMuPDF` cho Python (Khắc phục lỗi AI loading mãi)
- **Hiện tượng:** Khi nộp bài hoặc bấm phân tích báo cáo, tiến trình worker chạy nền bị crash:
  ```text
  ModuleNotFoundError: No module named 'pymupdf'
  at app/Services/pdf_lab/core/pdf_extractor.py:6
  ```
  Do không đọc được file PDF để chia phần (chunk), hệ thống **không bao giờ tạo được file tiến độ `ai_progress`**, làm giao diện web xoay spinner mãi mà không hiện tiến độ *"Đã đọc X/Y phần"*.
- **Cách xử lý:**
  Chạy lệnh cài đặt vào đúng interpreter Python của Laragon:
  ```powershell
  & "D:\laragon\bin\python\python-3.10\python.exe" -m pip install -r app/Services/pdf_lab/requirements.txt
  ```
  *(Hoặc `pip install PyMuPDF==1.28.2`)*
- **Kiểm tra sau khi cài:**
  ```powershell
  & "D:\laragon\bin\python\python-3.10\python.exe" app/Services/lab_pdf_summary.py --help
  ```
  Nếu hiện ra bảng hướng dẫn tham số (`options: --pdf, --stage...`) là thành công.

---

### 2. Cấu hình biến `PYTHON_PATH` trong `.env`
- **Hiện tượng:**
  - Code trong `app/Services/AiSummaryService.php` (dòng 110, 175) và `app/Http/Controllers/BaoCaoAiController.php` (dòng 52) đang hardcode `C:\laragon\bin\python\...`. Khi máy đặt Laragon ở ổ `D:\`, code sẽ fallback về `python` chung và dễ gọi nhầm Python shim của Windows Store.
  - Khi thêm `PYTHON_PATH="D:\laragon\..."` có dấu nháy kép `"` và dấu gạch chéo ngược `\` vào `.env`, `phpdotenv` của Laravel sẽ báo lỗi cú pháp:
    ```text
    Encountered an unexpected escape sequence at ["D:\laragon\bin\python\python-3.10\python.exe"]
    ```
- **Cách xử lý:**
  Khai báo đường dẫn dùng **dấu gạch xuôi `/`** vào file `.env`:
  ```env
  PYTHON_PATH=D:/laragon/bin/python/python-3.10/python.exe
  ```

---

### 3. Tương thích Vite với Node.js v18 của Laragon
- **Hiện tượng:**
  File `package.json` cấu hình `"vite": "^8.0.0"` đòi hỏi Node.js >= 20. Khi chạy trên Node v18 mặc định của Laragon, lệnh `npm run build` sẽ bị lỗi `styleText`, dẫn đến thiếu thư mục `public/build/manifest.json` và làm sập giao diện web (HTTP 500 Error).
- **Cách xử lý:**
  Cập nhật `package.json` sử dụng Vite 5:
  ```json
  "devDependencies": {
      "vite": "^5.4.11",
      "laravel-vite-plugin": "^1.2.0",
      "sass": "^1.77.8"
  }
  ```
  Sau đó thực hiện cài đặt và build lại asset:
  ```bash
  npm install
  npm run build
  ```

---

### 4. Bổ sung tính năng "Dừng phân tích" & "Phân tích lại ngay"
- **Hiện tượng:**
  Trước đây, khi phân tích gặp lỗi, job bị đẩy vào trạng thái `waiting` với thời gian hoãn tăng dần theo cấp số nhân (1, 2, 4, 8, 15 phút). Nút *"Phân tích lại"* bị khóa ở giao diện và bị chặn ở backend (`enqueue()`), làm người dùng không thể can thiệp.
- **Giải pháp đã nâng cấp:**
  1. **Nút "Dừng phân tích" (`cancel`)**: Cho phép người dùng dừng tiến trình ngay lập tức. Hệ thống sẽ:
     - Xóa các job đang chờ/delay của bài nộp này trong bảng `jobs`.
     - Xóa cache tiến độ tạm thời.
     - Chuyển trạng thái sang `cancelled` và mở khóa ngay nút *"Phân tích lại"*.
  2. **Cờ `$force = true` cho nút "Phân tích lại" (`reanalyze`)**: Bỏ qua mọi thời gian chờ cooldown, làm mới token và kích hoạt lượt phân tích mới ngay lập tức.

---

### 5. Khởi tạo bảng `khoibaocaotiendo` cho Database mới
- **Hiện tượng:**
  Migration `2026_10_16_090000_enhance_progress_reports_and_ai_summary_tables.php` thực hiện lệnh `alter table khoibaocaotiendo...`. Bảng này ban đầu được định nghĩa trong file SQL thô `database/migrations/create_khoi_bao_cao_tien_do.sql`. Nếu chạy `php artisan migrate` trên CSDL mới tinh sẽ gặp lỗi `Table 'quanly_doan.khoibaocaotiendo' doesn't exist`.
- **Cách xử lý:**
  Import file SQL tạo bảng trước khi chạy migrate:
  ```cmd
  mysql -u root quanly_doan < database\migrations\create_khoi_bao_cao_tien_do.sql
  php artisan migrate
  ```

---

### 6. Lỗi thuật toán phân đoạn bị "hỗn loạn" về cuối (False Positive Headings & Tree Collapse)
- **Hiện tượng trên giao diện:**
  - Ở các chương đầu (Chương 1 đến 5), các mục và nội dung được nhóm rất gọn gàng theo từng khối Accordion.
  - Nhưng đến cuối Chương 6 (ví dụ trang 127–135 ở bản v2 hoặc trang 151–153 ở bản v3), cấu trúc bị vỡ vụn:
    + Xuất hiện các thẻ lạ: *"Chương 3 và được thử nghiệm trong mục 6.4..."* hoặc *"2.3 thay vì cắt toàn bộ văn bản..."* hoặc *"Tóm tắt báo cáo"*.
    + Khối `CHƯƠNG 6: THỬ NGHIỆM VÀ TRIỂN KHAI` bị ngắt sớm (Trang 121–127 thay vì 135).
    + Các mục con của Chương 6 như `6.5.5`, `6.5.6`, `6.7.2`, `6.7.3`, `6.8. KẾT CHƯƠNG` đều bị "văng" ra ngoài thành từng thẻ Accordion cấp 1 độc lập.
- **Nguyên nhân kỹ thuật:**
  1. **Bắt nhầm câu thân bài thành Tiêu đề mục (`heading_detector.py`)**:
     - Do câu văn trong đoạn bị ngắt dòng rơi đúng cụm từ bắt đầu bằng số/chương:
       + Bản v3 (Trang 151): *"đã phân tích ở [xuống dòng] Chương 3 và được thử nghiệm..."* $\rightarrow$ dính Regex `CHAPTER`.
       + Bản v2 (Trang 127): *"như 1, 1.1, [xuống dòng] 2.3 thay vì cắt toàn bộ văn bản..."* $\rightarrow$ dính Regex `NUMBERED`.
       + Bản v2 (Trang 133): ô chữ *"Tóm tắt báo cáo"* trong Bảng 6.5 $\rightarrow$ dính `SPECIAL` (Abstract heading).
     - Thuật toán cũ tự động cộng điểm cao cho các regex này mà không kiểm tra chữ thường (`thay vì...`), không kiểm tra in đậm (`bold=False`), không kiểm tra từ nối, và không khóa phạm vi chương hiện tại (đang ở Chương 6 không thể định nghĩa mục `2.3`).
  2. **Hiệu ứng sụp đổ cây phân cấp (`section_tree.py`)**:
     - Khi một mục con giả mạo xuất hiện, thuật toán duyệt stack và pop mất `CHƯƠNG 6` cấp 1 thật ra khỏi cây.
     - Khi các mục con thật sự tiếp theo xuất hiện (`6.5.5`, `6.7.2`), do không tìm thấy tiền tố cha, chúng đều mất cha (`Parent = None`) và bị đôn lên làm các Root Section độc lập trên giao diện.
- **Cách khắc phục đã triển khai:**
  1. Trong [`heading_detector.py`](file:///D:/laragon/www/KhoaLuanCuNhan/app/Services/pdf_lab/core/heading_detector.py):
     - **Bắt buộc viết hoa**: Tiêu đề mục thật bắt buộc viết hoa chữ cái đầu; nếu bắt đầu bằng chữ thường (`thay vì...`) sẽ bị loại bỏ ngay.
     - **Chặn từ nối / giới từ**: Loại bỏ các dòng bắt đầu bằng `thay vì`, `và`, `được`, `trong`, `ở`, `có`, `là`,...
     - **Khóa phạm vi chương hiện tại**: Khi đang ở Chương 6, chỉ chấp nhận mục con có tiền tố `6.x`; số mục ngoại lai như `2.3` sẽ bị từ chối.
     - **Ràng buộc Front-Matter**: Mục tóm tắt (Abstract / Lời cảm ơn) chỉ được chấp nhận ở phần đầu tài liệu (trang $\le 20$ và trước Chương 1), không chấp nhận ô chữ trong bảng biểu giữa thân bài.
     - **Ràng buộc định dạng chữ**: Tiêu đề chuẩn phải in đậm (`bold=True`) hoặc cỡ chữ nổi bật; chữ thân bài thường (`13pt`, không đậm) bị từ chối.
  2. Trong [`section_tree.py`](file:///D:/laragon/www/KhoaLuanCuNhan/app/Services/pdf_lab/core/section_tree.py):
     - Ràng buộc ngăn xếp: Một mục con cấp 2 trở đi (`level > 1`) **tuyệt đối không bao giờ được phép pop Chương cấp 1 (`section_level == 1`) ra khỏi cây**, đảm bảo khối Chương 6 luôn được giữ nguyên vẹn cho toàn bộ các mục con phía sau.

---

---

### 7. Tính năng Tự động Dò tìm Python & Tự động Cài đặt PyMuPDF (Auto-Doctor & Auto-Install)
- **Cải tiến mới:**
  - **Tự động dò tìm Python đa môi trường**: [`AiSummaryService::resolvePythonBinary()`](file:///D:/laragon/www/KhoaLuanCuNhan/app/Services/AiSummaryService.php) tự động quét:
    + Cấu hình `PYTHON_PATH` trong `.env`
    + Virtual environment cục bộ (`.venv` hoặc `venv`)
    + Mọi ổ đĩa cài đặt Laragon phổ biến (`D:\`, `C:\`, `E:\`, `F:\`)
    + Trình thông dịch `python3` trên máy chủ Linux / Docker khi deploy lên server
  - **Tự động tải thư viện khi chạy `php artisan serve`**:
    Lệnh `php artisan serve` được tích hợp sẵn [`ensureAiEnvironment()`](file:///D:/laragon/www/KhoaLuanCuNhan/app/Console/Commands/ServeWithAiWorkerCommand.php). Nếu phát hiện máy mới chưa cài `PyMuPDF`, lệnh sẽ **tự động gọi pip cài đặt ngầm** mà người dùng không cần gõ lệnh thủ công.
  - **Lệnh kiểm tra & chuẩn bị toàn diện khi Deploy**:
    Chỉ cần chạy:
    ```bash
    php artisan ai:setup
    ```
    Hệ thống sẽ tự động quét và chuẩn bị toàn diện: Python, PyMuPDF, thư mục Cache/Storage, kiểm tra CSDL & Bảng `khoibaocaotiendo`, `jobs` và khóa Gemini API.

---

## 🚀 QUY TRÌNH THIẾT LẬP NHANH CHO MÁY MỚI HOẶC KHI DEPLOY

Khi bạn hoặc đồng đội clone code về máy mới hoặc deploy lên server:

```bash
# 1. Cài đặt dependency PHP & Frontend
composer install
npm install
npm run build

# 2. Tạo file cấu hình môi trường (.env)
cp .env.example .env
php artisan key:generate

# 3. Chạy lệnh tự động kiểm tra & cài đặt môi trường AI (Khuyên dùng khi deploy)
php artisan ai:setup

# 4. Khởi chạy dự án (Tự động bật kèm Worker và tự động cài PyMuPDF nếu còn thiếu)
php artisan serve
```

Truy cập: [http://127.0.0.1:8000](http://127.0.0.1:8000)
