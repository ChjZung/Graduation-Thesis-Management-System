<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

$testUsers = [
    'admin',
    'TK_CNTT_GV00000001',
    'TBM_CNTT_CNPM_GV00000002',
    'GV00000021',
    'GV00000023',
    '2001210001',
    '2001210002',
];

echo "KIEM TRA DANG NHAP BANG MAT KHAU 'Huit@2026':\n";
foreach ($testUsers as $username) {
    $tk = TaiKhoan::where('TenDangNhap', $username)->first();
    if (!$tk) {
        echo " [FAIL] Khong tim thay user: {$username}\n";
        continue;
    }
    $check = Hash::check('Huit@2026', $tk->MatKhau);
    $status = $check ? "PASS" : "FAIL";
    echo " [{$status}] {$username}: Mat khau hop le={$check}, TrangThai={$tk->TrangThai}, TrangThaiMatKhau={$tk->TrangThaiMatKhau}, SoLanSai={$tk->SoLanDangNhapSai}\n";
}
