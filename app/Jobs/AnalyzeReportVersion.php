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
                $processing->updateState($this->reportId,$this->token,['status'=>'partial','message'=>'Tệp, yêu cầu hoặc cấu hình xử lý đã thay đổi. Hãy chọn Phân tích lại để xử lý theo đầu vào mới.']);
                return;
            }
            $attempt = (int) ($state['attempts'] ?? 0)+1;
            $processing->updateState($this->reportId,$this->token,['status'=>'running','attempts'=>$attempt,'next_retry_at'=>null,'message'=>'AI đang xử lý báo cáo; các bước đã lưu sẽ được sử dụng lại.']);
            $only = ($state['mode'] ?? '') === 'review';
            // The Lab reader reuses Map when the brief changes, then refreshes its cited requirements.
            if (config('ai_processing.pdf_engine') === 'pdf_lab' && strtolower(pathinfo($processing->path($report), PATHINFO_EXTENSION)) === 'pdf') $only = false;

            // Đối chiếu phiên bản (Lần 1 vs Lần 2): Kiểm tra xem có phiên bản trước liền kề không
            $prevData = null;
            if ($report->LanNop > 1) {
                $previousReport = KhoiBaoCaoTienDo::where('MaBaoCao', $report->MaBaoCao)
                    ->where('LanNop', '<', $report->LanNop)
                    ->orderByDesc('LanNop')
                    ->first();
                if ($previousReport && !empty($previousReport->TomTatJson)) {
                    $prevData = [
                        'lan_nop' => $previousReport->LanNop,
                        'completion_percentage' => $previousReport->PhanTramHoanThanh,
                        'nhan_xet_gv' => $previousReport->NhanXetGV,
                        'overall_summary' => $previousReport->TomTatAI,
                        'data' => $previousReport->TomTatJson['data'] ?? $previousReport->TomTatJson,
                    ];
                }
            }

            $result = $ai->summarizePdf($processing->path($report),$report->dotNop?->giaiDoan?->ThuTu ?? 1,[
                'review_context'=>$report->dotNop?->nguCanhDoiChieuAi(),
                'review_only'=>$only,
                'progress_path'=>$processing->progressPath($this->token),
                'document_identity'=>$processing->identity($report),
                'previous_version'=>$prevData,
                'stage'=>$report->dotNop?->giaiDoan?->ThuTu ?? 1,
                'on_partial_result'=>function (array $partial) use ($processing,$ai,$state,$only) {
                    $current=KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->find($this->reportId);
                    if (empty($partial['data']['_source_hash']) ||
                        ($partial['data']['document_version_id']??null)!==$this->reportId) return;
                    if (!$current || ($processing->payload($current)['_processing']['token']??'')!==$this->token ||
                        $processing->fingerprint($current)!==$state['fingerprint'] ||
                        !$processing->sourceMatches($current,$partial['data'])) return;
                    $ai->saveVersionSummary($current,$partial,['review_only'=>$only,'token'=>$this->token]);
                },
            ]);
            $report->refresh()->load('dotNop.giaiDoan');
            if ($processing->fingerprint($report) !== $state['fingerprint']) {
                $processing->updateState($this->reportId,$this->token,['status'=>'partial','message'=>'Tệp, yêu cầu hoặc cấu hình xử lý đã thay đổi trong lúc phân tích. Kết quả trước đó được giữ; hãy phân tích lại theo đầu vào mới.']);
                return;
            }
            $data = $result['data'] ?? [];
            $data['_source_hash'] = hash_file('sha256',$processing->path($report));
            $reviewStatus = $data['requirements_review']['status'] ?? 'not_configured';
            if (!empty($data['analysis']['text_complete']) &&
                (($data['analysis']['engine'] ?? '') === 'pdf_lab' ? !empty($data['analysis']['quality_checks_completed']) : !empty($data['analysis']['completion_audit_completed']))
                && in_array($reviewStatus, ['completed', 'not_configured'])) {
                $data['_canonical_fingerprint'] = $state['fingerprint'];
            }
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
