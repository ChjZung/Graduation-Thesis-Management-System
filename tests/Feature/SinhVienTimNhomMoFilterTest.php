<?php

namespace Tests\Feature;

use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\PhieuDangKy;
use App\Models\HocKy;
use App\Models\HocPhan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SinhVienTimNhomMoFilterTest extends TestCase
{
    protected function getLoggedInStudent(): SinhVien
    {
        $sv = SinhVien::with('taiKhoan', 'lop.nganh')->first();
        $this->assertNotNull($sv, "Phải có ít nhất 1 sinh viên trong CSDL");
        $this->actingAs($sv->taiKhoan);
        return $sv;
    }

    public function test_giao_dien_bo_loc_dong_bo_va_khong_con_icon_kinh_lup_thua_hay_emoji()
    {
        $sv = $this->getLoggedInStudent();

        $response = $this->get(route('sinhvien.nhom.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Đồng bộ khối card bộ lọc với dangky
        $this->assertStringContainsString('Bộ Lọc Nhóm Khóa Luận:', $content);
        $this->assertStringContainsString('filter-item-box', $content);
        $this->assertStringContainsString('filter-item-label', $content);
        $this->assertStringContainsString('filter-item-select', $content);
        $this->assertStringContainsString('filter-item-static', $content);

        // 2. Ô input MSSV thuần không có khối addon kính lúp bên trái
        $this->assertStringContainsString('name="q"', $content);
        $this->assertStringContainsString('placeholder="Nhập MSSV hoặc tên nhóm..."', $content);
        $this->assertStringNotContainsString('🔍', $content);

        // 3. Icon lời mời gia nhập và không còn chuông emoji vàng
        $this->assertStringNotContainsString('fa-bell text-warning', $content);
        $this->assertStringContainsString('fa-regular fa-envelope-open', $content);

        // 4. Có các select lọc trạng thái đề tài và chỗ trống
        $this->assertStringContainsString('name="TrangThaiDeTai"', $content);
        $this->assertStringContainsString('name="ChoTrong"', $content);
    }

    public function test_loc_theo_trang_thai_de_tai()
    {
        $sv = $this->getLoggedInStudent();

        // Lọc nhóm Đã chọn đề tài
        $resDaChon = $this->get(route('sinhvien.nhom.index', ['TrangThaiDeTai' => 'da_chon', 'tab' => 'tab-timnhom']));
        $resDaChon->assertStatus(200);
        $nhomsDaChon = $resDaChon->viewData('nhomsOpen');
        foreach ($nhomsDaChon as $n) {
            $hasTopic = $n->phieuDangKys->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->isNotEmpty() || !empty($n->MaDeTai);
            $this->assertTrue($hasTopic, "Nhóm {$n->MaNhom} phải có đề tài");
        }

        // Lọc nhóm Chưa chọn đề tài
        $resChuaChon = $this->get(route('sinhvien.nhom.index', ['TrangThaiDeTai' => 'chua_chon', 'tab' => 'tab-timnhom']));
        $resChuaChon->assertStatus(200);
        $nhomsChuaChon = $resChuaChon->viewData('nhomsOpen');
        foreach ($nhomsChuaChon as $n) {
            $hasTopic = $n->phieuDangKys->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->isNotEmpty() || !empty($n->MaDeTai);
            $this->assertFalse($hasTopic, "Nhóm {$n->MaNhom} không được có đề tài");
        }
    }

    public function test_loc_theo_so_cho_trong()
    {
        $sv = $this->getLoggedInStudent();

        // Lọc nhóm Còn chỗ trống (< 3 thành viên chính thức)
        $resConCho = $this->get(route('sinhvien.nhom.index', ['ChoTrong' => 'con_cho', 'tab' => 'tab-timnhom']));
        $resConCho->assertStatus(200);
        $nhomsConCho = $resConCho->viewData('nhomsOpen');
        foreach ($nhomsConCho as $n) {
            $count = $n->thanhViens->where('TrangThai', 'da_tham_gia')->count();
            $this->assertLessThan(3, $count, "Nhóm {$n->MaNhom} phải còn chỗ trống (<3)");
        }

        // Lọc nhóm Đã đủ thành viên (>= 3 thành viên)
        $resDaDu = $this->get(route('sinhvien.nhom.index', ['ChoTrong' => 'da_du', 'tab' => 'tab-timnhom']));
        $resDaDu->assertStatus(200);
        $nhomsDaDu = $resDaDu->viewData('nhomsOpen');
        foreach ($nhomsDaDu as $n) {
            $count = $n->thanhViens->where('TrangThai', 'da_tham_gia')->count();
            $this->assertGreaterThanOrEqual(3, $count, "Nhóm {$n->MaNhom} phải đã đủ thành viên (>=3)");
        }
    }

    public function test_hien_thi_thong_bao_khi_khong_tim_thay_nhom_phu_hop()
    {
        $sv = $this->getLoggedInStudent();

        $res = $this->get(route('sinhvien.nhom.index', [
            'q' => 'TU_KHOA_KHONG_TON_TAI_TRONG_CSDL_99999',
            'tab' => 'tab-timnhom'
        ]));

        $res->assertStatus(200);
        $res->assertSee('Không tìm thấy nhóm phù hợp');
        $this->assertEquals(0, $res->viewData('nhomsOpen')->count());
    }
}
