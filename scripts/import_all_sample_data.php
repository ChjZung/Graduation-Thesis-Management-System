<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ExcelImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "========================================================\n";
echo ">>> BUOC 1: RESET TOAN BO DU LIEU DATABASE HE THONG <<<\n";
echo "========================================================\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

$tablesToTruncate = [
    'baocaotiendo',
    'bieumau',
    'chitietduyetdetai',
    'chitieuhuongdan',
    'danhsachsvdudieukien',
    'detai',
    'hoidong',
    'hosobaove',
    'ketquasinhvien',
    'lichgaphuongdan',
    'mocthoigiankhoaluan',
    'phancongphanbien',
    'phieuchamdiem',
    'phieudangky',
    'quydinhkhoaluan',
    'kehoachkhoaluan',
    'tailieunop',
    'tephosobaove',
    'thanhvienhoidong',
    'thanhviennhom',
    'thongbao',
    'tomtatbaocao',
    'yeucaudoimatkhau',
    'nhom',
    'sinhvien',
    'giangvien',
    'lop',
    'chuyennganh',
    'nganh',
    'bomon',
    'khoa',
    'hocky',
    'sessions',
    'jobs',
    'failed_jobs'
];

foreach ($tablesToTruncate as $tbl) {
    if (Schema::hasTable($tbl)) {
        DB::table($tbl)->truncate();
        echo "[TRUNCATE] Da xoa trang bang: {$tbl}\n";
    }
}

// Giu lai tai khoan Admin (VT01), xoa sach cac tai khoan con lai
echo "\n--- Xu ly bang taikhoan ---\n";
$deletedTK = DB::table('taikhoan')
    ->where('MaVaiTro', '!=', 'VT01')
    ->whereNotIn('TenDangNhap', ['admin', 'giaovu01'])
    ->delete();
echo "[DELETE] Da xoa {$deletedTK} tai khoan cu (Giang vien, Sinh vien, TBM, Truong khoa).\n";

$adminPass = Hash::make('123456');
DB::table('taikhoan')->where('TenDangNhap', 'admin')->update([
    'MatKhau' => $adminPass,
    'TrangThai' => 1,
    'SoLanDangNhapSai' => 0,
    'BatBuocDoiMatKhau' => 0,
    'TrangThaiMatKhau' => 'ACTIVE'
]);

DB::table('taikhoan')->where('TenDangNhap', 'giaovu01')->update([
    'MatKhau' => $adminPass,
    'TrangThai' => 1,
    'SoLanDangNhapSai' => 0,
    'BatBuocDoiMatKhau' => 0,
    'TrangThaiMatKhau' => 'ACTIVE'
]);

echo "[UPDATE] Da dat mat khau cho 'admin' va 'giaovu01' la: 123456\n";

if (Schema::hasTable('giaovu')) {
    DB::table('giaovu')->update([
        'MaKhoa' => null
    ]);
}

$roles = [
    ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ'],
    ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên'],
    ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên'],
    ['MaVaiTro' => 'VT04', 'TenVaiTro' => 'Trưởng bộ môn'],
    ['MaVaiTro' => 'VT05', 'TenVaiTro' => 'Trưởng khoa'],
];

foreach ($roles as $r) {
    DB::table('vaitro')->updateOrInsert(
        ['MaVaiTro' => $r['MaVaiTro']],
        ['TenVaiTro' => $r['TenVaiTro']]
    );
}

DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
echo "[CHECK] Da dam bao 5 vai tro co dinh trong bang vaitro.\n";

echo "\n========================================================\n";
echo ">>> BUOC 2: IMPORT DU LIEU TU THU MUC sample_imports <<<\n";
echo "========================================================\n\n";

$service = new ExcelImportService();
$sampleDir = base_path('sample_imports');

$filesToImport = [
    [
        'step'   => '1. Danh mục Khoa',
        'file'   => '01_Khoa_Mau.xlsx',
        'method' => 'importKhoa',
        'table'  => 'Khoa'
    ],
    [
        'step'   => '2. Danh mục Bộ Môn',
        'file'   => '02_BoMon_Mau.xlsx',
        'method' => 'importBoMon',
        'table'  => 'BoMon'
    ],
    [
        'step'   => '3. Danh mục Ngành',
        'file'   => '03_Nganh_Mau.xlsx',
        'method' => 'importNganh',
        'table'  => 'Nganh'
    ],
    [
        'step'   => '4. Danh mục Lớp',
        'file'   => '04_Lop_Mau.xlsx',
        'method' => 'importLop',
        'table'  => 'Lop'
    ],
    [
        'step'   => '5. Danh mục Học kỳ',
        'file'   => '05_HocKy_Mau.xlsx',
        'method' => 'importHocKy',
        'table'  => 'HocKy'
    ],
    [
        'step'   => '6. Danh sách Giảng viên',
        'file'   => '06_GiangVien_Mau.xlsx',
        'method' => 'importGiangVien',
        'table'  => 'GiangVien'
    ],
    [
        'step'   => '7. Danh sách Sinh viên',
        'file'   => '07_SinhVien_Mau.xlsx',
        'method' => 'importSinhVien',
        'table'  => 'SinhVien'
    ],
    [
        'step'   => '8. Tài khoản Trưởng Khoa & Trưởng Bộ Môn',
        'file'   => '08_TaiKhoan_TBM_TK_Mau.xlsx',
        'method' => 'importTaiKhoanTBM_TK',
        'table'  => 'TaiKhoan'
    ]
];

