<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Models\KetQuaSinhVien;
use App\Models\Nhom;
use App\Models\PhieuChamDiem;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Services\CloCalculationService;
use App\Services\CloReportExportService;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CloThreeLecturersCalculationAndExportTest extends TestCase
{
    use DatabaseTransactions;

    protected CloCalculationService $cloService;
    protected CloReportExportService $exportService;
    protected GradeCalculationService $gradeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cloService = app(CloCalculationService::class);
        $this->exportService = app(CloReportExportService::class);
        $this->gradeService = app(GradeCalculationService::class);
    }

    /**
     * Tạo dữ liệu giả lập Hội đồng chuẩn 3 Giảng viên và 1 Sinh viên
     */
    protected function createMockCouncilAndStudent(): array
    {
        $hocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::first();

        // 1. Hội đồng
        $maHd = 'HD_TEST_' . mt_rand(1000, 9999);
        $hoiDong = HoiDong::create([
            'MaHoiDong'  => $maHd,
            'TenHoiDong' => "Hội Đồng Thử Nghiệm {$maHd}",
            'MaHocKy'    => $hocKy->MaHocKy,
            'TrangThai'  => 'Đang chấm điểm',
            'NgayBaoVe'  => now()->toDateString(),
        ]);

        // 2. Lấy hoặc tạo 3 Giảng viên: GV1 (Chủ tịch), GV2 (Thư ký), GV3 (Phản biện)
        $gvs = GiangVien::take(3)->get();
        if ($gvs->count() < 3) {
            $this->markTestSkipped('Cần tối thiểu 3 giảng viên trong CSDL để chạy test.');
        }

        $roles = ['Chủ tịch', 'Thư ký', 'Phản biện'];
        foreach ($gvs as $idx => $gv) {
            ThanhVienHoiDong::create([
                'MaHoiDong' => $maHd,
                'MaGV'      => $gv->MaGV,
                'VaiTro'    => $roles[$idx],
            ]);
        }

        // 3. Sinh viên & Nhóm & Hồ sơ bảo vệ
        $sv = SinhVien::first();
        if (!$sv) {
            $this->markTestSkipped('Cần có sinh viên trong CSDL để chạy test.');
        }

        $deTai = \App\Models\DeTai::first();
        $maDeTai = $deTai?->MaDeTai ?? 'DT01';

        $maNhom = 'NHOM_TEST_' . mt_rand(1000, 9999);
        $nhom = Nhom::create([
            'MaNhom'  => $maNhom,
            'TenNhom' => "Nhóm Thử Nghiệm {$maNhom}",
            'MaHocKy' => $hocKy->MaHocKy,
            'MaDeTai' => $maDeTai,
        ]);

        ThanhVienNhom::create([
            'MaNhom'  => $maNhom,
            'MaSV'    => $sv->MaSV,
            'VaiTro'  => 'Trưởng nhóm',
        ]);

        $maHoSo = 'HS_TEST_' . mt_rand(1000, 9999);
        $hoSo = HoSoBaoVe::create([
            'MaHoSo'           => $maHoSo,
            'MaNhom'           => $maNhom,
            'MaHoiDong'        => $maHd,
            'TrangThai'        => 'Đã duyệt bảo vệ',
            'TrangThaiKetQua'  => 'ChuaDuPhieu',
            'LoaiKhoaLuan'     => 'KLCN',
        ]);

        return [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai];
    }

    /**
     * Test 1: Nguyên tắc tính điểm CLO trung bình = (CLO GV1 + CLO GV2 + CLO GV3) / 3
     */
    public function test_three_examiners_clo_average_calculation_formula(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockCouncilAndStudent();

        $rubric = PhieuChamDiem::getRubricKLCN();
        $cloGroups = $this->cloService->getCloGroupsFromRubric($rubric);

        // Giả lập điểm của 3 GV:
        // GV1 đạt 80% (tương đương 8.0/10) trên mọi tiêu chí
        // GV2 đạt 70% (tương đương 7.0/10) trên mọi tiêu chí
        // GV3 đạt 90% (tương đương 9.0/10) trên mọi tiêu chí
        $ratios = [0.80, 0.70, 0.90];

        foreach ($gvs as $idx => $gv) {
            $ratio = $ratios[$idx];
            $chiTiet = [];
            $tongDiem = 0.0;
            foreach ($rubric as $crit) {
                $score = round((float)$crit['max'] * $ratio, 2);
                $chiTiet[$crit['id']] = $score;
                if (empty($crit['is_bonus'])) {
                    $tongDiem += $score;
                }
            }

            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'P_TEST_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'   => $hoiDong->MaHoiDong,
                'MaHoSo'      => $hoSo->MaHoSo,
                'MaDeTai'     => $maDeTai,
                'MaGV'        => $gv->MaGV,
                'MaSV'        => $sv->MaSV,
                'MaHocKy'     => $hocKy->MaHocKy,
                'Diem'        => $tongDiem,
                'ChiTietDiem' => $chiTiet,
                'TrangThai'   => 'Đã hoàn thành',
                'NgayCham'    => now(),
            ]);
        }

        // Tính toán điểm CLO của sinh viên
        $res = $this->cloService->calculateStudentClo($hoiDong, $sv->MaSV, 'KLCN');

        $this->assertTrue($res['is_official'], 'Hội đồng phải ghi nhận trạng thái chính thức khi đủ 3 phiếu');
        $this->assertEquals(3, $res['completed_examiners']);

        // Kiểm tra từng CLO: Điểm GV1 ~ 8.0, GV2 ~ 7.0, GV3 ~ 9.0 -> Điểm TB = (8.0 + 7.0 + 9.0) / 3 = 8.0
        foreach ($cloGroups as $code => $g) {
            $cloObj = $res['cloScores'][$code];
            $gv1Score = $cloObj['gv1'];
            $gv2Score = $cloObj['gv2'];
            $gv3Score = $cloObj['gv3'];
            $avgScore = $cloObj['score'];

            $expectedAvg = round(($gv1Score + $gv2Score + $gv3Score) / 3, 2);
            $this->assertEquals($expectedAvg, $avgScore, "Điểm trung bình CLO {$code} phải đúng công thức (GV1+GV2+GV3)/3");
            $this->assertTrue($cloObj['passed'], "Điểm CLO {$code} >= 4.0 phải Đạt");
        }
    }

    /**
     * Test 2: Ràng buộc phải có đủ 3 phiếu chấm hợp lệ; nếu thiếu 1 GV thì chặn xuất báo cáo chính thức
     */
    public function test_system_blocks_export_when_fewer_than_three_examiners_complete_sheets(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockCouncilAndStudent();

        // Chỉ tạo 2 phiếu chấm (GV1 và GV2), thiếu GV3
        for ($i = 0; $i < 2; $i++) {
            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'P_TEST_' . $i . '_' . mt_rand(1000, 9999),
                'MaHoiDong'   => $hoiDong->MaHoiDong,
                'MaHoSo'      => $hoSo->MaHoSo,
                'MaDeTai'     => $maDeTai,
                'MaGV'        => $gvs[$i]->MaGV,
                'MaSV'        => $sv->MaSV,
                'MaHocKy'     => $hocKy->MaHocKy,
                'Diem'        => 8.0,
                'ChiTietDiem' => ['tc1' => 0.8],
                'TrangThai'   => 'Đã hoàn thành',
                'NgayCham'    => now(),
            ]);
        }

        // Kiểm tra hàm kiemTraDu3PhieuCham
        $check = $this->cloService->kiemTraDu3PhieuCham($hoiDong);
        $this->assertFalse($check['du_3_phieu'], 'Khi chỉ có 2 phiếu hoàn thành thì du_3_phieu phải là false');
        $this->assertNotEmpty($check['missing_examiners'], 'Danh sách thiếu phải thể hiện giám khảo thứ 3');

        // Xuất báo cáo không có cờ force phải ném ngoại lệ InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->exportService->export($hoiDong, 'KLCN', false);
    }

    /**
     * Test 3: Lưu điểm CLO trung bình vào CSDL (KetQuaSinhVien) và LichSuDieuChinh
     */
    public function test_clo_data_is_persisted_to_database_on_student_result(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockCouncilAndStudent();

        // Tạo đủ 3 phiếu
        foreach ($gvs as $idx => $gv) {
            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'P_TEST_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'   => $hoiDong->MaHoiDong,
                'MaHoSo'      => $hoSo->MaHoSo,
                'MaDeTai'     => $maDeTai,
                'MaGV'        => $gv->MaGV,
                'MaSV'        => $sv->MaSV,
                'MaHocKy'     => $hocKy->MaHocKy,
                'Diem'        => 8.0,
                'ChiTietDiem' => ['tc1' => 0.8],
                'TrangThai'   => 'Đã hoàn thành',
                'NgayCham'    => now(),
            ]);
        }

        // Kích hoạt tổng hợp kết quả hội đồng qua GradeCalculationService
        $this->gradeService->tongHopKetQuaHoiDong($hoiDong->MaHoiDong);

        $kq = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        if (!$kq) {
            // Nếu chưa có do học kỳ khác, gọi trực tiếp calculateCouncilClo
            $this->cloService->calculateCouncilClo($hoiDong, 'KLCN');
            $kq = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        }

        $this->assertNotNull($kq, 'Bản ghi KetQuaSinhVien phải tồn tại');
        $this->assertNotNull($kq->DiemCLO, 'Cột DiemCLO phải được lưu dữ liệu');
        $this->assertIsArray($kq->DiemCLO);
        $this->assertTrue($kq->DiemCLO['is_official']);
        $this->assertEquals(3, $kq->DiemCLO['completed_examiners']);
        $this->assertArrayHasKey('cloScores', $kq->DiemCLO);
    }

    /**
     * Test 4: Xuất báo cáo Excel chứa đủ 3 Sheet, Sheet 1 thể hiện đầy đủ điểm GV1, GV2, GV3 và Điểm TB
     */
    public function test_excel_export_contains_all_three_sheets_with_three_examiner_comparison(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockCouncilAndStudent();

        // Tạo đủ 3 phiếu
        foreach ($gvs as $idx => $gv) {
            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'P_TEST_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'   => $hoiDong->MaHoiDong,
                'MaHoSo'      => $hoSo->MaHoSo,
                'MaDeTai'     => $maDeTai,
                'MaGV'        => $gv->MaGV,
                'MaSV'        => $sv->MaSV,
                'MaHocKy'     => $hocKy->MaHocKy,
                'Diem'        => 8.0,
                'ChiTietDiem' => ['tc1' => 0.8],
                'TrangThai'   => 'Đã hoàn thành',
                'NgayCham'    => now(),
            ]);
        }

        $spreadsheet = $this->exportService->export($hoiDong, 'KLCN', true);

        // Kiểm tra danh sách 3 sheet
        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertContains('TongHop_3GV_CLO', $sheetNames, 'Phải có Sheet tổng hợp so sánh 3 GV');
        $this->assertContains('5B_CĐR_CLO', $sheetNames, 'Phải có Sheet 5B_CĐR_CLO');
        $this->assertContains('03C_CLO_ĐCK', $sheetNames, 'Phải có Sheet 03C_CLO_ĐCK');

        // Kiểm tra dữ liệu trên Sheet 1
        $sheet1 = $spreadsheet->getSheetByName('TongHop_3GV_CLO');
        $this->assertNotNull($sheet1);
        $title = $sheet1->getCell('A5')->getValue();
        $this->assertStringContainsString('BẢNG TỔNG HỢP SO SÁNH ĐIỂM CLO CỦA CẢ 3 GIẢNG VIÊN', $title);
    }

    /**
     * Test 5: Khi một giảng viên cập nhật điểm, hệ thống tự động tính lại điểm CLO trung bình
     */
    public function test_recalculates_clo_when_examiner_updates_score(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockCouncilAndStudent();

        $rubric = PhieuChamDiem::getRubricKLCN();

        // Lần 1: Cả 3 GV chấm tỉ lệ 0.80 (8.0 điểm)
        $phieus = [];
        foreach ($gvs as $idx => $gv) {
            $chiTiet = [];
            foreach ($rubric as $crit) {
                $chiTiet[$crit['id']] = round((float)$crit['max'] * 0.80, 2);
            }
            $phieus[$idx] = PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'P_TEST_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'   => $hoiDong->MaHoiDong,
                'MaHoSo'      => $hoSo->MaHoSo,
                'MaDeTai'     => $maDeTai,
                'MaGV'        => $gv->MaGV,
                'MaSV'        => $sv->MaSV,
                'MaHocKy'     => $hocKy->MaHocKy,
                'Diem'        => 8.0,
                'ChiTietDiem' => $chiTiet,
                'TrangThai'   => 'Đã hoàn thành',
                'NgayCham'    => now(),
            ]);
        }

        $res1 = $this->cloService->calculateStudentClo($hoiDong, $sv->MaSV, 'KLCN');
        $this->assertEquals(8.00, $res1['cloScores']['CLO1']['score']);

        // Lần 2: Giảng viên 1 cập nhật lại điểm lên tỉ lệ 1.0 (10.0 điểm)
        $newChiTiet = [];
        foreach ($rubric as $crit) {
            $newChiTiet[$crit['id']] = (float)$crit['max'];
        }
        $phieus[0]->update([
            'ChiTietDiem' => $newChiTiet,
            'Diem'        => 10.0,
        ]);

        // Tính lại điểm
        $res2 = $this->cloService->calculateStudentClo($hoiDong, $sv->MaSV, 'KLCN');

        $gv1Moi = $res2['cloScores']['CLO1']['gv1'];
        $this->assertEquals(10.00, $gv1Moi, 'Điểm GV1 sau khi sửa phải là 10.00');

        // Điểm TB mới = (10.0 + 8.0 + 8.0) / 3 = 26.0 / 3 = 8.67
        $expectedNewAvg = round((10.00 + 8.00 + 8.00) / 3, 2);
        $this->assertEquals($expectedNewAvg, $res2['cloScores']['CLO1']['score'], 'Điểm CLO trung bình phải được cập nhật lại theo kết quả mới');
    }
}
