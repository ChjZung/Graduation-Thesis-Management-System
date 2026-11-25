<?php

namespace App\Console\Commands;

use App\Services\AiSummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class AiKandesCheckCommand extends Command
{
    protected $signature = 'ai:kandes-check {--send : Cho phép gửi tối đa hai yêu cầu thử tới Kandes}';

    protected $description = 'Kiểm tra phản hồi văn bản và JSON của Kandes bằng prompt ngắn';

    public function handle(): int
    {
        if (!$this->option('send')) {
            $this->line('Chưa gửi yêu cầu. Dùng --send để chạy phép thử có thể phát sinh phí.');

            return self::SUCCESS;
        }

        $result = Process::path(app_path('Services/pdf_lab'))->timeout(70)
            ->env(AiSummaryService::kandesEnvironment() + ['PYTHONUTF8' => '1'])
            ->run([AiSummaryService::resolvePythonBinary(), '-m', 'ai_runtime.kandes', '--probe']);
        foreach (explode("\n", $result->errorOutput()) as $line) {
            AiSummaryService::logKandesMetric($line);
        }
        $response = json_decode($result->output(), true);
        if (!$result->successful() || empty($response['ok'])) {
            $this->error($response['message'] ?? 'Tiến trình kiểm tra Kandes chưa trả kết quả.');

            return self::FAILURE;
        }

        $this->info('Kandes trả văn bản và JSON hợp lệ trên model '.$response['model'].'.');

        return self::SUCCESS;
    }
}

