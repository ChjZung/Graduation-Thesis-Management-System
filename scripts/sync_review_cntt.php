<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$reviewDir = __DIR__ . '/../sample_imports/review';

function saveReviewXlsx($filename, array $headers, array $data, $dir)
{
    $xlsxPath = $dir . '/' . $filename;
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr(str_replace('.xlsx', '', $filename), 0, 31));

    foreach ($headers as $colIdx => $h) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue("{$colLetter}1", $h);
    }

    $rowIdx = 2;
    foreach ($data as $row) {
        foreach ($row as $colIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValueExplicit("{$colLetter}{$rowIdx}", (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $rowIdx++;
    }

    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $lastRow = max(2, $rowIdx - 1);

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '004A8F']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]
        ]
    ];
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(28);

    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];
    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);

    for ($i = 1; $i <= count($headers); $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($xlsxPath);
    echo " -> Đã đồng bộ review: {$filename}\n";
}

// 01_Khoa.xlsx
saveReviewXlsx('01_Khoa.xlsx', ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'MoTa'], [
    ['CNTT', 'Khoa Công nghệ Thông tin', 'PGS.TS. Nguyễn Văn Hùng', 'Khoa đào tạo CNTT, CNPM, HTTT, KHMT, ATTT, MMT & KHDL']
], $reviewDir);

// 02_BoMon.xlsx
saveReviewXlsx('02_BoMon.xlsx', ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon'], [
    ['CNPM', 'Bộ môn Công nghệ Phần mềm', 'CNTT', 'TS. Trần Anh Dũng'],
    ['HTTT', 'Bộ môn Hệ thống Thông tin', 'CNTT', 'TS. Phạm Minh Tuấn'],
    ['ATTT', 'Bộ môn An toàn Thông tin', 'CNTT', 'TS. Trịnh Văn Thành'],
    ['MMT',  'Bộ môn Mạng máy tính & Viễn thông', 'CNTT', 'TS. Lê Hải Đăng'],
    ['KHMT', 'Bộ môn Khoa học Máy tính', 'CNTT', 'PGS.TS. Võ Quốc Phong'],
    ['KHDL', 'Bộ môn Khoa học Dữ liệu & AI', 'CNTT', 'TS. Đỗ Anh Minh'],
], $reviewDir);

// 03_Nganh_ChuyenNganh.xlsx
saveReviewXlsx('03_Nganh_ChuyenNganh.xlsx', ['MaNganh', 'TenNganh', 'MaKhoa'], [
    ['7480201', 'Công nghệ Thông tin', 'CNTT'],
    ['7480103', 'Kỹ thuật Phần mềm', 'CNTT'],
    ['7480104', 'Hệ thống Thông tin', 'CNTT'],
    ['7480202', 'An toàn Thông tin', 'CNTT'],
    ['7480101', 'Khoa học Máy tính', 'CNTT'],
    ['7480102', 'Mạng máy tính và Truyền thông dữ liệu', 'CNTT'],
    ['7460112', 'Khoa học Dữ liệu', 'CNTT'],
], $reviewDir);

// 06_LopHanhChinh.xlsx
saveReviewXlsx('06_LopHanhChinh.xlsx', ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc', 'MaKhoa'], [
    ['12DHCNTT01', '12DHCNTT01', '7480201', '2021-2025', 'CNTT'],
    ['12DHCNTT02', '12DHCNTT02', '7480201', '2021-2025', 'CNTT'],
    ['12DHKPM01',  '12DHKPM01',  '7480103', '2021-2025', 'CNTT'],
    ['12DHHTTT01', '12DHHTTT01', '7480104', '2021-2025', 'CNTT'],
    ['12DHATTT01', '12DHATTT01', '7480202', '2021-2025', 'CNTT'],
    ['12DHKHMT01', '12DHKHMT01', '7480101', '2021-2025', 'CNTT'],
    ['13DHCNTT01', '13DHCNTT01', '7480201', '2022-2026', 'CNTT'],
    ['13DHCNTT02', '13DHCNTT02', '7480201', '2022-2026', 'CNTT'],
], $reviewDir);

echo "Đã đồng bộ thư mục review thành công!\n";
