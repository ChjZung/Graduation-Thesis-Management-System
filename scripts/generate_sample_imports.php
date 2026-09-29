<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$outDir = __DIR__ . '/../sample_imports';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

function saveFiles(string $baseName, array $headers, array $dataRows, string $outDir) {
    // 1. Write XLSX
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sheet1');

    $col = 1;
    foreach ($headers as $h) {
        $sheet->setCellValueByColumnAndRow($col++, 1, $h);
    }

    $rowNum = 2;
    foreach ($dataRows as $row) {
        $col = 1;
        foreach ($row as $val) {
            $sheet->setCellValueExplicitByColumnAndRow($col++, $rowNum, (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $rowNum++;
    }

    foreach (range(1, count($headers)) as $colIndex) {
        $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
    }

    $xlsxWriter = new Xlsx($spreadsheet);
    $xlsxFile = $outDir . '/' . $baseName . '.xlsx';
    $xlsxWriter->save($xlsxFile);

    // 2. Write CSV with UTF-8 BOM
    $csvFile = $outDir . '/' . $baseName . '.csv';
    $fp = fopen($csvFile, 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($dataRows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    echo "Saved {$baseName}.xlsx & {$baseName}.csv (" . count($dataRows) . " rows)\n";
}

// -------------------------------------------------------------
// 1. 01_Khoa_Mau: Danh sách các Khoa (Tên Trưởng khoa chân thật)
// -------------------------------------------------------------
$khoaList = [
    ['CNTT', 'Khoa Công nghệ Thông tin', 'PGS.TS. Nguyễn Văn Hùng', 'Khoa đào tạo CNTT, CNPM, HTTT, KHMT, ATTT, MMT'],
    ['QTKD', 'Khoa Quản trị Kinh doanh', 'PGS.TS. Trần Đình Nam', 'Khoa Quản trị kinh doanh và Marketing'],
    ['TP', 'Khoa Công nghệ Thực phẩm', 'GS.TS. Lê Thị Mai', 'Khoa Công nghệ thực phẩm và Dinh dưỡng'],
    ['DDT', 'Khoa Điện - Điện tử', 'PGS.TS. Vũ Trọng Hưng', 'Khoa Kỹ thuật Điện, Điện tử viễn thông, Tự động hóa'],
    ['CK', 'Khoa Cơ khí - Chế tạo máy', 'PGS.TS. Đặng Quốc Thịnh', 'Khoa Cơ khí động lực, Cơ điện tử, Chế tạo máy'],
    ['NN', 'Khoa Ngoại ngữ', 'TS. Phạm Thị Bích Loan', 'Khoa Ngôn ngữ Anh, Tiếng Trung, Tiếng Nhật'],
    ['KTTC', 'Khoa Kế toán - Tài chính', 'PGS.TS. Ngô Xuân Hiếu', 'Khoa Kế toán doanh nghiệp, Kiểm toán và Tài chính'],
    ['LUAT', 'Khoa Luật', 'TS. Phan Văn Phúc', 'Khoa Luật kinh tế và Luật thương mại quốc tế'],
    ['SH', 'Khoa Công nghệ Sinh học', 'PGS.TS. Đỗ Hoài Nam', 'Khoa Sinh học ứng dụng và Kỹ thuật y sinh'],
    ['HH', 'Khoa Hóa học & Công nghệ Hóa', 'TS. Dương Văn Cường', 'Khoa Công nghệ kỹ thuật Hóa học và Vật liệu polyme'],
    ['DL', 'Khoa Du lịch & Khách sạn', 'TS. Hoàng Yến Linh', 'Khoa Quản trị dịch vụ du lịch, Lữ hành và Khách sạn'],
    ['MT', 'Khoa Môi trường & Tài nguyên', 'TS. Bùi Mai Hoa', 'Khoa Khoa học Môi trường và Quản lý tài nguyên'],
    ['TOAN', 'Khoa Toán - Thống kê', 'TS. Lê Hải Đăng', 'Khoa Toán ứng dụng, Khoa học dữ liệu'],
    ['KTS', 'Khoa Kiến trúc - Mỹ thuật', 'TS. Phạm Minh Tuấn', 'Khoa Kiến trúc công trình và Thiết kế đồ họa'],
    ['XD', 'Khoa Kỹ thuật Xây dựng', 'TS. Nguyễn Hoàng Long', 'Khoa Xây dựng dân dụng và Công trình giao thông'],
    ['TMDT', 'Khoa Thương mại Điện tử', 'TS. Võ Duy Hùng', 'Khoa Kinh tế số và Thương mại điện tử'],
    ['TC', 'Khoa Tài chính - Ngân hàng', 'PGS.TS. Nguyễn Hồng Phong', 'Khoa Thị trường vốn, Chứng khoán và Fintech'],
    ['TT', 'Khoa Truyền thông & Quan hệ công chúng', 'TS. Lương Thu Thảo', 'Khoa Quan hệ công chúng và Báo chí truyền thông'],
    ['LOG', 'Khoa Logistics & Chuỗi cung ứng', 'PGS.TS. Mai Thế Vinh', 'Khoa Logistics và Quản trị chuỗi cung ứng toàn cầu'],
    ['ATTT', 'Khoa An toàn Thông tin & Mạng', 'TS. Trịnh Văn Thành', 'Khoa An toàn an ninh mạng và Mật mã học']
];

$khoaHeaders = ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'MoTa'];
saveFiles('01_Khoa_Mau', $khoaHeaders, $khoaList, $outDir);

// -------------------------------------------------------------
// 2. 02_BoMon_Mau: Danh sách các Bộ môn (Tên Trưởng bộ môn chân thật)
// -------------------------------------------------------------
$boMonList = [
    // Khoa CNTT
    ['CNPM', 'Bộ môn Công nghệ Phần mềm', 'CNTT', 'TS. Trần Anh Dũng'],
    ['HTTT', 'Bộ môn Hệ thống Thông tin', 'CNTT', 'TS. Phạm Minh Tuấn'],
    ['ATTT', 'Bộ môn An toàn Thông tin', 'CNTT', 'TS. Trịnh Văn Thành'],
    ['MMT', 'Bộ môn Mạng máy tính & Viễn thông', 'CNTT', 'TS. Lê Hải Đăng'],
    ['KHMT', 'Bộ môn Khoa học Máy tính', 'CNTT', 'PGS.TS. Võ Quốc Phong'],
    ['KHDL', 'Bộ môn Khoa học Dữ liệu & AI', 'CNTT', 'TS. Đỗ Anh Minh'],
    // Khoa QTKD
    ['QTKD_DN', 'Bộ môn Quản trị Doanh nghiệp', 'QTKD', 'TS. Bùi Thu Hà'],
    ['MKT', 'Bộ môn Marketing & Bán hàng', 'QTKD', 'TS. Nguyễn Văn An'],
    // Khoa TP
    ['CBTP', 'Bộ môn Chế biến Thực phẩm', 'TP', 'PGS.TS. Đỗ Hoài Nam'],
    ['DBTP', 'Bộ môn Đảm bảo Chất lượng & ATTP', 'TP', 'TS. Trương Tuyết Mai'],
    // Khoa DDT
    ['KTD', 'Bộ môn Kỹ thuật Điện & Năng lượng', 'DDT', 'TS. Vũ Anh Quân'],
    ['DT', 'Bộ môn Điện tử & Điều khiển Tự động', 'DDT', 'TS. Hồ Vĩnh Thắng'],
    // Khoa CK
    ['CTM', 'Bộ môn Chế tạo máy', 'CK', 'TS. Phan Trọng Nghĩa'],
    ['CDT', 'Bộ môn Cơ điện tử & Robot', 'CK', 'TS. Chu Văn Quân'],
    // Khoa NN
    ['TA_CN', 'Bộ môn Tiếng Anh Chuyên ngành', 'NN', 'ThS. Trần Thị Bích'],
    ['TA_CB', 'Bộ môn Tiếng Anh Cơ bản', 'NN', 'ThS. Hoàng Thị Em'],
    // Khoa KTTC
    ['KT', 'Bộ môn Kế toán Doanh nghiệp', 'KTTC', 'TS. Đinh Hoàng Xuân'],
    ['TC', 'Bộ môn Tài chính & Thuế', 'KTTC', 'TS. Cao Thái Vinh'],
    // Khoa LUAT
    ['LKTE', 'Bộ môn Luật Kinh tế', 'LUAT', 'TS. Phan Văn Phúc'],
    // Khoa SH
    ['SHUD', 'Bộ môn Công nghệ Sinh học Ứng dụng', 'SH', 'PGS.TS. Đỗ Hoài Nam'],
    // Khoa DL
    ['QTDL', 'Bộ môn Quản trị Du lịch & Lữ hành', 'DL', 'TS. Hoàng Yến Linh'],
    ['QTKS', 'Bộ môn Quản trị Khách sạn & Nhà hàng', 'DL', 'ThS. Mai Tuyết Nga'],
    // Khoa MT
    ['KHMT_MT', 'Bộ môn Khoa học Môi trường', 'MT', 'TS. Bùi Mai Hoa'],
    // Khoa TMDT
    ['TMDT_SO', 'Bộ môn Kinh tế số & E-Commerce', 'TMDT', 'TS. Võ Duy Hùng'],
    // Khoa LOG
    ['LOG_CCU', 'Bộ môn Quản trị Chuỗi cung ứng', 'LOG', 'PGS.TS. Mai Thế Vinh']
];

$boMonHeaders = ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon'];
saveFiles('02_BoMon_Mau', $boMonHeaders, $boMonList, $outDir);

// -------------------------------------------------------------
// 3. 03_Nganh_Mau: Danh sách các Ngành đào tạo
// -------------------------------------------------------------
$nganhList = [
    ['7480201', 'Công nghệ Thông tin', 'CNTT'],
    ['7480103', 'Kỹ thuật Phần mềm', 'CNTT'],
    ['7480104', 'Hệ thống Thông tin', 'CNTT'],
    ['7480202', 'An toàn Thông tin', 'CNTT'],
    ['7480101', 'Khoa học Máy tính', 'CNTT'],
    ['7480102', 'Mạng máy tính và Truyền thông dữ liệu', 'CNTT'],
    ['7460112', 'Khoa học Dữ liệu', 'CNTT'],
    ['7340101', 'Quản trị Kinh doanh', 'QTKD'],
    ['7340115', 'Marketing', 'QTKD'],
    ['7540101', 'Công nghệ Thực phẩm', 'TP'],
    ['7720401', 'Dinh dưỡng và Khoa học Thực phẩm', 'TP'],
    ['7510301', 'Kỹ thuật Điện - Điện tử', 'DDT'],
    ['7510303', 'Kỹ thuật Điều khiển và Tự động hóa', 'DDT'],
    ['7510201', 'Công nghệ Kỹ thuật Cơ khí', 'CK'],
    ['7510203', 'Công nghệ Kỹ thuật Cơ điện tử', 'CK'],
    ['7220201', 'Ngôn ngữ Anh', 'NN'],
    ['7340301', 'Kế toán', 'KTTC'],
    ['7340201', 'Tài chính - Ngân hàng', 'TC'],
    ['7380107', 'Luật Kinh tế', 'LUAT'],
    ['7420201', 'Công nghệ Sinh học', 'SH'],
    ['7810103', 'Quản trị Dịch vụ Du lịch và Lữ hành', 'DL'],
    ['7520320', 'Kỹ thuật Môi trường', 'MT'],
    ['7340122', 'Thương mại Điện tử', 'TMDT'],
    ['7510605', 'Logistics và Quản trị Chuỗi cung ứng', 'LOG']
];

$nganhHeaders = ['MaNganh', 'TenNganh', 'MaKhoa'];
saveFiles('03_Nganh_Mau', $nganhHeaders, $nganhList, $outDir);

// -------------------------------------------------------------
// 4. 04_Lop_Mau: Danh sách các Lớp sinh viên chính quy
// -------------------------------------------------------------
$lopList = [
    ['12DHCNTT01', '12DHCNTT01', '7480201', '2021-2025', 'CNTT'],
    ['12DHCNTT02', '12DHCNTT02', '7480201', '2021-2025', 'CNTT'],
    ['12DHKPM01',  '12DHKPM01',  '7480103', '2021-2025', 'CNTT'],
    ['12DHHTTT01', '12DHHTTT01', '7480104', '2021-2025', 'CNTT'],
    ['12DHATTT01', '12DHATTT01', '7480202', '2021-2025', 'CNTT'],
    ['12DHKHMT01', '12DHKHMT01', '7480101', '2021-2025', 'CNTT'],
    ['12DHQTKD01', '12DHQTKD01', '7340101', '2021-2025', 'QTKD'],
    ['12DHMKT01',  '12DHMKT01',  '7340115', '2021-2025', 'QTKD'],
    ['12DHTP01',   '12DHTP01',   '7540101', '2021-2025', 'TP'],
    ['12DHDDT01',  '12DHDDT01',  '7510301', '2021-2025', 'DDT'],
    ['12DHCK01',   '12DHCK01',   '7510201', '2021-2025', 'CK'],
    ['12DHNNA01',  '12DHNNA01',  '7220201', '2021-2025', 'NN'],
    ['12DHKT01',   '12DHKT01',   '7340301', '2021-2025', 'KTTC'],
    ['12DHTC01',   '12DHTC01',   '7340201', '2021-2025', 'TC'],
    ['12DHLKT01',  '12DHLKT01',  '7380107', '2021-2025', 'LUAT'],
    ['12DHCNSH01', '12DHCNSH01', '7420201', '2021-2025', 'SH'],
    ['12DHDL01',   '12DHDL01',   '7810103', '2021-2025', 'DL'],
    ['12DHMT01',   '12DHMT01',   '7520320', '2021-2025', 'MT'],
    ['12DHTMDT01', '12DHTMDT01', '7340122', '2021-2025', 'TMDT'],
    ['12DHLOG01',  '12DHLOG01',  '7510605', '2021-2025', 'LOG'],
];

$lopHeaders = ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc', 'MaKhoa'];
saveFiles('04_Lop_Mau', $lopHeaders, $lopList, $outDir);

// -------------------------------------------------------------
// 5. 05_HocKy_Mau: Danh sách Học kỳ
// -------------------------------------------------------------
$hocKyList = [
    ['HK2223_1', 'Học kỳ 1 (2022-2023)', '2022-2023', '2022-09-05', '2023-01-15', 'Đã kết thúc'],
    ['HK2223_2', 'Học kỳ 2 (2022-2023)', '2022-2023', '2023-01-16', '2023-06-10', 'Đã kết thúc'],
    ['HK2324_1', 'Học kỳ 1 (2023-2024)', '2023-2024', '2023-09-05', '2024-01-15', 'Đã kết thúc'],
    ['HK2324_2', 'Học kỳ 2 (2023-2024)', '2023-2024', '2024-01-16', '2024-06-10', 'Đã kết thúc'],
    ['HK2425_1', 'Học kỳ 1 (2024-2025)', '2024-2025', '2024-09-05', '2025-01-15', 'Đang diễn ra'],
    ['HK2425_2', 'Học kỳ 2 (2024-2025)', '2024-2025', '2025-01-16', '2025-06-10', 'Chưa bắt đầu'],
    ['HK2526_1', 'Học kỳ 1 (2025-2026)', '2025-2026', '2025-09-05', '2026-01-15', 'Chưa bắt đầu']
];

$hocKyHeaders = ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];
saveFiles('05_HocKy_Mau', $hocKyHeaders, $hocKyList, $outDir);

// -------------------------------------------------------------
// 6. 06_GiangVien_Mau: 50 Giảng viên (TẤT CẢ TBM VÀ TRƯỞNG KHOA NẰM Ở ĐÂY)
// -------------------------------------------------------------
$giangVienList = [
    // --- LÃNH ĐẠO KHOA & BỘ MÔN CNTT ---
    ['GV001', 'PGS.TS. Nguyễn Văn Hùng', 'hungnv@huit.edu.vn', '0903123001', '1975-04-12', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV002', 'TS. Trần Anh Dũng',       'dungta@huit.edu.vn', '0903123002', '1979-08-20', 'Nam', '',             'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV003', 'TS. Phạm Minh Tuấn',      'tuanpm@huit.edu.vn', '0903123003', '1982-11-15', 'Nam', '',             'Tiến sĩ', 'HTTT', 'Đang công tác'],
    ['GV004', 'TS. Trịnh Văn Thành',     'thanhtv@huit.edu.vn','0903123004', '1980-02-18', 'Nam', '',             'Tiến sĩ', 'ATTT', 'Đang công tác'],
    ['GV005', 'TS. Lê Hải Đăng',         'danglh@huit.edu.vn', '0903123005', '1984-06-25', 'Nam', '',             'Tiến sĩ', 'MMT',  'Đang công tác'],
    ['GV006', 'PGS.TS. Võ Quốc Phong',   'phongvq@huit.edu.vn','0903123006', '1976-09-30', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV007', 'TS. Đỗ Anh Minh',         'minhda@huit.edu.vn', '0903123007', '1985-03-14', 'Nam', '',             'Tiến sĩ', 'KHDL', 'Đang công tác'],

    // --- LÃNH ĐẠO KHOA & BỘ MÔN KHÁC ---
    ['GV008', 'PGS.TS. Trần Đình Nam',   'namtd@huit.edu.vn',  '0903123008', '1974-12-05', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'QTKD_DN', 'Đang công tác'],
    ['GV009', 'GS.TS. Lê Thị Mai',       'mailt@huit.edu.vn',  '0903123009', '1970-07-22', 'Nữ',  'Giáo sư',     'Tiến sĩ', 'CBTP',    'Đang công tác'],
    ['GV010', 'PGS.TS. Vũ Trọng Hưng',   'hungvt@huit.edu.vn', '0903123010', '1973-05-19', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KTD',     'Đang công tác'],
    ['GV011', 'TS. Bùi Thu Hà',          'habth@huit.edu.vn',  '0903123011', '1983-10-10', 'Nữ',  '',             'Tiến sĩ', 'QTKD_DN', 'Đang công tác'],
    ['GV012', 'TS. Nguyễn Văn An',       'annv@huit.edu.vn',   '0903123012', '1981-01-28', 'Nam', '',             'Tiến sĩ', 'MKT',     'Đang công tác'],
    ['GV013', 'PGS.TS. Đỗ Hoài Nam',     'namdh@huit.edu.vn',  '0903123013', '1977-03-08', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'CBTP',    'Đang công tác'],
    ['GV014', 'TS. Trương Tuyết Mai',    'maitt@huit.edu.vn',  '0903123014', '1986-09-17', 'Nữ',  '',             'Tiến sĩ', 'DBTP',    'Đang công tác'],
    ['GV015', 'TS. Vũ Anh Quân',         'quanva@huit.edu.vn', '0903123015', '1982-04-03', 'Nam', '',             'Tiến sĩ', 'KTD',     'Đang công tác'],
    ['GV016', 'TS. Hồ Vĩnh Thắng',       'thanghv@huit.edu.vn','0903123016', '1980-11-29', 'Nam', '',             'Tiến sĩ', 'DT',      'Đang công tác'],
    ['GV017', 'PGS.TS. Đặng Quốc Thịnh', 'thinhdq@huit.edu.vn','0903123017', '1972-08-16', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'CTM',     'Đang công tác'],
    ['GV018', 'TS. Phan Trọng Nghĩa',    'nghiapt@huit.edu.vn','0903123018', '1983-02-11', 'Nam', '',             'Tiến sĩ', 'CTM',     'Đang công tác'],
    ['GV019', 'TS. Chu Văn Quân',        'quancv@huit.edu.vn', '0903123019', '1981-12-24', 'Nam', '',             'Tiến sĩ', 'CDT',     'Đang công tác'],
    ['GV020', 'TS. Phạm Thị Bích Loan',  'loanptb@huit.edu.vn','0903123020', '1978-06-30', 'Nữ',  '',             'Tiến sĩ', 'TA_CN',   'Đang công tác'],

    // --- GIẢNG VIÊN GIẢNG DẠY CHUYÊN MÔN CNTT & CÁC KHOA ---
    ['GV021', 'ThS. Đặng Hải Giang',     'giangdh@huit.edu.vn','0903123021', '1988-05-14', 'Nam', '',             'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV022', 'ThS. Trần Thị Bích',      'bichtb@huit.edu.vn', '0903123022', '1989-09-02', 'Nữ',  '',             'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV023', 'TS. Lê Hoàng Cường',      'cuonglh@huit.edu.vn','0903123023', '1984-12-18', 'Nam', '',             'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV024', 'ThS. Ngô Đức Hải',        'haind@huit.edu.vn',  '0903123024', '1990-07-07', 'Nam', '',             'Thạc sĩ', 'ATTT', 'Đang công tác'],
    ['GV025', 'TS. Dương Thúy Hằng',     'hangdt@huit.edu.vn', '0903123025', '1983-04-26', 'Nữ',  '',             'Tiến sĩ', 'KHDL', 'Đang công tác'],
    ['GV026', 'ThS. Lý Thanh Lan',       'lanlt@huit.edu.vn',  '0903123026', '1991-02-14', 'Nữ',  '',             'Thạc sĩ', 'MMT',  'Đang công tác'],
    ['GV027', 'TS. Trịnh Văn Khang',     'khangtv@huit.edu.vn','0903123027', '1982-08-09', 'Nam', '',             'Tiến sĩ', 'KHMT', 'Đang công tác'],
    ['GV028', 'ThS. Mai Tuyết Nga',      'ngamt@huit.edu.vn',  '0903123028', '1987-11-21', 'Nữ',  '',             'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV029', 'TS. Võ Đình Phúc',        'phucvd@huit.edu.vn', '0903123029', '1980-03-31', 'Nam', '',             'Tiến sĩ', 'HTTT', 'Đang công tác'],
    ['GV030', 'ThS. Hoàng Thị Em',       'emht@huit.edu.vn',   '0903123030', '1992-01-19', 'Nữ',  '',             'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV031', 'TS. Đinh Hoàng Xuân',     'xuandh@huit.edu.vn', '0903123031', '1985-06-12', 'Nữ',  '',             'Tiến sĩ', 'KT',   'Đang công tác'],
    ['GV032', 'TS. Cao Thái Vinh',       'vinhct@huit.edu.vn', '0903123032', '1984-10-05', 'Nam', '',             'Tiến sĩ', 'TC',   'Đang công tác'],
    ['GV033', 'TS. Phan Văn Phúc',       'phucpv@huit.edu.vn', '0903123033', '1979-05-23', 'Nam', '',             'Tiến sĩ', 'LKTE', 'Đang công tác'],
    ['GV034', 'TS. Hoàng Yến Linh',      'linhhy@huit.edu.vn', '0903123034', '1986-07-15', 'Nữ',  '',             'Tiến sĩ', 'QTDL', 'Đang công tác'],
    ['GV035', 'TS. Bùi Mai Hoa',         'hoabm@huit.edu.vn',  '0903123035', '1981-09-09', 'Nữ',  '',             'Tiến sĩ', 'KHMT_MT','Đang công tác'],
    ['GV036', 'TS. Võ Duy Hùng',         'hungvd@huit.edu.vn', '0903123036', '1983-02-27', 'Nam', '',             'Tiến sĩ', 'TMDT_SO','Đang công tác'],
    ['GV037', 'PGS.TS. Mai Thế Vinh',    'vinhtm@huit.edu.vn', '0903123037', '1975-11-11', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'LOG_CCU','Đang công tác'],
    ['GV038', 'ThS. Nguyễn Hoàng Anh',   'anhnh@huit.edu.vn',  '0903123038', '1989-08-18', 'Nam', '',             'Thạc sĩ', 'CNPM', 'Đang công tác'],
    ['GV039', 'ThS. Lê Phương Thảo',     'thaolp@huit.edu.vn', '0903123039', '1990-12-03', 'Nữ',  '',             'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV040', 'TS. Vũ Đình Khôi',        'khoivd@huit.edu.vn', '0903123040', '1984-04-17', 'Nam', '',             'Tiến sĩ', 'ATTT', 'Đang công tác'],
    ['GV041', 'ThS. Huỳnh Quốc Bảo',     'baohq@huit.edu.vn',  '0903123041', '1991-03-25', 'Nam', '',             'Thạc sĩ', 'MMT',  'Đang công tác'],
    ['GV042', 'TS. Tôn Nữ Quỳnh Như',    'nhutnq@huit.edu.vn', '0903123042', '1987-10-30', 'Nữ',  '',             'Tiến sĩ', 'KHDL', 'Đang công tác'],
    ['GV043', 'ThS. Cù Huy Sơn',         'sonch@huit.edu.vn',  '0903123043', '1988-06-19', 'Nam', '',             'Thạc sĩ', 'KHMT', 'Đang công tác'],
    ['GV044', 'TS. Thạch Văn Tài',       'taitv@huit.edu.vn',  '0903123044', '1983-01-08', 'Nam', '',             'Tiến sĩ', 'CNPM', 'Đang công tác'],
    ['GV045', 'ThS. Uông Thị Uyên',      'uyenut@huit.edu.vn', '0903123045', '1992-09-14', 'Nữ',  '',             'Thạc sĩ', 'HTTT', 'Đang công tác'],
    ['GV046', 'TS. Phí Văn Việt',        'vietpv@huit.edu.vn', '0903123046', '1985-05-22', 'Nam', '',             'Tiến sĩ', 'ATTT', 'Đang công tác'],
    ['GV047', 'ThS. Mạc Thị Xuyến',      'xuyenmt@huit.edu.vn','0903123047', '1990-08-04', 'Nữ',  '',             'Thạc sĩ', 'CBTP', 'Đang công tác'],
    ['GV048', 'TS. La Quốc Ý',           'ylq@huit.edu.vn',    '0903123048', '1982-12-16', 'Nam', '',             'Tiến sĩ', 'KTD',  'Đang công tác'],
    ['GV049', 'ThS. Sầm Ngọc Anh',       'anhsn@huit.edu.vn',  '0903123049', '1993-04-11', 'Nữ',  '',             'Thạc sĩ', 'TA_CN','Đang công tác'],
    ['GV050', 'TS. Nông Văn Bắc',        'bacnv@huit.edu.vn',  '0903123050', '1981-07-29', 'Nam', '',             'Tiến sĩ', 'CTM',  'Đang công tác']
];

$giangVienHeaders = ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai'];
saveFiles('06_GiangVien_Mau', $giangVienHeaders, $giangVienList, $outDir);

// -------------------------------------------------------------
// 7. 07_SinhVien_Mau: 50 Sinh viên chính quy (Tên họ chân thật)
// -------------------------------------------------------------
$svRealNames = [
    ['Nguyễn Hoàng Long',  'Nam', '2003-05-12', '12DHCNTT01', 128, 3.45],
    ['Trần Minh Quân',     'Nam', '2003-08-20', '12DHCNTT01', 125, 3.20],
    ['Lê Phương Anh',      'Nữ',  '2003-11-15', '12DHCNTT01', 130, 3.65],
    ['Phạm Đức Duy',       'Nam', '2003-02-18', '12DHCNTT02', 122, 3.10],
    ['Hoàng Thùy Linh',    'Nữ',  '2003-06-25', '12DHCNTT02', 129, 3.52],
    ['Huỳnh Quốc Huy',     'Nam', '2003-09-30', '12DHKPM01',  132, 3.70],
    ['Võ Thị Kim Ngân',    'Nữ',  '2003-03-14', '12DHKPM01',  124, 3.15],
    ['Đặng Thành Nam',     'Nam', '2003-12-05', '12DHHTTT01', 126, 3.28],
    ['Bùi Thị Thanh Hằng', 'Nữ',  '2003-07-22', '12DHHTTT01', 131, 3.60],
    ['Đỗ Trọng Hiếu',      'Nam', '2003-05-19', '12DHATTT01', 127, 3.35],
    ['Hồ Diệu Linh',       'Nữ',  '2003-10-10', '12DHATTT01', 125, 3.22],
    ['Ngô Văn Tuấn',       'Nam', '2003-01-28', '12DHKHMT01', 135, 3.85],
    ['Dương Bảo Ngọc',     'Nữ',  '2003-03-08', '12DHKHMT01', 130, 3.58],
    ['Lý Gia Kiệt',        'Nam', '2003-09-17', '12DHQTKD01', 121, 3.05],
    ['Mai Thảo My',        'Nữ',  '2003-04-03', '12DHQTKD01', 128, 3.42],
    ['Trịnh Quốc Khánh',   'Nam', '2003-11-29', '12DHMKT01',  124, 3.18],
    ['Phan Thu Trang',     'Nữ',  '2003-08-16', '12DHMKT01',  129, 3.50],
    ['Chu Minh Trí',       'Nam', '2003-02-11', '12DHTP01',   126, 3.30],
    ['Lương Bích Phượng',  'Nữ',  '2003-12-24', '12DHTP01',   132, 3.68],
    ['Tạ Hữu Đạt',         'Nam', '2003-06-30', '12DHDDT01',  123, 3.12],
    ['Vũ Mỹ Duyên',        'Nữ',  '2003-05-14', '12DHDDT01',  127, 3.38],
    ['Cao Đình Phúc',      'Nam', '2003-09-02', '12DHCK01',   125, 3.25],
    ['Lâm Ngọc Bích',      'Nữ',  '2003-12-18', '12DHCK01',   130, 3.55],
    ['Đinh Tiến Dũng',     'Nam', '2003-07-07', '12DHNNA01',  128, 3.48],
    ['Quách Ánh Tuyết',    'Nữ',  '2003-04-26', '12DHNNA01',  134, 3.75],
    ['Ân Hoàng Phúc',      'Nam', '2003-02-14', '12DHKT01',   122, 3.10],
    ['Phùng Mai Chi',      'Nữ',  '2003-08-09', '12DHKT01',   129, 3.51],
    ['Diệp Văn Hùng',      'Nam', '2003-11-21', '12DHTC01',   126, 3.32],
    ['Trang Kiều Oanh',    'Nữ',  '2003-03-31', '12DHTC01',   131, 3.62],
    ['Lưu Quang Khải',     'Nam', '2003-01-19', '12DHLKT01',  125, 3.20],
    ['Kiều Thanh Tâm',     'Nữ',  '2003-06-12', '12DHLKT01',  133, 3.72],
    ['Nghiêm Bá Thông',    'Nam', '2003-10-05', '12DHCNSH01', 124, 3.15],
    ['Bạch Hoài An',       'Nữ',  '2003-05-23', '12DHCNSH01', 128, 3.44],
    ['Triệu Công Hậu',     'Nam', '2003-07-15', '12DHDL01',   123, 3.14],
    ['Doãn Cẩm Tú',        'Nữ',  '2003-09-09', '12DHDL01',   130, 3.56],
    ['Vi Nhật Minh',       'Nam', '2003-02-27', '12DHMT01',   126, 3.28],
    ['Khổng Ngọc Trâm',    'Nữ',  '2003-11-11', '12DHMT01',   127, 3.36],
    ['Thái Tấn Phát',      'Nam', '2003-08-18', '12DHTMDT01', 132, 3.66],
    ['Tôn Nữ Diễm My',     'Nữ',  '2003-12-03', '12DHTMDT01', 135, 3.82],
    ['Hứa Tấn Lộc',        'Nam', '2003-04-17', '12DHLOG01',  125, 3.22],
    ['La Thị Ngọc Giàu',   'Nữ',  '2003-03-25', '12DHLOG01',  129, 3.49],
    ['Nông Đức Mạnh',      'Nam', '2003-10-30', '12DHCNTT01', 127, 3.34],
    ['Lục Hải Yến',        'Nữ',  '2003-06-19', '12DHKPM01',  131, 3.63],
    ['Châu Gia Hưng',      'Nam', '2003-01-08', '12DHHTTT01', 124, 3.17],
    ['Mạc Quỳnh Giao',     'Nữ',  '2003-09-14', '12DHATTT01', 130, 3.54],
    ['Phí Trọng Nhân',     'Nam', '2003-05-22', '12DHKHMT01', 136, 3.88],
    ['Uông Bích Ngọc',     'Nữ',  '2003-08-04', '12DHQTKD01', 126, 3.30],
    ['Thạch Gia Bảo',      'Nam', '2003-12-16', '12DHTP01',   125, 3.21],
    ['Cù Hoàng Yến',       'Nữ',  '2003-04-11', '12DHDDT01',  128, 3.46],
    ['Sầm Văn Quyết',      'Nam', '2003-07-29', '12DHCK01',   123, 3.11]
];

$sinhVienList = [];
foreach ($svRealNames as $i => $sv) {
    $idx = $i + 1;
    $mssv = '200121' . str_pad($idx, 4, '0', STR_PAD_LEFT);
    $email = "{$mssv}@st.huit.edu.vn";
    $sdt = '0978' . str_pad($idx + 1000, 6, '0', STR_PAD_LEFT);

    $sinhVienList[] = [
        $mssv,
        $sv[0], // HoTen
        $email,
        $sdt,
        $sv[2], // NgaySinh
        $sv[1], // GioiTinh
        $sv[3], // MaLop
        '2021-2025',
        $sv[4], // SoTinChiTichLuy
        $sv[5], // DiemTichLuy
        'Đang học'
    ];
}

$sinhVienHeaders = ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'];
saveFiles('07_SinhVien_Mau', $sinhVienHeaders, $sinhVienList, $outDir);

// -------------------------------------------------------------
// 8. 08_TaiKhoan_TBM_TK_Mau: Tài khoản Trưởng khoa & Trưởng bộ môn
// ĐÚNG YÊU CẦU: Lấy thông tin từ Giảng viên có sẵn (MaCanBo), chỉ gán thêm vai trò!
// - Trưởng khoa: TK_{MaKhoa}_{MaCanBo} (VT05)
// - Trưởng bộ môn: TBM_{MaKhoa}_{MaBoMon}_{MaCanBo} (VT04)
// -------------------------------------------------------------
$taiKhoanTBM_TK_List = [
    // --- 1. LÃNH ĐẠO KHOA CNTT & CÁC BỘ MÔN TRỰC THUỘC ---
    ['GV001', 'TK_CNTT_GV001',          'PGS.TS. Nguyễn Văn Hùng', 'hungnv@huit.edu.vn',  '0903123001', 'VT05', 'CNTT', '',        '123456'],
    ['GV002', 'TBM_CNTT_CNPM_GV002',     'TS. Trần Anh Dũng',       'dungta@huit.edu.vn',  '0903123002', 'VT04', 'CNTT', 'CNPM',    '123456'],
    ['GV003', 'TBM_CNTT_HTTT_GV003',     'TS. Phạm Minh Tuấn',      'tuanpm@huit.edu.vn',  '0903123003', 'VT04', 'CNTT', 'HTTT',    '123456'],
    ['GV004', 'TBM_CNTT_ATTT_GV004',     'TS. Trịnh Văn Thành',     'thanhtv@huit.edu.vn', '0903123004', 'VT04', 'CNTT', 'ATTT',    '123456'],
    ['GV005', 'TBM_CNTT_MMT_GV005',      'TS. Lê Hải Đăng',         'danglh@huit.edu.vn',  '0903123005', 'VT04', 'CNTT', 'MMT',     '123456'],
    ['GV006', 'TBM_CNTT_KHMT_GV006',     'PGS.TS. Võ Quốc Phong',   'phongvq@huit.edu.vn', '0903123006', 'VT04', 'CNTT', 'KHMT',    '123456'],
    ['GV007', 'TBM_CNTT_KHDL_GV007',     'TS. Đỗ Anh Minh',         'minhda@huit.edu.vn',  '0903123007', 'VT04', 'CNTT', 'KHDL',    '123456'],

    // --- 2. LÃNH ĐẠO CÁC KHOA & BỘ MÔN KHÁC ---
    ['GV008', 'TK_QTKD_GV008',          'PGS.TS. Trần Đình Nam',   'namtd@huit.edu.vn',   '0903123008', 'VT05', 'QTKD', '',        '123456'],
    ['GV011', 'TBM_QTKD_QTKD_DN_GV011', 'TS. Bùi Thu Hà',          'habth@huit.edu.vn',   '0903123011', 'VT04', 'QTKD', 'QTKD_DN', '123456'],
    ['GV012', 'TBM_QTKD_MKT_GV012',     'TS. Nguyễn Văn An',       'annv@huit.edu.vn',    '0903123012', 'VT04', 'QTKD', 'MKT',     '123456'],

    ['GV009', 'TK_TP_GV009',            'GS.TS. Lê Thị Mai',       'mailt@huit.edu.vn',   '0903123009', 'VT05', 'TP',   '',        '123456'],
    ['GV013', 'TBM_TP_CBTP_GV013',       'PGS.TS. Đỗ Hoài Nam',     'namdh@huit.edu.vn',   '0903123013', 'VT04', 'TP',   'CBTP',    '123456'],
    ['GV014', 'TBM_TP_DBTP_GV014',       'TS. Trương Tuyết Mai',    'maitt@huit.edu.vn',   '0903123014', 'VT04', 'TP',   'DBTP',    '123456'],

    ['GV010', 'TK_DDT_GV010',           'PGS.TS. Vũ Trọng Hưng',   'hungvt@huit.edu.vn',  '0903123010', 'VT05', 'DDT',  '',        '123456'],
    ['GV015', 'TBM_DDT_KTD_GV015',       'TS. Vũ Anh Quân',         'quanva@huit.edu.vn',  '0903123015', 'VT04', 'DDT',  'KTD',     '123456'],
    ['GV016', 'TBM_DDT_DT_GV016',        'TS. Hồ Vĩnh Thắng',       'thanghv@huit.edu.vn', '0903123016', 'VT04', 'DDT',  'DT',      '123456'],

    ['GV017', 'TK_CK_GV017',            'PGS.TS. Đặng Quốc Thịnh', 'thinhdq@huit.edu.vn', '0903123017', 'VT05', 'CK',   '',        '123456'],
    ['GV018', 'TBM_CK_CTM_GV018',        'TS. Phan Trọng Nghĩa',    'nghiapt@huit.edu.vn', '0903123018', 'VT04', 'CK',   'CTM',     '123456'],
    ['GV019', 'TBM_CK_CDT_GV019',        'TS. Chu Văn Quân',        'quancv@huit.edu.vn',  '0903123019', 'VT04', 'CK',   'CDT',     '123456'],

    ['GV020', 'TK_NN_GV020',            'TS. Phạm Thị Bích Loan',  'loanptb@huit.edu.vn', '0903123020', 'VT05', 'NN',   '',        '123456']
];

$taiKhoanTBM_TK_Headers = ['MaCanBo', 'TenDangNhap', 'HoTen', 'Email', 'SoDienThoai', 'MaVaiTro', 'MaKhoa', 'MaBoMon', 'MatKhau'];
saveFiles('08_TaiKhoan_TBM_TK_Mau', $taiKhoanTBM_TK_Headers, $taiKhoanTBM_TK_List, $outDir);

echo "\n=======================================================\n";
echo ">>> TAT CA 8 FILE MAU EXCEL VA CSV DA DUOC TAO HOAN HAO <<<\n";
echo "=======================================================\n";
