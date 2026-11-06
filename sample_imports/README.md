# HƯỚNG DẪN DỮ LIỆU MẪU IMPORT EXCEL / CSV
### HỆ THỐNG QUẢN LÝ KHÓA LUẬN TỐT NGHIỆP - TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM (HUIT)

Thư mục này chứa toàn bộ các file mẫu chuẩn (`.xlsx` và `.csv` định dạng **UTF-8 có BOM** hiển thị tiếng Việt chuẩn 100% trên mọi phiên bản Microsoft Excel) dành cho hệ thống **Quản lý Đề tài & Khóa luận Tốt nghiệp**.

---

## 1. THỨ TỰ NHẬP LIỆU CHUẨN (IMPORT SEQUENCE)

Để đảm bảo tính toàn vẹn dữ liệu và các ràng buộc khóa ngoại (Foreign Keys) giữa các bảng trong hệ thống, vui lòng thực hiện import lần lượt theo đúng thứ tự đánh số từ **01** đến **12**:

| Thứ tự | Tên File Mẫu (.xlsx & .csv) | Bảng CSDL Tương Ứng | Ý Nghĩa / Mục Đích Nghiệp Vụ |
|:---:|---|---|---|
| **01** | `01_Khoa_Mau.xlsx` / `.csv` | `khoa` | Danh mục Khoa đào tạo (Khoa Công nghệ Thông tin) |
| **02** | `02_BoMon_Mau.xlsx` / `.csv` | `bomon` | Danh mục 6 Bộ môn trực thuộc Khoa (CNPM, HTTT, ATTT, KHMT, KHDL, MMT) |
| **03** | `03_Nganh_Mau.xlsx` / `.csv` | `nganh` | Danh mục 7 Ngành đào tạo trực thuộc Khoa |
| **04** | `04_Lop_Mau.xlsx` / `.csv` | `lop` | Danh mục Lớp sinh viên chính quy |
| **05** | `05_HocKy_Mau.xlsx` / `.csv` | `hocky` | Danh mục Học kỳ và Năm học (HK2425_1, HK2425_2, HK2526_1, HK2627_1...) |
| **06** | `06_GiangVien_Mau.xlsx` / `.csv` | `giangvien`, `taikhoan` | Danh sách Giảng viên gốc (tự động tạo Tài khoản giảng viên `TenDangNhap = MaGV`, mật khẩu ban đầu `Huit@2026`) |
| **07** | `07_SinhVien_Mau.xlsx` / `.csv` | `sinhvien`, `taikhoan` | Danh sách Sinh viên gốc (tự động tạo Tài khoản sinh viên `TenDangNhap = MSSV`, mật khẩu ban đầu `Huit@2026`) |
| **08** | `08_HocPhan_KhoaLuan_Mau.xlsx` / `.csv` | `hocphan` | Danh mục 12 Học phần Khóa luận cử nhân & KLTN theo 6 bộ môn |
| **09** | `09_HocPhan_HocKy_Mau.xlsx` / `.csv` | `hocphan_hocky` | Danh sách mở các học phần khóa luận áp dụng theo từng học kỳ |
| **10** | `10_GiangVien_TheoHocKy_Mau.xlsx` / `.csv` | `chitieuhuongdan` | Phân công Giảng viên tham gia hướng dẫn & Định mức chỉ tiêu số nhóm tối đa theo học kỳ |
| **11** | `11_SinhVien_TheoHocKy_Mau.xlsx` / `.csv` | `danhsachsvdudieukien` | Danh sách Sinh viên đủ điều kiện làm khóa luận theo từng học kỳ (xét tín chỉ và GPA) |
| **12** | `12_DeTai_KhoaLuan_Mau.xlsx` / `.csv` | `detai` | Danh sách Đề tài Khóa luận tốt nghiệp được đề xuất và công bố (chuẩn hóa từ 265 đề tài thực tế của ngành CNTT) |
| *Ref* | `12_DS_DangKy_DeTai_KLCN_Nganh_CNTT_MauGoc.xlsx` | *Tài liệu đối chiếu* | File phân công đề tài, giảng viên và nhóm sinh viên thực tế gốc của khoa CNTT |

---

## 2. QUY ĐỊNH CHI TIẾT TỪNG FILE VÀ CẤU TRÚC CỘT

