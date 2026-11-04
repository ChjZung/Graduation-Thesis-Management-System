<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            if (!Schema::hasColumn('KetQuaSinhVien', 'DiemCLO')) {
                $table->json('DiemCLO')->nullable()->after('LoaiKhoaLuan')->comment('Dữ liệu điểm CLO trung bình và điểm từng giảng viên của sinh viên');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            if (Schema::hasColumn('KetQuaSinhVien', 'DiemCLO')) {
                $table->dropColumn('DiemCLO');
            }
        });
    }
};
