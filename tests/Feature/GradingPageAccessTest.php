<?php
namespace Tests\Feature;

use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\GiangVien;
use App\Models\ThanhVienHoiDong;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GradingPageAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_role_helpers_use_actual_role_and_fail_closed_when_missing(): void
    {
        foreach([
            ['Trưởng khoa',true,false],['Admin',false,true],['Giáo vụ',false,true],
            ['Trưởng bộ môn',false,false],['Giảng viên',false,false],['Sinh viên',false,false],
        ] as [$role,$dean,$admin]) {
            $user=new TaiKhoan();$user->setRelation('vaiTro',new VaiTro(['TenVaiTro'=>$role]));
            $this->assertSame($dean,$user->isTruongKhoa());$this->assertSame($admin,$user->isAdmin());
        }
        $user=new TaiKhoan();$user->setRelation('vaiTro',null);
        $this->assertFalse($user->isTruongKhoa());$this->assertFalse($user->isAdmin());
    }

    public function test_lecturer_from_error_log_can_open_grading_page(): void
    {
        $gv=GiangVien::findOrFail('GV00000023');
        $user=TaiKhoan::where('MaTK',$gv->MaTK)->firstOrFail();
        $this->actingAs($user)->get(route('giangvien.chamdiem.index'))
            ->assertRedirect(route('giangvien.hoidong.index', ['tab' => 'chamdiem']));
        $this->get(route('giangvien.hoidong.index', ['tab' => 'chamdiem']))->assertOk();
    }

    public function test_privileged_lecturer_without_council_does_not_render_null_dossier_modal(): void
    {
        $gv=GiangVien::findOrFail('GV00000023');
        $user=TaiKhoan::where('MaTK',$gv->MaTK)->firstOrFail();
        $user->MaVaiTro=VaiTro::where('TenVaiTro','Trưởng khoa')->firstOrFail()->MaVaiTro;$user->save();
        ThanhVienHoiDong::where('MaGV',$gv->MaGV)->delete();
        $this->actingAs($user)->get(route('giangvien.hoidong.index', ['tab' => 'chamdiem']))->assertOk()
            ->assertDontSee('id="modalCaiDatChucNang"',false);
    }

    public function test_chair_can_save_functional_requirements_and_reload_grading_page(): void
    {
        $dossier=\App\Models\HoSoBaoVe::whereNotNull('MaHoiDong')->firstOrFail();
        $secretary=ThanhVienHoiDong::where('MaHoiDong',$dossier->MaHoiDong)
            ->whereIn('VaiTro',['Thư ký','Ủy viên thư ký'])->first();
        if (!$secretary) {
            $secretary=ThanhVienHoiDong::where('MaHoiDong',$dossier->MaHoiDong)->firstOrFail();
            $secretary->update(['VaiTro'=>'Thư ký']);
        }
        $gv=GiangVien::findOrFail($secretary->MaGV);
        $user=TaiKhoan::where('MaTK',$gv->MaTK)->firstOrFail();
        $requirements="+ Chức năng 01: Quản lý đề tài và xét duyệt đề cương\n  * Yêu cầu 1: Giảng viên đề xuất đề tài.";
        $this->actingAs($user)->post(route('giangvien.chamdiem.capNhatChucNang'),[
            'MaHoiDong'=>$dossier->MaHoiDong, 'MaHoSo'=>$dossier->MaHoSo,
            'TieuChiChucNang'=>$requirements,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($requirements,$dossier->fresh()->TieuChiChucNang);
        $this->get(route('giangvien.hoidong.index',['hd'=>$dossier->MaHoiDong,'hoso'=>$dossier->MaHoSo,'tab'=>'chamdiem']))
            ->assertOk()->assertSee('Giảng viên đề xuất đề tài.');
    }
}
