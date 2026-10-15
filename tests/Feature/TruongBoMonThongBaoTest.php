<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\ThongBao;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TruongBoMonThongBaoTest extends TestCase
{
    use DatabaseTransactions;

    protected $userTBM;
    protected $userTK;
    protected $thongBao;

    protected function setUp(): void
    {
        parent::setUp();

        // Tìm hoặc tạo tài khoản Trưởng bộ môn
        $this->userTBM = TaiKhoan::where('MaVaiTro', 'VT04')->first();
        if (!$this->userTBM) {
            $this->userTBM = TaiKhoan::create([
                'MaTK'              => 'TBM_TEST',
                'TenDangNhap'       => 'TBM_TEST',
                'MatKhau'           => bcrypt('123456'),
                'MaVaiTro'          => 'VT04',
                'TrangThai'         => true,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
            ]);
        } else {
            $this->userTBM->update([
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
            ]);
        }

        // Tìm hoặc tạo tài khoản Trưởng khoa
        $this->userTK = TaiKhoan::where('MaVaiTro', 'VT05')->first();
        if (!$this->userTK) {
            $this->userTK = TaiKhoan::create([
                'MaTK'              => 'TK_TEST',
                'TenDangNhap'       => 'TK_TEST',
                'MatKhau'           => bcrypt('123456'),
                'MaVaiTro'          => 'VT05',
                'TrangThai'         => true,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
            ]);
        } else {
            $this->userTK->update([
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
            ]);
        }

        $this->thongBao = ThongBao::first();
        if (!$this->thongBao) {
            $this->thongBao = ThongBao::create([
                'MaThongBao'   => 'TB_TEST_TBM',
                'TieuDe'       => 'Thông báo thử nghiệm TBM',
                'NoiDung'      => 'Nội dung thông báo kiểm tra hiển thị cho Trưởng bộ môn.',
                'LoaiThongBao' => 'Kế hoạch',
                'DoiTuongNhan' => 'Tất cả',
                'TrangThai'    => 'ĐÃ GỬI',
                'NgayGui'      => now(),
            ]);
        }
    }

    public function test_truong_bo_mon_xem_duoc_danh_sach_thong_bao()
    {
        $response = $this->actingAs($this->userTBM)->get(route('truongbomon.thongbao.index'));
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Thông Báo');

        $responseCommon = $this->actingAs($this->userTBM)->get(route('thongbao.index'));
        $responseCommon->assertStatus(200);
    }

    public function test_truong_bo_mon_xem_duoc_chi_tiet_thong_bao()
    {
        $response = $this->actingAs($this->userTBM)->get(route('truongbomon.thongbao.show', $this->thongBao->MaThongBao));
        $response->assertStatus(200);
        $response->assertSee($this->thongBao->TieuDe);
    }

    public function test_truong_khoa_xem_duoc_danh_sach_va_chi_tiet_thong_bao()
    {
        $response = $this->actingAs($this->userTK)->get(route('truongkhoa.thongbao.index'));
        $response->assertStatus(200);

        $responseDetail = $this->actingAs($this->userTK)->get(route('truongkhoa.thongbao.show', $this->thongBao->MaThongBao));
        $responseDetail->assertStatus(200);
    }

    public function test_admin_xem_duoc_admin_thongbao_bang_phuong_thuc_get()
    {
        $userAdmin = TaiKhoan::where('MaVaiTro', 'VT01')->first();
        if ($userAdmin) {
            $userAdmin->update([
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
            ]);
            // Test GET /admin/thongbao
            $response = $this->actingAs($userAdmin)->get('/admin/thongbao');
            $response->assertStatus(200);

            // Test GET /admin/thongbao/{id}
            $responseShow = $this->actingAs($userAdmin)->get('/admin/thongbao/' . $this->thongBao->MaThongBao);
            $responseShow->assertStatus(200);

            // Test GET root / redirects properly
            $responseRoot = $this->actingAs($userAdmin)->get('/');
            $responseRoot->assertRedirect(route('admin.dashboard'));
        }
    }
}
