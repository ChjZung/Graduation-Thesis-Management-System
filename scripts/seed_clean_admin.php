<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

echo "=== KHỞI TẠO TÀI KHOẢN QUẢN TRỊ VÀ VAI TRÒ HỆ THỐNG ===\n";

$now = Carbon::now();

// 1. Tạo 5 Vai Trò hệ thống cố định
$roles = [
    ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ', 'created_at' => $now, 'updated_at' => $now],
    ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên', 'created_at' => $now, 'updated_at' => $now],
    ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên', 'created_at' => $now, 'updated_at' => $now],
    ['MaVaiTro' => 'VT04', 'TenVaiTro' => 'Trưởng bộ môn', 'created_at' => $now, 'updated_at' => $now],
    ['MaVaiTro' => 'VT05', 'TenVaiTro' => 'Trưởng khoa', 'created_at' => $now, 'updated_at' => $now],
];

foreach ($roles as $r) {
    DB::table('vaitro')->updateOrInsert(
        ['MaVaiTro' => $r['MaVaiTro']],
        $r
    );
    echo " + Vai trò: {$r['MaVaiTro']} - {$r['TenVaiTro']}\n";
}

// 2. Tạo Tài khoản Giáo vụ Quản trị (admin)
DB::table('taikhoan')->updateOrInsert(
    ['TenDangNhap' => 'admin'],
    [
        'MaTK' => 'TK_ADMIN',
        'MaVaiTro' => 'VT01',
        'MatKhau' => Hash::make('Admin@123'), // Mật khẩu an toàn mới cho Giáo vụ
        'TrangThai' => true,
        'SoLanDangNhapSai' => 0,
        'BatBuocDoiMatKhau' => false,
        'TrangThaiMatKhau' => 'ACTIVE',
        'LanDangNhapDau' => $now,
        'NgayDoiMatKhau' => $now,
        'created_at' => $now,
        'updated_at' => $now
    ]
);
echo " + Tài khoản Quản trị viên: admin (Mật khẩu: Admin@123)\n";

// 3. Tạo thông tin hồ sơ Giáo vụ (GVU01)
DB::table('giaovu')->updateOrInsert(
    ['MaGVu' => 'GVU01'],
    [
        'MaTK' => 'TK_ADMIN',
        'HoTen' => 'Quản trị viên Giáo vụ',
        'Email' => 'giaovu@huit.edu.vn',
        'SoDienThoai' => '0908123456',
        'ChucVu' => 'Giáo vụ Khoa',
        'MaKhoa' => null,
        'created_at' => $now,
        'updated_at' => $now
    ]
);
echo " + Hồ sơ Giáo vụ: GVU01 - Quản trị viên Giáo vụ\n";

echo "=== DATABASE ĐÃ ĐƯỢC RESET SẠCH 100% VÀ SẴN SÀNG ĐỂ IMPORT ===\n";
