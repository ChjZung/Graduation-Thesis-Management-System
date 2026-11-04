<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Models\KetQuaSinhVien;
use App\Models\LichSuChinhSuaDiem;
use App\Models\Nhom;
use App\Models\PhieuChamDiem;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Services\CloCalculationService;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScoreEditingPolicyAndAuditLogTest extends TestCase
{
    use DatabaseTransactions;

    protected CloCalculationService $cloService;
    protected GradeCalculationService $gradeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cloService = app(CloCalculationService::class);
        $this->gradeService = app(GradeCalculationService::class);
    }

    protected function createMockData(): array
    {
        $hocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::first();

        $maHd = 'HD_AUDIT_' . mt_rand(1000, 9999);
        $sysNow = HoiDong::getSystemNow();
        $hoiDong = HoiDong::create([
            'MaHoiDong'  => $maHd,
            'TenHoiDong' => "Hội Đồng Kiểm Toán {$maHd}",
            'MaHocKy'    => $hocKy->MaHocKy,
            'TrangThai'  => 'Đang chấm điểm',
            'NgayBaoVe'  => $sysNow->toDateString(),
            'ThoiGianBatDau'         => $sysNow->copy()->subHours(2),
            'ThoiGianKetThuc'        => $sysNow->copy()->addHours(6),
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(1),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->addDays(2),
        ]);

        $gvs = GiangVien::take(3)->get();
        if ($gvs->count() < 3) {
            $this->markTestSkipped('Cần tối thiểu 3 giảng viên để chạy test.');
        }

        $roles = ['Chủ tịch', 'Thư ký', 'Phản biện'];
        foreach ($gvs as $idx => $gv) {
            ThanhVienHoiDong::create([
                'MaHoiDong' => $maHd,
                'MaGV'      => $gv->MaGV,
                'VaiTro'    => $roles[$idx],
            ]);
        }

        $sv = SinhVien::first();
        if (!$sv) {
            $this->markTestSkipped('Cần có sinh viên để chạy test.');
        }

        $deTai = \App\Models\DeTai::first();
        $maDeTai = $deTai?->MaDeTai ?? 'DT01';

        $maNhom = 'NHOM_AUDIT_' . mt_rand(1000, 9999);
        $nhom = Nhom::create([
            'MaNhom'  => $maNhom,
            'TenNhom' => "Nhóm Kiểm Toán {$maNhom}",
            'MaHocKy' => $hocKy->MaHocKy,
            'MaDeTai' => $maDeTai,
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $maNhom,
            'MaSV'   => $sv->MaSV,
            'VaiTro' => 'Trưởng nhóm',
        ]);

        $maHoSo = 'HS_AUDIT_' . mt_rand(1000, 9999);
        $hoSo = HoSoBaoVe::create([
            'MaHoSo'          => $maHoSo,
            'MaNhom'          => $maNhom,
            'MaDeTai'         => $maDeTai,
            'MaHoiDong'       => $maHd,
            'TrangThai'       => 'Đã duyệt bảo vệ',
            'TrangThaiKetQua' => 'ChuaDuPhieu',
            'LoaiKhoaLuan'    => 'KLCN',
        ]);

        return [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai];
    }

    /**
     * Yêu cầu 1: Giảng viên chỉ sửa phiếu của mình & KHÔNG được sửa nhận xét (nhận xét cũ được bảo toàn)
     */
    public function test_lecturer_can_edit_scores_but_comments_remain_strictly_unchanged(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        $rubric = PhieuChamDiem::getRubricKLCN();
        $initialChiTiet = [];
        foreach ($rubric as $crit) {
            $initialChiTiet[$crit['id']] = round((float)$crit['max'] * 0.70, 2);
        }

        // Tạo phiếu ban đầu đã xác nhận với nhận xét ban đầu
        $originalComment = 'Nhận xét ban đầu: Sinh viên hoàn thành tốt mục tiêu đề tài.';
        $phieu1 = PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 7.0,
            'ChiTietDiem'     => $initialChiTiet,
            'NhanXet'         => $originalComment,
            'TrangThai'       => 'Đã hoàn thành',
            'NgayCham'        => now(),
            'NgayXacNhan'     => now(),
        ]);

        // Giảng viên đăng nhập và gửi request sửa điểm với điểm mới và nhận xét giả mạo mới
        $newChiTiet = $initialChiTiet;
        $firstCritId = $rubric[0]['id'];
        $newChiTiet[$firstCritId] = round((float)$rubric[0]['max'] * 0.95, 2); // Tăng điểm tiêu chí đầu tiên

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                    'NhanXet'     => 'CỐ TÌNH SỬA ĐỔI NHẬN XÉT TRÁI QUY CHẾ',
                ],
            ],
        ]);

        $response->assertRedirect();

        // Kiểm tra trong CSDL:
        $updatedPhieu = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)
            ->where('MaGV', $gv1->MaGV)
            ->where('MaSV', $sv->MaSV)
            ->first();

        // 1. Nhận xét phải được giữ nguyên nhận xét ban đầu!
        $this->assertEquals($originalComment, $updatedPhieu->NhanXet, 'Nhận xét ban đầu phải được giữ nguyên không thay đổi khi sửa điểm.');

        // 2. Điểm tiêu chí phải được cập nhật giá trị mới
        $this->assertEquals($newChiTiet[$firstCritId], $updatedPhieu->ChiTietDiem[$firstCritId], 'Điểm tiêu chí phải được cập nhật mới.');

        // 3. Phải ghi nhận vết thay đổi trong LichSuChinhSuaDiem
        $log = LichSuChinhSuaDiem::where('MaPhieuChamDiem', $phieu1->MaPhieuChamDiem)
            ->where('MaSV', $sv->MaSV)
            ->where('MaGV', $gv1->MaGV)
            ->first();

        $this->assertNotNull($log, 'Hệ thống phải tự động ghi nhận lịch sử chỉnh sửa điểm.');
        $this->assertEquals($initialChiTiet[$firstCritId], $log->DiemCu, 'Điểm cũ trong lịch sử phải chính xác.');
        $this->assertEquals($newChiTiet[$firstCritId], $log->DiemMoi, 'Điểm mới trong lịch sử phải chính xác.');
    }

    /**
     * Yêu cầu 2: Hết thời hạn chỉnh sửa -> Tự động khóa và hiển thị thông báo
     */
    public function test_cannot_edit_when_editing_window_has_expired(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Đặt thời hạn chỉnh sửa đã kết thúc 2 tiếng trước
        $sysNow = HoiDong::getSystemNow();
        $hoiDong->update([
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(2),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->subHours(2),
        ]);

        PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 8.0,
            'ChiTietDiem'     => [],
            'NhanXet'         => 'Test',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => [],
                ],
            ],
        ]);

        // Phải bị chặn với lỗi hết thời hạn chỉnh sửa điểm
        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('hết thời hạn chỉnh sửa điểm', $errorMessage);
    }

    /**
     * Yêu cầu 3: Khi quản trị viên công bố điểm chính thức -> Tự động khóa quyền sửa điểm của cả 3 giảng viên
     */
    public function test_cannot_edit_when_scores_are_officially_published(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Đã công bố kết quả chính thức
        $hoSo->update(['TrangThaiKetQua' => 'DaCongBo']);
        $hoiDong->update(['TrangThaiCongBo' => 'Đã công bố']);

        PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 8.0,
            'ChiTietDiem'     => [],
            'NhanXet'         => 'Test',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => [],
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('công bố', $errorMessage);
    }

    /**
     * Yêu cầu 3 & 5: Quản trị viên mở khóa khi có sai sót có lý do bắt buộc -> Giảng viên sửa được điểm
     */
    public function test_admin_can_unlock_with_reason_and_gv_can_edit(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Khóa do đã hết hạn
        $sysNow = HoiDong::getSystemNow();
        $hoiDong->update([
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(3),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->subDay(),
        ]);

        $rubric = PhieuChamDiem::getRubricKLCN();
        $initialChiTiet = [];
        foreach ($rubric as $crit) {
            $initialChiTiet[$crit['id']] = round((float)$crit['max'] * 0.70, 2);
        }

        $phieu1 = PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 7.0,
            'ChiTietDiem'     => $initialChiTiet,
            'NhanXet'         => 'Nhận xét cố định',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        // Tạo tài khoản Trưởng khoa / Quản trị viên
        $truongKhoaTk = TaiKhoan::create([
            'MaTK'        => 'TK_TRUONGKHOA_' . mt_rand(100, 999),
            'TenDangNhap' => 'TK_CNTT_truongkhoa',
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT05', // Trưởng khoa
            'TrangThai'   => 1,
        ]);

        // Trưởng khoa mở khóa với lý do
        $reasonUnlock = 'Xử lý sai sót: Giám khảo chấm nhầm điểm tiêu chí 3 theo biên bản họp khoa';
        $resUnlock = $this->actingAs($truongKhoaTk)->post(route('truongkhoa.ketqua.mokhoaphieu'), [
            'MaHoiDong'   => $hoiDong->MaHoiDong,
            'ly_do'       => $reasonUnlock,
            'han_sua_moi' => $sysNow->copy()->addDay()->toDateTimeString(),
        ]);
        $resUnlock->assertRedirect();

        $hoiDong->refresh();
        $this->assertTrue($hoiDong->TrangThaiMoKhoaSuaDiem, 'Cờ mở khóa phải được bật.');
        $this->assertEquals($reasonUnlock, $hoiDong->LyDoMoKhoaSuaDiem, 'Lý do mở khóa phải được lưu trữ.');

        // Giảng viên bây giờ có thể sửa điểm thành công
        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $newChiTiet = $initialChiTiet;
        $firstCritId = $rubric[0]['id'];
        $newChiTiet[$firstCritId] = round((float)$rubric[0]['max'] * 0.90, 2);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Yêu cầu 5: Lịch sử chỉnh sửa không cho phép xóa (Immutable Audit Log)
     */
    public function test_score_edit_audit_log_cannot_be_deleted(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();

        $log = LichSuChinhSuaDiem::create([
            'MaPhieuChamDiem' => 'PCD_TEST_DELETE',
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaSV'            => $sv->MaSV,
            'MaGV'            => $gvs[0]->MaGV,
            'NguoiThucHien'   => $gvs[0]->HoTen,
            'MaCLO'           => 'CLO1',
            'TenTieuChi'      => 'Tiêu chí 1',
            'DiemCu'          => 7.0,
            'DiemMoi'         => 8.5,
            'ThoiGian'        => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Không được phép xóa lịch sử chỉnh sửa điểm');

        $log->delete();
    }

    /**
     * Yêu cầu 4: Đồng bộ tính điểm trung bình CLO của 3 giảng viên sau khi chỉnh sửa
     */
    public function test_three_examiners_average_clo_is_recalculated_after_score_edit(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();

        $rubric = PhieuChamDiem::getRubricKLCN();

        // 3 Giảng viên ban đầu chấm: GV1: 7.0, GV2: 8.0, GV3: 9.0
        // Điểm TB ban đầu = (7.0 + 8.0 + 9.0) / 3 = 8.0
        $ratios = [0.70, 0.80, 0.90];
        foreach ($gvs as $idx => $gv) {
            $chiTiet = [];
            foreach ($rubric as $crit) {
                $chiTiet[$crit['id']] = round((float)$crit['max'] * $ratios[$idx], 2);
            }
            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'PCD_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'       => $hoiDong->MaHoiDong,
                'MaHoSo'          => $hoSo->MaHoSo,
                'MaDeTai'         => $maDeTai,
                'MaHocKy'         => $hocKy->MaHocKy,
                'MaGV'            => $gv->MaGV,
                'MaSV'            => $sv->MaSV,
                'LoaiKhoaLuan'    => 'KLCN',
                'Diem'            => $ratios[$idx] * 10,
                'ChiTietDiem'     => $chiTiet,
                'NhanXet'         => "Nhận xét của GV {$gv->MaGV}",
                'TrangThai'       => 'Đã hoàn thành',
            ]);
        }

        // Tính điểm ban đầu
        $this->gradeService->tongHopKetQuaHoiDong($hoiDong->MaHoiDong);
        $kqBanDau = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        $this->assertNotNull($kqBanDau, 'KetQuaSinhVien phải tồn tại sau khi tổng hợp điểm');
        $this->assertEquals(8.0, round($kqBanDau->DiemHoiDongTB, 2));

        // GV1 chỉnh sửa điểm từ 7.0 lên 10.0 (1.00 ratio trên mọi tiêu chí)
        // Điểm TB mới kỳ vọng = (10.0 + 8.0 + 9.0) / 3 = 9.00
        $gv1 = $gvs[0];
        $newChiTiet = [];
        foreach ($rubric as $crit) {
            $newChiTiet[$crit['id']] = (float)$crit['max'];
        }

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $res = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                ],
            ],
        ]);

        $res->assertRedirect();

        // Kiểm tra KetQuaSinhVien sau khi sửa
        $kqMoi = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        $this->assertEquals(9.0, round($kqMoi->DiemHoiDongTB, 2), 'Điểm trung bình Hội đồng phải được tự động tính lại sau khi giảng viên sửa điểm.');

        // Kiểm tra DiemCLO được đồng bộ
        $cloData = $kqMoi->DiemCLO;
        $this->assertNotEmpty($cloData, 'Dữ liệu CLO phải được tự động đồng bộ.');
        $this->assertArrayHasKey('cloScores', $cloData);
        $this->assertEquals(3, $cloData['completed_examiners']);
        $this->assertTrue($cloData['is_official']);
    }
}
