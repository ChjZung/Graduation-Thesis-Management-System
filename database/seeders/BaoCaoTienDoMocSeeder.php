<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;

class BaoCaoTienDoMocSeeder extends Seeder
{
    public function run(): void
    {
        $plan = KeHoachKhoaLuan::where('TrangThai', 'Đang thực hiện')
            ->orWhere('TrangThai', 'ĐÃ CÔNG BỐ')
            ->first() ?? KeHoachKhoaLuan::latest()->first();

        if (!$plan) {
            return;
        }

        $mocs = [
            1 => [
                'MaMoc' => 'MOC_TIENDO_1',
                'TenMoc' => 'Báo cáo tiến độ GĐ 1: Nhận đề cương & Thiết kế CSDL',
                'NgayBatDau' => '2026-09-15',
                'NgayKetThuc' => '2026-09-18',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 1 [BAO_CAO_TIEN_DO_1]',
            ],
            2 => [
                'MaMoc' => 'MOC_TIENDO_2',
                'TenMoc' => 'Báo cáo tiến độ GĐ 2: Thiết kế hệ thống & Xây dựng chức năng',
                'NgayBatDau' => '2026-09-28',
                'NgayKetThuc' => '2026-10-16',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 2 [BAO_CAO_TIEN_DO_2]',
            ],
            3 => [
                'MaMoc' => 'MOC_TIENDO_3',
                'TenMoc' => 'Báo cáo tiến độ GĐ 3: Kiểm thử hoàn thiện & Báo cáo dự thảo',
                'NgayBatDau' => '2026-10-26',
                'NgayKetThuc' => '2026-11-06',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 3 [BAO_CAO_TIEN_DO_3]',
            ],
            4 => [
                'MaMoc' => 'MOC_TIENDO_4',
                'TenMoc' => 'Báo cáo tiến độ GĐ 4: Hoàn thiện hồ sơ bảo vệ',
                'NgayBatDau' => '2026-11-16',
                'NgayKetThuc' => '2026-11-27',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 4 [BAO_CAO_TIEN_DO_4]',
            ],
            5 => [
                'MaMoc' => 'MOC_TIENDO_5',
                'TenMoc' => 'Báo cáo tiến độ GĐ 5: Tiến hành bảo vệ trước Hội đồng',
                'NgayBatDau' => '2026-12-07',
                'NgayKetThuc' => '2026-12-18',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 5 [BAO_CAO_TIEN_DO_5]',
            ],
            6 => [
                'MaMoc' => 'MOC_TIENDO_6',
                'TenMoc' => 'Báo cáo tiến độ GĐ 6: Nhận kết quả & Nộp lại kết quả hoàn chỉnh',
                'NgayBatDau' => '2026-12-21',
                'NgayKetThuc' => '2027-01-05',
                'MoTa' => 'Mốc nộp báo cáo tiến độ đợt 6 [BAO_CAO_TIEN_DO_6]',
            ],
        ];

        foreach ($mocs as $lan => $data) {
            MocThoiGianKhoaLuan::updateOrCreate(
                [
                    'MakeHoach' => $plan->MakeHoach,
                    'MaMoc' => $data['MaMoc']
                ],
                [
                    'TenMoc' => $data['TenMoc'],
                    'NgayBatDau' => $data['NgayBatDau'],
                    'NgayKetThuc' => $data['NgayKetThuc'],
                    'MoTa' => $data['MoTa'],
                ]
            );
        }
    }
}
