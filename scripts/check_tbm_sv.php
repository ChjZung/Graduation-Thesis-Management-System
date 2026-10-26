<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\TaiKhoan;

$sv = SinhVien::with('lop')->find('2001210080');
echo "Sinh Vien: {$sv->MaSV} - {$sv->HoTen}\n";
echo "Lop: {$sv->MaLop}\n";
echo "Nganh: {$sv->MaNganh}\n";

$bm = BoMon::find('CNPM');
echo "\nBo Mon CNPM:\n";
echo "Ten Bo Mon: {$bm?->TenBoMon}\n";
echo "Truong Bo Mon (field): {$bm?->TruongBoMon}\n";

echo "\nTai Khoan Truong Bo Mon trong he thong:\n";
$tbmAccounts = TaiKhoan::where('TenDangNhap', 'like', '%CNPM%')
    ->orWhere('MaVaiTro', 'VT04')
    ->get();

foreach ($tbmAccounts as $tk) {
    $tk->loadMissing('vaiTro');
    $gv = GiangVien::where('MaTK', $tk->MaTK)->orWhere('MaGV', $tk->MaGV)->orWhere('MaGV', $tk->TenDangNhap)->first();
    echo "TenDangNhap: {$tk->TenDangNhap} | MaVaiTro: {$tk->vaiTro?->TenVaiTro} | HoTen: {$gv?->HoTen} | BoMon: {$gv?->MaBoMon} | MatKhau: Huit@2026\n";
}
