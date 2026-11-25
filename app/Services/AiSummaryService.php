<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Process\Exceptions\ProcessTimedOutException;

class AiSummaryService
{
    public function saveVersionSummary(\App\Models\KhoiBaoCaoTienDo $report, array $result, array $options = []): void
    {
        // Each submission keeps its own JSON. The legacy table has ONE snapshot per MaBaoCao.
        \Illuminate\Support\Facades\DB::transaction(function () use ($report, $result, $options) {
            $report = \App\Models\KhoiBaoCaoTienDo::lockForUpdate()->findOrFail($report->MaKhoi);
            $old = app(AiReportProcessingService::class)->storedPayload($report);
            if (isset($options['token']) && ($old['_processing']['token'] ?? '') !== $options['token']) return;
            // Retain an immutable private snapshot before replacing a saved AI generation.
            if (!empty($old['schema_version']) && (!empty($old['document_content']) || !empty($old['chapters']))) {
                $snapshot = $old;
                unset($snapshot['_analysis_history'], $snapshot['_processing']);
                $encoded = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $snapshotId = hash('sha256', $report->MaKhoi.'|'.$encoded);
                if (!Storage::disk('local')->exists('ai_history/'.$snapshotId.'.json')) {
                    Storage::disk('local')->put('ai_history/'.$snapshotId.'.json', $encoded);
                }
                $history = $old['_analysis_history'] ?? [];
                $history[$snapshotId] = ['id'=>$snapshotId, 'schema_version'=>$old['schema_version'],
                    'source_hash'=>$old['_source_hash'] ?? $old['source_identity']['source_hash'] ?? null];
                $old['_analysis_history'] = $history;
            }
            $merged = (new AiResultMerger)->merge($old,$result['data'],!empty($options['review_only']));
            foreach (['_submission_identity', '_analysis_history'] as $field) {
                if (isset($old[$field])) $merged[$field] = $old[$field];
            }
            if (isset($old['_processing'])) $merged['_processing'] = $old['_processing'];

            // Retrieval belongs to the background Python pipeline, never the read path.
            // Preserve a stronger verified result when a heuristic fallback arrives.
            $merged = AiRequirementResultPolicy::preserveVerified($old, $merged, $result['data']);

            $result = $this->formatSummarizeResult(array_merge($result,['data'=>$merged]));
            $result['data'] = $merged;

            $completionPct = isset($merged['completion_percentage']) && $merged['completion_percentage'] !== null
                ? (int)$merged['completion_percentage']
                : null;
            $completionBasis = $merged['completion_basis'] ?? null;

            $reportData = [
                'TomTatJson' => $result['data'],
                'TomTatAI' => $result['data']['overall_summary'] ?? '',
            ];
            if ($completionPct !== null) {
                $reportData['PhanTramHoanThanh'] = $completionPct;
            }
            $report->update($reportData);

            // Cập nhật BaoCaoTienDo cha nếu có
            if ($completionPct !== null && $report->MaBaoCao) {
                \App\Models\BaoCaoTienDo::where('MaBaoCao', $report->MaBaoCao)
                    ->update(['PhanTramHoanThanh' => $completionPct]);
            }

            // A late worker from an older version cannot replace the latest legacy snapshot.
            $latest = \App\Models\KhoiBaoCaoTienDo::where('MaBaoCao',$report->MaBaoCao)->orderByDesc('LanNop')->orderByDesc('NgayNop')->value('MaKhoi');
            if ($latest !== $report->MaKhoi) return;
            $summary = \App\Models\TomTatBaoCao::firstOrNew(['MaBaoCao' => $report->MaBaoCao]);
            if (!$summary->exists) {
                $summary->MaTomTat = \App\Helpers\IdGenerator::nextTomTat();
            }
            $summary->fill([
                'MaKhoi' => $report->MaKhoi, 'TenFile' => $report->TenFile, 'DuongDanFile' => $report->DuongDanFile,
                'SoTrang' => ($result['data']['document_type'] ?? 'pdf') === 'docx' ? 0 : (int) ($result['data']['analysis']['total_pages'] ?? $result['total_pages'] ?? 0),
                'TyLeHoanThanhAI' => $completionPct,
                'CanCuDanhGia' => $completionBasis,
                'ThoiDiemDanhGia' => now(),
                'TrangThaiDanhGia' => $completionPct !== null ? 'da_danh_gia' : 'chua_danh_gia',
                'CongViecDaHoanThanh' => $result['formatted']['completed_work'] ?? '',
                'KhoKhan' => $result['formatted']['difficulties'] ?? '', 'KeHoachTuanToi' => $result['formatted']['next_steps'] ?? '',
                'NoiDungAI' => $result['data']['overall_summary'] ?? '', 'TomTatJson' => $result['data'],
                'TrangThai' => 'Đã tạo', 'TrangThaiXuLy' => (($result['data']['analysis']['status'] ?? '') === 'completed' ? 'hoan_thanh' : 'chua_hoan_thanh'),
                'ThoiGianXuLy' => $result['processing_time'] ?? 0, 'NgayTomTat' => now(),
            ])->save();
        });
    }

