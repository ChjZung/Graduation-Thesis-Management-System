<?php

namespace Tests\Feature;

use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\TaiKhoan;
use App\Models\GiaiDoanThucHien;
use App\Models\KhoiBaoCaoTienDo;
use App\Services\ThesisProgressService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** All local fixture changes are rolled back. No files or AI requests are used. */
class LecturerStageManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function fixture(): array
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $lecturer = GiangVien::where('MaGV', $topic->MaGV)->firstOrFail();
        $user = TaiKhoan::where('MaTK', $lecturer->MaTK)->firstOrFail();
        $stage = app(ThesisProgressService::class)->taoGiaiDoan($topic, [
            'TenGiaiDoan' => "Giai đoạn kiểm thử O'Brien", 'MoTa' => 'Mục tiêu cũ',
            'NoiDungCongViec' => 'Việc cũ', 'KetQuaDauRa' => 'Kết quả cũ',
            'NgayBatDau' => '2026-10-01', 'NgayKetThuc' => '2026-10-31',
        ]);
        return [$stage, $user];
    }

    public function test_owner_can_edit_and_clear_optional_fields_without_changing_topic(): void
    {
        [$stage, $user] = $this->fixture();
        $topic = $stage->MaDeTai;
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.giai_doan.update', $stage), [
            'TenGiaiDoan' => 'Tên đã sửa', 'MoTa' => '', 'NoiDungCongViec' => '',
            'KetQuaDauRa' => '', 'NgayBatDau' => '', 'NgayKetThuc' => '',
            'TrangThai' => 'DANG_THUC_HIEN', 'MaDeTai' => 'UNAUTHORIZED_CHANGE',
        ])->assertRedirect('/giangvien/baocao')->assertSessionHasNoErrors();
        $stage->refresh();
        $this->assertSame('Tên đã sửa', $stage->TenGiaiDoan);
        $this->assertSame('DANG_THUC_HIEN', $stage->TrangThai);
        $this->assertSame($topic, $stage->MaDeTai);
        foreach (['MoTa', 'NoiDungCongViec', 'KetQuaDauRa', 'NgayBatDau', 'NgayKetThuc'] as $field) {
            $this->assertNull($stage->$field);
        }
    }

    public function test_invalid_dates_keep_stage_unchanged_and_return_edit_errors(): void
    {
        [$stage, $user] = $this->fixture();
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.giai_doan.update', $stage), [
            '_stage_id' => $stage->getKey(), 'TenGiaiDoan' => 'Không lưu',
            'NgayBatDau' => '2026-10-20', 'NgayKetThuc' => '2026-10-01', 'TrangThai' => 'HOAN_THANH',
        ])->assertRedirect()->assertSessionHasErrors(['NgayKetThuc'], null, 'updateStage');
        $this->assertSame("Giai đoạn kiểm thử O'Brien", $stage->fresh()->TenGiaiDoan);
    }

    public function test_other_lecturer_cannot_edit_or_delete_stage(): void
    {
        [$stage] = $this->fixture();
        $other = GiangVien::where('MaGV', '!=', $stage->deTai->MaGV)->whereNotNull('MaTK')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $other->MaTK)->firstOrFail();
        $this->actingAs($user)->put(route('giangvien.tiendo.giai_doan.update', $stage), [])->assertForbidden();
        $this->delete(route('giangvien.tiendo.giai_doan.destroy', $stage))->assertForbidden();
        $this->assertNotNull($stage->fresh());
    }

    public function test_owner_can_delete_stage_with_empty_rounds(): void
    {
        [$stage, $user] = $this->fixture();
        $round = app(ThesisProgressService::class)->taoDotNop($stage, ['TenDotNop' => 'Đợt chưa nộp']);
        $this->actingAs($user)->delete(route('giangvien.tiendo.giai_doan.destroy', $stage))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertNull($stage->fresh());
        $this->assertNull($round->fresh());
    }

    public function test_delete_preserves_direct_stage_submission(): void
    {
        $this->assertSubmissionPreventsDeletion(false);
    }

    public function test_delete_preserves_submission_linked_only_through_round(): void
    {
        $this->assertSubmissionPreventsDeletion(true);
    }

    private function assertSubmissionPreventsDeletion(bool $roundOnly): void
    {
        [$stage, $user] = $this->fixture();
        $round = app(ThesisProgressService::class)->taoDotNop($stage, ['TenDotNop' => 'Đợt có bài']);
        $source = KhoiBaoCaoTienDo::firstOrFail();
        $report = $source->replicate(['id']);
        $report->MaKhoi = 'TEST_STAGE_' . bin2hex(random_bytes(5));
        $report->MaGiaiDoan = $roundOnly ? null : $stage->getKey();
        $report->MaDotNop = $roundOnly ? $round->getKey() : null;
        $report->MaDeTai = $stage->MaDeTai;
        $report->LanNop = 998;
        $report->save();
        $this->actingAs($user)->delete(route('giangvien.tiendo.giai_doan.destroy', $stage))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($stage->fresh());
        $this->assertNotNull($round->fresh());
        $this->assertNotNull($report->fresh());
    }
}
