<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HoiDong;

$allHd = HoiDong::all();
echo "Total HoiDong: " . $allHd->count() . "\n";
foreach ($allHd as $h) {
    echo "{$h->MaHoiDong} | {$h->TenHoiDong} | MaKhoa: '{$h->MaKhoa}' | MaHocKy: '{$h->MaHocKy}'\n";
}
