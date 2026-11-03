<?php
namespace Tests\Feature;
use App\Models\{DotNopBaoCao,KhoiBaoCaoTienDo,Nhom,TaiKhoan};
use App\Services\{AiReportProcessingService,ReportMilestoneProgressService};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class TeacherRoundPresentationTest extends TestCase
{
    private function renderRound(bool $empty=false): string
    {
        $this->assertStringStartsWith('ai_stability_',DB::connection()->getDatabaseName());
        $round=DotNopBaoCao::with(['giaiDoan','deTai.giangVien','khoiBaoCaos'])->findOrFail(223);
        $teacher=$round->deTai->giangVien;
        $this->actingAs(TaiKhoan::where('MaGV',$teacher->MaGV)->firstOrFail());
        $group=Nhom::findOrFail('N_TEST737475');
        $versions=$empty?collect():app(ReportMilestoneProgressService::class)->versionsForRound($round,$group);
        $processing=\Mockery::mock(AiReportProcessingService::class)->makePartial();$processing->shouldReceive('sourceMatches')->andReturn(true);$this->app->instance(AiReportProcessingService::class,$processing);
        return view('giangvien.baocao.show_dot',['dotNop'=>$round,'deTai'=>$round->deTai,'nhom'=>$group,'giangVien'=>$teacher,'phienBans'=>$versions,'latestPhienBan'=>$versions->first(),
            'reportMilestoneProgress'=>app(ReportMilestoneProgressService::class)->forGroup($group,$round->deTai)])->render();
    }
    public function test_teacher_controls_and_latest_review_target_are_preserved(): void
    {
        $html=$this->renderRound();$dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new \DOMXPath($dom);
        $this->assertCount(1,$xp->query('//header[contains(@class,"report-round-header")]'));
        $this->assertCount(0,$xp->query('//form[contains(@action,"/yeu-cau-ma-nguon")]'));
        $this->assertCount(1,$xp->query('//form[contains(@action,"/toggle")]'));
        $this->assertCount(1,$xp->query('//form[contains(@action,"/danh-gia")]//input[@name="MaKhoi" and string-length(@value)>0]'));
        $this->assertCount(2,$xp->query('//input[@name="HanhDong"]'));
        $this->assertCount(1,$xp->query('//textarea[@name="NhanXet" and @required]'));
        $this->assertCount(0,$xp->query('//*[@data-report-milestone-progress]'));
        $this->assertStringNotContainsString('Tự đánh giá:',$html);
        $this->assertStringNotContainsString('fa-hourglass-half',$html);
    }
    public function test_empty_round_keeps_round_controls_without_an_empty_review_form(): void
    {
        $html=$this->renderRound(true);$this->assertStringContainsString('Chưa có bài nộp nào từ sinh viên',$html);
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new \DOMXPath($dom);
        $this->assertCount(0,$xp->query('//form[contains(@action,"/danh-gia")]'));
        $this->assertCount(0,$xp->query('//form[contains(@action,"/yeu-cau-ma-nguon")]'));
    }
}