### 01. Khoa (`01_Khoa_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaKhoa` | Có | Chuỗi | Mã Khoa duy nhất (`CNTT`). |
| `TenKhoa` | Có | Chuỗi | Tên đầy đủ của Khoa (`Khoa CNTT`). |
| `TruongKhoa` | Không | Chuỗi | Họ tên & học vị Trưởng khoa (`PGS.TS. Nguyễn Văn Hùng`). |
| `MoTa` | Không | Chuỗi | Giới thiệu hoặc thông tin liên hệ của văn phòng khoa. |

### 02. Bộ môn (`02_BoMon_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaBoMon` | Có | Chuỗi | Mã Bộ môn (`CNPM`, `HTTT`, `ATTT`, `KHMT`, `KHDL`, `MMT`). |
| `TenBoMon` | Có | Chuỗi | Tên Bộ môn (`Bộ môn CNPM`, `Bộ môn HTTT`...). |
| `MaKhoa` | Có | Chuỗi | Mã Khoa trực thuộc (`CNTT`). |
| `TruongBoMon` | Không | Chuỗi | Họ tên Trưởng bộ môn (`TS. Trần Anh Dũng`...). |
| `MoTa` | Không | Chuỗi | Chuyên môn, hướng nghiên cứu của bộ môn. |

### 03. Ngành (`03_Nganh_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaNganh` | Có | Chuỗi | Mã ngành theo Bộ GD&ĐT (`7480201`, `7480103`, `7460112`...). |
| `TenNganh` | Có | Chuỗi | Tên ngành đào tạo (`Công nghệ Thông tin`, `Kỹ thuật Phần mềm`...). |
| `MaKhoa` | Có | Chuỗi | Mã Khoa quản lý (`CNTT`). |

### 04. Lớp (`04_Lop_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaLop` | Có | Chuỗi | Mã lớp sinh hoạt (`12DHCNTT01`, `12DHCNTT02`, `14DHTH12`...). |
| `TenLop` | Có | Chuỗi | Tên lớp sinh hoạt. |
| `MaNganh` | Có | Chuỗi | Mã Ngành hoặc Tên Ngành lớp trực thuộc (`7480201`). |
| `KhoaHoc` | Không | Chuỗi | Khóa học (`2021-2025`, `2023-2027`). |
| `MaKhoa` | Không | Chuỗi | Mã Khoa (`CNTT`). |

### 05. Học kỳ (`05_HocKy_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaHocKy` | Có | Chuỗi | Mã Học kỳ (`HK2425_1`, `HK2526_1`, `HK2627_1`). |
| `TenHocKy` | Có | Chuỗi | Tên Học kỳ (`Học kỳ 1`, `Học kỳ 2`). |
| `NamHoc` | Có | Chuỗi | Năm học (`2024-2025`, `2026-2027`). |
| `NgayBatDau` | Không | Ngày | Ngày bắt đầu học kỳ (`YYYY-MM-DD`). |
| `NgayKetThuc`| Không | Ngày | Ngày kết thúc học kỳ (`YYYY-MM-DD`). |
| `TrangThai` | Không | Chuỗi | `Chưa bắt đầu`, `Đang diễn ra`, `Đã kết thúc`. |

### 06. Giảng viên gốc (`06_GiangVien_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaGV` | Có | Chuỗi | Mã số Giảng viên (`GV00000001`, `01001002`...). Duy nhất. |
| `HoTen` | Có | Chuỗi | Họ và tên đầy đủ của giảng viên. |
| `Email` | Có | Chuỗi | Email công vụ (`hungnv@huit.edu.vn`). |
| `SoDienThoai`| Không | Chuỗi | Số điện thoại di động 10 chữ số. |
| `NgaySinh` | Không | Ngày | Ngày sinh (`YYYY-MM-DD`). |
| `GioiTinh` | Không | Chuỗi | `Nam` hoặc `Nữ`. |
| `HocHam` | Không | Chuỗi | `Giáo sư`, `Phó Giáo sư` hoặc để trống. |
| `HocVi` | Có | Chuỗi | `Tiến sĩ`, `Thạc sĩ`, `Kỹ sư`, `Cử nhân`. |
| `MaBoMon` | Có | Chuỗi | Mã Bộ môn giảng viên công tác (`CNPM`, `HTTT`...). |
| `TrangThai` | Không | Chuỗi | `Đang công tác`, `Nghỉ phép`, `Đã nghỉ việc`. |
> **Tính năng tự động**: Hệ thống tự động tạo Tài khoản người dùng với `TenDangNhap = MaGV`, vai trò `VT02` (Giảng viên) và mật khẩu ban đầu là `Huit@2026`.

