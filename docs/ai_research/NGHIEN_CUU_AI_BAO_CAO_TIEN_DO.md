# Thiết kế chức năng tóm tắt báo cáo tiến độ bằng AI

## Bài toán và phạm vi đề cương

Giảng viên phải đọc nhiều báo cáo để tìm việc nhóm đã làm, các khó khăn và kế hoạch tiếp theo. Chức năng hỗ trợ rút gọn việc tra cứu bằng tóm tắt có bằng chứng; giảng viên giữ quyền nhận xét và duyệt báo cáo.

Mô hình ngôn ngữ lớn (LLM) là mô hình xử lý/sinh ngôn ngữ đã được huấn luyện. Hệ thống sử dụng API của mô hình, không tự huấn luyện hoặc fine-tune. Prompt Engineering là thiết kế câu lệnh, ràng buộc và lược đồ đầu ra để hướng dẫn mô hình thực hiện đúng bài toán. JSON là định dạng dữ liệu có cấu trúc dùng trao đổi giữa Python, Laravel và cơ sở dữ liệu.

## Đầu vào và nguồn dẫn chứng

- PDF: PyMuPDF lấy lớp văn bản theo số trang vật lý. Ảnh/scan chưa được OCR hay phân tích hình ảnh; phải thông báo giới hạn này.
- DOCX: đọc OOXML bằng ZipFile và ElementTree; trích đoạn văn và bảng theo thứ tự tài liệu. Giới hạn kích thước XML/giải nén, không thực thi macro hoặc liên kết ngoài. Mỗi đoạn/bảng có ID và nhãn ổn định; không gán số trang Word.
- Nguồn gốc được giữ bằng `source_pages` (ID nguồn nội bộ để tương thích) và `source_locations` gồm id, kind, label. Chỉ PDF có trang vật lý; DOCX có đoạn/bảng.

## Thuật toán và prompt

1. Đọc nguồn, giữ cấu trúc và ID.
2. Chia ngữ cảnh theo kích thước cấu hình, có overlap để giữ phần giao nhau.
3. Map: trích đúng ba nhóm công việc đã thực hiện, khó khăn, kế hoạch tiếp theo. Mỗi mục gồm content, evidence nguyên văn và ID nguồn. Không biến yêu cầu, mô tả thao tác người dùng hoặc kế hoạch thành công việc đã thực hiện.
4. Reduce: gộp và loại trùng, tạo tổng quan ngắn, giữ câu trích và ID nguồn; nhóm không có thông tin dùng [].
5. Đối chiếu câu evidence với văn bản nguồn. Câu chưa khớp không được công bố như dẫn chứng đã xác minh.
6. Lưu JSON theo từng lần nộp và đọc lại kết quả đã lưu khi xem trang.

Model theo cấu hình `services.gemini.model`, ghi nhận model thực tế trong metadata; tham số temperature hiện là 0.1. Mặc định chunk_size trong luồng ứng dụng là 12000 ký tự, overlap 350. Các thông số được đọc từ code/cấu hình khi tái lập, không khẳng định một phiên bản Python hoặc model cố định cho mọi máy.

## Lược đồ nghiệp vụ

Ba nhóm bắt buộc là `completed_work`, `difficulties`, `next_steps`. Tổng quan `overall_summary`, metadata `analysis` và nguồn dẫn chứng là thông tin phụ trợ, không phải các nhóm nghiệp vụ bổ sung. Schema hiện tại là phiên bản 3. Lịch sử JSON cũ vẫn được đọc tương thích.

Mỗi mục có content, evidence, source_pages, source_locations và verified. verified chỉ xác nhận câu khớp văn bản nguồn; nó không tự chứng minh phần mềm đã chạy đúng.

## Giao diện và xử lý thiếu thông tin

Hiển thị tổng quan và các nhóm có nội dung. Các nhóm rỗng được gom vào một dòng thông báo. Nếu việc đọc chưa hoàn tất hoặc đang dùng fallback, ghi “Chưa trích xuất được từ phần văn bản đã đọc”, không khẳng định tài liệu không đề cập.

Dẫn chứng PDF mở đúng trang và tô vàng; DOCX mở bản xem văn bản và tô vàng đoạn. Nội dung tài liệu được hiển thị như văn bản, không thực thi HTML hoặc chỉ dẫn trong file. Popup giữ khả năng cuộn ngang/dọc, xem lớn và sao chép.

Đối chiếu yêu cầu giảng viên là phần hỗ trợ mở thêm, tách khỏi ba nhóm tóm tắt. Kết luận đáp ứng phải qua bước kiểm chứng mức độ liên quan của câu trích dẫn; lỗi API không được biến thành đánh giá sinh viên thiếu nội dung.

## Kiểm thử và đánh giá

Kiểm thử kỹ thuật bao gồm PDF/DOCX, bảng, nhóm rỗng, sai nguồn, lỗi API, tài liệu chỉ có kế hoạch, nhiều phiên bản và các thao tác xem dẫn chứng. Kiểm thử giao diện và khớp câu nguồn không thay thế đánh giá chất lượng ngữ nghĩa.

Lệnh `ai:benchmark-reports` ghi độ trễ đầu cuối, tình trạng xử lý, JSON hợp lệ và nguồn xử lý thực tế. Precision/Recall/F1 chỉ tính khi có đáp án chuẩn đủ ba nhóm, bằng cách khớp câu evidence đã chuẩn hóa theo từng nhóm (micro exact evidence). Không có nhãn thì để null; không suy ra độ chính xác từ số trang/số mục hay tự gán 100%.

Hạn chế: PDF scan/ảnh chưa được OCR, Word không có trang cố định, mô hình có thể nhầm phân loại hoặc diễn giải; giảng viên cần kiểm tra dẫn chứng và đánh giá cuối cùng. Không dùng kết quả của bộ mẫu kiểm soát để suy rộng độ chính xác cho mọi báo cáo.
