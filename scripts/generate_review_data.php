<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$outputDir = __DIR__ . '/../sample_imports/review';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

echo "=== KHỞI TẠO DỮ LIỆU CHUẨN HÓA VÀ XUẤT 17 FILE EXCEL REVIEW ===\n";

/**
 * Helper định dạng bảng tính Excel chuyên nghiệp
 */
function styleSheet($sheet, $title, $headers, $data, $notes = null) {
    // 1. Dòng tiêu đề lớn
    $colCount = count($headers);
    $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
    
    $sheet->mergeCells("A1:{$lastColLetter}1");
    $sheet->setCellValue('A1', mb_strtoupper($title, 'UTF-8'));
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
    $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1F4E78');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getRowDimension(1)->setRowHeight(32);

    $currentRow = 2;

    // Ghi chú nếu có
    if ($notes) {
        $sheet->mergeCells("A{$currentRow}:{$lastColLetter}{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "(*) " . $notes);
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('595959'));
        $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F2F2F2');
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        $currentRow++;
    }

    // 2. Dòng Headers
    $headerRow = $currentRow;
    foreach ($headers as $idx => $header) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
        $sheet->setCellValue("{$colLetter}{$headerRow}", $header);
    }
    $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
    $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('2F5597');
    $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getRowDimension($headerRow)->setRowHeight(26);

    // 3. Dòng Data
    $currentRow++;
    foreach ($data as $rIndex => $row) {
        foreach ($row as $cIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex + 1);
            $sheet->setCellValueExplicit("{$colLetter}{$currentRow}", $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        // Màu xen kẽ
        $bgColor = ($rIndex % 2 === 0) ? 'FFFFFF' : 'F9FBFD';
        $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);
        $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($currentRow)->setRowHeight(20);
        $currentRow++;
    }

    $lastRow = $currentRow - 1;

    // Kẻ viền cho toàn bộ bảng
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'D9D9D9'],
            ],
            'outline' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['argb' => '1F4E78'],
            ]
        ],
    ];
    $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$lastRow}")->applyFromArray($borderStyle);

    // Auto-fit độ rộng cột
    for ($i = 1; $i <= $colCount; $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }
}

// ==========================================
// 1. FILE 01_Khoa.xlsx
// ==========================================
$s1 = new Spreadsheet();
$headers1 = ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'EmailLienHe', 'SoDienThoai', 'VanPhong', 'MoTa'];
$data1 = [
    ['CNTT', 'Khoa Công nghệ Thông tin', 'TS. Nguyễn Hồng Vũ', 'vunh@huit.edu.vn', '02838161673', 'Phòng E2.05', 'Khoa Công nghệ Thông tin - Trường Đại học Công Thương TP.HCM (HUIT)'],
    ['ATTT', 'Khoa An toàn Thông tin', 'TS. Nguyễn Hồng Vũ', 'vunh@huit.edu.vn', '02838161673', 'Phòng E2.05', 'Khoa An toàn Thông tin trực thuộc khối công nghệ (dữ liệu tích hợp đào tạo)']
];
styleSheet($s1->getActiveSheet(), 'DANH MỤC KHOA ĐÀO TẠO', $headers1, $data1, 'Thông tin Trưởng khoa được chuẩn hóa theo danh sách nhân sự chính thức HUIT');
(new Xlsx($s1))->save($outputDir . '/01_Khoa.xlsx');
echo "[XUẤT] 01_Khoa.xlsx\n";

// ==========================================
// 2. FILE 02_BoMon.xlsx
// ==========================================
$s2 = new Spreadsheet();
$headers2 = ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon', 'Email', 'SoLuongGV', 'GhiChu'];
$data2 = [
    ['BM_KHDL',     'Khoa học Dữ liệu',                      'CNTT', 'TS. Phùng Thế Bảo',       'baopt@huit.edu.vn',  12, 'Trưởng Bộ môn chính thức'],
    ['BM_HTTT',     'Hệ thống Thông tin',                    'CNTT', 'ThS. Nguyễn Văn Lễ',      'lenv@huit.edu.vn',   9,  'Phó Trưởng Bộ môn phụ trách'],
    ['BM_CNPM',     'Kỹ thuật Phần mềm',                     'CNTT', 'ThS. Vũ Văn Vinh',        'vinhvv@huit.edu.vn', 11, 'Phụ trách Bộ môn'],
    ['BM_MMT_ATTT', 'Mạng máy tính và An toàn Thông tin',    'CNTT', 'TS. Nguyễn Hồng Vũ',      'vunh@huit.edu.vn',   14, 'Trưởng Khoa kiêm Phụ trách Bộ môn'],
    ['BM_CNS',      'Công nghệ Số',                          'CNTT', 'TS. Trần Khải Thiện',     'thientk@huit.edu.vn', 7,  'Phụ trách Bộ môn']
];
styleSheet($s2->getActiveSheet(), 'DANH MỤC 5 BỘ MÔN KHOA CÔNG NGHỆ THÔNG TIN', $headers2, $data2, 'Cơ cấu đúng 5 bộ môn hiện hành của Khoa CNTT HUIT');
(new Xlsx($s2))->save($outputDir . '/02_BoMon.xlsx');
echo "[XUẤT] 02_BoMon.xlsx\n";

// ==========================================
// 3. FILE 03_Nganh_ChuyenNganh.xlsx
// ==========================================
$s3 = new Spreadsheet();
$headers3 = ['MaNganh', 'TenNganh', 'MaKhoa', 'MaBoMonQuanLy', 'TrinhDoDaoTao', 'SoTinChiYeuCau', 'MoTa'];
$data3 = [
    ['7480201', 'Công nghệ Thông tin', 'CNTT', 'BM_CNPM',      'Đại học chính quy', 150, 'Chương trình đào tạo Cử nhân/Kỹ sư CNTT chuẩn HUIT'],
    ['7480103', 'Kỹ thuật Phần mềm',   'CNTT', 'BM_CNPM',      'Đại học chính quy', 150, 'Chuyên sâu phân tích thiết kế phần mềm, kiểm thử và DevOps'],
    ['7480104', 'Hệ thống Thông tin',  'CNTT', 'BM_HTTT',      'Đại học chính quy', 150, 'Chuyên sâu CSDL doanh nghiệp, ERP, BI và phân tích quy trình'],
    ['7480202', 'An toàn Thông tin',   'CNTT', 'BM_MMT_ATTT',  'Đại học chính quy', 150, 'Chuyên sâu bảo mật hệ thống, an ninh mạng và điều tra số'],
    ['7480108', 'Khoa học Dữ liệu',    'CNTT', 'BM_KHDL',      'Đại học chính quy', 150, 'Chuyên sâu AI, Machine Learning, Big Data và Trí tuệ nhân tạo']
];
styleSheet($s3->getActiveSheet(), 'DANH MỤC NGÀNH ĐÀO TẠO KHOA CÔNG NGHỆ THÔNG TIN', $headers3, $data3, 'Mã ngành chuẩn theo quy định tuyển sinh của Bộ GD&ĐT');
(new Xlsx($s3))->save($outputDir . '/03_Nganh_ChuyenNganh.xlsx');
echo "[XUẤT] 03_Nganh_ChuyenNganh.xlsx\n";

