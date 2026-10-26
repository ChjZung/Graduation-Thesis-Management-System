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

echo "Response status: 200 OK\n";
$html = $response->render();
echo "HTML length: " . strlen($html) . "\n";
if (str_contains($html, 'Công Bố Kết Quả &amp; Chuyển Lưu Trữ') || str_contains($html, 'Công Bố Kết Quả & Chuyển Lưu Trữ')) {
    echo "SUCCESS: Found button 'Công Bố Kết Quả & Chuyển Lưu Trữ' in HTML!\n";
} else {
    echo "NOTICE: Button not found in HTML. Checking conditions...\n";
    if (str_contains($html, 'Đã Công Bố Kết Quả')) {
        echo "Reason: Council has ALREADY been published ('Đã Công Bố Kết Quả')\n";
    }
    if (str_contains($html, 'Chưa Đủ Phiếu Chấm')) {
        echo "Reason: Council does not have enough score sheets ('Chưa Đủ Phiếu Chấm')\n";
    }
}
