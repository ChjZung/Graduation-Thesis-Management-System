<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\PhienBanHoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\Khoa;
use App\Models\BoMon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GiangVienXacNhanBaoVeReportValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected $userGvA;
    protected $gvA;
    protected $userGvB;
    protected $gvB;
    protected $userTk;
    protected $nhom;
    protected $deTai;
    protected $hoSoChuaCoBaoCao;
    protected $hoSoCoBaoCao;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $khoa = Khoa::first() ?? Khoa::create(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa CNTT']);
        $boMon = BoMon::first() ?? BoMon::create(['MaBoMon' => 'BM_CNPM', 'TenBoMon' => 'Bộ môn CNPM', 'MaKhoa' => $khoa->MaKhoa]);

        // Giảng viên A (GVHD)
        $this->userGvA = TaiKhoan::create([
            'MaTK'        => 'TK_GVA_' . time(),
            'TenDangNhap' => 'gva_' . time(),
            'MatKhau'     => bcrypt('123456'),
            'MaVaiTro'    => 'VT02', // Giảng viên
            'TrangThai'   => true,
        ]);

        $this->gvA = GiangVien::create([
            'MaGV'    => 'GVA_' . time(),
            'HoTen'   => 'Giảng Viên A Test',
            'Email'   => 'gva' . time() . '@huit.edu.vn',
            'MaTK'    => $this->userGvA->MaTK,
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // Giảng viên B (Khác)
        $this->userGvB = TaiKhoan::create([
            'MaTK'        => 'TK_GVB_' . time(),
            'TenDangNhap' => 'gvb_' . time(),
            'MatKhau'     => bcrypt('123456'),
            'MaVaiTro'    => 'VT02',
            'TrangThai'   => true,
        ]);

        $this->gvB = GiangVien::create([
            'MaGV'    => 'GVB_' . time(),
            'HoTen'   => 'Giảng Viên B Test',
            'Email'   => 'gvb' . time() . '@huit.edu.vn',
            'MaTK'    => $this->userGvB->MaTK,
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // Trưởng Khoa
        $this->userTk = TaiKhoan::where('TenDangNhap', 'like', 'TK_CNTT_%')->first() 
            ?? TaiKhoan::where('MaVaiTro', 'VT05')->first()
            ?? $this->userGvA;

        // Sinh viên & Nhóm
        $userSv = TaiKhoan::create([
            'MaTK'        => 'TK_SV_' . time(),
            'TenDangNhap' => 'sv_' . time(),
            'MatKhau'     => bcrypt('123456'),
            'MaVaiTro'    => 'VT03',
            'TrangThai'   => true,
        ]);

        $sv = SinhVien::create([
            'MaSV'    => 'SV_' . time(),
            'HoTen'   => 'Sinh Viên Test',
            'Email'   => 'sv' . time() . '@huit.edu.vn',
            'MaTK'    => $userSv->MaTK,
            'MaKhoa'  => $khoa->MaKhoa,
        ]);

        $this->deTai = DeTai::create([
            'MaDeTai'  => 'DT_TEST_' . time(),
            'TenDeTai' => 'Đề tài Kiểm thử Xác Nhận Bảo Vệ',
            'MaGV'     => $this->gvA->MaGV,
            'MaHocKy'  => 'HK2627_1',
            'TrangThai'=> 'Đã duyệt',
        ]);

        $this->nhom = Nhom::create([
            'MaNhom'       => 'NHOM_TEST_' . time(),
            'TenNhom'      => 'Nhóm Test Báo Cáo',
            'MaDeTai'      => $this->deTai->MaDeTai,
            'MaTruongNhom' => $sv->MaSV,
            'TrangThai'    => 'Đã duyệt',
        ]);

        ThanhVienNhom::create([
            'MaNhom'    => $this->nhom->MaNhom,
            'MaSV'      => $sv->MaSV,
            'VaiTro'    => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
        ]);

        // Hồ sơ 1: CHƯA CÓ BÁO CÁO TOÀN VĂN
        $this->hoSoChuaCoBaoCao = HoSoBaoVe::create([
            'MaHoSo'      => 'HS_NO_' . mt_rand(10000000, 99999999),
            'MaNhom'      => $this->nhom->MaNhom,
            'MaDeTai'     => $this->deTai->MaDeTai,
            'XacNhanGVHD' => 'Chờ xác nhận',
            'TrangThai'   => 'Chờ GVHD kiểm tra',
        ]);

        // Hồ sơ 2: ĐÃ CÓ BÁO CÁO TOÀN VĂN (Tệp thật trên Fake Storage)
        $file = UploadedFile::fake()->create('bao_cao_toan_van.pdf', 2048, 'application/pdf');
        $storedPath = $file->store("hoso/{$this->nhom->MaNhom}", 'public');

        $this->hoSoCoBaoCao = HoSoBaoVe::create([
            'MaHoSo'      => 'HS_YES_' . mt_rand(1000000, 9999999),
            'MaNhom'      => $this->nhom->MaNhom,
            'MaDeTai'     => $this->deTai->MaDeTai,
            'XacNhanGVHD' => 'Chờ xác nhận',
            'TrangThai'   => 'Chờ GVHD kiểm tra',
        ]);

        PhienBanHoSoBaoVe::create([
            'MaHoSo'         => $this->hoSoCoBaoCao->MaHoSo,
            'PhienBan'       => '1.0',
            'TenFileBaoCao'  => 'bao_cao_toan_van.pdf',
            'DuongDanBaoCao' => $storedPath,
            'NgayNop'        => now(),
            'NguoiNop'       => $sv->MaSV,
            'TrangThai'      => 'Chờ GVHD kiểm tra',
        ]);

        TepHoSoBaoVe::create([
            'MaTep'        => 'TEP_' . time() . '_' . mt_rand(100, 999),
            'MaHoSo'       => $this->hoSoCoBaoCao->MaHoSo,
            'TenTep'       => 'bao_cao_toan_van.pdf',
            'LoaiTep'      => 'Khóa luận toàn văn',
            'DuongDanFile' => $storedPath,
            'NgayNop'      => now(),
            'TrangThai'    => 'Đã nộp',
        ]);
    }

    /**
     * 1. Duyệt nhóm chưa có báo cáo toàn văn bị từ chối
     */
    public function test_duyet_nhom_chua_co_bao_cao_bi_tu_choi()
    {
        // GVHD A gọi duyệt nhóm chưa có báo cáo
        $response = $this->actingAs($this->userGvA)->post(
            route('giangvien.xacnhan_baove.xacNhan', $this->hoSoChuaCoBaoCao->MaHoSo),
            [
                'HanhDong' => 'dong_y',
                'NhanXet'  => 'Đồng ý duyệt bảo vệ',
            ]
        );

        // Phải bị từ chối và redirect kèm flash error
        $response->assertSessionHas('error', 'Nhóm chưa nộp báo cáo toàn văn, không thể duyệt bảo vệ.');

        // Database không được đổi trạng thái sang Đã duyệt
        $this->hoSoChuaCoBaoCao->refresh();
        $this->assertNotEquals('Đã duyệt', $this->hoSoChuaCoBaoCao->XacNhanGVHD);
        $this->assertNotEquals('Đã phê duyệt', $this->hoSoChuaCoBaoCao->TrangThai);
    }

    /**
     * 2. Gọi trực tiếp API/Request JSON duyệt nhóm thiếu báo cáo bị chặn với mã 422
     */
    public function test_goi_thang_request_json_duyet_nhom_thieu_bao_cao_tra_ve_422()
    {
        $response = $this->actingAs($this->userGvA)->postJson(
            route('giangvien.xacnhan_baove.xacNhan', $this->hoSoChuaCoBaoCao->MaHoSo),
            [
                'HanhDong' => 'dong_y',
                'NhanXet'  => 'Cố tình duyệt khi thiếu báo cáo',
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Nhóm chưa nộp báo cáo toàn văn, không thể duyệt bảo vệ.'
        ]);
    }

    /**
     * 3. Duyệt nhóm có báo cáo toàn văn thành công
     */
    public function test_duyet_nhom_co_bao_cao_thanh_cong()
    {
        $response = $this->actingAs($this->userGvA)->post(
            route('giangvien.xacnhan_baove.xacNhan', $this->hoSoCoBaoCao->MaHoSo),
            [
                'HanhDong' => 'dong_y',
                'NhanXet'  => 'Báo cáo tốt, đồng ý cho bảo vệ.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->hoSoCoBaoCao->refresh();
        $this->assertEquals('Đã duyệt', $this->hoSoCoBaoCao->XacNhanGVHD);
        $this->assertEquals('Đã phê duyệt', $this->hoSoCoBaoCao->TrangThai);
    }

    /**
     * 4. Giảng viên khác không duyệt được hồ sơ không thuộc mình (403 Forbidden)
     */
    public function test_giang_vien_khac_khong_duyet_duoc_ho_so_chong_idor()
    {
        // Giảng viên B cố tình duyệt hồ sơ của Giảng viên A
        $response = $this->actingAs($this->userGvB)->post(
            route('giangvien.xacnhan_baove.xacNhan', $this->hoSoCoBaoCao->MaHoSo),
            [
                'HanhDong' => 'dong_y',
                'NhanXet'  => 'GV khác cố tình duyệt',
            ]
        );

        $response->assertStatus(403);
    }

    /**
     * 5. Xếp nhóm thiếu báo cáo vào Hội đồng bị từ chối ở server
     */
    public function test_xep_nhom_thieu_bao_cao_vao_hoi_dong_bi_tu_choi()
    {
        $hoiDong = HoiDong::create([
            'MaHoiDong'       => 'HD_TEST_' . time(),
            'TenHoiDong'      => 'Hội đồng Kiểm thử',
            'MaKhoa'          => 'CNTT',
            'MaHocKy'         => 'HK2627_1',
            'DotBaoVe'        => 'Đợt 1',
            'NgayBaoVe'       => now()->toDateString(),
            'ThoiGianBatDau'  => now()->addDay(),
            'ThoiGianKetThuc' => now()->addDay()->addHours(4),
            'DiaDiem'         => 'Phòng B304',
            'TrangThaiCongBo' => 'Chưa công bố',
            'TrangThai'       => 'Đang chuẩn bị',
        ]);

        // Cố tình thêm nhóm thiếu báo cáo vào Hội đồng qua action phanCongNhom
        $response = $this->actingAs($this->userTk)->post(
            route('truongkhoa.hoidong.phanCongNhom', $hoiDong->MaHoiDong),
            [
                'MaNhom' => $this->nhom->MaNhom,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('chưa nộp báo cáo toàn văn', session('error'));

        // Kiểm tra hồ sơ không bị gán mã Hội đồng
        $this->hoSoChuaCoBaoCao->refresh();
        $this->assertNull($this->hoSoChuaCoBaoCao->MaHoiDong);
    }

    /**
     * 6. Tạo Hội đồng mới kèm nhóm thiếu báo cáo toàn văn bị từ chối khi lưu (Store)
     */
    public function test_tao_hoi_dong_kem_nhom_thieu_bao_cao_bi_tu_choi()
    {
        $lecturers = GiangVien::limit(4)->pluck('MaGV');
        $members = [
            ['MaGV' => $lecturers[0], 'VaiTro' => 'Chủ tịch'],
            ['MaGV' => $lecturers[1], 'VaiTro' => 'Ủy viên phản biện'],
            ['MaGV' => $lecturers[2], 'VaiTro' => 'Ủy viên thư ký'],
        ];

        $response = $this->actingAs($this->userTk)->post(route('truongkhoa.hoidong.store'), [
            'TenHoiDong'      => 'Hội đồng Kiểm thử Wizard ' . time(),
            'MaHocKy'         => 'HK2627_1',
            'DotBaoVe'        => 'Đợt 1',
            'NgayBaoVe'       => now()->addDay()->toDateString(),
            'ThoiGianBatDau'  => now()->addDay()->format('Y-m-d\T08:00'),
            'ThoiGianKetThuc' => now()->addDay()->format('Y-m-d\T11:30'),
            'DiaDiem'         => 'Phòng B.301',
            'danh_sach_ho_so' => [$this->hoSoChuaCoBaoCao->MaHoSo],
            'thanh_viens'     => $members,
            'cong_bo_ngay'    => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('danh_sach_ho_so');
        $this->assertStringContainsString('chưa có báo cáo toàn văn', session('errors')->first('danh_sach_ho_so'));
    }

    /**
     * 7. Command audit hoạt động chính xác với cờ --dry-run và khi chạy thật
     */
    public function test_command_audit_xu_ly_dung_du_lieu_sai()
    {
        // Giả lập 1 hồ sơ bị sai: Đã duyệt nhưng thiếu báo cáo
        $hoSoLoi = HoSoBaoVe::create([
            'MaHoSo'      => 'HS_ERR_' . mt_rand(1000000, 9999999),
            'MaNhom'      => $this->nhom->MaNhom,
            'MaDeTai'     => $this->deTai->MaDeTai,
            'XacNhanGVHD' => 'Đã duyệt',
            'TrangThai'   => 'Đã phê duyệt',
        ]);

        // Chạy với cờ --dry-run
        $this->artisan('defense:audit-reports', ['--dry-run' => true])
            ->assertExitCode(0);

        // CSDL không bị đổi khi dry-run
        $hoSoLoi->refresh();
        $this->assertEquals('Đã duyệt', $hoSoLoi->XacNhanGVHD);

        // Chạy thật
        $this->artisan('defense:audit-reports')
            ->assertExitCode(0);

        // CSDL đã được chuyển về 'Chờ xác nhận' và 'Chờ GVHD kiểm tra'
        $hoSoLoi->refresh();
        $this->assertEquals('Chờ xác nhận', $hoSoLoi->XacNhanGVHD);
        $this->assertEquals('Chờ GVHD kiểm tra', $hoSoLoi->TrangThai);
        $this->assertStringContainsString('[AUTO_AUDIT_RESET', $hoSoLoi->GhiChu);
    }
}
