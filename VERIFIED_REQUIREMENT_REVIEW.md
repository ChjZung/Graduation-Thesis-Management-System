# Tích hợp đối chiếu yêu cầu từ Lab

Pipeline pdf-lab-v1.7-verified-requirement-review. config/ai_processing.php bật lab.review_engine=verified_v2.

Giữ Map–Reduce và bản đọc theo chương hiện có. Sau khi hoàn tất bản đọc, verified_requirement_review.py gọi engine ai_runtime/requirement_review để đối chiếu trực tiếp văn bản/ô bảng của PDF với từng yêu cầu gốc. Không dùng tóm tắt con như nguồn thay thế PDF.

Laravel database queue ai-reports vẫn quản lý công việc: nộp báo cáo/lệnh phân tích chỉ enqueue, AnalyzeReportVersion xử lý trong worker ai:work. Không đưa hàng đợi SQLite/Streamlit hoặc kho DPAPI của Lab vào ứng dụng Laravel. Key lấy từ cấu hình server và truyền qua biến môi trường cho Python; không nằm trong tham số lệnh hay JSON.

Cache Map và chương giữ cấu trúc cũ. Cache đối chiếu mới ở storage/app/private/ai_cache/verified_pdf_review, gắn hash PDF, phiên bản bài nộp, mô tả, model và phiên bản prompt/guard. Checkpoint được lưu từng bước. Các lượt nền chia ngân sách, chờ quota/503 và tiếp tục; lỗi ngữ nghĩa không tự được coi là thành công. Không tuyên bố độ chính xác 70%/80% khi chưa có bộ nhãn chuẩn.

UI giảng viên và sinh viên: mô tả gốc ở đầu; Có dẫn chứng / Chưa đủ dẫn chứng. SUPPORTS kèm câu trích hợp lệ được hiển thị. PARTIAL chỉ hiển thị bản viết lại thận trọng đã kiểm chứng và câu trích được chọn, có nhãn hỗ trợ một phần; không coi là đáp ứng toàn bộ. Vị trí chú thích sơ đồ được dẫn riêng để xem trực tiếp, không dùng làm bằng chứng nội dung hình. Phần thiếu nguồn có link mở báo cáo gốc, không kết luận sinh viên chưa làm. Hình/sơ đồ cần người đọc xem trực tiếp. Kết quả cũ không bị xóa; cần chọn Phân tích lại để sinh đối chiếu theo pipeline mới.

Worker: php artisan ai:work. Sau khi thay mã/cấu hình, dùng php artisan config:clear, php artisan view:clear, php artisan queue:restart rồi bảo đảm có ai:work đang chạy. Lệnh này dùng khóa file để tránh nhiều worker trên cùng máy. Cần trình quản lý dịch vụ cho tự khởi động sau khi reboot nếu triển khai lên máy chủ.

Kiểm tra tích hợp: 23 Python tests (bridge cũ + đối chiếu mới), 8 Laravel adapter/view tests (52 assertions), 17 queue/endpoint tests (68 assertions) qua; không gọi Gemini. Có thể kiểm tra cấu hình/worker mà không in key bằng php scripts/check_verified_review_runtime.php. Chưa thực hiện lượt Gemini mới trong project hay đo độ chính xác trên bộ nhãn chuẩn. Việc kiểm tra giao diện trong phiên tài khoản cần một phiên đăng nhập hợp lệ.

## Khởi động môi trường phát triển

Chạy `php artisan serve`: ứng dụng tự bật `php artisan ai:work` bằng cùng PHP CLI và thư mục project, xác nhận khóa worker trước khi mở web. Worker đã chạy sẽ được dùng lại; khóa OS trong ai:work tiếp tục ngăn worker trùng. Không cần chạy thêm terminal worker.

Khi serve kết thúc bình thường hoặc xử lý tín hiệu dừng, worker do nó tạo được dừng theo. Worker có sẵn từ terminal khác không bị dừng. Tắt cả terminal/máy đột ngột có thể ngắt lượt đang xử lý; lần mở sau vẫn cần quy tắc phục hồi hàng đợi/checkpoint hiện có. Giữ cửa sổ serve hoạt động trong lúc phân tích.

Dùng `php artisan serve --without-ai-worker` khi đã có trình quản lý worker riêng. Cơ chế này dành cho máy phát triển; triển khai Apache/Nginx vẫn cần dịch vụ worker được quản lý riêng. Không tự khởi động worker trong từng HTTP request. Không tự giám sát/khởi động lại worker khi nó chết giữa phiên serve; có thể khởi động riêng bằng `php artisan ai:work`.

Kiểm tra thay đổi: test lifecycle worker (bật, dùng lại, dọn tiến trình, lỗi khởi động, tùy chọn tắt) không gọi LLM; kiểm tra thật serve ở cổng phụ với worker có sẵn, dừng cổng phụ và xác nhận worker có sẵn vẫn hoạt động.

## Kiểm tra yêu cầu gộp sơ đồ và đặc tả

Coverage 1.3 nhận các cách gọi “đặc tả các ca sử dụng chính” và “đặc tả các Use Case”, lấy danh sách mục con từ PDF để tìm từng ca sử dụng. Guard 2.7 xét phạm vi tiêu chí con, tránh đưa từ “sơ đồ” của yêu cầu cha sang tiêu chí đặc tả văn bản. Mục hình/sơ đồ không qua LLM xác nhận nội dung khi chưa đọc ảnh; chỉ có điều hướng bằng chú thích khớp nguyên văn.