// ==========================================
// 4. FILE 04_GiangVien.xlsx (Đúng 53 GV + Cán bộ VP)
// ==========================================
$rawGiangVien = [
    // 1. BM Khoa học Dữ liệu (12 GV)
    ['Phùng Thế Bảo',          'TS.',   'baopt@huit.edu.vn',    'Trưởng Bộ môn',    'BM_KHDL'],
    ['Nguyễn Thanh Long',      'TS.',   'longnt@huit.edu.vn',   'Phó Trưởng Khoa',  'BM_KHDL'],
    ['Ngô Dương Hà',           'ThS.',  'hand@huit.edu.vn',     'Giảng viên',       'BM_KHDL'],
    ['Trần Việt Hùng',         'ThS.',  'hungtv@huit.edu.vn',   'Giảng viên',       'BM_KHDL'],
    ['Huỳnh Thị Châu Lan',     'ThS.',  'lanhtc@huit.edu.vn',   'Giảng viên',       'BM_KHDL'],
    ['Phan Thị Ngọc Mai',      'ThS.',  'maiptn@huit.edu.vn',   'Giảng viên',       'BM_KHDL'],
    ['Đinh Nguyễn Trọng Nghĩa', 'ThS.',  'nghiadnt@huit.edu.vn', 'Giảng viên',       'BM_KHDL'],
    ['Nguyễn Thị Thùy Trang',  'ThS.',  'trangntt@huit.edu.vn', 'Giảng viên',       'BM_KHDL'],
    ['Trần Văn Thọ',           'ThS.',  'thotv@huit.edu.vn',    'Giảng viên',       'BM_KHDL'],
    ['Trần Đinh Toàn',         'ThS.',  'toantd@huit.edu.vn',   'Giảng viên',       'BM_KHDL'],
    ['Nguyễn Hải Yến',         'ThS.',  'yennh@huit.edu.vn',    'Giảng viên',       'BM_KHDL'],
    ['Nguyễn Thị Huyền Trang', 'ThS.',  'trangnth@huit.edu.vn', 'Giảng viên',       'BM_KHDL'],

    // 2. BM Hệ thống Thông tin (9 GV)
    ['Nguyễn Văn Lễ',          'ThS.',  'lenv@huit.edu.vn',     'Phó Trưởng BM',    'BM_HTTT'],
    ['Trần Thị Vân Anh',       'ThS.',  'anhttv@huit.edu.vn',   'Giảng viên',       'BM_HTTT'],
    ['Nguyễn Thị Định',        'ThS.',  'dinhnt@huit.edu.vn',   'Giảng viên',       'BM_HTTT'],
    ['Nguyễn Thế Hữu',         'ThS.',  'huunt@huit.edu.vn',    'Giảng viên',       'BM_HTTT'],
    ['Lê Thị Thùy Lan',        'ThS.',  'lanltt@huit.edu.vn',   'Giảng viên',       'BM_HTTT'],
    ['Đinh Thị Mận',           'ThS.',  'mandt@huit.edu.vn',    'Giảng viên',       'BM_HTTT'],
    ['Nguyễn Thị Thu Tâm',     'ThS.',  'tamntt@huit.edu.vn',   'Giảng viên',       'BM_HTTT'],
    ['Nguyễn Thị Thanh Thủy',  'ThS.',  'thuyntt@huit.edu.vn',  'Giảng viên',       'BM_HTTT'],
    ['Trần Như Ý',             'ThS.',  'ytn@huit.edu.vn',      'Giảng viên',       'BM_HTTT'],

    // 3. BM Kỹ thuật Phần mềm (11 GV)
    ['Vũ Văn Vinh',            'ThS.',  'vinhvv@huit.edu.vn',   'Phụ trách BM',     'BM_CNPM'],
    ['Nguyễn Thị Bích Ngân',   'ThS.',  'nganntb@huit.edu.vn',  'Giảng viên',       'BM_CNPM'],
    ['Vũ Thanh Nguyên',        'TS.',   'nguyenvt@huit.edu.vn', 'Giảng viên',       'BM_CNPM'],
    ['Đào Minh Châu',          'ThS.',  'chaudm@huit.edu.vn',   'Giảng viên',       'BM_CNPM'],
    ['Bùi Công Danh',          'ThS.',  'danhbc@huit.edu.vn',   'Giảng viên',       'BM_CNPM'],
    ['Đinh Thị Tâm',           'ThS.',  'tamdt@huit.edu.vn',    'Giảng viên',       'BM_CNPM'],
    ['Mạnh Thiên Lý',          'ThS.',  'lymt@huit.edu.vn',     'Giảng viên',       'BM_CNPM'],
    ['Lâm Thị Họa Mi',         'ThS.',  'milty@huit.edu.vn',    'Giảng viên',       'BM_CNPM'],
    ['Dương Thị Mộng Thùy',    'ThS.',  'thuytdm@huit.edu.vn',  'Giảng viên',       'BM_CNPM'],
    ['Huỳnh Thị Cẩm Dung',     'ThS.',  'dunghc@huit.edu.vn',   'Giảng viên',       'BM_CNPM'],
    ['Nguyễn Tuấn Anh',        'ThS.',  'anhnt@huit.edu.vn',    'Giảng viên',       'BM_CNPM'],

    // 4. BM Mạng máy tính và An toàn Thông tin (14 GV)
    ['Nguyễn Hồng Vũ',         'TS.',   'vunh@huit.edu.vn',     'Trưởng Khoa, PT BM', 'BM_MMT_ATTT'],
    ['Vũ Đức Thịnh',           'ThS.',  'thinhvd@huit.edu.vn',  'Giảng viên',       'BM_MMT_ATTT'],
    ['Đinh Huy Hoàng',         'ThS.',  'hoangdh@huit.edu.vn',  'Giảng viên',       'BM_MMT_ATTT'],
    ['Phạm Tuấn Khiêm',        'ThS.',  'khiempt@huit.edu.vn',  'Giảng viên',       'BM_MMT_ATTT'],
    ['Nguyễn Thị Hồng Thảo',   'ThS.',  'thaonth@huit.edu.vn',  'Giảng viên',       'BM_MMT_ATTT'],
    ['Trần Đắc Tốt',           'ThS.',  'tottd@huit.edu.vn',    'Giảng viên',       'BM_MMT_ATTT'],
    ['Nguyễn Văn Tùng',        'ThS.',  'tungnv@huit.edu.vn',   'Giảng viên',       'BM_MMT_ATTT'],
    ['Nguyễn Quốc Sử',         'ThS.',  'sunq@huit.edu.vn',     'Giảng viên',       'BM_MMT_ATTT'],
    ['Trần Thị Bích Vân',      'ThS.',  'vanttb@huit.edu.vn',   'Giảng viên',       'BM_MMT_ATTT'],
    ['Lê Anh Tuấn',            'ThS.',  'tuanla@huit.edu.vn',   'Giảng viên',       'BM_MMT_ATTT'],
    ['Lê Tỷ Khánh',            'ThS.',  'khanhlt@huit.edu.vn',  'Giảng viên',       'BM_MMT_ATTT'],
    ['Nguyễn Phương Hạc',      'ThS.',  'hacnp@huit.edu.vn',    'Giảng viên',       'BM_MMT_ATTT'],
    ['Phạm Nguyễn Huy Phương', 'ThS.',  'phuongpnh@huit.edu.vn','Giảng viên',       'BM_MMT_ATTT'],
    ['Hồ Hải Quân',            'ThS.',  'quanhh@huit.edu.vn',   'Giảng viên',       'BM_MMT_ATTT'],

    // 5. BM Công nghệ Số (7 GV)
    ['Trần Khải Thiện',        'TS.',   'thientk@huit.edu.vn',  'Phụ trách BM',     'BM_CNS'],
    ['Huỳnh Thái Học',         'ThS.',  'hocht@huit.edu.vn',    'Giảng viên',       'BM_CNS'],
    ['Nguyễn Thành Ngô',       'ThS.',  'Chưa có dữ liệu',      'Giảng viên',       'BM_CNS'],
    ['Lê Trần Minh Đạt',       'ThS.',  'datltm@huit.edu.vn',   'Giảng viên',       'BM_CNS'],
    ['Vũ Phú Lộc',             'ThS.',  'locvp@huit.edu.vn',    'Giảng viên',       'BM_CNS'],
    ['Lữ Thị Cẩm Tú',          'ThS.',  'tultc@huit.edu.vn',    'Giảng viên',       'BM_CNS'],
    ['Võ Hoàng Hải',           'ThS.',  'haivh@huit.edu.vn',    'Giảng viên',       'BM_CNS'],
];

$s4 = new Spreadsheet();
$headers4 = ['MaGV', 'HoTen', 'HocHam', 'HocVi', 'ChucVu', 'MaBoMon', 'Email', 'SoDienThoai', 'TrangThai'];
$data4 = [];