    /**
     * Tự động dò tìm đường dẫn Python phù hợp trên nhiều môi trường khác nhau:
     * 1. Biến môi trường/config PYTHON_PATH
     * 2. Virtual environment (.venv hoặc venv cục bộ trong project)
     * 3. Laragon trên mọi ổ đĩa (D:\, C:\, E:\, F:\)
     * 4. Môi trường Linux server / Docker / Production (python3)
     */
    public static function resolvePythonBinary(): string
    {
        $configured = config('ai_processing.python_path') ?: env('PYTHON_PATH');
        if ($configured && (file_exists($configured) || PHP_OS_FAMILY !== 'Windows')) {
            return $configured;
        }

        // Virtual environments (local to project)
        $venvs = [
            base_path('.venv/Scripts/python.exe'),
            base_path('.venv/bin/python'),
            base_path('venv/Scripts/python.exe'),
            base_path('venv/bin/python'),
        ];
        foreach ($venvs as $v) {
            if (file_exists($v)) return $v;
        }

        // Common Laragon installation drives on Windows
        if (PHP_OS_FAMILY === 'Windows') {
            foreach (['D', 'C', 'E', 'F'] as $drive) {
                $laragonPath = "{$drive}:/laragon/bin/python/python-3.10/python.exe";
                if (file_exists($laragonPath)) {
                    return $laragonPath;
                }
            }
        }

        // Linux/Unix server detection (e.g. Ubuntu / Docker / Production)
        if (PHP_OS_FAMILY !== 'Windows') {
            $which = trim((string) @shell_exec('which python3 2>/dev/null'));
            if ($which && file_exists($which)) {
                return $which;
            }
        }

        return PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    }

    /**
     * Tự động kiểm tra và cài đặt thư viện cần thiết cho AI (PyMuPDF) nếu phát hiện máy mới / server mới.
     */
    public static function kandesEnvironment(): array
    {
        return [
            'KANDES_API_KEY' => (string) config('services.kandes.api_key'),
            'KANDES_BASE_URL' => (string) config('services.kandes.base_url'),
            'KANDES_MODEL' => (string) config('services.kandes.model'),
            'KANDES_LOCK_FILE' => storage_path('app/private/kandes-request.lock'),
        ];
    }

    public static function logKandesMetric(string $line): void
    {
        $prefix = 'AI_PROVIDER_METRIC ';
        if (!str_starts_with($line, $prefix)) return;
        $metric = json_decode(substr($line, strlen($prefix)), true);
        if (is_array($metric)) Log::info('AI provider request', array_intersect_key($metric, array_flip([
            'provider', 'model', 'operation', 'status', 'seconds', 'input_tokens', 'output_tokens',
        ])));
    }

