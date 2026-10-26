<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=================================================================\n";
echo "KHỞI TẠO 3 TÀI KHOẢN SINH VIÊN MỚI PHỤC VỤ TEST LUỒNG (CNPM)\n";
echo "=================================================================\n";

$studentsData = [
    [
        'MaSV'        => '2001210080',
        'HoTen'       => 'Lê Hoàng Nam',
        'GioiTinh'    => 'Nam',
        'NgaySinh'    => '2003-03-18',
        'Email'       => '2001210080@st.huit.edu.vn',
        'SoDienThoai' => '0978001080',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 132,
        'GPA'         => 3.60,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm (Bộ môn CNPM)
        'Khoa'        => 'CNTT',
    ],
    [
        'MaSV'        => '2001210081',
        'HoTen'       => 'Nguyễn Mai Phương',
        'GioiTinh'    => 'Nữ',
        'NgaySinh'    => '2003-09-22',
        'Email'       => '2001210081@st.huit.edu.vn',
        'SoDienThoai' => '0978001081',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 129,
        'GPA'         => 3.48,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm (Bộ môn CNPM)
        'Khoa'        => 'CNTT',
    ],
    [
        'MaSV'        => '2001210082',
        'HoTen'       => 'Vũ Tiến Đạt',
        'GioiTinh'    => 'Nam',
        'NgaySinh'    => '2003-12-10',
        'Email'       => '2001210082@st.huit.edu.vn',
        'SoDienThoai' => '0978001082',
        'KhoaHoc'     => '2021-2025',
        'TinChi'      => 135,
        'GPA'         => 3.72,
        'Lop'         => '12DHKPM01',
        'Nganh'       => '7480103', // Kỹ thuật Phần mềm (Bộ môn CNPM)
        'Khoa'        => 'CNTT',
    ],
];

$passwordHash = Hash::make('Huit@2026');
$now = now();

DB::beginTransaction();
try {
    foreach ($studentsData as $data) {
        $masv = $data['MaSV'];

        // 1. Tạo hoặc cập nhật Tài khoản
        $tk = TaiKhoan::updateOrCreate(
            ['TenDangNhap' => $masv],
            [
                'MaTK'              => $masv,
                'MatKhau'           => $passwordHash,
                'MaVaiTro'          => 'VT03', // Vai trò Sinh viên
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'ACTIVE',
                'BatBuocDoiMatKhau' => false,
                'SoLanDangNhapSai'  => 0,
                'NgayDoiMatKhau'    => $now,
                'LanDangNhapDau'    => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        // 2. Tạo hoặc cập nhật Sinh viên
        $sv = SinhVien::updateOrCreate(
            ['MaSV' => $masv],
            [
                'MaTK'            => $masv,
                'HoTen'           => $data['HoTen'],
                'NgaySinh'        => $data['NgaySinh'],
                'GioiTinh'        => $data['GioiTinh'],
                'Email'           => $data['Email'],
                'SoDienThoai'     => $data['SoDienThoai'],
                'KhoaHoc'         => $data['KhoaHoc'],
                'SoTinChiTichLuy' => $data['TinChi'],
                'DiemTichLuy'     => $data['GPA'],
                'TrangThai'       => 'Đang học',
                'MaKhoa'          => $data['Khoa'],
                'MaNganh'         => $data['Nganh'],
                'MaLop'           => $data['Lop'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        );

        // 3. Đưa vào danh sách đủ điều kiện cả 2 học kỳ HK2627_1 và HK2425_1 để test linh hoạt
        $hks = [
            'HK2627_1' => "DK_{$masv}_26",
            'HK2425_1' => "DK_{$masv}_24",
        ];
        foreach ($hks as $hk => $maDsdk) {
            DanhSachSVDuDieuKien::updateOrCreate(
                ['MaDSDK' => $maDsdk],
                [
                    'MaSV'         => $masv,
                    'MaHocKy'      => $hk,
                    'NgayXetDuyet' => $now->toDateString(),
                    'TrangThai'    => 'Đủ điều kiện',
                    'DieuKien'     => "Tín chỉ: {$data['TinChi']} | ĐTB: {$data['GPA']}",
                    'GhiChu'       => 'Sinh viên Kỹ thuật Phần mềm (Bộ môn CNPM)',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]
            );
        }

        echo " [OK] Đã tạo thành công Sinh viên: [{$masv}] {$data['HoTen']} - Lớp: {$data['Lop']} - Ngành: {$data['Nganh']}\n";
    }

    DB::commit();
    echo "\n=== HOÀN TẤT KHỞI TẠO 3 TÀI KHOẢN SINH VIÊN MỚI! ===\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo " [ERROR] Lỗi khi tạo sinh viên: " . $e->getMessage() . "\n";
    exit(1);
}
