<?php
namespace Tests\Feature;
use App\Models\{BaoCaoTienDo,DotNopBaoCao,KhoiBaoCaoTienDo,Nhom,TaiKhoan,TomTatBaoCao};
use App\Services\{ReportSubmissionDeletionService,AiReportProcessingService,AiSummaryService};
use App\Jobs\AnalyzeReportVersion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB,Cache,Process};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReportSubmissionDeletionTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void {parent::setUp();$this->assertStringStartsWith('ai_stability_',DB::connection()->getDatabaseName());}
    private function fixture(): array
    {
        $source=KhoiBaoCaoTienDo::with(['dotNop','baoCaoTienDo'])->findOrFail('KHOI_D223_V3_0ff1eb');
        $group=Nhom::with(['deTai','truongNhom'])->findOrFail('N_TEST737475');
        $round=$source->dotNop->replicate();$round->TrangThai='DANG_MO';$round->save();
        $parent=$source->baoCaoTienDo->replicate(['id']);$parent->MaBaoCao='DEL_'.bin2hex(random_bytes(4));$parent->save();
        $versions=[];
        for($i=1;$i<=2;$i++){
            $v=$source->replicate(['id']);$v->MaKhoi=$parent->MaBaoCao.'_V'.$i;$v->MaBaoCao=$parent->MaBaoCao;
            $v->MaDotNop=$round->MaDotNop;$v->LanNop=$i;$v->TrangThai='Chờ duyệt';$v->NhanXetGV=null;$v->NguoiNhanXet=null;$v->NgayNhanXet=null;
            $v->NgayNop=now()->addSeconds($i);$v->TomTatAI='Summary '.$i;
            $v->TomTatJson=['_submission_identity'=>['group_id'=>$group->MaNhom,'semester_id'=>$group->MaHocKy],
                'schema_version'=>6,'overall_summary'=>'Summary '.$i,'analysis'=>['status'=>'completed']];$v->save();$versions[]=$v;
        }
        return [$round,$group,$parent,...$versions];
    }
    public function test_delete_v2_restores_v1_keeps_its_identity_and_shared_file(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();$before=$v1->TomTatJson;
        $snapshot=TomTatBaoCao::first()->replicate(['id']);$snapshot->MaTomTat='TT_DEL_'.bin2hex(random_bytes(3));$snapshot->MaBaoCao=$parent->MaBaoCao;$snapshot->MaKhoi=$v2->MaKhoi;$snapshot->save();
        $result=app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v2->MaKhoi);
        $this->assertNull($v2->fresh());$this->assertSame(1,$result['latest_version']);$this->assertSame(2,$result['next_version']);
        $this->assertSame($before,$v1->fresh()->TomTatJson);$this->assertSame($v1->DuongDanFile,$parent->fresh()->DuongDanFile);
        $this->assertSame($v1->MaKhoi,TomTatBaoCao::where('MaBaoCao',$parent->MaBaoCao)->firstOrFail()->MaKhoi);
        $this->assertSame('Summary 1',TomTatBaoCao::where('MaBaoCao',$parent->MaBaoCao)->first()->TomTatJson['overall_summary']);
    }
    public function test_deleting_last_submission_resets_next_upload_to_v1(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();$svc=app(ReportSubmissionDeletionService::class);
        $svc->deletePendingVersion($round,$group,$v2->MaKhoi);$result=$svc->deletePendingVersion($round,$group,$v1->MaKhoi);
        $this->assertNull($result['latest_version']);$this->assertSame(1,$result['next_version']);$this->assertNull($parent->fresh());
    }
    public function test_older_pending_version_can_be_deleted_without_invalidating_survivor_ai_identity(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();
        $ai=app(AiReportProcessingService::class);$before=$ai->identity($v2);$fingerprint=$ai->fingerprint($v2);
        $result=app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v1->MaKhoi);
        $this->assertNull($v1->fresh());$this->assertSame(1,$v2->fresh()->LanNop);
        $this->assertSame(1,$result['latest_version']);$this->assertSame(2,$result['next_version']);
        $this->assertSame($before,$ai->identity($v2->fresh()));
        $this->assertSame($fingerprint,$ai->fingerprint($v2->fresh()));
        $this->assertSame('Summary 2',$v2->fresh()->TomTatJson['overall_summary']);
    }
    public function test_deleting_older_version_keeps_running_job_token_fingerprint_and_checkpoint(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();
        $ai=app(AiReportProcessingService::class);
        $state=$ai->enqueue($v2,false,true,false);
        $this->assertSame(2,$v2->fresh()->TomTatJson['_submission_identity']['submission_version']);
        $ai->updateState($v2->MaKhoi,$state['token'],['status'=>'running','attempts'=>1]);
        $path=$ai->progressPath($state['token']);
        if(!is_dir(dirname($path)))mkdir(dirname($path),0775,true);
        file_put_contents($path,json_encode(['map_done'=>5,'map_total'=>28]));
        try{
            app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v1->MaKhoi);
            $survivor=$v2->fresh();$kept=$ai->storedPayload($survivor)['_processing'];
            $this->assertSame(1,$survivor->LanNop);
            $this->assertSame($state['token'],$kept['token']);
            $this->assertSame('running',$kept['status']);
            $this->assertSame($state['fingerprint'],$ai->fingerprint($survivor));
            $this->assertSame(5,$ai->status($survivor)['progress']['map_done']);
            $this->assertSame(1,DB::table('jobs')->where('queue','ai-reports')->count());
        }finally{if(is_file($path))unlink($path);}
    }
    public function test_instructor_feedback_blocks_deletion_even_if_status_is_pending(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();$v2->update(['NhanXetGV'=>'Giữ lịch sử góp ý']);
        $this->expectException(ValidationException::class);app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v2->MaKhoi);
    }
    public function test_running_worker_lock_blocks_deletion(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();$lock=Cache::store('file')->lock('ai-report:'.$v2->MaKhoi,420);$this->assertTrue($lock->get());
        try{app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v2->MaKhoi);$this->fail('Expected validation');}
        catch(ValidationException $e){$this->assertNotNull($v2->fresh());}finally{$lock->release();}
    }
    public function test_only_target_queue_job_is_removed_and_late_job_does_not_call_ai(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();
        foreach([$v1,$v2] as $v)DB::table('jobs')->insert(['queue'=>'ai-reports','payload'=>json_encode(['data'=>['commandName'=>AnalyzeReportVersion::class,'command'=>serialize(new AnalyzeReportVersion($v->MaKhoi,'token'))]]),'attempts'=>0,'reserved_at'=>null,'available_at'=>time(),'created_at'=>time()]);
        app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$v2->MaKhoi);
        $this->assertSame(1,DB::table('jobs')->where('queue','ai-reports')->count());
        $this->assertStringContainsString($v1->MaKhoi,DB::table('jobs')->first()->payload);
        $ai=\Mockery::mock(AiSummaryService::class);$ai->shouldNotReceive('summarizePdf');
        (new AnalyzeReportVersion($v2->MaKhoi,'token'))->handle(app(AiReportProcessingService::class),$ai);
    }
    public function test_foreign_group_and_wrong_round_cannot_delete_submission(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();
        $foreign=$v2->replicate(['id']);$foreign->MaKhoi=$parent->MaBaoCao.'_FOREIGN';$foreign->LanNop=99;
        $foreign->TomTatJson=['_submission_identity'=>['group_id'=>'OTHER_GROUP','semester_id'=>$group->MaHocKy]];$foreign->save();
        try{app(ReportSubmissionDeletionService::class)->deletePendingVersion($round,$group,$foreign->MaKhoi);$this->fail('Expected forbidden');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());$this->assertNotNull($foreign->fresh());}
        $otherRound=$round->replicate();$otherRound->save();
        try{app(ReportSubmissionDeletionService::class)->deletePendingVersion($otherRound,$group,$v2->MaKhoi);$this->fail('Expected forbidden');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());$this->assertNotNull($v2->fresh());}
    }
    public function test_member_cannot_delete_and_leader_can_use_route(): void
    {
        [$round,$group,$parent,$v1,$v2]=$this->fixture();
        $member=\App\Models\SinhVien::whereIn('MaSV',\App\Models\ThanhVienNhom::where('MaNhom',$group->MaNhom)->pluck('MaSV'))->where('MaSV','<>',$group->truongNhom->MaSV)->firstOrFail();
        $this->actingAs(TaiKhoan::findOrFail($member->MaTK))->delete(route('sinhvien.baocao.dot_nop.xoa_phien_ban',[$round->MaDotNop,$v2->MaKhoi]))->assertForbidden();
        $this->assertNotNull($v2->fresh());
        $this->actingAs(TaiKhoan::findOrFail($group->truongNhom->MaTK))->delete(route('sinhvien.baocao.dot_nop.xoa_phien_ban',[$round->MaDotNop,$v2->MaKhoi]))->assertRedirect(route('sinhvien.baocao.dot_nop.show',$round->MaDotNop));
        $this->assertNull($v2->fresh());
    }
}
