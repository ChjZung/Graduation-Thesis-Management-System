<?php

namespace Tests\Unit;

use App\Services\AiReportProcessingService;
use Tests\TestCase;

class AiPdfCooldownProgressTest extends TestCase
{
    public function test_retry_prefers_absolute_expiry_and_honors_attempt_cap(): void
    {
        $service = new AiReportProcessingService;
        $expiry = time() + 1085;
        $data = ['processing_errors' => [[
            'code'=>'rate_limit', 'http_status'=>429, 'retry_after'=>1745, 'cooldown_until'=>$expiry,
        ]]];
        self::assertSame($expiry, $service->retryState($data, false, 1)['next_retry_at']);
        $data['processing_errors'][0]['cooldown_until'] = time() - 1;
        self::assertLessThanOrEqual(time() + 30, $service->retryState($data, false, 1)['next_retry_at']);
        self::assertSame('partial', $service->retryState($data, false, 20)['status']);
    }

    public function test_saved_pdf_progress_wins_over_stale_telemetry(): void
    {
        $analysis = ['analyzed_chunks'=>1, 'total_chunks'=>11];
        self::assertSame(1, AiReportProcessingService::confirmedPdfProgress(['map_done'=>0,'map_total'=>11], $analysis)['map_done']);
        self::assertSame(1, AiReportProcessingService::confirmedPdfProgress([], $analysis)['map_done']);
        self::assertSame(0, AiReportProcessingService::confirmedPdfProgress(['map_done'=>0,'map_total'=>8], $analysis)['map_done']);
    }
}
