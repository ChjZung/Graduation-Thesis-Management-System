# HƯỚNG DẪN DỮ LIỆU MẪU IMPORT EXCEL / CSV

Thư mục này chứa toàn bộ các file mẫu chuẩn (`.xlsx` và `.csv` định dạng UTF-8 có BOM hiển thị tiếng Việt chuẩn trên Excel) dành cho hệ thống **Quản lý Đề tài & Khóa luận Tốt nghiệp**.

---

## 1. THỨ TỰ NHẬP DỮ LIỆU KHUYẾN NGHỊ

Để đảm bảo tính toàn vẹn dữ liệu (khóa ngoại liên kết giữa các bảng), vui lòng thực hiện import theo thứ tự số thứ tự đã được đánh số trên từng file:

1. **`01_Khoa_Mau.xlsx` / `01_Khoa_Mau.csv`**: Danh mục Khoa
2. **`02_BoMon_Mau.xlsx` / `02_BoMon_Mau.csv`**: Danh mục Bộ Môn (trực thuộc Khoa)
3. **`03_Nganh_Mau.xlsx` / `03_Nganh_Mau.csv`**: Danh mục Ngành đào tạo (trực thuộc Khoa)
4. **`04_Lop_Mau.xlsx` / `04_Lop_Mau.csv`**: Danh mục Lớp sinh viên (trực thuộc Ngành & Khoa)
5. **`05_HocKy_Mau.xlsx` / `05_HocKy_Mau.csv`**: Danh mục Học kỳ / Năm học
6. **`06_GiangVien_Mau.xlsx` / `06_GiangVien_Mau.csv`**: Danh sách Giảng viên (trực thuộc Bộ Môn, tự tạo Tài khoản)
7. **`07_SinhVien_Mau.xlsx` / `07_SinhVien_Mau.csv`**: Danh sách Sinh viên (trực thuộc Lớp, tự liên kết Khoa/Ngành, tự tạo Tài khoản)
8. **`09_SinhVien_TheoHocKy_Mau.xlsx` / `09_SinhVien_TheoHocKy_Mau.csv`**: Danh sách Sinh viên đủ điều kiện làm khóa luận theo học kỳ
9. **`10_HocPhan_KhoaLuan_Mau.xlsx` / `10_HocPhan_KhoaLuan_Mau.csv`**: Danh mục Học phần Khóa luận cử nhân & Khóa luận tốt nghiệp (12 học phần cho 6 bộ môn)
10. **`11_DeTai_KhoaLuan_Mau.xlsx` / `11_DeTai_KhoaLuan_Mau.csv`** (hoặc `DanhSach_DeTai_Mau.xlsx` / `.csv`): Danh sách Đề tài Khóa luận tốt nghiệp được đề xuất và công bố (chuẩn hóa theo 12 học phần của 6 bộ môn)

---

## 2. CHI TIẾT CÁC CỘT VÀ QUY TẮC NHẬP LIỆU

### 1. Khoa (`01_Khoa_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaKhoa` | Tùy chọn | Chuỗi | Mã Khoa (VD: `NN`, `QTKD`). Nếu để trống, hệ thống tự sinh mã `K01`, `K02`... |
| `TenKhoa` | **Có** | Chuỗi | Tên Khoa (VD: `Khoa Ngoại Ngữ`). Duy nhất, không được trùng lặp. |
| `TruongKhoa` | Không | Chuỗi | Họ tên/Học vị Trưởng khoa (VD: `TS. Lê Thị Mai`). |
| `MoTa` | Không | Chuỗi | Giới thiệu hoặc mô tả khoa. |

### 2. Bộ môn (`02_BoMon_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaBoMon` | Tùy chọn | Chuỗi | Mã Bộ Môn (VD: `BMANH`). Nếu để trống, hệ thống tự sinh mã `BM01`, `BM02`... |
| `TenBoMon` | **Có** | Chuỗi | Tên Bộ Môn (VD: `Bộ Môn Tiếng Anh`). Duy nhất trong hệ thống. |
| `MaKhoa` | **Có** | Chuỗi | Mã Khoa hoặc Tên Khoa quản lý bộ môn này. |
| `TruongBoMon` | Không | Chuỗi | Họ tên Trưởng bộ môn. |
| `MoTa` | Không | Chuỗi | Mô tả nhiệm vụ hoặc chuyên môn bộ môn. |

