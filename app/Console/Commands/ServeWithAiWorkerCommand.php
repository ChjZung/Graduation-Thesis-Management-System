<?php

namespace App\Console\Commands;

use App\Services\AiReportProcessingService;
use Illuminate\Foundation\Console\ServeCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/** Keep the development web server and its AI worker in one command. */
class ServeWithAiWorkerCommand extends ServeCommand
{
    private ?Process $ownedWorker = null;
    private bool $serving = false;

    public function handle()
    {
        // Laravel retries another port by calling handle() recursively.
        if ($this->serving) return $this->serveApplication();
        $this->serving = true;
        try {
            if (!$this->option('without-ai-worker')) {
                $this->startAiWorker();
                // Parent ServeCommand exits directly when handling Unix signals.
                register_shutdown_function(fn () => $this->stopOwnedWorker());
            }
            return $this->serveApplication();
        } finally {
            $this->stopOwnedWorker();
            $this->serving = false;
        }
    }

    protected function serveApplication()
    {
        return parent::handle();
    }

    protected function makeWorker(): Process
    {
        $process = new Process([PHP_BINARY, base_path('artisan'), 'ai:work'], base_path());
        $process->setTimeout(null);
        // Avoid filling a pipe during a long development session. App errors
        // remain in storage/logs/laravel.log; web output stays readable.
        $process->disableOutput();
        return $process;
    }

    protected function startAiWorker(): void
    {
        $processing = $this->laravel->make(AiReportProcessingService::class);
        if ($processing->workerRunning()) {
            $this->components->info('AI worker is already running; reusing it.');
            return;
        }
        $this->ownedWorker = $this->makeWorker();
        $this->ownedWorker->start();
        // Confirm the worker acquired its OS lock, not merely that PHP spawned.
        for ($i = 0; $i < 30; $i++) {
            if ($processing->workerRunning()) {
                $this->components->info('AI worker is ready. Reports will be processed in the background.');
                return;
            }
            if (!$this->ownedWorker->isRunning()) break;
            usleep(100000);
        }
        throw new \RuntimeException('AI worker could not start. Check storage/logs/laravel.log. Use --without-ai-worker only if a separate worker is managed elsewhere.');
    }

    protected function stopOwnedWorker(): void
    {
        $worker = $this->ownedWorker;
        $this->ownedWorker = null;
        if ($worker && $worker->isRunning()) $worker->stop(10);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['without-ai-worker', null, InputOption::VALUE_NONE, 'Start only the development web server'],
        ]);
    }
}
