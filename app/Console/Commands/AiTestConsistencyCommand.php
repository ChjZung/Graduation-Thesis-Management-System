<?php

namespace App\Console\Commands;

use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;
use Illuminate\Console\Command;

class AiTestConsistencyCommand extends Command
{
    protected $signature = 'ai:test-consistency 
                            {report_id? : ID MaKhoi của báo cáo cần kiểm tra} 
                            {--runs=2 : Số lần phân tích lại liên tiếp} 
                            {--save : Lưu lại lịch sử và chỉ số nhất quán vào cơ sở dữ liệu}';

    protected $description = 'Kiểm tra và đánh giá tính nhất quán/tất định giữa các lần phân tích AI';

    public function handle(AiSummaryService $aiService, AiReportProcessingService $processing): int
    {
        $reportId = $this->argument('report_id');
        $report = null;

        if ($reportId) {
            $report = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')->find($reportId);
            if (!$report) {
                $this->error("Không tìm thấy báo cáo với mã MaKhoi = {$reportId}");
                return 1;
            }
        } else {
            $report = KhoiBaoCaoTienDo::with('dotNop.giaiDoan')
                ->whereNotNull('TenFile')
                ->orderByDesc('MaKhoi')
                ->first();

            if (!$report) {
                $this->error('Không tìm thấy báo cáo nào có tệp PDF trong cơ sở dữ liệu.');
                return 1;
            }
        }

        try {
            $pdfPath = $processing->path($report);
        } catch (\Throwable $e) {
            $this->error("Không thể lấy đường dẫn tệp PDF của báo cáo #{$report->MaKhoi}: " . $e->getMessage());
            return 1;
        }

        $runsCount = max(2, (int) $this->option('runs'));
        $shouldSave = (bool) $this->option('save');

        $this->info("================================================================================");
        $this->info(" KIỂM TRA TÍNH NHẤT QUÁN PHÂN TÍCH AI (AI CONSISTENCY BENCHMARK)");
        $this->info("================================================================================");
        $this->line("Báo cáo: #{$report->MaKhoi} - {$report->TenFile}");
        $this->line("Đường dẫn PDF: {$pdfPath}");
        $this->line("Số lượt phân tích: {$runsCount} lần (chế độ FRESH - bỏ qua cache)");
        $this->line("Ghi dữ liệu vào DB: " . ($shouldSave ? 'Có' : 'Chỉ benchmark bộ nhớ'));
        $this->newLine();

        $stage = $report->dotNop?->giaiDoan?->ThuTu ?? 1;
        $context = $report->dotNop?->nguCanhDoiChieuAi() ?? [];
        $identity = $processing->identity($report);

        $results = [];

        for ($i = 1; $i <= $runsCount; $i++) {
            $this->info("--> Đang chạy phân tích Lần {$i}/{$runsCount}...");
            $startTime = microtime(true);

            try {
                $output = $aiService->summarizePdf($pdfPath, $stage, [
                    'fresh' => true,
                    'review_context' => $context,
                    'document_identity' => $identity,
                ]);

                $duration = round(microtime(true) - $startTime, 2);

                if (empty($output['success'])) {
                    $this->error("    Lần {$i} thất bại: " . ($output['error'] ?? 'Không rõ lỗi'));
                    return 1;
                }

                $data = $output['data'] ?? [];
                $summary = $data['overall_summary'] ?? ($data['tom_tat'] ?? '');
                $summaryHash = md5($summary);

                $completedList = array_map(function ($w) {
                    return is_array($w) ? ($w['content'] ?? '') : (string) $w;
                }, $data['completed_work'] ?? []);

                $chaptersCount = count($data['chapters'] ?? []);

                $results[] = [
                    'run' => $i,
                    'duration' => $duration,
                    'summary' => $summary,
                    'summary_len' => mb_strlen($summary),
                    'summary_hash' => $summaryHash,
                    'completed_work' => $completedList,
                    'completed_count' => count($completedList),
                    'chapters_count' => $chaptersCount,
                    'raw_output' => $output,
                ];

                $this->line("    Hoàn tất trong {$duration}s | Hash: " . substr($summaryHash, 0, 8) . "... | " . count($completedList) . " công việc | {$chaptersCount} chương");

                if ($shouldSave) {
                    $aiService->saveVersionSummary($report, $output);
                    $report->refresh();
                }

            } catch (\Throwable $e) {
                $this->error("    Lỗi ngoại lệ lần {$i}: " . $e->getMessage());
                return 1;
            }
        }

        $this->newLine();
        $this->info("================================================================================");
        $this->info(" BẢNG TỔNG HỢP KẾT QUẢ CÁC LẦN PHÂN TÍCH");
        $this->info("================================================================================");

        $tableData = array_map(function ($r) {
            return [
                "Lần " . $r['run'],
                $r['duration'] . 's',
                substr($r['summary_hash'], 0, 10),
                $r['summary_len'] . ' ký tự',
                $r['completed_count'] . ' mục',
                $r['chapters_count'] . ' chương',
            ];
        }, $results);

        $this->table(['Lần chạy', 'Thời gian', 'MD5 Hash', 'Độ dài tóm tắt', 'Công việc', 'Chương/Phần'], $tableData);

        // Đối chiếu cặp: Lần 1 so với Lần 2 (và các lần kế tiếp)
        $this->newLine();
        $this->info("================================================================================");
        $this->info(" ĐỐI CHIẾU ĐỘ NHẤT QUÁN GIỮA LẦN 1 VÀ LẦN 2");
        $this->info("================================================================================");

        $run1 = $results[0];
        $run2 = $results[1];

        similar_text($run1['summary'], $run2['summary'], $simPct);
        $simPct = round($simPct, 2);

        $c1 = $run1['completed_work'];
        $c2 = $run2['completed_work'];
        $intersect = array_intersect($c1, $c2);
        $maxC = max(1, max(count($c1), count($c2)));
        $matchRate = round((count($intersect) / $maxC) * 100, 2);

        $identical = ($run1['summary_hash'] === $run2['summary_hash']);

        $this->line("1. Khớp mã băm tóm tắt (Identical Hash): " . ($identical ? '<info>TRÙNG KHỚP TUYỆT ĐỐI (100%)</info>' : '<comment>Khác nhau</comment>'));
        $this->line("2. Tỷ lệ tương đồng nội dung tóm tắt: " . ($simPct >= 90 ? "<info>{$simPct}%</info>" : "<comment>{$simPct}%</comment>"));
        $this->line("3. Tỷ lệ trùng khớp công việc đã hoàn thành: " . ($matchRate >= 90 ? "<info>{$matchRate}%</info>" : "<comment>{$matchRate}%</comment>"));
        $this->line("4. Chênh lệch số chương/phần bóc tách: " . abs($run1['chapters_count'] - $run2['chapters_count']) . " chương");

        $this->newLine();
        if ($identical) {
            $this->info("==> [KẾT LUẬN] Mô hình hoạt động hoàn toàn tất định và nhất quán 100% giữa các lần phân tích.");
        } elseif ($simPct >= 80) {
            $this->info("==> [KẾT LUẬN] Mô hình đạt tính nhất quán cao ({$simPct}%). Nội dung cốt lõi và các công việc chính được bảo toàn ổn định.");
        } else {
            $this->comment("==> [KẾT LUẬN] Có sự biến thiên giữa các lần chạy ({$simPct}% tương đồng).");
        }

        return 0;
    }
}
