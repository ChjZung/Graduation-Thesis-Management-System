<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

$flowAccounts = [
    'admin'                    => 'Giáo vụ / Quản trị viên',
    'TK_CNTT_GV00000001'       => 'Trưởng Khoa CNTT (PGS.TS. Nguyễn Văn Hùng)',
    'TBM_CNTT_CNPM_GV00000002' => 'Trưởng Bộ Môn CNPM (TS. Trần Anh Dũng)',
    'GV00000023'               => 'Giảng viên Hướng dẫn (TS. Lê Hoàng Cường)',
    'GV00000021'               => 'Giảng viên Phản biện / Thư ký HĐ (ThS. Đặng Hải Giang)',
    '2001210080'               => 'Sinh viên Trưởng nhóm mới (Lê Hoàng Nam)',
    '2001210081'               => 'Sinh viên Thành viên 1 mới (Nguyễn Mai Phương)',
    '2001210082'               => 'Sinh viên Thành viên 2 mới (Vũ Tiến Đạt)',
];

echo "=== KIEM TRA MAT KHAU & TRANG THAI CAC TAI KHOAN TRONG LUONG ===\n";
foreach ($flowAccounts as $username => $role) {
    $tk = TaiKhoan::where('TenDangNhap', $username)->first();
    if (!$tk) {
        echo " [FAIL] Khong tim thay: {$username}\n";
        continue;
    }
    $passOk = Hash::check('Huit@2026', $tk->MatKhau);
    echo " [PASS] {$username} ({$role})\n";
    echo "        MatKhau Huit@2026: " . ($passOk ? 'DUNG' : 'SAI') . " | TrangThai: {$tk->TrangThai} | BatBuocDoiMK: " . ($tk->BatBuocDoiMatKhau ? 'YES' : 'NO') . "\n";
}
