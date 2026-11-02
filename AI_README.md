# 🧠 TÀI LIỆU KIẾN TRÚC & TỔNG HỢP THAY ĐỔI MODULE AI (GTMS)

> **Tài liệu dành cho nhà phát triển nghiên cứu trên máy mới.**  
> **Dự án:** Graduation Thesis Management System (HUIT)  
> **Nhánh phát triển:** `dung`  
> **Cập nhật gần nhất:** Tháng 11/2026

---

## 📌 MỤC LỤC
1. [Tổng quan Module AI](#1-tổng-quan-module-ai)
2. [Danh sách các file thay đổi & Chi tiết Code](#2-danh-sách-các-file-thay-đổi--chi-tiết-code)
   - [2.1. Tầng hiển thị giao diện Blade & Javascript](#21-tầng-hiển-thị-giao-diện-blade--javascript)
   - [2.2. Tầng xử lý nhận diện cấu trúc & Heading PDF (Python)](#22-tầng-xử-lý-nhận-diện-cấu-trúc--heading-pdf-python)
   - [2.3. Tầng trích xuất chú thích & cây phân đoạn (`caption_index.py`)](#23-tầng-trích-xuất-chú-thích--cây-phân-đoạn-caption_indexpy)
   - [2.4. Tầng tổng hợp & đồng bộ kết quả (PHP Service & Job)](#24-tầng-tổng-hợp--đồng-bộ-kết-quả-php-service--job)
   - [2.5. Tầng điều phối hàng đợi & lệnh tự động hóa](#25-tầng-điều-phối-hàng-đợi--lệnh-tự-động-hóa)
3. [Luồng hoạt động End-to-End của Module AI](#3-luồng-hoạt-động-end-to-end-của-module-ai)
4. [Các sự cố kỹ thuật điển hình & Giải pháp đã xử lý](#4-các-sự-cố-kỹ-thuật-điển-hình--giải-pháp-đã-xử-lý)
5. [Hướng dẫn thiết lập & Chạy trên máy mới](#5-hướng-dẫn-thiết-lập--chạy-trên-máy-mới)
6. [Bộ lệnh kiểm thử (Unit & Feature Tests)](#6-bộ-lệnh-kiểm-thử-unit--feature-tests)

---

## 1. TỔNG QUAN MODULE AI

Module AI trong hệ thống GTMS được thiết kế để tự động phân tích các **Báo cáo tiến độ khóa luận tốt nghiệp** (định dạng PDF/DOCX) mà sinh viên nộp lên, hỗ trợ giảng viên hướng dẫn với 3 chức năng cốt lõi:
1. **Tóm tắt cấu trúc theo Chương - Mục (Document Hierarchy Tree):** Bóc tách mục lục, tiêu đề chương, mục 2 cấp, 3 cấp, 4 cấp và tóm tắt cô đọng từng phần với số trang tham chiếu chính xác.
2. **Tổng hợp công việc đã hoàn thành (Completed Work):** Rà soát các kết quả thực nghiệm, tính năng đã xây dựng mà tác giả báo cáo đã hoàn thành trong đợt nộp.
3. **Đối chiếu yêu cầu đợt nộp của Giảng viên (Requirements Alignment):** Đối chiếu từng yêu cầu cụ thể mà giảng viên giao (ví dụ: yêu cầu nộp khảo sát, thiết kế CSDL, giao diện...) với nội dung sinh viên viết trong báo cáo, trích xuất dẫn chứng kèm số trang và câu văn đối chứng trong PDF.

---

## 2. DANH SÁCH CÁC FILE THAY ĐỔI & CHI TIẾT CODE

### 2.1. Tầng hiển thị giao diện Blade & Javascript

#### 📁 `resources/views/partials/ai_document_node.blade.php`
- **Mục đích:** Render từng node (Chương / Mục / Tiểu mục) trong cây cấu trúc tài liệu của Tab 1.
- **Vấn đề trước khi sửa:**
  - File trước đó tái sử dụng `@include('partials.ai_summary_section')`. View này vốn dành cho 11 mục tổng quan lớn, biến từng ý thành một thẻ `<li class="ai-list-item">` gắn nhãn `Nội dung trích xuất`.
  - Bên dưới mỗi câu lại in một accordion `Xem dẫn chứng` chứa đúng câu trích nguyên văn từ PDF (lặp lại 100% nội dung vừa hiển thị).
  - Khi một mục cha (ví dụ `6.7`) có các tiểu mục con (`6.7.1`, `6.7.2`, `6.7.3`), mục cha in ra 4-5 thẻ lặp lại, rồi bên dưới từng mục con lại in lại lần nữa.
- **Thay đổi đã thực hiện:**
  1. Hợp nhất nội dung tóm tắt của mục thành đoạn văn liền mạch (`$summaryText`), loại bỏ hoàn toàn các thẻ rời rạc và nhãn thô `Nội dung trích xuất`.
  2. Nguồn trang được đưa lên thanh tiêu đề gọn gàng: `Nguồn: [Trang X] [Trang Y]`, cho phép click để nhảy thẳng vào vị trí trang PDF.
  3. Dẫn chứng nguyên văn PDF được chuyển vào accordion duy nhất ở cuối thẻ: `<details class="ai-review-disclosure" data-lab-source-references>`. Mặc định đóng lại để giao diện thông thoáng, người dùng chỉ mở khi cần kiểm tra đối chiếu.
  4. Nếu mục cha có tiểu mục con, chỉ hiển thị đoạn **Tổng quan mục** ngắn gọn và chuyển các dẫn chứng cụ thể xuống các mục con tương ứng, tránh lặp lại 2 lần.

#### 📁 `resources/views/partials/ai_unassigned_captions.blade.php` (Tạo mới)
- **Mục đích:** Hiển thị danh sách các Hình ảnh / Bảng biểu mà hệ thống bóc tách được từ PDF nhưng chưa gán được vào mục cụ thể (do nằm ngoài các vùng mục chính hoặc thuộc phần mở đầu/phụ lục).

#### 📁 `resources/views/partials/ai_summary_11_sections.blade.php` & `ai_teacher_report.blade.php`
- Tích hợp partial `ai_unassigned_captions` và điều chỉnh CSS/layout để Tab 1 hiển thị mượt mà.

#### 📁 `public/js/ai_teacher_report.js`
- **Thay đổi:** Nâng cấp cơ chế preview PDF. Bổ sung hỗ trợ mở tab PDF với số trang tương ứng khi click vào nút trích dẫn hoặc tên hình/bảng (`data-ai-action="citation"`).

---

### 2.2. Tầng xử lý nhận diện cấu trúc & Heading PDF (Python)

#### 📁 `app/Services/pdf_lab/core/heading_detector.py`
- **Mục đích:** Quét từng dòng văn bản trích xuất từ PyMuPDF, xác định dòng nào là tiêu đề mục (Heading) dựa trên cỡ chữ, độ đậm (`bold`), khoảng cách dòng và biểu thức chính quy số mục (VD: `1.1`, `4.4.5`, `6.7.1`).
- **Lỗi đã phát hiện:**
  - Mục `4.4.5. Theo dõi tiến độ thực hiện` bị biến mất khỏi danh sách mục, khiến các tiểu mục con `4.4.5.1` và `4.4.5.2` bị đẩy ra ngoài nằm cùng cấp với `4.4.3`, `4.4.4`, `4.4.6`.
  - **Nguyên nhân:** Regex lọc bỏ các câu văn trong thân bài có từ nối ở đầu:
    ```python
    if re.match(r'^(?:...|theo|tai|tại|nhu|như...)\b', g2_lower):
        rejected.append('Sau số mục là từ nối/giới từ/vị ngữ...')
    ```
    Từ khóa `theo` đã bắt nhầm cụm động từ `Theo dõi...` của tên mục!
- **Đoạn code đã sửa:**
  ```python
  # Tách từ 'theo' thành kiểm tra riêng: chỉ loại trừ nếu không phải là cụm động từ 'theo dõi' hoặc 'theo sát'
  is_subordinating = bool(re.match(r'^(?:thay|va|và|duoc|được|da|đã|trong|o|ở|co|có|la|là|nay|này|se|sẽ|nham|nhằm|voi|với|cho|do|boi|bởi|tai|tại|nhu|như|khi|de|để|neu|nếu|vi|vì)\b', g2_lower))
  if is_subordinating or (re.match(r'^theo\b', g2_lower) and not re.match(r'^theo\s+(?:dõi|doi|sát|sat)\b', g2_lower)):
      rejected.append('Sau số mục là từ nối/giới từ/vị ngữ, là câu văn trong đoạn chứ không phải tiêu đề mục.')
  ```
- **Kết quả:** Nhận diện chính xác 100% mục `4.4.5. Theo dõi tiến độ thực hiện`, các mục 4 cấp `4.4.5.1`, `4.4.5.2` được lồng chuẩn xác vào bên trong mục cha.

#### 📁 `app/Services/pdf_lab/core/section_tree.py`
- Tối ưu thuật toán dựng cây phân cấp (Tree Builder): Xử lý loại bỏ false-positive cho danh mục số và các tiêu đề đặc biệt giữa thân bài, đảm bảo thứ tự node cha - con không bị đứt gãy.

---

### 2.3. Tầng trích xuất chú thích & cây phân đoạn (`caption_index.py`)

#### 📁 `app/Services/pdf_lab/core/caption_index.py` (Tạo mới)
- **Mục đích:**
  1. `extract_captions(result)`: Tự động trích xuất tất cả tên hình và tên bảng (ví dụ: `Hình 3.1. Sơ đồ tuần tự`, `Bảng 4.2. Dữ liệu thử nghiệm`) kèm vị trí tọa độ `bbox`, số trang, và gán vào đúng mục chứa nó trong tài liệu.
  2. `section_nodes(...)`: Dựng cấu trúc cây phân đoạn hoàn chỉnh cho tài liệu, tổng hợp các observations từ Map phase vào từng node mục tương ứng.

#### 📁 `app/Services/lab_pdf_summary.py`
- Tích hợp `caption_index` vào pipeline xử lý tổng thể của PDF Lab.
- Xuất trường `caption_index` vào payload JSON trả về cho Laravel.

---

### 2.4. Tầng tổng hợp & đồng bộ kết quả (PHP Service & Job)

#### 📁 `app/Services/AiReportProcessingService.php`
- Đăng ký phiên bản `caption_index` (`caption-text-1`) và bổ sung `caption_index` vào danh sách các trường được phép hiển thị lên UI.

#### 📁 `app/Services/AiResultMerger.php`
- Bổ sung logic sáp nhập kết quả thông minh: Khi chạy phân tích lại chế độ review (`review_only`), giữ nguyên `caption_index` và `chapters` đã có mà không làm mất dữ liệu đã phân tích trước đó.

#### 📁 `app/Services/AiSummaryService.php`
- Tự động phát hiện môi trường thực thi Python (`resolvePythonBinary`): Tự động tìm Python trong `.venv`, `venv`, hoặc các ổ đĩa `C:`, `D:`, `E:` của Laragon, loại bỏ việc hardcode đường dẫn.

---

### 2.5. Tầng điều phối hàng đợi & lệnh tự động hóa

#### 📁 `app/Console/Commands/AiWorkCommand.php`
- Tự động kiểm tra môi trường Python và thư viện phụ thuộc (`PyMuPDF`) trước khi lắng nghe hàng đợi `ai-reports`. Nếu thiếu, hiển thị thông báo hướng dẫn rõ ràng.

#### 📁 `app/Console/Commands/ServeWithAiWorkerCommand.php` (`php artisan serve:ai`)
- Chạy đồng thời cả máy chủ web Laravel (`php artisan serve`) và worker xử lý AI nền (`php artisan ai:work`) trong một lệnh duy nhất, rất tiện lợi khi dev ở máy cá nhân.

---

## 3. LUỒNG HOẠT ĐỘNG END-TO-END CỦA MODULE AI

```
[Sinh viên nộp PDF báo cáo]
          │
          ▼
[Controller dispatch Job AnalyzeReportVersion]
          │
          ▼ (Đẩy vào Queue: ai-reports)
[Worker php artisan ai:work gọi lab_pdf_summary.py]
          │
          ├─► 1. pdf_extractor.py: Trích xuất text, bbox, font_size bằng PyMuPDF
          ├─► 2. heading_detector.py: Nhận diện tiêu đề Chương / Mục / Tiểu mục
          ├─► 3. section_tree.py: Dựng cây phân cấp tài liệu (Tree Builder)
          ├─► 4. caption_index.py: Bóc tách Hình/Bảng và liên kết mục
          ├─► 5. segmentation_pipeline.py: Chia nhỏ tài liệu thành các Chunk có ngữ cảnh
          ├─► 6. Map Phase: Gọi LLM Gemini tóm tắt từng chunk có dẫn chứng
          ├─► 7. Reduce Phase: Tổng hợp các đoạn tóm tắt chương & công việc hoàn thành
          └─► 8. Requirement Alignment: Đối chiếu yêu cầu đợt nộp của Giảng viên
          │
          ▼
[Lưu kết quả JSON vào cột TomTatJson của bảng KhoiBaoCaoTienDo]
          │
          ▼
[Giảng viên xem báo cáo: Blade views hiển thị Tabbed UI chuẩn hóa]
```

---

## 4. CÁC SỰ CỐ KỸ THUẬT ĐIỂN HÌNH & GIẢI PHÁP ĐÃ XỬ LÝ

| Hiện tượng | Nguyên nhân | File xử lý & Cách giải quyết |
| :--- | :--- | :--- |
| **Spinner xoay mãi không dừng, không hiện số phần đã đọc** | Thiếu thư viện Python `PyMuPDF`, worker crash ngay khi gọi script. | Chạy `pip install PyMuPDF==1.28.2` cho đúng interpreter Python của Laragon. |
| **Mục 4.4.5 bị mất, 4.4.5.1 nằm ngang hàng 4.4.3, 4.4.4** | Từ khóa `theo` trong regex lọc từ nối nhận nhầm tên mục `Theo dõi tiến độ...`. | Cập nhật [`heading_detector.py`](file:///d:/laragon/www/KhoaLuanCuNhan/app/Services/pdf_lab/core/heading_detector.py) loại trừ `theo dõi`, `theo sát`. |
| **Tóm tắt mục bị vụn thành 3-4 card lặp lại câu trích PDF** | View con dùng chung format card rời rạc và accordion quote của 11 section lớn. | Viết lại [`ai_document_node.blade.php`](file:///d:/laragon/www/KhoaLuanCuNhan/resources/views/partials/ai_document_node.blade.php): Hợp nhất thành đoạn tóm tắt hoàn chỉnh, thu gọn trích dẫn vào details. |
| **Mục cha in lặp lại toàn bộ các câu của mục con** | `descendants(s)` gom toàn bộ observation của con lên cha và cha render lại toàn bộ. | Thêm bộ lọc phát hiện mục con trong Blade để cha chỉ hiển thị đoạn **Tổng quan mục**, tránh lặp lại 2 lần. |
| **Lỗi `Encountered unexpected escape sequence` khi cấu hình `.env`** | Dùng dấu gạch chéo ngược `\` và ngoặc kép trong `PYTHON_PATH` trên Windows. | Đổi đường dẫn thành dấu gạch xuôi: `PYTHON_PATH=D:/laragon/bin/python/python-3.10/python.exe`. |

---

## 5. HƯỚNG DẪN THIẾT LẬP & CHẠY TRÊN MÁY MỚI (TỰ ĐỘNG HÓA 100%)

Hệ thống đã được **tự động hóa hoàn toàn**. Lệnh `php artisan serve` mặc định đã được ghi đè (`AppServiceProvider.php`) bằng `ServeWithAiWorkerCommand` để tự động làm mọi việc cho bạn.

Khi clone về máy mới, bạn chỉ cần thực hiện:

### Bước 1: Cài đặt PHP & Node dependencies
```powershell
composer install
npm install
npm run build
```

### Bước 2: Cấu hình API Key trong `.env`
Chỉ cần điền API Key của bạn (không bắt buộc khai báo `PYTHON_PATH` vì hệ thống tự động dò tìm Python ở các ổ `D:`, `C:`, `E:` của Laragon hoặc virtualenv):
```env
GEMINI_API_KEY=AIzaSy... (API Key của bạn)
```

### Bước 3: Khởi động hệ thống
Chỉ cần gõ **một lệnh duy nhất**:
```powershell
php artisan serve
```
✨ **Cơ chế tự động kích hoạt:**
1. **Tự động quét Python**: Tự động dò tìm Python 3.10+ của Laragon (`D:`, `C:`, `E:`) hoặc `.venv`.
2. **Tự động cài đặt thư viện**: Tự động kiểm tra `import pymupdf`; nếu môi trường máy mới chưa có thư viện, script sẽ tự động gọi `pip install -r app/Services/pdf_lab/requirements.txt` để tải và cài đặt ngay trong lúc khởi động mà không cần gõ lệnh tay!
3. **Tự động khởi động Worker AI**: Khởi chạy ngầm tiến trình `php artisan ai:work` để lắng nghe hàng đợi xử lý báo cáo song song với máy chủ web.

*(Lưu ý: Bạn cũng có thể dùng `php artisan serve:ai` hoặc lệnh tách biệt `php artisan ai:work` nếu muốn theo dõi log worker riêng).*

---

## 6. BỘ LỆNH KIỂM THỬ (UNIT & FEATURE TESTS)

Sau khi clone và cài đặt trên máy mới, hãy chạy bộ lệnh kiểm thử sau để xác nhận 100% hệ thống hoạt động ổn định:

1. **Kiểm tra giao diện Tabbed AI & Tree node:**
   ```powershell
   php artisan test --filter=AiTeacherReportUiTest
   ```
   *(Kỳ vọng: PASS 6/6 tests, 35 assertions)*.

2. **Kiểm tra cơ chế Merge & Caption Index:**
   ```powershell
   php artisan test --filter=AiCaptionMergeTest
   ```
   *(Kỳ vọng: PASS 1/1 test)*.

3. **Kiểm tra Python trích xuất Caption & Dựng cây phân cấp:**
   ```powershell
   python -c "import tests.Unit.test_caption_index as t; t.test_caption_index_excludes_index_and_mentions_and_keeps_offsets(); t.test_parent_without_direct_body_aggregates_child_and_rejects_bad_offsets(); t.test_missing_section_retains_caption_without_fabricating_number(); t.test_rotated_pdf_caption_keeps_page_offset_and_searchable_region(); print('ALL TESTS PASSED')"
   ```
   *(Kỳ vọng: `ALL TESTS PASSED`)*.
