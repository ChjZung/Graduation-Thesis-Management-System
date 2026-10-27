<?php
namespace Tests\Feature;
use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use Tests\TestCase;
class AiTeacherReportUiTest extends TestCase
{
    private function renderCard(array $replace=[], ?bool $teacher=true): string
    {
        $ref=['source_id'=>'INTERNAL_SOURCE_1','page'=>3,'end_page'=>3,'quote'=>'Nguồn nội dung theo chương.','quote_verified'=>true];
        $item=['content'=>'Nội dung chương giữ nguyên.','source_references'=>[$ref]];
        $data=array_replace_recursive(['schema_version'=>6,'overall_summary'=>'Tổng quan giữ nguyên.',
            'document_content'=>[$item],'completed_work'=>[$item],
            'analysis'=>['engine'=>'pdf_lab','status'=>'completed','analyzed_chunks'=>2,'total_chunks'=>2],
            'chapters'=>[['id'=>'C1','title'=>'CHƯƠNG 1','level'=>1,'kind'=>'section','start_page'=>1,'end_page'=>4,'items'=>[$item]]],
            'requirements_document'=>['title'=>'Đợt 1','period'=>'Giai đoạn 1','text'=>'Yêu cầu gốc.'],
            'teacher_requirements'=>[['requirement_id'=>'R0001','text'=>'Yêu cầu gốc.','evidence_quote'=>'Yêu cầu gốc.','report_relation'=>'related_text','report_explanation'=>'Trả lời AI theo báo cáo.','report_matches'=>[$item]]],
        ],$replace);
        foreach(['document_content','completed_work','chapters','teacher_requirements'] as $key) if(array_key_exists($key,$replace)) $data[$key]=$replace[$key];
        $r=new KhoiBaoCaoTienDo;$r->MaKhoi='UI_TEST';$r->DuongDanFile='test.pdf';$r->TomTatJson=$data;$r->setRelation('dotNop',null);
        $p=\Mockery::mock(AiReportProcessingService::class)->makePartial();$p->shouldReceive('sourceMatches')->andReturn(true);$this->app->instance(AiReportProcessingService::class,$p);
        return view('partials.ai_summary_11_sections',['pb'=>$r,'teacherReportUi'=>$teacher])->render();
    }
    public function test_three_tabs_keep_current_content_and_quote_relationships(): void
    {
        $html=$this->renderCard();$dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xpath=new \DOMXPath($dom);
        $this->assertCount(3,$xpath->query('//*[@data-ai-result-tab]'));
        foreach($xpath->query('//*[@data-ai-result-tab]') as $tab){$id=$tab->getAttribute('aria-controls');$this->assertCount(1,$xpath->query('//*[@id="'.$id.'"]'));}
        $this->assertStringContainsString('Nội dung chương giữ nguyên.',$html);
        $this->assertStringContainsString('1. Yêu cầu gốc',$html);
        $this->assertStringNotContainsString('Ý 1 — Nội dung liên quan',$html);
        $this->assertStringNotContainsString('Yêu cầu 1 của giảng viên',$html);
        $this->assertStringContainsString('Giảng viên nên đối chiếu với báo cáo gốc trước khi đưa ra đánh giá.',$html);
        $visible=$dom->textContent;$this->assertStringNotContainsString('INTERNAL_SOURCE_1',$visible);
        $this->assertStringNotContainsString('chính xác 95%',$visible);
    }
    public function test_empty_measurements_have_honest_labels_and_ai_html_is_escaped(): void
    {
        $html=$this->renderCard(['document_content'=>[],'completed_work'=>[],'chapters'=>[],
            'teacher_requirements'=>[],'requirements_document'=>['text'=>''], 'overall_summary'=>'<script>fake_ai()</script>', 'analysis'=>['total_chunks'=>null]]);
        $this->assertStringNotContainsString('<script>fake_ai()</script>',$html);
        $this->assertStringContainsString('Chưa có dữ liệu kiểm chứng',$html);
        $this->assertStringContainsString('Không tìm thấy công việc đã hoàn thành có đủ bằng chứng trong báo cáo.',$html);
        $this->assertStringContainsString('Giảng viên chưa thiết lập yêu cầu phân tích cho đợt nộp này.',$html);
    }
    public function test_atomic_requirements_show_individual_answers_and_abstain_without_sources(): void
    {
        $ref=['page'=>3,'quote'=>'Một câu trích trong báo cáo.'];
        $html=$this->renderCard(['teacher_requirements'=>[['requirement_id'=>'R0001','text'=>'Giới thiệu bài toán, mục tiêu và phạm vi.','evidence_quote'=>"Nộp báo cáo khảo sát, gồm:\n1. Giới thiệu bài toán, mục tiêu và phạm vi.",'report_points'=>[
            ['title'=>'Giới thiệu bài toán','relation'=>'related_text','explanation'=>'Bài toán quản lý khóa luận.','source_references'=>[$ref]],
            ['title'=>'Mục tiêu','relation'=>'not_found','explanation'=>'Không nên hiển thị suy đoán này.','source_references'=>[$ref]],
        ]]]]);
        $this->assertStringContainsString('1. Giới thiệu bài toán, mục tiêu và phạm vi.',$html);
        $this->assertStringNotContainsString('2. Mục tiêu',$html);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xpath=new \DOMXPath($dom);
        $this->assertCount(2,$xpath->query('//*[@data-requirement-id]'));
        $this->assertCount(2,$xpath->query('//*[@data-requirement-id]//h4'));
        $this->assertCount(2,$xpath->query('//*[@data-ai-requirement-tab]'));
        $this->assertCount(0,$xpath->query('//*[contains(@id,"requirements-pane-missing")]//*[@data-lab-source-references]'));
        $this->assertCount(1,$xpath->query('//*[contains(@id,"requirements-pane-completed")]//*[@data-lab-source-references]'));
        $this->assertStringContainsString('Điều này không đồng nghĩa sinh viên chưa thực hiện công việc.',$html);
        $this->assertCount(2,$xpath->query('//*[@data-requirement-id]//*[@data-requirement-point]'));
        $this->assertStringContainsString('Không có dẫn chứng',$html);
        $this->assertStringNotContainsString('Không nên hiển thị suy đoán này.',$html);
    }
    public function test_student_route_uses_updated_tabbed_report_by_default(): void
    {
        $route=new \Illuminate\Routing\Route('GET','sinhvien/baocao/dot-nop/{id}',fn()=>null);
        $route->name('sinhvien.baocao.show_dot');
        $this->app['request']->setRouteResolver(fn()=>$route);
        $html=$this->renderCard([],null);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xpath=new \DOMXPath($dom);
        $this->assertCount(3,$xpath->query('//*[@data-ai-result-tab]'));
        $this->assertCount(2,$xpath->query('//*[@data-ai-requirement-tab]'));
    }
    public function test_explicit_legacy_partial_keeps_previous_layout(): void
    {
        $html=$this->renderCard([],false);$dom=new \DOMDocument;@$dom->loadHTML($html);$xpath=new \DOMXPath($dom);
        $this->assertCount(0,$xpath->query('//*[@data-ai-result-tab]'));
        $this->assertStringContainsString('Nội dung chương giữ nguyên.',$html);
    }
}

