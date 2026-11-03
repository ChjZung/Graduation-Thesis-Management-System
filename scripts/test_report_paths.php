<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;

$procService = app(AiReportProcessingService::class);
$aiService = app(AiSummaryService::class);

echo "Testing KHOI_D51_V3_de95fa path and fingerprint:\n";
$r3 = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->find('KHOI_D51_V3_de95fa');
echo "Path: " . $procService->path($r3) . "\n";
echo "Fingerprint: " . $procService->fingerprint($r3) . "\n";

$savedFingerprint = $procService->payload($r3)['_processing']['fingerprint'] ?? 'N/A';
echo "Saved Fingerprint: {$savedFingerprint}\n";
echo "Fingerprint matches? " . ($procService->fingerprint($r3) === $savedFingerprint ? 'YES' : 'NO') . "\n";

echo "\nTesting KHOI_D50_V1_c1e185 path:\n";
$r50 = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->find('KHOI_D50_V1_c1e185');
if ($r50) {
    echo "Path: " . $procService->path($r50) . "\n";
    echo "File exists? " . (file_exists($procService->path($r50)) ? 'YES' : 'NO') . "\n";
    echo "Extension: " . pathinfo($procService->path($r50), PATHINFO_EXTENSION) . "\n";
}
