<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\Lop;
use App\Models\BoMon;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;

class FormValidationConstraintsTest extends TestCase
{
    protected $admin;
    protected $gvUser;
    protected $svUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
        $this->gvUser = TaiKhoan::where('TenDangNhap', 'GV00000001')->first();
        $this->svUser = TaiKhoan::where('TenDangNhap', 'SV00000001')->first();
    }

    /**
     * Test SinhVien: MSSV phải đúng định dạng SV + 8 số hoặc 10 chữ số.
     */
    public function test_sinhvien_store_validates_mssv_format()
    {
        $lop = Lop::first();

        // 1. Thử MSSV không hợp lệ (quá ngắn, ký tự sai)
        $response = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => 'INVALID_MSSV',
            'HoTen' => 'Nguyễn Văn An',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_invalid_mssv@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $response->assertSessionHasErrors(['TenDangNhap']);

        // 2. Thử MSSV hợp lệ (10 chữ số)
        $validMssv = '9999999999';
        // Xóa trước nếu tồn tại
        SinhVien::where('MaSV', $validMssv)->delete();
        TaiKhoan::where('TenDangNhap', $validMssv)->delete();

        $responseValid = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => $validMssv,
            'HoTen' => 'Nguyễn Văn Bình',
            'MaLop' => $lop->MaLop,
            'Email' => 'sv9999999999@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $responseValid->assertSessionDoesntHaveErrors(['TenDangNhap', 'HoTen', 'SoDienThoai']);

        // Dọn dẹp
        SinhVien::where('MaSV', $validMssv)->delete();
        TaiKhoan::where('TenDangNhap', $validMssv)->delete();
    }

    /**
     * Test SinhVien: Họ tên chỉ chứa chữ cái và khoảng trắng (từ 2 đến 100 ký tự).
     */
    public function test_sinhvien_validates_hoten_letters_only()
    {
        $lop = Lop::first();

        // Họ tên chứa số và ký tự đặc biệt -> Lỗi
        $response = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => '8888888888',
            'HoTen' => 'Nguyễn Văn 123!',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_hoten_invalid@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $response->assertSessionHasErrors(['HoTen']);

        // Họ tên quá ngắn (< 2 ký tự) -> Lỗi
        $responseShort = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => '8888888888',
            'HoTen' => 'A',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_hoten_invalid@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $responseShort->assertSessionHasErrors(['HoTen']);
    }

    /**
     * Test SinhVien & GiangVien: Số điện thoại phải đúng 10 chữ số bắt đầu bằng 0.
     */
    public function test_sodienthoai_format_validation()
    {
        $lop = Lop::first();

        // Số điện thoại không bắt đầu bằng số 0 -> Lỗi
        $res1 = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => '8888888888',
            'HoTen' => 'Nguyễn Văn Cường',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_phone1@huit.edu.vn',
            'SoDienThoai' => '1234567890',
        ]);
        $res1->assertSessionHasErrors(['SoDienThoai']);

        // Số điện thoại có 9 chữ số hoặc 11 chữ số -> Lỗi
        $res2 = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => '8888888888',
            'HoTen' => 'Nguyễn Văn Cường',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_phone2@huit.edu.vn',
            'SoDienThoai' => '091234567',
        ]);
        $res2->assertSessionHasErrors(['SoDienThoai']);

        // Số điện thoại chứa chữ cái -> Lỗi
        $res3 = $this->actingAs($this->admin)->post(route('sinhvien.store'), [
            'TenDangNhap' => '8888888888',
            'HoTen' => 'Nguyễn Văn Cường',
            'MaLop' => $lop->MaLop,
            'Email' => 'test_phone3@huit.edu.vn',
            'SoDienThoai' => '0912345abc',
        ]);
        $res3->assertSessionHasErrors(['SoDienThoai']);
    }

    /**
     * Test GiangVien: MaGV phải đúng 10 ký tự (GV + 8 số).
     */
    public function test_giangvien_store_validates_magv_format()
    {
        $boMon = BoMon::first();

        // MaGV không đúng định dạng -> Lỗi
        $response = $this->actingAs($this->admin)->post(route('giangvien.store'), [
            'TenDangNhap' => 'GV01', // quá ngắn
            'HoTen' => 'Trần Văn Giảng',
            'HocVi' => 'Tiến sĩ',
            'MaBoMon' => $boMon->MaBoMon,
            'Email' => 'gv_test_fail@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $response->assertSessionHasErrors(['TenDangNhap']);

        // MaGV đúng định dạng (GV + 8 số)
        $validMaGV = 'GV99999999';
        GiangVien::where('MaGV', $validMaGV)->delete();
        TaiKhoan::where('TenDangNhap', $validMaGV)->delete();

        $responseValid = $this->actingAs($this->admin)->post(route('giangvien.store'), [
            'TenDangNhap' => $validMaGV,
            'HoTen' => 'Trần Văn Hưng',
            'HocVi' => 'Tiến sĩ',
            'MaBoMon' => $boMon->MaBoMon,
            'Email' => 'gv99999999@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $responseValid->assertSessionDoesntHaveErrors(['TenDangNhap', 'HoTen', 'SoDienThoai']);

        // Dọn dẹp
        GiangVien::where('MaGV', $validMaGV)->delete();
        TaiKhoan::where('TenDangNhap', $validMaGV)->delete();
    }

    /**
     * Test DeTai: Tên đề tài (min 10), Mô tả (min 30), Yêu cầu (min 15).
     */
    public function test_detai_length_constraints()
    {
        $hk = HocKy::first();
        $hp = HocPhan::first();

        // 1. Tên đề tài quá ngắn (< 10 ký tự)
        $res1 = $this->actingAs($this->gvUser)->post(route('giangvien.detai.store'), [
            'TenDeTai' => 'Ngắn',
            'MaHocKy' => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'MoTa' => 'Mô tả đề tài nghiên cứu phải đủ ba mươi ký tự hợp lệ.',
            'YeuCau' => 'Yêu cầu năng lực mười lăm ký tự.',
        ]);
        $res1->assertSessionHasErrors(['TenDeTai']);

        // 2. Mô tả quá ngắn (< 30 ký tự)
        $res2 = $this->actingAs($this->gvUser)->post(route('giangvien.detai.store'), [
            'TenDeTai' => 'Hệ thống quản lý khóa luận tốt nghiệp HUIT',
            'MaHocKy' => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'MoTa' => 'Mô tả quá ngắn',
            'YeuCau' => 'Yêu cầu năng lực mười lăm ký tự.',
        ]);
        $res2->assertSessionHasErrors(['MoTa']);

        // 3. Yêu cầu quá ngắn (< 15 ký tự)
        $res3 = $this->actingAs($this->gvUser)->post(route('giangvien.detai.store'), [
            'TenDeTai' => 'Hệ thống quản lý khóa luận tốt nghiệp HUIT',
            'MaHocKy' => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'MoTa' => 'Mô tả đề tài nghiên cứu phải đủ ba mươi ký tự hợp lệ.',
            'YeuCau' => 'Ngắn quá',
        ]);
        $res3->assertSessionHasErrors(['YeuCau']);
    }

    /**
     * Test Profile: Cập nhật họ tên và số điện thoại phải tuân thủ quy tắc ràng buộc.
     */
    public function test_profile_update_validation_constraints()
    {
        // Họ tên chứa số và ký tự đặc biệt -> Lỗi
        $res = $this->actingAs($this->admin)->post(route('profile.update'), [
            'HoTen' => 'Admin 9999#',
            'Email' => 'admin@huit.edu.vn',
            'SoDienThoai' => '0912345678',
        ]);
        $res->assertSessionHasErrors(['HoTen']);

        // Số điện thoại sai định dạng -> Lỗi
        $resPhone = $this->actingAs($this->admin)->post(route('profile.update'), [
            'HoTen' => 'Quản Trị Viên Hệ Thống',
            'Email' => 'admin@huit.edu.vn',
            'SoDienThoai' => '09123',
        ]);
        $resPhone->assertSessionHasErrors(['SoDienThoai']);
    }
}
