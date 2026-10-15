<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$outputDir = __DIR__ . '/../sample_imports';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

function exportDualFormat($filenameBase, array $headers, array $data, $dir)
{
    // 1. Ghi file CSV có UTF-8 BOM
    $csvPath = $dir . '/' . $filenameBase . '.csv';
    $fp = fopen($csvPath, 'w');
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($data as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    echo " -> [CSV]  {$filenameBase}.csv (" . count($data) . " dong)\n";

    // 2. Ghi file XLSX chuẩn
    $xlsxPath = $dir . '/' . $filenameBase . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($filenameBase, 0, 31));

    // Headers
    foreach ($headers as $colIdx => $h) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue("{$colLetter}1", $h);
    }

    // Data
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

    // Style Header: Xanh HUIT, chữ trắng, in đậm, căn giữa
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '00529B']
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
    $writer->save($xlsxPath);
    echo " -> [XLSX] {$filenameBase}.xlsx\n";
}

echo "=========================================================\n";
echo " XUAT DU LIEU THUC TE TRONG DATABASE RA SAMPLE_IMPORTS\n";
echo "=========================================================\n";

// 1. Khoa
$headersKhoa = ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'MoTa'];
$khoas = \App\Models\Khoa::orderBy('MaKhoa')->get();
$dataKhoa = [];
foreach ($khoas as $k) {
    $dataKhoa[] = [
        $k->MaKhoa,
        $k->TenKhoa,
        $k->TruongKhoa ?? 'PGS.TS. Nguyễn Văn Hùng',
        $k->MoTa ?? 'Khoa đào tạo thuộc Đại học Công Thương TP.HCM'
    ];
}
exportDualFormat('01_Khoa_Mau', $headersKhoa, $dataKhoa, $outputDir);

// 2. Bộ môn
$headersBoMon = ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon', 'MoTa'];
$boMons = \App\Models\BoMon::orderBy('MaBoMon')->get();
$dataBoMon = [];
foreach ($boMons as $bm) {
    $dataBoMon[] = [
        $bm->MaBoMon,
        $bm->TenBoMon,
        $bm->MaKhoa ?? 'CNTT',
        $bm->TruongBoMon ?? '',
        $bm->MoTa ?? ''
    ];
}
exportDualFormat('02_BoMon_Mau', $headersBoMon, $dataBoMon, $outputDir);

// 3. Ngành
$headersNganh = ['MaNganh', 'TenNganh', 'MaKhoa'];
$nganhs = \App\Models\Nganh::orderBy('MaNganh')->get();
$dataNganh = [];
foreach ($nganhs as $ng) {
    $dataNganh[] = [
        $ng->MaNganh,
        $ng->TenNganh,
        $ng->MaKhoa ?? 'CNTT'
    ];
}
exportDualFormat('03_Nganh_Mau', $headersNganh, $dataNganh, $outputDir);

// 4. Lớp
$headersLop = ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc', 'MaKhoa'];
$lops = \App\Models\Lop::orderBy('MaLop')->get();
$dataLop = [];
foreach ($lops as $l) {
    $dataLop[] = [
        $l->MaLop,
        $l->TenLop,
        $l->MaNganh ?? '7480201',
        $l->KhoaHoc ?? '2021-2025',
        $l->MaKhoa ?? 'CNTT'
    ];
}
exportDualFormat('04_Lop_Mau', $headersLop, $dataLop, $outputDir);

// 5. Học kỳ
$headersHocKy = ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];
$hocKys = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
$dataHocKy = [];
foreach ($hocKys as $hk) {
    $dataHocKy[] = [
        $hk->MaHocKy,
        $hk->TenHocKy,
        $hk->NamHoc,
        $hk->NgayBatDau ? substr($hk->NgayBatDau, 0, 10) : '2026-08-01',
        $hk->NgayKetThuc ? substr($hk->NgayKetThuc, 0, 10) : '2027-01-15',
        $hk->TrangThai ?? 'Đang diễn ra'
    ];
}
exportDualFormat('05_HocKy_Mau', $headersHocKy, $dataHocKy, $outputDir);

// 6. Giảng viên
$headersGV = ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai'];
$giangViens = \App\Models\GiangVien::orderBy('MaGV')->get();
$dataGV = [];
foreach ($giangViens as $gv) {
    $dataGV[] = [
        $gv->MaGV,
        $gv->HoTen,
        $gv->Email ?? ($gv->MaGV . '@huit.edu.vn'),
        $gv->SoDienThoai ?? '',
        $gv->NgaySinh ? substr($gv->NgaySinh, 0, 10) : '1985-01-01',
        $gv->GioiTinh ?? 'Nam',
        $gv->HocHam ?? '',
        $gv->HocVi ?? 'Thạc sĩ',
        $gv->MaBoMon ?? 'CNPM',
        $gv->TrangThai ?? 'Đang công tác'
    ];
}
exportDualFormat('06_GiangVien_Mau', $headersGV, $dataGV, $outputDir);

