<?php

namespace App\Services;

use App\Jobs\AnalyzeReportVersion;
use App\Models\KhoiBaoCaoTienDo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $payload = $this->storedPayload($report);
        // Loading the round is a database read only; never retrieval or generation.
        if (($report->relationLoaded('dotNop') || $report->MaDotNop !== null) && $report->dotNop) {
            $payload = AiRequirementResultPolicy::forContext($payload, $report->dotNop->nguCanhDoiChieuAi());
        }
        $needsReview = ($payload['requirements_binding_status'] ?? '') === 'current'
            && empty($payload['teacher_requirements'])
            && ($payload['requirements_review']['status'] ?? '') !== 'not_configured';
        if (($payload['analysis']['engine'] ?? '') === 'pdf_lab' && (empty($payload['overall_summary']) || $needsReview)
            && !empty($payload['_analysis_history']) && $this->sourceMatches($report, $payload)) {
            $history = array_slice(array_reverse($payload['_analysis_history'], true), 0, 20, true);
            foreach ($history as $id => $entry) {
                if (!preg_match('/^[a-f0-9]{64}$/D', (string) $id)
                    || ($entry['source_hash'] ?? null) !== ($payload['_source_hash'] ?? null)) continue;
                $path = 'ai_history/'.$id.'.json';
                $disk = Storage::disk('local');
                if (!$disk->exists($path)) continue;
                $saved = json_decode($disk->get($path), true);
                if (!is_array($saved) || ($saved['document_version_id'] ?? null) !== $report->MaKhoi) continue;
                $payload = AiResultMerger::fillMissingReading($payload, $saved);
                if ($needsReview && !empty($saved['teacher_requirements'])) {
                    $payload = AiRequirementResultPolicy::preserveVerified($saved, $payload, $payload);
                    $needsReview = empty($payload['teacher_requirements']);
                }
                if (!empty($payload['overall_summary']) && !$needsReview) break;
            }
        }
        return $payload;
    }

    public function storedPayload(KhoiBaoCaoTienDo $report): array
    {
        $data = $report->TomTatJson ?? [];
        if (is_string($data)) $data = json_decode($data,true) ?: [];
        $payload = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;

        return $payload;
    }

    public function fingerprint(KhoiBaoCaoTienDo $report): string
    {
        return hash('sha256', json_encode([hash_file('sha256',$this->path($report)), $this->identity($report),
            $report->dotNop?->nguCanhDoiChieuAi(), config('services.gemini.model'), config('ai_processing.summary'),
            config('ai_processing.pipeline_version', 'section-v6.1'), config('ai_processing.pdf_engine'), config('ai_processing.lab')], JSON_UNESCAPED_UNICODE));
    }

    public function identity(KhoiBaoCaoTienDo $report): array
    {
        $data = $this->storedPayload($report);
        $bound = $data['_submission_identity'] ?? $data['source_identity'] ?? [];
        $topic = $report->deTai ?? $report->dotNop?->deTai;
        $owners = \App\Models\PhieuDangKy::where('MaDeTai', $report->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])->distinct()->pluck('MaNhom');
        $groupId = $bound['group_id'] ?? ($owners->count() === 1 ? $owners->first() : null);
        $semester = $bound['semester_id'] ?? ($groupId ? \App\Models\Nhom::find($groupId)?->MaHocKy : null) ?? $topic?->MaHocKy;
        return ['document_id'=>$report->MaBaoCao, 'document_version_id'=>$report->MaKhoi,
            'group_id'=>$groupId, 'topic_id'=>$report->MaDeTai, 'semester_id'=>$semester,
            'stage_id'=>$report->MaGiaiDoan, 'round_id'=>$report->MaDotNop, 'submission_version'=>$bound['submission_version'] ?? $report->LanNop];
    }

    public function sourceMatches(KhoiBaoCaoTienDo $report, array $data): bool
    {
        $expected = $data['_source_hash'] ?? $data['source_identity']['source_hash'] ?? null;
        if (!$expected) return true; // Legacy output is labelled as legacy, never silently upgraded.
        try { return hash_equals((string) $expected, hash_file('sha256', $this->path($report))); }
        catch (\Throwable) { return false; }
    }

    public function enqueue(KhoiBaoCaoTienDo $report, bool $reviewOnly = false, bool $force = false, bool $fresh = false): array
    {
        // Database queue insertion is in the same short transaction as the pending state.
        // An explicit connection keeps QUEUE_CONNECTION=sync from running AI in HTTP.
        return DB::transaction(function () use ($report,$reviewOnly,$force,$fresh) {
            $locked = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->lockForUpdate()->findOrFail($report->MaKhoi);
            $data = $this->storedPayload($locked);
            // Freeze source identity before dispatch. LanNop is only a display number
            // and may be renumbered when another unreviewed submission is deleted.
            $boundIdentity = is_array($data['_submission_identity'] ?? null) ? $data['_submission_identity'] : [];
            $data['_submission_identity'] = array_replace($this->identity($locked), $boundIdentity);
            $locked->TomTatJson = $data;
            $state = $data['_processing'] ?? [];
            $fingerprint = $this->fingerprint($locked);

            // Force means a new generation AFTER the active job finishes/cancels.
            // Repeated clicks must not delete a reserved job or bypass its cooldown.
            if (($state['fingerprint'] ?? '') === $fingerprint &&
                in_array($state['status'] ?? '', ['queued','running','waiting'])) {
                return $state;
            }

            if ($force) {
                $oldToken = $state['token'] ?? null;
                if ($oldToken) {
                    DB::table('jobs')->where('queue', 'ai-reports')->whereNull('reserved_at')
                        ->where('payload', 'like', '%' . $oldToken . '%')->delete();
                }
            } else {
                $captionReady = ($data['analysis']['engine'] ?? '') !== 'pdf_lab' ||
                    (($data['caption_index']['version'] ?? '') === 'caption-text-2' && ($data['caption_index']['status'] ?? '') === 'completed');
                $reviewGuardReady = ($data['analysis']['engine'] ?? '') !== 'pdf_lab' ||
                    ($data['requirements_review']['status'] ?? '') === 'not_configured' ||
                    ($data['requirements_review']['presentation_guard']['version'] ?? '') === AiRequirementResultPolicy::OUTPUT_GUARD_VERSION;
                if (($data['_canonical_fingerprint'] ?? '') === $fingerprint && !empty($data['analysis']['text_complete']) && $captionReady && $reviewGuardReady) {
                    return array_merge($state, ['status'=>($data['analysis']['status'] ?? '') === 'completed' ? 'completed' : 'partial', 'reused'=>true,
                        'message'=>'Đang sử dụng kết quả đã kiểm chứng. Tệp và cấu hình không đổi nên không gọi AI sinh lại.']);
                }
                if (($state['fingerprint'] ?? '') === $fingerprint && in_array($state['status'] ?? '', ['queued','running','waiting'])) {
                    return $state; // The worker owns retries; repeated clicks cannot bypass cooldown.
                }
            }

            // A changed brief also changes the focus of a flexible summary. Map checkpoints remain reusable.
            if ($reviewOnly && (int) ($data['schema_version'] ?? 0) === 5 &&
                ($data['summary_scope']['context_hash'] ?? '') !== $locked->dotNop?->maNguCanhDoiChieuAi()) {
                $reviewOnly = false;
            }
            if (!$this->sourceMatches($locked, $data)) $reviewOnly = false;
            $previousState = $state;
            $state = ['token'=>(string) Str::uuid(), 'fingerprint'=>$fingerprint, 'status'=>'queued', 'mode'=>$reviewOnly ? 'review' : 'full',
                'fresh'=>$fresh,
                'attempts'=>0, 'updated_at'=>time(), 'queued_at'=>time(), 'message'=>'Đã lưu bài nộp. AI đang chờ xử lý nền.', 'errors'=>[]];
            if ($fresh) {
                $state['cache_scope'] = hash('sha256', $state['token']);
            } elseif (($previousState['fingerprint'] ?? '') === $fingerprint && !empty($previousState['cache_scope'])) {
                $state['cache_scope'] = $previousState['cache_scope'];
            }
            if (!$fresh && ($previousState['fingerprint'] ?? '') === $fingerprint) {
                $state['last_progress'] = $this->status($locked)['progress'] ?? [];
                $state['history'] = $previousState['history'] ?? [];
            }
            $data['_processing'] = $state;
            $locked->update(['TomTatJson'=>$data]);
            AnalyzeReportVersion::dispatch($locked->MaKhoi,$state['token'])->onConnection('ai')->onQueue('ai-reports');
            return $state;
        });
    }

    public function cancel(KhoiBaoCaoTienDo $report, bool $pause = false): array
    {
        return DB::transaction(function () use ($report, $pause) {
            $locked = KhoiBaoCaoTienDo::lockForUpdate()->findOrFail($report->MaKhoi);
            $data = $this->storedPayload($locked);
            $current = $data['_processing'] ?? [];
            if (!in_array($current['status'] ?? '', ['queued','running','waiting'], true)) return $current;
            $lastProgress = $pause ? ($this->status($locked)['progress'] ?? []) : [];
            $oldToken = $current['token'] ?? null;
            if ($oldToken) {
                $prog = $this->progressPath($oldToken);
                if (($current['status'] ?? '') === 'running') @file_put_contents($prog.'.cancel', '1', LOCK_EX);
                if (is_file($prog)) @unlink($prog);
            }

            // Xóa các job đang chờ xử lý của báo cáo này trong hàng đợi database
            if ($oldToken) {
                DB::table('jobs')->where('queue', 'ai-reports')->whereNull('reserved_at')
                    ->where('payload', 'like', '%' . $oldToken . '%')->delete();
            }

            $state = [
                'token' => (string) Str::uuid(),
                'fingerprint' => $this->fingerprint($locked),
                'status' => $pause ? 'paused' : 'cancelled',
                'last_progress' => $lastProgress,
                'history' => $pause ? array_slice(array_merge($current['history'] ?? [], [
                    ['at'=>time(), 'status'=>'paused', 'message'=>'Người dùng đã tạm dừng.'],
                ]), -20) : [],
                'mode' => 'full',
                'attempts' => 0,
                'updated_at' => time(),
                'queued_at' => time(),
                'message' => $pause ? 'Đã tạm dừng. Chọn Tiếp tục để dùng lại các phần đã lưu.' :
                    'Đã dừng tiến trình phân tích. Bạn có thể bấm "Phân tích lại" để thực hiện lại.',
                'errors' => []
            ];

            if (!empty($current['cache_scope'])) $state['cache_scope'] = $current['cache_scope'];
            $data['_processing'] = $state;
            $locked->update(['TomTatJson' => $data]);
            return $state;
        });
    }

    public function isRunning(string $id, string $token): bool
    {
        $report = KhoiBaoCaoTienDo::find($id, ['MaKhoi', 'TomTatJson']);
        $state = $report ? ($this->storedPayload($report)['_processing'] ?? []) : [];
        return ($state['token'] ?? '') === $token && ($state['status'] ?? '') === 'running';
    }

    public function updateState(string $id, string $token, array $changes): bool
    {
        return DB::transaction(function () use ($id,$token,$changes) {
            $report = KhoiBaoCaoTienDo::lockForUpdate()->find($id);
            if (!$report) return false;
            $data = $this->storedPayload($report);
            if (($data['_processing']['token'] ?? '') !== $token) return false;
            $now = time();
            $history = $data['_processing']['history'] ?? [];
            if (isset($changes['status'])) {
                $history[] = ['at'=>$now, 'status'=>$changes['status'], 'message'=>$changes['message'] ?? '',
                    'error'=>$changes['errors'][0] ?? null];
            }
            $data['_processing'] = array_merge($data['_processing'], $changes,
                ['updated_at'=>$now, 'history'=>array_slice($history, -20)]);
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
        $progress = $state['last_progress'] ?? [];
        if (in_array($state['status'] ?? '', ['running','waiting','partial']) && !empty($state['token'])) {
            $path = $this->progressPath($state['token']);
            $value = is_file($path) ? json_decode(file_get_contents($path),true) : [];
            if (!empty($value) && is_array($value)) $progress = array_intersect_key($value,array_flip(['stage','completed_calls','reused_calls','waiting_seconds','model','map_done','map_total','map_active','map_failed','map_durable','chapter_done','chapter_total','engine','started_at','updated_at']));
        }
        if (($progress['engine'] ?? $data['analysis']['engine'] ?? '') === 'pdf_lab') {
            $progress = self::confirmedPdfProgress($progress, $data['analysis'] ?? []);
        }
        if (in_array($state['status'] ?? '', ['paused','waiting','queued','partial'])) {
            $progress['map_active'] = [];
        }
        $active = in_array($state['status'] ?? '', ['queued', 'running', 'waiting']);
        if (!$active && ($state['status'] ?? '') === 'partial') {
            foreach ($state['errors'] ?? [] as $index => $error) {
                if (($error['code'] ?? '') === 'budget_exhausted') {
                    $state['errors'][$index]['message'] = 'Lượt xử lý đã dừng ở giới hạn số lần tiếp tục. Tiến độ được giữ; chọn Tiếp tục từ tiến độ đã lưu để chạy tiếp.';
                }
            }
        }
        $workerRunning = $active ? $this->workerRunning() : null;
        if ($active && !$workerRunning && time() - ($state['queued_at'] ?? $state['updated_at'] ?? time()) >= 15) {
            $state['message'] = 'Tiến trình xử lý AI đang tắt. Bài nộp đã được lưu; vui lòng báo quản trị viên khởi động lại tiến trình xử lý AI. Không cần nộp lại bài.';
            $state['errors'] = array_merge($state['errors'] ?? [], [[
                'stage' => 'worker', 'code' => 'worker_offline',
                'message' => 'Chưa có tiến trình xử lý nền hoạt động; bài đang chờ, chưa phải lỗi gọi API.',
            ]]);
        }
        return array_intersect_key($state,array_flip(['token','status','message','attempts','next_retry_at','updated_at','queued_at','errors','history'])) + [
            'summary_status'=>$data['analysis']['status'] ?? null,
            'review_status'=>$data['requirements_review']['status'] ?? null,
            'review_has_results'=>(int)($data['requirements_review']['processing']['completed_checks'] ?? 0)>0,
            'result_revision'=>$this->resultRevision($data),
            'server_time'=>time(),
            'worker_running' => $workerRunning,
            'worker_stalled' => ($state['status'] ?? '') === 'running'
                && time() - max((int) ($state['updated_at'] ?? 0), (int) ($progress['updated_at'] ?? 0)) > 420,
            'progress'=>$progress,
            'summary_coverage'=>['done'=>$data['analysis']['analyzed_chunks'] ?? 0,'total'=>$data['analysis']['total_chunks'] ?? 0],
            'review_coverage'=>$data['requirements_review']['coverage'] ?? [],
        ];
    }

    public static function confirmedPdfProgress(array $progress, array $analysis): array
    {
        $savedTotal = (int) ($analysis['total_chunks'] ?? 0);
        $total = (int) ($progress['map_total'] ?? 0) ?: $savedTotal;
        if ($total > 0) {
            $savedDone = $total === $savedTotal ? (int) ($analysis['analyzed_chunks'] ?? 0) : 0;
            $progress['map_total'] = $total;
            $progress['map_done'] = min($total, max(0, $savedDone, (int) ($progress['map_done'] ?? 0)));
        }
        return $progress;
    }

    public function resultRevision(array $data): ?string
    {
        $visible=array_intersect_key($data,array_flip(['overview_summary','document_content','chapters','completed_work','teacher_requirements','content_review','progress_v1','progress_v1_status','progress_display']));
        if (!empty($data['requirements_review']['items'])) {
            $visible['requirements_review'] = array_intersect_key($data['requirements_review'], array_flip(['context_hash', 'status', 'items']));
        }
        if (!empty($data['source_navigation']['version'])) {
            $visible['source_navigation'] = $data['source_navigation']['version'];
        }
        if (!$visible) return null;
        return hash('sha256',json_encode($visible,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    }

    public function retryState(array $data, bool $reviewOnly, int $attempt): array
    {
        $errors = array_values(array_filter(array_merge($reviewOnly ? [] : ($data['analysis']['errors'] ?? []), $data['requirements_review']['errors'] ?? [],
            [$data['requirements_review']['citation_audit']['error'] ?? null], $data['processing_errors'] ?? []), 'is_array'));
        $now = time();
        $unique=[];
        foreach ($errors as $error) {
            $key=($error['stage'] ?? '').'|'.($error['code'] ?? '').'|'.($error['model'] ?? '').'|'.($error['quota'] ?? '').'|'.($error['http_status'] ?? '');
            if (!isset($unique[$key]) || self::retryDelay($error, $now) > self::retryDelay($unique[$key], $now)) $unique[$key]=$error;
        }
        $errors=array_values($unique);
        $summaryDone = $reviewOnly || ($data['analysis']['status'] ?? '') === 'completed';
        $review = $data['requirements_review'] ?? null;
        $reviewDone = ($reviewOnly ? is_array($review) : true) && (!$review || in_array($review['status'] ?? '', ['completed', 'not_configured']));
        $progressWaiting = ($data['progress_v1_status'] ?? '') === 'unavailable' && array_filter($errors,
            fn ($e) => ($e['stage'] ?? '') === 'progress_v1'
                && in_array($e['code'] ?? '', ['rate_limit','service_unavailable','timeout','connection_error'], true));
        if ($summaryDone && $reviewDone && !$progressWaiting) return ['status'=>'completed','message'=>$reviewOnly ? 'Đã hoàn tất đối chiếu yêu cầu. Phần tóm tắt trước đó được giữ nguyên.' : 'Đã hoàn tất phân tích và các bước đối chiếu hiện có.','errors'=>[]];
        $fatal = array_filter($errors, fn ($e) => in_array($e['code'] ?? '', ['authentication','invalid_request','input_too_large','extraction_failed','output_validation']));
        if ($attempt >= config('ai_processing.max_passes',20)) {
            return ['status'=>'partial','next_retry_at'=>null,'message'=>'Đã hết lượt tự thử. Kết quả đã lưu được giữ; cần kiểm tra dịch vụ AI trước khi tiếp tục.','errors'=>$errors];
        }
        if ($fatal || !$errors) {
            return ['status'=>'partial','next_retry_at'=>null,'message'=>'Đã giữ kết quả hợp lệ. Một số bước chưa hoàn tất; xem nguyên nhân bên dưới trước khi thử lại.','errors'=>$errors];
        }
        if ($errors && !array_filter($errors, fn ($e) => ($e['code'] ?? '') !== 'budget_exhausted')) {
            return ['status'=>'waiting','next_retry_at'=>time()+1,'errors'=>$errors,
                'message'=>'Đã lưu tiến độ; đang tiếp tục các bước còn thiếu từ checkpoint.'];
        }
        $delay = min(900, 30 * (2 ** min(5,max(0,$attempt-1))));
        // A local subprocess deadline is not a provider outage. Resume its
        // checkpoint without escalating to a fifteen-minute provider cooldown.
        if ($errors && !array_filter($errors, fn ($e) => !in_array($e['code'] ?? '', ['timeout','budget_exhausted']) ||
            (($e['code'] ?? '') === 'timeout' && ($e['stage'] ?? '') !== 'process'))) {
            $delay = 5;
        }
        if (array_filter($errors, fn ($error) => ($error['http_status'] ?? null) === 503)) {
            $delay += random_int(0, max(1, intdiv($delay, 5)));
        }
        // Resume the same token/checkpoint, rather than creating a fresh analysis.
        // Counted passes include normal budget continuations, so using that count
        // for unbounded backoff used to penalize large, otherwise healthy PDFs.
        $delay = min($delay, max(1, min(120, (int) config('ai_processing.max_transient_retry_seconds', 120))));
        foreach ($errors as $error) $delay = max($delay, self::retryDelay($error, $now));
        $quota = array_filter($errors,fn ($e) => ($e['code'] ?? '') === 'rate_limit');
        $daily = array_filter($quota,fn ($e) => ($e['quota'] ?? '') === 'day');
        $bothUnavailable = array_filter($errors, fn ($e) => ($e['primary_http_status'] ?? null) === 503
            && str_starts_with((string) ($e['model'] ?? ''), 'groq:'));
        if ($bothUnavailable) {
            return ['status'=>'waiting','next_retry_at'=>$now+$delay,'errors'=>$errors,
                'message'=>'Gemini và Groq tạm thời chưa xử lý được. Các phần đã lưu được giữ và sẽ tự tiếp tục đến lượt thử tiếp theo.'];
        }
        return ['status'=>'waiting','next_retry_at'=>$now+$delay,'errors'=>$errors,
            'message'=>$daily ? 'Hạn mức ngày đã bị giới hạn. Đã tạm dừng và giữ kết quả để tiếp tục sau thời gian chờ.' : ($quota ? 'Đang chờ giới hạn API. Hệ thống sẽ tự tiếp tục các bước còn thiếu.' : 'Dịch vụ AI chưa hoàn tất. Đã giữ kết quả và lên lịch thử tiếp.')];
    }

    private static function retryDelay(array $error, int $now): int
    {
        if (isset($error['cooldown_until']) && is_numeric($error['cooldown_until'])) {
            return max(0, (int) ceil((float) $error['cooldown_until']) - $now);
        }
        return max(0, (int) ($error['retry_after'] ?? 0));
    }
}
