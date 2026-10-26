<?php

namespace App\Services;

use App\Models\BaoCaoTienDo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class AiSummaryService
{
    private const GEMINI_API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent';
    private const MAX_RETRIES     = 3;
    private const CHUNK_SIZE      = 12000;
    private const CHUNK_OVERLAP   = 350;

    public function saveVersionSummary(\App\Models\KhoiBaoCaoTienDo $report, array $result, array $options = []): void
    {
        // Each submission keeps its own JSON. The legacy table has ONE snapshot per MaBaoCao.
        \Illuminate\Support\Facades\DB::transaction(function () use ($report, $result, $options) {
            $report = \App\Models\KhoiBaoCaoTienDo::lockForUpdate()->findOrFail($report->MaKhoi);
            $old = app(AiReportProcessingService::class)->payload($report);
            if (isset($options['token']) && ($old['_processing']['token'] ?? '') !== $options['token']) return;
            $merged = (new AiResultMerger)->merge($old,$result['data'],!empty($options['review_only']));
            if (isset($old['_processing'])) $merged['_processing'] = $old['_processing'];
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
     * Map-Reduce AI Summarizer cho file PDF báo cáo tiến độ.
     * Sử dụng Python PyMuPDF + Google Gemini API (xác thực qua header x-goog-api-key).
     * Hỗ trợ cấu hình tham số thực nghiệm: chunk_size, chunk_overlap, method, model.
     */
    public function summarizePdf(string $pdfPath, int $stage = 1, array $options = []): array
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(420);
        }
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');
        $scriptPath = base_path('app/Services/ai_summarizer.py');

        $chunkSize = $options['chunk_size'] ?? self::CHUNK_SIZE;
        $chunkOverlap = $options['chunk_overlap'] ?? self::CHUNK_OVERLAP;
        $method = $options['method'] ?? 'map_reduce';

        $pythonBin = env('PYTHON_PATH') ?: (file_exists('C:\laragon\bin\python\python-3.10\python.exe') ? 'C:\laragon\bin\python\python-3.10\python.exe' : 'python');

        $command = [
            $pythonBin,
            $scriptPath,
            '--pdf',
            $pdfPath,
            '--stage',
            (string)$stage,
            '--chunk-size',
            (string)$chunkSize,
            '--chunk-overlap',
            (string)$chunkOverlap,
            '--method',
            $method,
            '--model',
            $model,
            '--cache-dir',
            storage_path('app/private/ai_cache'),
        ];

        if (!empty($options['review_context'])) {
            $command[] = '--review-context';
            $command[] = json_encode($options['review_context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        if (!empty($options['review_only'])) {
            $command[] = '--review-only';
        }
        if (!empty($options['previous_version'])) {
            $command[] = '--previous-version';
            $command[] = json_encode($options['previous_version'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        $errorReason = '';

        try {
            // Đảm bảo các biến môi trường thiết yếu của Windows (SystemRoot, PATH) được truyền cho tiến trình con
            // để tránh lỗi _Py_HashRandomization_Init khi chạy dưới web server Apache/PHP Laragon
            $processEnv = array_filter([
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\Windows',
                'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\Windows',
                'PATH' => getenv('PATH') ?: 'C:\Windows\system32;C:\Windows;C:\laragon\bin\python\python-3.10',
                'TEMP' => getenv('TEMP') ?: 'C:\Windows\Temp',
                'TMP' => getenv('TMP') ?: 'C:\Windows\Temp',
                'PYTHONUTF8' => '1',
                'PYTHONIOENCODING' => 'utf-8',
                'GEMINI_API_KEY' => $apiKey,
                'AI_RATE_DB' => storage_path('app/private/ai_limits.sqlite'),
                'AI_RATE_SCOPE' => config('ai_processing.quota_scope'),
                'AI_SAFE_RPM' => (string) config('ai_processing.rpm',4),
                'AI_LITE_SAFE_RPM' => (string) config('ai_processing.lite_rpm',12),
                'AI_SAFE_TPM' => (string) config('ai_processing.tpm',200000),
                'AI_PROGRESS_FILE' => $options['progress_path'] ?? '',

            ]);

            // Cho phép thời gian xử lý tối đa 240s cho các tài liệu PDF dài (Map-Reduce đa tầng)
            $result = Process::timeout(360)->env($processEnv)->run($command);

            if ($result->successful()) {
                $output = trim($result->output());
                $data = json_decode($output, true);
                if (is_array($data) && !empty($data['success'])) {
                    return $this->formatSummarizeResult($data, $stage);
                }
                $errorReason = 'Python trả về kết quả không hợp lệ hoặc đánh dấu thất bại';
                Log::warning('AiSummaryService: invalid Python result.');
            } else {
                $errorReason = 'Lỗi thực thi tiến trình Python: ' . trim($result->errorOutput());
                Log::warning('AiSummaryService: Python process failed.', ['exit_code'=>$result->exitCode()]);
            }
        } catch (\Throwable $e) {
            $errorReason = 'Ngoại lệ khi gọi script Python: ' . $e->getMessage();
            Log::error('AiSummaryService: Python process exception.', ['type'=>get_class($e)]);
        }

        $fallback = $this->fallbackSummarize($pdfPath, $stage, $options['review_context'] ?? []);
        $parsed = isset($result) && is_object($result) ? json_decode($result->output(),true) : [];
        $failure = $parsed['processing_error'] ?? ['stage'=>'process','code'=>'extraction_failed','message'=>'Không đọc được tài liệu hoặc chưa chạy được tiến trình phân tích.'];
        $fallback['data']['processing_errors'] = [$failure];
        return $fallback;
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
                'schema_version' => 5,
                'document_type' => $documentType,
                'document_content' => [], 'summary_sections' => [], 'additional_sections' => [],
                'summary_scope' => ['mode'=>'document_fallback', 'context_hash'=>$contextHash,
                    'note'=>'Chưa xác định được nội dung trọng tâm; không dùng trạng thái lỗi để kết luận sinh viên thiếu yêu cầu.'],
                'analysis' => ['version'=>5, 'source'=>'system_fallback', 'status'=>'partial',
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

    /**
     * Tương thích ngược với các controller cũ gọi generate(BaoCaoTienDo $baoCao).
     */
    public function generate(BaoCaoTienDo $baoCao): array
    {
        if (!empty($baoCao->DuongDanFile)) {
            $fullPath = Storage::disk('public')->path($baoCao->DuongDanFile);
            if (file_exists($fullPath)) {
                $aiRes = $this->summarizePdf($fullPath, $baoCao->LanBaoCao ?? 1);
                $formatted = $aiRes['formatted'];

                $completedText = !empty($formatted['completed_work']) ? $formatted['completed_work'] : 'Chưa có thông tin hoàn thành trong văn bản';
                $diffText = !empty($formatted['difficulties']) ? $formatted['difficulties'] : 'Tài liệu không ghi nhận khó khăn';
                $nextText = !empty($formatted['next_steps']) ? $formatted['next_steps'] : 'Tài liệu không ghi nhận kế hoạch tiếp theo';

                return [
                    'CongViecDaHoanThanh' => $completedText,
                    'KhoKhan'             => $diffText,
                    'KeHoachTuanToi'      => $nextText,
                    'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$baoCao->LanBaoCao}: {$baoCao->TieuDe}**\n\n✅ **Đã hoàn thành:**\n{$completedText}\n\n⚠️ **Khó khăn:**\n{$diffText}\n\n📅 **Kế hoạch tiếp theo:**\n{$nextText}",
                    'TomTatJson'          => $aiRes['data'],
                    'DoTinCayAI'          => $aiRes['source'] === 'gemini_map_reduce' ? 95.00 : 85.00,
                    'NgayTomTat'          => now(),
                    'TrangThai'           => 'Đã tạo',
                ];
            }
        }

        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if ($apiKey) {
            return $this->generateWithGemini($baoCao, $apiKey);
        }

        return $this->generateMock($baoCao);
    }

    /**
     * Gọi trực tiếp Gemini API qua HTTP Client trong Laravel (Header x-goog-api-key)
     * Ưu tiên Gemini 3.5 Flash, dự phòng Gemini 3.5 Flash-Lite khi dịch vụ lỗi.
     */
    private function generateWithGemini(BaoCaoTienDo $baoCao, string $apiKey): array
    {
        $vanBan = $this->extractText($baoCao);
        $prompt = $this->buildPrompt($baoCao->LanBaoCao, $vanBan);
        $configuredModel = config('services.gemini.model', 'gemini-3.5-flash-lite');

        $candidateModels = array_unique([$configuredModel, 'gemini-3.5-flash-lite']);

        foreach ($candidateModels as $currentModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent";

            for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
                try {
                    $response = Http::withHeaders([
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(45)->post($url, [
                        'contents' => [[
                            'parts' => [['text' => $prompt]],
                        ]],
                        'generationConfig' => array_merge([
                            'maxOutputTokens' => 4096,
                            'responseMimeType' => 'application/json',
                        ], (str_starts_with($currentModel, 'gemini-3.8') || str_starts_with($currentModel, 'gemini-3.6'))
                            ? ['thinkingConfig' => ['thinkingLevel' => 'low']] : []),
                    ]);

                    if ($response->successful()) {
                        $rawText = collect($response->json('candidates.0.content.parts', []))
                            ->reject(fn ($part) => !empty($part['thought']))
                            ->pluck('text')->implode('');
                        $cleaned = trim($rawText);
                        if (str_starts_with($cleaned, '```')) {
                            $lines = explode("\n", $cleaned);
                            if (str_starts_with($lines[0], '```')) {
                                array_shift($lines);
                            }
                            if (!empty($lines) && trim(end($lines)) === '```') {
                                array_pop($lines);
                            }
                            $cleaned = trim(implode("\n", $lines));
                        }

                        $data = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);

                        $cong = $data['CongViecDaHoanThanh'] ?? 'Văn bản chưa nêu rõ chi tiết công việc hoàn thành.';
                        $kho  = $data['KhoKhan'] ?? 'Tài liệu không ghi nhận khó khăn đặc biệt.';
                        $ke   = $data['KeHoachTuanToi'] ?? 'Tài liệu không đề cập đến kế hoạch cụ thể.';

                        return [
                            'CongViecDaHoanThanh' => $cong,
                            'KhoKhan'             => $kho,
                            'KeHoachTuanToi'      => $ke,
                            'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$baoCao->LanBaoCao} ({$currentModel})**\n\n✅ **Đã hoàn thành:** {$cong}\n\n⚠️ **Khó khăn:** {$kho}\n\n📅 **Kế hoạch tiếp theo:** {$ke}",
                            'DoTinCayAI'          => 95.00,
                            'NgayTomTat'          => now(),
                            'TrangThai'           => 'Đã tạo',
                        ];
                    }

                    $status = $response->status();
                    // Lỗi client 400, 401, 403 không retry model này
                    if (in_array($status, [400, 401, 403])) {
                        Log::error("AiSummaryService: Gemini API client error HTTP {$status}: " . $response->body());
                        break 2;
                    }

                    // Nếu 404 (model dừng), chuyển ngay sang candidate model tiếp theo
                    if ($status === 404) {
                        Log::warning("AiSummaryService: Model {$currentModel} không tồn tại hoặc đã dừng (404), chuyển model dự phòng.");
                        break;
                    }

                    // 429 hoặc 503: chờ theo exponential backoff
                    sleep($attempt * 2);
                } catch (\Throwable $e) {
                    Log::warning("AiSummaryService: Lần thử {$attempt}/" . self::MAX_RETRIES . " model {$currentModel} thất bại: " . $e->getMessage());
                    sleep($attempt * 2);
                }
            }
        }

        return $this->generateMock($baoCao);
    }

    private function extractText(BaoCaoTienDo $baoCao): string
    {
        $parts = [];
        if (!empty($baoCao->NoiDungBaoCao)) {
            $parts[] = "Ghi chú sinh viên: " . mb_substr($baoCao->NoiDungBaoCao, 0, 1000);
        }
        if (!empty($baoCao->LinkCode)) {
            $parts[] = "Link source code: " . $baoCao->LinkCode;
        }
        return implode("\n\n", $parts) ?: "Báo cáo Mốc {$baoCao->LanBaoCao}";
    }

    private function buildPrompt(int $lan, string $vanBan): string
    {
        return <<<PROMPT
Bạn là trợ lý AI thẩm định báo cáo tiến độ khóa luận tốt nghiệp (Mốc {$lan}/6).
Đọc nội dung dưới đây và trích xuất thông tin ĐÚNG SỰ THỰC, KHÔNG TỰ BỊA NỘI DUNG.
Nếu tài liệu không nhắc đến khó khăn hoặc kế hoạch, hãy ghi rõ "Tài liệu không đề cập".

Trả về ĐÚNG FORMAT JSON sau (không thêm markdown ngoài JSON):
{
  "CongViecDaHoanThanh": "Mô tả ngắn gọn công việc đã hoàn thành",
  "KhoKhan": "Các khó khăn thực tế gặp phải (nếu có)",
  "KeHoachTuanToi": "Kế hoạch cụ thể cho giai đoạn tiếp theo (nếu có)"
}

NỘI DUNG BÁO CÁO:
{$vanBan}
PROMPT;
    }

    private function generateMock(BaoCaoTienDo $baoCao): array
    {
        $lan = $baoCao->LanBaoCao ?? 1;

        return [
            'CongViecDaHoanThanh' => "Tiếp nhận và rà soát báo cáo tiến độ Mốc {$lan}. (Chưa kích hoạt AI trực tuyến)",
            'KhoKhan'             => "Chưa ghi nhận khó khăn qua hệ thống tự động.",
            'KeHoachTuanToi'      => "Thực hiện theo kế hoạch khóa luận và hướng dẫn của GVHD.",
            'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$lan} [Dữ liệu cơ bản - Chưa qua Gemini]**\n\nHệ thống đã lưu nhận báo cáo. Vui lòng cấu hình GEMINI_API_KEY trong .env để kích hoạt thẩm định tự động chuyên sâu.",
            'DoTinCayAI'          => 70.00,
            'NgayTomTat'          => now(),
            'TrangThai'           => 'Đã tạo',
        ];
    }
}
