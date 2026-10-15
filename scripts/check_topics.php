<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dts = App\Models\DeTai::orderBy('updated_at', 'desc')->take(20)->get();
foreach ($dts as $d) {
    echo sprintf("%-6s | %-30s | %s\n", $d->MaDeTai, $d->TrangThai, $d->TenDeTai);
}
