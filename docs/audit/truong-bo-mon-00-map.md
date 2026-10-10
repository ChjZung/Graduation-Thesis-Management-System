# BÁO CÁO KHẢO SÁT HỆ THỐNG VÀ KIẾN TRÚC PHÂN HỆ TRƯỞNG BỘ MÔN
**Dự án**: Hệ thống Quản lý Khóa luận Tốt nghiệp (Laravel MVC + MySQL)  
**Ngày thực hiện**: 10/10/2026  
**Người thực hiện**: Senior Laravel Developer  
**Tài liệu**: `docs/audit/truong-bo-mon-00-map.md`

---

## 1. Cấu Trúc Route, Middleware Phân Quyền & Cách Xác Định Vai Trò Trưởng Bộ Môn

### 1.1. Middleware và Routing
- **Middleware phân quyền**: `App\Http\Middleware\CheckRole` (alias `role`).
  - Kiểm tra vai trò thông qua quan hệ `TaiKhoan::with('vaiTro')` (`TenVaiTro == 'Trưởng bộ môn'` hoặc mã vai trò `VT04`).
  - Middleware cũng hỗ trợ Trưởng bộ môn truy cập chức năng Giảng viên nếu cần.
- **Route Group hiện tại**:
  ```php
  Route::middleware(['auth', 'role:Trưởng bộ môn'])->prefix('truongbomon')->group(...)
  ```
  Các route hiện tại sử dụng tiền tố `/truongbomon/...`.
- **Cấu hình theo đặc tả mới**:
  Hệ thống sẽ đăng ký nhóm route chuẩn:
  ```php
  Route::middleware(['auth', 'role:Trưởng bộ môn'])->prefix('truong-bo-mon')->group(function () {
      Route::get('/phe-duyet-de-tai', [PheDuyetDeTaiController::class, 'index'])->name('truongbomon.phe_duyet_detai.index');
      Route::get('/phe-duyet-de-tai/giang-vien/{maGV}', [PheDuyetDeTaiController::class, 'danhSachDeTaiGV'])->name('truongbomon.phe_duyet_detai.giang_vien');
      Route::get('/phe-duyet-de-tai/de-tai/{maDeTai}', [PheDuyetDeTaiController::class, 'chiTietDeTai'])->name('truongbomon.phe_duyet_detai.show');
      Route::post('/phe-duyet-de-tai/de-tai/{maDeTai}/duyet', [PheDuyetDeTaiController::class, 'duyetDeTai'])->name('truongbomon.phe_duyet_detai.duyet');
      Route::post('/phe-duyet-de-tai/de-tai/{maDeTai}/yeu-cau-chinh-sua', [PheDuyetDeTaiController::class, 'yeuCauChinhSua'])->name('truongbomon.phe_duyet_detai.yeu_cau_sua');
      Route::post('/phe-duyet-de-tai/de-tai/{maDeTai}/tu-choi', [PheDuyetDeTaiController::class, 'tuChoi'])->name('truongbomon.phe_duyet_detai.tu_choi');
  });
  ```
  Đồng thời duy trì tương thích ngược với các route cũ `/truongbomon/duyet-detai` (redirect hoặc alias) để không làm hỏng bất kỳ liên kết sẵn có nào.

### 1.2. Cách Xác Định Hồ Sơ & Bộ Môn Trực Thuộc
- Sử dụng phương thức chuẩn `App\Models\GiangVien::getLoggedInGiangVien($user)` để lấy model `GiangVien` của tài khoản Trưởng bộ môn.
- Bộ môn được xác định qua `$giangVien->MaBoMon`, hoặc tra cứu trong `BoMon` qua `MaBoMon` hoặc tên đăng nhập `TBM_{MaKhoa}_{MaBoMon}_...`.
- **Nguyên tắc bảo mật đa tầng**:
  - Không tin cậy vào giao diện UI.
  - Mọi query danh sách giảng viên, danh sách đề tài và chi tiết đề tài đều bắt buộc có điều kiện `WHERE MaBoMon = :maBoMon`.
  - Nếu truy cập giảng viên (`{maGV}`) hoặc đề tài (`{maDeTai}`) thuộc bộ môn khác, controller ném ngay `abort(403, "Bạn không có quyền truy cập dữ liệu ngoài bộ môn phụ trách.")`.

