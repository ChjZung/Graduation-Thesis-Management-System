<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HoiDong;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\Khoa;

$hd = HoiDong::find('HD01');
echo "HD01:\n";
print_r($hd ? $hd->toArray() : 'NULL');

$userTk = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000001')->first();
$gv = GiangVien::where('MaTK', $userTk?->MaTK)->orWhere('MaGV', 'GV00000001')->first();
echo "GV: " . ($gv ? $gv->HoTen : 'null') . "\n";
echo "GV BoMon: " . ($gv?->MaBoMon) . "\n";
echo "GV Khoa: " . ($gv?->boMon?->MaKhoa) . "\n";

$khoas = Khoa::all();
foreach ($khoas as $k) {
    echo "Khoa: {$k->MaKhoa} - {$k->TenKhoa} | TruongKhoa: {$k->TruongKhoa}\n";
}
