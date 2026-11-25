<?php

namespace App\Services\Backup;

use DomainException;
use Illuminate\Support\Str;

class BackupStore
{
    public function root(): string
    {
        $root = config('mysql_backup.root');
        if (!is_dir($root) && !@mkdir($root, 0700, true) && !is_dir($root)) {
            throw new DomainException('Không tạo được thư mục sao lưu riêng.');
        }

        return $root;
    }

    public function exclusive(callable $action): mixed
    {
        $lock = $this->lock();
        try { return $action(); }
        finally { fclose($lock); }
    }

    /** Return a lease that is released when its file handle is closed. */
    public function lock(): mixed
    {
        $lock = @fopen($this->root().'/operation.lock', 'c');
        if (!$lock) throw new DomainException('Không mở được khóa sao lưu.');
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            throw new DomainException('Đang có tác vụ sao lưu hoặc phục hồi khác.');
        }
        return $lock;
    }

    public function create(string $type, ?string $actor): array
    {
        $record = ['id'=>(string) Str::uuid(), 'created_at'=>time(), 'type'=>$type, 'actor'=>$actor,
            'status'=>'PENDING', 'verification'=>'NOT_VERIFIED', 'scope'=>'mysql_tables'];
        $this->save($record);
        $this->audit('queued', $record['id'], $actor);
        return $record;
    }

    public function path(string $id, string $suffix = '.sql'): string
    {
        if (!Str::isUuid($id)) {
            throw new DomainException('Mã sao lưu không hợp lệ.');
        }
        return $this->root().'/'.$id.$suffix;
    }

    public function get(string $id): array
    {
        $path = $this->path($id, '.json');
        $record = is_file($path) && !is_link($path) ? $this->read($path) : [];
        if (($record['id'] ?? null) !== $id || !is_int($record['created_at'] ?? null)
            || !in_array($record['status'] ?? null, ['PENDING','RUNNING','SUCCESS','FAILED'], true)
            || !in_array($record['verification'] ?? null, ['NOT_VERIFIED','VERIFYING','VERIFIED','FAILED'], true)) {
            throw new DomainException('Không tìm thấy bản sao lưu.');
        }
        return $record;
    }

    public function all(): array
    {
        $records = [];
        foreach (glob($this->root().'/*.json') ?: [] as $file) {
            $id = basename($file, '.json');
            if (Str::isUuid($id) && !is_link($file)) {
                try { $records[] = $this->get($id); }
                catch (DomainException) { continue; }
            }
        }
        usort($records, fn ($a,$b) => $b['created_at'] <=> $a['created_at']);
        return $records;
    }

    public function save(array $record): void
    {
        $this->write($this->path($record['id'], '.json'), $record);
    }

    public function schedule(): array
    {
        return $this->read($this->root().'/scheduler.json');
    }

    public function setSchedule(array $state): void
    {
        $this->write($this->root().'/scheduler.json', $state);
    }

    public function audit(string $event, string $id, ?string $actor = null): void
    {
        $line = json_encode(['at'=>time(), 'event'=>$event, 'id'=>$id, 'actor'=>$actor], JSON_THROW_ON_ERROR).PHP_EOL;
        if (@file_put_contents($this->root().'/audit.ndjson', $line, FILE_APPEND | LOCK_EX) === false) {
            throw new DomainException('Không ghi được nhật ký sao lưu.');
        }
    }

    public function verify(array $record): string
    {
        $path = $this->path($record['id']);
        if (isset($record['deleted_at']) || ($record['status'] ?? '') !== 'SUCCESS' || !is_file($path) || is_link($path)
            || filesize($path) !== ($record['bytes'] ?? null)
            || !hash_equals((string) ($record['sha256'] ?? ''), (string) hash_file('sha256', $path))) {
            throw new DomainException('Bản sao lưu không hợp lệ hoặc sai SHA-256.');
        }
        return $path;
    }

    public function delete(string $id, ?string $actor): void
    {
        $record = $this->get($id);
        if (in_array($record['status'], ['PENDING','RUNNING'], true)) {
            throw new DomainException('Bản sao lưu đang được xử lý.');
        }
        $valid = false;
        try { $this->verify($record); $valid = true; }
        catch (DomainException) {}
        if ($valid) {
            foreach ($this->all() as $other) {
                if ($other['id'] === $id || $other['status'] !== 'SUCCESS') continue;
                try { $this->verify($other); $valid = false; break; }
                catch (DomainException) {}
            }
        }
        if ($valid) throw new DomainException('Cần giữ bản sao lưu hợp lệ cuối cùng.');
        // Hide the record first. Reconciliation handles a file left by a stopped process.
        $record['deleted_at'] = time();
        $this->save($record);
        $this->removeFiles($id);
        $this->audit('deleted', $id, $actor);
    }

    public function removeFiles(string $id): void
    {
        foreach (['.sql','.part'] as $suffix) {
            $path = $this->path($id,$suffix);
            if ((is_file($path) || is_link($path)) && !@unlink($path)) {
                throw new DomainException('Chưa xóa được file sao lưu.');
            }
        }
    }

    private function read(string $path): array
    {
        if (!is_file($path)) return [];
        $data = json_decode((string) @file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    private function write(string $path, array $data): void
    {
        $temp = $path.'.'.Str::random(10).'.part';
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if (@file_put_contents($temp, $json, LOCK_EX) !== strlen($json) || !@rename($temp, $path)) {
                throw new DomainException('Không công bố được manifest sao lưu.');
            }
        } finally {
            if (is_file($temp)) @unlink($temp);
        }
    }
}

