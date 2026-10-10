# BÁO CÁO KIỂM THỬ CHỨC NĂNG PHÊ DUYỆT ĐỀ TÀI (VAI TRÒ TRƯỞNG BỘ MÔN)

- **Dự án**: Hệ thống quản lý khóa luận tốt nghiệp (HUIT Thesis Management System)
- **Framework**: Laravel 11.x + MySQL
- **Người thực hiện**: Senior Developer
- **Thời gian kiểm thử**: 10/10/2026
- **Test Suite**: `tests/Feature/TruongBoMonPheDuyetDeTaiTest.php` + Full Suite (`php artisan test`)

---

## 1. TỔNG QUAN KẾT QUẢ KIỂM THỬ (3 LƯỢT CHẠY)

| Lượt kiểm thử | Lệnh thực thi | Tổng số test | Tổng số assertions | Thời gian | Trạng thái |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Lượt 1** | `php artisan test` | 44 / 44 | 184 | 2.18s | **PASSED (100%)** |
| **Lượt 2** | `php artisan test` | 44 / 44 | 184 | 2.09s | **PASSED (100%)** |
| **Lượt 3** | `php artisan test` | 44 / 44 | 184 | 1.98s | **PASSED (100%)** |

---

## 2. CHI TIẾT CÁC TEST CASE BẮT BUỘC

### Case 1: Đổi học kỳ -> danh sách giảng viên và số liệu thay đổi tương ứng
- **Test method**: `TruongBoMonPheDuyetDeTaiTest::test_case_1_doi_hoc_ky_thay_doi_so_lieu_giang_vien`
- **Thao tác**:
  - TBM đăng nhập (`TBM_BM_CNPM_GV00000001`), truy cập `/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2`.
  - Kiểm tra số liệu của các GV thuộc bộ môn (GV00000007, GV00000004 có đề tài chờ duyệt).
  - Đổi query string sang `hocKy=HK2425_1`.
- **Kỳ vọng**: Danh sách hiển thị theo học kỳ mới, số liệu từng trạng thái (Chờ duyệt, Sửa, Đã duyệt, Từ chối) được tính toán chính xác bằng 1 query GROUP BY gộp, không bị cache sai hoặc N+1.
- **Kết quả**: **PASS** (Thời gian phản hồi ~ 0.06s).

---

### Case 2: Cố tình truy cập giảng viên/đề tài của bộ môn khác bằng URL -> bị chặn (403/404)
- **Test method**: `TruongBoMonPheDuyetDeTaiTest::test_case_2_chan_truy_cap_giang_vien_va_de_tai_bo_mon_khac_403`
- **Thao tác**:
  - TBM BM_CNPM cố tình truy cập danh sách đề tài của GV thuộc BM_ATTT: `GET /truong-bo-mon/phe-duyet-de-tai/giang-vien/GV00000005`.
  - TBM BM_CNPM cố tình truy cập chi tiết đề tài của BM_ATTT: `GET /truong-bo-mon/phe-duyet-de-tai/de-tai/DT_ATTT_CHO_01`.
  - TBM truy cập mã đề tài không tồn tại: `GET /truong-bo-mon/phe-duyet-de-tai/de-tai/DT_KHONG_TON_TAI_999`.
- **Kỳ vọng**: Server chặn ngay lập tức ở cấp Controller bằng `assertGiangVienBelongsToBoMon` và `assertDeTaiBelongsToBoMon`, trả về HTTP 403 Forbidden. Đề tài không tồn tại trả về 404 Not Found.
- **Kết quả**: **PASS** (403/404 trả về chuẩn xác ở server-side, không rò rỉ dữ liệu chéo).

---

### Case 3: Thử cả 3 hành động: Phê duyệt, Yêu cầu chỉnh sửa, Từ chối
- **Test methods**:
  - `TruongBoMonPheDuyetDeTaiTest::test_case_3a_phe_duyet_de_tai_thanh_cong_va_ghi_lich_su`
  - `TruongBoMonPheDuyetDeTaiTest::test_case_3b_yeu_cau_chinh_sua_validation_va_thanh_cong`
  - `TruongBoMonPheDuyetDeTaiTest::test_case_3c_tu_choi_validation_va_thanh_cong`
- **Thao tác**:
  1. **Phê duyệt**: POST `.../duyet` có ghi chú tùy chọn -> Trạng thái đề tài cập nhật thành `'Chờ duyệt cấp Khoa'`, lưu `NgayDuyetBM`, `NguoiDuyetBM`, tạo 1 bản ghi bất biến trong `ChiTietDuyetDeTai` với `HanhDong = 'Phê duyệt'`.
  2. **Yêu cầu chỉnh sửa**:
     - Bỏ trống hoặc nhập dưới 5 ký tự -> Báo lỗi validation (`assertSessionHasErrors(['YeuCauSua'])`).
     - Nhập nội dung hợp lệ -> Trạng thái cập nhật thành `'Yêu cầu chỉnh sửa'`, lưu `LyDoTuChoi`, tạo log lịch sử trong `ChiTietDuyetDeTai`.
  3. **Từ chối**:
     - Bỏ trống hoặc nhập dưới 5 ký tự -> Báo lỗi validation (`assertSessionHasErrors(['LyDoTuChoi'])`).
     - Nhập lý do hợp lệ -> Trạng thái cập nhật thành `'Từ chối'`, lưu `LyDoTuChoi`, tạo log lịch sử trong `ChiTietDuyetDeTai`.
