<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RolesAndAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Chuẩn hóa 5 role
        $roles = [
            ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ'],
            ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên'],
            ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên'],
            ['MaVaiTro' => 'VT04', 'TenVaiTro' => 'Trưởng bộ môn'],
            ['MaVaiTro' => 'VT05', 'TenVaiTro' => 'Trưởng khoa'],
        ];
        foreach ($roles as $r) {
            DB::table('VaiTro')->updateOrInsert(
                ['MaVaiTro' => $r['MaVaiTro']],
                ['TenVaiTro' => $r['TenVaiTro'], 'updated_at' => $now]
            );
        }

        // 2. Tài khoản Giáo vụ: admin & giaovu01
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'admin'],
            [
                'MaTK' => 'TK_ADMIN',
                'MaVaiTro' => 'VT01',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'giaovu01'],
            [
                'MaTK' => 'TK_GIAOVU01',
                'MaVaiTro' => 'VT01',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('GiaoVu')->updateOrInsert(
            ['MaGVu' => 'GVU01'],
            [
                'MaTK' => 'TK_ADMIN',
                'HoTen' => 'Quản trị viên Giáo vụ',
                'Email' => 'giaovu@huit.edu.vn',
                'SoDienThoai' => '0908123456',
                'ChucVu' => 'Giáo vụ Khoa',
                'MaKhoa' => 'CNTT',
                'updated_at' => $now
            ]
        );

        // 3. Tài khoản Trưởng bộ môn CNPM: TBM_CNTT_CNPM_001 & tbm01
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'TBM_CNTT_CNPM_001'],
            [
                'MaTK' => 'TK_TBM_CNPM01',
                'MaVaiTro' => 'VT04',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'tbm01'],
            [
                'MaTK' => 'TK_TBM01',
                'MaVaiTro' => 'VT04',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('GiangVien')->updateOrInsert(
            ['MaGV' => 'GV_TBM01'],
            [
                'MaTK' => 'TK_TBM_CNPM01',
                'HoTen' => 'TS. Nguyễn Văn Trưởng Bộ Môn CNPM',
                'Email' => 'tbm_cnpm@huit.edu.vn',
                'SoDienThoai' => '0912345678',
                'HocVi' => 'Tiến sĩ',
                'MaBoMon' => 'CNPM',
                'TrangThai' => 'Đang công tác',
                'updated_at' => $now
            ]
        );
        DB::table('BoMon')->where('MaBoMon', 'CNPM')->update([
            'TruongBoMon' => 'TS. Nguyễn Văn Trưởng Bộ Môn CNPM'
        ]);

        // 3.1. Tài khoản Trưởng bộ môn HTTT: TBM_CNTT_HTTT_001
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'TBM_CNTT_HTTT_001'],
            [
                'MaTK' => 'TK_TBM_HTTT01',
                'MaVaiTro' => 'VT04',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('GiangVien')->updateOrInsert(
            ['MaGV' => 'GV_TBM_HTTT01'],
            [
                'MaTK' => 'TK_TBM_HTTT01',
                'HoTen' => 'TS. Lê Thị Trưởng Bộ Môn HTTT',
                'Email' => 'tbm_httt@huit.edu.vn',
                'SoDienThoai' => '0913345678',
                'HocVi' => 'Tiến sĩ',
                'MaBoMon' => 'HTTT',
                'TrangThai' => 'Đang công tác',
                'updated_at' => $now
            ]
        );
        DB::table('BoMon')->where('MaBoMon', 'HTTT')->update([
            'TruongBoMon' => 'TS. Lê Thị Trưởng Bộ Môn HTTT'
        ]);

        // 4. Tài khoản Trưởng khoa CNTT: TK_CNTT_001 & tk01
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'TK_CNTT_001'],
            [
                'MaTK' => 'TK_TK_CNTT01',
                'MaVaiTro' => 'VT05',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'tk01'],
            [
                'MaTK' => 'TK_TK01',
                'MaVaiTro' => 'VT05',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('GiangVien')->updateOrInsert(
            ['MaGV' => 'GV_TK01'],
            [
                'MaTK' => 'TK_TK_CNTT01',
                'HoTen' => 'PGS.TS. Trần Văn Trưởng Khoa CNTT',
                'Email' => 'tk_cntt@huit.edu.vn',
                'SoDienThoai' => '0987654321',
                'HocHam' => 'Phó Giáo sư',
                'HocVi' => 'Tiến sĩ',
                'MaBoMon' => 'CNPM',
                'TrangThai' => 'Đang công tác',
                'updated_at' => $now
            ]
        );
        DB::table('Khoa')->where('MaKhoa', 'CNTT')->update([
            'TruongKhoa' => 'PGS.TS. Trần Văn Trưởng Khoa CNTT'
        ]);

        // 5. Tài khoản Giảng viên gv01
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'gv01'],
            [
                'MaVaiTro' => 'VT02',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );

        // 6. Tài khoản Sinh viên sv01
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'sv01'],
            [
                'MaTK' => 'TK_SV01_ALIAS',
                'MaVaiTro' => 'VT03',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau' => 'ACTIVE',
                'LanDangNhapDau' => $now,
                'updated_at' => $now
            ]
        );
        DB::table('SinhVien')->updateOrInsert(
            ['MaSV' => 'SV01'],
            [
                'MaTK' => 'TK_SV01_ALIAS',
                'HoTen' => 'Nguyễn Văn Sinh Viên 01',
                'Email' => 'sv01@huit.edu.vn',
                'SoDienThoai' => '0901234567',
                'NgaySinh' => '2003-01-01',
                'GioiTinh' => 'Nam',
                'MaLop' => '12DHTH01',
                'MaKhoa' => 'CNTT',
                'MaNganh' => '7480201',
                'DiemTichLuy' => 3.2,
                'SoTinChiTichLuy' => 125,
                'TrangThai' => 'Đang học',
                'updated_at' => $now
            ]
        );
    }
}
