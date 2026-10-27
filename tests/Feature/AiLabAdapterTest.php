<?php
namespace Tests\Feature;

use App\Services\AiSummaryService;
use App\Services\AiReportProcessingService;
use App\Models\KhoiBaoCaoTienDo;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class AiLabAdapterTest extends TestCase
{
    private function labPayload(): array
    {
        $item=['content'=>'Nhóm báo cáo đã xây dựng chức năng quản lý đề tài.','kind'=>'reported_work','evidence'=>'Đã xây dựng quản lý đề tài.',
            'verified'=>true,'relevance_verified'=>false,'source_pages'=>[3],'source_references'=>[['source_id'=>'AI-001:1','page'=>3,'end_page'=>3,'quote'=>'Đã xây dựng quản lý đề tài.']]];
        return ['success'=>true,'total_pages'=>6,'source'=>'pdf_lab_map_reduce','data'=>[
            'schema_version'=>6,'overall_summary'=>'Tổng quan theo nguồn.','overview_summary'=>'Tổng quan theo nguồn.',
            'document_content'=>[$item],'chapters'=>[['id'=>'S1','number'=>'','title'=>'CHƯƠNG 1','level'=>1,'kind'=>'section','start_page'=>1,'end_page'=>6,'items'=>[$item],'sections'=>[],'status'=>'completed']],
            'completed_work'=>[$item],'teacher_requirements'=>[['text'=>'Bổ sung sơ đồ.','evidence_quote'=>'Bổ sung sơ đồ hoạt động.']],
            'requirements_document'=>['text'=>'Bổ sung sơ đồ hoạt động.','title'=>'Đợt 1','period'=>'Giai đoạn 1'],
            'teacher_requirements_status'=>'provided','analysis'=>['engine'=>'pdf_lab','status'=>'completed','text_complete'=>true,'quality_checks_completed'=>true,'completion_audit_completed'=>false,'total_pages'=>6,'analyzed_chunks'=>2,'total_chunks'=>2],
        ]];
    }
    public function test_pdf_service_uses_lab_adapter_and_passes_key_only_through_environment(): void
    {
        config(['ai_processing.pdf_engine'=>'pdf_lab','ai_processing.lab'=>['max_generation_calls'=>5], 'services.gemini.api_key'=>'fixture-private-key']);
        Process::fake(fn()=>Process::result(output:json_encode($this->labPayload(),JSON_UNESCAPED_UNICODE)));
        $result=app(AiSummaryService::class)->summarizePdf('fixture.pdf');
        $this->assertSame('pdf_lab',$result['data']['analysis']['engine']);
        $this->assertSame('Bổ sung sơ đồ.',$result['data']['teacher_requirements'][0]['text']);
        Process::assertRan(fn($p)=>is_array($p->command) && in_array(base_path('app/Services/lab_pdf_summary.py'),$p->command)
            && !str_contains(json_encode($p->command),'fixture-private-key') && ($p->environment['GEMINI_API_KEY'] ?? '')==='fixture-private-key');
        $this->assertArrayNotHasKey('completion_percentage',$result['data']);
    }
    public function test_docx_still_uses_its_existing_document_pipeline(): void
    {
        Process::fake(fn()=>Process::result(output:json_encode($this->labPayload())));
        app(AiSummaryService::class)->summarizePdf('fixture.docx');
        Process::assertRan(fn($p)=>is_array($p->command) && in_array(base_path('app/Services/ai_summarizer.py'),$p->command));
    }
    public function test_chapter_and_teacher_sources_render_escaped_and_with_per_quote_buttons(): void
    {
        $report=new KhoiBaoCaoTienDo;
        $report->MaKhoi='LAB_RENDER';$report->DuongDanFile='fixture.pdf';$report->TomTatJson=$this->labPayload()['data'];
        $report->setRelation('dotNop',null);
        $processing=\Mockery::mock(AiReportProcessingService::class)->makePartial();
        $processing->shouldReceive('sourceMatches')->andReturn(true);
        $this->app->instance(AiReportProcessingService::class,$processing);
        $html=view('partials.ai_summary_11_sections',['pb'=>$report])->render();
        $this->assertStringContainsString('Bản đọc theo chương dành cho giảng viên',$html);
        $this->assertStringContainsString('data-lab-source-references',$html);
        $this->assertStringContainsString('data-teacher-requirements-summary',$html);
        $this->assertStringContainsString('Xem yêu cầu gốc',$html);
        $this->assertStringContainsString('Đã xây dựng quản lý đề tài.',$html);
    }
    public function test_generation_budget_resumes_promptly_but_quota_and_authentication_do_not(): void
    {
        $service=app(AiReportProcessingService::class);
        $data=['analysis'=>['engine'=>'pdf_lab','status'=>'partial'],'processing_errors'=>[['stage'=>'summary','code'=>'budget_exhausted']]];
        $retry=$service->retryState($data,false,1);
        $this->assertSame('waiting',$retry['status']);$this->assertLessThanOrEqual(time()+2,$retry['next_retry_at']);
        $data['processing_errors'][0]['code']='rate_limit';$data['processing_errors'][0]['retry_after']=60;
        $retry=$service->retryState($data,false,1);$this->assertGreaterThanOrEqual(time()+59,$retry['next_retry_at']);
        $data['processing_errors'][0]['code']='authentication';
        $this->assertSame('partial',$service->retryState($data,false,1)['status']);
    }
    public function test_summary_does_not_put_a_deadline_on_the_long_running_cli_worker(): void
    {
        $before=ini_get('max_execution_time');
        ini_set('max_execution_time','321');
        try {
            Process::fake(fn()=>Process::result(output:json_encode($this->labPayload())));
            app(AiSummaryService::class)->summarizePdf('fixture.pdf');
            $this->assertSame('321',ini_get('max_execution_time'));
        } finally { ini_set('max_execution_time',$before); }
    }

    public function test_reanalysis_labels_saved_snapshot_separately_from_new_progress(): void
    {
        $processing=\Mockery::mock(AiReportProcessingService::class)->makePartial();
        $processing->shouldReceive('sourceMatches')->andReturn(true);
        $this->app->instance(AiReportProcessingService::class,$processing);
        foreach (['queued','running','waiting','completed'] as $status) {
            $report=new KhoiBaoCaoTienDo;
            $report->MaKhoi='LAB_LOADING';$report->DuongDanFile='fixture.pdf';
            $data=$this->labPayload()['data'];$data['_processing']=['status'=>$status,'updated_at'=>1];
            $data['analysis']['processing_time_seconds']=20.4;$report->TomTatJson=$data;
            $report->setRelation('dotNop',null);
            $html=view('partials.ai_summary_11_sections',['pb'=>$report])->render();
            preg_match('/data-summary-metadata>(.*?)<\/div>/s',$html,$metadata);
            if ($status==='completed') {
                $this->assertStringContainsString('Hoàn tất',$metadata[1]);
                $this->assertStringNotContainsString('data-showing-saved-summary',$html);
            } else {
                $this->assertStringContainsString('Đang hiển thị bản đã lưu',$metadata[1]);
                $this->assertStringNotContainsString('Hoàn tất',$metadata[1]);
                $this->assertStringContainsString('Lượt xử lý bản đã lưu',$metadata[1]);
                $this->assertStringContainsString('data-showing-saved-summary',$html);
                $this->assertStringContainsString('Bản đã lưu ·',$html);
            }
        }
    }

    public function test_each_teacher_requirement_renders_report_quote_buttons_without_grading(): void
    {
        $processing=\Mockery::mock(AiReportProcessingService::class)->makePartial();
        $processing->shouldReceive('sourceMatches')->andReturn(true);
        $this->app->instance(AiReportProcessingService::class,$processing);
        $data=$this->labPayload()['data'];
        $data['teacher_requirements'][0]['report_relation']='related_text';
        $data['teacher_requirements'][0]['report_explanation']='<script>unsafe</script> Nội dung liên quan.';
        $data['teacher_requirements'][0]['report_matches']=[$data['completed_work'][0]];
        $report=new KhoiBaoCaoTienDo;$report->MaKhoi='LAB_LINK';$report->DuongDanFile='fixture.pdf';$report->TomTatJson=$data;$report->setRelation('dotNop',null);
        $html=view('partials.ai_summary_11_sections',['pb'=>$report])->render();
        $this->assertStringContainsString('Đoạn liên quan trong báo cáo',$html);
        $this->assertStringContainsString('data-requirement-report-links',$html);
        $this->assertStringContainsString('data-lab-source-references',$html);
        $this->assertStringContainsString('Trang 3',$html);
        $this->assertStringNotContainsString('<script>unsafe</script>',$html);
        $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;',$html);
    }

    public function test_verified_review_renders_neutral_tabs_and_keeps_uncertain_quotes_hidden(): void
    {
        $report=new KhoiBaoCaoTienDo;
        $report->MaKhoi='VERIFIED_RENDER';$report->DuongDanFile='fixture.pdf';
        $data=$this->labPayload()['data'];
        $data['requirements_review']=['mode'=>'verified_v2','status'=>'completed','items'=>[]];
        $data['teacher_requirements']=[
            ['requirement_id'=>'req_001','text'=>'1. Giới thiệu bài toán.','report_points'=>[
                ['title'=>'Bài toán','relation'=>'related_text','explanation'=>'Báo cáo mô tả bài toán quản lý khóa luận.','source_references'=>[
                    ['source_id'=>'source1','page'=>2,'end_page'=>2,'quote'=>'Đoạn nguồn đã xác minh.','start'=>10,'semantic_support'=>'SUPPORTS']]],
            ]],
            ['requirement_id'=>'req_002','text'=>'2. Cài đặt đăng nhập.','report_points'=>[
                ['title'=>'Cài đặt','relation'=>'not_found','explanation'=>'Không đủ nguồn.','source_references'=>[],'check_status'=>'NO_VERIFIED_EVIDENCE'],
            ]],
        ];
        $report->TomTatJson=$data;$report->setRelation('dotNop',null);
        $processing=\Mockery::mock(AiReportProcessingService::class)->makePartial();
        $processing->shouldReceive('sourceMatches')->andReturn(true);
        $this->app->instance(AiReportProcessingService::class,$processing);
        foreach([true,false] as $teacher){
            $html=view('partials.ai_summary_11_sections',['pb'=>$report,'teacherReportUi'=>$teacher])->render();
            $this->assertStringContainsString('data-verified-requirement-review',$html);
            $this->assertStringContainsString('Có dẫn chứng',$html);
            $this->assertStringContainsString('Chưa đủ dẫn chứng',$html);
            $this->assertStringContainsString('Hệ thống chưa tìm được dẫn chứng phù hợp',$html);
            $this->assertStringContainsString('Đoạn nguồn đã xác minh.',$html);
            $this->assertStringNotContainsString('Công việc chưa hoàn thành',$html);
        }
    }
}

