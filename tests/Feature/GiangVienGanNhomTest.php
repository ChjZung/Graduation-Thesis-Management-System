<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\PhieuDangKy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GiangVienGanNhomTest extends TestCase
{
    use DatabaseTransactions;

    protected $userGV;
    protected $gv;
    protected $deTai;
    protected $hocKy;
    protected $hocPhan;
    protected $nhomDu3;
    protected $nhomThieu;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Lecturer
        $this->userGV = TaiKhoan::where('TenDangNhap', 'GV00000001')->firstOrFail();
        $this->userGV->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->gv = GiangVien::where('MaTK', $this->userGV->MaTK)->firstOrFail();

        $this->hocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? HocKy::first();
        $this->hocPhan = HocPhan::first();

        // 2. Published topic owned by GV that has no group assigned
        $this->deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_B3_' . rand(1000, 9999),
            'TenDeTai' => 'Đề tài test B3 gán nhóm',
            'MaGV' => $this->gv->MaGV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đã công bố',
            'SoLuongSinhVienToiDa' => 3,
        ]);

        // 3. Group with < 3 members (e.g. 2 members)
        $sv1 = SinhVien::first();
        $sv2 = SinhVien::skip(1)->first();
        $sv3 = SinhVien::skip(2)->first();
        $sv4 = SinhVien::skip(3)->first();

        $this->nhomThieu = Nhom::create([
            'MaNhom' => 'NHOM_THIEU_' . rand(1000, 9999),
            'TenNhom' => 'Nhóm Thiếu Thành Viên B3',
            'MaTruongNhom' => $sv1->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $this->nhomThieu->MaNhom,
            'MaSV' => $sv1->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $this->nhomThieu->MaNhom,
            'MaSV' => $sv2->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // 4. Group with >= 3 members (3 members)
        $this->nhomDu3 = Nhom::create([
            'MaNhom' => 'NHOM_DU3_' . rand(1000, 9999),
            'TenNhom' => 'Nhóm Đủ 3 Thành Viên B3',
            'MaTruongNhom' => $sv3->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $this->nhomDu3->MaNhom,
            'MaSV' => $sv3->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $this->nhomDu3->MaNhom,
            'MaSV' => $sv4->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);
        $sv5 = SinhVien::skip(4)->first();
        ThanhVienNhom::create([
            'MaNhom' => $this->nhomDu3->MaNhom,
            'MaSV' => $sv5->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);
    }

    public function test_nhom_khong_du_3_sinh_vien_khong_hien_o_giangvien_detai()
    {
        $response = $this->actingAs($this->userGV)
            ->get(route('giangvien.detai.index'));

        $response->assertStatus(200);

        // Nhóm không đủ 3 SV KHÔNG được có trong danh sách nhóm chưa có đề tài truyền vào view
        $nhomsChuaCoDeTai = $response->viewData('nhomsChuaCoDeTai');
        $this->assertFalse($nhomsChuaCoDeTai->contains('MaNhom', $this->nhomThieu->MaNhom));

        // Nhóm đủ 3 SV PHẢI có trong danh sách
        $this->assertTrue($nhomsChuaCoDeTai->contains('MaNhom', $this->nhomDu3->MaNhom));

        // Trong HTML của trang, nhóm thiếu không được xuất hiện dưới dạng option trong modal gán nhóm
        $response->assertDontSee($this->nhomThieu->TenNhom);
        $response->assertSee($this->nhomDu3->TenNhom);
    }

    public function test_chan_gan_nhom_khong_du_3_sinh_vien_server_side()
    {
        $response = $this->actingAs($this->userGV)
            ->post(route('giangvien.detai.ganNhom', $this->deTai->MaDeTai), [
                'MaNhom' => $this->nhomThieu->MaNhom,
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhieuDangKy', [
            'MaNhom' => $this->nhomThieu->MaNhom,
            'MaDeTai' => $this->deTai->MaDeTai,
        ]);
    }

    public function test_gan_nhom_du_3_sinh_vien_thanh_cong()
    {
        $response = $this->actingAs($this->userGV)
            ->post(route('giangvien.detai.ganNhom', $this->deTai->MaDeTai), [
                'MaNhom' => $this->nhomDu3->MaNhom,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('PhieuDangKy', [
            'MaNhom' => $this->nhomDu3->MaNhom,
            'MaDeTai' => $this->deTai->MaDeTai,
            'TrangThai' => 'Đã duyệt',
        ]);
    }

    public function test_chan_gan_nhom_khi_de_tai_chua_duoc_cong_bo()
    {
        $this->deTai->update(['TrangThai' => 'Chờ duyệt cấp Bộ môn']);

        $response = $this->actingAs($this->userGV)
            ->post(route('giangvien.detai.ganNhom', $this->deTai->MaDeTai), [
                'MaNhom' => $this->nhomDu3->MaNhom,
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhieuDangKy', [
            'MaNhom' => $this->nhomDu3->MaNhom,
            'MaDeTai' => $this->deTai->MaDeTai,
        ]);
    }

    public function test_de_tai_chua_cong_bo_khong_hien_nut_gan_nhom_o_view()
    {
        $this->deTai->update(['TrangThai' => 'Chờ duyệt cấp Bộ môn']);

        $response = $this->actingAs($this->userGV)
            ->get(route('giangvien.detai.index'));

        $response->assertStatus(200);
        $response->assertDontSee('#modalGanNhom' . $this->deTai->MaDeTai);
    }
}
