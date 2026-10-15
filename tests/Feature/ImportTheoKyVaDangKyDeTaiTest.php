<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\DangKyDeTai;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\BoMon;
use App\Models\Lop;
use App\Models\ChiTieuHuongDan;
use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportTheoKyVaDangKyDeTaiTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $hocKy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = TaiKhoan::where('TenDangNhap', 'admin')->firstOrFail();
        $this->hocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? HocKy::first();
    }

    /**
     * Tạo file Excel tạm trong bộ nhớ
     */
    private function createExcelFile(array $headers, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $r = 2;
        foreach ($rows as $row) {
            $sheet->fromArray($row, null, 'A' . $r);
            $r++;
        }
        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return new UploadedFile($tempPath, 'test_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * 1. Test Import lại Giảng viên bằng Excel và thêm 2 GV mới theo kỳ để quản lý
     */
    public function test_import_lai_giang_vien_va_them_2_gv_theo_ky()
    {
        $maHK = $this->hocKy->MaHocKy;
        $boMon = BoMon::first();
        $maBM = $boMon ? $boMon->MaBoMon : 'CNPM';

        $mgv1 = 'GV_TEST_KY_01';
        $mgv2 = 'GV_TEST_KY_02';

        $headers = ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai', 'MaHocKy', 'SoNhomToiDa'];
        $rows = [
            [$mgv1, 'ThS. Nguyễn Văn Kiểm Thử 1', 'gv_test01@huit.edu.vn', '0901112233', '1985-05-15', 'Nam', '', 'Thạc sĩ', $maBM, 'Đang công tác', $maHK, 5],
            [$mgv2, 'TS. Trần Thị Kiểm Thử 2', 'gv_test02@huit.edu.vn', '0902223344', '1988-10-20', 'Nữ', '', 'Tiến sĩ', $maBM, 'Đang công tác', $maHK, 5],
        ];

        $file1 = $this->createExcelFile($headers, $rows);

        // Lần 1: Import thêm mới 2 giảng viên theo kỳ
        $response1 = $this->actingAs($this->admin)->post('/admin/giangvien/import', [
            'file' => $file1,
            'MaHocKy' => $maHK,
        ]);
        $response1->assertSessionHasNoErrors();

        // Kiểm tra đã tạo 2 giảng viên và tài khoản
        $this->assertDatabaseHas('GiangVien', ['MaGV' => $mgv1, 'HoTen' => 'ThS. Nguyễn Văn Kiểm Thử 1', 'MaBoMon' => $maBM]);
        $this->assertDatabaseHas('GiangVien', ['MaGV' => $mgv2, 'HoTen' => 'TS. Trần Thị Kiểm Thử 2', 'MaBoMon' => $maBM]);
        $this->assertDatabaseHas('TaiKhoan', ['TenDangNhap' => $mgv1, 'MaVaiTro' => 'VT02']);
        $this->assertDatabaseHas('TaiKhoan', ['TenDangNhap' => $mgv2, 'MaVaiTro' => 'VT02']);
        $this->assertDatabaseHas('ChiTieuHuongDan', ['MaGV' => $mgv1, 'MaHocKy' => $maHK, 'SoNhomToiDa' => 5]);
        $this->assertDatabaseHas('ChiTieuHuongDan', ['MaGV' => $mgv2, 'MaHocKy' => $maHK, 'SoNhomToiDa' => 5]);

        // Lần 2: IMPORT LẠI GIẢNG VIÊN (Re-import cập nhật dữ liệu)
        $rowsUpdate = [
            [$mgv1, 'PGS.TS. Nguyễn Văn Kiểm Thử 1 (Updated)', 'gv_test01@huit.edu.vn', '0901112233', '1985-05-15', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', $maBM, 'Đang công tác', $maHK, 5],
            [$mgv2, 'TS. Trần Thị Kiểm Thử 2 (Updated)', 'gv_test02@huit.edu.vn', '0902223344', '1988-10-20', 'Nữ', '', 'Tiến sĩ', $maBM, 'Đang công tác', $maHK, 5],
        ];
        $file2 = $this->createExcelFile($headers, $rowsUpdate);

        $response2 = $this->actingAs($this->admin)->post('/admin/giangvien/import', [
            'file' => $file2,
            'MaHocKy' => $maHK,
        ]);
        $response2->assertSessionHasNoErrors();

        // Xác nhận đã cập nhật mà KHÔNG bị lỗi trùng MaGV
        $this->assertDatabaseHas('GiangVien', ['MaGV' => $mgv1, 'HoTen' => 'PGS.TS. Nguyễn Văn Kiểm Thử 1 (Updated)', 'HocHam' => 'Phó Giáo sư']);
        $this->assertDatabaseHas('GiangVien', ['MaGV' => $mgv2, 'HoTen' => 'TS. Trần Thị Kiểm Thử 2 (Updated)']);

        // Xác nhận cả 2 giảng viên xuất hiện khi quản lý / lọc theo học kỳ
        $filterResponse = $this->actingAs($this->admin)->get('/admin/giangvien?MaHocKy_filter=' . $maHK);
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($mgv1);
        $filterResponse->assertSee($mgv2);
    }

    /**
     * 2. Test Import Sinh viên theo học kỳ đầy đủ các trường và thêm 2 SV mới theo kỳ để quản lý
     */
    public function test_import_sinh_vien_theo_ky_day_du_cac_truong_va_them_2_sv()
    {
        $maHK = $this->hocKy->MaHocKy;
        $lop = Lop::first();
        $maLop = $lop ? $lop->MaLop : '12DHCNTT01';

        $msv1 = '2001990001';
        $msv2 = '2001990002';

        $headers = ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai', 'GhiChu'];
        $rows = [
            [$msv1, 'Lê Hoàng Kiểm Thử SV1', '2001990001@st.huit.edu.vn', '0981990001', '2003-03-12', 'Nam', $maLop, '2021-2025', 128, 3.45, 'Đủ điều kiện', 'Đủ điều kiện xét duyệt KLTN đợt 1'],
            [$msv2, 'Phạm Ngọc Kiểm Thử SV2', '2001990002@st.huit.edu.vn', '0981990002', '2003-07-25', 'Nữ', $maLop, '2021-2025', 120, 3.15, 'Đủ điều kiện', 'Đủ điều kiện xét duyệt KLTN đợt 1'],
        ];

        $file = $this->createExcelFile($headers, $rows);

        $response = $this->actingAs($this->admin)->post(route('admin.sinhvien.import_dk'), [
            'file' => $file,
            'MaHocKy' => $maHK,
        ]);
        $response->assertSessionHasNoErrors();

        // Kiểm tra hồ sơ 2 sinh viên được tạo đầy đủ trường
        $this->assertDatabaseHas('SinhVien', [
            'MaSV' => $msv1,
            'HoTen' => 'Lê Hoàng Kiểm Thử SV1',
            'Email' => '2001990001@st.huit.edu.vn',
            'SoDienThoai' => '0981990001',
            'SoTinChiTichLuy' => 128,
        ]);
        $this->assertDatabaseHas('SinhVien', [
            'MaSV' => $msv2,
            'HoTen' => 'Phạm Ngọc Kiểm Thử SV2',
            'Email' => '2001990002@st.huit.edu.vn',
            'SoDienThoai' => '0981990002',
            'SoTinChiTichLuy' => 120,
        ]);

        // Kiểm tra tài khoản Portal đã cấp
        $this->assertDatabaseHas('TaiKhoan', ['TenDangNhap' => $msv1, 'MaVaiTro' => 'VT03']);
        $this->assertDatabaseHas('TaiKhoan', ['TenDangNhap' => $msv2, 'MaVaiTro' => 'VT03']);

        // Kiểm tra danh sách điều kiện theo kỳ
        $this->assertDatabaseHas('DanhSachSVDuDieuKien', [
            'MaSV' => $msv1,
            'MaHocKy' => $maHK,
            'TrangThai' => 'Đủ điều kiện',
        ]);
        $this->assertDatabaseHas('DanhSachSVDuDieuKien', [
            'MaSV' => $msv2,
            'MaHocKy' => $maHK,
            'TrangThai' => 'Đủ điều kiện',
        ]);

        // Kiểm tra hiển thị trong giao diện quản lý sinh viên theo học kỳ
        $pageResponse = $this->actingAs($this->admin)->get("/admin/sinhvien?tab=du_dieu_kien&MaHocKy={$maHK}");
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee($msv1);
        $pageResponse->assertSee($msv2);
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 3. Test Đăng ký đề tài: Đề tài đã công bố còn trống + Nhóm đủ 3 thành viên -> tự động gán trực tiếp không cần GVHD duyệt
     */
    public function test_dang_ky_de_tai_tu_dong_gan_cho_nhom_du_3_thanh_vien()
    {
        \Carbon\Carbon::setTestNow('2026-08-11 10:00:00');

        $maHK = $this->hocKy->MaHocKy;
        $hocPhan = HocPhan::first();
        $gv = GiangVien::first();

        // 1. Tạo 1 đề tài đã công bố còn trống
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_DK_' . rand(1000, 9999),
            'TenDeTai' => 'Đề tài tự động gán khi nhóm đủ 3 thành viên',
            'MaGV' => $gv->MaGV,
            'MaHocKy' => $maHK,
            'MaHocPhan' => $hocPhan?->MaHocPhan,
            'TrangThai' => 'Đã công bố',
            'SoLuongSinhVienToiDa' => 3,
        ]);

        // 2. Tạo 3 sinh viên mới độc lập cho nhóm
        $s1 = SinhVien::create(['MaSV' => 'SV_REG_01', 'HoTen' => 'SV Reg 01', 'MaLop' => Lop::value('MaLop')]);
        $s2 = SinhVien::create(['MaSV' => 'SV_REG_02', 'HoTen' => 'SV Reg 02', 'MaLop' => Lop::value('MaLop')]);
        $s3 = SinhVien::create(['MaSV' => 'SV_REG_03', 'HoTen' => 'SV Reg 03', 'MaLop' => Lop::value('MaLop')]);

        $userTruongNhom = TaiKhoan::create([
            'MaTK' => 'SV_REG_01',
            'TenDangNhap' => 'SV_REG_01',
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT03',
            'TrangThai' => true,
        ]);
        $s1->update(['MaTK' => $userTruongNhom->MaTK]);

        // 3. Tạo nhóm có đủ 3 thành viên chính thức
        $nhom = Nhom::create([
            'MaNhom' => 'NHOM_TEST_3TV_' . rand(1000, 9999),
            'TenNhom' => 'Nhóm Test 3 TV',
            'MaHocKy' => $maHK,
            'MaHocPhan' => $hocPhan?->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);

        // Thành viên 1: Trưởng nhóm
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $s1->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);
        // Thành viên 2, 3
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $s2->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $s3->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // Nhóm trưởng đại diện đăng ký đề tài
        $response = $this->actingAs($userTruongNhom)->post('/sinhvien/dangky', [
            'MaDeTai' => $deTai->MaDeTai,
        ]);
        $response->assertSessionHasNoErrors();

        // Xác nhận đề tài được gán trực tiếp cho nhóm với trạng thái 'Đã duyệt' (không cần GVHD phê duyệt)
        $this->assertDatabaseHas('PhieuDangKy', [
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $deTai->MaDeTai,
            'TrangThai' => 'Đã duyệt',
        ]);

        $this->assertEquals($deTai->MaDeTai, $nhom->fresh()->MaDeTai);
    }
}
