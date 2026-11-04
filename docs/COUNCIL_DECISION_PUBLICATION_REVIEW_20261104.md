# Báo cáo sửa quyết định hội đồng và xuất báo cáo — 04/11/2026

Đã triển khai trên source local `C:\laragon\www\KhoaLuanCuNhan`. Không pull, đổi nhánh, reset database, commit hoặc push. Giữ các thay đổi local có sẵn. Không thử công bố trên database đang dùng để demo.

## Cách sử dụng

1. Trưởng khoa → Quản lý hội đồng → chọn **học kỳ và đợt bảo vệ**, rồi bấm **Xem trước quyết định** phía trên danh sách.
2. Trang xem trước hiển thị một PDF gồm quyết định chung, Phụ lục 1 thành viên các hội đồng, Phụ lục 2 sinh viên/đề tài/GVHD. Đây là toàn bộ hội đồng thuộc khoa + học kỳ + đúng đợt, không phụ thuộc trang hoặc từ khóa tìm kiếm.
3. Nếu thiếu thành phần bắt buộc, thiếu nhóm đề tài hoặc thông tin quyết định chưa đủ/chưa thống nhất, hệ thống nêu lỗi và khóa công bố. Bổ sung ở chức năng quản lý hiện có rồi mở lại bản xem trước.
4. Bấm **Công bố quyết định**: lưu đúng PDF vừa xem, cập nhật thông tin công bố quyết định, tạo thông báo **Toàn trường**. Sau thành công mở chi tiết thông báo có PDF đính kèm.
5. Admin/Giáo vụ → Quản lý hội đồng → chọn bộ lọc → **Xuất báo cáo CSV (Excel)**.

## Trước và sau

| Nội dung | Trước | Sau |
|---|---|---|
| Xem trước | Gắn với một hội đồng | Một quyết định cho toàn đợt theo khoa/học kỳ/đợt chính xác |
| Nguồn dữ liệu | Dễ hiểu là hội đồng/danh sách đang xem | Truy vấn riêng lấy toàn bộ đợt, không bị phân trang/tìm kiếm giới hạn |
| Công bố | Thao tác công bố tách khỏi bản PDF đã xem | Bắt buộc token của bản xem trước thuộc chính tài khoản/khoa/đợt |
| Nội dung PDF | Có thể tái sinh từ dữ liệu hiện tại | Đóng băng byte PDF; kiểm tra SHA-256 và thay đổi dữ liệu trước công bố |
| Người nhận | Các tài khoản liên quan hội đồng | Một thông báo Toàn trường, tất cả vai trò truy cập qua cơ chế inbox hiện có |
| Export Admin | CSV có tiêu đề bảng cơ bản, logic bộ lọc lặp | Vẫn CSV; thêm đơn vị, đúng tên báo cáo, học kỳ/đợt, ngày lập, phạm vi và số hội đồng; dùng chung bộ lọc với index |

## Cơ chế lưu và chống trùng

- File xem trước nằm trên disk `local` riêng tư, token UUID lưu trong session của người tạo. Hiệu lực 1 giờ, tối đa 5 bản giữ trong một session. Các bản bị đẩy ra được xóa.
- Bản xem trước ghi fingerprint của dữ liệu hội đồng, thành viên, đề tài và sinh viên. Nếu dữ liệu khác lúc xem trước, phải mở lại PDF trước khi công bố.
- Khi công bố, khóa khoa và các hội đồng trong transaction; xác thực phạm vi, dữ liệu, SHA-256; lưu PDF thành công trước khi cập nhật trạng thái và thông báo.
- File công bố có tên theo khoa/học kỳ/đợt/thời điểm và hậu tố riêng, nằm ở `public/quyet_dinh_hoi_dong`. Thông báo tham chiếu trực tiếp file đã lưu, không tái sinh khi tải.
- Một thông báo có `DoiTuongNhan = Toàn trường` dùng chung một PDF. Không tạo N bản ghi/N file theo số tài khoản; không cần thêm queue hay gửi email.
- Bấm lại cùng lượt đã công bố không tạo thêm thông báo. Nếu tạo/lưu PDF thất bại, không phát thông báo hỏng; transaction hoàn tác trạng thái.
- Lịch sử người/thời điểm/phạm vi/file được giữ trong thông tin công bố hội đồng và thông báo đã phát hành. Không thêm cơ chế thu hồi/công bố lại ngoài nghiệp vụ hiện có.
- Chỉ thay `HoiDong.TrangThaiCongBo`, `NgayCongBo`, `NguoiCongBo`, `FileQuyetDinh`. Không đổi điểm, CLO, trạng thái kết quả hoặc thời hạn sửa điểm.

