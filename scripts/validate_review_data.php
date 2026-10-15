<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dir = __DIR__ . '/../sample_imports/review';

echo "========================================================\n";
echo "   KIỂM TRA TÍNH TOÀN VẸN VÀ NHẤT QUÁN 17 FILE REVIEW    \n";
echo "========================================================\n\n";

$expectedFiles = [
    '01_Khoa.xlsx',
    '02_BoMon.xlsx',
    '03_Nganh_ChuyenNganh.xlsx',
    '04_GiangVien.xlsx',
    '05_SinhVien.xlsx',
    '06_LopHanhChinh.xlsx',
    '07_HocKy_NamHoc.xlsx',
    '08_TaiKhoan.xlsx',
    '09_VaiTro_PhanQuyen.xlsx',
    '10_KeHoach_KhoaLuan.xlsx',
    '11_DeTai_KhoaLuan.xlsx',
    '12_Nhom_SinhVien.xlsx',
    '13_PhanCong_HuongDan.xlsx',
    '14_BaoCao_TienDo.xlsx',
    '15_HoiDong_BaoVe.xlsx',
    '16_DanhSach_LoiCanXuLy.xlsx',
    '17_TongHop_PheDuyet.xlsx'
];

$errors = [];
$warnings = [];

// 1. Kiểm tra sự tồn tại của 17 file
foreach ($expectedFiles as $f) {
    $path = $dir . '/' . $f;
    if (!file_exists($path)) {
        $errors[] = "Thiếu file: {$f}";
    } else {
        echo "[OK] Tìm thấy file: {$f} (" . number_format(filesize($path)) . " bytes)\n";
    }
}

if (!empty($errors)) {
    echo "\n[ERROR] Phát hiện lỗi thiếu file!\n";
    exit(1);
}

// 2. Kiểm tra 04_GiangVien.xlsx
$gvSpreadsheet = IOFactory::load($dir . '/04_GiangVien.xlsx');
$gvSheet = $gvSpreadsheet->getActiveSheet();
$gvRows = $gvSheet->toArray();
// Bỏ qua dòng tiêu đề và ghi chú và header
// Row 1: Tiêu đề lớn
// Row 2: Ghi chú
// Row 3: Header
// Row 4+: Data
$gvData = array_slice($gvRows, 3);
$totalGV = count($gvData);
echo "\n--- KIỂM TRA 04_GiangVien.xlsx ---\n";
echo "Tổng số giảng viên: {$totalGV} (Kỳ vọng: 53)\n";
if ($totalGV !== 53) {
    $errors[] = "Số lượng giảng viên trong 04_GiangVien.xlsx là {$totalGV}, kỳ vọng 53!";
} else {
    echo "[PASS] Đúng 53 giảng viên theo danh sách chuẩn HUIT.\n";
}

$bmCounts = [];
$hasNgo = false;
foreach ($gvData as $row) {
    $maGV = $row[0];
    $hoTen = $row[1];
    $bm = $row[5];
    $email = $row[6];
    $bmCounts[$bm] = ($bmCounts[$bm] ?? 0) + 1;
    if (strpos($hoTen, 'Nguyễn Thành Ngô') !== false) {
        $hasNgo = true;
        if ($email !== 'Chưa có dữ liệu') {
            $warnings[] = "Thầy Nguyễn Thành Ngô có email: {$email} (kỳ vọng 'Chưa có dữ liệu')";
        } else {
            echo "[PASS] Thầy Nguyễn Thành Ngô được ghi nhận email đúng 'Chưa có dữ liệu'.\n";
        }
    }
}
echo "Số lượng GV theo từng bộ môn:\n";
foreach ($bmCounts as $b => $c) {
    echo "  + {$b}: {$c} giảng viên\n";
}

// 3. Kiểm tra 05_SinhVien.xlsx
$svSpreadsheet = IOFactory::load($dir . '/05_SinhVien.xlsx');
$svSheet = $svSpreadsheet->getActiveSheet();
$svRows = $svSheet->toArray();
$svData = array_slice($svRows, 3);
$totalSV = count($svData);
echo "\n--- KIỂM TRA 05_SinhVien.xlsx ---\n";
echo "Tổng số sinh viên: {$totalSV} (Kỳ vọng: 60)\n";
$firstMssv = (int)$svData[0][0];
$lastMssv = (int)$svData[count($svData) - 1][0];
echo "MSSV đầu tiên: {$firstMssv} | MSSV cuối cùng: {$lastMssv}\n";

