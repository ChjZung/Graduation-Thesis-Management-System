<?php

namespace App\Services;

use App\Models\PhieuChamDiem;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\SheetView;

class CloSchoolWorkbook
{
    private const CRITERIA = [
        ['Xác định bài toán cần giải quyết', '1.1', 'PLO1', ['tc1', 'tc6']],
        ['Lập kế hoạch và phân công thực hiện công việc', '6', 'PLO12', ['tc2']],
        ['Phân tích và đặc tả bài toán', '1.2', 'PLO1', ['tc3']],
        ['Thiết kế giải pháp cho bài toán', '2.1', 'PLO3', ['tc4']],
        ['Xây dựng giao diện tương tác người dùng', '2.2', 'PLO3', ['tc5']],
        ['Cài đặt các chức năng đáp ứng yêu cầu thực tế', '3', 'PLO4', ['tc7']],
        ['Thử nghiệm và triển khai ứng dụng', '4', 'PLO6', ['tc8']],
        ['Nội dung kiến thức trình bày trong quyển báo cáo', '5.1', 'PLO10', ['tc9']],
        ['Hình thức, định dạng quyển báo cáo', '5.1', 'PLO10', ['tc10']],
        ['Thái độ, tác phong làm việc', '6', 'PLO12', ['tc11']],
        ['Phong cách báo cáo, Slide', '5.2', 'PLO10', ['tc12']],
    ];

    private const PLOS = ['PLO1', 'PLO3', 'PLO4', 'PLO6', 'PLO10', 'PLO12'];

    public static function criteria(bool $engineer): array
    {
        $rubric = $engineer ? PhieuChamDiem::getRubricKLKS() : PhieuChamDiem::getRubricKLCN();
        $base = array_filter($rubric, fn ($item) => empty($item['is_bonus']));
        $maxima = array_column($base, 'max', 'id');
        if (count($maxima) !== count($base) || min($maxima) <= 0 || abs(array_sum($maxima) - 10) > 1e-8) {
            throw new \DomainException('Thang điểm CLO phải có mã tiêu chí duy nhất, điểm tối đa dương và tổng bằng 10.');
        }
        if (!$engineer) {
            return array_map(function ($row) use ($maxima) {
                [$label, $clo, $plo, $ids] = $row;
                $items = array_intersect_key($maxima, array_flip($ids));
                if (count($items) !== count($ids)) {
                    throw new \DomainException('Thiếu tiêu chí trong mẫu Cử nhân.');
                }
                return [$label, $clo, $plo, array_sum($items), $items];
            }, self::CRITERIA);
        }
        $criteria = [];
        foreach ($base as $item) {
            $clo = substr($item['clo'], 3);
            $criteria[] = [strtok($item['noi_dung'], "\n"), $clo, self::PLOS[(int) $clo - 1], $item['max'], [$item['id'] => $item['max']]];
        }

        return $criteria;
    }

