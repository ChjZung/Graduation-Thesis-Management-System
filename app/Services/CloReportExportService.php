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
    /**
     * Xuất file Excel báo cáo tổng hợp chuẩn đầu ra (CLO) cho toàn bộ sinh viên trong Hội đồng
     * theo đúng mẫu biểu của Khoa CNTT - ĐH Công Thương TP.HCM (HUIT).
     */
    public function export(HoiDong $hoiDong, string $loaiKhoaLuan = 'KLCN'): Spreadsheet
    {
        // 1. Tải toàn bộ dữ liệu Hội đồng, Hồ sơ bảo vệ, Đề tài, Sinh viên, Phiếu chấm
        $hoiDong->loadMissing([
            'thanhViens.giangVien',
            'hoSoBaoVes.nhom.thanhViens.sinhVien.lop.nganh',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hoSoBaoVes.nhom.deTai.hocPhan',
            'hoSoBaoVes.nhom.deTai.nganh',
            'hocKy',
            'khoa',
        ]);

        $phieuChams = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)->get();

        // 2. Lấy cấu hình Rubric theo loại khóa luận
        $customChucNang = $hoiDong->hoSoBaoVes->first()?->TieuChiChucNang;
        $rubric = ($loaiKhoaLuan === 'KLKS') 
            ? PhieuChamDiem::getRubricKLKS($customChucNang) 
            : PhieuChamDiem::getRubricKLCN($customChucNang);

        // 3. Gom nhóm CLO động từ Rubric (Không hardcode)
        $cloGroups = [];
        $criteriaMap = [];
        foreach ($rubric as $crit) {
            $criteriaMap[$crit['id']] = $crit;
            if (!empty($crit['is_bonus'])) {
                continue; // Điểm khuyến khích không tính vào chuẩn đầu ra 100% bắt buộc
            }
            $baseClo = preg_replace('/(\.[0-9]+)$/', '', $crit['clo']);
            if (!isset($cloGroups[$baseClo])) {
                $cloGroups[$baseClo] = [
                    'clo' => $baseClo,
                    'criteria' => [],
                    'max_sum' => 0.0,
                    'plo' => $this->getPloMapping($baseClo),
                ];
            }
            $cloGroups[$baseClo]['criteria'][] = $crit;
            $cloGroups[$baseClo]['max_sum'] += (float)$crit['max'];
        }
        ksort($cloGroups);

        // 4. Lấy danh sách thành viên Hội đồng sắp xếp theo vai trò
        $roleOrder = ['Chủ tịch' => 1, 'Thư ký' => 2, 'Phản biện' => 3, 'Ủy viên' => 4];
        $sortedMembers = $hoiDong->thanhViens->sortBy(fn($tv) => $roleOrder[$tv->VaiTro] ?? 5);

        // 5. Gom toàn bộ danh sách sinh viên trong hội đồng (không phân trang, không giới hạn 5 dòng)
        $studentsList = [];
        $stt = 1;
        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $nhom = $hoSo->nhom;
            $deTai = $nhom?->deTai;
            $thanhViensNhom = $nhom?->thanhViens ?? collect();

            foreach ($thanhViensNhom as $tvn) {
                $sv = $tvn->sinhVien;
                if (!$sv) continue;

                // Tách họ và tên chuẩn tiếng Việt
                $hoTenParts = preg_split('/\s+/', trim($sv->HoTen ?? ''));
                $ten = !empty($hoTenParts) ? array_pop($hoTenParts) : '';
                $ho = implode(' ', $hoTenParts);

                // Lấy các phiếu chấm của sinh viên này
                $svPhieus = $phieuChams->where('MaSV', $sv->MaSV);
                $completedPhieus = $svPhieus->where('TrangThai', 'Đã hoàn thành');
                $validPhieus = $completedPhieus->isNotEmpty() ? $completedPhieus : $svPhieus;

                // Tính điểm trung bình từng tiêu chí
                $critAverages = [];
                $critGkScores = [];
                foreach ($rubric as $crit) {
                    $cId = $crit['id'];
                    $scores = [];
                    foreach ($sortedMembers as $tv) {
                        $p = $svPhieus->firstWhere('MaGV', $tv->MaGV);
                        $cd = $p ? (is_array($p->ChiTietDiem) ? $p->ChiTietDiem : json_decode($p->ChiTietDiem, true)) : [];
                        $scoreVal = (isset($cd[$cId]) && is_numeric($cd[$cId])) ? (float)$cd[$cId] : null;
                        $critGkScores[$tv->MaGV][$cId] = $scoreVal;
                        if ($scoreVal !== null && ($p?->TrangThai === 'Đã hoàn thành' || $completedPhieus->isEmpty())) {
                            $scores[] = $scoreVal;
                        }
                    }
                    $critAverages[$cId] = !empty($scores) ? round(array_sum($scores) / count($scores), 2) : 0.0;
                }

                // Tính điểm từng CLO theo công thức: D_CLO = (Tổng D_TB,i / Tổng D_max,i) * 10
                $cloScores = [];
                $allCloPassed = true;
                foreach ($cloGroups as $code => $g) {
                    $sumTb = 0.0;
                    foreach ($g['criteria'] as $c) {
                        $sumTb += $critAverages[$c['id']] ?? 0.0;
                    }
                    $dClo = $g['max_sum'] > 0 ? round(($sumTb / $g['max_sum']) * 10, 2) : 0.0;
                    $isPass = ($dClo >= 4.0);
                    if (!$isPass) {
                        $allCloPassed = false;
                    }
                    $cloScores[$code] = [
                        'score' => $dClo,
                        'sum_tb' => round($sumTb, 2),
                        'max_sum' => $g['max_sum'],
                        'passed' => $isPass,
                    ];
                }

                // Điểm tổng kết
                $sumCrit = array_sum(array_filter($critAverages, fn($k) => empty($criteriaMap[$k]['is_bonus']), ARRAY_FILTER_USE_KEY));
                $bonusScore = $critAverages['tc13'] ?? 0.0;
                $finalScore = min(10.0, round($sumCrit + $bonusScore, 2));

                $studentsList[] = [
                    'stt'            => $stt++,
                    'maSV'           => $sv->MaSV,
                    'ho'             => $ho,
                    'ten'            => $ten,
                    'hoTen'          => $sv->HoTen,
                    'lop'            => $sv->lop?->TenLop ?? 'N/A',
                    'nganh'          => $sv->lop?->nganh?->TenNganh ?? $deTai?->nganh?->TenNganh ?? 'Công nghệ Thông tin',
                    'tenDeTai'       => $deTai?->TenDeTai ?? 'Chưa cập nhật',
                    'maHoSo'         => $hoSo->MaHoSo,
                    'critAverages'   => $critAverages,
                    'critGkScores'   => $critGkScores,
                    'cloScores'      => $cloScores,
                    'allCloPassed'   => $allCloPassed,
                    'finalScore'     => $finalScore,
                ];
            }
        }

        // 6. Khởi tạo Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Trường Đại học Công Thương TP.HCM (HUIT)')
            ->setTitle('Báo cáo đánh giá chuẩn đầu ra CLO Hội đồng ' . $hoiDong->MaHoiDong)
            ->setSubject('Báo cáo Chuẩn đầu ra Khóa luận tốt nghiệp')
            ->setDescription('Được tạo tự động từ Hệ thống Quản lý Khóa luận Tốt nghiệp HUIT');

        // Tạo Sheet 1: Tổng Hợp CĐR CLO (Master Sheet)
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('5B_CĐR_CLO');
        $this->buildMasterCloSheet($sheet1, $hoiDong, $loaiKhoaLuan, $studentsList, $rubric, $cloGroups);

        // Tạo Sheet 2: Chi tiết từng Giám khảo & Tiêu chí (03C Sheet)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('03C_CLO_ĐCK');
        $this->buildExaminerDetailSheet($sheet2, $hoiDong, $loaiKhoaLuan, $studentsList, $rubric, $sortedMembers);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    /**
     * Xây dựng Sheet 1: Mẫu chuẩn 5B_CĐR_CLO
     */
    private function buildMasterCloSheet($sheet, HoiDong $hoiDong, string $loaiKhoaLuan, array $studentsList, array $rubric, array $cloGroups): void
    {
        // Metadata
        $firstStudent = $studentsList[0] ?? null;
        $firstHoSo = $hoiDong->hoSoBaoVes->first();
        $firstDeTai = $firstHoSo?->nhom?->deTai;
        $tenHocPhan = ($loaiKhoaLuan === 'KLKS') ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân';
        $maHocPhan = $firstDeTai?->hocPhan?->MaHocPhan ?? ($loaiKhoaLuan === 'KLKS' ? 'HP_KLKS' : '0101102008');
        $soTinChi = $firstDeTai?->hocPhan?->SoTinChi ?? 4;
        $tenNganh = $firstStudent['nganh'] ?? 'Công nghệ thông tin';
        $tenHocKy = $hoiDong->hocKy?->TenHocKy ?? 'Học kỳ 1';
        $namHoc = $hoiDong->hocKy?->NamHoc ?? '2026-2027';

        // Page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Header Block
        $sheet->setCellValue('A1', 'BỘ CÔNG THƯƠNG');
        $sheet->setCellValue('A2', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH');
        $sheet->setCellValue('A3', 'KHOA CÔNG NGHỆ THÔNG TIN');

        $sheet->setCellValue('F2', 'Mẫu 5B_CĐR');
        $sheet->getStyle('F2')->getFont()->setItalic(true)->setSize(10);

        $sheet->setCellValue('A5', 'ĐÁNH GIÁ CHUẨN ĐẦU RA (CLO) HỌC PHẦN CỐT LÕI');
        $sheet->setCellValue('A6', mb_strtoupper($tenHocPhan, 'UTF-8') . " — {$tenHocKy} ({$namHoc})");

        $sheet->mergeCells('A5:M5');
        $sheet->mergeCells('A6:M6');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('002060'));
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A5:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata rows
        $sheet->setCellValue('A7', "Ngành đào tạo: {$tenNganh}");
        $sheet->setCellValue('E7', "Mã học phần: {$maHocPhan} | Số tín chỉ: {$soTinChi}");
        $sheet->setCellValue('I7', "Hội đồng: {$hoiDong->TenHoiDong} ({$hoiDong->MaHoiDong})");

        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A7:I7')->getFont()->setItalic(true)->setSize(10);

        // Table Header Setup
        // Row 9: CLO Header Group
        // Row 10: PLO Codes
        // Row 11: CLO Codes
        // Row 12: Column labels (STT, Mã SV, Họ, Tên, Lớp, Tên đề tài, ...)
        $startRow = 9;

        // Base Columns:
        // A: STT
        // B: Mã SV
        // C: Họ
        // D: Tên
        // E: Lớp
        // F: Tên đề tài
        $sheet->mergeCells("A{$startRow}:A" . ($startRow + 2));
        $sheet->setCellValue("A{$startRow}", "STT");

        $sheet->mergeCells("B{$startRow}:B" . ($startRow + 2));
        $sheet->setCellValue("B{$startRow}", "Mã SV");

        $sheet->mergeCells("C{$startRow}:C" . ($startRow + 2));
        $sheet->setCellValue("C{$startRow}", "Họ");

        $sheet->mergeCells("D{$startRow}:D" . ($startRow + 2));
        $sheet->setCellValue("D{$startRow}", "Tên");

        $sheet->mergeCells("E{$startRow}:E" . ($startRow + 2));
        $sheet->setCellValue("E{$startRow}", "Lớp");

        $sheet->mergeCells("F{$startRow}:F" . ($startRow + 2));
        $sheet->setCellValue("F{$startRow}", "Tên Đề Tài");

        // CLO Dynamic Columns
        $cloColIndex = 7; // G
        $cloCount = count($cloGroups);
        $lastCloColIndex = $cloColIndex + $cloCount - 1;

        $startCloColStr = Coordinate::stringFromColumnIndex($cloColIndex);
        $endCloColStr = Coordinate::stringFromColumnIndex($lastCloColIndex);

        // Group Header: Điểm trung bình CLO (Thang điểm 10)
        $sheet->mergeCells("{$startCloColStr}{$startRow}:{$endCloColStr}{$startRow}");
        $sheet->setCellValue("{$startCloColStr}{$startRow}", "ĐIỂM TRUNG BÌNH CLO (THANG 10)");

        $curCol = $cloColIndex;
        foreach ($cloGroups as $code => $g) {
            $colStr = Coordinate::stringFromColumnIndex($curCol);
            // Row 10: PLO
            $sheet->setCellValue("{$colStr}" . ($startRow + 1), $g['plo']);
            // Row 11: CLO Name
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
            ->getStartColor()->setRGB('BDD7EE'); // Soft Academic Blue

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
                $cloScore = $stu['cloScores'][$code]['score'] ?? 0.0;
                $sheet->setCellValue("{$cStr}{$currentRow}", $cloScore);
                $colIdx++;
            }

            // Kết quả đạt CĐR
            $rColStr = Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$rColStr}{$currentRow}", $stu['allCloPassed'] ? 'Đạt' : 'Chưa đạt');

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

            // Number formatting for CLO and GPA columns
            $cloRange = "{$startCloColStr}{$currentRow}:{$endCloColStr}{$currentRow}";
            $sheet->getStyle($cloRange)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle($cloRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("{$rColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($stu['allCloPassed']) {
                $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('006100'));
            } else {
                $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9C0006'));
            }

            $sheet->getStyle("{$gColStr}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("{$gColStr}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$gColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $currentRow++;
        }

        $dataEndRow = $currentRow - 1;

        // Borders across all data
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
            $sheet->setCellValue("A{$currentRow}", "TỔNG HỢP TOÀN HỘI ĐỒNG (TRUNG BÌNH CÁC CLO):");
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
            $passCount = count(array_filter($studentsList, fn($s) => $s['allCloPassed']));
            $passRate = $totalCount > 0 ? round(($passCount / $totalCount) * 100, 1) : 0;
            $sheet->setCellValue("{$rColStr}{$currentRow}", "Đạt {$passCount}/{$totalCount} ({$passRate}%)");
            $sheet->getStyle("{$rColStr}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$rColStr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

        // Signatures Block
        $sheet->setCellValue("A{$currentRow}", "Ghi chú: Chuẩn đầu ra Đạt khi điểm tổng hợp CLO >= 4.00 (theo quy chế đào tạo theo hệ thống tín chỉ).");
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9);

        $dateStr = "TP. Hồ Chí Minh, ngày " . date('d') . " tháng " . date('m') . " năm " . date('Y');
        $sigCol = Coordinate::stringFromColumnIndex(max(1, $curCol - 3));
        $sheet->setCellValue("{$sigCol}" . ($currentRow + 1), $dateStr);
        $sheet->setCellValue("{$sigCol}" . ($currentRow + 2), "ĐẠI DIỆN HỘI ĐỒNG BẢO VỆ");
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

        // Set Times New Roman everywhere
        $sheet->getStyle('A1:' . $lastColStr . ($currentRow + 5))->getFont()->setName('Times New Roman');

        // Freeze panes below headers
        $sheet->freezePane('G' . ($startRow + 3));
    }

    /**
     * Xây dựng Sheet 2: Chi tiết từng Tiêu chí và Điểm 3 Giám khảo (Mẫu 03C)
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
        // A: STT, B: Mã SV, C: Họ, D: Tên, E: Lớp
        $sheet->mergeCells('A4:A7'); $sheet->setCellValue('A4', 'STT');
        $sheet->mergeCells('B4:B7'); $sheet->setCellValue('B4', 'Mã SV');
        $sheet->mergeCells('C4:C7'); $sheet->setCellValue('C4', 'Họ và');
        $sheet->mergeCells('D4:D7'); $sheet->setCellValue('D4', 'Tên');
        $sheet->mergeCells('E4:E7'); $sheet->setCellValue('E4', 'Lớp');

        $colIdx = 6;
        foreach ($rubric as $crit) {
            $colStr = Coordinate::stringFromColumnIndex($colIdx);
            // Row 4: Criterion description / label
            $firstLine = strtok($crit['noi_dung'], "\n");
            $sheet->setCellValue("{$colStr}4", "TC{$crit['stt']}: " . mb_substr($firstLine, 0, 35) . '...');
            // Row 5: PLO
            $baseClo = preg_replace('/(\.[0-9]+)$/', '', $crit['clo']);
            $sheet->setCellValue("{$colStr}5", $this->getPloMapping($baseClo));
            // Row 6: CLO code
            $sheet->setCellValue("{$colStr}6", $crit['clo'] . '(*)');
            // Row 7: Max score
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

        // Header styles
        $headerRange = "A4:{$lastColStr}7";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');

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
                $colStr = Coordinate::stringFromColumnIndex($colIdx);
                $critScore = $stu['critAverages'][$crit['id']] ?? 0.0;
                $sheet->setCellValue("{$colStr}{$currentRow}", $critScore);
                $colIdx++;
            }

            // Total Score
            $sheet->setCellValue("{$totalColStr}{$currentRow}", $stu['finalScore']);
            // Topic
            $sheet->setCellValue("{$topicColStr}{$currentRow}", $stu['tenDeTai']);

            // Examiner marks
            foreach ($sortedMembers as $tv) {
                $gkColStr = Coordinate::stringFromColumnIndex($colIdx++);
                $gkPhieu = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)
                    ->where('MaGV', $tv->MaGV)
                    ->where('MaSV', $stu['maSV'])
                    ->first();
                $gkScore = $gkPhieu ? (float)$gkPhieu->Diem : null;
                $sheet->setCellValue("{$gkColStr}{$currentRow}", $gkScore !== null ? number_format($gkScore, 2) : '-');
            }

            // Alignments & formats
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}:D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $critRange = Coordinate::stringFromColumnIndex(6) . "{$currentRow}:{$totalColStr}{$currentRow}";
            $sheet->getStyle($critRange)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle($critRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$totalColStr}{$currentRow}")->getFont()->setBold(true);

            $currentRow++;
        }

        $dataEndRow = $currentRow - 1;

        if ($dataEndRow >= $dataStartRow) {
            $sheet->getStyle("A4:{$lastColStr}{$dataEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('D0D7DE');
        }

        // Auto widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(12);
        for ($c = 6; $c <= Coordinate::columnIndexFromString($lastColStr); $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(13);
        }
        $sheet->getColumnDimension($topicColStr)->setWidth(35);

        $sheet->getStyle('A1:' . $lastColStr . ($currentRow + 2))->getFont()->setName('Times New Roman');
        $sheet->freezePane('F8');
    }

    /**
     * Ánh xạ chuẩn đầu ra CTĐT (PLO) từ chuẩn đầu ra học phần (CLO)
     * Dựa theo Ma trận chuẩn đầu ra CTĐT ngành Công nghệ thông tin - HUIT
     */
    private function getPloMapping(string $baseClo): string
    {
        return match ($baseClo) {
            'CLO1' => 'PLO1',
            'CLO2' => 'PLO3',
            'CLO3' => 'PLO4',
            'CLO4' => 'PLO6',
            'CLO5' => 'PLO10',
            'CLO6' => 'PLO12',
            default => 'PLO1',
        };
    }
}