### 07. Sinh viên gốc (`07_SinhVien_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MSSV` | Có | Chuỗi | Mã số sinh viên (`2001210001`, `2001230046`...). Duy nhất. |
| `HoTen` | Có | Chuỗi | Họ và tên đầy đủ của sinh viên. |
| `Email` | Có | Chuỗi | Email trường cấp (`2001210001@st.huit.edu.vn`). |
| `SoDienThoai`| Không | Chuỗi | Số điện thoại di động. |
| `NgaySinh` | Không | Ngày | Ngày sinh (`YYYY-MM-DD`). |
| `GioiTinh` | Không | Chuỗi | `Nam` hoặc `Nữ`. |
| `MaLop` | Có | Chuỗi | Mã Lớp sinh hoạt của sinh viên (`12DHCNTT01`). |
| `KhoaHoc` | Không | Chuỗi | Khóa học (`2021-2025`). |
| `SoTinChiTichLuy`| Không | Số | Số tín chỉ đã tích lũy (VD: `128`). |
| `DiemTichLuy`| Không | Số | Điểm trung bình tích lũy hệ 4 (VD: `3.45`). |
| `TrangThai` | Không | Chuỗi | `Đang học`, `Tạm dừng`, `Bảo lưu`. |
> **Tính năng tự động**: Hệ thống tự động tạo Tài khoản sinh viên với `TenDangNhap = MSSV`, vai trò `VT03` (Sinh viên) và mật khẩu ban đầu là `Huit@2026`.

### 08. Học phần Khóa luận (`08_HocPhan_KhoaLuan_Mau`)
Bao gồm 12 học phần khóa luận chuẩn hóa của 6 bộ môn thuộc khoa CNTT:
- Công nghệ phần mềm: `HP_KLCN_CNPM`, `HP_KLTN_CNPM`
- Hệ thống thông tin: `HP_KLCN_HTTT`, `HP_KLTN_HTTT`
- An toàn thông tin: `HP_KLCN_ATTT`, `HP_KLTN_ATTT`
- Khoa học máy tính: `HP_KLCN_KHMT`, `HP_KLTN_KHMT`
- Khoa học dữ liệu & AI: `HP_KLCN_KHDL`, `HP_KLTN_KHDL`
- Mạng máy tính & TT: `HP_KLCN_MMT`, `HP_KLTN_MMT`

| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaHocPhan` | Có | Chuỗi | Mã học phần (`HP_KLCN_CNPM`). |
| `TenHocPhan` | Có | Chuỗi | Tên học phần (`Khóa luận cử nhân - Công nghệ phần mềm`). |
| `SoTinChi` | Có | Số | Số tín chỉ (`10`). |
| `MaKhoa` | Có | Chuỗi | Mã Khoa chủ quản (`CNTT`). |
| `MaBoMon` | Có | Chuỗi | Mã Bộ môn chuyên môn (`CNPM`). |
| `LoaiHocPhan`| Không | Chuỗi | `Khóa luận` hoặc `Chuyên ngành`. |
| `MoTa` | Không | Chuỗi | Mục tiêu và nội dung chuẩn đầu ra của học phần. |

### 09. Mở Học phần theo Học kỳ (`09_HocPhan_HocKy_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaHocPhan` | Có | Chuỗi | Mã học phần khóa luận đã có ở bước 08 (`HP_KLCN_CNPM`). |
| `TenHocPhan` | Có | Chuỗi | Tên học phần hiển thị trực quan. |
| `MaHocKy` | Có | Chuỗi | Mã học kỳ mở học phần (`HK2627_1`). |
| `TrangThai` | Có | Chuỗi | Trạng thái mở (`Đang mở`, `Đã đóng`). |
| `GhiChu` | Không | Chuỗi | Ghi chú kế hoạch đào tạo. |

### 10. Giảng viên theo Học kỳ (`10_GiangVien_TheoHocKy_Mau`)
*(Bổ sung theo yêu cầu nghiệp vụ phân công & chỉ tiêu hướng dẫn)*
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaGV` | Có | Chuỗi | Mã giảng viên đã có ở bước 06 (`GV00000002`). |
| `HoTen` | Có | Chuỗi | Họ tên giảng viên (`TS. Trần Anh Dũng`). |
| `MaBoMon` | Có | Chuỗi | Bộ môn công tác (`CNPM`). |
| `MaHocKy` | Có | Chuỗi | Học kỳ áp dụng chỉ tiêu (`HK2627_1`). |
| `SoNhomToiDa`| Có | Số | Số nhóm tối đa được phép hướng dẫn (mặc định: `5` nhóm). |
| `NgayPhanBo` | Không | Ngày | Ngày phân bổ chỉ tiêu (`YYYY-MM-DD`). |
| `GhiChu` | Không | Chuỗi | Ghi chú quyết định phân công của khoa. |

