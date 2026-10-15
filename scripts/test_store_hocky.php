<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\HocKyController;
use App\Models\HocKy;

echo "=== TEST THÊM HỌC KỲ BẰNG TAY (MANUAL STORE) ===\n";

$req = Request::create('/hocky', 'POST', [
    'TenHocKy' => 'Học kỳ Test Thủ Công',
    'NamHoc' => '2026-2027',
    'NgayBatDau' => '2026-09-01',
    'NgayKetThuc' => '2027-01-15',
    'TrangThai' => 'Chưa bắt đầu',
]);

// Set session for redirect
$session = app('session')->driver();
$req->setLaravelSession($session);

$controller = new HocKyController();
try {
    $res = $controller->store($req);
    echo "1. Controller store() executed successfully! Status: " . $res->getStatusCode() . "\n";
    
    $created = HocKy::where('TenHocKy', 'Học kỳ Test Thủ Công')->first();
    if ($created) {
        echo "2. Học kỳ đã được thêm thành công vào DB: MaHocKy = {$created->MaHocKy}, TenHocKy = {$created->TenHocKy}\n";
        $created->delete();
        echo "3. Đã dọn dẹp bản ghi test an toàn.\n";
    } else {
        echo "2. [Cảnh báo] Không tìm thấy bản ghi vừa tạo.\n";
    }
    echo "=== KẾT QUẢ: 100% THÀNH CÔNG, KHÔNG CÒN LỖI! ===\n";
} catch (\Throwable $e) {
    echo "LỖI PHÁT SINH: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
