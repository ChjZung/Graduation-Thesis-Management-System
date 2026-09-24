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
| `MaBoMon` | **Có** | Chuỗi | Mã Bộ Môn (VD: `BMANH`) hoặc Tên Bộ Môn trực thuộc. |
| `TrangThai` | Không | Chuỗi | Trạng thái công tác: `Đang công tác`, `Nghỉ phép`, `Đã nghỉ việc`. |
> **Tính năng tự động**: Khi import Giảng viên, hệ thống tự động tạo Tài khoản (`TaiKhoan`) với tên đăng nhập bằng `MaGV` (chữ thường) và mật khẩu mặc định `123456`.

### 7. Sinh viên (`07_SinhVien_Mau`)
| Cột | Bắt buộc | Kiểu dữ liệu | Ý nghĩa & Ghi chú |
|---|:---:|---|---|
| `MSSV` | **Có** | Chuỗi | Mã số sinh viên (VD: `2001240001`). Duy nhất trong hệ thống. |
| `HoTen` | **Có** | Chuỗi | Họ và tên đầy đủ của sinh viên. |
| `Email` | Không | Chuỗi | Email trường cấp (VD: `2001240001@st.huit.edu.vn`). Duy nhất. |
| `SoDienThoai`| Không | Chuỗi | Số điện thoại 10 số (VD: `0971234567`). Duy nhất. |
| `NgaySinh` | Không | Ngày | Ngày sinh (`YYYY-MM-DD` hoặc `DD/MM/YYYY`). |
| `GioiTinh` | Không | Chuỗi | `Nam` hoặc `Nữ`. |
| `MaLop` | **Có** | Chuỗi | Mã Lớp hoặc Tên Lớp sinh viên đang theo học. |
| `KhoaHoc` | Không | Chuỗi | Niên khóa (VD: `2024-2028`). Tự lấy theo Lớp nếu để trống. |
| `SoTinChiTichLuy`| Không | Số | Số tín chỉ đã tích lũy (VD: `95`). |
| `DiemTichLuy`| Không | Số | Điểm trung bình tích lũy hệ 4 (VD: `3.25`). |
| `TrangThai` | Không | Chuỗi | Trạng thái: `Đang học`, `Tạm dừng`, `Bảo lưu`, `Đã tốt nghiệp`. |
> **Tính năng tự động**: Khi import Sinh viên, hệ thống tự động suy ra `MaKhoa` và `MaNganh` từ Lớp học, đồng thời tạo Tài khoản sinh viên với `TenDangNhap = MSSV`, mật khẩu mặc định `123456`.
