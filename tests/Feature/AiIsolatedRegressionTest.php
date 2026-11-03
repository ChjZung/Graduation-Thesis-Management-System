<?php
namespace Tests\Feature;

use App\Jobs\AnalyzeReportVersion;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TaiKhoan;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class AiIsolatedRegressionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertStringStartsWith('ai_stability_',DB::connection()->getDatabaseName(), 'Use an isolated test database');
    }

    private function report(): KhoiBaoCaoTienDo
    {
        $source=KhoiBaoCaoTienDo::where('MaKhoi','KHOI_D223_V3_0ff1eb')->firstOrFail();
        $copy=$source->replicate(['id']);$copy->MaKhoi='TEST_ISOLATED_'.bin2hex(random_bytes(4));
        $copy->LanNop=999;$copy->TomTatJson=null;$copy->TomTatAI=null;$copy->save();
        return $copy;
    }

    public function test_force_click_cannot_replace_active_job_or_reset_attempts(): void
    {
        $r=$this->report();$svc=app(AiReportProcessingService::class);
        $first=$svc->enqueue($r);$count=DB::table('jobs')->count();
        $svc->updateState($r->MaKhoi,$first['token'],['status'=>'waiting','attempts'=>3,'next_retry_at'=>time()+60]);
        $again=$svc->enqueue($r,false,true,true);
        $this->assertSame($first['token'],$again['token']);
        $this->assertSame(3,$again['attempts']);
        $this->assertSame($count,DB::table('jobs')->count());
    }

    public function test_pdf_changed_after_enqueue_stops_before_any_ai_call(): void
    {
        $r=$this->report();$svc=app(AiReportProcessingService::class);$first=$svc->enqueue($r);
        $other=KhoiBaoCaoTienDo::whereNotNull('DuongDanFile')->where('DuongDanFile','<>',$r->DuongDanFile)->get()
            ->first(fn($row)=>is_file(storage_path('app/public/'.$row->DuongDanFile)));
        $this->assertNotNull($other);
        $r->update(['DuongDanFile'=>$other->DuongDanFile]);
        $ai=\Mockery::mock(AiSummaryService::class);$ai->shouldNotReceive('summarizePdf');
        (new AnalyzeReportVersion($r->MaKhoi,$first['token']))->handle($svc,$ai);
        $this->assertSame('partial',$r->fresh()->TomTatJson['_processing']['status']);
        $this->assertNull($r->fresh()->TomTatAI);
    }

    public function test_outsider_cannot_open_pdf_download_preview_or_review(): void
    {
        $r=$this->report();$r->load('deTai.giangVien');
        $owner=$r->deTai->MaGV;
        $outsider=TaiKhoan::whereNotNull('MaGV')->where('MaGV','<>',$owner)->firstOrFail();
        Process::fake();$this->actingAs($outsider);
        foreach(['baocao.ai.pdf','baocao.ai.download','baocao.ai.trang_thai'] as $route)$this->getJson(route($route,$r->MaKhoi))->assertForbidden();
        $this->postJson(route('baocao.ai.trich_dan',$r->MaKhoi),['page'=>1,'quote'=>'caption'])->assertForbidden();
        $this->postJson(route('baocao.ai.doi_chieu',$r->MaKhoi))->assertForbidden();
        Process::assertNothingRan();
        $this->assertSame(0,DB::table('jobs')->count());
    }

    public function test_old_caption_cache_cannot_skip_new_extraction_policy(): void
    {
        $r=$this->report();$svc=app(AiReportProcessingService::class);$fp=$svc->fingerprint($r);
        $r->update(['TomTatJson'=>['_canonical_fingerprint'=>$fp,'analysis'=>['engine'=>'pdf_lab','text_complete'=>true,'status'=>'completed'],
            'caption_index'=>['version'=>'caption-text-1','status'=>'completed'],
            'requirements_review'=>['status'=>'not_configured']]]);
        $state=$svc->enqueue($r,true);
        $this->assertArrayNotHasKey('reused',$state);
        $this->assertSame('queued',$state['status']);
        $this->assertFalse($state['fresh']);
        $this->assertSame(1,DB::table('jobs')->count());
    }

    public function test_fresh_generation_resets_only_first_pass_and_continuation_keeps_checkpoint(): void
    {
        $r=$this->report();$svc=app(AiReportProcessingService::class);$state=$svc->enqueue($r,false,true,true);$flags=[];
        $ai=\Mockery::mock(AiSummaryService::class);
        $ai->shouldReceive('summarizePdf')->twice()->andReturnUsing(function($path,$stage,$options)use(&$flags){
            $flags[]=$options['fresh'];
            return ['data'=>['analysis'=>['engine'=>'pdf_lab','status'=>'partial','text_complete'=>false],
                'processing_errors'=>[['code'=>'budget_exhausted','stage'=>'summary']]]];
        });
        $ai->shouldReceive('saveVersionSummary')->twice()->andReturnNull();
        for($i=0;$i<2;$i++){
            $job=new AnalyzeReportVersion($r->MaKhoi,$state['token']);$job->withFakeQueueInteractions();$job->handle($svc,$ai);
            $job->assertReleased();$svc->updateState($r->MaKhoi,$state['token'],['next_retry_at'=>time()-1]);
        }
        $this->assertSame([true,false],$flags);
        $this->assertSame(2,$r->fresh()->TomTatJson['_processing']['attempts']);
        $svc->updateState($r->MaKhoi,$state['token'],['status'=>'partial']);
        $resume=$svc->enqueue($r,false,true,false);
        $this->assertFalse($resume['fresh']);
        $this->assertNotSame($state['token'],$resume['token']);
    }

    public function test_stopped_budget_does_not_promise_automatic_continuation(): void
    {
        $r=$this->report();$data=['_processing'=>['status'=>'partial','errors'=>[['code'=>'budget_exhausted','message'=>'Hệ thống sẽ tự tiếp tục.']]]];
        $r->update(['TomTatJson'=>$data]);
        $status=app(AiReportProcessingService::class)->status($r);
        $this->assertSame('partial',$status['status']);
        $this->assertStringContainsString('đã dừng',$status['errors'][0]['message']);
        $this->assertStringNotContainsString('sẽ tự tiếp tục',$status['errors'][0]['message']);
        $this->assertSame($data,$r->fresh()->TomTatJson);
    }
}
