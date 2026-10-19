<?php

namespace Tests\Unit;

use App\Http\Controllers\BaoCaoAiController;
use App\Models\DotNopBaoCao;
use App\Models\GiaiDoanThucHien;
use App\Services\AiSummaryService;
use PHPUnit\Framework\TestCase;

class AiRequirementsReviewTest extends TestCase
{
    private function round(): DotNopBaoCao
    {
        $stage = new GiaiDoanThucHien(['TenGiaiDoan' => 'Thiết kế hệ thống', 'YeuCauCanDat' => 'Có sơ đồ thiết kế']);
        $round = new DotNopBaoCao(['TenDotNop' => 'Đợt thiết kế CSDL', 'MoTa' => '<p>Đối chiếu thiết kế</p>', 'NoiDungYeuCau' => '<p>Có ERD</p><p>Có từ điển dữ liệu &amp; minh chứng</p>', 'YeuCauSourceCode' => false]);
        $round->setRelation('giaiDoan', $stage);
        return $round;
    }

    public function test_context_contains_stage_round_description_and_requirements(): void
    {
        $context = $this->round()->nguCanhDoiChieuAi();
        $this->assertSame('Thiết kế hệ thống', $context['stage_title']);
        $this->assertSame('Đợt thiết kế CSDL', $context['round_title']);
        $this->assertSame('Đối chiếu thiết kế', $context['round_description']);
        $this->assertSame("Có ERD\nCó từ điển dữ liệu & minh chứng", $context['submission_requirements']);
        $this->assertFalse($context['require_source_code']);
    }

    public function test_context_hash_changes_when_lecturer_changes_brief(): void
    {
        $round = $this->round();
        $hash = $round->maNguCanhDoiChieuAi();
        $round->NoiDungYeuCau = 'Yêu cầu kiểm thử mới';
        $this->assertNotSame($hash, $round->maNguCanhDoiChieuAi());
    }

    public function test_formatter_keeps_review_separate_from_complete_summary(): void
    {
        $review = ['items' => [['id' => 1, 'status' => 'partial']]];
        $result = (new AiSummaryService)->formatSummarizeResult(['data' => ['overall_summary' => 'Tóm tắt toàn bộ PDF', 'requirements_review' => $review]]);
        $this->assertSame('Tóm tắt toàn bộ PDF', $result['data']['overall_summary']);
        $this->assertSame($review, $result['data']['requirements_review']);
    }

    public function test_recheck_preserves_existing_summary_and_metadata(): void
    {
        $method = new \ReflectionMethod(BaoCaoAiController::class, 'withReview');
        $payload = ['overall_summary' => 'Báo cáo đầy đủ', 'citations' => [['page' => 2]], 'version' => 3];
        $result = $method->invoke(new BaoCaoAiController, $payload, ['status' => 'completed']);
        $this->assertSame($payload['overall_summary'], $result['overall_summary']);
        $this->assertSame($payload['citations'], $result['citations']);
        $this->assertSame(3, $result['version']);
        $this->assertSame('completed', $result['requirements_review']['status']);
    }

    public function test_legacy_nested_json_retains_its_summary(): void
    {
        $method = new \ReflectionMethod(BaoCaoAiController::class, 'withReview');
        $result = $method->invoke(new BaoCaoAiController, '{"data":{"overall_summary":"Original"},"source":"legacy"}', ['status' => 'partial']);
        $this->assertSame('Original', $result['data']['overall_summary']);
        $this->assertSame('partial', $result['data']['requirements_review']['status']);
        $this->assertSame('legacy', $result['source']);
    }
}
