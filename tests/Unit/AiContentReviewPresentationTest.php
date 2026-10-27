<?php
namespace Tests\Unit;
use App\Services\AiSummaryService;
use App\Services\AiResultMerger;
use Tests\TestCase;

class AiContentReviewPresentationTest extends TestCase
{
    private function audit(): array {
        return ['version'=>'report-content-review-1.0','source_hash'=>'hash','status'=>'completed','coverage'=>['checked_leaf_sections'=>2,'map_available'=>true,'map_scope_complete'=>true],
            'missing_content'=>[['section_title'=>'4.2 Thiết kế','status'=>'heading_only','content'=>'Mục mới có tiêu đề.','source_references'=>[['source_id'=>'S','page'=>2,'quote'=>'4.2 Thiết kế']]]],
            'reported_unfinished'=>[], 'manual_review'=>[['section_title'=>'4.3 Sơ đồ','status'=>'needs_visual_review','content'=>'Có sơ đồ cần xem trực tiếp.','source_references'=>[['page'=>3,'quote'=>'4.3 Sơ đồ']]]]];
    }
    private function render(array $audit, bool $changed=false): string {
        return view('partials.ai_work_content',['aiData'=>['_source_hash'=>'hash','content_review'=>$audit],'sourceChanged'=>$changed,'uniqueId'=>'test','completedWork'=>[],'citationAvailable'=>true,'documentType'=>'pdf'])->render();
    }
    public function test_two_tabs_show_missing_and_manual_review_as_distinct_groups(): void {
        $html=$this->render($this->audit());
        $this->assertSame(2,substr_count($html,'data-ai-work-tab='));
        $this->assertStringContainsString('data-content-observation="heading_only"',$html);
        $this->assertStringNotContainsString('data-content-observation="needs_visual_review"',$html);
        $this->assertStringContainsString('data-citation-page="2"',$html);
        $this->assertStringContainsString('Các mục này không bị tính là thiếu nội dung',$html);
    }
    public function test_stale_audit_and_changed_file_do_not_display_old_findings(): void {
        $audit=$this->audit();$audit['source_hash']='another-hash';
        foreach([$this->render($audit),$this->render($this->audit(),true)] as $html){
            $this->assertStringNotContainsString('data-content-observation="heading_only"',$html);
            $this->assertStringContainsString('chưa thể kết luận mục nào còn thiếu',$html);
        }
    }
    public function test_formatter_preserves_the_audit_without_fabricating_a_progress_score(): void {
        $out=(new AiSummaryService)->formatSummarizeResult(['data'=>['schema_version'=>6,'content_review'=>$this->audit()],'total_pages'=>3]);
        $this->assertSame($this->audit(),$out['data']['content_review']);
        $this->assertArrayNotHasKey('completion_percentage',$out['data']);
    }
    public function test_complete_local_audit_can_be_saved_when_previous_summary_is_retained(): void {
        $old=['schema_version'=>6,'_source_hash'=>'hash','analysis'=>['status'=>'completed','total_pages'=>3],'overall_summary'=>'Saved summary'];
        $new=['schema_version'=>6,'_source_hash'=>'hash','analysis'=>['status'=>'partial','total_pages'=>3],'content_review'=>$this->audit()];
        $merged=(new AiResultMerger)->merge($old,$new);
        $this->assertSame('Saved summary',$merged['overall_summary']);
        $this->assertSame($this->audit(),$merged['content_review']);
    }
}
