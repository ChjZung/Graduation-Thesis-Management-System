<?php

namespace App\Services\Backup;

use DomainException;
use PDO;
use Symfony\Component\Process\Process;
use Throwable;

class MysqlBackupClient
{
    public function settings(string $role): array
    {
        $settings = config('mysql_backup.'.$role);
        if (($settings['driver'] ?? '') !== 'mysql'
            || !preg_match('/\A[A-Za-z0-9.-]+\z/', (string) ($settings['host'] ?? ''))
            || strtolower($settings['host']) === 'localhost'
            || !filter_var($settings['port'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>65535]])
            || !$this->identifier((string) ($settings['schema'] ?? ''))
            || empty($settings['user'])) {
            throw new DomainException('Thiếu cấu hình kết nối MySQL rõ ràng cho '.$role.'.');
        }
        return $settings;
    }

    public function connect(array $settings): PDO
    {
        try {
            return new PDO('mysql:host='.$settings['host'].';port='.$settings['port'].';dbname='.$settings['schema'].';charset=utf8mb4',
                $settings['user'], (string) $settings['password'], [
                    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT=>5,
                    PDO::MYSQL_ATTR_LOCAL_INFILE=>false,
                ]);
        } catch (Throwable) {
            throw new DomainException('Không kết nối được MySQL với thông tin đã cấu hình.');
        }
    }

    public function server(PDO $db): array
    {
        $row = $db->query('SELECT DATABASE() AS db, VERSION() AS version, @@port AS port')->fetch(PDO::FETCH_ASSOC);
        if (!preg_match('/^8\.(0|4)\.\d+/', $row['version']) || stripos($row['version'], 'MariaDB') !== false) {
            throw new DomainException('V1 hỗ trợ MySQL Server 8.0 hoặc 8.4 đã tương thích.');
        }
        return $row;
    }

    public function assertLimitedRestoreAccount(PDO $db, array $target): void
    {
        $current = $db->query('SELECT CURRENT_USER()')->fetchColumn();
        if (strtolower($target['user']) === 'root' || !str_starts_with($current, $target['user'].'@')) {
            throw new DomainException('Cần tài khoản restore riêng.');
        }
        $scope = preg_quote(chr(96).str_replace('_', '\\_', $target['schema']).chr(96).'.*', '/');
        $hasSchemaGrant = false;
        foreach ($db->query('SHOW GRANTS')->fetchAll(PDO::FETCH_COLUMN) as $grant) {
            if (stripos($grant, 'WITH GRANT OPTION') !== false) {
                throw new DomainException('Tài khoản restore có quyền cấp quyền.');
            }
            if (preg_match('/^GRANT USAGE ON \*\.\* TO /i', $grant)) continue;
            if (!preg_match('/^GRANT [A-Z ,]+ ON '.$scope.' TO /', $grant)) {
                throw new DomainException('Tài khoản restore có quyền ngoài schema đích hoặc quyền qua role.');
            }
            $hasSchemaGrant = true;
        }
        if (!$hasSchemaGrant) throw new DomainException('Tài khoản chưa có quyền trên schema đích.');
    }

    public function inventory(PDO $db): array
    {
        $objects = $db->query("SELECT TABLE_NAME,TABLE_TYPE,ENGINE FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
        foreach (['TRIGGERS'=>'TRIGGER_SCHEMA','ROUTINES'=>'ROUTINE_SCHEMA','EVENTS'=>'EVENT_SCHEMA'] as $table=>$column) {
            if ((int) $db->query("SELECT COUNT(*) FROM information_schema.$table WHERE $column=DATABASE()")->fetchColumn()) {
                throw new DomainException('Schema có trigger, routine hoặc event ngoài phạm vi V1.');
            }
        }
        $tables = [];
        foreach ($objects as $object) {
            if ($object['TABLE_TYPE'] !== 'BASE TABLE' || $object['ENGINE'] !== 'InnoDB') {
                throw new DomainException('V1 chỉ sao lưu schema gồm các bảng InnoDB.');
            }
            $name = $object['TABLE_NAME'];
            $ddl = $db->query('SHOW CREATE TABLE '.$this->quote($name))->fetch(PDO::FETCH_NUM)[1];
            // A collation identifies its charset. A dump can make the same implicit charset explicit.
            // Leave quoted defaults, comments and identifiers untouched.
            $quoted = <<<'REGEX'
'(?:\\\\.|''|[^'\\\\])*'|\x60(?:\x60\x60|[^\x60])*\x60
REGEX;
            $ddl = preg_replace("~(?:$quoted)(*SKIP)(*F)| CHARACTER SET [A-Za-z0-9_]+(?= COLLATE )| AUTO_INCREMENT=\d+(?= |$)~", '', $ddl);
            $tables[$name] = hash('sha256', $ddl);
        }
        if ((int) $db->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_SCHEMA IS NOT NULL
            AND REFERENCED_TABLE_SCHEMA<>DATABASE()")->fetchColumn()) {
            throw new DomainException('Phát hiện khóa ngoại tham chiếu sang schema khác.');
        }
        return $tables;
    }

    public function verifyData(PDO $db, array $rows): void
    {
        foreach ($rows as $table=>$expected) {
            if ((int) $db->query('SELECT COUNT(*) FROM '.$this->quote($table))->fetchColumn() !== $expected) {
                throw new DomainException('Số bản ghi sau restore khác manifest.');
            }
        }
        $keys = $db->query("SELECT TABLE_NAME,CONSTRAINT_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY TABLE_NAME,CONSTRAINT_NAME,ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
        $groups = [];
        foreach ($keys as $key) $groups[$key['TABLE_NAME']][$key['CONSTRAINT_NAME']][] = $key;
        foreach ($groups as $table=>$constraints) {
            foreach ($constraints as $columns) {
                $match = $present = [];
                foreach ($columns as $column) {
                    $child = 'c.'.$this->quote($column['COLUMN_NAME']);
                    $present[] = "$child IS NOT NULL";
                    $match[] = 'p.'.$this->quote($column['REFERENCED_COLUMN_NAME'])."=$child";
                }
                $parent = $this->quote($columns[0]['REFERENCED_TABLE_NAME']);
                $orphans = $db->query('SELECT 1 FROM '.$this->quote($table).' c WHERE '.implode(' AND ', $present)
                    .' AND NOT EXISTS (SELECT 1 FROM '.$parent.' p WHERE '.implode(' AND ', $match).') LIMIT 1');
                if ($orphans->fetchColumn()) throw new DomainException('Phát hiện dữ liệu mồ côi sau restore.');
            }
        }
    }

    public function dump(array $source, string $part, string $root): string
    {
        $binary = config('mysql_backup.dump_client');
        $version = $this->clientVersion($binary);
        $this->run($source, $binary, [
            '--single-transaction', '--skip-lock-tables', '--skip-add-locks', '--quick', '--no-tablespaces',
            '--set-gtid-purged=OFF', '--skip-column-statistics', '--skip-add-drop-table',
            '--skip-triggers', '--skip-events', '--skip-routines', '--skip-extended-insert',
            '--hex-blob', '--result-file='.$part, $source['schema'],
        ], $root);
        return $version;
    }

    public function import(array $target, string $path, string $root): void
    {
        $binary = config('mysql_backup.mysql_client');
        $this->clientVersion($binary);
        $input = @fopen($path, 'rb');
        if (!$input) throw new DomainException('Không đọc được file SQL để phục hồi.');
        try {
            $this->run($target, $binary, ['--binary-mode', '--local-infile=0', '--connect-timeout=5', '--database='.$target['schema']], $root, $input);
        } finally {
            fclose($input);
        }
    }

    public function inspectRows(string $path, array $tables): array
    {
        $rows = array_fill_keys(array_keys($tables), 0);
        $seen = [];
        $handle = @fopen($path, 'rb');
        if (!$handle) throw new DomainException('Không đọc được file SQL.');
        try {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^CREATE TABLE \x60([A-Za-z0-9_]+)\x60 \(/', $line, $match)) {
                    $seen[$match[1]] = true;
                }
                if (preg_match('/^INSERT INTO \x60([A-Za-z0-9_]+)\x60 VALUES \(/', $line, $match)) {
                    if (!array_key_exists($match[1], $rows)) throw new DomainException('Bản dump chứa bảng ngoài phạm vi.');
                    $rows[$match[1]]++;
                }
            }
            if (!feof($handle) || array_diff_key($tables, $seen) || array_diff_key($seen, $tables)) {
                throw new DomainException('Không xác nhận được danh sách bảng trong bản dump.');
            }
        } finally {
            fclose($handle);
        }
        return $rows;
    }

    private function identifier(string $value): bool
    {
        return preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $value) === 1;
    }

    private function quote(string $identifier): string
    {
        if (!$this->identifier($identifier)) throw new DomainException('Tên đối tượng ngoài phạm vi V1.');
        return chr(96).$identifier.chr(96);
    }

    private function clientVersion(string $binary): string
    {
        $process = new Process([$binary, '--no-defaults', '--version'], timeout: 10);
        try { $process->run(); }
        catch (Throwable) { throw new DomainException('Không chạy được MySQL client đã cấu hình.'); }
        if (!$process->isSuccessful() || !preg_match('/\b(?:Ver|Distrib) (8\.(?:0|4)\.\d+)\b/', $process->getOutput(), $match)
            || stripos($process->getOutput(), 'MariaDB') !== false) {
            throw new DomainException('Không tìm thấy MySQL client 8.0 hoặc 8.4 tương thích.');
        }
        return $match[1];
    }

    private function run(array $settings, string $binary, array $args, string $root, mixed $input = null): void
    {
        $secretFile = $root.'/client-'.bin2hex(random_bytes(12)).'.cnf.part';
        $handle = @fopen($secretFile, 'xb');
        if (!$handle) throw new DomainException('Không tạo được file cấu hình MySQL riêng.');
        try {
            if (DIRECTORY_SEPARATOR !== '\\' && !chmod($secretFile, 0600)) {
                throw new DomainException('Không giới hạn được quyền đọc file cấu hình MySQL.');
            }
            $password = (string) $settings['password'];
            if (strpbrk($password, "\r\n\0") !== false) throw new DomainException('Mật khẩu MySQL có ký tự không hỗ trợ.');
            $content = "[client]\npassword=\"".addcslashes($password, "\\\"")."\"\n";
            if (fwrite($handle, $content) !== strlen($content)) throw new DomainException('Không ghi được cấu hình MySQL.');
            fclose($handle);
            $handle = null;
            $process = new Process([$binary, '--defaults-file='.$secretFile, '--protocol=TCP',
                '--host='.$settings['host'], '--port='.$settings['port'], '--user='.$settings['user'],
                '--default-character-set=utf8mb4', ...$args], env:[
                    'MYSQL_PWD'=>false, 'MYSQL_TEST_LOGIN_FILE'=>$secretFile.'.unused',
                ], input:$input, timeout:max(1, (int) config('mysql_backup.timeout')));
            $process->disableOutput();
            try { $process->run(); }
            catch (Throwable) { throw new DomainException('Tiến trình MySQL bị gián đoạn hoặc quá thời gian.'); }
            if (!$process->isSuccessful()) {
                throw new DomainException('MySQL client kết thúc với mã lỗi '.$process->getExitCode().'.');
            }
        } finally {
            if (is_resource($handle)) fclose($handle);
            @unlink($secretFile);
        }
    }
}

