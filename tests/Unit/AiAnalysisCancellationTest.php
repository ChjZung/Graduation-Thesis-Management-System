<?php

namespace Tests\Unit;

use App\Services\AiSummaryService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class AiAnalysisCancellationTest extends TestCase
{
    public function test_running_process_receives_stop_on_cancel(): void
    {
        $process = Mockery::mock();
        $child = Mockery::mock();
        $process->shouldReceive('start')->once()->andReturn($child);
        $child->shouldReceive('running')->twice()->andReturnTrue();
        $child->shouldReceive('stop')->once()->with(0);
        $child->shouldNotReceive('wait');
        $checks = 0;
        $run = new ReflectionMethod(AiSummaryService::class, 'runAnalysisProcess');
        self::assertNull($run->invoke(new AiSummaryService, $process, [PHP_BINARY], static function () {},
            static function () use (&$checks): bool { return ++$checks === 1; }));
        self::assertSame(2, $checks);
    }

    public function test_cancelled_run_does_not_launch_a_process(): void
    {
        $process = Mockery::mock();
        $process->shouldNotReceive('start');
        $run = new ReflectionMethod(AiSummaryService::class, 'runAnalysisProcess');
        self::assertNull($run->invoke(new AiSummaryService, $process, [PHP_BINARY], static function () {}, static fn () => false));
    }
}
