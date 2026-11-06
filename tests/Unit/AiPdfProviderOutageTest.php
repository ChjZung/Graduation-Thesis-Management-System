<?php

namespace Tests\Unit;

use App\Jobs\AnalyzeReportVersion;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class AiPdfProviderOutageTest extends TestCase
{
    public function test_both_unavailable_wait_until_existing_deadline_and_stop_at_cap(): void
    {
        $expiry = time() + 1085;
        $data = ['analysis'=>['engine'=>'pdf_lab','status'=>'partial'], 'processing_errors'=>[[
            'code'=>'rate_limit', 'http_status'=>429, 'primary_http_status'=>503,
            'model'=>'groq:mock', 'cooldown_cached'=>true, 'cooldown_until'=>$expiry, 'retry_after'=>1745,
        ]]];
        $processing = new AiReportProcessingService;
        $waiting = $processing->retryState($data, false, 1);
        self::assertSame('waiting', $waiting['status']);
        self::assertSame($expiry, $waiting['next_retry_at']);
        $terminal = $processing->retryState($data, false, 20);
        self::assertSame('partial', $terminal['status']);
        self::assertNull($terminal['next_retry_at']);
    }

    public function test_duplicate_worker_does_not_start_ai_while_report_lock_is_owned(): void
    {
        $store = Mockery::mock();
        $lock = Mockery::mock();
        Cache::shouldReceive('store')->once()->with('file')->andReturn($store);
        $store->shouldReceive('lock')->once()->with('ai-report:fixture', 420)->andReturn($lock);
        $lock->shouldReceive('get')->once()->andReturnFalse();
        $lock->shouldNotReceive('release');
        $queueJob = Mockery::mock(Job::class);
        $queueJob->shouldReceive('release')->once()->with(15);
        $job = (new AnalyzeReportVersion('fixture', 'token'))->setJob($queueJob);
        $ai = Mockery::mock(AiSummaryService::class);
        $ai->shouldNotReceive('summarizePdf');
        $job->handle(new AiReportProcessingService, $ai);
    }
}
