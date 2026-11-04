# Kết quả chỉnh sửa phiếu chấm và rà soát báo cáo Admin — 04/11/2026

## Phạm vi và màn hình thực tế

Source local nhánh `final` dùng chung `resources/views/giangvien/hoidong/_cham-diem.blade.php` cho Chủ tịch, Thư ký và Phản biện. Không phát hiện ba trang lưu điểm độc lập để sửa riêng.

- Trang chính: `GET /giangvien/hoi-dong?tab=chamdiem&hd=...&hoso=...`, route `giangvien.hoidong.index`, `GiangVien/HoiDongController::index`.
- URL tương thích: `GET /giangvien/chamdiem` và `/giangvien/cham-diem` qua `ChamDiemController::index`, chuyển hướng sang tab chính. View standalone `giangvien/chamdiem/index` cũng tái sử dụng partial chung.
- Cửa sổ xem trước phiếu nằm trong partial chung; đã bổ sung thao tác chỉnh sửa/lưu tương ứng.
- Lưu điểm: `POST /giangvien/chamdiem`, route `giangvien.chamdiem.store`, `ChamDiemController::store`.
- Chuẩn bị phiếu/tiêu chí: `chuanBiPhieu`, `capNhatChucNang` trong cùng controller.

## Những thay đổi

1. Phiếu đã xác nhận hiển thị nút **Chỉnh sửa phiếu chấm điểm** và **Lưu phiếu chấm điểm** khi được phép. Các ô điểm ở chế độ xem ban đầu; nút Chỉnh sửa mở các ô có quyền sửa. Cửa sổ xem trước dùng cùng form lưu.
2. Phiếu đã lưu, kể cả nháp và đã hoàn thành, có thể chỉnh sửa từ khi hội đồng mở chấm đến trước công bố kết quả. Phiếu mới vẫn chịu hạn nhập điểm hiện có; không mở chấm trước thời điểm bắt đầu.
3. Sửa phiếu đã hoàn thành không đưa về nháp, không thay ngày xác nhận ban đầu, không xóa chữ ký nếu request gửi chữ ký rỗng. Giữ nhận xét và lịch sử theo quy tắc hiện có.
4. Phân biệt công bố quyết định hội đồng với công bố kết quả. Kiểm tra trạng thái hồ sơ và bản ghi `KetQuaSinhVien` đã công bố; không dùng `HoiDong.TrangThaiCongBo` làm khóa điểm.
5. Bỏ khả năng dùng tham số `unlock_token` bất kỳ để bỏ qua kiểm tra quyền sửa. Giữ cơ chế mở khóa đặc biệt đã có của quản lý; dữ liệu kết quả đã công bố không bị phép tổng hợp thông thường ghi đè.
6. Lưu và chuẩn bị phiếu sử dụng khóa hồ sơ; công bố kết quả dùng cùng khóa để tránh đua với lượt lưu điểm cuối. Lưu điểm kiểm tra lại quyền và thay đổi tiêu chí trong transaction.
7. Thư ký bị khóa chỉnh thông tin/tiêu chí khi đã có điểm lưu, kể cả nháp hoặc điểm 0, trên đúng hồ sơ và hội đồng. Giao diện vô hiệu hóa nút; backend từ chối cập nhật với HTTP 409. Hồ sơ khác chưa bắt đầu chấm vẫn được chuẩn bị bình thường.
8. Bổ sung kiểm tra hồ sơ thuộc hội đồng ở thao tác chuẩn bị phiếu. Giảng viên phải là thành viên hội đồng khi lưu; chỉ cập nhật phiếu của chính mình.
9. Giữ mã phiếu hiện hữu khi sửa để lịch sử vẫn tham chiếu đúng. Phiếu mới ràng buộc cả hội đồng, hồ sơ, giảng viên và sinh viên, tránh ghi đè phiếu của hồ sơ khác.

## Phần giữ nguyên

- Rubric, trần điểm, công thức trung bình, trọng số, xếp loại và công thức/ngưỡng CLO.
- Quy trình ký tên, nhận xét đã lưu và audit log bất biến.
- Cơ chế quản lý mở khóa đặc biệt, yêu cầu lý do và lưu vết hiện có.
- Các luồng AI báo cáo, phân đoạn và checkpoint không thuộc bản vá này.

## Rà soát báo cáo Admin