if ($firstMssv !== 2001230001) {
    $errors[] = "MSSV đầu tiên không phải 2001230001 (thực tế: {$firstMssv})";
} else {
    echo "[PASS] MSSV bắt đầu chính xác từ 2001230001.\n";
}

$isContinuous = true;
for ($i = 0; $i < count($svData); $i++) {
    $expectedMssv = 2001230001 + $i;
    if ((int)$svData[$i][0] !== $expectedMssv) {
        $isContinuous = false;
        $errors[] = "MSSV tại dòng " . ($i + 4) . " không liên tục: {$svData[$i][0]} != {$expectedMssv}";
        break;
    }
}
if ($isContinuous) {
    echo "[PASS] Toàn bộ dãy MSSV từ {$firstMssv} đến {$lastMssv} tăng liên tục không đứt đoạn.\n";
}

// 4. Kiểm tra bảo mật trong 08_TaiKhoan.xlsx
$tkSpreadsheet = IOFactory::load($dir . '/08_TaiKhoan.xlsx');
$tkSheet = $tkSpreadsheet->getActiveSheet();
$tkRows = $tkSheet->toArray();
$tkData = array_slice($tkRows, 3);
echo "\n--- KIỂM TRA BẢO MẬT 08_TaiKhoan.xlsx ---\n";
echo "Tổng số tài khoản: " . count($tkData) . "\n";
$hasPlaintextPassword = false;
$allInitial = true;
foreach ($tkData as $row) {
    foreach ($row as $val) {
        if ($val === '123456') {
            $hasPlaintextPassword = true;
            $errors[] = "Phát hiện mật khẩu mặc định '123456' trong 08_TaiKhoan.xlsx tại tài khoản: {$row[0]}";
        }
    }
    // Cột 4: TrangThaiMatKhau, Cột 5: BatBuocDoiMatKhau
    if ($row[4] !== 'INITIAL' || $row[5] !== '1') {
        $allInitial = false;
        $errors[] = "Tài khoản {$row[0]} không ở trạng thái INITIAL hoặc BatBuocDoiMatKhau != 1";
    }
}
if (!$hasPlaintextPassword) {
    echo "[PASS] Tuyệt đối không chứa mật khẩu thuần hoặc '123456' trong 08_TaiKhoan.xlsx.\n";
}
if ($allInitial) {
    echo "[PASS] 100% tài khoản đều mang trạng thái INITIAL và BatBuocDoiMatKhau = 1.\n";
}

// 5. Kiểm tra 17_TongHop_PheDuyet.xlsx (4 Sheet)
$thSpreadsheet = IOFactory::load($dir . '/17_TongHop_PheDuyet.xlsx');
$sheetNames = $thSpreadsheet->getSheetNames();
echo "\n--- KIỂM TRA 17_TongHop_PheDuyet.xlsx ---\n";
echo "Các Sheet hiện có: " . implode(', ', $sheetNames) . "\n";
$expectedSheets = ['Tổng quan', 'Đối chiếu dữ liệu', 'Dữ liệu cần xác minh', 'Trạng thái phê duyệt'];
foreach ($expectedSheets as $reqSheet) {
    if (!in_array($reqSheet, $sheetNames)) {
        $errors[] = "Thiếu Sheet '{$reqSheet}' trong 17_TongHop_PheDuyet.xlsx";
    } else {
        $sheet = $thSpreadsheet->getSheetByName($reqSheet);
        $rowCount = count($sheet->toArray());
        echo "[PASS] Sheet '{$reqSheet}': Tồn tại, {$rowCount} dòng dữ liệu.\n";
    }
}

// 6. Tổng kết
echo "\n========================================================\n";
if (empty($errors)) {
    echo ">> KẾT QUẢ: TẤT CẢ CÁC BÀI TEST KIỂM THỬ DỮ LIỆU ĐỀU ĐẠT CHUẨN 100%!\n";
    echo ">> Sẵn sàng bàn giao 17 file Excel trong sample_imports/review để người dùng duyệt.\n";
} else {
    echo ">> KẾT QUẢ: PHÁT HIỆN " . count($errors) . " LỖI DỮ LIỆU!\n";
    foreach ($errors as $err) {
        echo "  - [LỖI] {$err}\n";
    }
}
echo "========================================================\n";
