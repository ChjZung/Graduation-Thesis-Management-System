<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoKeHoachSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Kế hoạch Khóa luận HK1 2026-2027
        $maKH = 'KH_2026_01';
        DB::table('ke_hoach_khoa_luans')->updateOrInsert(
            ['MaKeHoach' => $maKH],
            [
                'MaHocKy'     => 'HK01',
                'NamHoc'      => '2026-2027',
                'MaKhoa'      => 'K01',
                'MaGVu'       => 'GVU01',
                'NguoiLap'    => 'GVU01',
                'TenKeHoach'  => 'Kế hoạch Khóa luận HK1 2026-2027',
                'NoiDung'     => 'Kế hoạch khóa luận tốt nghiệp chính thức Học kỳ 1 Năm học 2026-2027 Khoa CNTT.',
                'NgayBatDau'  => '2026-09-01',
                'NgayKetThuc' => '2026-12-10',
                'TrangThai'   => 'ĐÃ CÔNG BỐ',
                'NgayTao'     => '2026-08-25',
                'NgayCongBo'  => '2026-08-25',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // 2. Tạo 12 Giai đoạn / Mốc thời gian quy trình
        $mocs = \App\Services\PlanPhaseService::getDefaultPhases('2026-09-01');
        foreach ($mocs as $idx => $m) {
            $maMoc = 'MOC_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
            DB::table('moc_thoi_gian_khoa_luans')->updateOrInsert(
                ['MaMoc' => $maMoc],
                [
                    'MaKeHoach'       => $maKH,
                    'LoaiGiaiDoan'    => $m['LoaiGiaiDoan'],
                    'ThuTu'           => $m['ThuTu'],
                    'TenMoc'          => $m['TenMoc'],
                    'DoiTuongThucHien'=> $m['DoiTuongThucHien'],
                    'NgayBatDau'      => $m['NgayBatDau'],
                    'NgayKetThuc'     => $m['NgayKetThuc'],
                    'MoTa'            => $m['MoTa'],
                    'BatBuoc'         => $m['BatBuoc'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        // 3. Tạo 3 Đề Tài demo
        $deTais = [
            [
                'MaDeTai'               => 'DT_DEMO_01',
                'MaGV'                  => 'GV01',
                'TenDeTai'              => 'Xây dựng hệ thống quản lý khóa luận',
                'MoTa'                  => 'Hệ thống quản lý quy trình khóa luận tốt nghiệp theo mốc thời gian.',
                'YeuCau'                => 'Laravel, MySQL, Bootstrap 5, REST API.',
                'SoLuongSinhVienToiDa'  => 3,
                'MaHocKy'               => 'HK01',
                'TrangThai'             => 'Đã duyệt',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'MaDeTai'               => 'DT_DEMO_02',
                'MaGV'                  => 'GV02',
                'TenDeTai'              => 'Xây dựng hệ thống quản lý thư viện',
                'MoTa'                  => 'Hệ thống quản lý sách, mượn trả và tra cứu trực tuyến.',
                'YeuCau'                => 'PHP, Mysql, VueJS.',
                'SoLuongSinhVienToiDa'  => 3,
                'MaHocKy'               => 'HK01',
                'TrangThai'             => 'Đã duyệt',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'MaDeTai'               => 'DT_DEMO_03',
                'MaGV'                  => 'GV03',
                'TenDeTai'              => 'Xây dựng hệ thống quản lý thực tập',
                'MaHocKy'               => 'HK01',
                'MoTa'                  => 'Hệ thống kết nối doanh nghiệp và quản lý sinh viên thực tập.',
                'YeuCau'                => 'Laravel, PostgreSQL, Tailwind.',
                'SoLuongSinhVienToiDa'  => 4,
                'TrangThai'             => 'Đã duyệt',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
        ];

        foreach ($deTais as $dt) {
            DB::table('de_tais')->updateOrInsert(['MaDeTai' => $dt['MaDeTai']], $dt);
        }

        // 4. Tạo 3 Nhóm thực hiện (TEAM001, TEAM002, TEAM003)
        $nhoms = [
            [
                'MaNhom'        => 'TEAM001',
                'MaDeTai'       => 'DT_DEMO_01',
                'MaTruongNhom'  => '2001230104',
                'TenNhom'       => 'Nhóm 01 - Hệ thống Khóa luận',
                'TrangThai'     => 'Đã duyệt',
                'NgayTao'       => '2026-09-06',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'MaNhom'        => 'TEAM002',
                'MaDeTai'       => 'DT_DEMO_02',
                'MaTruongNhom'  => '2001230105',
                'TenNhom'       => 'Nhóm 02 - Hệ thống Thư viện',
                'TrangThai'     => 'Đã duyệt',
                'NgayTao'       => '2026-09-07',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'MaNhom'        => 'TEAM003',
                'MaDeTai'       => 'DT_DEMO_03',
                'MaTruongNhom'  => '2001230106',
                'TenNhom'       => 'Nhóm 03 - Hệ thống Thực tập',
                'TrangThai'     => 'Đã duyệt',
                'NgayTao'       => '2026-09-08',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        foreach ($nhoms as $n) {
            DB::table('nhoms')->updateOrInsert(['MaNhom' => $n['MaNhom']], $n);
        }

        // 5. Đăng ký đề tài & phân công GVHD
        $dangKys = [
            [
                'MaDangKy'     => 'DK_DEMO_01',
                'MaNhom'       => 'TEAM001',
                'MaDeTai'      => 'DT_DEMO_01',
                'MaGVHuongDan' => 'GV01',
                'NgayDangKy'   => '2026-09-06',
                'TrangThai'    => 'Đã duyệt',
                'NgayDuyet'    => '2026-09-15',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'MaDangKy'     => 'DK_DEMO_02',
                'MaNhom'       => 'TEAM002',
                'MaDeTai'      => 'DT_DEMO_02',
                'MaGVHuongDan' => 'GV02',
                'NgayDangKy'   => '2026-09-07',
                'TrangThai'    => 'Đã duyệt',
                'NgayDuyet'    => '2026-09-15',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'MaDangKy'     => 'DK_DEMO_03',
                'MaNhom'       => 'TEAM003',
                'MaDeTai'      => 'DT_DEMO_03',
                'MaGVHuongDan' => 'GV03',
                'NgayDangKy'   => '2026-09-08',
                'TrangThai'    => 'Đã duyệt',
                'NgayDuyet'    => '2026-09-15',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ];

        foreach ($dangKys as $dk) {
            DB::table('dang_ky_de_tais')->updateOrInsert(['MaDangKy' => $dk['MaDangKy']], $dk);
        }

        // 6. Báo cáo tiến độ cho các nhóm
        // TEAM001: Đúng tiến độ (Đã nộp đủ 3 lần báo cáo)
        for ($lan = 1; $lan <= 3; $lan++) {
            DB::table('bao_cao_tien_dos')->updateOrInsert(
                ['MaBaoCao' => "BC_T1_{$lan}"],
                [
                    'MaNhom'        => 'TEAM001',
                    'LanBaoCao'     => $lan,
                    'TieuDe'        => "Báo cáo tiến độ lần {$lan} - TEAM001",
                    'NoiDungBaoCao' => "Nội dung hoàn thành mốc báo cáo lần {$lan} của nhóm TEAM001.",
                    'NgayNop'       => "2026-10-0" . ($lan * 3),
                    'TenFile'       => "BaoCao_Lan{$lan}_TEAM001.pdf",
                    'DuongDanFile'  => "uploads/reports/BaoCao_Lan{$lan}_TEAM001.pdf",
                    'TrangThai'     => 'Đạt',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        // TEAM002: Sắp đến hạn (Đã nộp lần 1, sắp đến hạn lần 2)
        DB::table('bao_cao_tien_dos')->updateOrInsert(
            ['MaBaoCao' => 'BC_T2_1'],
            [
                'MaNhom'        => 'TEAM002',
                'LanBaoCao'     => 1,
                'TieuDe'        => 'Báo cáo tiến độ lần 1 - TEAM002',
                'NoiDungBaoCao' => 'Nội dung phân tích yêu cầu nhóm TEAM002.',
                'NgayNop'       => '2026-10-09',
                'TenFile'       => 'BaoCao_Lan1_TEAM002.pdf',
                'DuongDanFile'  => 'uploads/reports/BaoCao_Lan1_TEAM002.pdf',
                'TrangThai'     => 'Đạt',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );

        // TEAM003: Quá hạn (Chưa nộp báo cáo nào dù đã qua hạn lần 1)
        // (Không thêm báo cáo cho TEAM003 để mô phỏng trạng thái trễ hạn)
    }
}
