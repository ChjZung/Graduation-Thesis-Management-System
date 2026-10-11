<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\HocKy;
use App\Models\BoMon;

class AdminGiangVienSemesterFilterTest extends TestCase
{
    /**
     * Admin có thể truy cập trang Quản lý giảng viên.
     */
    public function test_admin_can_access_giangvien_index()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/giangvien');
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Giảng Viên &amp; Phân Bổ Hướng Dẫn', false);
        $response->assertSee('MaHocKy_filter');
    }

    /**
     * Admin có thể lọc giảng viên theo học kỳ.
     */
    public function test_admin_can_filter_giangvien_by_semester()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $hk = HocKy::where('MaHocKy', 'HK2627_2')->first() ?? HocKy::first();

        $response = $this->actingAs($admin)->get('/admin/giangvien?MaHocKy_filter=' . $hk->MaHocKy);
        $response->assertStatus(200);
        $response->assertSee('value="' . $hk->MaHocKy . '" selected', false);
    }

    /**
     * Admin có thể lọc giảng viên theo tình trạng hướng dẫn đề tài.
     */
    public function test_admin_can_filter_giangvien_by_guiding_status()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/giangvien?tinh_trang_hd=co_hd');
        $response->assertStatus(200);

        $responseChua = $this->actingAs($admin)->get('/admin/giangvien?tinh_trang_hd=chua_hd');
        $responseChua->assertStatus(200);
    }

    /**
     * Admin có thể xuất Excel có kèm bộ lọc học kỳ.
     */
    public function test_admin_can_export_giangvien_with_semester_filter()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $hk = HocKy::first();

        $response = $this->actingAs($admin)->get('/admin/giangvien/export?MaHocKy_filter=' . $hk->MaHocKy);
        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
    }

    /**
     * Giao diện có nút Quản Lý Giảng Viên Theo Học Kỳ và Modal 2 tab (Import & Thêm).
     */
    public function test_admin_sees_semester_management_modal_and_tabs()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/giangvien');

        $response->assertStatus(200);
        $response->assertSee('modalQuanLyGVTheoKy');
        $response->assertSee('Quản Lý Giảng Viên Theo Học Kỳ');
        $response->assertSee('tab-import-gv');
        $response->assertSee('tab-them-gv');
        $response->assertSee('Import Danh Sách Giảng Viên');
        $response->assertSee('Thêm Mới Giảng Viên');
    }

    /**
     * A3: Rà soát admin hiển thị chỉ tiêu khớp quy tắc P1, dùng chung ChiTieuService,
     * không còn hiển thị "6/5 đề tài" hay "6/5 hướng dẫn" khi giảng viên có 6 đề tài.
     */
    public function test_a3_admin_giangvien_uses_chitieuservice_and_never_shows_6_over_5()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $maGV = 'GV00000001';
        $maHocKy = 'HK2627_2';

        $initialTopicCount = \App\Models\DeTai::where('MaGV', $maGV)
            ->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành'])
            ->count();
        $expectedTotalTopics = $initialTopicCount + 6;

        // 1. Tạo 6 đề tài đã duyệt cho GV00000001
        $createdTopics = [];
        for ($i = 1; $i <= 6; $i++) {
            $createdTopics[] = \App\Models\DeTai::create([
                'MaDeTai' => "DT_A3_TEST_{$i}",
                'TenDeTai' => "Đề tài A3 test {$i}",
                'MaGV' => $maGV,
                'MaHocKy' => $maHocKy,
                'TrangThai' => 'Đã công bố',
            ]);
        }

        try {
            // 2. Truy cập màn hình /admin/giangvien
            $response = $this->actingAs($admin)->get("/admin/giangvien?search={$maGV}&MaHocKy_filter={$maHocKy}");
            $response->assertStatus(200);

            // TUYỆT ĐỐI không hiển thị "6/5"
            $response->assertDontSee('6/5 đề tài');
            $response->assertDontSee('6/5 hướng dẫn');

            // Hiển thị định mức nhóm theo đúng ChiTieuService (số nhóm sinh viên đã duyệt trên tổng số nhóm chỉ tiêu)
            $stats = \App\Services\ChiTieuService::getChiTieuStats($maGV, $maHocKy);
            $response->assertSee("{$stats['label']} nhóm");

            // 3. Kiểm tra trang chi tiết /admin/giangvien/{id}
            $showRes = $this->actingAs($admin)->get("/admin/giangvien/{$maGV}");
            $showRes->assertStatus(200);
            $showRes->assertDontSee('/5 đề tài');
            $showRes->assertSeeText("{$stats['da_dung']} / {$stats['tong']} nhóm tối đa");
            $showRes->assertSeeText("Đề Tài Đề Xuất ({$expectedTotalTopics})");
        } finally {
            foreach ($createdTopics as $dt) {
                $dt->delete();
            }
        }
    }
}
