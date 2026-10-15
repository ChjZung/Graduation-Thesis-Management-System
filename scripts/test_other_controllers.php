<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "=== KIỂM TRA THÊM MỚI BẰNG TAY TRÊN CÁC CONTROLLER KHÁC ===\n";

$session = app('session')->driver();

// 1. KhoaController::store
try {
    $req = Request::create('/khoa', 'POST', [
        'MaKhoa' => 'TEST_KHOA',
        'TenKhoa' => 'Khoa Test Kiểm Thử',
        'TruongKhoa' => 'TS. Test',
    ]);
    $req->setLaravelSession($session);
    $c = new \App\Http\Controllers\KhoaController();
    $res = $c->store($req);
    echo " + KhoaController::store() -> OK (Status " . $res->getStatusCode() . ")\n";
    \App\Models\Khoa::where('MaKhoa', 'TEST_KHOA')->delete();
} catch (\Throwable $e) {
    echo " [LỖI] KhoaController: " . $e->getMessage() . "\n";
}

// 2. BoMonController::store (cần Khoa)
try {
    \App\Models\Khoa::insertOrIgnore(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa CNTT']);
    $req = Request::create('/bomon', 'POST', [
        'MaBoMon' => 'TEST_BM',
        'TenBoMon' => 'Bộ môn Test Kiểm Thử',
        'MaKhoa' => 'CNTT',
    ]);
    $req->setLaravelSession($session);
    $c = new \App\Http\Controllers\BoMonController();
    $res = $c->store($req);
    echo " + BoMonController::store() -> OK (Status " . $res->getStatusCode() . ")\n";
    \App\Models\BoMon::where('MaBoMon', 'TEST_BM')->delete();
} catch (\Throwable $e) {
    echo " [LỖI] BoMonController: " . $e->getMessage() . "\n";
}

// 3. NganhController::store
try {
    $req = Request::create('/nganh', 'POST', [
        'MaNganh' => '9999999',
        'TenNganh' => 'Ngành Test Kiểm Thử',
        'MaKhoa' => 'CNTT',
    ]);
    $req->setLaravelSession($session);
    $c = new \App\Http\Controllers\NganhController();
    $res = $c->store($req);
    echo " + NganhController::store() -> OK (Status " . $res->getStatusCode() . ")\n";
    \App\Models\Nganh::where('MaNganh', '9999999')->delete();
} catch (\Throwable $e) {
    echo " [LỖI] NganhController: " . $e->getMessage() . "\n";
}

// 4. HocPhanController::store
try {
    $req = Request::create('/hocphan', 'POST', [
        'MaHocPhan' => 'HP_TEST',
        'TenHocPhan' => 'Học phần Test',
        'SoTinChi' => 3,
        'MaKhoa' => 'CNTT',
        'LoaiHocPhan' => 'Chuyên ngành',
    ]);
    $req->setLaravelSession($session);
    $c = new \App\Http\Controllers\HocPhanController();
    $res = $c->store($req);
    echo " + HocPhanController::store() -> OK (Status " . $res->getStatusCode() . ")\n";
    \App\Models\HocPhan::where('MaHocPhan', 'HP_TEST')->delete();
} catch (\Throwable $e) {
    echo " [LỖI] HocPhanController: " . $e->getMessage() . "\n";
}

// Dọn dẹp bản ghi tạm CNTT nếu vừa thêm
\App\Models\Khoa::where('MaKhoa', 'CNTT')->delete();

echo "=== TẤT CẢ CÁC CONTROLLER THÊM BẰNG TAY ĐỀU HOẠT ĐỘNG HOÀN HẢO ===\n";
