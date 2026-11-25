<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupStore;
use App\Services\Backup\MysqlBackup;
use DomainException;
use Illuminate\Console\Command;
use Throwable;

class MysqlBackupCommand extends Command
{
    protected $signature = 'backup:mysql {action : run, tick, list hoặc restore} {id?} {--confirm= : mysql://host:port/schema}';

    protected $description = 'Sao lưu MySQL và phục hồi vào schema độc lập';

    public function handle(MysqlBackup $backup, BackupStore $store): int
    {
        try {
            switch ($this->argument('action')) {
                case 'tick':
                    $backup->tick();
                    return self::SUCCESS;
                case 'list':
                    $this->table(['ID', 'Trạng thái', 'Kiểm chứng', 'UTC'], array_map(
                        fn ($row) => [$row['id'], $row['status'], $row['verification'], gmdate('Y-m-d H:i:s', $row['created_at'])],
                        array_filter($store->all(), fn ($row) => !isset($row['deleted_at']))
                    ));
                    return self::SUCCESS;
                case 'run':
                    $row = $backup->run();
                    $ok = $row['status'] === 'SUCCESS';
                    break;
                case 'restore':
                    $row = $backup->restore((string) $this->argument('id'), (string) $this->option('confirm'));
                    $ok = $row['verification'] === 'VERIFIED';
                    break;
                default:
                    throw new DomainException('Chọn run, tick, list hoặc restore.');
            }
            $this->line($row['id'].' · '.$row['status'].' · '.$row['verification']);
            if (!$ok) $this->error($row['restore_error'] ?? $row['error'] ?? 'Tác vụ chưa hoàn tất.');
            return $ok ? self::SUCCESS : self::FAILURE;
        } catch (DomainException $error) {
            $this->error($error->getMessage());
        } catch (Throwable $error) {
            $this->error('Tác vụ MySQL không hoàn tất ('.get_class($error).').');
        }
        return self::FAILURE;
    }
}