## Mẫu và định dạng

- Tham chiếu PDF mẫu người dùng: `C:\Mac\Home\Documents\1_monhoc\Khoá luận\qd-klks-nganh-cntt-20260525045507-e.pdf`: quyết định chung + 2 phụ lục, không sao chép dấu/chữ ký.
- Tham chiếu mẫu nội bộ: `resources/views/thongke_baocao/pdf/layout_pdf.blade.php` và `mau_06_hoi_dong.blade.php` để thống nhất đơn vị, cách đặt tên, thông tin phạm vi và tiêu đề cột.
- PDF dùng A4, DejaVu Serif có tiếng Việt; bảng tiêu đề lặp, hàng tránh ngắt giữa chừng, nhóm hội đồng tránh tách tiêu đề khỏi bảng khi vừa trang.
- Chức năng exportReport hiện chỉ có **CSV**, không phải XLSX. CSV hỗ trợ Unicode BOM, xuống dòng/dấu phẩy trong ô, số thứ tự và tên cột nhưng **không lưu font/cỡ chữ/logo/độ rộng cột/phân trang**. Không giả CSV thành Excel có định dạng và không thêm định dạng mới. Đã bỏ nhãn dễ hiểu nhầm Excel/CSV thành CSV (Excel).
- Đếm nhóm/sinh viên có loại trùng theo mã; chuẩn hóa vai trò Chủ tịch/Thư ký/Phản biện; tránh công thức bảng tính chạy từ dữ liệu người dùng.

## File chính sửa/thêm trong lượt này

