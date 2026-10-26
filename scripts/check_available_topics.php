<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeTai;
use App\Models\HocKy;

echo "=== HOC KY ACTIVE ===\n";
foreach(HocKy::all() as $hk) {
    echo "{$hk->MaHocKy} | {$hk->TenHocKy} | TrangThai: {$hk->TrangThai}\n";
}

echo "\n=== DE TAI CNPM ===\n";
$deTais = DeTai::where('MaBoMon', 'CNPM')->orWhere('MaNganh', '7480103')->get();
foreach($deTais as $dt) {
    echo "{$dt->MaDeTai} | {$dt->TenDeTai} | TrangThai: {$dt->TrangThai} | Nhom: " . ($dt->nhom ? $dt->nhom->TenNhom : 'Chua co nhom') . "\n";
}
