<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\PhieuDangKy;
use App\Models\ChiTieuHuongDan;
use App\Services\ChiTieuService;
use Carbon\Carbon;

class ChiTieuHuongDanTest extends TestCase
{
    protected $tbmCnpm;
    protected $tkCntt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tbmCnpm = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->first();
        $this->tkCntt = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();

        $this->assertNotNull($this->tbmCnpm);
        $this->assertNotNull($this->tkCntt);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Quy tắc 1: TBM và TK ĐƯỢC PHÉP duyệt nhiều đề tài hơn số chỉ tiêu của giảng viên
     * (Không bị chặn ở bước duyệt đề tài).
     */
    public function test_tbm_va_tk_duoc_duyet_vuot_chi_tieu_de_tai()
    {
        // 1. Cấu hình GV00000001 chỉ có chỉ tiêu = 1 nhóm
        $maHocKy = 'HK2627_2';
        ChiTieuHuongDan::updateOrCreate(
            ['MaGV' => 'GV00000001', 'MaHocKy' => $maHocKy],
            ['MaChiTieu' => 'CT_TEST_P1', 'SoNhomToiDa' => 1]
        );

        $this->assertEquals(1, ChiTieuService::getChiTieuTong('GV00000001', $maHocKy));

        // 2. Tạo 2 đề tài mới của GV00000001 ở trạng thái Chờ duyệt cấp Bộ môn
        $dt1 = DeTai::create([
            'MaDeTai' => 'DT_TEST_OVER_1',
            'TenDeTai' => 'Đề tài test vượt chỉ tiêu 1',
            'MaGV' => 'GV00000001',
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
        ]);

        $dt2 = DeTai::create([
            'MaDeTai' => 'DT_TEST_OVER_2',
            'TenDeTai' => 'Đề tài test vượt chỉ tiêu 2',
            'MaGV' => 'GV00000001',
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
        ]);

        try {
            // TBM duyệt đề tài 1
            $res1 = $this->actingAs($this->tbmCnpm)
                ->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$dt1->MaDeTai}/duyet");
            $res1->assertSessionHas('success');
            $this->assertEquals('Chờ duyệt cấp Khoa', $dt1->fresh()->TrangThai);

            // TBM tiếp tục duyệt đề tài 2 (vượt quá chỉ tiêu 1) -> Vẫn thành công
            $res2 = $this->actingAs($this->tbmCnpm)
                ->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$dt2->MaDeTai}/duyet");
            $res2->assertSessionHas('success');
            $this->assertEquals('Chờ duyệt cấp Khoa', $dt2->fresh()->TrangThai);

            // TK duyệt đề tài 1 -> Thành công
            $resTk1 = $this->actingAs($this->tkCntt)
                ->post("/truongkhoa/duyet-detai/{$dt1->MaDeTai}/duyet");
            $resTk1->assertSessionHas('success');
            $this->assertEquals('Đã công bố', $dt1->fresh()->TrangThai);

