<?php

namespace App\Services\Backup;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MysqlBackup
{
    public function __construct(
        private readonly BackupStore $store,
        private readonly MysqlBackupClient $client,
    ) {}

    public function enqueue(string $actor): array
    {
        $this->enabled();
        return $this->store->create('manual', $actor);
    }

    public function run(): array
    {
        $this->enabled();
        return $this->store->exclusive(function () {
            $this->reconcile();
            return $this->dump($this->store->create('manual', 'artisan'));
        });
    }

    public function tick(): void
    {
        if (!config('mysql_backup.enabled')) return;
        $this->store->exclusive(function () {
            $now = time();
            $slot = $this->dueSlot($now);
            $state = $this->store->schedule();
            $state['last_tick_at'] = $now;
            $state['next_due_at'] = $slot->modify('+1 day')->getTimestamp();
            $this->store->setSchedule($state);
            $this->reconcile();

            $latestAuto = 0;
            $lastAutoAttempt = 0;
            $pending = null;
            foreach ($this->store->all() as $record) {
                if (isset($record['deleted_at'])) continue;
                if ($record['type'] === 'automatic') {
                    $lastAutoAttempt = max($lastAutoAttempt, $record['created_at']);
                    if ($record['status'] === 'SUCCESS') $latestAuto = max($latestAuto, $record['created_at']);
                }
                if ($record['status'] === 'PENDING') $pending = $record;
            }

            if (config('mysql_backup.automatic') && $latestAuto < $slot->getTimestamp()
                && $lastAutoAttempt <= $now - 900) {
                // Catch up only the latest daily slot, even after several days offline.
                $this->dump($this->store->create('automatic', null));
            } elseif ($pending) {
                $this->dump($pending);
            }
            $this->cleanup($now);
        });
    }

    public function overview(): array
    {
        $now = time();
        $state = $this->store->schedule();
        $slot = $this->dueSlot($now);
        $valid = array_filter($this->store->all(), fn ($r) => $r['status'] === 'SUCCESS' && !isset($r['deleted_at']));
        $latest = max([0, ...array_column($valid, 'finished_at')]);
        $lastAuto = max([0, ...array_column(array_filter($valid, fn ($r) => $r['type'] === 'automatic'), 'created_at')]);
        $heartbeatWindow = max(180, (int) config('mysql_backup.timeout') + 60);
        return [
            'scheduler_seen'=>($state['last_tick_at'] ?? 0) >= $now - $heartbeatWindow,
            'last_tick_at'=>$state['last_tick_at'] ?? null,
            'next_due_at'=>$slot->modify('+1 day')->getTimestamp(),
            'last_success_at'=>$latest ?: null,
            'automatic'=>(bool) config('mysql_backup.automatic'),
            'overdue'=>config('mysql_backup.automatic') && $now >= $slot->getTimestamp() + 900
                && $lastAuto < $slot->getTimestamp(),
            'count'=>count($valid),
            'bytes'=>array_sum(array_column($valid, 'bytes')),
        ];
    }

    public function restore(string $id, string $confirmation): array
    {
        $this->enabled();
        return $this->store->exclusive(function () use ($id, $confirmation) {
            $this->reconcile();
            $record = $this->store->get($id);
            $path = $this->store->verify($record);
            $target = $this->client->settings('restore');
            $appConnection = config('database.default');
            $appSettings = config("database.connections.$appConnection", []);
            $appSchema = (new \Illuminate\Support\ConfigurationUrlParser)->parseConfiguration($appSettings)['database'] ?? '';
            $blocked = [$appSchema, config('mysql_backup.source.schema'), $record['source']['schema'] ?? '',
                'mysql', 'sys', 'information_schema', 'performance_schema'];
            if (in_array(strtolower($target['schema']), array_map(fn ($name) => strtolower((string) $name), $blocked), true)) {
                throw new DomainException('Schema đích trùng database nguồn, database ứng dụng hoặc schema hệ thống.');
            }
            $destination = "mysql://{$target['host']}:{$target['port']}/{$target['schema']}";
            if (!hash_equals($destination, $confirmation)) {
                throw new DomainException('Xác nhận chính xác kết nối restore bằng --confirm='.$destination);
            }
            $db = $this->client->connect($target);
            $server = $this->client->server($db);
            if ($server['db'] !== $target['schema'] || (int) $server['port'] !== (int) $target['port']) {
                throw new DomainException('MySQL đang kết nối sai cổng hoặc schema đích.');
            }
            $this->client->assertLimitedRestoreAccount($db, $target);
            if ($this->client->inventory($db) !== []) throw new DomainException('Schema đích phải trống.');

            $record['verification'] = 'VERIFYING';
            $record['restore_target'] = $destination;
            $record['restore_started_at'] = time();
            $started = microtime(true);
            $this->store->save($record);
            $this->store->audit('restore_started', $id, 'artisan');
            try {
                $this->client->import($target, $path, $this->store->root());
                if ($record['tables'] !== $this->client->inventory($db)) {
                    throw new DomainException('Schema sau restore khác bản đã sao lưu.');
                }
                $this->client->verifyData($db, $record['rows']);
                $record['verification'] = 'VERIFIED';
                unset($record['restore_error']);
            } catch (Throwable $error) {
                $record['verification'] = 'FAILED';
                $record['restore_error'] = $this->safeError($error).' Schema đích có thể chứa dữ liệu một phần. Cần DBA xử lý riêng.';
            }
            $record['restore_finished_at'] = time();
            $record['restore_seconds'] = round(microtime(true) - $started, 3);
            $this->store->save($record);
            $this->store->audit('restore_'.strtolower($record['verification']), $id, 'artisan');
            return $record;
        });
    }

