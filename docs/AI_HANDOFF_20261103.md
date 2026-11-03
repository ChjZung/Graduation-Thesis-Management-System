# Bàn giao ngữ cảnh module báo cáo AI cho máy khác

Ngày chốt ghi chú: **03/11/2026**, múi giờ UTC+7.

## 1. Đọc trước khi tiếp tục

Người dùng hiện thấy kết quả tương đối ổn và muốn máy của bạn mình nắm ngữ cảnh để chạy thử tiếp. **Ưu tiên giữ chức năng đang hoạt động; không tự viết lại pipeline hoặc mở rộng tính năng.**

**Không chuyển/upload database. Máy bên kia tự tạo dữ liệu mới để test.** Không cần mang theo bản backup, tài khoản demo, lịch sử AI hoặc checkpoint của máy hiện tại. Các ID trong ghi chú chỉ dùng giải thích lịch sử, không hardcode lên máy khác.

Repo: `https://github.com/ChjZung/Graduation-Thesis-Management-System.git`.

- Project ở máy hiện tại: `C:/laragon/www/KhoaLuanCuNhan`.
- Nhánh local: **`dung`**.
- Commit nền trước đợt nâng cấp: **`8900d15`**.
- Các bản vá được bàn giao cùng file này trên nhánh **`dung`**. Sau khi pull, kiểm tra HEAD và các file nguồn đi kèm; không chỉ sao chép riêng Markdown.
- Khi nhận source, phải kiểm tra cả file mới chưa tracked, không chỉ các file đã sửa trong Git diff. Không reset/checkout đè thay đổi của bạn ở máy kia.
- Không có API key hoặc mật khẩu thật trong file bàn giao.

## 2. Phạm vi chức năng đã chốt

Giao diện AI có **hai tab**:

1. **Tóm tắt theo chương/mục**: chương, mục X.X và mục con; tóm tắt mục cha được tổng hợp từ nội dung con khi cha không có đoạn riêng.
2. **Yêu cầu của giảng viên theo đợt**: giữ yêu cầu gốc, mô tả ngắn và dẫn chứng để mở PDF. Có nhóm nguồn đã tìm được và nhóm chưa đủ dẫn chứng.

**Tab công việc hoàn thành/chưa hoàn thành đã bị bỏ theo yêu cầu người dùng. Không khôi phục tab này.** Một số trường/bước nội bộ cũ vẫn còn vì pipeline dùng chung, không xóa tùy tiện.

Quy tắc quan trọng:

- Không tìm thấy dẫn chứng **không có nghĩa** sinh viên chưa làm.
- Không tự chấm đạt/không đạt hoặc kết luận hoàn thành toàn bộ yêu cầu.
- Caption chỉ là **tên hình/bảng từ lớp văn bản PDF**. Không có OCR, multimodal hoặc đọc hiểu nội dung ảnh/sơ đồ.
- Caption phù hợp vẫn hiển thị để mở đúng trang, kèm lưu ý giảng viên kiểm chứng PDF gốc.
- ERD khác Activity Diagram; lớp mức phân tích khác mức thiết kế; mô tả thiết kế khác chứng minh đã cài đặt.
- Người dùng chọn chất lượng/trung thực hơn việc ép AI trả câu trả lời đầy đủ.

## 3. Pipeline hiện tại

```text
Sinh viên nộp PDF / yêu cầu phân tích
  → lưu bản nộp và định danh phiên bản
  → enqueue Laravel Queue (ai / ai-reports)
  → Python trích xuất văn bản, chương/mục, offset, tọa độ
  → phân đoạn và đóng gói theo ngân sách token
  → Map: tóm tắt từng gói, giữ câu trích nguồn
  → Reduce/reader: tạo bản đọc chương/mục và tổng quan
  → lập index văn bản PDF gốc cho đối chiếu yêu cầu
  → phân tích yêu cầu, tìm nguồn và kiểm chứng
  → lọc loại nội dung/chức năng/giai đoạn của nguồn
  → giữ caption tham chiếu, hạ kết luận thiếu căn cứ
  → lưu kết quả, provenance và lịch sử
  → giao diện polling trạng thái, đọc kết quả từng phần
  → PDF Viewer mở đúng bản, trang và vùng trích
```

Các con số trang/mục/chunk/gói không cố định. Ví dụ bộ báo cáo 124 trang từng tạo 234 vùng và 23 gói; bản 155 trang tạo 28 gói. Không hardcode chúng làm quy tắc thuật toán.

