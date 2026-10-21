<?php

namespace Tests\Unit;

use App\Models\ChiTietDuyetDeTai;
use App\Models\DeTai;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class DeCuongStatusTest extends TestCase
{
    private string $directory;
    private mixed $previousStorage;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/outline-status-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        file_put_contents($this->directory . '/outline.pdf', '%PDF-1.4 test');
        $adapter = new LocalFilesystemAdapter($this->directory);
        $disk = new FilesystemAdapter(new Filesystem($adapter), $adapter);
        $this->previousStorage = Storage::getFacadeRoot();
        Storage::swap(new class($disk) {
            public function __construct(private FilesystemAdapter $disk) {}
            public function disk(string $name): FilesystemAdapter
            {
                if ($name !== 'public') throw new \LogicException('Unexpected disk');
                return $this->disk;
            }
        });
    }

    protected function tearDown(): void
    {
        if ($this->previousStorage) Storage::swap($this->previousStorage);
        else Storage::clearResolvedInstance('filesystem');
        unlink($this->directory . '/outline.pdf');
        rmdir($this->directory);
    }

    private function topic(?string $file = null, string $status = 'Đã công bố', array $history = []): DeTai
    {
        $topic = new DeTai(['FileDeCuong' => $file, 'TrangThai' => $status]);
        $topic->setRelation('chiTietDuyets', new Collection($history));
        return $topic;
    }

    private function approval(string $action = 'Phê duyệt đề cương & Công bố đề tài', ?string $reason = null): ChiTietDuyetDeTai
    {
        return new ChiTietDuyetDeTai(['TrangThai' => 'Đã công bố', 'HanhDong' => $action, 'LyDo' => $reason]);
    }

    public function test_published_proposal_without_outline_is_neither_submitted_nor_locked(): void
    {
        $topic = $this->topic();
        $this->assertFalse($topic->coTepDeCuong());
        $this->assertFalse($topic->daDuyetDeCuong());
        $this->assertFalse($topic->khoaNopDeCuong());
    }

    public function test_current_file_does_not_mean_outline_has_been_approved(): void
    {
        $topic = $this->topic('storage/outline.pdf');
        $this->assertTrue($topic->coTepDeCuong());
        $this->assertFalse($topic->daDuyetDeCuong());
        $this->assertFalse($topic->khoaNopDeCuong());
    }

    public function test_proposal_approval_history_does_not_approve_outline(): void
    {
        $topic = $this->topic('outline.pdf', history: [$this->approval('Trưởng khoa phê duyệt')]);
        $this->assertFalse($topic->daDuyetDeCuong());
        $this->assertFalse($topic->khoaNopDeCuong());
    }

    public function test_outline_approval_requires_current_file_and_explicit_history(): void
    {
        $topic = $this->topic('storage/outline.pdf', history: [$this->approval()]);
        $this->assertTrue($topic->daDuyetDeCuong());
        $this->assertTrue($topic->khoaNopDeCuong());
    }

    public function test_legacy_outline_approval_is_supported_without_confusing_publication(): void
    {
        $topic = $this->topic('outline.pdf', history: [$this->approval('Phê duyệt & Công bố')]);
        $this->assertFalse($topic->daDuyetDeCuong());
        $topic->setRelation('chiTietDuyets', new Collection([
            $this->approval('Phê duyệt & Công bố', 'Trưởng bộ môn đã duyệt đề cương và công bố đề tài.'),
        ]));
        $this->assertTrue($topic->daDuyetDeCuong());
    }

    public function test_missing_file_cannot_be_submitted_or_approved_despite_stale_status(): void
    {
        $topic = $this->topic('storage/missing.pdf', history: [$this->approval()]);
        $this->assertFalse($topic->coTepDeCuong());
        $this->assertFalse($topic->daDuyetDeCuong());
        $this->assertFalse($topic->khoaNopDeCuong());
    }

    public function test_draft_and_unrelated_document_history_do_not_count_as_current_outline(): void
    {
        $topic = $this->topic();
        $topic->DuLieuNhap = json_encode(['FileDeCuong' => 'storage/outline.pdf']);
        $topic->setRelation('taiLieuNops', new Collection([
            new \App\Models\TaiLieuNop(['TenTaiLieu' => 'đề cương tham khảo.pdf', 'DuongDan' => 'storage/outline.pdf']),
        ]));
        $this->assertFalse($topic->coTepDeCuong());
        $this->assertFalse($topic->daDuyetDeCuong());
    }

    public function test_review_passed_or_reupload_does_not_reuse_old_approval(): void
    {
        foreach (['Đã phản biện - Chờ TBM duyệt đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương'] as $status) {
            $topic = $this->topic('outline.pdf', $status, [$this->approval()]);
            $this->assertFalse($topic->daDuyetDeCuong());
            $this->assertFalse($topic->khoaNopDeCuong());
        }
    }

    public function test_completed_topic_stays_locked_without_claiming_missing_outline_was_approved(): void
    {
        $topic = $this->topic(null, 'Hoàn thành', [$this->approval()]);
        $this->assertTrue($topic->khoaNopDeCuong());
        $this->assertFalse($topic->daDuyetDeCuong());
    }

    public function test_storage_prefix_is_normalized_and_unsafe_paths_are_rejected(): void
    {
        $this->assertTrue($this->topic('/storage/outline.pdf')->coTepDeCuong());
        foreach (['', 'https://example.com/outline.pdf', '../outline.pdf', 'storage/../outline.pdf'] as $path) {
            $this->assertFalse($this->topic($path)->coTepDeCuong());
        }
    }

    public function test_department_approved_filter_requires_outline_and_outline_approval_history(): void
    {
        $previousResolver = Model::getConnectionResolver();
        $connection = new MySqlConnection(fn () => throw new \LogicException('Database access is forbidden in this test'));
        $resolver = new ConnectionResolver(['testing' => $connection]);
        $resolver->setDefaultConnection('testing');
        Model::setConnectionResolver($resolver);
        try {
            $query = DeTai::query()->daDuyetDeCuong();
            $sql = $query->toSql();
            $this->assertStringContainsString('`FileDeCuong` is not null', $sql);
            $this->assertStringContainsString('exists', $sql);
            $this->assertContains('Phê duyệt đề cương & Công bố đề tài', $query->getBindings());
            $this->assertContains('Trưởng bộ môn đã duyệt đề cương và công bố đề tài.', $query->getBindings());
        } finally {
            if ($previousResolver) Model::setConnectionResolver($previousResolver);
            else Model::unsetConnectionResolver();
        }
    }
}