    public function build(array $report): Spreadsheet
    {
        $engineer = ($report['scope']['loai'] ?? 'KLCN') === 'KLKS';
        $courseLabel = $engineer ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân';
        $criteria = self::criteria($engineer);
        $groups = array_fill(0, 6, []);
        $columns = [];
        foreach ($criteria as $j => [, $clo]) {
            $columns[] = $column = Coordinate::stringFromColumnIndex($j + 6);
            $groups[(int) $clo - 1][] = $column;
        }
        $countCriteria = count($columns);
        $lastCriterion = end($columns);
        $totalIndex = 6 + $countCriteria;
        $total = Coordinate::stringFromColumnIndex($totalIndex);
        $detailStart = Coordinate::stringFromColumnIndex($totalIndex + 1);
        $detailEnd = Coordinate::stringFromColumnIndex($totalIndex + 4);
        $judgeEnd = Coordinate::stringFromColumnIndex($totalIndex + ($engineer ? 4 : 3));
        $dataEnd = Coordinate::stringFromColumnIndex($totalIndex + 6);
        $book = new Spreadsheet();
        $sheets = [];
        foreach (['GUIDE', '02_CĐR', 'DSSV_Hội đồng', 'Giám khảo 1', 'Giám khảo 2', 'Giám khảo 3',
            '03C_CLO_ĐCK(TB 3 Giám khảo)', '04_GPA', 'Data_CĐR', '5B_CĐR_CLO', 'HP_BAN KY'] as $i => $name) {
            $sheets[] = ($i === 0 ? $book->getActiveSheet() : $book->createSheet())->setTitle($name);
        }

        [$guide, $plan, $roster, $g1, $g2, $g3, $average, $gpa, $data, $result, $print] = $sheets;
        $scope = $report['scope'];
        $council = $report['councils'][0]['hoiDong'];
        $course = $council->hoSoBaoVes->first()?->nhom?->deTai?->hocPhan;
        $this->header($guide, 'ĐÁNH GIÁ CHUẨN ĐẦU RA HỌC PHẦN', 'M');
        $guide->fromArray([
            ['Học phần', $courseLabel], ['Mã học phần', $course?->MaHocPhan], ['Số tín chỉ', $course?->SoTinChi],
            ['Học kỳ', $council->hocKy?->TenHocKy], ['Năm học', $council->hocKy?->NamHoc],
            ['Ngành', $scope['TenNganh']], ['Khoa', $scope['TenKhoa']], ['Phạm vi', $scope['label']],
            ['Ngưỡng đạt CLO (thang 10)', CloCalculationService::PASSING_THRESHOLD], ['Ngưỡng tỷ lệ đạt CLO', .4],
        ], null, 'A3', true);
        $guide->getStyle('B12')->getNumberFormat()->setFormatCode('0%');

        $this->header($plan, 'KẾ HOẠCH ĐÁNH GIÁ HỌC PHẦN · Mẫu 02_CĐR', 'F');
        $plan->fromArray(['CLO', 'PLO', 'Chuẩn đầu ra / tiêu chí', 'Bloom', 'Phương pháp', 'Thời điểm'], null, 'A5');
        $levels = ['1.1' => 3, '1.2' => 4, '2.1' => 3, '2.2' => 3, '3' => 3, '4' => 3, '5.1' => 4, '5.2' => 4, '6' => 4];
        $planRow = 6;
        $seen = [];
        foreach ($criteria as [$label, $clo, $plo]) {
            if (isset($seen[$clo])) {
                continue;
            }
            $seen[$clo] = true;
            $plan->fromArray(["CLO$clo", $plo, $label, $levels[$clo], 'Báo cáo', 'Cuối kỳ'], null, 'A'.$planRow++);
        }
        $this->table($plan, 'F', 5, $planRow - 1);

        $this->header($roster, 'DANH SÁCH SINH VIÊN BÁO CÁO '.mb_strtoupper($courseLabel), 'N');
        $roster->fromArray(['STT', 'Mã SV', 'Họ và', 'Tên', 'Lớp', 'Tên đề tài', 'GV hướng dẫn', 'Hội đồng',
            'Giám khảo 1', 'Giám khảo 2', 'Giám khảo 3', 'Ngày bảo vệ', 'Phòng', 'Mã học phần'], null, 'A9');

        foreach ([$g1, $g2, $g3, $average, $data] as $i => $sheet) {
            $this->header($sheet, match ($i) {
                0, 1, 2 => 'ĐIỂM ĐÁNH GIÁ CHUẨN ĐẦU RA · GIÁM KHẢO '.($i + 1),
                3 => 'ĐIỂM CUỐI KỲ ĐÁNH GIÁ CLO',
                4 => 'QUY ĐỔI THANG ĐIỂM 10 VÀ TỔNG HỢP CLO',
            }, $i === 4 ? $dataEnd : $detailEnd);
            $row = $i < 3 ? 2 : ($i === 4 ? 3 : 4);
            $sheet->fromArray(['STT', 'Mã SV', 'Họ và', 'Tên', 'Lớp'], null, "A$row");
            foreach ($criteria as $j => [$label, $clo, $plo, $max]) {
                $column = Coordinate::stringFromColumnIndex($j + 6);
                $sheet->fromArray([[$label], [$plo], ["CLO$clo(*)"], [$i === 4 ? "='03C_CLO_ĐCK(TB 3 Giám khảo)'!".$column.'7/10' : $max]], null, $column.$row);
            }
            $sheet->setCellValue($total.$row, $i === 4 ? 'GPA' : 'Điểm tổng');
            if ($i < 4) {
                $sheet->fromArray(['Tên đề tài', ...($i < 3 ? ['GV hướng dẫn', 'Giám khảo'] : ['Giám khảo 1', 'Giám khảo 2', 'Giám khảo 3'])], null, $detailStart.$row);
                if ($i < 3 && $engineer) {
                    $sheet->setCellValue($judgeEnd.$row, 'Điểm khuyến khích');
                }
            }
        }
        $data->getStyle('F6:'.$lastCriterion.'6')->getNumberFormat()->setFormatCode('0.0%');
        $data->mergeCells($detailStart.'2:'.$dataEnd.'2')->setCellValue($detailStart.'2', 'TRUNG BÌNH CLO');
        foreach (self::PLOS as $j => $plo) {
            $col = Coordinate::stringFromColumnIndex($j + $totalIndex + 1);
            $data->fromArray([[$plo], ['CLO'.($j + 1).'(*)']], null, $col.'4');
        }
        $this->header($gpa, 'ĐIỂM TỔNG KẾT GPA', 'F');
        $gpa->fromArray(['STT', 'Mã SV', 'Họ và', 'Tên', 'Lớp', 'Điểm tổng kết GPA (100%)'], null, 'A4');
        foreach ([$result, $print] as $sheet) {
            $this->header($sheet, 'KẾT QUẢ CHUẨN ĐẦU RA HỌC PHẦN', 'M');
            $sheet->setCellValue('A3', '=GUIDE!B6&" - "&GUIDE!B7');
            $sheet->setCellValue('A4', '="Khoa: "&GUIDE!B9');
            $sheet->setCellValue('A5', '="Học phần: "&GUIDE!B3');
            $sheet->setCellValue('A6', '="Ngành: "&GUIDE!B8');
            $sheet->mergeCells('F7:K7')->setCellValue('F7', 'Điểm trung bình CLO');
            $sheet->fromArray(self::PLOS, null, 'F8');
            $sheet->fromArray(['STT', 'Mã SV', 'Họ và tên', 'Tên', 'Lớp', 'CLO1(*)', 'CLO2(*)', 'CLO3(*)',
                'CLO4(*)', 'CLO5(*)', 'CLO6(*)', 'Kết quả đạt CĐR HP', 'GPA học phần'], null, 'A10');
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 10);
        }

