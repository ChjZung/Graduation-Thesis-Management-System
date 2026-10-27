<?php

namespace Tests\Unit;

use App\Services\AiSummaryService;
use PHPUnit\Framework\TestCase;

class AiSummaryIntegrityTest extends TestCase
{
    public function test_flexible_sections_and_scope_survive_formatting(): void
    {
        $item = ['content'=>'Nhóm đề xuất mô hình', 'kind'=>'proposal', 'evidence'=>'Nhóm đề xuất mô hình Transformer.',
            'source_pages'=>[12], 'source_ids'=>['F3_1'], 'verified'=>true];
        $sections = [['id'=>'summary_sections_1','title'=>'Mô hình đề xuất','items'=>[$item]]];
        $extra = [['id'=>'additional_sections_1','title'=>'Bối cảnh nghiên cứu','items'=>[$item]]];
        $scope = ['mode'=>'focused','note'=>'Theo yêu cầu giảng viên','context_hash'=>'brief-v2'];
        $result = (new AiSummaryService)->formatSummarizeResult(['data'=>[
            'schema_version'=>5, 'summary_sections'=>$sections, 'additional_sections'=>$extra, 'summary_scope'=>$scope,
        ]]);
        $this->assertSame(5, $result['data']['schema_version']);
        $this->assertSame($sections, $result['data']['summary_sections']);
        $this->assertSame($extra, $result['data']['additional_sections']);
        $this->assertSame($scope, $result['data']['summary_scope']);
        $this->assertSame($sections, $result['standard_report']['sections']);
        $this->assertArrayNotHasKey('kho_khan', $result['standard_report']);
    }

    public function test_process_failure_does_not_reintroduce_fixed_groups_or_fabricate_page_count(): void
    {
        $method = new \ReflectionMethod(AiSummaryService::class, 'fallbackSummarize');
        $result = $method->invoke(new AiSummaryService, 'fixture.pdf', 100, ['submission_requirements'=>'Khảo sát']);
        $this->assertSame(6, $result['data']['schema_version']);
        $this->assertSame([], $result['data']['chapters']);
        $this->assertArrayNotHasKey('difficulties', $result['data']);
        $this->assertSame(0, $result['data']['analysis']['total_pages']);
        $this->assertFalse($result['data']['analysis']['text_complete']);
    }

    public function test_new_brief_does_not_inherit_more_complete_summary_with_old_focus(): void
    {
        $old = ['schema_version'=>5, 'summary_scope'=>['context_hash'=>'old-brief'],
            'analysis'=>['status'=>'completed','analyzed_chunks'=>20]];
        $new = ['schema_version'=>5, 'summary_scope'=>['context_hash'=>'new-brief'],
            'analysis'=>['status'=>'partial','analyzed_chunks'=>3]];
        $this->assertSame($new, (new \App\Services\AiResultMerger)->merge($old, $new));
        $new['summary_scope']['context_hash'] = 'old-brief';
        $this->assertSame($old, (new \App\Services\AiResultMerger)->merge($old, $new));
    }

    public function test_hierarchy_version_and_source_ids_survive_formatting(): void
    {
        $item = ['content' => 'Đã khảo sát', 'evidence' => 'Nhóm đã khảo sát hiện trạng.',
            'source_pages' => [9], 'source_chunk_ids' => [2], 'source_ids' => ['C2_completed_work_1'], 'verified' => true];
        $result = (new AiSummaryService)->formatSummarizeResult(['data' => [
            'schema_version' => 4, 'completed_work' => [$item], 'difficulties' => [], 'next_steps' => [],
        ]]);
        $this->assertSame(4, $result['data']['schema_version']);
        $this->assertSame([$item], $result['data']['completed_work']);
        $this->assertSame([], $result['data']['difficulties']);
        $this->assertSame([], $result['data']['next_steps']);
    }

    public function test_old_complete_schema_does_not_mask_new_partial_pipeline(): void
    {
        $old = ['schema_version' => 3, 'overall_summary' => 'Kết quả cũ',
            'analysis' => ['status' => 'completed', 'analyzed_chunks' => 12]];
        $new = ['schema_version' => 4, 'overall_summary' => 'Kết quả mới có nguồn',
            'analysis' => ['status' => 'partial', 'analyzed_chunks' => 2]];
        $this->assertSame($new, (new \App\Services\AiResultMerger)->merge($old, $new));
    }

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
