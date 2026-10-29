<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;

class CreateThreeStudentsForGV00000023Seeder extends Seeder
{
    /**
     * Tạo 3 sinh viên phù hợp với Bộ môn CNPM (Công nghệ Phần mềm) của giảng viên GV00000023 (TS. Lê Hoàng Cường).
     * Ngành: 7480103 - Kỹ thuật Phần mềm.
     * Lớp: 12DHKPM01 (Khóa 2021-2025).
     */
    public function run()
    {
        DB::transaction(function () {
            $now = Carbon::now();
            $defaultPassword = 'Huit@2026';
            $passwordHash = Hash::make($defaultPassword);

            $students = [
                '2001210089' => [
                    'HoTen'       => 'Bùi Hoàng Nam',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-04-14',
                    'SoDienThoai' => '0901234089',
                    'DiemTichLuy' => 3.58,
                    'TinChi'      => 130,
                ],
                '2001210090' => [
                    'HoTen'       => 'Nguyễn Thị Thùy Linh',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-09-22',
                    'SoDienThoai' => '0901234090',
                    'DiemTichLuy' => 3.65,
                    'TinChi'      => 133,
                ],
                '2001210091' => [
                    'HoTen'       => 'Đỗ Minh Trí',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-12-05',
                    'SoDienThoai' => '0901234091',
                    'DiemTichLuy' => 3.42,
                    'TinChi'      => 127,
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
                            'GhiChu'       => 'Sinh viên Kỹ thuật Phần mềm (Bộ môn CNPM)',
                            'DieuKien'     => "Tín chỉ: {$info['TinChi']}/115 | ĐTB: {$info['DiemTichLuy']}/2.0",
                        ]
                    );
                }
            }
        });
    }
}