        $metadata = [];
        foreach ($report['councils'] as $entry) {
            $hd = $entry['hoiDong'];
            foreach ($hd->hoSoBaoVes as $dossier) {
                $topic = $dossier->nhom?->deTai;
                $metadata[$hd->MaHoiDong][$dossier->MaHoSo] = [$topic?->giangVien?->HoTen ?? $topic?->MaGV ?? '',
                    $hd->TenHoiDong, $hd->NgayBaoVe?->format('d/m/Y'), $hd->DiaDiem, $topic?->MaHocPhan];
            }
        }
        foreach ($report['students'] as $i => $student) {
            $ro = $i + 10;
            $exam = $i + 6;
            $avg = $i + 8;
            $normalized = $i + 7;
            $gp = $i + 5;
            $out = $i + 11;
            [$advisor, $councilName, $date, $room, $courseCode] = $metadata[$student['maHoiDong']][$student['maHoSo']] ?? ['', $student['maHoiDong'], '', '', ''];
            $roster->setCellValue("A$ro", $i + 1);
            $names = [];
            foreach ([$g1, $g2, $g3] as $j => $judge) {
                $marks = $student['examinerData']['GV'.($j + 1)] ?? [];
                $names[] = $marks['hoTen'] ?? $marks['ten'] ?? $marks['maGV'] ?? '';
                foreach ($criteria as $k => [, , , , $items]) {
                    $score = self::score($marks['chiTietDiem'] ?? [], $items);
                    $judge->setCellValue(Coordinate::stringFromColumnIndex($k + 6).$exam, $score);
                }
                $scoreRange = "F$exam:$lastCriterion$exam";
                $sum = "SUM($scoreRange)";
                if ($engineer) {
                    $bonus = $marks['chiTietDiem']['tc14'] ?? null;
                    $judge->setCellValue($judgeEnd.$exam, is_numeric($bonus) && $bonus >= 0 && $bonus <= 1 ? (float) $bonus : null);
                    $sum = "MIN(10,$sum+SUM($judgeEnd$exam))";
                }
                $judge->setCellValue($total.$exam, '=IF(COUNT('.$scoreRange.')='.$countCriteria.','.$sum.',"")');
                $judge->fromArray(["='DSSV_Hội đồng'!F$ro", "='DSSV_Hội đồng'!G$ro", "='DSSV_Hội đồng'!".chr(73 + $j).$ro], null, $detailStart.$exam);
            }
            foreach ([$student['maSV'], $student['ho'], $student['ten'], $student['lop'], $student['tenDeTai'],
                $advisor, $councilName, ...$names, $date, $room, $courseCode] as $j => $value) {
                $roster->setCellValueExplicit(Coordinate::stringFromColumnIndex($j + 2).$ro, (string) $value, DataType::TYPE_STRING);
            }
            foreach ([[$g1, $exam], [$g2, $exam], [$g3, $exam], [$average, $avg], [$gpa, $gp], [$data, $normalized], [$result, $out], [$print, $out]] as [$sheet, $row]) {
                foreach (range('A', 'E') as $col) {
                    $sheet->setCellValue($col.$row, "='DSSV_Hội đồng'!$col$ro");
                }
            }
            $average->fromArray(array_map(fn ($col) => "='DSSV_Hội đồng'!$col$ro", ['F', 'I', 'J', 'K']), null, $detailStart.$avg);
            foreach ($columns as $col) {
                $refs = implode(',', array_map(fn ($j) => "'Giám khảo $j'!$col$exam", [1, 2, 3]));
                $average->setCellValue($col.$avg, '=IF(COUNT('.$refs.')=3,AVERAGE('.$refs.'),"")');
                $ref = "'03C_CLO_ĐCK(TB 3 Giám khảo)'!$col$avg";
                $data->setCellValue($col.$normalized, '=IF(ISNUMBER('.$ref.'),'.$ref.'*10/\'03C_CLO_ĐCK(TB 3 Giám khảo)\'!'.$col.'$7,"")');
            }
            $avgRange = "F$avg:$lastCriterion$avg";
            $totalRefs = implode(',', array_map(fn ($j) => "'Giám khảo $j'!$total$exam", [1, 2, 3]));
            $average->setCellValue($total.$avg, $engineer
                ? '=IF(COUNT('.$totalRefs.')=3,AVERAGE('.$totalRefs.'),"")'
                : '=IF(COUNT('.$avgRange.')='.$countCriteria.',SUM('.$avgRange.'),"")');
            $avgTotal = "'03C_CLO_ĐCK(TB 3 Giám khảo)'!$total$avg";
            $gpa->setCellValue("F$gp", '=IF(ISNUMBER('.$avgTotal.'),'.$avgTotal.',"")');
            $data->setCellValue($total.$normalized, '=IF(ISNUMBER('.$avgTotal.'),'.$avgTotal.',"")');
            foreach ($groups as $j => $cols) {
                $values = implode(',', array_map(fn ($col) => $col.$normalized, $cols));
                $weights = array_map(fn ($col) => $col.'$6', $cols);
                $products = array_map(fn ($col, $weight) => $col.$normalized.'*'.$weight, $cols, $weights);
                $data->setCellValue(Coordinate::stringFromColumnIndex($totalIndex + 1 + $j).$normalized, '=IF(COUNT('.$values.')='.count($cols).',('.implode('+', $products).')/SUM('.implode(',', $weights).'),"")');
                foreach ([$result, $print] as $sheet) {
                    $ref = 'Data_CĐR!'.Coordinate::stringFromColumnIndex($totalIndex + 1 + $j).$normalized;
                    $sheet->setCellValue(chr(70 + $j).$out, '=IF(ISNUMBER('.$ref.'),'.$ref.',"")');
                }
            }
            foreach ([$result, $print] as $sheet) {
                $sheet->setCellValue("L$out", '=IF(COUNT(F'.$out.':K'.$out.')<6,"Chưa đủ điểm",IF(MIN(F'.$out.':K'.$out.')<GUIDE!$B$11,"Không đạt","Đạt"))');
                $sheet->setCellValue("M$out", '=IF(ISNUMBER(\'04_GPA\'!F'.$gp.'),\'04_GPA\'!F'.$gp.',"")');
            }
        }

