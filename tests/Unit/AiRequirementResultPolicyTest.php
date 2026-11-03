<?php
namespace Tests\Unit;

use App\Services\AiRequirementResultPolicy as Policy;
use App\Services\AiReportProcessingService;
use App\Models\KhoiBaoCaoTienDo;
use PHPUnit\Framework\TestCase;

class AiRequirementResultPolicyTest extends TestCase
{
    public function test_reading_payload_never_invokes_matcher_or_rewrites_saved_result(): void
    {
        $data=['chapters'=>[['id'=>'S1']], 'teacher_requirements'=>[['text'=>'Keep original']],
            'requirement_matcher_version'=>'older'];
        $report=new KhoiBaoCaoTienDo(['TomTatJson'=>$data]);
        $report->setRelation('dotNop', null);
        $service=new AiReportProcessingService;
        for ($i=0; $i<5; $i++) $this->assertSame($data,$service->payload($report));
        $this->assertSame($data,$report->TomTatJson);
    }

    public function test_changed_context_hides_stale_review_without_mutating_history_or_summary(): void
    {
        $old=['overall_summary'=>'Keep summary','teacher_requirements'=>[['text'=>'Old brief']],
            'summary_scope'=>['context_hash'=>Policy::contextHash(['brief'=>'old'])]];
        $visible=Policy::forContext($old,['brief'=>'new']);
        $this->assertSame('stale',$visible['requirements_binding_status']);
        $this->assertSame([],$visible['teacher_requirements']);
        $this->assertSame('Keep summary',$visible['overall_summary']);
        $this->assertSame('Old brief',$old['teacher_requirements'][0]['text']);
    }

    public function test_verified_result_survives_heuristic_update_only_for_same_pdf_and_context(): void
    {
        $old=['_source_hash'=>'pdf1','summary_scope'=>['context_hash'=>'brief1'],
            'requirements_review'=>['policy_version'=>'guard1','items'=>[['text'=>'Verified']]],
            'teacher_requirements'=>[['text'=>'Verified']]];
        $new=['_source_hash'=>'pdf1','summary_scope'=>['context_hash'=>'brief1'],
            'requirements_review'=>['mode'=>'verified_v2','items'=>[]], 'teacher_requirements'=>[]];
        $this->assertSame($old['requirements_review'],Policy::preserveVerified($old,$new,$new)['requirements_review']);
        $new['_source_hash']='pdf2';
        $this->assertSame($new,Policy::preserveVerified($old,$new,$new));
        $new['_source_hash']='pdf1'; $new['summary_scope']['context_hash']='brief2';
        $this->assertSame($new,Policy::preserveVerified($old,$new,$new));
    }

    public function test_provenance_survives_formatter_and_merger(): void
    {
        $provenance=['method'=>'verified_pdf_review','context_hash'=>'brief','source_hash'=>'pdf','created_at'=>123];
        $data=['schema_version'=>6,'requirements_provenance'=>$provenance];
        $formatted=(new \App\Services\AiSummaryService)->formatSummarizeResult(['data'=>$data]);
        $this->assertSame($provenance,$formatted['data']['requirements_provenance']);
        $merged=(new \App\Services\AiResultMerger)->merge(['overall_summary'=>'Keep'], $data,true);
        $this->assertSame($provenance,$merged['requirements_provenance']);
        $this->assertSame('Keep',$merged['overall_summary']);
    }
}
