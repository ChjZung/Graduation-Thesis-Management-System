<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AddTestStudentsSameMajorSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('Huit@2026');

        $students = [
            '2001210060' => ['HoTen' => 'Bùi Gia Bảo', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-01-12', 'SoDienThoai' => '0978001060'],
            '2001210061' => ['HoTen' => 'Nguyễn Minh Châu', 'GioiTinh' => 'Nữ', 'NgaySinh' => '2003-02-18', 'SoDienThoai' => '0978001061'],
            '2001210062' => ['HoTen' => 'Đặng Quốc Dũng', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-03-24', 'SoDienThoai' => '0978001062'],
            '2001210063' => ['HoTen' => 'Lê Hoàng Giang', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-04-15', 'SoDienThoai' => '0978001063'],
            '2001210064' => ['HoTen' => 'Vũ Thúy Hạnh', 'GioiTinh' => 'Nữ', 'NgaySinh' => '2003-05-20', 'SoDienThoai' => '0978001064'],
            '2001210065' => ['HoTen' => 'Phạm Quang Huy', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-06-11', 'SoDienThoai' => '0978001065'],
            '2001210066' => ['HoTen' => 'Đỗ Tuấn Kiệt', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-07-09', 'SoDienThoai' => '0978001066'],
            '2001210067' => ['HoTen' => 'Trịnh Mai Linh', 'GioiTinh' => 'Nữ', 'NgaySinh' => '2003-08-30', 'SoDienThoai' => '0978001067'],
            '2001210068' => ['HoTen' => 'Ngô Minh Nhật', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-09-17', 'SoDienThoai' => '0978001068'],
            '2001210069' => ['HoTen' => 'Hoàng Yến Nhi', 'GioiTinh' => 'Nữ', 'NgaySinh' => '2003-10-05', 'SoDienThoai' => '0978001069'],
            '2001210070' => ['HoTen' => 'Phan Thanh Phong', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-11-22', 'SoDienThoai' => '0978001070'],
            '2001210071' => ['HoTen' => 'Lương Hồng Phúc', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-12-14', 'SoDienThoai' => '0978001071'],
            '2001210072' => ['HoTen' => 'Võ Hữu Quân', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-01-28', 'SoDienThoai' => '0978001072'],
            '2001210073' => ['HoTen' => 'Mai Ngọc Sơn', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-02-25', 'SoDienThoai' => '0978001073'],
            '2001210074' => ['HoTen' => 'Đinh Phương Thảo', 'GioiTinh' => 'Nữ', 'NgaySinh' => '2003-03-19', 'SoDienThoai' => '0978001074'],
            '2001210075' => ['HoTen' => 'Hà Đức Trọng', 'GioiTinh' => 'Nam', 'NgaySinh' => '2003-04-08', 'SoDienThoai' => '0978001075'],
        ];

        $taiKhoans = [];
        $sinhViens = [];
        $dsdk = [];

        foreach ($students as $mssv => $info) {
            $taiKhoans[] = [
                'MaTK'              => $mssv,
                'TenDangNhap'       => $mssv,
                'MatKhau'           => $passwordHash,
                'MaVaiTro'          => 'VT03',
                'TrangThai'         => 1,
                'SoLanDangNhapSai'  => 0,
                'BatBuocDoiMatKhau' => 1,
                'TrangThaiMatKhau'  => 'INITIAL',
                'LanDangNhapDau'    => null,
                'NgayDoiMatKhau'    => null,
                'LanDangNhapCuoi'   => null,
                'NgayKhoa'          => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            $sinhViens[] = [
                'MaSV'            => $mssv,
                'MaTK'            => $mssv,
                'HoTen'           => $info['HoTen'],
                'NgaySinh'        => $info['NgaySinh'],
                'GioiTinh'        => $info['GioiTinh'],
                'Email'           => "{$mssv}@st.huit.edu.vn",
                'SoDienThoai'     => $info['SoDienThoai'],
                'NgayNhapHoc'     => '2021-09-05',
                'KhoaHoc'         => '2021-2025',
                'SoTinChiTichLuy' => 125,
                'DiemTichLuy'     => 3.25,
                'TrangThai'       => 'Đang học',
                'MaKhoa'          => 'CNTT',
                'MaNganh'         => '7480201', // Công nghệ Thông tin
                'MaLop'           => '12DHCNTT01',
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            $dsdk[] = [
                'MaDSDK'       => "DK_{$mssv}_HK2627",
                'NgayXetDuyet' => '2026-08-10',
                'TrangThai'    => 'Đủ điều kiện',
                'GhiChu'       => 'Đủ điều kiện làm khóa luận tốt nghiệp - Đã công bố',
                'DieuKien'     => 'Tín chỉ: 125/115 | ĐTB: 3.25/2',
                'MaSV'         => $mssv,
                'MaHocKy'      => 'HK2627_1',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        DB::table('TaiKhoan')->upsert($taiKhoans, ['MaTK'], [
            'TenDangNhap', 'MatKhau', 'MaVaiTro', 'TrangThai', 'SoLanDangNhapSai',
            'BatBuocDoiMatKhau', 'TrangThaiMatKhau', 'LanDangNhapDau', 'NgayDoiMatKhau', 'updated_at'
        ]);

        DB::table('SinhVien')->upsert($sinhViens, ['MaSV'], [
            'MaTK', 'HoTen', 'NgaySinh', 'GioiTinh', 'Email', 'SoDienThoai',
            'NgayNhapHoc', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai',
            'MaKhoa', 'MaNganh', 'MaLop', 'updated_at'
        ]);

        DB::table('DanhSachSVDuDieuKien')->upsert($dsdk, ['MaDSDK'], [
            'NgayXetDuyet', 'TrangThai', 'GhiChu', 'DieuKien', 'MaSV', 'MaHocKy', 'updated_at'
        ]);
    }
}
