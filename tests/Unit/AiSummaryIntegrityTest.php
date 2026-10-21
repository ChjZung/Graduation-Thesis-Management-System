<?php

namespace Tests\Unit;

use App\Services\AiSummaryService;
use PHPUnit\Framework\TestCase;

class AiSummaryIntegrityTest extends TestCase
{
    public function test_analysis_provenance_and_outline_survive_formatting_and_persistence_payload(): void
    {
        $analysis = ['version' => 2, 'source' => 'fallback_nlp', 'total_pages' => 72, 'matched_citations' => 4];
        $outline = [['content' => 'Chương 3. Phân tích thiết kế', 'source_pages' => [21]]];
        $result = (new AiSummaryService)->formatSummarizeResult(['source' => 'fallback_nlp', 'total_pages' => 72,
            'data' => ['analysis' => $analysis, 'document_outline' => $outline, 'completion_percentage' => null]]);
        $this->assertSame($analysis, $result['data']['analysis']);
        $this->assertSame($outline, $result['data']['document_outline']);
        $this->assertNull($result['completion_percentage']);
        $this->assertNull($result['data']['completion_basis']);
    }

    public function test_explicit_zero_percent_is_retained_without_default_progress(): void
    {
        $result = (new AiSummaryService)->formatSummarizeResult(['data' => ['completion_percentage' => 0, 'completion_basis' => 'pdf_explicit']]);
        $this->assertSame(0, $result['data']['completion_percentage']);
        $this->assertSame('pdf_explicit', $result['data']['completion_basis']);
    }

    public function test_content_overview_and_partial_processing_diagnostics_survive_formatting(): void
    {
        $content = [['content' => 'Báo cáo mô tả khảo sát.', 'evidence' => 'Thông tin quản lý bị phân tán.', 'source_pages' => [9], 'verified' => true]];
        $analysis = ['status' => 'partial', 'total_chunks' => 5, 'analyzed_chunks' => 2,
            'errors' => [['code' => 'timeout', 'message' => 'Chưa xử lý đầy đủ.']]];
        $result = (new AiSummaryService)->formatSummarizeResult(['data' => [
            'overall_summary' => 'Báo cáo mô tả khảo sát.', 'document_content' => $content, 'analysis' => $analysis,
        ]]);
        $this->assertSame($content, $result['data']['document_content']);
        $this->assertSame($analysis, $result['data']['analysis']);
    }
}
