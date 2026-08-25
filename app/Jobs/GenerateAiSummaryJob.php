<?php

namespace App\Jobs;

use App\Models\BaoCaoTienDo;
use App\Models\TomTatBaoCao;
use App\Services\AiSummaryService;
use App\Helpers\IdGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần thử lại nếu job thất bại.
     */
    public int $tries = 3;

    /**
     * Thời gian chờ tối đa (giây).
     */
    public int $timeout = 60;

    public function __construct(private string $maBaoCao) {}

    public function handle(): void
    {
        $baoCao = BaoCaoTienDo::find($this->maBaoCao);
        if (!$baoCao) {
            Log::warning("GenerateAiSummaryJob: BaoCao {$this->maBaoCao} không tìm thấy.");
            return;
        }

        // Không sinh lại nếu đã có tóm tắt
        if ($baoCao->tomTat()->exists()) {
            return;
        }

        try {
            $aiService = app(AiSummaryService::class);
            $aiData = $aiService->generate($baoCao);

            $maTomTat = IdGenerator::nextTomTat();

            TomTatBaoCao::create(array_merge(
                ['MaTomTat' => $maTomTat, 'MaBaoCao' => $baoCao->MaBaoCao],
                $aiData
            ));

            Log::info("GenerateAiSummaryJob: Đã tạo tóm tắt {$maTomTat} cho báo cáo {$this->maBaoCao}.");
        } catch (\Throwable $e) {
            Log::error("GenerateAiSummaryJob: Lỗi khi tạo tóm tắt AI cho {$this->maBaoCao}: " . $e->getMessage());
            throw $e; // Ném lại để Queue retry
        }
    }
}