        $count = count($report['students']);
        foreach ([[$roster, 'N', 9], [$g1, $judgeEnd, 5], [$g2, $judgeEnd, 5], [$g3, $judgeEnd, 5], [$average, $detailEnd, 7],
            [$gpa, 'F', 4], [$data, $dataEnd, 6], [$result, 'M', 10], [$print, 'M', 10]] as [$sheet, $lastCol, $header]) {
            $this->table($sheet, $lastCol, $header, $header + $count);
        }
        $last = 10 + $count;
        $stats = $last + 2;
        $result->fromArray([['THỐNG KÊ CLO ĐÁNH GIÁ CLĐT'], ['Điểm tối đa'], ['Ngưỡng đạt CLO'], ['Sinh viên đạt ngưỡng'],
            ['Tỷ lệ sinh viên đạt'], ['Kết quả thống kê']], null, "A$stats");
        foreach (range('F', 'K') as $col) {
            $result->setCellValue($col.($stats + 1), 10);
            $result->setCellValue($col.($stats + 2), '=GUIDE!$B$11');
            $result->setCellValue($col.($stats + 3), '=COUNTIFS('.$col.'11:'.$col.$last.',">="&GUIDE!$B$11,$L$11:$L$'.$last.',"<>Chưa đủ điểm")');
            $result->setCellValue($col.($stats + 4), '='.$col.($stats + 3).'/MAX(1,COUNTIF(L11:L'.$last.',"Đạt")+COUNTIF(L11:L'.$last.',"Không đạt"))');
            $result->setCellValue($col.($stats + 5), '=IF('.$col.($stats + 4).'>=GUIDE!$B$12,"ĐẠT","KHÔNG ĐẠT")');
        }
        $result->getStyle('F'.($stats + 4).':K'.($stats + 4))->getNumberFormat()->setFormatCode('0.0%');
        $print->setCellValue('A'.$stats, '="TỔNG SỐ LƯỢNG SINH VIÊN: "&COUNTA(B11:B'.$last.')');
        $print->setCellValue('A'.($stats + 1), '="SỐ SINH VIÊN ĐẠT ĐIỂM HỌC PHẦN: "&COUNTIF(M11:M'.$last.',">="&GUIDE!$B$11)');
        $print->setCellValue('A'.($stats + 2), '="SỐ SINH VIÊN ĐẠT CHUẨN ĐẦU RA: "&COUNTIF(L11:L'.$last.',"Đạt")');
        $print->setCellValue('J'.$stats, 'TRƯỞNG KHOA');
        $print->setCellValue('J'.($stats + 1), '(Ký và ghi rõ họ tên)');
        $result->getPageSetup()->setPrintArea('A1:M'.($stats + 5));
        $print->getPageSetup()->setPrintArea('A1:M'.($stats + 6));
        $book->addNamedRange(new NamedRange('NHOM_CLO', $data, '$B$7:$'.$dataEnd.'$'.(6 + $count)));
        $book->addNamedRange(new NamedRange('diem_gpa', $gpa, '$B$5:$F$'.(4 + $count)));