### 3. Ngành (`03_Nganh_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaNganh` | Tùy chọn | Chuỗi | Mã Ngành theo Bộ GD&ĐT (VD: `7220201`). Nếu để trống hệ thống tự sinh `NG01`, `NG02`... |
| `TenNganh` | **Có** | Chuỗi | Tên Ngành đào tạo (VD: `Ngôn Ngữ Anh`). |
| `MaKhoa` | **Có** | Chuỗi | Mã Khoa hoặc Tên Khoa chủ quản ngành này. |

### 4. Lớp (`04_Lop_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaLop` | Tùy chọn | Chuỗi | Mã Lớp (VD: `15DHNNA01`). Để trống sẽ lấy tự động bằng `TenLop`. |
| `TenLop` | **Có** | Chuỗi | Tên lớp sinh hoạt (VD: `15DHNNA01`). Duy nhất. |
| `MaNganh` | **Có** | Chuỗi | Mã Ngành hoặc Tên Ngành mà lớp trực thuộc. |
| `KhoaHoc` | Không | Chuỗi | Khóa học theo niên giám (VD: `2024-2028`). Mặc định `2023-2027`. |
| `MaKhoa` | Không | Chuỗi | Mã Khoa. Nếu để trống hệ thống tự lấy Khoa của Ngành tương ứng. |

### 5. Học kỳ (`05_HocKy_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaHocKy` | Tùy chọn | Chuỗi | Mã Học kỳ (VD: `HK2425_1`). Nếu để trống hệ thống tự sinh `HK01`, `HK02`... |
| `TenHocKy` | **Có** | Chuỗi | Tên Học kỳ (VD: `Học kỳ 1 (2024-2025)`). |
| `NamHoc` | **Có** | Chuỗi | Năm học (VD: `2024-2025`). Cặp `(TenHocKy, NamHoc)` là duy nhất. |
| `NgayBatDau` | Không | Ngày | Ngày bắt đầu học kỳ (Định dạng `YYYY-MM-DD` hoặc `DD/MM/YYYY`). |
| `NgayKetThuc`| Không | Ngày | Ngày kết thúc học kỳ. |
| `TrangThai` | Không | Chuỗi | Trạng thái: `Chưa bắt đầu`, `Đang diễn ra`, `Đã kết thúc`. |

### 6. Giảng viên (`06_GiangVien_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaGV` | **Có** | Chuỗi | Mã số Giảng viên (VD: `GV91`). Duy nhất trong hệ thống. |
| `HoTen` | **Có** | Chuỗi | Họ và tên đầy đủ của Giảng viên. |
| `Email` | Không | Chuỗi | Địa chỉ Email trường cấp (VD: `gv91_test@huit.edu.vn`). Duy nhất. |
| `SoDienThoai`| Không | Chuỗi | Số điện thoại 10 chữ số (VD: `0981112233`). Duy nhất. |
| `NgaySinh` | Không | Ngày | Ngày sinh (`YYYY-MM-DD` hoặc `DD/MM/YYYY`). |
| `GioiTinh` | Không | Chuỗi | `Nam` hoặc `Nữ`. |
| `HocHam` | Không | Chuỗi | Học hàm (VD: `Giáo sư`, `Phó Giáo sư` hoặc để trống). |
| `HocVi` | Không | Chuỗi | Học vị cao nhất (VD: `Tiến sĩ`, `Thạc sĩ`, `Cử nhân`). |
| `MaBoMon` | **Có** | Chuỗi | Mã Bộ Môn (VD: `CNPM`, `HTTT`, `ATTT`, `MMT`, `KHMT`, `KHDL`) hoặc Tên Bộ Môn trực thuộc. |
| `TrangThai` | Không | Chuỗi | Trạng thái công tác: `Đang công tác`, `Nghỉ phép`, `Đã nghỉ việc`. |
> **Tính năng tự động**: Khi import Giảng viên, hệ thống tự động tạo Tài khoản (`TaiKhoan`) với tên đăng nhập bằng `MaGV` (chữ thường) và mật khẩu tạm thời an toàn ngẫu nhiên (hoặc `Huit@2026`), yêu cầu bắt buộc đổi mật khẩu khi đăng nhập lần đầu.

