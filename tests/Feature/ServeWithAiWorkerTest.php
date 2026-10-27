<?php

namespace Tests\Feature;

use App\Console\Commands\ServeWithAiWorkerCommand;
use App\Services\AiReportProcessingService;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Console\ServeCommand;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ServeWithAiWorkerTest extends TestCase
{
    private function command(Process $worker, bool $disabled = false): ServeWithAiWorkerCommand
    {
        $command = new class($worker) extends ServeWithAiWorkerCommand {
            public bool $failServer = false;
            public int $serverCalls = 0;
            public function initializeOutput(): void { $this->components = new \Illuminate\Console\View\Components\Factory($this->output); }
            public function __construct(private Process $fakeWorker) { parent::__construct(); }
            protected function makeWorker(): Process { return $this->fakeWorker; }
            protected function serveApplication() {
                $this->serverCalls++;
                if ($this->failServer) throw new \RuntimeException('server failed');
                return 0;
            }
        };
        $input = new ArrayInput($disabled ? ['--without-ai-worker'=>true] : [], $command->getDefinition());
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, new BufferedOutput()));
        $command->initializeOutput();
        return $command;
    }

    public function test_serve_resolves_to_integrated_command(): void
    {
        $this->assertInstanceOf(ServeWithAiWorkerCommand::class, $this->app->make(ServeCommand::class));
    }

    public function test_starts_worker_and_stops_only_its_child_after_server_finishes(): void
    {
        $service = Mockery::mock(AiReportProcessingService::class);
        $service->shouldReceive('workerRunning')->twice()->andReturn(false, true);
        $this->app->instance(AiReportProcessingService::class, $service);
        $worker = Mockery::mock(Process::class);
        $worker->shouldReceive('start')->once();
        $worker->shouldReceive('isRunning')->once()->andReturn(true);
        $worker->shouldReceive('stop')->once()->with(10);
        $command = $this->command($worker);
        $this->assertSame(0, $command->handle());
        $this->assertSame(1, $command->serverCalls);
    }

    public function test_existing_worker_is_never_started_or_stopped(): void
    {
        $service = Mockery::mock(AiReportProcessingService::class);
        $service->shouldReceive('workerRunning')->once()->andReturn(true);
        $this->app->instance(AiReportProcessingService::class, $service);
        $worker = Mockery::mock(Process::class);
        $worker->shouldNotReceive('start');
        $worker->shouldNotReceive('stop');
        $this->assertSame(0, $this->command($worker)->handle());
    }

    public function test_child_is_cleaned_up_even_when_server_fails(): void
    {
        $service = Mockery::mock(AiReportProcessingService::class);
        $service->shouldReceive('workerRunning')->twice()->andReturn(false, true);
        $this->app->instance(AiReportProcessingService::class, $service);
        $worker = Mockery::mock(Process::class);
        $worker->shouldReceive('start')->once();
        $worker->shouldReceive('isRunning')->once()->andReturn(true);
        $worker->shouldReceive('stop')->once()->with(10);
        $command = $this->command($worker);
        $command->failServer = true;
        $this->expectExceptionMessage('server failed');
        $command->handle();
    }

    public function test_startup_failure_does_not_report_a_working_server(): void
    {
        $service = Mockery::mock(AiReportProcessingService::class);
        $service->shouldReceive('workerRunning')->twice()->andReturn(false);
        $this->app->instance(AiReportProcessingService::class, $service);
        $worker = Mockery::mock(Process::class);
        $worker->shouldReceive('start')->once();
        $worker->shouldReceive('isRunning')->twice()->andReturn(false);
        $worker->shouldNotReceive('stop');
        $command = $this->command($worker);
        try {
            $command->handle();
            $this->fail('Expected worker startup failure');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('AI worker could not start', $e->getMessage());
            $this->assertSame(0, $command->serverCalls);
        }
    }

    public function test_opt_out_does_not_touch_worker(): void
    {
        $service = Mockery::mock(AiReportProcessingService::class);
        $service->shouldNotReceive('workerRunning');
        $this->app->instance(AiReportProcessingService::class, $service);
        $worker = Mockery::mock(Process::class);
        $worker->shouldNotReceive('start');
        $this->assertSame(0, $this->command($worker, true)->handle());
    }
}
