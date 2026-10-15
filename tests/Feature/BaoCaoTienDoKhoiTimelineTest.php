<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\PhieuDangKy;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BaoCaoTienDoKhoiTimelineTest extends TestCase
{
    use DatabaseTransactions;

    protected $userSV;
    protected $sv;
    protected $userGV;
    protected $gv;
    protected $nhom;
    protected $deTai;
    protected $moc;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Giả lập thời gian hợp lệ sau 15/09/2026 cho C1
        Carbon::setTestNow('2026-09-20 09:00:00');

        // 1. Setup Student
        $this->userSV = TaiKhoan::where('TenDangNhap', '2001210044')->firstOrFail();
        $this->userSV->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->sv = SinhVien::where('MaTK', $this->userSV->MaTK)->firstOrFail();

        // 2. Setup Lecturer
        $this->gv = GiangVien::firstOrFail();
        $this->userGV = TaiKhoan::where('MaTK', $this->gv->MaTK)->firstOrFail();
        $this->userGV->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        $plan = KeHoachKhoaLuan::first();
        $hocKy = HocKy::where('MaHocKy', $plan?->MaHocKy)->first() ?? HocKy::first();
        $hocPhan = HocPhan::first();

        // 3. Setup Group & Topic
        $this->nhom = Nhom::firstOrCreate(['MaNhom' => 'NHOM_TEST_C2'], [
            'TenNhom' => 'Nhóm Test C2',
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

        $this->deTai = DeTai::firstOrCreate(['MaDeTai' => 'DT_TEST_C2'], [
            'TenDeTai' => 'Đề tài Test C2 Khối Bất Biến',
            'MaHocKy' => $hocKy->MaHocKy,
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'MaGV' => $this->gv->MaGV,
            'TrangThai' => 'Đã công bố',
            'SoLuongSinhVienToiDa' => 3,
        ]);

        PhieuDangKy::updateOrCreate(['MaDangKy' => 'PDK_TEST_C2'], [
            'MaNhom' => $this->nhom->MaNhom,
            'MaDeTai' => $this->deTai->MaDeTai,
            'TrangThai' => 'Đã duyệt',
            'NgayDangKy' => now(),
        ]);

        // 4. Setup Milestone C1
        $this->moc = MocThoiGianKhoaLuan::updateOrCreate(
            ['MaMoc' => 'MOC_R0JW_BC1'],
            [
                'TenMoc' => 'Báo cáo tiến độ đợt 1',
                'DoiTuongThucHien' => 'Sinh viên',
                'NgayBatDau' => '2026-09-15',
                'NgayKetThuc' => '2026-12-31',
                'MoTa' => 'Báo cáo tiến độ đợt 1 [BAO_CAO_TIEN_DO_1]',
                'MakeHoach' => $plan ? $plan->MakeHoach : 'KH_2026_R0JW',
            ]
        );

        // Clean any old reports
        KhoiBaoCaoTienDo::where('MaDeTai', $this->deTai->MaDeTai)->delete();
        BaoCaoTienDo::where('MaDeTai', $this->deTai->MaDeTai)->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_nop_lan_dau_tao_khoi_1_bat_bien()
    {
        $file = UploadedFile::fake()->create('baocao_v1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->userSV)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo GĐ 1 Lần 1',
                'FileBaoCao' => $file,
                'NoiDungBaoCao' => 'Nội dung nộp lần đầu',
                'LinkCode' => 'https://github.com/test/repo',
            ]);

        $response->assertSessionHas('success');

        // Kiểm tra BaoCaoTienDo
        $baoCao = BaoCaoTienDo::where('MaDeTai', $this->deTai->MaDeTai)->where('LanBaoCao', 1)->first();
        $this->assertNotNull($baoCao);
        $this->assertEquals('Chờ duyệt', $baoCao->TrangThai);

        // Kiểm tra KhoiBaoCaoTienDo có đúng 1 khối
        $khois = KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->get();
        $this->assertCount(1, $khois);

        $khoi1 = $khois->first();
        $this->assertEquals(1, $khoi1->LanNop);
        $this->assertEquals('Chờ duyệt', $khoi1->TrangThai);
        $this->assertEquals('baocao_v1.pdf', $khoi1->TenFile);
        $this->assertEquals('https://github.com/test/repo', $khoi1->LinkCode);
    }

    public function test_giang_vien_yeu_cau_chinh_sua_cap_nhat_khoi_1()
    {
        // 1. Tạo bản nộp ban đầu
        $baoCao = BaoCaoTienDo::create([
            'MaBaoCao' => 'BC_TEST_C2',
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'TieuDe' => 'Báo cáo GĐ 1 Lần 1',
            'NoiDungBaoCao' => 'Nộp bài lần 1',
            'TenFile' => 'v1.pdf',
            'DuongDanFile' => 'baocao/v1.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-20',
        ]);

        $khoi1 = KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_1',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo GĐ 1 Lần 1',
            'NoiDungBaoCao' => 'Nộp bài lần 1',
            'TenFile' => 'v1.pdf',
            'DuongDanFile' => 'baocao/v1.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-20 09:00:00',
        ]);

        // 2. Giảng viên đánh giá "Yêu cầu nộp lại"
        $response = $this->actingAs($this->userGV)
            ->post(route('giangvien.baocao.nhanxet', $baoCao->MaBaoCao), [
                'LoaiNhanXet' => 'Yêu cầu nộp lại',
                'NoiDung' => 'Cần bổ sung thiết kế sơ đồ CSDL và phân rã chức năng.',
            ]);

        $response->assertSessionHas('success');

        // Khối 1 phải được cập nhật nhận xét và trạng thái
        $khoi1->refresh();
        $this->assertEquals('Yêu cầu nộp lại', $khoi1->TrangThai);
        $this->assertEquals('Cần bổ sung thiết kế sơ đồ CSDL và phân rã chức năng.', $khoi1->NhanXetGV);
        $this->assertNotNull($khoi1->NgayNhanXet);

        $baoCao->refresh();
        $this->assertEquals('Yêu cầu nộp lại', $baoCao->TrangThai);
    }

    public function test_sinh_vien_nop_lai_tao_khoi_2_bat_bien_va_giu_nguyen_khoi_1()
    {
        // 1. Tạo bản nộp ban đầu đã bị yêu cầu nộp lại
        $baoCao = BaoCaoTienDo::create([
            'MaBaoCao' => 'BC_TEST_C2',
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'TieuDe' => 'Báo cáo GĐ 1 Lần 1',
            'NoiDungBaoCao' => 'Nộp bài lần 1',
            'TenFile' => 'v1.pdf',
            'DuongDanFile' => 'baocao/v1.pdf',
            'TrangThai' => 'Yêu cầu nộp lại',
            'NhanXet' => 'Cần bổ sung sơ đồ ERD',
            'NgayNop' => '2026-09-20',
        ]);

        $khoi1 = KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_1',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo GĐ 1 Lần 1',
            'NoiDungBaoCao' => 'Nộp bài lần 1',
            'TenFile' => 'v1.pdf',
            'DuongDanFile' => 'baocao/v1.pdf',
            'TrangThai' => 'Yêu cầu nộp lại',
            'NhanXetGV' => 'Cần bổ sung sơ đồ ERD',
            'NgayNop' => '2026-09-20 09:00:00',
            'NgayNhanXet' => '2026-09-21 10:00:00',
        ]);

        // Giả lập ngày sinh viên nộp lại (22/09/2026)
        Carbon::setTestNow('2026-09-22 14:00:00');
        $file2 = UploadedFile::fake()->create('baocao_v2_chinh_sua.pdf', 1024, 'application/pdf');

        // 2. Sinh viên nộp lại
        $response = $this->actingAs($this->userSV)
            ->post(route('sinhvien.baocao.store'), [
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo GĐ 1 Lần 2 đã bổ sung ERD',
                'FileBaoCao' => $file2,
                'NoiDungBaoCao' => 'Em đã bổ sung sơ đồ ERD chi tiết theo góp ý của Thầy.',
                'LinkCode' => 'https://github.com/test/repo-v2',
            ]);

        $response->assertSessionHas('success');

        // 3. Kiểm tra tính bất biến của Khối 1: KHÔNG ĐƯỢC THAY ĐỔI
        $khoi1->refresh();
        $this->assertEquals(1, $khoi1->LanNop);
        $this->assertEquals('v1.pdf', $khoi1->TenFile);
        $this->assertEquals('Yêu cầu nộp lại', $khoi1->TrangThai);
        $this->assertEquals('Cần bổ sung sơ đồ ERD', $khoi1->NhanXetGV);

        // 4. Kiểm tra Khối 2: ĐÃ ĐƯỢC TẠO MỚI
        $khois = KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->orderBy('LanNop')->get();
        $this->assertCount(2, $khois);

        $khoi2 = $khois->last();
        $this->assertEquals(2, $khoi2->LanNop);
        $this->assertEquals('Chờ duyệt', $khoi2->TrangThai);
        $this->assertEquals('baocao_v2_chinh_sua.pdf', $khoi2->TenFile);
        $this->assertEquals('https://github.com/test/repo-v2', $khoi2->LinkCode);
        $this->assertNull($khoi2->NhanXetGV);

        // 5. Kiểm tra trạng thái hiện tại của BaoCaoTienDo chuyển về Chờ duyệt
        $baoCao->refresh();
        $this->assertEquals('Chờ duyệt', $baoCao->TrangThai);
        $this->assertEquals('baocao_v2_chinh_sua.pdf', $baoCao->TenFile);
    }

    public function test_giang_vien_danh_gia_dat_cho_khoi_2_va_timeline_giu_nguyen()
    {
        // Setup 2 khối: Khối 1 (Yêu cầu nộp lại) và Khối 2 (Chờ duyệt)
        $baoCao = BaoCaoTienDo::create([
            'MaBaoCao' => 'BC_TEST_C2',
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'TieuDe' => 'Báo cáo GĐ 1 Lần 2',
            'NoiDungBaoCao' => 'Đã sửa theo yêu cầu',
            'TenFile' => 'v2.pdf',
            'DuongDanFile' => 'baocao/v2.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-22',
        ]);

        $khoi1 = KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_1',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo Lần 1',
            'TenFile' => 'v1.pdf',
            'TrangThai' => 'Yêu cầu nộp lại',
            'NhanXetGV' => 'Yêu cầu sửa',
            'NgayNop' => '2026-09-20 09:00:00',
        ]);

        $khoi2 = KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_2',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 2,
            'TieuDe' => 'Báo cáo Lần 2',
            'TenFile' => 'v2.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-22 14:00:00',
        ]);

        // Giảng viên đánh giá "Đạt" cho lần nộp 2
        $response = $this->actingAs($this->userGV)
            ->post(route('giangvien.baocao.nhanxet', $baoCao->MaBaoCao), [
                'LoaiNhanXet' => 'Đạt',
                'NoiDung' => 'Bài làm lần 2 rất tốt, hoàn thành đầy đủ yêu cầu.',
            ]);

        $response->assertSessionHas('success');

        // Khối 1 vẫn giữ nguyên nhận xét và trạng thái cũ
        $khoi1->refresh();
        $this->assertEquals('Yêu cầu nộp lại', $khoi1->TrangThai);
        $this->assertEquals('Yêu cầu sửa', $khoi1->NhanXetGV);

        // Khối 2 được cập nhật trạng thái "Đạt"
        $khoi2->refresh();
        $this->assertEquals('Đạt', $khoi2->TrangThai);
        $this->assertEquals('Bài làm lần 2 rất tốt, hoàn thành đầy đủ yêu cầu.', $khoi2->NhanXetGV);

        $baoCao->refresh();
        $this->assertEquals('Đạt', $baoCao->TrangThai);
    }

    public function test_timeline_hien_thi_dung_tren_view_sinh_vien_va_giang_vien()
    {
        $baoCao = BaoCaoTienDo::create([
            'MaBaoCao' => 'BC_TEST_C2',
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'TieuDe' => 'Báo cáo GĐ 1',
            'NoiDungBaoCao' => 'Nội dung',
            'TenFile' => 'v2.pdf',
            'DuongDanFile' => 'baocao/v2.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-22',
        ]);

        KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_1',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 1,
            'TieuDe' => 'Khối 1',
            'TenFile' => 'file_lan_1.pdf',
            'TrangThai' => 'Yêu cầu nộp lại',
            'NhanXetGV' => 'Nhận xét khối 1 của thầy',
            'NgayNop' => '2026-09-20 09:00:00',
        ]);

        KhoiBaoCaoTienDo::create([
            'MaKhoi' => 'KHOI_BC_TEST_C2_2',
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $this->deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 2,
            'TieuDe' => 'Khối 2',
            'TenFile' => 'file_lan_2.pdf',
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => '2026-09-22 14:00:00',
        ]);

        // 1. Kiểm tra view Sinh viên
        $respSV = $this->actingAs($this->userSV)->get(route('sinhvien.baocao.index'));
        $respSV->assertStatus(200);
        $respSV->assertSee('Lịch Sử Các Lần Nộp (Timeline)');
        $respSV->assertSee('file_lan_1.pdf');
        $respSV->assertSee('Nhận xét khối 1 của thầy');
        $respSV->assertSee('file_lan_2.pdf');

        // 2. Kiểm tra view Giảng viên
        $respGV = $this->actingAs($this->userGV)->get(route('giangvien.baocao.show', $baoCao->MaBaoCao));
        $respGV->assertStatus(200);
        $respGV->assertSee('Lịch Sử Các Khối Nộp Bài');
        $respGV->assertSee('file_lan_1.pdf');
        $respGV->assertSee('file_lan_2.pdf');
    }
}
