<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HoiDong;

$maKhoa = 'CNTT';
$q = HoiDong::where(function ($q) use ($maKhoa) {
    $q->where('MaKhoa', $maKhoa)
      ->orWhereNull('MaKhoa')
      ->orWhere('MaKhoa', '')
      ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
      ->orWhereHas('thanhViens.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
})->get();

echo "Count with robust filter: " . $q->count() . "\n";
foreach ($q as $hd) {
    echo "Found: {$hd->MaHoiDong} - {$hd->TenHoiDong}\n";
}
