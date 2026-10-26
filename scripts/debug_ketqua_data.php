<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

$userTk = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000001')->first();
Auth::login($userTk);

$request = Request::create('/truongkhoa/ket-qua', 'GET');
$controller = app()->make(\App\Http\Controllers\TruongKhoa\KetQuaController::class);
$response = $controller->index($request);
$data = $response->getData();

echo "viewMode: " . $data['viewMode'] . "\n";
echo "hoiDongs count: " . $data['hoiDongs']->count() . "\n";
foreach ($data['hoiDongs'] as $hd) {
    echo "HoiDong: {$hd->MaHoiDong} - {$hd->TenHoiDong}\n";
    $td = $data['tienDoHoiDongs'][$hd->MaHoiDong] ?? [];
    echo "  duPhieu: " . ($td['hoiDongDuPhieu'] ?? false ? 'true' : 'false') . "\n";
    echo "  soDeTai: " . ($td['tongSoDeTai'] ?? 0) . "\n";
    echo "  soDeTaiDu: " . ($td['soDeTaiDuPhieu'] ?? 0) . "\n";
    $coHoSo = $hd->hoSoBaoVes->isNotEmpty();
    $daCongBo = false;
    if ($coHoSo) {
        $daCongBo = $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
    }
    echo "  coHoSo: " . ($coHoSo ? 'true' : 'false') . " | daCongBo: " . ($daCongBo ? 'true' : 'false') . "\n";
}
