<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=================================================================\n";
echo "TIEN HANH KHOI TAO 3 SINH VIEN MOI THUOC CONG NGHE PHAN MEM\n";
echo "=================================================================\n";

$studentsData = [
    [
        'MaSV'        => '2001210076',
        'HoTen'       => 'Nguyễn Đức Duy',
        'GioiTinh'    => 'Nam',
        'NgaySinh'    => '2003-04-12',
        'Email'       => '2001210076@st.huit.edu.vn',
        'SoDienThoai' => '0978001076',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 130,
        'GPA'         => 3.55,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm
        'Khoa'        => 'CNTT',
    ],
    [
        'MaSV'        => '2001210077',
        'HoTen'       => 'Trần Thị Thu Trang',
        'GioiTinh'    => 'Nữ',
        'NgaySinh'    => '2003-08-20',
        'Email'       => '2001210077@st.huit.edu.vn',
        'SoDienThoai' => '0978001077',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 128,
        'GPA'         => 3.42,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm
        'Khoa'        => 'CNTT',
    ],
    [
        'MaSV'        => '2001210078',
        'HoTen'       => 'Phạm Minh Triết',
        'GioiTinh'    => 'Nam',
        'NgaySinh'    => '2003-11-05',
        'Email'       => '2001210078@st.huit.edu.vn',
        'SoDienThoai' => '0978001078',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 134,
        'GPA'         => 3.68,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm
        'Khoa'        => 'CNTT',
    ],
];

$passwordHash = Hash::make('Huit@2026');
$now = now();

DB::beginTransaction();
try {
    foreach ($studentsData as $data) {
        $masv = $data['MaSV'];

        // 1. Tạo hoặc cập nhật Tài khoản
        $tk = TaiKhoan::updateOrCreate(
            ['TenDangNhap' => $masv],
            [
                'MaTK'              => $masv,
                'MatKhau'           => $passwordHash,
                'MaVaiTro'          => 'VT03', // Vai trò Sinh viên
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'ACTIVE',
                'BatBuocDoiMatKhau' => false,
                'SoLanDangNhapSai'  => 0,
                'NgayDoiMatKhau'    => $now,
                'LanDangNhapDau'    => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        // 2. Tạo hoặc cập nhật Sinh viên
        $sv = SinhVien::updateOrCreate(
            ['MaSV' => $masv],
            [
                'MaTK'            => $masv,
                'HoTen'           => $data['HoTen'],
                'NgaySinh'        => $data['NgaySinh'],
                'GioiTinh'        => $data['GioiTinh'],
                'Email'           => $data['Email'],
                'SoDienThoai'     => $data['SoDienThoai'],
                'KhoaHoc'         => $data['KhoaHoc'],
                'SoTinChiTichLuy' => $data['TinChi'],
                'DiemTichLuy'     => $data['GPA'],
                'TrangThai'       => 'Đang học',
                'MaKhoa'          => $data['Khoa'],
                'MaNganh'         => $data['Nganh'],
                'MaLop'           => $data['Lop'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        );

        // 3. Thêm vào Danh Sách Đủ Điều Kiện (HK2627_1)
        $maDsdk = "DK_{$masv}_HK2627";
        DanhSachSVDuDieuKien::updateOrCreate(
            ['MaDSDK' => $maDsdk],
            [
                'MaSV'         => $masv,
                'MaHocKy'      => 'HK2627_1',
                'NgayXetDuyet' => $now->toDateString(),
                'TrangThai'    => 'Đủ điều kiện',
                'DieuKien'     => "Tín chỉ: {$data['TinChi']} | ĐTB: {$data['GPA']}",
                'GhiChu'       => 'Sinh viên Kỹ thuật Phần mềm (Bộ môn CNPM)',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );

        echo " [OK] Đã tạo thành công Sinh viên: [{$masv}] {$data['HoTen']} - Lớp: {$data['Lop']} - Ngành: {$data['Nganh']}\n";
    }

    DB::commit();
    echo "\n=== HOÀN TẤT KHỞI TẠO 3 SINH VIEN MOI ===\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo " [ERROR] Lỗi khi tạo sinh viên: " . $e->getMessage() . "\n";
    exit(1);
}
