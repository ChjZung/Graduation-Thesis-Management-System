<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Them3SVVa1GVCNPMSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $defaultPassword = Hash::make('Huit@2026');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Thêm 1 Giảng viên mới thuộc Bộ môn Công nghệ phần mềm (CNPM)
        $maGV = 'GV00000047';
        $tenGV = 'TS. Phạm Minh Tuấn';
        $emailGV = 'tuanpm@huit.edu.vn';
        $sdtGV = '0918765432';

        DB::table('taikhoan')->updateOrInsert(
            ['TenDangNhap' => $maGV],
            [
                'MaTK'              => $maGV,
                'TenDangNhap'       => $maGV,
                'MatKhau'           => $defaultPassword,
                'MaVaiTro'          => 'VT02', // Giảng viên
                'MaGV'              => $maGV,
                'TrangThai'         => 1,
                'SoLanDangNhapSai'  => 0,
                'BatBuocDoiMatKhau' => 1,
                'TrangThaiMatKhau'  => 'INITIAL',
                'LanDangNhapDau'    => null,
                'NgayDoiMatKhau'    => null,
                'LanDangNhapCuoi'   => null,
                'updated_at'        => $now,
                'created_at'        => $now,
            ]
        );

        DB::table('giangvien')->updateOrInsert(
            ['MaGV' => $maGV],
            [
                'MaTK'        => $maGV,
                'HoTen'       => $tenGV,
                'NgaySinh'    => '1982-08-15',
                'GioiTinh'    => 'Nam',
                'Email'       => $emailGV,
                'SoDienThoai' => $sdtGV,
                'HocHam'      => null,
                'HocVi'       => 'Tiến sĩ',
                'TrangThai'   => 'Đang công tác',
                'MaBoMon'     => 'CNPM', // Bộ môn Công nghệ phần mềm
                'updated_at'  => $now,
                'created_at'  => $now,
            ]
        );

        // 2. Thêm 3 Sinh viên mới thuộc Ngành Công nghệ Thông tin (7480201)
        $students = [
            '2001210076' => [
                'HoTen'       => 'Nguyễn Hoàng Long',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-06-15',
                'SoDienThoai' => '0978001076',
                'DiemTichLuy' => 3.45,
                'TinChi'      => 126,
            ],
            '2001210077' => [
                'HoTen'       => 'Trần Bích Phương',
                'GioiTinh'    => 'Nữ',
                'NgaySinh'    => '2003-07-22',
                'SoDienThoai' => '0978001077',
                'DiemTichLuy' => 3.62,
                'TinChi'      => 130,
            ],
            '2001210078' => [
                'HoTen'       => 'Lê Anh Tuấn',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-08-10',
                'SoDienThoai' => '0978001078',
                'DiemTichLuy' => 3.50,
                'TinChi'      => 128,
            ],
        ];

        foreach ($students as $mssv => $info) {
            // Tài khoản sinh viên
            DB::table('taikhoan')->updateOrInsert(
                ['TenDangNhap' => $mssv],
                [
                    'MaTK'              => $mssv,
                    'TenDangNhap'       => $mssv,
                    'MatKhau'           => $defaultPassword,
                    'MaVaiTro'          => 'VT03', // Sinh viên
                    'MaGV'              => null,
                    'TrangThai'         => 1,
                    'SoLanDangNhapSai'  => 0,
                    'BatBuocDoiMatKhau' => 1,
                    'TrangThaiMatKhau'  => 'INITIAL',
                    'LanDangNhapDau'    => null,
                    'NgayDoiMatKhau'    => null,
                    'LanDangNhapCuoi'   => null,
                    'updated_at'        => $now,
                    'created_at'        => $now,
                ]
            );

            // Thông tin sinh viên
            DB::table('sinhvien')->updateOrInsert(
                ['MaSV' => $mssv],
                [
                    'MaTK'            => $mssv,
                    'HoTen'           => $info['HoTen'],
                    'NgaySinh'        => $info['NgaySinh'],
                    'GioiTinh'        => $info['GioiTinh'],
                    'Email'           => "{$mssv}@st.huit.edu.vn",
                    'SoDienThoai'     => $info['SoDienThoai'],
                    'NgayNhapHoc'     => '2021-09-05',
                    'KhoaHoc'         => '2021-2025',
                    'SoTinChiTichLuy' => $info['TinChi'],
                    'DiemTichLuy'     => $info['DiemTichLuy'],
                    'TrangThai'       => 'Đang học',
                    'MaKhoa'          => 'CNTT',
                    'MaNganh'         => '7480201', // Công nghệ Thông tin
                    'MaLop'           => '12DHCNTT01',
                    'updated_at'      => $now,
                    'created_at'      => $now,
                ]
            );

            // Đưa vào danh sách đủ điều kiện làm khóa luận HK2627_1
            DB::table('danhsachsvdudieukien')->updateOrInsert(
                [
                    'MaSV'    => $mssv,
                    'MaHocKy' => 'HK2627_1',
                ],
                [
                    'MaDSDK'       => "DK_{$mssv}_261",
                    'NgayXetDuyet' => $now->toDateString(),
                    'TrangThai'    => 'Đủ điều kiện',
                    'GhiChu'       => 'Đủ điều kiện làm Khóa luận tốt nghiệp',
                    'DieuKien'     => 'Tích lũy >= 120 tín chỉ, CPA >= 2.5',
                    'updated_at'   => $now,
                    'created_at'   => $now,
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        if (isset($this->command)) {
            $this->command->info("Đã tạo thành công 1 Giảng viên ({$maGV} - {$tenGV}) thuộc Bộ môn CNPM với mật khẩu mặc định Huit@2026 (yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên).");
            $this->command->info("Đã tạo thành công 3 Sinh viên (2001210076, 2001210077, 2001210078) thuộc Ngành CNTT với mật khẩu mặc định Huit@2026 (yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên).");
        }
    }
}
