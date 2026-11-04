<?php
namespace Tests\Feature;

use App\Models\{TaiKhoan, ThongBao};
use App\Services\{NotificationAttachmentService, ThongBaoService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationAttachmentTest extends TestCase
{
    use DatabaseTransactions;
    private TaiKhoan $recipient;
    private string $tag;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->recipient = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'Trưởng khoa'))->firstOrFail();
        $this->recipient->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($this->recipient);
        $this->tag = 'ATTACH_TEST_'.bin2hex(random_bytes(4));
    }

    private function notice(?string $path, string $suffix): ThongBao
    {
        ThongBaoService::guiDen($this->recipient->MaTK, $this->tag.' '.$suffix, 'Nội dung thử nghiệm', 'Hội đồng', null, $path);
        return ThongBao::where('TieuDe', $this->tag.' '.$suffix)->firstOrFail();
    }

    public function test_pdf_is_visible_and_downloads_exact_bytes_without_public_symlink(): void
    {
        $bytes = '%PDF-1.4 Attachment test';
        Storage::disk('public')->put('quyet_dinh_hoi_dong/Danh sách hội đồng.pdf', $bytes);
        $notice = $this->notice('storage/quyet_dinh_hoi_dong/Danh sách hội đồng.pdf', 'pdf');
        $attachment = NotificationAttachmentService::resolve($notice);
        $this->assertFalse($attachment['missing']);
        $this->get(route('thongbao.show', $notice->MaThongBao))->assertOk()
            ->assertViewHas('fileUrl', $attachment['url'])->assertViewHas('fileType', 'pdf')->assertSee('Tải PDF');
        $response = $this->get($attachment['url'].'?download=1');
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertSame($bytes, $response->streamedContent());
    }

    public function test_no_attachment_does_not_create_a_fake_file_panel(): void
    {
        $notice = $this->notice(null, 'none');
        $this->assertNull(NotificationAttachmentService::resolve($notice));
        $this->get(route('thongbao.show', $notice->MaThongBao))->assertOk()->assertViewHas('fileUrl', null)
            ->assertDontSee('id="docViewerIframe"', false)->assertDontSee('Tệp đính kèm đã được ghi nhận');
    }

    public function test_missing_attachment_is_reported_without_fake_download(): void
    {
        $notice = $this->notice('storage/quyet_dinh_hoi_dong/missing.pdf', 'missing');
        $this->get(route('thongbao.show', $notice->MaThongBao))->assertOk()->assertViewHas('fileUrl', null)
            ->assertSee('Tệp đính kèm đã được ghi nhận nhưng hiện không còn trên máy chủ');
        $this->get(route('files.thongbao.attachment', ['id' => $notice->MaThongBao, 'filename' => 'missing.pdf']))->assertNotFound();
    }

    public function test_attachment_filter_respects_recipient_and_metadata(): void
    {
        Storage::disk('public')->put('notices/test.pdf', '%PDF-test');
        $with = $this->notice('notices/test.pdf', 'with');
        $none = $this->notice(null, 'without');
        $other = TaiKhoan::where('MaTK', '!=', $this->recipient->MaTK)->firstOrFail();
        ThongBaoService::guiDen($other->MaTK, $this->tag.' private', 'Riêng tư', 'Hội đồng', null, 'notices/test.pdf');
        foreach (['with' => $with, 'without' => $none] as $filter => $expected) {
            $this->get(route('truongkhoa.thongbao.index', ['search' => $this->tag, 'attachment' => $filter, 'LoaiThongBao' => 'Hội đồng']))
                ->assertOk()->assertViewHas('thongbaos', fn ($rows) => $rows->count() === 1 && $rows->first()->MaThongBao === $expected->MaThongBao);
        }
    }

    public function test_private_file_rejects_outsiders_and_guest_and_ignores_request_paths(): void
    {
        Storage::disk('public')->put('notices/test.pdf', '%PDF-test');
        $notice = $this->notice('notices/test.pdf', 'private');
        $url = NotificationAttachmentService::resolve($notice)['url'];
        $this->assertSame('%PDF-test', $this->get($url.'?path=../../.env')->streamedContent());
        $other = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))->firstOrFail();
        $other->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($other)->get($url)->assertForbidden();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_storage_traversal_cannot_escape_the_configured_disk(): void
    {
        $notice = $this->notice('../../.env', 'traversal');
        $this->assertTrue(NotificationAttachmentService::resolve($notice)['missing']);
    }

    public function test_lecturer_and_student_inboxes_show_their_own_attachment_filters(): void
    {
        Storage::disk('public')->put('notices/received.pdf', '%PDF-received');
        foreach (['Giảng viên' => 'giangvien', 'Sinh viên' => 'sinhvien'] as $role => $prefix) {
            $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', $role))
                ->whereHas($prefix === 'sinhvien' ? 'sinhVien' : 'giangVien')->firstOrFail();
            $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            $this->recipient = $user;
            $with = $this->notice('notices/received.pdf', $prefix.' file');
            $without = $this->notice(null, $prefix.' no-file');
            $this->actingAs($user);
            foreach (['with' => $with, 'without' => $without] as $filter => $expected) {
                $this->get(route($prefix.'.thongbao.index', ['search' => $this->tag, 'attachment' => $filter]))
                    ->assertOk()->assertViewHas('thongbaos', fn ($rows) => $rows->count() === 1 && $rows->first()->MaThongBao === $expected->MaThongBao);
            }
            $attachment = NotificationAttachmentService::resolve($with);
            $this->get(route($prefix.'.thongbao.show', $with->MaThongBao))->assertOk()->assertViewHas('fileUrl', $attachment['url']);
        }
    }

    public function test_legacy_link_repair_is_explicit_and_does_not_send_or_duplicate_notices(): void
    {
        $council = \App\Models\HoiDong::firstOrFail()->replicate();
        $council->forceFill(['MaHoiDong' => 'HD_ATTACH_TEST', 'TenHoiDong' => $this->tag.' legacy council', 'TrangThaiCongBo' => 'Đã công bố', 'FileQuyetDinh' => 'storage/notices/legacy.pdf']);
        $council->save();
        Storage::disk('public')->put('notices/legacy.pdf', '%PDF-legacy');
        $title = 'Thông báo công bố Hội đồng bảo vệ khóa luận: '.$council->TenHoiDong;
        ThongBaoService::guiDen($this->recipient->MaTK, $title, 'Thông báo cũ', 'Hội đồng');
        $notice = ThongBao::where('TieuDe', $title)->firstOrFail();
        $count = ThongBao::count();
        $this->artisan('notifications:audit-attachments', ['--repair' => true])->assertFailed();
        $this->assertNull($notice->fresh()->FileDinhKem);
        $this->artisan('notifications:audit-attachments', ['--repair' => true, '--council' => [$council->MaHoiDong]])->assertSuccessful();
        $this->assertSame($council->FileQuyetDinh, $notice->fresh()->FileDinhKem);
        $this->artisan('notifications:audit-attachments', ['--repair' => true, '--council' => [$council->MaHoiDong]])->assertSuccessful();
        $this->assertSame($count, ThongBao::count());
        $this->assertSame('Đã công bố', $council->fresh()->TrangThaiCongBo);
    }
}