foreach ($filesToImport as $item) {
    echo "--------------------------------------------------------\n";
    echo ">> Đang import: {$item['step']} ({$item['file']})...\n";
    
    $fullPath = $sampleDir . '/' . $item['file'];
    if (!file_exists($fullPath)) {
        $csvPath = str_replace('.xlsx', '.csv', $fullPath);
        if (file_exists($csvPath)) {
            $fullPath = $csvPath;
            $item['file'] = str_replace('.xlsx', '.csv', $item['file']);
        } else {
            echo "   [LOI] Khong tim thay file: {$fullPath}\n";
            continue;
        }
    }

    $mime = str_ends_with($item['file'], '.xlsx') 
        ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
        : 'text/csv';

    $uploadedFile = new UploadedFile($fullPath, $item['file'], $mime, null, true);

    try {
        $result = $service->{$item['method']}($uploadedFile);
        $total = $result['total_count'] ?? 0;
        $success = $result['success_count'] ?? 0;
        $error = $result['error_count'] ?? 0;
        echo "   [KET QUA] Tong so: {$total} | Thanh cong: {$success} | Loi: {$error}\n";

        if ($error > 0 && !empty($result['errors'])) {
            echo "   [CHI TIET LOI]:\n";
            foreach (array_slice($result['errors'], 0, 5) as $err) {
                echo "     - Dong " . ($err['row'] ?? '?') . ": " . ($err['reason'] ?? 'Khong xac dinh') . "\n";
            }
            if (count($result['errors']) > 5) {
                echo "     - ...va con " . (count($result['errors']) - 5) . " loi khac.\n";
            }
        }
    } catch (\Throwable $e) {
        echo "   [EXCEPTION] " . $e->getMessage() . "\n";
        echo "   Trace: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// BUOC 3: CAP NHAT LIEN KET GIAOVU VOI KHOA CNTT
echo "\n--------------------------------------------------------\n";
echo ">> Cap nhat lien ket Khoa cho tai khoan Giao vu/Admin...\n";
$cnttKhoa = DB::table('Khoa')->where('MaKhoa', 'CNTT')->first();
if ($cnttKhoa && Schema::hasTable('GiaoVu')) {
    DB::table('GiaoVu')->where('MaTK', 'TK_ADMIN')->update([
        'MaKhoa' => 'CNTT',
        'updated_at' => now()
    ]);
    echo "   [SUCCESS] Da gan MaKhoa 'CNTT' cho GiaoVu / Admin.\n";
}

// BUOC 4: TONG KET SO LIEU TRONG DATABASE
echo "\n========================================================\n";
echo ">>> BUOC 4: TONG HOP DU LIEU DA NHAP VAO DATABASE <<<\n";
echo "========================================================\n";

$summaryTables = [
    'Khoa'      => 'Khoa',
    'BoMon'     => 'Bộ Môn',
    'Nganh'     => 'Ngành',
    'Lop'       => 'Lớp',
    'HocKy'     => 'Học Kỳ',
    'GiangVien' => 'Giảng Viên',
    'SinhVien'  => 'Sinh Viên',
    'TaiKhoan'  => 'Tài Khoản Người Dùng'
];

foreach ($summaryTables as $tbl => $label) {
    if (Schema::hasTable($tbl)) {
        $count = DB::table($tbl)->count();
        echo sprintf(" - %-25s: %d ban ghi\n", $label, $count);
    }
}

echo "\n--- Phan bo tai khoan theo vai tro ---\n";
$roles = DB::table('VaiTro')->get();
foreach ($roles as $r) {
    $c = DB::table('TaiKhoan')->where('MaVaiTro', $r->MaVaiTro)->count();
    echo sprintf("   * [%s] %-20s: %d tai khoan\n", $r->MaVaiTro, $r->TenVaiTro, $c);
}

echo "\n========================================================\n";
echo ">>> HOAN TAT TOAN BO IMPORT DU LIEU MAU! <<<\n";
echo "========================================================\n";