## 4. Phân biệt ba chế độ chạy

- **Dùng kết quả đã lưu:** cùng nguồn/ngữ cảnh và policy hợp lệ thì trả kết quả, không sinh AI lại.
- **Phân tích mới:** `fresh=true` chỉ xóa checkpoint ở **attempt đầu của lượt mới**.
- **Tiếp tục:** retry do hết ngân sách mỗi vòng hoặc người dùng chọn tiếp tục phải giữ checkpoint; truyền `fresh=false`.

Các vòng Queue có giới hạn số lượt gọi. Hết ngân sách một vòng không nhất thiết là hết quota ngày hoặc lỗi hệ thống; có thể chuyển `waiting` rồi tiếp tục từ checkpoint.

Không chạy parse PDF, toàn bộ matcher hoặc Gemini trong polling, chuyển tab, render Blade hoặc chỉ mở accordion. Viewer vẫn cần đọc/render trang PDF để hiển thị dẫn chứng; đó không phải chạy lại pipeline AI.

## 5. Các lỗi đã sửa — đừng làm tái phát

### A. Polling ghi đè kết quả kiểm chứng bằng heuristic

Trước đây `payload()` có thể chạy lại matcher và thay nguồn ngay khi đọc dữ liệu. Đã tách:

- `storedPayload()`: đọc JSON gốc để lưu/snapshot/cập nhật trạng thái.
- `payload()`: bản hiển thị, kiểm tra ràng buộc ngữ cảnh; không chạy matcher.

Đã bỏ fallback matcher PHP khi lưu. Giữ provenance để biết kết quả đến từ pipeline kiểm chứng hay heuristic. Không ghi đè kết quả mạnh hơn bằng nguồn yếu hơn khi cùng PDF/ngữ cảnh. Đổi PDF hoặc yêu cầu không được dùng kết quả cũ như hiện hành.

### B. Dẫn chứng sai loại hoặc sai chức năng

Đã bổ sung gate PHP/Python và output guard:

- Loại Activity Diagram khỏi yêu cầu CSDL/ERD.
- Phân biệt mức phân tích và mức thiết kế theo cả hai chiều khi nguồn nêu rõ.
- Không lấy sơ đồ tuần tự của chức năng khác; giao diện đăng nhập không lấy danh sách người dùng.
- Không lấy bảng dữ liệu thử nghiệm làm nguồn cho yêu cầu thiết kế CSDL chỉ vì cùng có từ “dữ liệu”.
- Xử lý một số cụm dính khoảng trắng như `Sơ đồlớp` ở bước tìm kiếm, không sửa chuỗi canonical.
- Lọc cả raw review và đầu ra hiển thị để không chỉ giấu lỗi trên UI.
- Không cắt cứng danh sách caption phù hợp ở sáu nguồn nữa.

Gate vẫn là heuristic bảo thủ theo cụm từ, không phải hệ hiểu ngữ nghĩa tổng quát hoặc xác suất chính xác.

### C. Caption dính nội dung bảng

Caption và header bảng có thể cùng block. Đã dừng ghép khi dòng kế tiếp có dòng cùng hàng ở cột khác, tránh caption nuốt `STT`, `Tên bảng`, `Khóa chính`…

Giữ offset/tọa độ gốc. Không biến bước phát hiện ranh giới này thành phân tích nội dung bảng. Đã có test caption nhiều dòng và header nhiều cột.

### D. Hai “KẾT LUẬN” và cây mục sai

Một ô bảng có chữ KẾT LUẬN bị nhận thành heading, khiến các mục 6.7/6.8 bị gắn nhầm cha. Đã bổ sung kiểm tra hình học dòng cùng hàng/cột để loại ô bảng khỏi heading, giữ kết luận thật. Không xóa tiêu đề chỉ vì trùng tên.

### E. Sai nhãn phiên bản và nguồn PDF

Trang tổng hợp trước đó dựng đối tượng thiếu `LanNop`, khiến v3 hiện “Phiên bản 1”. Đã gắn kết quả với đúng model bản nộp; file, phiên bản và viewer cùng một định danh. Snapshot legacy không gắn được bản nộp không được mở bừa PDF khác.

### F. Loading lâu vì fresh lặp lại — lỗi quan trọng nhất gần đây

Một lượt phân tích mới từng chạy 20 vòng mà chỉ giữ 5/28 gói: worker truyền `fresh=true` ở mọi vòng, Python xóa checkpoint mỗi lần.

