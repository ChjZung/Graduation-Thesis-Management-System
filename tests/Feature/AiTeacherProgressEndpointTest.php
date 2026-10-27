<?php
namespace Tests\Feature;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TaiKhoan;
use App\Services\AiReportProcessingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class AiTeacherProgressEndpointTest extends TestCase
{
    use DatabaseTransactions;
    private function ownedReport(): array
    {
        $r=KhoiBaoCaoTienDo::with('deTai.giangVien')->whereNotNull('DuongDanFile')->whereHas('deTai.giangVien')->first();
        if(!$r)$this->markTestSkipped('Requires a local report.');
        $gv=$r->deTai->giangVien;$owner=TaiKhoan::where('MaGV',$gv->MaGV)->orWhere('MaTK',$gv->MaTK)->first();
        if(!$owner)$this->markTestSkipped('Requires an owner account.');
        return [$r,$owner];
    }
    public function test_json_analyze_keeps_authorization_and_does_not_reenqueue_a_cached_result(): void
    {
        [$r,$owner]=$this->ownedReport();$before=$r->TomTatJson;$jobs=DB::table('jobs')->count();
        $svc=\Mockery::mock(AiReportProcessingService::class)->makePartial();
        $svc->shouldReceive('enqueue')->once()->andReturn(['reused'=>true,'message'=>'Đã có kết quả.']);
        $svc->shouldReceive('status')->once()->andReturn(['status'=>'completed','queued_at'=>100,'server_time'=>110]);
        $this->app->instance(AiReportProcessingService::class,$svc);
        $this->actingAs($owner)->postJson(route('baocao.ai.phan_tich_lai',$r->MaKhoi))->assertOk()->assertJsonPath('reused',true)->assertJsonPath('state.status','completed');
        $this->assertSame($before,$r->fresh()->TomTatJson);$this->assertSame($jobs,DB::table('jobs')->count());
    }
    public function test_reading_status_does_not_create_jobs_or_change_report(): void
    {
        [$r,$owner]=$this->ownedReport();$before=$r->TomTatJson;$jobs=DB::table('jobs')->count();
        $this->actingAs($owner)->getJson(route('baocao.ai.trang_thai',$r->MaKhoi))->assertOk()->assertJsonStructure(['status','server_time']);
        $this->assertSame($before,$r->fresh()->TomTatJson);$this->assertSame($jobs,DB::table('jobs')->count());
    }
    public function test_guest_and_other_account_cannot_read_status_or_analyze(): void
    {
        [$r,$owner]=$this->ownedReport();
        $this->getJson(route('baocao.ai.trang_thai',$r->MaKhoi))->assertRedirect(route('login'));
        $outsider=TaiKhoan::where('MaTK','<>',$owner->MaTK)->where('MaVaiTro','VT01')->first();
        if(!$outsider)$this->markTestSkipped('Requires an unrelated account.');
        $this->actingAs($outsider)->getJson(route('baocao.ai.trang_thai',$r->MaKhoi))->assertForbidden();
        $this->postJson(route('baocao.ai.phan_tich_lai',$r->MaKhoi))->assertForbidden();
    }
}

