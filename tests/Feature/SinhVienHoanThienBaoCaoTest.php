<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\KetQuaSinhVien;
use App\Models\BaoCaoSauBaoVe;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\GiangVien;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SinhVienHoanThienBaoCaoTest extends TestCase
{
    use DatabaseTransactions;

    protected $userSvA;
    protected $svA;
    protected $userSvB;
    protected $svB;
    protected $nhomA;
    protected $nhomB;
    protected $hoSoA;
    protected $hoiDong;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $khoa = Khoa::first() ?? Khoa::create(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa Công nghệ Thông tin']);
        $boMon = BoMon::first() ?? BoMon::create(['MaBoMon' => 'BM_CNPM', 'TenBoMon' => 'Bộ môn CNPM', 'MaKhoa' => $khoa->MaKhoa]);
        $gv = GiangVien::first() ?? GiangVien::create([
            'MaGV' => 'GV_TEST_99',
            'HoTen' => 'Nguyễn Văn Giảng Viên',
            'Email' => 'gvtest@huit.edu.vn',
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // Tạo tài khoản & Sinh viên A
        $this->userSvA = TaiKhoan::create([
            'MaTK' => 'TK_SVA_' . time(),
            'TenDangNhap' => 'sva_' . time(),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT03', // Sinh viên
            'TrangThai' => true,
        ]);

        $this->svA = SinhVien::create([
            'MaSV' => 'SVA_' . time(),
            'HoTen' => 'Sinh Viên A Test',
            'Email' => 'sva' . time() . '@huit.edu.vn',
            'MaTK' => $this->userSvA->MaTK,
            'MaKhoa' => $khoa->MaKhoa,
        ]);

        // Tạo tài khoản & Sinh viên B
        $this->userSvB = TaiKhoan::create([
            'MaTK' => 'TK_SVB_' . time(),
            'TenDangNhap' => 'svb_' . time(),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT03',
            'TrangThai' => true,
        ]);

        $this->svB = SinhVien::create([
            'MaSV' => 'SVB_' . time(),
            'HoTen' => 'Sinh Viên B Test',
            'Email' => 'svb' . time() . '@huit.edu.vn',
            'MaTK' => $this->userSvB->MaTK,
            'MaKhoa' => $khoa->MaKhoa,
        ]);

        // Tạo Hội đồng
        $this->hoiDong = HoiDong::create([
            'MaHoiDong' => 'HD_TEST_' . time(),
            'TenHoiDong' => 'Hội đồng Test Bảo Vệ',
            'MaKhoa' => $khoa->MaKhoa,
            'DiaDiem' => 'Phòng B.304',
            'ThoiGianBatDau' => now()->subDays(2),
            'TrangThaiCongBo' => 'Đã công bố',
            'TrangThai' => 'Đã kết thúc',
        ]);

        // Tạo Đề tài A & Nhóm A
        $deTaiA = DeTai::create([
            'MaDeTai' => 'DT_A_' . time(),
            'TenDeTai' => 'Đề tài Nghiên cứu Khoa học của Nhóm A',
            'MaGV' => $gv->MaGV,
            'MaHocKy' => 'HK2627_1',
            'TrangThai' => 'Đã duyệt',
        ]);

        $this->nhomA = Nhom::create([
            'MaNhom' => 'NHOM_A_' . time(),
            'TenNhom' => 'Nhóm A',
            'MaDeTai' => $deTaiA->MaDeTai,
            'MaTruongNhom' => $this->svA->MaSV,
            'TrangThai' => 'Đã duyệt',
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $this->nhomA->MaNhom,
            'MaSV' => $this->svA->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
        ]);

        // Hồ sơ bảo vệ của nhóm A
        $this->hoSoA = HoSoBaoVe::create([
            'MaHoSo' => 'HS_A_' . time(),
            'MaNhom' => $this->nhomA->MaNhom,
            'MaHoiDong' => $this->hoiDong->MaHoiDong,
            'TrangThaiKetQua' => 'ChuaCongBo',
            'TrangThaiChinhSuaSauBaoVe' => 'ChoSinhVienNop',
            'HanNopBaoCaoSauBaoVe' => now()->addDays(5),
            'YeuCauChinhSuaSauBaoVe' => 'Cần bổ sung biểu đồ kiến trúc và danh mục tài liệu tham khảo.',
        ]);

        // Tạo Đề tài B & Nhóm B
        $deTaiB = DeTai::create([
            'MaDeTai' => 'DT_B_' . time(),
            'TenDeTai' => 'Đề tài Nhóm B',
            'MaGV' => $gv->MaGV,
            'MaHocKy' => 'HK2627_1',
            'TrangThai' => 'Đã duyệt',
        ]);

        $this->nhomB = Nhom::create([
            'MaNhom' => 'NHOM_B_' . time(),
            'TenNhom' => 'Nhóm B',
            'MaDeTai' => $deTaiB->MaDeTai,
            'MaTruongNhom' => $this->svB->MaSV,
            'TrangThai' => 'Đã duyệt',
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $this->nhomB->MaNhom,
            'MaSV' => $this->svB->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
        ]);

        HoSoBaoVe::create([
            'MaHoSo' => 'HS_B_' . time(),
            'MaNhom' => $this->nhomB->MaNhom,
            'MaHoiDong' => $this->hoiDong->MaHoiDong,
            'TrangThaiKetQua' => 'DaCongBo',
            'TrangThaiChinhSuaSauBaoVe' => 'ChoSinhVienNop',
            'HanNopBaoCaoSauBaoVe' => now()->addDays(5),
            'YeuCauChinhSuaSauBaoVe' => 'Yêu cầu nhóm B',
        ]);
    }

    public function test_chua_cong_bo_diem_bi_chan_redirect_ve_ket_qua_kem_thong_bao()
    {
        // Điểm chưa công bố
        $this->hoSoA->update(['TrangThaiKetQua' => 'ChuaCongBo']);
        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $this->svA->MaSV],
            [
                'MaKetQua' => 'KQ_' . mt_rand(10000000, 99999999),
                'TrangThai' => 'Chưa công bố',
                'DiemTongKet' => 8.0,
            ]
        );

        $response = $this->actingAs($this->userSvA)->get(route('sinhvien.ketqua.hoanthien'));

        // Phải bị redirect về trang kết quả kèm lỗi
        $response->assertRedirect(route('sinhvien.ketqua.index'));
        $response->assertSessionHas('error', 'Điểm chưa được công bố, bạn chưa thể hoàn thiện báo cáo.');
    }

    public function test_da_cong_bo_diem_thi_truy_cap_duoc_thanh_cong()
    {
        // Điểm đã công bố
        $this->hoSoA->update(['TrangThaiKetQua' => 'DaCongBo']);
        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $this->svA->MaSV],
            [
                'MaKetQua' => 'KQ_' . mt_rand(10000000, 99999999),
                'MaHoSo' => $this->hoSoA->MaHoSo,
                'TrangThai' => 'Đã công bố',
                'DiemTongKet' => 8.5,
                'DiemChinhThuc' => 8.5,
                'KetQua' => 'Giỏi',
                'NgayCongBo' => now(),
            ]
        );

        $response = $this->actingAs($this->userSvA)->get(route('sinhvien.ketqua.hoanthien'));

        $response->assertStatus(200);
        $response->assertSee('Hoàn Thiện Báo Cáo Sau Bảo Vệ');
        $response->assertSee('8.50');
        $response->assertSee('Giỏi');
    }

    public function test_sinh_vien_khac_khong_nop_duoc_bao_cao_cua_nguoi_khac_chong_idor()
    {
        // Điểm đã công bố
        $this->hoSoA->update(['TrangThaiKetQua' => 'DaCongBo']);

        // Sinh viên B cố tình submit MaHoSo của nhóm A
        $file = UploadedFile::fake()->create('bao_cao.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->userSvB)->post(route('sinhvien.ketqua.nopBaoCaoChinhSua'), [
            'MaHoSo' => $this->hoSoA->MaHoSo,
            'FileBaoCaoChinhSua' => $file,
            'TomTatChinhSua' => 'Chỉnh sửa nội dung đồ án',
        ]);

        // Phải bị chặn 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_nop_file_sai_dinh_dang_bi_tu_choi()
    {
        $this->hoSoA->update(['TrangThaiKetQua' => 'DaCongBo']);
        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $this->svA->MaSV],
            [
                'MaKetQua' => 'KQ_' . mt_rand(10000000, 99999999),
                'TrangThai' => 'Đã công bố',
                'DiemTongKet' => 8.5,
            ]
        );

        // Upload file .txt hoặc .exe
        $file = UploadedFile::fake()->create('virus.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($this->userSvA)->post(route('sinhvien.ketqua.nopBaoCaoChinhSua'), [
            'MaHoSo' => $this->hoSoA->MaHoSo,
            'FileBaoCaoChinhSua' => $file,
            'TomTatChinhSua' => 'Nộp thử file exe',
        ]);

        $response->assertSessionHasErrors('FileBaoCaoChinhSua');
    }

    public function test_nop_sau_han_deadline_bi_tu_choi()
    {
        // Thiết lập quá hạn nộp
        $this->hoSoA->update([
            'TrangThaiKetQua' => 'DaCongBo',
            'HanNopBaoCaoSauBaoVe' => now()->subDay(),
        ]);
        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $this->svA->MaSV],
            [
                'MaKetQua' => 'KQ_' . mt_rand(10000000, 99999999),
                'TrangThai' => 'Đã công bố',
                'DiemTongKet' => 8.5,
            ]
        );

        $file = UploadedFile::fake()->create('bao_cao_hoan_thien.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->userSvA)->post(route('sinhvien.ketqua.nopBaoCaoChinhSua'), [
            'MaHoSo' => $this->hoSoA->MaHoSo,
            'FileBaoCaoChinhSua' => $file,
            'TomTatChinhSua' => 'Nộp lại sau hạn',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_nop_hop_le_tao_ban_ghi_version_moi_va_cap_nhat_trang_thai()
    {
        // Đã công bố, còn hạn
        $this->hoSoA->update([
            'TrangThaiKetQua' => 'DaCongBo',
            'HanNopBaoCaoSauBaoVe' => now()->addDays(3),
        ]);
        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $this->svA->MaSV],
            [
                'MaKetQua' => 'KQ_' . mt_rand(10000000, 99999999),
                'TrangThai' => 'Đã công bố',
                'DiemTongKet' => 8.5,
            ]
        );

        $filePdf = UploadedFile::fake()->create('bao_cao_v1.pdf', 2048, 'application/pdf');
        $fileDocx = UploadedFile::fake()->create('giai_trinh.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->userSvA)->post(route('sinhvien.ketqua.nopBaoCaoChinhSua'), [
            'MaHoSo' => $this->hoSoA->MaHoSo,
            'FileBaoCaoChinhSua' => $filePdf,
            'FileGiaiTrinh' => $fileDocx,
            'TomTatChinhSua' => 'Đã sửa chữa biểu đồ và cập nhật tài liệu tham khảo theo chuẩn IEEE.',
        ]);

        $response->assertRedirect(route('sinhvien.ketqua.hoanthien'));
        $response->assertSessionHas('success');

        // Kiểm tra database có bản ghi mới
        $this->assertDatabaseHas('bao_cao_sau_bao_ve', [
            'MaHoSo' => $this->hoSoA->MaHoSo,
            'LanChinhSua' => 1,
            'TenFile' => 'bao_cao_v1.pdf',
            'NguoiNop' => $this->svA->MaSV,
            'TrangThai' => 'Đã nộp',
        ]);

        // Kiểm tra hồ sơ được cập nhật trạng thái
        $hoSoFresh = $this->hoSoA->fresh();
        $this->assertEquals('DaNop', $hoSoFresh->TrangThaiChinhSuaSauBaoVe);
        $this->assertEquals('DaNopChinhSua', $hoSoFresh->TrangThaiKetQua);
        $this->assertNotNull($hoSoFresh->FileBanChinhSua);
    }
}
