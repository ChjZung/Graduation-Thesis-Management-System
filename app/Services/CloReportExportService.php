<?php

namespace App\Services;

use App\Models\HoiDong;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CloReportExportService
{
    protected CloCalculationService $cloCalculationService;

    public function __construct(CloCalculationService $cloCalculationService)
    {
        $this->cloCalculationService = $cloCalculationService;
    }

    /**
     * Xuất file Excel báo cáo tổng hợp chuẩn đầu ra (CLO) cho toàn bộ sinh viên trong Hội đồng
     * theo đúng mẫu biểu của Khoa CNTT - ĐH Công Thương TP.HCM (HUIT).
     *
     * @param HoiDong $hoiDong
     * @param string $loaiKhoaLuan
     * @param bool $force Xuất ngay cả khi chưa đủ 3 phiếu (mặc định false: bắt buộc đủ 3 phiếu hợp lệ)
     * @return Spreadsheet
     * @throws \InvalidArgumentException Khi chưa đủ 3 phiếu chấm hợp lệ mà không bật cờ force
     */
    public function export(HoiDong $hoiDong, string $loaiKhoaLuan = 'KLCN', bool $force = false): Spreadsheet
    {
        // 1. Kiểm tra ràng buộc điều kiện đủ 3 phiếu chấm điểm từ 3 giảng viên Hội đồng
        $checkDuPhieu = $this->cloCalculationService->kiemTraDu3PhieuCham($hoiDong);
        if (!$force && !$checkDuPhieu['du_3_phieu']) {
            throw new \InvalidArgumentException($checkDuPhieu['message']);
        }

        // 2. Tính toán tổng hợp CLO toàn bộ sinh viên từ CloCalculationService
        $councilData = $this->cloCalculationService->calculateCouncilClo($hoiDong, $loaiKhoaLuan);

        $studentsList = $councilData['students'];
        $cloGroups = $councilData['cloGroups'];
        $sortedMembers = $councilData['sortedMembers'];
        $rubric = $councilData['rubric'];

        // 3. Khởi tạo Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Trường Đại học Công Thương TP.HCM (HUIT)')
            ->setTitle('Báo cáo đánh giá chuẩn đầu ra CLO Hội đồng ' . $hoiDong->MaHoiDong)
            ->setSubject('Báo cáo Chuẩn đầu ra Khóa luận tốt nghiệp - Tổng hợp 3 Giảng viên')
            ->setDescription('Được tạo tự động từ Hệ thống Quản lý Khóa luận Tốt nghiệp HUIT');

        // Sheet 1: Mẫu chuẩn 5B_CĐR_CLO - Thống kê Chuẩn đầu ra CLO theo Điểm trung bình của CLO (Yêu cầu chính)
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('5B_CĐR_CLO');
        $this->buildMasterCloSheet($sheet1, $hoiDong, $loaiKhoaLuan, $studentsList, $rubric, $cloGroups);

        // Sheet 2: Bảng đối chiếu chi tiết 3 Giảng viên (TongHop_3GV_CLO)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('TongHop_3GV_CLO');
        $this->buildThreeExaminersComparisonSheet($sheet2, $hoiDong, $loaiKhoaLuan, $studentsList, $cloGroups, $sortedMembers);

        // Sheet 3: Chi tiết từng Giám khảo & Tiêu chí (03C Sheet)
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('03C_CLO_ĐCK');
        $this->buildExaminerDetailSheet($sheet3, $hoiDong, $loaiKhoaLuan, $studentsList, $rubric, $sortedMembers);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    /** One master sheet for the chosen major/round; examiner detail stays per council. */
    public function exportRound(array $report): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getProperties()->setCreator('HUIT')->setTitle('Báo cáo CLO theo ngành và đợt bảo vệ');
        $first = $report['councils'][0];
        $book->getActiveSheet()->setTitle('5B_CĐR_CLO');
        $this->buildMasterCloSheet($book->getActiveSheet(), $first['hoiDong'], $report['scope']['loai'], $report['students'], $report['rubric'], $report['cloGroups'], $report['scope']);
        foreach ($report['councils'] as $i => $data) {
            $comparison = $book->createSheet()->setTitle('HD'.($i + 1).'_TongHop_3GV');
            $this->buildThreeExaminersComparisonSheet($comparison, $data['hoiDong'], $report['scope']['loai'], $data['students'], $data['cloGroups'], $data['sortedMembers']);
            $detail = $book->createSheet()->setTitle('HD'.($i + 1).'_ChiTiet');
            $this->buildExaminerDetailSheet($detail, $data['hoiDong'], $report['scope']['loai'], $data['students'], $data['rubric'], $data['sortedMembers']);
            if (!empty($report['scope']['TenKhoa'])) $comparison->setCellValue('A3', mb_strtoupper($report['scope']['TenKhoa']));
        }
        $book->setActiveSheetIndex(0);
        return $book;
    }

    /**
     * Xây dựng Sheet 1: BẢNG TỔNG HỢP SO SÁNH CLO CỦA CẢ 3 GIẢNG VIÊN VÀ ĐIỂM TRUNG BÌNH HỘI ĐỒNG
     * Thể hiện đầy đủ:
     * - Điểm CLO của Giảng viên 1 (Chủ tịch)
     * - Điểm CLO của Giảng viên 2 (Thư ký)
     * - Điểm CLO của Giảng viên 3 (Phản biện / Ủy viên)
     * - Điểm CLO trung bình của cả 3 giảng viên: D_CLO,TB = (GV1 + GV2 + GV3) / 3
     * - Tỷ lệ đạt CLO và Kết quả đánh giá CLO cuối cùng
     */
    private function buildThreeExaminersComparisonSheet(
        $sheet,
        HoiDong $hoiDong,
        string $loaiKhoaLuan,
        array $studentsList,
        array $cloGroups,
        $sortedMembers
    ): void {
        $firstStudent = $studentsList[0] ?? null;
        $firstHoSo = $hoiDong->hoSoBaoVes->first();
        $firstDeTai = $firstHoSo?->nhom?->deTai;
        $tenHocPhan = ($loaiKhoaLuan === 'KLKS') ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân';
        $maHocPhan = $firstDeTai?->hocPhan?->MaHocPhan ?? ($loaiKhoaLuan === 'KLKS' ? 'HP_KLKS' : '0101102008');
        $soTinChi = $firstDeTai?->hocPhan?->SoTinChi ?? 4;
        $tenNganh = $firstStudent['nganh'] ?? 'Công nghệ thông tin';
        $tenHocKy = $hoiDong->hocKy?->TenHocKy ?? 'Học kỳ 1';
        $namHoc = $hoiDong->hocKy?->NamHoc ?? '2026-2027';

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Header Block
        $sheet->setCellValue('A1', 'BỘ CÔNG THƯƠNG');
        $sheet->setCellValue('A2', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH');
        $sheet->setCellValue('A3', 'KHOA CÔNG NGHỆ THÔNG TIN');

        $sheet->setCellValue('F2', 'BÁO CÁO TỔNG HỢP 3 GIẢNG VIÊN');
        $sheet->getStyle('F2')->getFont()->setItalic(true)->setSize(10);

        $sheet->setCellValue('A5', 'BẢNG TỔNG HỢP SO SÁNH ĐIỂM CLO CỦA CẢ 3 GIẢNG VIÊN VÀ ĐIỂM TRUNG BÌNH HỘI ĐỒNG');
        $sheet->setCellValue('A6', mb_strtoupper($tenHocPhan, 'UTF-8') . " — {$tenHocKy} ({$namHoc}) &bull; HỘI ĐỒNG: {$hoiDong->TenHoiDong} ({$hoiDong->MaHoiDong})");

        $sheet->mergeCells('A5:P5');
        $sheet->mergeCells('A6:P6');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('002060'));
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A5:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata row
        $sheet->setCellValue('A7', "Ngành: {$tenNganh} | Mã học phần: {$maHocPhan} | Số tín chỉ: {$soTinChi}");
        $memberInfo = $sortedMembers->map(fn($m, $i) => "GV" . ($i + 1) . ": " . ($m->giangVien?->HoTen ?? $m->MaGV) . " (" . $m->VaiTro . ")")->implode('  |  ');
        $sheet->setCellValue('A8', "Thành viên Hội đồng: {$memberInfo}");

        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A7:A8')->getFont()->setItalic(true)->setSize(9.5);

        // Table Header: Start row 10
        // Base cols: A: STT, B: Mã SV, C: Họ và Tên, D: Lớp, E: Tên Đề Tài
        $headerRow1 = 10;
        $headerRow2 = 11;

        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}"); $sheet->setCellValue("A{$headerRow1}", "STT");
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}"); $sheet->setCellValue("B{$headerRow1}", "Mã SV");
        $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}"); $sheet->setCellValue("C{$headerRow1}", "Họ và Tên");
        $sheet->mergeCells("D{$headerRow1}:D{$headerRow2}"); $sheet->setCellValue("D{$headerRow1}", "Lớp");
        $sheet->mergeCells("E{$headerRow1}:E{$headerRow2}"); $sheet->setCellValue("E{$headerRow1}", "Tên Đề Tài");

        $curCol = 6; // Column F
        foreach ($cloGroups as $code => $g) {
            $colStartStr = Coordinate::stringFromColumnIndex($curCol);
            $colEndStr = Coordinate::stringFromColumnIndex($curCol + 3);

            // Row 1: Merged CLO header
            $sheet->mergeCells("{$colStartStr}{$headerRow1}:{$colEndStr}{$headerRow1}");
            $sheet->setCellValue("{$colStartStr}{$headerRow1}", "{$code} ({$g['plo']})");

            // Row 2: Sub-columns: GV1, GV2, GV3, ĐIỂM TB
            $c1 = Coordinate::stringFromColumnIndex($curCol++);
            $c2 = Coordinate::stringFromColumnIndex($curCol++);
            $c3 = Coordinate::stringFromColumnIndex($curCol++);
            $c4 = Coordinate::stringFromColumnIndex($curCol++);

            $sheet->setCellValue("{$c1}{$headerRow2}", "GV1");
            $sheet->setCellValue("{$c2}{$headerRow2}", "GV2");
            $sheet->setCellValue("{$c3}{$headerRow2}", "GV3");
            $sheet->setCellValue("{$c4}{$headerRow2}", "ĐIỂM TB");
        }

        // Summary columns
        $colRateStr = Coordinate::stringFromColumnIndex($curCol++);
        $sheet->mergeCells("{$colRateStr}{$headerRow1}:{$colRateStr}{$headerRow2}");
        $sheet->setCellValue("{$colRateStr}{$headerRow1}", "Tỷ lệ\nđạt CLO");

        $colStatusStr = Coordinate::stringFromColumnIndex($curCol++);
        $sheet->mergeCells("{$colStatusStr}{$headerRow1}:{$colStatusStr}{$headerRow2}");
        $sheet->setCellValue("{$colStatusStr}{$headerRow1}", "Đánh giá\nCĐR CLO");

        $colGpaStr = Coordinate::stringFromColumnIndex($curCol++);
        $sheet->mergeCells("{$colGpaStr}{$headerRow1}:{$colGpaStr}{$headerRow2}");
        $sheet->setCellValue("{$colGpaStr}{$headerRow1}", "Điểm TB\nHội Đồng");

        $lastColStr = Coordinate::stringFromColumnIndex($curCol - 1);

        // Styling header
        $headerRange = "A{$headerRow1}:{$lastColStr}{$headerRow2}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6D9F1'); // Header Theme Blue

        // Data Rows
        $currentRow = $headerRow2 + 1;
        $dataStartRow = $currentRow;

        foreach ($studentsList as $stu) {
            $sheet->setCellValue("A{$currentRow}", $stu['stt']);
            $sheet->setCellValueExplicit("B{$currentRow}", $stu['maSV'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$currentRow}", $stu['hoTen']);
            $sheet->setCellValue("D{$currentRow}", $stu['lop']);
            $sheet->setCellValue("E{$currentRow}", $stu['tenDeTai']);

            $colIdx = 6;
            foreach ($cloGroups as $code => $g) {
                $cloObj = $stu['cloScores'][$code] ?? [];
                $gv1 = $cloObj['gv1'] ?? null;
                $gv2 = $cloObj['gv2'] ?? null;
                $gv3 = $cloObj['gv3'] ?? null;
                $avg = $cloObj['score'] ?? 0.0;

                $c1 = Coordinate::stringFromColumnIndex($colIdx++);
                $c2 = Coordinate::stringFromColumnIndex($colIdx++);
                $c3 = Coordinate::stringFromColumnIndex($colIdx++);
                $c4 = Coordinate::stringFromColumnIndex($colIdx++);

                $sheet->setCellValue("{$c1}{$currentRow}", $gv1 !== null ? $gv1 : '-');
                $sheet->setCellValue("{$c2}{$currentRow}", $gv2 !== null ? $gv2 : '-');
                $sheet->setCellValue("{$c3}{$currentRow}", $gv3 !== null ? $gv3 : '-');
                $sheet->setCellValue("{$c4}{$currentRow}", $avg);

                // Highlight TB column
                $sheet->getStyle("{$c4}{$currentRow}")->getFont()->setBold(true);
                $sheet->getStyle("{$c4}{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF1F5');
            }

            // Summary
            $cRate = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$cRate}{$currentRow}", ($stu['clo_pass_rate'] ?? 0.0) . '%');

            $cStat = Coordinate::stringFromColumnIndex($colIdx++);
            $isPass = $stu['all_clo_passed'] ?? false;
            $sheet->setCellValue("{$cStat}{$currentRow}", $isPass ? 'Đạt' : 'Chưa đạt');
            $sheet->getStyle("{$cStat}{$currentRow}")->getFont()->setBold(true);
            if ($isPass) {
                $sheet->getStyle("{$cStat}{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('006100'));
            } else {
                $sheet->getStyle("{$cStat}{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9C0006'));
            }

            $cGpa = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$cGpa}{$currentRow}", $stu['finalScore']);
            $sheet->getStyle("{$cGpa}{$currentRow}")->getFont()->setBold(true);

            // Alignments & formats
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("F{$currentRow}:{$lastColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$cRate}{$currentRow}:{$cStat}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        $dataEndRow = $currentRow - 1;

        // Borders
        if ($dataEndRow >= $dataStartRow) {
            $sheet->getStyle("A{$headerRow1}:{$lastColStr}{$dataEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('D0D7DE');
            $sheet->getStyle("A{$headerRow1}:{$lastColStr}{$dataEndRow}")->getBorders()->getOutline()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('002060');
        }

        // Summary row
        if ($dataEndRow >= $dataStartRow) {
            $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "ĐIỂM TRUNG BÌNH TOÀN HỘI ĐỒNG:");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $colIdx = 6;
            foreach ($cloGroups as $code => $g) {
                for ($k = 0; $k < 4; $k++) {
                    $cStr = Coordinate::stringFromColumnIndex($colIdx++);
                    $sheet->setCellValue("{$cStr}{$currentRow}", "=AVERAGE({$cStr}{$dataStartRow}:{$cStr}{$dataEndRow})");
                    $sheet->getStyle("{$cStr}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
                    $sheet->getStyle("{$cStr}{$currentRow}")->getFont()->setBold(true);
                    $sheet->getStyle("{$cStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }

            // Summary pass rate
            $cRate = Coordinate::stringFromColumnIndex($colIdx++);
            $totalCount = count($studentsList);
            $passCount = count(array_filter($studentsList, fn($s) => !empty($s['all_clo_passed'])));
            $rate = ($totalCount > 0) ? round(($passCount / $totalCount) * 100, 1) : 0;
            $sheet->setCellValue("{$cRate}{$currentRow}", "{$rate}%");
            $sheet->getStyle("{$cRate}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$cRate}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $cStat = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$cStat}{$currentRow}", "Đạt {$passCount}/{$totalCount}");
            $sheet->getStyle("{$cStat}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$cStat}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $cGpa = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$cGpa}{$currentRow}", "=AVERAGE({$cGpa}{$dataStartRow}:{$cGpa}{$dataEndRow})");
            $sheet->getStyle("{$cGpa}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("{$cGpa}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$cGpa}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("A{$currentRow}:{$lastColStr}{$currentRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9EDF4');
            $sheet->getStyle("A{$currentRow}:{$lastColStr}{$currentRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $currentRow += 2;
        }

        // Formula Note
        $sheet->setCellValue("A{$currentRow}", "Ghi chú: Điểm CLO Trung Bình = (Điểm CLO GV1 + Điểm CLO GV2 + Điểm CLO GV3) / 3. Chuẩn đầu ra Đạt khi Điểm CLO TB >= 4.00 (thang 10).");
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9);

        // Signatures of all 3 Lecturers
        $currentRow += 2;
        $dateStr = "TP. Hồ Chí Minh, ngày " . date('d') . " tháng " . date('m') . " năm " . date('Y');
        $sheet->setCellValue("A{$currentRow}", $dateStr);
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9.5);

        $currentRow++;
        $sigRowTitle = $currentRow;
        $sigRowSign = $currentRow + 1;
        $sigRowName = $currentRow + 4;

        // Position signatures across columns
        $gv1 = $sortedMembers->get(0);
        $gv2 = $sortedMembers->get(1);
        $gv3 = $sortedMembers->get(2);

        // GV1 (Chủ tịch) - Trái / Giữa
        $sheet->setCellValue("B{$sigRowTitle}", "CHỦ TỊCH HỘI ĐỒNG");
        $sheet->setCellValue("B{$sigRowSign}", "(Ký và ghi rõ họ tên)");
        $sheet->setCellValue("B{$sigRowName}", $gv1?->giangVien?->HoTen ?? $gv1?->MaGV ?? '');
        $sheet->getStyle("B{$sigRowTitle}:B{$sigRowName}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B{$sigRowTitle}")->getFont()->setBold(true);
        $sheet->getStyle("B{$sigRowSign}")->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle("B{$sigRowName}")->getFont()->setBold(true);

        // GV2 (Thư ký) - Ở giữa
        $midCol = Coordinate::stringFromColumnIndex(intval($curCol / 2));
        $sheet->setCellValue("{$midCol}{$sigRowTitle}", "THƯ KÝ HỘI ĐỒNG");
        $sheet->setCellValue("{$midCol}{$sigRowSign}", "(Ký và ghi rõ họ tên)");
        $sheet->setCellValue("{$midCol}{$sigRowName}", $gv2?->giangVien?->HoTen ?? $gv2?->MaGV ?? '');
        $sheet->getStyle("{$midCol}{$sigRowTitle}:{$midCol}{$sigRowName}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$midCol}{$sigRowTitle}")->getFont()->setBold(true);
        $sheet->getStyle("{$midCol}{$sigRowSign}")->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle("{$midCol}{$sigRowName}")->getFont()->setBold(true);

        // GV3 (Phản biện / Ủy viên) - Bên phải
        $rightCol = Coordinate::stringFromColumnIndex(max(1, $curCol - 3));
        $role3 = $gv3?->VaiTro ?? 'PHẢN BIỆN / ỦY VIÊN';
        $sheet->setCellValue("{$rightCol}{$sigRowTitle}", mb_strtoupper($role3, 'UTF-8'));
        $sheet->setCellValue("{$rightCol}{$sigRowSign}", "(Ký và ghi rõ họ tên)");
        $sheet->setCellValue("{$rightCol}{$sigRowName}", $gv3?->giangVien?->HoTen ?? $gv3?->MaGV ?? '');
        $sheet->getStyle("{$rightCol}{$sigRowTitle}:{$rightCol}{$sigRowName}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$rightCol}{$sigRowTitle}")->getFont()->setBold(true);
        $sheet->getStyle("{$rightCol}{$sigRowSign}")->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle("{$rightCol}{$sigRowName}")->getFont()->setBold(true);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(32);
        for ($c = 6; $c <= Coordinate::columnIndexFromString($lastColStr); $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(10);
        }

        $sheet->getStyle('A1:' . $lastColStr . ($sigRowName + 2))->getFont()->setName('Times New Roman');
        $sheet->freezePane('F' . ($headerRow2 + 1));
    }

    /**
     * Xây dựng Sheet 2: Mẫu chuẩn 5B_CĐR_CLO (Sử dụng đúng điểm trung bình của 3 giảng viên)
     */
    private function buildMasterCloSheet($sheet, HoiDong $hoiDong, string $loaiKhoaLuan, array $studentsList, array $rubric, array $cloGroups, ?array $scope = null): void
    {
        $firstStudent = $studentsList[0] ?? null;
        $firstHoSo = $hoiDong->hoSoBaoVes->first();
        $firstDeTai = $firstHoSo?->nhom?->deTai;
        $tenHocPhan = ($loaiKhoaLuan === 'KLKS') ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân';
        $maHocPhan = $firstDeTai?->hocPhan?->MaHocPhan ?? ($loaiKhoaLuan === 'KLKS' ? 'HP_KLKS' : '0101102008');
        $soTinChi = $firstDeTai?->hocPhan?->SoTinChi ?? 4;
        $tenNganh = $scope['TenNganh'] ?? $firstStudent['nganh'] ?? 'Công nghệ thông tin';
        $tenHocKy = $hoiDong->hocKy?->TenHocKy ?? 'Học kỳ 1';
        $namHoc = $hoiDong->hocKy?->NamHoc ?? '2026-2027';

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Header Block
        $sheet->setCellValue('A1', 'BỘ CÔNG THƯƠNG');
        $sheet->setCellValue('A2', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH');
        $sheet->setCellValue('A3', mb_strtoupper($scope['TenKhoa'] ?? 'KHOA CÔNG NGHỆ THÔNG TIN'));

        $sheet->setCellValue('F2', 'Mẫu 5B_CĐR');
        $sheet->getStyle('F2')->getFont()->setItalic(true)->setSize(10);

        $sheet->setCellValue('A5', 'ĐÁNH GIÁ CHUẨN ĐẦU RA (CLO) HỌC PHẦN CỐT LÕI');
        $sheet->setCellValue('A6', mb_strtoupper($tenHocPhan, 'UTF-8') . " — {$tenHocKy} ({$namHoc})");

        $sheet->mergeCells('A5:M5');
        $sheet->mergeCells('A6:M6');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('002060'));
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A5:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A7', "Ngành đào tạo: {$tenNganh}");
        $sheet->setCellValue('E7', "Mã học phần: {$maHocPhan} | Số tín chỉ: {$soTinChi}");
        $sheet->setCellValue('I7', $scope ? ($scope['label'] ?? "Đợt bảo vệ: {$scope['DotBaoVe']}") : "Hội đồng: {$hoiDong->TenHoiDong} ({$hoiDong->MaHoiDong})");

        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A7:I7')->getFont()->setItalic(true)->setSize(10);
        $metadataEnd = Coordinate::stringFromColumnIndex(8 + count($cloGroups));
        $sheet->mergeCells('A7:D7'); $sheet->mergeCells('E7:H7'); $sheet->mergeCells('I7:'.$metadataEnd.'7');
        $sheet->getStyle('A7:'.$metadataEnd.'7')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(7)->setRowHeight(32);


        $startRow = 9;

        // Base Columns:
        $sheet->mergeCells("A{$startRow}:A" . ($startRow + 2)); $sheet->setCellValue("A{$startRow}", "STT");
        $sheet->mergeCells("B{$startRow}:B" . ($startRow + 2)); $sheet->setCellValue("B{$startRow}", "Mã SV");
        $sheet->mergeCells("C{$startRow}:C" . ($startRow + 2)); $sheet->setCellValue("C{$startRow}", "Họ");
        $sheet->mergeCells("D{$startRow}:D" . ($startRow + 2)); $sheet->setCellValue("D{$startRow}", "Tên");
        $sheet->mergeCells("E{$startRow}:E" . ($startRow + 2)); $sheet->setCellValue("E{$startRow}", "Lớp");
        $sheet->mergeCells("F{$startRow}:F" . ($startRow + 2)); $sheet->setCellValue("F{$startRow}", "Tên Đề Tài");

        // CLO Dynamic Columns: Chứa Điểm CLO trung bình của cả 3 giảng viên
        $cloColIndex = 7; // G
        $cloCount = count($cloGroups);
        $lastCloColIndex = $cloColIndex + $cloCount - 1;

        $startCloColStr = Coordinate::stringFromColumnIndex($cloColIndex);
        $endCloColStr = Coordinate::stringFromColumnIndex($lastCloColIndex);

        $sheet->mergeCells("{$startCloColStr}{$startRow}:{$endCloColStr}{$startRow}");
        $sheet->setCellValue("{$startCloColStr}{$startRow}", "ĐIỂM TRUNG BÌNH CLO TỪ 3 GIẢNG VIÊN (THANG 10)");

        $curCol = $cloColIndex;
        foreach ($cloGroups as $code => $g) {
            $colStr = Coordinate::stringFromColumnIndex($curCol);
            $sheet->setCellValue("{$colStr}" . ($startRow + 1), $g['plo']);
            $sheet->setCellValue("{$colStr}" . ($startRow + 2), "{$code}(*)");
            $curCol++;
        }

        // Summary columns
        $resultColStr = Coordinate::stringFromColumnIndex($curCol++);
        $sheet->mergeCells("{$resultColStr}{$startRow}:{$resultColStr}" . ($startRow + 2));
        $sheet->setCellValue("{$resultColStr}{$startRow}", "Kết quả đạt\nCĐR HP (CLO)");

        $gpaColStr = Coordinate::stringFromColumnIndex($curCol++);
        $sheet->mergeCells("{$gpaColStr}{$startRow}:{$gpaColStr}" . ($startRow + 2));
        $sheet->setCellValue("{$gpaColStr}{$startRow}", "Điểm tổng kết\n(Thang 10)");

        $lastColStr = Coordinate::stringFromColumnIndex($curCol - 1);

        // Header Styling
        $headerRange = "A{$startRow}:{$lastColStr}" . ($startRow + 2);
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('BDD7EE');

        // Data Rows
        $currentRow = $startRow + 3;
        $dataStartRow = $currentRow;

        foreach ($studentsList as $stu) {
            $sheet->setCellValue("A{$currentRow}", $stu['stt']);
            $sheet->setCellValueExplicit("B{$currentRow}", $stu['maSV'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$currentRow}", $stu['ho']);
            $sheet->setCellValue("D{$currentRow}", $stu['ten']);
            $sheet->setCellValue("E{$currentRow}", $stu['lop']);
            $sheet->setCellValue("F{$currentRow}", $stu['tenDeTai']);

            $colIdx = 7;
            foreach ($cloGroups as $code => $g) {
                $cStr = Coordinate::stringFromColumnIndex($colIdx);
                // Lấy đúng điểm trung bình của 3 giảng viên
                $cloScore = $stu['cloScores'][$code]['score'] ?? 0.0;
                $sheet->setCellValue("{$cStr}{$currentRow}", $cloScore);
                $colIdx++;
            }

            // Kết quả đạt CĐR
            $rColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $isPass = !empty($stu['all_clo_passed']);
            $sheet->setCellValue("{$rColStr}{$currentRow}", $isPass ? 'Đạt' : 'Chưa đạt');

            // Điểm tổng kết
            $gColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$gColStr}{$currentRow}", $stu['finalScore']);

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $cloRange = "{$startCloColStr}{$currentRow}:{$endCloColStr}{$currentRow}";
            $sheet->getStyle($cloRange)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle($cloRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("{$rColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $sheet->getRowDimension($currentRow)->setRowHeight(30);
            if ($isPass) {
                $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('006100'));
            } else {
                $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9C0006'));
            }

            $sheet->getStyle("{$gColStr}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("{$gColStr}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$gColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($currentRow)->setRowHeight(max(30, ceil(mb_strlen($stu['tenDeTai']) / 35) * 14));
            $currentRow++;
        }

        $dataEndRow = $currentRow - 1;

        // Borders
        if ($dataEndRow >= $dataStartRow) {
            $sheet->getStyle("A{$startRow}:{$lastColStr}{$dataEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('D0D7DE');
            $sheet->getStyle("A{$startRow}:{$lastColStr}{$dataEndRow}")->getBorders()->getOutline()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('002060');
        }

        // Summary Statistics Row
        if ($dataEndRow >= $dataStartRow) {
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $scopeTitle = match ($scope['type'] ?? ($scope ? 'round' : 'council')) {
                'major' => 'TOÀN NGÀNH', 'round' => 'TOÀN ĐỢT', default => 'HỘI ĐỒNG',
            };
            $sheet->setCellValue("A{$currentRow}", "TỔNG HỢP {$scopeTitle} (TRUNG BÌNH CÁC CLO):");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $colIdx = 7;
            foreach ($cloGroups as $code => $g) {
                $cStr = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue("{$cStr}{$currentRow}", "=AVERAGE({$cStr}{$dataStartRow}:{$cStr}{$dataEndRow})");
                $sheet->getStyle("{$cStr}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("{$cStr}{$currentRow}")->getFont()->setBold(true);
                $sheet->getStyle("{$cStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $colIdx++;
            }

            // Tỷ lệ Đạt
            $rColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $totalCount = count($studentsList);
            $passCount = count(array_filter($studentsList, fn($s) => !empty($s['all_clo_passed'])));
            $passRate = $totalCount > 0 ? round(($passCount / $totalCount) * 100, 1) : 0;
            $sheet->setCellValue("{$rColStr}{$currentRow}", "Đạt {$passCount}/{$totalCount} ({$passRate}%)");
            $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$rColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $sheet->getRowDimension($currentRow)->setRowHeight(30);

            // Điểm GPA trung bình
            $gColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$gColStr}{$currentRow}", "=AVERAGE({$gColStr}{$dataStartRow}:{$gColStr}{$dataEndRow})");
            $sheet->getStyle("{$gColStr}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("{$gColStr}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$gColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("A{$currentRow}:{$lastColStr}{$currentRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
            $sheet->getStyle("A{$currentRow}:{$lastColStr}{$currentRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $currentRow += 2;
        }

        // Statistics follow the supplied 5B template: score >= 4/10; cohort pass rate >= 40%.
        $statsTitleRow = $currentRow;
        $sheet->mergeCells("C{$statsTitleRow}:{$endCloColStr}{$statsTitleRow}");
        $sheet->setCellValue("C{$statsTitleRow}", 'THỐNG KÊ CLO ĐÁNH GIÁ CLĐT');
        $sheet->getStyle("C{$statsTitleRow}")->getFont()->setBold(true)->setSize(12);
        $labels = ['Điểm tối đa', 'Chọn ngưỡng Đạt cho CLO', 'Điểm Đạt tương ứng', 'Sinh viên đạt ngưỡng CLO', 'Tỷ lệ SV đạt ngưỡng CLO', 'Kết quả thống kê'];
        foreach ($labels as $offset => $label) {
            $r = $statsTitleRow + 1 + $offset;
            $sheet->mergeCells("C{$r}:F{$r}")->setCellValue("C{$r}", $label);
            $sheet->getRowDimension($r)->setRowHeight(23);
        }
        foreach (array_keys($cloGroups) as $i => $code) {
            $c = Coordinate::stringFromColumnIndex(7 + $i);
            $r = $statsTitleRow;
            $sheet->setCellValue($c.($r+1), 10);
            $sheet->setCellValue($c.($r+2), CloCalculationService::PASSING_THRESHOLD / 10);
            $sheet->setCellValue($c.($r+3), "={$c}".($r+1)."*{$c}".($r+2));
            $sheet->setCellValue($c.($r+4), $dataEndRow >= $dataStartRow ? '=COUNTIF('.$c.$dataStartRow.':'.$c.$dataEndRow.',">="&'.$c.($r+3).')' : 0);
            $sheet->setCellValue($c.($r+5), $dataEndRow >= $dataStartRow ? '=IFERROR('.$c.($r+4).'/COUNTA($B$'.$dataStartRow.':$B$'.$dataEndRow.'),0)' : 0);
            $sheet->setCellValue($c.($r+6), '=IF('.$c.($r+5).'>=40%,"ĐẠT","KHÔNG ĐẠT")');
            $sheet->getStyle($c.($r+1))->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle($c.($r+2))->getNumberFormat()->setFormatCode('0%');
            $sheet->getStyle($c.($r+3))->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle($c.($r+5))->getNumberFormat()->setFormatCode('0.0%');
        }
        $statsRange = 'C'.($statsTitleRow+1).':'.$endCloColStr.($statsTitleRow+6);
        $sheet->getStyle($statsRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBE5D6');
        $sheet->getStyle($statsRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($statsRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle($startCloColStr.($statsTitleRow+1).':'.$endCloColStr.($statsTitleRow+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow = $statsTitleRow + 8;

        // Signatures Block
        $sheet->setCellValue("A{$currentRow}", "Ghi chú: Điểm CLO tính từ trung bình 3 Giảng viên. Chuẩn đầu ra Đạt khi điểm tổng hợp CLO >= 4.00 (theo quy chế đào tạo).");
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9);

        $dateStr = "TP. Hồ Chí Minh, ngày " . date('d') . " tháng " . date('m') . " năm " . date('Y');
        $sigCol = Coordinate::stringFromColumnIndex(max(1, $curCol - 3));
        $sheet->setCellValue("{$sigCol}" . ($currentRow + 1), $dateStr);
        $sheet->setCellValue("{$sigCol}" . ($currentRow + 2), $scope && ($scope['type'] ?? 'round') !== 'council' ? "ĐẠI DIỆN KHOA" : "ĐẠI DIỆN HỘI ĐỒNG BẢO VỆ");
        $sheet->setCellValue("{$sigCol}" . ($currentRow + 3), "(Ký và ghi rõ họ tên)");
        $sheet->getStyle("{$sigCol}" . ($currentRow + 1) . ":{$sigCol}" . ($currentRow + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$sigCol}" . ($currentRow + 2))->getFont()->setBold(true);
        $sheet->getStyle("{$sigCol}" . ($currentRow + 3))->getFont()->setItalic(true)->setSize(9);

        // Auto width for columns
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(35);
        for ($c = 7; $c <= Coordinate::columnIndexFromString($lastColStr); $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(12);
        }

        $sheet->getStyle('A1:' . $lastColStr . ($currentRow + 5))->getFont()->setName('Times New Roman');
        $sheet->freezePane('G' . ($startRow + 3));
    }

    /**
     * Xây dựng Sheet 3: Chi tiết từng Tiêu chí và Điểm 3 Giám khảo (Mẫu 03C)
     */
    private function buildExaminerDetailSheet($sheet, HoiDong $hoiDong, string $loaiKhoaLuan, array $studentsList, array $rubric, $sortedMembers): void
    {
        $tenHocPhan = ($loaiKhoaLuan === 'KLKS') ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân';
        $tenHocKy = $hoiDong->hocKy?->TenHocKy ?? 'Học kỳ 1';
        $namHoc = $hoiDong->hocKy?->NamHoc ?? '2026-2027';

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $sheet->setCellValue('A1', 'ĐIỂM CUỐI KỲ ĐÁNH GIÁ CLO (CHI TIẾT TIÊU CHÍ VÀ ĐIỂM TRUNG BÌNH HỘI ĐỒNG)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('002060'));

        $sheet->setCellValue('A2', "Học kỳ: {$tenHocKy} | Năm học: {$namHoc} | Học phần: {$tenHocPhan} | Hội đồng: {$hoiDong->TenHoiDong}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        // Table Header: Row 4 to 7
        $sheet->mergeCells('A4:A7'); $sheet->setCellValue('A4', 'STT');
        $sheet->mergeCells('B4:B7'); $sheet->setCellValue('B4', 'Mã SV');
        $sheet->mergeCells('C4:C7'); $sheet->setCellValue('C4', 'Họ và');
        $sheet->mergeCells('D4:D7'); $sheet->setCellValue('D4', 'Tên');
        $sheet->mergeCells('E4:E7'); $sheet->setCellValue('E4', 'Lớp');

        $colIdx = 6;
        foreach ($rubric as $crit) {
            $colStr = Coordinate::stringFromColumnIndex($colIdx);
            $firstLine = strtok($crit['noi_dung'], "\n");
            $sheet->setCellValue("{$colStr}4", "TC{$crit['stt']}: " . mb_substr($firstLine, 0, 35) . '...');
            $baseClo = preg_replace('/(\.[0-9]+)$/', '', $crit['clo']);
            $sheet->setCellValue("{$colStr}5", $this->cloCalculationService->getPloMapping($baseClo));
            $sheet->setCellValue("{$colStr}6", $crit['clo'] . '(*)');
            $sheet->setCellValue("{$colStr}7", number_format($crit['max'], 2));

            $colIdx++;
        }

        // Total score column
        $totalColStr = Coordinate::stringFromColumnIndex($colIdx++);
        $sheet->mergeCells("{$totalColStr}4:{$totalColStr}6");
        $sheet->setCellValue("{$totalColStr}4", "Điểm TB\nKhóa luận");
        $sheet->setCellValue("{$totalColStr}7", "10.00");

        // Topic name
        $topicColStr = Coordinate::stringFromColumnIndex($colIdx++);
        $sheet->mergeCells("{$topicColStr}4:{$topicColStr}7");
        $sheet->setCellValue("{$topicColStr}4", "Tên Đề Tài");

        // Examiner names
        foreach ($sortedMembers as $tv) {
            $gkColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->mergeCells("{$gkColStr}4:{$gkColStr}7");
            $sheet->setCellValue("{$gkColStr}4", "{$tv->VaiTro}\n" . ($tv->giangVien?->HoTen ?? $tv->MaGV));
        }

        $lastColStr = Coordinate::stringFromColumnIndex($colIdx - 1);

        $sheet->getStyle("A4:{$lastColStr}7")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A4:{$lastColStr}7")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle("A4:{$lastColStr}7")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');

        // Data Rows
        $currentRow = 8;
        $dataStartRow = $currentRow;

        foreach ($studentsList as $stu) {
            $sheet->setCellValue("A{$currentRow}", $stu['stt']);
            $sheet->setCellValueExplicit("B{$currentRow}", $stu['maSV'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$currentRow}", $stu['ho']);
            $sheet->setCellValue("D{$currentRow}", $stu['ten']);
            $sheet->setCellValue("E{$currentRow}", $stu['lop']);

            $colIdx = 6;
            foreach ($rubric as $crit) {
                $cStr = Coordinate::stringFromColumnIndex($colIdx);
                $critAvg = $stu['critAverages'][$crit['id']] ?? 0.0;
                $sheet->setCellValue("{$cStr}{$currentRow}", $critAvg);
                $colIdx++;
            }

            // Final score
            $tStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$tStr}{$currentRow}", $stu['finalScore']);

            // Topic name
            $tpStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$tpStr}{$currentRow}", $stu['tenDeTai']);

            // Individual examiner criterion scores
            foreach ($sortedMembers as $idx => $tv) {
                $gkStr = Coordinate::stringFromColumnIndex($colIdx++);
                $exData = $stu['examinerData']['GV' . ($idx + 1)] ?? null;
                $sheet->setCellValue("{$gkStr}{$currentRow}", $exData['diemTong'] ?? '-');
            }

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}:" . Coordinate::stringFromColumnIndex(5 + count($rubric) + 1) . "{$currentRow}")
                ->getNumberFormat()->setFormatCode('0.00');

            $currentRow++;
        }

        $dataEndRow = $currentRow - 1;

        if ($dataEndRow >= $dataStartRow) {
            $sheet->getStyle("A4:{$lastColStr}{$dataEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('D0D7DE');
        }

        // Auto widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(12);
        for ($c = 6; $c <= Coordinate::columnIndexFromString($lastColStr); $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(12);
        }

        $sheet->getStyle('A1:' . $lastColStr . ($currentRow + 2))->getFont()->setName('Times New Roman');
        $sheet->freezePane('F8');
    }
}
