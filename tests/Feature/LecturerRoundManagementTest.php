<?php

namespace Tests\Feature;

use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\TaiKhoan;
use App\Models\KhoiBaoCaoTienDo;
use App\Services\ThesisProgressService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Fixture data is rolled back; no student files or AI calls are modified. */
class LecturerRoundManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function fixture(): array
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $topic->giangVien->MaTK)->firstOrFail();
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, ['TenGiaiDoan'=>'Giai đoạn kiểm thử đợt']);
        $round = $service->taoDotNop($stage, [
            'TenDotNop'=>"Đợt O'Brien", 'MoTa'=>'Mô tả cần giữ', 'NoiDungYeuCau'=>'Yêu cầu cũ',
            'NgayMoNop'=>'2026-10-01 08:00:00', 'HanNop'=>'2026-10-31 17:00:00', 'YeuCauSourceCode'=>true,
        ]);
        return [$round,$user];
    }

    public function test_owner_can_edit_round_and_disable_github_without_changing_stage(): void
    {
        [$round,$user] = $this->fixture();
        $stageId = $round->MaGiaiDoan;
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.dot_nop.update',$round),[
            'TenDotNop'=>'Đợt đã sửa','NoiDungYeuCau'=>'','NgayMoNop'=>'','HanNop'=>'',
            'YeuCauSourceCode'=>'0','SoLanNopToiDa'=>'','TrangThai'=>'TAM_DONG','MaGiaiDoan'=>999999,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $round->refresh();
        $this->assertSame('Đợt đã sửa',$round->TenDotNop);
        $this->assertSame('TAM_DONG',$round->TrangThai);
        $this->assertFalse($round->YeuCauSourceCode);
        $this->assertNull($round->NoiDungYeuCau);
        $this->assertNull($round->NgayMoNop);
        $this->assertNull($round->HanNop);
        $this->assertNull($round->SoLanNopToiDa);
        $this->assertSame($stageId,$round->MaGiaiDoan);
        $this->assertSame('Mô tả cần giữ',$round->MoTa);
        $this->assertSame('pdf',$round->DinhDangChoPhep);
    }

    public function test_invalid_deadline_returns_edit_errors_and_preserves_old_data(): void
    {
        [$round,$user]=$this->fixture();
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.dot_nop.update',$round),[
            '_round_id'=>$round->getKey(),'TenDotNop'=>'Không lưu','TrangThai'=>'DANG_MO',
            'NgayMoNop'=>'2026-10-20T09:00','HanNop'=>'2026-10-19T09:00',
        ])->assertRedirect()->assertSessionHasErrors(['HanNop'],null,'updateRound');
        $this->assertSame("Đợt O'Brien",$round->fresh()->TenDotNop);
    }

    public function test_other_lecturer_cannot_edit_or_delete_round(): void
    {
        [$round]=$this->fixture();
        $other=GiangVien::where('MaGV','!=',$round->deTai->MaGV)->whereNotNull('MaTK')->firstOrFail();
        $user=TaiKhoan::where('MaTK',$other->MaTK)->firstOrFail();
        $this->actingAs($user)->put(route('giangvien.tiendo.dot_nop.update',$round),[])->assertForbidden();
        $this->delete(route('giangvien.tiendo.dot_nop.destroy',$round))->assertForbidden();
        $this->assertNotNull($round->fresh());
    }

    public function test_delete_empty_round_preserves_parent_stage_and_sibling_round(): void
    {
        [$round,$user]=$this->fixture();
        $stage=$round->giaiDoan;
        $sibling=app(ThesisProgressService::class)->taoDotNop($stage,['TenDotNop'=>'Đợt khác']);
        $this->actingAs($user)->delete(route('giangvien.tiendo.dot_nop.destroy',$round))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertNull($round->fresh());
        $this->assertNotNull($stage->fresh());
        $this->assertNotNull($sibling->fresh());
    }

    public function test_submitted_round_cannot_be_deleted_but_can_be_edited(): void
    {
        [$round,$user]=$this->fixture();
        $report=KhoiBaoCaoTienDo::firstOrFail()->replicate(['id']);
        $report->MaKhoi='TEST_ROUND_'.bin2hex(random_bytes(5));
        $report->MaDotNop=$round->getKey();$report->MaGiaiDoan=$round->MaGiaiDoan;$report->LanNop=998;$report->save();
        $this->actingAs($user)->delete(route('giangvien.tiendo.dot_nop.destroy',$round))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($round->fresh());$this->assertNotNull($report->fresh());
        $this->put(route('giangvien.tiendo.dot_nop.update',$round),[
            'TenDotNop'=>'Đã gia hạn yêu cầu','TrangThai'=>'DANG_MO','YeuCauSourceCode'=>1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Đã gia hạn yêu cầu',$round->fresh()->TenDotNop);
        $this->assertTrue($round->fresh()->YeuCauSourceCode);
        $this->assertNotNull($report->fresh());
    }

    public function test_cannot_create_round_with_opening_date_before_stage_start(): void
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $topic->giangVien->MaTK)->firstOrFail();
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, [
            'TenGiaiDoan' => 'GĐ Có Hạn',
            'NgayBatDau' => '2026-11-01',
            'NgayKetThuc' => '2026-11-05',
        ]);

        $this->actingAs($user)->from('/giangvien/baocao')->post(route('giangvien.tiendo.dot_nop.store'), [
            'MaGiaiDoan' => $stage->MaGiaiDoan,
            'TenDotNop' => 'Đợt mở sớm trước giai đoạn',
            'NgayMoNop' => '2026-10-27T13:14',
            'HanNop' => '2026-11-03T17:00',
        ])->assertRedirect()->assertSessionHasErrors(['NgayMoNop'], null, 'addRound');
    }

    public function test_cannot_create_round_with_deadline_after_stage_end(): void
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $topic->giangVien->MaTK)->firstOrFail();
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, [
            'TenGiaiDoan' => 'GĐ Có Hạn 2',
            'NgayBatDau' => '2026-11-01',
            'NgayKetThuc' => '2026-11-05',
        ]);

        $this->actingAs($user)->from('/giangvien/baocao')->post(route('giangvien.tiendo.dot_nop.store'), [
            'MaGiaiDoan' => $stage->MaGiaiDoan,
            'TenDotNop' => 'Đợt hạn quá trễ',
            'NgayMoNop' => '2026-11-02T08:00',
            'HanNop' => '2026-11-06T17:00',
        ])->assertRedirect()->assertSessionHasErrors(['HanNop'], null, 'addRound');
    }

    public function test_cannot_update_round_outside_stage_time_boundaries(): void
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $topic->giangVien->MaTK)->firstOrFail();
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, [
            'TenGiaiDoan' => 'GĐ Có Hạn 3',
            'NgayBatDau' => '2026-11-01',
            'NgayKetThuc' => '2026-11-05',
        ]);
        $round = $service->taoDotNop($stage, [
            'TenDotNop' => 'Đợt hợp lệ ban đầu',
            'NgayMoNop' => '2026-11-01 08:00:00',
            'HanNop' => '2026-11-05 17:00:00',
        ]);

        // Thử sửa Ngày mở nộp trước ngày bắt đầu giai đoạn
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.dot_nop.update', $round), [
            '_round_id' => $round->getKey(),
            'TenDotNop' => 'Đợt sửa ngày sai',
            'TrangThai' => 'DANG_MO',
            'NgayMoNop' => '2026-10-31T23:59',
            'HanNop' => '2026-11-03T17:00',
        ])->assertRedirect()->assertSessionHasErrors(['NgayMoNop'], null, 'updateRound');

        // Thử sửa Hạn nộp sau ngày kết thúc giai đoạn
        $this->actingAs($user)->from('/giangvien/baocao')->put(route('giangvien.tiendo.dot_nop.update', $round), [
            '_round_id' => $round->getKey(),
            'TenDotNop' => 'Đợt sửa ngày sai 2',
            'TrangThai' => 'DANG_MO',
            'NgayMoNop' => '2026-11-02T08:00',
            'HanNop' => '2026-11-06T00:01',
        ])->assertRedirect()->assertSessionHasErrors(['HanNop'], null, 'updateRound');
    }

    public function test_can_create_and_update_round_within_stage_boundaries(): void
    {
        $topic = DeTai::whereHas('giangVien')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $topic->giangVien->MaTK)->firstOrFail();
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, [
            'TenGiaiDoan' => 'GĐ Chuẩn',
            'NgayBatDau' => '2026-11-01',
            'NgayKetThuc' => '2026-11-05',
        ]);

        $this->actingAs($user)->from('/giangvien/baocao')->post(route('giangvien.tiendo.dot_nop.store'), [
            'MaGiaiDoan' => $stage->MaGiaiDoan,
            'TenDotNop' => 'Đợt chuẩn thời gian',
            'NgayMoNop' => '2026-11-01T08:00',
            'HanNop' => '2026-11-05T17:00',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('dotnopbaocao', [
            'MaGiaiDoan' => $stage->MaGiaiDoan,
            'TenDotNop' => 'Đợt chuẩn thời gian',
        ]);
    }

    public function test_toggle_dot_nop_status_success_and_no_missing_column_error(): void
    {
        [$round, $user] = $this->fixture();
        $this->assertSame('DANG_MO', $round->TrangThai);

        // Đóng đợt nộp
        $this->actingAs($user)->post(route('giangvien.tiendo.dot_nop.toggle', $round))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('DA_DONG', $round->fresh()->TrangThai);

        // Mở lại đợt nộp
        $this->actingAs($user)->post(route('giangvien.tiendo.dot_nop.toggle', $round))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('DANG_MO', $round->fresh()->TrangThai);
    }
}
