<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;

class CreateThreeStudentsCNPMSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('Huit@2026');

        $students = [
            '2001210083' => [
                'HoTen'       => 'Nguyễn Văn An',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-05-15',
                'SoDienThoai' => '0901234083',
                'DiemTichLuy' => 3.52,
                'TinChi'      => 128,
            ],
            '2001210084' => [
                'HoTen'       => 'Trần Thị Mai',
                'GioiTinh'    => 'Nữ',
                'NgaySinh'    => '2003-08-20',
                'SoDienThoai' => '0901234084',
                'DiemTichLuy' => 3.65,
                'TinChi'      => 130,
            ],
            '2001210085' => [
                'HoTen'       => 'Lê Quốc Bảo',
                'GioiTinh'    => 'Nam',
                'NgaySinh'    => '2003-11-10',
                'SoDienThoai' => '0901234085',
                'DiemTichLuy' => 3.48,
                'TinChi'      => 126,
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
                    'MaNganh'         => '7480103', // Kỹ thuật Phần mềm (thuộc Bộ môn CNPM)
                    'MaLop'           => '12DHKPM01', // Lớp chuyên ngành Kỹ thuật Phần mềm
                ]
            );

            // 3. Đưa vào Danh sách đủ điều kiện làm khóa luận tốt nghiệp (HK hiện tại và HK sắp tới)
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
                        'MaDSDK'       => "DK_{$mssv}_{$short}", // <= 20 ký tự
                        'NgayXetDuyet' => $now->toDateString(),
                        'TrangThai'    => 'Đủ điều kiện',
                        'GhiChu'       => 'Sinh viên Kỹ thuật Phần mềm (Bộ môn CNPM)',
                        'DieuKien'     => "Tín chỉ: {$info['TinChi']}/115 | ĐTB: {$info['DiemTichLuy']}/2.0",
                    ]
                );
            }
        }
    }
}
