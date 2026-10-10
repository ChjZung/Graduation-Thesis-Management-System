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
}
