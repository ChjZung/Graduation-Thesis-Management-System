<?php

namespace Tests\Unit;

use App\Services\AiResultMerger;
use App\Services\AiSummaryService;
use PHPUnit\Framework\TestCase;

class AiDocumentSummaryTest extends TestCase
{
    public function test_v6_chapters_version_and_validation_survive_formatter_without_ai_percent(): void
    {
        $data = ['schema_version'=>6, 'document_id'=>'D', 'document_version_id'=>'V',
            'source_identity'=>['group_id'=>'G','source_hash'=>'HASH'],
            'chapters'=>[['id'=>'S1','title'=>'Khảo sát','sections'=>[['id'=>'S2','number'=>'1.1','items'=>[]]]]],
            'completed_work'=>[], 'analysis_metadata'=>['chunks_succeeded'=>3],
            'validation'=>['excluded_claims'=>[['reason'=>'Thiếu căn cứ']]], 'completion_percentage'=>99,
            'analysis_status'=>'partial', 'overview_summary'=>'Nội dung khảo sát', 'overall_summary'=>'Nội dung khảo sát'];
        $result = (new AiSummaryService)->formatSummarizeResult(['data'=>$data]);
        foreach (['chapters','document_id','document_version_id','source_identity','validation','analysis_metadata'] as $key) {
            $this->assertSame($data[$key], $result['data'][$key]);
        }
        foreach (['difficulties','next_steps','completion_percentage','completion_basis'] as $key) {
            $this->assertArrayNotHasKey($key, $result['data']);
        }
        $this->assertSame($data['chapters'], $result['standard_report']['chapters']);
        $this->assertNull($result['completion_percentage']);
    }

    public function test_timeout_without_new_document_keeps_verified_old_generation(): void
    {
        $old = ['schema_version'=>6, '_source_hash'=>'SAME', 'chapters'=>[['id'=>'S1']],
            'analysis'=>['status'=>'partial','analyzed_chunks'=>10,'total_pages'=>50,'token_settings'=>['target_tokens'=>2500]]];
        $new = ['schema_version'=>6, '_source_hash'=>'SAME', 'chapters'=>[],
            'analysis'=>['status'=>'partial','source'=>'system_fallback','total_pages'=>0,'analyzed_chunks'=>0]];
        $this->assertSame($old, (new AiResultMerger)->merge($old, $new));
        $new['_source_hash'] = 'DIFFERENT';
        $this->assertSame($new, (new AiResultMerger)->merge($old, $new));
    }
}
