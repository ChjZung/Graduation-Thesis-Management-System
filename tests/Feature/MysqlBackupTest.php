<?php

namespace Tests\Feature;

use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Services\Backup\BackupStore;
use App\Services\Backup\MysqlBackup;
use App\Services\Backup\MysqlBackupClient;
use DomainException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\TestCase;

class MysqlBackupTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir().'/mysql-backup-'.Str::random(12);
        config(['mysql_backup.root'=>$this->root, 'mysql_backup.enabled'=>true]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    public function test_only_explicitly_listed_operators_get_backup_permission(): void
    {
        $staff = new TaiKhoan(['MaTK'=>'staff']);
        $staff->setRelation('vaiTro', new VaiTro(['TenVaiTro'=>'Giáo vụ']));
        $this->assertTrue($staff->isAdmin());
        $this->assertFalse(Gate::forUser($staff)->allows('manage-mysql-backups'));
        $this->actingAs($staff)->post(route('admin.mysql-backup.store'))->assertForbidden();
        $this->get(route('admin.mysql-backup.download', (string) Str::uuid()))->assertForbidden();
        $this->delete(route('admin.mysql-backup.destroy', (string) Str::uuid()))->assertForbidden();

        config(['mysql_backup.operator_ids'=>['staff']]);
        $this->assertTrue(Gate::forUser($staff)->allows('manage-mysql-backups'));
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        \Illuminate\Support\Facades\Schema::create('ThongBao', function ($table) {
            foreach (['MaThongBao','TrangThai','LoaiThongBao','DoiTuongNhan','created_at'] as $column) {
                $table->string($column)->nullable();
            }
        });
        $this->withoutVite();
        $this->get(route('admin.mysql-backup.index'))->assertOk()->assertSee('Sao lưu và phục hồi dữ liệu');
        $this->post(route('admin.mysql-backup.store'))->assertRedirect();
        $record = app(BackupStore::class)->all()[0];
        $this->assertSame('PENDING', $record['status']);
        $this->assertSame('staff', $record['actor']);
    }

    public function test_tick_marks_abandoned_work_and_keeps_scheduler_heartbeat_in_real_time(): void
    {
        $store = app(BackupStore::class);
        $record = $store->create('manual','operator');
        $record['status'] = 'RUNNING';
        $store->save($record);
        file_put_contents($store->path($record['id'], '.part'), 'partial bytes');

        \Illuminate\Support\Carbon::setTestNow('2035-01-01');
        try { app(MysqlBackup::class)->tick(); }
        finally { \Illuminate\Support\Carbon::setTestNow(); }
        $result = $store->get($record['id']);
        $this->assertSame('FAILED', $result['status']);
        $this->assertSame('NOT_VERIFIED', $result['verification']);
        $this->assertFileDoesNotExist($store->path($record['id'], '.part'));
        $this->assertLessThanOrEqual(5, abs(time()-$store->schedule()['last_tick_at']));
    }

    public function test_during_an_operation_a_second_lease_is_rejected(): void
    {
        $store = app(BackupStore::class);
        $lock = $store->lock();
        try {
            $this->expectException(DomainException::class);
            $store->lock();
        } finally {
            fclose($lock);
        }
    }

    public function test_bad_checksum_and_the_last_good_backup_are_protected(): void
    {
        $store = app(BackupStore::class);
        $record = $this->makeArtifact('good', time()-40*86400);
        app(MysqlBackup::class)->tick();
        $this->assertFileExists($store->verify($record));
        try {
            $store->exclusive(fn () => $store->delete($record['id'], 'operator'));
            $this->fail('Deleted the last valid backup.');
        } catch (DomainException $error) {
            $this->assertStringContainsString('cuối cùng', $error->getMessage());
        }
        file_put_contents($store->path($record['id']), 'bad!');
        $this->expectExceptionMessage('SHA-256');
        $store->verify($store->get($record['id']));
    }

    public function test_operator_downloads_checked_sql_and_cleanup_removes_expired_backup(): void
    {
        $operator = new TaiKhoan(['MaTK'=>'operator']);
        $operator->setRelation('vaiTro', new VaiTro(['TenVaiTro'=>'Admin']));
        config(['mysql_backup.operator_ids'=>['operator'], 'mysql_backup.automatic'=>false]);
        $old = $this->makeArtifact('older',time()-40*86400);
        $current = $this->makeArtifact('current',time());
        $store = app(BackupStore::class);
        $response = $this->actingAs($operator)->get(route('admin.mysql-backup.download',$current['id']))
            ->assertOk()->assertDownload($current['id'].'.sql');
        $this->assertSame('current',$response->streamedContent());
        app(MysqlBackup::class)->tick();
        $this->assertFileDoesNotExist($store->path($old['id']));
        $this->assertArrayHasKey('deleted_at', $store->get($old['id']));
        $this->assertFileExists($store->path($current['id']));
    }

    public function test_interrupted_process_is_failed_and_never_publishes_a_dump(): void
    {
        $mock = \Mockery::mock(MysqlBackupClient::class);
        $mock->shouldReceive('settings')->andReturn(['schema'=>'fixture']);
        $mock->shouldReceive('connect')->andReturn(\Mockery::mock(\PDO::class));
        $mock->shouldReceive('server')->andReturn(['db'=>'fixture','version'=>'8.0.30','port'=>3306]);
        $mock->shouldReceive('inventory')->andReturn(['fixture'=>'ddl']);
        $mock->shouldReceive('dump')->andReturnUsing(function ($source, $path) {
            file_put_contents($path, 'interrupted output');
            throw new DomainException('Tiến trình MySQL bị gián đoạn.');
        });
        $this->app->instance(MysqlBackupClient::class, $mock);
        $row = app(MysqlBackup::class)->run();
        $this->assertSame('FAILED',$row['status']);
        $this->assertSame('NOT_VERIFIED',$row['verification']);
        $store = app(BackupStore::class);
        $this->assertFileDoesNotExist($store->path($row['id']));
        $this->assertFileDoesNotExist($store->path($row['id'], '.part'));
    }

    public function test_restore_rejects_the_source_before_opening_its_connection(): void
    {
        $record = $this->makeArtifact('demo', time(), 'sourcedemo');
        config(['mysql_backup.restore.schema'=>'sourcedemo','mysql_backup.restore.user'=>'dedicated']);
        $this->expectExceptionMessage('trùng database nguồn');
        app(MysqlBackup::class)->restore($record['id'], 'mysql://127.0.0.1:3306/sourcedemo');
    }

    public function test_actual_mysql_dump_restore_and_data_comparison(): void
    {
        $sourceName = getenv('BACKUP_FIXTURE_SOURCE');
        $targetName = getenv('BACKUP_FIXTURE_TARGET');
        if (!$sourceName || !$targetName) $this->markTestSkipped('Cần hai schema fixture MySQL độc lập.');
        $this->assertMatchesRegularExpression('/^huitbackupsrc[a-f0-9]+$/', $sourceName);
        $this->assertMatchesRegularExpression('/^huitbackupdst[a-f0-9]+$/', $targetName);
        $source = ['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'schema'=>$sourceName,
            'user'=>getenv('BACKUP_FIXTURE_READER'),'password'=>getenv('BACKUP_FIXTURE_READER_PASSWORD')];
        $target = ['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'schema'=>$targetName,
            'user'=>getenv('BACKUP_FIXTURE_RESTORER'),'password'=>getenv('BACKUP_FIXTURE_RESTORER_PASSWORD')];
        config(['mysql_backup.source'=>$source, 'mysql_backup.restore'=>$target, 'mysql_backup.automatic'=>true]);
        $client = app(MysqlBackupClient::class);
        $sourceDb = $client->connect($source);
        $select = static fn ($db) => $db->query('SELECT t.id,t.title,r.body,r.evidence FROM topics t JOIN reports r ON r.topic_id=t.id ORDER BY r.id')->fetchAll(\PDO::FETCH_ASSOC);
        $before = $select($sourceDb);
        $this->assertCount(2,$before);
        $backup = app(MysqlBackup::class);
        $store = app(BackupStore::class);
        $backup->tick();
        $records = $store->all();
        $this->assertCount(1, $records);
        $record = $records[0];
        $this->assertSame('automatic', $record['type']);
        $this->assertSame('SUCCESS', $record['status'], $record['error'] ?? '');
        $this->assertSame(['reports'=>2,'topics'=>2], $record['rows']);
        $this->assertSame('NOT_VERIFIED',$record['verification']);
        $backup->tick();
        $this->assertCount(1, $store->all(), 'Không tạo trùng bản tự động trong cùng lịch.');

        $path = $store->verify($record);
        $sql = file_get_contents($path);
        file_put_contents($path, substr_replace($sql, '!', -2, 1));
        $this->artisan('backup:mysql', ['action'=>'restore','id'=>$record['id'],'--confirm'=>"mysql://127.0.0.1:3306/$targetName"])
            ->expectsOutputToContain('SHA-256')->assertFailed();
        file_put_contents($path, $sql);

        // Recovery cannot use the app connection or reach the backup source.
        config(['database.default'=>'mysql','database.connections.mysql.host'=>'invalid.invalid',
            'database.connections.mysql.url'=>null,'mysql_backup.source.host'=>'invalid.invalid']);
        $this->artisan('backup:mysql', ['action'=>'list'])->expectsOutputToContain($record['id'])->assertSuccessful();
        $exit = \Illuminate\Support\Facades\Artisan::call('backup:mysql',
            ['action'=>'restore','id'=>$record['id'],'--confirm'=>"mysql://127.0.0.1:3306/$targetName"]);
        $this->assertSame(0, $exit, \Illuminate\Support\Facades\Artisan::output());
        $restored = $store->get($record['id']);
        $this->assertSame('VERIFIED', $restored['verification']);
        $restoredDb = $client->connect($target);
        $this->assertSame($before, $select($restoredDb));
        $this->assertSame($client->inventory($sourceDb),$client->inventory($restoredDb));
        $this->artisan('backup:mysql', ['action'=>'restore','id'=>$record['id'],'--confirm'=>"mysql://127.0.0.1:3306/$targetName"])
            ->expectsOutputToContain('Schema đích phải trống')->assertFailed();
        fwrite(STDOUT, sprintf("\nMySQL fixture: dump %.3fs, restore + verification %.3fs.\n", $record['seconds'], $restored['restore_seconds']));
    }

    private function makeArtifact(string $data, int $time, string $source = 'src'): array
    {
        $store = app(BackupStore::class);
        $record = $store->create('manual','operator');
        file_put_contents($store->path($record['id']), $data);
        $record['created_at'] = $time;
        $record['status'] = 'SUCCESS';
        $record['bytes'] = strlen($data);
        $record['sha256'] = hash('sha256',$data);
        $record['source'] = ['schema'=>$source];
        $store->save($record);
        return $record;
    }
}

