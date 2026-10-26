<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;

$allMaSV = SinhVien::pluck('MaSV')->toArray();
sort($allMaSV);
echo "Count SinhVien: " . count($allMaSV) . "\n";
echo "First 10: " . implode(', ', array_slice($allMaSV, 0, 10)) . "\n";
echo "Last 10: " . implode(', ', array_slice($allMaSV, -10)) . "\n";

// Check if 2001210080, 2001210081, 2001210082 exist or 2001210008, 2001210009, 2001210010
for ($i = 1; $i <= 100; $i++) {
    $code = '200121' . str_pad($i, 4, '0', STR_PAD_LEFT);
    if (!in_array($code, $allMaSV)) {
        echo "Available: {$code}\n";
    }
}
