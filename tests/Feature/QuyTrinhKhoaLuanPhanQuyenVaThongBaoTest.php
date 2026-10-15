<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\ThongBao;
use App\Services\PlanPhaseService;
use Carbon\Carbon;

class QuyTrinhKhoaLuanPhanQuyenVaThongBaoTest extends TestCase
{
    protected $studentUser;
    protected $student;
    protected $lecturerUser;
    protected $lecturer;
    protected $hocKy;
    protected $hocPhan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentUser = TaiKhoan::where('TenDangNhap', '2001210001')->first();
        if ($this->studentUser) {
            $this->studentUser->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            $this->student = SinhVien::where('MaTK', $this->studentUser->MaTK)->first();
        }

        $this->lecturerUser = TaiKhoan::where('TenDangNhap', 'GV00000023')->first() 
            ?? TaiKhoan::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Giảng viên'))->first();
        if ($this->lecturerUser) {
            $this->lecturerUser->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            $this->lecturer = GiangVien::where('MaTK', $this->lecturerUser->MaTK)->first();
        }

        $this->hocKy = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $this->hocPhan = HocPhan::where('MaHocPhan', 'HP_KLCN_CNPM')->first() ?? HocPhan::first();
    }

    /**
     * TEST 1: Danh sách nhóm cùng học phần cùng bộ môn hiển thị đầy đủ, không thiếu nhóm trong CSDL
     */
    public function test_danh_sach_nhom_mo_hien_thi_day_du_nhom_cung_hoc_phan_cung_bo_mon()
    {
        if (!$this->studentUser) {
            $this->markTestSkipped('Không có sinh viên 2001210001 trong DB test.');
        }

        $res = $this->actingAs($this->studentUser)->get(route('sinhvien.nhom.index', [
            'hoc_phan' => 'HP_KLCN_CNPM',
            'hoc_ky'   => 'HK2627_1',
            'tab'      => 'nhom_mo',
        ]));

        $res->assertStatus(200);
        // Đảm bảo các nhóm trong DB (N01, N02, N03) đều hiển thị
        $res->assertSee('Nhóm 2001210001');
        $res->assertSee('Nhóm 2001210002');
    }

    /**
     * TEST 2: Khi trong thời gian tạo nhóm (TAO_NHOM), Giảng viên bị chặn đề xuất đề tài mới
     */
    public function test_thoi_gian_tao_nhom_thi_giang_vien_khong_duoc_de_xuat_de_tai()
    {
        if (!$this->lecturerUser) {
            $this->markTestSkipped('Không có tài khoản giảng viên trong DB test.');
        }

        // Kiểm tra service
        $state = PlanPhaseService::isDeXuatDeTaiOpen($this->hocKy->MaHocKy, '2026-08-10 10:00:00');
        $this->assertFalse($state['is_open']);
        $this->assertStringContainsString('Cổng đề xuất đề tài của Giảng viên đã kết thúc', $state['message']);

        // 1. Giảng viên vào trang create -> bị redirect về index kèm thông báo lỗi
        $resCreate = $this->actingAs($this->lecturerUser)->get(route('giangvien.detai.create'));
        $resCreate->assertRedirect(route('giangvien.detai.index'));
        $resCreate->assertSessionHasErrors();

        // 2. Giảng viên cố tình submit store -> bị từ chối
        $resStore = $this->actingAs($this->lecturerUser)->post(route('giangvien.detai.store'), [
            'TenDeTai'  => 'Đề tài thử nghiệm khi đã khóa cổng đề xuất',
            'MaHocKy'   => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'MoTa'      => 'Đây là mô tả đề tài thử nghiệm với độ dài tối thiểu trên ba mươi ký tự hợp lệ.',
            'YeuCau'    => 'Yêu cầu sinh viên nắm vững kiến thức lập trình cơ bản và nâng cao.',
        ]);
        $resStore->assertSessionHasErrors();
    }

    /**
     * TEST 3: Cả 2 thông báo công bố đề tài và công bố danh sách nhóm đều hiển thị cho Sinh viên và Giảng viên
     */
    public function test_thong_bao_cong_bo_danh_sach_nhom_va_de_tai_hien_thi_day_du()
    {
        if (!$this->studentUser || !$this->lecturerUser) {
            $this->markTestSkipped('Thiếu tài khoản test.');
        }

        // Sinh viên truy cập danh sách thông báo
        $resSV = $this->actingAs($this->studentUser)->get(route('sinhvien.thongbao.index'));
        $resSV->assertStatus(200);
        $resSV->assertSee('THÔNG BÁO CHÍNH THỨC: Danh sách Sinh viên đăng ký đề tài');
        $resSV->assertSee('Công bố Danh mục Đề tài Khóa luận Tốt nghiệp');

        // Giảng viên truy cập danh sách thông báo nhận được
        $resGV = $this->actingAs($this->lecturerUser)->get(route('giangvien.thongbao.index', ['tab' => 'nhan_duoc']));
        $resGV->assertStatus(200);
        $resGV->assertSee('THÔNG BÁO CHÍNH THỨC: Danh sách Sinh viên đăng ký đề tài');
        $resGV->assertSee('Công bố Danh mục Đề tài Khóa luận Tốt nghiệp');
    }
}
