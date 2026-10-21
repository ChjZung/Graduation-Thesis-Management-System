<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TaiKhoan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Local database fixtures are rolled back; no AI requests are made. */
class ReportFileDownloadTest extends TestCase
{
    use DatabaseTransactions;

    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) unlink($file);
        }
        parent::tearDown();
    }

    private function report(string $extension = 'pdf'): array
    {
        $source = KhoiBaoCaoTienDo::with(['deTai', 'baoCao.deTai'])->whereNotNull('DuongDanFile')->first();
        if (!$source) $this->markTestSkipped('A local report is required.');
        $topic = $source->deTai ?? $source->baoCao?->deTai;
        $lecturer = GiangVien::where('MaGV', $topic?->MaGV)->first();
        $user = $lecturer ? TaiKhoan::where('MaGV', $lecturer->MaGV)->orWhere('MaTK', $lecturer->MaTK)->first() : null;
        if (!$user) $this->markTestSkipped('The report owner account is required.');
        $copy = $source->replicate(['id']);
        $copy->MaKhoi = 'TEST_FILE_' . bin2hex(random_bytes(5));
        $copy->LanNop = 997;
        $copy->DuongDanFile = 'baocao/' . $copy->MaKhoi . '.' . $extension;
        $copy->TenFile = 'Báo cáo thử nghiệm.' . $extension;
        $copy->save();
        $path = storage_path('app/public/' . $copy->DuongDanFile);
        $this->files[] = $path;
        file_put_contents($path, $extension === 'pdf' ? '%PDF-1.4 test file' : 'DOCX download fixture');
        return [$copy, $user, $path];
    }

    public function test_pdf_download_is_an_attachment_and_preview_stays_inline(): void
    {
        [$report, $user, $path] = $this->report();
        if (!is_readable($path)) $this->markTestSkipped('Windows sandbox cannot query file readability; BinaryFileResponse requires this check.');
        $this->actingAs($user)->get(route('baocao.ai.pdf', $report->MaKhoi))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename=' . basename($path));
        $response = $this->get(route('baocao.ai.download', $report->MaKhoi));
        $response->assertOk()->assertDownload($report->TenFile)->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(realpath($path), $response->baseResponse->getFile()->getRealPath());
    }

    public function test_docx_download_keeps_original_name_and_format(): void
    {
        [$report, $user, $path] = $this->report('docx');
        if (!is_readable($path)) $this->markTestSkipped('Windows sandbox cannot query file readability; BinaryFileResponse requires this check.');
        $this->actingAs($user)->get(route('baocao.ai.download', $report->MaKhoi))
            ->assertOk()->assertDownload($report->TenFile)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_another_account_cannot_download_or_preview_the_report(): void
    {
        [$report, $owner] = $this->report();
        $outsider = TaiKhoan::where('MaTK', '<>', $owner->MaTK)->where('MaVaiTro', 'VT01')->first();
        if (!$outsider) $this->markTestSkipped('An unrelated account is required.');
        $this->actingAs($outsider)->get(route('baocao.ai.download', $report->MaKhoi))->assertForbidden();
        $this->get(route('baocao.ai.pdf', $report->MaKhoi))->assertForbidden();
    }

    public function test_guest_cannot_download_a_report(): void
    {
        $this->get(route('baocao.ai.download', 'UNKNOWN'))->assertRedirect(route('login'));
    }

    public function test_missing_file_returns_not_found(): void
    {
        [$report, $user, $path] = $this->report();
        unlink($path);
        $this->actingAs($user)->get(route('baocao.ai.download', $report->MaKhoi))->assertNotFound();
    }

    public function test_download_rejects_a_path_outside_the_public_storage_root(): void
    {
        [$report, $user] = $this->report();
        $report->update(['DuongDanFile' => '../../logs/laravel.log']);
        $this->actingAs($user)->get(route('baocao.ai.download', $report->MaKhoi))->assertNotFound();
    }
}
