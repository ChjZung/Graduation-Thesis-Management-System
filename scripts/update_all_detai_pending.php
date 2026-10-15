<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeTai;
use Illuminate\Support\Facades\DB;

echo "--- CẬP NHẬT TẤT CẢ ĐỀ TÀI VỀ TRẠNG THÁI 'Chờ duyệt cấp Bộ môn' ---\n";

$updated = DeTai::query()->update([
    'TrangThai'       => 'Chờ duyệt cấp Bộ môn',
    'NgayCongBo'      => null,
    'NgayDuyet'       => null,
    'NgayDuyetBM'     => null,
    'NgayDuyetKhoa'   => null,
    'NguoiDuyetBM'    => null,
    'NguoiDuyetKhoa'  => null,
]);

echo "Đã cập nhật {$updated} đề tài về trạng thái 'Chờ duyệt cấp Bộ môn'!\n";

$dts = DeTai::orderBy('MaDeTai')->get();
foreach ($dts as $d) {
    echo sprintf("%-6s | %-25s | %s\n", $d->MaDeTai, $d->TrangThai, mb_strimwidth($d->TenDeTai, 0, 50, '...'));
}