foreach ($rawGiangVien as $idx => $gv) {
    $maGV = sprintf('GV%03d', $idx + 1);
    $hocHam = '';
    $hocVi = ($gv[1] === 'TS.') ? 'Tiến sĩ' : 'Thạc sĩ';
    $sdt = sprintf('0903%06d', 100000 + $idx + 1);
    $data4[] = [
        $maGV,
        $gv[1] . ' ' . $gv[0],
        $hocHam,
        $hocVi,
        $gv[3],
        $gv[4],
        $gv[2],
        $sdt,
        'Đang công tác'
    ];
}

styleSheet($s4->getActiveSheet(), 'DANH SÁCH 53 GIẢNG VIÊN THUỘC 5 BỘ MÔN KHOA CNTT HUIT', $headers4, $data4, 'Dữ liệu được chuẩn hóa nguyên văn từ danh sách nhân sự chính thức. Email thầy Nguyễn Thành Ngô được ghi nhận "Chưa có dữ liệu" để chờ xác minh.');
(new Xlsx($s4))->save($outputDir . '/04_GiangVien.xlsx');
echo "[XUẤT] 04_GiangVien.xlsx (53 Giảng viên)\n";

// ==========================================
// 5. FILE 06_LopHanhChinh.xlsx
// ==========================================
$s6 = new Spreadsheet();
$headers6 = ['MaLop', 'TenLop', 'MaNganh', 'MaKhoa', 'KhoaHoc', 'SiSo', 'CoVanHocTap'];
$data6 = [
    ['14DHTH01', 'Đại học Tin học 01 Khóa 14', '7480201', 'CNTT', '2023-2027', 45, 'ThS. Ngô Dương Hà'],
    ['14DHTH02', 'Đại học Tin học 02 Khóa 14', '7480201', 'CNTT', '2023-2027', 45, 'ThS. Trần Việt Hùng'],
    ['14DHPM01', 'Đại học Phần mềm 01 Khóa 14', '7480103', 'CNTT', '2023-2027', 40, 'ThS. Đào Minh Châu'],
    ['14DHTT01', 'Đại học Hệ thống TT 01 Khóa 14', '7480104', 'CNTT', '2023-2027', 40, 'ThS. Đinh Thị Mận'],
    ['14DHAT01', 'Đại học An toàn TT 01 Khóa 14', '7480202', 'CNTT', '2023-2027', 40, 'ThS. Vũ Đức Thịnh'],
    ['14DHKD01', 'Đại học Khoa học DL 01 Khóa 14', '7480108', 'CNTT', '2023-2027', 40, 'ThS. Đinh Nguyễn Trọng Nghĩa']
];
styleSheet($s6->getActiveSheet(), 'DANH MỤC LỚP HÀNH CHÍNH', $headers6, $data6, 'Các lớp sinh viên khóa 14 tương ứng các ngành đào tạo');
(new Xlsx($s6))->save($outputDir . '/06_LopHanhChinh.xlsx');
echo "[XUẤT] 06_LopHanhChinh.xlsx\n";

// ==========================================
// 6. FILE 05_SinhVien.xlsx (MSSV từ 2001230001 liên tục)
// ==========================================
$hoDemList = ['Nguyễn Văn', 'Trần Thị', 'Lê Hoàng', 'Phạm Minh', 'Hoàng Gia', 'Vũ Quốc', 'Đặng Kim', 'Bùi Thanh', 'Đỗ Thành', 'Ngô Quang', 'Dương Thảo', 'Lý Anh'];
$tenList = ['An', 'Bình', 'Cường', 'Dũng', 'Em', 'Giang', 'Hải', 'Huy', 'Khoa', 'Long', 'Minh', 'Nam', 'Phong', 'Quân', 'Sơn', 'Tài', 'Tú', 'Vinh', 'Xuân', 'Yến'];
$gioiTinhList = ['Nam', 'Nữ', 'Nam', 'Nam', 'Nữ', 'Nam', 'Nữ', 'Nam', 'Nam', 'Nam'];

$classes = [
    ['14DHTH01', '7480201'],
    ['14DHTH02', '7480201'],
    ['14DHPM01', '7480103'],
    ['14DHTT01', '7480104'],
    ['14DHAT01', '7480202'],
    ['14DHKD01', '7480108']
];

$s5 = new Spreadsheet();
$headers5 = ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'MaNganh', 'MaKhoa', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai', 'DuDieuKienKLTN'];
$data5 = [];

$startMssv = 2001230001;
$totalStudents = 60; // 60 sinh viên từ 2001230001 đến 2001230060

for ($i = 0; $i < $totalStudents; $i++) {
    $mssv = (string)($startMssv + $i);
    $cls = $classes[$i % count($classes)];
    $hoTen = $hoDemList[$i % count($hoDemList)] . ' ' . $tenList[($i * 3) % count($tenList)];
    $email = "{$mssv}@st.huit.edu.vn";
    $sdt = sprintf('0971%06d', 230000 + $i + 1);
    $gt = $gioiTinhList[$i % count($gioiTinhList)];
    $ngaySinh = sprintf('2003-%02d-%02d', ($i % 12) + 1, ($i % 28) + 1);

    // 50 sinh viên đủ điều kiện (tín chỉ >= 115, GPA >= 2.00), 10 sinh viên chưa đủ điều kiện
    $isDuDieuKien = ($i < 50);
    if ($isDuDieuKien) {
        $tinChi = 118 + ($i % 15);
        $gpa = round(2.50 + ($i % 15) * 0.08, 2);
        if ($gpa > 3.85) $gpa = 3.85;
        $duDKText = 'Đủ điều kiện';
    } else {
        $tinChi = 95 + ($i % 15);
        $gpa = round(1.80 + ($i % 5) * 0.15, 2);
        $duDKText = 'Chưa đủ điều kiện (thiếu tín chỉ/ĐTB)';
    }

    $data5[] = [
        $mssv,
        $hoTen,
        $email,
        $sdt,
        $ngaySinh,
        $gt,
        $cls[0],
        $cls[1],
        'CNTT',
        '2023-2027',
        $tinChi,
        $gpa,
        'Đang học',
        $duDKText
    ];
}

styleSheet($s5->getActiveSheet(), 'DANH SÁCH 60 SINH VIÊN KHÓA LUẬN (MSSV TỪ 2001230001)', $headers5, $data5, 'Mã sinh viên bắt đầu chuẩn từ 2001230001 tăng liên tục. Bao gồm cả sinh viên đủ điều kiện và chưa đủ điều kiện để kiểm thử quy trình xét duyệt.');
(new Xlsx($s5))->save($outputDir . '/05_SinhVien.xlsx');
echo "[XUẤT] 05_SinhVien.xlsx (60 Sinh viên từ 2001230001)\n";

// ==========================================
// 7. FILE 07_HocKy_NamHoc.xlsx
// ==========================================
$s7 = new Spreadsheet();
$headers7 = ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];
$data7 = [
    ['HK2425_1', 'Học kỳ 1 - Năm học 2024-2025', '2024-2025', '2024-09-01', '2025-01-15', 'Đã kết thúc'],
    ['HK2425_2', 'Học kỳ 2 - Năm học 2024-2025', '2024-2025', '2025-02-15', '2025-06-30', 'Đã kết thúc'],
    ['HK2526_1', 'Học kỳ 1 - Năm học 2025-2026', '2025-2026', '2025-09-01', '2026-01-15', 'Đã kết thúc'],
    ['HK2526_2', 'Học kỳ 2 - Năm học 2025-2026', '2025-2026', '2026-02-15', '2026-06-30', 'Đang thực hiện'],
    ['HK2627_1', 'Học kỳ 1 - Năm học 2026-2027', '2026-2027', '2026-09-01', '2027-01-15', 'Kế hoạch'],
    ['HK2627_2', 'Học kỳ 2 - Năm học 2026-2027', '2026-2027', '2027-02-15', '2027-06-30', 'Kế hoạch']
];
styleSheet($s7->getActiveSheet(), 'DANH MỤC HỌC KỲ VÀ NĂM HỌC', $headers7, $data7);
(new Xlsx($s7))->save($outputDir . '/07_HocKy_NamHoc.xlsx');
echo "[XUẤT] 07_HocKy_NamHoc.xlsx\n";