- **Kết quả**: **PASS** (Cả 3 luồng hoạt động chính xác, validation chặt chẽ, ghi nhận lịch sử đầy đủ).

---

### Case 4: Mở 2 tab cùng 1 đề tài (Race Condition)
- **Test method**: `TruongBoMonPheDuyetDeTaiTest::test_case_4_mo_hai_tab_thao_tac_trung_lap_bi_chan_server_side`
- **Kịch bản**: Giả lập Tab 1 đã hoàn tất duyệt đề tài (trạng thái chuyển thành 'Chờ duyệt cấp Khoa'). Tab 2 vẫn mở trang cũ và người dùng ấn nút Duyệt hoặc nút Từ chối.
- **Kỳ vọng**:
  - Cơ chế `lockForUpdate()` trong DB Transaction phát hiện đề tài không còn ở trạng thái `['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']`.
  - Server throw Exception và redirect back kèm session error thông báo rõ ràng cho người dùng: *"Đề tài hiện đang ở trạng thái '...', không thể phê duyệt lại"*.
  - Không sinh lỗi 500 unhandled, dữ liệu không bị ghi đè hay sai lệch.
- **Kết quả**: **PASS** (Server chặn thành công thao tác thứ 2).

---

### Case 5: Giảng viên chưa có đề tài -> Hiển thị đúng (0 đề tài, không crash)
- **Test method**: `TruongBoMonPheDuyetDeTaiTest::test_case_5_giang_vien_chua_co_de_tai_hien_thi_dung`
- **Thao tác**:
  - Giảng viên `GV00000002` (thuộc BM_CNPM) chưa có đề tài nào trong học kỳ `HK2627_2`.
  - Kiểm tra bảng Trang 1: Hiển thị dòng của GV00000002 với tổng số = 0, chờ duyệt = 0, đã duyệt = 0,...
  - Kiểm tra Trang 2 (`/giang-vien/GV00000002?hocKy=HK2627_2`): Hiển thị banner rỗng *"Giảng viên chưa đề xuất đề tài nào trong học kỳ này"*, giao diện đồng bộ, không crash hay lỗi chia cho 0.
- **Kết quả**: **PASS**.

---

### Case 6: Regression Test toàn hệ thống
- **Thao tác**: Chạy toàn bộ các test feature và unit hiện có:
  - `AdminGiangVienSemesterFilterTest` (5 tests)
  - `PhanQuyenDuyetDeTaiTest` (5 tests)
  - `AuthTest` (3 tests)
  - `MiddlewareTest` (5 tests)
  - `ApiTest` (16 tests)
  - `TruongBoMonPheDuyetDeTaiTest` (8 tests)
  - Unit tests (2 tests)
- **Kỳ vọng**: 100% test suites của các role Giáo vụ, Giảng viên, Sinh viên, BCN Khoa, Admin đều PASSED.
- **Kết quả**: **44/44 tests PASSED (184 assertions)**, không có bất kỳ regression nào.

---

## 3. THÔNG TIN PHỤC VỤ TEST THỦ CÔNG TRÊN TRÌNH DUYỆT (LOCALHOST)

- **Địa chỉ web server**: `http://127.0.0.1:8000`
- **Tài khoản Trưởng Bộ Môn CNPM**:
  - Tên đăng nhập: `TBM_BM_CNPM_GV00000001`
  - Mật khẩu: `123456`
  - Họ tên: `PGS.TS. Nguyễn Văn Hùng`
  - Bộ môn quản lý: `Công nghệ phần mềm (BM_CNPM)`
- **Tài khoản Trưởng Bộ Môn ATTT (để thử chặn chéo)**:
  - Tên đăng nhập: `TBM_BM_ATTT_GV00000005`
  - Mật khẩu: `123456`
  - Họ tên: `TS. Lê Thị Mai`
  - Bộ môn quản lý: `An toàn thông tin (BM_ATTT)`

### Các liên kết trực tiếp để trải nghiệm:
1. **Trang 1 - Danh sách giảng viên theo học kỳ**:
   `http://127.0.0.1:8000/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2`
2. **Trang 2 - Danh sách đề tài của giảng viên GV00000007**:
   `http://127.0.0.1:8000/truong-bo-mon/phe-duyet-de-tai/giang-vien/GV00000007?hocKy=HK2627_2`
3. **Trang 3 - Chi tiết và phê duyệt đề tài (Đang chờ duyệt)**:
   `http://127.0.0.1:8000/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01`
4. **Trang 3 - Xem đề tài có lịch sử góp ý trước đó**:
   `http://127.0.0.1:8000/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_SUA_01`
5. **Thử nghiệm chặn truy cập chéo (Bảo mật)**:
   Đăng nhập bằng tài khoản TBM CNPM và click vào link đề tài của BM ATTT:
   `http://127.0.0.1:8000/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_ATTT_CHO_01`
   *(Kết quả: Trang hiển thị 403 Forbidden chuẩn bảo mật)*
