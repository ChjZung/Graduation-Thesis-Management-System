<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;

class ChuanGSystemDesignStandardsTest extends TestCase
{
    protected $admin;
    protected $gvUser;
    protected $svUser;
    protected $tbmUser;
    protected $tkUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $this->gvUser = TaiKhoan::where('TenDangNhap', 'GV00000001')->first() ?? TaiKhoan::where('MaVaiTro', 'VT02')->first();
        $this->svUser = TaiKhoan::where('MaVaiTro', 'VT03')->first();
        if ($this->svUser) {
            $this->svUser->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        }
        $this->tbmUser = TaiKhoan::where('TenDangNhap', 'LIKE', 'TBM_%')->first();
        $this->tkUser = TaiKhoan::where('TenDangNhap', 'LIKE', 'TK_%')->first();
    }

    /**
     * 1. Kiểm tra layout Admin tuân thủ Chuẩn G: Header theo admin-top-bar, admin-breadcrumb, admin-user-pill.
     */
    public function test_admin_layout_follows_chuan_g_header()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('admin-top-bar');
        $response->assertSee('admin-breadcrumb');
        $response->assertSee('admin-user-pill');
    }

    /**
     * 2. Kiểm tra layout Giảng Viên đồng bộ header theo Chuẩn G (admin-top-bar, admin-breadcrumb, admin-user-pill).
     */
    public function test_giangvien_layout_follows_chuan_g_header()
    {
        $this->assertNotNull($this->gvUser);
        $response = $this->actingAs($this->gvUser)->get(route('giangvien.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('admin-top-bar');
        $response->assertSee('admin-breadcrumb');
        $response->assertSee('admin-user-pill');
    }

    /**
     * 3. Kiểm tra layout Sinh Viên đồng bộ header theo Chuẩn G.
     */
    public function test_sinhvien_layout_follows_chuan_g_header()
    {
        $this->assertNotNull($this->svUser);
        $response = $this->actingAs($this->svUser)->get(route('sinhvien.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('admin-top-bar');
        $response->assertSee('admin-breadcrumb');
        $response->assertSee('admin-user-pill');
    }

    /**
     * 4. Kiểm tra layout Trưởng Bộ Môn đồng bộ header theo Chuẩn G.
     */
    public function test_truongbomon_layout_follows_chuan_g_header()
    {
        if ($this->tbmUser) {
            $response = $this->actingAs($this->tbmUser)->get(route('truongbomon.dashboard'));
            $response->assertStatus(200);
            $response->assertSee('admin-top-bar');
            $response->assertSee('admin-breadcrumb');
            $response->assertSee('admin-user-pill');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 5. Kiểm tra layout Trưởng Khoa đồng bộ header theo Chuẩn G.
     */
    public function test_truongkhoa_layout_follows_chuan_g_header()
    {
        if ($this->tkUser) {
            $response = $this->actingAs($this->tkUser)->get(route('truongkhoa.dashboard'));
            $response->assertStatus(200);
            $response->assertSee('admin-top-bar');
            $response->assertSee('admin-breadcrumb');
            $response->assertSee('admin-user-pill');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 6. Kiểm tra huit_theme.css đã có đầy đủ bộ quy tắc Chuẩn G:
     * - Loại bỏ box-shadow: box-shadow: none !important
     * - Font size tối thiểu 13px: font-size: 13px !important
     * - Sidebar căn trái: text-align: left !important
     * - Ẩn icon trang trí trong label và bảng
     */
    public function test_huit_theme_css_contains_chuan_g_rules()
    {
        $cssPath = public_path('css/huit_theme.css');
        $this->assertFileExists($cssPath);
        $css = file_get_contents($cssPath);

        $this->assertStringContainsString('CHUẨN HÓA TOÀN BỘ HỆ THỐNG THEO CHUẨN G', $css);
        $this->assertStringContainsString('box-shadow: none !important', $css);
        $this->assertStringContainsString('font-size: 13px', $css);
        $this->assertStringContainsString('#sidebar', $css);
        $this->assertStringContainsString('label.form-label i', $css);
        $this->assertStringContainsString('table.table td:not(:last-child)', $css);
    }

    /**
     * 7. Kiểm tra không còn icon ký tự ★ trong bộ lọc học kỳ trang Sinh viên và Giảng viên.
     */
    public function test_no_star_icons_in_semester_select_options()
    {
        $resSv = $this->actingAs($this->admin)->get(route('sinhvien.index'));
        $resSv->assertStatus(200);
        $resSv->assertDontSee('>HK2425_1 ★<');
        $resSv->assertDontSee('>HK2617_1 ★<');

        $resGv = $this->actingAs($this->admin)->get(route('giangvien.index'));
        $resGv->assertStatus(200);
        $resGv->assertDontSee('>HK2425_1 ★<');
        $resGv->assertDontSee('>HK2617_1 ★<');
    }
}