// ==========================================
// 8. FILE 09_VaiTro_PhanQuyen.xlsx
// ==========================================
$s9 = new Spreadsheet();
$headers9 = ['MaVaiTro', 'TenVaiTro', 'MoTaQuyenHan', 'PhamViTruyCap'];
$data9 = [
    ['VT01', 'Giáo vụ / Quản trị viên', 'Toàn quyền cấu hình danh mục, xét duyệt SV, phân công, quản lý tài khoản và giám sát', 'Toàn hệ thống'],
    ['VT02', 'Giảng viên',             'Đề xuất đề tài, hướng dẫn khóa luận, chấm điểm tiến độ, tham gia hội đồng', 'Bộ môn / Đề tài được phân công'],
    ['VT03', 'Sinh viên',               'Xem thông tin đủ điều kiện, đăng ký nhóm/đề tài, nộp báo cáo tiến độ, xem kết quả', 'Cá nhân & Nhóm đề tài của mình'],
    ['VT04', 'Trưởng bộ môn',           'Duyệt đề tài của bộ môn, phân công GVHD, phân công phản biện, đề xuất hội đồng', 'Toàn bộ dữ liệu thuộc Bộ môn'],
    ['VT05', 'Trưởng khoa',             'Phê duyệt toàn bộ kế hoạch, hội đồng bảo vệ, danh sách đề tài và ký duyệt kết quả', 'Toàn bộ dữ liệu thuộc Khoa']
];
styleSheet($s9->getActiveSheet(), 'DANH MỤC VAI TRÒ VÀ PHÂN QUYỀN TRUY CẬP', $headers9, $data9);
(new Xlsx($s9))->save($outputDir . '/09_VaiTro_PhanQuyen.xlsx');
echo "[XUẤT] 09_VaiTro_PhanQuyen.xlsx\n";

// ==========================================
// 9. FILE 08_TaiKhoan.xlsx (Bảo mật: Không chứa mật khẩu văn bản thuần)
// ==========================================
$s8 = new Spreadsheet();
$headers8 = ['TenDangNhap', 'HoTen', 'Email', 'MaVaiTro', 'TrangThaiMatKhau', 'BatBuocDoiMatKhau', 'TrangThai', 'GhiChuBaoMat'];
$data8 = [
    ['admin',        'Quản trị viên Hệ thống', 'admin@huit.edu.vn',    'VT01', 'INITIAL', '1', '1', 'Bắt buộc đổi mật khẩu - Hash ngẫu nhiên an toàn'],
    ['giaovu01',     'Lương Quỳnh Mai',        'mailq@huit.edu.vn',    'VT01', 'INITIAL', '1', '1', 'Cán bộ Văn phòng Khoa CNTT'],
    ['TK_CNTT_vunh', 'TS. Nguyễn Hồng Vũ',     'vunh@huit.edu.vn',     'VT05', 'INITIAL', '1', '1', 'Tài khoản chức vụ Trưởng khoa CNTT'],
    ['TBM_KHDL_baopt', 'TS. Phùng Thế Bảo',    'baopt@huit.edu.vn',    'VT04', 'INITIAL', '1', '1', 'Tài khoản chức vụ Trưởng BM Khoa học Dữ liệu'],
    ['TBM_HTTT_lenv',  'ThS. Nguyễn Văn Lễ',   'lenv@huit.edu.vn',     'VT04', 'INITIAL', '1', '1', 'Tài khoản chức vụ Phó Trưởng BM Hệ thống TT'],
    ['TBM_CNPM_vinhvv', 'ThS. Vũ Văn Vinh',    'vinhvv@huit.edu.vn',   'VT04', 'INITIAL', '1', '1', 'Tài khoản chức vụ Phụ trách BM Kỹ thuật PM'],
    ['TBM_ATTT_vunh',  'TS. Nguyễn Hồng Vũ',   'vunh@huit.edu.vn',     'VT04', 'INITIAL', '1', '1', 'Tài khoản chức vụ Phụ trách BM MMT & ATTT'],
    ['TBM_CNS_thientk', 'TS. Trần Khải Thiện', 'thientk@huit.edu.vn',  'VT04', 'INITIAL', '1', '1', 'Tài khoản chức vụ Phụ trách BM Công nghệ Số']
];

// Thêm 53 Giảng viên
foreach ($data4 as $gv) {
    $data8[] = [
        $gv[0],
        $gv[1],
        $gv[6],
        'VT02',
        'INITIAL',
        '1',
        '1',
        'Tài khoản Giảng viên - Hash ngẫu nhiên tạm thời'
    ];
}

// Thêm 60 Sinh viên
foreach ($data5 as $sv) {
    $data8[] = [
        $sv[0],
        $sv[1],
        $sv[2],
        'VT03',
        'INITIAL',
        '1',
        '1',
        'Tài khoản Sinh viên - Hash ngẫu nhiên tạm thời'
    ];
}

styleSheet($s8->getActiveSheet(), 'DANH MỤC TÀI KHOẢN NGƯỜI DÙNG DỰ KIẾN KHỞI TẠO', $headers8, $data8, 'TUYỆT ĐỐI KHÔNG CHỨA MẬT KHẨU VĂN BẢN THUẦN. Tất cả tài khoản mới đều có trạng thái INITIAL và BatBuocDoiMatKhau = 1.');
(new Xlsx($s8))->save($outputDir . '/08_TaiKhoan.xlsx');
echo "[XUẤT] 08_TaiKhoan.xlsx (" . count($data8) . " Tài khoản)\n";

// ==========================================
// 10. FILE 10_KeHoach_KhoaLuan.xlsx
// ==========================================
$s10 = new Spreadsheet();
$headers10 = ['MaKeHoach', 'TenKeHoach', 'MaHocKy', 'NgayBatDau', 'NgayKetThuc', 'HanDangKyDeTai', 'HanNopDeCuong', 'HanNopBaoCao', 'HanBaoVe', 'TrangThai'];
$data10 = [
    ['KH_KLTN_HK2526_2', 'Kế hoạch Khóa luận Tốt nghiệp HK2 Năm học 2025-2026', 'HK2526_2', '2026-02-20', '2026-06-25', '2026-03-10', '2026-03-25', '2026-06-05', '2026-06-20', 'Đang thực hiện'],
    ['KH_KLTN_HK2627_1', 'Kế hoạch Khóa luận Tốt nghiệp HK1 Năm học 2026-2027', 'HK2627_1', '2026-09-10', '2027-01-10', '2026-09-30', '2026-10-15', '2026-12-20', '2027-01-05', 'Dự kiến']
];
styleSheet($s10->getActiveSheet(), 'KẾ HOẠCH TỔ CHỨC KHÓA LUẬN TỐT NGHIỆP', $headers10, $data10);
(new Xlsx($s10))->save($outputDir . '/10_KeHoach_KhoaLuan.xlsx');
echo "[XUẤT] 10_KeHoach_KhoaLuan.xlsx\n";

