<?php

/**
 * Script xóa toàn bộ dữ liệu nền và nghiệp vụ để chuẩn bị import lại từ đầu.
 * GIỮ LẠI:
 * - Bảng vaitro (5 vai trò hệ thống)
 * - Tài khoản Giáo vụ Quản trị (TK_ADMIN, admin, GVU01)
 * - Các bảng kỹ thuật hệ thống (migrations, cache, jobs, sessions)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== BẮT ĐẦU DỌN DẸP DỮ LIỆU NỀN ===\n";

DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

$tablesToTruncate = [
    'baocaotiendo',
    'khoibaocaotiendo',
    'tomtatbaocao',
    'tailieunop',
    'tephosobaove',
    'hosobaove',
    'phieuchamdiem',
    'thanhvienhoidong',
    'hoidong',
    'phancongphanbien',
    'chitieuphanbien',
    'chitieuhuongdan',
    'chitietduyetdetai',
    'phieudangky',
    'thanhviennhom',
    'nhom',
    'ketquasinhvien',
    'danhsachsvdudieukien',
    'detai',
    'lichgaphuongdan',
    'mocthoigiankhoaluan',
    'kehoachkhoaluan',
    'quydinhkhoaluan',
    'hocphan_hocky',
    'hocphan',
    'thongbao',
    'yeucaudoimatkhau',
    'bieumau',
    'sinhvien',
    'giangvien',
    'lop',
    'chuyennganh',
    'nganh',
    'bomon',
    'khoa',
    'hocky'
];

foreach ($tablesToTruncate as $tbl) {
    if (Schema::hasTable($tbl)) {
        DB::table($tbl)->truncate();
        echo " - Đã làm trống bảng: {$tbl}\n";
    }
}

// Giữ lại tài khoản admin trong taikhoan
if (Schema::hasTable('taikhoan')) {
    $deleted = DB::table('taikhoan')->where('MaTK', '!=', 'TK_ADMIN')->delete();
    echo " - Đã xóa {$deleted} tài khoản người dùng khác. Giữ lại tài khoản Quản trị viên (admin/TK_ADMIN).\n";
}

// Đảm bảo giaovu giữ nguyên GVU01
if (Schema::hasTable('giaovu')) {
    $otherGVu = DB::table('giaovu')->where('MaTK', '!=', 'TK_ADMIN')->delete();
    if ($otherGVu > 0) {
        echo " - Đã dọn dẹp các bản ghi giáo vụ phụ ({$otherGVu}).\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

echo "=== HOÀN TẤT DỌN DẸP DỮ LIỆU NỀN ===\n";
echo "Hệ thống đã sẵn sàng 100% để bạn thực hiện import lại từng phần.\n";