---

## 2. Các Bảng & Model Liên Quan

1. **`DeTai` (`App\Models\DeTai`)**:
   - Khóa chính: `MaDeTai` (varchar 20).
   - Các trường chính: `TenDeTai`, `LinhVuc`, `MoTa`, `MucTieu`, `YeuCau`, `SoLuongSinhVienToiDa`, `FileDeCuong`, `MaGV`, `MaHocKy`, `MaHocPhan`, `HocPhan`, `TrangThai`, `LyDoTuChoi`, `NgayDeXuat`, `NgayDuyetBM`, `NguoiDuyetBM`.
   - Quan hệ: `giangVien()`, `hocKy()`, `chiTietDuyetDeTais()`, `phanCongPhanBiens()`, `phieuDangKys()`.
2. **`GiangVien` (`App\Models\GiangVien`)**:
   - Khóa chính: `MaGV` (varchar 20).
   - Quan hệ: `boMon()`, `taiKhoan()`, `deTais()`, `chiTieuHuongDans()`.
3. **`BoMon` (`App\Models\BoMon`)**:
   - Khóa chính: `MaBoMon` (varchar 20).
   - Các trường: `TenBoMon`, `MaKhoa`, `TruongBoMon`.
4. **`HocKy` (`App\Models\HocKy`)**:
   - Khóa chính: `MaHocKy` (varchar 20).
   - Các trường: `TenHocKy`, `NamHoc`, `TrangThai` ('Đang diễn ra', 'Đã kết thúc', 'Sắp diễn ra').
5. **`ChiTietDuyetDeTai` (`App\Models\ChiTietDuyetDeTai`) & Lịch Sử Xử Lý**:
   - Bảng `ChiTietDuyetDeTai` đã có sẵn trong CSDL với các trường: `MaDuyet`, `MaDeTai`, `MaGV`, `TrangThai`, `LyDo`, `NgayDuyet`, `created_at`, `updated_at`.
   - Để đáp ứng yêu cầu *"Mỗi thao tác ghi 1 dòng lịch sử: mã đề tài, hành động, trạng thái cũ → mới, nội dung phản hồi, người thực hiện, thời gian"*, ta bổ sung các cột `HanhDong` (varchar 100), `TrangThaiCu` (varchar 50), `NguoiThucHien` (varchar 100) qua migration mới.
6. **File đính kèm / Đề cương**:
   - Cột `FileDeCuong` trong `DeTai` lưu đường dẫn tài liệu (`storage/de_cuong/...` hoặc link asset).
   - Kiểm tra tồn tại và hiển thị nút "Tải file đề cương" / "Xem đề cương", ẩn khi không có file.

---

## 3. Layout Sidebar Của Trưởng Bộ Môn & Tái Sử Dụng Giao Diện

- **Layout Master**: `resources/views/layouts/truongbomon.blade.php`.
- **Cấu trúc Sidebar**:
  - Nhóm 1: "Tổng Quan" (`truongbomon.dashboard`, `thongbao.*`).
  - Nhóm 2: "Xử Lý Đề Tài" / "Phê Duyệt Đề Tài".
  - Nhóm 3: "Giám Sát Bộ Môn" (`truongbomon.theodoi.*`).
