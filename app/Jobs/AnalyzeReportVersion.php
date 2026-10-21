<?php

namespace App\Jobs;

use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AnalyzeReportVersion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 25;
    public int $timeout = 390;
    public int $maxExceptions = 3;

    public function __construct(public string $reportId, public string $token) {}

    public function handle(AiReportProcessingService $processing, AiSummaryService $ai): void
    {
        $lock = Cache::store('file')->lock('ai-report:'.$this->reportId,420);
        if (!$lock->get()) { $this->release(15); return; }
        try {
            $report = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->find($this->reportId);
            if (!$report) return;
            $state = $processing->payload($report)['_processing'] ?? [];
            if (($state['token'] ?? '') !== $this->token || !in_array($state['status'] ?? '',['queued','running','waiting'])) return;
            if (($state['next_retry_at'] ?? 0) > time()) { $this->release($state['next_retry_at']-time()); return; }
            if ($processing->fingerprint($report) !== $state['fingerprint']) {
                $processing->updateState($this->reportId,$this->token,['status'=>'partial','message'=>'File hoặc yêu cầu đã thay đổi. Hãy chọn Phân tích lại để xử lý theo nội dung mới.']);
                return;
            }
            $attempt = (int) ($state['attempts'] ?? 0)+1;
            $processing->updateState($this->reportId,$this->token,['status'=>'running','attempts'=>$attempt,'next_retry_at'=>null,'message'=>'AI đang xử lý báo cáo; các bước đã lưu sẽ được sử dụng lại.']);
            $only = ($state['mode'] ?? '') === 'review';
            $result = $ai->summarizePdf($processing->path($report),$report->dotNop?->giaiDoan?->ThuTu ?? 1,[
                'review_context'=>$report->dotNop?->nguCanhDoiChieuAi(), 'review_only'=>$only,'progress_path'=>$processing->progressPath($this->token),
            ]);
            $report->refresh()->load('dotNop.giaiDoan');
            if ($processing->fingerprint($report) !== $state['fingerprint']) {
                $processing->updateState($this->reportId,$this->token,['status'=>'partial','message'=>'File hoặc yêu cầu đã thay đổi trong lúc phân tích. Kết quả trước đó được giữ; hãy phân tích lại theo nội dung mới.']);
                return;
            }
            $data = $result['data'] ?? [];
            $data['_source_hash'] = hash_file('sha256',$processing->path($report));
            $result['data'] = $data;
            $ai->saveVersionSummary($report,$result,['review_only'=>$only,'token'=>$this->token]);
            // Schedule from this attempt's errors, even if a stronger previous result was retained.
            $next = $processing->retryState($data,$only,$attempt);
            $processing->updateState($this->reportId,$this->token,$next);
            if ($next['status'] === 'waiting') $this->release(max(1,$next['next_retry_at']-time()));
        } catch (\Throwable $error) {
            $processing->updateState($this->reportId,$this->token,['status'=>'waiting','next_retry_at'=>time()+60,
                'message'=>'Tiến trình xử lý bị gián đoạn. Kết quả đã lưu được giữ lại; hệ thống sẽ thử tiếp.',
                'errors'=>[['stage'=>'worker','code'=>'worker_error','message'=>'Chưa thực hiện được bước xử lý nền.']]]);
            throw $error;
        } finally { $lock->release(); }
    }

    public function backoff(): array { return [60,120,300]; }

    public function failed(?\Throwable $error): void
    {
        app(AiReportProcessingService::class)->updateState($this->reportId,$this->token,[
            'status'=>'partial','message'=>'Tiến trình nền đã dừng sau các lần thử. Kết quả hợp lệ vẫn được giữ; có thể chọn Phân tích lại.',
        ]);
    }
}
