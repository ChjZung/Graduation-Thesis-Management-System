<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeTai;

$cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM DeTai");
foreach($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}

$deTais = DeTai::with(['hocPhanRef', 'giangVien.boMon', 'nhom'])->take(10)->get();
foreach($deTais as $dt) {
    echo "{$dt->MaDeTai} | {$dt->TenDeTai} | HP: {$dt->MaHocPhan} | GV: {$dt->giangVien?->HoTen} ({$dt->giangVien?->boMon?->TenBoMon}) | TrangThai: {$dt->TrangThai} | Nhom: " . ($dt->nhom ? $dt->nhom->TenNhom : 'Chua co') . "\n";
}