`ThongKeBaoCaoController::index`, `previewReport` và `exportPdf` cùng chuẩn hóa bộ lọc rồi lấy dữ liệu qua `ThongKeService::thongKeTongHop`.

Bộ lọc UI `Dat`, `KhongDat`, `ChuaXong` khớp nhánh lọc ở service. Kiểm thử đã đối chiếu dữ liệu chưa có kết quả/đã đủ phiếu, dữ liệu trang Admin, số sinh viên trong preview và tạo PDF thật thành công. Không phát hiện lỗi cần sửa ở bộ lọc/export trong phạm vi này.

Các mẫu PDF thống kê Admin hiện không có cột CLO chi tiết. Báo cáo CLO riêng tại màn hình chấm điểm dùng `CloReportExportService`; kiểm thử xuất Excel đã xác nhận ba sheet `TongHop_3GV_CLO`, `5B_CĐR_CLO`, `03C_CLO_ĐCK` có mặt. Chưa thêm mẫu/cột CLO vào PDF Admin vì chưa có đặc tả vị trí, trường dữ liệu và mẫu xuất cần dùng; đây là điểm cần chốt nếu muốn mở rộng, không phải chức năng đã triển khai trong bản vá.

## File thay đổi

- `app/Http/Controllers/GiangVien/ChamDiemController.php`
- `app/Models/HoSoBaoVe.php`
- `app/Models/HoiDong.php`
- `app/Services/GradeCalculationService.php`
- `resources/views/giangvien/hoidong/_cham-diem.blade.php`
- `tests/Feature/ScoreEditingPolicyAndAuditLogTest.php`
- `tests/Feature/GiangVienHoiDongGradingTimeTest.php`
- Tài liệu này.

## Database

Không tạo migration mới. Đã chạy toàn bộ migration còn thiếu trên database kiểm thử riêng để kiểm chứng nhánh `final`.

Trên database local đang dùng, chỉ áp dụng hai migration có sẵn, mang tính bổ sung schema:

- `2026_11_04_170000_add_diem_clo_to_ket_qua_sinh_vien_table`
- `2026_11_04_180000_add_score_edit_window_and_audit_log_tables`

Đã xác minh môi trường `local`, kết nối MySQL localhost, và số bản ghi `HoiDong`, `PhieuChamDiem`, `KetQuaSinhVien` trước/sau không đổi. Không chạy seeder, không thay điểm hay dữ liệu mẫu đang sử dụng. Hai migration chuẩn hóa vai trò hội đồng và tên học kỳ chưa được áp dụng trên database local trong lượt này.

## Kiểm chứng

Kiểm thử dữ liệu chạy trong database riêng `ai_stability_20261103_7783c6`, transaction rollback sau từng case.

| Bộ kiểm thử | Kết quả cuối |
|---|---|
| `ScoreEditingPolicyAndAuditLogTest` | 14 test, 91 assertions, đạt |
| `GiangVienHoiDongGradingTimeTest` | 8 test, 73 assertions, đạt |
| Case Excel ba sheet trong `CloThreeLecturersCalculationAndExportTest` | 1 test, 5 assertions, đạt |
| Tổng | 23 test, 169 assertions, đạt |

Bao gồm lưu điểm cả ba vai trò, tải lại giá trị, giữ chữ ký/ngày xác nhận, lưu phiếu hoàn thành không thành nháp, audit thay đổi, khóa kết quả đã công bố, từ chối token mở khóa giả, chống sửa trước giờ, hạn nhập phiếu mới, khóa tiêu chí sau điểm nháp, phạm vi hồ sơ, lọc báo cáo Admin, PDF thật và cấu trúc Excel CLO hiện có.

Các test thời gian cũ được sửa fixture để không dựa vào hồ sơ có kết quả công bố từ dữ liệu seed; điểm TC2 của fixture được đưa về đúng trần rubric hiện tại. Không đổi trần điểm của ứng dụng để làm test đạt.

Không bổ sung kịch bản riêng CLO dưới 4. Không thực hiện chấm/sửa điểm trên dữ liệu thật để thử. Chưa kiểm chứng thao tác chuột trên trình duyệt và bố cục PDF bằng ảnh; kiểm thử giao diện hiện tại là HTTP render/markup, kiểm thử PDF xác nhận tạo file hợp lệ. Chưa có kiểm thử cạnh tranh đa tiến trình cho thao tác công bố/lưu điểm.

Không commit, push hoặc deploy trong lượt này.
