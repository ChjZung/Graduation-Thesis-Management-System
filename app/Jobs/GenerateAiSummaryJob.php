<?php

namespace App\Jobs;

use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Compatibility adapter for older queued payloads; every version uses the same pipeline. */
class GenerateAiSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(private string $maBaoCao) { $this->onConnection('ai')->onQueue('ai-reports'); }
    public function handle(AiReportProcessingService $processing): void
    {
        $report=KhoiBaoCaoTienDo::where('MaBaoCao',$this->maBaoCao)->orderByDesc('LanNop')->first();
        if ($report && $report->DuongDanFile) $processing->enqueue($report);
    }
}
