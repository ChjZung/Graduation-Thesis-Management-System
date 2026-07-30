# 🎓 HỆ THỐNG QUẢN LÝ ĐỒ ÁN & KHÓA LUẬN TỐT NGHỆP (HUIT)

> **Trường Đại Học Công Thương TP. Hồ Chí Minh (HUIT)**  
> **Khoa Công Nghệ Thông Tin**  
> **Học Phần:** Lập Trình Mã Nguồn Mở Với PHP  
> **Giảng Viên Hướng Dẫn:** ThS. Nguyễn Thanh Truyền  
> **Hội Đồng Đánh Giá:** Hội Đồng 1  
> **Mã Nguồn:** `C:\laragon\www\Student-Project-Management-System`  
> **Repository GitHub:** [https://github.com/ChjZung/Student-Project-Management-System.git](https://github.com/ChjZung/Student-Project-Management-System.git)

---

## 📌 CHUẨN ĐẦU RA HỌC PHẦN (CLO STANDARDS)

Hệ thống được thiết kế và đáp ứng đầy đủ các chuẩn đầu ra môn học của Khoa CNTT - HUIT:
- **`CLO1`**: Nắm vững kỹ năng thuyết trình, phân tích nghiệp vụ và trả lời vấn đáp kỹ thuật chuyên sâu về PHP & Laravel Framework.
- **`CLO2`**: Triển khai tài liệu hướng dẫn chuẩn (`README.md`), vận hành mượt mà trên môi trường Production và kiểm thử tự động với PHPUnit (`26/26 tests passed`).
- **`CLO3`**: Tuân thủ quy trình làm việc nhóm chuyên nghiệp trên GitHub Workflow (Commits, Push, Pull Requests) và viết code theo chuẩn mở **PSR-12**.

---

## 🌟 TÍNH NĂNG NỔI BẬT (3 PHÂN HỆ)

### 🛡️ 1. Phân Hệ Quản Trị Viên (Admin)
- **Dashboard Thống Kê:** Biểu đồ Doughnut Chart.js tổng quan số lượng Sinh viên, Giảng viên, Đề tài, Nhóm đồ án và tiến độ.
- **Quản Lý Danh Mục (CRUD):** Quản lý Sinh viên, Giảng viên, Bộ môn, Ngành học, Lớp học, Môn học, Học kỳ.
- **Phân Công Hướng Dẫn:** Phân công Giảng viên hướng dẫn đồ án theo Lớp / Môn học / Học kỳ.
- **Import Excel Thông Minh:** Tải tệp mẫu `.xlsx` và Import dữ liệu hàng loạt cho 8 danh mục với cơ chế bắt lỗi từng dòng và xuất file CSV error log nếu lỗi.
- **Quản Lý Sản Phẩm & Kết Quả:** Theo dõi sản phẩm nộp của các nhóm và bảng điểm tổng hợp.
- **Quản Lý Thông Báo:** Phát thông báo toàn hệ thống hoặc gửi đích danh người dùng.

### 👨‍🏫 2. Phân Hệ Giảng Viên (GV)
- **Quản Lý Đề Tài:** Thêm mới, chỉnh sửa, xóa đề tài, upload file đính kèm. Hỗ trợ **Import đề tài từ file Excel**.
- **Duyệt Đăng Ký Đồ Án:** Duyệt / Từ chối yêu cầu đăng ký đề tài của các Nhóm sinh viên.
- **Duyệt Báo Cáo Tiến Độ:** Theo dõi và xác nhận các đợt nộp báo cáo tiến độ theo tuần/tháng và ghi nhận xét.
- **Quản Lý Sản Phẩm:** Kiểm tra file sản phẩm đồ án, mã nguồn, báo cáo hoàn chỉnh do sinh viên nộp.
- **Chấm Điểm Đồ Án:** Nhập điểm, đánh giá và nhận xét trực tiếp cho từng sinh viên/nhóm.
- **Đăng Thông Báo:** Phát thông báo nhắc nhở đến các sinh viên do mình hướng dẫn.

### 👨‍🎓 3. Phân Hệ Sinh Viên (SV)
- **Quản Lý Nhóm Đồ Án:** Tạo nhóm mới, tìm kiếm và gửi lời mời thành viên qua Mã sinh viên, chấp nhận/từ chối lời mời.
- **Đăng Ký Đề Tài:** Tra cứu danh sách đề tài của Giảng viên phân công và gửi yêu cầu đăng ký theo nhóm.
- **Báo Cáo Tiến Độ:** Nộp báo cáo tiến độ định kỳ (kèm file tài liệu / link đính kèm).
- **Nộp Sản Phẩm Đồ Án:** Nộp file báo cáo chính thức, sản phẩm phần mềm, tài liệu khóa luận.
- **Nhận Thông Báo:** Hộp thư thông báo riêng với badge số lượng thông báo chưa đọc.

---

## 🎨 GIAO DIỆN HUIT SKY BLUE AESTHETIC

- **Bảng Màu Chủ Đạo:** HUIT Sky Blue (`#0072CE`, `#0066B2`, `#E5F0FA`, `#BDE0FE`).
- **Sidebar Gradient:** Thanh điều hướng sang trọng kết hợp Logo nhà trường HUIT và Badge hiển thị vai trò người dùng.
- **Trang Đăng Nhập:** Thiết kế Split-screen 2 cột hiện đại với hình ảnh cổng trường HUIT (`hinhcongtruong.jpg`) và khung thông tin căn giữa.
- **Footer Chuyên Nghiệp:** Component `partials/footer.blade.php` tích hợp thông tin liên hệ HUIT 140 Lê Trọng Tấn, icon mạng xã hội chính hãng và Google Maps định vị.

---

## 🌐 DANH SÁCH RESTFUL API ENDPOINTS (16 ENDPOINTS)

Hệ thống cung cấp **16 RESTful JSON API Endpoints** đáp ứng tiêu chí đánh giá API / JSON Endpoints (2.5 điểm):

| # | Method | Endpoint | Mô Tả |
|:-:|:------:|:---------|:------|
| 1 | `GET` | `/api/detais` | Lấy danh sách đề tài (Hỗ trợ `?search=` tìm kiếm & `?per_page=` phân trang) |
| 2 | `GET` | `/api/detais/{id}` | Lấy chi tiết 1 đề tài (Trả 404 nếu không tìm thấy) |
| 3 | `POST` | `/api/detais` | Tạo đề tài mới qua API (Có Validation 422) |
| 4 | `PUT` | `/api/detais/{id}` | Cập nhật đề tài (Trả 404 nếu không tìm thấy) |
| 5 | `DELETE` | `/api/detais/{id}` | Xóa đề tài (Trả 409 nếu có ràng buộc dữ liệu) |
| 6 | `GET` | `/api/nhoms` | Lấy danh sách tất cả các nhóm đồ án |
| 7 | `GET` | `/api/nhoms/{id}` | Lấy chi tiết 1 nhóm đồ án |
| 8 | `GET` | `/api/sinhviens` | Lấy danh sách sinh viên kèm thông tin Lớp |
| 9 | `GET` | `/api/giangviens` | Lấy danh sách giảng viên kèm thông tin Bộ môn |
| 10 | `GET` | `/api/monhocs` | Lấy danh sách môn học |
| 11 | `GET` | `/api/lops` | Lấy danh sách lớp kèm thông tin Ngành |
| 12 | `GET` | `/api/hockys` | Lấy danh sách học kỳ |
| 13 | `GET` | `/api/bomons` | Lấy danh sách bộ môn |
| 14 | `GET` | `/api/nganhs` | Lấy danh sách ngành |
| 15 | `GET` | `/api/thongbaos` | Lấy danh sách 50 thông báo mới nhất |
| 16 | `GET` | `/api/thongke` | Thống kê tổng quan hệ thống (Số SV, GV, Đề tài, Nhóm...) |

---

## 🛠️ CÔNG NGHỆ SỬ DỤNG

- **Backend Framework:** PHP 8.3+, Laravel 11.x / 10.x
- **Database:** MySQL 8.0+
- **Frontend:** Blade Template, Bootstrap 5, FontAwesome 6, Custom Vanilla CSS (`huit_theme.css`), Chart.js
- **Excel Engine:** `phpoffice/phpspreadsheet` (Xử lý nạp/xuất file `.xlsx` thông minh)
- **Testing:** PHPUnit 10.x (Viết test tự động Feature & Unit Test)

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN

### ⚡ CÁCH 1: CHẠY TỰ ĐỘNG 1-CLICK (Khuyên dùng)
Trong thư mục dự án `C:\laragon\www\Student-Project-Management-System`, bạn chỉ cần **click đúp chuột vào file [`CHAY_DU_AN.bat`](file:///C:/laragon/www/Student-Project-Management-System/CHAY_DU_AN.bat)**.
Script sẽ tự động nạp CSDL MySQL `quanly_doan`, seed dữ liệu mẫu, xóa view cache và bật ứng dụng tại **`http://localhost:8000`**.

---

### 🛠️ CÁCH 2: CÀI ĐẶT THỦ CÔNG (Step-by-step)

#### 1️⃣ Clone Repository
```bash
git clone https://github.com/ChjZung/Student-Project-Management-System.git
cd Student-Project-Management-System
```

#### 2️⃣ Cài Đặt Thư Viện Composer
```bash
composer install
```

#### 3️⃣ Cấu Hình File Môi Trường (`.env`)
Tạo file `.env` từ file mẫu `.env.example`:
```cmd
copy .env.example .env
php artisan key:generate
```

Kiểm tra cấu hình Cơ Sở Dữ Liệu MySQL trong `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quanly_doan
DB_USERNAME=root
DB_PASSWORD=
```

#### 4️⃣ Khởi Tạo CSDL & Seed Dữ Liệu Mẫu
```bash
php artisan migrate:fresh --seed
```

#### 5️⃣ Tạo Liên Kết Storage & Khởi Động Server
```bash
php artisan storage:link
php artisan serve
```
Truy cập hệ thống tại: **`http://127.0.0.1:8000`**

---

## 🧪 HƯỚNG DẪN CHẠY KIỂM THỬ TỰ ĐỘNG (PHPUNIT TEST)

Hệ thống đã có **26 Test Cases tự động** kiểm thử chức năng Đăng nhập, RESTful API và Middleware Bảo mật:

```bash
php artisan test
```

**Kết quả đầu ra:**
```text
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ApiTest
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\MiddlewareTest

Tests:    26 passed (84 assertions)
Duration: 1.79s
```

---

## 🔑 TÀI KHOẢN ĐĂNG NHẬP MẪU (Sau khi Seed)

| Phân Hệ | Tên Đăng Nhập | Mật Khẩu | Quyền Hạn |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `123456` | Quản trị toàn bộ hệ thống |
| **Giảng Viên** | `gv01` | `123456` | Giảng viên bộ môn |
| **Giảng Viên** | `gv02` | `123456` | Giảng viên bộ môn |
| **Sinh Viên** | `sv01` | `123456` | Sinh viên (Trưởng nhóm) |
| **Sinh Viên** | `sv02` | `123456` | Sinh viên |

---

## 📁 CẤU TRÚC THƯ MỤC DỰ ÁN

```text
Student-Project-Management-System/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Admin, GiangVien, SinhVien, ApiController
│   │   └── Middleware/      # CheckRole Middleware phân quyền
│   ├── Models/               # Eloquent Models (SinhVien, GiangVien, DeTai, NhomDoAn...)
│   └── Services/             # ExcelImportService, ExcelTemplateService, FileUploadService
├── database/
│   ├── migrations/           # Cấu trúc các bảng cơ sở dữ liệu (15+ migrations)
│   └── seeders/              # Dữ liệu mẫu hệ thống chuẩn
├── public/
│   ├── css/huit_theme.css    # Bộ stylesheet màu Xanh HUIT Sky Blue
│   └── images/               # Logo, Banner, Hình ảnh nhà trường HUIT
├── resources/
│   ├── views/                # Giao diện Blade (admin, giangvien, sinhvien, auth)
│   └── views/partials/       # Component Footer HUIT dùng chung
├── tests/
│   └── Feature/              # AuthTest, ApiTest, MiddlewareTest (26 PHPUnit tests)
├── CHAY_DU_AN.bat            # File khởi động 1-Click tự động
└── routes/
    └── web.php               # Cấu hình Web & API Routes
```

---

## 📄 GIẤY PHÉP & BẢN QUYỀN

- Dự án phát triển phục vụ học phần Lập trình mã nguồn mở với PHP.
- Bản quyền thiết kế & thương hiệu thuộc về **Trường Đại Học Công Thương TP. Hồ Chí Minh (HUIT)**.

---
⭐ *Nếu bạn thấy dự án hữu ích, hãy tặng repo 1 sao (Star) nhé!*
