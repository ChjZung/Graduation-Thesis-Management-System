<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\HocPhan;
use App\Models\HocKy;
use App\Models\HocPhanHocKy;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chuẩn hóa trạng thái bảng HocPhan: chuyển các giá trị '1', 1, 'Đang áp dụng' về 'Đang sử dụng'
        DB::table('HocPhan')
            ->whereIn('TrangThai', ['1', 'Đang áp dụng'])
            ->orWhereNull('TrangThai')
            ->update(['TrangThai' => 'Đang sử dụng']);

        // 2. Tự động đồng bộ và mở các học phần dùng chung toàn khoa cho toàn bộ học kỳ hiện có
        $commonHps = DB::table('HocPhan')
            ->whereNull('MaBoMon')
            ->where('TrangThai', 'Đang sử dụng')
            ->pluck('MaHocPhan');

        $hocKies = DB::table('HocKy')->pluck('MaHocKy');

        foreach ($hocKies as $maHK) {
            foreach ($commonHps as $maHP) {
                DB::table('HocPhan_HocKy')->updateOrInsert(
                    [
                        'MaHocPhan' => $maHP,
                        'MaHocKy'   => $maHK,
                    ],
                    [
                        'TrangThai'   => 'Đang mở',
                        'GhiChu'      => 'Tự động mở học phần dùng chung toàn khoa',
                        'updated_at'  => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Không hoàn tác dữ liệu chuẩn hóa
    }
};
