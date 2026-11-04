<?php

namespace Tests\Feature;

use App\Models\BaoCaoTienDo;
use App\Models\GiangVien;
use App\Models\PhieuDangKy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RetiredTeacherReviewTest extends TestCase
{
    use DatabaseTransactions;

    private function report(): BaoCaoTienDo
    {
        return BaoCaoTienDo::with('deTai.giangVien.taiKhoan')
            ->whereHas('deTai.giangVien.taiKhoan.vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))
            ->firstOrFail();
    }

    private function loginTeacher($teacher): void
    {
        $teacher->taiKhoan->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($teacher->taiKhoan);
    }

    public function test_old_review_redirects_without_starting_ai(): void
    {
        Http::fake();
        Queue::fake();
        $report = $this->report();
        $this->loginTeacher($report->deTai->giangVien);
        $group = PhieuDangKy::where('MaDeTai', $report->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])->value('MaNhom');

        $this->get(route('giangvien.baocao.show', $report->MaBaoCao))
            ->assertRedirect(route('giangvien.baocao.index', $group ? ['nhom' => $group] : []));
        Http::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_old_review_still_rejects_other_teachers(): void
    {
        $report = $this->report();
        $other = GiangVien::with('taiKhoan')->where('MaGV', '!=', $report->deTai->MaGV)
            ->whereHas('taiKhoan.vaiTro', fn ($q) => $q->where('TenVaiTro', 'Giảng viên'))->firstOrFail();
        $this->loginTeacher($other);
        $this->get(route('giangvien.baocao.show', $report->MaBaoCao))->assertForbidden();
    }

    public function test_unknown_legacy_report_returns_not_found(): void
    {
        $this->loginTeacher($this->report()->deTai->giangVien);
        $this->get(route('giangvien.baocao.show', 'NOT_A_REAL_REVIEW'))->assertNotFound();
    }

    public function test_dashboard_and_current_progress_remain_available(): void
    {
        Http::fake();
        Queue::fake();
        $this->loginTeacher($this->report()->deTai->giangVien);
        $this->get('/giangvien')->assertOk()->assertDontSee('Review (AI)')->assertDontSee('btn-ai-review');
        $this->get(route('giangvien.baocao.index'))->assertOk();
    }
}
