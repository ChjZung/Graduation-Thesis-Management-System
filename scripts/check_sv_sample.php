<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DanhSachSVDuDieuKien;
use App\Models\SinhVien;
use App\Models\TaiKhoan;

echo "=== DANH SACH SV DU DIEU KIEN SAMPLES ===\n";
foreach(DanhSachSVDuDieuKien::take(5)->get() as $dk) {
    echo "MaDSDK: '{$dk->MaDSDK}' | MaSV: {$dk->MaSV} | MaHocKy: {$dk->MaHocKy} | TrangThai: {$dk->TrangThai} | DieuKien: {$dk->DieuKien}\n";
}

echo "\n=== SINH VIEN SAMPLE (2001210006) ===\n";
$sv = SinhVien::find('2001210006');
if ($sv) {
    print_r($sv->toArray());
}

$tk = TaiKhoan::find('2001210006');
if ($tk) {
    echo "MaTK: {$tk->MaTK} | TenDangNhap: {$tk->TenDangNhap} | MaVaiTro: {$tk->MaVaiTro}\n";
}
