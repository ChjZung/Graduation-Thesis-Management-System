<?php

namespace Tests\Unit;

use App\Services\CloSchoolWorkbook;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PHPUnit\Framework\TestCase;

class CloSchoolWorkbookTest extends TestCase
{
    public function test_school_sample_formulas_and_xlsx_round_trip(): void
    {
        // Numeric inputs from the first row of the school workbook. Student identifiers are fictional.
        $first = [.75, .25, .75, .50, .50, .25, 3, .50, .50, .50, .50, .25];
        $other = [.75, .25, .60, .40, .40, .25, 3.5, .40, .40, .40, .35, .30];
        $ids = array_map(fn ($i) => 'tc'.$i, range(1, 12));
        $marks = [];
        foreach ([$first, $other, $other] as $i => $row) {
            $marks['GV'.($i + 1)] = ['hoTen' => 'Giám khảo '.($i + 1), 'chiTietDiem' => array_combine($ids, $row)];
        }
        $student = [
            'maSV' => '001', 'ho' => 'Sinh', 'ten' => 'Viên', 'lop' => 'L1', 'tenDeTai' => '=1+1',
            'maHoiDong' => 'HD1', 'maHoSo' => 'HS1', 'examinerData' => $marks,
        ];
        $students = array_fill(0, 37, $student);
        foreach ($students as $i => &$item) {
            $item['maSV'] = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
        }
        unset($item, $students[1]['examinerData']['GV3']['chiTietDiem']['tc6']);
        $students[2]['examinerData']['GV3']['chiTietDiem']['tc6'] = 0;
        $council = (object) ['MaHoiDong' => 'HD1', 'TenHoiDong' => 'HD1', 'hoSoBaoVes' => collect(), 'hocKy' => null];
        $book = (new CloSchoolWorkbook())->build([
            'scope' => ['TenNganh' => 'CNTT', 'TenKhoa' => 'CNTT', 'label' => 'HD1'],
            'councils' => [['hoiDong' => $council]], 'students' => $students,
        ]);

        foreach ($students as &$item) {
            foreach ($item['examinerData'] as &$examiner) {
                $examiner['isFinal'] = true;
            }
            unset($examiner);
        }
        unset($item);
        $audit = \App\Services\CloCalculationService::auditSchoolScores($students[0]['examinerData'], false);
        $this->assertEqualsWithDelta(1, array_sum(array_column($audit['criteria'], 'weight')), 1e-10);
        $this->assertEquals(.125, $audit['criteria'][0]['weight']);
        $this->assertEquals(.075, $audit['criteria'][2]['weight']);
        $this->assertEquals(.025, $audit['criteria'][1]['weight']);
        $this->assertEquals(.05, $audit['criteria'][9]['weight']);
        $this->assertCount(2, array_filter($audit['criteria'], fn ($item) => $item['code'] === '5.1'));
        $this->assertNull(\App\Services\CloCalculationService::auditSchoolScores($students[1]['examinerData'], false)['passed']);
        $this->assertTrue(\App\Services\CloCalculationService::auditSchoolScores($students[2]['examinerData'], false)['complete']);

        $result = $book->getSheetByName('5B_CĐR_CLO');
        foreach (['F11' => 8.25, 'G11' => 8.6666666667, 'H11' => 8.3333333333, 'I11' => 5.7777777778,
            'J11' => 7.6666666667, 'K11' => 8.6666666667, 'M11' => 8.0833333333] as $cell => $value) {
            $this->assertEqualsWithDelta($value, $result->getCell($cell)->getCalculatedValue(), 1e-8, $cell);
        }
        foreach (range('F', 'K') as $i => $column) {
            $this->assertEqualsWithDelta($audit['clo']['CLO'.($i + 1)]['score'], $result->getCell($column.'11')->getCalculatedValue(), 1e-10);
        }
        $this->assertEqualsWithDelta($audit['final'], $result->getCell('M11')->getCalculatedValue(), 1e-10);
        $this->assertEquals(.125, $book->getSheetByName('Data_CĐR')->getCell('F6')->getCalculatedValue());
        foreach (['L11' => 'Đạt', 'L12' => 'Chưa đủ điểm', 'L13' => 'Đạt', 'B47' => '037'] as $cell => $value) {
            $this->assertSame($value, $result->getCell($cell)->getCalculatedValue());
        }
        $this->assertSame('s', $book->getSheetByName('DSSV_Hội đồng')->getCell('F10')->getDataType());

        $path = tempnam(sys_get_temp_dir(), 'clo');
        try {
            (new XlsxWriter($book))->save($path);
            $read = (new XlsxReader())->load($path);
            $this->assertCount(11, $read->getSheetNames());
            $xlsx = new \ZipArchive();
            $this->assertTrue($xlsx->open($path));
            try {
                foreach ($read->getAllSheets() as $i => $sheet) {
                    $this->assertSame('normal', $sheet->getSheetView()->getView(), $sheet->getTitle());
                    $this->assertEmpty($sheet->getBreaks(), $sheet->getTitle());
                    $xml = $xlsx->getFromName('xl/worksheets/sheet'.($i + 1).'.xml');
                    $this->assertIsString($xml);
                    $this->assertDoesNotMatchRegularExpression('/<pane\b/', $xml, $sheet->getTitle());
                }
            } finally {
                $xlsx->close();
            }
            $this->assertSame('A1:M54', $read->getSheetByName('5B_CĐR_CLO')->getPageSetup()->getPrintArea());
            $this->assertSame('A1:M55', $read->getSheetByName('HP_BAN KY')->getPageSetup()->getPrintArea());
            $this->assertSame('thin', $read->getSheetByName('5B_CĐR_CLO')->getStyle('F11')->getBorders()->getLeft()->getBorderStyle());
            $this->assertNotNull($read->getNamedRange('NHOM_CLO'));
            $this->assertNotNull($read->getNamedRange('diem_gpa'));
            $this->assertSame('Đạt', $read->getSheetByName('5B_CĐR_CLO')->getCell('L11')->getCalculatedValue());
            foreach (['03C_CLO_ĐCK(TB 3 Giám khảo)' => 'F8', 'Data_CĐR' => 'F7', '04_GPA' => 'F5',
                '5B_CĐR_CLO' => 'L11', 'HP_BAN KY' => 'M11'] as $name => $cell) {
                $this->assertSame('f', $read->getSheetByName($name)->getCell($cell)->getDataType());
            }
            $read->getSheetByName('03C_CLO_ĐCK(TB 3 Giám khảo)')->setCellValue('F7', 1.5);
            \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($read)->clearCalculationCache();
            $this->assertEquals(.15, $read->getSheetByName('Data_CĐR')->getCell('F6')->getCalculatedValue());
            $read->getSheetByName('03C_CLO_ĐCK(TB 3 Giám khảo)')->setCellValue('F7', 1.25);
            $read->getSheetByName('Giám khảo 1')->setCellValue('F6', .25);
            \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($read)->clearCalculationCache();
            $this->assertEqualsWithDelta(7, $read->getSheetByName('5B_CĐR_CLO')->getCell('F11')->getCalculatedValue(), 1e-8);
            $this->assertEqualsWithDelta(7.8333333333, $read->getSheetByName('HP_BAN KY')->getCell('M11')->getCalculatedValue(), 1e-8);
        } finally {
            unlink($path);
        }
    }
    public function test_engineer_uses_k13_weights_and_caps_the_bonus(): void
    {
        $scores = array_column(\App\Models\PhieuChamDiem::getRubricKLKS(), 'max', 'id');
        $scores['tc9'] = 0;
        $scores['tc14'] = .75;
        $judge = ['hoTen' => 'Giám khảo', 'chiTietDiem' => $scores];
        $student = ['maSV' => '001', 'ho' => 'Sinh', 'ten' => 'Viên', 'lop' => 'K13', 'tenDeTai' => 'Đề tài',
            'maHoiDong' => 'HD1', 'maHoSo' => 'HS1', 'examinerData' => array_fill_keys(['GV1', 'GV2', 'GV3'], $judge)];
        $maxStudent = $student;
        $maxStudent['maSV'] = '002';
        foreach ($maxStudent['examinerData'] as &$mark) {
            $mark['chiTietDiem']['tc9'] = 1.25;
        }
        unset($mark);
        $topic = (object) ['MaHocPhan' => '001KS', 'hocPhan' => (object) ['MaHocPhan' => '001KS', 'SoTinChi' => 6]];
        $dossier = (object) ['MaHoSo' => 'HS1', 'nhom' => (object) ['deTai' => $topic]];
        $council = (object) ['MaHoiDong' => 'HD1', 'TenHoiDong' => 'HD1', 'hoSoBaoVes' => collect([$dossier]),
            'hocKy' => null, 'NgayBaoVe' => null, 'DiaDiem' => null];

        $book = (new CloSchoolWorkbook())->build([
            'scope' => ['loai' => 'KLKS', 'TenNganh' => 'CNTT', 'TenKhoa' => 'CNTT', 'label' => 'HD1'],
            'councils' => [['hoiDong' => $council]], 'students' => [$student, $maxStudent],
        ]);
        $this->assertCount(11, $book->getSheetNames());
        $this->assertSame('Khóa luận kỹ sư', $book->getSheetByName('GUIDE')->getCell('B3')->getValue());
        $this->assertSame('001KS', $book->getSheetByName('DSSV_Hội đồng')->getCell('N10')->getValue());
        $this->assertEquals(1.25, $book->getSheetByName('Giám khảo 1')->getCell('N5')->getValue());
        $result = $book->getSheetByName('5B_CĐR_CLO');
        $this->assertEqualsWithDelta(40 / 5.25, $result->getCell('H11')->getCalculatedValue(), 1e-8);
        $this->assertEquals(9.5, $result->getCell('M11')->getCalculatedValue());
        $this->assertEquals(10, $result->getCell('M12')->getCalculatedValue());
    }

}