    public static function checkAndSetupEnvironment(?\Closure $notify = null): array
    {
        $notify = $notify ?? fn ($type, $msg) => null;
        $python = self::resolvePythonBinary();

        // 1. Kiểm tra Python executable
        $testProcess = new \Symfony\Component\Process\Process([$python, '--version']);
        $testProcess->run();
        if (!$testProcess->isSuccessful()) {
            $notify('error', "Không tìm thấy Python khả dụng tại [{$python}]. Vui lòng cài đặt Python 3.10+ hoặc cấu hình PYTHON_PATH trong .env.");
            return ['ready' => false, 'error' => "Python không chạy được tại: {$python}"];
        }
        $pythonVer = trim($testProcess->getOutput()) ?: 'Python 3.x';

        // 2. Kiểm tra thư viện PyMuPDF
        $checkProcess = new \Symfony\Component\Process\Process([$python, '-c', 'import pymupdf']);
        $checkProcess->run();
        if (!$checkProcess->isSuccessful()) {
            $notify('warn', "Phát hiện môi trường chưa có thư viện PyMuPDF. Đang tự động tải và cài đặt qua pip...");
            $reqPath = base_path('app/Services/pdf_lab/requirements.txt');

            $installProcess = new \Symfony\Component\Process\Process([$python, '-m', 'pip', 'install', '-r', $reqPath]);
            $installProcess->setTimeout(300);
            $installProcess->run();

            if ($installProcess->isSuccessful()) {
                $notify('info', "Đã tự động cài đặt thành công thư viện PyMuPDF vào {$pythonVer}!");
            } else {
                $errMsg = trim($installProcess->getErrorOutput()) ?: trim($installProcess->getOutput());
                $notify('error', "Tự động cài đặt thư viện thất bại: {$errMsg}");
                $notify('line', "Vui lòng chạy lệnh: \"{$python}\" -m pip install -r " . $reqPath);
                return ['ready' => false, 'error' => 'Cài đặt PyMuPDF qua pip thất bại'];
            }
        }

        $provider = config('services.llm_provider', 'gemini');
        $keyName = $provider === 'kandes' ? 'KANDES_API_KEY' : 'GEMINI_API_KEY';
        $apiKey = config("services.{$provider}.api_key");
        if (!in_array($provider, ['gemini', 'kandes'], true)) {
            $notify('warn', 'LLM_PROVIDER phải là gemini hoặc kandes.');
        } elseif (!is_string($apiKey) || trim($apiKey) === '' || str_starts_with($apiKey, 'your_')) {
            $notify('warn', "Chưa cấu hình {$keyName} trong file .env.");
        }

        return ['ready' => true, 'python' => $python, 'version' => $pythonVer];
    }

