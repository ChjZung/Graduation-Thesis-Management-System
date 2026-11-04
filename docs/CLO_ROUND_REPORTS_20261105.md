# Báo cáo CLO theo ngành và đợt và căn chỉnh sidebar

Ngày hoàn thành: 05/11/2026. Source: `C:\laragon\www\KhoaLuanCuNhan`. Không commit/push, không sửa điểm thật.

## Sử dụng

- Giữ nút xuất CLO tại trang hiện có để mở popup; đã bỏ mục sidebar và trang chọn độc lập theo yêu cầu. URL cũ `/bao-cao-clo` chuyển về trang hiện có và mở popup.
- Bắt buộc chọn **học kỳ, ngành sinh viên**. Đợt và hội đồng không bắt buộc. Có mẫu KLCN/KLKS và Excel/PDF.
- Chọn hội đồng: chỉ xuất hội đồng đó trong ngành/học kỳ đã chọn. Không chọn hội đồng nhưng chọn đợt: xuất toàn ngành trong đợt. Để trống cả hai: xuất toàn ngành trong học kỳ.
- Giảng viên chỉ chọn được hội đồng mình là thành viên; server chặn mã hội đồng khác kể cả sửa URL. Đợt nếu chọn phải khớp hội đồng. Admin/Giáo vụ và Trưởng khoa vẫn theo phạm vi ngành được cấp quyền.
- Admin/Giáo vụ truy cập các ngành; Trưởng khoa trong khoa phụ trách; GVHD được xuất toàn bộ sinh viên ngành/đợt khi có đề tài mình hướng dẫn trong phạm vi đó, theo lựa chọn của người dùng. Không giới hạn chỉ sinh viên do mình hướng dẫn. Sinh viên/Trưởng bộ môn không được mở endpoint này.
- Cập nhật theo yêu cầu mới: ngành xác định theo `SinhVien.MaNganh`; chỉ khi trống mới dùng `SinhVien.lop.MaNganh`. Không lấy ngành đề tài, mã ngành hay tên hội đồng thay cho ngành của sinh viên. Cùng quy tắc được dùng cho quyền GVHD, danh sách chọn ngành/đợt, bộ lọc sinh viên và tên ngành trên file xuất. Nhóm khác ngành được tách theo từng sinh viên trước khi kiểm tra phiếu chấm và tính CLO.
- Nếu hồ sơ chưa xác định được loại khóa luận, thiếu phiếu chấm hoàn thành, trùng sinh viên giữa các hội đồng hoặc không có dữ liệu phù hợp thì báo rõ và không xuất chính thức. Không cho `force=1` vượt điều kiện.

## File báo cáo

- Excel: sheet đầu `5B_CĐR_CLO` tổng hợp phạm vi đã chọn; các sheet sau giữ bảng đối chiếu 3 giảng viên và chi tiết tiêu chí **riêng từng hội đồng**, vì thành viên hội đồng có thể khác nhau. Không cộng/gộp nhầm giám khảo giữa hội đồng. Tên file, metadata Excel/PDF và nhãn tổng hợp phản ánh đúng hội đồng/đợt/toàn ngành.
- Thêm **THỐNG KÊ CLO ĐÁNH GIÁ CLĐT** dưới danh sách/tổng hợp sinh viên. Có 6 dòng: điểm tối đa; ngưỡng đạt; điểm đạt tương ứng; số sinh viên đạt; tỷ lệ đạt; kết quả thống kê.
- Mẫu tham chiếu thực tế nằm tại `C:\Mac\Home\Documents\1_monhoc\Khoá luận\CNTT_Mau_Tong hop CĐR_Khoa luan cu nhan_HK2 (25-26)_19-06_FULL (36sv).xlsx` (đường dẫn người dùng gửi bị ngắt thành thư mục, đã tìm đúng file).
- Đã đọc công thức `xl/worksheets/sheet10.xml`, C49:K55: điểm CLO đạt từ 4/10 (40%); tỷ lệ = COUNTIF điểm đạt / số sinh viên; kết quả thống kê ĐẠT nếu tỷ lệ >= 40%. Hai ngưỡng trùng số 40% nhưng mang ý nghĩa khác nhau. Công thức Excel mới giữ đúng quy tắc của mẫu.
- Các công thức nằm trong file xuất, có tính lại khi thay đổi điểm nguồn. PDF thể hiện cùng số liệu và quy tắc trên.
- Export mới gọi bộ tính CLO hiện có với `persist=false`, chỉ lấy phiếu hoàn thành, không cập nhật lại `KetQuaSinhVien`. Các lời gọi cũ giữ mặc định `persist=true` để bảo toàn chức năng hiện tại.

## Sidebar

Chỉnh CSS chung `.role-badge`: căn giữa, khoảng cách phía trên 18px, dưới 10px, kích thước/padding thống nhất, viền nhẹ; bỏ margin icon gây lệch. Sửa cả rule override cuối file vốn ép căn trái. Áp dụng cho Sinh viên, Giảng viên, Quản trị viên, Trưởng khoa và Trưởng bộ môn; giữ menu và phân quyền.

## File sửa/thêm

Dưới root source nêu trên:

