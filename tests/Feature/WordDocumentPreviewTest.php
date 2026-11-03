<?php

namespace Tests\Feature;

use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\HoiDong;
use App\Models\Khoa;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WordDocumentPreviewTest extends TestCase
{
    use DatabaseTransactions;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    private function createDocxFixture(string $relativeDir, string $fileName): string
    {
        $dir = storage_path('app/public/' . trim($relativeDir, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($fullPath, "PK\x03\x04Mock DOCX content for testing Word document preview");
        $this->tempFiles[] = $fullPath;
        return trim($relativeDir, '/') . '/' . $fileName;
    }

    private function setupDeTaiWithDocx(string $fileName = 'de_cuong_test.docx'): array
    {
        $khoa = Khoa::first() ?? Khoa::create(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Công nghệ thông tin']);
        $boMon = BoMon::where('MaKhoa', $khoa->MaKhoa)->first() ?? BoMon::create([
            'MaBoMon' => 'BM_CNTT',
            'TenBoMon' => 'Bộ môn Công nghệ phần mềm',
            'MaKhoa' => $khoa->MaKhoa,
        ]);
        $hocKy = HocKy::first() ?? HocKy::create([
            'MaHocKy' => 'HK2627_1',
            'TenHocKy' => 'Học kỳ 1 2026-2027',
            'NamHoc' => '2026-2027',
            'HocKy' => 1,
            'TrangThai' => 1,
        ]);
        $hocPhan = HocPhan::first() ?? HocPhan::create([
            'MaHocPhan' => 'HP_KLTN',
            'TenHocPhan' => 'Khóa luận tốt nghiệp',
            'SoTinChi' => 10,
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // Tạo tài khoản GV đề xuất
        $roleGv = VaiTro::where('TenVaiTro', 'Giảng viên')->first();
        $tkId = 'TK_' . bin2hex(random_bytes(5));
        $tkGv = TaiKhoan::create([
            'MaTK' => $tkId,
            'TenDangNhap' => 'GV_TEST_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('password123'),
            'MaVaiTro' => $roleGv->MaVaiTro,
            'TrangThai' => 1,
        ]);
        $gv = GiangVien::create([
            'MaGV' => 'GV_' . bin2hex(random_bytes(4)),
            'HoTen' => 'TS. Giảng Viên Hướng Dẫn',
            'Email' => 'gvhd_' . bin2hex(random_bytes(3)) . '@huit.edu.vn',
            'MaBoMon' => $boMon->MaBoMon,
            'MaTK' => $tkGv->MaTK,
        ]);

        $filePath = $this->createDocxFixture('de_cuong', $fileName);

        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_' . bin2hex(random_bytes(4)),
            'TenDeTai' => 'Nghiên cứu xây dựng hệ thống xem trước tài liệu Word trực tuyến',
            'MoTa' => 'Mô tả chi tiết đề tài',
            'YeuCau' => 'Yêu cầu kỹ thuật đề tài',
            'SoLuongSinhVienToiDa' => 2,
            'FileDeCuong' => $filePath,
            'MaGV' => $gv->MaGV,
            'MaHocKy' => $hocKy->MaHocKy,
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'TrangThai' => 'Chờ duyệt',
        ]);

        return [$deTai, $gv, $tkGv, $boMon, $khoa];
    }

    /**
     * Test 1: Giảng viên hướng dẫn (người tạo đề tài) có thể xem trước tệp Word inline.
     */
    public function test_owner_lecturer_can_preview_docx_inline(): void
    {
        [$deTai, $gv, $tkGv] = $this->setupDeTaiWithDocx('de_cuong_huong_dan.docx');

        $response = $this->actingAs($tkGv)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('de_cuong_huong_dan.docx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 2: Nút Tải về với ?download=1 trả về Content-Disposition: attachment.
     */
    public function test_owner_lecturer_can_download_docx_attachment(): void
    {
        [$deTai, $gv, $tkGv] = $this->setupDeTaiWithDocx('de_cuong_tai_ve.docx');

        $response = $this->actingAs($tkGv)
            ->get(route('files.decuong.download', ['id' => $deTai->MaDeTai, 'download' => 1]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('de_cuong_tai_ve.docx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 3: Sinh viên thuộc nhóm đã đăng ký đề tài được phép xem đề cương Word.
     */
    public function test_assigned_student_can_preview_docx_inline(): void
    {
        [$deTai, $gv, $tkGv, $boMon] = $this->setupDeTaiWithDocx('de_cuong_sinhvien.docx');

        $roleSv = VaiTro::where('TenVaiTro', 'Sinh viên')->first();
        $tkSv = TaiKhoan::create([
            'MaTK' => 'TK_SV_' . bin2hex(random_bytes(5)),
            'TenDangNhap' => 'SV_TEST_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('password123'),
            'MaVaiTro' => $roleSv->MaVaiTro,
            'TrangThai' => 1,
        ]);
        $sv = SinhVien::create([
            'MaSV' => '2001' . rand(100000, 999999),
            'HoTen' => 'Nguyễn Văn Sinh Viên',
            'MaTK' => $tkSv->MaTK,
        ]);

        $nhom = Nhom::create([
            'MaNhom' => 'NHOM_' . bin2hex(random_bytes(4)),
            'TenNhom' => 'Nhóm Nghiên Cứu',
            'TrangThai' => 'Đang hoạt động',
            'MaHocKy' => $deTai->MaHocKy,
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $sv->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
        ]);

        PhieuDangKy::create([
            'MaDangKy' => 'DK_' . bin2hex(random_bytes(4)),
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $deTai->MaDeTai,
            'TrangThai' => 'Đã duyệt',
            'NgayDangKy' => now(),
        ]);

        $response = $this->actingAs($tkSv)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 4: Thành viên Hội đồng bảo vệ đánh giá đề tài có quyền xem đề cương Word.
     */
    public function test_defense_council_member_can_preview_docx(): void
    {
        [$deTai, $gvOwner, $tkOwner, $boMon, $khoa] = $this->setupDeTaiWithDocx('de_cuong_hoidong.docx');

        // Tạo giảng viên ủy viên hội đồng
        $roleGv = VaiTro::where('TenVaiTro', 'Giảng viên')->first();
        $tkHd = TaiKhoan::create([
            'MaTK' => 'TK_HD_' . bin2hex(random_bytes(5)),
            'TenDangNhap' => 'GV_HD_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('password123'),
            'MaVaiTro' => $roleGv->MaVaiTro,
            'TrangThai' => 1,
        ]);
        $gvHd = GiangVien::create([
            'MaGV' => 'GVHD_' . bin2hex(random_bytes(4)),
            'HoTen' => 'PGS. TS. Thành Viên Hội Đồng',
            'Email' => 'council_' . bin2hex(random_bytes(3)) . '@huit.edu.vn',
            'MaBoMon' => $boMon->MaBoMon,
            'MaTK' => $tkHd->MaTK,
        ]);

        // Tạo hội đồng bảo vệ và phân công đề tài
        $hoiDong = HoiDong::create([
            'MaHoiDong' => 'HD_' . bin2hex(random_bytes(4)),
            'TenHoiDong' => 'Hội đồng bảo vệ KLTN số 1',
            'MaDeTai' => $deTai->MaDeTai,
            'MaKhoa' => $khoa->MaKhoa,
            'MaHocKy' => $deTai->MaHocKy,
            'TrangThai' => 'Đã thành lập',
        ]);

        ThanhVienHoiDong::create([
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'MaGV' => $gvHd->MaGV,
            'VaiTro' => 'Ủy viên',
        ]);

        $response = $this->actingAs($tkHd)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 5: Người dùng không liên quan bị chặn 403 Forbidden với thông báo rõ ràng.
     */
    public function test_unrelated_user_gets_403_forbidden(): void
    {
        [$deTai, $gvOwner, $tkOwner, $boMon] = $this->setupDeTaiWithDocx('de_cuong_bao_mat.docx');
        // Đề tài chưa công bố
        $deTai->update(['TrangThai' => 'Chờ duyệt']);

        // Tạo tài khoản một giảng viên khác không liên quan
        $roleGv = VaiTro::where('TenVaiTro', 'Giảng viên')->first();
        $tkStranger = TaiKhoan::create([
            'MaTK' => 'TK_STR_' . bin2hex(random_bytes(5)),
            'TenDangNhap' => 'GV_STRANGER_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('password123'),
            'MaVaiTro' => $roleGv->MaVaiTro,
            'TrangThai' => 1,
        ]);
        GiangVien::create([
            'MaGV' => 'GV_STR_' . bin2hex(random_bytes(4)),
            'HoTen' => 'ThS. Giảng Viên Lạ',
            'Email' => 'stranger_' . bin2hex(random_bytes(3)) . '@huit.edu.vn',
            'MaBoMon' => $boMon->MaBoMon,
            'MaTK' => $tkStranger->MaTK,
        ]);

        $response = $this->actingAs($tkStranger)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertForbidden();
    }

    /**
     * Test 6: Yêu cầu chưa đăng nhập (AJAX) nhận 401 Unauthorized thay vì mã lạ.
     */
    public function test_unauthenticated_ajax_request_gets_401(): void
    {
        [$deTai] = $this->setupDeTaiWithDocx('de_cuong_guest.docx');

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertStatus(401);
    }

    /**
     * Test 7: Tệp Word có tên tiếng Việt và khoảng trắng được mã hóa URL chuẩn RFC 5987/6266.
     */
    public function test_vietnamese_characters_and_spaces_in_docx_filename(): void
    {
        $vnFileName = 'Nguyễn Ngọc Hân_2028231133_Báo Cáo Khóa Luận.docx';
        [$deTai, $gv, $tkGv] = $this->setupDeTaiWithDocx($vnFileName);

        $response = $this->actingAs($tkGv)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($disposition);
        $this->assertStringContainsString("filename*=UTF-8''", $disposition);
        $this->assertStringContainsString(rawurlencode($vnFileName), $disposition);
    }

    /**
     * Test 8: Fallback route /storage/{path} hoạt động an toàn và không gây 403 do thiếu signed URL.
     */
    public function test_storage_fallback_route_serves_valid_authenticated_file(): void
    {
        [$deTai, $gv, $tkGv] = $this->setupDeTaiWithDocx('de_cuong_storage_fallback.docx');

        // Truy cập qua URL direct storage /storage/de_cuong/...
        $response = $this->actingAs($tkGv)
            ->get('/storage/' . $deTai->FileDeCuong);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 9: Đề tài không có file đề cương hoặc file bị xóa trả về 404 Not Found.
     */
    public function test_missing_outline_file_returns_404(): void
    {
        [$deTai, $gv, $tkGv] = $this->setupDeTaiWithDocx('file_se_bi_xoa.docx');
        $fullPath = storage_path('app/public/' . $deTai->FileDeCuong);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $response = $this->actingAs($tkGv)
            ->get(route('files.decuong.download', $deTai->MaDeTai));

        $response->assertNotFound();
    }
}
