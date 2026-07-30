# 🎓 Cổng Thông Tin Quản Lý Đồ ÁN HUIT (APMS)

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![Testing](https://img.shields.io/badge/Tests-26%20Passed-success?style=for-the-badge)

Hệ thống Quản lý Đồ án và Khóa luận Tốt nghiệp toàn diện, có khả năng mở rộng và bảo mật cao. Được thiết kế để số hóa toàn bộ quy trình làm việc giữa Ban Quản trị nhà trường, Giảng viên và Sinh viên. Xây dựng trên nền tảng PHP hiện đại (Laravel 11) và tuân thủ chặt chẽ tiêu chuẩn RESTful API.

---

## 🌟 Tính Năng Nổi Bật & Phân Hệ

### 🛡️ 1. Quản Trị Viên (System Management)
- **Dashboard Tương Tác:** Trực quan hóa dữ liệu thời gian thực qua Chart.js (theo dõi số lượng đồ án đang thực hiện, sinh viên đăng ký, và thống kê giảng viên).
- **Quản Lý Dữ Liệu Cốt Lõi (CRUD):** Quản lý tập trung dữ liệu Sinh viên, Giảng viên, Khoa, Ngành học, và Học kỳ.
- **Nạp/Xuất Dữ Liệu Thông Minh:** Tích hợp engine `phpoffice/phpspreadsheet` để nạp hàng loạt 8 loại dữ liệu qua tệp Excel (`.xlsx`). Cơ chế bắt lỗi từng dòng thông minh và tự động xuất file log CSV nếu xảy ra lỗi nạp liệu.
- **Kiểm Soát Truy Cập Theo Vai Trò (RBAC):** Triển khai Middleware nghiêm ngặt để bảo vệ các endpoints và luồng xử lý dữ liệu nhạy cảm.

### 👨‍🏫 2. Giảng Viên (Faculty Workflow)
- **Quản Lý Đề Tài:** Đề xuất, chỉnh sửa và công bố đề tài hướng dẫn. Hỗ trợ nạp hàng loạt đề tài từ file Excel.
- **Phê Duyệt Đăng Ký:** Quy trình xét duyệt / từ chối yêu cầu đăng ký đề tài từ các nhóm sinh viên.
- **Theo Dõi Tiến Độ:** Theo dõi báo cáo tiến độ định kỳ (tuần/tháng), xác nhận và đính kèm phản hồi nhận xét.
- **Chấm Điểm & Đánh Giá:** Giao diện bảo mật để nhập điểm số cuối cùng và nhận xét chi tiết cho từng đồ án.

### 👨‍🎓 3. Sinh Viên (Project Execution)
- **Cộng Tác Nhóm:** Thành lập nhóm đồ án, tìm kiếm thành viên qua Mã số Sinh viên (MSSV) và quản lý lời mời vào nhóm.
- **Đăng Ký Đề Tài:** Tra cứu kho đề tài được công bố và gửi yêu cầu đăng ký theo nhóm.
- **Nộp Báo Cáo & Sản Phẩm:** Nộp báo cáo tiến độ, mã nguồn dự án, và tài liệu khóa luận trực tuyến.
- **Thông Báo Thời Gian Thực (Real-time):** Trung tâm thông báo với biểu tượng (badge) số lượng chưa đọc, cập nhật lập tức nhận xét từ giảng viên và thông báo từ nhà trường.

---

## 🌐 Tích Hợp RESTful API

Hệ thống cung cấp **16 Endpoints RESTful JSON API** phục vụ cho việc tích hợp đa nền tảng và tách biệt kiến trúc Frontend - Backend trong tương lai.

| HTTP Method | Endpoint | Mô Tả |
|:------:|:---------|:------|
| `GET` | `/api/detais` | Lấy danh sách đề tài (Hỗ trợ `?search=` và phân trang `?per_page=`) |
| `POST` | `/api/detais` | Tạo đề tài mới (Tích hợp Validation lỗi 422) |
| `PUT` | `/api/detais/{id}` | Cập nhật metadata của đề tài (Trả về lỗi 404 nếu không tìm thấy) |
| `DELETE` | `/api/detais/{id}` | Xóa mềm đề tài (Trả về lỗi 409 nếu vướng ràng buộc dữ liệu) |
| `GET` | `/api/thongke` | Lấy số liệu thống kê tổng quan toàn hệ thống |

*(Bao gồm 11 endpoints khác để quản lý Nhóm, Người dùng, Bộ môn và Thông báo).*

---

## 🛠️ Kiến Trúc & Công Nghệ

- **Kiến Trúc Backend:** Mô hình MVC, Service Repository Pattern (ExcelImportService, FileUploadService).
- **Framework:** PHP 8.3+, Laravel 11.x
- **Cơ Sở Dữ Liệu:** MySQL 8.0+ (Tối ưu hóa Index và Ràng buộc khóa ngoại).
- **Giao Diện UI/UX:** Blade Template Engine, Bootstrap 5, FontAwesome 6, Giao diện tùy chỉnh "HUIT Sky Blue Aesthetic".
- **Kiểm Thử (Testing):** PHPUnit 10.x (Kiểm thử tự động Feature & Unit).

---

## 🚀 Hướng Dẫn Cài Đặt

### Yêu cầu hệ thống
- PHP >= 8.2
- Composer
- Máy chủ MySQL (XAMPP/Laragon hoặc Docker)

### Các bước triển khai

1. **Clone mã nguồn (Repository):**
   ```bash
   git clone https://github.com/HoangThang25/HUIT-Thesis-Management-Portal.git
   cd HUIT-Thesis-Management-Portal
   ```

2. **Cài đặt thư viện Composer:**
   ```bash
   composer install
   ```

3. **Cấu hình môi trường:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Cập nhật thông tin kết nối Cơ sở dữ liệu MySQL của bạn trong file `.env`.*

4. **Tạo cấu trúc bảng & Dữ liệu mẫu (Migration & Seeding):**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Tạo liên kết lưu trữ (Storage Link):**
   ```bash
   php artisan storage:link
   ```

6. **Khởi động máy chủ phát triển:**
   ```bash
   php artisan serve
   ```
   Truy cập ứng dụng tại: `http://127.0.0.1:8000`.

---

## 🧪 Hệ Thống Kiểm Thử Tự Động (Automated Testing)

Dự án tích hợp sẵn bộ kiểm thử tự động toàn diện để đảm bảo độ tin cậy của API, tính bảo mật của hệ thống Xác thực (Auth) và phân quyền (Middleware).

Chạy lệnh sau để khởi chạy bộ kiểm thử:
```bash
php artisan test
```

**Độ phủ kiểm thử (Test Coverage):**
- `AuthTest`: Quản lý phiên (Session), chống giả mạo (CSRF), và giới hạn lượt đăng nhập (Login throttling).
- `ApiTest`: Kiểm tra định dạng JSON và mã trạng thái (Status Code) của API.
- `MiddlewareTest`: Ngăn chặn truy cập trái phép và xác thực RBAC.
*(Kết quả: 26/26 Bài test thành công | 84 Assertions)*

---

## 🔑 Tài Khoản Đăng Nhập Mẫu (Sau khi Seed)

| Vai Trò | Tên Đăng Nhập | Mật Khẩu |
| :--- | :--- | :--- |
| **Quản Trị Viên (Admin)** | `admin` | `123456` |
| **Giảng Viên (Lecturer)** | `gv01` | `123456` |
| **Sinh Viên (Trưởng Nhóm)** | `sv01` | `123456` |

---

## 📄 Bản Quyền (License)
Dự án mã nguồn mở và được cung cấp dưới Giấy phép [MIT License](LICENSE).
Thiết kế và phát triển phục vụ mục đích quản lý đồ án học thuật và doanh nghiệp.
