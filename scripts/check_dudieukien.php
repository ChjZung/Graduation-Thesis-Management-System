<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Support\Facades\DB;

echo "=== DANH SACH SV DU DIEU KIEN COLUMNS ===\n";
$cols = DB::select("SHOW COLUMNS FROM danhsachsvdudieukien");
foreach($cols as $col) {
    echo "{$col->Field} ({$col->Type}) | Null: {$col->Null} | Default: {$col->Default}\n";
}

echo "\n=== DANH SACH SV DU DIEU KIEN HIEN TAI (SAMPLE 5) ===\n";
foreach(DanhSachSVDuDieuKien::take(5)->get() as $dk) {
    echo "ID: {$dk->id} | MaSV: {$dk->MaSV} | MaHocKy: {$dk->MaHocKy} | MaHocPhan: {$dk->MaHocPhan} | TrangThai: {$dk->TrangThai}\n";
}

echo "\n=== KIEM TRA CAC HOC KY CO SV DU DIEU KIEN ===\n";
$hks = DanhSachSVDuDieuKien::select('MaHocKy', DB::raw('count(*) as total'))->groupBy('MaHocKy')->get();
foreach($hks as $h) {
    echo "HocKy: {$h->MaHocKy} | Count: {$h->total}\n";
}
