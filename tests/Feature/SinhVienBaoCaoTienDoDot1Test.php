<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\GiangVien;
use App\Models\BaoCaoTienDo;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\PhieuDangKy;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SinhVienBaoCaoTienDoDot1Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $sv;
    protected $nhom;
    protected $deTai;
    protected $moc;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // 1. Setup user & student
        $this->user = TaiKhoan::where('TenDangNhap', '2001210044')->firstOrFail();
        $this->user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->sv = SinhVien::where('MaTK', $this->user->MaTK)->firstOrFail();

        $plan = KeHoachKhoaLuan::first();
        $hocKy = HocKy::where('MaHocKy', $plan?->MaHocKy)->first() ?? HocKy::first();
        $hocPhan = HocPhan::first();
        $gv = GiangVien::first();

        // 2. Setup group & topic
        $this->nhom = Nhom::firstOrCreate(['MaNhom' => 'NHOM_TEST_C1'], [
            'TenNhom' => 'Nhóm Test C1',
            'MaTruongNhom' => $this->sv->MaSV,
            'MaHocKy' => $hocKy->MaHocKy,
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'TrangThai' => 'Đã duyệt',
        ]);

        ThanhVienNhom::where('MaSV', $this->sv->MaSV)->delete();
        ThanhVienNhom::create([
            'MaNhom' => $this->nhom->MaNhom,
            'MaSV' => $this->sv->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        $this->deTai = DeTai::firstOrCreate(['MaDeTai' => 'DT_TEST_C1'], [
            'TenDeTai' => 'Đề tài Test C1',
            'MaHocKy' => $hocKy->MaHocKy,
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'MaGV' => $gv->MaGV,
            'TrangThai' => 'Đã công bố',
            'SoLuongSinhVienToiDa' => 3,
        ]);

        PhieuDangKy::updateOrCreate(['MaDangKy' => 'PDK_TEST_C1'], [
            'MaNhom' => $this->nhom->MaNhom,
            'MaDeTai' => $this->deTai->MaDeTai,
            'TrangThai' => 'Đã duyệt',
            'NgayDangKy' => now(),
        ]);

        // 3. Setup milestone in DB
        $this->moc = MocThoiGianKhoaLuan::updateOrCreate(
            ['MaMoc' => 'MOC_R0JW_BC1'],
            [
                'TenMoc' => 'Báo cáo tiến độ đợt 1',
                'DoiTuongThucHien' => 'Sinh viên',
                'NgayBatDau' => '2026-09-15',
                'NgayKetThuc' => null, // Không giới hạn
                'MoTa' => 'Báo cáo tiến độ đợt 1 [BAO_CAO_TIEN_DO_1]',
                'MakeHoach' => $plan ? $plan->MakeHoach : 'KH_2026_R0JW',
            ]
        );

        // Delete any existing reports for clean test
        BaoCaoTienDo::where('MaDeTai', $this->deTai->MaDeTai)->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_chan_nop_bao_cao_dot_1_truoc_ngay_15_9()
    {
        // Giả lập thời gian trước ngày 15/9 (ví dụ 14/09/2026)
        Carbon::setTestNow('2026-09-14 14:00:00');

        $file = UploadedFile::fake()->create('baocao_dot1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo thử nghiệm trước hạn',
                'FileBaoCao' => $file,
                'NoiDungBaoCao' => 'Nội dung nộp trước ngày 15/9',
            ]);

        // Phải bị chặn với lỗi mốc thời gian
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('BaoCaoTienDo', [
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
        ]);
    }

    public function test_duoc_phep_nop_bao_cao_dot_1_dung_ngay_15_9()
    {
        // Giả lập đúng ngày 15/9/2026 (gồm 15/9)
        Carbon::setTestNow('2026-09-15 08:30:00');

        $file = UploadedFile::fake()->create('baocao_dot1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo đợt 1 ngày 15/9',
                'FileBaoCao' => $file,
                'NoiDungBaoCao' => 'Nộp thành công vào ngày 15/9',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('BaoCaoTienDo', [
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'MaMoc' => 'MOC_R0JW_BC1',
        ]);
    }

    public function test_duoc_phep_nop_bao_cao_dot_1_sau_ngay_15_9_khi_ngay_ket_thuc_la_null()
    {
        // Giả lập sau ngày 15/9 (ví dụ 20/09/2026) và NgayKetThuc = NULL (không giới hạn)
        Carbon::setTestNow('2026-09-20 10:00:00');

        $file = UploadedFile::fake()->create('baocao_dot1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo đợt 1 sau 15/9 không giới hạn',
                'FileBaoCao' => $file,
                'NoiDungBaoCao' => 'Nộp thành công sau 15/9',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('BaoCaoTienDo', [
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
        ]);
    }

    public function test_chan_nop_khi_da_qua_ngay_ket_thuc_neu_co_cau_hinh()
    {
        // Cấu hình hạn kết thúc là ngày 25/09/2026
        $this->moc->update(['NgayKetThuc' => '2026-09-25']);

        // Giả lập thời gian đã quá hạn (ngày 26/09/2026)
        Carbon::setTestNow('2026-09-26 09:00:00');

        $file = UploadedFile::fake()->create('baocao_dot1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo nộp quá hạn',
                'FileBaoCao' => $file,
                'NoiDungBaoCao' => 'Nộp sau ngày kết thúc',
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('BaoCaoTienDo', [
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
        ]);
    }
}
