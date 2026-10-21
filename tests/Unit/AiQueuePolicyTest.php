<?php

namespace Tests\Unit;

use App\Services\AiResultMerger;
use App\Services\AiReportProcessingService;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;

class AiQueuePolicyTest extends TestCase
{
    private $previousContainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer=Container::getInstance();
        $container=new Container;
        $container->instance('config',new Repository(['ai_processing'=>['max_passes'=>20]]));
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        Container::setInstance($this->previousContainer);
        parent::tearDown();
    }

    public function test_complete_summary_survives_a_failed_reanalysis(): void
    {
        $old=['overall_summary'=>'Verified result','analysis'=>['status'=>'completed','analyzed_chunks'=>5],'_source_hash'=>'same'];
        $new=['overall_summary'=>'Incomplete','analysis'=>['status'=>'partial','analyzed_chunks'=>1,'total_pages'=>62],'_source_hash'=>'same'];
        $this->assertSame('Verified result',(new AiResultMerger)->merge($old,$new)['overall_summary']);
        $new['_source_hash']='different';
        $this->assertSame('Incomplete',(new AiResultMerger)->merge($old,$new)['overall_summary']);
    }

    public function test_verified_requirement_survives_audit_failure_but_not_changed_context(): void
    {
        $item=['id'=>1,'requirement'=>'Survey','status'=>'met','evidence'=>[['verified'=>true,'relevance_verified'=>true,'page'=>9,'quote'=>'Original']]];
        $old=['requirements_review'=>['context_hash'=>'brief','status'=>'partial','items'=>[$item]]];
        $failed=array_merge($item,['status'=>'insufficient_evidence','citation_audit_status'=>'failed','evidence'=>[]]);
        $new=['requirements_review'=>['context_hash'=>'brief','status'=>'service_error','items'=>[$failed]]];
        $merged=(new AiResultMerger)->merge($old,$new,true);
        $this->assertSame('met',$merged['requirements_review']['items'][0]['status']);
        $this->assertSame(1,$merged['requirements_review']['counts']['met']);
        $new['requirements_review']['context_hash']='changed';
        $this->assertSame('insufficient_evidence',(new AiResultMerger)->merge($old,$new,true)['requirements_review']['items'][0]['status']);
    }

    public function test_review_only_preserves_summary_and_three_progress_groups(): void
    {
        $old=['overall_summary'=>'Summary','completed_work'=>[['content'=>'Work']],'difficulties'=>[],'next_steps'=>[]];
        $merged=(new AiResultMerger)->merge($old,['requirements_review'=>['status'=>'completed']],true);
        $this->assertSame('Summary',$merged['overall_summary']);
        $this->assertSame($old['completed_work'],$merged['completed_work']);
        $this->assertSame('completed',$merged['requirements_review']['status']);
    }

    public function test_audit_quota_is_a_wait_and_retains_provider_delay(): void
    {
        $data=['analysis'=>['status'=>'completed'],'requirements_review'=>['status'=>'service_error','citation_audit'=>['error'=>['stage'=>'citation_audit','code'=>'rate_limit','quota'=>'day','retry_after'=>5000]]]];
        $next=(new AiReportProcessingService)->retryState($data,false,1);
        $this->assertSame('waiting',$next['status']);
        $this->assertGreaterThanOrEqual(time()+4999,$next['next_retry_at']);
        $this->assertSame('citation_audit',$next['errors'][0]['stage']);
    }

    public function test_failed_review_only_is_not_reported_as_completed(): void
    {
        $next=(new AiReportProcessingService)->retryState(['processing_errors'=>[['code'=>'timeout']]],true,1);
        $this->assertSame('waiting',$next['status']);
    }

    public function test_fatal_errors_and_retry_cap_stop_without_looping(): void
    {
        $service=new AiReportProcessingService;
        $data=['analysis'=>['status'=>'partial','errors'=>[['code'=>'authentication']]]];
        $this->assertSame('partial',$service->retryState($data,false,1)['status']);
        $data['analysis']['errors']=[['code'=>'timeout']];
        $this->assertSame('partial',$service->retryState($data,false,20)['status']);
        $this->assertSame('completed',$service->retryState(['analysis'=>['status'=>'completed']],false,1)['status']);
    }
}
