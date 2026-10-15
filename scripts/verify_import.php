<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$srv = app(\App\Services\ExcelImportService::class);
$file = __DIR__ . '/../sample_imports/DanhSach_DeTai_Moi_TheoHocPhan_HocKy.xlsx';

echo "========================================================================\n";
echo "    KẾT QUẢ IMPORT FILE ĐỀ TÀI MỚI (PHÂN THEO GV, HỌC PHẦN, HỌC KỲ)     \n";
echo "========================================================================\n";
$res = $srv->importDeTai($file);

echo " -> Tổng số bản ghi trong file: " . $res['total_count'] . "\n";
echo " -> Import / Cập nhật thành công: " . $res['success_count'] . "\n";
echo " -> Thất bại: " . $res['error_count'] . "\n";

if (!empty($res['errors'])) {
    echo "\nDanh sách lỗi:\n";
    foreach ($res['errors'] as $err) {
        echo " - Dòng " . $err['row'] . ": " . $err['reason'] . "\n";
    }
} else {
    echo " -> Tỷ lệ thành công: 100% (Không có lỗi nào!)\n";
}

echo "\n========================================================================\n";
echo "   CHI TIẾT DANH SÁCH ĐỀ TÀI PHÂN THEO GIẢNG VIÊN VÀ HỌC KỲ (HK2627_1)  \n";
echo "========================================================================\n";

$deTais = \App\Models\DeTai::with(['giangVien.boMon', 'hocPhan', 'hocKy'])
    ->orderBy('MaGV')
    ->orderBy('MaDeTai')
    ->get();

$currentGV = null;
foreach ($deTais as $dt) {
    $gv = $dt->giangVien;
    $gvId = $gv ? $gv->MaGV : $dt->MaGV;
    $gvName = $gv ? $gv->HoTen : 'Chưa cập nhật';
    $bmName = $gv && $gv->boMon ? $gv->boMon->TenBoMon : ($gv ? $gv->MaBoMon : '---');

    if ($currentGV !== $gvId) {
        $currentGV = $gvId;
        echo "\n------------------------------------------------------------------------\n";
        echo " 👨‍🏫 GIẢNG VIÊN: {$gvName} [Mã: {$gvId}] | BỘ MÔN: {$bmName}\n";
        echo "------------------------------------------------------------------------\n";
        echo sprintf("%-6s | %-13s | %-42s | %-12s | %s\n", "Mã ĐT", "Học kỳ", "Học phần phụ trách", "Trạng thái", "Tên đề tài");
    }

    $hpName = $dt->hocPhan ? $dt->hocPhan->TenHocPhan : $dt->HocPhan;
    // Cắt ngắn nếu quá dài khi in console
    $shortHp = mb_strimwidth($hpName, 0, 42, '...');
    $shortTitle = mb_strimwidth($dt->TenDeTai, 0, 60, '...');
    echo sprintf("%-6s | %-13s | %-42s | %-12s | %s\n", $dt->MaDeTai, $dt->MaHocKy, $shortHp, $dt->TrangThai, $shortTitle);
}

echo "\n========================================================================\n";
echo "                 TỔNG KẾT THEO HỌC PHẦN BỘ MÔN                         \n";
echo "========================================================================\n";
$stats = \App\Models\DeTai::select('MaHocPhan', \DB::raw('count(*) as total'))
    ->groupBy('MaHocPhan')
    ->orderBy('MaHocPhan')
    ->get();

foreach ($stats as $st) {
    $hp = \App\Models\HocPhan::find($st->MaHocPhan);
    $hpTitle = $hp ? $hp->TenHocPhan : $st->MaHocPhan;
    $bm = $hp ? $hp->MaBoMon : '---';
    echo sprintf(" - [%-4s] %-14s : %-44s -> %2d đề tài\n", $bm, $st->MaHocPhan, $hpTitle, $st->total);
}
echo "========================================================================\n";
