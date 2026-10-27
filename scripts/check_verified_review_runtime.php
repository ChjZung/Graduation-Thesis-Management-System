<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$out=['pipeline'=>config('ai_processing.pipeline_version'),'review_engine'=>config('ai_processing.lab.review_engine'),'worker_running'=>app(App\Services\AiReportProcessingService::class)->workerRunning()];
try{$out['queued_jobs']=Illuminate\Support\Facades\DB::connection(config('queue.connections.ai.connection'))->table(config('queue.connections.ai.table','jobs'))->where('queue','ai-reports')->count();}catch(Throwable $e){$out['queue_check']='unavailable';}
echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
