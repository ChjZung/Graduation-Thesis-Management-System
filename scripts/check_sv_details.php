<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;

foreach(['2001210006', '2001210007', '2001210008', '2001210009', '2001210010', '2001210043'] as $m) {
    $sv = SinhVien::find($m);
    if ($sv) {
        echo "{$sv->MaSV} | {$sv->HoTen} | {$sv->MaLop} | {$sv->MaNganh}\n";
    }
}
