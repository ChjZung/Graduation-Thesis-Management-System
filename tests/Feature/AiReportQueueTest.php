<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeReportVersion;
use App\Models\KhoiBaoCaoTienDo;
use App\Services\AiReportProcessingService;
use App\Services\AiSummaryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Mockery;
use Tests\TestCase;

/** Uses a rolled-back copy of a local report; never calls an external API. */
class AiReportQueueTest extends TestCase
{
    use DatabaseTransactions;

    public function test_repeated_analysis_of_same_verified_version_does_not_enqueue_or_change_result(): void
    {
        $report=$this->report();
        $processing=app(AiReportProcessingService::class);
        $hash=$processing->fingerprint($report);
        $payload=['schema_version'=>6,'_canonical_fingerprint'=>$hash,
            '_source_hash'=>hash_file('sha256',$processing->path($report)),
            'overview_summary'=>'Canonical verified overview','chapters'=>[['id'=>'S1']],
            'completed_work'=>[],'analysis'=>['status'=>'completed','text_complete'=>true],
            '_processing'=>['status'=>'completed','token'=>'canonical-test','fingerprint'=>$hash]];
        $report->update(['TomTatJson'=>$payload]);
        $before=DB::table('jobs')->where('queue','ai-reports')->count();
        $state=$processing->enqueue($report);
        $again=$processing->enqueue($report, true);
        $this->assertTrue($state['reused']);
        $this->assertTrue($again['reused']);
        $this->assertSame($before,DB::table('jobs')->where('queue','ai-reports')->count());
        $this->assertSame($payload,$report->fresh()->TomTatJson);
        $report->dotNop->update(['NoiDungYeuCau'=>'Changed criteria creates a new review input']);
        $this->assertArrayNotHasKey('reused',$processing->enqueue($report->fresh()));
    }

    public function test_python_timeout_retries_from_checkpoints_instead_of_failing_extraction(): void
    {
        $report=$this->report();
        $originalFile=$report->DuongDanFile;
        $processing=app(AiReportProcessingService::class);
        $state=$processing->enqueue($report);
        $path=$processing->progressPath($state['token']);
        if (!is_dir(dirname($path))) mkdir(dirname($path),0775,true);
        file_put_contents($path,json_encode(['stage'=>'summary_part','map_done'=>8,'map_total'=>30,
            'completed_calls'=>8,'reused_calls'=>0]));
        try {
            $process=new \Symfony\Component\Process\Process(['python','fixture.py']);
            $process->setTimeout(360);
            $exception=new \Illuminate\Process\Exceptions\ProcessTimedOutException(
                new \Symfony\Component\Process\Exception\ProcessTimedOutException($process,
                    \Symfony\Component\Process\Exception\ProcessTimedOutException::TYPE_GENERAL),
                Process::result());
            Process::fake(fn () => throw $exception);
            $job=new AnalyzeReportVersion($report->MaKhoi,$state['token']);
            $job->withFakeQueueInteractions();
            $job->handle($processing,app(AiSummaryService::class));
            $job->assertReleased();
            $fresh=$report->fresh();
            $this->assertSame('waiting',$fresh->TomTatJson['_processing']['status']);
            $this->assertSame('timeout',$fresh->TomTatJson['_processing']['errors'][0]['code']);
            $this->assertSame(8,$fresh->TomTatJson['analysis']['analyzed_chunks']);
            $this->assertSame(30,$fresh->TomTatJson['analysis']['total_chunks']);
            $this->assertSame($originalFile,$fresh->DuongDanFile);
            $this->assertSame($report->TrangThai,$fresh->TrangThai);
            $status=$processing->status($fresh);
            $this->assertSame(8,$status['progress']['completed_calls']);
            $this->assertSame(30,$status['progress']['map_total']);
        } finally { if (is_file($path)) unlink($path); }
    }

    public function test_process_exit_without_structured_extraction_error_is_retryable(): void
    {
        Process::fake(fn () => Process::result(errorOutput:'Temporary process failure',exitCode:1));
        $result=app(AiSummaryService::class)->summarizePdf('fixture.pdf');
        $this->assertSame('service_unavailable',$result['data']['processing_errors'][0]['code']);
        $this->assertSame('waiting',app(AiReportProcessingService::class)->retryState($result['data'],false,1)['status']);
    }