**Đã sửa `AnalyzeReportVersion`: chỉ `fresh && attempt === 1`.** Các vòng sau dùng checkpoint. Tách force enqueue khỏi fresh generation, thêm nút tiếp tục gửi `fresh=0`. Không sửa trạng thái partial thành completed để che lỗi.

UI dừng animation khi tác vụ đã dừng, hiển thị “Lượt phân tích mới đã dừng” khi hết số vòng; cho mở “Xem trạng thái / Tiếp tục”. Thông báo terminal không còn hứa tự tiếp tục khi không có lịch retry.

### G. Cache key thay đổi giữa tiến trình Python

Prompt biên tập reader từng dựng dict nguồn từ `set` chưa sắp xếp. Ba tiến trình có `PYTHONHASHSEED` khác nhau tạo ba key khác nhau cho cùng dữ liệu, có thể gọi lại bước đã làm.

Đã đổi vòng lặp thành `for i in sorted(ids)` trong `instructor.py`. Có test chạy ba tiến trình thật để xác minh key ổn định. Không đổi version reader hàng loạt hoặc xóa tất cả cache.

### H. Seeder demo nguy hiểm

SQL test cũ dùng REPLACE/ID cố định, có thể đụng dữ liệu nhóm khác. Đã bỏ seeder test khỏi luồng `DatabaseSeeder` mặc định, chặn trừ khi môi trường testing có opt-in riêng.

**Máy nhận tự tạo dữ liệu test mới bằng luồng ứng dụng hoặc fixture cô lập. Không chạy lại SQL demo đó trên database đang có dữ liệu.**

## 6. File cần đọc trước khi sửa tiếp

Đường dẫn dưới đây tương đối so với root project ở máy nhận:

| Thành phần | File |
|---|---|
| Queue job, fresh/resume, bảo vệ token | `app/Jobs/AnalyzeReportVersion.php` |
| Enqueue, fingerprint, trạng thái và đọc JSON | `app/Services/AiReportProcessingService.php` |
| Lưu kết quả/snapshot/formatter | `app/Services/AiSummaryService.php` |
| Merge kết quả | `app/Services/AiResultMerger.php` |
| Provenance và ngữ cảnh | `app/Services/AiRequirementResultPolicy.php` **(file mới)** |
| Gate PHP | `app/Services/AiRequirementRelevance.php` **(file mới)** |
| Matcher PHP | `app/Services/AiRequirementCrossMatcher.php` |
| Adapter Python | `app/Services/lab_pdf_summary.py` |
| Review PDF trực tiếp | `app/Services/verified_requirement_review.py` |
| Gate Python | `app/Services/pdf_lab/ai_runtime/requirement_relevance.py` **(file mới)** |
| Output guard | `app/Services/pdf_lab/ai_runtime/requirement_output_guard.py` **(file mới)** |
| Matcher Python | `app/Services/pdf_lab/ai_runtime/requirement_cross_matcher.py` |
| Reader và key biên tập | `app/Services/pdf_lab/ai_runtime/instructor.py` |
| Kiểm chứng review | `app/Services/pdf_lab/ai_runtime/requirement_review/validation.py` |
| Heading/caption | `app/Services/pdf_lab/core/heading_detector.py`, `caption_index.py` |
| API trạng thái, quyền xem, viewer | `app/Http/Controllers/BaoCaoAiController.php` |
| UI/trạng thái và resume | `public/js/ai_teacher_report.js` |
| Giao diện chung | `resources/views/partials/ai_summary_11_sections.blade.php`, `ai_teacher_report.blade.php`, `ai_verified_requirements.blade.php`, `ai_lab_sources.blade.php` |
| Trang tổng hợp GV | `resources/views/giangvien/baocao/show.blade.php` |
| Cấu hình | `config/ai_processing.php` |
| Khởi động worker | `app/Console/Commands/ServeWithAiWorkerCommand.php`, `AiWorkCommand.php`, `AiSetupCommand.php` |

Các test mới chưa tracked cần nhận đủ: `AiRequirementResultPolicyTest.php`, `AiDemoSeedSafetyTest.php`, `AiIsolatedRegressionTest.php`, `test_heading_table_labels.py`, `test_requirement_output_guard.py`, `test_reader_checkpoint_determinism.py`, fixture `tests/fixtures/requirement_scope.json`.

## 7. Version và cấu hình hiện tại

