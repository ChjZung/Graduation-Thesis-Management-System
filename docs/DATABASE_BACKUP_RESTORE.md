# Sao lưu và phục hồi MySQL V1

Chức năng nằm trong project gốc. V1 dùng cho môi trường thử nghiệm với dữ liệu giả lập. Cấu hình mặc định tắt, và môi trường Laravel **production** luôn tắt. Cơ sở dữ liệu nghiệp vụ, tệp báo cáo và pipeline AI không cần migration hay thay đổi.

## Phạm vi và kiến trúc

V1 sao lưu cấu trúc và dữ liệu của các bảng InnoDB trong một schema MySQL. Các đường dẫn tới tài liệu nghiệp vụ nằm trong SQL, còn tệp PDF, hình ảnh, đề cương và báo cáo sau bảo vệ vẫn nằm riêng trên đĩa. V1 xác nhận và xử lý schema chỉ có bảng InnoDB. Nó từ chối view, engine khác và khóa ngoại trỏ sang schema khác. Trigger, routine và event ở ngoài phạm vi, bị loại khỏi dump và bị từ chối nếu preflight nhìn thấy chúng. DBA cần kiểm kê các đối tượng này bằng tài khoản có quyền xem metadata nếu tài khoản sao lưu chỉ có SELECT.

V1 dùng PHP, Laravel Scheduler, Symfony Process, mysqldump và mysql có sẵn. Không thêm Composer dependency. Đã xác minh trên Windows Laragon với PHP 8.3.32 và MySQL server/client 8.0.30. Client hỗ trợ nhánh MySQL 8.0/8.4. Nên kiểm tra cặp server/client trên môi trường đích trước khi sử dụng.

| Tệp | Nhiệm vụ |
| --- | --- |
| **config/mysql_backup.php**, **.env.example** | Nguồn, đích, quyền thao tác, timeout, ngày lưu và lịch |
| **app/Services/Backup/BackupStore.php** | Manifest, khóa file, checksum, nhật ký audit và xóa file |
| **app/Services/Backup/MysqlBackupClient.php** | Kiểm tra kết nối, quyền restore, MySQL client, dump/import và dữ liệu |
| **app/Services/Backup/MysqlBackup.php** | Hàng chờ private, backup, phục hồi, phát hiện tác vụ bỏ dở và dọn bản cũ |
| **routes/console.php**, **app/Console/Commands/MysqlBackupCommand.php** | Scheduler và lệnh Artisan |
| **routes/web.php**, **app/Providers/AuthServiceProvider.php**, **app/Http/Controllers/Admin/MysqlBackupController.php** | Gate riêng, route, tải và xóa có kiểm tra phía server |
| **resources/views/admin/mysql_backup/index.blade.php**, **resources/views/layouts/admin.blade.php** | Menu và giao diện Backup |
| **tests/Feature/MysqlBackupTest.php** | Kiểm thử quyền, scheduler, checksum, trạng thái và MySQL thật |

~~~mermaid
flowchart LR
    A[Quản trị viên gửi yêu cầu] --> B[Manifest PENDING]
    C[Scheduler chạy mỗi phút] --> D{Khóa file chung}
    B --> D
    D --> E[mysqldump ghi file part]
    E --> F[Schema, số dòng và SHA-256]
    F --> G[Công bố SQL và manifest SUCCESS]
    E --> X[FAILED hoặc khôi phục trạng thái ở lượt kế]
    G --> H[Artisan xác nhận kết nối và schema đích trống]
    H --> I[mysql import vào schema độc lập]
    I --> J[So schema, số dòng, kiểm tra khóa ngoại]
    J --> K[VERIFIED hoặc FAILED]
~~~

## Cấu hình demo

1. Tạo hai schema có tên riêng, một nguồn có dữ liệu giả và một đích trống. Hai schema này khác database Laravel đang dùng. Ưu tiên tên chữ/số như **huitbackupdemo1** và **huitrecoverydemo1**. Các lệnh dưới đây dành cho schema chưa tồn tại và do DBA chạy. Thay mật khẩu trong ví dụ trước khi tạo tài khoản.

~~~sql
CREATE DATABASE huitbackupdemo1 CHARACTER SET utf8mb4;
CREATE DATABASE huitrecoverydemo1 CHARACTER SET utf8mb4;
CREATE USER 'huitdemo_reader'@'localhost' IDENTIFIED BY 'MAT_KHAU_NGUON';
CREATE USER 'huitdemo_restore'@'localhost' IDENTIFIED BY 'MAT_KHAU_RIENG_CHO_RESTORE';
GRANT SELECT ON huitbackupdemo1.* TO 'huitdemo_reader'@'localhost';
GRANT ALL PRIVILEGES ON huitrecoverydemo1.* TO 'huitdemo_restore'@'localhost';

