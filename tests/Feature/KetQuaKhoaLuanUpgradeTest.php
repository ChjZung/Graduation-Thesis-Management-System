<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use App\Models\LichSuCongBoKetQua;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class KetQuaKhoaLuanUpgradeTest extends TestCase
{
    use DatabaseTransactions;

    protected $userTruongKhoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userTruongKhoa = TaiKhoan::where('MaVaiTro', 'VT05')->first();
        if (!$this->userTruongKhoa) {
            $this->userTruongKhoa = TaiKhoan::whereHas('vaiTro', function($q) {
                $q->where('TenVaiTro', 'like', '%Trưởng khoa%');
            })->first();
        }
    }

    /**
     * 1. Kiểm tra 3 trạng thái điểm của Sinh viên:
     * - Chưa có điểm: thẻ xám/xanh dương, không hiển thị F giả, không hiển thị "0.00 / 10"
     */
    public function test_sinh_vien_chua_co_diem_khong_hien_thi_f_va_khong_hien_0_diem()
    {
        // Sinh viên của HSBV_N04 (trạng thái chưa đủ phiếu)
        $sv = SinhVien::find('2001210080');
        if (!$sv || !$sv->taiKhoan) {
            $this->markTestSkipped('Không tìm thấy sinh viên 2001210080');
        }

        // Đảm bảo không có bản ghi KetQuaSinhVien để kiểm tra trạng thái Chưa có điểm
        KetQuaSinhVien::where('MaSV', $sv->MaSV)->delete();

        $response = $this->actingAs($sv->taiKhoan)->get(route('sinhvien.ketqua.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Trạng thái thẻ điểm phải là "Chưa Có Điểm Khóa Luận"
        $this->assertStringContainsString('Chưa Có Điểm Khóa Luận', $content);
        // Tuyệt đối không hiển thị F giả hay 0.00 / 10
        $this->assertStringNotContainsString('0.00 / 10', $content);
        $this->assertStringNotContainsString('Điểm chữ: F', $content);
    }

    /**
     * - Đã có điểm nhưng chưa công bố: thông báo "Chưa công bố chính thức", ẩn điểm, fail-closed
     */
    public function test_sinh_vien_chua_cong_bo_chinh_thuc_khong_lo_diem_va_nhan_xet()
    {
        // Sử dụng HS01 nhưng tạm thời chuyển về ChoCongBo/DaDuPhieu trong transaction
        $hoSo = HoSoBaoVe::find('HS01');
        if (!$hoSo) {
            $this->markTestSkipped('Không tìm thấy hồ sơ HS01');
        }

        $hoSo->update(['TrangThaiKetQua' => 'DaDuPhieu']);

        $sv = SinhVien::find('2001210001');
        $this->assertNotNull($sv);
        KetQuaSinhVien::where('MaSV', $sv->MaSV)->update(['TrangThai' => 'Chưa công bố']);

        $response = $this->actingAs($sv->taiKhoan)->get(route('sinhvien.ketqua.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Phải có badge / thông báo "Chưa Công Bố Chính Thức"
        $this->assertStringContainsString('Chưa Công Bố Chính Thức', $content);
        // View data daCongBoChinhThuc phải là false
        $this->assertFalse($response->viewData('daCongBoChinhThuc'));
        // phieuChamDiems truyền sang view phải rỗng (bảo mật fail-closed)
        $this->assertCount(0, $response->viewData('phieuChamDiems'));
    }

    /**
     * - Đã công bố: hiển thị điểm chính thức, bảng Hội đồng dạng cột, danh sách sinh viên dạng bảng
     */
    public function test_sinh_vien_da_cong_bo_hien_thi_diem_va_thong_tin_hoi_dong_dang_bang()
    {
        $hoSo = HoSoBaoVe::find('HS01');
        if (!$hoSo) {
            $this->markTestSkipped('Không tìm thấy hồ sơ HS01');
        }

        $hoSo->update(['TrangThaiKetQua' => 'DaCongBo']);

        $sv = SinhVien::find('2001210001');
        $this->assertNotNull($sv);

        $response = $this->actingAs($sv->taiKhoan)->get(route('sinhvien.ketqua.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertTrue($response->viewData('daCongBoChinhThuc'));

        // Phải có cấu trúc bảng (table) chuẩn cho Hội đồng và Nhóm SV
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('Thành viên Hội đồng đánh giá', $content);
        $this->assertStringContainsString('Danh sách sinh viên thực hiện đề tài', $content);
        $this->assertStringContainsString('Chủ tịch', $content);
    }

    /**
     * 2. Trưởng khoa: Kiểm tra điều kiện công bố (AJAX kiemTraDieuKien)
     */
    public function test_truong_khoa_kiem_tra_dieu_kien_cong_bo_hop_le_va_khong_hop_le()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // 2.1. Đề tài chưa đủ phiếu (HSBV_N04)
        $hoSoThieu = HoSoBaoVe::find('HSBV_N04');
        if ($hoSoThieu) {
            $phieu = PhieuChamDiem::where('MaHoiDong', $hoSoThieu->MaHoiDong)->first();
            if ($phieu) {
                $phieu->TrangThai = 'Lưu nháp';
                $phieu->NgayXacNhan = null;
                $phieu->save();
            }

            $response = $this->actingAs($this->userTruongKhoa)
                ->getJson(route('truongkhoa.ketqua.kiemtra_dieukien', [
                    'pham_vi' => 'DE_TAI',
                    'MaHoSo' => $hoSoThieu->MaHoSo
                ]));

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'data' => [
                    'du_dieu_kien' => false
                ]
            ]);
            $this->assertNotEmpty($response->json('data.ly_do'));
        }

        // 2.2. Đề tài đã đủ phiếu (HS01)
        $hoSoDu = HoSoBaoVe::find('HS01');
        if ($hoSoDu) {
            $response = $this->actingAs($this->userTruongKhoa)
                ->getJson(route('truongkhoa.ketqua.kiemtra_dieukien', [
                    'pham_vi' => 'DE_TAI',
                    'MaHoSo' => $hoSoDu->MaHoSo
                ]));

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'data' => [
                    'du_dieu_kien' => true
                ]
            ]);
        }
    }

    /**
     * 3. Trưởng khoa: Thao tác Công bố đề tài & Thu hồi công bố (kèm lý do và audit log)
     */
    public function test_truong_khoa_cong_bo_de_tai_va_thu_hoi_co_luu_audit_log()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $hoSo = HoSoBaoVe::find('HS01');
        if (!$hoSo) {
            $this->markTestSkipped('Không tìm thấy hồ sơ HS01.');
        }

        $hoSo->update(['TrangThaiKetQua' => 'ChuaCongBo']);

        // 3.1. Thực hiện Công bố đề tài
        $resCongBo = $this->actingAs($this->userTruongKhoa)
            ->post(route('truongkhoa.ketqua.congbo_detai'), [
                'MaHoSo' => $hoSo->MaHoSo,
            ]);

        $resCongBo->assertSessionHas('success');
        $this->assertEquals('DaCongBo', $hoSo->fresh()->TrangThaiKetQua);

        // Kiểm tra audit log đã ghi nhận action CONG_BO
        $logCongBo = LichSuCongBoKetQua::where('PhamVi', 'DE_TAI')
            ->where('MaHoSo', $hoSo->MaHoSo)
            ->where('LoaiHanhDong', 'CONG_BO')
            ->latest('id')
            ->first();
        $this->assertNotNull($logCongBo);

        // 3.2. Thu hồi mà KHÔNG nhập lý do => Bị chặn (Validation Error)
        $resThuHoiFail = $this->actingAs($this->userTruongKhoa)
            ->post(route('truongkhoa.ketqua.thuhoi_detai'), [
                'MaHoSo' => $hoSo->MaHoSo,
                'ly_do' => '' // Rỗng
            ]);
        $resThuHoiFail->assertSessionHasErrors(['ly_do']);

        // 3.3. Thu hồi CÓ lý do hợp lệ => Thành công, hoàn trả về DaDuPhieu
        $resThuHoiOk = $this->actingAs($this->userTruongKhoa)
            ->post(route('truongkhoa.ketqua.thuhoi_detai'), [
                'MaHoSo' => $hoSo->MaHoSo,
                'ly_do' => 'Phát hiện sai sót biên bản chấm điểm cần thẩm định lại'
            ]);

        $resThuHoiOk->assertSessionHas('success');
        $this->assertEquals('DaDuPhieu', $hoSo->fresh()->TrangThaiKetQua);

        // Kiểm tra audit log đã ghi nhận action THU_HOI
        $logThuHoi = LichSuCongBoKetQua::where('PhamVi', 'DE_TAI')
            ->where('MaHoSo', $hoSo->MaHoSo)
            ->where('LoaiHanhDong', 'THU_HOI')
            ->latest('id')
            ->first();
        $this->assertNotNull($logThuHoi);
        $this->assertStringContainsString('Phát hiện sai sót', $logThuHoi->LyDo);
    }

    /**
     * 4. Modal Lịch sử công bố (Audit Log View)
     */
    public function test_api_lich_su_audit_log_tra_ve_thanh_cong()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // Tạo 1 bản ghi audit log
        LichSuCongBoKetQua::create([
            'LoaiHanhDong' => 'CONG_BO',
            'PhamVi' => 'HOI_DONG',
            'MaHoiDong' => 'HD01',
            'NguoiThucHien' => 'Trưởng Khoa',
            'MaTK' => $this->userTruongKhoa->MaTK,
            'LyDo' => 'Test log audit API',
            'SoLuongDeTai' => 1,
            'SoLuongSinhVien' => 3,
        ]);

        $response = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.lichsu'));

        $response->assertStatus(200);
        $response->assertSee('Lịch Sử Kiểm Toán');
        $response->assertSee('Test log audit API');
    }

    /**
     * 5. Modal Bảng 3: Chi tiết điểm từng đề tài theo Rubric
     */
    public function test_modal_rubric_chi_tiet_tra_ve_diem_cac_thanh_vien()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $hoSo = HoSoBaoVe::find('HS01');
        if (!$hoSo) {
            $this->markTestSkipped('Không có hồ sơ HS01.');
        }

        $response = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.chitiet_rubric', ['maHoSo' => $hoSo->MaHoSo]));

        $response->assertStatus(200);
        $response->assertSee('Bảng 3: Chi Tiết Đánh Giá Rubric');
        $response->assertSee($hoSo->MaHoSo);
    }

    /**
     * 6. Xuất Báo Cáo In và Xuất File Excel / CSV
     */
    public function test_xuat_bao_cao_in_va_xuat_excel()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // 6.1. Báo cáo In chuẩn A4
        $resPrint = $this->actingAs($this->userTruongKhoa)
            ->get(route('truongkhoa.ketqua.in_baocao'));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('BÁO CÁO TỔNG HỢP KẾT QUẢ ĐÁNH GIÁ KHÓA LUẬN TỐT NGHIỆP');
        $resPrint->assertSee('window.print()');

        // 6.2. Xuất Excel / CSV
        $resExport = $this->actingAs($this->userTruongKhoa)
            ->get(route('truongkhoa.ketqua.export'));
        $resExport->assertStatus(200);
        $this->assertTrue($resExport->headers->has('content-disposition'));
        $this->assertStringContainsString('.csv', $resExport->headers->get('content-disposition'));
    }
}
