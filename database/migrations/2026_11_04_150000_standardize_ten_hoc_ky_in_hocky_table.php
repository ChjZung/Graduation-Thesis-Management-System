<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hocKys = DB::table('HocKy')->get();
        foreach ($hocKys as $hk) {
            $tenHk = $hk->TenHocKy ?? '';
            // Loại bỏ phần năm học trong ngoặc tròn nếu có, ví dụ: 'Học kỳ 1 (2024-2025)' -> 'Học kỳ 1'
            $cleanTen = trim(preg_replace('/\s*\((?:20\d{2}[–\-]\d{2,4})\)\s*/u', '', $tenHk));
            if ($cleanTen !== '' && $cleanTen !== $tenHk) {
                DB::table('HocKy')->where('MaHocKy', $hk->MaHocKy)->update(['TenHocKy' => $cleanTen]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Chuẩn hóa tên học kỳ, không cần revert
    }
};
