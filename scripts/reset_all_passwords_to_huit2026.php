<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=================================================================\n";
echo "TIEN HANH RESET TOAN BO MAT KHAU TAI KHOAN VE 'Huit@2026'\n";
echo "=================================================================\n";

$currentDb = DB::connection()->getDatabaseName();
echo "Database: {$currentDb}\n\n";

$newPassword = 'Huit@2026';
$hashedPassword = Hash::make($newPassword);
$now = now();

$accounts = TaiKhoan::all();
$total = $accounts->count();
echo "Tim thay tong cong: {$total} tai khoan trong he thong.\n\n";

$updatedCount = 0;
foreach ($accounts as $tk) {
    $tk->MatKhau = $hashedPassword;
    $tk->TrangThai = 1; // Unlock neu dang bi khoa
    $tk->SoLanDangNhapSai = 0; // Reset dem sai
    $tk->NgayKhoa = null;
    $tk->BatBuocDoiMatKhau = false; // Cho phep dang nhap truc tiep
    $tk->TrangThaiMatKhau = 'ACTIVE';
    $tk->NgayDoiMatKhau = $now;
    $tk->save();

    $updatedCount++;
}

echo "Da reset thanh cong: {$updatedCount} / {$total} tai khoan!\n";
echo "Tat ca tai khoan gio day deu co:\n";
echo " - Mat khau: Huit@2026\n";
echo " - Trang thai: Kich hoat (ACTIVE)\n";
echo " - So lan dang nhap sai: 0 (Da mo khoa)\n";
echo " - Bat buoc doi mat khau: Khong (Dang nhap thang vao he thong)\n";
echo "=================================================================\n";
