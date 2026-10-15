<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\HocPhan;
use App\Models\HocKy;
use App\Models\DeTai;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminDuyetDeTaiExportAndFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected $userAdmin;
    protected $userSV;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userAdmin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        if (!$this->userAdmin) {
            $this->userAdmin = TaiKhoan::create([
                'MaTK' => 'TK_ADMIN_TEST',
                'TenDangNhap' => 'admin_test',
                'MatKhau' => bcrypt('Admin@123456'),
                'MaVaiTro' => 'VT01',
                'TrangThai' => true,
                'TrangThaiMatKhau' => 'ACTIVE',
                'BatBuocDoiMatKhau' => false,
            ]);
        }

        $this->userSV = TaiKhoan::where('MaVaiTro', 'VT03')->first();
    }

    public function test_tai_khoan_sinh_vien_initial_chuyen_huong_doi_mat_khau_lan_dau()
    {
        $this->userSV->update([
            'TrangThaiMatKhau'  => 'INITIAL',
            'BatBuocDoiMatKhau' => true,
        ]);

        $response = $this->actingAs($this->userSV)->get(route('sinhvien.dashboard'));
        $response->assertRedirect(route('password.setup'));
    }

    public function test_sinh_vien_get_ma_bo_mon_chuan_khong_bi_loi_bm_prefix()
    {
        $sv = SinhVien::where('MaTK', $this->userSV->MaTK)->first();
        $this->assertNotNull($sv);

        $boMon = $sv->getMaBoMon();
        $this->assertNotEmpty($boMon);
        // Không chứa tiền tố BM_ nếu bảng BoMon không có tiền tố BM_
        $this->assertStringNotContainsString('BM_BM_', $boMon);
    }

    public function test_admin_loc_de_tai_theo_trang_thai_va_hoc_phan()
    {
        $this->userAdmin->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        $response = $this->actingAs($this->userAdmin)->get(route('admin.duyet_detai.index', [
            'TrangThai' => 'da_cong_bo',
            'tab'       => 'quanly',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Trạng Thái');
        $response->assertSee('Xuất Đề Tài Đã Công Bố');
    }

    public function test_admin_xuat_de_tai_da_cong_bo_chuan_csv_co_utf8_bom()
    {
        $this->userAdmin->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        $response = $this->actingAs($this->userAdmin)->get(route('admin.duyet_detai.export', [
            'TrangThai' => 'da_cong_bo',
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8'));
        
        $content = $response->streamedContent();
        // Kiểm tra UTF-8 BOM \xEF\xBB\xBF
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        // Kiểm tra các cột chuẩn
        $this->assertStringContainsString('Mã Đề Tài', $content);
        $this->assertStringContainsString('Tên Đề Tài', $content);
        $this->assertStringContainsString('Giảng Viên Hướng Dẫn', $content);
        $this->assertStringContainsString('Tình Trạng Chỗ', $content);
    }
}
