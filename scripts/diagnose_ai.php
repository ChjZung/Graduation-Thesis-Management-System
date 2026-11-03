<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\DotNopBaoCao;
use App\Models\KhoiBaoCaoTienDo;

echo "=== 1. CHECK QUEUE TABLES ===\n";
echo "jobs count: " . DB::table('jobs')->count() . "\n";
echo "failed_jobs count: " . DB::table('failed_jobs')->count() . "\n";

$jobs = DB::table('jobs')->get();
foreach ($jobs as $j) {
    echo "Job ID: {$j->id}, Queue: {$j->queue}, Attempts: {$j->attempts}, ReservedAt: {$j->reserved_at}, AvailableAt: {$j->available_at}, CreatedAt: {$j->created_at}\n";
    $payload = json_decode($j->payload, true);
    echo "  DisplayName: " . ($payload['displayName'] ?? 'N/A') . "\n";
    echo "  Data: " . json_encode($payload['data'] ?? []) . "\n";
}

$failed = DB::table('failed_jobs')->get();
foreach ($failed as $f) {
    echo "Failed Job ID: {$f->id}, Connection: {$f->connection}, Queue: {$f->queue}, FailedAt: {$f->failed_at}\n";
    echo "  Exception: " . substr($f->exception, 0, 300) . "...\n";
}

echo "\n=== 2. CHECK DOT NOP 51 AND KHOI BAO CAO ===\n";
$dotNop = DotNopBaoCao::find(51);
if ($dotNop) {
    echo "DotNop 51 found: MaDotNop={$dotNop->MaDotNop}, Ten={$dotNop->TenDotNop}, MaDeTai={$dotNop->MaDeTai}\n";
} else {
    echo "DotNop 51 not found directly by ID. Searching all dot nop with id or similar...\n";
}

$reports = KhoiBaoCaoTienDo::where('MaDotNop', 51)->orWhere('TenFile', 'like', '%01_Quan ly do an_v4%')->get();
echo "Found " . $reports->count() . " reports matching MaDotNop=51 or TenFile like 01_Quan ly do an_v4:\n";
foreach ($reports as $r) {
    echo "Report: MaKhoi={$r->MaKhoi}, MaBaoCao={$r->MaBaoCao}, LanNop={$r->LanNop}, TenFile={$r->TenFile}, DuongDan={$r->DuongDanFile}, TrangThai={$r->TrangThai}\n";
    $data = $r->TomTatJson;
    if (is_string($data)) $data = json_decode($data, true);
    echo "  Processing State: " . json_encode($data['_processing'] ?? $data['data']['_processing'] ?? 'NO_PROCESSING_KEY') . "\n";
    echo "  TomTatAI preview: " . substr($r->TomTatAI ?? '', 0, 100) . "\n";
}

echo "\n=== 3. CHECK WORKER LOCK & RUNNING STATUS ===\n";
$procService = app(\App\Services\AiReportProcessingService::class);
echo "workerRunning() returned: " . ($procService->workerRunning() ? 'TRUE (RUNNING)' : 'FALSE (NOT RUNNING)') . "\n";
$lockFile = storage_path('app/private/ai-worker.lock');
echo "ai-worker.lock exists: " . (file_exists($lockFile) ? 'YES' : 'NO') . "\n";

echo "\n=== 4. CHECK RECENT KHOI BAO CAO WITH PROCESSING STATE ===\n";
$allReports = KhoiBaoCaoTienDo::orderBy('updated_at', 'desc')->take(5)->get();
foreach ($allReports as $r) {
    $data = $r->TomTatJson;
    if (is_string($data)) $data = json_decode($data, true);
    $proc = $data['_processing'] ?? $data['data']['_processing'] ?? null;
    echo "MaKhoi={$r->MaKhoi}, LanNop={$r->LanNop}, File={$r->TenFile}, HasProcessing=" . ($proc ? 'YES' : 'NO') . "\n";
    if ($proc) {
        echo "  Processing details: " . json_encode($proc) . "\n";
    }
}