            // TK duyệt đề tài 2 (vượt chỉ tiêu) -> Vẫn thành công
            $resTk2 = $this->actingAs($this->tkCntt)
                ->post("/truongkhoa/duyet-detai/{$dt2->MaDeTai}/duyet");
            $resTk2->assertSessionHas('success');
            $this->assertEquals('Đã công bố', $dt2->fresh()->TrangThai);
        } finally {
            $dt1->delete();
            $dt2->delete();
            ChiTieuHuongDan::where('MaChiTieu', 'CT_TEST_P1')->delete();
        }
    }

    /**
     * Quy tắc 2 & 3: Tính "đã dùng chỉ tiêu" dựa trên số nhóm đăng ký thành công (TrangThai = 'Đã duyệt'),
     * KHÔNG tính đề tài chỉ mới được duyệt, bị từ chối hay bản nháp.
     */
    public function test_tinh_chi_tieu_da_dung_chinh_xac()
    {
        $maGV = 'GV00000007'; // Giảng viên thuộc BM_CNPM
        $maHocKy = 'HK2627_2';

        // Lấy số chỉ tiêu đã dùng ban đầu của GV00000007
        $initialDaDung = ChiTieuService::getChiTieuDaDung($maGV, $maHocKy);

        // Tạo 3 đề tài ở các trạng thái khác nhau
        $dtDuyet = DeTai::create([
            'MaDeTai' => 'DT_TEST_COUNT_DUYET',
            'TenDeTai' => 'Đề tài đã duyệt chưa có nhóm',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Đã công bố',
        ]);

        $dtTuChoi = DeTai::create([
            'MaDeTai' => 'DT_TEST_COUNT_TUCHOI',
            'TenDeTai' => 'Đề tài bị từ chối',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Từ chối',
        ]);

        $dtDraft = DeTai::create([
            'MaDeTai' => 'DT_TEST_COUNT_DRAFT',
            'TenDeTai' => 'Đề tài bản nháp',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Bản nháp',
        ]);

        // Đề tài mới được duyệt, bị từ chối hay nháp KHÔNG làm tăng số chỉ tiêu đã dùng
        $this->assertEquals($initialDaDung, ChiTieuService::getChiTieuDaDung($maGV, $maHocKy));

        $dtCoNhom = DeTai::create([
            'MaDeTai' => 'DT_TEST_COUNT_NHOM',
            'TenDeTai' => 'Đề tài có nhóm đăng ký',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Đã công bố',
        ]);

        // Tạo nhóm và đăng ký thành công cho $dtCoNhom
        $nhom = Nhom::create([
            'MaNhom' => 'NHOM_TEST_CT',
            'TenNhom' => 'Nhóm Test Chỉ Tiêu',
            'TrangThai' => 'Đang hoạt động',
            'MaHocKy' => $maHocKy,
        ]);

        $pdk = PhieuDangKy::create([
            'MaDangKy' => 'PDK_TEST_CT_1',
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $dtCoNhom->MaDeTai,
            'TrangThai' => 'Đã duyệt',
            'NgayDangKy' => now(),
            'NgayDuyet' => now(),
        ]);

        try {
            // Đã dùng chỉ tiêu phải tăng đúng 1 khi có phiếu đăng ký 'Đã duyệt'
            $daDung = ChiTieuService::getChiTieuDaDung($maGV, $maHocKy);
            $this->assertEquals($initialDaDung + 1, $daDung);

            // Kiểm tra stats
            $stats = ChiTieuService::getChiTieuStats($maGV, $maHocKy);
            $this->assertEquals(5, $stats['tong']); // Mặc định 5 khi chưa cấu hình
            $this->assertEquals($initialDaDung + 1, $stats['da_dung']);
        } finally {
            $pdk->delete();
            $nhom->delete();
            $dtDuyet->delete();
            $dtTuChoi->delete();
            $dtDraft->delete();
            $dtCoNhom->delete();
        }
    }

    /**
     * Quy tắc 4: Sinh viên đăng ký bị chặn khi Giảng viên hết chỉ tiêu,
     * và thông báo lỗi rõ ràng.
     */
    public function test_sinh_vien_dang_ky_bi_chan_khi_gv_het_chi_tieu()
    {
        // Giả lập thời gian trong đợt đăng ký chính thức
        Carbon::setTestNow('2026-08-11 10:00:00');

        $maGV = 'GV00000001';
        $maHocKy = 'HK2627_2';

        // 1. Cấu hình chỉ tiêu của GV = 1 nhóm
        ChiTieuHuongDan::updateOrCreate(
            ['MaGV' => $maGV, 'MaHocKy' => $maHocKy],
            ['MaChiTieu' => 'CT_TEST_FULL', 'SoNhomToiDa' => 1]
        );

        // 2. Tạo đề tài 1 đã có nhóm đăng ký thành công (dùng hết 1/1 chỉ tiêu)
        $dtFull = DeTai::create([
            'MaDeTai' => 'DT_TEST_FULL_1',
            'TenDeTai' => 'Đề tài GV đã đầy chỉ tiêu 1',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'SoLuongSinhVienToiDa' => 3,
            'TrangThai' => 'Đã công bố',
        ]);

        $nhomFull = Nhom::create([
            'MaNhom' => 'NHOM_FULL_1',
            'TenNhom' => 'Nhóm Chiếm Chỉ Tiêu 1',
            'TrangThai' => 'Đang hoạt động',
            'MaHocKy' => $maHocKy,
        ]);

        $pdkFull = PhieuDangKy::create([
            'MaDangKy' => 'PDK_FULL_1',
            'MaNhom' => $nhomFull->MaNhom,
            'MaDeTai' => $dtFull->MaDeTai,
            'TrangThai' => 'Đã duyệt',
            'NgayDangKy' => now(),
            'NgayDuyet' => now(),
        ]);

        // Đề tài 2 cũng của GV này nhưng còn trống
        $dtTrong = DeTai::create([
            'MaDeTai' => 'DT_TEST_TRONG_2',
            'TenDeTai' => 'Đề tài GV đã đầy chỉ tiêu 2',
            'MaGV' => $maGV,
            'MaHocKy' => $maHocKy,
            'SoLuongSinhVienToiDa' => 3,
            'TrangThai' => 'Đã công bố',
        ]);

        // 3. Chuẩn bị nhóm sinh viên mới đủ 3 thành viên muốn đăng ký $dtTrong
        $sinhVienUser = TaiKhoan::where('TenDangNhap', '2001210001')->first();
        $this->assertNotNull($sinhVienUser);

        $sv1 = SinhVien::where('MaTK', $sinhVienUser->MaTK)->first();

        // Tạo nhóm mới do $sv1 làm trưởng nhóm và 3 thành viên
        $nhomMoi = Nhom::create([
            'MaNhom' => 'NHOM_MOI_TEST',
            'TenNhom' => 'Nhóm Đăng Ký Mới',
            'MaTruongNhom' => $sv1->MaSV,
            'TrangThai' => 'Đang hoạt động',
            'MaHocKy' => $maHocKy,
        ]);

        $tv1 = ThanhVienNhom::create([
            'MaNhom' => $nhomMoi->MaNhom,
            'MaSV' => $sv1->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        $tv2 = ThanhVienNhom::create([
            'MaNhom' => $nhomMoi->MaNhom,
            'MaSV' => '2001210002',
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        $tv3 = ThanhVienNhom::create([
            'MaNhom' => $nhomMoi->MaNhom,
            'MaSV' => '2001210003',
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        $origTrangThaiMK = $sinhVienUser->TrangThaiMatKhau;
        $origBatBuocDoiMK = $sinhVienUser->BatBuocDoiMatKhau;
        $sinhVienUser->update([
            'TrangThaiMatKhau' => 'ACTIVE',
            'BatBuocDoiMatKhau' => 0,
        ]);

        try {
            // Sinh viên gửi request đăng ký $dtTrong (của GV đã hết chỉ tiêu)
            $response = $this->actingAs($sinhVienUser->fresh())
                ->from('/sinhvien/dangky')
                ->post('/sinhvien/dangky', [
                    'MaDeTai' => $dtTrong->MaDeTai,
                ]);

            // Phải bị lỗi và redirect back
            $response->assertSessionHasErrors();

            // Kiểm tra session error có thông báo rõ ràng về hết chỉ tiêu
            $errors = session('errors')->all();
            $hasChiTieuError = false;
            foreach ($errors as $error) {
                if (str_contains($error, 'đã nhận đủ chỉ tiêu hướng dẫn') || str_contains($error, 'Hết chỉ tiêu')) {
                    $hasChiTieuError = true;
                    break;
                }
            }
            $this->assertTrue($hasChiTieuError, 'Hệ thống phải thông báo lỗi hết chỉ tiêu cho người đăng ký.');

            // Xác nhận không tạo bản ghi PhieuDangKy mới nào cho nhóm mới
            $this->assertDatabaseMissing('PhieuDangKy', [
                'MaNhom' => $nhomMoi->MaNhom,
                'MaDeTai' => $dtTrong->MaDeTai,
            ]);
        } finally {
            $sinhVienUser->update([
                'TrangThaiMatKhau' => $origTrangThaiMK,
                'BatBuocDoiMatKhau' => $origBatBuocDoiMK,
            ]);
            $tv1->delete();
            $tv2->delete();
            $tv3->delete();
            $nhomMoi->delete();
            $pdkFull->delete();
            $nhomFull->delete();
            $dtFull->delete();
            $dtTrong->delete();
            ChiTieuHuongDan::where('MaChiTieu', 'CT_TEST_FULL')->delete();
        }
    }

    /**
     * P4: Giáo vụ / Admin truy cập màn hình chỉ tiêu hướng dẫn thành công
     */
    public function test_giao_vu_truy_cap_man_hinh_chi_tieu_thanh_cong()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/chi-tieu-huong-dan');
        $response->assertStatus(200);
        $response->assertViewIs('admin.chi_tieu_huong_dan.index');
        $response->assertViewHasAll(['giangviens', 'kpiStats', 'hocKys', 'selectedHocKy']);
    }

    /**
     * P4: Role khác (Sinh viên, Giảng viên thông thường) bị chặn 403
     */
    public function test_role_khac_bi_chan_khi_truy_cap_man_hinh_chi_tieu()
    {
        $sv = TaiKhoan::where('TenDangNhap', '2001210001')->first();
        $this->assertNotNull($sv);
        $svOrig = $sv->TrangThaiMatKhau;
        $sv->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => 0]);

        try {
            $resSv = $this->actingAs($sv->fresh())->get('/admin/chi-tieu-huong-dan');
            $resSv->assertStatus(403);
        } finally {
            $sv->update(['TrangThaiMatKhau' => $svOrig]);
        }

        $gv = TaiKhoan::where('TenDangNhap', 'GV00000003')->first();
        if ($gv) {
            $gvOrig = $gv->TrangThaiMatKhau;
            $gv->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => 0]);
            try {
                $resGv = $this->actingAs($gv->fresh())->get('/admin/chi-tieu-huong-dan');
                $resGv->assertStatus(403);
            } finally {
                $gv->update(['TrangThaiMatKhau' => $gvOrig]);
            }
        }
    }

    /**
     * P4: Giáo vụ thiết lập chỉ tiêu thành công khi số nguyên >= 0
     */
    public function test_giao_vu_thiet_lap_chi_tieu_thanh_cong_khi_so_nguyen_lon_hon_hoac_bang_khong()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $maHocKy = 'HK2627_2';
        $maGV = 'GV00000002';

        // 1. Cập nhật chỉ tiêu = 7 nhóm
        $response = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 7,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ChiTieuHuongDan', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 7,
        ]);
        $this->assertEquals(7, ChiTieuService::getChiTieuTong($maGV, $maHocKy));

        // 2. Cập nhật chỉ tiêu = 0 nhóm (giảng viên đi học/nghỉ phép không nhận nhóm)
        $responseZero = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 0,
        ]);

        $responseZero->assertSessionHas('success');
        $this->assertDatabaseHas('ChiTieuHuongDan', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 0,
        ]);
        $this->assertEquals(0, ChiTieuService::getChiTieuTong($maGV, $maHocKy));

        // Dọn dẹp
        ChiTieuHuongDan::where('MaGV', $maGV)->where('MaHocKy', $maHocKy)->delete();
    }

    /**
     * P4: Validate nghiêm ngặt: từ chối số âm (< 0), số thập phân hoặc để trống
     */
    public function test_validate_tu_choi_chi_tieu_khong_hop_le()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $maHocKy = 'HK2627_2';
        $maGV = 'GV00000002';

        // 1. Số âm: -1 -> Phải bị từ chối
        $resNegative = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => -1,
        ]);
        $resNegative->assertSessionHasErrors('SoNhomToiDa');

        // 2. Số thập phân: 3.5 -> Phải bị từ chối
        $resDecimal = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 3.5,
        ]);
        $resDecimal->assertSessionHasErrors('SoNhomToiDa');

        // 3. Để trống -> Phải bị từ chối
        $resEmpty = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => '',
        ]);
        $resEmpty->assertSessionHasErrors('SoNhomToiDa');
    }

    /**
     * P4: Khôi phục chỉ tiêu về mặc định hệ thống
     */
    public function test_khoi_phuc_chi_tieu_ve_mac_dinh()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $maHocKy = 'HK2627_2';
        $maGV = 'GV00000004';

        // Tạo bản ghi riêng = 8 nhóm
        $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/update', [
            'MaGV'        => $maGV,
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => 8,
        ]);
        $this->assertDatabaseHas('ChiTieuHuongDan', ['MaGV' => $maGV, 'MaHocKy' => $maHocKy, 'SoNhomToiDa' => 8]);

        // Bấm khôi phục
        $resReset = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/reset', [
            'MaGV'    => $maGV,
            'MaHocKy' => $maHocKy,
        ]);
        $resReset->assertSessionHas('success');

        // Bản ghi riêng đã bị xóa, getChiTieuTong trả về 5 (mặc định)
        $this->assertDatabaseMissing('ChiTieuHuongDan', ['MaGV' => $maGV, 'MaHocKy' => $maHocKy]);
        $this->assertEquals(5, ChiTieuService::getChiTieuTong($maGV, $maHocKy));
    }

    /**
     * P4: Thiết lập hàng loạt thành công và validate số nguyên >= 0
     */
    public function test_thiet_lap_hang_loat_thanh_cong_va_validate_so_nguyen()
    {
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $maHocKy = 'HK2627_2';

        // 1. Thử số âm -> Bị từ chối
        $resNegative = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/bulk', [
            'MaHocKy'     => $maHocKy,
            'SoNhomToiDa' => -2,
            'apply_scope' => 'all',
        ]);
        $resNegative->assertSessionHasErrors('SoNhomToiDa');

        // 2. Thử thành công với số 6 cho bộ môn BM_CNPM
        $resSuccess = $this->actingAs($admin)->post('/admin/chi-tieu-huong-dan/bulk', [
            'MaHocKy'     => $maHocKy,
            'MaBoMon'     => 'BM_CNPM',
            'SoNhomToiDa' => 6,
            'apply_scope' => 'all',
        ]);
        $resSuccess->assertSessionHas('success');

        // Kiểm tra giảng viên thuộc BM_CNPM nhận chỉ tiêu 6
        $this->assertEquals(6, ChiTieuService::getChiTieuTong('GV00000001', $maHocKy));
        $this->assertEquals(6, ChiTieuService::getChiTieuTong('GV00000007', $maHocKy));

        // Dọn dẹp
        ChiTieuHuongDan::where('MaHocKy', $maHocKy)->whereIn('MaGV', ['GV00000001', 'GV00000007'])->delete();
    }
}