    public function test_review_with_changed_brief_reorganizes_flexible_summary(): void
    {
        $report = $this->report();
        $report->update(['TomTatJson'=>['schema_version'=>5,
            'summary_scope'=>['context_hash'=>'previous-brief']]]);
        $state = app(AiReportProcessingService::class)->enqueue($report, true);
        $this->assertSame('full', $state['mode']);
    }

    public function test_review_with_same_brief_preserves_flexible_summary(): void
    {
        $report = $this->report();
        $report->update(['TomTatJson'=>['schema_version'=>5,
            'summary_scope'=>['context_hash'=>$report->dotNop->maNguCanhDoiChieuAi()]]]);
        $state = app(AiReportProcessingService::class)->enqueue($report, true);
        $this->assertSame('review', $state['mode']);
    }

    private function report(): KhoiBaoCaoTienDo
    {
        $source=KhoiBaoCaoTienDo::whereNotNull('MaDotNop')->whereNotNull('DuongDanFile')->first();
        if (!$source) $this->markTestSkipped('A local report fixture is required.');
        $copy=$source->replicate(['id']);
        $copy->MaKhoi='TEST_AI_'.bin2hex(random_bytes(6));
        $copy->LanNop=999;
        $copy->TomTatJson=null;
        $copy->TomTatAI=null;
        $copy->save();
        return $copy;
    }

    public function test_enqueue_is_async_and_duplicate_clicks_create_one_database_job(): void
    {
        config(['queue.default'=>'sync']);
        $report=$this->report();
        $service=app(AiReportProcessingService::class);
        $before=DB::table('jobs')->where('queue','ai-reports')->count();
        $a=$service->enqueue($report);
        $b=$service->enqueue($report);
        $this->assertSame($a['token'],$b['token']);
        $this->assertSame('queued',$a['status']);
        $this->assertSame($before+1,DB::table('jobs')->where('queue','ai-reports')->count());
        $this->assertNull($report->fresh()->TomTatAI);
        $this->assertSame('sync',config('queue.default')); // Dedicated connection overrides the HTTP default.
    }

    public function test_transaction_rollback_removes_pending_state_and_job_together(): void
    {
        $report=$this->report();
        $before=DB::table('jobs')->where('queue','ai-reports')->count();
        DB::beginTransaction();
        app(AiReportProcessingService::class)->enqueue($report);
        DB::rollBack();
        $this->assertSame($before,DB::table('jobs')->where('queue','ai-reports')->count());
        $this->assertNull($report->fresh()->TomTatJson);
    }

    public function test_old_worker_cannot_write_after_another_generation_is_requested(): void
    {
        $report=$this->report();$processing=app(AiReportProcessingService::class);
        $old=$processing->enqueue($report);
        $processing->updateState($report->MaKhoi,$old['token'],['status'=>'partial']);
        $new=$processing->enqueue($report);
        app(AiSummaryService::class)->saveVersionSummary($report,['data'=>['overall_summary'=>'Stale result']],['token'=>$old['token']]);
        $this->assertSame($new['token'],$report->fresh()->TomTatJson['_processing']['token']);
        $this->assertArrayNotHasKey('overall_summary',$report->fresh()->TomTatJson);
    }

    public function test_worker_saves_partial_result_and_schedules_quota_retry_without_losing_submission(): void
    {
        $report=$this->report();$processing=app(AiReportProcessingService::class);
        $state=$processing->enqueue($report);
        $ai=Mockery::mock(AiSummaryService::class)->makePartial();
        $result=$ai->formatSummarizeResult(['total_pages'=>62,'data'=>[
            'overall_summary'=>'Source-backed result',
            'analysis'=>['status'=>'partial','total_pages'=>62,'analyzed_chunks'=>4,'total_chunks'=>5,
                'errors'=>[['stage'=>'summary_part','code'=>'rate_limit','quota'=>'minute','retry_after'=>65,'message'=>'Wait']]],
        ]]);
        $ai->shouldReceive('summarizePdf')->once()->andReturn($result);
        $job=new AnalyzeReportVersion($report->MaKhoi,$state['token']);
        $job->withFakeQueueInteractions();
        $job->handle($processing,$ai);
        $job->assertReleased();
        $fresh=$report->fresh();
        $this->assertSame('Source-backed result',$fresh->TomTatAI);
        $this->assertSame('waiting',$fresh->TomTatJson['_processing']['status']);
        $this->assertSame(4,$fresh->TomTatJson['analysis']['analyzed_chunks']);
        $this->assertSame($report->TrangThai,$fresh->TrangThai);
    }

