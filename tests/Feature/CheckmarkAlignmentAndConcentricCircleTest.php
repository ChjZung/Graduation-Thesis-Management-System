<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DotNopBaoCao;
use App\Models\GiaiDoanBaoCao;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\BaoCaoTienDo;
use App\Models\DeTai;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\Nhom;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckmarkAlignmentAndConcentricCircleTest extends TestCase
{
    use DatabaseTransactions;

    protected $studentUser;
    protected $lecturerUser;

    protected function setUp(): void
    {
        parent::setUp();

        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $this->studentUser = TaiKhoan::where('MaVaiTro', 'VT01')->first()
            ?? TaiKhoan::whereHas('sinhVien')->first();

        $this->lecturerUser = TaiKhoan::where('MaVaiTro', 'VT02')->first()
            ?? TaiKhoan::whereHas('giangVien')->first();
    }

    public function test_css_huit_theme_chua_cac_lop_can_giua_va_vong_tron_dong_tam()
    {
        $cssContent = file_get_contents(public_path('css/huit_theme.css'));

        // 1. Kiểm tra class vòng tròn đồng tâm
        $this->assertStringContainsString('.concentric-circle-outer', $cssContent);
        $this->assertStringContainsString('72px', $cssContent);
        $this->assertStringContainsString('.concentric-circle-inner', $cssContent);
        $this->assertStringContainsString('44px', $cssContent);

        // 2. Kiểm tra class icon check chuẩn
        $this->assertStringContainsString('.icon-check', $cssContent);
        $this->assertStringContainsString('line-height: 1', $cssContent);
        $this->assertStringContainsString('vertical-align: middle', $cssContent);

        // 3. Kiểm tra class badge và pill check
        $this->assertStringContainsString('.badge-check', $cssContent);
        $this->assertStringContainsString('.pill-check-btn', $cssContent);
        $this->assertStringContainsString('gap: 6px', $cssContent);

        // 4. Kiểm tra icon khi nav-pills active chuyển màu trắng đồng bộ
        $this->assertStringContainsString('.nav-pills .nav-link.active .icon-check', $cssContent);
    }

    public function test_view_sinhvien_show_dot_render_vong_tron_dong_tam_va_icon_check_chuan()
    {
        if ($this->studentUser) {
            $this->actingAs($this->studentUser);
        }

        $dotNop = DotNopBaoCao::first() ?? new DotNopBaoCao([
            'MaDotNop' => 'DOT_TEST_01',
            'TenDotNop' => 'Đợt 1: Báo cáo phân tích yêu cầu',
            'HanNop' => now()->addDays(5),
            'TrangThai' => 'Mo',
        ]);

        $pbPassed = new KhoiBaoCaoTienDo([
            'MaKhoi' => 'KB_TEST_01',
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo tuần 1 hoàn thiện',
            'TrangThai' => 'Đạt',
            'NgayNop' => now(),
            'PhanTramHoanThanh' => 100,
        ]);

        $html = view('sinhvien.baocao.show_dot', [
            'dotNop' => $dotNop,
            'phienBans' => collect([$pbPassed]),
            'latestPhienBan' => $pbPassed,
            'nhom' => new Nhom(['TenNhom' => 'Nhóm Test', 'MaNhom' => 'N01']),
            'isLeader' => true,
        ])->render();

        // 1. Tuyệt đối không còn ký tự unicode '✓'
        $this->assertStringNotContainsString('✓', $html);

        // 2. Có vòng tròn đồng tâm outer 72px và inner 44px
        $this->assertStringContainsString('concentric-circle-outer', $html);
        $this->assertStringContainsString('concentric-circle-inner', $html);

        // 3. Có icon check chuẩn bên trong vòng tròn và tiêu đề
        $this->assertStringContainsString('Đợt Báo Cáo Đã Hoàn Thành', $html);
        $this->assertStringContainsString('fa-solid fa-check icon-check', $html);

        // 4. Pill phiên bản có class pill-check-btn và gap 6px
        $this->assertStringContainsString('pill-check-btn', $html);
        $this->assertStringContainsString('gap: 6px', $html);

        // 5. Badge GVHD duyệt Đạt có badge-check
        $this->assertStringContainsString('badge-check', $html);
        $this->assertStringContainsString('GVHD đã duyệt Đạt', $html);
        $this->assertStringContainsString('Đã nộp 1 phiên bản', $html);
    }

    public function test_view_giangvien_show_dot_khong_con_unicode_check_va_dong_bo_badge()
    {
        if ($this->lecturerUser) {
            $this->actingAs($this->lecturerUser);
        }

        $dotNop = DotNopBaoCao::first() ?? new DotNopBaoCao([
            'MaDotNop' => 'DOT_TEST_01',
            'TenDotNop' => 'Đợt 1',
            'HanNop' => now()->addDays(5),
            'TrangThai' => 'Mo',
        ]);

        $pbPassed = new KhoiBaoCaoTienDo([
            'MaKhoi' => 'KB_TEST_01',
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo tuần 1',
            'TrangThai' => 'Đạt',
            'NgayNop' => now(),
            'PhanTramHoanThanh' => 100,
        ]);

        $html = view('giangvien.baocao.show_dot', [
            'dotNop' => $dotNop,
            'phienBans' => collect([$pbPassed]),
            'latestPhienBan' => $pbPassed,
            'nhom' => new Nhom(['TenNhom' => 'Nhóm Test', 'MaNhom' => 'N01']),
        ])->render();

        // 1. Không chứa ký tự unicode '✓'
        $this->assertStringNotContainsString('✓', $html);

        // 2. Chứa badge-check và pill-check-btn
        $this->assertStringContainsString('badge-check', $html);
        $this->assertStringContainsString('pill-check-btn', $html);
        $this->assertStringContainsString('Đã duyệt Đạt', $html);
    }

    public function test_view_giangvien_show_khong_con_unicode_check_hoac_emoji()
    {
        if ($this->lecturerUser) {
            $this->actingAs($this->lecturerUser);
        }

        $nhom = Nhom::first() ?? new Nhom(['TenNhom' => 'Nhóm Test', 'MaNhom' => 'N01']);
        $baoCao = BaoCaoTienDo::first() ?? new BaoCaoTienDo(['LanBaoCao' => 1, 'TrangThai' => 'Đạt']);

        $html = view('giangvien.baocao.show', [
            'nhom' => $nhom,
            'baoCao' => $baoCao,
            'truongNhom' => null,
            'thanhViens' => collect(),
            'giaiDoans' => collect(),
            'baoCaos' => collect([$baoCao]),
            'deTai' => new DeTai(['TenDeTai' => 'Đề tài Test']),
            'sinhViens' => collect(),
            'currentStage' => 1,
            'baoCaoHienTai' => $baoCao,
            'canReview' => false,
            'tomTat' => null,
        ])->render();

        // Không chứa ký tự unicode '✓' hoặc emoji 🔄
        $this->assertStringNotContainsString('✓', $html);
        $this->assertStringNotContainsString('🔄', $html);
        $this->assertStringContainsString('badge-check', $html);
        $this->assertStringContainsString('Đạt yêu cầu', $html);
    }
}