```text
pipeline_version: pdf-lab-v1.9.1-review-output-guard
caption version: caption-text-2
matcher: requirement-scope-5
output guard: review-output-guard-1.3
review_engine: verified_v2
queue connection: ai
queue name: ai-reports
max_passes: 20
max_generation_calls mỗi lượt: 5
input_budget: 8500
max_output_tokens: 16384
pass_seconds: 260
```

Model local đang dùng khi kiểm tra: `gemini-3.5-flash-lite`. Các giới hạn gọi được cấu hình riêng theo model/project; không tăng RPM hoặc số vòng để che việc checkpoint bị reset.

Kết quả chính lưu trong `khoibaocaotiendo.TomTatJson` theo từng bản nộp; `TomTatAI` là phần văn bản. `TomTatBaoCao` là snapshot tương thích được cập nhật cho bản phù hợp. Không lấy snapshot bản cũ làm kết quả hiện hành của bản mới.

Storage nội bộ liên quan: `storage/app/private/ai_cache`, `ai_progress`, `ai_history`, file limiter `ai_limits.sqlite`. Máy nhận không cần copy cache/history/limiter của máy hiện tại; để pipeline tạo lại với dữ liệu mới.

## 8. Chuẩn bị máy khác

Môi trường đã chạy trên máy hiện tại: Windows/Laragon, PHP 8.3.32, Python 3.10.6, PyMuPDF 1.28.2. Project khai báo Laravel 11. Dùng `composer.lock` và requirements của source nhận được; không tự nâng dependency trong đợt ổn định.

Tại root project máy nhận:

```powershell
composer install
python -m pip install -r app/Services/pdf_lab/requirements.txt
```

