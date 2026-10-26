<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HoiDong;

$updated = HoiDong::where('MaHoiDong', 'HD01')->update([
    'MaKhoa' => 'CNTT',
    'MaNganh' => '7480103',
]);
echo "Updated HD01: {$updated}\n";
