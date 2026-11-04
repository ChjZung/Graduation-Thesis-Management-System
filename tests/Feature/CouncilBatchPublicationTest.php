<?php

namespace Tests\Feature;

use App\Models\{HoiDong, HoSoBaoVe, HocKy, Khoa, GiangVien, TaiKhoan, ThanhVienHoiDong, ThongBao};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CouncilBatchPublicationTest extends TestCase
{
    use DatabaseTransactions;

    private array $councils = [];
    private array $dossiers = [];
    private ?string $previewToken = null;
    private string $semester = 'HK_BULK_TEST';
    private string $round = 'Đợt kiểm thử công bố';

    protected function setUp(): void
    {
        parent::setUp();
        $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'Trưởng khoa'))->firstOrFail();
        $user->update(['TenDangNhap' => 'TK_CNTT_BULK_TEST', 'TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
        $semester = HocKy::firstOrFail()->replicate();
        $semester->MaHocKy = $this->semester;
        $semester->TrangThai = 'Đã kết thúc';
        $semester->save();
    }

    protected function tearDown(): void
    {
        $paths = HoiDong::whereIn('MaHoiDong', $this->councils)->pluck('FileQuyetDinh')->filter()->unique();
        foreach ($paths as $stored) {
            if (str_contains($stored, 'quyet_dinh_va_danh_sach_hoi_dong_cntt_hk_bulk_test_')) {
                $path = storage_path('app/public/'.preg_replace('#^storage/#', '', $stored));
                if (is_file($path)) unlink($path);
            }
        }
        // Delete only PDFs generated for this test's unique council IDs.
        foreach ($this->councils as $id) {
            foreach (glob(storage_path('app/public/quyet_dinh_hoi_dong/QD_HoiDong_'.$id.'_*.pdf')) ?: [] as $path) {
                if (is_file($path)) unlink($path);
            }
        }
        foreach (session('council_decision_previews', []) as $preview) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($preview['path']);
        }
        parent::tearDown();
    }

    private function council(array $overrides = [], bool $complete = true): HoiDong
    {
        $council = HoiDong::firstOrFail()->replicate();
        $council->forceFill(array_merge([
            'MaHoiDong' => 'HD_BULK_'.bin2hex(random_bytes(4)),
            'MaKhoa' => 'CNTT', 'MaHocKy' => $this->semester,
            'DotBaoVe' => $this->round, 'TrangThaiCongBo' => 'Chưa công bố',
            'FileQuyetDinh' => null, 'NgayCongBo' => null,
            'SoQuyetDinh' => 'TEST/01', 'NgayBanHanh' => '2026-11-04',
            'NguoiKy' => 'Người ký kiểm thử', 'ChucVuNguoiKy' => 'HIỆU TRƯỞNG',
        ], $overrides));
        $council->TenHoiDong = 'Hội đồng kiểm thử '.$council->MaHoiDong;
        $council->save();
        $this->councils[] = $council->MaHoiDong;
        if ($complete) {
            $teachers = GiangVien::limit(3)->get();
            foreach (['Chủ tịch', 'Thư ký', 'Phản biện'] as $i => $role) {
                ThanhVienHoiDong::create(['MaHoiDong' => $council->MaHoiDong, 'MaGV' => $teachers[$i]->MaGV, 'VaiTro' => $role]);
            }
            $dossier = HoSoBaoVe::whereNotIn('MaHoSo', $this->dossiers)->firstOrFail();
            $dossier->update(['MaHoiDong' => $council->MaHoiDong]);
            $this->dossiers[] = $dossier->MaHoSo;
        }
        return $council;
    }

    private function preview(array $params = [])
    {
        return $this->get(route('truongkhoa.hoidong.previewDecision', array_merge(['MaHocKy' => $this->semester, 'DotBaoVe' => $this->round], $params)));
    }

    private function publish(array $params = [])
    {
        if (!$this->previewToken && auth()->user()->vaiTro->TenVaiTro === 'Trưởng khoa' && !$params) {
            $response = $this->preview()->assertOk();
            $this->previewToken = $response->viewData('token');
        }
        return $this->post(route('truongkhoa.hoidong.congBoToanDot'), array_merge(['MaHocKy' => $this->semester, 'DotBaoVe' => $this->round, 'preview_token' => $this->previewToken], $params));
    }

    public function test_publishes_all_drafts_and_retry_does_not_duplicate_notifications(): void
    {
        $first = $this->council();
        $second = $this->council();
        $drafts = [$first, $second];
        // The list has five items per page; publication must include later pages.
        for ($i = 0; $i < 4; $i++) $drafts[] = $this->council();
        $published = $this->council(['TrangThaiCongBo' => 'Đã công bố']);
        $this->publish()->assertSessionHas('success')->assertSessionHasNoErrors();
        foreach ($drafts as $council) {
            $council->refresh();
            $this->assertSame('Đã công bố', $council->TrangThaiCongBo);
            $path = storage_path('app/public/'.substr($council->FileQuyetDinh, strlen('storage/')));
            $this->assertFileExists($path);
            $this->assertStringStartsWith('%PDF-', file_get_contents($path));
        }
        $this->assertCount(1, collect($drafts)->map(fn ($row) => $row->fresh()->FileQuyetDinh)->unique());
        $this->assertTrue(copy($path, 'C:/Users/hochidung/Documents/Codex/2026-10-25/b-n-v-o-c-laragon/work/council-pdf-reference/generated-batch.pdf'));
        $this->assertSame($first->fresh()->FileQuyetDinh, $published->fresh()->FileQuyetDinh);
        $count = ThongBao::count();
        $this->publish()->assertSessionHas('info');
        $this->assertSame($count, ThongBao::count());
    }

    public function test_incomplete_council_blocks_the_whole_batch_before_publication(): void
    {
        $valid = $this->council();
        $invalid = $this->council([], false);
        $count = ThongBao::count();
        $this->publish()->assertSessionHasErrors('cong_bo');
        foreach ([$valid, $invalid] as $council) {
            $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
            $this->assertNull($council->fresh()->FileQuyetDinh);
        }
        $this->assertSame($count, ThongBao::count());
    }

    public function test_publication_is_scoped_to_faculty_semester_and_exact_round(): void
    {
        $own = $this->council();
        $otherRound = $this->council(['DotBaoVe' => $this->round.' khác']);
        $otherFaculty = $this->council(['MaKhoa' => Khoa::where('MaKhoa', '!=', 'CNTT')->firstOrFail()->MaKhoa]);
        $otherSemester = $this->council(['MaHocKy' => HocKy::where('MaHocKy', '!=', $this->semester)->firstOrFail()->MaHocKy]);
        $this->publish()->assertSessionHas('success');
        $this->assertSame('Đã công bố', $own->fresh()->TrangThaiCongBo);
        foreach ([$otherRound, $otherFaculty, $otherSemester] as $council) {
            $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
        }
    }

    public function test_publication_requires_scope_and_legacy_single_action_only_redirects(): void
    {
        $council = $this->council();
        $count = ThongBao::count();
        $this->publish(['MaHocKy' => '', 'DotBaoVe' => ''])->assertSessionHasErrors(['MaHocKy', 'DotBaoVe']);
        $this->post(route('truongkhoa.hoidong.congBo', $council->MaHoiDong))->assertRedirect(route('truongkhoa.hoidong.index', ['MaHocKy' => $this->semester, 'DotBaoVe' => $this->round]));
        $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
        $this->assertSame($count, ThongBao::count());
    }

    public function test_preview_fragment_keeps_private_pdf_and_publication_token(): void
    {
        $this->council();
        $response = $this->preview(['popup' => 1])->assertOk()
            ->assertSee('decision-preview-content', false)->assertDontSee('<html', false)
            ->assertSee('data-bs-dismiss="modal"', false)->assertSee('preview_token', false)
            ->assertSee('Tải bản xem trước');
        $token = $response->viewData('token');
        $this->get(route('truongkhoa.hoidong.previewDecisionPdf', ['token' => $token]))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('truongkhoa.hoidong.index', ['MaHocKy' => $this->semester, 'DotBaoVe' => $this->round]))
            ->assertOk()->assertSee('council-decision-dialog', false)->assertSee('council_decision_preview.js', false);
    }

    public function test_ui_offers_one_batch_action_and_no_immediate_publication_checkbox(): void
    {
        $council = $this->council();
        $this->get(route('truongkhoa.hoidong.index', ['MaHocKy' => $this->semester, 'DotBaoVe' => $this->round]))
            ->assertOk()->assertSee('Xem trước quyết định');
        $this->get(route('truongkhoa.hoidong.create'))->assertOk()->assertDontSee('name="cong_bo_ngay"', false);
        $this->get(route('truongkhoa.hoidong.show', $council->MaHoiDong))->assertOk()->assertSee('Xem quyết định chung của đợt');
    }

    public function test_create_stays_draft_even_if_an_old_tab_posts_publish_immediately(): void
    {
        $dossier = HoSoBaoVe::with('nhom.deTai')->firstOrFail();
        $dossier->update(['MaHoiDong' => null, 'XacNhanGVHD' => 'Đã duyệt']);
        $dossier->phienBans()->create(['PhienBan' => 999, 'DuongDanBaoCao' => 'hoso/'.$dossier->MaNhom.'/fixture.pdf', 'NgayNop' => now()]);
        $teachers = GiangVien::where('MaGV', '!=', $dossier->nhom?->deTai?->MaGV ?? 'NONE')->limit(3)->get();
        $count = ThongBao::count();
        $response = $this->post(route('truongkhoa.hoidong.store'), [
            'TenHoiDong' => 'Hội đồng kiểm thử lưu nháp', 'MaHocKy' => $this->semester,
            'DotBaoVe' => $this->round, 'NgayBaoVe' => '2041-01-10',
            'ThoiGianBatDau' => '2041-01-10 08:00:00', 'ThoiGianKetThuc' => '2041-01-10 10:00:00',
            'DiaDiem' => 'Phòng kiểm thử', 'danh_sach_ho_so' => [$dossier->MaHoSo],
            'thanh_viens' => collect(['Chủ tịch', 'Thư ký', 'Phản biện'])->map(fn ($role, $i) => ['MaGV' => $teachers[$i]->MaGV, 'VaiTro' => $role])->all(),
            'cong_bo_ngay' => 1,
        ]);
        $response->assertSessionHasNoErrors();
        $created = HoiDong::where('MaHocKy', $this->semester)->where('TenHoiDong', 'Hội đồng kiểm thử lưu nháp')->firstOrFail();
        $this->councils[] = $created->MaHoiDong;
        $this->assertSame('Chưa công bố', $created->TrangThaiCongBo);
        $this->assertNull($created->FileQuyetDinh);
        $this->assertSame($count, ThongBao::count());
    }

    public function test_other_roles_cannot_publish_a_batch(): void
    {
        $this->council();
        $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))->firstOrFail();
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
        $this->publish()->assertForbidden();
    }

    public function test_conflicting_decision_metadata_blocks_one_shared_decision(): void
    {
        $first = $this->council(['SoQuyetDinh' => 'TEST/01']);
        $second = $this->council(['SoQuyetDinh' => 'TEST/02']);
        $this->publish()->assertSessionHasErrors('cong_bo');
        $this->assertSame('Chưa công bố', $first->fresh()->TrangThaiCongBo);
        $this->assertSame('Chưa công bố', $second->fresh()->TrangThaiCongBo);
    }

    public function test_publisher_sees_notice_in_bell_and_inbox_with_attached_decision(): void
    {
        $council = $this->council();
        $publisher = auth()->user();
        $this->publish()->assertSessionHas('success');
        $notice = ThongBao::where('LoaiThongBao', 'Hội đồng')->where('NoiDung', 'like', '%'.$this->round.'%')->where('DoiTuongNhan', 'Toàn trường')->firstOrFail();
        $title = $notice->TieuDe;
        $this->assertSame($council->fresh()->FileQuyetDinh, $notice->FileDinhKem);
        $this->assertSame('Đã phát hành', $notice->TrangThai);
        $this->assertStringContainsString(e($title), view('partials.header_notifications')->render());
        $this->get(route('truongkhoa.thongbao.index', ['search' => $this->round]))
            ->assertOk()->assertViewHas('thongbaos', fn ($rows) => $rows->contains('MaThongBao', $notice->MaThongBao));
        $before = ThongBao::where('TieuDe', $title)->count();
        $this->publish()->assertSessionHas('info');
        $this->assertSame($before, ThongBao::where('TieuDe', $title)->count());
    }

    public function test_admin_inbox_includes_own_direct_publication_notices(): void
    {
        $council = $this->council();
        $this->publish()->assertSessionHas('success');
        $admin = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->whereIn('TenVaiTro', ['Admin', 'Giáo vụ']))->firstOrFail();
        $admin->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $notice = ThongBao::where('LoaiThongBao', 'Hội đồng')->where('NoiDung', 'like', '%'.$this->round.'%')->where('DoiTuongNhan', 'Toàn trường')->firstOrFail();
        $title = $notice->TieuDe;
        $this->actingAs($admin)->get(route('admin.thongbao.index', ['search' => $this->round]))
            ->assertOk()->assertViewHas('thongbaos', fn ($rows) => $rows->contains('MaThongBao', $notice->MaThongBao)
                && $rows->every(fn ($row) => $row->DoiTuongNhan === 'Toàn trường'));
        $this->assertStringContainsString(e($title), view('partials.header_notifications')->render());
    }

    public function test_preview_ignores_table_filters_and_freezes_exact_published_bytes(): void
    {
        for ($i = 0; $i < 7; $i++) $this->council();
        $this->council(['DotBaoVe' => $this->round.' khác']);
        $response = $this->preview(['page' => 2, 'search' => 'NONE', 'TrangThaiCongBo' => 'Đã công bố'])->assertOk();
        $this->assertCount(7, $response->viewData('councils'));
        $this->previewToken = $response->viewData('token');
        $before = $this->get(route('truongkhoa.hoidong.previewDecisionPdf', ['token' => $this->previewToken]))->assertOk()->getContent();
        $gradeState = HoSoBaoVe::whereIn('MaHoSo', $this->dossiers)->get()->mapWithKeys(fn ($row) => [$row->MaHoSo => $row->getAttributes()])->all();
        $this->publish()->assertSessionHas('success');
        $notice = ThongBao::where('DoiTuongNhan', 'Toàn trường')->where('NoiDung', 'like', '%'.$this->round.'%')->firstOrFail();
        $attachment = \App\Services\NotificationAttachmentService::resolve($notice);
        $this->assertSame($before, $this->get($attachment['url'].'?download=1')->assertOk()->streamedContent());
        HoiDong::whereKey($this->councils[0])->update(['TenHoiDong' => 'Tên thay đổi sau công bố']);
        $this->assertSame($before, $this->get($attachment['url'].'?download=1')->assertOk()->streamedContent());
        $this->assertSame($gradeState, HoSoBaoVe::whereIn('MaHoSo', $this->dossiers)->get()->mapWithKeys(fn ($row) => [$row->MaHoSo => $row->getAttributes()])->all());
    }

    public function test_changed_preview_is_rejected_and_empty_round_cannot_publish(): void
    {
        $council = $this->council();
        $this->previewToken = $this->preview()->assertOk()->viewData('token');
        $council->update(['DiaDiem' => 'Phòng đã đổi']);
        $before = ThongBao::count();
        $this->publish()->assertSessionHasErrors('cong_bo');
        $this->assertSame($before, ThongBao::count());
        $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
        $this->preview(['DotBaoVe' => 'Đợt trống'])->assertRedirect()->assertSessionHas('error');
        $this->publish(['DotBaoVe' => 'Đợt trống'])->assertStatus(422);
    }

    public function test_missing_official_metadata_is_explicit_and_blocks_publication(): void
    {
        $this->council(['NguoiKy' => null, 'SoQuyetDinh' => null, 'NgayBanHanh' => null]);
        $response = $this->preview()->assertOk()->assertSee('Cần bổ sung trước khi công bố');
        $this->assertNotEmpty($response->viewData('issues'));
        $this->previewToken = $response->viewData('token');
        $this->publish()->assertSessionHasErrors('cong_bo');
    }

    public function test_file_write_failure_rolls_back_without_broken_notice(): void
    {
        $council = $this->council();
        $this->previewToken = $this->preview()->assertOk()->viewData('token');
        $before = ThongBao::count();
        $realDisk = \Illuminate\Support\Facades\Storage::disk('public');
        $failedDisk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $failedDisk->shouldReceive('put')->once()->andReturn(false);
        $failedDisk->shouldReceive('delete')->once()->andReturn(true);
        \Illuminate\Support\Facades\Storage::set('public', $failedDisk);
        try {
            $this->publish()->assertSessionHas('error');
            $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
            $this->assertNull($council->fresh()->FileQuyetDinh);
            $this->assertSame($before, ThongBao::count());
        } finally { \Illuminate\Support\Facades\Storage::set('public', $realDisk); }
    }

    public function test_invalid_or_expired_preview_cannot_publish(): void
    {
        $this->council();
        $this->publish(['preview_token' => (string) \Illuminate\Support\Str::uuid()])->assertNotFound();
        $this->previewToken = $this->preview()->assertOk()->viewData('token');
        $this->travel(61)->minutes();
        $this->publish()->assertStatus(410);
        $this->travelBack();
    }

    public function test_global_notice_reaches_every_role_and_unrelated_account_can_download(): void
    {
        $this->council();
        $before = ThongBao::count();
        $this->publish()->assertSessionHas('success');
        $this->assertSame($before + 1, ThongBao::count());
        $notice = ThongBao::where('DoiTuongNhan', 'Toàn trường')->where('NoiDung', 'like', '%'.$this->round.'%')->firstOrFail();
        $attachment = \App\Services\NotificationAttachmentService::resolve($notice);
        foreach (['Admin' => 'admin', 'Giáo vụ' => 'admin', 'Trưởng khoa' => 'truongkhoa', 'Trưởng bộ môn' => 'truongbomon', 'Giảng viên' => 'giangvien', 'Sinh viên' => 'sinhvien'] as $role => $prefix) {
            $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', $role))->first();
            if (!$user) {
                $roleModel = \App\Models\VaiTro::firstOrCreate(['TenVaiTro' => $role], ['MaVaiTro' => 'VT_T_'.bin2hex(random_bytes(3))]);
                $user = TaiKhoan::firstOrFail()->replicate();
                $user->forceFill(['MaTK' => 'TK_T_'.bin2hex(random_bytes(4)), 'TenDangNhap' => 'TEST_'.bin2hex(random_bytes(4)), 'MaVaiTro' => $roleModel->MaVaiTro, 'MaGV' => null])->save();
            }
            $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            $this->actingAs($user);
            $response = $this->get(route($prefix.'.thongbao.index', ['search' => $this->round]));
            $this->assertSame(200, $response->status(), $role.' redirect: '.$response->headers->get('Location'));
            $response->assertOk()
                ->assertViewHas('thongbaos', fn ($rows) => $rows->contains('MaThongBao', $notice->MaThongBao));
            $this->assertStringContainsString(e($notice->TieuDe), view('partials.header_notifications')->render());
            $this->get(route($role === 'Sinh viên' ? 'sinhvien.thongbao.show' : 'thongbao.show', $notice->MaThongBao))->assertOk();
            $this->assertStringStartsWith('%PDF-', $this->get($attachment['url'].'?download=1')->assertOk()->streamedContent());
        }
    }

    public function test_pdf_generation_failure_never_publishes(): void
    {
        $council = $this->council();
        $before = ThongBao::count();
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('loadView')->once()->andThrow(new \RuntimeException('Simulated PDF failure'));
        $this->preview()->assertRedirect()->assertSessionHas('error');
        $this->assertSame('Chưa công bố', $council->fresh()->TrangThaiCongBo);
        $this->assertSame($before, ThongBao::count());
    }
}