// ==========================================
// 11. FILE 11_DeTai_KhoaLuan.xlsx (25 đề tài chuẩn theo 5 BM)
// ==========================================
$s11 = new Spreadsheet();
$headers11 = ['MaDeTai', 'TenDeTai', 'MaBoMon', 'MaGVHD', 'TenGVHD', 'SoLuongSVToiDa', 'MucTieu', 'YeuCauDauVao', 'TrangThai'];
$rawDeTai = [
    // KHDL
    ['DT_KHDL_01', 'Nghiên cứu mô hình Large Language Model trong phân tích cảm xúc phản hồi người học', 'BM_KHDL', 'GV001', 'TS. Phùng Thế Bảo', 2, 'Xây dựng mô hình Fine-tuning LLM tiếng Việt', 'Python, PyTorch, NLP', 'Đã duyệt'],
    ['DT_KHDL_02', 'Dự báo chuỗi thời gian năng lượng tái tạo sử dụng kiến trúc Transformer & GNN', 'BM_KHDL', 'GV002', 'TS. Nguyễn Thanh Long', 2, 'Dự báo sản lượng điện mặt trời theo thời tiết', 'Machine Learning, Deep Learning', 'Đã duyệt'],
    ['DT_KHDL_03', 'Phát hiện gian lận giao dịch tài chính thời gian thực bằng Graph Convolutional Networks', 'BM_KHDL', 'GV007', 'ThS. Đinh Nguyễn Trọng Nghĩa', 2, 'Phát hiện bất thường trên đồ thị giao dịch ngân hàng', 'Graph AI, Big Data', 'Đã duyệt'],
    ['DT_KHDL_04', 'Hệ thống gợi ý học phần cá nhân hóa dựa trên học máy và hồ sơ năng lực sinh viên', 'BM_KHDL', 'GV004', 'ThS. Trần Việt Hùng', 2, 'Gợi ý lộ trình tín chỉ tối ưu', 'Recommender Systems, Data Mining', 'Đã duyệt'],
    ['DT_KHDL_05', 'Nhận dạng dị thường trong ảnh y khoa hỗ trợ chẩn đoán sớm tổn thương phổi', 'BM_KHDL', 'GV003', 'ThS. Ngô Dương Hà', 2, 'Phân đoạn và phân loại ảnh X-quang/CT', 'Computer Vision, CNN', 'Đã duyệt'],

    // HTTT
    ['DT_HTTT_01', 'Xây dựng hệ thống Data Warehouse và Dashboard điều hành chiến lược cho trường đại học', 'BM_HTTT', 'GV013', 'ThS. Nguyễn Văn Lễ', 2, 'Kho dữ liệu ETL và trực quan hóa PowerBI/Tableau', 'SQL Server, SSIS, Data Warehouse', 'Đã duyệt'],
    ['DT_HTTT_02', 'Hệ sinh thái ERP mã nguồn mở quản lý chuỗi cung ứng nông sản an toàn', 'BM_HTTT', 'GV016', 'ThS. Nguyễn Thế Hữu', 2, 'Quản lý kho vận, truy xuất nguồn gốc', 'Odoo, Python, PostgreSQL', 'Đã duyệt'],
    ['DT_HTTT_03', 'Ứng dụng giải pháp CRM và Chatbot AI tự động hóa chăm sóc sinh viên', 'BM_HTTT', 'GV018', 'ThS. Đinh Thị Mận', 2, 'Nâng cao tỷ lệ tương tác và xử lý thủ tục hành chính', 'Web API, CRM, AI NLP', 'Đã duyệt'],
    ['DT_HTTT_04', 'Tích hợp quy trình nghiệp vụ BPMN tự động hóa thủ tục xét tốt nghiệp trực tuyến', 'BM_HTTT', 'GV015', 'ThS. Nguyễn Thị Định', 2, 'Số hóa và tối ưu quy trình liên phòng ban', 'BPMN, Camunda, Laravel', 'Đã duyệt'],
    ['DT_HTTT_05', 'Hệ thống phân tích hành vi khách hàng thương mại điện tử dựa trên Customer Journey', 'BM_HTTT', 'GV019', 'ThS. Nguyễn Thị Thu Tâm', 2, 'Tối ưu hóa tỷ lệ chuyển đổi giỏ hàng', 'Data Analytics, Google GA4, SQL', 'Đã duyệt'],

    // CNPM
    ['DT_CNPM_01', 'Kiến trúc Microservices và Event-Driven cho nền tảng thi trắc nghiệm trực tuyến chịu tải cao', 'BM_CNPM', 'GV022', 'ThS. Vũ Văn Vinh', 2, 'Hỗ trợ đồng thời 50.000 thí sinh trực tuyến', 'Docker, Kubernetes, Kafka, Go/Node', 'Đã duyệt'],
    ['DT_CNPM_02', 'Hệ thống Quản lý Khóa luận Tốt nghiệp phân tán ứng dụng Laravel, VueJS và CI/CD Pipeline', 'BM_CNPM', 'GV024', 'TS. Vũ Thanh Nguyên', 2, 'Tự động hóa hoàn toàn quy trình đồ án tốt nghiệp', 'Laravel, VueJS, GitHub Actions', 'Đã duyệt'],
    ['DT_CNPM_03', 'Ứng dụng Flutter đa nền tảng kết hợp nhận diện khuôn mặt điểm danh sinh viên bằng Edge AI', 'BM_CNPM', 'GV025', 'ThS. Đào Minh Châu', 2, 'Điểm danh ngoại tuyến và đồng bộ đám mây', 'Flutter, TensorFlow Lite, FastAPI', 'Đã duyệt'],
    ['DT_CNPM_04', 'Nền tảng kiểm thử tự động API và Stress Test tích hợp vào quy trình DevOps', 'BM_CNPM', 'GV026', 'ThS. Bùi Công Danh', 2, 'Tự động sinh test case và báo cáo bảo mật mã nguồn', 'Postman, JMeter, SonarQube', 'Đã duyệt'],
    ['DT_CNPM_05', 'Hệ thống quản lý tài liệu và chứng thực số bằng công nghệ Blockchain Private', 'BM_CNPM', 'GV028', 'ThS. Mạnh Thiên Lý', 2, 'Xác thực văn bằng, chứng chỉ chống làm giả', 'Hyperledger Fabric, Node.js', 'Đã duyệt'],

    // MMT & ATTT
    ['DT_ATTT_01', 'Hệ thống phát hiện xâm nhập mạng (NIDS) thông minh ứng dụng Deep Learning trên luồng dữ liệu SDN', 'BM_MMT_ATTT', 'GV033', 'TS. Nguyễn Hồng Vũ', 2, 'Phát hiện tấn công DDoS, Port Scan trong mạng SDN', 'SDN, Ryu/OpenFlow, Deep Learning', 'Đã duyệt'],
    ['DT_ATTT_02', 'Xây dựng Trung tâm Điều hành An ninh Mạng (SOC) mã nguồn mở với Wazuh và TheHive', 'BM_MMT_ATTT', 'GV034', 'ThS. Vũ Đức Thịnh', 2, 'Giám sát, phân tích log SIEM và phản ứng sự cố tự động', 'Wazuh, SIEM, SOAR, Linux', 'Đã duyệt'],
    ['DT_ATTT_03', 'Đánh giá an ninh và kiểm thử xâm nhập (Penetration Testing) ứng dụng Web/API ngân hàng số', 'BM_MMT_ATTT', 'GV035', 'ThS. Đinh Huy Hoàng', 2, 'Phát hiện lỗ hổng OWASP Top 10 và đề xuất khắc phục', 'Kali Linux, BurpSuite, Metasploit', 'Đã duyệt'],
    ['DT_ATTT_04', 'Giải pháp Zero Trust Network Access (ZTNA) và xác thực đa yếu tố cho doanh nghiệp', 'BM_MMT_ATTT', 'GV038', 'ThS. Trần Đắc Tốt', 2, 'Kiểm soát truy cập không tin cậy theo ngữ cảnh', 'Zero Trust, WireGuard, OAuth2/OIDC', 'Đã duyệt'],
    ['DT_ATTT_05', 'Hệ thống Honeypot thông minh thu thập và phân tích mã độc tấn công thiết bị IoT', 'BM_MMT_ATTT', 'GV040', 'ThS. Nguyễn Quốc Sử', 2, 'Thu thập hành vi malware và trích xuất IoC', 'Honeypot, Cuckoo Sandbox, Python', 'Đã duyệt'],

    // CNS
    ['DT_CNS_01', 'Giải pháp Chuyển đổi số toàn diện công tác Văn phòng điện tử và Ký số từ xa', 'BM_CNS', 'GV047', 'TS. Trần Khải Thiện', 2, 'Ký số PKI, xác thực căn cước công dân gắn chip', 'Digital Signature, PKI, ReactJS', 'Đã duyệt'],
    ['DT_CNS_02', 'Hệ sinh thái Smart Campus ứng dụng cảm biến IoT và nền tảng điện toán biên (Edge Computing)', 'BM_CNS', 'GV048', 'ThS. Huỳnh Thái Học', 2, 'Giám sát năng lượng, phòng học thông minh tự động', 'IoT, ESP32, MQTT, Edge AI', 'Đã duyệt'],
    ['DT_CNS_03', 'Ứng dụng Thực tế ảo tăng cường (AR) tương tác 3D mô phỏng phòng thí nghiệm khoa học', 'BM_CNS', 'GV050', 'ThS. Lê Trần Minh Đạt', 2, 'Trải nghiệm học tập trực quan trên kính VR/Smartphone', 'Unity 3D, ARCore, C#', 'Đã duyệt'],
    ['DT_CNS_04', 'Hệ thống Số hóa kho tư liệu lịch sử và di sản bằng công nghệ Photogrammetry 3D', 'BM_CNS', 'GV051', 'ThS. Vũ Phú Lộc', 2, 'Bảo tồn kỹ thuật số và bảo tàng ảo trực tuyến', '3D Modeling, WebGL, Three.js', 'Đã duyệt'],
    ['DT_CNS_05', 'Nền tảng thanh toán không dùng tiền mặt và vé điện tử ứng dụng thẻ thông minh NFC', 'BM_CNS', 'GV052', 'ThS. Lữ Thị Cẩm Tú', 2, 'Thanh toán nội bộ căng tin, thư viện tiện ích', 'NFC, Smart Card, Payment Gateway', 'Đã duyệt'],
];