### 11. Sinh viên đủ điều kiện theo Học kỳ (`11_SinhVien_TheoHocKy_Mau`)
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MSSV` | Có | Chuỗi | Mã số sinh viên đã có ở bước 07 (`2001210001`). |
| `HoTen` | Có | Chuỗi | Họ tên sinh viên (`Nguyễn Hoàng Long`). |
| `MaLop` | Có | Chuỗi | Lớp sinh hoạt (`12DHCNTT01`). |
| `MaHocKy` | Có | Chuỗi | Học kỳ xét duyệt điều kiện (`HK2627_1`). |
| `SoTinChiTichLuy`| Không | Số | Số tín chỉ tích lũy tại thời điểm xét (Chuẩn: >= 115). |
| `DiemTichLuy`| Không | Số | Điểm GPA tích lũy hệ 4 (Chuẩn: >= 2.0). |
| `TrangThai` | Có | Chuỗi | `Đủ điều kiện` hoặc `Chưa đủ điều kiện`. |
| `GhiChu` | Không | Chuỗi | Lý do hoặc ghi chú xét duyệt công bố. |

### 12. Đề tài Khóa luận tốt nghiệp (`12_DeTai_KhoaLuan_Mau`)
*(Được trích xuất và chuẩn hóa trực tiếp từ 265 đề tài thực tế của khoa CNTT HUIT)*
| Cột | Bắt buộc | Kiểu | Mô tả & Ví dụ |
|---|:---:|---|---|
| `MaDeTai` | Có | Chuỗi | Mã đề tài (`CNTT-KLCN055`, `CNTT-KLCN051`...). |
| `TenDeTai` | Có | Chuỗi | Tên đề tài khóa luận (Tối thiểu 10 ký tự). |
| `MoTa` | Có | Chuỗi | Mô tả bài toán nghiên cứu, kiến trúc và phạm vi thực hiện. |
| `YeuCau` | Có | Chuỗi | Yêu cầu năng lực công nghệ và kiến thức đối với sinh viên. |
| `LinhVuc` | Có | Chuỗi | Lĩnh vực chuyên môn (`Công nghệ phần mềm`, `Hệ thống thông tin`, `Khoa học dữ liệu & Trí tuệ nhân tạo`, `An toàn thông tin`). |
| `MaHocPhan` | Có | Chuỗi | Mã học phần khóa luận tương ứng (`HP_KLCN_CNPM`, `HP_KLCN_HTTT`...). |
| `TenHocPhan` | Có | Chuỗi | Tên học phần khóa luận. |
| `MaGV` | Có | Chuỗi | Mã giảng viên hướng dẫn (`01001002`, `01001003`...). |
| `TenGV` | Có | Chuỗi | Họ và tên giảng viên hướng dẫn (`Vũ Văn Vinh`...). |
| `MaHocKy` | Có | Chuỗi | Mã học kỳ áp dụng (`HK2627_1`). |
| `SoLuongSinhVienToiDa`| Có | Số | Số lượng sinh viên tối đa một nhóm (Mặc định: `3` sinh viên/đề tài). |
| `TrangThai` | Có | Chuỗi | Trạng thái đề tài: `Đã công bố` (hoặc `Chờ duyệt cấp Bộ môn`, `Chờ duyệt cấp Khoa`). |

---

## 3. LƯU Ý KHI SỬ DỤNG VÀ THỰC HIỆN IMPORT

1. **Mật khẩu khởi tạo**: Tất cả tài khoản giảng viên và sinh viên khi import mới đều được gán mật khẩu mặc định là **`Huit@2026`** (tài khoản Admin là **`Admin@123`**). Người dùng sẽ được yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên.
2. **Định dạng file**: Hệ thống hỗ trợ cả file `.xlsx` và `.csv`. Khi mở file `.csv` bằng Excel trên Windows, toàn bộ chữ tiếng Việt có dấu đều hiển thị chuẩn xác nhờ mã hóa UTF-8 BOM.
3. **Tính toàn vẹn**: Khi import từ đầu cho một học kỳ mới, luôn chạy theo đúng thứ tự từ **01 đến 12** để tránh lỗi thiếu dữ liệu tham chiếu (foreign key mismatch).
