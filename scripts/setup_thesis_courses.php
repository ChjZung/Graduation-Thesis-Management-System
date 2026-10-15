<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\HocPhan;
use App\Models\DeTai;

echo "--- 1. CẬP NHẬT 12 HỌC PHẦN (CỬ NHÂN & TỐT NGHIỆP CHO 6 BỘ MÔN) ---\n";

$courses = [
    // 1. CNPM
    [
        'MaHocPhan'   => 'HP_KLCN_CNPM',
        'TenHocPhan'  => 'Khóa luận cử nhân - Công nghệ phần mềm',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'CNPM',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Công nghệ phần mềm (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_CNPM',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - Công nghệ phần mềm',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'CNPM',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành Công nghệ phần mềm (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],

    // 2. HTTT
    [
        'MaHocPhan'   => 'HP_KLCN_HTTT',
        'TenHocPhan'  => 'Khóa luận cử nhân - Hệ thống thông tin',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'HTTT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Hệ thống thông tin quản lý và phân tích nghiệp vụ (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_HTTT',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - Hệ thống thông tin',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'HTTT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành Hệ thống thông tin (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],

    // 3. ATTT
    [
        'MaHocPhan'   => 'HP_KLCN_ATTT',
        'TenHocPhan'  => 'Khóa luận cử nhân - An toàn thông tin',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'ATTT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành An toàn thông tin và an ninh mạng (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_ATTT',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - An toàn thông tin',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'ATTT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành An toàn thông tin (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],

    // 4. KHMT
    [
        'MaHocPhan'   => 'HP_KLCN_KHMT',
        'TenHocPhan'  => 'Khóa luận cử nhân - Khoa học máy tính',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'KHMT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Khoa học máy tính và thuật toán ứng dụng (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_KHMT',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - Khoa học máy tính',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'KHMT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học máy tính (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],

    // 5. KHDL
    [
        'MaHocPhan'   => 'HP_KLCN_KHDL',
        'TenHocPhan'  => 'Khóa luận cử nhân - Khoa học dữ liệu & AI',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'KHDL',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Khoa học dữ liệu và phân tích định lượng (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_KHDL',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - Khoa học dữ liệu & AI',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'KHDL',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học dữ liệu & Trí tuệ nhân tạo (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],

    // 6. MMT
    [
        'MaHocPhan'   => 'HP_KLCN_MMT',
        'TenHocPhan'  => 'Khóa luận cử nhân - Mạng máy tính & Viễn thông',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'MMT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp hệ Cử nhân chuyên ngành Mạng máy tính và truyền thông dữ liệu (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
    [
        'MaHocPhan'   => 'HP_KLTN_MMT',
        'TenHocPhan'  => 'Khóa luận tốt nghiệp - Mạng máy tính & Viễn thông',
        'SoTinChi'    => 10,
        'MaKhoa'      => 'CNTT',
        'MaBoMon'     => 'MMT',
        'LoaiHocPhan' => 'Khóa luận',
        'MoTa'        => 'Học phần Khóa luận tốt nghiệp chuyên ngành Mạng máy tính và Viễn thông (10 tín chỉ).',
        'TrangThai'   => 'Đang sử dụng'
    ],
];

// Trước tiên chuyển các đề tài đang trỏ tới mã cũ nếu có
$topicMappings = [
    'DT01' => 'HP_KLCN_CNPM',
    'DT02' => 'HP_KLTN_CNPM',
    'DT03' => 'HP_KLCN_HTTT',
    'DT04' => 'HP_KLTN_HTTT',
    'DT05' => 'HP_KLCN_ATTT',
    'DT06' => 'HP_KLTN_ATTT',
    'DT07' => 'HP_KLCN_KHMT',
    'DT08' => 'HP_KLTN_KHMT',
    'DT09' => 'HP_KLCN_KHDL',
    'DT10' => 'HP_KLTN_KHDL',
    'DT11' => 'HP_KLCN_MMT',
    'DT12' => 'HP_KLTN_MMT',
];

// Chèn hoặc cập nhật 12 học phần
foreach ($courses as $c) {
    HocPhan::updateOrCreate(
        ['MaHocPhan' => $c['MaHocPhan']],
        $c
    );
    echo "  [OK] Học phần: {$c['MaHocPhan']} - {$c['TenHocPhan']}\n";
}

// Cập nhật DeTai trỏ đến đúng mã học phần
foreach ($topicMappings as $maDT => $maHP) {
    DeTai::where('MaDeTai', $maDT)->update([
        'MaHocPhan' => $maHP,
        'HocPhan'   => $maHP,
    ]);
    echo "  [OK] Cập nhật đề tài {$maDT} -> {$maHP}\n";
}

// Xóa các mã cũ HP_KLKS_* và mã cũ khác không dùng
$validCodes = array_column($courses, 'MaHocPhan');
$oldCodes = DB::table('HocPhan')
    ->whereNotIn('MaHocPhan', $validCodes)
    ->pluck('MaHocPhan')
    ->toArray();

foreach ($oldCodes as $old) {
    // Kiểm tra có đề tài nào dính không
    $dtCount = DB::table('DeTai')->where('MaHocPhan', $old)->count();
    if ($dtCount === 0) {
        DB::table('HocPhan_HocKy')->where('MaHocPhan', $old)->delete();
        DB::table('HocPhan')->where('MaHocPhan', $old)->delete();
        echo "  [CLEANUP] Đã xóa mã học phần cũ không dùng: {$old}\n";
    }
}

// Mở các học phần này trong kỳ HK2627_1
$hocKy = DB::table('HocKy')->where('TrangThai', 1)->first() 
      ?? DB::table('HocKy')->where('MaHocKy', 'HK2627_1')->first();
$maHocKy = $hocKy ? $hocKy->MaHocKy : 'HK2627_1';

echo "--- 2. MỞ HỌC PHẦN TRONG HỌC KỲ {$maHocKy} ---\n";
foreach ($validCodes as $mhp) {
    DB::table('HocPhan_HocKy')->updateOrInsert(
        ['MaHocPhan' => $mhp, 'MaHocKy' => $maHocKy],
        ['TrangThai' => 'Đang mở']
    );
    echo "  [OK] Học phần {$mhp} đã mở trong kỳ {$maHocKy}\n";
}

echo "=== HOÀN TẤT CẤU HÌNH 12 HỌC PHẦN KHÓA LUẬN CHO 6 BỘ MÔN ===\n";