### 7. Sinh viên (`07_SinhVien_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MSSV` | **Có** | Chuỗi | Mã số sinh viên (VD: `2001210001`). Duy nhất trong hệ thống. |
| `HoTen` | **Có** | Chuỗi | Họ và tên đầy đủ của sinh viên. |
| `Email` | Không | Chuỗi | Email trường cấp (VD: `2001210001@st.huit.edu.vn`). Duy nhất. |
| `SoDienThoai`| Không | Chuỗi | Số điện thoại 10 số (VD: `0978001001`). Duy nhất. |
| `NgaySinh` | Không | Ngày | Ngày sinh (`YYYY-MM-DD` hoặc `DD/MM/YYYY`). |
| `GioiTinh` | Không | Chuỗi | `Nam` hoặc `Nữ`. |
| `MaLop` | **Có** | Chuỗi | Mã Lớp hoặc Tên Lớp sinh viên đang theo học (VD: `12DHCNTT01`). |
| `KhoaHoc` | Không | Chuỗi | Niên khóa (VD: `2021-2025`). Tự lấy theo Lớp nếu để trống. |
| `SoTinChiTichLuy`| Không | Số | Số tín chỉ đã tích lũy (VD: `128`). |
| `DiemTichLuy`| Không | Số | Điểm trung bình tích lũy hệ 4 (VD: `3.45`). |
| `TrangThai` | Không | Chuỗi | Trạng thái: `Đang học`, `Tạm dừng`, `Bảo lưu`, `Đã tốt nghiệp`. |
> **Tính năng tự động**: Khi import Sinh viên, hệ thống tự động suy ra `MaKhoa` và `MaNganh` từ Lớp học, đồng thời tạo Tài khoản sinh viên với `TenDangNhap = MSSV`, mật khẩu tạm thời an toàn (yêu cầu đổi mật khẩu lần đầu).

### 8. Quy tắc Quản lý Tài khoản & Phân quyền
> **Quy tắc hệ thống**: Hệ thống **không cần file import riêng cho tài khoản**. 
> - **Sinh viên**: Khi import Sinh viên (`07_SinhVien_Mau`), hệ thống tự động khởi tạo 1 tài khoản duy nhất với `TenDangNhap = MSSV` (vai trò `VT03`).
> - **Giảng viên**: Khi import Giảng viên (`06_GiangVien_Mau`), hệ thống tự động khởi tạo tài khoản Giảng viên với `TenDangNhap = MaGV` (vai trò `VT02`).
> - **Trưởng Khoa & Trưởng Bộ Môn**: Từ hồ sơ giảng viên, hệ thống cấp thêm tài khoản chức vụ riêng biệt để thực hiện quyền phê duyệt:
>   - **Trưởng khoa**: `TK_{MaKhoa}_{MaGV}` (vai trò `VT05`).
>   - **Trưởng bộ môn**: `TBM_{MaKhoa}_{MaBoMon}_{MaGV}` (vai trò `VT04`).
>   - Cả tài khoản giảng viên và tài khoản chức vụ đều liên kết trực tiếp vào 1 hồ sơ giảng viên duy nhất (`MaGV`), giúp đồng bộ 100% dữ liệu họ tên, email, khoa, bộ môn trên toàn hệ thống.
> - **Mật khẩu khởi tạo chuẩn**: Mặc định là `Huit@2026`, yêu cầu bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên.

### 9. Sinh viên đủ điều kiện theo học kỳ (`09_SinhVien_TheoHocKy_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MSSV` | **Có** | Chuỗi | Mã số sinh viên đã có trong danh sách sinh viên. |
| `HoTen` | **Có** | Chuỗi | Họ và tên sinh viên. |
| `TrangThai` | Tùy chọn | Chuỗi | `Đủ điều kiện` hoặc `Chưa đủ điều kiện`. |
| `GhiChu` | Tùy chọn | Chuỗi | Ghi chú lý do xét duyệt (VD: `Đủ điều kiện làm khóa luận tốt nghiệp`). |

### 10. Học phần Khóa luận cử nhân & Khóa luận tốt nghiệp (`10_HocPhan_KhoaLuan_Mau`)
Danh mục gồm **12 học phần khóa luận chuẩn hóa theo 6 bộ môn** (mỗi bộ môn gồm 2 hệ: *Cử nhân* và *Tốt nghiệp*):
- **Công nghệ phần mềm**: `HP_KLCN_CNPM` (Khóa luận cử nhân), `HP_KLTN_CNPM` (Khóa luận tốt nghiệp)
- **Hệ thống thông tin**: `HP_KLCN_HTTT` (Khóa luận cử nhân), `HP_KLTN_HTTT` (Khóa luận tốt nghiệp)
- **An toàn thông tin**: `HP_KLCN_ATTT` (Khóa luận cử nhân), `HP_KLTN_ATTT` (Khóa luận tốt nghiệp)
- **Khoa học máy tính**: `HP_KLCN_KHMT` (Khóa luận cử nhân), `HP_KLTN_KHMT` (Khóa luận tốt nghiệp)
- **Khoa học dữ liệu & AI**: `HP_KLCN_KHDL` (Khóa luận cử nhân), `HP_KLTN_KHDL` (Khóa luận tốt nghiệp)
- **Mạng máy tính & Viễn thông**: `HP_KLCN_MMT` (Khóa luận cử nhân), `HP_KLTN_MMT` (Khóa luận tốt nghiệp)

| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaHocPhan` | Tùy chọn | Chuỗi | Mã học phần (VD: `HP_KLCN_CNPM`, `HP_KLTN_CNPM`...). Tự sinh nếu để trống. |
| `TenHocPhan` | **Có** | Chuỗi | Tên học phần (VD: `Khóa luận cử nhân - Công nghệ phần mềm`). |
| `SoTinChi` | **Có** | Số | Số tín chỉ của học phần (mặc định `10` tín chỉ cho Khóa luận). |
| `MaKhoa` | **Có** | Chuỗi | Mã Khoa chủ quản (`CNTT`). |
| `MaBoMon` | **Có** | Chuỗi | Mã Bộ Môn quản lý (`CNPM`, `HTTT`, `ATTT`, `MMT`, `KHMT`, `KHDL`). |
| `LoaiHocPhan`| Không | Chuỗi | Loại học phần: `Khóa luận` hoặc `Chuyên ngành`. |
| `MoTa` | Không | Chuỗi | Mô tả nội dung học phần khóa luận hoặc mục tiêu đầu ra. |

### 11. Đề tài Khóa luận tốt nghiệp (`11_DeTai_KhoaLuan_Mau` / `DanhSach_DeTai_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MaDeTai` | Tùy chọn | Chuỗi | Mã đề tài (VD: `DT01`, `DT02`). Nếu để trống, hệ thống tự động sinh `DT01`, `DT02`... |
| `TenDeTai` | **Có** | Chuỗi | Tên đề tài khóa luận (tối thiểu 10 ký tự, không trùng lặp). |
| `MoTa` | Tùy chọn | Chuỗi | Mô tả mục tiêu, bài toán và phạm vi nghiên cứu (tối thiểu 30 ký tự). |
| `YeuCau` | Tùy chọn | Chuỗi | Yêu cầu năng lực đối với sinh viên thực hiện (tối thiểu 15 ký tự). |
| `LinhVuc` | Tùy chọn | Chuỗi | Lĩnh vực chuyên môn (VD: `Công nghệ phần mềm`, `Hệ thống thông tin`, `An toàn thông tin`, `Khoa học máy tính`, `Khoa học dữ liệu`, `Mạng máy tính`). |
| `MaHocPhan` | Tùy chọn | Chuỗi | Mã học phần thuộc bộ môn (VD: `HP_KLCN_CNPM`, `HP_KLTN_CNPM`, `HP_KLCN_HTTT`, `HP_KLTN_HTTT`...). Tự động nhận diện theo Bộ môn nếu để trống. |
| `TenHocPhan` | Tùy chọn | Chuỗi | Tên học phần khóa luận tương ứng (hiển thị trực quan trên Excel). |
| `MaGV` | **Có** | Chuỗi | Mã Giảng viên hướng dẫn (VD: `GV00000001`, `GV00000002`...). Hoặc có thể nhập Họ tên giảng viên tại cột `TenGV`. |
| `TenGV` | Tùy chọn | Chuỗi | Họ và tên giảng viên hướng dẫn (hiển thị trực quan cho người dùng đọc). |
| `MaHocKy` | Tùy chọn | Chuỗi | Mã học kỳ áp dụng (VD: `HK2627_1`). Mặc định học kỳ đang diễn ra nếu để trống. |
| `SoLuongSinhVienToiDa` | Tùy chọn | Số | Số sinh viên tối đa trong một nhóm thực hiện đề tài (Mặc định: `3` theo quy chế đào tạo). |
| `TrangThai` | Tùy chọn | Chuỗi | Trạng thái đề tài: `Đã công bố`, `Đã duyệt`, `Chờ duyệt cấp Bộ môn`, `Chờ duyệt cấp Khoa`. Mặc định `Đã công bố`. |
> **Tính năng tự động**: Khi import danh sách đề tài, hệ thống tự động xác thực sự tồn tại của giảng viên, phân giải học phần theo bộ môn của giảng viên, sinh mã đề tài duy nhất `DTxx` (hỗ trợ chế độ Upsert ghi đè an toàn khi cập nhật) và ghi nhận lịch sử phê duyệt (`ChiTietDuyetDeTai`) đầy đủ.