USE huitbackupdemo1;
CREATE TABLE topics (
  id INT PRIMARY KEY,
  title VARCHAR(120) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE reports (
  id INT PRIMARY KEY,
  topic_id INT NOT NULL,
  body TEXT,
  CONSTRAINT fk_demo_topic FOREIGN KEY (topic_id) REFERENCES topics(id)
) ENGINE=InnoDB;
INSERT INTO huitbackupdemo1.topics VALUES (1, 'Đề tài mẫu');
INSERT INTO huitbackupdemo1.reports VALUES (1, 1, 'Báo cáo mẫu');
~~~

2. Thêm các biến sau vào .env local. Giữ nguyên DB_DATABASE của Laravel. MYSQL_BACKUP_OPERATOR_IDS nhận **MaTK** từ bảng taikhoan, phân cách bằng dấu phẩy. Tài khoản vẫn phải có vai trò Admin hoặc Giáo vụ và xuất hiện trong danh sách được cấp riêng.

~~~dotenv
MYSQL_BACKUP_ENABLED=true
MYSQL_BACKUP_AUTOMATIC=false
MYSQL_BACKUP_OPERATOR_IDS=MA_TAI_KHOAN_DUOC_CAP_QUYEN
MYSQL_BACKUP_HOST=127.0.0.1
MYSQL_BACKUP_PORT=3306
MYSQL_BACKUP_SCHEMA=huitbackupdemo1
MYSQL_BACKUP_USER=huitdemo_reader
MYSQL_BACKUP_PASSWORD=
MYSQL_RESTORE_HOST=127.0.0.1
MYSQL_RESTORE_PORT=3306
MYSQL_RESTORE_SCHEMA=huitrecoverydemo1
MYSQL_RESTORE_USER=huitdemo_restore
MYSQL_RESTORE_PASSWORD=
~~~

Dùng địa chỉ TCP như 127.0.0.1 để PHP và MySQL client kết nối cùng cổng, thay cho localhost có thể dùng socket trên Unix. Quyền restore phải giới hạn ở đúng schema và không có GRANT OPTION/role. Nếu schema có dấu gạch dưới, cấp quyền với tên được escape đúng để MySQL không hiểu dấu đó là wildcard trong GRANT.

Mặc định các binary lấy từ PATH. Có thể cấu hình MYSQL_BACKUP_DUMP_CLIENT và MYSQL_BACKUP_MYSQL_CLIENT bằng đường dẫn tuyệt đối. Dùng dấu nháy trong .env nếu đường dẫn chứa dấu cách. Cài MySQL client tương thích trên macOS/Linux. Khi chạy trong container, công cụ và thư mục private phải sẵn có trong tiến trình Laravel chạy Scheduler và lệnh khôi phục.

3. Chạy các lệnh trong thư mục project gốc.

~~~powershell
php artisan config:clear
php artisan schedule:list
php artisan schedule:work
~~~

schedule:work là tiến trình foreground cho local. Nếu dùng lịch hệ điều hành, chạy schedule:run mỗi phút từ đúng thư mục project bằng tài khoản có quyền trên private storage. Lệnh schedule:list chỉ xác nhận đã đăng ký. Giao diện báo tín hiệu Scheduler sau khi tick thực sự ghi heartbeat. Worker AI và các mốc thời gian giả lập không được dùng để quản lý lịch backup.

4. Mở menu **Sao lưu và phục hồi dữ liệu** và bấm **Sao lưu ngay**. Scheduler lấy bản thủ công đang chờ. Bấm **Làm mới** để xem kết quả hoặc chạy CLI:

~~~powershell
php artisan backup:mysql run
php artisan backup:mysql list
~~~

5. Lấy backup ID từ danh sách. Phục hồi lần đầu vào schema đích **trống**:

~~~powershell
php artisan backup:mysql restore BACKUP_ID --confirm=mysql://127.0.0.1:3306/huitrecoverydemo1
~~~

Thành công khi manifest hiện **SUCCESS · VERIFIED**. MySQL client dừng khi có lỗi SQL. Nếu import dở dang, lệnh đánh dấu FAILED và giữ schema đích để DBA kiểm tra. Lần chạy sau từ chối schema đã chứa bảng. Lệnh list/restore chỉ dùng private filesystem và kết nối MySQL đích, nên vẫn dùng được khi kết nối Laravel và nguồn dump không còn hoạt động. Nếu không có bản đang phục vụ cần ghi đè, không có bước sao lưu an toàn của schema đích trống.

## Vận hành và xử lý lỗi

- File và manifest lưu ở **storage/app/private/mysql-backups**. Nhật ký audit là **audit.ndjson**. File phải vừa đúng SHA-256 vừa có manifest SUCCESS để tải hay phục hồi. Tệp .part không được phục vụ.
- SHA-256 xác nhận dữ liệu file không đổi sau lúc tạo. VERIFIED được ghi sau kiểm tra schema, đếm dòng theo từng bảng và truy vấn kiểm tra bản ghi mồ côi trên schema restore. Bài thực nghiệm còn so sánh nội dung từng bản ghi mẫu.
- Trạng thái backup là PENDING/RUNNING/SUCCESS/FAILED. Trạng thái kiểm chứng độc lập là NOT_VERIFIED/VERIFYING/VERIFIED/FAILED.
- Backup, restore, tải xuống và dọn dẹp dùng cùng khóa file trong phạm vi một máy. Sau khi khóa được giải phóng bởi tiến trình bị dừng, lượt kế tiếp đánh dấu công việc bỏ dở và dọn file tạm. Tránh chạy DDL, migration, TRUNCATE hoặc đổi tên bảng trên nguồn trong lúc dump. mysqldump dùng snapshot InnoDB và không xuất GTID toàn server.
- Khi bật MYSQL_BACKUP_AUTOMATIC, mặc định chạy lúc 02:00 Asia/Ho_Chi_Minh. Scheduler bù lượt lịch gần nhất đã bỏ lỡ, chờ tối thiểu 15 phút giữa các lần thử tự động. Lịch lưu mặc định 30 ngày, giữ lại bản hợp lệ cuối cùng. Cấu hình theo máy và cần khởi động lại tiến trình Scheduler sau khi đổi.
- Chỉ root directory dành riêng cho worker quản trị được phép ghi manifest/SQL. Trên Unix, file cấu hình client tạm có mode 0600. Trên Windows, nó kế thừa ACL của thư mục private. Kiểm tra ACL trước khi bật vì SQL chứa dữ liệu nghiệp vụ và có thể chứa hash mật khẩu người dùng. Ứng dụng gỡ file xác thực tạm sau khi tiến trình kết thúc, và dọn file tạm còn sót khi lấy được khóa. Mật khẩu không nằm trong đối số lệnh và không xuất stderr thô vào log.

## Kết quả kiểm thử

Laravel/PHPUnit kiểm tra riêng quyền Giáo vụ, khi có hoặc chưa có quyền backup, tải file đúng checksum, dọn retention, giữ bản hợp lệ cuối, khóa đồng thời, tác vụ bị gián đoạn, Time Machine, restore sai checksum, schema nguồn và schema đích đã có bảng. Bài MySQL thật dùng hai schema độc lập cùng tài khoản nguồn chỉ đọc và tài khoản đích chỉ có quyền trên schema đó. So sánh hai bảng, bốn bản ghi, chuỗi tiếng Việt, dấu xuống dòng, dữ liệu nhị phân và khóa ngoại. Mô phỏng mất kết nối nguồn/ứng dụng trước khi chạy lệnh restore.

Chạy: **php artisan test --filter=MysqlBackupTest**. Case MySQL thật chỉ chạy khi cung cấp BACKUP_FIXTURE_SOURCE, BACKUP_FIXTURE_TARGET, BACKUP_FIXTURE_READER, BACKUP_FIXTURE_READER_PASSWORD, BACKUP_FIXTURE_RESTORER, BACKUP_FIXTURE_RESTORER_PASSWORD. Tên schema phải bắt đầu bằng huitbackupsrc và huitbackupdst theo sau bằng hậu tố hex. Fixture chứa hai bảng topics(id, title), reports(id, topic_id, body, evidence) và hai bản ghi ở mỗi bảng. Test khôi phục sẽ để lại schema đích đã nhập dữ liệu. Tạo schema đích trống mới cho lần chạy kế tiếp.

Trên fixture nhỏ, đo được dump 0,445 giây và restore kèm kiểm tra 0,506 giây. Đây là thời gian công cụ trong lần thử. RTO thật còn gồm chuẩn bị MySQL, chuyển môi trường và kiểm tra nghiệp vụ. Với lịch hằng ngày, RPO mục tiêu khoảng 24 giờ khi lịch chạy đều và backup thành công. Khi Scheduler hoặc backup lỗi, khoảng có thể mất dữ liệu tăng theo tuổi của bản hợp lệ cuối cùng.

## Giới hạn và phản biện

- **Sao lưu cấu trúc và dữ liệu có thay thế rollback không?** Transaction rollback xử lý thao tác chưa commit trong một phiên. MySQL crash recovery dựa vào redo/undo log để đưa server về trạng thái nhất quán. Backup phục hồi dữ liệu về ảnh chụp đã lưu. V1 không giữ binlog để khôi phục đến một thời điểm bất kỳ.
- **Vì sao dùng Artisan cho restore?** Người vận hành phải xác nhận đúng kết nối, nguồn tách đích và biết đích có thể nhập dở một phần vì DDL có implicit commit. Web chỉ có quản lý backup.
- **Vì sao không dùng backup này cho production?** V1 chưa có nơi lưu ngoài máy, mã hóa backup, kiểm thử trên dữ liệu thực và quy trình chuyển traffic. Các file nghiệp vụ cũng chưa được sao lưu trong V1.