Chuẩn bị `.env` theo máy mới, không copy key/mật khẩu vào chat/Git:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TEN_DATABASE_LOCAL_CUA_BAN
DB_USERNAME=TAI_KHOAN_LOCAL_CUA_BAN
DB_PASSWORD=MAT_KHAU_LOCAL_CUA_BAN
QUEUE_CONNECTION=database
GEMINI_API_KEY=KEY_LOCAL_CUA_BAN
GEMINI_MODEL=gemini-3.5-flash-lite
PYTHON_PATH="C:/DUONG_DAN_PYTHON_CUA_BAN/python.exe"
```

Các giá trị viết hoa là placeholder, không phải thông tin đăng nhập thật. PHP và Python phải cùng nhìn thấy PDF ở đường dẫn storage thực tế.

```powershell
php artisan config:clear
php artisan ai:setup --check-only
php artisan storage:link
php artisan serve
```

`php artisan serve` đã được mở rộng để bật worker khi khởi động, tránh phải nhớ chạy riêng trên máy dev. Nếu dùng Laragon/Apache/Nginx mà không chạy lệnh serve, cần một worker riêng:

```powershell
php artisan ai:work
```

Khi cập nhật code/config PHP: báo worker kết thúc bằng `php artisan queue:restart`, sau đó khởi động lại `php artisan serve` hoặc `php artisan ai:work`. **queue:restart không tự tạo một worker mới nếu không có supervisor.** Đừng chỉ nhìn file lock còn tồn tại; service kiểm tra khóa OS để xác định worker thật.

Kiểm tra schema/database mới theo migration thực tế trước khi chạy. Không dùng `migrate:fresh`/seed tùy tiện trên database có dữ liệu. Không cần import database từ máy này.

## 9. Dữ liệu test máy hiện tại — chỉ tham khảo, không chuyển

Nhóm từng dùng: SV73–74–75, GV44; topic `DT_TEST737475`. Các ID dưới đây là ID local, không phải hằng số ứng dụng:

| Giai đoạn | Đợt | Nội dung |
|---|---:|---|
| 1 (local ID 248) | 221 | Khảo sát/mô tả nghiệp vụ |
| 1 | 222 | Phân tích/đặc tả Use Case |
| 1 | 223 | Thiết kế CSDL và chức năng |
| 2 (local ID 249) | 224 | Cài đặt và kiểm thử hệ thống |

Snapshot khi viết bàn giao:

- Đợt 223 bản v4: job/tóm tắt/đối chiếu **completed**, 28/28 gói và 8 chương được lưu. Lượt này mất khoảng **15 phút 29 giây**, không phải 3–5 phút.
- Đợt 224 bản v1 đã được người dùng nộp: **tóm tắt completed, đối chiếu partial, job waiting** tại thời điểm kiểm tra. Trạng thái có thể thay đổi sau snapshot; cần đọc status thật, không coi ghi chú là live monitor.
- Đợt 222 từng còn partial; không nói toàn bộ dữ liệu demo đều hoàn tất.

## 10. Mẫu yêu cầu giai đoạn 2 để máy mới tự tạo

**Giai đoạn 2 — Cài đặt và kiểm thử hệ thống**

**Đợt 1 — Báo cáo cài đặt và kiểm thử hệ thống**

1. Trình bày kết quả cài đặt chức năng đăng nhập và phân quyền sử dụng hệ thống theo các vai trò người dùng.
2. Trình bày các chức năng quản lý đề tài và đăng ký đề tài đã triển khai, kèm mô tả thao tác và giao diện minh họa.
3. Trình bày chức năng theo dõi tiến độ: sinh viên nộp báo cáo, giảng viên xem báo cáo, nhận xét và yêu cầu chỉnh sửa.
4. Trình bày các chức năng tổ chức hội đồng, ghi nhận điểm và quản lý kết quả khóa luận đã cài đặt.
5. Trình bày việc kiểm thử các chức năng chính, gồm trường hợp kiểm thử, kết quả mong đợi và kết quả thực tế được ghi nhận.

Lưu ý: Cần có mô tả cụ thể trong thân báo cáo và chỉ rõ mục/trang minh họa. Phân biệt nội dung đã cài đặt hoặc kiểm thử với thiết kế, kế hoạch và hướng phát triển. Không yêu cầu nộp mã nguồn ở đợt này.

Trong đợt mẫu này, đặt năm mục và lưu ý vào **một trường `NoiDungYeuCau`**. Không lặp chúng thành các yêu cầu rộng ở `stage_requirements` hoặc `round_description`. Đã kiểm tra parser giữ đúng năm mục 1–5 và một điều kiện chung cho mẫu này.

## 11. Bằng chứng kiểm thử và cách hiểu

- Bộ hồi quy trước lượt sửa fresh cuối: **76 test Python** và **76 test PHP** đạt.
- Sau sửa fresh/resume: suite tích hợp database cô lập **35 test / 177 assertions**, suite UI/presentation liên quan **26 test / 103 assertions** đạt.
- Sau sửa key biên tập ổn định: test mới + adapter + verified bridge **31 test Python đạt**. Đây là subset, không cộng 31 vào 76 để quảng cáo tổng mới.
- Kiểm tra 7 PDF, tổng 428 trang: **349/349 offset caption được trích xuất** khớp canonical. Không có nghĩa phát hiện đủ mọi caption.
- Mẫu thủ công 11 trang: 15 caption tìm đúng/gắn đúng mục; tên caption sạch tăng 13/15 → 15/15 sau sửa header bảng.
- Mười tình huống chọn caption tham chiếu trên các trang đã xem: TP 14, FP 0, FN 0. Mẫu nhỏ, có nội dung báo cáo chồng lặp; không suy ra AI chính xác 100%.
- Benchmark Gemini thật trên một fixture 6 trang: baseline 109,422 giây/8 generate; bản mới 99,500 giây/8 generate. Cả hai có lỗi kiểm tra kế hoạch tiêu chí. Bản cũ vẫn có thể báo review hoàn tất, bản mới giữ partial. Không dùng phép đo này để tuyên bố đối chiếu đầy đủ nhanh hơn hoặc đáng tin 80%.

Các test database đã chạy trên database riêng có tiền tố `ai_stability_`. **phpunit.xml mặc định của project từng trỏ vào database local nghiệp vụ**; máy nhận phải tạo cấu hình test cô lập riêng trước khi chạy các feature test có ghi dữ liệu. Các cấu hình/work scripts ở workspace máy này có đường dẫn tuyệt đối, không được giả định chạy nguyên xi ở máy khác.

## 12. Checklist test ở máy mới

1. Nhận đủ source mới, kiểm tra Git status và các file mới kể trên.
2. Chạy preflight Python/PyMuPDF/DB/storage; xác nhận worker thật đang chạy.
3. Tự tạo GV, nhóm SV, đề tài, giai đoạn/đợt và quyền sở hữu dữ liệu test mới.
4. Nộp một PDF ngắn, xác nhận Queue chạy ngoài request HTTP và kết quả được lưu.
5. Refresh/chuyển tab/mở PDF không tăng số job hoặc số lượt sinh AI.
6. Bấm phân tích mới: chỉ lượt đầu reset checkpoint; những vòng sau số gói hoàn tất phải tăng.
7. Khi hết ngân sách một vòng: chờ/tiếp tục giữ số gói đã đọc. Terminal partial phải dừng spinner và không hứa tự retry nếu không có lịch.
8. Thử PDF khác và yêu cầu khác; không lấy nguồn từ bản cũ.
9. Mở vài dẫn chứng văn bản và caption, xác nhận đúng bản PDF/trang/vùng.
10. Kiểm tra tài khoản khác không đọc trạng thái, tải PDF hoặc preview nguồn của nhóm không thuộc quyền.
11. Test caption tên hình, chữ dính, phân tích/thiết kế, TODO/TBD, yêu cầu chỉ có tiêu đề và yêu cầu có nhiều ý.
12. Không coi hiện “Có dẫn chứng” là đã chứng minh mọi ý của yêu cầu; giảng viên quyết định đánh giá.

Nếu loading lâu, ghi nhận **report ID, token, trạng thái, attempts, progress stage, số Map/chương, heartbeat và errors**. Phân biệt đang chạy, chờ quota, lỗi model/validator, worker tắt, session hết hạn và UI chưa cập nhật. Không bấm fresh nhiều lần hoặc xóa cache trước khi tìm nguyên nhân.

## 13. Giới hạn còn tồn tại

- Chưa có đánh giá ngữ nghĩa độc lập trên bộ báo cáo lớn đủ để công bố phần trăm chính xác tổng thể.
- Gemini có thể trả kế hoạch hoặc dẫn chứng bị validator từ chối. Không bỏ validator để làm đẹp demo.
- Chọn nguồn theo cụm từ vẫn có thể bỏ sót hoặc chọn chưa đủ các vế; cần phản hồi thận trọng và mở PDF.
- Không đọc nội dung hình/bảng; caption không chứng minh chất lượng sơ đồ.
- Tài liệu scan hoặc caption chỉ nằm trong ảnh nằm ngoài phạm vi text-only.
- PDF dài có thể xử lý hơn 10 phút; chưa cam kết latency tối đa 3–5 phút.
- Tạo index PDF mới có thể tốn thời gian; quan sát tiến độ trang để phân biệt chạy thật với treo.
- Chưa kiểm thử tải đồng thời nhiều người dùng hoặc mọi môi trường deploy.

## 14. Tài liệu chi tiết ở workspace nguồn

Các file này nằm trong thư mục `outputs` của cuộc chat máy hiện tại, **không tự có trong repo clone**:

- `TONG_HOP_SUA_DOI_BUOI_TOI_20261102.md`.
- `BAO_CAO_ON_DINH_AI_TRUOC_DEMO_20261102.md`.
- `BAO_CAO_KIEM_THU_BO_SUNG_AI_20261103.md`.
- `BUG_FRESH_CHECKPOINT_20261103.md`.
- `MASTER_PROMPT_ON_DINH_AI_DA_HOP_NHAT.md`.

File bàn giao này đủ để bắt đầu nắm ngữ cảnh. Nếu cần chuyển thêm tài liệu, chỉ chuyển các báo cáo/source phù hợp; **không chuyển database theo yêu cầu người dùng**.

### Chỉ dẫn cho AI agent tiếp nhận

Đọc source thực tế và quy định repository trước khi sửa. Người dùng đang tương đối hài lòng, nên ưu tiên giữ kết quả, tái hiện lỗi thật rồi vá nhỏ. Không tự khôi phục tab công việc, thêm OCR/vision, đổi schema/enum, tăng số lượt Gemini, push GitHub hoặc reset thay đổi local. Không dùng ID/tài khoản của máy nguồn làm dữ liệu bắt buộc. Tạo dữ liệu test mới và báo đúng test/giới hạn quan sát được.


## Trạng thái lượt chạy kiểm tra trước khi bàn giao

Lúc 12:39 ngày 03/11/2026, lượt mới của Đợt 224 dừng tạm ở 8/28 gói Map vì lỗi API rate_limit, quota unknown. Worker vẫn hoạt động; Queue hẹn thử tiếp lúc 12:43:40. Đây không phải xác nhận đã hết quota ngày. Bản tóm tắt hoàn thành 28/28 được giữ từ lượt trước, không phải tiến độ lượt mới. Không bấm phân tích mới liên tục; kiểm tra checkpoint và trạng thái retry trước.
