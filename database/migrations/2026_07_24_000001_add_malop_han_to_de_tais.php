<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('de_tais', function (Blueprint $table) {
            if (!Schema::hasColumn('de_tais', 'MaLop')) {
                $table->unsignedBigInteger('MaLop')->nullable()->after('MaMon');
                $table->foreign('MaLop')->references('MaLop')->on('lops')->onUpdate('cascade')->onDelete('set null');
            }
            if (!Schema::hasColumn('de_tais', 'HanDangKy')) {
                $table->date('HanDangKy')->nullable()->after('TrangThai');
            }
            if (!Schema::hasColumn('de_tais', 'HanBaoCao')) {
                $table->date('HanBaoCao')->nullable()->after('HanDangKy');
            }
            if (!Schema::hasColumn('de_tais', 'HanNopSanPham')) {
                $table->date('HanNopSanPham')->nullable()->after('HanBaoCao');
            }
        });
    }

    public function down(): void {
        Schema::table('de_tais', function (Blueprint $table) {
            $table->dropForeign(['MaLop']);
            $table->dropColumn(['MaLop', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham']);
        });
    }
};
