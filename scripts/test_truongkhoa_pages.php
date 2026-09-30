<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING TRƯỞNG KHOA ROUTES & VIEWS ===\n";

$user = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV001')->first();
if (!$user) {
    die("ERROR: Account TK_CNTT_GV001 not found!\n");
}
Auth::login($user);
echo "1. Logged in as: {$user->TenDangNhap} (MaTK: {$user->MaTK})\n";

$tests = [
    'Dashboard' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\DashboardController();
        $r = Request::create('/truongkhoa', 'GET');
        $view = $c->index($r);
        return $view->render();
    },
    'Duyet De Tai Index (Danh sach)' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\DuyetDeTaiController();
        $r = Request::create('/truongkhoa/duyet-detai', 'GET', ['view' => 'danhsach']);
        $view = $c->index($r);
        return $view->render();
    },
    'Duyet De Tai Index (Thong ke tab)' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\DuyetDeTaiController();
        $r = Request::create('/truongkhoa/duyet-detai', 'GET', ['view' => 'thongke']);
        $view = $c->index($r);
        return $view->render();
    },
    'Duyet De Tai Show (DT_TK_01)' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\DuyetDeTaiController();
        $view = $c->show('DT_TK_01');
        return $view->render();
    },
    'Theo Doi Index (Tien do)' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\TheoDoiController();
        $r = Request::create('/truongkhoa/theo-doi', 'GET', ['tab' => 'tien_do']);
        $view = $c->index($r);
        return $view->render();
    },
    'Theo Doi Index (Bao ve)' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\TheoDoiController();
        $r = Request::create('/truongkhoa/theo-doi', 'GET', ['tab' => 'bao_ve']);
        $view = $c->index($r);
        return $view->render();
    },
    'Ke Hoach Index' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\KeHoachController();
        $r = Request::create('/truongkhoa/ke-hoach', 'GET');
        $view = $c->index($r);
        return $view->render();
    },
    'Ket Qua Index' => function() {
        $c = new \App\Http\Controllers\TruongKhoa\KetQuaController();
        $r = Request::create('/truongkhoa/ket-qua', 'GET');
        $view = $c->index($r);
        return $view->render();
    },
];

$allPassed = true;
foreach ($tests as $name => $fn) {
    try {
        $output = $fn();
        if (strlen($output) > 500) {
            echo "✓ [$name]: Rendered successfully (" . strlen($output) . " bytes)\n";
        } else {
            echo "⚠ [$name]: Output suspiciously short (" . strlen($output) . " bytes)\n";
        }
    } catch (\Throwable $e) {
        $allPassed = false;
        echo "✗ [$name]: ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

if ($allPassed) {
    echo "\n>>> ALL TRƯỞNG KHOA PAGES RENDERED WITH 0 ERRORS! <<<\n";
} else {
    echo "\n>>> SOME PAGES HAD ERRORS! <<<\n";
}
