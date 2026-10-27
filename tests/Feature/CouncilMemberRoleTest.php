<?php
namespace Tests\Feature;

use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Roll back all council fixtures and assignments; do not publish or send notifications. */
class CouncilMemberRoleTest extends TestCase
{
    use DatabaseTransactions;

    private function user(): TaiKhoan
    {
        return TaiKhoan::where('TenDangNhap','like','TK_CNTT_%')->firstOrFail();
    }

    public function test_numbered_legacy_member_and_canonical_member_can_be_saved(): void
    {
        $source=HoSoBaoVe::with('deTai')->whereHas('deTai')->firstOrFail();
        $report=$source->replicate(['id']);
        $report->MaHoSo='TEST_ROLE_'.bin2hex(random_bytes(4));
        $report->MaHoiDong=null;
        $report->XacNhanGVHD='Đã duyệt';
        $report->TrangThai='Đã phê duyệt';
        $report->save();
        \App\Models\TepHoSoBaoVe::create([
            'MaTep'        => 'TEP_' . bin2hex(random_bytes(4)),
            'MaHoSo'       => $report->MaHoSo,
            'TenTep'       => 'Báo cáo toàn văn.pdf',
            'LoaiTep'      => 'Khóa luận toàn văn',
            'DuongDanFile' => 'hoso/test.pdf',
        ]);
        $lecturers=GiangVien::where('MaGV','!=',$source->deTai->MaGV)->limit(5)->pluck('MaGV');
        $this->assertCount(5,$lecturers);
        $roles=['Chủ tịch','Ủy viên phản biện','Ủy viên thư ký','Ủy viên 1','Ủy viên'];
        $members=[];
        foreach($roles as $i=>$role)$members[]=['MaGV'=>$lecturers[$i],'VaiTro'=>$role];
        $name='Hội đồng kiểm thử vai trò '.bin2hex(random_bytes(3));
        $this->actingAs($this->user())->post(route('truongkhoa.hoidong.store'),[
            'TenHoiDong'=>$name,'MaHocKy'=>$source->deTai->MaHocKy,
            'DotBaoVe'=>'Đợt kiểm thử','NgayBaoVe'=>'2026-10-28',
            'ThoiGianBatDau'=>'2026-10-28T07:30','ThoiGianKetThuc'=>'2026-10-28T17:00',
            'DiaDiem'=>'Phòng kiểm thử','danh_sach_ho_so'=>[$report->MaHoSo],
            'thanh_viens'=>$members,'cong_bo_ngay'=>0,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $council=HoiDong::where('TenHoiDong',$name)->firstOrFail();
        $this->assertSame('Chưa công bố',$council->TrangThaiCongBo);
        $this->assertCount(5,$council->thanhViens);
        $this->assertSame('Ủy viên',$council->thanhViens->firstWhere('MaGV',$lecturers[3])->VaiTro);
        $this->assertSame('Ủy viên',$council->thanhViens->firstWhere('MaGV',$lecturers[4])->VaiTro);
    }

    public function test_unrecognized_role_still_returns_vietnamese_validation_error(): void
    {
        $gv=GiangVien::firstOrFail();
        $response=$this->actingAs($this->user())->post(route('truongkhoa.hoidong.store'),[
            'thanh_viens'=>array_fill(0,3,['MaGV'=>$gv->MaGV,'VaiTro'=>'Ủy viên tùy ý']),
        ]);
        $response->assertRedirect()->assertSessionHasErrors(['thanh_viens.0.VaiTro']);
        $this->assertStringContainsString('Vai trò thành viên', session('errors')->first('thanh_viens.0.VaiTro'));
    }
}