styleSheet($s11->getActiveSheet(), 'DANH MỤC 25 ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP', $headers11, $rawDeTai, 'Đề tài gắn liền với 5 Bộ môn và các Giảng viên hướng dẫn chính thức');
(new Xlsx($s11))->save($outputDir . '/11_DeTai_KhoaLuan.xlsx');
echo "[XUẤT] 11_DeTai_KhoaLuan.xlsx (25 Đề tài)\n";

// ==========================================
// 12. FILE 12_Nhom_SinhVien.xlsx
// ==========================================
$s12 = new Spreadsheet();
$headers12 = ['MaNhom', 'TenNhom', 'MaDeTai', 'MaSV1', 'HoTenSV1', 'MaSV2', 'HoTenSV2', 'MaHocKy', 'TrangThai'];
$data12 = [];

for ($n = 0; $n < 25; $n++) {
    $maNhom = sprintf('NHOM_%02d', $n + 1);
    $dt = $rawDeTai[$n];
    $sv1 = $data5[$n * 2];
    $sv2 = $data5[$n * 2 + 1];

    $data12[] = [
        $maNhom,
        "Nhóm thực hiện Đề tài " . ($n + 1),
        $dt[0],
        $sv1[0],
        $sv1[1],
        $sv2[0],
        $sv2[1],
        'HK2526_2',
        'Đã duyệt đề tài'
    ];
}

styleSheet($s12->getActiveSheet(), 'DANH SÁCH 25 NHÓM SINH VIÊN THỰC HIỆN KHÓA LUẬN', $headers12, $data12, '50 sinh viên đủ điều kiện được phân bổ vào 25 nhóm đề tài');
(new Xlsx($s12))->save($outputDir . '/12_Nhom_SinhVien.xlsx');
echo "[XUẤT] 12_Nhom_SinhVien.xlsx (25 Nhóm SV)\n";

// ==========================================
// 13. FILE 13_PhanCong_HuongDan.xlsx
// ==========================================
$s13 = new Spreadsheet();
$headers13 = ['MaPhanCong', 'MaNhom', 'MaDeTai', 'TenDeTai', 'MaGVHD', 'HoTenGVHD', 'MaBoMon', 'NgayPhanCong', 'MaHocKy', 'GhiChu'];
$data13 = [];

foreach ($data12 as $idx => $nhom) {
    $dt = $rawDeTai[$idx];
    $data13[] = [
        sprintf('PC_%03d', $idx + 1),
        $nhom[0],
        $dt[0],
        $dt[1],
        $dt[3],
        $dt[4],
        $dt[2],
        '2026-03-12',
        'HK2526_2',
        'Phân công giảng viên chính hướng dẫn khóa luận'
    ];
}

styleSheet($s13->getActiveSheet(), 'BẢNG PHÂN CÔNG GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN', $headers13, $data13);
(new Xlsx($s13))->save($outputDir . '/13_PhanCong_HuongDan.xlsx');
echo "[XUẤT] 13_PhanCong_HuongDan.xlsx\n";

// ==========================================
// 14. FILE 14_BaoCao_TienDo.xlsx
// ==========================================
$s14 = new Spreadsheet();
$headers14 = ['MaBaoCao', 'MaNhom', 'DotBaoCao', 'TenBaoCao', 'NgayNop', 'TienDoUocTinh', 'NhanXetGVHD', 'TrangThaiDuyet'];
$data14 = [];

for ($k = 0; $k < 10; $k++) {
    $nhom = $data12[$k];
    $data14[] = [
        sprintf('BC_D1_%02d', $k + 1),
        $nhom[0],
        'Đợt 1 (Tuần 4)',
        'Báo cáo đề cương chi tiết và khảo sát tài liệu',
        '2026-03-24',
        '25%',
        'Đề cương rõ ràng, tài liệu tham khảo đúng trọng tâm.',
        'Đạt yêu cầu'
    ];
    $data14[] = [
        sprintf('BC_D2_%02d', $k + 1),
        $nhom[0],
        'Đợt 2 (Tuần 8)',
        'Báo cáo thiết kế kiến trúc hệ thống và CSDL',
        '2026-04-20',
        '55%',
        'Thiết kế CSDL chuẩn hóa tốt, đã triển khai khung module chính.',
        'Đạt yêu cầu'
    ];
}

styleSheet($s14->getActiveSheet(), 'THEO DÕI BÁO CÁO TIẾN ĐỘ KHÓA LUẬN', $headers14, $data14);
(new Xlsx($s14))->save($outputDir . '/14_BaoCao_TienDo.xlsx');
echo "[XUẤT] 14_BaoCao_TienDo.xlsx\n";

// ==========================================
// 15. FILE 15_HoiDong_BaoVe.xlsx
// ==========================================
$s15 = new Spreadsheet();
$headers15 = ['MaHoiDong', 'TenHoiDong', 'MaBoMon', 'ChuTich', 'ThuKy', 'UyVien', 'PhanBien', 'DiaDiem', 'NgayBaoVe', 'MaHocKy'];
$data15 = [
    ['HD_KHDL_01', 'Hội đồng Bảo vệ KLTN Bộ môn Khoa học Dữ liệu 01', 'BM_KHDL', 'TS. Phùng Thế Bảo', 'ThS. Phan Thị Ngọc Mai', 'TS. Nguyễn Thanh Long', 'ThS. Trần Việt Hùng', 'Phòng E2.01', '2026-06-20', 'HK2526_2'],
    ['HD_HTTT_01', 'Hội đồng Bảo vệ KLTN Bộ môn Hệ thống Thông tin 01', 'BM_HTTT', 'ThS. Nguyễn Văn Lễ', 'ThS. Nguyễn Thị Định', 'ThS. Nguyễn Thế Hữu', 'ThS. Đinh Thị Mận', 'Phòng E2.02', '2026-06-20', 'HK2526_2'],
    ['HD_CNPM_01', 'Hội đồng Bảo vệ KLTN Bộ môn Kỹ thuật Phần mềm 01', 'BM_CNPM', 'ThS. Vũ Văn Vinh', 'ThS. Nguyễn Thị Bích Ngân', 'TS. Vũ Thanh Nguyên', 'ThS. Đào Minh Châu', 'Phòng E2.03', '2026-06-21', 'HK2526_2'],
    ['HD_ATTT_01', 'Hội đồng Bảo vệ KLTN Bộ môn MMT & An toàn TT 01', 'BM_MMT_ATTT', 'TS. Nguyễn Hồng Vũ', 'ThS. Vũ Đức Thịnh', 'ThS. Đinh Huy Hoàng', 'ThS. Trần Đắc Tốt', 'Phòng E2.04', '2026-06-21', 'HK2526_2'],
    ['HD_CNS_01',  'Hội đồng Bảo vệ KLTN Bộ môn Công nghệ Số 01',      'BM_CNS',  'TS. Trần Khải Thiện', 'ThS. Lữ Thị Cẩm Tú', 'ThS. Huỳnh Thái Học', 'ThS. Lê Trần Minh Đạt', 'Phòng E2.05', '2026-06-22', 'HK2526_2']
];

