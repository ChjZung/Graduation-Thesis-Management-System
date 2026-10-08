<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AddFiftyStudentsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $sinhViens = [];
        $taiKhoans = [];
        $dsdk = [];

        $hoArray = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
        $demArray = ['Văn', 'Thị', 'Đình', 'Minh', 'Ngọc', 'Hải', 'Thành', 'Quốc', 'Gia', 'Hoàng', 'Đức', 'Anh', 'Thanh', 'Hữu', 'Trọng'];
        $tenArray = ['An', 'Bình', 'Châu', 'Dũng', 'Em', 'Giang', 'Hưng', 'Khánh', 'Long', 'Minh', 'Nam', 'Phúc', 'Quân', 'Sơn', 'Tài', 'Tú', 'Vinh', 'Xuân', 'Yến', 'Khoa', 'Huy', 'Thịnh', 'Trí', 'Đạt', 'Hiếu'];

        $classes = [
            ['MaLop' => '12DHCNTT01', 'MaNganh' => '7480201'],
            ['MaLop' => '12DHCNTT02', 'MaNganh' => '7480201'],
            ['MaLop' => '12DHATTT01', 'MaNganh' => '7480202'],
            ['MaLop' => '12DHKPM01',  'MaNganh' => '7480103'],
            ['MaLop' => '12DHHTTT01', 'MaNganh' => '7480104'],
            ['MaLop' => '12DHKHMT01', 'MaNganh' => '7480101'],
        ];

        $passwordHash = Hash::make('123456');

        for ($i = 1; $i <= 50; $i++) {
            $maSV = '200123' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $ho = $hoArray[$i % count($hoArray)];
            $dem = $demArray[$i % count($demArray)];
            $ten = $tenArray[$i % count($tenArray)];
            $hoTen = "$ho $dem $ten";
            $cls = $classes[$i % count($classes)];

            // Insert TaiKhoan
            $taiKhoans[] = [
                'MaTK'              => $maSV,
                'TenDangNhap'       => $maSV,
                'MatKhau'           => $passwordHash,
                'MaVaiTro'          => 'VT03',
                'TrangThai'         => 1,
                'SoLanDangNhapSai'  => 0,
                'BatBuocDoiMatKhau' => 1,
                'TrangThaiMatKhau'  => 'INITIAL',
                'LanDangNhapDau'    => null,
                'NgayDoiMatKhau'    => $now,
                'LanDangNhapCuoi'   => null,
                'NgayKhoa'          => null,
                'created_at'        => $now,
                'updated_at'        => $now
            ];

            // Insert SinhVien
            $sinhViens[] = [
                'MaSV'            => $maSV,
                'MaTK'            => $maSV,
                'HoTen'           => $hoTen,
                'NgaySinh'        => '2005-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT),
                'GioiTinh'        => ($i % 3 == 0) ? 'Nữ' : 'Nam',
                'Email'           => $maSV . '@st.huit.edu.vn',
                'SoDienThoai'     => '09' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'NgayNhapHoc'     => '2023-09-05',
                'KhoaHoc'         => '2023-2027',
                'SoTinChiTichLuy' => rand(120, 142),
                'DiemTichLuy'     => round(rand(280, 395) / 100, 2),
                'TrangThai'       => 'Đang học',
                'MaKhoa'          => 'CNTT',
                'MaNganh'         => $cls['MaNganh'],
                'MaLop'           => $cls['MaLop'],
                'created_at'      => $now,
                'updated_at'      => $now
            ];

            // Insert DSDK cho học kỳ hiện tại HK2627_1
            $dsdk[] = [
                'MaDSDK'       => 'DSDK_' . $maSV,
                'NgayXetDuyet' => '2026-08-01',
                'TrangThai'    => 'Đủ điều kiện',
                'GhiChu'       => 'Đủ điều kiện làm khóa luận tốt nghiệp',
                'DieuKien'     => 'Tín chỉ: >= 115 | ĐTB: >= 2.50',
                'MaSV'         => $maSV,
                'MaHocKy'      => 'HK2627_1',
                'created_at'   => $now,
                'updated_at'   => $now
            ];
        }

        DB::table('TaiKhoan')->upsert($taiKhoans, ['MaTK'], ['MatKhau', 'MaVaiTro', 'TrangThai', 'updated_at']);
        DB::table('SinhVien')->upsert($sinhViens, ['MaSV'], ['HoTen', 'Email', 'MaLop', 'MaNganh', 'updated_at']);
        DB::table('DanhSachSVDuDieuKien')->upsert($dsdk, ['MaDSDK'], ['TrangThai', 'updated_at']);
    }
}
