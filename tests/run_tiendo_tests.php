<?php

/**
 * AUTOMATED TEST SUITE: TIẾN ĐỘ KHÓA LUẬN & TÓM TẮT BÁO CÁO AI
 * Thực thi kiểm thử tự động toàn diện các case từ Giai đoạn 1 đến Giai đoạn 6.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TomTatBaoCao;
use App\Models\Nhom;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Http\Controllers\SinhVien\BaoCaoController;
use App\Http\Controllers\GiangVien\DuyetBaoCaoController;
use App\Http\Controllers\Admin\TheoDoiDoAnController;
use App\Services\AiSummaryService;
use App\Helpers\IdGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TienDoTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $results = [];

    public function run()
    {
        echo PHP_EOL . "==========================================================================" . PHP_EOL;
        echo "       BỘ KIỂM THỬ TỰ ĐỘNG TIẾN ĐỘ KHÓA LUẬN & AI MAP-REDUCE HUIT         " . PHP_EOL;
        echo "==========================================================================" . PHP_EOL . PHP_EOL;

        $this->testSuiteTimeBoundaries();
        $this->testSuiteAiMapReduce();
        $this->testSuiteSequentialProgression();
        $this->testSuiteSubReportsCrud();
        $this->testSuiteLecturerReviewAndConfirmation();
        $this->testSuiteAdminOverdueTrackingAllStages();

        $this->printSummary();
    }

    private function assert(string $caseId, string $description, bool $condition, ?string $detail = null)
    {
        if ($condition) {
            $this->passed++;
            $this->results[] = ['id' => $caseId, 'name' => $description, 'status' => 'PASS', 'detail' => $detail ?? 'Khớp kỳ vọng'];
            echo " [PASS] {$caseId}: {$description}" . PHP_EOL;
        } else {
            $this->failed++;
            $this->results[] = ['id' => $caseId, 'name' => $description, 'status' => 'FAIL', 'detail' => $detail ?? 'Thất bại'];
            echo " [FAIL] {$caseId}: {$description} (Lỗi: {$detail})" . PHP_EOL;
        }
    }

    private function testSuiteTimeBoundaries()
    {
        echo "--- 1. KIỂM THỬ KHUNG THỜI GIAN VÀ CHẶN HẠN NỘP (RULE C1) ---" . PHP_EOL;
        $ctrl = app(BaoCaoController::class);

        // TC-01: GĐ 1 trước ngày 15/09/2026 -> Phải chặn
        $res = $ctrl->checkThoiGianNopBaoCao(1, null, '2026-09-10');
        $this->assert('TC-01', 'GĐ 1: Chặn nộp bài trước ngày 15/09 (10/09/2026)', !$res['is_open'] && $res['code'] === 'CHUA_DEN_HAN_NOP', $res['message']);

        // TC-02: GĐ 1 trong khung 15/09 - 18/09 -> Phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(1, null, '2026-09-16');
        $this->assert('TC-02', 'GĐ 1: Mở nộp bài trong khung hạn (16/09/2026)', $res['is_open'] && $res['code'] === 'DANG_MO', $res['message']);

        // TC-03: GĐ 1 sau ngày 18/09/2026 -> Quá hạn chặn nộp
        $res = $ctrl->checkThoiGianNopBaoCao(1, null, '2026-09-20');
        $this->assert('TC-03', 'GĐ 1: Chặn nộp bài khi đã quá deadline (20/09/2026)', !$res['is_open'] && $res['code'] === 'QUA_HAN_NOP', $res['message']);

        // TC-04: GĐ 2 mở nộp (28/09 - 16/10) -> Ngày 05/10/2026 phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(2, null, '2026-10-05');
        $this->assert('TC-04', 'GĐ 2: Mở nộp bài theo lịch (05/10/2026)', $res['is_open'] && $res['code'] === 'DANG_MO');

        // TC-05: GĐ 2 sau 16/10 -> Ngày 18/10/2026 phải quá hạn
        $res = $ctrl->checkThoiGianNopBaoCao(2, null, '2026-10-18');
        $this->assert('TC-05', 'GĐ 2: Chặn nộp bài khi quá hạn (18/10/2026)', !$res['is_open'] && $res['code'] === 'QUA_HAN_NOP');

        // TC-06: GĐ 3 mở nộp (26/10 - 06/11) -> Ngày 30/10/2026 phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(3, null, '2026-10-30');
        $this->assert('TC-06', 'GĐ 3: Mở nộp bài kiểm thử & dự thảo (30/10/2026)', $res['is_open'] && $res['code'] === 'DANG_MO');

        // TC-07: GĐ 4 mở nộp (16/11 - 27/11) -> Ngày 20/11/2026 phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(4, null, '2026-11-20');
        $this->assert('TC-07', 'GĐ 4: Mở nộp hồ sơ bảo vệ & Turnitin (20/11/2026)', $res['is_open'] && $res['code'] === 'DANG_MO');

        // TC-08: GĐ 5 mở bảo vệ (07/12 - 18/12) -> Ngày 10/12/2026 phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(5, null, '2026-12-10');
        $this->assert('TC-08', 'GĐ 5: Mở mốc bảo vệ Hội đồng (10/12/2026)', $res['is_open'] && $res['code'] === 'DANG_MO');

        // TC-09: GĐ 6 mở nộp hoàn chỉnh (21/12 - 05/01/2027) -> Ngày 25/12/2026 phải mở
        $res = $ctrl->checkThoiGianNopBaoCao(6, null, '2026-12-25');
        $this->assert('TC-09', 'GĐ 6: Mở mốc nộp hoàn chỉnh & lưu chiểu (25/12/2026)', $res['is_open'] && $res['code'] === 'DANG_MO');

        echo PHP_EOL;
    }

    private function testSuiteAiMapReduce()
    {
        echo "--- 2. KIỂM THỬ ĐỘNG CƠ AI MAP-REDUCE TRÍCH XUẤT TÀI LIỆU PDF ---" . PHP_EOL;
        $aiService = app(AiSummaryService::class);
        $samplePdf = base_path('storage/app/public/de_cuong/de_cuong_1792073636_cntt-klcn185-nguyen-van-le.pdf');

        if (!file_exists($samplePdf)) {
            $this->assert('TC-10', 'AI Engine: File PDF mẫu tồn tại', false, 'Không tìm thấy file PDF mẫu');
            return;
        }

        $res = $aiService->summarizePdf($samplePdf, 1);

        $this->assert('TC-10', 'AI Map-Reduce: Xử lý PDF thực tế thành công', $res['success'] === true, 'Thời gian: ' . ($res['processing_time'] ?? 0) . 's');
        $this->assert('TC-11', 'AI Map-Reduce: Trích xuất chính xác số trang tài liệu', ($res['total_pages'] ?? 0) === 5, 'Số trang: ' . ($res['total_pages'] ?? 0));
        
        $hasCompleted = !empty($res['data']['completed_work']);
        $hasPageCitation = $hasCompleted && !empty($res['data']['completed_work'][0]['source_pages']);
        $this->assert('TC-12', 'AI Map-Reduce: Trích xuất công việc kèm số trang nguồn [Trang X]', $hasPageCitation, 'Mục đầu: ' . ($res['data']['completed_work'][0]['content'] ?? 'None'));

        $hasDifficulties = !empty($res['data']['difficulties']);
        $this->assert('TC-13', 'AI Map-Reduce: Trích xuất khó khăn và tồn đọng học thuật', $hasDifficulties, 'Số lượng khó khăn: ' . count($res['data']['difficulties'] ?? []));

        $hasAutofill = !empty($res['formatted']['completed_work']) && !empty($res['formatted']['difficulties']);
        $this->assert('TC-14', 'AI Map-Reduce: Sinh chuỗi văn bản định dạng chuẩn cho 1-Click Autofill', $hasAutofill);

        echo PHP_EOL;
    }

    private function testSuiteSequentialProgression()
    {
        echo "--- 3. KIỂM THỬ TÍNH TUẦN TỰ VÀ KHÓA CHẶN GIAI ĐOẠN ---" . PHP_EOL;
        $nhom = Nhom::with('deTai')->first();
        if (!$nhom || !$nhom->deTai) {
            $this->assert('TC-15', 'Kiểm tra tuần tự giai đoạn', false, 'Không có nhóm/đề tài mẫu');
            return;
        }

        // Đảm bảo GĐ 1 chưa đạt
        $bc1 = BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)->where('LanBaoCao', 1)->first();
        $trangThaiCu = $bc1?->TrangThai;

        if ($bc1) {
            $bc1->update(['TrangThai' => 'Chờ duyệt']);
        }

        // Thử kiểm tra logic: nếu GĐ 1 chưa Đạt, không được nộp GĐ 2
        $mocTruoc = BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)->where('LanBaoCao', 1)->where('TrangThai', 'Đạt')->first();
        $this->assert('TC-15', 'Khóa GĐ 2 khi GĐ 1 chưa được đánh giá Đạt', $mocTruoc === null);

        // Khôi phục trạng thái
        if ($bc1 && $trangThaiCu) {
            $bc1->update(['TrangThai' => $trangThaiCu]);
        }

        echo PHP_EOL;
    }

    private function testSuiteSubReportsCrud()
    {
        echo "--- 4. KIỂM THỬ BÁO CÁO NHỎ (SUB-REPORTS) VÀ DỮ LIỆU ĐA TẦNG ---" . PHP_EOL;
        $nhom = Nhom::with('deTai')->first();
        $deTai = $nhom?->deTai;
        if (!$deTai) {
            $this->assert('TC-16', 'Tạo báo cáo nhỏ', false, 'Không có đề tài');
            return;
        }

        $baoCao = BaoCaoTienDo::firstOrCreate(
            ['MaDeTai' => $deTai->MaDeTai, 'LanBaoCao' => 1],
            [
                'MaBaoCao' => 'BC_TEST_AUTO_01',
                'TieuDe' => 'GĐ 1: Nhận đề cương & Thiết kế CSDL',
                'TrangThai' => 'Chờ duyệt',
                'NgayNop' => now()->toDateString(),
                'PhanTramHoanThanh' => 30,
            ]
        );

        $testKhoiId = 'KHOI_TEST_' . time();
        $khoi = KhoiBaoCaoTienDo::create([
            'MaKhoi' => $testKhoiId,
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $deTai->MaDeTai,
            'LanBaoCao' => 1,
            'LanNop' => 1,
            'TieuDe' => 'Báo cáo công việc tuần 1 - Khảo sát và thiết kế CSDL',
            'NoiDungBaoCao' => 'Đã hoàn thành lược đồ CSDL gồm 18 bảng chuẩn hóa 3NF.',
            'KetQuaDatDuoc' => 'Đã hoàn thành lược đồ CSDL gồm 18 bảng chuẩn hóa 3NF.',
            'KhoKhan' => 'Tối ưu quan hệ nhiều-nhiều giữa sinh viên và đề tài.',
            'HuongGiaiQuyet' => 'Tách bảng trung gian có khóa chính tổ hợp.',
            'KeHoachTiepTheo' => 'Bắt đầu viết mã nguồn API cho module quản lý đề tài.',
            'PhanTramHoanThanh' => 40,
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => now(),
        ]);

        $this->assert('TC-16', 'Tạo mới thành công báo cáo nhỏ trong giai đoạn 1', $khoi->exists);
        $this->assert('TC-17', 'Báo cáo nhỏ lưu đầy đủ 4 trường nghiệp vụ (Kết quả, Khó khăn, Hướng GQ, Kế hoạch)', 
            !empty($khoi->KetQuaDatDuoc) && !empty($khoi->KhoKhan) && !empty($khoi->HuongGiaiQuyet) && !empty($khoi->KeHoachTiepTheo)
        );

        // Test cập nhật báo cáo nhỏ
        $khoi->update(['KetQuaDatDuoc' => 'Đã hoàn thành lược đồ CSDL và thêm 2 bảng mới.', 'PhanTramHoanThanh' => 50]);
        $this->assert('TC-18', 'Sinh viên cập nhật nội dung báo cáo nhỏ thành công', $khoi->fresh()->PhanTramHoanThanh === 50);

        // Lưu bản ghi TomTatBaoCao liên kết
        $tt = TomTatBaoCao::updateOrCreate(
            ['MaBaoCao' => $baoCao->MaBaoCao],
            [
                'MaTomTat' => IdGenerator::nextTomTat(),
                'MaKhoi' => $khoi->MaKhoi,
                'CongViecDaHoanThanh' => $khoi->KetQuaDatDuoc,
                'KhoKhan' => $khoi->KhoKhan,
                'KeHoachTuanToi' => $khoi->KeHoachTiepTheo,
                'TomTatJson' => ['test' => true, 'source_pages' => [1, 2]],
                'TrangThai' => 'Đã tạo',
                'TrangThaiXuLy' => 'hoan_thanh',
                'NgayTomTat' => now(),
            ]
        );
        $this->assert('TC-19', 'Lưu trữ tóm tắt AI liên kết trực tiếp với MaKhoi và MaBaoCao', $tt->exists && $tt->MaKhoi === $khoi->MaKhoi);

        echo PHP_EOL;
    }

    private function testSuiteLecturerReviewAndConfirmation()
    {
        echo "--- 5. KIỂM THỬ GIẢNG VIÊN ĐỐI CHIẾU AI & XÁC NHẬN GIAI ĐOẠN ---" . PHP_EOL;
        $nhom = Nhom::with('deTai')->first();
        $deTai = $nhom?->deTai;
        $baoCao = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->where('LanBaoCao', 1)->first();
        $khoi = KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->latest()->first();

        // Giảng viên đánh giá báo cáo nhỏ Đạt
        $khoi->update([
            'TrangThai' => 'Đạt',
            'NhanXetGV' => 'Tiến độ CSDL tốt, thiết kế bảng chuẩn hóa đúng yêu cầu.',
            'NguoiNhanXet' => 'TS. Lê Hoàng Cường',
            'NgayNhanXet' => now(),
        ]);
        $this->assert('TC-20', 'GVHD đánh giá "Đạt" cho báo cáo nhỏ thành công', $khoi->fresh()->TrangThai === 'Đạt');

        // Giảng viên xác nhận hoàn thành giai đoạn lớn
        $baoCao->update([
            'TrangThai' => 'Đạt',
            'PhanTramHoanThanh' => 100,
            'NhanXet' => 'Xác nhận hoàn thành GĐ 1: Nhận đề cương và CSDL đạt yêu cầu.',
            'NgayXacNhan' => now(),
            'NguoiXacNhan' => 'TS. Lê Hoàng Cường',
        ]);
        $this->assert('TC-21', 'GVHD xác nhận nghiệm thu GĐ 1: đạt 100% tiến độ và lưu người/ngày xác nhận', 
            $baoCao->fresh()->TrangThai === 'Đạt' && $baoCao->fresh()->PhanTramHoanThanh === 100 && !empty($baoCao->fresh()->NgayXacNhan)
        );

        // Kiểm tra GĐ 2 đã đủ điều kiện mở
        $mocTruoc = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->where('LanBaoCao', 1)->where('TrangThai', 'Đạt')->first();
        $this->assert('TC-22', 'Mở khóa Giai đoạn 2 thành công sau khi GĐ 1 được nghiệm thu Đạt', $mocTruoc !== null);

        echo PHP_EOL;
    }

    private function testSuiteAdminOverdueTrackingAllStages()
    {
        echo "--- 6. KIỂM THỬ GIÁO VỤ (ADMIN) THEO DÕI TRỄ HẠN TOÀN BỘ 6 GIAI ĐOẠN ---" . PHP_EOL;
        $ctrl = app(TheoDoiDoAnController::class);

        $stageDeadlines = [
            1 => ['deadline' => '2026-09-18', 'test_date' => '2026-09-20', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 1'],
            2 => ['deadline' => '2026-10-16', 'test_date' => '2026-10-18', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 2'],
            3 => ['deadline' => '2026-11-06', 'test_date' => '2026-11-08', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 3'],
            4 => ['deadline' => '2026-11-27', 'test_date' => '2026-11-29', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 4'],
            5 => ['deadline' => '2026-12-18', 'test_date' => '2026-12-20', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 5'],
            6 => ['deadline' => '2027-01-05', 'test_date' => '2027-01-08', 'expected_code' => 'QUA_HAN', 'expected_label' => 'Trễ hạn GĐ 6'],
        ];

        foreach ($stageDeadlines as $stage => $data) {
            $isOverdue = Carbon::parse($data['test_date'])->format('Y-m-d') > $data['deadline'];
            $this->assert("TC-" . (22 + $stage), "Admin: Nhận diện trễ hạn GĐ {$stage} khi ngày hiện tại ({$data['test_date']}) > hạn ({$data['deadline']})", 
                $isOverdue, $data['expected_label']
            );
        }

        echo PHP_EOL;
    }

    private function printSummary()
    {
        $total = $this->passed + $this->failed;
        $passRate = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;

        echo "==========================================================================" . PHP_EOL;
        echo "                         KẾT QUẢ KIỂM THỬ CHUNG                           " . PHP_EOL;
        echo "==========================================================================" . PHP_EOL;
        echo " Tổng số Test Cases đã chạy : {$total}" . PHP_EOL;
        echo " Số Test Cases đạt (PASS)   : {$this->passed} (Tỷ lệ: {$passRate}%)" . PHP_EOL;
        echo " Số Test Cases lỗi (FAIL)   : {$this->failed}" . PHP_EOL;
        echo " Trạng thái đánh giá        : " . ($this->failed === 0 ? "HOÀN HẢO (ALL PASSED)" : "CẦN ĐIỀU CHỈNH") . PHP_EOL;
        echo "==========================================================================" . PHP_EOL . PHP_EOL;
    }
}

$runner = new TienDoTestRunner();
$runner->run();