    public function test_older_submission_cannot_replace_latest_legacy_summary_snapshot(): void
    {
        $report=$this->report();
        $older=$report->replicate(['id']);$older->MaKhoi='TEST_AI_OLD_'.bin2hex(random_bytes(5));$older->LanNop=998;$older->save();
        $ai=app(AiSummaryService::class);
        $result=$ai->formatSummarizeResult(['total_pages'=>1,'data'=>['overall_summary'=>'Latest','analysis'=>['status'=>'completed','total_pages'=>1,'analyzed_chunks'=>1]]]);
        $ai->saveVersionSummary($report,$result);
        $result['data']['overall_summary']='Older';
        $ai->saveVersionSummary($older,$result);
        $this->assertSame($report->MaKhoi,DB::table('TomTatBaoCao')->where('MaBaoCao',$report->MaBaoCao)->value('MaKhoi'));
        $this->assertSame('Latest',DB::table('TomTatBaoCao')->where('MaBaoCao',$report->MaBaoCao)->value('NoiDungAI'));
    }
    public function test_changed_brief_during_processing_cannot_receive_stale_result(): void
    {
        $report=$this->report();$processing=app(AiReportProcessingService::class);
        $state=$processing->enqueue($report);
        $ai=Mockery::mock(AiSummaryService::class)->makePartial();
        $ai->shouldReceive('summarizePdf')->once()->andReturnUsing(function () use ($report,$ai) {
            $report->dotNop->update(['NoiDungYeuCau'=>'Changed while processing']);
            return $ai->formatSummarizeResult(['data'=>['overall_summary'=>'Outdated answer']]);
        });
        (new AnalyzeReportVersion($report->MaKhoi,$state['token']))->handle($processing,$ai);
        $this->assertNull($report->fresh()->TomTatAI);
        $this->assertSame('partial',$report->fresh()->TomTatJson['_processing']['status']);
    }

    public function test_status_endpoint_requires_authentication(): void
    {
        $this->getJson('/bao-cao-ai/UNKNOWN/trang-thai')->assertRedirect(route('login'));
    }


    public function test_offline_worker_is_reported_without_mutating_or_requeueing_submission(): void
    {
        $report = $this->report();
        $processing = app(AiReportProcessingService::class);
        $state = $processing->enqueue($report);
        $processing->updateState($report->MaKhoi, $state['token'], [
            'queued_at' => time() - 60, 'updated_at' => time() - 60,
        ]);
        $before = $report->fresh()->TomTatJson;
        $jobCount = DB::table('jobs')->where('queue', 'ai-reports')->count();
        $offline = Mockery::mock(AiReportProcessingService::class)->makePartial();
        $offline->shouldReceive('workerRunning')->once()->andReturn(false);
        $status = $offline->status($report->fresh());
        $this->assertFalse($status['worker_running']);
        $this->assertSame('queued', $status['status']);
        $this->assertSame('worker_offline', $status['errors'][0]['code']);
        $this->assertStringContainsString('Không cần nộp lại', $status['message']);
        $this->assertSame($before, $report->fresh()->TomTatJson);
        $this->assertSame($jobCount, DB::table('jobs')->where('queue', 'ai-reports')->count());
    }

    public function test_healthy_worker_does_not_replace_api_cooldown_message(): void
    {
        $report = $this->report();
        $processing = app(AiReportProcessingService::class);
        $state = $processing->enqueue($report);
        $processing->updateState($report->MaKhoi, $state['token'], [
            'status'=>'waiting', 'queued_at'=>time()-60, 'next_retry_at'=>time()+65,
            'message'=>'Đang chờ giới hạn API.',
            'errors'=>[['stage'=>'summary_part','code'=>'rate_limit','message'=>'Đang chờ API']],
        ]);
        $healthy = Mockery::mock(AiReportProcessingService::class)->makePartial();
        $healthy->shouldReceive('workerRunning')->once()->andReturn(true);
        $status = $healthy->status($report->fresh());
        $this->assertTrue($status['worker_running']);
        $this->assertSame('Đang chờ giới hạn API.', $status['message']);
        $this->assertSame('rate_limit', $status['errors'][0]['code']);
        $this->assertGreaterThan(time(), $status['next_retry_at']);
    }
}