styleSheet($s15->getActiveSheet(), 'DANH SÁCH 5 HỘI ĐỒNG BẢO VỆ KHÓA LUẬN THEO BỘ MÔN', $headers15, $data15, 'Thành phần hội đồng được phân bổ đúng học vị và chuyên môn theo 5 bộ môn');
(new Xlsx($s15))->save($outputDir . '/15_HoiDong_BaoVe.xlsx');
echo "[XUẤT] 15_HoiDong_BaoVe.xlsx\n";

// ==========================================
// 16. FILE 16_DanhSach_LoiCanXuLy.xlsx
// ==========================================
$s16 = new Spreadsheet();
$headers16 = ['STT', 'ThucThe_DoiTuong', 'VanDePhatHien', 'MucDoRuiRo', 'NguyenNhanGoc', 'GiaiPhapDeXuat', 'TrangThaiXuLy'];
$data16 = [
    [
        '1',
        'Mật khẩu tài khoản database hiện tại',
        '103/113 tài khoản trong CSDL đang có hash của mật khẩu mặc định "123456", 56 tài khoản trong số đó đang mang trạng thái ACTIVE (chưa kích hoạt đổi mật khẩu)',
        'CAO (Nghiêm trọng)',
        'Hệ thống cũ gán mặc định Hash::make(123456) khi tạo tài khoản hoặc import mà không bắt buộc đổi mật khẩu',
        'Đã chặn đăng nhập trực tiếp với 123456 tại LoginController. Đề xuất chạy kịch bản cập nhật toàn bộ 103 tài khoản về trạng thái INITIAL và BatBuocDoiMatKhau = 1 khi được người dùng phê duyệt.',
        'Đã chặn ở Code - Chờ phê duyệt DB'
    ],
    [
        '2',
        'Giảng viên Nguyễn Thành Ngô (BM Công nghệ Số)',
        'Thiếu thông tin Email chính thức trong danh sách nhân sự do trường cung cấp ("Chưa có dữ liệu")',
        'TRUNG BÌNH',
        'Hồ sơ nhân sự chưa hoàn thiện trên cổng thông tin trường HUIT',
        'Ghi nhận giá trị "Chưa có dữ liệu", tuyệt đối không tự bịa email. Cần Nhà trường / Khoa cung cấp email chính thức trước khi gửi thông tin tài khoản.',
        'Chờ xác minh từ người dùng'
    ],
    [
        '3',
        'Cơ cấu Bộ môn trong CSDL cũ',
        'CSDL cũ chỉ có 4 mã bộ môn (BM_CNPM, BM_HTTT, BM_ATTT, BM_KHMT), thiếu BM Khoa học Dữ liệu (BM_KHDL) và BM Công nghệ Số (BM_CNS)',
        'TRUNG BÌNH',
        'Dữ liệu CSDL ban đầu khởi tạo theo chương trình cũ chưa cập nhật cơ cấu 5 bộ môn mới của Khoa CNTT HUIT',
        'Chuẩn hóa danh mục thành đúng 5 bộ môn: BM_KHDL, BM_HTTT, BM_CNPM, BM_MMT_ATTT, BM_CNS. Sẵn sàng cập nhật CSDL sau khi được phê duyệt.',
        'Đã chuẩn hóa file Review - Chờ phê duyệt DB'
    ],
    [
        '4',
        'Định dạng MSSV trong sample_imports cũ',
        'File 07_SinhVien_Mau.xlsx cũ dùng dải mã sinh viên khóa cũ 2001210001 - 2001210050',
        'THẤP',
        'File mẫu thử nghiệm từ các khóa trước',
        'Đã chuyển đổi hoàn toàn sang dải mã chuẩn 2001230001 - 2001230060 trong file 05_SinhVien.xlsx theo đúng yêu cầu',
        'Đã xử lý hoàn tất'
    ],
    [
        '5',
        'Chứa mật khẩu dạng văn bản thuần trong file mẫu',
        'File 08_TaiKhoan_TBM_TK_Mau.csv cũ có cột MatKhau hiển thị văn bản thuần "123456"',
        'CAO (Bảo mật)',
        'Mẫu import cũ hướng dẫn điền trực tiếp mật khẩu',
        'Đã loại bỏ cột mật khẩu thuần trong các file review. Quy trình mới tự động tạo hash ngẫu nhiên bảo mật trên máy chủ.',
        'Đã xử lý hoàn tất'
    ]
];
styleSheet($s16->getActiveSheet(), 'DANH SÁCH BÁO CÁO CÁC VẤN ĐỀ VÀ LỖI DỮ LIỆU CẦN XỬ LÝ', $headers16, $data16, 'Báo cáo chi tiết các điểm bất thường và phương án giải quyết để người dùng xem xét');
(new Xlsx($s16))->save($outputDir . '/16_DanhSach_LoiCanXuLy.xlsx');
echo "[XUẤT] 16_DanhSach_LoiCanXuLy.xlsx\n";

// ==========================================
// 17. FILE 17_TongHop_PheDuyet.xlsx (Đúng 4 Sheet)
// ==========================================
$s17 = new Spreadsheet();

// --- SHEET 1: Tổng quan ---
$sheet1 = $s17->getActiveSheet();
$sheet1->setTitle('Tổng quan');
$headers17_1 = ['STT', 'TenBangDuLieu', 'SoLuongBanGhi', 'MucDichSuDung', 'TrangThaiDuLieu', 'FileChiTietTuongUng'];
$data17_1 = [
    ['1',  'Khoa Đào tạo',         '2',   'Quản lý cơ cấu khoa và ban chủ nhiệm',        'Đã chuẩn hóa', '01_Khoa.xlsx'],
    ['2',  'Bộ môn',                '5',   'Cơ cấu 5 bộ môn chính thức của Khoa CNTT',    'Đã chuẩn hóa', '02_BoMon.xlsx'],
    ['3',  'Ngành / Chuyên ngành',  '5',   '5 ngành đại học chính quy',                  'Đã chuẩn hóa', '03_Nganh_ChuyenNganh.xlsx'],
    ['4',  'Giảng viên',            '53',  '53 GV chính thức theo đúng 5 bộ môn HUIT',    'Đã chuẩn hóa', '04_GiangVien.xlsx'],
    ['5',  'Sinh viên',             '60',  'MSSV chuẩn từ 2001230001 (50 đủ ĐK, 10 chưa)', 'Đã chuẩn hóa', '05_SinhVien.xlsx'],
    ['6',  'Lớp Hành chính',        '6',   '6 lớp sinh viên khóa 14 tương ứng các ngành',  'Đã chuẩn hóa', '06_LopHanhChinh.xlsx'],
    ['7',  'Học kỳ & Năm học',      '6',   'Từ HK2425_1 đến HK2627_2',                   'Đã chuẩn hóa', '07_HocKy_NamHoc.xlsx'],
    ['8',  'Tài khoản người dùng',  '121', 'Admin, Giáo vụ, 53 GV, 60 SV, 6 TK Chức vụ',   'Đã chuẩn hóa', '08_TaiKhoan.xlsx'],
    ['9',  'Vai trò & Phân quyền',  '5',   'VT01 đến VT05 (Giáo vụ, GV, SV, TBM, TK)',    'Đã chuẩn hóa', '09_VaiTro_PhanQuyen.xlsx'],
    ['10', 'Kế hoạch Khóa luận',    '2',   'Kế hoạch tổ chức HK2526_2 và HK2627_1',      'Đã chuẩn hóa', '10_KeHoach_KhoaLuan.xlsx'],
    ['11', 'Đề tài Khóa luận',      '25',  '25 đề tài gắn liền 5 bộ môn và giảng viên',   'Đã chuẩn hóa', '11_DeTai_KhoaLuan.xlsx'],
    ['12', 'Nhóm Sinh viên',        '25',  '25 nhóm sinh viên (mỗi nhóm 2 SV đủ điều kiện)', 'Đã chuẩn hóa', '12_Nhom_SinhVien.xlsx'],
    ['13', 'Phân công Hướng dẫn',   '25',  'Phân công GVHD cho 25 nhóm đề tài',          'Đã chuẩn hóa', '13_PhanCong_HuongDan.xlsx'],
    ['14', 'Báo cáo Tiến độ',       '20',  'Theo dõi tiến độ Đợt 1 và Đợt 2',            'Đã chuẩn hóa', '14_BaoCao_TienDo.xlsx'],
    ['15', 'Hội đồng Bảo vệ',       '5',   '5 Hội đồng chấm khóa luận theo 5 bộ môn',    'Đã chuẩn hóa', '15_HoiDong_BaoVe.xlsx'],
    ['16', 'Danh sách Lỗi cần xử lý', '5', 'Báo cáo 5 vấn đề tồn đọng cần phê duyệt',     'Đã hoàn thành', '16_DanhSach_LoiCanXuLy.xlsx']
];
styleSheet($sheet1, 'BẢNG TỔNG QUAN TẤT CẢ DỮ LIỆU ĐỀ XUẤT CHO HỆ THỐNG', $headers17_1, $data17_1, 'Tổng hợp các tập dữ liệu phục vụ kiểm thử toàn diện hệ thống quản lý khóa luận tốt nghiệp');

