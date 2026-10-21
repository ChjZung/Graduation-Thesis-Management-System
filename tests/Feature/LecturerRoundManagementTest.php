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
        ])->assertRedirect('/giangvien/baocao')->assertSessionHasNoErrors();
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
}