        return $book;
    }

    public static function score(array $scores, array $items): ?float
    {
        $total = 0;
        foreach ($items as $id => $max) {
            $value = $scores[$id] ?? null;
            if (!is_numeric($value) || $value < 0 || $value > $max) {
                return null;
            }
            $total += $value;
        }

        return $total;
    }

    private function header($sheet, string $title, string $end): void
    {
        $sheet->getSheetView()->setView(SheetView::SHEETVIEW_NORMAL);
        $sheet->mergeCells("A1:{$end}1")->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('174978');
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4)->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)->setFitToHeight(0);
    }

    private function table($sheet, string $end, int $header, int $last): void
    {
        $top = match ($sheet->getTitle()) {
            'Giám khảo 1', 'Giám khảo 2', 'Giám khảo 3' => 2,
            '03C_CLO_ĐCK(TB 3 Giám khảo)', 'Data_CĐR' => 4,
            '5B_CĐR_CLO', 'HP_BAN KY' => 7,
            default => $header,
        };
        $sheet->getStyle("A$top:{$end}$header")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
        $sheet->getStyle("A$top:{$end}$header")->getFont()->setBold(true);
        $sheet->getStyle("A$top:{$end}$header")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($top)->setRowHeight($top < $header ? 68 : 32);
        $sheet->getStyle("A$top:{$end}$last")->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setRGB('8FA9BC');
        foreach (range('A', $end) as $col) {
            $sheet->getColumnDimension($col)->setWidth(match ($col) { 'A' => 8, 'C' => 38, 'B', 'D', 'E' => 19, default => 16 });
        }
        if ($sheet->getTitle() === 'DSSV_Hội đồng') {
            $sheet->getColumnDimension('F')->setWidth(56);
        }
        if ($last > $header) {
            $sheet->getStyle('F'.($header + 1).":{$end}$last")->getNumberFormat()->setFormatCode('0.00');
        }
    }
}

