<?php
namespace Tests\Feature;

use App\Models\HoSoBaoVe;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use App\Models\TaiKhoan;
use App\Models\PhieuChamDiem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GradingDraftSaveTest extends TestCase
{
    use DatabaseTransactions;

    private function fixture(): array
    {
        $dossier=HoSoBaoVe::with('nhom.thanhViens')->whereNotNull('MaHoiDong')->firstOrFail();
        $chair=ThanhVienHoiDong::where('MaHoiDong',$dossier->MaHoiDong)->where('VaiTro','Chủ tịch')->firstOrFail();
        $gv=GiangVien::findOrFail($chair->MaGV);
        $user=TaiKhoan::where('MaTK',$gv->MaTK)->firstOrFail();
        // Keep this fixture unsigned, even when the lecturer has a saved signature.
        $gv->update(['ChuKy'=>null]);
        return [$dossier,$gv,$user];
    }

    public function test_draft_saves_numeric_student_keys_instead_of_silently_skipping_students(): void
    {
        [$dossier,$gv,$user]=$this->fixture();
        $students=$dossier->nhom->thanhViens->pluck('MaSV');
        $scores=[];
        foreach($students as $id) $scores[$id]=['ChiTietDiem'=>['tc1'=>0.6,'tc7'=>3.2],'NhanXet'=>'DỮ LIỆU KIỂM THỬ'];
        $this->actingAs($user)->post(route('giangvien.chamdiem.store'),[
            'MaHoiDong'=>$dossier->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,
            'LoaiKhoaLuan'=>'KLCN','action_type'=>'luu_nhap','diems'=>$scores,
        ])->assertRedirect()->assertSessionHasNoErrors();
        foreach($students as $id) {
            $record=PhieuChamDiem::where('MaHoiDong',$dossier->MaHoiDong)->where('MaGV',$gv->MaGV)->where('MaSV',$id)->firstOrFail();
            $this->assertEquals(3.8,$record->Diem);
            $this->assertSame('Lưu nháp',$record->TrangThai);
            $this->assertSame('DỮ LIỆU KIỂM THỬ',$record->NhanXet);
            $this->assertNull($record->NgayXacNhan);
            $this->assertNull($record->ChuKy);
        }
        $this->get(route('giangvien.chamdiem.index',['hd'=>$dossier->MaHoiDong,'hoso'=>$dossier->MaHoSo]))
            ->assertOk()->assertSee('DỮ LIỆU KIỂM THỬ');
    }

    public function test_unknown_student_key_is_rejected_without_saving_other_scores(): void
    {
        [$dossier,$gv,$user]=$this->fixture();
        $count=PhieuChamDiem::count();
        $this->actingAs($user)->post(route('giangvien.chamdiem.store'),[
            'MaHoiDong'=>$dossier->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,
            'LoaiKhoaLuan'=>'KLCN','action_type'=>'luu_nhap',
            'diems'=>['9999999999'=>['ChiTietDiem'=>['tc1'=>0.5]]],
        ])->assertForbidden();
        $this->assertSame($count,PhieuChamDiem::count());
    }
}
