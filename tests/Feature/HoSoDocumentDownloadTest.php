<?php

namespace Tests\Feature;

use App\Models\{HoSoBaoVe, GiangVien, TaiKhoan};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HoSoDocumentDownloadTest extends TestCase
{
    use DatabaseTransactions;

    private HoSoBaoVe $dossier;
    private string $path;
    private string $name = 'KIỂM TRA MÔN BIG DATA.docx';
    private string $bytes;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->dossier = HoSoBaoVe::whereHas('deTai.giangVien.taiKhoan.vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))->firstOrFail();
        $teacher = $this->dossier->deTai->giangVien;
        $user = $teacher->taiKhoan;
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
        $this->path = 'hoso/'.$this->dossier->MaNhom.'/bo_sung/test.docx';
        Storage::disk('public')->put($this->path, '');
        $zip = new \ZipArchive();
        $zip->open(Storage::disk('public')->path($this->path), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Tài liệu kiểm thử</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();
        $this->bytes = Storage::disk('public')->get($this->path);
        $this->dossier->update(['LienKetKhac' => [['loai' => 'file', 'file_path' => $this->path, 'ten_file' => $this->name]]]);
    }

    private function url(array $params = []): string
    {
        return route('files.hoso.document', array_merge(['id' => $this->dossier->MaHoSo, 'document' => 'supplement', 'index' => 0], $params));
    }

    public function test_owner_downloads_original_docx_bytes_and_unicode_name(): void
    {
        $response = $this->getJson($this->url());
        $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment;', $disposition);
        $this->assertStringContainsString("filename*=utf-8''".rawurlencode($this->name), $disposition);
        $downloaded = $response->streamedContent();
        $this->assertSame(strlen($this->bytes), strlen($downloaded));
        $this->assertSame(hash('sha256', $this->bytes), hash('sha256', $downloaded));
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('public')->path($this->path)));
        $this->assertStringContainsString('Tài liệu kiểm thử', $zip->getFromName('word/document.xml'));
        $zip->close();
    }

    public function test_missing_file_returns_clear_not_found_instead_of_a_download(): void
    {
        Storage::disk('public')->delete($this->path);
        $this->getJson($this->url())->assertNotFound()->assertJsonPath('message', 'Tệp không còn trên máy chủ. Vui lòng yêu cầu nhóm sinh viên nộp lại.');
    }

    public function test_teacher_page_uses_the_authorized_attachment_endpoint(): void
    {
        $this->get('/giangvien/xac-nhan-bao-ve/'.$this->dossier->MaHoSo)
            ->assertOk()->assertSee(e($this->url()), false)
            ->assertDontSee(asset('storage/'.$this->path), false);
    }

    public function test_unrelated_teacher_cannot_download_the_dossier(): void
    {
        $teacher = GiangVien::where('MaGV', '!=', $this->dossier->deTai->MaGV)
            ->whereHas('taiKhoan.vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))
            ->whereDoesntHave('thanhVienHoiDongs', fn ($q) => $q->where('MaHoiDong', $this->dossier->MaHoiDong))->firstOrFail();
        $this->dossier->update(['NguoiKiemTraChinhSua' => null]);
        $teacher->taiKhoan->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($teacher->taiKhoan)->get($this->url())->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();
        $this->get($this->url())->assertRedirect(route('login'));
    }

    public function test_arbitrary_query_path_cannot_override_the_stored_file(): void
    {
        $response = $this->get($this->url(['path' => '../../.env', 'file_path' => '../../.env']));
        $response->assertOk();
        $this->assertSame($this->bytes, $response->streamedContent());
        $this->get($this->url(['index' => 999]))->assertNotFound();
    }

    public function test_stored_traversal_or_another_groups_file_is_rejected(): void
    {
        foreach (['../../.env', 'hoso/OTHER_GROUP/bo_sung/test.docx', 'hoso/'.$this->dossier->MaNhom.'/../../.env'] as $path) {
            $this->dossier->update(['LienKetKhac' => [['file_path' => $path, 'ten_file' => $this->name]]]);
            $this->get($this->url())->assertNotFound();
        }
    }

    public function test_report_turnitin_and_history_pdf_use_the_same_authorization(): void
    {
        $path = 'hoso/'.$this->dossier->MaNhom.'/report.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 test');
        $this->dossier->update(['ToanVanKhoaLuan' => $path, 'MinhChungDaoVan' => $path]);
        $this->dossier->tepHoSoBaoVes()->create(['MaTep' => 'TEST_TURNITIN_FILE', 'TenTep' => 'Turnitin.pdf', 'LoaiTep' => 'Turnitin', 'DuongDanFile' => $path]);
        $version = $this->dossier->phienBans()->create(['PhienBan' => 998, 'DuongDanBaoCao' => $path, 'TenFileBaoCao' => 'Báo cáo.pdf', 'TenFileTurnitin' => 'Turnitin.pdf', 'NgayNop' => now()]);
        foreach (['report', 'turnitin', 'version'] as $type) {
            $response = $this->getJson($this->url(['document' => $type, 'version' => $version->id]));
            $this->assertSame(200, $response->getStatusCode(), $type.': '.($response->getStatusCode() === 200 ? '' : $response->json('message')));
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        }
        $response = $this->get($this->url(['document' => 'report', 'download' => 1]));
        $response->assertOk();
        $this->assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'));
        $this->get($this->url(['document' => 'version', 'version' => 2147483647]))->assertNotFound();
    }
}
