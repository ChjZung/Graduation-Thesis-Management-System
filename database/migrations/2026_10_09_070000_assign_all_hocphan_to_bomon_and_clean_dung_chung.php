<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\HocPhan;
use App\Models\HocPhanHocKy;
use App\Models\TaiKhoan;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Phân bổ toàn bộ 50 Giảng viên vào 4 Bộ môn (Không để MaBoMon null)
        $bmMapping = [
            'BM_CNPM' => [
                'GV00000001', 'GV00000002', 'GV00000004', 'GV00000007', 
                'GV00000011', 'GV00000015', 'GV00000019', 'GV00000022', 
                'GV00000024', 'GV00000035', 'GV00000038', 'GV00000045', 'GV00000049'
            ],
            'BM_HTTT' => [
                'GV00000003', 'GV00000008', 'GV00000012', 'GV00000016', 
                'GV00000020', 'GV00000023', 'GV00000026', 'GV00000029', 
                'GV00000032', 'GV00000036', 'GV00000040', 'GV00000043', 'GV00000047'
            ],
            'BM_ATTT' => [
                'GV00000005', 'GV00000009', 'GV00000013', 'GV00000017', 
                'GV00000021', 'GV00000025', 'GV00000028', 'GV00000031', 
                'GV00000034', 'GV00000037', 'GV00000041', 'GV00000044', 'GV00000048'
            ],
            'BM_KHMT' => [
                'GV00000006', 'GV00000010', 'GV00000014', 'GV00000018', 
                'GV00000027', 'GV00000030', 'GV00000033', 'GV00000039', 
                'GV00000042', 'GV00000046', 'GV00000050'
            ],
        ];

        foreach ($bmMapping as $maBM => $gvCodes) {
            DB::table('GiangVien')->whereIn('MaGV', $gvCodes)->update(['MaBoMon' => $maBM]);
        }

        // Cập nhật Trưởng bộ môn cho 4 bộ môn
        $tbmConfigs = [
            'BM_CNPM' => ['maGV' => 'GV00000001', 'hoTen' => 'PGS.TS. Nguyễn Văn Hùng'],
            'BM_HTTT' => ['maGV' => 'GV00000003', 'hoTen' => 'TS. Phạm Minh Tuấn'],
            'BM_ATTT' => ['maGV' => 'GV00000005', 'hoTen' => 'TS. Lê Hải Đăng'],
            'BM_KHMT' => ['maGV' => 'GV00000006', 'hoTen' => 'PGS.TS. Võ Quốc Phong'],
        ];

        foreach ($tbmConfigs as $maBM => $cfg) {
            DB::table('BoMon')->where('MaBoMon', $maBM)->update(['TruongBoMon' => $cfg['hoTen']]);

            // Cấp tài khoản TBM riêng cho từng bộ môn nếu chưa có
            $username = 'TBM_' . $maBM . '_' . $cfg['maGV'];
            if (!DB::table('TaiKhoan')->where('TenDangNhap', $username)->exists()) {
                DB::table('TaiKhoan')->insert([
                    'MaTK'             => 'TK_' . $username,
                    'TenDangNhap'      => $username,
                    'MatKhau'          => Hash::make('Huit@2026_TBM'),
                    'MaVaiTro'         => 'VT04',
                    'TrangThai'        => true,
                    'TrangThaiMatKhau' => 'ACTIVE',
                    'BatBuocDoiMatKhau'=> false,
                    'SoLanDangNhapSai' => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // 2. Xóa liên kết cũ của các học phần dùng chung (MaBoMon is null)
        DB::table('HocPhan_HocKy')->whereIn('MaHocPhan', ['HP_KLCN', 'HP_KLKS', 'HP_KLTN'])->delete();
        DB::table('HocPhan')->whereNull('MaBoMon')->delete();

        // 3. Khởi tạo các học phần Khóa luận cho từng Bộ môn (học phần là con của bộ môn)
        $boMons = DB::table('BoMon')->get();
        foreach ($boMons as $bm) {
            $bmSuffix = str_replace('BM_', '', $bm->MaBoMon);

            $thesisCourses = [
                [
                    'MaHocPhan'   => 'HP_KLCN_' . $bmSuffix,
                    'TenHocPhan'  => 'Khóa luận cử nhân',
                    'SoTinChi'    => 10,
                    'SoTietLT'    => 0,
                    'SoTietTH'    => 0,
                    'SoTietKhac'  => 150,
                    'MaKhoa'      => 'CNTT',
                    'MaBoMon'     => $bm->MaBoMon,
                    'LoaiHocPhan' => 'Khóa luận',
                    'TrangThai'   => 'Đang sử dụng',
                    'MoTa'        => 'Học phần khóa luận cử nhân thuộc ' . $bm->TenBoMon,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'MaHocPhan'   => 'HP_KLKS_' . $bmSuffix,
                    'TenHocPhan'  => 'Khóa luận kỹ sư',
                    'SoTinChi'    => 10,
                    'SoTietLT'    => 0,
                    'SoTietTH'    => 0,
                    'SoTietKhac'  => 150,
                    'MaKhoa'      => 'CNTT',
                    'MaBoMon'     => $bm->MaBoMon,
                    'LoaiHocPhan' => 'Khóa luận',
                    'TrangThai'   => 'Đang sử dụng',
                    'MoTa'        => 'Học phần khóa luận kỹ sư thuộc ' . $bm->TenBoMon,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'MaHocPhan'   => 'HP_KLTN_' . $bmSuffix,
                    'TenHocPhan'  => 'Khóa luận tốt nghiệp',
                    'SoTinChi'    => 10,
                    'SoTietLT'    => 0,
                    'SoTietTH'    => 0,
                    'SoTietKhac'  => 150,
                    'MaKhoa'      => 'CNTT',
                    'MaBoMon'     => $bm->MaBoMon,
                    'LoaiHocPhan' => 'Khóa luận',
                    'TrangThai'   => 'Đang sử dụng',
                    'MoTa'        => 'Học phần khóa luận tốt nghiệp thuộc ' . $bm->TenBoMon,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            ];

            foreach ($thesisCourses as $tc) {
                DB::table('HocPhan')->updateOrInsert(
                    ['MaHocPhan' => $tc['MaHocPhan']],
                    $tc
                );
            }
        }

        // Bổ sung môn chuyên ngành cho Bộ môn Khoa học máy tính nếu chưa có
        DB::table('HocPhan')->updateOrInsert(
            ['MaHocPhan' => 'HP_KHMT01'],
            [
                'TenHocPhan'  => 'Trí tuệ nhân tạo và Học máy',
                'SoTinChi'    => 3,
                'SoTietLT'    => 30,
                'SoTietTH'    => 30,
                'SoTietKhac'  => 0,
                'MaKhoa'      => 'CNTT',
                'MaBoMon'     => 'BM_KHMT',
                'LoaiHocPhan' => 'Chuyên ngành',
                'TrangThai'   => 'Đang sử dụng',
                'MoTa'        => 'Học phần chuyên ngành thuộc Bộ môn Khoa học máy tính',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // 4. Mở các học phần khóa luận và chuyên ngành cho Học kỳ hiện tại (HK2627_2)
        $allHpCodes = DB::table('HocPhan')->pluck('MaHocPhan');
        foreach ($allHpCodes as $hpCode) {
            DB::table('HocPhan_HocKy')->updateOrInsert(
                ['MaHocPhan' => $hpCode, 'MaHocKy' => 'HK2627_2'],
                [
                    'TrangThai'   => 'Đang mở',
                    'GhiChu'      => 'Mở cho HK2 2026-2027',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // No down
    }
};
