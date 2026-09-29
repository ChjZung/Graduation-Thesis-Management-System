<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$sampleDir = __DIR__ . '/../sample_imports';

function readXlsx($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = [];
    $rows = $sheet->toArray();
    $headers = array_shift($rows);
    foreach ($rows as $row) {
        if (empty(array_filter($row))) continue;
        $item = [];
        foreach ($headers as $idx => $header) {
            $item[trim($header)] = $row[$idx] ?? '';
        }
        $data[] = $item;
    }
    return $data;
}

$canBoAccounts = readXlsx($sampleDir . '/08_TaiKhoan_TBM_TK_Mau.xlsx');
$giangVien = readXlsx($sampleDir . '/06_GiangVien_Mau.xlsx');
$sinhVien = readXlsx($sampleDir . '/07_SinhVien_Mau.xlsx');

echo "=== TRUONG KHOA & TRUONG BO MON (" . count($canBoAccounts) . ") ===\n";
foreach ($canBoAccounts as $cb) {
    echo "- VaiTro: {$cb['MaVaiTro']} | Username: {$cb['TenDangNhap']} | Pass: {$cb['MatKhau']} | MaCB: {$cb['MaCanBo']} | HoTen: {$cb['HoTen']} | DonVi: {$cb['MaKhoa']}/{$cb['MaBoMon']}\n";
}

echo "\n=== GIANG VIEN (" . count($giangVien) . ") ===\n";
foreach ($giangVien as $gv) {
    echo "- GV | MaGV: {$gv['MaGV']} | HoTen: {$gv['HoTen']} | BoMon: {$gv['MaBoMon']} | Email: {$gv['Email']} | SDT: {$gv['SoDienThoai']}\n";
}

echo "\n=== SINH VIEN (" . count($sinhVien) . ") ===\n";
foreach ($sinhVien as $sv) {
    echo "- SV | MSSV: {$sv['MSSV']} | HoTen: {$sv['HoTen']} | Lop: {$sv['MaLop']} | Email: {$sv['Email']} | GPA: {$sv['DiemTichLuy']}\n";
}

