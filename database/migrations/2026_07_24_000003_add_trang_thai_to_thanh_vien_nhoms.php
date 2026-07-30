<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('thanh_vien_nhoms', function (Blueprint $table) {
            if (!Schema::hasColumn('thanh_vien_nhoms', 'TrangThai')) {
                $table->string('TrangThai', 50)->default('da_tham_gia')->after('VaiTro');
            }
        });
    }

    public function down(): void {
        Schema::table('thanh_vien_nhoms', function (Blueprint $table) {
            $table->dropColumn(['TrangThai']);
        });
    }
};
