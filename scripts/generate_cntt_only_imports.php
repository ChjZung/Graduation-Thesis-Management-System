<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$outputDir = __DIR__ . '/../sample_imports';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

function saveDualFormat($filenameBase, array $headers, array $data, $dir)
{
    // 1. Ghi CSV có BOM UTF-8
    $csvPath = $dir . '/' . $filenameBase . '.csv';
    $fp = fopen($csvPath, 'w');
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($data as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    echo " -> Đã tạo: {$filenameBase}.csv (" . count($data) . " dòng)\n";

    // 2. Ghi XLSX định dạng chuẩn đẹp
    $xlsxPath = $dir . '/' . $filenameBase . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($filenameBase, 0, 31));

    // Tiêu đề cột
    foreach ($headers as $colIdx => $h) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue("{$colLetter}1", $h);
    }

    // Dữ liệu
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

    // Style data borders
    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];
    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);

    // Tự động căn chiều rộng cột
    for ($i = 1; $i <= count($headers); $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($xlsxPath);
    echo " -> Đã tạo: {$filenameBase}.xlsx\n";
}

echo "=== TẠO FILE DỮ LIỆU MẪU IMPORT CHỈ CHỨA KHOA/BỘ MÔN/NGÀNH CNTT ===\n";

// 1. Khoa: Chỉ Khoa Công nghệ Thông tin
$headersKhoa = ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'MoTa'];
$dataKhoa = [
    ['CNTT', 'Khoa Công nghệ Thông tin', 'PGS.TS. Nguyễn Văn Hùng', 'Khoa đào tạo CNTT, CNPM, HTTT, KHMT, ATTT, MMT & KHDL']
];
saveDualFormat('01_Khoa_Mau', $headersKhoa, $dataKhoa, $outputDir);

// 2. Bộ môn: Chỉ 6 bộ môn thuộc Khoa CNTT
$headersBoMon = ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon'];
$dataBoMon = [
    ['CNPM', 'Bộ môn Công nghệ Phần mềm', 'CNTT', 'TS. Trần Anh Dũng'],
    ['HTTT', 'Bộ môn Hệ thống Thông tin', 'CNTT', 'TS. Phạm Minh Tuấn'],
    ['ATTT', 'Bộ môn An toàn Thông tin', 'CNTT', 'TS. Trịnh Văn Thành'],
    ['MMT',  'Bộ môn Mạng máy tính & Viễn thông', 'CNTT', 'TS. Lê Hải Đăng'],
    ['KHMT', 'Bộ môn Khoa học Máy tính', 'CNTT', 'PGS.TS. Võ Quốc Phong'],
    ['KHDL', 'Bộ môn Khoa học Dữ liệu & AI', 'CNTT', 'TS. Đỗ Anh Minh'],
];
saveDualFormat('02_BoMon_Mau', $headersBoMon, $dataBoMon, $outputDir);

// 3. Ngành: Ngành CNTT làm trọng tâm (kèm các ngành chuyên sâu thuộc khoa CNTT)
$headersNganh = ['MaNganh', 'TenNganh', 'MaKhoa'];
$dataNganh = [
    ['7480201', 'Công nghệ Thông tin', 'CNTT'],
    ['7480103', 'Kỹ thuật Phần mềm', 'CNTT'],
    ['7480104', 'Hệ thống Thông tin', 'CNTT'],
    ['7480202', 'An toàn Thông tin', 'CNTT'],
    ['7480101', 'Khoa học Máy tính', 'CNTT'],
    ['7480102', 'Mạng máy tính và Truyền thông dữ liệu', 'CNTT'],
    ['7460112', 'Khoa học Dữ liệu', 'CNTT'],
];
saveDualFormat('03_Nganh_Mau', $headersNganh, $dataNganh, $outputDir);

