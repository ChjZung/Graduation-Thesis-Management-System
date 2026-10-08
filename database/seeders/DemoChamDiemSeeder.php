<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\GiangVien;
use App\Models\TaiKhoan;

class DemoChamDiemSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // 1. Đảm bảo có Học kỳ đào tạo
        $maHocKy = 'HK2627_2';
        DB::table('HocKy')->updateOrInsert(
            ['MaHocKy' => $maHocKy],
            [
                'TenHocKy' => 'Học kỳ 2 — Năm học 2026–2027',
                'NamHoc' => '2026-2027',
                'NgayDiHoc' => '2026-02-15',
                'NgayKetThuc' => '2026-06-30',
                'TrangThai' => 'Đang diễn ra',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 1b. Đảm bảo có Học phần KLCN và KLKS
        DB::table('HocPhan')->updateOrInsert(
            ['MaHocPhan' => 'HP_KLCN'],
            ['TenHocPhan' => 'Khóa luận cử nhân', 'SoTinChi' => 10, 'TrangThai' => 1, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('HocPhan')->updateOrInsert(
            ['MaHocPhan' => 'HP_KLKS'],
            ['TenHocPhan' => 'Khóa luận kỹ sư', 'SoTinChi' => 10, 'TrangThai' => 1, 'created_at' => $now, 'updated_at' => $now]
        );

        // 2. Đảm bảo tài khoản GV001 - GV005 hoạt động với mật khẩu 123456
        $passwordHash = Hash::make('123456');
        for ($i = 1; $i <= 5; $i++) {
            $code = 'GV' . str_pad($i, 3, '0', STR_PAD_LEFT);
            DB::table('TaiKhoan')->where('TenDangNhap', $code)->update([
                'MatKhau' => $passwordHash,
                'TrangThai' => 1,
                'SoLanDangNhapSai' => 0,
                'BatBuocDoiMatKhau' => 0,
                'TrangThaiMatKhau' => 'ACTIVE',
            ]);
        }

        // 3. Tạo Hội đồng bảo vệ HD_CNTT_01
        $maHoiDong = 'HD_CNTT_01';
        DB::table('HoiDong')->updateOrInsert(
            ['MaHoiDong' => $maHoiDong],
            [
                'TenHoiDong' => 'Hội đồng Chuyên ngành Công nghệ phần mềm 01',
                'ThoiGianBatDau' => Carbon::now()->addDays(2)->setTime(8, 0),
                'ThoiGianKetThuc' => Carbon::now()->addDays(2)->setTime(17, 0),
                'DiaDiem' => 'Phòng B.304 (Tòa nhà B - 140 Lê Trọng Tấn)',
                'TrangThai' => 'Đang diễn ra',
                'GhiChu' => 'Hội đồng chấm khóa luận tốt nghiệp đợt 1 HK2 2026-2027',
                'MaHocKy' => $maHocKy,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 4. Phân công 5 thành viên Hội đồng
        $thanhViens = [
            ['MaGV' => 'GV001', 'VaiTro' => 'Chủ tịch'],
            ['MaGV' => 'GV002', 'VaiTro' => 'Thư ký'],
            ['MaGV' => 'GV003', 'VaiTro' => 'Phản biện'],
            ['MaGV' => 'GV004', 'VaiTro' => 'Thành viên'],
            ['MaGV' => 'GV005', 'VaiTro' => 'Thành viên'],
        ];
        foreach ($thanhViens as $tv) {
            DB::table('ThanhVienHoiDong')->updateOrInsert(
                ['MaHoiDong' => $maHoiDong, 'MaGV' => $tv['MaGV']],
                ['VaiTro' => $tv['VaiTro'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // 5. Đảm bảo sinh viên mẫu
        $svList = SinhVien::take(6)->get();
        if ($svList->count() < 6) {
            // Lấy thêm từ bảng
            $svIds = SinhVien::pluck('MaSV')->take(6)->toArray();
        } else {
            $svIds = $svList->pluck('MaSV')->toArray();
        }

        $svNhom1 = array_slice($svIds, 0, 3);
        $svNhom2 = array_slice($svIds, 3, 3);

        // 6. Tạo 2 Đề tài: 1 KLCN (Cử nhân) & 1 KLKS (Kỹ sư)
        $maDeTai1 = 'DT_KLCN_01';
        DB::table('DeTai')->updateOrInsert(
            ['MaDeTai' => $maDeTai1],
            [
                'TenDeTai' => 'Xây dựng hệ thống quản lý chuỗi cung ứng nông sản ứng dụng công nghệ Web và RESTful API',
                'MoTa' => 'Hệ thống quản lý truy xuất nguồn gốc nông sản, kết nối nông dân và nhà phân phối',
                'LinhVuc' => 'Web App & API',
                'SoLuongSinhVienToiDa' => 3,
                'HocPhan' => 'Khóa luận Cử nhân',
                'MaHocPhan' => 'HP_KLCN',
                'TrangThai' => 'Đã duyệt',
                'MaGV' => 'GV006',
                'MaHocKy' => $maHocKy,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $maDeTai2 = 'DT_KLKS_01';
        DB::table('DeTai')->updateOrInsert(
            ['MaDeTai' => $maDeTai2],
            [
                'TenDeTai' => 'Nghiên cứu và phát triển ứng dụng di động nhận diện bệnh cây trồng ứng dụng Trí tuệ nhân tạo (AI/Deep Learning)',
                'MoTa' => 'Ứng dụng mobile đa nền tảng kết hợp mô hình học sâu CNN để chẩn đoán sâu bệnh qua ảnh chụp lá cây',
                'LinhVuc' => 'Mobile App & AI',
                'SoLuongSinhVienToiDa' => 3,
                'HocPhan' => 'Khóa luận Kỹ sư',
                'MaHocPhan' => 'HP_KLKS',
                'TrangThai' => 'Đã duyệt',
                'MaGV' => 'GV007',
                'MaHocKy' => $maHocKy,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 7. Tạo 2 Nhóm sinh viên
        $maNhom1 = 'NHOM_KLCN_01';
        DB::table('Nhom')->updateOrInsert(
            ['MaNhom' => $maNhom1],
            [
                'TenNhom' => 'Nhóm 01 (Cử nhân) — Quản lý chuỗi cung ứng',
                'TrangThai' => 'Đang thực hiện',
                'MaHocKy' => $maHocKy,
                'MaHocPhan' => 'HP_KLCN',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach ($svNhom1 as $idx => $sId) {
            DB::table('ThanhVienNhom')->updateOrInsert(
                ['MaNhom' => $maNhom1, 'MaSV' => $sId],
                [
                    'VaiTro' => ($idx === 0) ? 'Trưởng nhóm' : 'Thành viên',
                    'TrangThai' => 'da_tham_gia',
                    'NgayThamGia' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('PhieuDangKy')->updateOrInsert(
            ['MaNhom' => $maNhom1, 'MaDeTai' => $maDeTai1],
            [
                'MaDangKy' => 'PDK_KLCN_01',
                'TrangThai' => 'Đã duyệt',
                'NgayDangKy' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $maNhom2 = 'NHOM_KLKS_01';
        DB::table('Nhom')->updateOrInsert(
            ['MaNhom' => $maNhom2],
            [
                'TenNhom' => 'Nhóm 02 (Kỹ sư) — AI Nhận diện bệnh cây trồng',
                'TrangThai' => 'Đang thực hiện',
                'MaHocKy' => $maHocKy,
                'MaHocPhan' => 'HP_KLKS',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach ($svNhom2 as $idx => $sId) {
            DB::table('ThanhVienNhom')->updateOrInsert(
                ['MaNhom' => $maNhom2, 'MaSV' => $sId],
                [
                    'VaiTro' => ($idx === 0) ? 'Trưởng nhóm' : 'Thành viên',
                    'TrangThai' => 'da_tham_gia',
                    'NgayThamGia' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('PhieuDangKy')->updateOrInsert(
            ['MaNhom' => $maNhom2, 'MaDeTai' => $maDeTai2],
            [
                'MaDangKy' => 'PDK_KLKS_01',
                'TrangThai' => 'Đã duyệt',
                'NgayDangKy' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 8. Tạo 2 Hồ sơ bảo vệ và xếp vào Hội đồng HD_CNTT_01
        $maHoSo1 = 'HS_KLCN_01';
        DB::table('HoSoBaoVe')->updateOrInsert(
            ['MaHoSo' => $maHoSo1],
            [
                'MaNhom' => $maNhom1,
                'MaDeTai' => $maDeTai1,
                'MaHoiDong' => $maHoiDong,
                'PhongBaoVe' => 'Phòng B.304',
                'ThoiGianBaoVe' => Carbon::now()->addDays(2)->setTime(8, 30),
                'TrangThai' => 'Đã phân công',
                'XacNhanGVHD' => 'Đã duyệt',
                'NgayNop' => Carbon::now()->subDays(5),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $maHoSo2 = 'HS_KLKS_01';
        DB::table('HoSoBaoVe')->updateOrInsert(
            ['MaHoSo' => $maHoSo2],
            [
                'MaNhom' => $maNhom2,
                'MaDeTai' => $maDeTai2,
                'MaHoiDong' => $maHoiDong,
                'PhongBaoVe' => 'Phòng B.304',
                'ThoiGianBaoVe' => Carbon::now()->addDays(2)->setTime(10, 0),
                'TrangThai' => 'Đã phân công',
                'XacNhanGVHD' => 'Đã duyệt',
                'NgayNop' => Carbon::now()->subDays(5),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info("Đã tạo thành công dữ liệu Hội đồng {$maHoiDong} với 2 nhóm bảo vệ (Mẫu KLCN và Mẫu KLKS)!");
    }
}
