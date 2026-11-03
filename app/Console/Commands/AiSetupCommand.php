<?php

namespace App\Console\Commands;

use App\Services\AiSummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiSetupCommand extends Command
{
    protected $signature = 'ai:setup {--check-only : Chỉ kiểm tra không tự động cài đặt}';
    protected $description = 'Kiểm tra và tự động thiết lập toàn bộ môi trường AI (Python, PyMuPDF, Database, Storage, API Key)';

    public function handle(): int
    {
        $this->components->info('Bắt đầu kiểm tra và thiết lập môi trường AI cho dự án...');
        $rows = [];
        $hasError = false;

        // 1. Python Environment
        $python = AiSummaryService::resolvePythonBinary();
        $testProcess = new \Symfony\Component\Process\Process([$python, '--version']);
        $testProcess->run();
        if ($testProcess->isSuccessful()) {
            $ver = trim($testProcess->getOutput());
            $rows[] = ['Trình thông dịch Python', $python . " ({$ver})", 'Sẵn sàng'];
        } else {
            $hasError = true;
            $rows[] = ['Trình thông dịch Python', $python, 'LỖI (Không thực thi được)'];
        }

        // 2. Thư viện PyMuPDF
        $checkMupdf = new \Symfony\Component\Process\Process([$python, '-c', 'import pymupdf; print(pymupdf.__version__)']);
        $checkMupdf->run();
        if ($checkMupdf->isSuccessful()) {
            $mupdfVer = trim($checkMupdf->getOutput());
            $rows[] = ['Thư viện PyMuPDF', "v{$mupdfVer}", 'Sẵn sàng'];
        } else {
            if ($this->option('check-only')) {
                $rows[] = ['Thư viện PyMuPDF', 'Chưa cài đặt', 'CẦN CÀI ĐẶT'];
                $hasError = true;
            } else {
                $this->components->warn('Chưa tìm thấy PyMuPDF. Đang tiến hành cài đặt tự động qua pip...');
                $reqPath = base_path('app/Services/pdf_lab/requirements.txt');
                $installProcess = new \Symfony\Component\Process\Process([$python, '-m', 'pip', 'install', '-r', $reqPath]);
                $installProcess->setTimeout(300);
                $installProcess->run();

                if ($installProcess->isSuccessful()) {
                    $rows[] = ['Thư viện PyMuPDF', 'Đã tự động cài đặt thành công', 'Sẵn sàng'];
                } else {
                    $hasError = true;
                    $rows[] = ['Thư viện PyMuPDF', 'Cài đặt thất bại', 'LỖI: ' . trim($installProcess->getErrorOutput())];
                }
            }
        }

        // 3. Storage Directories & Permissions
        $dirs = [
            storage_path('app/private'),
            storage_path('app/private/ai_cache'),
            storage_path('app/public/baocao'),
            storage_path('framework/cache'),
        ];
        $dirOk = true;
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (!is_writable($dir)) {
                $dirOk = false;
            }
        }
        $rows[] = ['Thư mục lưu trữ & Cache', 'storage/app/private & public/baocao', $dirOk ? 'Sẵn sàng (Writable)' : 'CẢNH BÁO (Quyền ghi)'];

        // 4. Database & Tables
        try {
            DB::connection()->getPdo();
            $hasKhoi = Schema::hasTable('khoibaocaotiendo');
            $hasJobs = Schema::hasTable('jobs');
            if ($hasKhoi && $hasJobs) {
                $rows[] = ['Cơ sở dữ liệu & Bảng', 'Đã kết nối, có bảng khoibaocaotiendo & jobs', 'Sẵn sàng'];
            } else {
                $missing = [];
                if (!$hasKhoi) $missing[] = 'khoibaocaotiendo';
                if (!$hasJobs) $missing[] = 'jobs';
                $rows[] = ['Cơ sở dữ liệu & Bảng', 'Thiếu bảng: ' . implode(', ', $missing), 'CẦN MIGRATE'];
                $hasError = true;
            }
        } catch (\Throwable $e) {
            $hasError = true;
            $rows[] = ['Cơ sở dữ liệu', 'Không thể kết nối CSDL', 'LỖI'];
        }

        // 5. Gemini API Key
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (empty($apiKey) || $apiKey === 'your_gemini_api_key_here' || str_starts_with((string)$apiKey, 'your_')) {
            $rows[] = ['Khóa Google Gemini API', 'Chưa có key thực tế trong .env', 'CẦN CẤU HÌNH'];
        } else {
            $rows[] = ['Khóa Google Gemini API', 'Đã cấu hình (' . substr($apiKey, 0, 6) . '...)', 'Sẵn sàng'];
        }

        // In bảng báo cáo
        $this->table(['Thành phần', 'Chi tiết', 'Trạng thái'], $rows);

        if ($hasError) {
            $this->components->error('Môi trường AI phát hiện thành phần chưa sẵn sàng. Vui lòng khắc phục các mục cảnh báo phía trên.');
            return self::FAILURE;
        }

        $this->components->info('Toàn bộ môi trường AI đã sẵn sàng hoạt động!');
        return self::SUCCESS;
    }
}