// 4. Lớp: Chỉ các lớp thuộc ngành và khoa CNTT
$headersLop = ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc', 'MaKhoa'];
$dataLop = [
    ['12DHCNTT01', '12DHCNTT01', '7480201', '2021-2025', 'CNTT'],
    ['12DHCNTT02', '12DHCNTT02', '7480201', '2021-2025', 'CNTT'],
    ['12DHKPM01',  '12DHKPM01',  '7480103', '2021-2025', 'CNTT'],
    ['12DHHTTT01', '12DHHTTT01', '7480104', '2021-2025', 'CNTT'],
    ['12DHATTT01', '12DHATTT01', '7480202', '2021-2025', 'CNTT'],
    ['12DHKHMT01', '12DHKHMT01', '7480101', '2021-2025', 'CNTT'],
    ['13DHCNTT01', '13DHCNTT01', '7480201', '2022-2026', 'CNTT'],
    ['13DHCNTT02', '13DHCNTT02', '7480201', '2022-2026', 'CNTT'],
];
saveDualFormat('04_Lop_Mau', $headersLop, $dataLop, $outputDir);

// 5. Học kỳ: Các học kỳ phục vụ xét KLTN
$headersHocKy = ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];
$dataHocKy = [
    ['HK2425_1', 'Học kỳ 1 (2024-2025)', '2024-2025', '2024-09-01', '2025-01-15', 'Đã kết thúc'],
    ['HK2425_2', 'Học kỳ 2 (2024-2025)', '2024-2025', '2025-02-15', '2025-06-30', 'Đang diễn ra'],
    ['HK2526_1', 'Học kỳ 1 (2025-2026)', '2025-2026', '2025-09-01', '2026-01-15', 'Chưa bắt đầu'],
];
saveDualFormat('05_HocKy_Mau', $headersHocKy, $dataHocKy, $outputDir);

