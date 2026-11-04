<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;

class CreateStudentsCNTTSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('Huit@2026');

        $students = [
            '2001210105' => [
                'HoTen'       => 'Nguyễn Hoàng Long',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-03-12',
                'SoDienThoai' => '0901210105',
                'DiemTichLuy' => 3.45,
                'TinChi'      => 128,
                'MaLop'       => '12DHCNTT01',
            ],
            '2001210106' => [
                'HoTen'       => 'Lê Thị Thanh Trúc',
                'GioiTinh'    => 'Nữ',
                'NgaySinh'    => '2003-07-25',
                'SoDienThoai' => '0901210106',
                'DiemTichLuy' => 3.62,
                'TinChi'      => 130,
                'MaLop'       => '12DHCNTT01',
            ],
            '2001210107' => [
                'HoTen'       => 'Phạm Đức Anh',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-09-18',
                'SoDienThoai' => '0901210107',
                'DiemTichLuy' => 3.38,
                'TinChi'      => 125,
                'MaLop'       => '12DHCNTT02',
            ],
            '2001210108' => [
                'HoTen'       => 'Vũ Hải Đăng',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-12-05',
                'SoDienThoai' => '0901210108',
                'DiemTichLuy' => 3.55,
                'TinChi'      => 127,
                'MaLop'       => '12DHCNTT02',
            ],
            '2001210109' => [
                'HoTen'       => 'Đỗ Quỳnh Nga',
                'GioiTinh'    => 'Nữ',
                'NgaySinh'    => '2003-04-30',
                'SoDienThoai' => '0901210109',
                'DiemTichLuy' => 3.70,
                'TinChi'      => 132,
                'MaLop'       => '12DHCNTT01',
            ],
        ];

        foreach ($students as $mssv => $info) {
            // 1. Tạo hoặc cập nhật Tài khoản
            TaiKhoan::updateOrCreate(
                ['TenDangNhap' => $mssv],
                [
                    'MaTK'              => $mssv,
                    'MatKhau'           => $passwordHash,
                    'MaVaiTro'          => 'VT03', // Vai trò Sinh viên
                    'TrangThai'         => true,
                    'SoLanDangNhapSai'  => 0,
                    'BatBuocDoiMatKhau' => false,
                    'TrangThaiMatKhau'  => 'ACTIVE',
                    'LanDangNhapDau'    => $now,
                    'NgayDoiMatKhau'    => $now,
                    'LanDangNhapCuoi'   => null,
                    'NgayKhoa'          => null,
                ]
            );

            // 2. Tạo hoặc cập nhật Sinh viên
            SinhVien::updateOrCreate(
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
                    'MaLop'           => $info['MaLop'],
                ]
            );

            // 3. Đưa vào Danh sách đủ điều kiện làm khóa luận tốt nghiệp
            $hocKys = [
                'HK2627_1' => '261',
                'HK2425_1' => '241',
            ];
            foreach ($hocKys as $hk => $short) {
                DanhSachSVDuDieuKien::updateOrCreate(
                    [
                        'MaSV'    => $mssv,
                        'MaHocKy' => $hk,
                    ],
                    [
                        'MaDSDK'       => "DK_{$mssv}_{$short}",
                        'NgayXetDuyet' => $now->toDateString(),
                        'TrangThai'    => 'Đủ điều kiện',
                        'GhiChu'       => 'Đủ điều kiện làm khóa luận tốt nghiệp',
                        'DieuKien'     => "Tín chỉ: {$info['TinChi']} | ĐTB: {$info['DiemTichLuy']}",
                    ]
                );
            }

            echo "Da tao/cap nhat sinh vien {$mssv} - {$info['HoTen']} thanh cong!\n";
        }
    }
}
