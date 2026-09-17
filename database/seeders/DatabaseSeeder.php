<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. VaiTrò
        DB::table('VaiTro')->insertOrIgnore([
            ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ', 'created_at' => $now, 'updated_at' => $now],
            ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên', 'created_at' => $now, 'updated_at' => $now],
            ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 2. Tài Khoản
        $accounts = [
            [
                'MaTK' => 'TK_ADMIN',
                'MaVaiTro' => 'VT01',
                'TenDangNhap' => 'admin',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'NgayDoiMatKhau' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        // 10 Giảng viên (GV01 - GV10 & gv01 - gv10)
        for ($i = 1; $i <= 10; $i++) {
            $code = str_pad($i, 2, '0', STR_PAD_LEFT);
            $accounts[] = [
                'MaTK' => 'TK_GV' . $code,
                'MaVaiTro' => 'VT02',
                'TenDangNhap' => 'GV' . $code,
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'NgayDoiMatKhau' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // 50 Sinh viên theo định dạng chuẩn 200123xxxx (ví dụ: 2001230106, 2001230136)
        for ($i = 1; $i <= 50; $i++) {
            $maSV = '200123' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT);
            $accounts[] = [
                'MaTK' => 'TK_' . $maSV,
                'MaVaiTro' => 'VT03',
                'TenDangNhap' => $maSV,
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => ($i > 45), // Tài khoản cuối để test Onboarding đổi mật khẩu lần đầu (BR02)
                'TrangThaiMatKhau' => ($i > 45) ? 'INITIAL' : 'ACTIVE',
                'LanDangNhapDau' => ($i > 45) ? null : $now,
                'NgayDoiMatKhau' => ($i > 45) ? null : $now,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        DB::table('TaiKhoan')->insertOrIgnore($accounts);

        // 3. Khoa
        DB::table('Khoa')->insertOrIgnore([
            ['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa Công Nghệ Thông Tin', 'created_at' => $now, 'updated_at' => $now],
            ['MaKhoa' => 'TP',   'TenKhoa' => 'Khoa Công Nghệ Thực Phẩm', 'created_at' => $now, 'updated_at' => $now],
            ['MaKhoa' => 'DDT',  'TenKhoa' => 'Khoa Điện - Điện Tử', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 4. Bộ Môn
        DB::table('BoMon')->insertOrIgnore([
            ['MaBoMon' => 'CNPM', 'TenBoMon' => 'Công Nghệ Phần Mềm', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaBoMon' => 'HTTT', 'TenBoMon' => 'Hệ Thống Thông Tin', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaBoMon' => 'MMT',  'TenBoMon' => 'Mạng Máy Tính & ATTT', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaBoMon' => 'KHM',  'TenBoMon' => 'Khoa Học Máy Tính & AI', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaBoMon' => 'CBTP', 'TenBoMon' => 'Chế Biến Thực Phẩm', 'MaKhoa' => 'TP', 'created_at' => $now, 'updated_at' => $now],
            ['MaBoMon' => 'TUD',  'TenBoMon' => 'Tự Động Hóa', 'MaKhoa' => 'DDT', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 5. Ngành
        DB::table('Nganh')->insertOrIgnore([
            ['MaNganh' => '7480201', 'TenNganh' => 'Công Nghệ Thông Tin', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaNganh' => '7480103', 'TenNganh' => 'Kỹ Thuật Phần Mềm', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaNganh' => '7480202', 'TenNganh' => 'An Toàn Thông Tin', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaNganh' => '7540101', 'TenNganh' => 'Công Nghệ Thực Phẩm', 'MaKhoa' => 'TP', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 6. Chuyên Ngành
        DB::table('ChuyenNganh')->insertOrIgnore([
            ['MaChuyenNganh' => 'CN_WEB', 'TenChuyenNganh' => 'Phát triển Web & Ứng dụng Di động', 'TrangThai' => 'Đang hoạt động', 'MaNganh' => '7480103', 'created_at' => $now, 'updated_at' => $now],
            ['MaChuyenNganh' => 'CN_AI',  'TenChuyenNganh' => 'Trí Tuệ Nhân Tạo & Dữ Liệu Lớn', 'TrangThai' => 'Đang hoạt động', 'MaNganh' => '7480201', 'created_at' => $now, 'updated_at' => $now],
            ['MaChuyenNganh' => 'CN_SEC', 'TenChuyenNganh' => 'An Ninh Mạng & Đám Mây', 'TrangThai' => 'Đang hoạt động', 'MaNganh' => '7480202', 'created_at' => $now, 'updated_at' => $now],
            ['MaChuyenNganh' => 'CN_QA',  'TenChuyenNganh' => 'Quản Lý Chất Lượng Thực Phẩm', 'TrangThai' => 'Đang hoạt động', 'MaNganh' => '7540101', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. Lớp
        DB::table('Lop')->insertOrIgnore([
            ['MaLop' => '12DHTH01',  'TenLop' => '12DHTH01',  'KhoaHoc' => '2022-2026', 'MaNganh' => '7480201', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaLop' => '12DHTH02',  'TenLop' => '12DHTH02',  'KhoaHoc' => '2022-2026', 'MaNganh' => '7480201', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaLop' => '12DKTPM01', 'TenLop' => '12DKTPM01', 'KhoaHoc' => '2022-2026', 'MaNganh' => '7480103', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
            ['MaLop' => '12DATTT01', 'TenLop' => '12DATTT01', 'KhoaHoc' => '2022-2026', 'MaNganh' => '7480202', 'MaKhoa' => 'CNTT', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 8. Giáo Vụ
        DB::table('GiaoVu')->insertOrIgnore([
            [
                'MaGVu' => 'GVU01',
                'MaTK' => 'TK_ADMIN',
                'HoTen' => 'Nguyễn Thị Thu Hà',
                'Email' => 'admin@huit.edu.vn',
                'SoDienThoai' => '0908123456',
                'ChucVu' => 'Giáo vụ Khoa CNTT',
                'MaKhoa' => 'CNTT',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 9. Giảng Viên
        $giangViens = [
            ['MaGV' => 'GV01', 'MaTK' => 'TK_GV01', 'HoTen' => 'PGS.TS Trần Văn Hướng Dẫn', 'Email' => 'gv01@huit.edu.vn', 'SoDienThoai' => '0912000001', 'HocHam' => 'Phó Giáo sư', 'HocVi' => 'Tiến sĩ', 'MaBoMon' => 'CNPM'],
            ['MaGV' => 'GV02', 'MaTK' => 'TK_GV02', 'HoTen' => 'TS. Lê Thị Phản Biện',       'Email' => 'gv02@huit.edu.vn', 'SoDienThoai' => '0912000002', 'HocHam' => null,          'HocVi' => 'Tiến sĩ', 'MaBoMon' => 'CNPM'],
            ['MaGV' => 'GV03', 'MaTK' => 'TK_GV03', 'HoTen' => 'TS. Nguyễn Văn Chủ Tịch',     'Email' => 'gv03@huit.edu.vn', 'SoDienThoai' => '0912000003', 'HocHam' => null,          'HocVi' => 'Tiến sĩ', 'MaBoMon' => 'HTTT'],
            ['MaGV' => 'GV04', 'MaTK' => 'TK_GV04', 'HoTen' => 'ThS. Hoàng Thị Thư Ký',      'Email' => 'gv04@huit.edu.vn', 'SoDienThoai' => '0912000004', 'HocHam' => null,          'HocVi' => 'Thạc sĩ', 'MaBoMon' => 'HTTT'],
            ['MaGV' => 'GV05', 'MaTK' => 'TK_GV05', 'HoTen' => 'ThS. Đỗ Minh Ủy Viên',        'Email' => 'gv05@huit.edu.vn', 'SoDienThoai' => '0912000005', 'HocHam' => null,          'HocVi' => 'Thạc sĩ', 'MaBoMon' => 'MMT'],
            ['MaGV' => 'GV06', 'MaTK' => 'TK_GV06', 'HoTen' => 'TS. Vũ Quang Trí',           'Email' => 'gv06@huit.edu.vn', 'SoDienThoai' => '0912000006', 'HocHam' => null,          'HocVi' => 'Tiến sĩ', 'MaBoMon' => 'KHM'],
            ['MaGV' => 'GV07', 'MaTK' => 'TK_GV07', 'HoTen' => 'ThS. Bùi Đình Thảo',         'Email' => 'gv07@huit.edu.vn', 'SoDienThoai' => '0912000007', 'HocHam' => null,          'HocVi' => 'Thạc sĩ', 'MaBoMon' => 'CNPM'],
            ['MaGV' => 'GV08', 'MaTK' => 'TK_GV08', 'HoTen' => 'ThS. Ngô Thanh Sơn',         'Email' => 'gv08@huit.edu.vn', 'SoDienThoai' => '0912000008', 'HocHam' => null,          'HocVi' => 'Thạc sĩ', 'MaBoMon' => 'HTTT'],
            ['MaGV' => 'GV09', 'MaTK' => 'TK_GV09', 'HoTen' => 'TS. Mai Tuấn Kiệt',          'Email' => 'gv09@huit.edu.vn', 'SoDienThoai' => '0912000009', 'HocHam' => null,          'HocVi' => 'Tiến sĩ', 'MaBoMon' => 'MMT'],
            ['MaGV' => 'GV10', 'MaTK' => 'TK_GV10', 'HoTen' => 'ThS. Đặng Cẩm Tú',           'Email' => 'gv10@huit.edu.vn', 'SoDienThoai' => '0912000010', 'HocHam' => null,          'HocVi' => 'Thạc sĩ', 'MaBoMon' => 'KHM'],
        ];
        foreach ($giangViens as &$gv) {
            $gv['NgaySinh'] = '1980-01-01';
            $gv['GioiTinh'] = 'Nam';
            $gv['TrangThai'] = 'Đang công tác';
            $gv['created_at'] = $now;
            $gv['updated_at'] = $now;
        }
        DB::table('GiangVien')->insertOrIgnore($giangViens);

        // 10. Sinh Viên
        $sinhViens = [];
        $hoArray = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng'];
        $demArray = ['Văn', 'Thị', 'Đình', 'Minh', 'Ngọc', 'Hải', 'Thành', 'Quốc', 'Gia', 'Hoàng'];
        $tenArray = ['An', 'Bình', 'Châu', 'Dũng', 'Em', 'Giang', 'Hưng', 'Khánh', 'Long', 'Minh', 'Nam', 'Phúc', 'Quân', 'Sơn', 'Tài', 'Tú', 'Vinh', 'Xuân', 'Yến', 'Khoa'];

        $lopCodes = ['12DHTH01', '12DHTH02', '12DKTPM01', '12DATTT01'];

        for ($i = 1; $i <= 50; $i++) {
            $code = str_pad($i, 2, '0', STR_PAD_LEFT);
            $maSV = '200123' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT);
            $ho = $hoArray[$i % count($hoArray)];
            $dem = $demArray[$i % count($demArray)];
            $ten = $tenArray[$i % count($tenArray)];
            $hoTen = "$ho $dem $ten";
            $lop = $lopCodes[$i % count($lopCodes)];

            $sinhViens[] = [
                'MaSV' => $maSV,
                'MaTK' => 'TK_' . $maSV,
                'HoTen' => $hoTen,
                'NgaySinh' => '2004-05-15',
                'GioiTinh' => ($i % 3 == 0) ? 'Nữ' : 'Nam',
                'Email' => $maSV . '@huit.edu.vn',
                'SoDienThoai' => '0987' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'NgayNhapHoc' => '2022-09-05',
                'KhoaHoc' => '2022-2026',
                'SoTinChiTichLuy' => rand(125, 145),
                'DiemTichLuy' => round(rand(260, 390) / 100, 2),
                'TrangThai' => 'Đang học',
                'MaKhoa' => 'CNTT',
                'MaNganh' => ($lop === '12DKTPM01') ? '7480103' : (($lop === '12DATTT01') ? '7480202' : '7480201'),
                'MaLop' => $lop,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::table('SinhVien')->insertOrIgnore($sinhViens);

        // 11. Học Kỳ
        DB::table('HocKy')->insertOrIgnore([
            ['MaHocKy' => 'HK2526_1', 'TenHocKy' => 'Học kỳ 1 (2025-2026)', 'NamHoc' => '2025-2026', 'NgayDiHoc' => '2025-08-15', 'NgayKetThuc' => '2026-01-15', 'TrangThai' => 'Đã kết thúc', 'created_at' => $now, 'updated_at' => $now],
            ['MaHocKy' => 'HK2526_2', 'TenHocKy' => 'Học kỳ 2 (2025-2026)', 'NamHoc' => '2025-2026', 'NgayDiHoc' => '2026-01-15', 'NgayKetThuc' => '2026-06-30', 'TrangThai' => 'Đang diễn ra', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 12. Danh Sách SV Đủ Điều Kiện (BR03)
        $dsdk = [];
        for ($i = 1; $i <= 50; $i++) {
            $code = str_pad($i, 2, '0', STR_PAD_LEFT);
            $maSV = '200123' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT);
            $dsdk[] = [
                'MaDSDK' => 'DSDK_' . $code,
                'NgayXetDuyet' => '2026-01-10',
                'TrangThai' => 'Đủ điều kiện',
                'GhiChu' => 'Tích lũy >= 120 tín chỉ, CPA >= 2.50',
                'DieuKien' => 'Đạt chuẩn đầu ra KLTN',
                'MaSV' => $maSV,
                'MaHocKy' => 'HK2526_2',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::table('DanhSachSVDuDieuKien')->insertOrIgnore($dsdk);

        // 13. Chỉ Tiêu Hướng Dẫn (BR06: max 5 nhóm/GV)
        $chiTieus = [];
        for ($i = 1; $i <= 10; $i++) {
            $code = str_pad($i, 2, '0', STR_PAD_LEFT);
            $chiTieus[] = [
                'MaChiTieu' => 'CT_' . $code,
                'SoNhomToiDa' => 5,
                'NgayPhanBo' => '2026-01-12',
                'MaHocKy' => 'HK2526_2',
                'MaGV' => 'GV' . $code,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::table('ChiTieuHuongDan')->insertOrIgnore($chiTieus);

        // 14. Kế Hoạch Khóa Luận
        DB::table('KeHoachKhoaLuan')->insertOrIgnore([
            [
                'MakeHoach' => 'KH2526_02',
                'TenKeHoach' => 'Kế hoạch Khóa luận Tốt nghiệp Học kỳ 2 (2025-2026)',
                'NoiDung' => 'Triển khai công tác đăng ký, thực hiện và đánh giá khóa luận tốt nghiệp cho sinh viên khóa 12 hệ đại học chính quy.',
                'TrangThai' => 'Đang thực hiện',
                'NgayTao' => '2026-01-05',
                'MaHocKy' => 'HK2526_2',
                'MaGVu' => 'GVU01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 15. Mốc Thời Gian Khóa Luận
        DB::table('MocThoiGianKhoaLuan')->insertOrIgnore([
            ['MaMoc' => 'MOC01', 'TenMoc' => 'Đăng ký đề tài & Thành lập nhóm', 'NgayBatDau' => '2026-01-15', 'NgayKetThuc' => '2026-01-30', 'MoTa' => 'Sinh viên lập nhóm 3 thành viên và đăng ký đề tài', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaMoc' => 'MOC02', 'TenMoc' => 'Báo cáo tiến độ lần 1 (Đặc tả & Thiết kế)', 'NgayBatDau' => '2026-02-15', 'NgayKetThuc' => '2026-02-28', 'MoTa' => 'Nộp báo cáo tiến độ tuần 4', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaMoc' => 'MOC03', 'TenMoc' => 'Báo cáo tiến độ lần 2 (Hiện thực hệ thống)', 'NgayBatDau' => '2026-03-25', 'NgayKetThuc' => '2026-04-10', 'MoTa' => 'Nộp báo cáo tiến độ tuần 10', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaMoc' => 'MOC04', 'TenMoc' => 'Nộp hồ sơ bảo vệ & Kiểm tra đạo văn', 'NgayBatDau' => '2026-05-01', 'NgayKetThuc' => '2026-05-15', 'MoTa' => 'Nộp toàn văn báo cáo và chứng nhận Turnitin', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaMoc' => 'MOC05', 'TenMoc' => 'Bảo vệ Khóa luận trước Hội đồng', 'NgayBatDau' => '2026-06-01', 'NgayKetThuc' => '2026-06-15', 'MoTa' => 'Tổ chức các hội đồng chấm bảo vệ khóa luận', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 16. Quy Định Khóa Luận
        DB::table('QuyDinhKhoaLuan')->insertOrIgnore([
            ['MaQuyDinh' => 'QD01', 'TenQuyDinh' => 'Giới hạn số thành viên nhóm', 'GiaTri' => '3 sinh viên', 'MoTa' => 'Mỗi nhóm khóa luận tối đa 3 sinh viên', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaQuyDinh' => 'QD02', 'TenQuyDinh' => 'Tỷ lệ trùng lặp đạo văn tối đa', 'GiaTri' => '20%', 'MoTa' => 'Kiểm tra qua hệ thống Turnitin trường', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaQuyDinh' => 'QD03', 'TenQuyDinh' => 'Chỉ tiêu giảng viên hướng dẫn', 'GiaTri' => '5 nhóm', 'MoTa' => 'Mỗi giảng viên hướng dẫn tối đa 5 nhóm trong 1 học kỳ', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 17. Biểu Mẫu
        DB::table('BieuMau')->insertOrIgnore([
            ['MaBieuMau' => 'BM01', 'TenBieuMau' => 'Mẫu phiếu đăng ký đề tài khóa luận', 'DuongDanFile' => 'uploads/bieumau/BM01_PhieuDangKy.docx', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaBieuMau' => 'BM02', 'TenBieuMau' => 'Mẫu báo cáo tiến độ định kỳ', 'DuongDanFile' => 'uploads/bieumau/BM02_BaoCaoTienDo.docx', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
            ['MaBieuMau' => 'BM03', 'TenBieuMau' => 'Mẫu phiếu nhận xét của giảng viên hướng dẫn', 'DuongDanFile' => 'uploads/bieumau/BM03_NhanXetGVHD.docx', 'MakeHoach' => 'KH2526_02', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 18. Thông Báo
        DB::table('ThongBao')->insertOrIgnore([
            [
                'MaThongBao' => 'TB01',
                'TieuDe' => 'Thông báo kế hoạch triển khai KLTN Học kỳ 2 (2025-2026)',
                'NoiDung' => 'Khoa CNTT thông báo đến toàn thể sinh viên đủ điều kiện làm khóa luận tốt nghiệp tiến hành chọn đề tài và ghép nhóm.',
                'LoaiThongBao' => 'Kế hoạch',
                'DoiTuongNhan' => 'Tất cả',
                'NgayTao' => '2026-01-10',
                'TrangThai' => 'Đã phát hành',
                'MaGVu' => 'GVU01',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'MaThongBao' => 'TB02',
                'TieuDe' => 'Nhắc nhở hạn nộp Báo cáo tiến độ lần 1',
                'NoiDung' => 'Hạn chót nộp báo cáo tiến độ lần 1 là ngày 28/02/2026. Đề nghị các nhóm sinh viên tải file tài liệu lên hệ thống.',
                'LoaiThongBao' => 'Nhắc nhở',
                'DoiTuongNhan' => 'Sinh viên',
                'NgayTao' => '2026-02-20',
                'TrangThai' => 'Đã phát hành',
                'MaGVu' => 'GVU01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 19. Đề Tài
        $deTais = [
            ['MaDeTai' => 'DT01', 'TenDeTai' => 'Xây dựng Hệ thống Quản lý Khóa luận Tốt nghiệp trực tuyến tích hợp AI', 'MoTa' => 'Phát triển nền tảng quản lý quy trình đồ án, tóm tắt báo cáo tiến độ bằng LLM', 'YeuCau' => 'PHP Laravel, Vue/Blade, MySQL, OpenAI API', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV01', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT02', 'TenDeTai' => 'Nghiên cứu và triển khai giải pháp phát hiện gian lận thi cử sử dụng Computer Vision', 'MoTa' => 'Ứng dụng YOLOv8 và OpenCV phát hiện cử chỉ bất thường trong phòng thi', 'YeuCau' => 'Python, PyTorch, OpenCV, FastApi', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV01', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT03', 'TenDeTai' => 'Xây dựng ứng dụng đặt lịch và tư vấn sức khỏe từ xa (Telehealth) đa nền tảng', 'MoTa' => 'Ứng dụng Flutter kết nối bệnh nhân và bác sĩ, gọi video WebRTC', 'YeuCau' => 'Flutter, Node.js, WebRTC, MongoDB', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV02', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT04', 'TenDeTai' => 'Hệ thống đánh giá năng lực ứng viên tự động qua video phỏng vấn bằng AI', 'MoTa' => 'Phân tích biểu cảm khuôn mặt và giọng nói ứng viên phỏng vấn tuyển dụng', 'YeuCau' => 'Python, Librosa, Whisper, ReactJS', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV03', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT05', 'TenDeTai' => 'Ứng dụng Blockchain trong truy xuất nguồn gốc nông sản an toàn', 'MoTa' => 'Sử dụng Smart Contract Ethereum và QR Code theo dõi chuỗi cung ứng thực phẩm', 'YeuCau' => 'Solidity, Web3.js, React, Node.js', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV04', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT06', 'TenDeTai' => 'Xây dựng giải pháp giám sát an ninh mạng SIEM trên nền tảng mã nguồn mở Elastic', 'MoTa' => 'Thu thập log mạng, phát hiện tấn công DDoS và Brute Force thời gian thực', 'YeuCau' => 'Elasticsearch, Logstash, Kibana, Snort', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV05', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT07', 'TenDeTai' => 'Hệ thống gợi ý sản phẩm thương mại điện tử dựa trên học sâu (Deep Learning)', 'MoTa' => 'Xây dựng mô hình Collaborative Filtering & Transformer gợi ý cá nhân hóa', 'YeuCau' => 'Python, TensorFlow, Redis, Django', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV06', 'MaHocKy' => 'HK2526_2'],
            ['MaDeTai' => 'DT08', 'TenDeTai' => 'Phát triển hệ thống IoT giám sát chất lượng môi trường nước nuôi trồng thủy sản', 'MoTa' => 'Thiết bị cảm biến ESP32 đo pH, độ mặn, nhiệt độ gửi lên Cloud Dashboard', 'YeuCau' => 'ESP32, MQTT, Node-RED, InfluxDB', 'TrangThai' => 'Đã duyệt', 'MaGV' => 'GV07', 'MaHocKy' => 'HK2526_2'],
        ];
        foreach ($deTais as &$dt) {
            $dt['NgayDeXuat'] = '2026-01-14';
            $dt['created_at'] = $now;
            $dt['updated_at'] = $now;
        }
        DB::table('DeTai')->insertOrIgnore($deTais);

        // 20. Chi Tiết Duyệt Đề Tài
        $duyetDeTais = [];
        foreach ($deTais as $dt) {
            $duyetDeTais[] = [
                'MaDuyet' => 'DUYET_' . $dt['MaDeTai'],
                'NgayDuyet' => '2026-01-16',
                'TrangThai' => 'Đã duyệt',
                'LyDo' => 'Đề tài đạt yêu cầu chuyên môn và phù hợp khối lượng đồ án tốt nghiệp',
                'MaGV' => $dt['MaGV'],
                'MaDeTai' => $dt['MaDeTai'],
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::table('ChiTietDuyetDeTai')->insertOrIgnore($duyetDeTais);

        // 21. Phân Công Phản Biện (BR07: GVHD <> GVPB)
        DB::table('PhanCongPhanBien')->insertOrIgnore([
            ['MaPhanCong' => 'PCPB01', 'VaiTro' => 'Phản biện', 'NgayPhanCong' => '2026-04-15', 'TrangThai' => 'Đã phân công', 'MaGV' => 'GV02', 'MaDeTai' => 'DT01', 'created_at' => $now, 'updated_at' => $now],
            ['MaPhanCong' => 'PCPB02', 'VaiTro' => 'Phản biện', 'NgayPhanCong' => '2026-04-15', 'TrangThai' => 'Đã phân công', 'MaGV' => 'GV03', 'MaDeTai' => 'DT02', 'created_at' => $now, 'updated_at' => $now],
            ['MaPhanCong' => 'PCPB03', 'VaiTro' => 'Phản biện', 'NgayPhanCong' => '2026-04-15', 'TrangThai' => 'Đã phân công', 'MaGV' => 'GV01', 'MaDeTai' => 'DT03', 'created_at' => $now, 'updated_at' => $now],
            ['MaPhanCong' => 'PCPB04', 'VaiTro' => 'Phản biện', 'NgayPhanCong' => '2026-04-15', 'TrangThai' => 'Đã phân công', 'MaGV' => 'GV06', 'MaDeTai' => 'DT04', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 22. Nhóm
        DB::table('Nhom')->insertOrIgnore([
            ['MaNhom' => 'NH01', 'TenNhom' => 'Nhóm SV01', 'TrangThai' => 'Đang hoạt động', 'NgayTao' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH02', 'TenNhom' => 'Nhóm SV04', 'TrangThai' => 'Đang hoạt động', 'NgayTao' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH03', 'TenNhom' => 'Nhóm SV07', 'TrangThai' => 'Đang hoạt động', 'NgayTao' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH04', 'TenNhom' => 'Nhóm SV10', 'TrangThai' => 'Đang hoạt động', 'NgayTao' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 23. Thành Viên Nhóm (BR04, BR05: 3 SV/nhóm)
        DB::table('ThanhVienNhom')->insertOrIgnore([
            // Nhóm 1 (Đề tài 1)
            ['MaNhom' => 'NH01', 'MaSV' => '2001230101', 'VaiTro' => 'Nhóm trưởng', 'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH01', 'MaSV' => '2001230102', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH01', 'MaSV' => '2001230103', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],

            // Nhóm 2 (Đề tài 2)
            ['MaNhom' => 'NH02', 'MaSV' => '2001230104', 'VaiTro' => 'Nhóm trưởng', 'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH02', 'MaSV' => '2001230105', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH02', 'MaSV' => '2001230106', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-16', 'created_at' => $now, 'updated_at' => $now],

            // Nhóm 3 (Đề tài 3)
            ['MaNhom' => 'NH03', 'MaSV' => '2001230107', 'VaiTro' => 'Nhóm trưởng', 'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH03', 'MaSV' => '2001230108', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH03', 'MaSV' => '2001230109', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],

            // Nhóm 4 (Đề tài 4)
            ['MaNhom' => 'NH04', 'MaSV' => '2001230110', 'VaiTro' => 'Nhóm trưởng', 'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH04', 'MaSV' => '2001230111', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
            ['MaNhom' => 'NH04', 'MaSV' => '2001230112', 'VaiTro' => 'Thành viên',  'NgayThamGia' => '2026-01-17', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 24. Phiếu Đăng Ký Đề Tài
        DB::table('PhieuDangKy')->insertOrIgnore([
            ['MaDangKy' => 'DK01', 'NgayDangKy' => '2026-01-18', 'TrangThai' => 'Đã duyệt', 'NgayDuyet' => '2026-01-20', 'LyDoTuChoi' => null, 'MaNhom' => 'NH01', 'MaDeTai' => 'DT01', 'created_at' => $now, 'updated_at' => $now],
            ['MaDangKy' => 'DK02', 'NgayDangKy' => '2026-01-18', 'TrangThai' => 'Đã duyệt', 'NgayDuyet' => '2026-01-20', 'LyDoTuChoi' => null, 'MaNhom' => 'NH02', 'MaDeTai' => 'DT02', 'created_at' => $now, 'updated_at' => $now],
            ['MaDangKy' => 'DK03', 'NgayDangKy' => '2026-01-19', 'TrangThai' => 'Đã duyệt', 'NgayDuyet' => '2026-01-21', 'LyDoTuChoi' => null, 'MaNhom' => 'NH03', 'MaDeTai' => 'DT03', 'created_at' => $now, 'updated_at' => $now],
            ['MaDangKy' => 'DK04', 'NgayDangKy' => '2026-01-19', 'TrangThai' => 'Đã duyệt', 'NgayDuyet' => '2026-01-21', 'LyDoTuChoi' => null, 'MaNhom' => 'NH04', 'MaDeTai' => 'DT04', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 25. Lịch Gặp Hướng Dẫn
        DB::table('LichGapHuongDan')->insertOrIgnore([
            [
                'MaLichGap' => 'LG01',
                'ThoiGianBatDau' => '2026-02-10 09:00:00',
                'ThoiGianKetThuc' => '2026-02-10 11:00:00',
                'DiaDiem' => 'Văn phòng Bộ môn CNPM (B.304)',
                'NoiDung' => 'Trao đổi về kiến trúc microservices và phân rã các module chức năng',
                'TrangThai' => 'Đã hoàn thành',
                'NgayTao' => '2026-02-01',
                'MaNhom' => 'NH01',
                'MaGV' => 'GV01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 26. Báo Cáo Tiến Độ
        DB::table('BaoCaoTienDo')->insertOrIgnore([
            [
                'MaBaoCao' => 'BC01',
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo tiến độ lần 1 - Khảo sát & Phân tích yêu cầu',
                'NoiDungBaoCao' => 'Nhóm đã hoàn thành tài liệu SRS, thiết kế Use Case diagram và sơ đồ cơ sở dữ liệu ERD 34 bảng.',
                'NgayNop' => '2026-02-20',
                'TrangThai' => 'Đã nhận xét',
                'TenFile' => 'BaoCaoTienDo_Lan1_Nhom01.pdf',
                'DuongDanFile' => 'uploads/baocao/BaoCaoTienDo_Lan1_Nhom01.pdf',
                'NhanXet' => 'Nhóm làm việc nghiêm túc, đặc tả yêu cầu chi tiết. Cần bổ sung thêm sơ đồ Sequence Diagram cho module chấm điểm.',
                'MaMoc' => 'MOC02',
                'MaDeTai' => 'DT01',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'MaBaoCao' => 'BC02',
                'LanBaoCao' => 2,
                'TieuDe' => 'Báo cáo tiến độ lần 2 - Hiện thực hóa giao diện và CSDL',
                'NoiDungBaoCao' => 'Nhóm đã xây dựng hoàn chỉnh giao diện giáo vụ, giảng viên và sinh viên, kết nối API AI tóm tắt.',
                'NgayNop' => '2026-04-05',
                'TrangThai' => 'Đã nhận xét',
                'TenFile' => 'BaoCaoTienDo_Lan2_Nhom01.pdf',
                'DuongDanFile' => 'uploads/baocao/BaoCaoTienDo_Lan2_Nhom01.pdf',
                'NhanXet' => 'Giao diện thân thiện, chức năng hoạt động đúng logic. Chuẩn bị chạy thử nghiệm để nộp hồ sơ bảo vệ.',
                'MaMoc' => 'MOC03',
                'MaDeTai' => 'DT01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 27. Tóm Tắt Báo Cáo (AI / LLM)
        DB::table('TomTatBaoCao')->insertOrIgnore([
            [
                'MaTomTat' => 'TT01',
                'CongViecDaHoanThanh' => '- Khảo sát hiện trạng hệ thống quản lý đồ án cũ của trường HUIT.\n- Xây dựng tài liệu đặc tả chức năng (SRS) gồm 13 quy tắc nghiệp vụ BR01-BR13.\n- Thiết kế kiến trúc CSDL quan hệ 34 bảng chuẩn hóa theo mô hình Rational Rose.',
                'KhoKhan' => '- Cần chuẩn hóa các ràng buộc liên kết để đảm bảo một sinh viên không đăng ký nhiều nhóm trong cùng một học kỳ.',
                'KeHoachTuanToi' => '- Bắt tay vào hiện thực hóa các màn hình giao diện Bootstrap 5 cho Giáo vụ và Giảng viên.\n- Viết module kết nối LLM API trích xuất tóm tắt nội dung file báo cáo.',
                'NoiDungAI' => 'Bản tóm tắt tự động được trích xuất bằng mô hình AI Gemini qua kỹ thuật Map-Reduce Chunking.',
                'TrangThai' => 'Đã tạo',
                'NgayTomTat' => '2026-02-20',
                'MaBaoCao' => 'BC01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 28. Tài Liệu Nộp (BR12: Quản lý phiên bản)
        DB::table('TaiLieuNop')->insertOrIgnore([
            ['MaTaiLieu' => 'TL01', 'TenTaiLieu' => 'Tài liệu Thiết kế Kiến trúc Hệ thống v1.0', 'DuongDan' => 'uploads/tailieu/KienTruc_v1.pdf', 'LoaiTaiLieu' => 'Tài liệu thiết kế', 'LanNop' => 1, 'NgayNop' => '2026-02-20', 'GhiChu' => 'Bản thảo ban đầu', 'MaDeTai' => 'DT01', 'created_at' => $now, 'updated_at' => $now],
            ['MaTaiLieu' => 'TL02', 'TenTaiLieu' => 'Tài liệu Thiết kế Kiến trúc Hệ thống v2.0 (Chỉnh sửa)', 'DuongDan' => 'uploads/tailieu/KienTruc_v2.pdf', 'LoaiTaiLieu' => 'Tài liệu thiết kế', 'LanNop' => 2, 'NgayNop' => '2026-03-01', 'GhiChu' => 'Cập nhật theo góp ý của GVHD', 'MaDeTai' => 'DT01', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 29. Hội Đồng (BR08, BR09)
        DB::table('HoiDong')->insertOrIgnore([
            [
                'MaHoiDong' => 'HD01',
                'TenHoiDong' => 'Hội đồng Bảo vệ KLTN số 01 - Chuyên ngành CNPM',
                'ThoiGianBatDau' => '2026-06-05 08:00:00',
                'ThoiGianKetThuc' => '2026-06-05 11:30:00',
                'DiaDiem' => 'Phòng Hội đồng B.401 - Cơ sở chính',
                'TrangThai' => 'Chưa diễn ra',
                'GhiChu' => 'Đánh giá các đề tài ứng dụng phần mềm và trí tuệ nhân tạo',
                'NgayBaoVe' => '2026-06-05',
                'MaDeTai' => 'DT01',
                'MaGV' => 'GV03',
                'MaHocKy' => 'HK2526_2',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 30. Thành Viên Hội Đồng (BR08: tối thiểu 3 thành viên, không trùng GVHD)
        DB::table('ThanhVienHoiDong')->insertOrIgnore([
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV03', 'VaiTro' => 'Chủ tịch',  'created_at' => $now, 'updated_at' => $now],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV04', 'VaiTro' => 'Thư ký',    'created_at' => $now, 'updated_at' => $now],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV02', 'VaiTro' => 'Ủy viên',   'created_at' => $now, 'updated_at' => $now],
        ]);

        // 31. Hồ Sơ Bảo Vệ (BR10)
        DB::table('HoSoBaoVe')->insertOrIgnore([
            [
                'MaHoSo' => 'HS01',
                'NgayLap' => '2026-05-02',
                'NgayNop' => '2026-05-10',
                'NgayXacNhan' => '2026-05-12',
                'XacNhanGVHD' => 'Đã duyệt', // BR10: Đã duyệt
                'ThoiGianBaoVe' => '2026-06-05 08:30:00',
                'PhongBaoVe' => 'Phòng B.401',
                'TrangThai' => 'Đủ điều kiện bảo vệ',
                'GhiChu' => 'Hồ sơ đầy đủ, tỷ lệ đạo văn Turnitin đạt chuẩn 8%',
                'MaGVu' => 'GVU01',
                'MaHoiDong' => 'HD01',
                'MaNhom' => 'NH01',
                'MaDeTai' => 'DT01',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        // 32. Tệp Hồ Sơ Bảo Vệ
        DB::table('TepHoSoBaoVe')->insertOrIgnore([
            ['MaTep' => 'TEP01', 'TenTep' => 'ToanVanKhoaLuan_Nhom01.pdf', 'LoaiTep' => 'Khóa luận toàn văn', 'DuongDanFile' => 'uploads/hoso/ToanVanKhoaLuan_Nhom01.pdf', 'PhienBan' => '1.0', 'NgayNop' => '2026-05-10', 'TrangThai' => 'Đã kiểm duyệt', 'GhiChu' => 'Bản hoàn thiện cuối cùng', 'MaHoSo' => 'HS01', 'created_at' => $now, 'updated_at' => $now],
            ['MaTep' => 'TEP02', 'TenTep' => 'MinhChungTurnitin_Nhom01.pdf', 'LoaiTep' => 'Minh chứng đạo văn', 'DuongDanFile' => 'uploads/hoso/MinhChungTurnitin_Nhom01.pdf', 'PhienBan' => '1.0', 'NgayNop' => '2026-05-10', 'TrangThai' => 'Đã kiểm duyệt', 'GhiChu' => 'Tỷ lệ trùng lặp: 8%', 'MaHoSo' => 'HS01', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 33. Phiếu Chấm Điểm (BR11)
        DB::table('PhieuChamDiem')->insertOrIgnore([
            // Điểm Hướng dẫn (GV01)
            ['MaPhieuChamDiem' => 'PCD01', 'Diem' => 9.00, 'NgayCham' => '2026-05-20', 'NhanXet' => 'Sinh viên chủ động, hoàn thành xuất sắc các mục tiêu đề tài đặt ra.', 'MaDeTai' => 'DT01', 'MaHoiDong' => null,   'MaGV' => 'GV01', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            // Điểm Phản biện (GV02)
            ['MaPhieuChamDiem' => 'PCD02', 'Diem' => 8.50, 'NgayCham' => '2026-05-25', 'NhanXet' => 'Đề tài có tính ứng dụng cao, cần tối ưu thêm hiệu năng truy vấn CSDL.', 'MaDeTai' => 'DT01', 'MaHoiDong' => null,   'MaGV' => 'GV02', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            // Điểm Hội đồng - Chủ tịch (GV03)
            ['MaPhieuChamDiem' => 'PCD03', 'Diem' => 9.20, 'NgayCham' => '2026-06-05', 'NhanXet' => 'Phần thuyết trình mạch lạc, trả lời tốt câu hỏi phản biện của hội đồng.', 'MaDeTai' => 'DT01', 'MaHoiDong' => 'HD01', 'MaGV' => 'GV03', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            // Điểm Hội đồng - Thư ký (GV04)
            ['MaPhieuChamDiem' => 'PCD04', 'Diem' => 8.80, 'NgayCham' => '2026-06-05', 'NhanXet' => 'Sản phẩm demo chạy ổn định, các chức năng đáp ứng đầy đủ.', 'MaDeTai' => 'DT01', 'MaHoiDong' => 'HD01', 'MaGV' => 'GV04', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            // Điểm Hội đồng - Ủy viên (GV02)
            ['MaPhieuChamDiem' => 'PCD05', 'Diem' => 9.00, 'NgayCham' => '2026-06-05', 'NhanXet' => 'Đã bổ sung đầy đủ các góp ý ở vòng phản biện.', 'MaDeTai' => 'DT01', 'MaHoiDong' => 'HD01', 'MaGV' => 'GV02', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 34. Kết Quả Sinh Viên (Công thức: Tổng kết = 0.2*HD + 0.3*PB + 0.5*HDTB)
        // HDTB = (9.2 + 8.8 + 9.0)/3 = 9.0. Tổng kết = 0.2*9.0 + 0.3*8.5 + 0.5*9.0 = 1.8 + 2.55 + 4.5 = 8.85
        DB::table('KetQuaSinhVien')->insertOrIgnore([
            ['MaKetQua' => 'KQ01', 'DiemPhanBien' => 8.50, 'DiemHoiDongTB' => 9.00, 'DiemTongKet' => 8.85, 'KetQua' => 'Đạt (Xuất sắc)', 'NhanXetChung' => 'Khóa luận xuất sắc, có thể gửi dự thi sinh viên nghiên cứu khoa học.', 'NgayCham' => '2026-06-05', 'MaSV' => '2001230101', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            ['MaKetQua' => 'KQ02', 'DiemPhanBien' => 8.50, 'DiemHoiDongTB' => 9.00, 'DiemTongKet' => 8.85, 'KetQua' => 'Đạt (Xuất sắc)', 'NhanXetChung' => 'Khóa luận xuất sắc, tinh thần làm việc nhóm rất tốt.', 'NgayCham' => '2026-06-05', 'MaSV' => '2001230102', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
            ['MaKetQua' => 'KQ03', 'DiemPhanBien' => 8.50, 'DiemHoiDongTB' => 9.00, 'DiemTongKet' => 8.85, 'KetQua' => 'Đạt (Xuất sắc)', 'NhanXetChung' => 'Khóa luận xuất sắc, kỹ năng lập trình tốt.', 'NgayCham' => '2026-06-05', 'MaSV' => '2001230103', 'MaHoSo' => 'HS01', 'MaHocKy' => 'HK2526_2', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