// 6. Giảng viên: 26 Giảng viên thuộc 6 bộ môn Khoa CNTT
$headersGV = ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai'];
$dataGV = [
    ['GV00000001', 'PGS.TS. Nguyễn Văn Hùng', 'hungnv@huit.edu.vn', '0903123001', '1975-04-12', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV00000002', 'TS. Trần Anh Dũng',     'dungta@huit.edu.vn', '0903123002', '1979-08-20', 'Nam', '',            'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000003', 'TS. Phạm Minh Tuấn',    'tuanpm@huit.edu.vn', '0903123003', '1982-11-15', 'Nam', '',            'Tiến sĩ', 'HTTT', 'Đang công tác'],
    ['GV00000004', 'TS. Trịnh Văn Thành',   'thanhtv@huit.edu.vn', '0903123004', '1980-02-18', 'Nam', '',            'Tiến sĩ', 'ATTT', 'Đang công tác'],
    ['GV00000005', 'TS. Lê Hải Đăng',       'danglh@huit.edu.vn', '0903123005', '1984-06-25', 'Nam', '',            'Tiến sĩ', 'MMT',  'Đang công tác'],
    ['GV00000006', 'PGS.TS. Võ Quốc Phong', 'phongvq@huit.edu.vn', '0903123006', '1976-09-30', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV00000007', 'TS. Đỗ Anh Minh',       'minhda@huit.edu.vn', '0903123007', '1985-03-14', 'Nam', '',            'Tiến sĩ', 'KHDL', 'Đang công tác'],
    ['GV00000021', 'ThS. Đặng Hải Giang',   'giangdh@huit.edu.vn', '0903123021', '1988-05-14', 'Nam', '',            'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000022', 'ThS. Trần Thị Bích',    'bichtb@huit.edu.vn', '0903123022', '1989-09-02', 'Nữ',  '',            'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV00000023', 'TS. Lê Hoàng Cường',    'cuonglh@huit.edu.vn', '0903123023', '1984-12-18', 'Nam', '',            'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000024', 'ThS. Ngô Đức Hải',      'haind@huit.edu.vn', '0903123024', '1990-07-07', 'Nam', '',            'Thạc sĩ', 'ATTT', 'Đang công tác'],
    ['GV00000025', 'TS. Dương Thúy Hằng',   'hangdt@huit.edu.vn', '0903123025', '1986-10-25', 'Nữ',  '',            'Tiến sĩ', 'KHDL', 'Đang công tác'],
    ['GV00000026', 'ThS. Lý Thanh Lan',     'lanlt@huit.edu.vn', '0903123026', '1991-03-18', 'Nữ',  '',            'Thạc sĩ', 'MMT',  'Đang công tác'],
    ['GV00000027', 'TS. Trịnh Văn Khang',   'khangtv@huit.edu.vn', '0903123027', '1983-08-05', 'Nam', '',            'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV00000028', 'ThS. Mai Tuyết Nga',    'ngamt@huit.edu.vn', '0903123028', '1989-11-12', 'Nữ',  '',            'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000029', 'TS. Võ Đình Phúc',      'phucvd@huit.edu.vn', '0903123029', '1982-01-30', 'Nam', '',            'Tiến sĩ', 'HTTT', 'Đang công tác'],
    ['GV00000030', 'ThS. Hoàng Thị Em',     'emht@huit.edu.vn', '0903123030', '1992-06-19', 'Nữ',  '',            'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000038', 'ThS. Nguyễn Hoàng Anh', 'anhnh@huit.edu.vn', '0903123038', '1987-04-15', 'Nam', '',            'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000039', 'ThS. Lê Phương Thảo',   'thaolp@huit.edu.vn', '0903123039', '1990-09-22', 'Nữ',  '',            'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV00000040', 'TS. Vũ Đình Khôi',      'khoivd@huit.edu.vn', '0903123040', '1981-11-08', 'Nam', '',            'Tiến sĩ', 'ATTT', 'Đang công tác'],
    ['GV00000041', 'ThS. Huỳnh Quốc Bảo',   'baohq@huit.edu.vn', '0903123041', '1989-02-14', 'Nam', '',            'Thạc sĩ', 'MMT',  'Đang công tác'],
    ['GV00000042', 'TS. Tôn Nữ Quỳnh Như',  'nhutnq@huit.edu.vn', '0903123042', '1985-07-29', 'Nữ',  '',            'Tiến sĩ', 'KHDL', 'Đang công tác'],
    ['GV00000043', 'ThS. Cù Huy Sơn',       'sonch@huit.edu.vn', '0903123043', '1993-12-03', 'Nam', '',            'Thạc sĩ', 'KHMT', 'Đang công tác'],
    ['GV00000044', 'TS. Thạch Văn Tài',     'taitv@huit.edu.vn', '0903123044', '1984-05-17', 'Nam', '',            'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV00000045', 'ThS. Uông Thị Uyên',    'uyenut@huit.edu.vn', '0903123045', '1991-08-20', 'Nữ',  '',            'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV00000046', 'TS. Phí Văn Việt',      'vietpv@huit.edu.vn', '0903123046', '1980-03-11', 'Nam', '',            'Tiến sĩ', 'ATTT', 'Đang công tác'],
];
saveDualFormat('06_GiangVien_Mau', $headersGV, $dataGV, $outputDir);

// 7. Sinh viên: 18 Sinh viên thuộc các lớp ngành CNTT
$headersSV = ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'];
$dataSV = [
    ['2001210001', 'Nguyễn Hoàng Long', '2001210001@st.huit.edu.vn', '0978001001', '2003-05-12', 'Nam', '12DHCNTT01', '2021-2025', '128', '3.45', 'Đang học'],
    ['2001210002', 'Trần Minh Quân',     '2001210002@st.huit.edu.vn', '0978001002', '2003-08-20', 'Nam', '12DHCNTT01', '2021-2025', '125', '3.20', 'Đang học'],
    ['2001210003', 'Lê Phương Anh',      '2001210003@st.huit.edu.vn', '0978001003', '2003-11-15', 'Nữ',  '12DHCNTT01', '2021-2025', '130', '3.65', 'Đang học'],
    ['2001210004', 'Phạm Đức Duy',      '2001210004@st.huit.edu.vn', '0978001004', '2003-02-18', 'Nam', '12DHCNTT02', '2021-2025', '122', '3.10', 'Đang học'],
    ['2001210005', 'Hoàng Thùy Linh',    '2001210005@st.huit.edu.vn', '0978001005', '2003-06-25', 'Nữ',  '12DHCNTT02', '2021-2025', '129', '3.52', 'Đang học'],
    ['2001210006', 'Huỳnh Quốc Huy',     '2001210006@st.huit.edu.vn', '0978001006', '2003-09-30', 'Nam', '12DHKPM01',  '2021-2025', '132', '3.70', 'Đang học'],
    ['2001210007', 'Võ Thị Kim Ngân',    '2001210007@st.huit.edu.vn', '0978001007', '2003-03-14', 'Nữ',  '12DHKPM01',  '2021-2025', '124', '3.15', 'Đang học'],
    ['2001210008', 'Đặng Thành Nam',     '2001210008@st.huit.edu.vn', '0978001008', '2003-12-05', 'Nam', '12DHHTTT01', '2021-2025', '126', '3.28', 'Đang học'],
    ['2001210009', 'Bùi Thị Thanh Hằng', '2001210009@st.huit.edu.vn', '0978001009', '2003-07-22', 'Nữ',  '12DHHTTT01', '2021-2025', '131', '3.60', 'Đang học'],
    ['2001210010', 'Đỗ Trọng Hiếu',      '2001210010@st.huit.edu.vn', '0978001010', '2003-05-19', 'Nam', '12DHATTT01', '2021-2025', '127', '3.35', 'Đang học'],
    ['2001210011', 'Hồ Diệu Linh',       '2001210011@st.huit.edu.vn', '0978001011', '2003-10-10', 'Nữ',  '12DHATTT01', '2021-2025', '125', '3.22', 'Đang học'],
    ['2001210012', 'Ngô Văn Tuấn',       '2001210012@st.huit.edu.vn', '0978001012', '2003-01-28', 'Nam', '12DHKHMT01', '2021-2025', '135', '3.85', 'Đang học'],
    ['2001210013', 'Dương Bảo Ngọc',     '2001210013@st.huit.edu.vn', '0978001013', '2003-03-08', 'Nữ',  '12DHKHMT01', '2021-2025', '130', '3.58', 'Đang học'],
    ['2001210042', 'Nông Đức Mạnh',      '2001210042@st.huit.edu.vn', '0978001042', '2003-04-10', 'Nam', '12DHCNTT01', '2021-2025', '120', '3.05', 'Đang học'],
    ['2001210043', 'Lục Hải Yến',        '2001210043@st.huit.edu.vn', '0978001043', '2003-07-16', 'Nữ',  '12DHKPM01',  '2021-2025', '134', '3.75', 'Đang học'],
    ['2001210044', 'Châu Gia Hưng',      '2001210044@st.huit.edu.vn', '0978001044', '2003-10-24', 'Nam', '12DHHTTT01', '2021-2025', '128', '3.32', 'Đang học'],
    ['2001210045', 'Mạc Quỳnh Giao',     '2001210045@st.huit.edu.vn', '0978001045', '2003-01-19', 'Nữ',  '12DHATTT01', '2021-2025', '126', '3.20', 'Đang học'],
    ['2001210046', 'Phí Trọng Nhân',      '2001210046@st.huit.edu.vn', '0978001046', '2003-06-08', 'Nam', '12DHKHMT01', '2021-2025', '133', '3.68', 'Đang học'],
];
saveDualFormat('07_SinhVien_Mau', $headersSV, $dataSV, $outputDir);

// 8. Bổ nhiệm Trưởng khoa & Trưởng bộ môn CNTT
$headersTBM = ['MaCanBo', 'TenDangNhap', 'HoTen', 'Email', 'SoDienThoai', 'MaVaiTro', 'MaKhoa', 'MaBoMon', 'MatKhau'];
$dataTBM = [
    ['GV00000001', 'TK_CNTT_GV001',        'PGS.TS. Nguyễn Văn Hùng', 'hungnv@huit.edu.vn', '0903123001', 'VT05', 'CNTT', '',     'Huit@2026'],
    ['GV00000002', 'TBM_CNTT_CNPM_GV002',  'TS. Trần Anh Dũng',     'dungta@huit.edu.vn', '0903123002', 'VT04', 'CNTT', 'CNPM', 'Huit@2026'],
    ['GV00000003', 'TBM_CNTT_HTTT_GV003',  'TS. Phạm Minh Tuấn',    'tuanpm@huit.edu.vn', '0903123003', 'VT04', 'CNTT', 'HTTT', 'Huit@2026'],
    ['GV00000004', 'TBM_CNTT_ATTT_GV004',  'TS. Trịnh Văn Thành',   'thanhtv@huit.edu.vn', '0903123004', 'VT04', 'CNTT', 'ATTT', 'Huit@2026'],
    ['GV00000005', 'TBM_CNTT_MMT_GV005',   'TS. Lê Hải Đăng',       'danglh@huit.edu.vn', '0903123005', 'VT04', 'CNTT', 'MMT',  'Huit@2026'],
    ['GV00000006', 'TBM_CNTT_KHMT_GV006',  'PGS.TS. Võ Quốc Phong', 'phongvq@huit.edu.vn', '0903123006', 'VT04', 'CNTT', 'KHMT', 'Huit@2026'],
    ['GV00000007', 'TBM_CNTT_KHDL_GV007',  'TS. Đỗ Anh Minh',       'minhda@huit.edu.vn', '0903123007', 'VT04', 'CNTT', 'KHDL', 'Huit@2026'],
];
saveDualFormat('08_TaiKhoan_TBM_TK_Mau', $headersTBM, $dataTBM, $outputDir);

// 9. Danh sách sinh viên CNTT đủ điều kiện theo học kỳ
$headersDK = ['MSSV', 'HoTen', 'TrangThai', 'GhiChu'];
$dataDK = [
    ['2001210001', 'Nguyễn Hoàng Long', 'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (128 TC, GPA 3.45)'],
    ['2001210002', 'Trần Minh Quân',     'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (125 TC, GPA 3.20)'],
    ['2001210003', 'Lê Phương Anh',      'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (130 TC, GPA 3.65)'],
    ['2001210004', 'Phạm Đức Duy',      'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (122 TC, GPA 3.10)'],
    ['2001210005', 'Hoàng Thùy Linh',    'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (129 TC, GPA 3.52)'],
    ['2001210006', 'Huỳnh Quốc Huy',     'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (132 TC, GPA 3.70)'],
    ['2001210007', 'Võ Thị Kim Ngân',    'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (124 TC, GPA 3.15)'],
    ['2001210008', 'Đặng Thành Nam',     'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (126 TC, GPA 3.28)'],
    ['2001210009', 'Bùi Thị Thanh Hằng', 'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (131 TC, GPA 3.60)'],
    ['2001210010', 'Đỗ Trọng Hiếu',      'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (127 TC, GPA 3.35)'],
    ['2001210011', 'Hồ Diệu Linh',       'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (125 TC, GPA 3.22)'],
    ['2001210012', 'Ngô Văn Tuấn',       'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (135 TC, GPA 3.85)'],
    ['2001210013', 'Dương Bảo Ngọc',     'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (130 TC, GPA 3.58)'],
    ['2001210042', 'Nông Đức Mạnh',      'Chưa đủ điều kiện', 'Thiếu tín chỉ tích lũy (120/125 TC)'],
    ['2001210043', 'Lục Hải Yến',        'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (134 TC, GPA 3.75)'],
    ['2001210044', 'Châu Gia Hưng',      'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (128 TC, GPA 3.32)'],
    ['2001210045', 'Mạc Quỳnh Giao',     'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (126 TC, GPA 3.20)'],
    ['2001210046', 'Phí Trọng Nhân',      'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp (133 TC, GPA 3.68)'],
];
saveDualFormat('09_SinhVien_TheoHocKy_Mau', $headersDK, $dataDK, $outputDir);

echo "=== HOÀN TẤT CHUẨN HÓA DỮ LIỆU CNTT ===\n";
