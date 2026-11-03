# Tỷ lệ yêu cầu có dẫn chứng — 03/11/2026

- Chỉ số tìm thấy nguồn, không phải tỷ lệ hoàn thành, điểm hay độ chính xác AI. Không ghi vào PhanTramHoanThanh và không thay dữ liệu duyệt của giảng viên.
- N yêu cầu lớn chia đều 100/N. Mục cha có con được ghi rõ bằng số/bullet chia đều trọng số cho con, áp dụng đệ quy; chỉ cộng các lá.
- Lấy cấu trúc từ nhãn yêu cầu gốc đã lưu, không lấy số lượng report_points do AI sinh ra làm trọng số. Không tự tách các vế câu theo ngữ nghĩa.
- Mỗi lá cần source_references có quote_verified=true, trang dương, câu trích không rỗng và không bị từ chối ngữ nghĩa. Mục con chỉ liên kết khi tiêu đề điểm đối chiếu khớp tên mục (bỏ số thứ tự/dấu câu). Không đoán liên kết nếu tên khác nhau.
- Caption được tính là tìm được tên hình/bảng; đánh dấu rõ cần giảng viên xem PDF, không xác nhận hiểu nội dung hình.
- Một mục lớn không có con được tính có dẫn chứng khi tìm được ít nhất một nguồn hợp lệ, không đồng nghĩa đã phủ hết nội dung yêu cầu.
- Dữ liệu thiếu, yêu cầu đã đổi hoặc legacy chưa ràng buộc đúng ngữ cảnh: không hiện phần trăm. Đang chạy/kết quả partial: ghi kết quả tạm thời/đã lưu.
- Tính trên kết quả hiện có khi render; không gọi thêm Gemini, không sửa Queue/checkpoint, không thay thuật toán phân đoạn. Bảng chi tiết cho thấy trọng số và phần đóng góp.
- Đã đổi khung mô tả cũ ở trang nộp sinh viên để không hứa AI tự chấm hoàn thành.

## Lỗi lượt chạy được phát hiện cùng lúc

Lượt Đợt 224 đã đọc 28/28 gói nhưng reader trả bản dài hơn 180 từ sau các lần biên tập giới hạn. processing_error trước đây gộp lỗi kiểm tra này thành invalid_request, khiến UI ghi nhầm API không chấp nhận yêu cầu. Đã tách output_validation và giữ dừng có kiểm soát; không tự bỏ qua kiểm chứng hoặc cắt văn bản tùy tiện. UI nhận diện cả engine_code cũ, không cần sửa lịch sử DB. Tiêu đề partial không còn ghi Tóm tắt đã hoàn thành chỉ vì kết quả cũ được giữ. Người dùng có thể tiếp tục từ checkpoint để sinh lại tác vụ lỗi; chưa khẳng định lượt thực đã hoàn thành.
