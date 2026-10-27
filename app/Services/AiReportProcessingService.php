<?php

namespace App\Services;

use App\Jobs\AnalyzeReportVersion;
use App\Models\KhoiBaoCaoTienDo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiReportProcessingService
{
    public function path(KhoiBaoCaoTienDo $report): string
    {
        $root = realpath(storage_path('app/public'));
        $path = $root && $report->DuongDanFile ? realpath($root.DIRECTORY_SEPARATOR.$report->DuongDanFile) : false;
        $prefix = strtolower(str_replace('\\','/',(string) $root)).'/';
        if (!$path || !is_file($path) || !str_starts_with(strtolower(str_replace('\\','/',$path)), $prefix)) throw new \RuntimeException('report_file_missing');
        if (!in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)), ['pdf','docx'])) throw new \RuntimeException('report_format_unsupported');
        return $path;
    }

    public function payload(KhoiBaoCaoTienDo $report): array
    {
        $data = $report->TomTatJson ?? [];
        if (is_string($data)) $data = json_decode($data,true) ?: [];
        return isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;
    }

    public function fingerprint(KhoiBaoCaoTienDo $report): string
    {
        return hash('sha256', json_encode([hash_file('sha256',$this->path($report)), $this->identity($report),
            $report->dotNop?->nguCanhDoiChieuAi(), config('services.gemini.model'), config('ai_processing.summary'),
            config('ai_processing.pipeline_version', 'section-v6.1'), config('ai_processing.pdf_engine'), config('ai_processing.lab')], JSON_UNESCAPED_UNICODE));
    }

    public function identity(KhoiBaoCaoTienDo $report): array
    {
        $data = $this->payload($report);
        $bound = $data['_submission_identity'] ?? $data['source_identity'] ?? [];
        $topic = $report->deTai ?? $report->dotNop?->deTai;
        $owners = \App\Models\PhieuDangKy::where('MaDeTai', $report->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])->distinct()->pluck('MaNhom');
        $groupId = $bound['group_id'] ?? ($owners->count() === 1 ? $owners->first() : null);
        $semester = $bound['semester_id'] ?? ($groupId ? \App\Models\Nhom::find($groupId)?->MaHocKy : null) ?? $topic?->MaHocKy;
        return ['document_id'=>$report->MaBaoCao, 'document_version_id'=>$report->MaKhoi,
            'group_id'=>$groupId, 'topic_id'=>$report->MaDeTai, 'semester_id'=>$semester,
            'stage_id'=>$report->MaGiaiDoan, 'round_id'=>$report->MaDotNop, 'submission_version'=>$report->LanNop];
    }

    public function sourceMatches(KhoiBaoCaoTienDo $report, array $data): bool
    {
        $expected = $data['_source_hash'] ?? $data['source_identity']['source_hash'] ?? null;
        if (!$expected) return true; // Legacy output is labelled as legacy, never silently upgraded.
        try { return hash_equals((string) $expected, hash_file('sha256', $this->path($report))); }
        catch (\Throwable) { return false; }
    }

    public function enqueue(KhoiBaoCaoTienDo $report, bool $reviewOnly = false): array
    {
        // Database queue insertion is in the same short transaction as the pending state.
        // An explicit connection keeps QUEUE_CONNECTION=sync from running AI in HTTP.
        return DB::transaction(function () use ($report,$reviewOnly) {
            $locked = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->lockForUpdate()->findOrFail($report->MaKhoi);
            $data = $this->payload($locked);
            $state = $data['_processing'] ?? [];
            $fingerprint = $this->fingerprint($locked);
            if (($data['_canonical_fingerprint'] ?? '') === $fingerprint && !empty($data['analysis']['text_complete'])) {
                return array_merge($state, ['status'=>($data['analysis']['status'] ?? '') === 'completed' ? 'completed' : 'partial', 'reused'=>true,
                    'message'=>'Đang sử dụng kết quả đã kiểm chứng. Tệp và cấu hình không đổi nên không gọi AI sinh lại.']);
            }
            if (($state['fingerprint'] ?? '') === $fingerprint && in_array($state['status'] ?? '', ['queued','running','waiting'])) {
                return $state; // The worker owns retries; repeated clicks cannot bypass cooldown.
            }
            // A changed brief also changes the focus of a flexible summary. Map checkpoints remain reusable.
            if ($reviewOnly && (int) ($data['schema_version'] ?? 0) === 5 &&
                ($data['summary_scope']['context_hash'] ?? '') !== $locked->dotNop?->maNguCanhDoiChieuAi()) {
                $reviewOnly = false;
            }
            if (!$this->sourceMatches($locked, $data)) $reviewOnly = false;
            $state = ['token'=>(string) Str::uuid(), 'fingerprint'=>$fingerprint, 'status'=>'queued', 'mode'=>$reviewOnly ? 'review' : 'full',
                'attempts'=>0, 'updated_at'=>time(), 'queued_at'=>time(), 'message'=>'Đã lưu bài nộp. AI đang chờ xử lý nền.', 'errors'=>[]];
            $data['_processing'] = $state;
            $locked->update(['TomTatJson'=>$data]);
            AnalyzeReportVersion::dispatch($locked->MaKhoi,$state['token'])->onConnection('ai')->onQueue('ai-reports');
            return $state;
        });
    }

    public function updateState(string $id, string $token, array $changes): bool
    {
        return DB::transaction(function () use ($id,$token,$changes) {
            $report = KhoiBaoCaoTienDo::lockForUpdate()->find($id);
            if (!$report) return false;
            $data = $this->payload($report);
            if (($data['_processing']['token'] ?? '') !== $token) return false;
            $data['_processing'] = array_merge($data['_processing'], $changes, ['updated_at'=>time()]);
            $report->update(['TomTatJson'=>$data]);
            return true;
        });
    }

    public function progressPath(string $token): string
    {
        return storage_path('app/private/ai_progress/'.hash('sha256',$token).'.json');
    }

    public function workerRunning(): bool
    {
        // ai:work holds this OS lock for its lifetime; a leftover file is not a heartbeat.
        $path = storage_path('app/private/ai-worker.lock');
        if (!is_file($path)) return false;
        $handle = @fopen($path, 'r');
        if (!$handle) return false;
        try {
            $available = flock($handle, LOCK_EX | LOCK_NB);
            if ($available) flock($handle, LOCK_UN);
            return !$available;
        } finally {
            fclose($handle);
        }
    }

    public function status(KhoiBaoCaoTienDo $report): array
    {
        $data = $this->payload($report);
        $state = $data['_processing'] ?? ['status'=>'idle','message'=>'Chưa có lượt xử lý nền.'];
        $progress = [];
        if (in_array($state['status'] ?? '', ['running','waiting','partial']) && !empty($state['token'])) {
            $path = $this->progressPath($state['token']);
            $value = is_file($path) ? json_decode(file_get_contents($path),true) : [];
            if (is_array($value)) $progress = array_intersect_key($value,array_flip(['stage','completed_calls','reused_calls','waiting_seconds','model','map_done','map_total','chapter_done','chapter_total','engine','started_at','updated_at']));
        }
        $active = in_array($state['status'] ?? '', ['queued', 'running', 'waiting']);
        $workerRunning = $active ? $this->workerRunning() : null;
        if ($active && !$workerRunning && time() - ($state['queued_at'] ?? $state['updated_at'] ?? time()) >= 15) {
            $state['message'] = 'Tiến trình xử lý AI đang tắt. Bài nộp đã được lưu; vui lòng báo quản trị viên khởi động lại tiến trình xử lý AI. Không cần nộp lại bài.';
            $state['errors'] = array_merge($state['errors'] ?? [], [[
                'stage' => 'worker', 'code' => 'worker_offline',
                'message' => 'Chưa có tiến trình xử lý nền hoạt động; bài đang chờ, chưa phải lỗi gọi API.',
            ]]);
        }
        return array_intersect_key($state,array_flip(['token','status','message','attempts','next_retry_at','updated_at','queued_at','errors'])) + [
            'summary_status'=>$data['analysis']['status'] ?? null,
            'review_status'=>$data['requirements_review']['status'] ?? null,
            'review_has_results'=>(int)($data['requirements_review']['processing']['completed_checks'] ?? 0)>0,
            'server_time'=>time(),
            'worker_running' => $workerRunning,
            'progress'=>$progress,
            'summary_coverage'=>['done'=>$data['analysis']['analyzed_chunks'] ?? 0,'total'=>$data['analysis']['total_chunks'] ?? 0],
            'review_coverage'=>$data['requirements_review']['coverage'] ?? [],
        ];
    }

    public function retryState(array $data, bool $reviewOnly, int $attempt): array
    {
        $errors = array_values(array_filter(array_merge($reviewOnly ? [] : ($data['analysis']['errors'] ?? []), $data['requirements_review']['errors'] ?? [],
            [$data['requirements_review']['citation_audit']['error'] ?? null], $data['processing_errors'] ?? []), 'is_array'));
        $unique=[];
        foreach ($errors as $error) {
            $key=($error['stage'] ?? '').'|'.($error['code'] ?? '').'|'.($error['model'] ?? '').'|'.($error['quota'] ?? '');
            if (!isset($unique[$key]) || ($error['retry_after'] ?? 0) > ($unique[$key]['retry_after'] ?? 0)) $unique[$key]=$error;
        }
        $errors=array_values($unique);
        $summaryDone = $reviewOnly || ($data['analysis']['status'] ?? '') === 'completed';
        $review = $data['requirements_review'] ?? null;
        $reviewDone = ($reviewOnly ? is_array($review) : true) && (!$review || in_array($review['status'] ?? '', ['completed', 'not_configured']));
        if ($summaryDone && $reviewDone) return ['status'=>'completed','message'=>$reviewOnly ? 'Đã hoàn tất đối chiếu yêu cầu. Phần tóm tắt trước đó được giữ nguyên.' : 'Đã hoàn tất phân tích và các bước đối chiếu hiện có.','errors'=>[]];
        $fatal = array_filter($errors, fn ($e) => in_array($e['code'] ?? '', ['authentication','invalid_request','input_too_large','extraction_failed']));
        if ($fatal || $attempt >= config('ai_processing.max_passes',20) || !$errors) {
            return ['status'=>'partial','message'=>'Đã giữ kết quả hợp lệ. Một số bước chưa hoàn tất; xem nguyên nhân bên dưới trước khi thử lại.','errors'=>$errors];
        }
        if (($data['analysis']['engine'] ?? '') === 'pdf_lab' && $errors && !array_filter($errors, fn ($e) => ($e['code'] ?? '') !== 'budget_exhausted')) {
            return ['status'=>'waiting','next_retry_at'=>time()+1,'errors'=>$errors,
                'message'=>'Đã lưu tiến độ; đang tiếp tục các bước còn thiếu từ checkpoint.'];
        }
        $delay = min(900, 30 * (2 ** min(5,max(0,$attempt-1))));
        foreach ($errors as $error) $delay = max($delay,(int) ($error['retry_after'] ?? 0));
        $quota = array_filter($errors,fn ($e) => ($e['code'] ?? '') === 'rate_limit');
        $daily = array_filter($quota,fn ($e) => ($e['quota'] ?? '') === 'day');
        return ['status'=>'waiting','next_retry_at'=>time()+$delay,'errors'=>$errors,
            'message'=>$daily ? 'Hạn mức ngày đã bị giới hạn. Đã tạm dừng và giữ kết quả để tiếp tục sau thời gian chờ.' : ($quota ? 'Đang chờ giới hạn API. Hệ thống sẽ tự tiếp tục các bước còn thiếu.' : 'Dịch vụ AI chưa hoàn tất. Đã giữ kết quả và lên lịch thử tiếp.')];
    }
}
