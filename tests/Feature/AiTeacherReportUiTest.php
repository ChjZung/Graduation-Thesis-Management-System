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
    public function test_two_tabs_keep_current_content_and_quote_relationships(): void
    {
        $html=$this->renderCard();$dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xpath=new \DOMXPath($dom);
        $this->assertCount(2,$xpath->query('//*[@data-ai-result-tab]'));
        $this->assertCount(0,$xpath->query('//*[@data-ai-work-summary] | //*[@data-ai-result-tab="work"] | //*[@data-ai-pane="work"]'));
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
        $this->assertStringNotContainsString('Không tìm thấy công việc đã hoàn thành có đủ bằng chứng trong báo cáo.',$html);
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
        $this->assertCount(2,$xpath->query('//*[@data-ai-result-tab]'));
        $this->assertCount(2,$xpath->query('//*[@data-ai-requirement-tab]'));
    }
    public function test_explicit_legacy_partial_keeps_previous_layout(): void
    {
        $html=$this->renderCard([],false);$dom=new \DOMDocument;@$dom->loadHTML($html);$xpath=new \DOMXPath($dom);
        $this->assertCount(0,$xpath->query('//*[@data-ai-result-tab]'));
        $this->assertStringContainsString('Nội dung chương giữ nguyên.',$html);
    }
    public function test_caption_pointer_keeps_deepest_section_and_escapes_text(): void
    {
        $asset=['display_text'=>'Hình 3.8 <script>bad</script>','raw_text'=>'Hình 3.8 nguồn',
            'section_number'=>'3.2.1','section_title'=>'Đăng ký đề tài','page'=>45];
        $html=$this->renderCard(['chapters'=>[['id'=>'S','title'=>'Thiết kế','level'=>2,'kind'=>'section',
            'start_page'=>45,'end_page'=>45,'status'=>'insufficient_text','related_assets'=>[$asset]]]]);
        $this->assertStringContainsString('Mục 3.2.1',$html);
        $this->assertStringContainsString('Trang PDF 45',$html);
        $this->assertStringContainsString('#page=45',$html);
        $this->assertStringContainsString('data-citation-page="45"',$html);
        $this->assertStringNotContainsString('<script>bad</script>',$html);
    }
    public function test_each_page_button_uses_its_own_source_quote(): void
    {
        $items=[['content'=>'Ý thứ nhất.','source_pages'=>[3,7,9],'source_references'=>[
            ['page'=>3,'quote'=>'Nguồn riêng trang ba.'],['page'=>7,'quote'=>'Nguồn riêng trang bảy.']]]];
        $html=$this->renderCard(['chapters'=>[['id'=>'PAGES','title'=>'Nhiều trang','level'=>2,'kind'=>'section',
            'start_page'=>3,'end_page'=>9,'items'=>$items]]]);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new \DOMXPath($dom);
        foreach([3=>'Nguồn riêng trang ba.',7=>'Nguồn riêng trang bảy.'] as $page=>$quote) {
            $buttons=$xp->query('//*[@data-summary-group="PAGES"]//*[@data-citation-page="'.$page.'"]');
            $this->assertCount(1,$buttons);
            $this->assertSame($quote,$buttons->item(0)->getAttribute('data-citation-quote'));
        }
        $this->assertCount(0,$xp->query('//*[@data-summary-group="PAGES"]//*[@data-citation-page="9"]'));
    }
    public function test_parent_keeps_access_to_evidence_with_child_sections(): void
    {
        $html=$this->renderCard(['chapters'=>[['id'=>'PARENT','title'=>'Mục cha','level'=>2,'kind'=>'section',
            'start_page'=>1,'end_page'=>3,'items'=>[['content'=>'Nội dung riêng của cha.',
            'source_references'=>[['page'=>1,'quote'=>'Nguồn trực tiếp của cha.']]]],
            'sections'=>[['id'=>'CHILD','title'=>'Mục con','level'=>3,'kind'=>'section','start_page'=>2,'end_page'=>3,'items'=>[]]]]]]);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new \DOMXPath($dom);
        $this->assertCount(1,$xp->query('//*[@data-summary-group="PARENT"]//*[@data-lab-source-references]'));
    }
    public function test_caption_sources_render_once_and_keep_every_relevant_diagram(): void
    {
        $a=['source_id'=>'A','page'=>85,'quote'=>'Hình 4.7 Sơ đồ tuần tự theo dõi tiến độ'];
        $b=['source_id'=>'B','page'=>86,'quote'=>'Hình 4.8 Sơ đồ tuần tự quản lý hồ sơ bảo vệ'];
        $point=['relation'=>'related_text','check_status'=>'NEEDS_VISUAL_REVIEW','explanation'=>'Có hai tên sơ đồ liên quan.',
            'source_references'=>[$a],'manual_references'=>[$a,$b,$b]];
        $html=$this->renderCard(['requirements_review'=>['mode'=>'verified_v2','status'=>'completed'],
            'teacher_requirements'=>[['requirement_id'=>'R4','text'=>'4. Theo dõi tiến độ và hồ sơ bảo vệ.','report_points'=>[$point]]]]);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new \DOMXPath($dom);
        $prefix='//*[@data-requirement-id="R4"]';
        $this->assertCount(2,$xp->query($prefix.'//*[@data-requirement-caption]'));
        foreach([85,86] as $page)$this->assertCount(1,$xp->query($prefix.'//*[@data-citation-page="'.$page.'"]'));
        $this->assertCount(1,$xp->query($prefix.'//*[@data-lab-source-references]'));
        $this->assertCount(2,$xp->query($prefix.'//*[@data-lab-source-references]//*[@data-requirement-caption]'));
        $this->assertStringContainsString('quản lý hồ sơ bảo vệ',$html);
    }
}