Thử trên báo cáo 124 trang, yêu cầu 2 của đợt 135: tìm đúng 8 mục đặc tả (trang PDF 69–77), nguồn LLM được kiểm chứng gồm 3 mục SUPPORTED và 5 mục PARTIAL, sơ đồ có vị trí trang 68 cần giảng viên xem trực tiếp. Kết quả riêng này đã cập nhật mà không đổi bản tóm tắt và yêu cầu 1/3. Đây là phép thử khả thi trên một báo cáo, chưa là phép đo độ chính xác tổng quát. 27 kiểm thử bridge/adapter qua; thử LLM riêng có 9 tiêu chí, 8 lượt sinh tích lũy, khoảng 94 giây xử lý tích lũy (không phải thời gian xử lý toàn báo cáo).

## Nội dung chưa hoàn thiện trong báo cáo

Mục Công việc trong báo cáo có hai tab con: Công việc đã hoàn thành và Nội dung chưa hoàn thiện. Bộ kiểm tra report-content-review-1.0 chạy cục bộ trên cấu trúc PDF đã có và tái sử dụng câu trích Map đã khớp nguồn; không thay đổi chunking, prompt Map/Reduce hoặc thêm lượt LLM. Dữ liệu content_review được lưu cùng kết quả theo hash file, có phiên bản bộ kiểm tra riêng.

Chỉ ghi nhận mục trống khi mục chính không có mục con, xác định được dòng tiêu đề, không có nội dung thực chất và không có dấu hiệu ảnh/bảng/đồ họa hoặc lỗi trích xuất. Mục chứa hình, chú thích hình/bảng, trang quét và cấu trúc bất định được xếp riêng là cần xem trực tiếp/chưa xác định. Mục cha không có lời dẫn nhưng có mục con không bị đánh dấu trống. TODO/TBD trong ví dụ minh họa được loại. Kế hoạch tương lai đơn thuần không là công việc chưa hoàn thành; chỉ trích câu Map có phủ định hoàn thành rõ ràng, đúng vị trí, không là điều kiện Use Case.

Không suy ra sinh viên chưa làm từ việc tài liệu thiếu nội dung. Không tạo tỷ lệ hoàn thành từ số mục trống. Kiểm tra cấu trúc lỗi sẽ trả trạng thái unavailable mà không làm hỏng bản tóm tắt. Giao diện kiểm tra đúng hash và không hiển thị kết quả cũ khi file thay đổi.

Đã bổ sung kiểm tra cục bộ cho báo cáo 124 trang tại các đợt 129, 130, 135; giữ nguyên các phần tóm tắt và đối chiếu. Kiểm tra trực tiếp một số trang PDF xác nhận mục chỉ có tiêu đề. Kiểm thử: 36 Python tests (bao gồm hồi quy bridge/phân loại) và 4 PHP tests / 13 assertions cho giao diện, hash, formatter và merge. Không gọi LLM trong bước bổ sung này.

## Hiển thị kết quả từng bước

Python xuất snapshot nguyên tử sau checkpoint, sau mỗi bản đọc chương hợp lệ và sau mỗi bước đối chiếu đã kiểm chứng. Laravel lưu snapshot nếu token tác vụ, fingerprint đầu vào, hash file và phiên bản bài nộp còn khớp; snapshot không tự kết thúc tác vụ hoặc gắn canonical fingerprint. Không thêm lượt gọi LLM cho việc xuất kết quả sớm. Map vẫn cần đủ dữ liệu cho bước tổng hợp liên quan; không hiển thị token đang sinh hoặc đoạn chưa kiểm chứng.

Trạng thái tóm tắt độc lập với đối chiếu: bản đọc có thể hoàn thành trong khi đối chiếu còn pending/partial. Worker vẫn xử lý các bước còn lại. Kết quả cuối truyền qua file JSON riêng để tránh trộn thông báo tiến độ vào JSON stdout trên Windows; file kết quả cuối được dọn sau khi đọc.

Endpoint trạng thái trả result_revision chỉ tính các trường kết quả hiển thị. Giao diện kiểm tra khoảng 5 giây khi tác vụ còn hoạt động, chỉ tải lại thẻ kết quả khi revision thay đổi. Cập nhật cả trang thường và cửa sổ Xem lớn, giữ tab chính/tab con, dẫn chứng đang mở và vị trí cuộn; không tải lại cả trang. Phiên đăng nhập/quyền truy cập thay đổi sẽ dừng lấy kết quả.

Kiểm thử: 37 Python tests cho bridge, xuất snapshot và phân loại nội dung; 11 Laravel adapter/view tests với 63 assertions. Phép thử snapshot xác nhận chương hợp lệ xuất hiện trước khi đối chiếu kết thúc và số lượt gọi provider không tăng. Kiểm tra trình duyệt bằng snapshot HTML thật và endpoint mô phỏng revision: cả hai thẻ cùng cập nhật, giữ tab con/dẫn chứng/vị trí cuộn, không trùng ID và vẫn đóng được cửa sổ Xem lớn. Phép thử giao diện không gọi AI hoặc thay dữ liệu bài nộp.
