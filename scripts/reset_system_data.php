<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "=======================================================\n";
echo ">>> TIEN HANH RESET TOAN BO DU LIEU HE THONG VE TRONG <<<\n";
echo "=======================================================\n\n";

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
echo "[DELETE] Da xoa {$deletedTK} tai khoan (Giang vien, Sinh vien, TBM, Truong khoa).\n";

// Cap nhat mat khau chuan '123456' cho admin va giaovu01 de tien su dung
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

// Cap nhat giaovu set MaKhoa = NULL vi bang khoa da lam trong
if (Schema::hasTable('giaovu')) {
    DB::table('giaovu')->update([
        'MaKhoa' => null
    ]);
    echo "[UPDATE] Da reset MaKhoa trong giaovu thanh NULL.\n";
}

// Dam bao 5 role chuan trong vaitro
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
echo "[CHECK] Da dam bao 5 vai tro co dinh trong bang vaitro.\n";

DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

echo "\n=======================================================\n";
echo ">>> HOAN TAT RESET DATABASE HE THONG! <<<\n";
echo "=======================================================\n";
