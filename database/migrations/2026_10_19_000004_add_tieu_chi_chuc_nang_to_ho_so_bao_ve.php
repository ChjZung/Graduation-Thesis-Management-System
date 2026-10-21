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
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (!Schema::hasColumn('HoSoBaoVe', 'TieuChiChucNang')) {
                $table->text('TieuChiChucNang')->nullable()->after('LoaiKhoaLuan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (Schema::hasColumn('HoSoBaoVe', 'TieuChiChucNang')) {
                $table->dropColumn('TieuChiChucNang');
            }
        });
    }
};
