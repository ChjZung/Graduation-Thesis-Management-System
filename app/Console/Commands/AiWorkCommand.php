<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AiWorkCommand extends Command
{
    protected $signature = 'ai:work {--once : Process one available job and exit}';
    protected $description = 'Run the dedicated, quota-aware report AI worker';

    public function handle(): int
    {
        $folder=storage_path('app/private');
        if (!is_dir($folder)) mkdir($folder,0775,true);
        $lock=fopen($folder.'/ai-worker.lock','c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) {
            $this->warn('AI worker is already running on this host.');
            if ($lock) fclose($lock);
            return self::SUCCESS;
        }
        try {
            $this->info('AI report worker started; submission data is saved before analysis.');
            return $this->call('queue:work',[
                'connection'=>'ai','--queue'=>'ai-reports','--sleep'=>3,'--timeout'=>390,
                '--tries'=>25,'--backoff'=>60,'--once'=>(bool) $this->option('once'),
            ]);
        } finally { flock($lock,LOCK_UN);fclose($lock); }
    }
}
