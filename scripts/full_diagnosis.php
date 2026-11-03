<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\KhoiBaoCaoTienDo;

echo "=== A. QUEUE WORKER & CONNECTION ===\n";
echo "default queue: " . config('queue.default') . "\n";
echo "ai connection driver: " . config('queue.connections.ai.driver') . "\n";
echo "ai connection queue: " . config('queue.connections.ai.queue') . "\n";
echo "ai connection retry_after: " . config('queue.connections.ai.retry_after') . "\n";
echo "jobs table count: " . DB::table('jobs')->count() . "\n";
echo "failed_jobs table count: " . DB::table('failed_jobs')->count() . "\n";

echo "\n--- All Jobs in 'jobs' table ---\n";
foreach (DB::table('jobs')->get() as $job) {
    echo "ID: {$job->id} | Queue: {$job->queue} | Attempts: {$job->attempts} | ReservedAt: {$job->reserved_at} | AvailableAt: {$job->available_at} (" . date('Y-m-d H:i:s', $job->available_at) . ") | CreatedAt: {$job->created_at} (" . date('Y-m-d H:i:s', $job->created_at) . ")\n";
    $p = json_decode($job->payload, true);
    echo "  Command: " . ($p['displayName'] ?? '') . "\n";
    if (isset($p['data']['command'])) {
        $cmd = unserialize($p['data']['command']);
        if ($cmd instanceof \App\Jobs\AnalyzeReportVersion) {
            echo "  AnalyzeReportVersion reportId={$cmd->reportId}, token={$cmd->token}\n";
        }
    }
}

echo "\n=== B. STATUS OF REPORT KHOI_D51_V3_de95fa ===\n";
$r3 = KhoiBaoCaoTienDo::find('KHOI_D51_V3_de95fa');
if ($r3) {
    echo "MaKhoi: {$r3->MaKhoi}\n";
    echo "TenFile: {$r3->TenFile}\n";
    echo "DuongDanFile: {$r3->DuongDanFile}\n";
    echo "LanNop: {$r3->LanNop}\n";
    echo "TrangThai: {$r3->TrangThai}\n";
    echo "TomTatAI: " . ($r3->TomTatAI ?: '(null)') . "\n";
    $data = $r3->TomTatJson;
    if (is_string($data)) $data = json_decode($data, true);
    echo "TomTatJson keys: " . implode(', ', array_keys($data ?: [])) . "\n";
    echo "_processing: " . json_encode($data['_processing'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "KHOI_D51_V3_de95fa not found!\n";
}

echo "\n=== D. AI CONFIGURATION ===\n";
echo "GEMINI_API_KEY set? " . (!empty(env('GEMINI_API_KEY')) ? 'YES (length ' . strlen(env('GEMINI_API_KEY')) . ')' : 'NO') . "\n";
echo "config('services.gemini.api_key') set? " . (!empty(config('services.gemini.api_key')) ? 'YES' : 'NO') . "\n";
echo "config('services.gemini.model'): " . config('services.gemini.model') . "\n";
echo "env('GEMINI_MODEL'): " . env('GEMINI_MODEL') . "\n";
echo "config('ai_processing.pdf_engine'): " . config('ai_processing.pdf_engine') . "\n";

echo "\n=== E. FILE CHECK FOR V3 ===\n";
if ($r3) {
    $storageRoot = storage_path('app/public');
    $fullPath = $storageRoot . '/' . $r3->DuongDanFile;
    echo "Expected path: {$fullPath}\n";
    echo "File exists? " . (file_exists($fullPath) ? 'YES (size ' . filesize($fullPath) . ' bytes)' : 'NO') . "\n";
    
    // Check python text extractor
    $python = env('PYTHON_PATH') ?: (file_exists('C:\\laragon\\bin\\python\\python-3.10\\python.exe') ? 'C:\\laragon\\bin\\python\\python-3.10\\python.exe' : 'python');
    echo "Python executable: {$python}\n";
    $pyCheck = shell_exec("{$python} --version 2>&1");
    echo "Python version: {$pyCheck}\n";
}

echo "\n=== G. OS LOCKS AND MUTEX ===\n";
$procService = app(\App\Services\AiReportProcessingService::class);
echo "workerRunning(): " . ($procService->workerRunning() ? 'TRUE' : 'FALSE') . "\n";
$lockFile = storage_path('app/private/ai-worker.lock');
echo "Lock file path: {$lockFile}\n";
if (file_exists($lockFile)) {
    $h = @fopen($lockFile, 'r');
    if ($h) {
        $canLock = flock($h, LOCK_EX | LOCK_NB);
        echo "Can acquire exclusive lock on ai-worker.lock? " . ($canLock ? 'YES (worker NOT actually running!)' : 'NO (held by another process!)') . "\n";
        if ($canLock) flock($h, LOCK_UN);
        fclose($h);
    }
}

// Check cache lock
$cacheLock = \Illuminate\Support\Facades\Cache::store('file')->lock('ai-report:KHOI_D51_V3_de95fa', 420);
echo "Can acquire cache lock on ai-report:KHOI_D51_V3_de95fa? " . ($cacheLock->get() ? 'YES' : 'NO') . "\n";
if ($cacheLock->get()) {
    $cacheLock->release();
}
