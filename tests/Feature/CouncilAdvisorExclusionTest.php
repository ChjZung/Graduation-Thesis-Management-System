<?php
namespace Tests\Feature;

use App\Models\{TaiKhoan, GiangVien, HoiDong, HoSoBaoVe, ThanhVienHoiDong};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CouncilAdvisorExclusionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = TaiKhoan::where('TenDangNhap', 'like', 'TK_CNTT_%')->firstOrFail();
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
    }

    private function report(): HoSoBaoVe
    {
        $source = HoSoBaoVe::whereHas('nhom.deTai.giangVien')->firstOrFail();
        $report = $source->replicate();
        $report->forceFill(['MaHoSo' => 'TEST_ADV_'.bin2hex(random_bytes(4)), 'MaHoiDong' => null, 'XacNhanGVHD' => 'Đã duyệt', 'TrangThai' => 'Đã phê duyệt'])->save();
        $report->phienBans()->create(['PhienBan' => 999, 'DuongDanBaoCao' => 'hoso/fixture.pdf', 'NgayNop' => now()]);
        return $report->load('nhom.deTai.giangVien');
    }

    public function test_server_rejects_supervisor_in_every_council_role(): void
    {
        $report = $this->report();
        $advisor = $report->nhom->deTai->MaGV;
        $others = GiangVien::where('MaGV', '!=', $advisor)->limit(4)->pluck('MaGV');
        $roles = ['Chủ tịch', 'Thư ký', 'Phản biện', 'Ủy viên'];
        $before = HoiDong::count();
        foreach ($roles as $slot => $role) {
            $members = collect($roles)->map(fn ($name, $i) => ['MaGV' => $slot === $i ? $advisor : $others[$i], 'VaiTro' => $name])->all();
            $this->post(route('truongkhoa.hoidong.store'), [
                'TenHoiDong' => 'Kiểm thử xung đột GVHD', 'MaHocKy' => $report->nhom->deTai->MaHocKy,
                'DotBaoVe' => 'Đợt kiểm thử', 'NgayBaoVe' => '2041-01-10', 'DiaDiem' => 'Phòng kiểm thử',
                'ThoiGianBatDau' => '2041-01-10 08:00', 'ThoiGianKetThuc' => '2041-01-10 10:00',
                'danh_sach_ho_so' => [$report->MaHoSo], 'thanh_viens' => $members,
            ])->assertRedirect()->assertSessionHasErrors('thanh_viens');
            $this->assertStringContainsString('Giảng viên hướng dẫn', session('errors')->first('thanh_viens'));
        }
        $this->assertSame($before, HoiDong::count());
        $this->assertNull($report->fresh()->MaHoiDong);
    }

    public function test_cannot_add_advisor_to_council_with_their_topic(): void
    {
        $report = $this->report();
        $council = HoiDong::firstOrFail()->replicate();
        $council->forceFill(['MaHoiDong' => 'HD_ADV_'.bin2hex(random_bytes(4)), 'TrangThaiCongBo' => 'Chưa công bố'])->save();
        $report->update(['MaHoiDong' => $council->MaHoiDong]);
        $this->post(route('truongkhoa.hoidong.themThanhVien', $council->MaHoiDong), ['MaGV' => $report->nhom->deTai->MaGV, 'VaiTro' => 'Ủy viên'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame(0, $council->thanhViens()->count());
    }

    public function test_cannot_add_topic_to_its_supervisors_council(): void
    {
        $fixture = $this->report();
        // Assignment endpoint selects the first dossier of a group, not a dossier ID.
        $report = HoSoBaoVe::where('MaNhom', $fixture->MaNhom)->firstOrFail();
        $report->update(['MaHoiDong' => null, 'XacNhanGVHD' => 'Đã duyệt', 'TrangThai' => 'Đã phê duyệt']);
        $report->phienBans()->create(['PhienBan' => 998, 'DuongDanBaoCao' => 'hoso/fixture.pdf', 'NgayNop' => now()]);
        $council = HoiDong::firstOrFail()->replicate();
        $council->forceFill(['MaHoiDong' => 'HD_ADV_'.bin2hex(random_bytes(4)), 'TrangThaiCongBo' => 'Chưa công bố'])->save();
        ThanhVienHoiDong::create(['MaHoiDong' => $council->MaHoiDong, 'MaGV' => $report->nhom->deTai->MaGV, 'VaiTro' => 'Thư ký']);
        $this->post(route('truongkhoa.hoidong.phanCongNhom', $council->MaHoiDong), ['MaNhom' => $report->MaNhom])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNull($report->fresh()->MaHoiDong);
        $this->assertStringContainsString('Giảng viên hướng dẫn', session('error'));
    }
}
