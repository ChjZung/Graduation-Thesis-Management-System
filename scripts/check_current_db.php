<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\DB;

$admins = TaiKhoan::where('MaVaiTro', 'VT01')->get(['MaTK', 'TenDangNhap', 'MaVaiTro', 'TrangThai']);
echo "ADMIN ACCOUNTS:\n";
foreach ($admins as $a) {
    echo "- MaTK: {$a->MaTK}, TenDangNhap: {$a->TenDangNhap}, VaiTro: {$a->MaVaiTro}\n";
}

$gvus = DB::table('giaovu')->get();
echo "\nGIAOVU TABLE:\n" . json_encode($gvus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$vts = DB::table('vaitro')->get();
echo "\nVAITRO TABLE:\n" . json_encode($vts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
