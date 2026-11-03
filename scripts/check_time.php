<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== TIME COMPARISON ===\n";
echo "Real PHP time(): " . time() . " (" . date('Y-m-d H:i:s', time()) . ")\n";
echo "Carbon::now(): " . Carbon::now()->toDateTimeString() . " (timestamp: " . Carbon::now()->timestamp . ")\n";

$job71 = DB::table('jobs')->where('id', 71)->first();
if ($job71) {
    echo "Job 71 created_at: {$job71->created_at} (" . date('Y-m-d H:i:s', $job71->created_at) . ")\n";
    echo "Job 71 available_at: {$job71->available_at} (" . date('Y-m-d H:i:s', $job71->available_at) . ")\n";
    echo "Difference (available_at - real_time): " . ($job71->available_at - time()) . " seconds (" . round(($job71->available_at - time()) / 3600, 2) . " hours)\n";
    echo "Difference (available_at - Carbon::now): " . ($job71->available_at - Carbon::now()->timestamp) . " seconds\n";
}

$job72 = DB::table('jobs')->where('id', 72)->first();
if ($job72) {
    echo "\nJob 72 created_at: {$job72->created_at} (" . date('Y-m-d H:i:s', $job72->created_at) . ")\n";
    echo "Job 72 available_at: {$job72->available_at} (" . date('Y-m-d H:i:s', $job72->available_at) . ")\n";
    echo "Job 72 reserved_at: {$job72->reserved_at} (" . ($job72->reserved_at ? date('Y-m-d H:i:s', $job72->reserved_at) : 'null') . ")\n";
    echo "Difference (reserved_at - real_time): " . ($job72->reserved_at - time()) . " seconds\n";
}

echo "\n=== TIME MACHINE / MOCK TIME ===\n";
echo "MockTime session or cookie? Let's check MockTimeMiddleware or config...\n";
if (class_exists(\App\Http\Middleware\MockTimeMiddleware::class)) {
    echo "MockTimeMiddleware exists.\n";
}