- `app/Services/CloRoundReportService.php` (mới): phạm vi, quyền và tổng hợp dữ liệu.
- `app/Http/Controllers/CloRoundReportController.php` (mới): form chọn và xuất file.
- `app/Services/CloReportExportService.php`: workbook toàn đợt, bảng thống kê, định dạng metadata và dòng đề tài.
- `app/Services/CloCalculationService.php`: tùy chọn chỉ đọc khi export mới; mặc định cũ giữ nguyên.
- `app/Http/Controllers/GiangVien/ChamDiemController.php`: route xuất cũ chuyển qua màn hình chọn.
- `resources/views/clo_reports/_selection.blade.php`, `export_modal.blade.php`, `public/js/clo_export_modal.js`: popup chọn học kỳ/ngành/đợt/hội đồng và định dạng.
- `resources/views/pdf/bao_cao_clo_theo_dot.blade.php` (mới): PDF toàn đợt kèm thống kê.
- `resources/views/giangvien/hoidong/_cham-diem.blade.php`: nút xuất mở màn hình chọn phạm vi.
- `resources/views/layouts/admin.blade.php`, `giangvien.blade.php`, `truongkhoa.blade.php`: link và trạng thái menu mới.
- `routes/web.php`: endpoint chọn và xuất được xác thực/phân quyền backend.
- `public/css/huit_theme.css`: căn nhãn vai trò sidebar dùng chung.
- `tests/Feature/CloRoundReportTest.php` (mới).

## Kiểm chứng

Cập nhật phạm vi tùy chọn hội đồng/đợt ngày 05/11/2026:
- Backend: `CloRoundReportTest` + `CloThreeLecturersCalculationAndExportTest`: **15 tests, 121 assertions đạt**, trên database kiểm thử riêng và transaction rollback.
- Frontend: `node --test tests/Frontend/cloExportScope.test.cjs`: **2 tests đạt**; kiểm tra chọn ba phạm vi, lọc hội đồng theo đợt, xóa lựa chọn cũ khi không còn phù hợp và khôi phục lựa chọn từ URL. `node --check` đạt.
- Đọc dữ liệu hiện có với GV00000023, HK2627_1, ngành sinh viên 7480201, Đợt 1, HD01: service trả **1 hội đồng, 3 sinh viên**, ngành hiển thị Công nghệ Thông tin dù tên hội đồng chứa Khoa học Dữ liệu. Không sửa dữ liệu thật, không thay điểm.
- Các ca chống sửa URL, hội đồng không tham gia, hội đồng không khớp học kỳ/đợt, thiếu phiếu ở phạm vi toàn ngành, xuất XLSX/PDF và giữ kết quả điểm hiện tại đều đạt. Chưa kiểm tra thao tác bằng trình duyệt trong lượt cập nhật này.

- Database riêng `ai_stability_20261103_7783c6`, dữ liệu test dùng transaction và rollback.
- `CloRoundReportTest` và `CloThreeLecturersCalculationAndExportTest`: **10 tests, 68 assertions, đạt**.
- Đã kiểm tra: bắt buộc đợt; quyền SV bị chặn; quyền ngành Trưởng khoa; GVHD xuất cả sinh viên của GVHD khác; gom 2 hội đồng; lọc đúng đợt/ngành/loại; đợt rỗng; thiếu phiếu không được vượt bằng force; không đổi dữ liệu điểm; công thức số lượng/tỷ lệ/kết quả cập nhật khi điểm thay đổi; tính CLO và lưu dữ liệu ở luồng cũ vẫn đạt kiểm thử hồi quy.
- Tạo file XLSX và PDF thật từ HTTP export. XLSX đọc/render lại bằng artifact-tool; kiểm tra cả 5 sheet của fixture 2 hội đồng. Kiểm tra XML file đã lưu không có cell lỗi cached. PDF render kiểm tra bố cục bảng và thống kê, fixture 1 trang.
- Hiện vật test ở workspace `...\b-n-v-o-c-laragon\work\clo_round_export.xlsx`, `.pdf` và `work\clo_qa`. Đây là dữ liệu thử, không phải số liệu báo cáo sinh viên thật.
- Chưa mở bằng Excel desktop hoặc thao tác sidebar bằng trình duyệt ở từng kích thước màn hình; chưa load-test báo cáo hàng nghìn sinh viên. Không tuyên bố đã kiểm chứng các trường hợp đó.

## Kiểm chứng bổ sung ngành sinh viên

Sau thay đổi nguồn ngành: 12 tests, 83 assertions đạt trên database test riêng. Bao gồm ưu tiên ngành sinh viên khi ngành lớp/đề tài/hội đồng khác nhau; fallback theo lớp khi ngành sinh viên trống; popup có đúng ngành/đợt của sinh viên; nhóm khác ngành chỉ xuất đúng sinh viên cần lấy và không bị chặn bởi phiếu chấm của sinh viên ngoài phạm vi.

## Cập nhật popup và quyền giảng viên

Đã bỏ trang trung gian và mục CLO trên sidebar của Admin/Giảng viên/Trưởng khoa theo yêu cầu. Nút xuất CLO có sẵn mở trực tiếp popup. Lỗi xuất/thiếu phiếu hiển thị trong popup qua JSON, không điều hướng sang trang 403 trắng. Tải file bằng Blob sau khi xuất thành công.

Sửa quyền giảng viên: có thể là GVHD **hoặc thành viên hội đồng** có sinh viên thuộc ngành/đợt đang chọn. Vẫn chặn ngành/đợt không liên quan và vẫn yêu cầu đủ phiếu hoàn thành để xuất báo cáo chính thức. Bộ test CLO theo đợt: 8 tests, 62 assertions đạt, gồm giảng viên hội đồng không phải GVHD và lỗi 422 khi thiếu phiếu.

Đối chiếu dữ liệu local chỉ đọc: HK2627_1 / Đợt 1 / ngành 7480201, HD01 có 3 sinh viên và 9 phiếu hoàn thành; HD02 có 3 sinh viên, chưa có phiếu. Do đó HD01 đã chấm xong không đồng nghĩa toàn Đợt 1 đã chấm xong.