    private function dump(array $record): array
    {
        $id = $record['id'];
        $part = $this->store->path($id, '.part');
        $record['status'] = 'RUNNING';
        $record['started_at'] = time();
        $this->store->save($record);
        $started = microtime(true);

        try {
            $source = $this->client->settings('source');
            $db = $this->client->connect($source);
            $server = $this->client->server($db);
            if ($server['db'] !== $source['schema'] || (int) $server['port'] !== (int) $source['port']) {
                throw new DomainException('MySQL đang kết nối sai cổng hoặc schema nguồn.');
            }
            $tables = $this->client->inventory($db);
            if (!$tables) throw new DomainException('Schema nguồn không có bảng để sao lưu.');
            $space = @disk_free_space($this->store->root());
            if ($space === false || $space < 16 * 1024 * 1024) {
                throw new DomainException('Không đủ dung lượng trống tối thiểu để bắt đầu sao lưu.');
            }

            $record['client_version'] = $this->client->dump($source, $part, $this->store->root());
            if ($tables !== $this->client->inventory($db)) throw new DomainException('Schema nguồn thay đổi trong lúc sao lưu.');
            $record['rows'] = $this->client->inspectRows($part, $tables);
            $record['tables'] = $tables;
            $record['bytes'] = filesize($part);
            $record['sha256'] = hash_file('sha256', $part);
            if (!$record['bytes'] || !$record['sha256'] || !@rename($part, $this->store->path($id))) {
                throw new DomainException('Không công bố được file SQL hoàn chỉnh.');
            }
            $record['source'] = ['schema'=>$source['schema'], 'version'=>$server['version']];
            $record['status'] = 'SUCCESS';
        } catch (Throwable $error) {
            $record['status'] = 'FAILED';
            $record['error'] = $this->safeError($error);
            $this->store->removeFiles($id);
        }
        $record['finished_at'] = time();
        $record['seconds'] = round(microtime(true) - $started, 3);
        $this->store->save($record);
        $this->store->audit('backup_'.strtolower($record['status']), $id, $record['actor']);
        return $record;
    }

    private function reconcile(): void
    {
        foreach ($this->store->all() as $record) {
            $id = $record['id'];
            if (isset($record['deleted_at']) || $record['status'] === 'FAILED') {
                $this->store->removeFiles($id);
            }
            if ($record['status'] === 'RUNNING') {
                $record['status'] = 'FAILED';
                $record['finished_at'] = time();
                $record['error'] = 'Tiến trình dừng trước khi công bố backup.';
                $this->store->save($record);
                $this->store->removeFiles($id);
                $this->store->audit('backup_interrupted', $id);
            }
            if ($record['verification'] === 'VERIFYING') {
                $record['verification'] = 'FAILED';
                $record['restore_error'] = 'Restore bị gián đoạn. Cần kiểm tra schema đích trước khi thử lại.';
                $record['restore_finished_at'] = time();
                $this->store->save($record);
                $this->store->audit('restore_interrupted', $id);
            }
        }
        foreach (glob($this->store->root().'/*.part') ?: [] as $part) {
            // An HTTP request may be publishing a new PENDING manifest independently.
            if (str_ends_with($part, '.cnf.part') || filemtime($part) < time() - 3600) @unlink($part);
        }
    }

    private function cleanup(int $now): void
    {
        $cutoff = $now - max(1, (int) config('mysql_backup.retention_days')) * 86400;
        foreach (array_reverse($this->store->all()) as $record) {
            if ($record['status'] !== 'SUCCESS' || isset($record['deleted_at']) || $record['created_at'] >= $cutoff) continue;
            try { $this->store->delete($record['id'], 'scheduler'); }
            catch (DomainException $error) {
                if ($error->getMessage() !== 'Cần giữ bản sao lưu hợp lệ cuối cùng.') throw $error;
            }
        }
    }

    private function dueSlot(int $now): DateTimeImmutable
    {
        $time = config('mysql_backup.time');
        if (!is_string($time) || !preg_match('/\A([01]\d|2[0-3]):([0-5]\d)\z/', $time, $parts)) {
            throw new DomainException('Giờ sao lưu phải có dạng HH:mm.');
        }
        try { $timezone = new DateTimeZone(config('mysql_backup.timezone')); }
        catch (Throwable) { throw new DomainException('Múi giờ sao lưu không hợp lệ.'); }
        $local = (new DateTimeImmutable('@'.$now))->setTimezone($timezone);
        $slot = $local->setTime((int) $parts[1], (int) $parts[2]);
        return $slot > $local ? $slot->modify('-1 day') : $slot;
    }

    private function enabled(): void
    {
        if (!config('mysql_backup.enabled')) throw new DomainException('Chức năng sao lưu chưa được bật.');
    }

    private function safeError(Throwable $error): string
    {
        if ($error instanceof DomainException) return $error->getMessage();
        Log::warning('MySQL backup operation failed', ['type'=>get_class($error)]);
        return 'MySQL hoặc hệ thống lưu trữ trả lỗi. Kiểm tra cấu hình và quyền.';
    }
}

