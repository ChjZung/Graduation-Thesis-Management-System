<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HocPhan;
use App\Models\DeTai;
use App\Models\Nhom;

class HocPhanSeeder extends Seeder
{
    public function run(): void
    {
        $hocPhans = [
            // 1. Học phần dùng chung (Khoa CNTT)
            [
                'MaHocPhan'   => 'HP_KLTN',
                'TenHocPhan'  => 'Khóa luận tốt nghiệp',
                'SoTinChi'    => 10,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Khóa luận',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Học phần khóa luận tốt nghiệp dùng chung cho sinh viên đủ điều kiện làm khóa luận.',
            ],
            [
                'MaHocPhan'   => 'HP_KLKS',
                'TenHocPhan'  => 'Khóa luận kỹ sư',
                'SoTinChi'    => 10,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Khóa luận',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Học phần khóa luận kỹ sư định hướng công nghệ ứng dụng chuyên sâu.',
            ],
            [
                'MaHocPhan'   => 'HP_DATN',
                'TenHocPhan'  => 'Đồ án tốt nghiệp',
                'SoTinChi'    => 4,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Đồ án',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Đồ án tốt nghiệp cử nhân theo định hướng chuyên ngành.',
            ],
            [
                'MaHocPhan'   => 'HP_DACN',
                'TenHocPhan'  => 'Đồ án chuyên ngành',
                'SoTinChi'    => 3,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Đồ án',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Đồ án phát triển ứng dụng chuyên ngành theo nhóm sinh viên.',
            ],

            // 2. Bộ môn Công nghệ phần mềm (CNPM)
            [
                'MaHocPhan'   => 'HP_CNPM',
                'TenHocPhan'  => 'Công nghệ phần mềm',
                'SoTinChi'    => 3,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => 'CNPM',
                'LoaiHocPhan' => 'Chuyên ngành',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Quy trình phát triển phần mềm, kiến trúc và mô hình phần mềm hiện đại.',
            ],
            [
                'MaHocPhan'   => 'HP_KTPM',
                'TenHocPhan'  => 'Kiểm thử phần mềm',
                'SoTinChi'    => 3,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => 'CNPM',
                'LoaiHocPhan' => 'Chuyên ngành',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Kỹ thuật kiểm thử phần mềm tự động hóa và đảm bảo chất lượng (QA/QC).',
            ],

            // 3. Bộ môn Hệ thống thông tin (HTTT)
            [
                'MaHocPhan'   => 'HP_PTTKHT',
                'TenHocPhan'  => 'Phân tích thiết kế hệ thống',
                'SoTinChi'    => 3,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => 'HTTT',
                'LoaiHocPhan' => 'Chuyên ngành',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Phương pháp luận phân tích yêu cầu nghiệp vụ và thiết kế kiến trúc hệ thống thông tin.',
            ],
            [
                'MaHocPhan'   => 'HP_HTTTQL',
                'TenHocPhan'  => 'Hệ thống thông tin quản lý',
                'SoTinChi'    => 3,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => 'HTTT',
                'LoaiHocPhan' => 'Chuyên ngành',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Ứng dụng hệ thống thông tin trong hoạch định nguồn lực và quản trị doanh nghiệp (ERP/CRM).',
            ],
        ];

        foreach ($hocPhans as $hp) {
            HocPhan::updateOrCreate(
                ['MaHocPhan' => $hp['MaHocPhan']],
                $hp
            );
        }

        // Cập nhật các nhóm và đề tài hiện có sang MaHocPhan mặc định là 'HP_KLTN' nếu chưa có
        Nhom::whereNull('MaHocPhan')->update(['MaHocPhan' => 'HP_KLTN']);
        DeTai::whereNull('MaHocPhan')->update([
            'MaHocPhan' => 'HP_KLTN',
            'HocPhan'   => 'Khóa luận tốt nghiệp'
        ]);
    }
}
