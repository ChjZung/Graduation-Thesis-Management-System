<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('phan_cong_huong_dan_lops', function (Blueprint $table) {
            $table->unique(['MaGV', 'MaLop', 'MaHocKy'], 'unique_gv_lop_hocky');
        });
    }

    public function down(): void {
        Schema::table('phan_cong_huong_dan_lops', function (Blueprint $table) {
            $table->dropUnique('unique_gv_lop_hocky');
        });
    }
};
