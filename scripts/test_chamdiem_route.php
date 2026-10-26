<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\HoiDong;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TEST TAIKHOAN METHODS ===\n";
$users = ['admin', 'TK_CNTT_GV00000001', 'TBM_CNTT_CNPM_GV00000002', 'GV00000021'];

foreach ($users as $u) {
    $tk = TaiKhoan::where('TenDangNhap', $u)->first();
    if ($tk) {
        $tk->loadMissing('vaiTro');
        echo "User: {$u} (VaiTro: {$tk->vaiTro?->TenVaiTro})\n";
        echo "  - isTruongKhoa: " . ($tk->isTruongKhoa() ? 'true' : 'false') . "\n";
        echo "  - isGiaoVu: " . ($tk->isGiaoVu() ? 'true' : 'false') . "\n";
        echo "  - isTruongBoMon: " . ($tk->isTruongBoMon() ? 'true' : 'false') . "\n";
        echo "  - isGiangVien: " . ($tk->isGiangVien() ? 'true' : 'false') . "\n";
        echo "  - isSinhVien: " . ($tk->isSinhVien() ? 'true' : 'false') . "\n";
        echo "  - isAdmin: " . ($tk->isAdmin() ? 'true' : 'false') . "\n";
    }
}

echo "\n=== TEST CHAM DIEM CONTROLLER WITH MaHoiDong=HD01 ===\n";
$userGv = TaiKhoan::where('TenDangNhap', 'GV00000021')->first();
Auth::login($userGv);

$request = Request::create('/giangvien/chamdiem', 'GET', ['MaHoiDong' => 'HD01']);
try {
    $controller = app()->make(\App\Http\Controllers\GiangVien\ChamDiemController::class);
    $response = $controller->index($request);
    echo "Controller index() executed successfully! Response type: " . get_class($response) . "\n";
    if ($response instanceof \Illuminate\View\View) {
        echo "View name: " . $response->getName() . "\n";
        $rendered = $response->render();
        echo "View rendered successfully without exception! Length: " . strlen($rendered) . " bytes\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