// 7. Sinh viên
$headersSV = ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'];
$sinhViens = \App\Models\SinhVien::orderBy('MaSV')->get();
$dataSV = [];
foreach ($sinhViens as $sv) {
    $dataSV[] = [
        $sv->MaSV,
        $sv->HoTen,
        $sv->Email ?? ($sv->MaSV . '@st.huit.edu.vn'),
        $sv->SoDienThoai ?? '',
        $sv->NgaySinh ? substr($sv->NgaySinh, 0, 10) : '2003-01-01',
        $sv->GioiTinh ?? 'Nam',
        $sv->MaLop ?? '12DHCNTT01',
        $sv->KhoaHoc ?? '2021-2025',
        $sv->SoTinChiTichLuy ?? 125,
        $sv->DiemTichLuy ?? 3.20,
        $sv->TrangThai ?? 'Đang học'
    ];
}
exportDualFormat('07_SinhVien_Mau', $headersSV, $dataSV, $outputDir);

// 8. Sinh viên đủ điều kiện theo học kỳ
$headersSVDDK = ['MSSV', 'HoTen', 'MaLop', 'MaHocKy', 'SoTinChiTichLuy', 'DiemTichLuy', 'GhiChu'];
$ddks = \App\Models\DanhSachSVDuDieuKien::with('sinhVien')->orderBy('MaSV')->get();
$dataDDK = [];
foreach ($ddks as $dk) {
    $dataDDK[] = [
        $dk->MaSV,
        $dk->sinhVien->HoTen ?? '',
        $dk->sinhVien->MaLop ?? '12DHCNTT01',
        $dk->MaHocKy ?? 'HK2627_1',
        $dk->sinhVien->SoTinChiTichLuy ?? 125,
        $dk->sinhVien->DiemTichLuy ?? 3.20,
        $dk->GhiChu ?? 'Đủ điều kiện làm khóa luận tốt nghiệp - Đã công bố'
    ];
}
exportDualFormat('09_SinhVien_TheoHocKy_Mau', $headersSVDDK, $dataDDK, $outputDir);

// 9. Học phần khóa luận
$headersHP = ['MaHocPhan', 'TenHocPhan', 'SoTinChi', 'MaKhoa', 'MaBoMon', 'LoaiHocPhan', 'MoTa'];
$hocPhans = \App\Models\HocPhan::orderBy('MaHocPhan')->get();
$dataHP = [];
foreach ($hocPhans as $hp) {
    $dataHP[] = [
        $hp->MaHocPhan,
        $hp->TenHocPhan,
        $hp->SoTinChi ?? 10,
        $hp->MaKhoa ?? 'CNTT',
        $hp->MaBoMon ?? 'CNPM',
        $hp->LoaiHocPhan ?? 'Khóa luận',
        $hp->MoTa ?? ''
    ];
}
exportDualFormat('10_HocPhan_KhoaLuan_Mau', $headersHP, $dataHP, $outputDir);
exportDualFormat('Template_HocPhan', $headersHP, $dataHP, $outputDir);

// 10. Đề tài khóa luận
$headersDT = ['MaDeTai', 'TenDeTai', 'MoTa', 'YeuCau', 'LinhVuc', 'MaHocPhan', 'TenHocPhan', 'MaGV', 'TenGV', 'MaHocKy', 'SoLuongSinhVienToiDa', 'TrangThai'];
$deTais = \App\Models\DeTai::with(['giangVien', 'hocPhan'])->orderBy('MaDeTai')->get();
$dataDT = [];
foreach ($deTais as $dt) {
    $dataDT[] = [
        $dt->MaDeTai,
        $dt->TenDeTai,
        strip_tags(str_replace(['</li><li>', '</li>', '<li>'], ["\n", "\n", "- "], $dt->MoTa ?? '')),
        $dt->YeuCau ?? '',
        $dt->LinhVuc ?? 'Công nghệ phần mềm',
        $dt->MaHocPhan ?? 'HP_KLCN_CNPM',
        $dt->HocPhan ?? ($dt->hocPhan->TenHocPhan ?? 'Khóa luận cử nhân - Công nghệ phần mềm'),
        $dt->MaGV ?? 'GV00000002',
        $dt->giangVien->HoTen ?? 'TS. Trần Anh Dũng',
        $dt->MaHocKy ?? 'HK2627_1',
        $dt->SoLuongSinhVienToiDa ?? 3,
        $dt->TrangThai ?? 'Chờ duyệt cấp Bộ môn'
    ];
}
exportDualFormat('11_DeTai_KhoaLuan_Mau', $headersDT, $dataDT, $outputDir);
exportDualFormat('DanhSach_DeTai_Mau', $headersDT, $dataDT, $outputDir);
exportDualFormat('DanhSach_DeTai_Moi_TheoHocPhan_HocKy', $headersDT, $dataDT, $outputDir);
exportDualFormat('DanhSach_DeTai_Moi_TheoHocPhan_HocKy_CapNhat', $headersDT, $dataDT, $outputDir);

echo "\n=> HOAN TAT XUAT TOAN BO FILE EXCEL & CSV SAMPLE IMPORTS!\n";
