<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;

$existing = SinhVien::pluck('MaSV')->toArray();
echo "Existing students count: " . count($existing) . "\n";
for ($i = 76; $i <= 90; $i++) {
    $id = '200121' . str_pad($i, 4, '0', STR_PAD_LEFT);
    echo "{$id}: " . (in_array($id, $existing) ? 'EXISTS' : 'FREE') . "\n";
}