Đều nằm dưới `C:\laragon\www\KhoaLuanCuNhan\`:

- `app/Http/Controllers/TruongKhoa/HoiDongController.php`: phạm vi công bố, bản xem trước riêng tư, công bố file đã xem, transaction và thông báo toàn trường; route cũ chuyển sang luồng chung.
- `app/Services/CouncilDecisionScopeService.php` (mới): truy vấn đầy đủ phạm vi, eager loading, fingerprint và kiểm tra điều kiện công bố.
- `app/Services/HoiDongPdfService.php`: tạo PDF toàn đợt; metadata thiếu không lấy mặc định bịa.
- `resources/views/pdf/quyet_dinh_hoi_dong_toan_dot.blade.php`: mẫu quyết định/phụ lục chung, giữ tiếng Việt và bố cục nhiều trang.
- `resources/views/truongkhoa/hoidong/preview_decision.blade.php` (mới): trang PDF + điều kiện/nút công bố.
- `resources/views/truongkhoa/hoidong/index.blade.php`: nút xem trước nổi bật và phạm vi rõ ràng; không công bố đợt rỗng.
- `resources/views/truongkhoa/hoidong/show.blade.php`: dẫn tới quyết định chung, bỏ nút tải riêng trùng với luồng xem trước.
- `app/Http/Controllers/Admin/HoiDongController.php`: dùng chung query index/export, chuẩn hóa CSV thực tế.
- `resources/views/admin/hoidong/index.blade.php`: nhãn export đúng định dạng.
- `routes/web.php`: endpoint trang xem trước và đọc file snapshot, đặt trước route động ID.
- `tests/Feature/CouncilBatchPublicationTest.php`: mở rộng kiểm thử công bố toàn đợt/snapshot/quyền/lỗi/chống trùng.
- `tests/Feature/AdminCouncilExportTest.php` (mới): xuất và đọc lại CSV thực tế.

Tái sử dụng phần đính kèm đã sửa local trước đó: `NotificationAttachmentService`, endpoint tải trong `FileDownloadController`, policy thông báo, bộ lọc có/không file và inbox của các vai trò. Không ghi đè những bản vá chấm điểm, giao diện, lịch hoặc AI khác đang có trong cây làm việc.

## Kết quả kiểm chứng đã chạy

Chạy trên database riêng `ai_stability_20261103_7783c6`, mọi test dữ liệu dùng transaction và rollback. Chạy cuối: **34 tests, 305 assertions, PASS**, PHP 8.3.32, PHPUnit 10.5.66, 18,273 giây.

| Bộ kiểm thử | Số test | Kết quả |
|---|---:|---|
| CouncilBatchPublicationTest | 17 | Đạt |
| AdminCouncilExportTest | 1 | Đạt |
| NotificationAttachmentTest | 8 | Đạt |
| HoSoDocumentDownloadTest | 8 | Đạt |

Đã kiểm tra bằng HTTP feature tests và file tạo thật:

- 7 hội đồng vượt phân trang 5: PDF chung chứa đủ, kể cả hội đồng đã công bố trong cùng phạm vi; không lẫn khoa/học kỳ/đợt khác.
- Thêm search/page/trạng thái phụ không làm thiếu dữ liệu xem trước.
- Đợt trống, metadata thiếu/xung đột, token sai/hết hạn, dữ liệu thay đổi đều không công bố thành công.
- Xem trước và file đã phát hành bằng nhau từng byte; đổi tên hội đồng sau công bố không làm file tải từ thông báo thay đổi.
- Bấm công bố lại không tăng số thông báo; toàn lượt chỉ thêm 1 thông báo Toàn trường.
- Admin, Giáo vụ, Trưởng khoa, Trưởng bộ môn, Giảng viên, Sinh viên đều thấy trong inbox/chuông và mở/tải được PDF, gồm tài khoản không tham gia hội đồng.
- Giả lập lỗi render PDF và lỗi lưu file: không có thông báo mới, không để trạng thái công bố thành công.
- So sánh toàn bộ thuộc tính hồ sơ trước/sau công bố: không đổi dữ liệu kết quả/điểm.
- CSV UTF-8 BOM, tên file đúng phạm vi, 15 cột, đủ 7 dòng ngoài phân trang, không trùng mã, giữ chuỗi tiếng Việt dài và ô có dấu phẩy/xuống dòng; lọc tìm kiếm/ngày/trạng thái/học kỳ/đợt đúng query index; dữ liệu công thức được thoát.
- Đã render PDF mẫu 7 trang và xem contact sheet tất cả trang; không thấy chữ bị cắt, đè bảng hoặc bảng tách tiêu đề. Kiểm tra tọa độ text nằm trong trang.
- `php -l` các controller/service/routes liên quan đạt; `git diff --check` không có lỗi khoảng trắng (có cảnh báo CRLF của các file local khác).

## Hiện vật kiểm thử

Trong workspace `C:\Users\hochidung\Documents\Codex\2026-10-25\b-n-v-o-c-laragon\work\council-pdf-reference\`:

- `generated-batch.pdf`: PDF dữ liệu kiểm thử, 7 hội đồng, 7 trang.
- `qa-contact-sheet.png`: ảnh tổng các trang đã kiểm tra.
- `admin-council-export.csv`: CSV xuất thực tế, dữ liệu kiểm thử.

Các tên/người ký kiểm thử trong hiện vật chỉ thuộc fixture database riêng, không phải văn bản đã công bố thật.

## Phạm vi chưa kiểm chứng / giới hạn

- Chưa thao tác giao diện bằng trình duyệt với phiên đăng nhập thật; feature tests đã thực thi route, middleware và render Blade.
- Chưa mở CSV bằng ứng dụng Excel desktop; đã đọc lại file thực tế bằng parser CSV, kiểm tra nội dung/Unicode/cột/dòng. Font/chiều rộng cột phụ thuộc phần mềm mở CSV.
- Chưa load-test hàng nghìn hội đồng hoặc hàng trăm yêu cầu công bố đồng thời; có transaction/khóa và kiểm tra retry, nhưng chưa coi đó là phép thử concurrency thực tế.
- PDF đã kiểm tra với fixture 7 hội đồng; hàng bảng cực dài hơn một trang cần kiểm tra thêm nếu phát sinh.
- Bản xem trước hết hạn sau 1 giờ; cần mở lại để công bố. File snapshot riêng tư từ session đã mất có thể cần dọn định kỳ khi vận hành lâu dài.
- Không tự cập nhật/phát lại các quyết định cũ và không tự bổ sung dữ liệu chính thức đang thiếu.
