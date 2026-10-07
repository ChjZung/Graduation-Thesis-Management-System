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
                'MaHocPhan'   => 'HP_KLCN',
                'TenHocPhan'  => 'Khóa luận cử nhân',
                'SoTinChi'    => 10,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Khóa luận',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Học phần khóa luận cử nhân dùng chung cho toàn bộ sinh viên khoa CNTT đủ điều kiện làm khóa luận.',
            ],
            [
                'MaHocPhan'   => 'HP_KLKS',
                'TenHocPhan'  => 'Khóa luận kỹ sư',
                'SoTinChi'    => 10,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => null, // Dùng chung
                'LoaiHocPhan' => 'Khóa luận',
                'TrangThai'   => 'Đang áp dụng',
                'MoTa'        => 'Học phần khóa luận kỹ sư định hướng công nghệ ứng dụng chuyên sâu cho sinh viên khoa CNTT.',
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

        // Cập nhật các nhóm và đề tài hiện có sang MaHocPhan mặc định là 'HP_KLCN' nếu chưa có
        Nhom::whereNull('MaHocPhan')->orWhere('MaHocPhan', 'HP_KLTN')->update(['MaHocPhan' => 'HP_KLCN']);
        DeTai::whereNull('MaHocPhan')->orWhere('MaHocPhan', 'HP_KLTN')->update([
            'MaHocPhan' => 'HP_KLCN',
            'HocPhan'   => 'Khóa luận cử nhân'
        ]);

        // 4. Mở học phần theo học kỳ (HocPhan_HocKy)
        $allHocKies = \App\Models\HocKy::all();
        foreach ($allHocKies as $hk) {
            // Khóa luận dùng chung luôn mở ở tất cả học kỳ
            \App\Models\HocPhanHocKy::updateOrCreate(
                ['MaHocPhan' => 'HP_KLCN', 'MaHocKy' => $hk->MaHocKy],
                ['TrangThai' => 'Đang mở', 'GhiChu' => 'Mở định kỳ toàn khoa']
            );
            \App\Models\HocPhanHocKy::updateOrCreate(
                ['MaHocPhan' => 'HP_KLKS', 'MaHocKy' => $hk->MaHocKy],
                ['TrangThai' => 'Đang mở', 'GhiChu' => 'Mở định kỳ toàn khoa']
            );

            // Phân bổ môn chuyên ngành theo kỳ:
            // Kỳ 1: Công nghệ phần mềm (CNPM) & Phân tích thiết kế hệ thống (HTTT)
            // Kỳ 2: Kiểm thử phần mềm (CNPM) & Hệ thống thông tin quản lý (HTTT)
            if (str_contains($hk->MaHocKy, '_1')) {
                \App\Models\HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => 'HP_CNPM', 'MaHocKy' => $hk->MaHocKy],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Học kỳ 1 môn chuyên ngành CNPM']
                );
                \App\Models\HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => 'HP_PTTKHT', 'MaHocKy' => $hk->MaHocKy],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Học kỳ 1 môn chuyên ngành HTTT']
                );
            } elseif (str_contains($hk->MaHocKy, '_2')) {
                \App\Models\HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => 'HP_KTPM', 'MaHocKy' => $hk->MaHocKy],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Học kỳ 2 môn chuyên ngành CNPM']
                );
                \App\Models\HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => 'HP_HTTTQL', 'MaHocKy' => $hk->MaHocKy],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Học kỳ 2 môn chuyên ngành HTTT']
                );
            }
        }
    }
}