- **Thực hiện điều chỉnh**:
  - Đổi tên mục chức năng thành **"Phê duyệt đề tài"** (icon `fa-solid fa-clipboard-check`).
  - Highlight `active-sub` và giữ mở `submenu-collapse show` / `active-parent` khi route hiện tại là một trong 3 trang:
    1. Trang danh sách giảng viên theo học kỳ (`truongbomon.phe_duyet_detai.index`)
    2. Trang danh sách đề tài của giảng viên (`truongbomon.phe_duyet_detai.giang_vien`)
    3. Trang chi tiết và phê duyệt đề tài (`truongbomon.phe_duyet_detai.show`)
- **Chuẩn giao diện**:
  - Sử dụng Bootstrap 5 + HUIT Theme (`css/huit_theme.css`), phẳng, không shadow đậm, font tối thiểu 13px, badge bo góc tròn (`badge rounded-pill`).
  - Bảng sử dụng `admin-table` hoặc `table table-hover align-middle`, hàng chẵn lẻ rõ ràng, nút thao tác tròn hoặc bo góc `rounded-pill` / `rounded-3`.

---

## 4. Bảng So Sánh Hiện Trạng & Yêu Cầu Cần Bổ Sung

| Hạng mục | Hiện có trong codebase | Đặc tả mới yêu cầu | Trạng thái / Giải pháp |
| :--- | :--- | :--- | :--- |
| **Sidebar Trưởng bộ môn** | Mục "Duyệt đề xuất đề tài" trỏ tới trang phẳng cũ | Đổi tên thành "Phê duyệt đề tài", active cả 3 trang phân cấp | **Cần cập nhật layout sidebar** |
| **Trang 1: Danh sách GV theo học kỳ** | Chỉ có bảng phẳng danh sách đề tài (không gom theo GV) | Bảng danh sách GV thuộc BM, số lượng đề tài theo từng trạng thái (Chờ duyệt / Yêu cầu sửa / Đã duyệt / Từ chối), đếm bằng 1 query GROUP BY | **Cần xây dựng mới View & Action** |
| **Trang 2: Danh sách đề tài của GV** | Không có trang riêng theo từng GV | Tiêu đề, thông tin GV, học kỳ, nút Quay lại, bảng đề tài có lọc trạng thái, sắp xếp "Chờ duyệt" lên đầu; chặn 403 nếu sai BM | **Cần xây dựng mới View & Action** |
| **Trang 3: Chi tiết & Phê duyệt** | Form duyệt ở modal đơn giản, chưa có timeline lịch sử chi tiết | Timeline trực quan lưu từng bước xử lý, 3 nút thao tác (Phê duyệt, Yêu cầu sửa, Từ chối) chỉ bật khi "Chờ duyệt", modal xác nhận, validate bắt buộc lý do/góp ý, lockForUpdate chống race condition | **Cần xây dựng mới View & Actions hoàn chỉnh** |
| **Lịch sử xử lý đề tài** | Bảng `ChiTietDuyetDeTai` chưa được dùng đầy đủ | Ghi chi tiết: mã đề tài, hành động, trạng thái cũ -> mới, nội dung phản hồi, người thực hiện, thời gian | **Cần migration bổ sung cột & logic ghi log** |
| **Luồng GV nộp lại đề tài** | Đổi trạng thái về `Chờ duyệt cấp Bộ môn` | Giữ nguyên logic nộp lại, bổ sung ghi 1 dòng lịch sử "Nộp lại đề tài" | **Tích hợp tối thiểu không phá vỡ logic cũ** |
| **Dữ liệu mẫu (Seed/SQL)** | Có sẵn dữ liệu cơ bản | Cần seed tối thiểu 1 BM có TBM, >=3 GV, >=2 HK, đủ 4 trạng thái đề tài, có lịch sử yêu cầu sửa, 1 GV chưa có đề tài, 1 BM khác để test chặn chéo | **Bổ sung Seeder / SQL script** |

---
**Kết luận**: Kiến trúc phân hệ đã sẵn sàng. Tiến hành triển khai theo đúng 4 bước và chuẩn nghiệp vụ được giao.
