<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hps = App\Models\HocPhan::with(['hocPhanHocKies' => fn($q) => $q->where('TrangThai', 'Đang mở')])->first();
$json = json_encode($hps);
echo "JSON has hoc_phan_hoc_kies: " . (str_contains($json, 'hoc_phan_hoc_kies') ? 'YES' : 'NO') . "\n";
echo "JSON has hocPhanHocKies: " . (str_contains($json, 'hocPhanHocKies') ? 'YES' : 'NO') . "\n";
echo "JSON snippet: " . substr($json, 0, 300) . "...\n";
