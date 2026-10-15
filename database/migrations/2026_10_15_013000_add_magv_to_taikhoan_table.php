<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TaiKhoan', function (Blueprint $table) {
            if (!Schema::hasColumn('TaiKhoan', 'MaGV')) {
                $table->string('MaGV', 20)->nullable()->after('MaVaiTro');
                $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('TaiKhoan', function (Blueprint $table) {
            if (Schema::hasColumn('TaiKhoan', 'MaGV')) {
                $table->dropForeign(['MaGV']);
                $table->dropColumn('MaGV');
            }
        });
    }
};