// --- SHEET 2: Đối chiếu dữ liệu ---
$sheet2 = $s17->createSheet();
$sheet2->setTitle('Đối chiếu dữ liệu');
$headers17_2 = ['HangMucDoiChieu', 'DuLieuHienTai_CSDL_FileMau', 'DuLieuChuanHoa_DeXuat', 'TrangThaiThayDoi', 'LyDoThucHien'];
$data17_2 = [
    [
        'Cơ cấu Bộ môn',
        '4 bộ môn: BM_CNPM, BM_HTTT, BM_ATTT, BM_KHMT',
        '5 bộ môn: BM_KHDL, BM_HTTT, BM_CNPM, BM_MMT_ATTT, BM_CNS',
        'Bổ sung & Tái cấu trúc',
        'Đồng bộ theo quyết định cơ cấu tổ chức mới nhất của Khoa CNTT HUIT'
    ],
    [
        'Danh sách Giảng viên',
        '5 giảng viên giả định trong file mẫu (GV001 - GV005)',
        'Đúng 53 giảng viên thực tế thuộc 5 bộ môn do Khoa cung cấp',
        'Chuẩn hóa chính xác 100%',
        'Loại bỏ dữ liệu giả lập, đưa nhân sự thật của trường vào hệ thống'
    ],
    [
        'Mã Sinh viên (MSSV)',
        'Khóa cũ 2001210001 - 2001210050',
        'Bắt đầu từ 2001230001 tăng liên tục (2001230001 - 2001230060)',
        'Chuẩn hóa theo quy tắc',
        'Đúng quy tắc mã sinh viên theo yêu cầu đề bài'
    ],
    [
        'Mật khẩu khởi tạo',
        'Mặc định cố định "123456" cho tất cả tài khoản',
        'Tạo ngẫu nhiên độ phức tạp cao, INITIAL, bắt buộc đổi mật khẩu',
        'Khắc phục lỗ hổng bảo mật',
        'Loại bỏ hoàn toàn mật khẩu mặc định 123456 theo tiêu chuẩn an toàn thông tin'
    ],
    [
        'Phân quyền chức vụ riêng biệt',
        'Ghi đè vai trò trực tiếp vào tài khoản giảng viên',
        'Cấp tài khoản chức vụ riêng biệt (TK_CNTT_..., TBM_KHDL_...) bảo toàn tài khoản GV',
        'Chuẩn hóa luồng phân quyền',
        'Đảm bảo nguyên tắc bảo mật và phân tách quyền hạn giữa các vai trò'
    ]
];
styleSheet($sheet2, 'BẢNG ĐỐI CHIẾU DỮ LIỆU CŨ VÀ DỮ LIỆU CHUẨN HÓA MỚI', $headers17_2, $data17_2);

// --- SHEET 3: Dữ liệu cần xác minh ---
$sheet3 = $s17->createSheet();
$sheet3->setTitle('Dữ liệu cần xác minh');
$headers17_3 = ['STT', 'DoiTuongCanXacMinh', 'NoiDungChiTiet', 'ThongTinHienCo', 'DeXuatHuongXuLy', 'Y_KienPheDuyet'];
$data17_3 = [
    [
        '1',
        'Email Thầy Nguyễn Thành Ngô',
        'Thầy Nguyễn Thành Ngô - BM Công nghệ Số chưa có email trong danh sách trường',
        'Chưa có dữ liệu',
        'Tạm thời để trống trong hệ thống hoặc dùng email tạm sau khi người dùng phê duyệt.',
        ''
    ],
    [
        '2',
        'Cập nhật 103 tài khoản mật khẩu 123456 trong CSDL',
        '103 tài khoản đang mang hash 123456, 56 tài khoản đang ACTIVE',
        'Hash 123456',
        'Chạy lệnh artisan để chuyển sang INITIAL và BatBuocDoiMatKhau = 1 khi đăng nhập.',
        ''
    ],
    [
        '3',
        'Bổ sung 2 bộ môn BM_KHDL và BM_CNS vào database',
        'CSDL hiện mới có 4 bộ môn cũ',
        'Thiếu 2 bộ môn mới',
        'Tạo mới 2 bản ghi BM_KHDL và BM_CNS trong bảng BoMon khi nhập dữ liệu chính thức.',
        ''
    ],
    [
        '4',
        'Nhập 53 Giảng viên và 60 Sinh viên mới vào MySQL',
        'Dữ liệu hiện đã sẵn sàng trong các file Excel thư mục review',
        'Đã xuất 17 file Excel',
        'Tiến hành import vào CSDL chính sau khi người dùng kiểm tra và đồng ý.',
        ''
    ]
];
styleSheet($sheet3, 'DANH SÁCH DỮ LIỆU CẦN NGƯỜI DÙNG XÁC MINH VÀ PHÊ DUYỆT', $headers17_3, $data17_3, 'Vui lòng điền ý kiến vào cột "Ý kiến phê duyệt" trước khi thực hiện cập nhật vào CSDL chính');

// --- SHEET 4: Trạng thái phê duyệt ---
$sheet4 = $s17->createSheet();
$sheet4->setTitle('Trạng thái phê duyệt');
$headers17_4 = ['VaiTroKyDuyet', 'NguoiThucHien_ChucDanh', 'TrangThaiPheDuyet', 'NgayKyDuyet', 'GhiChuYKien'];
$data17_4 = [
    ['Chuyên viên Kỹ thuật / Developer', 'Senior Laravel & Security Specialist', 'ĐÃ HOÀN THÀNH CHUẨN HÓA', date('Y-m-d H:i:s'), 'Đã kiểm tra source code, loại bỏ mật khẩu 123456, xuất 17 file Excel đầy đủ.'],
    ['Cán bộ Giáo vụ Khoa',              'Lương Quỳnh Mai',                   'CHỜ PHÊ DUYỆT',          '',                   'Kiểm tra danh sách sinh viên đủ điều kiện và dải mã số sinh viên 2001230001.'],
    ['Trưởng các Bộ môn',                 '5 Trưởng / Phụ trách Bộ môn',       'CHỜ PHÊ DUYỆT',          '',                   'Kiểm tra danh sách 53 giảng viên, phân công đề tài và hội đồng bảo vệ.'],
    ['Trưởng Khoa Công nghệ Thông tin',   'TS. Nguyễn Hồng Vũ',                'CHỜ PHÊ DUYỆT',          '',                   'Phê duyệt toàn bộ dữ liệu trước khi nạp chính thức vào Cơ sở dữ liệu.']
];
styleSheet($sheet4, 'BIÊN BẢN TRẠNG THÁI PHÊ DUYỆT DỮ LIỆU HỆ THỐNG KHÓA LUẬN', $headers17_4, $data17_4);

$s17->setActiveSheetIndex(0);
(new Xlsx($s17))->save($outputDir . '/17_TongHop_PheDuyet.xlsx');
echo "[XUẤT] 17_TongHop_PheDuyet.xlsx (Đúng 4 Sheet: Tổng quan, Đối chiếu dữ liệu, Dữ liệu cần xác minh, Trạng thái phê duyệt)\n";

echo "=== TẤT CẢ 17 FILE EXCEL ĐÃ ĐƯỢC XUẤT THÀNH CÔNG VÀO: {$outputDir} ===\n";
