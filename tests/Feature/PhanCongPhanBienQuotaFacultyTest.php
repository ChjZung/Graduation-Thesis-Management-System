<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\Khoa;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Models\ChiTieuPhanBien;
use App\Services\ChiTieuPhanBienService;
use Illuminate\Support\Facades\DB;

class PhanCongPhanBienQuotaFacultyTest extends TestCase
{
    protected $tbmCnpm;
    protected $gvCnpm1;
    protected $gvCnpm2;
    protected $gvAttt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tbmCnpm = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->first();
        if (!$this->tbmCnpm) {
            $this->tbmCnpm = TaiKhoan::where('TenDangNhap', 'like', 'TBM%')->first();
        }

        $this->gvCnpm1 = GiangVien::where('MaBoMon', 'BM_CNPM')->first();
        $this->gvCnpm2 = GiangVien::where('MaBoMon', 'BM_CNPM')->where('MaGV', '!=', $this->gvCnpm1?->MaGV)->first();
        $this->gvAttt = GiangVien::where('MaBoMon', 'BM_ATTT')->first();
    }

    /**
     * D1.1: Chỉ hiện đề tài đủ điều kiện phản biện (phải có FileDeCuong, không bị từ chối / chưa duyệt)
     */
    public function test_chi_hien_de_tai_du_dieu_kien_phan_bien()
    {
        $testDtCoDeCuong = 'DT_TEST_D1_CO_DC';
        $testDtKhongDeCuong = 'DT_TEST_D1_NO_DC';

        // Tạo 2 đề tài test
        DeTai::updateOrCreate(
            ['MaDeTai' => $testDtCoDeCuong],
            [
                'TenDeTai' => 'Đề tài D1 Có Đề Cương Chi Tiết',
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => 'HK2627_2',
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => 'decuong_test_d1.pdf',
                'NgayDeXuat' => now()->toDateString(),
            ]
        );

        DeTai::updateOrCreate(
            ['MaDeTai' => $testDtKhongDeCuong],
            [
                'TenDeTai' => 'Đề tài D1 Không Có Đề Cương',
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => 'HK2627_2',
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => null,
                'NgayDeXuat' => now()->toDateString(),
            ]
        );

        $response = $this->actingAs($this->tbmCnpm)->get('/truongbomon/phan-cong-phan-bien?tab=chua_phan_cong');
        $response->assertStatus(200);
        $response->assertSee($testDtCoDeCuong);
        $response->assertDontSee($testDtKhongDeCuong);

        // Dọn dẹp
        DeTai::whereIn('MaDeTai', [$testDtCoDeCuong, $testDtKhongDeCuong])->delete();
    }

    /**
     * D1.2: Chặn server-side nếu đề tài chưa có file đề cương chi tiết
     */
    public function test_chan_phan_cong_khi_de_tai_chua_co_de_cuong()
    {
        $testDt = 'DT_TEST_D1_NO_DC';
        DeTai::updateOrCreate(
            ['MaDeTai' => $testDt],
            [
                'TenDeTai' => 'Đề tài Test Chặn Thiếu Đề Cương',
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => 'HK2627_2',
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => null,
                'NgayDeXuat' => now()->toDateString(),
            ]
        );

        $response = $this->actingAs($this->tbmCnpm)->post("/truongbomon/phan-cong-phan-bien/{$testDt}", [
            'MaGVPhanBien' => $this->gvCnpm2->MaGV,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhanCongPhanBien', [
            'MaDeTai' => $testDt,
            'MaGV' => $this->gvCnpm2->MaGV,
        ]);

        DeTai::where('MaDeTai', $testDt)->delete();
    }

    /**
     * D1.3: Chặn giảng viên khác Khoa với đề tài
     */
    public function test_chan_giang_vien_khac_khoa()
    {
        // Tạo Khoa và Bộ môn khác
        $khoaKhac = Khoa::firstOrCreate(
            ['MaKhoa' => 'KHOA_KHAC'],
            ['TenKhoa' => 'Khoa Khác Test']
        );
        $bmKhac = BoMon::firstOrCreate(
            ['MaBoMon' => 'BM_KHAC_TEST'],
            ['TenBoMon' => 'Bộ môn Khác Test', 'MaKhoa' => 'KHOA_KHAC']
        );
        $gvKhacKhoa = GiangVien::firstOrCreate(
            ['MaGV' => 'GV_KHAC_KHOA'],
            ['HoTen' => 'Giảng Viên Khoa Khác', 'MaBoMon' => 'BM_KHAC_TEST', 'Email' => 'gvkhac@example.com']
        );

        $testDt = 'DT_TEST_D1_KHOA';
        DeTai::updateOrCreate(
            ['MaDeTai' => $testDt],
            [
                'TenDeTai' => 'Đề tài Test Kiểm Tra Khoa',
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => 'HK2627_2',
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => 'decuong.pdf',
                'NgayDeXuat' => now()->toDateString(),
            ]
        );

        $response = $this->actingAs($this->tbmCnpm)->post("/truongbomon/phan-cong-phan-bien/{$testDt}", [
            'MaGVPhanBien' => 'GV_KHAC_KHOA',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhanCongPhanBien', [
            'MaDeTai' => $testDt,
            'MaGV' => 'GV_KHAC_KHOA',
        ]);

        DeTai::where('MaDeTai', $testDt)->delete();
        GiangVien::where('MaGV', 'GV_KHAC_KHOA')->delete();
        BoMon::where('MaBoMon', 'BM_KHAC_TEST')->delete();
        Khoa::where('MaKhoa', 'KHOA_KHAC')->delete();
    }

    /**
     * D1.4: Cho phép giảng viên cùng Khoa (dù khác Bộ môn, ví dụ BM_ATTT và BM_CNPM cùng thuộc Khoa CNTT)
     */
    public function test_cho_phep_giang_vien_cung_khoa_phan_bien()
    {
        $testDt = 'DT_TEST_D1_CUNG_KHOA';
        DeTai::updateOrCreate(
            ['MaDeTai' => $testDt],
            [
                'TenDeTai' => 'Đề tài Test Giảng Viên Cùng Khoa',
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => 'HK2627_2',
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => 'decuong.pdf',
                'NgayDeXuat' => now()->toDateString(),
            ]
        );

        // Đảm bảo GV ATTT chưa đầy hạn mức
        ChiTieuPhanBienService::resetChiTieu($this->gvAttt->MaGV, 'HK2627_2');

        $response = $this->actingAs($this->tbmCnpm)->post("/truongbomon/phan-cong-phan-bien/{$testDt}", [
            'MaGVPhanBien' => $this->gvAttt->MaGV,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('PhanCongPhanBien', [
            'MaDeTai' => $testDt,
            'MaGV' => $this->gvAttt->MaGV,
            'VaiTro' => 'Phản biện đề cương',
        ]);

        PhanCongPhanBien::where('MaDeTai', $testDt)->delete();
        DeTai::where('MaDeTai', $testDt)->delete();
    }

    /**
     * D1.5: 1 GV tối đa phản biện 5 đề tài - chặn server-side khi đạt 5/5
     */
    public function test_chan_khi_giang_vien_dat_han_muc_toi_da_5_de_tai()
    {
        $maHK = 'HK2627_2';
        $gvPB = $this->gvCnpm2;

        // Reset chỉ tiêu về mặc định (5)
        ChiTieuPhanBienService::resetChiTieu($gvPB->MaGV, $maHK);

        // Tạo 5 đề tài giả lập đã phân công phản biện cho $gvPB
        $createdDtIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $dtId = "DT_TEST_PB_FULL_{$i}";
            $createdDtIds[] = $dtId;

            DeTai::updateOrCreate(
                ['MaDeTai' => $dtId],
                [
                    'TenDeTai' => "Đề tài Phản Biện Full {$i}",
                    'MaGV' => $this->gvCnpm1->MaGV,
                    'MaHocKy' => $maHK,
                    'TrangThai' => 'Đang phản biện đề cương',
                    'FileDeCuong' => "decuong_{$i}.pdf",
                ]
            );

            PhanCongPhanBien::updateOrCreate(
                ['MaDeTai' => $dtId, 'VaiTro' => 'Phản biện đề cương'],
                [
                    'MaPhanCong' => "PC_TEST_FULL_{$i}",
                    'MaGV' => $gvPB->MaGV,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai' => 'Đang phản biện',
                ]
            );
        }

        // Tạo đề tài thứ 6
        $dt6 = 'DT_TEST_PB_FULL_6';
        $createdDtIds[] = $dt6;
        DeTai::updateOrCreate(
            ['MaDeTai' => $dt6],
            [
                'TenDeTai' => "Đề tài Phản Biện Thứ 6",
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => $maHK,
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => "decuong_6.pdf",
            ]
        );

        // Phân công đề tài thứ 6 cho $gvPB -> Phải bị chặn vì đã đạt 5/5
        $response = $this->actingAs($this->tbmCnpm)->post("/truongbomon/phan-cong-phan-bien/{$dt6}", [
            'MaGVPhanBien' => $gvPB->MaGV,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhanCongPhanBien', [
            'MaDeTai' => $dt6,
            'MaGV' => $gvPB->MaGV,
        ]);

        // Dọn dẹp
        PhanCongPhanBien::whereIn('MaDeTai', $createdDtIds)->delete();
        DeTai::whereIn('MaDeTai', $createdDtIds)->delete();
    }

    /**
     * D1.6: Cho phép tùy chỉnh hạn mức (ví dụ nâng lên 8) thì được phép phân công tiếp
     */
    public function test_tuy_chinh_han_muc_cho_phep_vuot_qua_5()
    {
        $maHK = 'HK2627_2';
        $gvPB = $this->gvCnpm2;

        // Tạo 5 đề tài đã phân công
        $createdDtIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $dtId = "DT_TEST_PB_CUSTOM_{$i}";
            $createdDtIds[] = $dtId;

            DeTai::updateOrCreate(
                ['MaDeTai' => $dtId],
                [
                    'TenDeTai' => "Đề tài Phản Biện Custom {$i}",
                    'MaGV' => $this->gvCnpm1->MaGV,
                    'MaHocKy' => $maHK,
                    'TrangThai' => 'Đang phản biện đề cương',
                    'FileDeCuong' => "decuong_{$i}.pdf",
                ]
            );

            PhanCongPhanBien::updateOrCreate(
                ['MaDeTai' => $dtId, 'VaiTro' => 'Phản biện đề cương'],
                [
                    'MaPhanCong' => "PC_TEST_CUSTOM_{$i}",
                    'MaGV' => $gvPB->MaGV,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai' => 'Đang phản biện',
                ]
            );
        }

        // Tạo đề tài thứ 6
        $dt6 = 'DT_TEST_PB_CUSTOM_6';
        $createdDtIds[] = $dt6;
        DeTai::updateOrCreate(
            ['MaDeTai' => $dt6],
            [
                'TenDeTai' => "Đề tài Phản Biện Custom Thứ 6",
                'MaGV' => $this->gvCnpm1->MaGV,
                'MaHocKy' => $maHK,
                'TrangThai' => 'Đã nộp đề cương - Chờ phân công PB',
                'FileDeCuong' => "decuong_6.pdf",
            ]
        );

        // Nâng hạn mức lên 8 đề tài qua API TBM
        $updateQuotaResponse = $this->actingAs($this->tbmCnpm)->post('/truongbomon/phan-cong-phan-bien/thiet-lap-chi-tieu', [
            'MaGV' => $gvPB->MaGV,
            'SoDeTaiToiDa' => 8,
            'MaHocKy' => $maHK,
            'GhiChu' => 'Nâng hạn mức test',
        ]);
        $updateQuotaResponse->assertSessionHasNoErrors();

        // Bây giờ phân công đề tài thứ 6 -> Phải thành công!
        $response = $this->actingAs($this->tbmCnpm)->post("/truongbomon/phan-cong-phan-bien/{$dt6}", [
            'MaGVPhanBien' => $gvPB->MaGV,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('PhanCongPhanBien', [
            'MaDeTai' => $dt6,
            'MaGV' => $gvPB->MaGV,
        ]);

        // Reset về mặc định
        $resetResponse = $this->actingAs($this->tbmCnpm)->post('/truongbomon/phan-cong-phan-bien/reset-chi-tieu', [
            'MaGV' => $gvPB->MaGV,
            'MaHocKy' => $maHK,
        ]);
        $resetResponse->assertSessionHasNoErrors();

        // Dọn dẹp
        PhanCongPhanBien::whereIn('MaDeTai', $createdDtIds)->delete();
        DeTai::whereIn('MaDeTai', $createdDtIds)->delete();
        ChiTieuPhanBienService::resetChiTieu($gvPB->MaGV, $maHK);
    }
}
