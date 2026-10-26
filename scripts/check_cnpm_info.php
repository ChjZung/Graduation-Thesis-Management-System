<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Nganh;
use App\Models\BoMon;
use App\Models\Lop;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\DanhSachSVDuDieuKien;

echo "=== NGANH ===\n";
foreach(Nganh::all() as $n) {
    echo "{$n->MaNganh} | {$n->TenNganh} | MaBoMon: {$n->MaBoMon}\n";
}

echo "\n=== LOP ===\n";
foreach(Lop::all() as $l) {
    echo "{$l->MaLop} | {$l->TenLop} | MaNganh: {$l->MaNganh} | MaKhoa: {$l->MaKhoa}\n";
}

echo "\n=== SINH VIEN CNPM HIEN TAI ===\n";
$svList = SinhVien::where('MaNganh', '7480103')->orWhere('MaLop', 'like', '%KPM%')->get();
foreach($svList as $sv) {
    echo "{$sv->MaSV} | {$sv->HoTen} | Lop: {$sv->MaLop} | Nganh: {$sv->MaNganh} | MaTK: {$sv->MaTK}\n";
}

echo "\n=== MAX MaSV TRONG HE THONG ===\n";
$allSv = SinhVien::orderBy('MaSV', 'desc')->take(10)->get();
foreach($allSv as $s) {
    echo "{$s->MaSV} | {$s->HoTen} | {$s->MaLop} | {$s->MaNganh}\n";
}

echo "\n=== HOC KY HIEN HANH & HOC PHAN CNPM ===\n";
$hk = HocKy::where('TrangThai', 1)->first() ?? HocKy::first();
echo "HocKy: {$hk?->MaHocKy} - {$hk?->TenHocKy}\n";
$hps = HocPhan::where('MaBoMon', 'CNPM')->get();
foreach($hps as $hp) {
    echo "{$hp->MaHocPhan} | {$hp->TenHocPhan} | {$hp->MaBoMon}\n";
}