    /**
     * Map-Reduce AI Summarizer cho file PDF báo cáo tiến độ.
     * Sử dụng Python PyMuPDF và nhà cung cấp LLM đã cấu hình.
     */
    public function summarizePdf(string $pdfPath, int $stage = 1, array $options = []): array
    {
        if (PHP_SAPI !== 'cli' && function_exists('set_time_limit')) {
            set_time_limit(420);
        }
        $provider = config('services.llm_provider', 'gemini');
        if (!in_array($provider, ['gemini', 'kandes'], true)) {
            $fallback = $this->fallbackSummarize($pdfPath, $stage, $options['review_context'] ?? []);
            $fallback['data']['processing_errors'] = [[
                'stage' => 'process', 'code' => 'invalid_request', 'message' => 'LLM_PROVIDER phải là gemini hoặc kandes.',
            ]];
            return $fallback;
        }
        $isKandes = $provider === 'kandes';
        $apiKey = $isKandes ? '' : (config('services.gemini.api_key') ?: env('GEMINI_API_KEY', ''));
        $model = $isKandes ? config('services.kandes.model') : config('services.gemini.model', 'gemini-3.5-flash-lite');
        $labPdf = strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION)) === 'pdf' && config('ai_processing.pdf_engine') === 'pdf_lab';
        $scriptPath = base_path($labPdf ? 'app/Services/lab_pdf_summary.py' : 'app/Services/ai_summarizer.py');

        $method = $options['method'] ?? 'map_reduce';

        $pythonBin = self::resolvePythonBinary();

        $cacheDirectory = storage_path('app/private/ai_cache');
        if ($isKandes) {
            $cacheDirectory .= '/kandes-'.substr(hash('sha256', (string) config('services.kandes.base_url')), 0, 16);
        }
        $cacheScope = $options['cache_scope'] ?? '';
        if (preg_match('/^[a-f0-9]{64}$/D', $cacheScope)) $cacheDirectory .= '/runs/'.$cacheScope;

        $command = [
            $pythonBin,
            $scriptPath,
            '--pdf',
            $pdfPath,
            '--stage',
            (string)$stage,
            '--method',
            $method,
            '--model',
            $model,
            '--cache-dir',
            $cacheDirectory,
            '--pipeline-version',
            config('ai_processing.pipeline_version', 'section-v6.1'),
        ];

        if ($labPdf) {
            $command[] = '--lab-settings';
            $command[] = json_encode(config('ai_processing.lab'), JSON_THROW_ON_ERROR);
        } else {
            foreach (config('ai_processing.summary', []) as $name => $value) {
                $command[] = '--'.str_replace('_', '-', $name);
                $command[] = (string) $value;
            }
        }
        if (!empty($options['document_identity'])) {
            $command[] = '--document-identity';
            $command[] = json_encode($options['document_identity'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if (!empty($options['review_context'])) {
            $command[] = '--review-context';
            $command[] = json_encode($options['review_context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        if (!empty($options['review_only'])) {
            $command[] = '--review-only';
        }
        if ($labPdf && !empty($options['fresh'])) {
            $command[] = '--fresh';
        }
        // Previous-version comparison belongs to the legacy pipeline only.
        // PDF Lab does not accept this flag; passing the entire prior JSON also
        // exceeds Windows command/environment limits before Python can start.
        if (!$labPdf && !empty($options['previous_version'])) {
            $command[] = '--previous-version';
            $command[] = json_encode($options['previous_version'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        $resultFile=$labPdf && !empty($options['progress_path']) ? $options['progress_path'].'.result.json' : null;
        $partialFile=!empty($options['progress_path']) ? $options['progress_path'].'.partial.json' : null;
        if ($partialFile && is_file($partialFile)) @unlink($partialFile);
        if ($resultFile) { $command[]='--output'; $command[]=$resultFile; if (is_file($resultFile)) @unlink($resultFile); }
        $errorReason = '';
        $processFailure = null;
        $parsedResult = null;
        $lastPartialHash = null;

        try {
            // Đảm bảo các biến môi trường thiết yếu của Windows (SystemRoot, PATH) được truyền cho tiến trình con
            // để tránh lỗi _Py_HashRandomization_Init khi chạy dưới web server Apache/PHP Laragon
            $processEnv = array_filter([
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\Windows',
                'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\Windows',
                'PATH' => (is_file($pythonBin) ? dirname($pythonBin) . PATH_SEPARATOR : '') . (getenv('PATH') ?: 'C:\Windows\system32;C:\Windows'),
                'TEMP' => getenv('TEMP') ?: 'C:\Windows\Temp',
                'TMP' => getenv('TMP') ?: 'C:\Windows\Temp',
                'PYTHONUTF8' => '1',
                'PYTHONIOENCODING' => 'utf-8',
                'GEMINI_API_KEY' => $apiKey,
                'GEMINI_FALLBACK_MODEL' => config('services.gemini.fallback_model'),
                'GROQ_API_KEY' => config('services.groq.api_key'),
                'GROQ_MODEL' => config('services.groq.model'),
                'AI_RATE_DB' => storage_path('app/private/ai_limits.sqlite'),
                'AI_RATE_SCOPE' => config('ai_processing.quota_scope'),
                'AI_SAFE_RPM' => (string) config('ai_processing.rpm',4),
                'AI_LITE_SAFE_RPM' => (string) config('ai_processing.lite_rpm',12),
                'AI_SAFE_TPM' => (string) config('ai_processing.tpm',200000),
                'AI_PROGRESS_FILE' => $options['progress_path'] ?? '',
                'AI_CANCEL_FILE' => empty($options['progress_path']) ? '' : $options['progress_path'].'.cancel',
                'AI_PARTIAL_RESULT_FILE' => is_callable($options['on_partial_result']??null) ? ($partialFile??'') : '',
                'AI_EXACT_TOKEN_COUNT' => config('ai_processing.count_tokens', true) ? '1' : '0',
                'AI_MAX_REQUEST_TOKENS' => (string) config('ai_processing.max_request_tokens', 12000),

            ]);

            $processEnv['LLM_PROVIDER'] = $provider;
            if ($isKandes) {
                $processEnv = array_merge($processEnv, self::kandesEnvironment(), [
                    'GEMINI_API_KEY' => false,
                    'GEMINI_FALLBACK_MODEL' => false,
                    'GROQ_API_KEY' => false,
                    'GROQ_MODEL' => false,
                    'AI_RATE_SCOPE' => 'kandes:'.hash('sha256', (string) config('services.kandes.base_url')),
                ]);
            }
            $process = Process::timeout(360)->env($processEnv);
            $metricBuffer = '';
            $onOutput = function ($type,$buffer) use ($partialFile,$options,&$lastPartialHash,$isKandes,&$metricBuffer) {
                if ($isKandes && $type === 'err') {
                    $lines = explode("\n", $metricBuffer.$buffer);
                    $metricBuffer = substr(array_pop($lines), -4096);
                    foreach ($lines as $line) self::logKandesMetric($line);
                }
                if (!$partialFile || !is_callable($options['on_partial_result']??null) || !is_file($partialFile)) return;
                $bytes=@file_get_contents($partialFile);
                if ($bytes===false) return;
                $digest=hash('sha256',$bytes);
                if ($digest===$lastPartialHash) return;
                $snapshot=json_decode($bytes,true);
                if (!is_array($snapshot) || empty($snapshot['success']) || !is_array($snapshot['data']??null)) return;
                try { ($options['on_partial_result'])($this->formatSummarizeResult($snapshot,$options['stage']??1), !empty($snapshot['review_only'])); $lastPartialHash=$digest; }
                catch (\Throwable $e) { Log::warning('AI partial publication deferred.', ['type'=>get_class($e)]); }
            };
            $result = $this->runAnalysisProcess($process, $command, $onOutput, $options['should_continue'] ?? null);
            if ($result === null) return ['success'=>false, 'cancelled'=>true];

            if ($result->successful()) {
                $output = $resultFile && is_file($resultFile) ? file_get_contents($resultFile) : preg_replace('/^AI_PARTIAL_READY\r?\n/m','',trim($result->output()));
                $data = json_decode($output, true);
                $parsedResult=$data;
                if (is_array($data) && !empty($data['success'])) {
                    return $this->formatSummarizeResult($data, $stage);
                }
                $errorReason = 'Python trả về kết quả không hợp lệ hoặc đánh dấu thất bại';
                Log::warning('AiSummaryService: invalid Python result.');
            } else {
                $errorReason = 'Lỗi thực thi tiến trình Python: ' . trim($result->errorOutput());
                Log::warning('AiSummaryService: Python process failed.', ['exit_code'=>$result->exitCode()]);
            }
        } catch (ProcessTimedOutException $e) {
            $processFailure = ['stage'=>'process','code'=>'timeout',
                'message'=>'Lượt xử lý nền đã quá thời gian. Các bước thành công trong bộ nhớ đệm được giữ; hệ thống sẽ tự tiếp tục.'];
            Log::warning('AiSummaryService: Python process timed out; checkpoints retained.');
        } catch (\Throwable $e) {
            $errorReason = 'Ngoại lệ khi gọi script Python: ' . $e->getMessage();
            $processFailure = ['stage'=>'process','code'=>'service_unavailable',
                'message'=>'Chưa hoàn tất tiến trình phân tích. Các bước đã lưu được giữ để thử tiếp.'];
            Log::error('AiSummaryService: Python process exception.', ['type'=>get_class($e)]);
        } finally {
            if ($resultFile && is_file($resultFile)) @unlink($resultFile);
        }

        $fallback = $this->fallbackSummarize($pdfPath, $stage, $options['review_context'] ?? []);
        $parsed = $parsedResult ?? (isset($result) && is_object($result) ? json_decode($result->output(),true) : []);
        $failure = $processFailure ?? $parsed['processing_error'] ?? ['stage'=>'process','code'=>'service_unavailable',
            'message'=>'Tiến trình chưa trả kết quả hợp lệ. Các bước đã lưu được giữ để thử tiếp.'];
        $fallback['data']['processing_errors'] = [$failure];
        if (!empty($options['progress_path']) && is_file($options['progress_path'])) {
            $progress = json_decode(file_get_contents($options['progress_path']), true) ?: [];
            $fallback['data']['analysis']['analyzed_chunks'] = max(0, (int) ($progress['map_done'] ?? 0));
            $fallback['data']['analysis']['total_chunks'] = max(0, (int) ($progress['map_total'] ?? 0));
            $fallback['data']['analysis']['completed_calls'] = max(0, (int) ($progress['completed_calls'] ?? 0));
            $fallback['data']['analysis']['reused_calls'] = max(0, (int) ($progress['reused_calls'] ?? 0));
        }
        return $fallback;
    }

    private function runAnalysisProcess($process, array $command, callable $onOutput, ?callable $shouldContinue)
    {
        if ($shouldContinue === null) return $process->run($command, $onOutput);
        if (!$shouldContinue()) return null;

        $child = $process->start($command, $onOutput);
        $deadline = hrtime(true) + 360_000_000_000;
        try {
            while ($child->running() && hrtime(true) < $deadline) {
                usleep(1_000_000);
                if (!$shouldContinue()) return null;
            }
            return $child->wait();
        } finally {
            if ($child->running()) $child->stop(0);
        }
    }

    /**
     * Giữ các mục linh hoạt, ngữ cảnh và nguồn; hỗ trợ JSON lịch sử.
     */
    public function formatSummarizeResult(array $raw, int $stage = 1): array
    {
        $data = $raw['data'] ?? [];
        $totalPages = $raw['total_pages'] ?? 1;
        $procTime = $raw['processing_time'] ?? 0.0;
        $source = $raw['source'] ?? 'gemini_map_reduce';

        $completedWork = $data['completed_work'] ?? [];
        $deliverables = $data['deliverables'] ?? [];
        $technicalLinks = $data['technical_links'] ?? [];
        $techStack = $data['tech_stack'] ?? [];
        $difficulties = $data['difficulties'] ?? [];
        $pendingItems = $data['pending_items'] ?? [];
        $nextSteps = $data['next_steps'] ?? [];
        $missingInfo = $data['missing_information'] ?? [];
        $citations = $data['citations'] ?? [];
        $aiRecs = $data['ai_recommendations'] ?? [];
        $overallSummary = $data['overall_summary'] ?? ($raw['standard_report']['tom_tat'] ?? "Báo cáo tiến độ Giai đoạn {$stage}.");
        $completionPct = isset($data['completion_percentage']) && $data['completion_percentage'] !== null 
            ? (int)$data['completion_percentage'] 
            : null;
        if ((int) ($data['schema_version'] ?? 0) >= 6) $completionPct = null;

        // Chuẩn hóa text cho 1-click Autofill
        $formattedCompleted = [];
        foreach ($completedWork as $item) {
            $pagesStr = !empty($item['source_locations']) ? ' (' . implode(', ', array_column($item['source_locations'], 'label')) . ')' : (!empty($item['source_pages']) ? ' (Trang ' . implode(', ', (array)$item['source_pages']) . ')' : '');
            $line = "• " . ($item['content'] ?? '') . $pagesStr;
            if (!empty($item['evidence'])) {
                $verNote = !empty($item['verified']) ? ' [Đã khớp tài liệu]' : ' [Cần đối chiếu]';
                $line .= "\n  - Dẫn chứng: " . $item['evidence'] . $verNote;
            }
            $formattedCompleted[] = $line;
        }

        $formattedDeliverables = [];
        foreach ($deliverables as $item) {
            $pagesStr = !empty($item['source_locations']) ? ' (' . implode(', ', array_column($item['source_locations'], 'label')) . ')' : (!empty($item['source_pages']) ? ' (Trang ' . implode(', ', (array)$item['source_pages']) . ')' : '');
            $line = "• " . ($item['content'] ?? '') . $pagesStr;
            $formattedDeliverables[] = $line;
        }

        $formattedDifficulties = [];
        foreach ($difficulties as $item) {
            $pagesStr = !empty($item['source_locations']) ? ' (' . implode(', ', array_column($item['source_locations'], 'label')) . ')' : (!empty($item['source_pages']) ? ' (Trang ' . implode(', ', (array)$item['source_pages']) . ')' : '');
            $line = "• " . ($item['content'] ?? '') . $pagesStr;
            if (!empty($item['evidence'])) {
                $line .= "\n  - Dẫn chứng: " . $item['evidence'];
            }
            $formattedDifficulties[] = $line;
        }

        $formattedNextSteps = [];
        foreach ($nextSteps as $item) {
            $pagesStr = !empty($item['source_locations']) ? ' (' . implode(', ', array_column($item['source_locations'], 'label')) . ')' : (!empty($item['source_pages']) ? ' (Trang ' . implode(', ', (array)$item['source_pages']) . ')' : '');
            $line = "• " . ($item['content'] ?? '') . $pagesStr;
            if (!empty($item['evidence'])) {
                $line .= "\n  - Dẫn chứng: " . $item['evidence'];
            }
            $formattedNextSteps[] = $line;
        }

        // Chuẩn hóa mảng chuỗi đơn giản
        $congViecHoanThanh = array_map(fn($i) => is_array($i) ? ($i['content'] ?? '') : (string)$i, $completedWork);
        $khoKhan = array_map(fn($i) => is_array($i) ? ($i['content'] ?? '') : (string)$i, $difficulties);
        $keHoachTiepTheo = array_map(fn($i) => is_array($i) ? ($i['content'] ?? '') : (string)$i, $nextSteps);
        $noiDungChuaRo = array_map(fn($i) => is_array($i) ? ($i['content'] ?? '') : (string)$i, $missingInfo);
        $ketQuaDatDuoc = array_map(fn($i) => is_array($i) ? ($i['content'] ?? '') : (string)$i, $deliverables ?: $completedWork);
        $canhBao = $data['warnings'] ?? $aiRecs;

        $sectionsData = [
            'overall_summary' => $overallSummary,
            'completion_percentage' => $completionPct,
            'completed_work' => $completedWork,
            'deliverables' => $deliverables,
            'technical_links' => $technicalLinks,
            'tech_stack' => $techStack,
            'difficulties' => $difficulties,
            'pending_items' => $pendingItems,
            'next_steps' => $nextSteps,
            'missing_information' => $missingInfo,
            'citations' => $citations,
            'ai_recommendations' => $aiRecs,
            'analysis' => $data['analysis'] ?? ['version' => 2, 'source' => $source, 'status' => 'partial', 'note' => 'Chưa có thông tin kiểm chứng đầy đủ.'],
            'document_outline' => $data['document_outline'] ?? [],
            'completion_basis' => $data['completion_basis'] ?? null,
        ];
        if (isset($data['requirements_review']) && is_array($data['requirements_review'])) {
            $sectionsData['requirements_review'] = $data['requirements_review'];
        }
        $sectionsData['processing_errors'] = $data['processing_errors'] ?? [];
        $sectionsData['document_content'] = $data['document_content'] ?? [];
        foreach (['summary_sections', 'additional_sections', 'summary_scope'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) $sectionsData[$field] = $data[$field];
        }
        $sectionsData['schema_version'] = (int) ($data['schema_version'] ?? 3);
        $sectionsData['document_type'] = $data['document_type'] ?? $data['analysis']['document_type'] ?? 'pdf';
        $sectionsData = array_intersect_key($sectionsData, array_flip([
            'overall_summary', 'document_content', 'summary_sections', 'additional_sections', 'summary_scope', 'completed_work', 'difficulties', 'next_steps', 'analysis',
            'citations', 'document_outline', 'requirements_review', 'schema_version', 'document_type', 'completion_percentage', 'completion_basis', 'processing_errors',
        ]));
        if ((int) ($data['schema_version'] ?? 0) >= 6) {
            foreach (['document_id', 'document_version_id', 'source_identity', 'analysis_status', 'overview_summary',
                '_source_hash', 'chapters', 'caption_index', 'source_navigation', 'content_review', 'analysis_metadata', 'validation', 'teacher_requirements', 'requirements_document', 'teacher_requirements_status', 'requirements_links_status', 'requirements_provenance', '_analysis_history'] as $field) {
                if (array_key_exists($field, $data)) $sectionsData[$field] = $data[$field];
            }
            unset($sectionsData['difficulties'], $sectionsData['next_steps'], $sectionsData['completion_percentage'],
                $sectionsData['completion_basis']);
        }

        if (AiResultMerger::validProgress($data['progress_v1'] ?? null)) {
            $sectionsData['progress_v1'] = $data['progress_v1'];
            $sectionsData['progress_display'] = is_array($data['progress_display'] ?? null) ? $data['progress_display'] : [];
            $sectionsData['progress_v1_status'] = ($data['progress_v1_status'] ?? '') === 'complete'
                ? 'complete' : 'partial';
        } elseif (isset($data['progress_v1_status']) || array_key_exists('progress_v1', $data)) {
            $sectionsData['progress_v1_status'] = 'unavailable';
        }

        $stdReport = $raw['standard_report'] ?? ((int) ($data['schema_version'] ?? 0) >= 5 ? [
            'tom_tat' => $overallSummary,
            'sections' => $data['summary_sections'] ?? [],
            'additional_sections' => $data['additional_sections'] ?? [],
        ] : [
            'tom_tat' => $overallSummary,
            'cong_viec_hoan_thanh' => array_values(array_filter($congViecHoanThanh)),
            'ket_qua_dat_duoc'     => array_values(array_filter($ketQuaDatDuoc)),
            'kho_khan'             => array_values(array_filter($khoKhan)),
            'ke_hoach_tiep_theo'   => array_values(array_filter($keHoachTiepTheo)),
            'moc_thoi_gian'        => $data['moc_thoi_gian'] ?? ["Giai đoạn {$stage}"],
            'noi_dung_chua_ro'     => array_values(array_filter($noiDungChuaRo)),
            'canh_bao'             => is_array($canhBao) ? $canhBao : [],
            'nguon_tham_chieu'     => array_map(fn($c) => 'Trang ' . ($c['page'] ?? 1), $citations) ?: ['Trang 1'],
        ]);
        if ((int) ($data['schema_version'] ?? 0) >= 6) {
            $stdReport = ['tom_tat'=>$overallSummary, 'chapters'=>$data['chapters'] ?? [], 'completed_work'=>$completedWork];
        }

        $metrics = $raw['metrics'] ?? [
            'total_pages' => $totalPages,
            'valid_pages' => $totalPages,
            'char_count' => 0,
            'word_count' => 0,
            'chunk_count' => 1,
            'processing_time' => $procTime,
        ];

        return [
            'success' => true,
            'source' => $source,
            'total_pages' => $totalPages,
            'processing_time' => $procTime,
            'completion_percentage' => $completionPct,
            // Cấu trúc báo cáo khoa học chuẩn
            'standard_report'      => $stdReport,
            'metrics'              => $metrics,
            // Các trường tương thích với bản lưu và chức năng cũ.
            'cong_viec_hoan_thanh' => array_values(array_filter($congViecHoanThanh)),
            'ket_qua_dat_duoc'     => array_values(array_filter($ketQuaDatDuoc)),
            'kho_khan'             => array_values(array_filter($khoKhan)),
            'ke_hoach_tiep_theo'   => array_values(array_filter($keHoachTiepTheo)),
            'noi_dung_chua_ro'     => array_values(array_filter($noiDungChuaRo)),
            'danh_gia_tien_do'     => $overallSummary,
            'canh_bao'             => is_array($canhBao) ? $canhBao : [],
            'deliverables'         => $deliverables,
            'technical_links'      => $technicalLinks,
            'tech_stack'           => $techStack,
            'pending_items'        => $pendingItems,
            'citations'            => $citations,
            'ai_recommendations'   => $aiRecs,

            'data' => $sectionsData,
            'formatted' => [
                'completed_work' => implode("\n\n", $formattedCompleted),
                'deliverables'   => implode("\n\n", $formattedDeliverables),
                'difficulties'   => implode("\n\n", $formattedDifficulties),
                'next_steps'     => implode("\n\n", $formattedNextSteps),
                'overall_summary'=> $overallSummary,
            ]
        ];
    }

    /**
     * Fallback khi không chạy được python hoặc file bị lỗi.
     * TUYỆT ĐỐI KHÔNG TỰ BỊA NỘI DUNG / KHÔNG TỰ GÁN TECH STACK HOẶC KHÓ KHĂN GIẢ.
     */
    private function fallbackSummarize(string $pdfPath, int $stage = 1, array $context = []): array
    {
        $explanation = 'Hệ thống đã tiếp nhận báo cáo nhưng chưa đọc và tóm tắt được đầy đủ. Bạn có thể kiểm tra tệp hoặc thử phân tích lại.';
        ksort($context);
        $contextHash = hash('sha256', json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $documentType = strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION)) === 'docx' ? 'docx' : 'pdf';

        return $this->formatSummarizeResult([
            'success' => true,
            'source' => 'system_fallback',
            'total_pages' => 0,
            'processing_time' => 0,
            'data' => [
                'schema_version' => 6,
                'document_type' => $documentType,
                'document_content' => [], 'chapters' => [], 'analysis_status'=>'partial',
                'analysis' => ['version'=>6, 'source'=>'system_fallback', 'status'=>'partial',
                    'total_pages'=>0, 'total_units'=>0, 'analyzed_chunks'=>0, 'total_chunks'=>0,
                    'text_complete'=>false, 'reduce_completed'=>false, 'note'=>$explanation],
                'completed_work' => [],
                'deliverables' => [],
                'technical_links' => [],
                'tech_stack' => [],
                'difficulties' => [],
                'pending_items' => [],
                'next_steps' => [],
                'missing_information' => [
                    'Chưa trích xuất được nội dung tự động từ AI. Giảng viên vui lòng xem trực tiếp tệp PDF báo cáo đính kèm.'
                ],
                'citations' => [],
                'ai_recommendations' => [
                    'Kiểm tra lại cấu hình GEMINI_API_KEY trong tệp .env và kích hoạt lại chức năng phân tích.',
                    'Đảm bảo tệp PDF báo cáo có nội dung văn bản (không phải ảnh chụp scan chưa OCR).'
                ],
                'overall_summary' => $explanation,
                'completion_percentage' => null,
            ]
        ], $stage);
    }
}
