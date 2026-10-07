<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Nhom', function (Blueprint $table) {
            if (!Schema::hasColumn('Nhom', 'MaHocPhan')) {
                $table->string('MaHocPhan', 20)->nullable()->after('MaHocKy');
                $table->foreign('MaHocPhan')->references('MaHocPhan')->on('HocPhan')->nullOnDelete();
            }
        });

        Schema::table('DeTai', function (Blueprint $table) {
            if (!Schema::hasColumn('DeTai', 'MaHocPhan')) {
                $table->string('MaHocPhan', 20)->nullable()->after('HocPhan');
                $table->foreign('MaHocPhan')->references('MaHocPhan')->on('HocPhan')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('DeTai', function (Blueprint $table) {
            if (Schema::hasColumn('DeTai', 'MaHocPhan')) {
                $table->dropForeign(['MaHocPhan']);
                $table->dropColumn('MaHocPhan');
            }
        });

        Schema::table('Nhom', function (Blueprint $table) {
            if (Schema::hasColumn('Nhom', 'MaHocPhan')) {
                $table->dropForeign(['MaHocPhan']);
                $table->dropColumn('MaHocPhan');
            }
        });
    }
};
