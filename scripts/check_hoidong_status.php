<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\PhieuChamDiem;
use App\Services\GradeCalculationService;

$gradeService = app()->make(GradeCalculationService::class);

echo "=== DANH SACH HOI DONG & TIEN DO CHAM DIEM ===\n";
$hoiDongs = HoiDong::with(['thanhViens', 'hoSoBaoVes'])->get();

foreach ($hoiDongs as $hd) {
    echo "Hội đồng: [{$hd->MaHoiDong}] {$hd->TenHoiDong}\n";
    echo "  - Ngày bảo vệ: {$hd->NgayBaoVe} | Địa điểm: {$hd->DiaDiem}\n";
    echo "  - Trạng thái công bố HĐ (Thành lập): {$hd->TrangThaiCongBo}\n";
    echo "  - Số thành viên: " . $hd->thanhViens->count() . "\n";
    echo "  - Số hồ sơ / đề tài: " . $hd->hoSoBaoVes->count() . "\n";

    $tienDo = $gradeService->kiemTraTienDoChamHoiDong($hd->MaHoiDong);
    echo "  - Đủ phiếu chấm (hoiDongDuPhieu): " . ($tienDo['hoiDongDuPhieu'] ? 'YES' : 'NO') . "\n";
    echo "  - Số đề tài đủ phiếu: {$tienDo['soDeTaiDuPhieu']} / {$tienDo['tongSoDeTai']}\n";

    // Kiểm tra xem đã công bố chưa
    $coHoSo = $hd->hoSoBaoVes->isNotEmpty();
    $daCongBo = false;
    if ($coHoSo) {
        $daCongBo = $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
    }
    echo "  - Đã công bố kết quả (daCongBo): " . ($daCongBo ? 'YES' : 'NO') . "\n";

    foreach ($hd->hoSoBaoVes as $hs) {
        echo "    * Hồ sơ [{$hs->MaHoSo}] (Nhóm {$hs->MaNhom}): TrangThai={$hs->TrangThai}, TrangThaiKetQua={$hs->TrangThaiKetQua}, TrangThaiChinhSua={$hs->TrangThaiChinhSuaSauBaoVe}\n";
        echo "      FileBanChinhSua: " . ($hs->FileBanChinhSua ?? 'NULL') . "\n";
    }
    echo "---------------------------------------------------\n";
}
