<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;

class CreateNineStudentsForGV00000023Seeder extends Seeder
{
    /**
     * Tạo 9 sinh viên phù hợp với Bộ môn CNPM (Công nghệ Phần mềm) của giảng viên GV00000023 (TS. Lê Hoàng Cường).
     * Ngành: 7480103 - Kỹ thuật Phần mềm.
     * Lớp: 12DHKPM01 (Khóa 2021-2025).
     * Mật khẩu mặc định: Huit@2026
     */
    public function run()
    {
        DB::transaction(function () {
            $now = Carbon::now();
            $defaultPassword = 'Huit@2026';
            $passwordHash = Hash::make($defaultPassword);

            $students = [
                '2001210092' => [
                    'HoTen'       => 'Trần Gia Huy',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-03-12',
                    'SoDienThoai' => '0901234092',
                    'DiemTichLuy' => 3.52,
                    'TinChi'      => 131,
                ],
                '2001210093' => [
                    'HoTen'       => 'Lê Thị Ngọc Ánh',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-05-18',
                    'SoDienThoai' => '0901234093',
                    'DiemTichLuy' => 3.68,
                    'TinChi'      => 134,
                ],
                '2001210094' => [
                    'HoTen'       => 'Phạm Hoàng Long',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-07-25',
                    'SoDienThoai' => '0901234094',
                    'DiemTichLuy' => 3.45,
                    'TinChi'      => 128,
                ],
                '2001210095' => [
                    'HoTen'       => 'Nguyễn Khánh Linh',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-09-10',
                    'SoDienThoai' => '0901234095',
                    'DiemTichLuy' => 3.75,
                    'TinChi'      => 136,
                ],
                '2001210096' => [
                    'HoTen'       => 'Võ Minh Khang',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-11-04',
                    'SoDienThoai' => '0901234096',
                    'DiemTichLuy' => 3.38,
                    'TinChi'      => 126,
                ],
                '2001210097' => [
                    'HoTen'       => 'Đặng Phương Thảo',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-02-28',
                    'SoDienThoai' => '0901234097',
                    'DiemTichLuy' => 3.61,
                    'TinChi'      => 132,
                ],
                '2001210098' => [
                    'HoTen'       => 'Hoàng Quốc Việt',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-06-15',
                    'SoDienThoai' => '0901234098',
                    'DiemTichLuy' => 3.49,
                    'TinChi'      => 129,
                ],
                '2001210099' => [
                    'HoTen'       => 'Trịnh Mỹ Duyên',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-08-20',
                    'SoDienThoai' => '0901234099',
                    'DiemTichLuy' => 3.55,
                    'TinChi'      => 130,
                ],
                '2001210100' => [
                    'HoTen'       => 'Đỗ Nhật Nam',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-10-30',
                    'SoDienThoai' => '0901234100',
                    'DiemTichLuy' => 3.70,
                    'TinChi'      => 135,
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
                    'HK2627_1' => '26',
                    'HK2425_1' => '24',
                ];
                foreach ($hocKys as $hk => $suffix) {
                    DanhSachSVDuDieuKien::updateOrCreate(
                        [
                            'MaSV'    => $mssv,
                            'MaHocKy' => $hk,
                        ],
                        [
                            'MaDSDK'       => "DK_{$mssv}_{$suffix}",
                            'NgayXetDuyet' => $now->toDateString(),
                            'TrangThai'    => 'Đủ điều kiện',
                            'GhiChu'       => 'Sinh viên Kỹ thuật Phần mềm (Bộ môn CNPM)',
                            'DieuKien'     => "Tín chỉ: {$info['TinChi']} | ĐTB: {$info['DiemTichLuy']}",
                        ]
                    );
                }
            }
        });
    }
}
