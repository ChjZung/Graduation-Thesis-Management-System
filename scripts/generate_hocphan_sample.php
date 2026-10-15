<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$outputDir = __DIR__ . '/../sample_imports';

$headers = ['MaHocPhan', 'TenHocPhan', 'SoTinChi', 'MaKhoa', 'MaBoMon', 'LoaiHocPhan', 'MoTa'];

$data = [
    // 1. Bộ môn Công nghệ phần mềm (CNPM)
    [
        'HP_KLCN_CNPM',
        'Khóa luận cử nhân - Công nghệ phần mềm',
        10,
        'CNTT',
        'CNPM',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Công nghệ phần mềm (10 tín chỉ).'
    ],
    [
        'HP_KLTN_CNPM',
        'Khóa luận tốt nghiệp - Công nghệ phần mềm',
        10,
        'CNTT',
        'CNPM',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành Công nghệ phần mềm (10 tín chỉ).'
    ],

    // 2. Bộ môn Hệ thống thông tin (HTTT)
    [
        'HP_KLCN_HTTT',
        'Khóa luận cử nhân - Hệ thống thông tin',
        10,
        'CNTT',
        'HTTT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Hệ thống thông tin quản lý và phân tích nghiệp vụ (10 tín chỉ).'
    ],
    [
        'HP_KLTN_HTTT',
        'Khóa luận tốt nghiệp - Hệ thống thông tin',
        10,
        'CNTT',
        'HTTT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành Hệ thống thông tin (10 tín chỉ).'
    ],

    // 3. Bộ môn An toàn thông tin (ATTT)
    [
        'HP_KLCN_ATTT',
        'Khóa luận cử nhân - An toàn thông tin',
        10,
        'CNTT',
        'ATTT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành An toàn thông tin và an ninh mạng (10 tín chỉ).'
    ],
    [
        'HP_KLTN_ATTT',
        'Khóa luận tốt nghiệp - An toàn thông tin',
        10,
        'CNTT',
        'ATTT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành An toàn thông tin (10 tín chỉ).'
    ],

    // 4. Bộ môn Khoa học máy tính (KHMT)
    [
        'HP_KLCN_KHMT',
        'Khóa luận cử nhân - Khoa học máy tính',
        10,
        'CNTT',
        'KHMT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Khoa học máy tính và thuật toán ứng dụng (10 tín chỉ).'
    ],
    [
        'HP_KLTN_KHMT',
        'Khóa luận tốt nghiệp - Khoa học máy tính',
        10,
        'CNTT',
        'KHMT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học máy tính (10 tín chỉ).'
    ],

    // 5. Bộ môn Khoa học dữ liệu & AI (KHDL)
    [
        'HP_KLCN_KHDL',
        'Khóa luận cử nhân - Khoa học dữ liệu & AI',
        10,
        'CNTT',
        'KHDL',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Khoa học dữ liệu và phân tích định lượng (10 tín chỉ).'
    ],
    [
        'HP_KLTN_KHDL',
        'Khóa luận tốt nghiệp - Khoa học dữ liệu & AI',
        10,
        'CNTT',
        'KHDL',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học dữ liệu & Trí tuệ nhân tạo (10 tín chỉ).'
    ],

    // 6. Bộ môn Mạng máy tính & Viễn thông (MMT)
    [
        'HP_KLCN_MMT',
        'Khóa luận cử nhân - Mạng máy tính & Viễn thông',
        10,
        'CNTT',
        'MMT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Mạng máy tính và truyền thông dữ liệu (10 tín chỉ).'
    ],
    [
        'HP_KLTN_MMT',
        'Khóa luận tốt nghiệp - Mạng máy tính & Viễn thông',
        10,
        'CNTT',
        'MMT',
        'Khóa luận',
        'Học phần Khóa luận tốt nghiệp chuyên ngành Mạng máy tính và Viễn thông (10 tín chỉ).'
    ],
];

function writeDualFiles($baseName, $headers, $data, $dir) {
    // 1. CSV
    $csvFile = $dir . '/' . $baseName . '.csv';
    $fp = fopen($csvFile, 'w');
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($data as $r) {
        fputcsv($fp, $r);
    }
    fclose($fp);
    echo " -> Đã tạo {$baseName}.csv\n";

    // 2. XLSX
    $xlsxFile = $dir . '/' . $baseName . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('HocPhan_KhoaLuan');

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

    // Style Header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '0072CE']
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

    // Style Data
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
    $writer->save($xlsxFile);
    echo " -> Đã tạo {$baseName}.xlsx\n";
}

writeDualFiles('10_HocPhan_KhoaLuan_Mau', $headers, $data, $outputDir);
writeDualFiles('Template_HocPhan', $headers, $data, $outputDir);

echo "Hoàn tất tạo file mẫu học phần KLTN và Khóa luận kỹ sư!\n";
